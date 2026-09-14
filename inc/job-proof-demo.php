<?php
/**
 * Job Proof Demo paid LP — seed, detection, enqueue helpers.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JCP_JOB_PROOF_DEMO_SLUG', 'job-proof-demo' );
define( 'JCP_JOB_PROOF_DEMO_VARIANT', 'job_proof_demo' );
define( 'JCP_JOB_PROOF_DEMO_SEED_VERSION', '1' );

/**
 * Whether the current request is the job-proof-demo LP.
 */
function jcp_job_proof_demo_is_current(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( (string) $post->post_name === JCP_JOB_PROOF_DEMO_SLUG ) {
		return true;
	}
	$tpl = (string) get_page_template_slug( (int) $post->ID );
	return $tpl === 'page-job-proof-demo.php';
}

/**
 * Create / repair the locked WP page for /job-proof-demo/.
 */
function jcp_job_proof_demo_maybe_seed(): void {
	if ( is_admin() && ! wp_doing_cron() ) {
		// Allow editors to trigger via normal front requests only; still run on init for prod.
	}

	$ver = (string) get_option( 'jcp_job_proof_demo_seed_version', '' );
	$page = get_page_by_path( JCP_JOB_PROOF_DEMO_SLUG );

	if ( $page instanceof WP_Post && $ver === JCP_JOB_PROOF_DEMO_SEED_VERSION ) {
		$tpl = (string) get_page_template_slug( (int) $page->ID );
		if ( $tpl !== 'page-job-proof-demo.php' ) {
			update_post_meta( (int) $page->ID, '_wp_page_template', 'page-job-proof-demo.php' );
		}
		return;
	}

	$postarr = [
		'post_title'   => 'Job Proof Demo',
		'post_name'    => JCP_JOB_PROOF_DEMO_SLUG,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
		'post_excerpt' => 'See how one finished job becomes marketing proof — no signup required.',
	];

	if ( $page instanceof WP_Post ) {
		$postarr['ID'] = (int) $page->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return;
	}

	update_post_meta( (int) $post_id, '_wp_page_template', 'page-job-proof-demo.php' );
	update_post_meta( (int) $post_id, '_jcp_campaign_variant', JCP_JOB_PROOF_DEMO_VARIANT );
	update_post_meta( (int) $post_id, '_yoast_wpseo_meta-robots-noindex', '1' );
	update_option( 'jcp_job_proof_demo_seed_version', JCP_JOB_PROOF_DEMO_SEED_VERSION, false );
}
add_action( 'init', 'jcp_job_proof_demo_maybe_seed', 26 );

/**
 * noindex for paid LP.
 */
function jcp_job_proof_demo_noindex(): void {
	if ( ! jcp_job_proof_demo_is_current() ) {
		return;
	}
	echo '<meta name="robots" content="noindex,nofollow">' . "\n";
}
add_action( 'wp_head', 'jcp_job_proof_demo_noindex', 1 );

/**
 * Stamp lp_variant on document for attribution JS.
 */
function jcp_job_proof_demo_variant_bootstrap(): void {
	if ( ! jcp_job_proof_demo_is_current() ) {
		return;
	}
	$v = esc_js( JCP_JOB_PROOF_DEMO_VARIANT );
	echo '<script>document.documentElement.setAttribute("data-jcp-lp-variant","' . $v . '");document.addEventListener("DOMContentLoaded",function(){if(document.body)document.body.setAttribute("data-jcp-lp-variant","' . $v . '");});</script>' . "\n";
}
add_action( 'wp_head', 'jcp_job_proof_demo_variant_bootstrap', 2 );

/**
 * Campaign image base URL.
 */
function jcp_job_proof_demo_asset_url( string $file ): string {
	return trailingslashit( get_template_directory_uri() ) . 'assets/campaign/' . ltrim( $file, '/' );
}
