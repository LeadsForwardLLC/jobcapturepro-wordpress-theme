<?php
/**
 * Admin: JCP Global Settings (banner, signup, nav CTAs).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Global Settings submenu.
 */
function jcp_global_settings_admin_menu(): void {
	add_submenu_page(
		'jcp-theme-settings',
		__( 'Global Settings', 'jcp-core' ),
		__( 'Global Settings', 'jcp-core' ),
		'manage_options',
		'jcp-global-settings',
		'jcp_global_settings_render_page'
	);
}
add_action( 'admin_menu', 'jcp_global_settings_admin_menu', 11 );

/**
 * Save posted global settings.
 */
function jcp_global_settings_handle_save(): void {
	if ( ! isset( $_POST['jcp_global_settings_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jcp_global_settings_nonce'] ) ), 'jcp_global_settings_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$input = isset( $_POST['jcp_global'] ) && is_array( $_POST['jcp_global'] )
		? wp_unslash( $_POST['jcp_global'] )
		: [];

	$banner_enabled = ! empty( $input['banner']['enabled'] );
	$visibility     = sanitize_key( (string) ( $input['banner']['visibility'] ?? 'marketing' ) );
	if ( ! in_array( $visibility, [ 'marketing', 'all', 'off' ], true ) ) {
		$visibility = 'marketing';
	}

	$settings = [
		'banner'  => [
			'enabled'     => $banner_enabled,
			'visibility'  => $visibility,
			'headline'    => sanitize_text_field( (string) ( $input['banner']['headline'] ?? '' ) ),
			'text'        => sanitize_text_field( (string) ( $input['banner']['text'] ?? '' ) ),
			'code'        => sanitize_text_field( (string) ( $input['banner']['code'] ?? '' ) ),
			'cta_label'   => sanitize_text_field( (string) ( $input['banner']['cta_label'] ?? '' ) ),
			'cta_url'     => esc_url_raw( (string) ( $input['banner']['cta_url'] ?? '' ) ),
			'coupon'      => sanitize_text_field( (string) ( $input['banner']['coupon'] ?? '' ) ),
			'utm_content' => sanitize_key( (string) ( $input['banner']['utm_content'] ?? 'sitewide_banner' ) ),
		],
		'signup'  => [
			'base_url'   => esc_url_raw( (string) ( $input['signup']['base_url'] ?? '' ) ),
			'session_id' => sanitize_text_field( (string) ( $input['signup']['session_id'] ?? '' ) ),
			'step'       => sanitize_text_field( (string) ( $input['signup']['step'] ?? '1' ) ),
		],
		'nav_cta' => [
			'primary_label'   => sanitize_text_field( (string) ( $input['nav_cta']['primary_label'] ?? '' ) ),
			'primary_url'     => jcp_global_sanitize_url_field( (string) ( $input['nav_cta']['primary_url'] ?? '' ) ),
			'secondary_label' => sanitize_text_field( (string) ( $input['nav_cta']['secondary_label'] ?? '' ) ),
			'secondary_url'   => jcp_global_sanitize_url_field( (string) ( $input['nav_cta']['secondary_url'] ?? '' ) ),
		],
		'contact' => [
			'support_email' => sanitize_email( (string) ( $input['contact']['support_email'] ?? '' ) ),
		],
	];

	update_option( jcp_global_settings_option_key(), jcp_global_settings_merge( jcp_global_settings_defaults(), $settings ) );

	wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=jcp-global-settings' ) ) );
	exit;
}
add_action( 'admin_init', 'jcp_global_settings_handle_save' );

/**
 * Render settings page.
 */
function jcp_global_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s = jcp_global_settings();
	$b = $s['banner'] ?? [];
	$signup = $s['signup'] ?? [];
	$nav = $s['nav_cta'] ?? [];
	$contact = $s['contact'] ?? [];

	if ( isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Global settings saved.', 'jcp-core' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'JCP Global Settings', 'jcp-core' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Sitewide banner, signup URLs, and default navigation CTAs. Per-page nav overrides live in each page’s Quick Edit fields.', 'jcp-core' ); ?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'jcp_global_settings_save', 'jcp_global_settings_nonce' ); ?>

			<h2><?php esc_html_e( 'Sitewide banner', 'jcp-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enabled', 'jcp-core' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="jcp_global[banner][enabled]" value="1" <?php checked( ! empty( $b['enabled'] ) ); ?> />
							<?php esc_html_e( 'Show banner when visibility allows', 'jcp-core' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_visibility"><?php esc_html_e( 'Visibility', 'jcp-core' ); ?></label></th>
					<td>
						<select name="jcp_global[banner][visibility]" id="jcp_banner_visibility">
							<option value="marketing" <?php selected( ( $b['visibility'] ?? '' ), 'marketing' ); ?>><?php esc_html_e( 'Marketing pages only (default)', 'jcp-core' ); ?></option>
							<option value="all" <?php selected( ( $b['visibility'] ?? '' ), 'all' ); ?>><?php esc_html_e( 'All pages', 'jcp-core' ); ?></option>
							<option value="off" <?php selected( ( $b['visibility'] ?? '' ), 'off' ); ?>><?php esc_html_e( 'Off', 'jcp-core' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_headline"><?php esc_html_e( 'Headline', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="jcp_banner_headline" name="jcp_global[banner][headline]" value="<?php echo esc_attr( (string) ( $b['headline'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_text"><?php esc_html_e( 'Message', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="large-text" id="jcp_banner_text" name="jcp_global[banner][text]" value="<?php echo esc_attr( (string) ( $b['text'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_code"><?php esc_html_e( 'Promo code label', 'jcp-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="jcp_banner_code" name="jcp_global[banner][code]" value="<?php echo esc_attr( (string) ( $b['code'] ?? '' ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Leave empty to hide the code pill.', 'jcp-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_cta_label"><?php esc_html_e( 'Button label', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="jcp_banner_cta_label" name="jcp_global[banner][cta_label]" value="<?php echo esc_attr( (string) ( $b['cta_label'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_cta_url"><?php esc_html_e( 'Button URL', 'jcp-core' ); ?></label></th>
					<td>
						<input type="url" class="large-text" id="jcp_banner_cta_url" name="jcp_global[banner][cta_url]" value="<?php echo esc_attr( (string) ( $b['cta_url'] ?? '' ) ); ?>" placeholder="https://" />
						<p class="description"><?php esc_html_e( 'Leave empty to use the signup URL with coupon below.', 'jcp-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_coupon"><?php esc_html_e( 'Signup coupon', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="jcp_banner_coupon" name="jcp_global[banner][coupon]" value="<?php echo esc_attr( (string) ( $b['coupon'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_banner_utm"><?php esc_html_e( 'UTM content', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="jcp_banner_utm" name="jcp_global[banner][utm_content]" value="<?php echo esc_attr( (string) ( $b['utm_content'] ?? 'sitewide_banner' ) ); ?>" /></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Signup / app URL', 'jcp-core' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Used for “Start free trial”, “Get Started”, and empty CTA URLs across the site.', 'jcp-core' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="jcp_signup_base"><?php esc_html_e( 'Base URL', 'jcp-core' ); ?></label></th>
					<td><input type="url" class="large-text" id="jcp_signup_base" name="jcp_global[signup][base_url]" value="<?php echo esc_attr( (string) ( $signup['base_url'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_signup_session"><?php esc_html_e( 'Session ID', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="large-text" id="jcp_signup_session" name="jcp_global[signup][session_id]" value="<?php echo esc_attr( (string) ( $signup['session_id'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_signup_step"><?php esc_html_e( 'Default step', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="small-text" id="jcp_signup_step" name="jcp_global[signup][step]" value="<?php echo esc_attr( (string) ( $signup['step'] ?? '1' ) ); ?>" /></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Navigation CTAs (default)', 'jcp-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="jcp_nav_primary_label"><?php esc_html_e( 'Primary label', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="jcp_nav_primary_label" name="jcp_global[nav_cta][primary_label]" value="<?php echo esc_attr( (string) ( $nav['primary_label'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_nav_primary_url"><?php esc_html_e( 'Primary URL', 'jcp-core' ); ?></label></th>
					<td>
						<input type="url" class="large-text" id="jcp_nav_primary_url" name="jcp_global[nav_cta][primary_url]" value="<?php echo esc_attr( (string) ( $nav['primary_url'] ?? '' ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Empty = signup URL with nav_get_started UTM.', 'jcp-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_nav_secondary_label"><?php esc_html_e( 'Secondary label', 'jcp-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="jcp_nav_secondary_label" name="jcp_global[nav_cta][secondary_label]" value="<?php echo esc_attr( (string) ( $nav['secondary_label'] ?? '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="jcp_nav_secondary_url"><?php esc_html_e( 'Secondary URL', 'jcp-core' ); ?></label></th>
					<td><input type="url" class="large-text" id="jcp_nav_secondary_url" name="jcp_global[nav_cta][secondary_url]" value="<?php echo esc_attr( (string) ( $nav['secondary_url'] ?? '' ) ); ?>" /></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Contact', 'jcp-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="jcp_support_email"><?php esc_html_e( 'Support email', 'jcp-core' ); ?></label></th>
					<td><input type="email" class="regular-text" id="jcp_support_email" name="jcp_global[contact][support_email]" value="<?php echo esc_attr( (string) ( $contact['support_email'] ?? '' ) ); ?>" /></td>
				</tr>
			</table>

			<?php submit_button( __( 'Save global settings', 'jcp-core' ) ); ?>
		</form>
	</div>
	<?php
}
