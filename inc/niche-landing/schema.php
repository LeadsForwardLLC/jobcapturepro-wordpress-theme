<?php
/**
 * Niche landing JSON schema helpers.
 *
 * @package JCP_Core
 */

/**
 * Load default content JSON for a preset niche.
 *
 * @param string $preset e.g. plumbing.
 * @return array<string, mixed>
 */
function jcp_niche_load_preset( string $preset ): array {
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
 * Resolve CTA URLs (empty url = onboarding with UTM).
 *
 * @param array<string, mixed> $cta CTA block with label, url.
 * @param string               $niche_key For utm_content.
 * @return array{label: string, url: string}
 */
function jcp_niche_resolve_cta( array $cta, string $niche_key ): array {
	$label = isset( $cta['label'] ) ? (string) $cta['label'] : '';
	$url   = isset( $cta['url'] ) ? trim( (string) $cta['url'] ) : '';

	if ( $url === '' && stripos( $label, 'trial' ) !== false ) {
		$utm = function_exists( 'jcp_core_onboarding_utm_defaults' )
			? jcp_core_onboarding_utm_defaults( 'industry_' . $niche_key )
			: [ 'utm_content' => 'industry_' . $niche_key ];
		$url = function_exists( 'jcp_core_onboarding_app_url_raw' )
			? jcp_core_onboarding_app_url_raw( $utm )
			: home_url( '/demo' );
	}

	if ( $url === '' ) {
		$url = home_url( '/demo' );
	} elseif ( preg_match( '#^https?://#i', $url ) ) {
		return [
			'label' => $label,
			'url'   => $url,
		];
	}

	if ( $url !== '' && $url[0] === '/' && strpos( $url, '//' ) !== 0 ) {
		$url = home_url( $url );
	}

	return [
		'label' => $label,
		'url'   => $url,
	];
}

/**
 * Whether a post uses the JSON landing content system.
 *
 * @param int|null $post_id Post ID (defaults to queried object).
 */
function jcp_niche_is_content_page( ?int $post_id = null ): bool {
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
	if ( $post->post_type === 'jcp_niche_landing' ) {
		return true;
	}
	return $post->post_type === 'page' && get_page_template_slug( $id ) === 'page-referral-program.php';
}

/**
 * Get parsed content for a niche landing post.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_niche_get_content( int $post_id ): array {
	$raw = get_post_meta( $post_id, jcp_niche_content_meta_key(), true );
	if ( is_string( $raw ) && $raw !== '' ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) && ! empty( $decoded ) ) {
			return $decoded;
		}
	}

	$slug = get_post_field( 'post_name', $post_id );
	if ( $slug === 'plumbing' ) {
		return jcp_niche_load_preset( 'plumbing' );
	}
	if ( $slug === 'hvac' ) {
		return jcp_niche_load_preset( 'hvac' );
	}
	if ( $slug === 'referral-program' || get_page_template_slug( $post_id ) === 'page-referral-program.php' ) {
		return jcp_niche_load_preset( 'referral-program' );
	}

	return [];
}

/**
 * Save JSON content for a post.
 *
 * @param int                  $post_id Post ID.
 * @param array<string, mixed> $content Content array.
 */
function jcp_niche_save_content( int $post_id, array $content ): void {
	update_post_meta(
		$post_id,
		jcp_niche_content_meta_key(),
		wp_json_encode( $content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
	);
}

/**
 * SEO title for a niche landing post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function jcp_niche_seo_title( int $post_id ): string {
	$c = jcp_niche_get_content( $post_id );
	if ( ! empty( $c['seo']['title'] ) ) {
		return (string) $c['seo']['title'];
	}
	return get_the_title( $post_id );
}

/**
 * Meta description for a niche landing post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function jcp_niche_meta_description( int $post_id ): string {
	$c = jcp_niche_get_content( $post_id );
	if ( ! empty( $c['seo']['meta_description'] ) ) {
		return (string) $c['seo']['meta_description'];
	}
	$hero = $c['hero']['subheadline'] ?? '';
	return is_string( $hero ) ? wp_strip_all_tags( $hero ) : '';
}

/**
 * SEO title for the industries hub archive.
 */
function jcp_niche_archive_seo_title(): string {
	return __( 'Marketing Software for Home Service Contractors by Trade', 'jcp-core' ) . ' | ' . get_bloginfo( 'name' );
}

/**
 * Meta description for the industries hub archive.
 */
function jcp_niche_archive_meta_description(): string {
	return __( 'Browse marketing software built for your trade. See how JobCapturePro helps plumbers, roofers, HVAC companies, electricians, and home service contractors turn completed jobs into Google visibility, website proof, reviews, and more booked work.', 'jcp-core' );
}

/**
 * Output meta description in head.
 */
function jcp_niche_output_meta_description(): void {
	if ( is_post_type_archive( 'jcp_niche_landing' ) ) {
		$desc = jcp_niche_archive_meta_description();
		if ( $desc !== '' ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		return;
	}
	if ( ! jcp_niche_is_content_page() ) {
		return;
	}
	$desc = jcp_niche_meta_description( (int) get_queried_object_id() );
	if ( $desc === '' ) {
		return;
	}
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
}
add_action( 'wp_head', 'jcp_niche_output_meta_description', 2 );

/**
 * Filter document title for niche landings.
 *
 * @param array<int, string> $parts Title parts.
 * @return array<int, string>
 */
function jcp_niche_document_title( array $parts ): array {
	if ( jcp_niche_is_content_page() && is_singular() ) {
		$parts['title'] = jcp_niche_seo_title( (int) get_queried_object_id() );
	}
	if ( is_post_type_archive( 'jcp_niche_landing' ) ) {
		$parts['title'] = jcp_niche_archive_seo_title();
	}
	return $parts;
}
add_filter( 'document_title_parts', 'jcp_niche_document_title' );

/**
 * Override full document title (wins over SEO plugins in many cases).
 *
 * @param string $title Document title.
 * @return string
 */
function jcp_niche_pre_get_document_title( string $title ): string {
	if ( jcp_niche_is_content_page() && is_singular() ) {
		return jcp_niche_seo_title( (int) get_queried_object_id() );
	}
	if ( is_post_type_archive( 'jcp_niche_landing' ) ) {
		return jcp_niche_archive_seo_title();
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'jcp_niche_pre_get_document_title', 100 );
