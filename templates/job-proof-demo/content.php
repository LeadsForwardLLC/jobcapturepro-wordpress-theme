<?php
/**
 * Job Proof Demo page body markup.
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
?>

<section class="jpd-hero" aria-labelledby="jpd-hero-title">
	<div class="jpd-shell">
		<p class="jpd-eyebrow"><?php esc_html_e( 'Your best jobs shouldn’t die in the camera roll', 'jcp-core' ); ?></p>
		<h1 id="jpd-hero-title" class="jpd-hero__title"><?php esc_html_e( 'One Finished Job Should Keep Working After the Crew Leaves.', 'jcp-core' ); ?></h1>
		<p class="jpd-hero__sub"><?php esc_html_e( 'Your crew already creates the proof. See how JobCapturePro turns one completed job into marketing assets your customers can actually find.', 'jcp-core' ); ?></p>
		<div class="jpd-hero__actions">
			<a class="jpd-btn jpd-btn--primary" href="#proof" data-jpd-start><?php esc_html_e( 'See What One Job Creates →', 'jcp-core' ); ?></a>
			<p class="jpd-hero__micro"><?php esc_html_e( 'Free · Takes about 60 seconds · No signup required', 'jcp-core' ); ?></p>
			<p class="jpd-hero__skip">
				<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Already get it? Start Free Trial →', 'jcp-core' ); ?></a>
			</p>
		</div>
		<div class="jpd-hero__flow" aria-hidden="true">
			<span>Finished job</span>
			<span class="jpd-hero__arrow">→</span>
			<span>JCP</span>
			<span class="jpd-hero__arrow">→</span>
			<span>Public proof</span>
		</div>
	</div>
</section>

<section class="jpd-problem" aria-labelledby="jpd-problem-title">
	<div class="jpd-shell jpd-shell--narrow">
		<h2 id="jpd-problem-title" class="jpd-section-title"><?php esc_html_e( 'Most Job Photos Never Become Marketing.', 'jcp-core' ); ?></h2>
		<ol class="jpd-process jpd-process--bad" aria-label="<?php esc_attr_e( 'Without JobCapturePro', 'jcp-core' ); ?>">
			<li><?php esc_html_e( 'Job finished', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Photo taken', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Camera roll / CRM', 'jcp-core' ); ?></li>
			<li class="jpd-process__dead"><?php esc_html_e( 'Nothing', 'jcp-core' ); ?></li>
		</ol>
		<p class="jpd-problem__copy"><?php esc_html_e( 'The work happened. The proof exists. But if customers never see it, it does almost nothing for the next sale.', 'jcp-core' ); ?></p>
		<p class="jpd-problem__with"><?php esc_html_e( 'With JobCapturePro', 'jcp-core' ); ?></p>
		<ol class="jpd-process jpd-process--good" aria-label="<?php esc_attr_e( 'With JobCapturePro', 'jcp-core' ); ?>">
			<li><?php esc_html_e( 'Job finished', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Check-in', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Public proof', 'jcp-core' ); ?></li>
		</ol>
		<a class="jpd-btn jpd-btn--secondary" href="#proof" data-jpd-start><?php esc_html_e( 'Show Me →', 'jcp-core' ); ?></a>
	</div>
</section>

<section id="proof" class="jpd-proof" aria-labelledby="jpd-proof-title" data-jpd-proof>
	<div class="jpd-shell">
		<p class="jpd-proof__progress" id="jpdProofProgress" aria-live="polite"><?php esc_html_e( 'Moment 1 of 3', 'jcp-core' ); ?></p>

		<!-- Moment 1: Finished job -->
		<div class="jpd-moment is-active" data-jpd-moment="1" id="jpdMoment1">
			<h2 id="jpd-proof-title" class="jpd-section-title"><?php esc_html_e( 'The Job Is Done.', 'jcp-core' ); ?></h2>
			<article class="jpd-job-card">
				<div class="jpd-job-card__media">
					<img
						src="<?php echo esc_url( $photo_url ); ?>"
						alt="<?php esc_attr_e( 'Completed water heater replacement job photo', 'jcp-core' ); ?>"
						width="640"
						height="420"
						loading="eager"
						decoding="async"
						data-no-lazy
						data-fallback="<?php echo esc_url( $photo_fallback ); ?>"
					/>
					<span class="jpd-job-card__badge"><?php esc_html_e( 'Completed', 'jcp-core' ); ?></span>
				</div>
				<div class="jpd-job-card__body">
					<h3 class="jpd-job-card__service"><?php esc_html_e( 'Water Heater Replacement', 'jcp-core' ); ?></h3>
					<p class="jpd-job-card__meta"><?php esc_html_e( 'Austin, TX · Home services', 'jcp-core' ); ?></p>
					<p class="jpd-job-card__note"><?php esc_html_e( 'Normally, this is where the marketing stops.', 'jcp-core' ); ?></p>
				</div>
			</article>
			<button type="button" class="jpd-btn jpd-btn--primary" data-jpd-next="2"><?php esc_html_e( 'Put This Job to Work →', 'jcp-core' ); ?></button>
		</div>

		<!-- Moment 2: Transformation -->
		<div class="jpd-moment" data-jpd-moment="2" id="jpdMoment2" hidden>
			<h2 class="jpd-section-title"><?php esc_html_e( 'JobCapturePro Turns the Work Into Proof.', 'jcp-core' ); ?></h2>
			<div class="jpd-checkin" aria-live="polite">
				<div class="jpd-checkin__status" id="jpdCheckinStatus"><?php esc_html_e( 'Creating job proof…', 'jcp-core' ); ?></div>
				<div class="jpd-checkin__card" id="jpdCheckinCard">
					<img
						class="jpd-checkin__photo"
						src="<?php echo esc_url( $photo_url ); ?>"
						alt=""
						width="96"
						height="72"
						loading="lazy"
						decoding="async"
					/>
					<div class="jpd-checkin__copy">
						<p class="jpd-checkin__service"><?php esc_html_e( 'Water Heater Replacement', 'jcp-core' ); ?></p>
						<p class="jpd-checkin__loc"><?php esc_html_e( 'Austin, TX', 'jcp-core' ); ?></p>
						<p class="jpd-checkin__desc"><?php esc_html_e( 'Installed a high-efficiency water heater, verified venting, and documented the completed work with geotagged job proof ready for your website and Google.', 'jcp-core' ); ?></p>
					</div>
				</div>
			</div>
			<button type="button" class="jpd-btn jpd-btn--primary" data-jpd-next="3" id="jpdRevealOutputs" hidden><?php esc_html_e( 'See Where This Job Can Work →', 'jcp-core' ); ?></button>
		</div>

		<!-- Moment 3: Outputs + value + trial (same state) -->
		<div class="jpd-moment" data-jpd-moment="3" id="jpdMoment3" hidden>
			<h2 class="jpd-section-title"><?php esc_html_e( 'One Job. Now Working in More Places.', 'jcp-core' ); ?></h2>
			<p class="jpd-moment__sub"><?php esc_html_e( 'Instead of disappearing into a camera roll, the finished job becomes reusable proof across your marketing.', 'jcp-core' ); ?></p>

			<div class="jpd-outputs" role="list">
				<article class="jpd-output" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Website', 'jcp-core' ); ?></p>
					<p class="jpd-output__status"><?php esc_html_e( 'Ready for your website', 'jcp-core' ); ?></p>
					<div class="jpd-output__preview jpd-output__preview--web">
						<strong><?php esc_html_e( 'Water Heater Replacement', 'jcp-core' ); ?></strong>
						<span><?php esc_html_e( 'Austin, TX', 'jcp-core' ); ?></span>
					</div>
				</article>
				<article class="jpd-output" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Google', 'jcp-core' ); ?></p>
					<p class="jpd-output__status"><?php esc_html_e( 'Prepared for Google', 'jcp-core' ); ?></p>
					<div class="jpd-output__preview jpd-output__preview--g">
						<span><?php esc_html_e( 'GBP post draft · location context attached', 'jcp-core' ); ?></span>
					</div>
				</article>
				<article class="jpd-output" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Social', 'jcp-core' ); ?></p>
					<p class="jpd-output__status"><?php esc_html_e( 'Social post created', 'jcp-core' ); ?></p>
					<div class="jpd-output__preview jpd-output__preview--social">
						<span><?php esc_html_e( 'Photo + caption ready to share', 'jcp-core' ); ?></span>
					</div>
				</article>
				<article class="jpd-output" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Review', 'jcp-core' ); ?></p>
					<p class="jpd-output__status"><?php esc_html_e( 'Review opportunity ready', 'jcp-core' ); ?></p>
					<div class="jpd-output__preview jpd-output__preview--review">
						<span><?php esc_html_e( 'Request while the job is fresh', 'jcp-core' ); ?></span>
					</div>
				</article>
				<article class="jpd-output" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Local proof', 'jcp-core' ); ?></p>
					<p class="jpd-output__status"><?php esc_html_e( 'Job proof added', 'jcp-core' ); ?></p>
					<div class="jpd-output__preview jpd-output__preview--local">
						<span><?php esc_html_e( 'Public job proof for your service area', 'jcp-core' ); ?></span>
					</div>
				</article>
			</div>

			<div class="jpd-bridge">
				<h3 class="jpd-bridge__title"><?php esc_html_e( 'Now Imagine This Happening After Every Finished Job.', 'jcp-core' ); ?></h3>
				<p class="jpd-bridge__copy"><?php esc_html_e( 'Your crews are already doing the expensive part — completing the work. JobCapturePro helps make that work visible after the truck leaves.', 'jcp-core' ); ?></p>
				<div class="jpd-compare" aria-label="<?php esc_attr_e( 'Comparison', 'jcp-core' ); ?>">
					<div>
						<p class="jpd-compare__label"><?php esc_html_e( 'Without JCP', 'jcp-core' ); ?></p>
						<ul>
							<li><?php esc_html_e( 'Jobs happen.', 'jcp-core' ); ?></li>
							<li><?php esc_html_e( 'Photos pile up.', 'jcp-core' ); ?></li>
							<li><?php esc_html_e( 'Proof disappears.', 'jcp-core' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="jpd-compare__label jpd-compare__label--good"><?php esc_html_e( 'With JCP', 'jcp-core' ); ?></p>
						<ul>
							<li><?php esc_html_e( 'Jobs happen.', 'jcp-core' ); ?></li>
							<li><?php esc_html_e( 'Proof gets captured.', 'jcp-core' ); ?></li>
							<li><?php esc_html_e( 'Marketing keeps getting fresh evidence of the work.', 'jcp-core' ); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="jpd-convert" id="jpdConvert">
				<p class="jpd-eyebrow"><?php esc_html_e( 'Put your next job to work', 'jcp-core' ); ?></p>
				<h3 class="jpd-convert__title"><?php esc_html_e( 'Ready to Do This With Your Own Jobs?', 'jcp-core' ); ?></h3>
				<a
					class="jpd-btn jpd-btn--primary jpd-btn--trial"
					href="<?php echo esc_url( $trial_href ); ?>"
					data-jpd-trial
					data-jpd-source="convert"
					id="jpdTrialCta"
				><?php esc_html_e( 'Start My Free 14-Day Trial →', 'jcp-core' ); ?></a>
				<p class="jpd-convert__note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<p class="jpd-convert__secondary">
					<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP Expert', 'jcp-core' ); ?></a>
				</p>
				<p class="jpd-convert__tertiary">
					<a href="<?php echo esc_url( $case_href ); ?>"><?php esc_html_e( 'Or apply for the 90-day case study', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>
