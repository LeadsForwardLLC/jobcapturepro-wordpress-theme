<?php
/**
 * Proof Gap (/proof-gap/) performance — Hybrid ads + speed.
 *
 * - Slim first-party CSS (no layout/utilities/tailwind/components chain).
 * - Async mocks CSS (below-fold destinations).
 * - WebP logo preload.
 * - Early Meta Pixel PageView (ads signal).
 * - Defer GTM / PostHog SDK / Matomo / FPR until interaction (via demo-run-perf helpers).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the Proof Gap survey app.
 */
function jcp_core_is_proof_gap_request(): bool {
	return function_exists( 'jcp_proof_gap_is_current' ) && jcp_proof_gap_is_current();
}

/**
 * Include Proof Gap in the delayed third-party analytics strip/loader.
 *
 * @param bool $delay Whether to delay third-party tags.
 */
function jcp_core_proof_gap_delay_third_party( bool $delay ): bool {
	if ( $delay ) {
		return true;
	}
	return jcp_core_is_proof_gap_request();
}
add_filter( 'jcp_core_should_delay_third_party_analytics', 'jcp_core_proof_gap_delay_third_party' );

/**
 * Dequeue unused CSS/JS chrome on Proof Gap.
 */
function jcp_core_proof_gap_dequeue_unused_assets(): void {
	if ( ! jcp_core_is_proof_gap_request() ) {
		return;
	}

	$style_handles = [
		'jcp-core-layout',
		'jcp-core-buttons',
		'jcp-core-components',
		'jcp-core-utilities',
		'jobcapturepro-tailwind',
		'jobcapturepro-plugin-tailwind',
		'tailwind',
		'tailwindcss',
		'tailwind.min.css',
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles',
		'wc-blocks-style',
		'woocommerce-general',
		'woocommerce-layout',
		'woocommerce-smallscreen',
	];
	foreach ( $style_handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	$script_handles = [
		'wp-embed',
		'jcp-core-site-banner',
		'jcp-core-nav',
		'googlesitekit-events-provider-content-events',
	];
	foreach ( $script_handles as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_proof_gap_dequeue_unused_assets', 1001 );
add_action( 'wp_print_styles', 'jcp_core_proof_gap_dequeue_unused_assets', 101 );
add_action( 'wp_print_scripts', 'jcp_core_proof_gap_dequeue_unused_assets', 101 );

/**
 * Trim WP chrome on Proof Gap (emoji, oEmbed, etc.).
 */
function jcp_core_proof_gap_trim_wp_chrome(): void {
	if ( ! jcp_core_is_proof_gap_request() ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'template_redirect', 'jcp_core_proof_gap_trim_wp_chrome', 20 );

/**
 * Preload sized logo + limited preconnects (Meta + PostHog host).
 */
function jcp_core_proof_gap_head_hints(): void {
	if ( ! jcp_core_is_proof_gap_request() ) {
		return;
	}

	$logo = get_template_directory_uri() . '/assets/brand/jcp-logo-dark-320.webp';
	echo '<link rel="preload" as="image" type="image/webp" href="' . esc_url( $logo ) . '" fetchpriority="high">' . "\n";
	echo '<link rel="preconnect" href="https://connect.facebook.net" crossorigin>' . "\n";
	echo '<link rel="dns-prefetch" href="//www.facebook.com">' . "\n";
}
add_action( 'wp_head', 'jcp_core_proof_gap_head_hints', 1 );

/**
 * Early Meta Pixel — after first paint so it does not compete with LCP.
 * fbclid is still captured immediately by jcp-attribution.js.
 */
function jcp_core_proof_gap_early_meta_pixel(): void {
	if ( ! jcp_core_is_proof_gap_request() ) {
		return;
	}

	$pixel = '';
	if ( function_exists( 'jcp_trial_secret' ) ) {
		$pixel = (string) jcp_trial_secret( 'JCP_META_PIXEL_ID' );
	}
	if ( $pixel === '' && defined( 'JCP_META_PIXEL_ID_DEFAULT' ) ) {
		$pixel = (string) JCP_META_PIXEL_ID_DEFAULT;
	}
	if ( $pixel === '' ) {
		$pixel = '1440845294314184';
	}
	$pixel = preg_replace( '/[^0-9]/', '', $pixel );
	if ( $pixel === '' ) {
		return;
	}

	echo "<script id=\"jcp-pg-meta-pixel\" data-rocket-skip>\n";
	echo "(function(){\n";
	echo "var PIXEL=" . wp_json_encode( $pixel ) . ";\n";
	echo "var done=false;\n";
	echo "function inject(){\n";
	echo "if(done||window.fbq)return;done=true;\n";
	echo "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n";
	echo "n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;\n";
	echo "n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;\n";
	echo "t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',\n";
	echo "'https://connect.facebook.net/en_US/fbevents.js');\n";
	echo "fbq('init',PIXEL);fbq('track','PageView');\n";
	echo "}\n";
	// After paint: interaction first (real ads sessions), else idle/timeout backup.
	// Keeps PageView for Meta; avoids competing with LCP/TBT in the first ~3s.
	echo "var ev=['pointerdown','keydown','touchstart','scroll'];\n";
	echo "function onInteract(){ev.forEach(function(t){window.removeEventListener(t,onInteract,{capture:true});});inject();}\n";
	echo "ev.forEach(function(t){window.addEventListener(t,onInteract,{once:true,passive:true,capture:true});});\n";
	echo "if('requestIdleCallback' in window)requestIdleCallback(inject,{timeout:3500});\n";
	echo "else window.addEventListener('load',function(){setTimeout(inject,2500);});\n";
	echo "})();\n";
	echo "</script>\n";
}
add_action( 'wp_head', 'jcp_core_proof_gap_early_meta_pixel', 4 );

/**
 * Async-load page CSS after matching critical CSS paints the welcome fold.
 * Critical CSS in page-proof-gap.php mirrors chrome/CTA/checklist/trust so CLS stays ~0.
 *
 * @param string $html   Link tag.
 * @param string $handle Style handle.
 */
function jcp_core_proof_gap_async_page_css( string $html, string $handle ): string {
	if ( ! jcp_core_is_proof_gap_request() ) {
		return $html;
	}
	if ( $handle !== 'jcp-core-proof-gap' && $handle !== 'jcp-core-proof-gap-mocks' && $handle !== 'jcp-core-base' ) {
		return $html;
	}
	if ( strpos( $html, 'onload=' ) !== false ) {
		return $html;
	}
	$async = preg_replace( "/\smedia=['\"]all['\"]/", " media='print' onload=\"this.media='all'\"", $html, 1 );
	if ( ! is_string( $async ) ) {
		return $html;
	}
	if ( strpos( $async, 'noscript' ) === false && preg_match( '/href=[\'"]([^\'"]+)[\'"]/', $async, $m ) ) {
		$async .= '<noscript><link rel="stylesheet" href="' . esc_url( $m[1] ) . '"></noscript>';
	}
	return $async;
}
add_filter( 'style_loader_tag', 'jcp_core_proof_gap_async_page_css', 24, 2 );

/**
 * Keep survey + attribution scripts out of Rocket Delay JS.
 *
 * @param string[] $excluded Patterns.
 * @return string[]
 */
function jcp_core_proof_gap_rocket_delay_exclusions( array $excluded ): array {
	$excluded[] = 'jcp-core-proof-gap';
	$excluded[] = 'proof-gap.js';
	$excluded[] = 'proof-gap-entry-ab';
	$excluded[] = 'proof-gap-entry-ab.js';
	$excluded[] = 'pg-entry-ab';
	$excluded[] = 'jcp-core-onboarding-handoff';
	$excluded[] = 'jcp-onboarding-handoff.js';
	$excluded[] = 'jcp-core-posthog';
	$excluded[] = 'jcp-posthog.js';
	$excluded[] = 'jcp-pg-meta-pixel';
	$excluded[] = 'fbevents.js';
	$excluded[] = 'connect.facebook.net';
	$excluded[] = 'fbq(';
	return array_values( array_unique( $excluded ) );
}
add_filter( 'rocket_delay_js_exclusions', 'jcp_core_proof_gap_rocket_delay_exclusions' );
add_filter( 'rocket_exclude_js', 'jcp_core_proof_gap_rocket_delay_exclusions' );
add_filter( 'rocket_exclude_defer_js', 'jcp_core_proof_gap_rocket_delay_exclusions' );

/**
 * Skip duplicate robots meta from theme when Rank Math / core already printed one.
 * Template still emits a single canonical noindex,nofollow in page-proof-gap.php.
 */
function jcp_core_proof_gap_dedupe_robots_meta(): void {
	if ( ! jcp_core_is_proof_gap_request() ) {
		return;
	}
	// Theme helper in proof-gap.php also echoes robots — remove the duplicate action.
	remove_action( 'wp_head', 'jcp_proof_gap_robots_meta', 1 );
}
add_action( 'wp_head', 'jcp_core_proof_gap_dedupe_robots_meta', 0 );
