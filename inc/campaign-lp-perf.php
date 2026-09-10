<?php
/**
 * Paid campaign landing-page performance (Meta LPs).
 *
 * Targets: TBT / Speed Index / image delivery / render-blocking CSS.
 * Applies only when jcp_page_current_is_campaign_landing() is true.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current front-end request is a paid campaign LP.
 */
function jcp_core_is_campaign_lp_request(): bool {
	return function_exists( 'jcp_page_current_is_campaign_landing' )
		&& jcp_page_current_is_campaign_landing();
}

/**
 * Dequeue plugin CSS unused by campaign LPs (Tailwind, WP block library).
 */
function jcp_core_campaign_lp_dequeue_unused_assets(): void {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return;
	}

	$style_handles = [
		'jobcapturepro-tailwind',
		'jobcapturepro-plugin-tailwind',
		'tailwind',
		'tailwindcss',
		'tailwind.min.css',
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles',
		'wc-blocks-style',
		'woocommerce-general',
		'woocommerce-layout',
		'woocommerce-smallscreen',
	];
	foreach ( $style_handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	$script_handles = [
		'wp-embed',
		'googlesitekit-events-provider-content-events',
	];
	foreach ( $script_handles as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_campaign_lp_dequeue_unused_assets', 1001 );
add_action( 'wp_print_styles', 'jcp_core_campaign_lp_dequeue_unused_assets', 101 );
add_action( 'wp_print_scripts', 'jcp_core_campaign_lp_dequeue_unused_assets', 101 );

/**
 * Async-load CSS that is not needed for first paint on campaign LPs.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 */
function jcp_core_campaign_lp_async_secondary_css( string $html, string $handle ): string {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return $html;
	}

	$async_handles = [
		'jcp-core-story-moments',
		'jcp-core-content-prose',
		'jcp-core-case-study-cohort',
		'jcp-core-utilities',
	];
	if ( ! in_array( $handle, $async_handles, true ) ) {
		return $html;
	}
	if ( strpos( $html, 'onload=' ) !== false ) {
		return $html;
	}

	$async = preg_replace( "/\smedia=['\"]all['\"]/", " media='print' onload=\"this.media='all'\"", $html, 1 );
	if ( ! is_string( $async ) ) {
		return $html;
	}
	if ( strpos( $async, 'noscript' ) === false ) {
		$href = '';
		if ( preg_match( '/href=[\'"]([^\'"]+)[\'"]/', $async, $m ) ) {
			$href = $m[1];
		}
		if ( $href !== '' ) {
			$async .= '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '"></noscript>';
		}
	}
	return $async;
}
add_filter( 'style_loader_tag', 'jcp_core_campaign_lp_async_secondary_css', 25, 2 );

/**
 * Preload the real LCP image (story phone / HVAC capture WebP), not the map CSS bg.
 */
function jcp_core_campaign_lp_preload_lcp(): void {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return;
	}

	$webp = get_template_directory_uri() . '/assets/campaign/jcp-campaign-hvac-capture.webp';
	echo '<link rel="preload" as="image" type="image/webp" href="' . esc_url( $webp ) . '" fetchpriority="high">' . "\n";
}
add_action( 'wp_head', 'jcp_core_campaign_lp_preload_lcp', 1 );

/**
 * Map a campaign asset URL to an optimally sized WebP when the file exists.
 *
 * @param string $url   Original image URL.
 * @param int    $width Declared HTML width (0 if unknown).
 */
function jcp_core_campaign_lp_optimize_asset_url( string $url, int $width = 0 ): string {
	if ( $url === '' || strpos( $url, '/assets/campaign/' ) === false ) {
		return $url;
	}
	if ( ! preg_match( '#^(https?://[^/]+)?(/[^"\']+/assets/campaign/)([^/"\']+?)(-(192|640))?(\.webp|\.jpe?g)(\?[^"\']*)?$#i', $url, $m ) ) {
		return $url;
	}

	$prefix = ( $m[1] ?? '' ) . $m[2];
	$base   = $m[3];
	$query  = $m[7] ?? '';

	// Skip already-sized variants in the basename.
	$base = preg_replace( '/-(192|640)$/', '', $base ) ?? $base;

	$suffix = '';
	if ( $width > 0 && $width <= 120 ) {
		$suffix = '-192';
	} elseif ( $width > 0 && $width <= 720 ) {
		$suffix = '-640';
	}

	$dir = trailingslashit( get_template_directory() ) . 'assets/campaign/';
	$candidates = [];
	if ( $suffix !== '' ) {
		$candidates[] = $base . $suffix . '.webp';
	}
	$candidates[] = $base . '.webp';
	$candidates[] = $base . '.jpg';

	foreach ( $candidates as $file ) {
		if ( is_readable( $dir . $file ) ) {
			return $prefix . $file . $query;
		}
	}

	return $url;
}

/**
 * Rewrite <img> campaign sources to WebP (+ size variants).
 *
 * @param string $html Page HTML.
 */
function jcp_core_campaign_lp_rewrite_images( string $html ): string {
	if ( $html === '' || strpos( $html, '/assets/campaign/' ) === false ) {
		return $html;
	}

	// Rewrite each <img> that references campaign assets.
	$html = preg_replace_callback(
		'/<img\b([^>]*)>/i',
		static function ( array $m ): string {
			$attrs = $m[1];
			if ( strpos( $attrs, '/assets/campaign/' ) === false ) {
				return $m[0];
			}

			$width = 0;
			if ( preg_match( '/\bwidth=["\'](\d+)["\']/', $attrs, $wm ) ) {
				$width = (int) $wm[1];
			}

			$attrs = preg_replace_callback(
				'/\b(src|data-lazy-src|data-src)=["\']([^"\']+)["\']/i',
				static function ( array $am ) use ( $width ): string {
					$opt = jcp_core_campaign_lp_optimize_asset_url( $am[2], $width );
					return $am[1] . '="' . esc_attr( $opt ) . '"';
				},
				$attrs
			);

			// Tiny avatars / chips: never compete with LCP.
			if ( $width > 0 && $width <= 96 && stripos( $attrs, 'fetchpriority' ) === false ) {
				if ( ! preg_match( '/\bloading=/i', $attrs ) ) {
					$attrs .= ' loading="lazy"';
				}
			}

			// Story-phone camera photo is the usual LCP candidate.
			if ( strpos( $attrs, 'jcp-story-camera__img' ) !== false && stripos( $attrs, 'fetchpriority' ) === false ) {
				$attrs .= ' fetchpriority="high"';
				$attrs = preg_replace( '/\bloading=["\'][^"\']*["\']/i', '', $attrs ) ?? $attrs;
			}

			// First eager benefit image stays eager; force lazy on large below-fold repeats.
			if ( $width >= 400 && preg_match( '/\bloading=["\']eager["\']/i', $attrs ) && preg_match( '/benefits\.items\.[1-9]/', $attrs ) ) {
				$attrs = preg_replace( '/\bloading=["\']eager["\']/i', 'loading="lazy"', $attrs, 1 ) ?? $attrs;
			}

			return '<img' . $attrs . '>';
		},
		$html
	) ?? $html;

	// JSON stores / inline style urls that still point at campaign JPGs.
	$html = preg_replace_callback(
		'#https?://[^"\'\s]+/assets/campaign/[a-z0-9._-]+\.jpe?g#i',
		static function ( array $m ): string {
			return jcp_core_campaign_lp_optimize_asset_url( $m[0], 0 );
		},
		$html
	) ?? $html;

	return $html;
}

/**
 * Strip Rocket's mistaken high-priority preload of the map CSS background.
 *
 * @param string $html Page HTML.
 */
function jcp_core_campaign_lp_strip_bad_preloads( string $html ): string {
	if ( $html === '' ) {
		return $html;
	}

	$html = preg_replace(
		'#<link[^>]+rel=["\']preload["\'][^>]+jcp-map-bg-light\.jpg[^>]*>#i',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<link[^>]+jcp-map-bg-light\.jpg[^>]+rel=["\']preload["\'][^>]*>#i',
		'',
		$html
	) ?? $html;

	return $html;
}

/**
 * Combined HTML transform for campaign LPs.
 *
 * @param string $html Page HTML.
 */
function jcp_core_campaign_lp_transform_html( string $html ): string {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return $html;
	}
	$html = jcp_core_campaign_lp_strip_bad_preloads( $html );
	$html = jcp_core_campaign_lp_strip_map_bg_rocket( $html );
	$html = jcp_core_campaign_lp_rewrite_images( $html );
	return $html;
}

/**
 * Nest a campaign HTML buffer after the analytics strip buffer.
 */
function jcp_core_campaign_lp_install_html_buffer(): void {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return;
	}
	ob_start( 'jcp_core_campaign_lp_transform_html' );
}
add_action( 'template_redirect', 'jcp_core_campaign_lp_install_html_buffer', 1 );

/**
 * Script handles that can wait until idle/interaction on campaign LPs.
 *
 * @return string[]
 */
function jcp_core_campaign_lp_delayable_script_handles(): array {
	return [
		'jcp-core-story-phone',
		'jcp-core-authority',
		'jcp-core-story-moments',
		'jcp-core-campaign',
		'jcp-core-testimonials',
		'jcp-core-home-interactions',
		'jcp-core-case-study-exit',
		'jcp-core-onboarding-handoff',
		'jcp-core-site-banner',
	];
}

/**
 * On campaign LPs, allow Rocket Delay JS for heavy theme scripts.
 *
 * @param string[] $excluded Delay JS exclusion patterns.
 * @return string[]
 */
function jcp_core_campaign_lp_rocket_delay_exclusions( array $excluded ): array {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return $excluded;
	}

	$allow_delay = [
		'jcp-core-story-phone',
		'story-phone.js',
		'jcp-core-authority',
		'authority.js',
		'jcp-core-story-moments',
		'story-moments.js',
		'jcp-core-campaign',
		'campaign.js',
		'jcp-core-testimonials',
		'testimonials.js',
		'jcp-core-home-interactions',
		'home-interactions.js',
		'jcp-core-case-study-exit',
		'case-study-exit-intent.js',
		'jcp-core-onboarding-handoff',
		'jcp-onboarding-handoff.js',
		'jcp-core-site-banner',
		'jcp-site-banner.js',
	];

	$out = [];
	foreach ( $excluded as $item ) {
		$keep = true;
		foreach ( $allow_delay as $needle ) {
			if ( stripos( (string) $item, $needle ) !== false ) {
				$keep = false;
				break;
			}
		}
		if ( $keep ) {
			$out[] = $item;
		}
	}
	return array_values( $out );
}
add_filter( 'rocket_delay_js_exclusions', 'jcp_core_campaign_lp_rocket_delay_exclusions', 100 );

/**
 * Mark non-critical campaign scripts so they do not execute during first paint / TBT.
 *
 * @param string $tag    Script HTML.
 * @param string $handle Script handle.
 * @param string $src    Script URL.
 */
function jcp_core_campaign_lp_delay_script_tag( string $tag, string $handle, string $src ): string {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return $tag;
	}
	if ( ! in_array( $handle, jcp_core_campaign_lp_delayable_script_handles(), true ) ) {
		return $tag;
	}
	if ( $src === '' ) {
		return $tag;
	}

	return sprintf(
		'<script type="text/plain" data-jcp-delay-js id="%1$s-js" src="%2$s"></script>' . "\n",
		esc_attr( $handle ),
		esc_url( $src )
	);
}
add_filter( 'script_loader_tag', 'jcp_core_campaign_lp_delay_script_tag', 40, 3 );

/**
 * Idle/interaction loader for delayed campaign scripts.
 */
function jcp_core_campaign_lp_print_delayed_script_loader(): void {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return;
	}
	echo "<script id=\"jcp-campaign-delay-js\">\n";
	echo "(function(){\n";
	echo "function boot(){\n";
	echo "var nodes=document.querySelectorAll('script[data-jcp-delay-js][src]');\n";
	echo "if(!nodes.length)return;\n";
	echo "nodes.forEach(function(node){\n";
	echo "var s=document.createElement('script');\n";
	echo "s.src=node.getAttribute('src');\n";
	echo "s.defer=true;\n";
	echo "if(node.id)s.id=node.id;\n";
	echo "node.parentNode.insertBefore(s,node);\n";
	echo "node.parentNode.removeChild(node);\n";
	echo "});\n";
	echo "}\n";
	echo "['pointerdown','keydown','touchstart'].forEach(function(t){window.addEventListener(t,boot,{once:true,passive:true});});\n";
	echo "if('requestIdleCallback' in window){requestIdleCallback(function(){boot();},{timeout:4000});}\n";
	echo "else{setTimeout(boot,4000);}\n";
	echo "})();\n";
	echo "</script>\n";
}
add_action( 'wp_footer', 'jcp_core_campaign_lp_print_delayed_script_loader', 1 );

/**
 * Drop Rocket map-bg lazy pairs on campaign LPs (hero no longer uses that image).
 *
 * @param string $html Page HTML.
 */
function jcp_core_campaign_lp_strip_map_bg_rocket( string $html ): string {
	$html = preg_replace(
		'#<style id="wpr-lazyload-bg-exclusion">.*?</style>#is',
		'<style id="wpr-lazyload-bg-exclusion"></style>',
		$html,
		1
	) ?? $html;
	$html = preg_replace(
		'#const rocket_excluded_pairs = \[.*?\];#s',
		'const rocket_excluded_pairs = [];',
		$html,
		1
	) ?? $html;
	return $html;
}
