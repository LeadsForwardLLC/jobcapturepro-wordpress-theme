/**
 * Story moments — interactive publish + review beats for campaign LPs.
 */
(function () {
  'use strict';

  function showPanel(root, channel) {
    var panels = root.querySelectorAll('[data-channel-panel]');
    panels.forEach(function (panel) {
      var match = panel.getAttribute('data-channel-panel') === channel;
      panel.classList.toggle('is-active', match);
      if (match) {
        panel.removeAttribute('hidden');
      } else {
        panel.setAttribute('hidden', '');
      }
    });
  }

  function activateTile(root, tile) {
    if (!root || !tile) return;
    var channel = tile.getAttribute('data-channel') || 'website';

    root.querySelectorAll('.jcp-story-publish__tile').forEach(function (el) {
      var selected = el === tile;
      el.classList.toggle('is-live', selected);
      el.setAttribute('aria-selected', selected ? 'true' : 'false');
      el.setAttribute('tabindex', selected ? '0' : '-1');
    });

    showPanel(root, channel);
  }

  function runSequence(root) {
    var tiles = Array.prototype.slice.call(root.querySelectorAll('.jcp-story-publish__tile'));
    if (!tiles.length) return;
    var i = 0;
    function step() {
      if (i >= tiles.length) return;
      activateTile(root, tiles[i]);
      i += 1;
      if (i < tiles.length) {
        window.setTimeout(step, 700);
      }
    }
    window.setTimeout(step, 220);
  }

  function bindPublish(root) {
    activateTile(root, root.querySelector('.jcp-story-publish__tile.is-live') || root.querySelector('.jcp-story-publish__tile'));

    root.addEventListener('click', function (e) {
      var tile = e.target.closest('.jcp-story-publish__tile');
      if (!tile || !root.contains(tile)) return;
      activateTile(root, tile);
    });

    root.addEventListener('keydown', function (e) {
      var tile = e.target.closest('.jcp-story-publish__tile');
      if (!tile || !root.contains(tile)) return;
      var tiles = Array.prototype.slice.call(root.querySelectorAll('.jcp-story-publish__tile'));
      var idx = tiles.indexOf(tile);
      if (idx < 0) return;

      var next = -1;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        next = (idx + 1) % tiles.length;
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        next = (idx - 1 + tiles.length) % tiles.length;
      } else if (e.key === 'Home') {
        next = 0;
      } else if (e.key === 'End') {
        next = tiles.length - 1;
      }
      if (next < 0) return;
      e.preventDefault();
      activateTile(root, tiles[next]);
      tiles[next].focus();
    });

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    if (!('IntersectionObserver' in window)) {
      runSequence(root);
      return;
    }

    var started = false;
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting || started) return;
          started = true;
          runSequence(root);
          io.disconnect();
        });
      },
      { threshold: 0.35 }
    );
    io.observe(root);
  }

  function init() {
    document.querySelectorAll('[data-jcp-story-moments]').forEach(function (section) {
      var publish = section.querySelector('[data-jcp-story-publish]');
      if (publish) bindPublish(publish);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
