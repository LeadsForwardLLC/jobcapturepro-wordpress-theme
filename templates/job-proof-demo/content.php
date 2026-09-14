<?php
/**
 * Job Proof Demo — hero is the proof stage (morph in place).
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

$reviews = [];
if ( function_exists( 'jcp_sales_tool_default_reviews' ) ) {
	$all = jcp_sales_tool_default_reviews();
	foreach ( $all as $review ) {
		if ( ! is_array( $review ) ) {
			continue;
		}
		$id = (string) ( $review['id'] ?? '' );
		if ( $id === 'peter-bonk' || $id === 'brian-hardy' ) {
			$reviews[] = $review;
		}
	}
	$reviews = array_slice( $reviews, 0, 2 );
}

$service_label = __( 'Water heater replacement', 'jcp-core' );
$city_label    = __( 'Austin, TX', 'jcp-core' );
$desc_label    = __( 'Installed a high-efficiency water heater, verified venting, and documented the completed work with geotagged job proof ready for your website and Google.', 'jcp-core' );
?>

<section
	id="proof"
	class="jpd-stage"
	aria-labelledby="jpd-hero-title"
	data-jpd-stage
	data-jpd-proof
>
	<div class="jpd-shell">
		<!-- State A: Finished job (above the fold) -->
		<div class="jpd-state is-active" data-jpd-moment="1" id="jpdMoment1">
			<div class="jpd-stage__intro">
				<p class="jpd-eyebrow"><?php esc_html_e( 'Your best jobs shouldn’t die in the camera roll', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jpd-stage__title"><?php esc_html_e( 'One finished job should keep working after the crew leaves.', 'jcp-core' ); ?></h1>
				<p class="jpd-stage__sub"><?php esc_html_e( 'Your crew already creates the proof. Watch JobCapturePro turn one completed job into marketing assets customers can actually find.', 'jcp-core' ); ?></p>
			</div>

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
					<h2 class="jpd-job-card__service"><?php echo esc_html( $service_label ); ?></h2>
					<p class="jpd-job-card__meta"><?php echo esc_html( $city_label ); ?> · <?php esc_html_e( 'Home services', 'jcp-core' ); ?></p>
					<p class="jpd-job-card__note"><?php esc_html_e( 'Normally, this is where the marketing stops.', 'jcp-core' ); ?></p>
				</div>
			</article>

			<div class="jpd-stage__actions">
				<button type="button" class="btn btn-primary" data-jpd-start data-jpd-next="2">
					<?php esc_html_e( 'Put this job to work →', 'jcp-core' ); ?>
				</button>
				<p class="jpd-stage__micro"><?php esc_html_e( 'Free · Takes about 60 seconds · No signup required', 'jcp-core' ); ?></p>
				<p class="jpd-stage__skip">
					<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Already get it? Start free trial →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>

		<!-- State B: Transformation (auto) -->
		<div class="jpd-state" data-jpd-moment="2" id="jpdMoment2" hidden>
			<div class="jpd-stage__intro">
				<h2 class="jpd-stage__title jpd-stage__title--sm"><?php esc_html_e( 'JobCapturePro turns the work into proof.', 'jcp-core' ); ?></h2>
			</div>
			<div class="jpd-checkin" aria-live="polite">
				<div class="jpd-checkin__status" id="jpdCheckinStatus"><?php esc_html_e( 'Creating job proof…', 'jcp-core' ); ?></div>
				<div class="jpd-checkin__card" id="jpdCheckinCard">
					<img
						class="jpd-checkin__photo"
						src="<?php echo esc_url( $photo_url ); ?>"
						alt=""
						width="120"
						height="90"
						loading="lazy"
						decoding="async"
					/>
					<div class="jpd-checkin__copy">
						<p class="jpd-checkin__service"><?php echo esc_html( $service_label ); ?></p>
						<p class="jpd-checkin__loc"><?php echo esc_html( $city_label ); ?></p>
						<p class="jpd-checkin__desc"><?php echo esc_html( $desc_label ); ?></p>
						<span class="jpd-checkin__badge"><?php esc_html_e( 'Check-in ready', 'jcp-core' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- State C: Outputs + convert (same state) -->
		<div class="jpd-state" data-jpd-moment="3" id="jpdMoment3" hidden>
			<div class="jpd-stage__intro">
				<h2 class="jpd-stage__title jpd-stage__title--sm"><?php esc_html_e( 'One job. Now working in more places.', 'jcp-core' ); ?></h2>
				<p class="jpd-stage__sub"><?php esc_html_e( 'Instead of disappearing into a camera roll, the finished job becomes reusable proof across your marketing.', 'jcp-core' ); ?></p>
			</div>

			<div class="jpd-outputs" role="list">
				<article class="jpd-output" role="listitem">
					<div class="jpd-output__meta">
						<p class="jpd-output__channel"><?php esc_html_e( 'Website', 'jcp-core' ); ?></p>
						<p class="jpd-output__status"><?php esc_html_e( 'Ready for your website', 'jcp-core' ); ?></p>
					</div>
					<div class="jcp-sm-browser jpd-preview">
						<div class="jcp-sm-browser__bar" aria-hidden="true">
							<span></span><span></span><span></span>
							<div class="jcp-sm-browser__url">yourbusiness.com/recent-work</div>
						</div>
						<div class="jcp-sm-browser__body">
							<p class="jcp-sm-browser__heading"><?php esc_html_e( 'Recent work', 'jcp-core' ); ?></p>
							<div class="jcp-sm-job-card">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="120" height="90" loading="lazy" decoding="async" />
								<div>
									<strong><?php echo esc_html( $service_label ); ?></strong>
									<span><?php echo esc_html( $city_label ); ?></span>
									<p><?php esc_html_e( 'Completed install with geotagged job proof from the site.', 'jcp-core' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</article>

				<article class="jpd-output" role="listitem">
					<div class="jpd-output__meta">
						<p class="jpd-output__channel"><?php esc_html_e( 'Google', 'jcp-core' ); ?></p>
						<p class="jpd-output__status"><?php esc_html_e( 'Prepared for Google', 'jcp-core' ); ?></p>
					</div>
					<div class="jcp-sm-gbp jpd-preview">
						<div class="jcp-sm-gbp__brand">
							<?php if ( $icon( 'map-pin' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="16" height="16" />
							<?php endif; ?>
							<div>
								<strong><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Your business · Update', 'jcp-core' ); ?></span>
							</div>
						</div>
						<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="180" loading="lazy" decoding="async" />
						<div class="jcp-sm-gbp__copy">
							<strong><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
							<p><?php esc_html_e( 'Fresh job proof from today’s completed work, ready for homeowners nearby.', 'jcp-core' ); ?></p>
						</div>
						<span class="jcp-sm-gbp__meta"><?php esc_html_e( 'Draft ready · Location context attached', 'jcp-core' ); ?></span>
					</div>
				</article>

				<article class="jpd-output" role="listitem">
					<div class="jpd-output__meta">
						<p class="jpd-output__channel"><?php esc_html_e( 'Social', 'jcp-core' ); ?></p>
						<p class="jpd-output__status"><?php esc_html_e( 'Social post created', 'jcp-core' ); ?></p>
					</div>
					<div class="jcp-sm-social jpd-preview">
						<div class="jcp-sm-social__head">
							<img class="jcp-sm-social__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="34" height="34" loading="lazy" decoding="async" />
							<div>
								<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Just now · Austin, TX', 'jcp-core' ); ?></span>
							</div>
						</div>
						<p class="jcp-sm-social__copy"><?php esc_html_e( 'Another job wrapped. Water heater replacement done right — proof from the field.', 'jcp-core' ); ?></p>
						<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="200" loading="lazy" decoding="async" />
						<div class="jcp-sm-social__reactions" aria-hidden="true">
							<span><?php esc_html_e( 'Like', 'jcp-core' ); ?></span>
							<span><?php esc_html_e( 'Comment', 'jcp-core' ); ?></span>
							<span><?php esc_html_e( 'Share', 'jcp-core' ); ?></span>
						</div>
					</div>
				</article>

				<article class="jpd-output" role="listitem">
					<div class="jpd-output__meta">
						<p class="jpd-output__channel"><?php esc_html_e( 'Review', 'jcp-core' ); ?></p>
						<p class="jpd-output__status"><?php esc_html_e( 'Review opportunity ready', 'jcp-core' ); ?></p>
					</div>
					<div class="jpd-review-ask jpd-preview">
						<div class="jpd-review-ask__qr" aria-hidden="true">
							<?php if ( $icon( 'qr-code' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="56" height="56" />
							<?php endif; ?>
							<span><?php esc_html_e( 'Scan to review', 'jcp-core' ); ?></span>
						</div>
						<div class="jpd-review-ask__copy">
							<p class="jpd-review-ask__title"><?php esc_html_e( 'Ask while the job is fresh', 'jcp-core' ); ?></p>
							<p class="jpd-review-ask__body"><?php esc_html_e( 'Show the customer a QR or send a link before the truck leaves.', 'jcp-core' ); ?></p>
						</div>
					</div>
				</article>

				<article class="jpd-output jpd-output--wide" role="listitem">
					<div class="jpd-output__meta">
						<p class="jpd-output__channel"><?php esc_html_e( 'Local proof', 'jcp-core' ); ?></p>
						<p class="jpd-output__status"><?php esc_html_e( 'Job proof added', 'jcp-core' ); ?></p>
					</div>
					<div class="jcp-sm-directory jpd-preview">
						<p class="jcp-sm-directory__label"><?php esc_html_e( 'JobCapturePro public proof', 'jcp-core' ); ?></p>
						<div class="jcp-sm-directory__card" role="article">
							<div class="jcp-sm-directory__head">
								<img class="jcp-sm-directory__avatar" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="40" height="40" loading="lazy" decoding="async" />
								<div>
									<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
									<span>
										<?php if ( $icon( 'map-pin' ) ) : ?>
											<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="12" height="12" />
										<?php endif; ?>
										<?php echo esc_html( $city_label ); ?>
									</span>
								</div>
							</div>
							<div class="jcp-sm-directory__proof">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="80" height="60" loading="lazy" decoding="async" />
								<div>
									<strong><?php esc_html_e( 'Latest: Water heater replacement', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Added from today’s completed job', 'jcp-core' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</article>
			</div>

			<div class="jpd-bridge">
				<h3 class="jpd-bridge__title"><?php esc_html_e( 'Now imagine this happening after every finished job.', 'jcp-core' ); ?></h3>
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
				<h3 class="jpd-convert__title"><?php esc_html_e( 'Ready to do this with your own jobs?', 'jcp-core' ); ?></h3>
				<a
					class="btn btn-primary"
					href="<?php echo esc_url( $trial_href ); ?>"
					data-jpd-trial
					data-jpd-source="convert"
					id="jpdTrialCta"
				><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
				<p class="jpd-convert__note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
			</div>

			<aside class="jpd-credibility" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
				<p class="jpd-credibility__eyebrow"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
				<p class="jpd-credibility__copy"><?php esc_html_e( 'A decade helping contractors grow. Then we built the missing piece: turning finished-job proof into marketing customers can find.', 'jcp-core' ); ?></p>
				<ul class="jpd-credibility__stats">
					<li><strong>10</strong> <span><?php esc_html_e( 'years helping contractors grow', 'jcp-core' ); ?></span></li>
					<li><strong>250K+</strong> <span><?php esc_html_e( 'leads generated for contractor clients', 'jcp-core' ); ?></span></li>
					<li><strong>$150M+</strong> <span><?php esc_html_e( 'revenue booked from those leads', 'jcp-core' ); ?></span></li>
				</ul>
			</aside>

			<?php if ( $reviews !== [] ) : ?>
			<div class="jpd-quotes" aria-label="<?php esc_attr_e( 'Customer quotes', 'jcp-core' ); ?>">
				<?php foreach ( $reviews as $review ) : ?>
					<figure class="jpd-quote">
						<blockquote><?php echo esc_html( (string) ( $review['quote'] ?? '' ) ); ?></blockquote>
						<figcaption>
							<?php if ( ! empty( $review['avatar'] ) ) : ?>
								<img src="<?php echo esc_url( (string) $review['avatar'] ); ?>" alt="" width="36" height="36" loading="lazy" decoding="async" />
							<?php endif; ?>
							<span>
								<strong><?php echo esc_html( (string) ( $review['name'] ?? '' ) ); ?></strong>
								<?php if ( ! empty( $review['role'] ) ) : ?>
									<em><?php echo esc_html( (string) $review['role'] ); ?></em>
								<?php endif; ?>
							</span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<p class="jpd-convert__secondary">
				<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert', 'jcp-core' ); ?></a>
			</p>
			<?php if ( $show_case ) : ?>
			<p class="jpd-convert__tertiary">
				<a href="<?php echo esc_url( $case_href ); ?>"><?php esc_html_e( 'Or apply for the 90-day case study', 'jcp-core' ); ?></a>
			</p>
			<?php endif; ?>
		</div>
	</div>
</section>
