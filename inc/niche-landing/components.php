<?php
/**
 * Global marketing components (homepage + block library).
 *
 * Components = reusable markup atoms used inside Blocks (demo phone, directory card, etc.).
 * Blocks = full page sections registered in inc/page-blocks/registry.php.
 * UI Library (/ui-library/) = visual catalog of components; Block Library (WP Admin) = section catalog.
 *
 * @package JCP_Core
 */

/**
 * Small chevron-right SVG used in CTAs.
 */
function jcp_component_chevron_svg( int $size = 16 ): void {
	?>
	<svg width="<?php echo (int) $size; ?>" height="<?php echo (int) $size; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
		<path d="M5 12h14M13 5l7 7-7 7"/>
	</svg>
	<?php
}

/**
 * Shared meta-stat row (homepage hero, core mechanic, UI library).
 *
 * Layout:
 *   [icon]  1 photo
 *           Becomes proof on every channel
 *
 * @param array<int, array<string, mixed>> $items               Stats.
 * @param string                           $path                JSON path prefix ('' = static).
 * @param string                           $extra_class         Extra classes on the row wrapper.
 * @param bool                             $collection_controls Show add/remove controls for editors.
 */
function jcp_component_home_meta_stats( array $items, string $path = 'hero.meta_stats', string $extra_class = '', bool $collection_controls = false ): void {
	if ( empty( $items ) && ! $collection_controls ) {
		return;
	}
	$row_class = trim( 'directory-meta jcp-meta-stats ' . $extra_class );
	?>
	<div class="<?php echo esc_attr( $row_class ); ?>"<?php if ( $path !== '' ) { jcp_niche_array_attr( $path ); } ?>>
		<?php foreach ( $items as $i => $item ) : ?>
			<?php
			if ( ! is_array( $item ) ) {
				continue;
			}
			$icon       = ! empty( $item['icon'] ) ? (string) $item['icon'] : 'check';
			$label      = (string) ( $item['label'] ?? '' );
			$detail     = (string) ( $item['detail'] ?? '' );
			$css_class  = (string) ( $item['css_class'] ?? '' );
			$value      = trim( (string) ( $item['value'] ?? '' ) );
			$word       = trim( (string) ( $item['word'] ?? '' ) );
			$icon_path  = $path !== '' ? $path . '.' . $i . '.icon' : '';
			$item_class = trim( 'meta-item jcp-collection-item ' . $css_class );
			?>
			<div class="<?php echo esc_attr( $item_class ); ?>"<?php if ( $path !== '' ) { jcp_niche_array_item_attr( (int) $i ); } ?>>
				<span class="factor-icon-wrapper jcp-hero-meta-icon"<?php if ( $icon_path !== '' && function_exists( 'jcp_niche_user_can_inline_edit' ) && jcp_niche_user_can_inline_edit() ) { ?> data-jcp-icon-path="<?php echo esc_attr( $icon_path ); ?>" title="<?php esc_attr_e( 'Click to change icon', 'jcp-core' ); ?>" role="button" tabindex="0"<?php } ?>>
					<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="meta-icon" alt="" width="20" height="20" />
				</span>
				<div class="meta-copy">
					<strong class="meta-title">
						<?php if ( $path !== '' && ( $value !== '' || $word !== '' ) ) : ?>
							<span<?php jcp_niche_editable_attr( $path . '.' . $i . '.value' ); ?>><?php echo esc_html( $value ); ?></span><?php if ( $word !== '' ) : ?><span<?php jcp_niche_editable_attr( $path . '.' . $i . '.label' ); ?>><?php echo esc_html( ' ' . $word ); ?></span><?php endif; ?>
						<?php elseif ( $path !== '' ) : ?>
							<span<?php jcp_niche_editable_attr( $path . '.' . $i . '.label' ); ?>><?php echo esc_html( $label ); ?></span>
						<?php else : ?>
							<?php echo esc_html( $label ); ?>
						<?php endif; ?>
					</strong>
					<?php if ( $detail !== '' || $path !== '' ) : ?>
						<span class="meta-detail"<?php if ( $path !== '' ) { jcp_niche_editable_attr( $path . '.' . $i . '.detail' ); } ?>><?php echo esc_html( $detail ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $collection_controls && $path !== '' && function_exists( 'jcp_niche_collection_remove_btn' ) ) { jcp_niche_collection_remove_btn(); } ?>
			</div>
		<?php endforeach; ?>
		<?php if ( $collection_controls && $path !== '' && function_exists( 'jcp_niche_collection_add_btn' ) ) { jcp_niche_collection_add_btn( __( '+ Add stat', 'jcp-core' ) ); } ?>
	</div>
	<?php
}

/**
 * Hero social proof row: stars + face stack + rating label (campaign landers).
 *
 * @param array<string, mixed> $proof  { rating?: int, label?: string, faces?: list<{image_url,image_alt}> }.
 * @param string               $path   Editable path prefix.
 */
function jcp_component_hero_social_proof( array $proof, string $path = 'hero.social_proof' ): void {
	$faces = [];
	foreach ( (array) ( $proof['faces'] ?? [] ) as $face ) {
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
	$label  = trim( (string) ( $proof['label'] ?? '' ) );
	$rating = (int) ( $proof['rating'] ?? 5 );
	if ( $rating < 1 ) {
		$rating = 5;
	}
	if ( $rating > 5 ) {
		$rating = 5;
	}
	if ( $faces === [] && $label === '' ) {
		return;
	}
	?>
	<div class="jcp-hero-social-proof"<?php echo $path !== '' ? ' data-jcp-path-root="' . esc_attr( $path ) . '"' : ''; ?>>
		<div class="jcp-hero-social-proof__stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating */ __( '%d out of 5 stars', 'jcp-core' ), $rating ) ); ?>">
			<span aria-hidden="true"><?php echo esc_html( str_repeat( '★', $rating ) ); ?></span>
		</div>
		<?php if ( $faces !== [] ) : ?>
		<div class="jcp-hero-social-proof__faces" aria-hidden="true">
			<?php foreach ( $faces as $i => $face ) : ?>
				<img src="<?php echo esc_url( $face['url'] ); ?>" alt="" width="36" height="36" loading="lazy" decoding="async"<?php echo $path !== '' ? ' data-jcp-media-url-path="' . esc_attr( $path . '.faces.' . $i . '.image_url' ) . '"' : ''; ?> />
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php if ( $label !== '' ) : ?>
			<span class="jcp-hero-social-proof__label"<?php jcp_niche_editable_attr( $path . '.label' ); ?>><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

function jcp_component_hero_home_visual( string $demo_url = '', string $photo_url = '', string $photo_alt = '', bool $wrap_visual = true, ?array $cards = null, bool $lock_photo = false, string $cta_label = '' ): void {
	$demo_url  = $demo_url !== '' ? $demo_url : home_url( '/demo/' );
	$photo     = $photo_url !== '' ? $photo_url : jcp_media_default_phone_image();
	$cta_label = trim( $cta_label ) !== '' ? trim( $cta_label ) : __( 'Try the demo', 'jcp-core' );
	// Label + chevron SVG — strip trailing arrows so we never render two.
	$cta_label = trim( (string) preg_replace( '/[\s]*[→⟶»›]+[\s]*$/u', '', $cta_label ) );
	if ( $cta_label === '' ) {
		$cta_label = __( 'Try the demo', 'jcp-core' );
	}
	if ( ! is_array( $cards ) || $cards === [] ) {
		$cards = function_exists( 'jcp_media_industry_phone_cards' )
			? jcp_media_industry_phone_cards( '' )
			: [
				[ 'title' => __( 'New job captured', 'jcp-core' ), 'subtitle' => __( 'Photo uploaded', 'jcp-core' ) ],
				[ 'title' => __( 'AI check-in complete', 'jcp-core' ), 'subtitle' => __( 'Verified proof ready', 'jcp-core' ) ],
				[ 'title' => __( 'Ready to publish', 'jcp-core' ), 'subtitle' => __( 'Website · Google · Social', 'jcp-core' ) ],
			];
	}
	$card_icons = [
		'<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
		'<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
		'<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
	];
	if ( $wrap_visual ) {
		?>
	<div class="jcp-hero-visual hero-visual">
		<?php
	}
	?>
		<div class="hero-visual-stack">
			<div class="hero-visual-lines" aria-hidden="true">
				<span class="hero-line hero-line-1"></span>
				<span class="hero-line hero-line-2"></span>
				<span class="hero-line hero-line-3"></span>
				<span class="hero-line hero-line-4"></span>
				<span class="hero-line hero-line-5"></span>
			</div>
			<a href="<?php echo esc_url( $demo_url ); ?>" class="demo-phone-mockup hero-phone-mockup" aria-label="<?php echo esc_attr( $cta_label ); ?>" data-jcp-phone-cta-path="hero.phone_cta_label">
				<div class="phone-frame hero-phone-frame">
					<div class="phone-screen">
						<div class="phone-content">
							<div class="phone-header hero-phone-header">
								<div class="phone-status-bar">
									<span>9:41</span>
									<svg class="phone-battery-icon" width="24" height="12" viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
										<rect x="1" y="3" width="18" height="6" rx="1.5" fill="currentColor" fill-opacity="1"/>
										<rect x="1" y="3" width="18" height="6" rx="1.5" stroke="currentColor"/>
										<path d="M20 5v2h2v-2z" fill="currentColor"/>
									</svg>
								</div>
								<div class="hero-phone-live-row">
									<span class="hero-phone-live-badge"><?php esc_html_e( 'Live', 'jcp-core' ); ?></span>
								</div>
							</div>
							<div class="phone-body hero-phone-body">
								<div class="hero-phone-image-wrap">
									<img
										src="<?php echo esc_url( $photo ); ?>"
										alt="<?php echo esc_attr( $photo_alt ); ?>"
										class="hero-phone-image jcp-editable-media-image<?php echo $lock_photo ? ' jcp-hero-phone-image--featured' : ''; ?>"
										loading="eager"
										decoding="async"
										<?php if ( ! $lock_photo ) : ?>
										data-jcp-media-url-path="hero.phone_image_url"
										data-jcp-media-alt-path="hero.phone_image_alt"
										data-jcp-media-role="phone_screen"
										<?php else : ?>
										data-jcp-media-role="phone_screen_featured"
										data-jcp-media-locked="featured"
										<?php endif; ?>
									/>
								</div>
								<?php foreach ( array_values( $cards ) as $ci => $card ) : ?>
									<?php
									if ( ! is_array( $card ) ) {
										continue;
									}
									$title    = (string) ( $card['title'] ?? '' );
									$subtitle = (string) ( $card['subtitle'] ?? '' );
									$icon_svg = $card_icons[ $ci ] ?? $card_icons[0];
									?>
								<div class="demo-preview-item hero-phone-card hero-phone-card-<?php echo (int) ( $ci + 1 ); ?>">
									<div class="demo-item-icon">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
											<?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG paths. ?>
										</svg>
									</div>
									<div class="demo-item-content">
										<div class="demo-item-title"><?php echo esc_html( $title ); ?></div>
										<div class="demo-item-subtitle"><?php echo esc_html( $subtitle ); ?></div>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
	<?php
	if ( $wrap_visual ) {
		?>
	</div>
		<?php
	}
}

/**
 * Full JCP story phone — capture → optimize → publish → channels → more jobs.
 * Used in homepage/campaign heroes and demo preview sections.
 *
 * @param string $demo_url Demo URL.
 * @param string $photo_url Optional job photo for the capture beat.
 */
function jcp_component_demo_app_phone( string $demo_url = '', string $photo_url = '' ): void {
	$demo_url  = $demo_url !== '' ? $demo_url : home_url( '/demo/' );
	$photo_url = $photo_url !== '' ? $photo_url : ( function_exists( 'jcp_media_default_phone_image' ) ? jcp_media_default_phone_image() : '' );
	$captions  = [
		__( '1. Tap + to start a check-in', 'jcp-core' ),
		__( '2. Snap the finished job photo', 'jcp-core' ),
		__( '3. AI writes usable local proof', 'jcp-core' ),
		__( '4. Ready to publish across connected channels', 'jcp-core' ),
		__( '5. More visibility → more reasons to call', 'jcp-core' ),
	];
	?>
	<div class="jcp-story-phone" data-jcp-story-phone>
		<div class="jcp-story-phone__glow" aria-hidden="true"></div>
		<div class="jcp-story-phone__orbit" aria-hidden="true">
			<span class="jcp-story-chip jcp-story-chip--maps" data-story-chip="maps">
				<img src="<?php echo esc_url( jcp_core_icon( 'map-pin' ) ); ?>" alt="" width="14" height="14" />
				<?php esc_html_e( 'Google Maps', 'jcp-core' ); ?>
			</span>
			<span class="jcp-story-chip jcp-story-chip--web" data-story-chip="web">
				<img src="<?php echo esc_url( jcp_core_icon( 'globe' ) ); ?>" alt="" width="14" height="14" />
				<?php esc_html_e( 'Website', 'jcp-core' ); ?>
			</span>
			<span class="jcp-story-chip jcp-story-chip--social" data-story-chip="social">
				<img src="<?php echo esc_url( jcp_core_icon( 'share-2' ) ); ?>" alt="" width="14" height="14" />
				<?php esc_html_e( 'Social', 'jcp-core' ); ?>
			</span>
			<span class="jcp-story-chip jcp-story-chip--reviews" data-story-chip="reviews">
				<img src="<?php echo esc_url( jcp_core_icon( 'star' ) ); ?>" alt="" width="14" height="14" />
				<?php esc_html_e( 'Reviews', 'jcp-core' ); ?>
			</span>
			<span class="jcp-story-chip jcp-story-chip--jobs" data-story-chip="jobs">
				<img src="<?php echo esc_url( jcp_core_icon( 'trending-up' ) ); ?>" alt="" width="14" height="14" />
				<?php esc_html_e( 'More jobs', 'jcp-core' ); ?>
			</span>
		</div>
		<a href="<?php echo esc_url( $demo_url ); ?>" class="demo-phone-mockup demo-app-phone-mockup demo-preview-phone-mockup hero-phone-mockup jcp-story-phone__device" aria-label="<?php esc_attr_e( 'Open the interactive demo', 'jcp-core' ); ?>">
			<div class="phone-frame hero-phone-frame">
				<div class="phone-screen demo-phone-screen">
					<div class="phone-content demo-phone-content">
						<div class="phone-header hero-phone-header">
							<div class="phone-status-bar">
								<span>9:41</span>
								<svg class="phone-battery-icon" width="24" height="12" viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
									<rect x="1" y="3" width="18" height="6" rx="1.5" fill="currentColor" fill-opacity="1"/>
									<rect x="1" y="3" width="18" height="6" rx="1.5" stroke="currentColor"/>
									<path d="M20 5v2h2v-2z" fill="currentColor"/>
								</svg>
							</div>
							<div class="hero-phone-live-row">
								<span class="hero-phone-live-badge"><?php esc_html_e( 'Live', 'jcp-core' ); ?></span>
							</div>
						</div>

						<div class="demo-app-screen jcp-story-stage" aria-hidden="true">
							<!-- Scene: home / empty -->
							<div class="jcp-story-scene jcp-story-scene--home is-active" data-story-scene="home">
								<div class="demo-app-header">
									<p class="demo-app-header__title"><?php esc_html_e( 'Check-ins', 'jcp-core' ); ?></p>
								</div>
								<div class="demo-content-area">
									<div class="demo-action-tiles">
										<div class="demo-tile">
											<div class="demo-tile-icon"><img src="<?php echo esc_url( jcp_core_icon( 'briefcase' ) ); ?>" class="lucide-icon" alt="" width="24" height="24" /></div>
											<div class="demo-tile-label"><?php esc_html_e( 'My Jobs', 'jcp-core' ); ?></div>
										</div>
										<div class="demo-tile">
											<div class="demo-tile-icon"><img src="<?php echo esc_url( jcp_core_icon( 'users' ) ); ?>" class="lucide-icon" alt="" width="24" height="24" /></div>
											<div class="demo-tile-label"><?php esc_html_e( 'Team', 'jcp-core' ); ?></div>
										</div>
										<div class="demo-tile">
											<div class="demo-tile-icon"><img src="<?php echo esc_url( jcp_core_icon( 'archive' ) ); ?>" class="lucide-icon" alt="" width="24" height="24" /></div>
											<div class="demo-tile-label"><?php esc_html_e( 'Archived', 'jcp-core' ); ?></div>
										</div>
									</div>
									<div class="demo-empty-state jcp-story-empty">
										<p class="demo-empty-title"><?php esc_html_e( 'Start capturing proof', 'jcp-core' ); ?></p>
										<p><?php esc_html_e( 'Take a photo → submit → ready to publish across connected channels.', 'jcp-core' ); ?></p>
										<div class="demo-empty-hint jcp-story-hint"><span><?php esc_html_e( 'Tap', 'jcp-core' ); ?> <strong>+</strong> <?php esc_html_e( 'to create a check-in', 'jcp-core' ); ?></span></div>
									</div>
								</div>
								<div class="demo-tab-bar">
									<div class="demo-tab-item demo-tab-active">
										<div class="demo-tab-icon"><img src="<?php echo esc_url( jcp_core_icon( 'clipboard-list' ) ); ?>" class="lucide-icon" alt="" width="20" height="20" /></div>
										<?php esc_html_e( 'Check-ins', 'jcp-core' ); ?>
									</div>
									<div class="demo-fab jcp-story-fab"><img src="<?php echo esc_url( jcp_core_icon( 'plus' ) ); ?>" class="lucide-icon" alt="" width="24" height="24" /></div>
									<div class="demo-tab-item">
										<div class="demo-tab-icon"><img src="<?php echo esc_url( jcp_core_icon( 'user' ) ); ?>" class="lucide-icon" alt="" width="20" height="20" /></div>
										<?php esc_html_e( 'Profile', 'jcp-core' ); ?>
									</div>
								</div>
							</div>

							<!-- Scene: camera capture -->
							<div class="jcp-story-scene jcp-story-scene--camera" data-story-scene="camera">
								<div class="jcp-story-camera">
									<div class="jcp-story-camera__chrome">
										<span><?php esc_html_e( 'PHOTO', 'jcp-core' ); ?></span>
									</div>
									<div class="jcp-story-camera__view">
										<?php if ( $photo_url !== '' ) : ?>
										<img src="<?php echo esc_url( $photo_url ); ?>" alt="" class="jcp-story-camera__img" loading="lazy" decoding="async" />
										<?php endif; ?>
										<span class="jcp-story-camera__reticle"></span>
										<span class="jcp-story-camera__flash"></span>
									</div>
									<div class="jcp-story-camera__shutter" aria-hidden="true"><span></span></div>
								</div>
							</div>

							<!-- Scene: AI processing -->
							<div class="jcp-story-scene jcp-story-scene--process" data-story-scene="process">
								<div class="jcp-story-process">
									<div class="jcp-story-process__spinner" aria-hidden="true"></div>
									<p class="jcp-story-process__title jcp-story-process__title--1"><?php esc_html_e( 'Creating your check-in…', 'jcp-core' ); ?></p>
									<p class="jcp-story-process__title jcp-story-process__title--2"><?php esc_html_e( 'Building local job proof…', 'jcp-core' ); ?></p>
									<p class="jcp-story-process__title jcp-story-process__title--3"><?php esc_html_e( 'Writing channel-ready copy…', 'jcp-core' ); ?></p>
									<ul class="jcp-story-process__steps">
										<li class="is-done"><?php esc_html_e( 'Scanning job photos', 'jcp-core' ); ?></li>
										<li class="is-done"><?php esc_html_e( 'Pinning the job site', 'jcp-core' ); ?></li>
										<li class="is-active"><?php esc_html_e( 'Building usable job copy', 'jcp-core' ); ?></li>
									</ul>
								</div>
							</div>

							<!-- Scene: check-in ready + published -->
							<div class="jcp-story-scene jcp-story-scene--checkin" data-story-scene="checkin">
								<div class="demo-app-header">
									<p class="demo-app-header__title"><?php esc_html_e( 'Check-ins', 'jcp-core' ); ?></p>
								</div>
								<div class="demo-content-area">
									<div class="jcp-story-checkin-card">
										<?php if ( $photo_url !== '' ) : ?>
										<img src="<?php echo esc_url( $photo_url ); ?>" alt="" class="jcp-story-checkin-card__photo" loading="lazy" decoding="async" />
										<?php endif; ?>
										<div class="jcp-story-checkin-card__body">
											<div class="demo-item-title"><?php esc_html_e( 'Water heater install', 'jcp-core' ); ?></div>
											<div class="demo-item-subtitle"><?php esc_html_e( '1242 Mason Rd · Austin, TX', 'jcp-core' ); ?></div>
											<span class="jcp-story-checkin-card__badge"><?php esc_html_e( 'Published', 'jcp-core' ); ?></span>
										</div>
									</div>
									<ul class="jcp-story-channels-list" aria-label="<?php esc_attr_e( 'Published channels', 'jcp-core' ); ?>">
										<li><?php esc_html_e( 'Website', 'jcp-core' ); ?></li>
										<li><?php esc_html_e( 'Google', 'jcp-core' ); ?></li>
										<li><?php esc_html_e( 'Social', 'jcp-core' ); ?></li>
										<li><?php esc_html_e( 'Directory', 'jcp-core' ); ?></li>
									</ul>
								</div>
								<div class="demo-tab-bar">
									<div class="demo-tab-item demo-tab-active">
										<div class="demo-tab-icon"><img src="<?php echo esc_url( jcp_core_icon( 'clipboard-list' ) ); ?>" class="lucide-icon" alt="" width="20" height="20" /></div>
										<?php esc_html_e( 'Check-ins', 'jcp-core' ); ?>
									</div>
									<div class="demo-fab"><img src="<?php echo esc_url( jcp_core_icon( 'plus' ) ); ?>" class="lucide-icon" alt="" width="24" height="24" /></div>
									<div class="demo-tab-item">
										<div class="demo-tab-icon"><img src="<?php echo esc_url( jcp_core_icon( 'user' ) ); ?>" class="lucide-icon" alt="" width="20" height="20" /></div>
										<?php esc_html_e( 'Profile', 'jcp-core' ); ?>
									</div>
								</div>
							</div>

							<!-- Scene: outcome / lead -->
							<div class="jcp-story-scene jcp-story-scene--outcome" data-story-scene="outcome">
								<div class="jcp-story-outcome">
									<div class="jcp-story-outcome__pulse" aria-hidden="true"></div>
									<p class="jcp-story-outcome__eyebrow"><?php esc_html_e( 'What happens next', 'jcp-core' ); ?></p>
									<p class="jcp-story-outcome__title"><?php esc_html_e( 'Customers find the work', 'jcp-core' ); ?></p>
									<p class="jcp-story-outcome__sub"><?php esc_html_e( 'Fresh local job proof helps your business stay visible, and gives homeowners more reasons to call.', 'jcp-core' ); ?></p>
									<div class="jcp-story-outcome__lead">
										<img src="<?php echo esc_url( jcp_core_icon( 'phone' ) ); ?>" alt="" width="18" height="18" />
										<div>
											<strong><?php esc_html_e( 'New lead nearby', 'jcp-core' ); ?></strong>
											<span><?php esc_html_e( '“Saw your water heater jobs on Google”', 'jcp-core' ); ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</a>
		<p class="jcp-story-phone__caption" data-jcp-story-caption data-captions="<?php echo esc_attr( wp_json_encode( array_values( $captions ) ) ); ?>">
			<?php echo esc_html( $captions[0] ); ?>
		</p>
	</div>
	<?php
}

/**
 * Single directory preview card.
 *
 * @param array<string, string> $card Card data.
 * @param int                   $i    Index for editor paths.
 */
function jcp_component_directory_preview_card( array $card, int $i = 0 ): void {
	$name     = (string) ( $card['name'] ?? '' );
	$initials = (string) ( $card['initials'] ?? '' );
	$badge    = (string) ( $card['badge'] ?? 'Listed' );
	$badge_class = (string) ( $card['badge_class'] ?? 'listed' );
	$location = (string) ( $card['location'] ?? '' );
	$jobs     = (string) ( $card['jobs'] ?? '' );
	$activity = (string) ( $card['activity'] ?? '' );
	$rating   = (string) ( $card['rating'] ?? '' );
	$highlight = ! empty( $card['highlight'] );
	$url      = ! empty( $card['url'] ) ? (string) $card['url'] : home_url( '/directory/' );
	$path     = 'directory_preview.cards.' . $i;
	?>
	<a class="directory-card<?php echo $highlight ? ' directory-card-highlight' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
		<span class="directory-badge <?php echo esc_attr( $badge_class ); ?>"<?php jcp_niche_editable_attr( $path . '.badge' ); ?>><?php echo esc_html( $badge ); ?></span>
		<div class="card-header">
			<div class="company-mark">
				<div class="company-avatar"<?php jcp_niche_editable_attr( $path . '.initials' ); ?>><?php echo esc_html( $initials ); ?></div>
			</div>
			<div class="card-header-content">
				<h3 class="card-name"<?php jcp_niche_editable_attr( $path . '.name' ); ?>><?php echo esc_html( $name ); ?></h3>
			</div>
		</div>
		<div class="card-location">
			<img src="<?php echo esc_url( jcp_core_icon( 'map-pin' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
			<span<?php jcp_niche_editable_attr( $path . '.location' ); ?>><?php echo esc_html( $location ); ?></span>
		</div>
		<div class="card-meta-row">
			<span class="meta-inline">
				<img src="<?php echo esc_url( jcp_core_icon( 'camera' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
				<span<?php jcp_niche_editable_attr( $path . '.jobs' ); ?>><?php echo esc_html( $jobs ); ?></span>
			</span>
			<span class="meta-divider">·</span>
			<span class="meta-inline">
				<img src="<?php echo esc_url( jcp_core_icon( 'clock' ) ); ?>" class="lucide-icon lucide-icon-xs" alt="" width="14" height="14" />
				<span<?php jcp_niche_editable_attr( $path . '.activity' ); ?>><?php echo esc_html( $activity ); ?></span>
			</span>
		</div>
		<div class="card-rating">
			<div class="stars" aria-hidden="true">★★★★★</div>
			<span class="rating-text"<?php jcp_niche_editable_attr( $path . '.rating' ); ?>><?php echo esc_html( $rating ); ?></span>
		</div>
		<div class="card-footer">
			<span class="view-profile"><?php esc_html_e( 'View activity', 'jcp-core' ); ?></span>
		</div>
	</a>
	<?php
}

/**
 * Audience guarantee card (who it's for — image grid variant).
 *
 * @param array<string, mixed> $aud   Audience item.
 * @param int                  $index Index for editor paths.
 * @param array<string, bool>  $vis   Section visibility: show_images, show_badges, show_titles, show_body, show_stats.
 */
function jcp_component_audience_guarantee_card( array $aud, int $index = 0, array $vis = [] ): void {
	$title      = (string) ( $aud['title'] ?? '' );
	$body       = (string) ( $aud['body'] ?? '' );
	$badge      = (string) ( $aud['badge'] ?? '' );
	$image_url  = (string) ( $aud['image_url'] ?? '' );
	$image_alt  = (string) ( $aud['image_alt'] ?? '' );
	$stat_num   = (string) ( $aud['stat_number'] ?? '' );
	$stat_label = (string) ( $aud['stat_label'] ?? '' );
	$faq_target = (string) ( $aud['faq_target'] ?? '' );
	$path       = 'who_its_for.audiences.' . $index;
	$href       = $faq_target !== '' ? '#' . ltrim( $faq_target, '#' ) : '#faq';

	$show_images = ! array_key_exists( 'show_images', $vis ) || ! empty( $vis['show_images'] );
	$show_badges = ! array_key_exists( 'show_badges', $vis ) || ! empty( $vis['show_badges'] );
	$show_titles = ! array_key_exists( 'show_titles', $vis ) || ! empty( $vis['show_titles'] );
	$show_body   = ! array_key_exists( 'show_body', $vis ) || ! empty( $vis['show_body'] );
	$show_stats  = ! array_key_exists( 'show_stats', $vis ) || ! empty( $vis['show_stats'] );
	$item_image  = ! array_key_exists( 'show_image', $aud ) || ! empty( $aud['show_image'] );
	$render_image = $show_images && $item_image;
	$render_badge = $show_badges && $badge !== '';
	$can_edit     = jcp_niche_user_can_inline_edit();
	?>
	<a href="<?php echo esc_url( $href ); ?>" class="guarantee-item<?php echo $render_image ? '' : ' guarantee-item--no-image'; ?>"<?php echo $faq_target !== '' ? ' data-faq-target="' . esc_attr( $faq_target ) . '"' : ''; jcp_niche_array_item_attr( $index ); ?><?php echo $item_image ? '' : ' data-jcp-show-image="0"'; ?>>
		<?php if ( $can_edit ) : ?>
			<button type="button" class="jcp-card-piece-toggle<?php echo $item_image ? ' is-on' : ''; ?>" data-jcp-audience-toggle="show_image" data-jcp-audience-index="<?php echo esc_attr( (string) $index ); ?>" title="<?php echo $item_image ? esc_attr__( 'Hide this card image', 'jcp-core' ) : esc_attr__( 'Show this card image', 'jcp-core' ); ?>" aria-pressed="<?php echo $item_image ? 'true' : 'false'; ?>" tabindex="-1">
				<span class="jcp-card-piece-toggle__label"><?php esc_html_e( 'Image', 'jcp-core' ); ?></span>
			</button>
		<?php endif; ?>
		<?php if ( $render_image ) : ?>
		<div class="guarantee-image-wrapper jcp-editable-media-wrap">
			<?php if ( $image_url !== '' ) : ?>
				<img
					src="<?php echo esc_url( $image_url ); ?>"
					alt="<?php echo esc_attr( $image_alt ); ?>"
					class="guarantee-image jcp-editable-media-image"
					loading="lazy"
					data-jcp-media-url-path="<?php echo esc_attr( $path . '.image_url' ); ?>"
					data-jcp-media-alt-path="<?php echo esc_attr( $path . '.image_alt' ); ?>"
					data-jcp-media-types="image"
				/>
			<?php elseif ( $can_edit ) : ?>
				<div class="guarantee-image guarantee-image--empty" data-jcp-media-url-path="<?php echo esc_attr( $path . '.image_url' ); ?>" data-jcp-media-alt-path="<?php echo esc_attr( $path . '.image_alt' ); ?>" data-jcp-media-types="image"></div>
			<?php endif; ?>
			<?php if ( $render_badge ) : ?>
				<div class="guarantee-badge"<?php jcp_niche_editable_attr( $path . '.badge' ); ?>><?php echo esc_html( $badge ); ?></div>
			<?php endif; ?>
		</div>
		<?php elseif ( $render_badge ) : ?>
			<div class="guarantee-badge guarantee-badge--solo"<?php jcp_niche_editable_attr( $path . '.badge' ); ?>><?php echo esc_html( $badge ); ?></div>
		<?php endif; ?>
		<div class="guarantee-content">
			<?php if ( $show_titles ) : ?>
			<strong<?php jcp_niche_editable_attr( $path . '.title' ); ?>><?php echo esc_html( $title ); ?></strong>
			<?php endif; ?>
			<?php if ( $show_body ) : ?>
			<p<?php jcp_niche_editable_attr( $path . '.body' ); ?>><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
			<?php if ( $show_stats && $stat_num !== '' ) : ?>
				<div class="guarantee-stat">
					<span class="stat-number"<?php jcp_niche_editable_attr( $path . '.stat_number' ); ?>><?php echo esc_html( $stat_num ); ?></span>
					<span class="stat-label"<?php jcp_niche_editable_attr( $path . '.stat_label' ); ?>><?php echo esc_html( $stat_label ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</a>
	<?php
}
