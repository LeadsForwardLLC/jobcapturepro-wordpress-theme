<?php
/**
 * Template Name: Sales Tool
 *
 * Full-screen interactive sales presentation (blank live-call workspace).
 *
 * @package JCP_Core
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="jcp-sales-tool-html">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex,nofollow">
	<meta name="theme-color" content="#ff5036">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'jcp-sales-tool-page' ); ?>>
<?php get_template_part( 'templates/sales-tool/shell' ); ?>
<?php wp_footer(); ?>
</body>
</html>
