(function() {
  function init() {
  const header = document.getElementById('jcpGlobalHeader');
  if (!header) return;

  /* Reset body scroll on load so iOS / cached state never leaves scroll locked */
  document.body.style.overflow = '';

  const menuToggle = document.getElementById('mobileMenuToggle');
  const menuClose = document.getElementById('mobileMenuClose');
  const menuOverlay = document.getElementById('mobileMenuOverlay');

  const initMobileMenu = () => {
    if (!menuToggle || !menuClose || !menuOverlay) return;

    const closeMenu = () => {
      menuOverlay.classList.remove('active');
      menuToggle.classList.remove('active');
      document.body.classList.remove('mobile-menu-open');
      document.body.style.overflow = '';
    };

    menuToggle.addEventListener('click', () => {
      menuOverlay.classList.add('active');
      menuToggle.classList.add('active');
      document.body.classList.add('mobile-menu-open');
      document.body.style.overflow = 'hidden';
    });

    menuClose.addEventListener('click', closeMenu);
    menuOverlay.addEventListener('click', (e) => {
      if (e.target === menuOverlay) {
        closeMenu();
      }
    });

    document.querySelectorAll('.mobile-nav-link, .mobile-mega-card, .mobile-nav-panel-footer').forEach((link) => {
      link.addEventListener('click', () => closeMenu());
    });

    document.querySelectorAll('.mobile-nav-panel').forEach((panel) => {
      panel.addEventListener('toggle', () => {
        if (!panel.open) return;
        document.querySelectorAll('.mobile-nav-panel').forEach((other) => {
          if (other !== panel) other.open = false;
        });
        document.querySelectorAll('.mobile-nav-resources').forEach((other) => {
          other.open = false;
        });
      });
    });

    const mobileResources = document.getElementById('mobileNavResources');
    if (mobileResources) {
      mobileResources.addEventListener('toggle', () => {
        if (!mobileResources.open) return;
        document.querySelectorAll('.mobile-nav-panel').forEach((panel) => {
          panel.open = false;
        });
      });
    }

    const actionsTop = document.getElementById('mobileMenuActionsTop');
    if (actionsTop) {
      actionsTop.querySelectorAll('a, button').forEach((el) => {
        el.addEventListener('click', () => closeMenu());
      });
    }

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menuOverlay.classList.contains('active')) {
        closeMenu();
      }
    });
  };

  /* On pageshow (e.g. back/forward), ensure body scroll is not stuck (iOS / bfcache) */
  window.addEventListener('pageshow', function (e) {
    if (!menuOverlay || !menuOverlay.classList.contains('active')) {
      document.body.style.overflow = '';
    }
  });

  const syncMegaPanelTop = () => {
    const stack = document.getElementById('jcpHeaderStack');
    if (!stack) return;
    const rect = stack.getBoundingClientRect();
    const bottom = Math.ceil(rect.bottom);
    if (bottom > 0) {
      document.documentElement.style.setProperty('--jcp-mega-panel-top', `${bottom}px`);
    }
  };

  const initNavDropdown = (dropdown) => {
    if (!dropdown) return;

    const trigger = dropdown.querySelector('.nav-dropdown-trigger');
    const panelId = trigger ? trigger.getAttribute('aria-controls') : '';
    const menu = panelId
      ? document.getElementById(panelId)
      : dropdown.querySelector('.nav-mega-panel, .nav-dropdown-menu');
    if (!trigger || !menu) return;

    const isMega = dropdown.classList.contains('nav-mega') || menu.classList.contains('nav-mega-panel');

    const open = () => {
      document.querySelectorAll('.nav-mega.is-open, .nav-dropdown.is-open').forEach((other) => {
        if (other === dropdown) return;
        const otherTrigger = other.querySelector('.nav-dropdown-trigger');
        const otherPanelId = otherTrigger ? otherTrigger.getAttribute('aria-controls') : '';
        const otherMenu = otherPanelId
          ? document.getElementById(otherPanelId)
          : other.querySelector('.nav-mega-panel, .nav-dropdown-menu');
        if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
        if (otherMenu) otherMenu.setAttribute('hidden', '');
        other.classList.remove('is-open');
      });
      document.querySelectorAll('.jcp-header-stack > .jcp-desktop-mega-panels .nav-mega-panel').forEach((panel) => {
        if (panel === menu) return;
        panel.setAttribute('hidden', '');
      });
      syncMegaPanelTop();
      requestAnimationFrame(syncMegaPanelTop);
      trigger.setAttribute('aria-expanded', 'true');
      menu.removeAttribute('hidden');
      dropdown.classList.add('is-open');
      if (isMega) document.body.classList.add('nav-mega-open');
    };

    const close = () => {
      trigger.setAttribute('aria-expanded', 'false');
      menu.setAttribute('hidden', '');
      dropdown.classList.remove('is-open');
      if (!document.querySelector('.nav-mega.is-open')) {
        document.body.classList.remove('nav-mega-open');
      }
    };

    const isOpen = () => trigger.getAttribute('aria-expanded') === 'true';

    let hoverTimeout = null;

    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (isOpen()) {
        close();
      } else {
        open();
      }
    });

    const isWithinDropdown = (target) => {
      if (!target) return false;
      return dropdown.contains(target) || menu.contains(target);
    };

    dropdown.addEventListener('mouseenter', () => {
      if (hoverTimeout) clearTimeout(hoverTimeout);
      hoverTimeout = setTimeout(open, isMega ? 120 : 200);
    });

    dropdown.addEventListener('mouseleave', () => {
      if (hoverTimeout) clearTimeout(hoverTimeout);
      hoverTimeout = setTimeout(() => {
        if (isOpen()) close();
      }, isMega ? 180 : 150);
    });

    if (isMega) {
      menu.addEventListener('mouseenter', () => {
        if (hoverTimeout) clearTimeout(hoverTimeout);
      });

      menu.addEventListener('mouseleave', () => {
        if (hoverTimeout) clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(() => {
          if (isOpen()) close();
        }, 180);
      });
    }

    document.addEventListener('click', (e) => {
      if (isOpen() && !isWithinDropdown(e.target)) {
        close();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      if (isOpen()) {
        e.preventDefault();
        close();
        trigger.focus();
      }
    });

    const items = menu.querySelectorAll('[role="menuitem"], .nav-mega-card, .nav-dropdown-item');
    items.forEach((item, i) => {
      item.addEventListener('click', () => close());
      item.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' && i < items.length - 1) {
          e.preventDefault();
          items[i + 1].focus();
        } else if (e.key === 'ArrowUp' && i > 0) {
          e.preventDefault();
          items[i - 1].focus();
        } else if (e.key === 'Escape') {
          e.preventDefault();
          close();
        }
      });
    });

    trigger.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        trigger.click();
      }
      if (e.key === 'ArrowDown' && isOpen() && items.length) {
        e.preventDefault();
        items[0].focus();
      }
    });
  };

  const initResourcesDropdown = () => {
    initNavDropdown(document.getElementById('navResourcesDropdown'));
  };

  const initMegaMenus = () => {
    document.querySelectorAll('.nav-mega').forEach((dropdown) => initNavDropdown(dropdown));
  };

  const initScroll = () => {
    const onScroll = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  };

  const initNavLinks = () => {
    const root = document.getElementById('jcp-app');
    const pathname = (window.location.pathname || '/').replace(/\/$/, '') || '/';
    const page = root ? root.dataset.jcpPage : (pathname === '' || pathname === '/' || pathname === 'home' ? 'home' : 'page');
    const isHome = page === 'home';
    const isDirectory = page === 'directory';
    const isCompany = page === 'company';
    const isDemo = page === 'demo';
    const isDemoPage = ['demo', 'directory', 'company'].includes(page);
    const badge = document.getElementById('jcpHeaderIndicator');
    const mobileBadge = document.getElementById('jcpMobileBadge');

    if (badge) {
      badge.classList.toggle('is-hidden', !isDemoPage);
      if (isCompany && badge.textContent.trim() !== 'Contractor Profile (coming soon)') {
        badge.textContent = 'Contractor Profile (coming soon)';
      } else if (isDirectory && badge.textContent.trim() !== 'Directory (coming soon)') {
        badge.textContent = 'Directory (coming soon)';
      } else if (isDemo && badge.textContent.trim() !== 'Interactive Demo') {
        badge.textContent = 'Interactive Demo';
      }
    }
    if (mobileBadge) {
      mobileBadge.classList.toggle('is-hidden', !isDemoPage);
      if (isCompany && mobileBadge.textContent.trim() !== 'Contractor Profile (coming soon)') {
        mobileBadge.textContent = 'Contractor Profile (coming soon)';
      } else if (isDirectory && mobileBadge.textContent.trim() !== 'Directory (coming soon)') {
        mobileBadge.textContent = 'Directory (coming soon)';
      } else if (isDemo && mobileBadge.textContent.trim() !== 'Interactive Demo') {
        mobileBadge.textContent = 'Interactive Demo';
      }
    }

    document.querySelectorAll('[data-home-anchor]').forEach((link) => {
      const anchor = link.getAttribute('data-home-anchor');
      if (!anchor) return;
      if (isDirectory && anchor === '#how-it-works') {
        // On directory page, link to the how-it-works section on the same page
        link.setAttribute('href', '#how-it-works');
      } else {
        link.setAttribute('href', isHome ? anchor : `/#${anchor.replace('#', '')}`);
      }
    });

    const setActiveByPage = () => {
      document.querySelectorAll('.nav-link').forEach((link) => link.classList.remove('is-active'));
      document.querySelectorAll('.mobile-nav-link').forEach((link) => link.classList.remove('is-active'));
      const resourcesTrigger = document.getElementById('navResourcesTrigger');
      const featuresTrigger = document.getElementById('navFeaturesTrigger');
      const tradeTrigger = document.getElementById('navByTradeTrigger');
      if (resourcesTrigger) resourcesTrigger.classList.remove('is-active');
      if (featuresTrigger) featuresTrigger.classList.remove('is-active');
      if (tradeTrigger) tradeTrigger.classList.remove('is-active');
      const mobileResourcesSummary = document.querySelector('.mobile-nav-resources-summary');
      const mobileFeaturesSummary = document.querySelector('#mobileNavFeatures .mobile-nav-panel-summary');
      const mobileTradeSummary = document.querySelector('#mobileNavTrades .mobile-nav-panel-summary');
      if (mobileResourcesSummary) mobileResourcesSummary.classList.remove('is-active');
      if (mobileFeaturesSummary) mobileFeaturesSummary.classList.remove('is-active');
      if (mobileTradeSummary) mobileTradeSummary.classList.remove('is-active');

      const pathname = (window.location.pathname || '/').replace(/\/$/, '') || '/';

      // Primary nav pages: how-it-works, features, industries, pricing (by data-page or pathname)
      const primaryPages = ['how-it-works', 'features', 'industries', 'pricing'];
      const isIndustriesPage = page === 'industries' || pathname === '/industries' || pathname.indexOf('/industries/') === 0;
      const activePrimary = primaryPages.find((p) => {
        if (p === 'industries') return isIndustriesPage;
        return page === p || pathname === '/' + p;
      });
      if (activePrimary) {
        if (activePrimary === 'industries') {
          if (tradeTrigger) tradeTrigger.classList.add('is-active');
          if (mobileTradeSummary) mobileTradeSummary.classList.add('is-active');
        } else {
          document.querySelectorAll('.nav-link[data-page="' + activePrimary + '"]').forEach((link) => link.classList.add('is-active'));
          document.querySelectorAll('.mobile-nav-link[data-page="' + activePrimary + '"]').forEach((link) => link.classList.add('is-active'));
        }
        return;
      }

      // Resources dropdown: show active when on Blog, Help Center, or Contact (not Directory)
      const isBlogPage = page === 'blog' || pathname === '/blog' || (document.body && document.body.classList.contains('blog'));
      const isHelpPage = page === 'help' || pathname === '/help';
      const isContactPage = page === 'contact' || pathname === '/contact';
      const isReferralPage = page === 'referral-program' || pathname === '/referral-program';
      const isResourcesPage = isBlogPage || isHelpPage || isContactPage || isReferralPage;
      if (isResourcesPage) {
        if (resourcesTrigger) resourcesTrigger.classList.add('is-active');
        if (mobileResourcesSummary) mobileResourcesSummary.classList.add('is-active');
        return;
      }

      // Homepage: keep anchor link active when hash matches (demo/directory modes may still use data-home-anchor)
      if (isHome) {
        const hash = window.location.hash || '';
        if (hash) {
          const selector = `[data-home-anchor="${hash}"]`;
          document.querySelectorAll(`.nav-link${selector}`).forEach((link) => link.classList.add('is-active'));
          document.querySelectorAll(`.nav-dropdown-trigger${selector}`).forEach((link) => link.classList.add('is-active'));
          document.querySelectorAll(`.mobile-nav-link${selector}`).forEach((link) => link.classList.add('is-active'));
          if (hash === '#features' && featuresTrigger) featuresTrigger.classList.add('is-active');
          if (hash === '#features' && mobileFeaturesSummary) mobileFeaturesSummary.classList.add('is-active');
        }
      }
    };

    setActiveByPage();
    window.addEventListener('hashchange', setActiveByPage);
  };

  initMobileMenu();
  initScroll();
  initNavLinks();
  syncMegaPanelTop();
  window.addEventListener('resize', syncMegaPanelTop);
  window.addEventListener('scroll', syncMegaPanelTop, { passive: true });
  initMegaMenus();
  initResourcesDropdown();

  // Single delegated listener for CTA click tracking (Matomo)
  document.addEventListener('click', function (e) {
    var el = e.target && e.target.closest ? e.target.closest('a, button') : null;
    if (!el) return;
    var ctaName = el.getAttribute('data-cta');
    var href = el.getAttribute('href');
    var isTargetHref = false;
    var isReferralOutbound = false;
    var isOnboardingOutbound = false;
    if (el.tagName === 'A' && href) {
      var path = (href.charAt(0) === '/' ? href.split('?')[0] : (function () { try { return new URL(href, window.location.href).pathname; } catch (err) { return href; } })()).replace(/\/$/, '') || '/';
      var host = '';
      if (href.charAt(0) !== '/') {
        try { host = new URL(href, window.location.href).hostname.toLowerCase(); } catch (err) {}
      }
      isTargetHref = path === '/demo' || path === '/referral-program' || path === '/industries' || path.indexOf('/industries/') === 0;
      isReferralOutbound = host.indexOf('firstpromoter.com') !== -1;
      isOnboardingOutbound = host.indexOf('jobcapturepro.com') !== -1 && path.indexOf('/onboarding') === 0;
    }
    if (!ctaName && !isTargetHref && !isReferralOutbound && !isOnboardingOutbound) return;
    ctaName = ctaName || (isReferralOutbound ? 'Join Referral Program' : isOnboardingOutbound ? 'Start free trial' : (el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 80) || 'CTA');
    var pathname = (window.location.pathname || '/').replace(/\/$/, '') || '/';
    var ctaLocation = el.getAttribute('data-cta-location') || (el.closest('header') || el.closest('#jcpGlobalHeader') ? 'header' : el.closest('footer') ? 'footer' : pathname === '/' || pathname === '/home' ? 'homepage' : pathname === '/referral-program' ? 'referral_program' : 'page');
    try {
      if (typeof _paq !== 'undefined') {
        _paq.push(['trackEvent', 'CTA Clicked', ctaName, ctaLocation]);
        if (isOnboardingOutbound) {
          _paq.push(['trackEvent', 'Onboarding CTA', ctaName, ctaLocation]);
        }
      }
    } catch (err) {}
  });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
