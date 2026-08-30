/**
 * Story moments — interactive publish + review beats for campaign LPs.
 */
(function () {
  'use strict';

  var PREVIEW_LABELS = {
    website: 'Live on your website',
    google: 'Live on Google Business Profile',
    social: 'Ready for social',
    directory: 'Listed in your directory',
  };

  function activateTile(root, tile) {
    if (!root || !tile) return;
    var channel = tile.getAttribute('data-channel') || 'website';
    var live = tile.getAttribute('data-live-label') || PREVIEW_LABELS[channel] || PREVIEW_LABELS.website;

    root.querySelectorAll('.jcp-story-publish__tile').forEach(function (el) {
      var selected = el === tile;
      el.classList.toggle('is-live', selected);
      el.setAttribute('aria-pressed', selected ? 'true' : 'false');
      el.setAttribute('aria-selected', selected ? 'true' : 'false');
      var action = el.querySelector('.jcp-story-publish__tile-action');
      if (action) {
        action.textContent = selected ? 'Showing' : 'Preview';
      }
    });

    var preview = root.querySelector('[data-publish-preview]');
    var label = root.querySelector('[data-publish-preview-label]');
    if (label) label.textContent = live;
    if (preview) {
      preview.hidden = false;
      preview.classList.add('is-active');
    }
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
        window.setTimeout(step, 520);
      }
    }
    window.setTimeout(step, 180);
  }

  function bindPublish(root) {
    // Always show an initial preview so the result panel is never empty.
    activateTile(root, root.querySelector('.jcp-story-publish__tile'));

    root.addEventListener('click', function (e) {
      var tile = e.target.closest('.jcp-story-publish__tile');
      if (!tile || !root.contains(tile)) return;
      activateTile(root, tile);
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
