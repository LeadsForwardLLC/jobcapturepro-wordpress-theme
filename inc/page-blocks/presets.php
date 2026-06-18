<?php
/**
 * Default block stacks for JCP page presets.
 *
 * @package JCP_Core
 */

/**
 * Preset definitions: block type order and page metadata defaults.
 *
 * @return array<string, array<string, mixed>>
 */
function jcp_page_presets(): array {
	return [
		'industry' => [
			'label'      => __( 'Industry trade page', 'jcp-core' ),
			'page_kind'  => 'industry',
			'block_types' => [
				'breadcrumb',
				'hero',
				'what_it_is',
				'core_mechanic',
				[
					'type'       => 'media_text',
					'id'         => 'b-media-text-core',
					'legacy_key' => 'media_text',
				],
				'how_it_works',
				'check_ins',
				[
					'type'       => 'media_text',
					'id'         => 'b-media-text-check-ins',
					'legacy_key' => 'media_text_check_ins',
				],
				'problem',
				[
					'type'       => 'media_text',
					'id'         => 'b-media-text-problem',
					'legacy_key' => 'media_text_problem',
				],
				'benefits',
				'differentiation',
				'who_its_for',
				'faq',
				'conversion',
				'final_cta',
			],
		],
		'referral' => [
			'label'      => __( 'Referral program', 'jcp-core' ),
			'page_kind'  => 'referral',
			'block_types' => [
				'hero',
				'what_it_is',
				'cta_band',
				'how_it_works',
				'check_ins',
				'benefits',
				'commission',
				'partners',
				'share',
				'faq',
				'final_cta',
			],
		],
		'minimal' => [
			'label'      => __( 'Minimal landing', 'jcp-core' ),
			'page_kind'  => 'marketing',
			'block_types' => [
				'hero',
				'benefits',
				'faq',
				'final_cta',
			],
		],
		'marketing' => [
			'label'      => __( 'Block page', 'jcp-core' ),
			'page_kind'  => 'marketing',
			'block_types' => [
				'hero',
				'what_it_is',
				'how_it_works',
				'benefits',
				'faq',
				'final_cta',
			],
		],
		'home' => [
			'label'       => __( 'Homepage', 'jcp-core' ),
			'page_kind'   => 'home',
			'block_types' => [
				'hero',
				'how_it_works',
				'proof_flow',
				'benefits',
				'who_its_for',
				'directory_preview',
				'faq',
				'conversion',
				'final_cta',
			],
		],
	];
}

/**
 * @param string $preset Preset slug.
 * @return array<string, mixed>|null
 */
function jcp_page_get_preset( string $preset ): ?array {
	$all = jcp_page_presets();
	return $all[ $preset ] ?? null;
}

/**
 * Build empty blocks array from a preset slug.
 *
 * @param string $preset Preset slug.
 * @return array<int, array<string, mixed>>
 */
function jcp_page_blocks_from_preset( string $preset ): array {
	$def = jcp_page_get_preset( $preset );
	if ( ! $def ) {
		return [];
	}
	$blocks = [];
	foreach ( (array) ( $def['block_types'] ?? [] ) as $entry ) {
		$parsed = jcp_page_parse_preset_block_entry( $entry );
		if ( $parsed['type'] === '' ) {
			continue;
		}
		$block = [
			'id'    => $parsed['id'],
			'type'  => $parsed['type'],
			'props' => [],
		];
		if ( $parsed['legacy_key'] !== '' ) {
			$block['legacy_key'] = $parsed['legacy_key'];
		}
		$blocks[] = $block;
	}
	return $blocks;
}

/**
 * Normalize a preset block entry (string type or array definition).
 *
 * @param string|array<string, string> $entry Preset block entry.
 * @return array{type: string, id: string, legacy_key: string}
 */
function jcp_page_parse_preset_block_entry( string|array $entry ): array {
	if ( is_string( $entry ) ) {
		$type = trim( $entry );
		return [
			'type'       => $type,
			'id'         => 'b-' . sanitize_title( $type ),
			'legacy_key' => '',
		];
	}

	$type       = trim( (string) ( $entry['type'] ?? '' ) );
	$legacy_key = trim( (string) ( $entry['legacy_key'] ?? '' ) );
	$id         = trim( (string) ( $entry['id'] ?? '' ) );
	if ( $id === '' ) {
		$id = 'b-' . sanitize_title( $legacy_key !== '' ? $legacy_key : $type );
	}

	return [
		'type'       => $type,
		'id'         => $id,
		'legacy_key' => $legacy_key,
	];
}
