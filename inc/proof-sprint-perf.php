<?php
/**
 * Proof Sprint (/proof-sprint/) performance — Hybrid ads + speed.
 *
 * - Slim first-party CSS (base + page only).
 * - WebP logo preload.
 * - Early Meta Pixel PageView (ads signal).
 * - Defer GTM / PostHog SDK / Matomo / FPR until interaction.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the Proof Sprint funnel.
 */
function jcp_core_is_proof_sprint_request(): bool {
	return function_exists( 'jcp_proof_sprint_is_current' ) && jcp_proof_sprint_is_current();
}

/**
 * Include Proof Sprint in the delayed third-party analytics strip/loader.
 *
 * @param bool $delay Whether to delay third-party tags.
 */
function jcp_core_proof_sprint_delay_third_party( bool $delay ): bool {
	if ( $delay ) {
		return true;
	}
	return jcp_core_is_proof_sprint_request();
}
add_filter( 'jcp_core_should_delay_third_party_analytics', 'jcp_core_proof_sprint_delay_third_party' );

/**
 * Dequeue unused CSS/JS chrome on Proof Sprint.
 */
function jcp_core_proof_sprint_dequeue_unused_assets(): void {
	if ( ! jcp_core_is_proof_sprint_request() ) {
		return;
	}

	$style_handles = [
		'jcp-core-layout',
		'jcp-core-buttons',
		'jcp-core-components',
		'jcp-core-utilities',
		'jcp-core-sections',
		'jcp-core-niche-landing',
		'jcp-core-home',
		'jcp-core-story-moments',
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
add_action( 'wp_enqueue_scripts', 'jcp_core_proof_sprint_dequeue_unused_assets', 1001 );
add_action( 'wp_print_styles', 'jcp_core_proof_sprint_dequeue_unused_assets', 101 );
add_action( 'wp_print_scripts', 'jcp_core_proof_sprint_dequeue_unused_assets', 101 );

/**
 * Trim WP chrome on Proof Sprint.
 */
function jcp_core_proof_sprint_trim_wp_chrome(): void {
	if ( ! jcp_core_is_proof_sprint_request() ) {
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
add_action( 'template_redirect', 'jcp_core_proof_sprint_trim_wp_chrome', 20 );

/**
 * Preload sized logo + Meta preconnect.
 */
function jcp_core_proof_sprint_head_hints(): void {
	if ( ! jcp_core_is_proof_sprint_request() ) {
		return;
	}

	$logo = get_template_directory_uri() . '/assets/brand/jcp-logo-dark-320.webp';
	echo '<link rel="preload" as="image" type="image/webp" href="' . esc_url( $logo ) . '" fetchpriority="high">' . "\n";
	echo '<link rel="preconnect" href="https://connect.facebook.net" crossorigin>' . "\n";
	echo '<link rel="dns-prefetch" href="//www.facebook.com">' . "\n";
}
add_action( 'wp_head', 'jcp_core_proof_sprint_head_hints', 1 );

/**
 * Early Meta Pixel — after paint / on interaction (Hybrid ads).
 */
function jcp_core_proof_sprint_early_meta_pixel(): void {
	if ( ! jcp_core_is_proof_sprint_request() ) {
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

	echo "<script id=\"jcp-ps-meta-pixel\" data-rocket-skip>\n";
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
	echo "var ev=['pointerdown','keydown','touchstart','scroll'];\n";
	echo "function onInteract(){ev.forEach(function(t){window.removeEventListener(t,onInteract,{capture:true});});inject();}\n";
	echo "ev.forEach(function(t){window.addEventListener(t,onInteract,{once:true,passive:true,capture:true});});\n";
	echo "if('requestIdleCallback' in window)requestIdleCallback(inject,{timeout:3500});\n";
	echo "else window.addEventListener('load',function(){setTimeout(inject,2500);});\n";
	echo "})();\n";
	echo "</script>\n";
}
add_action( 'wp_head', 'jcp_core_proof_sprint_early_meta_pixel', 4 );

/**
 * Keep sprint + attribution scripts out of Rocket Delay JS.
 *
 * @param string[] $excluded Patterns.
 * @return string[]
 */
function jcp_core_proof_sprint_rocket_delay_exclusions( array $excluded ): array {
	$excluded[] = 'jcp-core-proof-sprint';
	$excluded[] = 'proof-sprint.js';
	$excluded[] = 'jcp-core-onboarding-handoff';
	$excluded[] = 'jcp-onboarding-handoff.js';
	$excluded[] = 'jcp-core-posthog';
	$excluded[] = 'jcp-posthog.js';
	$excluded[] = 'jcp-ps-meta-pixel';
	$excluded[] = 'fbevents.js';
	$excluded[] = 'connect.facebook.net';
	$excluded[] = 'fbq(';
	return array_values( array_unique( $excluded ) );
}
add_filter( 'rocket_delay_js_exclusions', 'jcp_core_proof_sprint_rocket_delay_exclusions' );

/**
 * Skip duplicate robots meta from theme when Rank Math / core already printed one.
 */
function jcp_core_proof_sprint_dedupe_robots_meta(): void {
	if ( ! jcp_core_is_proof_sprint_request() ) {
		return;
	}
	remove_action( 'wp_head', 'jcp_proof_sprint_robots_meta', 1 );
}
add_action( 'wp_head', 'jcp_core_proof_sprint_dedupe_robots_meta', 0 );
