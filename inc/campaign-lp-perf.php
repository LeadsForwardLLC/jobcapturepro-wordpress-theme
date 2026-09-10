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
 * Dequeue plugin CSS / chrome JS unused by campaign LPs.
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
		// Landing chrome hides the top banner; don't pay for its JS reflows.
		'jcp-core-site-banner',
		'jcp-core-nav',
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
 * Inline critical above-the-fold CSS so sheets can load async without FOUC/CLS.
 */
function jcp_core_campaign_lp_inline_critical_css(): void {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return;
	}

	echo '<style id="jcp-campaign-critical">';
	echo 'html{scroll-behavior:auto}';
	/*
	 * Kill site-header body offset immediately. Async base.css sets
	 * padding-top:var(--jcp-header-stack-height) (~69–72px); when that lands
	 * after first paint the whole hero shifts (~0.3 CLS on mobile).
	 */
	echo 'body.jcp-campaign-variant,body.jcp-landing-chrome-hidden{margin:0;background:#fff;color:#111827;padding-top:0!important;--jcp-header-height:0px;--jcp-header-stack-height:0px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif}';
	echo 'body.jcp-landing-chrome-hidden .directory-header,body.jcp-landing-chrome-hidden .jcp-top-banner,body.jcp-landing-chrome-hidden .mobile-menu-overlay,body.jcp-landing-chrome-hidden footer.jcp-footer{display:none!important}';
	echo '.jcp-landing-brandbar{display:flex;align-items:center;justify-content:center;min-height:56px;padding:.55rem 1rem;background:rgba(255,255,255,.96);border-bottom:1px solid #e5e7eb;position:sticky;top:0;z-index:50}';
	echo '.jcp-landing-brandbar__inner{display:flex;align-items:center;justify-content:center;gap:.75rem;width:100%;max-width:72rem;margin-inline:auto}';
	echo '.jcp-landing-brandbar__logo{display:block;height:34px;width:auto}';
	echo '.jcp-landing-brandbar__cta{opacity:0;pointer-events:none}';
	echo '.jcp-page-campaign .jcp-block-root{opacity:1;transform:none}';
	echo '.jcp-hero,.jcp-page-campaign .directory-hero{padding:1.25rem 1rem 2rem;background:#fff}';
	echo '.jcp-hero-grid{display:grid;gap:1.5rem;align-items:center;max-width:72rem;margin:0 auto}';
	echo '@media(min-width:900px){.jcp-hero-grid{grid-template-columns:1.05fr .95fr;gap:2rem;padding:0 1rem}}';
	echo '.jcp-hero h1,.jcp-page-campaign .directory-hero h1{font-size:clamp(1.75rem,4.2vw,2.75rem);line-height:1.12;letter-spacing:-.02em;margin:0 0 .75rem;font-weight:800;color:#111827}';
	echo '.jcp-hero p,.jcp-page-campaign .jcp-hero-sub{font-size:1.05rem;line-height:1.5;color:#4b5563;margin:0 0 1rem;max-width:36rem}';
	echo '.btn-primary,.jcp-page-campaign .btn-primary{display:inline-flex;align-items:center;justify-content:center;min-height:3rem;padding:.85rem 1.25rem;border-radius:999px;background:#ff503e;color:#fff!important;font-weight:750;text-decoration:none;border:0}';
	/* Meta stats: reserve flex layout before async sections.css arrives. */
	echo '.jcp-page-campaign .jcp-meta-stats,.jcp-page-campaign .directory-meta{display:flex;gap:1rem;margin-top:1rem;width:100%;align-items:flex-start;flex-wrap:nowrap}';
	echo '.jcp-page-campaign .jcp-meta-stats .meta-item,.jcp-page-campaign .directory-meta .meta-item{display:flex;gap:.75rem;flex:1 1 0;min-width:0}';
	echo '@media(max-width:480px){.jcp-page-campaign .jcp-meta-stats,.jcp-page-campaign .directory-meta{flex-direction:column;gap:.75rem}}';
	/*
	 * Story phone: keep column visible + lock final sizes with !important so
	 * later demo-app-phone / niche-landing width rules cannot thrash CLS.
	 * Do NOT override .jcp-story-scene display.
	 */
	echo '.jcp-page-campaign .jcp-hero-visual-column:has(.jcp-story-phone),.jcp-page-campaign .jcp-hero-visual:has(.jcp-story-phone){display:block!important}';
	echo '.jcp-page-campaign .jcp-story-phone{display:flex;flex-direction:column;align-items:center;width:100%;max-width:360px;margin-inline:auto}';
	echo '.jcp-page-campaign .jcp-story-phone__device.hero-phone-mockup,.jcp-page-campaign .demo-preview-phone-mockup.hero-phone-mockup{display:block!important;width:min(100%,300px)!important;max-width:300px!important;margin-inline:auto;opacity:1!important;transform:none!important;animation:none!important}';
	echo '.jcp-page-campaign .jcp-story-phone__device .phone-screen,.jcp-page-campaign .demo-preview-phone-mockup .phone-screen{aspect-ratio:9/19.5!important;width:100%!important;height:auto!important}';
	echo '.jcp-page-campaign .jcp-story-phone__caption{min-height:2.6em}';
	echo '@media(min-width:769px){.jcp-page-campaign .jcp-hero-visual-column:has(.jcp-story-phone),.jcp-page-campaign .jcp-story-phone{min-height:620px}}';
	echo '@media(max-width:768px){';
	echo '.jcp-page-campaign .jcp-hero-visual-column:has(.jcp-story-phone),.jcp-page-campaign .jcp-hero-visual:has(.jcp-story-phone),.jcp-page-campaign .jcp-story-phone{display:flex!important;justify-content:center}';
	echo '.jcp-page-campaign .jcp-hero-visual-column:has(.jcp-story-phone),.jcp-page-campaign .jcp-story-phone{min-height:560px}';
	echo '.jcp-page-campaign .jcp-story-phone{max-width:320px}';
	echo '.jcp-page-campaign .jcp-story-phone__device.hero-phone-mockup,.jcp-page-campaign .demo-preview-phone-mockup.hero-phone-mockup{width:min(100%,260px)!important;max-width:260px!important}';
	echo '}';
	echo '</style>' . "\n";
	// Neutralize delayed Rocket header-height script before it can run.
	echo '<script>document.documentElement.style.setProperty("--jcp-header-stack-height","0px");document.documentElement.style.setProperty("--jcp-header-height","0px");</script>' . "\n";
}
add_action( 'wp_head', 'jcp_core_campaign_lp_inline_critical_css', 2 );

/**
 * Async-load theme CSS on campaign LPs (critical CSS is inlined above).
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 */
function jcp_core_campaign_lp_async_secondary_css( string $html, string $handle ): string {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return $html;
	}

	$async_handles = [
		// Keep layout/base/sections/niche BLOCKING — async arrival was resizing
		// the hero phone column after first paint (CLS 0.3–0.5 on mobile).
		// hero-live-demo + demo-app-phone stay blocking for the story phone.
		'jcp-core-buttons',
		'jcp-core-components',
		'jcp-core-utilities',
		'jcp-core-home',
		'jcp-core-story-moments',
		'jcp-core-content-prose',
		'jcp-core-case-study-cohort',
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
	if ( strpos( $async, 'noscript' ) === false && preg_match( '/href=[\'"]([^\'"]+)[\'"]/', $async, $m ) ) {
		$async .= '<noscript><link rel="stylesheet" href="' . esc_url( $m[1] ) . '"></noscript>';
	}
	return $async;
}
add_filter( 'style_loader_tag', 'jcp_core_campaign_lp_async_secondary_css', 25, 2 );

/**
 * Preload logo only — do not preload mid-loop phone frames (desyncs animation).
 */
function jcp_core_campaign_lp_preload_lcp(): void {
	if ( ! jcp_core_is_campaign_lp_request() ) {
		return;
	}

	$logo = get_template_directory_uri() . '/assets/brand/jcp-logo-dark-320.webp';
	echo '<link rel="preload" as="image" type="image/webp" href="' . esc_url( $logo ) . '" fetchpriority="high">' . "\n";
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
	if ( strpos( $url, 'data:' ) === 0 ) {
		return $url;
	}
	if ( ! preg_match( '#^(https?://[^/]+)?(/[^"\']+/assets/campaign/)([^/"\']+?)(-(64|192|360|640))?(\.webp|\.jpe?g)(\?[^"\']*)?$#i', $url, $m ) ) {
		return $url;
	}

	$prefix = ( $m[1] ?? '' ) . $m[2];
	$base   = $m[3];
	$query  = $m[7] ?? '';

	$base = preg_replace( '/-(64|192|360|640)$/', '', $base ) ?? $base;

	$suffix = '';
	if ( $width > 0 && $width <= 64 ) {
		$suffix = '-64';
	} elseif ( $width > 0 && $width <= 120 ) {
		$suffix = '-192';
	} elseif ( $width > 0 && $width <= 520 ) {
		// Benefit cards declare width=480 but display ~372 CSS px.
		$suffix = '-360';
	} elseif ( $width > 0 && $width <= 720 ) {
		$suffix = '-640';
	}

	$dir        = trailingslashit( get_template_directory() ) . 'assets/campaign/';
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

	$html = preg_replace_callback(
		'/<img\b([^>]*)>/i',
		static function ( array $m ): string {
			$attrs = $m[1];
			if ( strpos( $attrs, '/assets/campaign/' ) === false ) {
				return $m[0];
			}

			$attrs = rtrim( $attrs );
			$attrs = preg_replace( '/\s*\/\s*$/', '', $attrs ) ?? $attrs;

			$width = 0;
			if ( preg_match( '/\bwidth=["\'](\d+)["\']/', $attrs, $wm ) ) {
				$width = (int) $wm[1];
			}

			$attrs = preg_replace_callback(
				'/\b(src|data-lazy-src|data-src)=(["\'])([^"\']+)\2/i',
				static function ( array $am ) use ( $width ): string {
					$raw = $am[3];
					if ( strpos( $raw, 'data:' ) === 0 ) {
						return $am[0];
					}
					$opt = jcp_core_campaign_lp_optimize_asset_url( $raw, $width );
					return $am[1] . '=' . $am[2] . esc_attr( $opt ) . $am[2];
				},
				$attrs
			);

			if ( $width > 0 && $width <= 96 ) {
				if ( ! preg_match( '/\bloading=/i', $attrs ) ) {
					$attrs .= ' loading="lazy"';
				}
				if ( ! preg_match( '/\bfetchpriority=/i', $attrs ) ) {
					$attrs .= ' fetchpriority="low"';
				}
			}

			if ( strpos( $attrs, 'jcp-story-camera__img' ) !== false ) {
				$attrs = preg_replace( '/\bfetchpriority=["\'][^"\']*["\']/i', 'fetchpriority="low"', $attrs ) ?? $attrs;
				$attrs = preg_replace( '/\bloading=["\'][^"\']*["\']/i', 'loading="lazy"', $attrs ) ?? $attrs;
				if ( ! preg_match( '/\bloading=/i', $attrs ) ) {
					$attrs .= ' loading="lazy"';
				}
			}

			if ( strpos( $attrs, 'benefits.items.0.image_url' ) !== false ) {
				$attrs = preg_replace( '/\bfetchpriority=["\'][^"\']*["\']/i', 'fetchpriority="low"', $attrs ) ?? $attrs;
				if ( ! preg_match( '/\bfetchpriority=/i', $attrs ) ) {
					$attrs .= ' fetchpriority="low"';
				}
				// Match displayed ~372px cards — avoid declaring 480x360 with a 360w file.
				$attrs = preg_replace( '/\bwidth=["\']\d+["\']/', 'width="360"', $attrs, 1 ) ?? $attrs;
				$attrs = preg_replace( '/\bheight=["\']\d+["\']/', 'height="240"', $attrs, 1 ) ?? $attrs;
			}

			if ( $width >= 400 && preg_match( '/benefits\.items\.[1-9]/', $attrs ) ) {
				if ( preg_match( '/\bloading=["\']eager["\']/i', $attrs ) ) {
					$attrs = preg_replace( '/\bloading=["\']eager["\']/i', 'loading="lazy"', $attrs, 1 ) ?? $attrs;
				} elseif ( ! preg_match( '/\bloading=/i', $attrs ) ) {
					$attrs .= ' loading="lazy"';
				}
				$attrs = preg_replace( '/\bwidth=["\']\d+["\']/', 'width="360"', $attrs, 1 ) ?? $attrs;
				$attrs = preg_replace( '/\bheight=["\']\d+["\']/', 'height="240"', $attrs, 1 ) ?? $attrs;
			}

			return '<img' . $attrs . '>';
		},
		$html
	) ?? $html;

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
 * Drop Rocket map-bg lazy pairs on campaign LPs.
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
	// Prevent delayed header-stack measurement from fighting campaign padding:0.
	$html = preg_replace(
		'#document\.documentElement\.style\.setProperty\(\s*[\'"]--jcp-header-stack-height[\'"]\s*,\s*height\s*\+\s*[\'"]px[\'"]\s*\)#',
		'document.documentElement.style.setProperty("--jcp-header-stack-height","0px")',
		$html
	) ?? $html;
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
