<?php
/**
 * Sales deck / sales tool page meta.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key.
 */
function jcp_sales_tool_meta_key(): string {
	return '_jcp_sales_tool';
}

/**
 * Defaults shared by CPT and page template.
 *
 * @return array<string, mixed>
 */
function jcp_sales_tool_defaults(): array {
	return [
		'presenter_type'     => 'internal',
		'presenter_name'     => '',
		'presenter_logo_id'  => 0,
		'mode'               => 'contractor',
		'company'            => '',
		'trade'              => 'Home services',
		'jobs_per_week'      => 20,
		'locations'          => 1,
		'crew_band'          => '2-4',
		'software'           => [],
		'photo_frequency'    => 'most',
		'photo_destination'  => [],
		'publish_habit'      => 'occasionally',
		'review_habit'       => 'occasionally',
		'challenges'         => [],
		'timeline'           => '30_days',
		'capture_rate'       => 45,
		'publish_rate'       => 15,
		'show_pricing'       => 1,
		'show_acculevel'     => 1,
		'acculevel_lead_lift'=> '',
		'cta_label'          => '',
		'cta_url'            => '',
		'secondary_cta_label'=> '',
		'secondary_cta_url'  => '',
	];
}

/**
 * Software stack options (assessment-aligned).
 *
 * @return array<string, string>
 */
function jcp_sales_tool_software_options(): array {
	return [
		'servicetitan'  => 'ServiceTitan',
		'housecallpro'  => 'Housecall Pro',
		'jobber'        => 'Jobber',
		'fieldedge'     => 'FieldEdge',
		'companycam'    => 'CompanyCam',
		'quickbooks'    => 'QuickBooks / Xero only',
		'spreadsheets'  => 'Spreadsheets (Excel/Sheets)',
		'none'          => 'None / Pen & Paper',
		'other'         => 'Other',
	];
}

/**
 * Whether post uses Sales Tool page template.
 *
 * @param int|WP_Post|null $post Post.
 */
function jcp_is_sales_tool_template( $post = null ): bool {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return false;
	}
	return get_page_template_slug( $post ) === 'page-sales-tool.php';
}

/**
 * Whether current request is a sales tool surface.
 */
function jcp_is_sales_tool_request(): bool {
	if ( is_singular( 'jcp_sales_deck' ) ) {
		return true;
	}
	if ( jcp_is_sales_tool_template() ) {
		return true;
	}
	return is_page_template( 'page-sales-tool.php' );
}

/**
 * Get merged settings for a post.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function jcp_sales_tool_get_settings( int $post_id ): array {
	$stored = get_post_meta( $post_id, jcp_sales_tool_meta_key(), true );
	$stored = is_array( $stored ) ? $stored : [];
	$out    = array_merge( jcp_sales_tool_defaults(), $stored );

	$out['presenter_type']      = sanitize_key( (string) ( $out['presenter_type'] ?? 'internal' ) );
	$out['presenter_name']      = sanitize_text_field( (string) ( $out['presenter_name'] ?? '' ) );
	$out['presenter_logo_id']   = absint( $out['presenter_logo_id'] ?? 0 );
	$out['mode']                = in_array( (string) ( $out['mode'] ?? '' ), [ 'contractor', 'affiliate', 'partner' ], true ) ? (string) $out['mode'] : 'contractor';
	$out['company']             = sanitize_text_field( (string) ( $out['company'] ?? '' ) );
	$out['trade']               = sanitize_text_field( (string) ( $out['trade'] ?? 'Home services' ) );
	$out['jobs_per_week']       = max( 1, absint( $out['jobs_per_week'] ?? 20 ) );
	$out['locations']           = max( 1, absint( $out['locations'] ?? 1 ) );
	$out['crew_band']           = sanitize_text_field( (string) ( $out['crew_band'] ?? '2-4' ) );
	$out['software']            = array_values( array_filter( array_map( 'sanitize_key', (array) ( $out['software'] ?? [] ) ) ) );
	$out['photo_frequency']     = sanitize_key( (string) ( $out['photo_frequency'] ?? 'most' ) );
	$out['photo_destination']   = array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $out['photo_destination'] ?? [] ) ) ) );
	$out['publish_habit']       = sanitize_key( (string) ( $out['publish_habit'] ?? 'occasionally' ) );
	$out['review_habit']        = sanitize_key( (string) ( $out['review_habit'] ?? 'occasionally' ) );
	$out['challenges']          = array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $out['challenges'] ?? [] ) ) ) );
	$out['timeline']            = sanitize_key( (string) ( $out['timeline'] ?? '30_days' ) );
	$out['capture_rate']        = min( 100, max( 0, absint( $out['capture_rate'] ?? 45 ) ) );
	$out['publish_rate']        = min( 100, max( 0, absint( $out['publish_rate'] ?? 15 ) ) );
	$out['show_pricing']        = empty( $out['show_pricing'] ) ? 0 : 1;
	$out['show_acculevel']      = empty( $out['show_acculevel'] ) ? 0 : 1;
	$out['acculevel_lead_lift'] = sanitize_text_field( (string) ( $out['acculevel_lead_lift'] ?? '' ) );
	$out['cta_label']           = sanitize_text_field( (string) ( $out['cta_label'] ?? '' ) );
	$out['cta_url']             = esc_url_raw( (string) ( $out['cta_url'] ?? '' ) );
	$out['secondary_cta_label'] = sanitize_text_field( (string) ( $out['secondary_cta_label'] ?? '' ) );
	$out['secondary_cta_url']   = esc_url_raw( (string) ( $out['secondary_cta_url'] ?? '' ) );

	return $out;
}

/**
 * Register meta boxes.
 */
function jcp_sales_tool_register_meta_boxes(): void {
	add_meta_box(
		'jcp_sales_tool',
		__( 'Sales Presentation', 'jcp-core' ),
		'jcp_sales_tool_render_meta_box',
		[ 'jcp_sales_deck', 'page' ],
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'jcp_sales_tool_register_meta_boxes' );

/**
 * Only show page meta box on Sales Tool template pages (and always for CPT).
 *
 * @param WP_Post $post Post.
 */
function jcp_sales_tool_maybe_remove_page_meta_box( WP_Post $post ): void {
	if ( $post->post_type !== 'page' ) {
		return;
	}
	if ( ! jcp_is_sales_tool_template( $post ) ) {
		remove_meta_box( 'jcp_sales_tool', 'page', 'normal' );
	}
}
add_action( 'add_meta_boxes_page', 'jcp_sales_tool_maybe_remove_page_meta_box', 20 );

/**
 * Render meta box.
 *
 * @param WP_Post $post Post.
 */
function jcp_sales_tool_render_meta_box( WP_Post $post ): void {
	if ( $post->post_type === 'page' && ! jcp_is_sales_tool_template( $post ) ) {
		echo '<p>' . esc_html__( 'Assign the Sales Tool page template to edit presentation settings.', 'jcp-core' ) . '</p>';
		return;
	}

	$s = jcp_sales_tool_get_settings( (int) $post->ID );
	wp_nonce_field( 'jcp_sales_tool_save', 'jcp_sales_tool_nonce' );

	$software_opts = jcp_sales_tool_software_options();
	$crew_opts     = [
		'solo'  => 'Solo / 1 Crew',
		'2-4'   => '2 - 4 Crews',
		'5-10'  => '5 - 10 Crews',
		'11+'   => '11+ Crews',
	];
	$photo_freq = [
		'every'       => 'Every single job (Before/After)',
		'most'        => 'Most jobs, if they remember',
		'occasionally'=> 'Occasionally on major projects',
		'rarely'      => 'Rarely or Never',
	];
	$publish = [
		'automatic'   => 'Automatically (via software)',
		'manual'      => 'Manually (someone uploads them)',
		'occasionally'=> 'Occasionally (when we have time)',
		'never'       => 'Never / We don\'t publish them',
	];
	$review = [
		'automatic'   => 'Automatically (triggered on job completion)',
		'manual'      => 'Manually (technician or office sends a link)',
		'occasionally'=> 'Occasionally (when we remember)',
		'never'       => 'Never / We don\'t ask',
	];
	$challenges = [
		'local_leads'   => 'Getting more high-quality local leads',
		'showcase_work' => 'Showcasing our completed work online',
		'save_time'     => 'Saving time on marketing tasks',
		'reviews'       => 'Generating consistent 5-star reviews',
		'crew_photos'   => 'Holding field crews accountable for photos',
		'maps'          => 'Beating local competitors on Google Maps',
	];
	$timelines = [
		'immediate' => 'Immediately (Within 1-2 weeks)',
		'30_days'   => 'Within the next 30 days',
		'research'  => 'Just researching / No immediate rush',
	];

	$logo_url = '';
	if ( $s['presenter_logo_id'] > 0 ) {
		$logo_url = (string) wp_get_attachment_image_url( (int) $s['presenter_logo_id'], 'medium' );
	}
	?>
	<style>
		.jcp-st-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;max-width:920px}
		.jcp-st-grid .wide{grid-column:1/-1}
		.jcp-st-grid label{display:block;font-weight:600;margin-bottom:4px}
		.jcp-st-grid input[type=text],.jcp-st-grid input[type=number],.jcp-st-grid input[type=url],.jcp-st-grid select{width:100%;max-width:100%}
		.jcp-st-checks{display:flex;flex-wrap:wrap;gap:8px 14px}
		.jcp-st-checks label{font-weight:400}
		.jcp-st-section{margin:18px 0 8px;padding-top:12px;border-top:1px solid #dcdcde;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#646970}
	</style>
	<p class="description"><?php esc_html_e( 'These fields personalize the live sales presentation. Assessment answers can be entered manually now; load-from-assessment comes next.', 'jcp-core' ); ?></p>

	<div class="jcp-st-section"><?php esc_html_e( 'Presenter & audience', 'jcp-core' ); ?></div>
	<div class="jcp-st-grid">
		<div>
			<label for="jcp_st_presenter_type"><?php esc_html_e( 'Presenter type', 'jcp-core' ); ?></label>
			<select name="jcp_st[presenter_type]" id="jcp_st_presenter_type">
				<option value="internal" <?php selected( $s['presenter_type'], 'internal' ); ?>><?php esc_html_e( 'Internal rep', 'jcp-core' ); ?></option>
				<option value="affiliate" <?php selected( $s['presenter_type'], 'affiliate' ); ?>><?php esc_html_e( 'Affiliate', 'jcp-core' ); ?></option>
				<option value="agency" <?php selected( $s['presenter_type'], 'agency' ); ?>><?php esc_html_e( 'Agency / partner', 'jcp-core' ); ?></option>
			</select>
		</div>
		<div>
			<label for="jcp_st_presenter_name"><?php esc_html_e( 'Presented by', 'jcp-core' ); ?></label>
			<input type="text" name="jcp_st[presenter_name]" id="jcp_st_presenter_name" value="<?php echo esc_attr( $s['presenter_name'] ); ?>" />
		</div>
		<div>
			<label for="jcp_st_mode"><?php esc_html_e( 'Story mode', 'jcp-core' ); ?></label>
			<select name="jcp_st[mode]" id="jcp_st_mode">
				<option value="contractor" <?php selected( $s['mode'], 'contractor' ); ?>><?php esc_html_e( 'Contractor (direct)', 'jcp-core' ); ?></option>
				<option value="affiliate" <?php selected( $s['mode'], 'affiliate' ); ?>><?php esc_html_e( 'Affiliate / referral', 'jcp-core' ); ?></option>
				<option value="partner" <?php selected( $s['mode'], 'partner' ); ?>><?php esc_html_e( 'Agency / strategic partner', 'jcp-core' ); ?></option>
			</select>
		</div>
		<div>
			<label for="jcp_st_presenter_logo_id"><?php esc_html_e( 'Presenter logo attachment ID', 'jcp-core' ); ?></label>
			<input type="number" min="0" name="jcp_st[presenter_logo_id]" id="jcp_st_presenter_logo_id" value="<?php echo esc_attr( (string) $s['presenter_logo_id'] ); ?>" />
			<?php if ( $logo_url ) : ?>
				<p><img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="max-height:48px;margin-top:6px;" /></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="jcp-st-section"><?php esc_html_e( 'Prospect / company (assessment)', 'jcp-core' ); ?></div>
	<div class="jcp-st-grid">
		<div>
			<label for="jcp_st_company"><?php esc_html_e( 'Company name', 'jcp-core' ); ?></label>
			<input type="text" name="jcp_st[company]" id="jcp_st_company" value="<?php echo esc_attr( $s['company'] ); ?>" />
		</div>
		<div>
			<label for="jcp_st_trade"><?php esc_html_e( 'Niche / trade', 'jcp-core' ); ?></label>
			<input type="text" name="jcp_st[trade]" id="jcp_st_trade" value="<?php echo esc_attr( $s['trade'] ); ?>" />
		</div>
		<div>
			<label for="jcp_st_jobs"><?php esc_html_e( 'Avg weekly completed jobs', 'jcp-core' ); ?></label>
			<input type="number" min="1" name="jcp_st[jobs_per_week]" id="jcp_st_jobs" value="<?php echo esc_attr( (string) $s['jobs_per_week'] ); ?>" />
		</div>
		<div>
			<label for="jcp_st_locations"><?php esc_html_e( 'Office locations', 'jcp-core' ); ?></label>
			<input type="number" min="1" name="jcp_st[locations]" id="jcp_st_locations" value="<?php echo esc_attr( (string) $s['locations'] ); ?>" />
		</div>
		<div>
			<label for="jcp_st_crew"><?php esc_html_e( 'Field crew count', 'jcp-core' ); ?></label>
			<select name="jcp_st[crew_band]" id="jcp_st_crew">
				<?php foreach ( $crew_opts as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $s['crew_band'], $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="jcp_st_timeline"><?php esc_html_e( 'Timeline', 'jcp-core' ); ?></label>
			<select name="jcp_st[timeline]" id="jcp_st_timeline">
				<?php foreach ( $timelines as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $s['timeline'], $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="wide">
			<label><?php esc_html_e( 'Software stack', 'jcp-core' ); ?></label>
			<div class="jcp-st-checks">
				<?php foreach ( $software_opts as $k => $label ) : ?>
					<label><input type="checkbox" name="jcp_st[software][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $s['software'], true ) ); ?> /> <?php echo esc_html( $label ); ?></label>
				<?php endforeach; ?>
			</div>
		</div>
		<div>
			<label for="jcp_st_photo_freq"><?php esc_html_e( 'Photo frequency', 'jcp-core' ); ?></label>
			<select name="jcp_st[photo_frequency]" id="jcp_st_photo_freq">
				<?php foreach ( $photo_freq as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $s['photo_frequency'], $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="jcp_st_publish"><?php esc_html_e( 'How jobs reach web / Google / social', 'jcp-core' ); ?></label>
			<select name="jcp_st[publish_habit]" id="jcp_st_publish">
				<?php foreach ( $publish as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $s['publish_habit'], $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="jcp_st_review"><?php esc_html_e( 'How review requests are sent', 'jcp-core' ); ?></label>
			<select name="jcp_st[review_habit]" id="jcp_st_review">
				<?php foreach ( $review as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $s['review_habit'], $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="jcp_st_capture"><?php esc_html_e( 'Jobs with usable photos %', 'jcp-core' ); ?></label>
			<input type="number" min="0" max="100" name="jcp_st[capture_rate]" id="jcp_st_capture" value="<?php echo esc_attr( (string) $s['capture_rate'] ); ?>" />
		</div>
		<div>
			<label for="jcp_st_publish_rate"><?php esc_html_e( 'Jobs published as proof %', 'jcp-core' ); ?></label>
			<input type="number" min="0" max="100" name="jcp_st[publish_rate]" id="jcp_st_publish_rate" value="<?php echo esc_attr( (string) $s['publish_rate'] ); ?>" />
		</div>
		<div class="wide">
			<label><?php esc_html_e( 'Top challenges (up to 3)', 'jcp-core' ); ?></label>
			<div class="jcp-st-checks">
				<?php foreach ( $challenges as $k => $label ) : ?>
					<label><input type="checkbox" name="jcp_st[challenges][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $s['challenges'], true ) ); ?> /> <?php echo esc_html( $label ); ?></label>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="jcp-st-section"><?php esc_html_e( 'Deck options & CTAs', 'jcp-core' ); ?></div>
	<div class="jcp-st-grid">
		<div>
			<label><input type="checkbox" name="jcp_st[show_pricing]" value="1" <?php checked( $s['show_pricing'], 1 ); ?> /> <?php esc_html_e( 'Show retail pricing (from live pricing catalog)', 'jcp-core' ); ?></label>
		</div>
		<div>
			<label><input type="checkbox" name="jcp_st[show_acculevel]" value="1" <?php checked( $s['show_acculevel'], 1 ); ?> /> <?php esc_html_e( 'Include Acculevel LocalFalcon proof', 'jcp-core' ); ?></label>
		</div>
		<div>
			<label for="jcp_st_lead_lift"><?php esc_html_e( 'Acculevel verified lead lift % (optional)', 'jcp-core' ); ?></label>
			<input type="text" name="jcp_st[acculevel_lead_lift]" id="jcp_st_lead_lift" value="<?php echo esc_attr( $s['acculevel_lead_lift'] ); ?>" placeholder="<?php esc_attr_e( 'Leave blank unless verified', 'jcp-core' ); ?>" />
		</div>
		<div>
			<label for="jcp_st_cta_label"><?php esc_html_e( 'Primary CTA label override', 'jcp-core' ); ?></label>
			<input type="text" name="jcp_st[cta_label]" id="jcp_st_cta_label" value="<?php echo esc_attr( $s['cta_label'] ); ?>" placeholder="<?php esc_attr_e( 'Defaults to Start Free Trial', 'jcp-core' ); ?>" />
		</div>
		<div class="wide">
			<label for="jcp_st_cta_url"><?php esc_html_e( 'Primary CTA URL override', 'jcp-core' ); ?></label>
			<input type="url" name="jcp_st[cta_url]" id="jcp_st_cta_url" value="<?php echo esc_attr( $s['cta_url'] ); ?>" placeholder="<?php esc_attr_e( 'Defaults to onboarding signup URL', 'jcp-core' ); ?>" />
		</div>
		<div>
			<label for="jcp_st_sec_label"><?php esc_html_e( 'Secondary CTA label', 'jcp-core' ); ?></label>
			<input type="text" name="jcp_st[secondary_cta_label]" id="jcp_st_sec_label" value="<?php echo esc_attr( $s['secondary_cta_label'] ); ?>" placeholder="<?php esc_attr_e( 'See It for My Business', 'jcp-core' ); ?>" />
		</div>
		<div>
			<label for="jcp_st_sec_url"><?php esc_html_e( 'Secondary CTA URL', 'jcp-core' ); ?></label>
			<input type="url" name="jcp_st[secondary_cta_url]" id="jcp_st_sec_url" value="<?php echo esc_attr( $s['secondary_cta_url'] ); ?>" placeholder="<?php echo esc_attr( home_url( '/demo/' ) ); ?>" />
		</div>
	</div>
	<?php
}

/**
 * Save meta.
 *
 * @param int $post_id Post ID.
 */
function jcp_sales_tool_save_meta( int $post_id ): void {
	if ( ! isset( $_POST['jcp_sales_tool_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jcp_sales_tool_nonce'] ) ), 'jcp_sales_tool_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( $post->post_type === 'page' && ! jcp_is_sales_tool_template( $post ) ) {
		return;
	}
	if ( ! in_array( $post->post_type, [ 'page', 'jcp_sales_deck' ], true ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['jcp_st'] ) || ! is_array( $_POST['jcp_st'] ) ) {
		return;
	}

	$raw = wp_unslash( $_POST['jcp_st'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$defaults = jcp_sales_tool_defaults();
	$out      = $defaults;

	$out['presenter_type']      = sanitize_key( (string) ( $raw['presenter_type'] ?? 'internal' ) );
	$out['presenter_name']      = sanitize_text_field( (string) ( $raw['presenter_name'] ?? '' ) );
	$out['presenter_logo_id']   = absint( $raw['presenter_logo_id'] ?? 0 );
	$mode_raw = sanitize_key( (string) ( $raw['mode'] ?? 'contractor' ) );
	$out['mode'] = in_array( $mode_raw, [ 'contractor', 'affiliate', 'partner' ], true ) ? $mode_raw : 'contractor';
	$out['company']             = sanitize_text_field( (string) ( $raw['company'] ?? '' ) );
	$out['trade']               = sanitize_text_field( (string) ( $raw['trade'] ?? 'Home services' ) );
	$out['jobs_per_week']       = max( 1, absint( $raw['jobs_per_week'] ?? 20 ) );
	$out['locations']           = max( 1, absint( $raw['locations'] ?? 1 ) );
	$out['crew_band']           = sanitize_text_field( (string) ( $raw['crew_band'] ?? '2-4' ) );
	$out['software']            = isset( $raw['software'] ) && is_array( $raw['software'] ) ? array_values( array_map( 'sanitize_key', $raw['software'] ) ) : [];
	$out['photo_frequency']     = sanitize_key( (string) ( $raw['photo_frequency'] ?? 'most' ) );
	$out['publish_habit']       = sanitize_key( (string) ( $raw['publish_habit'] ?? 'occasionally' ) );
	$out['review_habit']        = sanitize_key( (string) ( $raw['review_habit'] ?? 'occasionally' ) );
	$out['challenges']          = isset( $raw['challenges'] ) && is_array( $raw['challenges'] ) ? array_slice( array_values( array_map( 'sanitize_text_field', $raw['challenges'] ) ), 0, 3 ) : [];
	$out['timeline']            = sanitize_key( (string) ( $raw['timeline'] ?? '30_days' ) );
	$out['capture_rate']        = min( 100, max( 0, absint( $raw['capture_rate'] ?? 45 ) ) );
	$out['publish_rate']        = min( 100, max( 0, absint( $raw['publish_rate'] ?? 15 ) ) );
	$out['show_pricing']        = empty( $raw['show_pricing'] ) ? 0 : 1;
	$out['show_acculevel']      = empty( $raw['show_acculevel'] ) ? 0 : 1;
	$out['acculevel_lead_lift'] = sanitize_text_field( (string) ( $raw['acculevel_lead_lift'] ?? '' ) );
	$out['cta_label']           = sanitize_text_field( (string) ( $raw['cta_label'] ?? '' ) );
	$out['cta_url']             = esc_url_raw( (string) ( $raw['cta_url'] ?? '' ) );
	$out['secondary_cta_label'] = sanitize_text_field( (string) ( $raw['secondary_cta_label'] ?? '' ) );
	$out['secondary_cta_url']   = esc_url_raw( (string) ( $raw['secondary_cta_url'] ?? '' ) );

	update_post_meta( $post_id, jcp_sales_tool_meta_key(), $out );
}
add_action( 'save_post', 'jcp_sales_tool_save_meta' );
