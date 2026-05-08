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
$icon_repeat = esc_url( jcp_core_icon( 'repeat' ) );
$icon_calendar = esc_url( jcp_core_icon( 'calendar-check' ) );
$icon_clipboard = esc_url( jcp_core_icon( 'clipboard-check' ) );
$icon_eye = esc_url( jcp_core_icon( 'eye' ) );
$icon_zap = esc_url( jcp_core_icon( 'zap' ) );
$icon_play = esc_url( jcp_core_icon( 'play' ) );
$icon_globe = esc_url( jcp_core_icon( 'globe' ) );
?>
<section class="survey-deck" id="surveyDeck">
  <div class="deck">
    <div class="deck-top">
      <div class="deck-progress">
        <span class="deck-progress-bar" id="deckProgressBar"></span>
      </div>
      <div class="deck-progress-meta">
        <span id="deckProgressText">1 / 8</span>
        <button class="deck-skip" data-action="launch">Skip to demo →</button>
      </div>
    </div>

    <div class="deck-slides" id="deckSlides">
      <article class="deck-slide deck-slide--intro is-active">
        <h2 id="deckSlide1Title">Every completed job should help you win the next one.</h2>
        <p class="deck-lead">
          Capture real work once, and JobCapturePro turns it into proof that shows up across your website, directory, and search—so customers choose you faster.
        </p>

        <div class="deck-flow deck-flow--stack" aria-label="Outcomes">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_camera ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More proof builds trust faster</div>
              <div class="deck-flow-sub">Turn job photos into verified work customers can see.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_shield ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More trust leads to more calls</div>
              <div class="deck-flow-sub">Show real proof where people compare options.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_phone ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More calls mean more booked jobs</div>
              <div class="deck-flow-sub">Win faster decisions without discounting.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2>One photo is all your team needs to take.</h2>
        <p class="deck-lead">
          Snap a quick photo and JobCapturePro handles the rest—writing the story, tagging the job, and publishing proof everywhere your customers look.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="What happens from one photo">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_sparkle ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">AI writes the job story</div>
              <div class="deck-flow-sub">Get a clean, customer-friendly update you can publish immediately.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_tag ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Location + services are tagged</div>
              <div class="deck-flow-sub">Structured proof helps search engines and customers understand the job.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_rocket ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Publishing happens instantly</div>
              <div class="deck-flow-sub">Your proof shows up across channels without extra steps from your team.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2>Reviews arrive at the right time, without chasing.</h2>
        <p class="deck-lead">
          When the job wraps up, reviews go out automatically at the moment customers are happiest—so you build reputation without awkward follow-ups.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="Review outcomes">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_send ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Higher response rate</div>
              <div class="deck-flow-sub">Requests land while the experience is fresh—more customers respond.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_repeat ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">No manual follow-up</div>
              <div class="deck-flow-sub">Automated reminders keep it consistent without your time.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_star ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Trusted public feedback</div>
              <div class="deck-flow-sub">More real reviews means more confidence and more booked jobs.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide">
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
          <div class="grid-box rank-box">
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

      <article class="deck-slide">
        <h2>Show proof across every channel.</h2>
        <p class="deck-lead">
          Every check-in updates your website, directory listing, Google Business profile, and social content.
        </p>
        <div class="deck-tiles">
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
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2>Everything stays consistent without extra work.</h2>
        <p class="deck-lead">
          Your crew keeps working. JobCapturePro keeps your presence updated and consistent so your brand looks sharp without extra admin work.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="Consistency benefits">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_repeat ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">No manual posting</div>
              <div class="deck-flow-sub">Proof publishes in the background while you run jobs.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_calendar ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">No chasing photos</div>
              <div class="deck-flow-sub">One quick check-in is enough—your team stays moving.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_clipboard ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">No admin headaches</div>
              <div class="deck-flow-sub">A consistent cadence of proof makes your brand look active.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2>Prospects see proof instantly.</h2>
        <p class="deck-lead">
          Your work shows up where customers decide—search, maps, your site, and the directory—so you stand out before they even call.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="Prospect outcomes">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_eye ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Higher trust</div>
              <div class="deck-flow-sub">Real jobs + real proof makes you feel safer to hire.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_zap ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Faster decisions</div>
              <div class="deck-flow-sub">Customers see the difference quickly and stop shopping around.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_phone ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">More booked jobs</div>
              <div class="deck-flow-sub">More calls go to the contractor who looks proven and active.</div>
            </div>
          </div>
        </div>
      </article>

      <article class="deck-slide deck-slide--cards">
        <h2 id="deckPersonalTitle">Now watch one job publish everywhere for your business.</h2>
        <p class="deck-lead" id="deckPersonalLead">
          You're about to see the live workflow end-to-end—one job turning into proof that builds rankings, trust, and calls.
        </p>
        <div class="deck-flow deck-flow--stack" aria-label="What you'll see next">
          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">1</span>
              <img src="<?php echo esc_url( $icon_play ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Live job → automated proof</div>
              <div class="deck-flow-sub">Capture the job once and generate publish-ready proof instantly.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">2</span>
              <img src="<?php echo esc_url( $icon_globe ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Proof → higher rankings</div>
              <div class="deck-flow-sub">More verified activity strengthens visibility across maps and search.</div>
            </div>
          </div>

          <div class="deck-flow-card">
            <span class="deck-flow-icon" aria-hidden="true">
              <span class="deck-flow-step" aria-hidden="true">3</span>
              <img src="<?php echo esc_url( $icon_phone ); ?>" alt="" />
            </span>
            <div class="deck-flow-body">
              <div class="deck-flow-title">Rankings → more calls</div>
              <div class="deck-flow-sub">As you rise, you get chosen faster—and the phone rings more.</div>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div class="deck-actions">
      <button class="btn-control" data-action="prev">← Back</button>
      <button class="btn-control" data-action="next" id="deckNextBtn">Next</button>
      <button class="btn-control primary is-hidden" data-action="launch" id="deckLaunchBtn">
        Launch the live demo →
      </button>
    </div>
  </div>
</section>
