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

  <?php
  /**
   * Demo step-1 proof quotes — kept short & even so the strip stays balanced.
   * Names/roles match sales-deck reviews; copy is trimmed for this surface.
   *
   * @var list<array{name:string,role:string,quote:string}>
   */
  $survey_proof = [
    [
      'name'  => 'Trent Ellison',
      'role'  => 'Home service operator',
      'quote' => 'Easy to use and really smart. Turns completed jobs into useful online content — and the review side is amazing.',
    ],
    [
      'name'  => 'Brian Hardy',
      'role'  => 'Contractor',
      'quote' => 'Awesome — it takes my work site pictures and turns them into a full marketing campaign automatically.',
    ],
    [
      'name'  => 'Heriberto Eddie Roman',
      'role'  => 'Business owner',
      'quote' => 'JobCapturePro has been a game changer for my business — simple capture with real results online.',
    ],
    [
      'name'  => 'Peter Bonk',
      'role'  => 'Marketing agency',
      'quote' => 'One of the easiest wins for our HVAC client. Photos become GBP posts, website content, social, and reviews.',
    ],
  ];
  ?>
  <aside class="survey-proof" aria-label="<?php esc_attr_e( 'What customers say', 'jcp-core' ); ?>">
    <div class="survey-proof-banner">
      <span class="survey-proof-stars" aria-hidden="true">★★★★★</span>
      <p class="survey-proof-banner-text">
        <strong><?php esc_html_e( '5-star reviewed', 'jcp-core' ); ?></strong>
        <span><?php esc_html_e( 'by contractors & agencies using JobCapturePro', 'jcp-core' ); ?></span>
      </p>
    </div>
    <ul class="survey-proof-list">
      <?php foreach ( $survey_proof as $review ) : ?>
        <li class="survey-proof-item">
          <span class="survey-proof-item-stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'jcp-core' ); ?>">★★★★★</span>
          <p class="survey-proof-quote">&ldquo;<?php echo esc_html( (string) ( $review['quote'] ?? '' ) ); ?>&rdquo;</p>
          <p class="survey-proof-by">
            <strong><?php echo esc_html( (string) ( $review['name'] ?? '' ) ); ?></strong>
            <?php if ( ! empty( $review['role'] ) ) : ?>
              <span><?php echo esc_html( (string) $review['role'] ); ?></span>
            <?php endif; ?>
          </p>
        </li>
      <?php endforeach; ?>
    </ul>
  </aside>
</section>
