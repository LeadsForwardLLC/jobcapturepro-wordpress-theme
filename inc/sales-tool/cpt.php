<?php
/**
 * Sales deck custom post type.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT for branded sales presentations.
 */
function jcp_sales_deck_register_cpt(): void {
	$labels = [
		'name'               => __( 'Sales Decks', 'jcp-core' ),
		'singular_name'      => __( 'Sales Deck', 'jcp-core' ),
		'add_new'            => __( 'Add New', 'jcp-core' ),
		'add_new_item'       => __( 'Add New Sales Deck', 'jcp-core' ),
		'edit_item'          => __( 'Edit Sales Deck', 'jcp-core' ),
		'new_item'           => __( 'New Sales Deck', 'jcp-core' ),
		'view_item'          => __( 'View Sales Deck', 'jcp-core' ),
		'search_items'       => __( 'Search Sales Decks', 'jcp-core' ),
		'not_found'          => __( 'No sales decks found.', 'jcp-core' ),
		'not_found_in_trash' => __( 'No sales decks found in Trash.', 'jcp-core' ),
		'menu_name'          => __( 'Sales Decks', 'jcp-core' ),
	];

	register_post_type(
		'jcp_sales_deck',
		[
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			// Nest under JCP (see jcp-theme-settings top-level menu).
			'show_in_menu'        => 'jcp-theme-settings',
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => [
				'slug'       => 'sales',
				'with_front' => false,
			],
			'menu_icon'           => 'dashicons-presentation',
			'supports'            => [ 'title' ],
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
		]
	);
}
add_action( 'init', 'jcp_sales_deck_register_cpt' );

/**
 * Keep the JCP parent menu open on Sales Deck screens.
 *
 * @param string $parent_file Current parent file.
 */
function jcp_sales_deck_parent_file( string $parent_file ): string {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && $screen->post_type === 'jcp_sales_deck' ) {
		return 'jcp-theme-settings';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin menu highlight only.
	if ( isset( $_GET['page'] ) && sanitize_key( (string) wp_unslash( $_GET['page'] ) ) === 'jcp-sales-call-script' ) {
		return 'jcp-theme-settings';
	}
	return $parent_file;
}
add_filter( 'parent_file', 'jcp_sales_deck_parent_file' );

/**
 * Highlight the correct JCP submenu on Sales Deck screens.
 *
 * @param string $submenu_file Current submenu file.
 */
function jcp_sales_deck_submenu_file( string $submenu_file ): string {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && $screen->post_type === 'jcp_sales_deck' ) {
		if ( $screen->base === 'post' && $screen->action === 'add' ) {
			return 'post-new.php?post_type=jcp_sales_deck';
		}
		return 'edit.php?post_type=jcp_sales_deck';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin menu highlight only.
	if ( isset( $_GET['page'] ) && sanitize_key( (string) wp_unslash( $_GET['page'] ) ) === 'jcp-sales-call-script' ) {
		return 'jcp-sales-call-script';
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'jcp_sales_deck_submenu_file' );
