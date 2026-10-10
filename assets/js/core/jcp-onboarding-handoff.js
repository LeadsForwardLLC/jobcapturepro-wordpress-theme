/**
 * App onboarding link decorator — merges demo user + paid attribution into trial URLs.
 *
 * Acquisition UTMs (utm_*) come ONLY from JCPLeadAttribution current-touch payload.
 * Fake marketing defaults (jobcapturepro.com / website / onboarding) are stripped.
 * Internal CTA placement uses jcp_surface — never utm_content.
 */
(() => {
  const ONB_HOST = 'app.jobcapturepro.com';
  const ONB_PATH = '/onboarding';

  const MARKETING_UTM_DEFAULTS = {
    utm_source: 'jobcapturepro.com',
    utm_medium: 'website',
    utm_campaign: 'onboarding',
  };

  /** Legacy CTA labels wrongly stuffed into utm_content — migrate to jcp_surface. */
  const INTERNAL_UTM_CONTENT = {
    proof_sprint_trial: 1,
    proof_gap_survey_trial: 1,
    job_proof_demo_trial: 1,
    job_proof_demo_run_trial: 1,
    nav_get_started: 1,
    home_hero: 1,
    pricing: 1,
    sitewide_banner: 1,
    demo_handoff: 1,
    sales_tool: 1,
    demo_post_panel: 1,
  };

  const PAID_ATTR_KEYS = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'fbclid',
    'lp_variant',
    'jcp_pg_variant',
    'funnel_version',
    'qa_trace_id',
  ];

  const safeJson = (raw) => {
    try {
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  };

  const readDemoUser = () => {
    if (typeof window === 'undefined') return null;
    const raw = window.localStorage ? window.localStorage.getItem('demoUser') : null;
    const obj = raw ? safeJson(raw) : null;
    return obj && typeof obj === 'object' ? obj : null;
  };

  const readDemoSession = () => {
    try {
      return (
        (window.sessionStorage && window.sessionStorage.getItem('jcp_demo_session_id')) ||
        (window.localStorage && window.localStorage.getItem('jcp_demo_session_id')) ||
        null
      );
    } catch (e) {
      return null;
    }
  };

  const normalizeIndustryId = (raw) => {
    const val = (raw || '').toString().trim().toLowerCase();
    if (!val) return '';

    const allowed = new Set([
      'hvac',
      'plumbing',
      'cleaning-services',
      'pool-service',
      'roofing',
      'solar',
      'carpet-cleaning',
      'foundation-repair',
      'dumpster-rental',
      'tree-service',
      'deck-builder',
      'home-inspection',
      'home-windows',
    ]);

    if (allowed.has(val)) return val;

    const alias = {
      'cleaning service': 'cleaning-services',
      'cleaning services': 'cleaning-services',
      'house-cleaning': 'cleaning-services',
      'home-windows': 'home-windows',
      'windows-doors': 'home-windows',
      'windows & doors': 'home-windows',
      'home windows': 'home-windows',
      'deck builder': 'deck-builder',
      'tree service': 'tree-service',
      'pool service': 'pool-service',
    };
    if (alias[val]) return alias[val];

    const slug = val
      .replace(/['"]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    return allowed.has(slug) ? slug : '';
  };

  const readAttribution = () => {
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        return window.JCPLeadAttribution.getPayload() || {};
      }
    } catch (e) {}
    // Fallback when attribution.js is not on the page: read persisted current-touch store.
    try {
      const raw =
        (window.localStorage && window.localStorage.getItem('jcp_lead_attribution_v2')) ||
        (window.sessionStorage && window.sessionStorage.getItem('jcp_lead_attribution')) ||
        '';
      if (!raw) return {};
      const data = JSON.parse(raw);
      if (!data || typeof data !== 'object') return {};
      const out = {};
      [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbclid',
        'lp_variant',
        'jcp_pg_variant',
        'funnel_version',
        'qa_trace_id',
        'landing_page',
        'referrer',
        'contact_id',
        'ph_distinct_id',
      ].forEach((key) => {
        const val = data[key];
        if (val != null && String(val).trim() !== '') out[key] = String(val).trim();
      });
      return out;
    } catch (e2) {
      return {};
    }
  };

  const isMarketingDefault = (key, value) => {
    const def = MARKETING_UTM_DEFAULTS[key];
    if (!def) return false;
    return String(value || '') === def;
  };

  const isInternalUtmContent = (value) => {
    const v = String(value || '').trim();
    if (!v) return false;
    if (INTERNAL_UTM_CONTENT[v]) return true;
    // Catch placement-style labels still leaked into utm_content.
    if (/^proof_sprint_/.test(v)) return true;
    if (/_trial$/.test(v) && !/^qa_/i.test(v)) return true;
    return false;
  };

  /**
   * Remove fabricated acquisition UTMs and migrate internal utm_content → jcp_surface.
   */
  const scrubFakeAcquisition = (u) => {
    Object.keys(MARKETING_UTM_DEFAULTS).forEach((k) => {
      if (isMarketingDefault(k, u.searchParams.get(k))) {
        u.searchParams.delete(k);
      }
    });
    const content = u.searchParams.get('utm_content') || '';
    if (isInternalUtmContent(content)) {
      if (!u.searchParams.get('jcp_surface')) {
        u.searchParams.set('jcp_surface', content);
      }
      u.searchParams.delete('utm_content');
    }
  };

  const readPhDistinctId = () => {
    try {
      if (window.JCPPostHog && typeof window.JCPPostHog.getDistinctId === 'function') {
        const id = String(window.JCPPostHog.getDistinctId() || '').trim();
        if (id.length >= 8) return id.slice(0, 128);
      }
    } catch (e) {}
    try {
      const fromLs = window.localStorage && window.localStorage.getItem('jcp_ph_distinct_id');
      if (fromLs && String(fromLs).trim().length >= 8) return String(fromLs).trim().slice(0, 128);
    } catch (e2) {}
    try {
      const m = document.cookie.match(/(?:^|; )jcp_ph_id=([^;]*)/);
      if (m && m[1]) {
        const id = decodeURIComponent(m[1]).trim();
        if (id.length >= 8) return id.slice(0, 128);
      }
    } catch (e3) {}
    return '';
  };

  const buildHandoffParams = () => {
    const u = readDemoUser();
    const params = {};

    if (u) {
      const first = (u.firstName || '').trim();
      const last = (u.lastName || '').trim();
      const email = (u.email || '').trim();
      const company = (u.businessName || '').trim();
      const phone = (u.phone || '').trim();
      const businessType = (u.niche || '').trim();
      const fullName = [first, last].filter(Boolean).join(' ').trim();

      if (first) params.first_name = first;
      if (last) params.last_name = last;
      if (email) params.email = email;
      if (phone) {
        params.phone = phone;
        params.mobile = phone;
        params.mobile_phone = phone;
      }
      if (fullName) {
        params.full_name = fullName;
        params.fullName = fullName;
        params.name = fullName;
      }

      if (company && company.toLowerCase() !== 'your business') {
        params.company = company;
        params.organization_name = company;
        params.organizationName = company;
      }
      if (businessType) {
        params.business_type = businessType;
        params.industry = businessType;
        params.service_industry = businessType;
        params.serviceIndustry = businessType;

        const industryId = normalizeIndustryId(businessType);
        if (industryId) {
          params.industryId = industryId;
          params.industry_id = industryId;
        }
      }
    }

    const demoSession = readDemoSession();
    if (demoSession) params.demo_session = demoSession;

    const attr = readAttribution();
    [
      'utm_source',
      'utm_medium',
      'utm_campaign',
      'utm_content',
      'utm_term',
      'fbclid',
      'lp_variant',
      'jcp_pg_variant',
      'funnel_version',
      'qa_trace_id',
      'landing_page',
      'referrer',
      'contact_id',
      'ph_distinct_id',
    ].forEach((key) => {
      const val = attr[key];
      if (val != null && String(val).trim() !== '') {
        // Never propagate fabricated marketing-site source.
        if (key === 'utm_source' && String(val).indexOf('jobcapturepro.com') !== -1) return;
        if (key.indexOf('utm_') === 0 && isMarketingDefault(key, val)) return;
        if (key === 'utm_content' && isInternalUtmContent(val)) return;
        params[key] = String(val).trim();
      }
    });

    // Always prefer live marketing distinct_id so app can bootstrap identity.
    const livePh = readPhDistinctId();
    if (livePh) params.ph_distinct_id = livePh;

    return Object.keys(params).length ? params : null;
  };

  const isOnboardingUrl = (href) => {
    if (!href || typeof href !== 'string') return false;
    if (!href.includes(ONB_PATH)) return false;
    if (href.startsWith('http')) {
      try {
        const url = new URL(href);
        return url.hostname === ONB_HOST && url.pathname === ONB_PATH;
      } catch (e) {
        return false;
      }
    }
    return href.includes(ONB_PATH);
  };

  /**
   * Merge handoff params into onboarding href.
   * Always scrub fake acquisition UTMs; paid keys overwrite when present.
   */
  const decorateHref = (href, extraParams, surface) => {
    try {
      const u = href.startsWith('http') ? new URL(href) : new URL(href, window.location.origin);

      scrubFakeAcquisition(u);

      if (surface) {
        u.searchParams.set('jcp_surface', String(surface));
      }

      Object.keys(extraParams || {}).forEach((k) => {
        const val = extraParams[k];
        if (val === undefined || val === null || String(val).trim() === '') return;
        if (k === 'utm_content' && isInternalUtmContent(val)) {
          if (!u.searchParams.get('jcp_surface')) {
            u.searchParams.set('jcp_surface', String(val));
          }
          return;
        }
        if (k.indexOf('utm_') === 0 && isMarketingDefault(k, val)) return;
        if (k === 'utm_source' && String(val).indexOf('jobcapturepro.com') !== -1) return;

        if (PAID_ATTR_KEYS.indexOf(k) !== -1) {
          // Acquisition / attribution keys always win over whatever was in the static href.
          u.searchParams.set(k, String(val));
          return;
        }

        // Non-attribution keys: only fill if missing (PII etc.).
        if (!u.searchParams.has(k)) {
          u.searchParams.set(k, String(val));
        }
      });

      // Final scrub in case extras reintroduced fakes.
      scrubFakeAcquisition(u);

      // Guarantee cross-domain identity param even if extras omitted it.
      if (!u.searchParams.get('ph_distinct_id')) {
        const livePh = readPhDistinctId();
        if (livePh) u.searchParams.set('ph_distinct_id', livePh);
      }

      return u.toString();
    } catch (e) {
      return href;
    }
  };

  const decorateAll = () => {
    const attr = readAttribution();
    const attrOnly = {};
    PAID_ATTR_KEYS.forEach((k) => {
      if (attr[k]) {
        if (k === 'utm_source' && String(attr[k]).indexOf('jobcapturepro.com') !== -1) return;
        if (k.indexOf('utm_') === 0 && isMarketingDefault(k, attr[k])) return;
        if (k === 'utm_content' && isInternalUtmContent(attr[k])) return;
        attrOnly[k] = attr[k];
      }
    });
    if (attr.ph_distinct_id) attrOnly.ph_distinct_id = attr.ph_distinct_id;

    const extra = Object.assign({}, attrOnly, buildHandoffParams() || {});

    document.querySelectorAll('a[href]').forEach((a) => {
      const href = a.getAttribute('href') || '';
      if (!isOnboardingUrl(href)) return;
      const next = decorateHref(href, extra);
      if (next && next !== href) a.setAttribute('href', next);
    });
  };

  document.addEventListener(
    'click',
    (event) => {
      const a = event.target && event.target.closest ? event.target.closest('a[href]') : null;
      if (!a) return;
      const href = a.getAttribute('href') || '';
      if (!isOnboardingUrl(href)) return;
      const extra = buildHandoffParams() || {};
      const attr = readAttribution();
      PAID_ATTR_KEYS.forEach((k) => {
        if (!attr[k]) return;
        if (k === 'utm_source' && String(attr[k]).indexOf('jobcapturepro.com') !== -1) return;
        if (k.indexOf('utm_') === 0 && isMarketingDefault(k, attr[k])) return;
        if (k === 'utm_content' && isInternalUtmContent(attr[k])) return;
        extra[k] = attr[k];
      });
      if (attr.ph_distinct_id) extra.ph_distinct_id = attr.ph_distinct_id;
      const next = decorateHref(href, extra);
      if (next && next !== href) a.setAttribute('href', next);
    },
    true
  );

  const run = () => {
    decorateAll();
    setTimeout(decorateAll, 300);
    setTimeout(decorateAll, 1200);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }

  window.JCPOnboardingHandoff = {
    decorateHref,
    buildHandoffParams,
    readAttribution,
  };
})();
