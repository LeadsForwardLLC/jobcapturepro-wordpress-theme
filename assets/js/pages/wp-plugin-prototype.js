/**
 * WP Plugin Prototype – main slider (3 check-ins at a time) and per-card image carousels.
 */

(function () {
  var sliderTrack = document.getElementById('jcp-plugin-slider-track');
  var sliderViewport = sliderTrack ? sliderTrack.parentElement : null;
  var sliderPrev = document.getElementById('jcp-plugin-slider-prev');
  var sliderNext = document.getElementById('jcp-plugin-slider-next');

  var gap = 24;
  var currentIndex = 0;
  var totalCards = 0;

  function getCardsVisible() {
    if (!sliderViewport) return 3;
    var vw = sliderViewport.offsetWidth;
    if (vw < 640) return 1;
    if (vw < 1024) return 2;
    return 3;
  }

  function getViewportWidth(el) {
    return el && el.offsetWidth ? el.offsetWidth : 0;
  }

  function setSliderVars() {
    if (!sliderViewport || !sliderTrack) return;
    var cardsVisible = getCardsVisible();
    var vw = getViewportWidth(sliderViewport);
    if (vw <= 0) return;

    var cardWidth = (vw - (cardsVisible - 1) * gap) / cardsVisible;
    var step = cardWidth + gap;

    sliderTrack.style.setProperty('--card-width', cardWidth + 'px');
    sliderTrack.style.setProperty('--card-step', step + 'px');

    totalCards = sliderTrack.querySelectorAll('.jcp-plugin-card').length;
    currentIndex = Math.max(0, Math.min(currentIndex, Math.max(0, totalCards - cardsVisible)));

    updateSliderPosition();
    updateSliderButtons(cardsVisible);
  }

  function updateSliderPosition() {
    if (!sliderTrack) return;
    var step = parseFloat(sliderTrack.style.getPropertyValue('--card-step')) || 324;
    sliderTrack.style.transform = 'translateX(-' + currentIndex * step + 'px)';
  }

  function updateSliderButtons(cardsVisible) {
    var visible = cardsVisible || getCardsVisible();
    if (sliderPrev) sliderPrev.disabled = currentIndex <= 0;
    if (sliderNext) sliderNext.disabled = totalCards <= visible || currentIndex >= totalCards - visible;
  }

  function goPrev() {
    if (currentIndex <= 0) return;
    currentIndex -= 1;
    updateSliderPosition();
    updateSliderButtons();
  }

  function goNext() {
    var visible = getCardsVisible();
    if (totalCards <= visible || currentIndex >= totalCards - visible) return;
    currentIndex += 1;
    updateSliderPosition();
    updateSliderButtons(visible);
  }

  function initDescriptionToggles() {
    document.querySelectorAll('.jcp-plugin-card').forEach(function (card) {
      var text = card.querySelector('[data-desc-text]');
      var toggle = card.querySelector('[data-desc-toggle]');
      if (!text || !toggle) return;

      if (!toggle.dataset.bound) {
        toggle.addEventListener('click', function () {
          var expanded = text.classList.toggle('is-expanded');
          toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
          toggle.textContent = expanded ? 'Show less' : 'Read more';
        });
        toggle.dataset.bound = '1';
      }

      // If card is expanded, keep button visible.
      if (text.classList.contains('is-expanded')) {
        toggle.hidden = false;
        return;
      }

      requestAnimationFrame(function () {
        toggle.hidden = text.scrollHeight <= text.clientHeight + 2;
      });
    });
  }

  // Per-card image carousels
  document.querySelectorAll('.jcp-plugin-card__gallery[data-carousel]').forEach(function (gallery) {
    var slides = gallery.querySelectorAll('.jcp-plugin-card__slide');
    var prevBtn = gallery.querySelector('.jcp-plugin-card__nav--prev');
    var nextBtn = gallery.querySelector('.jcp-plugin-card__nav--next');
    var dots = gallery.querySelectorAll('.jcp-plugin-card__dot');
    var total = slides.length;
    var active = 0;

    function setActive(i) {
      if (i < 0) i = total - 1;
      if (i >= total) i = 0;
      active = i;
      slides.forEach(function (slide, idx) {
        slide.classList.toggle('is-active', idx === active);
      });
      dots.forEach(function (dot, idx) {
        dot.classList.toggle('is-active', idx === active);
      });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { setActive(active - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { setActive(active + 1); });
    dots.forEach(function (dot, idx) {
      dot.addEventListener('click', function () { setActive(idx); });
    });
  });

  if (sliderPrev) sliderPrev.addEventListener('click', goPrev);
  if (sliderNext) sliderNext.addEventListener('click', goNext);

  var resizeTimeout;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function () {
      setSliderVars();
      initDescriptionToggles();
    }, 100);
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setSliderVars();
      initDescriptionToggles();
    });
  } else {
    setSliderVars();
    initDescriptionToggles();
  }
})();
