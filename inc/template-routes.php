<?php
/**
 * Template Routing & Fallbacks
 * Handle SPA-style routing and fallback templates.
 *
 * DIAGNOSIS (directory / profile rendering):
 * - /directory: Served by 404 fallback (path 'directory') → page-directory.php.
 *   Template uses get_header(), <div id="jcp-app" data-jcp-page="directory">, get_footer().
 *   JS (jcp-render.js) fetches assets/directory/index.html and injects into #jcp-app.
 *   Data: JCP_DIRECTORY_DATA from inc/enqueue.php (get_posts jcp_company + demo seed).
 * - /company and /company?id=xxx: 404 fallback (path 'company') → single-jcp_company.php.
 *   Same shell; JS loads assets/directory/profile.html. Data: JCP_PROFILE_DATA when real
 *   jcp_company post; demo IDs use ?id= (no WP post). jcp_company CPT used by theme;
 *   registration not in theme/jobcapturepro-plugin (may be ACF/mu-plugins).
 * - Controlling files: inc/template-routes.php (routing), page-directory.php,
 *   single-jcp_company.php, inc/enqueue.php, assets/js/core/jcp-render.js.
 * - No plugin template override; theme owns directory/profile rendering.
 *
 * Directory, company, and demo use rewrite rules + template_include so they are served
 * as 200 with correct title (Rank Math compatible) without going through 404.
 *
 * @package JCP_Core
 */

/**
 * Register query var and rewrite rules for directory and company (WordPress-native routing).
 *
 * @return void
 */
function jcp_core_register_directory_routes(): void {
    add_rewrite_rule( '^directory/?$', 'index.php?jcp_route=directory', 'top' );
    add_rewrite_rule( '^directory/([^/]+)/?$', 'index.php?jcp_route=company&jcp_company_slug=$matches[1]', 'top' );
    add_rewrite_rule( '^company/?$', 'index.php?jcp_route=company', 'top' );
}

/**
 * Register jcp_route and jcp_company_slug query vars.
 *
 * @param array $vars Existing query vars.
 * @return array
 */
function jcp_core_register_route_query_var( array $vars ): array {
    $vars[] = 'jcp_route';
    $vars[] = 'jcp_company_slug';
    return $vars;
}

/**
 * Serve directory/company with 200 and correct title (no 404).
 * Sets document title and Rank Math title/canonical so Directory never shows Blog title.
 *
 * @return void
 */
function jcp_core_directory_route_template_redirect(): void {
    $route = get_query_var( 'jcp_route', '' );

    if ( $route === 'demo' ) {
        global $wp_query;
        $wp_query->is_404 = false;
        status_header( 200 );
        jcp_core_prime_demo_page_query();
        return;
    }

    if ( $route !== 'directory' && $route !== 'company' ) {
        return;
    }

    global $wp_query;
    $wp_query->is_404 = false;
    status_header( 200 );

    $titles = [
        'directory' => __( 'Directory', 'jcp-core' ),
        'company'   => __( 'Company Profile', 'jcp-core' ),
    ];
    if ( isset( $titles[ $route ] ) ) {
        $page_title = $titles[ $route ];
        add_filter(
            'document_title_parts',
            function ( $parts ) use ( $page_title ) {
                $parts['title'] = $page_title;
                return $parts;
            },
            999,
            1
        );
        // Rank Math outputs its own title/canonical from the main query (often blog). Override for directory/company.
        add_filter(
            'rank_math/frontend/title',
            function ( $title ) use ( $page_title ) {
                return $page_title . ' - ' . get_bloginfo( 'name' );
            },
            10,
            1
        );
        add_filter(
            'rank_math/frontend/canonical',
            function ( $canonical ) use ( $route ) {
                if ( $route === 'directory' ) {
                    return home_url( '/directory/' );
                }
                if ( $route === 'company' ) {
                    $slug = get_query_var( 'jcp_company_slug', '' );
                    if ( $slug !== '' ) {
                        return home_url( '/directory/' . $slug . '/' );
                    }
                    if ( isset( $_GET['id'] ) && is_string( $_GET['id'] ) ) {
                        $id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
                        if ( $id !== '' ) {
                            return home_url( '/directory/' . $id . '/' );
                        }
                    }
                    return home_url( '/company/' );
                }
                return $canonical;
            },
            10,
            1
        );
    }
}

/**
 * Load directory or company template when jcp_route is set.
 *
 * @param string $template Current template path.
 * @return string
 */
function jcp_core_directory_route_template_include( string $template ): string {
    $route = get_query_var( 'jcp_route', '' );
    if ( $route === 'demo' ) {
        $path = get_stylesheet_directory() . '/page-demo.php';
        if ( file_exists( $path ) ) {
            return $path;
        }
    }
    if ( $route === 'directory' ) {
        $path = get_stylesheet_directory() . '/page-directory.php';
        if ( file_exists( $path ) ) {
            return $path;
        }
    }
    if ( $route === 'company' ) {
        $path = get_stylesheet_directory() . '/single-jcp_company.php';
        if ( file_exists( $path ) ) {
            return $path;
        }
    }
    return $template;
}

add_action( 'init', 'jcp_core_register_directory_routes' );
add_filter( 'query_vars', 'jcp_core_register_route_query_var' );
add_action( 'template_redirect', 'jcp_core_directory_route_template_redirect' );
add_filter( 'template_include', 'jcp_core_directory_route_template_include', 5 );

/**
 * Force prototype templates by route path.
 *
 * This protects live environments where the WP page/template assignment for
 * /prototype or /wp-plugin-prototype may be missing or incorrect.
 *
 * @param string $template Current template path.
 * @return string
 */
function jcp_core_force_prototype_templates( string $template ): string {
    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    $segment = strpos( $path, '/' ) !== false ? strtok( $path, '/' ) : $path;

    if ( $segment === 'prototype' ) {
        $forced = get_stylesheet_directory() . '/page-prototype.php';
        if ( file_exists( $forced ) ) {
            return $forced;
        }
    }

    if ( $segment === 'wp-plugin-prototype' ) {
        $forced = get_stylesheet_directory() . '/page-wp-plugin-prototype.php';
        if ( file_exists( $forced ) ) {
            return $forced;
        }
    }

    return $template;
}
add_filter( 'template_include', 'jcp_core_force_prototype_templates', 4 );

/**
 * Flush rewrite rules on theme switch so directory/company rules take effect.
 *
 * @return void
 */
function jcp_core_flush_rewrite_rules_on_switch(): void {
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'jcp_core_flush_rewrite_rules_on_switch' );

/**
 * Redirect retired marketing routes.
 *
 * @return void
 */
function jcp_core_redirect_retired_routes(): void {
	if ( is_admin() ) {
		return;
	}

	$path    = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	$segment = strpos( $path, '/' ) !== false ? strtok( $path, '/' ) : $path;

	$redirects = [
		'early-access'         => home_url( '/demo/' ),
		'early-access-success' => home_url( '/demo/' ),
	];

	if ( ! isset( $redirects[ $segment ] ) ) {
		return;
	}

	wp_safe_redirect( $redirects[ $segment ], 301 );
	exit;
}
add_action( 'template_redirect', 'jcp_core_redirect_retired_routes', 1 );

/**
 * Keep /demo/?mode=run on the demo route (avoid canonical redirect to unrelated permalinks).
 *
 * @param string|false $redirect_url  Canonical redirect URL.
 * @param string       $requested_url Requested URL.
 * @return string|false
 */
function jcp_core_prevent_demo_run_canonical_redirect( $redirect_url, $requested_url ) {
    if ( ! isset( $_GET['mode'] ) || $_GET['mode'] !== 'run' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return $redirect_url;
    }
    $path = trim( (string) parse_url( $requested_url, PHP_URL_PATH ), '/' );
    if ( $path === 'demo' || str_ends_with( $path, '/demo' ) ) {
        return false;
    }
    return $redirect_url;
}
add_filter( 'redirect_canonical', 'jcp_core_prevent_demo_run_canonical_redirect', 10, 2 );

/**
 * If ?mode=run lands on a non-demo permalink, send to /demo/ with the same params.
 *
 * @return void
 */
function jcp_core_redirect_stray_demo_run_requests(): void {
    if ( is_admin() || jcp_core_is_demo_run_request() ) {
        return;
    }
    if ( ! isset( $_GET['mode'] ) || $_GET['mode'] !== 'run' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }
    wp_safe_redirect( jcp_core_demo_run_url( jcp_core_demo_run_query_args() ), 302 );
    exit;
}
add_action( 'template_redirect', 'jcp_core_redirect_stray_demo_run_requests', 2 );

/**
 * Keep /demo/ survey HTML out of full-page cache so deploys show immediately.
 *
 * @return void
 */
function jcp_core_bypass_demo_survey_page_cache(): void {
    if ( ! jcp_core_is_demo_survey_request() && ! jcp_core_is_demo_run_request() ) {
        return;
    }
    if ( ! defined( 'DONOTCACHEPAGE' ) ) {
        define( 'DONOTCACHEPAGE', true );
    }
    nocache_headers();
}
add_action( 'template_redirect', 'jcp_core_bypass_demo_survey_page_cache', 0 );

/**
 * Critical CSS for mobile demo run — applies before JS so no flash of old chrome.
 *
 * @return void
 */
function jcp_core_demo_run_critical_css(): void {
    if ( ! jcp_core_is_demo_run_request() ) {
        return;
    }
    echo '<style id="jcp-demo-run-critical">';
    echo 'body.jcp-demo-run #tour-float,body.jcp-demo-run #tour-bubble,body.jcp-demo-run .tour-dock{display:none!important}';
    echo 'body.jcp-demo-run,body.jcp-demo-run #jcp-app{min-height:100dvh;background:#fff;margin:0;padding:0}';
    echo '@media(max-width:1024px){html.jcp-demo-run-mobile,html.jcp-demo-run-mobile body.jcp-demo-run{height:100%;overflow:hidden;overscroll-behavior:none}body.jcp-demo-run #jcp-app{height:100%;min-height:0;max-height:100dvh;overflow:hidden}}';
    echo 'body.jcp-demo-run.jcp-desktop-guided,body.jcp-demo-run.jcp-desktop-guided #jcp-app{background:#f3f4f6}';
    echo '@media(max-width:1024px){';
    echo 'body.jcp-demo-run .right-panel{display:none!important}';
    echo '}';
    echo '</style>';
}
add_action( 'wp_head', 'jcp_core_demo_run_critical_css', 1 );

/**
 * Fallback template routing for non-WordPress pages
 * Allows /demo, /pricing, etc. to render even if pages don't exist in WordPress.
 * Directory and company are handled by rewrite + template_include above (not 404).
 *
 * @return void
 */
function jcp_core_fallback_template_routes(): void {
    if ( ! is_404() ) {
        return;
    }

    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    $path_segment = strpos( $path, '/' ) !== false ? strtok( $path, '/' ) : $path;

    $template_map = [
        'demo'              => 'page-demo.php',
        'pricing'           => 'page-pricing.php',
        'contact'           => 'page-contact.php',
        'contact-success'   => 'page-contact-success.php',
        'estimate'          => 'page-estimate.php',
        'ui-library'        => 'page-ui-library.php',
    ];
    if ( $path_segment === 'directory' || $path_segment === 'company' ) {
        return;
    }

    if ( ! isset( $template_map[ $path_segment ] ) ) {
        return;
    }

    $template_path = trailingslashit( get_stylesheet_directory() ) . $template_map[ $path_segment ];
    if ( ! file_exists( $template_path ) ) {
        return;
    }

    global $wp_query;
    $wp_query->is_404 = false;
    status_header( 200 );

    if ( $path_segment === 'demo' ) {
        jcp_core_prime_demo_page_query();
    }

    $route_titles = [
        'pricing'         => __( 'Pricing', 'jcp-core' ),
        'contact'         => __( 'Contact', 'jcp-core' ),
        'contact-success' => __( 'Message sent', 'jcp-core' ),
        'estimate'        => __( 'Estimate', 'jcp-core' ),
        'ui-library'      => __( 'UI Library', 'jcp-core' ),
    ];
    if ( isset( $route_titles[ $path_segment ] ) ) {
        add_filter(
            'document_title_parts',
            function ( $parts ) use ( $route_titles, $path_segment ) {
                $parts['title'] = $route_titles[ $path_segment ];
                return $parts;
            },
            999,
            1
        );
    }

    include $template_path;
    exit;
}

add_action( 'template_redirect', 'jcp_core_fallback_template_routes' );
