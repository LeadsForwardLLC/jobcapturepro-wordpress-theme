/**
 * JobCapturePro PostHog helper (marketing site).
 * Sends canonical events to project 593169 via the public capture API and
 * persists a cross-subdomain distinct_id so app.jobcapturepro.com can continue
 * the same person when bootstrapped with ph_distinct_id.
 *
 * API key resolution (in order): window.JCP_POSTHOG.apiKey (head/inline),
 * JCP_POSTHOG_CFG (wp_localize_script), then hardcoded public project key.
 * Key is resolved at capture-time so Rocket cannot leave a null config freeze.
 */
(function () {
  'use strict';

  // Public project API key — same token as GTM / web SDK. Safe as a fallback
  // when Rocket strips handle-bound inline config.
  var DEFAULT_API_KEY = 'phc_v8emzqtZ8beAjLsqj2byb5fK8wRHbW2g6hXBqAEZPMyS';
  var DEFAULT_API_HOST = 'https://us.i.posthog.com';
  var COOKIE_NAME = 'jcp_ph_id';
  var LS_KEY = 'jcp_ph_distinct_id';
  var ATTR_COOKIE = 'jcp_attr_v1';
  var LIB_VERSION = '1.1.0';

  function resolveCfg() {
    var cfg = {};
    try {
      if (typeof JCP_POSTHOG_CFG !== 'undefined' && JCP_POSTHOG_CFG && typeof JCP_POSTHOG_CFG === 'object') {
        Object.assign(cfg, JCP_POSTHOG_CFG);
      }
    } catch (e0) {}
    try {
      if (window.JCP_POSTHOG && typeof window.JCP_POSTHOG === 'object') {
        Object.assign(cfg, window.JCP_POSTHOG);
      }
    } catch (e1) {}
    if (!cfg.apiKey) cfg.apiKey = DEFAULT_API_KEY;
    if (!cfg.apiHost) cfg.apiHost = DEFAULT_API_HOST;
    window.JCP_POSTHOG = cfg;
    return cfg;
  }

  function resolveApiKey() {
    return String(resolveCfg().apiKey || DEFAULT_API_KEY);
  }

  function resolveApiHost() {
    return String(resolveCfg().apiHost || DEFAULT_API_HOST).replace(/\/$/, '');
  }

  function uuid() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = (Math.random() * 16) | 0;
      var v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  function getCookie(name) {
    try {
      var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : '';
    } catch (e) {
      return '';
    }
  }

  function setCookie(name, value, maxAge) {
    try {
      var host = String(location.hostname || '');
      var domain = host.indexOf('jobcapturepro.com') !== -1 ? '; domain=.jobcapturepro.com' : '';
      document.cookie =
        name +
        '=' +
        encodeURIComponent(value) +
        '; path=/' +
        domain +
        '; max-age=' +
        (maxAge || 31536000) +
        '; SameSite=Lax; Secure';
    } catch (e) {}
  }

  function getDistinctId() {
    var id = '';
    try {
      id = getCookie(COOKIE_NAME) || localStorage.getItem(LS_KEY) || '';
    } catch (e) {}

    // Sticky: once we have a marketing-site id, never replace it mid-funnel.
    if (id && id.length >= 8) {
      try {
        localStorage.setItem(LS_KEY, id);
      } catch (e2) {}
      setCookie(COOKIE_NAME, id);
      return id;
    }

    // First visit only: adopt GTM PostHog distinct_id when already present.
    try {
      if (window.posthog && typeof window.posthog.get_distinct_id === 'function') {
        var existing = String(window.posthog.get_distinct_id() || '');
        if (existing && existing.length > 8) id = existing;
      }
    } catch (e3) {}

    if (!id || id.length < 8) id = uuid();
    try {
      localStorage.setItem(LS_KEY, id);
    } catch (e4) {}
    setCookie(COOKIE_NAME, id);
    return id;
  }

  function isQaTraffic(props) {
    try {
      var q = new URLSearchParams(location.search);
      var trace = (props && props.qa_trace_id) || q.get('qa_trace_id') || '';
      if (trace && /^qa[_-]/i.test(String(trace))) return true;
      if (q.get('jcp_qa') === '1' || q.get('jcp_internal') === '1') return true;
      var host = String(location.hostname || '');
      if (host.indexOf('local') !== -1 || host.indexOf('localhost') !== -1) return true;
    } catch (e) {}
    return false;
  }

  function attributionExtras() {
    var out = {};
    try {
      if (window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function') {
        Object.assign(out, window.JCPLeadAttribution.getPayload() || {});
      }
    } catch (e) {}
    var fbp = getCookie('_fbp');
    var fbc = getCookie('_fbc');
    if (fbp) out._fbp = fbp;
    if (fbc) out._fbc = fbc;
    return out;
  }

  /**
   * Persist paid UTMs on .jobcapturepro.com so app subdomain can read them
   * even if URL params are stripped mid-flow.
   */
  function persistAttrCookie(extras) {
    try {
      var keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'lp_variant', 'qa_trace_id', 'landing_page'];
      var payload = {};
      keys.forEach(function (k) {
        if (extras[k]) payload[k] = String(extras[k]).slice(0, 512);
      });
      payload.ph_distinct_id = getDistinctId();
      if (Object.keys(payload).length < 2) return;
      setCookie(ATTR_COOKIE, JSON.stringify(payload), 60 * 60 * 24 * 30);
    } catch (e) {}
  }

  function scrub(props) {
    var payload = Object.assign({}, props || {});
    ['email', 'phone', 'first_name', 'last_name', 'business_name', 'password', 'fbclid', 'ttclid'].forEach(function (k) {
      delete payload[k];
    });
    Object.keys(payload).forEach(function (k) {
      if (payload[k] === undefined || payload[k] === null || payload[k] === '') delete payload[k];
    });
    return payload;
  }

  function sendBeaconOrFetch(body) {
    var url = resolveApiHost() + '/i/v0/e/?ip=1';
    var json = JSON.stringify(body);
    try {
      if (typeof navigator !== 'undefined' && typeof navigator.sendBeacon === 'function') {
        var blob = new Blob([json], { type: 'application/json' });
        if (navigator.sendBeacon(url, blob)) return true;
      }
    } catch (e) {}
    try {
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: json,
        keepalive: true,
        mode: 'cors',
        credentials: 'omit',
      }).catch(function () {});
      return true;
    } catch (e2) {
      return false;
    }
  }

  function capture(name, props) {
    var API_KEY = resolveApiKey();
    if (!name || !API_KEY) return;
    var distinctId = getDistinctId();
    var extras = attributionExtras();
    persistAttrCookie(extras);
    var properties = scrub(Object.assign({}, extras, props || {}));
    properties.distinct_id = distinctId;
    properties.$lib = 'jcp-posthog';
    properties.$lib_version = LIB_VERSION;
    if (isQaTraffic(properties)) {
      properties.is_qa = true;
      properties.$set = Object.assign({}, properties.$set || {}, { is_qa: true });
    }
    if (typeof location !== 'undefined') {
      if (!properties.$current_url) properties.$current_url = location.href;
      if (!properties.$host) properties.$host = location.host;
      if (!properties.$pathname) properties.$pathname = location.pathname;
    }

    sendBeaconOrFetch({
      api_key: API_KEY,
      event: name,
      properties: properties,
      timestamp: new Date().toISOString(),
    });
  }

  function register(extra) {
    try {
      if (window.posthog && typeof window.posthog.register === 'function') {
        window.posthog.register(scrub(Object.assign({}, attributionExtras(), extra || {})));
      }
    } catch (e) {}
  }

  window.JCPPostHog = {
    capture: capture,
    register: register,
    getDistinctId: getDistinctId,
    isQaTraffic: function () {
      return isQaTraffic({});
    },
  };

  // Warm cookie/localStorage + ensure config object exists early.
  try {
    resolveCfg();
    getDistinctId();
  } catch (eWarm) {}
})();
