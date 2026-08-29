<?php
/**
 * Ensure live homepage includes LeadsForward authority scoreboard.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical LeadsForward authority props for home + campaign.
 *
 * @return array<string, mixed>
 */
function jcp_page_authority_leadsforward_props(): array {
	return [
		'eyebrow'       => __( 'Built by LeadsForward', 'jcp-core' ),
		'headline'      => __( 'A decade growing contractors. Then we built the missing piece.', 'jcp-core' ),
		'body'          => __( '250,000+ leads. $150M+ booked. One gap we kept seeing: finished jobs that never became marketing. JobCapturePro closes it.', 'jcp-core' ),
		'stats'         => [
			[
				'value'  => '10',
				'label'  => 'years',
				'detail' => __( 'Helping contractors grow', 'jcp-core' ),
			],
			[
				'value'  => '250k+',
				'label'  => 'leads',
				'detail' => __( 'Generated for contractor clients', 'jcp-core' ),
			],
			[
				'value'  => '$150M+',
				'label'  => 'revenue',
				'detail' => __( 'Booked from those leads', 'jcp-core' ),
			],
		],
		'cta_primary'   => [
			'label' => __( 'View the live demo', 'jcp-core' ),
			'url'   => '/demo/',
		],
		'cta_note'      => __( 'No signup required · Takes about 2 minutes', 'jcp-core' ),
		'section_id'    => 'built-by-leadsforward',
		'show_eyebrow'  => true,
		'show_headline' => true,
		'show_body'     => true,
		'show_stats'    => true,
		'show_cta'      => true,
		'variant'       => 'scoreboard',
	];
}

/**
 * Insert/refresh authority after demo_preview on live home.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_home_authority( array $content, int $post_id ): array {
	if ( jcp_page_resolve_kind( $content, $post_id ) !== 'home' ) {
		return $content;
	}

	// Preview routes keep their own document.
	if ( ( $content['preset'] ?? '' ) === 'home_v2' || ! empty( $content['settings']['home_preview'] ) ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) ) {
		return $content;
	}

	$canonical = jcp_page_authority_leadsforward_props();
	$existing  = null;
	$rest      = [];
	foreach ( $blocks as $block ) {
		if ( ( $block['type'] ?? '' ) === 'authority' ) {
			$existing = $block;
			continue;
		}
		$rest[] = $block;
	}

	$authority = is_array( $existing ) ? $existing : [
		'id'    => function_exists( 'jcp_page_new_block_id' ) ? jcp_page_new_block_id( 'authority' ) : ( 'b-authority-' . wp_generate_password( 6, false ) ),
		'type'  => 'authority',
		'props' => [],
	];
	$authority['type']  = 'authority';
	$authority['props'] = array_merge( $canonical, is_array( $authority['props'] ?? null ) ? $authority['props'] : [] );
	// Force scoreboard + proof stats on home.
	$authority['props']['variant']      = 'scoreboard';
	$authority['props']['stats']        = $canonical['stats'];
	$authority['props']['eyebrow']     = $canonical['eyebrow'];
	$authority['props']['headline']    = $canonical['headline'];
	$authority['props']['body']        = $canonical['body'];
	$authority['props']['show_stats']  = true;
	$authority['props']['show_cta']    = true;
	if ( empty( $authority['props']['cta_primary']['label'] ) ) {
		$authority['props']['cta_primary'] = $canonical['cta_primary'];
	}
	if ( trim( (string) ( $authority['props']['cta_note'] ?? '' ) ) === '' ) {
		$authority['props']['cta_note'] = $canonical['cta_note'];
	}

	$out      = [];
	$inserted = false;
	foreach ( $rest as $block ) {
		$out[] = $block;
		if ( ! $inserted && ( $block['type'] ?? '' ) === 'demo_preview' ) {
			$out[]      = $authority;
			$inserted = true;
		}
	}
	if ( ! $inserted ) {
		// Fallback: after how_it_works, else append.
		$out      = [];
		$inserted = false;
		foreach ( $rest as $block ) {
			$out[] = $block;
			if ( ! $inserted && ( $block['type'] ?? '' ) === 'how_it_works' ) {
				$out[]      = $authority;
				$inserted = true;
			}
		}
		if ( ! $inserted ) {
			$out[] = $authority;
		}
	}

	$content['blocks'] = $out;

	// Conversion polish: tighten hero microcopy toward demo CTA.
	foreach ( $content['blocks'] as $i => $block ) {
		if ( ( $block['type'] ?? '' ) !== 'hero' ) {
			continue;
		}
		$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];
		$micro = trim( (string) ( $props['cta_microcopy'] ?? '' ) );
		if ( $micro === '' || stripos( $micro, 'under 5' ) !== false || stripos( $micro, '5 min' ) !== false ) {
			$props['cta_microcopy'] = __( 'No signup required · Takes about 2 minutes.', 'jcp-core' );
			$content['blocks'][ $i ]['props'] = $props;
		}
		break;
	}

	return $content;
}

/**
 * Ensure campaign landing authority uses the scoreboard variant.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_campaign_authority( array $content, int $post_id ): array {
	if ( ! function_exists( 'jcp_page_is_campaign_landing' ) || ! jcp_page_is_campaign_landing( $content ) ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) ) {
		return $content;
	}

	$canonical = jcp_page_authority_leadsforward_props();
	$changed   = false;
	foreach ( $blocks as $i => $block ) {
		if ( ( $block['type'] ?? '' ) !== 'authority' ) {
			continue;
		}
		$props                   = is_array( $block['props'] ?? null ) ? $block['props'] : [];
		$props['variant']        = 'scoreboard';
		$props['stats']          = $canonical['stats'];
		$props['eyebrow']       = $canonical['eyebrow'];
		$props['headline']      = $canonical['headline'];
		$props['body']          = $canonical['body'];
		$props['show_stats']    = true;
		$props['show_cta']      = true;
		$props['show_eyebrow']  = true;
		$props['show_headline'] = true;
		$props['show_body']     = true;
		if ( empty( $props['cta_primary']['label'] ) ) {
			$props['cta_primary'] = [
				'label' => __( 'See JobCapturePro On My Business', 'jcp-core' ),
				'url'   => '/demo/',
			];
		}
		if ( trim( (string) ( $props['cta_note'] ?? '' ) ) === '' ) {
			$props['cta_note'] = __( 'Free personalized demo · Work email required · No credit card', 'jcp-core' );
		}
		$blocks[ $i ]['props'] = $props;
		$changed               = true;
		break;
	}

	if ( ! $changed ) {
		return $content;
	}

	$content['blocks'] = $blocks;
	return $content;
}

