<?php
/**
 * JCP page content schema, storage, and legacy adapters.
 *
 * @package JCP_Core
 */

/**
 * Canonical meta key for block page content.
 */
function jcp_page_content_meta_key(): string {
	return '_jcp_page_content';
}

/**
 * Legacy meta key (industry pages).
 */
function jcp_page_legacy_meta_key(): string {
	return '_jcp_niche_content';
}

/**
 * @deprecated Use jcp_page_legacy_meta_key().
 */
function jcp_niche_content_meta_key(): string {
	return jcp_page_legacy_meta_key();
}

/**
 * Page templates that use the JCP block page system.
 *
 * @return array<int, string>
 */
function jcp_page_block_page_templates(): array {
	return [
		'page-jcp-blocks.php',
		'page-referral-program.php',
		'page-home.php',
	];
}

/**
 * Whether a WordPress page uses a JCP block page template.
 *
 * @param int $post_id Post ID.
 */
function jcp_page_uses_block_template( int $post_id ): bool {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return false;
	}
	return in_array( get_page_template_slug( $post_id ), jcp_page_block_page_templates(), true );
}

/**
 * Whether post uses structured JCP page content.
 *
 * @param int|null $post_id Post ID.
 */
function jcp_page_is_content_page( ?int $post_id = null ): bool {
	if ( is_post_type_archive( 'jcp_niche_landing' ) ) {
		return true;
	}
	$id = $post_id ?? ( is_singular() ? (int) get_queried_object_id() : 0 );
	if ( $id <= 0 ) {
		return false;
	}
	$post = get_post( $id );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( in_array( $post->post_type, [ 'jcp_niche_landing' ], true ) ) {
		return true;
	}
	if ( $post->post_type === 'page' ) {
		if ( jcp_page_uses_block_template( $id ) ) {
			return true;
		}
		if ( get_page_template_slug( $id ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === $id ) {
			return (bool) get_post_meta( $id, jcp_page_content_meta_key(), true );
		}
	}
	return jcp_page_uses_block_template( $id );
}

/**
 * @deprecated Use jcp_page_is_content_page().
 *
 * @param int|null $post_id Post ID.
 */
function jcp_niche_is_content_page( ?int $post_id = null ): bool {
	return jcp_page_is_content_page( $post_id );
}

/**
 * Infer page kind from stored content or post type.
 *
 * @param array<string, mixed> $content Content.
 * @param int                  $post_id Post ID.
 */
function jcp_page_resolve_kind( array $content, int $post_id ): string {
	if ( ! empty( $content['page_kind'] ) ) {
		$kind = sanitize_key( (string) $content['page_kind'] );
		if ( in_array( $kind, [ 'industry', 'marketing', 'referral', 'home' ], true ) ) {
			return $kind;
		}
	}

	$preset_slug = ! empty( $content['preset'] ) ? sanitize_key( (string) $content['preset'] ) : '';
	if ( $preset_slug === '' && $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			$preset_slug = jcp_writer_resolve_preset( $post, $content );
		}
	}
	if ( $preset_slug !== '' ) {
		$preset_def = jcp_page_get_preset( $preset_slug );
		if ( $preset_def && ! empty( $preset_def['page_kind'] ) ) {
			return (string) $preset_def['page_kind'];
		}
	}

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			if ( $post->post_type === 'jcp_niche_landing' ) {
				return 'industry';
			}
			if ( $post->post_type === 'page' ) {
				if ( get_page_template_slug( $post_id ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
					return 'referral';
				}
				if ( get_page_template_slug( $post_id ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === $post_id ) {
					return 'home';
				}
				if ( jcp_page_uses_block_template( $post_id ) ) {
					return 'marketing';
				}
			}
		}
	}
	if ( ( $content['page_type'] ?? '' ) === 'home' || ( $content['page_type'] ?? '' ) === 'homepage' ) {
		return 'home';
	}
	if ( ( $content['page_type'] ?? '' ) === 'referral' ) {
		return 'referral';
	}
	if ( $post_id <= 0 ) {
		return 'industry';
	}
	return 'marketing';
}

/**
 * Load preset JSON file (industry, referral, etc.).
 *
 * @param string $preset Preset slug.
 * @return array<string, mixed>
 */
function jcp_page_load_preset( string $preset ): array {
	$path = get_template_directory() . '/inc/niche-landing/dummy-' . sanitize_file_name( $preset ) . '.json';
	if ( ! is_readable( $path ) ) {
		return [];
	}
	$raw = file_get_contents( $path );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return [];
	}
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : [];
}

/**
 * @deprecated Use jcp_page_load_preset().
 *
 * @param string $preset Preset slug.
 * @return array<string, mixed>
 */
function jcp_niche_load_preset( string $preset ): array {
	return jcp_page_load_preset( $preset );
}

/**
 * Default preset content when post has no saved meta.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_default_content( int $post_id ): array {
	$slug = get_post_field( 'post_name', $post_id );
	if ( $slug === 'plumbing' ) {
		return jcp_page_load_preset( 'plumbing' );
	}
	if ( $slug === 'hvac' ) {
		return jcp_page_load_preset( 'hvac' );
	}
	if ( $slug === 'referral-program' || get_page_template_slug( $post_id ) === 'page-referral-program.php' ) {
		return jcp_page_load_preset( 'referral-program' );
	}
	if ( get_page_template_slug( $post_id ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === $post_id ) {
		return jcp_page_load_preset( 'home' );
	}
	return [];
}

/**
 * Read raw stored content (blocks or legacy flat).
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_get_content_raw( int $post_id ): array {
	foreach ( [ jcp_page_content_meta_key(), jcp_page_legacy_meta_key() ] as $key ) {
		$raw = get_post_meta( $post_id, $key, true );
		if ( is_string( $raw ) && $raw !== '' ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) && ! empty( $decoded ) ) {
				return $decoded;
			}
		}
	}
	return jcp_page_default_content( $post_id );
}

/**
 * Drop null or invalid entries from a blocks list (editor crashes on null blocks).
 *
 * @param array<int, mixed> $blocks Raw blocks.
 * @return array<int, array<string, mixed>>
 */
function jcp_page_sanitize_blocks_list( array $blocks ): array {
	return array_values(
		array_filter(
			$blocks,
			static function ( $block ): bool {
				return is_array( $block ) && ! empty( $block['type'] );
			}
		)
	);
}

/**
 * Normalize content to blocks format (in memory).
 *
 * @param array<string, mixed> $content Raw content.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_normalize_content( array $content, int $post_id ): array {
	if ( ! empty( $content['blocks'] ) && is_array( $content['blocks'] ) ) {
		$content['blocks']    = jcp_page_sanitize_blocks_list( $content['blocks'] );
		$content['version']   = $content['version'] ?? 1;
		$content['page_kind'] = jcp_page_resolve_kind( $content, $post_id );
		if ( empty( $content['page_key'] ) ) {
			$content['page_key'] = get_post_field( 'post_name', $post_id );
		}
		if ( empty( $content['page_label'] ) && empty( $content['niche_label'] ) ) {
			$content['page_label'] = get_the_title( $post_id );
		}
		return $content;
	}
	return jcp_page_legacy_to_blocks( $content, $post_id );
}

/**
 * Get normalized block content.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_get_content( int $post_id ): array {
	$raw = jcp_page_get_content_raw( $post_id );
	if ( empty( $raw ) ) {
		return [
			'version'    => 1,
			'page_kind'  => jcp_page_resolve_kind( [], $post_id ),
			'page_key'   => get_post_field( 'post_name', $post_id ),
			'page_label' => get_the_title( $post_id ),
			'blocks'     => [],
		];
	}
	$content = jcp_page_normalize_content( $raw, $post_id );
	$cleaned = jcp_page_sanitize_content_document( $content );
	$upgraded = jcp_page_upgrade_industry_media_blocks( $cleaned, $post_id );
	$upgraded = jcp_page_upgrade_embedded_demo_blocks( $upgraded, $post_id );
	if ( wp_json_encode( $upgraded ) !== wp_json_encode( $cleaned ) ) {
		jcp_page_save_content( $post_id, $upgraded );
		$cleaned = $upgraded;
	} elseif ( wp_json_encode( $cleaned ) !== wp_json_encode( $content ) ) {
		jcp_page_save_content( $post_id, $cleaned );
	}
	return $cleaned;
}

/**
 * Flat legacy content for render + inline editor paths (hero.h1, etc.).
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_get_content_flat( int $post_id ): array {
	return jcp_page_blocks_to_legacy( jcp_page_get_content( $post_id ) );
}

/**
 * @deprecated Use jcp_page_get_content_flat().
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_niche_get_content( int $post_id ): array {
	return jcp_page_get_content_flat( $post_id );
}

/**
 * Save content (accepts blocks or legacy; stores blocks format).
 *
 * @param int                  $post_id Post ID.
 * @param array<string, mixed> $content Content.
 */
function jcp_page_save_content( int $post_id, array $content ): void {
	if ( empty( $content['blocks'] ) ) {
		$content = jcp_page_normalize_content( $content, $post_id );
	}
	$content = jcp_page_sanitize_content_document( $content );
	update_post_meta(
		$post_id,
		jcp_page_content_meta_key(),
		wp_json_encode( $content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
	);
	// Remove legacy key once upgraded.
	delete_post_meta( $post_id, jcp_page_legacy_meta_key() );
}

/**
 * @deprecated Use jcp_page_save_content().
 *
 * @param int                  $post_id Post ID.
 * @param array<string, mixed> $content Content.
 */
function jcp_niche_save_content( int $post_id, array $content ): void {
	jcp_page_save_content( $post_id, $content );
}

/**
 * Convert legacy flat JSON to blocks array.
 *
 * @param array<string, mixed> $legacy Legacy content.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_legacy_to_blocks( array $legacy, int $post_id ): array {
	$page_kind = jcp_page_resolve_kind( $legacy, $post_id );
	$preset    = ! empty( $legacy['preset'] ) ? sanitize_key( (string) $legacy['preset'] ) : match ( $page_kind ) {
		'referral' => 'referral',
		'industry' => 'industry',
		'home'     => 'home',
		default    => 'marketing',
	};
	if ( ! jcp_page_get_preset( $preset ) ) {
		$preset = match ( $page_kind ) {
			'referral' => 'referral',
			'industry' => 'industry',
			'home'     => 'home',
			default    => 'marketing',
		};
	}
	$preset_def = jcp_page_get_preset( $preset );
	$order     = $preset_def['block_types'] ?? [];

	$blocks = [];
	foreach ( $order as $entry ) {
		$parsed = jcp_page_parse_preset_block_entry( $entry );
		$type   = $parsed['type'];
		if ( $type === '' ) {
			continue;
		}
		$def = jcp_block_get( $type );
		if ( ! $def ) {
			continue;
		}
		$key = $parsed['legacy_key'] !== '' ? $parsed['legacy_key'] : ( $def['legacy_key'] ?? null );
		if ( $type === 'breadcrumb' ) {
			if ( ! empty( $legacy['hide_breadcrumb'] ) ) {
				continue;
			}
			$blocks[] = [
				'id'     => $parsed['id'],
				'type'   => 'breadcrumb',
				'layout' => jcp_block_default_layout( 'breadcrumb', $page_kind ),
				'props'  => [],
			];
			continue;
		}
		if ( $key === null ) {
			continue;
		}
		$props = $legacy[ $key ] ?? null;
		if ( $props === null || $props === [] || $props === '' ) {
			continue;
		}
		if ( $type === 'hero' && is_array( $props ) ) {
			$block_layout = jcp_block_default_layout( (string) $type, $page_kind );
			if ( isset( $props['show_visual'] ) && empty( $props['show_visual'] ) ) {
				$block_layout['hero_variant'] = 'centered';
			}
			if ( ! empty( $props['rotating_words'] ) ) {
				$block_layout['hero_variant'] = 'home';
			}
		} else {
			$block_layout = jcp_block_default_layout( (string) $type, $page_kind );
		}
		$block = [
			'id'     => $parsed['id'],
			'type'   => (string) $type,
			'layout' => $block_layout,
			'props'  => is_array( $props ) ? $props : [ 'value' => $props ],
		];
		if ( $parsed['legacy_key'] !== '' ) {
			$block['legacy_key'] = $parsed['legacy_key'];
		}
		$blocks[] = $block;
	}

	return [
		'version'         => 1,
		'page_kind'       => $page_kind,
		'page_key'        => ! empty( $legacy['niche_key'] ) ? (string) $legacy['niche_key'] : ( ! empty( $legacy['page_key'] ) ? (string) $legacy['page_key'] : ( $post_id > 0 ? (string) get_post_field( 'post_name', $post_id ) : '' ) ),
		'page_label'      => ! empty( $legacy['niche_label'] ) ? (string) $legacy['niche_label'] : ( ! empty( $legacy['page_label'] ) ? (string) $legacy['page_label'] : ( $post_id > 0 ? (string) get_the_title( $post_id ) : '' ) ),
		'preset'          => $legacy['preset'] ?? $preset,
		'seo'             => $legacy['seo'] ?? [ 'keywords' => [] ],
		'settings'        => [
			'hide_breadcrumb' => ! empty( $legacy['hide_breadcrumb'] ),
		],
		'blocks'          => $blocks,
		'page_type'       => $legacy['page_type'] ?? ( $page_kind === 'referral' ? 'referral' : '' ),
		'nav_cta'         => is_array( $legacy['nav_cta'] ?? null ) ? $legacy['nav_cta'] : [],
	];
}

/**
 * Rebuild legacy flat content from blocks (for render + editor).
 *
 * @param array<string, mixed> $content Block content.
 * @return array<string, mixed>
 */
function jcp_page_blocks_to_legacy( array $content ): array {
	$legacy = [
		'niche_key'        => $content['page_key'] ?? '',
		'niche_label'      => $content['page_label'] ?? '',
		'page_key'         => $content['page_key'] ?? '',
		'page_label'       => $content['page_label'] ?? '',
		'page_type'        => $content['page_type'] ?? ( ( $content['page_kind'] ?? '' ) === 'referral' ? 'referral' : '' ),
		'page_kind'        => $content['page_kind'] ?? 'marketing',
		'preset'           => $content['preset'] ?? '',
		'seo'              => $content['seo'] ?? [ 'keywords' => [] ],
		'hide_breadcrumb'  => ! empty( $content['settings']['hide_breadcrumb'] ),
	];
	if ( ! empty( $content['nav_cta'] ) && is_array( $content['nav_cta'] ) ) {
		$legacy['nav_cta'] = $content['nav_cta'];
	}

	foreach ( (array) ( $content['blocks'] ?? [] ) as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type  = (string) ( $block['type'] ?? '' );
		$props = $block['props'] ?? [];
		if ( ! is_array( $props ) ) {
			$props = [];
		}
		$def = jcp_block_get( $type );
		if ( ! $def ) {
			continue;
		}
		if ( $type === 'breadcrumb' ) {
			continue;
		}
		$key = ! empty( $block['legacy_key'] )
			? (string) $block['legacy_key']
			: ( $def['legacy_key'] ?? $type );
		if ( $key ) {
			$legacy[ $key ] = $props;
		}
	}

	return $legacy;
}

/**
 * Merge parsed document content with existing page content.
 *
 * @param array<string, mixed> $parsed   Parsed document.
 * @param array<string, mixed> $existing Existing content.
 * @return array<string, mixed>
 */
function jcp_page_merge_parsed_content( array $parsed, array $existing = [] ): array {
	if ( empty( $existing ) ) {
		return $parsed;
	}
	$flat     = jcp_page_blocks_to_legacy( jcp_page_normalize_content( $existing, 0 ) );
	$existing = ! empty( $existing['blocks'] ) ? jcp_page_blocks_to_legacy( $existing ) : $existing;

	foreach ( [ 'hero', 'final_cta' ] as $section ) {
		if ( empty( $parsed['blocks'] ) ) {
			break;
		}
		foreach ( $parsed['blocks'] as $i => $block ) {
			if ( ( $block['type'] ?? '' ) !== $section ) {
				continue;
			}
			$props = $block['props'] ?? [];
			$exist = $flat[ $section ] ?? [];
			foreach ( [ 'cta_primary', 'cta_secondary' ] as $cta_key ) {
				if ( empty( $props[ $cta_key ]['url'] ) && ! empty( $exist[ $cta_key ]['url'] ) ) {
					$parsed['blocks'][ $i ]['props'][ $cta_key ]['url'] = $exist[ $cta_key ]['url'];
				}
			}
			if ( $section === 'how_it_works' && empty( $props['cta_url'] ) && ! empty( $exist['cta_url'] ) ) {
				$parsed['blocks'][ $i ]['props']['cta_url'] = $exist['cta_url'];
			}
		}
	}

	return $parsed;
}

/**
 * Merge imported blocks into an existing skeleton document.
 *
 * Keeps empty section slots from the skeleton while filling props from import.
 *
 * @param array<string, mixed> $parsed   Parsed import document.
 * @param array<string, mixed> $existing Existing stored content.
 * @return array<string, mixed>
 */
function jcp_page_merge_import_content( array $parsed, array $existing = [] ): array {
	$parsed = jcp_page_merge_parsed_content( $parsed, $existing );

	if ( empty( $existing['blocks'] ) || empty( $parsed['blocks'] ) ) {
		return $parsed;
	}

	$imported_by_key = [];
	foreach ( (array) $parsed['blocks'] as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		$key  = $type;
		if ( ! empty( $block['legacy_key'] ) ) {
			$key .= ':' . (string) $block['legacy_key'];
		}
		$imported_by_key[ $key ] = $block;
	}

	$merged_blocks = [];
	foreach ( (array) $existing['blocks'] as $skeleton ) {
		if ( ! is_array( $skeleton ) ) {
			continue;
		}
		$type = (string) ( $skeleton['type'] ?? '' );
		$key  = $type;
		if ( ! empty( $skeleton['legacy_key'] ) ) {
			$key .= ':' . (string) $skeleton['legacy_key'];
		}
		if ( isset( $imported_by_key[ $key ] ) ) {
			$hit = $imported_by_key[ $key ];
			unset( $imported_by_key[ $key ] );
			$skeleton['props']  = $hit['props'] ?? $skeleton['props'] ?? [];
			$skeleton['layout'] = $hit['layout'] ?? $skeleton['layout'] ?? [];
			if ( ! empty( $hit['label'] ) ) {
				$skeleton['label'] = $hit['label'];
			}
		}
		$merged_blocks[] = $skeleton;
	}

	foreach ( $imported_by_key as $block ) {
		$merged_blocks[] = $block;
	}

	$parsed['blocks'] = $merged_blocks;
	if ( ! empty( $existing['preset'] ) && empty( $parsed['preset'] ) ) {
		$parsed['preset'] = $existing['preset'];
	}

	return $parsed;
}

/**
 * @deprecated Use jcp_page_merge_parsed_content().
 *
 * @param array<string, mixed> $parsed Parsed.
 * @param array<string, mixed> $existing Existing.
 * @return array<string, mixed>
 */
function jcp_niche_merge_parsed_content( array $parsed, array $existing = [] ): array {
	return jcp_page_merge_parsed_content( $parsed, $existing );
}

/**
 * Whether an array is a sequential list (0..n-1 keys).
 *
 * @param array<mixed> $array Array.
 */
function jcp_is_sequential_array( array $array ): bool {
	if ( [] === $array ) {
		return true;
	}
	return array_keys( $array ) === range( 0, count( $array ) - 1 );
}

/**
 * Deep-merge block props: replace list arrays entirely so deletions persist.
 *
 * @param array<string, mixed> $base    Existing props.
 * @param array<string, mixed> $overlay Flat editor values.
 * @return array<string, mixed>
 */
function jcp_page_merge_props_deep( array $base, array $overlay ): array {
	foreach ( $overlay as $key => $value ) {
		if ( is_array( $value ) && jcp_is_sequential_array( $value ) ) {
			$base[ $key ] = $value;
			continue;
		}
		if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) && ! jcp_is_sequential_array( $value ) ) {
			$base[ $key ] = jcp_page_merge_props_deep( $base[ $key ], $value );
			continue;
		}
		$base[ $key ] = $value;
	}
	return $base;
}

/**
 * Merge flat legacy content (hero.h1 paths) into a blocks document before save.
 *
 * @param array<string, mixed> $doc  Blocks document.
 * @param array<string, mixed> $flat Flat legacy content from inline editor.
 * @return array<string, mixed>
 */
function jcp_page_merge_flat_into_blocks( array $doc, array $flat ): array {
	if ( empty( $doc['blocks'] ) || ! is_array( $doc['blocks'] ) ) {
		return jcp_page_normalize_content( $flat, 0 );
	}

	foreach ( $doc['blocks'] as $i => $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		$def  = jcp_block_get( $type );
		if ( ! $def ) {
			continue;
		}
		$key = ! empty( $block['legacy_key'] )
			? (string) $block['legacy_key']
			: ( $def['legacy_key'] ?? $type );
		if ( $key && isset( $flat[ $key ] ) && is_array( $flat[ $key ] ) ) {
			// Stat row props are the array itself — replace wholesale so add/remove persist.
			if ( $type === 'core_mechanic' ) {
				$doc['blocks'][ $i ]['props'] = array_values( $flat[ $key ] );
			} else {
				$doc['blocks'][ $i ]['props'] = jcp_page_merge_props_deep(
					$block['props'] ?? [],
					$flat[ $key ]
				);
			}
		}
	}

	if ( ! empty( $flat['niche_key'] ) ) {
		$doc['page_key'] = (string) $flat['niche_key'];
	}
	if ( ! empty( $flat['niche_label'] ) ) {
		$doc['page_label'] = (string) $flat['niche_label'];
	}
	if ( isset( $flat['hide_breadcrumb'] ) ) {
		$doc['settings'] = $doc['settings'] ?? [];
		$doc['settings']['hide_breadcrumb'] = (bool) $flat['hide_breadcrumb'];
	}
	if ( ! empty( $flat['seo'] ) && is_array( $flat['seo'] ) ) {
		$doc['seo'] = array_replace_recursive( $doc['seo'] ?? [], $flat['seo'] );
	}

	return $doc;
}
