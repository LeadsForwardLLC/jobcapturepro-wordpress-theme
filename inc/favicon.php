<?php
/**
 * Brand favicon for all front-end pages (including demo survey/run shells).
 * Overrides WordPress default / Customizer site icon so /demo never shows the WP logo.
 *
 * @package JCP_Core
 */

/**
 * URL for the theme favicon asset.
 *
 * @return string
 */
function jcp_core_get_brand_favicon_url(): string {
	$url = jcp_core_asset_url( 'shared/assets/brand/favicon.svg' );
	$ver = jcp_core_asset_version( 'shared/assets/brand/favicon.svg' );
	if ( $ver ) {
		$url = add_query_arg( 'ver', $ver, $url );
	}
	return $url;
}

/**
 * Stop WordPress from outputting the default / Customizer site icon.
 *
 * @return void
 */
function jcp_core_remove_wp_default_site_icon(): void {
	remove_action( 'wp_head', 'wp_site_icon', 99 );
}

/**
 * Output JobCapturePro favicon tags.
 *
 * @return void
 */
function jcp_core_output_brand_favicon(): void {
	if ( is_admin() ) {
		return;
	}

	$icon_url = jcp_core_get_brand_favicon_url();

	echo '<link rel="icon" href="' . esc_url( $icon_url ) . '" type="image/svg+xml">' . "\n";
	echo '<link rel="shortcut icon" href="' . esc_url( $icon_url ) . '" type="image/svg+xml">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $icon_url ) . '">' . "\n";
	echo '<meta name="theme-color" content="#FF503E">' . "\n";
}

add_action( 'wp_head', 'jcp_core_remove_wp_default_site_icon', 0 );
add_action( 'wp_head', 'jcp_core_output_brand_favicon', 1 );
