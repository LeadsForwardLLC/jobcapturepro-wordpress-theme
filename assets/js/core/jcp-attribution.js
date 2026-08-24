/**
 * First-touch lead attribution for paid LP → demo funnel
 * (UTMs, fbclid, landing page, referrer).
 * Stored in sessionStorage for the tab session and sent with GHL webhook payloads.
 */
(function () {
  const STORAGE_KEY = 'jcp_lead_attribution';
  const PARAM_KEYS = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'fbclid',
  ];

  function captureLeadAttribution() {
    try {
      if (sessionStorage.getItem(STORAGE_KEY)) return;
      const params = new URLSearchParams(window.location.search);
      const data = {
        landing_page: window.location.pathname + window.location.search,
        referrer: document.referrer || '',
      };
      PARAM_KEYS.forEach((key) => {
        data[key] = params.get(key) || '';
      });
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
      // no-op
    }
  }

  function getLeadAttributionPayload() {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) return {};
      const data = JSON.parse(raw);
      const out = {};
      PARAM_KEYS.forEach((key) => {
        const value = data[key] != null ? String(data[key]).trim() : '';
        if (value) out[key] = value;
      });
      if (data.landing_page) out.landing_page = String(data.landing_page).trim();
      if (data.referrer) out.referrer = String(data.referrer).trim();
      return out;
    } catch (e) {
      return {};
    }
  }

  /** Append stored UTMs to bare /demo/ links so shareable URLs keep attribution too. */
  function decorateDemoLinks() {
    try {
      const payload = getLeadAttributionPayload();
      const keys = PARAM_KEYS.filter((k) => payload[k]);
      if (!keys.length) return;
      document.querySelectorAll('a[href*="/demo"]').forEach((a) => {
        try {
          const url = new URL(a.href, window.location.origin);
          if (!url.pathname.replace(/\/$/, '').endsWith('/demo') && !url.pathname.includes('/demo/')) {
            return;
          }
          let changed = false;
          keys.forEach((key) => {
            if (!url.searchParams.get(key) && payload[key]) {
              url.searchParams.set(key, payload[key]);
              changed = true;
            }
          });
          if (changed) a.href = url.pathname + url.search + url.hash;
        } catch (e) {
          // no-op
        }
      });
    } catch (e) {
      // no-op
    }
  }

  window.JCPLeadAttribution = {
    capture: captureLeadAttribution,
    getPayload: getLeadAttributionPayload,
    decorateDemoLinks: decorateDemoLinks,
  };

  captureLeadAttribution();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', decorateDemoLinks);
  } else {
    decorateDemoLinks();
  }
})();
