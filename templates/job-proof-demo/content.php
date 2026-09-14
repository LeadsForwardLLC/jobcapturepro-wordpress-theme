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

// Prefer contractor/customer proof over agency intermediaries.
$reviews = [];
if ( function_exists( 'jcp_sales_tool_default_reviews' ) ) {
	$prefer = [ 'brian-hardy', 'trent-ellison', 'heriberto-eddie-roman' ];
	$by_id  = [];
	foreach ( jcp_sales_tool_default_reviews() as $review ) {
		if ( ! is_array( $review ) ) {
			continue;
		}
		$id = (string) ( $review['id'] ?? '' );
		if ( $id !== '' ) {
			$by_id[ $id ] = $review;
		}
	}
	foreach ( $prefer as $id ) {
		if ( isset( $by_id[ $id ] ) ) {
			$reviews[] = $by_id[ $id ];
		}
		if ( count( $reviews ) >= 2 ) {
			break;
		}
	}
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
		<!-- State A: Finished job (desktop: copy left / card right) -->
		<div class="jpd-state is-active" data-jpd-moment="1" id="jpdMoment1">
			<div class="jpd-hero-grid">
				<div class="jpd-hero-grid__copy">
					<p class="jpd-eyebrow"><?php esc_html_e( 'Your best jobs shouldn’t die in the camera roll', 'jcp-core' ); ?></p>
					<h1 id="jpd-hero-title" class="jpd-stage__title"><?php esc_html_e( 'One finished job should keep working after the crew leaves.', 'jcp-core' ); ?></h1>
					<p class="jpd-stage__sub"><?php esc_html_e( 'Your crew already creates the proof. Watch JobCapturePro turn one completed job into marketing assets customers can actually find.', 'jcp-core' ); ?></p>
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

		<!-- State C: Outputs + convert (full-width) -->
		<div class="jpd-state" data-jpd-moment="3" id="jpdMoment3" hidden>
			<div class="jpd-stage__intro jpd-stage__intro--result">
				<h2 class="jpd-stage__title jpd-stage__title--sm"><?php esc_html_e( 'One job. Now working in more places.', 'jcp-core' ); ?></h2>
			</div>

			<div class="jpd-outputs" role="list">
				<article class="jpd-output jpd-output--primary" role="listitem">
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

				<article class="jpd-output jpd-output--primary" role="listitem">
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

				<article class="jpd-output jpd-output--primary" role="listitem">
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
			</div>

			<div class="jpd-outputs-secondary" role="list">
				<article class="jpd-output-compact" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Review', 'jcp-core' ); ?></p>
					<div class="jpd-output-compact__row">
						<?php if ( $icon( 'qr-code' ) ) : ?>
							<img class="jpd-output-compact__icon" src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="28" height="28" />
						<?php endif; ?>
						<div>
							<p class="jpd-output-compact__status"><?php esc_html_e( 'Review opportunity ready', 'jcp-core' ); ?></p>
							<p class="jpd-output-compact__hint"><?php esc_html_e( 'Ask on-site with a QR or link while the job is fresh.', 'jcp-core' ); ?></p>
						</div>
					</div>
				</article>
				<article class="jpd-output-compact" role="listitem">
					<p class="jpd-output__channel"><?php esc_html_e( 'Local proof', 'jcp-core' ); ?></p>
					<div class="jpd-output-compact__row">
						<img class="jpd-output-compact__thumb" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="40" height="40" loading="lazy" decoding="async" />
						<div>
							<p class="jpd-output-compact__status"><?php esc_html_e( 'Job proof added', 'jcp-core' ); ?></p>
							<p class="jpd-output-compact__hint"><?php esc_html_e( 'Public JCP check-in for your service area.', 'jcp-core' ); ?></p>
						</div>
					</div>
				</article>
			</div>

			<div class="jpd-convert" id="jpdConvert">
				<p class="jpd-bridge-line"><?php esc_html_e( 'One finished job. Five pieces of proof working after the crew leaves.', 'jcp-core' ); ?></p>
				<a
					class="btn btn-primary"
					href="<?php echo esc_url( $trial_href ); ?>"
					data-jpd-trial
					data-jpd-source="convert"
					id="jpdTrialCta"
				><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
				<p class="jpd-convert__note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
			</div>

			<aside class="jpd-trust" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
				<p class="jpd-trust__label"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
				<ul class="jpd-trust__stats">
					<li><strong>10</strong> <?php esc_html_e( 'years', 'jcp-core' ); ?></li>
					<li><strong>250K+</strong> <?php esc_html_e( 'leads', 'jcp-core' ); ?></li>
					<li><strong>$150M+</strong> <?php esc_html_e( 'booked', 'jcp-core' ); ?></li>
				</ul>
			</aside>

			<?php if ( $reviews !== [] ) : ?>
			<div class="jpd-quotes" aria-label="<?php esc_attr_e( 'Customer quotes', 'jcp-core' ); ?>">
				<?php foreach ( $reviews as $review ) : ?>
					<figure class="jpd-quote">
						<blockquote><?php echo esc_html( (string) ( $review['quote'] ?? '' ) ); ?></blockquote>
						<figcaption>
							<?php if ( ! empty( $review['avatar'] ) ) : ?>
								<img src="<?php echo esc_url( (string) $review['avatar'] ); ?>" alt="" width="32" height="32" loading="lazy" decoding="async" />
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

			<nav class="jpd-secondary-links" aria-label="<?php esc_attr_e( 'Other options', 'jcp-core' ); ?>">
				<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				<?php if ( $show_case ) : ?>
					<a class="jpd-secondary-links__tertiary" href="<?php echo esc_url( $case_href ); ?>"><?php esc_html_e( 'Apply for the 90-day case study →', 'jcp-core' ); ?></a>
				<?php endif; ?>
			</nav>
		</div>
	</div>
</section>
