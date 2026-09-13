/**
 * Job Proof Demo — ungated 3-moment product proof + trial handoff.
 * Analytics: single dataLayer emission path only (no posthog.capture).
 */
(function () {
  'use strict';

  var LP_VARIANT = 'job_proof_demo';
  var STORAGE_PREFIX = 'jcp_proof_evt_';

  function attrPayload() {
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        return window.JCPLeadAttribution.getPayload() || {};
      }
    } catch (e) {}
    return {};
  }

  /**
   * Canonical analytics emission for this funnel.
   * dataLayer only — GTM owns PostHog/Meta forwarding. Do not also call posthog.capture.
   */
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
    // Explicitly never attach PII keys even if present elsewhere.
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

  var MARKETING_DEFAULTS = {
    utm_source: 'jobcapturepro.com',
    utm_medium: 'website',
    utm_campaign: 'onboarding',
  };

  function isMarketingDefault(key, value) {
    var def = MARKETING_DEFAULTS[key];
    if (!def) return false;
    return String(value || '') === def;
  }

  /**
   * Build trial URL: paid acquisition UTMs overwrite marketing defaults.
   * Surface attribution stored separately as jcp_surface.
   */
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
    var paidKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'lp_variant'];

    // Fill missing with website defaults first.
    Object.keys(MARKETING_DEFAULTS).forEach(function (k) {
      if (!u.searchParams.get(k)) u.searchParams.set(k, MARKETING_DEFAULTS[k]);
    });

    // Paid acquisition wins over marketing defaults.
    paidKeys.forEach(function (k) {
      var val = attr[k];
      if (!val) return;
      var current = u.searchParams.get(k) || '';
      if (!current || isMarketingDefault(k, current) || k === 'utm_content' || k === 'fbclid' || k === 'lp_variant' || k === 'utm_term') {
        u.searchParams.set(k, String(val));
      }
    });

    // Always stamp lp_variant for this LP if missing.
    if (!u.searchParams.get('lp_variant')) {
      u.searchParams.set('lp_variant', attr.lp_variant || LP_VARIANT);
    }

    if (surface) {
      u.searchParams.set('jcp_surface', String(surface));
    }

    // If paid utm_content was applied, keep surface separate (already jcp_surface).
    // If no paid utm_content, allow surface as utm_content fallback only when still default-ish.
    if (surface && !attr.utm_content) {
      var content = u.searchParams.get('utm_content') || '';
      if (!content || content.indexOf('job_proof_demo') === 0 || content === 'onboarding') {
        u.searchParams.set('utm_content', String(surface));
      }
    }

    return u.toString();
  }

  function decorateTrialLinks() {
    document.querySelectorAll('[data-jpd-trial]').forEach(function (a) {
      var source = a.getAttribute('data-jpd-source') || 'trial';
      a.setAttribute('href', buildTrialUrl('job_proof_demo_' + source));
    });
  }

  function decorateExpertLinks() {
    var attr = attrPayload();
    document.querySelectorAll('[data-jpd-expert]').forEach(function (a) {
      try {
        var u = new URL(a.href, location.origin);
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'lp_variant'].forEach(function (k) {
          if (attr[k] && !u.searchParams.get(k)) u.searchParams.set(k, attr[k]);
        });
        if (!u.searchParams.get('lp_variant')) u.searchParams.set('lp_variant', LP_VARIANT);
        if (!u.searchParams.get('utm_content')) u.searchParams.set('utm_content', 'job_proof_demo_expert');
        a.href = u.pathname + u.search + u.hash;
      } catch (e) {}
    });
  }

  var state = { moment: 1, started: false, checkinReady: false };

  function setProgress(n) {
    var el = document.getElementById('jpdProofProgress');
    if (el) el.textContent = 'Moment ' + n + ' of 3';
  }

  function showMoment(n) {
    state.moment = n;
    document.querySelectorAll('[data-jpd-moment]').forEach(function (el) {
      var id = Number(el.getAttribute('data-jpd-moment'));
      var on = id === n;
      el.hidden = !on;
      el.classList.toggle('is-active', on);
    });
    setProgress(n);

    try {
      history.replaceState({ jpdMoment: n }, '', n === 1 ? '#proof' : '#proof-m' + n);
    } catch (e) {}

    var proof = document.getElementById('proof');
    if (proof) {
      proof.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    if (n === 1) {
      trackProof('proof_job_viewed');
    }
    if (n === 2) {
      runCheckinSequence();
    }
    if (n === 3) {
      trackProof('proof_outputs_viewed');
      trackProof('proof_demo_completed');
    }
  }

  function startProof(source) {
    if (!state.started) {
      state.started = true;
      trackProof('proof_demo_started', { source: source || 'cta' });
    }
    showMoment(1);
  }

  function runCheckinSequence() {
    var status = document.getElementById('jpdCheckinStatus');
    var card = document.getElementById('jpdCheckinCard');
    var next = document.getElementById('jpdRevealOutputs');
    if (next) next.hidden = true;
    if (card) card.classList.remove('is-ready');

    var lines = [
      'Creating job proof…',
      'Adding job context…',
      'Preparing connected channels…',
    ];
    var i = 0;
    if (status) status.textContent = lines[0];

    function tick() {
      i += 1;
      if (i < lines.length) {
        if (status) status.textContent = lines[i];
        window.setTimeout(tick, 420);
        return;
      }
      if (status) status.textContent = 'Check-in ready';
      if (card) card.classList.add('is-ready');
      state.checkinReady = true;
      trackProof('proof_checkin_created');
      // Auto-advance to final moment — no extra click required for tracking.
      window.setTimeout(function () {
        showMoment(3);
      }, 500);
    }
    window.setTimeout(tick, 420);
  }

  function onClick(e) {
    var t = e.target;
    if (!(t instanceof Element)) return;

    var start = t.closest('[data-jpd-start]');
    if (start) {
      e.preventDefault();
      startProof(start.textContent.trim().slice(0, 40));
      return;
    }

    var next = t.closest('[data-jpd-next]');
    if (next) {
      e.preventDefault();
      var n = Number(next.getAttribute('data-jpd-next') || '0');
      if (n === 2 || n === 3) showMoment(n);
      return;
    }

    var trial = t.closest('[data-jpd-trial]');
    if (trial) {
      var source = trial.getAttribute('data-jpd-source') || 'trial';
      trial.setAttribute('href', buildTrialUrl('job_proof_demo_' + source));
      trackProof('proof_trial_cta_clicked', { source: source });
      // Allow navigation; do not preventDefault.
      return;
    }

    var expert = t.closest('[data-jpd-expert]');
    if (expert) {
      trackProof('proof_expert_cta_clicked');
    }
  }

  function onPopState() {
    var n = 1;
    try {
      if (history.state && history.state.jpdMoment) n = Number(history.state.jpdMoment) || 1;
      else if (/#proof-m3/.test(location.hash)) n = 3;
      else if (/#proof-m2/.test(location.hash)) n = 2;
    } catch (e) {}
    if (n > 1 && !state.started) {
      state.started = true;
    }
    showMoment(n);
  }

  function init() {
    trackProof('proof_lp_viewed');
    decorateTrialLinks();
    decorateExpertLinks();
    // Re-decorate after attribution capture settles.
    window.setTimeout(decorateTrialLinks, 200);
    window.setTimeout(decorateExpertLinks, 200);

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
    window.addEventListener('popstate', onPopState);

    if (/#proof/.test(location.hash)) {
      startProof('hash');
    }

    // Expose for QA.
    window.JCPJobProofDemo = {
      buildTrialUrl: buildTrialUrl,
      trackProof: trackProof,
      showMoment: showMoment,
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
