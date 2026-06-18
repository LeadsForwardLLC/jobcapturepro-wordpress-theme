<?php
/**
 * Admin: JCP Block Library reference page.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Block Library under JCP menu.
 */
function jcp_block_library_admin_menu(): void {
	add_submenu_page(
		'jcp-theme-settings',
		__( 'Block Library', 'jcp-core' ),
		__( 'Block Library', 'jcp-core' ),
		'edit_posts',
		'jcp-block-library',
		'jcp_block_library_render_page'
	);
}
add_action( 'admin_menu', 'jcp_block_library_admin_menu', 11 );

/**
 * Preview URL for a block type (homepage anchors or UI library).
 *
 * @param string $type Block type key.
 */
function jcp_block_library_preview_url( string $type ): string {
	$home    = home_url( '/' );
	$anchors = [
		'hero'              => '',
		'how_it_works'      => 'how-it-works',
		'demo_preview'      => 'demo-preview',
		'proof_flow'        => 'real-job-proof',
		'benefits'          => 'features',
		'who_its_for'       => 'who-its-for',
		'directory_preview' => 'directory-preview',
		'conversion'        => 'conversion',
		'faq'               => 'faq',
		'final_cta'         => '',
	];
	if ( array_key_exists( $type, $anchors ) ) {
		$anchor = $anchors[ $type ];
		return $anchor !== '' ? $home . '#' . $anchor : $home;
	}
	return home_url( '/ui-library/' );
}

/**
 * UI library anchor hints for block previews (when available).
 *
 * @return array<string, string>
 * @deprecated Use jcp_block_library_preview_url().
 */
function jcp_block_library_preview_anchors(): array {
	return [];
}

/**
 * Render Block Library admin page.
 */
function jcp_block_library_render_page(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$blocks     = jcp_block_registry();
	$ui_library = home_url( '/ui-library/' );
	$by_cat     = [];

	foreach ( $blocks as $block ) {
		$cat = (string) ( $block['category'] ?? 'other' );
		if ( ! isset( $by_cat[ $cat ] ) ) {
			$by_cat[ $cat ] = [];
		}
		$by_cat[ $cat ][] = $block;
	}
	ksort( $by_cat );

	$page_system_url = admin_url( 'admin.php?page=jcp-theme-settings' );
	?>
	<div class="wrap jcp-block-library">
		<h1><?php esc_html_e( 'JCP Block Library', 'jcp-core' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Blocks are full page sections (Hero, Proof flow, FAQ, etc.) you add, reorder, and edit on a page. Components are smaller building blocks inside them (demo phone mockup, directory card, factor card) — shared PHP partials, not separate blocks.', 'jcp-core' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Industry, block-built, referral, and homepage page types each show only the blocks allowed for that kind. No duplicate entries — one registry, one renderer per block.', 'jcp-core' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $page_system_url ); ?>" class="button"><?php esc_html_e( 'Page System SOP', 'jcp-core' ); ?></a>
			<a href="<?php echo esc_url( $ui_library ); ?>" class="button" target="_blank" rel="noopener"><?php esc_html_e( 'Open UI Library', 'jcp-core' ); ?></a>
		</p>

		<?php foreach ( $by_cat as $category => $items ) : ?>
			<h2 class="jcp-block-library__category"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $category ) ) ); ?></h2>
			<table class="widefat striped jcp-block-library__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Block', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Type key', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Used on', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Doc import sections', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Preview', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $block ) : ?>
						<?php
						$type    = (string) ( $block['type'] ?? '' );
						$kinds   = $block['page_kinds'] ?? [];
						$docs    = $block['doc_sections'] ?? [];
						$preview = jcp_block_library_preview_url( $type );
						$on_home = in_array( 'home', $kinds, true );
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( (string) ( $block['label'] ?? $type ) ); ?></strong>
								<p class="description"><?php echo esc_html( (string) ( $block['description'] ?? '' ) ); ?></p>
							</td>
							<td><code><?php echo esc_html( $type ); ?></code></td>
							<td><?php echo esc_html( implode( ', ', $kinds ) ); ?></td>
							<td>
								<?php
								if ( $docs ) {
									echo esc_html( implode( ', ', $docs ) );
								} else {
									echo '<span class="description">—</span>';
								}
								?>
							</td>
							<td>
								<a href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener"><?php echo $on_home ? esc_html__( 'Homepage', 'jcp-core' ) : esc_html__( 'UI Library', 'jcp-core' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endforeach; ?>
	</div>
	<style>
		.jcp-block-library__category { margin-top: 2em; }
		.jcp-block-library__category:first-of-type { margin-top: 1.25em; }
		.jcp-block-library__table { margin-top: 0.5em; }
		.jcp-block-library__table td { vertical-align: top; }
		.jcp-block-library__table .description { margin: 4px 0 0; }
	</style>
	<?php
}
