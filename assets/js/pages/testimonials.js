(function () {
  'use strict';

  var FALLBACK_REVIEWS = [
    {
      id: 'peter-bonk',
      name: 'Peter Bonk',
      role: 'Marketing agency',
      quote:
        "One of the easiest marketing wins we've had for an HVAC client. Techs already take photos. Now those become GBP updates, website content, social posts, and an on-site review ask. The review flow alone has been worth it.",
      rating: 5,
    },
    {
      id: 'brian-hardy',
      name: 'Brian Hardy',
      role: 'Contractor',
      quote: 'Awesome. It takes my work site pictures and turns them into a marketing campaign.',
      rating: 5,
    },
    {
      id: 'trent-ellison',
      name: 'Trent Ellison',
      role: 'Home service operator',
      quote:
        'Easy to use and really smart. Makes it super simple to turn completed work into useful online content, and the review side is amazing.',
      rating: 5,
    },
    {
      id: 'heriberto-eddie-roman',
      name: 'Heriberto Eddie Roman',
      role: 'Business owner',
      quote: 'JobCapturePro has been a game changer for my business!',
      rating: 5,
    },
  ];

  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function key(r) {
    return String((r && (r.id || r.name)) || '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '-');
  }

  function stars(rating, on) {
    if (!on || rating <= 0) return '';
    return (
      '<div class="jcp-testimonials-stars" aria-label="' +
      esc(rating + ' out of 5 stars') +
      '">' +
      '<span aria-hidden="true">' +
      '\u2605'.repeat(rating) +
      '\u2606'.repeat(Math.max(0, 5 - rating)) +
      '</span></div>'
    );
  }

  function initials(name) {
    var parts = String(name || '')
      .trim()
      .split(/\s+/)
      .filter(Boolean);
    if (!parts.length) return '?';
    var first = parts[0].charAt(0).toUpperCase();
    var last = parts.length > 1 ? parts[parts.length - 1].charAt(0).toUpperCase() : '';
    return first + last;
  }

  function avatar(r) {
    var url = String(r.avatar_url || r.avatar || r.image_url || '').trim();
    var mark = url
      ? '<img src="' + esc(url) + '" alt="" width="44" height="44" loading="lazy" decoding="async" />'
      : '<span class="jcp-testimonials-avatar__initials">' + esc(r.initials || initials(r.name)) + '</span>';
    return '<span class="jcp-testimonials-avatar" aria-hidden="true">' + mark + '</span>';
  }

  function card(r, showStars, showRoles) {
    var role = showRoles && r.role ? '<span class="jcp-testimonials-card-role">' + esc(r.role) + '</span>' : '';
    return (
      '<article class="jcp-testimonials-card" data-review-key="' +
      esc(key(r)) +
      '" aria-label="' +
      esc('Review from ' + r.name) +
      '" role="listitem">' +
      stars(r.rating || 5, showStars) +
      '<p class="jcp-testimonials-card-quote">' +
      esc(r.quote) +
      '</p>' +
      '<div class="jcp-testimonials-card-person">' +
      avatar(r) +
      '<span class="jcp-testimonials-card-person-text">' +
      '<span class="jcp-testimonials-card-name">' +
      esc(r.name) +
      '</span>' +
      role +
      '</span></div></article>'
    );
  }

  function parseStore(el) {
    var raw = (el && el.textContent ? el.textContent : '').trim();
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch (e1) {
      var ta = document.createElement('textarea');
      ta.innerHTML = raw;
      try {
        return JSON.parse(ta.value);
      } catch (e2) {
        return null;
      }
    }
  }

  function mergeCanonical(reviews) {
    var byId = {};
    (Array.isArray(reviews) ? reviews : []).forEach(function (r) {
      if (!r || typeof r !== 'object') return;
      var id = key(r);
      var name = String(r.name || '').trim();
      var quote = String(r.quote || '').trim();
      if (!id || !name || !quote) return;
      byId[id] = r;
    });
    return FALLBACK_REVIEWS.map(function (want) {
      var id = key(want);
      var existing = byId[id];
      if (!existing) return want;
      return {
        id: want.id,
        name: existing.name || want.name,
        role: existing.role || want.role,
        quote: existing.quote || want.quote,
        rating: existing.rating || want.rating,
        avatar_url: existing.avatar_url || existing.avatar || want.avatar_url || '',
        initials: existing.initials || initials(existing.name || want.name),
      };
    });
  }

  function init(root) {
    var storeEl = root.querySelector('[data-jcp-testimonials-store]');
    var parsed = storeEl ? parseStore(storeEl) : null;
    var reviews = mergeCanonical(parsed);

    var slider = root.querySelector('[data-jcp-testimonials-slider]');
    var track = slider && slider.querySelector('[data-jcp-testimonials-track]');
    if (!slider || !track) return;

    // Force grid wall classes/attrs so CSS + markup stay consistent.
    root.classList.add('jcp-testimonials--grid', 'jcp-testimonials--slider-only');
    root.setAttribute('data-layout', 'grid');
    root.setAttribute('data-slider-only', '1');
    root.removeAttribute('data-featured-key');

    var featuredEl = root.querySelector('[data-jcp-testimonials-featured]');
    if (featuredEl) featuredEl.hidden = true;

    var dotsEl = slider.querySelector('[data-jcp-testimonials-dots]');
    var prevBtn = slider.querySelector('[data-jcp-testimonials-prev]');
    var nextBtn = slider.querySelector('[data-jcp-testimonials-next]');
    var showStars = !!(
      track.querySelector('.jcp-testimonials-stars') ||
      (featuredEl && featuredEl.querySelector('.jcp-testimonials-stars')) ||
      true
    );
    var showRoles = !!(
      track.querySelector('.jcp-testimonials-card-role') ||
      (featuredEl && featuredEl.querySelector('.jcp-testimonials-role')) ||
      true
    );

    // Always paint all four reviews. Never filter a "featured" review out of the grid.
    track.innerHTML = reviews.map(function (r) {
      return card(r, showStars, showRoles);
    }).join('');

    if (prevBtn) {
      prevBtn.hidden = true;
      prevBtn.setAttribute('aria-hidden', 'true');
    }
    if (nextBtn) {
      nextBtn.hidden = true;
      nextBtn.setAttribute('aria-hidden', 'true');
    }
    if (dotsEl) {
      dotsEl.hidden = true;
      dotsEl.innerHTML = '';
    }
  }

  function boot() {
    document.querySelectorAll('[data-jcp-testimonials]').forEach(init);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
