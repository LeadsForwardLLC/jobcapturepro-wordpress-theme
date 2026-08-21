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
 *
 * @return array<int, array<string, string>>
 */
function jcp_page_home_balanced_meta_stats(): array {
	return [
		[
			'icon'      => 'camera',
			'label'     => '1 photo',
			'detail'    => 'Becomes proof on every channel',
			'css_class' => 'meta-stat-photo',
		],
		[
			'icon'      => 'map',
			'label'     => '4 channels',
			'detail'    => 'Website, Google, social & directory',
			'css_class' => 'meta-stat-channels',
		],
		[
			'icon'      => 'clock',
			'label'     => '0 busywork',
			'detail'    => 'Your crew just takes the photo',
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
	$outdated_details = [
		'shared on website, google, social + directory',
		'website, google, social + directory',
		'no extra work from you',
		'zero admin work',
		'proof everywhere',
		'web, maps, social',
		'site, maps, social',
		'zero work for you',
		'no work from you',
	];

	foreach ( $stats as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$detail = strtolower( trim( (string) ( $row['detail'] ?? '' ) ) );
		if ( $detail === '' || in_array( $detail, $outdated_details, true ) ) {
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
