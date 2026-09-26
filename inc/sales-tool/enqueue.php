<?php
/**
 * Sales tool asset enqueue.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue sales tool assets when on a sales surface.
 */
function jcp_sales_tool_enqueue_assets(): void {
	if ( ! function_exists( 'jcp_is_sales_tool_request' ) || ! jcp_is_sales_tool_request() ) {
		return;
	}

	$post_id = get_queried_object_id();
	$config  = jcp_sales_tool_build_config( (int) $post_id );

	jcp_core_enqueue_style( 'jcp-core-base', 'css/base.css' );
	jcp_core_enqueue_style( 'jcp-sales-tool', 'assets/jcp-sales-tool/styles.css', [ 'jcp-core-base' ] );
	jcp_core_enqueue_script( 'jcp-sales-tool', 'assets/jcp-sales-tool/app.js', [] );

	wp_localize_script( 'jcp-sales-tool', 'JCP_SALES_TOOL', $config );
}
add_action( 'wp_enqueue_scripts', 'jcp_sales_tool_enqueue_assets', 5 );

/**
 * Strip conflicting theme chrome scripts on sales tool pages.
 */
function jcp_sales_tool_dequeue_chrome(): void {
	if ( ! function_exists( 'jcp_is_sales_tool_request' ) || ! jcp_is_sales_tool_request() ) {
		return;
	}
	wp_dequeue_script( 'jcp-core-nav' );
	wp_dequeue_script( 'jcp-core-site-banner' );
	wp_dequeue_script( 'jcp-core-render' );
}
add_action( 'wp_enqueue_scripts', 'jcp_sales_tool_dequeue_chrome', 1000 );
