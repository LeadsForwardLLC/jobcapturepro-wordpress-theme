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
					'hero_variant' => 'centered',
					'align'        => 'center',
				]
			);
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
