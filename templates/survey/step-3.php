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
    <h2 class="survey-title">Last step: your details</h2>
    <p class="survey-subtitle">
      Enter your name and email, then we'll show you a short preview before your demo.
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
          required
        />
      </div>
      <div class="survey-field survey-field-full">
        <label for="email">Email address</label>
        <input
          id="email"
          type="email"
          class="survey-input"
          placeholder="you@company.com"
          autocomplete="email"
          required
        />
      </div>
    </div>
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="launch">Continue to preview</button>
    <p class="survey-consent">By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.</p>
  </div>
</section>
