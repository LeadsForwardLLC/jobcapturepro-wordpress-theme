<?php
/**
 * SEO for the /demo survey and live demo (?mode=run).
 * Always noindex; title/description from the WP page when published, else sensible defaults.
 *
 * @package JCP_Core
 */

/**
 * Published WordPress page for /demo, if one exists.
 *
 * @return WP_Post|null
 */
function jcp_core_get_demo_page(): ?WP_Post {
	$page = get_page_by_path( 'demo', OBJECT, 'page' );
	if ( $page instanceof WP_Post && $page->post_status === 'publish' ) {
		return $page;
	}
	return null;
}

/**
 * Whether the current request is the demo survey or live demo app.
 *
 * @return bool
 */
function jcp_core_is_demo_request(): bool {
	if ( get_query_var( 'jcp_route', '' ) === 'demo' ) {
		return true;
	}

	$path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	if ( $path === 'demo' ) {
		return true;
	}

	if ( strpos( $path, '/' ) !== false && strtok( $path, '/' ) === 'demo' ) {
		return true;
	}

	return is_page_template( 'page-demo.php' ) || is_page( 'demo' );
}

/**
 * Default document title when no published demo page exists.
 *
 * @return string
 */
function jcp_core_demo_default_title(): string {
	if ( jcp_core_is_demo_run_request() ) {
		return __( 'Live Product Demo', 'jcp-core' );
	}
	return __( 'Interactive Demo', 'jcp-core' );
}

/**
 * Default meta description when no published demo page exists.
 *
 * @return string
 */
function jcp_core_demo_default_description(): string {
	return __(
		'Experience JobCapturePro: run the guided sales demo and see how one completed job becomes proof across your website, Google, social, and directory.',
		'jcp-core'
	);
}

/**
 * Attach demo SEO filters (noindex + title/description/canonical).
 *
 * @return void
 */
function jcp_core_apply_demo_seo(): void {
	if ( ! jcp_core_is_demo_request() ) {
		return;
	}

	$canonical = home_url( '/demo/' );

	add_filter(
		'rank_math/frontend/robots',
		static function () {
			return [
				'index'  => 'noindex',
				'follow' => 'nofollow',
			];
		},
		99
	);

	if ( ! defined( 'RANK_MATH_VERSION' ) ) {
		add_action(
			'wp_head',
			static function () {
				echo '<meta name="robots" content="noindex, nofollow">' . "\n";
			},
			0
		);
	}

	add_filter(
		'rank_math/frontend/canonical',
		static function () use ( $canonical ) {
			return $canonical;
		},
		10
	);

	$page = jcp_core_get_demo_page();
	if ( $page ) {
		return;
	}

	$title       = jcp_core_demo_default_title();
	$description = jcp_core_demo_default_description();
	$site_name   = get_bloginfo( 'name' );

	add_filter(
		'document_title_parts',
		static function ( $parts ) use ( $title ) {
			$parts['title'] = $title;
			return $parts;
		},
		999
	);

	add_filter(
		'rank_math/frontend/title',
		static function () use ( $title, $site_name ) {
			return $title . ' - ' . $site_name;
		},
		99
	);

	add_filter(
		'rank_math/frontend/description',
		static function () use ( $description ) {
			return $description;
		},
		99
	);
}

/**
 * Point the main query at the published demo page so Rank Math can read its meta.
 *
 * @return void
 */
function jcp_core_prime_demo_page_query(): void {
	$page = jcp_core_get_demo_page();
	if ( ! $page ) {
		return;
	}

	global $wp_query;

	$wp_query->is_page     = true;
	$wp_query->is_singular = true;
	$wp_query->is_home     = false;
	$wp_query->is_archive  = false;
	$wp_query->is_404      = false;

	$wp_query->queried_object    = $page;
	$wp_query->queried_object_id = (int) $page->ID;
	$wp_query->post              = $page;
	$wp_query->posts             = [ $page ];
	$wp_query->post_count        = 1;
	$wp_query->found_posts       = 1;

	$GLOBALS['post'] = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $page );
}

add_action( 'template_redirect', 'jcp_core_apply_demo_seo', 5 );
