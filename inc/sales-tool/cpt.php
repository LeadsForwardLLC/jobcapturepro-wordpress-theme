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
			'show_in_menu'        => true,
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
