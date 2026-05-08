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

  const buildHandoffParams = () => {
    const u = readDemoUser();
    if (!u) return null;

    const params = {};
    const first = (u.firstName || '').trim();
    const last = (u.lastName || '').trim();
    const email = (u.email || '').trim();
    const company = (u.businessName || '').trim();
    const businessType = (u.niche || '').trim();

    if (first) params.first_name = first;
    if (last) params.last_name = last;
    if (email) params.email = email;
    if (company) params.company = company;
    if (businessType) params.business_type = businessType;

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

