<?php
/**
 * CLI regression tests for jcp_is_qa_traffic / Meta skip helpers.
 *
 * Run: php tests/qa-traffic.php
 *
 * @package JCP_Core
 */

declare(strict_types=1);

$root = dirname( __DIR__ );

// Minimal stubs so form-fields.php + stripe helpers can load without WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $root . '/' );
}
if ( ! function_exists( 'mb_substr' ) ) {
	function mb_substr( $str, $start, $length = null ) {
		return $length === null ? substr( (string) $str, (int) $start ) : substr( (string) $str, (int) $start, (int) $length );
	}
}

require_once $root . '/inc/form-fields.php';

// Mirror of jcp_trial_is_qa_attr / meta finalized without loading full stripe file.
function test_trial_is_qa_attr( array $attr ): bool {
	return jcp_is_qa_traffic( $attr );
}

function test_meta_finalized( string $status ): bool {
	return $status === 'sent' || $status === 'skipped_qa';
}

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

// --- QA positives ---
assert_true( jcp_is_qa_traffic( [ 'qa_trace_id' => 'audit_resume_mid' ] ), 'audit_resume_mid is QA' );
assert_true( jcp_is_qa_traffic( [ 'qa_trace_id' => 'qa_test123' ] ), 'qa_test123 is QA' );
assert_true( jcp_is_qa_traffic( [ 'jcp_qa' => '1' ] ), 'jcp_qa=1 is QA' );
assert_true( jcp_is_qa_traffic( [ 'jcp_internal' => '1' ] ), 'jcp_internal=1 is QA' );
assert_true( jcp_is_qa_traffic( [ 'utm_source' => 'qa' ] ), 'utm_source=qa is QA' );
assert_true( jcp_is_qa_traffic( [ 'utm_source' => 'QA' ] ), 'utm_source=QA is QA' );
assert_true( jcp_is_qa_traffic( [ 'is_qa' => true ] ), 'is_qa true is QA' );
assert_true( jcp_is_qa_traffic( [ 'is_qa' => '1' ] ), 'is_qa=1 is QA' );
assert_true( jcp_ghl_request_is_qa( [ 'qa_trace_id' => 'audit_resume_mid' ] ), 'GHL skip alias for audit_resume_mid' );

// --- Genuine paid / organic negatives ---
assert_true( ! jcp_is_qa_traffic( [
	'utm_source'   => 'fb',
	'utm_medium'   => 'paid_social',
	'utm_campaign' => 'Proof Gap',
	'fbclid'       => 'IwAR0example',
] ), 'Genuine Facebook UTM + fbclid is NOT QA' );

assert_true( ! jcp_is_qa_traffic( [
	'utm_source' => 'facebook',
	'utm_medium' => 'cpc',
] ), 'utm_source=facebook without QA markers is NOT QA' );

assert_true( ! jcp_is_qa_traffic( [
	'utm_source' => 'google',
	'utm_medium' => 'cpc',
] ), 'google cpc is NOT QA' );

assert_true( ! jcp_is_qa_traffic( [] ), 'Empty attribution is NOT QA' );
assert_true( ! jcp_is_qa_traffic( [ 'qa_trace_id' => '' ] ), 'Empty qa_trace_id is NOT QA' );
assert_true( ! jcp_is_qa_traffic( [ 'qa_trace_id' => '   ' ] ), 'Whitespace qa_trace_id is NOT QA' );

// --- Meta skip / finalize semantics ---
assert_true( test_trial_is_qa_attr( [ 'qa_trace_id' => 'audit_resume_mid' ] ), 'Trial attr audit_resume_mid skips Meta' );
assert_true( ! test_trial_is_qa_attr( [ 'utm_source' => 'fb' ] ), 'Paid fb trial does not skip Meta' );
assert_true( test_meta_finalized( 'sent' ), 'meta sent is finalized' );
assert_true( test_meta_finalized( 'skipped_qa' ), 'meta skipped_qa is finalized (no retry)' );
assert_true( ! test_meta_finalized( 'pending' ), 'meta pending is not finalized' );
assert_true( ! test_meta_finalized( 'failed' ), 'meta failed is not finalized' );

if ( $failures > 0 ) {
	fwrite( STDERR, "\n$failures failure(s)\n" );
	exit( 1 );
}

fwrite( STDOUT, "\nAll QA traffic tests passed.\n" );
exit( 0 );
