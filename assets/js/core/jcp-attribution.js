/**
 * First-touch lead attribution for paid LP → demo funnel
 * (UTMs, fbclid, landing page, referrer, optional GHL contact_id).
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
  const EXTRA_KEYS = ['lp_variant'];

  function readLpVariantFromPage() {
    try {
      const fromBody = document.body && document.body.getAttribute('data-jcp-lp-variant');
      const fromHtml = document.documentElement && document.documentElement.getAttribute('data-jcp-lp-variant');
      const raw = (fromBody || fromHtml || '').trim();
      return raw ? raw.slice(0, 64) : '';
    } catch (e) {
      return '';
    }
  }

  /**
   * GoHighLevel contact IDs are opaque strings (often 20–28 alphanumeric).
   * Reject empty / clearly invalid values so the demo falls back safely.
   */
  function isValidGhlContactId(value) {
    if (value == null) return false;
    const id = String(value).trim();
    if (id.length < 8 || id.length > 64) return false;
    return /^[A-Za-z0-9_-]+$/.test(id);
  }

  function readContactIdFromUrl() {
    try {
      const params = new URLSearchParams(window.location.search);
      const raw = params.get('contact_id') || params.get('contactId') || '';
      return isValidGhlContactId(raw) ? String(raw).trim() : '';
    } catch (e) {
      return '';
    }
  }

  function readStoredAttribution() {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      return data && typeof data === 'object' ? data : null;
    } catch (e) {
      return null;
    }
  }

  function writeStoredAttribution(data) {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
      // no-op
    }
  }

  /**
   * Capture first-touch UTMs once; always merge a valid contact_id from the URL
   * so social-comment deep links keep the original GHL contact for the session.
   */
  function captureLeadAttribution() {
    try {
      const params = new URLSearchParams(window.location.search);
      let data = readStoredAttribution();
      if (!data) {
        data = {
          landing_page: window.location.pathname + window.location.search,
          referrer: document.referrer || '',
        };
        PARAM_KEYS.forEach((key) => {
          data[key] = params.get(key) || '';
        });
      }

      // Preserve first-touch UTMs; always stamp LP variant from page or URL when present.
      const pageVariant = readLpVariantFromPage() || params.get('lp_variant') || '';
      if (pageVariant && !data.lp_variant) {
        data.lp_variant = pageVariant;
      }

      const contactId = readContactIdFromUrl();
      if (contactId) {
        data.contact_id = contactId;
      }

      writeStoredAttribution(data);
    } catch (e) {
      // no-op
    }
  }

  function getLeadAttributionPayload() {
    try {
      const data = readStoredAttribution();
      if (!data) return {};
      const out = {};
      PARAM_KEYS.forEach((key) => {
        const value = data[key] != null ? String(data[key]).trim() : '';
        if (value) out[key] = value;
      });
      EXTRA_KEYS.forEach((key) => {
        const value = data[key] != null ? String(data[key]).trim() : '';
        if (value) out[key] = value;
      });
      if (data.landing_page) out.landing_page = String(data.landing_page).trim();
      if (data.referrer) out.referrer = String(data.referrer).trim();
      if (isValidGhlContactId(data.contact_id)) {
        out.contact_id = String(data.contact_id).trim();
      }
      return out;
    } catch (e) {
      return {};
    }
  }

  /** Append stored UTMs (+ contact_id + lp_variant) to bare /demo/ links so shareable URLs keep attribution too. */
  function decorateDemoLinks() {
    try {
      const payload = getLeadAttributionPayload();
      const keys = PARAM_KEYS.filter((k) => payload[k]);
      EXTRA_KEYS.forEach((k) => {
        if (payload[k]) keys.push(k);
      });
      if (payload.contact_id) keys.push('contact_id');
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
    isValidContactId: isValidGhlContactId,
  };

  captureLeadAttribution();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', decorateDemoLinks);
  } else {
    decorateDemoLinks();
  }
})();
