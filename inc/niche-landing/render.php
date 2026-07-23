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
	if ( str_contains( $text, '<a' ) ) {
		jcp_niche_rich_e( $text );
		return;
	}
	echo esc_html( $text );
}

/**
 * Echo text that may contain safe inline links (for rich inline editing).
 *
 * @param string $text Text (may include `<a>` tags).
 */
function jcp_niche_rich_e( string $text ): void {
	$allowed = [
		'a' => [
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
			'class'  => true,
		],
	];
	echo wp_kses( $text, $allowed );
}

/**
 * Resolve page kind for breadcrumb parent link.
 *
 * Uses the WordPress post (type + template) first so imported JSON cannot
 * force an Industries trail on a JCP Block Page.
 *
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_breadcrumb_page_kind( array $c ): string {
	$post_id = get_queried_object_id();
	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			if ( $post->post_type === 'jcp_niche_landing' ) {
				return 'industry';
			}
			if ( $post->post_type === 'page' ) {
				if ( get_page_template_slug( $post_id ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
					return 'referral';
				}
				if ( get_page_template_slug( $post_id ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === $post_id ) {
					return 'home';
				}
				if ( function_exists( 'jcp_page_uses_block_template' ) && jcp_page_uses_block_template( $post_id ) ) {
					return 'marketing';
				}
			}
		}
	}

	if ( ! empty( $c['page_kind'] ) ) {
		return (string) $c['page_kind'];
	}
	if ( is_singular( 'jcp_niche_landing' ) ) {
		return 'industry';
	}
	$page_type = (string) ( $c['page_type'] ?? '' );
	if ( $page_type === 'referral' ) {
		return 'referral';
	}
	if ( $page_type === 'home' || $page_type === 'homepage' ) {
		return 'home';
	}
	return 'marketing';
}

/**
 * Current page label for breadcrumb trail.
 *
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_breadcrumb_current_label( array $c ): string {
	if ( ! empty( $c['page_label'] ) ) {
		return (string) $c['page_label'];
	}
	if ( ! empty( $c['niche_label'] ) ) {
		return (string) $c['niche_label'];
	}
	if ( is_singular() ) {
		$title = get_the_title();
		if ( $title !== '' ) {
			return $title;
		}
	}
	return '';
}

/**
 * Intermediate hub crumb (e.g. Features) when the page lives under a hub path.
 *
 * @param array<string, mixed> $c Content.
 * @return array{label: string, url: string}|null
 */
function jcp_niche_breadcrumb_hub_segment( array $c ): ?array {
	$post_id = get_queried_object_id();
	$path    = '';
	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			$path = trim( (string) get_page_uri( $post ), '/' );
		}
	}

	$preset = sanitize_key( (string) ( $c['preset'] ?? '' ) );
	if ( $path !== '' && str_starts_with( $path, 'features/' ) ) {
		$preset = 'features';
	}

	if ( $preset === 'features' ) {
		$features_page = get_page_by_path( 'features' );
		$url           = $features_page ? get_permalink( $features_page ) : home_url( '/features/' );
		return [
			'label' => __( 'Features', 'jcp-core' ),
			'url'   => (string) $url,
		];
	}

	if ( $path !== '' && str_starts_with( $path, 'industries/' ) ) {
		$hub = get_post_type_archive_link( 'jcp_niche_landing' );
		if ( ! $hub ) {
			$hub = home_url( '/industries/' );
		}
		return [
			'label' => __( 'Industries', 'jcp-core' ),
			'url'   => (string) $hub,
		];
	}

	return null;
}

/**
 * Full breadcrumb trail for the current page.
 *
 * @param array<string, mixed> $c Content.
 * @return array<int, array{label: string, url: string}>
 */
function jcp_niche_breadcrumb_trail( array $c ): array {
	$kind    = jcp_niche_breadcrumb_page_kind( $c );
	$current = jcp_niche_breadcrumb_current_label( $c );
	$trail   = [];

	if ( $kind === 'industry' ) {
		$hub = get_post_type_archive_link( 'jcp_niche_landing' );
		if ( ! $hub ) {
			$hub = home_url( '/industries/' );
		}
		$trail[] = [
			'label' => __( 'Home', 'jcp-core' ),
			'url'   => home_url( '/' ),
		];
		$trail[] = [
			'label' => __( 'Industries', 'jcp-core' ),
			'url'   => (string) $hub,
		];
	} else {
		$trail[] = [
			'label' => __( 'Home', 'jcp-core' ),
			'url'   => home_url( '/' ),
		];
		$hub = jcp_niche_breadcrumb_hub_segment( $c );
		if ( $hub ) {
			$trail[] = $hub;
		}
	}

	if ( $current !== '' ) {
		$trail[] = [
			'label' => $current,
			'url'   => '',
		];
	}

	return $trail;
}

/**
 * Whether the breadcrumb should render.
 *
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_should_show_breadcrumb( array $c ): bool {
	if ( ! empty( $c['hide_breadcrumb'] ) ) {
		return false;
	}
	if ( jcp_niche_breadcrumb_page_kind( $c ) === 'home' ) {
		return false;
	}
	return jcp_niche_breadcrumb_current_label( $c ) !== '';
}

/**
 * @param array<string, mixed> $c Content.
 * @param bool                 $inside_hero Render at top of hero (no separate header band).
 */
function jcp_niche_render_breadcrumb( array $c, bool $inside_hero = false ): void {
	if ( ! jcp_niche_should_show_breadcrumb( $c ) ) {
		return;
	}
	$trail   = jcp_niche_breadcrumb_trail( $c );
	$classes = 'jcp-niche-breadcrumb jcp-container';
	if ( $inside_hero ) {
		$classes .= ' jcp-niche-breadcrumb--in-hero';
	}
	?>
	<nav class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jcp-core' ); ?>">
		<?php foreach ( $trail as $i => $crumb ) : ?>
			<?php if ( $i > 0 ) : ?>
				<span aria-hidden="true">/</span>
			<?php endif; ?>
			<?php if ( ! empty( $crumb['url'] ) ) : ?>
				<a href="<?php echo esc_url( (string) $crumb['url'] ); ?>"><?php echo esc_html( (string) $crumb['label'] ); ?></a>
			<?php else : ?>
				<span><?php echo esc_html( (string) $crumb['label'] ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
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
	$post_id       = (int) get_queried_object_id();
	$is_industry   = function_exists( 'jcp_media_is_industry_post' ) && jcp_media_is_industry_post( $post_id );
	$phone_image   = jcp_media_resolve_phone_image( $h, $post_id );
	$phone_alt     = trim( (string) ( $h['phone_image_alt'] ?? $h['media_alt'] ?? '' ) );
	$phone_locked  = false;
	$phone_cards   = null;
	if ( $is_industry ) {
		$featured = jcp_media_industry_featured_image_url( $post_id );
		if ( $featured !== '' ) {
			$phone_image  = $featured;
			$phone_locked = true;
		}
		$trade_label = ! empty( $c['niche_label'] )
			? (string) $c['niche_label']
			: ( ! empty( $c['page_label'] ) ? (string) $c['page_label'] : '' );
		if ( $trade_label === '' && $post_id > 0 ) {
			$trade_label = get_the_title( $post_id );
		}
		$phone_cards = jcp_media_industry_phone_cards( $trade_label );
	}
	if ( $phone_alt === '' && $post_id > 0 ) {
		$attachment_id = (int) get_post_thumbnail_id( $post_id );
		if ( $attachment_id > 0 ) {
			$featured_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			if ( is_string( $featured_alt ) && $featured_alt !== '' ) {
				$phone_alt = $featured_alt;
			}
		}
		if ( $phone_alt === '' ) {
			$phone_alt = (string) ( $h['h1'] ?? get_the_title( $post_id ) );
		}
	}
	$show_visual = array_key_exists( 'show_visual', $h )
		? ! empty( $h['show_visual'] )
		: ( $variant !== 'centered' );
	$is_condensed = $variant === 'condensed';
	$is_internal  = $variant === 'condensed';
	$show_primary = ! array_key_exists( 'show_cta_primary', $h ) || ! empty( $h['show_cta_primary'] );
	$show_secondary = ! array_key_exists( 'show_cta_secondary', $h ) || ! empty( $h['show_cta_secondary'] );
	$show_trust   = jcp_niche_show_field( $h, 'show_trust_line', true );
	$hero_align   = in_array( (string) ( $c['_hero_align'] ?? '' ), [ 'left', 'center', 'right' ], true )
		? (string) $c['_hero_align']
		: ( $variant === 'centered' ? 'center' : 'left' );
	?>
	<section class="jcp-section jcp-hero jcp-niche-hero jcp-hero-variant-<?php echo esc_attr( $variant ); ?> jcp-layout-align-<?php echo esc_attr( $hero_align ); ?><?php echo $show_visual ? ' jcp-hero-has-visual' : ' jcp-hero--no-visual'; ?><?php echo $is_internal ? ' jcp-niche-hero--internal' : ''; ?><?php echo $is_condensed ? ' jcp-niche-hero--condensed' : ''; ?>">
		<?php if ( $is_internal && jcp_niche_should_show_breadcrumb( $c ) ) : ?>
			<?php jcp_niche_render_breadcrumb( $c, true ); ?>
		<?php endif; ?>
		<div class="jcp-container">
			<div class="jcp-hero-grid jcp-split-layout <?php echo esc_attr( jcp_media_position_class( $media['media_position'] ) ); ?>" data-jcp-split-path="hero" data-jcp-media-position-path="hero.media_position">
				<div class="jcp-hero-copy hero-copy jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
					<?php if ( $is_home ) : ?>
						<h1 class="jcp-hero-title" data-jcp-heading-tag-path="hero.headline_tag">
							<span<?php jcp_niche_editable_attr( 'hero.h1_prefix' ); ?>><?php echo esc_html( (string) ( $h['h1_prefix'] ?? $h['h1'] ?? '' ) ); ?></span>
							<span class="jcp-hero-title-end">
								<?php esc_html_e( 'more', 'jcp-core' ); ?>
								<span class="jcp-hero-rotating-word" aria-live="polite" data-words="<?php echo esc_attr( wp_json_encode( array_values( (array) $h['rotating_words'] ) ) ); ?>">
									<?php echo esc_html( (string) ( $h['rotating_words'][0] ?? 'visibility' ) ); ?>
								</span>
							</span>
						</h1>
					<?php else : ?>
					<h1 class="jcp-hero-title" data-jcp-heading-tag-path="hero.headline_tag"<?php jcp_niche_editable_attr( 'hero.h1' ); ?>><?php jcp_niche_e( (string) $h['h1'] ); ?></h1>
					<?php endif; ?>
					<?php if ( ! empty( $h['subheadline'] ) && jcp_niche_show_field( $h, 'show_subheadline', true ) ) : ?>
						<p class="jcp-hero-subtitle"<?php jcp_niche_editable_attr( 'hero.subheadline' ); ?>><?php jcp_niche_e( (string) $h['subheadline'] ); ?></p>
					<?php endif; ?>
					<div class="jcp-actions directory-cta-row"<?php echo ( ! $show_primary && ! $show_secondary ) ? ' style="display:none"' : ''; ?>>
						<?php if ( $show_primary && $primary['label'] !== '' ) : ?>
							<div class="jcp-hero-primary-cta">
								<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'hero.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], str_contains( $primary['url'], 'firstpromoter.com' ) ? 'referral_hero' : 'niche_hero', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
								<?php
								$cta_microcopy = trim( (string) ( $h['cta_microcopy'] ?? '' ) );
								if ( $is_home && $cta_microcopy !== '' && $show_trust ) :
									?>
									<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"<?php jcp_niche_editable_attr( 'hero.cta_microcopy' ); ?>><?php jcp_niche_e( $cta_microcopy ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( $show_secondary && $secondary['label'] !== '' ) : ?>
							<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'hero.cta_secondary' ); ?>><?php jcp_niche_e( $secondary['label'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php if ( ! $is_home && $show_trust && ! empty( $h['trust_line'] ) ) : ?>
						<p class="jcp-niche-trust-line"<?php jcp_niche_editable_attr( 'hero.trust_line' ); ?>><?php jcp_niche_e( (string) $h['trust_line'] ); ?></p>
					<?php endif; ?>
					<?php if ( $is_home && ! empty( $h['meta_stats'] ) && jcp_niche_show_field( $h, 'show_meta_stats', true ) ) : ?>
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
							'url_path'           => 'hero.image_url',
							'alt_path'           => 'hero.media_alt',
							'media_type'         => $media['media_type'],
							'image_url'          => $media['image_url'],
							'video_url'          => $media['video_url'],
							'media_alt'          => $media['media_alt'],
							'default_image'      => $is_condensed ? '' : $default_photo,
							'phone_mockup_style' => 'live_demo',
							'img_attrs'          => [
								'class'   => 'jcp-hero-slot-image',
								'width'   => '640',
								'height'  => '480',
								'loading' => 'eager',
							],
							'phone_render'  => function () use ( $hero_demo, $phone_image, $phone_alt, $phone_cards, $phone_locked ) {
								jcp_component_hero_home_visual( $hero_demo, $phone_image, $phone_alt, true, $phone_cards, $phone_locked );
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
	jcp_niche_render_split_media_block(
		$props,
		$path,
		'',
		[
			'variant'        => 'card',
			'wrap_container' => true,
			'root_class'     => 'jcp-block-media-text',
		]
	);
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
	$show_icons = jcp_niche_show_field( $w, 'show_icons', true );
	$vis_class  = jcp_niche_section_visibility_classes(
		$w,
		[
			'show_icons'       => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
		]
	);
	$team_icon  = ! empty( $w['team_already_icon'] ) ? (string) $w['team_already_icon'] : 'wrench';
	$turns_icon = ! empty( $w['turns_into_icon'] ) ? (string) $w['turns_into_icon'] : 'sparkles';
	$hl         = jcp_niche_field_visibility( $w, 'show_headline', true );
	$sub        = jcp_niche_field_visibility( $w, 'show_subheadline', true );
	$closing    = jcp_niche_field_visibility( $w, 'show_closing', true );
	$sub_text   = trim( (string) ( $w['subheadline'] ?? '' ) );
	$close_text = trim( (string) ( $w['closing'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section jcp-niche-what<?php echo esc_attr( $vis_class ); ?>">
		<div class="jcp-container">
			<?php if ( $hl['render'] || ( $sub['render'] && $sub_text !== '' ) ) : ?>
			<div class="rankings-header">
				<?php if ( $hl['render'] ) : ?>
				<?php
				$heading_tag = jcp_niche_heading_tag_from_props( $w, 'h2', false );
				jcp_niche_open_heading( $heading_tag, 'jcp-section-headline', 'what_it_is.headline', 'what_it_is.headline_tag', $hl['attr'] );
				jcp_niche_e( (string) $w['headline'] );
				jcp_niche_close_heading( $heading_tag );
				?>
				<?php endif; ?>
				<?php if ( $sub['render'] && $sub_text !== '' ) : ?>
					<p class="rankings-subtitle"<?php echo $sub['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_editable_rich_attr( 'what_it_is.subheadline' ); ?>><?php jcp_niche_rich_e( $sub_text ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="ranking-factors-grid jcp-niche-split-grid">
				<?php
				$team_title   = ! empty( $w['team_already_title'] ) ? (string) $w['team_already_title'] : __( 'Your team is already', 'jcp-core' );
				$turns_title  = ! empty( $w['turns_into_title'] ) ? (string) $w['turns_into_title'] : __( 'Turns real jobs into', 'jcp-core' );
				jcp_niche_factor_card(
					$team_title,
					$team_icon,
					'',
					'',
					function () use ( $w ) {
						echo '<ul class="jcp-niche-checklist"';
						jcp_niche_array_attr( 'what_it_is.team_already' );
						echo '>';
						foreach ( (array) ( $w['team_already'] ?? [] ) as $ti => $line ) {
							echo '<li class="jcp-collection-item"';
							jcp_niche_array_item_attr( (int) $ti );
							echo '><span class="jcp-checklist-item__text"';
							jcp_niche_editable_attr( 'what_it_is.team_already.' . $ti );
							echo '>' . esc_html( jcp_niche_clean_step_line( (string) $line ) ) . '</span>';
							jcp_niche_collection_remove_btn( true );
							echo '</li>';
						}
						jcp_niche_collection_add_btn( __( '+ Add item', 'jcp-core' ) );
						echo '</ul>';
					},
					'what_it_is.team_already_title',
					'',
					'',
					-1,
					'what_it_is.team_already_icon',
					$show_icons
				);
				jcp_niche_factor_card(
					$turns_title,
					$turns_icon,
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
							echo '<li class="jcp-collection-item"';
							jcp_niche_array_item_attr( (int) $ti );
							echo '><span class="jcp-checklist-item__text"';
							jcp_niche_editable_attr( 'what_it_is.turns_into.' . $ti );
							echo '>' . esc_html( jcp_niche_clean_step_line( (string) $line ) ) . '</span>';
							jcp_niche_collection_remove_btn( true );
							echo '</li>';
						}
						jcp_niche_collection_add_btn( __( '+ Add item', 'jcp-core' ) );
						echo '</ul>';
					},
					'what_it_is.turns_into_title',
					'',
					'',
					-1,
					'what_it_is.turns_into_icon',
					$show_icons
				);
				?>
			</div>
			<?php
			if ( $closing['render'] && $close_text !== '' ) {
				echo '<p class="rankings-supporting jcp-niche-section-closing"' . $closing['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				jcp_niche_editable_attr( 'what_it_is.closing' );
				echo '>' . esc_html( $close_text ) . '</p>';
			}
			$mechanic = $c['core_mechanic'] ?? [];
			if ( ! empty( $mechanic ) && is_array( $mechanic ) ) {
				echo '<div class="jcp-core-mechanic-embed">';
				jcp_niche_render_core_mechanic_strip( $mechanic, 'core_mechanic' );
				echo '</div>';
			}
			jcp_niche_render_section_optional_ctas( $w, 'what_it_is', (string) ( $c['niche_key'] ?? $c['page_key'] ?? '' ) );
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
	$primary = jcp_niche_resolve_cta(
		$h['cta_primary'] ?? [
			'label' => $h['cta_label'] ?? '',
			'url'   => $h['cta_url'] ?? '',
		],
		$niche_key
	);
	$secondary = jcp_niche_resolve_cta( $h['cta_secondary'] ?? [], $niche_key );
	$numeric_steps = ! empty( $h['numeric_steps'] );
	$section_id    = ! empty( $h['section_id'] ) ? (string) $h['section_id'] : 'how-it-works';
	$steps_vis     = jcp_niche_field_visibility( $h, 'show_steps', true );
	?>
	<section class="jcp-section rankings-section" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $h, 'how_it_works' ); ?>
			<?php if ( $steps_vis['render'] ) : ?>
			<div class="timeline-steps"<?php echo $steps_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_array_attr( 'how_it_works.steps' ); ?>>
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
							<?php jcp_niche_render_step_lines( $lines, 'how_it_works.steps.' . $i . '.lines' ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php
			jcp_niche_render_section_optional_ctas(
				array_merge(
					[
						'cta_primary'   => [ 'label' => $primary['label'], 'url' => $primary['url'] ],
						'cta_secondary' => $secondary,
					],
					array_intersect_key(
						$h,
						array_flip( [ 'show_cta', 'show_cta_secondary' ] )
					)
				),
				'how_it_works',
				$niche_key,
				[ 'secondary' => true ]
			);
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
	$show_icons = jcp_niche_show_field( $ch, 'show_icons', true );
	$vis_class  = jcp_niche_section_visibility_classes(
		$ch,
		[
			'show_icons'       => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
		]
	);
	$tags       = jcp_niche_field_visibility( $ch, 'show_tags', true );
	$closing    = jcp_niche_field_visibility( $ch, 'show_closing', true );
	$close_text = trim( (string) ( $ch['closing'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section jcp-niche-checkins<?php echo esc_attr( $vis_class ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $ch, 'check_ins' ); ?>
			<?php if ( $tags['render'] ) : ?>
			<div class="jcp-niche-tags-wrap"<?php echo $tags['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<ul class="jcp-niche-tags"<?php jcp_niche_array_attr( 'check_ins.job_types' ); ?>>
					<?php foreach ( (array) ( $ch['job_types'] ?? [] ) as $ti => $tag ) : ?>
						<li<?php jcp_niche_array_item_attr( (int) $ti ); ?>><span class="jcp-checklist-item__text"<?php jcp_niche_editable_attr( 'check_ins.job_types.' . $ti ); ?>><?php echo esc_html( jcp_niche_clean_step_line( (string) $tag ) ); ?></span></li>
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
					$icon = ! empty( $feat['icon'] ) ? (string) $feat['icon'] : ( $feat_icons[ $fi ] ?? 'badge-check' );
					jcp_niche_factor_card(
						(string) ( $feat['title'] ?? '' ),
						$icon,
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
						(int) $fi,
						'check_ins.features.' . $fi . '.icon',
						$show_icons
					);
				endforeach;
				?>
			</div>
			<?php
			if ( $closing['render'] && $close_text !== '' ) {
				jcp_niche_render_section_closing( $close_text, 'check_ins.closing', $closing['attr'] );
			}
			jcp_niche_render_section_optional_ctas( $ch, 'check_ins', (string) ( $c['niche_key'] ?? $c['page_key'] ?? '' ) );
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
	$show_icons = jcp_niche_show_field( $p, 'show_icons', true );
	$vis_class  = jcp_niche_section_visibility_classes(
		$p,
		[
			'show_icons'       => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
		]
	);
	$closing    = jcp_niche_field_visibility( $p, 'show_closing', true );
	$close_text = trim( (string) ( $p['closing'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section jcp-niche-problem<?php echo esc_attr( $vis_class ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $p, 'problem' ); ?>
			<div class="ranking-factors-grid"<?php jcp_niche_array_attr( 'problem.pain_points' ); ?>>
				<?php
				$pain_icons = [ 'image-off', 'clock', 'map-pin', 'users' ];
				foreach ( (array) ( $p['pain_points'] ?? [] ) as $pi => $pain ) :
					if ( ! is_array( $pain ) ) {
						continue;
					}
					$icon = ! empty( $pain['icon'] ) ? (string) $pain['icon'] : ( $pain_icons[ $pi ] ?? 'circle-alert' );
					jcp_niche_factor_card(
						(string) ( $pain['title'] ?? '' ),
						$icon,
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
						(int) $pi,
						'problem.pain_points.' . $pi . '.icon',
						$show_icons
					);
				endforeach;
				?>
			</div>
			<?php
			if ( $closing['render'] && $close_text !== '' ) {
				jcp_niche_render_section_closing( $close_text, 'problem.closing', $closing['attr'] );
			}
			jcp_niche_render_section_optional_ctas( $p, 'problem', (string) ( $c['niche_key'] ?? $c['page_key'] ?? '' ) );
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
	$items = (array) ( $b['items'] ?? [] );
	$headline = trim( (string) ( $b['headline'] ?? '' ) );
	if ( $headline === '' && empty( $items ) ) {
		return;
	}
	$section_id = ! empty( $b['section_id'] ) ? (string) $b['section_id'] : '';
	$vis_class  = jcp_niche_section_visibility_classes(
		$b,
		[
			'show_icons'       => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
			'show_card_stats'  => true,
		]
	);
	$show_icons = jcp_niche_show_field( $b, 'show_icons', true );
	$hl         = jcp_niche_field_visibility( $b, 'show_headline', true );
	$sub        = jcp_niche_field_visibility( $b, 'show_subheadline', true );
	$closing    = jcp_niche_field_visibility( $b, 'show_closing', true );
	$sub_text   = trim( (string) ( $b['subheadline'] ?? '' ) );
	$close_text = trim( (string) ( $b['closing'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section jcp-niche-benefits<?php echo esc_attr( $vis_class ); ?>"<?php echo $section_id !== '' ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
		<div class="jcp-container">
			<?php if ( $hl['render'] || ( $sub['render'] && $sub_text !== '' ) ) : ?>
			<div class="rankings-header">
				<?php if ( $hl['render'] && $headline !== '' ) : ?>
				<?php
				$heading_tag = jcp_niche_heading_tag_from_props( $b, 'h2', false );
				jcp_niche_open_heading( $heading_tag, 'jcp-section-headline', 'benefits.headline', 'benefits.headline_tag', $hl['attr'] );
				jcp_niche_e( $headline );
				jcp_niche_close_heading( $heading_tag );
				?>
				<?php endif; ?>
				<?php if ( $sub['render'] && $sub_text !== '' ) : ?>
					<p class="rankings-subtitle"<?php echo $sub['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_editable_attr( 'benefits.subheadline' ); ?>><?php jcp_niche_e( $sub_text ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="ranking-factors-grid"<?php jcp_niche_array_attr( 'benefits.items' ); ?>>
				<?php
				$benefit_icons = [ 'badge-check', 'map-pin', 'message-square', 'star', 'building-2', 'phone' ];
				foreach ( $items as $bi => $item ) :
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
						(int) $bi,
						'benefits.items.' . $bi . '.icon',
						$show_icons
					);
				endforeach;
				?>
			</div>
			<?php
			if ( $closing['render'] && $close_text !== '' ) {
				echo '<p class="rankings-supporting jcp-niche-section-closing"' . $closing['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				jcp_niche_editable_attr( 'benefits.closing' );
				echo '>' . esc_html( $close_text ) . '</p>';
			}
			jcp_niche_render_section_optional_ctas( $b, 'benefits', '', [ 'secondary' => true ] );
			?>
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
				<?php
				$heading_tag = jcp_niche_heading_tag_from_props( $m, 'h2', false );
				jcp_niche_open_heading( $heading_tag, 'jcp-section-headline', 'commission.headline', 'commission.headline_tag' );
				jcp_niche_e( (string) $m['headline'] );
				jcp_niche_close_heading( $heading_tag );
				?>
				<?php if ( ! empty( $m['subheadline'] ) && jcp_niche_show_field( $m, 'show_subheadline', true ) ) : ?>
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
				<?php
				$heading_tag = jcp_niche_heading_tag_from_props( $p, 'h2', false );
				jcp_niche_open_heading( $heading_tag, 'jcp-section-headline', 'partners.headline', 'partners.headline_tag' );
				jcp_niche_e( (string) $p['headline'] );
				jcp_niche_close_heading( $heading_tag );
				?>
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
				<?php
				$heading_tag = jcp_niche_heading_tag_from_props( $s, 'h2', false );
				jcp_niche_open_heading( $heading_tag, 'jcp-section-headline', 'share.headline', 'share.headline_tag' );
				jcp_niche_e( (string) $s['headline'] );
				jcp_niche_close_heading( $heading_tag );
				?>
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
	$vis_class = jcp_niche_section_visibility_classes( $d, [ 'show_icons' => true ] );
	$sub       = jcp_niche_field_visibility( $d, 'show_subheadline', true );
	$body_text = trim( (string) ( $d['body'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section jcp-niche-diff<?php echo esc_attr( $vis_class ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $d, 'differentiation', [ 'header_class' => 'rankings-header jcp-niche-diff-header' ] ); ?>
			<div class="jcp-niche-diff-panel">
				<?php if ( $sub['render'] && $body_text !== '' ) : ?>
					<p class="jcp-niche-diff-lead"<?php echo $sub['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_editable_rich_attr( 'differentiation.body' ); ?>><?php jcp_niche_rich_e( $body_text ); ?></p>
				<?php endif; ?>
				<?php
				jcp_niche_render_conversion_points(
					(array) ( $d['bullets'] ?? [] ),
					'differentiation.bullets',
					[
						'layout'     => 'columns',
						'per_column' => 5,
					]
				);
				?>
			</div>
			<?php jcp_niche_render_section_optional_ctas( $d, 'differentiation', (string) ( $c['niche_key'] ?? $c['page_key'] ?? '' ) ); ?>
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
	$show_icons = jcp_niche_show_field( $w, 'show_icons', true );
	$vis_class  = jcp_niche_section_visibility_classes(
		$w,
		[
			'show_icons'       => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
			'show_card_stats'  => true,
			'show_card_images' => true,
			'show_card_badges' => true,
		]
	);
	$cards_vis = jcp_niche_field_visibility( $w, 'show_cards', true );
	?>
	<section class="jcp-section rankings-section jcp-niche-audiences<?php echo esc_attr( $vis_class ); ?>" id="who-its-for">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $w, 'who_its_for' ); ?>
			<?php if ( $cards_vis['render'] ) : ?>
			<?php if ( $variant === 'guarantees' ) : ?>
				<div class="guarantees-grid"<?php echo $cards_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_array_attr( 'who_its_for.audiences' ); ?>>
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
			<div class="ranking-factors-grid jcp-niche-split-grid"<?php echo $cards_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_array_attr( 'who_its_for.audiences' ); ?>>
				<?php
				$aud_icons = [ 'briefcase', 'hard-hat', 'trending-up' ];
				foreach ( (array) ( $w['audiences'] ?? [] ) as $ai => $aud ) :
					if ( ! is_array( $aud ) ) {
						continue;
					}
					$icon_slug = ! empty( $aud['icon'] ) ? (string) $aud['icon'] : ( $aud_icons[ $ai ] ?? 'users' );
					jcp_niche_factor_card(
						(string) ( $aud['title'] ?? '' ),
						$icon_slug,
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
						(int) $ai,
						'who_its_for.audiences.' . $ai . '.icon',
						$show_icons
					);
				endforeach;
				?>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<?php jcp_niche_render_section_optional_ctas( $w, 'who_its_for', (string) ( $c['niche_key'] ?? $c['page_key'] ?? '' ) ); ?>
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
	$items_vis = jcp_niche_field_visibility( $f, 'show_items', true );
	?>
	<section class="jcp-section rankings-section faq-section" id="faq">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $f, 'faq' ); ?>
			<?php if ( $items_vis['render'] ) : ?>
			<div class="faq-grid"<?php echo $items_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_array_attr( 'faq.items' ); ?>>
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
							jcp_niche_editable_rich_attr( $apath );
							echo '>' . wp_kses(
								$para,
								[
									'a' => [
										'href'   => true,
										'title'  => true,
										'target' => true,
										'rel'    => true,
										'class'  => true,
									],
								]
							) . '</p>';
						}
						?>
					</details>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php jcp_niche_render_section_optional_ctas( $f, 'faq', (string) ( $c['niche_key'] ?? $c['page_key'] ?? '' ) ); ?>
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
	$show_sub = jcp_niche_show_field( $f, 'show_subheadline', true );
	$show_note = jcp_niche_show_field( $f, 'show_cta_note', true );
	$show_headline = jcp_niche_show_field( $f, 'show_headline', true );
	$show_cta = jcp_niche_show_field( $f, 'show_cta', true );
	$heading_tag = jcp_niche_heading_tag_from_props( $f, 'h3', false );
	?>
	<section class="jcp-section rankings-section jcp-niche-final">
		<div class="jcp-container">
			<div class="rankings-cta">
				<div class="cta-content">
					<?php if ( $show_headline ) : ?>
					<?php
					jcp_niche_open_heading( $heading_tag, 'jcp-section-headline', 'final_cta.headline', 'final_cta.headline_tag' );
					jcp_niche_e( (string) $f['headline'] );
					jcp_niche_close_heading( $heading_tag );
					?>
					<?php endif; ?>
					<?php if ( ! empty( $f['subheadline'] ) && $show_sub ) : ?>
						<p class="cta-paragraph"<?php jcp_niche_editable_attr( 'final_cta.subheadline' ); ?>><?php jcp_niche_e( (string) $f['subheadline'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $show_cta ) : ?>
				<div class="cta-button-wrapper">
					<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $url ); ?>"<?php jcp_niche_editable_link_attr( 'final_cta.cta_primary' ); jcp_niche_cta_tracking_attr( $url, str_contains( $url, 'firstpromoter.com' ) ? 'referral_footer' : 'niche_footer', $btn ); ?>><?php echo esc_html( $btn ); ?></a>
					<?php if ( $show_note ) : ?>
						<p class="cta-note"<?php jcp_niche_editable_attr( 'final_cta.cta_note' ); ?>><?php echo esc_html( $note ); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>
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
	$section_id = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'demo-preview';
	jcp_niche_render_split_media_block(
		$props,
		$path,
		$niche_key,
		[
			'variant'    => 'card',
			'section_id' => $section_id,
			'root_class' => 'jcp-block-demo-preview',
		]
	);
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
	$items_vis  = jcp_niche_field_visibility( $props, 'show_items', true );
	$callout    = jcp_niche_field_visibility( $props, 'show_callout', true );
	$link       = jcp_niche_field_visibility( $props, 'show_link', true );
	?>
	<section class="jcp-section rankings-section jcp-block-proof-flow" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $props, 'proof_flow' ); ?>
			<?php if ( $items_vis['render'] ) : ?>
			<div class="proof-flow"<?php echo $items_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<?php endif; ?>
			<?php if ( $callout['render'] && ! empty( $props['callout_title'] ) ) : ?>
				<div class="real-job-proof-callout"<?php echo $callout['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<?php if ( $link['render'] && ! empty( $props['link_label'] ) && ! empty( $props['link_url'] ) ) : ?>
				<div class="timeline-cta" style="margin-top: var(--jcp-space-3xl);<?php echo $link['show'] ? '' : 'display:none;'; ?>">
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
	$cards_vis  = jcp_niche_field_visibility( $props, 'show_cards', true );
	$outro_vis  = jcp_niche_field_visibility( $props, 'show_outro', true );
	$cta_vis    = jcp_niche_field_visibility( $props, 'show_cta', true );
	$outro_text = trim( (string) ( $props['outro'] ?? '' ) );
	?>
	<section class="jcp-section rankings-section directory-preview jcp-block-directory-preview" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $props, 'directory_preview' ); ?>
			<?php if ( $cards_vis['render'] ) : ?>
			<div class="directory-grid preview-grid"<?php echo $cards_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php foreach ( (array) ( $props['cards'] ?? [] ) as $ci => $card ) : ?>
					<?php
					if ( ! is_array( $card ) ) {
						continue;
					}
					jcp_component_directory_preview_card( $card, (int) $ci );
					?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $outro_vis['render'] && $outro_text !== '' ) : ?>
				<p class="directory-preview-outro"<?php echo $outro_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_editable_attr( 'directory_preview.outro' ); ?>><?php echo esc_html( $outro_text ); ?></p>
			<?php endif; ?>
			<?php if ( $cta_vis['render'] && ( $primary['label'] !== '' || jcp_niche_user_can_inline_edit() ) ) : ?>
				<div class="directory-preview-cta"<?php echo $cta_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php if ( $primary['label'] !== '' ) : ?>
					<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary directory-demo-cta"<?php jcp_niche_editable_link_attr( 'directory_preview.cta_primary' ); ?>>
						<span><?php echo esc_html( $primary['label'] ); ?></span>
						<?php jcp_component_chevron_svg( 20 ); ?>
					</a>
					<?php endif; ?>
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
	$vis_class  = jcp_niche_section_visibility_classes( $props, [ 'show_icons' => true ] );
	$points_vis = jcp_niche_field_visibility( $props, 'show_points', true );
	$media_vis  = jcp_niche_field_visibility( $props, 'show_media', true );
	$stats_vis  = jcp_niche_field_visibility( $props, 'show_stats', true );
	$cta_vis    = jcp_niche_field_visibility( $props, 'show_cta', true );
	?>
	<section class="jcp-section rankings-section conversion-section jcp-block-conversion<?php echo esc_attr( $vis_class ); ?>" id="<?php echo esc_attr( $section_id ); ?>">
		<div class="jcp-container">
			<div class="conversion-wrapper jcp-split-layout <?php echo esc_attr( jcp_media_position_class( $media['media_position'] ) ); ?>" data-jcp-split-path="conversion" data-jcp-media-position-path="conversion.media_position">
				<div class="conversion-content jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
					<?php jcp_niche_render_section_header( $props, 'conversion' ); ?>
					<?php if ( $points_vis['render'] ) : ?>
						<div<?php echo $points_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php jcp_niche_render_conversion_points( $points, 'conversion.points' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $cta_vis['render'] ) : ?>
					<div class="conversion-cta"<?php echo $cta_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php jcp_niche_optional_slot_attr( 'conversion.cta_primary', 'cta', 'Call-to-action button' ); ?>>
						<?php if ( $primary['label'] !== '' ) : ?>
							<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary conversion-cta-btn"<?php jcp_niche_editable_link_attr( 'conversion.cta_primary' ); ?>><?php echo esc_html( $primary['label'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php if ( $media_vis['render'] ) : ?>
				<div class="conversion-visual jcp-split-col jcp-split-col--media" data-jcp-split-col="media"<?php echo $media_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
									<?php if ( $stats_vis['render'] && ! empty( $props['stats'] ) && is_array( $props['stats'] ) ) : ?>
										<div class="conversion-stats"<?php echo $stats_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
				<?php endif; ?>
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
							$thumb_url = function_exists( 'jcp_nav_resolve_thumbnail_url' )
								? jcp_nav_resolve_thumbnail_url( (int) $post->ID, is_array( $content ) ? $content : [] )
								: (string) get_the_post_thumbnail_url( $post, 'medium_large' );
							$thumb_alt = $label;
							if ( ! empty( $content['hero']['media_alt'] ) ) {
								$thumb_alt = (string) $content['hero']['media_alt'];
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
								<?php if ( $thumb_url !== '' ) : ?>
									<span class="jcp-niche-archive-card-thumb" aria-hidden="true">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" decoding="async" width="640" height="400" />
									</span>
								<?php endif; ?>
								<?php if ( $excerpt !== '' ) : ?>
									<p class="jcp-niche-archive-card-excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 28, '…' ) ); ?></p>
								<?php endif; ?>
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
