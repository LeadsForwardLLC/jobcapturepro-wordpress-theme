<?php
/**
 * Shared media slot rendering for block sections (image / video / phone mockup).
 *
 * @package JCP_Core
 */

/**
 * Normalize a stored media URL for the current site (relative paths, migrated production URLs).
 *
 * @param string $url Raw URL.
 */
function jcp_media_resolve_url( string $url ): string {
	$url = trim( $url );
	if ( $url === '' ) {
		return '';
	}

	if ( preg_match( '#^(?:https?:)?//[^/]+(/wp-content/.+)$#i', $url, $match ) ) {
		return home_url( $match[1] );
	}

	if ( str_starts_with( $url, '/wp-content/' ) ) {
		return home_url( $url );
	}

	if ( str_starts_with( $url, '//' ) ) {
		return ( is_ssl() ? 'https:' : 'http:' ) . $url;
	}

	return $url;
}

/**
 * Whether a URL is an embedded video (YouTube/Vimeo).
 *
 * @param string $url URL.
 */
function jcp_media_is_video_url( string $url ): bool {
	return (bool) preg_match( '#(?:youtube\.com|youtu\.be|vimeo\.com)#i', $url );
}

/**
 * Resolve the best image URL from block props (attachment ID beats stored strings).
 *
 * @param array<string, mixed> $props Block props.
 */
function jcp_media_resolve_image_url_from_props( array $props ): string {
	$id_keys = [ 'image_attachment_id', 'media_attachment_id' ];
	foreach ( $id_keys as $key ) {
		$id = (int) ( $props[ $key ] ?? 0 );
		if ( $id <= 0 ) {
			continue;
		}
		$attached = wp_get_attachment_url( $id );
		if ( is_string( $attached ) && $attached !== '' ) {
			return $attached;
		}
	}

	$url = trim( (string) ( $props['image_url'] ?? '' ) );
	if ( $url === '' || jcp_media_is_video_url( $url ) ) {
		$shared = trim( (string) ( $props['media_url'] ?? '' ) );
		if ( $shared !== '' && ! jcp_media_is_video_url( $shared ) ) {
			$url = $shared;
		} else {
			$url = '';
		}
	}

	return jcp_media_resolve_url( $url );
}

/**
 * Resolve video embed URL from block props.
 *
 * @param array<string, mixed> $props Block props.
 */
function jcp_media_resolve_video_url_from_props( array $props ): string {
	$url = trim( (string) ( $props['media_url'] ?? '' ) );
	return jcp_media_is_video_url( $url ) ? $url : '';
}

/**
 * Normalize media type string.
 *
 * @param string $type Raw type.
 */
function jcp_media_normalize_type( string $type ): string {
	$type = sanitize_key( $type );
	return in_array( $type, [ 'image', 'video', 'phone_mockup' ], true ) ? $type : 'image';
}

/**
 * Echo data attributes for an editable media field group.
 *
 * @param string               $path   Flat content path prefix (e.g. conversion, hero).
 * @param array<string, mixed> $extra  Extra data attributes.
 */
function jcp_media_editable_attrs( string $path, array $extra = [] ): void {
	$attrs = array_merge(
		[
			'data-jcp-media-path' => $path,
		],
		$extra
	);
	foreach ( $attrs as $key => $value ) {
		if ( $value === null || $value === '' ) {
			continue;
		}
		printf( ' %s="%s"', esc_attr( (string) $key ), esc_attr( (string) $value ) );
	}
}

/**
 * Parse a YouTube or Vimeo URL into embed metadata.
 *
 * @param string $url Video URL.
 * @return array{provider:string,id:string,is_short:bool,embed_url:string}|null
 */
function jcp_media_parse_embed_video( string $url ): ?array {
	$url = trim( $url );
	if ( $url === '' ) {
		return null;
	}

	if ( preg_match( '#(?:youtube\.com/(?:watch\?(?:[^&\s]+&)*v=|embed/|shorts/|v/)|youtu\.be/)([a-zA-Z0-9_-]+)#i', $url, $yt ) ) {
		$id = $yt[1];
		return [
			'provider'   => 'youtube',
			'id'         => $id,
			'is_short'   => (bool) preg_match( '#/shorts/#i', $url ),
			'embed_url'  => 'https://www.youtube.com/embed/' . rawurlencode( $id ),
		];
	}

	if ( preg_match( '#vimeo\.com/(?:video/)?(\d+)#i', $url, $vm ) ) {
		return [
			'provider'   => 'vimeo',
			'id'         => $vm[1],
			'is_short'   => false,
			'embed_url'  => 'https://player.vimeo.com/video/' . rawurlencode( $vm[1] ),
		];
	}

	return null;
}

/**
 * Render a video embed or file player.
 *
 * @param string $url   Video URL.
 * @param string $title Accessible title.
 */
function jcp_media_render_video( string $url, string $title = '' ): void {
	$url   = trim( $url );
	$title = $title !== '' ? $title : __( 'Video', 'jcp-core' );
	if ( $url === '' ) {
		return;
	}

	$embed = jcp_media_parse_embed_video( $url );
	$wrap_class = 'jcp-media-text-video-wrap jcp-media-video-wrap';
	if ( $embed && ! empty( $embed['is_short'] ) ) {
		$wrap_class .= ' jcp-media-video-wrap--short';
	}
	?>
	<div class="<?php echo esc_attr( $wrap_class ); ?>">
		<?php if ( $embed ) : ?>
			<iframe
				src="<?php echo esc_url( $embed['embed_url'] ); ?>"
				title="<?php echo esc_attr( $title ); ?>"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
				allowfullscreen
				loading="lazy"
			></iframe>
		<?php else : ?>
			<video class="jcp-media-text-video jcp-media-video-file" src="<?php echo esc_url( $url ); ?>" controls playsinline preload="metadata"></video>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render image with per-instance alt text.
 *
 * @param string               $url          Image URL.
 * @param string               $alt          Alt text for this block instance.
 * @param string               $path         Flat path prefix for editor.
 * @param array<string, mixed> $img_attrs    Extra img attributes.
 */
function jcp_media_render_image( string $url, string $alt, string $url_path, string $alt_path, array $img_attrs = [] ): void {
	$url = jcp_media_resolve_url( trim( $url ) );
	$class = 'jcp-editable-media-image';
	if ( ! empty( $img_attrs['class'] ) ) {
		$class .= ' ' . (string) $img_attrs['class'];
		unset( $img_attrs['class'] );
	}
	?>
	<img
		<?php if ( $url !== '' ) : ?>src="<?php echo esc_url( $url ); ?>" <?php endif; ?>
		alt="<?php echo esc_attr( $alt ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		data-jcp-media-url-path="<?php echo esc_attr( $url_path ); ?>"
		data-jcp-media-alt-path="<?php echo esc_attr( $alt_path ); ?>"
		<?php
		foreach ( $img_attrs as $attr => $val ) {
			printf( '%s="%s" ', esc_attr( (string) $attr ), esc_attr( (string) $val ) );
		}
		?>
	/>
	<?php
}

/**
 * Render a media slot with image, video, and optional phone mockup variants.
 *
 * @param array<string, mixed> $config {
 *   @type string        $path          Flat content path prefix.
 *   @type string        $media_type    image|video|phone_mockup.
 *   @type string        $media_url     Video URL (or legacy shared URL).
 *   @type string        $image_url     Image URL when separate from media_url.
 *   @type string        $video_url     Video URL when separate from image_url.
 *   @type string        $media_alt     Per-instance alt text.
 *   @type string        $url_path      Flat path for URL (default path.media_url).
 *   @type string        $alt_path      Flat path for alt (default path.media_alt).
 *   @type string        $default_image Fallback image URL.
 *   @type callable|null $phone_render  Callback to render phone mockup markup.
 *   @type array<string,mixed> $img_attrs Extra img tag attributes.
 * }
 *   @type string        $phone_mockup_style live_demo|app_shell — labels the phone mockup variant.
 */
function jcp_media_render_slot( array $config ): void {
	$path          = (string) ( $config['path'] ?? '' );
	$media_type    = jcp_media_normalize_type( (string) ( $config['media_type'] ?? 'image' ) );
	$media_url     = trim( (string) ( $config['media_url'] ?? '' ) );
	$video_url     = trim( (string) ( $config['video_url'] ?? '' ) );
	$image_url     = trim( (string) ( $config['image_url'] ?? '' ) );
	$media_alt     = trim( (string) ( $config['media_alt'] ?? '' ) );
	$default_image = trim( (string) ( $config['default_image'] ?? '' ) );
	$phone_render  = $config['phone_render'] ?? null;
	$phone_style   = (string) ( $config['phone_mockup_style'] ?? '' );
	$img_attrs     = is_array( $config['img_attrs'] ?? null ) ? $config['img_attrs'] : [];
	$url_path      = (string) ( $config['url_path'] ?? ( $path !== '' ? $path . '.media_url' : '' ) );
	$alt_path      = (string) ( $config['alt_path'] ?? ( $path !== '' ? $path . '.media_alt' : '' ) );

	if ( $image_url === '' && $media_url !== '' && ! jcp_media_is_video_url( $media_url ) ) {
		$image_url = $media_url;
	}
	if ( $video_url === '' && jcp_media_is_video_url( $media_url ) ) {
		$video_url = $media_url;
	}
	if ( $image_url === '' || jcp_media_is_video_url( $image_url ) ) {
		$image_url = $default_image;
	}

	if ( $media_type === 'phone_mockup' && ! is_callable( $phone_render ) ) {
		$media_type = 'image';
	}
	$types_attr = is_callable( $phone_render ) ? 'image,video,phone_mockup' : 'image,video';
	$slot_attrs = [
		'data-jcp-media-type'     => $media_type,
		'data-jcp-media-types'    => $types_attr,
		'data-jcp-media-url-path' => $url_path,
		'data-jcp-media-alt-path' => $alt_path,
	];
	if ( $url_path !== '' && str_ends_with( $url_path, '.image_url' ) && $path !== '' ) {
		$slot_attrs['data-jcp-media-video-url-path'] = $path . '.media_url';
	}
	if ( is_callable( $phone_render ) && $phone_style !== '' ) {
		$slot_attrs['data-jcp-phone-mockup-style'] = $phone_style;
	}
	if ( is_callable( $phone_render ) && $phone_style === 'live_demo' ) {
		$slot_attrs['data-jcp-phone-screen-editable'] = 'true';
	}
	?>
	<div
		class="jcp-media-slot"
		<?php
		jcp_media_editable_attrs(
			$path,
			$slot_attrs
		);
		if ( function_exists( 'jcp_niche_user_can_inline_edit' ) && jcp_niche_user_can_inline_edit() ) {
			echo ' onclick="return window.jcpOpenMediaEditor&amp;&amp;window.jcpOpenMediaEditor(this,event)"';
		}
		?>
	>
		<?php if ( is_callable( $phone_render ) ) : ?>
			<div class="jcp-media-variant jcp-media-variant--phone_mockup"<?php echo $media_type !== 'phone_mockup' ? ' hidden' : ''; ?>>
				<?php $phone_render(); ?>
			</div>
		<?php endif; ?>
		<div class="jcp-media-variant jcp-media-variant--image"<?php echo $media_type !== 'image' ? ' hidden' : ''; ?>>
			<?php
			if ( $url_path !== '' && $alt_path !== '' ) {
				jcp_media_render_image( $image_url, $media_alt, $url_path, $alt_path, $img_attrs );
			}
			?>
		</div>
		<div class="jcp-media-variant jcp-media-variant--video"<?php echo $media_type !== 'video' ? ' hidden' : ''; ?>>
			<?php jcp_media_render_video( $video_url, $media_alt ); ?>
		</div>
	</div>
	<?php
}

/**
 * Split-layout modifier class for media on left or right.
 *
 * @param string $position left|right.
 */
function jcp_media_position_class( string $position ): string {
	return 'jcp-split-layout--media-' . ( $position === 'left' ? 'left' : 'right' );
}

/**
 * Default photo shown inside the hero phone mockup.
 */
function jcp_media_default_phone_image(): string {
	return 'https://jobcapturepro.com/wp-content/uploads/2025/12/jcp-user-photo.jpg';
}

/**
 * Resolve the image URL for a phone mockup screen photo.
 *
 * @param array<string, mixed> $props Block / hero props.
 */
function jcp_media_resolve_phone_image( array $props, int $post_id = 0 ): string {
	$id = (int) ( $props['phone_image_attachment_id'] ?? 0 );
	if ( $id > 0 ) {
		$attached = wp_get_attachment_url( $id );
		if ( is_string( $attached ) && $attached !== '' ) {
			return $attached;
		}
	}
	$url = trim( (string) ( $props['phone_image_url'] ?? '' ) );
	if ( $url !== '' ) {
		return jcp_media_resolve_url( $url );
	}
	if ( $post_id <= 0 ) {
		$post_id = (int) get_queried_object_id();
	}
	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post && $post->post_type === 'jcp_niche_landing' ) {
			$featured = get_the_post_thumbnail_url( $post_id, 'large' );
			if ( is_string( $featured ) && $featured !== '' ) {
				return $featured;
			}
		}
	}
	return jcp_media_default_phone_image();
}

/**
 * Read media fields from props with legacy image_url / image_alt aliases.
 *
 * @param array<string, mixed> $props Block props.
 * @return array{media_type:string,media_url:string,media_alt:string,media_position:string,phone_image_url:string}
 */
function jcp_media_props_from_block( array $props ): array {
	$image_url = jcp_media_resolve_image_url_from_props( $props );
	$video_url = jcp_media_resolve_video_url_from_props( $props );
	$alt       = trim( (string) ( $props['media_alt'] ?? $props['image_alt'] ?? '' ) );
	$type      = (string) ( $props['media_type'] ?? '' );
	if ( $type === '' && ! empty( $props['phone_image_url'] ) ) {
		$type = 'phone_mockup';
	}
	$type = $type !== '' ? jcp_media_normalize_type( $type ) : 'image';

	return [
		'media_type'       => $type,
		'image_url'        => $image_url,
		'video_url'        => $video_url,
		'media_url'        => $type === 'video' ? $video_url : $image_url,
		'media_alt'        => $alt,
		'phone_image_url'  => trim( (string) ( $props['phone_image_url'] ?? '' ) ),
		'media_position'   => in_array( (string) ( $props['media_position'] ?? 'right' ), [ 'left', 'right' ], true )
			? (string) $props['media_position']
			: 'right',
	];
}
