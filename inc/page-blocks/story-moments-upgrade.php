<?php
/**
 * Insert story_moments on campaign landings without overwriting editor edits.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert story_moments after benefits (before demo_preview) when missing.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_campaign_story_moments( array $content, int $post_id ): array {
	if ( ! function_exists( 'jcp_page_is_campaign_landing' ) || ! jcp_page_is_campaign_landing( $content ) ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) || $blocks === [] ) {
		return $content;
	}

	foreach ( $blocks as $block ) {
		if ( is_array( $block ) && ( $block['type'] ?? '' ) === 'story_moments' ) {
			return $content;
		}
	}

	$props = function_exists( 'jcp_niche_story_moments_defaults' )
		? jcp_niche_story_moments_defaults()
		: [];

	$new_block = [
		'id'     => function_exists( 'jcp_page_new_block_id' )
			? jcp_page_new_block_id( 'story_moments' )
			: ( 'b-story-moments-' . wp_generate_password( 6, false ) ),
		'type'   => 'story_moments',
		'props'  => $props,
		'layout' => function_exists( 'jcp_block_default_layout' )
			? jcp_block_default_layout( 'story_moments', 'marketing' )
			: [],
	];

	$out      = [];
	$inserted = false;
	foreach ( $blocks as $block ) {
		$out[] = $block;
		if ( $inserted || ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		// Prefer after benefits; fall back to before demo_preview on next iteration.
		if ( $type === 'benefits' ) {
			$out[]    = $new_block;
			$inserted = true;
		}
	}

	if ( ! $inserted ) {
		$out      = [];
		$inserted = false;
		foreach ( $blocks as $block ) {
			if ( ! $inserted && is_array( $block ) && ( $block['type'] ?? '' ) === 'demo_preview' ) {
				$out[]    = $new_block;
				$inserted = true;
			}
			$out[] = $block;
		}
	}

	if ( ! $inserted ) {
		$out[] = $new_block;
	}

	$content['blocks'] = $out;
	return $content;
}
