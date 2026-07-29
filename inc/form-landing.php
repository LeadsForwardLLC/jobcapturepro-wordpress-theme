<?php
/**
 * Form Landing — full-screen distraction-free page template helpers + admin.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key for form landing settings.
 */
function jcp_form_landing_meta_key(): string {
	return '_jcp_form_landing';
}

/**
 * Default settings.
 *
 * @return array<string, mixed>
 */
function jcp_form_landing_defaults(): array {
	return [
		'logo_id'         => 0,
		'logo_url'        => 'https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png',
		'title'           => '',
		'supporting'      => '',
		'reassurance'     => '',
		'embed'           => '',
		'close_fallback'  => '/personalized-demo/',
	];
}

/**
 * Whether a post uses the Form Landing template.
 *
 * @param int|WP_Post|null $post Post.
 */
function jcp_is_form_landing_template( $post = null ): bool {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return false;
	}
	return get_page_template_slug( $post ) === 'page-form-landing.php';
}

/**
 * Get merged form landing settings for a post.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_form_landing_get_settings( int $post_id ): array {
	$stored = get_post_meta( $post_id, jcp_form_landing_meta_key(), true );
	$stored = is_array( $stored ) ? $stored : [];
	$out    = array_merge( jcp_form_landing_defaults(), $stored );

	$out['logo_id']        = absint( $out['logo_id'] ?? 0 );
	$out['logo_url']       = esc_url_raw( (string) ( $out['logo_url'] ?? '' ) );
	$out['title']          = sanitize_text_field( (string) ( $out['title'] ?? '' ) );
	$out['supporting']     = sanitize_textarea_field( (string) ( $out['supporting'] ?? '' ) );
	$out['reassurance']    = sanitize_text_field( (string) ( $out['reassurance'] ?? '' ) );
	$out['embed']          = (string) ( $out['embed'] ?? '' );
	$out['close_fallback'] = (string) ( $out['close_fallback'] ?? '/personalized-demo/' );

	return $out;
}

/**
 * Resolve logo URL from settings.
 *
 * @param array<string, mixed> $settings Settings.
 */
function jcp_form_landing_logo_url( array $settings ): string {
	$logo_id = absint( $settings['logo_id'] ?? 0 );
	if ( $logo_id > 0 ) {
		$url = wp_get_attachment_image_url( $logo_id, 'medium' );
		if ( is_string( $url ) && $url !== '' ) {
			return $url;
		}
	}
	$url = trim( (string) ( $settings['logo_url'] ?? '' ) );
	if ( $url !== '' ) {
		return $url;
	}
	return (string) jcp_form_landing_defaults()['logo_url'];
}

/**
 * Resolve absolute close fallback URL.
 *
 * @param array<string, mixed> $settings Settings.
 */
function jcp_form_landing_close_url( array $settings ): string {
	$raw = trim( (string) ( $settings['close_fallback'] ?? '' ) );
	if ( $raw === '' ) {
		$raw = '/personalized-demo/';
	}
	if ( preg_match( '#^https?://#i', $raw ) ) {
		return $raw;
	}
	if ( $raw[0] !== '/' ) {
		$raw = '/' . $raw;
	}
	return home_url( $raw );
}

/**
 * Allowed HTML for custom embeds (iframes + post defaults).
 *
 * @return array<string, array<string, bool>>
 */
function jcp_form_landing_allowed_embed_html(): array {
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
	];
	return $allowed;
}

/**
 * Sanitize embed field on save.
 *
 * @param string $raw Raw embed.
 */
function jcp_form_landing_sanitize_embed( string $raw ): string {
	$raw = trim( wp_unslash( $raw ) );
	if ( $raw === '' ) {
		return '';
	}
	if ( function_exists( 'jcp_fluent_sanitize_shortcode' ) ) {
		$shortcode = jcp_fluent_sanitize_shortcode( $raw );
		if ( $shortcode !== '' ) {
			return $shortcode;
		}
	}
	// Single shortcode-ish line (other plugins).
	if ( preg_match( '/^\[[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]$/', $raw ) ) {
		return $raw;
	}
	return wp_kses( $raw, jcp_form_landing_allowed_embed_html() );
}

/**
 * Render embed HTML (shortcode or trusted markup).
 *
 * @param string $embed Stored embed.
 */
function jcp_form_landing_render_embed( string $embed ): void {
	$embed = trim( $embed );
	if ( $embed === '' ) {
		return;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcodes / kses'd HTML.
	echo do_shortcode( $embed );
}

/**
 * Whether embed likely needs Fluent Forms bridge styles.
 *
 * @param string $embed Embed string.
 */
function jcp_form_landing_embed_needs_fluent( string $embed ): bool {
	return $embed !== '' && stripos( $embed, '[fluentform' ) !== false;
}

/**
 * Register meta box.
 */
function jcp_form_landing_register_meta_box(): void {
	add_meta_box(
		'jcp_form_landing',
		__( 'Form Landing', 'jcp-core' ),
		'jcp_form_landing_render_meta_box',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'jcp_form_landing_register_meta_box' );

/**
 * Meta box UI (shown when Form Landing template is selected).
 *
 * @param WP_Post $post Post.
 */
function jcp_form_landing_render_meta_box( WP_Post $post ): void {
	$settings = jcp_form_landing_get_settings( (int) $post->ID );
	$logo_id  = absint( $settings['logo_id'] );
	$logo_url = jcp_form_landing_logo_url( $settings );
	$is_tpl   = jcp_is_form_landing_template( $post );
	wp_nonce_field( 'jcp_form_landing_save', 'jcp_form_landing_nonce' );
	?>
	<div id="jcp-form-landing-metabox" data-jcp-form-landing-metabox <?php echo $is_tpl ? '' : 'hidden'; ?>>
		<p class="description"><?php esc_html_e( 'Fields for the Form Landing template. Assign Template → Form Landing under Page Attributes.', 'jcp-core' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Logo', 'jcp-core' ); ?></th>
				<td>
					<div class="jcp-form-landing-logo-preview" style="margin-bottom:8px;">
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="max-height:40px;width:auto;" />
						<?php endif; ?>
					</div>
					<input type="hidden" name="jcp_form_landing[logo_id]" id="jcp_form_landing_logo_id" value="<?php echo esc_attr( (string) $logo_id ); ?>" />
					<input type="url" class="large-text" name="jcp_form_landing[logo_url]" id="jcp_form_landing_logo_url" value="<?php echo esc_attr( (string) $settings['logo_url'] ); ?>" placeholder="https://…" />
					<p>
						<button type="button" class="button" id="jcp_form_landing_logo_pick"><?php esc_html_e( 'Select from Media Library', 'jcp-core' ); ?></button>
						<button type="button" class="button" id="jcp_form_landing_logo_clear"><?php esc_html_e( 'Clear', 'jcp-core' ); ?></button>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="jcp_form_landing_title"><?php esc_html_e( 'Page title', 'jcp-core' ); ?></label></th>
				<td>
					<input type="text" class="large-text" id="jcp_form_landing_title" name="jcp_form_landing[title]" value="<?php echo esc_attr( (string) $settings['title'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Optional. Leave empty to hide the title on the page.', 'jcp-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="jcp_form_landing_supporting"><?php esc_html_e( 'Supporting text', 'jcp-core' ); ?></label></th>
				<td>
					<textarea class="large-text" rows="3" id="jcp_form_landing_supporting" name="jcp_form_landing[supporting]"><?php echo esc_textarea( (string) $settings['supporting'] ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="jcp_form_landing_reassurance"><?php esc_html_e( 'Reassurance line', 'jcp-core' ); ?></label></th>
				<td>
					<input type="text" class="large-text" id="jcp_form_landing_reassurance" name="jcp_form_landing[reassurance]" value="<?php echo esc_attr( (string) $settings['reassurance'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Optional. Example: Takes 3–5 minutes · Estimates are fine', 'jcp-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="jcp_form_landing_embed"><?php esc_html_e( 'Shortcode or embed code', 'jcp-core' ); ?></label></th>
				<td>
					<textarea class="large-text code" rows="6" id="jcp_form_landing_embed" name="jcp_form_landing[embed]" placeholder='[fluentform id="3"]'><?php echo esc_textarea( (string) $settings['embed'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Paste a Fluent Forms shortcode, another shortcode, or iframe/HTML embed.', 'jcp-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="jcp_form_landing_close"><?php esc_html_e( 'Close button fallback URL', 'jcp-core' ); ?></label></th>
				<td>
					<input type="text" class="large-text" id="jcp_form_landing_close" name="jcp_form_landing[close_fallback]" value="<?php echo esc_attr( (string) $settings['close_fallback'] ); ?>" placeholder="/personalized-demo/" />
					<p class="description"><?php esc_html_e( 'Used when there is no previous page in browser history. Relative or absolute URL.', 'jcp-core' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<script>
	(function () {
		var box = document.getElementById('jcp-form-landing-metabox');
		var wrap = document.getElementById('jcp_form_landing');
		function sync() {
			var select = document.getElementById('page_template');
			var isForm = select && select.value === 'page-form-landing.php';
			if (box) box.hidden = !isForm;
			if (wrap) wrap.style.display = isForm ? '' : 'none';
		}
		document.addEventListener('change', function (e) {
			if (e.target && e.target.id === 'page_template') sync();
		});
		sync();

		var pick = document.getElementById('jcp_form_landing_logo_pick');
		var clear = document.getElementById('jcp_form_landing_logo_clear');
		var idInput = document.getElementById('jcp_form_landing_logo_id');
		var urlInput = document.getElementById('jcp_form_landing_logo_url');
		var preview = document.querySelector('.jcp-form-landing-logo-preview');
		if (pick && typeof wp !== 'undefined' && wp.media) {
			pick.addEventListener('click', function (e) {
				e.preventDefault();
				var frame = wp.media({ title: 'Select logo', button: { text: 'Use logo' }, multiple: false });
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					if (idInput) idInput.value = att.id || '';
					if (urlInput) urlInput.value = (att.url || '');
					if (preview) preview.innerHTML = att.url ? '<img src="' + att.url + '" alt="" style="max-height:40px;width:auto;" />' : '';
				});
				frame.open();
			});
		}
		if (clear) {
			clear.addEventListener('click', function (e) {
				e.preventDefault();
				if (idInput) idInput.value = '0';
				if (urlInput) urlInput.value = '';
				if (preview) preview.innerHTML = '';
			});
		}
	})();
	</script>
	<?php
}

/**
 * Enqueue media for logo picker on page edit.
 *
 * @param string $hook_suffix Admin hook.
 */
function jcp_form_landing_admin_assets( string $hook_suffix ): void {
	if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'page' ) {
		return;
	}
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'jcp_form_landing_admin_assets' );

/**
 * Save meta box.
 *
 * @param int $post_id Post ID.
 */
function jcp_form_landing_save_meta_box( int $post_id ): void {
	if ( ! isset( $_POST['jcp_form_landing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['jcp_form_landing_nonce'] ) ), 'jcp_form_landing_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'page' ) {
		return;
	}

	$raw = isset( $_POST['jcp_form_landing'] ) && is_array( $_POST['jcp_form_landing'] )
		? wp_unslash( $_POST['jcp_form_landing'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: [];

	$close = trim( (string) ( $raw['close_fallback'] ?? '/personalized-demo/' ) );
	if ( $close !== '' && ! preg_match( '#^https?://#i', $close ) && isset( $close[0] ) && $close[0] !== '/' ) {
		$close = '/' . $close;
	}

	$settings = [
		'logo_id'        => absint( $raw['logo_id'] ?? 0 ),
		'logo_url'       => esc_url_raw( (string) ( $raw['logo_url'] ?? '' ) ),
		'title'          => sanitize_text_field( (string) ( $raw['title'] ?? '' ) ),
		'supporting'     => sanitize_textarea_field( (string) ( $raw['supporting'] ?? '' ) ),
		'reassurance'    => sanitize_text_field( (string) ( $raw['reassurance'] ?? '' ) ),
		'embed'          => jcp_form_landing_sanitize_embed( (string) ( $raw['embed'] ?? '' ) ),
		'close_fallback' => $close === '' ? '/personalized-demo/' : sanitize_text_field( $close ),
	];

	update_post_meta( $post_id, jcp_form_landing_meta_key(), $settings );
}
add_action( 'save_post_page', 'jcp_form_landing_save_meta_box' );
