/**
 * Proof Gap Survey — deterministic state machine (Phase 1 shell).
 * Isolated from /demo/ and /proof-sprint/ behavior.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'jcp_proof_gap_state_v1';
  var TTL_MS = 7 * 24 * 60 * 60 * 1000;
  var LEAD_EVENT_ID_KEY = 'jcp_pg_lead_event_id';
  var AUTO_ADVANCE_MS = 240;
  var LP_VARIANT = 'proof_gap_survey_v1';

  var STATES = [
    'trade',
    'current_workflow',
    'jobs_per_week',
    'public_proof_percentage',
    'proof_gap_result',
    'email_capture',
    'product_reveal',
    'trial_bridge',
  ];

  var PHASE_BY_STATE = {
    trade: 'work',
    current_workflow: 'work',
    jobs_per_week: 'work',
    public_proof_percentage: 'gap',
    proof_gap_result: 'gap',
    email_capture: 'plan',
    product_reveal: 'plan',
    trial_bridge: 'plan',
  };

  var boot = {};
  try {
    var bootEl = document.getElementById('pg-boot');
    if (bootEl) boot = JSON.parse(bootEl.textContent || '{}') || {};
  } catch (eBoot) {
    boot = {};
  }

  var trades = boot.trades || {};
  var workflows = boot.workflows || {};
  var jobsBuckets = boot.jobsBuckets || {};
  var proofPct = boot.proofPct || {};

  var state = createEmptyState();
  var advanceTimer = null;
  var startedTracked = false;
  var landingTracked = false;
  var questionViewed = {};

  function createEmptyState() {
    return {
      survey_id: boot.surveyId || 'proof_gap_survey_v1',
      survey_version: boot.surveyVersion || '1',
      session_id: '',
      current_state: 'trade',
      completed_states: [],
      trade: '',
      current_workflow: '',
      jobs_per_week_bucket: '',
      annual_jobs_min: null,
      annual_jobs_max: null,
      public_proof_percentage: '',
      proof_gap_band: '',
      email_captured: false,
      product_reveal_completed: false,
      trial_cta_clicked: false,
      handoff_token: '',
      attribution: {},
      created_at: Date.now(),
      updated_at: Date.now(),
    };
  }

  function uuid() {
    try {
      if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    } catch (e) {}
    return 'pg_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 10);
  }

  function deviceClass() {
    var w = window.innerWidth || 0;
    if (w < 768) return 'mobile';
    if (w < 1280) return 'tablet';
    return 'desktop';
  }

  function attrPayload() {
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        return window.JCPLeadAttribution.getPayload() || {};
      }
    } catch (e) {}
    return {};
  }

  function track(name, props) {
    props = props || {};
    var payload = {
      event: name,
      survey_id: state.survey_id,
      survey_version: state.survey_version,
      session_id: state.session_id,
      lp_variant: LP_VARIANT,
      device_class: deviceClass(),
      current_state: state.current_state,
    };
    Object.keys(props).forEach(function (k) {
      if (props[k] !== undefined && props[k] !== null && props[k] !== '') payload[k] = props[k];
    });
    // Hard ban PII keys
    delete payload.email;
    delete payload.phone;
    delete payload.first_name;
    delete payload.last_name;
    delete payload.business_name;
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(payload);
    } catch (e) {}
  }

  function saveState() {
    state.updated_at = Date.now();
    state.attribution = Object.assign({}, state.attribution || {}, attrPayload());
    try {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          expires: Date.now() + TTL_MS,
          state: state,
        })
      );
    } catch (e) {}
  }

  function loadState() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return false;
      var parsed = JSON.parse(raw);
      if (!parsed || !parsed.state || !parsed.expires || parsed.expires < Date.now()) {
        localStorage.removeItem(STORAGE_KEY);
        return false;
      }
      var s = parsed.state;
      if (s.survey_id !== (boot.surveyId || 'proof_gap_survey_v1')) return false;
      state = Object.assign(createEmptyState(), s);
      return true;
    } catch (e) {
      return false;
    }
  }

  function ensureSession() {
    if (!state.session_id) {
      state.session_id = uuid();
      saveState();
    }
  }

  function stateIndex(id) {
    var i = STATES.indexOf(id);
    return i < 0 ? 0 : i;
  }

  function markCompleted(id) {
    if (state.completed_states.indexOf(id) === -1) {
      state.completed_states.push(id);
    }
  }

  function answerForState(id) {
    if (id === 'jobs_per_week') return state.jobs_per_week_bucket;
    if (id === 'trade') return state.trade;
    if (id === 'current_workflow') return state.current_workflow;
    if (id === 'public_proof_percentage') return state.public_proof_percentage;
    return '';
  }

  function firstIncomplete() {
    for (var i = 0; i < STATES.length; i++) {
      var id = STATES[i];
      if (id === 'trade' || id === 'current_workflow' || id === 'jobs_per_week' || id === 'public_proof_percentage') {
        if (!answerForState(id)) return id;
        continue;
      }
      if (id === 'proof_gap_result') {
        if (state.completed_states.indexOf('proof_gap_result') === -1) return id;
        continue;
      }
      if (id === 'email_capture') {
        if (!state.email_captured) return id;
        continue;
      }
      if (id === 'product_reveal') {
        if (!state.product_reveal_completed) return id;
        continue;
      }
      if (id === 'trial_bridge') {
        return id;
      }
    }
    return 'trial_bridge';
  }

  function updateProgress() {
    var idx = stateIndex(state.current_state);
    var pct = ((idx + 1) / STATES.length) * 100;
    var fill = document.getElementById('pgProgressFill');
    if (fill) fill.style.width = pct + '%';
    var phase = PHASE_BY_STATE[state.current_state] || 'work';
    var order = ['work', 'gap', 'plan'];
    var activeIdx = order.indexOf(phase);
    document.querySelectorAll('.pg-progress__phase').forEach(function (el) {
      var p = el.getAttribute('data-phase');
      var pi = order.indexOf(p);
      el.classList.toggle('is-active', p === phase);
      el.classList.toggle('is-done', pi < activeIdx);
    });
  }

  function setBackVisible() {
    var back = document.getElementById('pgBack');
    if (!back) return;
    back.hidden = stateIndex(state.current_state) === 0;
  }

  function renderChoices(field, map, selectedKey) {
    var host = document.querySelector('[data-pg-choices="' + field + '"]');
    if (!host) return;
    host.innerHTML = '';
    Object.keys(map).forEach(function (key) {
      var label = typeof map[key] === 'object' ? map[key].label || key : map[key];
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'pg-choice' + (selectedKey === key ? ' is-selected' : '');
      btn.setAttribute('data-pg-field', field);
      btn.setAttribute('data-pg-value', key);
      btn.textContent = label;
      btn.addEventListener('click', function () {
        onChoice(field, key, btn);
      });
      host.appendChild(btn);
    });
  }

  function clearAdvance() {
    if (advanceTimer) {
      clearTimeout(advanceTimer);
      advanceTimer = null;
    }
  }

  function scheduleAdvance(next) {
    clearAdvance();
    var delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : AUTO_ADVANCE_MS;
    advanceTimer = setTimeout(function () {
      goTo(next);
    }, delay);
  }

  function onChoice(field, key, btn) {
    document.querySelectorAll('.pg-choice[data-pg-field="' + field + '"]').forEach(function (b) {
      b.classList.remove('is-selected');
    });
    if (btn) btn.classList.add('is-selected');

    var qIndex = stateIndex(state.current_state) + 1;
    track('SurveyQuestionAnswered', {
      question_index: qIndex,
      question_id: state.current_state,
      answer_key: key,
      trade: state.trade || (field === 'trade' ? key : ''),
      current_workflow: state.current_workflow || (field === 'current_workflow' ? key : ''),
      jobs_per_week_bucket: state.jobs_per_week_bucket || (field === 'jobs_per_week' ? key : ''),
      public_proof_percentage: state.public_proof_percentage || (field === 'public_proof_percentage' ? key : ''),
    });

    if (!startedTracked) {
      startedTracked = true;
      track('SurveyStarted', { question_id: 'trade' });
    }

    if (field === 'trade') {
      state.trade = key;
      markCompleted('trade');
      saveState();
      scheduleAdvance('current_workflow');
      return;
    }

    if (field === 'current_workflow') {
      state.current_workflow = key;
      markCompleted('current_workflow');
      saveState();
      var fb = document.querySelector('[data-pg-feedback="workflow"]');
      if (fb) {
        if (key === 'housecall_pro' || key === 'companycam') {
          fb.hidden = false;
          fb.textContent = 'Supported workflow noted — we’ll treat that as your source later.';
        } else {
          fb.hidden = true;
          fb.textContent = '';
        }
      }
      scheduleAdvance('jobs_per_week');
      return;
    }

    if (field === 'jobs_per_week') {
      state.jobs_per_week_bucket = key;
      var bucket = jobsBuckets[key] || {};
      state.annual_jobs_min = typeof bucket.min === 'number' ? bucket.min : null;
      state.annual_jobs_max = typeof bucket.max === 'number' ? bucket.max : null;
      markCompleted('jobs_per_week');
      saveState();
      var jfb = document.querySelector('[data-pg-feedback="jobs"]');
      if (jfb && state.annual_jobs_min != null && state.annual_jobs_max != null) {
        jfb.hidden = false;
        jfb.textContent =
          'That’s roughly ' +
          state.annual_jobs_min.toLocaleString() +
          '–' +
          state.annual_jobs_max.toLocaleString() +
          ' jobs per year (range, not an exact count).';
      }
      scheduleAdvance('public_proof_percentage');
      return;
    }

    if (field === 'public_proof_percentage') {
      state.public_proof_percentage = key;
      state.proof_gap_band = key;
      markCompleted('public_proof_percentage');
      saveState();
      scheduleAdvance('proof_gap_result');
    }
  }

  function renderResult() {
    var meta = document.getElementById('pgResultMeta');
    var completed = document.getElementById('pgResultCompleted');
    var pub = document.getElementById('pgResultPublic');
    if (meta) {
      meta.innerHTML =
        '<li><span>Trade</span> <strong>' +
        escapeHtml(trades[state.trade] || state.trade || '—') +
        '</strong></li>' +
        '<li><span>Weekly jobs</span> <strong>' +
        escapeHtml((jobsBuckets[state.jobs_per_week_bucket] || {}).weekly_label || state.jobs_per_week_bucket || '—') +
        '</strong></li>' +
        '<li><span>Annual range</span> <strong>' +
        escapeHtml(formatAnnualRange()) +
        '</strong></li>' +
        '<li><span>Public proof band</span> <strong>' +
        escapeHtml(proofPct[state.public_proof_percentage] || state.public_proof_percentage || '—') +
        '</strong></li>' +
        '<li><span>Workflow</span> <strong>' +
        escapeHtml(workflows[state.current_workflow] || state.current_workflow || '—') +
        '</strong></li>';
    }
    if (completed) completed.textContent = formatAnnualRange();
    if (pub) pub.textContent = proofPct[state.public_proof_percentage] || '—';
  }

  function formatAnnualRange() {
    if (state.annual_jobs_min == null || state.annual_jobs_max == null) return '—';
    return state.annual_jobs_min.toLocaleString() + '–' + state.annual_jobs_max.toLocaleString() + ' / year';
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function sourceLabel() {
    if (state.current_workflow === 'housecall_pro') return 'Housecall Pro';
    if (state.current_workflow === 'companycam') return 'CompanyCam';
    if (state.current_workflow === 'other_crm') return 'Your field / CRM workflow';
    if (state.current_workflow === 'phones_camera_roll') return 'Phones / camera roll';
    if (state.current_workflow === 'group_text_shared_folder') return 'Group text / shared folder';
    if (state.current_workflow === 'scattered') return 'Scattered sources';
    return 'JCP app';
  }

  function renderReveal() {
    var src = document.getElementById('pgRevealSource');
    if (src) src.textContent = sourceLabel();
  }

  function renderTrialSummary() {
    var list = document.getElementById('pgTrialSummary');
    if (!list) return;
    list.innerHTML =
      '<li><span>Trade</span><strong>' +
      escapeHtml(trades[state.trade] || state.trade || '—') +
      '</strong></li>' +
      '<li><span>Workflow</span><strong>' +
      escapeHtml(workflows[state.current_workflow] || state.current_workflow || '—') +
      '</strong></li>' +
      '<li><span>Weekly jobs</span><strong>' +
      escapeHtml((jobsBuckets[state.jobs_per_week_bucket] || {}).weekly_label || '—') +
      '</strong></li>' +
      '<li><span>Annual range</span><strong>' +
      escapeHtml(formatAnnualRange()) +
      '</strong></li>' +
      '<li><span>Public proof</span><strong>' +
      escapeHtml(proofPct[state.public_proof_percentage] || '—') +
      '</strong></li>';
    updateTrialHref();
  }

  function readUrlParam(key) {
    try {
      return new URLSearchParams(window.location.search).get(key) || '';
    } catch (e) {
      return '';
    }
  }

  function updateTrialHref() {
    var a = document.getElementById('pgTrialCta');
    if (!a) return;
    var base = boot.trialBase || 'https://app.jobcapturepro.com/onboarding';
    try {
      var u = new URL(base, window.location.origin);
      var attr = Object.assign({}, attrPayload() || {}, state.attribution || {});
      var passKeys = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbclid',
        'ttclid',
        'campaign_id',
        'adset_id',
        'ad_id',
      ];
      passKeys.forEach(function (k) {
        var v = attr[k] || readUrlParam(k);
        if (v) u.searchParams.set(k, v);
      });
      u.searchParams.set('lp_variant', LP_VARIANT);
      u.searchParams.set('survey_session_id', state.session_id);
      u.searchParams.set('jcp_surface', 'proof_gap_survey_trial');
      if (state.trade) u.searchParams.set('industry', mapTradeToIndustry(state.trade));
      if (state.handoff_token) u.searchParams.set('pg_handoff', state.handoff_token);
      // Prefer existing handoff decorator when present (may add email via demoUser).
      a.href = u.toString();
      if (window.JCPOnboardingHandoff && typeof window.JCPOnboardingHandoff.decorate === 'function') {
        a.href = window.JCPOnboardingHandoff.decorate(a.href) || a.href;
      } else if (typeof window.jcpDecorateOnboardingUrl === 'function') {
        a.href = window.jcpDecorateOnboardingUrl(a.href) || a.href;
      }
    } catch (e) {
      a.href = base;
    }
  }

  function mapTradeToIndustry(trade) {
    var map = {
      hvac: 'hvac',
      plumbing: 'plumbing',
      roofing: 'roofing',
      tree_service: 'tree-service',
      landscaping: 'landscaping',
      electrical: 'electrical',
      remodeling: 'remodeling',
    };
    return map[trade] || trade || '';
  }

  function goTo(id) {
    clearAdvance();
    if (STATES.indexOf(id) === -1) return;
    state.current_state = id;
    saveState();

    document.querySelectorAll('[data-pg-state]').forEach(function (el) {
      el.hidden = el.getAttribute('data-pg-state') !== id;
    });

    updateProgress();
    setBackVisible();

    var qIndex = stateIndex(id) + 1;
    if (!questionViewed[id]) {
      questionViewed[id] = true;
      if (['trade', 'current_workflow', 'jobs_per_week', 'public_proof_percentage', 'email_capture'].indexOf(id) !== -1) {
        track('SurveyQuestionViewed', {
          question_index: qIndex,
          question_id: id,
          trade: state.trade,
          current_workflow: state.current_workflow,
          jobs_per_week_bucket: state.jobs_per_week_bucket,
          public_proof_percentage: state.public_proof_percentage,
        });
      }
    }

    if (id === 'proof_gap_result') {
      renderResult();
      track('SurveyResultViewed', {
        trade: state.trade,
        current_workflow: state.current_workflow,
        jobs_per_week_bucket: state.jobs_per_week_bucket,
        public_proof_percentage: state.public_proof_percentage,
        annual_jobs_min: state.annual_jobs_min,
        annual_jobs_max: state.annual_jobs_max,
      });
    }
    if (id === 'email_capture') {
      track('EmailCaptureViewed', { trade: state.trade });
    }
    if (id === 'product_reveal') {
      renderReveal();
      track('ProductRevealStarted', {
        trade: state.trade,
        current_workflow: state.current_workflow,
        cta_source: 'product_reveal',
      });
    }
    if (id === 'trial_bridge') {
      renderTrialSummary();
      track('TrialCTAViewed', { trade: state.trade, cta_source: 'trial_bridge' });
    }

    try {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
      window.scrollTo(0, 0);
    }
  }

  function goBack() {
    clearAdvance();
    var idx = stateIndex(state.current_state);
    if (idx <= 0) return;
    goTo(STATES[idx - 1]);
  }

  function getOrCreateLeadEventId() {
    try {
      var existing = sessionStorage.getItem(LEAD_EVENT_ID_KEY);
      if (existing && /^[A-Za-z0-9_-]{8,64}$/.test(existing)) return existing;
    } catch (e) {}
    var id = uuid().replace(/[^A-Za-z0-9_-]/g, '').slice(0, 36);
    try {
      sessionStorage.setItem(LEAD_EVENT_ID_KEY, id);
    } catch (e2) {}
    return id;
  }

  function deriveFirstName(email) {
    var local = String(email || '').split('@')[0] || 'there';
    return local.replace(/[._-]+/g, ' ').trim().slice(0, 40) || 'there';
  }

  function pushAcquisitionLead(eventId) {
    try {
      if (sessionStorage.getItem('jcp_datalayer_pg_opt_in')) return;
      window.dataLayer = window.dataLayer || [];
      var attr = attrPayload();
      var payload = {
        event: 'demo_opt_in',
        lead_type: 'proof_gap_survey',
        source: 'proof_gap_survey',
        business_type: state.trade || '',
        trade: state.trade || '',
        utm_source: attr.utm_source || '',
        utm_medium: attr.utm_medium || '',
        utm_campaign: attr.utm_campaign || '',
        utm_content: attr.utm_content || '',
        lp_variant: attr.lp_variant || LP_VARIANT,
        fbclid: attr.fbclid || '',
        funnel_surface: 'proof_gap_survey',
        session_id: state.session_id,
      };
      if (eventId) {
        payload.event_id = eventId;
        payload.eventID = eventId;
      }
      window.dataLayer.push(payload);
      sessionStorage.setItem('jcp_datalayer_pg_opt_in', '1');
    } catch (err) {}
  }

  function persistDemoUser(email) {
    try {
      var user = {
        email: email,
        firstName: deriveFirstName(email),
        lastName: '',
        niche: state.trade || '',
        industry: mapTradeToIndustry(state.trade),
        trade: state.trade || '',
        source: 'proof_gap_survey',
      };
      localStorage.setItem('demoUser', JSON.stringify(user));
    } catch (e) {}
  }

  function submitEmail(ev) {
    if (ev) ev.preventDefault();
    var input = document.getElementById('pgEmail');
    var err = document.getElementById('pgEmailError');
    var btn = document.getElementById('pgEmailSubmit');
    var email = input ? String(input.value || '').trim() : '';
    if (err) {
      err.hidden = true;
      err.textContent = '';
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      if (err) {
        err.hidden = false;
        err.textContent = 'Enter a valid work email.';
      }
      if (input) input.focus();
      return;
    }

    var defaultLabel = btn ? btn.textContent : '';
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Saving…';
    }

    var eventId = getOrCreateLeadEventId();
    var attr = attrPayload();
    var body = {
      email: email,
      business_type: state.trade || '',
      survey_session_id: state.session_id,
      survey_version: state.survey_version,
      current_workflow: state.current_workflow || '',
      jobs_per_week_bucket: state.jobs_per_week_bucket || '',
      public_proof_percentage: state.public_proof_percentage || '',
      event_id: eventId,
      landing_page: location.href,
      lp_variant: attr.lp_variant || LP_VARIANT,
      funnel_surface: 'proof_gap_survey',
      utm_source: attr.utm_source || '',
      utm_medium: attr.utm_medium || '',
      utm_campaign: attr.utm_campaign || '',
      utm_content: attr.utm_content || '',
      utm_term: attr.utm_term || '',
      fbclid: attr.fbclid || '',
      referrer: attr.referrer || document.referrer || '',
    };

    var restUrl = boot.restUrl || '/wp-json/jcp/v1/proof-gap-survey-submit';

    fetch(restUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(function (res) {
        return res.json().then(function (json) {
          return { ok: res.ok && !!(json && (json.captured === true || json.success === true)), json: json };
        });
      })
      .then(function (result) {
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultLabel || 'Continue →';
        }
        if (!result.ok) {
          if (err) {
            err.hidden = false;
            err.textContent =
              (result.json && result.json.message) ||
              'We couldn’t save that right now. Please try again.';
          }
          return;
        }

        state.email_captured = true;
        state.handoff_token = (result.json && result.json.handoff_token) || '';
        markCompleted('email_capture');
        persistDemoUser(email);
        saveState();

        var returnedId = result.json && result.json.event_id ? String(result.json.event_id) : eventId;
        try {
          if (returnedId) sessionStorage.setItem(LEAD_EVENT_ID_KEY, returnedId);
        } catch (eId) {}

        track('EmailSubmitted', {
          trade: state.trade,
          current_workflow: state.current_workflow,
          jobs_per_week_bucket: state.jobs_per_week_bucket,
          public_proof_percentage: state.public_proof_percentage,
          cta_source: 'email_capture',
        });

        // Reuse canonical Meta Lead hook (GTM maps demo_opt_in → Lead + event_id dedupe).
        // Do not invent a second browser Lead event name.
        pushAcquisitionLead(returnedId);

        goTo('product_reveal');
      })
      .catch(function () {
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultLabel || 'Continue →';
        }
        if (err) {
          err.hidden = false;
          err.textContent = 'Network error — please try again.';
        }
      });
  }

  function bind() {
    var back = document.getElementById('pgBack');
    if (back) back.addEventListener('click', goBack);

    var resultCta = document.getElementById('pgResultCta');
    if (resultCta) {
      resultCta.addEventListener('click', function () {
        markCompleted('proof_gap_result');
        saveState();
        goTo('email_capture');
      });
    }

    var emailForm = document.getElementById('pgEmailForm');
    if (emailForm) emailForm.addEventListener('submit', submitEmail);

    var revealBtn = document.getElementById('pgRevealContinue');
    if (revealBtn) {
      revealBtn.addEventListener('click', function () {
        state.product_reveal_completed = true;
        markCompleted('product_reveal');
        saveState();
        track('ProductRevealCompleted', {
          trade: state.trade,
          current_workflow: state.current_workflow,
        });
        goTo('trial_bridge');
      });
    }

    var trial = document.getElementById('pgTrialCta');
    if (trial) {
      trial.addEventListener('click', function () {
        state.trial_cta_clicked = true;
        markCompleted('trial_bridge');
        saveState();
        track('TrialCTAClicked', {
          trade: state.trade,
          cta_source: 'trial_bridge',
        });
        updateTrialHref();
      });
    }

    window.addEventListener('pagehide', function () {
      track('SurveyExited', {
        question_id: state.current_state,
        trade: state.trade,
      });
    });
  }

  function initChoices() {
    renderChoices('trade', trades, state.trade);
    renderChoices('current_workflow', workflows, state.current_workflow);
    renderChoices('jobs_per_week', jobsBuckets, state.jobs_per_week_bucket);
    renderChoices('public_proof_percentage', proofPct, state.public_proof_percentage);
  }

  function init() {
    var resumed = loadState();
    ensureSession();
    state.attribution = Object.assign({}, state.attribution || {}, attrPayload());
    saveState();

    if (!landingTracked) {
      landingTracked = true;
      track('SurveyLandingViewed', {});
      // Canonical paid LP view for GTM (no Lead)
      try {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: 'PaidLandingView',
          page_path: location.pathname,
          lp_variant: LP_VARIANT,
          session_id: state.session_id,
        });
      } catch (e) {}
    }

    bind();
    initChoices();

    var startState = 'trade';
    if (resumed) {
      track('SurveyResumed', {
        question_id: state.current_state,
        trade: state.trade,
      });
      if (state.email_captured && state.product_reveal_completed) {
        startState = 'trial_bridge';
      } else if (state.email_captured) {
        startState = state.product_reveal_completed ? 'trial_bridge' : 'product_reveal';
      } else {
        startState = firstIncomplete();
      }
    }

    goTo(startState);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
