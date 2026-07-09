<?php
/**
 * Block layout options (alignment, width, hero variants).
 *
 * @package JCP_Core
 */

/**
 * Valid hero layout variants.
 *
 * @return array<int, string>
 */
function jcp_block_hero_variants(): array {
	return [ 'split', 'centered', 'stacked', 'condensed', 'home' ];
}

/**
 * Block types that support a column-count layout control.
 *
 * @return array<int, string>
 */
function jcp_block_column_types(): array {
	return [
		'how_it_works',
		'check_ins',
		'problem',
		'benefits',
		'who_its_for',
		'proof_flow',
	];
}

/**
 * Default layout per block type.
 *
 * @param string $type      Block type.
 * @param string $page_kind Page kind.
 * @return array<string, mixed>
 */
function jcp_block_default_layout( string $type, string $page_kind = 'industry' ): array {
	if ( $type === 'hero' ) {
		return [
			'hero_variant'    => $page_kind === 'referral' ? 'centered' : ( $page_kind === 'home' ? 'home' : 'condensed' ),
			'align'           => $page_kind === 'referral' ? 'center' : 'left',
			'section_surface' => jcp_section_surface_defaults(),
		];
	}

	$layout = [
		'align'           => 'center',
		'width'           => 'contained',
		'columns'         => 0,
		'section_surface' => jcp_section_surface_defaults(),
	];

	if ( $type === 'breadcrumb' ) {
		$layout['align'] = 'left';
	}

	if ( $type === 'conversion' ) {
		$layout['align'] = 'left';
	}

	return $layout;
}

/**
 * Resolve hero variant from layout (with legacy migration).
 *
 * @param array<string, mixed> $layout Resolved/partial layout.
 */
function jcp_block_resolve_hero_variant( array $layout ): string {
	$variant = (string) ( $layout['hero_variant'] ?? '' );
	if ( in_array( $variant, jcp_block_hero_variants(), true ) ) {
		return $variant;
	}
	if ( array_key_exists( 'hero_visual', $layout ) && empty( $layout['hero_visual'] ) ) {
		return 'centered';
	}
	if ( ( $layout['align'] ?? '' ) === 'center' && empty( $layout['hero_visual'] ) ) {
		return 'centered';
	}
	return 'split';
}

/**
 * Resolve layout for a block (defaults + stored values + hero props).
 *
 * @param array<string, mixed> $block     Block array.
 * @param string               $page_kind Page kind.
 * @return array<string, mixed>
 */
function jcp_block_resolve_layout( array $block, string $page_kind = 'industry' ): array {
	$type   = (string) ( $block['type'] ?? '' );
	$stored = is_array( $block['layout'] ?? null ) ? $block['layout'] : [];
	$layout = array_merge( jcp_block_default_layout( $type, $page_kind ), $stored );

	if ( $type === 'hero' ) {
		$layout['hero_variant'] = jcp_block_resolve_hero_variant( $layout );
		$align                  = (string) ( $layout['align'] ?? 'center' );
		$layout['align']        = in_array( $align, [ 'left', 'center', 'right' ], true ) ? $align : 'center';
		$layout['section_surface'] = jcp_section_surface_resolve( $layout );
		return $layout;
	}

	$align = (string) ( $layout['align'] ?? 'center' );
	$width = (string) ( $layout['width'] ?? 'contained' );
	$layout['align'] = in_array( $align, [ 'left', 'center', 'right' ], true ) ? $align : 'center';
	$layout['width'] = in_array( $width, [ 'contained', 'wide', 'full' ], true ) ? $width : 'contained';

	$columns = (int) ( $layout['columns'] ?? 0 );
	$layout['columns'] = ( $columns >= 1 && $columns <= 4 ) ? $columns : 0;
	$layout['section_surface'] = jcp_section_surface_resolve( $layout );

	return $layout;
}

/**
 * CSS classes for a block root from layout settings.
 *
 * @param array<string, mixed> $layout Resolved layout.
 * @param string               $type   Block type.
 */
function jcp_block_layout_classes( array $layout, string $type ): string {
	if ( $type === 'hero' ) {
		$variant = jcp_block_resolve_hero_variant( $layout );
		$align   = (string) ( $layout['align'] ?? 'center' );
		$align   = in_array( $align, [ 'left', 'center', 'right' ], true ) ? $align : 'center';
		return 'jcp-block-root jcp-hero-variant-' . $variant . ' jcp-layout-align-' . $align;
	}

	$classes = [
		'jcp-block-root',
		'jcp-layout-align-' . (string) ( $layout['align'] ?? 'center' ),
		'jcp-layout-width-' . (string) ( $layout['width'] ?? 'contained' ),
	];

	$columns = (int) ( $layout['columns'] ?? 0 );
	if ( $columns >= 1 && $columns <= 4 ) {
		$classes[] = 'jcp-block-cols-' . $columns;
	}

	return implode( ' ', $classes );
}

/**
 * Layout controls exposed to the front-end editor per block type.
 *
 * @param string $type Block type.
 * @return array<string, bool>
 */
function jcp_block_layout_options( string $type ): array {
	if ( in_array( $type, [ 'core_mechanic', 'breadcrumb' ], true ) ) {
		return [];
	}
	if ( $type === 'hero' ) {
		return [
			'hero_variant'   => true,
			'media_position' => true,
		];
	}
	if ( in_array( $type, [ 'media_text', 'demo_preview', 'conversion' ], true ) ) {
		return [
			'media_position' => true,
			'align'          => $type === 'media_text',
			'width'          => $type === 'media_text',
		];
	}
	$options = [
		'align' => true,
		'width' => true,
	];
	if ( in_array( $type, jcp_block_column_types(), true ) ) {
		$options['columns'] = true;
	}
	return $options;
}

/**
 * Human labels for hero variants (editor + docs).
 *
 * @return array<string, string>
 */
function jcp_block_hero_variant_labels(): array {
	return [
		'split'     => __( 'Split — copy + demo image', 'jcp-core' ),
		'centered'  => __( 'Centered — headline & CTA focus', 'jcp-core' ),
		'stacked'   => __( 'Stacked — copy above visual', 'jcp-core' ),
		'condensed' => __( 'Condensed — internal page hero', 'jcp-core' ),
		'home'      => __( 'Homepage — rotating headline + live phone', 'jcp-core' ),
	];
}
