<?php
/**
 * Pure helpers for Meta StartTrial deferral / claim / cookie selection (P1).
 *
 * Loaded by stripe-trial-tracking.php and CLI tests (no WordPress required).
 *
 * @package JCP_Core
 */

if ( ! defined( 'JCP_TRIAL_META_SENDING_STALE_SEC' ) ) {
	/** Seconds after which a stuck meta_status=sending may be reclaimed. */
	define( 'JCP_TRIAL_META_SENDING_STALE_SEC', 120 );
}

/**
 * Whether Meta delivery is terminal (must never send CAPI again).
 *
 * @param string $meta_status Row meta_status.
 */
function jcp_trial_meta_status_finalized( string $meta_status ): bool {
	return $meta_status === 'sent' || $meta_status === 'skipped_qa';
}

/**
 * Whether this worker may attempt an atomic claim for Meta delivery.
 *
 * @param string   $meta_status Current status.
 * @param int|null $updated_ts  Row updated_at as unix UTC (null = unknown → only pending/failed).
 * @param int      $now         Current unix UTC.
 * @param int      $stale_sec   Stale threshold for reclaiming "sending".
 */
function jcp_trial_meta_claim_allowed(
	string $meta_status,
	?int $updated_ts,
	int $now,
	int $stale_sec = JCP_TRIAL_META_SENDING_STALE_SEC
): bool {
	if ( jcp_trial_meta_status_finalized( $meta_status ) ) {
		return false;
	}
	if ( $meta_status === 'pending' || $meta_status === 'failed' ) {
		return true;
	}
	if ( $meta_status === 'sending' ) {
		if ( $updated_ts === null ) {
			return false;
		}
		return ( $now - $updated_ts ) >= $stale_sec;
	}
	return false;
}

/**
 * Whether Meta should be delivered for this enrichment state.
 *
 * Matched at ingest or after CDP wake → send. Unmatched → wait until window expires.
 * Finalized statuses never send (including late enrichment after timeout).
 *
 * @param bool   $attribution_matched Attribution status is matched.
 * @param bool   $enrichment_expired  Enrichment window closed.
 * @param string $meta_status         Current meta_status.
 */
function jcp_trial_meta_should_deliver(
	bool $attribution_matched,
	bool $enrichment_expired,
	string $meta_status
): bool {
	if ( jcp_trial_meta_status_finalized( $meta_status ) ) {
		return false;
	}
	return $attribution_matched || $enrichment_expired;
}

/**
 * Validate Meta _fbp cookie value (fb.1.<creation_time>.<random>).
 */
function jcp_trial_meta_fbp_is_valid( string $fbp ): bool {
	$fbp = trim( $fbp );
	return (bool) preg_match( '/^fb\.1\.\d+\.\d+$/', $fbp );
}

/**
 * Validate Meta _fbc cookie value (fb.<subdomain_index>.<click_time>.<fbclid>).
 */
function jcp_trial_meta_fbc_is_valid( string $fbc ): bool {
	$fbc = trim( $fbc );
	return (bool) preg_match( '/^fb\.\d+\.\d+\.[A-Za-z0-9_-]+$/', $fbc );
}

/**
 * Prefer existing valid fbp/fbc. Do not invent click time from Stripe trial timestamps.
 *
 * Optional synthesis only when both fbclid and an explicit Facebook click unix
 * timestamp are present (attr keys: facebook_click_time | fb_click_time | fbc_click_time).
 *
 * @param array<string, mixed> $attr Attribution.
 * @return array{fbp?:string,fbc?:string}
 */
function jcp_trial_meta_pick_click_ids( array $attr ): array {
	$out = [];

	$fbp = isset( $attr['_fbp'] ) ? trim( (string) $attr['_fbp'] ) : '';
	if ( $fbp !== '' && jcp_trial_meta_fbp_is_valid( $fbp ) ) {
		$out['fbp'] = $fbp;
	}

	$fbc = isset( $attr['_fbc'] ) ? trim( (string) $attr['_fbc'] ) : '';
	if ( $fbc !== '' && jcp_trial_meta_fbc_is_valid( $fbc ) ) {
		$out['fbc'] = $fbc;
		return $out;
	}

	$fbclid = isset( $attr['fbclid'] ) ? trim( (string) $attr['fbclid'] ) : '';
	$click  = 0;
	foreach ( [ 'facebook_click_time', 'fb_click_time', 'fbc_click_time' ] as $key ) {
		if ( ! empty( $attr[ $key ] ) && is_numeric( $attr[ $key ] ) ) {
			$click = (int) $attr[ $key ];
			break;
		}
	}
	// Reject clearly invalid / epoch noise; require plausible unix seconds.
	if ( $fbclid !== '' && $click >= 1000000000 && $click <= 9999999999 ) {
		$derived = 'fb.1.' . $click . '.' . $fbclid;
		if ( jcp_trial_meta_fbc_is_valid( $derived ) ) {
			$out['fbc'] = $derived;
		}
	}

	return $out;
}
