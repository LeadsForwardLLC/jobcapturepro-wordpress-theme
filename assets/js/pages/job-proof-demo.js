/**
 * Job Proof Demo — teaser → email+trade opt-in → personalized demo → trial.
 * Reuses /demo/ GHL webhook + Meta Lead (demo_opt_in) after successful upsert.
 * Analytics: dataLayer only (no posthog.capture).
 */
(function () {
  'use strict';

  var LP_VARIANT = 'job_proof_demo';
  var STORAGE_PREFIX = 'jcp_proof_evt_';
  var DEMO_SESSION_KEY = 'jcp_jpd_demo_session';

  var TRADE_JOBS = {
    plumbing: { label: 'plumbing', title: 'Water heater replacement', city: 'Austin, TX', photo: '' },
    hvac: { label: 'HVAC', title: 'AC system replacement', city: 'Austin, TX', photo: '' },
    electrical: { label: 'electrical', title: 'Electrical panel upgrade', city: 'Austin, TX', photo: '' },
    roofing: { label: 'roofing', title: 'Asphalt shingle roof replacement', city: 'Austin, TX', photo: '' },
    remodeling: { label: 'remodeling', title: 'Kitchen remodel', city: 'Austin, TX', photo: '' },
    painting: { label: 'painting', title: 'Exterior home painting', city: 'Austin, TX', photo: '' },
    landscaping: { label: 'landscaping', title: 'Landscape installation', city: 'Austin, TX', photo: '' },
    outdoor: { label: 'outdoor', title: 'Landscape installation', city: 'Austin, TX', photo: '' },
    'garage-door': { label: 'garage door', title: 'Garage door replacement', city: 'Austin, TX', photo: '' },
    'pest-control': { label: 'pest control', title: 'Treatment/service job', city: 'Austin, TX', photo: '' },
    'tree-service': { label: 'tree service', title: 'Tree removal', city: 'Austin, TX', photo: '' },
    cleaning: { label: 'power washing', title: 'Driveway cleaning', city: 'Austin, TX', photo: '' },
    default: { label: 'service', title: 'Completed service job', city: 'Austin, TX', photo: '' },
  };

  var state = {
    teaserDone: false,
    optedIn: false,
    contactSaved: false,
    demoCompleted: false,
    trialClicked: false,
    animating: false,
    engaged: false,
    email: '',
    niche: '',
    nicheLabel: '',
    timer: 0,
    sessionId: '',
    startedAt: Date.now(),
  };

  var EXIT_SHOWN_KEY = 'jcp_jpd_exit_shown';
  var EXIT_DISMISS_KEY = 'jcp_jpd_exit_dismissed';
  var OPTIN_SESSION_KEY = 'jcp_jpd_opted_in';
  var DEMO_DONE_KEY = 'jcp_jpd_demo_done';

  function attrPayload() {
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        return window.JCPLeadAttribution.getPayload() || {};
      }
    } catch (e) {}
    return {};
  }

  function trackProof(eventName, extra) {
    try {
      var key = STORAGE_PREFIX + eventName;
      if (sessionStorage.getItem(key)) return;
      sessionStorage.setItem(key, '1');
    } catch (e) {}

    var attr = attrPayload();
    var payload = {
      event: eventName,
      lp_variant: attr.lp_variant || LP_VARIANT,
      page_path: location.pathname,
    };
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'referrer'].forEach(function (k) {
      if (attr[k]) payload[k] = attr[k];
    });
    if (extra && typeof extra === 'object') {
      Object.keys(extra).forEach(function (k) {
        payload[k] = extra[k];
      });
    }
    delete payload.email;
    delete payload.phone;
    delete payload.first_name;
    delete payload.last_name;
    delete payload.name;
    delete payload.contact_id;

    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(payload);
    } catch (err) {}
  }

  function getDemoSessionId() {
    if (state.sessionId) return state.sessionId;
    try {
      var existing = sessionStorage.getItem(DEMO_SESSION_KEY);
      if (existing) {
        state.sessionId = existing;
        return existing;
      }
    } catch (e) {}
    state.sessionId =
      'jpd_' +
      Date.now().toString(36) +
      '_' +
      Math.random().toString(36).slice(2, 10);
    try {
      sessionStorage.setItem(DEMO_SESSION_KEY, state.sessionId);
    } catch (e2) {}
    return state.sessionId;
  }

  function pushDemoOptInDataLayer(bizType) {
    try {
      if (sessionStorage.getItem('jcp_datalayer_demo_opt_in')) return;
      window.dataLayer = window.dataLayer || [];
      var attr = attrPayload();
      window.dataLayer.push({
        event: 'demo_opt_in',
        lead_type: 'demo',
        source: 'job_proof_demo',
        business_type: bizType || '',
        utm_source: attr.utm_source || '',
        utm_medium: attr.utm_medium || '',
        utm_campaign: attr.utm_campaign || '',
        utm_content: attr.utm_content || '',
        lp_variant: attr.lp_variant || LP_VARIANT,
        fbclid: attr.fbclid || '',
      });
      window.dataLayer.push({
        event: 'DemoFormSubmitted',
        business_type: bizType || '',
        utm_source: attr.utm_source || '',
        utm_medium: attr.utm_medium || '',
        utm_campaign: attr.utm_campaign || '',
        utm_content: attr.utm_content || '',
        lp_variant: attr.lp_variant || LP_VARIANT,
      });
      sessionStorage.setItem('jcp_datalayer_demo_opt_in', '1');
    } catch (err) {}
  }

  function pushDemoFormViewed() {
    try {
      if (sessionStorage.getItem('jcp_dl_alias_DemoFormViewed')) return;
      window.dataLayer = window.dataLayer || [];
      var attr = attrPayload();
      window.dataLayer.push({
        event: 'DemoFormViewed',
        source: 'job_proof_demo',
        lp_variant: attr.lp_variant || LP_VARIANT,
      });
      sessionStorage.setItem('jcp_dl_alias_DemoFormViewed', '1');
    } catch (e) {}
    trackProof('proof_optin_viewed');
  }

  function postDemoEvent(eventType, metadata) {
    var url =
      (window.JCP_DEMO_EVENT && window.JCP_DEMO_EVENT.rest_url) ||
      (window.JCP_DEMO_SURVEY && window.JCP_DEMO_SURVEY.rest_event_url) ||
      '';
    if (!url) return;
    var attr = attrPayload();
    var body = {
      session_id: getDemoSessionId(),
      event_type: eventType,
      email: state.email || undefined,
      first_name: deriveFirstName(state.email),
      business_type: state.niche || undefined,
      metadata: metadata || undefined,
      utm_source: attr.utm_source || undefined,
      utm_medium: attr.utm_medium || undefined,
      utm_campaign: attr.utm_campaign || undefined,
      utm_content: attr.utm_content || undefined,
      fbclid: attr.fbclid || undefined,
      landing_page: location.href,
    };
    try {
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
        keepalive: true,
      }).catch(function () {});
    } catch (e) {}
  }

  var MARKETING_DEFAULTS = {
    utm_source: 'jobcapturepro.com',
    utm_medium: 'website',
    utm_campaign: 'onboarding',
  };

  function isMarketingDefault(key, value) {
    return String(value || '') === MARKETING_DEFAULTS[key];
  }

  function buildTrialUrl(surface) {
    var base =
      (window.JCP_ONBOARDING && window.JCP_ONBOARDING.url) ||
      'https://app.jobcapturepro.com/onboarding?sessionId=75ad8454-312e-4224-95b7-8f48f5cd0277&step=1';
    var u;
    try {
      u = new URL(base, location.origin);
    } catch (e) {
      return base;
    }
    var attr = attrPayload();
    Object.keys(MARKETING_DEFAULTS).forEach(function (k) {
      if (!u.searchParams.get(k)) u.searchParams.set(k, MARKETING_DEFAULTS[k]);
    });
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'lp_variant'].forEach(function (k) {
      var val = attr[k];
      if (!val) return;
      var current = u.searchParams.get(k) || '';
      if (!current || isMarketingDefault(k, current) || k === 'utm_content' || k === 'fbclid' || k === 'lp_variant' || k === 'utm_term') {
        u.searchParams.set(k, String(val));
      }
    });
    if (!u.searchParams.get('lp_variant')) u.searchParams.set('lp_variant', LP_VARIANT);
    if (surface) u.searchParams.set('jcp_surface', String(surface));
    // Prefill only via existing production-safe params when email is known (same as /demo/ handoff).
    if (state.email && !u.searchParams.get('email')) {
      u.searchParams.set('email', state.email);
    }
    if (state.niche && !u.searchParams.get('industryId') && !u.searchParams.get('niche')) {
      u.searchParams.set('niche', state.niche);
    }
    var first = deriveFirstName(state.email);
    if (first && !u.searchParams.get('first_name')) {
      u.searchParams.set('first_name', first);
    }
    return u.toString();
  }

  function decorateTrialLinks() {
    document.querySelectorAll('[data-jpd-trial], a[href*="app.jobcapturepro.com/onboarding"]').forEach(function (a) {
      var source = a.getAttribute('data-jpd-source') || 'trial';
      if (!a.hasAttribute('data-jpd-trial')) a.setAttribute('data-jpd-trial', '');
      a.setAttribute('href', buildTrialUrl('job_proof_demo_' + source));
    });
    document.querySelectorAll('a[href*="/personalized-demo"]').forEach(function (a) {
      a.setAttribute('data-jpd-expert', '');
    });
  }

  function deriveFirstName(email) {
    var local = String(email || '').split('@')[0] || '';
    local = local.replace(/[._-]+/g, ' ').trim();
    if (!local) return 'there';
    return local.charAt(0).toUpperCase() + local.slice(1, 40);
  }

  function normalizeNiche(raw) {
    var slug = String(raw || '')
      .toLowerCase()
      .trim()
      .replace(/['"]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    if (!slug) return 'default';
    var aliases = {
      'hvac-heating-cooling': 'hvac',
      'water-heaters': 'plumbing',
      landscaping: 'outdoor',
      'lawn-care': 'outdoor',
      'tree-service': 'tree-service',
      'pressure-washing': 'cleaning',
      'power-washing': 'cleaning',
      painting: 'painting',
      'garage-doors': 'garage-door',
      'garage-door': 'garage-door',
      'pest-control': 'pest-control',
      remodeling: 'remodeling',
      'general-contracting': 'remodeling',
    };
    if (TRADE_JOBS[slug]) return slug;
    if (aliases[slug]) return aliases[slug];
    if (slug.indexOf('plumb') !== -1) return 'plumbing';
    if (slug.indexOf('hvac') !== -1 || slug.indexOf('heat') !== -1) return 'hvac';
    if (slug.indexOf('electr') !== -1) return 'electrical';
    if (slug.indexOf('roof') !== -1) return 'roofing';
    if (slug.indexOf('paint') !== -1) return 'painting';
    if (slug.indexOf('land') !== -1 || slug.indexOf('lawn') !== -1) return 'landscaping';
    if (slug.indexOf('garage') !== -1) return 'garage-door';
    if (slug.indexOf('pest') !== -1) return 'pest-control';
    if (slug.indexOf('tree') !== -1) return 'tree-service';
    if (slug.indexOf('wash') !== -1 || slug.indexOf('clean') !== -1) return 'cleaning';
    if (slug.indexOf('remodel') !== -1 || slug.indexOf('kitchen') !== -1) return 'remodeling';
    return 'default';
  }

  function applyJobPersona(nicheRaw, label) {
    var key = normalizeNiche(nicheRaw || label);
    var job = TRADE_JOBS[key] || TRADE_JOBS.default;
    var tradeWord = label || job.label || key;
    state.nicheLabel = tradeWord;
    document.querySelectorAll('[data-jpd-job-title]').forEach(function (el) {
      el.textContent = job.title;
    });
    document.querySelectorAll('[data-jpd-job-city]').forEach(function (el) {
      el.textContent = job.city;
    });
    var heading = document.querySelector('[data-jpd-full-heading]');
    if (heading) {
      heading.textContent = 'Here’s what one ' + String(tradeWord).toLowerCase() + ' job can become.';
    }
    var gbp = document.querySelector('[data-jpd-gbp-headline]');
    if (gbp) gbp.textContent = 'Just finished another ' + job.title.toLowerCase() + ' in Austin';
    var social = document.querySelector('[data-jpd-social-copy]');
    if (social) social.textContent = 'Another job wrapped. ' + job.title + ' done right — proof from the field.';
    var dir = document.querySelector('[data-jpd-directory-latest]');
    if (dir) dir.textContent = 'Latest: ' + job.title;
  }

  /* ---- Trade combobox (same options JSON as /demo/) ---- */
  var nicheOptions = [];
  var nicheActive = -1;

  function loadNicheOptions() {
    var el = document.getElementById('jcpBusinessTypeOptions');
    if (!el) return;
    try {
      nicheOptions = JSON.parse(el.textContent || '[]') || [];
    } catch (e) {
      nicheOptions = [];
    }
  }

  function getBusinessTypeValue(prefix) {
    prefix = prefix || 'jpd';
    var niche = (document.getElementById(prefix + '-niche') || {}).value || '';
    var other = (document.getElementById(prefix + '-nicheOther') || {}).value || '';
    if (niche && niche !== 'other') return niche;
    if (other) return other;
    return (document.getElementById(prefix + '-nicheSearch') || {}).value || '';
  }

  function getBusinessTypeLabel(prefix) {
    var val = getBusinessTypeValue(prefix);
    for (var i = 0; i < nicheOptions.length; i++) {
      if (String(nicheOptions[i].value) === String(val) || String(nicheOptions[i].label) === String(val)) {
        return nicheOptions[i].label;
      }
    }
    return val;
  }

  function closeNicheList(prefix) {
    prefix = prefix || 'jpd';
    var list = document.getElementById(prefix + '-nicheListbox');
    var input = document.getElementById(prefix + '-nicheSearch');
    if (list) list.hidden = true;
    if (input) {
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    }
    nicheActive = -1;
  }

  function openNicheList(query, prefix) {
    prefix = prefix || 'jpd';
    var list = document.getElementById(prefix + '-nicheListbox');
    var input = document.getElementById(prefix + '-nicheSearch');
    if (!list || !input) return;
    var q = String(query || '').toLowerCase().trim();
    var matches = nicheOptions.filter(function (opt) {
      if (!q) return true;
      return String(opt.label || '').toLowerCase().indexOf(q) !== -1;
    }).slice(0, 12);
    list.innerHTML = '';
    matches.forEach(function (opt, idx) {
      var li = document.createElement('li');
      li.className = 'survey-combobox__option';
      li.id = prefix + '-niche-opt-' + idx;
      li.setAttribute('role', 'option');
      li.textContent = opt.label;
      li.addEventListener('mousedown', function (e) {
        e.preventDefault();
        selectNiche(opt, prefix);
      });
      list.appendChild(li);
    });
    list.hidden = matches.length === 0;
    input.setAttribute('aria-expanded', matches.length ? 'true' : 'false');
    nicheActive = -1;
  }

  function selectNiche(opt, prefix) {
    prefix = prefix || 'jpd';
    var input = document.getElementById(prefix + '-nicheSearch');
    var niche = document.getElementById(prefix + '-niche');
    var other = document.getElementById(prefix + '-nicheOther');
    if (input) input.value = opt.label;
    if (niche) niche.value = opt.value || '';
    if (other) other.value = '';
    closeNicheList(prefix);
  }

  function setupCombobox(prefix) {
    prefix = prefix || 'jpd';
    loadNicheOptions();
    var input = document.getElementById(prefix + '-nicheSearch');
    if (!input) return;
    input.addEventListener('input', function () {
      input.classList.remove('is-error');
      var niche = document.getElementById(prefix + '-niche');
      var other = document.getElementById(prefix + '-nicheOther');
      if (niche) niche.value = '';
      if (other) other.value = input.value.trim();
      openNicheList(input.value, prefix);
    });
    input.addEventListener('focus', function () {
      openNicheList(input.value, prefix);
    });
    input.addEventListener('blur', function () {
      window.setTimeout(function () {
        closeNicheList(prefix);
      }, 150);
    });
  }

  /* ---- Teaser ---- */
  function clearTimer() {
    if (state.timer) {
      window.clearTimeout(state.timer);
      state.timer = 0;
    }
  }

  function runTeaser() {
    clearTimer();
    state.engaged = true;
    state.animating = true;
    trackProof('proof_demo_started', { source: 'teaser' });
    trackProof('proof_job_viewed');

    var status = document.getElementById('jpdTeaserStatus');
    var checkin = document.getElementById('jpdTeaserCheckin');
    var nodes = document.getElementById('jpdTeaserNodes');
    var actions = document.querySelector('[data-jpd-teaser-actions]');
    var job = document.querySelector('[data-jpd-teaser-job]');

    if (actions) actions.hidden = true;
    if (status) {
      status.hidden = false;
      status.textContent = 'Creating job proof…';
    }

    var steps = [
      { t: 0, fn: function () { if (status) status.textContent = 'Creating job proof…'; } },
      {
        t: 1800,
        fn: function () {
          if (status) status.textContent = 'Check-in created';
          if (checkin) checkin.hidden = false;
          if (job) job.classList.add('is-dimmed');
          trackProof('proof_checkin_created');
        },
      },
      {
        t: 3600,
        fn: function () {
          if (status) status.textContent = 'Adding service + location context…';
        },
      },
      {
        t: 5200,
        fn: function () {
          if (status) status.textContent = 'Publishing across connected destinations…';
          if (nodes) {
            nodes.hidden = false;
            var items = nodes.querySelectorAll('[data-node]');
            var i = 0;
            function light() {
              if (i < items.length) {
                items[i].classList.add('is-lit');
                i += 1;
                state.timer = window.setTimeout(light, 700);
                return;
              }
              finishTeaser();
            }
            light();
          } else {
            finishTeaser();
          }
        },
      },
    ];

    steps.forEach(function (s) {
      window.setTimeout(s.fn, s.t);
    });
  }

  function finishTeaser() {
    state.teaserDone = true;
    state.animating = false;
    var status = document.getElementById('jpdTeaserStatus');
    if (status) status.textContent = 'One finished job is ready to work across your presence.';
    revealOptin();
  }

  function revealOptin() {
    var optin = document.querySelector('[data-jpd-optin]');
    if (!optin) return;
    optin.hidden = false;
    pushDemoFormViewed();
    window.setTimeout(function () {
      optin.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 200);
  }

  /* ---- Opt-in / GHL ---- */
  function validEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim());
  }

  function submitOptIn(attempt, source) {
    attempt = attempt || 1;
    source = source || 'inline';
    var isExit = source === 'exit';
    var prefix = isExit ? 'jpd-exit' : 'jpd';
    var emailEl = document.getElementById(prefix + '-email');
    var nicheSearch = document.getElementById(prefix + '-nicheSearch');
    var email = emailEl ? emailEl.value.trim() : '';
    var trade = getBusinessTypeValue(prefix);
    var errId = isExit ? 'jpdExitOptinError' : 'jpdOptinError';
    var btnId = isExit ? 'jpdExitOptinSubmit' : 'jpdOptinSubmit';
    var defaultBtn = isExit ? 'Send me my personalized demo →' : 'Personalize my demo →';

    showOptinError('', errId);
    if (!validEmail(email)) {
      showOptinError('Enter a valid work email.', errId);
      if (emailEl) {
        emailEl.classList.add('is-error');
        emailEl.focus();
      }
      return Promise.resolve(false);
    }
    if (!String(trade || '').trim()) {
      showOptinError('Select or enter your trade.', errId);
      if (nicheSearch) {
        nicheSearch.classList.add('is-error');
        nicheSearch.focus();
      }
      return Promise.resolve(false);
    }

    var btn = document.getElementById(btnId);
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Personalizing…';
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
      business_type: trade,
      demo_goals: [],
      referral_source: '',
      event: 'demo-opt-in',
      landing_page: location.href,
      utm_source: attr.utm_source || '',
      utm_medium: attr.utm_medium || '',
      utm_campaign: attr.utm_campaign || '',
      utm_content: attr.utm_content || (isExit ? 'exit_intent_optin' : ''),
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
        return res.json().then(function (json) {
          return { ok: res.ok && json && json.success !== false, json: json };
        }).catch(function () {
          return { ok: res.ok, json: null };
        });
      })
      .then(function (result) {
        if (!result.ok) {
          if (attempt < 2) {
            return submitOptIn(attempt + 1, source);
          }
          showOptinError(
            (result.json && result.json.message) ||
              'We couldn’t save your info right now. You can still continue the demo, or try again.',
            errId
          );
          if (btn) {
            btn.disabled = false;
            btn.textContent = defaultBtn;
          }
          // Allow continue without claiming CRM success / without Meta Lead.
          state.email = email;
          state.niche = trade;
          state.contactSaved = false;
          state.optedIn = true;
          try {
            sessionStorage.setItem(OPTIN_SESSION_KEY, '1');
          } catch (eFail) {}
          trackProof('proof_optin_submitted', { trade: trade, crm_saved: false, source: source });
          if (isExit) {
            trackProof('proof_exit_intent_submitted', { crm_saved: false });
            closeExit(true);
          }
          startFullDemo(false);
          return false;
        }

        state.email = email;
        state.niche = trade;
        state.contactSaved = true;
        state.optedIn = true;
        try {
          sessionStorage.setItem(OPTIN_SESSION_KEY, '1');
        } catch (e0) {}
        pushDemoOptInDataLayer(trade);
        trackProof('proof_optin_submitted', { trade: trade, crm_saved: true, source: source });
        if (isExit) {
          trackProof('proof_exit_intent_submitted', { crm_saved: true });
        }

        try {
          localStorage.setItem(
            'demoUser',
            JSON.stringify({
              email: email,
              niche: trade,
              firstName: deriveFirstName(email),
              businessName: '',
              goals: [],
            })
          );
        } catch (e) {}

        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultBtn;
        }
        if (isExit) closeExit(true);
        startFullDemo(true);
        return true;
      })
      .catch(function () {
        if (attempt < 2) return submitOptIn(attempt + 1, source);
        showOptinError('Network error. You can still continue the demo.', errId);
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultBtn;
        }
        state.email = email;
        state.niche = trade;
        state.contactSaved = false;
        state.optedIn = true;
        try {
          sessionStorage.setItem(OPTIN_SESSION_KEY, '1');
        } catch (eNet) {}
        trackProof('proof_optin_submitted', { trade: trade, crm_saved: false, source: source });
        if (isExit) {
          trackProof('proof_exit_intent_submitted', { crm_saved: false });
          closeExit(true);
        }
        startFullDemo(false);
        return false;
      });
  }

  function showOptinError(msg, errId) {
    var el = document.getElementById(errId || 'jpdOptinError');
    if (!el) return;
    if (!msg) {
      el.hidden = true;
      el.textContent = '';
      return;
    }
    el.hidden = false;
    el.textContent = msg;
  }

  function startFullDemo(crmOk) {
    var tradeLabel = getBusinessTypeLabel('jpd') || getBusinessTypeLabel('jpd-exit') || state.nicheLabel || state.niche;
    state.nicheLabel = tradeLabel;
    applyJobPersona(state.niche, tradeLabel);
    decorateTrialLinks();
    state.animating = true;

    var full = document.querySelector('[data-jpd-full]');
    var optin = document.querySelector('[data-jpd-optin]');
    if (optin) optin.hidden = true;
    if (full) {
      full.hidden = false;
      full.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Same semantic milestones as /demo/ run.
    postDemoViewed();
    postDemoEvent('demo_run_started');
    try {
      if (!sessionStorage.getItem('jcp_dl_alias_DemoStarted')) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ event: 'DemoStarted', source: 'job_proof_demo', business_type: state.niche || '' });
        sessionStorage.setItem('jcp_dl_alias_DemoStarted', '1');
      }
    } catch (e) {}

    runFullSequence();
  }

  function postDemoViewed() {
    var url = (window.JCP_DEMO_SURVEY && window.JCP_DEMO_SURVEY.rest_viewed_url) || '';
    if (!url || !state.email) return;
    var attr = attrPayload();
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        first_name: deriveFirstName(state.email),
        email: state.email,
        business_type: state.niche,
        company: '',
        landing_page: location.href,
        utm_source: attr.utm_source || '',
        utm_medium: attr.utm_medium || '',
        utm_campaign: attr.utm_campaign || '',
        utm_content: attr.utm_content || '',
        fbclid: attr.fbclid || '',
      }),
    }).catch(function () {});
  }

  function runFullSequence() {
    clearTimer();
    var status = document.getElementById('jpdFullStatus');
    var results = document.getElementById('jpdFullResults');
    var progress = document.getElementById('jpdFullProgress');
    if (results) results.hidden = true;
    if (progress) progress.hidden = false;

    var lines = [
      { t: 0, text: 'Creating your check-in…' },
      { t: 2200, text: 'Adding service + location context…' },
      { t: 4200, text: 'Publishing across connected channels…' },
      {
        t: 6500,
        text: '',
        fn: function () {
          if (progress) {
            progress.hidden = true;
            progress.style.display = 'none';
          }
          if (results) results.hidden = false;
          state.animating = false;
          state.demoCompleted = true;
          try {
            sessionStorage.setItem(DEMO_DONE_KEY, '1');
          } catch (eDone) {}
          trackProof('proof_outputs_viewed');
          trackProof('proof_demo_completed');
          postDemoEvent('demo_publish_completed');
          postDemoEvent('post_demo_modal_shown');
          try {
            if (!sessionStorage.getItem('jcp_dl_alias_DemoPublishViewed')) {
              window.dataLayer = window.dataLayer || [];
              window.dataLayer.push({ event: 'DemoPublishViewed', source: 'job_proof_demo' });
              sessionStorage.setItem('jcp_dl_alias_DemoPublishViewed', '1');
            }
          } catch (e) {}
          results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
      },
    ];

    lines.forEach(function (step) {
      window.setTimeout(function () {
        if (step.text && status) status.textContent = step.text;
        if (step.fn) step.fn();
      }, step.t);
    });
  }

  function hideChatWidgets() {
    var selectors = [
      '#chat-widget-container',
      '#lc_text-widget',
      '.lc_text-widget',
      '[id*="chat-widget"]',
      '[class*="chat-widget"]',
      'iframe[src*="leadconnector"]',
      'iframe[src*="msgsndr"]',
      'button[aria-label="Open chat"]',
      '.leadconnector-chat',
      '#leadconnector-chat',
      '.ghl-chat-widget',
    ];
    selectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        el.style.setProperty('display', 'none', 'important');
        el.style.setProperty('visibility', 'hidden', 'important');
        el.setAttribute('aria-hidden', 'true');
      });
    });
  }

  function onClick(e) {
    var t = e.target;
    if (!(t instanceof Element)) return;

    if (t.closest('[data-jpd-scroll-teaser]')) {
      e.preventDefault();
      var teaser = document.getElementById('jpd-teaser');
      if (teaser) teaser.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }

    if (t.closest('[data-jpd-start-teaser]')) {
      e.preventDefault();
      runTeaser();
      return;
    }

    var trial = t.closest('[data-jpd-trial]');
    if (trial) {
      var source = trial.getAttribute('data-jpd-source') || 'trial';
      trial.setAttribute('href', buildTrialUrl('job_proof_demo_' + source));
      state.trialClicked = true;
      trackProof('proof_trial_cta_clicked', { source: source });
      if (state.optedIn) {
        postDemoEvent('demo_converted', { cta: 'start_free_trial', source: source });
      }
      return;
    }

    if (t.closest('[data-jpd-expert]')) {
      trackProof('proof_expert_cta_clicked');
    }

    if (t.closest('[data-jpd-exit-dismiss]')) {
      closeExit(true);
      return;
    }

    var caseCta = t.closest('#jpdExitCaseCta');
    if (caseCta) {
      trackProof('proof_case_study_exit_clicked');
      try {
        sessionStorage.setItem(EXIT_DISMISS_KEY, '1');
      } catch (eCase) {}
    }
  }

  /* ---- Exit intent (desktop only, state-aware) ---- */
  function isDesktopExit() {
    return window.matchMedia && window.matchMedia('(min-width: 1024px)').matches;
  }

  function alreadyKey(key) {
    try {
      return Boolean(sessionStorage.getItem(key));
    } catch (e) {
      return false;
    }
  }

  function onceKey(key) {
    try {
      if (sessionStorage.getItem(key)) return false;
      sessionStorage.setItem(key, '1');
      return true;
    } catch (e) {
      return true;
    }
  }

  function caseStudyActive() {
    return document.body.getAttribute('data-jpd-case-active') === '1';
  }

  function meaningfulEngagement() {
    if (state.engaged || state.teaserDone) return true;
    return Date.now() - state.startedAt >= 20000;
  }

  function exitBlocked() {
    if (!isDesktopExit()) return true;
    if (alreadyKey(EXIT_DISMISS_KEY) || alreadyKey(EXIT_SHOWN_KEY)) return true;
    if (state.trialClicked) return true;
    if (state.animating) return true;
    if (!meaningfulEngagement()) return true;
    var active = document.activeElement;
    if (active && /^(INPUT|TEXTAREA|SELECT)$/i.test(active.tagName)) return true;
    // State B: opted in but demo not finished — let GHL recover, no modal.
    if (state.optedIn && !state.demoCompleted) return true;
    // State C only if case study still active.
    if (state.optedIn && state.demoCompleted && !caseStudyActive()) return true;
    return false;
  }

  function resolveExitMode() {
    if (!state.optedIn) return 'optin';
    if (state.optedIn && state.demoCompleted && !state.trialClicked && caseStudyActive()) return 'case';
    return '';
  }

  function closeExit(dismissed) {
    var root = document.getElementById('jpdExitRoot');
    if (!root) return;
    root.classList.remove('is-open');
    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('jcp-case-exit-open');
    if (dismissed) {
      try {
        sessionStorage.setItem(EXIT_DISMISS_KEY, '1');
      } catch (e) {}
    }
  }

  function openExit(source) {
    if (exitBlocked()) return false;
    var mode = resolveExitMode();
    if (!mode) return false;
    if (!onceKey(EXIT_SHOWN_KEY)) return false;

    var root = document.getElementById('jpdExitRoot');
    if (!root) return false;
    root.querySelectorAll('[data-jpd-exit-panel]').forEach(function (panel) {
      panel.hidden = panel.getAttribute('data-jpd-exit-panel') !== mode;
    });
    if (mode === 'case') {
      var caseUrl = document.body.getAttribute('data-jpd-case-url') || '/case-study/';
      var caseCta = document.getElementById('jpdExitCaseCta');
      if (caseCta) caseCta.setAttribute('href', caseUrl);
    }
    root.hidden = false;
    root.classList.add('is-open');
    root.setAttribute('aria-hidden', 'false');
    document.body.classList.add('jcp-case-exit-open');
    trackProof('proof_exit_intent_viewed', { mode: mode, source: source || 'mouseleave' });
    return true;
  }

  function setupExitIntent() {
    var armed = false;
    window.setTimeout(function () {
      armed = true;
    }, 8000);

    document.addEventListener('mouseout', function (e) {
      if (!armed || exitBlocked()) return;
      if (e.relatedTarget || e.toElement) return;
      if (typeof e.clientY === 'number' && e.clientY > 12) return;
      openExit('mouseleave');
    });

    document.addEventListener(
      'keydown',
      function (e) {
        if (e.key === 'Escape') closeExit(true);
      },
      true
    );

    var exitForm = document.getElementById('jpdExitOptinForm');
    if (exitForm) {
      exitForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitOptIn(1, 'exit');
      });
    }
  }

  /* ---- Resume opted-in visitors in same browser session ---- */
  function tryResumeSession() {
    var opted = false;
    try {
      opted = sessionStorage.getItem(OPTIN_SESSION_KEY) === '1' || sessionStorage.getItem(DEMO_DONE_KEY) === '1';
    } catch (e) {}

    var demoUser = null;
    try {
      demoUser = JSON.parse(localStorage.getItem('demoUser') || 'null');
    } catch (e2) {
      demoUser = null;
    }

    if (!opted && !(demoUser && demoUser.email && demoUser.niche)) return false;
    // Only resume when this session already opted in on this funnel (avoid cross-page demoUser hijack).
    try {
      if (sessionStorage.getItem(OPTIN_SESSION_KEY) !== '1' && sessionStorage.getItem(DEMO_DONE_KEY) !== '1') {
        return false;
      }
    } catch (e3) {
      return false;
    }

    state.optedIn = true;
    state.email = (demoUser && demoUser.email) || state.email;
    state.niche = (demoUser && demoUser.niche) || state.niche;
    state.engaged = true;
    state.teaserDone = true;

    var done = false;
    try {
      done = sessionStorage.getItem(DEMO_DONE_KEY) === '1';
    } catch (e4) {}

    if (done) {
      state.demoCompleted = true;
      applyJobPersona(state.niche, state.niche);
      var teaser = document.querySelector('[data-jpd-teaser]');
      var optin = document.querySelector('[data-jpd-optin]');
      var full = document.querySelector('[data-jpd-full]');
      var progress = document.getElementById('jpdFullProgress');
      var results = document.getElementById('jpdFullResults');
      if (teaser) {
        var actions = teaser.querySelector('[data-jpd-teaser-actions]');
        if (actions) actions.hidden = true;
      }
      if (optin) optin.hidden = true;
      if (full) full.hidden = false;
      if (progress) progress.hidden = true;
      if (results) results.hidden = false;
      window.setTimeout(function () {
        if (full) full.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 150);
      return true;
    }

    startFullDemo(true);
    return true;
  }

  function markMidPageEngagement() {
    var teaser = document.getElementById('jpd-teaser');
    if (!teaser || !('IntersectionObserver' in window)) return;
    try {
      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) state.engaged = true;
          });
        },
        { threshold: 0.25 }
      );
      io.observe(teaser);
    } catch (e) {}
  }

  function init() {
    trackProof('proof_lp_viewed');
    decorateTrialLinks();
    window.setTimeout(decorateTrialLinks, 300);
    setupCombobox('jpd');
    setupCombobox('jpd-exit');
    hideChatWidgets();
    [500, 2000, 5000].forEach(function (ms) {
      window.setTimeout(hideChatWidgets, ms);
    });
    if ('MutationObserver' in window) {
      try {
        new MutationObserver(hideChatWidgets).observe(document.documentElement, { childList: true, subtree: true });
      } catch (e) {}
    }

    document.querySelectorAll('img[data-fallback]').forEach(function (img) {
      img.addEventListener('error', function () {
        var fb = img.getAttribute('data-fallback');
        if (fb) {
          img.removeAttribute('data-fallback');
          img.src = fb;
        }
      });
    });

    var form = document.getElementById('jpdOptinForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitOptIn(1, 'inline');
      });
    }

    document.addEventListener('click', onClick, true);
    markMidPageEngagement();
    setupExitIntent();

    // Make brandbar CTA always visible on this page (trial path).
    var bar = document.querySelector('[data-jcp-landing-brandbar]');
    if (bar) bar.classList.add('is-compact');

    tryResumeSession();

    window.JCPJobProofDemo = {
      buildTrialUrl: buildTrialUrl,
      trackProof: trackProof,
      runTeaser: runTeaser,
      openExit: openExit,
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
