<?php
/**
 * Seed default industry pages (plumbing demo).
 *
 * @package JCP_Core
 */

/**
 * Create plumbing industry page if missing.
 *
 * @return int Post ID or 0.
 */
function jcp_niche_seed_plumbing(): int {
	$existing = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'name'           => 'plumbing',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);
	if ( ! empty( $existing[0] ) ) {
		$id = (int) $existing[0];
		if ( ! get_post_meta( $id, jcp_page_content_meta_key(), true ) && ! get_post_meta( $id, jcp_page_legacy_meta_key(), true ) ) {
			jcp_niche_save_content( $id, jcp_niche_load_preset( 'plumbing' ) );
		}
		return $id;
	}

	$preset = jcp_niche_load_preset( 'plumbing' );
	$id     = wp_insert_post(
		[
			'post_type'    => 'jcp_niche_landing',
			'post_status'  => 'publish',
			'post_name'    => 'plumbing',
			'post_title'   => ! empty( $preset['niche_label'] ) ? (string) $preset['niche_label'] : 'Plumbing',
			'post_excerpt' => ! empty( $preset['hero']['subheadline'] ) ? wp_strip_all_tags( (string) $preset['hero']['subheadline'] ) : '',
		],
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	jcp_niche_save_content( (int) $id, $preset );
	return (int) $id;
}

/**
 * Whether the plumbing demo post exists.
 */
function jcp_niche_plumbing_exists(): bool {
	$ids = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'name'           => 'plumbing',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);
	return ! empty( $ids[0] );
}

/**
 * Whether the HVAC industry post exists.
 */
function jcp_niche_hvac_exists(): bool {
	$ids = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'name'           => 'hvac',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);
	return ! empty( $ids[0] );
}

/**
 * Create HVAC industry page if missing.
 *
 * @return int Post ID or 0.
 */
function jcp_niche_seed_hvac(): int {
	$existing = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'name'           => 'hvac',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);
	if ( ! empty( $existing[0] ) ) {
		$id = (int) $existing[0];
		if ( ! get_post_meta( $id, jcp_page_content_meta_key(), true ) && ! get_post_meta( $id, jcp_page_legacy_meta_key(), true ) ) {
			jcp_niche_save_content( $id, jcp_niche_load_preset( 'hvac' ) );
		}
		return $id;
	}

	$preset = jcp_niche_load_preset( 'hvac' );
	$id     = wp_insert_post(
		[
			'post_type'    => 'jcp_niche_landing',
			'post_status'  => 'publish',
			'post_name'    => 'hvac',
			'post_title'   => ! empty( $preset['niche_label'] ) ? (string) $preset['niche_label'] : 'HVAC',
			'post_excerpt' => ! empty( $preset['hero']['subheadline'] ) ? wp_strip_all_tags( (string) $preset['hero']['subheadline'] ) : '',
		],
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	jcp_niche_save_content( (int) $id, $preset );
	return (int) $id;
}

/**
 * Create referral program page if missing.
 *
 * @return int Post ID or 0.
 */
function jcp_niche_seed_referral_program(): int {
	$existing = get_page_by_path( 'referral-program', OBJECT, 'page' );
	if ( $existing instanceof WP_Post ) {
		$id = (int) $existing->ID;
		if ( get_page_template_slug( $id ) !== 'page-jcp-blocks.php' && get_page_template_slug( $id ) !== 'page-referral-program.php' ) {
			update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
		}
		if ( ! get_post_meta( $id, jcp_page_content_meta_key(), true ) && ! get_post_meta( $id, jcp_page_legacy_meta_key(), true ) ) {
			jcp_niche_save_content( $id, jcp_niche_load_preset( 'referral-program' ) );
		}
		return $id;
	}

	$preset = jcp_niche_load_preset( 'referral-program' );
	$id     = wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'referral-program',
			'post_title'   => ! empty( $preset['niche_label'] ) ? (string) $preset['niche_label'] : 'Referral Program',
			'post_excerpt' => ! empty( $preset['hero']['subheadline'] ) ? wp_strip_all_tags( (string) $preset['hero']['subheadline'] ) : '',
		],
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	$id = (int) $id;
	update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
	jcp_niche_save_content( $id, $preset );
	return $id;
}

/**
 * Whether the referral program page exists.
 */
function jcp_niche_referral_exists(): bool {
	$page = get_page_by_path( 'referral-program', OBJECT, 'page' );
	return $page instanceof WP_Post;
}

/**
 * Build campaign block document from dummy-campaign.json.
 *
 * @return array<string, mixed>
 */
function jcp_niche_contractor_demo_document(): array {
	$legacy = jcp_page_load_preset( 'campaign' );
	if ( empty( $legacy ) ) {
		return [];
	}

	$asset_base = trailingslashit( get_template_directory_uri() ) . 'assets/campaign';
	$encoded    = wp_json_encode( $legacy );
	if ( is_string( $encoded ) && $encoded !== '' ) {
		$encoded = str_replace( '__CAMPAIGN_ASSET__', $asset_base, $encoded );
		$decoded = json_decode( $encoded, true );
		if ( is_array( $decoded ) ) {
			$legacy = $decoded;
		}
	}

	$doc = jcp_page_legacy_to_blocks( $legacy, 0 );
	if ( function_exists( 'jcp_page_finalize_campaign_document' ) ) {
		$doc = jcp_page_finalize_campaign_document( $doc );
	}
	$doc['page_kind'] = 'marketing';
	$doc['preset']    = 'campaign';
	return $doc;
}

/**
 * Create or refresh the Meta paid landing page at /contractor-demo/.
 *
 * @param bool $force_refresh When true, overwrite saved block content from the campaign preset.
 * @return int Post ID or 0.
 */
function jcp_niche_seed_contractor_demo( bool $force_refresh = false ): int {
	$existing = get_page_by_path( 'contractor-demo', OBJECT, 'page' );
	$doc      = jcp_niche_contractor_demo_document();
	if ( empty( $doc ) ) {
		return 0;
	}

	if ( $existing instanceof WP_Post ) {
		$id = (int) $existing->ID;
		if ( get_page_template_slug( $id ) !== 'page-jcp-blocks.php' ) {
			update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
		}
		$has_content = (string) get_post_meta( $id, jcp_page_content_meta_key(), true ) !== ''
			|| (string) get_post_meta( $id, jcp_page_legacy_meta_key(), true ) !== '';
		if ( $force_refresh || ! $has_content ) {
			jcp_page_save_content( $id, $doc );
		}
		return $id;
	}

	$id = wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'contractor-demo',
			'post_title'   => 'See JobCapturePro On Your Business',
			'post_excerpt' => 'Free personalized demo for contractors. Turn completed jobs into proof.',
		],
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	$id = (int) $id;
	update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
	jcp_page_save_content( $id, $doc );
	return $id;
}

/**
 * Whether the contractor demo landing page exists.
 */
function jcp_niche_contractor_demo_exists(): bool {
	$page = get_page_by_path( 'contractor-demo', OBJECT, 'page' );
	return $page instanceof WP_Post;
}

/**
 * Build home_v2 preview document from dummy-home_v2.json.
 *
 * @return array<string, mixed>
 */
function jcp_niche_home_preview_document(): array {
	$legacy = jcp_page_load_preset( 'home_v2' );
	if ( empty( $legacy ) ) {
		return [];
	}

	$campaign_base = trailingslashit( get_template_directory_uri() ) . 'assets/campaign';
	$home_v2_base  = trailingslashit( get_template_directory_uri() ) . 'assets/home-v2';
	$encoded       = wp_json_encode( $legacy );
	if ( is_string( $encoded ) && $encoded !== '' ) {
		$encoded = str_replace(
			[ '__CAMPAIGN_ASSET__', '__HOME_V2_ASSET__' ],
			[ $campaign_base, $home_v2_base ],
			$encoded
		);
		$decoded = json_decode( $encoded, true );
		if ( is_array( $decoded ) ) {
			$legacy = $decoded;
		}
	}

	$doc = jcp_page_legacy_to_blocks( $legacy, 0 );
	$doc['page_kind'] = 'home';
	$doc['preset']    = 'home_v2';
	if ( ! isset( $doc['settings'] ) || ! is_array( $doc['settings'] ) ) {
		$doc['settings'] = [];
	}
	$doc['settings']['hide_breadcrumb'] = true;
	$doc['settings']['hide_site_chrome'] = false;
	$doc['settings']['home_preview']     = true;
	$doc['settings']['noindex']          = true;

	// Sales-deck hero: never use the homepage rotating-word variant.
	$preset_order = [];
	$preset_def   = function_exists( 'jcp_page_get_preset' ) ? jcp_page_get_preset( 'home_v2' ) : null;
	if ( is_array( $preset_def ) ) {
		foreach ( (array) ( $preset_def['block_types'] ?? [] ) as $entry ) {
			$parsed = function_exists( 'jcp_page_parse_preset_block_entry' )
				? jcp_page_parse_preset_block_entry( $entry )
				: [ 'type' => is_string( $entry ) ? $entry : '' ];
			if ( ( $parsed['type'] ?? '' ) !== '' ) {
				$preset_order[] = (string) $parsed['type'];
			}
		}
	}

	$blocks_in = is_array( $doc['blocks'] ?? null ) ? $doc['blocks'] : [];
	$by_type   = [];
	foreach ( $blocks_in as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		if ( $type === 'hero' ) {
			if ( ! isset( $block['layout'] ) || ! is_array( $block['layout'] ) ) {
				$block['layout'] = [];
			}
			$block['layout']['hero_variant'] = 'split';
			if ( isset( $block['props'] ) && is_array( $block['props'] ) ) {
				unset( $block['props']['rotating_words'] );
			}
		}
		if ( $type !== '' ) {
			$by_type[ $type ] = $block;
		}
	}

	if ( $preset_order ) {
		$ordered = [];
		foreach ( $preset_order as $type ) {
			if ( isset( $by_type[ $type ] ) ) {
				$ordered[] = $by_type[ $type ];
				unset( $by_type[ $type ] );
			}
		}
		foreach ( $by_type as $block ) {
			$ordered[] = $block;
		}
		$doc['blocks'] = $ordered;
	} else {
		$doc['blocks'] = array_values( $by_type );
	}

	return $doc;
}

/**
 * Retire /home-preview/ — draft the page and stop reseed.
 *
 * @return int Post ID retired, or 0.
 */
function jcp_niche_retire_home_preview(): int {
	$page = get_page_by_path( 'home-preview', OBJECT, 'page' );
	if ( ! $page instanceof WP_Post ) {
		update_option( 'jcp_home_preview_retired', '1' );
		return 0;
	}
	$id = (int) $page->ID;
	if ( $page->post_status !== 'trash' && $page->post_status !== 'draft' ) {
		wp_update_post(
			[
				'ID'          => $id,
				'post_status' => 'draft',
			]
		);
	}
	update_option( 'jcp_home_preview_retired', '1' );
	update_option( 'jcp_home_preview_seed_version', 'retired' );
	return $id;
}

/**
 * Create or refresh the homepage redesign preview at /home-preview/.
 *
 * @deprecated Preview route retired — prefer jcp_niche_retire_home_preview().
 *
 * @param bool $force_refresh When true, overwrite saved block content from the home_v2 preset.
 * @return int Post ID or 0.
 */
function jcp_niche_seed_home_preview( bool $force_refresh = false ): int {
	// Preview cutover complete — do not recreate /home-preview/.
	if ( (string) get_option( 'jcp_home_preview_retired', '' ) === '1' && ! $force_refresh ) {
		return 0;
	}
	// Force refresh from admin still allowed for recovery; deploy uses retire instead.
	$existing = get_page_by_path( 'home-preview', OBJECT, 'page' );
	$doc      = jcp_niche_home_preview_document();
	if ( empty( $doc ) ) {
		return 0;
	}

	if ( $existing instanceof WP_Post ) {
		$id = (int) $existing->ID;
		if ( get_page_template_slug( $id ) !== 'page-jcp-blocks.php' ) {
			update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
		}
		$has_content = (string) get_post_meta( $id, jcp_page_content_meta_key(), true ) !== ''
			|| (string) get_post_meta( $id, jcp_page_legacy_meta_key(), true ) !== '';
		if ( $force_refresh || ! $has_content ) {
			jcp_page_save_content( $id, $doc );
		}
		return $id;
	}

	$id = wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'draft',
			'post_name'    => 'home-preview',
			'post_title'   => 'Home Preview — Sales Deck Homepage (retired)',
			'post_excerpt' => 'Retired preview route. Live homepage owns the funnel.',
		],
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	$id = (int) $id;
	update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
	jcp_page_save_content( $id, $doc );
	return $id;
}

/**
 * Whether the home preview page exists.
 */
function jcp_niche_home_preview_exists(): bool {
	$page = get_page_by_path( 'home-preview', OBJECT, 'page' );
	return $page instanceof WP_Post;
}

/**
 * Run seed after theme deploy; re-run if flag set but post was removed.
 */
function jcp_niche_maybe_seed(): void {
	if ( get_option( 'jcp_niche_plumbing_seeded' ) !== '1' || ! jcp_niche_plumbing_exists() ) {
		$created = jcp_niche_seed_plumbing();
		if ( $created > 0 ) {
			update_option( 'jcp_niche_plumbing_seeded', '1' );
		}
	}
	if ( get_option( 'jcp_niche_hvac_seeded' ) !== '1' || ! jcp_niche_hvac_exists() ) {
		$created = jcp_niche_seed_hvac();
		if ( $created > 0 ) {
			update_option( 'jcp_niche_hvac_seeded', '1' );
		}
	}
	if ( get_option( 'jcp_niche_referral_seeded' ) !== '1' || ! jcp_niche_referral_exists() ) {
		$created = jcp_niche_seed_referral_program();
		if ( $created > 0 ) {
			update_option( 'jcp_niche_referral_seeded', '1' );
		}
	}

	// v24 = hero CTA microcopy in-button + full-flow proof strip (5 channels → more jobs).
	$demo_ver = (string) get_option( 'jcp_contractor_demo_seed_version', '' );
	if ( $demo_ver !== '24' || ! jcp_niche_contractor_demo_exists() ) {
		$created = jcp_niche_seed_contractor_demo( $demo_ver !== '24' );
		if ( $created > 0 ) {
			update_option( 'jcp_contractor_demo_seed_version', '24' );
			update_option( 'jcp_niche_contractor_demo_seeded', '1' );
		}
	}

	// /home-preview/ retired — draft page, stop reseeding.
	if ( (string) get_option( 'jcp_home_preview_retired', '' ) !== '1' ) {
		jcp_niche_retire_home_preview();
	}
}
add_action( 'init', 'jcp_niche_maybe_seed', 20 );

/**
 * Admin action to re-run seed.
 */
function jcp_niche_admin_seed_notice(): void {
	if ( ! isset( $_GET['jcp_niche_seed'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'jcp_niche_seed' );
	jcp_niche_seed_plumbing();
	jcp_niche_seed_hvac();
	jcp_niche_seed_referral_program();
	jcp_niche_seed_contractor_demo( true );
	jcp_niche_retire_home_preview();
	update_option( 'jcp_niche_plumbing_seeded', '1' );
	update_option( 'jcp_niche_hvac_seeded', '1' );
	update_option( 'jcp_niche_referral_seeded', '1' );
	update_option( 'jcp_niche_contractor_demo_seeded', '1' );
	update_option( 'jcp_contractor_demo_seed_version', '24' );
	wp_safe_redirect( admin_url( 'edit.php?post_type=jcp_niche_landing&jcp_seeded=1' ) );
	exit;
}
add_action( 'admin_init', 'jcp_niche_admin_seed_notice' );

/**
 * @param string $which Which tab.
 */
function jcp_niche_admin_list_extra( string $which ): void {
	if ( $which !== 'top' || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'edit.php?post_type=jcp_niche_landing&jcp_niche_seed=1' ), 'jcp_niche_seed' );
	echo '<a href="' . esc_url( $url ) . '" class="page-title-action">' . esc_html__( 'Seed industry demos', 'jcp-core' ) . '</a>';
}
add_action( 'manage_posts_extra_tablenav', 'jcp_niche_admin_list_extra' );

/**
 * Notice when no industry pages exist.
 */
function jcp_niche_admin_empty_notice(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'jcp_niche_landing' ) {
		return;
	}
	$count = (int) wp_count_posts( 'jcp_niche_landing' )->publish;
	if ( $count > 0 ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'edit.php?post_type=jcp_niche_landing&jcp_niche_seed=1' ), 'jcp_niche_seed' );
	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'No industry pages yet.', 'jcp-core' );
	echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Create plumbing demo', 'jcp-core' ) . '</a>';
	echo '</p></div>';
}
add_action( 'admin_notices', 'jcp_niche_admin_empty_notice' );
