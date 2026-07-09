(() => {
  const KEY = 'jcp_blog_sticky_dismissed';
  const bar = document.getElementById('jcpBlogStickyCta');
  if (!bar) return;

  const closeBtn = document.getElementById('jcpBlogStickyCtaClose');
  const SCROLL_PCT = 0.4;
  const TIME_MS = 20000;

  const dismissed = (() => {
    try {
      return window.sessionStorage ? window.sessionStorage.getItem(KEY) === '1' : false;
    } catch (e) {
      return false;
    }
  })();

  if (dismissed) {
    bar.remove();
    return;
  }

  let shown = false;

  const show = () => {
    if (shown) return;
    shown = true;
    bar.hidden = false;
    requestAnimationFrame(() => {
      bar.classList.add('is-visible');
      document.body.classList.add('has-blog-sticky-cta');
    });
    window.removeEventListener('scroll', onScroll);
  };

  const hide = () => {
    try {
      window.sessionStorage && window.sessionStorage.setItem(KEY, '1');
    } catch (e) {}
    bar.classList.remove('is-visible');
    document.body.classList.remove('has-blog-sticky-cta');
    window.setTimeout(() => bar.remove(), 220);
  };

  const onScroll = () => {
    const doc = document.documentElement;
    const scrollable = doc.scrollHeight - window.innerHeight;
    if (scrollable <= 0) return;
    if (window.scrollY / scrollable >= SCROLL_PCT) {
      show();
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  window.setTimeout(show, TIME_MS);
  onScroll();

  if (closeBtn) {
    closeBtn.addEventListener('click', hide);
  }
})();
