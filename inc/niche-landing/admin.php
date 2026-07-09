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
	return __( 'Block editor: Settings → Template → JCP Block Page. Classic editor: Page Attributes → JCP Block Page. Then click Update.', 'jcp-core' );
}

/**
 * Whether this post uses the JCP block page editor in admin.
 *
 * @param WP_Post $post Post.
 */
function jcp_admin_page_uses_editor( WP_Post $post ): bool {
	if ( $post->post_type === 'jcp_niche_landing' ) {
		return true;
	}
	return $post->post_type === 'page' && jcp_page_uses_block_template( (int) $post->ID );
}

/**
 * Register meta box.
 */
function jcp_niche_register_meta_box(): void {
	$types = [ 'jcp_niche_landing', 'page' ];
	foreach ( $types as $post_type ) {
		add_meta_box(
			'jcp_page_editor',
			__( 'JCP Page Editor', 'jcp-core' ),
			'jcp_niche_render_unified_editor_meta_box',
			$post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'jcp_page_advanced',
			__( 'Developer: page JSON', 'jcp-core' ),
			'jcp_niche_render_meta_box',
			$post_type,
			'normal',
			'low'
		);
	}
}

/**
 * Unified editor: setup guide on standard pages, full tools on block pages.
 *
 * @param WP_Post $post Post.
 */
function jcp_niche_render_unified_editor_meta_box( WP_Post $post ): void {
	if ( ! jcp_admin_page_uses_editor( $post ) ) {
		jcp_niche_render_standard_page_setup_meta_box( $post );
		return;
	}

	$stored      = jcp_page_get_content( (int) $post->ID );
	$preset      = jcp_writer_resolve_preset( $post, $stored );
	$preset_label = jcp_writer_preset_label( $preset );
	$is_block_page = $post->post_type === 'page' && get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php';
	$is_industry   = $post->post_type === 'jcp_niche_landing';
	$sop_url       = admin_url( 'admin.php?page=jcp-theme-settings#document-import' );
	$bulk_url      = admin_url( 'edit.php?post_type=jcp_niche_landing&page=jcp-bulk-industries' );
	?>
	<div class="jcp-admin-page-editor">
		<div class="jcp-writer-workflow">
			<h3 class="jcp-writer-workflow__title"><?php esc_html_e( 'Writer workflow', 'jcp-core' ); ?></h3>
			<ol class="jcp-writer-workflow__steps">
				<li><?php esc_html_e( 'Copy the writer template (below) into Google Docs and fill in your content.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Set the page title and URL slug, then paste or upload your doc and click Build page.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click Update / Publish, then polish photos and text on the live page editor.', 'jcp-core' ); ?></li>
			</ol>
			<p class="description">
				<?php
				printf(
					/* translators: 1: layout label, 2: SOP link */
					esc_html__( 'Layout: %1$s. SEO fields are in Rank Math. %2$s', 'jcp-core' ),
					'<strong>' . esc_html( $preset_label ) . '</strong>',
					'<a href="' . esc_url( $sop_url ) . '">' . esc_html__( 'Full writer guide →', 'jcp-core' ) . '</a>'
				);
				if ( $is_industry ) {
					echo ' <a href="' . esc_url( $bulk_url ) . '">' . esc_html__( 'Bulk add trades →', 'jcp-core' ) . '</a>';
				}
				?>
			</p>
		</div>

		<?php if ( $is_block_page ) : ?>
			<?php jcp_niche_render_layout_template_picker( $post, $preset ); ?>
			<hr class="jcp-admin-page-editor__divider" />
		<?php endif; ?>

		<?php jcp_niche_render_import_meta_box_content( $post ); ?>

		<?php
		wp_nonce_field( 'jcp_niche_content_save', 'jcp_niche_content_nonce' );
		$json_display = jcp_page_get_admin_editor_json( $post );
		?>
		<textarea
			name="jcp_niche_content_json"
			id="jcp_niche_content_json"
			class="jcp-admin-page-editor__json-hidden"
			aria-hidden="true"
			tabindex="-1"
		><?php echo esc_textarea( $json_display ); ?></textarea>

		<hr class="jcp-admin-page-editor__divider" />

		<details class="jcp-admin-page-editor__details jcp-admin-page-editor__details--advanced">
			<summary><?php esc_html_e( 'Advanced: quick fields, live edit link, section list', 'jcp-core' ); ?></summary>
			<div class="jcp-admin-page-editor__advanced">
				<?php jcp_niche_render_quick_meta_box_content( $post ); ?>
				<hr class="jcp-admin-page-editor__divider" />
				<h3 class="jcp-admin-page-editor__heading"><?php esc_html_e( 'Page sections', 'jcp-core' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Drag to reorder, add, or remove blocks. Saves with the page.', 'jcp-core' ); ?></p>
				<?php jcp_page_block_structure_render_panel( $post ); ?>
			</div>
		</details>
	</div>
	<?php
}

/**
 * Layout template picker for JCP Block Page posts.
 *
 * @param WP_Post $post   Post.
 * @param string  $preset Active preset slug.
 */
function jcp_niche_render_layout_template_picker( WP_Post $post, string $preset ): void {
	$choices = jcp_writer_selectable_layout_presets();
	if ( ! $choices ) {
		return;
	}
	?>
	<div class="jcp-layout-picker">
		<h3 class="jcp-admin-page-editor__heading"><?php esc_html_e( 'Page layout template', 'jcp-core' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Choose the section stack for this page. Changing layout updates the writer template and AI prompt. Click Apply layout template to reset sections, or add/reorder sections freely in the live editor.', 'jcp-core' ); ?></p>
		<p>
			<label for="jcp_page_layout_preset"><strong><?php esc_html_e( 'Layout', 'jcp-core' ); ?></strong></label>
			<select name="jcp_page_layout_preset" id="jcp_page_layout_preset">
				<?php foreach ( $choices as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $preset, $slug ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button" id="jcp-apply-layout-preset"><?php esc_html_e( 'Apply layout template', 'jcp-core' ); ?></button>
			<span class="description" id="jcp-apply-layout-status"></span>
		</p>
	</div>
	<script>
	(function () {
		var btn = document.getElementById('jcp-apply-layout-preset');
		var select = document.getElementById('jcp_page_layout_preset');
		var status = document.getElementById('jcp-apply-layout-status');
		var ta = document.getElementById('jcp_niche_content_json');
		if (!btn || !select || !ta || typeof ajaxurl === 'undefined') return;
		btn.addEventListener('click', function () {
			if (!confirm('<?php echo esc_js( __( 'Replace the section list with this layout template? Unsaved import text is kept in the paste box above.', 'jcp-core' ) ); ?>')) return;
			var preset = select.value;
			status.textContent = '<?php echo esc_js( __( 'Applying…', 'jcp-core' ) ); ?>';
			btn.disabled = true;
			var body = new FormData();
			body.append('action', 'jcp_page_apply_layout_preset');
			body.append('_wpnonce', '<?php echo esc_js( wp_create_nonce( 'jcp_page_apply_layout_preset' ) ); ?>');
			body.append('preset', preset);
			body.append('post_id', '<?php echo (int) $post->ID; ?>');
			fetch(ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					btn.disabled = false;
					if (!data || !data.success || !data.data || !data.data.content) {
						status.textContent = '<?php echo esc_js( __( 'Could not apply layout.', 'jcp-core' ) ); ?>';
						return;
					}
					ta.value = data.data.content;
					ta.dispatchEvent(new Event('input', { bubbles: true }));
					var devTa = document.getElementById('jcp_niche_content_json_dev');
					if (devTa) devTa.value = data.data.content;
					var templateEl = document.getElementById('jcp-writer-template-json');
					if (templateEl && data.data.writer_template) {
						templateEl.textContent = JSON.stringify(data.data.writer_template);
					}
					var aiPromptEl = document.getElementById('jcp-writer-ai-prompt-json');
					if (aiPromptEl && data.data.ai_prompt) {
						aiPromptEl.textContent = JSON.stringify(data.data.ai_prompt);
					}
					var sectionsList = document.querySelector('.jcp-doc-import__sections');
					if (sectionsList && data.data.sections_html) {
						sectionsList.innerHTML = data.data.sections_html;
					}
					var workflowLabel = document.querySelector('.jcp-writer-workflow .description strong');
					if (workflowLabel && data.data.preset_label) {
						workflowLabel.textContent = data.data.preset_label;
					}
					status.textContent = '<?php echo esc_js( __( 'Layout applied — click Update to save.', 'jcp-core' ) ); ?>';
				})
				.catch(function () {
					btn.disabled = false;
					status.textContent = '<?php echo esc_js( __( 'Could not apply layout.', 'jcp-core' ) ); ?>';
				});
		});
	})();
	</script>
	<?php
}

/**
 * Friendly guide when a WP Page still uses the default template.
 *
 * @param WP_Post $post Post.
 */
function jcp_niche_render_standard_page_setup_meta_box( WP_Post $post ): void {
	$sop_url = admin_url( 'admin.php?page=jcp-theme-settings' );
	?>
	<div class="jcp-admin-page-setup">
		<p><strong><?php esc_html_e( 'This is a standard WordPress page.', 'jcp-core' ); ?></strong>
			<?php esc_html_e( 'The title and content editor above are what visitors see.', 'jcp-core' ); ?></p>

		<p><strong><?php esc_html_e( 'Want the block builder instead?', 'jcp-core' ); ?></strong></p>
		<ol class="jcp-admin-page-setup__steps">
			<li><?php esc_html_e( 'Block editor: Settings (gear, top-right) → Template. Classic editor: right sidebar → Page Attributes → Template.', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Choose JCP Block Page', 'jcp-core' ); ?></li>
			<li><?php esc_html_e( 'Click Update — the full JCP Page Editor panel replaces this guide', 'jcp-core' ); ?></li>
		</ol>

		<p class="description">
			<?php esc_html_e( 'Home and Referral Program templates are only for those specific pages. For everything else you build from blocks, use JCP Block Page. New pages usually only show Default + JCP Block Page in the template list.', 'jcp-core' ); ?>
		</p>

		<p class="description">
			<?php esc_html_e( 'Bottom CTA below: optional signup strip for simple pages. Block pages use section CTAs instead.', 'jcp-core' ); ?>
		</p>

		<p>
			<a href="<?php echo esc_url( $sop_url ); ?>" class="button button-primary"><?php esc_html_e( 'Which menu should I use?', 'jcp-core' ); ?></a>
			<?php if ( $post->ID > 0 ) : ?>
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="button" target="_blank" rel="noopener"><?php esc_html_e( 'View live page', 'jcp-core' ); ?></a>
			<?php endif; ?>
		</p>
	</div>
	<?php
}

/**
 * Hide developer JSON + SEO panels when they do not apply.
 */
function jcp_admin_cleanup_page_meta_boxes(): void {
	global $post;
	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return;
	}
	if ( ! jcp_admin_page_uses_editor( $post ) ) {
		remove_meta_box( 'jcp_page_advanced', 'page', 'normal' );
	}
}
add_action( 'add_meta_boxes', 'jcp_admin_cleanup_page_meta_boxes', 100 );

/**
 * Admin styles for consolidated page editor UI.
 *
 * @param string $hook Hook.
 */
function jcp_admin_page_editor_styles( string $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, [ 'jcp_niche_landing', 'page' ], true ) ) {
		return;
	}
	wp_enqueue_style(
		'jcp-admin-page-editor',
		get_template_directory_uri() . '/assets/css/admin/page-editor.css',
		[],
		function_exists( 'jcp_core_asset_version' ) ? jcp_core_asset_version( 'assets/css/admin/page-editor.css' ) : null
	);
}
add_action( 'admin_enqueue_scripts', 'jcp_admin_page_editor_styles' );

/**
 * Quick-edit fields (merged into JSON on save).
 *
 * @param WP_Post $post Post.
 */
function jcp_niche_render_quick_meta_box( WP_Post $post ): void {
	jcp_niche_render_quick_meta_box_content( $post );
}

/**
 * @param WP_Post $post Post.
 */
function jcp_niche_render_quick_meta_box_content( WP_Post $post ): void {
	$c     = jcp_page_get_content_flat( (int) $post->ID );
	$edit  = add_query_arg( 'jcp_edit', '1', get_permalink( $post ) );
	$hero  = $c['hero'] ?? [];
	$final = $c['final_cta'] ?? [];
	$is_industry  = $post->post_type === 'jcp_niche_landing';
	$is_marketing = $post->post_type === 'page' && get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php';
	?>
	<?php if ( $is_industry || $is_marketing ) : ?>
		<div class="notice notice-info inline jcp-admin-page-editor__notice" style="display:none;">
			<p style="margin: 0;">
				<strong><?php echo $is_industry ? esc_html__( 'Add a new trade page', 'jcp-core' ) : esc_html__( 'Build a block page', 'jcp-core' ); ?></strong>
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
		<?php
		$nav_cta = $c['nav_cta'] ?? [];
		?>
		<tr>
			<th colspan="2"><strong><?php esc_html_e( 'Nav bar CTA override (optional)', 'jcp-core' ); ?></strong>
				<p class="description" style="font-weight:normal;margin:4px 0 0;"><?php esc_html_e( 'Leave blank to use JCP → Global Settings defaults.', 'jcp-core' ); ?></p>
			</th>
		</tr>
		<tr>
			<th><label for="jcp_nav_primary_label"><?php esc_html_e( 'Primary nav label', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="regular-text" id="jcp_nav_primary_label" name="jcp_niche_quick[nav_primary_label]" value="<?php echo esc_attr( $nav_cta['primary_label'] ?? '' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="jcp_nav_primary_url"><?php esc_html_e( 'Primary nav URL', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="large-text" id="jcp_nav_primary_url" name="jcp_niche_quick[nav_primary_url]" value="<?php echo esc_attr( $nav_cta['primary_url'] ?? '' ); ?>" placeholder="/demo or https://…" /></td>
		</tr>
		<tr>
			<th><label for="jcp_nav_secondary_label"><?php esc_html_e( 'Secondary nav label', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="regular-text" id="jcp_nav_secondary_label" name="jcp_niche_quick[nav_secondary_label]" value="<?php echo esc_attr( $nav_cta['secondary_label'] ?? '' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="jcp_nav_secondary_url"><?php esc_html_e( 'Secondary nav URL', 'jcp-core' ); ?></label></th>
			<td><input type="text" class="large-text" id="jcp_nav_secondary_url" name="jcp_niche_quick[nav_secondary_url]" value="<?php echo esc_attr( $nav_cta['secondary_url'] ?? '' ); ?>" placeholder="/demo" /></td>
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
	jcp_niche_render_import_meta_box_content( $post );
}

/**
 * @param WP_Post $post Post.
 */
function jcp_niche_render_import_meta_box_content( WP_Post $post ): void {
	wp_nonce_field( 'jcp_niche_import_doc', 'jcp_niche_import_nonce' );
	$stored      = jcp_page_get_content( (int) $post->ID );
	$preset      = jcp_writer_resolve_preset( $post, $stored );
	$page_kind   = jcp_page_resolve_admin_page_kind( $post, $stored );
	$kind_label  = jcp_writer_preset_label( $preset );
	$sections    = jcp_page_doc_sections_for_preset( $preset );
	$template    = function_exists( 'jcp_writer_get_document_template' ) ? jcp_writer_get_document_template( $preset ) : '';
	$ai_prompt   = function_exists( 'jcp_writer_get_ai_prompt' ) ? jcp_writer_get_ai_prompt( $preset, $post ) : '';
	$sop_url     = admin_url( 'admin.php?page=jcp-theme-settings#document-import' );
	$ai_sop_url  = admin_url( 'admin.php?page=jcp-theme-settings#ai-writing' );
	?>
	<div class="jcp-doc-import">
		<h3 class="jcp-admin-page-editor__heading"><?php esc_html_e( 'Import from writer document', 'jcp-core' ); ?></h3>
		<p class="description">
			<?php
			printf(
				/* translators: 1: page type label, 2: SOP link, 3: AI SOP link */
				esc_html__( 'Paste or upload a writer document for this %1$s. Section headers must be ALL CAPS (HERO, WHAT IT IS, …). %2$s · %3$s', 'jcp-core' ),
				'<strong>' . esc_html( $kind_label ) . '</strong>',
				'<a href="' . esc_url( $sop_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Writer guide →', 'jcp-core' ) . '</a>',
				'<a href="' . esc_url( $ai_sop_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'AI prompt guide →', 'jcp-core' ) . '</a>'
			);
			?>
		</p>

		<?php if ( $ai_prompt !== '' ) : ?>
		<div class="jcp-writer-ai-workflow">
			<p class="jcp-writer-ai-workflow__title"><strong><?php esc_html_e( 'Drafting with ChatGPT or Claude?', 'jcp-core' ); ?></strong></p>
			<ol class="jcp-writer-ai-workflow__steps">
				<li><?php esc_html_e( 'Copy the AI prompt (includes editorial rules + this page’s template).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Fill in trade, state, and keyword placeholders, then paste into your AI tool.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Edit the draft for natural flow — AI is a starting point, not the final copy.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Paste the finished document below and click Build page from document.', 'jcp-core' ); ?></li>
			</ol>
			<p class="jcp-writer-ai-workflow__actions">
				<button type="button" class="button button-primary" id="jcp-copy-ai-prompt-inline"><?php esc_html_e( 'Copy AI prompt', 'jcp-core' ); ?></button>
				<?php if ( $template !== '' ) : ?>
					<button type="button" class="button" id="jcp-copy-writer-template-inline"><?php esc_html_e( 'Copy template only', 'jcp-core' ); ?></button>
					<span class="description" id="jcp-copy-template-inline-status" style="margin-left:8px;"></span>
				<?php endif; ?>
				<span class="description" id="jcp-copy-ai-prompt-inline-status" style="margin-left:8px;"></span>
			</p>
		</div>
		<?php endif; ?>

		<details class="jcp-doc-import__guide" open>
			<summary><?php esc_html_e( 'Section headers for this page type', 'jcp-core' ); ?></summary>
			<p class="description"><?php esc_html_e( 'Use these ALL CAPS lines in your doc. Sections marked “optional on this page” are not in this layout’s default stack — you can still add them; they import and appear in the page editor.', 'jcp-core' ); ?></p>
			<ul class="jcp-doc-import__sections">
				<?php foreach ( $sections as $row ) : ?>
					<li class="<?php echo ! empty( $row['on_page'] ) ? 'is-on-page' : 'is-extra'; ?>">
						<code><?php echo esc_html( $row['header'] ); ?></code>
						<span><?php echo esc_html( $row['label'] ); ?></span>
						<?php if ( empty( $row['on_page'] ) ) : ?>
							<em><?php esc_html_e( 'optional on this page', 'jcp-core' ); ?></em>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $template !== '' ) : ?>
				<p class="description jcp-doc-import__template-note"><?php esc_html_e( 'Template includes length targets, list counts, and formatting rules at the top for AI tools.', 'jcp-core' ); ?></p>
			<?php endif; ?>
		</details>

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
		</p>
		<div id="jcp-niche-import-status" class="jcp-doc-import__status" aria-live="polite"></div>
	</div>
	<?php if ( $template !== '' ) : ?>
	<script type="application/json" id="jcp-writer-template-json"><?php echo wp_json_encode( $template ); ?></script>
	<?php endif; ?>
	<?php if ( $ai_prompt !== '' ) : ?>
	<script type="application/json" id="jcp-writer-ai-prompt-json"><?php echo wp_json_encode( $ai_prompt ); ?></script>
	<?php endif; ?>
	<script>
	(function () {
		var btn = document.getElementById('jcp-niche-build-from-doc');
		var ta = document.getElementById('jcp_niche_import_doc');
		var status = document.getElementById('jcp-niche-import-status');
		var fileInput = document.getElementById('jcp_niche_import_file');
		var copyBtn = document.getElementById('jcp-copy-writer-template-inline');
		var copyStatus = document.getElementById('jcp-copy-template-inline-status');
		var templateEl = document.getElementById('jcp-writer-template-json');
		var aiPromptBtn = document.getElementById('jcp-copy-ai-prompt-inline');
		var aiPromptStatus = document.getElementById('jcp-copy-ai-prompt-inline-status');
		var aiPromptEl = document.getElementById('jcp-writer-ai-prompt-json');

		function copyJsonText(el, statusEl, okMsg) {
			if (!el) return;
			var text = '';
			try { text = JSON.parse(el.textContent || '""'); } catch (e) { return; }
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function () {
					if (statusEl) statusEl.textContent = okMsg;
				});
				return;
			}
			var scratch = document.createElement('textarea');
			scratch.value = text;
			document.body.appendChild(scratch);
			scratch.select();
			document.execCommand('copy');
			document.body.removeChild(scratch);
			if (statusEl) statusEl.textContent = okMsg;
		}

		if (copyBtn && templateEl) {
			copyBtn.addEventListener('click', function () {
				copyJsonText(templateEl, copyStatus, '<?php echo esc_js( __( 'Template copied!', 'jcp-core' ) ); ?>');
			});
		}

		if (aiPromptBtn && aiPromptEl) {
			aiPromptBtn.addEventListener('click', function () {
				copyJsonText(aiPromptEl, aiPromptStatus, '<?php echo esc_js( __( 'AI prompt copied!', 'jcp-core' ) ); ?>');
			});
		}

		function renderReport(report) {
			if (!status || !report) return;
			var html = '<p class="jcp-doc-import__summary"><strong>' + (report.message || '') + '</strong></p>';
			html += '<p class="description"><strong><?php echo esc_js( __( 'Next step:', 'jcp-core' ) ); ?></strong> <?php echo esc_js( __( 'Click Update or Publish at the top of this screen to save.', 'jcp-core' ) ); ?></p>';
			if (report.imported && report.imported.length) {
				html += '<p><strong><?php echo esc_js( __( 'Imported:', 'jcp-core' ) ); ?></strong> ';
				html += report.imported.map(function (row) { return row.label; }).join(', ');
				html += '</p>';
			}
			if (report.skipped && report.skipped.length) {
				html += '<p class="jcp-doc-import__skipped"><strong><?php echo esc_js( __( 'Not on this page type:', 'jcp-core' ) ); ?></strong> ';
				html += report.skipped.map(function (row) { return row.header + ' (' + row.label + ')'; }).join(', ');
				html += '</p>';
			}
			status.innerHTML = html;
		}

		if (!btn || !ta) return;

		btn.addEventListener('click', function () {
			var jsonTa = document.getElementById('jcp_niche_content_json');
			if (!jsonTa) {
				if (status) {
					status.innerHTML = '<p class="jcp-doc-import__error"><?php echo esc_js( __( 'Page data field missing — refresh and try again.', 'jcp-core' ) ); ?></p>';
				}
				return;
			}
			if (typeof ajaxurl === 'undefined') {
				if (status) status.innerHTML = '<p class="jcp-doc-import__error"><?php echo esc_js( __( 'Admin scripts not loaded. Refresh the page and try again.', 'jcp-core' ) ); ?></p>';
				return;
			}
			var hasFile = fileInput && fileInput.files && fileInput.files[0];
			if (!ta.value.trim() && !hasFile) {
				if (status) {
					status.innerHTML = '<p class="jcp-doc-import__error"><?php echo esc_js( __( 'Paste document text or choose a .docx / .txt file to upload.', 'jcp-core' ) ); ?></p>';
				}
				return;
			}
			var body = new FormData();
			body.append('action', 'jcp_niche_parse_document');
			body.append('_wpnonce', '<?php echo esc_js( wp_create_nonce( 'jcp_niche_parse_document' ) ); ?>');
			body.append('post_id', '<?php echo (int) $post->ID; ?>');
			body.append('doc_text', ta.value || '');
			if (fileInput && fileInput.files && fileInput.files[0]) {
				body.append('doc_file', fileInput.files[0]);
			}
			status.innerHTML = '<p><?php echo esc_js( __( 'Building…', 'jcp-core' ) ); ?></p>';
			btn.disabled = true;
			fetch(ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					btn.disabled = false;
					if (!data || !data.success) {
						status.innerHTML = '<p class="jcp-doc-import__error">' + ((data && data.data && data.data.message) ? data.data.message : '<?php echo esc_js( __( 'Import failed.', 'jcp-core' ) ); ?>') + '</p>';
						return;
					}
					jsonTa.value = data.data.content;
					jsonTa.dispatchEvent(new Event('input', { bubbles: true }));
					var devTa = document.getElementById('jcp_niche_content_json_dev');
					if (devTa) devTa.value = data.data.content;
					renderReport(data.data.report);
					if (status) {
						status.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
				})
				.catch(function () {
					btn.disabled = false;
					status.innerHTML = '<p class="jcp-doc-import__error"><?php echo esc_js( __( 'Import failed.', 'jcp-core' ) ); ?></p>';
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
			if ( ! class_exists( 'ZipArchive' ) ) {
				wp_send_json_error( [ 'message' => __( 'This server cannot read .docx files (ZipArchive missing). Paste the document text instead.', 'jcp-core' ) ] );
			}
			$text = jcp_niche_extract_docx_text( (string) $file['tmp_name'] );
			if ( $text === '' ) {
				wp_send_json_error( [ 'message' => __( 'Could not read that .docx file. Try File → Download → Plain text (.txt), or paste the document below.', 'jcp-core' ) ] );
			}
		} elseif ( $ext === 'txt' ) {
			$raw = file_get_contents( $file['tmp_name'] );
			$text = is_string( $raw ) ? jcp_niche_normalize_document_text( $raw ) : '';
		} else {
			wp_send_json_error( [ 'message' => __( 'Upload a .docx or .txt file, or paste document text.', 'jcp-core' ) ] );
		}
	}

	if ( $text === '' ) {
		wp_send_json_error( [ 'message' => __( 'Paste document text or upload a .docx / .txt file.', 'jcp-core' ) ] );
	}

	$niche_key   = $post instanceof WP_Post ? $post->post_name : '';
	$niche_label = $post instanceof WP_Post ? get_the_title( $post ) : '';
	$existing    = $post_id > 0 ? jcp_page_get_content( $post_id ) : [];
	if ( empty( $existing['blocks'] ) && $post instanceof WP_Post ) {
		$skeleton_json = jcp_page_get_admin_editor_json( $post );
		$skeleton      = json_decode( $skeleton_json, true );
		if ( is_array( $skeleton ) && ! empty( $skeleton['blocks'] ) ) {
			$existing = $skeleton;
		}
	}
	$preset      = jcp_writer_resolve_preset( $post, $existing );
	$page_kind   = jcp_page_resolve_admin_page_kind( $post, $existing );
	$parsed      = jcp_page_parse_document_with_report( $text, $niche_key, $niche_label, $page_kind, $preset );
	$content     = jcp_page_merge_import_content( $parsed['content'], $existing );
	$report      = jcp_page_doc_build_import_report(
		jcp_page_blocks_to_legacy( $parsed['content'] ),
		$content,
		$page_kind,
		$preset
	);

	wp_send_json_success(
		[
			'content' => wp_json_encode( $content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			'report'  => $report,
		]
	);
}
add_action( 'wp_ajax_jcp_niche_parse_document', 'jcp_niche_ajax_parse_document' );
add_action( 'wp_ajax_jcp_page_parse_document', 'jcp_niche_ajax_parse_document' );

/**
 * @param WP_Post $post Post.
 */
function jcp_niche_render_meta_box( WP_Post $post ): void {
	$display = jcp_page_get_admin_editor_json( $post );
	?>
	<p class="description">
		<?php esc_html_e( 'Raw page data for developers. Most edits should use JCP Page Editor above or the live page editor — only edit JSON if you know the block schema.', 'jcp-core' ); ?>
	</p>
	<?php if ( $post->post_type === 'jcp_niche_landing' ) : ?>
	<p>
		<button type="button" class="button" id="jcp-niche-load-plumbing-demo"><?php esc_html_e( 'Use plumbing as template', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-hvac-demo"><?php esc_html_e( 'Use HVAC as template', 'jcp-core' ); ?></button>
	</p>
	<?php elseif ( get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' ) : ?>
	<p>
		<button type="button" class="button" id="jcp-niche-load-industry-demo"><?php esc_html_e( 'Use industry trade preset', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-marketing-demo"><?php esc_html_e( 'Use block page preset', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-features-demo"><?php esc_html_e( 'Use features preset', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-comparison-demo"><?php esc_html_e( 'Use comparison preset', 'jcp-core' ); ?></button>
		<button type="button" class="button" id="jcp-niche-load-minimal-demo"><?php esc_html_e( 'Use minimal preset', 'jcp-core' ); ?></button>
	</p>
	<?php else : ?>
	<p>
		<button type="button" class="button" id="jcp-niche-load-referral-demo"><?php esc_html_e( 'Load referral program JSON', 'jcp-core' ); ?></button>
	</p>
	<?php endif; ?>
	<textarea id="jcp_niche_content_json_dev" rows="24" class="large-text code" style="width:100%;font-family:monospace;"><?php echo esc_textarea( $display ); ?></textarea>
	<script>
	(function () {
		var primary = document.getElementById('jcp_niche_content_json');
		var devTa = document.getElementById('jcp_niche_content_json_dev');
		if (primary && devTa) {
			var syncToDev = function () { devTa.value = primary.value; };
			var syncToPrimary = function () {
				primary.value = devTa.value;
				primary.dispatchEvent(new Event('input', { bubbles: true }));
			};
			primary.addEventListener('input', syncToDev);
			devTa.addEventListener('input', syncToPrimary);
			syncToDev();
		}

		function bindPreset(btnId, action) {
			var btn = document.getElementById(btnId);
			var ta = document.getElementById('jcp_niche_content_json') || document.getElementById('jcp_niche_content_json_dev');
			if (!btn || !ta) return;
			btn.addEventListener('click', function () {
				if (!confirm('Replace editor content with the selected preset?')) return;
				fetch(ajaxurl + '?action=' + action + '&_wpnonce=<?php echo esc_js( wp_create_nonce( 'jcp_niche_preset_json' ) ); ?>')
					.then(function (r) { return r.json(); })
					.then(function (data) {
						if (data && data.success && data.data && data.data.content) {
							ta.value = data.data.content;
							ta.dispatchEvent(new Event('input', { bubbles: true }));
							var other = ta.id === 'jcp_niche_content_json' ? devTa : primary;
							if (other) other.value = data.data.content;
						}
					});
			});
		}
		bindPreset('jcp-niche-load-plumbing-demo', 'jcp_niche_plumbing_json');
		bindPreset('jcp-niche-load-hvac-demo', 'jcp_niche_hvac_json');
		bindPreset('jcp-niche-load-referral-demo', 'jcp_niche_referral_json');
		bindPreset('jcp-niche-load-industry-demo', 'jcp_page_industry_json');
		bindPreset('jcp-niche-load-marketing-demo', 'jcp_page_marketing_json');
		bindPreset('jcp-niche-load-features-demo', 'jcp_page_features_json');
		bindPreset('jcp-niche-load-comparison-demo', 'jcp_page_comparison_json');
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
		$def = jcp_page_get_preset( $preset );
		$legacy = [
			'page_kind' => $def['page_kind'] ?? 'marketing',
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
function jcp_page_ajax_industry_json(): void {
	jcp_niche_ajax_preset_json( 'industry' );
}
add_action( 'wp_ajax_jcp_page_industry_json', 'jcp_page_ajax_industry_json' );

function jcp_page_ajax_marketing_json(): void {
	jcp_niche_ajax_preset_json( 'marketing' );
}
add_action( 'wp_ajax_jcp_page_marketing_json', 'jcp_page_ajax_marketing_json' );

function jcp_page_ajax_minimal_json(): void {
	jcp_niche_ajax_preset_json( 'minimal' );
}
add_action( 'wp_ajax_jcp_page_minimal_json', 'jcp_page_ajax_minimal_json' );

function jcp_page_ajax_features_json(): void {
	jcp_niche_ajax_preset_json( 'features' );
}
add_action( 'wp_ajax_jcp_page_features_json', 'jcp_page_ajax_features_json' );

function jcp_page_ajax_comparison_json(): void {
	jcp_niche_ajax_preset_json( 'comparison' );
}
add_action( 'wp_ajax_jcp_page_comparison_json', 'jcp_page_ajax_comparison_json' );

/**
 * AJAX: apply a layout preset skeleton to the current block page.
 */
function jcp_page_ajax_apply_layout_preset(): void {
	check_ajax_referer( 'jcp_page_apply_layout_preset' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error();
	}

	$preset  = isset( $_POST['preset'] ) ? sanitize_key( (string) wp_unslash( $_POST['preset'] ) ) : '';
	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	if ( ! jcp_page_get_preset( $preset ) ) {
		wp_send_json_error( [ 'message' => __( 'Unknown layout.', 'jcp-core' ) ] );
	}

	$post = $post_id > 0 ? get_post( $post_id ) : null;
	if ( ! $post instanceof WP_Post ) {
		$post = new WP_Post(
			(object) [
				'ID'         => 0,
				'post_name'  => '',
				'post_title' => __( 'New page', 'jcp-core' ),
				'post_type'  => 'page',
			]
		);
	}

	$skeleton = jcp_page_create_skeleton_document( $post, $preset );
	wp_send_json_success(
		[
			'content'         => wp_json_encode( $skeleton, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			'preset'          => $preset,
			'preset_label'    => jcp_writer_preset_label( $preset ),
			'writer_template' => jcp_writer_get_document_template( $preset ),
			'ai_prompt'       => jcp_writer_get_ai_prompt( $preset, $post instanceof WP_Post ? $post : null ),
			'sections_html'   => jcp_page_doc_sections_guide_html( $preset ),
		]
	);
}
add_action( 'wp_ajax_jcp_page_apply_layout_preset', 'jcp_page_ajax_apply_layout_preset' );

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
	$is_structured = $post->post_type === 'jcp_niche_landing'
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

	if ( isset( $_POST['jcp_page_layout_preset'] ) && $post->post_type === 'page' && get_page_template_slug( $post_id ) === 'page-jcp-blocks.php' ) {
		$layout_preset = sanitize_key( (string) wp_unslash( $_POST['jcp_page_layout_preset'] ) );
		if ( jcp_page_get_preset( $layout_preset ) ) {
			update_post_meta( $post_id, jcp_writer_layout_preset_meta_key(), $layout_preset );
		}
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
		$content['nav_cta'] = $content['nav_cta'] ?? [];
		foreach (
			[
				'nav_primary_label'   => 'primary_label',
				'nav_primary_url'     => 'primary_url',
				'nav_secondary_label' => 'secondary_label',
				'nav_secondary_url'   => 'secondary_url',
			] as $field => $key
		) {
			if ( ! array_key_exists( $field, $q ) ) {
				continue;
			}
			$val = trim( (string) $q[ $field ] );
			if ( $val === '' ) {
				unset( $content['nav_cta'][ $key ] );
			} else {
				$content['nav_cta'][ $key ] = sanitize_text_field( $val );
			}
		}
		if ( empty( $content['nav_cta'] ) ) {
			unset( $content['nav_cta'] );
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
		if ( empty( $decoded['page_kind'] ) && get_page_template_slug( $post_id ) === 'page-jcp-blocks.php' ) {
			$decoded['page_kind'] = 'marketing';
		}
		if ( empty( $decoded['preset'] ) && get_page_template_slug( $post_id ) === 'page-jcp-blocks.php' ) {
			$decoded['preset'] = jcp_writer_resolve_preset( $post, $decoded );
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
		if ( isset( $_POST['jcp_page_layout_preset'] ) && get_page_template_slug( $post_id ) === 'page-jcp-blocks.php' ) {
			$layout_preset = sanitize_key( (string) wp_unslash( $_POST['jcp_page_layout_preset'] ) );
			if ( jcp_page_get_preset( $layout_preset ) ) {
				$decoded['preset'] = $layout_preset;
			}
		}
	}
	jcp_page_save_content( $post_id, $decoded );
}
add_action( 'save_post_jcp_niche_landing', 'jcp_niche_save_meta_box' );
add_action( 'save_post_page', 'jcp_niche_save_meta_box' );
