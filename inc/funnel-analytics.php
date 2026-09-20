<?php
/**
 * First-party Funnel Analytics — shared non-PII event layer + reporting helpers.
 *
 * Storage: {$wpdb->prefix}jcp_funnel_events
 * Ingest:  POST /wp-json/jcp/v1/funnel-event
 * Admin:   JCP → Funnel Analytics
 *
 * Retention: raw events purged after 120 days (aggregates are computed live).
 * Demo funnel reports can adapt from existing jcp_demo_events (no PII columns used).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JCP_FUNNEL_EVENTS_TABLE', 'jcp_funnel_events' );
define( 'JCP_FUNNEL_ANALYTICS_RETENTION_DAYS', 120 );
define( 'JCP_FUNNEL_ANALYTICS_META_OPTION', 'jcp_funnel_analytics_meta' );

/**
 * Create funnel events table if needed.
 */
function jcp_funnel_analytics_maybe_create_table(): void {
	global $wpdb;
	$table   = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		event_uuid varchar(64) NOT NULL,
		session_id varchar(64) NOT NULL,
		anonymous_visitor_id varchar(64) DEFAULT NULL,
		funnel_id varchar(64) NOT NULL,
		funnel_version varchar(32) DEFAULT NULL,
		lp_variant varchar(128) DEFAULT NULL,
		event_name varchar(96) NOT NULL,
		screen varchar(96) DEFAULT NULL,
		question_index int(11) DEFAULT NULL,
		question_id varchar(64) DEFAULT NULL,
		answer_value varchar(128) DEFAULT NULL,
		trade varchar(64) DEFAULT NULL,
		jobs_per_week_bucket varchar(64) DEFAULT NULL,
		proof_gap_band varchar(64) DEFAULT NULL,
		workflow varchar(64) DEFAULT NULL,
		utm_source varchar(255) DEFAULT NULL,
		utm_medium varchar(255) DEFAULT NULL,
		utm_campaign varchar(255) DEFAULT NULL,
		utm_content varchar(255) DEFAULT NULL,
		utm_term varchar(255) DEFAULT NULL,
		has_fbclid tinyint(1) NOT NULL DEFAULT 0,
		has_ttclid tinyint(1) NOT NULL DEFAULT 0,
		device_category varchar(32) DEFAULT NULL,
		referrer varchar(512) DEFAULT NULL,
		metadata longtext DEFAULT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY event_uuid (event_uuid),
		KEY session_id (session_id),
		KEY funnel_id (funnel_id),
		KEY event_name (event_name),
		KEY created_at (created_at),
		KEY funnel_created (funnel_id, created_at),
		KEY question_id (question_id)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'jcp_funnel_analytics_maybe_create_table' );
add_action( 'init', 'jcp_funnel_analytics_maybe_create_table', 5 );

/**
 * Register REST ingest endpoint (public, non-PII only).
 */
function jcp_funnel_analytics_register_rest(): void {
	register_rest_route(
		'jcp/v1',
		'/funnel-event',
		[
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'jcp_funnel_analytics_handle_event',
		]
	);
}
add_action( 'rest_api_init', 'jcp_funnel_analytics_register_rest' );

/**
 * Strip any accidental PII keys from payload arrays.
 *
 * @param array $data Raw input.
 * @return array
 */
function jcp_funnel_analytics_strip_pii( array $data ): array {
	$blocked = [
		'email',
		'phone',
		'first_name',
		'last_name',
		'name',
		'business_name',
		'company',
		'password',
		'contact_name',
		'contact_email',
		'fbclid',
		'ttclid',
		'gclid',
	];
	foreach ( $blocked as $key ) {
		unset( $data[ $key ] );
	}
	return $data;
}

/**
 * Handle funnel event ingest.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function jcp_funnel_analytics_handle_event( WP_REST_Request $request ) {
	jcp_funnel_analytics_maybe_create_table();
	global $wpdb;

	$params = $request->get_json_params();
	if ( ! is_array( $params ) ) {
		$params = $request->get_params();
	}
	if ( ! is_array( $params ) ) {
		return new WP_Error( 'invalid_body', 'Invalid JSON body', [ 'status' => 400 ] );
	}
	$params = jcp_funnel_analytics_strip_pii( $params );

	$event_uuid = sanitize_text_field( (string) ( $params['event_uuid'] ?? '' ) );
	$session_id = sanitize_text_field( (string) ( $params['session_id'] ?? '' ) );
	$funnel_id  = sanitize_key( (string) ( $params['funnel_id'] ?? '' ) );
	$event_name = sanitize_text_field( (string) ( $params['event_name'] ?? $params['event'] ?? '' ) );

	if ( $event_uuid === '' || strlen( $event_uuid ) > 64 ) {
		return new WP_Error( 'missing_uuid', 'event_uuid required', [ 'status' => 400 ] );
	}
	if ( $session_id === '' || strlen( $session_id ) > 64 ) {
		return new WP_Error( 'missing_session', 'session_id required', [ 'status' => 400 ] );
	}
	if ( $funnel_id === '' ) {
		return new WP_Error( 'missing_funnel', 'funnel_id required', [ 'status' => 400 ] );
	}
	if ( $event_name === '' || strlen( $event_name ) > 96 ) {
		return new WP_Error( 'missing_event', 'event_name required', [ 'status' => 400 ] );
	}

	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$exists = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(1) FROM $table WHERE event_uuid = %s", $event_uuid )
	);
	if ( $exists > 0 ) {
		jcp_funnel_analytics_bump_meta( 'duplicate_rejected', 1 );
		return rest_ensure_response( [ 'ok' => true, 'deduped' => true ] );
	}

	$meta = $params['metadata'] ?? null;
	if ( is_array( $meta ) ) {
		$meta = jcp_funnel_analytics_strip_pii( $meta );
		$meta_json = wp_json_encode( $meta );
	} else {
		$meta_json = null;
	}

	$row = [
		'event_uuid'           => $event_uuid,
		'session_id'           => $session_id,
		'anonymous_visitor_id' => sanitize_text_field( (string) ( $params['anonymous_visitor_id'] ?? '' ) ) ?: null,
		'funnel_id'            => $funnel_id,
		'funnel_version'       => sanitize_text_field( (string) ( $params['funnel_version'] ?? '' ) ) ?: null,
		'lp_variant'           => sanitize_text_field( (string) ( $params['lp_variant'] ?? '' ) ) ?: null,
		'event_name'           => $event_name,
		'screen'               => sanitize_text_field( (string) ( $params['screen'] ?? '' ) ) ?: null,
		'question_index'       => isset( $params['question_index'] ) && $params['question_index'] !== '' && $params['question_index'] !== null
			? (int) $params['question_index']
			: null,
		'question_id'          => sanitize_text_field( (string) ( $params['question_id'] ?? '' ) ) ?: null,
		'answer_value'         => sanitize_text_field( (string) ( $params['answer_value'] ?? '' ) ) ?: null,
		'trade'                => sanitize_text_field( (string) ( $params['trade'] ?? '' ) ) ?: null,
		'jobs_per_week_bucket' => sanitize_text_field( (string) ( $params['jobs_per_week_bucket'] ?? '' ) ) ?: null,
		'proof_gap_band'       => sanitize_text_field( (string) ( $params['proof_gap_band'] ?? '' ) ) ?: null,
		'workflow'             => sanitize_text_field( (string) ( $params['workflow'] ?? '' ) ) ?: null,
		'utm_source'           => sanitize_text_field( (string) ( $params['utm_source'] ?? '' ) ) ?: null,
		'utm_medium'           => sanitize_text_field( (string) ( $params['utm_medium'] ?? '' ) ) ?: null,
		'utm_campaign'         => sanitize_text_field( (string) ( $params['utm_campaign'] ?? '' ) ) ?: null,
		'utm_content'          => sanitize_text_field( (string) ( $params['utm_content'] ?? '' ) ) ?: null,
		'utm_term'             => sanitize_text_field( (string) ( $params['utm_term'] ?? '' ) ) ?: null,
		'has_fbclid'           => ! empty( $params['has_fbclid'] ) ? 1 : 0,
		'has_ttclid'           => ! empty( $params['has_ttclid'] ) ? 1 : 0,
		'device_category'      => sanitize_text_field( (string) ( $params['device_category'] ?? $params['device_class'] ?? '' ) ) ?: null,
		'referrer'             => esc_url_raw( (string) ( $params['referrer'] ?? '' ) ) ?: null,
		'metadata'             => $meta_json,
		'created_at'           => current_time( 'mysql' ),
	];

	$inserted = $wpdb->insert( $table, $row );
	if ( false === $inserted ) {
		jcp_funnel_analytics_bump_meta( 'insert_failures', 1 );
		return new WP_Error( 'insert_failed', 'Could not store event', [ 'status' => 500 ] );
	}

	jcp_funnel_analytics_bump_meta( 'last_event_at', current_time( 'mysql' ), false );
	jcp_funnel_analytics_bump_meta( 'events_24h_hint', 1 );

	return rest_ensure_response( [ 'ok' => true, 'deduped' => false ] );
}

/**
 * Update diagnostics meta option.
 *
 * @param string $key   Meta key.
 * @param mixed  $value Value or increment.
 * @param bool   $incr  Whether to increment.
 */
function jcp_funnel_analytics_bump_meta( string $key, $value = 1, bool $incr = true ): void {
	$meta = get_option( JCP_FUNNEL_ANALYTICS_META_OPTION, [] );
	if ( ! is_array( $meta ) ) {
		$meta = [];
	}
	if ( $incr ) {
		$meta[ $key ] = (int) ( $meta[ $key ] ?? 0 ) + (int) $value;
	} else {
		$meta[ $key ] = $value;
	}
	update_option( JCP_FUNNEL_ANALYTICS_META_OPTION, $meta, false );
}

/**
 * Known funnels for admin UI.
 *
 * @return array<string,string>
 */
function jcp_funnel_analytics_funnels(): array {
	return [
		'proof_gap'    => __( 'Proof Gap', 'jcp-core' ),
		'demo'         => __( 'Demo', 'jcp-core' ),
		'proof_sprint' => __( 'Proof Sprint', 'jcp-core' ),
	];
}

/**
 * Canonical stage sequence for Proof Gap (unique-session funnel).
 *
 * @return array<int,array{key:string,label:string,events:string[],lifecycle?:bool}>
 */
function jcp_funnel_analytics_proof_gap_stages(): array {
	return [
		[ 'key' => 'landing', 'label' => __( 'Welcome', 'jcp-core' ), 'events' => [ 'SurveyLandingViewed' ] ],
		[ 'key' => 'started', 'label' => __( 'Survey started', 'jcp-core' ), 'events' => [ 'SurveyStarted' ] ],
		[ 'key' => 'trade', 'label' => __( 'Trade', 'jcp-core' ), 'events' => [ 'SurveyQuestionAnswered' ], 'question_id' => 'trade' ],
		[ 'key' => 'workflow', 'label' => __( 'Workflow', 'jcp-core' ), 'events' => [ 'SurveyQuestionAnswered' ], 'question_id' => 'current_workflow' ],
		[ 'key' => 'jobs', 'label' => __( 'Jobs/week', 'jcp-core' ), 'events' => [ 'SurveyQuestionAnswered' ], 'question_id' => 'jobs_per_week' ],
		[ 'key' => 'proof', 'label' => __( 'Proof frequency', 'jcp-core' ), 'events' => [ 'SurveyQuestionAnswered' ], 'question_id' => 'public_proof_percentage' ],
		[ 'key' => 'result', 'label' => __( 'Proof Gap result', 'jcp-core' ), 'events' => [ 'SurveyResultViewed' ] ],
		[ 'key' => 'email', 'label' => __( 'Email save', 'jcp-core' ), 'events' => [ 'EmailSubmitted' ] ],
		[ 'key' => 'reveal', 'label' => __( 'Product reveal', 'jcp-core' ), 'events' => [ 'ProductRevealStarted', 'ProductRevealCompleted' ] ],
		[ 'key' => 'trial_plan', 'label' => __( 'Trial plan', 'jcp-core' ), 'events' => [ 'TrialCTAViewed' ] ],
		[ 'key' => 'trial_cta', 'label' => __( 'Trial CTA', 'jcp-core' ), 'events' => [ 'TrialCTAClicked' ] ],
		[ 'key' => 'trial_started', 'label' => __( 'Trial started', 'jcp-core' ), 'events' => [ 'TrialStarted' ], 'lifecycle' => true ],
		[ 'key' => 'activated', 'label' => __( 'Activated', 'jcp-core' ), 'events' => [ 'ActivatedTrial' ], 'lifecycle' => true ],
		[ 'key' => 'paid', 'label' => __( 'Paid', 'jcp-core' ), 'events' => [ 'PaidCustomer' ], 'lifecycle' => true ],
	];
}

/**
 * Parse admin filters from request.
 *
 * @return array<string,mixed>
 */
function jcp_funnel_analytics_parse_filters(): array {
	$funnel = sanitize_key( (string) ( $_GET['funnel'] ?? 'proof_gap' ) );
	if ( ! isset( jcp_funnel_analytics_funnels()[ $funnel ] ) ) {
		$funnel = 'proof_gap';
	}
	$days = (int) ( $_GET['days'] ?? 30 );
	if ( ! in_array( $days, [ 7, 14, 30, 90 ], true ) ) {
		$days = 30;
	}
	return [
		'funnel'       => $funnel,
		'days'         => $days,
		'utm_source'   => sanitize_text_field( (string) ( $_GET['utm_source'] ?? '' ) ),
		'utm_campaign' => sanitize_text_field( (string) ( $_GET['utm_campaign'] ?? '' ) ),
		'utm_content'  => sanitize_text_field( (string) ( $_GET['utm_content'] ?? '' ) ),
		'lp_variant'   => sanitize_text_field( (string) ( $_GET['lp_variant'] ?? '' ) ),
		'trade'        => sanitize_text_field( (string) ( $_GET['trade'] ?? '' ) ),
		'device'       => sanitize_text_field( (string) ( $_GET['device'] ?? '' ) ),
	];
}

/**
 * Count unique sessions for a Proof Gap stage within filter window.
 *
 * Sessions are included if their first funnel event in-window qualifies as cohort start
 * (SurveyLandingViewed or SurveyStarted). Downstream lifecycle stages may attach later
 * outside the window when session_id matches a cohort session.
 *
 * @param array $filters Filters.
 * @return array{stages:array,summary:array,questions:array,traffic:array,diagnostics:array,lifecycle_connected:bool}
 */
function jcp_funnel_analytics_report( array $filters ): array {
	if ( $filters['funnel'] === 'demo' ) {
		return jcp_funnel_analytics_demo_adapter_report( $filters );
	}
	if ( $filters['funnel'] === 'proof_sprint' ) {
		return jcp_funnel_analytics_proof_sprint_adapter_report( $filters );
	}
	return jcp_funnel_analytics_proof_gap_report( $filters );
}

/**
 * @param array $filters Filters.
 * @return array
 */
function jcp_funnel_analytics_proof_gap_report( array $filters ): array {
	global $wpdb;
	jcp_funnel_analytics_maybe_create_table();
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;

	$since = gmdate( 'Y-m-d H:i:s', time() - ( (int) $filters['days'] * DAY_IN_SECONDS ) );
	$where = [ 'funnel_id = %s', 'created_at >= %s' ];
	$args  = [ 'proof_gap', $since ];

	foreach ( [ 'utm_source', 'utm_campaign', 'utm_content', 'lp_variant', 'trade' ] as $col ) {
		if ( ! empty( $filters[ $col ] ) ) {
			$where[] = "$col = %s";
			$args[]  = $filters[ $col ];
		}
	}
	if ( ! empty( $filters['device'] ) ) {
		$where[] = 'device_category = %s';
		$args[]  = $filters['device'];
	}
	$where_sql = implode( ' AND ', $where );

	// Cohort: sessions with landing or start in window.
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$cohort_sql = $wpdb->prepare(
		"SELECT DISTINCT session_id FROM $table WHERE $where_sql AND event_name IN ('SurveyLandingViewed','SurveyStarted')",
		$args
	);
	$cohort = $wpdb->get_col( $cohort_sql );
	$cohort = array_values( array_filter( array_map( 'strval', $cohort ?: [] ) ) );

	$stages_def = jcp_funnel_analytics_proof_gap_stages();
	$stage_rows = [];
	$prev_count = 0;
	$landing    = 0;

	foreach ( $stages_def as $i => $stage ) {
		$lifecycle = ! empty( $stage['lifecycle'] );
		if ( empty( $cohort ) ) {
			$count = 0;
		} elseif ( $lifecycle ) {
			// Lifecycle not connected in theme yet — report as not connected.
			$count = null;
		} else {
			$count = jcp_funnel_analytics_count_stage_sessions( $table, $cohort, $stage );
		}

		if ( $i === 0 ) {
			$landing = is_int( $count ) ? $count : 0;
		}

		$from_prev = null;
		$drop      = null;
		$drop_pct  = null;
		$from_land = null;
		if ( is_int( $count ) ) {
			if ( $i === 0 ) {
				$from_prev = 100.0;
				$drop      = 0;
				$drop_pct  = 0.0;
			} else {
				$from_prev = $prev_count > 0 ? round( ( $count / $prev_count ) * 100, 1 ) : 0.0;
				$drop      = max( 0, $prev_count - $count );
				$drop_pct  = $prev_count > 0 ? round( ( $drop / $prev_count ) * 100, 1 ) : 0.0;
			}
			$from_land = $landing > 0 ? round( ( $count / $landing ) * 100, 1 ) : 0.0;
			$prev_count = $count;
		}

		$stage_rows[] = [
			'key'            => $stage['key'],
			'label'          => $stage['label'],
			'sessions'       => $count,
			'from_previous'  => $from_prev,
			'drop'           => $drop,
			'drop_pct'       => $drop_pct,
			'from_landing'   => $from_land,
			'not_connected'  => $lifecycle,
		];
	}

	$summary = [
		'visitors'          => $stage_rows[0]['sessions'] ?? 0,
		'survey_starts'     => $stage_rows[1]['sessions'] ?? 0,
		'survey_completion' => $stage_rows[6]['sessions'] ?? 0,
		'emails_captured'   => $stage_rows[7]['sessions'] ?? 0,
		'product_reveal'    => $stage_rows[8]['sessions'] ?? 0,
		'trial_cta_clicks'  => $stage_rows[10]['sessions'] ?? 0,
		'trials_started'    => null,
		'activated_trials'  => null,
		'paid_customers'    => null,
	];

	return [
		'stages'              => $stage_rows,
		'summary'             => $summary,
		'questions'           => jcp_funnel_analytics_question_stats( $table, $cohort, $since ),
		'destinations'        => jcp_funnel_analytics_destination_stats( $table, $cohort ),
		'traffic'             => jcp_funnel_analytics_traffic_stats( $table, $cohort, $since ),
		'diagnostics'         => jcp_funnel_analytics_diagnostics( $table ),
		'lifecycle_connected' => false,
		'cohort_size'         => count( $cohort ),
		'calculation_note'    => __( 'Stage funnel uses unique session_ids whose SurveyLandingViewed or SurveyStarted occurred in the selected window. Conversion % is previous-stage and landing-relative, not raw event counts. Destination tabs count only user-selected ProductRevealDestinationSelected events. Trial/activation/paid require cross-domain lifecycle wiring (shown as Not connected).', 'jcp-core' ),
	];
}

/**
 * @param string $table Table.
 * @param array  $cohort Session IDs.
 * @param array  $stage Stage def.
 * @return int
 */
function jcp_funnel_analytics_count_stage_sessions( string $table, array $cohort, array $stage ): int {
	global $wpdb;
	if ( empty( $cohort ) ) {
		return 0;
	}
	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	$events       = $stage['events'];
	$event_ph     = implode( ',', array_fill( 0, count( $events ), '%s' ) );
	$args         = array_merge( $cohort, $events );
	$extra        = '';
	if ( ! empty( $stage['question_id'] ) ) {
		$extra  = ' AND question_id = %s';
		$args[] = $stage['question_id'];
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare(
		"SELECT COUNT(DISTINCT session_id) FROM $table WHERE session_id IN ($placeholders) AND event_name IN ($event_ph)$extra",
		$args
	);
	return (int) $wpdb->get_var( $sql );
}

/**
 * Product reveal destination engagement (user-selected tabs only).
 *
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @return array
 */
function jcp_funnel_analytics_destination_stats( string $table, array $cohort ): array {
	global $wpdb;
	$dests = [ 'website', 'google', 'social', 'reviews', 'directory' ];
	$out   = [];
	if ( empty( $cohort ) ) {
		foreach ( $dests as $d ) {
			$out[] = [ 'destination' => $d, 'sessions' => 0 ];
		}
		return $out;
	}
	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	foreach ( $dests as $d ) {
		$args = array_merge( $cohort, [ $d ] );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT session_id) FROM $table
				WHERE session_id IN ($placeholders)
				AND event_name = 'ProductRevealDestinationSelected'
				AND answer_value = %s",
				$args
			)
		);
		$out[] = [ 'destination' => $d, 'sessions' => $count ];
	}
	return $out;
}

/**
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @param string $since Since datetime.
 * @return array
 */
function jcp_funnel_analytics_question_stats( string $table, array $cohort, string $since ): array {
	global $wpdb;
	$questions = [
		'trade'                   => __( 'Trade', 'jcp-core' ),
		'current_workflow'        => __( 'Workflow', 'jcp-core' ),
		'jobs_per_week'           => __( 'Jobs/week', 'jcp-core' ),
		'public_proof_percentage' => __( 'Public proof', 'jcp-core' ),
	];
	$rows = [];
	foreach ( $questions as $qid => $label ) {
		if ( empty( $cohort ) ) {
			$rows[] = [
				'question'       => $label,
				'viewed'         => 0,
				'answered'       => 0,
				'answer_rate'    => 0,
				'drop_after_view'=> 0,
				'median_ms'      => null,
				'top_answers'    => [],
			];
			continue;
		}
		$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
		$args_base    = array_merge( $cohort, [ $qid ] );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$viewed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT session_id) FROM $table WHERE session_id IN ($placeholders) AND event_name = 'SurveyQuestionViewed' AND question_id = %s",
				$args_base
			)
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$answered = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT session_id) FROM $table WHERE session_id IN ($placeholders) AND event_name = 'SurveyQuestionAnswered' AND question_id = %s",
				$args_base
			)
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$top = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT answer_value AS a, COUNT(DISTINCT session_id) AS c FROM $table WHERE session_id IN ($placeholders) AND event_name = 'SurveyQuestionAnswered' AND question_id = %s AND answer_value IS NOT NULL AND answer_value <> '' GROUP BY answer_value ORDER BY c DESC LIMIT 5",
				$args_base
			),
			ARRAY_A
		);
		$rate = $viewed > 0 ? round( ( $answered / $viewed ) * 100, 1 ) : ( $answered > 0 ? 100.0 : 0.0 );
		$rows[] = [
			'question'        => $label,
			'viewed'          => $viewed,
			'answered'        => $answered,
			'answer_rate'     => $rate,
			'drop_after_view' => max( 0, $viewed - $answered ),
			'median_ms'       => null,
			'top_answers'     => array_map(
				static function ( $r ) {
					return [ 'value' => (string) ( $r['a'] ?? '' ), 'count' => (int) ( $r['c'] ?? 0 ) ];
				},
				$top ?: []
			),
		];
	}
	return $rows;
}

/**
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @param string $since Since.
 * @return array
 */
function jcp_funnel_analytics_traffic_stats( string $table, array $cohort, string $since ): array {
	global $wpdb;
	if ( empty( $cohort ) ) {
		return [];
	}
	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sources = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT COALESCE(NULLIF(utm_source,''),'(none)') AS source,
				COALESCE(NULLIF(utm_campaign,''),'(none)') AS campaign,
				COALESCE(NULLIF(utm_content,''),'(none)') AS content,
				COUNT(DISTINCT session_id) AS landing_sessions
			FROM $table
			WHERE session_id IN ($placeholders)
			AND event_name IN ('SurveyLandingViewed','SurveyStarted')
			GROUP BY source, campaign, content
			ORDER BY landing_sessions DESC
			LIMIT 40",
			$cohort
		),
		ARRAY_A
	);

	$out = [];
	foreach ( $sources ?: [] as $row ) {
		// Approximate conversions among sessions that match this source combo via any event.
		$src = (string) $row['source'];
		$cmp = (string) $row['campaign'];
		$ct  = (string) $row['content'];
		$sess_sql_parts = [ "session_id IN ($placeholders)" ];
		$args           = $cohort;
		if ( $src === '(none)' ) {
			$sess_sql_parts[] = "(utm_source IS NULL OR utm_source = '')";
		} else {
			$sess_sql_parts[] = 'utm_source = %s';
			$args[]           = $src;
		}
		if ( $cmp === '(none)' ) {
			$sess_sql_parts[] = "(utm_campaign IS NULL OR utm_campaign = '')";
		} else {
			$sess_sql_parts[] = 'utm_campaign = %s';
			$args[]           = $cmp;
		}
		if ( $ct === '(none)' ) {
			$sess_sql_parts[] = "(utm_content IS NULL OR utm_content = '')";
		} else {
			$sess_sql_parts[] = 'utm_content = %s';
			$args[]           = $ct;
		}
		$where = implode( ' AND ', $sess_sql_parts );
		$metric = static function ( string $event ) use ( $wpdb, $table, $where, $args ): int {
			$a = array_merge( $args, [ $event ] );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where AND event_name = %s",
					$a
				)
			);
		};
		$landing = (int) $row['landing_sessions'];
		$starts  = $metric( 'SurveyStarted' );
		$result  = $metric( 'SurveyResultViewed' );
		$email   = $metric( 'EmailSubmitted' );
		$cta     = $metric( 'TrialCTAClicked' );
		$out[]   = [
			'source'             => $src,
			'campaign'           => $cmp,
			'content'            => $ct,
			'landing_sessions'   => $landing,
			'starts'             => $starts,
			'survey_completion'  => $landing ? round( ( $result / $landing ) * 100, 1 ) : 0,
			'email_pct'          => $landing ? round( ( $email / $landing ) * 100, 1 ) : 0,
			'trial_cta_pct'      => $landing ? round( ( $cta / $landing ) * 100, 1 ) : 0,
			'trial_start_pct'    => null,
			'activation_pct'     => null,
			'paid_pct'           => null,
		];
	}
	return $out;
}

/**
 * @param string $table Table.
 * @return array
 */
function jcp_funnel_analytics_diagnostics( string $table ): array {
	global $wpdb;
	$meta = get_option( JCP_FUNNEL_ANALYTICS_META_OPTION, [] );
	if ( ! is_array( $meta ) ) {
		$meta = [];
	}
	$since_24h = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );
	$events_24h = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(1) FROM $table WHERE created_at >= %s", $since_24h )
	);
	$missing_source = (int) $wpdb->get_var(
		"SELECT COUNT(DISTINCT session_id) FROM $table WHERE (utm_source IS NULL OR utm_source = '') AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)"
	);
	$missing_campaign = (int) $wpdb->get_var(
		"SELECT COUNT(DISTINCT session_id) FROM $table WHERE (utm_campaign IS NULL OR utm_campaign = '') AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)"
	);
	$missing_funnel = (int) $wpdb->get_var(
		"SELECT COUNT(1) FROM $table WHERE funnel_id IS NULL OR funnel_id = ''"
	);
	$missing_session = (int) $wpdb->get_var(
		"SELECT COUNT(1) FROM $table WHERE session_id IS NULL OR session_id = ''"
	);
	$last = $wpdb->get_var( "SELECT created_at FROM $table ORDER BY id DESC LIMIT 1" );

	return [
		'missing_source_sessions'   => $missing_source,
		'missing_campaign_sessions' => $missing_campaign,
		'duplicate_rejected'        => (int) ( $meta['duplicate_rejected'] ?? 0 ),
		'missing_funnel_id'         => $missing_funnel,
		'missing_session_id'        => $missing_session,
		'last_event_received'       => $last ? (string) $last : (string) ( $meta['last_event_at'] ?? '' ),
		'events_last_24h'           => $events_24h,
		'endpoint_status'           => rest_url( 'jcp/v1/funnel-event' ),
		'retention_days'            => JCP_FUNNEL_ANALYTICS_RETENTION_DAYS,
	];
}

/**
 * Demo adapter — unique sessions from existing demo analytics tables (no PII displayed).
 *
 * @param array $filters Filters.
 * @return array
 */
function jcp_funnel_analytics_demo_adapter_report( array $filters ): array {
	global $wpdb;
	$events = $wpdb->prefix . 'jcp_demo_events';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $events ) );
	if ( $exists !== $events ) {
		return jcp_funnel_analytics_empty_adapter( __( 'Demo events table not found.', 'jcp-core' ) );
	}
	$since = gmdate( 'Y-m-d H:i:s', time() - ( (int) $filters['days'] * DAY_IN_SECONDS ) );
	$map   = [
		[ 'label' => __( 'Landing / demo viewed', 'jcp-core' ), 'types' => [ 'demo_viewed', 'landing_viewed', 'DemoLandingViewed' ] ],
		[ 'label' => __( 'Demo started', 'jcp-core' ), 'types' => [ 'demo_started', 'demo_run_started' ] ],
		[ 'label' => __( 'Demo completed', 'jcp-core' ), 'types' => [ 'demo_completed' ] ],
		[ 'label' => __( 'Converted (early access)', 'jcp-core' ), 'types' => [ 'demo_converted' ] ],
	];
	$stages = [];
	$prev   = 0;
	$land   = 0;
	foreach ( $map as $i => $stage ) {
		$ph   = implode( ',', array_fill( 0, count( $stage['types'] ), '%s' ) );
		$args = array_merge( [ $since ], $stage['types'] );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT session_id) FROM $events WHERE created_at >= %s AND event_type IN ($ph)",
				$args
			)
		);
		if ( $i === 0 ) {
			$land = $count;
		}
		$from_prev = $i === 0 ? 100.0 : ( $prev > 0 ? round( ( $count / $prev ) * 100, 1 ) : 0.0 );
		$drop      = $i === 0 ? 0 : max( 0, $prev - $count );
		$stages[]  = [
			'key'           => 'demo_' . $i,
			'label'         => $stage['label'],
			'sessions'      => $count,
			'from_previous' => $from_prev,
			'drop'          => $drop,
			'drop_pct'      => $i === 0 ? 0.0 : ( $prev > 0 ? round( ( $drop / $prev ) * 100, 1 ) : 0.0 ),
			'from_landing'  => $land > 0 ? round( ( $count / $land ) * 100, 1 ) : 0.0,
			'not_connected' => false,
		];
		$prev = $count;
	}
	return [
		'stages'              => $stages,
		'summary'             => [
			'visitors'          => $stages[0]['sessions'] ?? 0,
			'survey_starts'     => $stages[1]['sessions'] ?? 0,
			'survey_completion' => $stages[2]['sessions'] ?? 0,
			'emails_captured'   => null,
			'trial_cta_clicks'  => null,
			'trials_started'    => null,
			'activated_trials'  => null,
			'paid_customers'    => $stages[3]['sessions'] ?? 0,
		],
		'questions'           => [],
		'traffic'             => [],
		'diagnostics'         => jcp_funnel_analytics_diagnostics( $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE ),
		'lifecycle_connected' => false,
		'cohort_size'         => $stages[0]['sessions'] ?? 0,
		'calculation_note'    => __( 'Demo adapter reads existing jcp_demo_events using unique session_id counts. Legacy Demo Analytics page remains unchanged.', 'jcp-core' ),
	];
}

/**
 * @param array $filters Filters.
 * @return array
 */
function jcp_funnel_analytics_proof_sprint_adapter_report( array $filters ): array {
	// Proof Sprint currently posts milestones into demo-event when email is known.
	$report = jcp_funnel_analytics_demo_adapter_report( $filters );
	$report['calculation_note'] = __( 'Proof Sprint shares demo-event milestones when a contact email is present. Dedicated first-party Proof Sprint stages will appear here as instrumentation expands. Downstream trial/activation/paid remain Not connected unless lifecycle wiring exists.', 'jcp-core' );
	return $report;
}

/**
 * @param string $note Note.
 * @return array
 */
function jcp_funnel_analytics_empty_adapter( string $note ): array {
	return [
		'stages'              => [],
		'summary'             => [],
		'questions'           => [],
		'traffic'             => [],
		'diagnostics'         => [],
		'lifecycle_connected' => false,
		'cohort_size'         => 0,
		'calculation_note'    => $note,
	];
}

/**
 * Retention purge cron.
 */
function jcp_funnel_analytics_schedule_retention(): void {
	if ( ! wp_next_scheduled( 'jcp_funnel_analytics_retention_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'jcp_funnel_analytics_retention_purge' );
	}
}
add_action( 'init', 'jcp_funnel_analytics_schedule_retention', 20 );

/**
 * Purge raw events older than retention window.
 */
function jcp_funnel_analytics_retention_purge(): void {
	global $wpdb;
	jcp_funnel_analytics_maybe_create_table();
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$days  = (int) JCP_FUNNEL_ANALYTICS_RETENTION_DAYS;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $table WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
			$days
		)
	);
}
add_action( 'jcp_funnel_analytics_retention_purge', 'jcp_funnel_analytics_retention_purge' );
