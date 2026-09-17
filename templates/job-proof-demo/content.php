<?php
/**
 * Job Proof Demo LP — paid landing page CRO refinement.
 *
 * Funnel logic unchanged. Visual + copy only.
 *
 * @package JCP_Core
 *
 * @var string $trial_href
 * @var string $expert_href
 * @var string $demo_run_url
 * @var string $case_href
 * @var string $photo_url
 * @var string $photo_fallback
 * @var bool   $case_active
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon = static function ( string $name ): string {
	return function_exists( 'jcp_core_icon' ) ? jcp_core_icon( $name ) : '';
};

$campaign   = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$reviews    = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
$business_type_options = function_exists( 'jcp_core_business_type_flat_options' )
	? jcp_core_business_type_flat_options()
	: [];
$integrations_uri = trailingslashit( get_template_directory_uri() ) . 'assets/integrations/';
$integrations     = [
	[
		'name' => 'Housecall Pro',
		'logo' => '',
		'mark' => 'housecall',
	],
	[
		'name' => 'CompanyCam',
		'logo' => $integrations_uri . 'companycam.svg',
	],
	[
		'name' => 'Workiz',
		'logo' => $integrations_uri . 'workiz.svg',
	],
	[
		'name' => 'QuickBooks',
		'logo' => $integrations_uri . 'quickbooks.svg',
	],
];
$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
$field_photo     = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$cta_primary     = __( 'See it on my business', 'jcp-core' );

$featured = null;
$rest     = [];
foreach ( $reviews as $r ) {
	if ( ! $featured && ( (string) ( $r['id'] ?? '' ) === 'brian-hardy' || stripos( (string) ( $r['name'] ?? '' ), 'Brian' ) === 0 ) ) {
		$featured = $r;
		continue;
	}
	$rest[] = $r;
}
if ( ! $featured && $reviews !== [] ) {
	$featured = $reviews[0];
	$rest     = array_slice( $reviews, 1 );
}
$rest = array_slice( $rest, 0, 3 );

$map_markets = [
	[
		'id'      => 'triadelphia',
		'label'   => __( 'Triadelphia, WV', 'jcp-core' ),
		'meta'    => __( '~6-mile tracked area · ~12 weeks', 'jcp-core' ),
		'keyword' => __( 'Foundation repair', 'jcp-core' ),
		'map_bg'  => $campaign . 'lf-map-triadelphia-640.webp',
		'before'  => [
			'solv'    => '0%',
			'summary' => __( 'Low / not prominently visible', 'jcp-core' ),
			'pattern' => 'before_blank',
		],
		'after'   => [
			'solv'    => '90%',
			'summary' => __( 'Stronger local visibility', 'jcp-core' ),
			'pattern' => 'after_fr_wv',
		],
	],
	[
		'id'      => 'monroe',
		'label'   => __( 'Monroe, MI', 'jcp-core' ),
		'meta'    => __( '~6-mile tracked area · ~12 weeks', 'jcp-core' ),
		'keyword' => __( 'Foundation repair', 'jcp-core' ),
		'map_bg'  => $campaign . 'lf-map-monroe-640.webp',
		'before'  => [
			'solv'    => '0%',
			'summary' => __( 'Low / not prominently visible', 'jcp-core' ),
			'pattern' => 'before_blank',
		],
		'after'   => [
			'solv'    => '84%',
			'summary' => __( 'Stronger local visibility', 'jcp-core' ),
			'pattern' => 'after_fr_mi',
		],
	],
];

$faq_items = [
	[
		'q' => __( 'Does my crew need another app?', 'jcp-core' ),
		'a' => __( 'No. If they already take consistent job photos, you already have the raw material. Use JobCapturePro directly — or keep a supported photo/CRM workflow your team already knows. JCP handles what happens after the photo.', 'jcp-core' ),
	],
	[
		'q' => __( 'What systems/workflows can JCP work with?', 'jcp-core' ),
		'a' => __( 'Supported integrations today: Housecall Pro, CompanyCam, Workiz, and QuickBooks — plus capturing photos directly in JobCapturePro.', 'jcp-core' ),
	],
	[
		'q' => __( 'What does JobCapturePro actually publish?', 'jcp-core' ),
		'a' => __( 'Finished-job proof for your website, Google Business Profile activity, social content, review opportunities while the job is fresh, and a public JCP Directory footprint.', 'jcp-core' ),
	],
	[
		'q' => __( 'Do you guarantee Google rankings?', 'jcp-core' ),
		'a' => __( 'No. Local search depends on many factors. JCP helps you publish consistent, authentic proof from real completed work — it does not guarantee rankings or leads.', 'jcp-core' ),
	],
	[
		'q' => __( 'What happens after the free trial?', 'jcp-core' ),
		'a' => __( 'If JobCapturePro is a fit, continue on a paid plan. If not, cancel. The trial is for evaluating the product with real jobs, not a sandbox.', 'jcp-core' ),
	],
];

$render_map_panel = static function ( array $side, string $phase, string $map_bg ): void {
	$ranks = function_exists( 'jcp_lf_case_grid_pattern' )
		? jcp_lf_case_grid_pattern( (string) ( $side['pattern'] ?? 'before_blank' ) )
		: array_fill( 0, 49, $phase === 'before' ? 20 : 2 );
	$label = $phase === 'before' ? __( 'Before', 'jcp-core' ) : __( 'After', 'jcp-core' );
	?>
	<div class="jpd-map__panel jpd-map__panel--<?php echo esc_attr( $phase ); ?>">
		<div class="jpd-map__panel-head">
			<span class="jpd-map__phase"><?php echo esc_html( $label ); ?></span>
			<strong class="jpd-map__solv"><?php echo esc_html( (string) ( $side['solv'] ?? '' ) ); ?></strong>
			<span class="jpd-map__summary"><?php echo esc_html( (string) ( $side['summary'] ?? '' ) ); ?></span>
		</div>
		<div class="jpd-map__grid-wrap" style="--jpd-map-bg: url('<?php echo esc_url( $map_bg ); ?>');">
			<div class="jpd-map__geo" aria-hidden="true">
				<?php foreach ( $ranks as $rank ) : ?>
					<span class="jpd-map__cell" data-rank="<?php echo esc_attr( (string) (int) $rank ); ?>"></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
};
?>

<!-- 1. Hero -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split jcp-layout-align-left jcp-hero-has-visual jpd-hero" id="proof" aria-labelledby="jpd-hero-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout jpd-hero__grid">
			<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'You already have the proof.', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Your crew already creates the proof. JobCapturePro turns it into marketing.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><strong><?php esc_html_e( 'Real jobs. Real photos. Real locations.', 'jcp-core' ); ?></strong><br /><?php esc_html_e( 'JCP turns finished work into fresh website proof, Google activity, social content, review opportunities and a public JCP Directory footprint — without asking your techs to become marketers.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<div class="jcp-hero-primary-cta">
						<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="hero" data-jpd-source="hero">
							<span class="jcp-hero-cta-label"><?php echo esc_html( $cta_primary ); ?> →</span>
							<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free personalized demo · Work email + trade · No credit card', 'jcp-core' ); ?></span>
						</a>
					</div>
					<p class="jpd-hero-secondary">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Already sold? Start free trial →', 'jcp-core' ); ?></a>
					</p>
					<p class="jpd-hero-line"><?php esc_html_e( 'Your tech can go back to fixing things. JCP handles the marketing part.', 'jcp-core' ); ?></p>
				</div>
			</div>

			<div class="jcp-hero-visual-column jcp-split-col jcp-split-col--media" aria-hidden="true">
				<div class="jpd-theater" data-jpd-theater>
					<div class="jpd-theater__grid">
						<article class="jpd-theater__source" data-theater="source">
							<div class="jpd-theater__source-media">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="640" height="420" decoding="async" data-no-lazy data-fallback="<?php echo esc_url( $photo_fallback ); ?>" />
								<span class="jpd-canvas__badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
							</div>
							<div class="jpd-theater__source-meta">
								<strong><?php echo esc_html( $default_service ); ?></strong>
								<span><?php echo esc_html( $default_city ); ?></span>
							</div>
						</article>

						<div class="jpd-theater__bridge" data-theater="engine">
							<span class="jpd-theater__mark" aria-hidden="true"><?php esc_html_e( 'JCP', 'jcp-core' ); ?></span>
							<ul class="jpd-theater__resolve">
								<li data-resolve="1"><?php esc_html_e( 'Check-in', 'jcp-core' ); ?></li>
								<li data-resolve="2"><?php esc_html_e( 'Service', 'jcp-core' ); ?></li>
								<li data-resolve="3"><?php esc_html_e( 'Location', 'jcp-core' ); ?></li>
							</ul>
						</div>

						<ul class="jpd-theater__channels" data-theater="dest">
							<li class="jpd-theater__channel" data-preview="1">
								<span class="jpd-theater__channel-thumb"><img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="48" height="36" decoding="async" data-no-lazy /></span>
								<span class="jpd-theater__channel-copy">
									<strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Job map + check-in', 'jcp-core' ); ?></span>
								</span>
							</li>
							<li class="jpd-theater__channel" data-preview="2">
								<span class="jpd-theater__channel-thumb jpd-theater__channel-thumb--gbp" aria-hidden="true">G</span>
								<span class="jpd-theater__channel-copy">
									<strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Fresh job post', 'jcp-core' ); ?></span>
								</span>
							</li>
							<li class="jpd-theater__channel" data-preview="3">
								<span class="jpd-theater__channel-thumb"><img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="48" height="36" decoding="async" data-no-lazy /></span>
								<span class="jpd-theater__channel-copy">
									<strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Proof from the field', 'jcp-core' ); ?></span>
								</span>
							</li>
							<li class="jpd-theater__channel" data-preview="4">
								<span class="jpd-theater__channel-thumb jpd-theater__channel-thumb--star" aria-hidden="true">★</span>
								<span class="jpd-theater__channel-copy">
									<strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Ask while fresh', 'jcp-core' ); ?></span>
								</span>
							</li>
							<li class="jpd-theater__channel" data-preview="5">
								<span class="jpd-theater__channel-thumb jpd-theater__channel-thumb--dir" aria-hidden="true">◆</span>
								<span class="jpd-theater__channel-copy">
									<strong><?php esc_html_e( 'Directory', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Verified job proof', 'jcp-core' ); ?></span>
								</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. Authority -->
<section class="jpd-cred-strip" id="jpd-authority" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
	<div class="jcp-container">
		<p class="jpd-cred-strip__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
		<ul class="jpd-cred-strip__stats">
			<li>
				<strong class="jcp-count-up" data-count-to="10" data-count-prefix="" data-count-suffix="+" data-count-format="plain" data-count-decimals="0" data-count-ms="1200">0</strong>
				<span><?php esc_html_e( 'years helping contractors grow', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong class="jcp-count-up" data-count-to="250" data-count-prefix="" data-count-suffix="K+" data-count-format="plain" data-count-decimals="0" data-count-ms="1200">0</strong>
				<span><?php esc_html_e( 'leads generated', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong class="jcp-count-up" data-count-to="150" data-count-prefix="$" data-count-suffix="M+" data-count-format="plain" data-count-decimals="0" data-count-ms="1200">0</strong>
				<span><?php esc_html_e( 'revenue booked from those leads', 'jcp-core' ); ?></span>
			</li>
		</ul>
	</div>
</section>

<!-- 3. Map / local visibility -->
<section class="jcp-section jpd-map" id="jpd-map" aria-labelledby="jpd-map-title">
	<div class="jcp-container">
		<header class="jpd-map__head">
			<p class="jpd-eyebrow"><?php esc_html_e( 'Local visibility proof', 'jcp-core' ); ?></p>
			<h2 id="jpd-map-title" class="jcp-section-headline"><?php esc_html_e( 'Real job proof helps build local visibility where the work actually happens.', 'jcp-core' ); ?></h2>
			<p class="jpd-map__lead"><?php esc_html_e( 'Every completed job can add fresh service and location context to the places customers check before they call.', 'jcp-core' ); ?></p>
			<p class="jpd-map__source"><?php esc_html_e( 'LeadsForward local-search evidence · Local Falcon Share of Local Voice (how often a business shows in the Google Maps 3-Pack across a tracked grid). Past results do not guarantee future rankings.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-map__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Example markets', 'jcp-core' ); ?>">
			<?php foreach ( $map_markets as $i => $market ) : ?>
				<button
					type="button"
					class="jpd-map__tab<?php echo 0 === $i ? ' is-active' : ''; ?>"
					role="tab"
					id="jpd-map-tab-<?php echo esc_attr( $market['id'] ); ?>"
					aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
					aria-controls="jpd-map-panel-<?php echo esc_attr( $market['id'] ); ?>"
					data-jpd-map-tab="<?php echo esc_attr( $market['id'] ); ?>"
				><?php echo esc_html( $market['label'] ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="jpd-map__legend" aria-hidden="true">
			<span class="jpd-map__legend-item jpd-map__legend-item--red"><i></i><?php esc_html_e( 'Red = low / not prominently visible', 'jcp-core' ); ?></span>
			<span class="jpd-map__legend-item jpd-map__legend-item--green"><i></i><?php esc_html_e( 'Green = stronger local visibility', 'jcp-core' ); ?></span>
		</div>

		<?php foreach ( $map_markets as $i => $market ) : ?>
			<div
				class="jpd-map__market<?php echo 0 === $i ? ' is-active' : ''; ?>"
				id="jpd-map-panel-<?php echo esc_attr( $market['id'] ); ?>"
				role="tabpanel"
				aria-labelledby="jpd-map-tab-<?php echo esc_attr( $market['id'] ); ?>"
				data-jpd-map-panel="<?php echo esc_attr( $market['id'] ); ?>"
				<?php echo 0 === $i ? '' : 'hidden'; ?>
			>
				<div class="jpd-map__market-meta">
					<strong><?php echo esc_html( $market['label'] ); ?></strong>
					<span><?php echo esc_html( $market['keyword'] ); ?> · <?php echo esc_html( $market['meta'] ); ?></span>
				</div>
				<div class="jpd-map__compare">
					<?php
					$render_map_panel( $market['before'], 'before', $market['map_bg'] );
					$render_map_panel( $market['after'], 'after', $market['map_bg'] );
					?>
				</div>
			</div>
		<?php endforeach; ?>

		<ul class="jpd-map__takeaways">
			<li>
				<strong><?php esc_html_e( 'Real jobs', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'Give Google and customers fresh evidence of what you actually do.', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Real locations', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'Add service-area context from the places your crews actually work.', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Recency', 'jcp-core' ); ?></strong>
				<span><?php esc_html_e( 'Every completed job gives your online presence another fresh signal.', 'jcp-core' ); ?></span>
			</li>
		</ul>

		<p class="jpd-section-cta">
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="map" data-jpd-source="map"><?php echo esc_html( $cta_primary ); ?> →</a>
		</p>
	</div>
</section>

<!-- 4. Workflow -->
<section class="jcp-section jpd-workflow" id="workflow" aria-labelledby="jpd-workflow-title">
	<div class="jcp-container">
		<header class="jpd-workflow__head">
			<p class="jpd-eyebrow"><?php esc_html_e( 'No new marketing job for the crew', 'jcp-core' ); ?></p>
			<h2 id="jpd-workflow-title" class="jcp-section-headline"><?php esc_html_e( 'Your tech already has a job. Marketing assistant isn’t it.', 'jcp-core' ); ?></h2>
			<p class="jpd-workflow__lead"><?php esc_html_e( 'If your crew already takes consistent job photos, you already have the raw material.', 'jcp-core' ); ?></p>
			<p class="jpd-punch"><?php esc_html_e( 'Use JCP directly — or keep supported photo/CRM workflows your team already knows. JCP handles what happens after the photo.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-workflow__stage">
			<figure class="jpd-workflow__field">
				<img src="<?php echo esc_url( $field_photo ); ?>" alt="<?php esc_attr_e( 'Technician photographing completed HVAC work on site', 'jcp-core' ); ?>" width="640" height="420" loading="lazy" decoding="async" />
				<figcaption><?php esc_html_e( 'Don’t let the proof die in a camera roll.', 'jcp-core' ); ?></figcaption>
			</figure>

			<div class="jpd-workflow__integrations">
				<p class="jpd-workflow__integ-label"><?php esc_html_e( 'Works with tools your crew already uses', 'jcp-core' ); ?></p>
				<ul class="jpd-logo-row jpd-logo-row--light" aria-label="<?php esc_attr_e( 'Supported integrations', 'jcp-core' ); ?>">
					<?php foreach ( $integrations as $integration ) : ?>
						<li>
							<?php if ( ( $integration['mark'] ?? '' ) === 'housecall' ) : ?>
								<span class="jpd-logo-mark jpd-logo-mark--hcp" aria-label="<?php echo esc_attr( $integration['name'] ); ?>"><em>Housecall</em> Pro</span>
							<?php else : ?>
								<img src="<?php echo esc_url( $integration['logo'] ); ?>" alt="<?php echo esc_attr( $integration['name'] ); ?>" width="140" height="28" loading="lazy" decoding="async" />
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- 5. Proof-gap calculator -->
<section class="jcp-section jpd-calc" id="jpd-calculator" aria-labelledby="jpd-calc-title">
	<div class="jcp-container">
		<header class="jpd-calc__head">
			<p class="jpd-eyebrow"><?php esc_html_e( 'Optional proof check', 'jcp-core' ); ?></p>
			<h2 id="jpd-calc-title" class="jcp-section-headline"><?php esc_html_e( 'How much completed-job proof is sitting unused?', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Two quick numbers. This does not block the demo — skip anytime.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-calc__card" data-jpd-calc>
			<form id="jpdCalcForm" novalidate>
				<div class="jpd-calc__fields">
					<label class="jpd-calc__field">
						<span><?php esc_html_e( 'How many jobs does your company finish in a typical week?', 'jcp-core' ); ?></span>
						<input type="number" id="jpdCalcJobs" name="jobs" min="0" max="500" inputmode="numeric" placeholder="8" required />
					</label>
					<label class="jpd-calc__field">
						<span><?php esc_html_e( 'How many of those usually make it onto your website, Google or social?', 'jcp-core' ); ?></span>
						<input type="number" id="jpdCalcUsed" name="used" min="0" max="500" inputmode="numeric" placeholder="1" required />
					</label>
				</div>
				<button type="submit" class="btn btn-primary" id="jpdCalcSubmit"><?php esc_html_e( 'Show my unused proof →', 'jcp-core' ); ?></button>
			</form>

			<div class="jpd-calc__result" id="jpdCalcResult" hidden>
				<p class="jpd-calc__kicker"><?php esc_html_e( 'Your proof gap', 'jcp-core' ); ?></p>
				<h3 class="jpd-calc__result-title">
					<?php esc_html_e( 'You finish roughly', 'jcp-core' ); ?>
					<span id="jpdCalcAnnual">416</span>
					<?php esc_html_e( 'jobs per year.', 'jcp-core' ); ?>
				</h3>
				<p class="jpd-calc__result-body" id="jpdCalcSentence"></p>
				<p class="jpd-calc__result-note"><?php esc_html_e( 'That’s hundreds of chances to show homeowners what you do, where you work and why they should trust you.', 'jcp-core' ); ?></p>
				<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="calculator" data-jpd-source="calculator"><?php echo esc_html( $cta_primary ); ?> →</a>
			</div>
		</div>

		<p class="jpd-calc__skip">
			<a href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="calculator_skip" data-jpd-source="calculator"><?php echo esc_html( $cta_primary ); ?> →</a>
		</p>
	</div>
</section>

<!-- 6. Personalized demo form -->
<section class="jcp-section jpd-convert" id="jpd-optin" data-jpd-optin aria-labelledby="jpd-optin-title">
	<div class="jcp-container">
		<div class="jpd-convert__grid">
			<div class="jpd-convert__copy">
				<p class="jpd-eyebrow"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="jpd-optin-title" class="jcp-section-headline"><?php esc_html_e( 'See what JCP would do with the jobs your business already completes.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'Tell us your trade and we’ll show you the kind of finished job your crew handles every week — and what JobCapturePro can turn it into.', 'jcp-core' ); ?></p>
				<?php if ( $featured ) : ?>
					<blockquote class="jpd-convert__quote">
						<p>“<?php echo esc_html( (string) ( $featured['quote'] ?? '' ) ); ?>”</p>
						<footer>— <strong><?php echo esc_html( (string) ( $featured['name'] ?? '' ) ); ?></strong>, <?php echo esc_html( (string) ( $featured['role'] ?? '' ) ); ?></footer>
					</blockquote>
				<?php endif; ?>
			</div>

			<div class="jpd-convert__form jpd-optin__card survey-step active">
				<form class="survey-form survey-form--gate" id="jpdOptinForm" autocomplete="on" novalidate>
					<div class="survey-field">
						<label for="jpd-email"><?php esc_html_e( 'Work email', 'jcp-core' ); ?> <span class="survey-required">*</span></label>
						<input id="jpd-email" name="email" type="email" class="survey-input" placeholder="you@company.com" autocomplete="email" inputmode="email" required />
					</div>
					<div class="survey-field survey-combobox">
						<label for="jpd-nicheSearch"><?php esc_html_e( 'Trade', 'jcp-core' ); ?> <span class="survey-required">*</span></label>
						<div class="survey-combobox__control">
							<input
								id="jpd-nicheSearch"
								type="text"
								class="survey-input"
								placeholder="<?php esc_attr_e( 'Start typing your trade…', 'jcp-core' ); ?>"
								autocomplete="off"
								role="combobox"
								aria-expanded="false"
								aria-controls="jpd-nicheListbox"
								aria-autocomplete="list"
								required
							/>
							<input type="hidden" id="jpd-niche" value="" />
							<input type="hidden" id="jpd-nicheOther" value="" />
							<ul id="jpd-nicheListbox" class="survey-combobox__list" role="listbox" hidden aria-label="<?php esc_attr_e( 'Trade suggestions', 'jcp-core' ); ?>"></ul>
						</div>
						<script type="application/json" id="jcpBusinessTypeOptions"><?php echo wp_json_encode( $business_type_options ); ?></script>
					</div>
					<p class="jpd-optin__error" id="jpdOptinError" role="alert" hidden></p>
					<button type="submit" class="btn btn-primary survey-btn" id="jpdOptinSubmit"><?php esc_html_e( 'Show me my demo →', 'jcp-core' ); ?></button>
					<p class="survey-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free · About 60 seconds · No phone number · No credit card', 'jcp-core' ); ?></p>
					<p class="jpd-convert__trial-link">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="optin"><?php esc_html_e( 'Start free trial →', 'jcp-core' ); ?></a>
					</p>
					<p class="survey-legal"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- 7. Testimonials -->
<section class="jcp-section jpd-trust" id="why-jcp" aria-labelledby="jpd-reviews-title">
	<div class="jcp-container">
		<div class="jpd-trust__reviews">
			<h2 id="jpd-reviews-title" class="jcp-section-headline jpd-trust__reviews-title"><?php esc_html_e( 'Real contractors. Real jobs. No marketing fairy dust.', 'jcp-core' ); ?></h2>
			<?php if ( $featured ) : ?>
				<blockquote class="jpd-quote jpd-quote--featured">
					<div class="jpd-quote__stars" aria-hidden="true">★★★★★</div>
					<p>“<?php echo esc_html( (string) ( $featured['quote'] ?? '' ) ); ?>”</p>
					<footer>
						<?php if ( ! empty( $featured['avatar'] ) ) : ?>
							<img src="<?php echo esc_url( (string) $featured['avatar'] ); ?>" alt="" width="40" height="40" loading="lazy" />
						<?php endif; ?>
						<span>
							<strong><?php echo esc_html( (string) ( $featured['name'] ?? '' ) ); ?></strong>
							<em><?php echo esc_html( (string) ( $featured['role'] ?? '' ) ); ?></em>
						</span>
					</footer>
				</blockquote>
			<?php endif; ?>
			<div class="jpd-quote-grid jpd-quote-grid--compact">
				<?php foreach ( $rest as $r ) : ?>
					<blockquote class="jpd-quote">
						<div class="jpd-quote__stars" aria-hidden="true">★★★★★</div>
						<p>“<?php echo esc_html( (string) ( $r['quote'] ?? '' ) ); ?>”</p>
						<footer>
							<?php if ( ! empty( $r['avatar'] ) ) : ?>
								<img src="<?php echo esc_url( (string) $r['avatar'] ); ?>" alt="" width="32" height="32" loading="lazy" />
							<?php endif; ?>
							<span>
								<strong><?php echo esc_html( (string) ( $r['name'] ?? '' ) ); ?></strong>
								<em><?php echo esc_html( (string) ( $r['role'] ?? '' ) ); ?></em>
							</span>
						</footer>
					</blockquote>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- 8. Trial -->
<section class="jcp-section jpd-trial" id="jpd-trial" aria-labelledby="jpd-trial-title">
	<div class="jcp-container jpd-trial__layout">
		<div class="jpd-trial__copy">
			<p class="jpd-eyebrow jpd-eyebrow--on-dark"><?php esc_html_e( '14-day free trial', 'jcp-core' ); ?></p>
			<h2 id="jpd-trial-title" class="jpd-trial__title"><?php esc_html_e( 'Your next 14 days are already full of marketing. Don’t let those jobs disappear too.', 'jcp-core' ); ?></h2>
			<p class="jpd-trial__sub"><?php esc_html_e( 'Start with the jobs your company is already completing. Same job. More places working for you.', 'jcp-core' ); ?></p>
		</div>
		<div class="jpd-trial__panel">
			<ul class="jpd-trial__benefits">
				<li><?php esc_html_e( 'Turn finished jobs into public proof', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Publish across connected channels', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Create review opportunities while the job is fresh', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Build a growing history of real completed work', 'jcp-core' ); ?></li>
			</ul>
			<a class="btn btn-primary jpd-trial__cta" href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="trial"><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
			<p class="jpd-trial__micro"><?php esc_html_e( 'No credit card required.', 'jcp-core' ); ?></p>
		</div>
	</div>
</section>

<!-- 9. FAQ -->
<section class="jcp-section jpd-faq" id="jpd-faq" aria-labelledby="jpd-faq-title">
	<div class="jcp-container">
		<header class="jpd-faq__head">
			<p class="jpd-eyebrow"><?php esc_html_e( 'Quick answers', 'jcp-core' ); ?></p>
			<h2 id="jpd-faq-title" class="jcp-section-headline"><?php esc_html_e( 'Before you decide.', 'jcp-core' ); ?></h2>
		</header>
		<div class="jpd-faq__list">
			<?php foreach ( $faq_items as $i => $item ) : ?>
				<details class="jpd-faq__item"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $item['q'] ); ?></summary>
					<p><?php echo esc_html( $item['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 10. Final CTA -->
<section class="jcp-section rankings-section jcp-niche-final jpd-final-cta">
	<div class="jcp-container">
		<div class="rankings-cta jpd-final-cta__band">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'You already paid to do the job. Make it help win the next one.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'Free personalized demo · Work email + trade', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="final" data-jpd-source="final"><?php echo esc_html( $cta_primary ); ?> →</a>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="final"><?php esc_html_e( 'Start free trial →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<!-- Mobile sticky CTA -->
<div class="jpd-sticky-cta" id="jpdStickyCta" hidden>
	<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="mobile_sticky" data-jpd-source="mobile_sticky"><?php echo esc_html( $cta_primary ); ?> →</a>
</div>

<!-- Exit intent (unchanged semantics) -->
<div class="jcp-case-exit jpd-exit" id="jpdExitRoot" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="jpdExitTitle">
	<div class="jcp-case-exit__backdrop" data-jpd-exit-dismiss></div>
	<div class="jcp-case-exit__card">
		<button type="button" class="jcp-case-exit__close" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>" data-jpd-exit-dismiss>×</button>
		<div class="jpd-exit__panel" data-jpd-exit-panel="optin" hidden>
			<p class="jcp-case-exit__wait"><?php esc_html_e( 'BEFORE YOU GO', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title" id="jpdExitTitle"><?php esc_html_e( 'Before you bail, want the 60 second version for your trade?', 'jcp-core' ); ?></h2>
			<form id="jpdExitOptinForm" class="jpd-exit__form survey-step active" novalidate>
				<div class="survey-field">
					<label for="jpd-exit-email"><?php esc_html_e( 'Work email', 'jcp-core' ); ?> <span class="survey-required">*</span></label>
					<input id="jpd-exit-email" name="email" type="email" class="survey-input" placeholder="you@company.com" autocomplete="email" required />
				</div>
				<div class="survey-field">
					<label for="jpd-exit-nicheSearch"><?php esc_html_e( 'Trade', 'jcp-core' ); ?> <span class="survey-required">*</span></label>
					<div class="survey-combobox" data-jpd-exit-combobox>
						<input id="jpd-exit-nicheSearch" type="text" class="survey-input survey-combobox__input" placeholder="<?php esc_attr_e( 'Start typing your trade…', 'jcp-core' ); ?>" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="jpd-exit-nicheListbox" />
						<input type="hidden" id="jpd-exit-niche" value="" />
						<input type="hidden" id="jpd-exit-nicheOther" value="" />
						<ul id="jpd-exit-nicheListbox" class="survey-combobox__list" role="listbox" hidden></ul>
					</div>
				</div>
				<p class="jpd-optin__error" id="jpdExitOptinError" role="alert" hidden></p>
				<button type="submit" class="btn btn-primary jcp-case-exit__primary" id="jpdExitOptinSubmit"><?php esc_html_e( 'Send me my demo →', 'jcp-core' ); ?></button>
			</form>
			<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'No thanks', 'jcp-core' ); ?></button>
		</div>
		<div class="jpd-exit__panel" data-jpd-exit-panel="case" hidden>
			<p class="jcp-case-exit__wait jpd-exit__eyebrow"><?php esc_html_e( 'Not ready for a trial?', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title"><?php esc_html_e( 'Talk to a JCP expert, or see if you qualify for the 90 day case study.', 'jcp-core' ); ?></h2>
			<div class="jcp-case-exit__actions">
				<a class="jcp-case-exit__primary" href="<?php echo esc_url( $expert_href ); ?>" data-jpd-expert><?php esc_html_e( 'Talk to a JCP expert →', 'jcp-core' ); ?></a>
				<?php if ( $case_active ) : ?>
					<a class="jcp-case-exit__dismiss" id="jpdExitCaseCta" href="<?php echo esc_url( $case_href ); ?>"><?php esc_html_e( 'See if I qualify for the case study →', 'jcp-core' ); ?></a>
				<?php endif; ?>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Go back', 'jcp-core' ); ?></button>
			</div>
		</div>
	</div>
</div>
