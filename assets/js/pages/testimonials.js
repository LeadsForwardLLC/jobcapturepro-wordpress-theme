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

  function initials(name) {
    var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    var first = parts[0].charAt(0).toUpperCase();
    var last = parts.length > 1 ? parts[parts.length - 1].charAt(0).toUpperCase() : '';
    return first + last;
  }

  function avatar(r) {
    var url = String(r.avatar_url || r.image_url || '').trim();
    var mark = url
      ? '<img src="' + esc(url) + '" alt="" width="44" height="44" loading="lazy" decoding="async" />'
      : '<span class="jcp-testimonials-avatar__initials">' + esc(r.initials || initials(r.name)) + '</span>';
    return '<span class="jcp-testimonials-avatar" aria-hidden="true">' + mark + '</span>';
  }

  function card(r, showStars, showRoles, asButton) {
    var role = showRoles && r.role ? '<span class="jcp-testimonials-card-role">' + esc(r.role) + '</span>' : '';
    var inner =
      stars(r.rating || 5, showStars) +
      '<p class="jcp-testimonials-card-quote">' + esc(r.quote) + '</p>' +
      '<div class="jcp-testimonials-card-person">' +
      avatar(r) +
      '<span class="jcp-testimonials-card-person-text">' +
      '<span class="jcp-testimonials-card-name">' + esc(r.name) + '</span>' +
      role +
      '</span></div>';
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
      '<figcaption class="jcp-testimonials-cite">' +
      avatar(r) +
      '<span class="jcp-testimonials-cite-text"><cite class="jcp-testimonials-name">' + esc(r.name) + '</cite>' + role + '</span>' +
      '</figcaption>';
  }

  function parseStore(el) {
    var raw = (el && el.textContent ? el.textContent : '').trim();
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch (e1) {
      // Legacy pages used esc_html() so textContent can still contain &quot;.
      var ta = document.createElement('textarea');
      ta.innerHTML = raw;
      try {
        return JSON.parse(ta.value);
      } catch (e2) {
        return null;
      }
    }
  }

  function init(root) {
    var storeEl = root.querySelector('[data-jcp-testimonials-store]');
    if (!storeEl) return;

    var reviews = parseStore(storeEl);
    if (!Array.isArray(reviews) || !reviews.length) return;

    var slider = root.querySelector('[data-jcp-testimonials-slider]');
    var track = slider && slider.querySelector('[data-jcp-testimonials-track]');
    if (!slider || !track) return;

    var featuredEl = root.querySelector('[data-jcp-testimonials-featured]');
    var sliderOnly = root.getAttribute('data-slider-only') === '1' || !featuredEl;
    var isGrid = root.getAttribute('data-layout') === 'grid' || root.classList.contains('jcp-testimonials--grid');
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

    // Grid layout shows every review at once — skip carousel chrome.
    if (isGrid) {
      track.innerHTML = secondary().map(function (r) {
        return card(r, showStars, showRoles, !sliderOnly);
      }).join('');
      if (prevBtn) prevBtn.hidden = true;
      if (nextBtn) nextBtn.hidden = true;
      if (dotsEl) dotsEl.hidden = true;
      return;
    }

    var autoplayOn = root.getAttribute('data-autoplay') === '1';
    var autoplayMs = Math.max(1000, parseInt(root.getAttribute('data-autoplay-ms') || '6000', 10));
    var perView = Math.max(1, parseInt(root.getAttribute('data-per-view') || (sliderOnly ? '2' : '1'), 10));
    if (window.matchMedia('(max-width: 700px)').matches) perView = 1;

    // Advance one "page" (perView cards) in slider-only mode.
    var step = sliderOnly ? perView : 1;
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

    function maxIndex() {
      var n = cards().length;
      return Math.max(0, n - perView);
    }

    function pageCount() {
      return maxIndex() + 1;
    }

    function scrollTrackTo(i) {
      var list = cards();
      if (!list.length) return;
      var target = list[i];
      if (!target) return;
      // Prefer getBoundingClientRect — offsetLeft breaks when offsetParents differ.
      var left =
        target.getBoundingClientRect().left -
        track.getBoundingClientRect().left +
        track.scrollLeft;
      if (typeof track.scrollTo === 'function') {
        track.scrollTo({ left: left, behavior: reduced ? 'auto' : 'smooth' });
      } else {
        track.scrollLeft = left;
      }
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
          var pageIndex = sliderOnly ? i * step : i;
          var sel = pageIndex === index;
          d.classList.toggle('is-active', sel);
          d.setAttribute('aria-selected', sel ? 'true' : 'false');
          d.setAttribute('tabindex', sel ? '0' : '-1');
        });
      }
      var one = pages <= 1;
      if (prevBtn) {
        prevBtn.disabled = one;
        prevBtn.setAttribute('aria-disabled', one ? 'true' : 'false');
      }
      if (nextBtn) {
        nextBtn.disabled = one;
        nextBtn.setAttribute('aria-disabled', one ? 'true' : 'false');
      }
    }

    function goTo(n) {
      var max = maxIndex();
      if (max <= 0) {
        index = 0;
        scrollTrackTo(0);
        syncUi();
        return;
      }
      index = ((n % (max + 1)) + (max + 1)) % (max + 1);
      // Snap to step boundaries in slider-only mode.
      if (sliderOnly && step > 1) {
        index = Math.round(index / step) * step;
        if (index > max) index = max;
      }
      scrollTrackTo(index);
      syncUi();
    }

    function syncFromScroll() {
      var list = cards();
      if (!list.length) return;
      var trackLeft = track.getBoundingClientRect().left;
      var nearest = 0;
      var min = Infinity;
      list.forEach(function (c, i) {
        var d = Math.abs(c.getBoundingClientRect().left - trackLeft);
        if (d < min) {
          min = d;
          nearest = i;
        }
      });
      index = nearest;
      if (sliderOnly && step > 1) {
        index = Math.round(index / step) * step;
        if (index > maxIndex()) index = maxIndex();
      }
      syncUi();
    }

    function renderDots() {
      if (!dotsEl) return;
      dotsEl.innerHTML = '';
      var max = maxIndex();
      var pages = max + 1;
      if (sliderOnly && step > 1) {
        pages = Math.ceil(cards().length / step);
      }
      if (cards().length <= perView) pages = 1;
      for (var i = 0; i < pages; i++) {
        (function (j) {
          var dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'jcp-testimonials-dot';
          dot.setAttribute('role', 'tab');
          dot.setAttribute('aria-label', 'Reviews page ' + (j + 1));
          dot.addEventListener('click', function (e) {
            e.preventDefault();
            goTo(sliderOnly ? j * step : j);
            restart();
          });
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
      index = 0;
      track.scrollLeft = 0;
      renderDots();
      bindCards();
      syncUi();
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (!autoplayOn || reduced || paused || pageCount() <= 1) return;
      timer = setInterval(function () {
        if (!paused) goTo(index + step);
      }, autoplayMs);
    }

    function restart() {
      stop();
      start();
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function (e) {
        e.preventDefault();
        goTo(index - step);
        restart();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        goTo(index + step);
        restart();
      });
    }

    var scrollTimer;
    track.addEventListener('scroll', function () {
      clearTimeout(scrollTimer);
      scrollTimer = setTimeout(syncFromScroll, 80);
    }, { passive: true });

    root.addEventListener('mouseenter', function () {
      paused = true;
      stop();
    });
    root.addEventListener('mouseleave', function () {
      paused = false;
      start();
    });
    root.addEventListener('focusin', function () {
      paused = true;
      stop();
    });
    root.addEventListener('focusout', function (e) {
      if (!root.contains(e.relatedTarget)) {
        paused = false;
        start();
      }
    });

    window.addEventListener('resize', function () {
      var nextPer = Math.max(1, parseInt(root.getAttribute('data-per-view') || (sliderOnly ? '2' : '1'), 10));
      if (window.matchMedia('(max-width: 700px)').matches) nextPer = 1;
      if (nextPer !== perView) {
        perView = nextPer;
        step = sliderOnly ? perView : 1;
        renderDots();
        goTo(0);
        restart();
      }
    });

    renderDots();
    if (!sliderOnly) bindCards();
    syncUi();
    start();
  }

  function boot() {
    document.querySelectorAll('[data-jcp-testimonials]').forEach(init);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
