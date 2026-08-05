/**
 * First-touch lead attribution for the demo funnel (UTMs, landing page, referrer).
 * Stored in sessionStorage for the tab session and sent with GHL webhook payloads.
 */
(function () {
  const STORAGE_KEY = 'jcp_lead_attribution';

  function captureLeadAttribution() {
    try {
      if (sessionStorage.getItem(STORAGE_KEY)) return;
      const params = new URLSearchParams(window.location.search);
      const data = {
        utm_source: params.get('utm_source') || '',
        utm_medium: params.get('utm_medium') || '',
        utm_campaign: params.get('utm_campaign') || '',
        utm_content: params.get('utm_content') || '',
        landing_page: window.location.pathname + window.location.search,
        referrer: document.referrer || '',
      };
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
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'].forEach((key) => {
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

  window.JCPLeadAttribution = {
    capture: captureLeadAttribution,
    getPayload: getLeadAttributionPayload,
  };

  captureLeadAttribution();
})();
