<?php
/**
 * Proof Sprint funnel body.
 *
 * @package JCP_Core
 *
 * @var string $trial_href
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon = static function ( string $name ): string {
	return function_exists( 'jcp_core_icon' ) ? jcp_core_icon( $name ) : '';
};

$campaign   = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$integ      = trailingslashit( get_template_directory_uri() ) . 'assets/integrations/';
$photo_job  = function_exists( 'jcp_proof_sprint_asset_url' ) ? jcp_proof_sprint_asset_url( 'jcp-campaign-job-proof-640.webp' ) : $campaign . 'jcp-campaign-job-proof-640.webp';
$photo_why  = $campaign . 'jcp-campaign-face-owner-640.webp';
$map_url    = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';

$reviews = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
$featured_review = null;
$other_reviews   = [];
foreach ( $reviews as $r ) {
	if ( ! $featured_review && ( (string) ( $r['id'] ?? '' ) === 'brian-hardy' || stripos( (string) ( $r['name'] ?? '' ), 'Brian' ) === 0 ) ) {
		$featured_review = $r;
		continue;
	}
	$other_reviews[] = $r;
}
if ( ! $featured_review && $reviews !== [] ) {
	$featured_review = $reviews[0];
	$other_reviews   = array_slice( $reviews, 1 );
}
$other_reviews = array_slice( $other_reviews, 0, 3 );

$case_props = function_exists( 'jcp_proof_sprint_case_study_props' ) ? jcp_proof_sprint_case_study_props() : [];

$auth = function_exists( 'jcp_page_authority_leadsforward_props' ) ? jcp_page_authority_leadsforward_props() : [];
$auth_stats = is_array( $auth['stats'] ?? null ) ? $auth['stats'] : [];

$cta_primary = __( 'See it on my business', 'jcp-core' );
?>

<!-- 1. HERO -->
<section class="jcp-section ps-hero" id="ps-hero" aria-labelledby="ps-hero-title">
	<div class="jcp-container ps-hero__grid">
		<div class="ps-hero__copy">
			<p class="ps-eyebrow ps-eyebrow--accent"><?php esc_html_e( 'For home-service companies doing real work every week', 'jcp-core' ); ?></p>
			<h1 id="ps-hero-title" class="ps-hero__title">
				<?php esc_html_e( 'Your crew already creates the proof.', 'jcp-core' ); ?>
				<span class="ps-hero__accent"><?php esc_html_e( 'JobCapturePro puts it to work.', 'jcp-core' ); ?></span>
			</h1>
			<p class="ps-hero__sub"><?php esc_html_e( 'Every finished job can become fresh website proof, Google Business Profile content, social content, review opportunities and public JCP Directory proof — without turning your techs into marketers.', 'jcp-core' ); ?></p>
			<div class="ps-hero__actions">
				<a class="btn btn-primary ps-btn-xl" href="#ps-optin" data-ps-scroll-optin data-ps-track="DemoCTA" data-ps-source="hero_demo"><?php echo esc_html( $cta_primary ); ?> →</a>
				<a class="ps-link-secondary" href="#ps-demo" data-ps-track="demo_jump" data-ps-source="hero_demo">
					<?php if ( $icon( 'play' ) ) : ?>
						<img src="<?php echo esc_url( $icon( 'play' ) ); ?>" alt="" width="16" height="16" />
					<?php endif; ?>
					<?php esc_html_e( 'Watch one job transform →', 'jcp-core' ); ?>
				</a>
			</div>
			<p class="ps-micro"><?php esc_html_e( 'Work email + trade · About 60 seconds · No credit card', 'jcp-core' ); ?></p>
			<p class="ps-hero__trial-link">
				<a href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="hero_trial"><?php esc_html_e( 'Start free 14-day trial →', 'jcp-core' ); ?></a>
			</p>
		</div>
		<div class="ps-hero__visual" data-ps-theater aria-hidden="false">
			<div class="ps-theater">
				<div class="ps-theater__stage">
					<span class="ps-theater__pulse" aria-hidden="true"></span>
					<article class="ps-theater__job is-on" data-theater-job>
						<div class="ps-theater__job-media">
							<img src="<?php echo esc_url( $photo_job ); ?>" alt="" width="480" height="320" decoding="async" fetchpriority="high" data-no-lazy data-ps-job-photo />
							<span class="ps-badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
							<span class="ps-theater__scan" aria-hidden="true"></span>
						</div>
						<div class="ps-theater__job-meta">
							<strong data-ps-job-title><?php esc_html_e( 'Water heater replacement', 'jcp-core' ); ?></strong>
							<span data-ps-job-city><?php esc_html_e( 'Austin, TX', 'jcp-core' ); ?></span>
						</div>
					</article>
					<div class="ps-theater__hub" data-theater-hub>
						<span class="ps-theater__core">JCP</span>
						<span class="ps-theater__hub-label"><?php esc_html_e( 'Publishes', 'jcp-core' ); ?></span>
					</div>
					<ul class="ps-theater__outputs" data-theater-outputs>
						<li data-out="website"><strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Map + check-ins', 'jcp-core' ); ?></span></li>
						<li data-out="google"><strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'When connected', 'jcp-core' ); ?></span></li>
						<li data-out="review"><strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'QR / link', 'jcp-core' ); ?></span></li>
						<li data-out="social"><strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'When connected', 'jcp-core' ); ?></span></li>
						<li data-out="local"><strong><?php esc_html_e( 'Directory', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Live job proof', 'jcp-core' ); ?></span></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. AUTHORITY -->
<section class="ps-authority" aria-label="<?php esc_attr_e( 'LeadsForward track record', 'jcp-core' ); ?>" data-ps-reveal>
	<div class="jcp-container ps-authority__inner">
		<p class="ps-authority__label"><?php esc_html_e( 'Built by the team behind LeadsForward', 'jcp-core' ); ?></p>
		<ul class="ps-authority__stats">
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

<!-- 3. CASE STUDY / GEOGRIDS -->
<section class="jcp-section ps-case" id="ps-proof" data-ps-reveal data-ps-track-view="case_study_viewed">
	<?php
	if ( $case_props !== [] && function_exists( 'jcp_niche_render_local_rank_case_study' ) ) {
		jcp_niche_render_local_rank_case_study( $case_props, 'proof_sprint' );
	}
	?>
	<div class="jcp-container">
		<p class="ps-section-cta">
			<a class="btn btn-primary" href="#ps-optin" data-ps-scroll-optin data-ps-track="DemoCTA" data-ps-source="map_demo"><?php echo esc_html( $cta_primary ); ?> →</a>
		</p>
	</div>
</section>

<!-- 4. ASSESSMENT -->
<section class="jcp-section ps-assessment" id="ps-assessment" aria-labelledby="ps-assess-title" data-ps-reveal>
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'Optional proof check', 'jcp-core' ); ?></p>
			<h2 id="ps-assess-title" class="ps-section-title"><?php esc_html_e( 'How much completed-job proof is your business leaving unused?', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'Two quick answers. This does not block the demo.', 'jcp-core' ); ?></p>
		</header>

		<div class="ps-assess-card" data-ps-assessment>
			<div class="ps-assess-stepper" aria-hidden="true">
				<span data-stepper="1" class="is-on"></span>
				<span data-stepper="2"></span>
			</div>

			<form id="psProofForm" novalidate>
				<div class="ps-q is-active" data-ps-step="1">
					<p class="ps-q__label"><?php esc_html_e( 'About how many completed jobs does your company average per week?', 'jcp-core' ); ?></p>
					<div class="ps-choices ps-choices--compact" role="group">
						<?php
						$jobs = [
							'3'  => '1–5',
							'8'  => '6–10',
							'15' => '11–20',
							'28' => '21–35',
							'43' => '36–50',
							'55' => '50+',
						];
						foreach ( $jobs as $mid => $label ) :
							?>
							<button type="button" class="ps-choice" data-ps-field="jobs" data-ps-value="<?php echo esc_attr( $mid ); ?>" data-ps-label="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
					<label class="ps-custom">
						<span><?php esc_html_e( 'Or enter exact weekly jobs', 'jcp-core' ); ?></span>
						<input type="number" min="0" max="500" inputmode="numeric" id="psJobsCustom" data-ps-custom="jobs" />
					</label>
				</div>

				<div class="ps-q" data-ps-step="2" hidden>
					<p class="ps-q__label"><?php esc_html_e( 'About how many of those jobs usually become public proof on your website, Google or social?', 'jcp-core' ); ?></p>
					<div class="ps-choices" role="group">
						<button type="button" class="ps-choice" data-ps-field="used" data-ps-ratio="0" data-ps-label="<?php esc_attr_e( 'Almost none', 'jcp-core' ); ?>"><?php esc_html_e( 'Almost none', 'jcp-core' ); ?></button>
						<button type="button" class="ps-choice" data-ps-field="used" data-ps-ratio="0.15" data-ps-label="<?php esc_attr_e( 'A few', 'jcp-core' ); ?>"><?php esc_html_e( 'A few', 'jcp-core' ); ?></button>
						<button type="button" class="ps-choice" data-ps-field="used" data-ps-ratio="0.5" data-ps-label="<?php esc_attr_e( 'About half', 'jcp-core' ); ?>"><?php esc_html_e( 'About half', 'jcp-core' ); ?></button>
						<button type="button" class="ps-choice" data-ps-field="used" data-ps-ratio="0.8" data-ps-label="<?php esc_attr_e( 'Most', 'jcp-core' ); ?>"><?php esc_html_e( 'Most', 'jcp-core' ); ?></button>
						<button type="button" class="ps-choice" data-ps-field="used" data-ps-ratio="0.95" data-ps-label="<?php esc_attr_e( 'Nearly all', 'jcp-core' ); ?>"><?php esc_html_e( 'Nearly all', 'jcp-core' ); ?></button>
					</div>
				</div>

				<div class="ps-assess-actions">
					<button type="button" class="btn btn-secondary" id="psBackBtn" disabled><?php esc_html_e( '← Back', 'jcp-core' ); ?></button>
					<button type="button" class="btn btn-primary" id="psNextBtn" disabled><?php esc_html_e( 'Next →', 'jcp-core' ); ?></button>
				</div>
				<p class="ps-assess-msg" id="psFormMsg" aria-live="polite"></p>
			</form>
		</div>
	</div>
</section>

<!-- 5. RESULT -->
<section class="jcp-section ps-result" id="ps-result" hidden aria-live="polite" data-ps-result>
	<div class="jcp-container ps-result__grid">
		<div class="ps-result__copy">
			<p class="ps-eyebrow ps-eyebrow--light"><?php esc_html_e( 'Your proof potential', 'jcp-core' ); ?></p>
			<h2 class="ps-result__title"><?php esc_html_e( 'You complete roughly', 'jcp-core' ); ?> <span id="psAnnualJobs">416</span> <?php esc_html_e( 'jobs per year.', 'jcp-core' ); ?></h2>
			<p class="ps-result__loss"><?php esc_html_e( 'About', 'jcp-core' ); ?> <strong id="psUnusedJobs">396</strong> <?php esc_html_e( 'may never become public proof.', 'jcp-core' ); ?></p>
			<p class="ps-result__body" id="psResultSentence"><?php esc_html_e( 'You already paid to create the proof. JCP helps you keep using it.', 'jcp-core' ); ?></p>
			<a class="btn btn-primary ps-btn-xl" href="#ps-optin" id="psResultCta" data-ps-scroll-optin data-ps-track="DemoCTA" data-ps-source="assessment_demo"><?php esc_html_e( 'See what one of those jobs becomes →', 'jcp-core' ); ?></a>
		</div>
		<div class="ps-result__visual" aria-hidden="true">
			<div class="ps-result-fan">
				<div class="ps-result-fan__job">
					<img src="<?php echo esc_url( $photo_job ); ?>" alt="" width="200" height="140" loading="lazy" data-ps-job-photo />
					<span data-ps-trade-label><?php esc_html_e( 'Plumbing job', 'jcp-core' ); ?></span>
				</div>
				<ul class="ps-result-fan__outs">
					<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- 6. LEAD CAPTURE (single gate) -->
<section class="jcp-section ps-optin" id="ps-optin" data-ps-optin aria-labelledby="ps-optin-title" data-ps-reveal>
	<div class="jcp-container">
		<div class="ps-optin__grid">
			<div class="ps-optin__copy">
				<p class="ps-eyebrow"><?php esc_html_e( 'Free personalized demo', 'jcp-core' ); ?></p>
				<h2 id="ps-optin-title" class="ps-section-title"><?php esc_html_e( 'See what JCP would do with the jobs your business already completes.', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'Tell us your trade and we’ll show you the kind of finished job your crew handles every week — and what JobCapturePro can turn it into.', 'jcp-core' ); ?></p>
			</div>
			<div class="ps-optin__card">
				<form id="psOptinForm" novalidate>
					<div class="ps-optin__field">
						<label for="ps-email"><?php esc_html_e( 'Work email', 'jcp-core' ); ?> <span class="ps-required">*</span></label>
						<input id="ps-email" name="email" type="email" autocomplete="email" inputmode="email" placeholder="you@company.com" required />
					</div>
					<div class="ps-optin__field">
						<label for="ps-niche"><?php esc_html_e( 'Trade', 'jcp-core' ); ?> <span class="ps-required">*</span></label>
						<select id="ps-niche" name="trade" required>
							<option value=""><?php esc_html_e( 'Select your trade…', 'jcp-core' ); ?></option>
							<option value="hvac"><?php esc_html_e( 'HVAC', 'jcp-core' ); ?></option>
							<option value="plumbing"><?php esc_html_e( 'Plumbing', 'jcp-core' ); ?></option>
							<option value="roofing"><?php esc_html_e( 'Roofing', 'jcp-core' ); ?></option>
							<option value="electrical"><?php esc_html_e( 'Electrical', 'jcp-core' ); ?></option>
							<option value="foundation"><?php esc_html_e( 'Foundation / Waterproofing', 'jcp-core' ); ?></option>
							<option value="landscaping"><?php esc_html_e( 'Landscaping', 'jcp-core' ); ?></option>
							<option value="remodeling"><?php esc_html_e( 'Remodeling', 'jcp-core' ); ?></option>
							<option value="other"><?php esc_html_e( 'Other', 'jcp-core' ); ?></option>
						</select>
					</div>
					<p class="ps-optin__error" id="psOptinError" role="alert" hidden></p>
					<button type="submit" class="btn btn-primary ps-btn-xl" id="psOptinSubmit"><?php esc_html_e( 'Show me my demo →', 'jcp-core' ); ?></button>
					<p class="ps-micro"><?php esc_html_e( 'Free · About 60 seconds · No phone number · No credit card', 'jcp-core' ); ?></p>
					<p class="ps-optin__legal"><?php esc_html_e( 'By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.', 'jcp-core' ); ?></p>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- 7. DEMO -->
<section class="jcp-section ps-demo" id="ps-demo" aria-labelledby="ps-demo-title" data-ps-reveal data-ps-demo data-ps-map-url="<?php echo esc_url( $map_url ); ?>">
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'The interactive product demo', 'jcp-core' ); ?></p>
			<h2 id="ps-demo-title" class="ps-section-title"><?php esc_html_e( 'See what one completed job becomes.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'This is the point your crew closes the work. JobCapturePro turns that real job into proof without turning your technicians into marketers.', 'jcp-core' ); ?></p>
		</header>

		<div class="ps-demo-gate" id="psDemoGate">
			<p><?php esc_html_e( 'Enter your work email and trade above to unlock the personalized product experience.', 'jcp-core' ); ?></p>
			<a class="btn btn-primary" href="#ps-optin" data-ps-scroll-optin><?php echo esc_html( $cta_primary ); ?> →</a>
		</div>

		<div class="ps-demo-shell" id="psDemoShell" hidden>
			<div class="ps-demo-progress" id="psDemoProgress" aria-hidden="true"></div>
			<div class="ps-demo-layout">
				<div class="ps-demo-copy">
					<p class="ps-demo-step-label" id="psDemoStepLabel"><?php esc_html_e( 'Step 1 of 8', 'jcp-core' ); ?></p>
					<h3 id="psDemoTitle"><?php esc_html_e( 'The job is finished.', 'jcp-core' ); ?></h3>
					<p id="psDemoBody"><?php esc_html_e( 'Without JCP, this is where the marketing often stops.', 'jcp-core' ); ?></p>
					<p class="ps-demo-detail"><span class="ps-demo-detail__label" id="psDemoDetailLabel"><?php esc_html_e( 'INPUT', 'jcp-core' ); ?></span><strong id="psDemoSource"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></strong></p>
					<div class="ps-demo-controls">
						<button type="button" class="btn btn-secondary" id="psDemoPrev" disabled><?php esc_html_e( '← Previous', 'jcp-core' ); ?></button>
						<button type="button" class="btn btn-primary" id="psDemoNext"><?php esc_html_e( 'See what happens →', 'jcp-core' ); ?></button>
						<button type="button" class="btn btn-secondary" id="psDemoPause" hidden><?php esc_html_e( 'Pause', 'jcp-core' ); ?></button>
						<button type="button" class="ps-link-secondary" id="psDemoSkip" hidden><?php esc_html_e( 'Skip to results', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="ps-demo-canvas" id="psDemoCanvas" aria-live="polite"></div>
			</div>
		</div>

		<div class="ps-demo-payoff" id="psDemoPayoff" hidden>
			<div class="ps-demo-payoff__card">
				<div class="ps-demo-payoff__visual" aria-hidden="true">
					<img src="<?php echo esc_url( $photo_job ); ?>" alt="" width="480" height="320" loading="lazy" decoding="async" />
					<ul class="ps-demo-payoff__channels">
						<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
						<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
					</ul>
				</div>
				<div class="ps-demo-payoff__copy">
					<p class="ps-demo-payoff__kicker" id="psDemoPayoffJobs"></p>
					<h3><?php esc_html_e( 'One job is useful. Hundreds become a system.', 'jcp-core' ); ?></h3>
					<p class="ps-demo-payoff__body"><?php esc_html_e( 'Put your completed jobs through JCP and your website proof, Google activity, reviews, social content and Directory footprint keep growing from work your crew already does.', 'jcp-core' ); ?></p>
					<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="demo_trial" data-ps-track="trial_cta_clicked"><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
					<button type="button" class="ps-demo-payoff__restart" id="psDemoRestart"><?php esc_html_e( 'Replay the transformation', 'jcp-core' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 8. INTEGRATIONS -->
<section class="jcp-section ps-integrations" id="ps-integrations" data-ps-reveal>
	<div class="jcp-container ps-integrations__inner">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'It works with the way you already work', 'jcp-core' ); ?></p>
			<h2 class="ps-section-title"><?php esc_html_e( 'Already use a CRM or photo app? Good.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'If your team already captures consistent job information and photos, JCP does not need to replace a workflow that is already working. Connect a supported system — or use JCP directly.', 'jcp-core' ); ?></p>
		</header>
		<div class="ps-integ-groups">
			<div class="ps-integ-group">
				<p class="ps-integ-group__label"><?php esc_html_e( 'Field / CRM / photo workflows', 'jcp-core' ); ?></p>
				<ul class="ps-logo-row" aria-label="<?php esc_attr_e( 'Field workflows', 'jcp-core' ); ?>">
					<li class="ps-logo-mark ps-logo-mark--hcp" aria-label="Housecall Pro"><span>Housecall Pro</span></li>
					<li><img src="<?php echo esc_url( $integ . 'companycam.svg' ); ?>" alt="CompanyCam" width="140" height="36" loading="lazy" /></li>
					<li><img src="<?php echo esc_url( $integ . 'workiz.svg' ); ?>" alt="Workiz" width="120" height="36" loading="lazy" /></li>
				</ul>
			</div>
			<div class="ps-integ-group">
				<p class="ps-integ-group__label"><?php esc_html_e( 'Business / data integrations', 'jcp-core' ); ?></p>
				<ul class="ps-logo-row" aria-label="<?php esc_attr_e( 'Business integrations', 'jcp-core' ); ?>">
					<li><img src="<?php echo esc_url( $integ . 'quickbooks.svg' ); ?>" alt="QuickBooks" width="130" height="36" loading="lazy" /></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- 9. WHY -->
<section class="jcp-section ps-why" id="ps-why" data-ps-reveal>
	<div class="jcp-container ps-why__grid">
		<div class="ps-why__copy">
			<p class="ps-eyebrow"><?php esc_html_e( 'Why we built JobCapturePro', 'jcp-core' ); ?></p>
			<h2 class="ps-section-title"><?php esc_html_e( 'We spent 10 years generating contractor leads. We kept seeing the same waste.', 'jcp-core' ); ?></h2>
			<div class="ps-why__body">
				<p><?php esc_html_e( 'Contractors spend thousands on websites, SEO, ads, social and reputation tools — while some of their best marketing material disappears into camera rolls, text threads and CRM records.', 'jcp-core' ); ?></p>
				<p><?php esc_html_e( 'JobCapturePro connects the work crews already do with the proof future customers want to see: real jobs, real photos, real locations, real customers.', 'jcp-core' ); ?></p>
			</div>
			<ul class="ps-why__stats">
				<li><strong>250K+</strong> <span><?php esc_html_e( 'contractor leads generated', 'jcp-core' ); ?></span></li>
				<li><strong>$150M+</strong> <span><?php esc_html_e( 'client revenue booked from those leads', 'jcp-core' ); ?></span></li>
			</ul>
		</div>
		<div class="ps-why__visual">
			<img src="<?php echo esc_url( $photo_why ); ?>" alt="<?php esc_attr_e( 'Home-service operator credibility', 'jcp-core' ); ?>" width="640" height="480" loading="lazy" decoding="async" />
			<p class="ps-why__visual-caption"><?php esc_html_e( 'Built by the team behind LeadsForward — for contractors who already do the work.', 'jcp-core' ); ?></p>
		</div>
	</div>
</section>

<!-- 10. REVIEWS -->
<section class="jcp-section ps-reviews" id="ps-reviews" data-ps-reveal>
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'From real operators', 'jcp-core' ); ?></p>
			<h2 class="ps-section-title"><?php esc_html_e( 'Real contractors. Real feedback.', 'jcp-core' ); ?></h2>
		</header>
		<?php if ( $featured_review ) : ?>
			<blockquote class="ps-review-featured">
				<div class="ps-stars" aria-hidden="true">★★★★★</div>
				<p>“<?php echo esc_html( (string) ( $featured_review['quote'] ?? '' ) ); ?>”</p>
				<footer>
					<?php if ( ! empty( $featured_review['avatar'] ) ) : ?>
						<img src="<?php echo esc_url( (string) $featured_review['avatar'] ); ?>" alt="" width="48" height="48" loading="lazy" />
					<?php endif; ?>
					<span>
						<strong><?php echo esc_html( (string) ( $featured_review['name'] ?? '' ) ); ?></strong>
						<em><?php echo esc_html( (string) ( $featured_review['role'] ?? '' ) ); ?></em>
					</span>
				</footer>
			</blockquote>
		<?php endif; ?>
		<?php if ( $other_reviews !== [] ) : ?>
			<div class="ps-review-grid">
				<?php foreach ( $other_reviews as $r ) : ?>
					<blockquote class="ps-review-card">
						<div class="ps-stars" aria-hidden="true">★★★★★</div>
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
		<?php endif; ?>
	</div>
</section>

<!-- 11. TRIAL -->
<section class="jcp-section ps-trial" id="ps-trial" data-ps-reveal>
	<div class="jcp-container ps-trial__layout">
		<div class="ps-trial__copy">
			<p class="ps-eyebrow ps-eyebrow--light"><?php esc_html_e( 'The 14-day JobCapturePro Proof Sprint', 'jcp-core' ); ?></p>
			<h2 class="ps-trial__title"><?php esc_html_e( 'Your next 14 days are already full of marketing. Don’t let those jobs disappear too.', 'jcp-core' ); ?></h2>
			<p class="ps-trial__sub"><?php esc_html_e( 'Get your first real completed jobs working through JCP during the trial.', 'jcp-core' ); ?></p>
		</div>
		<div class="ps-trial__panel">
			<ul class="ps-trial__benefits">
				<li><?php esc_html_e( '14-day free trial', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'No credit card required', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Guided activation', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Connect a supported workflow or use the JCP app', 'jcp-core' ); ?></li>
			</ul>
			<a class="btn btn-primary ps-btn-xl ps-btn-light" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="trial_section" data-ps-track="trial_cta_clicked"><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
			<p class="ps-micro ps-micro--light"><?php esc_html_e( 'No credit card · Guided setup · Cancel anytime', 'jcp-core' ); ?></p>
		</div>
	</div>
</section>

<!-- 12. FAQ -->
<section class="jcp-section rankings-section faq-section ps-faq" id="ps-faq" data-ps-reveal>
	<div class="jcp-container">
		<header class="ps-section-head">
			<h2 class="ps-section-title"><?php esc_html_e( 'Clear answers before you start.', 'jcp-core' ); ?></h2>
		</header>
		<div class="faq-grid">
			<?php
			$faqs = [
				[
					'q' => __( 'We already use a CRM or photo app. Does my crew need to change anything?', 'jcp-core' ),
					'a' => __( 'Not necessarily. If your current supported workflow already captures the job information and photos JCP needs, your crew can keep working the way they already do. JCP handles the marketing workflow downstream. Teams can also use the JCP app directly when needed.', 'jcp-core' ),
				],
				[
					'q' => __( 'Does JobCapturePro guarantee Google rankings?', 'jcp-core' ),
					'a' => __( 'No. Google rankings depend on many factors. JCP helps create and publish real job activity and local proof that can support visibility, trust and conversion. Past results do not guarantee future rankings.', 'jcp-core' ),
				],
				[
					'q' => __( 'What happens after the 14 days?', 'jcp-core' ),
					'a' => __( 'You can continue on a paid plan if JobCapturePro is a fit, or cancel. During the trial we focus on getting a real workflow live with your jobs so you can evaluate the product with actual work, not a sandbox.', 'jcp-core' ),
				],
				[
					'q' => __( 'What if my website isn’t WordPress?', 'jcp-core' ),
					'a' => __( 'Many customers use the WordPress plugin for native website proof. If you’re on another platform, talk with us about current embed and publishing options for your setup. We won’t promise support that isn’t available yet.', 'jcp-core' ),
				],
				[
					'q' => __( 'What gets published from one completed job?', 'jcp-core' ),
					'a' => __( 'Depending on your plan and connected channels: website job proof, Google Business Profile activity, review opportunities, social-ready content, and directory or service-area visibility. Publishing depends on connected channels.', 'jcp-core' ),
				],
			];
			foreach ( $faqs as $i => $faq ) :
				?>
				<details class="faq-item"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $faq['q'] ); ?></summary>
					<p><?php echo esc_html( $faq['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- FINAL CTA -->
<section class="jcp-section ps-final ps-final--navy" data-ps-reveal>
	<div class="jcp-container ps-final__inner">
		<h2 class="ps-final__title"><?php esc_html_e( 'You already paid to do the job. Make it help win the next one.', 'jcp-core' ); ?></h2>
		<div class="ps-final__actions">
			<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="final_trial" data-ps-track="trial_cta_clicked"><?php esc_html_e( 'Start my free 14-day trial →', 'jcp-core' ); ?></a>
			<p class="ps-micro ps-micro--light"><?php esc_html_e( 'No credit card · Guided activation', 'jcp-core' ); ?></p>
			<button type="button" class="ps-final__replay" id="psFinalReplay"><?php esc_html_e( 'Replay the demo', 'jcp-core' ); ?></button>
		</div>
	</div>
</section>
