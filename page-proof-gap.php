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

// Sized WebP (~4.5KB) — never the full uploads PNG (1613×383 / ~29KB).
$logo_url = get_template_directory_uri() . '/assets/brand/jcp-logo-dark-320.webp';

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
	<style id="pg-critical">
		html:has(body.jcp-proof-gap){height:100%;height:100dvh}
		body.jcp-proof-gap{margin:0;background:#fff;color:#111827;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;-webkit-font-smoothing:antialiased;--pg-accent:#ff5036;--pg-ink:#111827;--pg-muted:#6b7280;--pg-card:#fff;--pg-border:rgba(15,23,42,.08);--pg-radius:.85rem;--pg-radius-sm:.65rem;--pg-radius-lg:1rem;--pg-warning:#f59e0b;--pg-bottom-pad:7.5rem}
		body.jcp-proof-gap .pg-shell{min-height:100dvh;display:flex;flex-direction:column}
		body.jcp-proof-gap .pg-chrome__bar{display:flex;align-items:center;justify-content:space-between;min-height:52px;padding:.65rem 1rem;border-bottom:1px solid #e5e7eb;background:#fff}
		body.jcp-proof-gap .pg-chrome__brand img{display:block;height:28px;width:auto}
		body.jcp-proof-gap .pg-chrome__back[hidden],body.jcp-proof-gap .pg-progress[hidden],body.jcp-proof-gap .pg-state[hidden]{display:none!important}
		body.jcp-proof-gap .pg-app{flex:1;display:flex;flex-direction:column;min-height:0}
		body.jcp-proof-gap .pg-stage{flex:1;overflow:auto;padding:1.25rem 1.25rem .75rem}
		body.jcp-proof-gap.pg-has-bottom-action .pg-stage{padding-bottom:calc(var(--pg-bottom-pad) + env(safe-area-inset-bottom,0px) + 1.25rem)}
		body.jcp-proof-gap .pg-state__inner{max-width:34rem;margin:0 auto}
		body.jcp-proof-gap .pg-eyebrow{margin:0 0 .65rem;font-size:.84rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--pg-accent)}
		body.jcp-proof-gap .pg-title--welcome{margin:0 0 .85rem;font-size:clamp(1.55rem,5.2vw,2.05rem);line-height:1.15;letter-spacing:-.02em;font-weight:850;color:var(--pg-ink)}
		body.jcp-proof-gap .pg-sub{margin:0 0 .85rem;font-size:1.02rem;line-height:1.45;color:var(--pg-muted)}
		body.jcp-proof-gap .pg-welcome-checklist{margin:.95rem 0 .85rem;padding:.9rem;border-radius:var(--pg-radius-lg);background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 8px 24px rgba(15,23,42,.05);text-align:center}
		body.jcp-proof-gap .pg-welcome-checklist__heading{margin:0 0 .65rem;font-size:.97rem;font-weight:800;letter-spacing:-.01em;line-height:1.3;color:var(--pg-ink);text-align:left}
		body.jcp-proof-gap .pg-welcome-checklist__list{list-style:none;margin:0;padding:0;display:grid;gap:.35rem;text-align:left}
		body.jcp-proof-gap .pg-welcome-checklist__list li{display:flex;align-items:center;gap:.55rem;margin:0;padding:.38rem .5rem;border-radius:var(--pg-radius-sm);background:rgba(22,163,74,.06);font-size:.97rem;font-weight:750;color:var(--pg-ink)}
		body.jcp-proof-gap .pg-welcome-checklist__check{display:inline-flex;align-items:center;justify-content:center;width:1.35rem;height:1.35rem;border-radius:999px;background:#16a34a;color:#fff;font-size:.75rem;font-weight:850;line-height:1;flex:0 0 auto}
		body.jcp-proof-gap .pg-trust{margin:.85rem 0 .35rem;padding:.9rem .95rem;border-radius:var(--pg-radius);background:var(--pg-card);border:1px solid var(--pg-border);display:grid;gap:.75rem}
		body.jcp-proof-gap .pg-trust--compact{margin-top:.85rem;padding:.95rem 1rem}
		body.jcp-proof-gap .pg-trust__proof{display:flex;align-items:center;justify-content:space-between;gap:.85rem}
		body.jcp-proof-gap .pg-trust__proof-copy{display:grid;gap:.28rem;min-width:0}
		body.jcp-proof-gap .pg-trust__stars{display:flex;gap:.1rem;font-size:1.05rem;line-height:1;color:var(--pg-warning)}
		body.jcp-proof-gap .pg-trust__label{font-size:.97rem;font-weight:650;color:var(--pg-muted);line-height:1.35}
		body.jcp-proof-gap .pg-trust__avatars{display:flex;flex-shrink:0;align-items:center}
		body.jcp-proof-gap .pg-trust__avatars img{width:2.25rem;height:2.25rem;border-radius:999px;object-fit:cover;border:2px solid #fff;margin-left:-.55rem}
		body.jcp-proof-gap .pg-trust__avatars img:first-child{margin-left:0}
		body.jcp-proof-gap .pg-trust__divider{height:1px;background:var(--pg-border)}
		body.jcp-proof-gap .pg-trust__authority-block{display:grid;gap:.55rem}
		body.jcp-proof-gap .pg-trust__authority{margin:0;font-size:.97rem;font-weight:600;color:var(--pg-muted);line-height:1.35}
		body.jcp-proof-gap .pg-trust__authority strong{color:var(--pg-ink);font-weight:800}
		body.jcp-proof-gap .pg-trust__metrics{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.45rem}
		body.jcp-proof-gap .pg-trust__metrics li{display:grid;gap:.1rem;padding:.45rem .4rem;border-radius:.55rem;background:#f8fafc;border:1px solid var(--pg-border);text-align:center}
		body.jcp-proof-gap .pg-trust__metrics strong{font-size:.97rem;font-weight:850;color:var(--pg-ink);line-height:1.15}
		body.jcp-proof-gap .pg-trust__metrics span{font-size:.68rem;font-weight:650;color:var(--pg-muted);line-height:1.25}
		body.jcp-proof-gap .pg-bottom-action{position:fixed;left:0;right:0;bottom:0;z-index:40;padding:.85rem 1rem calc(.85rem + env(safe-area-inset-bottom,0px));background:rgba(255,255,255,.96);border-top:1px solid #e5e7eb;backdrop-filter:blur(8px)}
		body.jcp-proof-gap .pg-bottom-action__micro{margin:0 auto .45rem;max-width:34rem;text-align:center;font-size:.78rem;font-weight:600;color:var(--pg-muted);line-height:1.3}
		body.jcp-proof-gap .pg-bottom-action__cta{max-width:34rem;margin:0 auto}
		body.jcp-proof-gap .pg-btn{display:flex;align-items:center;justify-content:center;width:100%;min-height:3.15rem;margin:0;padding:.9rem 1.2rem;border:0;border-radius:.85rem;background:var(--pg-accent);color:#fff;font-size:1rem;font-weight:800;cursor:pointer;text-decoration:none;box-sizing:border-box}
	</style>
	<?php wp_head(); ?>
</head>
<body
	<?php body_class( 'pg-has-bottom-action' ); ?>
	data-jcp-lp-variant="proof_gap_survey_v1"
	data-pg-survey-id="proof_gap_survey_v1"
	data-pg-survey-version="<?php echo esc_attr( JCP_PROOF_GAP_SURVEY_VERSION ); ?>"
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
				<span class="pg-progress__phase" data-phase="gap" role="listitem"><?php esc_html_e( 'What gets seen', 'jcp-core' ); ?></span>
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
