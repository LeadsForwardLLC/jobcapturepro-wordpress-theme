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
			'description'  => __( 'Home or Industries parent + page title (rendered in hero)', 'jcp-core' ),
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
			'description'  => __( 'Split row — badge, headline, body, optional CTA, image / video / phone mockup', 'jcp-core' ),
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
			'label'        => __( 'Stat row', 'jcp-core' ),
			'description'  => __( 'Badge stats row (e.g. 1 photo · 4 channels · 0 busywork)', 'jcp-core' ),
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
			'doc_sections' => [ 'DEMO PREVIEW' ],
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
			'doc_sections' => [ 'PROOF FLOW' ],
			'page_kinds'   => [ 'home', 'marketing' ],
		],
		'testimonials' => [
			'type'         => 'testimonials',
			'label'        => __( 'Testimonials', 'jcp-core' ),
			'description'  => __( 'Featured customer quote + slider of supporting reviews', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'testimonials',
			'doc_sections' => [ 'TESTIMONIALS' ],
			'page_kinds'   => [ 'home', 'marketing' ],
		],
		'authority' => [
			'type'         => 'authority',
			'label'        => __( 'Authority', 'jcp-core' ),
			'description'  => __( 'Credibility band — builder story, proof stats, CTA', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'authority',
			'doc_sections' => [ 'AUTHORITY', 'BUILT BY' ],
			'page_kinds'   => [ 'marketing', 'home' ],
		],
		'local_falcon_proof' => [
			'type'         => 'local_falcon_proof',
			'label'        => __( 'Local Falcon proof', 'jcp-core' ),
			'description'  => __( 'Anonymous Maps SoLV before/after grids', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'local_falcon_proof',
			'doc_sections' => [ 'LOCAL FALCON', 'MAPS PROOF' ],
			'page_kinds'   => [ 'home', 'marketing' ],
		],
		'problem' => [
			'type'         => 'problem',
			'label'        => __( 'Problem', 'jcp-core' ),
			'description'  => __( 'Pain point cards', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'problem',
			'doc_sections' => [ 'PROBLEM' ],
			'page_kinds'   => [ 'industry', 'marketing', 'home' ],
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
			'doc_sections' => [ 'DIRECTORY PREVIEW' ],
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
		'form_embed' => [
			'type'         => 'form_embed',
			'label'        => __( 'Form embed', 'jcp-core' ),
			'description'  => __( 'Fluent Forms shortcode — inline or takeover modal', 'jcp-core' ),
			'category'     => 'cta',
			'legacy_key'   => 'form_embed',
			'doc_sections' => [ 'FORM EMBED', 'FORM' ],
			'page_kinds'   => [ 'marketing', 'industry', 'referral', 'home' ],
		],
		'code_embed' => [
			'type'         => 'code_embed',
			'label'        => __( 'Code / Embed', 'jcp-core' ),
			'description'  => __( 'Shortcode or allowlisted iframe (Calendly, Cal.com, etc.)', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'code_embed',
			'doc_sections' => [ 'CODE EMBED', 'EMBED' ],
			'page_kinds'   => [ 'marketing', 'industry', 'referral', 'home' ],
		],
		'cta_band' => [
			'type'         => 'cta_band',
			'label'        => __( 'CTA band', 'jcp-core' ),
			'description'  => __( 'Mid-page CTA strip', 'jcp-core' ),
			'category'     => 'cta',
			'legacy_key'   => 'cta_band_1',
			'doc_sections' => [ 'CTA BAND' ],
			'page_kinds'   => [ 'referral', 'marketing' ],
		],
		'commission' => [
			'type'         => 'commission',
			'label'        => __( 'Commission table', 'jcp-core' ),
			'description'  => __( 'Referral commission tiers', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'commission',
			'doc_sections' => [ 'COMMISSION' ],
			'page_kinds'   => [ 'referral' ],
		],
		'partners' => [
			'type'         => 'partners',
			'label'        => __( 'Partners', 'jcp-core' ),
			'description'  => __( 'Partner types grid', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'partners',
			'doc_sections' => [ 'PARTNERS' ],
			'page_kinds'   => [ 'referral' ],
		],
		'share' => [
			'type'         => 'share',
			'label'        => __( 'Share', 'jcp-core' ),
			'description'  => __( 'Share / link copy section', 'jcp-core' ),
			'category'     => 'content',
			'legacy_key'   => 'share',
			'doc_sections' => [ 'SHARE' ],
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
			'show_subheadline'    => true,
			'cta_primary'         => [ 'label' => __( 'View the live demo', 'jcp-core' ), 'url' => '/demo' ],
			'cta_secondary'       => [ 'label' => __( 'See how it works', 'jcp-core' ), 'url' => '#how-it-works' ],
			'trust_line'          => __( 'No credit card · Start for free · Setup in under 10 minutes', 'jcp-core' ),
			'show_cta_primary'    => true,
			'show_cta_secondary'  => true,
			'show_trust_line'     => true,
			'show_visual'         => true,
			'media_type'          => 'image',
			'media_position'      => 'right',
			'image_url'           => '',
			'media_url'           => '',
			'media_alt'           => '',
			'image_attachment_id' => 0,
			'media_attachment_id' => 0,
			'phone_image_url'     => '',
			'phone_image_alt'     => '',
			'phone_cta_label'     => __( 'Try the demo', 'jcp-core' ),
			'media_link_url'      => '',
		],
		'media_text' => [
			'badge'              => '',
			'headline'           => __( 'Section headline', 'jcp-core' ),
			'subheadline'        => '',
			'cue'                => '',
			'body'               => __( 'Supporting copy for this section.', 'jcp-core' ),
			'cta_primary'        => [ 'label' => '', 'url' => '' ],
			'cta_note'           => '',
			'media_type'         => 'image',
			'media_url'          => '',
			'media_alt'          => '',
			'media_position'     => 'right',
			'phone_mockup_style' => 'app_shell',
			'show_badge'         => false,
			'show_subheadline'   => true,
			'show_cue'           => false,
			'show_body'          => true,
			'show_cta'           => false,
			'show_cta_note'      => false,
			'show_divider'       => false,
		],
		'benefits' => [
			'headline'         => __( 'Section headline', 'jcp-core' ),
			'show_icons'       => true,
			'show_headline'    => true,
			'show_subheadline' => true,
			'show_closing'     => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
			'show_card_stats'  => true,
			'cta_primary'      => [ 'label' => '', 'url' => '' ],
			'cta_secondary'    => [ 'label' => '', 'url' => '' ],
			'items'            => [],
		],
		'what_it_is' => [
			'headline'           => __( 'Section headline', 'jcp-core' ),
			'subheadline'        => '',
			'show_icons'         => true,
			'show_headline'      => true,
			'show_subheadline'   => true,
			'show_closing'       => true,
			'show_card_titles'   => true,
			'show_card_body'     => true,
			'team_already_icon'  => 'wrench',
			'turns_into_icon'    => 'sparkles',
			'team_already_lead'  => '',
			'lead'               => '',
			'cta_primary'        => [ 'label' => '', 'url' => '' ],
			'cta_secondary'      => [ 'label' => '', 'url' => '' ],
		],
		'how_it_works' => [
			'headline'    => __( 'How it works', 'jcp-core' ),
			'subheadline' => '',
			'show_headline' => true,
			'show_subheadline' => true,
			'show_steps' => true,
			'cta_primary' => [ 'label' => '', 'url' => '/demo' ],
			'cta_secondary' => [ 'label' => '', 'url' => '#how-it-works' ],
			'steps'       => [],
		],
		'check_ins' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
			'show_icons'  => true,
			'show_headline' => true,
			'show_subheadline' => true,
			'show_tags' => true,
			'show_card_titles' => true,
			'show_card_body' => true,
			'cta_primary' => [ 'label' => '', 'url' => '' ],
			'features'    => [],
		],
		'problem' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
			'show_icons'  => true,
			'show_headline' => true,
			'show_subheadline' => true,
			'show_closing' => true,
			'show_card_titles' => true,
			'show_card_body' => true,
			'cta_primary' => [ 'label' => '', 'url' => '' ],
			'pain_points' => [],
		],
		'differentiation' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'show_icons'  => true,
			'show_headline' => true,
			'show_subheadline' => true,
			'show_cta'    => false,
			'body'        => '',
			'bullets'     => [],
			'cta_primary' => [ 'label' => '', 'url' => '' ],
		],
		'who_its_for' => [
			'headline'         => __( 'Who it\'s for', 'jcp-core' ),
			'show_icons'       => true,
			'show_headline'    => true,
			'show_subheadline' => true,
			'show_cards'       => true,
			'show_card_titles' => true,
			'show_card_body'   => true,
			'show_card_stats'  => true,
			'show_card_images' => true,
			'show_card_badges' => true,
			'show_cta'         => false,
			'audiences'        => [],
			'cta_primary'      => [ 'label' => '', 'url' => '' ],
		],
		'faq' => [
			'headline'         => __( 'Frequently asked questions', 'jcp-core' ),
			'show_headline'    => true,
			'show_subheadline' => true,
			'show_items'       => true,
			'show_cta'         => false,
			'items'            => [],
			'cta_primary'      => [ 'label' => '', 'url' => '' ],
		],
		'final_cta' => [
			'headline'         => __( 'Ready to get started?', 'jcp-core' ),
			'subheadline'      => '',
			'cta_primary'      => [ 'label' => __( 'Start for free', 'jcp-core' ), 'url' => '' ],
			'cta_secondary'    => [ 'label' => __( 'See how it works', 'jcp-core' ), 'url' => '/demo' ],
			'cta_note'         => __( 'No credit card required', 'jcp-core' ),
			'show_subheadline' => true,
			'show_cta_note'    => true,
		],
		'form_embed' => [
			'headline'         => __( 'Apply for a spot', 'jcp-core' ),
			'subheadline'      => __( 'Tell us about your company. We’ll confirm fit and get you set up.', 'jcp-core' ),
			'shortcode'        => '',
			'display'          => 'inline',
			'show_headline'    => true,
			'show_subheadline' => true,
		],
		'code_embed' => [
			'headline'         => __( 'Book a time', 'jcp-core' ),
			'subheadline'      => '',
			'embed_code'       => '',
			'show_headline'    => true,
			'show_subheadline' => false,
		],
		'cta_band' => [
			'cta_primary' => [ 'label' => __( 'Start for free', 'jcp-core' ), 'url' => '' ],
			'band_key'    => 'cta_band_1',
		],
		'proof_flow' => [
			'headline'    => __( 'Section headline', 'jcp-core' ),
			'subheadline' => '',
			'items'       => [],
		],
		'testimonials' => [
			'eyebrow'          => __( 'Customer stories', 'jcp-core' ),
			'headline'         => __( 'Trusted by contractors who already take the photos', 'jcp-core' ),
			'subheadline'      => __( 'Real operators and agencies using JobCapturePro to turn completed jobs into visibility, content, and reviews.', 'jcp-core' ),
			'reviews'          => function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [],
			'featured_key'     => 'peter-bonk',
			'autoplay'         => true,
			'autoplay_ms'      => 6000,
			'show_stars'       => true,
			'show_roles'       => true,
			'show_eyebrow'     => true,
			'show_headline'    => true,
			'show_subheadline' => true,
			'section_id'       => 'testimonials',
		],
		'authority' => [
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
			'cta_primary'   => [ 'label' => __( 'View the live demo', 'jcp-core' ), 'url' => '/demo/' ],
			'cta_note'      => __( 'No signup required · Takes about 2 minutes', 'jcp-core' ),
			'section_id'    => 'built-by-leadsforward',
			'show_eyebrow'  => true,
			'show_headline' => true,
			'show_body'     => true,
			'show_stats'    => true,
			'show_cta'      => true,
			'variant'       => 'scoreboard',
		],
		'local_falcon_proof' => [
			'eyebrow'       => __( 'Maps proof', 'jcp-core' ),
			'headline'      => __( 'Same crews. Same jobs. Wildly different Maps footprint.', 'jcp-core' ),
			'subheadline'   => __( 'Anonymous basement waterproofing company · 111 locations · Local Falcon Share of Local Voice (how often they show in the Google 3-Pack across the grid).', 'jcp-core' ),
			'markets'       => [],
			'disclaimer'    => __( 'This isn’t a ranking guarantee. It’s what consistent, real job proof can do to local Maps presence.', 'jcp-core' ),
			'cta_primary'   => [ 'label' => __( 'Launch interactive demo', 'jcp-core' ), 'url' => '/demo/' ],
			'cta_secondary' => [ 'label' => __( 'Start free', 'jcp-core' ), 'url' => '' ],
			'section_id'    => 'maps-proof',
			'show_eyebrow'  => true,
			'show_headline' => true,
			'show_cta'      => true,
		],
		'demo_preview' => [
			'badge'           => __( 'Live Demo', 'jcp-core' ),
			'headline'        => __( 'See it in action', 'jcp-core' ),
			'body'            => '',
			'cta_primary'     => [ 'label' => __( 'Launch Interactive Demo', 'jcp-core' ), 'url' => '/demo' ],
			'cta_note'        => __( 'No signup required • Takes 2 minutes', 'jcp-core' ),
			'media_type'      => 'phone_mockup',
			'media_position'  => 'right',
			'show_headline'   => true,
			'show_body'       => true,
			'show_cta'        => true,
			'show_cta_note'   => true,
		],
		'directory_preview' => [
			'headline' => __( 'Section headline', 'jcp-core' ),
			'cards'    => [],
		],
		'conversion' => [
			'headline'            => __( 'Section headline', 'jcp-core' ),
			'show_icons'          => true,
			'points'              => [],
			'media_type'          => 'image',
			'media_position'      => 'right',
			'image_url'           => '',
			'image_alt'           => '',
			'image_attachment_id' => 0,
			'media_url'           => '',
		],
		'core_mechanic' => [
			[
				'value'  => '1',
				'label'  => 'photo',
				'detail' => __( 'Proof created instantly', 'jcp-core' ),
			],
			[
				'value'  => '4',
				'label'  => 'channels',
				'detail' => __( 'website, Google, social + directory', 'jcp-core' ),
			],
			[
				'value'  => '0',
				'label'  => 'busywork',
				'detail' => __( 'Nothing new for your crew', 'jcp-core' ),
			],
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
