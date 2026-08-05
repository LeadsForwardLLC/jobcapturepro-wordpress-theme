<?php
/**
 * Document import → blocks[] (wraps niche section parsers).
 *
 * @package JCP_Core
 */

/**
 * Parse writer document into block page content.
 *
 * @param string $text        Document plain text.
 * @param string $page_key    URL slug.
 * @param string $page_label  Page title.
 * @param string $page_kind   industry|marketing|referral.
 * @param string $preset      Optional layout preset slug.
 * @return array<string, mixed>
 */
function jcp_page_parse_document( string $text, string $page_key = '', string $page_label = '', string $page_kind = 'industry', string $preset = '' ): array {
	$legacy = jcp_niche_parse_document( $text, $page_key, $page_label );
	if ( $page_kind === 'referral' || ( $legacy['page_type'] ?? '' ) === 'referral' ) {
		$legacy['page_type'] = 'referral';
		$page_kind           = 'referral';
	}
	$legacy['page_kind'] = $page_kind;
	if ( $preset === '' ) {
		$preset = $page_kind === 'referral' ? 'referral' : ( $page_kind === 'industry' ? 'industry' : ( $page_kind === 'home' ? 'home' : 'marketing' ) );
	}
	$legacy['preset'] = $preset;
	return jcp_page_legacy_to_blocks( $legacy, 0 );
}

/**
 * Parse document and return blocks plus an import report for admin UI.
 *
 * @param string $text        Document plain text.
 * @param string $page_key    URL slug.
 * @param string $page_label  Page title.
 * @param string $page_kind   industry|marketing|referral|home.
 * @param string $preset      Optional layout preset slug.
 * @return array{content: array<string, mixed>, report: array<string, mixed>}
 */
function jcp_page_parse_document_with_report( string $text, string $page_key = '', string $page_label = '', string $page_kind = 'industry', string $preset = '' ): array {
	$legacy = jcp_niche_parse_document( $text, $page_key, $page_label );
	if ( $page_kind === 'referral' || ( $legacy['page_type'] ?? '' ) === 'referral' ) {
		$legacy['page_type'] = 'referral';
		$page_kind           = 'referral';
	}
	$legacy['page_kind'] = $page_kind;
	if ( $preset === '' ) {
		$preset = $page_kind === 'referral' ? 'referral' : ( $page_kind === 'industry' ? 'industry' : ( $page_kind === 'home' ? 'home' : 'marketing' ) );
	}
	$legacy['preset'] = $preset;
	$content          = jcp_page_legacy_to_blocks( $legacy, 0 );
	$report           = jcp_page_doc_build_import_report( $legacy, $content, $page_kind, $preset );

	return [
		'content' => $content,
		'report'  => $report,
	];
}

/**
 * @deprecated Use jcp_page_parse_document().
 */
function jcp_niche_parse_document_to_blocks( string $text, string $page_key = '', string $page_label = '' ): array {
	return jcp_page_parse_document( $text, $page_key, $page_label, 'industry' );
}
