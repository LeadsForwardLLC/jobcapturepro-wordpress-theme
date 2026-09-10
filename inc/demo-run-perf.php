<?php
/**
 * Fast /demo/ bootstrap: survey gate + ?mode=run shells.
 *
 * Survey gate Lighthouse wins: dequeue unused plugin/WP CSS, trim chrome,
 * preload survey CSS, defer FirstPromoter (was sync in &lt;head&gt;).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrite relative demo template asset paths to absolute theme asset URLs.
 */
function jcp_core_rewrite_demo_template_markup( string $html ): string {
	$asset_base = trailingslashit( get_stylesheet_directory_uri() ) . 'assets';
	$base_url   = untrailingslashit( home_url( '/' ) );

	$replacements = [
		'../../shared/assets/'       => $asset_base . '/shared/assets/',
		'../shared/assets/'          => $asset_base . '/shared/assets/',
		'./shared/assets/'           => $asset_base . '/shared/assets/',
		'/shared/assets/'            => $asset_base . '/shared/assets/',
		'../../campaign/'            => $asset_base . '/campaign/',
		'../campaign/'               => $asset_base . '/campaign/',
		'/src/jcp-demo/'             => $base_url . '/demo/',
		'/src/contractor-directory/' => $base_url . '/directory/',
		'/src/estimate-builder/'     => $base_url . '/estimate/',
	];

	return strtr( $html, $replacements );
}

/**
 * Body markup for the interactive demo shell (already path-rewritten).
 */
function jcp_core_get_demo_run_markup(): string {
	$path = trailingslashit( get_stylesheet_directory() ) . 'assets/demo/index.html';
	if ( ! is_readable( $path ) ) {
		return '';
	}

	$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $raw ) || $raw === '' ) {
		return '';
	}

	if ( preg_match( '/<body[^>]*>(.*)<\/body>/is', $raw, $m ) ) {
		$body = $m[1];
	} else {
		$body = $raw;
	}

	// Strip template-local stylesheet/script tags; WP enqueue owns those.
	$body = preg_replace( '/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', '', $body ) ?? $body;
	$body = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $body ) ?? $body;

	return jcp_core_rewrite_demo_template_markup( $body );
}

/**
 * Whether this request is a lean demo shell (survey gate or interactive run).
 */
function jcp_core_is_demo_shell_request(): bool {
	return ( function_exists( 'jcp_core_is_demo_survey_request' ) && jcp_core_is_demo_survey_request() )
		|| ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() );
}

/**
 * Print preload hints for the interactive demo critical path.
 */
function jcp_core_demo_run_preload_hints(): void {
	if ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ) {
		$demo_css = esc_url( jcp_core_asset_url( 'assets/shared/assets/demo.css' ) );
		$demo_js  = esc_url( jcp_core_asset_url( 'js/features/demo/jcp-demo.js' ) );
		echo '<link rel="dns-prefetch" href="//images.unsplash.com">' . "\n";
		echo '<link rel="preload" href="' . $demo_css . '" as="style">' . "\n";
		echo '<link rel="preload" href="' . $demo_js . '" as="script">' . "\n";
		return;
	}

	if ( ! function_exists( 'jcp_core_is_demo_survey_request' ) || ! jcp_core_is_demo_survey_request() ) {
		return;
	}

	$base   = esc_url( jcp_core_asset_url( 'css/base.css' ) );
	$shared = esc_url( jcp_core_asset_url( 'assets/shared/assets/survey.css' ) );
	$page   = esc_url( jcp_core_asset_url( 'css/pages/survey.css' ) );
	echo '<link rel="preload" href="' . $base . '" as="style">' . "\n";
	echo '<link rel="preload" href="' . $shared . '" as="style">' . "\n";
	echo '<link rel="preload" href="' . $page . '" as="style">' . "\n";
}
add_action( 'wp_head', 'jcp_core_demo_run_preload_hints', 2 );

/**
 * Strip non-essential WP chrome on demo shells.
 */
function jcp_core_demo_shell_trim_wp_chrome(): void {
	if ( ! jcp_core_is_demo_shell_request() ) {
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
add_action( 'template_redirect', 'jcp_core_demo_shell_trim_wp_chrome', 20 );

/**
 * Drop CSS/JS unused by the lean demo shells (survey gate + interactive run).
 */
function jcp_core_demo_shell_dequeue_unused_assets(): void {
	if ( ! jcp_core_is_demo_shell_request() ) {
		return;
	}

	$style_handles = [
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

	// Survey gate does not need Site Kit content-events provider (unused JS).
	if ( function_exists( 'jcp_core_is_demo_survey_request' ) && jcp_core_is_demo_survey_request() ) {
		$script_handles = [
			'googlesitekit-events-provider-content-events',
			'wp-embed',
		];
		foreach ( $script_handles as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_demo_shell_dequeue_unused_assets', 1000 );
add_action( 'wp_print_styles', 'jcp_core_demo_shell_dequeue_unused_assets', 100 );
add_action( 'wp_print_scripts', 'jcp_core_demo_shell_dequeue_unused_assets', 100 );

/**
 * FirstPromoter was loading sync in &lt;head&gt; on /demo/ — defer it so it
 * does not block first paint (affiliate still loads).
 *
 * @param string $tag    Script HTML.
 * @param string $handle Script handle.
 * @param string $src    Script URL.
 */
function jcp_core_demo_survey_defer_third_party_scripts( string $tag, string $handle, string $src ): string {
	if ( ! function_exists( 'jcp_core_is_demo_survey_request' ) || ! jcp_core_is_demo_survey_request() ) {
		return $tag;
	}
	if ( $handle !== 'firstpromoter-js' && strpos( $src, 'firstpromoter.com' ) === false ) {
		return $tag;
	}
	if ( strpos( $tag, ' defer' ) !== false || strpos( $tag, ' async' ) !== false ) {
		return $tag;
	}
	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'jcp_core_demo_survey_defer_third_party_scripts', 20, 3 );

/**
 * Load page-level survey.css after first paint (shared CSS remains blocking).
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 */
function jcp_core_demo_survey_async_secondary_css( string $html, string $handle ): string {
	if ( ! function_exists( 'jcp_core_is_demo_survey_request' ) || ! jcp_core_is_demo_survey_request() ) {
		return $html;
	}
	if ( $handle !== 'jcp-core-survey' ) {
		return $html;
	}
	if ( strpos( $html, 'onload=' ) !== false ) {
		return $html;
	}
	$async = preg_replace( "/\smedia=['\"]all['\"]/", " media='print' onload=\"this.media='all'\"", $html, 1 );
	return is_string( $async ) ? $async : $html;
}
add_filter( 'style_loader_tag', 'jcp_core_demo_survey_async_secondary_css', 20, 2 );

/**
 * Back-compat aliases.
 */
function jcp_core_demo_run_trim_wp_chrome(): void {
	jcp_core_demo_shell_trim_wp_chrome();
}

function jcp_core_demo_run_dequeue_plugin_assets(): void {
	jcp_core_demo_shell_dequeue_unused_assets();
}
