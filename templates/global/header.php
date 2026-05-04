<?php
/**
 * Global Header Template
 * Renders the opening HTML, head, and body tags
 *
 * @package JCP_Core
 */
$pages = jcp_core_get_page_detection();

// Sitewide early-bird banner: hide on standalone demos, directory, contractor profiles, prototype, UI library.
$GLOBALS['jcp_show_promo_bar'] = empty( $pages['is_demo'] )
	&& empty( $pages['is_directory'] )
	&& empty( $pages['is_company'] )
	&& empty( $pages['is_prototype'] )
	&& empty( $pages['is_ui_library'] );

$jcp_body_classes = array_values(
	array_filter(
		[
			'jcp-global-nav-active',
			! empty( $GLOBALS['jcp_show_promo_bar'] ) ? 'jcp-has-promo-bar' : null,
		]
	)
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class( $jcp_body_classes ); ?>>
  <?php if ( ! empty( $GLOBALS['jcp_show_promo_bar'] ) ) : ?>
    <?php get_template_part( 'templates/partials/promo-banner' ); ?>
  <?php endif; ?>
  <?php get_template_part( 'templates/partials/nav' ); ?>
  <div class="jcp-shell">
