<?php
/**
 * Proof Gap survey — native mobile app shell (final CRO).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign  = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$integ     = trailingslashit( get_template_directory_uri() ) . 'assets/integrations/';
$map_url   = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';
$qr_url    = $campaign . 'ps-dummy-qr.png';
$slots     = function_exists( 'jcp_proof_gap_review_slots' ) ? jcp_proof_gap_review_slots() : [ 'email' => null, 'trial' => null ];
$email_rev = is_array( $slots['email'] ?? null ) ? $slots['email'] : null;
$trial_rev = is_array( $slots['trial'] ?? null ) ? $slots['trial'] : null;

$render_stars = static function ( int $rating ): void {
	if ( $rating < 1 ) {
		return;
	}
	$rating = min( 5, $rating );
	echo '<div class="pg-stars" aria-label="' . esc_attr( sprintf( /* translators: %d: star rating */ __( '%d out of 5 stars', 'jcp-core' ), $rating ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$on = $i <= $rating ? ' is-on' : '';
		echo '<span class="pg-stars__star' . esc_attr( $on ) . '" aria-hidden="true">★</span>';
	}
	echo '</div>';
};

$render_review = static function ( ?array $review, string $slot ) use ( $render_stars ): void {
	if ( ! $review || trim( (string) ( $review['quote'] ?? '' ) ) === '' ) {
		echo '<aside class="pg-review-slot" data-pg-review-slot="' . esc_attr( $slot ) . '"><p class="pg-review-slot__placeholder">' . esc_html__( 'Approved review will appear here.', 'jcp-core' ) . '</p></aside>';
		return;
	}
	$avatar = (string) ( $review['avatar'] ?? $review['avatar_url'] ?? '' );
	$name   = (string) ( $review['name'] ?? '' );
	$role   = (string) ( $review['role'] ?? '' );
	$quote  = (string) ( $review['quote'] ?? '' );
	$rating = isset( $review['rating'] ) ? (int) $review['rating'] : 0;
	echo '<aside class="pg-review-slot pg-review-slot--live" data-pg-review-slot="' . esc_attr( $slot ) . '">';
	echo '<blockquote class="pg-review">';
	if ( $rating > 0 ) {
		$render_stars( $rating );
	}
	echo '<p class="pg-review__quote">“' . esc_html( $quote ) . '”</p>';
	echo '<footer class="pg-review__footer">';
	if ( $avatar !== '' ) {
		echo '<img class="pg-review__avatar" src="' . esc_url( $avatar ) . '" alt="" width="36" height="36" loading="lazy" decoding="async" />';
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
				<p class="pg-sub"><?php esc_html_e( 'Your crew already creates the proof.', 'jcp-core' ); ?></p>
				<p class="pg-sub pg-sub--tight"><?php esc_html_e( 'Answer 4 quick questions and see how much may be going unused — and what one finished job could become with JobCapturePro.', 'jcp-core' ); ?></p>

				<div class="pg-welcome-visual" data-pg-welcome-visual data-creative="default" aria-hidden="true">
					<div class="pg-welcome-visual__flow">
						<span class="pg-welcome-visual__node"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
						<span class="pg-welcome-visual__arrow" aria-hidden="true">→</span>
						<span class="pg-welcome-visual__node pg-welcome-visual__node--stamp">
							<svg viewBox="0 0 32 32" width="18" height="18" aria-hidden="true"><circle cx="16" cy="16" r="12" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10 16l4 4 8-9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
							<?php esc_html_e( 'Proof stamp', 'jcp-core' ); ?>
						</span>
						<span class="pg-welcome-visual__arrow" aria-hidden="true">→</span>
						<span class="pg-welcome-visual__outs">
							<em><?php esc_html_e( 'Website', 'jcp-core' ); ?></em>
							<em><?php esc_html_e( 'Google', 'jcp-core' ); ?></em>
							<em><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></em>
							<em><?php esc_html_e( 'Social', 'jcp-core' ); ?></em>
							<em><?php esc_html_e( 'Directory', 'jcp-core' ); ?></em>
						</span>
					</div>
					<p class="pg-welcome-visual__tagline"><?php esc_html_e( 'The work is already done. JCP puts it to work.', 'jcp-core' ); ?></p>
				</div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="trade" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-trade-title"><?php esc_html_e( 'What kind of work does your company do most?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--grid pg-choices--trade" role="group" aria-labelledby="pg-trade-title" data-pg-choices="trade"></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="current_workflow" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-workflow-title"><?php esc_html_e( 'Where do most of your finished-job photos live today?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--workflow" role="group" aria-labelledby="pg-workflow-title" data-pg-choices="current_workflow"
					data-logo-hcp="<?php echo esc_url( $integ . 'housecall-pro.svg' ); ?>"
					data-logo-cc="<?php echo esc_url( $integ . 'companycam.svg' ); ?>"></div>
				<div class="pg-answer-summary" data-pg-summary="workflow" hidden>
					<span class="pg-answer-summary__label"><?php esc_html_e( 'Your current workflow', 'jcp-core' ); ?></span>
					<div class="pg-answer-summary__row">
						<span class="pg-answer-summary__value" data-pg-summary-value></span>
						<button type="button" class="pg-answer-summary__change" data-pg-change="workflow"><?php esc_html_e( 'Change', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="proof-gap-insight-card" data-pg-insight="workflow" hidden></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="jobs_per_week" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-jobs-title"><?php esc_html_e( 'About how many jobs does your company finish in a normal week?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--compact" role="group" aria-labelledby="pg-jobs-title" data-pg-choices="jobs_per_week"></div>
				<div class="pg-answer-summary" data-pg-summary="jobs" hidden>
					<span class="pg-answer-summary__label"><?php esc_html_e( 'Jobs each week', 'jcp-core' ); ?></span>
					<div class="pg-answer-summary__row">
						<span class="pg-answer-summary__value" data-pg-summary-value></span>
						<button type="button" class="pg-answer-summary__change" data-pg-change="jobs"><?php esc_html_e( 'Change', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="proof-gap-insight-card" data-pg-insight="jobs" hidden></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="public_proof_percentage" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-proof-title"><?php esc_html_e( 'After a job is finished, how often does it become something a future customer can actually see?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--proof" role="group" aria-labelledby="pg-proof-title" data-pg-choices="public_proof_percentage"></div>
				<div class="pg-answer-summary" data-pg-summary="proof" hidden>
					<span class="pg-answer-summary__label"><?php esc_html_e( 'Your answer', 'jcp-core' ); ?></span>
					<div class="pg-answer-summary__row">
						<span class="pg-answer-summary__value" data-pg-summary-value></span>
						<button type="button" class="pg-answer-summary__change" data-pg-change="proof"><?php esc_html_e( 'Change', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="proof-gap-insight-card" data-pg-insight="proof" hidden></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="proof_gap_result" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pgResultTitle"></h1>
				<div class="pg-result pg-result--visual" id="pgResultCard">
					<div class="pg-result__hero">
						<strong class="pg-result__hero-num" id="pgResultCompleted">—</strong>
						<span class="pg-result__hero-label"><?php esc_html_e( 'Completed jobs / year', 'jcp-core' ); ?></span>
					</div>
					<div class="pg-gap-viz" id="pgGapViz" aria-hidden="true"></div>
					<ul class="pg-result__stats">
						<li><span><?php esc_html_e( 'Customer-visible proof', 'jcp-core' ); ?></span><strong id="pgResultPublic">—</strong></li>
						<li id="pgResultInvisibleWrap" hidden><span><?php esc_html_e( 'Potentially staying invisible', 'jcp-core' ); ?></span><strong id="pgResultInvisible">—</strong></li>
					</ul>
					<p class="pg-result__note"><?php esc_html_e( 'Based on the ranges you selected.', 'jcp-core' ); ?></p>
					<p class="pg-result__support" id="pgResultSupport"><?php esc_html_e( 'Your crew already does the expensive part: the actual work. The opportunity is putting more of it in front of the next customer — without giving your team another marketing job.', 'jcp-core' ); ?></p>
				</div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="email_capture" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm"><?php esc_html_e( 'Save your personalized Proof Plan', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Enter the best email to save your answers, see what one of your completed jobs could become, and come back anytime.', 'jcp-core' ); ?></p>
				<form class="pg-email" id="pgEmailForm" novalidate>
					<label class="pg-email__label" for="pgEmail"><?php esc_html_e( 'Email', 'jcp-core' ); ?> <span class="pg-req">*</span></label>
					<input class="pg-email__input" id="pgEmail" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@company.com" />
					<p class="pg-email__error" id="pgEmailError" role="alert" hidden></p>
					<p class="pg-email__consent"><?php esc_html_e( 'We’ll email your results and relevant JobCapturePro follow-up. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</form>
				<?php $render_review( $email_rev, 'email' ); ?>
			</div>
		</section>

		<section class="pg-state pg-state--reveal" data-pg-state="product_reveal" hidden>
			<div class="pg-state__inner pg-state__inner--wide">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pgRevealTitle"><?php esc_html_e( 'Here’s what one finished job could become.', 'jcp-core' ); ?></h1>

				<article class="pg-job-card pg-job-card--compact" id="pgJobCard">
					<div class="pg-job-card__media" id="pgJobMedia">
						<img class="pg-job-card__photo" id="pgJobPhoto" alt="" width="640" height="360" loading="lazy" decoding="async" hidden />
						<div class="pg-job-card__neutral" id="pgJobNeutral" hidden>
							<span class="pg-job-card__neutral-icon" aria-hidden="true">✓</span>
							<span><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
						</div>
					</div>
					<div class="pg-job-card__body">
						<strong class="pg-job-card__title" id="pgJobTitle">—</strong>
						<p class="pg-job-card__meta"><?php esc_html_e( 'Completed · Job photos · Field location', 'jcp-core' ); ?></p>
						<p class="pg-job-card__source" id="pgRevealSource">—</p>
					</div>
				</article>

				<p class="pg-reveal-kicker"><?php esc_html_e( 'JCP takes it from here', 'jcp-core' ); ?></p>
				<div class="pg-dest-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Proof destinations', 'jcp-core' ); ?>">
					<button type="button" class="pg-dest-tab is-active" role="tab" aria-selected="true" data-dest="website"><?php esc_html_e( 'Website', 'jcp-core' ); ?></button>
					<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="google"><?php esc_html_e( 'Google', 'jcp-core' ); ?></button>
					<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="social"><?php esc_html_e( 'Social', 'jcp-core' ); ?></button>
					<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="reviews"><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></button>
					<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="directory"><?php esc_html_e( 'Directory', 'jcp-core' ); ?></button>
				</div>
				<div class="pg-dest-panel" id="pgDestPanel" role="tabpanel"
					data-map="<?php echo esc_url( $map_url ); ?>"
					data-qr="<?php echo esc_url( $qr_url ); ?>"></div>
				<p class="pg-dest-note"><?php esc_html_e( 'Availability depends on your plan and connected channels.', 'jcp-core' ); ?></p>

				<p class="pg-continuity" id="pgContinuityNote" hidden></p>
				<ul class="pg-chips" aria-label="<?php esc_attr_e( 'Payoff', 'jcp-core' ); ?>">
					<li><?php esc_html_e( 'No rewriting jobs', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No moving photos', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No writing captions in the truck', 'jcp-core' ); ?></li>
				</ul>
				<p class="pg-payoff-line"><strong><?php esc_html_e( 'Your crew does the work. JCP makes the work keep working.', 'jcp-core' ); ?></strong></p>
			</div>
		</section>

		<section class="pg-state" data-pg-state="trial_bridge" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm"><?php esc_html_e( 'Your crew already does the hard part.', 'jcp-core' ); ?></h1>
				<p class="pg-sub pg-sub--tight"><?php esc_html_e( 'Keep finishing jobs. Keep taking the photos. Let JobCapturePro handle what happens next.', 'jcp-core' ); ?></p>
				<ul class="pg-summary pg-summary--grid" id="pgTrialSummary"></ul>
				<p class="pg-continuity pg-continuity--trial" id="pgTrialContinuity" hidden></p>
				<?php $render_review( $trial_rev, 'trial' ); ?>
			</div>
		</section>
	</div>

	<footer class="pg-bottom-action" id="pgBottomAction" hidden>
		<p class="pg-bottom-action__micro" id="pgBottomMicro" hidden></p>
		<div class="pg-bottom-action__cta" id="pgBottomCtaHost"></div>
	</footer>

	<aside class="pg-story" aria-hidden="true">
		<div class="pg-story-mark" data-pg-story-mark>
			<svg class="pg-story-mark__svg" viewBox="0 0 120 160" width="100" height="132" role="img" aria-label="">
				<rect x="8" y="12" width="104" height="136" rx="12" fill="none" stroke="currentColor" stroke-width="3" opacity="0.35"/>
				<path d="M28 48h64M28 72h48M28 96h56" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity="0.45"/>
				<circle cx="88" cy="118" r="18" fill="none" stroke="currentColor" stroke-width="3" opacity="0.5"/>
				<path d="M80 118l6 6 12-14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
			</svg>
			<span class="pg-story-mark__caption"><?php esc_html_e( 'Proof Files continuity', 'jcp-core' ); ?></span>
		</div>
	</aside>
</div>
