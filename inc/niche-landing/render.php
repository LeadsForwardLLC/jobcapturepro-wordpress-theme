<?php
/**
 * Server-rendered niche / industry landing sections.
 *
 * @package JCP_Core
 */

/**
 * Escape and echo plain text.
 *
 * @param string $text Text.
 */
function jcp_niche_e( string $text ): void {
	echo esc_html( $text );
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_breadcrumb( array $c ): void {
	if ( ! empty( $c['hide_breadcrumb'] ) ) {
		return;
	}
	$label = ! empty( $c['niche_label'] ) ? (string) $c['niche_label'] : '';
	if ( $label === '' ) {
		return;
	}
	$hub = get_post_type_archive_link( 'jcp_niche_landing' );
	if ( ! $hub ) {
		$hub = home_url( '/industries/' );
	}
	?>
	<nav class="jcp-niche-breadcrumb jcp-container" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jcp-core' ); ?>">
		<a href="<?php echo esc_url( $hub ); ?>"><?php esc_html_e( 'Industries', 'jcp-core' ); ?></a>
		<span aria-hidden="true">/</span>
		<span><?php echo esc_html( $label ); ?></span>
	</nav>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_hero( array $c, string $niche_key ): void {
	$h = $c['hero'] ?? [];
	if ( empty( $h['h1'] ) && empty( $h['h1_prefix'] ) ) {
		return;
	}
	$primary   = jcp_niche_resolve_cta( $h['cta_primary'] ?? [], $niche_key );
	$secondary = jcp_niche_resolve_cta( $h['cta_secondary'] ?? [ 'label' => 'See how it works', 'url' => '#how-it-works' ], $niche_key );
	$variant   = (string) ( $c['_hero_variant'] ?? '' );
	if ( ! in_array( $variant, jcp_block_hero_variants(), true ) ) {
		$variant = ! isset( $h['show_visual'] ) || ! empty( $h['show_visual'] ) ? 'split' : 'centered';
	}
	if ( ! empty( $h['rotating_words'] ) && is_array( $h['rotating_words'] ) ) {
		$variant = 'home';
	}
	$demo_url = home_url( '/demo/' );
	$default_photo = jcp_media_default_phone_image();
	$is_home  = $variant === 'home';
	$media    = jcp_media_props_from_block( $h );
	if ( empty( $h['media_type'] ) ) {
		$media['media_type'] = 'phone_mockup';
	}
	$phone_image = jcp_media_resolve_phone_image( $h );
	$phone_alt   = trim( (string) ( $h['phone_image_alt'] ?? $h['media_alt'] ?? '' ) );
	$show_visual = $variant !== 'centered';
	?>
	<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-<?php echo esc_attr( $variant ); ?>">
		<div class="jcp-container">
			<div class="jcp-hero-grid jcp-split-layout <?php echo esc_attr( jcp_media_position_class( $media['media_position'] ) ); ?>" data-jcp-split-path="hero" data-jcp-media-position-path="hero.media_position">
				<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
					<?php if ( $is_home ) : ?>
						<h1 class="jcp-hero-title">
							<span<?php jcp_niche_editable_attr( 'hero.h1_prefix' ); ?>><?php echo esc_html( (string) ( $h['h1_prefix'] ?? $h['h1'] ?? '' ) ); ?></span>
							<span class="jcp-hero-title-end">
								<?php esc_html_e( 'more', 'jcp-core' ); ?>
								<span class="jcp-hero-rotating-word" aria-live="polite" data-words="<?php echo esc_attr( wp_json_encode( array_values( (array) $h['rotating_words'] ) ) ); ?>">
									<?php echo esc_html( (string) ( $h['rotating_words'][0] ?? 'visibility' ) ); ?>
								</span>
							</span>
						</h1>
					<?php else : ?>
					<h1 class="jcp-hero-title"<?php jcp_niche_editable_attr( 'hero.h1' ); ?>><?php jcp_niche_e( (string) $h['h1'] ); ?></h1>
					<?php endif; ?>
					<?php if ( ! empty( $h['subheadline'] ) ) : ?>
						<p class="jcp-hero-subtitle"<?php jcp_niche_editable_attr( 'hero.subheadline' ); ?>><?php jcp_niche_e( (string) $h['subheadline'] ); ?></p>
					<?php endif; ?>
					<div class="jcp-actions directory-cta-row">
						<?php if ( $primary['label'] !== '' ) : ?>
							<div class="jcp-hero-primary-cta">
								<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'hero.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], str_contains( $primary['url'], 'firstpromoter.com' ) ? 'referral_hero' : 'niche_hero', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
								<?php if ( ! empty( $h['cta_microcopy'] ) ) : ?>
									<span class="jcp-hero-cta-microcopy"<?php jcp_niche_editable_attr( 'hero.cta_microcopy' ); ?>><?php jcp_niche_e( (string) $h['cta_microcopy'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( $secondary['label'] !== '' ) : ?>
							<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'hero.cta_secondary' ); ?>><?php jcp_niche_e( $secondary['label'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $h['trust_line'] ) ) : ?>
						<p class="jcp-niche-trust-line"<?php jcp_niche_editable_attr( 'hero.trust_line' ); ?>><?php jcp_niche_e( (string) $h['trust_line'] ); ?></p>
					<?php endif; ?>
					<?php if ( $is_home && ! empty( $h['meta_stats'] ) ) : ?>
						<?php jcp_component_home_meta_stats( (array) $h['meta_stats'] ); ?>
					<?php endif; ?>
				</div>
				<?php if ( $show_visual ) : ?>
				<div class="jcp-split-col jcp-split-col--media jcp-hero-visual-column" data-jcp-split-col="media" aria-hidden="false">
					<?php
					$hero_demo = $primary['url'] !== '' ? $primary['url'] : $demo_url;
					jcp_media_render_slot(
						[
							'path'               => 'hero',
							'media_type'         => $media['media_type'],
							'image_url'          => $media['image_url'],
							'video_url'          => $media['video_url'],
							'media_alt'          => $media['media_alt'],
							'default_image'      => $default_photo,
							'phone_mockup_style' => 'live_demo',
							'img_attrs'          => [
								'class'   => 'jcp-hero-slot-image',
								'width'   => '640',
								'height'  => '480',
								'loading' => 'eager',
							],
							'phone_render'  => function () use ( $is_home, $hero_demo, $phone_image, $phone_alt ) {
								if ( $is_home ) {
									jcp_component_hero_home_visual( $hero_demo, $phone_image, $phone_alt, true );
									return;
								}
								?>
								<div class="jcp-hero-visual hero-visual">
									<a href="<?php echo esc_url( $hero_demo ); ?>" class="demo-phone-mockup hero-phone-mockup" aria-label="<?php esc_attr_e( 'Try the live demo', 'jcp-core' ); ?>">
										<div class="phone-frame hero-phone-frame">
											<div class="phone-screen">
												<div class="phone-content">
													<div class="phone-header hero-phone-header">
														<div class="phone-status-bar"><span>9:41</span></div>
														<div class="hero-phone-live-row"><span class="hero-phone-live-badge"><?php esc_html_e( 'Live', 'jcp-core' ); ?></span></div>
													</div>
													<div class="phone-body hero-phone-body">
														<div class="hero-phone-image-wrap">
															<img src="<?php echo esc_url( $phone_image ); ?>" alt="<?php echo esc_attr( $phone_alt ); ?>" class="hero-phone-image jcp-editable-media-image" width="390" height="292" loading="eager" data-jcp-media-url-path="hero.phone_image_url" data-jcp-media-alt-path="hero.phone_image_alt" data-jcp-media-role="phone_screen" />
														</div>
														<div class="demo-preview-item hero-phone-card hero-phone-card-1">
															<div class="demo-item-content">
																<div class="demo-item-title"><?php esc_html_e( 'Job captured', 'jcp-core' ); ?></div>
																<div class="demo-item-subtitle"><?php esc_html_e( 'Photos from the field', 'jcp-core' ); ?></div>
															</div>
														</div>
													</div>
													<div class="phone-click-hint hero-phone-cta">
														<span><?php esc_html_e( 'Try the demo', 'jcp-core' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</a>
								</div>
								<?php
							},
						]
					);
					?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Media + text split section (image or video opposite copy).
 *
 * @param array<string, mixed> $props Block props.
 * @param string               $path  JSON path prefix.
 */
function jcp_niche_render_media_text( array $props, string $path = 'media_text' ): void {
	$headline = trim( (string) ( $props['headline'] ?? '' ) );
	$body     = trim( (string) ( $props['body'] ?? '' ) );
	if ( $headline === '' && $body === '' ) {
		return;
	}

	$position   = (string) ( $props['media_position'] ?? 'right' );
	$position   = in_array( $position, [ 'left', 'right' ], true ) ? $position : 'right';
	$media      = jcp_media_props_from_block( $props );
	$media_type = $media['media_type'];
	$media_url  = $media['media_url'];
	$media_alt  = $media['media_alt'];
	$default_image = 'https://jobcapturepro.com/wp-content/uploads/2025/12/jcp-user-photo.jpg';
	$cta        = is_array( $props['cta'] ?? null ) ? $props['cta'] : [];
	$cta_label  = trim( (string) ( $cta['label'] ?? '' ) );
	$cta_url    = trim( (string) ( $cta['url'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section jcp-media-text jcp-media-text--media-<?php echo esc_attr( $position ); ?>">
		<div class="jcp-container">
			<div class="jcp-media-text-grid jcp-split-layout <?php echo esc_attr( jcp_media_position_class( $position ) ); ?>" data-jcp-split-path="<?php echo esc_attr( $path ); ?>" data-jcp-media-position-path="<?php echo esc_attr( $path . '.media_position' ); ?>">
				<div class="jcp-media-text-copy jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
					<?php if ( $headline !== '' ) : ?>
						<h2<?php jcp_niche_editable_attr( $path . '.headline' ); ?>><?php jcp_niche_e( $headline ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $props['subheadline'] ) ) : ?>
						<p class="rankings-subtitle"<?php jcp_niche_editable_attr( $path . '.subheadline' ); ?>><?php jcp_niche_e( (string) $props['subheadline'] ); ?></p>
					<?php endif; ?>
					<?php if ( $body !== '' ) : ?>
						<p class="jcp-media-text-body"<?php jcp_niche_editable_attr( $path . '.body' ); ?>><?php jcp_niche_e( $body ); ?></p>
					<?php endif; ?>
					<?php if ( $cta_label !== '' ) : ?>
						<div class="jcp-actions directory-cta-row jcp-media-text-cta">
							<a class="btn btn-primary" href="<?php echo esc_url( $cta_url !== '' ? $cta_url : '#' ); ?>"<?php jcp_niche_editable_link_attr( $path . '.cta' ); ?>><?php jcp_niche_e( $cta_label ); ?></a>
						</div>
					<?php endif; ?>
				</div>
				<div class="jcp-media-text-media jcp-split-col jcp-split-col--media" data-jcp-split-col="media">
					<?php
					jcp_media_render_slot(
						[
							'path'          => $path,
							'media_type'    => $media_type,
							'image_url'     => $media['image_url'],
							'video_url'     => $media['video_url'],
							'media_alt'     => $media_alt,
							'default_image' => $default_image,
							'img_attrs'     => [
								'class'   => 'jcp-media-text-image',
								'width'   => '640',
								'height'  => '480',
								'loading' => 'lazy',
							],
						]
					);
					?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Centered mid-page CTA band.
 *
 * @param array<string, mixed> $band      CTA band block.
 * @param string               $niche_key Niche key.
 * @param string               $path      JSON path prefix.
 */
function jcp_niche_render_cta_band( array $band, string $niche_key, string $path = 'cta_band' ): void {
	$primary = jcp_niche_resolve_cta( $band['cta_primary'] ?? [], $niche_key );
	if ( $primary['label'] === '' ) {
		return;
	}
	?>
	<section class="jcp-section jcp-niche-cta-band">
		<div class="jcp-container">
			<div class="jcp-niche-cta-band-inner">
				<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( $path . '.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], 'referral_cta_band', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_what_it_is( array $c ): void {
	$w = $c['what_it_is'] ?? [];
	if ( empty( $w['headline'] ) ) {
		return;
	}
	$lead = ! empty( $w['lead'] ) ? (string) $w['lead'] : __( 'But once the work is done, most of it disappears. JobCapturePro changes that.', 'jcp-core' );
	?>
	<section class="jcp-section rankings-section jcp-niche-what">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'what_it_is.headline' ); ?>><?php jcp_niche_e( (string) $w['headline'] ); ?></h2>
				<?php if ( ! empty( $w['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'what_it_is.subheadline' ); ?>><?php jcp_niche_e( (string) $w['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="ranking-factors-grid jcp-niche-split-grid">
				<?php
				$team_title   = ! empty( $w['team_already_title'] ) ? (string) $w['team_already_title'] : __( 'Your team is already', 'jcp-core' );
				$turns_title  = ! empty( $w['turns_into_title'] ) ? (string) $w['turns_into_title'] : __( 'Turns real jobs into', 'jcp-core' );
				jcp_niche_factor_card(
					$team_title,
					'wrench',
					'',
					'',
					function () use ( $w ) {
						echo '<ul class="jcp-niche-checklist"';
						jcp_niche_array_attr( 'what_it_is.team_already' );
						echo '>';
						foreach ( (array) ( $w['team_already'] ?? [] ) as $ti => $line ) {
							echo '<li';
							jcp_niche_array_item_attr( (int) $ti );
							jcp_niche_editable_attr( 'what_it_is.team_already.' . $ti );
							echo '>' . esc_html( (string) $line ) . '</li>';
						}
						echo '</ul>';
					},
					'what_it_is.team_already_title'
				);
				jcp_niche_factor_card(
					$turns_title,
					'sparkles',
					'',
					'',
					function () use ( $w, $lead ) {
						echo '<p class="jcp-niche-card-lead"';
						jcp_niche_editable_attr( 'what_it_is.lead' );
						echo '>' . esc_html( $lead ) . '</p>';
						echo '<ul class="jcp-niche-checklist"';
						jcp_niche_array_attr( 'what_it_is.turns_into' );
						echo '>';
						foreach ( (array) ( $w['turns_into'] ?? [] ) as $ti => $line ) {
							echo '<li';
							jcp_niche_array_item_attr( (int) $ti );
							jcp_niche_editable_attr( 'what_it_is.turns_into.' . $ti );
							echo '>' . esc_html( (string) $line ) . '</li>';
						}
						echo '</ul>';
					},
					'what_it_is.turns_into_title'
				);
				?>
			</div>
			<?php
			if ( ! empty( $w['closing'] ) ) {
				jcp_niche_render_section_closing( (string) $w['closing'], 'what_it_is.closing' );
			}
			$mechanic = $c['core_mechanic'] ?? [];
			if ( ! empty( $mechanic ) && is_array( $mechanic ) ) {
				jcp_niche_render_meta_strip( $mechanic, 'core_mechanic' );
			}
			?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_how_it_works( array $c, string $niche_key ): void {
	$h = $c['how_it_works'] ?? [];
	if ( empty( $h['headline'] ) ) {
		return;
	}
	$has_cta = ! empty( $h['cta_label'] ) || ! empty( $h['cta_url'] ) || ! empty( $h['cta_primary'] );
	$cta     = $has_cta
		? jcp_niche_resolve_cta(
			[
				'label' => $h['cta_label'] ?? ( $h['cta_primary']['label'] ?? 'See it in action' ),
				'url'   => $h['cta_url'] ?? ( $h['cta_primary']['url'] ?? '/demo' ),
			],
			$niche_key
		)
		: [ 'label' => '', 'url' => '' ];
	$numeric_steps = ! empty( $h['numeric_steps'] );
	$section_id    = ! empty( $h['section_id'] ) ? (string) $h['section_id'] : 'how-it-works';
	?>
	<section class="jcp-section rankings-section" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'how_it_works.headline' ); ?>><?php jcp_niche_e( (string) $h['headline'] ); ?></h2>
				<?php if ( ! empty( $h['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'how_it_works.subheadline' ); ?>><?php jcp_niche_e( (string) $h['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="timeline-steps"<?php jcp_niche_array_attr( 'how_it_works.steps' ); ?>>
				<?php
				$steps = (array) ( $h['steps'] ?? [] );
				foreach ( $steps as $i => $step ) :
					if ( ! is_array( $step ) ) {
						continue;
					}
					$num   = $numeric_steps ? (string) ( $i + 1 ) : str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
					$lines = (array) ( $step['lines'] ?? [] );
					if ( empty( $lines ) ) {
						$fallback = (string) ( $step['body'] ?? $step['description'] ?? '' );
						if ( $fallback !== '' ) {
							$lines = [ $fallback ];
						}
					}
					?>
					<div class="timeline-step"<?php jcp_niche_array_item_attr( (int) $i ); ?>>
						<div class="step-number"><?php echo esc_html( $num ); ?></div>
						<div class="step-content">
							<h4 class="step-title"<?php jcp_niche_editable_attr( 'how_it_works.steps.' . $i . '.title' ); ?>><?php jcp_niche_e( (string) ( $step['title'] ?? '' ) ); ?></h4>
							<?php foreach ( $lines as $li => $line ) : ?>
								<p class="step-description"<?php jcp_niche_editable_attr( 'how_it_works.steps.' . $i . '.lines.' . $li ); ?>><?php jcp_niche_e( (string) $line ); ?></p>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( $cta['label'] !== '' ) : ?>
				<div class="timeline-cta">
					<a href="<?php echo esc_url( $cta['url'] ); ?>" class="timeline-cta-link"<?php jcp_niche_editable_link_paths( 'how_it_works.cta_label', 'how_it_works.cta_url' ); ?>>
						<?php jcp_niche_e( $cta['label'] ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
					</a>
				</div>
			<?php endif; ?>
			<?php
			if ( ! empty( $h['demo_preview'] ) && is_array( $h['demo_preview'] ) ) {
				jcp_niche_render_demo_preview( $h['demo_preview'], $niche_key, 'how_it_works.demo_preview' );
			}
			?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_check_ins( array $c ): void {
	$ch = $c['check_ins'] ?? [];
	if ( empty( $ch['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-checkins">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'check_ins.headline' ); ?>><?php jcp_niche_e( (string) $ch['headline'] ); ?></h2>
				<?php if ( ! empty( $ch['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'check_ins.subheadline' ); ?>><?php jcp_niche_e( (string) $ch['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $ch['job_types'] ) ) : ?>
				<div class="jcp-niche-tags-wrap">
					<ul class="jcp-niche-tags"<?php jcp_niche_array_attr( 'check_ins.job_types' ); ?>>
						<?php foreach ( (array) $ch['job_types'] as $ti => $tag ) : ?>
							<li<?php jcp_niche_array_item_attr( (int) $ti ); jcp_niche_editable_attr( 'check_ins.job_types.' . $ti ); ?>><?php jcp_niche_e( (string) $tag ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div class="ranking-factors-grid"<?php jcp_niche_array_attr( 'check_ins.features' ); ?>>
				<?php
				$feat_icons = [ 'map-pin', 'camera', 'sparkles', 'star' ];
				foreach ( (array) ( $ch['features'] ?? [] ) as $fi => $feat ) :
					if ( ! is_array( $feat ) ) {
						continue;
					}
					jcp_niche_factor_card(
						(string) ( $feat['title'] ?? '' ),
						$feat_icons[ $fi ] ?? 'badge-check',
						'',
						'',
						function () use ( $feat, $fi ) {
							echo '<p';
							jcp_niche_editable_attr( 'check_ins.features.' . $fi . '.body' );
							echo '>' . esc_html( (string) ( $feat['body'] ?? '' ) ) . '</p>';
						},
						'check_ins.features.' . $fi . '.title',
						'',
						'',
						(int) $fi
					);
				endforeach;
				?>
			</div>
			<?php
			if ( ! empty( $ch['closing'] ) ) {
				jcp_niche_render_section_closing( (string) $ch['closing'], 'check_ins.closing' );
			}
			?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_problem( array $c ): void {
	$p = $c['problem'] ?? [];
	if ( empty( $p['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-problem">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'problem.headline' ); ?>><?php jcp_niche_e( (string) $p['headline'] ); ?></h2>
				<?php if ( ! empty( $p['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'problem.subheadline' ); ?>><?php jcp_niche_e( (string) $p['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="ranking-factors-grid"<?php jcp_niche_array_attr( 'problem.pain_points' ); ?>>
				<?php
				$pain_icons = [ 'image-off', 'clock', 'map-pin', 'users' ];
				foreach ( (array) ( $p['pain_points'] ?? [] ) as $pi => $pain ) :
					if ( ! is_array( $pain ) ) {
						continue;
					}
					jcp_niche_factor_card(
						(string) ( $pain['title'] ?? '' ),
						$pain_icons[ $pi ] ?? 'circle-alert',
						'',
						'',
						function () use ( $pain, $pi ) {
							echo '<p';
							jcp_niche_editable_attr( 'problem.pain_points.' . $pi . '.body' );
							echo '>' . esc_html( (string) ( $pain['body'] ?? '' ) ) . '</p>';
						},
						'problem.pain_points.' . $pi . '.title',
						'',
						'',
						(int) $pi
					);
				endforeach;
				?>
			</div>
			<?php
			if ( ! empty( $p['closing'] ) ) {
				jcp_niche_render_section_closing( (string) $p['closing'], 'problem.closing' );
			}
			?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_benefits( array $c ): void {
	$b = $c['benefits'] ?? [];
	if ( empty( $b['headline'] ) ) {
		return;
	}
	$section_id = ! empty( $b['section_id'] ) ? (string) $b['section_id'] : '';
	$variant      = (string) ( $b['variant'] ?? '' );
	?>
	<section class="jcp-section rankings-section jcp-niche-benefits<?php echo $section_id !== '' ? '' : ''; ?>"<?php echo $section_id !== '' ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'benefits.headline' ); ?>><?php jcp_niche_e( (string) $b['headline'] ); ?></h2>
				<?php if ( ! empty( $b['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'benefits.subheadline' ); ?>><?php jcp_niche_e( (string) $b['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="ranking-factors-grid"<?php jcp_niche_array_attr( 'benefits.items' ); ?>>
				<?php
				$benefit_icons = [ 'badge-check', 'map-pin', 'message-square', 'star', 'building-2', 'phone' ];
				foreach ( (array) ( $b['items'] ?? [] ) as $bi => $item ) :
					if ( ! is_array( $item ) ) {
						continue;
					}
					$icon = ! empty( $item['icon'] ) ? (string) $item['icon'] : ( $benefit_icons[ $bi ] ?? 'badge-check' );
					jcp_niche_factor_card(
						(string) ( $item['title'] ?? '' ),
						$icon,
						(string) ( $item['stat_value'] ?? '' ),
						(string) ( $item['stat_label'] ?? '' ),
						function () use ( $item, $bi ) {
							echo '<p';
							jcp_niche_editable_attr( 'benefits.items.' . $bi . '.body' );
							echo '>' . esc_html( (string) ( $item['body'] ?? '' ) ) . '</p>';
						},
						'benefits.items.' . $bi . '.title',
						'benefits.items.' . $bi . '.stat_value',
						'benefits.items.' . $bi . '.stat_label',
						(int) $bi
					);
				endforeach;
				?>
			</div>
			<?php
			if ( ! empty( $b['closing'] ) ) {
				jcp_niche_render_section_closing( (string) $b['closing'], 'benefits.closing' );
			}
			if ( $variant === 'cta_row' ) :
				$primary   = jcp_niche_resolve_cta( $b['cta_primary'] ?? [], '' );
				$secondary = jcp_niche_resolve_cta( $b['cta_secondary'] ?? [], '' );
				?>
				<div class="benefits-cta-row">
					<div class="benefits-cta-slot"<?php jcp_niche_optional_slot_attr( 'benefits.cta_primary', 'cta', 'Primary button' ); ?>>
						<?php if ( $primary['label'] !== '' ) : ?>
							<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary"<?php jcp_niche_editable_link_attr( 'benefits.cta_primary' ); ?>><?php echo esc_html( $primary['label'] ); ?></a>
						<?php endif; ?>
					</div>
					<div class="benefits-cta-slot"<?php jcp_niche_optional_slot_attr( 'benefits.cta_secondary', 'link', 'Secondary link' ); ?>>
						<?php if ( $secondary['label'] !== '' ) : ?>
							<a href="<?php echo esc_url( $secondary['url'] ); ?>" class="benefits-cta-link"<?php jcp_niche_editable_link_attr( 'benefits.cta_secondary' ); ?>>
								<?php echo esc_html( $secondary['label'] ); ?>
								<?php jcp_component_chevron_svg(); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_commission( array $c, string $niche_key ): void {
	$m = $c['commission'] ?? [];
	if ( empty( $m['headline'] ) ) {
		return;
	}
	$rows    = (array) ( $m['rows'] ?? [] );
	$primary = jcp_niche_resolve_cta( $m['cta_primary'] ?? [], $niche_key );
	?>
	<section class="jcp-section rankings-section jcp-niche-commission">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'commission.headline' ); ?>><?php jcp_niche_e( (string) $m['headline'] ); ?></h2>
				<?php if ( ! empty( $m['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'commission.subheadline' ); ?>><?php jcp_niche_e( (string) $m['subheadline'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $m['body'] ) ) : ?>
					<p class="jcp-niche-commission-lead"<?php jcp_niche_editable_attr( 'commission.body' ); ?>><?php jcp_niche_e( (string) $m['body'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $rows ) ) : ?>
				<div class="jcp-niche-commission-table-wrap">
					<table class="jcp-niche-commission-table">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Plan', 'jcp-core' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Monthly Price', 'jcp-core' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Your Monthly Commission', 'jcp-core' ); ?></th>
								<th scope="col"><?php esc_html_e( '12-Month Potential', 'jcp-core' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $i => $row ) : ?>
								<?php if ( ! is_array( $row ) ) { continue; } ?>
								<tr>
									<td data-label="<?php esc_attr_e( 'Plan', 'jcp-core' ); ?>"<?php jcp_niche_editable_attr( 'commission.rows.' . $i . '.plan' ); ?>><?php jcp_niche_e( (string) ( $row['plan'] ?? '' ) ); ?></td>
									<td data-label="<?php esc_attr_e( 'Monthly Price', 'jcp-core' ); ?>"<?php jcp_niche_editable_attr( 'commission.rows.' . $i . '.price' ); ?>><?php jcp_niche_e( (string) ( $row['price'] ?? '' ) ); ?></td>
									<td data-label="<?php esc_attr_e( 'Your Monthly Commission', 'jcp-core' ); ?>"<?php jcp_niche_editable_attr( 'commission.rows.' . $i . '.monthly' ); ?>><?php jcp_niche_e( (string) ( $row['monthly'] ?? '' ) ); ?></td>
									<td data-label="<?php esc_attr_e( '12-Month Potential', 'jcp-core' ); ?>"<?php jcp_niche_editable_attr( 'commission.rows.' . $i . '.twelve_month' ); ?>><?php jcp_niche_e( (string) ( $row['twelve_month'] ?? '' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $m['footnote'] ) ) : ?>
				<p class="jcp-niche-commission-footnote"<?php jcp_niche_editable_attr( 'commission.footnote' ); ?>><?php jcp_niche_e( (string) $m['footnote'] ); ?></p>
			<?php endif; ?>
			<?php if ( $primary['label'] !== '' ) : ?>
				<div class="jcp-niche-cta-band-inner">
					<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'commission.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], 'referral_commission', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_partners( array $c, string $niche_key ): void {
	$p = $c['partners'] ?? [];
	if ( empty( $p['headline'] ) ) {
		return;
	}
	$primary = jcp_niche_resolve_cta( $p['cta_primary'] ?? [], $niche_key );
	?>
	<section class="jcp-section rankings-section jcp-niche-partners">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'partners.headline' ); ?>><?php jcp_niche_e( (string) $p['headline'] ); ?></h2>
			</div>
			<div class="real-job-proof-callout jcp-niche-partners-callout">
				<?php if ( ! empty( $p['body'] ) ) : ?>
					<p class="real-job-proof-callout-text"<?php jcp_niche_editable_attr( 'partners.body' ); ?>><?php jcp_niche_e( (string) $p['body'] ); ?></p>
				<?php endif; ?>
				<?php if ( $primary['label'] !== '' ) : ?>
					<div class="jcp-niche-cta-band-inner">
						<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'partners.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], 'referral_partners', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_share( array $c, string $niche_key ): void {
	$s = $c['share'] ?? [];
	if ( empty( $s['headline'] ) ) {
		return;
	}
	$primary   = jcp_niche_resolve_cta( $s['cta_primary'] ?? [], $niche_key );
	$secondary = jcp_niche_resolve_cta( $s['cta_secondary'] ?? [], $niche_key );
	?>
	<section class="jcp-section rankings-section jcp-niche-share">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'share.headline' ); ?>><?php jcp_niche_e( (string) $s['headline'] ); ?></h2>
				<?php if ( ! empty( $s['body'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'share.body' ); ?>><?php jcp_niche_e( (string) $s['body'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $s['quote'] ) ) : ?>
				<blockquote class="jcp-niche-share-quote"<?php jcp_niche_editable_attr( 'share.quote' ); ?>>
					<p><?php jcp_niche_e( (string) $s['quote'] ); ?></p>
				</blockquote>
			<?php endif; ?>
			<?php if ( ! empty( $s['note'] ) ) : ?>
				<p class="jcp-niche-share-note"<?php jcp_niche_editable_attr( 'share.note' ); ?>><?php jcp_niche_e( (string) $s['note'] ); ?></p>
			<?php endif; ?>
			<div class="jcp-actions directory-cta-row jcp-niche-share-actions">
				<?php if ( $primary['label'] !== '' ) : ?>
					<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'share.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], 'referral_share', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
				<?php endif; ?>
				<?php if ( $secondary['label'] !== '' ) : ?>
					<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'share.cta_secondary' ); jcp_niche_cta_tracking_attr( $secondary['url'], 'referral_share_demo', $secondary['label'] ); ?>><?php jcp_niche_e( $secondary['label'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_differentiation( array $c ): void {
	$d = $c['differentiation'] ?? [];
	if ( empty( $d['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-diff">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'differentiation.headline' ); ?>><?php jcp_niche_e( (string) $d['headline'] ); ?></h2>
			</div>
			<div class="real-job-proof-callout jcp-niche-diff-callout">
				<?php if ( ! empty( $d['body'] ) ) : ?>
					<p class="real-job-proof-callout-text"<?php jcp_niche_editable_attr( 'differentiation.body' ); ?>><?php jcp_niche_e( (string) $d['body'] ); ?></p>
				<?php endif; ?>
				<?php jcp_niche_render_conversion_points( (array) ( $d['bullets'] ?? [] ), 'differentiation.bullets' ); ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_who_its_for( array $c ): void {
	$w = $c['who_its_for'] ?? [];
	if ( empty( $w['headline'] ) ) {
		return;
	}
	$variant = (string) ( $w['variant'] ?? '' );
	?>
	<section class="jcp-section rankings-section jcp-niche-audiences" id="who-its-for">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'who_its_for.headline' ); ?>><?php jcp_niche_e( (string) $w['headline'] ); ?></h2>
				<?php if ( ! empty( $w['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'who_its_for.subheadline' ); ?>><?php jcp_niche_e( (string) $w['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $variant === 'guarantees' ) : ?>
				<div class="guarantees-grid"<?php jcp_niche_array_attr( 'who_its_for.audiences' ); ?>>
					<?php foreach ( (array) ( $w['audiences'] ?? [] ) as $ai => $aud ) : ?>
						<?php
						if ( ! is_array( $aud ) ) {
							continue;
						}
						jcp_component_audience_guarantee_card( $aud, (int) $ai );
						?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
			<div class="ranking-factors-grid jcp-niche-split-grid"<?php jcp_niche_array_attr( 'who_its_for.audiences' ); ?>>
				<?php
				$aud_icons = [ 'briefcase', 'hard-hat', 'trending-up' ];
				foreach ( (array) ( $w['audiences'] ?? [] ) as $ai => $aud ) :
					if ( ! is_array( $aud ) ) {
						continue;
					}
					jcp_niche_factor_card(
						(string) ( $aud['title'] ?? '' ),
						$aud_icons[ $ai ] ?? 'users',
						'',
						'',
						function () use ( $aud, $ai ) {
							echo '<p';
							jcp_niche_editable_attr( 'who_its_for.audiences.' . $ai . '.body' );
							echo '>' . esc_html( (string) ( $aud['body'] ?? '' ) ) . '</p>';
						},
						'who_its_for.audiences.' . $ai . '.title',
						'',
						'',
						(int) $ai
					);
				endforeach;
				?>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_faq( array $c ): void {
	$f = $c['faq'] ?? [];
	$items = (array) ( $f['items'] ?? [] );
	if ( empty( $f['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section faq-section" id="faq">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'faq.headline' ); ?>><?php jcp_niche_e( (string) $f['headline'] ); ?></h2>
				<?php if ( ! empty( $f['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'faq.subheadline' ); ?>><?php jcp_niche_e( (string) $f['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="faq-grid"<?php jcp_niche_array_attr( 'faq.items' ); ?>>
				<?php foreach ( $items as $i => $item ) : ?>
					<?php
					if ( ! is_array( $item ) ) {
						continue;
					}
					$faq_id = ! empty( $item['id'] ) ? (string) $item['id'] : 'faq-' . $i;
					?>
					<details class="faq-item" id="<?php echo esc_attr( $faq_id ); ?>"<?php jcp_niche_array_item_attr( (int) $i ); ?>>
						<summary<?php jcp_niche_editable_attr( 'faq.items.' . $i . '.q' ); ?>><?php jcp_niche_e( (string) ( $item['q'] ?? '' ) ); ?></summary>
						<?php
						$answer = $item['a'] ?? '';
						$paras  = is_array( $answer ) ? $answer : preg_split( "/\n\s*\n/", (string) $answer );
						foreach ( (array) $paras as $pi => $para ) {
							$para = trim( (string) $para );
							if ( $para === '' ) {
								continue;
							}
							$apath = is_array( $answer ) ? 'faq.items.' . $i . '.a.' . $pi : ( $pi === 0 ? 'faq.items.' . $i . '.a' : 'faq.items.' . $i . '.a.' . $pi );
							echo '<p';
							jcp_niche_editable_attr( $apath );
							echo '>' . esc_html( $para ) . '</p>';
						}
						?>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_final_cta( array $c, string $niche_key ): void {
	$f = $c['final_cta'] ?? [];
	if ( empty( $f['headline'] ) ) {
		return;
	}
	$primary = jcp_niche_resolve_cta( $f['cta_primary'] ?? [], $niche_key );
	$note    = ! empty( $f['cta_note'] ) ? (string) $f['cta_note'] : __( 'No signup required. Setup in minutes.', 'jcp-core' );
	$btn     = $primary['label'] !== '' ? $primary['label'] : __( 'See your business in the live demo', 'jcp-core' );
	$url     = $primary['url'] !== '' ? $primary['url'] : home_url( '/demo/' );
	?>
	<section class="jcp-section rankings-section jcp-niche-final">
		<div class="jcp-container">
			<div class="rankings-cta">
				<div class="cta-content">
					<h3<?php jcp_niche_editable_attr( 'final_cta.headline' ); ?>><?php jcp_niche_e( (string) $f['headline'] ); ?></h3>
					<?php if ( ! empty( $f['subheadline'] ) ) : ?>
						<p class="cta-paragraph"<?php jcp_niche_editable_attr( 'final_cta.subheadline' ); ?>><?php jcp_niche_e( (string) $f['subheadline'] ); ?></p>
					<?php endif; ?>
				</div>
				<div class="cta-button-wrapper">
					<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $url ); ?>"<?php jcp_niche_editable_link_attr( 'final_cta.cta_primary' ); jcp_niche_cta_tracking_attr( $url, str_contains( $url, 'firstpromoter.com' ) ? 'referral_footer' : 'niche_footer', $btn ); ?>><?php echo esc_html( $btn ); ?></a>
					<p class="cta-note"<?php jcp_niche_editable_attr( 'final_cta.cta_note' ); ?>><?php echo esc_html( $note ); ?></p>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Live demo preview block (split copy + demo phone).
 *
 * @param array<string, mixed> $props Block props.
 * @param string               $niche_key Page key.
 * @param string               $path      JSON path prefix for inline editor.
 */
function jcp_niche_render_demo_preview( array $props, string $niche_key = '', string $path = 'demo_preview' ): void {
	if ( empty( $props['headline'] ) ) {
		return;
	}
	$primary = jcp_niche_resolve_cta( $props['cta_primary'] ?? [ 'label' => 'Launch Interactive Demo', 'url' => '/demo' ], $niche_key );
	$section_id = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'demo-preview';
	$media = jcp_media_props_from_block( $props );
	if ( empty( $props['media_type'] ) ) {
		$media['media_type'] = 'phone_mockup';
	}
	?>
	<div class="demo-preview-section jcp-block-demo-preview" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="demo-preview-card">
			<div class="demo-preview-content jcp-split-layout <?php echo esc_attr( jcp_media_position_class( $media['media_position'] ) ); ?>" data-jcp-split-path="<?php echo esc_attr( $path ); ?>" data-jcp-media-position-path="<?php echo esc_attr( $path . '.media_position' ); ?>">
				<div class="demo-preview-text jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
					<?php if ( ! empty( $props['badge'] ) ) : ?>
						<div class="demo-badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<circle cx="12" cy="12" r="10"/>
								<polygon points="10 8 16 12 10 16 10 8"/>
							</svg>
							<span<?php jcp_niche_editable_attr( $path . '.badge' ); ?>><?php echo esc_html( (string) $props['badge'] ); ?></span>
						</div>
					<?php endif; ?>
					<h3 class="demo-preview-title"<?php jcp_niche_editable_attr( $path . '.headline' ); ?>><?php echo esc_html( (string) $props['headline'] ); ?></h3>
					<?php if ( ! empty( $props['body'] ) ) : ?>
						<p class="demo-preview-description"<?php jcp_niche_editable_attr( $path . '.body' ); ?>><?php echo esc_html( (string) $props['body'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $props['cue'] ) ) : ?>
						<p class="demo-preview-cue"<?php jcp_niche_editable_attr( $path . '.cue' ); ?>><?php echo esc_html( (string) $props['cue'] ); ?></p>
					<?php endif; ?>
					<div class="demo-cta-wrapper">
						<?php if ( $primary['label'] !== '' ) : ?>
							<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary demo-cta-primary"<?php jcp_niche_editable_link_attr( $path . '.cta_primary' ); ?>>
								<span><?php echo esc_html( $primary['label'] ); ?></span>
								<?php jcp_component_chevron_svg( 20 ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $props['cta_note'] ) ) : ?>
							<p class="demo-cta-note"<?php jcp_niche_editable_attr( $path . '.cta_note' ); ?>><?php echo esc_html( (string) $props['cta_note'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
				<div class="demo-preview-visual jcp-split-col jcp-split-col--media" data-jcp-split-col="media">
					<?php
					jcp_media_render_slot(
						[
							'path'               => $path,
							'media_type'         => $media['media_type'],
							'image_url'          => $media['image_url'],
							'video_url'          => $media['video_url'],
							'media_alt'          => $media['media_alt'],
							'phone_mockup_style' => 'app_shell',
							'phone_render'       => function () use ( $primary ) {
								jcp_component_demo_app_phone( $primary['url'] );
							},
							'img_attrs'    => [
								'class'   => 'demo-preview-slot-image',
								'loading' => 'lazy',
							],
						]
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Proof flow block — channels fed by one job.
 *
 * @param array<string, mixed> $props Block props.
 */
function jcp_niche_render_proof_flow( array $props ): void {
	if ( empty( $props['headline'] ) ) {
		return;
	}
	$section_id = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'real-job-proof';
	$items      = (array) ( $props['items'] ?? [] );
	?>
	<section class="jcp-section rankings-section jcp-block-proof-flow" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'proof_flow.headline' ); ?>><?php echo esc_html( (string) $props['headline'] ); ?></h2>
				<?php if ( ! empty( $props['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'proof_flow.subheadline' ); ?>><?php echo esc_html( (string) $props['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="proof-flow">
				<div class="proof-flow-lines" aria-hidden="true"></div>
				<?php foreach ( $items as $i => $item ) : ?>
					<?php
					if ( ! is_array( $item ) ) {
						continue;
					}
					$icon = ! empty( $item['icon'] ) ? (string) $item['icon'] : 'map-pin';
					?>
					<div class="proof-flow-item">
						<div class="factor-icon-wrapper">
							<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="factor-icon" alt="" width="32" height="32" />
						</div>
						<div class="proof-flow-content">
							<h4 class="proof-flow-label"<?php jcp_niche_editable_attr( 'proof_flow.items.' . $i . '.label' ); ?>><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></h4>
							<p class="proof-flow-copy"<?php jcp_niche_editable_attr( 'proof_flow.items.' . $i . '.copy' ); ?>><?php echo esc_html( (string) ( $item['copy'] ?? '' ) ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $props['callout_title'] ) ) : ?>
				<div class="real-job-proof-callout">
					<?php if ( ! empty( $props['callout_badge'] ) ) : ?>
						<div class="real-job-proof-callout-badge demo-badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
							</svg>
							<span<?php jcp_niche_editable_attr( 'proof_flow.callout_badge' ); ?>><?php echo esc_html( (string) $props['callout_badge'] ); ?></span>
						</div>
					<?php endif; ?>
					<h3 class="real-job-proof-callout-title"<?php jcp_niche_editable_attr( 'proof_flow.callout_title' ); ?>><?php echo esc_html( (string) $props['callout_title'] ); ?></h3>
					<?php if ( ! empty( $props['callout_text'] ) ) : ?>
						<p class="real-job-proof-callout-text"<?php jcp_niche_editable_attr( 'proof_flow.callout_text' ); ?>><?php echo esc_html( (string) $props['callout_text'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $props['link_label'] ) && ! empty( $props['link_url'] ) ) : ?>
				<div class="timeline-cta" style="margin-top: var(--jcp-space-3xl);">
					<a href="<?php echo esc_url( (string) $props['link_url'] ); ?>" class="timeline-cta-link"<?php jcp_niche_editable_link_paths( 'proof_flow.link_label', 'proof_flow.link_url' ); ?>>
						<?php echo esc_html( (string) $props['link_label'] ); ?>
						<?php jcp_component_chevron_svg(); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Directory preview block.
 *
 * @param array<string, mixed> $props Block props.
 * @param string               $niche_key Page key.
 */
function jcp_niche_render_directory_preview( array $props, string $niche_key = '' ): void {
	if ( empty( $props['headline'] ) ) {
		return;
	}
	$section_id = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'directory-preview';
	$primary    = jcp_niche_resolve_cta( $props['cta_primary'] ?? [], $niche_key );
	?>
	<section class="jcp-section rankings-section directory-preview jcp-block-directory-preview" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2<?php jcp_niche_editable_attr( 'directory_preview.headline' ); ?>><?php echo esc_html( (string) $props['headline'] ); ?></h2>
				<?php if ( ! empty( $props['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'directory_preview.subheadline' ); ?>><?php echo esc_html( (string) $props['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="directory-grid preview-grid">
				<?php foreach ( (array) ( $props['cards'] ?? [] ) as $ci => $card ) : ?>
					<?php
					if ( ! is_array( $card ) ) {
						continue;
					}
					jcp_component_directory_preview_card( $card, (int) $ci );
					?>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $props['outro'] ) ) : ?>
				<p class="directory-preview-outro"<?php jcp_niche_editable_attr( 'directory_preview.outro' ); ?>><?php echo esc_html( (string) $props['outro'] ); ?></p>
			<?php endif; ?>
			<?php if ( $primary['label'] !== '' ) : ?>
				<div class="directory-preview-cta">
					<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary directory-demo-cta"<?php jcp_niche_editable_link_attr( 'directory_preview.cta_primary' ); ?>>
						<span><?php echo esc_html( $primary['label'] ); ?></span>
						<?php jcp_component_chevron_svg( 20 ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Conversion section (checklist + image).
 *
 * @param array<string, mixed> $props Block props.
 * @param string               $niche_key Page key.
 */
function jcp_niche_render_conversion( array $props, string $niche_key = '' ): void {
	if ( empty( $props['headline'] ) ) {
		return;
	}
	$primary    = jcp_niche_resolve_cta( $props['cta_primary'] ?? [], $niche_key );
	$section_id = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'conversion';
	$points     = (array) ( $props['points'] ?? [] );
	$media      = jcp_media_props_from_block( $props );
	$image_url  = $media['image_url'];
	$video_url  = $media['video_url'];
	$image_alt  = $media['media_alt'];
	?>
	<section class="jcp-section rankings-section conversion-section jcp-block-conversion" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<div class="conversion-wrapper jcp-split-layout <?php echo esc_attr( jcp_media_position_class( $media['media_position'] ) ); ?>" data-jcp-split-path="conversion" data-jcp-media-position-path="conversion.media_position">
				<div class="conversion-content jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
					<div class="rankings-header">
						<h2<?php jcp_niche_editable_attr( 'conversion.headline' ); ?>><?php echo esc_html( (string) $props['headline'] ); ?></h2>
						<?php if ( ! empty( $props['subheadline'] ) ) : ?>
							<p class="rankings-subtitle"<?php jcp_niche_editable_attr( 'conversion.subheadline' ); ?>><?php echo esc_html( (string) $props['subheadline'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php jcp_niche_render_conversion_points( $points, 'conversion.points' ); ?>
					<div class="conversion-cta"<?php jcp_niche_optional_slot_attr( 'conversion.cta_primary', 'cta', 'Call-to-action button' ); ?>>
						<?php if ( $primary['label'] !== '' ) : ?>
							<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary conversion-cta-btn"<?php jcp_niche_editable_link_attr( 'conversion.cta_primary' ); ?>><?php echo esc_html( $primary['label'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<div class="conversion-visual jcp-split-col jcp-split-col--media" data-jcp-split-col="media">
						<div class="conversion-image-wrapper">
							<?php
							jcp_media_render_slot(
								[
									'path'          => 'conversion',
									'media_type'    => $media['media_type'],
									'image_url'     => $image_url,
									'video_url'     => $video_url,
									'media_alt'     => $image_alt,
									'url_path'      => 'conversion.image_url',
									'alt_path'      => 'conversion.image_alt',
									'img_attrs'     => [
										'class'   => 'conversion-image',
										'width'   => '800',
										'height'  => '600',
										'loading' => 'lazy',
									],
								]
							);
							?>
							<?php if ( $media['media_type'] === 'image' && ( ! empty( $props['image_badge'] ) || ! empty( $props['stats'] ) ) ) : ?>
								<div class="conversion-image-overlay">
									<?php if ( ! empty( $props['image_badge'] ) ) : ?>
										<div class="conversion-badge">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
												<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
												<polyline points="22 4 12 14.01 9 11.01"/>
											</svg>
											<span<?php jcp_niche_editable_attr( 'conversion.image_badge' ); ?>><?php echo esc_html( (string) $props['image_badge'] ); ?></span>
										</div>
									<?php endif; ?>
									<?php if ( ! empty( $props['stats'] ) && is_array( $props['stats'] ) ) : ?>
										<div class="conversion-stats">
											<?php foreach ( $props['stats'] as $si => $stat ) : ?>
												<?php if ( ! is_array( $stat ) ) { continue; } ?>
												<div class="conversion-stat-item">
													<div class="conversion-stat-number"<?php jcp_niche_editable_attr( 'conversion.stats.' . $si . '.value' ); ?>><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?></div>
													<div class="conversion-stat-label"<?php jcp_niche_editable_attr( 'conversion.stats.' . $si . '.label' ); ?>><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render industries archive hub.
 */
function jcp_niche_render_archive(): void {
	$posts = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]
	);
	$total = count( $posts );
	?>
	<span id="jcp-app" data-jcp-page="industries" hidden aria-hidden="true"></span>
	<main class="jcp-marketing jcp-niche jcp-niche-archive">
		<section class="jcp-section rankings-section jcp-archive-hero-section jcp-niche-archive-hero">
			<div class="jcp-container">
				<div class="rankings-header">
					<h1><?php esc_html_e( 'Marketing Software for Home Service Contractors by Trade', 'jcp-core' ); ?></h1>
					<p class="rankings-subtitle"><?php esc_html_e( 'JobCapturePro turns completed jobs into Google visibility, website proof, reviews, and local content, built for plumbers, roofers, HVAC crews, and every trade that runs real work in real neighborhoods.', 'jcp-core' ); ?></p>
				</div>
			</div>
		</section>

		<section class="jcp-section rankings-section jcp-blog-archive-section jcp-industries-archive-section">
			<div class="jcp-container">
				<?php if ( ! empty( $posts ) ) : ?>
					<div class="blog-search-wrapper directory-search-wrapper">
						<div class="directory-search blog-search-bar">
							<div class="search-box blog-search-box industries-search-box">
								<svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
									<circle cx="11" cy="11" r="8"/>
									<path d="m21 21-4.35-4.35"/>
								</svg>
								<input
									type="search"
									class="search-input industries-search-input"
									placeholder="<?php echo esc_attr( $total === 1 ? __( 'Search 1 trade', 'jcp-core' ) : sprintf( __( 'Search %d trades', 'jcp-core' ), $total ) ); ?>"
									data-placeholder-singular="<?php esc_attr_e( 'Search 1 trade', 'jcp-core' ); ?>"
									data-placeholder-plural="<?php echo esc_attr( __( 'Search %d trades', 'jcp-core' ) ); ?>"
									autocomplete="off"
									aria-label="<?php esc_attr_e( 'Search trades', 'jcp-core' ); ?>"
								>
								<button type="button" class="clear-search-btn is-hidden" aria-label="<?php esc_attr_e( 'Clear search', 'jcp-core' ); ?>">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
								</button>
							</div>
							<select class="filter-select industries-sort-filter blog-sort-filter" aria-label="<?php esc_attr_e( 'Sort trades', 'jcp-core' ); ?>">
								<option value="az"><?php esc_html_e( 'A to Z', 'jcp-core' ); ?></option>
								<option value="za"><?php esc_html_e( 'Z to A', 'jcp-core' ); ?></option>
							</select>
							<button type="button" class="clear-filters-btn is-hidden industries-clear-filters blog-clear-filters"><?php esc_html_e( 'Clear filters', 'jcp-core' ); ?></button>
						</div>
					</div>

					<div class="jcp-niche-archive-grid" id="industries-archive-grid">
						<?php foreach ( $posts as $post ) : ?>
							<?php
							$content  = jcp_niche_get_content( (int) $post->ID );
							$label    = ! empty( $content['niche_label'] ) ? (string) $content['niche_label'] : get_the_title( $post );
							$excerpt  = $content['hero']['subheadline'] ?? get_the_excerpt( $post );
							$excerpt  = wp_strip_all_tags( (string) $excerpt );
							$keywords = '';
							if ( ! empty( $content['seo']['keywords'] ) && is_array( $content['seo']['keywords'] ) ) {
								$keywords = implode( ' ', array_map( 'strval', $content['seo']['keywords'] ) );
							}
							?>
							<a
								class="jcp-niche-archive-card"
								href="<?php echo esc_url( get_permalink( $post ) ); ?>"
								data-title="<?php echo esc_attr( strtolower( $label . ' ' . $post->post_name ) ); ?>"
								data-excerpt="<?php echo esc_attr( strtolower( $excerpt ) ); ?>"
								data-keywords="<?php echo esc_attr( strtolower( $keywords ) ); ?>"
								data-sort="<?php echo esc_attr( $label ); ?>"
							>
								<h2 class="jcp-niche-archive-card-title"><?php echo esc_html( $label ); ?></h2>
								<p><?php echo esc_html( $excerpt ); ?></p>
								<span class="jcp-niche-archive-link"><?php esc_html_e( 'See how it works', 'jcp-core' ); ?> →</span>
							</a>
						<?php endforeach; ?>
					</div>
					<p class="jcp-industries-no-results is-hidden" id="industries-no-results"><?php esc_html_e( 'No trades match your search. Try a different keyword.', 'jcp-core' ); ?></p>
				<?php else : ?>
					<p class="jcp-industries-empty"><?php esc_html_e( 'Trade pages coming soon.', 'jcp-core' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	</main>
	<?php
}
