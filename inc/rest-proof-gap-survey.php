<?php
/**
 * REST: Proof Gap Survey lead capture.
 *
 * Reuses durable demo lead queue + GHL webhook transport with survey-specific
 * Event/tags (proof-gap-survey). Does NOT use demo-opt-in / demo-interest tags.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow proof-gap events on the shared GHL queue allowlist.
 *
 * @param array<string, list<string>> $events Map.
 * @return array<string, list<string>>
 */
function jcp_proof_gap_extend_allowed_events( array $events ): array {
	$events['proof-gap-survey'] = [ 'proof-gap-survey', 'proof_gap_survey_v1' ];
	return $events;
}
add_filter( 'jcp_demo_survey_allowed_events', 'jcp_proof_gap_extend_allowed_events' );

/**
 * Register Proof Gap survey REST routes.
 */
function jcp_proof_gap_register_rest_routes(): void {
	register_rest_route(
		'jcp/v1',
		'/proof-gap-survey-submit',
		[
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'jcp_proof_gap_survey_submit_handler',
			'args'                => [
				'email' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_email',
					'validate_callback' => static function ( $value ) {
						return is_email( $value );
					},
				],
				'business_type'     => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'other_trade_text'  => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'other_workflow_text' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'survey_session_id' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'survey_version'    => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'current_workflow'  => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'jobs_per_week_bucket' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'public_proof_percentage' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'annual_jobs_min' => [
					'required'          => false,
					'type'              => 'number',
					'sanitize_callback' => 'absint',
				],
				'annual_jobs_max' => [
					'required'          => false,
					'type'              => 'number',
					'sanitize_callback' => 'absint',
				],
				'unused_jobs_min' => [
					'required'          => false,
					'type'              => 'number',
					'sanitize_callback' => 'absint',
				],
				'unused_jobs_max' => [
					'required'          => false,
					'type'              => 'number',
					'sanitize_callback' => 'absint',
				],
				'event_id' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			] + ( function_exists( 'jcp_demo_ghl_attribution_rest_args' ) ? jcp_demo_ghl_attribution_rest_args() : [] ),
		]
	);
}
add_action( 'rest_api_init', 'jcp_proof_gap_register_rest_routes' );

/**
 * Handle Proof Gap email capture: durable persist + async GHL.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function jcp_proof_gap_survey_submit_handler( WP_REST_Request $request ): WP_REST_Response {
	if ( ! function_exists( 'jcp_demo_lead_queue_insert' ) || ! function_exists( 'jcp_demo_ghl_build_webhook_body' ) ) {
		return new WP_REST_Response(
			[
				'success'  => false,
				'captured' => false,
				'message'  => __( 'Lead capture unavailable.', 'jcp-core' ),
			],
			503
		);
	}

	$email = sanitize_email( (string) $request->get_param( 'email' ) );
	if ( ! is_email( $email ) ) {
		return new WP_REST_Response(
			[
				'success'  => false,
				'captured' => false,
				'message'  => __( 'Work email is required.', 'jcp-core' ),
			],
			400
		);
	}

	$local      = sanitize_text_field( (string) strstr( $email, '@', true ) );
	$first_name = $local !== '' ? $local : 'there';

	$business_type       = sanitize_text_field( (string) $request->get_param( 'business_type' ) );
	$session_id          = sanitize_text_field( (string) $request->get_param( 'survey_session_id' ) );
	$workflow            = sanitize_text_field( (string) $request->get_param( 'current_workflow' ) );
	$jobs_bucket         = sanitize_text_field( (string) $request->get_param( 'jobs_per_week_bucket' ) );
	$proof_pct           = sanitize_text_field( (string) $request->get_param( 'public_proof_percentage' ) );
	$other_trade_text    = mb_substr( sanitize_text_field( (string) $request->get_param( 'other_trade_text' ) ), 0, 80 );
	$other_workflow_text = mb_substr( sanitize_text_field( (string) $request->get_param( 'other_workflow_text' ) ), 0, 80 );
	$annual_min          = absint( $request->get_param( 'annual_jobs_min' ) );
	$annual_max          = absint( $request->get_param( 'annual_jobs_max' ) );
	$unused_min          = absint( $request->get_param( 'unused_jobs_min' ) );
	$unused_max          = absint( $request->get_param( 'unused_jobs_max' ) );

	$annual_label = '';
	if ( $annual_min > 0 || $annual_max > 0 ) {
		$annual_label = $annual_max > 0
			? 'annual_jobs:' . $annual_min . '-' . $annual_max
			: 'annual_jobs:' . $annual_min . '+';
	}
	$unused_label = '';
	if ( $unused_min > 0 || $unused_max > 0 ) {
		$unused_label = $unused_max > 0
			? 'unused_jobs:' . $unused_min . '-' . $unused_max
			: 'unused_jobs:' . $unused_min . '+';
	}

	$params = [
		'first_name'      => $first_name,
		'last_name'       => '',
		'email'           => $email,
		'phone'           => '',
		'company'         => '',
		'business_type'   => $business_type,
		'service_area'    => '',
		'demo_goals'      => [],
		'referral_source' => 'proof-gap-survey',
		'event'           => 'proof-gap-survey',
		'use_case'        => implode(
			' | ',
			array_filter(
				[
					$session_id !== '' ? 'session:' . $session_id : '',
					$business_type !== '' ? 'trade:' . $business_type : '',
					$workflow !== '' ? 'workflow:' . $workflow : '',
					$jobs_bucket !== '' ? 'jobs:' . $jobs_bucket : '',
					$proof_pct !== '' ? 'visibility:' . $proof_pct : '',
					$annual_label,
					$unused_label,
					$other_trade_text !== '' ? 'other_trade:' . $other_trade_text : '',
					$other_workflow_text !== '' ? 'other_workflow:' . $other_workflow_text : '',
				]
			)
		),
	];

	if ( function_exists( 'jcp_demo_ghl_merge_attribution_from_request' ) ) {
		$params = jcp_demo_ghl_merge_attribution_from_request( $params, $request );
	}

	// Force survey semantics regardless of client.
	$params['event']           = 'proof-gap-survey';
	$params['lp_variant']      = $params['lp_variant'] !== '' ? $params['lp_variant'] : JCP_PROOF_GAP_VARIANT;
	$params['funnel_surface']  = 'proof_gap_survey';
	$params['landing_page']    = $params['landing_page'] !== '' ? $params['landing_page'] : home_url( '/proof-gap/' );

	$tags        = [ 'proof-gap-survey', 'proof_gap_survey_v1' ];
	$body_string = jcp_demo_ghl_build_webhook_body( 'proof-gap-survey', $params, $tags );
	$event_id    = function_exists( 'jcp_demo_lead_resolve_event_id' )
		? jcp_demo_lead_resolve_event_id( $request->get_param( 'event_id' ) )
		: '';

	if ( defined( 'JCP_GHL_KEY_EVENT_ID' ) && $event_id !== '' ) {
		$body_string .= '&' . rawurlencode( JCP_GHL_KEY_EVENT_ID ) . '=' . rawurlencode( $event_id );
	}

	// Temporarily ensure allowlist includes our event for shared queue insert.
	$params['event'] = 'proof-gap-survey';
	$lead_id         = jcp_demo_lead_queue_insert( $params, $body_string, $event_id );

	if ( ! $lead_id ) {
		return new WP_REST_Response(
			[
				'success'  => false,
				'captured' => false,
				'message'  => __( 'Could not save your info. Please try again.', 'jcp-core' ),
			],
			500
		);
	}

	$handoff = function_exists( 'jcp_proof_gap_create_handoff_token' )
		? jcp_proof_gap_create_handoff_token(
			$email,
			[
				'trade'                   => $business_type,
				'current_workflow'        => $workflow,
				'jobs_per_week_bucket'    => $jobs_bucket,
				'public_proof_percentage' => $proof_pct,
				'annual_jobs_min'         => $annual_min,
				'annual_jobs_max'         => $annual_max,
				'unused_jobs_min'         => $unused_min,
				'unused_jobs_max'         => $unused_max,
				'survey_session_id'       => $session_id,
				'lp_variant'              => JCP_PROOF_GAP_VARIANT,
			]
		)
		: '';

	global $wpdb;
	$table     = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
	$row       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $lead_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$delivered = $row && function_exists( 'jcp_demo_lead_queue_attempt_row' )
		? jcp_demo_lead_queue_attempt_row( $row )
		: false;

	return new WP_REST_Response(
		[
			'success'       => true,
			'captured'      => true,
			'delivered'     => (bool) $delivered,
			'queued'        => ! $delivered,
			'lead_id'       => (int) $lead_id,
			'event_id'      => $event_id,
			'handoff_token' => $handoff,
		],
		200
	);
}
