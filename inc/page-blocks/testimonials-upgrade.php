<?php
/**
 * Ensure home page documents include testimonials after how_it_works,
 * and keep hero meta_stats copy balanced.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical balanced homepage hero meta_stats.
 * Aligned with dummy-home.json + contractor-demo (1 photo → 5 places → more jobs).
 *
 * @return array<int, array<string, string>>
 */
function jcp_page_home_balanced_meta_stats(): array {
	return [
		[
			'icon'      => 'camera',
			'label'     => '1 photo',
			'detail'    => 'Starts the workflow',
			'css_class' => 'meta-stat-photo',
		],
		[
			'icon'      => 'map',
			'label'     => '5 places',
			'detail'    => 'Web · Google · Social · Directory · Reviews',
			'css_class' => 'meta-stat-channels',
		],
		[
			'icon'      => 'clock',
			'label'     => 'More jobs',
			'detail'    => 'From finished work',
			'css_class' => 'meta-stat-busywork',
		],
	];
}

/**
 * Whether a hero meta_stats row still uses outdated copy.
 *
 * @param array<int, mixed> $stats Stats rows.
 */
function jcp_page_home_meta_stats_need_balance( array $stats ): bool {
	$canonical = jcp_page_home_balanced_meta_stats();
	if ( count( $stats ) !== count( $canonical ) ) {
		return true;
	}

	$blob = '';
	foreach ( $stats as $row ) {
		if ( ! is_array( $row ) ) {
			return true;
		}
		$blob .= ' ' . strtolower( trim( (string) ( $row['label'] ?? '' ) ) );
		$blob .= ' ' . strtolower( trim( (string) ( $row['detail'] ?? '' ) ) );
	}

	// Legacy claim-heavy / 4-channel framing — force upgrade to claim-safe 5-place strip.
	if (
		str_contains( $blob, '4 channels' )
		|| str_contains( $blob, '0 busywork' )
		|| str_contains( $blob, 'becomes proof on every channel' )
		|| str_contains( $blob, 'your crew just takes the photo' )
		|| str_contains( $blob, 'website, google, social & directory' )
	) {
		return true;
	}

	foreach ( $canonical as $i => $want ) {
		$row = is_array( $stats[ $i ] ?? null ) ? $stats[ $i ] : [];
		$label  = strtolower( trim( (string) ( $row['label'] ?? '' ) ) );
		$detail = strtolower( trim( (string) ( $row['detail'] ?? '' ) ) );
		$want_label  = strtolower( (string) $want['label'] );
		$want_detail = strtolower( (string) $want['detail'] );

		// Standard homepage labels: always keep canonical details in sync.
		if ( $label === $want_label && $detail !== $want_detail ) {
			return true;
		}
		if ( $detail === '' ) {
			return true;
		}
	}

	return false;
}

/**
 * Insert/move testimonials after how_it_works and balance hero meta_stats on home.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_home_testimonials( array $content, int $post_id ): array {
	if ( jcp_page_resolve_kind( $content, $post_id ) !== 'home' ) {
		return $content;
	}

	// Sales-deck preview (home_v2) owns its own funnel order + hero stats.
	if ( ( $content['preset'] ?? '' ) === 'home_v2' || ! empty( $content['settings']['home_preview'] ) ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) ) {
		return $content;
	}

	$testimonials = null;
	$rest         = [];
	foreach ( $blocks as $block ) {
		if ( ( $block['type'] ?? '' ) === 'testimonials' ) {
			$testimonials = $block;
			continue;
		}
		$rest[] = $block;
	}

	if ( $testimonials === null ) {
		$testimonials = [
			'id'    => 'testimonials-' . wp_generate_password( 8, false ),
			'type'  => 'testimonials',
			'props' => jcp_page_default_block_props( 'testimonials' ),
		];
	}

	$out      = [];
	$inserted = false;
	foreach ( $rest as $block ) {
		$out[] = $block;
		if ( ! $inserted && ( $block['type'] ?? '' ) === 'how_it_works' ) {
			$out[]    = $testimonials;
			$inserted = true;
		}
	}

	if ( ! $inserted ) {
		$out[] = $testimonials;
	}

	foreach ( $out as $i => $block ) {
		if ( ( $block['type'] ?? '' ) !== 'hero' ) {
			continue;
		}
		$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];
		$stats = is_array( $props['meta_stats'] ?? null ) ? $props['meta_stats'] : [];
		if ( empty( $stats ) || jcp_page_home_meta_stats_need_balance( $stats ) ) {
			$props['meta_stats'] = jcp_page_home_balanced_meta_stats();
			$out[ $i ]['props']  = $props;
		}
		break;
	}

	$content['blocks'] = $out;
	return $content;
}
