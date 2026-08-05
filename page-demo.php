<?php
/**
 * Template Name: Demo
 *
 * When ?mode=run: Renders the live demo app via JavaScript (data-jcp-page="demo").
 * Otherwise: Renders the survey (steps + deck) via WordPress template parts.
 *
 * @package JCP_Core
 */

$demo_mode = isset( $_GET['mode'] ) && $_GET['mode'] === 'run'; // phpcs:ignore

if ( $demo_mode ) {
	?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="jcp-demo-run-html">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'jcp-demo-run demo-run-only' ); ?>>
<div id="jcp-app" data-jcp-page="demo"></div>
<?php wp_footer(); ?>
</body>
</html>
	<?php
	return;
}

// Survey: minimal document shell (no site header/footer) for full-screen takeover.
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'survey-only' ); ?>>
<?php get_template_part( 'templates/survey/wrapper' ); ?>
<?php wp_footer(); ?>
</body>
</html>
