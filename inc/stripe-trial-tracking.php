<?php
/**
 * Marketing-owned Stripe trial conversion tracking.
 *
 * Stripe customer.subscription.created → qualify JCP free trial → join lead by email
 * → PostHog trial_started + Meta CAPI StartTrial.
 *
 * Secrets (wp-config.php or environment — never commit):
 * - JCP_STRIPE_WEBHOOK_SECRET
 * - JCP_STRIPE_SECRET_KEY (customer retrieve only)
 * - JCP_META_CAPI_ACCESS_TOKEN
 * - JCP_META_CAPI_TEST_EVENT_CODE (optional QA)
 * - JCP_POSTHOG_PROJECT_API_KEY (optional; falls back to public project key)
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JCP_TRIAL_CONVERSIONS_TABLE', 'jcp_trial_conversions' );
define( 'JCP_TRIAL_PLAN_LOOKUP_KEY', 'scale_monthly' );
define( 'JCP_TRIAL_DAYS', 14 );
define( 'JCP_META_PIXEL_ID_DEFAULT', '1440845294314184' );
define( 'JCP_POSTHOG_PROJECT_KEY_DEFAULT', 'phc_v8emzqtZ8beAjLsqj2byb5fK8wRHbW2g6hXBqAEZPMyS' );

/**
 * Resolve a secret from constant or environment.
 *
 * @param string $name Constant / env name.
 */
function jcp_trial_secret( string $name ): string {
	if ( defined( $name ) ) {
		$v = constant( $name );
		if ( is_string( $v ) && $v !== '' ) {
			return $v;
		}
	}
	$env = getenv( $name );
	return is_string( $env ) ? $env : '';
}

/**
 * Create trial conversions + ensure lead attribution column.
 */
function jcp_trial_conversions_maybe_create_table(): void {
	global $wpdb;
	$table   = $wpdb->prefix . JCP_TRIAL_CONVERSIONS_TABLE;
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		conversion_id varchar(128) NOT NULL,
		stripe_event_id varchar(128) NOT NULL DEFAULT '',
		stripe_subscription_id varchar(64) NOT NULL,
		stripe_customer_id varchar(64) NOT NULL DEFAULT '',
		organization_id varchar(64) NOT NULL DEFAULT '',
		email_norm varchar(255) NOT NULL DEFAULT '',
		attribution_status varchar(32) NOT NULL DEFAULT 'unmatched',
		lead_id bigint(20) unsigned DEFAULT NULL,
		attribution_json longtext DEFAULT NULL,
		trial_payload_json longtext DEFAULT NULL,
		posthog_status varchar(20) NOT NULL DEFAULT 'pending',
		meta_status varchar(20) NOT NULL DEFAULT 'pending',
		last_error text DEFAULT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY conversion_id (conversion_id),
		KEY stripe_event_id (stripe_event_id),
		KEY email_norm (email_norm),
		KEY posthog_status (posthog_status),
		KEY meta_status (meta_status)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	if ( function_exists( 'jcp_demo_lead_queue_maybe_create_table' ) ) {
		jcp_demo_lead_queue_maybe_create_table();
	}
}
add_action( 'after_switch_theme', 'jcp_trial_conversions_maybe_create_table' );
add_action( 'init', 'jcp_trial_conversions_maybe_create_table', 5 );

/**
 * Register Stripe webhook REST route.
 */
function jcp_trial_register_rest_routes(): void {
	register_rest_route(
		'jcp/v1',
		'/stripe-trial-webhook',
		[
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'jcp_trial_stripe_webhook_handler',
		]
	);
}
add_action( 'rest_api_init', 'jcp_trial_register_rest_routes' );

/**
 * Verify Stripe-Signature header against raw body.
 *
 * @param string $payload    Raw request body.
 * @param string $sig_header Stripe-Signature header.
 * @param string $secret     whsec_… secret.
 * @param int    $tolerance  Seconds.
 */
function jcp_trial_verify_stripe_signature( string $payload, string $sig_header, string $secret, int $tolerance = 300 ): bool {
	if ( $payload === '' || $sig_header === '' || $secret === '' ) {
		return false;
	}

	$parts = [];
	foreach ( explode( ',', $sig_header ) as $item ) {
		$kv = explode( '=', trim( $item ), 2 );
		if ( count( $kv ) === 2 ) {
			$parts[ $kv[0] ][] = $kv[1];
		}
	}
	if ( empty( $parts['t'][0] ) || empty( $parts['v1'] ) ) {
		return false;
	}

	$timestamp = (int) $parts['t'][0];
	if ( abs( time() - $timestamp ) > $tolerance ) {
		return false;
	}

	$signed_payload = $timestamp . '.' . $payload;
	$expected       = hash_hmac( 'sha256', $signed_payload, $secret );

	foreach ( $parts['v1'] as $sig ) {
		if ( hash_equals( $expected, $sig ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Normalize email for matching.
 */
function jcp_trial_normalize_email( string $email ): string {
	return strtolower( trim( $email ) );
}

/**
 * Build canonical conversion id.
 */
function jcp_trial_conversion_id( string $subscription_id ): string {
	return 'jcp_trial_' . $subscription_id;
}

/**
 * Extract price lookup key from a Stripe subscription object array.
 *
 * @param array<string, mixed> $subscription Subscription.
 */
function jcp_trial_subscription_lookup_key( array $subscription ): string {
	$meta = isset( $subscription['metadata'] ) && is_array( $subscription['metadata'] )
		? $subscription['metadata']
		: [];
	if ( ! empty( $meta['lookupKey'] ) ) {
		return (string) $meta['lookupKey'];
	}

	$items = $subscription['items']['data'] ?? null;
	if ( is_array( $items ) && ! empty( $items[0]['price']['lookup_key'] ) ) {
		return (string) $items[0]['price']['lookup_key'];
	}
	return '';
}

/**
 * Qualify a Stripe event as a new JCP free trial start.
 *
 * @param array<string, mixed> $event Decoded Stripe event.
 * @return array{ok:bool,reason:string,subscription?:array<string,mixed>}
 */
function jcp_trial_qualify_event( array $event ): array {
	$type = isset( $event['type'] ) ? (string) $event['type'] : '';
	if ( $type !== 'customer.subscription.created' ) {
		return [ 'ok' => false, 'reason' => 'ignored_event_type' ];
	}

	$sub = $event['data']['object'] ?? null;
	if ( ! is_array( $sub ) ) {
		return [ 'ok' => false, 'reason' => 'missing_subscription' ];
	}

	$status = isset( $sub['status'] ) ? (string) $sub['status'] : '';
	if ( $status !== 'trialing' ) {
		return [ 'ok' => false, 'reason' => 'not_trialing' ];
	}

	$lookup = jcp_trial_subscription_lookup_key( $sub );
	if ( $lookup === 'additional_location_monthly' ) {
		return [ 'ok' => false, 'reason' => 'additional_location' ];
	}
	if ( $lookup !== JCP_TRIAL_PLAN_LOOKUP_KEY ) {
		return [ 'ok' => false, 'reason' => 'wrong_lookup_key' ];
	}

	$meta = isset( $sub['metadata'] ) && is_array( $sub['metadata'] ) ? $sub['metadata'] : [];
	$org  = isset( $meta['organizationId'] ) ? trim( (string) $meta['organizationId'] ) : '';
	if ( $org === '' ) {
		return [ 'ok' => false, 'reason' => 'missing_organization_id' ];
	}

	return [ 'ok' => true, 'reason' => 'qualified', 'subscription' => $sub ];
}

/**
 * Retrieve Stripe customer email (and id) via API.
 *
 * @param string $customer_id cus_…
 * @return array{id:string,email:string}|null
 */
function jcp_trial_fetch_stripe_customer( string $customer_id ): ?array {
	$secret = jcp_trial_secret( 'JCP_STRIPE_SECRET_KEY' );
	if ( $secret === '' || $customer_id === '' ) {
		return null;
	}

	$response = wp_remote_get(
		'https://api.stripe.com/v1/customers/' . rawurlencode( $customer_id ),
		[
			'timeout' => 15,
			'headers' => [
				'Authorization' => 'Bearer ' . $secret,
			],
		]
	);
	if ( is_wp_error( $response ) ) {
		return null;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
		return null;
	}

	$email = isset( $body['email'] ) ? jcp_trial_normalize_email( (string) $body['email'] ) : '';
	return [
		'id'    => (string) ( $body['id'] ?? $customer_id ),
		'email' => $email,
	];
}

/**
 * Find the most recent marketing lead for email created at/before Stripe trial time.
 *
 * Matching rule:
 * - Normalize email (trim + lowercase)
 * - Lead created_at <= subscription.created (site-local equivalent of Stripe UTC)
 * - Prefer newest such lead (ORDER BY created_at DESC, id DESC LIMIT 1)
 * - Never match a lead created after the subscription
 *
 * @param string $email_norm Normalized email.
 * @param int    $before_ts  Stripe subscription created (unix UTC).
 * @return object|null DB row.
 */
function jcp_trial_find_lead_before( string $email_norm, int $before_ts ) {
	global $wpdb;
	if ( $email_norm === '' || $before_ts <= 0 ) {
		return null;
	}
	if ( ! defined( 'JCP_DEMO_LEAD_QUEUE_TABLE' ) ) {
		return null;
	}

	jcp_demo_lead_queue_maybe_create_table();
	$table = $wpdb->prefix . JCP_DEMO_LEAD_QUEUE_TABLE;

	$gmt    = gmdate( 'Y-m-d H:i:s', $before_ts );
	$cutoff = get_date_from_gmt( $gmt, 'Y-m-d H:i:s' );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM `$table` WHERE LOWER(email) = %s AND created_at <= %s ORDER BY created_at DESC, id DESC LIMIT 1",
			$email_norm,
			$cutoff
		)
	);

	return $row ?: null;
}

/**
 * Decode attribution_json from a lead row (with GHL payload UTM fallback).
 *
 * @param object $lead Lead queue row.
 * @return array<string, string>
 */
function jcp_trial_lead_attribution( object $lead ): array {
	$attr = [];
	if ( ! empty( $lead->attribution_json ) ) {
		$decoded = json_decode( (string) $lead->attribution_json, true );
		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $k => $v ) {
				if ( is_string( $k ) && ( is_string( $v ) || is_numeric( $v ) ) ) {
					$attr[ $k ] = (string) $v;
				}
			}
		}
	}

	// Legacy leads: recover UTMs from stored GHL form body when attribution_json empty.
	if ( empty( $attr ) && ! empty( $lead->payload ) ) {
		parse_str( (string) $lead->payload, $parsed );
		$map = [
			'UTM Source'         => 'utm_source',
			'UTM Medium'         => 'utm_medium',
			'UTM Campaign'       => 'utm_campaign',
			'UTM Content'        => 'utm_content',
			'UTM Term'           => 'utm_term',
			'Facebook Click ID'  => 'fbclid',
			'Landing Page'       => 'landing_page',
			'LP Variant'         => 'lp_variant',
			'Funnel Surface'     => 'funnel_surface',
			'Referrer'           => 'referrer',
		];
		foreach ( $map as $ghl_key => $out_key ) {
			if ( ! empty( $parsed[ $ghl_key ] ) ) {
				$attr[ $out_key ] = (string) $parsed[ $ghl_key ];
			}
		}
	}

	return $attr;
}

/**
 * Upsert conversion row; return current row.
 *
 * @param array<string, mixed> $fields Fields.
 */
function jcp_trial_upsert_conversion( array $fields ) {
	global $wpdb;
	jcp_trial_conversions_maybe_create_table();
	$table = $wpdb->prefix . JCP_TRIAL_CONVERSIONS_TABLE;

	$conversion_id = (string) $fields['conversion_id'];
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$existing = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM `$table` WHERE conversion_id = %s LIMIT 1", $conversion_id )
	);

	$now = current_time( 'mysql' );
	if ( $existing ) {
		$update = [
			'updated_at' => $now,
		];
		foreach (
			[
				'stripe_event_id',
				'stripe_subscription_id',
				'stripe_customer_id',
				'organization_id',
				'email_norm',
				'attribution_status',
				'lead_id',
				'attribution_json',
				'trial_payload_json',
				'last_error',
			] as $key
		) {
			if ( array_key_exists( $key, $fields ) && $fields[ $key ] !== null ) {
				$update[ $key ] = $fields[ $key ];
			}
		}
		$wpdb->update( $table, $update, [ 'id' => (int) $existing->id ] );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$table` WHERE id = %d", (int) $existing->id ) );
	}

	$insert = array_merge(
		[
			'conversion_id'          => $conversion_id,
			'stripe_event_id'        => '',
			'stripe_subscription_id' => '',
			'stripe_customer_id'     => '',
			'organization_id'        => '',
			'email_norm'             => '',
			'attribution_status'     => 'unmatched',
			'lead_id'                => null,
			'attribution_json'       => null,
			'trial_payload_json'     => null,
			'posthog_status'         => 'pending',
			'meta_status'            => 'pending',
			'last_error'             => null,
			'created_at'             => $now,
			'updated_at'             => $now,
		],
		$fields
	);
	$wpdb->insert( $table, $insert );
	$id = (int) $wpdb->insert_id;
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$table` WHERE id = %d", $id ) );
}

/**
 * Update delivery status flags on a conversion row.
 *
 * @param int                  $id     Row id.
 * @param array<string, mixed> $fields Fields.
 */
function jcp_trial_update_conversion( int $id, array $fields ): void {
	global $wpdb;
	$table           = $wpdb->prefix . JCP_TRIAL_CONVERSIONS_TABLE;
	$fields['updated_at'] = current_time( 'mysql' );
	$wpdb->update( $table, $fields, [ 'id' => $id ] );
}

/**
 * Stable fallback distinct_id when marketing ph_distinct_id is missing.
 */
function jcp_trial_fallback_distinct_id( string $customer_id, string $subscription_id ): string {
	$seed = $customer_id !== '' ? $customer_id : $subscription_id;
	return 'jcp_stripe_' . substr( hash( 'sha256', $seed ), 0, 32 );
}

/**
 * Send PostHog trial_started (server-side).
 *
 * @param object               $row     Conversion row.
 * @param array<string, mixed> $trial   Trial payload.
 * @param array<string, string> $attr   Attribution.
 * @return array{ok:bool,error:string}
 */
function jcp_trial_send_posthog( object $row, array $trial, array $attr ): array {
	$api_key = jcp_trial_secret( 'JCP_POSTHOG_PROJECT_API_KEY' );
	if ( $api_key === '' ) {
		$api_key = JCP_POSTHOG_PROJECT_KEY_DEFAULT;
	}

	$ph_id = isset( $attr['ph_distinct_id'] ) ? trim( (string) $attr['ph_distinct_id'] ) : '';
	if ( $ph_id === '' ) {
		$ph_id = jcp_trial_fallback_distinct_id(
			(string) $row->stripe_customer_id,
			(string) $row->stripe_subscription_id
		);
	}

	$conversion_id = (string) $row->conversion_id;
	$properties    = [
		'distinct_id'            => $ph_id,
		'$insert_id'             => $conversion_id,
		'event_id'               => $conversion_id,
		'stripe_subscription_id' => (string) $row->stripe_subscription_id,
		'stripe_customer_id'     => (string) $row->stripe_customer_id,
		'organization_id'        => (string) $row->organization_id,
		'plan'                   => 'scale',
		'price_lookup_key'       => JCP_TRIAL_PLAN_LOOKUP_KEY,
		'trial_days'             => JCP_TRIAL_DAYS,
		'attribution_status'     => (string) $row->attribution_status,
		'$lib'                   => 'jcp-stripe-trial-webhook',
		'$lib_version'           => '1.0.0',
	];

	foreach (
		[
			'trial_start',
			'trial_end',
			'source',
			'lp_variant',
			'funnel_surface',
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_content',
			'utm_term',
			'fbclid',
			'qa_trace_id',
			'landing_page',
		] as $key
	) {
		if ( isset( $trial[ $key ] ) && $trial[ $key ] !== '' && $trial[ $key ] !== null ) {
			$properties[ $key ] = $trial[ $key ];
		} elseif ( isset( $attr[ $key ] ) && $attr[ $key ] !== '' ) {
			$properties[ $key ] = $attr[ $key ];
		}
	}

	if ( empty( $properties['source'] ) && ! empty( $properties['funnel_surface'] ) ) {
		$properties['source'] = $properties['funnel_surface'];
	}

	$timestamp = ! empty( $trial['event_time_iso'] )
		? (string) $trial['event_time_iso']
		: gmdate( 'c' );

	$body = [
		'api_key'    => $api_key,
		'event'      => 'trial_started',
		'properties' => $properties,
		'timestamp'  => $timestamp,
	];

	$response = wp_remote_post(
		'https://us.i.posthog.com/i/v0/e/?ip=0',
		[
			'timeout' => 15,
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( $body ),
		]
	);

	if ( is_wp_error( $response ) ) {
		return [ 'ok' => false, 'error' => $response->get_error_message() ];
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return [ 'ok' => false, 'error' => 'posthog_http_' . $code ];
	}
	return [ 'ok' => true, 'error' => '' ];
}

/**
 * SHA256 hex for Meta hashed PII (normalized lowercase email).
 */
function jcp_trial_meta_hash( string $value ): string {
	return hash( 'sha256', $value );
}

/**
 * Send Meta CAPI StartTrial.
 *
 * event_source_url uses the onboarding URL (where the trial is actually provisioned),
 * not the marketing landing page.
 *
 * @param object                $row   Conversion row.
 * @param array<string, mixed>  $trial Trial payload.
 * @param array<string, string> $attr  Attribution.
 * @return array{ok:bool,error:string,response?:string}
 */
function jcp_trial_send_meta( object $row, array $trial, array $attr ): array {
	$token = jcp_trial_secret( 'JCP_META_CAPI_ACCESS_TOKEN' );
	if ( $token === '' ) {
		return [ 'ok' => false, 'error' => 'missing_meta_token' ];
	}

	$pixel = jcp_trial_secret( 'JCP_META_PIXEL_ID' );
	if ( $pixel === '' ) {
		$pixel = JCP_META_PIXEL_ID_DEFAULT;
	}

	$email = jcp_trial_normalize_email( (string) ( $trial['email'] ?? $row->email_norm ) );
	$user_data = [];
	if ( $email !== '' && is_email( $email ) ) {
		$user_data['em'] = [ jcp_trial_meta_hash( $email ) ];
	}
	if ( ! empty( $attr['_fbp'] ) ) {
		$user_data['fbp'] = (string) $attr['_fbp'];
	}
	if ( ! empty( $attr['_fbc'] ) ) {
		$user_data['fbc'] = (string) $attr['_fbc'];
	}
	// Client IP / UA intentionally omitted: no consent-gated durable capture on marketing leads.

	$event_time = isset( $trial['event_time'] ) ? (int) $trial['event_time'] : time();
	$event      = [
		'event_name'       => 'StartTrial',
		'event_time'       => $event_time,
		'event_id'         => (string) $row->conversion_id,
		'action_source'    => 'website',
		'event_source_url' => 'https://app.jobcapturepro.com/onboarding',
		'user_data'        => $user_data,
		'custom_data'      => [
			'content_name' => 'scale',
			'content_type' => 'product',
			'status'       => 'trialing',
			'trial_days'   => JCP_TRIAL_DAYS,
		],
	];

	$payload = [
		'data'         => [ $event ],
		'access_token' => $token,
	];

	$test_code = jcp_trial_secret( 'JCP_META_CAPI_TEST_EVENT_CODE' );
	if ( $test_code !== '' ) {
		$payload['test_event_code'] = $test_code;
	}

	$url      = 'https://graph.facebook.com/v21.0/' . rawurlencode( $pixel ) . '/events';
	$response = wp_remote_post(
		$url,
		[
			'timeout' => 15,
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( $payload ),
		]
	);

	if ( is_wp_error( $response ) ) {
		return [ 'ok' => false, 'error' => $response->get_error_message() ];
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = (string) wp_remote_retrieve_body( $response );
	if ( $code < 200 || $code >= 300 ) {
		return [ 'ok' => false, 'error' => 'meta_http_' . $code, 'response' => mb_substr( $body, 0, 300 ) ];
	}
	return [ 'ok' => true, 'error' => '', 'response' => mb_substr( $body, 0, 300 ) ];
}

/**
 * Attempt PostHog + Meta delivery for a conversion row (idempotent per channel).
 *
 * @param object $row Conversion row.
 */
function jcp_trial_deliver_side_effects( object $row ): void {
	$trial = [];
	if ( ! empty( $row->trial_payload_json ) ) {
		$decoded = json_decode( (string) $row->trial_payload_json, true );
		if ( is_array( $decoded ) ) {
			$trial = $decoded;
		}
	}
	$attr = [];
	if ( ! empty( $row->attribution_json ) ) {
		$decoded = json_decode( (string) $row->attribution_json, true );
		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $k => $v ) {
				if ( is_string( $k ) && ( is_string( $v ) || is_numeric( $v ) ) ) {
					$attr[ $k ] = (string) $v;
				}
			}
		}
	}

	$errors = [];

	if ( (string) $row->posthog_status !== 'sent' ) {
		$ph = jcp_trial_send_posthog( $row, $trial, $attr );
		if ( $ph['ok'] ) {
			jcp_trial_update_conversion( (int) $row->id, [ 'posthog_status' => 'sent' ] );
			$row->posthog_status = 'sent';
		} else {
			jcp_trial_update_conversion( (int) $row->id, [ 'posthog_status' => 'failed', 'last_error' => $ph['error'] ] );
			$errors[] = 'posthog:' . $ph['error'];
		}
	}

	if ( (string) $row->meta_status !== 'sent' ) {
		$meta = jcp_trial_send_meta( $row, $trial, $attr );
		if ( $meta['ok'] ) {
			jcp_trial_update_conversion( (int) $row->id, [ 'meta_status' => 'sent' ] );
			$row->meta_status = 'sent';
		} else {
			jcp_trial_update_conversion( (int) $row->id, [ 'meta_status' => 'failed', 'last_error' => $meta['error'] ] );
			$errors[] = 'meta:' . $meta['error'];
		}
	}

	if ( $errors ) {
		error_log( 'jcp_trial_deliver: ' . (string) $row->conversion_id . ' ' . implode( ';', $errors ) );
	}
}

/**
 * Process a qualified subscription into durable conversion + side effects.
 *
 * @param array<string, mixed> $event Stripe event.
 * @param array<string, mixed> $sub   Subscription object.
 * @return array{status:string,conversion_id:string,attribution_status:string}
 */
function jcp_trial_process_qualified( array $event, array $sub ): array {
	$subscription_id = (string) ( $sub['id'] ?? '' );
	$conversion_id   = jcp_trial_conversion_id( $subscription_id );
	$customer_ref    = $sub['customer'] ?? '';
	$customer_id     = is_string( $customer_ref ) ? $customer_ref : (string) ( $customer_ref['id'] ?? '' );

	$meta = isset( $sub['metadata'] ) && is_array( $sub['metadata'] ) ? $sub['metadata'] : [];
	$org  = isset( $meta['organizationId'] ) ? (string) $meta['organizationId'] : '';

	$trial_start = isset( $sub['trial_start'] ) ? (int) $sub['trial_start'] : 0;
	$trial_end   = isset( $sub['trial_end'] ) ? (int) $sub['trial_end'] : 0;
	$created     = isset( $sub['created'] ) ? (int) $sub['created'] : time();
	$event_time  = $trial_start > 0 ? $trial_start : $created;

	$email = '';
	if ( is_array( $customer_ref ) && ! empty( $customer_ref['email'] ) ) {
		$email = jcp_trial_normalize_email( (string) $customer_ref['email'] );
	}
	if ( $email === '' && $customer_id !== '' ) {
		$customer = jcp_trial_fetch_stripe_customer( $customer_id );
		if ( $customer ) {
			$customer_id = $customer['id'] !== '' ? $customer['id'] : $customer_id;
			$email       = $customer['email'];
		}
	}

	$attr_status = 'unmatched';
	$lead_id     = null;
	$attr        = [];
	if ( $email !== '' ) {
		$lead = jcp_trial_find_lead_before( $email, $created );
		if ( $lead ) {
			$attr_status = 'matched';
			$lead_id     = (int) $lead->id;
			$attr        = jcp_trial_lead_attribution( $lead );
		}
	}

	$trial_payload = [
		'email'                  => $email,
		'plan'                   => 'scale',
		'price_lookup_key'       => JCP_TRIAL_PLAN_LOOKUP_KEY,
		'trial_days'             => JCP_TRIAL_DAYS,
		'trial_start'            => $trial_start > 0 ? gmdate( 'c', $trial_start ) : '',
		'trial_end'              => $trial_end > 0 ? gmdate( 'c', $trial_end ) : '',
		'event_time'             => $event_time,
		'event_time_iso'         => gmdate( 'c', $event_time ),
		'stripe_subscription_id' => $subscription_id,
		'stripe_customer_id'     => $customer_id,
		'organization_id'        => $org,
	];

	$row = jcp_trial_upsert_conversion(
		[
			'conversion_id'          => $conversion_id,
			'stripe_event_id'        => (string) ( $event['id'] ?? '' ),
			'stripe_subscription_id' => $subscription_id,
			'stripe_customer_id'     => $customer_id,
			'organization_id'        => $org,
			'email_norm'             => $email,
			'attribution_status'     => $attr_status,
			'lead_id'                => $lead_id,
			'attribution_json'       => wp_json_encode( $attr, JSON_UNESCAPED_SLASHES ),
			'trial_payload_json'     => wp_json_encode( $trial_payload, JSON_UNESCAPED_SLASHES ),
		]
	);

	if ( $row ) {
		// Idempotent: skip channels already marked sent.
		jcp_trial_deliver_side_effects( $row );
	}

	return [
		'status'             => 'processed',
		'conversion_id'      => $conversion_id,
		'attribution_status' => $attr_status,
	];
}

/**
 * Stripe webhook HTTP handler.
 *
 * Always returns 200 after durable record for qualifying events so Stripe
 * retries do not depend on PostHog/Meta availability. Side-effect failures
 * are retried by cron.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function jcp_trial_stripe_webhook_handler( WP_REST_Request $request ): WP_REST_Response {
	$secret = jcp_trial_secret( 'JCP_STRIPE_WEBHOOK_SECRET' );
	if ( $secret === '' ) {
		return new WP_REST_Response( [ 'error' => 'webhook_not_configured' ], 503 );
	}

	$payload    = $request->get_body();
	$sig_header = (string) $request->get_header( 'stripe-signature' );
	if ( ! jcp_trial_verify_stripe_signature( $payload, $sig_header, $secret ) ) {
		return new WP_REST_Response( [ 'error' => 'invalid_signature' ], 400 );
	}

	$event = json_decode( $payload, true );
	if ( ! is_array( $event ) ) {
		return new WP_REST_Response( [ 'error' => 'invalid_json' ], 400 );
	}

	$qualification = jcp_trial_qualify_event( $event );
	if ( ! $qualification['ok'] ) {
		return new WP_REST_Response(
			[
				'received' => true,
				'handled'  => false,
				'reason'   => $qualification['reason'],
			],
			200
		);
	}

	try {
		$result = jcp_trial_process_qualified( $event, $qualification['subscription'] );
		return new WP_REST_Response(
			[
				'received'           => true,
				'handled'            => true,
				'conversion_id'      => $result['conversion_id'],
				'attribution_status' => $result['attribution_status'],
			],
			200
		);
	} catch ( Throwable $e ) {
		error_log( 'jcp_trial_webhook: ' . $e->getMessage() );
		// Ask Stripe to retry only when durable processing failed.
		return new WP_REST_Response( [ 'error' => 'processing_failed' ], 500 );
	}
}

/**
 * Cron: retry failed / pending PostHog + Meta deliveries.
 */
function jcp_trial_retry_deliveries(): void {
	global $wpdb;
	jcp_trial_conversions_maybe_create_table();
	$table = $wpdb->prefix . JCP_TRIAL_CONVERSIONS_TABLE;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT * FROM `$table`
		WHERE posthog_status IN ('pending','failed') OR meta_status IN ('pending','failed')
		ORDER BY id ASC
		LIMIT 25"
	);
	if ( ! $rows ) {
		return;
	}
	foreach ( $rows as $row ) {
		jcp_trial_deliver_side_effects( $row );
	}
}

/**
 * Schedule delivery retry cron.
 */
function jcp_trial_schedule_cron(): void {
	if ( ! wp_next_scheduled( 'jcp_trial_conversion_retry' ) ) {
		wp_schedule_event( time() + 120, 'five_minutes', 'jcp_trial_conversion_retry' );
	}
}
add_action( 'jcp_trial_conversion_retry', 'jcp_trial_retry_deliveries' );
add_action( 'init', 'jcp_trial_schedule_cron' );
add_action( 'after_switch_theme', 'jcp_trial_schedule_cron' );
