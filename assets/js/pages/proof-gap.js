/**
 * Proof Gap Survey — Phase 2 guided diagnosis state machine.
 * Isolated from /demo/ and /proof-sprint/ behavior.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'jcp_proof_gap_state_v1';
  var TTL_MS = 7 * 24 * 60 * 60 * 1000;
  var LEAD_EVENT_ID_KEY = 'jcp_pg_lead_event_id';
  var AUTO_ADVANCE_MS = 250;
  var LP_VARIANT = 'proof_gap_survey_v1';

  var STATES = [
    'welcome',
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
    welcome: null,
    trade: 'work',
    current_workflow: 'work',
    jobs_per_week: 'work',
    public_proof_percentage: 'gap',
    proof_gap_result: 'gap',
    email_capture: 'plan',
    product_reveal: 'plan',
    trial_bridge: 'plan',
  };

  var JOB_EXAMPLES = {
    hvac: 'HVAC service call',
    plumbing: 'Plumbing repair',
    electrical: 'Electrical install',
    roofing: 'Roofing project',
    remodeling: 'Remodel finish',
    painting: 'Interior paint job',
    landscaping: 'Landscaping job',
    garage_door: 'Garage door service',
    pest_control: 'Pest control visit',
    tree_service: 'Tree service job',
    power_washing: 'Power washing job',
    other: 'Finished field job',
  };

  var WORKFLOW_INSIGHTS = {
    housecall_pro: {
      variant: 'housecall_pro',
      headline: 'Good news — your crew may not need another field workflow.',
      body: 'With a supported connection, JobCapturePro can use job information and photos your team is already capturing in Housecall Pro.',
    },
    companycam: {
      variant: 'companycam',
      headline: 'Good news — your crew may not need another photo workflow.',
      body: 'With a supported connection, JobCapturePro can work with job photos your team is already capturing in CompanyCam.',
    },
    other_crm: {
      variant: 'other_crm',
      headline: 'You may be able to keep the workflow your crew already uses.',
      body: 'JobCapturePro can work with supported systems and workflows so the goal is not to create another marketing task for your technicians.',
    },
    phones_camera_roll: {
      variant: 'phones_camera_roll',
      headline: 'This is where a lot of proof gets stranded.',
      body: 'The photos exist — but unless someone does something with them after the job, that finished work may never become public proof.',
    },
    group_text_shared_folder: {
      variant: 'group_text_shared_folder',
      headline: 'The proof already exists.',
      body: 'It’s just stored somewhere customers cannot automatically see it. Turning it into marketing becomes another manual task.',
    },
    scattered: {
      variant: 'scattered',
      headline: 'That’s exactly the problem.',
      body: 'When finished-job proof lives in several places, consistently turning every job into public marketing becomes another job of its own.',
    },
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
  var insightVisible = false;

  function createEmptyState() {
    return {
      survey_id: boot.surveyId || 'proof_gap_survey_v1',
      survey_version: boot.surveyVersion || '2',
      session_id: '',
      current_state: 'welcome',
      completed_states: [],
      trade: '',
      current_workflow: '',
      jobs_per_week_bucket: '',
      annual_jobs_min: null,
      annual_jobs_max: null,
      public_proof_percentage: '',
      proof_gap_band: '',
      unused_jobs_min: null,
      unused_jobs_max: null,
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
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ expires: Date.now() + TTL_MS, state: state }));
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
      if (!state.current_state || STATES.indexOf(state.current_state) === -1) state.current_state = 'welcome';
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
    if (state.completed_states.indexOf(id) === -1) state.completed_states.push(id);
  }

  function invalidateDownstream(fromField) {
    var order = ['trade', 'current_workflow', 'jobs_per_week', 'public_proof_percentage', 'proof_gap_result'];
    var idx = order.indexOf(fromField);
    if (idx < 0) return;
    for (var i = idx + 1; i < order.length; i++) {
      var id = order[i];
      state.completed_states = state.completed_states.filter(function (c) {
        return c !== id;
      });
      if (id === 'current_workflow') state.current_workflow = '';
      if (id === 'jobs_per_week') {
        state.jobs_per_week_bucket = '';
        state.annual_jobs_min = null;
        state.annual_jobs_max = null;
      }
      if (id === 'public_proof_percentage') {
        state.public_proof_percentage = '';
        state.proof_gap_band = '';
        state.unused_jobs_min = null;
        state.unused_jobs_max = null;
      }
    }
    if (fromField === 'jobs_per_week' || fromField === 'public_proof_percentage' || fromField === 'trade' || fromField === 'current_workflow') {
      state.unused_jobs_min = null;
      state.unused_jobs_max = null;
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
      if (id === 'welcome') {
        if (state.completed_states.indexOf('welcome') === -1 && !state.trade) return 'welcome';
        continue;
      }
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
      if (id === 'trial_bridge') return id;
    }
    return 'trial_bridge';
  }

  function updateProgress() {
    var progress = document.getElementById('pgProgress');
    var isWelcome = state.current_state === 'welcome';
    if (progress) progress.hidden = isWelcome;

    if (isWelcome) return;

    var surveyStates = STATES.filter(function (s) {
      return s !== 'welcome';
    });
    var idx = surveyStates.indexOf(state.current_state);
    if (idx < 0) idx = 0;
    var pct = ((idx + 1) / surveyStates.length) * 100;
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
    back.hidden = state.current_state === 'welcome';
  }

  function choiceLabel(map, key) {
    var item = map[key];
    if (item == null) return key;
    if (typeof item === 'object') {
      if (item.title && item.band) return item.title + '\n' + item.band;
      return item.label || item.title || key;
    }
    return item;
  }

  function renderChoices(field, map, selectedKey) {
    var host = document.querySelector('[data-pg-choices="' + field + '"]');
    if (!host) return;
    host.innerHTML = '';
    Object.keys(map).forEach(function (key) {
      var item = map[key];
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'pg-choice' + (selectedKey === key ? ' is-selected' : '');
      btn.setAttribute('data-pg-field', field);
      btn.setAttribute('data-pg-value', key);
      if (field === 'public_proof_percentage' && item && typeof item === 'object') {
        btn.className += ' pg-choice--stacked';
        var t = document.createElement('span');
        t.className = 'pg-choice__title';
        t.textContent = item.title || item.label || key;
        var b = document.createElement('span');
        b.className = 'pg-choice__band';
        b.textContent = item.band || '';
        btn.appendChild(t);
        if (item.band) btn.appendChild(b);
      } else {
        btn.textContent = choiceLabel(map, key);
      }
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

  function hideAllInsights() {
    document.querySelectorAll('.proof-gap-insight-card').forEach(function (el) {
      el.hidden = true;
      el.innerHTML = '';
      el.classList.remove('is-visible');
    });
    insightVisible = false;
  }

  function ensureInsightVisible(card) {
    if (!card) return;
    try {
      var rect = card.getBoundingClientRect();
      var vh = window.innerHeight || 0;
      if (rect.bottom > vh - 16 || rect.top < 72) {
        card.scrollIntoView({ block: 'nearest', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      }
    } catch (e) {}
  }

  function showInsight(slot, data, nextState, ctaLabel) {
    var card = document.querySelector('[data-pg-insight="' + slot + '"]');
    if (!card) return;
    card.hidden = false;
    card.classList.remove('is-visible');
    card.innerHTML =
      '<div class="proof-gap-insight-card__stamp" aria-hidden="true">' +
      '<svg viewBox="0 0 40 40" width="28" height="28"><circle cx="20" cy="20" r="15" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 20l5 5 11-12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
      '</div>' +
      '<div class="proof-gap-insight-card__body">' +
      '<h2 class="proof-gap-insight-card__headline">' +
      escapeHtml(data.headline) +
      '</h2>' +
      (data.body ? '<p class="proof-gap-insight-card__text">' + escapeHtml(data.body) + '</p>' : '') +
      (data.body2 ? '<p class="proof-gap-insight-card__text">' + escapeHtml(data.body2) + '</p>' : '') +
      '<button type="button" class="btn btn-primary pg-btn proof-gap-insight-card__cta">' +
      escapeHtml(ctaLabel || 'Continue →') +
      '</button>' +
      '</div>';

    // Force reflow for entrance animation.
    void card.offsetWidth;
    card.classList.add('is-visible');
    insightVisible = true;

    var cta = card.querySelector('.proof-gap-insight-card__cta');
    if (cta) {
      cta.addEventListener('click', function () {
        hideAllInsights();
        goTo(nextState);
      });
      try {
        cta.focus({ preventScroll: true });
      } catch (eF) {}
    }
    ensureInsightVisible(card);
  }

  function formatAnnualRange() {
    if (state.annual_jobs_min == null) return '—';
    if (state.annual_jobs_max == null) {
      return state.annual_jobs_min.toLocaleString() + '+ / year';
    }
    return (
      state.annual_jobs_min.toLocaleString() +
      '–' +
      state.annual_jobs_max.toLocaleString() +
      ' / year'
    );
  }

  function formatUnusedRange() {
    if (state.unused_jobs_min == null || state.unused_jobs_max == null) return '';
    if (state.unused_jobs_min === state.unused_jobs_max) {
      return '≈' + state.unused_jobs_min.toLocaleString();
    }
    return state.unused_jobs_min.toLocaleString() + '–' + state.unused_jobs_max.toLocaleString();
  }

  function proofBandLabel(key) {
    var item = proofPct[key];
    if (!item) return key || '—';
    if (typeof item === 'object') return item.band || item.label || key;
    return item;
  }

  function proofDisplayLabel(key) {
    var item = proofPct[key];
    if (!item) return key || '—';
    if (typeof item === 'object') {
      if (item.title && item.band) return item.title + ' (' + item.band + ')';
      return item.label || item.title || key;
    }
    return item;
  }

  function computeUnusedRange() {
    state.unused_jobs_min = null;
    state.unused_jobs_max = null;
    var band = proofPct[state.public_proof_percentage];
    if (!band || typeof band !== 'object') return;
    if (band.min == null || band.max == null) return;
    if (state.annual_jobs_min == null) return;

    var annualMax = state.annual_jobs_max != null ? state.annual_jobs_max : state.annual_jobs_min;
    // unused_min = annual_min * (1 - proof_max); unused_max = annual_max * (1 - proof_min)
    var uMin = Math.max(0, Math.floor(state.annual_jobs_min * (1 - band.max)));
    var uMax = Math.max(0, Math.ceil(annualMax * (1 - band.min)));
    if (state.annual_jobs_max == null) {
      // Open-ended annual: report floor only as min=max floor estimate range label elsewhere
      state.unused_jobs_min = uMin;
      state.unused_jobs_max = null;
      return;
    }
    if (uMin > uMax) {
      var tmp = uMin;
      uMin = uMax;
      uMax = tmp;
    }
    state.unused_jobs_min = uMin;
    state.unused_jobs_max = uMax;
  }

  function proofTier(key) {
    if (key === 'unknown') return 'unknown';
    if (key === '0_10' || key === '11_25') return 'low';
    if (key === '26_50' || key === '51_75') return 'mid';
    if (key === '76_100') return 'high';
    return 'mid';
  }

  function onChoice(field, key, btn) {
    document.querySelectorAll('.pg-choice[data-pg-field="' + field + '"]').forEach(function (b) {
      b.classList.remove('is-selected');
    });
    if (btn) btn.classList.add('is-selected');

    var prev =
      field === 'trade'
        ? state.trade
        : field === 'current_workflow'
          ? state.current_workflow
          : field === 'jobs_per_week'
            ? state.jobs_per_week_bucket
            : state.public_proof_percentage;
    if (prev && prev !== key) invalidateDownstream(field);

    var feedbackShown = field !== 'trade';
    var feedbackVariant = '';

    if (field === 'trade') {
      state.trade = key;
      markCompleted('trade');
      saveState();
      track('SurveyQuestionAnswered', {
        question_index: stateIndex('trade'),
        question_id: 'trade',
        answer_key: key,
        feedback_shown: false,
        trade: key,
      });
      scheduleAdvance('current_workflow');
      return;
    }

    if (field === 'current_workflow') {
      state.current_workflow = key;
      markCompleted('current_workflow');
      saveState();
      feedbackVariant = (WORKFLOW_INSIGHTS[key] || {}).variant || key;
      track('SurveyQuestionAnswered', {
        question_index: stateIndex('current_workflow'),
        question_id: 'current_workflow',
        answer_key: key,
        feedback_shown: true,
        feedback_variant: feedbackVariant,
        trade: state.trade,
        current_workflow: key,
      });
      showInsight('workflow', WORKFLOW_INSIGHTS[key] || WORKFLOW_INSIGHTS.scattered, 'jobs_per_week', 'Continue →');
      return;
    }

    if (field === 'jobs_per_week') {
      state.jobs_per_week_bucket = key;
      var bucket = jobsBuckets[key] || {};
      state.annual_jobs_min = typeof bucket.min === 'number' ? bucket.min : null;
      state.annual_jobs_max = typeof bucket.max === 'number' ? bucket.max : null;
      markCompleted('jobs_per_week');
      saveState();
      feedbackVariant = key;
      track('SurveyQuestionAnswered', {
        question_index: stateIndex('jobs_per_week'),
        question_id: 'jobs_per_week',
        answer_key: key,
        feedback_shown: true,
        feedback_variant: feedbackVariant,
        trade: state.trade,
        current_workflow: state.current_workflow,
        jobs_per_week_bucket: key,
      });
      var highVol = key === '21_35' || key === '36_50' || key === '50_plus';
      showInsight(
        'jobs',
        {
          headline: 'That’s roughly ' + formatAnnualRange() + ' completed jobs every year.',
          body: highVol
            ? 'Your team is already creating an enormous amount of real-world marketing material.'
            : 'You probably don’t have a content-creation problem.',
          body2: highVol
            ? 'The question is what happens to it after the job.'
            : 'Your company is already producing the raw material every week.',
        },
        'public_proof_percentage',
        'Continue →'
      );
      return;
    }

    if (field === 'public_proof_percentage') {
      state.public_proof_percentage = key;
      state.proof_gap_band = key;
      computeUnusedRange();
      markCompleted('public_proof_percentage');
      saveState();
      var tier = proofTier(key);
      feedbackVariant = tier;
      track('SurveyQuestionAnswered', {
        question_index: stateIndex('public_proof_percentage'),
        question_id: 'public_proof_percentage',
        answer_key: key,
        feedback_shown: true,
        feedback_variant: feedbackVariant,
        trade: state.trade,
        current_workflow: state.current_workflow,
        jobs_per_week_bucket: state.jobs_per_week_bucket,
        public_proof_percentage: key,
      });

      var insight = { headline: '', body: '', body2: '' };
      if (tier === 'low') {
        insight.headline = 'That means a lot of work may disappear from public view.';
        insight.body = 'Not because the work wasn’t done. Because nobody turned the finished job into proof after it was completed.';
        if (state.unused_jobs_min != null) {
          var rangeTxt =
            state.unused_jobs_max == null
              ? state.unused_jobs_min.toLocaleString() + '+'
              : formatUnusedRange();
          insight.body2 =
            'Based on your answers: approximately ' + rangeTxt + ' completed jobs/year may not become public proof.';
        }
      } else if (tier === 'mid') {
        insight.headline = 'You’re creating more proof than you’re putting to work.';
        insight.body =
          'The opportunity is making the process consistent without adding another manual marketing task.';
        if (state.unused_jobs_min != null && state.unused_jobs_max != null) {
          insight.body2 =
            'Based on your answers: approximately ' +
            formatUnusedRange() +
            ' completed jobs/year may not become public proof.';
        }
      } else if (tier === 'high') {
        insight.headline = 'You’re already doing the hard part.';
        insight.body =
          'Your opportunity may be less about creating more proof and more about eliminating the manual work required to distribute it.';
      } else {
        insight.headline = 'Not knowing is useful information too.';
        insight.body =
          'If it is hard to tell what happens to a finished job after the crew leaves, the process probably is not as visible or repeatable as it could be.';
      }
      showInsight('proof', insight, 'proof_gap_result', 'See My Proof Gap →');
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderResult() {
    var title = document.getElementById('pgResultTitle');
    var completed = document.getElementById('pgResultCompleted');
    var pub = document.getElementById('pgResultPublic');
    var invWrap = document.getElementById('pgResultInvisibleWrap');
    var inv = document.getElementById('pgResultInvisible');
    var tier = proofTier(state.public_proof_percentage);

    if (title) {
      if (tier === 'low') {
        title.innerHTML = 'You don’t have a content problem.<br>You have a proof-distribution problem.';
      } else if (tier === 'mid') {
        title.textContent = 'You’re creating more proof than you’re putting to work.';
      } else if (tier === 'high') {
        title.innerHTML = 'You’re doing the hard part already.<br>Now remove the manual part.';
      } else {
        title.innerHTML = 'Your company is creating proof every week.<br>The missing piece is knowing what happens to it next.';
      }
    }
    if (completed) completed.textContent = formatAnnualRange();
    if (pub) pub.textContent = proofBandLabel(state.public_proof_percentage);
    computeUnusedRange();
    if (invWrap && inv) {
      if (state.unused_jobs_min != null && tier !== 'unknown' && tier !== 'high') {
        invWrap.hidden = false;
        inv.textContent =
          state.unused_jobs_max == null
            ? state.unused_jobs_min.toLocaleString() + '+ / year'
            : formatUnusedRange() + ' / year';
      } else {
        invWrap.hidden = true;
      }
    }
  }

  function sourceLabel() {
    if (state.current_workflow === 'housecall_pro') return 'Captured in Housecall Pro';
    if (state.current_workflow === 'companycam') return 'Captured in CompanyCam';
    if (state.current_workflow === 'other_crm') return 'Captured in your field / CRM workflow';
    if (state.current_workflow === 'phones_camera_roll') return 'Captured by your crew on phones';
    if (state.current_workflow === 'group_text_shared_folder') return 'Captured in a shared folder / group text';
    if (state.current_workflow === 'scattered') return 'Captured across scattered sources';
    return 'Captured by your crew';
  }

  function workflowShortLabel() {
    return workflows[state.current_workflow] || state.current_workflow || 'Your workflow';
  }

  function renderReveal() {
    var tradeLabel = trades[state.trade] || state.trade || 'field';
    var title = document.getElementById('pgRevealTitle');
    if (title) title.textContent = 'Here’s what one finished ' + tradeLabel + ' job could become.';

    var jobTitle = document.getElementById('pgJobTitle');
    var jobMeta = document.getElementById('pgJobMeta');
    var src = document.getElementById('pgRevealSource');
    var wfLabel = document.getElementById('pgRevealWorkflowLabel');
    var logo = document.getElementById('pgRevealWorkflowLogo');
    var continuity = document.getElementById('pgContinuityNote');

    if (jobTitle) jobTitle.textContent = JOB_EXAMPLES[state.trade] || 'Finished field job';
    if (jobMeta) jobMeta.textContent = 'Completed · Job photos · Field location';
    if (src) src.textContent = sourceLabel();
    if (wfLabel) wfLabel.textContent = workflowShortLabel();

    if (logo) {
      var url = '';
      if (state.current_workflow === 'housecall_pro') url = logo.getAttribute('data-hcp') || '';
      if (state.current_workflow === 'companycam') url = logo.getAttribute('data-cc') || '';
      if (url) {
        logo.src = url;
        logo.hidden = false;
        logo.alt = workflowShortLabel();
      } else {
        logo.hidden = true;
      }
    }

    if (continuity) {
      if (state.current_workflow === 'housecall_pro') {
        continuity.hidden = false;
        continuity.textContent =
          'Keep Housecall Pro. Your crew can keep working the way they already do when using the supported integration.';
      } else if (state.current_workflow === 'companycam') {
        continuity.hidden = false;
        continuity.textContent =
          'Keep CompanyCam. Your crew can keep working the way they already do when using the supported integration.';
      } else {
        continuity.hidden = true;
        continuity.textContent = '';
      }
    }
  }

  function renderTrialSummary() {
    var list = document.getElementById('pgTrialSummary');
    if (!list) return;
    list.innerHTML =
      '<li><span>Trade</span><strong>' +
      escapeHtml(trades[state.trade] || state.trade || '—') +
      '</strong></li>' +
      '<li><span>Current workflow</span><strong>' +
      escapeHtml(workflows[state.current_workflow] || state.current_workflow || '—') +
      '</strong></li>' +
      '<li><span>Completed jobs</span><strong>' +
      escapeHtml((jobsBuckets[state.jobs_per_week_bucket] || {}).weekly_label || '—') +
      ' / week</strong></li>' +
      '<li><span>Annual jobs</span><strong>' +
      escapeHtml(formatAnnualRange()) +
      '</strong></li>' +
      '<li><span>Current public proof</span><strong>' +
      escapeHtml(proofDisplayLabel(state.public_proof_percentage)) +
      '</strong></li>';

    var cont = document.getElementById('pgTrialContinuity');
    if (cont) {
      if (state.current_workflow === 'housecall_pro' || state.current_workflow === 'companycam') {
        cont.hidden = false;
        cont.textContent = 'No new crew photo workflow required when using the supported connection.';
      } else {
        cont.hidden = true;
        cont.textContent = '';
      }
    }
    updateTrialHref();
  }

  function readUrlParam(key) {
    try {
      return new URLSearchParams(window.location.search).get(key) || '';
    } catch (e) {
      return '';
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
    return map[trade] || '';
  }

  function updateTrialHref() {
    var a = document.getElementById('pgTrialCta');
    if (!a) return;
    var base = boot.trialBase || 'https://app.jobcapturepro.com/onboarding';
    try {
      var u = new URL(base, window.location.origin);
      var attr = Object.assign({}, attrPayload() || {}, state.attribution || {});
      [
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
      ].forEach(function (k) {
        var v = attr[k] || readUrlParam(k);
        if (v) u.searchParams.set(k, v);
      });
      u.searchParams.set('lp_variant', LP_VARIANT);
      u.searchParams.set('survey_session_id', state.session_id);
      u.searchParams.set('jcp_surface', 'proof_gap_survey_trial');
      var ind = mapTradeToIndustry(state.trade);
      if (ind) u.searchParams.set('industry', ind);
      if (state.handoff_token) u.searchParams.set('pg_handoff', state.handoff_token);
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

  function goTo(id) {
    clearAdvance();
    if (STATES.indexOf(id) === -1) return;
    hideAllInsights();
    state.current_state = id;
    saveState();

    document.querySelectorAll('[data-pg-state]').forEach(function (el) {
      el.hidden = el.getAttribute('data-pg-state') !== id;
    });

    updateProgress();
    setBackVisible();

    document.body.classList.toggle('pg-is-welcome', id === 'welcome');

    if (!questionViewed[id]) {
      questionViewed[id] = true;
      if (['trade', 'current_workflow', 'jobs_per_week', 'public_proof_percentage', 'email_capture'].indexOf(id) !== -1) {
        track('SurveyQuestionViewed', {
          question_index: stateIndex(id),
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
    if (id === 'email_capture') track('EmailCaptureViewed', { trade: state.trade });
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
      window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    } catch (e) {
      window.scrollTo(0, 0);
    }
  }

  function goBack() {
    clearAdvance();
    hideAllInsights();
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

  function deriveFirstName(email) {
    var local = String(email || '').split('@')[0] || 'there';
    return local.replace(/[._-]+/g, ' ').trim().slice(0, 40) || 'there';
  }

  function persistDemoUser(email) {
    try {
      localStorage.setItem(
        'demoUser',
        JSON.stringify({
          email: email,
          firstName: deriveFirstName(email),
          lastName: '',
          niche: state.trade || '',
          industry: mapTradeToIndustry(state.trade),
          trade: state.trade || '',
          source: 'proof_gap_survey',
        })
      );
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
        err.textContent = 'Enter a valid email address.';
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

    fetch(boot.restUrl || '/wp-json/jcp/v1/proof-gap-survey-submit', {
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
          btn.textContent = defaultLabel || 'Show Me My Job Transformation →';
        }
        if (!result.ok) {
          if (err) {
            err.hidden = false;
            err.textContent =
              (result.json && result.json.message) || 'We couldn’t save that right now. Please try again.';
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
        pushAcquisitionLead(returnedId);
        goTo('product_reveal');
      })
      .catch(function () {
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultLabel || 'Show Me My Job Transformation →';
        }
        if (err) {
          err.hidden = false;
          err.textContent = 'Network error — please try again.';
        }
      });
  }

  function bind() {
    var welcome = document.getElementById('pgWelcomeCta');
    if (welcome) {
      welcome.addEventListener('click', function () {
        markCompleted('welcome');
        if (!startedTracked) {
          startedTracked = true;
          track('SurveyStarted', { question_id: 'welcome', cta_source: 'welcome' });
        }
        saveState();
        goTo('trade');
      });
    }

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
        track('TrialCTAClicked', { trade: state.trade, cta_source: 'trial_bridge' });
        updateTrialHref();
      });
    }

    window.addEventListener('pagehide', function () {
      track('SurveyExited', { question_id: state.current_state, trade: state.trade });
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

    var startState = 'welcome';
    if (resumed) {
      track('SurveyResumed', { question_id: state.current_state, trade: state.trade });
      if (state.email_captured && state.product_reveal_completed) startState = 'trial_bridge';
      else if (state.email_captured) startState = 'product_reveal';
      else startState = firstIncomplete();
      if (state.completed_states.indexOf('welcome') !== -1 || state.trade) startedTracked = true;
    }

    goTo(startState);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
