<?php
/**
 * Proof Gap survey app body — Phase 2 persuasion shell.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign   = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$integ      = trailingslashit( get_template_directory_uri() ) . 'assets/integrations/';
$photo_job  = $campaign . 'jcp-campaign-job-proof-640.webp';
$slots      = function_exists( 'jcp_proof_gap_review_slots' ) ? jcp_proof_gap_review_slots() : [ 'email' => null, 'trial' => null ];
$email_rev  = is_array( $slots['email'] ?? null ) ? $slots['email'] : null;
$trial_rev  = is_array( $slots['trial'] ?? null ) ? $slots['trial'] : null;

$render_review = static function ( ?array $review, string $slot ): void {
	if ( ! $review || trim( (string) ( $review['quote'] ?? '' ) ) === '' ) {
		echo '<aside class="pg-review-slot" data-pg-review-slot="' . esc_attr( $slot ) . '" aria-label="' . esc_attr__( 'Customer review placeholder', 'jcp-core' ) . '">';
		echo '<p class="pg-review-slot__placeholder">' . esc_html__( 'Approved review will appear here.', 'jcp-core' ) . '</p>';
		echo '</aside>';
		return;
	}
	$avatar = (string) ( $review['avatar'] ?? $review['avatar_url'] ?? '' );
	$name   = (string) ( $review['name'] ?? '' );
	$role   = (string) ( $review['role'] ?? '' );
	$quote  = (string) ( $review['quote'] ?? '' );
	echo '<aside class="pg-review-slot pg-review-slot--live" data-pg-review-slot="' . esc_attr( $slot ) . '">';
	echo '<blockquote class="pg-review">';
	echo '<p class="pg-review__quote">“' . esc_html( $quote ) . '”</p>';
	echo '<footer class="pg-review__footer">';
	if ( $avatar !== '' ) {
		echo '<img class="pg-review__avatar" src="' . esc_url( $avatar ) . '" alt="" width="40" height="40" loading="lazy" decoding="async" />';
	}
	echo '<span class="pg-review__meta"><strong>' . esc_html( $name ) . '</strong>';
	if ( $role !== '' ) {
		echo '<em>' . esc_html( $role ) . '</em>';
	}
	echo '</span></footer></blockquote></aside>';
};
?>

<div class="pg-layout">
	<div class="pg-stage" id="pgStage" aria-live="polite">

		<section class="pg-state pg-state--welcome" data-pg-state="welcome" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'The 60-second proof gap check', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'How much of your best work disappears after the truck leaves?', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Your crew is already creating job photos, locations, customer proof and review opportunities.', 'jcp-core' ); ?></p>
				<p class="pg-sub pg-sub--tight"><?php esc_html_e( 'Answer 4 quick questions and we’ll show you how much of it may be going unused — and what one finished job could become with JobCapturePro.', 'jcp-core' ); ?></p>
				<div class="pg-welcome-mark" data-pg-story-mark aria-hidden="true">
					<svg class="pg-welcome-mark__svg" viewBox="0 0 160 100" width="160" height="100" role="img">
						<rect x="10" y="14" width="70" height="72" rx="10" fill="none" stroke="currentColor" stroke-width="3" opacity="0.4"/>
						<path d="M24 36h42M24 50h30M24 64h36" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" opacity="0.45"/>
						<circle cx="118" cy="50" r="28" fill="none" stroke="currentColor" stroke-width="3" opacity="0.55"/>
						<path d="M106 50l8 8 18-18" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.8"/>
					</svg>
					<span class="pg-welcome-mark__caption"><?php esc_html_e( 'Proof Files / Proof Stamp slot', 'jcp-core' ); ?></span>
				</div>
				<button type="button" class="btn btn-primary pg-btn" id="pgWelcomeCta"><?php esc_html_e( 'Find My Proof Gap →', 'jcp-core' ); ?></button>
				<p class="pg-micro"><?php esc_html_e( 'About 60 seconds · No phone required · No credit card', 'jcp-core' ); ?></p>
			</div>
		</section>

		<section class="pg-state" data-pg-state="trade" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-trade-title"><?php esc_html_e( 'What kind of work does your company do most?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--grid" role="group" aria-labelledby="pg-trade-title" data-pg-choices="trade"></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="current_workflow" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-workflow-title"><?php esc_html_e( 'Where do most of your finished-job photos live today?', 'jcp-core' ); ?></h1>
				<div class="pg-choices" role="group" aria-labelledby="pg-workflow-title" data-pg-choices="current_workflow"></div>
				<div class="proof-gap-insight-card" data-pg-insight="workflow" hidden></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="jobs_per_week" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-jobs-title"><?php esc_html_e( 'About how many jobs does your company finish in a normal week?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--compact" role="group" aria-labelledby="pg-jobs-title" data-pg-choices="jobs_per_week"></div>
				<div class="proof-gap-insight-card" data-pg-insight="jobs" hidden></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="public_proof_percentage" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-proof-title"><?php esc_html_e( 'After a job is finished, how often does it become something a future customer can actually see?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--proof" role="group" aria-labelledby="pg-proof-title" data-pg-choices="public_proof_percentage"></div>
				<div class="proof-gap-insight-card" data-pg-insight="proof" hidden></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="proof_gap_result" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pgResultTitle"><?php esc_html_e( 'Here’s the gap your answers describe.', 'jcp-core' ); ?></h1>
				<div class="pg-result" id="pgResultCard">
					<div class="pg-result__compare pg-result__compare--premium" aria-label="<?php esc_attr_e( 'Work created versus public proof', 'jcp-core' ); ?>">
						<div class="pg-result__col">
							<span class="pg-result__col-label"><?php esc_html_e( 'Your company creates', 'jcp-core' ); ?></span>
							<strong class="pg-result__col-value" id="pgResultCompleted">—</strong>
						</div>
						<div class="pg-result__col">
							<span class="pg-result__col-label"><?php esc_html_e( 'Becoming public proof', 'jcp-core' ); ?></span>
							<strong class="pg-result__col-value" id="pgResultPublic">—</strong>
						</div>
						<div class="pg-result__col pg-result__col--gap" id="pgResultInvisibleWrap" hidden>
							<span class="pg-result__col-label"><?php esc_html_e( 'Potentially staying invisible', 'jcp-core' ); ?></span>
							<strong class="pg-result__col-value" id="pgResultInvisible">—</strong>
						</div>
					</div>
					<p class="pg-result__note"><?php esc_html_e( 'Based on the ranges you selected.', 'jcp-core' ); ?></p>
					<p class="pg-result__support" id="pgResultSupport"><?php esc_html_e( 'Your crew is already doing the expensive part: the actual work. The opportunity is putting more of that finished work in front of the next customer — without giving your team another marketing job.', 'jcp-core' ); ?></p>
				</div>
				<button type="button" class="btn btn-primary pg-btn" id="pgResultCta"><?php esc_html_e( 'Show Me What One Job Could Become →', 'jcp-core' ); ?></button>
			</div>
		</section>

		<section class="pg-state" data-pg-state="email_capture" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'Save your personalized Proof Plan', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Enter the best email to save your answers, see what one of your completed jobs could become, and come back anytime.', 'jcp-core' ); ?></p>
				<form class="pg-email" id="pgEmailForm" novalidate>
					<label class="pg-email__label" for="pgEmail"><?php esc_html_e( 'Email', 'jcp-core' ); ?> <span class="pg-req">*</span></label>
					<input class="pg-email__input" id="pgEmail" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@company.com" />
					<p class="pg-email__error" id="pgEmailError" role="alert" hidden></p>
					<button type="submit" class="btn btn-primary pg-btn" id="pgEmailSubmit"><?php esc_html_e( 'Show Me My Job Transformation →', 'jcp-core' ); ?></button>
					<p class="pg-email__consent"><?php esc_html_e( 'We’ll email your results and relevant JobCapturePro follow-up. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</form>
				<?php $render_review( $email_rev, 'email' ); ?>
			</div>
		</section>

		<section class="pg-state" data-pg-state="product_reveal" hidden>
			<div class="pg-state__inner pg-state__inner--wide">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pgRevealTitle"><?php esc_html_e( 'Here’s what one finished job could become.', 'jcp-core' ); ?></h1>

				<article class="pg-job-card" id="pgJobCard">
					<img class="pg-job-card__photo" id="pgJobPhoto" src="<?php echo esc_url( $photo_job ); ?>" alt="" width="640" height="400" loading="lazy" decoding="async" />
					<div class="pg-job-card__body">
						<strong class="pg-job-card__title" id="pgJobTitle">—</strong>
						<p class="pg-job-card__meta" id="pgJobMeta">—</p>
						<p class="pg-job-card__source" id="pgRevealSource">—</p>
					</div>
				</article>

				<div class="pg-reveal-flow" id="pgReveal" data-pg-reveal>
					<div class="pg-reveal-flow__step">
						<span class="pg-reveal-flow__label"><?php esc_html_e( 'Your workflow', 'jcp-core' ); ?></span>
						<strong id="pgRevealWorkflowLabel">—</strong>
						<img class="pg-reveal-flow__logo" id="pgRevealWorkflowLogo" alt="" width="28" height="28" hidden
							data-hcp="<?php echo esc_url( $integ . 'housecall-pro.svg' ); ?>"
							data-cc="<?php echo esc_url( $integ . 'companycam.svg' ); ?>" />
					</div>
					<div class="pg-reveal-flow__arrow" aria-hidden="true">↓</div>
					<div class="pg-reveal-flow__step pg-reveal-flow__step--jcp">
						<span class="pg-reveal-flow__label"><?php esc_html_e( 'JobCapturePro', 'jcp-core' ); ?></span>
						<strong><?php esc_html_e( 'JCP takes it from here.', 'jcp-core' ); ?></strong>
					</div>
					<div class="pg-reveal-flow__arrow" aria-hidden="true">↓</div>
					<div class="pg-reveal-flow__step">
						<span class="pg-reveal-flow__label"><?php esc_html_e( 'Public proof', 'jcp-core' ); ?></span>
						<ul class="pg-reveal__outputs" aria-label="<?php esc_attr_e( 'Supported concepts', 'jcp-core' ); ?>">
							<li data-out="website"><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
							<li data-out="google"><?php esc_html_e( 'Google', 'jcp-core' ); ?> <em><?php esc_html_e( 'when connected', 'jcp-core' ); ?></em></li>
							<li data-out="social"><?php esc_html_e( 'Social', 'jcp-core' ); ?> <em><?php esc_html_e( 'when connected', 'jcp-core' ); ?></em></li>
							<li data-out="review"><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
							<li data-out="directory"><?php esc_html_e( 'JCP Directory', 'jcp-core' ); ?></li>
						</ul>
						<p class="pg-reveal-flow__note"><?php esc_html_e( 'Channel availability depends on your plan and connected channels.', 'jcp-core' ); ?></p>
					</div>
				</div>

				<p class="pg-continuity" id="pgContinuityNote" hidden></p>

				<div class="pg-payoff">
					<p><?php esc_html_e( 'No rewriting the job.', 'jcp-core' ); ?></p>
					<p><?php esc_html_e( 'No moving photos between apps.', 'jcp-core' ); ?></p>
					<p><?php esc_html_e( 'No sitting in the truck trying to invent a Facebook caption.', 'jcp-core' ); ?></p>
					<p class="pg-payoff__close"><strong><?php esc_html_e( 'Your crew does the work. JCP makes the work keep working.', 'jcp-core' ); ?></strong></p>
				</div>

				<button type="button" class="btn btn-primary pg-btn" id="pgRevealContinue"><?php esc_html_e( 'See My Trial Plan →', 'jcp-core' ); ?></button>
			</div>
		</section>

		<section class="pg-state" data-pg-state="trial_bridge" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'Your crew already does the hard part.', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Keep finishing jobs. Keep taking the photos. Let JobCapturePro handle what happens next.', 'jcp-core' ); ?></p>
				<ul class="pg-summary" id="pgTrialSummary"></ul>
				<p class="pg-continuity pg-continuity--trial" id="pgTrialContinuity" hidden></p>
				<p class="pg-benefit"><?php esc_html_e( 'Turn finished jobs into structured proof for the places your future customers look — based on your plan and connected channels.', 'jcp-core' ); ?></p>
				<a class="btn btn-primary pg-btn" id="pgTrialCta" href="#"><?php esc_html_e( 'Start My Free 14-Day Trial →', 'jcp-core' ); ?></a>
				<p class="pg-micro"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<?php $render_review( $trial_rev, 'trial' ); ?>
			</div>
		</section>
	</div>

	<aside class="pg-story" aria-hidden="true">
		<div class="pg-story-mark" data-pg-story-mark>
			<svg class="pg-story-mark__svg" viewBox="0 0 120 160" width="120" height="160" role="img" aria-label="">
				<rect x="8" y="12" width="104" height="136" rx="12" fill="none" stroke="currentColor" stroke-width="3" opacity="0.35"/>
				<path d="M28 48h64M28 72h48M28 96h56" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity="0.45"/>
				<circle cx="88" cy="118" r="18" fill="none" stroke="currentColor" stroke-width="3" opacity="0.5"/>
				<path d="M80 118l6 6 12-14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
			</svg>
			<span class="pg-story-mark__caption"><?php esc_html_e( 'Proof Files continuity', 'jcp-core' ); ?></span>
		</div>
	</aside>
</div>
