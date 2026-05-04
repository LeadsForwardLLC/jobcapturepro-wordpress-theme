(() => {
  const scriptSrc = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
  const fallbackBase = scriptSrc.includes('/core/')
    ? scriptSrc.split('/core/')[0]
    : '';
  const assetBase = () => window.JCP_ASSET_BASE || fallbackBase;
  const icon = (name) => `${assetBase()}/shared/assets/icons/lucide/${name}.svg`;

  // Same as demo survey Step 1 – used when PHP config (business_type_options) is missing
  const FALLBACK_BUSINESS_TYPE_OPTIONS = [
    { label: 'Building & mechanical', options: [{ value: 'plumbing', label: 'Plumbing' }, { value: 'hvac', label: 'HVAC' }, { value: 'electrical', label: 'Electrical' }, { value: 'roofing', label: 'Roofing' }] },
    { label: 'General contracting & remodeling', options: [{ value: 'general-contractor', label: 'General Contractor' }, { value: 'handyman', label: 'Handyman' }, { value: 'remodeling', label: 'Remodeling / Renovation' }] },
    { label: 'Outdoor & property', options: [{ value: 'landscaping', label: 'Landscaping' }, { value: 'lawn-care', label: 'Lawn care' }, { value: 'tree-service', label: 'Tree service' }, { value: 'pest-control', label: 'Pest control' }, { value: 'fencing', label: 'Fencing' }] },
    { label: 'Cleaning & restoration', options: [{ value: 'carpet-cleaning', label: 'Carpet cleaning' }, { value: 'house-cleaning', label: 'House cleaning' }, { value: 'pressure-washing', label: 'Pressure washing' }, { value: 'painting', label: 'Painting (interior / exterior)' }] },
    { label: 'Other trades', options: [{ value: 'flooring', label: 'Flooring' }, { value: 'windows-doors', label: 'Windows & doors' }, { value: 'insulation', label: 'Insulation' }, { value: 'garage-doors', label: 'Garage doors' }, { value: 'pool-service', label: 'Pool service' }, { value: 'moving-junk', label: 'Moving / Junk removal' }] },
    { label: 'Other', options: [{ value: 'other', label: 'Other home service' }] },
  ];

  function escAttr(str) {
    if (str == null) return '';
    const s = String(str);
    return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/`/g, '&#96;');
  }

  function escText(str) {
    if (str == null) return '';
    const s = String(str);
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/`/g, '&#96;');
  }

  window.renderEarlyAccess = () => {
    const root = document.getElementById('jcp-app');
    if (!root) return;

    const c = window.JCP_EARLY_ACCESS_FORM || {};
    const pageTitle = (root.dataset.pageTitle || '').trim();
    const pageSupporting = (root.dataset.pageSupporting || '').trim();
    const headline = pageTitle || c.headline || 'Early Access';
    const subhead = pageSupporting || c.subhead || "You're early. That's a good thing. Get access before public launch with early-bird pricing and help shape the platform as it grows.";
    const buttonLabel = c.button_label || 'Join Early Access';
    const referralOptions = (c.referral_options || []).map(
      (o) => `<option value="${escAttr(o.value)}">${escText(o.label)}</option>`
    ).join('');
    const businessTypeOpts = (c.business_type_options && c.business_type_options.length) ? c.business_type_options : FALLBACK_BUSINESS_TYPE_OPTIONS;
    const businessTypeGroups = businessTypeOpts.map((grp) => {
      const opts = (grp.options || []).map(
        (o) => `<option value="${escAttr(o.value)}">${escText(o.label)}</option>`
      ).join('');
      return `<optgroup label="${escAttr(grp.label || '')}">${opts}</optgroup>`;
    }).join('');
    const whyOptions = (c.why_interested_options || []).map(
      (o) => `<label class="jcp-form-goal"><input type="checkbox" name="demo_goals" value="${escAttr(o.value)}"><span>${escText(o.label)}</span></label>`
    ).join('');
    const requirePhone = c.require_phone !== false;
    const requireCompany = c.require_company !== false;

    root.innerHTML = `
      <main class="jcp-marketing jcp-early-access-page">
        <section class="jcp-section rankings-section">
          <div class="jcp-container">
            <div class="rankings-header">
              <h1>${escText(headline)}</h1>
              <p class="rankings-subtitle">
                ${escText(subhead)}
              </p>
            </div>
          </div>
        </section>

        <section class="jcp-section jcp-form-section">
          <div class="jcp-container">
            <div class="jcp-form-wrapper">
              <form class="jcp-founding-form" id="foundingCrewForm" novalidate data-require-phone="${requirePhone ? '1' : '0'}" data-require-company="${requireCompany ? '1' : '0'}">
                <div class="jcp-form-error" id="earlyAccessFormError" role="alert" aria-live="polite" style="display: none;"></div>
                <div class="jcp-form-grid">
                  <div class="jcp-form-field">
                    <label for="founding-first-name">First name</label>
                    <input
                      id="founding-first-name"
                      type="text"
                      name="first_name"
                      placeholder="John"
                      required
                    />
                  </div>
                  <div class="jcp-form-field">
                    <label for="founding-last-name">Last name</label>
                    <input
                      id="founding-last-name"
                      type="text"
                      name="last_name"
                      placeholder="Smith"
                      required
                    />
                  </div>
                  <div class="jcp-form-field">
                    <label for="founding-email">Email address</label>
                    <input
                      type="email"
                      id="founding-email"
                      name="email"
                      placeholder="you@company.com"
                      required
                    />
                  </div>
                  <div class="jcp-form-field">
                    <label for="founding-phone">Phone</label>
                    <input
                      type="tel"
                      id="founding-phone"
                      name="phone"
                      placeholder="(555) 123-4567"
                      ${requirePhone ? 'required' : ''}
                    />
                  </div>
                  <div class="jcp-form-field">
                    <label for="founding-company">Business name</label>
                    <input
                      type="text"
                      id="founding-company"
                      name="company"
                      placeholder="Summit Plumbing"
                      ${requireCompany ? 'required' : ''}
                    />
                  </div>
                  <div class="jcp-form-field">
                    <label for="founding-business-type">Business type</label>
                    <select id="founding-business-type" name="business_type" required>
                      <option value="">Select your business type</option>
                      ${businessTypeGroups}
                    </select>
                  </div>
                </div>
                <div class="jcp-form-field jcp-form-field-full">
                  <span class="jcp-form-label">Why are you interested in JobCapturePro?</span>
                  <p class="jcp-form-field-helper" id="founding-demo-goals-helper">Select all that apply</p>
                  <div class="jcp-form-goals" id="founding-demo-goals" aria-describedby="founding-demo-goals-helper" role="group">
                    ${whyOptions}
                  </div>
                </div>
                <div class="jcp-form-field jcp-form-field-full">
                  <label for="founding-referral">How did you hear about us?</label>
                  <select id="founding-referral" name="referral_source" required>
                    <option value="">Select one</option>
                    ${referralOptions}
                  </select>
                </div>
                <div class="jcp-form-field jcp-form-field-full">
                  <label for="founding-coupon">Coupon or promo code (optional)</label>
                  <input
                    type="text"
                    id="founding-coupon"
                    name="coupon_code"
                    placeholder="earlybird"
                    autocomplete="off"
                  />
                  <p class="jcp-form-field-helper" id="founding-coupon-helper">Founding crew: Enterprise lists at $399/mo yet locks at $125/mo with code <strong>earlybird</strong> during subscribe.</p>
                </div>
                <div class="jcp-form-field jcp-form-field-full jcp-form-field-consent">
                  <label class="jcp-form-consent-label">
                    <input type="checkbox" name="consent" id="founding-consent" required />
                    <span>I agree to receive marketing emails from JobCapturePro. I can unsubscribe at any time.</span>
                  </label>
                </div>
                <div class="jcp-form-actions">
                  <button type="submit" class="btn btn-primary" id="earlyAccessSubmitBtn">
                    ${escText(buttonLabel)}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </section>

        <section class="jcp-section rankings-section">
          <div class="jcp-container">
            <div class="rankings-header">
              <h2>Early Access Benefits</h2>
            </div>
            <div class="ranking-factors-grid">
              <div class="ranking-factor-card">
                <div class="factor-icon-wrapper">
                  <img src="${icon('badge-check')}" class="factor-icon" alt="">
                </div>
                <h3 class="factor-title">Early-bird pricing</h3>
                <p class="factor-description">Lock in pricing before public launch. Your rate stays the same as the platform scales.</p>
              </div>
              <div class="ranking-factor-card">
                <div class="factor-icon-wrapper">
                  <img src="${icon('message-square')}" class="factor-icon" alt="">
                </div>
                <h3 class="factor-title">Direct feedback loop</h3>
                <p class="factor-description">Your input shapes the roadmap. Work directly with the team building features you need.</p>
              </div>
              <div class="ranking-factor-card">
                <div class="factor-icon-wrapper">
                  <img src="${icon('zap')}" class="factor-icon" alt="">
                </div>
                <h3 class="factor-title">Priority onboarding</h3>
                <p class="factor-description">Get hands-on setup support so your team can start using the platform quickly.</p>
              </div>
            </div>
          </div>
        </section>

        <section class="jcp-section rankings-section">
          <div class="jcp-container">
            <div class="rankings-cta">
              <div class="cta-content">
                <h3>See how it works</h3>
                <p class="cta-paragraph">Preview how JobCapturePro would publish your work across Google, your website, reviews, and the public directory.</p>
              </div>
              <div class="cta-button-wrapper">
                <a class="btn btn-primary rankings-cta-btn" href="/demo">See your business in the live demo</a>
                <p class="cta-note">No signup required. Takes two minutes.</p>
              </div>
            </div>
          </div>
        </section>
      </main>
    `;

    initMarketingNav();
    initFoundingForm();
  };

  function initFoundingForm() {
    const form = document.getElementById('foundingCrewForm');
    if (!form) return;

    const errorEl = document.getElementById('earlyAccessFormError');
    const submitBtn = document.getElementById('earlyAccessSubmitBtn');

    // Prefill from Demo Survey submission (localStorage key: jcp_early_access_prefill).
    try {
      const raw = localStorage.getItem('jcp_early_access_prefill');
      if (raw) {
        const prefill = JSON.parse(raw);
        if (prefill && typeof prefill === 'object') {
          const set = (name, val) => {
            const el = form.querySelector('[name="' + name + '"]');
            if (el && val != null && val !== '') el.value = String(val);
          };
          set('first_name', prefill.first_name);
          set('last_name', prefill.last_name);
          set('email', prefill.email);
          set('company', prefill.company);
          set('business_type', prefill.business_type);

          // Survey uses short values (calls, google); Early Access uses full labels.
          const surveyToEa = {
            calls: 'More inbound calls',
            google: 'Better Google visibility',
            reviews: 'More customer reviews',
            trust: 'Stronger website trust',
            busywork: 'Less marketing busywork',
            showcase: 'Showcase my work',
          };
          const demo_goals = prefill.demo_goals || [];
          const eaValues = demo_goals.map((v) => surveyToEa[v] || v).filter(Boolean);
          if (eaValues.length) {
            form.querySelectorAll('input[name="demo_goals"]').forEach((cb) => {
              cb.checked = eaValues.indexOf(cb.value) !== -1;
            });
          }
        }
      }
    } catch (e) {
      // no-op
    }

    try {
      var params = new URLSearchParams(window.location.search);
      var qpCoupon = params.get('coupon');
      if (qpCoupon) {
        var couponInput = document.getElementById('founding-coupon');
        if (couponInput) couponInput.value = qpCoupon;
      }
    } catch (e) {
      // no-op
    }

    function showError(msg) {
      if (errorEl) {
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
      }
    }

    function hideError() {
      if (errorEl) {
        errorEl.textContent = '';
        errorEl.style.display = 'none';
      }
    }

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      hideError();

      const consent = form.querySelector('#founding-consent');
      if (!consent || !consent.checked) {
        showError('Please agree to the marketing consent to continue.');
        return;
      }

      const first_name = (form.querySelector('[name="first_name"]') || {}).value || '';
      const last_name = (form.querySelector('[name="last_name"]') || {}).value || '';
      const company = (form.querySelector('[name="company"]') || {}).value || '';
      const email = (form.querySelector('[name="email"]') || {}).value || '';
      const phone = (form.querySelector('[name="phone"]') || {}).value || '';
      const demoGoalsCheckboxes = form.querySelectorAll('input[name="demo_goals"]:checked');
      const demo_goals = Array.from(demoGoalsCheckboxes).map(function (cb) { return cb.value; }).filter(Boolean);
      const referral_source = (form.querySelector('[name="referral_source"]') || {}).value || '';
      const business_type = (form.querySelector('[name="business_type"]') || {}).value || '';
      const coupon_code = ((form.querySelector('[name="coupon_code"]') || {}).value || '').trim();

      const requirePhone = form.getAttribute('data-require-phone') === '1';
      const requireCompany = form.getAttribute('data-require-company') === '1';

      if (!first_name.trim() || !last_name.trim() || !email.trim() || !demo_goals.length || !referral_source || !business_type.trim()) {
        showError('Please fill in all required fields, including first name, last name, at least one "Why are you interested" option, and your business type.');
        return;
      }
      if (requireCompany && !company.trim()) {
        showError('Please enter your business name.');
        return;
      }
      if (requirePhone && !phone.trim()) {
        showError('Please enter your phone number.');
        return;
      }

      if (!submitBtn) return;
      submitBtn.disabled = true;

      const c = window.JCP_EARLY_ACCESS_FORM || {};
      const restUrl = c.rest_url || (window.JCP_CONFIG && window.JCP_CONFIG.baseUrl ? window.JCP_CONFIG.baseUrl.replace(/\/?$/, '') + '/wp-json/jcp/v1/early-access-submit' : '/wp-json/jcp/v1/early-access-submit');

      fetch(restUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          first_name: first_name.trim(),
          last_name: last_name.trim(),
          company: company.trim(),
          email: email.trim(),
          phone: phone.trim(),
          business_type: business_type.trim(),
          demo_goals: demo_goals,
          referral_source: referral_source.trim(),
          coupon_code: coupon_code,
        }),
      })
        .then((res) => {
          const ok = res.status === 200 || res.status === 201;
          return res.json().then((data) => ({ ok, data })).catch(() => ({ ok, data: {} }));
        })
        .then(({ ok, data }) => {
          if (ok) {
            try {
              const prefill = {
                first_name: first_name.trim(),
                last_name: last_name.trim(),
                email: email.trim(),
                company: company.trim(),
                business_type: business_type.trim(),
                demo_goals: demo_goals,
              };
              localStorage.setItem('jcp_demo_survey_prefill', JSON.stringify(prefill));
            } catch (e) {
              // no-op
            }
            let redirect = (window.JCP_EARLY_ACCESS_FORM || {}).success_redirect || '/early-access-success/';
            const demoSession = new URLSearchParams(window.location.search).get('demo_session');
            if (demoSession) {
              redirect += (redirect.indexOf('?') >= 0 ? '&' : '?') + 'demo_session=' + encodeURIComponent(demoSession);
            }
            window.location.href = redirect;
            return;
          }
          submitBtn.disabled = false;
          showError(data.message || 'Something went wrong. Please try again.');
        })
        .catch(() => {
          submitBtn.disabled = false;
          showError('Something went wrong. Please try again.');
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
