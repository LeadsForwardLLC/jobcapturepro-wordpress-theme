/**
 * Campaign landing — smooth #apply scroll + mobile sticky CTA visibility.
 */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function preferSmooth() {
    return !reduceMotion;
  }

  function scrollToApply(e) {
    var link = e.target.closest('a[href="#apply"], a[href$="#apply"]');
    if (!link) return;
    var apply = document.getElementById('apply');
    if (!apply) return;
    e.preventDefault();
    apply.scrollIntoView({ behavior: preferSmooth() ? 'smooth' : 'auto', block: 'start' });
    if (history && history.replaceState) {
      history.replaceState(null, '', '#apply');
    }
  }

  document.addEventListener('click', scrollToApply);

  var sticky = document.querySelector('.jcp-campaign-sticky-cta');
  var apply = document.getElementById('apply');
  if (!sticky || !apply || !('IntersectionObserver' in window)) {
    return;
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
          sticky.classList.add('is-hidden');
          sticky.setAttribute('aria-hidden', 'true');
        } else {
          sticky.classList.remove('is-hidden');
          sticky.setAttribute('aria-hidden', 'false');
        }
      });
    },
    { threshold: [0, 0.35, 0.6], rootMargin: '0px 0px -10% 0px' }
  );

  observer.observe(apply);
})();
