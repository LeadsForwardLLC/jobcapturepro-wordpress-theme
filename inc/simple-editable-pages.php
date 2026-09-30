<?php
/**
 * Simple editable marketing pages (Support, Pricing, Contact Success).
 *
 * Keeps PHP/JS templates but stores hero/CTA copy in `_jcp_page_content`
 * so the front-end editor can inline-edit via data-jcp-path.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Templates that participate in the simple editable-page system.
 *
 * @return array<int, string>
 */
function jcp_simple_editable_templates(): array {
	return [
		'page-support.php',
		'page-pricing.php',
		'page-contact-success.php',
	];
}

/**
 * Whether a page template is simple-editable.
 *
 * @param int $post_id Post ID.
 */
function jcp_page_is_simple_editable( int $post_id ): bool {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return false;
	}
	return in_array( get_page_template_slug( $post_id ), jcp_simple_editable_templates(), true );
}

/**
 * Expand content-page detection for simple editable templates.
 *
 * @param bool     $is_content Current flag.
 * @param int|null $post_id    Post ID.
 */
function jcp_simple_editable_is_content_page( bool $is_content, ?int $post_id = null ): bool {
	if ( $is_content ) {
		return true;
	}
	$id = $post_id ?? ( is_singular() ? (int) get_queried_object_id() : 0 );
	if ( $id <= 0 ) {
		return false;
	}
	return jcp_page_is_simple_editable( $id );
}

/**
 * Resolve post ID for editor when fallback routes have no queried object.
 */
function jcp_simple_editable_editor_post_id( int $post_id ): int {
	if ( $post_id > 0 ) {
		return $post_id;
	}
	if ( ! is_user_logged_in() ) {
		return 0;
	}
	$path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$map  = [
		'support'         => 'support',
		'contact'         => 'support',
		'pricing'         => 'pricing',
		'contact-success' => 'contact-success',
	];
	$segment = strpos( $path, '/' ) !== false ? strtok( $path, '/' ) : $path;
	if ( ! isset( $map[ $segment ] ) ) {
		return 0;
	}
	$page = get_page_by_path( $map[ $segment ], OBJECT, 'page' );
	if ( ! $page instanceof WP_Post ) {
		return 0;
	}
	if ( ! jcp_page_is_simple_editable( (int) $page->ID ) && ! function_exists( 'jcp_page_is_content_page' ) ) {
		return 0;
	}
	if ( function_exists( 'jcp_page_is_content_page' ) && ! jcp_page_is_content_page( (int) $page->ID ) && ! jcp_page_is_simple_editable( (int) $page->ID ) ) {
		return 0;
	}
	return current_user_can( 'edit_post', (int) $page->ID ) ? (int) $page->ID : 0;
}

/**
 * Default structured content for simple editable pages.
 *
 * @param array<string, mixed> $content Existing default.
 * @param int                  $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_simple_editable_default_content( array $content, int $post_id ): array {
	if ( ! empty( $content ) ) {
		return $content;
	}
	$template = get_page_template_slug( $post_id );
	$slug     = (string) get_post_field( 'post_name', $post_id );

	if ( $template === 'page-support.php' || $slug === 'support' ) {
		return jcp_simple_editable_support_document( $post_id );
	}
	if ( $template === 'page-pricing.php' || $slug === 'pricing' ) {
		return jcp_simple_editable_pricing_document( $post_id );
	}
	if ( $template === 'page-contact-success.php' || $slug === 'contact-success' ) {
		return jcp_simple_editable_contact_success_document( $post_id );
	}
	return $content;
}

/**
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_simple_editable_support_document( int $post_id ): array {
	$title = trim( (string) get_the_title( $post_id ) );
	if ( $title === '' ) {
		$title = __( 'Support', 'jcp-core' );
	}
	return [
		'version'    => 1,
		'page_kind'  => 'marketing',
		'page_key'   => 'support',
		'page_label' => $title,
		'settings'   => [
			'noindex' => false,
		],
		'blocks'     => [
			[
				'id'    => 'support-hero',
				'type'  => 'hero',
				'props' => [
					'eyebrow'            => __( 'Support', 'jcp-core' ),
					'h1'                 => $title,
					'subheadline'        => __( 'Start with the Help Center — most answers are there. If you still need us, send a message and we’ll get back within one business day.', 'jcp-core' ),
					'show_visual'        => false,
					'show_eyebrow'       => true,
					'cta_primary'        => [
						'label' => __( 'Message support', 'jcp-core' ),
						'url'   => '#contact-form',
					],
					'cta_secondary'      => [
						'label' => __( 'Browse Help Center', 'jcp-core' ),
						'url'   => home_url( '/help/' ),
					],
					'show_cta_primary'   => true,
					'show_cta_secondary' => true,
				],
			],
		],
	];
}

/**
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_simple_editable_pricing_document( int $post_id ): array {
	$title = trim( (string) get_the_title( $post_id ) );
	if ( $title === '' || strcasecmp( $title, 'Pricing' ) === 0 ) {
		$title = __( 'Choose the plan that matches your growth', 'jcp-core' );
	}
	return [
		'version'    => 1,
		'page_kind'  => 'marketing',
		'page_key'   => 'pricing',
		'page_label' => __( 'Pricing', 'jcp-core' ),
		'blocks'     => [
			[
				'id'    => 'pricing-hero',
				'type'  => 'hero',
				'props' => [
					'h1'          => $title,
					'subheadline' => __( 'Each tier aligns to business maturity and visibility goals. Start Free Trial and turn real work into reviews, visibility, and trust that drives inbound demand.', 'jcp-core' ),
					'show_visual' => false,
				],
			],
		],
	];
}

/**
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_simple_editable_contact_success_document( int $post_id ): array {
	return [
		'version'    => 1,
		'page_kind'  => 'marketing',
		'page_key'   => 'contact-success',
		'page_label' => __( 'Message sent', 'jcp-core' ),
		'settings'   => [
			'noindex' => true,
		],
		'blocks'     => [
			[
				'id'    => 'contact-success-hero',
				'type'  => 'hero',
				'props' => [
					'h1'                 => __( 'Message sent', 'jcp-core' ),
					'subheadline'        => __( 'Thanks for reaching out. We’ll review your note and get back within 24–48 hours.', 'jcp-core' ),
					'show_visual'        => false,
					'cta_primary'        => [
						'label' => __( 'Back to Support', 'jcp-core' ),
						'url'   => home_url( '/support/' ),
					],
					'cta_secondary'      => [
						'label' => __( 'Browse Help Center', 'jcp-core' ),
						'url'   => home_url( '/help/' ),
					],
					'show_cta_primary'   => true,
					'show_cta_secondary' => true,
				],
			],
		],
	];
}

/**
 * Topic keys and default headlines for support thank-you.
 *
 * @return array<string, string>
 */
function jcp_contact_success_topic_headlines(): array {
	return [
		'getting-started'  => __( 'You’re on your way', 'jcp-core' ),
		'technical-issue'  => __( 'We’re on it', 'jcp-core' ),
		'feature-request'  => __( 'Thanks for the idea', 'jcp-core' ),
		'billing'          => __( 'Billing note received', 'jcp-core' ),
		'general-question' => __( 'Got your question', 'jcp-core' ),
	];
}

/**
 * Normalize Topic field value to a slug key.
 *
 * @param string $raw Raw topic from query or form.
 */
function jcp_contact_success_normalize_topic( string $raw ): string {
	$raw = strtolower( trim( wp_strip_all_tags( $raw ) ) );
	if ( $raw === '' || $raw === '- select -' || $raw === 'select' ) {
		return '';
	}
	$map = [
		'getting started'  => 'getting-started',
		'getting-started'  => 'getting-started',
		'technical issue'  => 'technical-issue',
		'technical-issue'  => 'technical-issue',
		'feature request'  => 'feature-request',
		'feature-request'  => 'feature-request',
		'billing'          => 'billing',
		'general question' => 'general-question',
		'general-question' => 'general-question',
	];
	if ( isset( $map[ $raw ] ) ) {
		return $map[ $raw ];
	}
	$slug = sanitize_title( $raw );
	$keys = array_keys( jcp_contact_success_topic_headlines() );
	return in_array( $slug, $keys, true ) ? $slug : '';
}

/**
 * Resolve thank-you headline for a topic (editable default + topic override).
 *
 * @param string               $topic Topic key.
 * @param array<string, mixed> $flat  Flat page content.
 */
function jcp_contact_success_resolve_headline( string $topic, array $flat ): string {
	$topics = jcp_contact_success_topic_headlines();
	if ( $topic !== '' && isset( $topics[ $topic ] ) ) {
		$custom = trim( (string) ( $flat['topics'][ $topic ]['headline'] ?? '' ) );
		return $custom !== '' ? $custom : $topics[ $topic ];
	}
	$default = trim( (string) ( $flat['hero']['h1'] ?? '' ) );
	return $default !== '' ? $default : __( 'Message sent', 'jcp-core' );
}

/**
 * Always noindex the contact-success confirmation page.
 */
function jcp_contact_success_noindex(): void {
	$pages = function_exists( 'jcp_core_get_page_detection' ) ? jcp_core_get_page_detection() : [];
	$path  = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( empty( $pages['is_contact_success'] ) && $path !== 'contact-success' ) {
		return;
	}
	echo '<meta name="robots" content="noindex, follow">' . "\n";
}
add_action( 'wp_head', 'jcp_contact_success_noindex', 1 );

/**
 * Ensure WP pages exist for Support / Pricing / Contact Success.
 */
function jcp_simple_editable_ensure_pages(): void {
	if ( get_option( 'jcp_simple_editable_pages_v1', '' ) === '1' ) {
		return;
	}

	$defs = [
		[
			'slug'     => 'contact-success',
			'title'    => 'Message sent',
			'template' => 'page-contact-success.php',
		],
		[
			'slug'     => 'pricing',
			'title'    => 'Pricing',
			'template' => 'page-pricing.php',
		],
		[
			'slug'     => 'support',
			'title'    => 'Support',
			'template' => 'page-support.php',
		],
	];

	foreach ( $defs as $def ) {
		$page = get_page_by_path( $def['slug'], OBJECT, 'page' );
		if ( ! $page instanceof WP_Post ) {
			$id = wp_insert_post(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $def['slug'],
					'post_title'   => $def['title'],
					'post_content' => '',
				],
				true
			);
			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}
			$page_id = (int) $id;
		} else {
			$page_id = (int) $page->ID;
		}
		update_post_meta( $page_id, '_wp_page_template', $def['template'] );
		if ( function_exists( 'jcp_page_get_content_raw' ) && empty( jcp_page_get_content_raw( $page_id ) ) && function_exists( 'jcp_page_save_content' ) ) {
			$doc = jcp_simple_editable_default_content( [], $page_id );
			if ( ! empty( $doc ) ) {
				jcp_page_save_content( $page_id, $doc );
			}
		}
	}

	update_option( 'jcp_simple_editable_pages_v1', '1' );
}
add_action( 'init', 'jcp_simple_editable_ensure_pages', 35 );
