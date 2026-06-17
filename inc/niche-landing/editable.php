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
	$is_key_conversion    = in_array( $path, [ '/demo', '/early-access', '/referral-program' ], true );

	if ( ! $is_referral_outbound && ! $is_key_conversion ) {
		return;
	}

	$name = $cta_name !== '' ? $cta_name : ( $is_referral_outbound ? 'Join Referral Program' : '' );
	if ( $name !== '' ) {
		echo ' data-cta="' . esc_attr( $name ) . '"';
	}
	echo ' data-cta-location="' . esc_attr( $location ) . '"';
}
