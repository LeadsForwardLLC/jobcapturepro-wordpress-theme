<?php
/**
 * REST API: Demo Survey form submission → GoHighLevel webhook (Demo only)
 *
 * Separate from Early Access. Sends application/x-www-form-urlencoded to the
 * Demo Survey webhook. Maps contact fields + demo-specific fields. Applies
 * demo tags (demo-completed, demo-interest); does not apply early-access tag.
 *
 * @package JCP_Core
 */

/**
 * GHL webhook URL for Demo Survey (single workflow).
 * Fired for: gate unlock (Event=demo-opt-in) and launch into live demo (Event=demo-viewed),
 * plus in-demo milestones (demo-run-started, demo-publish-seen, etc.).
 * In GHL use if/then on Event. Tags for viewed are "demo-viewed" (not "viewed-demo").
 */
define( 'JCP_GHL_DEMO_SURVEY_WEBHOOK_URL', 'https://services.leadconnectorhq.com/hooks/kMIwmFm9I7LJPEYo35qi/webhook-trigger/zYfSsYRsSdSdHlD5vqUv' );

/**
 * GHL webhook URL for Proof Gap lead intake (separate from Demo Survey).
 * Workflow: JCP Proof Gap – Create Contact + Tag. Event/tag: proof-gap-lead.
 * Never reuse the demo webhook or demo-interest / demo-viewed tags here.
 */
define( 'JCP_GHL_PROOF_GAP_WEBHOOK_URL', 'https://services.leadconnectorhq.com/hooks/kMIwmFm9I7LJPEYo35qi/webhook-trigger/818e6ce8-348a-419f-aed1-ba8b106a970a' );

/**
 * Register REST routes for Demo Survey.
 */
function jcp_core_register_demo_survey_rest_routes(): void {
    register_rest_route( 'jcp/v1', '/demo-survey-submit', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => 'jcp_core_demo_survey_submit_handler',
        'args'                => [
            'first_name'     => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'last_name'      => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'email'          => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_email',
                'validate_callback' => function ( $value ) {
                    return is_email( $value );
                },
            ],
            'phone'          => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'company'        => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'business_type'  => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'service_area'   => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'demo_goals'     => [
                'required'          => false,
                'type'              => 'array',
                'items'             => [ 'type' => 'string' ],
            ],
            'referral_source' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'event'          => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'survey_session_id' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'survey_version' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'business_type_other' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ] + jcp_demo_ghl_attribution_rest_args(),
    ] );

    register_rest_route( 'jcp/v1', '/demo-viewed-submit', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => 'jcp_core_demo_viewed_submit_handler',
        'args'                => [
            'first_name' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'last_name'  => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email'      => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
                'validate_callback' => function ( $value ) {
                    return is_email( $value );
                },
            ],
            'company'        => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'business_type'  => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'service_area'   => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'demo_goals'     => [
                'required'          => false,
                'type'              => 'array',
                'items'             => [ 'type' => 'string' ],
            ],
            'referral_source' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'survey_session_id' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'survey_version' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'business_type_other' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ] + jcp_demo_ghl_attribution_rest_args(),
    ] );
}

add_action( 'rest_api_init', 'jcp_core_register_demo_survey_rest_routes' );

/**
 * REST args for lead attribution fields (UTMs, landing page, referrer, GHL contact id).
 *
 * @return array<string, array<string, mixed>>
 */
function jcp_demo_ghl_attribution_rest_args(): array {
    return [
        'utm_source'   => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'utm_medium'   => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'utm_campaign' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'utm_content'  => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'utm_term'     => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'fbclid'       => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'landing_page' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'lp_variant'   => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'funnel_surface' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'funnel_version' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'jcp_pg_variant' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'utm_id' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'is_qa' => [
            'required' => false,
        ],
        'jcp_qa' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'referrer'     => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ],
        // Durable conversion attribution (server-side only; NOT forwarded to GHL).
        '_fbp' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        '_fbc' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'qa_trace_id' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'ph_distinct_id' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'first_touch_timestamp' => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'contact_id'   => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'jcp_demo_ghl_sanitize_contact_id',
        ],
        'event_id'     => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
    ];
}

/**
 * Attribution keys accepted on lead POST that are durable for Stripe trial join
 * but must never be copied into the GHL webhook body.
 *
 * @return list<string>
 */
function jcp_demo_lead_conversion_attr_keys(): array {
	return [
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
		'utm_id',
		'fbclid',
		'_fbp',
		'_fbc',
		'qa_trace_id',
		'ph_distinct_id',
		'landing_page',
		'lp_variant',
		'jcp_pg_variant',
		'funnel_surface',
		'funnel_version',
		'referrer',
		'first_touch_timestamp',
		'is_qa',
		'jcp_qa',
	];
}

/**
 * Build durable conversion-attribution JSON for a lead (not sent to GHL).
 *
 * @param array<string, mixed> $params Request params.
 * @return string JSON object.
 */
function jcp_demo_lead_build_attribution_json( array $params ): string {
	$out = [];
	foreach ( jcp_demo_lead_conversion_attr_keys() as $key ) {
		if ( ! isset( $params[ $key ] ) ) {
			continue;
		}
		$val = trim( (string) $params[ $key ] );
		if ( $val === '' ) {
			continue;
		}
		// Bound lengths for cookie / id fields.
		$max = 512;
		if ( in_array( $key, [ '_fbp', '_fbc', 'ph_distinct_id', 'qa_trace_id' ], true ) ) {
			$max = 256;
		}
		if ( $key === 'landing_page' || $key === 'referrer' ) {
			$max = 1024;
		}
		$out[ $key ] = mb_substr( $val, 0, $max );
	}
	return wp_json_encode( $out, JSON_UNESCAPED_SLASHES ) ?: '{}';
}

/**
 * Sanitize / validate a GoHighLevel contact id from the client.
 * Invalid values become empty so webhook payloads omit contactId (legacy create/update path).
 *
 * @param mixed $value Raw value.
 */
function jcp_demo_ghl_sanitize_contact_id( $value ): string {
    $id = trim( sanitize_text_field( (string) $value ) );
    if ( $id === '' || strlen( $id ) < 8 || strlen( $id ) > 64 ) {
        return '';
    }
    if ( ! preg_match( '/^[A-Za-z0-9_-]+$/', $id ) ) {
        return '';
    }
    return $id;
}

/**
 * Merge attribution fields from a REST request into a params array.
 *
 * @param array<string, mixed> $params  Existing params.
 * @param \WP_REST_Request     $request REST request.
 * @return array<string, mixed>
 */
function jcp_demo_ghl_merge_attribution_from_request( array $params, \WP_REST_Request $request ): array {
    foreach ( array_keys( jcp_demo_ghl_attribution_rest_args() ) as $key ) {
        $params[ $key ] = $request->get_param( $key );
    }
    return $params;
}

/**
 * Resolve demo business niche into machine key + legacy display label.
 *
 * Does not invent values: known catalog slugs stay machine-readable; free-text
 * "other" answers become niche=other with the typed text preserved separately.
 *
 * @param string $raw         business_type from the client (slug or free text).
 * @param string $other_text  Optional explicit other free text.
 * @return array{niche: string, label: string, other_text: string}
 */
function jcp_demo_resolve_business_niche( string $raw, string $other_text = '' ): array {
	$raw        = trim( $raw );
	$other_text = trim( $other_text );
	$other_key  = function_exists( 'jcp_core_business_type_other_value' )
		? jcp_core_business_type_other_value()
		: 'other';

	if ( $raw === '' && $other_text === '' ) {
		return [ 'niche' => '', 'label' => '', 'other_text' => '' ];
	}

	$options = function_exists( 'jcp_core_business_type_flat_options' )
		? jcp_core_business_type_flat_options()
		: [];

	foreach ( $options as $opt ) {
		$value = isset( $opt['value'] ) ? (string) $opt['value'] : '';
		$label = isset( $opt['label'] ) ? (string) $opt['label'] : '';
		if ( $value !== '' && $value === $raw ) {
			return [
				'niche'      => $value,
				'label'      => $label !== '' ? $label : $value,
				'other_text' => ( $value === $other_key ) ? $other_text : '',
			];
		}
	}

	foreach ( $options as $opt ) {
		$value = isset( $opt['value'] ) ? (string) $opt['value'] : '';
		$label = isset( $opt['label'] ) ? (string) $opt['label'] : '';
		if ( $label !== '' && strcasecmp( $label, $raw ) === 0 ) {
			return [
				'niche'      => $value,
				'label'      => $label,
				'other_text' => ( $value === $other_key ) ? $other_text : '',
			];
		}
	}

	// Free-text / legacy "other" payloads where the client sent the typed trade only.
	$free = $other_text !== '' ? $other_text : $raw;
	return [
		'niche'      => $other_key,
		'label'      => $free,
		'other_text' => $free,
	];
}

/**
 * Normalize demo contact fields for GHL webhook payloads.
 *
 * @param array<string, mixed> $params Request params.
 * @return array<string, string>
 */
function jcp_demo_ghl_normalize_contact_params( array $params ): array {
    $first_name      = isset( $params['first_name'] ) ? trim( (string) $params['first_name'] ) : '';
    $last_name       = isset( $params['last_name'] ) ? trim( (string) $params['last_name'] ) : '';
    $email           = isset( $params['email'] ) ? trim( (string) $params['email'] ) : '';
    $phone           = isset( $params['phone'] ) ? trim( (string) $params['phone'] ) : '';
    $company         = isset( $params['company'] ) ? trim( (string) $params['company'] ) : '';
    $business_raw    = isset( $params['business_type'] ) ? trim( (string) $params['business_type'] ) : '';
    $business_other  = isset( $params['business_type_other'] ) ? trim( (string) $params['business_type_other'] ) : '';
    $service_area    = isset( $params['service_area'] ) ? trim( (string) $params['service_area'] ) : '';
    $referral_source = isset( $params['referral_source'] ) ? trim( (string) $params['referral_source'] ) : '';

    $demo_goals = $params['demo_goals'] ?? [];
    if ( ! is_array( $demo_goals ) ) {
        $demo_goals = [];
    }
    $demo_goals = array_values( array_filter( array_map( static function ( $goal ) {
        return trim( (string) $goal );
    }, $demo_goals ) ) );

    $resolved = jcp_demo_resolve_business_niche( $business_raw, $business_other );

    $use_case_param = isset( $params['use_case'] ) ? trim( (string) $params['use_case'] ) : '';
    $use_case       = $use_case_param !== '' ? $use_case_param : implode( ', ', $demo_goals );

    $assessment_parts = [];
    if ( $resolved['other_text'] !== '' ) {
        $assessment_parts[] = 'other_trade:' . $resolved['other_text'];
    }
    if ( $demo_goals ) {
        $assessment_parts[] = 'goals:' . implode( ',', $demo_goals );
    }
    $assessment_notes = isset( $params['assessment_notes'] ) ? trim( (string) $params['assessment_notes'] ) : '';
    if ( $assessment_notes === '' && $assessment_parts ) {
        $assessment_notes = implode( ' | ', $assessment_parts );
    }

    $session_id = isset( $params['survey_session_id'] ) ? trim( (string) $params['survey_session_id'] ) : '';
    $funnel_ver = isset( $params['funnel_version'] ) ? trim( (string) $params['funnel_version'] ) : '';
    if ( $funnel_ver === '' && defined( 'JCP_DEMO_FUNNEL_VERSION' ) ) {
        $funnel_ver = (string) JCP_DEMO_FUNNEL_VERSION;
    }
    $survey_ver = isset( $params['survey_version'] ) ? trim( (string) $params['survey_version'] ) : '';
    if ( $survey_ver === '' && defined( 'JCP_DEMO_SURVEY_VERSION' ) ) {
        $survey_ver = (string) JCP_DEMO_SURVEY_VERSION;
    }

    return [
        'first_name'          => $first_name,
        'last_name'           => $last_name,
        'email'               => $email,
        'phone'               => $phone,
        'company'             => $company,
        'business_niche'      => $resolved['niche'],
        'business_type'       => $resolved['label'],
        'business_type_other' => $resolved['other_text'],
        'service_area'        => $service_area,
        // Prefer an explicit use_case; fall back to demo_goals for classic demo opt-in.
        'use_case'            => $use_case,
        'assessment_notes'    => $assessment_notes,
        'referral_source'     => $referral_source,
        'utm_source'          => isset( $params['utm_source'] ) ? trim( (string) $params['utm_source'] ) : '',
        'utm_medium'          => isset( $params['utm_medium'] ) ? trim( (string) $params['utm_medium'] ) : '',
        'utm_campaign'        => isset( $params['utm_campaign'] ) ? trim( (string) $params['utm_campaign'] ) : '',
        'utm_content'         => isset( $params['utm_content'] ) ? trim( (string) $params['utm_content'] ) : '',
        'utm_term'            => isset( $params['utm_term'] ) ? trim( (string) $params['utm_term'] ) : '',
        'utm_id'              => isset( $params['utm_id'] ) ? trim( (string) $params['utm_id'] ) : '',
        'fbclid'              => isset( $params['fbclid'] ) ? trim( (string) $params['fbclid'] ) : '',
        'landing_page'        => isset( $params['landing_page'] ) ? trim( (string) $params['landing_page'] ) : '',
        'lp_variant'          => isset( $params['lp_variant'] ) ? trim( (string) $params['lp_variant'] ) : '',
        'funnel_surface'      => isset( $params['funnel_surface'] ) ? trim( (string) $params['funnel_surface'] ) : '',
        'funnel_version'      => $funnel_ver,
        'survey_version'      => $survey_ver,
        'survey_session_id'   => $session_id,
        'ph_distinct_id'      => isset( $params['ph_distinct_id'] ) ? trim( (string) $params['ph_distinct_id'] ) : '',
        'qa_trace_id'         => isset( $params['qa_trace_id'] ) ? trim( (string) $params['qa_trace_id'] ) : '',
        'referrer'            => isset( $params['referrer'] ) ? trim( (string) $params['referrer'] ) : '',
        'contact_id'          => function_exists( 'jcp_demo_ghl_sanitize_contact_id' )
            ? jcp_demo_ghl_sanitize_contact_id( $params['contact_id'] ?? '' )
            : '',
    ];
}

/**
 * Parse a form-urlencoded GHL webhook body into an associative array (QA audit preview).
 *
 * @param string $body Form-urlencoded body.
 * @return array<string, mixed>
 */
function jcp_demo_ghl_webhook_body_to_array( string $body ): array {
	if ( function_exists( 'jcp_proof_gap_webhook_body_to_array' ) ) {
		return jcp_proof_gap_webhook_body_to_array( $body );
	}
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
 * Persist a demo lead as skipped_qa (never deliver to production GHL).
 *
 * @param array<string, mixed> $params      Contact + attribution.
 * @param string               $body_string Webhook body preview.
 * @param string               $event_id    Meta event id.
 * @return int Lead queue row id (0 on failure).
 */
function jcp_demo_lead_queue_mark_skipped_qa( array $params, string $body_string, string $event_id ): int {
	$lead_id = jcp_demo_lead_queue_insert( $params, $body_string, $event_id );
	if ( ! $lead_id ) {
		return 0;
	}
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
	return (int) $lead_id;
}

/**
 * Build application/x-www-form-urlencoded GHL webhook body with contact + event + optional tags.
 *
 * Sends canonical snake_case keys (business_niche, utm_source, …) PLUS legacy Title Case
 * aliases the published Demo Start inbound webhook still maps (Business Type, UTM Source, …).
 * Only includes fields present in the current demo state — never fabricates qualification answers.
 *
 * @param string               $event  GHL Event value.
 * @param array<string, mixed> $params Contact request params.
 * @param string[]             $tags   Optional tags.
 */
function jcp_demo_ghl_build_webhook_body( string $event, array $params, array $tags = [] ): string {
    $contact = jcp_demo_ghl_normalize_contact_params( $params );
    $is_qa   = function_exists( 'jcp_ghl_request_is_qa' ) && jcp_ghl_request_is_qa( $params );

    // Legacy Title Case keys (existing GHL Demo Start workflow).
    $scalar = [
        JCP_GHL_KEY_EVENT         => $event,
        JCP_GHL_KEY_FIRST_NAME    => $contact['first_name'],
        JCP_GHL_KEY_LAST_NAME     => $contact['last_name'],
        JCP_GHL_KEY_EMAIL         => $contact['email'],
        JCP_GHL_KEY_PHONE         => $contact['phone'],
        JCP_GHL_KEY_COMPANY       => $contact['company'],
        JCP_GHL_KEY_BUSINESS_TYPE => $contact['business_type'],
        JCP_GHL_KEY_SERVICE_AREA  => $contact['service_area'],
        JCP_GHL_KEY_USE_CASE      => $contact['use_case'],
        JCP_GHL_KEY_UTM_SOURCE    => $contact['utm_source'],
        JCP_GHL_KEY_UTM_MEDIUM    => $contact['utm_medium'],
        JCP_GHL_KEY_UTM_CAMPAIGN  => $contact['utm_campaign'],
        JCP_GHL_KEY_UTM_CONTENT   => $contact['utm_content'],
        JCP_GHL_KEY_UTM_TERM      => $contact['utm_term'],
        JCP_GHL_KEY_FBCLID        => $contact['fbclid'],
        JCP_GHL_KEY_LANDING_PAGE  => $contact['landing_page'],
        JCP_GHL_KEY_REFERRER      => $contact['referrer'],
    ];

    // Canonical snake_case contact + attribution (new GHL custom-field mappings).
    if ( defined( 'JCP_GHL_KEY_CANONICAL_FIRST_NAME' ) && $contact['first_name'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_FIRST_NAME ] = $contact['first_name'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_LAST_NAME' ) && $contact['last_name'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_LAST_NAME ] = $contact['last_name'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_EMAIL' ) && $contact['email'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_EMAIL ] = $contact['email'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_PHONE' ) && $contact['phone'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_PHONE ] = $contact['phone'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_COMPANY' ) && $contact['company'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_COMPANY ] = $contact['company'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_BUSINESS_NICHE' ) && $contact['business_niche'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_BUSINESS_NICHE ] = $contact['business_niche'];
    }
    if ( defined( 'JCP_GHL_KEY_BUSINESS_NICHE' ) && $contact['business_niche'] !== '' ) {
        $scalar[ JCP_GHL_KEY_BUSINESS_NICHE ] = $contact['business_niche'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_UTM_SOURCE' ) && $contact['utm_source'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_UTM_SOURCE ] = $contact['utm_source'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_UTM_MEDIUM' ) && $contact['utm_medium'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_UTM_MEDIUM ] = $contact['utm_medium'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_UTM_CAMPAIGN' ) && $contact['utm_campaign'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_UTM_CAMPAIGN ] = $contact['utm_campaign'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_UTM_CONTENT' ) && $contact['utm_content'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_UTM_CONTENT ] = $contact['utm_content'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_UTM_TERM' ) && $contact['utm_term'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_UTM_TERM ] = $contact['utm_term'];
    }
    if ( defined( 'JCP_GHL_KEY_UTM_ID' ) && $contact['utm_id'] !== '' ) {
        $scalar[ JCP_GHL_KEY_UTM_ID ] = $contact['utm_id'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_LANDING_PAGE' ) && $contact['landing_page'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_LANDING_PAGE ] = $contact['landing_page'];
    }
    if ( defined( 'JCP_GHL_KEY_CANONICAL_REFERRER' ) && $contact['referrer'] !== '' ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_REFERRER ] = $contact['referrer'];
    }

    if ( $contact['lp_variant'] !== '' && defined( 'JCP_GHL_KEY_LP_VARIANT' ) ) {
        $scalar[ JCP_GHL_KEY_LP_VARIANT ] = $contact['lp_variant'];
        if ( defined( 'JCP_GHL_KEY_CANONICAL_LP_VARIANT' ) ) {
            $scalar[ JCP_GHL_KEY_CANONICAL_LP_VARIANT ] = $contact['lp_variant'];
        }
    }
    if ( $contact['funnel_surface'] !== '' && defined( 'JCP_GHL_KEY_FUNNEL_SURFACE' ) ) {
        $scalar[ JCP_GHL_KEY_FUNNEL_SURFACE ] = $contact['funnel_surface'];
    }
    if ( $contact['funnel_version'] !== '' && defined( 'JCP_GHL_KEY_FUNNEL_VERSION' ) ) {
        $scalar[ JCP_GHL_KEY_FUNNEL_VERSION ] = $contact['funnel_version'];
    }
    if ( $contact['survey_version'] !== '' && defined( 'JCP_GHL_KEY_SURVEY_VERSION' ) ) {
        $scalar[ JCP_GHL_KEY_SURVEY_VERSION ] = $contact['survey_version'];
    }
    if ( $contact['survey_session_id'] !== '' && defined( 'JCP_GHL_KEY_CANONICAL_SURVEY_SESSION_ID' ) ) {
        $scalar[ JCP_GHL_KEY_CANONICAL_SURVEY_SESSION_ID ] = $contact['survey_session_id'];
        if ( defined( 'JCP_GHL_KEY_SURVEY_SESSION_ID' ) ) {
            $scalar[ JCP_GHL_KEY_SURVEY_SESSION_ID ] = $contact['survey_session_id'];
        }
    }
    if ( $contact['ph_distinct_id'] !== '' && defined( 'JCP_GHL_KEY_PH_DISTINCT_ID' ) ) {
        $scalar[ JCP_GHL_KEY_PH_DISTINCT_ID ] = mb_substr( $contact['ph_distinct_id'], 0, 256 );
    }
    if ( $contact['assessment_notes'] !== '' ) {
        if ( defined( 'JCP_GHL_KEY_ASSESSMENT_NOTES' ) ) {
            $scalar[ JCP_GHL_KEY_ASSESSMENT_NOTES ] = $contact['assessment_notes'];
        }
        if ( defined( 'JCP_GHL_KEY_CANONICAL_ASSESSMENT_NOTES' ) ) {
            $scalar[ JCP_GHL_KEY_CANONICAL_ASSESSMENT_NOTES ] = $contact['assessment_notes'];
        }
    }
    if ( $contact['qa_trace_id'] !== '' && defined( 'JCP_GHL_KEY_QA_TRACE_ID' ) ) {
        $scalar[ JCP_GHL_KEY_QA_TRACE_ID ] = mb_substr( $contact['qa_trace_id'], 0, 80 );
    }
    if ( $is_qa && defined( 'JCP_GHL_KEY_IS_QA' ) ) {
        $scalar[ JCP_GHL_KEY_IS_QA ] = 'true';
    }

    // When present, GHL workflows should Find/Update this contact instead of creating a duplicate.
    if ( $contact['contact_id'] !== '' && defined( 'JCP_GHL_KEY_CONTACT_ID' ) ) {
        $scalar[ JCP_GHL_KEY_CONTACT_ID ] = $contact['contact_id'];
        $scalar['Contact Id']             = $contact['contact_id'];
    }

    $body = http_build_query( $scalar, '', '&', PHP_QUERY_RFC3986 );
    if ( $contact['referral_source'] !== '' ) {
        // Match Early Access: Referral Source[] for GHL multi-select custom fields.
        $body .= '&' . rawurlencode( JCP_GHL_KEY_REFERRAL_SOURCE ) . '%5B%5D=' . rawurlencode( $contact['referral_source'] );
    }
    foreach ( $tags as $tag ) {
        $tag = trim( (string) $tag );
        if ( $tag === '' ) {
            continue;
        }
        $body .= '&Tags%5B%5D=' . rawurlencode( $tag );
    }
    return $body;
}

/**
 * Allowed Event overrides for /demo-survey-submit (same webhook, GHL if/then branches).
 *
 * @return array<string, string[]> Map of event => tags.
 */
function jcp_demo_survey_allowed_events(): array {
    $events = [
        'demo-opt-in'         => [ 'demo-completed', 'demo-interest' ],
        'demo-phone-entered'  => [ 'demo-phone-entered' ],
        'demo-company-enrich' => [ 'demo-company-enrich' ],
    ];
    /**
     * Filter allowed demo-survey GHL Event overrides (event => tags).
     *
     * @param array<string, list<string>> $events Map.
     */
    return apply_filters( 'jcp_demo_survey_allowed_events', $events );
}

/**
 * Build application/x-www-form-urlencoded body for Demo Survey GHL webhook.
 * Default Event=demo-opt-in. Pass event=demo-phone-entered to update phone + SMS branch.
 *
 * @param array $params Sanitized request params (optional event key).
 * @return string
 */
function jcp_core_build_demo_survey_ghl_body( array $params ): string {
    $allowed = jcp_demo_survey_allowed_events();
    $event   = isset( $params['event'] ) ? sanitize_text_field( (string) $params['event'] ) : 'demo-opt-in';
    if ( ! isset( $allowed[ $event ] ) ) {
        $event = 'demo-opt-in';
    }
    return jcp_demo_ghl_build_webhook_body( $event, $params, $allowed[ $event ] );
}

/** Durable lead queue table (without $wpdb prefix). */
define( 'JCP_DEMO_LEAD_QUEUE_TABLE', 'jcp_demo_lead_queue' );

/** Max automatic GHL delivery attempts before permanent failure. */
define( 'JCP_DEMO_LEAD_QUEUE_MAX_ATTEMPTS', 8 );

/**
 * Create durable demo-lead queue table if missing.
 */
function jcp_demo_lead_queue_maybe_create_table(): void {
	global $wpdb;
	$table   = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(255) NOT NULL,
		business_type varchar(255) DEFAULT NULL,
		event_name varchar(64) NOT NULL DEFAULT 'demo-opt-in',
		event_id varchar(64) NOT NULL DEFAULT '',
		payload longtext NOT NULL,
		attribution_json longtext DEFAULT NULL,
		status varchar(20) NOT NULL DEFAULT 'pending',
		attempts int(11) NOT NULL DEFAULT 0,
		last_error text DEFAULT NULL,
		last_http_code int(11) DEFAULT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		delivered_at datetime DEFAULT NULL,
		next_attempt_at datetime DEFAULT NULL,
		PRIMARY KEY (id),
		KEY status_next (status, next_attempt_at),
		KEY email (email),
		KEY event_id (event_id)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	// Soft-add attribution_json for installs that already had the table.
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$col = $wpdb->get_results( "SHOW COLUMNS FROM `$table` LIKE 'attribution_json'" );
	if ( empty( $col ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE `$table` ADD COLUMN attribution_json longtext DEFAULT NULL AFTER payload" );
	}
}

/**
 * Generate a stable Meta/GTM event id for Lead dedup.
 */
function jcp_demo_lead_new_event_id(): string {
	if ( function_exists( 'wp_generate_uuid4' ) ) {
		return wp_generate_uuid4();
	}
	return 'jcp_' . bin2hex( random_bytes( 16 ) );
}

/**
 * Accept a client-supplied Meta event_id when valid; otherwise mint one.
 *
 * @param mixed $raw Raw request value.
 */
function jcp_demo_lead_resolve_event_id( $raw ): string {
	$id = trim( sanitize_text_field( (string) $raw ) );
	if ( $id !== '' && preg_match( '/^[A-Za-z0-9_-]{8,64}$/', $id ) ) {
		return $id;
	}
	return jcp_demo_lead_new_event_id();
}

/**
 * Persist a demo survey lead before CRM delivery is considered safe.
 *
 * @param array<string, mixed> $params Normalized request params.
 * @param string               $body   GHL form-urlencoded body.
 * @param string               $event_id Meta event id.
 * @return int|false Insert id or false.
 */
function jcp_demo_lead_queue_insert( array $params, string $body, string $event_id ) {
	global $wpdb;
	jcp_demo_lead_queue_maybe_create_table();
	$table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;

	$contact = jcp_demo_ghl_normalize_contact_params( $params );
	$event   = isset( $params['event'] ) ? sanitize_text_field( (string) $params['event'] ) : 'demo-opt-in';
	$allowed = jcp_demo_survey_allowed_events();
	if ( ! isset( $allowed[ $event ] ) ) {
		$event = 'demo-opt-in';
	}

	$attribution_json = function_exists( 'jcp_demo_lead_build_attribution_json' )
		? jcp_demo_lead_build_attribution_json( $params )
		: '{}';

	$now = current_time( 'mysql' );
	$ok  = $wpdb->insert(
		$table,
		[
			'email'             => $contact['email'],
			'business_type'     => $contact['business_type'],
			'event_name'        => $event,
			'event_id'          => $event_id,
			'payload'           => $body,
			'attribution_json'  => $attribution_json,
			'status'            => 'pending',
			'attempts'          => 0,
			'created_at'        => $now,
			'updated_at'        => $now,
			'next_attempt_at'   => $now,
		],
		[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ]
	);

	if ( ! $ok ) {
		error_log( 'JCP demo lead queue insert failed: ' . (string) $wpdb->last_error );
		return false;
	}
	return (int) $wpdb->insert_id;
}

/**
 * Resolve GHL webhook URL for a queued lead event.
 *
 * Proof Gap leads must never hit the Demo Survey webhook.
 *
 * @param string $event_name Queue event_name.
 */
function jcp_demo_lead_queue_webhook_url_for_event( string $event_name ): string {
	$proof_gap = [ 'proof-gap-lead', 'proof-gap-survey' ];
	if ( in_array( $event_name, $proof_gap, true ) && defined( 'JCP_GHL_PROOF_GAP_WEBHOOK_URL' ) ) {
		return JCP_GHL_PROOF_GAP_WEBHOOK_URL;
	}
	return JCP_GHL_DEMO_SURVEY_WEBHOOK_URL;
}

/**
 * POST a queued payload to a GHL inbound webhook.
 *
 * @param string $body        Form-urlencoded body.
 * @param string $webhook_url Absolute webhook URL (defaults to Demo Survey).
 * @return array{ok:bool,code:int,error:string}
 */
function jcp_demo_lead_queue_deliver_body( string $body, string $webhook_url = '' ): array {
	$url = $webhook_url !== '' ? $webhook_url : JCP_GHL_DEMO_SURVEY_WEBHOOK_URL;
	$response = wp_remote_post(
		$url,
		[
			'timeout' => 15,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			'body'    => $body,
		]
	);

	if ( is_wp_error( $response ) ) {
		return [
			'ok'    => false,
			'code'  => 0,
			'error' => $response->get_error_message(),
		];
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$ok   = $code >= 200 && $code < 300;
	$err  = '';
	if ( ! $ok ) {
		$res_body = wp_remote_retrieve_body( $response );
		$decoded  = json_decode( $res_body, true );
		if ( is_array( $decoded ) && isset( $decoded['message'] ) && is_string( $decoded['message'] ) ) {
			$err = $decoded['message'];
		} else {
			$err = $res_body !== '' ? substr( $res_body, 0, 500 ) : ( 'HTTP ' . $code );
		}
	}

	return [
		'ok'    => $ok,
		'code'  => $code,
		'error' => $err,
	];
}

/**
 * Mark a queue row delivered or schedule the next retry / permanent failure.
 *
 * @param int                  $id      Queue row id.
 * @param array{ok:bool,code:int,error:string} $result Delivery result.
 * @param int                  $attempts Attempts after this try.
 */
function jcp_demo_lead_queue_mark_result( int $id, array $result, int $attempts ): void {
	global $wpdb;
	$table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
	$now   = current_time( 'mysql' );

	if ( ! empty( $result['ok'] ) ) {
		$wpdb->update(
			$table,
			[
				'status'         => 'delivered',
				'attempts'       => $attempts,
				'last_error'     => null,
				'last_http_code' => (int) $result['code'],
				'updated_at'     => $now,
				'delivered_at'   => $now,
				'next_attempt_at'=> null,
			],
			[ 'id' => $id ],
			[ '%s', '%d', '%s', '%d', '%s', '%s', '%s' ],
			[ '%d' ]
		);
		return;
	}

	$max = (int) JCP_DEMO_LEAD_QUEUE_MAX_ATTEMPTS;
	if ( $attempts >= $max ) {
		$wpdb->update(
			$table,
			[
				'status'         => 'failed',
				'attempts'       => $attempts,
				'last_error'     => (string) $result['error'],
				'last_http_code' => (int) $result['code'],
				'updated_at'     => $now,
				'next_attempt_at'=> null,
			],
			[ 'id' => $id ],
			[ '%s', '%d', '%s', '%d', '%s', '%s' ],
			[ '%d' ]
		);
		error_log(
			'JCP demo lead PERMANENT FAIL id=' . $id
			. ' http=' . (int) $result['code']
			. ' err=' . (string) $result['error']
		);
		$failed = get_option( 'jcp_demo_lead_permanent_failures', [] );
		if ( ! is_array( $failed ) ) {
			$failed = [];
		}
		$failed[] = [
			'id'        => $id,
			'at'        => $now,
			'http_code' => (int) $result['code'],
			'error'     => (string) $result['error'],
		];
		update_option( 'jcp_demo_lead_permanent_failures', array_slice( $failed, -50 ), false );
		return;
	}

	// Backoff: 2, 5, 15, 30, 60, 120, 240 minutes.
	$delays = [ 2, 5, 15, 30, 60, 120, 240 ];
	$mins   = $delays[ min( $attempts - 1, count( $delays ) - 1 ) ];
	$next   = gmdate( 'Y-m-d H:i:s', time() + ( $mins * MINUTE_IN_SECONDS ) );
	// Store in site local time for WP cron comparisons via current_time.
	$next_local = get_date_from_gmt( $next );

	$wpdb->update(
		$table,
		[
			'status'         => 'pending',
			'attempts'       => $attempts,
			'last_error'     => (string) $result['error'],
			'last_http_code' => (int) $result['code'],
			'updated_at'     => $now,
			'next_attempt_at'=> $next_local,
		],
		[ 'id' => $id ],
		[ '%s', '%d', '%s', '%d', '%s', '%s' ],
		[ '%d' ]
	);
}

/**
 * Attempt GHL delivery for one queue row and update status.
 *
 * @param object $row Queue row.
 * @return bool True when delivered.
 */
function jcp_demo_lead_queue_attempt_row( $row ): bool {
	global $wpdb;
	if ( ! $row || empty( $row->id ) || empty( $row->payload ) ) {
		return false;
	}
	$event_name = isset( $row->event_name ) ? (string) $row->event_name : '';
	$url        = function_exists( 'jcp_demo_lead_queue_webhook_url_for_event' )
		? jcp_demo_lead_queue_webhook_url_for_event( $event_name )
		: JCP_GHL_DEMO_SURVEY_WEBHOOK_URL;
	$result     = jcp_demo_lead_queue_deliver_body( (string) $row->payload, $url );
	$attempts   = (int) $row->attempts + 1;
	jcp_demo_lead_queue_mark_result( (int) $row->id, $result, $attempts );
	return ! empty( $result['ok'] );
}

/**
 * Cron: retry pending demo leads that are due.
 */
function jcp_demo_lead_queue_cron_retry(): void {
	global $wpdb;
	jcp_demo_lead_queue_maybe_create_table();
	$table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
	$now   = current_time( 'mysql' );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE status = 'pending' AND (next_attempt_at IS NULL OR next_attempt_at <= %s) ORDER BY id ASC LIMIT 25",
			$now
		)
	);
	if ( ! is_array( $rows ) ) {
		return;
	}
	foreach ( $rows as $row ) {
		jcp_demo_lead_queue_attempt_row( $row );
	}
}

/**
 * Schedule lead-queue retry cron (every 5 minutes).
 */
function jcp_demo_lead_queue_schedule_cron(): void {
	if ( ! wp_next_scheduled( 'jcp_demo_lead_queue_retry' ) ) {
		wp_schedule_event( time() + 60, 'five_minutes', 'jcp_demo_lead_queue_retry' );
	}
}

/**
 * Register a five_minutes cron schedule.
 *
 * @param array<string, array{interval:int,display:string}> $schedules Schedules.
 * @return array<string, array{interval:int,display:string}>
 */
function jcp_demo_lead_queue_cron_schedules( array $schedules ): array {
	if ( ! isset( $schedules['five_minutes'] ) ) {
		$schedules['five_minutes'] = [
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => 'Every five minutes',
		];
	}
	return $schedules;
}

add_filter( 'cron_schedules', 'jcp_demo_lead_queue_cron_schedules' );
add_action( 'jcp_demo_lead_queue_retry', 'jcp_demo_lead_queue_cron_retry' );
add_action( 'init', 'jcp_demo_lead_queue_schedule_cron' );
add_action( 'after_switch_theme', 'jcp_demo_lead_queue_maybe_create_table' );
add_action( 'after_switch_theme', 'jcp_demo_lead_queue_schedule_cron' );

/**
 * Handle Demo Survey form POST: persist lead, attempt GHL, queue retry on failure.
 * Soft-continue UX is client-side; this endpoint never silently discards a valid lead.
 *
 * @param \WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function jcp_core_demo_survey_submit_handler( \WP_REST_Request $request ): \WP_REST_Response {
    $first_name = $request->get_param( 'first_name' );
    $email      = $request->get_param( 'email' );

    if ( empty( trim( (string) $first_name ) ) || empty( trim( (string) $email ) ) ) {
        if ( empty( trim( (string) $email ) ) ) {
            return new \WP_REST_Response(
                [ 'success' => false, 'message' => __( 'Work email is required.', 'jcp-core' ) ],
                400
            );
        }
        if ( empty( trim( (string) $first_name ) ) ) {
            $local      = sanitize_text_field( (string) strstr( (string) $email, '@', true ) );
            $first_name = $local !== '' ? $local : 'there';
        }
    }

    $params = jcp_demo_ghl_merge_attribution_from_request(
        [
            'first_name'          => $first_name,
            'last_name'           => $request->get_param( 'last_name' ),
            'email'               => $email,
            'phone'               => $request->get_param( 'phone' ),
            'company'             => $request->get_param( 'company' ),
            'business_type'       => $request->get_param( 'business_type' ),
            'business_type_other' => $request->get_param( 'business_type_other' ),
            'service_area'        => $request->get_param( 'service_area' ),
            'demo_goals'          => $request->get_param( 'demo_goals' ),
            'referral_source'     => $request->get_param( 'referral_source' ),
            'event'               => $request->get_param( 'event' ),
            'survey_session_id'   => $request->get_param( 'survey_session_id' ),
            'survey_version'      => $request->get_param( 'survey_version' ),
        ],
        $request
    );

    $body_string = jcp_core_build_demo_survey_ghl_body( $params );
    $event_id    = jcp_demo_lead_resolve_event_id( $request->get_param( 'event_id' ) );
    // Persist Meta event_id on the GHL webhook body for CAPI workflows that map Event Id.
    if ( defined( 'JCP_GHL_KEY_EVENT_ID' ) && $event_id !== '' ) {
        $body_string .= '&' . rawurlencode( JCP_GHL_KEY_EVENT_ID ) . '=' . rawurlencode( $event_id );
    }

    $payload_preview = jcp_demo_ghl_webhook_body_to_array( $body_string );
    $is_qa           = function_exists( 'jcp_ghl_request_is_qa' ) && jcp_ghl_request_is_qa( $params );

    // QA / test traffic: persist for audit but NEVER fire the production Demo Survey webhook.
    if ( $is_qa ) {
        $lead_id = jcp_demo_lead_queue_mark_skipped_qa( $params, $body_string, $event_id );
        return new \WP_REST_Response(
            [
                'success'         => true,
                'captured'        => true,
                'delivered'       => false,
                'queued'          => false,
                'ghl_skipped_qa'  => true,
                'lead_id'         => $lead_id,
                'event_id'        => $event_id,
                'webhook_payload' => $payload_preview,
            ],
            200
        );
    }

    $lead_id = jcp_demo_lead_queue_insert( $params, $body_string, $event_id );

    if ( ! $lead_id ) {
        return new \WP_REST_Response(
            [
                'success'  => false,
                'captured' => false,
                'message'  => __( 'Could not save your info. Please try again.', 'jcp-core' ),
            ],
            500
        );
    }

    global $wpdb;
    $table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;
    $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $lead_id ) );
    $delivered = $row ? jcp_demo_lead_queue_attempt_row( $row ) : false;

    return new \WP_REST_Response(
        [
            'success'   => true,
            'captured'  => true,
            'delivered' => (bool) $delivered,
            'queued'    => ! $delivered,
            'lead_id'   => (int) $lead_id,
            'event_id'  => $event_id,
        ],
        200
    );
}

/**
 * Build application/x-www-form-urlencoded body for "viewed demo" hit (same webhook, Event=demo-viewed).
 *
 * @param array<string, mixed> $params Contact request params.
 * @return string
 */
function jcp_core_build_demo_viewed_ghl_body( array $params ): string {
    return jcp_demo_ghl_build_webhook_body( 'demo-viewed', $params, [ 'demo-viewed' ] );
}

/**
 * Build contact params for GHL from a REST request (with metadata fallbacks).
 *
 * @param \WP_REST_Request     $request  REST request.
 * @param array<string, mixed>|null $metadata Optional event metadata.
 * @return array<string, mixed>
 */
function jcp_demo_ghl_contact_params_from_request( \WP_REST_Request $request, $metadata = null ): array {
    $params = jcp_demo_ghl_merge_attribution_from_request(
        [
            'first_name'    => $request->get_param( 'first_name' ),
            'last_name'     => $request->get_param( 'last_name' ),
            'email'         => $request->get_param( 'email' ),
            'company'       => $request->get_param( 'company' ),
            'business_type' => $request->get_param( 'business_type' ),
            'service_area'  => $request->get_param( 'service_area' ),
            'demo_goals'    => $request->get_param( 'demo_goals' ),
            'referral_source' => $request->get_param( 'referral_source' ),
        ],
        $request
    );

    if ( ! is_array( $metadata ) ) {
        return $params;
    }

    if ( trim( (string) $params['company'] ) === '' && ! empty( $metadata['company'] ) ) {
        $params['company'] = $metadata['company'];
    }
    if ( trim( (string) $params['business_type'] ) === '' && ! empty( $metadata['business_type'] ) ) {
        $params['business_type'] = $metadata['business_type'];
    }
    if ( ( ! is_array( $params['demo_goals'] ) || empty( $params['demo_goals'] ) ) && ! empty( $metadata['demo_goals'] ) && is_array( $metadata['demo_goals'] ) ) {
        $params['demo_goals'] = $metadata['demo_goals'];
    }
    if ( trim( (string) ( $params['referral_source'] ?? '' ) ) === '' && ! empty( $metadata['referral_source'] ) ) {
        $params['referral_source'] = $metadata['referral_source'];
    }

    return $params;
}

/**
 * Map a stored demo analytics event to a GHL webhook Event + tags (or null if not forwarded).
 *
 * @param string       $event_type Analytics event type.
 * @param array|null   $metadata   Event metadata from the client.
 * @return array{event: string, tags: string[]}|null
 */
function jcp_demo_ghl_milestone_mapping( string $event_type, $metadata ): ?array {
    switch ( $event_type ) {
        case 'demo_run_started':
            return [
                'event' => 'demo-run-started',
                'tags'  => [ 'demo-run-started' ],
            ];
        case 'demo_publish_completed':
            return [
                'event' => 'demo-publish-seen',
                'tags'  => [ 'demo-publish-seen' ],
            ];
        case 'demo_review_sent':
            return [
                'event' => 'demo-review-sent',
                'tags'  => [ 'demo-review-sent' ],
            ];
        case 'demo_outcomes_opened':
            return [
                'event' => 'demo-outcomes-opened',
                'tags'  => [ 'demo-outcomes-opened' ],
            ];
        case 'post_demo_modal_shown':
            return [
                'event' => 'demo-finished',
                'tags'  => [ 'demo-finished' ],
            ];
        case 'demo_converted':
            return [
                'event' => 'demo-converted',
                'tags'  => [ 'demo-converted' ],
            ];
        case 'cta_clicked':
            $cta = is_array( $metadata ) && isset( $metadata['cta'] ) ? (string) $metadata['cta'] : '';
            if ( in_array( $cta, [ 'view_directory', 'view_main_directory' ], true ) ) {
                return [
                    'event' => 'demo-cta-directory',
                    'tags'  => [ 'demo-cta-directory' ],
                ];
            }
            if ( $cta === 'personalized_demo' ) {
                return [
                    'event' => 'demo-cta-personalized',
                    'tags'  => [ 'demo-cta-personalized' ],
                ];
            }
            if ( $cta === 'phone_save' ) {
                return [
                    'event' => 'demo-phone-entered',
                    'tags'  => [ 'demo-phone-entered' ],
                ];
            }
            // get_started_free is covered by demo_converted (same click also fires that event).
            return null;
        default:
            return null;
    }
}

/**
 * Whether this is the first analytics row of its kind for the session (dedupe GHL forwards).
 *
 * @param string     $session_id Session ID.
 * @param string     $event_type Analytics event type.
 * @param array|null $metadata   Event metadata.
 */
function jcp_demo_ghl_milestone_is_first_for_session( string $session_id, string $event_type, $metadata ): bool {
    global $wpdb;
    $table = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;

    if ( $event_type === 'cta_clicked' ) {
        $cta = is_array( $metadata ) && isset( $metadata['cta'] ) ? (string) $metadata['cta'] : '';
        if ( $cta === '' ) {
            return false;
        }
        // Directory CTAs share one GHL milestone bucket.
        if ( in_array( $cta, [ 'view_directory', 'view_main_directory' ], true ) ) {
            $count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE session_id = %s AND event_type = 'cta_clicked' AND (metadata LIKE %s OR metadata LIKE %s)",
                    $session_id,
                    '%"cta":"view_directory"%',
                    '%"cta":"view_main_directory"%'
                )
            );
            return $count === 1;
        }
        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE session_id = %s AND event_type = 'cta_clicked' AND metadata LIKE %s",
                $session_id,
                '%"cta":"' . $wpdb->esc_like( $cta ) . '"%'
            )
        );
        return $count === 1;
    }

    $count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE session_id = %s AND event_type = %s",
            $session_id,
            $event_type
        )
    );

    return $count === 1;
}

/**
 * Build GHL webhook body for a demo milestone (find-contact branches in GHL Demo Start workflow).
 *
 * @param string               $event  GHL Event value.
 * @param array<string, mixed> $params Contact request params.
 * @param string[]             $tags   Tags to include in payload.
 */
function jcp_demo_ghl_build_milestone_body( string $event, array $params, array $tags ): string {
    return jcp_demo_ghl_build_webhook_body( $event, $params, $tags );
}

/**
 * Forward a demo analytics milestone to the Demo Survey GHL webhook when mapped.
 *
 * @param string               $session_id      Session ID.
 * @param string               $event_type      Analytics event type.
 * @param array|null           $metadata        Event metadata.
 * @param array<string, mixed> $contact_params  Contact fields for GHL.
 */
function jcp_demo_ghl_maybe_forward_demo_milestone(
    string $session_id,
    string $event_type,
    $metadata,
    array $contact_params
): void {
    if ( ! defined( 'JCP_GHL_DEMO_SURVEY_WEBHOOK_URL' ) ) {
        return;
    }

    // Never forward QA / test traffic into the production Demo Survey workflow.
    if ( function_exists( 'jcp_ghl_request_is_qa' ) && jcp_ghl_request_is_qa( $contact_params ) ) {
        return;
    }

    $contact = jcp_demo_ghl_normalize_contact_params( $contact_params );
    if ( $contact['email'] === '' || ! is_email( $contact['email'] ) ) {
        return;
    }

    $mapping = jcp_demo_ghl_milestone_mapping( $event_type, $metadata );
    if ( $mapping === null ) {
        return;
    }

    if ( ! jcp_demo_ghl_milestone_is_first_for_session( $session_id, $event_type, $metadata ) ) {
        return;
    }

    $body_string = jcp_demo_ghl_build_milestone_body(
        $mapping['event'],
        $contact_params,
        $mapping['tags']
    );

    $response = wp_remote_post(
        JCP_GHL_DEMO_SURVEY_WEBHOOK_URL,
        [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body_string,
        ]
    );

    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        $code = wp_remote_retrieve_response_code( $response );
        error_log(
            'JCP Demo GHL milestone: event=' . $mapping['event']
            . ' email=' . $contact['email']
            . ' company=' . $contact['company']
            . ' http=' . (string) $code
        );
    }
}

/**
 * Handle Demo Viewed POST: forward to same GHL webhook with Event=demo-viewed for if/then branching.
 *
 * @param \WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function jcp_core_demo_viewed_submit_handler( \WP_REST_Request $request ): \WP_REST_Response {
    $first_name = trim( (string) $request->get_param( 'first_name' ) );
    $last_name  = trim( (string) $request->get_param( 'last_name' ) );
    $email      = trim( (string) $request->get_param( 'email' ) );

    if ( $email === '' || ! is_email( $email ) ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'Work email is required.', 'jcp-core' ) ],
            400
        );
    }
    if ( $first_name === '' ) {
        $local      = sanitize_text_field( (string) strstr( $email, '@', true ) );
        $first_name = $local !== '' ? $local : 'there';
    }

    $params = jcp_demo_ghl_merge_attribution_from_request(
        [
            'first_name'          => $first_name,
            'last_name'           => $last_name,
            'email'               => $email,
            'company'             => $request->get_param( 'company' ),
            'business_type'       => $request->get_param( 'business_type' ),
            'business_type_other' => $request->get_param( 'business_type_other' ),
            'service_area'        => $request->get_param( 'service_area' ),
            'demo_goals'          => $request->get_param( 'demo_goals' ),
            'referral_source'     => $request->get_param( 'referral_source' ),
            'survey_session_id'   => $request->get_param( 'survey_session_id' ),
            'survey_version'      => $request->get_param( 'survey_version' ),
        ],
        $request
    );

    $body_string     = jcp_core_build_demo_viewed_ghl_body( $params );
    $payload_preview = jcp_demo_ghl_webhook_body_to_array( $body_string );
    $is_qa           = function_exists( 'jcp_ghl_request_is_qa' ) && jcp_ghl_request_is_qa( $params );

    if ( $is_qa ) {
        $event_id = jcp_demo_lead_resolve_event_id( $request->get_param( 'event_id' ) );
        $params['event'] = 'demo-viewed';
        $lead_id = jcp_demo_lead_queue_mark_skipped_qa( $params, $body_string, $event_id );
        return new \WP_REST_Response(
            [
                'success'         => true,
                'captured'        => true,
                'delivered'       => false,
                'ghl_skipped_qa'  => true,
                'lead_id'         => $lead_id,
                'webhook_payload' => $payload_preview,
            ],
            200
        );
    }

    $response = wp_remote_post(
        JCP_GHL_DEMO_SURVEY_WEBHOOK_URL,
        [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body_string,
        ]
    );

    $code = wp_remote_retrieve_response_code( $response );
    $res_body = wp_remote_retrieve_body( $response );
    $ok = $code >= 200 && $code < 300;

    if ( $ok ) {
        return new \WP_REST_Response( [ 'success' => true ], 200 );
    }

    $msg = __( 'Something went wrong. Please try again.', 'jcp-core' );
    if ( $res_body !== '' ) {
        $decoded = json_decode( $res_body, true );
        if ( is_array( $decoded ) && isset( $decoded['message'] ) && is_string( $decoded['message'] ) ) {
            $msg = $decoded['message'];
        }
    }

    return new \WP_REST_Response( [ 'success' => false, 'message' => $msg ], 400 );
}
