<?php
/**
 * Optional business personalization — after email + trade, before interactive demo.
 * Feels like configuring the product, not a second lead form.
 *
 * @package JCP_Core
 */
?>
<section class="survey-step survey-step--personalize" data-step="1">
  <div class="survey-head">
    <div class="survey-eyebrow">
      <span class="survey-eyebrow-icon" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 20h9"/>
          <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
        </svg>
      </span>
      <span><?php esc_html_e( 'Make it yours', 'jcp-core' ); ?></span>
    </div>
    <h1 class="survey-title"><?php esc_html_e( 'What should we call your business?', 'jcp-core' ); ?></h1>
    <p class="survey-subtitle">
      <?php esc_html_e( 'Optional — we’ll use this name in your personalized demo.', 'jcp-core' ); ?>
    </p>
  </div>

  <form class="survey-form survey-form--personalize" autocomplete="organization" onsubmit="return false;">
    <div class="survey-field">
      <label for="businessName"><?php esc_html_e( 'Business name', 'jcp-core' ); ?></label>
      <input
        id="businessName"
        type="text"
        class="survey-input"
        name="organization"
        placeholder="Summit Plumbing"
        autocomplete="organization"
        maxlength="120"
      />
    </div>
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="personalize-continue"><?php esc_html_e( 'Use This Business →', 'jcp-core' ); ?></button>
    <button type="button" class="survey-skip-link" data-action="personalize-skip"><?php esc_html_e( 'Skip', 'jcp-core' ); ?></button>
  </div>
</section>
