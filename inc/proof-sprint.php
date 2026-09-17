<?php
/**
 * Proof Sprint paid acquisition funnel — /proof-sprint/
 *
 * Native rebuild of assets/shared/reference-funnel using theme systems.
 * Acculevel case study is presented anonymously (multi-location foundation company).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JCP_PROOF_SPRINT_SLUG', 'proof-sprint' );
define( 'JCP_PROOF_SPRINT_VARIANT', 'proof_sprint' );
define( 'JCP_PROOF_SPRINT_SEED_VERSION', '1' );

/**
 * Request path without leading/trailing slashes.
 */
function jcp_proof_sprint_request_path(): string {
	return trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}

/**
 * Whether the raw request is /proof-sprint/.
 */
function jcp_proof_sprint_is_path(): bool {
	return jcp_proof_sprint_request_path() === JCP_PROOF_SPRINT_SLUG;
}

/**
 * Whether the current request should load the Proof Sprint funnel.
 */
function jcp_proof_sprint_is_current(): bool {
	if ( jcp_proof_sprint_is_path() ) {
		return true;
	}
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( (string) $post->post_name === JCP_PROOF_SPRINT_SLUG && (int) $post->post_parent === 0 ) {
		return true;
	}
	return (string) get_page_template_slug( (int) $post->ID ) === 'page-proof-sprint.php';
}

/**
 * Funnel URL.
 *
 * @param array<string, string> $args Query args.
 */
function jcp_proof_sprint_url( array $args = [] ): string {
	$url = home_url( '/' . JCP_PROOF_SPRINT_SLUG . '/' );
	if ( $args !== [] ) {
		$url = add_query_arg( $args, $url );
	}
	return $url;
}

/**
 * Campaign asset URL helper.
 */
function jcp_proof_sprint_asset_url( string $file ): string {
	$file = ltrim( $file, '/' );
	$base = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
	$url  = $base . $file;
	if ( function_exists( 'jcp_core_campaign_lp_optimize_asset_url' ) ) {
		$opt = jcp_core_campaign_lp_optimize_asset_url( $url );
		if ( is_string( $opt ) && $opt !== '' ) {
			return $opt;
		}
	}
	return $url;
}

/**
 * Anonymous Local Falcon case-study props (canonical Acculevel metrics, no brand name).
 *
 * @return array<string, mixed>
 */
function jcp_proof_sprint_case_study_props(): array {
	$campaign = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';

	return [
		'eyebrow'       => __( 'Real Google Maps visibility', 'jcp-core' ),
		'headline'      => __( 'From invisible across the map to showing up across the market.', 'jcp-core' ),
		'body'          => __( 'During a roughly 12-week period using real completed-job proof as part of a multi-location foundation company’s local-search strategy (LeadsForward + JobCapturePro), tracked visibility improved substantially across two markets.', 'jcp-core' ),
		'stats'         => [
			[
				'value' => '0% → 90%',
				'label' => __( 'Triadelphia · Foundation repair', 'jcp-core' ),
			],
			[
				'value' => '0% → 100%',
				'label' => __( 'Triadelphia · Basement waterproofing', 'jcp-core' ),
			],
			[
				'value' => '0% → 84%',
				'label' => __( 'Monroe · Foundation repair', 'jcp-core' ),
			],
			[
				'value' => '0% → 96%',
				'label' => __( 'Monroe · Basement waterproofing', 'jcp-core' ),
			],
		],
		'locations'     => [
			[
				'name'    => __( 'Triadelphia, WV', 'jcp-core' ),
				'address' => __( 'Real service area on Google Maps', 'jcp-core' ),
				'meta'    => __( 'Across town · ~6 mile area', 'jcp-core' ),
				'map_bg'  => $campaign . 'lf-map-triadelphia.jpg',
				'scans'   => [
					[
						'keyword'    => __( 'foundation repair', 'jcp-core' ),
						'grid_label' => __( 'Share of Local Voice (SoLV)', 'jcp-core' ),
						'before'     => [
							'date'    => __( 'March', 'jcp-core' ),
							'solv'    => '0%',
							'summary' => __( 'Not showing prominently', 'jcp-core' ),
							'pattern' => 'before_blank',
						],
						'after'      => [
							'date'    => __( 'June', 'jcp-core' ),
							'solv'    => '90%',
							'summary' => __( 'Showing across more of the tracked market', 'jcp-core' ),
							'pattern' => 'after_fr_wv',
						],
					],
				],
			],
			[
				'name'    => __( 'Monroe, MI', 'jcp-core' ),
				'address' => __( 'Real service area on Google Maps', 'jcp-core' ),
				'meta'    => __( 'Across town · ~6 mile area', 'jcp-core' ),
				'map_bg'  => $campaign . 'lf-map-monroe.jpg',
				'scans'   => [
					[
						'keyword'    => __( 'foundation repair', 'jcp-core' ),
						'grid_label' => __( 'Share of Local Voice (SoLV)', 'jcp-core' ),
						'before'     => [
							'date'    => __( 'March', 'jcp-core' ),
							'solv'    => '0%',
							'summary' => __( 'Not showing prominently', 'jcp-core' ),
							'pattern' => 'before_blank',
						],
						'after'      => [
							'date'    => __( 'June', 'jcp-core' ),
							'solv'    => '84%',
							'summary' => __( 'Showing across more of the tracked market', 'jcp-core' ),
							'pattern' => 'after_fr_mi',
						],
					],
				],
			],
		],
		'footnote'      => __( 'SoLV = percentage of tracked Local Falcon grid points where the business appeared in the Google Maps 3-Pack across the measured service area. Past performance does not guarantee future rankings.', 'jcp-core' ),
		'cta_secondary' => [
			'label' => '',
			'url'   => '',
		],
		'section_id'    => 'ps-proof',
		'show_eyebrow'  => true,
		'show_headline' => true,
		'show_body'     => true,
		'show_stats'    => true,
		'show_cta'      => false,
	];
}

/**
 * Seed / repair the Proof Sprint page.
 */
function jcp_proof_sprint_maybe_seed(): void {
	$ver  = (string) get_option( 'jcp_proof_sprint_seed_version', '' );
	$page = get_page_by_path( JCP_PROOF_SPRINT_SLUG );

	if ( ! ( $page instanceof WP_Post ) || $ver !== JCP_PROOF_SPRINT_SEED_VERSION ) {
		$postarr = [
			'post_title'   => 'Proof Sprint',
			'post_name'    => JCP_PROOF_SPRINT_SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_excerpt' => 'Your crew is already creating your marketing. JobCapturePro puts it to work.',
		];
		if ( $page instanceof WP_Post ) {
			$postarr['ID'] = (int) $page->ID;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}
		if ( ! is_wp_error( $post_id ) && $post_id ) {
			update_post_meta( (int) $post_id, '_wp_page_template', 'page-proof-sprint.php' );
			update_post_meta( (int) $post_id, '_jcp_campaign_variant', JCP_PROOF_SPRINT_VARIANT );
			update_post_meta( (int) $post_id, '_yoast_wpseo_meta-robots-noindex', '1' );
		}
	} elseif ( $page instanceof WP_Post ) {
		$tpl = (string) get_page_template_slug( (int) $page->ID );
		if ( $tpl !== 'page-proof-sprint.php' ) {
			update_post_meta( (int) $page->ID, '_wp_page_template', 'page-proof-sprint.php' );
		}
	}

	if ( $ver !== JCP_PROOF_SPRINT_SEED_VERSION ) {
		flush_rewrite_rules( false );
	}
	update_option( 'jcp_proof_sprint_seed_version', JCP_PROOF_SPRINT_SEED_VERSION, false );
}
add_action( 'init', 'jcp_proof_sprint_maybe_seed', 27 );

/**
 * Force Proof Sprint template when path matches.
 *
 * @param string $template Template path.
 */
function jcp_proof_sprint_force_template( string $template ): string {
	if ( ! jcp_proof_sprint_is_path() && ! jcp_proof_sprint_is_current() ) {
		return $template;
	}
	$custom = trailingslashit( get_template_directory() ) . 'page-proof-sprint.php';
	return is_readable( $custom ) ? $custom : $template;
}
add_filter( 'template_include', 'jcp_proof_sprint_force_template', 98 );

/**
 * Mark body / html for analytics.
 */
function jcp_proof_sprint_body_class( array $classes ): array {
	if ( jcp_proof_sprint_is_current() ) {
		$classes[] = 'jcp-proof-sprint';
		$classes[] = 'jcp-landing-chrome-hidden';
		$classes[] = 'jcp-marketing';
		$classes[] = 'jcp-page-marketing';
		$classes[] = 'jcp-page-campaign';
		$classes[] = 'jcp-home';
	}
	return $classes;
}
add_filter( 'body_class', 'jcp_proof_sprint_body_class' );
