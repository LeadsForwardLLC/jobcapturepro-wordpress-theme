<?php
/**
 * Admin meta box for niche landing JSON content.
 *
 * @package JCP_Core
 */

/**
 * Admin notice when a WP page needs a block template assigned.
 */
function jcp_niche_block_template_admin_hint(): string {
	return __( 'Assign the “JCP Block Page”, “Home”, or “Referral Program” template to use structured block content, document import, and the live page editor.', 'jcp-core' );
}

/**
 * Register meta box.
 */
function jcp_niche_register_meta_box(): void {
	$structured_types = [ 'jcp_niche_landing', 'jcp_page' ];
	foreach ( $structured_types as $post_type ) {
		add_meta_box(
			'jcp_niche_import',
			__( 'Landing Page — Import from Document', 'jcp-core' ),
			'jcp_niche_render_import_meta_box',
			$post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'jcp_niche_quick',
			__( 'Landing Page — Quick Edit', 'jcp-core' ),
			'jcp_niche_render_quick_meta_box',
			$post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'jcp_niche_content',
			__( 'Landing Page — Advanced JSON', 'jcp-core' ),
			'jcp_niche_render_meta_box',
			$post_type,
			'normal',
			'default'
		);
	}
	$page_boxes = [
		[
			'id'       => 'jcp_niche_import',
			'title'    => __( 'Landing Page — Import from Document', 'jcp-core' ),
			'callback' => 'jcp_niche_render_import_meta_box',
			'priority' => 'high',
		],
		[
			'id'       => 'jcp_niche_quick',
			'title'    => __( 'Landing Page — Quick Edit', 'jcp-core' ),
			'callback' => 'jcp_niche_render_quick_meta_box',
			'priority' => 'high',
		],
		[
			'id'       => 'jcp_niche_content',
			'title'    => __( 'Landing Page — Advanced JSON', 'jcp-core' ),
			'callback' => 'jcp_niche_render_meta_box',
			'priority' => 'default',
		],
	];
	foreach ( $page_boxes as $box ) {
		add_meta_box(
			$box['id'],
			$box['title'],
			$box['callback'],
			'page',
			'normal',
			$box['priority']
		);
	}
}

/**
 * Quick-edit fields (merged into JSON on save).
 *
 * @param WP_Post $post Post.
 */
function jcp_niche_render_quick_meta_box( WP_Post $post ): void {
	if ( $post->post_type === 'page' && ! jcp_page_uses_block_template( (int) $post->ID ) ) {
		echo '<p class="description">' . esc_html( jcp_niche_block_template_admin_hint() ) . '</p>';
		return;
	}
	$c     = jcp_page_get_content_flat( (int) $post->ID );
	$edit  = add_query_arg( 'jcp_edit', '1', get_permalink( $post ) );
	$hero  = $c['hero'] ?? [];
	$final = $c['final_cta'] ?? [];
	$is_industry  = $post->post_type === 'jcp_niche_landing';
	$is_marketing = $post->post_type === 'jcp_page'
		|| ( $post->post_type === 'page' && get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' );
	?>
	<?php if ( $is_industry || $is_marketing ) : ?>
		<div class="notice notice-info inline" style="margin: 0 0 1em; padding: 0.75em 1em;">
			<p style="margin: 0;">
				<strong><?php echo $is_industry ? esc_html__( 'Add a new trade page', 'jcp-core' ) : esc_html__( 'Build a marketing page', 'jcp-core' ); ?></strong><br />
				<?php
				printf(
					esc_html__( '1. Set the URL slug. 2. Paste the writer template (see JCP → Page System) or upload .docx/.txt, then click Build page. 3. Publish — add photos on the live page editor. SEO is in Rank Math. %s', 'jcp-core' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=jcp-theme-settings' ) ) . '">' . esc_html__( 'Full SOP →', 'jcp-core' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php endif; ?>
	<p>
		<a href="<?php echo esc_url( $edit ); ?>" class="button button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Edit on live page (click text & buttons)', 'jcp-core' ); ?>
		</a>
		<span class="description"><?php esc_html_e( 'On the live page: click “Click to edit page”, then click any highlighted text or button to edit.', 'jcp-core' ); ?></span>
	</p>
	<table class="form-table" role="presentation">
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
 * Document import meta box for industry pages.
 *
 * @param WP_Post $post Post.
 */
function jcp_niche_render_import_meta_box( WP_Post $post ): void {
	if ( $post->post_type === 'page' && ! jcp_page_uses_block_template( (int) $post->ID ) ) {
		echo '<p class="description">' . esc_html( jcp_niche_block_template_admin_hint() ) . '</p>';
		return;
	}
	wp_nonce_field( 'jcp_niche_import_doc', 'jcp_niche_import_nonce' );
	?>
	<p class="description">
		<?php esc_html_e( 'Paste plain text using the writer template (JCP → Page System → copy template), or upload a .docx / .txt export from Google Docs or Word. Random Word formatting is stripped — section headers must be ALL CAPS (HERO, WHAT IT IS, etc.). Images are added on the live page after publish.', 'jcp-core' ); ?>
	</p>
	<p>
		<label for="jcp_niche_import_doc"><strong><?php esc_html_e( 'Paste document text', 'jcp-core' ); ?></strong></label>
		<textarea name="jcp_niche_import_doc" id="jcp_niche_import_doc" rows="14" class="large-text code" style="width:100%;font-family:monospace;" placeholder="<?php esc_attr_e( 'Paste content starting at HERO…', 'jcp-core' ); ?>"></textarea>
	</p>
	<p>
		<label for="jcp_niche_import_file"><strong><?php esc_html_e( 'Or upload .docx / .txt', 'jcp-core' ); ?></strong></label><br />
		<input type="file" name="jcp_niche_import_file" id="jcp_niche_import_file" accept=".docx,.txt,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document" />
	</p>
	<p>
		<button type="button" class="button button-primary" id="jcp-niche-build-from-doc"><?php esc_html_e( 'Build page from document', 'jcp-core' ); ?></button>
		<span class="description" id="jcp-niche-import-status" style="margin-left:8px;"></span>
	</p>
	<script>
	(function () {
		var btn = document.getElementById('jcp-niche-build-from-doc');
		var ta = document.getElementById('jcp_niche_import_doc');
		var jsonTa = document.getElementById('jcp_niche_content_json');
		var status = document.getElementById('jcp-niche-import-status');
		var fileInput = document.getElementById('jcp_niche_import_file');
		if (!btn || !ta || !jsonTa) return;

		btn.addEventListener('click', function () {
			var body = new FormData();
			body.append('action', 'jcp_niche_parse_document');
			body.append('_wpnonce', '<?php echo esc_js( wp_create_nonce( 'jcp_niche_parse_document' ) ); ?>');
			body.append('post_id', '<?php echo (int) $post->ID; ?>');
			body.append('doc_text', ta.value || '');
			if (fileInput && fileInput.files && fileInput.files[0]) {
				body.append('doc_file', fileInput.files[0]);
			}
			status.textContent = '<?php echo esc_js( __( 'Building…', 'jcp-core' ) ); ?>';
			btn.disabled = true;
			fetch(ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					btn.disabled = false;
					if (!data || !data.success) {
						status.textContent = (data && data.data && data.data.message) ? data.data.message : '<?php echo esc_js( __( 'Import failed.', 'jcp-core' ) ); ?>';
						return;
					}
					jsonTa.value = data.data.content;
					status.textContent = '<?php echo esc_js( __( 'JSON ready — click Update to save.', 'jcp-core' ) ); ?>';
					jsonTa.focus();
				})
				.catch(function () {
					btn.disabled = false;
					status.textContent = '<?php echo esc_js( __( 'Import failed.', 'jcp-core' ) ); ?>';
				});
		});
	})();
	</script>
	<?php
}

/**
 * AJAX: parse writer document into page JSON.
 */
function jcp_niche_ajax_parse_document(): void {
	check_ajax_referer( 'jcp_niche_parse_document' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'jcp-core' ) ] );
	}

	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	$post    = $post_id > 0 ? get_post( $post_id ) : null;
	$text    = isset( $_POST['doc_text'] ) ? jcp_niche_normalize_document_text( wp_unslash( (string) $_POST['doc_text'] ) ) : '';

	if ( $text === '' && ! empty( $_FILES['doc_file']['tmp_name'] ) ) {
		$file = $_FILES['doc_file'];
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( $ext === 'docx' ) {
			$text = jcp_niche_extract_docx_text( (string) $file['tmp_name'] );
		} elseif ( $ext === 'txt' ) {
			$raw = file_get_contents( $file['tmp_name'] );
			$text = is_string( $raw ) ? jcp_niche_normalize_document_text( $raw ) : '';
		}
	}

	if ( $text === '' ) {
		wp_send_json_error( [ 'message' => __( 'Paste document text or upload a .docx / .txt file.', 'jcp-core' ) ] );
	}

	$niche_key   = $post instanceof WP_Post ? $post->post_name : '';
	$niche_label = $post instanceof WP_Post ? get_the_title( $post ) : '';
	$page_kind   = 'marketing';
	if ( $post instanceof WP_Post ) {
		if ( $post->post_type === 'jcp_niche_landing' ) {
			$page_kind = 'industry';
		} elseif ( get_page_template_slug( $post ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
			$page_kind = 'referral';
		} elseif ( get_page_template_slug( $post ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
			$page_kind = 'home';
		} elseif ( $post->post_type === 'jcp_page' || jcp_page_uses_block_template( (int) $post->ID ) ) {
			$page_kind = 'marketing';
		}
	}
	$parsed = jcp_page_parse_document( $text, $niche_key, $niche_label, $page_kind );
	$parsed = jcp_page_merge_parsed_content( $parsed, $post_id > 0 ? jcp_page_get_content( $post_id ) : [] );

	wp_send_json_success(
		[
			'content' => wp_json_encode( $parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
		]
	);
}
add_action( 'wp_ajax_jcp_niche_parse_document', 'jcp_niche_ajax_parse_document' );
add_action( 'wp_ajax_jcp_page_parse_document', 'jcp_niche_ajax_parse_document' );

/**
 * @param WP_Post $post Post.
 */
function jcp_niche_render_meta_box( WP_Post $post ): void {
	if ( $post->post_type === 'page' && ! jcp_page_uses_block_template( (int) $post->ID ) ) {
		echo '<p class="description">' . esc_html( jcp_niche_block_template_admin_hint() ) . '</p>';
		return;
	}
	wp_nonce_field( 'jcp_niche_content_save', 'jcp_niche_content_nonce' );
	$stored  = jcp_page_get_content( (int) $post->ID );
	$display = ! empty( $stored['blocks'] ) ? wp_json_encode( $stored, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
	if ( $display === '' && $post->post_name === 'plumbing' ) {
		$display = wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'plumbing' ), 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && $post->post_name === 'hvac' ) {
		$display = wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'hvac' ), 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && ( get_page_template_slug( $post->ID ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) ) {
		$display = wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'home' ), (int) $post->ID ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && ( $post->post_name === 'referral-program' || get_page_template_slug( $post->ID ) === 'page-referral-program.php' ) ) {
		$display = wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'referral-program' ), 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && $post->post_type === 'jcp_page' ) {
		$empty = jcp_page_legacy_to_blocks(
			[
				'page_kind'  => 'marketing',
				'page_key'   => $post->post_name,
				'page_label' => get_the_title( $post ),
				'preset'     => 'marketing',
			],
			(int) $post->ID
		);
		$empty['blocks'] = jcp_page_blocks_from_preset( 'marketing' );
		$display         = wp_json_encode( $empty, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $display === '' && $post->post_type === 'page' && get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' ) {
		$empty = jcp_page_legacy_to_blocks(
			[
				'page_kind'  => 'marketing',
				'page_key'   => $post->post_name,
				'page_label' => get_the_title( $post ),
				'preset'     => 'marketing',
			],
			(int) $post->ID
		);
		$empty['blocks'] = jcp_page_blocks_from_preset( 'marketing' );
		$display         = wp_json_encode( $empty, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	?>
	<p class="description">
		<?php
		if ( $post->post_type === 'jcp_niche_landing' ) {
			esc_html_e( 'Page content as JSON blocks. Load a trade template to start. Published pages appear on /industries/ automatically.', 'jcp-core' );
		} elseif ( $post->post_type === 'jcp_page' ) {
			esc_html_e( 'Page content as JSON blocks. Load a preset or import a writer document. Published at /pages/{slug}/.', 'jcp-core' );
		} elseif ( get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' ) {
			esc_html_e( 'Page content as JSON blocks. Load a preset or import a writer document. Published at this page’s existing URL — SEO in Rank Math is unchanged.', 'jcp-core' );
		} else {
			esc_html_e( 'Structured page content. Edit JSON directly or use a preset loader below.', 'jcp-core' );
		}
		?>
	</p>
	<?php if ( $post->post_type === 'jcp_niche_landing' ) : ?>
	<p>
		<button type="button" class="button" id="jcp-niche-load-plumbing-demo"><?php esc_html_e( 'Use plumbing as template', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-hvac-demo"><?php esc_html_e( 'Use HVAC as template', 'jcp-core' ); ?></button>
	</p>
	<?php elseif ( $post->post_type === 'jcp_page' || get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' ) : ?>
	<p>
		<button type="button" class="button" id="jcp-niche-load-marketing-demo"><?php esc_html_e( 'Use marketing preset', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-minimal-demo"><?php esc_html_e( 'Use minimal preset', 'jcp-core' ); ?></button>
	</p>
	<?php else : ?>
	<p>
		<button type="button" class="button" id="jcp-niche-load-referral-demo"><?php esc_html_e( 'Load referral program JSON', 'jcp-core' ); ?></button>
	</p>
	<?php endif; ?>
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
		bindPreset('jcp-niche-load-marketing-demo', 'jcp_page_marketing_json');
		bindPreset('jcp-niche-load-minimal-demo', 'jcp_page_minimal_json');
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
	$legacy = jcp_page_load_preset( $preset );
	if ( empty( $legacy ) ) {
		$legacy = [
			'page_kind' => $preset === 'referral-program' ? 'referral' : ( $preset === 'home' ? 'home' : ( in_array( $preset, [ 'plumbing', 'hvac' ], true ) ? 'industry' : 'marketing' ) ),
			'preset'    => $preset,
		];
		$data = array_merge( jcp_page_legacy_to_blocks( $legacy, 0 ), [ 'blocks' => jcp_page_blocks_from_preset( $preset ) ] );
	} else {
		$data = jcp_page_legacy_to_blocks( $legacy, 0 );
	}
	wp_send_json_success(
		[
			'content' => wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
		]
	);
}

/**
 * AJAX: marketing / minimal empty presets.
 */
function jcp_page_ajax_marketing_json(): void {
	jcp_niche_ajax_preset_json( 'marketing' );
}
add_action( 'wp_ajax_jcp_page_marketing_json', 'jcp_page_ajax_marketing_json' );

function jcp_page_ajax_minimal_json(): void {
	jcp_niche_ajax_preset_json( 'minimal' );
}
add_action( 'wp_ajax_jcp_page_minimal_json', 'jcp_page_ajax_minimal_json' );

/**
 * Save meta box.
 *
 * @param int $post_id Post ID.
 */
function jcp_niche_save_meta_box( int $post_id ): void {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$is_structured = in_array( $post->post_type, [ 'jcp_niche_landing', 'jcp_page' ], true )
		|| jcp_page_uses_block_template( $post_id );
	if ( ! $is_structured ) {
		return;
	}
	if ( ! isset( $_POST['jcp_niche_content_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jcp_niche_content_nonce'] ) ), 'jcp_niche_content_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$content = jcp_page_get_content_flat( $post_id );
	if ( isset( $_POST['jcp_niche_quick'] ) && is_array( $_POST['jcp_niche_quick'] ) ) {
		$q = wp_unslash( $_POST['jcp_niche_quick'] );
		$content['hero']      = $content['hero'] ?? [];
		$content['final_cta'] = $content['final_cta'] ?? [];
		$content['final_cta']['cta_primary'] = $content['final_cta']['cta_primary'] ?? [];
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
		jcp_page_save_content( $post_id, $content );
	}

	if ( ! isset( $_POST['jcp_niche_content_json'] ) ) {
		return;
	}
	$json = wp_unslash( $_POST['jcp_niche_content_json'] );
	$json = is_string( $json ) ? trim( $json ) : '';
	if ( $json === '' ) {
		delete_post_meta( $post_id, jcp_page_content_meta_key() );
		delete_post_meta( $post_id, jcp_page_legacy_meta_key() );
		return;
	}
	$decoded = json_decode( $json, true );
	if ( ! is_array( $decoded ) ) {
		return;
	}
	$post = get_post( $post_id );
	if ( $post instanceof WP_Post ) {
		if ( empty( $decoded['page_key'] ) && empty( $decoded['niche_key'] ) ) {
			$decoded['page_key'] = $post->post_name;
			$decoded['niche_key'] = $post->post_name;
		}
		if ( empty( $decoded['page_label'] ) && empty( $decoded['niche_label'] ) ) {
			$decoded['page_label'] = get_the_title( $post_id );
			$decoded['niche_label'] = get_the_title( $post_id );
		}
		if ( empty( $decoded['page_kind'] ) && $post->post_type === 'jcp_niche_landing' ) {
			$decoded['page_kind'] = 'industry';
		}
		if ( empty( $decoded['page_kind'] ) && $post->post_type === 'jcp_page' ) {
			$decoded['page_kind'] = 'marketing';
		}
		if ( empty( $decoded['page_kind'] ) && get_page_template_slug( $post_id ) === 'page-referral-program.php' ) {
			$decoded['page_kind'] = 'referral';
		}
		if ( empty( $decoded['page_kind'] ) && get_page_template_slug( $post_id ) === 'page-home.php' ) {
			$decoded['page_kind'] = 'home';
		}
		if ( empty( $decoded['page_kind'] ) && (int) get_option( 'page_on_front' ) === $post_id ) {
			$decoded['page_kind'] = 'home';
		}
		if ( empty( $decoded['page_kind'] ) && get_page_template_slug( $post_id ) === 'page-jcp-blocks.php' ) {
			$decoded['page_kind'] = 'marketing';
		}
	}
	jcp_page_save_content( $post_id, $decoded );
}
add_action( 'save_post_jcp_niche_landing', 'jcp_niche_save_meta_box' );
add_action( 'save_post_jcp_page', 'jcp_niche_save_meta_box' );
add_action( 'save_post_page', 'jcp_niche_save_meta_box' );
