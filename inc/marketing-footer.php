<?php
/**
 * Editable marketing footer (site option + REST).
 *
 * @package JCP_Core
 */

function jcp_marketing_footer_option_key(): string {
	return 'jcp_marketing_footer';
}

/**
 * @return array<string, mixed>
 */
function jcp_marketing_footer_defaults(): array {
	return [
		'blurb'     => 'Turn real job photos into proof, visibility, reviews, and more jobs.',
		'columns'   => [
			[
				'id'      => 'product',
				'heading' => 'Product',
				'links'   => [
					[ 'label' => 'How it Works', 'url' => home_url( '/#how-it-works' ) ],
					[ 'label' => 'Features', 'url' => home_url( '/#features' ) ],
					[ 'label' => 'By Trade', 'url' => home_url( '/industries/' ) ],
					[ 'label' => 'Pricing', 'url' => home_url( '/pricing' ) ],
				],
			],
			[
				'id'      => 'resources',
				'heading' => 'Resources',
				'links'   => [
					[ 'label' => 'Blog', 'url' => home_url( '/blog' ) ],
					[ 'label' => 'Help Center', 'url' => home_url( '/help' ) ],
					[ 'label' => 'Online Demo', 'url' => home_url( '/demo' ) ],
					[ 'label' => 'Referral Program', 'url' => home_url( '/referral-program' ) ],
				],
			],
			[
				'id'      => 'company',
				'heading' => 'Company',
				'links'   => [
					[ 'label' => 'Support', 'url' => home_url( '/support/' ) ],
					[ 'label' => 'About JobCapturePro', 'url' => home_url( '/about' ) ],
				],
			],
		],
		'trial_cta' => [
			'label' => 'Start Free Trial',
			'url'   => '',
		],
		'socials'   => [
			'facebook'  => 'https://www.facebook.com/profile.php?id=61574958638999',
			'x'         => 'https://x.com/jobcapturepro',
			'instagram' => 'https://www.instagram.com/jobcapturepro/',
			'tiktok'    => 'https://www.tiktok.com/@jobcapturepro',
			'youtube'   => 'https://www.youtube.com/channel/UCckc38UwNU5P8A7eI1txZAw',
		],
	];
}

/**
 * Deep-merge stored footer with defaults.
 *
 * @return array<string, mixed>
 */
function jcp_get_marketing_footer(): array {
	$stored = get_option( jcp_marketing_footer_option_key(), [] );
	if ( ! is_array( $stored ) ) {
		$stored = [];
	}
	return jcp_global_settings_merge( jcp_marketing_footer_defaults(), $stored );
}

/**
 * @param string $url Raw URL (absolute or site-relative).
 */
function jcp_marketing_footer_sanitize_url( string $url ): string {
	$url = trim( $url );
	if ( $url === '' ) {
		return '';
	}
	if ( function_exists( 'jcp_global_sanitize_url_field' ) ) {
		$sanitized = jcp_global_sanitize_url_field( $url );
		if ( $sanitized !== '' && $sanitized[0] !== '/' ) {
			return esc_url_raw( $sanitized );
		}
		return $sanitized;
	}
	return esc_url_raw( $url );
}

/**
 * @param array<string, mixed> $input Raw footer payload.
 * @return array<string, mixed>
 */
function jcp_sanitize_marketing_footer( array $input ): array {
	$defaults     = jcp_marketing_footer_defaults();
	$allowed_ids  = [ 'product', 'resources', 'company' ];
	$social_keys  = [ 'facebook', 'x', 'instagram', 'tiktok', 'youtube' ];
	$input_cols   = is_array( $input['columns'] ?? null ) ? $input['columns'] : [];
	$columns_by_id = [];

	foreach ( $input_cols as $col ) {
		if ( ! is_array( $col ) ) {
			continue;
		}
		$id = sanitize_key( (string) ( $col['id'] ?? '' ) );
		if ( in_array( $id, $allowed_ids, true ) ) {
			$columns_by_id[ $id ] = $col;
		}
	}

	$columns = [];
	foreach ( $defaults['columns'] as $def_col ) {
		if ( ! is_array( $def_col ) ) {
			continue;
		}
		$id      = (string) ( $def_col['id'] ?? '' );
		$col_in  = $columns_by_id[ $id ] ?? [];
		$heading = sanitize_text_field( (string) ( $col_in['heading'] ?? $def_col['heading'] ?? '' ) );

		$links_in = is_array( $col_in['links'] ?? null ) ? $col_in['links'] : [];
		$links    = [];
		$count    = 0;
		foreach ( $links_in as $link ) {
			if ( $count >= 12 ) {
				break;
			}
			if ( ! is_array( $link ) ) {
				continue;
			}
			$label = sanitize_text_field( (string) ( $link['label'] ?? '' ) );
			if ( $label === '' ) {
				continue;
			}
			$links[] = [
				'label' => $label,
				'url'   => jcp_marketing_footer_sanitize_url( (string) ( $link['url'] ?? '' ) ),
			];
			++$count;
		}

		$columns[] = [
			'id'      => $id,
			'heading' => $heading,
			'links'   => $links,
		];
	}

	$trial_in = is_array( $input['trial_cta'] ?? null ) ? $input['trial_cta'] : [];
	$def_trial = $defaults['trial_cta'];

	$socials = [];
	$social_in = is_array( $input['socials'] ?? null ) ? $input['socials'] : [];
	foreach ( $social_keys as $key ) {
		$raw = (string) ( $social_in[ $key ] ?? $defaults['socials'][ $key ] ?? '' );
		$socials[ $key ] = $raw === '' ? '' : esc_url_raw( $raw );
	}

	return [
		'blurb'     => sanitize_text_field( (string) ( $input['blurb'] ?? $defaults['blurb'] ) ),
		'columns'   => $columns,
		'trial_cta' => [
			'label' => sanitize_text_field( (string) ( $trial_in['label'] ?? $def_trial['label'] ) ),
			'url'   => jcp_marketing_footer_sanitize_url( (string) ( $trial_in['url'] ?? $def_trial['url'] ) ),
		],
		'socials'   => $socials,
	];
}

/**
 * @param array<string, mixed> $input Raw footer payload.
 * @return array<string, mixed> Saved merged footer.
 */
function jcp_save_marketing_footer( array $input ): array {
	$sanitized = jcp_sanitize_marketing_footer( $input );
	update_option( jcp_marketing_footer_option_key(), $sanitized );
	return jcp_get_marketing_footer();
}

/**
 * REST permission: inline editors when available, else edit_posts.
 */
function jcp_marketing_footer_rest_can_edit(): bool {
	if ( function_exists( 'jcp_niche_user_can_inline_edit' ) ) {
		return jcp_niche_user_can_inline_edit();
	}
	return current_user_can( 'edit_posts' );
}

/**
 * Register marketing footer REST routes.
 */
function jcp_marketing_footer_register_rest_routes(): void {
	register_rest_route(
		'jcp/v1',
		'/marketing-footer',
		[
			[
				'methods'             => 'GET',
				'callback'            => 'jcp_marketing_footer_rest_get',
				'permission_callback' => 'jcp_marketing_footer_rest_can_edit',
			],
			[
				'methods'             => 'POST',
				'callback'            => 'jcp_marketing_footer_rest_save',
				'permission_callback' => 'jcp_marketing_footer_rest_can_edit',
			],
		]
	);
}
add_action( 'rest_api_init', 'jcp_marketing_footer_register_rest_routes' );

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function jcp_marketing_footer_rest_get( WP_REST_Request $request ): WP_REST_Response {
	unset( $request );
	return new WP_REST_Response(
		[
			'footer' => jcp_get_marketing_footer(),
		]
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function jcp_marketing_footer_rest_save( WP_REST_Request $request ) {
	$body = $request->get_json_params();
	if ( ! is_array( $body ) || empty( $body['footer'] ) || ! is_array( $body['footer'] ) ) {
		return new WP_Error( 'invalid', __( 'Missing footer object.', 'jcp-core' ), [ 'status' => 400 ] );
	}

	$footer = jcp_save_marketing_footer( $body['footer'] );

	return new WP_REST_Response(
		[
			'success' => true,
			'footer'  => $footer,
		]
	);
}
