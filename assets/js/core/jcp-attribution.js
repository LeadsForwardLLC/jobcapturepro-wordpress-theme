/**
 * Lead attribution for paid LP → demo / trial funnel
 * (UTMs, fbclid, landing page, referrer, optional GHL contact_id).
 *
 * Flat fields are CURRENT touch (refreshed when a paid URL lands).
 * first_touch_snapshot preserves historical first-touch for acquisition reporting.
 * Stored in localStorage + sessionStorage; sent with GHL / PostHog / onboarding handoff.
 */
(function () {
  const STORAGE_KEY = 'jcp_lead_attribution';
  const STORAGE_KEY_PERSIST = 'jcp_lead_attribution_v2';
  const PARAM_KEYS = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'fbclid',
  ];
  // Durable conversion fields. Cookies (_fbp/_fbc) are POST-only — never URL-decorated.
  const EXTRA_KEYS = ['lp_variant', 'jcp_pg_variant', 'funnel_version', 'qa_trace_id', 'first_touch_timestamp', '_fbp', '_fbc'];
  const URL_SAFE_EXTRA_KEYS = ['lp_variant', 'jcp_pg_variant', 'funnel_version', 'qa_trace_id', 'first_touch_timestamp'];

  /** Path → analytics key for paid LPs (belt-and-suspenders if PHP attr misses). */
  const PATH_VARIANT_MAP = {
    '/contractor-demo': 'contractor_demo',
    '/contractor-formula': 'formula',
    '/contractor-nature': 'nature_doc',
    '/job-proof': 'proof_waste',
    '/why-we-built-jcp': 'founder',
    '/job-proof-demo': 'job_proof_demo',
    '/proof-sprint': 'proof_sprint',
    // Proof Gap entry A/B: lp_variant is control|direct_question (set by proof-gap-entry-ab.js).
    // Do not map path → proof_gap_survey_v1 (that id is now funnel_version).
  };

  function readLpVariantFromPath() {
    try {
      const path = String(window.location.pathname || '').replace(/\/+$/, '') || '/';
      if (PATH_VARIANT_MAP[path]) return PATH_VARIANT_MAP[path];
      // Child routes (e.g. /job-proof-demo/demo) inherit parent LP variant.
      if (path.indexOf('/job-proof-demo') === 0) return 'job_proof_demo';
      return '';
    } catch (e) {
      return '';
    }
  }

  function readLpVariantFromPage() {
    try {
      const fromBody = document.body && document.body.getAttribute('data-jcp-lp-variant');
      const fromHtml = document.documentElement && document.documentElement.getAttribute('data-jcp-lp-variant');
      const fromPath = readLpVariantFromPath();
      const raw = (fromBody || fromHtml || fromPath || '').trim();
      if (raw && document.body && !document.body.getAttribute('data-jcp-lp-variant')) {
        document.body.setAttribute('data-jcp-lp-variant', raw);
        document.documentElement.setAttribute('data-jcp-lp-variant', raw);
      }
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

  function readCookie(name) {
    try {
      const m = document.cookie.match(
        new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)')
      );
      return m ? decodeURIComponent(m[1]) : '';
    } catch (e) {
      return '';
    }
  }

  function readStoredAttribution() {
    try {
      const persisted = localStorage.getItem(STORAGE_KEY_PERSIST);
      if (persisted) {
        const data = JSON.parse(persisted);
        if (data && typeof data === 'object') return data;
      }
    } catch (ePersist) {}
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
    try {
      localStorage.setItem(STORAGE_KEY_PERSIST, JSON.stringify(data));
    } catch (e2) {
      // no-op
    }
  }

  /**
   * Snapshot first-touch fields once (historical acquisition).
   * Flat utm_*, qa_trace_id, lp_variant, landing_page remain the CURRENT paid touch
   * so Gap vs Sprint experiment handoff and PostHog props are not polluted by a
   * sticky first-touch record from an earlier LP visit in the same browser.
   */
  function ensureFirstTouchSnapshot(data) {
    if (!data || typeof data !== 'object') return;
    if (data.first_touch_snapshot) return;
    const snap = {};
    PARAM_KEYS.forEach((key) => {
      if (data[key]) snap[key] = String(data[key]);
    });
    if (data.lp_variant) snap.lp_variant = String(data.lp_variant);
    if (data.qa_trace_id) snap.qa_trace_id = String(data.qa_trace_id);
    if (data.landing_page) snap.landing_page = String(data.landing_page);
    if (data.referrer) snap.referrer = String(data.referrer);
    if (data.first_touch_timestamp) snap.first_touch_timestamp = String(data.first_touch_timestamp);
    data.first_touch_snapshot = snap;
  }

  function urlHasPaidTouch(params) {
    try {
      if (params.get('qa_trace_id') || params.get('jcp_qa_trace') || params.get('qa_trace')) return true;
      return PARAM_KEYS.some((key) => {
        const v = params.get(key);
        return !!(v && String(v).trim());
      });
    } catch (e) {
      return false;
    }
  }

  /**
   * Capture attribution: first-touch once (snapshot), current-touch from URL/page
   * whenever a paid visit brings fresh UTMs / qa_trace_id / LP variant.
   * Always merge a valid contact_id from the URL for GHL deep links.
   */
  function captureLeadAttribution() {
    try {
      const params = new URLSearchParams(window.location.search);
      let data = readStoredAttribution();
      if (!data) {
        data = {
          landing_page: window.location.pathname + window.location.search,
          referrer: document.referrer || '',
          first_touch_timestamp: new Date().toISOString(),
        };
        PARAM_KEYS.forEach((key) => {
          data[key] = params.get(key) || '';
        });
      }

      if (!data.first_touch_timestamp) {
        data.first_touch_timestamp = new Date().toISOString();
      }
      if (!data.landing_page) {
        data.landing_page = window.location.pathname + window.location.search;
      }
      if (!data.referrer && document.referrer) {
        data.referrer = document.referrer;
      }

      // Freeze historical first-touch before any current-touch refresh.
      ensureFirstTouchSnapshot(data);

      // Refresh CURRENT paid touch when this navigation carries campaign params.
      if (urlHasPaidTouch(params)) {
        PARAM_KEYS.forEach((key) => {
          const v = params.get(key);
          if (v != null && String(v).trim() !== '') {
            data[key] = String(v).trim();
          }
        });
        data.landing_page = window.location.pathname + window.location.search;
        if (document.referrer) data.referrer = document.referrer;
      }

      // Current LP variant always follows the page the visitor is on (experiment surface).
      const pageVariant = readLpVariantFromPage() || params.get('lp_variant') || '';
      if (pageVariant) {
        data.lp_variant = String(pageVariant).trim().slice(0, 64);
      }

      const qaTrace =
        params.get('qa_trace_id') || params.get('jcp_qa_trace') || params.get('qa_trace') || '';
      if (qaTrace) {
        data.qa_trace_id = String(qaTrace).trim().slice(0, 80);
      }

      const fbp = readCookie('_fbp');
      const fbc = readCookie('_fbc');
      if (fbp) data._fbp = fbp;
      if (fbc) data._fbc = fbc;

      const contactId = readContactIdFromUrl();
      if (contactId) {
        data.contact_id = contactId;
      }

      writeStoredAttribution(data);
    } catch (e) {
      // no-op
    }
  }

  function readPhDistinctId() {
    try {
      if (window.JCPPostHog && typeof window.JCPPostHog.getDistinctId === 'function') {
        const id = String(window.JCPPostHog.getDistinctId() || '').trim();
        if (id && id.length >= 8) return id.slice(0, 128);
      }
    } catch (e) {}
    try {
      const fromLs = localStorage.getItem('jcp_ph_distinct_id');
      if (fromLs && String(fromLs).trim().length >= 8) return String(fromLs).trim().slice(0, 128);
    } catch (e2) {}
    return '';
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
      if (data.first_touch_timestamp) {
        out.first_touch_timestamp = String(data.first_touch_timestamp).trim();
      }
      if (data.qa_trace_id) out.qa_trace_id = String(data.qa_trace_id).trim();
      if (data._fbp) out._fbp = String(data._fbp).trim();
      if (data._fbc) out._fbc = String(data._fbc).trim();
      if (isValidGhlContactId(data.contact_id)) {
        out.contact_id = String(data.contact_id).trim();
      }
      const phId = readPhDistinctId();
      if (phId) out.ph_distinct_id = phId;
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
      // Never put _fbp/_fbc/ph_distinct_id into public URLs.
      URL_SAFE_EXTRA_KEYS.forEach((k) => {
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

  function getFirstTouchPayload() {
    try {
      const data = readStoredAttribution();
      if (!data || !data.first_touch_snapshot || typeof data.first_touch_snapshot !== 'object') {
        return {};
      }
      return Object.assign({}, data.first_touch_snapshot);
    } catch (e) {
      return {};
    }
  }

  window.JCPLeadAttribution = {
    capture: captureLeadAttribution,
    getPayload: getLeadAttributionPayload,
    getFirstTouchPayload: getFirstTouchPayload,
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
