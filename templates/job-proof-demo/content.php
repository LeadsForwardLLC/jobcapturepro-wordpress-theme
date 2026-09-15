<?php
/**
 * Job Proof Demo LP — CRO-refined paid funnel shell.
 *
 * Structure: Hero → Credibility → Workflow → Transformation → Opt-in → Trust → Final CTA
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

$campaign = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$reviews  = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
$business_type_options = function_exists( 'jcp_core_business_type_flat_options' )
	? jcp_core_business_type_flat_options()
	: [];
/* Live product FAQ (home.js): HouseCall Pro, CompanyCam, Workiz, QuickBooks only. */
$integrations = [ 'HouseCall Pro', 'CompanyCam', 'Workiz', 'QuickBooks' ];
$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
$hvac_photo      = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$founder_thumb   = $campaign . 'jcp-campaign-face-owner-640.webp';
$why_href        = home_url( '/why-we-built-jcp/' );
$cta_primary     = __( 'See my free personalized demo', 'jcp-core' );
?>

<!-- 1. Hero -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split jcp-layout-align-left jcp-hero-has-visual jpd-hero" id="proof" aria-labelledby="jpd-hero-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout">
			<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'You already paid for the proof.', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Your crew already creates the proof. JobCapturePro puts it to work.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><?php esc_html_e( 'Real jobs. Real photos. Real locations.', 'jcp-core' ); ?></p>
				<p class="jpd-hero-body"><?php esc_html_e( 'JCP turns finished work into website proof, Google activity, social content, review opportunities and JCP Directory proof — without turning your techs into marketers.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<div class="jcp-hero-primary-cta">
						<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="hero" data-jpd-source="hero_primary">
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

			<div class="jcp-hero-visual-column jcp-split-col jcp-split-col--media" id="jpd-canvas" data-jpd-canvas>
				<div class="jpd-stage" data-jpd-canvas-stage="idle" aria-live="polite">
					<div class="jpd-stage__compose">
						<svg class="jpd-stage__wires" viewBox="0 0 640 420" fill="none" aria-hidden="true" focusable="false">
							<path class="jpd-wire" d="M290 210 C360 210 390 70 470 70" />
							<path class="jpd-wire" d="M290 210 C360 210 390 140 470 140" />
							<path class="jpd-wire" d="M290 210 C360 210 390 210 470 210" />
							<path class="jpd-wire" d="M290 210 C360 210 390 280 470 280" />
							<path class="jpd-wire" d="M290 210 C360 210 390 350 470 350" />
						</svg>

						<article class="jpd-stage__hub">
							<div class="jpd-stage__hub-media">
								<img
									src="<?php echo esc_url( $photo_url ); ?>"
									alt="<?php esc_attr_e( 'Completed water heater replacement', 'jcp-core' ); ?>"
									width="640"
									height="420"
									decoding="async"
									data-no-lazy
									data-jpd-job-photo
									data-fallback="<?php echo esc_url( $photo_fallback ); ?>"
								/>
								<span class="jpd-canvas__badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
							</div>
							<div class="jpd-stage__hub-meta">
								<p class="jpd-stage__hub-label"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></p>
								<strong data-jpd-job-title><?php echo esc_html( $default_service ); ?></strong>
								<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
							</div>
						</article>

						<div class="jpd-stage__core" data-jpd-ai-panel>
							<p class="jpd-stage__core-title"><?php esc_html_e( 'JCP', 'jcp-core' ); ?></p>
							<ul class="jpd-stage__checklist">
								<li class="jpd-canvas__step" data-step="photo"><?php esc_html_e( 'Photo in', 'jcp-core' ); ?></li>
								<li class="jpd-canvas__step" data-step="context"><?php esc_html_e( 'Job context', 'jcp-core' ); ?></li>
								<li class="jpd-canvas__step" data-step="ai"><?php esc_html_e( 'Check-in ready', 'jcp-core' ); ?></li>
								<li class="jpd-canvas__step" data-step="publish"><?php esc_html_e( 'Publishing', 'jcp-core' ); ?></li>
							</ul>
						</div>

						<ul class="jpd-stage__outs" data-jpd-destinations>
							<li data-dest="website" class="jpd-out">
								<div class="jpd-out__chrome" aria-hidden="true"><span></span><span></span><span></span></div>
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="160" height="90" loading="lazy" decoding="async" data-jpd-job-photo />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong>
									<span data-jpd-job-title><?php echo esc_html( $default_service ); ?></span>
								</div>
							</li>
							<li data-dest="google" class="jpd-out jpd-out--gbp">
								<p class="jpd-out__kicker"><?php esc_html_e( 'Google', 'jcp-core' ); ?></p>
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="160" height="72" loading="lazy" decoding="async" data-jpd-job-photo />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Fresh job update', 'jcp-core' ); ?></span>
								</div>
							</li>
							<li data-dest="social" class="jpd-out jpd-out--social">
								<img src="<?php echo esc_url( $hvac_photo ); ?>" alt="" width="160" height="90" loading="lazy" decoding="async" />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Field proof post', 'jcp-core' ); ?></span>
								</div>
							</li>
							<li data-dest="reviews" class="jpd-out jpd-out--reviews">
								<div class="jpd-out__qr" aria-hidden="true">
									<?php if ( $icon( 'qr-code' ) ) : ?>
										<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="40" height="40" />
									<?php endif; ?>
								</div>
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Ask while it’s fresh', 'jcp-core' ); ?></span>
								</div>
							</li>
							<li data-dest="directory" class="jpd-out jpd-out--dir">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="56" height="56" loading="lazy" decoding="async" data-jpd-job-photo />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Directory', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Public job history', 'jcp-core' ); ?></span>
								</div>
							</li>
						</ul>
					</div>

					<div class="jpd-stage__controls" data-jpd-canvas-idle>
						<button type="button" class="btn btn-primary jpd-stage__primary" data-jpd-use-sample>
							<?php esc_html_e( 'Watch one job become proof →', 'jcp-core' ); ?>
						</button>
					</div>

					<div class="jpd-stage__controls jpd-stage__controls--run" data-jpd-canvas-run hidden>
						<p class="jpd-canvas__status" id="jpdCanvasStatus"><?php esc_html_e( 'Job photo received…', 'jcp-core' ); ?></p>
						<div class="jpd-canvas__payoff" data-jpd-payoff hidden>
							<p class="jpd-canvas__payoff-line"><?php esc_html_e( 'Same job. Five places working harder for you.', 'jcp-core' ); ?></p>
							<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="hero_payoff" data-jpd-source="hero_payoff"><?php echo esc_html( $cta_primary ); ?> →</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. Compact credibility strip -->
<section class="jpd-cred-strip" id="jpd-authority" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
	<div class="jcp-container jpd-cred-strip__inner">
		<p class="jpd-cred-strip__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
		<ul class="jpd-cred-strip__stats">
			<li>
				<strong class="jcp-count-up" data-count-to="10" data-count-prefix="" data-count-suffix="" data-count-format="plain" data-count-decimals="0" data-count-ms="1400">0</strong>
				<span><?php esc_html_e( 'years helping contractors grow', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong class="jcp-count-up" data-count-to="250" data-count-prefix="" data-count-suffix="K+" data-count-format="plain" data-count-decimals="0" data-count-ms="1400">0</strong>
				<span><?php esc_html_e( 'leads generated', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong class="jcp-count-up" data-count-to="150" data-count-prefix="$" data-count-suffix="M+" data-count-format="plain" data-count-decimals="0" data-count-ms="1400">0</strong>
				<span><?php esc_html_e( 'revenue booked from those leads', 'jcp-core' ); ?></span>
			</li>
		</ul>
	</div>
</section>

<!-- 3. Workflow + integrations (merged) -->
<section class="jcp-section jpd-workflow" id="workflow" aria-labelledby="jpd-workflow-title">
	<div class="jcp-container">
		<div class="jpd-workflow__grid">
			<div class="jpd-workflow__copy">
				<h2 id="jpd-workflow-title" class="jcp-section-headline"><?php esc_html_e( 'Your tech has a job. “Marketing assistant” isn’t it.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'If your crew already takes job photos, you already have the raw material.', 'jcp-core' ); ?></p>
				<p><?php esc_html_e( 'They can capture with JCP — or, with supported integrations, keep using the workflow they already know.', 'jcp-core' ); ?></p>
				<p class="jpd-workflow__punch"><?php esc_html_e( 'JCP takes it from there.', 'jcp-core' ); ?></p>
				<ul class="jpd-workflow__integrations" aria-label="<?php esc_attr_e( 'Supported integrations', 'jcp-core' ); ?>">
					<?php foreach ( $integrations as $name ) : ?>
						<li><?php echo esc_html( $name ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="jpd-workflow__note"><?php esc_html_e( 'Works with supported CRM and photo workflows.', 'jcp-core' ); ?></p>
			</div>
			<div class="jpd-workflow__visual" aria-hidden="true">
				<div class="jpd-flow">
					<div class="jpd-flow__node jpd-flow__node--in">
						<span><?php esc_html_e( 'Crew photos', 'jcp-core' ); ?></span>
						<small><?php esc_html_e( 'JCP app or CRM / photo workflow', 'jcp-core' ); ?></small>
					</div>
					<span class="jpd-flow__arrow">→</span>
					<div class="jpd-flow__node jpd-flow__node--jcp">
						<span><?php esc_html_e( 'JCP', 'jcp-core' ); ?></span>
					</div>
					<span class="jpd-flow__arrow">→</span>
					<ul class="jpd-flow__outs">
						<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 4. One-job transformation -->
<section class="jcp-section jpd-ai jpd-band jpd-band--tint" id="ai-transform" aria-labelledby="jpd-ai-title">
	<div class="jcp-container">
		<header class="jpd-section-head">
			<h2 id="jpd-ai-title" class="jcp-section-headline"><?php esc_html_e( 'One job. Way more useful than one photo.', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Normally the job gets finished, the photo gets saved… and that’s the end of the marketing strategy.', 'jcp-core' ); ?></p>
			<p class="jpd-workflow__punch"><?php esc_html_e( 'JCP changes that.', 'jcp-core' ); ?></p>
		</header>
		<div class="jpd-ai__story">
			<div class="jpd-ai__node jpd-ai__node--in">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'One real job', 'jcp-core' ); ?></p>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="320" height="220" loading="lazy" decoding="async" data-jpd-job-photo />
			</div>
			<div class="jpd-ai__arrow" aria-hidden="true">
				<svg viewBox="0 0 80 24" width="80" height="24"><path d="M2 12h68M58 4l12 8-12 8" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div class="jpd-ai__node jpd-ai__node--jcp">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'JCP check-in', 'jcp-core' ); ?></p>
				<ul class="jpd-ai__checks">
					<li><?php esc_html_e( 'Service + location', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'AI description', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Ready to publish', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="jpd-ai__arrow" aria-hidden="true">
				<svg viewBox="0 0 80 24" width="80" height="24"><path d="M2 12h68M58 4l12 8-12 8" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div class="jpd-ai__node jpd-ai__node--out">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'Working for you', 'jcp-core' ); ?></p>
				<ul class="jpd-ai__channels">
					<li><?php esc_html_e( 'Website proof', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google activity', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social content', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Review opportunity', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'JCP Directory proof', 'jcp-core' ); ?></li>
				</ul>
			</div>
		</div>
		<p class="jpd-transform-line"><?php esc_html_e( 'Same job. Five places working harder for you.', 'jcp-core' ); ?></p>
		<p class="jpd-section-cta">
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="DemoCTA" data-jpd-section="transform" data-jpd-source="transform"><?php esc_html_e( 'See what this looks like for my trade →', 'jcp-core' ); ?></a>
		</p>
	</div>
</section>

<!-- 5. Opt-in -->
<section class="jcp-section jpd-optin" id="jpd-optin" data-jpd-optin aria-labelledby="jpd-optin-title">
	<div class="jcp-container">
		<div class="jpd-optin__card survey-step active">
			<header class="survey-head">
				<p class="demo-badge"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="jpd-optin-title" class="survey-title"><?php esc_html_e( 'See what this looks like for your trade.', 'jcp-core' ); ?></h2>
				<p class="survey-subtitle"><?php esc_html_e( 'Tell us your trade and we’ll show you the kind of job your crew actually completes — and what JCP turns it into.', 'jcp-core' ); ?></p>
			</header>

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
				<p class="survey-legal"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
			</form>
		</div>
	</div>
</section>

<!-- 6. Founder + testimonials -->
<section class="jcp-section jpd-trust" id="why-jcp" aria-labelledby="jpd-founder-title">
	<div class="jcp-container">
		<div class="jpd-trust__founder">
			<a class="jpd-founder__thumb" href="<?php echo esc_url( $why_href ); ?>">
				<img src="<?php echo esc_url( $founder_thumb ); ?>" alt="<?php esc_attr_e( 'Why we built JobCapturePro', 'jcp-core' ); ?>" width="640" height="400" loading="lazy" decoding="async" />
			</a>
			<div class="jpd-founder__copy">
				<h2 id="jpd-founder-title" class="jcp-section-headline"><?php esc_html_e( 'We built JCP because contractor marketing has a proof problem.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'For 10 years, we’ve helped home-service companies generate leads.', 'jcp-core' ); ?></p>
				<p><?php esc_html_e( 'And we kept seeing the same ridiculous thing:', 'jcp-core' ); ?></p>
				<p><?php esc_html_e( 'Contractors would do great work all week… then leave the best proof of it sitting in someone’s phone.', 'jcp-core' ); ?></p>
				<p class="jpd-workflow__punch"><?php esc_html_e( 'JCP was built to fix that.', 'jcp-core' ); ?></p>
				<p class="jpd-founder__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
			</div>
		</div>

		<?php if ( $reviews !== [] ) : ?>
			<div class="jpd-trust__reviews">
				<h3 class="jpd-trust__reviews-title"><?php esc_html_e( 'Real contractors. Real jobs. No marketing fairy dust.', 'jcp-core' ); ?></h3>
				<div class="jpd-quote-grid">
					<?php foreach ( array_slice( $reviews, 0, 4 ) as $r ) : ?>
						<blockquote class="jpd-quote">
							<p><?php echo esc_html( (string) ( $r['quote'] ?? '' ) ); ?></p>
							<footer>
								<strong><?php echo esc_html( (string) ( $r['name'] ?? '' ) ); ?></strong>
								<span><?php echo esc_html( (string) ( $r['role'] ?? '' ) ); ?></span>
							</footer>
						</blockquote>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- 7. Final CTA -->
<section class="jcp-section jcp-niche-final jpd-final-cta">
	<div class="jcp-container">
		<div class="rankings-cta">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'You already did the expensive part.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'You paid for the truck, the tech, the tools and the job. Don’t let the proof die in a camera roll.', 'jcp-core' ); ?></p>
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

<!-- Exit intent -->
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
			<h2 class="jcp-case-exit__title" id="jpdExitCaseTitle"><?php esc_html_e( 'Talk to a JCP expert — or see if you qualify for the 90-day case study.', 'jcp-core' ); ?></h2>
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
