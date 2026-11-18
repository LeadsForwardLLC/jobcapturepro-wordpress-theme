<?php
/**
 * Job Proof Demo paid funnel — LP + personalized demo child route.
 *
 * Routes:
 * - /job-proof-demo/       → product-led landing (page-job-proof-demo.php)
 * - /job-proof-demo/demo/  → personalized demo run (page-job-proof-demo-run.php)
 *
 * IMPORTANT: child slug is also "demo", same as organic /demo/. WordPress can
 * resolve the hierarchical URL to the top-level demo page — we force the child.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JCP_JOB_PROOF_DEMO_SLUG', 'job-proof-demo' );
define( 'JCP_JOB_PROOF_DEMO_RUN_SLUG', 'demo' );
define( 'JCP_JOB_PROOF_DEMO_VARIANT', 'job_proof_demo' );
define( 'JCP_JOB_PROOF_DEMO_SEED_VERSION', '4' );

/**
 * Request path without leading/trailing slashes.
 */
function jcp_job_proof_demo_request_path(): string {
	return trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}

/**
 * Whether the raw request path is the paid personalized-demo child route.
 */
function jcp_job_proof_demo_is_run_path(): bool {
	return jcp_job_proof_demo_request_path() === JCP_JOB_PROOF_DEMO_SLUG . '/' . JCP_JOB_PROOF_DEMO_RUN_SLUG;
}

/**
 * Whether the raw request path is the paid LP (exact).
 */
function jcp_job_proof_demo_is_lp_path(): bool {
	return jcp_job_proof_demo_request_path() === JCP_JOB_PROOF_DEMO_SLUG;
}

/**
 * Published child page for /job-proof-demo/demo/, if any.
 */
function jcp_job_proof_demo_get_run_page(): ?WP_Post {
	$child = get_page_by_path( JCP_JOB_PROOF_DEMO_SLUG . '/' . JCP_JOB_PROOF_DEMO_RUN_SLUG );
	if ( $child instanceof WP_Post && $child->post_status === 'publish' ) {
		return $child;
	}
	return null;
}

/**
 * Force hierarchical child page when slug "demo" collides with organic /demo/.
 *
 * @param array<string, mixed> $query_vars Query vars.
 * @return array<string, mixed>
 */
function jcp_job_proof_demo_force_child_query( array $query_vars ): array {
	if ( ! jcp_job_proof_demo_is_run_path() ) {
		return $query_vars;
	}
	$child = jcp_job_proof_demo_get_run_page();
	if ( ! $child ) {
		return $query_vars;
	}
	$query_vars['page_id'] = (int) $child->ID;
	unset( $query_vars['pagename'], $query_vars['name'], $query_vars['attachment'], $query_vars['error'] );
	return $query_vars;
}
add_filter( 'request', 'jcp_job_proof_demo_force_child_query', 1 );

/**
 * Never canonical-redirect the paid child onto organic /demo/.
 *
 * @param string|false $redirect_url  Redirect target.
 * @param string       $requested_url Requested URL.
 * @return string|false
 */
function jcp_job_proof_demo_preserve_child_canonical( $redirect_url, $requested_url ) {
	$path = trim( (string) parse_url( (string) $requested_url, PHP_URL_PATH ), '/' );
	if ( $path === JCP_JOB_PROOF_DEMO_SLUG . '/' . JCP_JOB_PROOF_DEMO_RUN_SLUG ) {
		return false;
	}
	if ( is_string( $redirect_url ) && $redirect_url !== '' ) {
		$dest = trim( (string) parse_url( $redirect_url, PHP_URL_PATH ), '/' );
		if ( $path === JCP_JOB_PROOF_DEMO_SLUG . '/' . JCP_JOB_PROOF_DEMO_RUN_SLUG && $dest === 'demo' ) {
			return false;
		}
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'jcp_job_proof_demo_preserve_child_canonical', 5, 2 );

/**
 * Hard-load run template if path matches even when query object is wrong.
 *
 * @param string $template Template path.
 */
function jcp_job_proof_demo_force_run_template( string $template ): string {
	if ( ! jcp_job_proof_demo_is_run_path() ) {
		return $template;
	}
	$custom = trailingslashit( get_template_directory() ) . 'page-job-proof-demo-run.php';
	return is_readable( $custom ) ? $custom : $template;
}
add_filter( 'template_include', 'jcp_job_proof_demo_force_run_template', 99 );

/**
 * Whether the current request is the job-proof-demo landing page (not the child demo).
 */
function jcp_job_proof_demo_is_current(): bool {
	if ( jcp_job_proof_demo_is_run_path() ) {
		return false;
	}
	if ( jcp_job_proof_demo_is_lp_path() ) {
		return true;
	}
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( (string) $post->post_name === JCP_JOB_PROOF_DEMO_SLUG && (int) $post->post_parent === 0 ) {
		return true;
	}
	$tpl = (string) get_page_template_slug( (int) $post->ID );
	return $tpl === 'page-job-proof-demo.php';
}

/**
 * Whether the current request is /job-proof-demo/demo/.
 */
function jcp_job_proof_demo_run_is_current(): bool {
	if ( jcp_job_proof_demo_is_run_path() ) {
		return true;
	}
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	$tpl = (string) get_page_template_slug( (int) $post->ID );
	if ( $tpl === 'page-job-proof-demo-run.php' ) {
		return true;
	}
	if ( (string) $post->post_name !== JCP_JOB_PROOF_DEMO_RUN_SLUG ) {
		return false;
	}
	$parent = $post->post_parent ? get_post( (int) $post->post_parent ) : null;
	return $parent instanceof WP_Post && (string) $parent->post_name === JCP_JOB_PROOF_DEMO_SLUG;
}

/**
 * Absolute URL for the personalized demo run.
 */
function jcp_job_proof_demo_run_url( array $args = [] ): string {
	$url = home_url( '/' . JCP_JOB_PROOF_DEMO_SLUG . '/' . JCP_JOB_PROOF_DEMO_RUN_SLUG . '/' );
	if ( $args !== [] ) {
		$url = add_query_arg( $args, $url );
	}
	return $url;
}

/**
 * Create / repair parent LP + child demo pages.
 */
function jcp_job_proof_demo_maybe_seed(): void {
	$ver  = (string) get_option( 'jcp_job_proof_demo_seed_version', '' );
	$page = get_page_by_path( JCP_JOB_PROOF_DEMO_SLUG );

	if ( ! ( $page instanceof WP_Post ) || $ver !== JCP_JOB_PROOF_DEMO_SEED_VERSION ) {
		$postarr = [
			'post_title'   => 'Job Proof Demo',
			'post_name'    => JCP_JOB_PROOF_DEMO_SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_excerpt' => 'Turn one finished job into marketing everywhere customers look.',
		];
		if ( $page instanceof WP_Post ) {
			$postarr['ID'] = (int) $page->ID;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}
		if ( ! is_wp_error( $post_id ) && $post_id ) {
			$page = get_post( (int) $post_id );
			update_post_meta( (int) $post_id, '_wp_page_template', 'page-job-proof-demo.php' );
			update_post_meta( (int) $post_id, '_jcp_campaign_variant', JCP_JOB_PROOF_DEMO_VARIANT );
			update_post_meta( (int) $post_id, '_yoast_wpseo_meta-robots-noindex', '1' );
		}
	} elseif ( $page instanceof WP_Post ) {
		$tpl = (string) get_page_template_slug( (int) $page->ID );
		if ( $tpl !== 'page-job-proof-demo.php' ) {
			update_post_meta( (int) $page->ID, '_wp_page_template', 'page-job-proof-demo.php' );
		}
	}

	if ( ! ( $page instanceof WP_Post ) ) {
		return;
	}

	$child_path = JCP_JOB_PROOF_DEMO_SLUG . '/' . JCP_JOB_PROOF_DEMO_RUN_SLUG;
	$child      = get_page_by_path( $child_path );
	if ( ! ( $child instanceof WP_Post ) ) {
		$child_id = wp_insert_post(
			[
				'post_title'   => 'Personalized Job Proof Demo',
				'post_name'    => JCP_JOB_PROOF_DEMO_RUN_SLUG,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_parent'  => (int) $page->ID,
				'post_content' => '',
				'post_excerpt' => 'See what one finished job becomes across your online presence.',
			],
			true
		);
		if ( ! is_wp_error( $child_id ) && $child_id ) {
			$child = get_post( (int) $child_id );
		}
	}

	if ( $child instanceof WP_Post ) {
		update_post_meta( (int) $child->ID, '_wp_page_template', 'page-job-proof-demo-run.php' );
		update_post_meta( (int) $child->ID, '_jcp_campaign_variant', JCP_JOB_PROOF_DEMO_VARIANT );
		update_post_meta( (int) $child->ID, '_yoast_wpseo_meta-robots-noindex', '1' );
		if ( (int) $child->post_parent !== (int) $page->ID ) {
			wp_update_post(
				[
					'ID'          => (int) $child->ID,
					'post_parent' => (int) $page->ID,
				]
			);
		}
	}

	if ( $ver !== JCP_JOB_PROOF_DEMO_SEED_VERSION ) {
		flush_rewrite_rules( false );
	}
	update_option( 'jcp_job_proof_demo_seed_version', JCP_JOB_PROOF_DEMO_SEED_VERSION, false );
}
add_action( 'init', 'jcp_job_proof_demo_maybe_seed', 26 );

/**
 * noindex for LP + demo run.
 */
function jcp_job_proof_demo_noindex(): void {
	if ( ! jcp_job_proof_demo_is_current() && ! jcp_job_proof_demo_run_is_current() ) {
		return;
	}
	echo '<meta name="robots" content="noindex,nofollow">' . "\n";
}
add_action( 'wp_head', 'jcp_job_proof_demo_noindex', 1 );

/**
 * Stamp lp_variant on document for attribution JS.
 */
function jcp_job_proof_demo_variant_bootstrap(): void {
	if ( ! jcp_job_proof_demo_is_current() && ! jcp_job_proof_demo_run_is_current() ) {
		return;
	}
	$v = esc_js( JCP_JOB_PROOF_DEMO_VARIANT );
	echo '<script>document.documentElement.setAttribute("data-jcp-lp-variant","' . $v . '");document.addEventListener("DOMContentLoaded",function(){if(document.body)document.body.setAttribute("data-jcp-lp-variant","' . $v . '");});</script>' . "\n";
}
add_action( 'wp_head', 'jcp_job_proof_demo_variant_bootstrap', 2 );

/**
 * Campaign asset URL helper.
 */
function jcp_job_proof_demo_asset_url( string $file ): string {
	return trailingslashit( get_template_directory_uri() ) . 'assets/campaign/' . ltrim( $file, '/' );
}
