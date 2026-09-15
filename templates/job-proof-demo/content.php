<?php
/**
 * Job Proof Demo LP — conversion-first paid funnel shell.
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
$integrations = function_exists( 'jcp_core_demo_field_software_integrations' )
	? jcp_core_demo_field_software_integrations()
	: [ 'Housecall Pro', 'Jobber', 'ServiceTitan', 'CompanyCam' ];
$default_service = __( 'Water heater replacement', 'jcp-core' );
$default_city    = __( 'Austin, TX', 'jcp-core' );
$capture_photo   = $campaign . 'jcp-campaign-hvac-capture-640.webp';
$founder_thumb   = $campaign . 'jcp-campaign-face-owner-640.webp';
$why_href        = home_url( '/why-we-built-jcp/' );
$cta_primary     = __( 'See my personalized demo', 'jcp-core' );
?>

<!-- 2. Hero -->
<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-split jcp-layout-align-left jcp-hero-has-visual jpd-hero" id="proof" aria-labelledby="jpd-hero-title" data-jpd-track-view="proof_lp_hero">
	<div class="jcp-container">
		<div class="jcp-hero-grid jcp-split-layout">
			<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy">
				<p class="jcp-hero-eyebrow demo-badge"><?php esc_html_e( 'Built for home-service crews', 'jcp-core' ); ?></p>
				<h1 id="jpd-hero-title" class="jcp-hero-title"><?php esc_html_e( 'Your crew already takes the photos. JobCapturePro turns finished jobs into marketing proof.', 'jcp-core' ); ?></h1>
				<p class="jcp-hero-subtitle"><?php esc_html_e( 'Fresh, local, trust-building proof across your website, Google, social, reviews, and the JCP Directory — automatically — without creating another marketing job.', 'jcp-core' ); ?></p>
				<div class="jcp-actions directory-cta-row">
					<div class="jcp-hero-primary-cta">
						<a class="btn btn-primary jcp-hero-cta-stacked" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="hero_primary_cta_clicked" data-jpd-section="hero">
							<span class="jcp-hero-cta-label"><?php echo esc_html( $cta_primary ); ?> →</span>
							<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free · About 60 seconds · No phone call · No credit card', 'jcp-core' ); ?></span>
						</a>
					</div>
					<p class="jpd-hero-secondary">
						<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="hero_skip" data-jpd-track="hero_secondary_cta_clicked" data-jpd-section="hero"><?php esc_html_e( 'Already know you want it? Start free trial', 'jcp-core' ); ?></a>
					</p>
				</div>
				<p class="jpd-compat-line" role="note">
					<?php esc_html_e( 'Works with your existing workflow — including ServiceTitan, Housecall Pro, Jobber, CompanyCam and more.', 'jcp-core' ); ?>
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
							<?php esc_html_e( 'Watch one job become proof →', 'jcp-core' ); ?>
						</button>
						<p class="jpd-canvas__note"><?php esc_html_e( 'Then personalize the full demo to your trade.', 'jcp-core' ); ?></p>
					</div>

					<div class="jpd-stage__controls jpd-stage__controls--run" data-jpd-canvas-run hidden>
						<p class="jpd-canvas__status" id="jpdCanvasStatus"><?php esc_html_e( 'Job photo received…', 'jcp-core' ); ?></p>
						<div class="jpd-canvas__payoff" data-jpd-payoff hidden>
							<p class="jpd-canvas__payoff-line"><?php esc_html_e( 'Your crew took one photo.', 'jcp-core' ); ?><br /><?php esc_html_e( 'JCP just turned it into a marketing system.', 'jcp-core' ); ?></p>
							<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="hero_primary_cta_clicked" data-jpd-section="hero_payoff"><?php echo esc_html( $cta_primary ); ?> →</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 3. Immediate proof / credibility -->
<?php
if ( function_exists( 'jcp_niche_render_authority' ) ) {
	jcp_niche_render_authority(
		[
			'variant'      => 'scoreboard',
			'section_id'   => 'jpd-authority',
			'eyebrow'      => __( 'Built by LeadsForward', 'jcp-core' ),
			'headline'     => __( 'A decade helping contractors turn attention into booked work.', 'jcp-core' ),
			'body'         => __( 'JobCapturePro comes from the same team that has spent years generating contractor leads — and saw finished jobs disappear into camera rolls instead of becoming public proof.', 'jcp-core' ),
			'show_eyebrow' => true,
			'show_body'    => true,
			'show_stats'   => true,
			'show_cta'     => false,
			'stats'        => [
				[
					'value'  => '10',
					'label'  => __( 'years', 'jcp-core' ),
					'detail' => __( 'helping contractors grow', 'jcp-core' ),
				],
				[
					'value'  => '250K+',
					'label'  => __( 'leads', 'jcp-core' ),
					'detail' => __( 'generated for home-service companies', 'jcp-core' ),
				],
				[
					'value'  => '$150M+',
					'label'  => __( 'booked', 'jcp-core' ),
					'detail' => __( 'revenue from those leads', 'jcp-core' ),
				],
			],
		],
		'job_proof_demo'
	);
}
?>

<!-- 4. Integrations / compatibility -->
<section class="jcp-section rankings-section jpd-integrations" id="integrations" aria-labelledby="jpd-integrations-title" data-jpd-track-view="integration_section_viewed">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-integrations-title" class="jcp-section-headline"><?php esc_html_e( 'JCP works with the photos and workflow you already have.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'If your crews already take photos in another system, they do not need to switch their daily behavior just to get value from JobCapturePro.', 'jcp-core' ); ?></p>
		</div>
		<ul class="jpd-integrations__list" aria-label="<?php esc_attr_e( 'Compatible field systems', 'jcp-core' ); ?>">
			<?php foreach ( $integrations as $name ) : ?>
				<li class="jpd-integrations__item"><?php echo esc_html( $name ); ?></li>
			<?php endforeach; ?>
			<li class="jpd-integrations__item jpd-integrations__item--more"><?php esc_html_e( 'and more', 'jcp-core' ); ?></li>
		</ul>
		<p class="jpd-integrations__line"><?php esc_html_e( 'Your crew doesn’t need another marketing job. Keep the workflow they already know — let JCP handle what happens after the photo.', 'jcp-core' ); ?></p>
		<p class="jpd-integrations__note"><?php esc_html_e( 'Supported integrations and workflows vary by setup. JobCapturePro works from completed-job proof your team already captures — in the JCP app or through connected field systems when available.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 5. Problem / solution -->
<section class="jcp-section rankings-section jpd-diff jpd-band jpd-band--tint" id="differentiator" aria-labelledby="jpd-diff-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-diff-title" class="jcp-section-headline"><?php esc_html_e( 'Your crew doesn’t need another marketing job.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'JCP doesn’t create generic marketing. It markets the work you already did.', 'jcp-core' ); ?></p>
		</div>
		<div class="jpd-diff__grid">
			<article class="jpd-diff__card jpd-diff__card--generic">
				<p class="jpd-diff__label"><?php esc_html_e( 'What normally happens', 'jcp-core' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Job photos stay buried in phones and CRMs', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Nobody posts them after a long day', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No fresh proof for homeowners to see', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No local signal from real completed work', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'No review ask while the job is still fresh', 'jcp-core' ); ?></li>
				</ul>
			</article>
			<article class="jpd-diff__card jpd-diff__card--real">
				<p class="jpd-diff__label"><?php esc_html_e( 'What happens with JCP', 'jcp-core' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Finished jobs become usable marketing proof', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Website content with real service + location', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google Business Profile activity', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social proof from the field', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Directory presence + review opportunity', 'jcp-core' ); ?></li>
				</ul>
			</article>
		</div>
	</div>
</section>

<!-- 6. See what one finished job becomes -->
<section class="jcp-section rankings-section jpd-ai" id="ai-transform" aria-labelledby="jpd-ai-title" data-jpd-track-view="outputs_preview_viewed">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-ai-title" class="jcp-section-headline"><?php esc_html_e( 'See what one finished job becomes.', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'One ordinary completed-job photo becomes multiple pieces of useful marketing proof — without your office rewriting captions after hours.', 'jcp-core' ); ?></p>
		</div>
		<div class="jpd-ai__story">
			<div class="jpd-ai__node jpd-ai__node--in">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'Finished job', 'jcp-core' ); ?></p>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="" width="320" height="220" loading="lazy" decoding="async" />
				<ul>
					<li><?php esc_html_e( 'Crew photo from the site', 'jcp-core' ); ?></li>
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
					<li><?php esc_html_e( 'AI-written check-in description', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Service + location attached', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Ready to publish where connected', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="jpd-ai__arrow" aria-hidden="true">
				<svg viewBox="0 0 80 24" width="80" height="24"><path d="M2 12h68M58 4l12 8-12 8" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div class="jpd-ai__node jpd-ai__node--out">
				<p class="jpd-ai__col-label"><?php esc_html_e( 'Proof everywhere', 'jcp-core' ); ?></p>
				<ul class="jpd-ai__channels">
					<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
				</ul>
			</div>
		</div>
		<p class="jpd-section-cta">
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="hero_primary_cta_clicked" data-jpd-section="outputs_preview"><?php echo esc_html( $cta_primary ); ?> →</a>
		</p>
	</div>
</section>

<!-- 7. Opt-in -->
<section class="jcp-section rankings-section jpd-optin" id="jpd-optin" data-jpd-optin aria-labelledby="jpd-optin-title" data-jpd-track-view="form_viewed">
	<div class="jcp-container">
		<div class="jpd-optin__card survey-step active">
			<header class="survey-head">
				<p class="demo-badge"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="jpd-optin-title" class="survey-title"><?php esc_html_e( 'See what this looks like for your trade.', 'jcp-core' ); ?></h2>
				<p class="survey-subtitle"><?php esc_html_e( 'Enter your work email and trade. We’ll show a real completed-job example personalized to the kind of work your crew does — no phone call required.', 'jcp-core' ); ?></p>
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
				<p class="jpd-optin__hint"><?php esc_html_e( 'Your demo is personalized to the type of work you actually do.', 'jcp-core' ); ?></p>
				<p class="jpd-optin__error" id="jpdOptinError" role="alert" hidden></p>
				<button type="submit" class="btn btn-primary survey-btn" id="jpdOptinSubmit"><?php echo esc_html( $cta_primary ); ?> →</button>
				<p class="survey-microcopy jcp-niche-trust-line"><?php esc_html_e( 'Free · About 60 seconds · No phone number · No credit card', 'jcp-core' ); ?></p>
				<p class="survey-legal"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
			</form>
		</div>
	</div>
</section>

<!-- 8. Outputs value -->
<section class="jcp-section rankings-section jpd-band jpd-band--tint" id="local-signals" aria-labelledby="jpd-seo-title">
	<div class="jcp-container">
		<div class="rankings-header">
			<h2 id="jpd-seo-title" class="jcp-section-headline"><?php esc_html_e( 'What one finished job creates', 'jcp-core' ); ?></h2>
			<p class="rankings-subtitle"><?php esc_html_e( 'Every completed job can leave fresh proof, local relevance, and more reasons to get chosen.', 'jcp-core' ); ?></p>
		</div>
		<div class="jpd-value-grid">
			<article class="jpd-value-card">
				<h3 class="jcp-section-subhead"><?php esc_html_e( 'Fresh proof', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Show customers — and connected platforms — that your business is actively completing real work.', 'jcp-core' ); ?></p>
			</article>
			<article class="jpd-value-card">
				<h3 class="jcp-section-subhead"><?php esc_html_e( 'Local relevance', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Pair real services with real job and location context homeowners actually care about.', 'jcp-core' ); ?></p>
			</article>
			<article class="jpd-value-card">
				<h3 class="jcp-section-subhead"><?php esc_html_e( 'More trust', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Give prospects visible evidence that you perform the work you advertise — before they call.', 'jcp-core' ); ?></p>
			</article>
		</div>
	</div>
</section>

<!-- 9. Founder -->
<section class="jcp-section rankings-section jpd-founder" id="why-jcp" aria-labelledby="jpd-founder-title">
	<div class="jcp-container jpd-founder__grid">
		<a class="jpd-founder__thumb" href="<?php echo esc_url( $why_href ); ?>" data-jpd-founder data-jpd-track="founder_video_played" data-jpd-section="founder">
			<img src="<?php echo esc_url( $founder_thumb ); ?>" alt="<?php esc_attr_e( 'Why we built JobCapturePro', 'jcp-core' ); ?>" width="640" height="400" loading="lazy" decoding="async" />
			<span class="jpd-founder__play" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
			</span>
		</a>
		<div class="jpd-founder__copy">
			<h2 id="jpd-founder-title" class="jcp-section-headline"><?php esc_html_e( 'Why we built JobCapturePro', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'LeadsForward has spent years helping contractors grow. We kept seeing the same gap: crews were finishing great jobs every day, but almost none of that real work became public marketing proof. JobCapturePro was built so the jobs you already complete can keep working after the truck leaves.', 'jcp-core' ); ?></p>
			<p class="jpd-founder__by"><?php esc_html_e( 'Built by LeadsForward', 'jcp-core' ); ?></p>
			<a class="btn btn-primary" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="hero_primary_cta_clicked" data-jpd-section="founder"><?php echo esc_html( $cta_primary ); ?> →</a>
		</div>
	</div>
</section>

<!-- 10. Testimonials -->
<?php
if ( function_exists( 'jcp_niche_render_testimonials' ) && $reviews !== [] ) {
	$normalized = [];
	foreach ( array_slice( $reviews, 0, 4 ) as $r ) {
		$normalized[] = [
			'quote'  => (string) ( $r['quote'] ?? $r['text'] ?? '' ),
			'name'   => (string) ( $r['name'] ?? '' ),
			'role'   => (string) ( $r['role'] ?? $r['title'] ?? '' ),
			'avatar' => (string) ( $r['avatar'] ?? $r['image'] ?? '' ),
			'rating' => 5,
		];
	}
	echo '<div data-jpd-track-view="testimonials_viewed">';
	jcp_niche_render_testimonials(
		[
			'headline'      => __( 'Real crews. Real completed jobs. Real proof.', 'jcp-core' ),
			'show_headline' => true,
			'section_id'    => 'testimonials',
			'reviews'       => $normalized,
		]
	);
	echo '</div>';
}
?>

<!-- 11. Final CTA -->
<section class="jcp-section rankings-section jcp-niche-final jpd-final-cta" data-jpd-track-view="final_cta_viewed">
	<div class="jcp-container">
		<div class="rankings-cta">
			<div class="cta-content">
				<h2 class="jcp-section-headline"><?php esc_html_e( 'Stop letting finished jobs disappear into the camera roll.', 'jcp-core' ); ?></h2>
				<p class="cta-paragraph"><?php esc_html_e( 'See what one of your jobs can become — then decide if you want every job to work this hard.', 'jcp-core' ); ?></p>
			</div>
			<div class="cta-button-wrapper">
				<a class="btn btn-primary rankings-cta-btn" href="#jpd-optin" data-jpd-scroll-optin data-jpd-track="final_cta_clicked" data-jpd-section="final"><?php echo esc_html( $cta_primary ); ?> →</a>
				<p class="cta-note"><?php esc_html_e( 'Free · About 60 seconds · No phone call required', 'jcp-core' ); ?></p>
				<p class="cta-note cta-secondary-link">
					<a href="<?php echo esc_url( $trial_href ); ?>" data-jpd-trial data-jpd-source="final"><?php esc_html_e( 'Already know you want it? Start free trial', 'jcp-core' ); ?></a>
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
			<h2 class="jcp-case-exit__title" id="jpdExitTitle"><?php esc_html_e( 'Want us to personalize this to your trade and send it to your inbox?', 'jcp-core' ); ?></h2>
			<p class="jcp-case-exit__body"><?php esc_html_e( 'Enter your work email and trade — we’ll send your personalized demo so you can come back when you have a minute.', 'jcp-core' ); ?></p>
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
				<a class="jcp-case-exit__primary" id="jpdExitCaseCta" href="<?php echo esc_url( $case_href ); ?>" data-jpd-track="proof_case_study_exit_clicked"><?php esc_html_e( 'See if I qualify →', 'jcp-core' ); ?></a>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Go back', 'jcp-core' ); ?></button>
			</div>
		</div>
		<div class="jpd-exit__panel" data-jpd-exit-panel="resume" hidden>
			<p class="jcp-case-exit__wait"><?php esc_html_e( 'PICK UP WHERE YOU LEFT OFF', 'jcp-core' ); ?></p>
			<h2 class="jcp-case-exit__title"><?php esc_html_e( 'Your personalized demo is ready.', 'jcp-core' ); ?></h2>
			<p class="jcp-case-exit__body"><?php esc_html_e( 'You already opted in — jump back into the demo built around your trade.', 'jcp-core' ); ?></p>
			<div class="jcp-case-exit__actions">
				<a class="jcp-case-exit__primary" href="<?php echo esc_url( $demo_run_url ); ?>"><?php esc_html_e( 'Continue my demo →', 'jcp-core' ); ?></a>
				<button type="button" class="jcp-case-exit__dismiss" data-jpd-exit-dismiss><?php esc_html_e( 'Not now', 'jcp-core' ); ?></button>
			</div>
		</div>
	</div>
</div>
