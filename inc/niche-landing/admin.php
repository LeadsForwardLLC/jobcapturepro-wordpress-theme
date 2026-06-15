<?php
/**
 * Admin meta box for niche landing JSON content.
 *
 * @package JCP_Core
 */

/**
 * Register meta box.
 */
function jcp_niche_register_meta_box(): void {
	add_meta_box(
		'jcp_niche_quick',
		__( 'Landing Page — Quick Edit', 'jcp-core' ),
		'jcp_niche_render_quick_meta_box',
		'jcp_niche_landing',
		'normal',
		'high'
	);
	add_meta_box(
		'jcp_niche_content',
		__( 'Landing Page — Advanced JSON', 'jcp-core' ),
		'jcp_niche_render_meta_box',
		'jcp_niche_landing',
		'normal',
		'default'
	);
	add_meta_box(
		'jcp_niche_quick',
		__( 'Landing Page — Quick Edit', 'jcp-core' ),
		'jcp_niche_render_quick_meta_box',
		'page',
		'normal',
		'high'
	);
	add_meta_box(
		'jcp_niche_content',
		__( 'Landing Page — Advanced JSON', 'jcp-core' ),
		'jcp_niche_render_meta_box',
		'page',
		'normal',
		'default'
	);
}

/**
 * Quick-edit fields (merged into JSON on save).
 *
 * @param WP_Post $post Post.
 */
function jcp_niche_render_quick_meta_box( WP_Post $post ): void {
	if ( $post->post_type === 'page' && get_page_template_slug( $post->ID ) !== 'page-referral-program.php' ) {
		echo '<p class="description">' . esc_html__( 'Assign the “Referral Program” page template to use structured landing content.', 'jcp-core' ) . '</p>';
		return;
	}
	$c     = jcp_niche_get_content( (int) $post->ID );
	$edit  = add_query_arg( 'jcp_edit', '1', get_permalink( $post ) );
	$seo   = $c['seo'] ?? [];
	$hero  = $c['hero'] ?? [];
	$final = $c['final_cta'] ?? [];
	?>
	<p>
		<a href="<?php echo esc_url( $edit ); ?>" class="button button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Edit on live page (click text & buttons)', 'jcp-core' ); ?>
		</a>
		<span class="description"><?php esc_html_e( 'On the live page: click “Click to edit page”, then click any highlighted text or button to edit.', 'jcp-core' ); ?></span>
	</p>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="jcp_niche_seo_title"><?php esc_html_e( 'SEO title', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="large-text" id="jcp_niche_seo_title" name="jcp_niche_quick[seo_title]" value="<?php echo esc_attr( $seo['title'] ?? '' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="jcp_niche_hero_h1"><?php esc_html_e( 'Hero H1', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="large-text" id="jcp_niche_hero_h1" name="jcp_niche_quick[hero_h1]" value="<?php echo esc_attr( $hero['h1'] ?? '' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="jcp_niche_hero_sub"><?php esc_html_e( 'Hero subheadline', 'jcp-core' ); ?></label></th>
			<td><textarea class="large-text" rows="3" id="jcp_niche_hero_sub" name="jcp_niche_quick[hero_sub]"><?php echo esc_textarea( $hero['subheadline'] ?? '' ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="jcp_niche_final_h"><?php esc_html_e( 'Final CTA headline', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="large-text" id="jcp_niche_final_h" name="jcp_niche_quick[final_h]" value="<?php echo esc_attr( $final['headline'] ?? '' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="jcp_niche_final_btn"><?php esc_html_e( 'Final CTA button', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="regular-text" id="jcp_niche_final_btn" name="jcp_niche_quick[final_btn]" value="<?php echo esc_attr( $final['cta_primary']['label'] ?? '' ); ?>" /></td>
		</tr>
	</table>
	<?php
}
add_action( 'add_meta_boxes', 'jcp_niche_register_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function jcp_niche_render_meta_box( WP_Post $post ): void {
	if ( $post->post_type === 'page' && get_page_template_slug( $post->ID ) !== 'page-referral-program.php' ) {
		echo '<p class="description">' . esc_html__( 'Assign the “Referral Program” page template to edit JSON content.', 'jcp-core' ) . '</p>';
		return;
	}
	wp_nonce_field( 'jcp_niche_content_save', 'jcp_niche_content_nonce' );
	$raw     = get_post_meta( $post->ID, jcp_niche_content_meta_key(), true );
	$display = is_string( $raw ) && $raw !== '' ? $raw : '';
	if ( $display === '' && $post->post_name === 'plumbing' ) {
		$preset  = jcp_niche_load_preset( 'plumbing' );
		$display = wp_json_encode( $preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && $post->post_name === 'hvac' ) {
		$preset  = jcp_niche_load_preset( 'hvac' );
		$display = wp_json_encode( $preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && ( $post->post_name === 'referral-program' || get_page_template_slug( $post->ID ) === 'page-referral-program.php' ) ) {
		$preset  = jcp_niche_load_preset( 'referral-program' );
		$display = wp_json_encode( $preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	?>
	<p class="description">
		<?php esc_html_e( 'Structured page content. Edit JSON directly or use a preset loader below.', 'jcp-core' ); ?>
	</p>
	<p>
		<button type="button" class="button" id="jcp-niche-load-plumbing-demo"><?php esc_html_e( 'Load plumbing demo JSON', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-hvac-demo"><?php esc_html_e( 'Load HVAC demo JSON', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-referral-demo"><?php esc_html_e( 'Load referral program JSON', 'jcp-core' ); ?></button>
	</p>
	<textarea name="jcp_niche_content_json" id="jcp_niche_content_json" rows="24" class="large-text code" style="width:100%;font-family:monospace;"><?php echo esc_textarea( $display ); ?></textarea>
	<script>
	(function () {
		function bindPreset(btnId, action) {
			var btn = document.getElementById(btnId);
			var ta = document.getElementById('jcp_niche_content_json');
			if (!btn || !ta) return;
			btn.addEventListener('click', function () {
				if (!confirm('Replace editor content with the selected preset?')) return;
				fetch(ajaxurl + '?action=' + action + '&_wpnonce=<?php echo esc_js( wp_create_nonce( 'jcp_niche_preset_json' ) ); ?>')
					.then(function (r) { return r.json(); })
					.then(function (data) {
						if (data && data.content) ta.value = data.content;
					});
			});
		}
		bindPreset('jcp-niche-load-plumbing-demo', 'jcp_niche_plumbing_json');
		bindPreset('jcp-niche-load-hvac-demo', 'jcp_niche_hvac_json');
		bindPreset('jcp-niche-load-referral-demo', 'jcp_niche_referral_json');
	})();
	</script>
	<?php
}

/**
 * AJAX: return pretty-printed plumbing JSON for admin editor.
 */
function jcp_niche_ajax_plumbing_json(): void {
	jcp_niche_ajax_preset_json( 'plumbing' );
}
add_action( 'wp_ajax_jcp_niche_plumbing_json', 'jcp_niche_ajax_plumbing_json' );

/**
 * AJAX: return pretty-printed HVAC JSON for admin editor.
 */
function jcp_niche_ajax_hvac_json(): void {
	jcp_niche_ajax_preset_json( 'hvac' );
}
add_action( 'wp_ajax_jcp_niche_hvac_json', 'jcp_niche_ajax_hvac_json' );

/**
 * AJAX: return pretty-printed referral program JSON for admin editor.
 */
function jcp_niche_ajax_referral_json(): void {
	jcp_niche_ajax_preset_json( 'referral-program' );
}
add_action( 'wp_ajax_jcp_niche_referral_json', 'jcp_niche_ajax_referral_json' );

/**
 * @param string $preset Preset slug.
 */
function jcp_niche_ajax_preset_json( string $preset ): void {
	check_ajax_referer( 'jcp_niche_preset_json' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error();
	}
	$data = jcp_niche_load_preset( $preset );
	wp_send_json_success(
		[
			'content' => wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
		]
	);
}

/**
 * Save meta box.
 *
 * @param int $post_id Post ID.
 */
function jcp_niche_save_meta_box( int $post_id ): void {
	if ( ! isset( $_POST['jcp_niche_content_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jcp_niche_content_nonce'] ) ), 'jcp_niche_content_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$content = jcp_niche_get_content( $post_id );
	if ( isset( $_POST['jcp_niche_quick'] ) && is_array( $_POST['jcp_niche_quick'] ) ) {
		$q = wp_unslash( $_POST['jcp_niche_quick'] );
		$content['seo']       = $content['seo'] ?? [];
		$content['hero']      = $content['hero'] ?? [];
		$content['final_cta'] = $content['final_cta'] ?? [];
		$content['final_cta']['cta_primary'] = $content['final_cta']['cta_primary'] ?? [];
		if ( ! empty( $q['seo_title'] ) ) {
			$content['seo']['title'] = sanitize_text_field( $q['seo_title'] );
		}
		if ( ! empty( $q['hero_h1'] ) ) {
			$content['hero']['h1'] = sanitize_text_field( $q['hero_h1'] );
		}
		if ( isset( $q['hero_sub'] ) ) {
			$content['hero']['subheadline'] = sanitize_textarea_field( $q['hero_sub'] );
		}
		if ( ! empty( $q['final_h'] ) ) {
			$content['final_cta']['headline'] = sanitize_text_field( $q['final_h'] );
		}
		if ( ! empty( $q['final_btn'] ) ) {
			$content['final_cta']['cta_primary']['label'] = sanitize_text_field( $q['final_btn'] );
		}
		jcp_niche_save_content( $post_id, $content );
	}

	if ( ! isset( $_POST['jcp_niche_content_json'] ) ) {
		return;
	}
	$json = wp_unslash( $_POST['jcp_niche_content_json'] );
	$json = is_string( $json ) ? trim( $json ) : '';
	if ( $json === '' ) {
		delete_post_meta( $post_id, jcp_niche_content_meta_key() );
		return;
	}
	$decoded = json_decode( $json, true );
	if ( ! is_array( $decoded ) ) {
		return;
	}
	jcp_niche_save_content( $post_id, $decoded );
}
add_action( 'save_post_jcp_niche_landing', 'jcp_niche_save_meta_box' );
add_action( 'save_post_page', 'jcp_niche_save_meta_box' );
