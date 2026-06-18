<?php
/**
 * App (SaaS) onboarding URLs — temporary hard-coded sessionId until issuance API lands.
 *
 * @package JCP_Core
 */

/**
 * Placeholder onboarding session UUID (engineering-provided).
 *
 * @return string
 */
function jcp_core_onboarding_hardcoded_session_id(): string {
	$settings = function_exists( 'jcp_global_settings' ) ? jcp_global_settings()['signup'] ?? [] : [];
	$session  = trim( (string) ( $settings['session_id'] ?? '' ) );
	if ( $session !== '' ) {
		return $session;
	}
	return '75ad8454-312e-4224-95b7-8f48f5cd0277';
}

/**
 * Default UTM parameters for marketing site → app onboarding (merge into query string).
 * Optional utm_content identifies the CTA surface for analytics.
 *
 * @param string $utm_content e.g. nav_get_started, pricing, demo_post_panel.
 * @return array<string, string>
 */
function jcp_core_onboarding_utm_defaults( string $utm_content = '' ): array {
	$args = [
		'utm_source'   => 'jobcapturepro.com',
		'utm_medium'   => 'website',
		'utm_campaign' => 'onboarding',
	];
	if ( $utm_content !== '' ) {
		$args['utm_content'] = $utm_content;
	}
	return $args;
}

/**
 * Build onboarding URL without HTML entity encoding (safe for wp_json_encode / redirects).
 *
 * @param array $query_extra Merge into query string; overrides defaults if keys match.
 * @return string Absolute URL.
 */
function jcp_core_onboarding_app_url_raw( array $query_extra = [] ): string {
	$settings = function_exists( 'jcp_global_settings' ) ? jcp_global_settings()['signup'] ?? [] : [];
	$base     = trim( (string) ( $settings['base_url'] ?? '' ) );
	if ( $base === '' ) {
		$base = 'https://app.jobcapturepro.com/onboarding';
	}
	$step = trim( (string) ( $settings['step'] ?? '1' ) );
	if ( $step === '' ) {
		$step = '1';
	}
	$args = array_merge(
		[
			'sessionId' => jcp_core_onboarding_hardcoded_session_id(),
			'step'      => $step,
		],
		$query_extra
	);

	return add_query_arg( $args, $base );
}

/**
 * Escaped onboarding URL for HTML href attributes.
 *
 * @param array $query_extra Same as jcp_core_onboarding_app_url_raw().
 * @return string
 */
function jcp_core_onboarding_app_url( array $query_extra = [] ): string {
	return esc_url( jcp_core_onboarding_app_url_raw( $query_extra ) );
}
