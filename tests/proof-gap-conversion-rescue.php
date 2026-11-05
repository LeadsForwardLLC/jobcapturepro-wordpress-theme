<?php
/**
 * CLI checks for Proof Gap conversion-rescue first-screen + trial-bridge copy.
 *
 * @package JCP_Core
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
$content = file_get_contents( $root . '/templates/proof-gap/content.php' );
$js      = file_get_contents( $root . '/assets/js/pages/proof-gap.js' );
$css     = file_get_contents( $root . '/css/pages/proof-gap.css' );

$fail = 0;
$assert = static function ( bool $ok, string $msg ) use ( &$fail ): void {
	if ( $ok ) {
		echo "OK  {$msg}\n";
		return;
	}
	$fail++;
	echo "FAIL {$msg}\n";
};

$assert( is_string( $content ) && $content !== '', 'content.php readable' );
$assert( is_string( $js ) && $js !== '', 'proof-gap.js readable' );
$assert( is_string( $css ) && $css !== '', 'proof-gap.css readable' );

// First screen.
$assert( str_contains( $content, 'You finish the job. Does anyone see the work?' ), 'welcome headline tightened' );
$assert( str_contains( $content, 'Check my job visibility →' ), 'control CTA is visibility check' );
$assert( ! str_contains( $content, 'See What Your Jobs Could Be Doing →' ), 'old welcome CTA removed from template' );
$assert( str_contains( $content, 'pg-welcome-secondary' ), 'control brochure wrapped for below-fold' );
$assert( str_contains( $content, 'About 60 seconds · No phone · No credit card' ), 'direct_question trust microcopy' );
$assert( str_contains( $css, 'pg-welcome-secondary' ), 'welcome-secondary CSS present' );

// Trial bridge.
$assert( str_contains( $content, 'Start turning unused jobs into marketing.' ), 'trial headline reconnects unused work' );
$assert( str_contains( $content, '14-day free trial. No credit card.' ), 'trial sub leads with free + no CC' );
$assert( str_contains( $content, 'id="pgPlanHero"' ), 'plan hero unused-jobs slot present' );
$assert( str_contains( $content, '<details class="pg-trial-previews"' ), 'channel previews collapsed by default' );
$assert( str_contains( $content, 'Optional: preview sample outputs' ), 'preview framed as optional' );
$assert( str_contains( $js, 'Check my job visibility \\u2192' ) || str_contains( $js, 'Check my job visibility →' ) || str_contains( $js, "Check my job visibility \u2192" ), 'JS welcome CTA fallback updated' );
$assert( str_contains( $js, '14 days free' ) && str_contains( $js, 'No credit card' ), 'JS trial CTA sublabel updated' );
$assert( str_contains( $js, 'cta_visually_on_screen' ), 'trial_cta_viewed records sticky CTA visibility' );
$assert( str_contains( $js, 'pgPlanUnused' ), 'JS fills unused-jobs hero' );
$assert( str_contains( $js, "details.open" ) || str_contains( $js, 'details.open' ), 'ProductRevealStarted deferred until preview open' );

exit( $fail > 0 ? 1 : 0 );
