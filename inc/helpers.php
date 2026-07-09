<?php
/**
 * Helper Functions
 * Asset path/URL/version utilities and template helpers
 *
 * @package JCP_Core
 */

/**
 * Get the full file path for a theme asset
 *
 * @param string $relative_path Path relative to theme root (e.g., 'assets/css/base.css')
 * @return string Full file path
 */
function jcp_core_asset_path( string $relative_path ): string {
    $clean = ltrim( $relative_path, '/' );
    $theme_path = trailingslashit( get_stylesheet_directory() ) . $clean;
    if ( file_exists( $theme_path ) ) {
        return $theme_path;
    }
    return trailingslashit( get_stylesheet_directory() ) . 'assets/' . $clean;
}

/**
 * Get the URL for a theme asset
 *
 * @param string $relative_path Path relative to theme root
 * @return string Asset URL
 */
function jcp_core_asset_url( string $relative_path ): string {
    $clean = ltrim( $relative_path, '/' );
    $theme_path = trailingslashit( get_stylesheet_directory() ) . $clean;
    if ( file_exists( $theme_path ) ) {
        return trailingslashit( get_stylesheet_directory_uri() ) . $clean;
    }
    return trailingslashit( get_stylesheet_directory_uri() ) . 'assets/' . $clean;
}

/**
 * Get the version (filemtime) for cache busting
 *
 * @param string $relative_path Path relative to theme root
 * @return string|null File modification time or null if file doesn't exist
 */
function jcp_core_asset_version( string $relative_path ): ?string {
    $path = jcp_core_asset_path( $relative_path );

    if ( file_exists( $path ) ) {
        return (string) filemtime( $path );
    }

    return null;
}

/**
 * Register a stylesheet with asset helpers
 *
 * @param string $handle Stylesheet handle
 * @param string $relative_path Relative path to CSS file
 * @param array  $deps Array of dependency handles
 * @return void
 */
function jcp_core_enqueue_style( string $handle, string $relative_path, array $deps = [] ): void {
    $path = jcp_core_asset_path( $relative_path );
    $url = jcp_core_asset_url( $relative_path );
    $version = filemtime( $path ) ? (string) filemtime( $path ) : null;
    
    // Always enqueue, let WordPress handle missing files
    wp_enqueue_style( $handle, $url, $deps, $version );
}

/**
 * Register a script with asset helpers
 *
 * @param string $handle Script handle
 * @param string $relative_path Relative path to JS file
 * @param array  $deps Array of dependency handles
 * @return void
 */
function jcp_core_enqueue_script( string $handle, string $relative_path, array $deps = [] ): void {
    $path = jcp_core_asset_path( $relative_path );
    $url = jcp_core_asset_url( $relative_path );
    $version = filemtime( $path ) ? (string) filemtime( $path ) : null;
    
    // Always enqueue, let WordPress handle missing files
    wp_enqueue_script( $handle, $url, $deps, $version, true );
}

/**
 * Get a Lucide icon SVG URL
 *
 * @param string $icon_name Icon name without extension (e.g., 'camera', 'map-pin')
 * @return string Icon SVG URL
 */
function jcp_core_icon( string $icon_name ): string {
    return jcp_core_asset_url( 'shared/assets/icons/lucide/' . $icon_name . '.svg' );
}

/**
 * Canonical URL for launching the interactive demo (?mode=run).
 *
 * @param array<string, string> $args Optional query arguments.
 * @return string
 */
function jcp_core_demo_run_url( array $args = [] ): string {
    $url = home_url( '/demo/' );
    if ( $args !== [] ) {
        $url = add_query_arg( $args, $url );
    }
    return $url;
}

/**
 * Sanitized query args allowed on demo run URLs.
 *
 * @return array<string, string>
 */
function jcp_core_demo_run_query_args(): array {
    $allowed = [ 'mode', 'name', 'first_name', 'last_name', 'business', 'company', 'niche', 'business_type', 'email', 'forceSurvey' ];
    $out     = [ 'mode' => 'run' ];
    foreach ( $allowed as $key ) {
        if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            continue;
        }
        $val = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_string( $val ) && $val !== '' ) {
            $out[ $key ] = sanitize_text_field( $val );
        }
    }
    return $out;
}

/**
 * Whether the current request is already on a valid demo run route.
 *
 * @return bool
 */
function jcp_core_is_demo_run_request(): bool {
    if ( ! isset( $_GET['mode'] ) || $_GET['mode'] !== 'run' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return false;
    }
    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    if ( $path === 'demo' ) {
        return true;
    }
    return is_page_template( 'page-demo.php' );
}

/**
 * Whether the current request is the demo survey (not ?mode=run).
 *
 * @return bool
 */
function jcp_core_is_demo_survey_request(): bool {
    if ( isset( $_GET['mode'] ) && $_GET['mode'] === 'run' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return false;
    }
    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    if ( $path === 'demo' ) {
        return true;
    }
    return is_page_template( 'page-demo.php' );
}

/**
 * Get page detection for conditional enqueuing
 *
 * @return array Associative array of page booleans
 */
function jcp_core_get_page_detection(): array {
    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    $path_segments = array_filter( explode( '/', $path ) );
    $is_prototype_path = ( $path === 'prototype' || in_array( 'prototype', $path_segments, true ) );

    return [
        'is_home'         => is_front_page() || $path === '' || $path === 'home',
        'is_prototype'    => is_page_template( 'page-prototype.php' ) || is_page( 'prototype' ) || $is_prototype_path,
        'is_demo'         => is_page_template( 'page-demo.php' ) || is_page( 'demo' ) || $path === 'demo' || get_query_var( 'jcp_route', '' ) === 'demo',
        'is_pricing'      => is_page_template( 'page-pricing.php' ) || is_page( 'pricing' ) || $path === 'pricing',
        'is_contact'      => is_page_template( 'page-contact.php' ) || is_page( 'contact' ) || $path === 'contact',
        'is_contact_success' => $path === 'contact-success',
        'is_directory'    => is_page_template( 'page-directory.php' ) || is_page( 'directory' ) || $path === 'directory',
        'is_estimate'     => is_page_template( 'page-estimate.php' ) || is_page( 'estimate' ) || $path === 'estimate',
        'is_company'      => is_singular( 'jcp_company' ) || is_page( 'company' ) || $path === 'company' || ( preg_match( '#^directory/[^/]+$#', $path ) === 1 ),
        'is_ui_library'   => is_page_template( 'page-ui-library.php' ) || is_page( 'ui-library' ) || $path === 'ui-library',
        'is_wp_plugin_prototype' => is_page_template( 'page-wp-plugin-prototype.php' ) || is_page( 'wp-plugin-prototype' ) || $path === 'wp-plugin-prototype',
        'is_blog'         => is_home() || is_archive() || is_single() || is_search(),
        'is_single'       => is_single() && ! is_singular( 'jcp_company' ),
        'is_page'         => is_page() && ! is_page_template(),
        'is_niche_landing' => is_singular( 'jcp_niche_landing' )
            || is_post_type_archive( 'jcp_niche_landing' )
            || ( is_singular( 'page' ) && function_exists( 'jcp_page_uses_block_template' ) && jcp_page_uses_block_template( (int) get_queried_object_id() ) )
            || ( is_front_page() && function_exists( 'jcp_page_is_content_page' ) && ( (int) get_option( 'page_on_front' ) ) > 0 && jcp_page_is_content_page( (int) get_option( 'page_on_front' ) ) ),
    ];
}

/**
 * Whether the current request is the Help articles CPT archive (/help/).
 * Supports post type names "help_article", "help", or rewrite slug "help".
 *
 * @return bool
 */
function jcp_core_is_help_archive(): bool {
    if ( ! is_post_type_archive() ) {
        return false;
    }
    $pt = get_queried_object();
    if ( ! $pt instanceof WP_Post_Type ) {
        return false;
    }
    $slug = isset( $pt->rewrite['slug'] ) ? $pt->rewrite['slug'] : $pt->name;
    return $pt->name === 'help_article' || $pt->name === 'help' || $slug === 'help';
}

/**
 * Whether the current request is the Help Articles page (Page with template or slug "help").
 * Used when /help/ is a WordPress Page; assign template "Help Articles" so search/filter connect to the CPT.
 *
 * @return bool
 */
function jcp_core_is_help_page(): bool {
    if ( ! is_page() ) {
        return false;
    }
    $template = get_page_template_slug();
    if ( $template === 'page-help.php' ) {
        return true;
    }
    $post = get_queried_object();
    return $post instanceof WP_Post && $post->post_name === 'help';
}

/**
 * Whether the current request is in Directory Mode (marketplace context).
 * True on /directory, /directory/*, and contractor profile (/company).
 * Used by the global header to switch nav links and CTAs.
 *
 * @return bool
 */
function jcp_is_directory_mode(): bool {
    $pages = jcp_core_get_page_detection();
    if ( $pages['is_directory'] || $pages['is_company'] ) {
        return true;
    }
    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    return strpos( $path, 'directory/' ) === 0;
}

/**
 * Add noindex/nofollow to UI library page
 *
 * The UI library page is internal documentation and should not be indexed
 * by search engines or publicly linked.
 *
 * @return void
 */
function jcp_core_design_system_noindex(): void {
    $pages = jcp_core_get_page_detection();
    if ( $pages['is_ui_library'] ) {
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
}

add_action( 'wp_head', 'jcp_core_design_system_noindex' );

/**
 * Post ID for the current block page editor context (0 if none).
 */
function jcp_core_get_page_editor_post_id(): int {
	if ( ! is_user_logged_in() || ! function_exists( 'jcp_page_is_content_page' ) ) {
		return 0;
	}
	$pid = is_singular() ? (int) get_queried_object_id() : 0;
	if ( $pid > 0 && jcp_page_is_content_page( $pid ) ) {
		return $pid;
	}
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id > 0 && is_front_page() && jcp_page_is_content_page( $front_id ) ) {
		return $front_id;
	}
	return 0;
}

/**
 * Enqueue front-end page block editor (toolbar, structure panel, inline edit).
 *
 * @param int $post_id Structured content post ID.
 */
function jcp_core_enqueue_page_block_editor( int $post_id ): void {
	static $enqueued = false;
	if ( $enqueued || $post_id <= 0 || ! function_exists( 'jcp_page_is_content_page' ) ) {
		return;
	}
	if ( ! jcp_page_is_content_page( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$enqueued = true;

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$is_home = (int) get_option( 'page_on_front' ) === $post_id;
	if ( $is_home || ! wp_style_is( 'jcp-core-niche-landing', 'enqueued' ) ) {
		jcp_core_enqueue_style( 'jcp-core-niche-landing', 'css/pages/niche-landing.css', [ 'jcp-core-sections' ] );
	}

	wp_enqueue_media();
	jcp_core_enqueue_script( 'jcp-page-media-editor', 'js/pages/page-media-editor.js', [ 'media-models', 'media-views' ] );
	jcp_core_enqueue_script( 'jcp-page-collection-editor', 'js/pages/page-collection-editor.js', [ 'jcp-page-media-editor' ] );
	jcp_core_enqueue_script( 'jcp-niche-page-editor', 'js/pages/niche-page-editor.js', [ 'jcp-page-media-editor', 'jcp-page-collection-editor' ] );
	$page_doc  = jcp_page_get_content( $post_id );
	$page_kind = jcp_page_resolve_kind( $page_doc, $post_id );
	wp_localize_script(
		'jcp-niche-page-editor',
		'JCP_NICHE_EDITOR',
		[
			'postId'    => $post_id,
			'restUrl'   => rest_url( 'jcp/v1/page/' . $post_id ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'adminUrl'  => get_edit_post_link( $post_id, 'raw' ),
			'url'       => get_permalink( $post_id ),
			'bootstrap' => [
				'blocks'       => $page_doc,
				'content'      => jcp_page_get_content_flat( $post_id ),
				'registry'     => jcp_block_registry_public( $page_kind ),
				'pageKind'     => $page_kind,
				'linkIndex'    => function_exists( 'jcp_internal_link_editor_payload' )
					? jcp_internal_link_editor_payload( $post_id )
					: [ 'pages' => [], 'current_path' => '' ],
			],
			'strings'   => [
				'mediaTitle'  => __( 'Choose or upload media', 'jcp-core' ),
				'mediaButton' => __( 'Use this media', 'jcp-core' ),
			],
		]
	);
}

