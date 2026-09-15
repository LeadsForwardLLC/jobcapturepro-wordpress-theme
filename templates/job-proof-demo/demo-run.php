<?php
/**
 * Personalized demo run — /job-proof-demo/demo/
 *
 * @package JCP_Core
 *
 * @var string $trial_href
 * @var string $expert_href
 * @var string $lp_href
 * @var string $photo_url
 * @var string $photo_fallback
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon = static function ( string $name ): string {
	return function_exists( 'jcp_core_icon' ) ? jcp_core_icon( $name ) : '';
};

$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
?>

<section class="jcp-section rankings-section jpd-run-hero" aria-labelledby="jpd-run-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h1 id="jpd-run-title" class="jcp-section-headline" data-jpd-full-heading><?php esc_html_e( 'Here’s what one job can become.', 'jcp-core' ); ?></h1>
			<p class="rankings-subtitle"><?php esc_html_e( 'No posting to five different places. No writing captions after work. No letting another good job disappear into a camera roll.', 'jcp-core' ); ?></p>
		</div>

		<div class="jpd-run__stage ranking-factor-card" data-jpd-run-stage>
			<article class="jpd-run__job" data-jpd-teaser-job>
				<div class="jpd-run__job-media">
					<img
						src="<?php echo esc_url( $photo_url ); ?>"
						alt=""
						width="640"
						height="420"
						decoding="async"
						data-jpd-job-photo
						data-fallback="<?php echo esc_url( $photo_fallback ); ?>"
					/>
					<span class="jpd-canvas__badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
				</div>
				<div class="jpd-run__job-body">
					<strong data-jpd-job-title><?php echo esc_html( $default_service ); ?></strong>
					<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
				</div>
			</article>

			<div class="jpd-run__progress" id="jpdFullProgress" aria-live="polite">
				<p class="jpd-full__status" id="jpdFullStatus"><?php esc_html_e( 'Creating your check-in…', 'jcp-core' ); ?></p>
				<ul class="jpd-run__steps">
					<li data-run-step="photo"><?php esc_html_e( 'Photo', 'jcp-core' ); ?></li>
					<li data-run-step="checkin"><?php esc_html_e( 'Check-in', 'jcp-core' ); ?></li>
					<li data-run-step="ai"><?php esc_html_e( 'AI description', 'jcp-core' ); ?></li>
					<li data-run-step="context"><?php esc_html_e( 'Service + location', 'jcp-core' ); ?></li>
					<li data-run-step="publish"><?php esc_html_e( 'Publishing', 'jcp-core' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<section class="jcp-section rankings-section jpd-run-results" id="jpdFullResults" data-jpd-results hidden aria-labelledby="jpd-results-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-results-title" class="jcp-section-headline"><?php esc_html_e( 'One finished job just became fresh proof across your online presence.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'JobCapturePro automatically publishes completed-job proof across connected channels while creating more ways for customers to discover, evaluate and trust your business.', 'jcp-core' ); ?></p>
			<p class="jpd-outcome"><?php esc_html_e( 'More real work online. More local relevance. More reasons to get found and chosen.', 'jcp-core' ); ?></p>
		</div>

		<div class="jpd-results-primary">
			<article class="ranking-factor-card jpd-result-card">
				<p class="jpd-result-card__channel"><?php esc_html_e( 'Website', 'jcp-core' ); ?></p>
				<h3 class="jpd-result-card__title"><?php esc_html_e( 'Automatically published to your website', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-browser">
					<div class="jcp-sm-browser__chrome"><span></span><span></span><span></span><em>yoursite.com/jobs</em></div>
					<div class="jcp-sm-job-card">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="120" height="90" loading="lazy" decoding="async" data-jpd-job-photo />
						<div>
							<strong data-jpd-job-title><?php echo esc_html( $default_service ); ?></strong>
							<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
							<p><?php esc_html_e( 'Completed install with geotagged job proof from the site.', 'jcp-core' ); ?></p>
						</div>
					</div>
				</div>
			</article>
			<article class="ranking-factor-card jpd-result-card">
				<p class="jpd-result-card__channel"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
				<h3 class="jpd-result-card__title"><?php esc_html_e( 'Automatically posted to Google', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-gbp">
					<p class="jcp-sm-gbp__brand"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
					<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="180" loading="lazy" data-jpd-job-photo />
					<div class="jcp-sm-gbp__copy">
						<strong data-jpd-gbp-headline><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
						<p><?php esc_html_e( 'Fresh job proof from today’s completed work, ready for homeowners nearby.', 'jcp-core' ); ?></p>
					</div>
				</div>
			</article>
			<article class="ranking-factor-card jpd-result-card">
				<p class="jpd-result-card__channel"><?php esc_html_e( 'Social', 'jcp-core' ); ?></p>
				<h3 class="jpd-result-card__title"><?php esc_html_e( 'Automatically posted to social', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-social">
					<div class="jcp-sm-social__head">
						<img class="jcp-sm-social__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="34" height="34" loading="lazy" data-jpd-job-photo />
						<div>
							<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
							<span><?php esc_html_e( 'Just now', 'jcp-core' ); ?> · <span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span></span>
						</div>
					</div>
					<p class="jcp-sm-social__copy" data-jpd-social-copy><?php esc_html_e( 'Another job wrapped. Water heater replacement done right — proof from the field.', 'jcp-core' ); ?></p>
					<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="200" loading="lazy" data-jpd-job-photo />
				</div>
			</article>
		</div>

		<div class="jpd-results-secondary">
			<article class="ranking-factor-card jpd-result-card jpd-result-card--directory">
				<p class="jpd-result-card__channel"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
				<h3 class="jpd-result-card__title"><?php esc_html_e( 'Published to your public JCP presence', 'jcp-core' ); ?></h3>
				<p class="jpd-result-card__note"><?php esc_html_e( 'Completed jobs become public proof inside JobCapturePro so customers can see the services and areas your business actually works in.', 'jcp-core' ); ?></p>
				<div class="jcp-sm-directory">
					<div class="jcp-sm-directory__card">
						<img class="jcp-sm-directory__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="40" height="40" loading="lazy" data-jpd-job-photo />
						<div>
							<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
							<span><?php if ( $icon( 'map-pin' ) ) : ?><img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="12" height="12" /><?php endif; ?> <span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span></span>
						</div>
					</div>
					<div class="jcp-sm-directory__proof">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="80" height="60" loading="lazy" data-jpd-job-photo />
						<strong data-jpd-directory-latest><?php esc_html_e( 'Latest: Water heater replacement', 'jcp-core' ); ?></strong>
					</div>
				</div>
			</article>
			<article class="ranking-factor-card jpd-result-card">
				<p class="jpd-result-card__channel"><?php esc_html_e( 'Review opportunity', 'jcp-core' ); ?></p>
				<h3 class="jpd-result-card__title"><?php esc_html_e( 'Ask while the job is still fresh', 'jcp-core' ); ?></h3>
				<div class="jpd-review-ask">
					<div class="jpd-review-ask__qr" aria-hidden="true">
						<?php if ( $icon( 'qr-code' ) ) : ?>
							<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="72" height="72" />
						<?php endif; ?>
						<span>QR</span>
					</div>
					<p><?php esc_html_e( 'Show the customer a QR or send a link before the truck leaves.', 'jcp-core' ); ?></p>
				</div>
			</article>
		</div>

		<p class="jpd-publish-note"><?php esc_html_e( 'Automatic publishing depends on connected channels.', 'jcp-core' ); ?></p>

		<div class="rankings-cta jpd-trial-bridge" id="jpdTrialBridge">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'What if this happened after every job your crew finished?', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'Instead of letting job photos disappear into phones and CRMs, every completed job can keep building fresh proof where customers search before they call.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="run_convert" id="jpdTrialCta"><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
				<p class="cta-note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<!-- Gate: returning visitor without opt-in session -->
<section class="jcp-section rankings-section jpd-run-gate" id="jpdRunGate" data-jpd-run-gate hidden>
	<div class="jcp-container">
		<div class="jpd-optin__card survey-step active">
			<h2 class="survey-title"><?php esc_html_e( 'Start your personalized demo', 'jcp-core' ); ?></h2>
			<p class="survey-subtitle"><?php esc_html_e( 'Enter your work email and trade to continue.', 'jcp-core' ); ?></p>
			<p><a class="btn btn-primary" href="<?php echo esc_url( $lp_href ); ?>#jpd-optin"><?php esc_html_e( 'Go to the demo form →', 'jcp-core' ); ?></a></p>
		</div>
	</div>
</section>

<!-- Exit case study (post-demo) -->
<div class="jcp-case-exit jpd-exit" id="jpdExitRoot" hidden aria-hidden="true" role="dialog" aria-modal="true">
	<div class="jcp-case-exit__backdrop" data-jpd-exit-dismiss></div>
	<div class="jcp-case-exit__card">
		<button type="button" class="jcp-case-exit__close" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>" data-jpd-exit-dismiss>×</button>
		<div class="jpd-exit__panel" data-jpd-exit-panel="case">
			<p class="jcp-case-exit__wait jpd-exit__eyebrow"><?php esc_html_e( 'Not ready to start a trial?', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title"><?php esc_html_e( 'Want to be considered for the 90-day JobCapturePro case study?', 'jcp-core' ); ?></h2>
			<p class="jcp-case-exit__body"><?php esc_html_e( 'We’re selecting a limited number of qualifying home-service companies to use JobCapturePro as part of our case-study program.', 'jcp-core' ); ?></p>
			<div class="jcp-case-exit__actions">
				<a class="jcp-case-exit__primary" id="jpdExitCaseCta" href="<?php echo esc_url( isset( $case_href ) ? $case_href : home_url( '/case-study/' ) ); ?>"><?php esc_html_e( 'See if I qualify →', 'jcp-core' ); ?></a>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Go back to my demo', 'jcp-core' ); ?></button>
			</div>
		</div>
	</div>
</div>
