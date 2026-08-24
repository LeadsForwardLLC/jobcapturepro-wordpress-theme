<?php
/**
 * Survey Step 3: First name + Last name + Email
 *
 * @package JCP_Core
 */
?>
<section class="survey-step" data-step="2">
  <div class="survey-head">
    <div class="survey-eyebrow">Step 3</div>
    <h2 class="survey-title">Where should we send your demo?</h2>
    <p class="survey-subtitle">
      We’ll personalize the walkthrough to your business. Takes about a minute — then you see the demo.
    </p>
  </div>

  <form class="survey-form" autocomplete="off">
    <div class="survey-grid-2">
      <div class="survey-field">
        <label for="firstName">First name</label>
        <input
          id="firstName"
          type="text"
          class="survey-input"
          placeholder="John"
          autocomplete="given-name"
          required
        />
      </div>
      <div class="survey-field">
        <label for="lastName">Last name</label>
        <input
          id="lastName"
          type="text"
          class="survey-input"
          placeholder="Smith"
          autocomplete="family-name"
          required
        />
      </div>
      <div class="survey-field survey-field-full">
        <label for="email">Work email</label>
        <input
          id="email"
          type="email"
          class="survey-input"
          placeholder="you@company.com"
          autocomplete="email"
          required
        />
      </div>
      <div class="survey-field survey-field-full">
        <label for="phone">Mobile phone</label>
        <input
          id="phone"
          type="tel"
          class="survey-input"
          placeholder="(555) 555-5555"
          autocomplete="tel"
          inputmode="tel"
          required
        />
        <p class="survey-field-hint">So we can follow up if you want help getting set up. No spam.</p>
      </div>
      <div class="survey-field survey-field-full">
        <label for="referralSource">How did you hear about us?</label>
        <select id="referralSource" class="survey-input" required>
          <?php
          if ( function_exists( 'jcp_core_render_referral_source_select_options' ) ) {
              jcp_core_render_referral_source_select_options( true );
          }
          ?>
        </select>
      </div>
      <div class="survey-field survey-field-full" id="referralSourceOtherWrap" hidden>
        <label for="referralSourceOther">Please specify</label>
        <input
          id="referralSourceOther"
          type="text"
          class="survey-input"
          placeholder="Tell us how you found JobCapturePro"
          maxlength="120"
          autocomplete="off"
        />
      </div>
    </div>
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="launch">Show my demo</button>
    <p class="survey-consent">By continuing you agree to receive the demo and relevant updates by email or text. Unsubscribe anytime.</p>
  </div>
</section>

