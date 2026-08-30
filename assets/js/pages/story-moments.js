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
      el.classList.toggle('is-live', el === tile);
      el.setAttribute('aria-pressed', el === tile ? 'true' : 'false');
      var status = el.querySelector('.jcp-story-publish__tile-status');
      if (status) {
        status.textContent = el === tile ? 'Live' : 'Ready';
      }
    });

    var preview = root.querySelector('[data-publish-preview]');
    var label = root.querySelector('[data-publish-preview-label]');
    if (label) label.textContent = live;
    if (preview) preview.hidden = false;
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
        window.setTimeout(step, 420);
      }
    }
    window.setTimeout(step, 280);
  }

  function bindPublish(root) {
    root.addEventListener('click', function (e) {
      var tile = e.target.closest('.jcp-story-publish__tile');
      if (!tile || !root.contains(tile)) return;
      activateTile(root, tile);
    });

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      activateTile(root, root.querySelector('.jcp-story-publish__tile'));
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

  function bindReviews(card) {
    if (!card || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      if (card) card.classList.add('is-visible');
      return;
    }
    if (!('IntersectionObserver' in window)) {
      card.classList.add('is-visible');
      return;
    }
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        });
      },
      { threshold: 0.4 }
    );
    io.observe(card);
  }

  function init() {
    document.querySelectorAll('[data-jcp-story-moments]').forEach(function (section) {
      var publish = section.querySelector('[data-jcp-story-publish]');
      if (publish) bindPublish(publish);
      var reviewsCard = section.querySelector('.jcp-story-reviews__card');
      if (reviewsCard) bindReviews(reviewsCard);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
