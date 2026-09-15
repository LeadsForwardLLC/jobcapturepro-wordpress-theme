<?php
/**
 * Job Proof Demo LP — Product Theater paid landing page.
 *
 * Exactly 7 sections. Visual/CRO only — funnel logic unchanged.
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
		'logo' => $integrations_uri . 'housecall-pro.svg',
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
$founder_thumb   = $campaign . 'jcp-campaign-face-operator-640.webp';
$field_photo     = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$map_url         = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';
$cta_primary     = __( 'See my free personalized demo', 'jcp-core' );

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
?>

<!-- 1. Hero — Product Theater -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split jcp-layout-align-left jcp-hero-has-visual jpd-hero" id="proof" aria-labelledby="jpd-hero-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout jpd-hero__grid">
			<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'You already have the proof.', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Your crew does the work. JCP turns the proof into marketing.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><?php esc_html_e( 'One finished job → website, Google, social, reviews, and Directory — automatically.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<div class="jcp-hero-primary-cta">
						<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="hero" data-jpd-source="hero">
							<span class="jcp-hero-cta-label"><?php echo esc_html( $cta_primary ); ?> →</span>
							<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Work email + trade · ~60 seconds · No card', 'jcp-core' ); ?></span>
						</a>
					</div>
					<p class="jpd-hero-secondary">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Already sold? Start free trial →', 'jcp-core' ); ?></a>
					</p>
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
								<strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Job map + check-in', 'jcp-core' ); ?></span>
							</li>
							<li class="jpd-theater__channel" data-preview="2">
								<strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Fresh job post', 'jcp-core' ); ?></span>
							</li>
							<li class="jpd-theater__channel" data-preview="3">
								<strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Proof from the field', 'jcp-core' ); ?></span>
							</li>
							<li class="jpd-theater__channel" data-preview="4">
								<strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Ask while fresh', 'jcp-core' ); ?></span>
							</li>
							<li class="jpd-theater__channel" data-preview="5">
								<strong><?php esc_html_e( 'Directory', 'jcp-core' ); ?></strong>
								<span><?php esc_html_e( 'Verified job proof', 'jcp-core' ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. Credibility + works-with -->
<section class="jpd-cred-strip" id="jpd-authority" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
	<div class="jcp-container">
		<div class="jpd-cred-strip__inner">
			<p class="jpd-cred-strip__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
			<ul class="jpd-cred-strip__stats">
				<li>
					<strong class="jcp-count-up" data-count-to="10" data-count-prefix="" data-count-suffix="" data-count-format="plain" data-count-decimals="0" data-count-ms="1200">0</strong>
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
		<div class="jpd-cred-strip__works">
			<p class="jpd-cred-strip__works-label"><?php esc_html_e( 'Works with the tools your crew already uses', 'jcp-core' ); ?></p>
			<ul class="jpd-logo-row" aria-label="<?php esc_attr_e( 'Supported integrations', 'jcp-core' ); ?>">
				<?php foreach ( $integrations as $integration ) : ?>
					<li>
						<img src="<?php echo esc_url( $integration['logo'] ); ?>" alt="<?php echo esc_attr( $integration['name'] ); ?>" width="140" height="28" loading="lazy" decoding="async" />
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

<!-- 3. Workflow + problem (merged) -->
<section class="jcp-section jpd-workflow" id="workflow" aria-labelledby="jpd-workflow-title">
	<div class="jcp-container">
		<div class="jpd-workflow__grid">
			<div class="jpd-workflow__copy">
				<p class="jpd-eyebrow"><?php esc_html_e( 'No new marketing job for the crew', 'jcp-core' ); ?></p>
				<h2 id="jpd-workflow-title" class="jcp-section-headline"><?php esc_html_e( 'Your tech has a job. “Marketing assistant” isn’t it.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'If your team already takes job photos, you already have the raw material. Capture in JCP — or keep using workflows you know.', 'jcp-core' ); ?></p>
				<p class="jpd-punch"><?php esc_html_e( 'JCP handles what happens after the photo.', 'jcp-core' ); ?></p>
				<figure class="jpd-workflow__field">
					<img src="<?php echo esc_url( $field_photo ); ?>" alt="<?php esc_attr_e( 'Technician photographing completed HVAC work on site', 'jcp-core' ); ?>" width="640" height="420" loading="lazy" decoding="async" />
					<figcaption><?php esc_html_e( 'Your guys already shoot the proof. JCP ships it.', 'jcp-core' ); ?></figcaption>
				</figure>
			</div>

			<div class="jpd-compare" aria-label="<?php esc_attr_e( 'Without JCP vs with JCP', 'jcp-core' ); ?>">
				<div class="jpd-compare__side jpd-compare__side--without">
					<p class="jpd-compare__label"><?php esc_html_e( 'Without JCP', 'jcp-core' ); ?></p>
					<ol>
						<li><?php esc_html_e( 'Job completed', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Photos saved', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'CRM / camera roll', 'jcp-core' ); ?></li>
						<li class="is-dead"><?php esc_html_e( 'Nothing happens', 'jcp-core' ); ?></li>
					</ol>
				</div>
				<div class="jpd-compare__side jpd-compare__side--with">
					<p class="jpd-compare__label"><?php esc_html_e( 'With JCP', 'jcp-core' ); ?></p>
					<ol>
						<li><?php esc_html_e( 'Job completed', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'JCP', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Website · Google · Social', 'jcp-core' ); ?></li>
						<li class="is-live"><?php esc_html_e( 'Reviews · Directory', 'jcp-core' ); ?></li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 4. Finished outputs (large real UI — not a flowchart) -->
<section class="jcp-section jpd-transform" id="ai-transform" aria-labelledby="jpd-ai-title" data-jpd-transform>
	<div class="jcp-container">
		<header class="jpd-transform__head">
			<p class="jpd-eyebrow"><?php esc_html_e( 'One job. Five places.', 'jcp-core' ); ?></p>
			<h2 id="jpd-ai-title" class="jcp-section-headline"><?php esc_html_e( 'This is what the finished output looks like.', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Same completed job — live across the channels customers check before they call.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-showcase" data-jpd-showcase>
			<article class="jpd-showcase__web jcp-sm-browser" data-showcase="web">
				<div class="jcp-sm-browser__chrome"><span></span><span></span><span></span><em>yoursite.com/jobs</em></div>
				<div class="jpd-showcase__map">
					<img src="<?php echo esc_url( $map_url ); ?>" alt="" width="640" height="280" loading="lazy" />
					<span class="jpd-plugin__pin jpd-plugin__pin--active" style="left:44%;top:46%;"></span>
					<span class="jpd-plugin__pin" style="left:32%;top:38%;"></span>
					<span class="jpd-plugin__pin" style="left:56%;top:58%;"></span>
				</div>
				<div class="jpd-showcase__checkins">
					<div class="jpd-showcase__checkin is-active">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="72" height="54" loading="lazy" />
						<div>
							<strong><?php echo esc_html( $default_service ); ?></strong>
							<span><?php echo esc_html( $default_city ); ?></span>
						</div>
					</div>
					<div class="jpd-showcase__checkin">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="72" height="54" loading="lazy" />
						<div>
							<strong><?php esc_html_e( 'Recent check-in', 'jcp-core' ); ?></strong>
							<span><?php esc_html_e( 'Same trade · nearby', 'jcp-core' ); ?></span>
						</div>
					</div>
				</div>
			</article>

			<div class="jpd-showcase__rail">
				<article class="jpd-showcase__panel jcp-sm-gbp" data-showcase="gbp">
					<p class="jcp-sm-gbp__brand"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
					<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="360" height="160" loading="lazy" />
					<strong><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
				</article>
				<article class="jpd-showcase__panel jcp-sm-social" data-showcase="social">
					<div class="jcp-sm-social__head">
						<span class="jpd-social-avatar" aria-hidden="true">YB</span>
						<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
					</div>
					<p><?php esc_html_e( 'Another job wrapped — proof from the field.', 'jcp-core' ); ?></p>
					<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="360" height="140" loading="lazy" />
				</article>
			</div>

			<article class="jpd-showcase__panel jpd-showcase__panel--review" data-showcase="review">
				<?php if ( $icon( 'qr-code' ) ) : ?>
					<img class="jpd-showcase__qr" src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="88" height="88" />
				<?php endif; ?>
				<div>
					<p class="jpd-showcase__kicker"><?php esc_html_e( 'Review request', 'jcp-core' ); ?></p>
					<strong><?php esc_html_e( 'Ask while it’s fresh', 'jcp-core' ); ?></strong>
					<span><?php esc_html_e( 'On-site QR · same job · same day', 'jcp-core' ); ?></span>
				</div>
			</article>

			<article class="jpd-showcase__panel directory-card jpd-showcase__panel--dir" data-showcase="dir">
				<span class="directory-badge verified"><?php esc_html_e( 'Verified', 'jcp-core' ); ?></span>
				<div class="card-header">
					<div class="company-avatar">YB</div>
					<strong class="card-name"><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></strong>
				</div>
				<div class="jpd-directory-latest">
					<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="72" height="54" loading="lazy" />
					<div>
						<strong><?php echo esc_html( $default_service ); ?></strong>
						<span><?php echo esc_html( $default_city ); ?></span>
					</div>
				</div>
			</article>
		</div>

		<p class="jpd-section-cta">
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="transform" data-jpd-source="transform"><?php esc_html_e( 'Show me this for my trade →', 'jcp-core' ); ?></a>
		</p>
	</div>
</section>

<!-- 5. Personalized demo conversion -->
<section class="jcp-section jpd-convert" id="jpd-optin" data-jpd-optin aria-labelledby="jpd-optin-title">
	<div class="jcp-container">
		<div class="jpd-convert__grid">
			<div class="jpd-convert__copy">
				<p class="jpd-eyebrow"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="jpd-optin-title" class="jcp-section-headline"><?php esc_html_e( 'See what JCP would do with jobs like yours.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'Work email + trade. About 60 seconds. No phone number. No credit card.', 'jcp-core' ); ?></p>
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
					<button type="submit" class="btn btn-primary survey-btn" id="jpdOptinSubmit"><?php esc_html_e( 'Show me my personalized demo →', 'jcp-core' ); ?></button>
					<p class="survey-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free · About 60 seconds · No phone number', 'jcp-core' ); ?></p>
					<p class="survey-legal"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- 6. Founder + testimonials -->
<section class="jcp-section jpd-trust" id="why-jcp" aria-labelledby="jpd-founder-title">
	<div class="jcp-container">
		<div class="jpd-trust__grid">
			<div class="jpd-trust__founder">
				<img class="jpd-trust__photo" src="<?php echo esc_url( $founder_thumb ); ?>" alt="<?php esc_attr_e( 'Home-service operator on a job site', 'jcp-core' ); ?>" width="640" height="400" loading="lazy" decoding="async" />
				<p class="jpd-eyebrow"><?php esc_html_e( 'Why we built JCP', 'jcp-core' ); ?></p>
				<h2 id="jpd-founder-title" class="jcp-section-headline"><?php esc_html_e( 'Contractor marketing has a proof problem.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'For 10 years, we’ve helped home-service companies generate leads — and watched great proof die in camera rolls.', 'jcp-core' ); ?></p>
				<p class="jpd-punch"><?php esc_html_e( 'JCP was built to fix that.', 'jcp-core' ); ?></p>
				<p class="jpd-trust__mini-stats"><strong>10</strong> years · <strong>250K+</strong> leads · <strong>$150M+</strong> booked</p>
			</div>

			<div class="jpd-trust__reviews">
				<h3 class="jpd-trust__reviews-title"><?php esc_html_e( 'Real contractors. Real jobs.', 'jcp-core' ); ?></h3>
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
	</div>
</section>

<!-- 7. Final CTA -->
<section class="jcp-section rankings-section jcp-niche-final jpd-final-cta">
	<div class="jcp-container">
		<div class="rankings-cta jpd-final-cta__band">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'You already did the expensive part.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'Don’t let the proof die in a camera roll.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="final" data-jpd-source="final"><?php echo esc_html( $cta_primary ); ?> →</a>
				<p class="cta-note"><?php esc_html_e( 'Work email + trade only · No credit card', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="final"><?php esc_html_e( 'Already sold? Start free trial →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<!-- Exit intent (unchanged semantics) -->
<div class="jcp-case-exit jpd-exit" id="jpdExitRoot" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="jpdExitTitle">
	<div class="jcp-case-exit__backdrop" data-jpd-exit-dismiss></div>
	<div class="jcp-case-exit__card">
		<button type="button" class="jcp-case-exit__close" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>" data-jpd-exit-dismiss>×</button>
		<div class="jpd-exit__panel" data-jpd-exit-panel="optin" hidden>
			<p class="jcp-case-exit__wait"><?php esc_html_e( 'BEFORE YOU GO', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title" id="jpdExitTitle"><?php esc_html_e( 'Before you bail — want the 60-second version for your trade?', 'jcp-core' ); ?></h2>
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
			<h2 class="jcp-case-exit__title"><?php esc_html_e( 'Talk to a JCP expert — or see if you qualify for the 90-day case study.', 'jcp-core' ); ?></h2>
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
