/**
 * Job Proof Demo funnel — product-led LP + /job-proof-demo/demo/ run.
 * GHL upsert + Meta Lead (demo_opt_in) only after successful CRM response.
 * Analytics: dataLayer only (no posthog.capture). One event per funnel stage.
 */
(function () {
  'use strict';

  var LP_VARIANT = 'job_proof_demo';
  var STORAGE_PREFIX = 'jcp_proof_evt_';
  var DEMO_SESSION_KEY = 'jcp_jpd_demo_session';
  var EXIT_SHOWN_KEY = 'jcp_jpd_exit_shown';
  var EXIT_DISMISS_KEY = 'jcp_jpd_exit_dismissed';
  var OPTIN_SESSION_KEY = 'jcp_jpd_opted_in';
  var DEMO_DONE_KEY = 'jcp_jpd_demo_done';
  var HERO_DONE_KEY = 'jcp_jpd_hero_done';

  var TRADE_JOBS = {
    plumbing: {
      label: 'Plumbing',
      title: 'Water heater replacement',
      city: 'Austin, TX',
      photo: 'campaign:jcp-campaign-job-proof-640.webp',
      fallback: 'campaign:jcp-campaign-job-proof.jpg',
      desc: 'Completed water heater replacement with geotagged job proof from the site.',
    },
    hvac: {
      label: 'HVAC',
      title: 'AC system replacement',
      city: 'Austin, TX',
      photo: 'campaign:jcp-campaign-hvac-capture-640.webp',
      fallback: 'campaign:jcp-campaign-hvac-capture.jpg',
      desc: 'New outdoor unit set, lineset connected, and system commissioned for cooling.',
    },
    electrical: {
      label: 'Electrical',
      title: 'Electrical panel upgrade',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'New breaker layout, labeled circuits, and safety check completed.',
    },
    roofing: {
      label: 'Roofing',
      title: 'Roof replacement',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e8?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Tear-off complete, new underlayment and shingles installed, flashing sealed.',
    },
    remodeling: {
      label: 'Remodeling',
      title: 'Kitchen remodel',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Kitchen remodel finished with clean installs and job-site proof.',
    },
    painting: {
      label: 'Painting',
      title: 'Exterior home painting',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1562259929-b4e1fd3aef09?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Exterior paint job completed with crisp lines and a clean site.',
    },
    landscaping: {
      label: 'Landscaping',
      title: 'Landscape installation',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1558904541-efa843a96f01?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Landscape installation completed and documented on site.',
    },
    outdoor: {
      label: 'Outdoor',
      title: 'Landscape installation',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1558904541-efa843a96f01?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Outdoor project completed with clear job-site proof.',
    },
    'garage-door': {
      label: 'Garage door',
      title: 'Garage door replacement',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Garage door replacement installed and tested.',
    },
    'pest-control': {
      label: 'Pest control',
      title: 'Treatment / service job',
      city: 'Austin, TX',
      photo: 'campaign:jcp-campaign-crew-review-640.webp',
      fallback: 'campaign:jcp-campaign-crew-review.jpg',
      desc: 'Service completed and documented for the property.',
    },
    'tree-service': {
      label: 'Tree service',
      title: 'Tree removal',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Tree work completed with a clean property and job proof.',
    },
    cleaning: {
      label: 'Cleaning',
      title: 'Driveway cleaning',
      city: 'Austin, TX',
      photo: 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&h=600&fit=crop&q=75',
      fallback: '',
      desc: 'Cleaning service finished with clear before/after proof.',
    },
    default: {
      label: 'Home service',
      title: 'Completed service job',
      city: 'Austin, TX',
      photo: 'campaign:jcp-campaign-job-proof-640.webp',
      fallback: 'campaign:jcp-campaign-job-proof.jpg',
      desc: 'Completed service job with geotagged proof from the site.',
    },
  };

  function campaignAsset(name) {
    var base =
      (window.JCP_JPD && window.JCP_JPD.campaignBase) ||
      (document.body && document.body.getAttribute('data-jpd-campaign-base')) ||
      '';
    if (!base) {
      try {
        base = location.origin + '/wp-content/themes/jobcapturepro-core/assets/campaign/';
      } catch (e) {
        base = '/wp-content/themes/jobcapturepro-core/assets/campaign/';
      }
    }
    return String(base).replace(/\/?$/, '/') + String(name || '').replace(/^campaign:/, '');
  }

  function resolvePhoto(ref) {
    var s = String(ref || '');
    if (!s) return '';
    if (s.indexOf('campaign:') === 0) return campaignAsset(s.slice(9));
    return s;
  }

  var state = {
    heroDone: false,
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

  var isRunPage = !!(document.body && document.body.classList.contains('jcp-job-proof-demo-run'));

  function attrPayload() {
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        return window.JCPLeadAttribution.getPayload() || {};
      }
    } catch (e) {}
    return {};
  }

  function deviceType() {
    try {
      if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches) return 'mobile';
      if (window.matchMedia && window.matchMedia('(max-width: 1023px)').matches) return 'tablet';
    } catch (e) {}
    return 'desktop';
  }

  /** Fire once per session per event name (unless extra.force). */
  function track(eventName, extra) {
    var force = !!(extra && extra.force);
    if (extra && Object.prototype.hasOwnProperty.call(extra, 'force')) {
      try {
        delete extra.force;
      } catch (eForce) {}
    }
    try {
      var key = STORAGE_PREFIX + eventName;
      if (!force && sessionStorage.getItem(key)) return;
      sessionStorage.setItem(key, '1');
    } catch (e) {}

    var attr = attrPayload();
    var page = isRunPage ? 'job_proof_demo_run' : 'job_proof_demo';
    var payload = {
      event: eventName,
      lp_variant: attr.lp_variant || LP_VARIANT,
      page: page,
      page_path: location.pathname,
      device: deviceType(),
      referrer: attr.referrer || document.referrer || '',
      session_id: getDemoSessionId(),
      opted_in: !!(state.optedIn || hasOptInSessionSafe()),
      demo_completed: !!state.demoCompleted,
    };
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'].forEach(function (k) {
      if (attr[k]) payload[k] = attr[k];
    });
    if (state.niche) payload.trade = state.niche;
    if (state.nicheLabel) payload.trade_label = state.nicheLabel;
    if (extra && typeof extra === 'object') {
      Object.keys(extra).forEach(function (k) {
        payload[k] = extra[k];
      });
    }
    if (!payload.source) payload.source = page;
    delete payload.email;
    delete payload.phone;
    delete payload.first_name;
    delete payload.last_name;
    delete payload.name;
    delete payload.contact_id;

    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(payload);
      if (window.JCP_JPD_DEBUG || /[?&]jpd_debug=1/.test(location.search)) {
        // eslint-disable-next-line no-console
        console.info('[JPD]', eventName, payload);
      }
    } catch (err) {}
  }

  function hasOptInSessionSafe() {
    try {
      return sessionStorage.getItem(OPTIN_SESSION_KEY) === '1';
    } catch (e) {
      return false;
    }
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
      'jpd_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 10);
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
      sessionStorage.setItem('jcp_datalayer_demo_opt_in', '1');
    } catch (err) {}
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
    if (state.email && !u.searchParams.get('email')) u.searchParams.set('email', state.email);
    if (state.niche && !u.searchParams.get('industryId') && !u.searchParams.get('niche')) {
      u.searchParams.set('niche', state.niche);
    }
    var first = deriveFirstName(state.email);
    if (first && !u.searchParams.get('first_name')) u.searchParams.set('first_name', first);
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
      'pressure-washing': 'cleaning',
      'power-washing': 'cleaning',
      'garage-doors': 'garage-door',
      'general-contracting': 'remodeling',
    };
    if (TRADE_JOBS[slug]) return slug;
    if (aliases[slug]) return aliases[slug];
    if (slug.indexOf('plumb') !== -1) return 'plumbing';
    if (slug.indexOf('hvac') !== -1 || slug.indexOf('heat') !== -1) return 'hvac';
    if (slug.indexOf('electr') !== -1) return 'electrical';
    if (slug.indexOf('roof') !== -1) return 'roofing';
    if (slug.indexOf('paint') !== -1) return 'painting';
    if (slug.indexOf('land') !== -1 || slug.indexOf('lawn') !== -1) return 'outdoor';
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
    var tradeWord = job.label || 'Home service';
    state.niche = key;
    state.nicheLabel = tradeWord;

    var photo = resolvePhoto(job.photo);
    var fallback = resolvePhoto(job.fallback);

    document.querySelectorAll('[data-jpd-job-title]').forEach(function (el) {
      el.textContent = job.title;
    });
    document.querySelectorAll('[data-jpd-job-city]').forEach(function (el) {
      el.textContent = job.city;
    });
    document.querySelectorAll('[data-jpd-job-photo]').forEach(function (el) {
      if (!photo) return;
      el.setAttribute('src', photo);
      if (fallback) el.setAttribute('data-fallback', fallback);
      else el.removeAttribute('data-fallback');
    });
    document.querySelectorAll('[data-jpd-trade-label]').forEach(function (el) {
      el.textContent = tradeWord;
    });
    document.querySelectorAll('[data-jpd-job-desc]').forEach(function (el) {
      el.textContent = job.desc || '';
    });

    var heading = document.querySelector('[data-jpd-full-heading]');
    if (heading) {
      heading.textContent = 'Here’s what one ' + tradeWord + ' job can become.';
    }
    var gbp = document.querySelector('[data-jpd-gbp-headline]');
    if (gbp) {
      gbp.textContent = 'Just finished another ' + job.title.toLowerCase() + ' in Austin';
    }
    var social = document.querySelector('[data-jpd-social-copy]');
    if (social) {
      social.textContent =
        'Another job wrapped. ' + job.title + ' done right — proof from the field.';
    }
    var dir = document.querySelector('[data-jpd-directory-latest]');
    if (dir) dir.textContent = job.title;
  }

  /* ---- Trade combobox ---- */
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
    var matches = nicheOptions
      .filter(function (opt) {
        if (!q) return true;
        return String(opt.label || '')
          .toLowerCase()
          .indexOf(q) !== -1;
      })
      .slice(0, 12);
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

  function clearTimer() {
    if (state.timer) {
      window.clearTimeout(state.timer);
      state.timer = 0;
    }
  }

  function demoRunUrl() {
    var fromBody = document.body.getAttribute('data-jpd-demo-run-url');
    var fromCfg = window.JCP_DEMO_SURVEY && window.JCP_DEMO_SURVEY.demo_run_url;
    var raw = fromBody || fromCfg || '/job-proof-demo/demo/';
    try {
      var u = new URL(raw, location.origin);
      // Keep same host as the LP so sessionStorage survives the handoff
      // (Local/WP home_url can be localhost while the visitor uses *.local).
      u.protocol = location.protocol;
      u.host = location.host;
      return u.pathname.replace(/\/?$/, '/') + u.search + u.hash;
    } catch (e) {
      return '/job-proof-demo/demo/';
    }
  }

  /* ---- Hero canvas transformation (LP) ---- */
  function runHeroCanvas() {
    if (state.animating || state.heroDone) return;
    clearTimer();
    state.engaged = true;
    state.animating = true;
    track('DemoCTA', { section: 'hero', source: 'hero_sample_job', cta_source: 'hero_sample_job' });

    var canvas = document.querySelector('[data-jpd-canvas]');
    var idle = document.querySelector('[data-jpd-canvas-idle]');
    var run = document.querySelector('[data-jpd-canvas-run]');
    var status = document.getElementById('jpdCanvasStatus');
    var destinations = document.querySelector('[data-jpd-destinations]');
    var payoff = document.querySelector('[data-jpd-payoff]');
    var stage = canvas && (canvas.querySelector('.jpd-stage') || canvas.querySelector('.jpd-canvas'));
    var aiPanel = document.querySelector('[data-jpd-ai-panel]');

    if (idle) idle.hidden = true;
    if (run) run.hidden = false;
    if (stage) stage.setAttribute('data-jpd-canvas-stage', 'running');
    if (aiPanel) aiPanel.classList.add('is-live');
    if (payoff) payoff.hidden = true;
    if (destinations) {
      destinations.querySelectorAll('[data-dest]').forEach(function (el) {
        el.classList.remove('is-lit');
      });
    }
    document.querySelectorAll('.jpd-canvas__step').forEach(function (el) {
      el.classList.remove('is-active', 'is-done');
    });

    var steps = [
      {
        t: 0,
        fn: function () {
          setCanvasStep('photo', 'Job photo received…');
        },
      },
      {
        t: 1400,
        fn: function () {
          setCanvasStep('context', 'Service + location context attached…');
        },
      },
      {
        t: 2800,
        fn: function () {
          setCanvasStep('ai', 'AI job description created…');
        },
      },
      {
        t: 4200,
        fn: function () {
          setCanvasStep('publish', 'Publishing destinations lighting up…');
          lightDestinations();
        },
      },
    ];

    function setCanvasStep(name, text) {
      if (status) status.textContent = text;
      document.querySelectorAll('.jpd-canvas__step').forEach(function (el) {
        var key = el.getAttribute('data-step');
        el.classList.remove('is-active');
        if (key === name) el.classList.add('is-active');
        var order = ['photo', 'context', 'ai', 'publish'];
        if (order.indexOf(key) < order.indexOf(name)) el.classList.add('is-done');
      });
    }

    function lightDestinations() {
      if (!destinations) {
        finishHero();
        return;
      }
      if (stage) stage.classList.add('is-publishing');
      var items = destinations.querySelectorAll('[data-dest]');
      var i = 0;
      function next() {
        if (i < items.length) {
          items[i].classList.add('is-lit');
          i += 1;
          state.timer = window.setTimeout(next, 450);
          return;
        }
        finishHero();
      }
      next();
    }

    function finishHero() {
      state.heroDone = true;
      state.animating = false;
      if (stage) stage.setAttribute('data-jpd-canvas-stage', 'done');
      if (payoff) payoff.hidden = false;
      if (status) status.textContent = 'One job → JCP → five public marketing outcomes';
      try {
        sessionStorage.setItem(HERO_DONE_KEY, '1');
      } catch (e) {}
      track('HeroTransformCompleted', { section: 'hero', source: 'hero_canvas', cta_source: 'hero_canvas' });
    }

    steps.forEach(function (s) {
      window.setTimeout(s.fn, s.t);
    });
  }

  function scrollToOptin() {
    var optin = document.querySelector('[data-jpd-optin]') || document.getElementById('jpd-optin');
    if (!optin) return;
    pushDemoFormViewed();
    window.setTimeout(function () {
      optin.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 80);
  }

  function pushDemoFormViewed() {
    track('DemoFormViewed', { section: 'optin', source: 'optin', cta_source: 'optin' });
  }

  /* ---- Opt-in / GHL ---- */
  function validEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim());
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

  function persistOptIn(email, trade, label) {
    state.email = email;
    state.niche = trade;
    state.nicheLabel = label || trade;
    state.optedIn = true;
    try {
      sessionStorage.setItem(OPTIN_SESSION_KEY, '1');
      sessionStorage.setItem(
        'jcp_jpd_demo_state',
        JSON.stringify({
          niche: trade,
          nicheLabel: state.nicheLabel,
          optedIn: true,
          at: Date.now(),
        })
      );
    } catch (e) {}
    try {
      localStorage.setItem(
        'demoUser',
        JSON.stringify({
          email: email,
          niche: trade,
          firstName: deriveFirstName(email),
          businessName: '',
          goals: [],
          nicheLabel: state.nicheLabel,
          source: 'job_proof_demo',
        })
      );
    } catch (e2) {}
  }

  function goToPersonalizedDemo() {
    // Persist synchronously before navigation — one gate only.
    try {
      sessionStorage.setItem(OPTIN_SESSION_KEY, '1');
    } catch (e) {}
    var url = demoRunUrl();
    try {
      var u = new URL(url, location.origin);
      if (state.niche) u.searchParams.set('niche', state.niche);
      // Carry non-PII attribution into the child route.
      var attr = attrPayload();
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'lp_variant'].forEach(function (k) {
        if (attr[k] && !u.searchParams.get(k)) u.searchParams.set(k, String(attr[k]));
      });
      if (!u.searchParams.get('lp_variant')) u.searchParams.set('lp_variant', LP_VARIANT);
      url = u.toString();
    } catch (err) {}
    window.location.replace(url);
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
    var tradeLabel = getBusinessTypeLabel(prefix);
    var errId = isExit ? 'jpdExitOptinError' : 'jpdOptinError';
    var btnId = isExit ? 'jpdExitOptinSubmit' : 'jpdOptinSubmit';
    var defaultBtn = isExit ? 'Send me my demo →' : 'Show me my demo →';

    showOptinError('', errId);
    if (!validEmail(email)) {
      showOptinError('Enter a valid work email.', errId);
      track('DemoFormFailed', { section: 'optin', source: source, reason: 'invalid_email', cta_source: source });
      if (emailEl) {
        emailEl.classList.add('is-error');
        emailEl.focus();
      }
      return Promise.resolve(false);
    }
    if (!String(trade || '').trim()) {
      showOptinError('Select or enter your trade.', errId);
      track('DemoFormFailed', { section: 'optin', source: source, reason: 'missing_trade', cta_source: source });
      if (nicheSearch) {
        nicheSearch.classList.add('is-error');
        nicheSearch.focus();
      }
      return Promise.resolve(false);
    }

    track('DemoFormAttempted', { section: 'optin', source: source, trade: trade, cta_source: source });

    var btn = document.getElementById(btnId);
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
        return res
          .json()
          .then(function (json) {
            return { ok: res.ok && json && json.success !== false, json: json };
          })
          .catch(function () {
            return { ok: res.ok, json: null };
          });
      })
      .then(function (result) {
        if (!result.ok) {
          if (attempt < 2) return submitOptIn(attempt + 1, source);
          showOptinError(
            (result.json && result.json.message) ||
              'We couldn’t save your info right now. Please try again in a moment.',
            errId
          );
          if (btn) {
            btn.disabled = false;
            btn.textContent = defaultBtn;
          }
          track('DemoFormFailed', { section: 'optin', source: source, trade: trade, crm_saved: false, cta_source: source });
          // Soft-continue still lands on personalized demo; CRM may retry server-side.
          return false;
        }

        persistOptIn(email, trade, tradeLabel);
        state.contactSaved = true;
        pushDemoOptInDataLayer(trade);
        track('DemoFormSubmitted', { section: 'optin', source: source, trade: trade, crm_saved: true, cta_source: source });
        if (isExit) {
          track('ExitIntentSubmitted', { section: 'exit', source: 'exit', trade: trade, cta_source: 'exit' });
        }
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultBtn;
        }
        if (isExit) closeExit(true);
        goToPersonalizedDemo();
        return true;
      })
      .catch(function () {
        if (attempt < 2) return submitOptIn(attempt + 1, source);
        showOptinError('Network error. Please try again.', errId);
        track('DemoFormFailed', { section: 'optin', source: source, reason: 'network', cta_source: source });
        if (btn) {
          btn.disabled = false;
          btn.textContent = defaultBtn;
        }
        return false;
      });
  }

  /* ---- Personalized demo run ---- */
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

  function runPersonalizedSequence() {
    clearTimer();
    state.animating = true;
    track('PersonalizedDemoStarted', { section: 'demo_run', source: 'demo_run', cta_source: 'demo_run' });
    postDemoEvent('demo_run_started');

    var status = document.getElementById('jpdFullStatus');
    var results = document.getElementById('jpdFullResults');
    var progress = document.getElementById('jpdFullProgress');
    var stage = document.querySelector('[data-jpd-run-stage]');
    if (results) results.hidden = true;
    if (progress) progress.hidden = false;

    var stepKeys = ['photo', 'checkin', 'context', 'publish'];
    function markRunStep(name) {
      document.querySelectorAll('[data-run-step]').forEach(function (el) {
        var key = el.getAttribute('data-run-step');
        el.classList.toggle('is-active', key === name);
        if (stepKeys.indexOf(key) < stepKeys.indexOf(name)) el.classList.add('is-done');
      });
    }

    var lines = [
      { t: 0, text: 'Completed job received…', step: 'photo' },
      { t: 900, text: 'Creating your check-in…', step: 'checkin' },
      { t: 2200, text: 'Adding service + location context…', step: 'context' },
      { t: 3600, text: 'Publishing across connected channels…', step: 'publish' },
      {
        t: 5200,
        text: '',
        fn: function () {
          if (progress) {
            progress.hidden = true;
            progress.style.display = 'none';
          }
          if (stage) stage.classList.add('is-complete');
          if (results) results.hidden = false;
          state.animating = false;
          state.demoCompleted = true;
          try {
            sessionStorage.setItem(DEMO_DONE_KEY, '1');
          } catch (eDone) {}
          track('DemoResultsViewed', { section: 'results', source: 'demo_run', cta_source: 'demo_run' });
          postDemoEvent('demo_publish_completed');
          if (results) results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
      },
    ];

    lines.forEach(function (step) {
      window.setTimeout(function () {
        if (step.text && status) status.textContent = step.text;
        if (step.step) markRunStep(step.step);
        if (step.fn) step.fn();
      }, step.t);
    });
  }

  function loadDemoUser() {
    try {
      return JSON.parse(localStorage.getItem('demoUser') || 'null');
    } catch (e) {
      return null;
    }
  }

  function loadDemoState() {
    try {
      return JSON.parse(sessionStorage.getItem('jcp_jpd_demo_state') || 'null');
    } catch (e) {
      return null;
    }
  }

  function hasOptInSession() {
    try {
      if (sessionStorage.getItem(OPTIN_SESSION_KEY) === '1') return true;
    } catch (e) {}
    var demoState = loadDemoState();
    if (demoState && demoState.optedIn && demoState.niche) return true;
    var user = loadDemoUser();
    return !!(user && user.email && user.niche && user.source === 'job_proof_demo');
  }

  function redirectToLpOptIn() {
    var raw = document.body.getAttribute('data-jpd-lp-url') || '/job-proof-demo/';
    try {
      var u = new URL(raw, location.origin);
      u.protocol = location.protocol;
      u.host = location.host;
      u.hash = 'jpd-optin';
      var cur = new URLSearchParams(location.search);
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'lp_variant'].forEach(function (k) {
        var v = cur.get(k);
        if (v && !u.searchParams.get(k)) u.searchParams.set(k, v);
      });
      if (!u.searchParams.get('lp_variant')) u.searchParams.set('lp_variant', LP_VARIANT);
      window.location.replace(u.pathname.replace(/\/?$/, '/') + u.search + u.hash);
    } catch (e) {
      window.location.replace('/job-proof-demo/#jpd-optin');
    }
  }

  function startRunOrGate() {
    var gate = document.querySelector('[data-jpd-run-gate]');
    var hero = document.querySelector('.jpd-run-hero');
    var results = document.getElementById('jpdFullResults');

    if (!hasOptInSession()) {
      if (hero) hero.hidden = true;
      if (results) results.hidden = true;
      if (gate) gate.hidden = true;
      redirectToLpOptIn();
      return;
    }

    if (gate) gate.hidden = true;
    var user = loadDemoUser() || {};
    var demoState = loadDemoState() || {};
    state.optedIn = true;
    state.email = user.email || state.email;
    state.niche =
      user.niche ||
      demoState.niche ||
      state.niche ||
      (new URLSearchParams(location.search).get('niche') || '');
    state.nicheLabel = user.nicheLabel || demoState.nicheLabel || state.niche;
    applyJobPersona(state.niche, state.nicheLabel);
    decorateTrialLinks();

    track('PersonalizedDemoViewed', { section: 'demo_run', source: 'demo_run', cta_source: 'demo_run' });
    postDemoViewed();

    var done = false;
    try {
      done = sessionStorage.getItem(DEMO_DONE_KEY) === '1';
    } catch (e) {}

    if (done) {
      state.demoCompleted = true;
      var progress = document.getElementById('jpdFullProgress');
      if (progress) progress.hidden = true;
      if (results) results.hidden = false;
      return;
    }

    runPersonalizedSequence();
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

    var tracked = t.closest('[data-jpd-track]');
    if (tracked) {
      var evt = tracked.getAttribute('data-jpd-track') || '';
      if (evt) {
        track(evt, {
          section: tracked.getAttribute('data-jpd-section') || '',
          source: tracked.getAttribute('data-jpd-source') || tracked.getAttribute('data-jpd-section') || evt,
        });
      }
    }

    if (t.closest('[data-jpd-scroll-canvas]')) {
      e.preventDefault();
      var canvas = document.getElementById('jpd-canvas');
      if (canvas) canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    if (t.closest('[data-jpd-use-sample]')) {
      e.preventDefault();
      runHeroCanvas();
      return;
    }

    if (t.closest('[data-jpd-scroll-optin]')) {
      e.preventDefault();
      scrollToOptin();
      return;
    }

    var trial = t.closest('[data-jpd-trial]');
    if (trial) {
      var source = trial.getAttribute('data-jpd-source') || 'trial';
      trial.setAttribute('href', buildTrialUrl('job_proof_demo_' + source));
      state.trialClicked = true;
      track('TrialCTAClicked', { section: 'trial', source: source, cta_source: source });
      if (state.optedIn) {
        postDemoEvent('demo_converted', { cta: 'start_free_trial', source: source });
      }
      return;
    }

    if (t.closest('[data-jpd-expert]')) {
      track('ExpertCTAClicked', { section: 'expert', source: 'expert', cta_source: 'expert' });
    }

    if (t.closest('[data-jpd-exit-dismiss]')) {
      track('ExitIntentDismissed', { section: 'exit', source: 'dismiss', cta_source: 'dismiss' });
      closeExit(true);
      return;
    }

    var caseCta = t.closest('#jpdExitCaseCta');
    if (caseCta) {
      track('CaseStudyExitClicked', { section: 'exit', source: 'exit_case', cta_source: 'exit_case' });
      try {
        sessionStorage.setItem(EXIT_DISMISS_KEY, '1');
      } catch (eCase) {}
    }
  }

  /* ---- Exit intent ---- */
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
    if (state.engaged || state.heroDone || state.demoCompleted) return true;
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
    if (isRunPage) {
      if (!state.demoCompleted) return true;
      if (state.trialClicked) return true;
      return false;
    }
    // LP: after opt-in, no exit lead popup.
    if (state.optedIn || hasOptInSession()) return true;
    return false;
  }

  function resolveExitMode() {
    if (isRunPage) {
      if (state.demoCompleted && !state.trialClicked) return 'case';
      return '';
    }
    if (state.optedIn || hasOptInSession()) return '';
    return 'optin';
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
    track('ExitIntentViewed', { mode: mode, section: 'exit', source: source || 'mouseleave', cta_source: source || 'mouseleave' });
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

  function observeOptin() {
    var optin = document.getElementById('jpd-optin');
    if (!optin || !('IntersectionObserver' in window)) return;
    try {
      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              state.engaged = true;
              pushDemoFormViewed();
              io.disconnect();
            }
          });
        },
        { threshold: 0.35 }
      );
      io.observe(optin);
    } catch (e) {}
  }

  function observeTrackedViews() {
    if (!('IntersectionObserver' in window)) return;
    var nodes = document.querySelectorAll('[data-jpd-track-view]');
    if (!nodes.length) return;
    try {
      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            var evt = el.getAttribute('data-jpd-track-view');
            if (!evt) return;
            track(evt, {
              section: el.getAttribute('data-jpd-section') || el.id || evt,
              source: 'viewport',
            });
            io.unobserve(el);
          });
        },
        { threshold: 0.4 }
      );
      nodes.forEach(function (n) {
        io.observe(n);
      });
    } catch (e) {}
  }

  function setupFormStarted() {
    var fired = false;
    function markStarted() {
      if (fired) return;
      fired = true;
      track('DemoFormStarted', { section: 'optin', source: 'optin', cta_source: 'optin' });
    }
    ['jpd-email', 'jpd-nicheSearch', 'jpd-exit-email', 'jpd-exit-nicheSearch'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('focus', markStarted, { once: true });
      el.addEventListener('input', markStarted, { once: true });
    });
  }

  function initShared() {
    decorateTrialLinks();
    window.setTimeout(decorateTrialLinks, 300);
    hideChatWidgets();
    [500, 2000, 5000].forEach(function (ms) {
      window.setTimeout(hideChatWidgets, ms);
    });
    if ('MutationObserver' in window) {
      try {
        new MutationObserver(hideChatWidgets).observe(document.documentElement, {
          childList: true,
          subtree: true,
        });
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
    document.addEventListener('click', onClick, true);
    setupExitIntent();
    var bar = document.querySelector('[data-jcp-landing-brandbar]');
    if (bar) bar.classList.add('is-compact');
  }

  function initLp() {
    track('PaidLandingView', { section: 'lp', source: 'lp', cta_source: 'lp' });
    setupCombobox('jpd');
    setupCombobox('jpd-exit');
    observeOptin();
    observeTrackedViews();
    setupFormStarted();

    var form = document.getElementById('jpdOptinForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitOptIn(1, 'inline');
      });
    }

    try {
      if (sessionStorage.getItem(HERO_DONE_KEY) === '1') {
        state.heroDone = true;
      }
    } catch (e) {}
  }

  function initRun() {
    observeTrackedViews();
    startRunOrGate();
  }

  function init() {
    initShared();
    if (isRunPage) initRun();
    else initLp();

    window.JCPJobProofDemo = {
      buildTrialUrl: buildTrialUrl,
      track: track,
      runHeroCanvas: runHeroCanvas,
      openExit: openExit,
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
