<?php
/**
 * Niche landing JSON schema helpers.
 *
 * @package JCP_Core
 */

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

	if ( function_exists( 'jcp_global_resolve_cta' ) ) {
		return jcp_global_resolve_cta( $label, $url, 'industry_' . $niche_key );
	}

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
 * Admin list: show live URL for each industry page.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function jcp_niche_admin_columns( array $columns ): array {
	$columns['jcp_niche_url'] = __( 'URL', 'jcp-core' );
	return $columns;
}
add_filter( 'manage_jcp_niche_landing_posts_columns', 'jcp_niche_admin_columns' );

/**
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 */
function jcp_niche_admin_column_content( string $column, int $post_id ): void {
	if ( $column !== 'jcp_niche_url' ) {
		return;
	}
	$url = get_permalink( $post_id );
	if ( ! $url ) {
		echo '—';
		return;
	}
	echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( wp_make_link_relative( $url ) ) . '</a>';
}
add_action( 'manage_jcp_niche_landing_posts_custom_column', 'jcp_niche_admin_column_content', 10, 2 );
