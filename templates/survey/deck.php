<?php
/**
 * Survey deck: visual pitch slides after Step 3, before the live demo.
 *
 * @package JCP_Core
 */

$campaign_uri = trailingslashit( get_template_directory_uri() ) . 'assets/campaign/';
$img_capture  = esc_url( $campaign_uri . 'jcp-campaign-hvac-capture.jpg' );
$img_proof    = esc_url( $campaign_uri . 'jcp-campaign-job-proof.jpg' );
$img_crew     = esc_url( $campaign_uri . 'jcp-campaign-crew-review.jpg' );
$img_owner    = esc_url( $campaign_uri . 'jcp-campaign-face-owner.jpg' );
$img_operator = esc_url( $campaign_uri . 'jcp-campaign-face-operator.jpg' );

$icon_layout = esc_url( jcp_core_icon( 'layout-list' ) );
$icon_map    = esc_url( jcp_core_icon( 'map-pin' ) );
$icon_social = esc_url( jcp_core_icon( 'message-square' ) );
$icon_dir    = esc_url( jcp_core_icon( 'map' ) );
$icon_camera = esc_url( jcp_core_icon( 'camera' ) );
$icon_shield = esc_url( jcp_core_icon( 'shield-check' ) );
$icon_phone  = esc_url( jcp_core_icon( 'phone-call' ) );
$icon_star   = esc_url( jcp_core_icon( 'star' ) );
$icon_qr     = esc_url( jcp_core_icon( 'qr-code' ) );
$icon_send   = esc_url( jcp_core_icon( 'send' ) );
$icon_globe  = esc_url( jcp_core_icon( 'globe' ) );
$icon_check  = esc_url( jcp_core_icon( 'check' ) );
?>
<section class="survey-deck" id="surveyDeck">
  <button type="button" class="deck-skip deck-skip--header" data-action="launch" id="deckSkipHeader" hidden>Skip to demo</button>
  <div class="deck">
    <div class="deck-top">
      <div class="deck-progress">
        <span class="deck-progress-bar" id="deckProgressBar"></span>
      </div>
      <div class="deck-progress-meta">
        <span id="deckProgressText">1 / 5</span>
        <button type="button" class="deck-skip" data-action="launch">Skip to demo →</button>
      </div>
    </div>

    <div class="deck-slides" id="deckSlides">

      <article class="deck-slide deck-slide--visual deck-slide--intro is-active">
        <div class="deck-visual-hero">
          <img
            src="<?php echo $img_crew; ?>"
            alt="Crew reviewing a completed job on site"
            width="720"
            height="480"
            loading="eager"
            decoding="async"
          />
          <div class="deck-visual-hero__fade" aria-hidden="true"></div>
          <div class="deck-visual-hero__chip">
            <img src="<?php echo $icon_camera; ?>" alt="" width="16" height="16" />
            Job just finished
          </div>
        </div>
        <h2 id="deckSlide1Title">Every completed job should help you win the next one.</h2>
        <p class="deck-lead">Capture once. JobCapturePro turns it into proof customers see — so you get more calls without more marketing work.</p>
        <ul class="deck-pills" aria-label="Outcomes">
          <li class="deck-pill">
            <span class="deck-pill__icon"><img src="<?php echo $icon_camera; ?>" alt="" /></span>
            <span>More proof</span>
          </li>
          <li class="deck-pill">
            <span class="deck-pill__icon"><img src="<?php echo $icon_shield; ?>" alt="" /></span>
            <span>More trust</span>
          </li>
          <li class="deck-pill">
            <span class="deck-pill__icon"><img src="<?php echo $icon_phone; ?>" alt="" /></span>
            <span>More calls</span>
          </li>
        </ul>
      </article>

      <article class="deck-slide deck-slide--visual deck-slide--channels">
        <h2>One photo publishes everywhere.</h2>
        <p class="deck-lead">Tap a channel — watch one job photo become live proof.</p>

        <div class="deck-publish" data-deck-publish>
          <button type="button" class="deck-publish__photo is-active" data-publish-source aria-pressed="true">
            <img src="<?php echo $img_capture; ?>" alt="Technician photographing a completed HVAC job" width="400" height="300" loading="lazy" />
            <span class="deck-publish__photo-label">
              <img src="<?php echo $icon_camera; ?>" alt="" width="14" height="14" />
              Job photo
            </span>
          </button>

          <div class="deck-tiles" aria-label="Where it publishes">
            <button type="button" class="deck-tile" data-channel="website" aria-pressed="false">
              <span class="tile-icon"><img src="<?php echo $icon_layout; ?>" alt="" width="22" height="22" /></span>
              <span class="tile-label">Website</span>
              <span class="tile-status">Publish</span>
            </button>
            <button type="button" class="deck-tile" data-channel="google" aria-pressed="false">
              <span class="tile-icon"><img src="<?php echo $icon_map; ?>" alt="" width="22" height="22" /></span>
              <span class="tile-label">Google</span>
              <span class="tile-status">Publish</span>
            </button>
            <button type="button" class="deck-tile" data-channel="social" aria-pressed="false">
              <span class="tile-icon"><img src="<?php echo $icon_social; ?>" alt="" width="22" height="22" /></span>
              <span class="tile-label">Social</span>
              <span class="tile-status">Publish</span>
            </button>
            <button type="button" class="deck-tile" data-channel="directory" aria-pressed="false">
              <span class="tile-icon"><img src="<?php echo $icon_dir; ?>" alt="" width="22" height="22" /></span>
              <span class="tile-label">Directory</span>
              <span class="tile-status">Publish</span>
            </button>
          </div>

          <div class="deck-publish__preview" data-publish-preview hidden>
            <img src="<?php echo $img_proof; ?>" alt="Finished job as marketing proof" width="480" height="320" loading="lazy" />
            <p data-publish-preview-label>Live on your website</p>
          </div>
        </div>

        <p class="deck-integrations">Works with Housecall Pro, Jobber, ServiceTitan, CompanyCam, and more.</p>
      </article>

      <article class="deck-slide deck-slide--visual deck-slide--reviews">
        <h2>Ask for reviews while it still matters.</h2>
        <p class="deck-lead">On-site QR. Customer is still happy. Review lands before your truck leaves.</p>

        <div class="deck-review-stage">
          <div class="deck-review-stage__photo">
            <img src="<?php echo $img_owner; ?>" alt="Owner on site after a completed job" width="480" height="360" loading="lazy" />
          </div>
          <div class="deck-review-card" aria-hidden="true">
            <div class="deck-review-card__qr">
              <img src="<?php echo $icon_qr; ?>" alt="" width="48" height="48" />
              <span>Scan to review</span>
            </div>
            <div class="deck-review-card__stars">
              <img src="<?php echo $icon_star; ?>" alt="" /><img src="<?php echo $icon_star; ?>" alt="" /><img src="<?php echo $icon_star; ?>" alt="" /><img src="<?php echo $icon_star; ?>" alt="" /><img src="<?php echo $icon_star; ?>" alt="" />
            </div>
            <p>“Tech was on time and cleaned up. 5 stars.”</p>
          </div>
        </div>

        <ul class="deck-pills deck-pills--tight" aria-label="Review benefits">
          <li class="deck-pill"><span class="deck-pill__icon"><img src="<?php echo $icon_send; ?>" alt="" /></span><span>Ask in person</span></li>
          <li class="deck-pill"><span class="deck-pill__icon"><img src="<?php echo $icon_qr; ?>" alt="" /></span><span>QR on site</span></li>
          <li class="deck-pill"><span class="deck-pill__icon"><img src="<?php echo $icon_star; ?>" alt="" /></span><span>Public proof</span></li>
        </ul>
      </article>

      <article class="deck-slide deck-slide--visual deck-slide--rank">
        <h2>Rank higher in your local market.</h2>
        <p class="deck-lead">Verified job activity strengthens your presence on maps and search.</p>
        <div class="grid-compare">
          <div class="grid-box">
            <div class="grid-title">Local map coverage</div>
            <div class="geo-grid geo-grid-animate" aria-hidden="true">
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="grid-caption">More completed jobs → more local coverage.</div>
          </div>
          <div class="grid-box rank-box" id="surveyRankBox">
            <div class="grid-title">Local map pack</div>
            <div class="rank-list" id="surveyRankList">
              <div class="rank-item is-top" id="surveyRankTop">
                <span class="rank-num" id="surveyRankNumTop">1</span>
                <div class="rank-content">
                  <span class="rank-name">Summit Roofing</span>
                  <div class="rank-rating"><span class="rank-stars" aria-hidden="true">★★★★</span> <span>4.0 (12)</span></div>
                  <div class="rank-meta">Active this month · 3 jobs</div>
                </div>
              </div>
              <div class="rank-item is-mid" id="surveyRankMid">
                <span class="rank-num" id="surveyRankNumMid">2</span>
                <div class="rank-content">
                  <span class="rank-name">Lakeview Plumbing</span>
                  <div class="rank-rating"><span class="rank-stars" aria-hidden="true">★★★</span> <span>3.5 (8)</span></div>
                  <div class="rank-meta">Active last month · 1 job</div>
                </div>
              </div>
              <div class="rank-item rank-you" id="surveyRankYou">
                <span class="rank-num" id="surveyRankNumYou">3</span>
                <div class="rank-content">
                  <span class="rank-name" id="surveyRankName">Your Business</span>
                  <div class="rank-rating"><span class="rank-stars" aria-hidden="true">★★★★★</span> <span>4.9 (48)</span></div>
                  <div class="rank-meta">Rising fast · proof verified</div>
                  <div class="rank-earned">+ visibility after 1 job</div>
                </div>
              </div>
            </div>
            <div class="rank-caption">Watch your business climb as proof publishes.</div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--visual deck-slide--launch">
        <div class="deck-launch-faces" aria-hidden="true">
          <img src="<?php echo $img_operator; ?>" alt="" width="64" height="64" />
          <img src="<?php echo $img_owner; ?>" alt="" width="64" height="64" />
          <img src="<?php echo $img_crew; ?>" alt="" width="64" height="64" />
        </div>
        <h2 id="deckPersonalTitle">Ready? See one job publish everywhere.</h2>
        <p class="deck-lead" id="deckPersonalLead">
          In the live demo you’ll capture a job, publish proof, and request a review — end to end.
        </p>
        <ul class="deck-checklist" aria-label="What you’ll do">
          <li>
            <span class="deck-checklist__icon"><img src="<?php echo $icon_check; ?>" alt="" /></span>
            <span>Capture a job photo</span>
          </li>
          <li>
            <span class="deck-checklist__icon"><img src="<?php echo $icon_globe; ?>" alt="" /></span>
            <span>Publish to website, Google, social &amp; directory</span>
          </li>
          <li>
            <span class="deck-checklist__icon"><img src="<?php echo $icon_star; ?>" alt="" /></span>
            <span>Trigger an on-site review ask</span>
          </li>
        </ul>
        <p class="deck-launch-hint">About 2 minutes · Personalized to your business</p>
      </article>
    </div>

    <div class="deck-actions">
      <button type="button" class="btn-control" data-action="prev" id="deckPrevBtn">← Back</button>
      <button type="button" class="btn-control primary" data-action="next" id="deckNextBtn">Next</button>
      <button type="button" class="btn-control primary is-hidden" data-action="launch" id="deckLaunchBtn">
        Launch demo →
      </button>
    </div>
  </div>
</section>
