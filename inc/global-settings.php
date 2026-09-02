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
			'headline'    => 'Start Free Trial:',
			'text'        => 'Start Free Trial — no credit card required.',
			'code'        => '',
			'cta_label'   => 'Start Free Trial',
			'cta_url'     => '',
			'coupon'      => '',
			'utm_content' => 'sitewide_banner',
		],
		'signup'  => [
			'base_url'   => 'https://app.jobcapturepro.com/onboarding',
			'session_id' => '75ad8454-312e-4224-95b7-8f48f5cd0277',
			'step'       => '1',
		],
		'nav_cta' => [
			'primary_label'   => 'Start Free Trial',
			'primary_url'     => '',
			'secondary_label' => 'Login',
			'secondary_url'   => '',
		],
		'header_nav' => [
			// Overrides keyed by item id — merged onto jcp_global_header_nav_defaults() at resolve time.
			'overrides' => [],
		],
		'contact' => [
			'support_email' => 'support@jobcapturepro.com',
			'support_phone' => '(941) 941-9506',
		],
		'fluent_forms' => [
			'enabled'            => true,
			'default_shortcode'  => '',
			'mount_global_modal' => false,
		],
	];
}

/**
 * Canonical main-header link structure (single source of truth).
 * Admin can override label / url / enabled per id; new defaults appear automatically.
 *
 * @return array<int, array<string, mixed>>
 */
function jcp_global_header_nav_defaults(): array {
	return [
		[
			'id'          => 'how_it_works',
			'type'        => 'link',
			'label'       => 'How it works',
			'url'         => '',
			'home_anchor' => '#how-it-works',
			'enabled'     => true,
		],
		[
			'id'          => 'features',
			'type'        => 'features_mega',
			'label'       => 'Features',
			'url'         => '',
			'home_anchor' => '#features',
			'enabled'     => true,
		],
		[
			'id'        => 'by_trade',
			'type'      => 'trade_mega',
			'label'     => 'By Trade',
			'url'       => '/industries/',
			'data_page' => 'industries',
			'enabled'   => true,
		],
		[
			'id'        => 'pricing',
			'type'      => 'link',
			'label'     => 'Pricing',
			'url'       => '/pricing',
			'data_page' => 'pricing',
			'enabled'   => true,
		],
		[
			'id'       => 'resources',
			'type'     => 'dropdown',
			'label'    => 'Resources',
			'enabled'  => true,
			'children' => [
				[
					'id'        => 'blog',
					'label'     => 'Blog',
					'url'       => '/blog',
					'data_page' => 'blog',
					'enabled'   => true,
				],
				[
					'id'        => 'help',
					'label'     => 'Help Center',
					'url'       => '/help',
					'data_page' => 'help',
					'enabled'   => true,
				],
				[
					'id'        => 'support',
					'label'     => 'Support',
					'url'       => '/support',
					'data_page' => 'support',
					'enabled'   => true,
				],
				[
					'id'        => 'referral',
					'label'     => 'Referral Program',
					'url'       => '/referral-program',
					'data_page' => 'referral-program',
					'enabled'   => true,
				],
			],
		],
	];
}

/**
 * Resolve a nav URL (relative → absolute; empty + home_anchor → home hash).
 *
 * @param array<string, mixed> $item Nav item.
 */
function jcp_global_resolve_header_nav_url( array $item ): string {
	$url         = trim( (string) ( $item['url'] ?? '' ) );
	$home_anchor = trim( (string) ( $item['home_anchor'] ?? '' ) );
	$is_home     = function_exists( 'jcp_core_get_page_detection' )
		? ! empty( jcp_core_get_page_detection()['is_home'] )
		: is_front_page();

	if ( $home_anchor !== '' && ( $url === '' || $url[0] === '#' ) ) {
		$anchor = $home_anchor[0] === '#' ? $home_anchor : '#' . $home_anchor;
		return $is_home ? $anchor : ( home_url( '/' ) . $anchor );
	}
	if ( $url === '' ) {
		return home_url( '/' );
	}
	if ( $url[0] === '#' ) {
		return $is_home ? $url : ( home_url( '/' ) . $url );
	}
	if ( $url[0] === '/' && strpos( $url, '//' ) !== 0 ) {
		return home_url( $url );
	}
	if ( preg_match( '#^https?://#i', $url ) ) {
		return $url;
	}
	return home_url( '/' . ltrim( $url, '/' ) );
}

/**
 * Merge stored overrides onto canonical header nav defaults (by id — no drift).
 *
 * @return array<int, array<string, mixed>>
 */
function jcp_global_resolve_header_nav(): array {
	$defaults  = jcp_global_header_nav_defaults();
	$overrides = jcp_global_settings()['header_nav']['overrides'] ?? [];
	if ( ! is_array( $overrides ) ) {
		$overrides = [];
	}

	$out = [];
	foreach ( $defaults as $item ) {
		$id = (string) ( $item['id'] ?? '' );
		if ( $id === '' ) {
			continue;
		}
		$over = is_array( $overrides[ $id ] ?? null ) ? $overrides[ $id ] : [];
		if ( array_key_exists( 'label', $over ) && trim( (string) $over['label'] ) !== '' ) {
			$item['label'] = sanitize_text_field( (string) $over['label'] );
		}
		if ( array_key_exists( 'url', $over ) ) {
			$item['url'] = jcp_global_sanitize_url_field( (string) $over['url'] );
		}
		if ( array_key_exists( 'enabled', $over ) ) {
			$item['enabled'] = (string) $over['enabled'] === '1' || $over['enabled'] === 1 || $over['enabled'] === true;
		}
		if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
			$child_over = is_array( $over['children'] ?? null ) ? $over['children'] : [];
			$children   = [];
			foreach ( $item['children'] as $child ) {
				if ( ! is_array( $child ) ) {
					continue;
				}
				$cid = (string) ( $child['id'] ?? '' );
				$co  = is_array( $child_over[ $cid ] ?? null ) ? $child_over[ $cid ] : [];
				if ( array_key_exists( 'label', $co ) && trim( (string) $co['label'] ) !== '' ) {
					$child['label'] = sanitize_text_field( (string) $co['label'] );
				}
				if ( array_key_exists( 'url', $co ) ) {
					$child['url'] = jcp_global_sanitize_url_field( (string) $co['url'] );
				}
				if ( array_key_exists( 'enabled', $co ) ) {
					$child['enabled'] = (string) $co['enabled'] === '1' || $co['enabled'] === 1 || $co['enabled'] === true;
				}
				$child['resolved_url'] = jcp_global_resolve_header_nav_url( $child );
				$children[]            = $child;
			}
			$item['children'] = $children;
		}
		$item['resolved_url'] = jcp_global_resolve_header_nav_url( $item );
		$out[]                = $item;
	}

	return $out;
}

/**
 * Sanitize header nav overrides from admin POST.
 *
 * @param mixed $raw Posted header_nav data.
 * @return array{overrides: array<string, mixed>}
 */
function jcp_global_sanitize_header_nav( $raw ): array {
	$overrides = [];
	$items     = [];
	if ( is_array( $raw ) ) {
		$items = is_array( $raw['items'] ?? null ) ? $raw['items'] : $raw;
	}
	$defaults_by_id = [];
	foreach ( jcp_global_header_nav_defaults() as $def ) {
		$defaults_by_id[ (string) $def['id'] ] = $def;
	}

	foreach ( $items as $id => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$id = sanitize_key( (string) ( $row['id'] ?? $id ) );
		if ( $id === '' || ! isset( $defaults_by_id[ $id ] ) ) {
			continue;
		}
		$def = $defaults_by_id[ $id ];
		$entry = [
			'label'   => sanitize_text_field( (string) ( $row['label'] ?? $def['label'] ) ),
			'url'     => jcp_global_sanitize_url_field( (string) ( $row['url'] ?? ( $def['url'] ?? '' ) ) ),
			'enabled' => (string) ( $row['enabled'] ?? '0' ) === '1',
		];
		if ( ( $def['type'] ?? '' ) === 'dropdown' && ! empty( $def['children'] ) ) {
			$entry['children'] = [];
			$posted_children   = is_array( $row['children'] ?? null ) ? $row['children'] : [];
			foreach ( $def['children'] as $cdef ) {
				$cid = (string) ( $cdef['id'] ?? '' );
				$crow = is_array( $posted_children[ $cid ] ?? null ) ? $posted_children[ $cid ] : [];
				$entry['children'][ $cid ] = [
					'label'   => sanitize_text_field( (string) ( $crow['label'] ?? $cdef['label'] ) ),
					'url'     => jcp_global_sanitize_url_field( (string) ( $crow['url'] ?? ( $cdef['url'] ?? '' ) ) ),
					'enabled' => (string) ( $crow['enabled'] ?? '0' ) === '1',
				];
			}
		}
		$overrides[ $id ] = $entry;
	}

	return [ 'overrides' => $overrides ];
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
	$settings = jcp_global_settings_merge( jcp_global_settings_defaults(), $stored );
	return jcp_global_settings_scrub_legacy_promo_copy( $settings );
}

/**
 * Strip retired promo banner wording (legacy Early Bird / founding-crew campaigns).
 *
 * @param array<string, mixed> $settings Merged settings.
 * @return array<string, mixed>
 */
function jcp_global_settings_scrub_legacy_promo_copy( array $settings ): array {
	$banner = is_array( $settings['banner'] ?? null ) ? $settings['banner'] : [];
	$defaults = jcp_global_settings_defaults()['banner'];

	$contact = is_array( $settings['contact'] ?? null ) ? $settings['contact'] : [];
	if ( ( $contact['support_email'] ?? '' ) === 'hello@jobcapturepro.com' ) {
		$contact['support_email'] = 'support@jobcapturepro.com';
	}
	if ( trim( (string) ( $contact['support_phone'] ?? '' ) ) === '' ) {
		$contact['support_phone'] = '(941) 941-9506';
	}
	$settings['contact'] = $contact;

	$headline = (string) ( $banner['headline'] ?? '' );
	$code     = (string) ( $banner['code'] ?? '' );
	$coupon   = (string) ( $banner['coupon'] ?? '' );
	$text     = (string) ( $banner['text'] ?? '' );

	$is_early_bird_headline = (bool) preg_match( '/early\s*bird/i', $headline );
	$is_early_bird_code     = (bool) preg_match( '/^earlybird$/i', trim( $code ) );
	$is_early_bird_coupon   = (bool) preg_match( '/^earlybird$/i', trim( $coupon ) );
	$is_founding_copy       = (bool) preg_match( '/founding\s+crew/i', $headline . ' ' . $text );

	if ( $is_early_bird_headline || $is_founding_copy ) {
		$banner['headline'] = (string) ( $defaults['headline'] ?? 'Start Free Trial:' );
		$banner['text']     = (string) ( $defaults['text'] ?? '' );
		$banner['cta_label'] = (string) ( $defaults['cta_label'] ?? 'Start Free Trial' );
	}
	if ( $is_early_bird_code ) {
		$banner['code'] = '';
	}
	if ( $is_early_bird_coupon ) {
		$banner['coupon'] = '';
	}

	// Migrate stored trial / start-free CTA copy → Start Free Trial.
	$banner['headline']  = jcp_global_rewrite_trial_label( (string) ( $banner['headline'] ?? '' ), 'headline' );
	$banner['text']      = jcp_global_rewrite_trial_label( (string) ( $banner['text'] ?? '' ), 'prose' );
	$banner['cta_label'] = jcp_global_rewrite_trial_label( (string) ( $banner['cta_label'] ?? '' ), 'button' );

	$nav = is_array( $settings['nav_cta'] ?? null ) ? $settings['nav_cta'] : [];
	if ( isset( $nav['primary_label'] ) ) {
		$nav['primary_label'] = jcp_global_rewrite_trial_label( (string) $nav['primary_label'], 'button' );
	}
	$settings['nav_cta'] = $nav;
	$settings['banner']  = $banner;
	return $settings;
}

/**
 * Canonicalize primary trial/signup CTA wording to “Start Free Trial”.
 *
 * Preserves demo CTAs (e.g. personalized demo) — only rewrites trial/signup variants.
 *
 * @param string $text Raw label or sentence.
 * @param string $kind button|headline|prose.
 */
function jcp_global_rewrite_trial_label( string $text, string $kind = 'button' ): string {
	$trimmed = trim( $text );
	if ( $trimmed === '' ) {
		return $text;
	}

	// Skip demo-specific CTAs.
	if ( preg_match( '/\b(personalized\s+demo|interactive\s+demo|live\s+demo|view\s+(the\s+)?demo|launch\s+(interactive\s+)?demo|see\s+it\s+for\s+my\s+business|see\s+.{0,40}demo|get\s+a\s+personalized\s+demo)\b/i', $trimmed ) ) {
		return $text;
	}

	$canonical_btn      = 'Start Free Trial';
	$canonical_headline = 'Start Free Trial:';

	if ( preg_match( '/^start\s+free\s+trial:?$/i', $trimmed ) ) {
		return $kind === 'headline' ? $canonical_headline : $canonical_btn;
	}

	$exact = [
		'/^get\s+started\s+free!?$/i',
		'/^get\s+started!?$/i',
		'/^start\s+for\s+free!?$/i',
		'/^start\s+free!?$/i',
		'/^start\s+a\s+free(\s+\d+[-\s]?day)?\s+trial!?$/i',
		'/^start\s+free(\s+\d+[-\s]?day)?\s+trial!?$/i',
		'/^sign\s+up\s+for\s+free!?$/i',
		'/^sign\s+up\s+free!?$/i',
		'/^claim\s+(your\s+)?free\s+trial!?$/i',
		'/^free\s+trial:?$/i',
	];

	if ( $kind === 'button' || $kind === 'headline' ) {
		foreach ( $exact as $pattern ) {
			if ( preg_match( $pattern, $trimmed ) ) {
				return $kind === 'headline' ? $canonical_headline : $canonical_btn;
			}
		}
		if ( $kind === 'headline' && preg_match( '/^(start\s+free|start\s+for\s+free|get\s+started\s+free|free\s+trial):?$/i', $trimmed ) ) {
			return $canonical_headline;
		}
	}

	$out = $text;
	// Longer / more specific phrases first.
	$replacements = [
		[ '/\bGet\s+[Ss]tarted\s+[Ff]ree\b/', $canonical_btn ],
		[ '/\bget\s+started\s+free\b/', $canonical_btn ],
		[ '/\bStart\s+a\s+free(?:\s+\d+[-\s]?day)?\s+[Tt]rial\b/', $canonical_btn ],
		[ '/\bstart\s+a\s+free(?:\s+\d+[-\s]?day)?\s+trial\b/', $canonical_btn ],
		[ '/\bStart\s+[Ff]ree(?:\s+\d+[-\s]?day)?\s+[Tt]rial\b/', $canonical_btn ],
		[ '/\bSign\s+[Uu]p\s+for\s+[Ff]ree\b/', $canonical_btn ],
		[ '/\bsign\s+up\s+for\s+free\b/', $canonical_btn ],
		[ '/\bStart\s+for\s+[Ff]ree\b/', $canonical_btn ],
		[ '/\bstart\s+for\s+free\b/', $canonical_btn ],
		[ '/\bStart\s+free\b(?!\s+[Tt]rial)/', $canonical_btn ],
		[ '/\bstart\s+free\b(?!\s+trial)/', $canonical_btn ],
		[ '/\bFree\s+14[-\s]?day\s+trial\b/i', $canonical_btn ],
		[ '/\bfree\s+14[-\s]?day\s+trial\b/i', $canonical_btn ],
		[ '/\bthe\s+free\s+trial\b/i', 'Start Free Trial' ],
		[ '/\ba\s+free\s+trial\b/i', 'Start Free Trial' ],
		[ '/\bFree\s+trial:?\b/', $canonical_headline ],
		[ '/\bfree\s+trial\b/i', 'Start Free Trial' ],
	];

	foreach ( $replacements as [ $pattern, $replacement ] ) {
		$out = preg_replace( $pattern, $replacement, $out ) ?? $out;
	}

	return $out;
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
	if ( function_exists( 'jcp_page_current_hides_site_chrome' ) && jcp_page_current_hides_site_chrome() ) {
		return false;
	}
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
	$label = trim( (string) ( $banner['cta_label'] ?? '' ) );
	// Empty or demo leftovers for Start-free labels → app onboarding.
	$path = (string) ( wp_parse_url( $url, PHP_URL_PATH ) ?? '' );
	$is_demo = $url === '/demo' || rtrim( $path, '/' ) === '/demo';
	if ( $url === '' || ( $is_demo && preg_match( '/start\s+(for\s+)?free|trial|sign\s*up|get\s*started|claim/i', $label ) ) ) {
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
			: esc_url( 'https://app.jobcapturepro.com/onboarding' );
	}
	if ( preg_match( '#^https?://#i', $url ) ) {
		return esc_url( $url );
	}
	return esc_url( home_url( $url ) );
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
	$label = jcp_global_rewrite_trial_label( trim( $label ), 'button' );
	$url   = trim( $url );

	$utm = $utm_content !== '' && function_exists( 'jcp_core_onboarding_utm_defaults' )
		? jcp_core_onboarding_utm_defaults( $utm_content )
		: ( $utm_content !== '' ? [ 'utm_content' => $utm_content ] : [] );

	$is_signup_label = $label !== '' && (bool) preg_match(
		'/trial|sign\s*up|get\s*started|claim|start\s+for\s+free|start\s+free/i',
		$label
	);
	$url_path = (string) ( wp_parse_url( $url, PHP_URL_PATH ) ?? '' );
	if ( $is_signup_label && ( $url === '/demo' || rtrim( $url_path, '/' ) === '/demo' ) ) {
		$url = '';
	}

	if ( $url === '' && $label !== '' && preg_match( '/\blog\s*in\b/i', $label ) ) {
		$url = function_exists( 'jcp_core_app_login_url_raw' )
			? jcp_core_app_login_url_raw( array_merge( $utm, $query_extra ) )
			: 'https://app.jobcapturepro.com/login';
	} elseif ( $url === '' && $is_signup_label ) {
		$url = function_exists( 'jcp_core_onboarding_app_url_raw' )
			? jcp_core_onboarding_app_url_raw( array_merge( $utm, $query_extra ) )
			: 'https://app.jobcapturepro.com/onboarding';
	} elseif ( $url === '' ) {
		$url = home_url( '/demo' );
	} elseif ( preg_match( '#^https?://app\.jobcapturepro\.com/login/?$#i', $url ) ) {
		$url = function_exists( 'jcp_core_app_login_url_raw' )
			? jcp_core_app_login_url_raw( array_merge( $utm, $query_extra ) )
			: $url;
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
	$primary_label   = (string) ( $global['primary_label'] ?? 'Start Free Trial' );
	$primary_url     = (string) ( $global['primary_url'] ?? '' );
	$secondary_label = (string) ( $global['secondary_label'] ?? 'Login' );
	$secondary_url   = (string) ( $global['secondary_url'] ?? '' );

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

	// Migrate legacy Online Demo secondary CTA → Login (app login + UTMs).
	$secondary_path = (string) ( wp_parse_url( $secondary_url, PHP_URL_PATH ) ?? '' );
	$is_legacy_demo = (bool) preg_match( '/online\s*demo/i', $secondary_label )
		|| $secondary_url === '/demo'
		|| rtrim( $secondary_path, '/' ) === '/demo';
	if ( $is_legacy_demo ) {
		$secondary_label = 'Login';
		$secondary_url   = '';
	}

	// Migrate legacy Get Started / Start Free Trial primary CTA → Start Free Trial.
	if ( preg_match( '/^(get\s*started(\s+free)?|start\s+(for\s+)?free)$/i', trim( $primary_label ) ) ) {
		$primary_label = 'Start Free Trial';
	}
	$primary_label = jcp_global_rewrite_trial_label( $primary_label, 'button' );

	// Trial CTAs must never keep a leftover /demo URL from a prior label rename.
	if ( preg_match( '/start\s+(for\s+)?free|start\s+free\s+trial|get\s+started/i', $primary_label ) ) {
		$primary_path = (string) ( wp_parse_url( $primary_url, PHP_URL_PATH ) ?? '' );
		if ( $primary_url === '/demo' || rtrim( $primary_path, '/' ) === '/demo' ) {
			$primary_url = '';
		}
	}

	return [
		'primary'   => jcp_global_resolve_cta( $primary_label, $primary_url, 'nav_get_started' ),
		'secondary' => jcp_global_resolve_cta( $secondary_label, $secondary_url, 'nav_login' ),
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
