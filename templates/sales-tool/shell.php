<?php
/**
 * Sales tool app shell markup.
 *
 * @package JCP_Core
 */

$config = function_exists( 'jcp_sales_tool_build_config' )
	? jcp_sales_tool_build_config( (int) get_queried_object_id() )
	: [];
$asset  = trailingslashit( get_template_directory_uri() ) . 'assets/jcp-sales-tool/assets';
$jcp_logo = $asset . '/jcp-logo-dark.png';
$logo   = $jcp_logo;
if ( ! empty( $config['presenter']['logoUrl'] ) ) {
	$logo = (string) $config['presenter']['logoUrl'];
}
?>
<div class="app-shell" id="appShell">
  <aside class="sidebar" aria-label="<?php esc_attr_e( 'Sales call chapters', 'jcp-core' ); ?>">
    <a class="brand" href="#" data-go="0" aria-label="JobCapturePro">
      <img src="<?php echo esc_url( $logo ); ?>" alt="JobCapturePro" />
    </a>
    <nav class="chapters" id="chapterNav"></nav>
    <div class="sidebar-foot">
      <span class="status-dot" aria-hidden="true"></span>
      <span><?php esc_html_e( 'Call workspace saves locally', 'jcp-core' ); ?></span>
    </div>
  </aside>

  <main class="workspace">
    <header class="topbar">
      <a class="present-brand" href="#" data-go="0" aria-label="JobCapturePro">
        <img src="<?php echo esc_url( $jcp_logo ); ?>" alt="JobCapturePro" width="180" height="40" />
      </a>
      <div class="call-meta">
        <label for="prospectName"><?php esc_html_e( 'Prospect', 'jcp-core' ); ?></label>
        <input id="prospectName" autocomplete="organization" placeholder="<?php esc_attr_e( 'Company name', 'jcp-core' ); ?>" />
        <span class="save-state" id="saveState"><?php esc_html_e( 'Saved', 'jcp-core' ); ?></span>
      </div>
      <div class="top-actions">
        <div class="mode-switch" aria-label="<?php esc_attr_e( 'Presentation audience', 'jcp-core' ); ?>">
          <button data-mode="contractor" type="button"><?php esc_html_e( 'Contractor', 'jcp-core' ); ?></button>
          <button data-mode="affiliate" type="button"><?php esc_html_e( 'Affiliate', 'jcp-core' ); ?></button>
          <button data-mode="partner" type="button"><?php esc_html_e( 'Partner', 'jcp-core' ); ?></button>
        </div>
        <button class="quiet-btn" id="customizeBtn" type="button"><?php esc_html_e( 'Customize', 'jcp-core' ); ?></button>
        <?php if ( ! empty( $config['scriptAdminUrl'] ) ) : ?>
        <a class="quiet-btn" id="scriptBtn" href="<?php echo esc_url( (string) $config['scriptAdminUrl'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Call script', 'jcp-core' ); ?></a>
        <?php endif; ?>
        <button class="quiet-btn" id="resetBtn" type="button"><?php esc_html_e( 'Reset', 'jcp-core' ); ?></button>
        <button class="outline-btn" id="presentBtn" type="button"><?php esc_html_e( 'Present', 'jcp-core' ); ?></button>
      </div>
    </header>

    <div class="chapter-stage" id="chapterStage" aria-live="polite"></div>

    <footer class="call-controls">
      <button class="back-btn" id="backBtn" type="button" aria-label="<?php esc_attr_e( 'Previous chapter', 'jcp-core' ); ?>">← <span><?php esc_html_e( 'Back', 'jcp-core' ); ?></span></button>
      <div class="progress-wrap" aria-label="<?php esc_attr_e( 'Call progress', 'jcp-core' ); ?>">
        <span id="progressLabel">01 / 10</span>
        <div class="progress-track"><i id="progressBar"></i></div>
      </div>
      <button class="next-btn" id="nextBtn" type="button"><span><?php esc_html_e( 'Continue', 'jcp-core' ); ?></span> →</button>
    </footer>
  </main>
</div>

<div class="drawer-backdrop" id="drawerBackdrop" hidden></div>
<aside class="customizer" id="customizer" aria-label="<?php esc_attr_e( 'Presentation settings', 'jcp-core' ); ?>" aria-hidden="true">
  <div class="customizer-head">
    <div>
      <span><?php esc_html_e( 'Presentation setup', 'jcp-core' ); ?></span>
      <h2><?php esc_html_e( 'Make it theirs.', 'jcp-core' ); ?></h2>
    </div>
    <button id="closeCustomizer" type="button" aria-label="<?php esc_attr_e( 'Close settings', 'jcp-core' ); ?>">×</button>
  </div>
  <div class="customizer-body">
    <div class="settings-field">
      <label for="settingProspect"><?php esc_html_e( 'Company or partner', 'jcp-core' ); ?></label>
      <input id="settingProspect" type="text" placeholder="<?php esc_attr_e( 'Company name', 'jcp-core' ); ?>" />
    </div>
    <div class="settings-field">
      <label for="settingRep"><?php esc_html_e( 'Presented by', 'jcp-core' ); ?></label>
      <input id="settingRep" type="text" placeholder="<?php esc_attr_e( 'Your name', 'jcp-core' ); ?>" />
    </div>
    <div class="settings-field">
      <label for="settingTrade"><?php esc_html_e( 'Primary trade', 'jcp-core' ); ?></label>
      <input id="settingTrade" type="text" placeholder="<?php esc_attr_e( 'Roofing, HVAC, plumbing…', 'jcp-core' ); ?>" />
    </div>
    <div class="settings-row">
      <div class="settings-field">
        <label for="settingJobs"><?php esc_html_e( 'Jobs / week', 'jcp-core' ); ?></label>
        <input id="settingJobs" type="number" min="1" />
      </div>
      <div class="settings-field">
        <label for="settingLocations"><?php esc_html_e( 'Locations', 'jcp-core' ); ?></label>
        <input id="settingLocations" type="number" min="1" />
      </div>
    </div>
    <div class="settings-field">
      <label for="settingLeadLift"><?php esc_html_e( 'Anonymous case verified lead lift', 'jcp-core' ); ?></label>
      <div class="suffix-input">
        <input id="settingLeadLift" type="number" min="0" step="0.1" placeholder="<?php esc_attr_e( 'Leave blank until verified', 'jcp-core' ); ?>" />
        <span>%</span>
      </div>
      <small><?php esc_html_e( 'Only enter a figure supported by CRM, GBP, or call-tracking data.', 'jcp-core' ); ?></small>
    </div>
    <label class="setting-toggle">
      <input id="settingPricing" type="checkbox" />
      <span><i></i></span>
      <strong><?php esc_html_e( 'Show retail pricing', 'jcp-core' ); ?></strong>
    </label>
    <label class="setting-toggle">
      <input id="settingAcculevel" type="checkbox" />
      <span><i></i></span>
      <strong><?php esc_html_e( 'Show anonymous Maps case study', 'jcp-core' ); ?></strong>
    </label>
    <div class="settings-field">
      <label for="settingAssessmentEmail"><?php esc_html_e( 'Load assessment (coming soon)', 'jcp-core' ); ?></label>
      <input id="settingAssessmentEmail" type="email" placeholder="<?php esc_attr_e( 'Prospect work email', 'jcp-core' ); ?>" disabled />
      <small><?php esc_html_e( 'Will pull Demo Assessment answers by email. Enter fields manually for now.', 'jcp-core' ); ?></small>
    </div>
  </div>
  <div class="customizer-foot">
    <button class="next-btn" id="applySettings" type="button"><span><?php esc_html_e( 'Apply to presentation', 'jcp-core' ); ?></span> →</button>
  </div>
</aside>

<div class="report-modal" id="reportModal" hidden role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Google Maps coverage enlarge', 'jcp-core' ); ?>">
  <button type="button" class="report-modal-backdrop" id="closeReportBackdrop" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>"></button>
  <div class="report-modal-panel">
    <button id="closeReport" type="button" aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>">×</button>
    <img id="reportImage" alt="<?php esc_attr_e( 'Google Maps coverage before and after', 'jcp-core' ); ?>" />
  </div>
</div>

<div class="toast" id="toast" role="status"></div>
