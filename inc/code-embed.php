<?php
/**
 * Code / Embed block — safer shortcode + allowlisted iframe rendering.
 *
 * @package JCP_Core
 */

/**
 * Allowlisted iframe host suffixes (filterable).
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
	];
	/**
	 * Filter allowlisted iframe host suffixes for the Code/Embed block.
	 *
	 * @param array<int, string> $hosts Host suffixes.
	 */
	return array_values( array_filter( array_map( 'strval', (array) apply_filters( 'jcp_code_embed_iframe_hosts', $hosts ) ) ) );
}

/**
 * Whether a URL host is on the iframe allowlist.
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
 * Whether raw input is a single shortcode tag.
 *
 * @param string $raw Raw embed string.
 */
function jcp_code_embed_is_shortcode( string $raw ): bool {
	$raw = trim( $raw );
	return (bool) preg_match( '/^\[[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]$/', $raw );
}

/**
 * Sanitize embed input to a shortcode string or safe iframe HTML.
 *
 * @param string $raw Raw paste (shortcode or HTML).
 * @return array{ok:bool,kind:string,value:string,message:string}
 */
function jcp_code_embed_sanitize( string $raw ): array {
	$raw = trim( wp_unslash( $raw ) );
	$raw = trim( $raw, " \t\n\r\0\x0B`\"'" );

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

	if ( ! preg_match_all( '/<iframe\b[^>]*>.*?<\/iframe>|<iframe\b[^>]*\/?>/is', $raw, $matches ) ) {
		return [
			'ok'      => false,
			'kind'    => 'invalid',
			'value'   => '',
			'message' => __( 'Embed not allowed — use a shortcode or an iframe from an approved calendar host.', 'jcp-core' ),
		];
	}

	$iframes = [];
	foreach ( $matches[0] as $iframe_html ) {
		$built = jcp_code_embed_sanitize_iframe_tag( $iframe_html );
		if ( $built !== '' ) {
			$iframes[] = $built;
		}
	}

	if ( $iframes === [] ) {
		return [
			'ok'      => false,
			'kind'    => 'invalid',
			'value'   => '',
			'message' => __( 'Embed not allowed — use a shortcode or an iframe from an approved calendar host.', 'jcp-core' ),
		];
	}

	return [
		'ok'      => true,
		'kind'    => 'iframe',
		'value'   => implode( "\n", $iframes ),
		'message' => '',
	];
}

/**
 * Rebuild a single iframe tag with allowlisted src + safe attributes.
 *
 * @param string $html Raw iframe markup.
 */
function jcp_code_embed_sanitize_iframe_tag( string $html ): string {
	if ( ! preg_match( '/<iframe\b([^>]*)>/i', $html, $m ) ) {
		return '';
	}
	$attr_blob = $m[1];
	$attrs     = [];
	if ( preg_match_all( '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(["\'])(.*?)\2/s', $attr_blob, $am, PREG_SET_ORDER ) ) {
		foreach ( $am as $row ) {
			$name           = strtolower( $row[1] );
			$val            = html_entity_decode( $row[3], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			$attrs[ $name ] = $val;
		}
	}

	$src = trim( (string) ( $attrs['src'] ?? '' ) );
	if ( $src === '' || stripos( $src, 'javascript:' ) === 0 ) {
		return '';
	}
	if ( str_starts_with( $src, '//' ) ) {
		$src = 'https:' . $src;
	}
	if ( ! preg_match( '#^https://#i', $src ) ) {
		return '';
	}
	if ( ! jcp_code_embed_host_allowed( $src ) ) {
		return '';
	}

	$out = [
		'src'             => esc_url( $src ),
		'loading'         => 'lazy',
		'referrerpolicy'  => 'no-referrer-when-downgrade',
		'title'           => '',
		'width'           => '100%',
		'height'          => '700',
		'frameborder'     => '0',
		'allowfullscreen' => 'true',
	];

	if ( ! empty( $attrs['title'] ) ) {
		$out['title'] = sanitize_text_field( $attrs['title'] );
	}
	if ( ! empty( $attrs['width'] ) && preg_match( '/^[\d.%]+$/', (string) $attrs['width'] ) ) {
		$out['width'] = (string) $attrs['width'];
	}
	if ( ! empty( $attrs['height'] ) && preg_match( '/^[\d.%]+$/', (string) $attrs['height'] ) ) {
		$out['height'] = (string) $attrs['height'];
	}
	if ( ! empty( $attrs['allow'] ) ) {
		$out['allow'] = preg_replace( '/[^a-z0-9\-\s;]/i', '', (string) $attrs['allow'] ) ?? '';
	}
	if ( isset( $attrs['allowfullscreen'] ) ) {
		$out['allowfullscreen'] = 'true';
	}
	if ( ! empty( $attrs['style'] ) ) {
		$style      = (string) $attrs['style'];
		$safe_parts = [];
		foreach ( explode( ';', $style ) as $decl ) {
			$decl = trim( $decl );
			if ( $decl === '' ) {
				continue;
			}
			if ( preg_match( '/^(min-)?(width|height)\s*:\s*[\d.%]+(px|%|rem|vh|vw)?$/i', $decl ) ) {
				$safe_parts[] = $decl;
			}
			if ( preg_match( '/^border\s*:\s*0(px)?$/i', $decl ) ) {
				$safe_parts[] = 'border:0';
			}
		}
		if ( $safe_parts ) {
			$out['style'] = implode( '; ', $safe_parts );
		}
	}

	$html_out = '<iframe';
	foreach ( $out as $name => $value ) {
		if ( $value === '' && $name !== 'title' ) {
			continue;
		}
		if ( $name === 'allowfullscreen' ) {
			$html_out .= ' allowfullscreen';
			continue;
		}
		$html_out .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( (string) $value ) );
	}
	$html_out .= '></iframe>';
	return $html_out;
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
							placeholder="<?php esc_attr_e( '[shortcode] or calendar iframe HTML', 'jcp-core' ); ?>"
							autocomplete="off"
							spellcheck="false"
						><?php echo esc_textarea( $raw_embed ); ?></textarea>
						<span class="jcp-code-embed__hint"><?php esc_html_e( 'Paste a shortcode or an iframe from an approved calendar host, then Save.', 'jcp-core' ); ?></span>
					</label>
					<?php if ( ! $parsed['ok'] && $raw_embed !== '' ) : ?>
						<p class="jcp-code-embed__error" role="alert"><?php echo esc_html( $parsed['message'] ); ?></p>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $has_embed ) : ?>
					<div class="jcp-code-embed__output">
						<?php
						if ( $parsed['kind'] === 'shortcode' ) {
							echo do_shortcode( $parsed['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							echo $parsed['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized iframe HTML.
						}
						?>
					</div>
				<?php elseif ( $can_edit && ( $parsed['ok'] || $raw_embed === '' ) ) : ?>
					<p class="jcp-code-embed__empty" role="status"><?php esc_html_e( 'Calendar / embed will appear here after you save allowed embed code.', 'jcp-core' ); ?></p>
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
