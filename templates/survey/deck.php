<?php
/**
 * Survey deck: slides shown after Step 3 before launching demo
 *
 * @package JCP_Core
 */
$icon_layout = esc_url( jcp_core_icon( 'layout-list' ) );
$icon_map   = esc_url( jcp_core_icon( 'map-pin' ) );
$icon_social = esc_url( jcp_core_icon( 'message-square' ) );
$icon_dir   = esc_url( jcp_core_icon( 'map' ) );
$icon_camera = esc_url( jcp_core_icon( 'camera' ) );
$icon_shield = esc_url( jcp_core_icon( 'shield-check' ) );
$icon_phone  = esc_url( jcp_core_icon( 'phone-call' ) );
$icon_sparkle = esc_url( jcp_core_icon( 'sparkle' ) );
$icon_tag = esc_url( jcp_core_icon( 'tag' ) );
$icon_rocket = esc_url( jcp_core_icon( 'rocket' ) );
$icon_send = esc_url( jcp_core_icon( 'send' ) );
$icon_star = esc_url( jcp_core_icon( 'star' ) );
$icon_qr = esc_url( jcp_core_icon( 'qr-code' ) );
$icon_play = esc_url( jcp_core_icon( 'play' ) );
$icon_globe = esc_url( jcp_core_icon( 'globe' ) );
$icon_plug = esc_url( jcp_core_icon( 'plug' ) );
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
        <button class="deck-skip" data-action="launch">Skip to demo →</button>
      </div>
    </div>

    <div class="deck-slides" id="deckSlides">
      <article class="deck-slide deck-slide--intro is-active">
        <h2 id="deckSlide1Title">Every completed job should help you win the next one.</h2>
        <p class="deck-lead">
          Capture work once. JobCapturePro turns it into proof on your website, directory, and search so customers choose you faster.
        </p>

        <div class="deck-flow deck-flow--stack" aria-label="Outcomes">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_camera ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More proof builds trust</div>
              <div class="deck-flow-sub">Job photos become verified work customers can see.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_shield ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More trust brings more calls</div>
              <div class="deck-flow-sub">Show real proof where people compare options.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_phone ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More calls book more jobs</div>
              <div class="deck-flow-sub">Win faster without discounting.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--cards deck-slide--channels">
        <h2>One photo publishes everywhere.</h2>
        <p class="deck-lead">
          Snap a quick photo. JobCapturePro writes the story, tags the job, and updates your website, Google Business Profile, social, and directory — with no extra admin.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="What happens from one photo">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_sparkle ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">AI writes the job story</div>
              <div class="deck-flow-sub">Get a clean update you can publish right away.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_tag ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Location + services are tagged</div>
              <div class="deck-flow-sub">Structured proof helps search and customers understand the job.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_rocket ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Proof goes live across channels</div>
              <div class="deck-flow-sub">Website, Google, social, and directory update automatically.</div>
            </div>
          </div>
        </div>
        <div class="deck-tiles" aria-label="Channels">
          <div class="deck-tile">
            <span class="tile-icon">
              <img src="<?php echo $icon_layout; ?>" alt="" width="24" height="24">
            </span>
            <span>Website</span>
          </div>
          <div class="deck-tile">
            <span class="tile-icon">
              <img src="<?php echo $icon_map; ?>" alt="" width="24" height="24">
            </span>
            <span>Google</span>
          </div>
          <div class="deck-tile">
            <span class="tile-icon">
              <img src="<?php echo $icon_social; ?>" alt="" width="24" height="24">
            </span>
            <span>Social</span>
          </div>
          <div class="deck-tile">
            <span class="tile-icon">
              <img src="<?php echo $icon_dir; ?>" alt="" width="24" height="24">
            </span>
            <span>Directory</span>
          </div>
        </div>
        <p class="deck-integrations">
          Already on Housecall Pro, Jobber, ServiceTitan, or CompanyCam? We integrate with the tools your crews already use.
        </p>
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2>Ask for reviews while it still matters.</h2>
        <p class="deck-lead">
          After the job, your crew shows a QR code so the customer can leave a review on the spot — while they are happiest and still standing there.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="Review outcomes">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_send ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Higher response rate</div>
              <div class="deck-flow-sub">Ask in person while the experience is fresh.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_qr ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">QR handoff on site</div>
              <div class="deck-flow-sub">Takes seconds before your tech leaves the job.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_star ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Trusted public feedback</div>
              <div class="deck-flow-sub">More real reviews means more confidence and booked jobs.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--rank">
        <h2>Rank higher in your local market.</h2>
        <p class="deck-lead">
          Verified activity boosts your presence across maps and search results.
        </p>
        <div class="grid-compare">
          <div class="grid-box">
            <div class="grid-title">Local map coverage</div>
            <div class="geo-grid">
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
              <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="grid-caption">More completed jobs = more local coverage.</div>
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
                  <div class="rank-rating"><span class="rank-stars" aria-hidden="true">★★★★★</span> <span>4.9 (500)</span></div>
                  <div class="rank-meta">Rising fast · proof verified</div>
                  <div class="rank-earned">+ visibility after 1 job</div>
                </div>
              </div>
            </div>
            <div class="rank-caption">
              Every completed job increases local rankings and map coverage.
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2 id="deckPersonalTitle">Ready? See one job publish everywhere.</h2>
        <p class="deck-lead" id="deckPersonalLead">
          Walk through the live workflow end to end — one job turning into proof, rankings, and more calls.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="What you'll see next">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_play ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Live job → automated proof</div>
              <div class="deck-flow-sub">Capture once and generate publish-ready proof instantly.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_globe ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Proof → higher rankings</div>
              <div class="deck-flow-sub">Verified activity strengthens visibility across maps and search.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_plug ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Works with your stack</div>
              <div class="deck-flow-sub">Integrates with Housecall Pro, Jobber, ServiceTitan, CompanyCam, and more.</div>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div class="deck-actions">
      <button class="btn-control" data-action="prev" id="deckPrevBtn">← Back</button>
      <button class="btn-control primary" data-action="next" id="deckNextBtn">Next</button>
      <button class="btn-control primary is-hidden" data-action="launch" id="deckLaunchBtn">
        Launch demo →
      </button>
    </div>
  </div>
</section>
