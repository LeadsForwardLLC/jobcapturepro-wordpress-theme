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
define( 'JCP_FUNNEL_ANALYTICS_EXCLUDED_IPS_OPTION', 'jcp_funnel_analytics_excluded_ips' );

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
		creative_concept varchar(128) DEFAULT NULL,
		has_fbclid tinyint(1) NOT NULL DEFAULT 0,
		has_ttclid tinyint(1) NOT NULL DEFAULT 0,
		device_category varchar(32) DEFAULT NULL,
		referrer varchar(512) DEFAULT NULL,
		ip_hash varchar(64) DEFAULT NULL,
		metadata longtext DEFAULT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY event_uuid (event_uuid),
		KEY session_id (session_id),
		KEY funnel_id (funnel_id),
		KEY event_name (event_name),
		KEY created_at (created_at),
		KEY funnel_created (funnel_id, created_at),
		KEY question_id (question_id),
		KEY creative_concept (creative_concept),
		KEY ip_hash (ip_hash)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	// Existing installs: ensure ip_hash column + index (dbDelta with IF NOT EXISTS is unreliable).
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$col = $wpdb->get_results( "SHOW COLUMNS FROM $table LIKE 'ip_hash'" );
	if ( empty( $col ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE $table ADD COLUMN ip_hash varchar(64) DEFAULT NULL AFTER referrer" );
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$idx = $wpdb->get_results( "SHOW INDEX FROM $table WHERE Key_name = 'ip_hash'" );
	if ( empty( $idx ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE $table ADD KEY ip_hash (ip_hash)" );
	}
}
add_action( 'after_switch_theme', 'jcp_funnel_analytics_maybe_create_table' );
add_action( 'init', 'jcp_funnel_analytics_maybe_create_table', 5 );

/**
 * Resolve request client IP (Cloudflare / proxy aware).
 */
function jcp_funnel_analytics_request_ip(): string {
	$candidates = [];
	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$candidates[] = (string) $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$parts = explode( ',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$candidates[] = trim( (string) ( $parts[0] ?? '' ) );
	}
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$candidates[] = (string) $_SERVER['REMOTE_ADDR'];
	}
	foreach ( $candidates as $ip ) {
		$ip = jcp_funnel_analytics_normalize_ip( $ip );
		if ( $ip !== '' ) {
			return $ip;
		}
	}
	return '';
}

/**
 * Normalize an IP string for storage / comparison.
 */
function jcp_funnel_analytics_normalize_ip( string $ip ): string {
	$ip = trim( $ip );
	if ( $ip === '' ) {
		return '';
	}
	// Strip brackets around IPv6 literals.
	if ( $ip[0] === '[' && substr( $ip, -1 ) === ']' ) {
		$ip = substr( $ip, 1, -1 );
	}
	// Drop port on IPv4 host:port.
	if ( substr_count( $ip, ':' ) === 1 && strpos( $ip, '.' ) !== false ) {
		$ip = explode( ':', $ip )[0];
	}
	if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		// Compress IPv6 for stable hashing.
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			$packed = @inet_pton( $ip );
			if ( $packed !== false ) {
				$expanded = inet_ntop( $packed );
				if ( is_string( $expanded ) && $expanded !== '' ) {
					return strtolower( $expanded );
				}
			}
		}
		return $ip;
	}
	return '';
}

/**
 * HMAC hash of an IP (no raw IP stored in event rows).
 */
function jcp_funnel_analytics_ip_hash( string $ip ): string {
	$ip = jcp_funnel_analytics_normalize_ip( $ip );
	if ( $ip === '' ) {
		return '';
	}
	$salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : wp_salt( 'auth' );
	return hash_hmac( 'sha256', $ip, (string) $salt );
}

/**
 * @return list<string>
 */
function jcp_funnel_analytics_get_excluded_ips(): array {
	$raw = get_option( JCP_FUNNEL_ANALYTICS_EXCLUDED_IPS_OPTION, [] );
	if ( is_string( $raw ) ) {
		$raw = preg_split( '/\r\n|\r|\n/', $raw ) ?: [];
	}
	if ( ! is_array( $raw ) ) {
		return [];
	}
	$out = [];
	foreach ( $raw as $ip ) {
		$n = jcp_funnel_analytics_normalize_ip( (string) $ip );
		if ( $n !== '' ) {
			$out[ $n ] = $n;
		}
	}
	return array_values( $out );
}

/**
 * @param list<string>|string $ips IPs.
 * @return list<string>
 */
function jcp_funnel_analytics_save_excluded_ips( $ips ): array {
	if ( is_string( $ips ) ) {
		$ips = preg_split( '/\r\n|\r|\n|,/', $ips ) ?: [];
	}
	$clean = [];
	foreach ( (array) $ips as $ip ) {
		$n = jcp_funnel_analytics_normalize_ip( (string) $ip );
		if ( $n !== '' ) {
			$clean[ $n ] = $n;
		}
	}
	$clean = array_values( $clean );
	update_option( JCP_FUNNEL_ANALYTICS_EXCLUDED_IPS_OPTION, $clean, false );
	return $clean;
}

/**
 * Seed default excluded IPs once (operator testing IPs).
 */
function jcp_funnel_analytics_maybe_seed_excluded_ips(): void {
	if ( get_option( 'jcp_funnel_excluded_ips_seeded', '' ) === '1' ) {
		return;
	}
	$existing = jcp_funnel_analytics_get_excluded_ips();
	$defaults = [
		'188.92.253.150',
		'2001:4860:7:22d::ff',
	];
	$merged = array_values( array_unique( array_merge( $existing, $defaults ) ) );
	jcp_funnel_analytics_save_excluded_ips( $merged );
	update_option( 'jcp_funnel_excluded_ips_seeded', '1', false );
}
add_action( 'init', 'jcp_funnel_analytics_maybe_seed_excluded_ips', 6 );

/**
 * @return list<string> Hashes of excluded IPs.
 */
function jcp_funnel_analytics_excluded_ip_hashes(): array {
	$hashes = [];
	foreach ( jcp_funnel_analytics_get_excluded_ips() as $ip ) {
		$h = jcp_funnel_analytics_ip_hash( $ip );
		if ( $h !== '' ) {
			$hashes[] = $h;
		}
	}
	return array_values( array_unique( $hashes ) );
}

/**
 * Whether an IP is on the exclusion list.
 */
function jcp_funnel_analytics_is_ip_excluded( string $ip ): bool {
	$ip = jcp_funnel_analytics_normalize_ip( $ip );
	if ( $ip === '' ) {
		return false;
	}
	$excluded = jcp_funnel_analytics_get_excluded_ips();
	if ( in_array( $ip, $excluded, true ) ) {
		return true;
	}
	// Also compare hashes in case of IPv6 compression differences.
	$hash = jcp_funnel_analytics_ip_hash( $ip );
	return $hash !== '' && in_array( $hash, jcp_funnel_analytics_excluded_ip_hashes(), true );
}

/**
 * SQL fragment to exclude filtered IPs from reports (event-level).
 *
 * @return array{sql:string,args:array}
 */
function jcp_funnel_analytics_excluded_ip_sql(): array {
	$hashes = jcp_funnel_analytics_excluded_ip_hashes();
	if ( ! $hashes ) {
		return [ 'sql' => '1=1', 'args' => [] ];
	}
	$ph = implode( ',', array_fill( 0, count( $hashes ), '%s' ) );
	return [
		'sql'  => "(ip_hash IS NULL OR ip_hash NOT IN ($ph))",
		'args' => $hashes,
	];
}

/**
 * Delete stored events whose ip_hash matches the exclusion list.
 *
 * @return int Rows deleted.
 */
function jcp_funnel_analytics_purge_excluded_ip_events(): int {
	global $wpdb;
	jcp_funnel_analytics_maybe_create_table();
	$hashes = jcp_funnel_analytics_excluded_ip_hashes();
	if ( ! $hashes ) {
		return 0;
	}
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$ph    = implode( ',', array_fill( 0, count( $hashes ), '%s' ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$deleted = $wpdb->query(
		$wpdb->prepare( "DELETE FROM $table WHERE ip_hash IN ($ph)", $hashes )
	);
	return is_int( $deleted ) ? $deleted : 0;
}

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

	$client_ip = jcp_funnel_analytics_request_ip();
	$ip_hash   = $client_ip !== '' ? jcp_funnel_analytics_ip_hash( $client_ip ) : '';
	if ( $client_ip !== '' && jcp_funnel_analytics_is_ip_excluded( $client_ip ) ) {
		jcp_funnel_analytics_bump_meta( 'excluded_ip_skipped', 1 );
		return rest_ensure_response( [ 'ok' => true, 'filtered' => true ] );
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
		$meta      = [];
	}

	$creative = sanitize_text_field( (string) ( $params['creative_concept'] ?? '' ) );
	if ( $creative === '' && is_array( $meta ) ) {
		$creative = sanitize_text_field( (string) ( $meta['creative_concept'] ?? '' ) );
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
		'creative_concept'     => $creative !== '' ? $creative : null,
		'has_fbclid'           => ! empty( $params['has_fbclid'] ) ? 1 : 0,
		'has_ttclid'           => ! empty( $params['has_ttclid'] ) ? 1 : 0,
		'device_category'      => sanitize_text_field( (string) ( $params['device_category'] ?? $params['device_class'] ?? '' ) ) ?: null,
		'referrer'             => esc_url_raw( (string) ( $params['referrer'] ?? '' ) ) ?: null,
		'ip_hash'              => $ip_hash !== '' ? $ip_hash : null,
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
		[ 'key' => 'reveal', 'label' => __( 'App sim / transform', 'jcp-core' ), 'events' => [ 'AppSimCompleted' ] ],
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
	$compare = ! empty( $_GET['compare'] ) && (string) $_GET['compare'] !== '0';
	return [
		'funnel'            => $funnel,
		'days'              => $days,
		'compare'           => $compare,
		'utm_source'        => sanitize_text_field( (string) ( $_GET['utm_source'] ?? '' ) ),
		'utm_medium'        => sanitize_text_field( (string) ( $_GET['utm_medium'] ?? '' ) ),
		'utm_campaign'      => sanitize_text_field( (string) ( $_GET['utm_campaign'] ?? '' ) ),
		'utm_content'       => sanitize_text_field( (string) ( $_GET['utm_content'] ?? '' ) ),
		'lp_variant'        => sanitize_text_field( (string) ( $_GET['lp_variant'] ?? '' ) ),
		'creative_concept'  => sanitize_text_field( (string) ( $_GET['creative_concept'] ?? '' ) ),
		'trade'             => sanitize_text_field( (string) ( $_GET['trade'] ?? '' ) ),
		'workflow'          => sanitize_text_field( (string) ( $_GET['workflow'] ?? '' ) ),
		'device'            => sanitize_text_field( (string) ( $_GET['device'] ?? '' ) ),
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
	$primary = jcp_funnel_analytics_proof_gap_report_window( $filters, 0 );
	if ( ! empty( $filters['compare'] ) ) {
		$previous                 = jcp_funnel_analytics_proof_gap_report_window( $filters, (int) $filters['days'] );
		$primary['compare']       = jcp_funnel_analytics_summary_delta( $primary['summary'] ?? [], $previous['summary'] ?? [] );
		$primary['compare_note']  = sprintf(
			/* translators: %d: number of days */
			__( 'Compared to the previous %d-day window.', 'jcp-core' ),
			(int) $filters['days']
		);
		$primary['previous_summary'] = $previous['summary'] ?? [];
	}
	return $primary;
}

/**
 * Build a filtered WHERE for Proof Gap events.
 *
 * @param array  $filters Filters.
 * @param int    $offset_days Shift window start/end backward by this many days.
 * @return array{sql:string,args:array,since:string,until:string}
 */
function jcp_funnel_analytics_proof_gap_where( array $filters, int $offset_days = 0 ): array {
	$days   = (int) $filters['days'];
	$until  = gmdate( 'Y-m-d H:i:s', time() - ( $offset_days * DAY_IN_SECONDS ) );
	$since  = gmdate( 'Y-m-d H:i:s', time() - ( ( $offset_days + $days ) * DAY_IN_SECONDS ) );
	// Cohort window only — session-attribute filters applied via EXISTS after cohort build.
	$where  = [ 'funnel_id = %s', 'created_at >= %s', 'created_at < %s' ];
	$args   = [ 'proof_gap', $since, $until ];

	if ( ! empty( $filters['utm_source'] ) ) {
		$where[] = 'utm_source = %s';
		$args[]  = $filters['utm_source'];
	}
	if ( ! empty( $filters['utm_medium'] ) ) {
		$where[] = 'utm_medium = %s';
		$args[]  = $filters['utm_medium'];
	}
	if ( ! empty( $filters['utm_campaign'] ) ) {
		$where[] = 'utm_campaign = %s';
		$args[]  = $filters['utm_campaign'];
	}
	if ( ! empty( $filters['utm_content'] ) ) {
		$where[] = 'utm_content = %s';
		$args[]  = $filters['utm_content'];
	}
	if ( ! empty( $filters['lp_variant'] ) ) {
		$where[] = 'lp_variant = %s';
		$args[]  = $filters['lp_variant'];
	}
	if ( ! empty( $filters['device'] ) ) {
		$where[] = 'device_category = %s';
		$args[]  = $filters['device'];
	}

	$ip_ex   = jcp_funnel_analytics_excluded_ip_sql();
	$where[] = $ip_ex['sql'];
	$args    = array_merge( $args, $ip_ex['args'] );

	return [
		'sql'   => implode( ' AND ', $where ),
		'args'  => $args,
		'since' => $since,
		'until' => $until,
	];
}

/**
 * @param array $filters Filters.
 * @param int   $offset_days Offset.
 * @return array
 */
function jcp_funnel_analytics_proof_gap_report_window( array $filters, int $offset_days = 0 ): array {
	global $wpdb;
	jcp_funnel_analytics_maybe_create_table();
	$table = $wpdb->prefix . JCP_FUNNEL_EVENTS_TABLE;
	$win   = jcp_funnel_analytics_proof_gap_where( $filters, $offset_days );
	$where_sql = $win['sql'];
	$args      = $win['args'];
	$since     = $win['since'];

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$cohort_sql = $wpdb->prepare(
		"SELECT DISTINCT session_id FROM $table WHERE $where_sql AND event_name IN ('SurveyLandingViewed','SurveyStarted')",
		$args
	);
	$cohort = $wpdb->get_col( $cohort_sql );
	$cohort = array_values( array_filter( array_map( 'strval', $cohort ?: [] ) ) );
	$cohort = jcp_funnel_analytics_filter_cohort_by_session_attrs( $table, $cohort, $filters );

	$stages_def = jcp_funnel_analytics_proof_gap_stages();
	$stage_rows = [];
	$prev_count = 0;
	$landing    = 0;

	foreach ( $stages_def as $i => $stage ) {
		$lifecycle = ! empty( $stage['lifecycle'] );
		if ( empty( $cohort ) ) {
			$count = 0;
		} elseif ( $lifecycle ) {
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
		$median_ms = null;
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
			$from_land  = $landing > 0 ? round( ( $count / $landing ) * 100, 1 ) : 0.0;
			$prev_count = $count;
			$median_ms  = jcp_funnel_analytics_median_step_ms( $table, $cohort, (string) $stage['key'], $stage );
		}

		$stage_rows[] = [
			'key'           => $stage['key'],
			'label'         => $stage['label'],
			'sessions'      => $count,
			'from_previous' => $from_prev,
			'drop'          => $drop,
			'drop_pct'      => $drop_pct,
			'from_landing'  => $from_land,
			'median_ms'     => $median_ms,
			'not_connected' => $lifecycle,
		];
	}

	$summary = [
		'visitors'          => $stage_rows[0]['sessions'] ?? 0,
		'survey_starts'     => $stage_rows[1]['sessions'] ?? 0,
		'survey_completion' => $stage_rows[6]['sessions'] ?? 0,
		'emails_captured'   => $stage_rows[7]['sessions'] ?? 0,
		'product_reveal'    => $stage_rows[8]['sessions'] ?? 0,
		'trial_plan_views'  => $stage_rows[9]['sessions'] ?? 0,
		'trial_cta_clicks'  => $stage_rows[10]['sessions'] ?? 0,
		'trials_started'    => null,
		'activated_trials'  => null,
		'paid_customers'    => null,
	];

	$email_to_cta = null;
	if ( is_int( $summary['emails_captured'] ) && is_int( $summary['trial_cta_clicks'] ) && $summary['emails_captured'] > 0 ) {
		$email_to_cta = round( ( $summary['trial_cta_clicks'] / $summary['emails_captured'] ) * 100, 1 );
	}
	$start_to_cta = null;
	if ( is_int( $summary['survey_starts'] ) && is_int( $summary['trial_cta_clicks'] ) && $summary['survey_starts'] > 0 ) {
		$start_to_cta = round( ( $summary['trial_cta_clicks'] / $summary['survey_starts'] ) * 100, 1 );
	}

	return [
		'stages'              => $stage_rows,
		'summary'             => $summary,
		'rates'               => [
			'email_to_trial_cta'  => $email_to_cta,
			'start_to_trial_cta'  => $start_to_cta,
			'start_to_email'      => ( is_int( $summary['survey_starts'] ) && $summary['survey_starts'] > 0 && is_int( $summary['emails_captured'] ) )
				? round( ( $summary['emails_captured'] / $summary['survey_starts'] ) * 100, 1 )
				: null,
			'reveal_reach'        => ( is_int( $summary['survey_starts'] ) && $summary['survey_starts'] > 0 && is_int( $summary['product_reveal'] ) )
				? round( ( $summary['product_reveal'] / $summary['survey_starts'] ) * 100, 1 )
				: null,
		],
		'questions'           => jcp_funnel_analytics_question_stats( $table, $cohort, $since ),
		'destinations'        => jcp_funnel_analytics_destination_stats( $table, $cohort ),
		'destination_insight' => jcp_funnel_analytics_destination_insight( $table, $cohort ),
		'devices'             => jcp_funnel_analytics_device_stats( $table, $cohort ),
		'traffic'             => jcp_funnel_analytics_traffic_stats( $table, $cohort, $since ),
		'diagnostics'         => jcp_funnel_analytics_diagnostics( $table ),
		'lifecycle_connected' => false,
		'cohort_size'         => count( $cohort ),
		'window'              => [ 'since' => $since, 'until' => $win['until'] ],
		'calculation_note'    => __( 'Stage funnel uses unique session_ids whose SurveyLandingViewed or SurveyStarted occurred in the selected window. Conversion % is previous-stage and landing-relative, not raw event counts. Destination tabs count only first user-selected ProductRevealDestinationSelected per destination. Median step time uses SurveyStepTiming. Trial/activation/paid require cross-domain lifecycle wiring (shown as Not connected).', 'jcp-core' ),
	];
}

/**
 * @param array $current Current summary.
 * @param array $previous Previous summary.
 * @return array<string,array{current:mixed,previous:mixed,delta:mixed,delta_pct:mixed}>
 */
function jcp_funnel_analytics_summary_delta( array $current, array $previous ): array {
	$out = [];
	foreach ( $current as $key => $val ) {
		$prev = $previous[ $key ] ?? null;
		$delta = null;
		$pct   = null;
		if ( is_numeric( $val ) && is_numeric( $prev ) ) {
			$delta = (float) $val - (float) $prev;
			$pct   = (float) $prev > 0 ? round( ( $delta / (float) $prev ) * 100, 1 ) : null;
		}
		$out[ $key ] = [
			'current'   => $val,
			'previous'  => $prev,
			'delta'     => $delta,
			'delta_pct' => $pct,
		];
	}
	return $out;
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
 * First destination + average destinations viewed per reveal session.
 *
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @return array{first:array<int,array{destination:string,sessions:int}>,avg_per_session:float,sessions_with_dest:int}
 */
function jcp_funnel_analytics_destination_insight( string $table, array $cohort ): array {
	global $wpdb;
	$empty = [ 'first' => [], 'avg_per_session' => 0.0, 'sessions_with_dest' => 0 ];
	if ( empty( $cohort ) ) {
		return $empty;
	}
	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT session_id, answer_value, created_at
			FROM $table
			WHERE session_id IN ($placeholders)
			AND event_name = 'ProductRevealDestinationSelected'
			AND answer_value IS NOT NULL AND answer_value <> ''
			ORDER BY created_at ASC",
			$cohort
		),
		ARRAY_A
	);
	$first_counts = [];
	$per_session  = [];
	foreach ( $rows ?: [] as $row ) {
		$sid = (string) ( $row['session_id'] ?? '' );
		$dest = (string) ( $row['answer_value'] ?? '' );
		if ( $sid === '' || $dest === '' ) {
			continue;
		}
		if ( ! isset( $first_counts[ $sid ] ) ) {
			$first_counts[ $sid ] = $dest;
		}
		if ( ! isset( $per_session[ $sid ] ) ) {
			$per_session[ $sid ] = [];
		}
		$per_session[ $sid ][ $dest ] = true;
	}
	$agg = [];
	foreach ( $first_counts as $dest ) {
		$agg[ $dest ] = ( $agg[ $dest ] ?? 0 ) + 1;
	}
	arsort( $agg );
	$first = [];
	foreach ( $agg as $dest => $count ) {
		$first[] = [ 'destination' => (string) $dest, 'sessions' => (int) $count ];
	}
	$n = count( $per_session );
	$sum = 0;
	foreach ( $per_session as $set ) {
		$sum += count( $set );
	}
	return [
		'first'              => $first,
		'avg_per_session'    => $n > 0 ? round( $sum / $n, 2 ) : 0.0,
		'sessions_with_dest' => $n,
	];
}

/**
 * Device breakdown for cohort.
 *
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @return array<int,array{device:string,sessions:int}>
 */
function jcp_funnel_analytics_device_stats( string $table, array $cohort ): array {
	global $wpdb;
	if ( empty( $cohort ) ) {
		return [];
	}
	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT COALESCE(NULLIF(device_category,''),'(unknown)') AS device, COUNT(DISTINCT session_id) AS sessions
			FROM $table
			WHERE session_id IN ($placeholders)
			AND event_name IN ('SurveyLandingViewed','SurveyStarted')
			GROUP BY device
			ORDER BY sessions DESC",
			$cohort
		),
		ARRAY_A
	);
	$out = [];
	foreach ( $rows ?: [] as $row ) {
		$out[] = [
			'device'   => (string) ( $row['device'] ?? '(unknown)' ),
			'sessions' => (int) ( $row['sessions'] ?? 0 ),
		];
	}
	return $out;
}

/**
 * Median dwell ms for a funnel stage, mapped from SurveyStepTiming.question_id / screen.
 *
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @param string $stage_key Stage key.
 * @param array  $stage Stage def.
 * @return int|null
 */
function jcp_funnel_analytics_median_step_ms( string $table, array $cohort, string $stage_key, array $stage ): ?int {
	$map = [
		'landing'     => 'welcome',
		'started'     => 'welcome',
		'trade'       => 'trade',
		'workflow'    => 'current_workflow',
		'jobs'        => 'jobs_per_week',
		'proof'       => 'public_proof_percentage',
		'result'      => 'proof_gap_result',
		'email'       => 'email_capture',
		'reveal'      => 'product_reveal',
		'trial_plan'  => 'trial_bridge',
		'trial_cta'   => 'trial_bridge',
	];
	$screen = $map[ $stage_key ] ?? ( $stage['question_id'] ?? '' );
	if ( $screen === '' || empty( $cohort ) ) {
		return null;
	}
	return jcp_funnel_analytics_median_timing_for_screen( $table, $cohort, $screen );
}

/**
 * @param string $table Table.
 * @param array  $cohort Sessions.
 * @param string $screen Screen / question_id.
 * @return int|null
 */
function jcp_funnel_analytics_median_timing_for_screen( string $table, array $cohort, string $screen ): ?int {
	global $wpdb;
	if ( empty( $cohort ) || $screen === '' ) {
		return null;
	}
	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	$args         = array_merge( $cohort, [ $screen, $screen ] );
	// duration_ms is mirrored into answer_value by the client for SQL-friendly medians.
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$vals = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT answer_value
			FROM $table
			WHERE session_id IN ($placeholders)
			AND event_name = 'SurveyStepTiming'
			AND (question_id = %s OR screen = %s)
			AND answer_value REGEXP '^[0-9]{3,8}$'",
			$args
		)
	);
	$nums = [];
	foreach ( $vals ?: [] as $v ) {
		$n = (int) $v;
		if ( $n >= 250 && $n <= 30 * 60 * 1000 ) {
			$nums[] = $n;
		}
	}
	if ( ! $nums ) {
		return null;
	}
	sort( $nums, SORT_NUMERIC );
	$mid = (int) floor( count( $nums ) / 2 );
	if ( count( $nums ) % 2 ) {
		return (int) $nums[ $mid ];
	}
	return (int) round( ( $nums[ $mid - 1 ] + $nums[ $mid ] ) / 2 );
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
			'median_ms'       => jcp_funnel_analytics_median_timing_for_screen( $table, $cohort, $qid ),
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
	$ip_ex      = jcp_funnel_analytics_excluded_ip_sql();
	$since_24h  = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );
	$ip_sql     = $ip_ex['sql'];
	$ip_args    = $ip_ex['args'];

	$q24 = "SELECT COUNT(1) FROM $table WHERE created_at >= %s AND $ip_sql";
	$events_24h = (int) $wpdb->get_var(
		$ip_args
			? $wpdb->prepare( $q24, array_merge( [ $since_24h ], $ip_args ) )
			: $wpdb->prepare( "SELECT COUNT(1) FROM $table WHERE created_at >= %s", $since_24h )
	);

	$q_src = "SELECT COUNT(DISTINCT session_id) FROM $table WHERE (utm_source IS NULL OR utm_source = '') AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY) AND $ip_sql";
	$missing_source = (int) ( $ip_args ? $wpdb->get_var( $wpdb->prepare( $q_src, $ip_args ) ) : $wpdb->get_var( $q_src ) );

	$q_camp = "SELECT COUNT(DISTINCT session_id) FROM $table WHERE (utm_campaign IS NULL OR utm_campaign = '') AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY) AND $ip_sql";
	$missing_campaign = (int) ( $ip_args ? $wpdb->get_var( $wpdb->prepare( $q_camp, $ip_args ) ) : $wpdb->get_var( $q_camp ) );

	$q_funnel = "SELECT COUNT(1) FROM $table WHERE (funnel_id IS NULL OR funnel_id = '') AND $ip_sql";
	$missing_funnel = (int) ( $ip_args ? $wpdb->get_var( $wpdb->prepare( $q_funnel, $ip_args ) ) : $wpdb->get_var( $q_funnel ) );

	$q_sess = "SELECT COUNT(1) FROM $table WHERE (session_id IS NULL OR session_id = '') AND $ip_sql";
	$missing_session = (int) ( $ip_args ? $wpdb->get_var( $wpdb->prepare( $q_sess, $ip_args ) ) : $wpdb->get_var( $q_sess ) );

	$q_last = "SELECT created_at FROM $table WHERE $ip_sql ORDER BY id DESC LIMIT 1";
	$last   = $ip_args ? $wpdb->get_var( $wpdb->prepare( $q_last, $ip_args ) ) : $wpdb->get_var( $q_last );

	return [
		'missing_source_sessions'   => $missing_source,
		'missing_campaign_sessions' => $missing_campaign,
		'duplicate_rejected'        => (int) ( $meta['duplicate_rejected'] ?? 0 ),
		'excluded_ip_skipped'       => (int) ( $meta['excluded_ip_skipped'] ?? 0 ),
		'excluded_ips_count'        => count( jcp_funnel_analytics_get_excluded_ips() ),
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
 * Apply trade/workflow/creative filters via any event in the session (not landing row).
 *
 * @param string $table Table.
 * @param array  $cohort Session IDs.
 * @param array  $filters Filters.
 * @return array
 */
function jcp_funnel_analytics_filter_cohort_by_session_attrs( string $table, array $cohort, array $filters ): array {
	global $wpdb;
	if ( empty( $cohort ) ) {
		return [];
	}
	$attr_cols = [];
	foreach ( [ 'trade', 'workflow', 'creative_concept' ] as $col ) {
		if ( ! empty( $filters[ $col ] ) ) {
			$attr_cols[ $col ] = (string) $filters[ $col ];
		}
	}
	if ( ! $attr_cols ) {
		return $cohort;
	}

	$placeholders = implode( ',', array_fill( 0, count( $cohort ), '%s' ) );
	$args         = $cohort;
	$exists       = [];
	foreach ( $attr_cols as $col => $val ) {
		$exists[] = "EXISTS (
			SELECT 1 FROM $table e
			WHERE e.session_id = s.session_id
			AND e.funnel_id = 'proof_gap'
			AND e.$col = %s
		)";
		$args[] = $val;
	}
	$exists_sql = implode( ' AND ', $exists );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare(
		"SELECT DISTINCT s.session_id FROM $table s
		WHERE s.session_id IN ($placeholders)
		AND $exists_sql",
		$args
	);
	$filtered = $wpdb->get_col( $sql );
	return array_values( array_filter( array_map( 'strval', $filtered ?: [] ) ) );
}

/**
 * @param array $filters Filters.
 * @return array
 */
function jcp_funnel_analytics_proof_sprint_adapter_report( array $filters ): array {
	return jcp_funnel_analytics_empty_adapter(
		__( 'Proof Sprint is not yet wired into first-party Funnel Analytics (funnel_id=proof_sprint). This selector stays empty until dedicated instrumentation lands. Use Demo Analytics for legacy Proof Sprint milestones.', 'jcp-core' )
	);
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
