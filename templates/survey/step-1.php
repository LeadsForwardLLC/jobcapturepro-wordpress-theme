<?php
/**
 * Survey gate (single screen): trade + work email → launch demo.
 * Business name + first name are optional personalization.
 *
 * @package JCP_Core
 */
$demo_headline = 'See JobCapturePro on your business';
$demo_subhead  = 'Your trade + work email. Personalized demo in about 2 minutes — no credit card.';
$demo_btn      = 'See my demo →';
?>
<section class="survey-step active" data-step="0">
  <div class="survey-head">
    <div class="survey-eyebrow">Online Demo</div>
    <h1 class="survey-title"><?php echo esc_html( $demo_headline ); ?></h1>
    <p class="survey-subtitle">
      <?php echo esc_html( $demo_subhead ); ?>
    </p>
  </div>

  <form class="survey-form survey-form--gate" autocomplete="on">
    <div class="survey-field">
      <label for="niche">Business type <span class="survey-required">*</span></label>
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

    <div class="survey-field">
      <label for="email">Work email <span class="survey-required">*</span></label>
      <input
        id="email"
        type="email"
        class="survey-input"
        placeholder="you@company.com"
        autocomplete="email"
        required
      />
    </div>

    <div class="survey-grid-2 survey-gate-optional">
      <div class="survey-field">
        <label for="businessName">Business name <span class="survey-optional">(optional)</span></label>
        <input
          id="businessName"
          type="text"
          class="survey-input"
          placeholder="Summit Plumbing"
          autocomplete="organization"
        />
      </div>
      <div class="survey-field">
        <label for="firstName">First name <span class="survey-optional">(optional)</span></label>
        <input
          id="firstName"
          type="text"
          class="survey-input"
          placeholder="Alex"
          autocomplete="given-name"
        />
      </div>
    </div>

    <?php /* Kept in DOM (hidden) so existing JS / CRM payloads stay stable. */ ?>
    <input type="hidden" id="lastName" value="" autocomplete="family-name" />
    <input type="hidden" id="phone" value="" autocomplete="tel" />
    <input type="hidden" id="referralSource" value="" />
    <input type="hidden" id="referralSourceOther" value="" />
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="launch"><?php echo esc_html( $demo_btn ); ?></button>
    <p class="survey-consent">By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.</p>
  </div>

  <?php
  /**
   * Compact proof strip for the single-screen gate.
   *
   * @var list<array{name:string,role:string,quote:string}>
   */
  $survey_proof = [
    [
      'name'  => 'Trent Ellison',
      'role'  => 'Home service operator',
      'quote' => 'Turns completed jobs into useful online content — and the review side is amazing.',
    ],
    [
      'name'  => 'Brian Hardy',
      'role'  => 'Contractor',
      'quote' => 'Takes my work site pictures and turns them into a full marketing campaign automatically.',
    ],
    [
      'name'  => 'Peter Bonk',
      'role'  => 'Marketing agency',
      'quote' => 'Photos become GBP posts, website content, social, and reviews — easiest win for our HVAC client.',
    ],
  ];
  ?>
  <aside class="survey-proof survey-proof--compact" aria-label="<?php esc_attr_e( 'What customers say', 'jcp-core' ); ?>">
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
