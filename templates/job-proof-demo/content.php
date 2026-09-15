<?php
/**
 * Job Proof Demo — locked paid funnel content.
 *
 * @package JCP_Core
 *
 * @var string $trial_href
 * @var string $expert_href
 * @var string $case_href
 * @var string $photo_url
 * @var string $photo_fallback
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon = static function ( string $name ): string {
	return function_exists( 'jcp_core_icon' ) ? jcp_core_icon( $name ) : '';
};

$show_case = true;
if ( function_exists( 'jcp_case_study_spots_remaining' ) ) {
	$show_case = jcp_case_study_spots_remaining() > 0;
}

$campaign = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$reviews  = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];

$business_type_options = function_exists( 'jcp_core_business_type_flat_options' )
	? jcp_core_business_type_flat_options()
	: [];

$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
?>

<!-- 6. Hero -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split" id="proof" aria-labelledby="jpd-hero-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout">
			<div class="jcp-hero-copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'Your best jobs shouldn’t die in the camera roll', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Turn every finished job into fresh local proof — automatically.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><?php esc_html_e( 'Your crews already take the photos. JobCapturePro automatically turns finished jobs into website content, Google activity, social proof, review opportunities and public JCP Directory proof — so the work you already paid to complete keeps helping your business get found.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-teaser" data-jpd-scroll-teaser>
						<?php esc_html_e( 'See the free personalized demo →', 'jcp-core' ); ?>
					</a>
					<p class="jcp-hero-cta-microcopy"><?php esc_html_e( 'Free · About 60 seconds · No phone call required', 'jcp-core' ); ?></p>
					<p class="jpd-hero-skip">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Already know you want it? Start free trial →', 'jcp-core' ); ?></a>
					</p>
				</div>
			</div>
			<div class="jcp-hero-media" aria-hidden="true">
				<div class="jpd-hero-flow">
					<article class="jpd-hero-flow__job">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="320" height="210" decoding="async" data-no-lazy data-fallback="<?php echo esc_url( $photo_fallback ); ?>" />
						<span><?php esc_html_e( 'Finished job', 'jcp-core' ); ?></span>
					</article>
					<div class="jpd-hero-flow__arrow" aria-hidden="true">→</div>
					<div class="jpd-hero-flow__jcp">
						<strong>JobCapturePro</strong>
					</div>
					<div class="jpd-hero-flow__arrow" aria-hidden="true">→</div>
					<ul class="jpd-hero-flow__dest">
						<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'JCP Directory', 'jcp-core' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
// 7. Credibility — established authority scoreboard (no CTA competing with funnel).
if ( function_exists( 'jcp_niche_render_authority' ) ) {
	jcp_niche_render_authority(
		[
			'variant'    => 'scoreboard',
			'eyebrow'    => __( 'Built by LeadsForward', 'jcp-core' ),
			'headline'   => __( '10 years helping contractors grow', 'jcp-core' ),
			'body'       => __( 'We kept seeing the same problem: contractors completed great work every day, but almost none of that work became public marketing proof. JobCapturePro was built to close that gap.', 'jcp-core' ),
			'stats'      => [
				[
					'value'  => '10',
					'label'  => __( 'years', 'jcp-core' ),
					'detail' => __( 'Helping contractors grow', 'jcp-core' ),
				],
				[
					'value'  => '250K+',
					'label'  => __( 'leads', 'jcp-core' ),
					'detail' => __( 'Generated for contractor clients', 'jcp-core' ),
				],
				[
					'value'  => '$150M+',
					'label'  => __( 'revenue', 'jcp-core' ),
					'detail' => __( 'Booked from those leads', 'jcp-core' ),
				],
			],
			'show_cta'   => false,
			'section_id' => 'built-by-leadsforward',
		],
		'job_proof_demo'
	);
}
?>

<!-- 8. Ungated product-proof teaser -->
<section class="jcp-section jpd-teaser" id="jpd-teaser" data-jpd-teaser aria-labelledby="jpd-teaser-title">
	<div class="jcp-container">
		<header class="rankings-header">
			<h2 id="jpd-teaser-title" class="jcp-section-headline"><?php esc_html_e( 'See what happens to one finished job.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'Your crew finishes the work and takes the photo. JobCapturePro handles what happens next.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-teaser__stage" data-jpd-teaser-stage>
			<article class="jpd-job-card" data-jpd-teaser-job>
				<div class="jpd-job-card__media">
					<img
						src="<?php echo esc_url( $photo_url ); ?>"
						alt="<?php esc_attr_e( 'Completed water heater replacement job photo', 'jcp-core' ); ?>"
						width="640"
						height="420"
						loading="lazy"
						decoding="async"
						data-jpd-job-photo
						data-fallback="<?php echo esc_url( $photo_fallback ); ?>"
					/>
					<span class="jpd-job-card__badge"><?php esc_html_e( 'Completed', 'jcp-core' ); ?></span>
				</div>
				<div class="jpd-job-card__body">
					<h3 class="jpd-job-card__service" data-jpd-job-title><?php echo esc_html( $default_service ); ?></h3>
					<p class="jpd-job-card__meta"><span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span> · <?php esc_html_e( 'Home services', 'jcp-core' ); ?></p>
				</div>
			</article>

			<div class="jpd-teaser__status" id="jpdTeaserStatus" aria-live="polite" hidden></div>

			<div class="jpd-teaser__checkin" id="jpdTeaserCheckin" hidden>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="96" height="72" loading="lazy" decoding="async" data-jpd-job-photo />
				<div>
					<p class="jpd-teaser__checkin-title" data-jpd-job-title><?php echo esc_html( $default_service ); ?></p>
					<p class="jpd-teaser__checkin-meta"><span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span></p>
					<span class="jpd-teaser__checkin-badge"><?php esc_html_e( 'Check-in ready', 'jcp-core' ); ?></span>
				</div>
			</div>

			<ul class="jpd-teaser__nodes" id="jpdTeaserNodes" hidden aria-hidden="true">
				<li data-node="website"><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
				<li data-node="google"><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
				<li data-node="social"><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
				<li data-node="review"><?php esc_html_e( 'Review opportunity', 'jcp-core' ); ?></li>
				<li data-node="directory"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></li>
			</ul>
		</div>

		<div class="jpd-teaser__actions" data-jpd-teaser-actions>
			<button type="button" class="btn btn-primary" data-jpd-start-teaser>
				<?php esc_html_e( 'Watch JCP turn this into marketing →', 'jcp-core' ); ?>
			</button>
		</div>
	</div>
</section>

<!-- 9. Micro opt-in -->
<section class="jcp-section jpd-optin" id="jpd-optin" data-jpd-optin hidden aria-labelledby="jpd-optin-title">
	<div class="jcp-container">
		<div class="jpd-optin__card survey-step">
			<header class="survey-head">
				<h2 id="jpd-optin-title" class="survey-title"><?php esc_html_e( 'Want to see this for your trade?', 'jcp-core' ); ?></h2>
				<p class="survey-subtitle"><?php esc_html_e( 'Enter your work email and trade. We’ll personalize the full demo to the kind of jobs your business actually completes — and your demo follow-up can reach your inbox if you get pulled away.', 'jcp-core' ); ?></p>
			</header>

			<form class="survey-form survey-form--gate" id="jpdOptinForm" autocomplete="on" novalidate>
				<div class="survey-field">
					<label for="jpd-email"><?php esc_html_e( 'Work email', 'jcp-core' ); ?> <span class="survey-required">*</span></label>
					<input
						id="jpd-email"
						name="email"
						type="email"
						class="survey-input"
						placeholder="you@company.com"
						autocomplete="email"
						inputmode="email"
						required
					/>
				</div>

				<div class="survey-field survey-combobox">
					<label for="jpd-nicheSearch"><?php esc_html_e( 'Trade', 'jcp-core' ); ?> <span class="survey-required">*</span></label>
					<div class="survey-combobox__control">
						<input
							id="jpd-nicheSearch"
							type="text"
							class="survey-input"
							role="combobox"
							aria-autocomplete="list"
							aria-expanded="false"
							aria-controls="jpd-nicheListbox"
							aria-haspopup="listbox"
							placeholder="<?php esc_attr_e( 'Start typing your trade…', 'jcp-core' ); ?>"
							autocomplete="off"
							maxlength="120"
							required
						/>
						<input type="hidden" id="jpd-niche" value="" />
						<input type="hidden" id="jpd-nicheOther" value="" />
						<ul
							id="jpd-nicheListbox"
							class="survey-combobox__list"
							role="listbox"
							hidden
							aria-label="<?php esc_attr_e( 'Trade suggestions', 'jcp-core' ); ?>"
						></ul>
					</div>
					<script type="application/json" id="jcpBusinessTypeOptions"><?php echo wp_json_encode( $business_type_options ); ?></script>
				</div>

				<p class="jpd-optin__error" id="jpdOptinError" role="alert" hidden></p>

				<div class="survey-actions-row">
					<button type="submit" class="btn btn-primary survey-btn" id="jpdOptinSubmit">
						<?php esc_html_e( 'Personalize my demo →', 'jcp-core' ); ?>
					</button>
					<p class="survey-microcopy"><?php esc_html_e( 'Free · No phone number required · No credit card', 'jcp-core' ); ?></p>
					<p class="survey-consent"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</div>
			</form>
		</div>
	</div>
</section>

<!-- 15–19. Full personalized demo + results + trial CTA -->
<section class="jcp-section jpd-full" id="jpd-full-demo" data-jpd-full hidden aria-labelledby="jpd-full-title">
	<div class="jcp-container">
		<header class="rankings-header">
			<h2 id="jpd-full-title" class="jcp-section-headline" data-jpd-full-heading><?php esc_html_e( 'Here’s what one job can become.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'No posting to five different places. No writing captions after work. No letting another good job disappear into a camera roll.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-full__progress" id="jpdFullProgress" aria-live="polite">
			<p class="jpd-full__status" id="jpdFullStatus"><?php esc_html_e( 'Creating your check-in…', 'jcp-core' ); ?></p>
		</div>

		<div class="jpd-full__results" id="jpdFullResults" hidden>
			<header class="rankings-header">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'One finished job just became fresh proof across your online presence.', 'jcp-core' ); ?></h2>
				<p class="rankings-subtitle"><?php esc_html_e( 'JobCapturePro automatically publishes completed-job proof across connected channels while creating new ways for customers to discover, evaluate and trust your business.', 'jcp-core' ); ?></p>
				<p class="jpd-outcome"><?php esc_html_e( 'More real work online. More local relevance. More reasons to get found and chosen.', 'jcp-core' ); ?></p>
			</header>

			<div class="jpd-results-primary">
				<article class="jpd-result-card">
					<p class="jpd-result-card__channel"><?php esc_html_e( 'Website', 'jcp-core' ); ?></p>
					<h3 class="jpd-result-card__title"><?php esc_html_e( 'Automatically published to your website', 'jcp-core' ); ?></h3>
					<div class="jcp-sm-browser">
						<div class="jcp-sm-browser__bar" aria-hidden="true">
							<span></span><span></span><span></span>
							<div class="jcp-sm-browser__url">yourbusiness.com/recent-work</div>
						</div>
						<div class="jcp-sm-browser__body">
							<p class="jcp-sm-browser__heading"><?php esc_html_e( 'Recent work', 'jcp-core' ); ?></p>
							<div class="jcp-sm-job-card">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="120" height="90" loading="lazy" decoding="async" data-jpd-job-photo />
								<div>
									<strong data-jpd-job-title><?php echo esc_html( $default_service ); ?></strong>
									<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
									<p><?php esc_html_e( 'Completed install with geotagged job proof from the site.', 'jcp-core' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</article>

				<article class="jpd-result-card">
					<p class="jpd-result-card__channel"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
					<h3 class="jpd-result-card__title"><?php esc_html_e( 'Automatically posted to Google', 'jcp-core' ); ?></h3>
					<div class="jcp-sm-gbp">
						<div class="jcp-sm-gbp__brand">
							<?php if ( $icon( 'map-pin' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="16" height="16" />
							<?php endif; ?>
							<div>
								<strong><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Your business · Update', 'jcp-core' ); ?></span>
							</div>
						</div>
						<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="180" loading="lazy" decoding="async" data-jpd-job-photo />
						<div class="jcp-sm-gbp__copy">
							<strong data-jpd-gbp-headline><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
							<p><?php esc_html_e( 'Fresh job proof from today’s completed work, ready for homeowners nearby.', 'jcp-core' ); ?></p>
						</div>
						<span class="jcp-sm-gbp__meta"><?php esc_html_e( 'Posted to Google · Location context attached', 'jcp-core' ); ?></span>
					</div>
				</article>

				<article class="jpd-result-card">
					<p class="jpd-result-card__channel"><?php esc_html_e( 'Social', 'jcp-core' ); ?></p>
					<h3 class="jpd-result-card__title"><?php esc_html_e( 'Automatically posted to social', 'jcp-core' ); ?></h3>
					<div class="jcp-sm-social">
						<div class="jcp-sm-social__head">
							<img class="jcp-sm-social__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="34" height="34" loading="lazy" decoding="async" data-jpd-job-photo />
							<div>
								<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Just now', 'jcp-core' ); ?> · <span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span></span>
							</div>
						</div>
						<p class="jcp-sm-social__copy" data-jpd-social-copy><?php esc_html_e( 'Another job wrapped. Water heater replacement done right — proof from the field.', 'jcp-core' ); ?></p>
						<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="200" loading="lazy" decoding="async" data-jpd-job-photo />
						<div class="jcp-sm-social__reactions" aria-hidden="true">
							<span><?php esc_html_e( 'Like', 'jcp-core' ); ?></span>
							<span><?php esc_html_e( 'Comment', 'jcp-core' ); ?></span>
							<span><?php esc_html_e( 'Share', 'jcp-core' ); ?></span>
						</div>
					</div>
				</article>
			</div>

			<div class="jpd-results-secondary">
				<article class="jpd-result-card jpd-result-card--directory">
					<p class="jpd-result-card__channel"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
					<h3 class="jpd-result-card__title"><?php esc_html_e( 'Published to your public JCP presence', 'jcp-core' ); ?></h3>
					<p class="jpd-result-card__note"><?php esc_html_e( 'Completed jobs become public proof inside JobCapturePro so customers can see the services and areas your business actually works in.', 'jcp-core' ); ?></p>
					<div class="jcp-sm-directory">
						<p class="jcp-sm-directory__label"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
						<div class="jcp-sm-directory__card" role="article">
							<div class="jcp-sm-directory__head">
								<img class="jcp-sm-directory__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="40" height="40" loading="lazy" decoding="async" data-jpd-job-photo />
								<div>
									<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
									<span>
										<?php if ( $icon( 'map-pin' ) ) : ?>
											<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="12" height="12" />
										<?php endif; ?>
										<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
									</span>
								</div>
							</div>
							<div class="jcp-sm-directory__meta">
								<span><?php esc_html_e( 'Public job proof', 'jcp-core' ); ?></span>
								<span aria-hidden="true">·</span>
								<span><?php esc_html_e( 'Service-area context', 'jcp-core' ); ?></span>
							</div>
							<div class="jcp-sm-directory__proof">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="80" height="60" loading="lazy" decoding="async" data-jpd-job-photo />
								<div>
									<strong data-jpd-directory-latest><?php esc_html_e( 'Latest: Water heater replacement', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Added from today’s completed job', 'jcp-core' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</article>

				<article class="jpd-result-card">
					<p class="jpd-result-card__channel"><?php esc_html_e( 'Review opportunity', 'jcp-core' ); ?></p>
					<h3 class="jpd-result-card__title"><?php esc_html_e( 'Ask while the job is still fresh', 'jcp-core' ); ?></h3>
					<div class="jpd-review-ask">
						<div class="jpd-review-ask__qr" aria-hidden="true">
							<?php if ( $icon( 'qr-code' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="64" height="64" />
							<?php endif; ?>
							<span><?php esc_html_e( 'Scan to review', 'jcp-core' ); ?></span>
						</div>
						<p><?php esc_html_e( 'Show the customer a QR or send a link before the truck leaves.', 'jcp-core' ); ?></p>
					</div>
				</article>
			</div>

			<p class="jpd-publish-note"><?php esc_html_e( 'Automatic publishing depends on connected channels.', 'jcp-core' ); ?></p>

			<!-- 19. Immediate trial CTA -->
			<div class="rankings-cta jpd-trial-bridge" id="jpdTrialBridge">
				<div class="cta-content">
					<h2 class="jcp-section-headline"><?php esc_html_e( 'What if this happened after every job your crew finished?', 'jcp-core' ); ?></h2>
					<p class="cta-paragraph"><?php esc_html_e( 'Instead of letting job photos disappear into phones and CRMs, every completed job can keep adding fresh proof to the places customers search before they call.', 'jcp-core' ); ?></p>
				</div>
				<div class="cta-button-wrapper">
					<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="convert" id="jpdTrialCta">
						<?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?>
					</a>
					<p class="cta-note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 22. Compounding value -->
<section class="jcp-section jpd-compound-section" aria-labelledby="jpd-compound-title">
	<div class="jcp-container">
		<header class="rankings-header">
			<h2 id="jpd-compound-title" class="jcp-section-headline"><?php esc_html_e( 'One job is useful. Every job compounds the advantage.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'As your crews finish more work, JobCapturePro keeps building a larger body of real, recent job proof across your connected marketing channels.', 'jcp-core' ); ?></p>
		</header>
		<div class="jpd-compound-visual" aria-hidden="true">
			<div class="jpd-compound-visual__row">
				<article class="jpd-compound-visual__job">
					<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="120" height="90" loading="lazy" decoding="async" />
					<span><?php esc_html_e( '1 completed job', 'jcp-core' ); ?></span>
				</article>
				<span class="jpd-compound-visual__arrow">→</span>
				<ul class="jpd-compound-visual__outputs">
					<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Review', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="jpd-compound-visual__grow">
				<span><?php esc_html_e( 'More completed jobs', 'jcp-core' ); ?></span>
				<span class="jpd-compound-visual__arrow">→</span>
				<span><?php esc_html_e( 'Growing body of fresh public proof', 'jcp-core' ); ?></span>
			</div>
		</div>
	</div>
</section>

<?php
// 23. Business outcomes — established benefits grid.
if ( function_exists( 'jcp_niche_render_benefits' ) ) {
	jcp_niche_render_benefits(
		[
			'benefits' => [
				'headline'        => __( 'What this changes for your business', 'jcp-core' ),
				'show_icons'      => true,
				'show_card_stats' => false,
				'items'           => [
					[
						'title' => __( 'Get found with fresher local proof', 'jcp-core' ),
						'body'  => __( 'Real completed jobs give your website and connected profiles a steady stream of recent, service-specific, location-relevant content.', 'jcp-core' ),
						'icon'  => 'map-pin',
					],
					[
						'title' => __( 'Build trust before the phone call', 'jcp-core' ),
						'body'  => __( 'Prospects see real work your company actually completed instead of generic marketing claims.', 'jcp-core' ),
						'icon'  => 'badge-check',
					],
					[
						'title' => __( 'Stop making marketing another job', 'jcp-core' ),
						'body'  => __( 'Your crews document the work. JobCapturePro handles the repeatable publishing workflow across connected channels.', 'jcp-core' ),
						'icon'  => 'message-square',
					],
				],
			],
		]
	);
}
?>

<!-- 24. Directory feature section -->
<section class="jcp-section jpd-directory-feature" id="jpd-directory" aria-labelledby="jpd-directory-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout">
			<div>
				<h2 id="jpd-directory-title" class="jcp-section-headline"><?php esc_html_e( 'Your finished work also builds a public footprint in the JCP Directory.', 'jcp-core' ); ?></h2>
				<p class="rankings-subtitle"><?php esc_html_e( 'Completed jobs become public proof inside JobCapturePro — showing services performed and job/service-area context so prospects can evaluate real work, not just claims.', 'jcp-core' ); ?></p>
				<ul class="jpd-directory-points">
					<li><?php esc_html_e( 'Public completed-job proof', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Services you actually perform', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Job and service-area context', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Business visibility inside the JCP ecosystem', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="jcp-sm-directory">
				<p class="jcp-sm-directory__label"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
				<div class="jcp-sm-directory__card" role="article">
					<div class="jcp-sm-directory__head">
						<img class="jcp-sm-directory__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="48" height="48" loading="lazy" decoding="async" />
						<div>
							<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
							<span>
								<?php if ( $icon( 'map-pin' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="12" height="12" />
								<?php endif; ?>
								<?php echo esc_html( $default_city ); ?>
							</span>
						</div>
					</div>
					<div class="jcp-sm-directory__meta">
						<span><?php esc_html_e( 'Public job proof', 'jcp-core' ); ?></span>
						<span aria-hidden="true">·</span>
						<span><?php esc_html_e( 'Active presence', 'jcp-core' ); ?></span>
					</div>
					<div class="jcp-sm-directory__proof">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="96" height="72" loading="lazy" decoding="async" />
						<div>
							<strong><?php esc_html_e( 'Latest completed job', 'jcp-core' ); ?></strong>
							<span><?php esc_html_e( 'Service + area context from the field', 'jcp-core' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
// 25. Local search signals.
if ( function_exists( 'jcp_niche_render_benefits' ) ) {
	jcp_niche_render_benefits(
		[
			'benefits' => [
				'headline'        => __( 'Turn the work you already do into stronger local signals.', 'jcp-core' ),
				'subheadline'     => __( 'Real jobs create something generic marketing content cannot: fresh evidence of what you do, where you do it and how recently you did it.', 'jcp-core' ),
				'closing'         => __( 'JobCapturePro helps create fresh, location-relevant, real-world content that can support stronger local visibility over time.', 'jcp-core' ),
				'show_icons'      => true,
				'show_card_stats' => false,
				'section_id'      => 'local-signals',
				'items'           => [
					[
						'title' => __( 'Freshness', 'jcp-core' ),
						'body'  => __( 'Recent completed work keeps your online presence active.', 'jcp-core' ),
						'icon'  => 'badge-check',
					],
					[
						'title' => __( 'Local relevance', 'jcp-core' ),
						'body'  => __( 'Real jobs carry service and location context.', 'jcp-core' ),
						'icon'  => 'map-pin',
					],
					[
						'title' => __( 'Proof', 'jcp-core' ),
						'body'  => __( 'Prospects see evidence that you actually perform the services you advertise.', 'jcp-core' ),
						'icon'  => 'star',
					],
				],
			],
		]
	);
}
?>

<?php
// 26. Four verified reviews.
if ( function_exists( 'jcp_niche_render_testimonials' ) && $reviews !== [] ) {
	$faces = [
		[ 'image_url' => $campaign . 'jcp-campaign-face-operator.jpg', 'image_alt' => '' ],
		[ 'image_url' => $campaign . 'jcp-campaign-face-owner.jpg', 'image_alt' => '' ],
		[ 'image_url' => $campaign . 'jcp-campaign-crew-review.jpg', 'image_alt' => '' ],
	];
	$normalized = [];
	foreach ( $reviews as $r ) {
		if ( ! is_array( $r ) ) {
			continue;
		}
		$normalized[] = [
			'id'         => (string) ( $r['id'] ?? '' ),
			'name'       => (string) ( $r['name'] ?? '' ),
			'role'       => (string) ( $r['role'] ?? '' ),
			'quote'      => (string) ( $r['quote'] ?? '' ),
			'rating'     => (int) ( $r['rating'] ?? 5 ),
			'avatar_url' => (string) ( $r['avatar'] ?? '' ),
			'avatar_alt' => (string) ( $r['avatarAlt'] ?? $r['name'] ?? '' ),
		];
	}
	jcp_niche_render_testimonials(
		[
			'headline'         => __( 'What people using JobCapturePro are saying', 'jcp-core' ),
			'show_eyebrow'     => false,
			'show_subheadline' => false,
			'per_view'         => 4,
			'show_stars'       => true,
			'show_roles'       => true,
			'section_id'       => 'testimonials',
			'faces_label'      => __( 'Real crews. Real completed jobs.', 'jcp-core' ),
			'faces'            => $faces,
			'reviews'          => $normalized,
		]
	);
}
?>

<!-- 27. Final CTA -->
<section class="jcp-section rankings-section jcp-niche-final">
	<div class="jcp-container">
		<div class="rankings-cta">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'Stop letting finished jobs disappear into the camera roll.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'Let the next job your crew finishes start building proof for the one after it.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="final">
					<?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?>
				</a>
				<p class="cta-note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<p class="jpd-final-demo-link jcp-container">
	<a href="#jpd-teaser" data-jpd-scroll-teaser><?php esc_html_e( 'See the free personalized demo →', 'jcp-core' ); ?></a>
	<?php if ( $show_case ) : ?>
		<span aria-hidden="true"> · </span>
		<a class="jpd-case-link" href="<?php echo esc_url( $case_href ); ?>"><?php esc_html_e( 'Apply for the 90-day case study →', 'jcp-core' ); ?></a>
	<?php endif; ?>
</p>
