<?php
/**
 * Paid campaign landing-page variants (message-matched Meta LPs).
 *
 * Reuses the contractor-demo campaign preset and overrides only angle-specific
 * copy. Does not modify /contractor-demo/.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Seed version for all paid LP variants (bump to force-refresh content). */
const JCP_CAMPAIGN_VARIANTS_SEED_VERSION = '2';

/**
 * Variant registry keyed by analytics id.
 *
 * @return array<string, array<string, mixed>>
 */
function jcp_campaign_variants(): array {
	$cta = [
		'label' => 'See It On My Business',
		'url'   => '/demo/',
	];
	$micro = 'Free personalized demo · About 2 minutes · No credit card';

	return [
		'formula'     => [
			'slug'         => 'contractor-formula',
			'post_title'   => 'The Contractor Marketing Formula Is Simpler Than You Think',
			'post_excerpt' => 'Your crew already creates the proof. See how one finished job becomes marketing with JobCapturePro.',
			'label'        => 'Formula',
			'seo'          => [
				'title'            => 'The Contractor Marketing Formula Is Simpler Than You Think | JobCapturePro',
				'meta_description' => 'Your crew already creates the proof. See how one completed job can become website content, Google activity, social proof and review opportunities with JobCapturePro.',
			],
			'hero'         => [
				'eyebrow'       => 'THE CONTRACTOR MARKETING FORMULA',
				'show_eyebrow'  => true,
				'h1'            => 'You Don’t Need Another Marketing Trick. You Need Proof.',
				'subheadline'   => 'Your crew already creates it on every finished job. JobCapturePro turns that real work into website proof, Google activity, social content and review opportunities — automatically.',
				'cta_primary'   => $cta,
				'trust_line'    => $micro,
				'cta_microcopy' => $micro,
			],
			'how_it_works' => [
				'headline'       => 'The Formula Is Actually Pretty Simple.',
				'subheadline'    => 'Finish the job. Capture the proof. Let JobCapturePro put it to work.',
				'numeric_steps'  => true,
				'show_cta'       => false,
				'section_id'     => 'the-formula',
				'closing'        => 'No 47 “near me”s required.',
				'steps'          => [
					[
						'title' => 'Your crew does the work',
						'lines' => [ 'They already complete jobs, take photos and create the proof homeowners care about.' ],
					],
					[
						'title' => 'JCP turns it into marketing',
						'lines' => [ 'Job details, photos and location become usable public-facing content.' ],
					],
					[
						'title' => 'The next customer can see it',
						'lines' => [ 'Website proof, Google activity, social content, review opportunities and local job evidence.' ],
					],
				],
			],
			'benefits'     => [
				'headline'    => 'One Finished Job Can Become More Than One Marketing Asset.',
				'subheadline' => 'Your tech captures the job once. JobCapturePro helps turn that work into proof across the channels homeowners actually use.',
				'cta_primary' => $cta,
				'cta_note'    => $micro,
			],
			'final_cta'    => [
				'headline'      => 'Forget the Formula. Put the Work You Already Did to Work.',
				'subheadline'   => 'See what one finished job could become for your business.',
				'cta_primary'   => $cta,
				'cta_note'      => $micro,
				'cta_footnote'  => '',
			],
			'funnel_order' => [
				'hero',
				'how_it_works',
				'core_mechanic',
				'benefits',
				'authority',
				'story_moments',
				'testimonials',
				'local_rank_case_study',
				'faq',
				'final_cta',
			],
		],
		'nature_doc'  => [
			'slug'         => 'contractor-nature',
			'post_title'   => 'Make Every Finished Job Keep Working',
			'post_excerpt' => 'See how finished-job proof keeps working after the crew leaves.',
			'label'        => 'Nature Doc',
			'seo'          => [
				'title'            => 'Make Every Finished Job Keep Working | JobCapturePro',
				'meta_description' => 'See how JobCapturePro turns completed-job photos, location and job details into website proof, Google activity, social content and review opportunities.',
			],
			'hero'         => [
				'eyebrow'       => 'THE JOB IS FINISHED. THE PROOF SHOULDN’T BE.',
				'show_eyebrow'  => true,
				'h1'            => 'Your Finished Jobs Should Keep Working After the Crew Leaves.',
				'subheadline'   => 'JobCapturePro turns the photos, location and job details your team already creates into website proof, Google activity, social content and review opportunities.',
				'cta_primary'   => $cta,
				'trust_line'    => $micro,
				'cta_microcopy' => $micro,
			],
			'problem'      => [
				'variant'           => 'contrast',
				'headline'          => 'Normally, This Is Where the Marketing Dies.',
				'subheadline'       => 'The job is done. The customer is happy. Someone took the photos. Then the truck leaves — and most of that proof disappears into a camera roll or internal system.',
				'show_subheadline'  => true,
				'contrast_without'  => [
					'label' => 'Without JobCapturePro',
					'steps' => [
						'Job finished',
						'Photos stored',
						'Proof disappears',
					],
				],
				'contrast_with'     => [
					'label'     => 'With JobCapturePro',
					'steps'     => [
						'Job finished',
						'Check-in created',
						'Proof published',
						'Review opportunity',
						'Work keeps working',
					],
					'loop_note' => 'One completed job can keep creating value long after your crew leaves.',
				],
				'section_id'        => 'where-marketing-dies',
			],
			'benefits'     => [
				'headline'    => 'One Job. Working in Several Places at Once.',
				'subheadline' => 'Capture the job once, then put that real-world proof across the places homeowners look before they call.',
				'items'       => [
					[
						'title'     => 'Website',
						'body'      => 'Real completed-job proof on your site.',
						'label'     => 'Website',
						'chrome'    => 'website',
						'image_url' => '__CAMPAIGN_ASSET__/jcp-campaign-job-proof.jpg',
						'image_alt' => 'Completed job as website proof',
					],
					[
						'title'     => 'Google',
						'body'      => 'Fresh job activity for your Business Profile.',
						'label'     => 'Google',
						'chrome'    => 'google',
						'image_url' => '__CAMPAIGN_ASSET__/jcp-campaign-job-proof.jpg',
						'image_alt' => 'Completed job for Google Business Profile',
					],
					[
						'title'     => 'Social',
						'body'      => 'Completed work becomes usable social content.',
						'label'     => 'Social',
						'chrome'    => 'social',
						'image_url' => '__CAMPAIGN_ASSET__/jcp-campaign-face-owner.jpg',
						'image_alt' => 'Social-ready job proof',
					],
					[
						'title'     => 'Directory',
						'body'      => 'Real job activity strengthens your JCP listing.',
						'label'     => 'Directory',
						'chrome'    => 'social',
						'image_url' => '__CAMPAIGN_ASSET__/jcp-campaign-face-owner.jpg',
						'image_alt' => 'Directory listing proof',
					],
					[
						'title'     => 'Reviews',
						'body'      => 'Ask while the customer experience is still fresh.',
						'label'     => 'Reviews',
						'chrome'    => 'reviews',
						'image_url' => '__CAMPAIGN_ASSET__/jcp-campaign-crew-review.jpg',
						'image_alt' => 'On-site review ask',
					],
				],
				'cta_primary' => $cta,
				'cta_note'    => $micro,
			],
			'authority'    => [
				'eyebrow'     => 'Built by LeadsForward',
				'headline'    => 'Built After a Decade of Helping Contractors Grow.',
				'body'        => 'We spent years helping home-service companies generate demand and kept seeing the same problem: great work happened every day, but the proof rarely made it online. JobCapturePro was built to close that gap.',
				'cta_primary' => $cta,
				'cta_note'    => $micro,
			],
			'final_cta'    => [
				'headline'     => 'Make the Next Job Start the Cycle.',
				'subheadline'  => 'Finish the work. Capture the proof. Let JobCapturePro help put it to work.',
				'cta_primary'  => $cta,
				'cta_note'     => $micro,
				'cta_footnote' => 'Then do it again on the next job.',
			],
			'funnel_order' => [
				'hero',
				'problem',
				'core_mechanic',
				'benefits',
				'authority',
				'story_moments',
				'testimonials',
				'local_rank_case_study',
				'faq',
				'final_cta',
			],
		],
		'proof_waste' => [
			'slug'         => 'job-proof',
			'post_title'   => 'Stop Wasting the Proof Your Jobs Already Create',
			'post_excerpt' => 'You already pay to create valuable job proof. See how JobCapturePro helps put it to work.',
			'label'        => 'Proof Waste',
			'copy_status'  => 'draft',
			'seo'          => [
				'title'            => 'Stop Wasting the Proof Your Jobs Already Create | JobCapturePro',
				'meta_description' => 'Contractors create valuable proof on every finished job — then often leave it trapped in phones and CRMs. See how JobCapturePro helps turn that work into public-facing marketing.',
			],
			'hero'         => [
				'eyebrow'       => 'YOU ALREADY PAID FOR THE PROOF',
				'show_eyebrow'  => true,
				'h1'            => 'Stop Paying Twice for Marketing You Already Created.',
				'subheadline'   => 'Your crew finishes jobs and takes the photos. Too often that proof stays trapped in camera rolls, CRMs, or internal folders — where the next customer never sees it. JobCapturePro helps turn completed-job proof into website content, Google activity, social posts, and review opportunities.',
				'cta_primary'   => $cta,
				'trust_line'    => $micro,
				'cta_microcopy' => $micro,
			],
			'problem'      => [
				'variant'          => 'contrast',
				'headline'         => 'The Work Happened. The Marketing Value Often Didn’t.',
				'subheadline'      => 'Every finished job creates photos, location, and job details homeowners care about. When that stays private, you paid for proof that never becomes public marketing.',
				'show_subheadline' => true,
				'contrast_without' => [
					'label' => 'Proof wasted',
					'steps' => [
						'Job finished',
						'Photos stored privately',
						'Next customer never sees it',
					],
				],
				'contrast_with'    => [
					'label'     => 'Proof put to work',
					'steps'     => [
						'Job finished',
						'Check-in created',
						'Public-facing proof',
						'Review opportunity',
					],
					'loop_note' => 'Same job. More useful places.',
				],
				'section_id'       => 'proof-waste',
			],
			'benefits'     => [
				'headline'    => 'Turn Completed Jobs Into Proof Homeowners Can Actually See.',
				'subheadline' => 'Capture the job once. JobCapturePro helps put that real work across the channels people check before they call.',
				'cta_primary' => $cta,
				'cta_note'    => $micro,
			],
			'final_cta'    => [
				'headline'     => 'Stop Leaving Finished-Job Proof On the Table.',
				'subheadline'  => 'See what one completed job could become for your business.',
				'cta_primary'  => $cta,
				'cta_note'     => $micro,
				'cta_footnote' => '',
			],
			'funnel_order' => [
				'hero',
				'problem',
				'core_mechanic',
				'benefits',
				'authority',
				'story_moments',
				'testimonials',
				'local_rank_case_study',
				'faq',
				'final_cta',
			],
		],
		'founder'     => [
			'slug'         => 'why-we-built-jcp',
			'post_title'   => 'Why We Built JobCapturePro',
			'post_excerpt' => 'Built by LeadsForward after years of helping contractors grow — and watching finished-job proof go unused.',
			'label'        => 'Founder',
			'copy_status'  => 'draft',
			'seo'          => [
				'title'            => 'Why We Built JobCapturePro | JobCapturePro',
				'meta_description' => 'LeadsForward built JobCapturePro after years of helping contractors grow — and repeatedly seeing completed-job proof fail to become public marketing.',
			],
			'hero'         => [
				'eyebrow'       => 'BUILT BY LEADSFORWARD',
				'show_eyebrow'  => true,
				'h1'            => 'We Built JobCapturePro Because Finished Jobs Kept Going Unused.',
				'subheadline'   => 'After years helping home-service companies grow, we kept seeing the same gap: contractors created valuable proof every day, but that proof rarely became public marketing. JobCapturePro was built to close it.',
				'cta_primary'   => $cta,
				'trust_line'    => $micro,
				'cta_microcopy' => $micro,
			],
			'how_it_works' => [
				'headline'      => 'The Pattern We Saw Over and Over.',
				'subheadline'   => 'Great work. Real photos. Then almost none of it showed up where the next homeowner looks.',
				'numeric_steps' => true,
				'show_cta'      => false,
				'section_id'    => 'why-we-built',
				'closing'       => 'So we built the system that turns completed jobs into usable public proof.',
				'steps'         => [
					[
						'title' => 'Contractors finish real jobs',
						'lines' => [ 'Crews create photos, location, and job details homeowners already trust.' ],
					],
					[
						'title' => 'The proof usually stays private',
						'lines' => [ 'Camera rolls, CRMs, and internal folders rarely become public marketing.' ],
					],
					[
						'title' => 'JCP was built to close the gap',
						'lines' => [ 'One check-in helps turn finished work into website proof, Google activity, social content, and review opportunities.' ],
					],
				],
			],
			'authority'    => [
				'eyebrow'     => 'Built by LeadsForward',
				'headline'    => 'A Decade Helping Contractors Grow — Then the Missing Piece.',
				'body'        => 'LeadsForward spent years helping contractors generate demand. JobCapturePro came from the recurring problem we could not ignore: completed jobs create proof, but that proof too often never becomes public-facing marketing.',
				'cta_primary' => $cta,
				'cta_note'    => $micro,
			],
			'benefits'     => [
				'headline'    => 'What JobCapturePro Is Built to Do.',
				'subheadline' => 'Help contractors turn work they already completed into proof homeowners can see — without inventing a new marketing process for the crew.',
				'cta_primary' => $cta,
				'cta_note'    => $micro,
			],
			'final_cta'    => [
				'headline'     => 'See the System We Built for That Gap.',
				'subheadline'  => 'A short personalized demo shows how one finished job can become public-facing proof.',
				'cta_primary'  => $cta,
				'cta_note'     => $micro,
				'cta_footnote' => '',
			],
			'funnel_order' => [
				'hero',
				'how_it_works',
				'authority',
				'core_mechanic',
				'benefits',
				'story_moments',
				'testimonials',
				'local_rank_case_study',
				'faq',
				'final_cta',
			],
		],
	];
}

/**
 * @param string $key Variant key.
 * @return array<string, mixed>|null
 */
function jcp_campaign_variant( string $key ): ?array {
	$all = jcp_campaign_variants();
	return isset( $all[ $key ] ) && is_array( $all[ $key ] ) ? $all[ $key ] : null;
}

/**
 * Deep-merge associative arrays; list values are replaced.
 *
 * @param array<string, mixed> $base Base.
 * @param array<string, mixed> $over Overrides.
 * @return array<string, mixed>
 */
function jcp_campaign_variant_deep_merge( array $base, array $over ): array {
	foreach ( $over as $key => $value ) {
		if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) && jcp_campaign_variant_is_assoc( $value ) && jcp_campaign_variant_is_assoc( $base[ $key ] ) ) {
			$base[ $key ] = jcp_campaign_variant_deep_merge( $base[ $key ], $value );
		} else {
			$base[ $key ] = $value;
		}
	}
	return $base;
}

/**
 * @param array<mixed> $arr Array.
 */
function jcp_campaign_variant_is_assoc( array $arr ): bool {
	if ( $arr === [] ) {
		return true;
	}
	return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
}

/**
 * Shared CTA rewrite so every primary CTA stays on /demo/.
 *
 * @param array<string, mixed> $legacy Legacy doc.
 * @param string               $variant_key Variant key.
 * @return array<string, mixed>
 */
function jcp_campaign_variant_normalize_ctas( array $legacy, string $variant_key ): array {
	$label = 'See It On My Business';
	$micro = 'Free personalized demo · About 2 minutes · No credit card';
	$url   = '/demo/?lp_variant=' . rawurlencode( $variant_key );
	jcp_campaign_variant_rewrite_ctas_node( $legacy, $label, $micro, $url );
	return $legacy;
}

/**
 * Recursively normalize CTA fields on a legacy content node.
 *
 * @param array<string, mixed> $node Node.
 * @param string               $label CTA label.
 * @param string               $micro Microcopy.
 * @param string               $url CTA URL.
 */
function jcp_campaign_variant_rewrite_ctas_node( array &$node, string $label, string $micro, string $url ): void {
	foreach ( [ 'cta_primary', 'cta_secondary' ] as $cta_key ) {
		if ( ! isset( $node[ $cta_key ] ) || ! is_array( $node[ $cta_key ] ) ) {
			continue;
		}
		$cta_url = (string) ( $node[ $cta_key ]['url'] ?? '' );
		if ( $cta_url === '' || stripos( $cta_url, '/demo' ) === false ) {
			continue;
		}
		if ( $cta_key === 'cta_primary' ) {
			$node[ $cta_key ]['label'] = $label;
		}
		$node[ $cta_key ]['url'] = $url;
	}
	if ( array_key_exists( 'cta_note', $node ) ) {
		$node['cta_note'] = $micro;
	}
	if ( array_key_exists( 'trust_line', $node ) ) {
		$node['trust_line'] = $micro;
	}
	if ( array_key_exists( 'cta_microcopy', $node ) ) {
		$node['cta_microcopy'] = $micro;
	}
	foreach ( $node as &$child ) {
		if ( is_array( $child ) ) {
			jcp_campaign_variant_rewrite_ctas_node( $child, $label, $micro, $url );
		}
	}
	unset( $child );
}

/**
 * Ensure a legacy section exists as a block in the document.
 *
 * @param array<string, mixed> $doc    Block document.
 * @param array<string, mixed> $legacy Legacy content.
 * @param string               $type   Block type / legacy key.
 * @return array<string, mixed>
 */
function jcp_campaign_variant_ensure_block_from_legacy( array $doc, array $legacy, string $type ): array {
	$props = $legacy[ $type ] ?? null;
	if ( ! is_array( $props ) || $props === [] ) {
		return $doc;
	}
	$blocks    = is_array( $doc['blocks'] ?? null ) ? $doc['blocks'] : [];
	$page_kind = (string) ( $doc['page_kind'] ?? 'marketing' );
	foreach ( $blocks as $i => $block ) {
		if ( ! is_array( $block ) || ( $block['type'] ?? '' ) !== $type ) {
			continue;
		}
		$blocks[ $i ]['props']      = $props;
		$blocks[ $i ]['legacy_key'] = $type;
		$doc['blocks']              = $blocks;
		return $doc;
	}
	$blocks[] = [
		'id'         => 'b-' . sanitize_title( $type ) . '-variant',
		'type'       => $type,
		'layout'     => function_exists( 'jcp_block_default_layout' ) ? jcp_block_default_layout( $type, $page_kind ) : [],
		'props'      => $props,
		'legacy_key' => $type,
	];
	$doc['blocks'] = $blocks;
	return $doc;
}

/**
 * Build a campaign block document for a paid LP variant.
 *
 * @param string $variant_key Variant key.
 * @return array<string, mixed>
 */
function jcp_campaign_variant_document( string $variant_key ): array {
	$variant = jcp_campaign_variant( $variant_key );
	if ( ! $variant ) {
		return [];
	}

	$legacy = function_exists( 'jcp_page_load_preset' ) ? jcp_page_load_preset( 'campaign' ) : [];
	if ( empty( $legacy ) ) {
		return [];
	}

	$legacy['niche_key']   = (string) ( $variant['slug'] ?? $variant_key );
	$legacy['niche_label'] = (string) ( $variant['label'] ?? $variant_key );
	$legacy['seo']         = array_merge( (array) ( $legacy['seo'] ?? [] ), (array) ( $variant['seo'] ?? [] ) );

	foreach ( [ 'hero', 'how_it_works', 'problem', 'benefits', 'authority', 'final_cta', 'story_moments' ] as $section ) {
		if ( empty( $variant[ $section ] ) || ! is_array( $variant[ $section ] ) ) {
			continue;
		}
		$base               = is_array( $legacy[ $section ] ?? null ) ? $legacy[ $section ] : [];
		$legacy[ $section ] = jcp_campaign_variant_deep_merge( $base, $variant[ $section ] );
	}

	$legacy = jcp_campaign_variant_normalize_ctas( $legacy, $variant_key );

	$asset_base = trailingslashit( get_template_directory_uri() ) . 'assets/campaign';
	$encoded    = wp_json_encode( $legacy );
	if ( is_string( $encoded ) && $encoded !== '' ) {
		$encoded = str_replace( '__CAMPAIGN_ASSET__', $asset_base, $encoded );
		$decoded = json_decode( $encoded, true );
		if ( is_array( $decoded ) ) {
			$legacy = $decoded;
		}
	}

	$doc = function_exists( 'jcp_page_legacy_to_blocks' ) ? jcp_page_legacy_to_blocks( $legacy, 0 ) : [];
	if ( empty( $doc ) ) {
		return [];
	}

	// Campaign preset omits how_it_works; inject when the variant funnel needs it.
	$doc = jcp_campaign_variant_ensure_block_from_legacy( $doc, $legacy, 'how_it_works' );

	if ( function_exists( 'jcp_page_finalize_campaign_document' ) ) {
		$doc = jcp_page_finalize_campaign_document( $doc );
	}

	$doc['page_kind'] = 'marketing';
	$doc['preset']    = 'campaign';
	$doc['settings']  = is_array( $doc['settings'] ?? null ) ? $doc['settings'] : [];
	$doc['settings']['campaign_landing'] = true;
	$doc['settings']['noindex']          = true;
	$doc['settings']['hide_site_chrome'] = true;
	$doc['settings']['hide_breadcrumb']  = true;
	$doc['settings']['campaign_variant'] = $variant_key;
	if ( ! empty( $variant['copy_status'] ) ) {
		$doc['settings']['copy_status'] = (string) $variant['copy_status'];
	}
	if ( ! empty( $variant['funnel_order'] ) && is_array( $variant['funnel_order'] ) ) {
		$doc['settings']['campaign_funnel_order'] = array_values( $variant['funnel_order'] );
	}

	$doc = jcp_campaign_variant_apply_funnel_order( $doc );
	return $doc;
}

/**
 * Reorder/filter blocks for a variant using settings.campaign_funnel_order.
 *
 * @param array<string, mixed> $doc Block document.
 * @return array<string, mixed>
 */
function jcp_campaign_variant_apply_funnel_order( array $doc ): array {
	$order = $doc['settings']['campaign_funnel_order'] ?? null;
	if ( ! is_array( $order ) || $order === [] ) {
		return $doc;
	}

	$blocks = $doc['blocks'] ?? [];
	if ( ! is_array( $blocks ) || $blocks === [] ) {
		return $doc;
	}

	$by_type = [];
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		if ( $type === '' || isset( $by_type[ $type ] ) ) {
			continue;
		}
		$by_type[ $type ] = $block;
	}

	$out = [];
	foreach ( $order as $type ) {
		$type = (string) $type;
		if ( isset( $by_type[ $type ] ) ) {
			$out[] = $by_type[ $type ];
			unset( $by_type[ $type ] );
		}
	}

	$doc['blocks'] = $out;
	return $doc;
}

/**
 * Create or refresh a single paid LP variant page.
 *
 * @param string $variant_key Variant key.
 * @param bool   $force_refresh Overwrite saved content.
 * @return int Post ID or 0.
 */
function jcp_campaign_variant_seed( string $variant_key, bool $force_refresh = false ): int {
	$variant = jcp_campaign_variant( $variant_key );
	if ( ! $variant ) {
		return 0;
	}
	$slug = sanitize_title( (string) ( $variant['slug'] ?? '' ) );
	if ( $slug === '' || $slug === 'contractor-demo' ) {
		return 0;
	}

	$doc = jcp_campaign_variant_document( $variant_key );
	if ( empty( $doc ) ) {
		return 0;
	}

	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$title    = (string) ( $variant['post_title'] ?? $variant['label'] ?? $slug );
	$excerpt  = (string) ( $variant['post_excerpt'] ?? '' );

	if ( $existing instanceof WP_Post ) {
		$id = (int) $existing->ID;
		if ( get_page_template_slug( $id ) !== 'page-jcp-blocks.php' ) {
			update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
		}
		$has_content = (string) get_post_meta( $id, jcp_page_content_meta_key(), true ) !== '';
		if ( $force_refresh || ! $has_content ) {
			jcp_page_save_content( $id, $doc );
			wp_update_post(
				[
					'ID'           => $id,
					'post_title'   => $title,
					'post_excerpt' => $excerpt,
				]
			);
		}
		update_post_meta( $id, '_jcp_campaign_variant', $variant_key );
		return $id;
	}

	$id = wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_excerpt' => $excerpt,
		],
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	$id = (int) $id;
	update_post_meta( $id, '_wp_page_template', 'page-jcp-blocks.php' );
	update_post_meta( $id, '_jcp_campaign_variant', $variant_key );
	jcp_page_save_content( $id, $doc );
	return $id;
}

/**
 * Seed all paid LP variants.
 *
 * @param bool $force_refresh Overwrite content.
 * @return array<string, int> Map of variant key => post ID.
 */
function jcp_campaign_variants_seed_all( bool $force_refresh = false ): array {
	$out = [];
	foreach ( array_keys( jcp_campaign_variants() ) as $key ) {
		$out[ $key ] = jcp_campaign_variant_seed( $key, $force_refresh );
	}
	return $out;
}

/**
 * Auto-seed paid LP variants when version bumps.
 */
function jcp_campaign_variants_maybe_seed(): void {
	$ver = (string) get_option( 'jcp_campaign_variants_seed_version', '' );
	if ( $ver === JCP_CAMPAIGN_VARIANTS_SEED_VERSION ) {
		// Still ensure pages exist.
		foreach ( jcp_campaign_variants() as $key => $variant ) {
			$slug = (string) ( $variant['slug'] ?? '' );
			if ( $slug !== '' && ! get_page_by_path( $slug, OBJECT, 'page' ) ) {
				jcp_campaign_variant_seed( $key, true );
			}
		}
		return;
	}
	jcp_campaign_variants_seed_all( true );
	update_option( 'jcp_campaign_variants_seed_version', JCP_CAMPAIGN_VARIANTS_SEED_VERSION, false );
}
add_action( 'init', 'jcp_campaign_variants_maybe_seed', 25 );

/**
 * Current page campaign variant key (empty for control /contractor-demo/).
 */
function jcp_campaign_current_variant_key(): string {
	if ( ! is_singular( 'page' ) ) {
		return '';
	}
	$post_id = (int) get_queried_object_id();
	if ( $post_id <= 0 ) {
		return '';
	}
	$meta = (string) get_post_meta( $post_id, '_jcp_campaign_variant', true );
	if ( $meta !== '' && jcp_campaign_variant( $meta ) ) {
		return $meta;
	}
	if ( function_exists( 'jcp_page_get_content' ) ) {
		$content = jcp_page_get_content( $post_id );
		$key     = (string) ( $content['settings']['campaign_variant'] ?? '' );
		if ( $key !== '' && jcp_campaign_variant( $key ) ) {
			return $key;
		}
	}
	return '';
}

/**
 * Body classes for paid LP variants.
 *
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function jcp_campaign_variant_body_class( array $classes ): array {
	$key = jcp_campaign_current_variant_key();
	if ( $key === '' ) {
		return $classes;
	}
	$classes[] = 'jcp-campaign-variant';
	$classes[] = 'jcp-campaign-variant-' . sanitize_html_class( $key );
	return $classes;
}
add_filter( 'body_class', 'jcp_campaign_variant_body_class' );

/**
 * Expose lp_variant on <body> for attribution JS.
 */
function jcp_campaign_variant_body_attr(): void {
	$key = jcp_campaign_current_variant_key();
	if ( $key === '' ) {
		return;
	}
	echo ' data-jcp-lp-variant="' . esc_attr( $key ) . '"';
}
// body_class filter cannot add attributes; hook via language_attributes on body is awkward.
// Use wp_body_open + inline script fallback is worse. Prefer filter on body_class is class-only.
// Render attribute via custom action used in header if present; otherwise inject via wp_footer bootstrap.

/**
 * Print a tiny bootstrap so attribution can read the variant even without body attr.
 */
function jcp_campaign_variant_bootstrap_script(): void {
	$key = jcp_campaign_current_variant_key();
	if ( $key === '' ) {
		return;
	}
	printf(
		'<script>document.documentElement.setAttribute("data-jcp-lp-variant",%s);if(document.body){document.body.setAttribute("data-jcp-lp-variant",%s);}</script>' . "\n",
		wp_json_encode( $key ),
		wp_json_encode( $key )
	);
}
add_action( 'wp_head', 'jcp_campaign_variant_bootstrap_script', 1 );

/**
 * Rank Math / document title for variants when Rank Math meta is empty.
 *
 * @param string $title Title.
 * @return string
 */
function jcp_campaign_variant_document_title( string $title ): string {
	$key = jcp_campaign_current_variant_key();
	if ( $key === '' ) {
		return $title;
	}
	$variant = jcp_campaign_variant( $key );
	$seo     = is_array( $variant['seo'] ?? null ) ? $variant['seo'] : [];
	$custom  = trim( (string) ( $seo['title'] ?? '' ) );
	return $custom !== '' ? $custom : $title;
}
add_filter( 'pre_get_document_title', 'jcp_campaign_variant_document_title', 20 );
add_filter( 'rank_math/frontend/title', 'jcp_campaign_variant_document_title', 20 );

/**
 * Meta description for variants (Rank Math filter when available).
 *
 * @param string $desc Description.
 * @return string
 */
function jcp_campaign_variant_meta_description( string $desc ): string {
	$key = jcp_campaign_current_variant_key();
	if ( $key === '' ) {
		return $desc;
	}
	$variant = jcp_campaign_variant( $key );
	$seo     = is_array( $variant['seo'] ?? null ) ? $variant['seo'] : [];
	$custom  = trim( (string) ( $seo['meta_description'] ?? '' ) );
	return $custom !== '' ? $custom : $desc;
}
add_filter( 'rank_math/frontend/description', 'jcp_campaign_variant_meta_description', 20 );
