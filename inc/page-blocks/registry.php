<?php
/**
 * JCP page block registry.
 *
 * @package JCP_Core
 */

/**
 * All registered block types.
 *
 * @return array<string, array<string, mixed>>
 */
function jcp_block_registry(): array {
	return [
		'breadcrumb' => [
			'type'         => 'breadcrumb',
			'label'        => __( 'Breadcrumb', 'jcp-core' ),
			'description'  => __( 'Industries hub breadcrumb', 'jcp-core' ),
			'category'     => 'nav',
			'legacy_key'   => null,
			'doc_sections' => [],
			'page_kinds'   => [ 'industry' ],
		],
		'hero' => [
			'type'         => 'hero',
			'label'        => __( 'Hero', 'jcp-core' ),
			'description'  => __( 'H1, subheadline, CTAs, trust line', 'jcp-core' ),
			'category'     => 'header',
			'legacy_key'   => 'hero',
			'doc_sections' => [ 'HERO' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral', 'home' ],
		],
		'media_text' => [
			'type'         => 'media_text',
			'label'        => __( 'Media + text', 'jcp-core' ),
			'description'  => __( 'Split row — copy on one side, image or video on the other', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'media_text',
			'doc_sections' => [],
			'page_kinds'   => [ 'industry', 'marketing', 'referral', 'home' ],
		],
		'what_it_is' => [
			'type'         => 'what_it_is',
			'label'        => __( 'What it is', 'jcp-core' ),
			'description'  => __( 'Intro with checklist columns', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'what_it_is',
			'doc_sections' => [ 'WHAT IT IS' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral' ],
		],
		'core_mechanic' => [
			'type'         => 'core_mechanic',
			'label'        => __( 'Core mechanic', 'jcp-core' ),
			'description'  => __( 'Stat strip (1 photo / 4 channels)', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'core_mechanic',
			'doc_sections' => [ 'CORE MECHANIC' ],
			'page_kinds'   => [ 'industry', 'marketing', 'home' ],
		],
		'demo_preview' => [
			'type'         => 'demo_preview',
			'label'        => __( 'Demo preview', 'jcp-core' ),
			'description'  => __( 'Live demo CTA with interactive phone mockup', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'demo_preview',
			'doc_sections' => [],
			'page_kinds'   => [ 'home', 'marketing' ],
		],
		'how_it_works' => [
			'type'         => 'how_it_works',
			'label'        => __( 'How it works', 'jcp-core' ),
			'description'  => __( 'Numbered timeline steps', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'how_it_works',
			'doc_sections' => [ 'HOW IT WORKS' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral', 'home' ],
		],
		'check_ins' => [
			'type'         => 'check_ins',
			'label'        => __( 'Check-ins / features', 'jcp-core' ),
			'description'  => __( 'Feature cards grid', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'check_ins',
			'doc_sections' => [ 'CHECK-INS' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral' ],
		],
		'proof_flow' => [
			'type'         => 'proof_flow',
			'label'        => __( 'Proof flow', 'jcp-core' ),
			'description'  => __( 'Channel flow — one job to Google, web, social, reviews', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'proof_flow',
			'doc_sections' => [],
			'page_kinds'   => [ 'home', 'marketing' ],
		],
		'problem' => [
			'type'         => 'problem',
			'label'        => __( 'Problem', 'jcp-core' ),
			'description'  => __( 'Pain point cards', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'problem',
			'doc_sections' => [ 'PROBLEM' ],
			'page_kinds'   => [ 'industry', 'marketing' ],
		],
		'benefits' => [
			'type'         => 'benefits',
			'label'        => __( 'Benefits', 'jcp-core' ),
			'description'  => __( 'Benefit cards', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'benefits',
			'doc_sections' => [ 'BENEFITS' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral', 'home' ],
		],
		'differentiation' => [
			'type'         => 'differentiation',
			'label'        => __( 'Differentiation', 'jcp-core' ),
			'description'  => __( 'Body copy and bullets', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'differentiation',
			'doc_sections' => [ 'DIFFERENTIATION' ],
			'page_kinds'   => [ 'industry', 'marketing' ],
		],
		'who_its_for' => [
			'type'         => 'who_its_for',
			'label'        => __( 'Who it\'s for', 'jcp-core' ),
			'description'  => __( 'Audience cards', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'who_its_for',
			'doc_sections' => [ "WHO IT'S FOR", 'WHO ITS FOR' ],
			'page_kinds'   => [ 'industry', 'marketing', 'home' ],
		],
		'directory_preview' => [
			'type'         => 'directory_preview',
			'label'        => __( 'Directory preview', 'jcp-core' ),
			'description'  => __( 'Sample directory listing cards', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'directory_preview',
			'doc_sections' => [],
			'page_kinds'   => [ 'home', 'marketing' ],
		],
		'faq' => [
			'type'         => 'faq',
			'label'        => __( 'FAQ', 'jcp-core' ),
			'description'  => __( 'Questions and answers', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'faq',
			'doc_sections' => [ 'FAQ' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral', 'home' ],
		],
		'conversion' => [
			'type'         => 'conversion',
			'label'        => __( 'Conversion', 'jcp-core' ),
			'description'  => __( 'Checklist + image conversion band', 'jcp-core' ),
			'category'     => 'cta',
			'legacy_key'   => 'conversion',
			'doc_sections' => [ 'CONVERSION' ],
			'page_kinds'   => [ 'home', 'marketing', 'industry' ],
		],
		'final_cta' => [
			'type'         => 'final_cta',
			'label'        => __( 'Final CTA', 'jcp-core' ),
			'description'  => __( 'Bottom conversion band', 'jcp-core' ),
			'category'     => 'cta',
			'legacy_key'   => 'final_cta',
			'doc_sections' => [ 'FINAL CTA' ],
			'page_kinds'   => [ 'industry', 'marketing', 'referral', 'home' ],
		],
		'cta_band' => [
			'type'         => 'cta_band',
			'label'        => __( 'CTA band', 'jcp-core' ),
			'description'  => __( 'Mid-page CTA strip', 'jcp-core' ),
			'category'     => 'cta',
			'legacy_key'   => 'cta_band_1',
			'doc_sections' => [],
			'page_kinds'   => [ 'referral', 'marketing' ],
		],
		'commission' => [
			'type'         => 'commission',
			'label'        => __( 'Commission table', 'jcp-core' ),
			'description'  => __( 'Referral commission tiers', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'commission',
			'doc_sections' => [],
			'page_kinds'   => [ 'referral' ],
		],
		'partners' => [
			'type'         => 'partners',
			'label'        => __( 'Partners', 'jcp-core' ),
			'description'  => __( 'Partner types grid', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'partners',
			'doc_sections' => [],
			'page_kinds'   => [ 'referral' ],
		],
		'share' => [
			'type'         => 'share',
			'label'        => __( 'Share', 'jcp-core' ),
			'description'  => __( 'Share / link copy section', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'share',
			'doc_sections' => [],
			'page_kinds'   => [ 'referral' ],
		],
	];
}

/**
 * @param string $type Block type.
 * @return array<string, mixed>|null
 */
function jcp_block_get( string $type ): ?array {
	$registry = jcp_block_registry();
	return $registry[ $type ] ?? null;
}

/**
 * Block types allowed for a page kind.
 *
 * @param string $page_kind industry|marketing|referral.
 * @return array<int, array<string, mixed>>
 */
function jcp_block_types_for_kind( string $page_kind ): array {
	$out = [];
	foreach ( jcp_block_registry() as $block ) {
		$kinds = $block['page_kinds'] ?? [];
		if ( in_array( $page_kind, $kinds, true ) ) {
			$out[] = $block;
		}
	}
	return $out;
}

/**
 * Registry entries safe for REST / editor (no PHP callbacks).
 *
 * @param string $page_kind Optional filter.
 * @return array<int, array<string, mixed>>
 */
function jcp_block_registry_public( string $page_kind = '' ): array {
	$out = [];
	foreach ( jcp_block_registry() as $block ) {
		if ( $page_kind !== '' ) {
			$kinds = $block['page_kinds'] ?? [];
			if ( $kinds && ! in_array( $page_kind, $kinds, true ) ) {
				continue;
			}
		}
		$out[] = [
			'type'           => $block['type'],
			'label'          => $block['label'],
			'description'    => $block['description'],
			'category'       => $block['category'],
			'doc_sections'   => $block['doc_sections'] ?? [],
			'layout_options' => jcp_block_layout_options( (string) $block['type'] ),
		];
	}
	return $out;
}

/**
 * Default props when inserting a new block in the editor.
 *
 * @param string $type Block type.
 * @return array<string, mixed>
 */
function jcp_page_default_block_props( string $type ): array {
	$defaults = [
		'hero' => [
			'h1'                  => __( 'Page headline', 'jcp-core' ),
			'subheadline'         => '',
			'cta_primary'         => [ 'label' => __( 'Start free trial', 'jcp-core' ), 'url' => '' ],
			'cta_secondary'       => [ 'label' => __( 'See how it works', 'jcp-core' ), 'url' => '#how-it-works' ],
			'trust_line'          => '',
			'media_type'          => 'phone_mockup',
			'media_position'      => 'right',
			'media_url'           => '',
			'media_alt'           => '',
			'media_attachment_id' => 0,
			'phone_image_url'     => '',
			'phone_image_alt'     => '',
		],
		'media_text' => [
			'headline'        => __( 'Section headline', 'jcp-core' ),
			'subheadline'     => '',
			'body'            => __( 'Supporting copy for this section.', 'jcp-core' ),
			'media_type'      => 'image',
			'media_url'       => '',
			'media_alt'       => '',
			'media_position'  => 'right',
			'cta'             => [ 'label' => '', 'url' => '' ],
		],
		'what_it_is' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
		],
		'how_it_works' => [
			'headline'    => __( 'How it works', 'jcp-core' ),
			'subheadline' => '',
			'cta_label'   => __( 'See it in action', 'jcp-core' ),
			'cta_url'     => '/demo',
			'steps'       => [],
		],
		'check_ins' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
			'features'    => [],
		],
		'problem' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
			'pain_points' => [],
		],
		'benefits' => [
			'headline' => __( 'Section headline', 'jcp-core' ),
			'items'    => [],
		],
		'differentiation' => [
			'headline' => __( 'Section headline', 'jcp-core' ),
			'body'     => '',
			'bullets'  => [],
		],
		'who_its_for' => [
			'headline'  => __( 'Who it\'s for', 'jcp-core' ),
			'audiences' => [],
		],
		'faq' => [
			'headline' => __( 'Frequently asked questions', 'jcp-core' ),
			'items'    => [],
		],
		'final_cta' => [
			'headline'    => __( 'Ready to get started?', 'jcp-core' ),
			'subheadline' => '',
			'cta_primary' => [ 'label' => __( 'Start free trial', 'jcp-core' ), 'url' => '' ],
			'cta_secondary' => [ 'label' => __( 'See how it works', 'jcp-core' ), 'url' => '/demo' ],
		],
		'cta_band' => [
			'cta_primary' => [ 'label' => __( 'Get started', 'jcp-core' ), 'url' => '' ],
			'band_key'    => 'cta_band_1',
		],
		'proof_flow' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
			'items'       => [],
		],
		'demo_preview' => [
			'badge'           => __( 'Live Demo', 'jcp-core' ),
			'headline'        => __( 'See it in action', 'jcp-core' ),
			'body'            => '',
			'cta_primary'     => [ 'label' => __( 'Launch Interactive Demo', 'jcp-core' ), 'url' => '/demo' ],
			'media_type'      => 'phone_mockup',
			'media_position'  => 'right',
		],
		'directory_preview' => [
			'headline' => __( 'Section headline', 'jcp-core' ),
			'cards'    => [],
		],
		'conversion' => [
			'headline'            => __( 'Section headline', 'jcp-core' ),
			'points'              => [],
			'media_type'          => 'image',
			'media_position'      => 'right',
			'image_url'           => '',
			'image_alt'           => '',
			'image_attachment_id' => 0,
			'media_url'           => '',
		],
	];
	return $defaults[ $type ] ?? [];
}

/**
 * Create a new block array for the editor.
 *
 * @param string $type Block type.
 * @return array<string, mixed>
 */
function jcp_page_new_block( string $type, string $page_kind = 'industry' ): array {
	return [
		'id'     => 'b-' . sanitize_title( $type ) . '-' . wp_generate_password( 6, false, false ),
		'type'   => $type,
		'layout' => jcp_block_default_layout( $type, $page_kind ),
		'props'  => jcp_page_default_block_props( $type ),
	];
}

/**
 * Map doc section header to block type.
 *
 * @param string $section Section header.
 */
function jcp_block_type_from_doc_section( string $section ): ?string {
	$upper = strtoupper( str_replace( '’', "'", trim( $section ) ) );
	foreach ( jcp_block_registry() as $block ) {
		$sections = $block['doc_sections'] ?? [];
		foreach ( $sections as $doc ) {
			if ( strtoupper( $doc ) === $upper ) {
				return (string) $block['type'];
			}
		}
	}
	return null;
}
