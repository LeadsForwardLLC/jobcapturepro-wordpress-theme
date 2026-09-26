/**
 * JobCapturePro PostHog helper (marketing site).
 * Sends canonical events to project 593169 via the public capture API and
 * persists a cross-subdomain distinct_id so app.jobcapturepro.com can continue
 * the same person when bootstrapped with ph_distinct_id.
 */
(function () {
  'use strict';

  var cfg = window.JCP_POSTHOG || {};
  var API_KEY = cfg.apiKey || '';
  var API_HOST = String(cfg.apiHost || 'https://us.i.posthog.com').replace(/\/$/, '');
  var COOKIE_NAME = 'jcp_ph_id';
  var LS_KEY = 'jcp_ph_distinct_id';
  var QUEUE_KEY = 'jcp_ph_queue_v1';

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

  function setCookie(name, value) {
    try {
      var host = String(location.hostname || '');
      var domain = host.indexOf('jobcapturepro.com') !== -1 ? '; domain=.jobcapturepro.com' : '';
      document.cookie =
        name +
        '=' +
        encodeURIComponent(value) +
        '; path=/' +
        domain +
        '; max-age=31536000; SameSite=Lax; Secure';
    } catch (e) {}
  }

  function getDistinctId() {
    var id = '';
    try {
      id = getCookie(COOKIE_NAME) || localStorage.getItem(LS_KEY) || '';
    } catch (e) {}
    if (!id || id.length < 8) {
      id = uuid();
    }
    try {
      localStorage.setItem(LS_KEY, id);
    } catch (e2) {}
    setCookie(COOKIE_NAME, id);
    // Align with GTM PostHog when present (do not create a new person).
    try {
      if (window.posthog && typeof window.posthog.get_distinct_id === 'function') {
        var existing = String(window.posthog.get_distinct_id() || '');
        if (existing && existing.length > 8) {
          id = existing;
          localStorage.setItem(LS_KEY, id);
          setCookie(COOKIE_NAME, id);
        } else if (typeof window.posthog.identify === 'function') {
          // Keep anonymous continuity via register/bootstrap-style assign when supported.
        }
      }
    } catch (e3) {}
    return id;
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
    var url = API_HOST + '/i/v0/e/?ip=1';
    var json = JSON.stringify(body);
    try {
      if (typeof navigator !== 'undefined' && typeof navigator.sendBeacon === 'function') {
        var blob = new Blob([json], { type: 'application/json' });
        if (navigator.sendBeacon(url, blob)) return;
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
    } catch (e2) {}
  }

  function capture(name, props) {
    if (!name || !API_KEY) return;
    var distinctId = getDistinctId();
    var properties = scrub(Object.assign({}, attributionExtras(), props || {}));
    properties.distinct_id = distinctId;
    properties.$lib = 'jcp-posthog';
    properties.$lib_version = '1.0.1';
    if (typeof location !== 'undefined') {
      if (!properties.$current_url) properties.$current_url = location.href;
      if (!properties.$host) properties.$host = location.host;
      if (!properties.$pathname) properties.$pathname = location.pathname;
    }

    // Always send via the public capture API so events are not dependent on GTM
    // PostHog SDK readiness / filtering. Optionally mirror into the live SDK.
    sendBeaconOrFetch({
      api_key: API_KEY,
      event: name,
      properties: properties,
      timestamp: new Date().toISOString(),
    });
    try {
      if (window.posthog && typeof window.posthog.capture === 'function' && window.posthog.__loaded) {
        window.posthog.capture(name, properties);
      }
    } catch (ePh) {}
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
  };

  // Warm cookie/localStorage early.
  try {
    getDistinctId();
  } catch (eWarm) {}
})();
