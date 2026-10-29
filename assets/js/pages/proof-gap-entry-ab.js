/**
 * Proof Gap entry A/B — first-party assignment (proof_gap_entry_v1).
 * Must run before paint; excluded from WP Rocket delay/defer/minify.
 * Canonical: cookie/localStorage jcp_pg_entry_variant = control | direct_question.
 * PostHog must NOT assign.
 */
(function () {
  'use strict';
  var COOKIE = 'jcp_pg_entry_variant';
  var LS = 'jcp_pg_entry_variant';
  var DAYS = 30;
  var VALID = { control: 1, direct_question: 1 };

  function readCookie(name) {
    try {
      var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : '';
    } catch (e) {
      return '';
    }
  }

  function writeCookie(name, value) {
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
        DAYS * 24 * 60 * 60 +
        '; SameSite=Lax; Secure';
    } catch (e) {}
  }

  function readLs() {
    try {
      return localStorage.getItem(LS) || '';
    } catch (e) {
      return '';
    }
  }

  function writeLs(value) {
    try {
      localStorage.setItem(LS, value);
    } catch (e) {}
  }

  function forcedFromUrl() {
    try {
      var q = new URLSearchParams(location.search);
      var v = (q.get('pg_variant') || '').trim();
      return VALID[v] ? v : '';
    } catch (e) {
      return '';
    }
  }

  function assignStable() {
    try {
      if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
        var buf = new Uint8Array(1);
        window.crypto.getRandomValues(buf);
        return buf[0] < 128 ? 'control' : 'direct_question';
      }
    } catch (e) {}
    return Math.random() < 0.5 ? 'control' : 'direct_question';
  }

  function resolve() {
    var forced = forcedFromUrl();
    if (forced) {
      writeCookie(COOKIE, forced);
      writeLs(forced);
      return forced;
    }
    var fromCookie = readCookie(COOKIE);
    if (VALID[fromCookie]) {
      writeLs(fromCookie);
      return fromCookie;
    }
    var fromLs = readLs();
    if (VALID[fromLs]) {
      writeCookie(COOKIE, fromLs);
      return fromLs;
    }
    var next = assignStable();
    writeCookie(COOKIE, next);
    writeLs(next);
    return next;
  }

  var variant = resolve();
  try {
    document.documentElement.setAttribute('data-pg-entry-variant', variant);
    document.documentElement.setAttribute('data-jcp-lp-variant', variant);
    document.documentElement.classList.remove('pg-ab-pending');
    document.documentElement.classList.add('pg-ab-ready');
    if (document.body) {
      document.body.setAttribute('data-pg-entry-variant', variant);
      document.body.setAttribute('data-jcp-lp-variant', variant);
    } else {
      document.addEventListener('DOMContentLoaded', function () {
        if (document.body) {
          document.body.setAttribute('data-pg-entry-variant', variant);
          document.body.setAttribute('data-jcp-lp-variant', variant);
        }
      });
    }
  } catch (eSet) {}

  window.JCP_PG_ENTRY = {
    experiment: 'proof_gap_entry_v1',
    variant: variant,
    funnelVersion: 'proof_gap_survey_v1',
  };
})();
