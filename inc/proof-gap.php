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
define( 'JCP_PROOF_GAP_SURVEY_VERSION', '1' );
define( 'JCP_PROOF_GAP_SEED_VERSION', '1' );

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
		}
	} elseif ( $page instanceof WP_Post ) {
		$tpl = (string) get_page_template_slug( (int) $page->ID );
		if ( $tpl !== 'page-proof-gap.php' ) {
			update_post_meta( (int) $page->ID, '_wp_page_template', 'page-proof-gap.php' );
		}
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
 *
 * @return array<string, array{label:string,weekly_label:string,min:int,max:int}>
 */
function jcp_proof_gap_jobs_buckets(): array {
	return [
		'1_5'     => [ 'label' => '1–5', 'weekly_label' => '1–5', 'min' => 52, 'max' => 260 ],
		'6_10'    => [ 'label' => '6–10', 'weekly_label' => '6–10', 'min' => 312, 'max' => 520 ],
		'11_20'   => [ 'label' => '11–20', 'weekly_label' => '11–20', 'min' => 572, 'max' => 1040 ],
		'21_35'   => [ 'label' => '21–35', 'weekly_label' => '21–35', 'min' => 1092, 'max' => 1820 ],
		'36_50'   => [ 'label' => '36–50', 'weekly_label' => '36–50', 'min' => 1872, 'max' => 2600 ],
		'50_plus' => [ 'label' => '50+', 'weekly_label' => '50+', 'min' => 2600, 'max' => 5200 ],
	];
}

/**
 * Public proof percentage bands.
 *
 * @return array<string, string>
 */
function jcp_proof_gap_proof_percentage_options(): array {
	return [
		'0_10'    => '0–10%',
		'11_25'   => '11–25%',
		'26_50'   => '26–50%',
		'51_75'   => '51–75%',
		'76_100'  => '76–100%',
		'unknown' => __( 'Not sure', 'jcp-core' ),
	];
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
