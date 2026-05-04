<?php
/**
 * REST API: Early Access form submission → GoHighLevel webhook
 *
 * Sends application/x-www-form-urlencoded. Exact keys: First Name, Email, Phone,
 * Company, Business Type, Referral Source[] (array), Message. Shared keys match Demo Survey.
 *
 * @package JCP_Core
 */

/**
 * GHL webhook URL for Early Access form submissions only.
 * Do not use for Demo Survey; see inc/rest-demo-survey.php for Demo webhook.
 */
define( 'JCP_GHL_WEBHOOK_URL_DEFAULT', 'https://services.leadconnectorhq.com/hooks/kMIwmFm9I7LJPEYo35qi/webhook-trigger/d476d7e2-286d-4201-811d-4fedfea5fdf5' );

/**
 * Register REST routes for Early Access.
 */
function jcp_core_register_early_access_rest_routes(): void {
    register_rest_route( 'jcp/v1', '/early-access-submit', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => 'jcp_core_early_access_submit_handler',
        'args'                => [
            'first_name'      => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'last_name'       => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'company'         => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'email'           => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_email',
                'validate_callback'  => function ( $value ) {
                    return is_email( $value );
                },
            ],
            'phone'           => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'demo_goals'      => [
                'required'          => true,
                'type'              => 'array',
                'items'             => [ 'type' => 'string' ],
            ],
            'referral_source' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'business_type'   => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'coupon_code'      => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
        ],
    ] );

    register_rest_route( 'jcp/v1', '/early-access-test-ghl', [
        'methods'             => 'GET',
        'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
        'callback'            => 'jcp_core_early_access_test_ghl',
    ] );
}

add_action( 'rest_api_init', 'jcp_core_register_early_access_rest_routes' );

/**
 * Get GHL webhook URL (hardcoded default for Early Access only).
 *
 * @return string
 */
function jcp_core_ghl_webhook_url(): string {
    return JCP_GHL_WEBHOOK_URL_DEFAULT;
}

/**
 * Build application/x-www-form-urlencoded body for GHL.
 * Uses canonical GHL keys from form-fields.php (same as Demo Survey).
 *
 * @param string $first_name First name.
 * @param string $last_name  Last name.
 * @param string $email      Email.
 * @param string $phone      Phone.
 * @param string $company    Company.
 * @param string $business_type_label Business Type (display label).
 * @param array  $referral_source Referral Source (at least one value).
 * @param string $use_case Use Case (comma-joined from demo_goals).
 * @param string $message Optional Message field for CRM (coupon codes, notes).
 * @return string
 */
function jcp_core_build_ghl_body( string $first_name, string $last_name, string $email, string $phone, string $company, string $business_type_label, array $referral_source, string $use_case, string $message = '' ): string {
    $scalar = [
        JCP_GHL_KEY_FIRST_NAME     => $first_name,
        JCP_GHL_KEY_LAST_NAME      => $last_name,
        JCP_GHL_KEY_EMAIL         => $email,
        JCP_GHL_KEY_PHONE         => $phone,
        JCP_GHL_KEY_COMPANY       => $company,
        JCP_GHL_KEY_BUSINESS_TYPE => $business_type_label,
        JCP_GHL_KEY_USE_CASE      => $use_case,
    ];
    if ( $message !== '' ) {
        $scalar[ JCP_GHL_KEY_MESSAGE ] = $message;
    }
    $body = http_build_query( $scalar, '', '&', PHP_QUERY_RFC3986 );
    foreach ( $referral_source as $v ) {
        $v = trim( (string) $v );
        if ( $v !== '' ) {
            $body .= '&' . rawurlencode( JCP_GHL_KEY_REFERRAL_SOURCE . '[]' ) . '=' . rawurlencode( $v );
        }
    }
    return $body;
}

/**
 * Handle Early Access form POST: build GHL payload and forward.
 *
 * @param \WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function jcp_core_early_access_submit_handler( \WP_REST_Request $request ): \WP_REST_Response {
    $first_name      = $request->get_param( 'first_name' );
    $last_name       = $request->get_param( 'last_name' );
    $company         = $request->get_param( 'company' );
    $email           = $request->get_param( 'email' );
    $phone           = $request->get_param( 'phone' );
    $demo_goals      = $request->get_param( 'demo_goals' );
    $referral_source = $request->get_param( 'referral_source' );
    $business_type   = $request->get_param( 'business_type' );
    $coupon_code     = $request->get_param( 'coupon_code' );

    $require_company = true;
    $require_phone   = true;

    $demo_goals_array = is_array( $demo_goals ) ? array_filter( array_map( 'trim', $demo_goals ) ) : [];
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $demo_goals_array ) || empty( $referral_source ) || empty( $business_type ) ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'First name, last name, email, at least one interest, business type, and referral source are required.', 'jcp-core' ) ],
            400
        );
    }
    if ( $require_company && ( $company === null || trim( (string) $company ) === '' ) ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'Business name is required.', 'jcp-core' ) ],
            400
        );
    }
    if ( $require_phone && ( $phone === null || trim( (string) $phone ) === '' ) ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'Phone is required.', 'jcp-core' ) ],
            400
        );
    }

    $first_name      = trim( (string) $first_name );
    $last_name       = trim( (string) $last_name );
    $company         = trim( (string) $company );
    $email           = trim( (string) $email );
    $phone           = trim( (string) $phone );
    $referral_source = [ trim( (string) $referral_source ) ];
    $business_type   = trim( (string) $business_type );
    $use_case        = implode( ', ', $demo_goals_array );

    $business_type_label = function_exists( 'jcp_core_early_access_business_type_label' )
        ? jcp_core_early_access_business_type_label( $business_type )
        : $business_type;
    if ( $business_type_label === '' ) {
        $business_type_label = $business_type;
    }

    $coupon_trim = trim( (string) $coupon_code );
    $coupon_note = '';
    if ( $coupon_trim !== '' ) {
        $coupon_note = 'Coupon / promo code: ' . $coupon_trim;
        if ( strcasecmp( $coupon_trim, 'earlybird' ) === 0 ) {
            $coupon_note .= ' (Early Bird: Enterprise $125/month offer vs $399)';
        }
    }

    $body_string = jcp_core_build_ghl_body( $first_name, $last_name, $email, $phone, $company, $business_type_label, $referral_source, $use_case, $coupon_note );
    $url         = jcp_core_ghl_webhook_url();

    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( 'JCP GHL payload: ' . $body_string );
        error_log( 'JCP GHL URL: ' . $url );
    }

    $response = wp_remote_post(
        $url,
        [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body_string,
        ]
    );

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( 'JCP GHL response code: ' . (string) $code );
        error_log( 'JCP GHL response body: ' . (string) $body );
    }

    $ok = $code >= 200 && $code < 300;

    if ( $ok ) {
        return new \WP_REST_Response( [ 'success' => true ], 200 );
    }

    $msg = __( 'Something went wrong. Please try again.', 'jcp-core' );
    if ( $body !== '' ) {
        $decoded = json_decode( $body, true );
        if ( is_array( $decoded ) && isset( $decoded['message'] ) && is_string( $decoded['message'] ) ) {
            $msg = $decoded['message'];
        }
    }

    return new \WP_REST_Response( [ 'success' => false, 'message' => $msg ], 400 );
}

/**
 * Test GHL webhook (WP Admin only). Sends a test payload and returns response.
 *
 * @param \WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function jcp_core_early_access_test_ghl( \WP_REST_Request $request ): \WP_REST_Response {
    $business_type_label = 'General Contractor';

    $body_string = jcp_core_build_ghl_body(
        'Test First',
        'Test Last',
        'test@example.com',
        '555-000-0000',
        'Test Company',
        $business_type_label,
        [ 'Google Search' ],
        'More inbound calls, Better Google visibility'
    );
    $url = jcp_core_ghl_webhook_url();

    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( 'JCP GHL TEST payload: ' . $body_string );
        error_log( 'JCP GHL TEST URL: ' . $url );
    }

    $response = wp_remote_post(
        $url,
        [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body_string,
        ]
    );

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( 'JCP GHL TEST response code: ' . (string) $code );
        error_log( 'JCP GHL TEST response body: ' . (string) $body );
    }

    return new \WP_REST_Response( [
        'payload_sent'   => $body_string,
        'response_code' => $code,
        'response_body' => $body,
        'logged'        => true,
    ], 200 );
}
