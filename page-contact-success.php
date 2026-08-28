<?php
/**
 * Template Name: Contact Success
 *
 * Shown after successful Contact form submission.
 * URL: /contact-success/ (routed via template-routes when no WP page exists).
 * Optional query param: attachment_omitted=1 to show attachment notice.
 *
 * @package JCP_Core
 */

get_header();

$attachment_omitted  = isset( $_GET['attachment_omitted'] ) && $_GET['attachment_omitted'] === '1'; // phpcs:ignore
$default_message     = __( "Thank you. Your message has been sent. We'll get back to you within one business day.", 'jcp-core' );
if ( $attachment_omitted ) {
	$default_message .= ' ' . __( 'The attachment could not be included due to server size limits—please email it separately if needed.', 'jcp-core' );
}
// Default is backup only: never show when page content is set in WP admin.
$page_content = '';
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$page_content = get_post_field( 'post_content', get_the_ID() );
		break;
	}
	rewind_posts();
}
$supporting = trim( (string) $page_content );
$subtitle   = $supporting !== '' ? $supporting : $default_message;
?>
<main class="jcp-marketing jcp-contact-page jcp-success-page">
  <section class="jcp-section rankings-section jcp-success-section">
    <div class="jcp-container">
      <div class="rankings-header">
        <h1>Message sent</h1>
        <?php if ( $supporting !== '' ) : ?>
          <div class="jcp-page-intro jcp-archive-intro"><?php echo apply_filters( 'the_content', $page_content ); ?></div>
        <?php else : ?>
          <p class="rankings-subtitle"><?php echo esc_html( $subtitle ); ?></p>
        <?php endif; ?>
      </div>
      <div class="jcp-form-actions jcp-success-actions">
        <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( 'Back to Support', 'jcp-core' ); ?></a>
        <a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/demo' ) ); ?>"><?php esc_html_e( 'See the Demo', 'jcp-core' ); ?></a>
      </div>
    </div>
  </section>
</main>
<?php
get_footer();
