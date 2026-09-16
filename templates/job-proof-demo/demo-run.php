<?php
/**
 * Personalized demo run — /job-proof-demo/demo/
 * CINEMA: LOAD → APP → PIPELINE → OUTPUTS → GROWTH → TRIAL
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
$companion_b     = $campaign . 'jcp-campaign-job-proof-640.webp';
$reviews         = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
$compact_reviews = [];
foreach ( $reviews as $r ) {
	$id = (string) ( $r['id'] ?? '' );
	if ( in_array( $id, [ 'brian-hardy', 'trent-ellison' ], true ) ) {
		$compact_reviews[] = $r;
	}
}
if ( count( $compact_reviews ) < 2 ) {
	$compact_reviews = array_slice( $reviews, 0, 2 );
}

$pipeline_channels = [
	'website'   => [ 'label' => __( 'Website', 'jcp-core' ), 'icon' => 'globe' ],
	'google'    => [ 'label' => __( 'Google', 'jcp-core' ), 'icon' => 'map-pin' ],
	'social'    => [ 'label' => __( 'Social', 'jcp-core' ), 'icon' => 'share-2' ],
	'directory' => [ 'label' => __( 'Directory', 'jcp-core' ), 'icon' => 'building-2' ],
	'review'    => [ 'label' => __( 'Reviews', 'jcp-core' ), 'icon' => 'star' ],
];
?>

<div class="jpd-cinema-loader" id="jpdCinemaLoader" data-jpd-loader aria-live="polite" aria-busy="true">
	<div class="jpd-cinema-loader__backdrop" aria-hidden="true"></div>
	<div class="jpd-cinema-loader__panel">
		<span class="jpd-cinema-loader__mark" aria-hidden="true">JCP</span>
		<p class="jpd-cinema-loader__title"><?php esc_html_e( 'Building your personalized demo', 'jcp-core' ); ?></p>
		<p class="jpd-cinema-loader__status" id="jpdLoaderStatus"><?php esc_html_e( 'Loading your trade and job photo…', 'jcp-core' ); ?></p>
		<div class="jpd-cinema-loader__meter" aria-hidden="true"><span class="jpd-cinema-loader__meter-bar" id="jpdLoaderMeter"></span></div>
		<ul class="jpd-cinema-loader__ticks">
			<li data-loader-tick="persona"><?php esc_html_e( 'Personalize', 'jcp-core' ); ?></li>
			<li data-loader-tick="app"><?php esc_html_e( 'Field app', 'jcp-core' ); ?></li>
			<li data-loader-tick="engine"><?php esc_html_e( 'JCP engine', 'jcp-core' ); ?></li>
			<li data-loader-tick="channels"><?php esc_html_e( 'Channels', 'jcp-core' ); ?></li>
		</ul>
	</div>
</div>

<section class="jcp-section jpd-run-hero jpd-cinema" aria-labelledby="jpd-run-title" data-jpd-run-theater data-jpd-cinema>
	<div class="jcp-container">
		<header class="jpd-run-hero__header">
			<p class="jpd-eyebrow"><?php esc_html_e( 'Your personalized demo', 'jcp-core' ); ?></p>
			<h1 id="jpd-run-title" class="jcp-section-headline" data-jpd-full-heading><?php esc_html_e( 'Here’s what one job can become.', 'jcp-core' ); ?></h1>
			<p class="jpd-run-hero__sub"><?php esc_html_e( 'Watch one finished job photo turn into proof, distribution, and more reasons for homeowners to call.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-cinema__stage" data-jpd-run-stage>
			<div class="jpd-cinema__phone-col">
				<?php
				if ( function_exists( 'jcp_component_demo_app_phone' ) ) {
					jcp_component_demo_app_phone( '', $photo_url, true );
				}
				?>
			</div>

			<div class="jpd-cinema-pipeline" data-jpd-pipeline aria-hidden="true">
				<div class="jpd-cinema-pipeline__viz">
					<span class="jpd-cinema-pipeline__pulse" aria-hidden="true"></span>
					<div class="jpd-cinema-pipeline__core">
						<span class="jpd-cinema-pipeline__core-mark">JCP</span>
						<span class="jpd-cinema-pipeline__core-label"><?php esc_html_e( 'Building proof', 'jcp-core' ); ?></span>
					</div>
					<ul class="jpd-cinema-pipeline__nodes">
						<?php foreach ( $pipeline_channels as $key => $ch ) : ?>
							<li data-pipeline-channel="<?php echo esc_attr( $key ); ?>">
								<?php if ( $icon( $ch['icon'] ) ) : ?>
									<img src="<?php echo esc_url( $icon( $ch['icon'] ) ); ?>" alt="" width="16" height="16" />
								<?php endif; ?>
								<span><?php echo esc_html( $ch['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<p class="jpd-cinema-pipeline__caption" id="jpdPipelineCaption"><?php esc_html_e( 'Waiting for the job photo…', 'jcp-core' ); ?></p>
			</div>
		</div>

		<div class="jpd-run__progress" id="jpdFullProgress" aria-live="polite">
			<div class="jpd-run__meter" aria-hidden="true"><span class="jpd-run__meter-bar" id="jpdRunMeter"></span></div>
			<p class="jpd-full__status" id="jpdFullStatus"><?php esc_html_e( 'Starting in the field…', 'jcp-core' ); ?></p>
			<ul class="jpd-run__steps">
				<li data-run-step="capture"><?php esc_html_e( 'Photo captured', 'jcp-core' ); ?></li>
				<li data-run-step="build"><?php esc_html_e( 'Proof built', 'jcp-core' ); ?></li>
				<li data-run-step="publish"><?php esc_html_e( 'Publishing live', 'jcp-core' ); ?></li>
				<li data-run-step="grow"><?php esc_html_e( 'Visibility grows', 'jcp-core' ); ?></li>
			</ul>
		</div>
	</div>
</section>

<section class="jcp-section jpd-run-results" id="jpdFullResults" data-jpd-results hidden aria-labelledby="jpd-results-title">
	<div class="jcp-container">
		<header class="jpd-run-results__header">
			<p class="jpd-eyebrow"><?php esc_html_e( 'One job. Five places.', 'jcp-core' ); ?></p>
			<h2 id="jpd-results-title" class="jcp-section-headline"><?php esc_html_e( 'Same finished job. Now working for you.', 'jcp-core' ); ?></h2>
			<p class="jpd-run-results__sub"><?php esc_html_e( 'Website. Google. Social. Reviews. Directory. No extra work for the crew.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-cinema-growth" id="jpdCinemaGrowth" data-jpd-growth hidden aria-labelledby="jpd-growth-title">
			<div class="jpd-cinema-growth__intro">
				<p class="jpd-eyebrow"><?php esc_html_e( 'What stacks up over time', 'jcp-core' ); ?></p>
				<h3 id="jpd-growth-title" class="jpd-cinema-growth__title"><?php esc_html_e( 'More proof. More visibility. More inbound calls.', 'jcp-core' ); ?></h3>
				<p class="jpd-cinema-growth__sub"><?php esc_html_e( 'Illustration of how documented jobs can compound. Results vary by market and consistency.', 'jcp-core' ); ?></p>
			</div>
			<div class="jpd-cinema-growth__grid">
				<div class="jpd-growth-card jpd-growth-card--vis">
					<p class="jpd-growth-card__label"><?php esc_html_e( 'Local visibility', 'jcp-core' ); ?></p>
					<div class="jpd-growth-vis__bar" aria-hidden="true"><span class="jpd-growth-vis__fill" data-jpd-vis-meter style="width:18%"></span></div>
					<ul class="jpd-growth-vis__ticks" aria-hidden="true">
						<li><?php esc_html_e( 'Week 1', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Week 4', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Week 12', 'jcp-core' ); ?></li>
					</ul>
				</div>
				<div class="jpd-growth-card jpd-growth-card--search">
					<p class="jpd-growth-card__label"><?php esc_html_e( 'Search presence', 'jcp-core' ); ?></p>
					<div class="jpd-growth-search" data-jpd-search-rank>
						<div class="jpd-growth-search__row jpd-growth-search__row--ghost">
							<span class="jpd-growth-search__pos">4</span>
							<span class="jpd-growth-search__name"><?php esc_html_e( 'Your business', 'jcp-core' ); ?></span>
							<span class="jpd-growth-search__hint"><?php esc_html_e( 'Fresh job posts', 'jcp-core' ); ?></span>
						</div>
						<div class="jpd-growth-search__row jpd-growth-search__row--hero">
							<span class="jpd-growth-search__pos" data-jpd-rank-pos>2</span>
							<span class="jpd-growth-search__name"><?php esc_html_e( 'Your business', 'jcp-core' ); ?></span>
							<span class="jpd-growth-search__hint"><?php esc_html_e( 'Map + recent jobs', 'jcp-core' ); ?></span>
						</div>
					</div>
				</div>
				<div class="jpd-growth-card jpd-growth-card--leads">
					<p class="jpd-growth-card__label"><?php esc_html_e( 'Inbound leads', 'jcp-core' ); ?></p>
					<ul class="jpd-growth-leads" data-jpd-leads aria-live="polite">
						<li data-lead="1" hidden>
							<?php if ( $icon( 'phone-incoming' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'phone-incoming' ) ); ?>" alt="" width="18" height="18" />
							<?php endif; ?>
							<div>
								<strong><?php esc_html_e( 'Call · Google Maps', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( '“Saw your recent water heater jobs nearby.”', 'jcp-core' ); ?></span>
							</div>
						</li>
						<li data-lead="2" hidden>
							<?php if ( $icon( 'message-square' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'message-square' ) ); ?>" alt="" width="18" height="18" />
							<?php endif; ?>
							<div>
								<strong><?php esc_html_e( 'Form · Website', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( '“Need a quote for a replacement.”', 'jcp-core' ); ?></span>
							</div>
						</li>
						<li data-lead="3" hidden>
							<?php if ( $icon( 'star' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'star' ) ); ?>" alt="" width="18" height="18" />
							<?php endif; ?>
							<div>
								<strong><?php esc_html_e( 'Review submitted', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( '“On time and left the area clean.”', 'jcp-core' ); ?></span>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<ul class="jpd-run-payoffs" data-jpd-payoffs aria-label="<?php esc_attr_e( 'What this leads to', 'jcp-core' ); ?>">
			<li data-payoff="1">
				<strong><?php esc_html_e( 'Higher visibility', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'Proof where customers already look', 'jcp-core' ); ?></span>
			</li>
			<li data-payoff="2">
				<strong><?php esc_html_e( 'More reviews', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'Ask on site while trust is highest', 'jcp-core' ); ?></span>
			</li>
			<li data-payoff="3">
				<strong><?php esc_html_e( 'More leads over time', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'Every job keeps selling after the truck leaves', 'jcp-core' ); ?></span>
			</li>
		</ul>

		<div class="jpd-outputs" data-jpd-outputs>
			<article class="jpd-output jpd-output--website" data-jpd-output="website">
				<p class="jpd-output__channel"><?php esc_html_e( '01 Website', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Live on your site. Map plus recent jobs.', 'jcp-core' ); ?></h3>
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
								<strong><?php esc_html_e( 'Service call', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Cedar Park, TX', 'jcp-core' ); ?></span>
							</div>
						</article>
					</div>
					<p class="jpd-plugin__powered"><?php esc_html_e( 'Powered by JobCapturePro', 'jcp-core' ); ?></p>
				</div>
			</article>

			<article class="jpd-output" data-jpd-output="google">
				<p class="jpd-output__channel"><?php esc_html_e( '02 Google', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Fresh Google activity. Automatically.', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-gbp">
					<p class="jcp-sm-gbp__brand"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
					<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="180" loading="lazy" data-jpd-job-photo />
					<div class="jcp-sm-gbp__copy">
						<strong data-jpd-gbp-headline><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
						<p data-jpd-job-desc><?php esc_html_e( 'Fresh job proof from today’s completed work.', 'jcp-core' ); ?></p>
						<p class="jcp-sm-gbp__meta"><?php esc_html_e( 'Posted just now', 'jcp-core' ); ?></p>
					</div>
				</div>
			</article>

			<article class="jpd-output" data-jpd-output="social">
				<p class="jpd-output__channel"><?php esc_html_e( '03 Social', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'A social post your tech never had to write.', 'jcp-core' ); ?></h3>
				<div class="jcp-sm-social">
					<p class="jpd-run-brand"><?php esc_html_e( 'Your business', 'jcp-core' ); ?> · <span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span></p>
					<p class="jcp-sm-social__copy" data-jpd-social-copy><?php esc_html_e( 'Another job wrapped. Water heater replacement done right. Proof from the field.', 'jcp-core' ); ?></p>
					<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="400" height="200" loading="lazy" data-jpd-job-photo />
				</div>
			</article>

			<article class="jpd-output" data-jpd-output="directory">
				<p class="jpd-output__channel"><?php esc_html_e( '04 Directory', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Verified proof that stays findable.', 'jcp-core' ); ?></h3>
				<div class="jpd-directory-preview">
					<div class="directory-card directory-card-highlight jpd-directory-card" role="group" aria-label="<?php esc_attr_e( 'Your directory listing', 'jcp-core' ); ?>">
						<div class="jpd-channel__dir-head">
							<strong class="card-name"><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></strong>
							<span class="directory-badge verified"><?php esc_html_e( 'Verified', 'jcp-core' ); ?></span>
						</div>
						<p class="jpd-directory-trade" data-jpd-trade-label><?php esc_html_e( 'Plumbing', 'jcp-core' ); ?></p>
						<div class="card-location">
							<?php if ( $icon( 'map-pin' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
							<?php endif; ?>
							<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
						</div>
						<div class="card-meta-row">
							<span class="meta-inline"><?php esc_html_e( 'Jobs documented', 'jcp-core' ); ?></span>
							<span class="meta-divider">·</span>
							<span class="meta-inline"><?php esc_html_e( 'Active today', 'jcp-core' ); ?></span>
						</div>
						<div class="jpd-directory-latest">
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="72" height="54" loading="lazy" decoding="async" data-jpd-job-photo />
							<div>
								<p class="jpd-directory-latest__label"><?php esc_html_e( 'Latest completed job', 'jcp-core' ); ?></p>
								<strong data-jpd-directory-latest><?php echo esc_html( $default_service ); ?></strong>
							</div>
						</div>
					</div>
				</div>
			</article>

			<article class="jpd-output" data-jpd-output="review">
				<p class="jpd-output__channel"><?php esc_html_e( '05 Reviews', 'jcp-core' ); ?></p>
				<h3 class="jpd-output__title"><?php esc_html_e( 'Ask while they still remember your name.', 'jcp-core' ); ?></h3>
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
								<strong><?php esc_html_e( 'Send a review link', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Text it before you leave the driveway.', 'jcp-core' ); ?></span>
							</div>
						</div>
						<div class="jpd-review__row">
							<span class="jpd-review__icon" aria-hidden="true">
								<?php if ( $icon( 'smartphone' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'smartphone' ) ); ?>" alt="" width="18" height="18" />
								<?php endif; ?>
							</span>
							<div>
								<strong><?php esc_html_e( 'Show the QR', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Before the truck leaves.', 'jcp-core' ); ?></span>
							</div>
						</div>
						<p class="jpd-review__note"><?php esc_html_e( 'Before the customer forgets. Before you are chasing reviews weeks later.', 'jcp-core' ); ?></p>
					</div>
				</div>
			</article>
		</div>

		<p class="jpd-publish-note"><?php esc_html_e( 'Publishing depends on connected channels.', 'jcp-core' ); ?></p>

		<div class="rankings-cta jpd-trial-bridge" id="jpdTrialBridge" data-jpd-trial-bridge>
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'Now imagine this after every job.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'One job is proof. Twenty a month is a marketing system. Higher visibility, more reviews, more leads. Without adding a marketing job for the crew.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="run_convert" id="jpdTrialCta"><?php esc_html_e( 'Start my free 14 day trial →', 'jcp-core' ); ?></a>
				<p class="cta-note"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>

		<?php if ( $compact_reviews !== [] ) : ?>
			<div class="jpd-run-quotes">
				<?php foreach ( $compact_reviews as $r ) : ?>
					<blockquote class="jpd-quote jpd-quote--compact">
						<div class="jpd-quote__stars" aria-hidden="true">★★★★★</div>
						<p>“<?php echo esc_html( (string) ( $r['quote'] ?? '' ) ); ?>”</p>
						<footer>
							<?php if ( ! empty( $r['avatar'] ) ) : ?>
								<img src="<?php echo esc_url( (string) $r['avatar'] ); ?>" alt="" width="36" height="36" loading="lazy" />
							<?php endif; ?>
							<span>
								<strong><?php echo esc_html( (string) ( $r['name'] ?? '' ) ); ?></strong>
								<em><?php echo esc_html( (string) ( $r['role'] ?? '' ) ); ?></em>
							</span>
						</footer>
					</blockquote>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="jcp-section rankings-section jpd-run-gate" id="jpdRunGate" data-jpd-run-gate hidden>
	<div class="jcp-container">
		<p class="rankings-subtitle"><?php esc_html_e( 'Taking you to the demo form…', 'jcp-core' ); ?></p>
		<p><a class="btn btn-primary" href="<?php echo esc_url( $lp_href ); ?>#jpd-optin"><?php esc_html_e( 'Continue to the personalized demo form →', 'jcp-core' ); ?></a></p>
	</div>
</section>

<div class="jcp-case-exit jpd-exit" id="jpdExitRoot" hidden aria-hidden="true" role="dialog" aria-modal="true">
	<div class="jcp-case-exit__backdrop" data-jpd-exit-dismiss></div>
	<div class="jcp-case-exit__card">
		<button type="button" class="jcp-case-exit__close" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>" data-jpd-exit-dismiss>×</button>
		<div class="jpd-exit__panel" data-jpd-exit-panel="case">
			<p class="jcp-case-exit__wait jpd-exit__eyebrow"><?php esc_html_e( 'Not ready for a trial?', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title"><?php esc_html_e( 'Talk to a JCP expert, or see if you qualify for the 90 day case study.', 'jcp-core' ); ?></h2>
			<div class="jcp-case-exit__actions">
				<a class="jcp-case-exit__primary" href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				<a class="jcp-case-exit__dismiss" id="jpdExitCaseCta" href="<?php echo esc_url( isset( $case_href ) ? $case_href : home_url( '/case-study/' ) ); ?>"><?php esc_html_e( 'See if I qualify →', 'jcp-core' ); ?></a>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Go back to my demo', 'jcp-core' ); ?></button>
			</div>
		</div>
	</div>
</div>
