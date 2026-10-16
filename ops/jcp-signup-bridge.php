<?php
/**
 * Direct PostHog CDP → WP signup bridge entrypoint.
 *
 * Lives outside /wp-json/ so SiteGround Anti-Bot is less likely to challenge
 * PostHog destination IPs with sgcaptcha.
 *
 * Auth: X-JCP-Bridge-Secret (same as REST /jcp/v1/posthog-signup-bridge).
 */

require __DIR__ . '/wp-load.php';

if ( ! function_exists( 'jcp_trial_posthog_signup_bridge_handler' ) ) {
	status_header( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( [ 'error' => 'bridge_unavailable' ] );
	exit;
}

$request = new WP_REST_Request( 'POST', '/jcp/v1/posthog-signup-bridge' );
$raw     = file_get_contents( 'php://input' );
if ( is_string( $raw ) && $raw !== '' ) {
	$decoded = json_decode( $raw, true );
	if ( is_array( $decoded ) ) {
		$request->set_body_params( $decoded );
		$request->set_header( 'Content-Type', 'application/json' );
	}
	$request->set_body( $raw );
}

foreach ( [ 'HTTP_X_JCP_BRIDGE_SECRET', 'REDIRECT_HTTP_X_JCP_BRIDGE_SECRET' ] as $hk ) {
	if ( ! empty( $_SERVER[ $hk ] ) ) {
		$request->set_header( 'x-jcp-bridge-secret', (string) $_SERVER[ $hk ] );
		break;
	}
}
if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
	$request->set_header( 'authorization', (string) $_SERVER['HTTP_AUTHORIZATION'] );
}

$response = jcp_trial_posthog_signup_bridge_handler( $request );
status_header( (int) $response->get_status() );
header( 'Content-Type: application/json; charset=utf-8' );
echo wp_json_encode( $response->get_data() );
exit;
