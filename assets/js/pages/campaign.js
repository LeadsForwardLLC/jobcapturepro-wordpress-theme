/**
 * Campaign landing interactions — scroll reveal + sticky brand bar.
 * No heavy parallax; respects prefers-reduced-motion.
 */
(function () {
  'use strict';

  function revealOnScroll() {
    var nodes = document.querySelectorAll(
      '.jcp-page-campaign [data-jcp-reveal], .jcp-page-campaign .jcp-block-root, .jcp-page-campaign .jcp-niche-benefits--job-flow'
    );
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
      { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
    );

    nodes.forEach(function (el) {
      if (el.getAttribute('data-jcp-block-type') === 'hero') {
        el.classList.add('is-visible');
        return;
      }
      io.observe(el);
    });
  }

  function stickyBrandbar() {
    var bar = document.querySelector('[data-jcp-landing-brandbar]');
    if (!bar) return;

    var threshold = 36;
    var compact = false;

    function update() {
      var next = (window.scrollY || 0) > threshold;
      if (next === compact) return;
      compact = next;
      bar.classList.toggle('is-compact', compact);
    }

    // Don't force layout on boot — wait for first scroll.
    window.addEventListener('scroll', update, { passive: true });
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
    stickyBrandbar();
  });
})();
