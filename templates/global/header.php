<?php
/**
 * Global Header Template
 * Renders the opening HTML, head, and body tags
 *
 * @package JCP_Core
 */
$pages = jcp_core_get_page_detection();
$show_top_banner = function_exists( 'jcp_global_should_show_banner' )
	? jcp_global_should_show_banner( $pages )
	: (
		empty( $pages['is_prototype'] )
		&& empty( $pages['is_wp_plugin_prototype'] )
		&& empty( $pages['is_demo'] )
		&& empty( $pages['is_directory'] )
		&& empty( $pages['is_company'] )
		&& empty( $pages['is_estimate'] )
		&& empty( $pages['is_ui_library'] )
	);

$body_classes = 'jcp-global-nav-active' . ( $show_top_banner ? ' has-top-banner' : '' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class( $body_classes ); ?>>
  <?php if ( $show_top_banner ) : ?>
    <?php
    $banner      = function_exists( 'jcp_global_settings' ) ? ( jcp_global_settings()['banner'] ?? [] ) : [];
    $banner_url  = function_exists( 'jcp_global_banner_cta_url' ) ? jcp_global_banner_cta_url( $banner ) : home_url( '/pricing' );
    $headline    = (string) ( $banner['headline'] ?? 'Early Bird:' );
    $message     = (string) ( $banner['text'] ?? '' );
    $code        = trim( (string) ( $banner['code'] ?? '' ) );
    $cta_label   = (string) ( $banner['cta_label'] ?? 'Claim offer' );
    $aria_label  = $headline !== '' ? $headline : __( 'Site announcement', 'jcp-core' );
    ?>
    <div class="jcp-top-banner" id="jcpSiteBanner" role="region" aria-label="<?php echo esc_attr( $aria_label ); ?>">
      <div class="jcp-top-banner__inner">
        <div class="jcp-top-banner__copy">
          <?php if ( $headline !== '' ) : ?>
            <strong class="jcp-top-banner__headline"><?php echo esc_html( $headline ); ?></strong>
          <?php endif; ?>
          <?php if ( $message !== '' ) : ?>
            <span class="jcp-top-banner__text"><?php echo esc_html( $message ); ?></span>
          <?php endif; ?>
          <?php if ( $code !== '' ) : ?>
            <span class="jcp-top-banner__code"><?php esc_html_e( 'Code:', 'jcp-core' ); ?> <strong><?php echo esc_html( $code ); ?></strong></span>
          <?php endif; ?>
        </div>
        <div class="jcp-top-banner__actions">
          <?php if ( $cta_label !== '' ) : ?>
            <a class="jcp-top-banner__cta" href="<?php echo esc_url( $banner_url ); ?>"><?php echo esc_html( $cta_label ); ?> →</a>
          <?php endif; ?>
          <button type="button" class="jcp-top-banner__close" id="jcpSiteBannerClose" aria-label="<?php esc_attr_e( 'Dismiss banner', 'jcp-core' ); ?>">
            <span aria-hidden="true">×</span>
          </button>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <?php get_template_part( 'templates/partials/nav' ); ?>
  <div class="jcp-shell">
