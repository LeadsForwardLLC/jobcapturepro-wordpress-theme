<?php
/**
 * Industry page media + text block slots and one-time upgrades.
 *
 * @package JCP_Core
 */

/**
 * Preset media + text slots (inserted after anchor block types).
 *
 * @return array<int, array<string, string>>
 */
function jcp_page_industry_media_slots(): array {
	return [
		[
			'id'         => 'b-media-text-core',
			'legacy_key' => 'media_text',
			'after'      => 'core_mechanic',
		],
		[
			'id'         => 'b-media-text-check-ins',
			'legacy_key' => 'media_text_check_ins',
			'after'      => 'check_ins',
		],
		[
			'id'         => 'b-media-text-problem',
			'legacy_key' => 'media_text_problem',
			'after'      => 'problem',
		],
	];
}

/**
 * Default props for an industry media slot.
 *
 * @param string $page_key   Page slug (hvac, plumbing, etc.).
 * @param string $legacy_key Legacy flat key.
 * @return array<string, mixed>
 */
function jcp_page_industry_media_props_for( string $page_key, string $legacy_key ): array {
	$trade = ucfirst( str_replace( '-', ' ', $page_key ) );
	$all   = [
		'hvac' => [
			'media_text' => [
				'headline'       => __( 'Your crew already takes the photos — now they work for you', 'jcp-core' ),
				'subheadline'    => __( 'Turn everyday HVAC job activity into proof homeowners trust.', 'jcp-core' ),
				'body'           => __( 'Technicians snap a few photos in under 30 seconds. JobCapturePro handles the check-in, publishing, and review follow-up across Google, your website, and social — without adding marketing work to the day.', 'jcp-core' ),
				'media_type'     => 'image',
				'media_url'      => '',
				'media_alt'      => __( 'HVAC technician capturing job photos on site', 'jcp-core' ),
				'media_position' => 'right',
				'cta'            => [ 'label' => __( 'See how it works', 'jcp-core' ), 'url' => '#how-it-works' ],
			],
			'media_text_check_ins' => [
				'headline'       => __( 'Every service call becomes a check-in customers can find', 'jcp-core' ),
				'subheadline'    => '',
				'body'           => __( 'Geo-tagged job photos, before-and-after shots, and AI-written summaries — tied to real addresses and published where local customers search. Each check-in strengthens your listing and builds trust before the phone rings.', 'jcp-core' ),
				'media_type'     => 'image',
				'media_url'      => '',
				'media_alt'      => __( 'HVAC check-in with before and after photos', 'jcp-core' ),
				'media_position' => 'left',
				'cta'            => [ 'label' => '', 'url' => '' ],
			],
			'media_text_problem' => [
				'headline'       => __( 'You\'re doing the work — customers just can\'t see it', 'jcp-core' ),
				'subheadline'    => '',
				'body'           => __( 'Photos stay on phones, posting gets pushed off, and reviews are missed. Meanwhile competitors look more active online. JobCapturePro closes that visibility gap so completed repairs and installs keep working for you after the truck leaves.', 'jcp-core' ),
				'media_type'     => 'image',
				'media_url'      => '',
				'media_alt'      => __( 'Busy HVAC company losing online visibility', 'jcp-core' ),
				'media_position' => 'right',
				'cta'            => [ 'label' => '', 'url' => '' ],
			],
		],
		'plumbing' => [
			'media_text' => [
				'headline'       => __( 'Every completed plumbing job should bring in the next call', 'jcp-core' ),
				'subheadline'    => __( 'Turn real service calls into proof homeowners trust before they dial.', 'jcp-core' ),
				'body'           => __( 'Your plumbers already take job photos. JobCapturePro turns those into check-ins, Google updates, website proof, and review requests — without adding admin work to the office or the truck.', 'jcp-core' ),
				'media_type'     => 'image',
				'media_url'      => '',
				'media_alt'      => __( 'Plumber capturing job photos on site', 'jcp-core' ),
				'media_position' => 'right',
				'cta'            => [ 'label' => __( 'See how it works', 'jcp-core' ), 'url' => '#how-it-works' ],
			],
			'media_text_check_ins' => [
				'headline'       => __( 'Real jobs, real addresses, real proof online', 'jcp-core' ),
				'subheadline'    => '',
				'body'           => __( 'Each check-in combines geo-tagged photos, a clear job summary, and a permanent record of the work — published where local homeowners search for a plumber they can trust.', 'jcp-core' ),
				'media_type'     => 'image',
				'media_url'      => '',
				'media_alt'      => __( 'Plumbing check-in with before and after photos', 'jcp-core' ),
				'media_position' => 'left',
				'cta'            => [ 'label' => '', 'url' => '' ],
			],
			'media_text_problem' => [
				'headline'       => __( 'Busy crews. Invisible online presence.', 'jcp-core' ),
				'subheadline'    => '',
				'body'           => __( 'Emergency calls get handled, repairs get done, and photos stay on phones. Marketing slips, reviews get missed, and competitors look more active. JobCapturePro keeps your completed work visible after the job is done.', 'jcp-core' ),
				'media_type'     => 'image',
				'media_url'      => '',
				'media_alt'      => __( 'Plumbing company missing online visibility', 'jcp-core' ),
				'media_position' => 'right',
				'cta'            => [ 'label' => '', 'url' => '' ],
			],
		],
	];

	$page_key = sanitize_title( $page_key );
	if ( isset( $all[ $page_key ][ $legacy_key ] ) ) {
		return $all[ $page_key ][ $legacy_key ];
	}

	return [
		'headline'       => sprintf(
			/* translators: %s: trade or industry name */
			__( 'Show %s customers the work you already do', 'jcp-core' ),
			$trade
		),
		'subheadline'    => '',
		'body'           => __( 'Add a photo of your crew on the job, a before-and-after, or a short walkthrough video. JobCapturePro publishes proof across Google, your website, and social automatically.', 'jcp-core' ),
		'media_type'     => 'image',
		'media_url'      => '',
		'media_alt'      => sprintf(
			/* translators: %s: trade or industry name */
			__( '%s crew on a completed job', 'jcp-core' ),
			$trade
		),
		'media_position' => $legacy_key === 'media_text_check_ins' ? 'left' : 'right',
		'cta'            => [ 'label' => '', 'url' => '' ],
	];
}

/**
 * Insert missing industry media + text blocks into an existing page document.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_industry_media_blocks( array $content, int $post_id ): array {
	if ( ( $content['page_kind'] ?? '' ) !== 'industry' ) {
		return $content;
	}

	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) || empty( $blocks ) ) {
		return $content;
	}

	$page_key = (string) ( $content['page_key'] ?? get_post_field( 'post_name', $post_id ) );
	$changed  = false;

	foreach ( jcp_page_industry_media_slots() as $slot ) {
		$legacy_key = (string) $slot['legacy_key'];
		$after_type = (string) $slot['after'];

		$exists = false;
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) || ( $block['type'] ?? '' ) !== 'media_text' ) {
				continue;
			}
			$key = (string) ( $block['legacy_key'] ?? 'media_text' );
			if ( $key === $legacy_key ) {
				$exists = true;
				break;
			}
		}
		if ( $exists ) {
			continue;
		}

		$insert_at = null;
		for ( $i = count( $blocks ) - 1; $i >= 0; $i-- ) {
			if ( ( $blocks[ $i ]['type'] ?? '' ) === $after_type ) {
				$insert_at = $i + 1;
				break;
			}
		}
		if ( $insert_at === null ) {
			continue;
		}

		$blocks[] = null;
		$new_block = [
			'id'         => (string) $slot['id'],
			'type'       => 'media_text',
			'legacy_key' => $legacy_key,
			'layout'     => jcp_block_default_layout( 'media_text', 'industry' ),
			'props'      => jcp_page_industry_media_props_for( $page_key, $legacy_key ),
		];
		array_splice( $blocks, $insert_at, 0, [ $new_block ] );
		$changed = true;
	}

	if ( $changed ) {
		$content['blocks'] = $blocks;
	}

	return $content;
}
