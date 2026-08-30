<?php
/**
 * Survey wrapper: single-screen gate → live demo (deck skipped by default).
 * Used when /demo is loaded without ?mode=run (survey view).
 *
 * @package JCP_Core
 */
$logo_url = esc_url( 'https://jobcapturepro.com/wp-content/uploads/2025/11/JobCapturePro-Logo-Dark.png' );
?>
<div class="survey-overlay">
  <button class="survey-close" id="surveyClose" type="button" aria-label="<?php esc_attr_e( 'Close demo', 'jcp-core' ); ?>">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
      <path d="M18 6L6 18M6 6l12 12"/>
    </svg>
  </button>

  <p class="survey-step-indicator" id="surveyStepIndicator" aria-live="polite" hidden>Quick start</p>

  <div class="survey-card survey-card--gate">
    <div class="survey-brand">
      <img src="<?php echo $logo_url; ?>" alt="<?php esc_attr_e( 'JobCapturePro', 'jcp-core' ); ?>" width="180" height="40" />
    </div>

    <?php get_template_part( 'templates/survey/step-1' ); ?>
    <?php
    // Deck remains available via ?deck=1; default path skips it and launches the demo.
    get_template_part( 'templates/survey/deck' );
    get_template_part( 'templates/survey/desktop-handoff' );
    ?>

    <div class="survey-progress is-hidden" id="surveyProgress" hidden>
      <div class="survey-progress-track" aria-hidden="true">
        <span class="survey-progress-fill" id="surveyProgressFill"></span>
      </div>
      <div class="survey-progress-row">
        <span class="survey-progress-label" id="surveyProgressText">Quick start</span>
        <div class="survey-stepper" role="tablist" aria-label="<?php esc_attr_e( 'Survey steps', 'jcp-core' ); ?>">
          <button class="stepper-step is-active" type="button" data-step="0">1</button>
        </div>
      </div>
    </div>
  </div>
</div>
