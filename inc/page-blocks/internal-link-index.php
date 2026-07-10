<?php
/**
 * Internal link catalog + inbound counts for the on-page SEO link tool.
 *
 * @package JCP_Core
 */

/**
 * Normalize an href to an internal path (or empty when invalid).
 *
 * @param string $href Raw href.
 */
function jcp_internal_link_normalize_href( string $href ): string {
	$href = trim( html_entity_decode( $href, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( $href === '' || str_starts_with( $href, '#' ) || str_starts_with( $href, 'javascript:' ) || str_starts_with( $href, 'mailto:' ) || str_starts_with( $href, 'tel:' ) ) {
		return '';
	}

	if ( preg_match( '#^https?://#i', $href ) ) {
		$home = trailingslashit( home_url() );
		if ( ! str_starts_with( $href, $home ) && ! str_starts_with( $href, untrailingslashit( $home ) ) ) {
			return '';
		}
		$parsed = wp_parse_url( $href );
		$href   = (string) ( $parsed['path'] ?? '/' );
		if ( ! empty( $parsed['query'] ) ) {
			$href .= '?' . $parsed['query'];
		}
	}

	if ( ! str_starts_with( $href, '/' ) ) {
		return '';
	}

	$path = strtok( $href, '#' );
	if ( ! is_string( $path ) || $path === '' ) {
		return '';
	}

	$path = '/' . ltrim( $path, '/' );
	$path = untrailingslashit( $path );
	if ( $path === '' ) {
		$path = '/';
	}

	return $path;
}

/**
 * Whether a normalized path is a valid internal link target.
 *
 * @param string $path Normalized path.
 */
function jcp_internal_link_is_valid_target( string $path ): bool {
	if ( $path === '' || $path === '/' ) {
		return false;
	}

	$lower = strtolower( $path );
	$blocked_prefixes = [
		'/@',
		'/channel/',
		'/wp-admin',
		'/wp-json',
		'/wp-content',
		'/feed',
		'/xmlrpc',
		'/cgi-bin',
	];
	foreach ( $blocked_prefixes as $prefix ) {
		if ( str_starts_with( $lower, $prefix ) ) {
			return false;
		}
	}

	if ( preg_match( '#/(@|channel/)[^/]+#i', $path ) ) {
		return false;
	}

	// Social-style single-segment paths (e.g. /jobcapturepro, /@handle).
	$segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
	if ( count( $segments ) === 1 && preg_match( '/^@?[a-z0-9_-]{3,}$/i', $segments[0] ) && ! in_array( $segments[0], [ 'demo', 'pricing', 'blog', 'contact', 'features', 'industries', 'resources', 'directory' ], true ) ) {
		return false;
	}

	return true;
}

/**
 * Recursively collect hrefs from flat page content.
 *
 * @param mixed                $value Value.
 * @param array<int, string>   $out   Output hrefs.
 */
function jcp_internal_link_extract_hrefs_from_value( $value, array &$out ): void {
	if ( is_string( $value ) ) {
		if ( preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\']/iu', $value, $matches ) ) {
			foreach ( $matches[1] as $href ) {
				$out[] = (string) $href;
			}
		}
		return;
	}

	if ( ! is_array( $value ) ) {
		return;
	}

	foreach ( $value as $key => $child ) {
		if ( ( $key === 'url' || $key === 'link_url' ) && is_string( $child ) && $child !== '' ) {
			$out[] = $child;
		}
		jcp_internal_link_extract_hrefs_from_value( $child, $out );
	}
}

/**
 * @param int $post_id Post ID.
 * @return array<int, string>
 */
function jcp_internal_link_hrefs_from_post( int $post_id ): array {
	$flat  = jcp_page_get_content_flat( $post_id );
	$raw   = [];
	jcp_internal_link_extract_hrefs_from_value( $flat, $raw );
	$paths = [];
	foreach ( $raw as $href ) {
		$path = jcp_internal_link_normalize_href( (string) $href );
		if ( $path !== '' && jcp_internal_link_is_valid_target( $path ) ) {
			$paths[] = $path;
		}
	}
	return array_values( array_unique( $paths ) );
}

/**
 * Link audit for a block/industry page (inbound, outbound internal, outbound external).
 *
 * @param int $post_id Post ID.
 * @return array{inbound: int, outbound_internal: int, outbound_external: int, external_hosts: array<int, string>}
 */
function jcp_internal_link_post_audit( int $post_id ): array {
	$flat = jcp_page_get_content_flat( $post_id );
	$raw  = [];
	jcp_internal_link_extract_hrefs_from_value( $flat, $raw );

	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$site_host = is_string( $site_host ) ? strtolower( $site_host ) : '';

	$internal = [];
	$external = [];
	foreach ( $raw as $href ) {
		$href = trim( (string) $href );
		if ( $href === '' || str_starts_with( $href, '#' ) || str_starts_with( $href, 'mailto:' ) || str_starts_with( $href, 'tel:' ) ) {
			continue;
		}
		$host = wp_parse_url( $href, PHP_URL_HOST );
		$host = is_string( $host ) ? strtolower( $host ) : '';
		if ( $host !== '' && $site_host !== '' && $host !== $site_host ) {
			$external[] = $href;
			continue;
		}
		$path = jcp_internal_link_normalize_href( $href );
		if ( $path !== '' && jcp_internal_link_is_valid_target( $path ) ) {
			$internal[] = $path;
		}
	}

	$internal = array_values( array_unique( $internal ) );
	$external = array_values( array_unique( $external ) );

	$external_hosts = [];
	foreach ( $external as $url ) {
		$h = wp_parse_url( $url, PHP_URL_HOST );
		if ( is_string( $h ) && $h !== '' ) {
			$external_hosts[ strtolower( $h ) ] = $h;
		}
	}

	$current_path = jcp_internal_link_normalize_href( (string) get_permalink( $post_id ) );
	$index        = jcp_internal_link_build_index();
	$inbound      = $current_path !== '' ? (int) ( $index['inbound'][ $current_path ] ?? 0 ) : 0;

	return [
		'inbound'             => $inbound,
		'outbound_internal'   => count( $internal ),
		'outbound_external'   => count( $external ),
		'external_hosts'      => array_values( $external_hosts ),
	];
}

/**
 * Hub label for a page path.
 *
 * @param string $path   Normalized path.
 * @param string $post_type Post type.
 */
function jcp_internal_link_hub_for_path( string $path, string $post_type ): string {
	if ( $post_type === 'jcp_niche_landing' || str_starts_with( $path, '/industries' ) ) {
		return 'trade';
	}
	if ( str_starts_with( $path, '/features' ) ) {
		return 'feature';
	}
	if ( in_array( $path, [ '/pricing', '/demo', '/contact' ], true ) ) {
		return 'conversion';
	}
	if ( str_starts_with( $path, '/blog' ) || str_starts_with( $path, '/resources' ) ) {
		return 'resource';
	}
	return 'page';
}

/**
 * Tokenize text for relevance scoring.
 *
 * @param string $text Text.
 * @return array<int, string>
 */
function jcp_internal_link_tokenize( string $text ): array {
	$text = strtolower( wp_strip_all_tags( $text ) );
	$text = preg_replace( '/[^a-z0-9\s-]/u', ' ', $text ) ?? '';
	$parts = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! is_array( $parts ) ) {
		return [];
	}

	$stop = [
		'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'have', 'in', 'is', 'it',
		'of', 'on', 'or', 'that', 'the', 'their', 'this', 'to', 'was', 'were', 'will', 'with', 'your',
		'our', 'we', 'you', 'more', 'every', 'into', 'how', 'what', 'when', 'who', 'why',
	];

	$tokens = [];
	foreach ( $parts as $part ) {
		$part = trim( $part, '-' );
		if ( strlen( $part ) < 3 || in_array( $part, $stop, true ) ) {
			continue;
		}
		$tokens[] = $part;
	}

	return array_values( array_unique( $tokens ) );
}

/**
 * Read Rank Math focus keyword when available.
 *
 * @param int $post_id Post ID.
 */
function jcp_internal_link_focus_keyword( int $post_id ): string {
	if ( function_exists( 'jcp_seo_audit_rank_math_meta' ) ) {
		$rm = jcp_seo_audit_rank_math_meta( $post_id );
		return trim( (string) ( $rm['focus_keyword'] ?? '' ) );
	}
	$focus = trim( (string) get_post_meta( $post_id, 'rank_math_focus_keyword', true ) );
	if ( $focus === '' ) {
		$focus = trim( (string) get_post_meta( $post_id, '_rank_math_focus_keyword', true ) );
	}
	return $focus;
}

/**
 * Build keyword corpus for a page.
 *
 * @param int                  $post_id Post ID.
 * @param array<string, mixed> $flat    Flat content.
 * @return array<int, string>
 */
function jcp_internal_link_page_keywords( int $post_id, array $flat ): array {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return [];
	}

	$rm    = [ 'focus_keyword' => jcp_internal_link_focus_keyword( $post_id ) ];
	$hero  = is_array( $flat['hero'] ?? null ) ? $flat['hero'] : [];
	$bits  = [
		(string) get_the_title( $post ),
		(string) $post->post_name,
		(string) ( $flat['niche_label'] ?? '' ),
		(string) ( $flat['niche_key'] ?? '' ),
		(string) ( $hero['h1'] ?? '' ),
		(string) ( $hero['subheadline'] ?? '' ),
		(string) ( $rm['focus_keyword'] ?? '' ),
	];

	$tokens = [];
	foreach ( $bits as $bit ) {
		$tokens = array_merge( $tokens, jcp_internal_link_tokenize( $bit ) );
	}

	return array_values( array_unique( $tokens ) );
}

/**
 * Marketing routes that may not use JCP block content but are valid link targets.
 *
 * @return array<int, array<string, mixed>>
 */
function jcp_internal_link_static_pages(): array {
	$routes = [
		[
			'path'     => '/directory',
			'label'    => __( 'Directory', 'jcp-core' ),
			'hub'      => 'resource',
			'keywords' => [ 'directory', 'contractors', 'listings', 'find', 'contractor' ],
		],
		[
			'path'     => '/features',
			'label'    => __( 'Features', 'jcp-core' ),
			'hub'      => 'feature',
			'keywords' => [ 'features', 'feature' ],
		],
		[
			'path'     => '/pricing',
			'label'    => __( 'Pricing', 'jcp-core' ),
			'hub'      => 'conversion',
			'keywords' => [ 'pricing', 'price', 'plans' ],
		],
		[
			'path'     => '/demo',
			'label'    => __( 'Live demo', 'jcp-core' ),
			'hub'      => 'conversion',
			'keywords' => [ 'demo', 'interactive', 'trial' ],
		],
		[
			'path'     => '/contact',
			'label'    => __( 'Contact', 'jcp-core' ),
			'hub'      => 'conversion',
			'keywords' => [ 'contact', 'sales' ],
		],
		[
			'path'     => '/blog',
			'label'    => __( 'Blog', 'jcp-core' ),
			'hub'      => 'resource',
			'keywords' => [ 'blog', 'resources', 'articles' ],
		],
		[
			'path'     => '/industries',
			'label'    => __( 'Industries', 'jcp-core' ),
			'hub'      => 'trade',
			'keywords' => [ 'industries', 'trades', 'trade' ],
		],
	];

	$out = [];
	foreach ( $routes as $route ) {
		$path = jcp_internal_link_normalize_href( home_url( (string) $route['path'] ) );
		if ( $path === '' || ! jcp_internal_link_is_valid_target( $path ) ) {
			continue;
		}

		$slug  = basename( $path );
		$page  = get_page_by_path( $slug );
		$label = ( $page instanceof WP_Post && get_the_title( $page ) !== '' )
			? get_the_title( $page )
			: (string) $route['label'];

		$keywords = array_values(
			array_unique(
				array_merge(
					(array) $route['keywords'],
					jcp_internal_link_tokenize( $label ),
					jcp_internal_link_tokenize( $slug )
				)
			)
		);

		$out[] = [
			'id'            => $page instanceof WP_Post ? (int) $page->ID : 0,
			'href'          => $path,
			'label'         => $label,
			'hub'           => (string) $route['hub'],
			'focus_keyword' => '',
			'keywords'      => $keywords,
		];
	}

	return $out;
}

/**
 * Merge link index pages without duplicate hrefs.
 *
 * @param array<int, array<string, mixed>> $pages Existing pages.
 * @param array<int, array<string, mixed>> $extra Pages to add.
 * @return array<int, array<string, mixed>>
 */
function jcp_internal_link_merge_pages( array $pages, array $extra ): array {
	$seen = [];
	foreach ( $pages as $page ) {
		if ( is_array( $page ) && ! empty( $page['href'] ) ) {
			$seen[ (string) $page['href'] ] = true;
		}
	}
	foreach ( $extra as $page ) {
		if ( ! is_array( $page ) ) {
			continue;
		}
		$href = (string) ( $page['href'] ?? '' );
		if ( $href === '' || isset( $seen[ $href ] ) ) {
			continue;
		}
		$pages[]       = $page;
		$seen[ $href ] = true;
	}
	return $pages;
}

/**
 * Collect linkable published pages.
 *
 * @return array<int, WP_Post>
 */
function jcp_internal_link_collect_posts(): array {
	$posts = get_posts(
		[
			'post_type'      => [ 'jcp_niche_landing', 'page' ],
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]
	);

	$out = [];
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		if ( $post->post_type === 'page' && ! jcp_page_is_content_page( (int) $post->ID ) ) {
			continue;
		}
		$out[] = $post;
	}

	return $out;
}

/**
 * Build site-wide internal link index (cached).
 *
 * @return array{pages: array<int, array<string, mixed>>, inbound: array<string, int>}
 */
function jcp_internal_link_build_index(): array {
	$cached = get_transient( 'jcp_internal_link_index_v3' );
	if ( is_array( $cached ) && isset( $cached['pages'], $cached['inbound'] ) ) {
		return $cached;
	}

	$posts   = jcp_internal_link_collect_posts();
	$pages   = [];
	$inbound = [];

	foreach ( $posts as $post ) {
		$post_id = (int) $post->ID;
		$path    = jcp_internal_link_normalize_href( (string) get_permalink( $post_id ) );
		if ( $path === '' || ! jcp_internal_link_is_valid_target( $path ) ) {
			continue;
		}

		$flat     = jcp_page_get_content_flat( $post_id );
		$rm       = [ 'focus_keyword' => jcp_internal_link_focus_keyword( $post_id ) ];
		$keywords = jcp_internal_link_page_keywords( $post_id, $flat );
		$label    = ! empty( $flat['niche_label'] ) ? (string) $flat['niche_label'] : get_the_title( $post );

		$pages[] = [
			'id'             => $post_id,
			'href'           => $path,
			'label'          => $label,
			'hub'            => jcp_internal_link_hub_for_path( $path, $post->post_type ),
			'focus_keyword'  => (string) ( $rm['focus_keyword'] ?? '' ),
			'keywords'       => $keywords,
		];
	}

	foreach ( $posts as $post ) {
		foreach ( jcp_internal_link_hrefs_from_post( (int) $post->ID ) as $target ) {
			$inbound[ $target ] = ( $inbound[ $target ] ?? 0 ) + 1;
		}
	}

	$hub_candidates = array_filter(
		[
			get_post_type_archive_link( 'jcp_niche_landing' ),
			home_url( '/features/' ),
			home_url( '/pricing/' ),
			home_url( '/demo/' ),
		]
	);
	foreach ( $hub_candidates as $hub_url ) {
		$path = jcp_internal_link_normalize_href( (string) $hub_url );
		if ( $path === '' || $path === '/' ) {
			continue;
		}
		$exists = false;
		foreach ( $pages as $page ) {
			if ( ( $page['href'] ?? '' ) === $path ) {
				$exists = true;
				break;
			}
		}
		if ( $exists ) {
			continue;
		}
		$pages[] = [
			'id'            => 0,
			'href'          => $path,
			'label'         => ucwords( str_replace( [ '-', '_' ], ' ', basename( $path ) ) ),
			'hub'           => jcp_internal_link_hub_for_path( $path, 'page' ),
			'focus_keyword' => '',
			'keywords'      => jcp_internal_link_tokenize( basename( $path ) ),
		];
	}

	$pages = jcp_internal_link_merge_pages( $pages, jcp_internal_link_static_pages() );

	$data = [
		'pages'   => $pages,
		'inbound' => $inbound,
	];
	set_transient( 'jcp_internal_link_index_v3', $data, HOUR_IN_SECONDS );

	return $data;
}

/**
 * Editor payload: link targets + inbound counts for current page.
 *
 * @param int $current_post_id Current post being edited.
 * @return array<string, mixed>
 */
function jcp_internal_link_editor_payload( int $current_post_id ): array {
	$index        = jcp_internal_link_build_index();
	$current_path = jcp_internal_link_normalize_href( (string) get_permalink( $current_post_id ) );
	$pages        = [];

	foreach ( $index['pages'] as $page ) {
		if ( ! is_array( $page ) ) {
			continue;
		}
		$href = (string) ( $page['href'] ?? '' );
		if ( $href === '' || $href === $current_path ) {
			continue;
		}
		$pages[] = [
			'href'          => $href,
			'label'         => (string) ( $page['label'] ?? $href ),
			'hub'           => (string) ( $page['hub'] ?? 'page' ),
			'focus_keyword' => (string) ( $page['focus_keyword'] ?? '' ),
			'keywords'      => array_values( array_map( 'strval', $page['keywords'] ?? [] ) ),
			'site_inlinks'  => (int) ( $index['inbound'][ $href ] ?? 0 ),
		];
	}

	return [
		'current_path' => $current_path,
		'pages'        => $pages,
		'generated_at' => time(),
	];
}

/**
 * Clear cached link index when content changes.
 *
 * @param int $post_id Post ID.
 */
function jcp_internal_link_clear_cache( int $post_id ): void {
	if ( $post_id <= 0 ) {
		return;
	}
	delete_transient( 'jcp_internal_link_index_v1' );
	delete_transient( 'jcp_internal_link_index_v2' );
	delete_transient( 'jcp_internal_link_index_v3' );
}
add_action( 'save_post_jcp_niche_landing', 'jcp_internal_link_clear_cache' );
add_action( 'save_post_page', 'jcp_internal_link_clear_cache' );
