(() => {
  const scriptSrc = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
  const fallbackBase = scriptSrc.includes('/core/')
    ? scriptSrc.split('/core/')[0]
    : '';
  const assetBase = () => window.JCP_ASSET_BASE || fallbackBase;
  const icon = (name) => `${assetBase()}/shared/assets/icons/lucide/${name}.svg`;

  // Pricing data with monthly and yearly prices
  // Features can be strings or { text: string, tooltip: string } for items with tooltips
  const pricingData = {
    starter: {
      monthly: 99,
      yearly: 79, // 20% discount
      name: 'Starter',
      description: 'Single-location companies',
      pill: 'Build trust fast',
      features: [
        'Listed price covers 1 operating location',
        { text: 'Mobile app check-ins', tooltip: 'photo → AI content' },
        { text: 'SEO-optimized check-ins', tooltip: 'WebP images, filenames, schema' },
        'WordPress plugin (1 site)',
        'Review links + QR codes',
        'Company dashboard'
      ]
    },
    scale: {
      monthly: 249,
      yearly: 199, // 20% discount
      name: 'Scale',
      description: 'Growing teams needing automation',
      pill: 'Automate visibility',
      features: [
        'Everything in Starter, plus:',
        { text: 'CRM integrations', tooltip: 'Housecall Pro, Workiz, QuickBooks, CompanyCam' },
        { text: 'Automated review requests', tooltip: 'SMS/email + follow-ups' },
        { text: 'Auto-posting to social', tooltip: 'Facebook, Instagram, X' },
        'Google Business Profile auto-posting',
        { text: 'Monthly GeoGrid reports', tooltip: 'LocalFalcon' },
        'Add locations at $100/mo each beyond your first (up to 3 on Scale)'
      ],
      featured: true
    },
    enterprise: {
      monthly: 399,
      yearly: 319, // 20% discount
      name: 'Enterprise',
      description: 'Multi-location companies, franchises, agencies',
      pill: 'Scale across locations',
      features: [
        'Everything in Scale, plus:',
        'Listed price covers 1 location · add unlimited extras at $100/mo each',
        'Org-level dashboard',
        { text: 'User roles & permissions', tooltip: 'Admin, manager, and crew access levels' },
        'API access',
        { text: 'Custom integrations & migration', tooltip: 'API-based connectors, data migration support' },
        { text: 'White-glove onboarding', tooltip: 'Dedicated setup, training, and go-live support' },
        { text: 'Dedicated success manager', tooltip: 'Single point of contact for strategy and support' }
      ]
    }
  };

  // Escape HTML for tooltip content (safe for innerHTML)
  const escapeHtml = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  // Escape for HTML attribute (e.g. aria-label)
  const escapeAttr = (s) => escapeHtml(s).replace(/"/g, '&quot;');

  // Render a single feature list item (string or { text, tooltip })
  const renderFeature = (feature) => {
    if (typeof feature === 'string') {
      return `<li>${feature}</li>`;
    }
    const tip = escapeHtml(feature.tooltip);
    const ariaTip = escapeAttr(feature.tooltip);
    return `<li class="jcp-plan-feature-with-tooltip"><span>${feature.text}</span> <span class="jcp-feature-tooltip-trigger" tabindex="0" role="button" aria-label="More info: ${ariaTip}"><img src="${icon('info')}" alt="" width="14" height="14" class="jcp-feature-tooltip-icon" /><span class="jcp-feature-tooltip-bubble">${tip}</span></span></li>`;
  };

  // Pricing-specific FAQ items
  const pricingFAQItems = [
    {
      id: 'faq-pricing-setup',
      question: 'How fast can we launch?',
      answer: 'Most companies are live within a few days. We connect your website, set up your locations, and turn on the channels you want so job activity can start publishing immediately.'
    },
    {
      id: 'faq-pricing-integrations',
      question: 'What integrations do you support?',
      answer: 'JobCapturePro supports HouseCall Pro, CompanyCam, Workiz, and QuickBooks today. If you use a different system and it has an API, we can evaluate a custom integration for higher tier plans.'
    },
    {
      id: 'faq-pricing-locations',
      question: 'Can we use JobCapturePro for multiple locations?',
      answer: 'Yes. Each location can have its own Google Business Profile and connected social accounts, with organization level management for multi location teams.'
    },
    {
      id: 'faq-pricing-pricing',
      question: 'What is included in each plan?',
      answer: 'All plans include core features like photo capture, proof generation, and basic publishing. Higher tiers add CRM integrations, automated reviews, social automation, and advanced reporting. See the comparison table above for details.'
    },
    {
      id: 'faq-pricing-trial',
      question: 'Is there a free trial?',
      answer: 'We offer early access pricing for founding members. Contact us to learn more about current offers and see if you qualify for special pricing.'
    },
    {
      id: 'faq-pricing-cancel',
      question: 'Can I change plans or cancel?',
      answer: 'Yes, you can upgrade, downgrade, or cancel your plan at any time. Changes take effect at your next billing cycle.'
    }
  ];

  // Format price for display
  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(price);
  };

  // Calculate discount percentage
  const calculateDiscount = (monthly, yearly) => {
    return Math.round(((monthly * 12 - yearly * 12) / (monthly * 12)) * 100);
  };

  // Billing period state (module-level)
  let isYearly = false;

  // Render pricing card
  const renderPricingCard = (plan, isYearly) => {
    const hasPricing = plan.monthly !== undefined;
    const currentPrice = isYearly ? plan.yearly : plan.monthly;
    const discount = hasPricing ? calculateDiscount(plan.monthly, plan.yearly) : 0;
    const featuredClass = plan.featured ? ' jcp-pricing-featured' : '';
    const tagHTML = plan.featured ? '<div class="jcp-plan-tag">Most popular</div>' : '';
    
    return `
      <article class="jcp-pricing-card${featuredClass}" data-plan="${plan.name.toLowerCase()}">
        ${tagHTML}
        <div class="jcp-plan-head">
          <h3>${plan.name}</h3>
          <p>${plan.description}</p>
        </div>
        ${hasPricing ? `
          <div class="jcp-plan-pricing">
            <div class="jcp-plan-price">
              <span class="jcp-price-amount">${formatPrice(currentPrice)}</span>
              <span class="jcp-price-period">/${isYearly ? 'month' : 'month'}</span>
            </div>
            ${isYearly && discount > 0 ? `
              <div class="jcp-plan-discount">
                <span class="jcp-discount-badge">Save ${discount}%</span>
                <span class="jcp-original-price">${formatPrice(plan.monthly)}/month</span>
              </div>
            ` : ''}
            ${isYearly ? '<p class="jcp-billing-note">Billed annually</p>' : ''}
          </div>
        ` : ''}
        <div class="jcp-plan-pill">${plan.pill}</div>
        <ul class="jcp-plan-list">
          ${plan.features.map(renderFeature).join('')}
        </ul>
        <a class="btn ${plan.featured ? 'btn-primary' : 'btn-secondary'}" href="/early-access">
          ${hasPricing ? 'Join Early Access' : 'Contact sales'}
        </a>
      </article>
    `;
  };

  window.renderPricing = () => {
    const root = document.getElementById('jcp-app');
    if (!root) return;

    const pageTitle = (root.dataset.pageTitle || '').trim();
    const pageSupporting = (root.dataset.pageSupporting || '').trim();
    const heroTitle = pageTitle || 'Choose the plan that matches your growth';
    const heroSubtitle = pageSupporting || 'Each tier aligns to business maturity and visibility goals. Get early bird pricing and unlock the benefits of turning real work into reviews, visibility, and trust that drives inbound demand.';

    // Load FAQ component if available
    const faqHTML = typeof window.renderFAQ === 'function' 
      ? window.renderFAQ({
          title: 'Pricing FAQ',
          subtitle: 'Common questions about plans, pricing, and getting started.',
          items: pricingFAQItems,
          id: 'pricing-faq'
        })
      : '';


    root.innerHTML = `
      <main class="jcp-marketing jcp-pricing-page">
        <section class="jcp-section rankings-section">
          <div class="jcp-container">
            <div class="rankings-header">
              <h1>${escapeHtml(heroTitle)}</h1>
              <p class="rankings-subtitle">${escapeHtml(heroSubtitle)}</p>
            </div>
            
            <!-- Billing Toggle -->
            <div class="jcp-billing-toggle-wrapper">
              <div class="jcp-billing-toggle">
                <button class="jcp-toggle-option ${!isYearly ? 'active' : ''}" data-period="monthly">
                  Monthly
                </button>
                <button class="jcp-toggle-option ${isYearly ? 'active' : ''}" data-period="yearly">
                  Yearly
                  <span class="jcp-toggle-badge">Save up to 20%</span>
                </button>
              </div>
            </div>

            <div class="jcp-pricing-grid-container">
              <div class="jcp-pricing-grid">
                ${renderPricingCard(pricingData.starter, isYearly)}
                ${renderPricingCard(pricingData.scale, isYearly)}
                ${renderPricingCard(pricingData.enterprise, isYearly)}
              </div>
            </div>
            <div class="jcp-pricing-notes">
              <p class="jcp-pricing-notes-label">Location pricing</p>
              <ul class="jcp-pricing-notes-list">
                <li class="jcp-pricing-note-item">
                  <img src="${icon('map-pin')}" alt="" class="jcp-pricing-note-icon" width="18" height="18" />
                  <span>Each plan&apos;s advertised price covers <strong>one</strong> operating location.</span>
                </li>
                <li class="jcp-pricing-note-item">
                  <img src="${icon('dollar-sign')}" alt="" class="jcp-pricing-note-icon" width="18" height="18" />
                  <span>Each additional location is <strong>$100/month</strong> (priced per location).</span>
                </li>
                <li class="jcp-pricing-note-item">
                  <img src="${icon('users')}" alt="" class="jcp-pricing-note-icon" width="18" height="18" />
                  <span>Unlimited users per location · not billed per seat</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <section class="jcp-section rankings-section">
          <div class="jcp-container">
            <div class="rankings-header">
              <h2>Compare plans by outcome</h2>
              <p class="rankings-subtitle">Capture, publish, reviews, visibility, and reporting—organized for quick decisions.</p>
            </div>
            <div class="jcp-compare-table">
              <div class="jcp-compare-row jcp-compare-head">
                <div>Feature</div>
                <div>Starter</div>
                <div>Scale</div>
                <div>Enterprise</div>
              </div>

              <!-- Capture -->
              <div class="jcp-compare-row jcp-compare-group">
                <div>Photo capture</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>CRM integration</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Multi-location capture</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>

              <!-- Publish -->
              <div class="jcp-compare-row jcp-compare-group">
                <div>Website publishing</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Social publishing</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Google Business Profile automation</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>White-label publishing</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>

              <!-- Reviews -->
              <div class="jcp-compare-row jcp-compare-group">
                <div>Manual review requests</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Automated review sequences</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Custom review workflows</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>

              <!-- Visibility -->
              <div class="jcp-compare-row jcp-compare-group">
                <div>Directory listings</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Job map visibility</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Rank tracking</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Advanced visibility reporting</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>

              <!-- Reporting -->
              <div class="jcp-compare-row jcp-compare-group">
                <div>Email summaries</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Local reporting dashboards</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Multi-location dashboards</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
            </div>
            <div class="jcp-actions jcp-compare-actions">
              <a class="btn btn-primary" href="/early-access">Join Early Access</a>
              <a class="btn btn-secondary" href="/demo">See the Demo</a>
            </div>
          </div>
        </section>

        ${faqHTML}
      </main>
    `;

    initMarketingNav();
    initPricingToggle();
  };

  function initPricingToggle() {
    const root = document.getElementById('jcp-app');
    if (!root) return;

    const toggleOptions = root.querySelectorAll('.jcp-toggle-option');
    const pricingGridContainer = root.querySelector('.jcp-pricing-grid-container');
    
    if (!toggleOptions.length || !pricingGridContainer) return;

    toggleOptions.forEach(option => {
      option.addEventListener('click', () => {
        const period = option.dataset.period;
        isYearly = period === 'yearly';
        
        // Update toggle active states
        toggleOptions.forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');
        
        // Re-render pricing cards
        const grid = pricingGridContainer.querySelector('.jcp-pricing-grid');
        if (grid) {
          grid.innerHTML = `
            ${renderPricingCard(pricingData.starter, isYearly)}
            ${renderPricingCard(pricingData.scale, isYearly)}
            ${renderPricingCard(pricingData.enterprise, isYearly)}
          `;
        }
      });
    });
  }

  function initMarketingNav() {
    const menuToggle = document.getElementById('mobileMenuToggle');
    const menuClose = document.getElementById('mobileMenuClose');
    const menuOverlay = document.getElementById('mobileMenuOverlay');

    if (!menuToggle || !menuClose || !menuOverlay) return;

    menuToggle.addEventListener('click', () => {
      menuOverlay.classList.add('active');
      menuToggle.classList.add('active');
      document.body.style.overflow = 'hidden';
    });

    const closeMenu = () => {
      menuOverlay.classList.remove('active');
      menuToggle.classList.remove('active');
      document.body.style.overflow = '';
    };

    menuClose.addEventListener('click', closeMenu);
    menuOverlay.addEventListener('click', (e) => {
      if (e.target === menuOverlay) {
        closeMenu();
      }
    });

    document.querySelectorAll('.mobile-nav-link').forEach((link) => {
      link.addEventListener('click', () => closeMenu());
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menuOverlay.classList.contains('active')) {
        closeMenu();
      }
    });
  }
})();
