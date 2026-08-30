/**
 * Story phone: one active scene at a time + caption sync.
 * CSS owns in-scene 18s motion; JS owns which scene is visible (no stacked fades).
 */
(function () {
  'use strict';

  var LOOP_MS = 18000;
  // Windows aligned with css/components/demo-app-phone.css keyframe beats.
  var SCENES = [
    { id: 'home', from: 0, to: 0.16 },
    { id: 'camera', from: 0.16, to: 0.32 },
    { id: 'process', from: 0.32, to: 0.5 },
    { id: 'checkin', from: 0.5, to: 0.74 },
    { id: 'outcome', from: 0.74, to: 0.92 },
  ];
  var CAPTION_AT = [0, 0.16, 0.32, 0.5, 0.74];

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
    }, 160);
  }

  function captionIndex(progress) {
    var idx = 0;
    for (var i = 0; i < CAPTION_AT.length; i++) {
      if (progress >= CAPTION_AT[i]) idx = i;
    }
    return idx;
  }

  function sceneIdFor(progress) {
    for (var i = 0; i < SCENES.length; i++) {
      if (progress >= SCENES[i].from && progress < SCENES[i].to) {
        return SCENES[i].id;
      }
    }
    return 'home';
  }

  function setActiveScene(root, id) {
    if (root.getAttribute('data-active-scene') === id) return;
    root.setAttribute('data-active-scene', id);
    root.querySelectorAll('[data-story-scene]').forEach(function (scene) {
      var on = scene.getAttribute('data-story-scene') === id;
      scene.classList.toggle('is-active', on);
      scene.setAttribute('aria-hidden', on ? 'false' : 'true');
    });
  }

  function initStoryPhone(root) {
    if (!root || root.getAttribute('data-jcp-story-ready') === '1') return;
    root.setAttribute('data-jcp-story-ready', '1');

    var caption = root.querySelector('[data-jcp-story-caption]');
    var captions = caption ? parseCaptions(caption) : [];
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var start = 0;
    var elapsed = 0;
    var raf = 0;
    var visible = true;
    var lastCaption = -1;

    function tick(now) {
      if (!visible || reduce) {
        raf = 0;
        return;
      }
      elapsed = (now - start) % LOOP_MS;
      var progress = elapsed / LOOP_MS;
      setActiveScene(root, sceneIdFor(progress));

      if (captions.length) {
        var idx = captionIndex(progress);
        if (idx !== lastCaption) {
          lastCaption = idx;
          setCaption(caption, captions[idx] || captions[0]);
        }
      }
      raf = window.requestAnimationFrame(tick);
    }

    function play() {
      if (reduce || raf) return;
      start = performance.now() - elapsed;
      root.classList.remove('is-paused');
      raf = window.requestAnimationFrame(tick);
    }

    function pause() {
      if (raf) {
        window.cancelAnimationFrame(raf);
        raf = 0;
      }
      elapsed = (performance.now() - start) % LOOP_MS;
      if (elapsed < 0) elapsed = 0;
      root.classList.add('is-paused');
    }

    if (reduce) {
      setActiveScene(root, 'checkin');
      if (caption && captions.length) {
        setCaption(caption, captions[Math.min(3, captions.length - 1)]);
      }
      root.classList.add('is-reduced');
      return;
    }

    setActiveScene(root, 'home');
    elapsed = 0;
    start = performance.now();

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
