<?php
/**
 * Proof Gap Survey paid acquisition funnel — /proof-gap/
 *
 * Phase 1 foundation only. Isolated from /demo/ and /proof-sprint/.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JCP_PROOF_GAP_SLUG', 'proof-gap' );
define( 'JCP_PROOF_GAP_VARIANT', 'proof_gap_survey_v1' );
define( 'JCP_PROOF_GAP_SURVEY_ID', 'proof_gap_survey_v1' );
define( 'JCP_PROOF_GAP_SURVEY_VERSION', '10' );
define( 'JCP_PROOF_GAP_SEED_VERSION', '11' );

/**
 * Request path without leading/trailing slashes.
 */
function jcp_proof_gap_request_path(): string {
	return trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}

/**
 * Whether the raw request is /proof-gap/.
 */
function jcp_proof_gap_is_path(): bool {
	return jcp_proof_gap_request_path() === JCP_PROOF_GAP_SLUG;
}

/**
 * Whether the current request should load the Proof Gap survey.
 */
function jcp_proof_gap_is_current(): bool {
	if ( jcp_proof_gap_is_path() ) {
		return true;
	}
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( (string) $post->post_name === JCP_PROOF_GAP_SLUG && (int) $post->post_parent === 0 ) {
		return true;
	}
	return (string) get_page_template_slug( (int) $post->ID ) === 'page-proof-gap.php';
}

/**
 * Funnel URL.
 *
 * @param array<string, string> $args Query args.
 */
function jcp_proof_gap_url( array $args = [] ): string {
	$url = home_url( '/' . JCP_PROOF_GAP_SLUG . '/' );
	if ( $args !== [] ) {
		$url = add_query_arg( $args, $url );
	}
	return $url;
}

/**
 * Seed / repair the Proof Gap page.
 */
function jcp_proof_gap_maybe_seed(): void {
	$ver  = (string) get_option( 'jcp_proof_gap_seed_version', '' );
	$page = get_page_by_path( JCP_PROOF_GAP_SLUG );

	if ( ! ( $page instanceof WP_Post ) || $ver !== JCP_PROOF_GAP_SEED_VERSION ) {
		$postarr = [
			'post_title'   => 'Proof Gap Survey',
			'post_name'    => JCP_PROOF_GAP_SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_excerpt' => 'Proof gap survey acquisition experiment (foundation).',
		];
		if ( $page instanceof WP_Post ) {
			$postarr['ID'] = (int) $page->ID;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}
		if ( ! is_wp_error( $post_id ) && $post_id ) {
			update_post_meta( (int) $post_id, '_wp_page_template', 'page-proof-gap.php' );
			update_post_meta( (int) $post_id, '_jcp_campaign_variant', JCP_PROOF_GAP_VARIANT );
			update_post_meta( (int) $post_id, '_yoast_wpseo_meta-robots-noindex', '1' );
			update_post_meta( (int) $post_id, '_yoast_wpseo_meta-robots-nofollow', '1' );
			update_post_meta( (int) $post_id, 'rank_math_robots', [ 'noindex', 'nofollow' ] );
		}
	} elseif ( $page instanceof WP_Post ) {
		$tpl = (string) get_page_template_slug( (int) $page->ID );
		if ( $tpl !== 'page-proof-gap.php' ) {
			update_post_meta( (int) $page->ID, '_wp_page_template', 'page-proof-gap.php' );
		}
		update_post_meta( (int) $page->ID, '_yoast_wpseo_meta-robots-noindex', '1' );
		update_post_meta( (int) $page->ID, '_yoast_wpseo_meta-robots-nofollow', '1' );
		update_post_meta( (int) $page->ID, 'rank_math_robots', [ 'noindex', 'nofollow' ] );
	}

	if ( $ver !== JCP_PROOF_GAP_SEED_VERSION ) {
		flush_rewrite_rules( false );
	}
	update_option( 'jcp_proof_gap_seed_version', JCP_PROOF_GAP_SEED_VERSION, false );
}
add_action( 'init', 'jcp_proof_gap_maybe_seed', 28 );

/**
 * Force Proof Gap template when path matches.
 *
 * @param string $template Template path.
 */
function jcp_proof_gap_force_template( string $template ): string {
	if ( ! jcp_proof_gap_is_path() && ! jcp_proof_gap_is_current() ) {
		return $template;
	}
	$custom = trailingslashit( get_template_directory() ) . 'page-proof-gap.php';
	return is_readable( $custom ) ? $custom : $template;
}
add_filter( 'template_include', 'jcp_proof_gap_force_template', 98 );

/**
 * Body classes for isolation + chrome hide.
 *
 * @param list<string> $classes Classes.
 * @return list<string>
 */
function jcp_proof_gap_body_class( array $classes ): array {
	if ( jcp_proof_gap_is_current() ) {
		$classes[] = 'jcp-proof-gap';
		$classes[] = 'jcp-landing-chrome-hidden';
		$classes[] = 'jcp-marketing';
	}
	return $classes;
}
add_filter( 'body_class', 'jcp_proof_gap_body_class' );

/**
 * Always noindex + nofollow the Proof Gap funnel.
 */
function jcp_proof_gap_robots_meta(): void {
	if ( ! jcp_proof_gap_is_current() ) {
		return;
	}
	echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}
add_action( 'wp_head', 'jcp_proof_gap_robots_meta', 1 );

/**
 * Core robots API: noindex + nofollow for Proof Gap.
 *
 * @param array<string, mixed> $robots Robots directives.
 * @return array<string, mixed>
 */
function jcp_proof_gap_wp_robots( $robots ) {
	if ( ! jcp_proof_gap_is_current() ) {
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
add_filter( 'wp_robots', 'jcp_proof_gap_wp_robots', 999 );

/**
 * Rank Math robots: noindex + nofollow for Proof Gap.
 *
 * @param array<string, string> $robots Robots directives.
 * @return array<string, string>
 */
function jcp_proof_gap_rank_math_robots( $robots ) {
	if ( ! jcp_proof_gap_is_current() ) {
		return $robots;
	}
	if ( ! is_array( $robots ) ) {
		$robots = [];
	}
	$robots['index']  = 'noindex';
	$robots['follow'] = 'nofollow';
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'jcp_proof_gap_rank_math_robots', 999 );

/**
 * Yoast robots: noindex + nofollow for Proof Gap.
 *
 * @param string $robots Robots content string.
 * @return string
 */
function jcp_proof_gap_yoast_robots( $robots ) {
	if ( ! jcp_proof_gap_is_current() ) {
		return $robots;
	}
	return 'noindex, nofollow';
}
add_filter( 'wpseo_robots', 'jcp_proof_gap_yoast_robots', 999 );

/**
 * Keep Proof Gap out of the core XML sitemap.
 *
 * @param array|false  $entry     Sitemap entry.
 * @param \WP_Post     $post      Post.
 * @param string       $post_type Post type.
 * @return array|false
 */
function jcp_proof_gap_exclude_from_wp_sitemap( $entry, $post, $post_type ) {
	if ( $post_type !== 'page' || ! ( $post instanceof WP_Post ) ) {
		return $entry;
	}
	if ( (string) $post->post_name === JCP_PROOF_GAP_SLUG && (int) $post->post_parent === 0 ) {
		return false;
	}
	if ( (string) get_page_template_slug( (int) $post->ID ) === 'page-proof-gap.php' ) {
		return false;
	}
	return $entry;
}
add_filter( 'wp_sitemaps_posts_entry', 'jcp_proof_gap_exclude_from_wp_sitemap', 10, 3 );

/**
 * Keep Proof Gap out of Rank Math sitemaps.
 *
 * @param array|false $url    Sitemap URL data.
 * @param string      $type   Object type.
 * @param mixed       $object Object.
 * @return array|false
 */
function jcp_proof_gap_exclude_from_rank_math_sitemap( $url, $type, $object ) {
	if ( $type !== 'post' || ! ( $object instanceof WP_Post ) || $object->post_type !== 'page' ) {
		return $url;
	}
	if ( (string) $object->post_name === JCP_PROOF_GAP_SLUG && (int) $object->post_parent === 0 ) {
		return false;
	}
	if ( (string) get_page_template_slug( (int) $object->ID ) === 'page-proof-gap.php' ) {
		return false;
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'jcp_proof_gap_exclude_from_rank_math_sitemap', 10, 3 );

/**
 * Trade option catalog (canonical key => label).
 *
 * @return array<string, string>
 */
function jcp_proof_gap_trade_options(): array {
	return [
		'hvac'           => __( 'HVAC', 'jcp-core' ),
		'plumbing'       => __( 'Plumbing', 'jcp-core' ),
		'electrical'     => __( 'Electrical', 'jcp-core' ),
		'roofing'        => __( 'Roofing', 'jcp-core' ),
		'remodeling'     => __( 'Remodeling', 'jcp-core' ),
		'painting'       => __( 'Painting', 'jcp-core' ),
		'landscaping'    => __( 'Landscaping', 'jcp-core' ),
		'garage_door'    => __( 'Garage door', 'jcp-core' ),
		'pest_control'   => __( 'Pest control', 'jcp-core' ),
		'tree_service'   => __( 'Tree service', 'jcp-core' ),
		'power_washing'  => __( 'Power washing', 'jcp-core' ),
		'other'          => __( 'Other', 'jcp-core' ),
	];
}

/**
 * Workflow options.
 *
 * @return array<string, string>
 */
function jcp_proof_gap_workflow_options(): array {
	return [
		'housecall_pro'           => __( 'Housecall Pro', 'jcp-core' ),
		'companycam'              => __( 'CompanyCam', 'jcp-core' ),
		'other_crm'               => __( 'Another CRM / field app', 'jcp-core' ),
		'phones_camera_roll'      => __( 'Phones / camera roll', 'jcp-core' ),
		'group_text_shared_folder'=> __( 'Group text / shared folder', 'jcp-core' ),
		'scattered'               => __( 'Scattered across places', 'jcp-core' ),
	];
}

/**
 * Jobs-per-week buckets → annual range.
 * 50+ has a floor only (no invented upper bound).
 *
 * @return array<string, array{label:string,weekly_label:string,min:int,max:?int}>
 */
function jcp_proof_gap_jobs_buckets(): array {
	return [
		'1_5'     => [ 'label' => '1–5', 'weekly_label' => '1–5', 'min' => 52, 'max' => 260 ],
		'6_10'    => [ 'label' => '6–10', 'weekly_label' => '6–10', 'min' => 312, 'max' => 520 ],
		'11_20'   => [ 'label' => '11–20', 'weekly_label' => '11–20', 'min' => 572, 'max' => 1040 ],
		'21_35'   => [ 'label' => '21–35', 'weekly_label' => '21–35', 'min' => 1092, 'max' => 1820 ],
		'36_50'   => [ 'label' => '36–50', 'weekly_label' => '36–50', 'min' => 1872, 'max' => 2600 ],
		'50_plus' => [ 'label' => '50+', 'weekly_label' => '50+', 'min' => 2600, 'max' => null ],
	];
}

/**
 * Public proof frequency options (display + band + fraction bounds).
 *
 * @return array<string, array{label:string,band:string,title:string,min:?float,max:?float}>
 */
function jcp_proof_gap_proof_percentage_options(): array {
	return [
		'0_10' => [
			'label' => __( 'Almost none', 'jcp-core' ),
			'title' => 'Almost none',
			'band'  => '0–10%',
			'min'   => 0.0,
			'max'   => 0.10,
		],
		'11_25' => [
			'label' => __( 'A few', 'jcp-core' ),
			'title' => 'A few',
			'band'  => '11–25%',
			'min'   => 0.11,
			'max'   => 0.25,
		],
		'26_50' => [
			'label' => __( 'About half', 'jcp-core' ),
			'title' => 'About half',
			'band'  => '26–50%',
			'min'   => 0.26,
			'max'   => 0.50,
		],
		'51_75' => [
			'label' => __( 'Most jobs', 'jcp-core' ),
			'title' => 'Most jobs',
			'band'  => '51–75%',
			'min'   => 0.51,
			'max'   => 0.75,
		],
		'76_100' => [
			'label' => __( 'Nearly every job', 'jcp-core' ),
			'title' => 'Nearly every job',
			'band'  => '76–100%',
			'min'   => 0.76,
			'max'   => 1.0,
		],
		'unknown' => [
			'label' => __( 'Not sure', 'jcp-core' ),
			'title' => 'Not sure',
			'band'  => '',
			'min'   => null,
			'max'   => null,
		],
	];
}

/**
 * Review snippets for Proof Gap slots (all four default testimonials, unique faces).
 *
 * @return array{workflow:?array<string,mixed>,email:?array<string,mixed>,reveal:?array<string,mixed>,trial:?array<string,mixed>}
 */
function jcp_proof_gap_review_slots(): array {
	$reviews = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
	$by_id   = [];
	foreach ( $reviews as $r ) {
		if ( is_array( $r ) && ! empty( $r['id'] ) ) {
			$by_id[ (string) $r['id'] ] = $r;
		}
	}
	return [
		'workflow' => $by_id['heriberto-eddie-roman'] ?? null,
		'email'    => $by_id['brian-hardy'] ?? null,
		'reveal'   => $by_id['peter-bonk'] ?? null,
		'trial'    => $by_id['trent-ellison'] ?? null,
	];
}

/**
 * Trade → curated local job photo map (theme assets only — never remote stock).
 *
 * Global default: assets/campaign/jobs/default.webp (home-service HVAC capture).
 * Niche files live under assets/campaign/jobs/{slug}.webp when verified; otherwise default.
 *
 * @return array<string, array{title:string,photo:string,neutral:bool}>
 */
function jcp_proof_gap_trade_job_assets(): array {
	$theme_dir = trailingslashit( get_template_directory() );
	$theme_uri = trailingslashit( get_template_directory_uri() );

	$asset_url = static function ( string $rel ) use ( $theme_dir, $theme_uri ): string {
		$rel  = ltrim( $rel, '/' );
		$path = $theme_dir . $rel;
		$url  = $theme_uri . $rel;
		if ( is_readable( $path ) ) {
			return $url . '?v=' . (string) filemtime( $path );
		}
		return $url;
	};

	$default = $asset_url( 'assets/campaign/jobs/default.webp' );

	$job_photo = static function ( string $slug ) use ( $theme_dir, $asset_url, $default ): string {
		$rel = 'assets/campaign/jobs/' . $slug . '.webp';
		if ( is_readable( $theme_dir . $rel ) ) {
			return $asset_url( $rel );
		}
		return $default;
	};

	return [
		'hvac'          => [
			'title'   => 'HVAC service call',
			'photo'   => $asset_url( 'assets/campaign/jcp-campaign-hvac-capture-640.webp' ),
			'neutral' => false,
		],
		'plumbing'      => [
			'title'   => 'Water heater replacement',
			'photo'   => $asset_url( 'assets/campaign/jcp-campaign-job-proof-640.webp' ),
			'neutral' => false,
		],
		'electrical'    => [
			'title'   => 'Electrical panel upgrade',
			'photo'   => $job_photo( 'electrical' ),
			'neutral' => false,
		],
		'roofing'       => [
			'title'   => 'Roofing project',
			'photo'   => $job_photo( 'roofing' ),
			'neutral' => false,
		],
		'remodeling'    => [
			'title'   => 'Remodel finish',
			'photo'   => $job_photo( 'remodeling' ),
			'neutral' => false,
		],
		'painting'      => [
			'title'   => 'Paint job',
			'photo'   => $job_photo( 'painting' ),
			'neutral' => false,
		],
		'landscaping'   => [
			'title'   => 'Landscaping job',
			'photo'   => $job_photo( 'landscaping' ),
			'neutral' => false,
		],
		'garage_door'   => [
			'title'   => 'Garage door service',
			'photo'   => $job_photo( 'garage_door' ),
			'neutral' => false,
		],
		'pest_control'  => [
			'title'   => 'Pest control visit',
			'photo'   => $default,
			'neutral' => false,
		],
		'tree_service'  => [
			'title'   => 'Tree service job',
			'photo'   => $job_photo( 'tree_service' ),
			'neutral' => false,
		],
		'power_washing' => [
			'title'   => 'Power washing job',
			'photo'   => $job_photo( 'power_washing' ),
			'neutral' => false,
		],
		'other'         => [
			'title'   => 'Finished field job',
			'photo'   => $default,
			'neutral' => false,
		],
	];
}

/**
 * Whitelisted creative_concept values for ad→funnel continuity.
 *
 * @return list<string>
 */
function jcp_proof_gap_creative_concepts(): array {
	return [ 'default', 'empty_window', 'concept_02', 'concept_03' ];
}

/**
 * Create opaque handoff token (email stored server-side only).
 *
 * @param string               $email   Work email.
 * @param array<string, mixed> $context Non-PII survey context.
 * @return string Token.
 */
function jcp_proof_gap_create_handoff_token( string $email, array $context = [] ): string {
	$token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : bin2hex( random_bytes( 16 ) );
	set_transient(
		'jcp_pg_handoff_' . $token,
		[
			'email'   => sanitize_email( $email ),
			'context' => $context,
			'created' => time(),
		],
		WEEK_IN_SECONDS
	);
	return $token;
}

/**
 * Resolve handoff token (server-side only; not used by production app yet).
 *
 * @param string $token Token.
 * @return array<string, mixed>|null
 */
function jcp_proof_gap_resolve_handoff_token( string $token ): ?array {
	$token = sanitize_text_field( $token );
	if ( $token === '' || ! preg_match( '/^[A-Za-z0-9_-]{8,64}$/', $token ) ) {
		return null;
	}
	$data = get_transient( 'jcp_pg_handoff_' . $token );
	return is_array( $data ) ? $data : null;
}
