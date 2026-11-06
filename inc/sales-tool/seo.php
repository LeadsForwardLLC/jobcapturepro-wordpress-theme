<?php
/**
 * Sales deck / sales tool SEO: always noindex and keep out of sitemaps.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math robots for sales surfaces.
 *
 * @param array<string, string> $robots Robots directives.
 * @return array<string, string>
 */
function jcp_sales_tool_rank_math_robots( $robots ) {
	if ( ! function_exists( 'jcp_is_sales_tool_request' ) || ! jcp_is_sales_tool_request() ) {
		return $robots;
	}
	if ( ! is_array( $robots ) ) {
		$robots = [];
	}
	$robots['index']  = 'noindex';
	$robots['follow'] = 'nofollow';
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'jcp_sales_tool_rank_math_robots', 999 );

/**
 * Core robots API for sales surfaces.
 *
 * @param array<string, mixed> $robots Robots directives.
 * @return array<string, mixed>
 */
function jcp_sales_tool_wp_robots( $robots ) {
	if ( ! function_exists( 'jcp_is_sales_tool_request' ) || ! jcp_is_sales_tool_request() ) {
		return $robots;
	}
	if ( ! is_array( $robots ) ) {
		$robots = [];
	}
	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	unset( $robots['index'], $robots['follow'] );
	return $robots;
}
add_filter( 'wp_robots', 'jcp_sales_tool_wp_robots', 999 );

/**
 * Exclude sales deck CPT from core XML sitemaps.
 *
 * @param array<string, \WP_Sitemaps_Provider> $providers Sitemap providers keyed by name.
 * @return array<string, \WP_Sitemaps_Provider>
 */
function jcp_sales_tool_exclude_cpt_from_wp_sitemaps( $post_types ) {
	if ( ! is_array( $post_types ) ) {
		return $post_types;
	}
	unset( $post_types['jcp_sales_deck'] );
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'jcp_sales_tool_exclude_cpt_from_wp_sitemaps' );

/**
 * Exclude Sales Tool page template from core page sitemap.
 *
 * @param array             $entry     Sitemap entry.
 * @param \WP_Post          $post      Post.
 * @param string            $post_type Post type.
 * @return array|false
 */
function jcp_sales_tool_exclude_page_from_wp_sitemap( $entry, $post, $post_type ) {
	if ( $post_type !== 'page' || ! ( $post instanceof WP_Post ) ) {
		return $entry;
	}
	if ( function_exists( 'jcp_is_sales_tool_template' ) && jcp_is_sales_tool_template( $post ) ) {
		return false;
	}
	return $entry;
}
add_filter( 'wp_sitemaps_posts_entry', 'jcp_sales_tool_exclude_page_from_wp_sitemap', 10, 3 );

/**
 * Exclude sales deck CPT from Rank Math sitemaps.
 *
 * @param bool   $exclude Whether to exclude.
 * @param string $type    Post type.
 * @return bool
 */
function jcp_sales_tool_exclude_cpt_from_rank_math_sitemap( $exclude, $type ) {
	if ( $type === 'jcp_sales_deck' ) {
		return true;
	}
	return $exclude;
}
add_filter( 'rank_math/sitemap/exclude_post_type', 'jcp_sales_tool_exclude_cpt_from_rank_math_sitemap', 10, 2 );

/**
 * Exclude Sales Tool pages from Rank Math sitemaps.
 *
 * @param array|false $url    Sitemap URL data.
 * @param string      $type   Object type.
 * @param mixed       $object Object.
 * @return array|false
 */
function jcp_sales_tool_exclude_page_from_rank_math_sitemap( $url, $type, $object ) {
	if ( $type !== 'post' || ! ( $object instanceof WP_Post ) ) {
		return $url;
	}
	if ( $object->post_type === 'jcp_sales_deck' ) {
		return false;
	}
	if ( $object->post_type === 'page' && function_exists( 'jcp_is_sales_tool_template' ) && jcp_is_sales_tool_template( $object ) ) {
		return false;
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'jcp_sales_tool_exclude_page_from_rank_math_sitemap', 10, 3 );
