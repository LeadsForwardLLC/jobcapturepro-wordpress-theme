<?php
/**
 * WP Rocket compatibility — keep critical theme CSS/JS out of stale minify + Delay JS.
 *
 * Logged-in editors bypass Rocket page cache and see fresh assets. Anonymous
 * visitors get minified CSS from /cache/min/. That minify cache can lag behind
 * theme deploys, so demo-app-phone.css was serving a pre–story-phone build
 * (orbit chips unpositioned, scenes not hidden → PHOTO bleed). Delay JS also
 * deferred story-phone.js until interaction.
 *
 * @package JCP_Core
 */

/**
 * Paths/handles to exclude from Rocket CSS minify.
 *
 * @param string[] $excluded Excluded file patterns.
 * @return string[]
 */
function jcp_core_rocket_exclude_css( array $excluded ): array {
	// Match relative path fragments Rocket compares against stylesheet URLs.
	$excluded[] = '/themes/jobcapturepro-core/css/components/demo-app-phone.css';
	$excluded[] = '/themes/jobcapturepro-core/css/components/hero-live-demo.css';
	$excluded[] = '/themes/jobcapturepro-core/css/components/story-moments.css';
	$excluded[] = '/themes/jobcapturepro-core/css/pages/niche-landing.css';
	$excluded[] = '/themes/jobcapturepro-core/css/pages/home.css';
	return array_values( array_unique( $excluded ) );
}
add_filter( 'rocket_exclude_css', 'jcp_core_rocket_exclude_css' );

/**
 * Scripts that must run on first paint (story phone scenes, authority counts).
 *
 * @param string[] $excluded Delay JS exclusion patterns.
 * @return string[]
 */
function jcp_core_rocket_delay_js_exclusions( array $excluded ): array {
	$excluded[] = 'jcp-core-story-phone';
	$excluded[] = 'story-phone.js';
	$excluded[] = 'jcp-core-authority';
	$excluded[] = 'authority.js';
	$excluded[] = 'jcp-core-home-interactions';
	$excluded[] = 'home-interactions.js';
	// Campaign LP: blocks start at opacity:0 until campaign.js marks .is-visible.
	$excluded[] = 'jcp-core-story-moments';
	$excluded[] = 'story-moments.js';
	$excluded[] = 'jcp-core-campaign';
	$excluded[] = 'campaign.js';
	$excluded[] = 'jcp-core-testimonials';
	$excluded[] = 'testimonials.js';
	$excluded[] = 'jcp-core-attribution';
	$excluded[] = 'jcp-attribution.js';
	// Demo funnel: gate submit + live demo must not wait for first interaction.
	$excluded[] = 'jcp-core-survey';
	$excluded[] = 'survey.js';
	$excluded[] = 'jcp-demo';
	$excluded[] = 'jcp-demo.js';
	return array_values( array_unique( $excluded ) );
}
add_filter( 'rocket_delay_js_exclusions', 'jcp_core_rocket_delay_js_exclusions' );

/**
 * Also exclude from JS minify combine so Delay exclusions stay reliable.
 *
 * @param string[] $excluded Excluded JS patterns.
 * @return string[]
 */
function jcp_core_rocket_exclude_js( array $excluded ): array {
	// Helpers resolve missing theme-root paths under /assets/.
	$excluded[] = '/themes/jobcapturepro-core/js/pages/story-phone.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/story-phone.js';
	$excluded[] = '/themes/jobcapturepro-core/js/pages/authority.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/authority.js';
	$excluded[] = '/themes/jobcapturepro-core/js/pages/home-interactions.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/home-interactions.js';
	$excluded[] = '/themes/jobcapturepro-core/js/pages/story-moments.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/story-moments.js';
	$excluded[] = '/themes/jobcapturepro-core/js/pages/campaign.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/campaign.js';
	$excluded[] = '/themes/jobcapturepro-core/js/pages/testimonials.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/testimonials.js';
	$excluded[] = '/themes/jobcapturepro-core/js/core/jcp-attribution.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/core/jcp-attribution.js';
	$excluded[] = '/themes/jobcapturepro-core/js/pages/survey.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/pages/survey.js';
	$excluded[] = '/themes/jobcapturepro-core/js/features/demo/jcp-demo.js';
	$excluded[] = '/themes/jobcapturepro-core/assets/js/features/demo/jcp-demo.js';
	return array_values( array_unique( $excluded ) );
}
add_filter( 'rocket_exclude_js', 'jcp_core_rocket_exclude_js' );

/**
 * One-shot Rocket cache purge after this compatibility fix lands.
 * Bump $bust when critical front-end assets change and anonymous CSS must refresh.
 */
function jcp_core_rocket_bust_stale_minify(): void {
	$bust = '2026-08-30-story-moments-channels-v1';
	if ( get_option( 'jcp_core_rocket_bust' ) === $bust ) {
		return;
	}
	update_option( 'jcp_core_rocket_bust', $bust, false );

	if ( function_exists( 'rocket_clean_minify' ) ) {
		rocket_clean_minify();
	}
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}
}
add_action( 'init', 'jcp_core_rocket_bust_stale_minify', 20 );
