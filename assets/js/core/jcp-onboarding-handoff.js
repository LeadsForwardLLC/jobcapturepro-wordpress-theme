(() => {
  const ONB_HOST = 'app.jobcapturepro.com';
  const ONB_PATH = '/onboarding';

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
      return window.localStorage ? window.localStorage.getItem('jcp_demo_session_id') : null;
    } catch (e) {
      return null;
    }
  };

  const normalizeIndustryId = (raw) => {
    const val = (raw || '').toString().trim().toLowerCase();
    if (!val) return '';

    // Allowed values inferred from the app's Step 2 <select>.
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

    // Direct match.
    if (allowed.has(val)) return val;

    // Common legacy/demo values → app ids.
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

    // Basic slugify attempt (for labels like "Cleaning Services").
    const slug = val
      .replace(/['"]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    return allowed.has(slug) ? slug : '';
  };

  const buildHandoffParams = () => {
    const u = readDemoUser();
    if (!u) return null;

    const params = {};
    const first = (u.firstName || '').trim();
    const last = (u.lastName || '').trim();
    const email = (u.email || '').trim();
    const company = (u.businessName || '').trim();
    const businessType = (u.niche || '').trim();
    const fullName = [first, last].filter(Boolean).join(' ').trim();

    // Account step
    if (first) params.first_name = first;
    if (last) params.last_name = last;
    if (email) params.email = email;
    if (fullName) {
      params.full_name = fullName; // legacy / snake_case
      params.fullName = fullName;  // likely app key
      params.name = fullName;      // compatibility
    }

    // Org step
    if (company) {
      params.company = company;                 // legacy
      params.organization_name = company;       // snake_case
      params.organizationName = company;        // likely app key
      params.organizationName = company;        // explicit: matches Step 2 input id/key
    }
    if (businessType) {
      params.business_type = businessType;      // legacy
      params.industry = businessType;           // likely app label
      params.service_industry = businessType;   // snake_case variant
      params.serviceIndustry = businessType;    // camelCase variant

      const industryId = normalizeIndustryId(businessType);
      if (industryId) {
        params.industryId = industryId;         // explicit: matches Step 2 select id/key
        params.industry_id = industryId;        // snake_case variant
      }
    }

    const demoSession = readDemoSession();
    if (demoSession) params.demo_session = demoSession;

    return Object.keys(params).length ? params : null;
  };

  const isOnboardingUrl = (href) => {
    if (!href || typeof href !== 'string') return false;
    if (!href.includes(ONB_PATH)) return false;
    if (href.startsWith('http')) {
      try {
        const u = new URL(href);
        return u.hostname === ONB_HOST && u.pathname === ONB_PATH;
      } catch (e) {
        return false;
      }
    }
    // allow relative/on-site rewritten URLs that still contain /onboarding
    return href.includes(ONB_PATH);
  };

  const decorateHref = (href, extraParams) => {
    try {
      const base =
        typeof window !== 'undefined' && window.JCP_ONBOARDING && window.JCP_ONBOARDING.url
          ? window.JCP_ONBOARDING.url
          : href;
      const u = base.startsWith('http') ? new URL(href) : new URL(href, window.location.origin);
      Object.keys(extraParams).forEach((k) => {
        if (!u.searchParams.has(k)) u.searchParams.set(k, String(extraParams[k]));
      });
      return u.toString();
    } catch (e) {
      return href;
    }
  };

  const decorateAll = () => {
    const extra = buildHandoffParams();
    if (!extra) return;

    const links = Array.from(document.querySelectorAll('a[href]'));
    links.forEach((a) => {
      const href = a.getAttribute('href') || '';
      if (!isOnboardingUrl(href)) return;
      const next = decorateHref(href, extra);
      if (next && next !== href) a.setAttribute('href', next);
    });
  };

  // Re-apply on click in case localStorage was written after initial decorate.
  document.addEventListener(
    'click',
    (event) => {
      const a = event.target && event.target.closest ? event.target.closest('a[href]') : null;
      if (!a) return;
      const href = a.getAttribute('href') || '';
      if (!isOnboardingUrl(href)) return;
      const extra = buildHandoffParams();
      if (!extra) return;
      const next = decorateHref(href, extra);
      if (next && next !== href) a.setAttribute('href', next);
    },
    true
  );

  // Templates can render after DOMContentLoaded; run a few times.
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
})();

