<?php
/**
 * Code / Embed block — shortcodes, iframes, scripts, and embed HTML.
 *
 * @package JCP_Core
 */

/**
 * Allowlisted host suffixes for iframe/script embeds (filterable).
 *
 * @return array<int, string>
 */
function jcp_code_embed_iframe_hosts(): array {
	$hosts = [
		'calendly.com',
		'cal.com',
		'hubspot.com',
		'hsforms.com',
		'hs-sites.com',
		'google.com',
		'calendar.google.com',
		'microsoft.com',
		'outlook.office.com',
		'outlook.office365.com',
		'chilipiper.com',
		'savvycal.com',
		'youcanbook.me',
		'leadconnectorhq.com',
		'msgsndr.com',
		'leadconnector.com',
		'gohighlevel.com',
		'oncehub.com',
		'tidycal.com',
		'appointlet.com',
		'setmore.com',
		'square.site',
		'squareupsandbox.com',
		'acuityscheduling.com',
		'squareup.com',
		'typeform.com',
		'forms.gle',
		'youtube.com',
		'youtu.be',
		'vimeo.com',
		'loom.com',
		'wistia.com',
		'player.vimeo.com',
	];
	/**
	 * Filter allowlisted embed host suffixes for the Code/Embed block.
	 *
	 * @param array<int, string> $hosts Host suffixes.
	 */
	return array_values( array_filter( array_map( 'strval', (array) apply_filters( 'jcp_code_embed_iframe_hosts', $hosts ) ) ) );
}

/**
 * Whether a URL host is on the embed allowlist.
 *
 * @param string $url Absolute URL.
 */
function jcp_code_embed_host_allowed( string $url ): bool {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! is_string( $host ) || $host === '' ) {
		return false;
	}
	$host = strtolower( $host );
	foreach ( jcp_code_embed_iframe_hosts() as $suffix ) {
		$suffix = strtolower( ltrim( (string) $suffix, '.' ) );
		if ( $suffix === '' ) {
			continue;
		}
		if ( $host === $suffix || str_ends_with( $host, '.' . $suffix ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether current user may paste unfiltered embed HTML (any https iframe/script).
 */
function jcp_code_embed_user_can_unfiltered(): bool {
	return is_user_logged_in() && (
		current_user_can( 'unfiltered_html' )
		|| current_user_can( 'manage_options' )
		|| current_user_can( 'edit_theme_options' )
	);
}

/**
 * Normalize a URL for embed src checks (force https).
 *
 * @param string $src Raw src.
 */
function jcp_code_embed_normalize_src( string $src ): string {
	$src = trim( html_entity_decode( $src, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( $src === '' || stripos( $src, 'javascript:' ) === 0 || stripos( $src, 'data:' ) === 0 ) {
		return '';
	}
	if ( str_starts_with( $src, '//' ) ) {
		$src = 'https:' . $src;
	}
	if ( ! preg_match( '#^https://#i', $src ) ) {
		return '';
	}
	return $src;
}

/**
 * Whether an embed src URL is allowed.
 *
 * @param string $src Absolute https URL.
 */
function jcp_code_embed_src_allowed( string $src ): bool {
	$src = jcp_code_embed_normalize_src( $src );
	if ( $src === '' ) {
		return false;
	}
	if ( jcp_code_embed_user_can_unfiltered() ) {
		return true;
	}
	return jcp_code_embed_host_allowed( $src );
}

/**
 * Whether raw input is a single shortcode tag.
 *
 * @param string $raw Raw embed string.
 */
function jcp_code_embed_is_shortcode( string $raw ): bool {
	$raw = trim( $raw );
	return (bool) preg_match( '/^\[[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]$/', $raw );
}

/**
 * Allowed HTML tags/attrs for non-script Code/Embed markup.
 *
 * @return array<string, array<string, bool>>
 */
function jcp_code_embed_allowed_html(): array {
	$allowed = wp_kses_allowed_html( 'post' );

	$allowed['iframe'] = [
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'style'           => true,
		'class'           => true,
		'id'              => true,
		'title'           => true,
		'name'            => true,
		'loading'         => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'frameborder'     => true,
		'referrerpolicy'  => true,
		'sandbox'         => true,
		'scrolling'       => true,
	];

	$allowed['div']['style']   = true;
	$allowed['div']['id']      = true;
	$allowed['div']['class']   = true;
	$allowed['span']['style']  = true;
	$allowed['span']['id']     = true;
	$allowed['span']['class']  = true;
	$allowed['section']['id']  = true;
	$allowed['section']['class'] = true;

	/**
	 * Filter allowed HTML for Code/Embed block markup (scripts handled separately).
	 *
	 * @param array<string, array<string, bool>> $allowed Allowed tags.
	 */
	return (array) apply_filters( 'jcp_code_embed_allowed_html', $allowed );
}

/**
 * Rebuild a safe external <script src> tag.
 *
 * @param string $src  Normalized https URL.
 * @param string $open Original opening tag (for type/async/defer/id).
 */
function jcp_code_embed_external_script_tag( string $src, string $open = '' ): string {
	$type = 'text/javascript';
	if ( preg_match( '/\btype\s*=\s*(["\'])(.*?)\1/i', $open, $tm ) ) {
		$type = sanitize_text_field( $tm[2] );
	}
	$id_attr = '';
	if ( preg_match( '/\bid\s*=\s*(["\'])(.*?)\1/i', $open, $im ) ) {
		$id_attr = ' id="' . esc_attr( sanitize_html_class( $im[2] ) ) . '"';
	}
	$async = preg_match( '/\basync\b/i', $open ) ? ' async' : '';
	$defer = preg_match( '/\bdefer\b/i', $open ) ? ' defer' : '';
	return '<script src="' . esc_url( $src ) . '" type="' . esc_attr( $type ) . '"' . $id_attr . $async . $defer . '></script>';
}

/**
 * Whether an inline script body is acceptable for the Code/Embed block.
 *
 * @param string $body Script contents.
 */
function jcp_code_embed_inline_script_allowed( string $body ): bool {
	$body = str_replace( "\0", '', $body );
	if ( trim( $body ) === '' ) {
		return false;
	}
	// Reject script-breakout / nested closing tags.
	if ( preg_match( '/<\/script/i', $body ) ) {
		return false;
	}
	// Soft size cap (booking widgets + query wiring stay well under this).
	if ( strlen( $body ) > 100000 ) {
		return false;
	}
	/**
	 * Filter whether an inline embed script body is allowed.
	 *
	 * @param bool   $allowed Allowed.
	 * @param string $body    Script body.
	 */
	return (bool) apply_filters( 'jcp_code_embed_inline_script_allowed', true, $body );
}

/**
 * Filter iframe tags: keep only https srcs that pass allowlist rules.
 *
 * @param string $html HTML without script tags.
 */
function jcp_code_embed_filter_iframes( string $html ): string {
	if ( $html === '' ) {
		return '';
	}

	return (string) preg_replace_callback(
		'/<iframe\b[^>]*>.*?<\/iframe>|<iframe\b[^>]*\/?>/is',
		static function ( array $m ): string {
			$tag = $m[0];
			if ( ! preg_match( '/\bsrc\s*=\s*(["\'])(.*?)\1/i', $tag, $sm ) ) {
				// Empty mount iframes are not used; drop src-less iframes.
				return '';
			}
			$src = jcp_code_embed_normalize_src( $sm[2] );
			if ( $src === '' || ! jcp_code_embed_src_allowed( $src ) ) {
				return '';
			}
			return (string) preg_replace(
				'/\bsrc\s*=\s*(["\']).*?\1/i',
				'src="' . esc_attr( $src ) . '"',
				$tag,
				1
			);
		},
		$html
	);
}

/**
 * Sanitize a single <script>…</script> (external src or inline).
 *
 * @param string $tag Full script tag.
 */
function jcp_code_embed_sanitize_script_tag( string $tag ): string {
	if ( ! preg_match( '/<script\b([^>]*)>([\s\S]*?)<\/script>/i', $tag, $m ) ) {
		// Self-closing / empty open-only — only useful with src.
		if ( preg_match( '/<script\b([^>]*)\/?>/i', $tag, $om ) && preg_match( '/\bsrc\s*=\s*(["\'])(.*?)\1/i', $om[1], $sm ) ) {
			$src = jcp_code_embed_normalize_src( $sm[2] );
			if ( $src !== '' && jcp_code_embed_src_allowed( $src ) ) {
				return jcp_code_embed_external_script_tag( $src, $om[1] );
			}
		}
		return '';
	}

	$open = $m[1];
	$body = $m[2];

	if ( preg_match( '/\bsrc\s*=\s*(["\'])(.*?)\1/i', $open, $sm ) ) {
		$src = jcp_code_embed_normalize_src( $sm[2] );
		if ( $src === '' || ! jcp_code_embed_src_allowed( $src ) ) {
			return '';
		}
		return jcp_code_embed_external_script_tag( $src, $open );
	}

	if ( ! jcp_code_embed_inline_script_allowed( $body ) ) {
		return '';
	}

	$type_attr = '';
	if ( preg_match( '/\btype\s*=\s*(["\'])(.*?)\1/i', $open, $tm ) ) {
		$type = sanitize_text_field( $tm[2] );
		if ( $type !== '' && ! preg_match( '/^(text|application)\/(javascript|ecmascript)$/i', $type ) && strtolower( $type ) !== 'module' ) {
			return '';
		}
		if ( $type !== '' ) {
			$type_attr = ' type="' . esc_attr( $type ) . '"';
		}
	}

	// Preserve inline JS exactly (needed for booking widgets that read query params).
	return '<script' . $type_attr . '>' . $body . '</script>';
}

/**
 * Sanitize embed input to shortcode or embed HTML (including inline scripts).
 *
 * @param string $raw Raw paste (shortcode or HTML).
 * @return array{ok:bool,kind:string,value:string,message:string}
 */
function jcp_code_embed_sanitize( string $raw ): array {
	$raw = trim( wp_unslash( $raw ) );
	$raw = trim( $raw, " \t\n\r\0\x0B`" );

	if ( $raw === '' ) {
		return [
			'ok'      => true,
			'kind'    => 'empty',
			'value'   => '',
			'message' => '',
		];
	}

	if ( jcp_code_embed_is_shortcode( $raw ) ) {
		return [
			'ok'      => true,
			'kind'    => 'shortcode',
			'value'   => $raw,
			'message' => '',
		];
	}

	// Pull scripts out first — wp_kses destroys inline script bodies.
	$parts   = [];
	$cursor  = 0;
	$matched = preg_match_all( '/<script\b[^>]*>[\s\S]*?<\/script>|<script\b[^>]*\/?>/i', $raw, $script_matches, PREG_OFFSET_CAPTURE );
	if ( $matched ) {
		foreach ( $script_matches[0] as $hit ) {
			$start = (int) $hit[1];
			$tag   = (string) $hit[0];
			$len   = strlen( $tag );
			if ( $start > $cursor ) {
				$parts[] = [
					'type' => 'html',
					'raw'  => substr( $raw, $cursor, $start - $cursor ),
				];
			}
			$parts[] = [
				'type' => 'script',
				'raw'  => $tag,
			];
			$cursor = $start + $len;
		}
	}
	if ( $cursor < strlen( $raw ) ) {
		$parts[] = [
			'type' => 'html',
			'raw'  => substr( $raw, $cursor ),
		];
	}
	if ( $parts === [] ) {
		$parts[] = [
			'type' => 'html',
			'raw'  => $raw,
		];
	}

	$out = '';
	foreach ( $parts as $part ) {
		if ( $part['type'] === 'script' ) {
			$out .= jcp_code_embed_sanitize_script_tag( $part['raw'] );
			continue;
		}
		$chunk = (string) $part['raw'];
		// Strip on* handlers before kses.
		$chunk = (string) preg_replace( '/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $chunk );
		$chunk = wp_kses( $chunk, jcp_code_embed_allowed_html() );
		$chunk = jcp_code_embed_filter_iframes( $chunk );
		$out  .= $chunk;
	}

	$out = trim( $out );

	// Preserve shortcodes mixed into HTML (do_shortcode on render).
	if ( $out === '' && preg_match( '/\[[a-zA-Z][a-zA-Z0-9_-]/', $raw ) ) {
		return [
			'ok'      => true,
			'kind'    => 'shortcode',
			'value'   => $raw,
			'message' => '',
		];
	}

	if ( $out === '' ) {
		return [
			'ok'      => false,
			'kind'    => 'invalid',
			'value'   => '',
			'message' => __( 'Embed not allowed — paste a shortcode, iframe, HTML, or booking widget script.', 'jcp-core' ),
		];
	}

	return [
		'ok'      => true,
		'kind'    => 'html',
		'value'   => $out,
		'message' => '',
	];
}

/**
 * Render the Code / Embed block.
 *
 * @param array<string, mixed> $props Block props.
 * @param string               $path  Flat path prefix (usually code_embed).
 */
function jcp_niche_render_code_embed( array $props, string $path = 'code_embed' ): void {
	$headline    = trim( (string) ( $props['headline'] ?? '' ) );
	$subheadline = trim( (string) ( $props['subheadline'] ?? '' ) );
	$raw_embed   = (string) ( $props['embed_code'] ?? '' );
	$parsed      = jcp_code_embed_sanitize( $raw_embed );

	$show_headline    = ! array_key_exists( 'show_headline', $props ) || ! empty( $props['show_headline'] );
	$show_subheadline = ! empty( $props['show_subheadline'] );

	$can_edit = function_exists( 'jcp_niche_user_can_inline_edit' ) && jcp_niche_user_can_inline_edit();

	$has_intro = ( $show_headline && $headline !== '' ) || ( $show_subheadline && $subheadline !== '' );
	$has_embed = $parsed['ok'] && $parsed['value'] !== '';

	if ( ! $has_intro && ! $has_embed && ! $can_edit ) {
		return;
	}

	$hl_vis  = function_exists( 'jcp_niche_field_visibility' ) ? jcp_niche_field_visibility( $props, 'show_headline', true ) : [ 'show' => $show_headline, 'attr' => '' ];
	$sub_vis = function_exists( 'jcp_niche_field_visibility' ) ? jcp_niche_field_visibility( $props, 'show_subheadline', false ) : [ 'show' => $show_subheadline, 'attr' => '' ];
	?>
	<section class="jcp-section jcp-code-embed">
		<div class="jcp-container">
			<?php if ( ( ! empty( $hl_vis['show'] ) && ( $headline !== '' || $can_edit ) ) || ( ! empty( $sub_vis['show'] ) && ( $subheadline !== '' || $can_edit ) ) ) : ?>
				<div class="jcp-code-embed__intro">
					<?php if ( ! empty( $hl_vis['show'] ) && ( $headline !== '' || $can_edit ) ) : ?>
						<h2<?php echo $hl_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( $path . '.headline' ); } ?>><?php echo esc_html( $headline !== '' ? $headline : __( 'Book a time', 'jcp-core' ) ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $sub_vis['show'] ) && ( $subheadline !== '' || $can_edit ) ) : ?>
						<p<?php echo $sub_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( $path . '.subheadline' ); } ?>><?php echo esc_html( $subheadline ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="jcp-code-embed__panel">
				<?php if ( $can_edit ) : ?>
					<label class="jcp-code-embed__field">
						<span class="jcp-code-embed__field-label"><?php esc_html_e( 'Embed code', 'jcp-core' ); ?></span>
						<textarea
							class="jcp-code-embed__textarea"
							data-jcp-input-path="<?php echo esc_attr( $path . '.embed_code' ); ?>"
							rows="5"
							placeholder="<?php esc_attr_e( '[shortcode], HTML, or booking widget script', 'jcp-core' ); ?>"
							autocomplete="off"
							spellcheck="false"
						><?php echo esc_textarea( $raw_embed ); ?></textarea>
						<span class="jcp-code-embed__hint"><?php esc_html_e( 'Paste a shortcode, iframe, HTML, or inline booking script, then Save.', 'jcp-core' ); ?></span>
					</label>
					<?php if ( ! $parsed['ok'] && $raw_embed !== '' ) : ?>
						<p class="jcp-code-embed__error" role="alert"><?php echo esc_html( $parsed['message'] ); ?></p>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $has_embed ) : ?>
					<div class="jcp-code-embed__output">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcodes / sanitized embed HTML.
						echo do_shortcode( $parsed['value'] );
						?>
					</div>
				<?php elseif ( $can_edit && ( $parsed['ok'] || $raw_embed === '' ) ) : ?>
					<p class="jcp-code-embed__empty" role="status"><?php esc_html_e( 'Calendar / embed will appear here after you save embed code.', 'jcp-core' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Apply Thank You preset defaults (message + embed + secondary CTA).
 *
 * @param array<string, mixed> $doc Block document.
 * @return array<string, mixed>
 */
function jcp_page_finalize_thank_you_document( array $doc ): array {
	$preset = sanitize_key( (string) ( $doc['preset'] ?? '' ) );
	if ( $preset !== 'thank_you' ) {
		return $doc;
	}

	$doc['settings'] = is_array( $doc['settings'] ?? null ) ? $doc['settings'] : [];
	$doc['settings']['hide_site_chrome'] = false;
	$doc['settings']['campaign_landing'] = false;
	$doc['settings']['hide_breadcrumb']  = true;

	if ( empty( $doc['blocks'] ) || ! is_array( $doc['blocks'] ) ) {
		return $doc;
	}

	foreach ( $doc['blocks'] as $i => $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type  = (string) ( $block['type'] ?? '' );
		$props = is_array( $block['props'] ?? null ) ? $block['props'] : [];

		if ( $type === 'hero' ) {
			$base = is_array( $block['layout'] ?? null )
				? $block['layout']
				: jcp_block_default_layout( 'hero', 'marketing' );
			if ( empty( $block['layout']['hero_variant'] ) ) {
				$base['hero_variant'] = 'centered';
			}
			if ( empty( $block['layout']['align'] ) ) {
				$base['align'] = 'center';
			}
			$doc['blocks'][ $i ]['layout'] = $base;
			if ( empty( $props['h1'] ) ) {
				$props['h1'] = __( 'Thanks — you’re on the list', 'jcp-core' );
			}
			if ( empty( $props['subheadline'] ) ) {
				$props['subheadline'] = __( 'We’ll follow up shortly. Prefer to talk now? Pick a time below.', 'jcp-core' );
			}
			if ( ! array_key_exists( 'show_visual', $props ) ) {
				$props['show_visual'] = false;
			}
			if ( ! array_key_exists( 'show_cta_primary', $props ) ) {
				$props['show_cta_primary'] = false;
			}
			if ( ! array_key_exists( 'show_cta_secondary', $props ) ) {
				$props['show_cta_secondary'] = false;
			}
			$doc['blocks'][ $i ]['props'] = $props;
		}

		if ( $type === 'code_embed' ) {
			if ( empty( $props['headline'] ) ) {
				$props['headline'] = __( 'Book a time', 'jcp-core' );
			}
			if ( ! array_key_exists( 'show_headline', $props ) ) {
				$props['show_headline'] = true;
			}
			if ( ! array_key_exists( 'show_subheadline', $props ) ) {
				$props['show_subheadline'] = false;
			}
			$doc['blocks'][ $i ]['props'] = $props;
		}

		if ( $type === 'final_cta' ) {
			if ( empty( $props['headline'] ) ) {
				$props['headline'] = __( 'While you wait', 'jcp-core' );
			}
			$primary = is_array( $props['cta_primary'] ?? null ) ? $props['cta_primary'] : [];
			if ( empty( $primary['label'] ) ) {
				$primary['label'] = __( 'Back to home', 'jcp-core' );
			}
			if ( empty( $primary['url'] ) ) {
				$primary['url'] = '/';
			}
			$props['cta_primary'] = $primary;
			$secondary            = is_array( $props['cta_secondary'] ?? null ) ? $props['cta_secondary'] : [];
			if ( empty( $secondary['label'] ) ) {
				$secondary['label'] = __( 'Explore the demo', 'jcp-core' );
			}
			if ( empty( $secondary['url'] ) ) {
				$secondary['url'] = '/demo';
			}
			$props['cta_secondary']       = $secondary;
			$doc['blocks'][ $i ]['props'] = $props;
		}
	}

	return $doc;
}
