(() => {
  const KEY = 'jcp_site_banner_dismissed';
  const banner = document.getElementById('jcpSiteBanner') || document.getElementById('jcpEarlybirdBanner');
  if (!banner) return;

  const close = document.getElementById('jcpSiteBannerClose') || document.getElementById('jcpEarlybirdBannerClose');

  const syncBannerOffset = () => {
    const height = Math.ceil(banner.getBoundingClientRect().height);
    if (height > 0) {
      document.documentElement.style.setProperty('--jcp-banner-offset', `${height}px`);
    }
  };

  const hide = () => {
    banner.remove();
    document.documentElement.style.setProperty('--jcp-banner-offset', '0px');
    try {
      document.body.classList.remove('has-top-banner');
    } catch (e) {}
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

  syncBannerOffset();
  window.addEventListener('resize', syncBannerOffset);
  if (typeof ResizeObserver !== 'undefined') {
    const ro = new ResizeObserver(syncBannerOffset);
    ro.observe(banner);
  }

  if (!close) return;
  close.addEventListener('click', () => {
    try {
      window.sessionStorage && window.sessionStorage.setItem(KEY, '1');
    } catch (e) {}
    hide();
  });
})();
