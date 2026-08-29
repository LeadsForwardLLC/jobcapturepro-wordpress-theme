(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function esc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function key(r) {
    return String(r.id || '');
  }

  function stars(rating, on) {
    if (!on || rating <= 0) return '';
    return (
      '<div class="jcp-testimonials-stars" aria-label="' + esc(rating + ' out of 5 stars') + '">' +
      '<span aria-hidden="true">' + '\u2605'.repeat(rating) + '\u2606'.repeat(Math.max(0, 5 - rating)) + '</span></div>'
    );
  }

  function card(r, showStars, showRoles, asButton) {
    var role = showRoles && r.role ? '<span class="jcp-testimonials-card-role">' + esc(r.role) + '</span>' : '';
    var inner =
      stars(r.rating || 5, showStars) +
      '<p class="jcp-testimonials-card-quote">' + esc(r.quote) + '</p>' +
      '<span class="jcp-testimonials-card-name">' + esc(r.name) + '</span>' + role;
    if (asButton) {
      return (
        '<button type="button" class="jcp-testimonials-card" data-review-key="' + esc(key(r)) + '" aria-label="' +
        esc('Show review from ' + r.name) + '" role="listitem">' + inner + '</button>'
      );
    }
    return (
      '<article class="jcp-testimonials-card" data-review-key="' + esc(key(r)) + '" aria-label="' +
      esc('Review from ' + r.name) + '" role="listitem">' + inner + '</article>'
    );
  }

  function setFeatured(el, r, showStars, showRoles) {
    var role = showRoles && r.role ? '<span class="jcp-testimonials-role">' + esc(r.role) + '</span>' : '';
    el.innerHTML =
      stars(r.rating || 5, showStars) +
      '<blockquote class="jcp-testimonials-quote"><p>' + esc(r.quote) + '</p></blockquote>' +
      '<figcaption class="jcp-testimonials-cite"><cite class="jcp-testimonials-name">' + esc(r.name) + '</cite>' + role + '</figcaption>';
  }

  function init(root) {
    var storeEl = root.querySelector('[data-jcp-testimonials-store]');
    if (!storeEl) return;

    var reviews;
    try {
      reviews = JSON.parse(storeEl.textContent || '[]');
    } catch (e) {
      return;
    }
    if (!Array.isArray(reviews) || !reviews.length) return;

    var slider = root.querySelector('[data-jcp-testimonials-slider]');
    var track = slider && slider.querySelector('[data-jcp-testimonials-track]');
    if (!slider || !track) return;

    var featuredEl = root.querySelector('[data-jcp-testimonials-featured]');
    var sliderOnly = root.getAttribute('data-slider-only') === '1' || !featuredEl;
    var dotsEl = slider.querySelector('[data-jcp-testimonials-dots]');
    var prevBtn = slider.querySelector('[data-jcp-testimonials-prev]');
    var nextBtn = slider.querySelector('[data-jcp-testimonials-next]');
    var showStars = !!(
      (featuredEl && featuredEl.querySelector('.jcp-testimonials-stars')) ||
      track.querySelector('.jcp-testimonials-stars')
    );
    var showRoles = !!(
      (featuredEl && featuredEl.querySelector('.jcp-testimonials-role')) ||
      track.querySelector('.jcp-testimonials-card-role')
    );
    var autoplayOn = root.getAttribute('data-autoplay') === '1';
    var autoplayMs = Math.max(1000, parseInt(root.getAttribute('data-autoplay-ms') || '6000', 10));
    var perView = Math.max(1, parseInt(root.getAttribute('data-per-view') || (sliderOnly ? '2' : '1'), 10));
    if (window.matchMedia('(max-width: 700px)').matches) perView = 1;
    var index = 0;
    var paused = false;
    var timer = null;

    function featuredKey() {
      return root.getAttribute('data-featured-key') || '';
    }

    function secondary() {
      if (sliderOnly) return reviews.slice();
      var fk = featuredKey();
      return reviews.filter(function (r) { return key(r) !== fk; });
    }

    function cards() {
      return Array.prototype.slice.call(track.querySelectorAll('.jcp-testimonials-card'));
    }

    function pageCount() {
      var n = cards().length;
      if (n <= perView) return 1;
      return Math.max(1, n - perView + 1);
    }

    function behavior() {
      return reduced ? 'auto' : 'smooth';
    }

    function syncUi() {
      var list = cards();
      var pages = pageCount();
      list.forEach(function (c, i) {
        var active = i >= index && i < index + perView;
        c.classList.toggle('is-active', active);
        if (active) c.setAttribute('aria-current', 'true');
        else c.removeAttribute('aria-current');
      });
      if (dotsEl) {
        Array.prototype.forEach.call(dotsEl.querySelectorAll('button'), function (d, i) {
          var sel = i === index;
          d.classList.toggle('is-active', sel);
          d.setAttribute('aria-selected', sel ? 'true' : 'false');
          d.setAttribute('tabindex', sel ? '0' : '-1');
        });
      }
      var one = pages <= 1;
      if (prevBtn) prevBtn.disabled = one;
      if (nextBtn) nextBtn.disabled = one;
    }

    function scrollToIndex(n) {
      var list = cards();
      if (!list.length) return;
      var max = Math.max(0, list.length - perView);
      index = ((n % (max + 1)) + (max + 1)) % (max + 1);
      list[index].scrollIntoView({ behavior: behavior(), inline: 'start', block: 'nearest' });
      syncUi();
    }

    function syncFromScroll() {
      var list = cards();
      if (!list.length) return;
      var left = track.scrollLeft;
      var nearest = 0;
      var min = Infinity;
      list.forEach(function (c, i) {
        var d = Math.abs(c.offsetLeft - left);
        if (d < min) { min = d; nearest = i; }
      });
      index = nearest;
      syncUi();
    }

    function renderDots(count) {
      if (!dotsEl) return;
      dotsEl.innerHTML = '';
      var pages = Math.max(1, count - perView + 1);
      if (count <= perView) pages = 1;
      for (var i = 0; i < pages; i++) {
        (function (j) {
          var dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'jcp-testimonials-dot';
          dot.setAttribute('role', 'tab');
          dot.setAttribute('aria-label', 'Reviews page ' + (j + 1));
          dot.addEventListener('click', function () { scrollToIndex(j); restart(); });
          dotsEl.appendChild(dot);
        })(i);
      }
    }

    function bindCards() {
      if (sliderOnly) return;
      cards().forEach(function (c) {
        c.addEventListener('click', function () {
          var k = c.getAttribute('data-review-key');
          if (!k || k === featuredKey() || !featuredEl) return;
          var r = reviews.find(function (item) { return key(item) === k; });
          if (!r) return;
          root.setAttribute('data-featured-key', k);
          setFeatured(featuredEl, r, showStars, showRoles);
          rebuildTrack();
          restart();
        });
      });
    }

    function rebuildTrack() {
      var list = secondary();
      track.innerHTML = list.map(function (r) { return card(r, showStars, showRoles, !sliderOnly); }).join('');
      renderDots(list.length);
      index = 0;
      track.scrollLeft = 0;
      bindCards();
      syncUi();
    }

    function stop() {
      if (timer) { clearInterval(timer); timer = null; }
    }

    function start() {
      stop();
      if (!autoplayOn || reduced || paused || pageCount() <= 1) return;
      timer = setInterval(function () {
        if (!paused) scrollToIndex(index + 1);
      }, autoplayMs);
    }

    function restart() { stop(); start(); }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollToIndex(index - 1); restart(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollToIndex(index + 1); restart(); });

    var scrollTimer;
    track.addEventListener('scroll', function () {
      clearTimeout(scrollTimer);
      scrollTimer = setTimeout(syncFromScroll, 80);
    });

    root.addEventListener('mouseenter', function () { paused = true; stop(); });
    root.addEventListener('mouseleave', function () { paused = false; start(); });
    root.addEventListener('focusin', function () { paused = true; stop(); });
    root.addEventListener('focusout', function (e) {
      if (!root.contains(e.relatedTarget)) { paused = false; start(); }
    });

    if (sliderOnly) {
      renderDots(cards().length);
      syncUi();
    } else {
      renderDots(secondary().length);
      bindCards();
      syncUi();
    }
    start();
  }

  function boot() {
    document.querySelectorAll('[data-jcp-testimonials]').forEach(init);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
