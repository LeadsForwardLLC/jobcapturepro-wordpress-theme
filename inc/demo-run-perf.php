<?php
/**
 * Fast /demo/?mode=run bootstrap: server-hydrate template markup + path rewrite.
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
		'../../shared/assets/'   => $asset_base . '/shared/assets/',
		'../shared/assets/'      => $asset_base . '/shared/assets/',
		'./shared/assets/'       => $asset_base . '/shared/assets/',
		'/shared/assets/'        => $asset_base . '/shared/assets/',
		'../../campaign/'        => $asset_base . '/campaign/',
		'../campaign/'           => $asset_base . '/campaign/',
		'/src/jcp-demo/'         => $base_url . '/demo/',
		'/src/contractor-directory/' => $base_url . '/directory/',
		'/src/estimate-builder/' => $base_url . '/estimate/',
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
 * Print preload hints for the interactive demo critical path.
 */
function jcp_core_demo_run_preload_hints(): void {
	if ( ! function_exists( 'jcp_core_is_demo_run_request' ) || ! jcp_core_is_demo_run_request() ) {
		return;
	}
	$demo_css = esc_url( jcp_core_asset_url( 'assets/shared/assets/demo.css' ) );
	$demo_js  = esc_url( jcp_core_asset_url( 'js/features/demo/jcp-demo.js' ) );
	echo '<link rel="dns-prefetch" href="//images.unsplash.com">' . "\n";
	echo '<link rel="preload" href="' . $demo_css . '" as="style">' . "\n";
	echo '<link rel="preload" href="' . $demo_js . '" as="script">' . "\n";
}
add_action( 'wp_head', 'jcp_core_demo_run_preload_hints', 2 );

/**
 * Strip non-essential WP chrome on the interactive demo shell.
 */
function jcp_core_demo_run_trim_wp_chrome(): void {
	if ( ! function_exists( 'jcp_core_is_demo_run_request' ) || ! jcp_core_is_demo_run_request() ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'template_redirect', 'jcp_core_demo_run_trim_wp_chrome', 20 );

/**
 * Drop plugin CSS unused by the interactive demo shell.
 */
function jcp_core_demo_run_dequeue_plugin_assets(): void {
	if ( ! function_exists( 'jcp_core_is_demo_run_request' ) || ! jcp_core_is_demo_run_request() ) {
		return;
	}
	$handles = [ 'jobcapturepro-tailwind', 'jobcapturepro-plugin-tailwind', 'tailwind', 'tailwindcss' ];
	foreach ( $handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_demo_run_dequeue_plugin_assets', 1000 );
add_action( 'wp_print_styles', 'jcp_core_demo_run_dequeue_plugin_assets', 100 );
