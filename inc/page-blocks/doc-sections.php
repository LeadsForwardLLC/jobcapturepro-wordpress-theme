<?php
/**
 * Writer document section headers — registry, synonyms, import reports.
 *
 * @package JCP_Core
 */

/**
 * Optional media rows (not block types; map to media_text legacy keys).
 *
 * @return array<string, string> Canonical header => legacy_key.
 */
function jcp_page_doc_media_sections(): array {
	return [
		'MEDIA CORE'        => 'media_text',
		'MEDIA CHECK-INS'   => 'media_text_check_ins',
		'MEDIA PROBLEM'     => 'media_text_problem',
	];
}

/**
 * Friendly aliases writers may use — normalized to canonical ALL CAPS headers.
 *
 * @return array<string, string> Alias (uppercase) => canonical section.
 */
function jcp_page_doc_section_synonyms(): array {
	return [
		'STAT ROW'    => 'CORE MECHANIC',
		'STAT STRIP'  => 'CORE MECHANIC',
		'STATS'       => 'CORE MECHANIC',
		'STAT ROWS'   => 'CORE MECHANIC',
		'CHECK INS'   => 'CHECK-INS',
		'CHECKINS'    => 'CHECK-INS',
		'WHO ITS FOR' => "WHO IT'S FOR",
		'DEMO'        => 'DEMO PREVIEW',
		'DIRECTORY'   => 'DIRECTORY PREVIEW',
	];
}

/**
 * Normalize a line to uppercase header form for matching.
 *
 * @param string $line Raw line.
 */
function jcp_page_doc_normalize_header_line( string $line ): string {
	$upper = strtoupper( str_replace( '’', "'", trim( $line ) ) );
	$upper = preg_replace( '/[\s\-—–]+/u', ' ', $upper ) ?? $upper;
	return preg_replace( '/\s+/', ' ', trim( $upper ) );
}

/**
 * All canonical section headers the parser accepts.
 *
 * @return array<int, string>
 */
function jcp_page_doc_canonical_sections(): array {
	static $cache = null;
	if ( is_array( $cache ) ) {
		return $cache;
	}

	$sections = array_keys( jcp_page_doc_media_sections() );
	foreach ( jcp_block_registry() as $block ) {
		foreach ( (array) ( $block['doc_sections'] ?? [] ) as $doc ) {
			$norm = jcp_page_doc_normalize_header_line( (string) $doc );
			if ( $norm !== '' && ! in_array( $norm, $sections, true ) ) {
				$sections[] = $norm;
			}
		}
	}

	$cache = $sections;
	return $cache;
}

/**
 * All header strings that start a new section (canonical + synonyms).
 *
 * @return array<int, string>
 */
function jcp_page_doc_recognized_section_headers(): array {
	static $cache = null;
	if ( is_array( $cache ) ) {
		return $cache;
	}

	$headers = jcp_page_doc_canonical_sections();
	foreach ( array_keys( jcp_page_doc_section_synonyms() ) as $alias ) {
		if ( ! in_array( $alias, $headers, true ) ) {
			$headers[] = $alias;
		}
	}

	$cache = $headers;
	return $cache;
}

/**
 * Resolve a document line to a canonical section key, or null.
 *
 * @param string $line Raw line.
 */
function jcp_page_doc_normalize_section_header( string $line ): ?string {
	$upper = jcp_page_doc_normalize_header_line( $line );
	if ( $upper === '' ) {
		return null;
	}

	$synonyms = jcp_page_doc_section_synonyms();
	if ( isset( $synonyms[ $upper ] ) ) {
		$upper = $synonyms[ $upper ];
	}

	$canonical = jcp_page_doc_canonical_sections();
	if ( in_array( $upper, $canonical, true ) ) {
		return $upper;
	}

	return null;
}

/**
 * Legacy content keys populated by the document parser.
 *
 * @return array<string, string> legacy_key => canonical section header.
 */
function jcp_page_doc_legacy_key_map(): array {
	return [
		'hero'                  => 'HERO',
		'what_it_is'            => 'WHAT IT IS',
		'core_mechanic'         => 'CORE MECHANIC',
		'media_text'            => 'MEDIA CORE',
		'media_text_check_ins'  => 'MEDIA CHECK-INS',
		'media_text_problem'    => 'MEDIA PROBLEM',
		'how_it_works'          => 'HOW IT WORKS',
		'check_ins'             => 'CHECK-INS',
		'problem'               => 'PROBLEM',
		'benefits'              => 'BENEFITS',
		'differentiation'       => 'DIFFERENTIATION',
		'who_its_for'           => "WHO IT'S FOR",
		'faq'                   => 'FAQ',
		'conversion'            => 'CONVERSION',
		'final_cta'             => 'FINAL CTA',
		'demo_preview'          => 'DEMO PREVIEW',
		'proof_flow'            => 'PROOF FLOW',
		'directory_preview'     => 'DIRECTORY PREVIEW',
		'cta_band_1'            => 'CTA BAND',
		'commission'            => 'COMMISSION',
		'partners'              => 'PARTNERS',
		'share'                 => 'SHARE',
	];
}

/**
 * Whether parsed legacy content for a key is non-empty.
 *
 * @param mixed $value Legacy value.
 */
function jcp_page_doc_legacy_has_content( $value ): bool {
	if ( $value === null || $value === '' || $value === [] ) {
		return false;
	}
	if ( ! is_array( $value ) ) {
		return trim( (string) $value ) !== '';
	}
	foreach ( $value as $item ) {
		if ( is_array( $item ) ) {
			foreach ( $item as $part ) {
				if ( trim( (string) $part ) !== '' ) {
					return true;
				}
			}
		} elseif ( trim( (string) $item ) !== '' ) {
			return true;
		}
	}
	return false;
}

/**
 * Human label for a canonical section (for admin UI).
 *
 * @param string $section Canonical header.
 */
function jcp_page_doc_section_label( string $section ): string {
	$media = jcp_page_doc_media_sections();
	if ( isset( $media[ $section ] ) ) {
		return match ( $section ) {
			'MEDIA CORE'      => __( 'Media + text (after stat row)', 'jcp-core' ),
			'MEDIA CHECK-INS' => __( 'Media + text (after check-ins)', 'jcp-core' ),
			'MEDIA PROBLEM'   => __( 'Media + text (after problem)', 'jcp-core' ),
			default           => __( 'Media + text', 'jcp-core' ),
		};
	}

	$type = jcp_block_type_from_doc_section( $section );
	if ( $type ) {
		$def = jcp_block_get( $type );
		if ( $def && ! empty( $def['label'] ) ) {
			return (string) $def['label'];
		}
	}

	return $section;
}

/**
 * Section headers writers should use for a page kind (import guide).
 *
 * @param string $page_kind industry|marketing|referral|home.
 * @param string $preset    Optional layout preset slug.
 * @return array<int, array{header:string,label:string,on_page:bool}>
 */
function jcp_page_doc_sections_for_kind( string $page_kind, string $preset = '' ): array {
	$out       = [];
	$seen      = [];
	if ( $preset === '' ) {
		$preset = match ( $page_kind ) {
			'referral' => 'referral',
			'industry' => 'industry',
			'home'     => 'home',
			default    => 'marketing',
		};
	}
	$preset_def = jcp_page_get_preset( $preset );
	$preset_types = [];
	foreach ( (array) ( $preset_def['block_types'] ?? [] ) as $entry ) {
		$parsed = jcp_page_parse_preset_block_entry( $entry );
		if ( ! empty( $parsed['type'] ) ) {
			$preset_types[] = (string) $parsed['type'];
		}
	}

	$add = static function ( string $header, string $block_type ) use ( &$out, &$seen, $preset_types ): void {
		if ( isset( $seen[ $header ] ) ) {
			return;
		}
		$seen[ $header ] = true;
		$out[]             = [
			'header'  => $header,
			'label'   => jcp_page_doc_section_label( $header ),
			'on_page' => in_array( $block_type, $preset_types, true ),
		];
	};

	foreach ( jcp_block_registry() as $block ) {
		$kinds = $block['page_kinds'] ?? [];
		if ( $kinds && ! in_array( $page_kind, $kinds, true ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		foreach ( (array) ( $block['doc_sections'] ?? [] ) as $doc ) {
			$header = jcp_page_doc_normalize_section_header( (string) $doc );
			if ( $header ) {
				$add( $header, $type );
			}
		}
	}

	if ( $page_kind === 'industry' ) {
		foreach ( jcp_page_doc_media_sections() as $header => $legacy_key ) {
			$add( $header, 'media_text' );
		}
	}

	$order = [
		'HERO',
		'WHAT IT IS',
		'CORE MECHANIC',
		'MEDIA CORE',
		'HOW IT WORKS',
		'DEMO PREVIEW',
		'PROOF FLOW',
		'CHECK-INS',
		'MEDIA CHECK-INS',
		'CTA BAND',
		'PROBLEM',
		'MEDIA PROBLEM',
		'BENEFITS',
		'DIFFERENTIATION',
		"WHO IT'S FOR",
		'DIRECTORY PREVIEW',
		'COMMISSION',
		'PARTNERS',
		'SHARE',
		'FAQ',
		'CONVERSION',
		'FINAL CTA',
	];
	usort(
		$out,
		static function ( array $a, array $b ) use ( $order ): int {
			$pa = array_search( $a['header'], $order, true );
			$pb = array_search( $b['header'], $order, true );
			$pa = $pa === false ? 999 : $pa;
			$pb = $pb === false ? 999 : $pb;
			return $pa <=> $pb;
		}
	);

	return $out;
}

/**
 * Doc sections for a layout preset slug.
 *
 * @param string $preset Preset slug.
 * @return array<int, array{header:string,label:string,on_page:bool}>
 */
function jcp_page_doc_sections_for_preset( string $preset ): array {
	$def = jcp_page_get_preset( $preset );
	$page_kind = $def ? (string) ( $def['page_kind'] ?? 'marketing' ) : 'marketing';
	return jcp_page_doc_sections_for_kind( $page_kind, $preset );
}

/**
 * HTML list items for the admin import section guide.
 *
 * @param string $preset Preset slug.
 */
function jcp_page_doc_sections_guide_html( string $preset ): string {
	$html     = '';
	$sections = jcp_page_doc_sections_for_preset( $preset );
	foreach ( $sections as $row ) {
		$class = ! empty( $row['on_page'] ) ? 'is-on-page' : 'is-extra';
		$html .= '<li class="' . esc_attr( $class ) . '">';
		$html .= '<code>' . esc_html( (string) $row['header'] ) . '</code>';
		$html .= '<span>' . esc_html( (string) $row['label'] ) . '</span>';
		if ( empty( $row['on_page'] ) ) {
			$html .= '<em>' . esc_html__( 'optional on this page', 'jcp-core' ) . '</em>';
		}
		$html .= '</li>';
	}
	return $html;
}

/**
 * Resolve page kind for admin import UI.
 *
 * @param WP_Post|null         $post    Post.
 * @param array<string, mixed> $content Stored content (optional).
 */
function jcp_page_resolve_admin_page_kind( ?WP_Post $post, array $content = [] ): string {
	if ( ! $post instanceof WP_Post ) {
		return 'marketing';
	}
	if ( $post->post_type === 'jcp_niche_landing' ) {
		return 'industry';
	}
	if ( get_page_template_slug( $post ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
		return 'referral';
	}
	if ( get_page_template_slug( $post ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
		return 'home';
	}
	return jcp_page_resolve_kind( $content, (int) $post->ID );
}

/**
 * Human label for page kind.
 *
 * @param string $page_kind Page kind.
 */
function jcp_page_kind_label( string $page_kind ): string {
	return match ( $page_kind ) {
		'industry'  => __( 'Industry trade page', 'jcp-core' ),
		'marketing' => __( 'Block page', 'jcp-core' ),
		'referral'  => __( 'Referral program', 'jcp-core' ),
		'home'      => __( 'Homepage', 'jcp-core' ),
		default     => __( 'Page', 'jcp-core' ),
	};
}

/**
 * Whether a parsed section appears in the blocks document.
 *
 * @param array<int, array<string, mixed>> $blocks Block list.
 * @param string                           $section Canonical header.
 */
function jcp_page_doc_section_in_blocks( array $blocks, string $section ): bool {
	$media = jcp_page_doc_media_sections();
	if ( isset( $media[ $section ] ) ) {
		$legacy_key = $media[ $section ];
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( ( $block['type'] ?? '' ) !== 'media_text' ) {
				continue;
			}
			$key = ! empty( $block['legacy_key'] ) ? (string) $block['legacy_key'] : 'media_text';
			if ( $key === $legacy_key ) {
				return true;
			}
		}
		return false;
	}

	$type = jcp_block_type_from_doc_section( $section );
	if ( ! $type ) {
		return false;
	}
	foreach ( $blocks as $block ) {
		if ( is_array( $block ) && ( $block['type'] ?? '' ) === $type ) {
			return true;
		}
	}
	return false;
}

/**
 * Build a human-readable import report after parsing.
 *
 * @param array<string, mixed> $legacy    Parsed legacy content.
 * @param array<string, mixed> $blocks_doc Blocks document.
 * @param string               $page_kind Page kind.
 * @param string               $preset    Optional layout preset slug.
 * @return array<string, mixed>
 */
function jcp_page_doc_build_import_report( array $legacy, array $blocks_doc, string $page_kind, string $preset = '' ): array {
	$blocks       = (array) ( $blocks_doc['blocks'] ?? [] );
	$imported     = [];
	$skipped      = [];
	$block_labels = [];

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );
		$def  = jcp_block_get( $type );
		$block_labels[] = $def['label'] ?? $type;
	}

	foreach ( jcp_page_doc_legacy_key_map() as $legacy_key => $section ) {
		if ( ! jcp_page_doc_legacy_has_content( $legacy[ $legacy_key ] ?? null ) ) {
			continue;
		}
		$row = [
			'header' => $section,
			'label'  => jcp_page_doc_section_label( $section ),
		];
		if ( jcp_page_doc_section_in_blocks( $blocks, $section ) ) {
			$imported[] = $row;
		} else {
			$skipped[] = $row;
		}
	}

	$applicable = array_values( array_filter(
		jcp_page_doc_sections_for_kind( $page_kind, $preset ),
		static fn( array $row ): bool => ! empty( $row['on_page'] )
	) );

	$preset_def   = $preset !== '' ? jcp_page_get_preset( $preset ) : null;
	$preset_label = $preset_def ? (string) ( $preset_def['label'] ?? $preset ) : jcp_page_kind_label( $page_kind );

	$message_parts = [];
	if ( $imported ) {
		$message_parts[] = sprintf(
			/* translators: %d: number of sections */
			_n( '%d section imported into this page.', '%d sections imported into this page.', count( $imported ), 'jcp-core' ),
			count( $imported )
		);
	} else {
		$message_parts[] = __( 'No recognized sections found — check ALL CAPS headers (HERO, WHAT IT IS, etc.).', 'jcp-core' );
	}
	if ( $skipped ) {
		$message_parts[] = sprintf(
			/* translators: %d: number of sections */
			_n( '%d section parsed but not in this layout’s default stack (add the block in Page Structure to use it).', '%d sections parsed but not in this layout’s default stack (add the block in Page Structure to use them).', count( $skipped ), 'jcp-core' ),
			count( $skipped )
		);
	}
	$message_parts[] = __( 'Click Update / Publish to save.', 'jcp-core' );

	return [
		'page_kind'       => $page_kind,
		'page_kind_label' => $preset_label,
		'preset'          => $preset,
		'imported'        => $imported,
		'skipped'         => $skipped,
		'blocks'          => $block_labels,
		'applicable'      => $applicable,
		'message'         => implode( ' ', $message_parts ),
	];
}

/**
 * Catalog rows for theme docs table.
 *
 * @return array<int, array{header:string,label:string}>
 */
function jcp_page_doc_section_catalog(): array {
	$rows = [];
	foreach ( jcp_page_doc_sections_for_kind( 'industry' ) as $row ) {
		$rows[] = [
			'header' => $row['header'],
			'label'  => $row['label'],
		];
	}
	return $rows;
}
