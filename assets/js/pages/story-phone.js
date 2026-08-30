/**
 * Story phone caption sync + pause when offscreen / reduced motion.
 */
(function () {
  'use strict';

  var LOOP_MS = 18000;
  var CAPTION_AT = [0, 0.18, 0.34, 0.52, 0.74];

  function parseCaptions(el) {
    var raw = el.getAttribute('data-captions') || '[]';
    try {
      var parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
    } catch (e) {
      return [];
    }
  }

  function setCaption(el, text) {
    if (!el || el.textContent === text) return;
    el.classList.add('is-swapping');
    window.setTimeout(function () {
      el.textContent = text;
      el.classList.remove('is-swapping');
    }, 180);
  }

  function captionIndex(progress) {
    var idx = 0;
    for (var i = 0; i < CAPTION_AT.length; i++) {
      if (progress >= CAPTION_AT[i]) idx = i;
    }
    return idx;
  }

  function initStoryPhone(root) {
    if (!root || root.getAttribute('data-jcp-story-ready') === '1') return;
    root.setAttribute('data-jcp-story-ready', '1');

    var caption = root.querySelector('[data-jcp-story-caption]');
    var captions = caption ? parseCaptions(caption) : [];
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var start = performance.now();
    var raf = 0;
    var visible = true;
    var lastIdx = -1;

    function tick(now) {
      if (!visible || reduce || captions.length === 0) {
        raf = 0;
        return;
      }
      var progress = ((now - start) % LOOP_MS) / LOOP_MS;
      var idx = captionIndex(progress);
      if (idx !== lastIdx) {
        lastIdx = idx;
        setCaption(caption, captions[idx] || captions[0]);
      }
      raf = window.requestAnimationFrame(tick);
    }

    function play() {
      if (reduce || raf) return;
      start = performance.now() - ((performance.now() - start) % LOOP_MS);
      raf = window.requestAnimationFrame(tick);
      root.classList.remove('is-paused');
    }

    function pause() {
      if (raf) {
        window.cancelAnimationFrame(raf);
        raf = 0;
      }
      root.classList.add('is-paused');
    }

    if (reduce && caption && captions.length) {
      setCaption(caption, captions[Math.min(3, captions.length - 1)]);
      root.classList.add('is-reduced');
      return;
    }

    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            visible = entry.isIntersecting && entry.intersectionRatio > 0.2;
            if (visible) play();
            else pause();
          });
        },
        { threshold: [0, 0.2, 0.5] }
      );
      io.observe(root);
    } else {
      play();
    }

    document.addEventListener(
      'visibilitychange',
      function () {
        if (document.hidden) pause();
        else if (visible) play();
      },
      { passive: true }
    );
  }

  function boot() {
    document.querySelectorAll('[data-jcp-story-phone]').forEach(initStoryPhone);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
