<?php
/**
 * Proof Gap survey — native app shell content.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign  = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$campaign_dir = trailingslashit( get_template_directory() ) . 'assets/campaign/';
$integ     = trailingslashit( get_template_directory_uri() ) . 'assets/integrations/';
$map_url   = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';
$qr_url    = $campaign . 'ps-dummy-qr.png';
$campaign_asset = static function ( string $file ) use ( $campaign, $campaign_dir ): string {
	$path = $campaign_dir . $file;
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : (string) time();
	return $campaign . $file . '?v=' . rawurlencode( $ver );
};
$slots     = function_exists( 'jcp_proof_gap_review_slots' ) ? jcp_proof_gap_review_slots() : [];
$workflow_rev = is_array( $slots['workflow'] ?? null ) ? $slots['workflow'] : null;
$email_rev    = is_array( $slots['email'] ?? null ) ? $slots['email'] : null;
$reveal_rev   = is_array( $slots['reveal'] ?? null ) ? $slots['reveal'] : null;
$trial_rev    = is_array( $slots['trial'] ?? null ) ? $slots['trial'] : null;

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

$render_review = static function ( ?array $review, string $slot, string $mod = '', string $context = '' ) use ( $render_stars ): void {
	if ( ! $review || trim( (string) ( $review['quote'] ?? '' ) ) === '' ) {
		return;
	}
	$avatar = (string) ( $review['avatar'] ?? $review['avatar_url'] ?? '' );
	$name   = (string) ( $review['name'] ?? '' );
	$role   = (string) ( $review['role'] ?? '' );
	$quote  = (string) ( $review['quote'] ?? '' );
	$rating = isset( $review['rating'] ) ? (int) $review['rating'] : 0;
	$cls    = 'pg-review-slot pg-review-slot--live' . ( $mod !== '' ? ' ' . $mod : '' );
	echo '<aside class="' . esc_attr( $cls ) . '" data-pg-review-slot="' . esc_attr( $slot ) . '">';
	echo '<blockquote class="pg-review pg-review--compact">';
	if ( $rating > 0 ) {
		$render_stars( $rating );
	}
	if ( $context !== '' ) {
		echo '<p class="pg-review__context" data-pg-review-context>' . esc_html( $context ) . '</p>';
	}
	echo '<p class="pg-review__quote">“' . esc_html( $quote ) . '”</p>';
	echo '<footer class="pg-review__footer">';
	if ( $avatar !== '' ) {
		echo '<img class="pg-review__avatar" src="' . esc_url( $avatar ) . '" alt="" width="32" height="32" loading="lazy" decoding="async" />';
	}
	echo '<span class="pg-review__meta"><strong>' . esc_html( $name ) . '</strong>';
	if ( $role !== '' ) {
		echo '<em>' . esc_html( $role ) . '</em>';
	}
	echo '</span></footer></blockquote></aside>';
};

$render_authority = static function ( string $mod = '' ): void {
	$cls = 'pg-authority' . ( $mod !== '' ? ' ' . $mod : '' );
	echo '<div class="' . esc_attr( $cls ) . '">';
	echo '<p class="pg-authority__by">' . esc_html__( 'Built by the team behind LeadsForward', 'jcp-core' ) . '</p>';
	echo '<p class="pg-authority__stats">' . esc_html__( '10 years · 250K+ contractor leads · $150M+ client revenue booked', 'jcp-core' ) . '</p>';
	echo '</div>';
};

$render_authority_metrics = static function ( string $mod = '' ): void {
	$cls = 'pg-authority pg-authority--metrics' . ( $mod !== '' ? ' ' . $mod : '' );
	echo '<div class="' . esc_attr( $cls ) . '">';
	echo '<div class="pg-authority__metric"><span class="pg-authority__metric-val">10 years</span><span class="pg-authority__metric-label">' . esc_html__( 'in home services', 'jcp-core' ) . '</span></div>';
	echo '<div class="pg-authority__metric"><span class="pg-authority__metric-val">250K+</span><span class="pg-authority__metric-label">' . esc_html__( 'contractor leads', 'jcp-core' ) . '</span></div>';
	echo '<div class="pg-authority__metric"><span class="pg-authority__metric-val">$150M+</span><span class="pg-authority__metric-label">' . esc_html__( 'client revenue', 'jcp-core' ) . '</span></div>';
	echo '</div>';
};

$trust_avatars = [];
foreach ( [ $workflow_rev, $email_rev, $reveal_rev, $trial_rev ] as $rev ) {
	if ( is_array( $rev ) && ! empty( $rev['avatar'] ) ) {
		$trust_avatars[] = (string) ( $rev['avatar'] ?? $rev['avatar_url'] ?? '' );
	}
}
// Fallback face set if a slot review is missing — four distinct male faces.
if ( count( $trust_avatars ) < 4 ) {
	$trust_avatars = [
		$campaign_asset( 'jcp-campaign-face-owner-64.webp' ),
		$campaign_asset( 'jcp-campaign-face-operator-64.webp' ),
		$campaign_asset( 'jcp-campaign-face-crew-man-64.webp' ),
		$campaign_asset( 'jcp-campaign-face-manager-64.webp' ),
	];
} else {
	$trust_avatars = array_values( array_unique( $trust_avatars ) );
	if ( count( $trust_avatars ) < 4 ) {
		$trust_avatars[] = $campaign_asset( 'jcp-campaign-face-manager-64.webp' );
	}
	$trust_avatars = array_slice( $trust_avatars, 0, 4 );
}

$render_case = static function (): void {
	echo '<aside class="pg-case" aria-label="' . esc_attr__( 'Foundation repair case study', 'jcp-core' ) . '">';
	echo '<p class="pg-case__eyebrow">' . esc_html__( 'Verified local search result', 'jcp-core' ) . '</p>';
	echo '<p class="pg-case__lead">' . esc_html__( 'One foundation repair company saw:', 'jcp-core' ) . '</p>';
	echo '<p class="pg-case__metric"><strong class="pg-case__metric-lo">0%</strong><span class="pg-case__metric-arrow" aria-hidden="true">→</span><strong class="pg-case__metric-hi">84–100%</strong></p>';
	echo '<p class="pg-case__metric-label">' . esc_html__( 'Google Maps 3-Pack visibility', 'jcp-core' ) . '</p>';
	echo '<p class="pg-case__body">' . esc_html__( 'From nearly invisible to showing across tracked local searches in about 12 weeks — measured across four keyword/market grids.', 'jcp-core' ) . '</p>';
	echo '<p class="pg-case__attr">' . esc_html__( 'LeadsForward + JobCapturePro strategy', 'jcp-core' ) . '</p>';
	echo '<p class="pg-case__note">' . esc_html__( 'Past performance does not guarantee future rankings.', 'jcp-core' ) . '</p>';
	echo '</aside>';
};
?>

<div class="pg-layout">
	<div class="pg-stage" id="pgStage" aria-live="polite">

		<section class="pg-state pg-state--welcome" data-pg-state="welcome" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'The 60 second job visibility check', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--welcome"><?php esc_html_e( 'Your crew already takes the photos. Are they helping you win the next job?', 'jcp-core' ); ?></h1>
				<p class="pg-sub pg-sub--emphasis"><strong><?php esc_html_e( 'Too many completed jobs never make it past the camera roll or CRM.', 'jcp-core' ); ?></strong></p>
				<p class="pg-sub"><?php esc_html_e( 'Answer 4 quick questions to see how much of your work is going unseen and what JobCapturePro could be doing with it.', 'jcp-core' ); ?></p>

				<div class="pg-welcome-checklist" data-pg-welcome-visual data-creative="default" aria-hidden="true">
					<p class="pg-welcome-checklist__heading"><?php esc_html_e( 'One completed job can become:', 'jcp-core' ); ?></p>
					<ul class="pg-welcome-checklist__list">
						<li><span class="pg-welcome-checklist__check" aria-hidden="true">✓</span><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
						<li><span class="pg-welcome-checklist__check" aria-hidden="true">✓</span><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
						<li><span class="pg-welcome-checklist__check" aria-hidden="true">✓</span><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
						<li><span class="pg-welcome-checklist__check" aria-hidden="true">✓</span><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
						<li><span class="pg-welcome-checklist__check" aria-hidden="true">✓</span><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					</ul>
				</div>
			<div class="pg-trust pg-trust--compact">
				<div class="pg-trust__proof">
					<div class="pg-trust__proof-copy">
						<span class="pg-trust__stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'jcp-core' ); ?>">★★★★★</span>
						<span class="pg-trust__label"><?php esc_html_e( '5-star feedback from contractors and home-service operators', 'jcp-core' ); ?></span>
					</div>
					<span class="pg-trust__avatars" aria-hidden="true">
						<?php foreach ( $trust_avatars as $trust_src ) : ?>
							<img src="<?php echo esc_url( $trust_src ); ?>" alt="" width="36" height="36" loading="lazy" decoding="async" />
						<?php endforeach; ?>
					</span>
				</div>
				<div class="pg-trust__divider" aria-hidden="true"></div>
				<div class="pg-trust__authority-block">
					<p class="pg-trust__authority"><?php echo wp_kses_post( __( 'Built by the team behind <strong>LeadsForward</strong>', 'jcp-core' ) ); ?></p>
					<ul class="pg-trust__metrics">
						<li><strong>10 yrs</strong><span><?php esc_html_e( 'home services', 'jcp-core' ); ?></span></li>
						<li><strong>250K+</strong><span><?php esc_html_e( 'contractor leads', 'jcp-core' ); ?></span></li>
						<li><strong>$150M+</strong><span><?php esc_html_e( 'client revenue', 'jcp-core' ); ?></span></li>
					</ul>
				</div>
			</div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="trade" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( '1. Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-trade-title"><?php esc_html_e( 'What kind of work does your company do most?', 'jcp-core' ); ?></h1>
			<div class="pg-choices pg-choices--grid pg-choices--trade" role="group" aria-labelledby="pg-trade-title" data-pg-choices="trade"></div>
				<div class="pg-other-field" id="pgTradeOtherField" hidden>
					<label class="pg-other-field__label" for="pgTradeOtherInput"><?php esc_html_e( 'Your trade (optional)', 'jcp-core' ); ?></label>
					<input class="pg-other-field__input" id="pgTradeOtherInput" type="text" maxlength="80" placeholder="<?php esc_attr_e( 'e.g. Pool service, flooring, solar', 'jcp-core' ); ?>" autocomplete="off" />
					<div class="pg-other-field__actions" id="pgTradeOtherActions">
						<button type="button" class="btn btn-primary pg-btn pg-other-continue" data-pg-other-continue="trade"><?php esc_html_e( 'Continue', 'jcp-core' ); ?></button>
						<button type="button" class="pg-other-skip" data-pg-other-skip="trade"><?php esc_html_e( 'Skip', 'jcp-core' ); ?></button>
					</div>
				</div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="current_workflow" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( '2. Your work', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-workflow-title"><?php esc_html_e( 'Where do most of your finished-job photos live today?', 'jcp-core' ); ?></h1>
			<div class="pg-choices pg-choices--workflow" role="group" aria-labelledby="pg-workflow-title" data-pg-choices="current_workflow"
				data-logo-hcp="<?php echo esc_url( $integ . 'housecall-pro.svg' ); ?>"
				data-logo-cc="<?php echo esc_url( $integ . 'companycam.svg' ); ?>"></div>
				<div class="pg-other-field" id="pgWorkflowOtherField" hidden>
					<label class="pg-other-field__label" for="pgWorkflowOtherInput"><?php esc_html_e( 'Which one? (optional)', 'jcp-core' ); ?></label>
					<input class="pg-other-field__input" id="pgWorkflowOtherInput" type="text" maxlength="80" placeholder="<?php esc_attr_e( 'e.g. FieldEdge, Service Fusion', 'jcp-core' ); ?>" autocomplete="off" />
					<div class="pg-other-field__actions" id="pgWorkflowOtherActions">
						<button type="button" class="btn btn-primary pg-btn pg-other-continue" data-pg-other-continue="workflow"><?php esc_html_e( 'Continue', 'jcp-core' ); ?></button>
						<button type="button" class="pg-other-skip" data-pg-other-skip="workflow"><?php esc_html_e( 'Skip', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="pg-answer-summary" data-pg-summary="workflow" hidden>
					<span class="pg-answer-summary__label"><?php esc_html_e( 'Your current workflow', 'jcp-core' ); ?></span>
					<div class="pg-answer-summary__row">
						<span class="pg-answer-summary__value" data-pg-summary-value></span>
						<button type="button" class="pg-answer-summary__change" data-pg-change="workflow"><?php esc_html_e( 'Change', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="proof-gap-insight-card" data-pg-insight="workflow" hidden></div>
				<?php $render_review( $workflow_rev, 'workflow', 'pg-review-slot--insight' ); ?>
			</div>
		</section>

		<section class="pg-state" data-pg-state="jobs_per_week" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( '3. Your work', 'jcp-core' ); ?></p>
			<h1 class="pg-title pg-title--sm" id="pg-jobs-title"><?php esc_html_e( 'How many jobs does your company complete per week?', 'jcp-core' ); ?></h1>
				<p class="pg-hint"><?php esc_html_e( 'We’ll estimate how much valuable marketing material your company creates every year.', 'jcp-core' ); ?></p>
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
				<p class="pg-eyebrow"><?php esc_html_e( '4. What gets seen', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pg-proof-title"><?php esc_html_e( 'Of those finished jobs, about how many actually make it into your marketing?', 'jcp-core' ); ?></h1>
				<p class="pg-hint"><?php esc_html_e( 'Website content, Google profile, social media, or review requests.', 'jcp-core' ); ?></p>
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
				<p class="pg-eyebrow"><?php esc_html_e( 'What gets seen', 'jcp-core' ); ?></p>
				<div class="pg-result pg-result--visual" id="pgResultCard">
					<div class="pg-result__hero">
						<strong class="pg-result__hero-num" id="pgResultCompleted">—</strong>
						<span class="pg-result__hero-label" id="pgResultHeroLabel"><?php esc_html_e( 'finished jobs / year', 'jcp-core' ); ?></span>
						<p class="pg-result__hero-sub" id="pgResultHeroSub" hidden></p>
					</div>
					<h1 class="pg-title pg-title--sm pg-result__title" id="pgResultTitle"></h1>
					<div class="pg-gap-viz" id="pgGapViz" aria-hidden="true"></div>
					<ul class="pg-result__stats" id="pgResultStats">
						<li><span><?php esc_html_e( 'Completed jobs / year', 'jcp-core' ); ?></span><strong id="pgResultPublic">—</strong></li>
						<li id="pgResultInvisibleWrap" hidden><span><?php esc_html_e( 'Currently reused in marketing', 'jcp-core' ); ?></span><strong id="pgResultInvisible">—</strong></li>
					</ul>
					<p class="pg-result__note" id="pgResultNote"><?php esc_html_e( 'Based on the ranges you selected.', 'jcp-core' ); ?></p>
					<div class="pg-result__support" id="pgResultSupport"></div>
				</div>
			</div>
		</section>

		<section class="pg-state" data-pg-state="email_capture" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm"><?php esc_html_e( 'See exactly what one of your jobs could become.', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Enter your email to unlock your personalized example and save your results.', 'jcp-core' ); ?></p>
				<form class="pg-email" id="pgEmailForm" novalidate>
					<label class="pg-email__label" for="pgEmail"><?php esc_html_e( 'Email', 'jcp-core' ); ?> <span class="pg-req">*</span></label>
					<input class="pg-email__input" id="pgEmail" name="email" type="email" autocomplete="email" inputmode="email" required placeholder="you@company.com" />
					<p class="pg-email__error" id="pgEmailError" role="alert" hidden></p>
					<p class="pg-email__consent"><?php esc_html_e( 'We’ll send your results — no spam.', 'jcp-core' ); ?></p>
				</form>
				<div class="pg-email-proof">
					<?php $render_case(); ?>
					<?php $render_review( $email_rev, 'email' ); ?>
				</div>
			</div>
		</section>

		<section class="pg-state pg-state--app-sim" data-pg-state="app_sim" hidden>
			<div class="pg-state__inner pg-app-sim">
				<p class="pg-eyebrow"><?php esc_html_e( 'See it work', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pgAppSimTitle"><?php esc_html_e( 'Building your job into marketing…', 'jcp-core' ); ?></h1>
				<p class="pg-sub pg-sub--tight" id="pgAppSimSub"><?php esc_html_e( 'Watch JobCapturePro turn one finished job into live outputs.', 'jcp-core' ); ?></p>

				<div class="pg-app-sim__device" role="img" aria-label="<?php esc_attr_e( 'JobCapturePro app preview', 'jcp-core' ); ?>">
					<div class="pg-app-sim__bezel">
						<span class="pg-app-sim__island" aria-hidden="true"></span>
						<div class="pg-app-sim__screen">
							<div class="pg-app-sim__chrome" aria-hidden="true">
								<span class="pg-app-sim__time">9:41</span>
								<span class="pg-app-sim__live"><?php esc_html_e( 'Live', 'jcp-core' ); ?></span>
							</div>
							<div class="pg-app-sim__panel" id="pgAppSimStage">
								<div class="pg-app-sim__panel-head">
									<span class="pg-app-sim__mark" aria-hidden="true">JCP</span>
									<p class="pg-app-sim__status" id="pgAppSimStatus" aria-live="polite"><?php esc_html_e( 'Starting with your finished job…', 'jcp-core' ); ?></p>
								</div>
								<div class="pg-app-sim__meter" aria-hidden="true">
									<span class="pg-app-sim__meter-fill" id="pgAppSimMeter"></span>
								</div>
								<ul class="pg-app-sim__ticks" id="pgAppSimTicks" aria-label="<?php esc_attr_e( 'Progress checklist', 'jcp-core' ); ?>">
									<li data-sim-tick="capture"><?php esc_html_e( 'Photo captured', 'jcp-core' ); ?></li>
									<li data-sim-tick="build"><?php esc_html_e( 'Proof built', 'jcp-core' ); ?></li>
									<li data-sim-tick="website"><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
									<li data-sim-tick="google"><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
									<li data-sim-tick="social"><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
									<li data-sim-tick="reviews"><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
									<li data-sim-tick="directory"><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="pg-app-sim__skip" id="pgAppSimSkip" hidden><?php esc_html_e( 'Skip preview →', 'jcp-core' ); ?></button>
			</div>
		</section>

		<section class="pg-state pg-state--reveal" data-pg-state="product_reveal" hidden>
			<div class="pg-state__inner pg-state__inner--wide">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pgRevealTitle"><?php esc_html_e( 'Here’s what one finished job can become.', 'jcp-core' ); ?></h1>
				<p class="pg-sub pg-sub--tight" id="pgRevealSubline"><?php esc_html_e( 'Your crew finishes the job and takes the photos. JobCapturePro turns that into marketing.', 'jcp-core' ); ?></p>

				<p class="pg-reveal-same" id="pgRevealSame"><?php esc_html_e( 'Tap a channel below to preview it', 'jcp-core' ); ?></p>
				<div class="pg-dest-tabs-wrap">
					<div class="pg-dest-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Preview each marketing channel', 'jcp-core' ); ?>">
						<button type="button" class="pg-dest-tab is-active" role="tab" aria-selected="true" data-dest="website"><?php esc_html_e( 'Website', 'jcp-core' ); ?></button>
						<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="google"><?php esc_html_e( 'Google', 'jcp-core' ); ?></button>
						<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="social"><?php esc_html_e( 'Social', 'jcp-core' ); ?></button>
						<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="reviews"><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></button>
						<button type="button" class="pg-dest-tab" role="tab" aria-selected="false" data-dest="directory"><?php esc_html_e( 'Directory', 'jcp-core' ); ?></button>
					</div>
					<p class="pg-dest-tabs-hint" id="pgDestTabsHint" aria-hidden="true"><?php esc_html_e( 'Swipe or tap to switch previews', 'jcp-core' ); ?></p>
				</div>
				<p class="pg-dest-caption" id="pgDestCaption" hidden></p>
				<div class="pg-dest-panel" id="pgDestPanel" role="tabpanel"
					data-map="<?php echo esc_url( $map_url ); ?>"
					data-qr="<?php echo esc_url( $qr_url ); ?>"></div>

				<p class="pg-payoff-line"><strong><?php esc_html_e( 'Your crew does the work. JobCapturePro makes the work keep working.', 'jcp-core' ); ?></strong></p>
				<p class="pg-benefit-line"><?php esc_html_e( 'No rewriting jobs · No moving photos · No captions in the truck', 'jcp-core' ); ?></p>
				<?php $render_review( $reveal_rev, 'reveal', 'pg-review-slot--reveal', '' ); ?>
			</div>
		</section>

		<section class="pg-state pg-state--plan-build" data-pg-state="plan_build" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title pg-title--sm" id="pgPlanBuildTitle"><?php esc_html_e( 'Creating your personalized plan…', 'jcp-core' ); ?></h1>
				<p class="pg-sub pg-sub--tight" id="pgPlanBuildSub"><?php esc_html_e( 'Matching JobCapturePro to how your crew already works.', 'jcp-core' ); ?></p>

				<div class="pg-plan-build" id="pgPlanBuild" aria-live="polite">
					<ul class="pg-plan-build__chips" id="pgPlanBuildChips"></ul>

					<div class="pg-plan-build__meter" aria-hidden="true">
						<span class="pg-plan-build__meter-fill" id="pgPlanBuildMeter"></span>
					</div>

					<ul class="pg-plan-build__steps" id="pgPlanBuildSteps">
						<li class="pg-plan-build__step" data-build-step="0">
							<span class="pg-plan-build__check" aria-hidden="true"></span>
							<span class="pg-plan-build__step-text"><?php esc_html_e( 'Locking in your trade', 'jcp-core' ); ?></span>
						</li>
						<li class="pg-plan-build__step" data-build-step="1">
							<span class="pg-plan-build__check" aria-hidden="true"></span>
							<span class="pg-plan-build__step-text"><?php esc_html_e( 'Connecting your photo workflow', 'jcp-core' ); ?></span>
						</li>
						<li class="pg-plan-build__step" data-build-step="2">
							<span class="pg-plan-build__check" aria-hidden="true"></span>
							<span class="pg-plan-build__step-text"><?php esc_html_e( 'Sizing your job volume', 'jcp-core' ); ?></span>
						</li>
						<li class="pg-plan-build__step" data-build-step="3">
							<span class="pg-plan-build__check" aria-hidden="true"></span>
							<span class="pg-plan-build__step-text"><?php esc_html_e( 'Picking your easiest first win', 'jcp-core' ); ?></span>
						</li>
					</ul>

					<p class="pg-plan-build__status" id="pgPlanBuildStatus"><?php esc_html_e( 'Almost ready…', 'jcp-core' ); ?></p>
				</div>
			</div>
		</section>

		<section class="pg-state pg-state--trial" data-pg-state="trial_bridge" hidden>
			<div class="pg-state__inner">
				<p class="pg-eyebrow"><?php esc_html_e( 'Your plan', 'jcp-core' ); ?></p>
				<h1 class="pg-title"><?php esc_html_e( 'Put the next job to work.', 'jcp-core' ); ?></h1>
				<p class="pg-sub"><?php esc_html_e( 'Start free for 14 days. Connect your workflow, capture one real job, and see JobCapturePro turn it into marketing.', 'jcp-core' ); ?></p>

				<div class="pg-plan-card" id="pgPlanCard">
					<div class="pg-plan-setup">
						<p class="pg-plan-card__kicker"><?php esc_html_e( 'Your setup', 'jcp-core' ); ?></p>
						<ul class="pg-plan-chips" id="pgTrialSummary"></ul>
					</div>

					<div class="pg-plan-win">
						<p class="pg-plan-win__label"><?php esc_html_e( 'Easiest first win', 'jcp-core' ); ?></p>
						<p class="pg-plan-first-win" id="pgTrialFirstWin"></p>
					</div>
				</div>

				<?php $render_review( $trial_rev, 'trial', 'pg-review-slot--trial' ); ?>
			</div>
		</section>
	</div>

	<footer class="pg-bottom-action" id="pgBottomAction" hidden>
		<p class="pg-bottom-action__micro" id="pgBottomMicro" hidden></p>
		<div class="pg-bottom-action__cta" id="pgBottomCtaHost"></div>
	</footer>
</div>
