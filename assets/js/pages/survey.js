(() => {
  function initSurvey() {
  document.body.classList.add('survey-only');
  const steps = Array.from(document.querySelectorAll('.survey-step'));
  if (!steps.length) return;

  const progressWrap = document.querySelector('.survey-progress');
  const progressText = document.getElementById('surveyProgressText');
  const progressFill = document.getElementById('surveyProgressFill');
  const stepIndicator = document.getElementById('surveyStepIndicator');
  const stepButtons = Array.from(document.querySelectorAll('.stepper-step'));
  const closeBtn = document.getElementById('surveyClose');
  const goalsWrap = document.getElementById('surveyGoals');
  const deckSection = document.getElementById('surveyDeck');
  const deckSlidesWrap = document.getElementById('deckSlides');
  const deckSlides = deckSlidesWrap ? Array.from(deckSlidesWrap.querySelectorAll('.deck-slide')) : [];
  const deckProgressBar = document.getElementById('deckProgressBar');
  const deckProgressText = document.getElementById('deckProgressText');
  const deckLaunchBtn = document.getElementById('deckLaunchBtn');
  const deckNextBtn = document.getElementById('deckNextBtn');
  const deckPrevBtn = document.getElementById('deckPrevBtn');
  const deckSkipHeader = document.getElementById('deckSkipHeader');
  const rankName = document.getElementById('surveyRankName');
  const rankList = document.getElementById('surveyRankList');
  const rankNumTop = document.getElementById('surveyRankNumTop');
  const rankNumMid = document.getElementById('surveyRankNumMid');
  const rankNumYou = document.getElementById('surveyRankNumYou');
  const handoffEl = document.getElementById('surveyDesktopHandoff');
  const handoffStatusEl = document.getElementById('surveyDesktopHandoffStatus');
  const shareDemoBtn = document.getElementById('surveyShareDemoLink');

  const baseUrl = window.JCP_CONFIG && window.JCP_CONFIG.baseUrl
    ? window.JCP_CONFIG.baseUrl.replace(/\/$/, '')
    : window.location.origin;

  const getDemoRunBase = () => {
    if (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.demo_run_url) {
      return window.JCP_DEMO_SURVEY.demo_run_url;
    }
    return new URL('/demo/', window.location.origin).href;
  };

  const getValue = (id) => (document.getElementById(id)?.value || '').trim();

  const BUSINESS_TYPE_OTHER = 'other';
  const REFERRAL_SOURCE_OTHER = 'Other';

  const loadBusinessTypeOptions = () => {
    const el = document.getElementById('jcpBusinessTypeOptions');
    if (!el) return [];
    try {
      const parsed = JSON.parse(el.textContent || '[]');
      return Array.isArray(parsed) ? parsed.filter((o) => o && o.value && o.label) : [];
    } catch (e) {
      return [];
    }
  };

  const businessTypeOptions = loadBusinessTypeOptions();
  const nicheSearchEl = document.getElementById('nicheSearch');
  const nicheHiddenEl = document.getElementById('niche');
  const nicheOtherEl = document.getElementById('nicheOther');
  const nicheListboxEl = document.getElementById('nicheListbox');
  let nicheActiveIndex = -1;
  let nicheListOpen = false;

  const findBusinessTypeOption = (raw) => {
    const q = String(raw || '').trim().toLowerCase();
    if (!q) return null;
    return businessTypeOptions.find(
      (o) => String(o.value).toLowerCase() === q || String(o.label).toLowerCase() === q
    ) || null;
  };

  const filterBusinessTypeOptions = (query) => {
    const q = String(query || '').trim().toLowerCase();
    if (!q) return businessTypeOptions.slice(0, 40);
    const starts = [];
    const contains = [];
    businessTypeOptions.forEach((opt) => {
      const label = String(opt.label || '').toLowerCase();
      const value = String(opt.value || '').toLowerCase();
      if (label.startsWith(q) || value.startsWith(q)) {
        starts.push(opt);
      } else if (label.includes(q) || value.includes(q)) {
        contains.push(opt);
      }
    });
    return starts.concat(contains).slice(0, 40);
  };

  const setNicheFields = (slug, otherText, displayText) => {
    if (nicheHiddenEl) nicheHiddenEl.value = slug || '';
    if (nicheOtherEl) nicheOtherEl.value = otherText || '';
    if (nicheSearchEl && displayText != null) nicheSearchEl.value = displayText;
  };

  const closeNicheList = () => {
    nicheListOpen = false;
    nicheActiveIndex = -1;
    if (nicheListboxEl) {
      nicheListboxEl.hidden = true;
      nicheListboxEl.innerHTML = '';
    }
    if (nicheSearchEl) {
      nicheSearchEl.setAttribute('aria-expanded', 'false');
      nicheSearchEl.removeAttribute('aria-activedescendant');
    }
  };

  const updateNicheActiveOption = () => {
    if (!nicheListboxEl) return;
    const options = Array.from(nicheListboxEl.querySelectorAll('[role="option"]'));
    options.forEach((opt, idx) => {
      const active = idx === nicheActiveIndex;
      opt.setAttribute('aria-selected', active ? 'true' : 'false');
      opt.classList.toggle('is-active', active);
      if (active) {
        nicheSearchEl?.setAttribute('aria-activedescendant', opt.id);
        opt.scrollIntoView({ block: 'nearest' });
      }
    });
    if (nicheActiveIndex < 0) {
      nicheSearchEl?.removeAttribute('aria-activedescendant');
    }
  };

  const selectBusinessTypeOption = (opt) => {
    if (!opt) return;
    if (String(opt.value) === BUSINESS_TYPE_OTHER) {
      setNicheFields(BUSINESS_TYPE_OTHER, '', '');
      if (nicheSearchEl) {
        nicheSearchEl.placeholder = 'Describe your trade…';
        nicheSearchEl.focus();
      }
    } else {
      setNicheFields(opt.value, '', opt.label);
      if (nicheSearchEl) {
        nicheSearchEl.placeholder = 'Start typing your trade…';
      }
    }
    closeNicheList();
    setHandoffStatus('');
    scheduleSaveProgress();
  };

  const commitNicheFromSearch = () => {
    const typed = nicheSearchEl ? nicheSearchEl.value.trim() : '';
    if (!typed) {
      setNicheFields('', '', typed);
      return;
    }
    const match = findBusinessTypeOption(typed);
    if (match && String(match.value) !== BUSINESS_TYPE_OTHER) {
      setNicheFields(match.value, '', match.label);
      return;
    }
    if (match && String(match.value) === BUSINESS_TYPE_OTHER) {
      // Exact "Other" label/value without a custom description — prompt for detail.
      setNicheFields(BUSINESS_TYPE_OTHER, '', '');
      return;
    }
    setNicheFields(BUSINESS_TYPE_OTHER, typed, typed);
  };

  const openNicheList = (query) => {
    if (!nicheListboxEl || !nicheSearchEl) return;
    const matches = filterBusinessTypeOptions(query);
    nicheListboxEl.innerHTML = '';
    if (!matches.length) {
      closeNicheList();
      return;
    }
    matches.forEach((opt, idx) => {
      const li = document.createElement('li');
      li.id = `niche-option-${idx}`;
      li.setAttribute('role', 'option');
      li.setAttribute('aria-selected', 'false');
      li.className = 'survey-combobox__option';
      li.dataset.value = opt.value;
      li.textContent = opt.label;
      if (opt.group) {
        li.dataset.group = opt.group;
      }
      li.addEventListener('mousedown', (e) => {
        e.preventDefault();
        selectBusinessTypeOption(opt);
      });
      nicheListboxEl.appendChild(li);
    });
    nicheListboxEl.hidden = false;
    nicheListOpen = true;
    nicheActiveIndex = 0;
    nicheSearchEl.setAttribute('aria-expanded', 'true');
    updateNicheActiveOption();
  };

  const syncNicheOtherField = () => {
    // Combobox: custom text lives in #nicheSearch / #nicheOther; no secondary field.
    commitNicheFromSearch();
  };

  const syncReferralSourceOtherField = () => {
    const wrap = document.getElementById('referralSourceOtherWrap');
    const otherInput = document.getElementById('referralSourceOther');
    const isOther = getValue('referralSource') === REFERRAL_SOURCE_OTHER;
    if (wrap) wrap.hidden = !isOther;
    if (otherInput) {
      otherInput.required = isOther;
      if (!isOther) otherInput.value = '';
    }
  };

  const getReferralSourceValue = () => {
    const selected = getValue('referralSource');
    if (!selected) return '';
    if (selected === REFERRAL_SOURCE_OTHER) {
      const detail = getValue('referralSourceOther');
      return detail ? `${REFERRAL_SOURCE_OTHER}: ${detail}` : REFERRAL_SOURCE_OTHER;
    }
    return selected;
  };

  const getBusinessTypeValue = () => {
    commitNicheFromSearch();
    const selected = getValue('niche');
    if (selected === BUSINESS_TYPE_OTHER) {
      return getValue('nicheOther');
    }
    return selected;
  };

  const getBusinessTypeLabel = () => {
    commitNicheFromSearch();
    const selected = getValue('niche');
    if (!selected) return '';
    if (selected === BUSINESS_TYPE_OTHER) {
      return getValue('nicheOther');
    }
    const match = businessTypeOptions.find((o) => o.value === selected);
    return match ? String(match.label).trim() : getValue('nicheSearch');
  };

  const setBusinessTypeFromStored = (storedType) => {
    if (storedType == null) return;
    const raw = String(storedType).trim();
    if (!raw) return;
    const byValue = businessTypeOptions.find((o) => o.value === raw);
    if (byValue && byValue.value !== BUSINESS_TYPE_OTHER) {
      setNicheFields(byValue.value, '', byValue.label);
      return;
    }
    const byLabel = findBusinessTypeOption(raw);
    if (byLabel && byLabel.value !== BUSINESS_TYPE_OTHER) {
      setNicheFields(byLabel.value, '', byLabel.label);
      return;
    }
    setNicheFields(BUSINESS_TYPE_OTHER, raw, raw);
  };

  const getAttributionPayload = () => (
    window.JCPLeadAttribution && typeof window.JCPLeadAttribution.getPayload === 'function'
      ? window.JCPLeadAttribution.getPayload()
      : {}
  );

  const PROGRESS_KEY = 'jcp_survey_progress';
  const RETURN_URL_KEY = 'jcp_survey_return_url';
  const INTAKE_COMPLETE_KEY = 'jcp_demo_intake_complete';
  const DEMO_SESSION_KEY = 'jcp_demo_session_id';

  const getFormSnapshot = () => {
    commitNicheFromSearch();
    return {
      businessName: getValue('businessName'),
      niche: getValue('niche'),
      nicheOther: getValue('nicheOther'),
      firstName: getValue('firstName'),
      lastName: getValue('lastName'),
      email: getValue('email'),
      phone: getValue('phone'),
      referralSource: getValue('referralSource'),
      referralSourceOther: getValue('referralSourceOther'),
      goals: Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || []).map((input) => input.value),
    };
  };

  const applyFormSnapshot = (form) => {
    if (!form || typeof form !== 'object') return;
    const setField = (id, val) => {
      const el = document.getElementById(id);
      if (el && val != null && String(val).trim() !== '') {
        el.value = val;
      }
    };
    setField('businessName', form.businessName);
    if (form.niche === BUSINESS_TYPE_OTHER) {
      const other = (form.nicheOther || '').trim();
      setNicheFields(BUSINESS_TYPE_OTHER, other, other);
    } else if (form.niche) {
      setBusinessTypeFromStored(form.niche);
    }
    syncNicheOtherField();
    setField('firstName', form.firstName);
    setField('lastName', form.lastName);
    setField('email', form.email);
    setField('phone', form.phone);
    setField('referralSource', form.referralSource);
    setField('referralSourceOther', form.referralSourceOther);
    syncReferralSourceOtherField();
    if (goalsWrap && Array.isArray(form.goals)) {
      goalsWrap.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
        cb.checked = form.goals.indexOf(cb.value) !== -1;
      });
      enforceGoalLimit();
    }
  };

  const saveSurveyProgress = () => {
    const phase = deckSection?.classList.contains('active') ? 'deck' : 'form';
    try {
      localStorage.setItem(
        PROGRESS_KEY,
        JSON.stringify({
          phase,
          currentIndex,
          deckIndex,
          form: getFormSnapshot(),
          updatedAt: Date.now(),
        })
      );
    } catch (e) {
      // no-op
    }
  };

  const clearSurveyProgress = () => {
    try {
      localStorage.removeItem(PROGRESS_KEY);
    } catch (e) {
      // no-op
    }
  };

  const hasSavedProgress = () => {
    try {
      const raw = localStorage.getItem(PROGRESS_KEY);
      if (!raw) return false;
      const parsed = JSON.parse(raw);
      return Boolean(parsed && typeof parsed === 'object');
    } catch (e) {
      return false;
    }
  };

  const rememberReturnUrl = () => {
    try {
      if (sessionStorage.getItem(RETURN_URL_KEY)) return;
      const ref = document.referrer || '';
      const home = `${baseUrl}/`;
      if (ref && !ref.includes('/demo')) {
        sessionStorage.setItem(RETURN_URL_KEY, ref);
      } else {
        sessionStorage.setItem(RETURN_URL_KEY, home);
      }
    } catch (e) {
      // no-op
    }
  };

  let saveProgressTimer;
  const scheduleSaveProgress = () => {
    clearTimeout(saveProgressTimer);
    saveProgressTimer = setTimeout(saveSurveyProgress, 280);
  };

  const getStoredDemoUser = () => {
    try {
      const raw = localStorage.getItem('demoUser');
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      return null;
    }
  };

  const markDemoIntakeComplete = () => {
    try {
      sessionStorage.setItem(INTAKE_COMPLETE_KEY, '1');
    } catch (e) {
      // no-op
    }
  };

  const resolveNicheFromSource = (source) => {
    if (!source || typeof source !== 'object') return getBusinessTypeValue();
    const select = (source.niche || '').trim();
    if (select === BUSINESS_TYPE_OTHER) {
      return (source.nicheOther || '').trim();
    }
    return select;
  };

  const buildPersonalizedDemoUrl = (storedUser) => {
    const url = new URL(getDemoRunBase());
    url.searchParams.set('mode', 'run');
    const source = storedUser && typeof storedUser === 'object' ? storedUser : getFormSnapshot();
    const firstName = (source.firstName || '').trim();
    const lastName = (source.lastName || '').trim();
    const business = (source.businessName || '').trim();
    const niche = resolveNicheFromSource(source);
    const email = (source.email || '').trim();
    if (firstName) url.searchParams.set('name', firstName);
    if (lastName) url.searchParams.set('last_name', lastName);
    if (business) url.searchParams.set('business', business);
    if (niche) url.searchParams.set('niche', niche);
    if (email) url.searchParams.set('email', email);
    return url.href;
  };

  const shouldSkipSurveyForReturningUser = () => {
    try {
      const params = new URLSearchParams(window.location.search || '');
      if (params.get('mode') === 'run') return false;
      if (params.get('forceSurvey') === '1') return false;
      if (hasSavedProgress()) return false;

      const demoUser = getStoredDemoUser();
      if (!demoUser || typeof demoUser !== 'object') return false;

      const hasTrade = Boolean((demoUser.niche || demoUser.businessName || '').toString().trim());
      const hasEmail = Boolean((demoUser.email || '').trim());
      if (!hasTrade || !hasEmail) return false;

      const intakeDone = sessionStorage.getItem(INTAKE_COMPLETE_KEY) === '1';
      const hasSession = Boolean((sessionStorage.getItem(DEMO_SESSION_KEY) || '').trim());
      return intakeDone || hasSession;
    } catch (e) {
      return false;
    }
  };

  if (shouldSkipSurveyForReturningUser()) {
    window.location.replace(buildPersonalizedDemoUrl(getStoredDemoUser()));
    return;
  }

  let currentIndex = 0;
  let deckIndex = 0;
  let rankTimers = [];
  let channelTimers = [];
  const rankBox = document.getElementById('surveyRankBox');

  function getSurveySessionId() {
    try {
      let id = sessionStorage.getItem(DEMO_SESSION_KEY);
      if (!id) {
        id = 'd_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
        sessionStorage.setItem(DEMO_SESSION_KEY, id);
      }
      return id;
    } catch (e) {
      return 'd_' + Date.now();
    }
  }

  function surveyTrack(eventType, stepNumber, metadata) {
    const restEventUrl = (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.rest_event_url) ? window.JCP_DEMO_SURVEY.rest_event_url : baseUrl + '/wp-json/jcp/v1/demo-event';
    try {
      const body = {
        session_id: getSurveySessionId(),
        event_type: eventType,
        step_number: stepNumber != null ? stepNumber : undefined,
        metadata: metadata || undefined,
        ...getAttributionPayload(),
      };
      const firstName = getValue('firstName');
      const lastName = getValue('lastName');
      const email = getValue('email');
      const company = getValue('businessName');
      if (firstName) body.first_name = firstName;
      if (lastName) body.last_name = lastName;
      if (email) body.email = email;
      const phone = getValue('phone');
      if (phone) body.phone = phone;
      if (company) body.company = company;
      const niche = getBusinessTypeValue();
      if (niche) body.business_type = niche;
      fetch(restEventUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      }).catch(function() {});
    } catch (e) {}
  }

  const isMobileSurvey = () => window.matchMedia('(max-width: 768px)').matches;

  const setHandoffStatus = (message, isError) => {
    if (!handoffStatusEl) return;
    handoffStatusEl.textContent = message || '';
    handoffStatusEl.classList.toggle('is-error', Boolean(isError));
  };

  const updateDesktopHandoff = () => {
    if (!handoffEl) return;
    const deckActive = deckSection?.classList.contains('active');
    const show = isMobileSurvey();
    handoffEl.hidden = !show;
    if (shareDemoBtn) {
      shareDemoBtn.hidden = typeof navigator.share !== 'function';
    }
    if (!show) {
      setHandoffStatus('');
    }
  };

  const copyDemoLink = async () => {
    const url = buildPersonalizedDemoUrl();
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(url);
      } else {
        const input = document.createElement('textarea');
        input.value = url;
        input.setAttribute('readonly', '');
        input.style.position = 'absolute';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
      }
      setHandoffStatus('Link copied. Paste it in a text or email on your computer.');
      surveyTrack('demo_link_copied', null, { company: getValue('businessName') });
    } catch (err) {
      setHandoffStatus('Could not copy automatically. Long-press the address bar and copy the page URL.', true);
    }
  };

  const shareDemoLink = async () => {
    const url = buildPersonalizedDemoUrl();
    if (typeof navigator.share !== 'function') {
      copyDemoLink();
      return;
    }
    try {
      await navigator.share({
        title: 'JobCapturePro Demo',
        text: 'Open this on your computer for the full interactive demo.',
        url,
      });
      setHandoffStatus('Link shared. Open it on a desktop or laptop when you are ready.');
      surveyTrack('demo_link_shared', null, { company: getValue('businessName') });
    } catch (err) {
      if (err && err.name === 'AbortError') return;
      copyDemoLink();
    }
  };

  // Prefill survey from Early Access form submission (localStorage key: jcp_demo_survey_prefill).
  const prefillFromEarlyAccess = () => {
    try {
      const raw = localStorage.getItem('jcp_demo_survey_prefill');
      if (!raw) return;
      const prefill = JSON.parse(raw);
      if (!prefill || typeof prefill !== 'object') return;

      const businessNameEl = document.getElementById('businessName');
      const nicheEl = document.getElementById('niche');
      const firstNameEl = document.getElementById('firstName');
      const lastNameEl = document.getElementById('lastName');
      const emailEl = document.getElementById('email');
      const phoneEl = document.getElementById('phone');

      if (businessNameEl && prefill.company != null) businessNameEl.value = prefill.company;
      if (prefill.business_type != null) setBusinessTypeFromStored(prefill.business_type);
      if (firstNameEl && prefill.first_name != null) firstNameEl.value = prefill.first_name;
      if (lastNameEl && prefill.last_name != null) lastNameEl.value = prefill.last_name;
      if (emailEl && prefill.email != null) emailEl.value = prefill.email;
      if (phoneEl && prefill.phone != null) phoneEl.value = prefill.phone;

      // Early Access uses full labels as values; survey uses short values (calls, google, etc.).
      const eaToSurvey = {
        'More inbound calls': 'calls',
        'Better Google visibility': 'google',
        'More customer reviews': 'reviews',
        'Stronger website trust': 'trust',
        'Less marketing busywork': 'busywork',
        'Showcase my work': 'showcase',
      };
      const surveyValues = (prefill.demo_goals || []).map((v) => eaToSurvey[v] || v).filter(Boolean);
      if (goalsWrap && surveyValues.length) {
        goalsWrap.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
          cb.checked = surveyValues.indexOf(cb.value) !== -1;
        });
        enforceGoalLimit();
      }
    } catch (e) {
      // no-op
    }
  };

  const setProgressChromeVisible = (visible) => {
    if (!progressWrap) return;
    if (visible) {
      progressWrap.classList.remove('is-hidden');
      progressWrap.hidden = false;
      progressWrap.setAttribute('aria-hidden', 'false');
      return;
    }
    progressWrap.classList.add('is-hidden');
    progressWrap.hidden = true;
    progressWrap.setAttribute('aria-hidden', 'true');
    if (stepIndicator) stepIndicator.hidden = true;
  };

  const updateProgress = () => {
    const total = Math.max(1, steps.length);
    const stepNum = Math.min(currentIndex + 1, total);
    // Single-screen gate: never show step chrome / numbered badge.
    if (total <= 1) {
      setProgressChromeVisible(false);
      return;
    }
    if (progressText) {
      progressText.textContent = `Step ${stepNum} of ${total}`;
    }
    if (stepIndicator) {
      stepIndicator.textContent = `Step ${stepNum}/${total}`;
      stepIndicator.hidden = true;
    }
    if (progressFill) {
      progressFill.style.width = `${(stepNum / total) * 100}%`;
    }
    stepButtons.forEach((btn, idx) => {
      btn.classList.toggle('is-active', idx === currentIndex);
    });
  };

  window.addEventListener('resize', () => {
    if (progressWrap && !progressWrap.classList.contains('is-hidden')) {
      updateProgress();
    }
    if (deckSection?.classList.contains('active')) {
      setDeckUI();
    }
  });

  const getSurveyFormMetadata = () => {
    const meta = { company: getValue('businessName'), business_type: getBusinessTypeValue() };
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || []).map((input) => input.value);
    if (goals.length) meta.demo_goals = goals;
    const referral = getReferralSourceValue();
    if (referral) meta.referral_source = referral;
    return meta;
  };

  const showStep = (index) => {
    if (index > currentIndex && currentIndex >= 0 && currentIndex < 3) {
      surveyTrack('form_step_completed', currentIndex + 1, getSurveyFormMetadata());
    }
    steps.forEach((step, idx) => {
      step.classList.toggle('active', idx === index);
    });
    deckSection?.classList.remove('active');
    setProgressChromeVisible(steps.length > 1);
    if (deckSkipHeader) deckSkipHeader.hidden = true;
    currentIndex = index;
    updateProgress();
    updateDesktopHandoff();
    saveSurveyProgress();
  };

  const clearRankTimers = () => {
    if (!rankTimers.length) return;
    rankTimers.forEach((id) => clearTimeout(id));
    rankTimers = [];
  };

  const setRankNums = (top, mid, you) => {
    if (!rankNumTop || !rankNumMid || !rankNumYou) return;
    rankNumTop.textContent = String(top);
    rankNumMid.textContent = String(mid);
    rankNumYou.textContent = String(you);
  };

  const resetRankState = () => {
    if (!rankList) return;
    rankList.classList.remove('promote-step-1', 'promote-step-2');
    setRankNums(1, 2, 3);
  };

  const clearChannelTimers = () => {
    if (!channelTimers.length) return;
    channelTimers.forEach((id) => clearTimeout(id));
    channelTimers = [];
  };

  const CHANNEL_PREVIEW_LABELS = {
    website: 'Live on your website',
    google: 'Live on Google Business Profile',
    social: 'Ready for social',
    directory: 'Listed in your directory',
  };

  const resetChannelTiles = () => {
    const slide = deckSlides.find((el) => el.classList.contains('deck-slide--channels'));
    if (!slide) return;
    slide.classList.remove('is-sequencing');
    slide.querySelectorAll('.deck-tile').forEach((tile) => {
      tile.classList.remove('is-visible', 'is-live');
      tile.setAttribute('aria-pressed', 'false');
      const status = tile.querySelector('.tile-status');
      if (status) status.textContent = 'Publish';
    });
    const preview = slide.querySelector('[data-publish-preview]');
    if (preview) preview.hidden = true;
  };

  const activateChannelTile = (tile, { revealPreview = true } = {}) => {
    if (!tile) return;
    const slide = tile.closest('.deck-slide--channels');
    const channel = tile.getAttribute('data-channel') || 'website';
    tile.classList.add('is-visible', 'is-live');
    tile.setAttribute('aria-pressed', 'true');
    const status = tile.querySelector('.tile-status');
    if (status) status.textContent = 'Live';
    if (!revealPreview || !slide) return;
    const preview = slide.querySelector('[data-publish-preview]');
    const label = slide.querySelector('[data-publish-preview-label]');
    if (label) label.textContent = CHANNEL_PREVIEW_LABELS[channel] || CHANNEL_PREVIEW_LABELS.website;
    if (preview) preview.hidden = false;
  };

  const runChannelsSequence = () => {
    const slide = deckSlides.find((el) => el.classList.contains('deck-slide--channels'));
    if (!slide) return;
    const tiles = [...slide.querySelectorAll('.deck-tile')];
    if (!tiles.length) return;

    clearChannelTimers();
    resetChannelTiles();
    slide.classList.add('is-sequencing');

    tiles.forEach((tile, idx) => {
      channelTimers.push(setTimeout(() => {
        activateChannelTile(tile, { revealPreview: idx === tiles.length - 1 });
      }, 180 + idx * 420));
    });
  };

  const runRankSequence = () => {
    if (!rankList) return;
    resetRankState();
    clearRankTimers();
    rankTimers.push(setTimeout(() => {
      rankList.classList.add('promote-step-1');
      setRankNums(1, 3, 2);
    }, 450));
    rankTimers.push(setTimeout(() => {
      rankList.classList.remove('promote-step-1');
      rankList.classList.add('promote-step-2');
      setRankNums(2, 3, 1);
    }, 1250));
  };

  const runRankSlideSequence = () => {
    if (!rankList) return;
    resetRankState();
    clearRankTimers();

    if (!isMobileSurvey()) {
      runRankSequence();
      return;
    }

    rankTimers.push(setTimeout(() => {
      const scrollEl = deckSlidesWrap;
      if (scrollEl && rankBox) {
        const top = rankBox.getBoundingClientRect().top
          - scrollEl.getBoundingClientRect().top
          + scrollEl.scrollTop
          - 8;
        scrollEl.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
      }
      rankTimers.push(setTimeout(() => runRankSequence(), 650));
    }, 3000));
  };

  const setDeckUI = () => {
    if (!deckSlides.length) return;
    surveyTrack('slideshow_step_viewed', deckIndex + 1);
    deckSlides.forEach((slide, idx) => {
      slide.classList.toggle('is-active', idx === deckIndex);
      slide.classList.toggle('is-prev', idx < deckIndex);
    });
    const total = deckSlides.length;
    const shown = deckIndex + 1;
    if (deckProgressText) deckProgressText.textContent = `${shown} / ${total}`;
    if (deckProgressBar) deckProgressBar.style.width = `${(shown / total) * 100}%`;
    if (stepIndicator && isMobileSurvey()) {
      stepIndicator.textContent = `${shown}/${total}`;
      stepIndicator.hidden = false;
    }
    if (deckSkipHeader) deckSkipHeader.hidden = !isMobileSurvey();
    const isLast = deckIndex === total - 1;
    if (deckLaunchBtn) deckLaunchBtn.classList.toggle('is-hidden', !isLast);
    if (deckNextBtn) deckNextBtn.classList.toggle('is-hidden', isLast);
    // Always keep Back available on deck (except slide 1) — mobile used to hide it and trap users.
    if (deckPrevBtn) {
      deckPrevBtn.classList.toggle('is-hidden', deckIndex === 0);
    }

    if (deckSlidesWrap) deckSlidesWrap.scrollTop = 0;

    if (rankList) {
      const rankSlideIndex = deckSlides.findIndex((el) => el.classList.contains('deck-slide--rank'));
      const isRankSlide = rankSlideIndex >= 0 && deckIndex === rankSlideIndex;
      clearRankTimers();
      if (isRankSlide) {
        runRankSlideSequence();
      } else {
        resetRankState();
      }
    }

    const channelsSlideIndex = deckSlides.findIndex((el) => el.classList.contains('deck-slide--channels'));
    const isChannelsSlide = channelsSlideIndex >= 0 && deckIndex === channelsSlideIndex;
    clearChannelTimers();
    if (isChannelsSlide) {
      runChannelsSequence();
    } else {
      resetChannelTiles();
    }

    saveSurveyProgress();
  };

  const showDeck = (startIndex = 0) => {
    document.documentElement.classList.remove('survey-gate-scroll');
    document.body.classList.remove('survey-gate-scroll');
    steps.forEach((step) => step.classList.remove('active'));
    deckSection?.classList.add('active');
    setProgressChromeVisible(false);
    if (deckSkipHeader) deckSkipHeader.hidden = !isMobileSurvey();
    deckIndex = Math.min(Math.max(0, startIndex), Math.max(0, deckSlides.length - 1));
    setDeckUI();

    // First slide: personalize with business type when available
    const titleEl = document.getElementById('deckSlide1Title');
    const label = getBusinessTypeLabel();
    if (titleEl && label) {
      titleEl.textContent = 'Every completed ' + label + ' job should help you win the next one.';
    }
    const personalTitle = document.getElementById('deckPersonalTitle');
    const business = getValue('businessName');
    if (personalTitle && business) {
      personalTitle.textContent = 'Ready, ' + business + '? See one job publish everywhere.';
    }
    hydrateRankName();
    updateDesktopHandoff();
    saveSurveyProgress();
  };


  const deriveFirstName = () => {
    const named = getValue('firstName');
    if (named) return named;
    const email = getValue('email');
    const local = (email.split('@')[0] || '').replace(/[._-]+/g, ' ').trim();
    if (!local) return 'there';
    return local.split(' ').filter(Boolean).map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
  };

  const resolveBusinessName = () => {
    const named = getValue('businessName');
    if (named) return named;
    const nicheLabel = getBusinessTypeLabel();
    return nicheLabel ? (nicheLabel + ' Pro') : 'Your Business';
  };

  /** Single-screen gate: trade + work email required; phone recommended but optional. */
  const validateGate = () => {
    commitNicheFromSearch();
    const nicheSelect = getValue('niche');
    const businessType = getBusinessTypeValue();
    const emailInput = document.getElementById('email');
    const email = getValue('email');
    if (!nicheSelect && !getValue('nicheSearch')) {
      alert('Please choose or type your business type to personalize the demo.');
      nicheSearchEl?.focus();
      nicheSearchEl?.classList.add('is-error');
      return false;
    }
    if (!businessType || (nicheSelect === BUSINESS_TYPE_OTHER && !getValue('nicheOther'))) {
      alert('Please describe your business type to continue.');
      nicheSearchEl?.focus();
      nicheSearchEl?.classList.add('is-error');
      return false;
    }
    nicheSearchEl?.classList.remove('is-error');
    if (!email || !emailInput?.checkValidity()) {
      emailInput?.classList.add('is-error');
      emailInput?.focus();
      alert('Please enter a valid work email to continue.');
      return false;
    }
    // Fill optional personalization defaults used by demo + CRM.
    const businessEl = document.getElementById('businessName');
    if (businessEl && !getValue('businessName')) {
      businessEl.value = resolveBusinessName();
    }
    const firstEl = document.getElementById('firstName');
    if (firstEl && !getValue('firstName')) {
      firstEl.value = deriveFirstName();
    }
    return true;
  };

  const validateStep1 = () => validateGate();
  const validateStep2 = () => true;
  const validateStep3 = () => validateGate();

  const enforceGoalLimit = () => {
    if (!goalsWrap) return;
    const checked = goalsWrap.querySelectorAll('input[type="checkbox"]:checked');
    const inputs = goalsWrap.querySelectorAll('input[type="checkbox"]');
    inputs.forEach((input) => {
      if (!input.checked) {
        input.disabled = checked.length >= 2;
      }
    });
  };

  const saveSurveyPrefillForEarlyAccess = () => {
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map((input) => input.value);
    const prefill = {
      first_name: getValue('firstName'),
      last_name: getValue('lastName'),
      email: getValue('email'),
      phone: getValue('phone'),
      company: getValue('businessName'),
      business_type: getBusinessTypeValue(),
      demo_goals: goals,
      referral_source: getReferralSourceValue(),
    };
    try {
      localStorage.setItem('jcp_early_access_prefill', JSON.stringify(prefill));
    } catch (e) {
      // no-op
    }
  };

  // GTM → Meta Lead: fires once when step 3 validates and they click Continue to demo.
  const pushDemoOptInDataLayer = () => {
    try {
      if (sessionStorage.getItem('jcp_datalayer_demo_opt_in')) return;
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: 'demo_opt_in',
        lead_type: 'demo',
        source: 'demo_survey',
      });
      sessionStorage.setItem('jcp_datalayer_demo_opt_in', '1');
    } catch (err) {
      // no-op
    }
  };

  // Submit opt-in on gate unlock — sends contact + attribution to Demo Survey webhook (Event=demo-opt-in).
  const submitDemoOptIn = async () => {
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map((input) => input.value);
    const restUrl = (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.rest_url) || `${baseUrl}/wp-json/jcp/v1/demo-survey-submit`;
    pushDemoOptInDataLayer();
    try {
      await Promise.race([
        fetch(restUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            first_name: getValue('firstName'),
            last_name: getValue('lastName'),
            email: getValue('email'),
            phone: getValue('phone'),
            company: getValue('businessName'),
            business_type: getBusinessTypeValue(),
            demo_goals: goals,
            referral_source: getReferralSourceValue(),
            ...getAttributionPayload(),
          }),
        }),
        new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 5000)),
      ]);
    } catch (err) {
      console.warn('JCP Demo Survey: opt-in submit failed', err);
    }
  };

  // Launch live demo — send to same webhook with Event=demo-viewed (tag demo-viewed).
  const launchDemo = async () => {
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map((input) => input.value);

    markDemoIntakeComplete();

    try {
      localStorage.removeItem('demoReturnState');
      localStorage.removeItem('directoryDemoListing');
    } catch (e) {
      // no-op
    }

    const firstName = getValue('firstName');
    const lastName = getValue('lastName');
    const email = getValue('email');
    const businessName = getValue('businessName');
    const niche = getBusinessTypeValue();
    const referralSource = getReferralSourceValue();
    localStorage.setItem('demoUser', JSON.stringify({
      businessName,
      niche,
      goals,
      firstName,
      lastName,
      email,
      phone: getValue('phone'),
      referralSource,
    }));
    saveSurveyPrefillForEarlyAccess();
    clearSurveyProgress();

    const viewedUrl = (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.rest_viewed_url) || `${baseUrl}/wp-json/jcp/v1/demo-viewed-submit`;
    try {
      await Promise.race([
        fetch(viewedUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            first_name: firstName,
            last_name: lastName,
            email,
            phone: getValue('phone'),
            company: businessName,
            business_type: niche,
            demo_goals: goals,
            referral_source: referralSource,
            ...getAttributionPayload(),
          }),
        }),
        new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 5000)),
      ]);
    } catch (err) {
      console.warn('JCP Demo Survey: viewed submit failed', err);
    }

    window.location.href = buildPersonalizedDemoUrl();
  };

  const hydrateRankName = () => {
    if (!rankName) return;
    let name = 'Your Business';
    try {
      const stored = JSON.parse(localStorage.getItem('demoUser') || 'null');
      if (stored && stored.businessName) {
        name = stored.businessName;
      }
    } catch (e) {
      // no-op
    }
    rankName.textContent = name;
  };

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    e.preventDefault();
    const action = btn.dataset.action;
    const deckActive = deckSection?.classList.contains('active');

    if (action === 'next' && !deckActive) {
      if (currentIndex === 0 && !validateStep1()) return;
      if (currentIndex === 1 && !validateStep2()) return;
      if (currentIndex < steps.length - 1) {
        showStep(currentIndex + 1);
      }
    }

    if (action === 'launch' && !deckActive) {
      if (!validateGate()) return;
      surveyTrack('form_step_completed', 1, getSurveyFormMetadata());
      saveSurveyPrefillForEarlyAccess();
      const params = new URLSearchParams(window.location.search || '');
      const forceDeck = params.get('deck') === '1';
      if (forceDeck && deckSlides.length) {
        submitDemoOptIn().then(() => showDeck());
        return;
      }
      submitDemoOptIn().then(() => launchDemo());
    }

    if ((action === 'deck-next' || (deckActive && action === 'next')) && deckIndex < deckSlides.length - 1) {
      if (deckIndex < deckSlides.length - 1) {
        deckIndex += 1;
        setDeckUI();
      }
    }

    if (action === 'deck-prev' || (deckActive && action === 'prev')) {
      if (deckIndex > 0) {
        deckIndex -= 1;
        setDeckUI();
      }
    }

    if (action === 'deck-launch' || (deckActive && action === 'launch')) {
      if (e.target.closest('.deck-skip')) {
        surveyTrack('slideshow_skipped');
      }
      launchDemo();
    }

    if (action === 'copy-demo-link') {
      copyDemoLink();
    }

    if (action === 'share-demo-link') {
      shareDemoLink();
    }
  });

  stepButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = Number(btn.dataset.step);
      if (Number.isNaN(target)) return;
      if (target > currentIndex) {
        if (currentIndex === 0 && !validateStep1()) return;
        if (currentIndex === 1 && !validateStep2()) return;
      }
      showStep(target);
    });
  });

  document.getElementById('phone')?.addEventListener('input', (e) => {
    e.target.classList.remove('is-error');
  });

  document.getElementById('email')?.addEventListener('input', (e) => {
    e.target.classList.remove('is-error');
    setHandoffStatus('');
    scheduleSaveProgress();
  });

  ['firstName', 'lastName', 'phone', 'businessName', 'niche', 'nicheOther', 'nicheSearch', 'referralSource', 'referralSourceOther'].forEach((id) => {
    const el = document.getElementById(id);
    el?.addEventListener('input', () => {
      setHandoffStatus('');
      scheduleSaveProgress();
    });
    el?.addEventListener('change', () => {
      setHandoffStatus('');
      scheduleSaveProgress();
    });
  });

  if (nicheSearchEl) {
    nicheSearchEl.addEventListener('input', () => {
      nicheSearchEl.classList.remove('is-error');
      openNicheList(nicheSearchEl.value);
    });
    nicheSearchEl.addEventListener('focus', () => {
      openNicheList(nicheSearchEl.value);
    });
    nicheSearchEl.addEventListener('blur', () => {
      // Delay so option mousedown can select first.
      window.setTimeout(() => {
        commitNicheFromSearch();
        closeNicheList();
        scheduleSaveProgress();
      }, 120);
    });
    nicheSearchEl.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (nicheListOpen) {
          e.preventDefault();
          e.stopPropagation();
          closeNicheList();
        }
        return;
      }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!nicheListOpen) openNicheList(nicheSearchEl.value);
        const count = nicheListboxEl?.querySelectorAll('[role="option"]').length || 0;
        if (!count) return;
        nicheActiveIndex = Math.min(count - 1, nicheActiveIndex + 1);
        if (nicheActiveIndex < 0) nicheActiveIndex = 0;
        updateNicheActiveOption();
        return;
      }
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!nicheListOpen) return;
        nicheActiveIndex = Math.max(0, nicheActiveIndex - 1);
        updateNicheActiveOption();
        return;
      }
      if (e.key === 'Enter') {
        if (nicheListOpen && nicheActiveIndex >= 0 && nicheListboxEl) {
          e.preventDefault();
          const opts = Array.from(nicheListboxEl.querySelectorAll('[role="option"]'));
          const active = opts[nicheActiveIndex];
          if (active) {
            const match = businessTypeOptions.find((o) => o.value === active.dataset.value);
            if (match) selectBusinessTypeOption(match);
          }
        } else {
          commitNicheFromSearch();
          closeNicheList();
        }
      }
    });
  }

  document.getElementById('referralSource')?.addEventListener('change', syncReferralSourceOtherField);

  goalsWrap?.addEventListener('change', () => {
    enforceGoalLimit();
    scheduleSaveProgress();
  });

  window.addEventListener('resize', updateDesktopHandoff);

  const closeSurvey = () => {
    saveSurveyProgress();
    let returnUrl = `${baseUrl}/`;
    try {
      returnUrl = sessionStorage.getItem(RETURN_URL_KEY) || returnUrl;
    } catch (e) {
      // no-op
    }
    if (window.history.length > 1) {
      window.history.back();
      return;
    }
    window.location.href = returnUrl;
  };

  closeBtn?.addEventListener('click', closeSurvey);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (nicheListOpen) {
        closeNicheList();
        return;
      }
      closeSurvey();
    }
  });

  // Interactive channel tiles on deck slide 2
  document.addEventListener('click', (e) => {
    const tile = e.target.closest('.deck-slide--channels .deck-tile[data-channel]');
    if (!tile) return;
    const slide = tile.closest('.deck-slide--channels');
    if (!slide || !slide.classList.contains('is-active')) return;
    clearChannelTimers();
    activateChannelTile(tile, { revealPreview: true });
  });

  enforceGoalLimit();
  hydrateRankName();
  rememberReturnUrl();
  prefillFromEarlyAccess();

  const params = new URLSearchParams(window.location.search || '');
  if (params.get('forceSurvey') === '1') {
    clearSurveyProgress();
    try {
      sessionStorage.removeItem(INTAKE_COMPLETE_KEY);
    } catch (e) {
      // no-op
    }
  }

  let restored = null;
  try {
    const raw = localStorage.getItem(PROGRESS_KEY);
    if (raw && params.get('forceSurvey') !== '1') {
      restored = JSON.parse(raw);
    }
  } catch (e) {
    restored = null;
  }

  if (restored && restored.form) {
    applyFormSnapshot(restored.form);
  } else {
    syncNicheOtherField();
    syncReferralSourceOtherField();
  }

  surveyTrack('demo_started', null, getSurveyFormMetadata());

  const paramsDeck = new URLSearchParams(window.location.search || '');
  const allowDeckResume = paramsDeck.get('deck') === '1';
  if (allowDeckResume && restored && restored.phase === 'deck' && deckSlides.length) {
    const deckStart = Number.isFinite(restored.deckIndex) ? restored.deckIndex : 0;
    showDeck(deckStart);
  } else if (restored && Number.isFinite(restored.currentIndex)) {
    const stepStart = Math.min(Math.max(0, restored.currentIndex), Math.max(0, steps.length - 1));
    showStep(stepStart);
  } else {
    showStep(0);
  }

  updateDesktopHandoff();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSurvey);
  } else {
    initSurvey();
  }
})();
