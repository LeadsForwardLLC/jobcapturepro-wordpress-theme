<?php
/**
 * Migrate /contact → /support (slug, title, template) + 301 redirect.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time: rename Contact page to Support at /support/.
 */
function jcp_migrate_contact_page_to_support(): void {
	if ( get_option( 'jcp_support_page_migrated', '' ) === '1' ) {
		return;
	}

	$support = get_page_by_path( 'support', OBJECT, 'page' );
	$contact = get_page_by_path( 'contact', OBJECT, 'page' );

	if ( $support instanceof WP_Post ) {
		wp_update_post(
			[
				'ID'         => (int) $support->ID,
				'post_title' => 'Support',
			]
		);
		update_post_meta( (int) $support->ID, '_wp_page_template', 'page-support.php' );
		if ( $contact instanceof WP_Post && (int) $contact->ID !== (int) $support->ID ) {
			// Leave old contact page as a redirect target handled in template_redirect.
		}
		update_option( 'jcp_support_page_migrated', '1' );
		return;
	}

	if ( ! $contact instanceof WP_Post ) {
		$id = wp_insert_post(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => 'support',
				'post_title'   => 'Support',
				'post_content' => '',
			],
			true
		);
		if ( ! is_wp_error( $id ) && $id ) {
			update_post_meta( (int) $id, '_wp_page_template', 'page-support.php' );
			update_option( 'jcp_support_page_migrated', '1' );
		}
		return;
	}

	$result = wp_update_post(
		[
			'ID'         => (int) $contact->ID,
			'post_name'  => 'support',
			'post_title' => 'Support',
		],
		true
	);
	if ( ! is_wp_error( $result ) && $result ) {
		update_post_meta( (int) $contact->ID, '_wp_page_template', 'page-support.php' );
		update_option( 'jcp_support_page_migrated', '1' );
	}
}
add_action( 'init', 'jcp_migrate_contact_page_to_support', 30 );

/**
 * 301 /contact/ → /support/ (and bare /contact).
 */
function jcp_redirect_contact_to_support(): void {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	$path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $path !== 'contact' ) {
		return;
	}
	$target = home_url( '/support/' );
	$query  = (string) ( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY ) ?? '' );
	if ( $query !== '' ) {
		$target .= ( str_contains( $target, '?' ) ? '&' : '?' ) . $query;
	}
	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'jcp_redirect_contact_to_support', 1 );
