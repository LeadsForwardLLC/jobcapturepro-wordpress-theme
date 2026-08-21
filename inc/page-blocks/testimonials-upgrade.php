<?php
/**
 * Ensure home page documents include testimonials after proof_flow.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert testimonials block on saved home documents when missing.
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

	foreach ( $blocks as $block ) {
		if ( ( $block['type'] ?? '' ) === 'testimonials' ) {
			return $content;
		}
	}

	$new = [
		'id'    => 'testimonials-' . wp_generate_password( 8, false ),
		'type'  => 'testimonials',
		'props' => jcp_page_default_block_props( 'testimonials' ),
	];

	$out      = [];
	$inserted = false;
	foreach ( $blocks as $block ) {
		$out[] = $block;
		if ( ! $inserted && ( $block['type'] ?? '' ) === 'proof_flow' ) {
			$out[]    = $new;
			$inserted = true;
		}
	}

	if ( ! $inserted ) {
		$out[] = $new;
	}

	$content['blocks'] = $out;
	return $content;
}
