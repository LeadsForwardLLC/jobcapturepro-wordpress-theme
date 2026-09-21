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
      headline: 'You may be able to keep that workflow.',
      body: 'JobCapturePro works with supported systems so the goal is not another marketing task for your crew.',
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
  var tradeAssets = boot.tradeAssets || {};
  var creativeWhitelist = boot.creativeConcepts || ['default'];

  var DEST_TABS = ['website', 'google', 'social', 'reviews', 'directory'];

  var state = createEmptyState();
  var advanceTimer = null;
  var startedTracked = false;
  var landingTracked = false;
  var questionViewed = {};
  var milestoneViewed = {};
  var stepEnteredAt = Date.now();
  var stepEnteredId = 'welcome';
  var insightVisible = false;
  var preloadedTradePhoto = '';
  var bottomActionHandler = null;
  var keyboardBound = false;
  var activeDest = 'website';
  var destTracked = {};
  var autoplayTimer = null;
  var autoplayDisabled = false;
  var autoplayOrder = ['website', 'google', 'social', 'reviews', 'directory'];
  var autoplayIndex = 0;
  var autoplayPaused = false;

  function syncBottomPad() {
    var bar = document.getElementById('pgBottomAction');
    var pad = 0;
    if (bar && !bar.hidden) {
      pad = Math.ceil(bar.getBoundingClientRect().height) || 88;
    }
    document.documentElement.style.setProperty('--pg-bottom-pad', pad + 'px');
    document.body.style.setProperty('--pg-bottom-pad', pad + 'px');
  }

  function clearBottomAction() {
    var bar = document.getElementById('pgBottomAction');
    var host = document.getElementById('pgBottomCtaHost');
    var micro = document.getElementById('pgBottomMicro');
    bottomActionHandler = null;
    if (host) host.innerHTML = '';
    if (micro) {
      micro.hidden = true;
      micro.textContent = '';
    }
    if (bar) {
      bar.hidden = true;
      bar.classList.remove('is-entering');
    }
    document.body.classList.remove('pg-has-bottom-action');
    syncBottomPad();
  }

  /**
   * @param {{label:string, onClick?:Function, href?:string, type?:string, form?:string, id?:string, micro?:string, animate?:boolean}} opts
   */
  function setBottomAction(opts) {
    opts = opts || {};
    var bar = document.getElementById('pgBottomAction');
    var host = document.getElementById('pgBottomCtaHost');
    var micro = document.getElementById('pgBottomMicro');
    if (!bar || !host) return;

    host.innerHTML = '';
    bottomActionHandler = typeof opts.onClick === 'function' ? opts.onClick : null;

    var el;
    if (opts.href) {
      el = document.createElement('a');
      el.className = 'btn btn-primary pg-btn';
      el.href = opts.href;
      el.textContent = opts.label || 'Continue →';
      if (opts.id) el.id = opts.id;
      el.addEventListener('click', function (ev) {
        if (bottomActionHandler) bottomActionHandler(ev);
      });
    } else {
      el = document.createElement('button');
      el.type = opts.type || 'button';
      el.className = 'btn btn-primary pg-btn';
      el.textContent = opts.label || 'Continue →';
      if (opts.id) el.id = opts.id;
      if (opts.form) el.setAttribute('form', opts.form);
      el.addEventListener('click', function (ev) {
        if (opts.type === 'submit') return;
        if (bottomActionHandler) {
          ev.preventDefault();
          bottomActionHandler(ev);
        }
      });
    }
    host.appendChild(el);

    if (micro) {
      if (opts.micro) {
        micro.hidden = false;
        micro.textContent = opts.micro;
      } else {
        micro.hidden = true;
        micro.textContent = '';
      }
    }

    var wasHidden = bar.hidden;
    bar.hidden = false;
    document.body.classList.add('pg-has-bottom-action');
    if (opts.animate !== false && wasHidden && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      bar.classList.remove('is-entering');
      void bar.offsetWidth;
      bar.classList.add('is-entering');
    }
    requestAnimationFrame(syncBottomPad);
    setTimeout(syncBottomPad, 50);
  }

  function syncBottomActionForState(id) {
    if (id === 'welcome') {
      setBottomAction({
        id: 'pgWelcomeCta',
        label: 'Find My Proof Gap →',
        micro: 'About 60 seconds · No phone required · No credit card',
        animate: false,
        onClick: function () {
          markCompleted('welcome');
          if (!startedTracked) {
            startedTracked = true;
            track('SurveyStarted', { question_id: 'welcome', cta_source: 'welcome' });
          }
          saveState();
          goTo('trade');
        },
      });
      return;
    }

    if (id === 'trade') {
      clearBottomAction();
      return;
    }

    if (id === 'current_workflow' || id === 'jobs_per_week' || id === 'public_proof_percentage') {
      // Insight CTA is set by showInsight after selection; otherwise no bar.
      if (!insightVisible) clearBottomAction();
      return;
    }

    if (id === 'proof_gap_result') {
      setBottomAction({
        id: 'pgResultCta',
        label: 'Show Me What One Job Could Become →',
        animate: false,
        onClick: function () {
          markCompleted('proof_gap_result');
          saveState();
          goTo('email_capture');
        },
      });
      return;
    }

    if (id === 'email_capture') {
      setBottomAction({
        id: 'pgEmailSubmit',
        label: 'Show Me My Job Transformation →',
        type: 'submit',
        form: 'pgEmailForm',
        animate: false,
      });
      return;
    }

    if (id === 'product_reveal') {
      setBottomAction({
        id: 'pgRevealContinue',
        label: 'See My Trial Plan →',
        animate: false,
        onClick: function () {
          state.product_reveal_completed = true;
          markCompleted('product_reveal');
          saveState();
          track('ProductRevealCompleted', {
            trade: state.trade,
            current_workflow: state.current_workflow,
          });
          goTo('trial_bridge');
        },
      });
      return;
    }

    if (id === 'trial_bridge') {
      setBottomAction({
        id: 'pgTrialCta',
        label: 'Start My Free 14-Day Trial →',
        href: '#',
        micro: 'No credit card required.',
        animate: false,
        onClick: function () {
          state.trial_cta_clicked = true;
          markCompleted('trial_bridge');
          saveState();
          track('TrialCTAClicked', { trade: state.trade, cta_source: 'trial_bridge' });
          updateTrialHref();
        },
      });
      updateTrialHref();
      return;
    }

    clearBottomAction();
  }

  function bindKeyboardSafe() {
    if (keyboardBound) return;
    keyboardBound = true;
    var vv = window.visualViewport;
    if (!vv) return;
    var onResize = function () {
      var offset = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
      document.body.style.setProperty('--pg-keyboard-offset', offset > 40 ? offset + 'px' : '0px');
      syncBottomPad();
    };
    vv.addEventListener('resize', onResize);
    vv.addEventListener('scroll', onResize);
  }

  function createEmptyState() {
    return {
      survey_id: boot.surveyId || 'proof_gap_survey_v1',
      survey_version: boot.surveyVersion || '3',
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
      other_trade_text: '',
      other_workflow_text: '',
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
    var eventUuid = uuid();
    var attr = Object.assign({}, state.attribution || {}, attrPayload());
    var payload = {
      event: name,
      event_uuid: eventUuid,
      survey_id: state.survey_id,
      survey_version: state.survey_version,
      session_id: state.session_id,
      lp_variant: LP_VARIANT,
      device_class: deviceClass(),
      current_state: state.current_state,
      funnel_id: 'proof_gap',
      funnel_version: state.survey_version,
      trade: state.trade || undefined,
      workflow: state.current_workflow || undefined,
      jobs_per_week_bucket: state.jobs_per_week_bucket || undefined,
      proof_gap_band: state.proof_gap_band || undefined,
    };
    Object.keys(props).forEach(function (k) {
      if (props[k] !== undefined && props[k] !== null && props[k] !== '') payload[k] = props[k];
    });
    if (attr.creative_concept && !payload.creative_concept) {
      payload.creative_concept = attr.creative_concept;
    }
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'ttclid', 'referrer'].forEach(function (k) {
      if (attr[k] && !payload[k]) payload[k] = attr[k];
    });
    if (attr.fbclid) payload.has_fbclid = true;
    if (attr.ttclid) payload.has_ttclid = true;
    delete payload.email;
    delete payload.phone;
    delete payload.first_name;
    delete payload.last_name;
    delete payload.business_name;
    delete payload.fbclid;
    delete payload.ttclid;
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(payload);
    } catch (e) {}
    persistFunnelEvent(name, eventUuid, payload);
  }

  function persistFunnelEvent(name, eventUuid, payload) {
    var url = boot.funnelEventUrl || '/wp-json/jcp/v1/funnel-event';
    var body = {
      event_uuid: eventUuid,
      session_id: state.session_id || '',
      funnel_id: 'proof_gap',
      funnel_version: String(state.survey_version || ''),
      lp_variant: LP_VARIANT,
      event_name: name,
      screen: state.current_state || '',
      question_index: propsQuestionIndex(payload),
      question_id: payload.question_id || '',
      answer_value: payload.answer_value || payload.answer_key || payload.answer || '',
      trade: state.trade || '',
      jobs_per_week_bucket: state.jobs_per_week_bucket || '',
      proof_gap_band: state.proof_gap_band || '',
      workflow: state.current_workflow || '',
      utm_source: payload.utm_source || '',
      utm_medium: payload.utm_medium || '',
      utm_campaign: payload.utm_campaign || '',
      utm_content: payload.utm_content || '',
      utm_term: payload.utm_term || '',
      has_fbclid: !!payload.has_fbclid,
      has_ttclid: !!payload.has_ttclid,
      device_category: deviceClass(),
      referrer: payload.referrer || '',
      metadata: {
        creative_concept: payload.creative_concept || '',
        destination: payload.destination || '',
        cta_source: payload.cta_source || '',
        duration_ms: payload.duration_ms || '',
      },
      creative_concept: payload.creative_concept || '',
    };
    try {
      var json = JSON.stringify(body);
      if (typeof navigator !== 'undefined' && typeof navigator.sendBeacon === 'function') {
        try {
          var blob = new Blob([json], { type: 'application/json' });
          if (navigator.sendBeacon(url, blob)) return;
        } catch (eBeacon) {}
      }
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: json,
        keepalive: true,
      }).catch(function () {});
    } catch (ePersist) {}
  }

  function propsQuestionIndex(payload) {
    var q = payload.question_id || '';
    var map = { trade: 1, current_workflow: 2, jobs_per_week: 3, public_proof_percentage: 4 };
    return map[q] || null;
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

    var phase = PHASE_BY_STATE[state.current_state] || 'work';
    var order = ['work', 'gap', 'plan'];
    var activeIdx = order.indexOf(phase);
    var phasePct = { work: 34, gap: 67, plan: 100 };
    // Interpolate within phase by step position for smoother fill.
    var inPhase = STATES.filter(function (s) {
      return PHASE_BY_STATE[s] === phase;
    });
    var localIdx = Math.max(0, inPhase.indexOf(state.current_state));
    var localSpan = inPhase.length || 1;
    var prevPct = activeIdx <= 0 ? 0 : phasePct[order[activeIdx - 1]] || 0;
    var endPct = phasePct[phase] || 100;
    var pct = prevPct + ((localIdx + 1) / localSpan) * (endPct - prevPct);
    var fill = document.getElementById('pgProgressFill');
    if (fill) fill.style.width = Math.min(100, Math.round(pct)) + '%';

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

  function readUrlParam(key) {
    try {
      return new URLSearchParams(window.location.search).get(key) || '';
    } catch (e) {
      return '';
    }
  }

  function initCreativeConcept() {
    var fromUrl = readUrlParam('creative_concept');
    var concept = 'default';
    if (fromUrl && creativeWhitelist.indexOf(fromUrl) !== -1) {
      concept = fromUrl;
    } else if (state.attribution && state.attribution.creative_concept && creativeWhitelist.indexOf(state.attribution.creative_concept) !== -1) {
      concept = state.attribution.creative_concept;
    }
    state.attribution = state.attribution || {};
    state.attribution.creative_concept = concept;
    var visual = document.querySelector('[data-pg-welcome-visual]');
    if (visual) visual.setAttribute('data-creative', concept);
  }

  function summarySlotForField(field) {
    if (field === 'current_workflow') return 'workflow';
    if (field === 'jobs_per_week') return 'jobs';
    if (field === 'public_proof_percentage') return 'proof';
    return '';
  }

  function summaryValueText(field, key) {
    if (field === 'current_workflow') return workflows[key] || key;
    if (field === 'jobs_per_week') {
      var b = jobsBuckets[key] || {};
      return b.weekly_label || key;
    }
    if (field === 'public_proof_percentage') return proofDisplayLabel(key);
    return key;
  }

  function fillAnswerSummary(field, key, valueText) {
    var slot = summarySlotForField(field);
    var summary = document.querySelector('[data-pg-summary="' + slot + '"]');
    if (!summary) return;
    summary.hidden = false;
    var val = summary.querySelector('[data-pg-summary-value]');
    if (!val) return;
    if (field === 'current_workflow') {
      val.innerHTML = workflowChoiceInner(key, workflows[key] || key);
      val.classList.add('pg-answer-summary__value--workflow');
    } else {
      val.classList.remove('pg-answer-summary__value--workflow');
      val.textContent = valueText || '';
    }
  }

  function collapseChoiceList(field, valueText, key, done) {
    var choices = document.querySelector('[data-pg-choices="' + field + '"]');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finish = function () {
      if (choices) {
        choices.classList.remove('is-exiting');
        choices.hidden = true;
      }
      fillAnswerSummary(field, key, valueText);
      if (typeof done === 'function') done();
    };
    if (!choices || choices.hidden || reduce) {
      finish();
      return;
    }
    choices.classList.add('is-exiting');
    window.setTimeout(finish, 200);
  }

  function expandChoiceList(field) {
    var slot = summarySlotForField(field);
    var choices = document.querySelector('[data-pg-choices="' + field + '"]');
    var summary = document.querySelector('[data-pg-summary="' + slot + '"]');
    if (choices) {
      choices.classList.remove('is-exiting');
      choices.hidden = false;
    }
    if (summary) summary.hidden = true;
    hideAllInsights();
    clearBottomAction();
  }

  function syncQuestionUI(stateId) {
    var field = stateId;
    if (['current_workflow', 'jobs_per_week', 'public_proof_percentage'].indexOf(stateId) === -1) return;
    var ans = answerForState(stateId);
    if (!ans) {
      expandChoiceList(field);
      return;
    }
    collapseChoiceList(field, summaryValueText(field, ans), ans);
    if (stateId === 'current_workflow' && !state.jobs_per_week_bucket) {
      showInsight('workflow', WORKFLOW_INSIGHTS[ans] || WORKFLOW_INSIGHTS.scattered, 'jobs_per_week', 'Continue →');
    } else if (stateId === 'jobs_per_week' && !state.public_proof_percentage) {
      var highVol = ans === '21_35' || ans === '36_50' || ans === '50_plus';
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
          extraHtml: typeof buildJobsStackHtml === 'function' ? buildJobsStackHtml() : '',
        },
        'public_proof_percentage',
        'Continue →'
      );
    } else if (stateId === 'public_proof_percentage' && state.completed_states.indexOf('proof_gap_result') === -1) {
      computeUnusedRange();
      var tier = proofTier(ans);
      var insight = {
        headline: '',
        body: '',
        body2: '',
        extraHtml: typeof buildProofGridHtml === 'function' ? buildProofGridHtml(ans) : '',
      };
      if (tier === 'low') {
        insight.headline = 'That means a lot of work may disappear from public view.';
        insight.body = 'Not because the work wasn’t done — because finished jobs weren’t turned into proof.';
        if (state.unused_jobs_min != null) {
          var rangeTxt =
            state.unused_jobs_max == null
              ? state.unused_jobs_min.toLocaleString() + '+'
              : formatUnusedRange();
          insight.body2 =
            'Based on your answers: ~' + rangeTxt + ' completed jobs/year may not become public proof.';
        }
      } else if (tier === 'mid') {
        insight.headline = 'You’re creating more proof than you’re putting to work.';
        insight.body = 'Make the process consistent without adding another manual marketing task.';
        if (state.unused_jobs_min != null && state.unused_jobs_max != null) {
          insight.body2 =
            'Based on your answers: ~' + formatUnusedRange() + ' completed jobs/year may not become public proof.';
        }
      } else if (tier === 'high') {
        insight.headline = 'You’re already doing the hard part.';
        insight.body = 'The opportunity is removing the manual work required to distribute proof.';
      } else {
        insight.headline = 'Not knowing is useful information too.';
        insight.body =
          'If it’s hard to tell what happens after the crew leaves, the process may not be repeatable yet.';
      }
      showInsight('proof', insight, 'proof_gap_result', 'See My Proof Gap →');
    }
  }

  function getTradeAsset(trade) {
    return tradeAssets[trade] || tradeAssets.other || { title: JOB_EXAMPLES[trade] || 'Finished field job', photo: null, neutral: true };
  }

  function preloadTradePhoto(trade) {
    var asset = getTradeAsset(trade);
    if (!asset || !asset.photo) return;
    if (preloadedTradePhoto === asset.photo) return;
    preloadedTradePhoto = asset.photo;
    try {
      var img = new Image();
      img.decoding = 'async';
      img.src = asset.photo;
    } catch (e) {}
  }

  function getJobPhotoUrl() {
    var asset = getTradeAsset(state.trade);
    if (asset && asset.photo) return asset.photo;
    // Approved generic field-job photo — same asset across all destinations (never a gray skeleton).
    var campaign = (boot.campaignBase || '').replace(/\/?$/, '/');
    if (campaign) return campaign + 'jcp-campaign-job-proof-360.webp';
    var hvac = tradeAssets.hvac;
    return (hvac && hvac.photo) || '';
  }

  function workflowIconSvg(key) {
    if (key === 'other_crm') {
      return '<svg class="pg-choice__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M7 9h10M7 13h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    }
    if (key === 'phones_camera_roll') {
      return '<svg class="pg-choice__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="14" r="3" fill="none" stroke="currentColor" stroke-width="2"/></svg>';
    }
    if (key === 'group_text_shared_folder') {
      return '<svg class="pg-choice__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M4 6h16v12H4z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    }
    return '<svg class="pg-choice__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="6" cy="8" r="2" fill="currentColor"/><circle cx="12" cy="6" r="2" fill="currentColor"/><circle cx="18" cy="9" r="2" fill="currentColor"/><path d="M4 18c0-2 2-3 4-3M10 18c0-2 2-3 4-3M16 18c0-1 2-2 3-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
  }

  function workflowChoiceInner(key, label) {
    var host = document.querySelector('[data-pg-choices="current_workflow"]');
    var safeLabel = escapeHtml(label);
    if (key === 'housecall_pro' && host) {
      var hcp = host.getAttribute('data-logo-hcp') || '';
      if (hcp) {
        return '<span class="pg-choice__lead"><img class="pg-choice__logo" src="' + escapeHtml(hcp) + '" alt="" width="72" height="20" loading="lazy" decoding="async" /></span><span class="pg-choice__text">' + safeLabel + '</span>';
      }
    }
    if (key === 'companycam' && host) {
      var cc = host.getAttribute('data-logo-cc') || '';
      if (cc) {
        return '<span class="pg-choice__lead"><img class="pg-choice__logo" src="' + escapeHtml(cc) + '" alt="" width="72" height="20" loading="lazy" decoding="async" /></span><span class="pg-choice__text">' + safeLabel + '</span>';
      }
    }
    return '<span class="pg-choice__lead">' + workflowIconSvg(key) + '</span><span class="pg-choice__text">' + safeLabel + '</span>';
  }

  function buildJobsStackHtml() {
    var tradeLabel = trades[state.trade] || 'Job';
    var annual = formatAnnualRange();
    var cells = '';
    for (var i = 0; i < 12; i++) {
      cells += '<span class="pg-job-grid__cell' + (i < 4 ? ' is-accent' : '') + '"></span>';
    }
    return (
      '<div class="pg-job-grid" aria-hidden="true">' +
      '<p class="pg-job-grid__focal"><strong>' +
      escapeHtml(annual) +
      '</strong><span>completed jobs</span></p>' +
      '<div class="pg-job-grid__board" style="grid-template-columns:repeat(12,minmax(0,1fr));">' +
      cells +
      '</div>' +
      '<p class="pg-job-grid__caption"><span>' +
      escapeHtml(tradeLabel) +
      ' \u00b7 raw proof your crew already produces</span></p></div>'
    );
  }

  function proofHighlightCount() {
    var band = proofPct[state.public_proof_percentage];
    if (!band || band.min == null || band.max == null) return 2;
    return Math.max(1, Math.min(10, Math.round(((band.min + band.max) / 2) * 10)));
  }

  function buildProofGridHtml() {
    var lit = proofHighlightCount();
    var cells = '';
    for (var i = 0; i < 10; i++) {
      cells += '<span class="pg-proof-grid__cell' + (i < lit ? ' is-lit' : '') + '"></span>';
    }
    return '<div class="pg-proof-grid" aria-hidden="true">' + cells + '<p class="pg-proof-grid__cap">Illustrative — ' + lit + ' of 10 jobs visible</p></div>';
  }

  function renderGapViz() {
    var el = document.getElementById('pgGapViz');
    if (!el) return;
    var band = proofPct[state.public_proof_percentage];
    var tier = proofTier(state.public_proof_percentage);
    if (!band || tier === 'unknown' || band.min == null || band.max == null) {
      el.innerHTML = '';
      return;
    }
    var pubMid = Math.round(((band.min + band.max) / 2) * 100);
    var gapMid = 100 - pubMid;
    el.innerHTML =
      '<div class="pg-result-bar">' +
      '<div class="pg-result-bar__track" role="img" aria-label="Public proof versus invisible">' +
      '<span class="pg-result-bar__seg--visible" style="width:' + pubMid + '%"></span>' +
      '<span class="pg-result-bar__seg--gap" style="width:' + gapMid + '%"></span></div>' +
      '<div class="pg-result-bar__legend">' +
      '<span><i class="pg-result-bar__dot pg-result-bar__dot--visible"></i> Visible proof ~' + pubMid + '%</span>' +
      '<span><i class="pg-result-bar__dot pg-result-bar__dot--gap"></i> Potentially invisible ~' + gapMid + '%</span>' +
      '</div></div>';
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
        if (key === 'unknown') btn.className += ' pg-choice--span';
        var t = document.createElement('span');
        t.className = 'pg-choice__title';
        t.textContent = item.title || item.label || key;
        var b = document.createElement('span');
        b.className = 'pg-choice__band';
        b.textContent = item.band || '';
        btn.appendChild(t);
        if (item.band) btn.appendChild(b);
      } else if (field === 'current_workflow') {
        btn.className += ' pg-choice--workflow';
        btn.innerHTML = workflowChoiceInner(key, choiceLabel(map, key));
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
    document.querySelectorAll('.pg-review-slot--insight').forEach(function (el) {
      el.classList.remove('is-shown');
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
      (data.extraHtml ? '<div class="proof-gap-insight-card__extra">' + data.extraHtml + '</div>' : '') +
      '</div>';

    void card.offsetWidth;
    card.classList.add('is-visible');
    insightVisible = true;
    if (slot === 'workflow') {
      document.querySelectorAll('.pg-review-slot--insight').forEach(function (el) {
        el.classList.add('is-shown');
      });
    }

    setBottomAction({
      label: ctaLabel || 'Continue →',
      animate: true,
      onClick: function () {
        hideAllInsights();
        goTo(nextState);
      },
    });
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
      if (item.title && item.band) return item.title + ' · ' + item.band;
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
      preloadTradePhoto(key);

      if (key === 'other') {
        var otherField = document.getElementById('pgTradeOtherField');
        if (otherField) otherField.hidden = false;
        saveState();
        track('SurveyQuestionAnswered', {
          question_index: stateIndex('trade'),
          question_id: 'trade',
          answer_key: key,
          feedback_shown: false,
          trade: key,
          other_text_provided: false,
        });
        setBottomAction({
          label: 'Continue \u2192',
          animate: true,
          onClick: function () {
            var inp = document.getElementById('pgTradeOtherInput');
            var val = inp ? sanitizeCustomText(inp.value) : '';
            state.other_trade_text = val;
            saveState();
            if (val) {
              track('SurveyOtherTextProvided', { question_id: 'trade', other_text_provided: true });
            }
            goTo('current_workflow');
          },
        });
        return;
      }

      var otherFieldHide = document.getElementById('pgTradeOtherField');
      if (otherFieldHide) otherFieldHide.hidden = true;
      state.other_trade_text = '';
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

      if (key === 'other_crm') {
        var wfField = document.getElementById('pgWorkflowOtherField');
        if (wfField) wfField.hidden = false;
        saveState();
        feedbackVariant = (WORKFLOW_INSIGHTS[key] || {}).variant || key;
        track('SurveyQuestionAnswered', {
          question_index: stateIndex('current_workflow'),
          question_id: 'current_workflow',
          answer_key: key,
          feedback_shown: false,
          feedback_variant: feedbackVariant,
          trade: state.trade,
          current_workflow: key,
          custom_workflow_provided: false,
        });
        setBottomAction({
          label: 'Continue \u2192',
          animate: true,
          onClick: function () {
            var inp = document.getElementById('pgWorkflowOtherInput');
            var val = inp ? sanitizeCustomText(inp.value) : '';
            state.other_workflow_text = val;
            saveState();
            if (val) {
              track('SurveyOtherTextProvided', { question_id: 'current_workflow', custom_workflow_provided: true });
            }
            var wfFieldHide = document.getElementById('pgWorkflowOtherField');
            if (wfFieldHide) wfFieldHide.hidden = true;
            collapseChoiceList('current_workflow', val || summaryValueText('current_workflow', key), key, function () {
              showInsight('workflow', WORKFLOW_INSIGHTS[key] || WORKFLOW_INSIGHTS.scattered, 'jobs_per_week', 'Continue \u2192');
            });
          },
        });
        return;
      }

      var wfFieldHide = document.getElementById('pgWorkflowOtherField');
      if (wfFieldHide) wfFieldHide.hidden = true;
      state.other_workflow_text = '';
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
      collapseChoiceList('current_workflow', summaryValueText('current_workflow', key), key, function () {
        showInsight('workflow', WORKFLOW_INSIGHTS[key] || WORKFLOW_INSIGHTS.scattered, 'jobs_per_week', 'Continue \u2192');
      });
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
      collapseChoiceList('jobs_per_week', summaryValueText('jobs_per_week', key), key, function () {
        showInsight(
          'jobs',
          {
            headline: 'That’s roughly ' + formatAnnualRange() + ' completed jobs every year.',
            body: highVol
              ? 'Your team is already creating an enormous amount of real-world marketing material.'
              : 'You probably don’t have a content-creation problem.',
            body2: highVol
              ? 'The question is what happens to it after the job.'
              : 'Your company already produces the raw material every week.',
            extraHtml: buildJobsStackHtml(),
          },
          'public_proof_percentage',
          'Continue →'
        );
      });
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

      var insight = { headline: '', body: '', body2: '', extraHtml: buildProofGridHtml() };
      if (tier === 'low') {
        insight.headline = 'That means a lot of work may disappear from public view.';
        insight.body = 'Not because the work wasn’t done — because finished jobs weren’t turned into proof.';
        if (state.unused_jobs_min != null) {
          var rangeTxt =
            state.unused_jobs_max == null
              ? state.unused_jobs_min.toLocaleString() + '+'
              : formatUnusedRange();
          insight.body2 =
            'Based on your answers: ~' + rangeTxt + ' completed jobs/year may not become public proof.';
        }
      } else if (tier === 'mid') {
        insight.headline = 'You’re creating more proof than you’re putting to work.';
        insight.body = 'Make the process consistent without adding another manual marketing task.';
        if (state.unused_jobs_min != null && state.unused_jobs_max != null) {
          insight.body2 =
            'Based on your answers: ~' + formatUnusedRange() + ' completed jobs/year may not become public proof.';
        }
      } else if (tier === 'high') {
        insight.headline = 'You’re already doing the hard part.';
        insight.body = 'The opportunity is removing the manual work required to distribute proof.';
      } else {
        insight.headline = 'Not knowing is useful information too.';
        insight.body =
          'If it’s hard to tell what happens after the crew leaves, the process may not be repeatable yet.';
      }
      collapseChoiceList('public_proof_percentage', summaryValueText('public_proof_percentage', key), key, function () {
        showInsight('proof', insight, 'proof_gap_result', 'See My Proof Gap →');
      });
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function sanitizeCustomText(raw) {
    return String(raw || '')
      .replace(/[<>&"']/g, '')
      .trim()
      .slice(0, 80);
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
      if (state.unused_jobs_min != null && tier !== 'unknown') {
        invWrap.hidden = false;
        inv.textContent =
          state.unused_jobs_max == null
            ? state.unused_jobs_min.toLocaleString() + '+ / year'
            : formatUnusedRange() + ' / year';
      } else {
        invWrap.hidden = true;
      }
    }
    renderGapViz();
    var support = document.getElementById('pgResultSupport');
    if (support) {
      if (tier === 'high') {
        support.textContent =
          'You’re already putting finished jobs in front of customers. JobCapturePro can help remove the manual steps between capture and publish.';
      } else if (tier === 'unknown') {
        support.textContent =
          'When visibility is unclear, proof often stalls after the job. Making the path repeatable is the leverage point.';
      }
    }
  }

  function sourceLabel() {
    return 'Captured from the field';
  }

  function sourceWorkflowHint() {
    if (state.current_workflow === 'housecall_pro') return 'Housecall Pro';
    if (state.current_workflow === 'companycam') return 'CompanyCam';
    if (state.current_workflow === 'other_crm' && state.other_workflow_text) return state.other_workflow_text;
    if (state.current_workflow === 'other_crm') return 'Field / CRM workflow';
    if (state.current_workflow === 'phones_camera_roll') return 'Phones / camera roll';
    return '';
  }

  function workflowShortLabel() {
    return workflows[state.current_workflow] || state.current_workflow || 'Your workflow';
  }

  function brandMark(initial) {
    var letter = (initial || 'J').toString().charAt(0).toUpperCase() || 'J';
    return (
      '<span class="ps-mock__logo pg-dest-mark" aria-hidden="true" style="display:flex;align-items:center;justify-content:center;line-height:1;font-weight:800;color:#fff;font-size:0.85rem;">' +
      escapeHtml(letter) +
      '</span>'
    );
  }

  function revealJobContext() {
    var asset = getTradeAsset(state.trade);
    var title = (asset && asset.title) || JOB_EXAMPLES[state.trade] || 'Finished field job';
    var city = 'Your service area';
    var desc = 'Documented from the field — ready for channels.';
    return { title: title, city: city, desc: desc };
  }

  function renderJobCardMedia() {
    var photoEl = document.getElementById('pgJobPhoto');
    var neutralEl = document.getElementById('pgJobNeutral');
    var badgeEl = document.getElementById('pgJobBadge');
    var tradeEl = document.getElementById('pgJobTrade');
    var url = getJobPhotoUrl();
    var tradeLabel =
      state.trade === 'other' && state.other_trade_text
        ? state.other_trade_text
        : trades[state.trade] || 'Trade';
    if (badgeEl) badgeEl.textContent = 'Completed job';
    if (tradeEl) tradeEl.textContent = tradeLabel;
    if (photoEl && neutralEl) {
      if (url) {
        photoEl.src = url;
        photoEl.alt = revealJobContext().title;
        photoEl.hidden = false;
        neutralEl.hidden = true;
      } else {
        photoEl.hidden = true;
        photoEl.removeAttribute('src');
        neutralEl.hidden = false;
      }
    }
  }

  /** Product destination panels — reused proof-sprint ps-mock markup (user-controlled only). */
  function renderDestPanel(dest) {
    var panel = document.getElementById('pgDestPanel');
    if (!panel) return;
    var job = revealJobContext();
    var photoUrl = getJobPhotoUrl();
    var mapUrl = panel.getAttribute('data-map') || '';
    var qrUrl = panel.getAttribute('data-qr') || '';
    var tradeLabel = trades[state.trade] || 'Trade';
    if (state.trade === 'other' && state.other_trade_text) tradeLabel = state.other_trade_text;
    var biz = tradeLabel + ' Co';
    var title = escapeHtml(job.title);
    var city = escapeHtml(job.city);
    var desc = escapeHtml(job.desc);
    var bizEsc = escapeHtml(biz);
    var mark = brandMark(tradeLabel);
    var photo =
      photoUrl !== ''
        ? '<img class="ps-mock__photo" src="' + escapeHtml(photoUrl) + '" alt="" width="640" height="400" loading="lazy" decoding="async" />'
        : '<div class="ps-mock__photo ps-mock__photo--fallback" aria-hidden="true"><span>Completed job</span></div>';
    var thumb =
      photoUrl !== ''
        ? '<img class="ps-mock__thumb ps-mock__photo" src="' + escapeHtml(photoUrl) + '" alt="" width="160" height="120" loading="lazy" decoding="async" />'
        : '<div class="ps-mock__thumb ps-mock__thumb--fallback" aria-hidden="true"></div>';

    if (dest === 'website') {
      var mapBlock = mapUrl
        ? '<div class="ps-mock-map ps-mock-map--pins" aria-hidden="true">' +
          '<img class="ps-mock-map__img" src="' +
          escapeHtml(mapUrl) +
          '" alt="" width="640" height="280" loading="lazy" decoding="async" />' +
          '<span class="ps-mock-map__pin is-active" style="left:52%;top:42%;"></span>' +
          '<span class="ps-mock-map__pin" style="left:34%;top:58%;"></span>' +
          '<span class="ps-mock-map__pin" style="left:68%;top:55%;"></span>' +
          '<span class="ps-mock-map__pin" style="left:44%;top:28%;"></span>' +
          '<span class="ps-mock-map__veil"></span>' +
          '<span class="ps-mock-map__chip">Service area check-ins</span>' +
          '</div>'
        : '<div class="ps-mock-map ps-mock-map--neutral" aria-hidden="true">' +
          '<span class="ps-mock-map__pin is-active"></span>' +
          '<span class="ps-mock-map__label">Service area</span></div>';

      panel.innerHTML =
        '<div class="ps-mock ps-mock--browser ps-mock--browser-fit">' +
        '<div class="ps-mock-browser__bar"><span></span><span></span><span></span>' +
        '<div class="ps-mock-browser__url">yourcompany.com/service-area</div></div>' +
        '<div class="ps-mock-browser__body ps-mock-browser__body--fit">' +
        '<div class="ps-mock-browser__topline">' +
        mark +
        '<div><p class="ps-mock-browser__kicker">Service area</p><h4>Recent check-ins</h4></div></div>' +
        mapBlock +
        '<article class="ps-mock-jobcard ps-mock-jobcard--fit is-active">' +
        thumb +
        '<div><strong>' +
        title +
        '</strong><span>Just published</span></div></article></div></div>';
      return;
    }
    if (dest === 'google') {
      panel.innerHTML =
        '<div class="ps-mock ps-mock--gbp">' +
        '<div class="ps-mock-gbp__brand">' +
        mark +
        '<div><strong>' +
        bizEsc +
        '</strong><span>Google Business Profile · Update</span></div>' +
        '<em>Posted when connected</em></div>' +
        '<div class="ps-mock__media">' +
        photo +
        '</div>' +
        '<div class="ps-mock-gbp__copy"><strong>Just finished</strong><p>' +
        title +
        ' completed in your service area.</p><p>Real work documented from the field.</p></div></div>';
      return;
    }
    if (dest === 'social') {
      panel.innerHTML =
        '<div class="ps-mock ps-mock--social">' +
        '<div class="ps-mock-social__head">' +
        mark +
        '<div><strong>' +
        bizEsc +
        '</strong><span>Posted when connected</span></div></div>' +
        '<p class="ps-mock-social__copy">' +
        title +
        ' completed today in your service area.</p>' +
        '<p class="ps-mock-social__copy ps-mock-social__copy--sec">Another real job documented from the field.</p>' +
        '<div class="ps-mock__media">' +
        photo +
        '</div>' +
        '<div class="ps-mock-social__reactions"><span>Like</span><span>Comment</span><span>Share</span></div></div>';
      return;
    }
    if (dest === 'reviews') {
      panel.innerHTML =
        '<div class="ps-mock ps-mock--review">' +
        '<p class="ps-mock-sms__label">Review request</p>' +
        '<div class="ps-mock-review__phone">' +
        '<div class="ps-mock-review__phone-bar">' +
        '<span class="ps-mock-review__app">Messages</span>' +
        '<strong class="ps-mock-review__contact">Customer</strong>' +
        '</div>' +
        '<div class="ps-mock-review__thread">' +
        '<div class="ps-mock-sms__bubble">Thanks again for choosing ' +
        bizEsc +
        '. If we earned it, would you mind leaving a quick review?</div>' +
        '<div class="ps-mock-sms__bubble is-link">review.jobcapturepro.com/\u2026</div>' +
        '<p class="ps-mock-review__time">Delivered \u00b7 Just now</p></div></div>' +
        (qrUrl
          ? '<div class="ps-mock-qr ps-mock-qr--compact"><img src="' +
            escapeHtml(qrUrl) +
            '" alt="" width="56" height="56" loading="lazy" decoding="async" />' +
            '<div><strong>Scan on-site</strong><span>Show this QR at the job</span></div></div>'
          : '') +
        '</div>';
      return;
    }
    var tradeInitial = (tradeLabel.charAt(0) || 'J').toUpperCase();
    panel.innerHTML =
      '<div class="ps-mock ps-mock--directory">' +
      '<p class="ps-mock-dir__label">JobCapturePro Directory</p>' +
      '<article class="ps-mock-dir__card">' +
      '<div class="ps-mock-dir__head">' +
      '<div class="ps-mock-dir__avatar" style="display:flex;align-items:center;justify-content:center;font-size:1.1rem;">' +
      escapeHtml(tradeInitial) +
      '</div>' +
      '<div><strong>' +
      bizEsc +
      '</strong><span>' +
      escapeHtml(tradeLabel) +
      ' \u00b7 ' +
      city +
      '</span></div></div>' +
      '<div class="ps-mock-dir__latest">' +
      thumb +
      '<div><em>Latest completed job</em><strong>' +
      title +
      '</strong><span>Documented on site \u00b7 Just published</span></div></div>' +
      '<div class="ps-mock-dir__meta"><span>Service area activity</span></div></article></div>';
  }

  function setDestTab(dest, options) {
    options = options || {};
    if (DEST_TABS.indexOf(dest) === -1) dest = 'website';
    activeDest = dest;
    document.querySelectorAll('.pg-dest-tab').forEach(function (tab) {
      var on = tab.getAttribute('data-dest') === dest;
      tab.classList.toggle('is-active', on);
      tab.setAttribute('aria-selected', on ? 'true' : 'false');
      tab.setAttribute('tabindex', on ? '0' : '-1');
    });
    var panel = document.getElementById('pgDestPanel');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var apply = function () {
      renderDestPanel(dest);
      if (panel) panel.classList.remove('is-switching');
    };
    if (panel && options.animate && !reduce) {
      panel.classList.add('is-switching');
      window.setTimeout(apply, 160);
    } else {
      apply();
    }
    if (options.fromUser) {
      autoplayDisabled = true;
      stopAutoplay();
      if (!destTracked[dest]) {
        destTracked[dest] = true;
        track('ProductDestinationClicked', { destination: dest, trade: state.trade });
      }
      track('ProductDestinationViewed', { destination: dest, source: 'manual' });
    }
  }

  function bindDestTabs() {
    var tabs = document.querySelectorAll('.pg-dest-tab');
    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        var dest = tab.getAttribute('data-dest') || 'website';
        setDestTab(dest, { fromUser: true, animate: true });
      });
      tab.addEventListener('keydown', function (e) {
        var next = index;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = (index + 1) % tabs.length;
        else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = (index - 1 + tabs.length) % tabs.length;
        else return;
        e.preventDefault();
        tabs[next].focus();
        setDestTab(tabs[next].getAttribute('data-dest') || 'website', { fromUser: true, animate: true });
      });
    });

    var panel = document.getElementById('pgDestPanel');
    if (panel && !panel.getAttribute('data-swipe-bound')) {
      panel.setAttribute('data-swipe-bound', '1');
      var touchX = null;
      panel.addEventListener(
        'touchstart',
        function (e) {
          if (!e.changedTouches || !e.changedTouches[0]) return;
          touchX = e.changedTouches[0].clientX;
        },
        { passive: true }
      );
      panel.addEventListener(
        'touchend',
        function (e) {
          if (touchX === null || !e.changedTouches || !e.changedTouches[0]) return;
          var dx = e.changedTouches[0].clientX - touchX;
          touchX = null;
          if (Math.abs(dx) < 48) return;
          var i = DEST_TABS.indexOf(activeDest);
          if (i < 0) i = 0;
          if (dx < 0 && i < DEST_TABS.length - 1) setDestTab(DEST_TABS[i + 1], { fromUser: true, animate: true });
          else if (dx > 0 && i > 0) setDestTab(DEST_TABS[i - 1], { fromUser: true, animate: true });
        },
        { passive: true }
      );
    }
  }

  function renderReveal() {
    var tradeLabel = trades[state.trade] || state.trade || 'field';
    var title = document.getElementById('pgRevealTitle');
    if (title) title.textContent = 'Here\u2019s what one finished ' + tradeLabel + ' job could become.';

    var jobTitle = document.getElementById('pgJobTitle');
    var src = document.getElementById('pgRevealSource');
    var ctx = revealJobContext();

    if (jobTitle) jobTitle.textContent = ctx.title;
    if (src) src.textContent = sourceLabel();
    renderJobCardMedia();
    setDestTab('website', { fromUser: false, animate: false });

    var contextEl = document.querySelector('[data-pg-review-slot="reveal"] [data-pg-review-context]');
    if (contextEl) {
      if (state.trade && state.trade !== 'hvac') {
        contextEl.textContent = 'From an agency using JobCapturePro with an HVAC client';
        contextEl.hidden = false;
      } else {
        contextEl.textContent = '';
        contextEl.hidden = true;
      }
    }

    bindAutoplayPause();
    autoplayDisabled = false;
    setTimeout(startAutoplay, 800);
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearTimeout(autoplayTimer);
      autoplayTimer = null;
    }
  }

  function autoplayTick() {
    if (autoplayDisabled) return;
    if (state.current_state !== 'product_reveal') {
      stopAutoplay();
      return;
    }
    if (autoplayPaused) {
      autoplayTimer = setTimeout(autoplayTick, 400);
      return;
    }
    autoplayIndex++;
    if (autoplayIndex >= autoplayOrder.length) {
      stopAutoplay();
      return;
    }
    var dest = autoplayOrder[autoplayIndex];
    setDestTab(dest, { fromUser: false, animate: true });
    track('ProductDestinationViewed', { destination: dest, source: 'auto' });
    autoplayTimer = setTimeout(autoplayTick, 4750);
  }

  function startAutoplay() {
    if (autoplayDisabled) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    stopAutoplay();
    autoplayIndex = 0;
    track('ProductDestinationViewed', { destination: autoplayOrder[0], source: 'auto' });
    autoplayTimer = setTimeout(autoplayTick, 5300);
  }

  function bindAutoplayPause() {
    var panel = document.getElementById('pgDestPanel');
    if (!panel || panel.getAttribute('data-autoplay-bound')) return;
    panel.setAttribute('data-autoplay-bound', '1');
    panel.addEventListener('mouseenter', function () { autoplayPaused = true; });
    panel.addEventListener('mouseleave', function () { autoplayPaused = false; });
    panel.addEventListener('focusin', function () { autoplayPaused = true; });
    panel.addEventListener('focusout', function () { autoplayPaused = false; });
    try {
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) { autoplayPaused = true; }
        else { autoplayPaused = false; }
      });
    } catch (e) {}
  }

  function renderTrialSummary() {
    var list = document.getElementById('pgTrialSummary');
    var annualEl = document.getElementById('pgPlanAnnual');
    if (annualEl) annualEl.textContent = formatAnnualRange() || '\u2014';
    if (!list) return;

    var tradeChip = (state.trade === 'other' && state.other_trade_text)
      ? escapeHtml(state.other_trade_text)
      : escapeHtml(trades[state.trade] || state.trade || '\u2014');
    var workflowChip = (state.current_workflow === 'other_crm' && state.other_workflow_text)
      ? escapeHtml(state.other_workflow_text)
      : escapeHtml(workflows[state.current_workflow] || state.current_workflow || '\u2014');
    var proofChipLabel = proofBandLabel(state.public_proof_percentage);

    list.innerHTML =
      '<li>' + tradeChip + '</li>' +
      '<li>' + escapeHtml((jobsBuckets[state.jobs_per_week_bucket] || {}).weekly_label || '\u2014') + ' jobs/week</li>' +
      '<li>' + workflowChip + '</li>' +
      '<li>Public proof: ' + escapeHtml(proofChipLabel) + '</li>';

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
    var base =
      (window.JCP_ONBOARDING && window.JCP_ONBOARDING.url) ||
      boot.trialBase ||
      'https://app.jobcapturepro.com/onboarding';
    try {
      var handoffExtra = {
        lp_variant: LP_VARIANT,
      };
      if (state.handoff_token) handoffExtra.pg_handoff = state.handoff_token;
      if (state.session_id) handoffExtra.survey_session_id = state.session_id;

      var href = base;
      if (window.JCPOnboardingHandoff && typeof window.JCPOnboardingHandoff.decorateHref === 'function') {
        href = window.JCPOnboardingHandoff.decorateHref(href, handoffExtra, 'proof_gap_survey_trial') || href;
      } else {
        var u = new URL(href, window.location.origin);
        Object.keys(handoffExtra).forEach(function (k) {
          if (handoffExtra[k]) u.searchParams.set(k, handoffExtra[k]);
        });
        if (state.email) u.searchParams.set('email', state.email);
        href = u.toString();
      }
      a.href = href;
    } catch (e) {
      a.href = base;
    }
  }

  function goTo(id) {
    clearAdvance();
    stopAutoplay();
    if (STATES.indexOf(id) === -1) return;

    // Dwell timing for previous step (once per leave; capped to avoid tab-sleep outliers).
    if (stepEnteredId && stepEnteredAt) {
      var dwell = Date.now() - stepEnteredAt;
      if (dwell >= 250 && dwell <= 30 * 60 * 1000) {
        track('SurveyStepTiming', {
          question_id: stepEnteredId,
          screen: stepEnteredId,
          duration_ms: dwell,
          answer_value: String(dwell),
        });
      }
    }
    stepEnteredId = id;
    stepEnteredAt = Date.now();

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
      if (!milestoneViewed.result) {
        milestoneViewed.result = true;
        track('SurveyResultViewed', {
          trade: state.trade,
          current_workflow: state.current_workflow,
          jobs_per_week_bucket: state.jobs_per_week_bucket,
          public_proof_percentage: state.public_proof_percentage,
          annual_jobs_min: state.annual_jobs_min,
          annual_jobs_max: state.annual_jobs_max,
        });
      }
    }
    if (id === 'email_capture' && !milestoneViewed.email_view) {
      milestoneViewed.email_view = true;
      track('EmailCaptureViewed', { trade: state.trade });
    }
    if (id === 'product_reveal') {
      renderReveal();
      if (!milestoneViewed.reveal) {
        milestoneViewed.reveal = true;
        track('ProductRevealStarted', {
          trade: state.trade,
          current_workflow: state.current_workflow,
          cta_source: 'product_reveal',
        });
      }
    }
    if (id === 'trial_bridge') {
      renderTrialSummary();
      if (!milestoneViewed.trial_view) {
        milestoneViewed.trial_view = true;
        track('TrialCTAViewed', { trade: state.trade, cta_source: 'trial_bridge' });
      }
    }

    syncQuestionUI(id);
    syncBottomActionForState(id);
    bindKeyboardSafe();

    try {
      var stage = document.getElementById('pgStage');
      if (stage) stage.scrollTop = 0;
      window.scrollTo({ top: 0, behavior: 'auto' });
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
      var industry = mapTradeToIndustry(state.trade);
      var user = {
        email: email,
        firstName: deriveFirstName(email),
        lastName: '',
        businessName: '',
        phone: '',
        goals: [],
        niche: industry || state.trade || '',
        industry: industry,
        trade: state.trade || '',
        source: 'proof_gap_survey',
      };
      if (state.other_trade_text) user.other_trade_text = state.other_trade_text;
      if (state.other_workflow_text) user.other_workflow_text = state.other_workflow_text;
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
    if (state.other_trade_text) body.other_trade_text = state.other_trade_text;
    if (state.other_workflow_text) body.other_workflow_text = state.other_workflow_text;

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

        state.email = email;
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
        updateTrialHref();
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
    var back = document.getElementById('pgBack');
    if (back) back.addEventListener('click', goBack);

    document.querySelectorAll('[data-pg-change]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var slot = btn.getAttribute('data-pg-change');
        var field =
          slot === 'workflow' ? 'current_workflow' : slot === 'jobs' ? 'jobs_per_week' : slot === 'proof' ? 'public_proof_percentage' : '';
        if (!field) return;
        expandChoiceList(field);
        var host = document.querySelector('[data-pg-choices="' + field + '"]');
        if (host) {
          try {
            host.scrollIntoView({ block: 'nearest', behavior: 'auto' });
          } catch (e) {}
        }
      });
    });

    bindDestTabs();

    var emailForm = document.getElementById('pgEmailForm');
    if (emailForm) emailForm.addEventListener('submit', submitEmail);

    window.addEventListener('resize', syncBottomPad);

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
    initCreativeConcept();
    if (state.trade) preloadTradePhoto(state.trade);
    saveState();

    if (!landingTracked) {
      try {
        if (sessionStorage.getItem('jcp_pg_landing_tracked') === '1') {
          landingTracked = true;
        }
      } catch (eLand) {}
    }
    if (!landingTracked) {
      landingTracked = true;
      try {
        sessionStorage.setItem('jcp_pg_landing_tracked', '1');
      } catch (eSet) {}
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
