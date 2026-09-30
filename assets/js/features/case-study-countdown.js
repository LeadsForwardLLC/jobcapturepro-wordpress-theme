/**
 * Live countdown for case-study application window (shared by capacity bar + exit modal).
 */
(function () {
  'use strict';

  function formatRemaining(ms) {
    if (ms <= 0) return 'Application window closed';
    var totalSec = Math.floor(ms / 1000);
    var days = Math.floor(totalSec / 86400);
    var hours = Math.floor((totalSec % 86400) / 3600);
    var mins = Math.floor((totalSec % 3600) / 60);
    var secs = totalSec % 60;
    if (days > 0) {
      return (
        'Closes in ' +
        days +
        'd ' +
        hours +
        'h ' +
        String(mins).padStart(2, '0') +
        'm'
      );
    }
    return (
      'Closes in ' +
      hours +
      'h ' +
      String(mins).padStart(2, '0') +
      'm ' +
      String(secs).padStart(2, '0') +
      's'
    );
  }

  function tickEl(el) {
    if (!el) return;
    var ts = Number(el.getAttribute('data-jcp-case-countdown') || el.getAttribute('data-closes-at') || 0);
    if (!ts) return;
    var ms = ts * 1000 - Date.now();
    el.textContent = formatRemaining(ms);
    el.classList.toggle('is-closed', ms <= 0);
  }

  function refreshAll() {
    document.querySelectorAll('[data-jcp-case-countdown]').forEach(tickEl);
  }

  window.JCPCaseStudyCountdown = {
    formatRemaining: formatRemaining,
    tickEl: tickEl,
    refreshAll: refreshAll,
  };

  refreshAll();
  setInterval(refreshAll, 1000);
})();
