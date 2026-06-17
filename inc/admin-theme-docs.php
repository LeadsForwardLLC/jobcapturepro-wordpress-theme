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

	$industries_url = admin_url( 'edit.php?post_type=jcp_niche_landing' );
	$add_new_url    = admin_url( 'post-new.php?post_type=jcp_niche_landing' );
	$hub_url        = home_url( '/industries/' );
	$marketing_url = admin_url( 'edit.php?post_type=jcp_page' );
	$marketing_new = admin_url( 'post-new.php?post_type=jcp_page' );
	$block_lib_url = admin_url( 'admin.php?page=jcp-block-library' );
	$docs_url      = admin_url( 'admin.php?page=jcp-theme-settings' );
	?>
	<div class="wrap jcp-theme-docs">
		<h1><?php esc_html_e( 'JCP Page System — Standard Operating Procedure', 'jcp-core' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'How to build structured marketing pages using the global block library: industry trade pages, marketing landers, and referral pages.', 'jcp-core' ); ?>
		</p>

		<div class="jcp-theme-docs__actions">
			<a href="<?php echo esc_url( $add_new_url ); ?>" class="button button-primary"><?php esc_html_e( 'Add Industry Page', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( $marketing_new ); ?>" class="button button-primary"><?php esc_html_e( 'Add Marketing Page', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( $industries_url ); ?>" class="button"><?php esc_html_e( 'All Industries', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( $marketing_url ); ?>" class="button"><?php esc_html_e( 'All Marketing Pages', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( $hub_url ); ?>" class="button" target="_blank" rel="noopener"><?php esc_html_e( 'View /industries/ hub', 'jcp-core' ); ?></a>
		</div>

		<nav class="jcp-theme-docs__toc" aria-label="<?php esc_attr_e( 'On this page', 'jcp-core' ); ?>">
			<strong><?php esc_html_e( 'On this page', 'jcp-core' ); ?></strong>
			<ul>
				<li><a href="#overview"><?php esc_html_e( 'Overview', 'jcp-core' ); ?></a></li>
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
			<p><?php esc_html_e( 'Industry pages are trade-specific landing pages built from structured JSON. Each published page lives at:', 'jcp-core' ); ?></p>
			<pre class="jcp-theme-docs__code">/industries/{slug}/</pre>
			<p><?php esc_html_e( 'All published trades also appear on the hub:', 'jcp-core' ); ?> <a href="<?php echo esc_url( $hub_url ); ?>" target="_blank" rel="noopener">/industries/</a></p>
			<table class="widefat striped jcp-theme-docs__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'What', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Where', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Manage pages', 'jcp-core' ); ?></td>
						<td><a href="<?php echo esc_url( $industries_url ); ?>"><?php esc_html_e( 'WP Admin → Industries', 'jcp-core' ); ?></a></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'This SOP', 'jcp-core' ); ?></td>
						<td><a href="<?php echo esc_url( $docs_url ); ?>"><?php esc_html_e( 'WP Admin → JCP → Industry Pages', 'jcp-core' ); ?></a></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Site navigation', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Main nav “By Trade” links to /industries/', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Marketing / internal pages (WP Pages)', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Pages → assign “JCP Block Page” template — keeps existing URL and Rank Math SEO', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Referral program', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Pages → “Referral Program” template (same block system)', 'jcp-core' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'SEO title & meta description', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'Rank Math panel on each Industry post (not in the theme JSON)', 'jcp-core' ); ?></td>
					</tr>
				</tbody>
			</table>
		</section>

		<section id="quick-start" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Quick start — WP Page (About, Features, etc.)', 'jcp-core' ); ?></h2>
			<ol class="jcp-theme-docs__steps">
				<li>
					<strong><?php esc_html_e( 'Use an existing page or create one', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'WP Admin → Pages. Keep the same slug if converting an existing page — URL and SEO stay intact.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Assign template', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'In Page attributes, choose “JCP Block Page”. Update the page.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Build content', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Import a document, load the marketing preset, or use “Edit on live page”. Rank Math SEO is unchanged.', 'jcp-core' ); ?>
				</li>
			</ol>
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
					<?php esc_html_e( 'In the “Import from Document” box, paste your Google Doc / Word export (or upload .docx / .txt). Click “Build page from document”. JSON fills in below.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Publish', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Click Update / Publish. The page is live at /industries/{slug}/ and listed on the hub.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'SEO in Rank Math', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Add SEO title and meta description in the Rank Math box on the same post.', 'jcp-core' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Polish copy on the live page (optional)', 'jcp-core' ); ?></strong><br />
					<?php esc_html_e( 'Use “Edit on live page” in Quick Edit to open the front-end editor for click-to-edit tweaks.', 'jcp-core' ); ?>
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
				<li><?php esc_html_e( 'Open the Industry post in WP Admin.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Expand “Landing Page — Import from Document”.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Paste or upload the writer file.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “Build page from document”.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Review JSON in “Advanced JSON” (optional).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click Update / Publish to save.', 'jcp-core' ); ?></li>
			</ol>

			<div class="notice notice-warning inline jcp-theme-docs__notice">
				<p>
					<strong><?php esc_html_e( 'Important:', 'jcp-core' ); ?></strong>
					<?php esc_html_e( '“Build page from document” fills the JSON editor but does not save until you click Update / Publish. Re-building overwrites the JSON textarea — save manual JSON edits first if you changed them by hand.', 'jcp-core' ); ?>
				</p>
			</div>

			<h3><?php esc_html_e( 'Section headers the parser recognizes', 'jcp-core' ); ?></h3>
			<table class="widefat striped jcp-theme-docs__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Section header (ALL CAPS line)', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Becomes', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>HERO</code></td><td><?php esc_html_e( 'Hero banner (H1, subheadline, CTAs, trust line)', 'jcp-core' ); ?></td></tr>
					<tr><td><code>WHAT IT IS</code></td><td><?php esc_html_e( 'Intro section with bullet lists', 'jcp-core' ); ?></td></tr>
					<tr><td><code>CORE MECHANIC</code></td><td><?php esc_html_e( 'Three stat blocks (e.g. 1 photo / 4 channels / 0 busywork)', 'jcp-core' ); ?></td></tr>
					<tr><td><code>HOW IT WORKS</code></td><td><?php esc_html_e( 'Numbered steps 01–04 + CTA', 'jcp-core' ); ?></td></tr>
					<tr><td><code>CHECK-INS</code></td><td><?php esc_html_e( 'Feature cards', 'jcp-core' ); ?></td></tr>
					<tr><td><code>PROBLEM</code></td><td><?php esc_html_e( 'Pain point cards + closing line', 'jcp-core' ); ?></td></tr>
					<tr><td><code>BENEFITS</code></td><td><?php esc_html_e( 'Benefit cards + closing paragraph', 'jcp-core' ); ?></td></tr>
					<tr><td><code>DIFFERENTIATION</code></td><td><?php esc_html_e( 'Body copy + short bullets', 'jcp-core' ); ?></td></tr>
					<tr><td><code>WHO IT'S FOR</code></td><td><?php esc_html_e( 'Audience cards (Owners, Technicians, etc.)', 'jcp-core' ); ?></td></tr>
					<tr><td><code>FAQ</code></td><td><?php esc_html_e( 'Question / answer pairs', 'jcp-core' ); ?></td></tr>
					<tr><td><code>CONVERSION</code></td><td><?php esc_html_e( 'Checklist + image band (headline, bullets, CTA — add photo on live page)', 'jcp-core' ); ?></td></tr>
					<tr><td><code>FINAL CTA</code></td><td><?php esc_html_e( 'Bottom conversion band', 'jcp-core' ); ?></td></tr>
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
			<p><?php esc_html_e( 'Each Industry post has three content meta boxes:', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( '1. Import from Document', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'Paste or upload the writer file and click “Build page from document”. Use this for initial page creation or full rewrites.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( '2. Quick Edit', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'Fast fields for the most-edited copy:', 'jcp-core' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Hero H1', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Hero subheadline', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Final CTA headline', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Final CTA button label', 'jcp-core' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Also includes “Edit on live page” — opens the published page with the front-end editor ready.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( '3. Advanced JSON', 'jcp-core' ); ?></h3>
			<p><?php esc_html_e( 'Full page content as JSON. Developers or advanced users can edit directly. Template buttons load plumbing or HVAC as a starting point.', 'jcp-core' ); ?></p>
			<div class="notice notice-info inline jcp-theme-docs__notice">
				<p><?php esc_html_e( 'JSON is the source of truth. Quick Edit fields merge into JSON on save. Import fills JSON via the Build button.', 'jcp-core' ); ?></p>
			</div>
		</section>

		<section id="frontend-editor" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'Front-end editor (live page)', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'Logged-in users with permission to edit the post see a fixed toolbar at the bottom of the page.', 'jcp-core' ); ?></p>

			<h3><?php esc_html_e( 'How to open', 'jcp-core' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'From WP Admin: Quick Edit → “Edit on live page (click text & buttons)”', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Or visit the live URL while logged in and click “Click to edit page” on the toolbar', 'jcp-core' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Editing', 'jcp-core' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Click “Click to edit page” on the bottom toolbar.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click any highlighted text to edit it inline.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click a button/CTA to change its URL in the popover.', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'Click “Save changes” (or press Cmd/Ctrl + S).', 'jcp-core' ); ?></li>
				<li><?php esc_html_e( 'The page reloads with saved content.', 'jcp-core' ); ?></li>
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
			<p><?php esc_html_e( 'Blocks are shared across Industry pages, Marketing pages, and the referral program. Each page type only shows blocks allowed for that kind in the “Add block” modal.', 'jcp-core' ); ?></p>
		</section>

		<section id="seo" class="jcp-theme-docs__section">
			<h2><?php esc_html_e( 'SEO (Rank Math)', 'jcp-core' ); ?></h2>
			<p><?php esc_html_e( 'SEO title and meta description are not stored in theme JSON. Set them in the Rank Math panel on each Industry post after publishing.', 'jcp-core' ); ?></p>
			<p><?php esc_html_e( 'The “Primary Keyword” line in writer documents is used only for search on the /industries/ hub — not for meta tags.', 'jcp-core' ); ?></p>
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
 * Writer document skeleton for copy/paste.
 */
function jcp_theme_docs_get_writer_template(): string {
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

CORE MECHANIC
1 photo
 Proof created instantly
4 channels
 Google, website, social, directory
0 busywork
 Nothing new for your crew

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

BENEFITS
Headline
[Headline]

[Benefit title]
 [Benefit body]

[Closing paragraph title]
 [Closing paragraph body]

DIFFERENTIATION
Headline
[Headline]

[Body paragraph line one]
[Body paragraph line two]
[Body paragraph line three]

No new process
 No extra admin
 No marketing workload

WHO IT'S FOR
Headline
[Headline]

Owners
 [Owner audience body]
Technicians
 [Technician audience body]
Growing teams
 [Growing teams body]

FAQ
Headline
Common questions from [trade] companies

[Question ending with ?]
[Answer paragraph]

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
[Final subheadline]

CTA
Start free trial
See how it works
TEMPLATE;
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
