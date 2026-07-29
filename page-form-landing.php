<?php
/**
 * Template Name: Form Landing
 *
 * Full-screen, distraction-free form / embed page.
 * No site header, footer, nav, or banner.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id  = get_queried_object_id();
$settings = function_exists( 'jcp_form_landing_get_settings' )
	? jcp_form_landing_get_settings( (int) $post_id )
	: [];

$title = trim( (string) ( $settings['title'] ?? '' ) );
if ( $title === '' ) {
	$title = get_the_title( $post_id );
}
if ( $title === '' ) {
	$title = __( 'Get started', 'jcp-core' );
}

$supporting  = trim( (string) ( $settings['supporting'] ?? '' ) );
$reassurance = trim( (string) ( $settings['reassurance'] ?? '' ) );
$embed       = (string) ( $settings['embed'] ?? '' );
$logo_url    = function_exists( 'jcp_form_landing_logo_url' )
	? jcp_form_landing_logo_url( $settings )
	: 'https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png';
$close_url   = function_exists( 'jcp_form_landing_close_url' )
	? jcp_form_landing_close_url( $settings )
	: home_url( '/personalized-demo/' );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'jcp-form-landing' ); ?>>
<a class="jcp-form-landing__skip" href="#jcp-form-landing-main"><?php esc_html_e( 'Skip to content', 'jcp-core' ); ?></a>

<header class="jcp-form-landing__top" role="banner">
	<div class="jcp-form-landing__shell">
		<a class="jcp-form-landing__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'JobCapturePro home', 'jcp-core' ); ?>">
			<img
				class="jcp-form-landing__logo"
				src="<?php echo esc_url( $logo_url ); ?>"
				alt="JobCapturePro"
				width="160"
				height="36"
				decoding="async"
			/>
		</a>
		<a
			class="jcp-form-landing__close"
			href="<?php echo esc_url( $close_url ); ?>"
			data-jcp-form-landing-close
			aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>"
		>
			<span aria-hidden="true">&times;</span>
		</a>
	</div>
</header>

<main id="jcp-form-landing-main" class="jcp-form-landing__main">
	<div class="jcp-form-landing__shell jcp-form-landing__inner">
		<h1 class="jcp-form-landing__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $supporting !== '' ) : ?>
			<p class="jcp-form-landing__supporting"><?php echo esc_html( $supporting ); ?></p>
		<?php endif; ?>
		<?php if ( $reassurance !== '' ) : ?>
			<p class="jcp-form-landing__reassurance"><?php echo esc_html( $reassurance ); ?></p>
		<?php endif; ?>

		<div class="jcp-form-landing__embed jcp-fluent-bridge jcp-fluent-bridge--inline">
			<?php
			if ( $embed !== '' && function_exists( 'jcp_form_landing_render_embed' ) ) {
				jcp_form_landing_render_embed( $embed );
			} elseif ( current_user_can( 'edit_posts' ) ) {
				echo '<p class="jcp-form-landing__empty">' . esc_html__( 'Add a shortcode or embed in the Form Landing settings.', 'jcp-core' ) . '</p>';
			}
			?>
		</div>
	</div>
</main>

<?php wp_footer(); ?>
</body>
</html>
