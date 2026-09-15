<?php
/**
 * Job Proof Demo LP — $1M-caliber paid landing page.
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
$integrations = [ 'HouseCall Pro', 'CompanyCam', 'Workiz', 'QuickBooks' ];
$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
$hvac_photo      = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$founder_thumb   = $campaign . 'jcp-campaign-face-owner-640.webp';
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

<!-- 1. Hero -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split jcp-layout-align-left jcp-hero-has-visual jpd-hero" id="proof" aria-labelledby="jpd-hero-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout jpd-hero__grid">
			<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'You already have the proof.', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Your crew does the work. JCP turns the proof into marketing.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><?php esc_html_e( 'Real jobs. Real photos. Real locations.', 'jcp-core' ); ?></p>
				<p class="jpd-hero-body"><?php esc_html_e( 'JobCapturePro turns finished work into fresh website proof, Google activity, social content, review opportunities and JCP Directory proof — automatically across connected channels.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<div class="jcp-hero-primary-cta">
						<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="hero" data-jpd-source="hero">
							<span class="jcp-hero-cta-label"><?php echo esc_html( $cta_primary ); ?> →</span>
							<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Work email + trade only · About 60 seconds · No credit card', 'jcp-core' ); ?></span>
						</a>
					</div>
					<p class="jpd-hero-secondary">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Already sold? Start free trial →', 'jcp-core' ); ?></a>
					</p>
				</div>
				<p class="jpd-personality"><?php esc_html_e( 'Your tech can go back to fixing things. JCP handles the marketing part.', 'jcp-core' ); ?></p>
			</div>

			<div class="jcp-hero-visual-column jcp-split-col jcp-split-col--media" aria-hidden="true">
				<div class="jpd-hero-canvas" data-jpd-hero-canvas>
					<article class="jpd-hero-job">
						<div class="jpd-hero-job__media">
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="640" height="420" decoding="async" data-no-lazy data-fallback="<?php echo esc_url( $photo_fallback ); ?>" />
							<span class="jpd-canvas__badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
						</div>
						<div class="jpd-hero-job__meta">
							<strong><?php echo esc_html( $default_service ); ?></strong>
							<span><?php echo esc_html( $default_city ); ?></span>
						</div>
					</article>

					<div class="jpd-hero-bridge">
						<span class="jpd-hero-bridge__logo"><?php esc_html_e( 'JCP', 'jcp-core' ); ?></span>
						<span class="jpd-hero-bridge__line"></span>
					</div>

					<div class="jpd-hero-outs">
						<div class="jpd-hero-out jpd-hero-out--web">
							<div class="jpd-hero-out__map">
								<img src="<?php echo esc_url( $map_url ); ?>" alt="" width="280" height="120" loading="lazy" decoding="async" />
								<span class="jpd-plugin__pin jpd-plugin__pin--active" style="left:42%;top:48%;"></span>
								<span class="jpd-plugin__pin" style="left:30%;top:40%;"></span>
								<span class="jpd-plugin__pin" style="left:52%;top:55%;"></span>
							</div>
							<div class="jpd-hero-out__row">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="48" height="36" loading="lazy" />
								<span><?php esc_html_e( 'Website', 'jcp-core' ); ?></span>
							</div>
						</div>
						<div class="jpd-hero-out jpd-hero-out--gbp">
							<p class="jpd-hero-out__kicker"><?php esc_html_e( 'Google', 'jcp-core' ); ?></p>
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="200" height="90" loading="lazy" />
							<strong><?php esc_html_e( 'Fresh job update', 'jcp-core' ); ?></strong>
						</div>
						<div class="jpd-hero-out jpd-hero-out--social">
							<div class="jpd-hero-out__social-head">
								<span class="jpd-social-avatar" aria-hidden="true">YB</span>
								<em><?php esc_html_e( 'Just now', 'jcp-core' ); ?></em>
							</div>
							<img src="<?php echo esc_url( $hvac_photo ); ?>" alt="" width="200" height="90" loading="lazy" />
						</div>
						<div class="jpd-hero-out jpd-hero-out--review">
							<?php if ( $icon( 'qr-code' ) ) : ?>
								<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="44" height="44" />
							<?php endif; ?>
							<span><?php esc_html_e( 'Ask while fresh', 'jcp-core' ); ?></span>
						</div>
						<div class="jpd-hero-out jpd-hero-out--dir directory-card">
							<span class="directory-badge verified"><?php esc_html_e( 'Verified', 'jcp-core' ); ?></span>
							<div class="card-header">
								<div class="company-avatar">YB</div>
								<strong class="card-name"><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></strong>
							</div>
							<span class="jpd-hero-out__dir-job"><?php echo esc_html( $default_service ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. Compact credibility -->
<section class="jpd-cred-strip" id="jpd-authority" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
	<div class="jcp-container jpd-cred-strip__inner">
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
</section>

<!-- 3. Workflow + problem (merged) -->
<section class="jcp-section jpd-workflow" id="workflow" aria-labelledby="jpd-workflow-title">
	<div class="jcp-container">
		<div class="jpd-workflow__grid">
			<div class="jpd-workflow__copy">
				<p class="jpd-eyebrow"><?php esc_html_e( 'No new marketing job for the crew', 'jcp-core' ); ?></p>
				<h2 id="jpd-workflow-title" class="jcp-section-headline"><?php esc_html_e( 'Your tech has a job. “Marketing assistant” isn’t it.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'If your team already takes job photos, you already have the raw material.', 'jcp-core' ); ?></p>
				<p><?php esc_html_e( 'They can capture with JCP — or keep using supported workflows they already know.', 'jcp-core' ); ?></p>
				<p class="jpd-punch"><?php esc_html_e( 'JCP handles what happens after the photo.', 'jcp-core' ); ?></p>
				<ul class="jpd-workflow__integrations" aria-label="<?php esc_attr_e( 'Supported integrations', 'jcp-core' ); ?>">
					<?php foreach ( $integrations as $name ) : ?>
						<li><?php echo esc_html( $name ); ?></li>
					<?php endforeach; ?>
				</ul>
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

<!-- 4. One-job transformation -->
<section class="jcp-section jpd-transform" id="ai-transform" aria-labelledby="jpd-ai-title" data-jpd-transform>
	<div class="jcp-container">
		<header class="jpd-transform__head">
			<p class="jpd-eyebrow"><?php esc_html_e( 'One job. Way more useful.', 'jcp-core' ); ?></p>
			<h2 id="jpd-ai-title" class="jcp-section-headline"><?php esc_html_e( 'See what one finished job becomes.', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Normally the job gets finished, the photo gets saved… and that’s where the marketing strategy ends.', 'jcp-core' ); ?></p>
			<p class="jpd-punch"><?php esc_html_e( 'JCP changes that.', 'jcp-core' ); ?></p>
		</header>

		<div class="jpd-xform" data-jpd-xform>
			<article class="jpd-xform__job">
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="480" height="320" loading="lazy" decoding="async" data-fallback="<?php echo esc_url( $photo_fallback ); ?>" />
				<span class="jpd-canvas__badge"><?php esc_html_e( 'Completed', 'jcp-core' ); ?></span>
				<div class="jpd-xform__job-meta">
					<strong><?php echo esc_html( $default_service ); ?></strong>
					<span><?php echo esc_html( $default_city ); ?></span>
				</div>
			</article>

			<div class="jpd-xform__core">
				<p class="jpd-xform__core-title"><?php esc_html_e( 'JobCapturePro', 'jcp-core' ); ?></p>
				<ul>
					<li data-xform-step="1"><?php esc_html_e( 'Creating check-in', 'jcp-core' ); ?></li>
					<li data-xform-step="2"><?php esc_html_e( 'Adding service context', 'jcp-core' ); ?></li>
					<li data-xform-step="3"><?php esc_html_e( 'Adding location context', 'jcp-core' ); ?></li>
					<li data-xform-step="4"><?php esc_html_e( 'Publishing connected outputs', 'jcp-core' ); ?></li>
				</ul>
			</div>

			<div class="jpd-xform__outs">
				<div class="jpd-xform__out jcp-sm-browser">
					<div class="jcp-sm-browser__chrome"><span></span><span></span><span></span><em>yoursite.com/jobs</em></div>
					<div class="jpd-xform__map">
						<img src="<?php echo esc_url( $map_url ); ?>" alt="" width="360" height="140" loading="lazy" />
						<span class="jpd-plugin__pin jpd-plugin__pin--active" style="left:44%;top:46%;"></span>
					</div>
					<div class="jpd-xform__checkin">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="64" height="48" loading="lazy" />
						<div>
							<strong><?php echo esc_html( $default_service ); ?></strong>
							<span><?php echo esc_html( $default_city ); ?></span>
						</div>
					</div>
				</div>
				<div class="jpd-xform__out jcp-sm-gbp">
					<p class="jcp-sm-gbp__brand"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
					<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="280" height="120" loading="lazy" />
					<strong><?php esc_html_e( 'Just finished another water heater replacement in Austin', 'jcp-core' ); ?></strong>
				</div>
				<div class="jpd-xform__out jcp-sm-social">
					<div class="jcp-sm-social__head">
						<span class="jpd-social-avatar" aria-hidden="true">YB</span>
						<strong><?php esc_html_e( 'Your business', 'jcp-core' ); ?></strong>
					</div>
					<p><?php esc_html_e( 'Another job wrapped — proof from the field.', 'jcp-core' ); ?></p>
					<img class="jcp-sm-social__photo" src="<?php echo esc_url( $photo_url ); ?>" alt="" width="280" height="120" loading="lazy" />
				</div>
				<div class="jpd-xform__out jpd-xform__out--review">
					<?php if ( $icon( 'qr-code' ) ) : ?>
						<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="56" height="56" />
					<?php endif; ?>
					<span><?php esc_html_e( 'Review ask while it’s fresh', 'jcp-core' ); ?></span>
				</div>
				<div class="jpd-xform__out directory-card jpd-xform__out--dir">
					<span class="directory-badge verified"><?php esc_html_e( 'Verified', 'jcp-core' ); ?></span>
					<div class="card-header">
						<div class="company-avatar">YB</div>
						<strong class="card-name"><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></strong>
					</div>
					<div class="jpd-directory-latest">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="56" height="42" loading="lazy" />
						<strong><?php echo esc_html( $default_service ); ?></strong>
					</div>
				</div>
			</div>
		</div>

		<p class="jpd-transform-line"><?php esc_html_e( 'Same job. Five places working harder for you.', 'jcp-core' ); ?></p>
		<p class="jpd-transform-sub"><?php esc_html_e( 'The work your crew already did becomes fresh proof across the places customers check before they call.', 'jcp-core' ); ?></p>

		<ul class="jpd-chips" aria-label="<?php esc_attr_e( 'What this creates', 'jcp-core' ); ?>">
			<li><strong><?php esc_html_e( 'Freshness', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Recent completed work', 'jcp-core' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Local relevance', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Real service + location', 'jcp-core' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Trust', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Evidence you do the work', 'jcp-core' ); ?></span></li>
		</ul>

		<p class="jpd-section-cta">
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="transform" data-jpd-source="transform"><?php esc_html_e( 'See what this looks like for my trade →', 'jcp-core' ); ?></a>
		</p>
	</div>
</section>

<!-- 5. Personalized demo conversion -->
<section class="jcp-section jpd-convert" id="jpd-optin" data-jpd-optin aria-labelledby="jpd-optin-title">
	<div class="jcp-container">
		<div class="jpd-convert__grid">
			<div class="jpd-convert__copy">
				<p class="jpd-eyebrow"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="jpd-optin-title" class="jcp-section-headline"><?php esc_html_e( 'See what JCP would do with the jobs your business already completes.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'Pick your trade and we’ll show you a real example built around the kind of work your crew does every week.', 'jcp-core' ); ?></p>
				<ul class="jpd-convert__checks">
					<li><?php esc_html_e( 'About 60 seconds', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No phone number required', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No credit card', 'jcp-core' ); ?></li>
				</ul>
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
				<img class="jpd-trust__photo" src="<?php echo esc_url( $founder_thumb ); ?>" alt="<?php esc_attr_e( 'Why we built JobCapturePro', 'jcp-core' ); ?>" width="640" height="400" loading="lazy" decoding="async" />
				<p class="jpd-eyebrow"><?php esc_html_e( 'Why we built JCP', 'jcp-core' ); ?></p>
				<h2 id="jpd-founder-title" class="jcp-section-headline"><?php esc_html_e( 'Contractor marketing has a proof problem.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'For 10 years, we’ve helped home-service companies generate leads.', 'jcp-core' ); ?></p>
				<p><?php esc_html_e( 'And we kept seeing the same ridiculous thing: contractors would do great work all week… then leave the best proof of it sitting in someone’s phone.', 'jcp-core' ); ?></p>
				<p class="jpd-punch"><?php esc_html_e( 'JCP was built to fix that.', 'jcp-core' ); ?></p>
				<p class="jpd-trust__mini-stats"><strong>10</strong> years · <strong>250K+</strong> leads · <strong>$150M+</strong> booked</p>
			</div>

			<div class="jpd-trust__reviews">
				<h3 class="jpd-trust__reviews-title"><?php esc_html_e( 'Real contractors. Real jobs. No marketing fairy dust.', 'jcp-core' ); ?></h3>
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
				<p class="cta-paragraph"><?php esc_html_e( 'You paid for the truck, the tech, the tools and the job. Don’t let the proof die in a camera roll.', 'jcp-core' ); ?></p>
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
