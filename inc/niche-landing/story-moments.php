<?php
/**
 * Story moments — interactive deck beats for campaign / marketing landings.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default props for the story_moments block.
 *
 * @return array<string, mixed>
 */
function jcp_niche_story_moments_defaults(): array {
	$campaign_uri = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';

	return [
		'section_id'          => 'see-how-it-works',
		'eyebrow'             => __( 'What the job can do next', 'jcp-core' ),
		'headline'            => __( 'What Your Finished Jobs Can Start Doing For You', 'jcp-core' ),
		'body'                => __( 'Turn work you\'ve already completed into proof homeowners can actually see.', 'jcp-core' ),
		'show_eyebrow'        => true,
		'show_headline'       => true,
		'show_body'           => true,
		'show_publish'        => true,
		'show_reviews'        => true,
		'publish_headline'    => __( 'Turn finished work into public proof.', 'jcp-core' ),
		'publish_body'        => __( 'Tap a channel — see the same completed job show up where homeowners look.', 'jcp-core' ),
		'publish_photo_url'   => $campaign_uri . 'jcp-campaign-hvac-capture.jpg',
		'publish_photo_alt'   => __( 'Technician photographing a completed HVAC job', 'jcp-core' ),
		'publish_preview_url' => $campaign_uri . 'jcp-campaign-job-proof.jpg',
		'reviews_headline'    => __( 'Ask while they’re still happy.', 'jcp-core' ),
		'reviews_body'        => __( 'Show the QR before you leave — while the experience is still fresh.', 'jcp-core' ),
		'reviews_photo_url'   => $campaign_uri . 'jcp-campaign-face-owner.jpg',
		'reviews_photo_alt'   => __( 'Owner on site after a completed job', 'jcp-core' ),
		'reviews_quote'       => __( '“Tech was on time and cleaned up. 5 stars.”', 'jcp-core' ),
		'cta_primary'         => [
			'label' => __( 'See JobCapturePro On My Business', 'jcp-core' ),
			'url'   => '/demo/',
		],
		'cta_note'            => __( 'Free demo · ~2 min · No card', 'jcp-core' ),
		'show_cta'            => true,
	];
}

/**
 * Render story moments section.
 *
 * @param array<string, mixed> $props    Block props.
 * @param string               $page_key Page key (unused; editable paths are fixed).
 */
function jcp_niche_render_story_moments( array $props, string $page_key = '' ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$d     = jcp_niche_story_moments_defaults();
	$props = array_merge( $d, $props );

	$raw_id     = (string) ( $props['section_id'] ?? 'see-how-it-works' );
	$section_id = function_exists( 'sanitize_title' )
		? sanitize_title( $raw_id )
		: preg_replace( '/[^a-z0-9\-]+/', '-', strtolower( $raw_id ) );
	$path       = 'story_moments';

	$icon = static function ( string $name ): string {
		return function_exists( 'jcp_core_icon' ) ? (string) jcp_core_icon( $name ) : '';
	};

	$job_photo = (string) $props['publish_preview_url'];
	$src_photo = (string) $props['publish_photo_url'];
	$face      = (string) $props['reviews_photo_url'];

	$channels = [
		[
			'key'   => 'website',
			'label' => __( 'Website', 'jcp-core' ),
			'icon'  => 'globe',
		],
		[
			'key'   => 'google',
			'label' => __( 'Google', 'jcp-core' ),
			'icon'  => 'map-pin',
		],
		[
			'key'   => 'social',
			'label' => __( 'Social', 'jcp-core' ),
			'icon'  => 'message-square',
		],
		[
			'key'   => 'directory',
			'label' => __( 'Directory', 'jcp-core' ),
			'icon'  => 'map',
		],
	];

	$cta       = is_array( $props['cta_primary'] ?? null ) ? $props['cta_primary'] : $d['cta_primary'];
	$cta_label = trim( (string) ( $cta['label'] ?? '' ) );
	$cta_url   = (string) ( $cta['url'] ?? '/demo/' );
	?>
	<section
		class="jcp-story-moments"
		id="<?php echo esc_attr( $section_id ); ?>"
		data-jcp-story-moments
		<?php jcp_niche_editable_attr( $path ); ?>
	>
		<div class="jcp-container jcp-story-moments__inner">
			<header class="jcp-story-moments__intro">
				<?php if ( ! empty( $props['show_eyebrow'] ) && trim( (string) $props['eyebrow'] ) !== '' ) : ?>
					<p class="jcp-story-moments__eyebrow"<?php jcp_niche_editable_attr( $path . '.eyebrow' ); ?>>
						<?php echo esc_html( (string) $props['eyebrow'] ); ?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $props['show_headline'] ) && trim( (string) $props['headline'] ) !== '' ) : ?>
					<h2 class="jcp-story-moments__headline"<?php jcp_niche_editable_attr( $path . '.headline' ); ?>>
						<?php echo esc_html( (string) $props['headline'] ); ?>
					</h2>
				<?php endif; ?>
				<?php if ( ! empty( $props['show_body'] ) && trim( (string) $props['body'] ) !== '' ) : ?>
					<p class="jcp-story-moments__body"<?php jcp_niche_editable_attr( $path . '.body' ); ?>>
						<?php echo esc_html( (string) $props['body'] ); ?>
					</p>
				<?php endif; ?>
			</header>

			<div class="jcp-story-moments__grid">
				<?php if ( ! empty( $props['show_publish'] ) ) : ?>
					<article class="jcp-story-moment jcp-story-moment--publish">
						<header class="jcp-story-moment__header">
							<p class="jcp-story-moment__kicker"><?php esc_html_e( 'Same job photo', 'jcp-core' ); ?></p>
							<h3 class="jcp-story-moment__title"<?php jcp_niche_editable_attr( $path . '.publish_headline' ); ?>>
								<?php echo esc_html( (string) $props['publish_headline'] ); ?>
							</h3>
							<p class="jcp-story-moment__lead"<?php jcp_niche_editable_attr( $path . '.publish_body' ); ?>>
								<?php echo esc_html( (string) $props['publish_body'] ); ?>
							</p>
						</header>

						<div class="jcp-story-publish" data-jcp-story-publish>
							<div class="jcp-story-publish__source">
								<img
									src="<?php echo esc_url( $src_photo ); ?>"
									alt="<?php echo esc_attr( (string) $props['publish_photo_alt'] ); ?>"
									width="72"
									height="72"
									loading="lazy"
									decoding="async"
								/>
								<div>
									<strong><?php esc_html_e( 'HVAC install · Austin, TX', 'jcp-core' ); ?></strong>
									<span><?php esc_html_e( 'Captured on site · Ready for connected channels', 'jcp-core' ); ?></span>
								</div>
							</div>

							<div
								class="jcp-story-publish__tiles"
								role="tablist"
								aria-label="<?php esc_attr_e( 'Preview channels', 'jcp-core' ); ?>"
							>
								<?php foreach ( $channels as $i => $ch ) : ?>
									<button
										type="button"
										class="jcp-story-publish__tile<?php echo 0 === (int) $i ? ' is-live' : ''; ?>"
										role="tab"
										id="jcp-sm-tab-<?php echo esc_attr( $ch['key'] ); ?>"
										data-channel="<?php echo esc_attr( $ch['key'] ); ?>"
										aria-controls="jcp-sm-panel-<?php echo esc_attr( $ch['key'] ); ?>"
										aria-selected="<?php echo 0 === (int) $i ? 'true' : 'false'; ?>"
									>
										<span class="jcp-story-publish__tile-icon">
											<?php if ( $icon( $ch['icon'] ) ) : ?>
												<img src="<?php echo esc_url( $icon( $ch['icon'] ) ); ?>" alt="" width="16" height="16" />
											<?php endif; ?>
										</span>
										<span class="jcp-story-publish__tile-label"><?php echo esc_html( $ch['label'] ); ?></span>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="jcp-story-publish__stage" data-publish-preview>
								<!-- Website -->
								<div
									class="jcp-sm-panel is-active"
									id="jcp-sm-panel-website"
									data-channel-panel="website"
									role="tabpanel"
									aria-labelledby="jcp-sm-tab-website"
								>
									<div class="jcp-sm-browser">
										<div class="jcp-sm-browser__bar" aria-hidden="true">
											<span></span><span></span><span></span>
											<div class="jcp-sm-browser__url">yourbusiness.com/recent-work</div>
										</div>
										<div class="jcp-sm-browser__body">
											<p class="jcp-sm-browser__heading"><?php esc_html_e( 'Recent work', 'jcp-core' ); ?></p>
											<div class="jcp-sm-job-card">
												<img src="<?php echo esc_url( $job_photo ); ?>" alt="" width="120" height="90" loading="lazy" decoding="async" />
												<div>
													<strong><?php esc_html_e( 'Completed HVAC install', 'jcp-core' ); ?></strong>
													<span><?php esc_html_e( '1242 Mason Rd · Austin, TX', 'jcp-core' ); ?></span>
													<p><?php esc_html_e( 'New system install with before/after proof from the job site.', 'jcp-core' ); ?></p>
													<em><?php esc_html_e( 'Published to your website', 'jcp-core' ); ?></em>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Google -->
								<div
									class="jcp-sm-panel"
									id="jcp-sm-panel-google"
									data-channel-panel="google"
									role="tabpanel"
									aria-labelledby="jcp-sm-tab-google"
									hidden
								>
									<div class="jcp-sm-gbp">
										<div class="jcp-sm-gbp__brand">
											<?php if ( $icon( 'map-pin' ) ) : ?>
												<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="16" height="16" />
											<?php endif; ?>
											<div>
												<strong><?php esc_html_e( 'Google Business Profile', 'jcp-core' ); ?></strong>
												<span><?php esc_html_e( 'Your Business · Update', 'jcp-core' ); ?></span>
											</div>
										</div>
										<img class="jcp-sm-gbp__photo" src="<?php echo esc_url( $job_photo ); ?>" alt="" width="400" height="180" loading="lazy" decoding="async" />
										<div class="jcp-sm-gbp__copy">
											<strong><?php esc_html_e( 'Just finished another HVAC install in Austin', 'jcp-core' ); ?></strong>
											<p><?php esc_html_e( 'Fresh job proof from today’s completed work — ready for homeowners nearby.', 'jcp-core' ); ?></p>
										</div>
										<span class="jcp-sm-gbp__meta"><?php esc_html_e( 'Posted to Google · Today', 'jcp-core' ); ?></span>
									</div>
								</div>

								<!-- Social -->
								<div
									class="jcp-sm-panel"
									id="jcp-sm-panel-social"
									data-channel-panel="social"
									role="tabpanel"
									aria-labelledby="jcp-sm-tab-social"
									hidden
								>
									<div class="jcp-sm-social">
										<div class="jcp-sm-social__head">
											<img class="jcp-sm-social__avatar" src="<?php echo esc_url( $src_photo ); ?>" alt="" width="34" height="34" loading="lazy" decoding="async" />
											<div>
												<strong><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></strong>
												<span><?php esc_html_e( 'Just now · Austin, TX', 'jcp-core' ); ?></span>
											</div>
										</div>
										<p class="jcp-sm-social__copy"><?php esc_html_e( 'Another job wrapped. New HVAC install done right — proof from the field.', 'jcp-core' ); ?></p>
										<img class="jcp-sm-social__photo" src="<?php echo esc_url( $job_photo ); ?>" alt="" width="400" height="200" loading="lazy" decoding="async" />
										<div class="jcp-sm-social__reactions" aria-hidden="true">
											<span><?php esc_html_e( 'Like', 'jcp-core' ); ?></span>
											<span><?php esc_html_e( 'Comment', 'jcp-core' ); ?></span>
											<span><?php esc_html_e( 'Share', 'jcp-core' ); ?></span>
										</div>
									</div>
								</div>

								<!-- Directory -->
								<div
									class="jcp-sm-panel"
									id="jcp-sm-panel-directory"
									data-channel-panel="directory"
									role="tabpanel"
									aria-labelledby="jcp-sm-tab-directory"
									hidden
								>
									<div class="jcp-sm-directory">
										<p class="jcp-sm-directory__label"><?php esc_html_e( 'JobCapturePro Directory', 'jcp-core' ); ?></p>
										<div class="jcp-sm-directory__card" role="article">
											<div class="jcp-sm-directory__head">
												<img class="jcp-sm-directory__avatar" src="<?php echo esc_url( $job_photo ); ?>" alt="" width="40" height="40" loading="lazy" decoding="async" />
												<div>
													<strong><?php esc_html_e( 'Your Business', 'jcp-core' ); ?></strong>
													<span>
														<?php if ( $icon( 'map-pin' ) ) : ?>
															<img src="<?php echo esc_url( $icon( 'map-pin' ) ); ?>" alt="" width="12" height="12" />
														<?php endif; ?>
														<?php esc_html_e( 'Austin, TX', 'jcp-core' ); ?>
													</span>
												</div>
											</div>
											<div class="jcp-sm-directory__meta">
												<span><?php esc_html_e( '12 verified jobs', 'jcp-core' ); ?></span>
												<span aria-hidden="true">·</span>
												<span><?php esc_html_e( 'Active today', 'jcp-core' ); ?></span>
											</div>
											<div class="jcp-sm-directory__proof">
												<img src="<?php echo esc_url( $job_photo ); ?>" alt="" width="80" height="60" loading="lazy" decoding="async" />
												<div>
													<strong><?php esc_html_e( 'Latest: HVAC install', 'jcp-core' ); ?></strong>
													<span><?php esc_html_e( 'Added from today’s completed job', 'jcp-core' ); ?></span>
												</div>
											</div>
											<div class="jcp-sm-directory__rating" aria-hidden="true">
												<span class="jcp-sm-directory__stars">★★★★★</span>
												<span><?php esc_html_e( '5.0 from recent reviews', 'jcp-core' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</article>
				<?php endif; ?>

				<?php if ( ! empty( $props['show_reviews'] ) ) : ?>
					<article class="jcp-story-moment jcp-story-moment--reviews">
						<header class="jcp-story-moment__header">
							<p class="jcp-story-moment__kicker"><?php esc_html_e( 'On-site review ask', 'jcp-core' ); ?></p>
							<h3 class="jcp-story-moment__title"<?php jcp_niche_editable_attr( $path . '.reviews_headline' ); ?>>
								<?php echo esc_html( (string) $props['reviews_headline'] ); ?>
							</h3>
							<p class="jcp-story-moment__lead"<?php jcp_niche_editable_attr( $path . '.reviews_body' ); ?>>
								<?php echo esc_html( (string) $props['reviews_body'] ); ?>
							</p>
						</header>

						<div class="jcp-story-reviews">
							<div class="jcp-story-reviews__scene">
								<div class="jcp-story-reviews__photo">
									<img
										src="<?php echo esc_url( $face ); ?>"
										alt="<?php echo esc_attr( (string) $props['reviews_photo_alt'] ); ?>"
										width="480"
										height="320"
										loading="lazy"
										decoding="async"
									/>
								</div>
								<div class="jcp-story-reviews__ask" data-jcp-story-reviews-card>
									<div class="jcp-story-reviews__qr-block" aria-hidden="true">
										<?php if ( $icon( 'qr-code' ) ) : ?>
											<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="56" height="56" />
										<?php endif; ?>
										<span><?php esc_html_e( 'Scan to review', 'jcp-core' ); ?></span>
									</div>
									<div class="jcp-story-reviews__result">
										<div class="jcp-story-reviews__stars" aria-hidden="true">★★★★★</div>
										<p<?php jcp_niche_editable_attr( $path . '.reviews_quote' ); ?>>
											<?php echo esc_html( (string) $props['reviews_quote'] ); ?>
										</p>
									</div>
								</div>
							</div>

							<p class="jcp-story-reviews__flow" aria-label="<?php esc_attr_e( 'Review flow', 'jcp-core' ); ?>">
								<span><?php esc_html_e( 'Ask in person', 'jcp-core' ); ?></span>
								<span class="jcp-story-reviews__flow-sep" aria-hidden="true">→</span>
								<span><?php esc_html_e( 'Show the QR', 'jcp-core' ); ?></span>
								<span class="jcp-story-reviews__flow-sep" aria-hidden="true">→</span>
								<span><?php esc_html_e( 'Review goes live', 'jcp-core' ); ?></span>
							</p>
						</div>
					</article>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $props['show_cta'] ) && $cta_label !== '' ) : ?>
				<div class="jcp-story-moments__cta">
					<a
						class="btn btn-primary"
						href="<?php echo esc_url( $cta_url ); ?>"
						<?php
						if ( function_exists( 'jcp_niche_editable_link_paths' ) ) {
							jcp_niche_editable_link_paths( $path . '.cta_primary.label', $path . '.cta_primary.url' );
						}
						if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
							jcp_niche_cta_tracking_attr( $cta_url, 'story_moments_cta', $cta_label );
						}
						?>
					><?php echo esc_html( $cta_label ); ?></a>
					<?php if ( trim( (string) ( $props['cta_note'] ?? '' ) ) !== '' ) : ?>
						<p class="jcp-story-moments__cta-note"<?php jcp_niche_editable_attr( $path . '.cta_note' ); ?>>
							<?php echo esc_html( (string) $props['cta_note'] ); ?>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
