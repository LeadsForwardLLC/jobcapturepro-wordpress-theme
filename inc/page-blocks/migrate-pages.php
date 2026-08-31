<?php
/**
 * One-time migrations: existing WP pages → JCP Block Page template + blocks storage.
 *
 * @package JCP_Core
 */

/**
 * Migrate /referral-program/ to the unified JCP Block Page template.
 *
 * Preserves URL, post ID, Rank Math meta, and any saved page content.
 * Normalizes legacy flat JSON into blocks[] on first run.
 *
 * @return int Post ID or 0.
 */
function jcp_page_migrate_referral_program_to_jcp_blocks(): int {
	if ( function_exists( 'jcp_niche_seed_referral_program' ) ) {
		jcp_niche_seed_referral_program();
	}

	$page = get_page_by_path( 'referral-program', OBJECT, 'page' );
	if ( ! $page instanceof WP_Post ) {
		return 0;
	}

	$post_id = (int) $page->ID;

	$content = jcp_page_get_content( $post_id );
	jcp_page_save_content( $post_id, $content );

	update_post_meta( $post_id, '_wp_page_template', 'page-jcp-blocks.php' );

	return $post_id;
}

/**
 * Migrate homepage (front page) to block storage.
 *
 * Only seeds when no block content exists — never overwrites admin edits.
 *
 * @return int Post ID or 0.
 */
function jcp_page_migrate_home_to_blocks(): int {
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id <= 0 ) {
		$pages = get_pages(
			[
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'page-home.php',
				'number'     => 1,
			]
		);
		$front_id = ! empty( $pages[0] ) ? (int) $pages[0]->ID : 0;
	}
	if ( $front_id <= 0 ) {
		return 0;
	}

	if ( get_post_meta( $front_id, jcp_page_content_meta_key(), true ) ) {
		return $front_id;
	}

	$preset = jcp_page_load_preset( 'home' );
	if ( empty( $preset ) ) {
		return 0;
	}

	$content = jcp_page_normalize_content( $preset, $front_id );
	$content['page_kind'] = 'home';
	jcp_page_save_content( $front_id, $content );
	update_post_meta( $front_id, '_wp_page_template', 'page-home.php' );

	return $front_id;
}

/**
 * Force-refresh homepage content from dummy-home.json when seed version changes.
 *
 * @param bool $force_refresh When true, overwrite saved block content from the home preset.
 * @return int Post ID or 0.
 */
function jcp_page_seed_home( bool $force_refresh = false ): int {
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id <= 0 ) {
		$pages = get_pages(
			[
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'page-home.php',
				'number'     => 1,
			]
		);
		$front_id = ! empty( $pages[0] ) ? (int) $pages[0]->ID : 0;
	}
	if ( $front_id <= 0 ) {
		return 0;
	}

	$preset = jcp_page_load_preset( 'home' );
	if ( empty( $preset ) ) {
		return 0;
	}

	$asset_base = trailingslashit( get_template_directory_uri() ) . 'assets/campaign';
	$encoded    = wp_json_encode( $preset );
	if ( is_string( $encoded ) && $encoded !== '' ) {
		$encoded = str_replace( '__CAMPAIGN_ASSET__', $asset_base, $encoded );
		$decoded = json_decode( $encoded, true );
		if ( is_array( $decoded ) ) {
			$preset = $decoded;
		}
	}

	$existing = get_post_meta( $front_id, jcp_page_content_meta_key(), true );
	if ( $existing && ! $force_refresh ) {
		return $front_id;
	}

	$content = jcp_page_normalize_content( $preset, $front_id );
	$content['page_kind'] = 'home';
	jcp_page_save_content( $front_id, $content );
	update_post_meta( $front_id, '_wp_page_template', 'page-home.php' );

	return $front_id;
}

/**
 * Run page migrations once per environment after deploy.
 */
function jcp_page_maybe_migrate_pages(): void {
	if ( get_option( 'jcp_migrated_referral_jcp_blocks_v1' ) !== '1' ) {
		$id = jcp_page_migrate_referral_program_to_jcp_blocks();
		if ( $id > 0 ) {
			update_option( 'jcp_migrated_referral_jcp_blocks_v1', '1' );
		}
	}
	if ( get_option( 'jcp_migrated_home_blocks_v1' ) !== '1' ) {
		$id = jcp_page_migrate_home_to_blocks();
		if ( $id > 0 ) {
			update_option( 'jcp_migrated_home_blocks_v1', '1' );
		}
	}

	// v7 = Keep all four testimonials visible in grid (restore Peter Bonk).
	$home_ver = (string) get_option( 'jcp_home_seed_version', '' );
	if ( $home_ver !== '7' ) {
		$id = jcp_page_seed_home( true );
		if ( $id > 0 ) {
			update_option( 'jcp_home_seed_version', '7' );
		}
	}
}
add_action( 'init', 'jcp_page_maybe_migrate_pages', 25 );
