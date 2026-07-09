<?php
/**
 * Page editor defaults: meta box order and hidden panels.
 *
 * @package JCP_Core
 */

/**
 * Post types that use the JCP page editor workflow.
 *
 * @return array<int, string>
 */
function jcp_admin_page_editor_post_types(): array {
	return [ 'page', 'jcp_niche_landing' ];
}

/**
 * Meta boxes hidden by default on page edit screens.
 *
 * @return array<int, string>
 */
function jcp_admin_default_hidden_page_metabox_ids(): array {
	return [ 'commentsdiv', 'wpcode-metabox-snippets' ];
}

/**
 * Hide Comments and WPCode Page Scripts until enabled in Screen Options.
 *
 * @param array<int, string> $hidden Hidden meta box IDs.
 * @param WP_Screen          $screen Current screen.
 * @return array<int, string>
 */
function jcp_admin_default_hidden_page_metaboxes( array $hidden, WP_Screen $screen ): array {
	if ( ! in_array( $screen->post_type, jcp_admin_page_editor_post_types(), true ) ) {
		return $hidden;
	}

	foreach ( jcp_admin_default_hidden_page_metabox_ids() as $id ) {
		if ( ! in_array( $id, $hidden, true ) ) {
			$hidden[] = $id;
		}
	}

	return $hidden;
}
add_filter( 'default_hidden_meta_boxes', 'jcp_admin_default_hidden_page_metaboxes', 10, 2 );

/**
 * Apply hidden meta box defaults for users who have not saved Screen Options yet.
 *
 * @param mixed $hidden User meta value.
 * @return mixed
 */
function jcp_admin_get_metaboxhidden_page( $hidden ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, jcp_admin_page_editor_post_types(), true ) ) {
		return $hidden;
	}

	if ( $hidden !== false ) {
		return $hidden;
	}

	return jcp_admin_default_hidden_page_metabox_ids();
}
foreach ( jcp_admin_page_editor_post_types() as $post_type ) {
	add_filter( 'get_user_option_metaboxhidden_' . $post_type, 'jcp_admin_get_metaboxhidden_page' );
}

/**
 * Preferred normal-context meta box order for new users.
 */
function jcp_admin_default_page_meta_box_order(): string {
	return 'normal=jcp_page_editor,jcp_seo_health,rank_math_metabox,jcp_page_advanced';
}

/**
 * Ensure SEO Health sits directly above Rank Math SEO.
 *
 * @param string $order Serialized meta box order.
 * @return string
 */
function jcp_admin_fix_seo_rank_math_meta_box_order( string $order ): string {
	if ( ! preg_match( '/normal=([^&]*)/', $order, $matches ) ) {
		return $order;
	}

	$ids = array_values( array_filter( explode( ',', (string) $matches[1] ) ) );
	if ( ! in_array( 'jcp_seo_health', $ids, true ) || ! in_array( 'rank_math_metabox', $ids, true ) ) {
		return $order;
	}

	$without = array_values(
		array_filter(
			$ids,
			static fn( string $id ): bool => ! in_array( $id, [ 'jcp_seo_health', 'rank_math_metabox' ], true )
		)
	);

	$insert_at = 0;
	foreach ( $ids as $id ) {
		if ( $id === 'rank_math_metabox' ) {
			break;
		}
		if ( ! in_array( $id, [ 'jcp_seo_health', 'rank_math_metabox' ], true ) ) {
			++$insert_at;
		}
	}

	array_splice( $without, $insert_at, 0, [ 'jcp_seo_health', 'rank_math_metabox' ] );

	return (string) preg_replace( '/normal=[^&]*/', 'normal=' . implode( ',', $without ), $order, 1 );
}

/**
 * Default meta box order when the user has not customized layout yet.
 *
 * @param mixed $order User meta value.
 * @return mixed
 */
function jcp_admin_get_page_meta_box_order( $order ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, jcp_admin_page_editor_post_types(), true ) ) {
		return $order;
	}

	if ( $order === false || $order === '' || $order === null ) {
		return jcp_admin_default_page_meta_box_order();
	}

	if ( ! is_string( $order ) ) {
		return $order;
	}

	return jcp_admin_fix_seo_rank_math_meta_box_order( $order );
}
foreach ( jcp_admin_page_editor_post_types() as $post_type ) {
	add_filter( 'get_user_option_meta-box-order_' . $post_type, 'jcp_admin_get_page_meta_box_order' );
}

/**
 * Move SEO Health to sit directly above Rank Math when meta boxes register.
 */
function jcp_admin_reorder_page_meta_boxes(): void {
	global $wp_meta_boxes, $post;

	if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, jcp_admin_page_editor_post_types(), true ) ) {
		return;
	}

	$screen  = $post->post_type;
	$context = 'normal';

	if ( empty( $wp_meta_boxes[ $screen ][ $context ] ) || ! is_array( $wp_meta_boxes[ $screen ][ $context ] ) ) {
		return;
	}

	$seo_box = null;
	$rm_id   = 'rank_math_metabox';
	$rm_prio = null;

	foreach ( $wp_meta_boxes[ $screen ][ $context ] as $priority => $boxes ) {
		if ( ! is_array( $boxes ) ) {
			continue;
		}
		if ( isset( $boxes['jcp_seo_health'] ) ) {
			$seo_box = $boxes['jcp_seo_health'];
			unset( $wp_meta_boxes[ $screen ][ $context ][ $priority ]['jcp_seo_health'] );
		}
		if ( isset( $boxes[ $rm_id ] ) ) {
			$rm_prio = $priority;
		}
	}

	if ( ! $seo_box || ! $rm_prio || empty( $wp_meta_boxes[ $screen ][ $context ][ $rm_prio ] ) ) {
		return;
	}

	$boxes = $wp_meta_boxes[ $screen ][ $context ][ $rm_prio ];
	$new   = [];

	foreach ( $boxes as $id => $box ) {
		if ( $id === $rm_id ) {
			$new['jcp_seo_health'] = $seo_box;
		}
		$new[ $id ] = $box;
	}

	$wp_meta_boxes[ $screen ][ $context ][ $rm_prio ] = $new;
}
add_action( 'add_meta_boxes', 'jcp_admin_reorder_page_meta_boxes', 9999 );
