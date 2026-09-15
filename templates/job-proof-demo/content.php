<?php
/**
 * Job Proof Demo LP — product-led campaign shell.
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
$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
$capture_photo   = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$founder_thumb   = $campaign . 'jcp-campaign-face-owner-640.webp';
$why_href        = home_url( '/why-we-built-jcp/' );
?>

<!-- 1. Hero: Give JCP a job -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split jcp-layout-align-left jcp-hero-has-visual jpd-hero" id="proof" aria-labelledby="jpd-hero-title">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout">
			<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'The job is done. Now put the proof to work.', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Turn one finished job into marketing everywhere customers look.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><?php esc_html_e( 'Your crew already takes the photos. JobCapturePro turns real completed work into fresh website content, Google activity, social proof, review opportunities and JCP Directory proof — automatically across connected channels.', 'jcp-core' ); ?></p>
				<p class="jpd-hero-line"><?php esc_html_e( 'Your tech does the job. JCP does the marketing.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<div class="jcp-hero-primary-cta">
						<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-canvas" data-jpd-scroll-canvas>
							<span class="jcp-hero-cta-label"><?php esc_html_e( 'Show me with a real job →', 'jcp-core' ); ?></span>
							<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free personalized demo · About 60 seconds · No phone call required', 'jcp-core' ); ?></span>
						</a>
					</div>
					<p class="jpd-hero-secondary">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip"><?php esc_html_e( 'Start free trial →', 'jcp-core' ); ?></a>
					</p>
				</div>
				<p class="jpd-trust-strip" role="note">
					<span><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></span>
					<span aria-hidden="true">·</span>
					<span><?php esc_html_e( '10 years · 250K+ leads · $150M+ booked', 'jcp-core' ); ?></span>
				</p>
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
									data-fallback="<?php echo esc_url( $photo_fallback ); ?>"
								/>
								<span class="jpd-canvas__badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
							</div>
							<div class="jpd-stage__hub-meta">
								<p class="jpd-stage__hub-label"><?php esc_html_e( 'Finished job photo', 'jcp-core' ); ?></p>
								<strong data-jpd-job-title><?php echo esc_html( $default_service ); ?></strong>
								<span data-jpd-job-city><?php echo esc_html( $default_city ); ?></span>
							</div>
						</article>

						<div class="jpd-stage__core" data-jpd-ai-panel>
							<p class="jpd-stage__core-title"><?php esc_html_e( 'JobCapturePro', 'jcp-core' ); ?></p>
							<ul class="jpd-stage__checklist">
								<li class="jpd-canvas__step" data-step="photo"><?php esc_html_e( 'Job photo received', 'jcp-core' ); ?></li>
								<li class="jpd-canvas__step" data-step="context"><?php esc_html_e( 'Service + location attached', 'jcp-core' ); ?></li>
								<li class="jpd-canvas__step" data-step="ai"><?php esc_html_e( 'AI description written', 'jcp-core' ); ?></li>
								<li class="jpd-canvas__step" data-step="publish"><?php esc_html_e( 'Publishing destinations', 'jcp-core' ); ?></li>
							</ul>
						</div>

						<ul class="jpd-stage__outs" data-jpd-destinations>
							<li data-dest="website" class="jpd-out">
								<div class="jpd-out__chrome" aria-hidden="true"><span></span><span></span><span></span></div>
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="160" height="90" loading="lazy" decoding="async" />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong>
									<span data-jpd-job-title><?php echo esc_html( $default_service ); ?></span>
								</div>
							</li>
							<li data-dest="google" class="jpd-out jpd-out--gbp">
								<p class="jpd-out__kicker"><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></p>
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="160" height="72" loading="lazy" decoding="async" />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Fresh job update', 'jcp-core' ); ?></span>
								</div>
							</li>
							<li data-dest="social" class="jpd-out jpd-out--social">
								<img src="<?php echo esc_url( $capture_photo ); ?>" alt="" width="160" height="90" loading="lazy" decoding="async" />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Field proof post', 'jcp-core' ); ?></span>
								</div>
							</li>
							<li data-dest="reviews" class="jpd-out jpd-out--reviews">
								<div class="jpd-out__qr" aria-hidden="true">
									<?php if ( $icon( 'qr-code' ) ) : ?>
										<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="40" height="40" />
									<?php else : ?>
										<svg width="40" height="40" viewBox="0 0 40 40" aria-hidden="true"><rect x="4" y="4" width="12" height="12" fill="currentColor"/><rect x="24" y="4" width="12" height="12" fill="currentColor"/><rect x="4" y="24" width="12" height="12" fill="currentColor"/><rect x="22" y="22" width="6" height="6" fill="currentColor"/><rect x="30" y="30" width="6" height="6" fill="currentColor"/></svg>
									<?php endif; ?>
								</div>
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Ask while it’s fresh', 'jcp-core' ); ?></span>
								</div>
							</li>
							<li data-dest="directory" class="jpd-out jpd-out--dir">
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="56" height="56" loading="lazy" decoding="async" />
								<div class="jpd-out__body">
									<strong><?php esc_html_e( 'JCP Directory', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Public job history', 'jcp-core' ); ?></span>
								</div>
							</li>
						</ul>
					</div>

					<div class="jpd-stage__controls" data-jpd-canvas-idle>
						<button type="button" class="btn btn-primary jpd-stage__primary" data-jpd-use-sample>
							<?php esc_html_e( 'Use this sample job →', 'jcp-core' ); ?>
						</button>
						<p class="jpd-canvas__note"><?php esc_html_e( 'Personalize the full demo to your trade in the next step.', 'jcp-core' ); ?></p>
					</div>

					<div class="jpd-stage__controls jpd-stage__controls--run" data-jpd-canvas-run hidden>
						<p class="jpd-canvas__status" id="jpdCanvasStatus"><?php esc_html_e( 'Job photo received…', 'jcp-core' ); ?></p>
						<div class="jpd-canvas__payoff" data-jpd-payoff hidden>
							<p class="jpd-canvas__payoff-line"><?php esc_html_e( 'Your crew took one photo.', 'jcp-core' ); ?><br /><?php esc_html_e( 'JCP just turned it into a marketing system.', 'jcp-core' ); ?></p>
							<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin><?php esc_html_e( 'See this for my business →', 'jcp-core' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. Keep current field software -->
<section class="jcp-section rankings-section jpd-integrations jpd-band jpd-band--tint" id="integrations" aria-labelledby="jpd-integrations-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-integrations-title" class="jcp-section-headline"><?php esc_html_e( 'Your crew doesn’t need another marketing job.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'If your team already captures consistent job photos in software you use today, JobCapturePro can work from that existing proof through supported integrations.', 'jcp-core' ); ?></p>
		</div>
		<p class="jpd-integrations__line"><?php esc_html_e( 'Keep the workflow your crew already knows. Let JCP handle what happens after the photo.', 'jcp-core' ); ?></p>
		<p class="jpd-integrations__note"><?php esc_html_e( 'Supported integrations and workflows vary by setup. JobCapturePro works from completed-job proof your team already captures — in the JCP app or through connected field systems when available.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 3. Core differentiator -->
<section class="jcp-section rankings-section jpd-diff" id="differentiator" aria-labelledby="jpd-diff-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-diff-title" class="jcp-section-headline"><?php esc_html_e( 'JCP doesn’t create generic marketing. It markets the work you actually did.', 'jcp-core' ); ?></h2>
		</div>
		<div class="jpd-diff__grid">
			<article class="ranking-factor-card jpd-diff__card jpd-diff__card--generic">
				<p class="jpd-diff__label"><?php esc_html_e( 'Generic contractor marketing', 'jcp-core' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Stock content', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Generic city pages', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Recycled social posts', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Stale Google profile', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Claims anyone can make', 'jcp-core' ); ?></li>
				</ul>
			</article>
			<article class="ranking-factor-card jpd-diff__card jpd-diff__card--real">
				<p class="jpd-diff__label"><?php esc_html_e( 'Real job proof with JCP', 'jcp-core' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Actual completed jobs', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Actual job photos', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Actual service + location context', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Recent activity', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Real review opportunity', 'jcp-core' ); ?></li>
				</ul>
			</article>
		</div>
		<p class="jpd-diff__outcome"><?php esc_html_e( 'Real work creates proof competitors can’t manufacture.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 4. AI transformation -->
<section class="jcp-section rankings-section jpd-ai jpd-band jpd-band--tint" id="ai-transform" aria-labelledby="jpd-ai-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-ai-title" class="jcp-section-headline"><?php esc_html_e( 'A normal job photo becomes useful marketing.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'Turn ordinary job photos into optimized job content — AI-written check-in descriptions with service and location context, ready to publish across connected channels.', 'jcp-core' ); ?></p>
		</div>
		<div class="jpd-ai__story">
			<div class="jpd-ai__node jpd-ai__node--in">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'Input', 'jcp-core' ); ?></p>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="320" height="220" loading="lazy" decoding="async" />
				<ul>
					<li><?php esc_html_e( 'Ordinary job photo', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Trade / service', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Location context', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="jpd-ai__arrow" aria-hidden="true">
				<svg viewBox="0 0 80 24" width="80" height="24"><path d="M2 12h68M58 4l12 8-12 8" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div class="jpd-ai__node jpd-ai__node--jcp">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'JobCapturePro', 'jcp-core' ); ?></p>
				<ul class="jpd-ai__checks">
					<li><?php esc_html_e( 'AI-generated check-in description', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Service + location context', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Optimized publishing content', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="jpd-ai__arrow" aria-hidden="true">
				<svg viewBox="0 0 80 24" width="80" height="24"><path d="M2 12h68M58 4l12 8-12 8" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div class="jpd-ai__node jpd-ai__node--out">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'Output', 'jcp-core' ); ?></p>
				<ul class="jpd-ai__channels">
					<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- Credibility band -->
<section class="jcp-section jpd-credibility" aria-label="<?php esc_attr_e( 'Built by LeadsForward', 'jcp-core' ); ?>">
	<div class="jcp-container">
		<p class="jpd-credibility__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
		<ul class="jpd-credibility__stats">
			<li>
				<strong>10</strong>
				<span><?php esc_html_e( 'years helping contractors grow', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong>250K+</strong>
				<span><?php esc_html_e( 'leads generated', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong>$150M+</strong>
				<span><?php esc_html_e( 'revenue booked from those leads', 'jcp-core' ); ?></span>
			</li>
		</ul>
	</div>
</section>

<!-- 5. Personalized demo opt-in -->
<section class="jcp-section rankings-section jpd-optin" id="jpd-optin" data-jpd-optin aria-labelledby="jpd-optin-title">
	<div class="jcp-container">
		<div class="jpd-optin__card survey-step active">
			<header class="survey-head">
				<p class="demo-badge"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="jpd-optin-title" class="survey-title"><?php esc_html_e( 'See what JCP would do with the jobs your business already completes.', 'jcp-core' ); ?></h2>
				<p class="survey-subtitle"><?php esc_html_e( 'Choose your trade and we’ll show you a real example built around the kind of work your crew does every week.', 'jcp-core' ); ?></p>
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
				<button type="submit" class="btn btn-primary survey-btn" id="jpdOptinSubmit"><?php esc_html_e( 'Build my personalized demo →', 'jcp-core' ); ?></button>
				<p class="survey-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free · About 60 seconds · No phone number · No credit card', 'jcp-core' ); ?></p>
				<p class="survey-legal"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
			</form>
		</div>
	</div>
</section>

<?php
// Directory
if ( function_exists( 'jcp_niche_render_directory_preview' ) ) {
	jcp_niche_render_directory_preview(
		[
			'eyebrow'     => '',
			'headline'    => __( 'Your finished work builds a public footprint in the JCP Directory.', 'jcp-core' ),
			'body'        => __( 'Your completed jobs do more than feed other platforms. They also build a public history of real work inside JobCapturePro — showing the services you actually perform and the areas your business serves. Consistently documenting real work builds a richer, more current public footprint.', 'jcp-core' ),
			'bullets'     => [
				__( 'Public completed-job proof', 'jcp-core' ),
				__( 'Services you actually perform', 'jcp-core' ),
				__( 'Job and service-area context', 'jcp-core' ),
				__( 'Business visibility inside the JCP ecosystem', 'jcp-core' ),
			],
			'section_id'  => 'jpd-directory',
			'show_cta'    => false,
		],
		'job_proof_demo'
	);
}
?>

<!-- Local signals -->
<section class="jcp-section rankings-section jpd-band jpd-band--tint" id="local-signals" aria-labelledby="jpd-seo-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-seo-title" class="jcp-section-headline"><?php esc_html_e( 'Every finished job creates another local signal.', 'jcp-core' ); ?></h2>
		</div>
		<div class="jcp-niche-benefits jcp-niche-benefits--cards">
			<article class="ranking-factor-card">
				<h3 class="jcp-section-subhead"><?php esc_html_e( 'Recency', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Show customers—and connected platforms—that your business is actively completing work.', 'jcp-core' ); ?></p>
			</article>
			<article class="ranking-factor-card">
				<h3 class="jcp-section-subhead"><?php esc_html_e( 'Relevance', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Pair real services with real job/location context.', 'jcp-core' ); ?></p>
			</article>
			<article class="ranking-factor-card">
				<h3 class="jcp-section-subhead"><?php esc_html_e( 'Trust', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Give prospects visible evidence that you actually perform the work you advertise.', 'jcp-core' ); ?></p>
			</article>
		</div>
		<p class="rankings-subtitle jpd-seo-close"><?php esc_html_e( 'JCP helps turn completed work into fresh, location-relevant proof that can support stronger local visibility over time.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- Founder / why we built (no autoplay video asset in theme — thumbnail + link) -->
<section class="jcp-section rankings-section jpd-founder" id="why-jcp" aria-labelledby="jpd-founder-title">
	<div class="jcp-container jpd-founder__grid">
		<a class="jpd-founder__thumb ranking-factor-card" href="<?php echo esc_url( $why_href ); ?>" data-jpd-founder>
			<img src="<?php echo esc_url( $founder_thumb ); ?>" alt="<?php esc_attr_e( 'Why we built JobCapturePro', 'jcp-core' ); ?>" width="640" height="400" loading="lazy" decoding="async" />
			<span class="jpd-founder__play" aria-hidden="true">▶</span>
		</a>
		<div class="jpd-founder__copy">
			<h2 id="jpd-founder-title" class="jcp-section-headline"><?php esc_html_e( 'Why we built JobCapturePro', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'We’ve spent 10 years helping contractors generate leads. We kept seeing the same thing: contractors were doing great work every day, but almost none of that real work was becoming useful marketing. JobCapturePro was built so the jobs you already complete can keep working after the crew leaves.', 'jcp-core' ); ?></p>
			<ul class="jpd-founder__stats">
				<li><strong>10</strong> <?php esc_html_e( 'years helping contractors grow', 'jcp-core' ); ?></li>
				<li><strong>250K+</strong> <?php esc_html_e( 'leads generated', 'jcp-core' ); ?></li>
				<li><strong>$150M+</strong> <?php esc_html_e( 'revenue booked from those leads', 'jcp-core' ); ?></li>
			</ul>
			<p class="jpd-founder__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin><?php esc_html_e( 'See it on my business →', 'jcp-core' ); ?></a>
		</div>
	</div>
</section>

<?php
if ( function_exists( 'jcp_niche_render_testimonials' ) && $reviews !== [] ) {
	$normalized = [];
	foreach ( array_slice( $reviews, 0, 4 ) as $r ) {
		$normalized[] = [
			'quote'  => (string) ( $r['quote'] ?? $r['text'] ?? '' ),
			'name'   => (string) ( $r['name'] ?? '' ),
			'role'   => (string) ( $r['role'] ?? $r['title'] ?? '' ),
			'avatar' => (string) ( $r['avatar'] ?? $r['image'] ?? '' ),
			'stars'  => 5,
		];
	}
	jcp_niche_render_testimonials(
		[
			'headline'         => __( 'What people using JobCapturePro are saying', 'jcp-core' ),
			'show_headline'    => true,
			'section_id'       => 'testimonials',
			'reviews'          => $normalized,
		]
	);
}
?>

<!-- Final CTA -->
<section class="jcp-section rankings-section jcp-niche-final">
	<div class="jcp-container">
		<div class="rankings-cta">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'Stop letting finished jobs disappear into the camera roll.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'Let the next job your crew finishes start building proof for the one after it.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="#jpd-optin" data-jpd-scroll-optin><?php esc_html_e( 'Build my personalized demo →', 'jcp-core' ); ?></a>
				<p class="cta-note"><?php esc_html_e( 'Free · About 60 seconds · No phone call required', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="final"><?php esc_html_e( 'Start free trial →', 'jcp-core' ); ?></a>
				</p>
			</div>
		</div>
	</div>
</section>

<!-- Exit intent State A -->
<div class="jcp-case-exit jpd-exit" id="jpdExitRoot" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="jpdExitTitle">
	<div class="jcp-case-exit__backdrop" data-jpd-exit-dismiss></div>
	<div class="jcp-case-exit__card">
		<button type="button" class="jcp-case-exit__close" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>" data-jpd-exit-dismiss>×</button>
		<div class="jpd-exit__panel" data-jpd-exit-panel="optin" hidden>
			<p class="jcp-case-exit__wait"><?php esc_html_e( 'BEFORE YOU GO', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title" id="jpdExitTitle"><?php esc_html_e( 'Want me to send you your personalized demo?', 'jcp-core' ); ?></h2>
			<p class="jcp-case-exit__body"><?php esc_html_e( 'Enter your work email and trade and we’ll send you the demo so you can come back when you have a minute.', 'jcp-core' ); ?></p>
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
				<button type="submit" class="btn btn-primary jcp-case-exit__primary" id="jpdExitOptinSubmit"><?php esc_html_e( 'Send my demo →', 'jcp-core' ); ?></button>
			</form>
			<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'No thanks — continue browsing', 'jcp-core' ); ?></button>
		</div>
		<div class="jpd-exit__panel" data-jpd-exit-panel="case" hidden>
			<p class="jcp-case-exit__wait jpd-exit__eyebrow"><?php esc_html_e( 'Not ready to start a trial?', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title" id="jpdExitCaseTitle"><?php esc_html_e( 'Want to be considered for the 90-day JobCapturePro case study?', 'jcp-core' ); ?></h2>
			<p class="jcp-case-exit__body"><?php esc_html_e( 'We’re selecting a limited number of qualifying home-service companies to use JobCapturePro as part of our case-study program.', 'jcp-core' ); ?></p>
			<div class="jcp-case-exit__actions">
				<a class="jcp-case-exit__primary" id="jpdExitCaseCta" href="<?php echo esc_url( $case_href ); ?>"><?php esc_html_e( 'See if I qualify →', 'jcp-core' ); ?></a>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Go back', 'jcp-core' ); ?></button>
			</div>
		</div>
	</div>
</div>
