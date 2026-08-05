<?php
/**
 * Split embedded how_it_works.demo_preview into a standalone demo_preview block.
 *
 * @package JCP_Core
 */

/**
 * Move legacy embedded demo strips into their own block.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_embedded_demo_blocks( array $content, int $post_id ): array {
	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) || empty( $blocks ) ) {
		return $content;
	}

	$has_demo_block = false;
	foreach ( $blocks as $block ) {
		if ( is_array( $block ) && ( $block['type'] ?? '' ) === 'demo_preview' ) {
			$has_demo_block = true;
			break;
		}
	}
	if ( $has_demo_block ) {
		return $content;
	}

	$flat = jcp_page_blocks_to_legacy( $content );
	$demo = $flat['how_it_works']['demo_preview'] ?? null;
	if ( ! is_array( $demo ) || empty( $demo['headline'] ) ) {
		return $content;
	}

	$insert_at = null;
	for ( $i = 0; $i < count( $blocks ); $i++ ) {
		if ( ( $blocks[ $i ]['type'] ?? '' ) === 'how_it_works' ) {
			$insert_at = $i + 1;
			break;
		}
	}
	if ( $insert_at === null ) {
		return $content;
	}

	$page_kind = (string) ( $content['page_kind'] ?? 'marketing' );
	$new_block = [
		'id'     => 'b-demo-preview',
		'type'   => 'demo_preview',
		'layout' => jcp_block_default_layout( 'demo_preview', $page_kind ),
		'props'  => array_merge(
			jcp_page_default_block_props( 'demo_preview' ),
			$demo
		),
	];

	array_splice( $blocks, $insert_at, 0, [ $new_block ] );

	for ( $i = 0; $i < count( $blocks ); $i++ ) {
		if ( ( $blocks[ $i ]['type'] ?? '' ) !== 'how_it_works' ) {
			continue;
		}
		$props = is_array( $blocks[ $i ]['props'] ?? null ) ? $blocks[ $i ]['props'] : [];
		unset( $props['demo_preview'] );
		$props['show_demo_preview'] = false;
		$blocks[ $i ]['props']     = $props;
		break;
	}

	$content['blocks'] = $blocks;

	return $content;
}
