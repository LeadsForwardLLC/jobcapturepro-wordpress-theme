<?php
/**
 * Demo Analytics: custom table, REST endpoint, and event storage.
 * No post meta or options for raw events. Used by survey + demo JS.
 *
 * Event types include demo_converted: one per session when user reaches
 * /early-access-success/ with a session that had demo_started or demo_run_started.
 * Stats keys: demo_conversions (count), conversion_rate (%). Same table; no extra DB keys.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Table name (without prefix) */
define( 'JCP_DEMO_EVENTS_TABLE', 'jcp_demo_events' );

/** Sessions table: one row per demo session (lightweight; no PII). */
define( 'JCP_DEMO_SESSIONS_TABLE', 'jcp_demo_sessions' );

/** Option key for "data since" after a reset (stored as MySQL datetime string). */
define( 'JCP_DEMO_ANALYTICS_START_DATE_OPTION', 'jcp_demo_analytics_start_date' );

/**
 * Create demo events table if it doesn't exist.
 */
function jcp_demo_analytics_maybe_create_table(): void {
    global $wpdb;
    $table = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        session_id varchar(64) NOT NULL,
        event_type varchar(64) NOT NULL,
        step_number int(11) DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        metadata longtext DEFAULT NULL,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY event_type (event_type),
        KEY created_at (created_at)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

/**
 * Create demo sessions table if it doesn't exist.
 * Stores session-level summary: session_id, business/contact fields, timestamps, flags.
 */
function jcp_demo_analytics_maybe_create_sessions_table(): void {
    global $wpdb;
    $table  = $wpdb->prefix . JCP_DEMO_SESSIONS_TABLE;
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        session_id varchar(64) NOT NULL,
        business_name varchar(255) DEFAULT NULL,
        business_type varchar(255) DEFAULT NULL,
        demo_goals longtext DEFAULT NULL,
        contact_name varchar(255) DEFAULT NULL,
        contact_email varchar(255) DEFAULT NULL,
        demo_started_at datetime NOT NULL,
        demo_completed tinyint(1) NOT NULL DEFAULT 0,
        demo_converted tinyint(1) NOT NULL DEFAULT 0,
        conversion_at datetime DEFAULT NULL,
        PRIMARY KEY (session_id),
        KEY demo_started_at (demo_started_at),
        KEY demo_converted (demo_converted)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    if ( $wpdb->get_var( "SHOW COLUMNS FROM $table LIKE 'demo_goals'" ) !== 'demo_goals' ) {
        $wpdb->query( "ALTER TABLE $table ADD COLUMN demo_goals longtext DEFAULT NULL AFTER business_type" );
    }
    if ( $wpdb->get_var( "SHOW COLUMNS FROM $table LIKE 'contact_name'" ) !== 'contact_name' ) {
        $wpdb->query( "ALTER TABLE $table ADD COLUMN contact_name varchar(255) DEFAULT NULL AFTER demo_goals" );
    }
    if ( $wpdb->get_var( "SHOW COLUMNS FROM $table LIKE 'contact_email'" ) !== 'contact_email' ) {
        $wpdb->query( "ALTER TABLE $table ADD COLUMN contact_email varchar(255) DEFAULT NULL AFTER contact_name" );
    }
}

/**
 * Register REST route for demo events.
 */
function jcp_demo_analytics_register_rest_route(): void {
    register_rest_route( 'jcp/v1', '/demo-event', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => 'jcp_demo_analytics_handle_event',
        'args'                => [
            'session_id'   => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ( $v ) {
                    return is_string( $v ) && strlen( $v ) <= 64 && strlen( $v ) >= 1;
                },
            ],
            'event_type'   => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ( $v ) {
                    $allowed = [
                        'demo_started',
                        'form_step_completed',
                        'slideshow_step_viewed',
                        'slideshow_skipped',
                        'demo_run_started',
                        'demo_step_viewed',
                        'demo_publish_completed',
                        'demo_review_sent',
                        'demo_coach_minimized',
                        'demo_replayed',
                        'post_demo_modal_shown',
                        'cta_clicked',
                        'demo_converted',
                    ];
                    return in_array( $v, $allowed, true );
                },
            ],
            'step_number'  => [
                'required' => false,
                'type'    => 'integer',
            ],
            'metadata'     => [
                'required' => false,
                'type'    => 'object',
            ],
            'email'        => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
            ],
            'first_name'   => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'last_name'    => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'company'      => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'business_type' => [
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'demo_goals'   => [
                'required' => false,
                'type'     => 'array',
                'items'    => [ 'type' => 'string' ],
            ],
        ] + ( function_exists( 'jcp_demo_ghl_attribution_rest_args' ) ? jcp_demo_ghl_attribution_rest_args() : [] ),
    ] );
}

/**
 * REST handler: validate, then insert event.
 *
 * @param \WP_REST_Request $request
 * @return \WP_REST_Response|\WP_Error
 */
function jcp_demo_analytics_handle_event( \WP_REST_Request $request ) {
    jcp_demo_analytics_maybe_create_table();

    global $wpdb;
    $table = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;

    $session_id  = $request->get_param( 'session_id' );
    $event_type  = $request->get_param( 'event_type' );
    $step_number = $request->get_param( 'step_number' );
    $metadata    = $request->get_param( 'metadata' );

    $meta_json = null;
    if ( is_array( $metadata ) && ! empty( $metadata ) ) {
        $meta_json = wp_json_encode( $metadata );
        if ( ! $meta_json || strlen( $meta_json ) > 65535 ) {
            $meta_json = null;
        }
    }

    $step_val = null;
    if ( is_numeric( $step_number ) ) {
        $step_val = (int) $step_number;
    }

    if ( $event_type === 'demo_converted' ) {
        $already = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM $table WHERE session_id = %s AND event_type = 'demo_converted' LIMIT 1",
            $session_id
        ) );
        if ( $already ) {
            return new \WP_REST_Response( [ 'ok' => true ], 200 );
        }
        $has_demo = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM $table WHERE session_id = %s AND event_type IN ('demo_started', 'demo_run_started') LIMIT 1",
            $session_id
        ) );
        if ( ! $has_demo ) {
            return new \WP_REST_Response( [ 'ok' => true ], 200 );
        }
    }

    $inserted = $wpdb->insert(
        $table,
        [
            'session_id'   => $session_id,
            'event_type'   => $event_type,
            'step_number'  => $step_val,
            'metadata'     => $meta_json,
        ],
        [ '%s', '%s', '%d', '%s' ]
    );

    if ( $inserted === false ) {
        return new \WP_REST_Response( [ 'ok' => true ], 200 );
    }

    jcp_demo_analytics_upsert_session_from_event(
        $session_id,
        $event_type,
        $metadata,
        $meta_json,
        [
            'email'      => $request->get_param( 'email' ),
            'first_name' => $request->get_param( 'first_name' ),
            'last_name'  => $request->get_param( 'last_name' ),
            'company'    => $request->get_param( 'company' ),
        ]
    );

    if ( function_exists( 'jcp_demo_ghl_maybe_forward_demo_milestone' ) ) {
        $contact_params = function_exists( 'jcp_demo_ghl_contact_params_from_request' )
            ? jcp_demo_ghl_contact_params_from_request( $request, is_array( $metadata ) ? $metadata : null )
            : [
                'first_name'    => $request->get_param( 'first_name' ),
                'last_name'     => $request->get_param( 'last_name' ),
                'email'         => $request->get_param( 'email' ),
                'company'       => $request->get_param( 'company' ),
                'business_type' => $request->get_param( 'business_type' ),
                'demo_goals'    => $request->get_param( 'demo_goals' ),
            ];
        jcp_demo_ghl_maybe_forward_demo_milestone(
            $session_id,
            $event_type,
            is_array( $metadata ) ? $metadata : null,
            $contact_params
        );
    }

    return new \WP_REST_Response( [ 'ok' => true ], 200 );
}

/**
 * Upsert session row from a demo event.
 * Called after inserting an event. Fails silently if sessions table missing.
 *
 * @param string              $session_id Session ID.
 * @param string              $event_type Event type just inserted.
 * @param array|null          $metadata   Decoded metadata (may contain company, business_type).
 * @param string|null         $meta_json  Raw JSON (unused; for future use).
 * @param array<string,mixed> $contact    Optional contact fields from the REST request.
 */
function jcp_demo_analytics_upsert_session_from_event( string $session_id, string $event_type, $metadata, $meta_json, array $contact = [] ): void {
    jcp_demo_analytics_maybe_create_sessions_table();
    global $wpdb;
    $stable = $wpdb->prefix . JCP_DEMO_SESSIONS_TABLE;
    $now    = current_time( 'mysql' );

    $contact_email = '';
    if ( ! empty( $contact['email'] ) && is_email( (string) $contact['email'] ) ) {
        $contact_email = sanitize_email( (string) $contact['email'] );
    }
    $first = isset( $contact['first_name'] ) ? trim( (string) $contact['first_name'] ) : '';
    $last  = isset( $contact['last_name'] ) ? trim( (string) $contact['last_name'] ) : '';
    $contact_name = trim( $first . ' ' . $last );
    $contact_name  = $contact_name !== '' ? substr( sanitize_text_field( $contact_name ), 0, 255 ) : null;
    $contact_email = $contact_email !== '' ? substr( $contact_email, 0, 255 ) : null;
    $company_from_contact = isset( $contact['company'] ) ? trim( (string) $contact['company'] ) : '';
    $company_from_contact = $company_from_contact !== '' ? substr( sanitize_text_field( $company_from_contact ), 0, 255 ) : null;

    $apply_contact_updates = static function () use ( $wpdb, $stable, $session_id, $contact_name, $contact_email, $company_from_contact ): void {
        if ( $contact_name === null && $contact_email === null && $company_from_contact === null ) {
            return;
        }
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT session_id FROM $stable WHERE session_id = %s LIMIT 1", $session_id ), ARRAY_A );
        if ( empty( $existing ) ) {
            return;
        }
        $updates = [];
        $formats = [];
        if ( $company_from_contact !== null ) {
            $updates['business_name'] = $company_from_contact;
            $formats[] = '%s';
        }
        if ( $contact_name !== null ) {
            $updates['contact_name'] = $contact_name;
            $formats[] = '%s';
        }
        if ( $contact_email !== null ) {
            $updates['contact_email'] = $contact_email;
            $formats[] = '%s';
        }
        if ( ! empty( $updates ) ) {
            $wpdb->update( $stable, $updates, [ 'session_id' => $session_id ], $formats, [ '%s' ] );
        }
    };

    if ( in_array( $event_type, [ 'demo_started', 'demo_run_started' ], true ) ) {
        $business_name = $company_from_contact;
        $business_type = null;
        if ( is_array( $metadata ) && ! empty( $metadata ) ) {
            $name = isset( $metadata['company'] ) ? $metadata['company'] : ( isset( $metadata['business_name'] ) ? $metadata['business_name'] : null );
            if ( is_string( $name ) && trim( $name ) !== '' ) {
                $business_name = substr( sanitize_text_field( trim( $name ) ), 0, 255 );
            }
            if ( isset( $metadata['business_type'] ) && is_string( $metadata['business_type'] ) && trim( $metadata['business_type'] ) !== '' ) {
                $business_type = substr( sanitize_text_field( trim( $metadata['business_type'] ) ), 0, 255 );
            }
        }
        $wpdb->query( $wpdb->prepare(
            "INSERT INTO $stable (session_id, business_name, business_type, contact_name, contact_email, demo_started_at, demo_completed, demo_converted, conversion_at)
             VALUES (%s, %s, %s, %s, %s, %s, 0, 0, NULL)
             ON DUPLICATE KEY UPDATE
             demo_started_at = IF(demo_started_at IS NULL OR demo_started_at > %s, %s, demo_started_at),
             business_name = COALESCE(NULLIF(TRIM(business_name), ''), %s),
             business_type = COALESCE(NULLIF(TRIM(business_type), ''), %s),
             contact_name = COALESCE(NULLIF(TRIM(contact_name), ''), %s),
             contact_email = COALESCE(NULLIF(TRIM(contact_email), ''), %s)",
            $session_id,
            $business_name ?: null,
            $business_type ?: null,
            $contact_name,
            $contact_email,
            $now,
            $now,
            $now,
            $business_name ?: null,
            $business_type ?: null,
            $contact_name,
            $contact_email
        ) );
        return;
    }

    if ( $event_type === 'form_step_completed' && is_array( $metadata ) && ! empty( $metadata ) ) {
        $business_name = null;
        $business_type = null;
        $demo_goals_json = null;
        $name = isset( $metadata['company'] ) ? $metadata['company'] : ( isset( $metadata['business_name'] ) ? $metadata['business_name'] : null );
        if ( is_string( $name ) && trim( $name ) !== '' ) {
            $business_name = substr( sanitize_text_field( trim( $name ) ), 0, 255 );
        } elseif ( $company_from_contact !== null ) {
            $business_name = $company_from_contact;
        }
        if ( isset( $metadata['business_type'] ) && is_string( $metadata['business_type'] ) && trim( $metadata['business_type'] ) !== '' ) {
            $business_type = substr( sanitize_text_field( trim( $metadata['business_type'] ) ), 0, 255 );
        }
        if ( isset( $metadata['demo_goals'] ) && is_array( $metadata['demo_goals'] ) && ! empty( $metadata['demo_goals'] ) ) {
            $goals = array_values( array_unique( array_filter( array_map( 'trim', array_map( 'strval', $metadata['demo_goals'] ) ) ) ) );
            if ( ! empty( $goals ) ) {
                $demo_goals_json = wp_json_encode( array_slice( $goals, 0, 10 ) );
                if ( strlen( $demo_goals_json ) > 65535 ) {
                    $demo_goals_json = null;
                }
            }
        }
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT session_id FROM $stable WHERE session_id = %s LIMIT 1", $session_id ), ARRAY_A );
        if ( ! empty( $existing ) ) {
            $updates = [];
            $formats = [];
            if ( $business_name !== null ) {
                $updates['business_name'] = $business_name;
                $formats[] = '%s';
            }
            if ( $business_type !== null ) {
                $updates['business_type'] = $business_type;
                $formats[] = '%s';
            }
            if ( $demo_goals_json !== null ) {
                $updates['demo_goals'] = $demo_goals_json;
                $formats[] = '%s';
            }
            if ( $contact_name !== null ) {
                $updates['contact_name'] = $contact_name;
                $formats[] = '%s';
            }
            if ( $contact_email !== null ) {
                $updates['contact_email'] = $contact_email;
                $formats[] = '%s';
            }
            if ( ! empty( $updates ) ) {
                $wpdb->update( $stable, $updates, [ 'session_id' => $session_id ], $formats, [ '%s' ] );
            }
        }
        return;
    }

    $apply_contact_updates();

    if ( $event_type === 'post_demo_modal_shown' ) {
        $wpdb->update(
            $stable,
            [ 'demo_completed' => 1 ],
            [ 'session_id' => $session_id ],
            [ '%d' ],
            [ '%s' ]
        );
        return;
    }

    if ( $event_type === 'demo_converted' ) {
        $wpdb->update(
            $stable,
            [ 'demo_converted' => 1, 'conversion_at' => $now ],
            [ 'session_id' => $session_id ],
            [ '%d', '%s' ],
            [ '%s' ]
        );
    }
}

add_action( 'rest_api_init', 'jcp_demo_analytics_register_rest_route' );

/** Run table creation on theme switch so tables exist before first event */
add_action( 'after_switch_theme', 'jcp_demo_analytics_maybe_create_table' );
add_action( 'after_switch_theme', 'jcp_demo_analytics_maybe_create_sessions_table' );

/**
 * Display label for survey business type (niche) value. Matches step-1 options.
 *
 * @param string $value Value from survey (e.g. plumbing, hvac).
 * @return string
 */
function jcp_demo_analytics_business_type_label( string $value ): string {
    if ( function_exists( 'jcp_core_early_access_business_type_label' ) ) {
        return jcp_core_early_access_business_type_label( $value );
    }
    return $value;
}

/**
 * Display label for survey "What should this demo prove?" (demo_goals) value. Matches step-2 options.
 *
 * @param string $value Value from survey (e.g. calls, google).
 * @return string
 */
function jcp_demo_analytics_demo_goal_label( string $value ): string {
    $map = [
        'calls' => 'More inbound calls',
        'google' => 'Better Google visibility',
        'reviews' => 'More customer reviews',
        'trust' => 'Stronger website trust',
        'busywork' => 'Less marketing busywork',
        'showcase' => 'Showcase my work',
    ];
    $v = trim( $value );
    return isset( $map[ $v ] ) ? $map[ $v ] : $v;
}

/**
 * Get funnel stats for admin: step → completion %, drop-off %, CTA counts, completion rate.
 * Read-only; no side effects.
 *
 * @return array{ funnel: array, cta_counts: array, completion_rate: float, total_sessions: int }
 */
function jcp_demo_analytics_get_stats(): array {
    global $wpdb;
    $table = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;

    jcp_demo_analytics_maybe_create_table();

    $total_sessions = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type IN ('demo_started', 'demo_run_started')"
    );

    $cta_counts = [
        'early_access'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE event_type = 'cta_clicked' AND (metadata LIKE '%early_access%' OR metadata LIKE '%get_started_free%')" ),
        'view_directory'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE event_type = 'cta_clicked' AND metadata LIKE '%view_directory%' AND metadata NOT LIKE '%view_main_directory%'" ),
        'view_main_directory'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE event_type = 'cta_clicked' AND metadata LIKE '%view_main_directory%'" ),
        'start_free_trial'     => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = 'cta_clicked' AND (metadata LIKE '%\"cta\":\"start_free_trial\"%' OR metadata LIKE '%\"cta\":\"get_started_free\"%')" ),
        'personalized_demo'    => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = 'cta_clicked' AND metadata LIKE '%\"cta\":\"personalized_demo\"%'" ),
        'replay_demo'          => (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT session_id) FROM $table WHERE
                (event_type = 'cta_clicked' AND metadata LIKE '%\"cta\":\"replay_demo\"%')
                OR event_type = 'demo_replayed'"
        ),
    ];

    $post_demo_shown = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = 'post_demo_modal_shown'" );
    $post_demo_cta_rates = [];
    foreach (
        [
            'start_free_trial'  => __( 'Start free 14-day trial', 'jcp-core' ),
            'personalized_demo' => __( 'Apply for a personalized demo', 'jcp-core' ),
            'replay_demo'       => __( 'Replay demo', 'jcp-core' ),
        ] as $key => $label
    ) {
        $count = (int) ( $cta_counts[ $key ] ?? 0 );
        $post_demo_cta_rates[] = [
            'key'   => $key,
            'label' => $label,
            'count' => $count,
            'pct'   => $post_demo_shown > 0 ? round( ( $count / $post_demo_shown ) * 100, 1 ) : 0.0,
        ];
    }

    $steps = [];
    $step_defs = [
        [ 'label' => 'Form step 1', 'type' => 'form_step_completed', 'num' => 1 ],
        [ 'label' => 'Form step 2', 'type' => 'form_step_completed', 'num' => 2 ],
        [ 'label' => 'Form step 3', 'type' => 'form_step_completed', 'num' => 3 ],
        [ 'label' => 'Slideshow step 1', 'type' => 'slideshow_step_viewed', 'num' => 1 ],
        [ 'label' => 'Slideshow step 2', 'type' => 'slideshow_step_viewed', 'num' => 2 ],
        [ 'label' => 'Slideshow step 3', 'type' => 'slideshow_step_viewed', 'num' => 3 ],
        [ 'label' => 'Slideshow step 4', 'type' => 'slideshow_step_viewed', 'num' => 4 ],
        [ 'label' => 'Slideshow step 5', 'type' => 'slideshow_step_viewed', 'num' => 5 ],
        [ 'label' => 'Slideshow step 6', 'type' => 'slideshow_step_viewed', 'num' => 6 ],
        [ 'label' => 'Slideshow step 7', 'type' => 'slideshow_step_viewed', 'num' => 7 ],
        [ 'label' => 'Slideshow step 8', 'type' => 'slideshow_step_viewed', 'num' => 8 ],
        [ 'label' => 'Slideshow skipped', 'type' => 'slideshow_skipped', 'num' => null ],
        [ 'label' => 'Demo run started', 'type' => 'demo_run_started', 'num' => null ],
        [ 'label' => 'Demo step 1', 'type' => 'demo_step_viewed', 'num' => 1 ],
        [ 'label' => 'Demo step 2', 'type' => 'demo_step_viewed', 'num' => 2 ],
        [ 'label' => 'Demo step 3', 'type' => 'demo_step_viewed', 'num' => 3 ],
        [ 'label' => 'Demo step 4', 'type' => 'demo_step_viewed', 'num' => 4 ],
        [ 'label' => 'Publish completed', 'type' => 'demo_publish_completed', 'num' => 4 ],
        [ 'label' => 'Demo step 5', 'type' => 'demo_step_viewed', 'num' => 5 ],
        [ 'label' => 'Review sent', 'type' => 'demo_review_sent', 'num' => 5 ],
        [ 'label' => 'Demo step 6', 'type' => 'demo_step_viewed', 'num' => 6 ],
        [ 'label' => 'Post-demo modal shown', 'type' => 'post_demo_modal_shown', 'num' => null ],
        [ 'label' => 'Demo replayed', 'type' => 'demo_replayed', 'num' => null ],
        [ 'label' => 'Converted (Start free trial)', 'type' => 'demo_converted', 'num' => null ],
    ];

    $prev_count = $total_sessions;
    foreach ( $step_defs as $def ) {
        if ( $def['num'] !== null ) {
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = %s AND step_number = %d",
                $def['type'],
                $def['num']
            ) );
        } else {
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = %s",
                $def['type']
            ) );
        }
        $pct = $total_sessions > 0 ? round( ( $count / $total_sessions ) * 100, 1 ) : 0;
        $dropoff = $prev_count > 0 ? round( ( ( $prev_count - $count ) / $prev_count ) * 100, 1 ) : 0;
        $steps[] = [
            'step'   => $def['label'],
            'count'  => $count,
            'pct'    => $pct,
            'dropoff' => $dropoff,
        ];
        $prev_count = $count;
    }

    $completed = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = 'post_demo_modal_shown'" );
    $completion_rate = $total_sessions > 0 ? round( ( $completed / $total_sessions ) * 100, 1 ) : 0.0;

    $demo_conversions = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE event_type = 'demo_converted'" );
    $conversion_rate = $total_sessions > 0 ? round( ( $demo_conversions / $total_sessions ) * 100, 1 ) : 0.0;
    $post_demo_conversion_rate = $completed > 0 ? round( ( $demo_conversions / $completed ) * 100, 1 ) : 0.0;

    $data_since = jcp_demo_analytics_get_data_since();

    $completion_times = jcp_demo_analytics_get_completion_times();
    $avg_time_seconds   = ( $completion_times && count( $completion_times ) >= 1 ) ? jcp_demo_analytics_average_seconds( $completion_times ) : null;
    $median_time_seconds = ( $completion_times && count( $completion_times ) >= 2 ) ? jcp_demo_analytics_median_seconds( $completion_times ) : null;

    $primary_dropoff = null;
    if ( $total_sessions > 0 && ! empty( $steps ) ) {
        $max_dropoff = 0;
        $max_step   = null;
        foreach ( $steps as $row ) {
            if ( isset( $row['dropoff'] ) && (float) $row['dropoff'] > $max_dropoff ) {
                $max_dropoff = (float) $row['dropoff'];
                $max_step   = $row;
            }
        }
        if ( $max_step !== null ) {
            $primary_dropoff = [ 'label' => $max_step['step'], 'dropoff' => $max_step['dropoff'] ];
        }
    }

    $business_type_distribution = [];
    $demo_goals_distribution    = [];
    $events_table = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;
    $event_rows   = $wpdb->get_results(
        "SELECT session_id, event_type, metadata FROM $events_table WHERE event_type IN ('demo_started', 'form_step_completed') AND metadata IS NOT NULL AND TRIM(metadata) != '' AND metadata != 'null' ORDER BY id ASC",
        ARRAY_A
    );
    $business_type_by_session = [];
    $demo_goals_by_session = [];
    if ( is_array( $event_rows ) && ! empty( $event_rows ) ) {
        foreach ( $event_rows as $row ) {
            $meta = isset( $row['metadata'] ) ? json_decode( $row['metadata'], true ) : null;
            if ( ! is_array( $meta ) ) {
                continue;
            }
            $sid = isset( $row['session_id'] ) ? (string) $row['session_id'] : '';
            if ( $sid === '' ) {
                continue;
            }
            if ( isset( $meta['business_type'] ) && is_string( $meta['business_type'] ) && trim( $meta['business_type'] ) !== '' ) {
                $business_type_by_session[ $sid ] = substr( trim( $meta['business_type'] ), 0, 255 );
            }
            if ( isset( $meta['demo_goals'] ) && is_array( $meta['demo_goals'] ) && ! empty( $meta['demo_goals'] ) ) {
                if ( ! isset( $demo_goals_by_session[ $sid ] ) ) {
                    $demo_goals_by_session[ $sid ] = [];
                }
                foreach ( $meta['demo_goals'] as $g ) {
                    if ( is_string( $g ) && trim( $g ) !== '' ) {
                        $demo_goals_by_session[ $sid ][ trim( $g ) ] = true;
                    }
                }
            }
        }
    }
    if ( ! empty( $business_type_by_session ) ) {
        $business_type_counts = array_count_values( $business_type_by_session );
        arsort( $business_type_counts, SORT_NUMERIC );
        $type_total = count( $business_type_by_session );
        foreach ( $business_type_counts as $val => $cnt ) {
            $pct = $type_total > 0 ? round( ( $cnt / $type_total ) * 100, 1 ) : 0;
            $business_type_distribution[] = [
                'value' => $val,
                'label' => jcp_demo_analytics_business_type_label( $val ),
                'count' => $cnt,
                'pct'   => $pct,
            ];
        }
    }
    if ( ! empty( $demo_goals_by_session ) ) {
        $goals_counts = [];
        $goals_respondents = count( $demo_goals_by_session );
        foreach ( $demo_goals_by_session as $session_goals ) {
            foreach ( array_keys( $session_goals ) as $g ) {
                $goals_counts[ $g ] = ( $goals_counts[ $g ] ?? 0 ) + 1;
            }
        }
        if ( $goals_respondents > 0 && ! empty( $goals_counts ) ) {
            arsort( $goals_counts, SORT_NUMERIC );
            foreach ( $goals_counts as $val => $cnt ) {
                $pct = round( ( $cnt / $goals_respondents ) * 100, 1 );
                $demo_goals_distribution[] = [
                    'value' => $val,
                    'label' => jcp_demo_analytics_demo_goal_label( $val ),
                    'count' => $cnt,
                    'pct'   => $pct,
                ];
            }
        }
    }

    return [
        'funnel'                   => $steps,
        'cta_counts'               => $cta_counts,
        'post_demo_shown'          => $post_demo_shown,
        'post_demo_cta_rates'      => $post_demo_cta_rates,
        'completion_rate'          => $completion_rate,
        'total_sessions'           => $total_sessions,
        'demo_conversions'         => $demo_conversions,
        'conversion_rate'          => $conversion_rate,
        'post_demo_conversion_rate'=> $post_demo_conversion_rate,
        'data_since'               => $data_since,
        'avg_time_to_completion_seconds'   => $avg_time_seconds,
        'median_time_to_completion_seconds' => $median_time_seconds,
        'primary_dropoff'         => $primary_dropoff,
        'business_type_distribution' => $business_type_distribution,
        'demo_goals_distribution' => $demo_goals_distribution,
    ];
}

/**
 * Get "data since" for display: reset timestamp if set, else earliest event, else null.
 *
 * @return string|null Formatted date string or null if no data.
 */
function jcp_demo_analytics_get_data_since(): ?string {
    $option = get_option( JCP_DEMO_ANALYTICS_START_DATE_OPTION, null );
    if ( $option && is_string( $option ) && trim( $option ) !== '' ) {
        return wp_date( get_option( 'date_format' ), strtotime( $option ) );
    }
    global $wpdb;
    $table  = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;
    $oldest = $wpdb->get_var( "SELECT MIN(created_at) FROM $table" );
    if ( $oldest ) {
        return wp_date( get_option( 'date_format' ), strtotime( $oldest ) );
    }
    return null;
}

/**
 * Get list of completion durations in seconds (first event to post_demo_modal_shown per session).
 *
 * @return array<int>|null Array of seconds, or null if insufficient data.
 */
function jcp_demo_analytics_get_completion_times(): ?array {
    global $wpdb;
    $table = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;
    jcp_demo_analytics_maybe_create_table();

    $completed_sessions = $wpdb->get_col(
        "SELECT DISTINCT session_id FROM $table WHERE event_type = 'post_demo_modal_shown'"
    );
    if ( empty( $completed_sessions ) ) {
        return null;
    }

    $times = [];
    foreach ( $completed_sessions as $sid ) {
        $first = $wpdb->get_var( $wpdb->prepare(
            "SELECT MIN(created_at) FROM $table WHERE session_id = %s",
            $sid
        ) );
        $done = $wpdb->get_var( $wpdb->prepare(
            "SELECT created_at FROM $table WHERE session_id = %s AND event_type = 'post_demo_modal_shown' LIMIT 1",
            $sid
        ) );
        if ( $first && $done ) {
            $times[] = strtotime( $done ) - strtotime( $first );
        }
    }
    return $times ? $times : null;
}

/**
 * Format seconds for display (e.g. "2 min 30 sec").
 *
 * @param int $seconds
 * @return string
 */
function jcp_demo_analytics_format_seconds( int $seconds ): string {
    if ( $seconds < 60 ) {
        return $seconds . ' sec';
    }
    $mins = (int) floor( $seconds / 60 );
    $secs = $seconds % 60;
    if ( $secs === 0 ) {
        return $mins . ' min';
    }
    return $mins . ' min ' . $secs . ' sec';
}

/**
 * Average of an array of integers (seconds).
 *
 * @param array<int> $seconds
 * @return int
 */
function jcp_demo_analytics_average_seconds( array $seconds ): int {
    if ( empty( $seconds ) ) {
        return 0;
    }
    return (int) round( array_sum( $seconds ) / count( $seconds ) );
}

/**
 * Median of an array of integers (seconds).
 *
 * @param array<int> $seconds
 * @return int
 */
function jcp_demo_analytics_median_seconds( array $seconds ): int {
    if ( empty( $seconds ) ) {
        return 0;
    }
    $sorted = $seconds;
    sort( $sorted, SORT_NUMERIC );
    $c = count( $sorted );
    $mid = (int) floor( $c / 2 );
    if ( $c % 2 === 0 ) {
        return (int) round( ( $sorted[ $mid - 1 ] + $sorted[ $mid ] ) / 2 );
    }
    return (int) $sorted[ $mid ];
}

/**
 * Get session-level records for admin (read-only), including post-demo CTA clicks.
 *
 * @param string $filter 'all' or 'converted'.
 * @param int    $limit  Max rows (default 25).
 * @return array<int, array<string, mixed>>
 */
function jcp_demo_analytics_get_sessions( string $filter = 'all', int $limit = 25 ): array {
    if ( ! current_user_can( 'manage_options' ) ) {
        return [];
    }
    global $wpdb;
    jcp_demo_analytics_maybe_create_sessions_table();
    $stable = $wpdb->prefix . JCP_DEMO_SESSIONS_TABLE;
    $etable = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;
    $limit  = max( 1, min( 100, $limit ) );
    $order  = 'ORDER BY demo_started_at DESC LIMIT ' . (int) $limit;
    if ( $filter === 'converted' ) {
        $rows = $wpdb->get_results( "SELECT session_id, business_name, business_type, contact_name, contact_email, demo_started_at, demo_completed, demo_converted, conversion_at FROM $stable WHERE demo_converted = 1 $order", ARRAY_A );
    } else {
        $rows = $wpdb->get_results( "SELECT session_id, business_name, business_type, contact_name, contact_email, demo_started_at, demo_completed, demo_converted, conversion_at FROM $stable $order", ARRAY_A );
    }
    if ( ! is_array( $rows ) ) {
        return [];
    }

    $session_ids = array_values( array_filter( array_map( static function ( $row ) {
        return isset( $row['session_id'] ) ? (string) $row['session_id'] : '';
    }, $rows ) ) );

    $cta_by_session = [];
    if ( ! empty( $session_ids ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $session_ids ), '%s' ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders built safely.
        $sql = $wpdb->prepare(
            "SELECT session_id, event_type, metadata FROM $etable
             WHERE session_id IN ($placeholders)
               AND (event_type = 'cta_clicked' OR event_type = 'demo_replayed')
             ORDER BY id ASC",
            ...$session_ids
        );
        $event_rows = $wpdb->get_results( $sql, ARRAY_A );
        if ( is_array( $event_rows ) ) {
            foreach ( $event_rows as $erow ) {
                $sid = (string) ( $erow['session_id'] ?? '' );
                if ( $sid === '' ) {
                    continue;
                }
                if ( ! isset( $cta_by_session[ $sid ] ) ) {
                    $cta_by_session[ $sid ] = [];
                }
                $cta_key = '';
                if ( ( $erow['event_type'] ?? '' ) === 'demo_replayed' ) {
                    $cta_key = 'replay_demo';
                } else {
                    $meta = isset( $erow['metadata'] ) ? json_decode( (string) $erow['metadata'], true ) : null;
                    if ( is_array( $meta ) && ! empty( $meta['cta'] ) ) {
                        $cta_key = sanitize_key( (string) $meta['cta'] );
                        if ( $cta_key === 'get_started_free' ) {
                            $cta_key = 'start_free_trial';
                        }
                    }
                }
                if ( $cta_key !== '' && ! in_array( $cta_key, $cta_by_session[ $sid ], true ) ) {
                    $cta_by_session[ $sid ][] = $cta_key;
                }
            }
        }
    }

    $cta_labels = [
        'start_free_trial'  => __( 'Start free 14-day trial', 'jcp-core' ),
        'personalized_demo' => __( 'Personalized demo', 'jcp-core' ),
        'replay_demo'       => __( 'Replay demo', 'jcp-core' ),
        'view_directory'    => __( 'View directory listing', 'jcp-core' ),
        'view_main_directory' => __( 'View main directory', 'jcp-core' ),
        'early_access'      => __( 'Early access', 'jcp-core' ),
    ];

    $out = [];
    foreach ( $rows as $row ) {
        $bt = isset( $row['business_type'] ) && trim( (string) $row['business_type'] ) !== '' ? (string) $row['business_type'] : null;
        $sid = isset( $row['session_id'] ) ? (string) $row['session_id'] : '';
        $cta_keys = $cta_by_session[ $sid ] ?? [];
        $cta_display = [];
        foreach ( $cta_keys as $key ) {
            $cta_display[] = $cta_labels[ $key ] ?? $key;
        }
        $out[] = [
            'session_id'            => $sid,
            'business_name'         => isset( $row['business_name'] ) && trim( (string) $row['business_name'] ) !== '' ? (string) $row['business_name'] : null,
            'business_type'         => $bt,
            'business_type_display' => $bt ? jcp_demo_analytics_business_type_label( $bt ) : null,
            'contact_name'          => isset( $row['contact_name'] ) && trim( (string) $row['contact_name'] ) !== '' ? (string) $row['contact_name'] : null,
            'contact_email'         => isset( $row['contact_email'] ) && trim( (string) $row['contact_email'] ) !== '' ? (string) $row['contact_email'] : null,
            'demo_started_at'       => isset( $row['demo_started_at'] ) ? (string) $row['demo_started_at'] : '',
            'demo_completed'        => ! empty( $row['demo_completed'] ),
            'demo_converted'        => ! empty( $row['demo_converted'] ),
            'conversion_at'         => isset( $row['conversion_at'] ) && trim( (string) $row['conversion_at'] ) !== '' ? (string) $row['conversion_at'] : null,
            'post_demo_ctas'        => $cta_keys,
            'post_demo_ctas_display'=> $cta_display,
        ];
    }
    return $out;
}

/**
 * Reset demo analytics: truncate events and sessions tables; set analytics_start_date option.
 * Requires manage_options. No side effects on failure.
 *
 * @return bool True on success.
 */
function jcp_demo_analytics_reset(): bool {
    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }
    global $wpdb;
    jcp_demo_analytics_maybe_create_table();
    jcp_demo_analytics_maybe_create_sessions_table();
    $table  = $wpdb->prefix . JCP_DEMO_EVENTS_TABLE;
    $stable = $wpdb->prefix . JCP_DEMO_SESSIONS_TABLE;

    $wpdb->query( "TRUNCATE TABLE $table" );
    $wpdb->query( "TRUNCATE TABLE $stable" );
    update_option( JCP_DEMO_ANALYTICS_START_DATE_OPTION, current_time( 'mysql' ) );
    return true;
}

