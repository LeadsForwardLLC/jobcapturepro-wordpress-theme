/**
 * Campaign landing interactions — lightweight scroll reveal only.
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

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  ready(revealOnScroll);
})();
