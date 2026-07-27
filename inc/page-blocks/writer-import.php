<?php
/**
 * Shared writer-document → page import (admin AJAX, Update save, admin-post).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether block content has a renderable hero headline.
 *
 * @param array<string, mixed> $content Block document.
 */
function jcp_page_content_has_hero_copy( array $content ): bool {
	foreach ( (array) ( $content['blocks'] ?? [] ) as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		if ( ( $block['type'] ?? '' ) !== 'hero' ) {
			continue;
		}
		$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];
		if ( trim( (string) ( $props['h1'] ?? '' ) ) !== '' || trim( (string) ( $props['h1_prefix'] ?? '' ) ) !== '' ) {
			return true;
		}
	}
	return false;
}

/**
 * Import a writer document into a post. Always persists on success.
 *
 * @param int    $post_id Post ID.
 * @param string $text    Writer document plain text.
 * @return array{ok:bool,message:string,imported:int,content?:array<string,mixed>,view_url?:string}
 */
function jcp_page_import_writer_document_to_post( int $post_id, string $text ): array {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return [
			'ok'       => false,
			'message'  => __( 'Page not found.', 'jcp-core' ),
			'imported' => 0,
		];
	}

	$received_len = strlen( $text );
	$text         = jcp_niche_normalize_document_text( $text );
	if ( $text === '' ) {
		return [
			'ok'       => false,
			'message'  => sprintf(
				/* translators: %d: raw POST body length */
				__( 'Paste did not reach the server (received %d characters). Hard-refresh, paste again, and use Import document & save page.', 'jcp-core' ),
				$received_len
			),
			'imported' => 0,
		];
	}

	if ( ! preg_match( '/(?:^|\n)\s*HERO\s*(?:\n|$)/i', $text ) ) {
		return [
			'ok'       => false,
			'message'  => sprintf(
				/* translators: %d: document character count */
				__( 'No HERO section found in %d characters of paste. Start from the line that says HERO (ALL CAPS).', 'jcp-core' ),
				strlen( $text )
			),
			'imported' => 0,
		];
	}

	// Block pages must use the JCP Block Page template or the front stays blank.
	if ( $post->post_type === 'page' && ! jcp_page_uses_block_template( $post_id ) ) {
		update_post_meta( $post_id, '_wp_page_template', 'page-jcp-blocks.php' );
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return [
				'ok'       => false,
				'message'  => __( 'Could not assign JCP Block Page template.', 'jcp-core' ),
				'imported' => 0,
			];
		}
	}

	$existing = jcp_page_get_content( $post_id );
	$preset   = jcp_writer_resolve_preset( $post, $existing );
	if ( $preset === '' || $preset === 'marketing' ) {
		// Campaign docs include FORM EMBED; prefer that stack when present.
		if ( preg_match( '/(?:^|\n)\s*FORM EMBED\s*(?:\n|$)/i', $text ) || preg_match( '/(?:^|\n)\s*CORE MECHANIC\s*(?:\n|$)/i', $text ) ) {
			$preset = 'campaign';
		}
	}
	if ( $preset === '' ) {
		$preset = 'campaign';
	}

	// Empty / single blank hero pages: start from a full preset skeleton so merge has slots to fill.
	$block_count = count( (array) ( $existing['blocks'] ?? [] ) );
	if ( $block_count < 2 || ! jcp_page_content_has_hero_copy( $existing ) ) {
		$existing = jcp_page_create_skeleton_document( $post, $preset );
	}

	$page_kind = jcp_page_resolve_admin_page_kind( $post, $existing );
	if ( $preset === 'campaign' ) {
		$page_kind = 'marketing';
	}

	$parsed = jcp_page_parse_document_with_report( $text, $post->post_name, get_the_title( $post ), $page_kind, $preset );
	$merged = jcp_page_merge_import_content( $parsed['content'], $existing );
	$report = jcp_page_doc_build_import_report(
		jcp_page_blocks_to_legacy( $parsed['content'] ),
		$merged,
		$page_kind,
		$preset
	);

	$imported_n = count( (array) ( $report['imported'] ?? [] ) );
	if ( $imported_n < 1 || ! jcp_page_content_has_hero_copy( $merged ) ) {
		return [
			'ok'       => false,
			'message'  => sprintf(
				/* translators: 1: sections found, 2: characters parsed */
				__( 'Import found no usable sections (%1$d headers matched in %2$d characters). Keep ALL CAPS headers (HERO, PROBLEM, BENEFITS, …).', 'jcp-core' ),
				$imported_n,
				strlen( $text )
			),
			'imported' => $imported_n,
		];
	}

	$merged['preset']    = $preset;
	$merged['page_kind'] = $page_kind !== '' ? $page_kind : 'marketing';
	if ( $merged['preset'] === 'campaign' && function_exists( 'jcp_page_finalize_campaign_document' ) ) {
		$merged = jcp_page_finalize_campaign_document( $merged );
	}

	jcp_page_save_content( $post_id, $merged );
	update_post_meta( $post_id, jcp_writer_layout_preset_meta_key(), (string) $merged['preset'] );

	// Prove the write stuck (catches object-cache / meta-filter failures).
	clean_post_cache( $post_id );
	$verify = jcp_page_get_content_raw( $post_id );
	$verify = is_array( $verify ) ? jcp_page_normalize_content( $verify, $post_id ) : [];
	if ( ! jcp_page_content_has_hero_copy( $verify ) ) {
		return [
			'ok'       => false,
			'message'  => __( 'Import parsed OK but WordPress did not keep the page data (meta write failed). Disable object-cache plugins briefly and retry.', 'jcp-core' ),
			'imported' => $imported_n,
		];
	}

	$hero_h1 = '';
	foreach ( (array) ( $verify['blocks'] ?? [] ) as $block ) {
		if ( is_array( $block ) && ( $block['type'] ?? '' ) === 'hero' ) {
			$hero_h1 = trim( (string) ( $block['props']['h1'] ?? '' ) );
			break;
		}
	}

	$labels = array_map(
		static function ( array $row ): string {
			return (string) ( $row['label'] ?? $row['header'] ?? '' );
		},
		(array) ( $report['imported'] ?? [] )
	);

	return [
		'ok'       => true,
		'message'  => sprintf(
			/* translators: 1: number of sections, 2: hero H1, 3: comma-separated labels */
			__( 'Saved %1$d sections. Hero: “%2$s”. (%3$s). Reload if fields look empty.', 'jcp-core' ),
			$imported_n,
			$hero_h1 !== '' ? $hero_h1 : '—',
			implode( ', ', array_filter( $labels ) )
		),
		'imported' => $imported_n,
		'content'  => $merged,
		'view_url' => (string) get_permalink( $post_id ),
		'report'   => $report,
		'hero_h1'  => $hero_h1,
	];
}

/**
 * Store a one-time admin notice after import.
 *
 * @param int                  $user_id User ID.
 * @param array<string, mixed> $result  Import result.
 */
function jcp_page_set_import_notice( int $user_id, array $result ): void {
	set_transient(
		'jcp_page_import_notice_' . $user_id,
		[
			'ok'      => ! empty( $result['ok'] ),
			'message' => (string) ( $result['message'] ?? '' ),
		],
		300
	);
}

/**
 * Render import admin notice if present.
 */
function jcp_page_render_import_admin_notice(): void {
	$user_id = get_current_user_id();
	if ( $user_id <= 0 ) {
		return;
	}
	$key  = 'jcp_page_import_notice_' . $user_id;
	$data = get_transient( $key );
	if ( ! is_array( $data ) || empty( $data['message'] ) ) {
		return;
	}
	delete_transient( $key );
	$class = ! empty( $data['ok'] ) ? 'notice notice-success' : 'notice notice-error';
	printf(
		'<div class="%1$s is-dismissible" style="border-left-width:6px;"><p style="font-size:15px;"><strong>%2$s</strong> %3$s</p></div>',
		esc_attr( $class ),
		esc_html__( 'JCP import:', 'jcp-core' ),
		esc_html( (string) $data['message'] )
	);
}
add_action( 'admin_notices', 'jcp_page_render_import_admin_notice' );

/**
 * Dedicated import submit (outside the Update form — reliable).
 */
function jcp_page_admin_post_import_writer_doc(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'jcp-core' ) );
	}
	check_admin_referer( 'jcp_import_writer_doc', 'jcp_import_writer_nonce' );

	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	$text    = '';
	if ( isset( $_POST['doc_text'] ) ) {
		$text = (string) wp_unslash( $_POST['doc_text'] );
	} elseif ( isset( $_POST['jcp_import_doc_text'] ) ) {
		$text = (string) wp_unslash( $_POST['jcp_import_doc_text'] );
	}

	if ( $text === '' && ! empty( $_FILES['doc_file']['tmp_name'] ) ) {
		$file = $_FILES['doc_file'];
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( $ext === 'docx' ) {
			$text = jcp_niche_extract_docx_text( (string) $file['tmp_name'] );
		} elseif ( $ext === 'txt' ) {
			$raw  = file_get_contents( (string) $file['tmp_name'] );
			$text = is_string( $raw ) ? $raw : '';
		}
	}

	$result = jcp_page_import_writer_document_to_post( $post_id, $text );
	jcp_page_set_import_notice( get_current_user_id(), $result );

	$redirect = get_edit_post_link( $post_id, 'raw' );
	if ( ! is_string( $redirect ) || $redirect === '' ) {
		$redirect = admin_url( 'edit.php?post_type=page' );
	}
	$redirect = add_query_arg(
		[
			'jcp_import' => ! empty( $result['ok'] ) ? '1' : '0',
			'jcp_chars'  => (string) strlen( $text ),
		],
		$redirect
	);
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_jcp_import_writer_doc', 'jcp_page_admin_post_import_writer_doc' );
