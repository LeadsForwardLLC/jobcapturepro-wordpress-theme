/**
 * Proof Sprint — direct Meta LP (trial-first).
 * PostHog canonical events + attribution handoff. No survey / demo gate.
 */
(function () {
  'use strict';

  var LP_VARIANT = 'proof_sprint';
  var viewedOnce = false;
  var ctaViewed = {};
  var channelViewed = {};
  var caseViewed = false;
  var faqOpened = {};

  var POSTHOG_CANONICAL = {
    proof_sprint_viewed: true,
    proof_sprint_cta_clicked: true,
    proof_sprint_output_viewed: true,
    proof_sprint_case_study_viewed: true,
    proof_sprint_faq_opened: true,
    trial_cta_viewed: true,
    trial_cta_clicked: true,
  };

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
    return {};
  }

  function capturePostHog(name, props) {
    if (!POSTHOG_CANONICAL[name]) return;
    try {
      if (window.JCPPostHog && typeof window.JCPPostHog.capture === 'function') {
        window.JCPPostHog.capture(name, Object.assign({ source: 'proof_sprint', lp_variant: LP_VARIANT }, props || {}));
      }
    } catch (e) {}
  }

  function track(eventName, extra) {
    var payload = Object.assign(
      {
        event: eventName,
        lp_variant: LP_VARIANT,
        page: location.pathname,
        page_path: location.pathname,
        source: 'proof_sprint',
      },
      extra || {}
    );
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(payload);
    } catch (e) {}
    capturePostHog(eventName, payload);
  }

  function prefersReducedMotion() {
    try {
      return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {
      return false;
    }
  }

  /* ---- Trial link decoration ---- */
  function decorateTrialLinks() {
    var base =
      (window.JCP_ONBOARDING && window.JCP_ONBOARDING.url) ||
      'https://app.jobcapturepro.com/onboarding';
    var attr = attrPayload();

    document.querySelectorAll('[data-ps-trial]').forEach(function (a) {
      try {
        var placement = a.getAttribute('data-ps-placement') || 'trial';
        var handoffExtra = {
          lp_variant: LP_VARIANT,
          jcp_surface: 'proof_sprint_' + placement,
        };
        if (attr.qa_trace_id) handoffExtra.qa_trace_id = attr.qa_trace_id;
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'].forEach(function (k) {
          if (attr[k] && String(attr[k]).indexOf('jobcapturepro.com') === -1) {
            handoffExtra[k] = attr[k];
          }
        });
        try {
          if (window.JCPPostHog && typeof window.JCPPostHog.getDistinctId === 'function') {
            var phId = window.JCPPostHog.getDistinctId();
            if (phId) handoffExtra.ph_distinct_id = phId;
          }
        } catch (ePh) {}

        if (window.JCPOnboardingHandoff && typeof window.JCPOnboardingHandoff.buildHandoffParams === 'function') {
          var built = window.JCPOnboardingHandoff.buildHandoffParams() || {};
          Object.keys(built).forEach(function (k) {
            if (handoffExtra[k] == null || handoffExtra[k] === '') handoffExtra[k] = built[k];
          });
        }

        var href = a.getAttribute('href') || base;
        if (window.JCPOnboardingHandoff && typeof window.JCPOnboardingHandoff.decorateHref === 'function') {
          href =
            window.JCPOnboardingHandoff.decorateHref(href, handoffExtra, 'proof_sprint_' + placement) || href;
        } else {
          var u = new URL(href, location.origin);
          Object.keys(handoffExtra).forEach(function (k) {
            if (handoffExtra[k]) u.searchParams.set(k, handoffExtra[k]);
          });
          href = u.toString();
        }
        a.href = href;
      } catch (err) {}

      a.addEventListener('click', function () {
        var placement = a.getAttribute('data-ps-placement') || 'trial';
        var dest = a.href || '';
        track('proof_sprint_cta_clicked', {
          placement: placement,
          destination: dest,
          source: 'proof_sprint',
        });
        track('trial_cta_clicked', {
          source: 'proof_sprint',
          placement: placement,
          destination: dest,
        });
        // Intentionally NO trial_started — that fires only after backend provisioning.
      });
    });
  }

  /* ---- Hero theater ---- */
  function runHeroTheater() {
    var root = document.querySelector('[data-ps-theater] .ps-theater');
    if (!root) return;
    var outs = root.querySelectorAll('[data-out]');
    if (prefersReducedMotion()) {
      root.classList.add('is-live', 'is-processing');
      outs.forEach(function (el) {
        el.classList.add('is-on');
      });
      return;
    }
    root.classList.add('is-running');
    window.setTimeout(function () {
      root.classList.add('is-processing');
    }, 400);
    outs.forEach(function (el, i) {
      window.setTimeout(function () {
        el.classList.add('is-on');
        if (i === outs.length - 1) {
          root.classList.remove('is-running');
          root.classList.add('is-live');
        }
      }, 600 + i * 320);
    });
  }

  /* ---- Channel tabs ---- */
  function setupOutputs() {
    var root = document.querySelector('[data-ps-outputs]');
    if (!root) return;
    var tabs = root.querySelectorAll('[data-ps-channel]');
    var panels = root.querySelectorAll('[data-ps-panel]');

    function activate(id, fromUser) {
      tabs.forEach(function (tab) {
        var on = tab.getAttribute('data-ps-channel') === id;
        tab.classList.toggle('is-active', on);
        tab.setAttribute('aria-selected', on ? 'true' : 'false');
        tab.tabIndex = on ? 0 : -1;
      });
      panels.forEach(function (panel) {
        var on = panel.getAttribute('data-ps-panel') === id;
        panel.classList.toggle('is-active', on);
        if (on) panel.removeAttribute('hidden');
        else panel.setAttribute('hidden', '');
      });
      if (fromUser && !channelViewed[id]) {
        channelViewed[id] = true;
        track('proof_sprint_output_viewed', { channel: id });
      }
    }

    tabs.forEach(function (tab, idx) {
      tab.addEventListener('click', function () {
        activate(tab.getAttribute('data-ps-channel'), true);
      });
      tab.addEventListener('keydown', function (e) {
        var next = null;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = tabs[Math.min(tabs.length - 1, idx + 1)];
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = tabs[Math.max(0, idx - 1)];
        if (e.key === 'Home') next = tabs[0];
        if (e.key === 'End') next = tabs[tabs.length - 1];
        if (next) {
          e.preventDefault();
          next.focus();
          activate(next.getAttribute('data-ps-channel'), true);
        }
      });
    });

    // First panel counts as viewed when section enters viewport.
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var first = tabs[0] && tabs[0].getAttribute('data-ps-channel');
            if (first && !channelViewed[first]) {
              channelViewed[first] = true;
              track('proof_sprint_output_viewed', { channel: first });
            }
            io.disconnect();
          });
        },
        { threshold: 0.35 }
      );
      io.observe(root);
    }
  }

  /* ---- Case study expand ---- */
  function setupCaseStudy() {
    var more = document.querySelector('[data-ps-case-more]');
    if (!more) return;
    more.addEventListener('toggle', function () {
      if (more.open && !caseViewed) {
        caseViewed = true;
        track('proof_sprint_case_study_viewed', {});
      }
    });
  }

  /* ---- FAQ ---- */
  function setupFaq() {
    document.querySelectorAll('[data-ps-faq]').forEach(function (el) {
      el.addEventListener('toggle', function () {
        if (!el.open) return;
        var q = (el.querySelector('summary') && el.querySelector('summary').textContent) || el.getAttribute('data-ps-faq') || '';
        q = String(q).trim().slice(0, 120);
        if (faqOpened[q]) return;
        faqOpened[q] = true;
        track('proof_sprint_faq_opened', { question: q });
      });
    });
  }

  /* ---- Sticky mobile CTA ---- */
  function setupStickyCta() {
    var sticky = document.getElementById('psStickyCta');
    if (!sticky) return;
    var hero = document.getElementById('ps-hero');
    var footer = document.querySelector('.ps-footer');
    var final = document.getElementById('ps-final');

    function update() {
      var show = false;
      try {
        if (window.matchMedia('(max-width: 768px)').matches) {
          var heroBottom = hero ? hero.getBoundingClientRect().bottom : 0;
          var nearEnd = false;
          if (footer) {
            nearEnd = footer.getBoundingClientRect().top < window.innerHeight - 40;
          }
          if (final) {
            var fr = final.getBoundingClientRect();
            if (fr.top < window.innerHeight && fr.bottom > 0) nearEnd = true;
          }
          show = heroBottom < 40 && !nearEnd;
        }
      } catch (e) {}
      sticky.hidden = !show;
      document.body.classList.toggle('ps-has-sticky', show);
    }

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  }

  /* ---- Trial CTA viewed ---- */
  function setupCtaViewed() {
    if (!('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          var placement = el.getAttribute('data-ps-placement') || 'unknown';
          if (ctaViewed[placement]) return;
          ctaViewed[placement] = true;
          track('trial_cta_viewed', { source: 'proof_sprint', placement: placement });
          io.unobserve(el);
        });
      },
      { threshold: 0.5 }
    );
    document.querySelectorAll('[data-ps-track="trial_cta"]').forEach(function (el) {
      io.observe(el);
    });
  }

  function init() {
    if (!document.body || !document.body.classList.contains('jcp-proof-sprint')) return;

    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.capture === 'function') {
        window.JCPLeadAttribution.capture({ lp_variant: LP_VARIANT });
      }
    } catch (eAttr) {}

    decorateTrialLinks();
    runHeroTheater();
    setupOutputs();
    setupCaseStudy();
    setupFaq();
    setupStickyCta();
    setupCtaViewed();

    if (!viewedOnce) {
      viewedOnce = true;
      track('proof_sprint_viewed', {});
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
