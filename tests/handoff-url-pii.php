<?php
/**
 * CLI checks: trial CTA handoff must not force email into the URL.
 *
 * Run: php tests/handoff-url-pii.php
 *
 * @package JCP_Core
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
$js   = file_get_contents( $root . '/assets/js/pages/proof-gap.js' );
$handoff = file_get_contents( $root . '/assets/js/core/jcp-onboarding-handoff.js' );
$rocket  = file_get_contents( $root . '/inc/wp-rocket.php' );

if ( $js === false ) {
	fwrite( STDERR, "FAIL: could not read proof-gap.js\n" );
	exit( 1 );
}
if ( $handoff === false ) {
	fwrite( STDERR, "FAIL: could not read jcp-onboarding-handoff.js\n" );
	exit( 1 );
}
if ( $rocket === false ) {
	fwrite( STDERR, "FAIL: could not read wp-rocket.php\n" );
	exit( 1 );
}

$failures = 0;
function assert_true( bool $cond, string $msg ): void {
	global $failures;
	if ( ! $cond ) {
		fwrite( STDERR, "FAIL: $msg\n" );
		$failures++;
		return;
	}
	echo "OK: $msg\n";
}

assert_true( str_contains( $js, 'jcp_pg_prefill' ), 'sets jcp_pg_prefill cookie' );
assert_true( str_contains( $js, 'scrubHandoffUrlPii' ), 'defines URL PII scrubber' );
assert_true( str_contains( $js, 'HANDOFF_URL_PII_KEYS' ), 'lists PII keys to strip' );
assert_true( ! str_contains( $js, "parsed.searchParams.set('email'" ), 'does not force email into CTA URL' );
assert_true( ! str_contains( $js, "searchParams.set('email', state.email)" ), 'does not set email query from state' );
assert_true( str_contains( $js, 'scrubHandoffUrlPii(href)' ), 'scrubs href before assigning CTA' );
assert_true( str_contains( $js, 'domain=.jobcapturepro.com' ), 'prefill cookie scoped to registrable domain' );
assert_true( str_contains( $js, 'SameSite=Lax' ), 'prefill cookie SameSite=Lax for top-level handoff' );
assert_true( str_contains( $js, 'Secure' ), 'prefill cookie marked Secure' );
assert_true( str_contains( $js, 'max-age=604800' ), 'prefill cookie expires in 7 days' );
assert_true( str_contains( $js, 'weekly_job_volume' ), 'survey weekly volume rides in prefill cookie' );
assert_true( str_contains( $js, 'photo_workflow' ), 'survey workflow rides in prefill cookie' );
assert_true( str_contains( $js, 'marketing_usage' ), 'survey marketing usage rides in prefill cookie' );

// Shared handoff decorator must scrub AFTER merge (decorateAll / click capture).
assert_true( str_contains( $handoff, 'HANDOFF_URL_PII_KEYS' ), 'handoff.js lists PII keys' );
assert_true( str_contains( $handoff, 'scrubHandoffUrlPii' ), 'handoff.js defines scrubber' );
assert_true( str_contains( $handoff, "'email'" ), 'handoff.js strips email' );
assert_true( str_contains( $handoff, 'scrubHandoffUrlPii(u)' ), 'handoff.js scrubs URL object before return' );
$scrub_pos = strpos( $handoff, 'scrubHandoffUrlPii(u)' );
$return_pos = $scrub_pos !== false ? strpos( $handoff, 'return u.toString()', $scrub_pos ) : false;
assert_true( $scrub_pos !== false && $return_pos !== false && $scrub_pos < $return_pos, 'handoff scrub runs before decorateHref return' );
assert_true( str_contains( $rocket, 'onboarding-handoff-pii-scrub-v2' ), 'Rocket bust bumped for handoff scrub' );

if ( $failures > 0 ) {
	fwrite( STDERR, "\n$failures failure(s)\n" );
	exit( 1 );
}
echo "\nAll handoff URL PII checks passed.\n";
exit( 0 );
