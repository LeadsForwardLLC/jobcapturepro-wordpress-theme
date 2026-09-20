<?php
/**
 * Funnel Analytics: first-party events table, REST ingest, report helper.
 * No PII columns (no email/name/phone/business_name).
 *
 * For proof-gap.js (other agent): funnelEventUrl = rest_url( 'jcp/v1/funnel-event' )
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Table name (without prefix). */
define( 'JCP_FUNNEL_EVENTS_TABLE', 'jcp_funnel_events' );

/** Retention days for funnel events. */
define( 'JCP_FUNNEL_EVENTS_RETENTION_DAYS', 120 );

/** Cron hook for retention cleanup. */
define( 'JCP_FUNNEL_ANALYTICS_RETENTION_CRON', 'jcp_funnel_analytics_retention' );

/** Max public POSTs per IP per minute (rate-safe ingest). */
define( 'JCP_FUNNEL_EVENT_RATE_LIMIT', 60 );

/**
 * Allowed funnel event names for REST ingest.
 *
 * @return string[]
 */
function jcp_funnel_analytics_allowed_event_names(): array {
	return [
		'SurveyLandingViewed',
		'SurveyStarted',
		'SurveyQuestionAnswered',
		'SurveyResultViewed',
		'EmailCaptureViewed',
		'EmailSubmitted',
		'ProductRevealStarted',
		'ProductRevealCompleted',
		'TrialCTAViewed',
		'TrialCTAClicked',
		'SurveyResumed',
		'SurveyExited',
	];
}

/**
 * Stage definitions for proof_gap unique-session funnel.
 * SurveyQuestionAnswered stages are scoped by question_id.
 *
 * @return array<int, array{key: string, label: string, event_name: string, question_id?: string}>
 */
function jcp_funnel_analytics_proof_gap_stages(): array {
	return [
		[
			'key'        => 'landing_viewed',
			'label'      => 'SurveyLandingViewed',
			'event_name' => 'SurveyLandingViewed',
		],
		[
			'key'        => 'survey_started',
			'label'      => 'SurveyStarted',
			'event_name' => 'SurveyStarted',
		],
		[
			'key'          => 'answered_trade',
			'label'        => 'SurveyQuestionAnswered(trade)',
			'event_name'   => 'SurveyQuestionAnswered',
			'question_id'  => 'trade',
		],
		[
			'key'          => 'answered_workflow',
			'label'        => 'SurveyQuestionAnswered(current_workflow)',
			'event_name'   => 'SurveyQuestionAnswered',
			'question_id'  => 'current_workflow',
		],
		[
			'key'          => 'answered_jobs',
			'label'        => 'SurveyQuestionAnswered(jobs_per_week)',
			'event_name'   => 'SurveyQuestionAnswered',
			'question_id'  => 'jobs_per_week',
		],
		[
			'key'          => 'answered_proof',
			'label'        => 'SurveyQuestionAnswered(public_proof_percentage)',
			'event_name'   => 'SurveyQuestionAnswered',
			'question_id'  => 'public_proof_percentage',
		],
		[
			'key'        => 'result_viewed',
			'label'      => 'SurveyResultViewed',
			'event_name' => 'SurveyResultViewed',
		],
		[
			'key'        => 'email_capture_viewed',
			'label'      => 'EmailCaptureViewed',
			'event_name' => 'EmailCaptureViewed',
		],
		[
			'key'        => 'email_submitted',
			'label'      => 'EmailSubmitted',
			'event_name' => 'EmailSubmitted',
		],
		[
			'key'        => 'product_reveal_started',
			'label'      => 'ProductRevealStarted',
			'event_name' => 'ProductRevealStarted',
		],
		[
			'key'        => 'product_reveal_completed',
			'label'      => 'ProductRevealCompleted',
			'event_name' => 'ProductRevealCompleted',
		],
		[
			'key'        => 'trial_cta_viewed',
			'label'      => 'TrialCTAViewed',
			'event_name' => 'TrialCTAViewed',
		],
		[
			'key'        => 'trial_cta_clicked',
			'label'      => 'TrialCTAClicked',
			'event_name' => 'TrialCTAClicked',
		],
	];
}

/**
 * Create funnel events table if it doesn't exist.
 */
function jcp_funnel_analytics_maybe_create_table(): void {
	global $wpdb;
	$table   = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		event_uuid varchar(64) NOT NULL,
		session_id varchar(64) NOT NULL,
		funnel_id varchar(64) NOT NULL DEFAULT 'proof_gap',
		funnel_version varchar(32) DEFAULT NULL,
		lp_variant varchar(64) DEFAULT NULL,
		event_name varchar(64) NOT NULL,
		screen_state varchar(64) DEFAULT NULL,
		question_index int(11) DEFAULT NULL,
		question_id varchar(64) DEFAULT NULL,
		answer_key varchar(128) DEFAULT NULL,
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
		device_class varchar(32) DEFAULT NULL,
		referrer_host varchar(255) DEFAULT NULL,
		creative_concept varchar(128) DEFAULT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY event_uuid (event_uuid),
		KEY session_id (session_id),
		KEY funnel_id (funnel_id),
		KEY event_name (event_name),
		KEY created_at (created_at),
		KEY funnel_created (funnel_id, created_at),
		KEY question_id (question_id),
		KEY utm_source (utm_source(191)),
		KEY utm_campaign (utm_campaign(191))
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Schedule daily retention cron if not already scheduled.
 */
function jcp_funnel_analytics_schedule_retention(): void {
	if ( ! wp_next_scheduled( JCP_FUNNEL_ANALYTICS_RETENTION_CRON ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', JCP_FUNNEL_ANALYTICS_RETENTION_CRON );
	}
}

/**
 * Delete funnel events older than retention window.
 */
function jcp_funnel_analytics_run_retention(): void {
	global $wpdb;
	jcp_funnel_analytics_maybe_create_table();
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$days  = (int) JCP_FUNNEL_EVENTS_RETENTION_DAYS;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $table WHERE created_at < ( NOW() - INTERVAL %d DAY )",
			$days
		)
	);
}

/**
 * Simple IP-based rate limit for public ingest. Returns true if allowed.
 */
function jcp_funnel_analytics_rate_limit_ok(): bool {
	$ip = '';
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && is_string( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$parts = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$ip    = trim( (string) $parts[0] );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
	}
	$ip = sanitize_text_field( $ip );
	if ( $ip === '' ) {
		$ip = 'unknown';
	}
	$key   = 'jcp_funnel_rl_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= (int) JCP_FUNNEL_EVENT_RATE_LIMIT ) {
		return false;
	}
	set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
	return true;
}

/**
 * Truncate a sanitized string to max length, or null if empty.
 *
 * @param mixed $value Raw value.
 * @param int   $max   Max length.
 * @return string|null
 */
function jcp_funnel_analytics_nullable_str( $value, int $max ): ?string {
	if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
		return null;
	}
	$s = sanitize_text_field( (string) $value );
	$s = trim( $s );
	if ( $s === '' ) {
		return null;
	}
	return substr( $s, 0, $max );
}

/**
 * Register REST route for funnel events.
 */
function jcp_funnel_analytics_register_rest_route(): void {
	register_rest_route(
		'jcp/v1',
		'/funnel-event',
		[
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'jcp_funnel_analytics_handle_event',
			'args'                => [
				'event_uuid'           => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $v ) {
						return is_string( $v ) && strlen( $v ) >= 8 && strlen( $v ) <= 64;
					},
				],
				'session_id'           => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $v ) {
						return is_string( $v ) && strlen( $v ) >= 1 && strlen( $v ) <= 64;
					},
				],
				'funnel_id'            => [
					'required'          => false,
					'type'              => 'string',
					'default'           => 'proof_gap',
					'sanitize_callback' => 'sanitize_key',
				],
				'funnel_version'       => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'lp_variant'           => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'event_name'           => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $v ) {
						return is_string( $v ) && in_array( $v, jcp_funnel_analytics_allowed_event_names(), true );
					},
				],
				'screen_state'         => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'question_index'       => [
					'required' => false,
					'type'     => 'integer',
				],
				'question_id'          => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'answer_key'           => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'trade'                => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'jobs_per_week_bucket' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'proof_gap_band'       => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'workflow'             => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'utm_source'           => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'utm_medium'           => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'utm_campaign'         => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'utm_content'          => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'utm_term'             => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'has_fbclid'           => [
					'required' => false,
					'type'     => 'boolean',
				],
				'has_ttclid'           => [
					'required' => false,
					'type'     => 'boolean',
				],
				'device_class'         => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'referrer_host'        => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'creative_concept'     => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		]
	);
}

/**
 * REST handler: rate-limit, dedupe on event_uuid, insert (no PII).
 *
 * @param \WP_REST_Request $request Request.
 * @return \WP_REST_Response|\WP_Error
 */
function jcp_funnel_analytics_handle_event( \WP_REST_Request $request ) {
	if ( ! jcp_funnel_analytics_rate_limit_ok() ) {
		return new \WP_Error(
			'jcp_funnel_rate_limited',
			__( 'Too many requests. Try again shortly.', 'jcp-core' ),
			[ 'status' => 429 ]
		);
	}

	jcp_funnel_analytics_maybe_create_table();

	global $wpdb;
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;

	$event_uuid = (string) $request->get_param( 'event_uuid' );
	$session_id = (string) $request->get_param( 'session_id' );
	$event_name = (string) $request->get_param( 'event_name' );

	$exists = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT 1 FROM $table WHERE event_uuid = %s LIMIT 1",
			$event_uuid
		)
	);
	if ( $exists ) {
		return new \WP_REST_Response( [ 'ok' => true, 'deduped' => true ], 200 );
	}

	$funnel_id = jcp_funnel_analytics_nullable_str( $request->get_param( 'funnel_id' ), 64 );
	if ( $funnel_id === null ) {
		$funnel_id = 'proof_gap';
	}

	$question_index = $request->get_param( 'question_index' );
	$qi_val         = null;
	if ( is_numeric( $question_index ) ) {
		$qi_val = (int) $question_index;
	}

	$has_fbclid = $request->get_param( 'has_fbclid' ) ? 1 : 0;
	$has_ttclid = $request->get_param( 'has_ttclid' ) ? 1 : 0;

	$row = [
		'event_uuid'           => $event_uuid,
		'session_id'           => $session_id,
		'funnel_id'            => $funnel_id,
		'funnel_version'       => jcp_funnel_analytics_nullable_str( $request->get_param( 'funnel_version' ), 32 ),
		'lp_variant'           => jcp_funnel_analytics_nullable_str( $request->get_param( 'lp_variant' ), 64 ),
		'event_name'           => $event_name,
		'screen_state'         => jcp_funnel_analytics_nullable_str( $request->get_param( 'screen_state' ), 64 ),
		'question_index'       => $qi_val,
		'question_id'          => jcp_funnel_analytics_nullable_str( $request->get_param( 'question_id' ), 64 ),
		'answer_key'           => jcp_funnel_analytics_nullable_str( $request->get_param( 'answer_key' ), 128 ),
		'trade'                => jcp_funnel_analytics_nullable_str( $request->get_param( 'trade' ), 64 ),
		'jobs_per_week_bucket' => jcp_funnel_analytics_nullable_str( $request->get_param( 'jobs_per_week_bucket' ), 64 ),
		'proof_gap_band'       => jcp_funnel_analytics_nullable_str( $request->get_param( 'proof_gap_band' ), 64 ),
		'workflow'             => jcp_funnel_analytics_nullable_str( $request->get_param( 'workflow' ), 64 ),
		'utm_source'           => jcp_funnel_analytics_nullable_str( $request->get_param( 'utm_source' ), 255 ),
		'utm_medium'           => jcp_funnel_analytics_nullable_str( $request->get_param( 'utm_medium' ), 255 ),
		'utm_campaign'         => jcp_funnel_analytics_nullable_str( $request->get_param( 'utm_campaign' ), 255 ),
		'utm_content'          => jcp_funnel_analytics_nullable_str( $request->get_param( 'utm_content' ), 255 ),
		'utm_term'             => jcp_funnel_analytics_nullable_str( $request->get_param( 'utm_term' ), 255 ),
		'has_fbclid'           => $has_fbclid,
		'has_ttclid'           => $has_ttclid,
		'device_class'         => jcp_funnel_analytics_nullable_str( $request->get_param( 'device_class' ), 32 ),
		'referrer_host'        => jcp_funnel_analytics_nullable_str( $request->get_param( 'referrer_host' ), 255 ),
		'creative_concept'     => jcp_funnel_analytics_nullable_str( $request->get_param( 'creative_concept' ), 128 ),
		'created_at'           => current_time( 'mysql' ),
	];

	$formats = [
		'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s',
		'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d',
		'%d', '%s', '%s', '%s', '%s',
	];

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional first-party analytics insert.
	$inserted = $wpdb->insert( $table, $row, $formats );

	if ( $inserted === false ) {
		// Race on unique event_uuid: treat as success (deduped).
		$again = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM $table WHERE event_uuid = %s LIMIT 1",
				$event_uuid
			)
		);
		if ( $again ) {
			return new \WP_REST_Response( [ 'ok' => true, 'deduped' => true ], 200 );
		}
		return new \WP_REST_Response( [ 'ok' => true ], 200 );
	}

	return new \WP_REST_Response( [ 'ok' => true ], 200 );
}

/**
 * Build WHERE clause fragments for report filters.
 *
 * @param array<string, mixed> $args Report args.
 * @return array{sql: string, params: array<int, mixed>}
 */
function jcp_funnel_analytics_report_where( array $args ): array {
	$funnel_id    = isset( $args['funnel_id'] ) ? sanitize_key( (string) $args['funnel_id'] ) : 'proof_gap';
	$date_from    = isset( $args['date_from'] ) ? sanitize_text_field( (string) $args['date_from'] ) : '';
	$date_to      = isset( $args['date_to'] ) ? sanitize_text_field( (string) $args['date_to'] ) : '';
	$utm_source   = isset( $args['utm_source'] ) ? sanitize_text_field( (string) $args['utm_source'] ) : '';
	$utm_campaign = isset( $args['utm_campaign'] ) ? sanitize_text_field( (string) $args['utm_campaign'] ) : '';

	$clauses = [ 'funnel_id = %s' ];
	$params  = [ $funnel_id ];

	if ( $date_from !== '' && preg_match( '/^\d{4}-\d{2}-\d{2}/', $date_from ) ) {
		$clauses[] = 'created_at >= %s';
		$params[]  = substr( $date_from, 0, 10 ) . ' 00:00:00';
	}
	if ( $date_to !== '' && preg_match( '/^\d{4}-\d{2}-\d{2}/', $date_to ) ) {
		$clauses[] = 'created_at <= %s';
		$params[]  = substr( $date_to, 0, 10 ) . ' 23:59:59';
	}
	if ( $utm_source !== '' ) {
		$clauses[] = 'utm_source = %s';
		$params[]  = $utm_source;
	}
	if ( $utm_campaign !== '' ) {
		$clauses[] = 'utm_campaign = %s';
		$params[]  = $utm_campaign;
	}

	return [
		'sql'    => implode( ' AND ', $clauses ),
		'params' => $params,
	];
}

/**
 * Count distinct sessions for a stage definition under filters.
 *
 * @param string               $where_sql Where SQL (placeholders).
 * @param array<int, mixed>    $params    Prepare params.
 * @param array<string, mixed> $stage     Stage def.
 * @return int
 */
function jcp_funnel_analytics_count_stage_sessions( string $where_sql, array $params, array $stage ): int {
	global $wpdb;
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;

	$extra  = ' AND event_name = %s';
	$params = array_merge( $params, [ $stage['event_name'] ] );
	if ( ! empty( $stage['question_id'] ) ) {
		$extra   .= ' AND question_id = %s';
		$params[] = $stage['question_id'];
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table + where built from fixed fragments.
	$sql = $wpdb->prepare(
		"SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where_sql$extra",
		...$params
	);

	return (int) $wpdb->get_var( $sql );
}

/**
 * Question answer distribution for SurveyQuestionAnswered events.
 *
 * @param string            $where_sql Where SQL.
 * @param array<int, mixed> $params    Prepare params.
 * @return array<int, array{question_id: string, answer_key: string, sessions: int, pct: float}>
 */
function jcp_funnel_analytics_question_stats( string $where_sql, array $params ): array {
	global $wpdb;
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare(
		"SELECT question_id, answer_key, COUNT(DISTINCT session_id) AS sessions
		 FROM $table
		 WHERE $where_sql
		   AND event_name = 'SurveyQuestionAnswered'
		   AND question_id IS NOT NULL AND question_id != ''
		   AND answer_key IS NOT NULL AND answer_key != ''
		 GROUP BY question_id, answer_key
		 ORDER BY question_id ASC, sessions DESC",
		...$params
	);

	$rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return [];
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$tot_sql = $wpdb->prepare(
		"SELECT question_id, COUNT(DISTINCT session_id) AS sessions
		 FROM $table
		 WHERE $where_sql
		   AND event_name = 'SurveyQuestionAnswered'
		   AND question_id IS NOT NULL AND question_id != ''
		 GROUP BY question_id",
		...$params
	);
	$tot_rows = $wpdb->get_results( $tot_sql, ARRAY_A );
	$totals   = [];
	if ( is_array( $tot_rows ) ) {
		foreach ( $tot_rows as $trow ) {
			$totals[ (string) ( $trow['question_id'] ?? '' ) ] = (int) ( $trow['sessions'] ?? 0 );
		}
	}

	$out = [];
	foreach ( $rows as $row ) {
		$qid = (string) ( $row['question_id'] ?? '' );
		$cnt = (int) ( $row['sessions'] ?? 0 );
		$tot = (int) ( $totals[ $qid ] ?? 0 );
		$out[] = [
			'question_id' => $qid,
			'answer_key'  => (string) ( $row['answer_key'] ?? '' ),
			'sessions'    => $cnt,
			'pct'         => $tot > 0 ? round( ( $cnt / $tot ) * 100, 1 ) : 0.0,
		];
	}
	return $out;
}

/**
 * Traffic / source breakdown (utm_source × utm_campaign).
 *
 * @param string            $where_sql Where SQL.
 * @param array<int, mixed> $params    Prepare params.
 * @return array<int, array{utm_source: string, utm_campaign: string, sessions: int, pct: float}>
 */
function jcp_funnel_analytics_source_breakdown( string $where_sql, array $params ): array {
	global $wpdb;
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare(
		"SELECT
			COALESCE(NULLIF(TRIM(utm_source), ''), '') AS utm_source,
			COALESCE(NULLIF(TRIM(utm_campaign), ''), '') AS utm_campaign,
			COUNT(DISTINCT session_id) AS sessions
		 FROM $table
		 WHERE $where_sql
		 GROUP BY COALESCE(NULLIF(TRIM(utm_source), ''), ''), COALESCE(NULLIF(TRIM(utm_campaign), ''), '')
		 ORDER BY sessions DESC
		 LIMIT 50",
		...$params
	);

	$rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return [];
	}

	$total = 0;
	foreach ( $rows as $row ) {
		$total += (int) ( $row['sessions'] ?? 0 );
	}

	$out = [];
	foreach ( $rows as $row ) {
		$cnt = (int) ( $row['sessions'] ?? 0 );
		$out[] = [
			'utm_source'   => (string) ( $row['utm_source'] ?? '' ),
			'utm_campaign' => (string) ( $row['utm_campaign'] ?? '' ),
			'sessions'     => $cnt,
			'pct'          => $total > 0 ? round( ( $cnt / $total ) * 100, 1 ) : 0.0,
		];
	}
	return $out;
}

/**
 * Data quality metrics for the filtered window.
 *
 * @param string            $where_sql Where SQL.
 * @param array<int, mixed> $params    Prepare params.
 * @return array<string, mixed>
 */
function jcp_funnel_analytics_data_quality( string $where_sql, array $params ): array {
	global $wpdb;
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total_events = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $where_sql", ...$params )
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total_sessions = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where_sql", ...$params )
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$with_utm = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where_sql AND utm_source IS NOT NULL AND TRIM(utm_source) != ''",
			...$params
		)
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$with_device = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where_sql AND device_class IS NOT NULL AND TRIM(device_class) != ''",
			...$params
		)
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$with_click_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT session_id) FROM $table WHERE $where_sql AND (has_fbclid = 1 OR has_ttclid = 1)",
			...$params
		)
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$oldest = $wpdb->get_var(
		$wpdb->prepare( "SELECT MIN(created_at) FROM $table WHERE $where_sql", ...$params )
	);
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$newest = $wpdb->get_var(
		$wpdb->prepare( "SELECT MAX(created_at) FROM $table WHERE $where_sql", ...$params )
	);

	return [
		'total_events'           => $total_events,
		'total_sessions'         => $total_sessions,
		'sessions_with_utm'      => $with_utm,
		'sessions_with_device'   => $with_device,
		'sessions_with_click_id' => $with_click_id,
		'utm_coverage_pct'       => $total_sessions > 0 ? round( ( $with_utm / $total_sessions ) * 100, 1 ) : 0.0,
		'oldest_event'           => $oldest ? (string) $oldest : null,
		'newest_event'           => $newest ? (string) $newest : null,
		'retention_days'         => (int) JCP_FUNNEL_EVENTS_RETENTION_DAYS,
		'pii_columns'            => false,
	];
}

/**
 * Unique-session stage funnel report for a funnel_id.
 *
 * Activation / Paid stages return status `not_connected` (not fake zeros).
 * Demo funnel_id returns a stub adapter response.
 *
 * @param array<string, mixed> $args {
 *     @type string $funnel_id    Default proof_gap. Use `demo` for adapter stub.
 *     @type string $date_from    Y-m-d inclusive.
 *     @type string $date_to      Y-m-d inclusive.
 *     @type string $utm_source   Optional filter.
 *     @type string $utm_campaign Optional filter.
 * }
 * @return array<string, mixed>
 */
function jcp_funnel_analytics_get_report( array $args = [] ): array {
	$funnel_id = isset( $args['funnel_id'] ) ? sanitize_key( (string) $args['funnel_id'] ) : 'proof_gap';
	if ( $funnel_id === '' ) {
		$funnel_id = 'proof_gap';
	}

	// Demo adapter stub — does not read demo analytics tables.
	if ( $funnel_id === 'demo' ) {
		return [
			'funnel_id'        => 'demo',
			'status'           => 'adapter_stub',
			'message'          => __( 'Demo funnel adapter is not connected to this report yet. Use Demo Analytics for /demo/ metrics.', 'jcp-core' ),
			'stages'           => [],
			'question_stats'   => [],
			'source_breakdown' => [],
			'summary'          => [
				'total_sessions' => 0,
				'started'        => 0,
				'email_submitted'=> 0,
				'trial_clicked'  => 0,
			],
			'activation'       => [ 'status' => 'not_connected' ],
			'paid'             => [ 'status' => 'not_connected' ],
			'data_quality'     => [],
		];
	}

	jcp_funnel_analytics_maybe_create_table();

	$args['funnel_id'] = $funnel_id;
	$where             = jcp_funnel_analytics_report_where( $args );
	$where_sql         = $where['sql'];
	$params            = $where['params'];

	$stage_defs    = ( $funnel_id === 'proof_gap' ) ? jcp_funnel_analytics_proof_gap_stages() : [];
	$stages        = [];
	$prev_count    = null;
	$landing_count = 0;

	foreach ( $stage_defs as $def ) {
		$count = jcp_funnel_analytics_count_stage_sessions( $where_sql, $params, $def );
		if ( empty( $stages ) ) {
			$landing_count = $count;
		}
		$pct     = $landing_count > 0 ? round( ( $count / $landing_count ) * 100, 1 ) : 0.0;
		$dropoff = ( $prev_count !== null && $prev_count > 0 )
			? round( ( ( $prev_count - $count ) / $prev_count ) * 100, 1 )
			: 0.0;
		$stages[] = [
			'key'      => $def['key'],
			'label'    => $def['label'],
			'sessions' => $count,
			'pct'      => $pct,
			'dropoff'  => $dropoff,
		];
		$prev_count = $count;
	}

	$landing   = (int) ( $stages[0]['sessions'] ?? 0 );
	$started   = 0;
	$email_sub = 0;
	$trial_clk = 0;
	foreach ( $stages as $s ) {
		if ( $s['key'] === 'survey_started' ) {
			$started = (int) $s['sessions'];
		}
		if ( $s['key'] === 'email_submitted' ) {
			$email_sub = (int) $s['sessions'];
		}
		if ( $s['key'] === 'trial_cta_clicked' ) {
			$trial_clk = (int) $s['sessions'];
		}
	}

	return [
		'funnel_id'        => $funnel_id,
		'status'           => 'ok',
		'stages'           => $stages,
		'question_stats'   => jcp_funnel_analytics_question_stats( $where_sql, $params ),
		'source_breakdown' => jcp_funnel_analytics_source_breakdown( $where_sql, $params ),
		'summary'          => [
			'total_sessions'  => $landing,
			'started'         => $started,
			'email_submitted' => $email_sub,
			'trial_clicked'   => $trial_clk,
			'start_rate'      => $landing > 0 ? round( ( $started / $landing ) * 100, 1 ) : 0.0,
			'email_rate'      => $started > 0 ? round( ( $email_sub / $started ) * 100, 1 ) : 0.0,
			'trial_rate'      => $email_sub > 0 ? round( ( $trial_clk / $email_sub ) * 100, 1 ) : 0.0,
		],
		'activation'       => [ 'status' => 'not_connected' ],
		'paid'             => [ 'status' => 'not_connected' ],
		'data_quality'     => jcp_funnel_analytics_data_quality( $where_sql, $params ),
		'filters'          => [
			'funnel_id'    => $funnel_id,
			'date_from'    => $args['date_from'] ?? '',
			'date_to'      => $args['date_to'] ?? '',
			'utm_source'   => $args['utm_source'] ?? '',
			'utm_campaign' => $args['utm_campaign'] ?? '',
		],
	];
}

add_action( 'rest_api_init', 'jcp_funnel_analytics_register_rest_route' );
add_action( 'after_switch_theme', 'jcp_funnel_analytics_maybe_create_table' );
add_action( 'after_switch_theme', 'jcp_funnel_analytics_schedule_retention' );
add_action( 'init', 'jcp_funnel_analytics_schedule_retention' );
add_action( JCP_FUNNEL_ANALYTICS_RETENTION_CRON, 'jcp_funnel_analytics_run_retention' );
