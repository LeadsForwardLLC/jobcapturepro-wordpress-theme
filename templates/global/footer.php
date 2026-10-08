<?php
/**
 * Global Footer Template
 * Renders the global footer and closing body/html tags.
 * Directory Mode: same component, contextual link groups and blurb via jcp_is_directory_mode().
 *
 * @package JCP_Core
 */

$privacy_url = 'https://jobcapturepro.com/privacy-policy/';
$terms_url  = 'https://jobcapturepro.com/terms-and-conditions/';
$leadsforward_url = 'https://leadsforward.com/';
$app_store_url = 'https://apps.apple.com/us/app/jobcapturepro/id6636248590';
$play_store_url = 'https://play.google.com/store/apps/details?id=com.jobcapturepro.mobile&pcampaignid=web_share';
$footer_support = '';
$footer_sales   = '';
$footer_address = '';
$social_links = [
    'facebook'  => [ 'url' => 'https://www.facebook.com/profile.php?id=61574958638999', 'label' => 'Facebook' ],
    'x'        => [ 'url' => 'https://x.com/jobcapturepro', 'label' => 'X' ],
    'instagram'=> [ 'url' => 'https://www.instagram.com/jobcapturepro/', 'label' => 'Instagram' ],
    'tiktok'   => [ 'url' => 'https://www.tiktok.com/@jobcapturepro', 'label' => 'TikTok' ],
    'youtube'  => [ 'url' => 'https://www.youtube.com/channel/UCckc38UwNU5P8A7eI1txZAw', 'label' => 'YouTube' ],
];

$directory_mode       = function_exists( 'jcp_is_directory_mode' ) && jcp_is_directory_mode();
$jcp_onb_utms      = function_exists( 'jcp_core_onboarding_utm_defaults' );
$jcp_onboarding_url = ! function_exists( 'jcp_core_onboarding_app_url' )
  ? home_url( '/demo' )
  : ( $jcp_onb_utms
    ? jcp_core_onboarding_app_url( jcp_core_onboarding_utm_defaults( 'footer_signup' ) )
    : jcp_core_onboarding_app_url() );
$jcp_onboarding_url_dir_listed = ! function_exists( 'jcp_core_onboarding_app_url' )
  ? home_url( '/demo' )
  : ( $jcp_onb_utms
    ? jcp_core_onboarding_app_url( jcp_core_onboarding_utm_defaults( 'footer_get_listed' ) )
    : jcp_core_onboarding_app_url() );
$jcp_onboarding_url_dir_started = ! function_exists( 'jcp_core_onboarding_app_url' )
  ? home_url( '/demo' )
  : ( $jcp_onb_utms
    ? jcp_core_onboarding_app_url( jcp_core_onboarding_utm_defaults( 'footer_get_started' ) )
    : jcp_core_onboarding_app_url() );
$dir_url = home_url( '/directory' );
$dir_search = $dir_url . '/#search';
$dir_how = $dir_url . '/#how-it-works';
$dir_trust = $dir_url . '/#trust';
$hide_site_chrome = function_exists( 'jcp_page_current_hides_site_chrome' ) && jcp_page_current_hides_site_chrome();
?>
  <?php if ( $hide_site_chrome ) : ?>
  <footer class="jcp-footer jcp-footer--landing-minimal">
    <div class="jcp-container jcp-footer-bottom-inner">
      <nav class="jcp-footer-legal" aria-label="<?php esc_attr_e( 'Legal', 'jcp-core' ); ?>">
        <a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy', 'jcp-core' ); ?></a>
        <span class="jcp-footer-sep" aria-hidden="true">·</span>
        <a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms', 'jcp-core' ); ?></a>
      </nav>
      <p class="jcp-footer-landing-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> JobCapturePro</p>
    </div>
  </footer>
  <?php else : ?>
  <footer class="jcp-footer">
    <div class="jcp-container jcp-footer-grid">
      <?php if ( $directory_mode ) : ?>
        <div class="jcp-footer-brand">
          <a href="<?php echo esc_url( $dir_url ); ?>" aria-label="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>">
            <img src="<?php echo esc_url( 'https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png' ); ?>" alt="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>" width="180" height="40" />
          </a>
          <p><?php esc_html_e( 'Verified job proof from active contractors.', 'jcp-core' ); ?></p>
        </div>
        <div class="jcp-footer-col">
          <h4><?php esc_html_e( 'For homeowners', 'jcp-core' ); ?></h4>
          <a href="<?php echo esc_url( $dir_search ); ?>"><?php esc_html_e( 'Find contractors', 'jcp-core' ); ?></a>
          <a href="<?php echo esc_url( $dir_how ); ?>"><?php esc_html_e( 'How rankings work', 'jcp-core' ); ?></a>
          <span class="jcp-footer-col-item"><?php echo esc_html( __( 'Request a quote (coming soon)', 'jcp-core' ) ); ?></span>
        </div>
        <div class="jcp-footer-col">
          <h4><?php esc_html_e( 'For contractors', 'jcp-core' ); ?></h4>
          <a href="<?php echo esc_url( $jcp_onboarding_url_dir_listed ); ?>"<?php
            if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
              jcp_niche_cta_tracking_attr( $jcp_onboarding_url_dir_listed, 'footer', 'Get listed' );
            }
          ?>><?php esc_html_e( 'Get listed', 'jcp-core' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/demo' ) ); ?>"><?php esc_html_e( 'See the live demo', 'jcp-core' ); ?></a>
          <a href="<?php echo esc_url( $jcp_onboarding_url_dir_started ); ?>"<?php
            if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
              jcp_niche_cta_tracking_attr( $jcp_onboarding_url_dir_started, 'footer', 'Start Free Trial' );
            }
          ?>><?php esc_html_e( 'Start Free Trial', 'jcp-core' ); ?></a>
        </div>
      <?php else : ?>
        <div class="jcp-footer-brand">
          <img src="<?php echo esc_url( 'https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png' ); ?>" alt="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>" width="180" height="40" />
          <p>Turn real job photos into proof, visibility, reviews, and more jobs.</p>
        </div>
        <div class="jcp-footer-col">
          <h4>Product</h4>
          <a href="<?php echo esc_url( home_url( '/#how-it-works' ) ); ?>">How it Works</a>
          <a href="<?php echo esc_url( home_url( '/#features' ) ); ?>">Features</a>
          <a href="<?php echo esc_url( home_url( '/industries/' ) ); ?>"><?php esc_html_e( 'By Trade', 'jcp-core' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>">Pricing</a>
        </div>
        <div class="jcp-footer-col">
          <h4>Resources</h4>
          <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">Blog</a>
          <a href="<?php echo esc_url( home_url( '/help' ) ); ?>">Help Center</a>
          <a href="<?php echo esc_url( home_url( '/demo' ) ); ?>">Online Demo</a>
          <a href="<?php echo esc_url( home_url( '/referral-program' ) ); ?>">Referral Program</a>
        </div>
        <div class="jcp-footer-col">
          <h4>Company</h4>
          <a href="<?php echo esc_url( home_url( '/support/' ) ); ?>">Support</a>
          <a href="<?php echo esc_url( $jcp_onboarding_url ); ?>"<?php
            if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
              jcp_niche_cta_tracking_attr( $jcp_onboarding_url, 'footer', 'Start Free Trial' );
            }
          ?>><?php esc_html_e( 'Start Free Trial', 'jcp-core' ); ?></a>
          <?php
          $about_page = get_page_by_path( 'about' );
          if ( $about_page && $about_page->post_status === 'publish' ) :
            ?>
            <a href="<?php echo esc_url( home_url( '/about' ) ); ?>">About JobCapturePro</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="jcp-container jcp-footer-apps">
      <p class="jcp-footer-apps-label"><?php esc_html_e( 'Get the app', 'jcp-core' ); ?></p>
      <div class="jcp-footer-apps-badges">
        <a
          href="<?php echo esc_url( $app_store_url ); ?>"
          class="jcp-footer-app-badge"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="<?php esc_attr_e( 'Download JobCapturePro on the App Store', 'jcp-core' ); ?>"
        >
          <svg class="jcp-footer-app-badge__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
          </svg>
          <span class="jcp-footer-app-badge__copy">
            <span class="jcp-footer-app-badge__eyebrow"><?php esc_html_e( 'Download on the', 'jcp-core' ); ?></span>
            <span class="jcp-footer-app-badge__name"><?php esc_html_e( 'App Store', 'jcp-core' ); ?></span>
          </span>
        </a>
        <a
          href="<?php echo esc_url( $play_store_url ); ?>"
          class="jcp-footer-app-badge"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="<?php esc_attr_e( 'Get JobCapturePro on Google Play', 'jcp-core' ); ?>"
        >
          <svg class="jcp-footer-app-badge__icon jcp-footer-app-badge__icon--play" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
            <path fill="#00F076" d="M1.5 2.1C1.1 2.5.9 3.1.9 3.9v16.2c0 .8.2 1.4.6 1.8l.1.1L12.1 12v-.2L1.6 2l-.1.1z"/>
            <path fill="#FFD400" d="M16.2 14.4l-3.1-3.1v-.2l3.1-3.1.1.1 3.7 2.1c1 .6 1 1.6 0 2.2l-3.7 2.1-.1-.1z"/>
            <path fill="#FF3333" d="M16.3 14.5l-3.2-3.2-10.6 10.6c.4.4 1 .5 1.6.1l12.2-7.5z"/>
            <path fill="#00D3FF" d="M16.3 10.9L4.1 3.4c-.6-.4-1.2-.3-1.6.1l10.6 10.6 3.2-3.2z"/>
          </svg>
          <span class="jcp-footer-app-badge__copy">
            <span class="jcp-footer-app-badge__eyebrow"><?php esc_html_e( 'GET IT ON', 'jcp-core' ); ?></span>
            <span class="jcp-footer-app-badge__name"><?php esc_html_e( 'Google Play', 'jcp-core' ); ?></span>
          </span>
        </a>
      </div>
    </div>

    <div class="jcp-footer-bottom">
      <div class="jcp-container jcp-footer-bottom-inner">
        <nav class="jcp-footer-legal" aria-label="<?php esc_attr_e( 'Legal', 'jcp-core' ); ?>">
          <a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy', 'jcp-core' ); ?></a>
          <span class="jcp-footer-sep" aria-hidden="true">·</span>
          <a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms', 'jcp-core' ); ?></a>
        </nav>
        <div class="jcp-footer-social" role="list">
          <?php foreach ( $social_links as $key => $item ) : ?>
            <a href="<?php echo esc_url( $item['url'] ); ?>" class="jcp-footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $item['label'] ); ?>" role="listitem">
              <?php if ( $key === 'facebook' ) : ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              <?php elseif ( $key === 'x' ) : ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              <?php elseif ( $key === 'instagram' ) : ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.265.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.058 1.645-.07 4.849-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              <?php elseif ( $key === 'tiktok' ) : ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
              <?php elseif ( $key === 'youtube' ) : ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
        <?php if ( ! $directory_mode ) : ?>
        <p class="jcp-footer-powered">
          <?php esc_html_e( 'Powered by', 'jcp-core' ); ?>
          <a href="<?php echo esc_url( $leadsforward_url ); ?>" target="_blank" rel="noopener noreferrer">LeadsForward</a>
        </p>
        <?php endif; ?>
      </div>
    </div>
  </footer>
  <?php endif; ?>
  </div><!-- .jcp-shell -->
  <?php wp_footer(); ?>
</body>
</html>
