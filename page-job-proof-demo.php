<?php
/**
 * Template Name: Job Proof Demo
 * Locked paid-traffic funnel: /job-proof-demo/
 * Ungated product proof → trial. Does not use /demo/.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo_url = 'https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png';
if ( function_exists( 'jcp_form_landing_logo_url' ) ) {
	$maybe = jcp_form_landing_logo_url( [] );
	if ( is_string( $maybe ) && $maybe !== '' ) {
		$logo_url = $maybe;
	}
}

$trial_href = function_exists( 'jcp_core_onboarding_app_url_raw' )
	? jcp_core_onboarding_app_url_raw(
		function_exists( 'jcp_core_onboarding_utm_defaults' )
			? jcp_core_onboarding_utm_defaults( 'job_proof_demo_trial' )
			: []
	)
	: 'https://app.jobcapturepro.com/onboarding';

$expert_href = home_url( '/personalized-demo/' );
$case_href   = home_url( '/case-study/' );
$photo_url   = function_exists( 'jcp_job_proof_demo_asset_url' )
	? jcp_job_proof_demo_asset_url( 'jcp-campaign-job-proof-640.webp' )
	: get_template_directory_uri() . '/assets/campaign/jcp-campaign-job-proof-640.webp';
$photo_fallback = function_exists( 'jcp_job_proof_demo_asset_url' )
	? jcp_job_proof_demo_asset_url( 'jcp-campaign-job-proof.jpg' )
	: get_template_directory_uri() . '/assets/campaign/jcp-campaign-job-proof.jpg';

?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-jcp-lp-variant="job_proof_demo">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'jcp-job-proof-demo jcp-landing-chrome-hidden' ); ?> data-jcp-lp-variant="job_proof_demo">
<?php wp_body_open(); ?>

<a class="jpd-skip" href="#jpd-main"><?php esc_html_e( 'Skip to content', 'jcp-core' ); ?></a>

<header class="jpd-brandbar" role="banner">
	<div class="jpd-brandbar__inner">
		<a class="jpd-brandbar__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'JobCapturePro home', 'jcp-core' ); ?>">
			<img
				class="jpd-brandbar__logo"
				src="<?php echo esc_url( $logo_url ); ?>"
				alt="JobCapturePro"
				width="160"
				height="36"
				decoding="async"
				data-no-lazy
			/>
		</a>
		<a
			class="btn btn-primary jpd-brandbar__trial"
			href="<?php echo esc_url( $trial_href ); ?>"
			data-jpd-trial
			data-jpd-source="brandbar"
		><?php esc_html_e( 'Start free trial', 'jcp-core' ); ?></a>
	</div>
</header>

<main id="jpd-main" class="jpd">
	<?php
	require get_template_directory() . '/templates/job-proof-demo/content.php';
	?>
</main>

<footer class="jpd-footer" role="contentinfo">
	<div class="jpd-footer__inner">
		<p class="jpd-footer__links">
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'jcp-core' ); ?></a>
			<span aria-hidden="true">·</span>
			<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'jcp-core' ); ?></a>
		</p>
		<p class="jpd-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> JobCapturePro</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
