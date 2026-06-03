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
		if ( ! get_post_meta( $id, jcp_niche_content_meta_key(), true ) ) {
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
 * Run seed after theme deploy; re-run if flag set but post was removed.
 */
function jcp_niche_maybe_seed(): void {
	if ( get_option( 'jcp_niche_plumbing_seeded' ) === '1' && jcp_niche_plumbing_exists() ) {
		return;
	}
	$created = jcp_niche_seed_plumbing();
	if ( $created > 0 ) {
		update_option( 'jcp_niche_plumbing_seeded', '1' );
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
	update_option( 'jcp_niche_plumbing_seeded', '1' );
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
	echo '<a href="' . esc_url( $url ) . '" class="page-title-action">' . esc_html__( 'Seed plumbing demo', 'jcp-core' ) . '</a>';
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
