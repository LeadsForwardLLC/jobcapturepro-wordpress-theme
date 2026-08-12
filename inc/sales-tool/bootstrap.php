<?php
/**
 * Sales tool bootstrap.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/cpt.php';
require_once __DIR__ . '/meta.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/enqueue.php';

/**
 * Flush rewrites once after sales deck CPT is introduced.
 */
function jcp_sales_tool_maybe_flush_rewrites(): void {
	if ( get_option( 'jcp_sales_deck_flush' ) === '1' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'jcp_sales_deck_flush', '1' );
}
add_action( 'init', 'jcp_sales_tool_maybe_flush_rewrites', 99 );
