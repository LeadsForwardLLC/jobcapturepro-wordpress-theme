<?php
/**
 * Asset Enqueuing
 * Conditional CSS/JS loading per page
 *
 * @package JCP_Core
 */

/**
 * Enqueue CSS and JS based on current page
 *
 * @return void
 */
function jcp_core_enqueue_assets(): void {
    $pages = jcp_core_get_page_detection();
    $render_handle = 'jcp-core-render';
    $render_deps = [];

    // Preload LCP image to improve LCP (exclude from lazy-load in WP Rocket)
    if ( $pages['is_home'] ) {
        $lcp_url = esc_url( 'https://jobcapturepro.com/wp-content/uploads/2025/12/jcp-user-photo.jpg' );
        add_action( 'wp_head', function () use ( $lcp_url ) {
            echo '<link rel="preload" href="' . $lcp_url . '" as="image">' . "\n";
        }, 1 );
    }
    if ( $pages['is_directory'] ) {
        $lcp_url = esc_url( 'https://jobcapturepro.com/wp-content/uploads/2025/11/confident-foreman.jpg' );
        add_action( 'wp_head', function () use ( $lcp_url ) {
            echo '<link rel="preload" href="' . $lcp_url . '" as="image">' . "\n";
        }, 1 );
    }

    $is_marketing = $pages['is_home'] || $pages['is_pricing'] || $pages['is_contact'] || ! empty( $pages['is_niche_landing'] );

    // Always load navigation JS (skip on prototype - no header/footer)
    if ( ! $pages['is_prototype'] ) {
        jcp_core_enqueue_script( 'jcp-core-nav', 'js/core/jcp-nav.js' );
        // Early bird banner dismiss behavior (no-op if banner not present).
        jcp_core_enqueue_script( 'jcp-core-earlybird-banner', 'js/core/jcp-earlybird-banner.js', [ 'jcp-core-nav' ] );
    }

    // UI Library page (internal documentation - shows all components)
    if ( $pages['is_ui_library'] ) {
        jcp_core_enqueue_style( 'jcp-core-base', 'css/base.css' );
        jcp_core_enqueue_style( 'jcp-core-layout', 'css/layout.css', [ 'jcp-core-base' ] );
        jcp_core_enqueue_style( 'jcp-core-buttons', 'css/buttons.css', [ 'jcp-core-layout' ] );
        jcp_core_enqueue_style( 'jcp-core-components', 'css/components.css', [ 'jcp-core-buttons' ] );
        jcp_core_enqueue_style( 'jcp-core-utilities', 'css/utilities.css', [ 'jcp-core-components' ] );
        jcp_core_enqueue_style( 'jcp-core-sections', 'css/sections.css', [ 'jcp-core-components' ] );
        jcp_core_enqueue_style( 'jcp-core-hero-live-demo', 'css/components/hero-live-demo.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_style( 'jcp-core-demo-app-phone', 'css/components/demo-app-phone.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_style( 'jcp-core-home', 'css/pages/home.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_style( 'jcp-core-blog', 'css/pages/blog.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_style( 'jcp-core-pricing', 'css/pages/pricing.css', [ 'jcp-core-sections' ] );
        return;
    }

    // Load base CSS with all design system variables on all pages
    jcp_core_enqueue_style( 'jcp-core-base', 'css/base.css' );

    // Marketing pages: full design system
    if ( $is_marketing ) {
        jcp_core_enqueue_style( 'jcp-core-layout', 'css/layout.css', [ 'jcp-core-base' ] );
        jcp_core_enqueue_style( 'jcp-core-buttons', 'css/buttons.css', [ 'jcp-core-layout' ] );
        jcp_core_enqueue_style( 'jcp-core-components', 'css/components.css', [ 'jcp-core-buttons' ] );
        jcp_core_enqueue_style( 'jcp-core-utilities', 'css/utilities.css', [ 'jcp-core-components' ] );
        // Shared section styles (FAQ, Final CTA, etc.) for all marketing pages
        jcp_core_enqueue_style( 'jcp-core-sections', 'css/sections.css', [ 'jcp-core-components' ] );
        jcp_core_enqueue_style( 'jcp-core-hero-live-demo', 'css/components/hero-live-demo.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_style( 'jcp-core-demo-app-phone', 'css/components/demo-app-phone.css', [ 'jcp-core-sections' ] );
    } else {
        // Other pages: include layout so .jcp-container works (page.php, blog, single)
        jcp_core_enqueue_style( 'jcp-core-layout', 'css/layout.css', [ 'jcp-core-base' ] );
        jcp_core_enqueue_style( 'jcp-core-buttons', 'css/buttons.css', [ 'jcp-core-layout' ] );
        jcp_core_enqueue_style( 'jcp-core-components', 'css/components.css', [ 'jcp-core-buttons' ] );
        jcp_core_enqueue_style( 'jcp-core-utilities', 'css/utilities.css', [ 'jcp-core-components' ] );
    }

    // Page-specific assets
    if ( $pages['is_home'] ) {
        jcp_core_enqueue_style( 'jcp-core-home', 'css/pages/home.css', [ 'jcp-core-sections' ] );
        $front_id = (int) get_option( 'page_on_front' );
        $uses_blocks = $front_id > 0 && get_post_meta( $front_id, jcp_page_content_meta_key(), true );
        if ( $uses_blocks ) {
            jcp_core_enqueue_script( 'jcp-core-home-interactions', 'js/pages/home-interactions.js' );
        } else {
            jcp_core_enqueue_script( 'jcp-core-home', 'js/pages/home.js' );
            $render_deps[] = 'jcp-core-home';
            $home_ctas = [
                'primary_text'   => 'View the live demo',
                'primary_url'    => '/demo',
                'secondary_text' => 'Learn how it works',
                'secondary_url'  => '#how-it-works',
            ];
            wp_localize_script( 'jcp-core-home', 'JCP_HOME_HERO_CTAS', $home_ctas );
        }
    }

    if ( $pages['is_pricing'] ) {
        // FAQ styles now come from css/sections.css (enqueued above for all marketing pages)
        jcp_core_enqueue_style( 'jcp-core-pricing', 'css/pages/pricing.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_script( 'jcp-shared-faq', 'js/features/faq.js' );
        jcp_core_enqueue_script( 'jcp-core-pricing', 'js/pages/pricing.js', [ 'jcp-shared-faq' ] );
        $render_deps[] = 'jcp-core-pricing';
    }

    if ( ! empty( $pages['is_niche_landing'] ) ) {
        jcp_core_enqueue_style( 'jcp-core-niche-landing', 'css/pages/niche-landing.css', [ 'jcp-core-sections', 'jcp-core-hero-live-demo' ] );
        if ( is_post_type_archive( 'jcp_niche_landing' ) ) {
            jcp_core_enqueue_style( 'jcp-core-blog', 'css/pages/blog.css', [ 'jcp-core-sections' ] );
            jcp_core_enqueue_script( 'jcp-industries-archive', 'js/pages/industries-archive.js' );
        }
    }

    $editor_post_id = jcp_core_get_page_editor_post_id();
    if ( $editor_post_id > 0 ) {
        jcp_core_enqueue_page_block_editor( $editor_post_id );
    }

    if ( $pages['is_contact_success'] ) {
        jcp_core_enqueue_style( 'jcp-core-sections', 'css/sections.css', [ 'jcp-core-components' ] );
        jcp_core_enqueue_style( 'jcp-core-contact', 'css/pages/contact.css', [ 'jcp-core-sections' ] );
    }

    if ( $pages['is_contact'] ) {
        jcp_core_enqueue_style( 'jcp-core-contact', 'css/pages/contact.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_script( 'jcp-core-contact', 'js/pages/contact.js' );
        $render_deps[] = 'jcp-core-contact';
        wp_localize_script( 'jcp-core-contact', 'JCP_CONTACT_FORM', [
            'rest_url'         => rest_url( 'jcp/v1/contact-submit' ),
            'success_redirect' => home_url( '/contact-success/' ),
        ] );
    }

    if ( $pages['is_blog'] || $pages['is_single'] || $pages['is_page'] ) {
        // Blog and standard page: ensure sections loaded for blog.css dependency
        if ( ! $is_marketing ) {
            jcp_core_enqueue_style( 'jcp-core-sections', 'css/sections.css', [ 'jcp-core-components' ] );
        }
        jcp_core_enqueue_style( 'jcp-core-blog', 'css/pages/blog.css', [ 'jcp-core-sections' ] );
    }

    // Load render dispatcher only on JS app-shell pages (not block-rendered homepage).
    $home_uses_blocks = false;
    if ( $pages['is_home'] ) {
        $front_id = (int) get_option( 'page_on_front' );
        $home_uses_blocks = $front_id > 0 && (bool) get_post_meta( $front_id, jcp_page_content_meta_key(), true );
    }
    $needs_render = ( $pages['is_home'] && ! $home_uses_blocks ) || $pages['is_pricing'] || $pages['is_contact']
        || $pages['is_prototype'] || $pages['is_demo'] || $pages['is_directory'] || $pages['is_company'] || $pages['is_estimate'];
    if ( $needs_render ) {
        jcp_core_enqueue_script( $render_handle, 'js/core/jcp-render.js', $render_deps );
        // Decorate onboarding CTAs with demo form values (from localStorage) when present.
        jcp_core_enqueue_script( 'jcp-core-onboarding-handoff', 'js/core/jcp-onboarding-handoff.js', [ $render_handle ] );
        $globals = "window.JCP_ENV = 'live';\n";
        $globals .= "window.JCP_CONFIG = { env: 'live', baseUrl: '" . esc_url_raw( site_url() ) . "' };\n";
        $globals .= "window.JCP_ASSET_BASE = '" . esc_url_raw( get_stylesheet_directory_uri() . '/assets' ) . "';";
        if ( function_exists( 'jcp_core_onboarding_app_url_raw' ) && function_exists( 'jcp_core_onboarding_hardcoded_session_id' ) ) {
            $onb = [
                'url'         => jcp_core_onboarding_app_url_raw(),
                'sessionId'   => jcp_core_onboarding_hardcoded_session_id(),
                'utmDefaults' => function_exists( 'jcp_core_onboarding_utm_defaults' ) ? jcp_core_onboarding_utm_defaults() : [],
            ];
            $globals .= "\nwindow.JCP_ONBOARDING = " . wp_json_encode( $onb ) . ';';
        }
        wp_add_inline_script( $render_handle, $globals, 'before' );
    }

    // App Prototype page - full interactive experience (source of truth)
    if ( $pages['is_prototype'] ) {
        jcp_core_enqueue_style( 'jcp-core-demo-shared', 'assets/shared/assets/demo.css' );
        jcp_core_enqueue_style( 'jcp-core-demo', 'css/pages/demo.css', [ 'jcp-core-demo-shared' ] );
        jcp_core_enqueue_style( 'jcp-core-leaflet', 'demo/leaflet/leaflet.css', [ 'jcp-core-demo' ] );
        jcp_core_enqueue_script( 'jcp-core-leaflet', 'demo/leaflet/leaflet.js', [ $render_handle ] );
        jcp_core_enqueue_script( 'jcp-core-demo', 'js/features/demo/jcp-demo.js', [ 'jcp-core-leaflet' ] );
        wp_localize_script( 'jcp-core-demo', 'JCP_DEMO_EVENT', [
            'rest_url' => rest_url( 'jcp/v1/demo-event' ),
        ] );
        // Prototype mode: no demo flow, no tour, start on app home; full access
        wp_add_inline_script( 'jcp-core-demo', 'window.JCP_IS_DEMO_MODE = false; window.JCP_IS_PROTOTYPE = true;', 'before' );
        return;
    }

    // Demo page - same UI as prototype but with restrictions
    if ( $pages['is_demo'] ) {
        $demo_mode = isset( $_GET['mode'] ) && $_GET['mode'] === 'run'; // phpcs:ignore
        jcp_core_enqueue_style( 'jcp-core-demo-shared', 'assets/shared/assets/demo.css' );
        jcp_core_enqueue_style( 'jcp-core-demo', 'css/pages/demo.css', [ 'jcp-core-demo-shared' ] );
        if ( $demo_mode ) {
            jcp_core_enqueue_style( 'jcp-core-leaflet', 'demo/leaflet/leaflet.css', [ 'jcp-core-demo' ] );
            jcp_core_enqueue_script( 'jcp-core-leaflet', 'demo/leaflet/leaflet.js', [ $render_handle ] );
            jcp_core_enqueue_script( 'jcp-core-demo', 'js/features/demo/jcp-demo.js', [ 'jcp-core-leaflet' ] );
            wp_localize_script( 'jcp-core-demo', 'JCP_DEMO_EVENT', [
                'rest_url' => rest_url( 'jcp/v1/demo-event' ),
            ] );
            // Demo mode: restricted access
            wp_add_inline_script( 'jcp-core-demo', 'window.JCP_IS_DEMO_MODE = true;', 'before' );
        } else {
            jcp_core_enqueue_style( 'jcp-core-survey-shared', 'assets/shared/assets/survey.css' );
            jcp_core_enqueue_style( 'jcp-core-survey', 'css/pages/survey.css', [ 'jcp-core-demo', 'jcp-core-survey-shared' ] );
            jcp_core_enqueue_script( 'jcp-core-survey', 'js/pages/survey.js', [ $render_handle ] );
            wp_localize_script( 'jcp-core-survey', 'JCP_DEMO_SURVEY', [
                'rest_url'        => rest_url( 'jcp/v1/demo-survey-submit' ),
                'rest_viewed_url' => rest_url( 'jcp/v1/demo-viewed-submit' ),
                'rest_event_url'  => rest_url( 'jcp/v1/demo-event' ),
                'demo_run_url'    => home_url( '/demo/' ),
            ] );
        }
        return;
    }

    // Directory page
    if ( $pages['is_directory'] ) {
        jcp_core_enqueue_style( 'jcp-core-demo-shared', 'assets/shared/assets/demo.css' );
        jcp_core_enqueue_style( 'jcp-core-demo', 'css/pages/demo.css', [ 'jcp-core-demo-shared' ] );
        jcp_core_enqueue_style( 'jcp-core-directory', 'css/pages/directory-consolidated.css', [ 'jcp-core-utilities' ] );
        jcp_core_enqueue_script( 'jcp-core-directory', 'js/features/directory/directory.js', [ $render_handle ] );

        // Fetch all companies
        $companies = get_posts(
            [
                'post_type'      => 'jcp_company',
                'post_status'    => 'publish',
                'numberposts'    => -1,
            ]
        );

        $listings = [];
        foreach ( $companies as $company ) {
            $listings[] = jcp_core_company_data( $company );
        }

        // Add demo companies if we have fewer than 10 listings (permalink /directory/slug)
        if ( count( $listings ) < 10 && function_exists( 'jcp_core_get_demo_companies' ) ) {
            $listings = array_merge( $listings, jcp_core_get_demo_companies() );
        }

        $directory_data = wp_json_encode( [ 'listings' => $listings ] );
        wp_add_inline_script( 'jcp-core-directory', "window.JCP_DIRECTORY_DATA = {$directory_data};", 'before' );
        return;
    }

    // Company (single company profile): /directory/slug or /company?id=slug
    if ( $pages['is_company'] ) {
        jcp_core_enqueue_style( 'jcp-core-demo-shared', 'assets/shared/assets/demo.css' );
        jcp_core_enqueue_style( 'jcp-core-demo', 'css/pages/demo.css', [ 'jcp-core-demo-shared' ] );
        jcp_core_enqueue_style( 'jcp-core-directory', 'css/pages/directory-consolidated.css', [ 'jcp-core-utilities' ] );
        jcp_core_enqueue_style( 'jcp-core-profile', 'css/pages/profile-consolidated.css', [ 'jcp-core-directory' ] );
        jcp_core_enqueue_script( 'jcp-core-profile', 'js/features/directory/profile.js', [ $render_handle ] );
        jcp_core_enqueue_script( 'jcp-core-directory-integration', 'js/features/directory/directory-integration.js', [ 'jcp-core-profile' ] );

        $slug = get_query_var( 'jcp_company_slug', '' );
        if ( $slug === '' && isset( $_GET['id'] ) && is_string( $_GET['id'] ) ) {
            $slug = sanitize_text_field( wp_unslash( $_GET['id'] ) );
        }
        if ( $slug !== '' && function_exists( 'jcp_core_resolve_company_for_profile' ) ) {
            $resolved = jcp_core_resolve_company_for_profile( $slug );
            if ( $resolved !== null ) {
                $profile_data = wp_json_encode( $resolved );
                wp_add_inline_script( 'jcp-core-profile', "window.JCP_PROFILE_DATA = {$profile_data};", 'before' );
            }
        } else {
            $post = get_post();
            if ( $post && $post->post_type === 'jcp_company' ) {
                $profile_data = wp_json_encode( jcp_core_company_data( $post ) );
                wp_add_inline_script( 'jcp-core-profile', "window.JCP_PROFILE_DATA = {$profile_data};", 'before' );
            }
        }
        return;
    }

    // Estimate page
    if ( $pages['is_estimate'] ) {
        jcp_core_enqueue_style( 'jcp-core-demo-shared', 'assets/shared/assets/demo.css' );
        jcp_core_enqueue_style( 'jcp-core-demo', 'css/pages/demo.css', [ 'jcp-core-demo-shared' ] );
        jcp_core_enqueue_style( 'jcp-core-estimate', 'css/pages/estimate.css' );
        jcp_core_enqueue_script( 'jcp-core-analytics', 'js/features/estimate/analytics.js', [ $render_handle ] );
        jcp_core_enqueue_script( 'jcp-core-requests', 'js/features/estimate/requests.js', [ $render_handle ] );
        jcp_core_enqueue_script( 'jcp-core-estimate', 'js/features/estimate/estimate-builder.js', [ 'jcp-core-analytics', 'jcp-core-requests' ] );
        return;
    }

    // WP Plugin Prototype – map + check-in slider + Powered by footer (demo content)
    if ( $pages['is_wp_plugin_prototype'] ) {
        jcp_core_enqueue_style( 'jcp-core-sections', 'css/sections.css', [ 'jcp-core-components' ] );
        jcp_core_enqueue_style( 'jcp-core-wp-plugin-prototype', 'css/pages/wp-plugin-prototype.css', [ 'jcp-core-sections' ] );
        jcp_core_enqueue_script( 'jcp-core-wp-plugin-prototype', 'js/pages/wp-plugin-prototype.js' );
        return;
    }
}

add_action( 'wp_enqueue_scripts', 'jcp_core_enqueue_assets' );

/**
 * Remove conflicting third-party auth scripts on prototype routes.
 *
 * Some plugins inject WebAuthn/FIDO scripts globally; on prototype pages those
 * scripts can throw runtime errors and prevent the app shell from rendering.
 *
 * @return void
 */
function jcp_core_strip_conflicting_scripts_on_prototype(): void {
    $pages = jcp_core_get_page_detection();
    if ( ! $pages['is_prototype'] && ! $pages['is_wp_plugin_prototype'] ) {
        return;
    }

    global $wp_scripts;
    if ( ! ( $wp_scripts instanceof WP_Scripts ) ) {
        return;
    }

    foreach ( (array) $wp_scripts->queue as $handle ) {
        if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
            continue;
        }

        $src = (string) $wp_scripts->registered[ $handle ]->src;
        if ( $src !== '' && strpos( $src, 'fido2-page-script.js' ) !== false ) {
            wp_dequeue_script( $handle );
            wp_deregister_script( $handle );
        }
    }
}

add_action( 'wp_print_scripts', 'jcp_core_strip_conflicting_scripts_on_prototype', 100 );

/**
 * Add defer to theme scripts to reduce parse-blocking and improve LCP/TBT.
 * Scripts still run in order after DOM ready; no behavior change.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string Modified tag.
 */
function jcp_core_defer_theme_scripts( $tag, $handle ): string {
    if ( strpos( $handle, 'jcp-core-' ) === 0 || strpos( $handle, 'jcp-shared-' ) === 0 ) {
        if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
            return str_replace( ' src', ' defer src', $tag );
        }
    }
    return $tag;
}

add_filter( 'script_loader_tag', 'jcp_core_defer_theme_scripts', 10, 2 );
