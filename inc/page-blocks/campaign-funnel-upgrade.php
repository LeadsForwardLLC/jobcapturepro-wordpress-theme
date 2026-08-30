<?php
/**
 * Campaign funnel v16 — remove redundant blocks and enforce CRO order.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drop demo_preview / how_it_works and reorder campaign blocks for paid traffic.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_campaign_funnel_order( array $content, int $post_id ): array {
	if ( ! function_exists( 'jcp_page_is_campaign_landing' ) || ! jcp_page_is_campaign_landing( $content ) ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) || $blocks === [] ) {
		return $content;
	}

	$desired = [
		'hero',
		'core_mechanic',
		'benefits',
		'authority',
		'problem',
		'story_moments',
		'testimonials',
		'faq',
		'final_cta',
	];
	$remove = [
		'demo_preview' => true,
		'how_it_works' => true,
	];

	$by_type = [];
	$changed = false;
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		if ( $type === '' || isset( $remove[ $type ] ) ) {
			$changed = true;
			continue;
		}
		// Keep first instance of each known type.
		if ( ! isset( $by_type[ $type ] ) ) {
			$by_type[ $type ] = $block;
		}
	}

	$out = [];
	foreach ( $desired as $type ) {
		if ( isset( $by_type[ $type ] ) ) {
			$out[] = $by_type[ $type ];
			unset( $by_type[ $type ] );
		}
	}
	// Preserve any unexpected leftover types before final_cta.
	$leftovers = [];
	foreach ( $by_type as $type => $block ) {
		$leftovers[] = $block;
		$changed     = true;
	}
	if ( $leftovers !== [] ) {
		$with_leftovers = [];
		$inserted_left  = false;
		foreach ( $out as $block ) {
			if ( ! $inserted_left && ( $block['type'] ?? '' ) === 'final_cta' ) {
				foreach ( $leftovers as $left ) {
					$with_leftovers[] = $left;
				}
				$inserted_left = true;
			}
			$with_leftovers[] = $block;
		}
		if ( ! $inserted_left ) {
			foreach ( $leftovers as $left ) {
				$with_leftovers[] = $left;
			}
		}
		$out = $with_leftovers;
	}

	// Detect order drift.
	$old_types = [];
	foreach ( $blocks as $block ) {
		if ( is_array( $block ) && ! empty( $block['type'] ) ) {
			$old_types[] = (string) $block['type'];
		}
	}
	$new_types = [];
	foreach ( $out as $block ) {
		$new_types[] = (string) ( $block['type'] ?? '' );
	}
	if ( $old_types !== $new_types ) {
		$changed = true;
	}

	if ( ! $changed ) {
		return $content;
	}

	$content['blocks'] = $out;
	return $content;
}
