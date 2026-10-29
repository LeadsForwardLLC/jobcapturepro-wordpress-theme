<?php
/**
 * CLI checks for Proof Gap first-screen copy + control secondary placement.
 *
 * Run: php tests/proof-gap-first-screen-copy.php
 *
 * @package JCP_Core
 */

declare(strict_types=1);

$root     = dirname( __DIR__ );
$template = file_get_contents( $root . '/templates/proof-gap/content.php' );
$js       = file_get_contents( $root . '/assets/js/pages/proof-gap.js' );

if ( $template === false || $js === false ) {
	fwrite( STDERR, "FAIL: could not read template or JS\n" );
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

$headline = 'You finish the job. Does anyone see the work?';
$sub      = 'Every completed job is an opportunity to show future customers what you do. But how much of that work actually makes it onto your website, Google Business Profile, or social media?';
$support  = 'Answer 4 quick questions to see where your job photos and customer proof could be working harder.';
$cta      = 'Check my job visibility →';

assert_true( substr_count( $template, $headline ) >= 2, 'headline present on both entry shells' );
assert_true( substr_count( $template, $sub ) >= 2, 'subheadline present on both entry shells' );
assert_true( substr_count( $template, $support ) >= 2, 'supporting line present on both entry shells' );
assert_true( str_contains( $template, $cta ), 'control CTA copy in template' );
assert_true( str_contains( $js, 'Check my job visibility' ), 'control CTA fallback label in JS' );

assert_true( ! str_contains( $template, 'Are they helping you win the next job?' ), 'old headline removed' );
assert_true( ! str_contains( $template, 'See What Your Jobs Could Be Doing' ), 'old CTA removed from template' );
assert_true( ! str_contains( $js, 'See What Your Jobs Could Be Doing' ), 'old CTA removed from JS' );

// Control: checklist + trust must sit in post-CTA secondary block, not before supporting line.
$control_pos = strpos( $template, 'data-pg-entry="control"' );
$dq_pos      = strpos( $template, 'data-pg-entry="direct_question"' );
assert_true( $control_pos !== false && $dq_pos !== false && $control_pos < $dq_pos, 'control shell before direct_question shell' );

$control_block = substr( $template, (int) $control_pos, (int) $dq_pos - (int) $control_pos );
assert_true( str_contains( $control_block, 'pg-welcome-secondary' ), 'control has secondary block for checklist/trust' );
assert_true(
	strpos( $control_block, $support ) !== false
	&& strpos( $control_block, 'pg-welcome-secondary' ) > strpos( $control_block, $support ),
	'control supporting line appears before secondary checklist/trust'
);
assert_true( str_contains( $control_block, 'pg-welcome-checklist' ), 'checklist retained in control' );
assert_true( str_contains( $control_block, 'pg-trust--compact' ), 'trust/agency block retained in control' );

$dq_end   = strpos( $template, 'data-pg-state="trade"', $dq_pos );
$dq_block = substr( $template, (int) $dq_pos, (int) $dq_end - (int) $dq_pos );
assert_true( str_contains( $dq_block, 'data-pg-choices="trade"' ), 'direct_question keeps immediate trade choices' );
assert_true( ! str_contains( $dq_block, 'pgWelcomeCta' ), 'direct_question does not add welcome CTA' );
assert_true( ! str_contains( $dq_block, 'pg-welcome-checklist' ), 'direct_question does not gain control checklist' );

if ( $failures > 0 ) {
	fwrite( STDERR, "\n$failures failure(s)\n" );
	exit( 1 );
}

echo "\nAll proof-gap first-screen copy checks passed.\n";
exit( 0 );
