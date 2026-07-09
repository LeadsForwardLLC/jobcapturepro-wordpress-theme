<?php
/**
 * Inline editor data attributes for industry pages.
 *
 * @package JCP_Core
 */

/**
 * Whether the current user can use the inline editor.
 */
function jcp_niche_user_can_inline_edit(): bool {
	return is_user_logged_in() && current_user_can( 'edit_posts' );
}

/**
 * Data attribute for editable text (JSON dot path).
 *
 * @param string $path e.g. hero.h1.
 */
function jcp_niche_editable_attr( string $path ): void {
	// Always emit paths so cached HTML still exposes edit targets when the toolbar loads.
	echo ' data-jcp-path="' . esc_attr( $path ) . '"';
}

/**
 * Editable rich text (allows inline links in the live editor).
 *
 * @param string $path e.g. what_it_is.subheadline.
 */
function jcp_niche_editable_rich_attr( string $path ): void {
	echo ' data-jcp-path="' . esc_attr( $path ) . '" data-jcp-rich="true"';
}

/**
 * Data attributes for editable link (label + url paths).
 *
 * @param string $base_path e.g. hero.cta_primary (maps to .label and .url).
 */
function jcp_niche_editable_link_attr( string $base_path ): void {
	echo ' data-jcp-path="' . esc_attr( $base_path ) . '.label" data-jcp-href-path="' . esc_attr( $base_path ) . '.url"';
}

/**
 * Link with explicit label and URL JSON paths.
 *
 * @param string $label_path Label path.
 * @param string $url_path   URL path.
 */
function jcp_niche_editable_link_paths( string $label_path, string $url_path ): void {
	echo ' data-jcp-path="' . esc_attr( $label_path ) . '" data-jcp-href-path="' . esc_attr( $url_path ) . '"';
}

/**
 * Marks a repeatable list container (add/remove items in the page editor).
 *
 * @param string $path JSON array path (e.g. conversion.points).
 */
function jcp_niche_array_attr( string $path ): void {
	echo ' data-jcp-array="' . esc_attr( $path ) . '"';
}

/**
 * Marks one item inside a repeatable list.
 *
 * @param int $index Item index.
 */
function jcp_niche_array_item_attr( int $index ): void {
	echo ' data-jcp-array-item="' . esc_attr( (string) $index ) . '"';
}

/**
 * Remove control for a repeatable list/card item (shown in inline edit mode via CSS).
 *
 * @param bool $list_item Use compact positioning for checklist rows.
 */
function jcp_niche_collection_remove_btn( bool $list_item = false ): void {
	if ( ! jcp_niche_user_can_inline_edit() ) {
		return;
	}
	$class = 'jcp-collection-remove' . ( $list_item ? ' jcp-collection-remove--list-item' : '' );
	printf(
		'<button type="button" class="%1$s" aria-label="%2$s" title="%3$s" tabindex="-1">×</button>',
		esc_attr( $class ),
		esc_attr__( 'Remove item', 'jcp-core' ),
		esc_attr__( 'Remove', 'jcp-core' )
	);
}

/**
 * Add control at the bottom of a repeatable list container.
 *
 * @param string $label Button label.
 */
function jcp_niche_collection_add_btn( string $label = '' ): void {
	if ( ! jcp_niche_user_can_inline_edit() ) {
		return;
	}
	if ( $label === '' ) {
		$label = __( '+ Add item', 'jcp-core' );
	}
	printf(
		'<button type="button" class="jcp-collection-add" tabindex="-1">%s</button>',
		esc_html( $label )
	);
}

/**
 * Strip editor artifact suffixes from how-it-works checklist lines.
 *
 * @param string $text Raw line text from JSON.
 */
function jcp_niche_clean_step_line( string $text ): string {
	$text = trim( $text );
	for ( $i = 0; $i < 3; $i++ ) {
		$text = preg_replace( '/(?:nttt)+x*\s*$/iu', '', $text );
		$text = preg_replace( '/[\s\x{00D7}]+$/u', '', $text );
		$text = preg_replace( '/x\s*$/iu', '', $text );
		$text = trim( $text );
	}

	return $text;
}

/**
 * Clean a list of string lines (checklists, step bullets, tags).
 *
 * @param array<int, mixed> $items Raw items.
 * @return array<int, string>
 */
function jcp_niche_clean_string_list( array $items ): array {
	$out = [];
	foreach ( $items as $item ) {
		$out[] = jcp_niche_clean_step_line( is_string( $item ) ? $item : (string) $item );
	}
	return $out;
}

/**
 * Remove editor garbage from block props before render/save.
 *
 * @param array<string, mixed> $block Block document entry.
 * @return array<string, mixed>
 */
function jcp_page_sanitize_block_props( array $block ): array {
	$type  = (string) ( $block['type'] ?? '' );
	$props = $block['props'] ?? [];
	if ( ! is_array( $props ) ) {
		return $block;
	}

	switch ( $type ) {
		case 'how_it_works':
			if ( ! empty( $props['steps'] ) && is_array( $props['steps'] ) ) {
				foreach ( $props['steps'] as $i => $step ) {
					if ( ! is_array( $step ) || empty( $step['lines'] ) || ! is_array( $step['lines'] ) ) {
						continue;
					}
					$props['steps'][ $i ]['lines'] = jcp_niche_clean_string_list( $step['lines'] );
				}
			}
			break;
		case 'what_it_is':
			foreach ( [ 'team_already', 'turns_into' ] as $key ) {
				if ( ! empty( $props[ $key ] ) && is_array( $props[ $key ] ) ) {
					$props[ $key ] = jcp_niche_clean_string_list( $props[ $key ] );
				}
			}
			break;
		case 'check_ins':
			if ( ! empty( $props['job_types'] ) && is_array( $props['job_types'] ) ) {
				$props['job_types'] = jcp_niche_clean_string_list( $props['job_types'] );
			}
			break;
		case 'differentiation':
			if ( ! empty( $props['bullets'] ) && is_array( $props['bullets'] ) ) {
				$props['bullets'] = jcp_niche_clean_string_list( $props['bullets'] );
			}
			break;
		case 'conversion':
			if ( ! empty( $props['points'] ) && is_array( $props['points'] ) ) {
				$props['points'] = jcp_niche_clean_string_list( $props['points'] );
			}
			break;
	}

	$block['props'] = $props;
	return $block;
}

/**
 * Sanitize all checklist/step strings in a block document.
 *
 * @param array<string, mixed> $content Block document.
 * @return array<string, mixed>
 */
function jcp_page_sanitize_content_document( array $content ): array {
	if ( empty( $content['blocks'] ) || ! is_array( $content['blocks'] ) ) {
		return $content;
	}
	foreach ( $content['blocks'] as $i => $block ) {
		if ( is_array( $block ) ) {
			$content['blocks'][ $i ] = jcp_page_sanitize_block_props( $block );
		}
	}
	return $content;
}

/**
 * Marks an optional slot (button, card row) that can be removed and restored.
 *
 * @param string $path  JSON path (e.g. conversion.cta_primary).
 * @param string $kind  Slot kind: cta, link, or box.
 * @param string $label Placeholder label when removed.
 */
function jcp_niche_optional_slot_attr( string $path, string $kind = 'cta', string $label = '' ): void {
	echo ' data-jcp-optional="' . esc_attr( $path ) . '" data-jcp-optional-kind="' . esc_attr( $kind ) . '"';
	if ( $label !== '' ) {
		echo ' data-jcp-optional-label="' . esc_attr( $label ) . '"';
	}
}

/**
 * Matomo CTA tracking attributes for outbound / key conversion links.
 *
 * @param string $url       Link URL.
 * @param string $location  Section context (e.g. referral_hero).
 * @param string $cta_name  Optional event name override.
 */
function jcp_niche_cta_tracking_attr( string $url, string $location, string $cta_name = '' ): void {
	$url = trim( $url );
	if ( $url === '' || $location === '' ) {
		return;
	}

	$host = wp_parse_url( $url, PHP_URL_HOST );
	$host = is_string( $host ) ? strtolower( $host ) : '';
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$path = is_string( $path ) ? rtrim( $path, '/' ) : '';

	$is_referral_outbound = $host !== '' && str_contains( $host, 'firstpromoter.com' );
	$is_onboarding        = $host !== '' && str_contains( $host, 'jobcapturepro.com' ) && str_contains( $path, '/onboarding' );
	$is_key_conversion    = in_array( $path, [ '/demo', '/referral-program' ], true );

	if ( ! $is_referral_outbound && ! $is_onboarding && ! $is_key_conversion ) {
		return;
	}

	$name = $cta_name !== ''
		? $cta_name
		: ( $is_referral_outbound ? 'Join Referral Program' : ( $is_onboarding ? 'Start free trial' : '' ) );
	if ( $name !== '' ) {
		echo ' data-cta="' . esc_attr( $name ) . '"';
	}
	echo ' data-cta-location="' . esc_attr( $location ) . '"';
}
