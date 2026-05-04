(() => {
  const scriptSrc = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
  const fallbackBase = scriptSrc.includes('/core/')
    ? scriptSrc.split('/core/')[0]
    : '';
  const assetBase = () => window.JCP_ASSET_BASE || fallbackBase;
  const icon = (name) => `${assetBase()}/shared/assets/icons/lucide/${name}.svg`;

  // Pricing data with monthly and yearly prices
  // Features can be strings or { text: string, tooltip: string } for items with tooltips
  const hrefEarlyAccess = '/early-access';
  const hrefEarlyBird = '/early-access?coupon=earlybird';

  const pricingData = {
    starter: {
      monthly: 99,
      yearly: 79,
      name: 'Starter',
      description: 'Single-location teams that want proof and publishing without friction',
      locationRibbon:
        'One operating location · upgrade to Scale to add offices or trucks under one account.',
      pill: 'Build proof & trust fast',
      features: [
        { text: 'Mobile App workflow', tooltip: 'Photo check-ins → AI content, GPS tagging' },
        { text: 'SEO-optimized check-ins', tooltip: 'WebP, filenames, alt text, schema, EXIF GEO' },
        'WordPress plugin (single site)',
        'Manual review links & QR after check-ins',
        'Company dashboard (core view)'
      ]
    },
    scale: {
      monthly: 249,
      yearly: 199,
      name: 'Scale',
      description: 'Growing teams ready for automation and steady visibility everywhere',
      locationRibbon:
        'Listed price covers your first location · add more for $100/mo each · up to 3 locations on Scale.',
      pill: 'Automate CRM, reviews & posts',
      features: [
        'Everything in Starter, plus:',
        { text: 'CRM integrations', tooltip: 'HouseCall Pro (+ photos), Workiz, QuickBooks, CompanyCam' },
        { text: 'Automated SMS/email reviews', tooltip: '+ smart routing, reminders & branded sender' },
        { text: 'Auto social publishing', tooltip: 'Facebook, Instagram & X with AI captions' },
        'Google Business Profile auto-posting',
        { text: 'Monthly GeoGrid reports', tooltip: 'LocalFalcon delivery per location' },
        'Org tools: roles, lite onboarding assistance'
      ],
      featured: true
    },
    enterprise: {
      monthly: 399,
      yearly: 319,
      name: 'Enterprise',
      description: 'Agencies & multi-location ops that need APIs, bespoke CRM routes & white glove support',
      locationRibbon:
        'Listed price covers your first location · add unlimited extra locations · $100/mo each.',
      pill: 'APIs, integrations & rollup reporting',
      features: [
        'Everything in Scale, plus:',
        { text: 'Custom CRM integrations', tooltip: 'Any CRM exposing a viable API' },
        'Org-level dashboards & consolidated reporting across locations',
        { text: 'User roles & permissions', tooltip: 'Admin, ops, crews, partners' },
        'API tokens (org scoped or location scoped)',
        { text: 'Custom branding / white-label', tooltip: 'Productized on Enterprise' },
        { text: 'Migration + data hygiene support', tooltip: 'Hands-on onboarding & rollout' },
        'Dedicated Customer Success Manager'
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
      answer: 'Most teams are capturing check-ins inside of a few days once WordPress access and accounts are wired. Larger rollouts involving multiple locations or bespoke CRM integrations follow a guided playbook so nothing breaks midseason.'
    },
    {
      id: 'faq-pricing-integrations',
      question: 'What integrations ship today?',
      answer: 'HouseCall Pro with photos, CompanyCam (photos tied back to CRM data), Workiz, and QuickBooks. Enterprise scopes custom connectors for CRMs exposing a workable API.'
    },
    {
      id: 'faq-pricing-locations',
      question: 'How does multi-location billing work?',
      answer: 'Each plan assumes one operating storefront or yard on the marquee price. Expansion locations are billed at another $100 per month per spot so budgeting stays predictable. Starter remains one location until you graduate to Scale (up to three total) or Enterprise (unlimited rollout).'
    },
    {
      id: 'faq-pricing-earlybird',
      question: 'What is the early bird Enterprise offer?',
      answer: 'Use coupon code earlybird when you subscribe to lock Enterprise-grade access for $125 per month compared with the upcoming $399 list price. Mention the code inside the onboarding form so your contract reflects the founders rate.'
    },
    {
      id: 'faq-pricing-pricing',
      question: 'What is included per tier?',
      answer: 'Starter leans entirely on technician driven capture plus WordPress. Scale unlocks CRM automation, GBP + social autoposting, richer review sequences, monthly GeoGrid reports, and lite onboarding touches. Enterprise adds APIs, consolidated reporting across locations, migrations, branding, white glove onboarding, and a dedicated Success Manager.'
    },
    {
      id: 'faq-pricing-cancel',
      question: 'Can I change plans or cancel?',
      answer: 'Yes. Upgrades and downgrades are prorated to the billing cycle whenever possible and you can pause or cancel with written notice—we would rather steer you correctly than trap you.'
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

  /** @returns {string} */
  function buildPricingComparison(iconFn) {
    const chk = `<div><img src="${iconFn('check')}" class="lucide-icon lucide-icon-xs" alt="Included"></div>`;
    const no = `<div><img src="${iconFn('x')}" class="lucide-icon lucide-icon-xs" alt="Not included"></div>`;
    /** @param {string} txt */
    const txt = (t) => `<div class="jcp-compare-cell-text">${escapeHtml(t)}</div>`;
    /** @param {string} title */
    const band = (title) => `<div class="jcp-compare-section-banner">${escapeHtml(title)}</div>`;

    const row = (feature, s, mid, ent) =>
      `<div class="jcp-compare-row">
        <div>${escapeHtml(feature)}</div>
        ${typeof s === 'string' ? txt(s) : s}
        ${typeof mid === 'string' ? txt(mid) : mid}
        ${typeof ent === 'string' ? txt(ent) : ent}
      </div>`;

    return `
      ${band('Core workflow')}
      ${row('Mobile app photo & AI generated check-ins', chk, chk, chk)}
      ${row('SEO optimized posts (webp, filenames, schema)', chk, chk, chk)}
      ${row('WordPress plugin integration', chk, chk, chk)}
      ${row('Manual review QR links', chk, chk, chk)}
      ${row('Company dashboard', chk, chk, chk)}
      ${row('Single storefront included on list price', chk, chk, chk)}

      ${band('Automation & integrations')}
      ${row('CRM integrations (HouseCall Pro, Workiz, QuickBooks, CompanyCam)', no, chk, chk)}
      ${row('Enterprise-grade custom CRM connectors', no, no, chk)}
      ${row('Auto social scheduling (Facebook, Instagram, X)', no, chk, chk)}
      ${row('Google Business Profile auto-posting', no, chk, chk)}
      ${row('Automated review requests SMS/email plus follow-ups', no, chk, chk)}

      ${band('Analytics & reporting')}
      ${row('Check-in analytics', chk, chk, chk)}
      ${row('Monthly GeoGrid (Local Falcon)', no, chk, chk)}
      ${row('Keyword visibility exports', no, chk, chk)}
      ${row('Multi-location rollup reporting', no, no, chk)}

      ${band('Multi-location workspace')}
      ${row('Multi-location rollout', txt('Not included — single site tier'), txt('Yes · up to 3 locations'), txt('Yes · unlimited campuses'))}
      ${row('Each extra location adds', txt('Upgrade plan first'), txt('$100/mo'), txt('$100/mo'))}
      ${row('Org dashboards & permissions', no, chk, chk)}

      ${band('Brand, API & onboarding')}
      ${row('Human support', chk, txt('Priority queue'), txt('Dedicated CSM'))}
      ${row('Custom branding & white-label', no, no, chk)}
      ${row('API & token access', no, no, chk)}
      ${row('Custom integrations & migrations', no, no, chk)}
      ${row('Guided onboarding', no, txt('Lite enablement'), txt('White-glove launch'))}`;
  }

  function buildPricingDeepDive() {
    const integrations = `
      <p class="jcp-pricing-integrations-note"><strong>In production today:</strong> HouseCall Pro (CRM plus photos via CompanyCam), CompanyCam standalone, Workiz, QuickBooks Enterprise unlocks bespoke CRM wiring.</p>
      <ul>
        <li>HouseCall Pro pairing with CompanyCam merges CRM metadata with onsite photography.</li>
        <li>Photo-only fleets still sync because technicians keep capturing while CRM handles workflow.</li>
        <li>Enterprise orchestrates integrations for any cloud CRM exposing an API surface.</li>
      </ul>`;

    const geogridHref = 'https://app.screencast.com/atN6N4TOcWHLR';

    return `
      <div class="jcp-pricing-deep-dive jcp-container">
        <div class="rankings-header">
          <h2>Everything inside JobCapturePro</h2>
          <p class="rankings-subtitle">Use this playbook with leadership, finance, and marketing—every bullet maps to the feature matrix above.</p>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>1 · Capture methods</h3>
          <p><strong>Technician app.</strong> Upload a jobsite photo, JobCapturePro tags GPS, filters sensitive shots, publishes AI narration, and stores proof automatically.</p>
          <p><strong>CRM automations.</strong> When ops marks a closed job, integrations pull invoices, crews, attachments, even CompanyCam reels to generate a richer check-in instantly.</p>
          ${integrations}
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>2 · Multi-location workspaces</h3>
          <p>Every storefront or yard inherits its own GeoGrid targets, GBP profile, integrations, planners, tokens, so corporate strategy never collides with local nuance.</p>
          <ul>
            <li>Geo rankings stay scoped per market.</li>
            <li>Tokens can authorize an entire franchise group or spotlight a lone pilot territory.</li>
          </ul>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>3 · SEO-first publishing</h3>
          <ul>
            <li>Images convert to lightning fast WebPs with GEO aware filenames and descriptive alt tags.</li>
            <li>EXIF payloads carry precise latitude and longitude stamps for blended local signals.</li>
            <li>AI guardrails purge customer artifacts, blurry duplicates, or unsafe rigging shots automatically.</li>
            <li>JSON-LD schema ships with each post so search engines ingest proof without manual tinkering.</li>
          </ul>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>4 · Website embeds today (WordPress tomorrow everywhere)</h3>
          <p>The WordPress plugin ships shortcodes like <strong>[checkins city="houston"]</strong> so city landing pages drip authentic projects without duplicating spreadsheets.</p>
          <p>Future CMS adapters lean on authenticated API feeds plus embed snippets for non WP stacks.</p>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>5 · JobCapturePro SEO directory</h3>
          <p>Customers earn a searchable profile tying every verified check-in together, stronger operators earn carousel placement plus authoritative backlinks inbound to their money pages.</p>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>6 · GeoGrid rankings from Local Falcon</h3>
          <p>Scale and Enterprise include monthly grids so marketing sees block by block swings after fresh content hits the SERPs.</p>
          <p class="jcp-pricing-integrations-note"><a href="${escapeAttr(geogridHref)}" target="_blank" rel="noopener">Open a sample GeoGrid recap</a> to share with leadership.</p>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>7 · Automated social & GBP distribution</h3>
          <ul>
            <li>Social autopilot rotates Facebook, Instagram, and X placements with captions drafted from your freshest proof.</li>
            <li>Google Business Profiles receive daily highlights sourced from rolling 24 hour job streams.</li>
          </ul>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>8 · Review acceleration</h3>
          <ul>
            <li>Technicians broadcast QR workflows or deeplinks with photos bundled for photo reviews automatically.</li>
            <li>CRM triggers drip SMS/email review asks with escalation guardrails tied to sentiment.</li>
            <li>Smart routing shields public listings from unhappy homeowners while prompting internal triage instantly.</li>
            <li>Follow ups stop after acknowledgement and each brand sends from trusted company numbers plus email domains.</li>
          </ul>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>9 · Admin cockpit</h3>
          <p>Finance, ops, marketers, crews, vendors each see scoped panels for users, GBP assets, integrations, approvals, transcripts, impersonation safeguards, escalations—all without hijacking technician apps.</p>
        </div>

        <div class="jcp-pricing-detail-block">
          <h3>10 · Plan ladder & migrations</h3>
          <p>Walk Starter to prove adoption, Scale to automate busywork everywhere, Enterprise when franchises expect APIs plus compliance heavy integrations.</p>
          <ul>
            <li>Listed rates always cite the first storefront—extra yards stay transparent at plus $100 per month.</li>
            <li>Early adopters layering Enterprise workloads should stack the founders coupon outlined above.</li>
          </ul>
        </div>
      </div>`;
  }

  // Billing period state (module-level)
  let isYearly = false;

  // Render pricing card
  const renderPricingCard = (plan, isYearly, planKey) => {
    const hasPricing = plan.monthly !== undefined;
    const currentPrice = isYearly ? plan.yearly : plan.monthly;
    const discount = hasPricing ? calculateDiscount(plan.monthly, plan.yearly) : 0;
    const featuredClass = plan.featured ? ' jcp-pricing-featured' : '';
    const tagHTML = plan.featured ? '<div class="jcp-plan-tag">Most popular</div>' : '';
    const locRibbon =
      typeof plan.locationRibbon === 'string' && plan.locationRibbon
        ? `<p class="jcp-plan-location-ribbon">${escapeHtml(plan.locationRibbon)}</p>`
        : '';
    const earlyBirdPromo =
      planKey === 'enterprise'
        ? `<div class="jcp-plan-earlybird" role="note"><strong>Early bird founders rate:</strong> Enterprise-level access locks at ${formatPrice(125)} /month when billing uses coupon code <strong>earlybird</strong> (${formatPrice(399)} list price).</div>`
        : '';
    const primaryCta = planKey === 'enterprise' ? hrefEarlyBird : hrefEarlyAccess;
    const ctaLabel =
      planKey === 'enterprise' ? 'Claim Enterprise founders rate' : 'Join early access';

    return `
      <article class="jcp-pricing-card${featuredClass}" data-plan="${plan.name.toLowerCase()}">
        ${tagHTML}
        <div class="jcp-plan-head">
          <h3>${escapeHtml(plan.name)}</h3>
          <p>${escapeHtml(plan.description)}</p>
        </div>
        ${hasPricing ? `
          <div class="jcp-plan-pricing">
            <div class="jcp-plan-price">
              <span class="jcp-price-amount">${formatPrice(currentPrice)}</span>
              <span class="jcp-price-period">/month</span>
            </div>
            ${locRibbon}
            ${isYearly && discount > 0 ? `
              <div class="jcp-plan-discount">
                <span class="jcp-discount-badge">Save ${discount}%</span>
                <span class="jcp-original-price">${formatPrice(plan.monthly)}/month</span>
              </div>
            ` : ''}
            ${isYearly ? '<p class="jcp-billing-note">Billed annually</p>' : ''}
          </div>
        ` : ''}
        ${earlyBirdPromo}
        <div class="jcp-plan-pill">${escapeHtml(plan.pill)}</div>
        <ul class="jcp-plan-list">
          ${plan.features.map(renderFeature).join('')}
        </ul>
        <a class="btn ${plan.featured ? 'btn-primary' : 'btn-secondary'}" href="${primaryCta}">
          ${hasPricing ? ctaLabel : 'Contact sales'}
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
    const heroSubtitle =
      pageSupporting ||
      'Every marquee price covers one operating yard or shop. Stack more trucks or offices for $100 more per month, automate on Scale, graduate to Enterprise APIs when you are ready. Founders can still lock Enterprise horsepower for $125 with code earlybird.';

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
                ${renderPricingCard(pricingData.starter, isYearly, 'starter')}
                ${renderPricingCard(pricingData.scale, isYearly, 'scale')}
                ${renderPricingCard(pricingData.enterprise, isYearly, 'enterprise')}
              </div>
            </div>

            <div class="jcp-pricing-intro-callout">
              Simple math: <strong>one storefront on the price card</strong>. Every additional address, yard, or branded van line is <strong>$100 more per month</strong> so CFOs can forecast without surprise line items. Starter keeps you intentionally focused on a single campus while Scale and Enterprise unlock the growth paths your sales deck already promises.
            </div>

            <div class="jcp-pricing-notes">
              <p class="jcp-pricing-notes-label">How billing stacks</p>
              <ul class="jcp-pricing-notes-list">
                <li class="jcp-pricing-note-item">
                  <img src="${icon('map-pin')}" alt="" class="jcp-pricing-note-icon" width="18" height="18" />
                  <span>Listed price = your first operating location. Add more markets for $100/mo each.</span>
                </li>
                <li class="jcp-pricing-note-item">
                  <img src="${icon('users')}" alt="" class="jcp-pricing-note-icon" width="18" height="18" />
                  <span>Unlimited technicians and office staff per location—still not per-seat.</span>
                </li>
                <li class="jcp-pricing-note-item">
                  <img src="${icon('sparkles')}" alt="" class="jcp-pricing-note-icon" width="18" height="18" />
                  <span>Use <strong>earlybird</strong> at signup to hold Enterprise for $125/mo (normally $399).</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <section class="jcp-section rankings-section">
          <div class="jcp-container">
            <div class="rankings-header">
              <h2>Plan matrix</h2>
              <p class="rankings-subtitle">Feature-for-feature map for finance, ops, and marketing—includes the $100 per additional location rule baked into every column.</p>
            </div>
            <div class="jcp-compare-table">
              <div class="jcp-compare-row jcp-compare-head">
                <div>Feature</div>
                <div>Starter ($99)</div>
                <div>Scale ($249)</div>
                <div>Enterprise ($399)</div>
              </div>
              ${buildPricingComparison(icon)}
            </div>
            <div class="jcp-actions jcp-compare-actions">
              <a class="btn btn-primary" href="${hrefEarlyBird}">Start with founders pricing</a>
              <a class="btn btn-secondary" href="/demo">See the live demo</a>
            </div>
          </div>
        </section>

        <section class="jcp-section rankings-section">
          ${buildPricingDeepDive()}
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
            ${renderPricingCard(pricingData.starter, isYearly, 'starter')}
            ${renderPricingCard(pricingData.scale, isYearly, 'scale')}
            ${renderPricingCard(pricingData.enterprise, isYearly, 'enterprise')}
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
