<?php
/**
 * Survey Step 2: Demo goals (checkboxes)
 *
 * @package JCP_Core
 */
?>
<section class="survey-step" data-step="1">
  <div class="survey-head">
    <div class="survey-eyebrow">Step 2</div>
    <h2 class="survey-title">What should this demo prove?</h2>
    <p class="survey-subtitle">Choose up to two priorities.</p>
  </div>

  <div class="survey-goals" id="surveyGoals">
    <label class="survey-goal">
      <input type="checkbox" value="calls">
      <span>More inbound calls</span>
    </label>
    <label class="survey-goal">
      <input type="checkbox" value="google">
      <span>Better Google visibility</span>
    </label>
    <label class="survey-goal">
      <input type="checkbox" value="reviews">
      <span>More customer reviews</span>
    </label>
    <label class="survey-goal">
      <input type="checkbox" value="busywork">
      <span>Less marketing busywork</span>
    </label>
  </div>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="next">Continue</button>
  </div>
</section>
