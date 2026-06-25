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
 * Fired for both: "Continue to preview" (Event=opt-in) and "Skip to demo" / "Launch the live demo" (Event=viewed-demo).
 * In GHL use an if/then: if Event = "demo-viewed" → Find Contact by Email → Add Tag "viewed-demo"; else (Event = "demo-opt-in") → Create Contact → Add tag (e.g. demo-opt-in).
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
        ],
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
        ],
    ] );
}

add_action( 'rest_api_init', 'jcp_core_register_demo_survey_rest_routes' );

/**
 * Build application/x-www-form-urlencoded body for Demo Survey GHL webhook.
 * Shared contact fields + demo-specific fields. Tags: demo-completed, demo-interest.
 *
 * @param array $params Sanitized request params.
 * @return string
 */
function jcp_core_build_demo_survey_ghl_body( array $params ): string {
    $first_name    = isset( $params['first_name'] ) ? trim( (string) $params['first_name'] ) : '';
    $last_name     = isset( $params['last_name'] ) ? trim( (string) $params['last_name'] ) : '';
    $email         = isset( $params['email'] ) ? trim( (string) $params['email'] ) : '';
    $phone         = isset( $params['phone'] ) ? trim( (string) $params['phone'] ) : '';
    $company       = isset( $params['company'] ) ? trim( (string) $params['company'] ) : '';
    $business_type = isset( $params['business_type'] ) ? trim( (string) $params['business_type'] ) : '';
    $service_area  = isset( $params['service_area'] ) ? trim( (string) $params['service_area'] ) : '';
    $demo_goals    = isset( $params['demo_goals'] ) && is_array( $params['demo_goals'] )
        ? array_filter( array_map( 'trim', $params['demo_goals'] ) )
        : [];

    $business_type_label = function_exists( 'jcp_core_early_access_business_type_label' )
        ? jcp_core_early_access_business_type_label( $business_type )
        : $business_type;
    if ( $business_type_label === '' ) {
        $business_type_label = $business_type;
    }

    $scalar = [
        JCP_GHL_KEY_EVENT          => 'demo-opt-in',
        JCP_GHL_KEY_FIRST_NAME     => $first_name,
        JCP_GHL_KEY_LAST_NAME      => $last_name,
        JCP_GHL_KEY_EMAIL          => $email,
        JCP_GHL_KEY_PHONE          => $phone,
        JCP_GHL_KEY_COMPANY        => $company,
        JCP_GHL_KEY_BUSINESS_TYPE  => $business_type_label,
        JCP_GHL_KEY_SERVICE_AREA   => $service_area,
        JCP_GHL_KEY_USE_CASE       => implode( ', ', $demo_goals ),
    ];
    $body = http_build_query( $scalar, '', '&', PHP_QUERY_RFC3986 );

    $tags = [ 'demo-completed', 'demo-interest' ];
    foreach ( $tags as $tag ) {
        $body .= '&Tags%5B%5D=' . rawurlencode( $tag );
    }

    return $body;
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
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'First name, last name, and email are required.', 'jcp-core' ) ],
            400
        );
    }

    $params = [
        'first_name'    => $first_name,
        'last_name'     => $request->get_param( 'last_name' ),
        'email'         => $email,
        'phone'         => $request->get_param( 'phone' ),
        'company'       => $request->get_param( 'company' ),
        'business_type' => $request->get_param( 'business_type' ),
        'service_area'  => $request->get_param( 'service_area' ),
        'demo_goals'    => $request->get_param( 'demo_goals' ),
    ];

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
 * GHL can branch on Event: if "demo-viewed" → Find Contact by Email → Add Tag "demo-viewed".
 *
 * @param string $first_name First name.
 * @param string $last_name  Last name.
 * @param string $email      Email.
 * @return string
 */
function jcp_core_build_demo_viewed_ghl_body( string $first_name, string $last_name, string $email ): string {
    $scalar = [
        JCP_GHL_KEY_EVENT      => 'demo-viewed',
        JCP_GHL_KEY_FIRST_NAME => $first_name,
        JCP_GHL_KEY_LAST_NAME  => $last_name,
        JCP_GHL_KEY_EMAIL      => $email,
    ];
    $body = http_build_query( $scalar, '', '&', PHP_QUERY_RFC3986 );
    $body .= '&Tags%5B%5D=' . rawurlencode( 'demo-viewed' );
    return $body;
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
            if ( ! in_array( $cta, [ 'view_directory', 'view_main_directory' ], true ) ) {
                return null;
            }
            return [
                'event' => 'demo-cta-directory',
                'tags'  => [ 'demo-cta-directory' ],
            ];
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
 * @param string   $event      GHL Event value.
 * @param string   $email      Contact email.
 * @param string   $first_name First name.
 * @param string   $last_name  Last name.
 * @param string[] $tags       Tags to include in payload.
 */
function jcp_demo_ghl_build_milestone_body( string $event, string $email, string $first_name, string $last_name, array $tags ): string {
    $scalar = [
        JCP_GHL_KEY_EVENT      => $event,
        JCP_GHL_KEY_EMAIL      => $email,
        JCP_GHL_KEY_FIRST_NAME => $first_name,
        JCP_GHL_KEY_LAST_NAME  => $last_name,
    ];
    $body = http_build_query( $scalar, '', '&', PHP_QUERY_RFC3986 );
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
 * Forward a demo analytics milestone to the Demo Survey GHL webhook when mapped.
 *
 * @param string     $session_id Session ID.
 * @param string     $event_type Analytics event type.
 * @param array|null $metadata   Event metadata.
 * @param string     $email      Contact email.
 * @param string     $first_name First name.
 * @param string     $last_name  Last name.
 */
function jcp_demo_ghl_maybe_forward_demo_milestone(
    string $session_id,
    string $event_type,
    $metadata,
    string $email,
    string $first_name,
    string $last_name
): void {
    if ( ! defined( 'JCP_GHL_DEMO_SURVEY_WEBHOOK_URL' ) ) {
        return;
    }

    $email = trim( $email );
    if ( $email === '' || ! is_email( $email ) ) {
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
        $email,
        trim( $first_name ),
        trim( $last_name ),
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
        error_log( 'JCP Demo GHL milestone: event=' . $mapping['event'] . ' email=' . $email . ' http=' . (string) $code );
    }
}

/**
 * Handle Demo Viewed POST: forward to same GHL webhook with Event=viewed-demo for if/then branching.
 *
 * @param \WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function jcp_core_demo_viewed_submit_handler( \WP_REST_Request $request ): \WP_REST_Response {
    $first_name = trim( (string) $request->get_param( 'first_name' ) );
    $last_name  = trim( (string) $request->get_param( 'last_name' ) );
    $email      = trim( (string) $request->get_param( 'email' ) );

    if ( $first_name === '' || $email === '' || ! is_email( $email ) ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'First name, last name, and email are required.', 'jcp-core' ) ],
            400
        );
    }

    $body_string = jcp_core_build_demo_viewed_ghl_body( $first_name, $last_name, $email );

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
