<?php
/**
 * REST: Proof Gap Survey lead capture.
 *
 * Reuses durable lead queue transport with a dedicated GHL webhook
 * (JCP_GHL_PROOF_GAP_WEBHOOK_URL). Event/tag: proof-gap-lead only.
 * Does NOT use the Demo Survey webhook or any demo-* tags.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Canonical GHL Event + tag for Proof Gap acquisition. */
define( 'JCP_PROOF_GAP_GHL_EVENT', 'proof-gap-lead' );

/**
 * Allow proof-gap events on the shared GHL queue allowlist.
 *
 * @param array<string, list<string>> $events Map.
 * @return array<string, list<string>>
 */
function jcp_proof_gap_extend_allowed_events( array $events ): array {
	$events[ JCP_PROOF_GAP_GHL_EVENT ] = [ JCP_PROOF_GAP_GHL_EVENT ];
	// Legacy allowlist so any pending queue rows from the prior event name can still retry.
	$events['proof-gap-survey'] = [ JCP_PROOF_GAP_GHL_EVENT ];
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
 * Find an existing Proof Gap queue row by Meta/client event_id (idempotency).
 *
 * @param string $event_id Client or minted event id.
 * @return object|null
 */
function jcp_proof_gap_find_lead_by_event_id( string $event_id ) {
	if ( $event_id === '' || ! defined( 'JCP_DEMO_LEAD_QUEUE_TABLE' ) ) {
		return null;
	}
	global $wpdb;
	jcp_demo_lead_queue_maybe_create_table();
	$table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE event_id = %s AND event_name IN (%s, %s) ORDER BY id DESC LIMIT 1",
			$event_id,
			JCP_PROOF_GAP_GHL_EVENT,
			'proof-gap-survey'
		)
	);
}

/**
 * Append extra scalar keys to a form-urlencoded GHL body.
 *
 * @param string               $body  Existing body.
 * @param array<string,string> $extra Key => value (empty values skipped).
 */
function jcp_proof_gap_append_ghl_fields( string $body, array $extra ): string {
	foreach ( $extra as $key => $val ) {
		$key = trim( (string) $key );
		$val = trim( (string) $val );
		if ( $key === '' || $val === '' ) {
			continue;
		}
		$body .= '&' . rawurlencode( $key ) . '=' . rawurlencode( $val );
	}
	return $body;
}

/**
 * Build personalized Use Case string for GHL (survey summary + result ranges).
 *
 * @param array<string, mixed> $survey Survey answers.
 */
function jcp_proof_gap_build_use_case( array $survey ): string {
	$session_id          = (string) ( $survey['survey_session_id'] ?? '' );
	$business_type       = (string) ( $survey['business_type'] ?? '' );
	$workflow            = (string) ( $survey['current_workflow'] ?? '' );
	$jobs_bucket         = (string) ( $survey['jobs_per_week_bucket'] ?? '' );
	$proof_pct           = (string) ( $survey['public_proof_percentage'] ?? '' );
	$other_trade_text    = (string) ( $survey['other_trade_text'] ?? '' );
	$other_workflow_text = (string) ( $survey['other_workflow_text'] ?? '' );
	$annual_min          = (int) ( $survey['annual_jobs_min'] ?? 0 );
	$annual_max          = (int) ( $survey['annual_jobs_max'] ?? 0 );
	$unused_min          = (int) ( $survey['unused_jobs_min'] ?? 0 );
	$unused_max          = (int) ( $survey['unused_jobs_max'] ?? 0 );

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

	return implode(
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
	);
}

/**
 * Handle Proof Gap email capture: durable persist + async GHL (Proof Gap webhook only).
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

	if ( ! defined( 'JCP_GHL_PROOF_GAP_WEBHOOK_URL' ) || JCP_GHL_PROOF_GAP_WEBHOOK_URL === '' ) {
		return new WP_REST_Response(
			[
				'success'  => false,
				'captured' => false,
				'message'  => __( 'Proof Gap lead intake unavailable.', 'jcp-core' ),
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

	$event_id = function_exists( 'jcp_demo_lead_resolve_event_id' )
		? jcp_demo_lead_resolve_event_id( $request->get_param( 'event_id' ) )
		: '';

	// Idempotent: same client event_id must not create a second GHL fire.
	$existing = $event_id !== '' ? jcp_proof_gap_find_lead_by_event_id( $event_id ) : null;
	if ( $existing ) {
		$delivered = (string) ( $existing->status ?? '' ) === 'delivered';
		if ( ! $delivered && function_exists( 'jcp_demo_lead_queue_attempt_row' ) ) {
			$delivered = jcp_demo_lead_queue_attempt_row( $existing );
		}

		$handoff = function_exists( 'jcp_proof_gap_create_handoff_token' )
			? jcp_proof_gap_create_handoff_token(
				$email,
				[
					'trade'                   => sanitize_text_field( (string) $request->get_param( 'business_type' ) ),
					'current_workflow'        => sanitize_text_field( (string) $request->get_param( 'current_workflow' ) ),
					'jobs_per_week_bucket'    => sanitize_text_field( (string) $request->get_param( 'jobs_per_week_bucket' ) ),
					'public_proof_percentage' => sanitize_text_field( (string) $request->get_param( 'public_proof_percentage' ) ),
					'annual_jobs_min'         => absint( $request->get_param( 'annual_jobs_min' ) ),
					'annual_jobs_max'         => absint( $request->get_param( 'annual_jobs_max' ) ),
					'unused_jobs_min'         => absint( $request->get_param( 'unused_jobs_min' ) ),
					'unused_jobs_max'         => absint( $request->get_param( 'unused_jobs_max' ) ),
					'survey_session_id'       => sanitize_text_field( (string) $request->get_param( 'survey_session_id' ) ),
					'lp_variant'              => defined( 'JCP_PROOF_GAP_VARIANT' ) ? JCP_PROOF_GAP_VARIANT : 'proof_gap_survey_v1',
				]
			)
			: '';

		return new WP_REST_Response(
			[
				'success'       => true,
				'captured'      => true,
				'delivered'     => (bool) $delivered,
				'queued'        => ! $delivered,
				'lead_id'       => (int) $existing->id,
				'event_id'      => $event_id,
				'handoff_token' => $handoff,
				'dedupe'        => true,
			],
			200
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

	$params = [
		'first_name'      => $first_name,
		'last_name'       => '',
		'email'           => $email,
		'phone'           => '',
		'company'         => '',
		'business_type'   => $business_type,
		'service_area'    => '',
		'demo_goals'      => [],
		'referral_source' => 'proof-gap-lead',
		'event'           => JCP_PROOF_GAP_GHL_EVENT,
		'use_case'        => jcp_proof_gap_build_use_case(
			[
				'survey_session_id'         => $session_id,
				'business_type'             => $business_type,
				'current_workflow'          => $workflow,
				'jobs_per_week_bucket'      => $jobs_bucket,
				'public_proof_percentage'   => $proof_pct,
				'other_trade_text'          => $other_trade_text,
				'other_workflow_text'       => $other_workflow_text,
				'annual_jobs_min'           => $annual_min,
				'annual_jobs_max'           => $annual_max,
				'unused_jobs_min'           => $unused_min,
				'unused_jobs_max'           => $unused_max,
			]
		),
	];

	if ( function_exists( 'jcp_demo_ghl_merge_attribution_from_request' ) ) {
		$params = jcp_demo_ghl_merge_attribution_from_request( $params, $request );
	}

	// Force Proof Gap semantics regardless of client.
	$params['event']          = JCP_PROOF_GAP_GHL_EVENT;
	$params['lp_variant']     = ! empty( $params['lp_variant'] ) ? $params['lp_variant'] : ( defined( 'JCP_PROOF_GAP_VARIANT' ) ? JCP_PROOF_GAP_VARIANT : 'proof_gap_survey_v1' );
	$params['funnel_surface'] = 'proof_gap_survey';
	$params['landing_page']   = ! empty( $params['landing_page'] ) ? $params['landing_page'] : home_url( '/proof-gap/' );

	$tags        = [ JCP_PROOF_GAP_GHL_EVENT ];
	$body_string = jcp_demo_ghl_build_webhook_body( JCP_PROOF_GAP_GHL_EVENT, $params, $tags );

	$extra = [
		defined( 'JCP_GHL_KEY_JOBS_PER_WEEK' ) ? JCP_GHL_KEY_JOBS_PER_WEEK : 'Jobs Per Week'             => $jobs_bucket,
		defined( 'JCP_GHL_KEY_PHOTO_WORKFLOW' ) ? JCP_GHL_KEY_PHOTO_WORKFLOW : 'Photo Workflow'           => $workflow,
		defined( 'JCP_GHL_KEY_MARKETING_USAGE' ) ? JCP_GHL_KEY_MARKETING_USAGE : 'Marketing Usage'       => $proof_pct,
		defined( 'JCP_GHL_KEY_SURVEY_SESSION_ID' ) ? JCP_GHL_KEY_SURVEY_SESSION_ID : 'Survey Session Id' => $session_id,
	];
	$qa_trace = isset( $params['qa_trace_id'] ) ? trim( (string) $params['qa_trace_id'] ) : '';
	if ( $qa_trace !== '' ) {
		$extra[ defined( 'JCP_GHL_KEY_QA_TRACE_ID' ) ? JCP_GHL_KEY_QA_TRACE_ID : 'qa_trace_id' ] = mb_substr( $qa_trace, 0, 80 );
	}
	if ( $other_trade_text !== '' ) {
		$extra['Other Trade'] = $other_trade_text;
	}
	if ( $other_workflow_text !== '' ) {
		$extra['Other Workflow'] = $other_workflow_text;
	}
	if ( $annual_min > 0 || $annual_max > 0 ) {
		$extra['Annual Jobs Min'] = (string) $annual_min;
		if ( $annual_max > 0 ) {
			$extra['Annual Jobs Max'] = (string) $annual_max;
		}
	}
	if ( $unused_min > 0 || $unused_max > 0 ) {
		$extra['Unused Jobs Min'] = (string) $unused_min;
		if ( $unused_max > 0 ) {
			$extra['Unused Jobs Max'] = (string) $unused_max;
		}
	}
	$body_string = jcp_proof_gap_append_ghl_fields( $body_string, $extra );

	if ( defined( 'JCP_GHL_KEY_EVENT_ID' ) && $event_id !== '' ) {
		$body_string .= '&' . rawurlencode( JCP_GHL_KEY_EVENT_ID ) . '=' . rawurlencode( $event_id );
	}

	$params['event'] = JCP_PROOF_GAP_GHL_EVENT;
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
				'lp_variant'              => defined( 'JCP_PROOF_GAP_VARIANT' ) ? JCP_PROOF_GAP_VARIANT : 'proof_gap_survey_v1',
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
