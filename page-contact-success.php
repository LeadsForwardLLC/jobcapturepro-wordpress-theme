<?php
/**
 * Template Name: Contact Success
 *
 * Shown after successful Support form submission.
 * URL: /contact-success/?topic=billing (topic optional).
 * Always noindex. Front-end editable via simple page content.
 *
 * @package JCP_Core
 */

get_header();

$topic_raw = isset( $_GET['topic'] ) ? wp_unslash( (string) $_GET['topic'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$topic     = function_exists( 'jcp_contact_success_normalize_topic' )
	? jcp_contact_success_normalize_topic( $topic_raw )
	: '';

$post_id = get_queried_object_id();
if ( $post_id <= 0 ) {
	$page = get_page_by_path( 'contact-success', OBJECT, 'page' );
	$post_id = $page instanceof WP_Post ? (int) $page->ID : 0;
}

$flat = ( $post_id > 0 && function_exists( 'jcp_page_get_content_flat' ) )
	? jcp_page_get_content_flat( $post_id )
	: [];
$hero = is_array( $flat['hero'] ?? null ) ? $flat['hero'] : [];

$headline = function_exists( 'jcp_contact_success_resolve_headline' )
	? jcp_contact_success_resolve_headline( $topic, $flat )
	: __( 'Message sent', 'jcp-core' );

$subtitle = trim( (string) ( $hero['subheadline'] ?? '' ) );
if ( $subtitle === '' ) {
	$subtitle = __( 'Thanks for reaching out. We’ll review your note and get back within 24–48 hours.', 'jcp-core' );
}

$immediate = trim( (string) ( $hero['immediate_help'] ?? '' ) );
if ( $immediate === '' ) {
	$immediate = __( 'Need help sooner? Email or call — we’re happy to jump on it.', 'jcp-core' );
}

$cta_primary = is_array( $hero['cta_primary'] ?? null ) ? $hero['cta_primary'] : [];
$cta_secondary = is_array( $hero['cta_secondary'] ?? null ) ? $hero['cta_secondary'] : [];
$primary_label = trim( (string) ( $cta_primary['label'] ?? '' ) );
$primary_url   = trim( (string) ( $cta_primary['url'] ?? '' ) );
$secondary_label = trim( (string) ( $cta_secondary['label'] ?? '' ) );
$secondary_url   = trim( (string) ( $cta_secondary['url'] ?? '' ) );
if ( $primary_label === '' ) {
	$primary_label = __( 'Back to Support', 'jcp-core' );
}
if ( $primary_url === '' ) {
	$primary_url = home_url( '/support/' );
}
if ( $secondary_label === '' ) {
	$secondary_label = __( 'Browse Help Center', 'jcp-core' );
}
if ( $secondary_url === '' ) {
	$secondary_url = home_url( '/help/' );
}

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

$topic_labels = [
	'getting-started'  => __( 'Getting Started', 'jcp-core' ),
	'technical-issue'  => __( 'Technical Issue', 'jcp-core' ),
	'feature-request'  => __( 'Feature Request', 'jcp-core' ),
	'billing'          => __( 'Billing', 'jcp-core' ),
	'general-question' => __( 'General Question', 'jcp-core' ),
];
$topic_label = $topic !== '' && isset( $topic_labels[ $topic ] ) ? $topic_labels[ $topic ] : '';
?>
<main class="jcp-marketing jcp-contact-page jcp-success-page" data-jcp-topic="<?php echo esc_attr( $topic ); ?>">
	<section class="jcp-section rankings-section jcp-success-section">
		<div class="jcp-container">
			<div class="rankings-header">
				<?php if ( $topic_label !== '' ) : ?>
					<p class="jcp-contact-eyebrow jcp-success-topic"><?php echo esc_html( $topic_label ); ?></p>
				<?php endif; ?>
				<h1<?php if ( function_exists( 'jcp_niche_editable_attr' ) && $topic === '' ) { jcp_niche_editable_attr( 'hero.h1' ); } ?>><?php echo esc_html( $headline ); ?></h1>
				<p class="rankings-subtitle"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.subheadline' ); } ?>><?php echo esc_html( $subtitle ); ?></p>
			</div>

			<div class="jcp-success-immediate" aria-labelledby="jcp-success-immediate-heading">
				<h2 id="jcp-success-immediate-heading" class="jcp-success-immediate__title"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.immediate_help' ); } ?>><?php echo esc_html( $immediate ); ?></h2>
				<ul class="jcp-contact-channels jcp-success-channels">
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

			<div class="jcp-form-actions jcp-success-actions">
				<a class="btn btn-primary" href="<?php echo esc_url( $primary_url ); ?>"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.cta_primary.label' ); } ?>><?php echo esc_html( $primary_label ); ?></a>
				<a class="btn btn-secondary" href="<?php echo esc_url( $secondary_url ); ?>"<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'hero.cta_secondary.label' ); } ?>><?php echo esc_html( $secondary_label ); ?></a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
