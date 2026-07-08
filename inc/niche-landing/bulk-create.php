<?php
/**
 * Bulk-create industry trade pages for writers.
 *
 * @package JCP_Core
 */

/**
 * Register Bulk add submenu under Industries.
 */
function jcp_niche_register_bulk_create_page(): void {
	add_submenu_page(
		'edit.php?post_type=jcp_niche_landing',
		__( 'Bulk add trade pages', 'jcp-core' ),
		__( 'Bulk add', 'jcp-core' ),
		'edit_posts',
		'jcp-bulk-industries',
		'jcp_niche_render_bulk_create_page'
	);
}
add_action( 'admin_menu', 'jcp_niche_register_bulk_create_page' );

/**
 * Handle bulk create form submission.
 */
function jcp_niche_handle_bulk_create(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'jcp-core' ) );
	}
	check_admin_referer( 'jcp_bulk_create_industries' );

	$lines  = isset( $_POST['jcp_bulk_lines'] ) ? (string) wp_unslash( $_POST['jcp_bulk_lines'] ) : '';
	$status = isset( $_POST['jcp_bulk_status'] ) && $_POST['jcp_bulk_status'] === 'publish' ? 'publish' : 'draft';
	$init   = ! empty( $_POST['jcp_bulk_init_skeleton'] );

	$created = 0;
	$skipped = 0;
	$errors  = [];

	foreach ( preg_split( '/\r\n|\r|\n/', $lines ) ?: [] as $line ) {
		$line = trim( $line );
		if ( $line === '' || str_starts_with( $line, '#' ) ) {
			continue;
		}

		$title = $line;
		$slug  = '';
		if ( str_contains( $line, '|' ) ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$title = $parts[0];
			$slug  = $parts[1] ?? '';
		}
		if ( $title === '' ) {
			continue;
		}
		if ( $slug === '' ) {
			$slug = sanitize_title( $title );
		} else {
			$slug = sanitize_title( $slug );
		}

		$existing = get_page_by_path( $slug, OBJECT, 'jcp_niche_landing' );
		if ( $existing instanceof WP_Post ) {
			++$skipped;
			continue;
		}

		$post_id = wp_insert_post(
			[
				'post_type'   => 'jcp_niche_landing',
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_status' => $status,
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$errors[] = $title . ': ' . $post_id->get_error_message();
			continue;
		}

		if ( $init ) {
			$post = get_post( (int) $post_id );
			if ( $post instanceof WP_Post ) {
				jcp_page_save_content( (int) $post_id, jcp_page_create_skeleton_document( $post, 'industry' ) );
			}
		}

		++$created;
	}

	$redirect = add_query_arg(
		[
			'page'           => 'jcp-bulk-industries',
			'jcp_bulk_done'    => 1,
			'jcp_bulk_created' => $created,
			'jcp_bulk_skipped' => $skipped,
			'jcp_bulk_errors'  => count( $errors ),
		],
		admin_url( 'edit.php?post_type=jcp_niche_landing' )
	);
	if ( $errors ) {
		set_transient( 'jcp_bulk_create_errors_' . get_current_user_id(), $errors, MINUTE_IN_SECONDS * 5 );
	}
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_jcp_bulk_create_industries', 'jcp_niche_handle_bulk_create' );

/**
 * Render bulk create admin page.
 */
function jcp_niche_render_bulk_create_page(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$done    = isset( $_GET['jcp_bulk_done'] );
	$created = isset( $_GET['jcp_bulk_created'] ) ? (int) $_GET['jcp_bulk_created'] : 0;
	$skipped = isset( $_GET['jcp_bulk_skipped'] ) ? (int) $_GET['jcp_bulk_skipped'] : 0;
	$errors  = get_transient( 'jcp_bulk_create_errors_' . get_current_user_id() );
	if ( is_array( $errors ) ) {
		delete_transient( 'jcp_bulk_create_errors_' . get_current_user_id() );
	} else {
		$errors = [];
	}

	$sop_url = admin_url( 'admin.php?page=jcp-theme-settings#document-import' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Bulk add trade pages', 'jcp-core' ); ?></h1>

		<?php if ( $done ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: created count, 2: skipped count */
						esc_html__( 'Created %1$d draft pages. Skipped %2$d existing slugs.', 'jcp-core' ),
						$created,
						$skipped
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( $errors ) : ?>
			<div class="notice notice-error">
				<p><strong><?php esc_html_e( 'Some rows could not be created:', 'jcp-core' ); ?></strong></p>
				<ul>
					<?php foreach ( $errors as $err ) : ?>
						<li><?php echo esc_html( $err ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<p class="description">
			<?php
			printf(
				/* translators: %s: SOP link */
				esc_html__( 'Add many industry pages at once. One trade per line. Optionally use Title|slug. After creating, open each page, paste your writer doc, and click Build page. %s', 'jcp-core' ),
				'<a href="' . esc_url( $sop_url ) . '">' . esc_html__( 'Writer guide →', 'jcp-core' ) . '</a>'
			);
			?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'jcp_bulk_create_industries' ); ?>
			<input type="hidden" name="action" value="jcp_bulk_create_industries" />

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="jcp_bulk_lines"><?php esc_html_e( 'Trade pages', 'jcp-core' ); ?></label></th>
					<td>
						<textarea name="jcp_bulk_lines" id="jcp_bulk_lines" rows="14" class="large-text code" placeholder="<?php esc_attr_e( "Plumbing\nHVAC\nRoofing|roofing-contractors", 'jcp-core' ); ?>"></textarea>
						<p class="description"><?php esc_html_e( 'One per line. Use Title|custom-slug for a custom URL slug. Lines starting with # are ignored.', 'jcp-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Status', 'jcp-core' ); ?></th>
					<td>
						<label><input type="radio" name="jcp_bulk_status" value="draft" checked /> <?php esc_html_e( 'Draft (recommended)', 'jcp-core' ); ?></label><br />
						<label><input type="radio" name="jcp_bulk_status" value="publish" /> <?php esc_html_e( 'Publish immediately', 'jcp-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Section structure', 'jcp-core' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="jcp_bulk_init_skeleton" value="1" checked />
							<?php esc_html_e( 'Pre-fill empty industry section list (recommended — writers see all sections before import)', 'jcp-core' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Create trade pages', 'jcp-core' ) ); ?>
		</form>
	</div>
	<?php
}
