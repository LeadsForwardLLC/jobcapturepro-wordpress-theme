/**
 * Proof Sprint funnel — assessment, demo, theater, analytics, trial handoff.
 */
(function () {
  'use strict';

  var LP_VARIANT = 'proof_sprint';
  var STORAGE_KEY = 'jcp_ps_state';
  var campaignBase =
    (document.body && document.body.getAttribute('data-ps-campaign-base')) ||
    '';

  var TRADE_JOBS = {
    hvac: {
      label: 'HVAC',
      title: 'AC system replacement',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'New outdoor unit set and system commissioned for cooling.',
    },
    plumbing: {
      label: 'Plumbing',
      title: 'Water heater replacement',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'Completed water heater replacement with geotagged job proof.',
    },
    roofing: {
      label: 'Roofing',
      title: 'Roof replacement',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'Tear-off complete and new shingles installed.',
    },
    electrical: {
      label: 'Electrical',
      title: 'Electrical panel upgrade',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'New breaker layout and safety check completed.',
    },
    foundation: {
      label: 'Foundation',
      title: 'Foundation repair',
      city: 'Triadelphia, WV',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'Structural repair documented on site.',
    },
    landscaping: {
      label: 'Landscaping',
      title: 'Landscape install',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'Completed landscape project with before-and-after photos.',
    },
    remodeling: {
      label: 'Remodeling',
      title: 'Interior remodel',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'Finished remodel documented for the homeowner.',
    },
    other: {
      label: 'Home service',
      title: 'Completed service job',
      city: 'Austin, TX',
      photo: 'jcp-campaign-job-proof-640.webp',
      desc: 'Finished work with photos from the site.',
    },
  };

  var state = {
    step: 1,
    trade: '',
    jobs: 0,
    jobsLabel: '',
    source: '',
    used: 0,
    usedLabel: '',
    demoStep: 0,
    annual: 0,
    unused: 0,
  };

  function photoUrl(file) {
    if (!file) return '';
    if (/^https?:\/\//i.test(file)) return file;
    return campaignBase + file;
  }

  function jobPersona() {
    return TRADE_JOBS[state.trade] || TRADE_JOBS.plumbing;
  }

  function track(eventName, extra) {
    try {
      window.dataLayer = window.dataLayer || [];
      var payload = {
        event: eventName,
        lp_variant: LP_VARIANT,
        page: location.pathname,
        page_path: location.pathname,
      };
      if (extra && typeof extra === 'object') {
        Object.keys(extra).forEach(function (k) {
          payload[k] = extra[k];
        });
      }
      window.dataLayer.push(payload);
    } catch (e) {}
  }

  function saveState() {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {}
  }

  function loadState() {
    try {
      var raw = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || 'null');
      if (raw && typeof raw === 'object') {
        Object.keys(raw).forEach(function (k) {
          state[k] = raw[k];
        });
      }
    } catch (e) {}
  }

  function attrParams() {
    var out = {};
    try {
      var cur = new URLSearchParams(location.search);
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid', 'lp_variant'].forEach(function (k) {
        var v = cur.get(k);
        if (v) out[k] = v;
      });
    } catch (e) {}
    if (!out.lp_variant) out.lp_variant = LP_VARIANT;
    return out;
  }

  function decorateTrialLinks() {
    var params = attrParams();
    var base =
      (window.JCP_ONBOARDING && window.JCP_ONBOARDING.url) ||
      'https://app.jobcapturepro.com/onboarding';
    document.querySelectorAll('[data-ps-trial]').forEach(function (a) {
      try {
        var u = new URL(a.getAttribute('href') || base, location.origin);
        if (!u.hostname || u.hostname.indexOf('jobcapturepro.com') === -1) {
          u = new URL(base);
        }
        Object.keys(params).forEach(function (k) {
          if (!u.searchParams.get(k)) u.searchParams.set(k, params[k]);
        });
        var source = a.getAttribute('data-ps-source') || 'trial';
        u.searchParams.set('jcp_surface', 'proof_sprint_' + source);
        if (!u.searchParams.get('utm_content')) u.searchParams.set('utm_content', 'proof_sprint_' + source);
        a.href = u.toString();
      } catch (e) {}
      a.addEventListener('click', function () {
        track('trial_cta_clicked', {
          source: a.getAttribute('data-ps-source') || 'trial',
          cta_source: a.getAttribute('data-ps-source') || 'trial',
        });
        track('trial_signup_started', { source: a.getAttribute('data-ps-source') || 'trial' });
      });
    });
  }

  function applyJobPersonaToDom() {
    var job = jobPersona();
    var url = photoUrl(job.photo);
    document.querySelectorAll('[data-ps-job-photo]').forEach(function (img) {
      if (url) img.setAttribute('src', url);
    });
    document.querySelectorAll('[data-ps-job-title]').forEach(function (el) {
      el.textContent = job.title;
    });
    document.querySelectorAll('[data-ps-job-city]').forEach(function (el) {
      el.textContent = job.city;
    });
    document.querySelectorAll('[data-ps-trade-label]').forEach(function (el) {
      el.textContent = job.label + ' job';
    });
  }

  /* ---- Hero theater ---- */
  function runHeroTheater() {
    var root = document.querySelector('[data-ps-theater] .ps-theater');
    if (!root) return;
    var reduce = false;
    try {
      reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {}
    var outs = root.querySelectorAll('[data-out]');
    if (reduce) {
      root.classList.add('is-live', 'is-processing');
      outs.forEach(function (el) {
        el.classList.add('is-on');
      });
      return;
    }
    root.classList.add('is-running');
    window.setTimeout(function () {
      root.classList.add('is-processing');
    }, 900);
    outs.forEach(function (el, i) {
      window.setTimeout(function () {
        el.classList.add('is-on');
        if (i === outs.length - 1) {
          root.classList.remove('is-running');
          root.classList.add('is-live');
        }
      }, 1400 + i * 380);
    });
  }

  /* ---- Assessment ---- */
  function stepReady() {
    if (state.step === 1) return !!state.trade;
    if (state.step === 2) return state.jobs > 0;
    if (state.step === 3) return !!state.source;
    if (state.step === 4) {
      var custom = document.getElementById('psUsedCustom');
      if (custom && custom.value !== '') return Number(custom.value) >= 0;
      return !!document.querySelector('.ps-choice[data-ps-field="used"].is-selected');
    }
    return false;
  }

  function updateAssessmentUI() {
    document.querySelectorAll('[data-ps-step]').forEach(function (el) {
      var n = Number(el.getAttribute('data-ps-step'));
      var on = n === state.step;
      el.hidden = !on;
      el.classList.toggle('is-active', on);
    });
    document.querySelectorAll('[data-stepper]').forEach(function (el) {
      var n = Number(el.getAttribute('data-stepper'));
      el.classList.toggle('is-on', n <= state.step);
    });
    var back = document.getElementById('psBackBtn');
    var next = document.getElementById('psNextBtn');
    if (back) back.disabled = state.step === 1;
    if (next) {
      next.disabled = !stepReady();
      next.textContent = state.step === 4 ? 'Show My Proof Potential →' : 'Next →';
    }
    var msg = document.getElementById('psFormMsg');
    if (msg) msg.textContent = '';
  }

  function showResult() {
    var annual = Math.max(0, Math.round(state.jobs * 52));
    var usedAnnual = Math.min(annual, Math.round(Math.max(0, state.used) * 52));
    var unused = Math.max(0, annual - usedAnnual);
    state.annual = annual;
    state.unused = unused;
    saveState();
    applyJobPersonaToDom();

    var annualEl = document.getElementById('psAnnualJobs');
    var unusedEl = document.getElementById('psUnusedJobs');
    var sentence = document.getElementById('psResultSentence');
    if (annualEl) annualEl.textContent = annual.toLocaleString();
    if (unusedEl) unusedEl.textContent = unused.toLocaleString();
    var pct = annual ? Math.round((unused / annual) * 100) : 0;
    if (sentence) {
      sentence.textContent =
        pct >= 80
          ? 'About ' + pct + '% of your completed jobs may be disappearing instead of becoming public proof.'
          : 'The work already happened. The photos may already exist. JobCapturePro helps turn more of those completed jobs into assets that keep working after the truck leaves.';
    }

    var result = document.getElementById('ps-result');
    if (result) {
      result.hidden = false;
      try {
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (e) {
        result.scrollIntoView(true);
      }
    }
    track('proof_assessment_completed', { trade: state.trade, jobs: state.jobs, source: state.source, used: state.used, annual: annual, unused: unused });
    track('proof_result_viewed', { annual: annual, unused: unused });
    renderDemo();
  }

  function setupAssessment() {
    var next = document.getElementById('psNextBtn');
    var back = document.getElementById('psBackBtn');
    if (!next || !back) return;

    document.querySelectorAll('.ps-choice').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var field = btn.getAttribute('data-ps-field');
        var value = btn.getAttribute('data-ps-value');
        document.querySelectorAll('.ps-choice[data-ps-field="' + field + '"]').forEach(function (b) {
          b.classList.remove('is-selected');
        });
        btn.classList.add('is-selected');
        if (field === 'trade') {
          state.trade = value;
          applyJobPersonaToDom();
          renderDemo();
        } else if (field === 'jobs') {
          state.jobs = Number(value) || 0;
          state.jobsLabel = btn.getAttribute('data-ps-label') || value;
          var jc = document.getElementById('psJobsCustom');
          if (jc) jc.value = '';
        } else if (field === 'source') {
          state.source = value;
        } else if (field === 'used') {
          state.used = Number(value) || 0;
          state.usedLabel = btn.getAttribute('data-ps-label') || value;
          var uc = document.getElementById('psUsedCustom');
          if (uc) uc.value = '';
        }
        updateAssessmentUI();
      });
    });

    ['psJobsCustom', 'psUsedCustom'].forEach(function (id) {
      var input = document.getElementById(id);
      if (!input) return;
      input.addEventListener('input', function () {
        var field = input.getAttribute('data-ps-custom');
        var n = Number(input.value);
        if (!isFinite(n) || n < 0) return;
        document.querySelectorAll('.ps-choice[data-ps-field="' + field + '"]').forEach(function (b) {
          b.classList.remove('is-selected');
        });
        if (field === 'jobs') {
          state.jobs = n;
          state.jobsLabel = String(n);
        } else {
          state.used = n;
          state.usedLabel = String(n);
        }
        updateAssessmentUI();
      });
    });

    back.addEventListener('click', function () {
      if (state.step > 1) {
        state.step -= 1;
        updateAssessmentUI();
      }
    });

    next.addEventListener('click', function () {
      if (!stepReady()) {
        var msg = document.getElementById('psFormMsg');
        if (msg) msg.textContent = 'Choose one option to continue.';
        return;
      }
      if (state.step === 1) track('proof_assessment_step_1', { trade: state.trade });
      if (state.step === 2) track('proof_assessment_step_2', { jobs: state.jobs });
      if (state.step === 3) track('proof_assessment_step_3', { source: state.source });
      if (state.step < 4) {
        if (state.step === 1) track('proof_assessment_started', { trade: state.trade });
        state.step += 1;
        updateAssessmentUI();
        return;
      }
      showResult();
    });

    updateAssessmentUI();
  }

  function esc(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function brandPersona() {
    var brands = {
      hvac: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      plumbing: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      roofing: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      electrical: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      foundation: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      landscaping: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      remodeling: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
      other: { name: 'Lone Star Comfort', slug: 'lonestarcomfort' },
    };
    return brands[state.trade] || brands.plumbing;
  }

  function brandMark() {
    return (
      '<span class="ps-mock-mark" aria-hidden="true">' +
      '<svg class="ps-mock-mark__svg" viewBox="0 0 24 24" width="14" height="14" focusable="false">' +
      '<text x="12" y="17.5" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="800" font-family="Arial, Helvetica, sans-serif">L</text>' +
      '</svg></span>'
    );
  }

  function qrMarkup() {
    var base =
      (typeof JCP_PS !== 'undefined' && JCP_PS.campaignBase) || campaignBase || '';
    return (
      '<img class="ps-mock-qr__img" src="' +
      esc(base + 'ps-dummy-qr.png') +
      '" alt="" width="112" height="112" loading="lazy" decoding="async" />'
    );
  }

  function socialCopy(job) {
    return (
      'Another job wrapped in ' +
      job.city +
      '. ' +
      job.title +
      ' done right. Documented from the field.'
    );
  }

  function captureSourceLabel() {
    var source = state.source || 'JCP mobile app';
    if (source.indexOf('Housecall') >= 0) return 'Housecall Pro photo';
    if (source.indexOf('CompanyCam') >= 0) return 'CompanyCam photo';
    if (source.indexOf('Workiz') >= 0) return 'Workiz photo';
    if (source.indexOf('camera') >= 0 || source.indexOf('Camera') >= 0 || source.indexOf('Phones') >= 0) {
      return 'Phone camera roll';
    }
    if (source.indexOf('JCP') >= 0) return 'JCP mobile app';
    return 'Connected workflow';
  }

  /* ---- Demo ---- */
  var DEMO_EVENTS = [
    'demo_job_capture_viewed',
    'demo_checkin_viewed',
    'demo_website_viewed',
    'demo_gbp_viewed',
    'demo_review_viewed',
    'demo_social_viewed',
    'demo_directory_viewed',
    'demo_completed',
  ];

  function demoSteps() {
    var job = jobPersona();
    var source = captureSourceLabel();
    return [
      {
        title: 'The job is finished.',
        body:
          'Your crew already did the hard part. They completed the work and the job photos usually already exist on a phone or in your field app. Without JobCapturePro, that proof often dies in a camera roll.',
        detail: 'Completed job on site',
        event: DEMO_EVENTS[0],
        type: 'job',
      },
      {
        title: 'Your tech just snaps the photo.',
        body:
          'One photo from the driveway is enough. Capture it in the JCP mobile app or your existing workflow (' +
          source +
          '). JobCapturePro handles structuring, writing, and publishing from there.',
        detail: source,
        event: DEMO_EVENTS[0],
        type: 'capture',
      },
      {
        title: 'JCP creates the check-in.',
        body:
          'We turn those job photos plus service type and location into a structured check-in: channel-ready copy, geotags, and assets your marketing channels can use immediately.',
        detail: job.label + ' job structured',
        event: DEMO_EVENTS[1],
        type: 'checkin',
      },
      {
        title: 'Published to your website. Location included.',
        body:
          'The same completed job can land on your recent-work feed and auto-post to the matching service-area location page through the JCP website plugin. Real local pages. Real job proof. Built for SEO.',
        detail: 'Website plugin · location page',
        event: DEMO_EVENTS[2],
        type: 'web',
      },
      {
        title: 'Fresh Google activity. Automatically.',
        body:
          'Instead of another generic promo, your Google Business Profile can stay active with real completed work from the field — photos and details pulled from the same job check-in.',
        detail: 'Google Business Profile',
        event: DEMO_EVENTS[3],
        type: 'google',
      },
      {
        title: 'Ask while they still remember your name.',
        body:
          'Before you leave the driveway, send a review SMS or show a QR. The customer chooses whether to review. No review gating. No guaranteed five-star claims.',
        detail: 'Review opportunity',
        event: DEMO_EVENTS[4],
        type: 'review',
      },
      {
        title: 'Social content your tech never had to write.',
        body:
          'Ready-to-post social content is generated from work your crew already finished. No asking techs to become marketers after a long day.',
        detail: 'Social post ready',
        event: DEMO_EVENTS[5],
        type: 'social',
      },
      {
        title: 'And a living directory listing.',
        body:
          'Your JobCapturePro Directory listing stays alive with verified jobs, ratings, and recent work — so homeowners see proof you actually do the job in their area.',
        detail: 'JobCapturePro Directory',
        event: DEMO_EVENTS[6],
        type: 'directory',
      },
    ];
  }

  function renderDemoCanvas(type) {
    var job = jobPersona();
    var brand = brandPersona();
    var url = photoUrl(job.photo);
    var c = document.getElementById('psDemoCanvas');
    if (!c) return;
    var title = esc(job.title);
    var city = esc(job.city);
    var desc = esc(job.desc);
    var biz = esc(brand.name);
    var slug = esc(brand.slug);
    var mark = brandMark();
    var photo =
      '<img class="ps-mock__photo" src="' +
      esc(url) +
      '" alt="" width="640" height="400" loading="lazy" data-ps-job-photo />';
    var thumb =
      '<img class="ps-mock__thumb" src="' +
      esc(url) +
      '" alt="" width="160" height="120" loading="lazy" data-ps-job-photo />';

    if (type === 'job') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--job">' +
        '<div class="ps-mock__chip-row"><span class="ps-mock__chip is-good">Completed job</span><span class="ps-mock__chip">Just now</span></div>' +
        '<div class="ps-mock__media">' +
        photo +
        '<span class="ps-mock__geo">' +
        city +
        '</span></div>' +
        '<div class="ps-mock__job-foot"><div><strong>' +
        title +
        '</strong><span>' +
        city +
        '</span></div><em>Ready for JCP</em></div></div>';
      return;
    }

    if (type === 'capture') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--capture">' +
        '<div class="ps-mock-capture__phone">' +
        '<div class="ps-mock-capture__notch"></div>' +
        '<div class="ps-mock-capture__screen">' +
        photo +
        '<div class="ps-mock-capture__shutter" aria-hidden="true"></div>' +
        '</div></div>' +
        '<div class="ps-mock-capture__copy">' +
        '<strong>One snap. That\'s it.</strong>' +
        '<p>Tech captures the finished job in ' +
        esc(captureSourceLabel()) +
        '. JCP turns it into website proof, Google activity, reviews, social and directory updates.</p>' +
        '</div></div>';
      return;
    }

    if (type === 'checkin') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--checkin">' +
        '<div class="ps-mock__checkin-head">' +
        mark +
        '<div><strong>Creating check-in</strong><span>' +
        title +
        ' · ' +
        city +
        '</span></div></div>' +
        '<div class="ps-mock__checkin-grid">' +
        '<div class="ps-mock__media ps-mock__media--sm">' +
        photo +
        '</div>' +
        '<ul class="ps-mock__checklist">' +
        '<li class="is-done">Photos received</li>' +
        '<li class="is-done">Service identified</li>' +
        '<li class="is-done">Location attached</li>' +
        '<li class="is-done">Channel-ready copy</li>' +
        '<li class="is-on">Publishing channels…</li>' +
        '</ul></div></div>';
      return;
    }

    if (type === 'web') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--browser">' +
        '<div class="ps-mock-browser__bar"><span></span><span></span><span></span>' +
        '<div class="ps-mock-browser__url">' +
        slug +
        '.com/locations/austin-tx</div></div>' +
        '<div class="ps-mock-browser__body">' +
        '<div class="ps-mock-browser__topline">' +
        mark +
        '<div><p class="ps-mock-browser__kicker">Austin, TX service area</p>' +
        '<h4>Recent jobs near you</h4></div></div>' +
        '<article class="ps-mock-jobcard">' +
        thumb +
        '<div><strong>' +
        title +
        '</strong><span>' +
        city +
        ' · Location page</span><p>' +
        desc +
        '</p><em>Auto-posted by JCP website plugin</em></div>' +
        '</article>' +
        '<p class="ps-mock__meta">Live on map + location pages. Built for local SEO.</p>' +
        '</div></div>';
      return;
    }

    if (type === 'google') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--gbp">' +
        '<div class="ps-mock-gbp__brand">' +
        mark +
        '<div><strong>' +
        biz +
        '</strong><span>Google Business Profile · Update</span></div>' +
        '<em>Posted</em></div>' +
        '<div class="ps-mock__media">' +
        photo +
        '</div>' +
        '<div class="ps-mock-gbp__copy"><strong>Just finished: ' +
        title +
        ' in ' +
        city +
        '</strong><p>' +
        desc +
        ' Real work. Real photos. Posted from JobCapturePro.</p></div>' +
        '<div class="ps-mock-gbp__actions"><span>Share</span><span>Call</span><span>Directions</span></div>' +
        '<p class="ps-mock__meta">Prepared automatically · Verified job details</p></div>';
      return;
    }

    if (type === 'review') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--review">' +
        '<div class="ps-mock-review__row">' +
        '<div class="ps-mock-imessage">' +
        '<div class="ps-mock-imessage__bar"><strong>Messages</strong><span>Customer</span></div>' +
        '<div class="ps-mock-imessage__thread">' +
        '<div class="ps-mock-imessage__bubble is-out">Thanks again for choosing ' +
        biz +
        '. If we earned it, you can leave a quick review here:</div>' +
        '<div class="ps-mock-imessage__bubble is-out is-link">review.jobcapturepro.com/' +
        slug +
        '</div>' +
        '<div class="ps-mock-imessage__time">Delivered · Just now</div>' +
        '</div></div>' +
        '<div class="ps-mock-qr">' +
        qrMarkup() +
        '<strong>Or show QR on site</strong>' +
        '<span>Customer chooses whether to review. No gating.</span></div>' +
        '</div></div>';
      return;
    }

    if (type === 'social') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--social">' +
        '<div class="ps-mock-social__head">' +
        mark +
        '<div><strong>' +
        biz +
        '</strong><span>Prepared just now · ' +
        city +
        '</span></div></div>' +
        '<p class="ps-mock-social__copy">' +
        esc(socialCopy(job)) +
        '</p>' +
        '<div class="ps-mock__media">' +
        photo +
        '</div>' +
        '<div class="ps-mock-social__reactions"><span>👍 Like</span><span>💬 Comment</span><span>↗ Share</span></div></div>';
      return;
    }

    if (type === 'directory') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--directory">' +
        '<p class="ps-mock-dir__label">JobCapturePro Directory</p>' +
        '<article class="directory-card directory-card-highlight ps-mock-dir__card">' +
        '<span class="directory-badge verified">Verified</span>' +
        '<div class="card-header">' +
        '<div class="company-mark">' +
        mark +
        '</div>' +
        '<div class="card-header-content"><h3 class="card-name">' +
        biz +
        '</h3></div></div>' +
        '<div class="card-location"><span>' +
        esc(job.label) +
        ' · ' +
        city +
        '</span></div>' +
        '<div class="card-meta-row"><span class="meta-inline">12 jobs documented</span><span class="meta-divider">·</span><span class="meta-inline">Active today</span></div>' +
        '<div class="card-rating"><div class="stars" aria-hidden="true">★★★★★</div><span class="rating-text">4.9 · 128 reviews</span></div>' +
        '<div class="ps-mock-dir__reviews">' +
        '<blockquote>“Showed up, finished the job clean, and I could see other local work they had done.”</blockquote>' +
        '<blockquote>“Photos of real installs in my area made it easy to trust them.”</blockquote>' +
        '</div>' +
        '<div class="ps-mock-dir__latest">' +
        thumb +
        '<div><p class="ps-mock-dir__latest-label">Latest completed job</p><strong>' +
        title +
        '</strong><span>Documented on site · Just published</span></div></div>' +
        '<div class="card-footer"><span class="view-profile">View activity</span></div>' +
        '</article></div>';
    }
  }

  function renderDemo() {
    var steps = demoSteps();
    if (state.demoStep >= steps.length) state.demoStep = steps.length - 1;
    var d = steps[state.demoStep];
    var label = document.getElementById('psDemoStepLabel');
    var title = document.getElementById('psDemoTitle');
    var body = document.getElementById('psDemoBody');
    var source = document.getElementById('psDemoSource');
    var prev = document.getElementById('psDemoPrev');
    var next = document.getElementById('psDemoNext');
    var progress = document.getElementById('psDemoProgress');
    var payoff = document.getElementById('psDemoPayoff');

    if (label) label.textContent = 'Step ' + (state.demoStep + 1) + ' of ' + steps.length;
    if (title) title.textContent = d.title;
    if (body) body.textContent = d.body;
    if (source) source.textContent = d.detail;
    if (prev) prev.disabled = state.demoStep === 0;
    if (next) next.textContent = state.demoStep === steps.length - 1 ? 'See the payoff →' : 'Next →';

    if (progress) {
      while (progress.children.length < steps.length) {
        progress.appendChild(document.createElement('span'));
      }
      while (progress.children.length > steps.length) {
        progress.removeChild(progress.lastChild);
      }
      Array.prototype.forEach.call(progress.children, function (el, i) {
        el.classList.toggle('is-done', i <= state.demoStep);
      });
    }

    renderDemoCanvas(d.type);
    if (d.event) track(d.event, { step: state.demoStep + 1, trade: state.trade });

    if (payoff) payoff.hidden = true;
    var shell = document.querySelector('.ps-demo-shell');
    if (shell) shell.hidden = false;
  }

  function showDemoPayoff() {
    var payoff = document.getElementById('psDemoPayoff');
    var jobsLine = document.getElementById('psDemoPayoffJobs');
    var shell = document.querySelector('.ps-demo-shell');
    var annual = state.annual || Math.round((state.jobs || 20) * 52);
    var weekly = state.jobs || 20;
    if (jobsLine) {
      jobsLine.textContent =
        'You told us you complete approximately ' +
        weekly +
        ' jobs every week. That’s roughly ' +
        annual.toLocaleString() +
        ' opportunities each year.';
    }
    if (shell) shell.hidden = true;
    if (payoff) {
      payoff.hidden = false;
      try {
        payoff.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } catch (e) {
        payoff.scrollIntoView(true);
      }
    }
    track('demo_completed', { annual: annual, trade: state.trade });
  }

  function setupDemo() {
    var prev = document.getElementById('psDemoPrev');
    var next = document.getElementById('psDemoNext');
    if (!prev || !next) return;
    prev.addEventListener('click', function () {
      if (state.demoStep > 0) {
        state.demoStep -= 1;
        saveState();
        renderDemo();
      }
    });
    next.addEventListener('click', function () {
      var steps = demoSteps();
      if (state.demoStep < steps.length - 1) {
        state.demoStep += 1;
        saveState();
        renderDemo();
      } else {
        showDemoPayoff();
      }
    });
    renderDemo();
  }

  function setupReveals() {
    var nodes = document.querySelectorAll('[data-ps-reveal]');
    if (!nodes.length) return;
    var reduce = false;
    try {
      reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {}
    if (reduce || !('IntersectionObserver' in window)) {
      nodes.forEach(function (el) {
        el.classList.add('is-in');
      });
      return;
    }
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-in');
          var view = entry.target.getAttribute('data-ps-track-view');
          if (view) track(view, { section: entry.target.id || view });
          io.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -6% 0px' }
    );
    nodes.forEach(function (el) {
      io.observe(el);
    });
  }

  function setupStickyCta() {
    var sticky = document.getElementById('psStickyCta');
    var hero = document.getElementById('ps-hero');
    if (!sticky || !hero || !('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(min-width: 768px)').matches) return;
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          var show = !entry.isIntersecting;
          sticky.hidden = !show;
          document.body.classList.toggle('has-sticky-cta', show);
        });
      },
      { threshold: 0.05 }
    );
    io.observe(hero);
  }

  function setupTrackedClicks() {
    document.addEventListener(
      'click',
      function (e) {
        var t = e.target && e.target.closest ? e.target.closest('[data-ps-track]') : null;
        if (!t) return;
        track(t.getAttribute('data-ps-track') || 'cta_click', {
          source: t.getAttribute('data-ps-source') || '',
        });
      },
      true
    );
  }

  function hideChat() {
    [
      '#chat-widget-container',
      '#lc_text-widget',
      '.lc_text-widget',
      '[id*="chat-widget"]',
      'iframe[src*="leadconnector"]',
      '.ghl-chat-widget',
    ].forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        el.style.setProperty('display', 'none', 'important');
      });
    });
  }

  function boot() {
    if (!document.body || !document.body.classList.contains('jcp-proof-sprint')) return;
    loadState();
    applyJobPersonaToDom();
    decorateTrialLinks();
    setupAssessment();
    setupDemo();
    setupReveals();
    setupStickyCta();
    setupTrackedClicks();
    hideChat();
    runHeroTheater();
    track('PaidLandingView', { section: 'proof_sprint' });

    if (state.annual > 0) {
      var result = document.getElementById('ps-result');
      if (result) result.hidden = false;
      var annualEl = document.getElementById('psAnnualJobs');
      var unusedEl = document.getElementById('psUnusedJobs');
      if (annualEl) annualEl.textContent = state.annual.toLocaleString();
      if (unusedEl) unusedEl.textContent = state.unused.toLocaleString();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
