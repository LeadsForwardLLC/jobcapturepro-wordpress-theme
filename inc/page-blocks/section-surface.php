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
	$val = $props[ $key ];
	if ( is_bool( $val ) ) {
		return $val;
	}
	if ( $val === 0 || $val === '0' || $val === 'false' || $val === '' || $val === null ) {
		return false;
	}
	if ( $val === 1 || $val === '1' || $val === 'true' ) {
		return true;
	}
	return ! empty( $val );
}

/**
 * Visibility for a field: whether to render (always in editor) and optional hidden attr.
 *
 * Keeps markup in the DOM while inline-editing so SHOW toggles can turn fields back on.
 *
 * @param array<string, mixed> $props   Section props.
 * @param string               $key     Flag key.
 * @param bool                 $default Default when unset.
 * @return array{show: bool, render: bool, attr: string}
 */
function jcp_niche_field_visibility( array $props, string $key, bool $default = true ): array {
	$show = jcp_niche_show_field( $props, $key, $default );
	$edit = function_exists( 'jcp_niche_user_can_inline_edit' ) && jcp_niche_user_can_inline_edit();
	return [
		'show'   => $show,
		'render' => $show || $edit,
		'attr'   => ( ! $show && $edit ) ? ' style="display:none"' : '',
	];
}

/**
 * Section CSS modifiers for icon/card-piece visibility flags.
 *
 * @param array<string, mixed> $props Section props.
 * @param array<string, bool>  $flags Flag key => default (only include keys this section supports).
 */
function jcp_niche_section_visibility_classes( array $props, array $flags ): string {
	$map = [
		'show_icons'        => 'jcp-section--no-icons',
		'show_card_titles'  => 'jcp-section--no-card-titles',
		'show_card_body'    => 'jcp-section--no-card-body',
		'show_card_stats'   => 'jcp-section--no-card-stats',
		'show_card_images'  => 'jcp-section--no-card-images',
		'show_card_badges'  => 'jcp-section--no-card-badges',
	];
	$classes = [];
	foreach ( $flags as $key => $default ) {
		if ( ! isset( $map[ $key ] ) ) {
			continue;
		}
		if ( ! jcp_niche_show_field( $props, $key, (bool) $default ) ) {
			$classes[] = $map[ $key ];
		}
	}
	return $classes ? ' ' . implode( ' ', $classes ) : '';
}
