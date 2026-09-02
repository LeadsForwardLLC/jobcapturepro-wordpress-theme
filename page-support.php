<?php
/**
 * Template Name: Support
 *
 * Support page: Help Center preview, Fluent Form, phone/email last resort.
 * Assign this template to the page with slug "support" (/support/).
 * Hero copy is front-end editable via `_jcp_page_content`.
 *
 * @package JCP_Core
 */

get_header();

$post_id = get_queried_object_id();
if ( $post_id <= 0 ) {
	$page    = get_page_by_path( 'support', OBJECT, 'page' );
	$post_id = $page instanceof WP_Post ? (int) $page->ID : 0;
}

$flat = ( $post_id > 0 && function_exists( 'jcp_page_get_content_flat' ) )
	? jcp_page_get_content_flat( $post_id )
	: [];
$hero = is_array( $flat['hero'] ?? null ) ? $flat['hero'] : [];

$defaults = [
	'title'    => __( 'Support', 'jcp-core' ),
	'subtitle' => __( 'Start with the Help Center — most answers are there. If you still need us, send a message and we’ll get back within one business day.', 'jcp-core' ),
	'eyebrow'  => __( 'Support', 'jcp-core' ),
];

$title = trim( (string) ( $hero['h1'] ?? '' ) );
if ( $title === '' ) {
	$title = trim( (string) get_the_title( $post_id ) );
}
if ( $title === '' ) {
	$title = $defaults['title'];
}

$subtitle = trim( (string) ( $hero['subheadline'] ?? '' ) );
if ( $subtitle === '' ) {
	$page_content = $post_id > 0 ? trim( (string) get_post_field( 'post_content', $post_id ) ) : '';
	$raw_sub      = $page_content !== '' ? wp_strip_all_tags( $page_content ) : '';
	$subtitle     = $raw_sub !== '' && stripos( $raw_sub, 'fill out the form below' ) === false
		? $raw_sub
		: $defaults['subtitle'];
}

$eyebrow = trim( (string) ( $hero['eyebrow'] ?? '' ) );
if ( $eyebrow === '' ) {
	$eyebrow = $defaults['eyebrow'];
}

$cta_primary   = is_array( $hero['cta_primary'] ?? null ) ? $hero['cta_primary'] : [];
$cta_secondary = is_array( $hero['cta_secondary'] ?? null ) ? $hero['cta_secondary'] : [];
$primary_label = trim( (string) ( $cta_primary['label'] ?? '' ) ) ?: __( 'Message support', 'jcp-core' );
$primary_url   = trim( (string) ( $cta_primary['url'] ?? '' ) ) ?: '#contact-form';
$secondary_label = trim( (string) ( $cta_secondary['label'] ?? '' ) ) ?: __( 'Browse Help Center', 'jcp-core' );
$secondary_url   = trim( (string) ( $cta_secondary['url'] ?? '' ) ) ?: home_url( '/help/' );

$contact = function_exists( 'jcp_global_settings' ) ? ( jcp_global_settings()['contact'] ?? [] ) : [];
$email   = sanitize_email( (string) ( $contact['support_email'] ?? '' ) );
if ( $email === '' || $email === 'hello@jobcapturepro.com' ) {
	$email = 'support@jobcapturepro.com';
}
$phone_display = trim( (string) ( $contact['support_phone'] ?? '' ) );
if ( $phone_display === '' ) {
	$phone_display = '(941) 941-9506';
}
$phone_tel = preg_replace( '/[^\d+]/', '', $phone_display );
if ( is_string( $phone_tel ) && $phone_tel !== '' && $phone_tel[0] !== '+' ) {
	$phone_tel = '+1' . $phone_tel;
}

$fluent_shortcode = '[fluentform id="1"]';
if ( function_exists( 'jcp_fluent_sanitize_shortcode' ) ) {
	$fluent_shortcode = jcp_fluent_sanitize_shortcode( $fluent_shortcode );
}

$help_query = new WP_Query(
	[
		'post_type'           => 'help_article',
		'posts_per_page'      => 6,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	]
);
$help_url = home_url( '/help/' );
?>
<main class="jcp-marketing jcp-contact-page jcp-support-page" data-jcp-support-thanks="<?php echo esc_url( home_url( '/contact-success/' ) ); ?>">
	<section class="jcp-section rankings-section jcp-contact-hero">
		<div class="jcp-container">
			<div class="rankings-header">
				<p class="jcp-contact-eyebrow"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.eyebrow' ); } ?>><?php echo esc_html( $eyebrow ); ?></p>
				<h1<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.h1' ); } ?>><?php echo esc_html( $title ); ?></h1>
				<p class="rankings-subtitle"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.subheadline' ); } ?>><?php echo esc_html( $subtitle ); ?></p>
				<div class="jcp-contact-hero-actions">
					<a class="btn btn-secondary" href="<?php echo esc_url( $secondary_url ); ?>"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.cta_secondary.label' ); } ?>><?php echo esc_html( $secondary_label ); ?></a>
					<a class="btn btn-primary" href="<?php echo esc_url( $primary_url ); ?>"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.cta_primary.label' ); } ?>><?php echo esc_html( $primary_label ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $help_query->have_posts() ) : ?>
		<section class="jcp-section jcp-contact-help" aria-labelledby="jcp-contact-help-heading">
			<div class="jcp-container">
				<div class="jcp-contact-section-header">
					<h2 id="jcp-contact-help-heading"><?php esc_html_e( 'Try the Help Center first', 'jcp-core' ); ?></h2>
					<p><?php esc_html_e( 'Guides for setup, Google, Facebook, CRM connections, and reviews — written for contractors and their teams.', 'jcp-core' ); ?></p>
					<a class="jcp-contact-section-link" href="<?php echo esc_url( $help_url ); ?>"><?php esc_html_e( 'View all help articles →', 'jcp-core' ); ?></a>
				</div>
				<div class="jcp-blog-grid jcp-contact-help-grid">
					<?php
					while ( $help_query->have_posts() ) :
						$help_query->the_post();
						get_template_part( 'templates/content/content', 'help-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="jcp-section jcp-form-section" id="contact-form" aria-labelledby="jcp-contact-form-heading">
		<div class="jcp-container">
			<div class="jcp-contact-section-header jcp-contact-section-header--center">
				<h2 id="jcp-contact-form-heading"><?php esc_html_e( 'Still need us? Send a message', 'jcp-core' ); ?></h2>
				<p><?php esc_html_e( 'Setup questions, troubleshooting, billing, feedback — we read every note.', 'jcp-core' ); ?></p>
			</div>
			<div class="jcp-form-wrapper">
				<div class="jcp-contact-fluent jcp-fluent-bridge jcp-fluent-bridge--inline">
					<?php
					if ( $fluent_shortcode !== '' && shortcode_exists( 'fluentform' ) ) {
						echo do_shortcode( $fluent_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fluent Forms shortcode HTML.
					} elseif ( current_user_can( 'edit_pages' ) ) {
						echo '<p class="jcp-contact-fluent-missing">';
						esc_html_e( 'Fluent Forms shortcode [fluentform id="1"] could not render. Activate Fluent Forms and confirm form ID 1 exists.', 'jcp-core' );
						echo '</p>';
					}
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="jcp-section jcp-contact-last-resort" aria-labelledby="jcp-contact-last-resort-heading">
		<div class="jcp-container">
			<div class="jcp-contact-last-resort__card">
				<p class="jcp-contact-eyebrow"><?php esc_html_e( 'Last resort', 'jcp-core' ); ?></p>
				<h2 id="jcp-contact-last-resort-heading"><?php esc_html_e( 'Prefer email or a call?', 'jcp-core' ); ?></h2>
				<p class="jcp-contact-last-resort__copy"><?php esc_html_e( 'If the form isn’t an option, reach us directly. Email is usually fastest; phone is available when you need a human voice.', 'jcp-core' ); ?></p>
				<ul class="jcp-contact-channels">
					<li class="jcp-contact-channel">
						<span class="jcp-contact-channel__label"><?php esc_html_e( 'Email', 'jcp-core' ); ?></span>
						<a class="jcp-contact-channel__value" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</li>
					<li class="jcp-contact-channel">
						<span class="jcp-contact-channel__label"><?php esc_html_e( 'Phone', 'jcp-core' ); ?></span>
						<a class="jcp-contact-channel__value" href="<?php echo esc_url( 'tel:' . $phone_tel ); ?>"><?php echo esc_html( $phone_display ); ?></a>
					</li>
				</ul>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
