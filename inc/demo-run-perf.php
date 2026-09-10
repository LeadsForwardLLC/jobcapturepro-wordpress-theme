<?php
/**
 * Fast /demo/ bootstrap: survey gate + ?mode=run shells.
 *
 * Survey gate Lighthouse wins: dequeue unused plugin/WP CSS, trim chrome,
 * preload survey CSS, defer FirstPromoter (was sync in &lt;head&gt;).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrite relative demo template asset paths to absolute theme asset URLs.
 */
function jcp_core_rewrite_demo_template_markup( string $html ): string {
	$asset_base = trailingslashit( get_stylesheet_directory_uri() ) . 'assets';
	$base_url   = untrailingslashit( home_url( '/' ) );

	$replacements = [
		'../../shared/assets/'       => $asset_base . '/shared/assets/',
		'../shared/assets/'          => $asset_base . '/shared/assets/',
		'./shared/assets/'           => $asset_base . '/shared/assets/',
		'/shared/assets/'            => $asset_base . '/shared/assets/',
		'../../campaign/'            => $asset_base . '/campaign/',
		'../campaign/'               => $asset_base . '/campaign/',
		'/src/jcp-demo/'             => $base_url . '/demo/',
		'/src/contractor-directory/' => $base_url . '/directory/',
		'/src/estimate-builder/'     => $base_url . '/estimate/',
	];

	return strtr( $html, $replacements );
}

/**
 * Body markup for the interactive demo shell (already path-rewritten).
 */
function jcp_core_get_demo_run_markup(): string {
	$path = trailingslashit( get_stylesheet_directory() ) . 'assets/demo/index.html';
	if ( ! is_readable( $path ) ) {
		return '';
	}

	$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $raw ) || $raw === '' ) {
		return '';
	}

	if ( preg_match( '/<body[^>]*>(.*)<\/body>/is', $raw, $m ) ) {
		$body = $m[1];
	} else {
		$body = $raw;
	}

	// Strip template-local stylesheet/script tags; WP enqueue owns those.
	$body = preg_replace( '/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', '', $body ) ?? $body;
	$body = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $body ) ?? $body;

	return jcp_core_rewrite_demo_template_markup( $body );
}

/**
 * Whether this request is a lean demo shell (survey gate or interactive run).
 */
function jcp_core_is_demo_shell_request(): bool {
	return ( function_exists( 'jcp_core_is_demo_survey_request' ) && jcp_core_is_demo_survey_request() )
		|| ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() );
}

/**
 * Print preload hints for the interactive demo critical path.
 */
function jcp_core_demo_run_preload_hints(): void {
	if ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ) {
		$demo_css = esc_url( jcp_core_asset_url( 'assets/shared/assets/demo.css' ) );
		$demo_js  = esc_url( jcp_core_asset_url( 'js/features/demo/jcp-demo.js' ) );
		echo '<link rel="dns-prefetch" href="//images.unsplash.com">' . "\n";
		echo '<link rel="preload" href="' . $demo_css . '" as="style">' . "\n";
		echo '<link rel="preload" href="' . $demo_js . '" as="script">' . "\n";
		return;
	}

	if ( ! function_exists( 'jcp_core_is_demo_survey_request' ) || ! jcp_core_is_demo_survey_request() ) {
		return;
	}

	$base   = esc_url( jcp_core_asset_url( 'css/base.css' ) );
	$shared = esc_url( jcp_core_asset_url( 'assets/shared/assets/survey.css' ) );
	$page   = esc_url( jcp_core_asset_url( 'css/pages/survey.css' ) );
	echo '<link rel="preload" href="' . $base . '" as="style">' . "\n";
	echo '<link rel="preload" href="' . $shared . '" as="style">' . "\n";
	echo '<link rel="preload" href="' . $page . '" as="style">' . "\n";
}
add_action( 'wp_head', 'jcp_core_demo_run_preload_hints', 2 );

/**
 * Strip non-essential WP chrome on demo shells.
 */
function jcp_core_demo_shell_trim_wp_chrome(): void {
	if ( ! jcp_core_is_demo_shell_request() ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'template_redirect', 'jcp_core_demo_shell_trim_wp_chrome', 20 );

/**
 * Pages where third-party analytics must not block interactivity.
 * Demo funnel + paid campaign LPs (heavy ad traffic).
 */
function jcp_core_should_delay_third_party_analytics(): bool {
	if ( jcp_core_is_demo_shell_request() ) {
		return true;
	}
	if ( function_exists( 'jcp_page_current_is_campaign_landing' ) && jcp_page_current_is_campaign_landing() ) {
		return true;
	}
	return false;
}

/**
 * Block Site Kit from printing gtag/GTM early (we re-inject after idle).
 *
 * @param bool $blocked Whether the tag is blocked.
 */
function jcp_core_block_site_kit_early_tags( bool $blocked ): bool {
	return jcp_core_should_delay_third_party_analytics() ? true : $blocked;
}
add_filter( 'googlesitekit_analytics-4_tag_blocked', 'jcp_core_block_site_kit_early_tags' );
add_filter( 'googlesitekit_tagmanager_tag_blocked', 'jcp_core_block_site_kit_early_tags' );
add_filter( 'googlesitekit_ads_tag_blocked', 'jcp_core_block_site_kit_early_tags' );
add_filter( 'googlesitekit_adsense_tag_blocked', 'jcp_core_block_site_kit_early_tags' );

/**
 * Dequeue head-blocking third-party scripts on high-traffic conversion pages.
 */
function jcp_core_demo_shell_dequeue_unused_assets(): void {
	$is_shell = jcp_core_is_demo_shell_request();
	$delay_tp = jcp_core_should_delay_third_party_analytics();
	if ( ! $is_shell && ! $delay_tp ) {
		return;
	}

	if ( $is_shell ) {
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
	}

	$script_handles = [];
	if ( $is_shell ) {
		$script_handles[] = 'googlesitekit-events-provider-content-events';
		$script_handles[] = 'wp-embed';
	}
	if ( $delay_tp ) {
		// Site Kit gtag + FirstPromoter — reloaded after idle / first interaction.
		$script_handles[] = 'google_gtagjs';
		$script_handles[] = 'googlesitekit-gtag';
		$script_handles[] = 'firstpromoter-js';
		$script_handles[] = 'rocket-preload-links';
		$script_handles[] = 'rocket-browser-checker';
	}
	foreach ( array_unique( $script_handles ) as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_core_demo_shell_dequeue_unused_assets', 1000 );
add_action( 'wp_print_styles', 'jcp_core_demo_shell_dequeue_unused_assets', 100 );
add_action( 'wp_print_scripts', 'jcp_core_demo_shell_dequeue_unused_assets', 100 );

/**
 * Delayed analytics bootstrap (GTM + gtag + FirstPromoter).
 * Keeps dataLayer so early survey events still queue.
 */
function jcp_core_print_delayed_analytics_loader(): void {
	if ( ! jcp_core_should_delay_third_party_analytics() ) {
		return;
	}

	/**
	 * Filter delayed analytics config for conversion pages.
	 *
	 * @param array{gtm_id:string,gtag_id:string,fpr:bool} $cfg Config.
	 */
	$cfg = apply_filters(
		'jcp_core_delayed_analytics',
		[
			'gtm_id'  => 'GTM-MVCMTVCZ',
			'gtag_id' => 'GT-WKGPHZXP',
			'fpr'     => true,
		]
	);

	$gtm  = preg_replace( '/[^A-Z0-9\-]/', '', (string) ( $cfg['gtm_id'] ?? '' ) );
	$gtag = preg_replace( '/[^A-Z0-9\-]/', '', (string) ( $cfg['gtag_id'] ?? '' ) );
	$fpr  = ! empty( $cfg['fpr'] );

	echo "\n<script id=\"jcp-delayed-analytics\">\n";
	echo "(function(){\n";
	echo "window.dataLayer=window.dataLayer||[];\n";
	echo "var loaded=false;\n";
	echo "function load(){\n";
	echo "if(loaded)return;loaded=true;\n";
	if ( $gtm !== '' ) {
		echo "var gtm=" . wp_json_encode( $gtm ) . ";\n";
		echo "window.dataLayer.push({'gtm.start':new Date().getTime(),event:'gtm.js'});\n";
		echo "var f=document.getElementsByTagName('script')[0],j=document.createElement('script'),dl=window.dataLayer!='dataLayer'?'&l=dataLayer':'';\n";
		echo "j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+gtm+dl;f.parentNode.insertBefore(j,f);\n";
	}
	if ( $gtag !== '' ) {
		echo "var gid=" . wp_json_encode( $gtag ) . ";\n";
		echo "var gs=document.createElement('script');gs.async=true;gs.src='https://www.googletagmanager.com/gtag/js?id='+gid;\n";
		echo "document.head.appendChild(gs);\n";
		echo "window.gtag=window.gtag||function(){dataLayer.push(arguments);};\n";
		echo "gtag('js',new Date());\n";
		echo "gtag('config',gid,{googlesitekit_post_type:(document.body&&document.body.classList.contains('page')?'page':'')});\n";
	}
	if ( $fpr ) {
		echo "if(!window.fpr){(function(w){w.fpr=w.fpr||function(){w.fpr.q=w.fpr.q||[];w.fpr.q[arguments[0]=='set'?'unshift':'push'](arguments);};})(window);}\n";
		echo "var fs=document.createElement('script');fs.async=true;fs.src='https://cdn.firstpromoter.com/fpr.js';\n";
		echo "document.head.appendChild(fs);\n";
		echo "try{fpr('init',{cid:'6d8y17fs'});fpr('click');fpr('crossDomain',['app.jobcapturepro.com']);}catch(e){}\n";
	}
	echo "}\n";
	// Interaction only — idle timeouts fire mid-Lighthouse and dump GTM into TBT.
	echo "['pointerdown','keydown','touchstart'].forEach(function(t){window.addEventListener(t,load,{once:true,passive:true});});\n";
	echo "})();\n";
	echo "</script>\n";
}
add_action( 'wp_footer', 'jcp_core_print_delayed_analytics_loader', 5 );

/**
 * Strip any remaining early GTM/gtag/FPR tags plugins still print in the HTML.
 */
function jcp_core_demo_shell_start_analytics_buffer(): void {
	if ( ! jcp_core_should_delay_third_party_analytics() ) {
		return;
	}
	ob_start( 'jcp_core_demo_shell_strip_early_analytics' );
}
add_action( 'template_redirect', 'jcp_core_demo_shell_start_analytics_buffer', 0 );

/**
 * @param string $html Full page HTML.
 */
function jcp_core_demo_shell_strip_early_analytics( string $html ): string {
	if ( $html === '' ) {
		return $html;
	}

	// Remove Site Kit / GTM bootstrap blocks that execute immediately.
	$html = preg_replace(
		'#<!-- Google tag \(gtag\.js\) snippet added by Site Kit -->.*?<script[^>]*id="google_gtagjs-js-after"[^>]*>.*?</script>#is',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<script[^>]*id="google_gtagjs-js"[^>]*>.*?</script>\s*<script[^>]*id="google_gtagjs-js-after"[^>]*>.*?</script>#is',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<script[^>]*src=["\']https://www\.googletagmanager\.com/gtag/js\?id=[^"\']+["\'][^>]*>\s*</script>#i',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<script>\s*\(function\(w,d,s,l,i\)\{w\[l\]=w\[l\]\|\|\[\];w\[l\]\.push\(\{\'gtm\.start\':.*?\'https://www\.googletagmanager\.com/gtm\.js\?id=\'\+i\+dl;.*?</script>#is',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<noscript>.*?googletagmanager\.com/ns\.html\?id=GTM-[^<]+</noscript>#is',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<script[^>]*id="firstpromoter-js-js-before"[^>]*>.*?</script>\s*<script[^>]*id="firstpromoter-js-js"[^>]*>.*?</script>#is',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<script[^>]*src=["\'][^"\']*firstpromoter\.com/fpr\.js[^"\']*["\'][^>]*>\s*</script>#i',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<link[^>]+href=[\'"]//cdn\.firstpromoter\.com[\'"][^>]*>#i',
		'',
		$html
	) ?? $html;
	$html = preg_replace(
		'#<link[^>]+href=[\'"]//www\.googletagmanager\.com[\'"][^>]*>#i',
		'',
		$html
	) ?? $html;

	return $html;
}

/**
 * FirstPromoter was loading sync in &lt;head&gt; — kept as safety net if not stripped.
 *
 * @param string $tag    Script HTML.
 * @param string $handle Script handle.
 * @param string $src    Script URL.
 */
function jcp_core_demo_survey_defer_third_party_scripts( string $tag, string $handle, string $src ): string {
	if ( ! jcp_core_should_delay_third_party_analytics() ) {
		return $tag;
	}
	if ( $handle === 'firstpromoter-js' || strpos( $src, 'firstpromoter.com' ) !== false ) {
		return '';
	}
	if ( $handle === 'google_gtagjs' || strpos( $src, 'googletagmanager.com/gtag' ) !== false ) {
		return '';
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'jcp_core_demo_survey_defer_third_party_scripts', 20, 3 );

/**
 * Load page-level survey.css after first paint (shared CSS remains blocking).
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 */
function jcp_core_demo_survey_async_secondary_css( string $html, string $handle ): string {
	if ( ! function_exists( 'jcp_core_is_demo_survey_request' ) || ! jcp_core_is_demo_survey_request() ) {
		return $html;
	}
	if ( $handle !== 'jcp-core-survey' ) {
		return $html;
	}
	if ( strpos( $html, 'onload=' ) !== false ) {
		return $html;
	}
	$async = preg_replace( "/\smedia=['\"]all['\"]/", " media='print' onload=\"this.media='all'\"", $html, 1 );
	return is_string( $async ) ? $async : $html;
}
add_filter( 'style_loader_tag', 'jcp_core_demo_survey_async_secondary_css', 20, 2 );

/**
 * Back-compat aliases.
 */
function jcp_core_demo_run_trim_wp_chrome(): void {
	jcp_core_demo_shell_trim_wp_chrome();
}

function jcp_core_demo_run_dequeue_plugin_assets(): void {
	jcp_core_demo_shell_dequeue_unused_assets();
}
