<?php
/**
 * Campaign landing layout: CRO-focused cold-traffic preset helpers.
 *
 * @package JCP_Core
 */

/**
 * Apply campaign-specific document defaults (breadcrumb, hero layout, flags).
 *
 * @param array<string, mixed> $doc Block document.
 * @return array<string, mixed>
 */
function jcp_page_finalize_campaign_document( array $doc ): array {
	$preset = sanitize_key( (string) ( $doc['preset'] ?? '' ) );
	if ( $preset !== 'campaign' ) {
		return $doc;
	}

	$doc['settings'] = is_array( $doc['settings'] ?? null ) ? $doc['settings'] : [];
	$doc['settings']['hide_breadcrumb']  = true;
	$doc['settings']['campaign_landing'] = true;
	$doc['settings']['hide_site_chrome'] = true;

	if ( empty( $doc['blocks'] ) || ! is_array( $doc['blocks'] ) ) {
		return $doc;
	}

	foreach ( $doc['blocks'] as $i => $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		if ( $type === 'hero' ) {
			$base = is_array( $block['layout'] ?? null )
				? $block['layout']
				: jcp_block_default_layout( 'hero', 'marketing' );
			$doc['blocks'][ $i ]['layout'] = array_merge(
				$base,
				[
					'hero_variant' => 'split',
					'align'        => 'left',
				]
			);
			$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];
			$props['show_visual'] = true;
			if ( empty( $props['media_type'] ) ) {
				$props['media_type'] = 'phone_mockup';
			}
			// Mirror core_mechanic into hero meta_stats (homepage pattern) using existing labels only.
			if ( empty( $props['meta_stats'] ) || ! is_array( $props['meta_stats'] ) ) {
				$props['meta_stats'] = jcp_page_campaign_meta_stats_from_doc( $doc );
			}
			$doc['blocks'][ $i ]['props'] = $props;
		}
		if ( $type === 'demo_preview' ) {
			$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];
			// Campaign landers always show the animated app mockup on the right.
			$props['media_type']         = 'phone_mockup';
			$props['phone_mockup_style'] = ! empty( $props['phone_mockup_style'] )
				? (string) $props['phone_mockup_style']
				: 'app_shell';
			$props['media_position']     = ! empty( $props['media_position'] )
				? (string) $props['media_position']
				: 'right';
			$doc['blocks'][ $i ]['props'] = $props;
		}
		if ( $type === 'how_it_works' ) {
			$base = is_array( $block['layout'] ?? null )
				? $block['layout']
				: jcp_block_default_layout( 'how_it_works', 'marketing' );
			$base['columns']              = 3;
			$doc['blocks'][ $i ]['layout'] = $base;
		}
		if ( $type === 'problem' ) {
			$base = is_array( $block['layout'] ?? null )
				? $block['layout']
				: jcp_block_default_layout( 'problem', 'marketing' );
			$base['columns']              = 3;
			$doc['blocks'][ $i ]['layout'] = $base;
		}
		if ( $type === 'benefits' ) {
			$base = is_array( $block['layout'] ?? null )
				? $block['layout']
				: jcp_block_default_layout( 'benefits', 'marketing' );
			$base['columns']              = 2;
			$doc['blocks'][ $i ]['layout'] = $base;
		}
	}

	return $doc;
}

/**
 * Build homepage-style hero meta_stats from campaign core_mechanic copy.
 *
 * @param array<string, mixed> $doc Block document.
 * @return array<int, array<string, string>>
 */
function jcp_page_campaign_meta_stats_from_doc( array $doc ): array {
	$icons  = [ 'camera', 'map', 'clock' ];
	$classes = [ 'meta-stat-photo', 'meta-stat-channels', 'meta-stat-busywork' ];
	$items  = [];

	foreach ( (array) ( $doc['blocks'] ?? [] ) as $block ) {
		if ( ! is_array( $block ) || ( $block['type'] ?? '' ) !== 'core_mechanic' ) {
			continue;
		}
		$props = $block['props'] ?? null;
		$rows  = is_array( $props ) ? $props : [];
		// core_mechanic props may be a list of stats.
		if ( isset( $rows['items'] ) && is_array( $rows['items'] ) ) {
			$rows = $rows['items'];
		}
		foreach ( array_values( $rows ) as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$value = trim( (string) ( $row['value'] ?? '' ) );
			$word  = trim( (string) ( $row['label'] ?? '' ) );
			$label = trim( $value . ( $value !== '' && $word !== '' ? ' ' : '' ) . $word );
			if ( $label === '' ) {
				continue;
			}
			$items[] = [
				'icon'      => (string) ( $row['icon'] ?? ( $icons[ $i ] ?? 'check' ) ),
				'label'     => $label,
				'detail'    => (string) ( $row['detail'] ?? '' ),
				'css_class' => (string) ( $row['css_class'] ?? ( $classes[ $i ] ?? '' ) ),
			];
			if ( count( $items ) >= 3 ) {
				break;
			}
		}
		break;
	}

	return $items;
}

/**
 * List counts for writer templates / AI prompts (preset-aware).
 *
 * @param string $preset Preset slug.
 * @return array<string, int|string>
 */
function jcp_writer_document_list_counts_for_preset( string $preset = 'industry' ): array {
	$preset = sanitize_key( $preset );
	if ( $preset === 'campaign' ) {
		return [
			'what_it_is_team_already' => 0,
			'what_it_is_turns_into'   => 0,
			'how_it_works_steps'      => 3,
			'check_in_features'       => 0,
			'problem_pain_points'     => 3,
			'benefits_items'          => 4,
			'who_its_for_segments'    => 0,
			'faq_questions'           => 5,
			'conversion_bullets'      => 0,
			'core_mechanic_stats'     => 3,
		];
	}

	return jcp_writer_document_list_counts();
}

/**
 * Whether the block document is a campaign landing page.
 *
 * @param array<string, mixed> $content Normalized content.
 */
function jcp_page_is_campaign_landing( array $content ): bool {
	if ( ! empty( $content['settings']['campaign_landing'] ) ) {
		return true;
	}
	return sanitize_key( (string) ( $content['preset'] ?? '' ) ) === 'campaign';
}

/**
 * Whether the current front-end request should hide site chrome
 * (announcement banner, primary nav, full footer).
 */
function jcp_page_current_hides_site_chrome(): bool {
	if ( is_admin() || ! is_singular() ) {
		return (bool) apply_filters( 'jcp_page_hide_site_chrome', false );
	}

	$post_id = (int) get_queried_object_id();
	if ( $post_id <= 0 || ! function_exists( 'jcp_page_get_content' ) ) {
		return (bool) apply_filters( 'jcp_page_hide_site_chrome', false );
	}

	$content  = jcp_page_get_content( $post_id );
	$settings = is_array( $content['settings'] ?? null ) ? $content['settings'] : [];

	if ( array_key_exists( 'hide_site_chrome', $settings ) ) {
		$hide = ! empty( $settings['hide_site_chrome'] );
	} else {
		// Campaign pages default to stripped chrome until explicitly opted out.
		$hide = jcp_page_is_campaign_landing( $content );
	}

	return (bool) apply_filters( 'jcp_page_hide_site_chrome', $hide, $post_id, $content );
}

/**
 * Whether the current front-end request is a campaign landing page.
 */
function jcp_page_current_is_campaign_landing(): bool {
	if ( is_admin() || ! is_singular() || ! function_exists( 'jcp_page_get_content' ) ) {
		return false;
	}
	$post_id = (int) get_queried_object_id();
	if ( $post_id <= 0 ) {
		return false;
	}
	return jcp_page_is_campaign_landing( jcp_page_get_content( $post_id ) );
}

/**
 * Paid campaign landings are Meta traffic destinations — keep them out of organic indexes.
 * Home preview pages are also noindexed until cutover.
 */
function jcp_page_campaign_noindex(): void {
	if ( is_admin() || ! is_singular() || ! function_exists( 'jcp_page_get_content' ) ) {
		return;
	}
	$post_id = (int) get_queried_object_id();
	if ( $post_id <= 0 ) {
		return;
	}
	$content = jcp_page_get_content( $post_id );
	$noindex = false;
	if ( function_exists( 'jcp_page_is_campaign_landing' ) && jcp_page_is_campaign_landing( $content ) ) {
		$noindex = true;
	}
	if ( ! empty( $content['settings']['noindex'] ) || ! empty( $content['settings']['home_preview'] ) ) {
		$noindex = true;
	}
	if ( is_page( 'home-preview' ) ) {
		$noindex = true;
	}
	if ( ! $noindex ) {
		return;
	}
	echo '<meta name="robots" content="noindex, follow">' . "\n";
}
add_action( 'wp_head', 'jcp_page_campaign_noindex', 1 );
