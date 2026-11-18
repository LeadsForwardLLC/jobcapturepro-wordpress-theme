<?php
/**
 * Footer app store badges (official Apple / Google artwork).
 *
 * @package JCP_Core
 */

defined( 'ABSPATH' ) || exit;

$app_store_url  = $args['app_store_url'] ?? 'https://apps.apple.com/us/app/jobcapturepro/id6636248590';
$play_store_url = $args['play_store_url'] ?? 'https://play.google.com/store/apps/details?id=com.jobcapturepro.mobile&pcampaignid=web_share';
$badge_base     = trailingslashit( get_template_directory_uri() ) . 'assets/badges/';
?>
<div class="jcp-footer-apps">
  <p class="jcp-footer-apps-label"><?php esc_html_e( 'Get the app', 'jcp-core' ); ?></p>
  <div class="jcp-footer-apps-badges">
    <a
      href="<?php echo esc_url( $app_store_url ); ?>"
      class="jcp-footer-app-badge jcp-footer-app-badge--apple"
      target="_blank"
      rel="noopener noreferrer"
    >
      <img
        src="<?php echo esc_url( $badge_base . 'app-store-black.svg' ); ?>"
        alt="<?php esc_attr_e( 'Download on the App Store', 'jcp-core' ); ?>"
        width="120"
        height="40"
        loading="lazy"
        decoding="async"
      />
    </a>
    <a
      href="<?php echo esc_url( $play_store_url ); ?>"
      class="jcp-footer-app-badge jcp-footer-app-badge--google"
      target="_blank"
      rel="noopener noreferrer"
    >
      <img
        src="<?php echo esc_url( $badge_base . 'google-play-badge.svg' ); ?>"
        alt="<?php esc_attr_e( 'Get it on Google Play', 'jcp-core' ); ?>"
        width="135"
        height="40"
        loading="lazy"
        decoding="async"
      />
    </a>
  </div>
</div>
