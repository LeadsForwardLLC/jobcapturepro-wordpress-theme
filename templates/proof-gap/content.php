<?php
/**
 * Proof Gap survey app body — state panels (shell).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pg-layout">
	<div class="pg-stage" id="pgStage" aria-live="polite">
		<!-- States rendered/shown by JS state machine; markup provides structure + slots -->

		<section class="pg-state" data-pg-state="trade" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-trade-title"><?php esc_html_e( 'What kind of work does your crew finish every week?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--grid" role="group" aria-labelledby="pg-trade-title" data-pg-choices="trade"></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="current_workflow" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-workflow-title"><?php esc_html_e( 'Where does most of your finished-job proof live right now?', 'jcp-core' ); ?></h1>
				<div class="pg-choices" role="group" aria-labelledby="pg-workflow-title" data-pg-choices="current_workflow"></div>
				<p class="pg-feedback" data-pg-feedback="workflow" hidden></p>
			</div>
		</section>

		<section class="pg-state" data-pg-state="jobs_per_week" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-jobs-title"><?php esc_html_e( 'About how many completed jobs does your company average per week?', 'jcp-core' ); ?></h1>
				<div class="pg-choices pg-choices--compact" role="group" aria-labelledby="pg-jobs-title" data-pg-choices="jobs_per_week"></div>
				<p class="pg-feedback" data-pg-feedback="jobs" hidden></p>
			</div>
		</section>

		<section class="pg-state" data-pg-state="public_proof_percentage" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h1 class="pg-title" id="pg-proof-title"><?php esc_html_e( 'What percentage of completed jobs regularly becomes proof somewhere customers can see?', 'jcp-core' ); ?></h1>
				<div class="pg-choices" role="group" aria-labelledby="pg-proof-title" data-pg-choices="public_proof_percentage"></div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="proof_gap_result" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'Here’s the gap your answers describe.', 'jcp-core' ); ?></h1>
				<div class="pg-result" id="pgResultCard">
					<ul class="pg-result__meta" id="pgResultMeta"></ul>
					<div class="pg-result__compare" aria-label="<?php esc_attr_e( 'Work completed versus public proof', 'jcp-core' ); ?>">
						<div class="pg-result__col">
							<span class="pg-result__col-label"><?php esc_html_e( 'Work completed', 'jcp-core' ); ?></span>
							<strong class="pg-result__col-value" id="pgResultCompleted">—</strong>
						</div>
						<div class="pg-result__vs" aria-hidden="true">vs</div>
						<div class="pg-result__col">
							<span class="pg-result__col-label"><?php esc_html_e( 'Work becoming public proof', 'jcp-core' ); ?></span>
							<strong class="pg-result__col-value" id="pgResultPublic">—</strong>
						</div>
					</div>
					<p class="pg-result__note"><?php esc_html_e( 'Ranges reflect your answers — not an exact count.', 'jcp-core' ); ?></p>
				</div>
				<button type="button" class="btn btn-primary pg-btn" id="pgResultCta"><?php esc_html_e( 'Show me what one job could become →', 'jcp-core' ); ?></button>
			</div>
		</section>

		<section class="pg-state" data-pg-state="email_capture" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'Where should we send your personalized view?', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Work email only. We’ll use it to show what one finished job can become.', 'jcp-core' ); ?></p>
				<form class="pg-email" id="pgEmailForm" novalidate>
					<label class="pg-email__label" for="pgEmail"><?php esc_html_e( 'Work email', 'jcp-core' ); ?> <span class="pg-req">*</span></label>
					<input class="pg-email__input" id="pgEmail" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@company.com" />
					<p class="pg-email__error" id="pgEmailError" role="alert" hidden></p>
					<button type="submit" class="btn btn-primary pg-btn" id="pgEmailSubmit"><?php esc_html_e( 'Continue →', 'jcp-core' ); ?></button>
					<p class="pg-email__consent"><?php esc_html_e( 'By continuing you agree to receive this demo view and relevant product updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</form>
				<aside class="pg-review-slot" data-pg-review-slot="email" aria-label="<?php esc_attr_e( 'Customer review placeholder', 'jcp-core' ); ?>">
					<p class="pg-review-slot__placeholder"><?php esc_html_e( 'Approved review will appear here.', 'jcp-core' ); ?></p>
				</aside>
			</div>
		</section>

		<section class="pg-state" data-pg-state="product_reveal" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'One finished job → structured proof', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Product visuals plug in here. Channel availability depends on plan and connections.', 'jcp-core' ); ?></p>
				<div class="pg-reveal" id="pgReveal" data-pg-reveal>
					<div class="pg-reveal__step" data-reveal="input">
						<span class="pg-reveal__label"><?php esc_html_e( 'Input / source', 'jcp-core' ); ?></span>
						<strong class="pg-reveal__value" id="pgRevealSource">—</strong>
					</div>
					<div class="pg-reveal__arrow" aria-hidden="true">→</div>
					<div class="pg-reveal__step" data-reveal="jcp">
						<span class="pg-reveal__label"><?php esc_html_e( 'JCP structured check-in', 'jcp-core' ); ?></span>
						<strong class="pg-reveal__value">JobCapturePro</strong>
					</div>
					<div class="pg-reveal__arrow" aria-hidden="true">→</div>
					<ul class="pg-reveal__outputs" aria-label="<?php esc_attr_e( 'Possible outputs', 'jcp-core' ); ?>">
						<li data-out="website"><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
						<li data-out="google"><?php esc_html_e( 'Google', 'jcp-core' ); ?> <em><?php esc_html_e( 'when connected', 'jcp-core' ); ?></em></li>
						<li data-out="review"><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
						<li data-out="social"><?php esc_html_e( 'Social', 'jcp-core' ); ?> <em><?php esc_html_e( 'when connected', 'jcp-core' ); ?></em></li>
						<li data-out="directory"><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					</ul>
				</div>
				<button type="button" class="btn btn-primary pg-btn" id="pgRevealContinue"><?php esc_html_e( 'See my trial plan →', 'jcp-core' ); ?></button>
			</div>
		</section>

		<section class="pg-state" data-pg-state="trial_bridge" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'Start with the jobs you’re already completing.', 'jcp-core' ); ?></h1>
				<ul class="pg-summary" id="pgTrialSummary"></ul>
				<a class="btn btn-primary pg-btn" id="pgTrialCta" href="#"><?php esc_html_e( 'Start My Free 14-Day Trial →', 'jcp-core' ); ?></a>
				<p class="pg-micro"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<aside class="pg-review-slot" data-pg-review-slot="trial" aria-label="<?php esc_attr_e( 'Customer review placeholder', 'jcp-core' ); ?>">
					<p class="pg-review-slot__placeholder"><?php esc_html_e( 'Approved review will appear here.', 'jcp-core' ); ?></p>
				</aside>
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
			<span class="pg-story-mark__caption"><?php esc_html_e( 'Field-proof continuity slot', 'jcp-core' ); ?></span>
		</div>
	</aside>
</div>
