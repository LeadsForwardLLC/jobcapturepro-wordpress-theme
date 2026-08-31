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
 * Aligned with contractor-demo / campaign core_mechanic (1 photo → 5 channels → More jobs).
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
			'label'     => '5 channels',
			'detail'    => 'Published online',
			'css_class' => 'meta-stat-channels',
		],
		[
			'icon'      => 'badge-check',
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

	// Legacy claim-heavy / outdated framing — force upgrade to claim-safe LP meta strip.
	if (
		str_contains( $blob, '4 channels' )
		|| str_contains( $blob, '5 places' )
		|| str_contains( $blob, '0 busywork' )
		|| str_contains( $blob, 'becomes proof on every channel' )
		|| str_contains( $blob, 'your crew just takes the photo' )
		|| str_contains( $blob, 'website, google, social & directory' )
		|| str_contains( $blob, 'web · google · social · directory · reviews' )
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

/**
 * Canonical four reviews for homepage / campaign / sales surfaces.
 *
 * @return list<array<string, mixed>>
 */
function jcp_page_canonical_testimonial_reviews(): array {
	if ( function_exists( 'jcp_sales_tool_default_reviews' ) ) {
		$raw = jcp_sales_tool_default_reviews();
		$out = [];
		foreach ( $raw as $review ) {
			if ( ! is_array( $review ) ) {
				continue;
			}
			$out[] = [
				'id'         => (string) ( $review['id'] ?? sanitize_title( (string) ( $review['name'] ?? '' ) ) ),
				'name'       => (string) ( $review['name'] ?? '' ),
				'role'       => (string) ( $review['role'] ?? '' ),
				'quote'      => (string) ( $review['quote'] ?? '' ),
				'rating'     => isset( $review['rating'] ) ? (int) $review['rating'] : 5,
				'avatar_url' => (string) ( $review['avatar_url'] ?? $review['avatar'] ?? '' ),
				'avatar_alt' => (string) ( $review['avatar_alt'] ?? $review['avatarAlt'] ?? $review['name'] ?? '' ),
			];
		}
		return $out;
	}
	return [];
}

/**
 * Ensure testimonials blocks keep all four canonical reviews in a grid.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_ensure_canonical_testimonial_reviews( array $content, int $post_id = 0 ): array {
	unset( $post_id );
	$blocks = $content['blocks'] ?? null;
	if ( ! is_array( $blocks ) ) {
		return $content;
	}
	$canonical = jcp_page_canonical_testimonial_reviews();
	if ( $canonical === [] ) {
		return $content;
	}

	$changed = false;
	foreach ( $blocks as $i => $block ) {
		if ( ! is_array( $block ) || ( $block['type'] ?? '' ) !== 'testimonials' ) {
			continue;
		}
		$props   = is_array( $block['props'] ?? null ) ? $block['props'] : [];
		$reviews = is_array( $props['reviews'] ?? null ) ? $props['reviews'] : [];
		$by_id   = [];
		foreach ( $reviews as $review ) {
			if ( ! is_array( $review ) ) {
				continue;
			}
			$id = trim( (string) ( $review['id'] ?? '' ) );
			if ( $id === '' ) {
				$id = sanitize_title( (string) ( $review['name'] ?? '' ) );
			}
			if ( $id !== '' ) {
				$by_id[ $id ] = $review;
			}
		}

		$merged = [];
		foreach ( $canonical as $want ) {
			$id = (string) ( $want['id'] ?? '' );
			if ( $id !== '' && isset( $by_id[ $id ] ) && is_array( $by_id[ $id ] ) ) {
				$existing       = $by_id[ $id ];
				$existing_name  = trim( (string) ( $existing['name'] ?? '' ) );
				$existing_quote = trim( (string) ( $existing['quote'] ?? '' ) );
				if ( $existing_name === '' || $existing_quote === '' ) {
					$merged[] = $want;
					$changed  = true;
					continue;
				}
				// Prefer saved quote/name but restore missing avatar fields from canonical.
				if ( empty( $existing['avatar_url'] ) && empty( $existing['avatar'] ) && ! empty( $want['avatar_url'] ) ) {
					$existing['avatar_url'] = $want['avatar_url'];
					$changed               = true;
				}
				if ( empty( $existing['avatar_alt'] ) && empty( $existing['avatarAlt'] ) && ! empty( $want['avatar_alt'] ) ) {
					$existing['avatar_alt'] = $want['avatar_alt'];
					$changed               = true;
				}
				$merged[] = $existing;
			} else {
				$merged[] = $want;
				$changed  = true;
			}
		}

		$before_layout   = (string) ( $props['layout'] ?? '' );
		$before_featured = ! empty( $props['show_featured'] );
		$before_count    = count( $reviews );

		$props['reviews']       = $merged;
		$props['layout']        = 'grid';
		$props['show_featured'] = false;
		$props['per_view']      = max( 4, (int) ( $props['per_view'] ?? 4 ) );
		if ( $before_layout !== 'grid' || $before_featured || $before_count !== count( $merged ) ) {
			$changed = true;
		}
		$blocks[ $i ]['props'] = $props;
	}

	if ( $changed ) {
		$content['blocks'] = $blocks;
	}
	return $content;
}
