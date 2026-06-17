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
 * Homepage hero meta stats row (1 photo / 4 channels / 0 busywork).
 *
 * @param array<int, array<string, string>> $items Stats.
 * @param string                            $path  JSON path prefix for editor.
 */
function jcp_component_home_meta_stats( array $items, string $path = 'hero.meta_stats' ): void {
	if ( empty( $items ) ) {
		return;
	}
	?>
	<div class="directory-meta">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php
			if ( ! is_array( $item ) ) {
				continue;
			}
			$icon      = ! empty( $item['icon'] ) ? (string) $item['icon'] : 'check';
			$label     = (string) ( $item['label'] ?? '' );
			$detail    = (string) ( $item['detail'] ?? '' );
			$css_class = (string) ( $item['css_class'] ?? '' );
			?>
			<div class="meta-item<?php echo $css_class !== '' ? ' ' . esc_attr( $css_class ) : ''; ?>">
				<div class="meta-label">
					<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="meta-icon" alt="" width="20" height="20" />
					<strong<?php jcp_niche_editable_attr( $path . '.' . $i . '.label' ); ?>><?php echo esc_html( $label ); ?></strong>
				</div>
				<?php if ( $detail !== '' ) : ?>
					<span<?php jcp_niche_editable_attr( $path . '.' . $i . '.detail' ); ?>><?php echo esc_html( $detail ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Homepage hero phone mockup with animated cards.
 *
 * @param string $demo_url Demo link.
 */
function jcp_component_hero_home_visual( string $demo_url = '', string $photo_url = '', string $photo_alt = '', bool $wrap_visual = true ): void {
	$demo_url = $demo_url !== '' ? $demo_url : home_url( '/demo/' );
	$photo    = $photo_url !== '' ? $photo_url : jcp_media_default_phone_image();
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
			<a href="<?php echo esc_url( $demo_url ); ?>" class="demo-phone-mockup hero-phone-mockup" aria-label="<?php esc_attr_e( 'Try the live demo', 'jcp-core' ); ?>">
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
										class="hero-phone-image jcp-editable-media-image"
										width="390"
										height="292"
										fetchpriority="high"
										data-jcp-media-url-path="hero.phone_image_url"
										data-jcp-media-alt-path="hero.phone_image_alt"
										data-jcp-media-role="phone_screen"
									/>
								</div>
								<div class="demo-preview-item hero-phone-card hero-phone-card-1">
									<div class="demo-item-icon">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
											<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
											<circle cx="12" cy="13" r="4"/>
										</svg>
									</div>
									<div class="demo-item-content">
										<div class="demo-item-title"><?php esc_html_e( 'New job captured', 'jcp-core' ); ?></div>
										<div class="demo-item-subtitle"><?php esc_html_e( 'Photo uploaded', 'jcp-core' ); ?></div>
									</div>
								</div>
								<div class="demo-preview-item hero-phone-card hero-phone-card-2">
									<div class="demo-item-icon">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
											<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
											<circle cx="8.5" cy="8.5" r="1.5"/>
											<polyline points="21 15 16 10 5 21"/>
										</svg>
									</div>
									<div class="demo-item-content">
										<div class="demo-item-title"><?php esc_html_e( 'AI check-in complete', 'jcp-core' ); ?></div>
										<div class="demo-item-subtitle"><?php esc_html_e( 'Verified proof ready', 'jcp-core' ); ?></div>
									</div>
								</div>
								<div class="demo-preview-item hero-phone-card hero-phone-card-3">
									<div class="demo-item-icon">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
											<circle cx="12" cy="12" r="10"/>
											<line x1="2" y1="12" x2="22" y2="12"/>
											<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
										</svg>
									</div>
									<div class="demo-item-content">
										<div class="demo-item-title"><?php esc_html_e( 'Published everywhere', 'jcp-core' ); ?></div>
										<div class="demo-item-subtitle"><?php esc_html_e( 'Google Maps • Website • Social', 'jcp-core' ); ?></div>
									</div>
								</div>
							</div>
							<div class="phone-click-hint hero-phone-cta">
								<span><?php esc_html_e( 'Try the demo', 'jcp-core' ); ?></span>
								<?php jcp_component_chevron_svg( 20 ); ?>
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
 * Demo app phone mockup (check-ins screen) for demo preview block.
 *
 * @param string $demo_url Demo URL.
 */
function jcp_component_demo_app_phone( string $demo_url = '' ): void {
	$demo_url = $demo_url !== '' ? $demo_url : home_url( '/demo/' );
	?>
	<a href="<?php echo esc_url( $demo_url ); ?>" class="demo-phone-mockup">
		<div class="phone-frame">
			<div class="phone-screen demo-phone-screen">
				<div class="phone-content demo-phone-content">
					<div class="phone-header">
						<div class="phone-status-bar">
							<span>9:41</span>
							<svg class="phone-battery-icon" width="24" height="12" viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
								<rect x="1" y="3" width="18" height="6" rx="1.5" fill="currentColor" fill-opacity="1"/>
								<rect x="1" y="3" width="18" height="6" rx="1.5" stroke="currentColor"/>
								<path d="M20 5v2h2v-2z" fill="currentColor"/>
							</svg>
						</div>
						<div class="phone-nav"></div>
					</div>
					<div class="demo-app-screen">
						<div class="demo-app-header">
							<h1><?php esc_html_e( 'Check-ins', 'jcp-core' ); ?></h1>
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
							<div class="demo-empty-state">
								<h3><?php esc_html_e( 'Start capturing proof', 'jcp-core' ); ?></h3>
								<p><?php esc_html_e( 'Take a few photos → submit → automatically published everywhere.', 'jcp-core' ); ?></p>
								<div class="demo-empty-hint"><span><?php esc_html_e( 'Tap', 'jcp-core' ); ?> <strong>+</strong> <?php esc_html_e( 'to create a check-in', 'jcp-core' ); ?></span></div>
							</div>
						</div>
						<div class="demo-tab-bar">
							<div class="demo-tab-item demo-tab-active">
								<div class="demo-tab-icon"><img src="<?php echo esc_url( jcp_core_icon( 'clipboard-list' ) ); ?>" class="lucide-icon" alt="" width="20" height="20" /></div>
								<?php esc_html_e( 'Your check-ins', 'jcp-core' ); ?>
							</div>
							<div class="demo-fab"><img src="<?php echo esc_url( jcp_core_icon( 'plus' ) ); ?>" class="lucide-icon" alt="" width="24" height="24" /></div>
							<div class="demo-tab-item">
								<div class="demo-tab-icon"><img src="<?php echo esc_url( jcp_core_icon( 'user' ) ); ?>" class="lucide-icon" alt="" width="20" height="20" /></div>
								<?php esc_html_e( 'Profile', 'jcp-core' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</a>
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
 * @param array<string, string> $aud   Audience item.
 * @param int                   $index Index for editor paths.
 */
function jcp_component_audience_guarantee_card( array $aud, int $index = 0 ): void {
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
	?>
	<a href="<?php echo esc_url( $href ); ?>" class="guarantee-item"<?php echo $faq_target !== '' ? ' data-faq-target="' . esc_attr( $faq_target ) . '"' : ''; jcp_niche_array_item_attr( $index ); ?>>
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
			<?php else : ?>
				<div class="guarantee-image guarantee-image--empty" data-jcp-media-url-path="<?php echo esc_attr( $path . '.image_url' ); ?>" data-jcp-media-alt-path="<?php echo esc_attr( $path . '.image_alt' ); ?>" data-jcp-media-types="image"></div>
			<?php endif; ?>
			<?php if ( $badge !== '' ) : ?>
				<div class="guarantee-badge"<?php jcp_niche_editable_attr( $path . '.badge' ); ?>><?php echo esc_html( $badge ); ?></div>
			<?php endif; ?>
		</div>
		<div class="guarantee-content">
			<strong<?php jcp_niche_editable_attr( $path . '.title' ); ?>><?php echo esc_html( $title ); ?></strong>
			<p<?php jcp_niche_editable_attr( $path . '.body' ); ?>><?php echo esc_html( $body ); ?></p>
			<?php if ( $stat_num !== '' ) : ?>
				<div class="guarantee-stat">
					<span class="stat-number"<?php jcp_niche_editable_attr( $path . '.stat_number' ); ?>><?php echo esc_html( $stat_num ); ?></span>
					<span class="stat-label"<?php jcp_niche_editable_attr( $path . '.stat_label' ); ?>><?php echo esc_html( $stat_label ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</a>
	<?php
}
