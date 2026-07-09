<?php
/**
 * JobCapturePro Theme Bootstrap
 * Loads all modular theme functionality from /inc/ directory
 *
 * @package JCP_Core
 */

// Load helper functions (asset paths, URLs, ACF helpers)
require_once get_template_directory() . '/inc/helpers.php';

// Brand favicon on all front-end pages (demo, survey, marketing).
require_once get_template_directory() . '/inc/favicon.php';

// App onboarding handoff URLs (marketing site → SaaS signup)
require_once get_template_directory() . '/inc/onboarding.php';

// Sitewide settings (banner, signup URL, nav CTAs)
require_once get_template_directory() . '/inc/global-settings.php';
require_once get_template_directory() . '/inc/nav-mega-menu.php';

// Load company data functions (description resolution, demo companies, save_post description generation)
require_once get_template_directory() . '/inc/company-data.php';

// JCP Companies CPT + API sync (Import from API, daily cron, shortcode). API key: set JCP_API_TOKEN in wp-config.php.
require_once get_template_directory() . '/inc/jcp-api-cpt.php';

// Load asset enqueuing logic
require_once get_template_directory() . '/inc/enqueue.php';

// Load template routing
require_once get_template_directory() . '/inc/template-routes.php';

// SEO for directory and contractor profile pages (meta description, profile titles, schema)
require_once get_template_directory() . '/inc/seo-directory.php';

// SEO for /demo (noindex, title/meta; uses WP page when published)
require_once get_template_directory() . '/inc/seo-demo.php';

// Load ACF configuration (if ACF is available)
require_once get_template_directory() . '/inc/acf-config.php';

// Industry / niche landing pages (CPT, JSON content, /industries/ archive)
require_once get_template_directory() . '/inc/page-blocks/registry.php';
require_once get_template_directory() . '/inc/page-blocks/layout.php';
require_once get_template_directory() . '/inc/page-blocks/section-surface.php';
require_once get_template_directory() . '/inc/page-blocks/presets.php';
require_once get_template_directory() . '/inc/page-blocks/writer-tools.php';
require_once get_template_directory() . '/inc/page-blocks/schema.php';
require_once get_template_directory() . '/inc/page-blocks/industry-media.php';
require_once get_template_directory() . '/inc/page-blocks/demo-migration.php';
require_once get_template_directory() . '/inc/page-blocks/doc-sections.php';
require_once get_template_directory() . '/inc/niche-landing/cpt.php';
require_once get_template_directory() . '/inc/niche-landing/schema.php';
require_once get_template_directory() . '/inc/niche-landing/doc-parser.php';
require_once get_template_directory() . '/inc/page-blocks/doc-parser.php';
require_once get_template_directory() . '/inc/niche-landing/partials.php';
require_once get_template_directory() . '/inc/niche-landing/components.php';
require_once get_template_directory() . '/inc/niche-landing/media.php';
require_once get_template_directory() . '/inc/niche-landing/editable.php';
require_once get_template_directory() . '/inc/niche-landing/split-block.php';
require_once get_template_directory() . '/inc/niche-landing/split-block.php';
require_once get_template_directory() . '/inc/niche-landing/render.php';
require_once get_template_directory() . '/inc/page-blocks/render.php';
require_once get_template_directory() . '/inc/page-blocks/rest-content.php';
require_once get_template_directory() . '/inc/page-blocks/internal-link-index.php';
require_once get_template_directory() . '/inc/niche-landing/seed.php';
require_once get_template_directory() . '/inc/page-blocks/migrate-pages.php';
if ( is_admin() ) {
	require_once get_template_directory() . '/inc/niche-landing/admin.php';
	require_once get_template_directory() . '/inc/niche-landing/bulk-create.php';
	require_once get_template_directory() . '/inc/page-blocks/seo-audit.php';
	require_once get_template_directory() . '/inc/page-blocks/admin-structure.php';
	require_once get_template_directory() . '/inc/admin-post-editor.php';
}

// Canonical form field names (REST params + GHL keys); Demo Survey = source of truth
require_once get_template_directory() . '/inc/form-fields.php';

// REST: Demo Survey form → GHL webhook
require_once get_template_directory() . '/inc/rest-demo-survey.php';

// REST: Contact form → GHL webhook
require_once get_template_directory() . '/inc/rest-contact.php';

// Demo analytics: DB table + REST endpoint
require_once get_template_directory() . '/inc/demo-analytics.php';

// Page template dropdown filter (must load on REST too — block editor template list).
require_once get_template_directory() . '/inc/admin-page-templates.php';

if ( is_admin() ) {
    require_once get_template_directory() . '/inc/admin-theme-docs.php';
    require_once get_template_directory() . '/inc/admin-block-library.php';
    require_once get_template_directory() . '/inc/admin-demo-analytics.php';
    require_once get_template_directory() . '/inc/admin-global-settings.php';
}

/**
 * Theme setup: text domain and SEO-safe document title.
 */
function jcp_core_theme_setup(): void {
	load_theme_textdomain( 'jcp-core', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'jcp_core_theme_setup' );

/**
 * Flush permalinks once after removing the legacy jcp_page CPT.
 */
function jcp_core_flush_after_jcp_page_cpt_removed(): void {
	if ( get_option( 'jcp_page_cpt_removed_flush' ) === '1' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'jcp_page_cpt_removed_flush', '1' );
}
add_action( 'init', 'jcp_core_flush_after_jcp_page_cpt_removed', 99 );

/**
 * Remove tailwind.min.css if it's being enqueued (prevents 404 errors)
 */
function jcp_core_remove_tailwind() {
    wp_dequeue_style( 'tailwind' );
    wp_deregister_style( 'tailwind' );
    wp_dequeue_style( 'tailwindcss' );
    wp_deregister_style( 'tailwindcss' );
    wp_dequeue_style( 'tailwind.min.css' );
    wp_deregister_style( 'tailwind.min.css' );
}
add_action( 'wp_enqueue_scripts', 'jcp_core_remove_tailwind', 999 );

/**
 * On the prototype page, dequeue plugin scripts that throw (e.g. Fido2 CredentialsContainer)
 * so they don't break the app on live.
 */
function jcp_core_prototype_dequeue_conflicting_scripts(): void {
	$pages = jcp_core_get_page_detection();
	if ( empty( $pages['is_prototype'] ) ) {
		return;
	}
	$wp_scripts = wp_scripts();
	if ( ! $wp_scripts ) {
		return;
	}
	$conflicting = [ 'fido2', 'fido-2' ];
	foreach ( $wp_scripts->registered as $handle => $obj ) {
		if ( ! isset( $obj->src ) || ! is_string( $obj->src ) ) {
			continue;
		}
		$src_lower = strtolower( $obj->src );
		foreach ( $conflicting as $needle ) {
			if ( strpos( $src_lower, $needle ) !== false ) {
				wp_dequeue_script( $handle );
				break;
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_prototype_dequeue_conflicting_scripts', 9999 );
