<?php
/**
 * Optional personalization — after email + trade, before interactive demo.
 * Business name only (skippable) so the demo and GHL feel personal.
 *
 * @package JCP_Core
 */
?>
<section class="survey-step survey-step--personalize" data-step="1">
  <div class="survey-head">
    <p class="survey-step-progress"><?php esc_html_e( 'Step 2 of 2', 'jcp-core' ); ?></p>
    <div class="survey-eyebrow">
      <span class="survey-eyebrow-icon" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 20h9"/>
          <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
        </svg>
      </span>
      <span><?php esc_html_e( 'Make it yours', 'jcp-core' ); ?></span>
    </div>
    <h1 class="survey-title"><?php esc_html_e( 'Personalize your demo', 'jcp-core' ); ?></h1>
    <p class="survey-subtitle">
      <?php esc_html_e( 'Add your business name and we’ll use it throughout the demo.', 'jcp-core' ); ?>
    </p>
  </div>

  <form class="survey-form survey-form--personalize" autocomplete="on" onsubmit="return false;">
    <div class="survey-field">
      <label for="businessName"><?php esc_html_e( 'Business name', 'jcp-core' ); ?></label>
      <input
        id="businessName"
        type="text"
        class="survey-input"
        name="organization"
        placeholder="<?php esc_attr_e( 'Enter your business name', 'jcp-core' ); ?>"
        autocomplete="organization"
        maxlength="120"
      />
    </div>
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="personalize-continue"><?php esc_html_e( 'Personalize My Demo →', 'jcp-core' ); ?></button>
    <button type="button" class="survey-skip-link" data-action="personalize-skip"><?php esc_html_e( 'Skip personalization', 'jcp-core' ); ?></button>
  </div>
</section>
