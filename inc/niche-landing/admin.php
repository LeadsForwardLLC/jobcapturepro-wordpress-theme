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
		'jcp_niche_content',
		__( 'Industry Page Content (JSON)', 'jcp-core' ),
		'jcp_niche_render_meta_box',
		'jcp_niche_landing',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'jcp_niche_register_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function jcp_niche_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'jcp_niche_content_save', 'jcp_niche_content_nonce' );
	$raw     = get_post_meta( $post->ID, jcp_niche_content_meta_key(), true );
	$display = is_string( $raw ) && $raw !== '' ? $raw : '';
	if ( $display === '' && $post->post_name === 'plumbing' ) {
		$preset  = jcp_niche_load_preset( 'plumbing' );
		$display = wp_json_encode( $preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	?>
	<p class="description">
		<?php esc_html_e( 'Structured page content. Edit JSON directly or use “Load plumbing demo” to reset from the preset file.', 'jcp-core' ); ?>
	</p>
	<p>
		<button type="button" class="button" id="jcp-niche-load-plumbing-demo"><?php esc_html_e( 'Load plumbing demo JSON', 'jcp-core' ); ?></button>
	</p>
	<textarea name="jcp_niche_content_json" id="jcp_niche_content_json" rows="24" class="large-text code" style="width:100%;font-family:monospace;"><?php echo esc_textarea( $display ); ?></textarea>
	<script>
	(function () {
		var btn = document.getElementById('jcp-niche-load-plumbing-demo');
		var ta = document.getElementById('jcp_niche_content_json');
		if (!btn || !ta) return;
		btn.addEventListener('click', function () {
			if (!confirm('Replace editor content with the plumbing demo preset?')) return;
			fetch(ajaxurl + '?action=jcp_niche_plumbing_json&_wpnonce=<?php echo esc_js( wp_create_nonce( 'jcp_niche_plumbing_json' ) ); ?>')
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data && data.content) ta.value = data.content;
				});
		});
	})();
	</script>
	<?php
}

/**
 * AJAX: return pretty-printed plumbing JSON for admin editor.
 */
function jcp_niche_ajax_plumbing_json(): void {
	check_ajax_referer( 'jcp_niche_plumbing_json' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error();
	}
	$preset = jcp_niche_load_preset( 'plumbing' );
	wp_send_json_success(
		[
			'content' => wp_json_encode( $preset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
		]
	);
}
add_action( 'wp_ajax_jcp_niche_plumbing_json', 'jcp_niche_ajax_plumbing_json' );

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
