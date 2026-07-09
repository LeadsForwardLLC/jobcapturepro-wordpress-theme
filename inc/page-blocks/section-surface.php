<?php
/**
 * Per-section background / surface styling for block pages.
 *
 * @package JCP_Core
 */

/**
 * Default section surface settings.
 *
 * @return array<string, mixed>
 */
function jcp_section_surface_defaults(): array {
	return [
		'preset'    => 'default',
		'color'     => '#ffffff',
		'opacity'   => 100,
		'image_url' => '',
	];
}

/**
 * Valid surface preset slugs.
 *
 * @return array<int, string>
 */
function jcp_section_surface_presets(): array {
	return [ 'default', 'white', 'off_white', 'dark', 'image', 'custom' ];
}

/**
 * @param array<string, mixed> $layout Block layout array.
 * @return array<string, mixed>
 */
function jcp_section_surface_resolve( array $layout ): array {
	$stored = is_array( $layout['section_surface'] ?? null ) ? $layout['section_surface'] : [];
	$surface = array_merge( jcp_section_surface_defaults(), $stored );

	$preset = sanitize_key( (string) ( $surface['preset'] ?? 'default' ) );
	if ( ! in_array( $preset, jcp_section_surface_presets(), true ) ) {
		$preset = 'default';
	}
	$surface['preset'] = $preset;

	$opacity = (int) ( $surface['opacity'] ?? 100 );
	$surface['opacity'] = max( 0, min( 100, $opacity ) );

	$color = trim( (string) ( $surface['color'] ?? '#ffffff' ) );
	if ( ! preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color ) ) {
		$color = '#ffffff';
	}
	$surface['color'] = $color;
	$surface['image_url'] = esc_url_raw( (string) ( $surface['image_url'] ?? '' ) );

	return $surface;
}

/**
 * Attributes for the block root element (class, style, data attrs).
 *
 * @param array<string, mixed> $layout Block layout.
 * @return array{class: string, style: string, data: array<string, string>}
 */
function jcp_section_surface_block_attrs( array $layout ): array {
	$surface = jcp_section_surface_resolve( $layout );
	$preset  = (string) $surface['preset'];

	if ( $preset === 'default' ) {
		return [
			'class' => '',
			'style' => '',
			'data'  => [],
		];
	}

	$classes = [ 'jcp-has-section-surface', 'jcp-section-surface--' . $preset ];
	$alpha   = max( 0, min( 100, (int) $surface['opacity'] ) ) / 100;
	$style   = '--jcp-section-bg-opacity:' . $alpha . ';';
	$data    = [
		'jcp-surface'         => $preset,
		'jcp-surface-opacity' => (string) $surface['opacity'],
	];

	if ( $preset === 'custom' ) {
		$data['jcp-surface-color'] = (string) $surface['color'];
		$style                    .= '--jcp-section-bg-color:' . $surface['color'] . ';';
	}

	if ( $preset === 'image' && $surface['image_url'] !== '' ) {
		$data['jcp-surface-image'] = (string) $surface['image_url'];
		$style                    .= '--jcp-section-bg-image:url(' . esc_url( $surface['image_url'] ) . ');';
	}

	return [
		'class' => implode( ' ', $classes ),
		'style' => $style,
		'data'  => $data,
	];
}

/**
 * Whether a block type supports section surface controls.
 *
 * @param string $type Block type.
 */
function jcp_section_surface_supports_type( string $type ): bool {
	return $type !== '' && $type !== 'breadcrumb';
}

/**
 * Whether a block prop visibility flag is on.
 *
 * @param array<string, mixed> $props   Section props.
 * @param string               $key    Flag key e.g. show_subheadline.
 * @param bool                 $default Default when unset.
 */
function jcp_niche_show_field( array $props, string $key, bool $default = true ): bool {
	if ( ! array_key_exists( $key, $props ) ) {
		return $default;
	}
	return ! empty( $props[ $key ] );
}
