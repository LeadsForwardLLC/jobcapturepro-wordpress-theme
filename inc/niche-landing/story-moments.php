<?php
/**
 * Story moments — interactive deck beats for campaign / marketing landings.
 * Port of the best pre-demo survey slides (publish channels + on-site reviews).
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
		'eyebrow'             => __( 'See how it works', 'jcp-core' ),
		'headline'            => __( 'One finished job. Proof everywhere.', 'jcp-core' ),
		'body'                => __( 'These are the moments that win the next call — without another marketing task on your plate.', 'jcp-core' ),
		'show_eyebrow'        => true,
		'show_headline'       => true,
		'show_body'           => true,
		'show_publish'        => true,
		'show_reviews'        => true,
		'publish_headline'    => __( 'One photo publishes everywhere.', 'jcp-core' ),
		'publish_body'        => __( 'Tap a channel — watch one job photo become live proof.', 'jcp-core' ),
		'publish_photo_url'   => $campaign_uri . 'jcp-campaign-hvac-capture.jpg',
		'publish_photo_alt'   => __( 'Technician photographing a completed HVAC job', 'jcp-core' ),
		'publish_preview_url' => $campaign_uri . 'jcp-campaign-job-proof.jpg',
		'reviews_headline'    => __( 'Ask for reviews while it still matters.', 'jcp-core' ),
		'reviews_body'        => __( 'On-site QR. Customer is still happy. Review lands before your truck leaves.', 'jcp-core' ),
		'reviews_photo_url'   => $campaign_uri . 'jcp-campaign-face-owner.jpg',
		'reviews_photo_alt'   => __( 'Owner on site after a completed job', 'jcp-core' ),
		'reviews_quote'       => __( '“Tech was on time and cleaned up. 5 stars.”', 'jcp-core' ),
		'cta_primary'         => [
			'label' => __( 'See JobCapturePro On My Business', 'jcp-core' ),
			'url'   => '/demo/',
		],
		'cta_note'            => __( 'Trade + work email · About 2 minutes · No credit card', 'jcp-core' ),
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

	$channels = [
		[
			'key'   => 'website',
			'label' => __( 'Website', 'jcp-core' ),
			'icon'  => 'layout-list',
			'live'  => __( 'Live on your website', 'jcp-core' ),
		],
		[
			'key'   => 'google',
			'label' => __( 'Google', 'jcp-core' ),
			'icon'  => 'map-pin',
			'live'  => __( 'Live on Google Business Profile', 'jcp-core' ),
		],
		[
			'key'   => 'social',
			'label' => __( 'Social', 'jcp-core' ),
			'icon'  => 'message-square',
			'live'  => __( 'Ready for social', 'jcp-core' ),
		],
		[
			'key'   => 'directory',
			'label' => __( 'Directory', 'jcp-core' ),
			'icon'  => 'map',
			'live'  => __( 'Listed in your directory', 'jcp-core' ),
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
						<h3 class="jcp-story-moment__title"<?php jcp_niche_editable_attr( $path . '.publish_headline' ); ?>>
							<?php echo esc_html( (string) $props['publish_headline'] ); ?>
						</h3>
						<p class="jcp-story-moment__lead"<?php jcp_niche_editable_attr( $path . '.publish_body' ); ?>>
							<?php echo esc_html( (string) $props['publish_body'] ); ?>
						</p>

						<div class="jcp-story-publish" data-jcp-story-publish>
							<div class="jcp-story-publish__photo">
								<img
									src="<?php echo esc_url( (string) $props['publish_photo_url'] ); ?>"
									alt="<?php echo esc_attr( (string) $props['publish_photo_alt'] ); ?>"
									width="480"
									height="280"
									loading="lazy"
									decoding="async"
								/>
								<span class="jcp-story-publish__photo-label">
									<?php if ( $icon( 'camera' ) ) : ?>
										<img src="<?php echo esc_url( $icon( 'camera' ) ); ?>" alt="" width="14" height="14" />
									<?php endif; ?>
									<?php esc_html_e( 'Job photo', 'jcp-core' ); ?>
								</span>
							</div>

							<div class="jcp-story-publish__tiles" role="group" aria-label="<?php esc_attr_e( 'Where it publishes', 'jcp-core' ); ?>">
								<?php foreach ( $channels as $ch ) : ?>
									<button
										type="button"
										class="jcp-story-publish__tile"
										data-channel="<?php echo esc_attr( $ch['key'] ); ?>"
										data-live-label="<?php echo esc_attr( $ch['live'] ); ?>"
										aria-pressed="false"
									>
										<span class="jcp-story-publish__tile-icon">
											<?php if ( $icon( $ch['icon'] ) ) : ?>
												<img src="<?php echo esc_url( $icon( $ch['icon'] ) ); ?>" alt="" width="20" height="20" />
											<?php endif; ?>
										</span>
										<span class="jcp-story-publish__tile-label"><?php echo esc_html( $ch['label'] ); ?></span>
										<span class="jcp-story-publish__tile-status"><?php esc_html_e( 'Publish', 'jcp-core' ); ?></span>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="jcp-story-publish__preview" data-publish-preview hidden>
								<img
									src="<?php echo esc_url( (string) $props['publish_preview_url'] ); ?>"
									alt="<?php esc_attr_e( 'Finished job as marketing proof', 'jcp-core' ); ?>"
									width="480"
									height="160"
									loading="lazy"
									decoding="async"
								/>
								<p data-publish-preview-label><?php esc_html_e( 'Live on your website', 'jcp-core' ); ?></p>
							</div>
						</div>
					</article>
				<?php endif; ?>

				<?php if ( ! empty( $props['show_reviews'] ) ) : ?>
					<article class="jcp-story-moment jcp-story-moment--reviews">
						<h3 class="jcp-story-moment__title"<?php jcp_niche_editable_attr( $path . '.reviews_headline' ); ?>>
							<?php echo esc_html( (string) $props['reviews_headline'] ); ?>
						</h3>
						<p class="jcp-story-moment__lead"<?php jcp_niche_editable_attr( $path . '.reviews_body' ); ?>>
							<?php echo esc_html( (string) $props['reviews_body'] ); ?>
						</p>

						<div class="jcp-story-reviews">
							<div class="jcp-story-reviews__photo">
								<img
									src="<?php echo esc_url( (string) $props['reviews_photo_url'] ); ?>"
									alt="<?php echo esc_attr( (string) $props['reviews_photo_alt'] ); ?>"
									width="480"
									height="320"
									loading="lazy"
									decoding="async"
								/>
							</div>
							<div class="jcp-story-reviews__card" aria-hidden="true">
								<div class="jcp-story-reviews__qr">
									<?php if ( $icon( 'qr-code' ) ) : ?>
										<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="40" height="40" />
									<?php endif; ?>
									<span><?php esc_html_e( 'Scan to review', 'jcp-core' ); ?></span>
								</div>
								<div class="jcp-story-reviews__stars" aria-hidden="true">★★★★★</div>
								<p<?php jcp_niche_editable_attr( $path . '.reviews_quote' ); ?>>
									<?php echo esc_html( (string) $props['reviews_quote'] ); ?>
								</p>
							</div>
						</div>

						<ul class="jcp-story-reviews__pills" aria-label="<?php esc_attr_e( 'Review benefits', 'jcp-core' ); ?>">
							<li>
								<?php if ( $icon( 'send' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'send' ) ); ?>" alt="" width="14" height="14" />
								<?php endif; ?>
								<span><?php esc_html_e( 'Ask in person', 'jcp-core' ); ?></span>
							</li>
							<li>
								<?php if ( $icon( 'qr-code' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'qr-code' ) ); ?>" alt="" width="14" height="14" />
								<?php endif; ?>
								<span><?php esc_html_e( 'QR on site', 'jcp-core' ); ?></span>
							</li>
							<li>
								<?php if ( $icon( 'star' ) ) : ?>
									<img src="<?php echo esc_url( $icon( 'star' ) ); ?>" alt="" width="14" height="14" />
								<?php endif; ?>
								<span><?php esc_html_e( 'Public proof', 'jcp-core' ); ?></span>
							</li>
						</ul>
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
