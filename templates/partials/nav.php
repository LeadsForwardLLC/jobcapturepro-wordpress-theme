<?php
/**
 * Global Navigation Partial
 * Used across all pages - links are dynamic based on current page.
 * Directory Mode: same component, contextual nav/CTAs for /directory and /company.
 *
 * @package JCP_Core
 */
$pages = jcp_core_get_page_detection();
$is_directory = $pages['is_directory'];
$is_company = $pages['is_company'];
$is_demo = $pages['is_demo'];
$is_home = $pages['is_home'];
$directory_mode = function_exists( 'jcp_is_directory_mode' ) && jcp_is_directory_mode();
$home_how = $is_home ? '#how-it-works' : esc_url( home_url( '/#how-it-works' ) );
$home_features = $is_home ? '#features' : esc_url( home_url( '/#features' ) );
$home_who = $is_home ? '#who-its-for' : esc_url( home_url( '/#who-its-for' ) );
$dir_url = home_url( '/directory' );
$industries_url = home_url( '/industries/' );
$dir_search = $dir_url . '/#search';
$dir_how = $dir_url . '/#how-it-works';
$dir_trust = $dir_url . '/#trust';
?><header class="directory-header" id="jcpGlobalHeader">
  <div class="header-brand">
    <a href="<?php echo $directory_mode ? esc_url( $dir_url ) : esc_url( home_url( '/' ) ); ?>" class="brand-link">
      <img
        src="https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png"
        alt="JobCapturePro"
        class="logo-image"
        width="180"
        height="40"
      />
    </a>
    <?php if ( $directory_mode ) : ?>
      <span class="demo-indicator" aria-hidden="true"><?php echo esc_html__( 'Directory', 'jcp-core' ); ?></span>
    <?php else : ?>
      <span class="demo-indicator is-hidden" id="jcpHeaderIndicator"><?php echo esc_html( $is_company ? 'Contractor Profile (coming soon)' : ( $is_directory ? 'Directory (coming soon)' : ( $is_demo ? 'Interactive Demo' : 'Live Demo' ) ) ); ?></span>
    <?php endif; ?>
  </div>

  <nav class="header-nav" id="headerNav" aria-label="<?php esc_attr_e( 'Main navigation', 'jcp-core' ); ?>">
    <?php if ( $is_demo ) : ?>
      <a href="<?php echo $home_how; ?>" class="nav-link" data-home-anchor="#how-it-works">How it works</a>
      <a href="<?php echo $home_features; ?>" class="nav-link" data-home-anchor="#features">Features</a>
      <a href="<?php echo $home_who; ?>" class="nav-link" data-home-anchor="#who-its-for">Who it's for</a>
      <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="nav-link" data-page="pricing">Pricing</a>
    <?php elseif ( $directory_mode ) : ?>
      <a href="<?php echo esc_url( $dir_search ); ?>" class="nav-link" data-page="directory"><?php esc_html_e( 'Find contractors', 'jcp-core' ); ?></a>
      <a href="<?php echo esc_url( $dir_how ); ?>" class="nav-link" data-home-anchor="#how-it-works"><?php esc_html_e( 'How rankings work', 'jcp-core' ); ?></a>
      <a href="<?php echo esc_url( $dir_trust ); ?>" class="nav-link"><?php esc_html_e( 'Trust & verification', 'jcp-core' ); ?></a>
    <?php else : ?>
      <a href="<?php echo $home_how; ?>" class="nav-link" data-home-anchor="#how-it-works">How it works</a>
      <a href="<?php echo $home_features; ?>" class="nav-link" data-home-anchor="#features">Features</a>
      <a href="<?php echo esc_url( $industries_url ); ?>" class="nav-link" data-page="industries"><?php esc_html_e( 'By Trade', 'jcp-core' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="nav-link" data-page="pricing">Pricing</a>
      <div class="nav-dropdown" id="navResourcesDropdown">
        <button type="button" class="nav-dropdown-trigger nav-link" id="navResourcesTrigger" aria-haspopup="true" aria-expanded="false" aria-controls="navResourcesMenu">Resources <svg class="nav-dropdown-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
        <div class="nav-dropdown-menu" id="navResourcesMenu" role="menu" aria-labelledby="navResourcesTrigger" hidden>
          <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="nav-dropdown-item nav-link" role="menuitem" data-page="blog">Blog</a>
          <a href="<?php echo esc_url( home_url( '/help' ) ); ?>" class="nav-dropdown-item nav-link" role="menuitem" data-page="help">Help Center</a>
          <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="nav-dropdown-item nav-link" role="menuitem" data-page="contact">Contact</a>
          <a href="<?php echo esc_url( home_url( '/referral-program' ) ); ?>" class="nav-dropdown-item nav-link" role="menuitem" data-page="referral-program">Referral Program</a>
        </div>
      </div>
    <?php endif; ?>
  </nav>

  <div class="header-actions">
    <?php if ( $is_demo ) : ?>
      <button class="btn btn-secondary" id="btnReset">↺ Reset</button>
      <button class="btn btn-secondary is-hidden" id="btnViewDirectory" type="button">View Demo Directory →</button>
      <button class="btn btn-primary" id="btnNext">Run Guided Demo →</button>
    <?php elseif ( $directory_mode ) : ?>
      <?php
      $secondary_label = __( 'Are you a contractor?', 'jcp-core' );
      $secondary_url   = home_url( '/' );
      $primary_label   = __( 'Find a contractor', 'jcp-core' );
      $primary_url     = $dir_search;
      ?>
      <a href="<?php echo esc_url( $secondary_url ); ?>" class="btn btn-secondary" id="dynamicBackBtn">
        <span><?php echo esc_html( $secondary_label ); ?></span>
        <svg id="dynamicBackIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M5 12h14M13 5l7 7-7 7"/>
        </svg>
      </a>
      <a href="<?php echo esc_url( $primary_url ); ?>" class="btn btn-primary"><?php echo esc_html( $primary_label ); ?></a>
    <?php else : ?>
      <?php
      $secondary_label = 'Online Demo';
      $secondary_url   = home_url( '/demo' );
      $primary_label   = 'Get Started';
      $primary_url     = function_exists( 'jcp_core_onboarding_app_url' ) && function_exists( 'jcp_core_onboarding_utm_defaults' )
        ? jcp_core_onboarding_app_url( jcp_core_onboarding_utm_defaults( 'nav_get_started' ) )
        : ( function_exists( 'jcp_core_onboarding_app_url' ) ? jcp_core_onboarding_app_url() : home_url( '/demo' ) );
      ?>
      <a href="<?php echo esc_url( $secondary_url ); ?>" class="btn btn-secondary" id="dynamicBackBtn">
        <span><?php echo esc_html( $secondary_label ); ?></span>
        <svg id="dynamicBackIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M5 12h14M13 5l7 7-7 7"/>
        </svg>
      </a>
      <a href="<?php echo esc_url( $primary_url ); ?>" class="btn btn-primary"><?php echo esc_html( $primary_label ); ?></a>
    <?php endif; ?>
  </div>

  <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
    <span class="menu-icon">
      <span></span>
      <span></span>
      <span></span>
    </span>
  </button>
</header>

<div class="mobile-menu-overlay" id="mobileMenuOverlay">
  <div class="mobile-menu-content">
    <div class="mobile-menu-header">
      <div class="mobile-menu-title">
        <img
          src="https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png"
          alt="JobCapturePro"
          class="mobile-logo"
          width="160"
          height="36"
        />
        <?php if ( $directory_mode ) : ?>
          <span class="mobile-directory-badge" aria-hidden="true"><?php echo esc_html__( 'Directory', 'jcp-core' ); ?></span>
        <?php else : ?>
          <span class="mobile-directory-badge is-hidden" id="jcpMobileBadge"><?php echo esc_html( $is_company ? 'Contractor Profile (coming soon)' : ( $is_directory ? 'Directory (coming soon)' : ( $is_demo ? 'Interactive Demo' : 'Live Demo' ) ) ); ?></span>
        <?php endif; ?>
      </div>
      <button class="mobile-menu-close" id="mobileMenuClose" aria-label="<?php esc_attr_e( 'Close menu', 'jcp-core' ); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <div class="mobile-menu-actions" id="mobileMenuActionsTop">
      <?php if ( $is_demo ) : ?>
        <button type="button" class="mobile-btn mobile-btn-secondary" id="mobileBtnReset">↺ Reset</button>
        <button type="button" class="mobile-btn mobile-btn-primary" id="mobileBtnNext">Run Guided Demo →</button>
      <?php elseif ( $directory_mode ) : ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-btn mobile-btn-secondary">
          <span><?php echo esc_html__( 'Are you a contractor?', 'jcp-core' ); ?></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
        <a href="<?php echo esc_url( $dir_search ); ?>" class="mobile-btn mobile-btn-primary"><?php echo esc_html__( 'Find a contractor', 'jcp-core' ); ?></a>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/demo' ) ); ?>" class="mobile-btn mobile-btn-secondary">
          <span><?php echo esc_html( $secondary_label ); ?></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
        <a href="<?php echo esc_url( $primary_url ); ?>" class="mobile-btn mobile-btn-primary"><?php echo esc_html( $primary_label ); ?></a>
      <?php endif; ?>
    </div>

    <nav class="mobile-nav" id="mobileNav" aria-label="<?php esc_attr_e( 'Mobile menu', 'jcp-core' ); ?>">
      <?php if ( $is_demo ) : ?>
        <a href="<?php echo $home_how; ?>" class="mobile-nav-link" data-home-anchor="#how-it-works">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polygon points="10 8 16 12 10 16 10 8"></polygon>
          </svg>
          <span>How it works</span>
        </a>
        <a href="<?php echo $home_features; ?>" class="mobile-nav-link" data-home-anchor="#features">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
          <span>Features</span>
        </a>
        <a href="<?php echo $home_who; ?>" class="mobile-nav-link" data-home-anchor="#who-its-for">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
          <span>Who it's for</span>
        </a>
        <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="mobile-nav-link" data-page="pricing">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="2" x2="12" y2="22"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
          <span>Pricing</span>
        </a>
      <?php elseif ( $directory_mode ) : ?>
        <a href="<?php echo esc_url( $dir_search ); ?>" class="mobile-nav-link" data-page="directory">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
          <span><?php esc_html_e( 'Find contractors', 'jcp-core' ); ?></span>
        </a>
        <a href="<?php echo esc_url( $dir_how ); ?>" class="mobile-nav-link" data-home-anchor="#how-it-works">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polygon points="10 8 16 12 10 16 10 8"></polygon>
          </svg>
          <span><?php esc_html_e( 'How rankings work', 'jcp-core' ); ?></span>
        </a>
        <a href="<?php echo esc_url( $dir_trust ); ?>" class="mobile-nav-link">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <path d="m9 12 2 2 4-4"></path>
          </svg>
          <span><?php esc_html_e( 'Trust & verification', 'jcp-core' ); ?></span>
        </a>
      <?php else : ?>
        <a href="<?php echo $home_how; ?>" class="mobile-nav-link" data-home-anchor="#how-it-works">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polygon points="10 8 16 12 10 16 10 8"></polygon>
          </svg>
          <span>How it works</span>
        </a>
        <a href="<?php echo $home_features; ?>" class="mobile-nav-link" data-home-anchor="#features">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
          <span>Features</span>
        </a>
        <a href="<?php echo esc_url( $industries_url ); ?>" class="mobile-nav-link" data-page="industries">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 20h20"></path>
            <path d="M5 20V10l7-6 7 6v10"></path>
            <path d="M9 20v-6h6v6"></path>
          </svg>
          <span><?php esc_html_e( 'By Trade', 'jcp-core' ); ?></span>
        </a>
        <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="mobile-nav-link" data-page="pricing">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="2" x2="12" y2="22"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
          <span>Pricing</span>
        </a>
        <details class="mobile-nav-resources" id="mobileNavResources">
          <summary class="mobile-nav-resources-summary" aria-expanded="false" aria-controls="mobileNavResourcesList">Resources</summary>
          <div class="mobile-nav-resources-list" id="mobileNavResourcesList">
            <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="mobile-nav-link" data-page="blog">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                <line x1="8" y1="6" x2="16" y2="6"></line>
                <line x1="8" y1="10" x2="16" y2="10"></line>
              </svg>
              <span>Blog</span>
            </a>
            <a href="<?php echo esc_url( home_url( '/help' ) ); ?>" class="mobile-nav-link" data-page="help">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
              <span><?php esc_html_e( 'Help Center', 'jcp-core' ); ?></span>
            </a>
            <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="mobile-nav-link" data-page="contact">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <span>Contact</span>
            </a>
            <a href="<?php echo esc_url( home_url( '/referral-program' ) ); ?>" class="mobile-nav-link" data-page="referral-program">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
              <span>Referral Program</span>
            </a>
          </div>
        </details>
      <?php endif; ?>
    </nav>
  </div>
</div>
