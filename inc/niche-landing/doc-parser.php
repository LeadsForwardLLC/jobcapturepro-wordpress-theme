<?php
/**
 * Parse industry page content documents into niche landing JSON.
 *
 * Supports copy-paste from Google Docs or plain text / .docx exports.
 *
 * @package JCP_Core
 */

/**
 * Extract plain text from a .docx file path.
 *
 * @param string $path Absolute file path.
 */
function jcp_niche_extract_docx_text( string $path ): string {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return '';
	}
	$zip = new ZipArchive();
	if ( $zip->open( $path ) !== true ) {
		return '';
	}
	$xml = $zip->getFromName( 'word/document.xml' );
	$zip->close();
	if ( ! is_string( $xml ) || $xml === '' ) {
		return '';
	}
	$xml = str_replace( [ '</w:p>', '</w:tr>', '<w:tab/>' ], [ "\n", "\n", "\t" ], $xml );
	$text = wp_strip_all_tags( $xml );
	return jcp_niche_normalize_document_text( $text );
}

/**
 * Normalize document text line endings and spacing.
 *
 * @param string $text Raw text.
 */
function jcp_niche_normalize_document_text( string $text ): string {
	$text = str_replace( [ "\r\n", "\r" ], "\n", $text );
	$text = preg_replace( "/\xC2\xA0/", ' ', $text ) ?? $text;
	$text = preg_replace( "/[ \t]+\n/", "\n", $text ) ?? $text;
	return trim( $text );
}

/**
 * Parse a writer document into niche landing JSON.
 *
 * @param string   $text      Document plain text.
 * @param string   $niche_key URL slug fallback.
 * @param string   $niche_label Post title fallback.
 * @return array<string, mixed>
 */
function jcp_niche_parse_document( string $text, string $niche_key = '', string $niche_label = '' ): array {
	$text  = jcp_niche_normalize_document_text( $text );
	$lines = $text === '' ? [] : explode( "\n", $text );

	$start = 0;
	foreach ( $lines as $i => $line ) {
		if ( stripos( $line, 'write content here' ) !== false || strtoupper( trim( $line ) ) === 'HERO' ) {
			$start = stripos( $line, 'write content here' ) !== false ? $i + 1 : $i;
			break;
		}
	}

	$meta_lines = array_slice( $lines, 0, $start );

	$sections = [];
	$current  = '_meta';
	$sections[ $current ] = $meta_lines;

	$section_names = jcp_page_doc_recognized_section_headers();

	for ( $i = $start; $i < count( $lines ); $i++ ) {
		$raw  = $lines[ $i ];
		$trim = trim( $raw );
		if ( $trim === '' ) {
			continue;
		}
		$canonical = jcp_page_doc_normalize_section_header( $trim );
		if ( $canonical !== null ) {
			$current = $canonical;
			if ( ! isset( $sections[ $current ] ) ) {
				$sections[ $current ] = [];
			}
			continue;
		}
		$sections[ $current ][] = $raw;
	}

	$keywords = jcp_niche_doc_parse_keywords( $sections['_meta'] ?? [] );
	$content  = [
		'niche_key'   => sanitize_title( $niche_key ),
		'niche_label' => $niche_label !== '' ? $niche_label : ucwords( str_replace( '-', ' ', $niche_key ) ),
		'seo'         => [
			'keywords' => $keywords,
		],
	];

	if ( ! empty( $sections['HERO'] ) ) {
		$content['hero'] = jcp_niche_doc_parse_hero( $sections['HERO'] );
	}
	if ( ! empty( $sections['WHAT IT IS'] ) ) {
		$content['what_it_is'] = jcp_niche_doc_parse_what_it_is( $sections['WHAT IT IS'] );
	}
	if ( ! empty( $sections['CORE MECHANIC'] ) ) {
		$content['core_mechanic'] = jcp_niche_doc_parse_core_mechanic( $sections['CORE MECHANIC'] );
	}
	if ( ! empty( $sections['HOW IT WORKS'] ) ) {
		$content['how_it_works'] = jcp_niche_doc_parse_how_it_works( $sections['HOW IT WORKS'] );
	}
	if ( ! empty( $sections['CHECK-INS'] ) ) {
		$content['check_ins'] = jcp_niche_doc_parse_check_ins( $sections['CHECK-INS'] );
	}
	if ( ! empty( $sections['PROBLEM'] ) ) {
		$content['problem'] = jcp_niche_doc_parse_problem( $sections['PROBLEM'] );
	}
	if ( ! empty( $sections['BENEFITS'] ) ) {
		$content['benefits'] = jcp_niche_doc_parse_benefits( $sections['BENEFITS'] );
	}
	if ( ! empty( $sections['DIFFERENTIATION'] ) ) {
		$content['differentiation'] = jcp_niche_doc_parse_differentiation( $sections['DIFFERENTIATION'] );
	}
	if ( ! empty( $sections["WHO IT'S FOR"] ) ) {
		$content['who_its_for'] = jcp_niche_doc_parse_who_its_for( $sections["WHO IT'S FOR"] );
	}
	if ( ! empty( $sections['FAQ'] ) ) {
		$content['faq'] = jcp_niche_doc_parse_faq( $sections['FAQ'] );
	}
	if ( ! empty( $sections['CONVERSION'] ) ) {
		$content['conversion'] = jcp_niche_doc_parse_conversion( $sections['CONVERSION'] );
	}
	if ( ! empty( $sections['FINAL CTA'] ) ) {
		$content['final_cta'] = jcp_niche_doc_parse_final_cta( $sections['FINAL CTA'] );
	}
	if ( ! empty( $sections['MEDIA CORE'] ) ) {
		$content['media_text'] = jcp_niche_doc_parse_media_text( $sections['MEDIA CORE'] );
	}
	if ( ! empty( $sections['MEDIA CHECK-INS'] ) ) {
		$content['media_text_check_ins'] = jcp_niche_doc_parse_media_text( $sections['MEDIA CHECK-INS'] );
	}
	if ( ! empty( $sections['MEDIA PROBLEM'] ) ) {
		$content['media_text_problem'] = jcp_niche_doc_parse_media_text( $sections['MEDIA PROBLEM'] );
	}

	return jcp_niche_doc_derive_media_text_blocks( $content );
}

/**
 * @param string[] $lines Meta header lines.
 * @return string[]
 */
function jcp_niche_doc_parse_keywords( array $lines ): array {
	foreach ( $lines as $line ) {
		if ( stripos( $line, 'primary keyword' ) === 0 ) {
			$parts = explode( ':', $line, 2 );
			if ( count( $parts ) < 2 ) {
				return [];
			}
			$raw = array_map( 'trim', explode( ',', $parts[1] ) );
			return array_values( array_filter( $raw ) );
		}
	}
	return [];
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_labeled_fields( array $lines ): array {
	$labels = [ 'h1', 'subheadline', 'headline', 'cta', 'cta note', 'trust line', 'closing line' ];
	$out    = [];
	$count  = count( $lines );

	for ( $i = 0; $i < $count; $i++ ) {
		$low = strtolower( trim( $lines[ $i ] ) );
		if ( ! in_array( $low, $labels, true ) ) {
			continue;
		}
		for ( $j = $i + 1; $j < $count; $j++ ) {
			$next = trim( $lines[ $j ] );
			if ( $next === '' ) {
				continue;
			}
			$next_low = strtolower( $next );
			if ( in_array( $next_low, $labels, true ) ) {
				break;
			}
			$out[ $low ] = $next;
			break;
		}
	}

	return $out;
}

/**
 * Parse optional trailing CTA label(s) after a "CTA" field label.
 *
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_trailing_cta( array $lines, string $primary_url = '/demo', string $secondary_url = '#how-it-works' ): array {
	$cta_index = null;
	foreach ( $lines as $i => $line ) {
		if ( strtolower( trim( $line ) ) === 'cta' ) {
			$cta_index = (int) $i;
		}
	}
	if ( $cta_index === null ) {
		return [
			'cta_primary'        => [ 'label' => '', 'url' => '' ],
			'cta_secondary'      => [ 'label' => '', 'url' => '' ],
			'show_cta'           => false,
			'show_cta_secondary' => false,
		];
	}

	$labels = [];
	for ( $j = $cta_index + 1; $j < count( $lines ); $j++ ) {
		$trim = trim( $lines[ $j ] );
		if ( $trim === '' ) {
			continue;
		}
		$labels[] = $trim;
		if ( count( $labels ) >= 2 ) {
			break;
		}
	}

	$primary_label   = $labels[0] ?? '';
	$secondary_label = $labels[1] ?? '';

	return [
		'cta_primary'        => [
			'label' => $primary_label,
			'url'   => $primary_label !== '' ? $primary_url : '',
		],
		'cta_secondary'      => [
			'label' => $secondary_label,
			'url'   => $secondary_label !== '' ? $secondary_url : '',
		],
		'show_cta'           => $primary_label !== '',
		'show_cta_secondary' => $secondary_label !== '',
	];
}

/**
 * Merge trailing CTA props into a parsed section.
 *
 * @param array<string, mixed> $section Parsed section.
 * @param string[]             $lines   Original section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_merge_section_cta( array $section, array $lines ): array {
	return array_merge( $section, jcp_niche_doc_parse_trailing_cta( $lines ) );
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_hero( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$ctas   = [];
	$mode   = 'fields';

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( $low === 'cta' ) {
			$mode = 'cta';
			continue;
		}
		if ( in_array( $low, [ 'h1', 'subheadline', 'trust line' ], true ) ) {
			$mode = 'fields';
			continue;
		}
		if ( $mode === 'cta' && $low !== '' ) {
			$ctas[] = $trim;
		}
	}

	return [
		'h1'          => $fields['h1'] ?? '',
		'subheadline' => $fields['subheadline'] ?? '',
		'cta_primary' => [
			'label' => $ctas[0] ?? 'Start free trial',
			'url'   => '',
		],
		'cta_secondary' => [
			'label' => $ctas[1] ?? 'See how it works',
			'url'   => '#how-it-works',
		],
		'trust_line' => $fields['trust line'] ?? '',
	];
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_what_it_is( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$out    = [
		'headline'    => $fields['headline'] ?? '',
		'subheadline' => $fields['subheadline'] ?? '',
		'team_already' => [],
		'turns_into'   => [],
		'lead'         => '',
		'closing'      => $fields['closing line'] ?? '',
	];

	$mode = 'scan';
	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( in_array( $low, [ 'headline', 'subheadline', 'closing line' ], true ) ) {
			$mode = 'skip_label';
			continue;
		}
		if ( $mode === 'skip_label' ) {
			$mode = 'scan';
			continue;
		}
		if ( preg_match( '/already\s*:$/i', $trim ) ) {
			$out['team_already_title'] = rtrim( $trim, ':' );
			$mode                      = 'team_already';
			continue;
		}
		if ( preg_match( '/turns .+ into\s*:$/i', $trim ) ) {
			$out['turns_into_title'] = rtrim( $trim, ':' );
			$mode                    = 'turns_into';
			continue;
		}
		if ( $low === 'closing line' ) {
			$mode = 'closing';
			continue;
		}
		if ( $mode === 'closing' ) {
			$out['closing'] = $trim;
			$mode           = 'scan';
			continue;
		}
		if ( $mode === 'team_already' ) {
			if ( stripos( $trim, 'but ' ) === 0 ) {
				$out['lead'] = $trim;
				$mode        = 'lead';
				continue;
			}
			$out['team_already'][] = $trim;
			continue;
		}
		if ( $mode === 'lead' ) {
			if ( preg_match( '/turns .+ into\s*:$/i', $trim ) ) {
				$out['turns_into_title'] = rtrim( $trim, ':' );
				$mode                    = 'turns_into';
				continue;
			}
			$out['lead'] .= ( $out['lead'] !== '' ? ' ' : '' ) . $trim;
			continue;
		}
		if ( $mode === 'turns_into' ) {
			if ( stripos( $trim, 'closing line' ) === 0 ) {
				$mode = 'closing';
				continue;
			}
			if ( preg_match( '/^(automatically\.?|etc\.?)$/i', $trim ) ) {
				continue;
			}
			$out['turns_into'][] = rtrim( $trim, '.' );
		}
	}

	return jcp_niche_doc_merge_section_cta( $out, $lines );
}

/**
 * @param string[] $lines Section lines.
 * @return array<int, array<string, string>>
 */
function jcp_niche_doc_parse_core_mechanic( array $lines ): array {
	$items = [];
	$pending = null;
	foreach ( $lines as $line ) {
		$trim = trim( $line );
		if ( preg_match( '/^(\d+)\s+(.+)$/', $trim, $m ) ) {
			if ( $pending ) {
				$items[] = $pending;
			}
			$pending = [
				'value' => $m[1],
				'label' => trim( $m[2] ),
				'detail' => '',
			];
			continue;
		}
		if ( $pending && $pending['detail'] === '' ) {
			$pending['detail'] = $trim;
		}
	}
	if ( $pending ) {
		$items[] = $pending;
	}
	return $items;
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_how_it_works( array $lines ): array {
	$fields  = jcp_niche_doc_parse_labeled_fields( $lines );
	$steps     = [];
	$current   = null;
	$skip_next = false;

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( $skip_next ) {
			$skip_next = false;
			continue;
		}
		if ( in_array( $low, [ 'headline', 'subheadline' ], true ) ) {
			$skip_next = true;
			continue;
		}
		if ( $low === 'cta' ) {
			$current = null;
			continue;
		}
		if ( preg_match( '/^0?(\d+)\s+(.+)$/', $trim, $m ) ) {
			$steps[] = [
				'title' => trim( $m[2] ),
				'lines' => [],
			];
			$current = count( $steps ) - 1;
			continue;
		}
		if ( is_int( $current ) ) {
			if ( preg_match( '/live proof across\s*:?\s*$/i', $trim ) ) {
				$steps[ $current ]['_publish_prefix'] = rtrim( $trim, ':' );
				$steps[ $current ]['_channels']       = true;
				continue;
			}
			if ( ! empty( $steps[ $current ]['_channels'] ) ) {
				if ( preg_match( '/^Homeowners can see/i', $trim ) ) {
					$prefix   = $steps[ $current ]['_publish_prefix'] ?? 'That job becomes live proof across';
					$channels = $steps[ $current ]['_channel_parts'] ?? [];
					$steps[ $current ]['lines'][] = trim( $prefix . ' ' . implode( ', ', array_map( 'trim', $channels ) ) );
					unset( $steps[ $current ]['_channels'], $steps[ $current ]['_publish_prefix'], $steps[ $current ]['_channel_parts'] );
					$steps[ $current ]['lines'][] = $trim;
					continue;
				}
				$steps[ $current ]['_channel_parts'][] = $trim;
				continue;
			}
			$steps[ $current ]['lines'][] = $trim;
		}
	}

	// Merge Publish step channel bullets into one sentence; strip internal keys.
	foreach ( $steps as &$step ) {
		unset( $step['_channels'], $step['_publish_prefix'], $step['_channel_parts'] );
	}
	unset( $step );

	// Merge indented follow-up lines in Review step (fresh — right after...).
	foreach ( $steps as &$step ) {
		if ( strtolower( $step['title'] ) !== 'review' || count( $step['lines'] ) < 2 ) {
			continue;
		}
		$second = $step['lines'][1];
		if ( preg_match( '/^right after/i', $second ) ) {
			$step['lines'][0] .= ' — ' . lcfirst( $second );
			array_splice( $step['lines'], 1, 1 );
		}
	}
	unset( $step );

	$section = [
		'headline'    => $fields['headline'] ?? '',
		'subheadline' => $fields['subheadline'] ?? '',
		'steps'       => array_values( $steps ),
	];

	return jcp_niche_doc_merge_section_cta( $section, $lines );
}

/**
 * Parse paired title/body blocks.
 *
 * @param string[] $lines   Content lines after labeled fields.
 * @param string   $closing Optional closing key.
 * @return array{items: array<int, array{title: string, body: string}>, closing: string}
 */
function jcp_niche_doc_parse_title_body_pairs( array $lines, string $closing = '' ): array {
	$items        = [];
	$closing_text = $closing;
	$title        = '';
	$body         = '';

	$flush = static function () use ( &$items, &$title, &$body ): void {
		if ( $title === '' ) {
			return;
		}
		$items[] = [ 'title' => $title, 'body' => trim( $body ) ];
		$title   = '';
		$body    = '';
	};

	$is_body_line = static function ( string $line ): bool {
		return $line !== '' && ( $line[0] === ' ' || $line[0] === "\t" );
	};

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		if ( $trim === '' ) {
			continue;
		}
		$low = strtolower( $trim );
		if ( in_array( $low, [ 'closing line', 'headline', 'subheadline' ], true ) ) {
			continue;
		}
		if ( preg_match( '/^(each check-in|this is where|homeowners trust|customers are making)/i', $trim ) && ! empty( $items ) ) {
			$flush();
			$closing_text = $trim;
			break;
		}
		if ( $is_body_line( $line ) && $title !== '' ) {
			$body .= ( $body !== '' ? ' ' : '' ) . $trim;
			continue;
		}
		$flush();
		$title = $trim;
		$body  = '';
	}
	$flush();

	return [ 'items' => $items, 'closing' => $closing_text ];
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_check_ins( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$body_lines = [];
	$past_labels = false;
	foreach ( $lines as $line ) {
		$low = strtolower( trim( $line ) );
		if ( in_array( $low, [ 'headline', 'subheadline' ], true ) ) {
			$past_labels = false;
			continue;
		}
		if ( $low === 'headline' || $low === 'subheadline' ) {
			continue;
		}
		if ( in_array( $low, [ 'headline', 'subheadline' ], true ) ) {
			continue;
		}
		if ( isset( $fields['headline'] ) && trim( $line ) === $fields['headline'] ) {
			$past_labels = true;
			continue;
		}
		if ( isset( $fields['subheadline'] ) && trim( $line ) === $fields['subheadline'] ) {
			$past_labels = true;
			continue;
		}
		if ( ! $past_labels && ( $low === 'headline' || $low === 'subheadline' ) ) {
			continue;
		}
		if ( $low === 'headline' || $low === 'subheadline' ) {
			continue;
		}
		if ( strtolower( trim( $line ) ) === 'headline' ) {
			continue;
		}
		$body_lines[] = $line;
	}

	// Strip field label lines from body_lines
	$filtered = [];
	$skip_next = false;
	foreach ( $lines as $line ) {
		$low = strtolower( trim( $line ) );
		if ( in_array( $low, [ 'headline', 'subheadline' ], true ) ) {
			$skip_next = true;
			continue;
		}
		if ( $skip_next ) {
			$skip_next = false;
			continue;
		}
		$filtered[] = $line;
	}

	$parsed = jcp_niche_doc_parse_title_body_pairs( $filtered );
	$features = array_map(
		static function ( $item ) {
			return [ 'title' => $item['title'], 'body' => $item['body'] ];
		},
		$parsed['items']
	);

	return jcp_niche_doc_merge_section_cta(
		[
			'headline'    => $fields['headline'] ?? '',
			'subheadline' => $fields['subheadline'] ?? '',
			'features'    => $features,
			'closing'     => $parsed['closing'] !== '' ? rtrim( $parsed['closing'], '.' ) . '.' : '',
		],
		$lines
	);
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_problem( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$filtered = [];
	$skip_next = false;
	foreach ( $lines as $line ) {
		$low = strtolower( trim( $line ) );
		if ( in_array( $low, [ 'headline', 'subheadline', 'closing line' ], true ) ) {
			$skip_next = true;
			continue;
		}
		if ( $skip_next ) {
			$skip_next = false;
			continue;
		}
		$filtered[] = $line;
	}
	$parsed = jcp_niche_doc_parse_title_body_pairs( $filtered, $fields['closing line'] ?? '' );
	$pain_points = array_map(
		static function ( $item ) {
			return [ 'title' => $item['title'], 'body' => $item['body'] ];
		},
		$parsed['items']
	);
	$closing = $parsed['closing'];
	if ( $closing !== '' && stripos( $closing, 'customers are making' ) === 0 ) {
		foreach ( $filtered as $line ) {
			$trim = trim( $line );
			if ( stripos( $trim, 'this is where' ) === 0 ) {
				$closing = jcp_niche_doc_join_sentences( [ $closing, $trim ] );
				break;
			}
		}
	}
	if ( $closing === '' && ! empty( $pain_points ) ) {
		$last = end( $pain_points );
		if ( $last && $last['body'] === '' && strlen( $last['title'] ) > 60 ) {
			$closing = array_pop( $pain_points )['title'];
		}
	}

	return jcp_niche_doc_merge_section_cta(
		[
			'headline'    => $fields['headline'] ?? '',
			'subheadline' => $fields['subheadline'] ?? '',
			'pain_points' => $pain_points,
			'closing'     => $closing !== '' ? $closing : ( $fields['closing line'] ?? '' ),
		],
		$lines
	);
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_benefits( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$filtered = [];
	$skip_next = false;
	foreach ( $lines as $line ) {
		$low = strtolower( trim( $line ) );
		if ( $low === 'headline' ) {
			$skip_next = true;
			continue;
		}
		if ( $skip_next ) {
			$skip_next = false;
			continue;
		}
		$filtered[] = $line;
	}
	$parsed = jcp_niche_doc_parse_title_body_pairs( $filtered );
	$items = array_map(
		static function ( $item ) {
			return [ 'title' => $item['title'], 'body' => $item['body'] ];
		},
		$parsed['items']
	);
	$closing = $parsed['closing'];
	if ( stripos( $closing, 'homeowners trust' ) === 0 ) {
		foreach ( $filtered as $idx => $line ) {
			if ( trim( $line ) !== $closing ) {
				continue;
			}
			$next = $filtered[ $idx + 1 ] ?? '';
			if ( $next !== '' && ( $next[0] === ' ' || $next[0] === "\t" ) ) {
				$closing = jcp_niche_doc_join_sentences( [ $closing, trim( $next ) ] );
			}
			break;
		}
	}
	if ( $closing === '' && ! empty( $items ) ) {
		$last = end( $items );
		if ( $last['body'] === '' && strlen( $last['title'] ) > 80 ) {
			$closing = array_pop( $items )['title'];
		} elseif ( $last['body'] !== '' && strlen( $last['title'] ) > 40 && stripos( $last['title'], 'homeowners' ) !== false ) {
			$closing = $last['title'] . ( $last['body'] !== '' ? ' ' . $last['body'] : '' );
			array_pop( $items );
		}
	}
	// Merge last item if it's clearly a closing paragraph (title + body both present on two lines in one item)
	if ( $closing === '' && ! empty( $items ) ) {
		$last_idx = count( $items ) - 1;
		$last     = $items[ $last_idx ];
		if ( stripos( $last['title'], 'homeowners trust' ) === 0 ) {
			$closing = trim( $last['title'] . ' ' . $last['body'] );
			array_pop( $items );
		}
	}

	return jcp_niche_doc_merge_section_cta(
		[
			'headline' => $fields['headline'] ?? '',
			'items'    => $items,
			'closing'  => $closing,
		],
		$lines
	);
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_conversion( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$points = [];
	$cta    = '';
	$mode   = 'points';
	$skip   = false;

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( in_array( $low, [ 'headline', 'subheadline' ], true ) ) {
			$skip = true;
			continue;
		}
		if ( $skip ) {
			$skip = false;
			continue;
		}
		if ( $low === 'cta' ) {
			$mode = 'cta';
			continue;
		}
		if ( $mode === 'cta' && $trim !== '' ) {
			$cta = $trim;
			continue;
		}
		if ( $trim !== '' ) {
			$points[] = $trim;
		}
	}

	return [
		'headline'        => $fields['headline'] ?? '',
		'subheadline'     => $fields['subheadline'] ?? '',
		'points'          => $points,
		'cta_primary'     => [
			'label' => $cta !== '' ? $cta : ( $fields['cta'] ?? __( 'See how this works for your business', 'jcp-core' ) ),
			'url'   => '/demo',
		],
		'media_type'      => 'image',
		'media_position'  => 'right',
		'image_url'       => '',
		'image_alt'       => '',
		'media_url'       => '',
		'media_alt'       => '',
	];
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_differentiation( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$body_parts = [];
	$bullets    = [];
	$mode       = 'body';

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( $low === 'headline' ) {
			$mode = 'skip';
			continue;
		}
		if ( $mode === 'skip' ) {
			$mode = 'body';
			continue;
		}
		if ( preg_match( '/^no (new|extra|marketing)/i', $trim ) ) {
			$mode = 'bullets';
		}
		if ( $mode === 'bullets' ) {
			$bullets[] = $trim;
			continue;
		}
		if ( $trim !== '' && stripos( $trim, 'jobcapturepro' ) !== false || preg_match( '/^you /i', $trim ) ) {
			$body_parts[] = $trim;
		} elseif ( $trim !== '' && empty( $bullets ) ) {
			$body_parts[] = $trim;
		}
	}

	return jcp_niche_doc_merge_section_cta(
		[
			'headline' => $fields['headline'] ?? '',
			'body'     => jcp_niche_doc_join_sentences( $body_parts ),
			'bullets'  => $bullets,
		],
		$lines
	);
}

/**
 * Join paragraph lines into one body string with sentence punctuation.
 *
 * @param string[] $parts Lines.
 */
function jcp_niche_doc_join_sentences( array $parts ): string {
	$parts = array_values( array_filter( array_map( 'trim', $parts ) ) );
	if ( empty( $parts ) ) {
		return '';
	}
	$out = array_shift( $parts );
	foreach ( $parts as $part ) {
		if ( $out === '' ) {
			$out = $part;
			continue;
		}
		$out = rtrim( $out, '. ' );
		$part = ltrim( $part );
		$out .= ( str_ends_with( $out, '.' ) ? ' ' : '. ' ) . $part;
	}
	return rtrim( $out, '. ' ) . ( str_ends_with( $out, '.' ) ? '' : '.' );
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_who_its_for( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$filtered = [];
	$skip_next = false;
	foreach ( $lines as $line ) {
		$low = strtolower( trim( $line ) );
		if ( $low === 'headline' ) {
			$skip_next = true;
			continue;
		}
		if ( $skip_next ) {
			$skip_next = false;
			continue;
		}
		$filtered[] = $line;
	}
	$parsed = jcp_niche_doc_parse_title_body_pairs( $filtered );
	$audiences = array_map(
		static function ( $item ) {
			return [ 'title' => $item['title'], 'body' => $item['body'] ];
		},
		$parsed['items']
	);

	return jcp_niche_doc_merge_section_cta(
		[
			'headline'  => $fields['headline'] ?? '',
			'audiences' => $audiences,
		],
		$lines
	);
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_faq( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$items  = [];
	$q      = null;

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( $low === 'headline' ) {
			continue;
		}
		if ( $trim === '' ) {
			continue;
		}
		if ( str_ends_with( $trim, '?' ) ) {
			if ( $q !== null ) {
				$items[] = [ 'q' => $q, 'a' => '' ];
			}
			$q = $trim;
			continue;
		}
		if ( $q !== null ) {
			$items[] = [ 'q' => $q, 'a' => $trim ];
			$q       = null;
		}
	}
	if ( $q !== null ) {
		$items[] = [ 'q' => $q, 'a' => '' ];
	}

	return jcp_niche_doc_merge_section_cta(
		[
			'headline' => $fields['headline'] ?? '',
			'items'    => $items,
		],
		$lines
	);
}

/**
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_final_cta( array $lines ): array {
	$fields = jcp_niche_doc_parse_labeled_fields( $lines );
	$ctas   = [];
	$mode   = 'fields';

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		$low  = strtolower( $trim );
		if ( $low === 'cta' ) {
			$mode = 'cta';
			continue;
		}
		if ( in_array( $low, [ 'headline', 'subheadline', 'cta note' ], true ) ) {
			$mode = 'fields';
			continue;
		}
		if ( $mode === 'cta' && $trim !== '' ) {
			$ctas[] = $trim;
		}
	}

	return [
		'headline'         => $fields['headline'] ?? '',
		'subheadline'      => $fields['subheadline'] ?? '',
		'cta_primary'      => [
			'label' => $ctas[0] ?? 'Start free trial',
			'url'   => '',
		],
		'cta_secondary'    => [
			'label' => $ctas[1] ?? 'See how it works',
			'url'   => '/demo',
		],
		'cta_note'         => trim( (string) ( $fields['cta note'] ?? 'No credit card required. Setup in under 10 minutes.' ) ),
		'show_subheadline' => trim( (string) ( $fields['subheadline'] ?? '' ) ) !== '',
		'show_cta_note'    => trim( (string) ( $fields['cta note'] ?? '' ) ) !== '',
	];
}

/**
 * Parse a MEDIA + text section from a writer document.
 *
 * @param string[] $lines Section lines.
 * @return array<string, mixed>
 */
function jcp_niche_doc_parse_media_text( array $lines ): array {
	$labels = [ 'badge', 'headline', 'subheadline', 'cue', 'body', 'cta', 'cta note', 'media position' ];
	$out    = [];
	$count  = count( $lines );

	for ( $i = 0; $i < $count; $i++ ) {
		$low = strtolower( trim( $lines[ $i ] ) );
		if ( ! in_array( $low, $labels, true ) ) {
			continue;
		}
		for ( $j = $i + 1; $j < $count; $j++ ) {
			$next = trim( $lines[ $j ] );
			if ( $next === '' ) {
				continue;
			}
			$next_low = strtolower( $next );
			if ( in_array( $next_low, $labels, true ) ) {
				break;
			}
			$out[ $low ] = $next;
			break;
		}
	}

	$cta_label = trim( (string) ( $out['cta'] ?? '' ) );
	$position  = strtolower( trim( (string) ( $out['media position'] ?? 'right' ) ) );
	$position  = $position === 'left' ? 'left' : 'right';

	return [
		'badge'              => trim( (string) ( $out['badge'] ?? '' ) ),
		'headline'           => trim( (string) ( $out['headline'] ?? '' ) ),
		'subheadline'        => trim( (string) ( $out['subheadline'] ?? '' ) ),
		'cue'                => trim( (string) ( $out['cue'] ?? '' ) ),
		'body'               => trim( (string) ( $out['body'] ?? '' ) ),
		'cta_primary'        => [
			'label' => $cta_label,
			'url'   => $cta_label !== '' ? '#how-it-works' : '',
		],
		'cta_note'           => trim( (string) ( $out['cta note'] ?? '' ) ),
		'media_type'         => 'image',
		'media_position'     => $position,
		'phone_mockup_style' => 'app_shell',
		'show_badge'         => trim( (string) ( $out['badge'] ?? '' ) ) !== '',
		'show_subheadline'   => trim( (string) ( $out['subheadline'] ?? '' ) ) !== '',
		'show_cue'           => trim( (string) ( $out['cue'] ?? '' ) ) !== '',
		'show_body'          => trim( (string) ( $out['body'] ?? '' ) ) !== '',
		'show_cta'           => $cta_label !== '',
		'show_cta_note'      => trim( (string) ( $out['cta note'] ?? '' ) ) !== '',
		'show_divider'       => false,
	];
}

/**
 * Auto-fill media + text blocks from parsed sections when not written explicitly.
 *
 * @param array<string, mixed> $content Parsed document content.
 * @return array<string, mixed>
 */
function jcp_niche_doc_derive_media_text_blocks( array $content ): array {
	if ( empty( $content['media_text']['headline'] ) ) {
		$what = $content['what_it_is'] ?? [];
		$lead = trim( (string) ( $what['lead'] ?? '' ) );
		$content['media_text'] = [
			'headline'           => trim( (string) ( $what['team_already_title'] ?? 'Your crew already takes the photos — now they work for you' ) ),
			'subheadline'        => trim( (string) ( $what['subheadline'] ?? '' ) ),
			'body'               => $lead !== '' ? $lead : trim( (string) ( $what['closing'] ?? '' ) ),
			'cta_primary'        => [
				'label' => 'See how it works',
				'url'   => '#how-it-works',
			],
			'media_type'         => 'image',
			'media_position'     => 'right',
			'phone_mockup_style' => 'app_shell',
			'show_subheadline'   => trim( (string) ( $what['subheadline'] ?? '' ) ) !== '',
			'show_body'          => true,
			'show_cta'           => true,
			'show_divider'       => false,
		];
	}

	if ( empty( $content['media_text_check_ins']['headline'] ) ) {
		$check = $content['check_ins'] ?? [];
		$content['media_text_check_ins'] = [
			'headline'           => trim( (string) ( $check['headline'] ?? '' ) ),
			'subheadline'        => '',
			'body'               => trim( (string) ( $check['subheadline'] ?? '' ) ) . ( ! empty( $check['closing'] ) ? ' ' . trim( (string) $check['closing'] ) : '' ),
			'media_type'         => 'image',
			'media_position'     => 'left',
			'phone_mockup_style' => 'app_shell',
			'show_subheadline'   => false,
			'show_body'          => true,
			'show_cta'           => false,
			'show_divider'       => false,
		];
	}

	if ( empty( $content['media_text_problem']['headline'] ) ) {
		$problem = $content['problem'] ?? [];
		$content['media_text_problem'] = [
			'headline'           => trim( (string) ( $problem['headline'] ?? '' ) ),
			'subheadline'        => trim( (string) ( $problem['subheadline'] ?? '' ) ),
			'body'               => trim( (string) ( $problem['closing'] ?? '' ) ),
			'media_type'         => 'image',
			'media_position'     => 'right',
			'phone_mockup_style' => 'app_shell',
			'show_subheadline'   => trim( (string) ( $problem['subheadline'] ?? '' ) ) !== '',
			'show_body'          => trim( (string) ( $problem['closing'] ?? '' ) ) !== '',
			'show_cta'           => false,
			'show_divider'       => false,
		];
	}

	return $content;
}
