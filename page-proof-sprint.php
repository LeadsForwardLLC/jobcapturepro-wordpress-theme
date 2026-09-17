<?php
/**
 * Template Name: Proof Sprint
 * Paid acquisition funnel: /proof-sprint/
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
			? jcp_core_onboarding_utm_defaults( 'proof_sprint_trial' )
			: []
	)
	: 'https://app.jobcapturepro.com/onboarding';

$login_href = function_exists( 'jcp_core_app_login_url_raw' )
	? jcp_core_app_login_url_raw()
	: 'https://app.jobcapturepro.com/login';

?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-jcp-lp-variant="proof_sprint">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex,nofollow">
	<style id="ps-hide-chat">
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
	<?php body_class(); ?>
	data-jcp-lp-variant="proof_sprint"
	data-ps-campaign-base="<?php echo esc_attr( trailingslashit( get_template_directory_uri() ) . 'assets/campaign/' ); ?>"
>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#ps-main"><?php esc_html_e( 'Skip to content', 'jcp-core' ); ?></a>

<header class="jcp-landing-brandbar is-compact ps-brandbar" role="banner">
	<div class="jcp-landing-brandbar__inner">
		<span class="jcp-landing-brandbar__link ps-brandbar__logo" aria-label="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>">
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
		</span>
		<div class="ps-brandbar__actions">
			<a
				class="jcp-landing-brandbar__cta"
				href="<?php echo esc_url( $trial_href ); ?>"
				data-ps-trial
				data-ps-source="brandbar"
			><?php esc_html_e( 'Start free 14-day trial', 'jcp-core' ); ?></a>
		</div>
	</div>
</header>

<main id="ps-main" class="ps-main jcp-marketing">
	<?php
	$trial_href = $trial_href; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- used in content.
	require get_template_directory() . '/templates/proof-sprint/content.php';
	?>
</main>

<footer class="jcp-footer jcp-footer--landing-minimal ps-footer" role="contentinfo">
	<div class="jcp-container ps-footer__inner">
		<p class="ps-footer__built"><?php esc_html_e( 'Built by the team behind LeadsForward', 'jcp-core' ); ?></p>
		<nav class="jcp-footer-legal ps-footer__nav" aria-label="<?php esc_attr_e( 'Legal', 'jcp-core' ); ?>">
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( 'Contact', 'jcp-core' ); ?></a>
		</nav>
		<p class="jcp-footer-landing-copy ps-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> JobCapturePro</p>
	</div>
</footer>

<div class="ps-sticky-cta" id="psStickyCta" hidden>
	<a class="btn btn-primary" href="#ps-optin" data-ps-scroll-optin data-ps-track="DemoCTA" data-ps-source="mobile_sticky"><?php esc_html_e( 'See it on my business →', 'jcp-core' ); ?></a>
</div>

<?php wp_footer(); ?>
</body>
</html>
