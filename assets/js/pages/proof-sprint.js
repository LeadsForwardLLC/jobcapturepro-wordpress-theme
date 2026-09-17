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
    email: '',
    optedIn: false,
    demoAutoTimer: null,
    demoPaused: false,
    demoAutoRunning: false,
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
    state.demoAutoTimer = null;
    state.demoAutoRunning = false;
    state.demoPaused = false;
  }

  function attrPayload() {
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        return window.JCPLeadAttribution.getPayload() || {};
      }
    } catch (e) {}
    try {
      if (window.JCPAttribution && typeof window.JCPAttribution.getPayload === 'function') {
        return window.JCPAttribution.getPayload() || {};
      }
    } catch (e2) {}
    try {
      var raw = sessionStorage.getItem('jcp_attr') || sessionStorage.getItem('jcp_lead_attribution');
      if (raw) return JSON.parse(raw) || {};
    } catch (e3) {}
    return {};
  }

  function deriveFirstName(email) {
    var local = String(email || '').split('@')[0] || '';
    local = local.replace(/[._-]+/g, ' ').trim();
    if (!local) return 'there';
    return local.charAt(0).toUpperCase() + local.slice(1, 40);
  }

  function validEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim());
  }

  function prefersReducedMotion() {
    try {
      return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {
      return false;
    }
  }

  function mapAssetUrl() {
    var fromDom = document.querySelector('[data-ps-map-url]');
    if (fromDom && fromDom.getAttribute('data-ps-map-url')) {
      return fromDom.getAttribute('data-ps-map-url');
    }
    if (typeof JCP_PS !== 'undefined' && JCP_PS.mapUrl) return JCP_PS.mapUrl;
    return '/wp-content/themes/jobcapturepro-core/assets/map-3c5b675f-f28d-41a5-ba3a-972b4c189f10.png';
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
        if (state.email && !u.searchParams.get('email')) {
          u.searchParams.set('email', state.email);
        }
        if (state.trade && !u.searchParams.get('industry') && !u.searchParams.get('trade')) {
          u.searchParams.set('industry', state.trade);
        }
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
  function assessmentQuestionEl(logicalStep) {
    var field = logicalStep === 1 ? 'jobs' : 'used';
    var steps = document.querySelectorAll('[data-ps-step]');
    for (var i = 0; i < steps.length; i++) {
      if (steps[i].querySelector('[data-ps-field="' + field + '"]')) return steps[i];
    }
    return document.querySelector('[data-ps-step="' + logicalStep + '"]');
  }

  function stepReady() {
    if (state.step === 1) return state.jobs > 0;
    if (state.step === 2) {
      var custom = document.getElementById('psUsedCustom');
      if (custom && custom.value !== '') return Number(custom.value) >= 0;
      return !!document.querySelector('.ps-choice[data-ps-field="used"].is-selected');
    }
    return false;
  }

  function updateAssessmentUI() {
    var jobsEl = assessmentQuestionEl(1);
    var usedEl = assessmentQuestionEl(2);
    document.querySelectorAll('[data-ps-step]').forEach(function (el) {
      var on = el === (state.step === 1 ? jobsEl : usedEl);
      el.hidden = !on;
      el.classList.toggle('is-active', on);
    });
    document.querySelectorAll('[data-stepper]').forEach(function (el) {
      var n = Number(el.getAttribute('data-stepper'));
      if (n > 2) {
        el.hidden = true;
        return;
      }
      el.hidden = false;
      el.classList.toggle('is-on', n <= state.step);
    });
    var back = document.getElementById('psBackBtn');
    var next = document.getElementById('psNextBtn');
    if (back) back.disabled = state.step === 1;
    if (next) {
      next.disabled = !stepReady();
      next.textContent = state.step === 2 ? 'Show My Proof Potential →' : 'Next →';
    }
    var msg = document.getElementById('psFormMsg');
    if (msg) msg.textContent = '';
  }

  function advanceAssessment() {
    if (!stepReady()) {
      var msg = document.getElementById('psFormMsg');
      if (msg) msg.textContent = 'Choose one option to continue.';
      return;
    }
    if (state.step === 1) track('proof_assessment_step_1', { jobs: state.jobs });
    if (state.step === 2) track('proof_assessment_step_2', { used: state.used });
    if (state.step < 2) {
      if (state.step === 1) track('proof_assessment_started', { jobs: state.jobs });
      state.step += 1;
      saveState();
      updateAssessmentUI();
      return;
    }
    showResult();
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
      if (pct >= 50) {
        sentence.textContent =
          'Roughly ' +
          pct +
          '% of your completed jobs likely vanish after the invoice — photos buried in phones, CRMs, and camera rolls instead of becoming proof that keeps selling. JobCapturePro turns more of that finished work into public assets that stay working after the truck leaves.';
      } else {
        sentence.textContent =
          'Even when some jobs become proof, most finished work still disappears after the invoice — stuck in camera rolls instead of fueling your website, Google, reviews, and social. JobCapturePro helps you keep more of that proof working for the next customer.';
      }
    }
    updateResultCta();

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
          var ratio = btn.getAttribute('data-ps-ratio');
          if (ratio !== null && ratio !== '') {
            state.used = Math.round(state.jobs * Number(ratio));
          } else {
            state.used = Number(value) || 0;
          }
          state.usedLabel = btn.getAttribute('data-ps-label') || value;
          var uc = document.getElementById('psUsedCustom');
          if (uc) uc.value = '';
        }
        saveState();
        updateAssessmentUI();
        if (field === 'jobs' || field === 'used') {
          window.setTimeout(function () {
            var active = document.activeElement;
            if (active && active.getAttribute && active.getAttribute('data-ps-custom')) return;
            if (!stepReady()) return;
            advanceAssessment();
          }, 280);
        }
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
        saveState();
        updateAssessmentUI();
      });
    });

    back.addEventListener('click', function () {
      if (state.step > 1) {
        state.step -= 1;
        saveState();
        updateAssessmentUI();
      }
    });

    next.addEventListener('click', function () {
      advanceAssessment();
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
      '<svg class="ps-mock-mark__svg" viewBox="0 0 16 16" width="16" height="16" focusable="false">' +
      '<path fill="#ffffff" d="M3.2 2.2h2.35v9.1H12.8V13.8H3.2z"/>' +
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
    var source = state.source || '';
    if (!source) return 'Job photo';
    if (source.indexOf('Housecall') >= 0) return 'Housecall Pro photo';
    if (source.indexOf('CompanyCam') >= 0) return 'CompanyCam photo';
    if (source.indexOf('Workiz') >= 0) return 'Workiz photo';
    if (source.indexOf('camera') >= 0 || source.indexOf('Camera') >= 0 || source.indexOf('Phones') >= 0) {
      return 'Phone camera roll';
    }
    if (source.indexOf('JCP') >= 0) return 'JCP mobile app';
    return 'Existing workflow';
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
    var captureDetail = state.source ? source : source === 'Job photo' ? 'Job photo' : 'Existing workflow';
    return [
      {
        title: 'The job is finished.',
        body:
          'Your crew already did the expensive part — the work is done and the proof usually already exists on a phone or in your CRM photo app. Without JobCapturePro, that finished job rarely becomes marketing.',
        detailLabel: 'INPUT',
        detail: 'Completed job',
        event: DEMO_EVENTS[0],
        type: 'job',
      },
      {
        title: 'Your tech snaps the photo.',
        body:
          'One finished-job photo is enough. Capture it in JobCapturePro or your supported workflow — JobCapturePro handles structuring, writing, and publishing from there.',
        detailLabel: 'INPUT',
        detail: captureDetail,
        event: DEMO_EVENTS[0],
        type: 'capture',
      },
      {
        title: 'JCP turns the job into structured proof.',
        body:
          'JobCapturePro combines photos, service type, and location into a structured check-in — channel-ready copy, geotags, and assets your marketing channels can use immediately.',
        detailLabel: 'INPUT',
        detail: 'Job details',
        event: DEMO_EVENTS[1],
        type: 'checkin',
      },
      {
        title: 'Published to your website.\nLocation included.',
        body:
          'Your newest completed job joins your map and recent-work feed — real local pages with real job proof, built for SEO.',
        detailLabel: 'PUBLISHED TO',
        detail: 'Website',
        event: DEMO_EVENTS[2],
        type: 'web',
      },
      {
        title: 'Fresh Google activity.\nWithout writing another post.',
        body:
          'Automatically posted when connected and enabled — photos and details from the same job check-in keep your Google Business Profile active. Available with automated publishing plans.',
        detailLabel: 'PUBLISHED TO',
        detail: 'Google Business Profile',
        event: DEMO_EVENTS[3],
        type: 'google',
      },
      {
        title: 'Ask while they still remember your name.',
        body:
          'QR/link from the JCP app, or automated request through supported CRM workflows. The customer chooses whether to review. No review gating. No guaranteed five-star claims.',
        detailLabel: 'CREATED',
        detail: 'Review opportunity',
        event: DEMO_EVENTS[4],
        type: 'review',
      },
      {
        title: 'Social content your tech never had to write.',
        body:
          'Automatically posted when connected and enabled — posts built from work your crew already finished. Available with automated publishing plans.',
        detailLabel: 'PUBLISHED TO',
        detail: 'Social',
        event: DEMO_EVENTS[5],
        type: 'social',
      },
      {
        title: 'Live on the JobCapturePro Directory.',
        body:
          'Your demo business listing shows the latest completed job in your trade and city — real check-in proof updating as your crew finishes work, not paid placement or fake ratings.',
        detailLabel: 'PUBLISHED TO',
        detail: 'JCP Directory',
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
      var mapUrl = esc(mapAssetUrl());
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
        '<h4>Recent check-ins</h4></div></div>' +
        '<div class="ps-mock-map" style="position:relative;margin:0 0 12px;border-radius:12px;overflow:hidden;">' +
        '<img src="' +
        mapUrl +
        '" alt="" width="640" height="280" loading="lazy" style="width:100%;height:auto;display:block;" />' +
        '<span class="ps-mock-map__pin is-active" style="position:absolute;left:52%;top:42%;width:12px;height:12px;border-radius:50%;background:#e85d04;box-shadow:0 0 0 4px rgba(232,93,4,.35);"></span>' +
        '<span class="ps-mock-map__pin" style="position:absolute;left:34%;top:58%;width:10px;height:10px;border-radius:50%;background:#64748b;opacity:.85;"></span>' +
        '<span class="ps-mock-map__pin" style="position:absolute;left:68%;top:55%;width:10px;height:10px;border-radius:50%;background:#64748b;opacity:.85;"></span>' +
        '<span class="ps-mock-map__pin" style="position:absolute;left:44%;top:28%;width:10px;height:10px;border-radius:50%;background:#64748b;opacity:.85;"></span>' +
        '</div>' +
        '<div class="ps-mock-checkin-row" style="display:flex;gap:10px;overflow:auto;">' +
        '<article class="ps-mock-jobcard is-active" style="min-width:58%;flex:0 0 auto;">' +
        thumb +
        '<div><strong>' +
        title +
        '</strong><span>' +
        city +
        ' · Just published</span><p>' +
        desc +
        '</p></div></article>' +
        '<article class="ps-mock-jobcard ps-mock-jobcard--placeholder" style="min-width:42%;flex:0 0 auto;opacity:.72;">' +
        '<div class="ps-mock__thumb" style="background:#e2e8f0;min-height:72px;border-radius:8px;"></div>' +
        '<div><strong>Earlier job</strong><span>' +
        city +
        '</span></div></article>' +
        '<article class="ps-mock-jobcard ps-mock-jobcard--placeholder" style="min-width:42%;flex:0 0 auto;opacity:.72;">' +
        '<div class="ps-mock__thumb" style="background:#e2e8f0;min-height:72px;border-radius:8px;"></div>' +
        '<div><strong>Earlier job</strong><span>' +
        city +
        '</span></div></article>' +
        '</div></div></div>';
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
        '<em>Posted when connected</em></div>' +
        '<div class="ps-mock__media">' +
        photo +
        '</div>' +
        '<div class="ps-mock-gbp__copy"><strong>Just finished: ' +
        title +
        ' in ' +
        city +
        '</strong><p>' +
        desc +
        ' Real work. Real photos from the field.</p></div>' +
        '<div class="ps-mock-gbp__actions"><span>Share</span><span>Call</span><span>Directions</span></div></div>';
      return;
    }

    if (type === 'review') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--review">' +
        '<div class="ps-mock-review__phone">' +
        '<div class="ps-mock-review__phone-bar"><span>Messages</span><strong>Customer</strong></div>' +
        '<div class="ps-mock-review__thread">' +
        '<div class="ps-mock-review__bubble">Thanks again for choosing ' +
        biz +
        '. If we earned it, leave a quick review:</div>' +
        '<div class="ps-mock-review__bubble is-link">review.jobcapturepro.com/' +
        slug +
        '</div>' +
        '<p class="ps-mock-review__time">Delivered · Just now</p>' +
        '</div></div>' +
        '<div class="ps-mock-qr">' +
        qrMarkup() +
        '<strong>Or show QR on site</strong>' +
        '<span>QR/link from the app · CRM automation when enabled</span></div></div>';
      return;
    }

    if (type === 'social') {
      c.innerHTML =
        '<div class="ps-mock ps-mock--social">' +
        '<div class="ps-mock-social__head">' +
        mark +
        '<div><strong>' +
        biz +
        '</strong><span>Posted when connected · ' +
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
        '<span class="directory-badge">Demo</span>' +
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
        '<div class="ps-mock-dir__latest">' +
        thumb +
        '<div><p class="ps-mock-dir__latest-label">Latest completed job</p><strong>' +
        title +
        '</strong><span>Documented on site · Just published</span></div></div>' +
        '<div class="card-footer"><span class="view-profile">View activity</span></div>' +
        '</article></div>';
    }
  }

  function stopDemoAuto() {
    if (state.demoAutoTimer) {
      window.clearInterval(state.demoAutoTimer);
      state.demoAutoTimer = null;
    }
    state.demoAutoRunning = false;
    state.demoPaused = false;
  }

  function autoRunDemo() {
    stopDemoAuto();
    state.demoAutoRunning = true;
    state.demoPaused = false;
    var tickMs = prefersReducedMotion() ? 380 : 2000;
    renderDemo();
    state.demoAutoTimer = window.setInterval(function () {
      if (state.demoPaused) return;
      var steps = demoSteps();
      if (state.demoStep >= steps.length - 1) {
        stopDemoAuto();
        showDemoPayoff();
        return;
      }
      state.demoStep += 1;
      saveState();
      renderDemo();
      if (state.demoStep >= steps.length - 1) {
        window.setTimeout(function () {
          stopDemoAuto();
          showDemoPayoff();
        }, tickMs);
      }
    }, tickMs);
  }

  function pauseDemoAuto() {
    if (!state.demoAutoRunning) return;
    if (state.demoAutoTimer) {
      window.clearInterval(state.demoAutoTimer);
      state.demoAutoTimer = null;
    }
    state.demoAutoRunning = false;
    state.demoPaused = false;
    renderDemo();
  }

  function renderDemo() {
    var steps = demoSteps();
    if (state.demoStep >= steps.length) state.demoStep = steps.length - 1;
    var d = steps[state.demoStep];
    var label = document.getElementById('psDemoStepLabel');
    var title = document.getElementById('psDemoTitle');
    var body = document.getElementById('psDemoBody');
    var source = document.getElementById('psDemoSource');
    var detailLabelEl = document.getElementById('psDemoDetailLabel');
    var prev = document.getElementById('psDemoPrev');
    var next = document.getElementById('psDemoNext');
    var pauseBtn = document.getElementById('psDemoPause');
    var progress = document.getElementById('psDemoProgress');
    var payoff = document.getElementById('psDemoPayoff');

    if (label) label.textContent = 'Step ' + (state.demoStep + 1) + ' of ' + steps.length;
    if (title) title.textContent = d.title;
    if (body) body.textContent = d.body.replace(/\n/g, ' ');
    if (source) source.textContent = d.detail;
    if (detailLabelEl) detailLabelEl.textContent = d.detailLabel || 'Source';
    document.querySelectorAll('.ps-demo-detail__label').forEach(function (el) {
      if (el.id === 'psDemoDetailLabel') return;
      el.textContent = d.detailLabel || 'Source';
    });
    if (prev) prev.disabled = state.demoStep === 0 || (state.demoAutoRunning && !state.demoPaused);

    if (next) {
      if (state.demoAutoRunning && !state.demoPaused) {
        next.hidden = true;
      } else {
        next.hidden = false;
        if (state.demoStep === 0) next.textContent = 'See what happens →';
        else if (state.demoStep === 1) next.textContent = 'Let JCP take it from here →';
        else if (state.demoStep >= steps.length - 1) next.textContent = 'See the payoff →';
        else next.textContent = 'Next →';
      }
    }
    if (pauseBtn) {
      pauseBtn.hidden = !(state.demoAutoRunning && !state.demoPaused);
    }

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
    try {
      var viewedKey = 'jcp_ps_DemoStepViewed_' + state.demoStep;
      if (!sessionStorage.getItem(viewedKey)) {
        sessionStorage.setItem(viewedKey, '1');
        track('DemoStepViewed', {
          step_index: state.demoStep,
          step_name: d.type,
          auto_or_manual: state.demoAutoRunning && !state.demoPaused ? 'auto' : 'manual',
          trade: state.trade,
          lp_variant: LP_VARIANT,
        });
      }
    } catch (eViewed) {
      track('DemoStepViewed', {
        step_index: state.demoStep,
        step_name: d.type,
        auto_or_manual: state.demoAutoRunning && !state.demoPaused ? 'auto' : 'manual',
        trade: state.trade,
        lp_variant: LP_VARIANT,
      });
    }
    if (d.event) {
      try {
        var stepKey = 'jcp_ps_step_' + d.event + '_' + state.demoStep;
        if (!sessionStorage.getItem(stepKey)) {
          sessionStorage.setItem(stepKey, '1');
          track(d.event, { step: state.demoStep + 1, trade: state.trade });
        }
      } catch (eStep) {
        track(d.event, { step: state.demoStep + 1, trade: state.trade });
      }
    }

    if (payoff) payoff.hidden = true;
    var shell = document.querySelector('.ps-demo-shell');
    if (shell && state.optedIn) shell.hidden = false;
  }

  function showDemoPayoff() {
    stopDemoAuto();
    var payoff = document.getElementById('psDemoPayoff');
    var jobsLine = document.getElementById('psDemoPayoffJobs');
    var shell = document.querySelector('.ps-demo-shell');
    var annual = state.annual || Math.round((state.jobs || 20) * 52);
    var weekly = state.jobs || 20;
    var unused = state.unused || Math.max(0, annual - Math.round(Math.max(0, state.used) * 52));
    if (jobsLine) {
      jobsLine.textContent =
        'You complete about ' +
        weekly +
        ' jobs every week — roughly ' +
        annual.toLocaleString() +
        ' per year — and an estimated ' +
        unused.toLocaleString() +
        ' may never become proof that keeps working.';
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
    try {
      if (!sessionStorage.getItem('jcp_ps_demo_completed')) {
        sessionStorage.setItem('jcp_ps_demo_completed', '1');
        track('demo_completed', { annual: annual, trade: state.trade });
      }
    } catch (eDone) {
      track('demo_completed', { annual: annual, trade: state.trade });
    }
  }

  function restartDemo() {
    stopDemoAuto();
    state.demoStep = 0;
    saveState();
    var payoff = document.getElementById('psDemoPayoff');
    var shell = document.querySelector('.ps-demo-shell');
    if (payoff) payoff.hidden = true;
    if (shell) shell.hidden = false;
    renderDemo();
    var demo = document.getElementById('ps-demo');
    if (demo) {
      try {
        demo.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (e) {
        demo.scrollIntoView(true);
      }
    }
    track('demo_restarted', { trade: state.trade });
  }

  function demoAdvanceManual() {
    var steps = demoSteps();
    var from = state.demoStep;
    if (state.demoStep < steps.length - 1) {
      state.demoStep += 1;
      saveState();
      renderDemo();
      if (from === 1 && state.demoStep === 2) autoRunDemo();
    } else {
      showDemoPayoff();
    }
  }

  function setupDemo() {
    var prev = document.getElementById('psDemoPrev');
    var next = document.getElementById('psDemoNext');
    var restart = document.getElementById('psDemoRestart');
    var pauseBtn = document.getElementById('psDemoPause');
    var skipBtn = document.getElementById('psDemoSkip');
    if (!prev || !next) return;
    prev.addEventListener('click', function () {
      if (state.demoAutoRunning && !state.demoPaused) return;
      if (state.demoStep > 0) {
        stopDemoAuto();
        state.demoStep -= 1;
        saveState();
        renderDemo();
      }
    });
    next.addEventListener('click', function () {
      if (state.demoAutoRunning && !state.demoPaused) return;
      stopDemoAuto();
      demoAdvanceManual();
    });
    if (pauseBtn) {
      pauseBtn.addEventListener('click', function () {
        pauseDemoAuto();
      });
    }
    if (skipBtn) {
      skipBtn.addEventListener('click', function () {
        stopDemoAuto();
        showDemoPayoff();
        track('demo_skipped_to_payoff', { trade: state.trade });
      });
    }
    if (restart) {
      restart.addEventListener('click', function () {
        restartDemo();
      });
    }
    var finalReplay = document.getElementById('psFinalReplay');
    if (finalReplay) {
      finalReplay.addEventListener('click', function () {
        if (!state.optedIn) {
          scrollToOptin();
          return;
        }
        restartDemo();
      });
    }
    if (state.optedIn) renderDemo();
  }

  function updateResultCta() {
    var cta = document.getElementById('psResultCta');
    if (!cta) return;
    if (state.optedIn) {
      cta.setAttribute('href', '#ps-demo');
      cta.removeAttribute('data-ps-scroll-optin');
    } else {
      cta.setAttribute('href', '#ps-optin');
      cta.setAttribute('data-ps-scroll-optin', '1');
    }
  }

  function unlockDemo() {
    var demo = document.getElementById('ps-demo');
    var shell = document.querySelector('.ps-demo-shell');
    var gate = document.getElementById('psDemoGate');
    if (demo) {
      demo.classList.remove('is-locked', 'is-hidden');
      demo.removeAttribute('hidden');
    }
    if (shell) {
      shell.classList.remove('is-locked', 'is-hidden');
      shell.hidden = false;
    }
    if (gate) gate.hidden = true;
    updateResultCta();
    updateStickyCtaLink();
  }

  function scrollToDemo() {
    var demo = document.getElementById('ps-demo');
    if (!demo) return;
    try {
      demo.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      demo.scrollIntoView(true);
    }
  }

  function scrollToOptin() {
    var optin = document.getElementById('ps-optin');
    if (!optin) return;
    try {
      optin.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (e) {
      optin.scrollIntoView(true);
    }
  }

  function normalizeOptinTrade(raw) {
    var slug = String(raw || '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/^_+|_+$/g, '');
    var map = {
      hvac: 'hvac',
      plumbing: 'plumbing',
      roofing: 'roofing',
      electrical: 'electrical',
      foundation: 'foundation',
      landscaping: 'landscaping',
      remodeling: 'remodeling',
      other: 'other',
    };
    return map[slug] || (slug ? slug : 'plumbing');
  }

  function readOptinTrade() {
    var sel = document.getElementById('ps-niche');
    var other = document.getElementById('ps-nicheOther');
    if (sel) {
      var val = sel.value;
      if (val === 'other' && other && other.value.trim()) {
        return { trade: 'other', business_type: other.value.trim(), label: other.value.trim() };
      }
      if (val) {
        var label = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : val;
        return { trade: normalizeOptinTrade(val), business_type: val, label: label };
      }
    }
    var search = document.getElementById('ps-nicheSearch');
    if (search && search.value.trim()) {
      return { trade: normalizeOptinTrade(search.value), business_type: search.value.trim(), label: search.value.trim() };
    }
    return { trade: '', business_type: '', label: '' };
  }

  function showPsOptinError(msg) {
    var el = document.getElementById('psOptinError');
    if (!el) return;
    if (!msg) {
      el.hidden = true;
      el.textContent = '';
      return;
    }
    el.hidden = false;
    el.textContent = msg;
  }

  function finishOptinSuccess(email, tradeInfo, crmSaved, ctaSource, eventId) {
    state.optedIn = true;
    state.email = email;
    if (tradeInfo.trade) state.trade = tradeInfo.trade;
    saveState();
    try {
      localStorage.setItem(
        'demoUser',
        JSON.stringify({
          email: email,
          firstName: deriveFirstName(email),
          lastName: '',
          industry: tradeInfo.trade || '',
          businessType: tradeInfo.business_type || '',
        })
      );
    } catch (eUser) {}
    applyJobPersonaToDom();
    decorateTrialLinks();
    unlockDemo();
    renderDemo();
    scrollToDemo();
    track('DemoFormSubmitted', {
      section: 'optin',
      source: ctaSource || 'optin',
      trade: tradeInfo.business_type || tradeInfo.trade,
      crm_saved: !!crmSaved,
      cta_source: ctaSource || 'optin',
    });
    if (crmSaved) {
      pushDemoOptIn(tradeInfo.business_type || tradeInfo.trade, eventId || '');
    }
  }

  function pushDemoOptIn(bizType, eventId) {
    try {
      if (sessionStorage.getItem('jcp_datalayer_demo_opt_in')) return;
      window.dataLayer = window.dataLayer || [];
      var attr = attrPayload();
      var payload = {
        event: 'demo_opt_in',
        lead_type: 'demo',
        source: 'proof_sprint',
        business_type: bizType || '',
        trade: bizType || '',
        utm_source: attr.utm_source || '',
        utm_medium: attr.utm_medium || '',
        utm_campaign: attr.utm_campaign || '',
        utm_content: attr.utm_content || '',
        lp_variant: attr.lp_variant || LP_VARIANT,
        fbclid: attr.fbclid || '',
      };
      if (eventId) {
        payload.event_id = eventId;
        payload.eventID = eventId;
      }
      window.dataLayer.push(payload);
      sessionStorage.setItem('jcp_datalayer_demo_opt_in', '1');
    } catch (err) {}
  }

  function submitPsOptin(attempt, ctaSource) {
    attempt = attempt || 1;
    ctaSource = ctaSource || 'optin';
    var emailEl = document.getElementById('ps-email');
    var email = emailEl ? emailEl.value.trim() : '';
    var tradeInfo = readOptinTrade();
    var btn = document.querySelector('#psOptinForm [type="submit"], #psOptinSubmit');

    showPsOptinError('');
    if (!validEmail(email)) {
      showPsOptinError('Enter a valid work email.');
      track('DemoFormFailed', { section: 'optin', source: ctaSource, reason: 'invalid_email', cta_source: ctaSource });
      if (emailEl) emailEl.focus();
      return Promise.resolve(false);
    }
    if (!String(tradeInfo.business_type || '').trim()) {
      showPsOptinError('Select or enter your trade.');
      track('DemoFormFailed', { section: 'optin', source: ctaSource, reason: 'missing_trade', cta_source: ctaSource });
      var niche = document.getElementById('ps-niche') || document.getElementById('ps-nicheSearch');
      if (niche) niche.focus();
      return Promise.resolve(false);
    }

    track('DemoFormAttempted', {
      section: 'optin',
      source: ctaSource,
      trade: tradeInfo.business_type,
      cta_source: ctaSource,
    });

    var defaultLabel = btn ? btn.textContent : '';
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Building…';
    }

    var restUrl =
      (window.JCP_DEMO_SURVEY && window.JCP_DEMO_SURVEY.rest_url) ||
      '/wp-json/jcp/v1/demo-survey-submit';
    var attr = attrPayload();
    var body = {
      first_name: deriveFirstName(email),
      last_name: '',
      email: email,
      phone: '',
      company: '',
      business_type: tradeInfo.business_type,
      demo_goals: [],
      referral_source: '',
      event: 'demo-opt-in',
      landing_page: location.href,
      utm_source: attr.utm_source || '',
      utm_medium: attr.utm_medium || '',
      utm_campaign: attr.utm_campaign || '',
      utm_content: attr.utm_content || '',
      utm_term: attr.utm_term || '',
      fbclid: attr.fbclid || '',
      referrer: attr.referrer || document.referrer || '',
    };

    return fetch(restUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(function (res) {
        return res
          .json()
          .then(function (json) {
            var captured = !!(json && (json.captured === true || json.success === true));
            return {
              ok: res.ok && captured,
              delivered: !!(json && json.delivered),
              eventId: json && json.event_id ? String(json.event_id) : '',
              json: json,
            };
          })
          .catch(function () {
            return { ok: res.ok, delivered: res.ok, eventId: '', json: null };
          });
      })
      .then(function (result) {
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultLabel || 'Show me my demo →';
        }
        if (!result.ok) {
          if (attempt < 2) return submitPsOptin(attempt + 1, ctaSource);
          showPsOptinError(
            (result.json && result.json.message) ||
              'We couldn’t save your info right now — unlocking your demo anyway.'
          );
          track('DemoFormFailed', {
            section: 'optin',
            source: ctaSource,
            trade: tradeInfo.business_type,
            crm_saved: false,
            cta_source: ctaSource,
          });
          // Soft-continue UX only — server should have queued; do not treat as CRM-saved.
          finishOptinSuccess(email, tradeInfo, false, ctaSource, '');
          return false;
        }
        if (!result.delivered) {
          showPsOptinError('Saved — CRM sync will retry in the background.');
        }
        finishOptinSuccess(email, tradeInfo, true, ctaSource, result.eventId);
        return true;
      })
      .catch(function () {
        if (attempt < 2) return submitPsOptin(attempt + 1, ctaSource);
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultLabel || 'Show me my demo →';
        }
        showPsOptinError('Network error — unlocking your demo anyway.');
        track('DemoFormFailed', { section: 'optin', source: ctaSource, reason: 'network', cta_source: ctaSource });
        finishOptinSuccess(email, tradeInfo, false, ctaSource, '');
        return false;
      });
  }

  function setupOptin() {
    var form = document.getElementById('psOptinForm');
    if (!form) return;

    var sel = document.getElementById('ps-niche');
    if (sel && !sel.options.length) {
      [
        ['', 'Select your trade'],
        ['hvac', 'HVAC'],
        ['plumbing', 'Plumbing'],
        ['roofing', 'Roofing'],
        ['electrical', 'Electrical'],
        ['foundation', 'Foundation'],
        ['landscaping', 'Landscaping'],
        ['remodeling', 'Remodeling'],
        ['other', 'Other'],
      ].forEach(function (pair) {
        var opt = document.createElement('option');
        opt.value = pair[0];
        opt.textContent = pair[1];
        sel.appendChild(opt);
      });
    }

    var otherWrap = document.getElementById('ps-nicheOtherWrap');
    if (sel && otherWrap) {
      sel.addEventListener('change', function () {
        otherWrap.hidden = sel.value !== 'other';
      });
      otherWrap.hidden = sel.value !== 'other';
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var source = form.getAttribute('data-ps-source') || 'optin';
      submitPsOptin(1, source);
    });

    if (state.trade && sel) {
      sel.value = state.trade;
    }
    if (state.email) {
      var emailEl = document.getElementById('ps-email');
      if (emailEl) emailEl.value = state.email;
    }

    var optinSec = document.getElementById('ps-optin');
    if (optinSec && 'IntersectionObserver' in window) {
      var formViewed = false;
      var formIo = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting || formViewed) return;
            formViewed = true;
            try {
              if (sessionStorage.getItem('jcp_ps_DemoFormViewed')) return;
              sessionStorage.setItem('jcp_ps_DemoFormViewed', '1');
            } catch (e) {}
            track('DemoFormViewed', { section: 'optin', source: 'optin', cta_source: 'optin' });
            formIo.unobserve(optinSec);
          });
        },
        { threshold: 0.2 }
      );
      formIo.observe(optinSec);
    }
  }

  function setupDemoScrollGate() {
    document.addEventListener(
      'click',
      function (e) {
        if (state.optedIn) return;
        var t = e.target && e.target.closest ? e.target.closest('a[href="#ps-demo"], [data-ps-scroll-optin]') : null;
        if (!t) return;
        e.preventDefault();
        scrollToOptin();
        track('demo_gate_optin_redirect', { source: t.getAttribute('data-ps-source') || '' });
      },
      true
    );
  }

  function setupMapTabs() {
    var root = document.querySelector('#ps-proof .jcp-lf-case__locations');
    if (!root) return;
    var locations = root.querySelectorAll('.jcp-lf-case__location');
    if (locations.length < 2) return;

    var tabs = document.createElement('div');
    tabs.className = 'ps-map-tabs';
    tabs.setAttribute('role', 'tablist');
    tabs.setAttribute('aria-label', 'Locations');

    function isMobile() {
      try {
        return window.matchMedia('(max-width: 767px)').matches;
      } catch (e) {
        return false;
      }
    }

    function syncView(activeIndex) {
      locations.forEach(function (loc, i) {
        var on = !isMobile() || i === activeIndex;
        loc.hidden = !on;
        loc.classList.toggle('is-tab-active', on);
      });
      Array.prototype.forEach.call(tabs.children, function (btn, i) {
        btn.classList.toggle('is-active', i === activeIndex);
        btn.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
      });
    }

    locations.forEach(function (loc, i) {
      var nameEl = loc.querySelector('.jcp-lf-case__location-name');
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ps-map-tab';
      btn.setAttribute('role', 'tab');
      btn.textContent = nameEl ? nameEl.textContent.trim() : 'Location ' + (i + 1);
      btn.addEventListener('click', function () {
        syncView(i);
      });
      tabs.appendChild(btn);
    });

    root.parentNode.insertBefore(tabs, root);
    syncView(0);

    window.addEventListener('resize', function () {
      var active = 0;
      Array.prototype.forEach.call(tabs.children, function (btn, i) {
        if (btn.classList.contains('is-active')) active = i;
      });
      syncView(active);
    });
  }

  function updateStickyCtaLink() {
    var sticky = document.getElementById('psStickyCta');
    if (!sticky) return;
    var a = sticky.querySelector('a');
    if (!a) return;
    if (state.optedIn) {
      var base =
        (window.JCP_ONBOARDING && window.JCP_ONBOARDING.url) ||
        'https://app.jobcapturepro.com/onboarding';
      a.setAttribute('href', base);
      a.textContent = 'Start free 14-day trial →';
      a.setAttribute('data-ps-trial', '');
      a.setAttribute('data-ps-source', 'sticky_mobile');
      try {
        var params = attrParams();
        var u = new URL(a.getAttribute('href') || base, location.origin);
        Object.keys(params).forEach(function (k) {
          if (!u.searchParams.get(k)) u.searchParams.set(k, params[k]);
        });
        u.searchParams.set('jcp_surface', 'proof_sprint_sticky_mobile');
        if (!u.searchParams.get('utm_content')) u.searchParams.set('utm_content', 'proof_sprint_sticky_mobile');
        a.href = u.toString();
      } catch (eHref) {}
    } else {
      a.setAttribute('href', '#ps-optin');
      a.removeAttribute('data-ps-trial');
      a.textContent = 'See it on my business →';
    }
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
    if (!sticky || !hero) return;
    if (window.matchMedia && window.matchMedia('(min-width: 768px)').matches) return;

    updateStickyCtaLink();

    var hideReasons = { hero: false, optin: false, focus: false };
    function refreshSticky() {
      var hide = hideReasons.hero || hideReasons.optin || hideReasons.focus;
      sticky.hidden = hide;
      document.body.classList.toggle('has-sticky-cta', !hide);
    }

    if ('IntersectionObserver' in window) {
      var heroIo = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            hideReasons.hero = entry.isIntersecting;
            refreshSticky();
          });
        },
        { threshold: 0.05 }
      );
      heroIo.observe(hero);

      var optin = document.getElementById('ps-optin');
      if (optin) {
        var optinIo = new IntersectionObserver(
          function (entries) {
            entries.forEach(function (entry) {
              hideReasons.optin = entry.isIntersecting;
              refreshSticky();
            });
          },
          { threshold: 0.08 }
        );
        optinIo.observe(optin);
      }
    }

    document.addEventListener(
      'focusin',
      function (e) {
        var t = e.target;
        var inOptin = t && t.closest && t.closest('#ps-optin, #psOptinForm');
        var inDemoForm = t && t.closest && t.closest('#ps-demo form, #psDemoGate form');
        hideReasons.focus = !!(inOptin || (state.optedIn && inDemoForm));
        refreshSticky();
      },
      true
    );
    document.addEventListener(
      'focusout',
      function () {
        window.setTimeout(function () {
          var active = document.activeElement;
          var inOptin = active && active.closest && active.closest('#ps-optin, #psOptinForm');
          var inDemoForm = active && active.closest && active.closest('#ps-demo form, #psDemoGate form');
          hideReasons.focus = !!(inOptin || (state.optedIn && inDemoForm));
          refreshSticky();
        }, 0);
      },
      true
    );

    refreshSticky();
  }

  function setupTrackedClicks() {
    document.addEventListener(
      'click',
      function (e) {
        var t = e.target && e.target.closest ? e.target.closest('[data-ps-track]') : null;
        if (!t) return;
        track(t.getAttribute('data-ps-track') || 'cta_click', {
          source: t.getAttribute('data-ps-source') || '',
          cta_source: t.getAttribute('data-ps-source') || '',
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
    if (!state.trade) state.trade = 'plumbing';
    applyJobPersonaToDom();
    decorateTrialLinks();
    setupAssessment();
    setupOptin();
    setupDemoScrollGate();
    setupDemo();
    setupMapTabs();
    setupReveals();
    setupStickyCta();
    setupTrackedClicks();
    hideChat();
    runHeroTheater();
    try {
      if (!sessionStorage.getItem('jcp_ps_PaidLandingView')) {
        sessionStorage.setItem('jcp_ps_PaidLandingView', '1');
        track('PaidLandingView', { section: 'proof_sprint' });
      }
    } catch (eView) {
      track('PaidLandingView', { section: 'proof_sprint' });
    }

    updateResultCta();
    if (state.optedIn) {
      unlockDemo();
      renderDemo();
    }

    if (state.annual > 0) {
      var result = document.getElementById('ps-result');
      if (result) result.hidden = false;
      var annualEl = document.getElementById('psAnnualJobs');
      var unusedEl = document.getElementById('psUnusedJobs');
      if (annualEl) annualEl.textContent = state.annual.toLocaleString();
      if (unusedEl) unusedEl.textContent = state.unused.toLocaleString();
      updateResultCta();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
