(() => {
  const KEY = 'jcp_site_banner_dismissed';
  const stack = document.getElementById('jcpHeaderStack');
  const banner = document.getElementById('jcpSiteBanner') || document.getElementById('jcpEarlybirdBanner');

  const syncStackHeight = () => {
    if (!stack) return;
    const height = Math.ceil(stack.getBoundingClientRect().height);
    if (height > 0) {
      document.documentElement.style.setProperty('--jcp-header-stack-height', `${height}px`);
    }
  };

  if (stack) {
    syncStackHeight();
    window.addEventListener('resize', syncStackHeight);
    window.addEventListener('orientationchange', syncStackHeight);
    if (typeof ResizeObserver !== 'undefined') {
      const stackObserver = new ResizeObserver(syncStackHeight);
      stackObserver.observe(stack);
    }
  }

  if (!banner) return;

  const close = document.getElementById('jcpSiteBannerClose') || document.getElementById('jcpEarlybirdBannerClose');

  const hide = () => {
    banner.remove();
    try {
      document.body.classList.remove('has-top-banner');
    } catch (e) {}
    syncStackHeight();
  };

  const dismissed = (() => {
    try {
      return window.sessionStorage ? window.sessionStorage.getItem(KEY) === '1' : false;
    } catch (e) {
      return false;
    }
  })();

  if (dismissed) {
    hide();
    return;
  }

  if (!close) return;
  close.addEventListener('click', () => {
    try {
      window.sessionStorage && window.sessionStorage.setItem(KEY, '1');
    } catch (e) {}
    hide();
  });
})();
