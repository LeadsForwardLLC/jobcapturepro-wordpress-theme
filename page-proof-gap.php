<?php
/**
 * Template Name: Proof Gap Survey
 * Paid acquisition survey app: /proof-gap/
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

$trial_base = function_exists( 'jcp_core_onboarding_app_url_raw' )
	? jcp_core_onboarding_app_url_raw(
		function_exists( 'jcp_core_onboarding_utm_defaults' )
			? jcp_core_onboarding_utm_defaults( 'proof_gap_survey_trial' )
			: []
	)
	: 'https://app.jobcapturepro.com/onboarding';

?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-jcp-lp-variant="proof_gap_survey_v1">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex,nofollow">
	<meta name="theme-color" content="#0b1220">
	<style id="pg-hide-chat">
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
	data-jcp-lp-variant="proof_gap_survey_v1"
	data-pg-survey-id="proof_gap_survey_v1"
	data-pg-survey-version="8"
>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#pg-app"><?php esc_html_e( 'Skip to survey', 'jcp-core' ); ?></a>

<div class="pg-shell" id="pg-shell">
	<header class="pg-chrome" role="banner">
		<div class="pg-chrome__bar">
			<span class="pg-chrome__brand" aria-label="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>">
				<img
					src="<?php echo esc_url( $logo_url ); ?>"
					alt="JobCapturePro"
					width="120"
					height="28"
					decoding="async"
					fetchpriority="high"
					data-no-lazy
				/>
			</span>
			<button type="button" class="pg-chrome__back" id="pgBack" hidden>
				<?php esc_html_e( 'Back', 'jcp-core' ); ?>
			</button>
		</div>
		<div class="pg-progress" id="pgProgress" hidden aria-label="<?php esc_attr_e( 'Survey progress', 'jcp-core' ); ?>">
			<div class="pg-progress__phases" role="list">
				<span class="pg-progress__phase is-active" data-phase="work" role="listitem"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></span>
				<span class="pg-progress__phase" data-phase="gap" role="listitem"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></span>
				<span class="pg-progress__phase" data-phase="plan" role="listitem"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></span>
			</div>
			<div class="pg-progress__track" aria-hidden="true">
				<div class="pg-progress__fill" id="pgProgressFill"></div>
			</div>
		</div>
	</header>

	<main id="pg-app" class="pg-app" data-pg-app>
		<?php require get_template_directory() . '/templates/proof-gap/content.php'; ?>
	</main>
</div>

<script type="application/json" id="pg-boot">
<?php
echo wp_json_encode(
	[
		'surveyId'          => JCP_PROOF_GAP_SURVEY_ID,
		'surveyVersion'     => JCP_PROOF_GAP_SURVEY_VERSION,
		'lpVariant'         => JCP_PROOF_GAP_VARIANT,
		'restUrl'           => rest_url( 'jcp/v1/proof-gap-survey-submit' ),
		'funnelEventUrl'    => rest_url( 'jcp/v1/funnel-event' ),
		'trialBase'         => $trial_base,
		'campaignBase'      => trailingslashit( get_template_directory_uri() ) . 'assets/campaign/',
		'trades'            => function_exists( 'jcp_proof_gap_trade_options' ) ? jcp_proof_gap_trade_options() : [],
		'workflows'         => function_exists( 'jcp_proof_gap_workflow_options' ) ? jcp_proof_gap_workflow_options() : [],
		'jobsBuckets'       => function_exists( 'jcp_proof_gap_jobs_buckets' ) ? jcp_proof_gap_jobs_buckets() : [],
		'proofPct'          => function_exists( 'jcp_proof_gap_proof_percentage_options' ) ? jcp_proof_gap_proof_percentage_options() : [],
		'tradeAssets'       => function_exists( 'jcp_proof_gap_trade_job_assets' ) ? jcp_proof_gap_trade_job_assets() : [],
		'creativeConcepts'  => function_exists( 'jcp_proof_gap_creative_concepts' ) ? jcp_proof_gap_creative_concepts() : [ 'default' ],
	]
);
?>
</script>

<?php wp_footer(); ?>
</body>
</html>
