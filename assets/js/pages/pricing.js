(() => {
  const scriptSrc = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
  const fallbackBase = scriptSrc.includes('/core/')
    ? scriptSrc.split('/core/')[0]
    : '';
  const assetBase = () => window.JCP_ASSET_BASE || fallbackBase;
  const icon = (name) => `${assetBase()}/shared/assets/icons/lucide/${name}.svg`;

  const onboardingBaseHref =
    typeof window !== 'undefined' && window.JCP_ONBOARDING && window.JCP_ONBOARDING.url
      ? window.JCP_ONBOARDING.url
      : 'https://app.jobcapturepro.com/onboarding?sessionId=75ad8454-312e-4224-95b7-8f48f5cd0277&step=1';
  const onboardingUtmFallback = { utm_source: 'jobcapturepro.com', utm_medium: 'website', utm_campaign: 'onboarding' };
  const onboardingCtaHref = (() => {
    try {
      const u = onboardingBaseHref.startsWith('http')
        ? new URL(onboardingBaseHref)
        : new URL(onboardingBaseHref, window.location.origin);
      const defs =
        window.JCP_ONBOARDING && window.JCP_ONBOARDING.utmDefaults && typeof window.JCP_ONBOARDING.utmDefaults === 'object'
          ? window.JCP_ONBOARDING.utmDefaults
          : onboardingUtmFallback;
      Object.keys(defs).forEach((key) => {
        const val = defs[key];
        if (val !== undefined && val !== null && String(val).trim() !== '') u.searchParams.set(key, String(val));
      });
      u.searchParams.set('utm_content', 'pricing');
      return u.toString();
    } catch (e) {
      return onboardingBaseHref;
    }
  })();

  // Pricing data — prefer shared PHP catalog (JCP_PRICING) so sales tool + pricing stay aligned.
  const pricingDataFallback = {
    starter: {
      monthly: 99,
      yearly: 79,
      name: 'Starter',
      description: 'Everything a single-location business needs to turn check-ins into reviews.',
      pill: '1 location only',
      allows_additional_locations: false,
      extra_location_fee: null,
      features: [
        { text: '1 location included', tooltip: 'Starter includes one operating location and cannot add more. Upgrade to Scale or Enterprise to add locations.' },
        { text: 'Unlimited check-in tracking', tooltip: 'Track unlimited jobs/check-ins for your included location.' },
        { text: 'On-site review requests', tooltip: 'After each job, your crew shows a QR code or sends a link so the customer can leave a review while the experience is fresh.' },
        { text: 'Team activity feed', tooltip: 'See check-ins and activity across your team in one place.' },
        { text: 'Advanced analytics', tooltip: 'Reporting across check-ins, reviews, and performance.' },
        'Email support'
      ]
    },
    scale: {
      monthly: 249,
      yearly: 199,
      name: 'Scale',
      description: 'Built for multi-location brands ready to grow without adding overhead.',
      pill: 'Most popular',
      allows_additional_locations: true,
      extra_location_fee: 150,
      features: [
        'Everything in Starter',
        { text: 'Add locations anytime (+$150/mo each)', tooltip: 'Add more operating locations under one account at $150/month each.' },
        { text: '1 Local Falcon keyword tracked', tooltip: 'Track one keyword in Local Falcon to see your Google Maps ranking and coverage radius.' },
        { text: 'CRM integration', tooltip: 'Connect systems like Housecall Pro, Jobber, ServiceTitan, and CompanyCam.' },
        'WordPress plugin',
        'Social Media posting',
        'Google Business Profile posting',
        { text: 'Priority support', tooltip: 'Faster responses and escalation for time-sensitive issues.' }
      ],
      featured: true
    },
    enterprise: {
      monthly: 399,
      yearly: 319,
      name: 'Enterprise',
      description: 'Dedicated support and custom connectivity for multi-location teams.',
      pill: 'Enterprise',
      allows_additional_locations: true,
      extra_location_fee: 100,
      features: [
        'Everything in Scale',
        { text: 'Add locations anytime (+$100/mo each)', tooltip: 'Add more operating locations under one account at $100/month each.' },
        { text: 'Custom integrations', tooltip: 'Custom API integrations and tailored workflows for complex stacks.' },
        { text: 'Dedicated account manager', tooltip: 'A single point of contact for rollout, strategy, and ongoing success.' },
        { text: 'SLA guarantee', tooltip: 'Priority handling with service-level commitments for support/uptime.' }
      ]
    }
  };
  const pricingData =
    window.JCP_PRICING && window.JCP_PRICING.plans && typeof window.JCP_PRICING.plans === 'object'
      ? { ...pricingDataFallback, ...window.JCP_PRICING.plans }
      : pricingDataFallback;
  const extraLocationFees = (() => {
    const fromPhp = window.JCP_PRICING && window.JCP_PRICING.extraLocationFees;
    if (fromPhp && typeof fromPhp === 'object') {
      return {
        starter: fromPhp.starter == null ? null : Number(fromPhp.starter),
        scale: fromPhp.scale == null ? 150 : Number(fromPhp.scale),
        enterprise: fromPhp.enterprise == null ? 100 : Number(fromPhp.enterprise),
      };
    }
    return {
      starter: null,
      scale: Number(pricingData.scale?.extra_location_fee) || 150,
      enterprise: Number(pricingData.enterprise?.extra_location_fee) || 100,
    };
  })();
  const scaleLocationFee = extraLocationFees.scale;
  const enterpriseLocationFee = extraLocationFees.enterprise;

  // Escape HTML for tooltip content (safe for innerHTML)
  const escapeHtml = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  // Escape for HTML attribute (e.g. aria-label)
  const escapeAttr = (s) => escapeHtml(s).replace(/"/g, '&quot;');

  // Render a single feature list item (string or { text, tooltip })
  const renderFeature = (feature) => {
    if (typeof feature === 'string') {
      return `<li class="jcp-plan-feature"><span class="jcp-plan-feature__label">${escapeHtml(feature)}</span></li>`;
    }
    const tip = escapeHtml(feature.tooltip);
    const ariaTip = escapeAttr(feature.tooltip);
    return `<li class="jcp-plan-feature jcp-plan-feature--has-tip"><span class="jcp-plan-feature__label">${escapeHtml(feature.text)}</span><span class="jcp-feature-tooltip-trigger" tabindex="0" role="button" aria-label="More info: ${ariaTip}"><img src="${icon('info')}" alt="" width="14" height="14" class="jcp-feature-tooltip-icon" /><span class="jcp-feature-tooltip-bubble">${tip}</span></span></li>`;
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
      answer: 'Starter is limited to one location and cannot add more. Scale lets you add locations at $150/month each and includes 1 Local Falcon keyword for Maps ranking and radius. Enterprise adds locations at $100/month each, with custom integrations and dedicated support.'
    },
    {
      id: 'faq-pricing-pricing',
      question: 'What is included in each plan?',
      answer: 'All plans include core features like photo capture, proof generation, basic publishing, and on-site QR review requests. Higher tiers add CRM integrations, social automation, and advanced reporting. See the comparison table above for details.'
    },
    {
      id: 'faq-pricing-trial',
      question: 'Is there a free trial?',
      answer: 'Yes. You can start a free 14-day trial with no credit card required. Explore the platform with your own jobs and cancel anytime before it converts.'
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
        <a class="btn ${plan.featured ? 'btn-primary' : 'btn-secondary'}" href="${escapeAttr(onboardingCtaHref)}">
          ${hasPricing ? 'Start free 14-day trial' : 'Contact sales'}
        </a>
        ${hasPricing ? '<p class="jcp-plan-cta-note">No credit card required</p>' : ''}
      </article>
    `;
  };

  window.renderPricing = () => {
    const root = document.getElementById('jcp-app');
    if (!root) return;

    const pageTitle = (root.dataset.pageTitle || '').trim();
    const pageSupportingRaw = (root.dataset.pageSupporting || '').trim();
    const scrubRetiredPromo = (text) => {
      if (!text) return text;
      return text
        .replace(/Get early bird pricing and unlock the benefits of turning real work into reviews, visibility, and trust that drives inbound demand\./gi,
          'Start a free 14-day trial and turn real work into reviews, visibility, and trust that drives inbound demand.')
        .replace(/Get early bird pricing/gi, 'Start a free 14-day trial')
        .replace(/early bird pricing/gi, 'free 14-day trial pricing')
        .replace(/early bird/gi, 'free trial')
        .replace(/founding crew/gi, 'customers');
    };
    const pageSupporting = scrubRetiredPromo(pageSupportingRaw);
    const heroTitle = scrubRetiredPromo(pageTitle) || 'Choose the plan that matches your growth';
    const heroSubtitle = pageSupporting || 'Each tier aligns to business maturity and visibility goals. Start a free 14-day trial and turn real work into reviews, visibility, and trust that drives inbound demand.';

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
            <section class="jcp-pricing-extras" aria-label="Add-ons and plan details">
              <div class="jcp-pricing-extras__head">
                <p class="jcp-pricing-notes-label">Locations &amp; add-ons</p>
                <h3 class="jcp-addons__title">Grow locations when your plan allows it</h3>
                <p class="jcp-addons__sub"><strong>Starter</strong> is locked to 1 location. <strong>Scale</strong> adds locations at <strong>$${scaleLocationFee}/mo</strong> each. <strong>Enterprise</strong> adds locations at <strong>$${enterpriseLocationFee}/mo</strong> each.</p>
              </div>

              <div class="jcp-addons__grid jcp-addons__grid--three">
                <article class="jcp-addon-card jcp-addon-card--included">
                  <div class="jcp-addon-card__top">
                    <div class="jcp-addon-card__icon">
                      <img src="${icon('users')}" alt="" width="18" height="18" />
                    </div>
                    <div class="jcp-addon-card__meta">
                      <div class="jcp-addon-card__name">Unlimited users per location</div>
                      <div class="jcp-addon-card__note">Included in every plan</div>
                    </div>
                  </div>
                  <div class="jcp-addon-card__body">You are not billed per seat. Invite your whole team.</div>
                </article>

                <article class="jcp-addon-card">
                  <div class="jcp-addon-card__top">
                    <div class="jcp-addon-card__icon">
                      <img src="${icon('map')}" alt="" width="18" height="18" />
                    </div>
                    <div class="jcp-addon-card__meta">
                      <div class="jcp-addon-card__name">Additional Location</div>
                      <div class="jcp-addon-card__note">Scale $${scaleLocationFee}/mo · Enterprise $${enterpriseLocationFee}/mo</div>
                      <div class="jcp-addon-card__price">
                        <span class="jcp-addon-card__amount">From $${enterpriseLocationFee}</span><span class="jcp-addon-card__period">/mo</span>
                      </div>
                    </div>
                  </div>
                  <div class="jcp-addon-card__body">Not available on Starter. Add another operating location under the same organization on Scale or Enterprise.</div>
                </article>

                <article class="jcp-addon-card jcp-addon-card--enterprise">
                  <div class="jcp-addon-card__top">
                    <div class="jcp-addon-card__icon">
                      <img src="${icon('map-pin')}" alt="" width="18" height="18" />
                    </div>
                    <div class="jcp-addon-card__meta">
                      <div class="jcp-addon-card__name">Local Falcon keyword</div>
                      <div class="jcp-addon-card__note">Included on Scale+</div>
                      <div class="jcp-addon-card__price">
                        <span class="jcp-addon-card__amount">1</span><span class="jcp-addon-card__period">keyword</span>
                      </div>
                    </div>
                  </div>
                  <div class="jcp-addon-card__body">See your Google Maps ranking and coverage radius for one tracked keyword.</div>
                </article>
              </div>
            </section>
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
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Additional locations</div>
                <div>Not available</div>
                <div>+$${scaleLocationFee}/mo each</div>
                <div>+$${enterpriseLocationFee}/mo each</div>
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
                <div>On-site QR review requests</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Review link option</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
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
                <div>Local Falcon keyword tracking</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div>1 keyword</div>
                <div>1 keyword</div>
              </div>
              <div class="jcp-compare-row">
                <div>Rank tracking (Maps ranking + radius)</div>
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
                <div>Advanced analytics</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Local reporting dashboards</div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
              <div class="jcp-compare-row">
                <div>Multi-location dashboards</div>
                <div><img src="${icon('x')}" class="lucide-icon lucide-icon-xs" alt="Not available"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
                <div><img src="${icon('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>
              </div>
            </div>
            <div class="jcp-actions jcp-compare-actions">
              <a class="btn btn-primary" href="${escapeAttr(onboardingCtaHref)}">Start free 14-day trial</a>
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
