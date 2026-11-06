<?php
/**
 * Normalize trial/signup CTA labels to “Start Free Trial” in page block documents.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recursively rewrite known trial CTA strings in page content.
 *
 * @param array<string, mixed> $content Block document.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_page_upgrade_start_free_trial_cta_labels( array $content, int $post_id ): array {
	unset( $post_id );
	if ( ! function_exists( 'jcp_global_rewrite_trial_label' ) ) {
		return $content;
	}

	$should_rewrite = static function ( string $key, string $value ): bool {
		if ( $value === '' ) {
			return false;
		}
		if ( preg_match( '/personalized\s+demo|interactive\s+demo|live\s+demo|launch\s+.*demo|view\s+.*demo/i', $value ) ) {
			return false;
		}
		if ( preg_match( '/^(label|cta_label|primary_label)$/i', $key ) ) {
			return (bool) preg_match(
				'/start\s+for\s+free|start\s+free(?!\s+trial)|get\s+started(\s+free)?|sign\s+up\s+for\s+free|free\s+trial|start\s+a\s+free/i',
				$value
			);
		}
		if ( preg_match( '/trust_line|cta_note|cta_microcopy|microcopy|cta_label|primary_label/i', $key ) ) {
			return (bool) preg_match(
				'/start\s+for\s+free|start\s+free(?!\s+trial)|get\s+started\s+free|sign\s+up\s+for\s+free/i',
				$value
			);
		}
		return false;
	};

	$walk = static function ( $node ) use ( &$walk, $should_rewrite ) {
		if ( ! is_array( $node ) ) {
			return $node;
		}
		foreach ( $node as $key => $value ) {
			if ( is_string( $value ) && is_string( $key ) && $should_rewrite( $key, $value ) ) {
				$kind = 'prose';
				if ( preg_match( '/^(label|cta_label|primary_label)$/i', $key ) ) {
					$kind = 'button';
				} elseif ( preg_match( '/headline/i', $key ) ) {
					$kind = 'headline';
				}
				$node[ $key ] = jcp_global_rewrite_trial_label( $value, $kind );
			} elseif ( is_array( $value ) ) {
				$node[ $key ] = $walk( $value );
			}
		}
		return $node;
	};

	return $walk( $content );
}
