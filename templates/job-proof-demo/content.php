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

$featured_quote = null;
$second_quote   = null;
if ( function_exists( 'jcp_sales_tool_default_reviews' ) ) {
	$by_id = [];
	foreach ( jcp_sales_tool_default_reviews() as $review ) {
		if ( ! is_array( $review ) ) {
			continue;
		}
		$id = (string) ( $review['id'] ?? '' );
		if ( $id !== '' ) {
			$by_id[ $id ] = $review;
		}
	}
	$featured_quote = $by_id['brian-hardy'] ?? null;
	$second_quote   = $by_id['trent-ellison'] ?? null;
}

$service_label = __( 'Water heater replacement', 'jcp-core' );
$city_label    = __( 'Austin, TX', 'jcp-core' );
$desc_label    = __( 'Installed a high-efficiency water heater, verified venting, and documented the completed work with geotagged job proof ready for your website and Google.', 'jcp-core' );

$destinations = [
	[
		'id'     => 'website',
		'label'  => __( 'Website', 'jcp-core' ),
		'status' => __( 'Automatically published to your website', 'jcp-core' ),
	],
	[
		'id'     => 'google',
		'label'  => __( 'Google Business Profile', 'jcp-core' ),
		'status' => __( 'Automatically posted to Google', 'jcp-core' ),
	],
	[
		'id'     => 'social',
		'label'  => __( 'Social', 'jcp-core' ),
		'status' => __( 'Automatically posted to social', 'jcp-core' ),
	],
	[
		'id'     => 'review',
		'label'  => __( 'Review opportunity', 'jcp-core' ),
		'status' => __( 'Review opportunity created', 'jcp-core' ),
	],
	[
		'id'     => 'directory',
		'label'  => __( 'JobCapturePro Directory', 'jcp-core' ),
		'status' => __( 'Published to your public JCP presence', 'jcp-core' ),
	],
	[
		'id'     => 'local',
		'label'  => __( 'Local / service-area proof', 'jcp-core' ),
		'status' => __( 'Service and location context attached', 'jcp-core' ),
	],
];
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
					<p class="jpd-stage__sub"><?php esc_html_e( 'Your crews already take the photos. JobCapturePro automatically turns finished jobs into website content, Google activity, social proof, reviews and public JCP directory proof — so the work you already paid to complete keeps helping your business get found.', 'jcp-core' ); ?></p>
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

		<!-- State B: Check-in + automatic distribution -->
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

			<div class="jpd-distribute" id="jpdDistribute" hidden>
				<p class="jpd-distribute__label" id="jpdDistributeLabel"><?php esc_html_e( 'Publishing across connected destinations…', 'jcp-core' ); ?></p>
				<ul class="jpd-distribute__nodes" aria-hidden="true">
					<?php foreach ( $destinations as $dest ) : ?>
						<li class="jpd-distribute__node" data-jpd-node="<?php echo esc_attr( $dest['id'] ); ?>">
							<span class="jpd-distribute__node-label"><?php echo esc_html( $dest['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<!-- State C: Outputs + convert -->
		<div class="jpd-state" data-jpd-moment="3" id="jpdMoment3" hidden>
			<div class="jpd-stage__intro jpd-stage__intro--result">
				<h2 class="jpd-stage__title jpd-stage__title--sm"><?php esc_html_e( 'One job just became fresh proof across your entire online presence.', 'jcp-core' ); ?></h2>
				<p class="jpd-stage__sub"><?php esc_html_e( 'JobCapturePro automatically publishes completed-job proof to your website, Google, social and the JCP directory — while creating a review opportunity before the customer moves on.', 'jcp-core' ); ?></p>
				<p class="jpd-outcome-line"><?php esc_html_e( 'More real work online. More local relevance. More reasons for customers to find you and trust you.', 'jcp-core' ); ?></p>
			</div>

			<div class="jpd-destinations" data-jpd-destinations>
				<details class="jpd-dest">
					<summary class="jpd-dest__summary">
						<span class="jpd-dest__channel"><?php esc_html_e( 'Website', 'jcp-core' ); ?></span>
						<span class="jpd-dest__status"><?php esc_html_e( 'Automatically published to your website', 'jcp-core' ); ?></span>
						<span class="jpd-dest__chevron" aria-hidden="true"></span>
					</summary>
					<div class="jpd-dest__body">
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
					</div>
				</details>

				<details class="jpd-dest">
					<summary class="jpd-dest__summary">
						<span class="jpd-dest__channel"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></span>
						<span class="jpd-dest__status"><?php esc_html_e( 'Automatically posted to Google', 'jcp-core' ); ?></span>
						<span class="jpd-dest__chevron" aria-hidden="true"></span>
					</summary>
					<div class="jpd-dest__body">
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
							<span class="jcp-sm-gbp__meta"><?php esc_html_e( 'Posted to Google · Location context attached', 'jcp-core' ); ?></span>
						</div>
					</div>
				</details>

				<details class="jpd-dest">
					<summary class="jpd-dest__summary">
						<span class="jpd-dest__channel"><?php esc_html_e( 'Social', 'jcp-core' ); ?></span>
						<span class="jpd-dest__status"><?php esc_html_e( 'Automatically posted to social', 'jcp-core' ); ?></span>
						<span class="jpd-dest__chevron" aria-hidden="true"></span>
					</summary>
					<div class="jpd-dest__body">
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
					</div>
				</details>

				<details class="jpd-dest">
					<summary class="jpd-dest__summary">
						<span class="jpd-dest__channel"><?php esc_html_e( 'Review opportunity', 'jcp-core' ); ?></span>
						<span class="jpd-dest__status"><?php esc_html_e( 'Review opportunity created', 'jcp-core' ); ?></span>
						<span class="jpd-dest__chevron" aria-hidden="true"></span>
					</summary>
					<div class="jpd-dest__body">
						<div class="jpd-review-ask jpd-preview">
							<div class="jpd-review-ask__qr" aria-hidden="true">
								<?php if ( $icon( 'qr-code' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="56" height="56" />
								<?php endif; ?>
								<span><?php esc_html_e( 'Scan to review', 'jcp-core' ); ?></span>
							</div>
							<div>
								<p class="jpd-review-ask__title"><?php esc_html_e( 'Ask while the job is fresh', 'jcp-core' ); ?></p>
								<p class="jpd-review-ask__copy"><?php esc_html_e( 'Show the customer a QR or send a link before the truck leaves.', 'jcp-core' ); ?></p>
							</div>
						</div>
					</div>
				</details>

				<details class="jpd-dest jpd-dest--directory">
					<summary class="jpd-dest__summary">
						<span class="jpd-dest__channel"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></span>
						<span class="jpd-dest__status"><?php esc_html_e( 'Published to your public JCP presence', 'jcp-core' ); ?></span>
						<span class="jpd-dest__chevron" aria-hidden="true"></span>
					</summary>
					<div class="jpd-dest__body">
						<p class="jpd-dest__note"><?php esc_html_e( 'Completed jobs become public proof inside JobCapturePro so customers can see the services and areas your business actually works in.', 'jcp-core' ); ?></p>
						<div class="jcp-sm-directory jpd-preview">
							<p class="jcp-sm-directory__label"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
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
								<div class="jcp-sm-directory__meta">
									<span><?php esc_html_e( 'Public job proof', 'jcp-core' ); ?></span>
									<span aria-hidden="true">·</span>
									<span><?php esc_html_e( 'Service-area context', 'jcp-core' ); ?></span>
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
					</div>
				</details>

				<details class="jpd-dest">
					<summary class="jpd-dest__summary">
						<span class="jpd-dest__channel"><?php esc_html_e( 'Local / service-area proof', 'jcp-core' ); ?></span>
						<span class="jpd-dest__status"><?php esc_html_e( 'Service and location context attached', 'jcp-core' ); ?></span>
						<span class="jpd-dest__chevron" aria-hidden="true"></span>
					</summary>
					<div class="jpd-dest__body">
						<div class="jpd-local-proof jpd-preview">
							<p class="jpd-local-proof__service"><?php echo esc_html( $service_label ); ?></p>
							<p class="jpd-local-proof__place"><?php echo esc_html( $city_label ); ?></p>
							<p class="jpd-local-proof__note"><?php esc_html_e( 'Where supported, completed jobs carry the service and area context that helps your public proof stay locally relevant.', 'jcp-core' ); ?></p>
						</div>
					</div>
				</details>
			</div>

			<p class="jpd-publish-note"><?php esc_html_e( 'Publishing availability depends on connected channels.', 'jcp-core' ); ?></p>
			<p class="jpd-dest-hint"><?php esc_html_e( 'Tap any destination above to see a realistic preview.', 'jcp-core' ); ?></p>

			<div class="jpd-convert" id="jpdConvert">
				<a
					class="btn btn-primary"
					href="<?php echo esc_url( $trial_href ); ?>"
					data-jpd-trial
					data-jpd-source="convert"
					id="jpdTrialCta"
				><?php esc_html_e( 'Put my next job to work →', 'jcp-core' ); ?></a>
				<p class="jpd-convert__note"><?php esc_html_e( 'Start your free 14-day trial · No credit card required', 'jcp-core' ); ?></p>
			</div>

			<aside class="jpd-compound" aria-labelledby="jpd-compound-title">
				<h3 id="jpd-compound-title" class="jpd-compound__heading"><?php esc_html_e( 'One job is useful. Every job changes the picture.', 'jcp-core' ); ?></h3>
				<p class="jpd-compound__copy"><?php esc_html_e( 'Each completed job can create multiple fresh public-proof opportunities across connected channels — and that body of real work grows as your company finishes more jobs.', 'jcp-core' ); ?></p>
				<div class="jpd-compound__visual" aria-hidden="true">
					<div class="jpd-compound__job">
						<span class="jpd-compound__job-dot"></span>
						<span><?php esc_html_e( 'One completed job', 'jcp-core' ); ?></span>
					</div>
					<ul class="jpd-compound__channels">
						<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Review', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Local proof', 'jcp-core' ); ?></li>
					</ul>
					<div class="jpd-compound__growth">
						<span class="jpd-compound__bar jpd-compound__bar--1"></span>
						<span class="jpd-compound__bar jpd-compound__bar--2"></span>
						<span class="jpd-compound__bar jpd-compound__bar--3"></span>
						<span class="jpd-compound__bar jpd-compound__bar--4"></span>
						<p class="jpd-compound__growth-label"><?php esc_html_e( 'More jobs → more fresh public proof over time', 'jcp-core' ); ?></p>
					</div>
				</div>
			</aside>

			<aside class="jpd-search-explain" aria-labelledby="jpd-search-title">
				<h3 id="jpd-search-title" class="jpd-search-explain__title"><?php esc_html_e( 'Turn the work you already do into local search signals', 'jcp-core' ); ?></h3>
				<ul class="jpd-search-explain__concepts">
					<li><strong><?php esc_html_e( 'Freshness', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'recent completed work keeps your online presence active.', 'jcp-core' ); ?></li>
					<li><strong><?php esc_html_e( 'Local relevance', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'real jobs carry service and location context.', 'jcp-core' ); ?></li>
					<li><strong><?php esc_html_e( 'Proof', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'prospects can see evidence of the services you actually perform.', 'jcp-core' ); ?></li>
				</ul>
				<p class="jpd-search-explain__copy"><?php esc_html_e( 'JobCapturePro helps create fresh, location-relevant, real-world content that can support stronger local visibility over time.', 'jcp-core' ); ?></p>
			</aside>

			<aside class="jpd-trust" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
				<p class="jpd-trust__label"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
				<ul class="jpd-trust__stats">
					<li><strong>10</strong> <?php esc_html_e( 'years', 'jcp-core' ); ?></li>
					<li><strong>250K+</strong> <?php esc_html_e( 'leads', 'jcp-core' ); ?></li>
					<li><strong>$150M+</strong> <?php esc_html_e( 'booked', 'jcp-core' ); ?></li>
				</ul>
			</aside>

			<?php if ( is_array( $featured_quote ) ) : ?>
			<figure class="jpd-quote jpd-quote--featured">
				<blockquote><?php esc_html_e( 'It takes my work site pictures and turns them into a marketing campaign.', 'jcp-core' ); ?></blockquote>
				<figcaption>
					<?php if ( ! empty( $featured_quote['avatar'] ) ) : ?>
						<img src="<?php echo esc_url( (string) $featured_quote['avatar'] ); ?>" alt="" width="36" height="36" loading="lazy" decoding="async" />
					<?php endif; ?>
					<span>
						<strong><?php echo esc_html( (string) ( $featured_quote['name'] ?? 'Brian Hardy' ) ); ?></strong>
						<em><?php esc_html_e( 'Contractor', 'jcp-core' ); ?></em>
					</span>
				</figcaption>
			</figure>
			<?php endif; ?>

			<?php if ( is_array( $second_quote ) ) : ?>
			<figure class="jpd-quote jpd-quote--subordinate">
				<blockquote><?php echo esc_html( (string) ( $second_quote['quote'] ?? '' ) ); ?></blockquote>
				<figcaption>
					<span>
						<strong><?php echo esc_html( (string) ( $second_quote['name'] ?? '' ) ); ?></strong>
						<?php if ( ! empty( $second_quote['role'] ) ) : ?>
							<em><?php echo esc_html( (string) $second_quote['role'] ); ?></em>
						<?php endif; ?>
					</span>
				</figcaption>
			</figure>
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
