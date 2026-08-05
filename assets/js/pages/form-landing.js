/**
 * Form Landing — close control (history.back with fallback href).
 */
(function () {
  function canGoBack() {
    try {
      if (!document.referrer) return false;
      var ref = new URL(document.referrer);
      return ref.origin === window.location.origin && window.history.length > 1;
    } catch (e) {
      return false;
    }
  }

  document.addEventListener('click', function (event) {
    var el = event.target && event.target.closest
      ? event.target.closest('[data-jcp-form-landing-close]')
      : null;
    if (!el) return;
    if (!canGoBack()) return;
    event.preventDefault();
    window.history.back();
  });
})();
