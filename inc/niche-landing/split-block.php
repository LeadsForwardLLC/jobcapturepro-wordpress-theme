<?php
/**
 * Shared split media + copy block (demo preview & media + text).
 *
 * @package JCP_Core
 */

/**
 * Default placeholder copy for optional split-block fields.
 *
 * @param string $field badge|cue|cta_note.
 */
function jcp_niche_split_placeholder( string $field ): string {
	$map = [
		'badge'    => __( 'See it in action', 'jcp-core' ),
		'cue'      => __( 'Add a short lead line to introduce this section.', 'jcp-core' ),
		'cta_note' => __( 'No credit card required', 'jcp-core' ),
	];
	return $map[ $field ] ?? '';
}

/**
 * Display text for an optional split field (saved copy or placeholder).
 *
 * @param array<string, mixed> $props Block props.
 * @param string               $field Field key.
 */
function jcp_niche_split_field_display( array $props, string $field ): string {
	$text = trim( (string) ( $props[ $field ] ?? '' ) );
	if ( $text !== '' ) {
		return $text;
	}
	return jcp_niche_split_placeholder( $field );
}

/**
 * Normalize split-block props (legacy cta alias, visibility defaults).
 *
 * @param array<string, mixed> $props Raw block props.
 * @return array<string, mixed>
 */
function jcp_niche_normalize_split_block_props( array $props ): array {
	if ( empty( $props['cta_primary'] ) && ! empty( $props['cta'] ) && is_array( $props['cta'] ) ) {
		$props['cta_primary'] = $props['cta'];
	}

	$headline    = trim( (string) ( $props['headline'] ?? '' ) );
	$subheadline = trim( (string) ( $props['subheadline'] ?? '' ) );
	$body        = trim( (string) ( $props['body'] ?? '' ) );
	$cue         = trim( (string) ( $props['cue'] ?? '' ) );
	$badge       = trim( (string) ( $props['badge'] ?? '' ) );
	$cta         = is_array( $props['cta_primary'] ?? null ) ? $props['cta_primary'] : [];
	$cta_label   = trim( (string) ( $cta['label'] ?? '' ) );
	$cta_note    = trim( (string) ( $props['cta_note'] ?? '' ) );

	foreach (
		[
			'show_badge'       => $badge !== '',
			'show_subheadline' => $subheadline !== '',
			'show_cue'         => $cue !== '',
			'show_body'        => $body !== '',
			'show_cta'         => $cta_label !== '',
			'show_cta_note'    => $cta_note !== '',
		] as $key => $default
	) {
		if ( ! array_key_exists( $key, $props ) ) {
			$props[ $key ] = $default;
		} else {
			$props[ $key ] = ! empty( $props[ $key ] );
		}
	}

	if ( $headline === '' && $body === '' && $subheadline === '' ) {
		return $props;
	}

	if ( ! isset( $props['media_type'] ) || (string) $props['media_type'] === '' ) {
		$props['media_type'] = 'image';
	}

	if ( empty( $props['phone_mockup_style'] ) ) {
		$props['phone_mockup_style'] = 'app_shell';
	}

	return $props;
}

/**
 * Whether a split block has renderable copy.
 *
 * @param array<string, mixed> $props Normalized props.
 */
function jcp_niche_split_block_has_copy( array $props ): bool {
	return trim( (string) ( $props['headline'] ?? '' ) ) !== ''
		|| trim( (string) ( $props['subheadline'] ?? '' ) ) !== ''
		|| trim( (string) ( $props['body'] ?? '' ) ) !== ''
		|| trim( (string) ( $props['cue'] ?? '' ) ) !== '';
}

/**
 * Render a split copy + media block (demo preview style).
 *
 * @param array<string, mixed> $props     Block props.
 * @param string               $path      Flat JSON path prefix.
 * @param string               $niche_key Page key for CTA URLs.
 * @param array<string, mixed> $options   {
 *   @type string $variant       card|plain — card uses demo-preview-card styling.
 *   @type string $section_id    Optional HTML id.
 *   @type string $root_class    Extra classes on outer wrapper.
 *   @type bool   $wrap_container Wrap in .jcp-container (industry pages).
 * }
 */
function jcp_niche_render_split_media_block( array $props, string $path, string $niche_key = '', array $options = [] ): void {
	$props = jcp_niche_normalize_split_block_props( $props );
	if ( ! jcp_niche_split_block_has_copy( $props ) ) {
		return;
	}

	$variant     = (string) ( $options['variant'] ?? 'card' );
	$section_id  = (string) ( $options['section_id'] ?? '' );
	$root_class  = trim( (string) ( $options['root_class'] ?? 'jcp-split-media-block' ) );
	$wrap_container = ! empty( $options['wrap_container'] );
	$position    = in_array( (string) ( $props['media_position'] ?? 'right' ), [ 'left', 'right' ], true )
		? (string) $props['media_position']
		: 'right';
	$media       = jcp_media_props_from_block( $props );
	$primary     = jcp_niche_resolve_cta( $props['cta_primary'] ?? [], $niche_key );
	$phone_style = (string) ( $props['phone_mockup_style'] ?? 'app_shell' );
	$demo_url    = $primary['url'] !== '' ? $primary['url'] : '/demo';

	$outer_tag = $variant === 'plain' ? 'section' : 'div';
	$outer_class = $variant === 'plain'
		? trim( "jcp-section rankings-section jcp-media-text jcp-media-text--media-{$position} {$root_class}" )
		: trim( "demo-preview-section jcp-block-demo-preview {$root_class}" );

	$card_open  = $variant === 'card' ? '<div class="demo-preview-card">' : '<div class="jcp-container">';
	$card_close = $variant === 'card' ? '</div>' : '</div>';

	if ( $section_id === '' && ! empty( $props['section_id'] ) ) {
		$section_id = (string) $props['section_id'];
	}

	printf( '<%s class="%s"%s>', esc_attr( $outer_tag ), esc_attr( $outer_class ), $section_id !== '' ? ' id="' . esc_attr( $section_id ) . '"' : '' );
	if ( $wrap_container ) {
		echo '<div class="jcp-container">';
	}
	echo $card_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	<div class="demo-preview-content jcp-split-layout jcp-media-text-grid <?php echo esc_attr( jcp_media_position_class( $position ) ); ?>" data-jcp-split-path="<?php echo esc_attr( $path ); ?>" data-jcp-media-position-path="<?php echo esc_attr( $path . '.media_position' ); ?>">
		<div class="demo-preview-text jcp-split-col jcp-split-col--copy" data-jcp-split-col="copy">
			<div class="demo-badge"<?php echo empty( $props['show_badge'] ) ? ' style="display:none"' : ''; ?>>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<circle cx="12" cy="12" r="10"/>
					<polygon points="10 8 16 12 10 16 10 8"/>
				</svg>
				<span<?php jcp_niche_editable_attr( $path . '.badge' ); ?>><?php echo esc_html( jcp_niche_split_field_display( $props, 'badge' ) ); ?></span>
			</div>
			<?php if ( trim( (string) ( $props['headline'] ?? '' ) ) !== '' ) : ?>
				<h3 class="demo-preview-title"<?php jcp_niche_editable_attr( $path . '.headline' ); ?>><?php echo esc_html( (string) $props['headline'] ); ?></h3>
			<?php endif; ?>
			<p class="rankings-subtitle jcp-split-subheadline"<?php echo empty( $props['show_subheadline'] ) ? ' style="display:none"' : ''; ?><?php jcp_niche_editable_attr( $path . '.subheadline' ); ?>><?php
			$subheadline = trim( (string) ( $props['subheadline'] ?? '' ) );
			echo esc_html( $subheadline !== '' ? $subheadline : __( 'Add a subheadline for this section.', 'jcp-core' ) );
			?></p>
			<p class="demo-preview-cue"<?php echo empty( $props['show_cue'] ) ? ' style="display:none"' : ''; ?><?php jcp_niche_editable_attr( $path . '.cue' ); ?>><?php echo esc_html( jcp_niche_split_field_display( $props, 'cue' ) ); ?></p>
			<p class="demo-preview-description"<?php echo empty( $props['show_body'] ) ? ' style="display:none"' : ''; ?><?php jcp_niche_editable_attr( $path . '.body' ); ?>><?php
			$body = trim( (string) ( $props['body'] ?? '' ) );
			echo esc_html( $body !== '' ? $body : __( 'Add body copy for this section.', 'jcp-core' ) );
			?></p>
			<div class="demo-cta-wrapper"<?php echo ( empty( $props['show_cta'] ) && empty( $props['show_cta_note'] ) ) ? ' style="display:none"' : ''; ?>>
				<div class="demo-cta-slot benefits-cta-slot"<?php echo empty( $props['show_cta'] ) ? ' style="display:none"' : ''; ?><?php jcp_niche_optional_slot_attr( $path . '.cta_primary', 'cta', __( 'Button', 'jcp-core' ) ); ?>>
					<?php if ( ! empty( $props['show_cta'] ) && $primary['label'] !== '' ) : ?>
						<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary demo-cta-primary"<?php jcp_niche_editable_link_attr( $path . '.cta_primary' ); ?>>
							<span><?php echo esc_html( $primary['label'] ); ?></span>
							<?php jcp_component_chevron_svg( 20 ); ?>
						</a>
					<?php endif; ?>
				</div>
				<p class="demo-cta-note"<?php echo empty( $props['show_cta_note'] ) ? ' style="display:none"' : ''; ?><?php jcp_niche_editable_attr( $path . '.cta_note' ); ?>><?php echo esc_html( jcp_niche_split_field_display( $props, 'cta_note' ) ); ?></p>
			</div>
		</div>
		<div class="demo-preview-visual jcp-media-text-media jcp-split-col jcp-split-col--media" data-jcp-split-col="media">
			<?php
			jcp_media_render_slot(
				[
					'path'               => $path,
					'media_type'         => $media['media_type'],
					'image_url'          => $media['image_url'],
					'video_url'          => $media['video_url'],
					'media_alt'          => $media['media_alt'],
					'default_image'      => jcp_media_default_phone_image(),
					'phone_mockup_style' => $phone_style,
					'phone_render'       => function () use ( $demo_url, $phone_style ) {
						if ( $phone_style === 'live_demo' ) {
							jcp_component_demo_app_phone( $demo_url );
							return;
						}
						jcp_component_demo_app_phone( '' );
					},
					'img_attrs'          => [
						'class'   => 'demo-preview-slot-image jcp-media-text-image',
						'loading' => 'lazy',
					],
				]
			);
			?>
		</div>
	</div>
	<?php
	echo $card_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( $wrap_container ) {
		echo '</div>';
	}
	printf( '</%s>', esc_attr( $outer_tag ) );
}
