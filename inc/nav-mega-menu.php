<?php
/**
 * Navigation mega menu data + render helpers (By Trade, Features).
 *
 * @package JCP_Core
 */

/**
 * Trim intro copy for mega menu cards.
 *
 * @param string $text  Raw text.
 * @param int    $words Max words.
 */
function jcp_nav_trim_intro( string $text, int $words = 14 ): string {
	$text = trim( wp_strip_all_tags( $text ) );
	if ( $text === '' ) {
		return '';
	}
	return wp_trim_words( $text, $words, '…' );
}

/**
 * Resolve thumbnail URL for a nav card (featured image, then JSON hero/media).
 *
 * @param int                  $post_id Post ID.
 * @param array<string, mixed> $content Flat page content.
 */
function jcp_nav_resolve_thumbnail_url( int $post_id, array $content = [] ): string {
	$thumb = get_the_post_thumbnail_url( $post_id, 'medium_large' );
	if ( is_string( $thumb ) && $thumb !== '' ) {
		return $thumb;
	}

	$candidates = [];
	if ( ! empty( $content['hero'] ) && is_array( $content['hero'] ) ) {
		$candidates[] = $content['hero'];
	}
	if ( ! empty( $content['media_text'] ) && is_array( $content['media_text'] ) ) {
		$candidates[] = $content['media_text'];
	}
	if ( ! empty( $content['conversion'] ) && is_array( $content['conversion'] ) ) {
		$candidates[] = $content['conversion'];
	}

	foreach ( $candidates as $props ) {
		if ( ! function_exists( 'jcp_media_resolve_image_url_from_props' ) ) {
			continue;
		}
		$url = jcp_media_resolve_image_url_from_props( $props );
		if ( $url !== '' ) {
			return $url;
		}
	}

	return '';
}

/**
 * Trade pages for By Trade mega menu.
 *
 * @return array<int, array{label: string, url: string, excerpt: string, image: string, image_alt: string, slug: string}>
 */
function jcp_nav_get_trade_items(): array {
	$cached = get_transient( 'jcp_nav_trade_items_v1' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$posts = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]
	);

	$items = [];
	foreach ( $posts as $post ) {
		$post_id = (int) $post->ID;
		$content = function_exists( 'jcp_niche_get_content' ) ? jcp_niche_get_content( $post_id ) : [];
		$label   = ! empty( $content['niche_label'] ) ? (string) $content['niche_label'] : get_the_title( $post );
		$raw     = $content['hero']['subheadline'] ?? get_the_excerpt( $post );
		$image   = jcp_nav_resolve_thumbnail_url( $post_id, is_array( $content ) ? $content : [] );
		$alt     = ! empty( $content['hero']['image_alt'] ) ? (string) $content['hero']['image_alt'] : $label;

		$items[] = [
			'label'     => $label,
			'url'       => get_permalink( $post ),
			'excerpt'   => jcp_nav_trim_intro( (string) $raw, 16 ),
			'image'     => $image,
			'image_alt' => $alt,
			'slug'      => $post->post_name,
		];
	}

	set_transient( 'jcp_nav_trade_items_v1', $items, HOUR_IN_SECONDS );

	return $items;
}

/**
 * Default benefit icons (matches homepage benefits section).
 *
 * @return array<int, string>
 */
function jcp_nav_feature_default_icons(): array {
	return [ 'badge-check', 'map-pin', 'message-square', 'star', 'building-2', 'phone' ];
}

/**
 * Feature cards for Features mega menu (benefits block until dedicated feature pages exist).
 *
 * @return array<int, array{label: string, url: string, excerpt: string, icon: string, slug: string}>
 */
function jcp_nav_get_feature_items(): array {
	$cached = get_transient( 'jcp_nav_feature_items_v3' );
	if ( is_array( $cached ) ) {
		return apply_filters( 'jcp_nav_feature_items', $cached );
	}

	$content = [];
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id > 0 && function_exists( 'jcp_page_get_content_flat' ) ) {
		$content = jcp_page_get_content_flat( $front_id );
	}
	if ( empty( $content ) && function_exists( 'jcp_niche_load_preset' ) ) {
		$content = jcp_niche_load_preset( 'home' );
	}

	$benefit_items = $content['benefits']['items'] ?? [];
	if ( ! is_array( $benefit_items ) ) {
		$benefit_items = [];
	}

	$benefit_icons = jcp_nav_feature_default_icons();
	$items         = [];
	$index         = 0;

	foreach ( $benefit_items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = trim( (string) ( $item['title'] ?? '' ) );
		if ( $title === '' ) {
			continue;
		}
		$slug    = sanitize_title( $title );
		$icon    = ! empty( $item['icon'] ) ? (string) $item['icon'] : ( $benefit_icons[ $index % count( $benefit_icons ) ] ?? 'badge-check' );
		$feature = jcp_nav_resolve_feature_page( $slug );

		$items[] = [
			'label'   => $title,
			'url'     => $feature['url'],
			'excerpt' => jcp_nav_trim_intro( (string) ( $item['body'] ?? '' ), 12 ),
			'icon'    => $icon,
			'slug'    => $slug,
		];
		++$index;
	}

	set_transient( 'jcp_nav_feature_items_v3', $items, HOUR_IN_SECONDS );

	return apply_filters( 'jcp_nav_feature_items', $items );
}

/**
 * Resolve a feature page when it exists; otherwise fall back to homepage #features.
 *
 * @param string $slug Feature slug.
 * @return array{url: string, image: string, image_alt: string}
 */
function jcp_nav_resolve_feature_page( string $slug ): array {
	$empty = [
		'url'       => home_url( '/#features' ),
		'image'     => '',
		'image_alt' => '',
	];

	$slug = sanitize_title( $slug );
	if ( $slug === '' ) {
		return $empty;
	}

	$page = get_page_by_path( 'features/' . $slug );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( $slug );
	}
	if ( ! $page instanceof WP_Post || $page->post_status !== 'publish' ) {
		return $empty;
	}

	$image = get_the_post_thumbnail_url( (int) $page->ID, 'medium_large' );
	if ( ! is_string( $image ) ) {
		$image = '';
	}

	return [
		'url'       => get_permalink( $page ),
		'image'     => $image,
		'image_alt' => get_the_title( $page ),
	];
}

/**
 * Clear nav mega menu caches when trade or feature content changes.
 */
function jcp_nav_clear_mega_menu_cache(): void {
	delete_transient( 'jcp_nav_trade_items_v1' );
	delete_transient( 'jcp_nav_feature_items_v1' );
	delete_transient( 'jcp_nav_feature_items_v2' );
	delete_transient( 'jcp_nav_feature_items_v3' );
}
add_action( 'save_post_jcp_niche_landing', 'jcp_nav_clear_mega_menu_cache' );
add_action( 'save_post_page', 'jcp_nav_clear_mega_menu_cache' );
add_action( 'after_switch_theme', 'jcp_nav_clear_mega_menu_cache' );

/**
 * Render a single mega menu card.
 *
 * @param array<string, string> $item    Card data.
 * @param string                $variant desktop|mobile.
 * @param bool                  $show_excerpt Whether to show excerpt line.
 */
function jcp_nav_render_mega_card( array $item, string $variant = 'desktop', bool $show_excerpt = true ): void {
	$label   = $item['label'] ?? '';
	$url     = $item['url'] ?? '';
	$excerpt = $item['excerpt'] ?? '';
	$icon    = $item['icon'] ?? '';
	$image   = $item['image'] ?? '';
	$alt     = $item['image_alt'] ?? $label;
	$slug    = $item['slug'] ?? sanitize_title( $label );

	if ( $label === '' || $url === '' ) {
		return;
	}

	$tag = $variant === 'mobile' ? 'a' : 'a';
	$class = $variant === 'mobile' ? 'mobile-mega-card' : 'nav-mega-card';
	?>
	<<?php echo esc_html( $tag ); ?>
		class="<?php echo esc_attr( $class ); ?>"
		href="<?php echo esc_url( $url ); ?>"
		data-nav-card-slug="<?php echo esc_attr( $slug ); ?>"
	>
		<span class="nav-mega-card-thumb<?php echo $image === '' ? ' nav-mega-card-thumb--placeholder' : ''; ?>" aria-hidden="true">
			<?php if ( $image !== '' ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async" width="72" height="72" />
			<?php else : ?>
				<span class="nav-mega-card-initial"><?php
				$initial = function_exists( 'mb_substr' )
					? mb_strtoupper( mb_substr( $label, 0, 1 ) )
					: strtoupper( substr( $label, 0, 1 ) );
				echo esc_html( $initial );
				?></span>
			<?php endif; ?>
		</span>
		<span class="nav-mega-card-body">
			<strong class="nav-mega-card-title"><?php echo esc_html( $label ); ?></strong>
			<?php if ( $show_excerpt && $excerpt !== '' ) : ?>
				<span class="nav-mega-card-excerpt"><?php echo esc_html( $excerpt ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

/**
 * Render a Features mega menu card (homepage benefit icons only — never photos).
 *
 * @param array<string, string> $item         Card data.
 * @param string                $variant      desktop|mobile.
 * @param bool                  $show_excerpt Whether to show excerpt line.
 */
function jcp_nav_render_feature_mega_card( array $item, string $variant = 'desktop', bool $show_excerpt = true ): void {
	$label   = $item['label'] ?? '';
	$url     = $item['url'] ?? '';
	$excerpt = $item['excerpt'] ?? '';
	$icon    = $item['icon'] ?? '';
	$slug    = $item['slug'] ?? sanitize_title( $label );

	if ( $label === '' || $url === '' ) {
		return;
	}

	if ( $icon === '' || ! function_exists( 'jcp_core_icon' ) ) {
		$icons = jcp_nav_feature_default_icons();
		$icon  = $icons[0];
	}

	$class = $variant === 'mobile' ? 'mobile-mega-card' : 'nav-mega-card';
	$icon_size = $variant === 'mobile' ? 22 : 24;
	?>
	<a
		class="<?php echo esc_attr( $class ); ?>"
		href="<?php echo esc_url( $url ); ?>"
		data-nav-card-slug="<?php echo esc_attr( $slug ); ?>"
		data-nav-card-type="feature"
	>
		<span class="factor-icon-wrapper nav-mega-card-icon" aria-hidden="true">
			<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="factor-icon" alt="" width="<?php echo (int) $icon_size; ?>" height="<?php echo (int) $icon_size; ?>" />
		</span>
		<span class="nav-mega-card-body">
			<strong class="nav-mega-card-title"><?php echo esc_html( $label ); ?></strong>
			<?php if ( $show_excerpt && $excerpt !== '' ) : ?>
				<span class="nav-mega-card-excerpt"><?php echo esc_html( $excerpt ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

/**
 * Desktop mega menu trigger: By Trade.
 *
 * @param string $label Optional custom label.
 */
function jcp_nav_render_desktop_trade_mega_trigger( string $label = '' ): void {
	if ( $label === '' ) {
		$label = __( 'By Trade', 'jcp-core' );
	}
	?>
	<div class="nav-mega nav-dropdown" id="navByTradeMega" data-nav-mega="trade">
		<button
			type="button"
			class="nav-dropdown-trigger nav-link"
			id="navByTradeTrigger"
			aria-haspopup="true"
			aria-expanded="false"
			aria-controls="navByTradePanel"
			data-page="industries"
		>
			<?php echo esc_html( $label ); ?>
			<svg class="nav-dropdown-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
		</button>
	</div>
	<?php
}

/**
 * Desktop mega menu panel: By Trade (mounted on header stack, outside backdrop-filter header).
 *
 * @param string $industries_url Hub URL.
 */
function jcp_nav_render_desktop_trade_mega_panel( string $industries_url ): void {
	$items = jcp_nav_get_trade_items();
	?>
	<div class="nav-mega-panel" id="navByTradePanel" role="region" aria-labelledby="navByTradeTrigger" data-nav-mega-panel="trade" hidden>
		<div class="nav-mega-inner">
			<div class="nav-mega-header">
				<div class="nav-mega-header-copy">
					<p class="nav-mega-eyebrow"><?php esc_html_e( 'By Trade', 'jcp-core' ); ?></p>
					<h2 class="nav-mega-title"><?php esc_html_e( 'Marketing built for how you work', 'jcp-core' ); ?></h2>
					<p class="nav-mega-lead"><?php esc_html_e( 'See how JobCapturePro turns completed jobs into proof, visibility, and calls for your trade.', 'jcp-core' ); ?></p>
				</div>
				<a class="nav-mega-header-link" href="<?php echo esc_url( $industries_url ); ?>"><?php esc_html_e( 'Browse all trades', 'jcp-core' ); ?> →</a>
			</div>
			<?php if ( ! empty( $items ) ) : ?>
				<div class="nav-mega-grid nav-mega-grid--trades" role="list">
					<?php foreach ( $items as $item ) : ?>
						<?php jcp_nav_render_mega_card( $item, 'desktop', true ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="nav-mega-empty"><?php esc_html_e( 'Trade pages coming soon.', 'jcp-core' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Desktop mega menu trigger + panel (legacy wrapper).
 *
 * @param string $industries_url Hub URL.
 */
function jcp_nav_render_desktop_trade_mega( string $industries_url ): void {
	jcp_nav_render_desktop_trade_mega_trigger();
}

/**
 * Desktop mega menu trigger: Features.
 *
 * @param string $label Optional custom label.
 */
function jcp_nav_render_desktop_features_mega_trigger( string $label = '' ): void {
	if ( $label === '' ) {
		$label = __( 'Features', 'jcp-core' );
	}
	?>
	<div class="nav-mega nav-dropdown" id="navFeaturesMega" data-nav-mega="features">
		<button
			type="button"
			class="nav-dropdown-trigger nav-link"
			id="navFeaturesTrigger"
			aria-haspopup="true"
			aria-expanded="false"
			aria-controls="navFeaturesPanel"
			data-home-anchor="#features"
		>
			<?php echo esc_html( $label ); ?>
			<svg class="nav-dropdown-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
		</button>
	</div>
	<?php
}

/**
 * Desktop mega menu panel: Features (mounted on header stack, outside backdrop-filter header).
 *
 * @param string $features_url Default features anchor URL.
 */
function jcp_nav_render_desktop_features_mega_panel( string $features_url ): void {
	$items = jcp_nav_get_feature_items();
	?>
	<div class="nav-mega-panel" id="navFeaturesPanel" role="region" aria-labelledby="navFeaturesTrigger" data-nav-mega-panel="features" hidden>
		<div class="nav-mega-inner">
			<div class="nav-mega-header">
				<div class="nav-mega-header-copy">
					<p class="nav-mega-eyebrow"><?php esc_html_e( 'Features', 'jcp-core' ); ?></p>
					<h2 class="nav-mega-title"><?php esc_html_e( 'Proof that compounds across every channel', 'jcp-core' ); ?></h2>
					<p class="nav-mega-lead"><?php esc_html_e( 'Everything JobCapturePro publishes automatically from the jobs you already complete.', 'jcp-core' ); ?></p>
				</div>
				<a class="nav-mega-header-link" href="<?php echo esc_url( $features_url ); ?>" data-home-anchor="#features"><?php esc_html_e( 'See all features', 'jcp-core' ); ?> →</a>
			</div>
			<?php if ( ! empty( $items ) ) : ?>
				<div class="nav-mega-grid nav-mega-grid--features" role="list">
					<?php foreach ( $items as $item ) : ?>
						<?php jcp_nav_render_feature_mega_card( $item, 'desktop', true ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="nav-mega-empty"><?php esc_html_e( 'Feature pages coming soon.', 'jcp-core' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Desktop mega menu trigger + panel (legacy wrapper).
 *
 * @param string $features_url Default features anchor URL.
 */
function jcp_nav_render_desktop_features_mega( string $features_url ): void {
	jcp_nav_render_desktop_features_mega_trigger();
}

/**
 * Mobile expanding panel: By Trade.
 *
 * @param string $industries_url Hub URL.
 * @param string $label          Optional custom label.
 */
function jcp_nav_render_mobile_trade_panel( string $industries_url, string $label = '' ): void {
	$items = jcp_nav_get_trade_items();
	if ( $label === '' ) {
		$label = __( 'By Trade', 'jcp-core' );
	}
	?>
	<details class="mobile-nav-panel mobile-nav-panel--trades" id="mobileNavTrades">
		<summary class="mobile-nav-panel-summary" aria-controls="mobileNavTradesBody">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
				<path d="M2 20h20"></path>
				<path d="M5 20V10l7-6 7 6v10"></path>
				<path d="M9 20v-6h6v6"></path>
			</svg>
			<span><?php echo esc_html( $label ); ?></span>
		</summary>
		<div class="mobile-nav-panel-body" id="mobileNavTradesBody">
			<p class="mobile-nav-panel-lead"><?php esc_html_e( 'Pick your trade to see how JobCapturePro fits your business.', 'jcp-core' ); ?></p>
			<div class="mobile-mega-list">
				<?php foreach ( $items as $item ) : ?>
					<?php jcp_nav_render_mega_card( $item, 'mobile', true ); ?>
				<?php endforeach; ?>
			</div>
			<a class="mobile-nav-panel-footer" href="<?php echo esc_url( $industries_url ); ?>" data-page="industries"><?php esc_html_e( 'Browse all trades', 'jcp-core' ); ?> →</a>
		</div>
	</details>
	<?php
}

/**
 * Mobile expanding panel: Features.
 *
 * @param string $features_url Default features anchor URL.
 * @param string $label        Optional custom label.
 */
function jcp_nav_render_mobile_features_panel( string $features_url, string $label = '' ): void {
	$items = jcp_nav_get_feature_items();
	if ( $label === '' ) {
		$label = __( 'Features', 'jcp-core' );
	}
	?>
	<details class="mobile-nav-panel mobile-nav-panel--features" id="mobileNavFeatures">
		<summary class="mobile-nav-panel-summary" aria-controls="mobileNavFeaturesBody">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
				<rect x="3" y="3" width="7" height="7"></rect>
				<rect x="14" y="3" width="7" height="7"></rect>
				<rect x="14" y="14" width="7" height="7"></rect>
				<rect x="3" y="14" width="7" height="7"></rect>
			</svg>
			<span><?php echo esc_html( $label ); ?></span>
		</summary>
		<div class="mobile-nav-panel-body" id="mobileNavFeaturesBody">
			<p class="mobile-nav-panel-lead"><?php esc_html_e( 'Explore what JobCapturePro publishes from every completed job.', 'jcp-core' ); ?></p>
			<div class="mobile-mega-list">
				<?php foreach ( $items as $item ) : ?>
					<?php jcp_nav_render_feature_mega_card( $item, 'mobile', true ); ?>
				<?php endforeach; ?>
			</div>
			<a class="mobile-nav-panel-footer" href="<?php echo esc_url( $features_url ); ?>" data-home-anchor="#features"><?php esc_html_e( 'See all features', 'jcp-core' ); ?> →</a>
		</div>
	</details>
	<?php
}

/**
 * Render desktop main-header items from the shared header_nav config.
 *
 * @param array<string, mixed> $ctx Context: home_how, home_features, industries_url, etc.
 */
function jcp_nav_render_desktop_main_items( array $ctx = [] ): void {
	$items = function_exists( 'jcp_global_resolve_header_nav' ) ? jcp_global_resolve_header_nav() : [];
	$home_features   = (string) ( $ctx['home_features'] ?? home_url( '/#features' ) );
	$industries_url  = (string) ( $ctx['industries_url'] ?? home_url( '/industries/' ) );

	foreach ( $items as $item ) {
		if ( empty( $item['enabled'] ) ) {
			continue;
		}
		$type  = (string) ( $item['type'] ?? 'link' );
		$label = (string) ( $item['label'] ?? '' );
		$url   = (string) ( $item['resolved_url'] ?? '' );

		if ( $type === 'features_mega' ) {
			if ( function_exists( 'jcp_nav_render_desktop_features_mega_trigger' ) ) {
				jcp_nav_render_desktop_features_mega_trigger( $label );
			} else {
				printf(
					'<a href="%s" class="nav-link" data-home-anchor="#features">%s</a>',
					esc_url( $url !== '' ? $url : $home_features ),
					esc_html( $label )
				);
			}
			continue;
		}
		if ( $type === 'trade_mega' ) {
			if ( function_exists( 'jcp_nav_render_desktop_trade_mega_trigger' ) ) {
				jcp_nav_render_desktop_trade_mega_trigger( $label );
			} else {
				printf(
					'<a href="%s" class="nav-link" data-page="industries">%s</a>',
					esc_url( $url !== '' ? $url : $industries_url ),
					esc_html( $label )
				);
			}
			continue;
		}
		if ( $type === 'dropdown' ) {
			$children = array_values(
				array_filter(
					(array) ( $item['children'] ?? [] ),
					static fn( $c ): bool => is_array( $c ) && ! empty( $c['enabled'] )
				)
			);
			if ( $children === [] ) {
				continue;
			}
			?>
			<div class="nav-dropdown" id="navResourcesDropdown">
				<button type="button" class="nav-dropdown-trigger nav-link" id="navResourcesTrigger" aria-haspopup="true" aria-expanded="false" aria-controls="navResourcesMenu"><?php echo esc_html( $label ); ?> <svg class="nav-dropdown-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
				<div class="nav-dropdown-menu" id="navResourcesMenu" role="menu" aria-labelledby="navResourcesTrigger" hidden>
					<?php foreach ( $children as $child ) : ?>
						<a
							href="<?php echo esc_url( (string) ( $child['resolved_url'] ?? '' ) ); ?>"
							class="nav-dropdown-item nav-link"
							role="menuitem"
							<?php if ( ! empty( $child['data_page'] ) ) : ?>
								data-page="<?php echo esc_attr( (string) $child['data_page'] ); ?>"
							<?php endif; ?>
						><?php echo esc_html( (string) ( $child['label'] ?? '' ) ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
			continue;
		}

		$attrs = '';
		if ( ! empty( $item['home_anchor'] ) ) {
			$attrs .= ' data-home-anchor="' . esc_attr( (string) $item['home_anchor'] ) . '"';
		}
		if ( ! empty( $item['data_page'] ) ) {
			$attrs .= ' data-page="' . esc_attr( (string) $item['data_page'] ) . '"';
		}
		printf(
			'<a href="%s" class="nav-link"%s>%s</a>',
			esc_url( $url ),
			$attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr above.
			esc_html( $label )
		);
	}
}

/**
 * Render mobile main-header items from the shared header_nav config.
 *
 * @param array<string, mixed> $ctx Context URLs.
 */
function jcp_nav_render_mobile_main_items( array $ctx = [] ): void {
	$items = function_exists( 'jcp_global_resolve_header_nav' ) ? jcp_global_resolve_header_nav() : [];
	$home_features  = (string) ( $ctx['home_features'] ?? home_url( '/#features' ) );
	$industries_url = (string) ( $ctx['industries_url'] ?? home_url( '/industries/' ) );

	$icons = [
		'how_it_works' => '<circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon>',
		'features'     => '<rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>',
		'by_trade'     => '<path d="M2 20h20"></path><path d="M5 20V10l7-6 7 6v10"></path><path d="M9 20v-6h6v6"></path>',
		'pricing'      => '<line x1="12" y1="2" x2="12" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>',
		'blog'         => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="10" x2="16" y2="10"></line>',
		'help'         => '<circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line>',
		'contact'      => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
		'referral'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
	];

	foreach ( $items as $item ) {
		if ( empty( $item['enabled'] ) ) {
			continue;
		}
		$type  = (string) ( $item['type'] ?? 'link' );
		$label = (string) ( $item['label'] ?? '' );
		$url   = (string) ( $item['resolved_url'] ?? '' );
		$id    = (string) ( $item['id'] ?? '' );

		if ( $type === 'features_mega' ) {
			if ( function_exists( 'jcp_nav_render_mobile_features_panel' ) ) {
				jcp_nav_render_mobile_features_panel( $url !== '' ? $url : $home_features, $label );
			}
			continue;
		}
		if ( $type === 'trade_mega' ) {
			if ( function_exists( 'jcp_nav_render_mobile_trade_panel' ) ) {
				jcp_nav_render_mobile_trade_panel( $url !== '' ? $url : $industries_url, $label );
			}
			continue;
		}
		if ( $type === 'dropdown' ) {
			$children = array_values(
				array_filter(
					(array) ( $item['children'] ?? [] ),
					static fn( $c ): bool => is_array( $c ) && ! empty( $c['enabled'] )
				)
			);
			if ( $children === [] ) {
				continue;
			}
			?>
			<details class="mobile-nav-resources" id="mobileNavResources">
				<summary class="mobile-nav-resources-summary" aria-expanded="false" aria-controls="mobileNavResourcesList"><?php echo esc_html( $label ); ?></summary>
				<div class="mobile-nav-resources-list" id="mobileNavResourcesList">
					<?php foreach ( $children as $child ) : ?>
						<?php
						$cid  = (string) ( $child['id'] ?? '' );
						$icon = $icons[ $cid ] ?? $icons['blog'];
						?>
						<a
							href="<?php echo esc_url( (string) ( $child['resolved_url'] ?? '' ) ); ?>"
							class="mobile-nav-link"
							<?php if ( ! empty( $child['data_page'] ) ) : ?>
								data-page="<?php echo esc_attr( (string) $child['data_page'] ); ?>"
							<?php endif; ?>
						>
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
								<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
							</svg>
							<span><?php echo esc_html( (string) ( $child['label'] ?? '' ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</details>
			<?php
			continue;
		}

		$icon  = $icons[ $id ] ?? $icons['how_it_works'];
		$attrs = '';
		if ( ! empty( $item['home_anchor'] ) ) {
			$attrs .= ' data-home-anchor="' . esc_attr( (string) $item['home_anchor'] ) . '"';
		}
		if ( ! empty( $item['data_page'] ) ) {
			$attrs .= ' data-page="' . esc_attr( (string) $item['data_page'] ) . '"';
		}
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="mobile-nav-link"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
				<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			</svg>
			<span><?php echo esc_html( $label ); ?></span>
		</a>
		<?php
	}
}
