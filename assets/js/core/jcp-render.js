(() => {
  const root = document.getElementById('jcp-app');
  if (!root) return;

  const page = (root.dataset.jcpPage || '').trim().toLowerCase();

  if (!window.JCP_ASSET_BASE) {
    const scriptSrc = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
    const marker = '/core/jcp-render.js';
    if (scriptSrc.includes(marker)) {
      window.JCP_ASSET_BASE = scriptSrc.split(marker)[0];
    }
  }

  const assetBase = window.JCP_ASSET_BASE || '';
  const baseUrl = window.JCP_CONFIG && window.JCP_CONFIG.baseUrl
    ? window.JCP_CONFIG.baseUrl
    : window.location.origin;

  let templateUrl = '';

  // Treat prototype and demo the same (fetch demo/index.html, then initDemo)
  if (page === 'prototype' || page === 'demo') {
    templateUrl = `${assetBase}/demo/index.html`;
  } else switch (page) {
    case 'home':
      if (typeof window.renderHome === 'function') {
        window.renderHome();
        return;
      }
      console.warn('JCP render: renderHome is not available');
      return;
    case 'pricing':
      if (typeof window.renderPricing === 'function') {
        window.renderPricing();
        return;
      }
      console.warn('JCP render: renderPricing is not available');
      return;
    case 'contact':
      if (typeof window.renderContact === 'function') {
        window.renderContact();
        return;
      }
      console.warn('JCP render: renderContact is not available');
      return;
    case 'directory':
      templateUrl = `${assetBase}/directory/index.html`;
      break;
    case 'estimate':
      templateUrl = `${assetBase}/estimate/index.html`;
      break;
    case 'company':
      templateUrl = `${assetBase}/directory/profile.html`;
      break;
    default:
      console.warn(`JCP render: unknown page "${page}"`);
      return;
  }

  // Reserve layout space to reduce CLS while template loads
  root.style.minHeight = '50vh';

  let fetchUrl = templateUrl;
  if ((page === 'demo' || page === 'prototype') && window.JCP_DEMO_TEMPLATE_VERSION) {
    fetchUrl = `${templateUrl}?v=${encodeURIComponent(window.JCP_DEMO_TEMPLATE_VERSION)}`;
  }

  fetch(fetchUrl, { cache: 'no-store' })
    .then((res) => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.text();
    })
    .then((html) => {
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const inlineScripts = [];

      doc.body.querySelectorAll('script').forEach((script) => {
        if (!script.src && script.textContent.trim()) {
          inlineScripts.push(script.textContent);
        }
        script.remove();
      });

      doc.body.querySelectorAll('link[rel="stylesheet"]').forEach((link) => {
        link.remove();
      });

      const wrapper = document.createElement('div');
      wrapper.innerHTML = doc.body.innerHTML;

      const rewriteAssetValue = (value) => {
    if (!value) return value;

    const sharedMatch = value.match(/^(?:\.\.\/)*shared\/assets\/(.+)$/);
    if (sharedMatch) {
      return `${assetBase}/shared/assets/${sharedMatch[1]}`;
    }

    if (value.startsWith('/shared/assets/')) {
      return `${assetBase}${value}`;
    }

    if (value.startsWith('/src/jcp-demo/')) {
      return `${baseUrl}/demo/`;
    }

    if (value.startsWith('/src/contractor-directory/')) {
      return `${baseUrl}/directory/`;
    }

    if (value.startsWith('/src/estimate-builder/')) {
      return `${baseUrl}/estimate/`;
    }

    if (value === 'index.html') {
      return `${baseUrl}/directory/`;
    }

    if (value.startsWith('profile.html')) {
      const query = value.includes('?') ? value.slice(value.indexOf('?') + 1) : '';
      const params = new URLSearchParams(query);
      const id = params.get('id');
      return id ? `${baseUrl}/directory/${id}` : `${baseUrl}/directory/`;
    }

    if (value.startsWith('../estimator-dashboard/')) {
      return `${baseUrl}/estimate/`;
    }

    return value;
  };

  wrapper.querySelectorAll('[src]').forEach((el) => {
    el.setAttribute('src', rewriteAssetValue(el.getAttribute('src')));
  });

  wrapper.querySelectorAll('[href]').forEach((el) => {
    el.setAttribute('href', rewriteAssetValue(el.getAttribute('href')));
  });

  wrapper.querySelectorAll('[poster]').forEach((el) => {
    el.setAttribute('poster', rewriteAssetValue(el.getAttribute('poster')));
  });

      root.style.minHeight = '';
      root.innerHTML = '';
      while (wrapper.firstChild) {
        root.appendChild(wrapper.firstChild);
      }

      inlineScripts.forEach((code) => {
        const script = document.createElement('script');
        script.text = code;
        document.body.appendChild(script);
      });

      if ((page === 'demo' || page === 'prototype') && typeof window.initDemo === 'function') {
        window.initDemo();
      }
      if (page === 'directory' && typeof window.initDirectory === 'function') {
        window.initDirectory();
      }
      if (page === 'company' && typeof window.initProfile === 'function') {
        window.initProfile();
      }
    })
    .catch((err) => {
      root.style.minHeight = '';
      console.error('JCP render: failed to load template', templateUrl, err);
    });
})();
