<?php
/**
 * Persist case-study form_embed display=modal in stored page content.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force /case-study/ form embed to modal mode in saved JSON.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_case_study_form_modal( array $content, int $post_id ): array {
	$slug = (string) get_post_field( 'post_name', $post_id );
	if ( $slug !== 'case-study' ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) || $blocks === [] ) {
		return $content;
	}

	$changed = false;
	foreach ( $blocks as $i => $block ) {
		if ( ! is_array( $block ) || ( $block['type'] ?? '' ) !== 'form_embed' ) {
			continue;
		}
		$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];
		if ( sanitize_key( (string) ( $props['display'] ?? 'inline' ) ) === 'modal' ) {
			continue;
		}
		$props['display']         = 'modal';
		$blocks[ $i ]['props']    = $props;
		$changed                  = true;
	}

	if ( ! $changed ) {
		return $content;
	}

	$content['blocks'] = $blocks;
	return $content;
}

/**
 * Avoid stale HTML for the case-study campaign (modal markup must stay fresh).
 */
function jcp_case_study_nocache_headers(): void {
	if ( is_admin() || ! is_singular() ) {
		return;
	}
	if ( get_post_field( 'post_name', get_queried_object_id() ) !== 'case-study' ) {
		return;
	}
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	if ( ! defined( 'DONOTCACHEDYNAMIC' ) ) {
		define( 'DONOTCACHEDYNAMIC', true );
	}
	nocache_headers();
	// SiteGround / Cloudflare-facing hints (in addition to nocache_headers).
	header( 'Cache-Control: no-cache, must-revalidate, max-age=0, no-store, private' );
	header( 'Pragma: no-cache' );
	header( 'X-JCP-Cache: bypass-case-study' );
}
add_action( 'template_redirect', 'jcp_case_study_nocache_headers', 0 );

/**
 * Exclude /case-study/ from SiteGround Dynamic Cache (campaign form must not stick).
 *
 * @param array<int, string> $excluded_urls Excluded URL parts.
 * @return array<int, string>
 */
function jcp_case_study_sgo_exclude_urls( array $excluded_urls ): array {
	$excluded_urls[] = '/case-study/';
	$excluded_urls[] = '/case-study';
	return $excluded_urls;
}
add_filter( 'sgo_exclude_urls_from_cache', 'jcp_case_study_sgo_exclude_urls' );
