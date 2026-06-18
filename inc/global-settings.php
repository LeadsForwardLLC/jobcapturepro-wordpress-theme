<?php
/**
 * Sitewide JCP settings (banner, signup URLs, nav CTAs).
 *
 * @package JCP_Core
 */

/**
 * Option key for merged global settings.
 */
function jcp_global_settings_option_key(): string {
	return 'jcp_global_settings';
}

/**
 * Default global settings.
 *
 * @return array<string, mixed>
 */
function jcp_global_settings_defaults(): array {
	return [
		'banner'  => [
			'enabled'     => true,
			'visibility'  => 'marketing',
			'headline'    => 'Early Bird:',
			'text'        => 'Get the Enterprise plan (normally $399/mo) for $125/mo.',
			'code'        => 'EARLYBIRD',
			'cta_label'   => 'Claim offer',
			'cta_url'     => '',
			'coupon'      => 'earlybird',
			'utm_content' => 'sitewide_banner',
		],
		'signup'  => [
			'base_url'   => 'https://app.jobcapturepro.com/onboarding',
			'session_id' => '75ad8454-312e-4224-95b7-8f48f5cd0277',
			'step'       => '1',
		],
		'nav_cta' => [
			'primary_label'   => 'Get Started',
			'primary_url'     => '',
			'secondary_label' => 'Online Demo',
			'secondary_url'   => '/demo',
		],
		'contact' => [
			'support_email' => 'hello@jobcapturepro.com',
		],
	];
}

/**
 * Deep-merge user settings with defaults.
 *
 * @return array<string, mixed>
 */
function jcp_global_settings(): array {
	$stored = get_option( jcp_global_settings_option_key(), [] );
	if ( ! is_array( $stored ) ) {
		$stored = [];
	}
	return jcp_global_settings_merge( jcp_global_settings_defaults(), $stored );
}

/**
 * @param array<string, mixed> $defaults Defaults.
 * @param array<string, mixed> $custom   Stored values.
 * @return array<string, mixed>
 */
function jcp_global_settings_merge( array $defaults, array $custom ): array {
	$out = $defaults;
	foreach ( $custom as $key => $value ) {
		if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) ) {
			$out[ $key ] = jcp_global_settings_merge( $defaults[ $key ], $value );
		} else {
			$out[ $key ] = $value;
		}
	}
	return $out;
}

/**
 * Whether the sitewide banner should render on this request.
 *
 * @param array<string, bool> $pages Page detection from jcp_core_get_page_detection().
 */
function jcp_global_should_show_banner( array $pages ): bool {
	$banner = jcp_global_settings()['banner'] ?? [];
	if ( empty( $banner['enabled'] ) ) {
		return false;
	}
	$visibility = (string) ( $banner['visibility'] ?? 'marketing' );
	if ( $visibility === 'off' ) {
		return false;
	}
	if ( $visibility === 'all' ) {
		return true;
	}
	return empty( $pages['is_prototype'] )
		&& empty( $pages['is_wp_plugin_prototype'] )
		&& empty( $pages['is_demo'] )
		&& empty( $pages['is_directory'] )
		&& empty( $pages['is_company'] )
		&& empty( $pages['is_estimate'] )
		&& empty( $pages['is_ui_library'] );
}

/**
 * Build banner CTA URL from settings.
 *
 * @param array<string, mixed> $banner Banner settings slice.
 */
function jcp_global_banner_cta_url( array $banner ): string {
	$url = trim( (string) ( $banner['cta_url'] ?? '' ) );
	if ( $url !== '' ) {
		if ( preg_match( '#^https?://#i', $url ) ) {
			return esc_url( $url );
		}
		return esc_url( home_url( $url ) );
	}
	$utm_content = (string) ( $banner['utm_content'] ?? 'sitewide_banner' );
	$extra       = function_exists( 'jcp_core_onboarding_utm_defaults' )
		? jcp_core_onboarding_utm_defaults( $utm_content )
		: [ 'utm_content' => $utm_content ];
	$coupon = trim( (string) ( $banner['coupon'] ?? '' ) );
	if ( $coupon !== '' ) {
		$extra['coupon'] = $coupon;
		$extra['promo']  = $coupon;
	}
	return function_exists( 'jcp_core_onboarding_app_url' )
		? jcp_core_onboarding_app_url( $extra )
		: esc_url( home_url( '/pricing' ) );
}

/**
 * Resolve a CTA pair (label + absolute URL).
 *
 * @param string               $label       Button label.
 * @param string               $url         Relative, absolute, or empty.
 * @param string               $utm_content Analytics key when URL empty and label implies signup.
 * @param array<string, mixed> $query_extra Extra signup query args.
 * @return array{label: string, url: string}
 */
function jcp_global_resolve_cta( string $label, string $url, string $utm_content = '', array $query_extra = [] ): array {
	$label = trim( $label );
	$url   = trim( $url );

	if ( $url === '' && $label !== '' && preg_match( '/trial|sign\s*up|get\s*started|claim/i', $label ) ) {
		$utm = $utm_content !== '' && function_exists( 'jcp_core_onboarding_utm_defaults' )
			? jcp_core_onboarding_utm_defaults( $utm_content )
			: ( $utm_content !== '' ? [ 'utm_content' => $utm_content ] : [] );
		$url = function_exists( 'jcp_core_onboarding_app_url_raw' )
			? jcp_core_onboarding_app_url_raw( array_merge( $utm, $query_extra ) )
			: home_url( '/demo' );
	} elseif ( $url === '' ) {
		$url = home_url( '/demo' );
	} elseif ( ! preg_match( '#^https?://#i', $url ) ) {
		$url = home_url( $url );
	}

	return [
		'label' => $label,
		'url'   => $url,
	];
}

/**
 * Nav bar CTAs: global defaults with optional per-page override from page content.
 *
 * @param int|null $post_id Post ID.
 * @return array{primary: array{label: string, url: string}, secondary: array{label: string, url: string}}
 */
function jcp_global_resolve_nav_ctas( ?int $post_id = null ): array {
	$global = jcp_global_settings()['nav_cta'] ?? [];
	$primary_label   = (string) ( $global['primary_label'] ?? 'Get Started' );
	$primary_url     = (string) ( $global['primary_url'] ?? '' );
	$secondary_label = (string) ( $global['secondary_label'] ?? 'Online Demo' );
	$secondary_url   = (string) ( $global['secondary_url'] ?? '/demo' );

	if ( $post_id && $post_id > 0 && function_exists( 'jcp_page_get_content_flat' ) ) {
		$content = jcp_page_get_content_flat( $post_id );
		$override = $content['nav_cta'] ?? [];
		if ( is_array( $override ) ) {
			if ( ! empty( $override['primary_label'] ) ) {
				$primary_label = (string) $override['primary_label'];
			}
			if ( array_key_exists( 'primary_url', $override ) && (string) $override['primary_url'] !== '' ) {
				$primary_url = (string) $override['primary_url'];
			}
			if ( ! empty( $override['secondary_label'] ) ) {
				$secondary_label = (string) $override['secondary_label'];
			}
			if ( array_key_exists( 'secondary_url', $override ) && (string) $override['secondary_url'] !== '' ) {
				$secondary_url = (string) $override['secondary_url'];
			}
		}
	}

	return [
		'primary'   => jcp_global_resolve_cta( $primary_label, $primary_url, 'nav_get_started' ),
		'secondary' => jcp_global_resolve_cta( $secondary_label, $secondary_url, 'nav_online_demo' ),
	];
}

/**
 * Sanitize a URL field that may be absolute or site-relative.
 *
 * @param string $url Raw URL.
 */
function jcp_global_sanitize_url_field( string $url ): string {
	$url = trim( $url );
	if ( $url === '' ) {
		return '';
	}
	if ( $url[0] === '/' && strpos( $url, '//' ) !== 0 ) {
		return sanitize_text_field( $url );
	}
	return esc_url_raw( $url );
}

/**
 * Settings safe for frontend scripts (no secrets).
 *
 * @return array<string, mixed>
 */
function jcp_global_settings_public(): array {
	$settings = jcp_global_settings();
	return [
		'banner'  => $settings['banner'] ?? [],
		'nav_cta' => $settings['nav_cta'] ?? [],
		'contact' => $settings['contact'] ?? [],
	];
}
