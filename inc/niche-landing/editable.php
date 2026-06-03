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
	if ( ! jcp_niche_user_can_inline_edit() ) {
		return;
	}
	echo ' data-jcp-path="' . esc_attr( $path ) . '"';
}

/**
 * Data attributes for editable link (label + url paths).
 *
 * @param string $base_path e.g. hero.cta_primary (maps to .label and .url).
 */
function jcp_niche_editable_link_attr( string $base_path ): void {
	if ( ! jcp_niche_user_can_inline_edit() ) {
		return;
	}
	echo ' data-jcp-path="' . esc_attr( $base_path ) . '.label" data-jcp-href-path="' . esc_attr( $base_path ) . '.url"';
}

/**
 * Link with explicit label and URL JSON paths.
 *
 * @param string $label_path Label path.
 * @param string $url_path   URL path.
 */
function jcp_niche_editable_link_paths( string $label_path, string $url_path ): void {
	if ( ! jcp_niche_user_can_inline_edit() ) {
		return;
	}
	echo ' data-jcp-path="' . esc_attr( $label_path ) . '" data-jcp-href-path="' . esc_attr( $url_path ) . '"';
}
