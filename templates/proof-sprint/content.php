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
$photo_hvac = function_exists( 'jcp_proof_sprint_asset_url' ) ? jcp_proof_sprint_asset_url( 'jcp-campaign-hvac-capture-640.webp' ) : $campaign . 'jcp-campaign-hvac-capture-640.webp';
$photo_job  = function_exists( 'jcp_proof_sprint_asset_url' ) ? jcp_proof_sprint_asset_url( 'jcp-campaign-job-proof-640.webp' ) : $campaign . 'jcp-campaign-job-proof-640.webp';
$photo_crew = function_exists( 'jcp_proof_sprint_asset_url' ) ? jcp_proof_sprint_asset_url( 'jcp-campaign-crew-review-640.webp' ) : $campaign . 'jcp-campaign-crew-review-640.webp';
$map_url    = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';

$reviews = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
$featured_review = $reviews[0] ?? null;
$other_reviews   = array_slice( $reviews, 1 );

$case_props = function_exists( 'jcp_proof_sprint_case_study_props' ) ? jcp_proof_sprint_case_study_props() : [];

$auth = function_exists( 'jcp_page_authority_leadsforward_props' ) ? jcp_page_authority_leadsforward_props() : [];
$auth_stats = is_array( $auth['stats'] ?? null ) ? $auth['stats'] : [];
?>

<!-- 1. HERO -->
<section class="jcp-section ps-hero" id="ps-hero" aria-labelledby="ps-hero-title">
	<div class="jcp-container ps-hero__grid">
		<div class="ps-hero__copy">
			<p class="ps-eyebrow ps-eyebrow--accent"><?php esc_html_e( 'For home-service companies doing real work every week', 'jcp-core' ); ?></p>
			<h1 id="ps-hero-title" class="ps-hero__title">
				<?php esc_html_e( 'Your crew is already creating your marketing.', 'jcp-core' ); ?>
				<span class="ps-hero__accent"><?php esc_html_e( 'JobCapturePro puts it to work.', 'jcp-core' ); ?></span>
			</h1>
			<p class="ps-hero__sub"><?php esc_html_e( 'Turn completed jobs into website proof, Google Business Profile content, review opportunities, social content and local visibility, without adding another marketing task to your day.', 'jcp-core' ); ?></p>
			<div class="ps-hero__actions">
				<a class="btn btn-primary ps-btn-xl" href="#ps-assessment" data-ps-track="proof_assessment_cta" data-ps-source="hero"><?php esc_html_e( 'See What My Jobs Could Become →', 'jcp-core' ); ?></a>
				<a class="ps-link-secondary" href="#ps-demo" data-ps-track="demo_jump" data-ps-source="hero">
					<?php if ( $icon( 'play' ) ) : ?>
						<img src="<?php echo esc_url( $icon( 'play' ) ); ?>" alt="" width="16" height="16" />
					<?php endif; ?>
					<?php esc_html_e( 'Watch One Job Transform', 'jcp-core' ); ?>
				</a>
			</div>
			<p class="ps-micro"><?php esc_html_e( 'No credit card · 14-day trial · Guided activation', 'jcp-core' ); ?></p>
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
						<li data-out="website"><strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Job proof live', 'jcp-core' ); ?></span></li>
						<li data-out="google"><strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'GBP update', 'jcp-core' ); ?></span></li>
						<li data-out="review"><strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Ask on site', 'jcp-core' ); ?></span></li>
						<li data-out="social"><strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Ready to post', 'jcp-core' ); ?></span></li>
						<li data-out="local"><strong><?php esc_html_e( 'Directory', 'jcp-core' ); ?></strong><span><?php esc_html_e( 'Verified listing', 'jcp-core' ); ?></span></li>
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
			<?php foreach ( $auth_stats as $stat ) : ?>
				<li>
					<strong><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?><?php if ( ! empty( $stat['label'] ) ) : ?><em><?php echo esc_html( (string) $stat['label'] ); ?></em><?php endif; ?></strong>
					<span><?php echo esc_html( (string) ( $stat['detail'] ?? '' ) ); ?></span>
				</li>
			<?php endforeach; ?>
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
</section>

<!-- 4. ASSESSMENT -->
<section class="jcp-section ps-assessment" id="ps-assessment" aria-labelledby="ps-assess-title" data-ps-reveal>
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( '30-second proof waste assessment', 'jcp-core' ); ?></p>
			<h2 id="ps-assess-title" class="ps-section-title"><?php esc_html_e( 'How much completed-job proof is your business leaving unused?', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'Answer four quick questions. We’ll estimate how much potential marketing material your business creates every year.', 'jcp-core' ); ?></p>
		</header>

		<div class="ps-assess-card" data-ps-assessment>
			<div class="ps-assess-stepper" aria-hidden="true">
				<span data-stepper="1" class="is-on"></span>
				<span data-stepper="2"></span>
				<span data-stepper="3"></span>
				<span data-stepper="4"></span>
			</div>

			<form id="psProofForm" novalidate>
				<div class="ps-q is-active" data-ps-step="1">
					<p class="ps-q__label"><?php esc_html_e( 'What’s your primary trade?', 'jcp-core' ); ?></p>
					<div class="ps-choices" role="group" aria-label="<?php esc_attr_e( 'Primary trade', 'jcp-core' ); ?>">
						<?php
						$trades = [
							'hvac'        => __( 'HVAC', 'jcp-core' ),
							'plumbing'    => __( 'Plumbing', 'jcp-core' ),
							'roofing'     => __( 'Roofing', 'jcp-core' ),
							'electrical'  => __( 'Electrical', 'jcp-core' ),
							'foundation'  => __( 'Foundation / Waterproofing', 'jcp-core' ),
							'landscaping' => __( 'Landscaping', 'jcp-core' ),
							'remodeling'  => __( 'Remodeling', 'jcp-core' ),
							'other'       => __( 'Other', 'jcp-core' ),
						];
						foreach ( $trades as $key => $label ) :
							?>
							<button type="button" class="ps-choice" data-ps-field="trade" data-ps-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="ps-q" data-ps-step="2" hidden>
					<p class="ps-q__label"><?php esc_html_e( 'About how many completed jobs do you average per week?', 'jcp-core' ); ?></p>
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

				<div class="ps-q" data-ps-step="3" hidden>
					<p class="ps-q__label"><?php esc_html_e( 'Where do your job photos typically live?', 'jcp-core' ); ?></p>
					<div class="ps-choices" role="group">
						<?php
						$sources = [
							'Housecall Pro'   => __( 'Housecall Pro', 'jcp-core' ),
							'CompanyCam'      => __( 'CompanyCam', 'jcp-core' ),
							'Workiz'          => __( 'Workiz', 'jcp-core' ),
							'QuickBooks'      => __( 'QuickBooks', 'jcp-core' ),
							'JCP mobile app'  => __( 'JCP mobile app', 'jcp-core' ),
							'Camera roll'     => __( 'Phones / camera rolls', 'jcp-core' ),
							'Another system'  => __( 'Another system', 'jcp-core' ),
							'No consistent photos' => __( 'We don’t consistently take photos', 'jcp-core' ),
						];
						foreach ( $sources as $val => $label ) :
							?>
							<button type="button" class="ps-choice" data-ps-field="source" data-ps-value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="ps-q" data-ps-step="4" hidden>
					<p class="ps-q__label"><?php esc_html_e( 'How many completed jobs currently become public marketing proof each week?', 'jcp-core' ); ?></p>
					<p class="ps-q__hint"><?php esc_html_e( 'Website projects, Google updates, reviews, social posts or service-area proof. Count any job that actually gets used publicly.', 'jcp-core' ); ?></p>
					<div class="ps-choices ps-choices--compact" role="group">
						<?php
						$used = [
							'0'  => '0',
							'1'  => '1',
							'2'  => '2',
							'4'  => '3–5',
							'8'  => '6–10',
							'12' => '10+',
						];
						foreach ( $used as $mid => $label ) :
							?>
							<button type="button" class="ps-choice" data-ps-field="used" data-ps-value="<?php echo esc_attr( $mid ); ?>" data-ps-label="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
					<label class="ps-custom">
						<span><?php esc_html_e( 'Or enter exact weekly count', 'jcp-core' ); ?></span>
						<input type="number" min="0" max="500" inputmode="numeric" id="psUsedCustom" data-ps-custom="used" />
					</label>
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
			<h2 class="ps-result__title"><?php esc_html_e( 'You complete roughly', 'jcp-core' ); ?> <span id="psAnnualJobs">1,040</span> <?php esc_html_e( 'jobs per year.', 'jcp-core' ); ?></h2>
			<p class="ps-result__loss"><?php esc_html_e( 'About', 'jcp-core' ); ?> <strong id="psUnusedJobs">936</strong> <?php esc_html_e( 'may never become public proof.', 'jcp-core' ); ?></p>
			<p class="ps-result__body" id="psResultSentence"><?php esc_html_e( 'The work already happened. The photos may already exist. JobCapturePro helps turn more of those completed jobs into assets that keep working after the truck leaves.', 'jcp-core' ); ?></p>
			<a class="btn btn-primary ps-btn-xl" href="#ps-demo" id="psResultCta" data-ps-track="interactive_demo_started" data-ps-source="result"><?php esc_html_e( 'See What One of Those Jobs Becomes →', 'jcp-core' ); ?></a>
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

<!-- 6. DEMO -->
<section class="jcp-section ps-demo" id="ps-demo" aria-labelledby="ps-demo-title" data-ps-reveal data-ps-demo>
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'The interactive product demo', 'jcp-core' ); ?></p>
			<h2 id="ps-demo-title" class="ps-section-title"><?php esc_html_e( 'See what one completed job becomes.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'This is the point your crew closes the work. JobCapturePro turns that real job into proof without turning your technicians into marketers.', 'jcp-core' ); ?></p>
		</header>

		<div class="ps-demo-shell">
			<div class="ps-demo-progress" id="psDemoProgress" aria-hidden="true"></div>
			<div class="ps-demo-layout">
				<div class="ps-demo-copy">
					<p class="ps-demo-step-label" id="psDemoStepLabel"><?php esc_html_e( 'Step 1 of 8', 'jcp-core' ); ?></p>
					<h3 id="psDemoTitle"><?php esc_html_e( 'The job is finished.', 'jcp-core' ); ?></h3>
					<p id="psDemoBody"><?php esc_html_e( 'Without JCP, this is where the marketing often stops.', 'jcp-core' ); ?></p>
					<p class="ps-demo-detail"><span><?php esc_html_e( 'Source', 'jcp-core' ); ?></span> <strong id="psDemoSource"><?php esc_html_e( 'JCP App or connected workflow', 'jcp-core' ); ?></strong></p>
					<div class="ps-demo-controls">
						<button type="button" class="btn btn-secondary" id="psDemoPrev" disabled><?php esc_html_e( '← Previous', 'jcp-core' ); ?></button>
						<button type="button" class="btn btn-primary" id="psDemoNext"><?php esc_html_e( 'Next →', 'jcp-core' ); ?></button>
					</div>
				</div>
				<div class="ps-demo-canvas" id="psDemoCanvas" aria-live="polite"></div>
			</div>
		</div>

		<div class="ps-demo-payoff" id="psDemoPayoff" hidden>
			<p class="ps-demo-payoff__kicker" id="psDemoPayoffJobs"></p>
			<h3><?php esc_html_e( 'One job is useful. Hundreds become a system.', 'jcp-core' ); ?></h3>
			<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="demo_payoff" data-ps-track="trial_cta_clicked"><?php esc_html_e( 'Put My Next Job Through JobCapturePro →', 'jcp-core' ); ?></a>
		</div>
	</div>
</section>

<!-- 7. INTEGRATIONS -->
<section class="jcp-section ps-integrations" id="ps-integrations" data-ps-reveal>
	<div class="jcp-container ps-integrations__inner">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'It works with the way you already work', 'jcp-core' ); ?></p>
			<h2 class="ps-section-title"><?php esc_html_e( 'Already use a CRM or photo app? Good.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'If your team already captures consistent job information and photos, we don’t want to replace a workflow that’s working. JobCapturePro can connect with supported systems and use the work your crew already captures.', 'jcp-core' ); ?></p>
		</header>
		<ul class="ps-logo-row" aria-label="<?php esc_attr_e( 'Supported systems', 'jcp-core' ); ?>">
			<li class="ps-logo-mark ps-logo-mark--hcp" aria-label="Housecall Pro"><span>Housecall Pro</span></li>
			<li><img src="<?php echo esc_url( $integ . 'companycam.svg' ); ?>" alt="CompanyCam" width="140" height="36" loading="lazy" /></li>
			<li><img src="<?php echo esc_url( $integ . 'workiz.svg' ); ?>" alt="Workiz" width="120" height="36" loading="lazy" /></li>
			<li><img src="<?php echo esc_url( $integ . 'quickbooks.svg' ); ?>" alt="QuickBooks" width="130" height="36" loading="lazy" /></li>
		</ul>
		<p class="ps-integrations__note"><?php esc_html_e( 'Custom integrations available on qualifying plans.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 8. WHY -->
<section class="jcp-section ps-why" id="ps-why" data-ps-reveal>
	<div class="jcp-container ps-why__grid">
		<div class="ps-why__copy">
			<p class="ps-eyebrow"><?php esc_html_e( 'Why we built JobCapturePro', 'jcp-core' ); ?></p>
			<h2 class="ps-section-title"><?php esc_html_e( 'We spent 10 years generating contractor leads. We kept seeing the same waste.', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Contractors were spending thousands on websites, SEO, advertising, social media and reputation management while some of their best marketing material was disappearing into camera rolls, text threads and CRM records.', 'jcp-core' ); ?></p>
			<p><?php esc_html_e( 'Real jobs. Real photos. Real neighborhoods. Real customers. Real outcomes.', 'jcp-core' ); ?></p>
			<p><?php esc_html_e( 'JobCapturePro exists to connect the work contractors already do with the proof future customers want to see.', 'jcp-core' ); ?></p>
			<ul class="ps-why__stats">
				<li><strong>250K+</strong> <span><?php esc_html_e( 'contractor leads generated', 'jcp-core' ); ?></span></li>
				<li><strong>$150M+</strong> <span><?php esc_html_e( 'client revenue booked from those leads', 'jcp-core' ); ?></span></li>
			</ul>
		</div>
		<div class="ps-why__visual">
			<img src="<?php echo esc_url( $photo_crew ); ?>" alt="" width="560" height="420" loading="lazy" decoding="async" />
		</div>
	</div>
</section>

<!-- 9. REVIEWS -->
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
							<strong><?php echo esc_html( (string) ( $r['name'] ?? '' ) ); ?></strong>
							<em><?php echo esc_html( (string) ( $r['role'] ?? '' ) ); ?></em>
						</footer>
					</blockquote>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- 10. COMPACT PROOF REMINDER -->
<section class="jcp-section ps-proof-reminder" data-ps-reveal>
	<div class="jcp-container ps-proof-reminder__inner">
		<p class="ps-eyebrow"><?php esc_html_e( 'Real jobs → real proof → measurable change', 'jcp-core' ); ?></p>
		<div class="ps-proof-reminder__grids">
			<?php
			if ( function_exists( 'jcp_lf_case_render_grid' ) && function_exists( 'jcp_lf_case_grid_pattern' ) ) {
				$tri_map = $campaign . 'lf-map-triadelphia.jpg';
				$mon_map = $campaign . 'lf-map-monroe.jpg';
				?>
				<figure>
					<span><?php esc_html_e( 'Triadelphia · Before', 'jcp-core' ); ?></span>
					<?php jcp_lf_case_render_grid( jcp_lf_case_grid_pattern( 'before_blank' ), __( 'Triadelphia before', 'jcp-core' ), $tri_map ); ?>
				</figure>
				<figure>
					<span><?php esc_html_e( 'Triadelphia · After', 'jcp-core' ); ?></span>
					<?php jcp_lf_case_render_grid( jcp_lf_case_grid_pattern( 'after_fr_wv' ), __( 'Triadelphia after', 'jcp-core' ), $tri_map ); ?>
				</figure>
				<figure>
					<span><?php esc_html_e( 'Monroe · Before', 'jcp-core' ); ?></span>
					<?php jcp_lf_case_render_grid( jcp_lf_case_grid_pattern( 'before_blank' ), __( 'Monroe before', 'jcp-core' ), $mon_map ); ?>
				</figure>
				<figure>
					<span><?php esc_html_e( 'Monroe · After', 'jcp-core' ); ?></span>
					<?php jcp_lf_case_render_grid( jcp_lf_case_grid_pattern( 'after_fr_mi' ), __( 'Monroe after', 'jcp-core' ), $mon_map ); ?>
				</figure>
				<?php
			}
			?>
		</div>
		<!-- Placeholder: link to public case-study narrative if a dedicated public Acculevel page is published later. -->
		<a class="ps-link-secondary" href="<?php echo esc_url( home_url( '/#case-study' ) ); ?>" target="_blank" rel="noopener" data-ps-track="case_study_viewed" data-ps-source="reminder"><?php esc_html_e( 'See the full case study →', 'jcp-core' ); ?></a>
	</div>
</section>

<!-- 11. TRIAL -->
<section class="jcp-section ps-trial" id="ps-trial" data-ps-reveal>
	<div class="jcp-container ps-trial__inner">
		<p class="ps-eyebrow ps-eyebrow--light"><?php esc_html_e( 'The 14-day JobCapturePro Proof Sprint', 'jcp-core' ); ?></p>
		<h2 class="ps-trial__title"><?php esc_html_e( 'Your next 14 days are already full of marketing. Don’t let those jobs disappear too.', 'jcp-core' ); ?></h2>
		<p class="ps-trial__sub"><?php esc_html_e( 'Start with the jobs your company is already completing. We’ll help you get your first real JobCapturePro workflow live during the trial.', 'jcp-core' ); ?></p>
		<ul class="ps-trial__benefits">
			<li><?php esc_html_e( '14-day trial', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'No credit card required', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Guided activation', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Connect your existing workflow where supported', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Or use the JCP mobile app', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Publish your first proof', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'See the product working with your actual jobs', 'jcp-core' ); ?></li>
		</ul>
		<a class="btn btn-primary ps-btn-xl ps-btn-light" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="trial_section" data-ps-track="trial_cta_clicked"><?php esc_html_e( 'Start My 14-Day Proof Sprint →', 'jcp-core' ); ?></a>
		<p class="ps-micro ps-micro--light"><?php esc_html_e( 'No credit card · Guided setup · Cancel anytime', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 12. FAQ -->
<section class="jcp-section ps-faq" id="ps-faq" data-ps-reveal>
	<div class="jcp-container ps-faq__inner">
		<header class="ps-section-head">
			<h2 class="ps-section-title"><?php esc_html_e( 'Clear answers before you start.', 'jcp-core' ); ?></h2>
		</header>
		<div class="ps-faq-list">
			<?php
			$faqs = [
				[
					'q' => __( 'Does my crew have to learn another app?', 'jcp-core' ),
					'a' => __( 'It depends on your current workflow. If compatible CRM or photo data already exists, JobCapturePro can integrate into that workflow. Otherwise the JCP mobile app provides a simple capture flow for the field.', 'jcp-core' ),
				],
				[
					'q' => __( 'I already use CompanyCam or Housecall Pro. Why would I need JCP?', 'jcp-core' ),
					'a' => __( 'Those systems are excellent for capturing and storing job information operationally. JobCapturePro is built to turn completed-job proof into public marketing: website updates, Google activity, review opportunities, social content and local visibility.', 'jcp-core' ),
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
				<details class="ps-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $faq['q'] ); ?></summary>
					<p><?php echo esc_html( $faq['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- FINAL CTA -->
<section class="jcp-section ps-final" data-ps-reveal>
	<div class="ps-final__bg" aria-hidden="true">
		<img src="<?php echo esc_url( $photo_hvac ); ?>" alt="" width="1600" height="900" loading="lazy" decoding="async" />
	</div>
	<div class="jcp-container ps-final__inner">
		<h2 class="ps-final__title"><?php esc_html_e( 'You already paid to do the job. Make it help win the next one.', 'jcp-core' ); ?></h2>
		<div class="ps-final__actions">
			<a class="btn btn-primary ps-btn-xl ps-btn-light" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-source="final" data-ps-track="trial_cta_clicked"><?php esc_html_e( 'Start My 14-Day Proof Sprint →', 'jcp-core' ); ?></a>
			<a class="ps-link-secondary ps-link-secondary--light" href="#ps-demo"><?php esc_html_e( 'See One Job Transform Again', 'jcp-core' ); ?></a>
		</div>
	</div>
</section>
