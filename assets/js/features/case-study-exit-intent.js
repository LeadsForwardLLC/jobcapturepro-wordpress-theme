/**
 * Case-study exit-intent (last-resort).
 * Desktop: mouse-leave. Mobile + demo: Exit/close / back. Interrupt: BEFORE YOU GO + hand.
 */
(function () {
  'use strict';

  var cfg = window.JCP_CASE_STUDY || {};
  var STORAGE_KEY = 'jcp_case_study_exit_intent';
  var shownKey = STORAGE_KEY + '_shown';
  var dismissKey = STORAGE_KEY + '_dismissed';
  var allowDemo = Boolean(cfg.allowDemo);
  var minDesktop = Number(cfg.minWidth || 1024);

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
    return window.matchMedia && window.matchMedia('(min-width: ' + minDesktop + 'px)').matches;
  }

  function isDemoPath() {
    return /\/demo\/?/i.test(location.pathname || '');
  }

  function isBlocked() {
    if (already(dismissKey) || already(shownKey)) return true;
    if (document.body.classList.contains('survey-only')) return true;
    if (isDemoPath() && !allowDemo) return true;
    if (document.body.classList.contains('jcp-guided-demo') && !allowDemo) return true;
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

  /**
   * Credibility-safe scarcity: admin fill % is marketing-edited, not live app counts.
   * Do not show fabricated percentages / progress bars.
   */
  function buildCapacityHtml() {
    var selecting = Number(cfg.selectingTotal || 10);
    return (
      '<div class="jcp-case-capacity jcp-case-capacity--modal jcp-case-exit__capacity" role="status">' +
      '<div class="jcp-case-capacity__head">' +
      '<p class="jcp-case-capacity__label"><strong>Only ' +
      selecting +
      ' businesses will be selected.</strong></p>' +
      '</div></div>'
    );
  }

  function handSvg() {
    return (
      '<svg class="jcp-case-exit__hand-icon" viewBox="0 0 64 64" width="56" height="56" aria-hidden="true" focusable="false">' +
      '<circle cx="32" cy="32" r="32" fill="#fff1ee"/>' +
      '<path fill="#ff503e" d="M38.2 14.2c-1.3 0-2.4 1-2.4 2.4v11.2h-1.6V11.8c0-1.3-1.1-2.4-2.4-2.4s-2.4 1.1-2.4 2.4v15.9h-1.6V14.6c0-1.3-1.1-2.4-2.4-2.4s-2.4 1.1-2.4 2.4v16.2h-1.6V18.8c0-1.3-1.1-2.4-2.4-2.4s-2.4 1.1-2.4 2.4v20.3c0 7.2 4.4 12.7 12.1 12.7 5.9 0 10.4-3.4 12.1-8.9l2.8-9.1c.5-1.6-.4-3.3-2-3.8-1-.3-2-.1-2.7.5V16.6c0-1.3-1.1-2.4-2.4-2.4z"/>' +
      '</svg>'
    );
  }

  function dismissLabel() {
    if (isDemoPath() || document.body.classList.contains('jcp-guided-demo') || allowDemo) {
      return 'No thanks — continue the demo';
    }
    return 'No thanks — continue browsing';
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

  function open(source) {
    if (isBlocked()) return false;
    if (!once(shownKey)) return false;

    var root = document.createElement('div');
    root.className = 'jcp-case-exit is-open';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-labelledby', 'jcpCaseExitTitle');
    root.innerHTML =
      '<div class="jcp-case-exit__backdrop" data-case-exit-dismiss="1"></div>' +
      '<div class="jcp-case-exit__card">' +
      '<button type="button" class="jcp-case-exit__close" aria-label="Close" data-case-exit-dismiss="1">×</button>' +
      '<div class="jcp-case-exit__interrupt">' +
      handSvg() +
      '<p class="jcp-case-exit__wait" id="jcpCaseExitTitle">BEFORE YOU GO</p>' +
      '</div>' +
      '<h2 class="jcp-case-exit__title">Want to use JobCapturePro free for 90 days?</h2>' +
      '<p class="jcp-case-exit__body">We\'re selecting 10 home-service companies for hands-on onboarding and a 90-day results study. Selected businesses use JobCapturePro free during the study.</p>' +
      buildCapacityHtml() +
      '<div class="jcp-case-exit__actions">' +
      '<a class="jcp-case-exit__primary" id="jcpCaseExitApply" href="' +
      String(cfg.url || '/case-study/') +
      '">Apply for a Case Study Spot →</a>' +
      '<button type="button" class="jcp-case-exit__dismiss" data-case-exit-dismiss="1">' +
      dismissLabel() +
      '</button>' +
      '</div></div>';

    document.body.appendChild(root);
    document.body.classList.add('jcp-case-exit-open');
    pushEvent('CaseStudyExitIntentShown', {
      fill_percent: cfg.fillPercent,
      selecting_total: cfg.selectingTotal,
      page_path: location.pathname,
      source: source || 'mouseleave',
      viewport: isDesktop() ? 'desktop' : 'mobile',
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
          fill_percent: cfg.fillPercent,
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
    return true;
  }

  window.JCPCaseStudyExit = {
    open: open,
    isBlocked: isBlocked,
  };

  var armed = false;
  var delay = Number(cfg.delayMs || 18000);

  setTimeout(function () {
    armed = true;
  }, delay);

  // Desktop classic exit-intent.
  document.addEventListener('mouseout', function (e) {
    if (!armed || !isDesktop() || isBlocked()) return;
    if (e.relatedTarget || e.toElement) return;
    if (typeof e.clientY === 'number' && e.clientY > 12) return;
    open('mouseleave');
  });

  // Demo: Exit demo / close outcomes (desktop + mobile).
  if (allowDemo) {
    document.addEventListener(
      'click',
      function (e) {
        if (!armed || isBlocked()) return;
        var t = e.target;
        if (!(t instanceof Element)) return;
        var exitBtn = t.closest('#mobileDemoExit, button[aria-label="Exit demo"], a[aria-label="Exit demo"]');
        var outcomesClose = t.closest(
          '#demoOutcomesModalClose, #demoOutcomesModalBackdrop, .demo-outcomes-modal__close'
        );
        if (!exitBtn && !outcomesClose) return;
        if (open(exitBtn && !outcomesClose ? 'demo_exit' : 'outcomes_close')) {
          e.preventDefault();
          e.stopPropagation();
        }
      },
      true
    );
  }

  // Mobile / tablet: intercept one back gesture after arm delay.
  if (!isDesktop()) {
    try {
      var histKey = STORAGE_KEY + '_hist';
      if (!sessionStorage.getItem(histKey)) {
        history.pushState({ jcpCaseExit: 1 }, '', location.href);
        sessionStorage.setItem(histKey, '1');
      }
    } catch (e) {}

    window.addEventListener('popstate', function () {
      if (!armed || isBlocked()) return;
      if (open('mobile_back')) {
        try {
          history.pushState({ jcpCaseExit: 1 }, '', location.href);
        } catch (err) {}
      }
    });
  }
})();
