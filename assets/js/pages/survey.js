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

  const PROGRESS_KEY = 'jcp_survey_progress';
  const RETURN_URL_KEY = 'jcp_survey_return_url';

  const getFormSnapshot = () => ({
    businessName: getValue('businessName'),
    niche: getValue('niche'),
    firstName: getValue('firstName'),
    lastName: getValue('lastName'),
    email: getValue('email'),
    goals: Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || []).map((input) => input.value),
  });

  const applyFormSnapshot = (form) => {
    if (!form || typeof form !== 'object') return;
    const setField = (id, val) => {
      const el = document.getElementById(id);
      if (el && val != null && String(val).trim() !== '') {
        el.value = val;
      }
    };
    setField('businessName', form.businessName);
    setField('niche', form.niche);
    setField('firstName', form.firstName);
    setField('lastName', form.lastName);
    setField('email', form.email);
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

  const buildPersonalizedDemoUrl = () => {
    const url = new URL(getDemoRunBase());
    url.searchParams.set('mode', 'run');
    const firstName = getValue('firstName');
    const lastName = getValue('lastName');
    const business = getValue('businessName');
    const niche = getValue('niche');
    const email = getValue('email');
    if (firstName) url.searchParams.set('name', firstName);
    if (lastName) url.searchParams.set('last_name', lastName);
    if (business) url.searchParams.set('business', business);
    if (niche) url.searchParams.set('niche', niche);
    if (email) url.searchParams.set('email', email);
    return url.href;
  };

  // If they've already completed the demo form before, skip the form + slides and go straight to run-demo.
  // (We can only check localStorage client-side.)
  const shouldAutoRun = (() => {
    try {
      const params = new URLSearchParams(window.location.search || '');
      if (params.get('mode') === 'run') return false;
      if (params.get('forceSurvey') === '1') return false; // escape hatch
      if (hasSavedProgress()) return false;
      const raw = localStorage.getItem('demoUser');
      if (!raw) return false;
      const demoUser = JSON.parse(raw);
      if (!demoUser || typeof demoUser !== 'object') return false;
      const hasIdentity = Boolean(
        (demoUser.firstName || '').trim() &&
        (demoUser.lastName || '').trim() &&
        (demoUser.email || '').trim()
      );
      const hasSession = Boolean((localStorage.getItem('jcp_demo_session_id') || '').trim());
      return hasIdentity && hasSession;
    } catch (e) {
      return false;
    }
  })();

  if (shouldAutoRun) {
    window.location.replace(buildPersonalizedDemoUrl());
    return;
  }

  let currentIndex = 0;
  let deckIndex = 0;
  let rankTimers = [];

  function getSurveySessionId() {
    const key = 'jcp_demo_session_id';
    try {
      let id = sessionStorage.getItem(key);
      if (!id) {
        id = 'd_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
        sessionStorage.setItem(key, id);
      }
      return id;
    } catch (e) {
      return 'd_' + Date.now();
    }
  }

  function surveyTrack(eventType, stepNumber, metadata) {
    const restEventUrl = (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.rest_event_url) ? window.JCP_DEMO_SURVEY.rest_event_url : baseUrl + '/wp-json/jcp/v1/demo-event';
    try {
      fetch(restEventUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          session_id: getSurveySessionId(),
          event_type: eventType,
          step_number: stepNumber != null ? stepNumber : undefined,
          metadata: metadata || undefined
        })
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
    const show = isMobileSurvey() && (currentIndex === 2 || deckActive);
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

      if (businessNameEl && prefill.company != null) businessNameEl.value = prefill.company;
      if (nicheEl && prefill.business_type != null) nicheEl.value = prefill.business_type;
      if (firstNameEl && prefill.first_name != null) firstNameEl.value = prefill.first_name;
      if (lastNameEl && prefill.last_name != null) lastNameEl.value = prefill.last_name;
      if (emailEl && prefill.email != null) emailEl.value = prefill.email;

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

  const updateProgress = () => {
    const stepNum = currentIndex + 1;
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    if (progressText) {
      progressText.textContent = `Step ${stepNum} of 3`;
    }
    if (stepIndicator) {
      stepIndicator.textContent = `Step ${stepNum}/3`;
      stepIndicator.hidden = !isMobile;
    }
    if (progressFill) {
      progressFill.style.width = `${(stepNum / 3) * 100}%`;
    }
    stepButtons.forEach((btn, idx) => {
      btn.classList.toggle('is-active', idx === currentIndex);
    });
  };

  window.addEventListener('resize', () => {
    if (progressWrap && !progressWrap.classList.contains('is-hidden')) {
      updateProgress();
    }
  });

  const getSurveyFormMetadata = () => {
    const meta = { company: getValue('businessName'), business_type: getValue('niche') };
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || []).map((input) => input.value);
    if (goals.length) meta.demo_goals = goals;
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
    progressWrap?.classList.remove('is-hidden');
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
    const isLast = deckIndex === total - 1;
    if (deckLaunchBtn) deckLaunchBtn.classList.toggle('is-hidden', !isLast);
    if (deckNextBtn) deckNextBtn.classList.toggle('is-hidden', isLast);

    if (rankList) {
      const isRankSlide = deckIndex === 3;
      clearRankTimers();
      if (isRankSlide) {
        runRankSequence();
      } else {
        resetRankState();
      }
    }
    saveSurveyProgress();
  };

  const showDeck = (startIndex = 0) => {
    steps.forEach((step) => step.classList.remove('active'));
    deckSection?.classList.add('active');
    progressWrap?.classList.add('is-hidden');
    if (stepIndicator) stepIndicator.hidden = true;
    deckIndex = Math.min(Math.max(0, startIndex), Math.max(0, deckSlides.length - 1));
    setDeckUI();

    // First slide: if they selected a business type, swap "job" for "[type] job"
    const titleEl = document.getElementById('deckSlide1Title');
    const nicheSelect = document.getElementById('niche');
    if (titleEl && nicheSelect && nicheSelect.value) {
      const option = nicheSelect.options[nicheSelect.selectedIndex];
      const label = option ? option.text.trim() : '';
      if (label) {
        titleEl.textContent = 'Every completed ' + label + ' job should help you win the next one.';
      }
    }
    updateDesktopHandoff();
    saveSurveyProgress();
  };

  const validateStep1 = () => {
    const businessName = getValue('businessName');
    const niche = getValue('niche');
    if (!businessName || !niche) {
      alert('Please enter your business name and type to continue.');
      return false;
    }
    return true;
  };

  const validateStep2 = () => {
    const checked = goalsWrap ? goalsWrap.querySelectorAll('input[type="checkbox"]:checked') : [];
    if (!checked.length) {
      alert('Please choose at least one demo goal to continue.');
      return false;
    }
    return true;
  };

  const validateStep3 = () => {
    const firstName = getValue('firstName');
    const lastName = getValue('lastName');
    const emailInput = document.getElementById('email');
    const email = getValue('email');
    if (!firstName || !lastName) {
      alert('Please enter your first and last name to continue.');
      return false;
    }
    if (!email || !emailInput?.checkValidity()) {
      emailInput?.classList.add('is-error');
      emailInput?.focus();
      return false;
    }
    return true;
  };

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

  // Save current survey data so Early Access form can prefill if user visits that page later.
  const saveSurveyPrefillForEarlyAccess = () => {
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map((input) => input.value);
    const prefill = {
      first_name: getValue('firstName'),
      last_name: getValue('lastName'),
      email: getValue('email'),
      company: getValue('businessName'),
      business_type: getValue('niche'),
      demo_goals: goals,
    };
    try {
      localStorage.setItem('jcp_early_access_prefill', JSON.stringify(prefill));
    } catch (e) {
      // no-op
    }
  };

  // Submit opt-in when user clicks "Continue to preview" — sends full form to first webhook (Create Contact + tag demo-opt-in).
  const submitDemoOptIn = async () => {
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map((input) => input.value);
    const restUrl = (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.rest_url) || `${baseUrl}/wp-json/jcp/v1/demo-survey-submit`;
    try {
      await Promise.race([
        fetch(restUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            first_name: getValue('firstName'),
            last_name: getValue('lastName'),
            email: getValue('email'),
            company: getValue('businessName'),
            business_type: getValue('niche'),
            demo_goals: goals,
          }),
        }),
        new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 5000)),
      ]);
    } catch (err) {
      console.warn('JCP Demo Survey: opt-in submit failed', err);
    }
  };

  // When user clicks "Skip to demo" or "Launch the live demo" — send to second webhook so GHL can add tag "viewed-demo".
  const launchDemo = async () => {
    const goals = Array.from(goalsWrap?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map((input) => input.value);

    try {
      localStorage.removeItem('demoReturnState');
      localStorage.removeItem('directoryDemoListing');
    } catch (e) {
      // no-op
    }

    const firstName = getValue('firstName');
    const lastName = getValue('lastName');
    const email = getValue('email');
    localStorage.setItem('demoUser', JSON.stringify({
      businessName: getValue('businessName'),
      niche: getValue('niche'),
      goals,
      firstName,
      lastName,
      email,
    }));
    saveSurveyPrefillForEarlyAccess();
    clearSurveyProgress();

    const viewedUrl = (typeof window.JCP_DEMO_SURVEY !== 'undefined' && window.JCP_DEMO_SURVEY.rest_viewed_url) || `${baseUrl}/wp-json/jcp/v1/demo-viewed-submit`;
    try {
      await Promise.race([
        fetch(viewedUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ first_name: firstName, last_name: lastName, email }),
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
      if (!validateStep3()) return;
      surveyTrack('form_step_completed', 3, getSurveyFormMetadata());
      saveSurveyPrefillForEarlyAccess();
      if (deckSlides.length) {
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

  document.getElementById('email')?.addEventListener('input', (e) => {
    e.target.classList.remove('is-error');
    setHandoffStatus('');
    scheduleSaveProgress();
  });

  ['firstName', 'lastName', 'businessName', 'niche'].forEach((id) => {
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
      closeSurvey();
    }
  });

  enforceGoalLimit();
  hydrateRankName();
  rememberReturnUrl();
  prefillFromEarlyAccess();

  const params = new URLSearchParams(window.location.search || '');
  if (params.get('forceSurvey') === '1') {
    clearSurveyProgress();
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
  }

  surveyTrack('demo_started', null, getSurveyFormMetadata());

  if (restored && restored.phase === 'deck' && deckSlides.length) {
    const deckStart = Number.isFinite(restored.deckIndex) ? restored.deckIndex : 0;
    showDeck(deckStart);
  } else if (restored && Number.isFinite(restored.currentIndex)) {
    const stepStart = Math.min(Math.max(0, restored.currentIndex), steps.length - 1);
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
