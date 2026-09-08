<?php
/**
 * Survey gate (single screen): work email + trade → launch demo.
 * Business name / phone / name / referral collected later (fields kept hidden for payload stability).
 *
 * @package JCP_Core
 */
$demo_headline = 'See JobCapturePro on Your Business';
$demo_subhead  = 'Enter your work email and trade. We’ll personalize the demo for you.';
$demo_btn      = 'See My Demo →';
$demo_micro    = 'Free · About 2 minutes · No credit card';

/**
 * Featured proof uses the canonical Peter Bonk quote (sales-tool defaults).
 * Secondary cards use the other approved survey gate quotes, shown in full.
 *
 * @var array{name:string,role:string,quote:string} $survey_featured
 * @var list<array{name:string,role:string,quote:string}> $survey_proof
 */
$survey_featured = [
	'name'  => 'Peter Bonk',
	'role'  => 'Marketing agency',
	'quote' => 'One of the easiest marketing wins we\'ve had for an HVAC client. Techs already take photos. Now those become GBP updates, website content, social posts, and an on-site review ask. The review flow alone has been worth it.',
];
$survey_proof = [
	[
		'name'  => 'Trent Ellison',
		'role'  => 'Home service operator',
		'quote' => 'Easy to use and really smart. Makes it super simple to turn completed work into useful online content, and the review side is amazing.',
	],
	[
		'name'  => 'Brian Hardy',
		'role'  => 'Contractor',
		'quote' => 'Awesome. It takes my work site pictures and turns them into a marketing campaign.',
	],
];
?>
<section class="survey-step active" data-step="0">
  <div class="survey-head">
    <div class="survey-eyebrow">
      <span class="survey-eyebrow-icon" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
          <rect x="5" y="11" width="14" height="10" rx="2"/>
          <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
        </svg>
      </span>
      <span><?php esc_html_e( 'Personalized demo', 'jcp-core' ); ?></span>
    </div>
    <h1 class="survey-title"><?php echo esc_html( $demo_headline ); ?></h1>
    <p class="survey-subtitle">
      <?php echo esc_html( $demo_subhead ); ?>
    </p>
  </div>

  <form class="survey-form survey-form--gate" autocomplete="on">
    <?php
    $business_type_options = function_exists( 'jcp_core_business_type_flat_options' )
      ? jcp_core_business_type_flat_options()
      : [];
    ?>
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

    <div class="survey-field survey-combobox">
      <label for="nicheSearch">Business type <span class="survey-required">*</span></label>
      <div class="survey-combobox__control">
        <input
          id="nicheSearch"
          type="text"
          class="survey-input"
          role="combobox"
          aria-autocomplete="list"
          aria-expanded="false"
          aria-controls="nicheListbox"
          aria-haspopup="listbox"
          placeholder="<?php esc_attr_e( 'Start typing your trade…', 'jcp-core' ); ?>"
          autocomplete="off"
          maxlength="120"
          required
        />
        <input type="hidden" id="niche" value="" />
        <input type="hidden" id="nicheOther" value="" />
        <ul
          id="nicheListbox"
          class="survey-combobox__list"
          role="listbox"
          hidden
          aria-label="<?php esc_attr_e( 'Business type suggestions', 'jcp-core' ); ?>"
        ></ul>
      </div>
      <script type="application/json" id="jcpBusinessTypeOptions"><?php echo wp_json_encode( $business_type_options ); ?></script>
    </div>

    <?php /* Name / phone collected later (personalization + post-value). Kept for payload stability. */ ?>
    <input type="hidden" id="firstName" value="" autocomplete="given-name" />
    <input type="hidden" id="lastName" value="" autocomplete="family-name" />
    <input type="hidden" id="phone" value="" autocomplete="tel" />
    <input type="hidden" id="referralSource" value="" />
    <input type="hidden" id="referralSourceOther" value="" />
  </form>

  <div class="survey-actions-row">
    <button type="button" class="survey-btn" data-action="launch"><?php echo esc_html( $demo_btn ); ?></button>
    <p class="survey-microcopy"><?php echo esc_html( $demo_micro ); ?></p>
    <p class="survey-consent">By continuing you agree to receive the demo and relevant updates by email. Unsubscribe anytime.</p>
  </div>

  <aside class="survey-proof survey-proof--compact" aria-label="<?php esc_attr_e( 'What customers say', 'jcp-core' ); ?>">
    <div class="survey-proof-banner">
      <span class="survey-proof-stars" aria-hidden="true">★★★★★</span>
      <p class="survey-proof-banner-text">
        <strong><?php esc_html_e( '5-star reviewed', 'jcp-core' ); ?></strong>
        <span><?php esc_html_e( 'by contractors & agencies using JobCapturePro', 'jcp-core' ); ?></span>
      </p>
    </div>

    <figure class="survey-proof-featured">
      <span class="survey-proof-item-stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'jcp-core' ); ?>">★★★★★</span>
      <blockquote class="survey-proof-featured-quote">
        <p>&ldquo;<?php echo esc_html( (string) ( $survey_featured['quote'] ?? '' ) ); ?>&rdquo;</p>
      </blockquote>
      <figcaption class="survey-proof-by">
        <strong><?php echo esc_html( (string) ( $survey_featured['name'] ?? '' ) ); ?></strong>
        <?php if ( ! empty( $survey_featured['role'] ) ) : ?>
          <span><?php echo esc_html( (string) $survey_featured['role'] ); ?></span>
        <?php endif; ?>
      </figcaption>
    </figure>

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
