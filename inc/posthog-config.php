<?php
/**
 * PostHog public project config for the marketing site.
 *
 * Rocket minify/combine often strips wp_add_inline_script() attached to a
 * script handle. Config is therefore delivered three ways:
 * 1) Hardcoded fallback inside jcp-posthog.js
 * 2) wp_localize_script on the posthog handle
 * 3) A standalone <script id="jcp-posthog-config"> in wp_head (not handle-bound)
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public PostHog project API key (same token as web SDK / GTM).
 */
function jcp_core_posthog_project_api_key(): string {
	return (string) apply_filters(
		'jcp_posthog_project_api_key',
		defined( 'JCP_POSTHOG_PROJECT_KEY_DEFAULT' )
			? JCP_POSTHOG_PROJECT_KEY_DEFAULT
			: 'phc_v8emzqtZ8beAjLsqj2byb5fK8wRHbW2g6hXBqAEZPMyS'
	);
}

/**
 * @return array{apiKey:string,apiHost:string,uiHost:string}
 */
function jcp_core_posthog_public_config(): array {
	return [
		'apiKey'  => jcp_core_posthog_project_api_key(),
		'apiHost' => (string) apply_filters( 'jcp_posthog_api_host', 'https://us.i.posthog.com' ),
		'uiHost'  => (string) apply_filters( 'jcp_posthog_ui_host', 'https://us.posthog.com' ),
	];
}

/**
 * Localize config onto the enqueue handle (Rocket usually keeps this).
 */
function jcp_core_localize_posthog_script(): void {
	if ( ! wp_script_is( 'jcp-core-posthog', 'enqueued' ) && ! wp_script_is( 'jcp-core-posthog', 'registered' ) ) {
		return;
	}
	wp_localize_script( 'jcp-core-posthog', 'JCP_POSTHOG_CFG', jcp_core_posthog_public_config() );
	// Also keep window.JCP_POSTHOG for older callers; prefer localize + head script.
	wp_add_inline_script(
		'jcp-core-posthog',
		'window.JCP_POSTHOG=window.JCP_POSTHOG||(typeof JCP_POSTHOG_CFG!=="undefined"?JCP_POSTHOG_CFG:{});',
		'before'
	);
}

/**
 * Standalone head config — survives Rocket handle minify stripping.
 */
function jcp_core_print_posthog_config_head(): void {
	if ( is_admin() ) {
		return;
	}
	$cfg = jcp_core_posthog_public_config();
	echo '<script id="jcp-posthog-config" type="text/javascript">window.JCP_POSTHOG=window.JCP_POSTHOG||'
		. wp_json_encode( $cfg )
		. ';</script>' . "\n";
}
add_action( 'wp_head', 'jcp_core_print_posthog_config_head', 0 );

/**
 * Keep PostHog config + helper out of Rocket Delay / Exclude minify where possible.
 *
 * @param string[] $excluded Patterns.
 * @return string[]
 */
function jcp_core_posthog_rocket_exclusions( array $excluded ): array {
	$excluded[] = 'jcp-posthog-config';
	$excluded[] = 'jcp-core-posthog';
	$excluded[] = 'jcp-posthog.js';
	$excluded[] = 'JCP_POSTHOG';
	$excluded[] = 'JCP_POSTHOG_CFG';
	return array_values( array_unique( $excluded ) );
}
add_filter( 'rocket_delay_js_exclusions', 'jcp_core_posthog_rocket_exclusions' );
add_filter( 'rocket_exclude_js', 'jcp_core_posthog_rocket_exclusions' );
add_filter( 'rocket_exclude_defer_js', 'jcp_core_posthog_rocket_exclusions' );
