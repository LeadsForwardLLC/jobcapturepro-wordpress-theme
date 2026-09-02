<?php
/**
 * Global Header Template
 * Renders the opening HTML, head, and body tags
 *
 * @package JCP_Core
 */
$pages = jcp_core_get_page_detection();
$hide_site_chrome = function_exists( 'jcp_page_current_hides_site_chrome' ) && jcp_page_current_hides_site_chrome();
$show_top_banner  = ! $hide_site_chrome && (
	function_exists( 'jcp_global_should_show_banner' )
		? jcp_global_should_show_banner( $pages )
		: (
			empty( $pages['is_prototype'] )
			&& empty( $pages['is_wp_plugin_prototype'] )
			&& empty( $pages['is_demo'] )
			&& empty( $pages['is_directory'] )
			&& empty( $pages['is_company'] )
			&& empty( $pages['is_estimate'] )
			&& empty( $pages['is_ui_library'] )
		)
);

$body_classes = ( $hide_site_chrome ? 'jcp-landing-chrome-hidden' : 'jcp-global-nav-active' ) . ( $show_top_banner ? ' has-top-banner' : '' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class( $body_classes ); ?>>
  <div class="jcp-header-stack" id="jcpHeaderStack">
  <?php if ( $hide_site_chrome ) : ?>
    <?php
    $landing_demo_url = function_exists( 'home_url' ) ? home_url( '/demo/' ) : '/demo/';
    ?>
    <header class="jcp-landing-brandbar" role="banner" data-jcp-landing-brandbar>
      <div class="jcp-landing-brandbar__inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="jcp-landing-brandbar__link" aria-label="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>">
          <img
            src="https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png"
            alt="JobCapturePro"
            class="jcp-landing-brandbar__logo"
            width="160"
            height="36"
          />
        </a>
        <a
          class="jcp-landing-brandbar__cta"
          href="<?php echo esc_url( $landing_demo_url ); ?>"
          data-cta="Start Demo"
          data-cta-location="landing_sticky_bar"
        ><?php esc_html_e( 'Start Demo', 'jcp-core' ); ?></a>
      </div>
    </header>
  <?php else : ?>
  <?php if ( $show_top_banner ) : ?>
    <?php
    $banner      = function_exists( 'jcp_global_settings' ) ? ( jcp_global_settings()['banner'] ?? [] ) : [];
    $banner_url  = function_exists( 'jcp_global_banner_cta_url' ) ? jcp_global_banner_cta_url( $banner ) : home_url( '/pricing' );
    $headline    = (string) ( $banner['headline'] ?? 'Start Free Trial:' );
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
  <?php
  $jcp_show_mega_panels = empty( $pages['is_demo'] )
    && empty( $pages['is_directory'] )
    && empty( $pages['is_company'] )
    && ! ( function_exists( 'jcp_is_directory_mode' ) && jcp_is_directory_mode() );
  if ( $jcp_show_mega_panels && function_exists( 'jcp_nav_render_desktop_features_mega_panel' ) ) :
    $jcp_home_features  = ! empty( $pages['is_home'] ) ? '#features' : esc_url( home_url( '/#features' ) );
    $jcp_industries_url = home_url( '/industries/' );
    ?>
  <div class="jcp-desktop-mega-panels" id="jcpDesktopMegaPanels" aria-hidden="true">
    <?php
    jcp_nav_render_desktop_features_mega_panel( $jcp_home_features );
    jcp_nav_render_desktop_trade_mega_panel( $jcp_industries_url );
    ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
  </div>
  <script>
  (function () {
    var stack = document.getElementById('jcpHeaderStack');
    if (!stack) return;
    var height = Math.ceil(stack.getBoundingClientRect().height);
    if (height > 0) {
      document.documentElement.style.setProperty('--jcp-header-stack-height', height + 'px');
    }
  })();
  </script>
  <div class="jcp-shell">
