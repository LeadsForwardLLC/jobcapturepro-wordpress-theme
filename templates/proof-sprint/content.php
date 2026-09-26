<?php
/**
 * Proof Sprint — direct Meta LP body (trial-first, no survey).
 *
 * @package JCP_Core
 *
 * @var string $trial_href
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$integ    = trailingslashit( get_template_directory_uri() ) . 'assets/integrations/';
$photo    = function_exists( 'jcp_proof_sprint_asset_url' )
	? jcp_proof_sprint_asset_url( 'jcp-campaign-job-proof-640.webp' )
	: $campaign . 'jcp-campaign-job-proof-640.webp';
$map_url  = get_template_directory_uri() . '/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';
$hcp_logo = $integ . 'housecall-pro.svg';
$cc_logo  = $integ . 'companycam.svg';

$reviews = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
$by_id   = [];
foreach ( $reviews as $r ) {
	$id = (string) ( $r['id'] ?? '' );
	if ( $id !== '' ) {
		$by_id[ $id ] = $r;
	}
}
$testimonial_ids = [ 'trent-ellison', 'brian-hardy', 'peter-bonk' ];
$testimonials    = [];
foreach ( $testimonial_ids as $tid ) {
	if ( isset( $by_id[ $tid ] ) ) {
		$testimonials[] = $by_id[ $tid ];
	}
}

$case_props = function_exists( 'jcp_proof_sprint_case_study_props' ) ? jcp_proof_sprint_case_study_props() : [];

$cta_label = __( 'Start My Free 14-Day Trial →', 'jcp-core' );
$cta_micro = __( 'No credit card required', 'jcp-core' );

$channels = [
	[
		'id'    => 'website',
		'title' => __( 'Website', 'jcp-core' ),
		'h'     => __( 'Turn completed jobs into real project and service-area content.', 'jcp-core' ),
		'b'     => __( 'Show future customers real work you’ve completed near them.', 'jcp-core' ),
	],
	[
		'id'    => 'google',
		'title' => __( 'Google', 'jcp-core' ),
		'h'     => __( 'Keep your Google Business Profile active with real job activity.', 'jcp-core' ),
		'b'     => __( 'Turn completed jobs into consistent local proof.', 'jcp-core' ),
	],
	[
		'id'    => 'social',
		'title' => __( 'Social', 'jcp-core' ),
		'h'     => __( 'Turn the same job into a ready-to-publish social post.', 'jcp-core' ),
		'b'     => __( 'No sitting in the truck trying to figure out what to write.', 'jcp-core' ),
	],
	[
		'id'    => 'reviews',
		'title' => __( 'Reviews', 'jcp-core' ),
		'h'     => __( 'Turn a finished job into a review opportunity.', 'jcp-core' ),
		'b'     => __( 'Create more consistent opportunities to ask at the right time.', 'jcp-core' ),
	],
	[
		'id'    => 'directory',
		'title' => __( 'Directory', 'jcp-core' ),
		'h'     => __( 'Give every completed job another place to be discovered.', 'jcp-core' ),
		'b'     => __( 'Show recent work, services and service-area activity.', 'jcp-core' ),
	],
];
?>

<!-- 1. HERO -->
<section class="jcp-section ps-hero" id="ps-hero" aria-labelledby="ps-hero-title">
	<div class="jcp-container ps-hero__grid">
		<div class="ps-hero__copy">
			<p class="ps-eyebrow ps-eyebrow--accent"><?php esc_html_e( 'For home-service contractors who already take job photos', 'jcp-core' ); ?></p>
			<h1 id="ps-hero-title" class="ps-hero__title"><?php esc_html_e( 'Turn every finished job into proof that helps win the next one.', 'jcp-core' ); ?></h1>
			<p class="ps-hero__sub"><?php esc_html_e( 'Your crew already takes the photos. JobCapturePro turns completed jobs into website content, Google activity, social posts, review opportunities and local proof — without giving your team another marketing job.', 'jcp-core' ); ?></p>
			<div class="ps-hero__actions">
				<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-placement="hero" data-ps-track="trial_cta"><?php echo esc_html( $cta_label ); ?></a>
			</div>
			<p class="ps-micro"><?php echo esc_html( $cta_micro ); ?></p>
			<p class="ps-hero__jump">
				<a class="ps-link-secondary" href="#ps-outputs"><?php esc_html_e( 'See what one finished job becomes ↓', 'jcp-core' ); ?></a>
			</p>
			<ul class="ps-outcome-chips" aria-label="<?php esc_attr_e( 'Outcomes', 'jcp-core' ); ?>">
				<li><?php esc_html_e( 'Get Found', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Build Trust', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Win the Next Job', 'jcp-core' ); ?></li>
			</ul>
		</div>
		<div class="ps-hero__visual" data-ps-theater aria-hidden="false">
			<div class="ps-theater" data-ps-reduced-ok>
				<div class="ps-theater__stage">
					<article class="ps-theater__job is-on">
						<div class="ps-theater__job-media">
							<img src="<?php echo esc_url( $photo ); ?>" alt="<?php esc_attr_e( 'Completed water heater replacement job', 'jcp-core' ); ?>" width="480" height="320" decoding="async" fetchpriority="high" data-no-lazy />
							<span class="ps-badge"><?php esc_html_e( 'Completed job', 'jcp-core' ); ?></span>
						</div>
						<div class="ps-theater__job-meta">
							<strong><?php esc_html_e( 'Water heater replacement', 'jcp-core' ); ?></strong>
							<span><?php esc_html_e( 'Austin, TX', 'jcp-core' ); ?></span>
						</div>
					</article>
					<div class="ps-theater__hub" aria-hidden="true">
						<span class="ps-theater__core">JCP</span>
					</div>
					<ul class="ps-theater__outputs" data-theater-outputs>
						<li data-out="website" class="is-on"><strong><?php esc_html_e( 'Website', 'jcp-core' ); ?></strong></li>
						<li data-out="google"><strong><?php esc_html_e( 'Google', 'jcp-core' ); ?></strong></li>
						<li data-out="social"><strong><?php esc_html_e( 'Social', 'jcp-core' ); ?></strong></li>
						<li data-out="reviews"><strong><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></strong></li>
						<li data-out="directory"><strong><?php esc_html_e( 'Directory', 'jcp-core' ); ?></strong></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 2. WORKFLOW REASSURANCE -->
<section class="jcp-section ps-workflow" id="ps-workflow" aria-labelledby="ps-workflow-title">
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'Keep your field workflow', 'jcp-core' ); ?></p>
			<h2 id="ps-workflow-title" class="ps-section-title"><?php esc_html_e( 'Your crew doesn’t need another marketing job.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'Already taking job photos in a supported system? Keep doing it. JobCapturePro can use supported workflows to turn the work your team already captures into marketing.', 'jcp-core' ); ?></p>
		</header>
		<ol class="ps-flow-steps" aria-label="<?php esc_attr_e( 'How crews keep working', 'jcp-core' ); ?>">
			<li><strong><?php esc_html_e( 'Finish job', 'jcp-core' ); ?></strong></li>
			<li><strong><?php esc_html_e( 'Take photos like normal', 'jcp-core' ); ?></strong></li>
			<li><strong><?php esc_html_e( 'JobCapturePro puts them to work', 'jcp-core' ); ?></strong></li>
		</ol>
		<ul class="ps-logo-row ps-logo-row--supported" aria-label="<?php esc_attr_e( 'Supported workflows', 'jcp-core' ); ?>">
			<li><img src="<?php echo esc_url( $hcp_logo ); ?>" alt="Housecall Pro" width="148" height="36" loading="lazy" /></li>
			<li><img src="<?php echo esc_url( $cc_logo ); ?>" alt="CompanyCam" width="140" height="36" loading="lazy" /></li>
			<li class="ps-logo-mark"><span><?php esc_html_e( 'JobCapturePro App', 'jcp-core' ); ?></span></li>
		</ul>
		<p class="ps-workflow__takeaway"><?php esc_html_e( 'Your techs do not become marketers.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 3. ONE JOB → FIVE OUTPUTS -->
<section class="jcp-section ps-outputs" id="ps-outputs" aria-labelledby="ps-outputs-title" data-ps-outputs>
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'One job. Multiple channels.', 'jcp-core' ); ?></p>
			<h2 id="ps-outputs-title" class="ps-section-title"><?php esc_html_e( 'One finished job. Five ways it can keep working.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'Instead of letting completed-job proof disappear, JobCapturePro turns it into marketing across the channels your future customers already use.', 'jcp-core' ); ?></p>
		</header>

		<div class="ps-outputs__layout">
			<div class="ps-outputs__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Marketing channels', 'jcp-core' ); ?>">
				<?php foreach ( $channels as $i => $ch ) : ?>
					<button
						type="button"
						class="ps-outputs__tab<?php echo 0 === $i ? ' is-active' : ''; ?>"
						role="tab"
						id="ps-tab-<?php echo esc_attr( $ch['id'] ); ?>"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						aria-controls="ps-panel-<?php echo esc_attr( $ch['id'] ); ?>"
						data-ps-channel="<?php echo esc_attr( $ch['id'] ); ?>"
					><?php echo esc_html( $ch['title'] ); ?></button>
				<?php endforeach; ?>
			</div>

			<div class="ps-outputs__panels">
				<?php foreach ( $channels as $i => $ch ) : ?>
					<div
						class="ps-outputs__panel<?php echo 0 === $i ? ' is-active' : ''; ?>"
						role="tabpanel"
						id="ps-panel-<?php echo esc_attr( $ch['id'] ); ?>"
						aria-labelledby="ps-tab-<?php echo esc_attr( $ch['id'] ); ?>"
						data-ps-panel="<?php echo esc_attr( $ch['id'] ); ?>"
						<?php echo 0 === $i ? '' : ' hidden'; ?>
					>
						<div class="ps-outputs__copy">
							<h3><?php echo esc_html( $ch['h'] ); ?></h3>
							<p><?php echo esc_html( $ch['b'] ); ?></p>
						</div>
						<div class="ps-outputs__preview" aria-hidden="true">
							<div class="ps-mock ps-mock--<?php echo esc_attr( $ch['id'] ); ?>">
								<div class="ps-mock__job">
									<img src="<?php echo esc_url( $photo ); ?>" alt="" width="280" height="180" loading="lazy" decoding="async" />
									<span><?php esc_html_e( 'Water heater replacement · Austin, TX', 'jcp-core' ); ?></span>
								</div>
								<?php if ( 'website' === $ch['id'] ) : ?>
									<div class="ps-mock__map">
										<img src="<?php echo esc_url( $map_url ); ?>" alt="" width="320" height="180" loading="lazy" decoding="async" />
										<span><?php esc_html_e( 'Service-area check-in', 'jcp-core' ); ?></span>
									</div>
								<?php elseif ( 'google' === $ch['id'] ) : ?>
									<div class="ps-mock__gbp">
										<strong><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></strong>
										<p><?php esc_html_e( 'Just finished a water heater replacement in Austin — fresh photos from the crew.', 'jcp-core' ); ?></p>
									</div>
								<?php elseif ( 'social' === $ch['id'] ) : ?>
									<div class="ps-mock__social">
										<strong><?php esc_html_e( 'Ready-to-publish post', 'jcp-core' ); ?></strong>
										<p><?php esc_html_e( 'Another job in the books. Real work. Real photos. Ready for social.', 'jcp-core' ); ?></p>
									</div>
								<?php elseif ( 'reviews' === $ch['id'] ) : ?>
									<div class="ps-mock__review">
										<img src="<?php echo esc_url( $campaign . 'ps-dummy-qr.png' ); ?>" alt="" width="72" height="72" loading="lazy" />
										<p><?php esc_html_e( 'Review ask at the right moment', 'jcp-core' ); ?></p>
									</div>
								<?php else : ?>
									<div class="ps-mock__dir">
										<strong><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></strong>
										<p><?php esc_html_e( 'Recent work · services · service-area activity', 'jcp-core' ); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<p class="ps-section-cta">
			<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-placement="outputs" data-ps-track="trial_cta"><?php echo esc_html( $cta_label ); ?></a>
			<span class="ps-micro"><?php esc_html_e( 'No credit card required · Start with one real job', 'jcp-core' ); ?></span>
		</p>
	</div>
</section>

<!-- 4. PROOF WASTE -->
<section class="jcp-section ps-waste" id="ps-waste" aria-labelledby="ps-waste-title">
	<div class="jcp-container">
		<header class="ps-section-head ps-section-head--light">
			<h2 id="ps-waste-title" class="ps-section-title"><?php esc_html_e( 'You already paid to create the proof. Then it disappears.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'You paid for the lead, the truck, the technician, the materials, the job and the photos — then most companies let that marketing value vanish into a camera roll, CRM attachment or folder.', 'jcp-core' ); ?></p>
		</header>
		<div class="ps-waste__compare">
			<div class="ps-waste__col ps-waste__col--before">
				<p class="ps-waste__label"><?php esc_html_e( 'Before', 'jcp-core' ); ?></p>
				<p class="ps-waste__start"><?php esc_html_e( 'Finished Job', 'jcp-core' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Camera Roll', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'CRM Attachment', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Shared Folder', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Forgotten', 'jcp-core' ); ?></li>
				</ul>
			</div>
			<div class="ps-waste__col ps-waste__col--after">
				<p class="ps-waste__label"><?php esc_html_e( 'With JobCapturePro', 'jcp-core' ); ?></p>
				<p class="ps-waste__start"><?php esc_html_e( 'Finished Job', 'jcp-core' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Reviews', 'jcp-core' ); ?></li>
					<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- 5. AUTHORITY -->
<section class="jcp-section ps-authority-block" id="ps-authority" aria-labelledby="ps-authority-title">
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'Why we built it', 'jcp-core' ); ?></p>
			<h2 id="ps-authority-title" class="ps-section-title"><?php esc_html_e( 'A decade helping contractors grow exposed the missing piece.', 'jcp-core' ); ?></h2>
			<p class="ps-section-sub"><?php esc_html_e( 'For more than a decade, the LeadsForward team has helped home-service companies generate demand. The same problem kept showing up after the job was already won: contractors were creating valuable proof every day — then barely using it. That led to JobCapturePro.', 'jcp-core' ); ?></p>
		</header>
		<ul class="ps-authority__stats ps-authority__stats--block">
			<li>
				<strong>10+</strong>
				<span><?php esc_html_e( 'Years helping home-service companies grow', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong>250K+</strong>
				<span><?php esc_html_e( 'Leads generated for contractor clients', 'jcp-core' ); ?></span>
			</li>
			<li>
				<strong>$150M+</strong>
				<span><?php esc_html_e( 'Client revenue booked from those leads', 'jcp-core' ); ?></span>
			</li>
		</ul>
		<p class="ps-authority__note"><?php esc_html_e( 'Figures reflect LeadsForward client history, not JobCapturePro alone.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 6. CASE STUDY -->
<section class="jcp-section ps-case" id="ps-proof" aria-labelledby="ps-case-title" data-ps-case>
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'Real local visibility case study', 'jcp-core' ); ?></p>
			<h2 id="ps-case-title" class="ps-section-title"><?php esc_html_e( 'From invisible to showing across the market.', 'jcp-core' ); ?></h2>
		</header>

		<?php
		$map_triad = $campaign . 'lf-map-triadelphia-640.webp';
		$before_ranks = function_exists( 'jcp_lf_case_grid_pattern' ) ? jcp_lf_case_grid_pattern( 'before_blank' ) : [];
		$after_ranks  = function_exists( 'jcp_lf_case_grid_pattern' ) ? jcp_lf_case_grid_pattern( 'after_fr_wv' ) : [];
		?>
		<div class="ps-case__primary" data-ps-case-primary>
			<div class="ps-case__ba">
				<figure class="ps-case__grid ps-case__grid--before">
					<span class="ps-case__tag"><?php esc_html_e( 'Before', 'jcp-core' ); ?></span>
					<?php
					if ( $before_ranks !== [] && function_exists( 'jcp_lf_case_render_grid' ) ) {
						jcp_lf_case_render_grid( $before_ranks, __( 'Before: not showing in the Google Maps 3-Pack', 'jcp-core' ), $map_triad );
					}
					?>
					<figcaption>0% SoLV</figcaption>
				</figure>
				<figure class="ps-case__grid ps-case__grid--after">
					<span class="ps-case__tag"><?php esc_html_e( 'After', 'jcp-core' ); ?></span>
					<?php
					if ( $after_ranks !== [] && function_exists( 'jcp_lf_case_render_grid' ) ) {
						jcp_lf_case_render_grid( $after_ranks, __( 'After: showing across more of the tracked market', 'jcp-core' ), $map_triad );
					}
					?>
					<figcaption>84–100% SoLV</figcaption>
				</figure>
			</div>
			<p class="ps-case__metric"><strong>0% → 84–100%</strong></p>
			<p class="ps-case__support"><?php esc_html_e( 'Top-3 grid visibility across four tracked keyword/market combinations in ~12 weeks.', 'jcp-core' ); ?></p>
			<p class="ps-case__attr"><?php esc_html_e( 'LeadsForward + JobCapturePro local-search strategy', 'jcp-core' ); ?></p>
			<p class="ps-case__disclaimer"><?php esc_html_e( 'Past performance does not guarantee future rankings.', 'jcp-core' ); ?></p>
		</div>

		<details class="ps-case__more" data-ps-case-more>
			<summary><?php esc_html_e( 'View the full case study →', 'jcp-core' ); ?></summary>
			<div class="ps-case__full">
				<?php
				if ( $case_props !== [] && function_exists( 'jcp_niche_render_local_rank_case_study' ) ) {
					$full_case = array_merge(
						$case_props,
						[
							'section_id'    => 'ps-proof-full',
							'show_eyebrow'  => false,
							'show_headline' => false,
							'show_body'     => true,
							'show_cta'      => false,
						]
					);
					jcp_niche_render_local_rank_case_study( $full_case, 'proof_sprint' );
				}
				?>
			</div>
		</details>
	</div>
</section>

<!-- 7. HOW IT WORKS -->
<section class="jcp-section ps-how" id="ps-how" aria-labelledby="ps-how-title">
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'Start with one job', 'jcp-core' ); ?></p>
			<h2 id="ps-how-title" class="ps-section-title"><?php esc_html_e( 'Getting started should take one completed job.', 'jcp-core' ); ?></h2>
		</header>
		<ol class="ps-how__steps">
			<li>
				<span class="ps-how__num">1</span>
				<h3><?php esc_html_e( 'Connect your workflow', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Use the JobCapturePro app or connect a supported workflow your team already uses.', 'jcp-core' ); ?></p>
			</li>
			<li>
				<span class="ps-how__num">2</span>
				<h3><?php esc_html_e( 'Complete one real job', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'Your crew captures the photos like normal.', 'jcp-core' ); ?></p>
			</li>
			<li>
				<span class="ps-how__num">3</span>
				<h3><?php esc_html_e( 'Put the proof to work', 'jcp-core' ); ?></h3>
				<p><?php esc_html_e( 'JobCapturePro turns that completed job into useful marketing across the channels you enable.', 'jcp-core' ); ?></p>
			</li>
		</ol>
		<p class="ps-section-cta">
			<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-placement="how" data-ps-track="trial_cta"><?php esc_html_e( 'Start With My Next Job →', 'jcp-core' ); ?></a>
			<span class="ps-micro"><?php esc_html_e( '14 days free · No credit card required', 'jcp-core' ); ?></span>
		</p>
	</div>
</section>

<!-- 8. TESTIMONIALS -->
<?php if ( $testimonials !== [] ) : ?>
<section class="jcp-section ps-reviews" id="ps-reviews" aria-labelledby="ps-reviews-title">
	<div class="jcp-container">
		<header class="ps-section-head">
			<p class="ps-eyebrow"><?php esc_html_e( 'Customer proof', 'jcp-core' ); ?></p>
			<h2 id="ps-reviews-title" class="ps-section-title"><?php esc_html_e( 'Real operators. Real feedback.', 'jcp-core' ); ?></h2>
		</header>
		<div class="ps-review-grid ps-review-grid--three">
			<?php foreach ( $testimonials as $r ) : ?>
				<blockquote class="ps-review-card">
					<div class="ps-stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'jcp-core' ); ?>">★★★★★</div>
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
	</div>
</section>
<?php endif; ?>

<!-- 9. WHO IT'S FOR -->
<section class="jcp-section ps-fit" id="ps-fit" aria-labelledby="ps-fit-title">
	<div class="jcp-container ps-fit__inner">
		<h2 id="ps-fit-title" class="ps-section-title"><?php esc_html_e( 'JCP is built for companies already doing real work every week.', 'jcp-core' ); ?></h2>
		<ul class="ps-fit__list">
			<li><?php esc_html_e( 'Your crew already takes job photos', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'You complete jobs consistently', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'You want those jobs working harder after completion', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'You want more consistent website, Google, social and review proof', 'jcp-core' ); ?></li>
		</ul>
		<p class="ps-fit__close"><?php esc_html_e( 'If you’re already creating the proof, JobCapturePro can help put it to work.', 'jcp-core' ); ?></p>
	</div>
</section>

<!-- 10. LOW-RISK TRIAL -->
<section class="jcp-section ps-trial" id="ps-trial" aria-labelledby="ps-trial-title" data-ps-trial-section>
	<div class="jcp-container ps-trial__layout">
		<div class="ps-trial__copy">
			<p class="ps-eyebrow ps-eyebrow--light"><?php esc_html_e( 'Your 14-day trial', 'jcp-core' ); ?></p>
			<h2 id="ps-trial-title" class="ps-trial__title"><?php esc_html_e( 'Your only goal: put one real completed job to work.', 'jcp-core' ); ?></h2>
			<p class="ps-trial__sub"><?php esc_html_e( 'Connect the easiest workflow, use one real job, and see what JobCapturePro can turn it into.', 'jcp-core' ); ?></p>
			<ol class="ps-trial__timeline">
				<li><strong><?php esc_html_e( 'Day 1', 'jcp-core' ); ?></strong> <?php esc_html_e( 'Connect your workflow', 'jcp-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Next completed job', 'jcp-core' ); ?></strong> <?php esc_html_e( 'Capture or import it', 'jcp-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Then', 'jcp-core' ); ?></strong> <?php esc_html_e( 'See what JCP creates', 'jcp-core' ); ?></li>
			</ol>
		</div>
		<div class="ps-trial__panel">
			<a class="btn btn-primary ps-btn-xl ps-btn-light" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-placement="trial_section" data-ps-track="trial_cta"><?php echo esc_html( $cta_label ); ?></a>
			<p class="ps-micro ps-micro--light"><?php echo esc_html( $cta_micro ); ?></p>
		</div>
	</div>
</section>

<!-- 11. FAQ -->
<section class="jcp-section ps-faq" id="ps-faq" aria-labelledby="ps-faq-title">
	<div class="jcp-container">
		<header class="ps-section-head">
			<h2 id="ps-faq-title" class="ps-section-title"><?php esc_html_e( 'Clear answers before you start.', 'jcp-core' ); ?></h2>
		</header>
		<div class="ps-faq__list">
			<?php
			$faqs = [
				[
					'q' => __( 'Will my technicians have to use another app?', 'jcp-core' ),
					'a' => __( 'Not necessarily. If a supported workflow already captures the job photos and information JCP needs, your crew can keep working the way they already do. Teams can also use the JobCapturePro app when that fits better.', 'jcp-core' ),
				],
				[
					'q' => __( 'Can JobCapturePro work with the CRM or photo system we already use?', 'jcp-core' ),
					'a' => __( 'Yes for supported workflows such as Housecall Pro and CompanyCam. Availability depends on your setup and plan. If your system isn’t listed, use the JobCapturePro app or ask us about current options.', 'jcp-core' ),
				],
				[
					'q' => __( 'What exactly happens to a completed job?', 'jcp-core' ),
					'a' => __( 'Depending on the channels you enable, a completed job can become website content, Google Business Profile activity, social-ready posts, review opportunities, and directory/local proof.', 'jcp-core' ),
				],
				[
					'q' => __( 'Do I need a WordPress website?', 'jcp-core' ),
					'a' => __( 'Many customers use the WordPress plugin for native website proof. If you’re on another platform, talk with us about current embed and publishing options for your setup.', 'jcp-core' ),
				],
				[
					'q' => __( 'Does JobCapturePro guarantee Google rankings?', 'jcp-core' ),
					'a' => __( 'No. Rankings depend on many factors. JCP helps create and publish real job activity and local proof that can support visibility and trust. Past results do not guarantee future rankings.', 'jcp-core' ),
				],
				[
					'q' => __( 'How quickly can I get my first job live?', 'jcp-core' ),
					'a' => __( 'Most teams can connect a workflow and publish from one real completed job during the first days of the trial. Exact timing depends on your workflow and channels.', 'jcp-core' ),
				],
				[
					'q' => __( 'Do I need a credit card?', 'jcp-core' ),
					'a' => __( 'No. The 14-day trial does not require a credit card to start.', 'jcp-core' ),
				],
				[
					'q' => __( 'What happens after the 14-day trial?', 'jcp-core' ),
					'a' => __( 'You can continue on a paid plan if JobCapturePro is a fit, or cancel. During the trial we focus on getting a real workflow live with your jobs so you can evaluate with actual work.', 'jcp-core' ),
				],
			];
			foreach ( $faqs as $i => $faq ) :
				?>
				<details class="ps-faq__item" data-ps-faq="<?php echo esc_attr( (string) $i ); ?>"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $faq['q'] ); ?></summary>
					<p><?php echo esc_html( $faq['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 12. FINAL CTA -->
<section class="jcp-section ps-final ps-final--navy" id="ps-final" aria-labelledby="ps-final-title">
	<div class="jcp-container ps-final__inner">
		<h2 id="ps-final-title" class="ps-final__title"><?php esc_html_e( 'Your next job is going to create proof either way.', 'jcp-core' ); ?></h2>
		<p class="ps-final__sub"><?php esc_html_e( 'You can let it disappear — or put it to work.', 'jcp-core' ); ?></p>
		<a class="btn btn-primary ps-btn-xl" href="<?php echo esc_url( $trial_href ); ?>" data-ps-trial data-ps-placement="final" data-ps-track="trial_cta"><?php echo esc_html( $cta_label ); ?></a>
		<p class="ps-micro ps-micro--light"><?php echo esc_html( $cta_micro ); ?></p>
	</div>
</section>
