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
				'estimated_jobs_per_year' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'potentially_unused_percent' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
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
		$body .= ( $body === '' ? '' : '&' ) . rawurlencode( $key ) . '=' . rawurlencode( $val );
	}
	return $body;
}

/**
 * Decode a form-urlencoded GHL body to a flat associative array (for QA preview).
 *
 * Does not use parse_str() — PHP's parse_str converts spaces to underscores and would
 * misrepresent keys like "Business Type" / "Jobs Per Week" in the audit payload.
 *
 * @param string $body Form-urlencoded body.
 * @return array<string, mixed>
 */
function jcp_proof_gap_webhook_body_to_array( string $body ): array {
	$out = [];
	if ( $body === '' ) {
		return $out;
	}
	foreach ( explode( '&', $body ) as $pair ) {
		if ( $pair === '' ) {
			continue;
		}
		$parts = explode( '=', $pair, 2 );
		$key   = rawurldecode( str_replace( '+', ' ', (string) ( $parts[0] ?? '' ) ) );
		$val   = rawurldecode( str_replace( '+', ' ', (string) ( $parts[1] ?? '' ) ) );
		if ( $key === '' ) {
			continue;
		}
		// Collapse Referral Source[] / Tags[] into arrays.
		if ( substr( $key, -2 ) === '[]' ) {
			$base = substr( $key, 0, -2 );
			if ( ! isset( $out[ $base ] ) || ! is_array( $out[ $base ] ) ) {
				$out[ $base ] = [];
			}
			$out[ $base ][] = $val;
			continue;
		}
		$out[ $key ] = $val;
	}
	return $out;
}

/**
 * Format estimated jobs/year range from production result math (no new scoring).
 *
 * @param int $annual_min Annual jobs floor.
 * @param int $annual_max Annual jobs ceiling (0 = open-ended).
 */
function jcp_proof_gap_format_estimated_jobs_per_year( int $annual_min, int $annual_max ): string {
	if ( $annual_min <= 0 && $annual_max <= 0 ) {
		return '';
	}
	if ( $annual_max > 0 ) {
		return (string) $annual_min . '-' . (string) $annual_max;
	}
	return (string) $annual_min . '+';
}

/**
 * Potentially unused % midpoint from the same proof-band table the UI uses.
 *
 * @param string $marketing_usage Machine key e.g. 11_25.
 */
function jcp_proof_gap_format_potentially_unused_percent( string $marketing_usage ): string {
	if ( $marketing_usage === '' || ! function_exists( 'jcp_proof_gap_proof_percentage_options' ) ) {
		return '';
	}
	$opts = jcp_proof_gap_proof_percentage_options();
	if ( ! isset( $opts[ $marketing_usage ] ) || ! is_array( $opts[ $marketing_usage ] ) ) {
		return '';
	}
	$band = $opts[ $marketing_usage ];
	if ( ! isset( $band['min'], $band['max'] ) || $band['min'] === null || $band['max'] === null ) {
		return '';
	}
	$pub_mid = (int) round( ( ( (float) $band['min'] + (float) $band['max'] ) / 2 ) * 100 );
	return (string) ( 100 - $pub_mid );
}

/**
 * Whether this Proof Gap submit is QA/test traffic (must not enter production sales automation).
 *
 * @param array<string, mixed> $params Merged contact + attribution params.
 */
function jcp_proof_gap_request_is_qa( array $params ): bool {
	if ( ! empty( $params['is_qa'] ) ) {
		$raw = $params['is_qa'];
		if ( $raw === true || $raw === 1 || $raw === '1' || $raw === 'true' ) {
			return true;
		}
	}
	if ( ! empty( $params['jcp_qa'] ) && (string) $params['jcp_qa'] === '1' ) {
		return true;
	}
	$qa = isset( $params['qa_trace_id'] ) ? trim( (string) $params['qa_trace_id'] ) : '';
	if ( $qa !== '' ) {
		return true;
	}
	$utm = isset( $params['utm_source'] ) ? strtolower( trim( (string) $params['utm_source'] ) ) : '';
	return $utm === 'qa';
}

/**
 * Build Proof Gap GHL webhook body.
 *
 * Sends canonical machine keys (business_niche, …) PLUS legacy Title Case aliases
 * the published GHL inbound workflow still maps (Business Type, Jobs Per Week, …).
 * Survey answer values stay machine-readable (hvac, 1_5, phones_camera_roll, 11_25).
 * Empty contact fields are omitted so later onboarding cannot wipe acquisition data via blanks.
 * Tag is proof-gap-lead only (never for QA — caller must skip delivery).
 *
 * @param array<string, mixed>  $params Contact + attribution params.
 * @param array<string, string> $survey Discrete survey fields.
 */
function jcp_proof_gap_build_webhook_body( array $params, array $survey ): string {
	$email = isset( $params['email'] ) ? trim( (string) $params['email'] ) : '';
	// Only pass through an explicit first name — never invent one from the email.
	$first_name = isset( $params['first_name'] ) ? trim( (string) $params['first_name'] ) : '';
	$last_name  = isset( $params['last_name'] ) ? trim( (string) $params['last_name'] ) : '';

	// Machine-readable survey answers — do NOT convert to human labels.
	$business_niche    = isset( $params['business_type'] ) ? trim( (string) $params['business_type'] ) : '';
	$weekly_job_volume = isset( $survey['jobs_per_week'] ) ? trim( (string) $survey['jobs_per_week'] ) : '';
	$photo_workflow    = isset( $survey['photo_workflow'] ) ? trim( (string) $survey['photo_workflow'] ) : '';
	$marketing_usage   = isset( $survey['marketing_usage'] ) ? trim( (string) $survey['marketing_usage'] ) : '';
	$session_id        = isset( $survey['survey_session_id'] ) ? trim( (string) $survey['survey_session_id'] ) : '';

	$annual_min = isset( $survey['annual_jobs_min'] ) ? absint( $survey['annual_jobs_min'] ) : 0;
	$annual_max = isset( $survey['annual_jobs_max'] ) ? absint( $survey['annual_jobs_max'] ) : 0;
	$estimated  = isset( $survey['estimated_jobs_per_year'] ) ? trim( (string) $survey['estimated_jobs_per_year'] ) : '';
	if ( $estimated === '' ) {
		$estimated = jcp_proof_gap_format_estimated_jobs_per_year( $annual_min, $annual_max );
	}
	$unused_pct = isset( $survey['potentially_unused_percent'] ) ? trim( (string) $survey['potentially_unused_percent'] ) : '';
	if ( $unused_pct === '' ) {
		$unused_pct = jcp_proof_gap_format_potentially_unused_percent( $marketing_usage );
	}

	$assessment = isset( $params['use_case'] ) ? trim( (string) $params['use_case'] ) : '';
	$lp_variant = isset( $params['lp_variant'] ) ? trim( (string) $params['lp_variant'] ) : '';
	$jcp_pg     = isset( $params['jcp_pg_variant'] ) ? trim( (string) $params['jcp_pg_variant'] ) : $lp_variant;
	$funnel_ver = isset( $params['funnel_version'] ) ? trim( (string) $params['funnel_version'] ) : '';
	if ( $funnel_ver === '' && defined( 'JCP_PROOF_GAP_FUNNEL_VERSION' ) ) {
		$funnel_ver = (string) JCP_PROOF_GAP_FUNNEL_VERSION;
	}
	if ( $funnel_ver === '' ) {
		$funnel_ver = 'proof_gap_survey_v1';
	}
	$survey_ver = isset( $params['survey_version'] ) ? trim( (string) $params['survey_version'] ) : '';
	if ( $survey_ver === '' && isset( $survey['survey_version'] ) ) {
		$survey_ver = trim( (string) $survey['survey_version'] );
	}
	$ph_id = isset( $params['ph_distinct_id'] ) ? trim( (string) $params['ph_distinct_id'] ) : '';

	$scalar = [
		JCP_GHL_KEY_EVENT        => JCP_PROOF_GAP_GHL_EVENT,
		JCP_GHL_KEY_LAST_NAME    => $last_name,
		JCP_GHL_KEY_EMAIL        => $email,
		JCP_GHL_KEY_PHONE        => isset( $params['phone'] ) ? trim( (string) $params['phone'] ) : '',
		JCP_GHL_KEY_COMPANY      => isset( $params['company'] ) ? trim( (string) $params['company'] ) : '',
		JCP_GHL_KEY_SERVICE_AREA => isset( $params['service_area'] ) ? trim( (string) $params['service_area'] ) : '',
		JCP_GHL_KEY_UTM_SOURCE   => isset( $params['utm_source'] ) ? trim( (string) $params['utm_source'] ) : '',
		JCP_GHL_KEY_UTM_MEDIUM   => isset( $params['utm_medium'] ) ? trim( (string) $params['utm_medium'] ) : '',
		JCP_GHL_KEY_UTM_CAMPAIGN => isset( $params['utm_campaign'] ) ? trim( (string) $params['utm_campaign'] ) : '',
		JCP_GHL_KEY_UTM_CONTENT  => isset( $params['utm_content'] ) ? trim( (string) $params['utm_content'] ) : '',
		JCP_GHL_KEY_UTM_TERM     => isset( $params['utm_term'] ) ? trim( (string) $params['utm_term'] ) : '',
		JCP_GHL_KEY_FBCLID       => isset( $params['fbclid'] ) ? trim( (string) $params['fbclid'] ) : '',
		JCP_GHL_KEY_LANDING_PAGE => isset( $params['landing_page'] ) ? trim( (string) $params['landing_page'] ) : '',
		JCP_GHL_KEY_REFERRER     => isset( $params['referrer'] ) ? trim( (string) $params['referrer'] ) : '',
	];

	if ( defined( 'JCP_GHL_KEY_UTM_ID' ) && ! empty( $params['utm_id'] ) ) {
		$scalar[ JCP_GHL_KEY_UTM_ID ] = trim( (string) $params['utm_id'] );
	}

	// Only include First Name when collected — never fabricate; never send blank (avoids wipe).
	if ( $first_name !== '' ) {
		$scalar[ JCP_GHL_KEY_FIRST_NAME ] = $first_name;
	}

	// Canonical machine keys (new GHL mappings).
	$scalar[ JCP_GHL_KEY_CANONICAL_BUSINESS_NICHE ]     = $business_niche;
	$scalar[ JCP_GHL_KEY_CANONICAL_WEEKLY_JOB_VOLUME ]  = $weekly_job_volume;
	$scalar[ JCP_GHL_KEY_CANONICAL_PHOTO_WORKFLOW ]     = $photo_workflow;
	$scalar[ JCP_GHL_KEY_CANONICAL_MARKETING_USAGE ]    = $marketing_usage;
	$scalar[ JCP_GHL_KEY_CANONICAL_SURVEY_SESSION_ID ]  = $session_id;

	// Legacy Title Case aliases the published inbound webhook still expects.
	// Business Type / Jobs Per Week were the missing mappings in live GHL contacts.
	$scalar[ JCP_GHL_KEY_BUSINESS_TYPE ]       = $business_niche;
	$scalar[ JCP_GHL_KEY_JOBS_PER_WEEK ]       = $weekly_job_volume;
	$scalar[ JCP_GHL_KEY_PHOTO_WORKFLOW ]      = $photo_workflow;
	$scalar[ JCP_GHL_KEY_MARKETING_USAGE ]     = $marketing_usage;
	$scalar[ JCP_GHL_KEY_BUSINESS_NICHE ]      = $business_niche;
	$scalar[ JCP_GHL_KEY_WEEKLY_JOB_VOLUME ]   = $weekly_job_volume;
	$scalar[ JCP_GHL_KEY_SURVEY_SESSION_ID ]   = $session_id;
	$scalar[ JCP_GHL_KEY_ASSESSMENT_NOTES ]    = $assessment;

	if ( $estimated !== '' ) {
		$scalar[ JCP_GHL_KEY_ESTIMATED_JOBS_PER_YEAR ] = $estimated;
	}
	if ( $unused_pct !== '' ) {
		$scalar[ JCP_GHL_KEY_POTENTIALLY_UNUSED_PERCENT ] = $unused_pct;
	}
	if ( $ph_id !== '' ) {
		$scalar[ JCP_GHL_KEY_PH_DISTINCT_ID ] = mb_substr( $ph_id, 0, 256 );
	}
	if ( $lp_variant !== '' && defined( 'JCP_GHL_KEY_LP_VARIANT' ) ) {
		$scalar[ JCP_GHL_KEY_LP_VARIANT ] = $lp_variant;
		$scalar['lp_variant']             = $lp_variant;
	}
	if ( $jcp_pg !== '' ) {
		$scalar[ JCP_GHL_KEY_JCP_PG_VARIANT ] = $jcp_pg;
	}
	if ( $funnel_ver !== '' ) {
		$scalar[ JCP_GHL_KEY_FUNNEL_VERSION ] = $funnel_ver;
	}
	if ( $survey_ver !== '' ) {
		$scalar[ JCP_GHL_KEY_SURVEY_VERSION ] = $survey_ver;
	}
	if ( ! empty( $params['funnel_surface'] ) && defined( 'JCP_GHL_KEY_FUNNEL_SURFACE' ) ) {
		$scalar[ JCP_GHL_KEY_FUNNEL_SURFACE ] = trim( (string) $params['funnel_surface'] );
	}

	$qa = isset( $params['qa_trace_id'] ) ? trim( (string) $params['qa_trace_id'] ) : '';
	if ( $qa !== '' ) {
		$scalar[ JCP_GHL_KEY_QA_TRACE_ID ] = mb_substr( $qa, 0, 80 );
	}
	if ( jcp_proof_gap_request_is_qa( $params ) ) {
		$scalar[ JCP_GHL_KEY_IS_QA ] = 'true';
	}

	// Drop empty scalars so GHL does not overwrite existing contact fields with blanks.
	$body_parts = [];
	foreach ( $scalar as $key => $val ) {
		if ( $val === '' || $val === null ) {
			continue;
		}
		$body_parts[ $key ] = $val;
	}
	$body = http_build_query( $body_parts, '', '&', PHP_QUERY_RFC3986 );

	$referral = isset( $params['referral_source'] ) ? trim( (string) $params['referral_source'] ) : '';
	if ( $referral !== '' && defined( 'JCP_GHL_KEY_REFERRAL_SOURCE' ) ) {
		$body .= '&' . rawurlencode( JCP_GHL_KEY_REFERRAL_SOURCE ) . '%5B%5D=' . rawurlencode( $referral );
	}

	// Tag proof-gap-lead only — never demo-interest / demo-*.
	$body .= '&Tags%5B%5D=' . rawurlencode( JCP_PROOF_GAP_GHL_EVENT );

	$optional = [];
	if ( ! empty( $survey['other_trade'] ) ) {
		$optional['Other Trade'] = (string) $survey['other_trade'];
	}
	if ( ! empty( $survey['other_workflow'] ) ) {
		$optional['Other Workflow'] = (string) $survey['other_workflow'];
	}
	if ( $annual_min > 0 ) {
		$optional['Annual Jobs Min'] = (string) $annual_min;
	}
	if ( $annual_max > 0 ) {
		$optional['Annual Jobs Max'] = (string) $annual_max;
	}
	if ( ! empty( $survey['unused_jobs_min'] ) ) {
		$optional['Unused Jobs Min'] = (string) $survey['unused_jobs_min'];
	}
	if ( ! empty( $survey['unused_jobs_max'] ) ) {
		$optional['Unused Jobs Max'] = (string) $survey['unused_jobs_max'];
	}
	$body = jcp_proof_gap_append_ghl_fields( $body, $optional );

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
	if ( ! function_exists( 'jcp_demo_lead_queue_insert' ) || ! function_exists( 'jcp_proof_gap_build_webhook_body' ) ) {
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

	// Never fabricate First Name from the email local-part (avoids "Hi john.smith82" in nurture).
	$first_name = '';

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
	$survey_version      = sanitize_text_field( (string) $request->get_param( 'survey_version' ) );
	$estimated_jobs      = sanitize_text_field( (string) $request->get_param( 'estimated_jobs_per_year' ) );
	$unused_pct_param    = sanitize_text_field( (string) $request->get_param( 'potentially_unused_percent' ) );

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
		'survey_version'  => $survey_version,
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
	$params['first_name']     = ''; // Re-assert after merge — never invent a name.
	$params['funnel_surface'] = 'proof_gap_survey';
	$params['landing_page']   = ! empty( $params['landing_page'] ) ? $params['landing_page'] : home_url( '/proof-gap/' );
	// Prefer client experiment arm (control|direct_question); do not fall back to funnel id.
	if ( empty( $params['lp_variant'] ) ) {
		$params['lp_variant'] = 'control';
	}
	if ( empty( $params['jcp_pg_variant'] ) ) {
		$params['jcp_pg_variant'] = (string) $params['lp_variant'];
	}
	if ( empty( $params['funnel_version'] ) ) {
		$params['funnel_version'] = defined( 'JCP_PROOF_GAP_FUNNEL_VERSION' )
			? JCP_PROOF_GAP_FUNNEL_VERSION
			: 'proof_gap_survey_v1';
	}
	if ( empty( $params['survey_version'] ) ) {
		$params['survey_version'] = $survey_version;
	}

	$is_qa = jcp_proof_gap_request_is_qa( $params );

	$lp_for_handoff = (string) $params['lp_variant'];

	// Idempotent: same client event_id must not create a second GHL fire.
	$existing = $event_id !== '' ? jcp_proof_gap_find_lead_by_event_id( $event_id ) : null;
	if ( $existing ) {
		$existing_status = (string) ( $existing->status ?? '' );
		$delivered       = $existing_status === 'delivered';
		$skipped_qa      = $existing_status === 'skipped_qa';
		// Never re-attempt delivery for QA-skipped rows, and never deliver QA traffic.
		if ( ! $delivered && ! $skipped_qa && ! $is_qa && function_exists( 'jcp_demo_lead_queue_attempt_row' ) ) {
			$delivered = jcp_demo_lead_queue_attempt_row( $existing );
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
					'lp_variant'              => $lp_for_handoff,
				]
			)
			: '';

		$preview = jcp_proof_gap_webhook_body_to_array( (string) ( $existing->payload ?? '' ) );

		return new WP_REST_Response(
			[
				'success'         => true,
				'captured'        => true,
				'delivered'       => (bool) $delivered,
				'queued'          => ! $delivered && ! $skipped_qa && ! $is_qa,
				'ghl_skipped_qa'  => $skipped_qa || $is_qa,
				'lead_id'         => (int) $existing->id,
				'event_id'        => $event_id,
				'handoff_token'   => $handoff,
				'dedupe'          => true,
				'webhook_payload' => $preview,
			],
			200
		);
	}

	$survey_fields = [
		'jobs_per_week'               => $jobs_bucket,
		'photo_workflow'              => $workflow,
		'marketing_usage'             => $proof_pct,
		'survey_session_id'           => $session_id,
		'survey_version'              => $survey_version,
		'other_trade'                 => $other_trade_text,
		'other_workflow'              => $other_workflow_text,
		'annual_jobs_min'             => $annual_min > 0 ? (string) $annual_min : '',
		'annual_jobs_max'             => $annual_max > 0 ? (string) $annual_max : '',
		'unused_jobs_min'             => $unused_min > 0 ? (string) $unused_min : '',
		'unused_jobs_max'             => $unused_max > 0 ? (string) $unused_max : '',
		'estimated_jobs_per_year'     => $estimated_jobs,
		'potentially_unused_percent'  => $unused_pct_param,
	];
	$body_string = jcp_proof_gap_build_webhook_body( $params, $survey_fields );

	if ( defined( 'JCP_GHL_KEY_EVENT_ID' ) && $event_id !== '' ) {
		$body_string .= '&' . rawurlencode( JCP_GHL_KEY_EVENT_ID ) . '=' . rawurlencode( $event_id );
	}

	$params['event'] = JCP_PROOF_GAP_GHL_EVENT;
	$payload_preview = jcp_proof_gap_webhook_body_to_array( $body_string );

	// QA / test traffic: persist for audit but NEVER fire the production proof-gap-lead webhook.
	if ( $is_qa ) {
		$lead_id = jcp_demo_lead_queue_insert( $params, $body_string, $event_id );
		if ( $lead_id ) {
			global $wpdb;
			$table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->update(
				$table,
				[
					'status'     => 'skipped_qa',
					'updated_at' => current_time( 'mysql' ),
				],
				[ 'id' => (int) $lead_id ],
				[ '%s', '%s' ],
				[ '%d' ]
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
					'lp_variant'              => $lp_for_handoff,
				]
			)
			: '';

		return new WP_REST_Response(
			[
				'success'         => true,
				'captured'        => true,
				'delivered'       => false,
				'queued'          => false,
				'ghl_skipped_qa'  => true,
				'lead_id'         => $lead_id ? (int) $lead_id : 0,
				'event_id'        => $event_id,
				'handoff_token'   => $handoff,
				'webhook_payload' => $payload_preview,
			],
			200
		);
	}

	$lead_id = jcp_demo_lead_queue_insert( $params, $body_string, $event_id );

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
				'lp_variant'              => $lp_for_handoff,
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
			'success'         => true,
			'captured'        => true,
			'delivered'       => (bool) $delivered,
			'queued'          => ! $delivered,
			'ghl_skipped_qa'  => false,
			'lead_id'         => (int) $lead_id,
			'event_id'        => $event_id,
			'handoff_token'   => $handoff,
			'webhook_payload' => $payload_preview,
		],
		200
	);
}
