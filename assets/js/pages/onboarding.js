/**
 * Customer onboarding hub analytics (PostHog / dataLayer).
 * Does not track QR scans — store button clicks only.
 */
(function () {
  'use strict';

  function capture(name, props) {
    props = props || {};
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(Object.assign({ event: name }, props));
    } catch (eDl) {}
    try {
      if (window.JCPPostHog && typeof window.JCPPostHog.capture === 'function') {
        window.JCPPostHog.capture(name, props);
        return;
      }
    } catch (ePh) {}
    try {
      if (window.posthog && typeof window.posthog.capture === 'function') {
        window.posthog.capture(name, props);
      }
    } catch (eRaw) {}
  }

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  onReady(function () {
    capture('onboarding_page_viewed', {
      page: 'onboarding_hub',
      path: location.pathname,
    });

    document.querySelectorAll('[data-jcp-store]').forEach(function (el) {
      el.addEventListener('click', function () {
        var platform = el.getAttribute('data-jcp-store') || '';
        if (platform !== 'ios' && platform !== 'android') return;
        capture('onboarding_app_store_clicked', { platform: platform });
      });
    });

    var preview = document.querySelector('[data-jcp-ob-preview]') || document.getElementById('jcp-app');
    var interacted = false;
    function markInteracted() {
      if (interacted) return;
      interacted = true;
      capture('onboarding_preview_interacted', { page: 'onboarding_hub' });
      if (preview) {
        preview.removeEventListener('pointerdown', markInteracted, true);
        preview.removeEventListener('keydown', markInteracted, true);
      }
    }
    if (preview) {
      preview.addEventListener('pointerdown', markInteracted, true);
      preview.addEventListener('keydown', markInteracted, true);
    }
  });
})();
