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
	$secondary = jcp_niche_resolve_cta( $h['cta_secondary'] ?? [ 'label' => 'Start Free 14-Day Trial', 'url' => '' ], $niche_key );
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
						<?php
						/* Keep "into more {rotator}" as one wrap unit so "into" is never stranded alone. */
						$h1_prefix = (string) ( $h['h1_prefix'] ?? $h['h1'] ?? '' );
						$h1_prefix = preg_replace( '/\s+into\s*$/iu', '', $h1_prefix ) ?? $h1_prefix;
						$h1_prefix = rtrim( $h1_prefix ) . ' ';
						?>
						<h1 class="jcp-hero-title" data-jcp-heading-tag-path="hero.headline_tag">
							<span<?php jcp_niche_editable_attr( 'hero.h1_prefix' ); ?>><?php echo esc_html( $h1_prefix ); ?></span>
							<span class="jcp-hero-title-end jcp-hero-more-rotator">
								<?php echo esc_html( __( 'into', 'jcp-core' ) . ' ' . __( 'more', 'jcp-core' ) ); ?>
								<span class="jcp-hero-rotating-word" aria-live="polite" data-words="<?php echo esc_attr( wp_json_encode( array_values( (array) $h['rotating_words'] ) ) ); ?>">
									<?php echo esc_html( (string) ( $h['rotating_words'][0] ?? 'visibility' ) ); ?>
								</span>
							</span>
						</h1>
					<?php elseif ( trim( (string) ( $h['h1_emphasis'] ?? '' ) ) !== '' ) : ?>
						<h1 class="jcp-hero-title" data-jcp-heading-tag-path="hero.headline_tag">
							<span<?php jcp_niche_editable_attr( 'hero.h1' ); ?>><?php jcp_niche_e( (string) ( $h['h1'] ?? '' ) ); ?></span>
							<span class="jcp-hero-emphasis"<?php jcp_niche_editable_attr( 'hero.h1_emphasis' ); ?>><?php jcp_niche_e( (string) $h['h1_emphasis'] ); ?></span>
						</h1>
					<?php else : ?>
					<h1 class="jcp-hero-title" data-jcp-heading-tag-path="hero.headline_tag"<?php jcp_niche_editable_attr( 'hero.h1' ); ?>><?php jcp_niche_e( (string) $h['h1'] ); ?></h1>
					<?php endif; ?>
					<?php if ( ! empty( $h['subheadline'] ) && jcp_niche_show_field( $h, 'show_subheadline', true ) ) : ?>
						<p class="jcp-hero-subtitle"<?php jcp_niche_editable_attr( 'hero.subheadline' ); ?>><?php jcp_niche_e( (string) $h['subheadline'] ); ?></p>
					<?php endif; ?>
					<?php if ( $show_primary || $show_secondary ) : ?>
					<div class="jcp-actions directory-cta-row">
						<?php if ( $show_primary && $primary['label'] !== '' ) : ?>
							<?php
							$cta_microcopy   = trim( (string) ( $h['cta_microcopy'] ?? '' ) );
							$trust_text      = $cta_microcopy !== '' ? $cta_microcopy : trim( (string) ( $h['trust_line'] ?? '' ) );
							$trust_path      = $cta_microcopy !== '' ? 'hero.cta_microcopy' : 'hero.trust_line';
							/* Home + campaign: microcopy stacks inside the primary button (LP pattern). */
							$microcopy_in_btn = $show_trust && $trust_text !== '' && (
								$is_home
								|| ! empty( $c['campaign_landing'] )
								|| ( function_exists( 'jcp_page_current_is_campaign_landing' ) && jcp_page_current_is_campaign_landing() )
							);
							?>
							<div class="jcp-hero-primary-cta">
								<?php if ( $microcopy_in_btn ) : ?>
									<a class="btn btn-primary jcp-hero-cta-stacked" href="<?php echo esc_url( $primary['url'] ); ?>" data-jcp-href-path="hero.cta_primary.url"<?php jcp_niche_cta_tracking_attr( $primary['url'], str_contains( $primary['url'], 'firstpromoter.com' ) ? 'referral_hero' : 'niche_hero', $primary['label'] ); ?>>
										<span class="jcp-hero-cta-label"<?php jcp_niche_editable_attr( 'hero.cta_primary.label' ); ?>><?php jcp_niche_e( $primary['label'] ); ?></span>
										<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"<?php jcp_niche_editable_attr( $trust_path ); ?>><?php jcp_niche_e( $trust_text ); ?></span>
									</a>
								<?php else : ?>
									<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'hero.cta_primary' ); jcp_niche_cta_tracking_attr( $primary['url'], str_contains( $primary['url'], 'firstpromoter.com' ) ? 'referral_hero' : 'niche_hero', $primary['label'] ); ?>><?php jcp_niche_e( $primary['label'] ); ?></a>
									<?php if ( $show_trust && $trust_text !== '' ) : ?>
										<span class="jcp-hero-cta-microcopy jcp-niche-trust-line"<?php jcp_niche_editable_attr( $trust_path ); ?>><?php jcp_niche_e( $trust_text ); ?></span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( $show_secondary && $secondary['label'] !== '' ) : ?>
							<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'hero.cta_secondary' ); ?>><?php jcp_niche_e( $secondary['label'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					<?php
					$social_proof = is_array( $h['social_proof'] ?? null ) ? $h['social_proof'] : [];
					if ( $social_proof !== [] && jcp_niche_show_field( $h, 'show_social_proof', true ) && function_exists( 'jcp_component_hero_social_proof' ) ) {
						jcp_component_hero_social_proof( $social_proof, 'hero.social_proof' );
					}
					?>
					<?php if ( ! empty( $h['meta_stats'] ) && jcp_niche_show_field( $h, 'show_meta_stats', true ) ) : ?>
						<?php jcp_component_home_meta_stats( (array) $h['meta_stats'] ); ?>
					<?php endif; ?>
				</div>
				<?php if ( $show_visual ) : ?>
				<div class="jcp-split-col jcp-split-col--media jcp-hero-visual-column" data-jcp-split-col="media" aria-hidden="false">
					<?php
					$phone_link = trim( (string) ( $h['media_link_url'] ?? '' ) );
					$hero_demo  = $phone_link !== ''
						? $phone_link
						: ( $primary['url'] !== '' ? $primary['url'] : $demo_url );
					$phone_cta  = trim( (string) ( $h['phone_cta_label'] ?? '' ) );
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
							'phone_render'  => function () use ( $hero_demo, $phone_image ) {
								jcp_component_demo_app_phone( $hero_demo, $phone_image );
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
	$can_edit   = jcp_niche_user_can_inline_edit();
	$team_lead  = trim( (string) ( $w['team_already_lead'] ?? '' ) );
	$turns_lead = trim( (string) ( $w['lead'] ?? '' ) );
	if ( $turns_lead === '' && ! $can_edit ) {
		$turns_lead = __( 'But once the work is done, most of it disappears. JobCapturePro changes that.', 'jcp-core' );
	}
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
				$what_pieces = [
					'show_title' => jcp_niche_show_field( $w, 'show_card_titles', true ),
					'show_body'  => jcp_niche_show_field( $w, 'show_card_body', true ),
				];
				jcp_niche_factor_card(
					$team_title,
					$team_icon,
					'',
					'',
					function () use ( $w, $team_lead, $can_edit ) {
						if ( $team_lead !== '' || $can_edit ) {
							echo '<p class="jcp-niche-card-lead"';
							jcp_niche_editable_attr( 'what_it_is.team_already_lead' );
							if ( $can_edit ) {
								echo ' data-placeholder="' . esc_attr__( 'Add intro text…', 'jcp-core' ) . '"';
							}
							echo '>' . esc_html( $team_lead ) . '</p>';
						}
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
					$show_icons,
					$what_pieces
				);
				jcp_niche_factor_card(
					$turns_title,
					$turns_icon,
					'',
					'',
					function () use ( $w, $turns_lead, $can_edit ) {
						if ( $turns_lead !== '' || $can_edit ) {
							echo '<p class="jcp-niche-card-lead"';
							jcp_niche_editable_attr( 'what_it_is.lead' );
							if ( $can_edit ) {
								echo ' data-placeholder="' . esc_attr__( 'Add intro text…', 'jcp-core' ) . '"';
							}
							echo '>' . esc_html( $turns_lead ) . '</p>';
						}
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
					$show_icons,
					$what_pieces
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
						$show_icons,
						[
							'show_title' => jcp_niche_show_field( $ch, 'show_card_titles', true ),
							'show_body'  => jcp_niche_show_field( $ch, 'show_card_body', true ),
						]
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
	$variant    = sanitize_key( (string) ( $p['variant'] ?? '' ) );
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
	$pain_points = (array) ( $p['pain_points'] ?? [] );
	$without     = is_array( $p['contrast_without'] ?? null ) ? $p['contrast_without'] : [];
	$with        = is_array( $p['contrast_with'] ?? null ) ? $p['contrast_with'] : [];
	$is_contrast = $variant === 'contrast' || ( $pain_points === [] && ( ! empty( $without['steps'] ) || ! empty( $with['steps'] ) ) );
	?>
	<section class="jcp-section rankings-section jcp-niche-problem<?php echo $is_contrast ? ' jcp-niche-problem--contrast' : ''; ?><?php echo esc_attr( $vis_class ); ?>">
		<div class="jcp-container">
			<?php jcp_niche_render_section_header( $p, 'problem' ); ?>
			<?php if ( $is_contrast ) : ?>
				<div class="jcp-problem-contrast"<?php jcp_niche_array_attr( 'problem.contrast' ); ?>>
					<?php
					foreach (
						[
							[ 'key' => 'without', 'data' => $without, 'mod' => 'without' ],
							[ 'key' => 'with', 'data' => $with, 'mod' => 'with' ],
						] as $side
					) :
						$side_data = is_array( $side['data'] ) ? $side['data'] : [];
						$label     = trim( (string) ( $side_data['label'] ?? '' ) );
						$steps     = array_values( array_filter( array_map( 'strval', (array) ( $side_data['steps'] ?? [] ) ) ) );
						$is_with   = (string) $side['mod'] === 'with';
						$loop_note = trim( (string) ( $side_data['loop_note'] ?? '' ) );
						if ( $is_with && $loop_note === '' ) {
							$loop_note = __( 'Feeds the next job', 'jcp-core' );
						}
						if ( $label === '' && $steps === [] ) {
							continue;
						}
						$step_count = count( $steps );
						?>
						<div class="jcp-problem-contrast__side jcp-problem-contrast__side--<?php echo esc_attr( (string) $side['mod'] ); ?><?php echo $is_with ? ' jcp-problem-contrast__side--cycle' : ''; ?>">
							<?php if ( $label !== '' ) : ?>
								<p class="jcp-problem-contrast__label"<?php jcp_niche_editable_attr( 'problem.contrast_' . $side['key'] . '.label' ); ?>><?php echo esc_html( $label ); ?></p>
							<?php endif; ?>
							<?php if ( $is_with ) : ?>
								<div class="jcp-problem-contrast__cycle">
									<ol class="jcp-problem-contrast__ring">
										<?php foreach ( $steps as $si => $step ) : ?>
											<li
												class="<?php echo 0 === (int) $si ? 'is-loop-start' : ''; ?><?php echo (int) $si === $step_count - 1 ? ' is-loop-end' : ''; ?>"
												data-step="<?php echo esc_attr( (string) ( $si + 1 ) ); ?>"
												<?php jcp_niche_editable_attr( 'problem.contrast_' . $side['key'] . '.steps.' . $si ); ?>
											><?php echo esc_html( $step ); ?></li>
										<?php endforeach; ?>
									</ol>
									<p class="jcp-problem-contrast__loop-note"<?php jcp_niche_editable_attr( 'problem.contrast_with.loop_note' ); ?>>
										<span class="jcp-problem-contrast__loop-arrow" aria-hidden="true">↻</span>
										<?php echo esc_html( $loop_note ); ?>
									</p>
								</div>
							<?php else : ?>
								<ol class="jcp-problem-contrast__steps">
									<?php foreach ( $steps as $si => $step ) : ?>
										<li data-step="<?php echo esc_attr( (string) ( $si + 1 ) ); ?>"<?php jcp_niche_editable_attr( 'problem.contrast_' . $side['key'] . '.steps.' . $si ); ?>><?php echo esc_html( $step ); ?></li>
									<?php endforeach; ?>
								</ol>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
			<div class="ranking-factors-grid"<?php jcp_niche_array_attr( 'problem.pain_points' ); ?>>
				<?php
				$pain_icons = [ 'image-off', 'clock', 'map-pin', 'users' ];
				foreach ( $pain_points as $pi => $pain ) :
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
						$show_icons,
						[
							'show_title' => jcp_niche_show_field( $p, 'show_card_titles', true ),
							'show_body'  => jcp_niche_show_field( $p, 'show_card_body', true ),
						]
					);
				endforeach;
				?>
			</div>
			<?php endif; ?>
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
	$variant    = sanitize_key( (string) ( $b['variant'] ?? '' ) );
	$is_job_flow = $variant === 'job_flow';
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
	$flow_mods  = [ 'capture', 'website', 'google', 'reviews', 'social' ];
	?>
	<section class="jcp-section rankings-section jcp-niche-benefits<?php echo $is_job_flow ? ' jcp-niche-benefits--job-flow' : ''; ?><?php echo esc_attr( $vis_class ); ?>" data-jcp-reveal<?php echo $section_id !== '' ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
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
			<?php if ( $is_job_flow ) : ?>
			<div class="jcp-job-flow"<?php jcp_niche_array_attr( 'benefits.items' ); ?>>
				<?php foreach ( $items as $bi => $item ) : ?>
					<?php
					if ( ! is_array( $item ) ) {
						continue;
					}
					$img   = trim( (string) ( $item['image_url'] ?? '' ) );
					$alt   = trim( (string) ( $item['image_alt'] ?? $item['title'] ?? '' ) );
					$label = trim( (string) ( $item['label'] ?? '' ) );
					$title = trim( (string) ( $item['title'] ?? '' ) );
					$body  = trim( (string) ( $item['body'] ?? '' ) );
					$mod   = sanitize_key( (string) ( $item['chrome'] ?? ( $flow_mods[ $bi ] ?? 'capture' ) ) );
					if ( $label === '' && $mod === 'website' ) {
						$label = __( 'Website', 'jcp-core' );
					}
					$step  = (int) $bi + 1;
					?>
					<article class="jcp-job-flow__step jcp-job-flow__step--<?php echo esc_attr( $mod ); ?>" data-jcp-array-item="<?php echo esc_attr( (string) $bi ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: step number */ __( 'Step %d', 'jcp-core' ), $step ) ); ?>">
						<div class="jcp-job-flow__media" aria-hidden="<?php echo $img === '' ? 'true' : 'false'; ?>">
							<span class="jcp-job-flow__num" aria-hidden="true"><?php echo esc_html( (string) $step ); ?></span>
							<?php if ( $label !== '' ) : ?>
								<span class="jcp-job-flow__badge"<?php jcp_niche_editable_attr( 'benefits.items.' . $bi . '.label' ); ?>><?php echo esc_html( $label ); ?></span>
							<?php endif; ?>
							<?php if ( $img !== '' ) : ?>
								<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="480" height="360" loading="<?php echo $bi === 0 ? 'eager' : 'lazy'; ?>" decoding="async" class="jcp-editable-media-image" data-jcp-media-url-path="benefits.items.<?php echo esc_attr( (string) $bi ); ?>.image_url" data-jcp-media-alt-path="benefits.items.<?php echo esc_attr( (string) $bi ); ?>.image_alt" data-jcp-media-types="image"<?php jcp_niche_editable_attr( 'benefits.items.' . $bi . '.image_url' ); ?> />
							<?php endif; ?>
							<?php if ( $mod === 'capture' ) : ?>
								<div class="jcp-job-flow__chrome jcp-job-flow__chrome--capture" aria-hidden="true">
									<strong>Check-in ready</strong>
									<span>Photo captured · Just now</span>
								</div>
							<?php elseif ( $mod === 'website' ) : ?>
								<div class="jcp-job-flow__chrome jcp-job-flow__chrome--browser" aria-hidden="true">
									<strong>Website jobs page</strong>
									<span>yoursite.com/jobs</span>
								</div>
							<?php elseif ( $mod === 'google' ) : ?>
								<div class="jcp-job-flow__chrome jcp-job-flow__chrome--gbp" aria-hidden="true">
									<strong>Google Business Profile</strong>
									<span>Update ready · Today</span>
								</div>
							<?php elseif ( $mod === 'reviews' ) : ?>
								<div class="jcp-job-flow__chrome jcp-job-flow__chrome--qr" aria-hidden="true">
									<span class="jcp-job-flow__qr"></span>
									<span class="jcp-job-flow__chrome-text">
										<strong>Scan to review</strong>
										<span>Ask on site</span>
									</span>
								</div>
							<?php elseif ( $mod === 'social' ) : ?>
								<div class="jcp-job-flow__chrome jcp-job-flow__chrome--social" aria-hidden="true">
									<strong>Post ready</strong>
									<span>Social · Directory</span>
								</div>
							<?php endif; ?>
						</div>
						<div class="jcp-job-flow__copy">
							<?php if ( $title !== '' ) : ?>
								<h3 class="jcp-job-flow__title"<?php jcp_niche_editable_attr( 'benefits.items.' . $bi . '.title' ); ?>><?php echo esc_html( $title ); ?></h3>
							<?php endif; ?>
							<?php if ( $body !== '' ) : ?>
								<p class="jcp-job-flow__body"<?php jcp_niche_editable_attr( 'benefits.items.' . $bi . '.body' ); ?>><?php echo esc_html( $body ); ?></p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
			<?php else : ?>
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
						$show_icons,
						[
							'show_title' => jcp_niche_show_field( $b, 'show_card_titles', true ),
							'show_body'  => jcp_niche_show_field( $b, 'show_card_body', true ),
							'show_stats' => jcp_niche_show_field( $b, 'show_card_stats', true ),
						],
						(string) ( $item['url'] ?? '' ),
						'benefits.items.' . $bi . '.url'
					);
				endforeach;
				?>
			</div>
			<?php endif; ?>
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
	$card_vis   = [
		'show_images' => jcp_niche_show_field( $w, 'show_card_images', true ),
		'show_badges' => jcp_niche_show_field( $w, 'show_card_badges', true ),
		'show_titles' => jcp_niche_show_field( $w, 'show_card_titles', true ),
		'show_body'   => jcp_niche_show_field( $w, 'show_card_body', true ),
		'show_stats'  => jcp_niche_show_field( $w, 'show_card_stats', true ),
	];
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
				<div class="guarantees-grid"<?php jcp_niche_array_attr( 'who_its_for.audiences' ); ?>>
					<?php foreach ( (array) ( $w['audiences'] ?? [] ) as $ai => $aud ) : ?>
						<?php
						if ( ! is_array( $aud ) ) {
							continue;
						}
						jcp_component_audience_guarantee_card( $aud, (int) $ai, $card_vis );
						?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
			<div class="ranking-factors-grid jcp-niche-split-grid"<?php jcp_niche_array_attr( 'who_its_for.audiences' ); ?>>
				<?php
				$aud_icons = [ 'briefcase', 'hard-hat', 'trending-up' ];
				$pieces    = [
					'show_title' => $card_vis['show_titles'],
					'show_body'  => $card_vis['show_body'],
					'show_stats' => $card_vis['show_stats'],
				];
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
						$show_icons,
						$pieces
					);
				endforeach;
				?>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<?php
			$agency = is_array( $w['agency_band'] ?? null ) ? $w['agency_band'] : [];
			$agency_headline = trim( (string) ( $agency['headline'] ?? '' ) );
			if ( $agency_headline !== '' ) :
				$agency_body = trim( (string) ( $agency['body'] ?? '' ) );
				$agency_cta  = trim( (string) ( $agency['cta_label'] ?? '' ) );
				$agency_url  = trim( (string) ( $agency['cta_url'] ?? '/demo/' ) );
				?>
				<div class="jcp-agency-band">
					<div class="jcp-agency-band__inner">
						<p class="jcp-agency-band__eyebrow"><?php esc_html_e( 'For agencies', 'jcp-core' ); ?></p>
						<h3 class="jcp-agency-band__headline"<?php jcp_niche_editable_attr( 'who_its_for.agency_band.headline' ); ?>><?php echo esc_html( $agency_headline ); ?></h3>
						<?php if ( $agency_body !== '' ) : ?>
							<p class="jcp-agency-band__body"<?php jcp_niche_editable_attr( 'who_its_for.agency_band.body' ); ?>><?php echo esc_html( $agency_body ); ?></p>
						<?php endif; ?>
						<?php if ( $agency_cta !== '' ) : ?>
							<a class="btn btn-secondary" href="<?php echo esc_url( $agency_url !== '' ? $agency_url : home_url( '/demo/' ) ); ?>"<?php jcp_niche_editable_attr( 'who_its_for.agency_band.cta_label' ); ?>><?php echo esc_html( $agency_cta ); ?></a>
						<?php endif; ?>
					</div>
				</div>
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
	$primary   = jcp_niche_resolve_cta( $f['cta_primary'] ?? [], $niche_key );
	$secondary = jcp_niche_resolve_cta( $f['cta_secondary'] ?? [], $niche_key );
	$note      = ! empty( $f['cta_note'] ) ? (string) $f['cta_note'] : __( 'No credit card required', 'jcp-core' );
	$btn       = $primary['label'] !== '' ? $primary['label'] : __( 'See It for My Business →', 'jcp-core' );
	$url       = $primary['url'] !== '' ? $primary['url'] : home_url( '/demo/' );
	$show_sub  = jcp_niche_show_field( $f, 'show_subheadline', true );
	$show_note = jcp_niche_show_field( $f, 'show_cta_note', true );
	$show_headline = jcp_niche_show_field( $f, 'show_headline', true );
	$show_cta = jcp_niche_show_field( $f, 'show_cta', true );
	$show_secondary = $secondary['label'] !== '' && jcp_niche_show_field( $f, 'show_cta_secondary', true );
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
					<?php if ( $show_secondary ) : ?>
						<p class="cta-note cta-secondary-link">
							<a href="<?php echo esc_url( $secondary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'final_cta.cta_secondary' ); jcp_niche_cta_tracking_attr( $secondary['url'], 'niche_footer_secondary', $secondary['label'] ); ?>><?php echo esc_html( $secondary['label'] ); ?></a>
						</p>
					<?php endif; ?>
					<?php
					$footnote = trim( (string) ( $f['cta_footnote'] ?? '' ) );
					if ( $footnote !== '' ) :
						?>
						<p class="cta-note cta-footnote"<?php jcp_niche_editable_attr( 'final_cta.cta_footnote' ); ?>><?php echo esc_html( $footnote ); ?></p>
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
			'variant'         => 'card',
			'section_id'      => $section_id,
			'root_class'      => 'jcp-block-demo-preview',
			'wrap_container'  => true,
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
 * Stable key for a testimonial review (id preferred, else name slug).
 *
 * @param array<string, mixed> $review Review item.
 */
function jcp_testimonials_review_key( array $review ): string {
	if ( ! empty( $review['id'] ) ) {
		return (string) $review['id'];
	}
	$name = trim( (string) ( $review['name'] ?? '' ) );
	if ( $name !== '' ) {
		return sanitize_title( $name );
	}
	return '';
}

/**
 * Resolve featured review from list by featured_key (id, name slug, or first item).
 *
 * @param list<array<string, mixed>> $reviews      Normalized reviews.
 * @param string                     $featured_key Featured key from props.
 * @return array<string, mixed>|null
 */
function jcp_testimonials_resolve_featured( array $reviews, string $featured_key ): ?array {
	if ( $reviews === [] ) {
		return null;
	}
	$featured_key = trim( $featured_key );
	if ( $featured_key !== '' ) {
		foreach ( $reviews as $review ) {
			if ( ! is_array( $review ) ) {
				continue;
			}
			$key = jcp_testimonials_review_key( $review );
			if ( $key === $featured_key ) {
				return $review;
			}
			$name_slug = sanitize_title( (string) ( $review['name'] ?? '' ) );
			if ( $name_slug !== '' && $name_slug === sanitize_title( $featured_key ) ) {
				return $review;
			}
		}
	}
	foreach ( $reviews as $review ) {
		if ( is_array( $review ) ) {
			return $review;
		}
	}
	return null;
}

/**
 * Normalize reviews for testimonials block output.
 *
 * @param array<string, mixed> $props Block props.
 * @return list<array{id:string,name:string,role:string,quote:string,rating:int,avatar_url:string,avatar_alt:string,initials:string}>
 */
function jcp_testimonials_normalize_reviews( array $props ): array {
	$raw = $props['reviews'] ?? null;
	if ( ! is_array( $raw ) || $raw === [] ) {
		$raw = function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [];
	}
	$out = [];
	foreach ( $raw as $review ) {
		if ( ! is_array( $review ) ) {
			continue;
		}
		$name  = trim( (string) ( $review['name'] ?? '' ) );
		$quote = trim( (string) ( $review['quote'] ?? '' ) );
		if ( $name === '' || $quote === '' ) {
			continue;
		}
		$id = trim( (string) ( $review['id'] ?? '' ) );
		if ( $id === '' ) {
			$id = sanitize_title( $name );
		}
		$rating = isset( $review['rating'] ) ? (int) $review['rating'] : 5;
		if ( $rating < 0 ) {
			$rating = 0;
		}
		if ( $rating > 5 ) {
			$rating = 5;
		}
		$avatar = trim( (string) ( $review['avatar_url'] ?? $review['image_url'] ?? $review['photo_url'] ?? '' ) );
		if ( $avatar !== '' && function_exists( 'set_url_scheme' ) ) {
			$avatar = set_url_scheme( $avatar, 'https' );
		}
		$alt    = trim( (string) ( $review['avatar_alt'] ?? $review['image_alt'] ?? '' ) );
		if ( $alt === '' ) {
			/* translators: %s: reviewer name. */
			$alt = sprintf( __( 'Photo of %s', 'jcp-core' ), $name );
		}
		$out[] = [
			'id'         => $id,
			'name'       => $name,
			'role'       => trim( (string) ( $review['role'] ?? '' ) ),
			'quote'      => $quote,
			'rating'     => $rating,
			'avatar_url' => $avatar,
			'avatar_alt' => $alt,
			'initials'   => jcp_testimonials_review_initials( $name ),
		];
	}
	return $out;
}

/**
 * Build 1–2 letter initials for a reviewer name.
 */
function jcp_testimonials_review_initials( string $name ): string {
	$name = trim( preg_replace( '/\s+/', ' ', $name ) ?? '' );
	if ( $name === '' ) {
		return '';
	}
	$parts = preg_split( '/\s+/', $name ) ?: [];
	$first = isset( $parts[0][0] ) ? strtoupper( $parts[0][0] ) : '';
	$last  = count( $parts ) > 1 && isset( $parts[ count( $parts ) - 1 ][0] )
		? strtoupper( $parts[ count( $parts ) - 1 ][0] )
		: '';
	return $first . $last;
}

/**
 * Render avatar mark for a review card / featured cite.
 *
 * @param array<string, mixed> $review Normalized review.
 */
function jcp_testimonials_render_avatar( array $review ): void {
	$url      = trim( (string) ( $review['avatar_url'] ?? '' ) );
	$alt      = trim( (string) ( $review['avatar_alt'] ?? '' ) );
	$initials = trim( (string) ( $review['initials'] ?? '' ) );
	if ( $initials === '' ) {
		$initials = jcp_testimonials_review_initials( (string) ( $review['name'] ?? '' ) );
	}
	?>
	<span class="jcp-testimonials-avatar" aria-hidden="true">
		<?php if ( $url !== '' ) : ?>
			<img src="<?php echo esc_url( $url ); ?>" alt="" width="44" height="44" loading="lazy" decoding="async" />
		<?php else : ?>
			<span class="jcp-testimonials-avatar__initials"><?php echo esc_html( $initials !== '' ? $initials : '?' ); ?></span>
		<?php endif; ?>
	</span>
	<?php
	unset( $alt ); // Alt reserved for parent accessible name; decorative avatar.
}

/**
 * Render star row for a testimonial rating.
 *
 * @param int  $rating     Star count (0–5).
 * @param bool $show_stars Whether to output stars.
 */
function jcp_testimonials_render_stars( int $rating, bool $show_stars ): void {
	if ( ! $show_stars || $rating <= 0 ) {
		return;
	}
	$filled = str_repeat( '★', $rating );
	$empty  = str_repeat( '☆', max( 0, 5 - $rating ) );
	/* translators: %d: star rating out of 5. */
	$label = sprintf( __( '%d out of 5 stars', 'jcp-core' ), $rating );
	?>
	<div class="jcp-testimonials-stars" aria-label="<?php echo esc_attr( $label ); ?>">
		<span aria-hidden="true"><?php echo esc_html( $filled . $empty ); ?></span>
	</div>
	<?php
}

/**
 * Normalize abbreviated stat units for display (250k → 250K, $150m → $150M).
 *
 * @param string $raw Raw value.
 */
function jcp_niche_normalize_stat_value( string $raw ): string {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return $raw;
	}
	return (string) preg_replace_callback(
		'/(\$?[\d,]+(?:\.\d+)?)([kKmMbB])(\+?)/',
		static function ( array $m ): string {
			return $m[1] . strtoupper( $m[2] ) . $m[3];
		},
		$raw
	);
}

/**
 * Parse a display stat (e.g. 10, 250k+, $150M+) into count-up attributes.
 *
 * @param string $raw Raw value string.
 * @return array{display:string,to:?float,prefix:string,suffix:string,format:string,decimals:int}
 */
function jcp_niche_parse_count_value( string $raw ): array {
	$display = trim( $raw );
	$out     = [
		'display'  => $display,
		'to'       => null,
		'prefix'   => '',
		'suffix'   => '',
		'format'   => 'plain',
		'decimals' => 0,
	];
	if ( $display === '' ) {
		return $out;
	}
	if ( ! preg_match( '/^(\$?)([\d,]+(?:\.\d+)?)([kKmMbB]?)(\+?)$/', $display, $m ) ) {
		return $out;
	}
	$prefix = (string) $m[1];
	$num    = str_replace( ',', '', (string) $m[2] );
	$scale  = strtolower( (string) $m[3] );
	$scale_display = strtoupper( (string) $m[3] );
	$plus   = (string) $m[4];
	$to     = (float) $num;
	if ( ! is_finite( $to ) || $to < 0 ) {
		return $out;
	}
	$decimals = ( strpos( $num, '.' ) !== false ) ? strlen( substr( strrchr( $num, '.' ), 1 ) ) : 0;
	$out['to']       = $to;
	$out['prefix']   = $prefix;
	$out['suffix']   = $scale_display . $plus;
	$out['decimals'] = $decimals;
	$out['format']   = ( strpos( (string) $m[2], ',' ) !== false ) ? 'comma' : 'plain';
	return $out;
}

/**
 * Render one authority stat value (static or count-up).
 *
 * @param array{value:string,label:string,detail:string} $stat Stat row.
 * @param int                                              $i    Index.
 * @param bool                                             $animate Whether to animate.
 */
function jcp_niche_render_authority_stat_value( array $stat, int $i, bool $animate ): void {
	$stat['value'] = jcp_niche_normalize_stat_value( (string) ( $stat['value'] ?? '' ) );
	$parsed = jcp_niche_parse_count_value( $stat['value'] );
	$attrs  = '';
	$class  = '';
	if ( $animate && $parsed['to'] !== null ) {
		$class = ' jcp-count-up';
		$attrs = sprintf(
			' data-count-to="%s" data-count-prefix="%s" data-count-suffix="%s" data-count-format="%s" data-count-decimals="%d" data-count-ms="1400"',
			esc_attr( (string) $parsed['to'] ),
			esc_attr( $parsed['prefix'] ),
			esc_attr( $parsed['suffix'] ),
			esc_attr( $parsed['format'] ),
			(int) $parsed['decimals']
		);
	}
	?>
	<span class="<?php echo esc_attr( trim( $class ) ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr above. ?><?php jcp_niche_editable_attr( 'authority.stats.' . $i . '.value' ); ?>><?php echo esc_html( jcp_niche_normalize_stat_value( (string) ( $stat['value'] ?? '' ) ) ); ?></span>
	<?php
}

/**
 * Authority / credibility band (e.g. Built by LeadsForward).
 *
 * @param array<string, mixed> $props     Block props.
 * @param string               $niche_key Page key for CTA URLs.
 */
function jcp_niche_render_authority( array $props, string $niche_key = '' ): void {
	$headline = trim( (string) ( $props['headline'] ?? '' ) );
	if ( $headline === '' ) {
		return;
	}

	$section_id   = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'built-by-leadsforward';
	$eyebrow      = trim( (string) ( $props['eyebrow'] ?? '' ) );
	$body         = trim( (string) ( $props['body'] ?? '' ) );
	$cta_note     = trim( (string) ( $props['cta_note'] ?? '' ) );
	$primary      = jcp_niche_resolve_cta( $props['cta_primary'] ?? [], $niche_key );
	$variant      = sanitize_key( (string) ( $props['variant'] ?? 'panel' ) );
	if ( $variant !== 'scoreboard' ) {
		$variant = 'panel';
	}
	$show_eyebrow = ! array_key_exists( 'show_eyebrow', $props ) || ! empty( $props['show_eyebrow'] );
	$show_body    = ! array_key_exists( 'show_body', $props ) || ! empty( $props['show_body'] );
	$show_stats   = ! array_key_exists( 'show_stats', $props ) || ! empty( $props['show_stats'] );
	$show_cta     = ! array_key_exists( 'show_cta', $props ) || ! empty( $props['show_cta'] );
	$animate      = $variant === 'scoreboard';
	$stats        = [];
	foreach ( (array) ( $props['stats'] ?? [] ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$value = trim( (string) ( $row['value'] ?? '' ) );
		$label = trim( (string) ( $row['label'] ?? '' ) );
		if ( $value === '' && $label === '' ) {
			continue;
		}
		$stats[] = [
			'value'  => $value,
			'label'  => $label,
			'detail' => trim( (string) ( $row['detail'] ?? '' ) ),
		];
	}
	$section_class = 'jcp-section jcp-block-authority jcp-authority--' . $variant;
	?>
	<section class="<?php echo esc_attr( $section_class ); ?>" id="<?php echo esc_attr( $section_id ); ?>" data-jcp-authority-variant="<?php echo esc_attr( $variant ); ?>">
		<div class="jcp-container">
			<?php if ( $variant === 'scoreboard' ) : ?>
				<div class="jcp-authority-scoreboard">
					<header class="jcp-authority-scoreboard__intro">
						<?php if ( $show_eyebrow && $eyebrow !== '' ) : ?>
							<p class="jcp-authority-eyebrow"<?php jcp_niche_editable_attr( 'authority.eyebrow' ); ?>><?php echo esc_html( $eyebrow ); ?></p>
						<?php endif; ?>
						<h2 class="jcp-authority-headline"<?php jcp_niche_editable_attr( 'authority.headline' ); ?>><?php echo esc_html( $headline ); ?></h2>
						<?php if ( $show_body && $body !== '' ) : ?>
							<p class="jcp-authority-body"<?php jcp_niche_editable_attr( 'authority.body' ); ?>><?php echo esc_html( $body ); ?></p>
						<?php endif; ?>
					</header>
					<?php if ( $show_stats && $stats !== [] ) : ?>
						<div class="jcp-authority-stats jcp-authority-scoreboard__stats"<?php jcp_niche_array_attr( 'authority.stats' ); ?>>
							<?php foreach ( $stats as $i => $stat ) : ?>
								<div class="jcp-authority-stat">
									<div class="jcp-authority-stat-value">
										<?php jcp_niche_render_authority_stat_value( $stat, $i, $animate ); ?>
										<?php if ( $stat['label'] !== '' ) : ?>
											<span class="jcp-authority-stat-label"<?php jcp_niche_editable_attr( 'authority.stats.' . $i . '.label' ); ?>><?php echo esc_html( $stat['label'] ); ?></span>
										<?php endif; ?>
									</div>
									<?php if ( $stat['detail'] !== '' ) : ?>
										<p class="jcp-authority-stat-detail"<?php jcp_niche_editable_attr( 'authority.stats.' . $i . '.detail' ); ?>><?php echo esc_html( $stat['detail'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if ( $show_cta && $primary['label'] !== '' ) : ?>
						<div class="jcp-authority-cta jcp-authority-scoreboard__cta">
							<a
								href="<?php echo esc_url( $primary['url'] ); ?>"
								class="btn btn-primary"
								<?php jcp_niche_editable_link_paths( 'authority.cta_primary.label', 'authority.cta_primary.url' ); ?>
								<?php jcp_niche_cta_tracking_attr( $primary['url'], 'authority_cta', $primary['label'] ); ?>
							><?php echo esc_html( $primary['label'] ); ?></a>
							<?php if ( $cta_note !== '' ) : ?>
								<p class="jcp-authority-cta-note"<?php jcp_niche_editable_attr( 'authority.cta_note' ); ?>><?php echo esc_html( $cta_note ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="jcp-authority-panel">
					<div class="jcp-authority-copy">
						<?php if ( $show_eyebrow && $eyebrow !== '' ) : ?>
							<p class="jcp-authority-eyebrow"<?php jcp_niche_editable_attr( 'authority.eyebrow' ); ?>><?php echo esc_html( $eyebrow ); ?></p>
						<?php endif; ?>
						<h2 class="jcp-authority-headline"<?php jcp_niche_editable_attr( 'authority.headline' ); ?>><?php echo esc_html( $headline ); ?></h2>
						<?php if ( $show_body && $body !== '' ) : ?>
							<p class="jcp-authority-body"<?php jcp_niche_editable_attr( 'authority.body' ); ?>><?php echo esc_html( $body ); ?></p>
						<?php endif; ?>
						<?php if ( $show_cta && $primary['label'] !== '' ) : ?>
							<div class="jcp-authority-cta">
								<a
									href="<?php echo esc_url( $primary['url'] ); ?>"
									class="btn btn-primary"
									<?php jcp_niche_editable_link_paths( 'authority.cta_primary.label', 'authority.cta_primary.url' ); ?>
									<?php jcp_niche_cta_tracking_attr( $primary['url'], 'authority_cta', $primary['label'] ); ?>
								><?php echo esc_html( $primary['label'] ); ?></a>
								<?php if ( $cta_note !== '' ) : ?>
									<p class="jcp-authority-cta-note"<?php jcp_niche_editable_attr( 'authority.cta_note' ); ?>><?php echo esc_html( $cta_note ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php if ( $show_stats && $stats !== [] ) : ?>
						<div class="jcp-authority-stats"<?php jcp_niche_array_attr( 'authority.stats' ); ?>>
							<?php foreach ( $stats as $i => $stat ) : ?>
								<div class="jcp-authority-stat">
									<div class="jcp-authority-stat-value">
										<?php jcp_niche_render_authority_stat_value( $stat, $i, false ); ?>
										<?php if ( $stat['label'] !== '' ) : ?>
											<span class="jcp-authority-stat-label"<?php jcp_niche_editable_attr( 'authority.stats.' . $i . '.label' ); ?>><?php echo esc_html( $stat['label'] ); ?></span>
										<?php endif; ?>
									</div>
									<?php if ( $stat['detail'] !== '' ) : ?>
										<p class="jcp-authority-stat-detail"<?php jcp_niche_editable_attr( 'authority.stats.' . $i . '.detail' ); ?>><?php echo esc_html( $stat['detail'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Local Falcon / SoLV before-after proof (anonymous case).
 *
 * @param array<string, mixed> $props     Block props.
 * @param string               $niche_key Page key for CTA URLs.
 */
function jcp_niche_render_local_falcon_proof( array $props, string $niche_key = '' ): void {
	$headline = trim( (string) ( $props['headline'] ?? '' ) );
	if ( $headline === '' ) {
		return;
	}

	$section_id   = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'maps-proof';
	$eyebrow      = trim( (string) ( $props['eyebrow'] ?? '' ) );
	$subheadline  = trim( (string) ( $props['subheadline'] ?? '' ) );
	$disclaimer   = trim( (string) ( $props['disclaimer'] ?? '' ) );
	$show_eyebrow = ! array_key_exists( 'show_eyebrow', $props ) || ! empty( $props['show_eyebrow'] );
	$show_cta     = ! array_key_exists( 'show_cta', $props ) || ! empty( $props['show_cta'] );
	$primary      = jcp_niche_resolve_cta( $props['cta_primary'] ?? [], $niche_key );
	$secondary    = jcp_niche_resolve_cta( $props['cta_secondary'] ?? [], $niche_key );
	$markets      = [];
	foreach ( (array) ( $props['markets'] ?? [] ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$img = trim( (string) ( $row['image_url'] ?? '' ) );
		if ( $img === '' && empty( $row['market'] ) ) {
			continue;
		}
		$markets[] = [
			'market'       => trim( (string) ( $row['market'] ?? '' ) ),
			'keyword'      => trim( (string) ( $row['keyword'] ?? '' ) ),
			'before_solv'  => trim( (string) ( $row['before_solv'] ?? '' ) ),
			'after_solv'   => trim( (string) ( $row['after_solv'] ?? '' ) ),
			'before_label' => trim( (string) ( $row['before_label'] ?? __( 'Before', 'jcp-core' ) ) ),
			'after_label'  => trim( (string) ( $row['after_label'] ?? __( 'After', 'jcp-core' ) ) ),
			'image_url'    => $img,
			'image_alt'    => trim( (string) ( $row['image_alt'] ?? '' ) ),
		];
	}
	?>
	<section class="jcp-section jcp-block-local-falcon" id="<?php echo esc_attr( $section_id ); ?>" data-jcp-reveal>
		<div class="jcp-container">
			<header class="jcp-local-falcon__header">
				<?php if ( $show_eyebrow && $eyebrow !== '' ) : ?>
					<p class="jcp-local-falcon__eyebrow"<?php jcp_niche_editable_attr( 'local_falcon_proof.eyebrow' ); ?>><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<h2 class="jcp-local-falcon__headline"<?php jcp_niche_editable_attr( 'local_falcon_proof.headline' ); ?>><?php echo esc_html( $headline ); ?></h2>
				<?php if ( $subheadline !== '' ) : ?>
					<p class="jcp-local-falcon__sub"<?php jcp_niche_editable_attr( 'local_falcon_proof.subheadline' ); ?>><?php echo esc_html( $subheadline ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( $markets !== [] ) : ?>
				<div class="jcp-local-falcon__grid">
					<?php foreach ( $markets as $i => $market ) : ?>
						<article class="jcp-local-falcon__card" data-jcp-lf-card>
							<div class="jcp-local-falcon__meta">
								<?php if ( $market['market'] !== '' ) : ?>
									<h3 class="jcp-local-falcon__market"<?php jcp_niche_editable_attr( 'local_falcon_proof.markets.' . $i . '.market' ); ?>><?php echo esc_html( $market['market'] ); ?></h3>
								<?php endif; ?>
								<?php if ( $market['keyword'] !== '' ) : ?>
									<p class="jcp-local-falcon__keyword"<?php jcp_niche_editable_attr( 'local_falcon_proof.markets.' . $i . '.keyword' ); ?>><?php echo esc_html( $market['keyword'] ); ?></p>
								<?php endif; ?>
								<div class="jcp-local-falcon__solv">
									<span class="jcp-local-falcon__solv-before">
										<em><?php echo esc_html( $market['before_label'] ); ?></em>
										<strong<?php jcp_niche_editable_attr( 'local_falcon_proof.markets.' . $i . '.before_solv' ); ?>><?php echo esc_html( $market['before_solv'] ); ?></strong>
										<span><?php esc_html_e( 'SoLV', 'jcp-core' ); ?></span>
									</span>
									<span class="jcp-local-falcon__solv-arrow" aria-hidden="true">→</span>
									<span class="jcp-local-falcon__solv-after">
										<em><?php echo esc_html( $market['after_label'] ); ?></em>
										<strong<?php jcp_niche_editable_attr( 'local_falcon_proof.markets.' . $i . '.after_solv' ); ?>><?php echo esc_html( $market['after_solv'] ); ?></strong>
										<span><?php esc_html_e( 'SoLV', 'jcp-core' ); ?></span>
									</span>
								</div>
							</div>
							<?php if ( $market['image_url'] !== '' ) : ?>
								<figure class="jcp-local-falcon__figure">
									<div class="jcp-local-falcon__compare" data-jcp-lf-compare>
										<img src="<?php echo esc_url( $market['image_url'] ); ?>" alt="<?php echo esc_attr( $market['image_alt'] !== '' ? $market['image_alt'] : $market['market'] ); ?>" width="960" height="540" loading="lazy" decoding="async" />
										<label class="jcp-local-falcon__slider-label">
											<span class="screen-reader-text"><?php esc_html_e( 'Reveal after grid', 'jcp-core' ); ?></span>
											<input type="range" min="0" max="100" value="50" data-jcp-lf-range />
										</label>
										<div class="jcp-local-falcon__reveal" data-jcp-lf-reveal style="width:50%"></div>
									</div>
								</figure>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $disclaimer !== '' ) : ?>
				<p class="jcp-local-falcon__disclaimer"<?php jcp_niche_editable_attr( 'local_falcon_proof.disclaimer' ); ?>><?php echo esc_html( $disclaimer ); ?></p>
			<?php endif; ?>
			<?php if ( $show_cta && ( $primary['label'] !== '' || $secondary['label'] !== '' ) ) : ?>
				<div class="jcp-local-falcon__cta jcp-actions">
					<?php if ( $primary['label'] !== '' ) : ?>
						<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>"<?php jcp_niche_editable_link_attr( 'local_falcon_proof.cta_primary' ); ?>><?php echo esc_html( $primary['label'] ); ?></a>
					<?php endif; ?>
					<?php if ( $secondary['label'] !== '' ) : ?>
						<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] !== '' ? $secondary['url'] : ( function_exists( 'jcp_global_onboarding_url' ) ? jcp_global_onboarding_url( 'home_preview_lf' ) : home_url( '/' ) ) ); ?>"<?php jcp_niche_editable_link_attr( 'local_falcon_proof.cta_secondary' ); ?>><?php echo esc_html( $secondary['label'] ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Deterministic 7×7 geo-grid rank patterns (1–20+) for CSS heatmaps.
 *
 * @param string $pattern Pattern key.
 * @return array<int, int> Exactly 49 ranks.
 */
function jcp_lf_case_grid_pattern( string $pattern ): array {
	$patterns = [
		// All out of pack / not ranking in tracked keywords.
		'before_blank' => array_fill( 0, 49, 20 ),
		// ~89.8% SoLV (~44/49 in top 3) — a few yellow edge cells.
		'after_fr_wv'  => [
			2, 1, 1, 2, 1, 3, 4,
			1, 1, 2, 1, 2, 1, 3,
			1, 2, 1, 1, 2, 1, 2,
			2, 1, 1, 1, 1, 2, 5,
			1, 1, 2, 1, 1, 3, 2,
			3, 1, 1, 2, 1, 1, 6,
			4, 3, 2, 1, 2, 3, 7,
		],
		// 100% SoLV — near-solid Map Pack green.
		'after_bw_wv'  => [
			1, 1, 2, 1, 1, 2, 1,
			1, 2, 1, 1, 2, 1, 1,
			2, 1, 1, 1, 1, 2, 1,
			1, 1, 2, 1, 1, 1, 2,
			1, 2, 1, 1, 2, 1, 1,
			2, 1, 1, 2, 1, 1, 1,
			1, 1, 2, 1, 1, 2, 1,
		],
		// ~83.7% SoLV (41/49 in top 3) — more yellow fringe.
		'after_fr_mi'  => [
			2, 1, 1, 3, 2, 4, 8,
			1, 2, 1, 1, 2, 3, 5,
			1, 1, 2, 1, 1, 2, 3,
			3, 1, 1, 1, 2, 1, 6,
			2, 1, 2, 1, 1, 3, 2,
			5, 2, 1, 2, 1, 2, 7,
			4, 3, 2, 1, 2, 3, 9,
		],
		// ~95.9% SoLV (47/49) — nearly solid green, two soft edges.
		'after_bw_mi'  => [
			1, 1, 2, 1, 1, 2, 3,
			1, 2, 1, 1, 2, 1, 1,
			2, 1, 1, 1, 1, 2, 1,
			1, 1, 2, 1, 1, 1, 2,
			1, 2, 1, 1, 2, 1, 1,
			2, 1, 1, 2, 1, 1, 4,
			1, 1, 2, 1, 1, 5, 1,
		],
	];

	$grid = $patterns[ $pattern ] ?? $patterns['before_blank'];
	if ( count( $grid ) !== 49 ) {
		$grid = array_pad( array_slice( $grid, 0, 49 ), 49, 20 );
	}
	return array_map( 'intval', $grid );
}

/**
 * Map a Local Falcon–style rank to a CSS color token class.
 *
 * @param int $rank Rank 1–20+.
 */
function jcp_lf_case_rank_class( int $rank ): string {
	if ( $rank <= 3 ) {
		return 'is-rank-top';
	}
	if ( $rank <= 10 ) {
		return 'is-rank-mid';
	}
	if ( $rank <= 19 ) {
		return 'is-rank-low';
	}
	return 'is-rank-out';
}

/**
 * Render a 7×7 CSS geo-grid heatmap (optional real map underlay).
 *
 * @param array<int, int> $ranks   49 ranks.
 * @param string          $label   Accessible label.
 * @param string          $map_url Optional map background URL.
 */
function jcp_lf_case_render_grid( array $ranks, string $label, string $map_url = '' ): void {
	$mapped = $map_url !== '';
	?>
	<div class="jcp-lf-grid<?php echo $mapped ? ' jcp-lf-grid--mapped' : ''; ?>" role="img" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php if ( $mapped ) : ?>
			<img class="jcp-lf-grid__map" src="<?php echo esc_url( $map_url ); ?>" alt="" width="640" height="640" loading="lazy" decoding="async" />
		<?php endif; ?>
		<div class="jcp-lf-grid__cells">
			<?php foreach ( $ranks as $rank ) : ?>
				<span class="jcp-lf-grid__cell <?php echo esc_attr( jcp_lf_case_rank_class( (int) $rank ) ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %d: map pack rank */ __( 'Rank %d', 'jcp-core' ), (int) $rank ) ); ?>"></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Anonymous Local Falcon case study — CSS heatmaps on real map underlays.
 *
 * @param array<string, mixed> $props     Block props.
 * @param string               $niche_key Page key.
 */
function jcp_niche_render_local_rank_case_study( array $props, string $niche_key = '' ): void {
	$headline = trim( (string) ( $props['headline'] ?? '' ) );
	if ( $headline === '' ) {
		return;
	}

	$section_id   = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'case-study';
	$eyebrow      = trim( (string) ( $props['eyebrow'] ?? '' ) );
	$body         = trim( (string) ( $props['body'] ?? '' ) );
	$footnote     = trim( (string) ( $props['footnote'] ?? '' ) );
	$show_eyebrow = ! array_key_exists( 'show_eyebrow', $props ) || ! empty( $props['show_eyebrow'] );
	$show_body    = ! array_key_exists( 'show_body', $props ) || ! empty( $props['show_body'] );
	$show_stats   = ! array_key_exists( 'show_stats', $props ) || ! empty( $props['show_stats'] );
	$show_cta     = ! empty( $props['show_cta'] );
	$secondary    = function_exists( 'jcp_niche_resolve_cta' )
		? jcp_niche_resolve_cta( $props['cta_secondary'] ?? [], $niche_key )
		: [
			'label' => trim( (string) ( ( $props['cta_secondary']['label'] ?? '' ) ) ),
			'url'   => trim( (string) ( ( $props['cta_secondary']['url'] ?? '/demo/' ) ) ),
		];

	$stats = [];
	foreach ( (array) ( $props['stats'] ?? [] ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$value = trim( (string) ( $row['value'] ?? '' ) );
		$label = trim( (string) ( $row['label'] ?? '' ) );
		if ( $value === '' && $label === '' ) {
			continue;
		}
		$stats[] = [
			'value' => $value,
			'label' => $label,
		];
	}

	$locations = [];
	foreach ( (array) ( $props['locations'] ?? [] ) as $loc ) {
		if ( ! is_array( $loc ) ) {
			continue;
		}
		$name    = trim( (string) ( $loc['name'] ?? '' ) );
		$address = trim( (string) ( $loc['address'] ?? '' ) );
		if ( $name === '' && $address === '' ) {
			continue;
		}
		$scans = [];
		foreach ( (array) ( $loc['scans'] ?? [] ) as $scan ) {
			if ( ! is_array( $scan ) ) {
				continue;
			}
			$keyword = trim( (string) ( $scan['keyword'] ?? '' ) );
			if ( $keyword === '' ) {
				continue;
			}
			$before = is_array( $scan['before'] ?? null ) ? $scan['before'] : [];
			$after  = is_array( $scan['after'] ?? null ) ? $scan['after'] : [];
			$scans[] = [
				'keyword'     => $keyword,
				'before'      => [
					'date'    => trim( (string) ( $before['date'] ?? '' ) ),
					'arp'     => trim( (string) ( $before['arp'] ?? '' ) ),
					'atrp'    => trim( (string) ( $before['atrp'] ?? '' ) ),
					'solv'    => trim( (string) ( $before['solv'] ?? '' ) ),
					'pattern' => trim( (string) ( $before['pattern'] ?? 'before_blank' ) ),
				],
				'after'       => [
					'date'    => trim( (string) ( $after['date'] ?? '' ) ),
					'arp'     => trim( (string) ( $after['arp'] ?? '' ) ),
					'atrp'    => trim( (string) ( $after['atrp'] ?? '' ) ),
					'solv'    => trim( (string) ( $after['solv'] ?? '' ) ),
					'pattern' => trim( (string) ( $after['pattern'] ?? 'before_blank' ) ),
				],
				'grid_label'  => trim( (string) ( $scan['grid_label'] ?? __( 'Geo-grid scan (7×7 · 6 mi)', 'jcp-core' ) ) ),
			];
		}
		$locations[] = [
			'name'    => $name,
			'address' => $address,
			'meta'    => trim( (string) ( $loc['meta'] ?? '' ) ),
			'map_bg'  => trim( (string) ( $loc['map_bg'] ?? '' ) ),
			'scans'   => $scans,
		];
	}
	?>
	<section class="jcp-section jcp-block-lf-case" id="<?php echo esc_attr( $section_id ); ?>" data-jcp-reveal>
		<div class="jcp-container">
			<header class="jcp-lf-case__header">
				<?php if ( $show_eyebrow && $eyebrow !== '' ) : ?>
					<p class="jcp-lf-case__eyebrow demo-badge"<?php jcp_niche_editable_attr( 'local_rank_case_study.eyebrow' ); ?>><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<h2 class="jcp-lf-case__headline"<?php jcp_niche_editable_attr( 'local_rank_case_study.headline' ); ?>><?php echo esc_html( $headline ); ?></h2>
				<?php if ( $show_body && $body !== '' ) : ?>
					<p class="jcp-lf-case__body"<?php jcp_niche_editable_attr( 'local_rank_case_study.body' ); ?>><?php echo esc_html( $body ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( $show_stats && $stats !== [] ) : ?>
				<ul class="jcp-lf-case__stats" role="list">
					<?php foreach ( $stats as $i => $stat ) : ?>
						<li class="jcp-lf-case__stat">
							<strong class="jcp-lf-case__stat-value"<?php jcp_niche_editable_attr( 'local_rank_case_study.stats.' . $i . '.value' ); ?>><?php echo esc_html( $stat['value'] ); ?></strong>
							<span class="jcp-lf-case__stat-label"<?php jcp_niche_editable_attr( 'local_rank_case_study.stats.' . $i . '.label' ); ?>><?php echo esc_html( $stat['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $locations !== [] ) : ?>
				<div class="jcp-lf-case__locations">
					<?php foreach ( $locations as $li => $location ) : ?>
						<article class="jcp-lf-case__location">
							<header class="jcp-lf-case__location-head">
								<?php if ( $location['name'] !== '' ) : ?>
									<h3 class="jcp-lf-case__location-name"<?php jcp_niche_editable_attr( 'local_rank_case_study.locations.' . $li . '.name' ); ?>><?php echo esc_html( $location['name'] ); ?></h3>
								<?php endif; ?>
								<?php if ( $location['address'] !== '' ) : ?>
									<p class="jcp-lf-case__location-address"<?php jcp_niche_editable_attr( 'local_rank_case_study.locations.' . $li . '.address' ); ?>><?php echo esc_html( $location['address'] ); ?></p>
								<?php endif; ?>
								<?php if ( $location['meta'] !== '' ) : ?>
									<p class="jcp-lf-case__location-meta"<?php jcp_niche_editable_attr( 'local_rank_case_study.locations.' . $li . '.meta' ); ?>><?php echo esc_html( $location['meta'] ); ?></p>
								<?php endif; ?>
							</header>

							<?php foreach ( $location['scans'] as $si => $scan ) : ?>
								<div class="jcp-lf-case__scan">
									<div class="jcp-lf-case__scan-chrome">
										<span class="jcp-lf-case__keyword"<?php jcp_niche_editable_attr( 'local_rank_case_study.locations.' . $li . '.scans.' . $si . '.keyword' ); ?>><?php echo esc_html( $scan['keyword'] ); ?></span>
										<span class="jcp-lf-case__scan-label"><?php echo esc_html( $scan['grid_label'] ); ?></span>
									</div>
									<div class="jcp-lf-case__compare">
										<?php
										foreach ( [ 'before', 'after' ] as $phase ) :
											$side = $scan[ $phase ];
											$ranks = jcp_lf_case_grid_pattern( (string) $side['pattern'] );
											$aria  = sprintf(
												/* translators: 1: before/after, 2: keyword, 3: ARP, 4: SoLV */
												__( '%1$s scan for %2$s — ARP %3$s, SoLV %4$s', 'jcp-core' ),
												$phase === 'before' ? __( 'Before', 'jcp-core' ) : __( 'After', 'jcp-core' ),
												$scan['keyword'],
												$side['arp'] !== '' ? $side['arp'] : '—',
												$side['solv'] !== '' ? $side['solv'] : '—'
											);
											?>
											<div class="jcp-lf-case__panel jcp-lf-case__panel--<?php echo esc_attr( $phase ); ?>">
												<div class="jcp-lf-case__panel-head">
													<span class="jcp-lf-case__phase"><?php echo esc_html( $phase === 'before' ? __( 'Before', 'jcp-core' ) : __( 'After', 'jcp-core' ) ); ?></span>
													<?php if ( $side['date'] !== '' ) : ?>
														<span class="jcp-lf-case__date"><?php echo esc_html( $side['date'] ); ?></span>
													<?php endif; ?>
												</div>
												<?php jcp_lf_case_render_grid( $ranks, $aria, (string) ( $location['map_bg'] ?? '' ) ); ?>
												<div class="jcp-lf-case__metrics">
													<?php if ( $side['arp'] !== '' ) : ?>
														<span class="jcp-lf-case__pill"><em><?php esc_html_e( 'ARP', 'jcp-core' ); ?></em> <strong><?php echo esc_html( $side['arp'] ); ?></strong></span>
													<?php endif; ?>
													<?php if ( $side['atrp'] !== '' ) : ?>
														<span class="jcp-lf-case__pill"><em><?php esc_html_e( 'ATRP', 'jcp-core' ); ?></em> <strong><?php echo esc_html( $side['atrp'] ); ?></strong></span>
													<?php endif; ?>
													<?php if ( $side['solv'] !== '' ) : ?>
														<span class="jcp-lf-case__pill jcp-lf-case__pill--solv"><em><?php esc_html_e( 'SoLV', 'jcp-core' ); ?></em> <strong><?php echo esc_html( $side['solv'] ); ?></strong></span>
													<?php endif; ?>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $footnote !== '' ) : ?>
				<p class="jcp-lf-case__footnote"<?php jcp_niche_editable_attr( 'local_rank_case_study.footnote' ); ?>><?php echo esc_html( $footnote ); ?></p>
			<?php endif; ?>

			<?php if ( $show_cta && $secondary['label'] !== '' ) : ?>
				<p class="jcp-lf-case__cta">
					<a class="jcp-lf-case__cta-link" href="<?php echo esc_url( $secondary['url'] !== '' ? $secondary['url'] : home_url( '/demo/' ) ); ?>"<?php jcp_niche_editable_link_attr( 'local_rank_case_study.cta_secondary' ); ?>><?php echo esc_html( $secondary['label'] ); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Testimonials block — featured quote + secondary strip, or slider-only.
 *
 * @param array<string, mixed> $props Block props.
 */
function jcp_niche_render_testimonials( array $props ): void {
	if ( empty( $props['headline'] ) ) {
		return;
	}

	$reviews = jcp_testimonials_normalize_reviews( $props );
	if ( $reviews === [] ) {
		return;
	}

	// Default off so new pages match the landing-page grid (no featured highlight).
	$show_featured = ! empty( $props['show_featured'] );
	$featured_key  = trim( (string) ( $props['featured_key'] ?? '' ) );
	$featured      = $show_featured ? jcp_testimonials_resolve_featured( $reviews, $featured_key ) : null;
	if ( $show_featured && $featured === null ) {
		return;
	}
	$featured_id = $featured ? jcp_testimonials_review_key( $featured ) : '';
	$slider_list = [];
	if ( $show_featured && $featured ) {
		foreach ( $reviews as $review ) {
			if ( jcp_testimonials_review_key( $review ) === $featured_id ) {
				continue;
			}
			$slider_list[] = $review;
		}
	} else {
		$slider_list = $reviews;
	}

	$section_id  = ! empty( $props['section_id'] ) ? (string) $props['section_id'] : 'testimonials';
	$autoplay    = ! empty( $props['autoplay'] );
	$autoplay_ms = isset( $props['autoplay_ms'] ) ? max( 1000, (int) $props['autoplay_ms'] ) : 6000;
	$per_view    = isset( $props['per_view'] ) ? max( 1, (int) $props['per_view'] ) : ( $show_featured ? 1 : 4 );
	$layout      = sanitize_key( (string) ( $props['layout'] ?? ( $show_featured ? 'slider' : 'grid' ) ) );
	if ( ! in_array( $layout, [ 'slider', 'grid' ], true ) ) {
		$layout = $show_featured ? 'slider' : 'grid';
	}
	$show_stars  = ! array_key_exists( 'show_stars', $props ) || ! empty( $props['show_stars'] );
	$show_roles  = ! array_key_exists( 'show_roles', $props ) || ! empty( $props['show_roles'] );
	$eyebrow_vis = jcp_niche_field_visibility( $props, 'show_eyebrow', true );
	$eyebrow     = trim( (string) ( $props['eyebrow'] ?? '' ) );
	$faces       = [];
	foreach ( (array) ( $props['faces'] ?? [] ) as $face ) {
		if ( ! is_array( $face ) ) {
			continue;
		}
		$url = trim( (string) ( $face['image_url'] ?? '' ) );
		if ( $url === '' ) {
			continue;
		}
		$faces[] = [
			'url' => $url,
			'alt' => (string) ( $face['image_alt'] ?? $face['alt'] ?? '' ),
		];
	}
	$faces_label = trim( (string) ( $props['faces_label'] ?? '' ) );
	$store_json  = wp_json_encode( $reviews );
	$mode_class  = $show_featured ? '' : ' jcp-testimonials--slider-only';
	if ( $layout === 'grid' ) {
		$mode_class .= ' jcp-testimonials--grid';
	}
	?>
	<section
		class="jcp-section rankings-section jcp-block-testimonials<?php echo esc_attr( $mode_class ); ?>"
		id="<?php echo esc_attr( $section_id ); ?>"
		data-jcp-testimonials
		data-layout="<?php echo esc_attr( $layout ); ?>"
		data-autoplay="<?php echo esc_attr( $autoplay && $layout !== 'grid' ? '1' : '0' ); ?>"
		data-autoplay-ms="<?php echo esc_attr( (string) $autoplay_ms ); ?>"
		data-per-view="<?php echo esc_attr( (string) $per_view ); ?>"
		<?php if ( $show_featured ) : ?>
		data-featured-key="<?php echo esc_attr( $featured_id ); ?>"
		<?php else : ?>
		data-slider-only="1"
		<?php endif; ?>
	>
		<div class="jcp-container">
			<?php if ( $eyebrow_vis['render'] && $eyebrow !== '' ) : ?>
				<p class="jcp-testimonials-eyebrow demo-badge"<?php jcp_niche_editable_attr( 'testimonials.eyebrow' ); ?>><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php jcp_niche_render_section_header( $props, 'testimonials' ); ?>
			<?php if ( $faces !== [] ) : ?>
				<div class="jcp-campaign-faces" aria-hidden="<?php echo $faces_label === '' ? 'true' : 'false'; ?>">
					<div class="jcp-campaign-faces__row">
						<?php foreach ( $faces as $face ) : ?>
							<img src="<?php echo esc_url( $face['url'] ); ?>" alt="<?php echo esc_attr( $face['alt'] ); ?>" width="52" height="52" loading="lazy" decoding="async" />
						<?php endforeach; ?>
						<div class="jcp-campaign-faces__stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating out of 5. */ __( '%d out of 5 stars', 'jcp-core' ), 5 ) ); ?>">
							<span aria-hidden="true"><?php echo esc_html( str_repeat( '★', 5 ) ); ?></span>
						</div>
						<?php if ( $faces_label !== '' ) : ?>
							<span class="jcp-campaign-faces__label"><?php echo esc_html( $faces_label ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="jcp-testimonials">
				<?php if ( $show_featured && $featured ) : ?>
				<figure class="jcp-testimonials-featured" data-jcp-testimonials-featured>
					<?php jcp_testimonials_render_stars( (int) ( $featured['rating'] ?? 5 ), $show_stars ); ?>
					<blockquote class="jcp-testimonials-quote">
						<p><?php echo esc_html( (string) $featured['quote'] ); ?></p>
					</blockquote>
					<figcaption class="jcp-testimonials-cite">
						<?php jcp_testimonials_render_avatar( $featured ); ?>
						<span class="jcp-testimonials-cite-text">
							<cite class="jcp-testimonials-name"><?php echo esc_html( (string) $featured['name'] ); ?></cite>
							<?php if ( $show_roles && ! empty( $featured['role'] ) ) : ?>
								<span class="jcp-testimonials-role"><?php echo esc_html( (string) $featured['role'] ); ?></span>
							<?php endif; ?>
						</span>
					</figcaption>
				</figure>
				<?php endif; ?>
				<?php if ( $slider_list !== [] ) : ?>
				<div class="jcp-testimonials-slider" data-jcp-testimonials-slider>
					<button type="button" class="jcp-testimonials-nav jcp-testimonials-nav--prev" data-jcp-testimonials-prev aria-label="<?php esc_attr_e( 'Previous review', 'jcp-core' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<div class="jcp-testimonials-track" data-jcp-testimonials-track role="list">
						<?php foreach ( $slider_list as $review ) : ?>
							<?php
							$card_key = jcp_testimonials_review_key( $review );
							/* translators: %s: reviewer name. */
							$card_label = sprintf( __( 'Review from %s', 'jcp-core' ), (string) $review['name'] );
							$tag        = $show_featured ? 'button' : 'article';
							?>
							<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php if ( $show_featured ) : ?>
								type="button"
								class="jcp-testimonials-card"
								data-review-key="<?php echo esc_attr( $card_key ); ?>"
								aria-label="<?php echo esc_attr( $card_label ); ?>"
								role="listitem"
								<?php else : ?>
								class="jcp-testimonials-card"
								data-review-key="<?php echo esc_attr( $card_key ); ?>"
								aria-label="<?php echo esc_attr( $card_label ); ?>"
								role="listitem"
								<?php endif; ?>
							>
								<?php jcp_testimonials_render_stars( (int) ( $review['rating'] ?? 5 ), $show_stars ); ?>
								<p class="jcp-testimonials-card-quote"><?php echo esc_html( (string) $review['quote'] ); ?></p>
								<div class="jcp-testimonials-card-person">
									<?php jcp_testimonials_render_avatar( $review ); ?>
									<span class="jcp-testimonials-card-person-text">
										<span class="jcp-testimonials-card-name"><?php echo esc_html( (string) $review['name'] ); ?></span>
										<?php if ( $show_roles && ! empty( $review['role'] ) ) : ?>
											<span class="jcp-testimonials-card-role"><?php echo esc_html( (string) $review['role'] ); ?></span>
										<?php endif; ?>
									</span>
								</div>
							</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php endforeach; ?>
					</div>
					<button type="button" class="jcp-testimonials-nav jcp-testimonials-nav--next" data-jcp-testimonials-next aria-label="<?php esc_attr_e( 'Next review', 'jcp-core' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
					</button>
					<div class="jcp-testimonials-dots" data-jcp-testimonials-dots role="tablist" aria-label="<?php esc_attr_e( 'Review slides', 'jcp-core' ); ?>"></div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( is_string( $store_json ) && $store_json !== '' ) : ?>
			<?php
			/*
			 * Do not esc_html() here — it turns " into &quot; and JSON.parse(textContent) fails,
			 * which leaves the slider uninitialized (nav clicks do nothing).
			 * JSON_HEX_TAG / JSON_HEX_AMP keep </script> and & safe inside the script tag.
			 */
			$store_safe = wp_json_encode( $reviews, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			?>
			<?php if ( is_string( $store_safe ) && $store_safe !== '' ) : ?>
			<script type="application/json" data-jcp-testimonials-store><?php echo $store_safe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* encoded. ?></script>
			<?php endif; ?>
		<?php endif; ?>
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
