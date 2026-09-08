/**
 * Desktop-only exit-intent for the 90-day case study (last-resort).
 * Does not run on /demo/ or when a form field is focused.
 */
(function () {
  'use strict';

  var cfg = window.JCP_CASE_STUDY || {};
  var STORAGE_KEY = 'jcp_case_study_exit_intent';
  var shownKey = STORAGE_KEY + '_shown';
  var dismissKey = STORAGE_KEY + '_dismissed';

  function once(key) {
    try {
      if (sessionStorage.getItem(key)) return false;
      sessionStorage.setItem(key, '1');
      return true;
    } catch (e) {
      return true;
    }
  }

  function already(key) {
    try {
      return Boolean(sessionStorage.getItem(key));
    } catch (e) {
      return false;
    }
  }

  function isDesktop() {
    return window.matchMedia && window.matchMedia('(min-width: ' + (cfg.minWidth || 1024) + 'px)').matches;
  }

  function isBlocked() {
    if (!isDesktop()) return true;
    if (already(dismissKey) || already(shownKey)) return true;
    if (document.body.classList.contains('jcp-guided-demo')) return true;
    if (document.body.classList.contains('survey-only')) return true;
    if (/\/demo\/?/i.test(location.pathname)) return true;
    var active = document.activeElement;
    if (active && /^(INPUT|TEXTAREA|SELECT)$/i.test(active.tagName)) return true;
    return false;
  }

  function pushEvent(name, extra) {
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(Object.assign({ event: name }, extra || {}));
    } catch (e) {}
  }

  function buildCapacityHtml() {
    var claimed = Number(cfg.spotsClaimed || 0);
    var total = Number(cfg.spotsTotal || 10);
    var remaining = Math.max(0, total - claimed);
    var pct = total > 0 ? Math.round((claimed / total) * 100) : 0;
    var meta =
      remaining <= 0
        ? 'Cohort full · Applications still reviewed for waitlist'
        : remaining +
          (remaining === 1 ? ' spot remaining' : ' spots remaining') +
          ' · Application required — selection is not automatic';
    return (
      '<div class="jcp-case-capacity jcp-case-capacity--modal jcp-case-exit__capacity" role="status">' +
      '<div class="jcp-case-capacity__head">' +
      '<p class="jcp-case-capacity__label"><strong>' +
      claimed +
      ' of ' +
      total +
      ' companies selected</strong></p>' +
      '<p class="jcp-case-capacity__meta">' +
      meta +
      '</p></div>' +
      '<div class="jcp-case-capacity__track" aria-hidden="true">' +
      '<span class="jcp-case-capacity__fill" style="width:' +
      pct +
      '%"></span></div></div>'
    );
  }

  function close(root, dismissed) {
    if (!root) return;
    root.classList.remove('is-open');
    root.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('jcp-case-exit-open');
    if (dismissed) {
      try {
        sessionStorage.setItem(dismissKey, '1');
      } catch (e) {}
    }
  }

  function open() {
    if (isBlocked()) return;
    if (!once(shownKey)) return;

    var root = document.createElement('div');
    root.className = 'jcp-case-exit is-open';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-labelledby', 'jcpCaseExitTitle');
    root.innerHTML =
      '<div class="jcp-case-exit__backdrop" data-case-exit-dismiss="1"></div>' +
      '<div class="jcp-case-exit__card">' +
      '<button type="button" class="jcp-case-exit__close" aria-label="Close" data-case-exit-dismiss="1">×</button>' +
      '<p class="jcp-case-exit__eyebrow">Last open spots</p>' +
      '<h2 class="jcp-case-exit__title" id="jcpCaseExitTitle">Apply for the 90-day case study</h2>' +
      '<p class="jcp-case-exit__body">We are selecting 10 home-service companies for hands-on onboarding and measurable results. Selected companies receive JobCapturePro free during the study. Applying does not guarantee acceptance.</p>' +
      buildCapacityHtml() +
      '<div class="jcp-case-exit__actions">' +
      '<a class="jcp-case-exit__primary" id="jcpCaseExitApply" href="' +
      String(cfg.url || '/case-study/') +
      '">Apply for a case study spot</a>' +
      '<button type="button" class="jcp-case-exit__dismiss" data-case-exit-dismiss="1">No thanks — continue browsing</button>' +
      '</div></div>';

    document.body.appendChild(root);
    document.body.classList.add('jcp-case-exit-open');
    pushEvent('CaseStudyExitIntentShown', {
      spots_claimed: cfg.spotsClaimed,
      spots_total: cfg.spotsTotal,
      page_path: location.pathname,
    });

    root.addEventListener('click', function (e) {
      var t = e.target;
      if (!(t instanceof Element)) return;
      if (t.closest('[data-case-exit-dismiss]')) {
        close(root, true);
      }
    });

    var apply = document.getElementById('jcpCaseExitApply');
    if (apply) {
      apply.addEventListener('click', function () {
        pushEvent('CaseStudyCTAClicked', {
          source: 'exit_intent',
          spots_claimed: cfg.spotsClaimed,
          page_path: location.pathname,
        });
        try {
          sessionStorage.setItem(dismissKey, '1');
        } catch (e) {}
      });
    }

    document.addEventListener(
      'keydown',
      function onKey(e) {
        if (e.key === 'Escape') {
          close(root, true);
          document.removeEventListener('keydown', onKey);
        }
      },
      true
    );
  }

  var armed = false;
  var delay = Number(cfg.delayMs || 18000);

  setTimeout(function () {
    armed = true;
  }, delay);

  document.addEventListener('mouseout', function (e) {
    if (!armed || isBlocked()) return;
    if (e.relatedTarget || e.toElement) return;
    if (typeof e.clientY === 'number' && e.clientY > 12) return;
    open();
  });
})();
