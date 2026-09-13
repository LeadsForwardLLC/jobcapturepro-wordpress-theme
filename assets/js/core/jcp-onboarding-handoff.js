/**
 * App onboarding link decorator — merges demo user + paid attribution into trial URLs.
 *
 * Paid acquisition UTMs (utm_*, fbclid, lp_variant) overwrite marketing-site defaults
 * (jobcapturepro.com / website / onboarding). Internal CTA surface is stored as jcp_surface.
 */
(() => {
  const ONB_HOST = 'app.jobcapturepro.com';
  const ONB_PATH = '/onboarding';

  const MARKETING_UTM_DEFAULTS = {
    utm_source: 'jobcapturepro.com',
    utm_medium: 'website',
    utm_campaign: 'onboarding',
  };

  const PAID_ATTR_KEYS = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'fbclid',
    'lp_variant',
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
      // Writers use sessionStorage; fall back to localStorage for older sessions.
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
    return {};
  };

  const isMarketingDefault = (key, value) => {
    const def = MARKETING_UTM_DEFAULTS[key];
    if (!def) return false;
    return String(value || '') === def;
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
      'landing_page',
      'referrer',
      'contact_id',
    ].forEach((key) => {
      const val = attr[key];
      if (val != null && String(val).trim() !== '') {
        params[key] = String(val).trim();
      }
    });

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
   * Paid acquisition keys always overwrite marketing defaults.
   */
  const decorateHref = (href, extraParams, surface) => {
    try {
      const u = href.startsWith('http') ? new URL(href) : new URL(href, window.location.origin);

      // Preserve existing non-default surface utm_content into jcp_surface before overwrites.
      const existingContent = u.searchParams.get('utm_content') || '';
      if (surface) {
        u.searchParams.set('jcp_surface', String(surface));
      } else if (existingContent && !u.searchParams.get('jcp_surface')) {
        u.searchParams.set('jcp_surface', existingContent);
      }

      Object.keys(extraParams || {}).forEach((k) => {
        const val = extraParams[k];
        if (val === undefined || val === null || String(val).trim() === '') return;

        if (PAID_ATTR_KEYS.indexOf(k) !== -1) {
          const current = u.searchParams.get(k) || '';
          if (!current || isMarketingDefault(k, current) || k === 'fbclid' || k === 'lp_variant' || k === 'utm_term' || k === 'utm_content') {
            u.searchParams.set(k, String(val));
          }
          return;
        }

        // Non-attribution keys: only fill if missing (PII etc.).
        if (!u.searchParams.has(k)) {
          u.searchParams.set(k, String(val));
        }
      });

      return u.toString();
    } catch (e) {
      return href;
    }
  };

  const decorateAll = () => {
    const extra = buildHandoffParams();
    if (!extra) {
      // Still apply attribution-only decoration when no demoUser exists (paid LPs).
      const attrOnly = {};
      const attr = readAttribution();
      PAID_ATTR_KEYS.forEach((k) => {
        if (attr[k]) attrOnly[k] = attr[k];
      });
      if (!Object.keys(attrOnly).length) return;

      document.querySelectorAll('a[href]').forEach((a) => {
        const href = a.getAttribute('href') || '';
        if (!isOnboardingUrl(href)) return;
        const next = decorateHref(href, attrOnly);
        if (next && next !== href) a.setAttribute('href', next);
      });
      return;
    }

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
        if (attr[k]) extra[k] = attr[k];
      });
      if (!Object.keys(extra).length) return;
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
