<?php
/**
 * Retire Early Bird / founding-crew promo wording from theme output and WP content.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace retired promo phrases in a string.
 *
 * @param string $text Raw text.
 */
function jcp_core_replace_retired_promo_copy( string $text ): string {
	if ( $text === '' ) {
		return $text;
	}

	$needs_early = stripos( $text, 'early bird' ) !== false || stripos( $text, 'founding crew' ) !== false || stripos( $text, 'EARLYBIRD' ) !== false;
	$needs_trial = stripos( $text, 'trial' ) !== false;
	if ( ! $needs_early && ! $needs_trial ) {
		return $text;
	}

	if ( $needs_early ) {
		$replacements = [
			'Get early bird pricing and unlock the benefits of turning real work into reviews, visibility, and trust that drives inbound demand.' =>
				'Start for free and turn real work into reviews, visibility, and trust that drives inbound demand.',
			'get early bird pricing and unlock the benefits of turning real work into reviews, visibility, and trust that drives inbound demand.' =>
				'Start for free and turn real work into reviews, visibility, and trust that drives inbound demand.',
			'Get early bird pricing' => 'Start for free',
			'get early bird pricing' => 'start for free',
			'early bird pricing'     => 'start-for-free pricing',
			'Early Bird pricing'     => 'Start-for-free pricing',
			'Early Bird:'            => 'Start free:',
			'Early Bird'             => 'Start free',
			'early bird'             => 'start for free',
			'EARLYBIRD'              => '',
			'founding crew'          => 'customers',
			'Founding crew'          => 'Customers',
			'Founding Crew'           => 'Customers',
		];
		$text = str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
	}

	if ( $needs_trial && function_exists( 'jcp_global_rewrite_trial_label' ) ) {
		$text = jcp_global_rewrite_trial_label( $text, 'prose' );
	}

	return $text;
}

/**
 * One-time: rewrite Pricing (and any matching) page content that still has retired promo copy.
 */
function jcp_core_migrate_retire_promo_copy_content(): void {
	if ( get_option( 'jcp_retired_early_bird_copy_v1' ) === '1' ) {
		return;
	}
	if ( ! function_exists( 'get_page_by_path' ) ) {
		return;
	}

	$ids = [];
	$page = get_page_by_path( 'pricing', OBJECT, 'page' );
	if ( $page instanceof WP_Post ) {
		$ids[] = (int) $page->ID;
	}

	$found = get_posts(
		[
			'post_type'              => 'page',
			'post_status'            => [ 'publish', 'draft', 'private' ],
			'posts_per_page'         => 20,
			's'                      => 'early bird',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);
	if ( is_array( $found ) ) {
		foreach ( $found as $id ) {
			$ids[] = (int) $id;
		}
	}

	$ids = array_values( array_unique( array_filter( $ids ) ) );
	foreach ( $ids as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$new_content = jcp_core_replace_retired_promo_copy( (string) $post->post_content );
		$new_excerpt = jcp_core_replace_retired_promo_copy( (string) $post->post_excerpt );
		$update      = [];
		if ( $new_content !== (string) $post->post_content ) {
			$update['post_content'] = $new_content;
		}
		if ( $new_excerpt !== (string) $post->post_excerpt ) {
			$update['post_excerpt'] = $new_excerpt;
		}
		if ( $update !== [] ) {
			$update['ID'] = $post_id;
			wp_update_post( wp_slash( $update ) );
		}

		foreach ( [ 'rank_math_description', 'rank_math_facebook_description', 'rank_math_twitter_description', '_yoast_wpseo_metadesc' ] as $meta_key ) {
			$meta = get_post_meta( $post_id, $meta_key, true );
			if ( ! is_string( $meta ) || $meta === '' ) {
				continue;
			}
			$scrubbed = jcp_core_replace_retired_promo_copy( $meta );
			if ( $scrubbed !== $meta ) {
				update_post_meta( $post_id, $meta_key, $scrubbed );
			}
		}
	}

	update_option( 'jcp_retired_early_bird_copy_v1', '1', false );
}
add_action( 'init', 'jcp_core_migrate_retire_promo_copy_content', 30 );

/**
 * One-time: scrub “free trial” CTA wording from Pricing and matching pages.
 */
function jcp_core_migrate_retire_trial_copy_content(): void {
	if ( get_option( 'jcp_retired_trial_copy_v1' ) === '1' ) {
		return;
	}
	if ( ! function_exists( 'get_page_by_path' ) ) {
		return;
	}

	$ids = [];
	$page = get_page_by_path( 'pricing', OBJECT, 'page' );
	if ( $page instanceof WP_Post ) {
		$ids[] = (int) $page->ID;
	}

	foreach ( [ 'free trial', '14-day trial', '14 day trial' ] as $needle ) {
		$found = get_posts(
			[
				'post_type'              => [ 'page', 'post' ],
				'post_status'            => [ 'publish', 'draft', 'private' ],
				'posts_per_page'         => 50,
				's'                      => $needle,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);
		if ( is_array( $found ) ) {
			foreach ( $found as $id ) {
				$ids[] = (int) $id;
			}
		}
	}

	$ids = array_values( array_unique( array_filter( $ids ) ) );
	foreach ( $ids as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$new_content = jcp_core_replace_retired_promo_copy( (string) $post->post_content );
		$new_excerpt = jcp_core_replace_retired_promo_copy( (string) $post->post_excerpt );
		$update      = [];
		if ( $new_content !== (string) $post->post_content ) {
			$update['post_content'] = $new_content;
		}
		if ( $new_excerpt !== (string) $post->post_excerpt ) {
			$update['post_excerpt'] = $new_excerpt;
		}
		if ( $update !== [] ) {
			$update['ID'] = $post_id;
			wp_update_post( wp_slash( $update ) );
		}

		foreach ( [ 'rank_math_description', 'rank_math_facebook_description', 'rank_math_twitter_description', '_yoast_wpseo_metadesc' ] as $meta_key ) {
			$meta = get_post_meta( $post_id, $meta_key, true );
			if ( ! is_string( $meta ) || $meta === '' ) {
				continue;
			}
			$scrubbed = jcp_core_replace_retired_promo_copy( $meta );
			if ( $scrubbed !== $meta ) {
				update_post_meta( $post_id, $meta_key, $scrubbed );
			}
		}
	}

	update_option( 'jcp_retired_trial_copy_v1', '1', false );
}
add_action( 'init', 'jcp_core_migrate_retire_trial_copy_content', 31 );
