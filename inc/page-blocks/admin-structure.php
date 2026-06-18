<?php
/**
 * Admin panel: visual page block structure (syncs to JSON textarea).
 *
 * @package JCP_Core
 */

/**
 * Render structure panel (embedded in JCP Page Editor meta box).
 *
 * @param WP_Post $post Post.
 */
function jcp_page_block_structure_render_panel( WP_Post $post ): void {
	$page_kind = 'marketing';
	if ( $post->post_type === 'jcp_niche_landing' ) {
		$page_kind = 'industry';
	} elseif ( get_page_template_slug( $post->ID ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
		$page_kind = 'referral';
	} elseif ( get_page_template_slug( $post->ID ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
		$page_kind = 'home';
	}

	?>
	<div id="jcp-admin-block-structure" class="jcp-admin-block-structure" data-page-kind="<?php echo esc_attr( $page_kind ); ?>"></div>
	<noscript>
		<p><?php esc_html_e( 'Enable JavaScript to use the visual page structure editor.', 'jcp-core' ); ?></p>
	</noscript>
	<?php

	jcp_page_block_structure_enqueue_assets();
}

/**
 * Scripts/styles for the structure panel.
 */
function jcp_page_block_structure_enqueue_assets(): void {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	global $post;
	$page_kind = 'marketing';
	if ( $post instanceof WP_Post ) {
		if ( $post->post_type === 'jcp_niche_landing' ) {
			$page_kind = 'industry';
		} elseif ( get_page_template_slug( $post->ID ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
			$page_kind = 'referral';
		} elseif ( get_page_template_slug( $post->ID ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
			$page_kind = 'home';
		}
	}

	$registry = jcp_block_registry_public( $page_kind );
	$default_props = [];
	foreach ( $registry as $entry ) {
		$type = (string) ( $entry['type'] ?? '' );
		if ( $type !== '' ) {
			$default_props[ $type ] = jcp_page_default_block_props( $type );
		}
	}

	wp_enqueue_style(
		'jcp-admin-block-structure',
		get_template_directory_uri() . '/assets/css/admin/page-block-structure.css',
		[],
		function_exists( 'jcp_core_asset_version' ) ? jcp_core_asset_version( 'assets/css/admin/page-block-structure.css' ) : null
	);
	wp_enqueue_script(
		'jcp-admin-block-structure',
		get_template_directory_uri() . '/assets/js/admin/page-block-structure.js',
		[],
		function_exists( 'jcp_core_asset_version' ) ? jcp_core_asset_version( 'assets/js/admin/page-block-structure.js' ) : null,
		true
	);
	wp_localize_script(
		'jcp-admin-block-structure',
		'jcpAdminBlockStructure',
		[
			'registry'      => $registry,
			'defaultProps'  => $default_props,
			'textareaId'    => 'jcp_niche_content_json',
			'i18n'          => [
				'empty'       => __( 'No sections yet. Add a block below.', 'jcp-core' ),
				'add'         => __( '+ Add block', 'jcp-core' ),
				'remove'      => __( 'Remove', 'jcp-core' ),
				'removeConfirm' => __( 'Remove this section from the page?', 'jcp-core' ),
				'chooseType'  => __( 'Choose block type', 'jcp-core' ),
				'insert'      => __( 'Insert block', 'jcp-core' ),
				'cancel'      => __( 'Cancel', 'jcp-core' ),
				'syncError'   => __( 'Could not sync structure — check Advanced JSON for syntax errors.', 'jcp-core' ),
			],
		]
	);
}

/**
 * Enqueue structure script only when meta box is present.
 *
 * @param string $hook Admin hook.
 */
function jcp_page_block_structure_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, [ 'jcp_niche_landing', 'jcp_page', 'page' ], true ) ) {
		return;
	}
	// Script is enqueued from meta box render when the box is shown.
}
add_action( 'admin_enqueue_scripts', 'jcp_page_block_structure_admin_assets' );
