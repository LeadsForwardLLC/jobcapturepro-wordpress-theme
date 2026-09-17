<?php
/**
 * Template Name: Job Proof Demo
 * Product-led paid LP: /job-proof-demo/
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo_url = get_template_directory_uri() . '/assets/brand/jcp-logo-dark-320.webp';
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
$demo_run_url = function_exists( 'jcp_job_proof_demo_run_url' )
	? jcp_job_proof_demo_run_url()
	: home_url( '/job-proof-demo/demo/' );
$case_href = function_exists( 'jcp_case_study_url' )
	? jcp_case_study_url( 'job_proof_demo_exit' )
	: home_url( '/case-study/' );
$photo_url = function_exists( 'jcp_job_proof_demo_asset_url' )
	? jcp_job_proof_demo_asset_url( 'jcp-campaign-job-proof-640.webp' )
	: get_template_directory_uri() . '/assets/campaign/jcp-campaign-job-proof-640.webp';
$photo_fallback = function_exists( 'jcp_job_proof_demo_asset_url' )
	? jcp_job_proof_demo_asset_url( 'jcp-campaign-job-proof.jpg' )
	: get_template_directory_uri() . '/assets/campaign/jcp-campaign-job-proof.jpg';
$case_active = true;
if ( function_exists( 'jcp_case_study_spots_remaining' ) ) {
	$case_active = jcp_case_study_spots_remaining() > 0;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-jcp-lp-variant="job_proof_demo">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<style id="jpd-hide-chat">
		#chat-widget-container, #lc_text-widget, .lc_text-widget,
		[id*="chat-widget"], [class*="chat-widget"],
		iframe[src*="leadconnector"], iframe[src*="msgsndr"],
		button[aria-label="Open chat"], .leadconnector-chat, #leadconnector-chat, .ghl-chat-widget {
			display: none !important; visibility: hidden !important; pointer-events: none !important;
		}
	</style>
	<?php wp_head(); ?>
</head>
<body
	<?php body_class( 'jcp-job-proof-demo jcp-landing-chrome-hidden jcp-marketing jcp-page-marketing jcp-page-campaign jcp-home' ); ?>
	data-jcp-lp-variant="job_proof_demo"
	data-jpd-case-active="<?php echo $case_active ? '1' : '0'; ?>"
	data-jpd-case-url="<?php echo esc_attr( $case_href ); ?>"
	data-jpd-demo-run-url="<?php echo esc_attr( $demo_run_url ); ?>"
	data-jpd-campaign-base="<?php echo esc_attr( trailingslashit( get_template_directory_uri() ) . 'assets/campaign/' ); ?>"
>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#jpd-main"><?php esc_html_e( 'Skip to content', 'jcp-core' ); ?></a>

<header class="jcp-landing-brandbar is-compact" role="banner" data-jcp-landing-brandbar>
	<div class="jcp-landing-brandbar__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="jcp-landing-brandbar__link" aria-label="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>">
			<img
				src="<?php echo esc_url( $logo_url ); ?>"
				alt="JobCapturePro"
				class="jcp-landing-brandbar__logo"
				width="160"
				height="36"
				decoding="async"
				fetchpriority="high"
				data-no-lazy
			/>
		</a>
		<a
			class="jcp-landing-brandbar__cta"
			href="<?php echo esc_url( $trial_href ); ?>"
			data-jpd-trial
			data-jpd-source="brandbar"
		><?php esc_html_e( 'Start free trial', 'jcp-core' ); ?></a>
	</div>
</header>

<main id="jpd-main" class="jcp-marketing jcp-niche jcp-page-marketing jcp-page-campaign jcp-home">
	<?php require get_template_directory() . '/templates/job-proof-demo/content.php'; ?>
</main>

<footer class="jcp-footer jcp-footer--landing-minimal" role="contentinfo">
	<div class="jcp-container jcp-footer-bottom-inner">
		<nav class="jcp-footer-legal" aria-label="<?php esc_attr_e( 'Legal', 'jcp-core' ); ?>">
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'jcp-core' ); ?></a>
		</nav>
		<p class="jcp-footer-landing-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> JobCapturePro</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
