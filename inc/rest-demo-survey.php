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
        'referrer'     => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ],
        'contact_id'   => [
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'jcp_demo_ghl_sanitize_contact_id',
        ],
    ];
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
 * Normalize demo contact fields for GHL webhook payloads.
 *
 * @param array<string, mixed> $params Request params.
 * @return array{first_name: string, last_name: string, email: string, phone: string, company: string, business_type: string, service_area: string, use_case: string, referral_source: string, utm_source: string, utm_medium: string, utm_campaign: string, utm_content: string, utm_term: string, fbclid: string, landing_page: string, referrer: string, contact_id: string}
 */
function jcp_demo_ghl_normalize_contact_params( array $params ): array {
    $first_name    = isset( $params['first_name'] ) ? trim( (string) $params['first_name'] ) : '';
    $last_name     = isset( $params['last_name'] ) ? trim( (string) $params['last_name'] ) : '';
    $email         = isset( $params['email'] ) ? trim( (string) $params['email'] ) : '';
    $phone         = isset( $params['phone'] ) ? trim( (string) $params['phone'] ) : '';
    $company       = isset( $params['company'] ) ? trim( (string) $params['company'] ) : '';
    $business_type = isset( $params['business_type'] ) ? trim( (string) $params['business_type'] ) : '';
    $service_area  = isset( $params['service_area'] ) ? trim( (string) $params['service_area'] ) : '';
    $referral_source = isset( $params['referral_source'] ) ? trim( (string) $params['referral_source'] ) : '';

    $demo_goals = $params['demo_goals'] ?? [];
    if ( ! is_array( $demo_goals ) ) {
        $demo_goals = [];
    }
    $demo_goals = array_filter( array_map( static function ( $goal ) {
        return trim( (string) $goal );
    }, $demo_goals ) );

    $business_type_label = function_exists( 'jcp_core_early_access_business_type_label' )
        ? jcp_core_early_access_business_type_label( $business_type )
        : $business_type;
    if ( $business_type_label === '' ) {
        $business_type_label = $business_type;
    }

    return [
        'first_name'       => $first_name,
        'last_name'        => $last_name,
        'email'            => $email,
        'phone'            => $phone,
        'company'          => $company,
        'business_type'    => $business_type_label,
        'service_area'     => $service_area,
        'use_case'         => implode( ', ', $demo_goals ),
        'referral_source'  => $referral_source,
        'utm_source'       => isset( $params['utm_source'] ) ? trim( (string) $params['utm_source'] ) : '',
        'utm_medium'       => isset( $params['utm_medium'] ) ? trim( (string) $params['utm_medium'] ) : '',
        'utm_campaign'     => isset( $params['utm_campaign'] ) ? trim( (string) $params['utm_campaign'] ) : '',
        'utm_content'      => isset( $params['utm_content'] ) ? trim( (string) $params['utm_content'] ) : '',
        'utm_term'         => isset( $params['utm_term'] ) ? trim( (string) $params['utm_term'] ) : '',
        'fbclid'           => isset( $params['fbclid'] ) ? trim( (string) $params['fbclid'] ) : '',
        'landing_page'     => isset( $params['landing_page'] ) ? trim( (string) $params['landing_page'] ) : '',
        'referrer'         => isset( $params['referrer'] ) ? trim( (string) $params['referrer'] ) : '',
        'contact_id'       => function_exists( 'jcp_demo_ghl_sanitize_contact_id' )
            ? jcp_demo_ghl_sanitize_contact_id( $params['contact_id'] ?? '' )
            : '',
    ];
}

/**
 * Build application/x-www-form-urlencoded GHL webhook body with contact + event + optional tags.
 *
 * @param string               $event  GHL Event value.
 * @param array<string, mixed> $params Contact request params.
 * @param string[]             $tags   Optional tags.
 */
function jcp_demo_ghl_build_webhook_body( string $event, array $params, array $tags = [] ): string {
    $contact = jcp_demo_ghl_normalize_contact_params( $params );
    $scalar  = [
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
    // When present, GHL workflows should Find/Update this contact instead of creating a duplicate.
    if ( $contact['contact_id'] !== '' && defined( 'JCP_GHL_KEY_CONTACT_ID' ) ) {
        $scalar[ JCP_GHL_KEY_CONTACT_ID ] = $contact['contact_id'];
        // Human-readable alias for easier inbound-webhook field mapping in GHL.
        $scalar['Contact Id'] = $contact['contact_id'];
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
 * Build application/x-www-form-urlencoded body for Demo Survey GHL webhook.
 * Shared contact fields + demo-specific fields. Tags: demo-completed, demo-interest.
 *
 * @param array $params Sanitized request params.
 * @return string
 */
function jcp_core_build_demo_survey_ghl_body( array $params ): string {
    return jcp_demo_ghl_build_webhook_body( 'demo-opt-in', $params, [ 'demo-completed', 'demo-interest' ] );
}

/**
 * Handle Demo Survey form POST: build GHL payload and forward to Demo Survey webhook.
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
            'first_name'    => $first_name,
            'last_name'     => $request->get_param( 'last_name' ),
            'email'         => $email,
            'phone'         => $request->get_param( 'phone' ),
            'company'       => $request->get_param( 'company' ),
            'business_type' => $request->get_param( 'business_type' ),
            'service_area'  => $request->get_param( 'service_area' ),
            'demo_goals'    => $request->get_param( 'demo_goals' ),
            'referral_source' => $request->get_param( 'referral_source' ),
        ],
        $request
    );

    $body_string = jcp_core_build_demo_survey_ghl_body( $params );

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

    $body_string = jcp_core_build_demo_viewed_ghl_body(
        jcp_demo_ghl_merge_attribution_from_request(
            [
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'email'         => $email,
                'company'       => $request->get_param( 'company' ),
                'business_type' => $request->get_param( 'business_type' ),
                'service_area'  => $request->get_param( 'service_area' ),
                'demo_goals'    => $request->get_param( 'demo_goals' ),
                'referral_source' => $request->get_param( 'referral_source' ),
            ],
            $request
        )
    );

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
