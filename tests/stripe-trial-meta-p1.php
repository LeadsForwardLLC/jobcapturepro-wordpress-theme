<?php
/**
 * CLI tests for P1 Meta StartTrial deferral helpers.
 *
 * Run: php tests/stripe-trial-meta-p1.php
 *
 * @package JCP_Core
 */

declare(strict_types=1);

$root = dirname( __DIR__ );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $root . '/' );
}

require_once $root . '/inc/form-fields.php';
require_once $root . '/inc/stripe-trial-meta-helpers.php';

$failures = 0;

function assert_true( bool $cond, string $msg ): void {
	global $failures;
	if ( ! $cond ) {
		fwrite( STDERR, "FAIL: $msg\n" );
		$failures++;
	} else {
		fwrite( STDOUT, "PASS: $msg\n" );
	}
}

// --- Finalized / claim gates ---
assert_true( jcp_trial_meta_status_finalized( 'sent' ), 'sent is finalized' );
assert_true( jcp_trial_meta_status_finalized( 'skipped_qa' ), 'skipped_qa is finalized' );
assert_true( ! jcp_trial_meta_status_finalized( 'pending' ), 'pending not finalized' );
assert_true( ! jcp_trial_meta_status_finalized( 'failed' ), 'failed not finalized' );
assert_true( ! jcp_trial_meta_status_finalized( 'sending' ), 'sending not finalized (reclaimable)' );

$now = 1_700_000_000;
assert_true( jcp_trial_meta_claim_allowed( 'pending', $now, $now ), 'claim pending' );
assert_true( jcp_trial_meta_claim_allowed( 'failed', $now, $now ), 'claim failed' );
assert_true( ! jcp_trial_meta_claim_allowed( 'sent', $now, $now ), 'no claim after sent' );
assert_true( ! jcp_trial_meta_claim_allowed( 'skipped_qa', $now, $now ), 'no claim after skipped_qa' );
assert_true(
	! jcp_trial_meta_claim_allowed( 'sending', $now - 30, $now, 120 ),
	'fresh sending not reclaimable'
);
assert_true(
	jcp_trial_meta_claim_allowed( 'sending', $now - 121, $now, 120 ),
	'stale sending reclaimable'
);

// --- Should deliver (matched / timeout / late) ---
assert_true(
	jcp_trial_meta_should_deliver( true, false, 'pending' ),
	'normal matched path delivers'
);
assert_true(
	jcp_trial_meta_should_deliver( false, true, 'pending' ),
	'timeout unmatched delivers'
);
assert_true(
	! jcp_trial_meta_should_deliver( false, false, 'pending' ),
	'unmatched inside window does NOT deliver (defer)'
);
assert_true(
	! jcp_trial_meta_should_deliver( true, false, 'sent' ),
	'late enrichment after sent does not deliver'
);
assert_true(
	! jcp_trial_meta_should_deliver( true, true, 'skipped_qa' ),
	'late enrichment after skipped_qa does not deliver'
);
assert_true(
	jcp_trial_meta_should_deliver( true, false, 'failed' ),
	'failed Meta remains eligible on matched retry'
);

// --- Concurrent claim simulation (exactly one winner) ---
$claims = 0;
$status = 'pending';
$updated = $now;
for ( $i = 0; $i < 5; $i++ ) {
	if ( jcp_trial_meta_claim_allowed( $status, $updated, $now ) ) {
		$claims++;
		$status  = 'sending';
		$updated = $now;
	}
}
assert_true( $claims === 1, 'concurrent delivery attempts: exactly one claim wins' );

// After finalize, further attempts blocked.
$status = 'sent';
assert_true( ! jcp_trial_meta_claim_allowed( $status, $updated, $now ), 'duplicate webhook after sent: no claim' );

// Failed request → reclaimable.
$status = 'failed';
assert_true( jcp_trial_meta_claim_allowed( $status, $updated, $now ), 'failed Meta request can retry' );

// --- Click id selection (prefer valid cookies; no Stripe-time synthesis) ---
$picked = jcp_trial_meta_pick_click_ids( [
	'_fbp' => 'fb.1.1690000000.1234567890',
	'_fbc' => 'fb.1.1690000001.IwAR0abc',
] );
assert_true( ( $picked['fbp'] ?? '' ) === 'fb.1.1690000000.1234567890', 'keeps valid fbp' );
assert_true( ( $picked['fbc'] ?? '' ) === 'fb.1.1690000001.IwAR0abc', 'keeps valid fbc' );

$picked = jcp_trial_meta_pick_click_ids( [
	'_fbp'   => 'not-a-cookie',
	'fbclid' => 'IwAR0only',
] );
assert_true( ! isset( $picked['fbp'] ), 'rejects invalid fbp' );
assert_true( ! isset( $picked['fbc'] ), 'does not synthesize fbc without click timestamp' );

$picked = jcp_trial_meta_pick_click_ids( [
	'fbclid'              => 'IwAR0click',
	'facebook_click_time' => 1690000111,
] );
assert_true(
	( $picked['fbc'] ?? '' ) === 'fb.1.1690000111.IwAR0click',
	'derives fbc only with explicit Facebook click timestamp'
);

$picked = jcp_trial_meta_pick_click_ids( [
	'fbclid'              => 'IwAR0bad',
	'facebook_click_time' => 42, // not a real unix click time
] );
assert_true( ! isset( $picked['fbc'] ), 'rejects non-plausible click timestamps' );

// --- Delayed QA vs paid attribution semantics (decision layer) ---
assert_true(
	jcp_is_qa_traffic( [ 'qa_trace_id' => 'audit_resume_mid' ] ),
	'delayed QA attribution still QA at send-time check'
);
assert_true(
	! jcp_is_qa_traffic( [
		'utm_source' => 'fb',
		'_fbp'       => 'fb.1.1690000000.1',
		'_fbc'       => 'fb.1.1690000001.IwAR0paid',
	] ),
	'delayed paid attribution is not QA'
);

// Matched paid inside window → should deliver; QA finalize is separate (skipped_qa path).
assert_true(
	jcp_trial_meta_should_deliver( true, false, 'pending' ),
	'delayed paid match is eligible for Meta'
);
assert_true(
	jcp_trial_meta_should_deliver( true, false, 'pending' )
	&& jcp_is_qa_traffic( [ 'qa_trace_id' => 'audit_resume_mid' ] ),
	'delayed QA match is eligible for deliver gate but QA skip applies before HTTP'
);

if ( $failures > 0 ) {
	fwrite( STDERR, "\n$failures failure(s)\n" );
	exit( 1 );
}

fwrite( STDOUT, "\nAll P1 Meta deferral tests passed.\n" );
exit( 0 );
