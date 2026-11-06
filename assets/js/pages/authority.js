/**
 * Authority scoreboard — count-up stats on scroll.
 * Respects prefers-reduced-motion.
 */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function formatValue(n, decimals, format) {
    var v = decimals > 0 ? n.toFixed(decimals) : String(Math.round(n));
    if (format === 'comma') {
      return v.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    return v;
  }

  function animateEl(el) {
    var to = parseFloat(el.getAttribute('data-count-to') || '0');
    if (!isFinite(to) || to < 0) return;
    var prefix = el.getAttribute('data-count-prefix') || '';
    var suffix = el.getAttribute('data-count-suffix') || '';
    var format = el.getAttribute('data-count-format') || 'plain';
    var decimals = Math.max(0, parseInt(el.getAttribute('data-count-decimals') || '0', 10) || 0);
    var duration = Math.max(600, parseInt(el.getAttribute('data-count-ms') || '1400', 10) || 1400);

    if (reduced) {
      el.textContent = prefix + formatValue(to, decimals, format) + suffix;
      el.classList.add('is-complete');
      return;
    }

    var start = null;
    function frame(ts) {
      if (start === null) start = ts;
      var t = Math.min(1, (ts - start) / duration);
      var eased = 1 - Math.pow(1 - t, 3);
      var current = to * eased;
      el.textContent = prefix + formatValue(current, decimals, format) + suffix;
      if (t < 1) {
        requestAnimationFrame(frame);
      } else {
        el.textContent = prefix + formatValue(to, decimals, format) + suffix;
        el.classList.add('is-complete');
      }
    }
    requestAnimationFrame(frame);
  }

  function boot() {
    var nodes = document.querySelectorAll('.jcp-count-up[data-count-to]');
    if (!nodes.length) return;

    if (reduced || !('IntersectionObserver' in window)) {
      nodes.forEach(animateEl);
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          animateEl(entry.target);
          io.unobserve(entry.target);
        });
      },
      { threshold: 0.35, rootMargin: '0px 0px -8% 0px' }
    );

    nodes.forEach(function (el) {
      io.observe(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
