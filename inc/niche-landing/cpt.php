<?php
/**
 * Industry / niche landing pages CPT.
 *
 * URLs: /industries/ (archive), /industries/plumbing/ (single).
 *
 * @package JCP_Core
 */

/**
 * Register jcp_niche_landing post type.
 */
function jcp_niche_register_post_type(): void {
	$labels = [
		'name'               => __( 'Industry Pages', 'jcp-core' ),
		'singular_name'      => __( 'Industry Page', 'jcp-core' ),
		'menu_name'          => __( 'Industries', 'jcp-core' ),
		'add_new'            => __( 'Add Industry', 'jcp-core' ),
		'add_new_item'       => __( 'Add Industry Page', 'jcp-core' ),
		'edit_item'          => __( 'Edit Industry Page', 'jcp-core' ),
		'new_item'           => __( 'New Industry Page', 'jcp-core' ),
		'view_item'          => __( 'View Industry Page', 'jcp-core' ),
		'search_items'       => __( 'Search Industry Pages', 'jcp-core' ),
		'not_found'          => __( 'No industry pages found.', 'jcp-core' ),
		'not_found_in_trash' => __( 'No industry pages found in Trash.', 'jcp-core' ),
	];

	register_post_type(
		'jcp_niche_landing',
		[
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-building',
			'menu_position'       => 21,
			'has_archive'         => true,
			'rewrite'             => [
				'slug'       => 'industries',
				'with_front' => false,
			],
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
			'show_in_rest'        => true,
			'capability_type'     => 'page',
		]
	);
}
add_action( 'init', 'jcp_niche_register_post_type' );

/**
 * Meta key for JSON page content.
 */
function jcp_niche_content_meta_key(): string {
	return '_jcp_niche_content';
}

/**
 * Flush rewrite rules once after CPT registration.
 */
function jcp_niche_maybe_flush_rewrites(): void {
	if ( get_option( 'jcp_niche_landing_rewrite_flush' ) === '1' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'jcp_niche_landing_rewrite_flush', '1' );
}
add_action( 'init', 'jcp_niche_maybe_flush_rewrites', 99 );
