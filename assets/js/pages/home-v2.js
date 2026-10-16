(function () {
  'use strict';

  function revealOnScroll() {
    var nodes = document.querySelectorAll('[data-jcp-reveal], .jcp-niche-benefits--job-flow');
    if (!nodes.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      nodes.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    if (!('IntersectionObserver' in window)) {
      nodes.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      },
      { rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
    );

    nodes.forEach(function (el) {
      io.observe(el);
    });
  }

  function bindLocalFalconCompare() {
    document.querySelectorAll('[data-jcp-lf-compare]').forEach(function (wrap) {
      var range = wrap.querySelector('[data-jcp-lf-range]');
      var reveal = wrap.querySelector('[data-jcp-lf-reveal]');
      if (!range || !reveal) return;
      var sync = function () {
        reveal.style.width = String(range.value) + '%';
      };
      range.addEventListener('input', sync);
      sync();
    });
  }

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  ready(function () {
    revealOnScroll();
    bindLocalFalconCompare();
  });
})();
