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

$campaign        = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
$map_url         = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';
$companion_a     = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$companion_b     = $campaign . 'jcp-campaign-crew-review-640.webp';
$reviews         = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
?>

<section class="jcp-section jpd-run-hero" aria-labelledby="jpd-run-title" data-jpd-track-view="proof_job_viewed">
	<div class="jcp-container">
		<header class="jpd-run-hero__header">
			<h1 id="jpd-run-title" class="jcp-section-headline" data-jpd-full-heading><?php esc_html_e( 'Here’s what one job can become.', 'jcp-core' ); ?></h1>
			<p class="jpd-run-hero__sub"><?php esc_html_e( 'No posting to five different places. No writing captions after work. No letting another good job disappear into a camera roll.', 'jcp-core' ); ?></p>
		</header>

		<article class="jpd-run__job" data-jpd-teaser-job data-jpd-run-stage>
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
</section>

<section class="jcp-section jpd-run-results" id="jpdFullResults" data-jpd-results hidden aria-labelledby="jpd-results-title" data-jpd-track-view="proof_outputs_viewed">
	<div class="jcp-container">
		<header class="jpd-run-results__header">
			<h2 id="jpd-results-title" class="jcp-section-headline"><?php esc_html_e( 'One finished job just became fresh proof across your online presence.', 'jcp-core' ); ?></h2>
			<p class="jpd-run-results__sub"><?php esc_html_e( 'JobCapturePro publishes completed-job proof across connected channels — creating more ways for customers to discover, evaluate, and trust your business.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-outputs">
			<!-- Website: real plugin concept — map + recent check-ins -->
			<article class="jpd-output" data-jpd-output="website" data-jpd-track-view="output_card_website_viewed">
				<p class="jpd-output__channel"><?php esc_html_e( 'Website', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Published to your website plugin', 'jcp-core' ); ?></h3>
				<div class="jpd-plugin">
					<div class="jpd-plugin__map" aria-hidden="true">
						<img class="jpd-plugin__map-img" src="<?php echo esc_url( $map_url ); ?>" alt="" width="800" height="320" loading="lazy" decoding="async" />
						<span class="jpd-plugin__pin" style="left:28%;top:42%;"></span>
						<span class="jpd-plugin__pin jpd-plugin__pin--active" style="left:42%;top:45%;"></span>
						<span class="jpd-plugin__pin" style="left:35%;top:38%;"></span>
						<span class="jpd-plugin__pin" style="left:48%;top:52%;"></span>
						<span class="jpd-plugin__pin" style="left:32%;top:58%;"></span>
					</div>
					<div class="jpd-plugin__strip" aria-label="<?php esc_attr_e( 'Recent check-ins', 'jcp-core' ); ?>">
						<article class="jpd-plugin__card jpd-plugin__card--active">
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="200" height="140" loading="lazy" decoding="async" data-jpd-job-photo />
							<div class="jpd-plugin__card-body">
								<strong data-jpd-job-title><?php echo esc_html( $default_service ); ?></strong>
								<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
								<em><?php esc_html_e( 'Just completed', 'jcp-core' ); ?></em>
							</div>
						</article>
						<article class="jpd-plugin__card">
							<img src="<?php echo esc_url( $companion_a ); ?>" alt="" width="200" height="140" loading="lazy" decoding="async" />
							<div class="jpd-plugin__card-body">
								<strong><?php esc_html_e( 'AC system tune-up', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Round Rock, TX', 'jcp-core' ); ?></span>
							</div>
						</article>
						<article class="jpd-plugin__card">
							<img src="<?php echo esc_url( $companion_b ); ?>" alt="" width="200" height="140" loading="lazy" decoding="async" />
							<div class="jpd-plugin__card-body">
								<strong><?php esc_html_e( 'Drain line repair', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Cedar Park, TX', 'jcp-core' ); ?></span>
							</div>
						</article>
					</div>
					<p class="jpd-plugin__powered"><?php esc_html_e( 'Powered by JobCapturePro', 'jcp-core' ); ?></p>
				</div>
			</article>

			<!-- Google -->
			<article class="jpd-output" data-jpd-output="google" data-jpd-track-view="output_card_google_viewed">
				<p class="jpd-output__channel"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Posted as fresh Google activity', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-gbp">
					<p class="jcp-sm-gbp__brand"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
					<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="180" loading="lazy" data-jpd-job-photo />
					<div class="jcp-sm-gbp__copy">
						<strong data-jpd-gbp-headline><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
						<p><?php esc_html_e( 'Fresh job proof from today’s completed work, ready for homeowners nearby.', 'jcp-core' ); ?></p>
						<p class="jcp-sm-gbp__meta"><?php esc_html_e( 'Posted just now', 'jcp-core' ); ?></p>
					</div>
				</div>
			</article>

			<!-- Social -->
			<article class="jpd-output" data-jpd-output="social" data-jpd-track-view="output_card_social_viewed">
				<p class="jpd-output__channel"><?php esc_html_e( 'Social', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Shared as field proof', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-social">
					<div class="jcp-sm-social__head">
						<span class="jpd-social-avatar" aria-hidden="true">YB</span>
						<div>
							<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
							<span><?php esc_html_e( 'Just now', 'jcp-core' ); ?> · <span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span></span>
						</div>
					</div>
					<p class="jcp-sm-social__copy" data-jpd-social-copy><?php esc_html_e( 'Another job wrapped. Water heater replacement done right — proof from the field.', 'jcp-core' ); ?></p>
					<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="200" loading="lazy" data-jpd-job-photo />
				</div>
			</article>

			<!-- Directory: mirror live directory-card -->
			<article class="jpd-output" data-jpd-output="directory" data-jpd-track-view="output_card_directory_viewed">
				<p class="jpd-output__channel"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Listed with public completed-job proof', 'jcp-core' ); ?></h3>
				<div class="jpd-directory-preview preview-grid">
					<div class="directory-card directory-card-highlight jpd-directory-card" role="group" aria-label="<?php esc_attr_e( 'Your directory listing', 'jcp-core' ); ?>">
						<span class="directory-badge verified"><?php esc_html_e( 'Verified', 'jcp-core' ); ?></span>
						<div class="card-header">
							<div class="company-mark">
								<div class="company-avatar">YB</div>
							</div>
							<div class="card-header-content">
								<h3 class="card-name"><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></h3>
							</div>
						</div>
						<div class="card-location">
							<?php if ( $icon( 'map-pin' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
							<?php endif; ?>
							<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
						</div>
						<div class="card-meta-row">
							<span class="meta-inline">
								<?php if ( $icon( 'camera' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'camera' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
								<?php endif; ?>
								<span><?php esc_html_e( '48 jobs documented', 'jcp-core' ); ?></span>
							</span>
							<span class="meta-divider">·</span>
							<span class="meta-inline">
								<?php if ( $icon( 'clock' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'clock' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
								<?php endif; ?>
								<span><?php esc_html_e( 'Active today', 'jcp-core' ); ?></span>
							</span>
						</div>
						<div class="card-rating">
							<div class="stars" aria-hidden="true">★★★★★</div>
							<span class="rating-text">4.9 (128)</span>
						</div>
						<div class="jpd-directory-latest">
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="72" height="54" loading="lazy" decoding="async" data-jpd-job-photo />
							<div>
								<p class="jpd-directory-latest__label"><?php esc_html_e( 'Latest completed job', 'jcp-core' ); ?></p>
								<strong data-jpd-directory-latest><?php esc_html_e( 'Water heater replacement', 'jcp-core' ); ?></strong>
							</div>
						</div>
						<div class="card-footer">
							<span class="view-profile"><?php esc_html_e( 'View activity', 'jcp-core' ); ?></span>
						</div>
					</div>
				</div>
			</article>

			<!-- Review opportunity -->
			<article class="jpd-output" data-jpd-output="review" data-jpd-track-view="output_card_review_viewed">
				<p class="jpd-output__channel"><?php esc_html_e( 'Review opportunity', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Ask while the job is still fresh', 'jcp-core' ); ?></h3>
				<div class="jpd-review">
					<div class="jpd-review__qr" aria-hidden="true">
						<?php if ( $icon( 'qr-code' ) ) : ?>
							<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="88" height="88" />
						<?php endif; ?>
						<span><?php esc_html_e( 'Scan to review', 'jcp-core' ); ?></span>
					</div>
					<div class="jpd-review__actions">
						<div class="jpd-review__row">
							<span class="jpd-review__icon" aria-hidden="true">
								<?php if ( $icon( 'message-square' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'message-square' ) ); ?>" alt="" width="18" height="18" />
								<?php endif; ?>
							</span>
							<div>
								<strong><?php esc_html_e( 'Text the customer a review link', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Send before you leave the driveway.', 'jcp-core' ); ?></span>
							</div>
						</div>
						<div class="jpd-review__row">
							<span class="jpd-review__icon" aria-hidden="true">
								<?php if ( $icon( 'smartphone' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'smartphone' ) ); ?>" alt="" width="18" height="18" />
								<?php endif; ?>
							</span>
							<div>
								<strong><?php esc_html_e( 'Show QR before the truck leaves', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'One tap while the work is still top of mind.', 'jcp-core' ); ?></span>
							</div>
						</div>
						<p class="jpd-review__note"><?php esc_html_e( 'Simple handoff. No awkward follow-up email weeks later.', 'jcp-core' ); ?></p>
					</div>
				</div>
			</article>
		</div>

		<p class="jpd-publish-note"><?php esc_html_e( 'Publishing depends on connected channels.', 'jcp-core' ); ?></p>

		<?php
		if ( function_exists( 'jcp_niche_render_testimonials' ) && $reviews !== [] ) {
			$normalized = [];
			foreach ( array_slice( $reviews, 0, 3 ) as $r ) {
				$normalized[] = [
					'quote'  => (string) ( $r['quote'] ?? $r['text'] ?? '' ),
					'name'   => (string) ( $r['name'] ?? '' ),
					'role'   => (string) ( $r['role'] ?? $r['title'] ?? '' ),
					'avatar' => (string) ( $r['avatar'] ?? $r['image'] ?? '' ),
					'rating' => 5,
				];
			}
			jcp_niche_render_testimonials(
				[
					'headline'      => __( 'Real crews. Real completed jobs. Real proof.', 'jcp-core' ),
					'show_headline' => true,
					'section_id'    => 'jpd-run-testimonials',
					'reviews'       => $normalized,
				]
			);
		}
		?>

		<div class="rankings-cta jpd-trial-bridge" id="jpdTrialBridge">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'What if this happened after every job your crew finished?', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'Instead of letting job photos disappear into phones and CRMs, every completed job can keep building fresh proof where customers search before they call.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="run_convert" id="jpdTrialCta" data-jpd-track="proof_trial_cta_clicked" data-jpd-section="results_cta"><?php esc_html_e( 'Start free trial', 'jcp-core' ); ?> →</a>
				<p class="cta-note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert data-jpd-track="proof_expert_cta_clicked" data-jpd-section="results_cta"><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<!-- Direct-visit fallback: never show a second email/trade gate. JS redirects to LP. -->
<section class="jcp-section rankings-section jpd-run-gate" id="jpdRunGate" data-jpd-run-gate hidden>
	<div class="jcp-container">
		<p class="rankings-subtitle"><?php esc_html_e( 'Taking you to the demo form…', 'jcp-core' ); ?></p>
		<p><a class="btn btn-primary" href="<?php echo esc_url( $lp_href ); ?>#jpd-optin"><?php esc_html_e( 'Continue to the personalized demo form →', 'jcp-core' ); ?></a></p>
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
				<a class="jcp-case-exit__primary" id="jpdExitCaseCta" href="<?php echo esc_url( isset( $case_href ) ? $case_href : home_url( '/case-study/' ) ); ?>" data-jpd-track="proof_case_study_exit_clicked"><?php esc_html_e( 'See if I qualify →', 'jcp-core' ); ?></a>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Go back to my demo', 'jcp-core' ); ?></button>
			</div>
		</div>
	</div>
</div>
