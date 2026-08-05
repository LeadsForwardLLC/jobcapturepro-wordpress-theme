<?php
/**
 * Survey Step 1: Business name + Business type
 *
 * @package JCP_Core
 */
$demo_headline = 'See a live demo built for your business';
$demo_subhead  = 'Just a few details so the demo reflects real jobs and real outcomes.';
$demo_btn      = 'Next step';
?>
<section class="survey-step active" data-step="0">
  <div class="survey-head">
    <div class="survey-eyebrow">Online Demo</div>
    <h1 class="survey-title"><?php echo esc_html( $demo_headline ); ?></h1>
    <p class="survey-subtitle">
      <?php echo esc_html( $demo_subhead ); ?>
    </p>
  </div>

  <form class="survey-form" autocomplete="off">
    <div class="survey-field">
      <label for="businessName">Business name</label>
      <input
        id="businessName"
        type="text"
        class="survey-input"
        placeholder="Summit Plumbing"
        required
      />
    </div>

    <div class="survey-field">
      <label for="niche">Business type</label>
      <select id="niche" class="survey-input" required>
        <?php
        if ( function_exists( 'jcp_core_render_business_type_select_options' ) ) {
            jcp_core_render_business_type_select_options( true );
        }
        ?>
      </select>
    </div>

    <div class="survey-field survey-field--niche-other" id="nicheOtherWrap" hidden>
      <label for="nicheOther">Describe your business type</label>
      <input
        id="nicheOther"
        type="text"
        class="survey-input"
        placeholder="e.g. Mobile detailing"
        maxlength="120"
        autocomplete="off"
      />
    </div>
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="next"><?php echo esc_html( $demo_btn ); ?></button>
  </div>
</section>
