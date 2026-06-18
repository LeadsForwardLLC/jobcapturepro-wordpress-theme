<?php
/**
 * Unified JCP page renderer (block loop → existing section renderers).
 *
 * @package JCP_Core
 */

/**
 * Render a structured JCP page.
 *
 * @param int $post_id Post ID.
 */
function jcp_page_render( int $post_id ): void {
	$content   = jcp_page_get_content( $post_id );
	$legacy    = jcp_page_blocks_to_legacy( $content );
	$page_key  = ! empty( $legacy['niche_key'] ) ? sanitize_title( (string) $legacy['niche_key'] ) : sanitize_title( get_post_field( 'post_name', $post_id ) );
	$page_kind = (string) ( $content['page_kind'] ?? 'marketing' );
	$is_ref    = $page_kind === 'referral' || ( ( $legacy['page_type'] ?? '' ) === 'referral' );
	$is_home   = $page_kind === 'home' || ( ( $legacy['page_type'] ?? '' ) === 'home' );

	$main_class = 'jcp-marketing jcp-niche';
	if ( $is_home ) {
		$main_class = 'jcp-marketing jcp-home';
	} elseif ( $is_ref ) {
		$main_class .= ' jcp-niche-referral';
	}
	if ( $page_kind === 'marketing' ) {
		$main_class .= ' jcp-page-marketing';
	}

	echo '<main class="' . esc_attr( $main_class ) . '" data-niche="' . esc_attr( $page_key ) . '" data-page-kind="' . esc_attr( $page_kind ) . '">';

	$blocks = (array) ( $content['blocks'] ?? [] );
	if ( empty( $blocks ) ) {
		jcp_niche_render_content( $legacy, $post_id, $page_key, $is_ref );
		echo '</main>';
		return;
	}

	$ctx = [
		'post_id'     => $post_id,
		'page_key'    => $page_key,
		'page_kind'   => $page_kind,
		'is_referral' => $is_ref,
	];

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		jcp_page_render_block( $block, $legacy, $ctx );
	}

	echo '</main>';
}

/**
 * Render one block using existing niche section functions.
 *
 * @param array<string, mixed> $block  Block { type, props }.
 * @param array<string, mixed> $legacy Full legacy content for cross-section data.
 * @param array<string, mixed> $ctx    Render context.
 */
function jcp_page_render_block( array $block, array $legacy, array $ctx ): void {
	$type  = (string) ( $block['type'] ?? '' );
	$props = $block['props'] ?? [];
	if ( ! is_array( $props ) ) {
		$props = [];
	}

	$def = jcp_block_get( $type );
	if ( ! $def ) {
		return;
	}

	$kinds = $def['page_kinds'] ?? [];
	if ( $kinds && ! in_array( $ctx['page_kind'], $kinds, true ) ) {
		return;
	}

	$page_key = (string) $ctx['page_key'];
	$c        = array_merge( $legacy, [ $def['legacy_key'] ?? $type => $props ] );

	$block_id = esc_attr( (string) ( $block['id'] ?? 'b-' . $type ) );
	$layout   = jcp_block_resolve_layout( $block, (string) ( $ctx['page_kind'] ?? 'industry' ) );
	$classes  = 'jcp-block-root ' . jcp_block_layout_classes( $layout, $type );
	printf(
		'<div class="%1$s" data-jcp-block-id="%2$s" data-jcp-block-type="%3$s">',
		esc_attr( $classes ),
		$block_id,
		esc_attr( $type )
	);

	switch ( $type ) {
		case 'breadcrumb':
			// Breadcrumb markup is rendered inside the industry hero section.
			break;
		case 'hero':
			$c['_hero_variant'] = jcp_block_resolve_hero_variant( $layout );
			jcp_niche_render_hero( $c, $page_key );
			break;
		case 'media_text':
			$path = ! empty( $block['legacy_key'] ) ? (string) $block['legacy_key'] : 'media_text';
			jcp_niche_render_media_text( $props, $path );
			break;
		case 'what_it_is':
			$c_what = $c;
			unset( $c_what['core_mechanic'] );
			jcp_niche_render_what_it_is( $c_what );
			break;
		case 'core_mechanic':
			jcp_page_render_core_mechanic_block( $props );
			break;
		case 'how_it_works':
			jcp_niche_render_how_it_works( $c, $page_key );
			break;
		case 'check_ins':
			jcp_niche_render_check_ins( $c );
			break;
		case 'problem':
			jcp_niche_render_problem( $c );
			break;
		case 'benefits':
			jcp_niche_render_benefits( $c );
			break;
		case 'differentiation':
			jcp_niche_render_differentiation( $c );
			break;
		case 'who_its_for':
			jcp_niche_render_who_its_for( $c );
			break;
		case 'faq':
			jcp_niche_render_faq( $c );
			break;
		case 'final_cta':
			jcp_niche_render_final_cta( $c, $page_key );
			break;
		case 'cta_band':
			$band_key = ! empty( $props['band_key'] ) ? (string) $props['band_key'] : 'cta_band_1';
			jcp_niche_render_cta_band( $props, $page_key, $band_key );
			break;
		case 'commission':
			jcp_niche_render_commission( $c, $page_key );
			break;
		case 'partners':
			jcp_niche_render_partners( $c, $page_key );
			break;
		case 'share':
			jcp_niche_render_share( $c, $page_key );
			break;
		case 'proof_flow':
			jcp_niche_render_proof_flow( $props );
			break;
		case 'demo_preview':
			jcp_niche_render_demo_preview( $props, $page_key );
			break;
		case 'directory_preview':
			jcp_niche_render_directory_preview( $props, $page_key );
			break;
		case 'conversion':
			jcp_niche_render_conversion( $props, $page_key );
			break;
	}

	echo '</div>';
}

/**
 * Standalone core mechanic strip (when split from what_it_is block).
 *
 * @param array<int, array<string, string>> $items Mechanic items.
 */
function jcp_page_render_core_mechanic_block( array $items ): void {
	if ( empty( $items ) ) {
		return;
	}
	?>
	<section class="jcp-section jcp-niche-core-mechanic jcp-core-mechanic-strip">
		<div class="jcp-container">
			<?php jcp_niche_render_core_mechanic_strip( $items, 'core_mechanic' ); ?>
		</div>
	</section>
	<?php
}

/**
 * Legacy fixed-order render (fallback).
 *
 * @param array<string, mixed> $c         Flat content.
 * @param int                  $post_id   Post ID.
 * @param string               $page_key  Page key.
 * @param bool                 $is_ref    Referral page.
 */
function jcp_niche_render_content( array $c, int $post_id, string $page_key, bool $is_ref ): void {
	jcp_niche_render_hero( $c, $page_key );
	jcp_niche_render_what_it_is( $c );
	if ( $is_ref ) {
		jcp_niche_render_cta_band( $c['cta_band_1'] ?? [], $page_key, 'cta_band_1' );
	}
	jcp_niche_render_how_it_works( $c, $page_key );
	jcp_niche_render_check_ins( $c );
	if ( ! $is_ref ) {
		jcp_niche_render_problem( $c );
	}
	jcp_niche_render_benefits( $c );
	if ( $is_ref ) {
		jcp_niche_render_commission( $c, $page_key );
		jcp_niche_render_partners( $c, $page_key );
		jcp_niche_render_share( $c, $page_key );
	} else {
		jcp_niche_render_differentiation( $c );
		jcp_niche_render_who_its_for( $c );
	}
	jcp_niche_render_faq( $c );
	jcp_niche_render_final_cta( $c, $page_key );
}

/**
 * Render full niche landing page (backward compatible entry point).
 *
 * @param int $post_id Post ID.
 */
function jcp_niche_render_page( int $post_id ): void {
	jcp_page_render( $post_id );
}
