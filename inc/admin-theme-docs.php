<?php
/**
 * Theme documentation — Industry Pages SOP (backend admin page under JCP).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Industry Pages documentation under JCP.
 */
function jcp_theme_docs_admin_menu(): void {
	add_submenu_page(
		'jcp-theme-settings',
		__( 'Page System', 'jcp-core' ),
		__( 'Page System', 'jcp-core' ),
		'edit_posts',
		'jcp-theme-settings',
		'jcp_theme_docs_render_page'
	);
}
add_action( 'admin_menu', 'jcp_theme_docs_admin_menu', 9 );

/**
 * Render the Industry Pages SOP.
 */
function jcp_theme_docs_render_page(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$add_new_url    = admin_url( 'post-new.php?post_type=jcp_niche_landing' );
	$hub_url        = home_url( '/industries/' );
	$pages_new_url  = admin_url( 'post-new.php?post_type=page' );
	$posts_new_url  = admin_url( 'post-new.php' );
	$block_lib_url  = admin_url( 'admin.php?page=jcp-block-library' );
	?>
	<div class="wrap jcp-theme-docs">
		<h1><?php esc_html_e( 'JCP Page System — Standard Operating Procedure', 'jcp-core' ); ?></h1>

		<div class="notice notice-info jcp-theme-docs__start-here" style="margin: 16px 0 20px; padding: 16px 20px;">
			<h2 style="margin: 0 0 10px; font-size: 1.15em;"><?php esc_html_e( 'Start here — what are you adding?', 'jcp-core' ); ?></h2>
			<table class="widefat striped" style="background: #fff; margin-top: 12px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'I need to…', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Go to', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Template', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Add a new page (About, Features, etc.)', 'jcp-core' ); ?></td>
						<td><strong><a href="<?php echo esc_url( $pages_new_url ); ?>"><?php esc_html_e( 'Pages → Add New', 'jcp-core' ); ?></a></strong></td>
						<td><strong><?php esc_html_e( 'JCP Block Page', 'jcp-core' ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Add an industry page (Plumbing, HVAC, etc.)', 'jcp-core' ); ?></td>
						<td><strong><a href="<?php echo esc_url( $add_new_url ); ?>"><?php esc_html_e( 'Industries → Add Industry', 'jcp-core' ); ?></a></strong></td>
						<td><?php esc_html_e( '—', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Add a simple text page (legal, policies)', 'jcp-core' ); ?></td>
						<td><a href="<?php echo esc_url( $pages_new_url ); ?>"><?php esc_html_e( 'Pages → Add New', 'jcp-core' ); ?></a></td>
						<td><?php esc_html_e( 'Default template', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Write a blog post', 'jcp-core' ); ?></td>
						<td><a href="<?php echo esc_url( $posts_new_url ); ?>"><?php esc_html_e( 'Posts → Add New', 'jcp-core' ); ?></a></td>
						<td><?php esc_html_e( '—', 'jcp-core' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<p class="description">
			<?php esc_html_e( 'Block pages and industry pages use the same JCP editor — import a writer doc or edit on the live page. Details below.', 'jcp-core' ); ?>
		</p>

		<div class="jcp-theme-docs__actions">
			<a href="<?php echo esc_url( $pages_new_url ); ?>" class="button button-primary"><?php esc_html_e( 'Add Page', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( $add_new_url ); ?>" class="button button-primary"><?php esc_html_e( 'Add Industry', 'jcp-core' ); ?></a>
		</div>

		<nav class="jcp-theme-docs__toc" aria-label="<?php esc_attr_e( 'On this page', 'jcp-core' ); ?>">
			<strong><?php esc_html_e( 'On this page', 'jcp-core' ); ?></strong>
			<ul>
				<li><a href="#overview"><?php esc_html_e( 'Overview', 'jcp-core' ); ?></a></li>
				<li><a href="#component-model"><?php esc_html_e( 'How components stay consistent', 'jcp-core' ); ?></a></li>
				<li><a href="#quick-start"><?php esc_html_e( 'Quick start (WP block page)', 'jcp-core' ); ?></a></li>
				<li><a href="#quick-start-industry"><?php esc_html_e( 'Quick start (industry page)', 'jcp-core' ); ?></a></li>
				<li><a href="#document-import"><?php esc_html_e( 'Document import', 'jcp-core' ); ?></a></li>
				<li><a href="#document-template"><?php esc_html_e( 'Writer document template', 'jcp-core' ); ?></a></li>
				<li><a href="#backend-editor"><?php esc_html_e( 'Backend editor (WP Admin)', 'jcp-core' ); ?></a></li>
				<li><a href="#frontend-editor"><?php esc_html_e( 'Front-end editor (live page)', 'jcp-core' ); ?></a></li>
				<li><a href="#page-structure"><?php esc_html_e( 'Page structure (reorder blocks)', 'jcp-core' ); ?></a></li>
				<li><a href="#block-library"><?php esc_html_e( 'Block Library', 'jcp-core' ); ?></a></li>
				<li><a href="#seo"><?php esc_html_e( 'SEO (Rank Math)', 'jcp-core' ); ?></a></li>
				<li><a href="#hub"><?php esc_html_e( 'Industries hub', 'jcp-core' ); ?></a></li>
				<li><a href="#troubleshooting"><?php esc_html_e( 'Troubleshooting', 'jcp-core' ); ?></a></li>
			</ul>
		</nav>

		<section id="overview" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Overview', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Block pages and industry pages are built from the same section library (Hero, FAQ, Final CTA, etc.). Industry pages publish at /industries/{slug}/ and appear on the hub:', 'jcp-core' ); ?> <a href="<?php echo esc_url( $hub_url ); ?>" target="_blank" rel="noopener">/industries/</a></p>
			<p><?php esc_html_e( 'SEO title and meta description are set in Rank Math on each post — not in the writer document or JCP editor.', 'jcp-core' ); ?></p>
		</section>

		<section id="component-model" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'How components stay consistent', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Every section on the site is a registered block type (Hero, FAQ, Final CTA, etc.). Each block type has:', 'jcp-core' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'One PHP renderer — outputs the same HTML structure everywhere', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Shared CSS in css/sections.css and component sheets (e.g. hero live-demo phone)', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Per-page content stored as JSON props (headlines, bullets, images)', 'jcp-core' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'When developers update a component’s design in PHP or CSS, every page using that block updates automatically — only the text and media inside each instance change per page.', 'jcp-core' ); ?></p>
			<p>
				<?php
				printf(
					/* translators: %s: block library link */
					esc_html__( 'See %s for the full list of block types and which page kinds can use them.', 'jcp-core' ),
					'<a href="' . esc_url( $block_lib_url ) . '">' . esc_html__( 'JCP → Block Library', 'jcp-core' ) . '</a>'
				);
				?>
			</p>
			<p><?php esc_html_e( 'Document import maps writer sections (HERO, FAQ, etc.) directly onto these blocks and fills their props — it does not create one-off HTML.', 'jcp-core' ); ?></p>
		</section>

		<section id="quick-start" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Quick start — new block-built page (About, Features, etc.)', 'jcp-core' ); ?></h2>
			<ol class="jcp-theme-docs__steps">
				<li>
					<strong><?php esc_html_e( 'Create or open the page', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'WP Admin → Pages → Add New (or open an existing page). Set the URL slug you want — changing slug later changes the live URL.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Choose the JCP Block Page template', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Block editor: Settings panel (gear icon, top-right) → Template → JCP Block Page. Classic editor: right sidebar → Page Attributes → Template → JCP Block Page. Then click Update or Publish.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Add content', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Easiest path: JCP Page Editor → expand “Import from writer document”, paste or upload the writer file, click Build page from document, then click Update / Publish again. Or click “Edit on live page” to type copy directly on the published page.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'SEO in Rank Math', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Set focus keyword, SEO title, and meta description in the Rank Math panel on the same screen.', 'jcp-core' ); ?>
				</li>
			</ol>
			<p class="description">
				<?php esc_html_e( 'Starter presets (“Use block page preset”) live in the Developer: page JSON box at the bottom — for developers only. Normal workflow is document import or the live page editor.', 'jcp-core' ); ?>
			</p>
		</section>

		<section id="quick-start-industry" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Quick start — new trade page', 'jcp-core' ); ?></h2>
			<ol class="jcp-theme-docs__steps">
				<li>
					<strong><?php esc_html_e( 'Create the post', 'jcp-core' ); ?></strong><br />
					<?php
					printf(
						/* translators: %s: admin link */
						esc_html__( 'Go to %s. Set the title (e.g. “Roofing”) and URL slug (e.g. roofing). The slug becomes the page URL.', 'jcp-core' ),
						'<a href="' . esc_url( $add_new_url ) . '">' . esc_html__( 'Industries → Add Industry', 'jcp-core' ) . '</a>'
					);
					?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Import your document', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'In the JCP Page Editor box, expand “Import from writer document”. Paste your Google Doc / Word export (or upload .docx / .txt). Click “Build page from document”. Read the import summary — green “Imported” lines succeeded; “Not on this page type” means that section was skipped for this kind of page.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Publish', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Click Update / Publish at the top of the screen (required — import does not save by itself). The page is live at /industries/{slug}/ and listed on the hub.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'SEO in Rank Math', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Add SEO title and meta description in the Rank Math box on the same post.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Polish copy on the live page (optional)', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Use “Edit on live page” in the JCP Page Editor box for front-end tweaks.', 'jcp-core' ); ?>
				</li>
			</ol>
		</section>

		<section id="document-import" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Document import', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Writers deliver a Google Doc or Word file using the section template below. The theme parser reads section headers and field labels, then builds page JSON automatically.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'Supported inputs', 'jcp-core' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Paste plain text into the Import textarea', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Upload .docx (Word / Google Docs download)', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Upload .txt (Google Docs → File → Download → Plain text)', 'jcp-core' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Import workflow', 'jcp-core' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Open the page in WP Admin (Industries, or Pages with JCP Block Page / Home / Referral Program template).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'In the “JCP Page Editor” box, expand “Import from writer document”.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Paste or upload the writer file.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “Build page from document”.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Check the import summary under the button (which sections imported vs skipped).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click Update / Publish at the top of the screen to save.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Optional: reload the admin page if “Page sections” does not reflect the import yet.', 'jcp-core' ); ?></li>
			</ol>

			<div class="notice notice-warning inline jcp-theme-docs__notice">
				<p>
					<strong><?php esc_html_e( 'Important:', 'jcp-core' ); ?></strong>
					<?php esc_html_e( '“Build page from document” prepares content but does not save until you click Update / Publish. Re-building overwrites what you imported — save first if you already published.', 'jcp-core' ); ?>
				</p>
			</div>

			<h3><?php esc_html_e( 'Section headers the parser recognizes', 'jcp-core' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Writers can use CORE MECHANIC or STAT ROW (same section — shown on the site as “Stat row”). Headers below are defined in the block registry and update automatically.', 'jcp-core' ); ?></p>
			<table class="widefat striped jcp-theme-docs__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Section header (ALL CAPS line)', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Becomes', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( jcp_page_doc_section_catalog() as $row ) : ?>
						<tr>
							<td><code><?php echo esc_html( $row['header'] ); ?></code></td>
							<td><?php echo esc_html( $row['label'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Field labels inside sections', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'Put each label on its own line, then the content on the next line(s):', 'jcp-core' ); ?></p>
			<p><code>H1</code> · <code>Subheadline</code> · <code>Headline</code> · <code>CTA</code> · <code>Trust Line</code> · <code>Closing Line</code></p>
			<p><?php esc_html_e( 'For title + body pairs (benefits, problem, check-ins, who it’s for): put the title on one line and the body on the next line with a leading space (indent).', 'jcp-core' ); ?></p>
			<p><?php esc_html_e( 'Primary Keyword in the doc header is saved for hub search filtering only — not for meta tags.', 'jcp-core' ); ?></p>
		</section>

		<section id="document-template" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Writer document template', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Give this skeleton to content writers. They replace placeholder copy but keep section names and field labels exactly as shown.', 'jcp-core' ); ?></p>
			<pre class="jcp-theme-docs__template"><?php echo esc_html( jcp_theme_docs_get_writer_template() ); ?></pre>
			<p>
				<button type="button" class="button" id="jcp-copy-writer-template"><?php esc_html_e( 'Copy template to clipboard', 'jcp-core' ); ?></button>
				<span id="jcp-copy-template-status" class="description" style="margin-left:8px;"></span>
			</p>
		</section>

		<section id="backend-editor" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Backend editor (WP Admin)', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'On Industries posts and block-built Pages (JCP Block Page, Home, Referral Program), you get one main panel — JCP Page Editor — plus optional developer tools.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'JCP Page Editor (main panel)', 'jcp-core' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'Edit on live page', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'opens the published URL in a new tab with the editing toolbar (same as adding ?jcp_edit=1 to the URL while logged in)', 'jcp-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Key fields', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'quick edits for hero H1, final CTA, optional nav button overrides — saves when you Update the post', 'jcp-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Page sections', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'drag to reorder, add, or remove blocks — saves when you Update the post', 'jcp-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Import from writer document', 'jcp-core' ); ?></strong> — <?php esc_html_e( 'collapsed section for .docx / paste import (see Document import above)', 'jcp-core' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'SEO Health (block pages only)', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'The “SEO Health (JCP + Rank Math)” box checks focus keyword, title, meta description, and hero copy. It appears on Industries posts and Pages using JCP Block Page, Home, or Referral Program templates.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'Developer: page JSON', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'Raw data at the bottom of the screen, including optional starter presets. Ignore unless you are a developer — use JCP Page Editor or the live page editor instead.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'Standard Pages (default template)', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'If you have not chosen JCP Block Page, the JCP panel shows a short setup guide only. Use the WordPress title/content editor above. Optional “Bottom CTA” adds a signup strip at the end.', 'jcp-core' ); ?></p>
		</section>

		<section id="frontend-editor" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Front-end editor (live page)', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Logged-in users with permission to edit the post see a fixed toolbar at the bottom of the page.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'How to open', 'jcp-core' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'From WP Admin: JCP Page Editor → “Edit on live page (click text & buttons)”', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Or visit the live URL while logged in — a toolbar appears at the bottom of the page', 'jcp-core' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Editing', 'jcp-core' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Click “Click to edit page” on the bottom toolbar.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click highlighted text to edit it inline.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click a button or CTA to change its label or URL in the popover.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click an image or video area to swap media (upload, library, or YouTube/Vimeo URL).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Use + / × on stat rows, FAQ items, and similar lists to add or remove entries.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “Save changes” (or press Cmd/Ctrl + S). The page reloads with saved content.', 'jcp-core' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Toolbar reference', 'jcp-core' ); ?></h3>
			<table class="widefat striped jcp-theme-docs__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Control', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Action', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Click to edit page', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Turns on inline editing mode', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Page structure', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Opens the layout sidebar to reorder, add, or remove sections', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Save changes', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Writes JSON back via REST API (enabled when there are unsaved edits)', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'WP Admin', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Jump back to the post editor', 'jcp-core' ); ?></td>
					</tr>
				</tbody>
			</table>

			<div class="notice notice-info inline jcp-theme-docs__notice">
				<p>
					<?php
					printf(
						/* translators: %s: block library admin link */
						esc_html__( 'To reorder sections or add/remove blocks, use Page structure on the live toolbar, or see %s for all available block types.', 'jcp-core' ),
						'<a href="' . esc_url( $block_lib_url ) . '">' . esc_html__( 'JCP → Block Library', 'jcp-core' ) . '</a>'
					);
					?>
				</p>
			</div>
		</section>

		<section id="page-structure" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Page structure (reorder blocks)', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'The front-end editor includes a layout panel for changing which sections appear on the page and in what order — without editing JSON by hand.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'How to open', 'jcp-core' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'On the live page toolbar, click “Page structure”.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Or add ?jcp_structure=1 to the page URL while logged in.', 'jcp-core' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'What you can do', 'jcp-core' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Drag blocks in the sidebar to reorder sections.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “+ Add block” to insert a section from the block library (filtered for this page type).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “Remove” on a block to delete that section (confirmation required).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “Save changes” — the page reloads with the new layout.', 'jcp-core' ); ?></li>
			</ol>

			<div class="notice notice-warning inline jcp-theme-docs__notice">
				<p>
					<strong><?php esc_html_e( 'Note:', 'jcp-core' ); ?></strong>
					<?php esc_html_e( 'New blocks are inserted with placeholder copy. Use click-to-edit or document import to fill in content. Reordering does not change text until you save.', 'jcp-core' ); ?>
				</p>
			</div>
		</section>

		<section id="block-library" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Block Library', 'jcp-core' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: admin link */
					esc_html__( 'All registered page blocks (Hero, FAQ, Final CTA, etc.) are listed in %s with descriptions, page types, and doc-import section names.', 'jcp-core' ),
					'<a href="' . esc_url( $block_lib_url ) . '">' . esc_html__( 'JCP → Block Library', 'jcp-core' ) . '</a>'
				);
				?>
			</p>
			<p><?php esc_html_e( 'Blocks are shared across industry pages, block-built pages, home, and referral. Each page type only shows blocks allowed for that kind in the “Add block” modal.', 'jcp-core' ); ?></p>
		</section>

		<section id="seo" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'SEO (Rank Math + JCP)', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Every structured page needs a primary focus keyword and full Rank Math optimization before publish.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'Rank Math (required)', 'jcp-core' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Set Focus Keyword — one primary phrase per page (e.g. “HVAC marketing software”).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Write SEO Title — include the keyword near the start (~50–60 characters).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Write Meta Description — benefit-led summary with keyword (~140–160 characters).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Use Rank Math’s score suggestions until green where possible.', 'jcp-core' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'JCP SEO Health box', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'On each block page edit screen, the “SEO Health” meta box cross-checks Rank Math settings against your hero H1 and subheadline. The Industries / Pages list table also shows an SEO column (OK / Needs work / Incomplete).', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'On-page copy', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'Use the focus keyword naturally in the hero H1, subheadline, and at least one section headline. Document import fills body copy; you still set Rank Math meta separately.', 'jcp-core' ); ?></p>
			<p><?php esc_html_e( 'The “Primary Keyword” line in writer documents is saved for hub search on /industries/ — copy it into Rank Math as the focus keyword.', 'jcp-core' ); ?></p>
		</section>

		<section id="hub" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Industries hub', 'jcp-core' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: hub URL */
					esc_html__( 'All published industry pages appear automatically on %s. Visitors can search and sort by title. No extra setup required after publish.', 'jcp-core' ),
					'<a href="' . esc_url( $hub_url ) . '" target="_blank" rel="noopener">/industries/</a>'
				);
				?>
			</p>
		</section>

		<section id="troubleshooting" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Troubleshooting', 'jcp-core' ); ?></h2>
			<table class="widefat striped jcp-theme-docs__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Problem', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Fix', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Import says “Paste document text or upload…”', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Ensure the doc includes section headers like HERO on their own lines. Paste from plain text or use .docx / .txt.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Built JSON looks wrong / sections missing', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Check section headers are ALL CAPS on their own line. Check field labels (H1, Headline, etc.) match the template exactly.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Page not on /industries/ hub', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Post must be Published (not Draft). Slug must be set.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'No edit toolbar on live page', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Log in to WordPress with a role that can edit the post (Editor or Administrator).', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Save changes button stays disabled', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Click “Click to edit page” first, then make an edit. The button enables when there are unsaved changes.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Imported content not on the live site', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'After “Build page from document”, you must click Update / Publish in WP Admin. Import alone does not publish.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Too many templates in the dropdown', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'New pages should only show Default + JCP Block Page. If you see many templates, refresh the page. Fixed-route templates (Pricing, Demo, etc.) only appear when editing that specific page slug.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'JCP Page Editor panel is missing', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Assign the JCP Block Page template (Settings → Template), click Update, then reload the edit screen.', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( '404 on /industries/new-slug/', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Go to Settings → Permalinks and click Save (flushes rewrite rules).', 'jcp-core' ); ?></td>
					</tr>
				</tbody>
			</table>
		</section>
	</div>
	<style>
		.jcp-theme-docs__actions { margin: 1em 0 1.5em; display: flex; gap: 8px; flex-wrap: wrap; }
		.jcp-theme-docs__toc { background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 12px 16px; margin: 0 0 24px; max-width: 320px; }
		.jcp-theme-docs__toc ul { margin: 8px 0 0; padding-left: 18px; }
		.jcp-theme-docs__section { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 16px 20px 20px; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
		.jcp-theme-docs__section h2 { margin-top: 0; padding-bottom: 8px; border-bottom: 1px solid #dcdcde; }
		.jcp-theme-docs__section h3 { margin-top: 1.25em; }
		.jcp-theme-docs__steps { font-size: 14px; line-height: 1.6; }
		.jcp-theme-docs__steps li { margin-bottom: 12px; }
		.jcp-theme-docs__code { display: inline-block; background: #f6f7f7; padding: 6px 10px; border-radius: 3px; }
		.jcp-theme-docs__template { background: #1d2327; color: #f0f0f1; padding: 16px; overflow: auto; max-height: 420px; font-size: 12px; line-height: 1.5; white-space: pre-wrap; word-break: break-word; }
		.jcp-theme-docs__table { margin-top: 12px; }
		.jcp-theme-docs__notice { margin: 16px 0 0; padding: 10px 12px; }
	</style>
	<script>
	(function () {
		var btn = document.getElementById('jcp-copy-writer-template');
		var pre = document.querySelector('.jcp-theme-docs__template');
		var status = document.getElementById('jcp-copy-template-status');
		if (!btn || !pre) return;
		btn.addEventListener('click', function () {
			var text = pre.textContent || '';
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function () {
					status.textContent = '<?php echo esc_js( __( 'Copied!', 'jcp-core' ) ); ?>';
				});
			} else {
				var ta = document.createElement('textarea');
				ta.value = text;
				document.body.appendChild(ta);
				ta.select();
				document.execCommand('copy');
				document.body.removeChild(ta);
				status.textContent = '<?php echo esc_js( __( 'Copied!', 'jcp-core' ) ); ?>';
			}
		});
	})();
	</script>
	<?php
}

/**
 * Full industry trade page writer skeleton.
 */
function jcp_theme_docs_get_industry_writer_template(): string {
	return <<<'TEMPLATE'
Word count:

Primary Keyword: trade keyword one, keyword two, keyword three

SEO Title (website/blogs only):

Meta Description (website/blogs only):

↓ Write Content Here ↓


HERO
H1
[Main headline]
Subheadline
[Supporting paragraph]
CTA
Start free trial
See how it works
Trust Line
No credit card · Free trial · Setup in under 10 minutes

WHAT IT IS
Headline
[Section headline]
Subheadline
[Section subheadline]

Most [trade] companies are already:
[bullet one]
[bullet two]
[bullet three]
But very little of that work actually shows up online consistently.
JobCapturePro fixes that.
It turns real job activity into:
[output one]
[output two]
[output three]
[output four]
automatically.

Closing Line
[Closing sentence for this section]

CTA
[Optional — leave blank to hide section button]

CORE MECHANIC
1 photo
 Proof created instantly
4 channels
 Google, website, social, directory
0 busywork
 Nothing new for your crew

MEDIA CORE
Headline
[Optional — auto-filled from What It Is if omitted]
Subheadline
[Optional subheadline]
Body
[Optional body copy]
CTA
See how it works
Badge
[Optional badge label, e.g. Live Demo]

HOW IT WORKS
Headline
How it works for your [trade] business
Subheadline
Four steps. One app. Zero busywork for your crew

01 Capture
[Step one line one]
 [Step one line two — indent with leading space]
 [Step one line three]

02 Check-In
[Step two content lines…]

03 Publish
That job becomes live proof across:
Google Business Profile
 Your website
 Social channels
 Contractor directory
[Additional publish lines…]

04 Review
[Step four content lines…]

CTA
See it in action

CHECK-INS
Headline
[Headline]
Subheadline
[Subheadline]

[Feature title one]
 [Feature body — indent with leading space]
[Feature title two]
 [Feature body]

CTA
[Optional — leave blank to hide]

MEDIA CHECK-INS
Headline
[Optional — auto-filled from Check-Ins if omitted]
Body
[Optional supporting copy]

PROBLEM
Headline
[Headline]
Subheadline
[Subheadline]

[Pain point title]
 [Pain point body]
[Pain point title]
 [Pain point body]

[Closing sentence one]
[Closing sentence two]

CTA
[Optional — leave blank to hide]

MEDIA PROBLEM
Headline
[Optional — auto-filled from Problem if omitted]
Subheadline
[Optional]
Body
[Optional closing / supporting copy]

BENEFITS
Headline
[Headline]

[Benefit title]
 [Benefit body]

[Closing paragraph title]
 [Closing paragraph body]

CTA
[Optional primary button]
[Optional secondary link]

DIFFERENTIATION
Headline
[Headline]

[Body paragraph line one]
[Body paragraph line two]
[Body paragraph line three]

No new process
 No extra admin
 No marketing workload

CTA
[Optional — leave blank to hide]

WHO IT'S FOR
Headline
[Headline]

Owners
 [Owner audience body]
Technicians
 [Technician audience body]
Growing teams
 [Growing teams body]

CTA
[Optional — leave blank to hide]

FAQ
Headline
Common questions from [trade] companies

[Question ending with ?]
[Answer paragraph]

CTA
[Optional — leave blank to hide]

CONVERSION
Headline
[Headline — e.g. This works when the work is real]
Subheadline
[Supporting paragraph]
[Checklist bullet one]
[Checklist bullet two]
[Checklist bullet three]
CTA
See how this works for your business

FINAL CTA
Headline
[Final headline]
Subheadline
[Final subheadline — optional; hide in Page Structure if unused]
CTA Note
[Text under the button — optional]

CTA
Start free trial
See how it works
TEMPLATE;
}

/**
 * Writer document skeleton for copy/paste (any layout preset).
 *
 * @param string $preset Optional preset slug.
 */
function jcp_theme_docs_get_writer_template( string $preset = 'industry' ): string {
	return jcp_writer_get_document_template( $preset );
}

/**
 * Enqueue docs styles on the docs screen only.
 *
 * @param string $hook Current admin page hook.
 */
function jcp_theme_docs_admin_assets( string $hook ): void {
	if ( $hook !== 'toplevel_page_jcp-theme-settings' ) {
		return;
	}
	// Styles are inline in jcp_theme_docs_render_page for portability.
}
add_action( 'admin_enqueue_scripts', 'jcp_theme_docs_admin_assets' );
