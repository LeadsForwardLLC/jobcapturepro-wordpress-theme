/* =========================================================
   JobCapturePro Demo JS (Clean, Organized, Stable)
   - Keeps all features currently used
   - Preserves global functions called by HTML onclick
   - Fixes broken handlers / stray code / flow issues
========================================================= */

/* =========================
   Personalization (Survey)
========================= */
let demoUser = {
  firstName: 'John',
  lastName: 'Smith',
  businessName: 'Summit Plumbing',
  niche: 'plumbing'
};

const baseUrl = window.JCP_CONFIG && window.JCP_CONFIG.baseUrl
  ? window.JCP_CONFIG.baseUrl
  : window.location.origin;
const assetBase = window.JCP_ASSET_BASE || '';

/** Build app onboarding URL; merges UTM defaults, then extra params (demo_session, email, names, utm_content, …). */
function jcpBuildOnboardingUrl(extraParams) {
  const fallback =
    'https://app.jobcapturepro.com/onboarding?sessionId=75ad8454-312e-4224-95b7-8f48f5cd0277&step=1';
  const base =
    typeof window.JCP_ONBOARDING === 'object' && window.JCP_ONBOARDING && window.JCP_ONBOARDING.url
      ? window.JCP_ONBOARDING.url
      : fallback;
  const utmFallback = { utm_source: 'jobcapturepro.com', utm_medium: 'website', utm_campaign: 'onboarding' };
  try {
    const u = base.startsWith('http') ? new URL(base) : new URL(base, window.location.origin);
    const defs =
      typeof window.JCP_ONBOARDING === 'object' &&
      window.JCP_ONBOARDING &&
      window.JCP_ONBOARDING.utmDefaults &&
      typeof window.JCP_ONBOARDING.utmDefaults === 'object'
        ? window.JCP_ONBOARDING.utmDefaults
        : utmFallback;
    Object.keys(defs).forEach((key) => {
      const val = defs[key];
      if (val !== undefined && val !== null && String(val).trim() !== '') {
        u.searchParams.set(key, String(val));
      }
    });
    if (extraParams && typeof extraParams === 'object') {
      Object.keys(extraParams).forEach((key) => {
        const val = extraParams[key];
        if (val !== undefined && val !== null && String(val).trim() !== '') {
          u.searchParams.set(key, String(val));
        }
      });
    }
    return u.toString();
  } catch (e) {
    return base;
  }
}

/** Query params for handoff after demo (session + PII the app can prefill), plus optional utm_content. */
function jcpDemoOnboardingHandoffQuery(utmContent) {
  const extra = {
    demo_session: getDemoSessionId(),
    utm_content: utmContent || 'demo_handoff'
  };
  try {
    if (demoUser && typeof demoUser.email === 'string' && demoUser.email.trim()) {
      extra.email = demoUser.email.trim();
    }
    const fn = (demoUser.firstName || '').trim();
    const ln = (demoUser.lastName || '').trim();
    if (fn) extra.first_name = fn;
    if (ln) extra.last_name = ln;
    const fullName = [demoUser.firstName, demoUser.lastName].filter(Boolean).join(' ').trim();
    if (fullName) {
      extra.full_name = fullName;
      extra.fullName = fullName;
      extra.name = fullName;
    }
    const company = (demoUser.businessName || '').trim();
    if (company) {
      extra.company = company;
      extra.organization_name = company;
      extra.organizationName = company;
    }
    const industry = (demoUser.niche || '').trim();
    if (industry) {
      extra.business_type = industry;
      extra.industry = industry;
      extra.service_industry = industry;
      extra.serviceIndustry = industry;

      // Step 2 expects a select value like "plumbing" or "cleaning-services".
      const allowed = new Set([
        'hvac',
        'plumbing',
        'cleaning-services',
        'pool-service',
        'roofing',
        'solar',
        'carpet-cleaning',
        'foundation-repair',
        'dumpster-rental',
        'tree-service',
        'deck-builder',
        'home-inspection',
        'home-windows',
      ]);
      const slug = industry
        .toLowerCase()
        .replace(/['"]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
      if (allowed.has(industry)) {
        extra.industryId = industry;
        extra.industry_id = industry;
      } else if (allowed.has(slug)) {
        extra.industryId = slug;
        extra.industry_id = slug;
      }
    }
  } catch (e) {}
  return extra;
}

/* =========================
   Demo Mode Configuration
   - isPrototype: true = clean app-only page, no tour, no Start Demo screen
   - isDemoMode: true = restricted demo, false = full prototype
   - Fallback: if #jcp-app has data-jcp-page="prototype", treat as prototype (fixes live when PHP global is missing)
========================= */
function _jcpIsPrototype() {
  if (window.JCP_IS_PROTOTYPE === true) return true;
  try {
    const app = document.getElementById('jcp-app');
    if (app && (app.dataset.jcpPage || '').toLowerCase() === 'prototype') return true;
    if (document.body && document.body.classList.contains('jcp-prototype-page')) return true;

    const path = (window.location.pathname || '').toLowerCase();
    if (path === '/prototype' || path === '/prototype/') return true;
  } catch (e) {}
  return false;
}
const isPrototype = _jcpIsPrototype();
const isDemoMode = window.JCP_IS_DEMO_MODE === true;

const MOBILE_DEMO_MQ = '(max-width: 1024px)';

function isMobileViewport() {
  return window.matchMedia(MOBILE_DEMO_MQ).matches;
}

function isMobileDemoRun() {
  return isDemoMode && !isPrototype && isMobileViewport();
}

function isGuidedDemoRun() {
  return isDemoMode && !isPrototype;
}

// Features disabled in demo mode
const demoRestrictions = {
  profileAccess: true,      // Profile button/navigation
  accountSettings: true,    // Settings access
  destructiveActions: true, // Delete, archive, etc.
  locationSwitcher: true,   // Location switching (visible but disabled)
};

/**
 * Show a tooltip when a restricted action is attempted in demo mode
 */
function showDemoRestrictionTooltip(element, message = 'This action is disabled in the demo') {
  if (!isDemoMode) return false;
  
  // Remove any existing tooltip
  const existing = document.querySelector('.demo-restriction-tooltip');
  if (existing) existing.remove();
  
  const tooltip = document.createElement('div');
  tooltip.className = 'demo-restriction-tooltip';
  tooltip.textContent = message;
  tooltip.style.cssText = `
    position: fixed;
    background: #1f2937;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    z-index: 10000;
    pointer-events: none;
    animation: tooltipFade 0.2s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  `;
  
  document.body.appendChild(tooltip);
  
  // Position near the element
  const rect = element.getBoundingClientRect();
  tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
  tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
  
  // Auto-remove after delay
  setTimeout(() => {
    tooltip.style.opacity = '0';
    tooltip.style.transition = 'opacity 0.2s ease';
    setTimeout(() => tooltip.remove(), 200);
  }, 2000);
  
  return true;
}

/**
 * Apply demo mode restrictions to UI elements
 */
function applyDemoRestrictions() {
  if (!isDemoMode || isGuidedDemoRun()) return;
  
  // Add demo mode indicator
  const indicator = document.createElement('div');
  indicator.className = 'demo-mode-indicator';
  indicator.innerHTML = `
    <span class="demo-mode-badge">Demo Mode</span>
    <span class="demo-mode-hint">Some features are restricted</span>
  `;
  indicator.style.cssText = `
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 2px;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
  `;
  document.body.appendChild(indicator);
  
  // Disable profile button everywhere (all tab bars)
  if (demoRestrictions.profileAccess) {
    document.querySelectorAll('[data-action="profile"]').forEach((profileBtn) => {
      profileBtn.classList.add('is-demo-disabled');
      profileBtn.style.opacity = '0.5';
      profileBtn.style.cursor = 'not-allowed';
      profileBtn.setAttribute('aria-disabled', 'true');
      profileBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        showDemoRestrictionTooltip(profileBtn, 'Profile access is disabled in demo mode');
      }, true);
    });
  }

  // Location switcher: in demo mode chip stays visible and sheet can open; switching is no-op (handled in selectLocation / renderLocationList)
  if (demoRestrictions.locationSwitcher) {
    const locationSwitcher = document.getElementById('location-switcher') || document.querySelector('[data-action="switch-location"]');
    if (locationSwitcher) locationSwitcher.classList.add('is-demo-disabled');
  }

  // Archived tab: disabled in demo (setHomeTab shows tooltip on click)
  const tabArchived = document.getElementById('tab-archived');
  if (tabArchived && isDemoMode) tabArchived.classList.add('is-demo-disabled');
  
  // Add CSS for demo disabled state
  const style = document.createElement('style');
  style.textContent = `
    .is-demo-disabled {
      opacity: 0.5 !important;
      cursor: not-allowed !important;
      pointer-events: auto !important;
    }
    .is-demo-disabled * {
      pointer-events: none;
    }
    .demo-restriction-tooltip {
      animation: tooltipFade 0.2s ease;
    }
    @keyframes tooltipFade {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .demo-mode-indicator .demo-mode-badge {
      font-size: 13px;
      font-weight: 700;
    }
    .demo-mode-indicator .demo-mode-hint {
      font-size: 11px;
      opacity: 0.85;
    }
    @media (max-width: 768px) {
      .demo-mode-indicator {
        bottom: 80px !important;
        left: 12px !important;
        padding: 8px 12px !important;
      }
    }
  `;
  document.head.appendChild(style);
}

// In demo mode, hide location modals (interactive only on prototype)
if (isDemoMode) {
  const hide = document.createElement('style');
  hide.textContent = '#location-modal-overlay, #location-prompt-overlay { display: none !important; }';
  document.head.appendChild(hide);
}

function getDemoSessionId() {
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

function jcpDemoTrack(eventType, stepNumber, metadata) {
  const url = window.JCP_DEMO_EVENT && window.JCP_DEMO_EVENT.rest_url;
  if (!url) return;
  try {
    const body = {
      session_id: getDemoSessionId(),
      event_type: eventType,
      step_number: stepNumber != null ? stepNumber : undefined,
      metadata: metadata || undefined
    };
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    }).catch(function() {});
  } catch (e) {}
}

// Optional URL params for email links: ?mode=run&name=Jane&business=ABC+Plumbing&niche=plumbing
function getDemoUserFromUrl() {
  try {
    const params = new URLSearchParams(window.location.search);
    const name = params.get('name') || params.get('first_name');
    const lastName = params.get('last_name');
    const business = params.get('business') || params.get('company');
    const niche = params.get('niche') || params.get('business_type');
    const email = params.get('email');
    if (name || lastName || business || niche || email) {
      return {
        firstName: decodeURIComponent(name || '').trim() || demoUser.firstName,
        lastName: decodeURIComponent(lastName || '').trim() || demoUser.lastName,
        businessName: decodeURIComponent(business || '').trim() || demoUser.businessName,
        niche: decodeURIComponent(niche || '').trim() || demoUser.niche,
        email: decodeURIComponent(email || '').trim() || ''
      };
    }
  } catch (e) {
    // ignore
  }
  return null;
}

try {
  const fromUrl = getDemoUserFromUrl();
  if (fromUrl) {
    demoUser = {
      firstName: fromUrl.firstName || demoUser.firstName,
      lastName: fromUrl.lastName || demoUser.lastName,
      businessName: fromUrl.businessName || demoUser.businessName,
      niche: fromUrl.niche || demoUser.niche,
      email: fromUrl.email || demoUser.email || ''
    };
    try {
      localStorage.setItem('demoUser', JSON.stringify(demoUser));
    } catch (e) {
      // ignore
    }
  } else {
    const stored = localStorage.getItem('demoUser');
    if (stored) {
      const parsed = JSON.parse(stored);

      demoUser = {
        firstName: parsed.firstName || demoUser.firstName,
        lastName: parsed.lastName || demoUser.lastName,
        businessName: parsed.businessName || demoUser.businessName,
        niche: parsed.niche || demoUser.niche,
        email: parsed.email || ''
      };
    }
  }
} catch (e) {
  console.warn('Demo personalization fallback used');
}


/* ---------------------------
   Demo Assets
---------------------------- */
const demoPhotos = [
  'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?w=400&h=400&fit=crop',
  'https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=400&h=400&fit=crop',
  'https://images.unsplash.com/photo-1607400201889-565b1ee75f8e?w=400&h=400&fit=crop'
];

const descriptions = [
  'Replaced an aging water heater and brought the system up to code. We installed a new high-efficiency unit, verified proper venting, and tested temperature + pressure relief for safe operation. Customer is back to consistent hot water with improved energy performance.',
  'Completed a full water heater swap-out: removed the failing tank, installed a new unit, reconnected lines, and confirmed there are no leaks. Verified ignition and heating cycle, set the thermostat, and cleaned up the work area before departure.',
  'Installed a new water heater and ensured everything is running safely and efficiently. Connections were tightened, valves were tested, and we confirmed stable hot-water delivery throughout the home.'
];

/* ---------------------------
   State
---------------------------- */
const state = {
  currentScreen: 'login-screen',
  hasPublished: false,
  guideDisabled: false,
  isFinalStep: false,
  photoCount: 0,
  metrics: { checkins: 12, posts: 36, reviews: 48 },
  currentDescriptionIndex: 0,
  savedCheckins: [],
  archivedCheckins: [],
  homeActiveTab: 'my-jobs',
  activeCheckinIndex: null,
  activeCheckinFromArchived: false,
  comingFromProcessPhotos: false,
  map: null,
  mapMarkers: [],
  guideHidden: false,
  selectedTags: [],
  reviewMethodBackScreen: 'home-screen',
};

const EDIT_TAGS = [
  { id: 'plumbing', label: 'Plumbing' },
  { id: 'water-heater', label: 'Water Heater' },
  { id: 'hvac', label: 'HVAC' },
  { id: 'emergency', label: 'Emergency' },
  { id: 'same-day', label: 'Same-day' },
  { id: 'routine', label: 'Routine Service' },
];

const CHECKINS_STORAGE_KEY = 'jcp_checkins';
const ARCHIVED_STORAGE_KEY = 'jcp_archived_checkins';

function persistCheckins() {
  if (isDemoMode) return;
  try {
    localStorage.setItem(CHECKINS_STORAGE_KEY, JSON.stringify(state.savedCheckins));
    localStorage.setItem(ARCHIVED_STORAGE_KEY, JSON.stringify(state.archivedCheckins));
  } catch (e) {}
}

function loadCheckins() {
  if (isDemoMode) return;
  try {
    const saved = localStorage.getItem(CHECKINS_STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      state.savedCheckins = Array.isArray(parsed) ? parsed : [];
    }
    const archived = localStorage.getItem(ARCHIVED_STORAGE_KEY);
    if (archived) {
      const parsed = JSON.parse(archived);
      state.archivedCheckins = Array.isArray(parsed) ? parsed : [];
    }
  } catch (e) {}
}

/* ---------------------------
   Locations (mock data for prototype)
---------------------------- */
const LOCATIONS = [
  { id: 'austin', name: 'Austin', city: 'Austin', state: 'TX', lat: 30.2672, lng: -97.7431 },
  { id: 'round-rock', name: 'Round Rock', city: 'Round Rock', state: 'TX', lat: 30.5083, lng: -97.6789 },
  { id: 'cedar-park', name: 'Cedar Park', city: 'Cedar Park', state: 'TX', lat: 30.5052, lng: -97.8203 },
  { id: 'georgetown', name: 'Georgetown', city: 'Georgetown', state: 'TX', lat: 30.6333, lng: -97.6784 },
  { id: 'pflugerville', name: 'Pflugerville', city: 'Pflugerville', state: 'TX', lat: 30.4397, lng: -97.6200 },
  { id: 'san-marcos', name: 'San Marcos', city: 'San Marcos', state: 'TX', lat: 29.8833, lng: -97.9414 },
  { id: 'kyle', name: 'Kyle', city: 'Kyle', state: 'TX', lat: 29.9891, lng: -97.8772 },
];

const locationState = {
  activeId: null,
  userCoords: null,
};

const LOCATION_STORAGE_KEY = 'jcp_active_location_id';

/* ---------------------------
   Guide Content
---------------------------- */
const DEMO_OUTCOME_ITEMS = [
  'Published on your website',
  'Posted to social media',
  'Live on Google Business',
  'Added to JobCapturePro directory',
  'Review request sent',
];

const demoGuideContent = {
  step1: {
    pill: 'Step 1',
    title: 'Start the demo',
    body: 'Tap Start Demo above. You will walk through the real workflow step by step.',
    interactHint: 'Tap the highlighted Start Demo button.'
  },
  step2: {
    pill: 'Step 2',
    title: 'Tap + to create a check-in',
    body: 'This is what your tech does on each job. One quick check-in powers everything.',
    interactHint: 'Tap the + button, then choose New Check-in.'
  },
  step3: {
    pill: 'Step 3',
    title: 'Add a photo, then submit',
    body: 'Photos generate the job content. Add one photo, then tap Submit.',
    interactHint: 'Tap the camera, add a photo, then tap Submit.'
  },
  step4: {
    pill: 'Step 4',
    title: 'Publish the job',
    body: 'Tap Publish Everywhere to push this job to your website, Google, social media and directory.',
    interactHint: 'Tap Publish Everywhere to continue.'
  },
  step5: {
    pill: 'Step 5',
    title: 'Request a review',
    body: 'Review requests go out automatically. Tap Request Review to preview it.',
    interactHint: 'Tap Request Review to preview the automatic send.'
  },
  step6: {
    pill: 'Final step',
    title: 'See everything that published',
    body: 'Swipe the dots to explore what happened automatically for this job.',
    interactHint: ''
  },
  step6Dock: {
    pill: 'Final step',
    title: 'Ready to get started?',
    body: 'Start free and turn every completed job into proof that drives more calls.',
    interactHint: ''
  }
};

const OUTCOMES_SLIDE_LABELS = [
  'Live on your website',
  'Posted to social media',
  'Live on Google Business',
  'Added to JobCapturePro directory',
  'New 5-star review received',
];

const outcomesSlideshow = {
  index: 0,
  total: 5,
  isOpen: false,
  touchStartX: 0,
};


/* ---------------------------
   DOM Helpers
---------------------------- */
const $ = (id) => document.getElementById(id);
function escapeHtml(s) {
  if (s == null) return '';
  const t = String(s);
  return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function safeText(id, value) {
  const el = $(id);
  if (el) el.textContent = value;
}

function excerptText(str, maxWords = 10) {
  if (!str || typeof str !== 'string') return str || '';
  const words = str.trim().split(/\s+/);
  if (words.length <= maxWords) return str;
  return words.slice(0, maxWords).join(' ') + '...';
}

function applyPersonalization() {
  // Website header title
  const h1 = document.querySelector('.website-header h1');
  if (h1) h1.textContent = `Recent Work from ${demoUser.businessName}`;

  // Optional: personalize browser URL pill (if present)
  const urlPill = document.querySelector('.browser-url');
  if (urlPill) {
    const slug = demoUser.businessName.toLowerCase().replace(/[^a-z0-9]+/g, '');
    urlPill.textContent = `${slug}.com/jobs`;
  }
}

function updateProfilePersonalization() {
  const nameEl = $('profile-name');
  const emailEl = $('profile-email');
  if (nameEl) nameEl.textContent = demoUser.firstName || 'User';
  if (emailEl) {
    const email = demoUser.email || `${(demoUser.firstName || 'user').toLowerCase()}@${(demoUser.businessName || 'company').replace(/\s+/g, '').toLowerCase()}.com`;
    emailEl.textContent = email;
  }
}

/* ---------------------------
   Location Switcher (prototype only)
---------------------------- */
function getActiveLocation() {
  const id = locationState.activeId || LOCATIONS[0].id;
  return LOCATIONS.find((loc) => loc.id === id) || LOCATIONS[0];
}

function getLocationDisplay(loc) {
  return loc ? `${loc.city}, ${loc.state}` : 'Austin, TX';
}

function updateLocationUI() {
  const loc = getActiveLocation();
  const label = document.querySelector('.location-switcher .location-switcher__label');
  if (label) label.textContent = getLocationDisplay(loc);
}

function haversineMi(lat1, lon1, lat2, lon2) {
  const R = 3959;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

function formatDistance(mi) {
  if (mi == null || mi < 0) return '';
  if (mi < 10) return mi.toFixed(1) + ' mi';
  return '~' + Math.round(mi) + ' mi';
}

function getLocationsWithDistance(searchQuery) {
  const q = (searchQuery || '').toLowerCase().trim();
  let list = LOCATIONS.map((loc) => {
    const distance = locationState.userCoords
      ? haversineMi(
        locationState.userCoords.lat,
        locationState.userCoords.lng,
        loc.lat,
        loc.lng
      )
      : null;
    return { ...loc, distance };
  });
  if (q) {
    list = list.filter(
      (loc) =>
        loc.name.toLowerCase().includes(q) ||
        loc.city.toLowerCase().includes(q) ||
        loc.state.toLowerCase().includes(q)
    );
  }
  list.sort((a, b) => {
    if (a.distance != null && b.distance != null) return a.distance - b.distance;
    if (a.distance != null) return -1;
    if (b.distance != null) return 1;
    return a.name.localeCompare(b.name);
  });
  return list;
}

function openLocationSheet() {
  const overlay = $('location-modal-overlay');
  const listEl = $('location-modal-list');
  const searchEl = $('location-modal-search');
  if (!overlay || !listEl) return;
  if (searchEl) searchEl.value = '';
  renderLocationList('');
  overlay.classList.add('is-open');
  overlay.setAttribute('aria-hidden', 'false');
  if (searchEl) searchEl.focus();
}

function closeLocationSheet() {
  const overlay = $('location-modal-overlay');
  if (!overlay) return;
  overlay.classList.remove('is-open');
  overlay.setAttribute('aria-hidden', 'true');
}

function renderLocationList(searchQuery) {
  const listEl = $('location-modal-list');
  if (!listEl) return;
  const activeId = locationState.activeId || LOCATIONS[0].id;
  const list = getLocationsWithDistance(searchQuery);
  const hasUserCoords = locationState.userCoords != null;
  const showGroups = hasUserCoords && !(searchQuery || '').trim() && list.length > 0;
  const closestCount = showGroups ? Math.min(5, list.length) : 0;
  const closest = showGroups ? list.slice(0, closestCount) : [];
  const other = showGroups ? list.slice(closestCount) : list;

  function itemMarkup(loc) {
    const isActive = loc.id === activeId;
    const distStr = formatDistance(loc.distance);
    const currentBadge = isActive ? '<span class="location-sheet-item-current-badge">Current</span>' : '';
    const itemClass = 'location-sheet-item' + (isActive ? ' is-active' : '') + (isDemoMode ? ' is-demo-disabled' : '');
    return `
      <button type="button" class="${itemClass}" data-location-id="${loc.id}" role="option">
        <div class="location-sheet-item-name-row">
          <span class="location-sheet-item-name">${loc.name}</span>
          ${currentBadge}
        </div>
        <span class="location-sheet-item-meta">${loc.city}, ${loc.state}</span>
        ${distStr ? `<span class="location-sheet-item-distance">${distStr}</span>` : ''}
      </button>`;
  }

  let html = '';
  if (showGroups && closest.length > 0) {
    html += '<p class="location-sheet-group-title">Closest to you</p>';
    closest.forEach((loc) => { html += itemMarkup(loc); });
  }
  if (showGroups && other.length > 0) {
    html += '<p class="location-sheet-group-title">Other locations</p>';
    other.forEach((loc) => { html += itemMarkup(loc); });
  }
  if (!showGroups) {
    list.forEach((loc) => { html += itemMarkup(loc); });
  }
  listEl.innerHTML = html;

  listEl.querySelectorAll('.location-sheet-item').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (isDemoMode) {
        showDemoRestrictionTooltip(btn, 'Switching locations is disabled in demo');
        return;
      }
      const id = btn.getAttribute('data-location-id');
      if (id) selectLocation(id);
    });
  });
}

function selectLocation(id) {
  if (isDemoMode) return;
  const loc = LOCATIONS.find((l) => l.id === id);
  if (!loc) return;
  locationState.activeId = id;
  try {
    localStorage.setItem(LOCATION_STORAGE_KEY, id);
  } catch (e) {}
  updateLocationUI();
  closeLocationSheet();
  renderHomeCheckins();
}

function initLocationSwitcher() {
  try {
    const saved = localStorage.getItem(LOCATION_STORAGE_KEY);
    if (saved && LOCATIONS.some((l) => l.id === saved)) {
      locationState.activeId = saved;
    }
  } catch (e) {}
  updateLocationUI();

  const trigger = $('location-switcher');
  const overlay = $('location-modal-overlay');
  const closeBtn = $('location-modal-close');
  const searchEl = $('location-modal-search');

  if (trigger) {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      openLocationSheet();
    });
  }
  if (closeBtn) closeBtn.addEventListener('click', closeLocationSheet);
  if (overlay) {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeLocationSheet();
    });
  }
  if (searchEl) {
    searchEl.addEventListener('input', () => renderLocationList(searchEl.value));
    searchEl.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeLocationSheet();
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) {
      closeLocationSheet();
    }
  });
}

function openTagSheet() {
  const overlay = $('tag-sheet-overlay');
  const sheet = $('tag-sheet');
  if (overlay) overlay.classList.add('is-open');
  if (sheet) sheet.classList.add('is-open');
  renderTagSheetList();
}

function closeTagSheet() {
  const overlay = $('tag-sheet-overlay');
  const sheet = $('tag-sheet');
  if (overlay) overlay.classList.remove('is-open');
  if (sheet) sheet.classList.remove('is-open');
}

function renderTagSheetList() {
  const listEl = $('tag-sheet-list');
  if (!listEl) return;
  listEl.innerHTML = EDIT_TAGS.map((tag) => {
    const selected = state.selectedTags.includes(tag.id);
    const itemClass = 'location-sheet-item' + (selected ? ' is-active' : '');
    return `<button type="button" class="${itemClass}" data-tag-id="${tag.id}" role="option">
        <div class="location-sheet-item-name-row">
          <span class="location-sheet-item-name">${escapeHtml(tag.label)}</span>
        </div>
      </button>`;
  }).join('');
  listEl.querySelectorAll('.location-sheet-item').forEach((btn) => {
    btn.addEventListener('click', () => toggleTag(btn.dataset.tagId));
  });
}

function renderTagPills() {
  const wrap = $('edit-tag-pills');
  if (!wrap) return;
  wrap.innerHTML = state.selectedTags.map((id) => {
    const tag = EDIT_TAGS.find((t) => t.id === id);
    if (!tag) return '';
    return `<span class="tag-pill" data-tag-id="${tag.id}">${escapeHtml(tag.label)}<button type="button" class="tag-pill-remove" aria-label="Remove tag">×</button></span>`;
  }).join('');
  wrap.querySelectorAll('.tag-pill-remove').forEach((btn) => {
    const pill = btn.closest('.tag-pill');
    if (pill) btn.addEventListener('click', () => toggleTag(pill.dataset.tagId));
  });
}

function toggleTag(tagId) {
  const idx = state.selectedTags.indexOf(tagId);
  if (idx === -1) state.selectedTags.push(tagId);
  else state.selectedTags.splice(idx, 1);
  renderTagPills();
  renderTagSheetList();
}

function initTagSelector() {
  const trigger = $('tag-selector-trigger');
  const overlay = $('tag-sheet-overlay');
  const closeBtn = $('tag-sheet-close');
  if (trigger) trigger.addEventListener('click', (e) => { e.preventDefault(); openTagSheet(); });
  if (closeBtn) closeBtn.addEventListener('click', closeTagSheet);
  if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeTagSheet(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const o = $('tag-sheet-overlay');
      if (o && o.classList.contains('is-open')) closeTagSheet();
    }
  });
  renderTagPills();
}

/**
 * Show the "nearer location" prompt. Prototype: static, no geolocation, shows every time.
 * Demo: not used (prompt is prototype-only).
 */
function showSmartLocationPrompt(closerLocation) {
  if (!isPrototype || isDemoMode) return;
  if (!closerLocation) return;
  const overlay = $('location-prompt-overlay');
  const nameEl = $('location-prompt-name');
  const keepBtn = $('location-prompt-keep');
  const switchBtn = $('location-prompt-switch');
  if (!overlay || !nameEl) return;
  nameEl.textContent = closerLocation.name;
  overlay.classList.add('is-open');
  overlay.setAttribute('aria-hidden', 'false');
  const closePrompt = () => {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
  };
  if (keepBtn) keepBtn.addEventListener('click', closePrompt, { once: true });
  if (switchBtn) switchBtn.addEventListener('click', () => {
    selectLocation(closerLocation.id);
    closePrompt();
  }, { once: true });
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closePrompt(); }, { once: true });
}

/**
 * Prototype only: show the "nearer location" prompt every time with generic/static info.
 * No geolocation — browser never asks for location. Suggests a different location than
 * current; if user clicks Switch, we switch to that location. For developer reference
 * when building the real app (dynamic geolocation + closest location).
 */
function initLocationSmartPrompt() {
  if (!isPrototype || isDemoMode) return;
  const active = getActiveLocation();
  const suggested = active.id === LOCATIONS[0].id ? LOCATIONS[1] : LOCATIONS[0];
  setTimeout(() => showSmartLocationPrompt(suggested), 800);
}

/* ---------------------------
   Utilities
---------------------------- */
function wait(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function pulse(id) {
  const el = $(id);
  if (!el) return;
  el.classList.add('pulsing');
  setTimeout(() => el.classList.remove('pulsing'), 900);
}

async function pulseSequence(ids, delay = 350) {
  for (const id of ids) {
    pulse(id);
    await wait(delay);
  }
}

/* =========================================================
   Layout Systems
   - Tour docking
   - Focal points
   - Mobile mode
========================================================= */

function applyFocalPoint() {
  const zones = {
    phone: $('focus-phone'),
    website: $('focus-website'),
    social: $('focus-social')
  };

  if (isGuidedDemoRun()) {
    document.querySelectorAll('.is-dimmed, .is-focused').forEach((el) => {
      el.classList.remove('is-dimmed', 'is-focused');
    });
    document.body.classList.remove('show-website', 'show-social');

    if (state.guideDisabled || state.isFinalStep) {
      hideMobileSpotlight();
      return;
    }

    positionMobileSpotlight();
    return;
  }

  if (state.guideDisabled || state.isFinalStep) {
    document.body.classList.remove('show-website', 'show-social');
    document.querySelectorAll('.is-dimmed, .is-focused').forEach((el) => {
      el.classList.remove('is-dimmed', 'is-focused');
    });
    return;
  }

  Object.values(zones).forEach((el) => {
    if (!el) return;
    el.classList.add('is-dimmed');
    el.classList.remove('is-focused');
  });

  const focus = (el) => {
    if (!el) return;
    el.classList.remove('is-dimmed');
    el.classList.add('is-focused');
  };

  switch (tour.stepKey) {
    case 'step1':
    case 'step2':
    case 'step3':
    case 'step4':
      focus(zones.phone);
      break;
    case 'step5':
      focus(zones.social);
      break;
    default:
      focus(zones.website);
  }

  document.body.classList.toggle('show-website', ['step4', 'step6'].includes(tour.stepKey));
  document.body.classList.toggle('show-social', tour.stepKey === 'step5');
}

function applyMobileMode() {
  const isMobile = isMobileViewport();
  document.body.classList.toggle('is-mobile-mode', isMobile);

  const usePhoneShell = isPrototype || (isDemoMode && isMobile);
  document.body.classList.toggle('jcp-phone-shell', usePhoneShell);
  document.documentElement.classList.toggle('jcp-demo-run-mobile', isDemoMode && isMobile);
  document.body.classList.toggle('jcp-guided-demo', isGuidedDemoRun());
  document.body.classList.toggle('jcp-desktop-guided', isGuidedDemoRun() && !isMobileViewport());
  document.body.classList.toggle('demo-run-only', isDemoMode);

  const stepper = $('mobile-stepper');
  if (stepper) {
    if (isGuidedDemoRun()) {
      stepper.style.display = 'flex';
    } else {
      stepper.style.display = 'none';
    }
  }

  updateGuidedCoachBackdrop();
  syncMobileGuideChrome();
}

function updateGuidedCoachBackdrop() {
  const backdrop = $('guidedCoachBackdrop');
  if (!backdrop) return;
  const show = isGuidedDemoRun() && !mobileGuideCollapsed && !state.isFinalStep;
  backdrop.hidden = !show;
}

let mobileGuideCollapsed = false;

function hideMobileSpotlight() {
  const ring = $('mobileSpotlight');
  if (ring) ring.style.display = 'none';
}

function positionMobileSpotlight() {
  const ring = $('mobileSpotlight');
  if (!ring || !isGuidedDemoRun() || mobileGuideCollapsed || tour.isHidden || state.guideDisabled || state.isFinalStep) {
    hideMobileSpotlight();
    return;
  }

  const selector = tour.anchors[tour.stepKey];
  const target = selector ? document.querySelector(selector) : null;
  if (!target || target.offsetParent === null) {
    hideMobileSpotlight();
    return;
  }

  const pad = 10;
  const r = target.getBoundingClientRect();
  ring.style.display = 'block';
  ring.style.top = `${Math.round(r.top - pad)}px`;
  ring.style.left = `${Math.round(r.left - pad)}px`;
  ring.style.width = `${Math.round(r.width + pad * 2)}px`;
  ring.style.height = `${Math.round(r.height + pad * 2)}px`;
}

function setMobileGuideCollapsed(collapsed) {
  mobileGuideCollapsed = collapsed;
  document.body.classList.toggle('jcp-mobile-guide-collapsed', collapsed);
  const stepper = $('mobile-stepper');
  const pill = $('mobileGuidePill');
  if (stepper) stepper.classList.toggle('is-collapsed', collapsed);
  if (pill) pill.hidden = !collapsed || state.isFinalStep;
  updateGuidedCoachBackdrop();
  if (collapsed && isGuidedDemoRun()) {
    const stepNum = tour.stepKey && /^step(\d)$/.test(tour.stepKey) ? parseInt(tour.stepKey.slice(-1), 10) : null;
    jcpDemoTrack('demo_coach_minimized', stepNum);
  }
  if (collapsed) {
    hideMobileSpotlight();
  } else {
    positionMobileSpotlight();
  }
  updateMobileLayoutMetrics();
}

function toggleMobileGuideCollapse() {
  setMobileGuideCollapsed(!mobileGuideCollapsed);
}

function syncMobileGuideChrome() {
  if (!isGuidedDemoRun()) {
    updateGuidedCoachBackdrop();
    return;
  }

  const stepKey = tour.stepKey;
  const step = stepKey === 'step6' && !outcomesSlideshow.isOpen
    ? demoGuideContent.step6Dock
    : (stepKey ? demoGuideContent[stepKey] : null);
  const stepNum = stepKey && /^step(\d)$/.test(stepKey) ? parseInt(stepKey.slice(-1), 10) : null;
  const total = 6;

  if (stepNum) {
    safeText('mobileDemoStep', `Step ${stepNum} of ${total}`);
    const fill = $('mobileStepProgressFill');
    if (fill) fill.style.width = `${(stepNum / total) * 100}%`;
  }

  if (step) {
    safeText('mobileStepTitle', step.title);
    safeText('mobileStepBody', step.body);
    safeText('mobileGuidePillText', step.pill);
  }

  updateMobileStepperLabel();
  updateMobileLayoutMetrics();
  positionMobileSpotlight();
  updateGuidedCoachBackdrop();
}

function updateMobileLayoutMetrics() {
  if (!isGuidedDemoRun() || !document.body.classList.contains('is-mobile-mode')) return;
  const stepper = $('mobile-stepper');
  let height = 168;
  if (stepper && !stepper.classList.contains('is-collapsed')) {
    const style = window.getComputedStyle(stepper);
    if (style.display !== 'none' && style.visibility !== 'hidden') {
      height = Math.ceil(stepper.getBoundingClientRect().height);
    }
  } else {
    height = 72;
  }
  document.documentElement.style.setProperty('--jcp-stepper-height', `${height}px`);
}

function getDemoSalesPhoneHref() {
  const raw =
    (window.JCP_ONBOARDING && window.JCP_ONBOARDING.salesPhone) ||
    (window.JCP_CONFIG && window.JCP_CONFIG.salesPhone) ||
    '';
  const digits = String(raw).replace(/[^\d+]/g, '');
  if (digits) return `tel:${digits}`;
  return `${(baseUrl || '').replace(/\/$/, '')}/contact/`;
}

function updateMobileStepperLabel() {
  const btn = $('btnMobileNext');
  const hint = $('mobileStepInteractHint');
  if (!btn || !isGuidedDemoRun()) return;

  const label = tour.stepKey ? getNextLabelForStep(tour.stepKey) : 'Next →';
  btn.textContent = label;

  const hideNext = tour.stepKey !== 'step6' || outcomesSlideshow.isOpen;
  btn.style.display = hideNext ? 'none' : '';

  const reopen = $('demoOutcomesReopenCta');
  if (reopen) {
    const showReopen = tour.stepKey === 'step6' && !outcomesSlideshow.isOpen;
    reopen.hidden = !showReopen;
  }

  if (hint) {
    const step = tour.stepKey ? demoGuideContent[tour.stepKey] : null;
    const hintText = step?.interactHint || '';
    hint.textContent = hintText;
    hint.hidden = !hideNext || !hintText;
  }
}

/* =========================================================
   Floating Tour Tooltip (moves to target, collapsible)
========================================================= */

const tour = {
  isHidden: false,
  isMinimized: false,

  // This is the ONLY thing that should control what the tooltip says
  stepKey: 'step1',

    anchors: {
      step1: '#btnStartDemo',
      step2: '#fabNewCheckin',
      step3: '#submit-btn',
      step4: '#btnSavePublish',
      step5: '#btnRequestReview',
      step6: '#demoOutcomesModal',
    }
};


function showTour() {
  if (isGuidedDemoRun()) {
    syncMobileGuideChrome();
    $('tour-float')?.classList.add('is-hidden');
    $('tour-bubble')?.classList.add('is-hidden');
    return;
  }

  const el = $('tour-float');
  const bubble = $('tour-bubble');
  if (!el || !bubble) return;

  bubble.classList.add('is-expanding');

  el.classList.remove('is-hidden');
  bubble.classList.add('is-hidden');

  setTimeout(() => {
    bubble.classList.remove('is-expanding');
  }, 200);

  tour.isHidden = false;
  tour.isMinimized = false;
}

function minimizeTour() {
  if (isGuidedDemoRun()) {
    toggleMobileGuideCollapse();
    return;
  }
  if (state.isFinalStep) return;

  const el = $('tour-float');
  const bubble = $('tour-bubble');
  if (!el || !bubble) return;

  el.classList.add('is-minimizing');

  setTimeout(() => {
    el.classList.add('is-hidden');
    el.classList.remove('is-minimizing');
    bubble.classList.remove('is-hidden');
  }, 300);

  tour.isMinimized = true;
}

function closeTour() {
  const el = $('tour-float');
  const bubble = $('tour-bubble');

  if (el) el.classList.add('is-hidden');
  if (bubble) bubble.classList.add('is-hidden');

  tour.isHidden = true;
  tour.isMinimized = false;
}

function setTourStep(stepKey) {
  const tourEl = document.getElementById('tour-float');
  state.isFinalStep = stepKey === 'step6';
  const bubble = document.getElementById('tour-bubble');

  if (state.isFinalStep && bubble) {
    bubble.classList.add('is-hidden');
  }
    if (tourEl) {
      tourEl.classList.toggle('final-step', state.isFinalStep);
    }
  tour.stepKey = stepKey;
  if (stepKey) {
    document.body.dataset.tourStep = stepKey;
  } else {
    delete document.body.dataset.tourStep;
  }
  if (isGuidedDemoRun() && stepKey !== 'step6') {
    setMobileGuideCollapsed(false);
  }
  const stepNum = stepKey && stepKey.match(/^step(\d)$/) ? parseInt(stepKey.slice(-1), 10) : null;
  if (stepNum >= 1 && stepNum <= 6) {
    jcpDemoTrack('demo_step_viewed', stepNum);
  }
  updateTourNextLabel(getNextLabelForStep(stepKey));
  updateMobileStepperLabel();
  syncCreateSheetDemoState();
  // Disable back buttons during guided steps (prevents breaking the flow) — skip on prototype
  if (!isPrototype) {
    lockBackButtons(['step2','step3','step4','step5'].includes(stepKey));
  }

    // Control Request Review button by tour step — on prototype always enabled
    const requestReviewBtn = document.getElementById('btnRequestReview');
    if (requestReviewBtn) {
      const enabled = isPrototype || stepKey === 'step5';
      requestReviewBtn.disabled = !enabled;
      requestReviewBtn.classList.toggle('is-disabled', !enabled);
    }

    const minimizeBtn = document.getElementById('tour-minimize');
    const nextBtn = document.getElementById('tour-next');

    if (state.isFinalStep) {
      minimizeBtn?.classList.add('is-hidden');
      nextBtn?.classList.add('is-hidden');

      // Final step = close-only tooltip
      document.getElementById('tour-close')?.classList.remove('is-hidden');
    } else {
      minimizeBtn?.classList.remove('is-hidden');
      nextBtn?.classList.remove('is-hidden');
      document.getElementById('tour-close')?.classList.add('is-hidden');
    }

    const fab = document.getElementById('fabNewCheckin');
    const checkins = document.querySelectorAll('.home-checkin-item');

    // Re-enable interactive elements for guided steps
    if (fab) {
      fab.classList.remove('is-disabled');
    }

    checkins.forEach(item => {
      item.classList.remove('is-disabled');
    });

  updateTourFloating();
  applyFocalPoint();
  if (isGuidedDemoRun() && document.body.classList.contains('is-mobile-mode') && stepKey !== 'step6') {
    const screen = document.querySelector('.iphone-frame .screen');
    if (screen) screen.scrollTop = 0;
    const activeApp = document.querySelector('.app-screen.active');
    const contentArea = activeApp?.querySelector('.content-area');
    if (contentArea) contentArea.scrollTop = 0;
  }
  scrollGuidedStepTarget(stepKey);
}

function getNextLabelForStep(stepKey) {
  if (stepKey === 'step4') return 'Publish →';
  if (stepKey === 'step5') return 'Send Review →';
  if (stepKey === 'step6') return 'Get Started Free';
  return 'Next →';
}

function updateTourFloating() {
  if (isGuidedDemoRun()) {
    syncMobileGuideChrome();
    return;
  }

  if (tour.isHidden || tour.isMinimized) return;

  const step = demoGuideContent[tour.stepKey];
  if (!step) return;

  if (state.isFinalStep) {
    safeText('tour-pill', '');
    safeText('tour-float-title', 'View your live directory');
    safeText('tour-float-body', 'This is now visible to customers.');
  } else {
    safeText('tour-pill', step.pill);
    safeText('tour-float-title', step.title);
    safeText('tour-float-body', step.body);
  }

  positionTourNear();
  
  // Auto-fade tooltip after 5 seconds for final step
  if (state.isFinalStep) {
    const tourFloat = document.getElementById('tour-float');
    if (tourFloat && !tourFloat.dataset.fadeScheduled) {
      tourFloat.dataset.fadeScheduled = 'true';
      setTimeout(() => {
        if (tourFloat && state.isFinalStep) {
          tourFloat.style.transition = 'opacity 0.5s ease-out';
          tourFloat.style.opacity = '0';
          setTimeout(() => {
            if (tourFloat) {
              tourFloat.classList.add('is-hidden');
              tourFloat.style.opacity = '';
              tourFloat.style.transition = '';
              delete tourFloat.dataset.fadeScheduled;
            }
          }, 500);
        }
      }, 5000);
    }
  }
}

function positionTourNear() {
  if (isGuidedDemoRun()) return;

  const floatEl = $('tour-float');
  const arrow = $('tour-arrow');
  if (!floatEl || !arrow) return;

  const selector = tour.anchors[tour.stepKey];
  const isReviewStep = tour.stepKey === 'step5';
  const isFinalStep = tour.stepKey === 'step6';
  const target = document.querySelector(selector);
  if (!target) return;

  floatEl.classList.remove('is-hidden');

  const r = target.getBoundingClientRect();
  const w = floatEl.offsetWidth;
  const h = floatEl.offsetHeight;
  const pad = 12;

  let top, left, side;

  // STEP 6: center below "View Demo Directory" button
  if (isFinalStep) {
    // BELOW the header button, arrow points UP
    top = r.bottom + 12;
    left = r.left + r.width / 2 - w / 2;
    // Ensure it doesn't go off-screen to the left
    if (left < pad) {
      left = pad;
    }
    // Ensure it doesn't go off-screen to the right
    if (left + w > window.innerWidth - pad) {
      left = window.innerWidth - w - pad;
    }
    side = 'top';
  } else if (isReviewStep) {
    top = r.bottom + 14;
    left = r.left + r.width / 2 - w / 2;
    side = 'top';
  } else {
    top = r.top + r.height / 2 - h / 2;
    left = r.right + 14;
    side = 'right';
  }

  if (left + w > window.innerWidth) {
    left = r.left - w - 14;
    side = 'left';
  }

  top = Math.max(pad, Math.min(top, window.innerHeight - h - pad));
  left = Math.max(pad, Math.min(left, window.innerWidth - w - pad));

  floatEl.style.top = `${Math.round(top)}px`;
  floatEl.style.left = `${Math.round(left)}px`;

  if (side === 'bottom') {
    arrow.style.bottom = `-7px`;
    arrow.style.top = 'auto';
    arrow.style.left = `${Math.round(w / 2 - 7)}px`;
    arrow.style.right = 'auto';
  } else if (side === 'top') {
    arrow.style.top = `-7px`;
    arrow.style.bottom = 'auto';
    arrow.style.left = `${Math.round(w / 2 - 7)}px`;
    arrow.style.right = 'auto';
  } else {
    arrow.style.top = `${Math.round(r.top + r.height / 2 - top)}px`;
    arrow.style.left = side === 'right' ? `-7px` : 'auto';
    arrow.style.right = side === 'left' ? `-7px` : 'auto';
    arrow.style.bottom = 'auto';
  }
}

function syncAttentionAnimations() {
  const startBtn = $('btnStartDemo');
  const fab = $('fabNewCheckin');
  const submitBtn = $('submit-btn');

  // Clear all
  startBtn?.classList.remove('wiggle-attention');
  fab?.classList.remove('fab-glow', 'fab-attention');
  submitBtn?.classList.remove('wiggle-attention');

  if (state.currentScreen === 'login-screen') {
    startBtn?.classList.add('wiggle-attention');
  }

  if (state.currentScreen === 'home-screen' && state.savedCheckins.length === 0) {
    fab?.classList.add('fab-attention');
  }

  if (state.currentScreen === 'new-screen') {
    // Only if they have photos
    if (state.photoCount >= 1) submitBtn?.classList.add('wiggle-attention');
  }
}

/* =========================================================
   Screens / Navigation
========================================================= */
function lockBackButtons(lock) {
  if (isPrototype) lock = false; // Prototype: all controls always usable
  const buttons = ['btnBackToHome', 'btnEditBack'];
  buttons.forEach(id => {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.classList.toggle('is-disabled', lock);
    btn.disabled = lock;
  });
}

/** On prototype: ensure all buttons, back arrows, and controls are active and usable */
function ensurePrototypeControlsEnabled() {
  if (!isPrototype) return;
  lockBackButtons(false);
  const requestReviewBtn = document.getElementById('btnRequestReview');
  if (requestReviewBtn) {
    requestReviewBtn.disabled = false;
    requestReviewBtn.classList.remove('is-disabled');
  }
  const fab = document.getElementById('fabNewCheckin');
  if (fab) {
    fab.classList.remove('is-disabled');
    fab.disabled = false;
  }
  document.querySelectorAll('.home-checkin-item').forEach((el) => {
    el.classList.remove('is-disabled');
  });
  document.querySelectorAll('.back-btn').forEach((btn) => {
    btn.classList.remove('is-disabled');
    btn.disabled = false;
  });
}

function setScreen(screenId) {
  document.querySelectorAll('.app-screen').forEach(s => s.classList.remove('active'));
  const target = $(screenId);
  if (target) target.classList.add('active');

  state.currentScreen = screenId;

  if (isPrototype && typeof window.CustomEvent !== 'undefined') {
    window.dispatchEvent(new CustomEvent('jcp-prototype-screen-change', { detail: { screenId } }));
  }

// Tour is step-driven, not screen-driven
updateTourFloating();


if (['login-screen','home-screen','new-screen','edit-screen','profile-screen','edit-profile-screen','change-password-screen','review-request-options-screen','request-review-screen'].includes(screenId)) {
  applyFocalPoint();
}

if (screenId === 'edit-screen' && !state.hasPublished) {
  updateTourNextLabel('Publish →');
} else if (screenId === 'edit-screen' && state.hasPublished) {
  updateTourNextLabel('Request Review →');
} else {
  updateTourNextLabel('Next →');
}

  // Attention
  syncAttentionAnimations();
}

/* =========================================================
   Website Preview (Right Panel)
========================================================= */

function initializeWebsite() {
  const container = $('website-checkins');
  if (!container) return;

  container.innerHTML = `
    <div class="empty-state" id="website-empty">
      <h3>Your published check-ins appear here</h3>
      <p>
        Publish a check-in to automatically add a job card here (photos, location, proof of work).
      </p>
    </div>
  `;

  applyPersonalization();
}

const header = document.querySelector('.website-header h1');
if (header) {
  header.textContent = `Recent Work from ${demoUser.businessName}`;
}

function createCheckinCard(checkin, isNew = false) {
  return `
    <div class="checkin-card ${isNew ? 'new' : ''}">
      <div class="checkin-image">
        <img src="${checkin.image}" alt="${checkin.title}" width="400" height="300" loading="lazy">
      </div>
      <div class="checkin-content">
        <div class="checkin-title">${checkin.title}</div>
        <div class="checkin-meta">
          <img src="${assetBase}/shared/assets/icons/lucide/map-pin.svg" class="lucide-icon lucide-icon-sm" alt="">
          <span>Near ${checkin.location}</span>
        </div>
        <div class="checkin-meta">
          <img src="${assetBase}/shared/assets/icons/lucide/calendar.svg" class="lucide-icon lucide-icon-sm" alt="">
          <span>${checkin.date}</span>
        </div>
      </div>
    </div>
  `;
}

function loadSampleCheckins() {
  if (state.savedCheckins.length > 0) return;

  const samples = Array.from({ length: 6 }).map((_, i) => ({
    title: 'Service Job Completed',
    address: `${100 + i} Main St`,
    location: 'Austin, TX',
    summary: 'Completed professional service work.',
    customer: 'Customer',
    time: `${i + 1}d ago`,
    image: demoPhotos[i % demoPhotos.length]
  }));

  samples.forEach(job => state.savedCheckins.push(job));
  persistCheckins();

  // Phone
  renderHomeCheckins();

  // Website
  const websiteContainer = document.getElementById('website-checkins');
  document.getElementById('website-empty')?.remove();

  samples.forEach(job => {
    websiteContainer.insertAdjacentHTML(
      'beforeend',
      createCheckinCard({
        title: job.title,
        location: job.location,
        date: 'Recent',
        image: job.image
      })
    );
  });

  // Metrics
  state.metrics.checkins += samples.length;
  state.metrics.posts += samples.length * 3;

  safeText('metric-checkins', state.metrics.checkins);
  safeText('metric-posts', state.metrics.posts);
}



/* =========================================================
   Home Screen (Phone)
========================================================= */

function renderHomeCheckins() {
  const list = $('home-checkin-list');
  const emptyState = document.querySelector('#home-screen .empty-state');
  if (!list) return;

  list.innerHTML = '';
  
  // Load and display pending jobs
  let pendingJobs = [];
  try {
    const stored = localStorage.getItem('jcpPendingJobs');
    if (stored) {
      pendingJobs = JSON.parse(stored);
    }
  } catch (e) {
    console.warn('Could not load pending jobs');
  }
  
  const isArchived = state.homeActiveTab === 'archived';
  const sourceList = isArchived ? state.archivedCheckins : state.savedCheckins;

  if (isArchived) {
    if (sourceList.length === 0) {
      if (emptyState) {
        emptyState.querySelector('h3').textContent = 'No archived check-ins';
        emptyState.querySelector('p').textContent = 'Archived check-ins will appear here.';
        emptyState.style.display = 'block';
      }
      return;
    }
    if (emptyState) emptyState.style.display = 'none';
    sourceList.forEach((checkin, index) => {
      const item = document.createElement('div');
      item.className = 'home-checkin-item';
      item.innerHTML = `
        <div class="home-checkin-left">
          <h3>${checkin.address || '105 Walnut St'}</h3>
          <div class="home-checkin-location">${checkin.location}</div>
          <div class="home-checkin-desc">${excerptText(checkin.summary || 'Replaced water heater.', 10)}</div>
          <div class="home-checkin-meta">
            <span class="home-checkin-user">${checkin.customer || 'John Doe'}</span>
            <span class="home-checkin-time">${checkin.time || '2h ago'}</span>
          </div>
        </div>
        <div class="home-checkin-thumb"><img src="${checkin.image}" alt="Job photo"></div>
      `;
      item.onclick = () => {
        if (isDemoMode) { showDemoRestrictionTooltip(item, 'Check-in editing is disabled in the demo'); return; }
        openCheckinForEdit(index, true);
      };
      list.appendChild(item);
    });
    return;
  }

  // My Jobs: show pending first, then saved check-ins
  pendingJobs.forEach((job, index) => {
    const item = document.createElement('div');
    item.className = 'home-checkin-item pending-job-item';
    item.innerHTML = `
      <div class="home-checkin-left">
        <div class="pending-pill">Next job ready</div>
        <h3>${job.address}</h3>
        <div class="home-checkin-location">${job.city}</div>
        <div class="home-checkin-desc">${job.scopeSummary.service} · ${job.scopeSummary.scope}</div>
        <div class="home-checkin-meta">
          <span class="home-checkin-user">${job.status}</span>
          <span class="home-checkin-time">Tap to start</span>
        </div>
      </div>
      <div class="home-checkin-thumb"><div class="pending-icon"><i class="fas fa-clipboard-check"></i></div></div>
    `;
    item.onclick = () => startPendingJob(job, index);
    list.appendChild(item);
  });

  if (state.savedCheckins.length === 0 && pendingJobs.length === 0) {
    if (emptyState) {
      emptyState.querySelector('h3').textContent = 'Start capturing proof';
      emptyState.querySelector('p').textContent = 'Take a few photos → submit → automatically published everywhere.';
      emptyState.style.display = 'block';
    }
    return;
  }

  if (emptyState) emptyState.style.display = 'none';

  state.savedCheckins.forEach((checkin, index) => {
    const item = document.createElement('div');
    item.className = 'home-checkin-item';
    item.innerHTML = `
      <div class="home-checkin-left">
        <h3>${checkin.address || '105 Walnut St'}</h3>
        <div class="home-checkin-location">${checkin.location}</div>
        <div class="home-checkin-desc">${excerptText(checkin.summary || 'Replaced water heater.', 10)}</div>
        <div class="home-checkin-meta">
          <span class="home-checkin-user">${checkin.customer || 'John Doe'}</span>
          <span class="home-checkin-time">${checkin.time || '2h ago'}</span>
        </div>
      </div>
      <div class="home-checkin-thumb"><img src="${checkin.image}" alt="Job photo"></div>
    `;
    item.onclick = () => {
      if (isDemoMode) { showDemoRestrictionTooltip(item, 'Check-in editing is disabled in the demo'); return; }
      openCheckinForEdit(index, false);
    };
    list.appendChild(item);
  });
}

function startPendingJob(job, jobIndex) {
  // Preload the job context and go directly to edit screen
  setScreen('edit-screen');
  
  // Populate edit screen with pending job data
  const addressEl = document.querySelector('#edit-screen .location-info h3');
  const cityEl = document.querySelector('#edit-screen .location-info p');
  const descEl = $('description-field');
  
  if (addressEl) addressEl.textContent = job.address;
  if (cityEl) cityEl.textContent = job.city;
  if (descEl) {
    descEl.value = `${job.scopeSummary.service} ${job.scopeSummary.scope}. ${job.scopeSummary.notes || 'Prepared from estimate.'}`;
  }
  
  // Remove from pending jobs after starting
  try {
    let pending = JSON.parse(localStorage.getItem('jcpPendingJobs') || '[]');
    pending.splice(jobIndex, 1);
    localStorage.setItem('jcpPendingJobs', JSON.stringify(pending));
  } catch (e) {
    console.warn('Could not update pending jobs');
  }
}

/* =========================================================
   Map (Leaflet)
========================================================= */

function initializeMap() {
  if (state.map) return;

  // Safety: only init if the element exists
  const mapEl = $('job-map');
  if (!mapEl || typeof L === 'undefined') return;

  state.map = L.map('job-map').setView([30.2672, -97.7431], 11);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(state.map);
}

function addMapMarker(lat, lng, title) {
  if (!state.map || typeof L === 'undefined') return;

  const marker = L.marker([lat, lng])
    .addTo(state.map)
    .bindPopup(`<strong>${title}</strong>`);

  state.mapMarkers.push(marker);

  const group = new L.featureGroup(state.mapMarkers);
  state.map.fitBounds(group.getBounds().pad(0.3));
}

/* =========================================================
   New Check-in Flow (Phone)
========================================================= */
function goToHome() {
  state.activeCheckinIndex = null;
  state.activeCheckinFromArchived = false;
  setScreen('home-screen');
  renderHomeCheckins();

  applyFocalPoint();
  updateTourFloating();
}

function goToNew() {
  setScreen('new-screen');

  state.photoCount = 0;
  const grid = $('photo-grid');
  if (grid) grid.innerHTML = '';

  const submit = $('submit-btn');
  if (submit) {
    submit.disabled = true;
    submit.onclick = null;
  }
  if (tour.stepKey === 'step2') {
    setTourStep('step3');
    applyFocalPoint();
    updateTourFloating();
  }
  syncAttentionAnimations();
}

function openCreateActionSheet() {
  const overlay = $('create-sheet-overlay');
  const sheet = $('create-sheet');
  if (!overlay || !sheet) {
    goToNew();
    return;
  }

  $('fabNewCheckin')?.classList.remove('fab-attention', 'fab-glow');
  document.querySelectorAll('.fab').forEach((el) => el.classList.add('is-sheet-open'));

  syncCreateSheetDemoState();
  overlay.classList.add('is-open');
  sheet.classList.add('is-open');
  overlay.setAttribute('aria-hidden', 'false');
}

function syncCreateSheetDemoState() {
  const reviewBtn = $('create-action-review');
  const demoNote = $('createReviewDemoNote');
  if (!reviewBtn) return;

  const disabled = isGuidedDemoRun() && tour.stepKey === 'step2';
  reviewBtn.classList.toggle('is-demo-disabled', disabled);
  reviewBtn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
  if (demoNote) demoNote.hidden = !disabled;
}

function closeCreateActionSheet() {
  const overlay = $('create-sheet-overlay');
  const sheet = $('create-sheet');
  if (!overlay || !sheet) return;

  overlay.classList.remove('is-open');
  sheet.classList.remove('is-open');
  overlay.setAttribute('aria-hidden', 'true');
  document.querySelectorAll('.fab').forEach((el) => el.classList.remove('is-sheet-open'));
}

function handleCreateAction(action) {
  if (action === 'review' && isGuidedDemoRun() && tour.stepKey === 'step2') {
    showDemoRestrictionTooltip($('create-action-review'), 'Send review request is not available in the demo');
    return;
  }
  closeCreateActionSheet();
  if (action === 'review') {
    goToReviewRequestOptions('home-screen');
    return;
  }
  goToNew();
}

function initCreateActionSheet() {
  const overlay = $('create-sheet-overlay');
  const closeBtn = $('create-sheet-close');
  const cancelBtn = $('create-sheet-cancel');
  const checkinBtn = $('create-action-checkin');
  const reviewBtn = $('create-action-review');
  if (!overlay) return;
  if (overlay.dataset.bound === '1') return;

  overlay.dataset.bound = '1';
  closeBtn?.addEventListener('click', closeCreateActionSheet);
  cancelBtn?.addEventListener('click', closeCreateActionSheet);
  checkinBtn?.addEventListener('click', () => handleCreateAction('checkin'));
  reviewBtn?.addEventListener('click', () => handleCreateAction('review'));

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeCreateActionSheet();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (overlay.classList.contains('is-open')) closeCreateActionSheet();
  });
}

function goToProfile() {
  if (isDemoMode) {
    const el = document.querySelector('[data-action="profile"]');
    if (el) showDemoRestrictionTooltip(el, 'Profile access is disabled in demo mode');
    return;
  }
  setScreen('profile-screen');
  updateProfilePersonalization();
}

function goToEditProfile() {
  setScreen('edit-profile-screen');
  const input = $('edit-profile-name');
  if (input) input.value = demoUser.firstName || '';
}

function goToChangePassword() {
  setScreen('change-password-screen');
  const newInput = $('change-password-new');
  const confirmInput = $('change-password-confirm');
  if (newInput) newInput.value = '';
  if (confirmInput) confirmInput.value = '';
}

function confirmChangePassword() {
  goToProfile();
}

function saveEditProfile() {
  const input = $('edit-profile-name');
  if (input && input.value.trim()) {
    demoUser.firstName = input.value.trim();
    try {
      const stored = localStorage.getItem('demoUser');
      const parsed = stored ? JSON.parse(stored) : {};
      localStorage.setItem('demoUser', JSON.stringify({ ...parsed, firstName: demoUser.firstName }));
    } catch (e) {}
    updateProfilePersonalization();
    const greeting = document.querySelector('.greeting');
    if (greeting) {
      greeting.innerHTML = `Hi, <span class="greeting-accent">${demoUser.firstName}</span> | ${demoUser.businessName}`;
    }
  }
  goToProfile();
}

function addPhotos() {
  if (state.photoCount >= 3) return;

  const grid = $('photo-grid');
  if (!grid) return;

  const idx = state.photoCount;

  const photoDiv = document.createElement('div');
  photoDiv.className = 'photo-item';
  photoDiv.innerHTML = `
    <img src="${demoPhotos[idx]}" alt="Job photo" width="120" height="90">
    <button class="photo-remove" type="button">×</button>
  `;

  // Remove handler (safe)
  photoDiv.querySelector('.photo-remove')?.addEventListener('click', () => {
    photoDiv.remove();
    state.photoCount = Math.max(0, state.photoCount - 1);
    updateSubmitButtonState();
    syncAttentionAnimations();
  });

  grid.appendChild(photoDiv);
  state.photoCount++;

  updateSubmitButtonState();
  syncAttentionAnimations();
}

function updateSubmitButtonState() {
  const btn = $('submit-btn');
  if (!btn) return;

  if (state.photoCount >= 1) {
    btn.disabled = false;
    btn.classList.remove('is-disabled');
    btn.onclick = processPhotos;
  } else {
    btn.disabled = true;
    btn.classList.add('is-disabled');
    btn.onclick = null;
  }
}

async function processPhotos() {
  const overlay = $('processing');
  if (!overlay) {
    // Fallback
    showEditScreen();
    return;
  }

  overlay.classList.add('active');
  if (isPrototype && typeof window.CustomEvent !== 'undefined') {
    window.dispatchEvent(new CustomEvent('jcp-prototype-screen-change', { detail: { screenId: 'checkin-creation-screen' } }));
  }

  await wait(700);
  markProcessingStepDone('step1');

  await wait(700);
  markProcessingStepDone('step2');

  await wait(700);
  markProcessingStepDone('step3');

  await wait(350);
  overlay.classList.remove('active');

  // Reset steps
  setTimeout(() => resetProcessingSteps(['step1','step2','step3']), 400);

  if (isPrototype) {
    const summary = descriptions[0] || 'Replaced water heater.';
    state.savedCheckins.push({
      title: 'Water Heater Replacement',
      address: '105 Walnut St',
      location: 'Austin, TX',
      summary,
      customer: 'John Doe',
      time: 'Just now',
      image: demoPhotos[0]
    });
    persistCheckins();
    state.activeCheckinIndex = state.savedCheckins.length - 1;
    state.comingFromProcessPhotos = true;
  }

  showEditScreen();
}

function markProcessingStepDone(stepId) {
  const step = $(stepId);
  if (!step) return;
  step.classList.add('done');
  const icon = step.querySelector('.step-icon');
  if (icon) icon.innerHTML = `<img src="${assetBase}/shared/assets/icons/lucide/check.svg" class="lucide-icon lucide-icon-sm" alt="">`;
}

function resetProcessingSteps(ids) {
  ids.forEach(id => {
    const step = $(id);
    if (!step) return;
    step.classList.remove('done');
    const icon = step.querySelector('.step-icon');
    if (icon) icon.textContent = '';
  });
}

function showEditScreen() {
  const editGrid = $('edit-photo-grid');
  const descriptionField = $('description-field');
  const addressEl = document.querySelector('#edit-screen .location-info h3');
  const locationEl = document.querySelector('#edit-screen .location-info p');

  if (state.comingFromProcessPhotos && state.activeCheckinIndex !== null) {
    const checkin = state.savedCheckins[state.activeCheckinIndex];
    if (checkin) {
      if (descriptionField) descriptionField.value = checkin.summary || '';
      if (addressEl) addressEl.textContent = checkin.address || '105 Walnut St';
      if (locationEl) locationEl.textContent = checkin.location || 'Austin, TX';
      if (editGrid) {
        editGrid.innerHTML = '';
        const imgSrc = checkin.image || demoPhotos[0];
        const photoDiv = document.createElement('div');
        photoDiv.className = 'photo-item';
        photoDiv.innerHTML = `<img src="${imgSrc}" alt="Job photo" width="120" height="90">`;
        editGrid.appendChild(photoDiv);
      }
    }
    state.comingFromProcessPhotos = false;
  } else {
    state.activeCheckinIndex = null;
    state.activeCheckinFromArchived = false;
    if (editGrid) {
      editGrid.innerHTML = '';
      for (let i = 0; i < state.photoCount; i++) {
        const photoDiv = document.createElement('div');
        photoDiv.className = 'photo-item';
        photoDiv.innerHTML = `<img src="${demoPhotos[i]}" alt="Job photo" width="120" height="90">`;
        editGrid.appendChild(photoDiv);
      }
    }
  }

  updateArchiveButtonUI();
  const publishBtn = $('btnSavePublish');
  if (publishBtn) {
    publishBtn.disabled = false;
    publishBtn.classList.remove('is-disabled');
    if (isPrototype) {
      publishBtn.innerHTML = `<img src="${assetBase}/shared/assets/icons/lucide/check.svg" class="lucide-icon lucide-icon-sm" alt=""> Save`;
    } else {
      publishBtn.innerHTML = `<img src="${assetBase}/shared/assets/icons/lucide/upload.svg" class="lucide-icon lucide-icon-sm" alt=""> Publish Everywhere`;
    }
    publishBtn.onclick = saveCheckin;
  }
  setScreen('edit-screen');
  setTourStep('step4');

  // AUTO-SCROLL TO PUBLISH BUTTON (REAL SCROLLER)
  setTimeout(() => {
    const scroller = document.querySelector('#edit-screen .content-area');
    const publishBtn = document.getElementById('btnSavePublish');

    if (!scroller || !publishBtn) return;

    scroller.scrollTo({
      top: publishBtn.offsetTop - 60,
      behavior: 'smooth'
    });

    // RE-ANCHOR TOUR AFTER SCROLL
    setTimeout(() => {
      updateTourFloating();
    }, 300);
  }, 500);
}


function regenerateDescription() {
  state.currentDescriptionIndex = (state.currentDescriptionIndex + 1) % descriptions.length;

  const field = $('description-field');
  if (!field) return;

  field.value = descriptions[state.currentDescriptionIndex];

  field.classList.add('is-fading');
  setTimeout(() => {
    field.classList.remove('is-fading');
  }, 180);
}

function openRegenerateSheet() {
  const overlay = $('regenerate-sheet-overlay');
  const sheet = $('regenerate-sheet');
  const input = $('regenerate-prompt');
  if (overlay) overlay.classList.add('is-open');
  if (sheet) sheet.classList.add('is-open');
  if (overlay) overlay.setAttribute('aria-hidden', 'false');
  if (input) {
    input.value = '';
    setTimeout(() => input.focus(), 150);
  }
}

function closeRegenerateSheet() {
  const overlay = $('regenerate-sheet-overlay');
  const sheet = $('regenerate-sheet');
  if (overlay) overlay.classList.remove('is-open');
  if (sheet) sheet.classList.remove('is-open');
  if (overlay) overlay.setAttribute('aria-hidden', 'true');
}

function initRegenerateModal() {
  const trigger = $('btnRegenerateDescription');
  const overlay = $('regenerate-sheet-overlay');
  const closeBtn = $('regenerate-sheet-close');
  const cancelBtn = $('regenerate-sheet-cancel');
  const submitBtn = $('regenerate-sheet-submit');
  const input = $('regenerate-prompt');
  if (trigger) trigger.addEventListener('click', (e) => { e.preventDefault(); openRegenerateSheet(); });
  if (closeBtn) closeBtn.addEventListener('click', closeRegenerateSheet);
  if (cancelBtn) cancelBtn.addEventListener('click', closeRegenerateSheet);
  if (submitBtn) submitBtn.addEventListener('click', () => {
    regenerateDescription();
    closeRegenerateSheet();
  });
  if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeRegenerateSheet(); });
  if (input) input.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeRegenerateSheet();
    if (e.key === 'Enter') { e.preventDefault(); regenerateDescription(); closeRegenerateSheet(); }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const o = $('regenerate-sheet-overlay');
      if (o && o.classList.contains('is-open')) closeRegenerateSheet();
    }
  });
}

/* =========================================================
   Save / Publish / Social / Review
========================================================= */

async function saveCheckin() {
const publishBtn = $('btnSavePublish');
if (publishBtn) {
  publishBtn.disabled = true;
  if (isPrototype) {
    publishBtn.innerHTML = `<img src="${assetBase}/shared/assets/icons/lucide/check.svg" class="lucide-icon lucide-icon-sm" alt=""> Saved`;
  } else {
    publishBtn.textContent = 'Published';
  }
  publishBtn.classList.add('is-disabled');
}
  const descField = $('description-field');

  // Edit existing (from My Jobs or after Unarchive we're still in savedCheckins)
  if (state.activeCheckinIndex !== null && !state.activeCheckinFromArchived) {
    const existing = state.savedCheckins[state.activeCheckinIndex];
    if (existing) {
      existing.summary = descField ? descField.value : existing.summary;
      existing.time = 'Just now';
    }
    persistCheckins();
    state.activeCheckinIndex = null;
    state.activeCheckinFromArchived = false;
  } else if (state.activeCheckinIndex !== null && state.activeCheckinFromArchived) {
    const existing = state.archivedCheckins[state.activeCheckinIndex];
    if (existing) {
      existing.summary = descField ? descField.value : existing.summary;
      existing.time = 'Just now';
    }
    persistCheckins();
    state.activeCheckinIndex = null;
    state.activeCheckinFromArchived = false;
  } else {
    // Create new
    const desc = descField ? descField.value : 'Replaced water heater.';
    state.savedCheckins.push({
      title: 'Water Heater Replacement',
      address: '105 Walnut St',
      location: 'Austin, TX',
      summary: desc,
      customer: 'John Doe',
      time: 'Just now',
      image: demoPhotos[0]
    });
    persistCheckins();

    initializeMap();
    addMapMarker(30.2672, -97.7431, 'Water Heater Replacement');
  }

  // Prototype: save only, no publish to website/social
  if (isPrototype) return;

  // Metrics
  state.metrics.checkins++;
  state.metrics.posts += 3;

  safeText('metric-checkins', String(state.metrics.checkins));
  safeText('metric-posts', String(state.metrics.posts));

    // Publish to WEBSITE first (this is the trigger moment)
    const websiteContainer = document.getElementById('website-checkins');
    if (!websiteContainer) return;

    // 1) Replace the LEFT placeholder with the checkin card
const empty = document.getElementById('website-empty');
if (empty) empty.remove();

websiteContainer.insertAdjacentHTML(
  'afterbegin',
  createCheckinCard({
    title: 'Water Heater Replacement',
    location: 'Austin, TX',
    date: new Date().toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    }),
    image: demoPhotos[0]
  }, true)
);

    // MARK AS PUBLISHED
    state.hasPublished = true;

    if (isGuidedDemoRun()) {
      await runGuidedPublishSequence();
      setScreen('edit-screen');
      setTourStep('step5');
      updateTourFloating();
      applyFocalPoint();
      return;
    }

    // EXIT GUIDED MODE PERMANENTLY (legacy non-guided path)
    state.guideDisabled = true;

    // Publish to social
    await publishToSocial();

    // Pulse through channels
    await pulseSequence(['website-checkins', 'sim-google', 'sim-facebook'], 350);

    // FULL VISIBILITY AFTER PUBLISH
    applyFocalPoint();

    // Advance tour to review step
    state.currentScreen = 'edit-screen';
    setTourStep('step5');

    // CRITICAL: force tooltip + layout refresh
    updateTourFloating();
}

async function runGuidedPublishSequence() {
  const overlay = $('demoPublishOverlay');
  const items = overlay ? [...overlay.querySelectorAll('.demo-publish-item')] : [];
  if (!overlay || !items.length) {
    await publishToSocial();
    return;
  }

  overlay.classList.add('active');
  document.body.classList.add('jcp-publish-modal-open');
  items.forEach((item) => item.classList.remove('is-done'));

  for (let i = 0; i < items.length; i++) {
    await wait(580);
    items[i].classList.add('is-done');
    if (i === 1) {
      await publishToSocial();
    }
  }

  await wait(650);
  overlay.classList.remove('active');
  document.body.classList.remove('jcp-publish-modal-open');
  jcpDemoTrack('demo_publish_completed', 4);
}

async function publishToSocial() {
  // Remove empty states
  $('google-empty')?.remove();
  $('facebook-empty')?.remove();

  const jobCard = `
    <div class="feed-card">
      <div class="feed-image"><img src="${demoPhotos[0]}" alt="Job" width="400" height="300" loading="lazy"></div>
      <div class="feed-content">
        <h4>Water Heater Replacement • Austin, TX</h4>
        <p>Posted today • Professional installation</p>
      </div>
    </div>
  `;

  pulse('sim-google');
  $('feed-google')?.insertAdjacentHTML('afterbegin', jobCard);

  await wait(350);

  pulse('sim-facebook');
  $('feed-facebook')?.insertAdjacentHTML('afterbegin', jobCard);
}

function openReviewDialog() {
  $('review-modal')?.classList.add('active');
  document.body.classList.add('jcp-review-modal-open');
}

function closeReviewDialog() {
  $('review-modal')?.classList.remove('active');
  document.body.classList.remove('jcp-review-modal-open');
}

function populateDemoReviewModal() {
  const checkin = getCurrentCheckinForReview();
  const title = checkin?.title || 'Water Heater Replacement';
  const address = checkin?.address || '105 Walnut St';
  const location = checkin?.location || 'Austin, TX';
  const imgSrc = checkin?.image || demoPhotos[0];

  safeText('demoReviewTitle', title);
  safeText('demoReviewLocation', `${address}, ${location}`);
  const photo = $('demoReviewPhoto');
  if (photo) photo.src = imgSrc;

  const message = $('demoReviewMessage');
  if (message) {
    message.value = 'We loved working with you! If you have a moment to leave a review, it would mean a lot to us.';
  }
}

function openDemoReviewModal() {
  populateDemoReviewModal();
  $('review-modal')?.classList.add('active');
  document.body.classList.add('jcp-review-modal-open');
  setMobileGuideCollapsed(true);
}

async function confirmDemoReviewSend() {
  const sendBtn = $('btnDemoReviewSend');
  if (sendBtn) {
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending…';
  }

  await wait(900);

  closeReviewDialog();
  if (tour.stepKey === 'step5') {
    setMobileGuideCollapsed(false);
  }
  if (sendBtn) {
    sendBtn.disabled = false;
    sendBtn.textContent = 'Send';
  }

  await completeGuidedReviewFlow();
}

async function completeGuidedReviewFlow() {
  state.metrics.reviews++;
  safeText('metric-reviews', String(state.metrics.reviews));
  jcpDemoTrack('demo_review_sent', 5);

  setScreen('home-screen');
  renderHomeCheckins();

  setTourStep('step6');
  applyFocalPoint();
  syncMobileGuideChrome();

  await openOutcomesSlideshow();

  lockBackButtons(false);
  document.querySelectorAll('.is-disabled').forEach((el) => {
    el.classList.remove('is-disabled');
    el.disabled = false;
  });
}

function hideDemoOutcomes() {
  hideDemoOutcomesInline();
  closeOutcomesSlideshow();
}

function hideDemoOutcomesInline() {
  const panel = $('demoOutcomes');
  const review = $('demoReviewReceived');
  if (panel) {
    panel.hidden = true;
    panel.classList.remove('is-visible');
  }
  if (review) {
    review.hidden = true;
    review.classList.remove('is-visible');
  }
  const list = $('demoOutcomesList');
  if (list) list.innerHTML = '';
}

function getDemoDirectoryUrl() {
  return `${(baseUrl || '').replace(/\/$/, '')}/directory/`;
}

function getCompanyInitial(name) {
  const trimmed = String(name || '').trim();
  return trimmed ? trimmed.charAt(0).toUpperCase() : '?';
}

function getAvatarColor(initial) {
  const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'];
  const code = String(initial || 'A').charCodeAt(0);
  return colors[code % colors.length];
}

function getOutcomesJobContext() {
  const checkin = getCurrentCheckinForReview();
  const businessName = demoUser.businessName || 'Your Business';
  const image = checkin?.image || demoPhotos[0] || '';
  const address = checkin?.address || '105 Walnut St';
  const location = checkin?.location || 'Austin, TX';
  const title = checkin?.title || 'Water Heater Replacement';
  const summary = checkin?.summary || excerptText(descriptions[0], 22);
  const nicheLabel = demoUser.niche
    ? demoUser.niche.replace(/-/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase())
    : 'Plumbing';
  const slug = businessName.toLowerCase().replace(/[^a-z0-9]+/g, '') || 'yourbusiness';
  const initial = getCompanyInitial(businessName);
  return {
    businessName,
    image,
    address,
    location,
    title,
    summary,
    nicheLabel,
    slug,
    firstName: demoUser.firstName || 'John',
    initial,
    avatarColor: getAvatarColor(initial),
    jobsCount: state.metrics?.checkins ?? 12,
    reviewsCount: state.metrics?.reviews ?? 48,
    rating: '5.0',
    directoryUrl: getDemoDirectoryUrl(),
  };
}

function buildOutcomesSlideHtml(index, ctx) {
  const e = escapeHtml;
  const img = e(ctx.image);
  const title = e(ctx.title);
  const business = e(ctx.businessName);
  const address = e(ctx.address);
  const location = e(ctx.location);
  const summary = e(ctx.summary);
  const niche = e(ctx.nicheLabel);
  const slug = e(ctx.slug);
  const first = e(ctx.firstName);
  const initial = e(ctx.initial);
  const avatarColor = e(ctx.avatarColor);
  const jobsCount = Number(ctx.jobsCount) || 12;
  const reviewsCount = Number(ctx.reviewsCount) || 48;
  const rating = e(ctx.rating);
  const directoryUrl = e(ctx.directoryUrl);

  switch (index) {
    case 0:
      return `
        <article class="demo-outcomes-slide" data-slide="0">
          <div class="outcomes-preview outcomes-preview--website">
            <div class="outcomes-browser">
              <div class="outcomes-browser__bar">
                <span></span><span></span><span></span>
                <div class="outcomes-browser__url">${slug}.com/jobs</div>
              </div>
              <div class="outcomes-browser__body">
                <p class="outcomes-browser__heading">Recent work from ${business}</p>
                <div class="outcomes-job-card">
                  <img src="${img}" alt="" width="120" height="90" loading="lazy">
                  <div>
                    <strong>${title}</strong>
                    <span>${address}, ${location}</span>
                    <p>${summary}</p>
                    <em>Published just now</em>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </article>`;
    case 1:
      return `
        <article class="demo-outcomes-slide" data-slide="1">
          <div class="outcomes-preview outcomes-preview--social">
            <div class="outcomes-social-card">
              <div class="outcomes-social-card__head">
                <span class="outcomes-social-card__avatar">${business.charAt(0)}</span>
                <div>
                  <strong>${business}</strong>
                  <span>Just now · ${location}</span>
                </div>
              </div>
              <p class="outcomes-social-card__copy">${summary}</p>
              <img class="outcomes-social-card__photo" src="${img}" alt="" width="400" height="220" loading="lazy">
              <div class="outcomes-social-card__reactions">
                <span>👍 Like</span><span>💬 Comment</span><span>↗ Share</span>
              </div>
            </div>
          </div>
        </article>`;
    case 2:
      return `
        <article class="demo-outcomes-slide" data-slide="2">
          <div class="outcomes-preview outcomes-preview--google">
            <div class="outcomes-gbp-card">
              <div class="outcomes-gbp-card__brand">
                <img src="${assetBase}/shared/assets/icons/lucide/map-pin.svg" class="lucide-icon lucide-icon-sm" alt="">
                <strong>${business}</strong>
                <span>Google Business Profile</span>
              </div>
              <img class="outcomes-gbp-card__photo" src="${img}" alt="" width="400" height="200" loading="lazy">
              <h4>${title} completed in ${location}</h4>
              <p>${summary}</p>
              <span class="outcomes-gbp-card__meta">Posted automatically · Verified job</span>
            </div>
          </div>
        </article>`;
    case 3:
      return `
        <article class="demo-outcomes-slide" data-slide="3">
          <div class="outcomes-preview outcomes-preview--directory">
            <div class="directory-card is-demo outcomes-directory-card" role="article">
              <span class="demo-flag">Demo Listing</span>
              <span class="directory-badge verified">Verified</span>
              <div class="card-header">
                <div class="company-mark">
                  <div class="company-avatar" style="background:${avatarColor}">${initial}</div>
                </div>
                <div class="card-header-content">
                  <h3 class="card-name">${business}</h3>
                </div>
              </div>
              <div class="card-location">
                <span class="card-location-segment">
                  <img src="${assetBase}/shared/assets/icons/lucide/map-pin.svg" class="lucide-icon lucide-icon-xs" alt="">
                  <span>${location}</span>
                </span>
                <span class="card-location-segment">
                  <img src="${assetBase}/shared/assets/icons/lucide/briefcase.svg" class="lucide-icon lucide-icon-xs" alt="">
                  <span>${niche}</span>
                </span>
              </div>
              <div class="card-meta-row">
                <span class="meta-inline">
                  <img src="${assetBase}/shared/assets/icons/lucide/camera.svg" class="lucide-icon lucide-icon-xs" alt="">
                  ${jobsCount} jobs
                </span>
                <span class="meta-divider">·</span>
                <span class="meta-inline">
                  <img src="${assetBase}/shared/assets/icons/lucide/clock.svg" class="lucide-icon lucide-icon-xs" alt="">
                  Active just now
                </span>
              </div>
              <div class="card-rating">
                <div class="stars">★★★★★</div>
                <span class="rating-text">${rating} (${reviewsCount})</span>
              </div>
              <div class="card-footer">
                <span class="view-profile">View activity</span>
              </div>
            </div>
            <a href="${directoryUrl}" target="_blank" rel="noopener noreferrer" class="outcomes-directory-view-link">View the directory</a>
          </div>
        </article>`;
    default:
      return `
        <article class="demo-outcomes-slide" data-slide="4">
          <div class="outcomes-preview outcomes-preview--review">
            <div class="outcomes-review-card">
              <div class="outcomes-review-card__stars" aria-hidden="true">★★★★★</div>
              <div class="outcomes-review-card__body">
                <img class="outcomes-review-card__thumb" src="${img}" alt="" width="56" height="56" loading="lazy">
                <div class="outcomes-review-card__copy">
                  <strong>New 5-star review</strong>
                  <p>"Great service, fast and very professional!"</p>
                  <small>Arrived right after your review request</small>
                </div>
              </div>
              <div class="outcomes-review-card__sent">
                <img src="${assetBase}/shared/assets/icons/lucide/send.svg" class="lucide-icon lucide-icon-sm" alt="">
                Review request sent automatically after the job
              </div>
            </div>
          </div>
        </article>`;
  }
}

function renderOutcomesSlides() {
  const track = $('demoOutcomesTrack');
  const dots = $('demoOutcomesDots');
  if (!track || !dots) return;

  const ctx = getOutcomesJobContext();
  track.innerHTML = Array.from({ length: outcomesSlideshow.total }, (_, i) => buildOutcomesSlideHtml(i, ctx)).join('');
  dots.innerHTML = Array.from({ length: outcomesSlideshow.total }, (_, i) => (
    `<button type="button" class="demo-outcomes-dot${i === 0 ? ' is-active' : ''}" data-slide="${i}" aria-label="Slide ${i + 1}" role="tab"></button>`
  )).join('');
}

function updateOutcomesSlideshowUi() {
  const { index, total } = outcomesSlideshow;
  const track = $('demoOutcomesTrack');
  if (track) track.style.transform = `translateX(-${index * 100}%)`;

  document.querySelectorAll('.demo-outcomes-slide').forEach((slide, i) => {
    slide.classList.toggle('is-active', i === index);
  });
  document.querySelectorAll('.demo-outcomes-dot').forEach((dot, i) => {
    dot.classList.toggle('is-active', i === index);
    dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
  });

  safeText('demoOutcomesSlideCounter', `${index + 1} of ${total}`);
  safeText('demoOutcomesSlideLabel', OUTCOMES_SLIDE_LABELS[index] || '');

  const isLast = index >= total - 1;
  const nextBtn = $('demoOutcomesNextCta');
  const finishBtn = $('demoOutcomesFinishCta');
  if (nextBtn) {
    nextBtn.hidden = isLast;
    nextBtn.disabled = isLast;
  }
  if (finishBtn) {
    finishBtn.classList.toggle('demo-outcomes-modal__finish--solo', isLast);
  }
}

function handleOutcomesModalAction(action) {
  if (action === 'next') {
    if (outcomesSlideshow.index < outcomesSlideshow.total - 1) {
      setOutcomesSlide(outcomesSlideshow.index + 1);
    }
    return;
  }
  if (action === 'finish') {
    completeDemoConversion();
  }
}

function onOutcomesModalClick(e) {
  if (e.target.closest('#demoOutcomesModalClose')) {
    e.preventDefault();
    closeOutcomesSlideshow();
    return;
  }
  if (e.target.closest('#demoOutcomesModalBackdrop')) {
    closeOutcomesSlideshow();
    return;
  }

  const actionBtn = e.target.closest('[data-outcomes-action]');
  if (actionBtn) {
    e.preventDefault();
    e.stopPropagation();
    handleOutcomesModalAction(actionBtn.dataset.outcomesAction);
    return;
  }

  // Legacy markup (cached demo/index.html)
  if (e.target.closest('#demoOutcomesNext, #demoOutcomesPrev')) {
    e.preventDefault();
    e.stopPropagation();
    if (e.target.closest('#demoOutcomesPrev')) return;
    handleOutcomesModalAction('next');
    return;
  }
  if (e.target.closest('#demoOutcomesFinish, #demoOutcomesStartCta, #demoOutcomesPrimaryCta')) {
    e.preventDefault();
    e.stopPropagation();
    handleOutcomesModalAction('finish');
  }
}

function setOutcomesSlide(index, { trackAnalytics = true } = {}) {
  const clamped = Math.min(Math.max(0, index), outcomesSlideshow.total - 1);
  outcomesSlideshow.index = clamped;
  updateOutcomesSlideshowUi();
  if (trackAnalytics && outcomesSlideshow.isOpen) {
    jcpDemoTrack('demo_outcomes_slide', clamped + 1);
  }
}

function openOutcomesSlideshow() {
  const modal = $('demoOutcomesModal');
  if (!modal) return;

  hideDemoOutcomesInline();
  outcomesSlideshow.isOpen = true;
  outcomesSlideshow.index = 0;

  renderOutcomesSlides();
  wireOutcomesSlideshow();
  setOutcomesSlide(0, { trackAnalytics: false });

  modal.hidden = false;
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('jcp-outcomes-modal-open');
  hideOutcomesCtaDock();
  setMobileGuideCollapsed(true);
  updateMobileLayoutMetrics();
  hideMobileSpotlight();
  updateGuidedCoachBackdrop();
  jcpDemoTrack('demo_outcomes_opened', 6);

  requestAnimationFrame(() => modal.classList.add('is-visible'));
  syncMobileGuideChrome();
}

function showOutcomesCtaDock() {
  if (tour.stepKey !== 'step6' || !isGuidedDemoRun()) return;
  document.body.classList.add('jcp-outcomes-cta-dock');
  setMobileGuideCollapsed(false);
  updateMobileLayoutMetrics();
  syncMobileGuideChrome();
}

function hideOutcomesCtaDock() {
  document.body.classList.remove('jcp-outcomes-cta-dock');
}

function completeDemoConversion() {
  jcpDemoTrack('demo_outcomes_completed', 6);
  if (outcomesSlideshow.isOpen) {
    closeOutcomesSlideshow({ keepDock: false });
  }
  hideOutcomesCtaDock();
  state.isFinalStep = true;
  showPostDemoPanel();
}

function closeOutcomesSlideshow({ keepDock = true } = {}) {
  const modal = $('demoOutcomesModal');
  if (!modal || modal.hidden) return;

  outcomesSlideshow.isOpen = false;
  modal.classList.remove('is-visible');
  modal.hidden = true;
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('jcp-outcomes-modal-open');
  if (keepDock && tour.stepKey === 'step6' && isGuidedDemoRun()) {
    showOutcomesCtaDock();
  } else {
    syncMobileGuideChrome();
  }
}

function finishOutcomesSlideshow() {
  completeDemoConversion();
}

function ensureOutcomesFooterButtons() {
  const modal = $('demoOutcomesModal');
  if (!modal) return;

  const card = modal.querySelector('.demo-outcomes-modal__card');
  if (!card) return;

  let footer = card.querySelector('.demo-outcomes-modal__footer');
  if (!footer) {
    footer = document.createElement('div');
    footer.className = 'demo-outcomes-modal__footer';
    card.appendChild(footer);
  }

  if (!$('demoOutcomesNextCta')) {
    const next = document.createElement('button');
    next.type = 'button';
    next.id = 'demoOutcomesNextCta';
    next.className = 'btn btn-secondary demo-outcomes-modal__next';
    next.dataset.outcomesAction = 'next';
    next.textContent = 'Next';
    footer.appendChild(next);
  }

  if (!$('demoOutcomesFinishCta')) {
    const finish = document.createElement('button');
    finish.type = 'button';
    finish.id = 'demoOutcomesFinishCta';
    finish.className = 'btn btn-primary demo-outcomes-modal__finish';
    finish.dataset.outcomesAction = 'finish';
    finish.textContent = 'Get Started Free';
    footer.appendChild(finish);
  }
}

function wireOutcomesSlideshow() {
  const modal = $('demoOutcomesModal');
  if (!modal) return;

  ensureOutcomesFooterButtons();

  modal.querySelectorAll('.demo-outcomes-modal__actions').forEach((el) => el.remove());
  modal.querySelectorAll('#demoOutcomesStartCta, #demoOutcomesPrimaryCta, #demoOutcomesPrev, #demoOutcomesNext, #demoOutcomesFinish').forEach((el) => {
    el.remove();
  });

  if (modal.dataset.bound !== '1') {
    modal.dataset.bound = '1';
    modal.addEventListener('click', onOutcomesModalClick);

    $('demoOutcomesDots')?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-slide]');
      if (!btn) return;
      setOutcomesSlide(parseInt(btn.dataset.slide, 10));
    });

    $('demoOutcomesTrack')?.addEventListener('click', (e) => {
      const link = e.target.closest('.outcomes-directory-view-link');
      if (!link) return;
      jcpDemoTrack('cta_clicked', null, { cta: 'view_directory', source: 'demo_outcomes_modal' });
    });

    const viewport = $('demoOutcomesViewport');
    if (viewport) {
      viewport.addEventListener('touchstart', (e) => {
        outcomesSlideshow.touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });
      viewport.addEventListener('touchend', (e) => {
        const delta = e.changedTouches[0].screenX - outcomesSlideshow.touchStartX;
        if (Math.abs(delta) < 40) return;
        if (delta < 0) setOutcomesSlide(outcomesSlideshow.index + 1);
        else setOutcomesSlide(outcomesSlideshow.index - 1);
      }, { passive: true });
    }

    document.addEventListener('keydown', (e) => {
      if (!outcomesSlideshow.isOpen) return;
      if (e.key === 'Escape') closeOutcomesSlideshow();
      if (e.key === 'ArrowRight') setOutcomesSlide(outcomesSlideshow.index + 1);
      if (e.key === 'ArrowLeft') setOutcomesSlide(outcomesSlideshow.index - 1);
    });
  }
}

function getDemoAppScrollParents() {
  const screen = document.querySelector('.iphone-frame .screen');
  const activeApp = document.querySelector('.app-screen.active');
  const contentArea = activeApp?.querySelector('.content-area');
  const parents = [];
  if (contentArea) parents.push(contentArea);
  if (screen) parents.push(screen);
  return parents;
}

function getMobileStepperTop() {
  const stepper = $('mobile-stepper');
  if (!stepper || stepper.classList.contains('is-collapsed')) {
    return window.innerHeight;
  }
  const style = window.getComputedStyle(stepper);
  if (style.display === 'none' || style.visibility === 'hidden') {
    return window.innerHeight;
  }
  return stepper.getBoundingClientRect().top;
}

function scrollGuidedControlIntoView(target, extraGap = 20) {
  if (!target || !isGuidedDemoRun() || !document.body.classList.contains('is-mobile-mode')) return;

  const stepperTop = getMobileStepperTop();
  const targetRect = target.getBoundingClientRect();
  const desiredBottom = stepperTop - extraGap;

  getDemoAppScrollParents().forEach((scrollParent) => {
    const parentRect = scrollParent.getBoundingClientRect();
    const visibleBottom = Math.min(parentRect.bottom, desiredBottom) - extraGap;

    if (targetRect.bottom > visibleBottom) {
      scrollParent.scrollBy({
        top: targetRect.bottom - visibleBottom,
        behavior: 'smooth',
      });
    } else if (targetRect.top < parentRect.top + 12) {
      scrollParent.scrollBy({
        top: targetRect.top - parentRect.top - 12,
        behavior: 'smooth',
      });
    }
  });
}

function scrollGuidedStepTarget(stepKey) {
  if (!isGuidedDemoRun() || !document.body.classList.contains('is-mobile-mode')) return;
  if (!stepKey || stepKey === 'step6') return;

  const selector = tour.anchors[stepKey];
  const target = selector ? document.querySelector(selector) : null;
  if (!target) return;

  const run = () => {
    scrollGuidedControlIntoView(target);
    positionMobileSpotlight();
  };

  updateMobileLayoutMetrics();
  requestAnimationFrame(run);
  setTimeout(run, 400);
  setTimeout(run, 950);
}

function scrollDemoTargetIntoView(target, extraGap = 16) {
  if (!target) return;
  const stepperTop = getMobileStepperTop();

  getDemoAppScrollParents().forEach((scrollParent) => {
    const parentRect = scrollParent.getBoundingClientRect();
    const targetRect = target.getBoundingClientRect();
    const visibleBottom = Math.min(parentRect.bottom, stepperTop) - extraGap;

    if (targetRect.bottom > visibleBottom) {
      scrollParent.scrollTo({
        top: scrollParent.scrollTop + (targetRect.bottom - visibleBottom),
        behavior: 'smooth',
      });
      return;
    }

    if (targetRect.top < parentRect.top + 12) {
      scrollParent.scrollTo({
        top: Math.max(0, scrollParent.scrollTop + (targetRect.top - parentRect.top) - 12),
        behavior: 'smooth',
      });
    }
  });
}

function buildDirectoryPreview() {
  const businessName = demoUser.businessName || 'Your Business';
  const nicheLabel = demoUser.niche
    ? demoUser.niche
        .replace(/-/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
    : 'Service';
  const ownerLabel = demoUser.firstName ? `Owner: ${demoUser.firstName}` : 'Owner: Local Operator';

  return `
    <li><img src="${assetBase}/shared/assets/icons/lucide/badge-check.svg" class="lucide-icon lucide-icon-sm" alt=""> Verified via Live Jobs</li>
    <li><img src="${assetBase}/shared/assets/icons/lucide/building.svg" class="lucide-icon lucide-icon-sm" alt=""> ${businessName}</li>
    <li><img src="${assetBase}/shared/assets/icons/lucide/briefcase.svg" class="lucide-icon lucide-icon-sm" alt=""> ${nicheLabel}</li>
    <li><img src="${assetBase}/shared/assets/icons/lucide/user.svg" class="lucide-icon lucide-icon-sm" alt=""> ${ownerLabel}</li>
    <li><img src="${assetBase}/shared/assets/icons/lucide/activity.svg" class="lucide-icon lucide-icon-sm" alt=""> Status: Active</li>
    <li><img src="${assetBase}/shared/assets/icons/lucide/clock.svg" class="lucide-icon lucide-icon-sm" alt=""> Last verified job: Just now</li>
    <li><img src="${assetBase}/shared/assets/icons/lucide/trending-up.svg" class="lucide-icon lucide-icon-sm" alt=""> Ranking improves with continued job activity</li>
  `;
}

function unlockDirectoryButton() {
  const btn = document.getElementById('btnViewDirectory');
  if (!btn) return;

  btn.classList.remove('is-hidden');
  btn.classList.add('dir-unlock');

  // Remove animation class after it runs once
  setTimeout(() => {
    btn.classList.remove('dir-unlock');
  }, 900);
}

async function sendReviewRequest() {
  if (isGuidedDemoRun()) {
    openDemoReviewModal();
    return;
  }

  // Legacy path (non-guided)
  document.getElementById('review-empty')?.remove();

  // Metrics
  state.metrics.reviews++;
  safeText('metric-reviews', String(state.metrics.reviews));

  pulse('sim-review');

  document.getElementById('feed-review')?.insertAdjacentHTML('afterbegin', `
    <div class="feed-card">
      <div class="feed-image"><img src="${demoPhotos[0]}" alt="Job" width="400" height="300" loading="lazy"></div>
      <div class="feed-content">
        <h4>Review Request Sent</h4>
        <p>SMS sent automatically</p>
      </div>
    </div>
  `);

  await wait(1100);

  pulse('sim-google');
  document.getElementById('feed-google')?.insertAdjacentHTML('afterbegin', `
    <div class="feed-card">
      <div class="feed-image"><img src="${demoPhotos[0]}" alt="Job" width="400" height="300" loading="lazy"></div>
      <div class="feed-content">
        <h4>⭐ 5-Star Review Received</h4>
        <p>"Great service!"</p>
      </div>
    </div>
  `);

  // Unlock all simulator interactions
  lockBackButtons(false);
  document.querySelectorAll('.is-disabled').forEach(el => {
    el.classList.remove('is-disabled');
    el.disabled = false;
  });

  // Unlock directory CTA with animation
  unlockDirectoryButton();

  // Re-anchor tour
  updateTourFloating();

  // Return to home state
  setScreen('home-screen');
  // Advance to Directory step
  setTourStep('step6');

  renderHomeCheckins();

  // Update top CTA
  const headerCta = document.getElementById('btnNext');
  if (headerCta) {
    headerCta.textContent = 'Get Started →';
    headerCta.onclick = () => {
      window.location.href = jcpBuildOnboardingUrl(jcpDemoOnboardingHandoffQuery('demo_header_complete'));
    };
  }
}


/* =========================================================
   Edit Existing Check-in
========================================================= */

function openCheckinForEdit(index, fromArchived = false) {
  const list = fromArchived ? state.archivedCheckins : state.savedCheckins;
  const checkin = list[index];
  if (!checkin) return;

  state.activeCheckinIndex = index;
  state.activeCheckinFromArchived = fromArchived;

  const descriptionField = $('description-field');
  if (descriptionField) {
    descriptionField.value = checkin.summary || 'Replaced water heater.';
  }

  const addressEl = document.querySelector('#edit-screen .location-info h3');
  const locationEl = document.querySelector('#edit-screen .location-info p');
  if (addressEl) addressEl.textContent = checkin.address || '105 Walnut St';
  if (locationEl) locationEl.textContent = checkin.location || 'Austin, TX';

  const editGrid = $('edit-photo-grid');
  if (editGrid) {
    editGrid.innerHTML = '';
    if (checkin.image) {
      const photoDiv = document.createElement('div');
      photoDiv.className = 'photo-item';
      photoDiv.innerHTML = `<img src="${checkin.image}" alt="Job photo" width="400" height="300" loading="lazy">`;
      editGrid.appendChild(photoDiv);
    }
  }

  const status = document.querySelector('.status-pill');
  if (status) status.innerHTML = `<img src="${assetBase}/shared/assets/icons/lucide/badge-check.svg" class="lucide-icon lucide-icon-sm" alt=""> Published`;

  const publishBtn = $('btnSavePublish');
  if (publishBtn) {
    publishBtn.disabled = true;
    publishBtn.classList.add('is-disabled');
    publishBtn.textContent = 'Published';
    publishBtn.onclick = null;
  }

  updateArchiveButtonUI();
  setScreen('edit-screen');
}

function updateArchiveButtonUI() {
  const btn = $('btnArchiveCheckin');
  const label = $('btnArchiveCheckinLabel');
  if (!btn || !label) return;
  const isNew = state.activeCheckinIndex === null;
  if (state.activeCheckinFromArchived) {
    label.textContent = 'Unarchive';
    btn.title = 'Move back to My Jobs';
    btn.disabled = false;
    btn.classList.remove('is-disabled');
  } else if (isNew) {
    label.textContent = 'Archive Check-In';
    btn.title = 'Publish first to archive';
    btn.disabled = true;
    btn.classList.add('is-disabled');
  } else {
    label.textContent = 'Archive Check-In';
    btn.title = '';
    btn.disabled = false;
    btn.classList.remove('is-disabled');
  }
  if (isDemoMode) {
    btn.classList.add('is-demo-disabled');
  } else {
    btn.classList.remove('is-demo-disabled');
  }

  const normalBtns = $('edit-screen-buttons-normal');
  const archivedBtns = $('edit-screen-buttons-archived');
  if (normalBtns && archivedBtns) {
    if (state.activeCheckinFromArchived) {
      normalBtns.style.display = 'none';
      archivedBtns.style.display = 'flex';
    } else {
      normalBtns.style.display = 'flex';
      archivedBtns.style.display = 'none';
    }
  }
}

function saveArchivedCheckinEdits() {
  if (state.activeCheckinIndex === null || !state.activeCheckinFromArchived) return;
  const checkin = state.archivedCheckins[state.activeCheckinIndex];
  if (!checkin) return;
  const descField = $('description-field');
  if (descField) checkin.summary = descField.value;
  persistCheckins();
}

function deleteArchivedCheckin() {
  if (isDemoMode) {
    showDemoRestrictionTooltip($('btnArchivedDelete'), 'Deleting is disabled in the demo');
    return;
  }
  if (state.activeCheckinIndex === null || !state.activeCheckinFromArchived) return;
  state.archivedCheckins.splice(state.activeCheckinIndex, 1);
  persistCheckins();
  state.activeCheckinIndex = null;
  state.activeCheckinFromArchived = false;
  goToHome();
}

function goToRequestReview() {
  if (isGuidedDemoRun() && tour.stepKey === 'step5') {
    openDemoReviewModal();
    return;
  }
  const checkin = getCurrentCheckinForReview();
  const subtitle = $('request-review-subtitle');
  const addressEl = $('request-review-address');
  const locationEl = $('request-review-location');
  const messageEl = $('request-review-message');
  const photosEl = $('request-review-photos');
  if (subtitle) subtitle.textContent = checkin ? `${checkin.address || '105 Walnut St'}, ${checkin.location || 'Austin, TX'}` : '105 Walnut St, Austin, TX';
  if (addressEl) addressEl.textContent = checkin?.address || '105 Walnut St';
  if (locationEl) locationEl.textContent = checkin?.location || 'Austin, TX';
  if (messageEl) messageEl.value = 'We loved working with you! If you have a moment to leave a review, it would mean a lot to us.';
  if (photosEl) {
    const imgSrc = checkin?.image || demoPhotos[0];
    photosEl.innerHTML = `<img src="${imgSrc}" alt="Job" class="request-review-photo-thumb">`;
  }
  setScreen('request-review-screen');
}

function goToReviewRequestOptions(fromScreen = 'home-screen') {
  state.reviewMethodBackScreen = fromScreen;
  setScreen('review-request-options-screen');
}

function goBackFromReviewMethod() {
  const backTo = state.reviewMethodBackScreen || 'home-screen';
  if (backTo === 'edit-screen') {
    goBackToEdit();
    return;
  }
  goToHome();
}

function getCurrentCheckinForReview() {
  if (state.activeCheckinIndex !== null && state.activeCheckinFromArchived) return state.archivedCheckins[state.activeCheckinIndex];
  if (state.activeCheckinIndex !== null) return state.savedCheckins[state.activeCheckinIndex];
  if (state.savedCheckins.length > 0) return state.savedCheckins[state.savedCheckins.length - 1];
  return null;
}

function goBackToEdit() {
  setScreen('edit-screen');
}

function submitReviewRequestFromScreen() {
  state.metrics.reviews++;
  safeText('metric-reviews', String(state.metrics.reviews));
  goToHome();
}

function archiveCheckin() {
  if (isDemoMode) {
    showDemoRestrictionTooltip($('btnArchiveCheckin'), 'Archiving is disabled in the demo');
    return;
  }
  if (state.activeCheckinFromArchived) {
    const checkin = state.archivedCheckins[state.activeCheckinIndex];
    if (checkin) {
      state.archivedCheckins.splice(state.activeCheckinIndex, 1);
      state.savedCheckins.push(checkin);
      persistCheckins();
    }
  } else {
    const checkin = state.savedCheckins[state.activeCheckinIndex];
    if (checkin) {
      state.savedCheckins.splice(state.activeCheckinIndex, 1);
      state.archivedCheckins.push(checkin);
      persistCheckins();
    }
  }
  state.activeCheckinIndex = null;
  state.activeCheckinFromArchived = false;
  goToHome();
}

function setHomeTab(tab) {
  if (isDemoMode && tab === 'archived') {
    const tabEl = document.getElementById('tab-archived');
    if (tabEl) showDemoRestrictionTooltip(tabEl, 'Archived list is disabled in the demo');
    return;
  }
  state.homeActiveTab = tab;
  document.querySelectorAll('.action-tiles [data-tab]').forEach((el) => {
    const isActive = el.getAttribute('data-tab') === tab;
    el.classList.toggle('btn-primary', isActive);
    el.classList.toggle('btn-secondary', !isActive);
  });
  renderHomeCheckins();
}

/* =========================================================
   Unified Advance (Desktop + Mobile)
========================================================= */

function advanceDemo() {
  switch (tour.stepKey) {
    case 'step1':
      goToHome();
      setTourStep('step2');
      break;

    case 'step2':
      goToNew();
      setTourStep('step3');
      break;

    case 'step3':
      if (state.photoCount === 0) addPhotos();
      processPhotos();
      break;

    case 'step4':
      saveCheckin();
      break;

    case 'step5':
      openDemoReviewModal();
      break;

    case 'step6':
      if (!outcomesSlideshow.isOpen) {
        openOutcomesSlideshow();
      } else {
        finishOutcomesSlideshow();
      }
      break;

    default:
      return;
  }
}

function resetGuidedDemoFeeds() {
  initializeWebsite();
  const feedResets = [
    {
      id: 'feed-google',
      html: `<div class="empty-state" id="google-empty"><h3>No posts yet</h3><p>Posts appear after a job is published.</p></div>`,
    },
    {
      id: 'feed-facebook',
      html: `<div class="empty-state" id="facebook-empty"><h3>No updates yet</h3><p>Updates appear after a job is published.</p></div>`,
    },
    {
      id: 'feed-review',
      html: `<div class="empty-state" id="review-empty"><h3>No reviews yet</h3><p>Reviews appear after a request is sent.</p></div>`,
    },
  ];
  feedResets.forEach(({ id, html }) => {
    const el = $(id);
    if (el) el.innerHTML = html;
  });
}

function resetGuidedEditScreen() {
  const publishBtn = $('btnSavePublish');
  if (publishBtn) {
    publishBtn.disabled = false;
    publishBtn.classList.remove('is-disabled');
    publishBtn.innerHTML = `<img src="${assetBase}/shared/assets/icons/lucide/upload.svg" class="lucide-icon lucide-icon-sm" alt=""> Publish Everywhere`;
    publishBtn.onclick = saveCheckin;
  }
  state.photoCount = 0;
  state.activeCheckinIndex = null;
  state.activeCheckinFromArchived = false;
  state.comingFromProcessPhotos = false;
  const editGrid = $('edit-photo-grid');
  if (editGrid) editGrid.innerHTML = '';
  const desc = $('description-field');
  if (desc) desc.value = descriptions[0];
}

function restartGuidedDemo() {
  jcpDemoTrack('demo_replayed', null, { source: 'post_demo_panel' });

  state.isFinalStep = false;
  hidePostDemoPanel();
  document.getElementById('post-demo-bubble')?.classList.add('is-hidden');
  hideDemoOutcomes();
  hideOutcomesCtaDock();
  closeReviewDialog();
  $('demoPublishOverlay')?.classList.remove('active');
  document.body.classList.remove('jcp-publish-modal-open');

  state.hasPublished = false;
  state.guideDisabled = false;
  state.savedCheckins = [];
  state.metrics = { checkins: 12, posts: 36, reviews: 48 };
  safeText('metric-checkins', '12');
  safeText('metric-posts', '36');
  safeText('metric-reviews', '48');
  tour.stepKey = 'step1';
  tour.isHidden = false;
  tour.isMinimized = false;
  mobileGuideCollapsed = false;

  resetGuidedDemoFeeds();
  resetGuidedEditScreen();

  document.querySelectorAll('.app-screen').forEach((s) => s.classList.remove('active'));
  $('login-screen')?.classList.add('active');
  state.currentScreen = 'login-screen';

  $('btnStartDemo')?.classList.add('wiggle-attention');
  setTourStep('step1');
  showTour();
  applyFocalPoint();
  syncMobileGuideChrome();
  renderHomeCheckins();
}

function restartTour() {
  if (isGuidedDemoRun()) {
    restartGuidedDemo();
    return;
  }
  window.location.reload();
}


/* =========================================================
   Controls
========================================================= */

function wireControls() {
  $('btnReset')?.addEventListener('click', () => location.reload());
  $('mobileBtnReset')?.addEventListener('click', () => location.reload());

  $('btnRestartLead')?.addEventListener('click', () => location.reload());

  $('btnNext')?.addEventListener('click', () => advanceDemo());
  $('mobileBtnNext')?.addEventListener('click', () => advanceDemo());

  $('btnViewDirectory')?.addEventListener('click', openDirectoryProfileFromDemo);

  $('btnExit')?.addEventListener('click', () => {
    setScreen('login-screen');
    state.activeCheckinIndex = null;
  });
  $('mobileBtnExit')?.addEventListener('click', () => {
    setScreen('login-screen');
    state.activeCheckinIndex = null;
  });

  $('btnMobileNext')?.addEventListener('click', () => {
    if (tour.stepKey === 'step6' && !outcomesSlideshow.isOpen) {
      completeDemoConversion();
      return;
    }
    advanceDemo();
  });

  $('demoOutcomesReopenCta')?.addEventListener('click', () => {
    if (tour.stepKey === 'step6' && !outcomesSlideshow.isOpen) {
      openOutcomesSlideshow();
    }
  });

  $('mobileDemoClose')?.addEventListener('click', () => {
    const returnUrl = sessionStorage.getItem('jcp_survey_return_url') || '/demo/';
    window.location.href = returnUrl;
  });

  $('mobileGuideMinimize')?.addEventListener('click', () => toggleMobileGuideCollapse());
  $('mobileGuidePill')?.addEventListener('click', () => setMobileGuideCollapsed(false));
  $('guidedCoachBackdrop')?.addEventListener('click', () => setMobileGuideCollapsed(true));
  $('btnDemoReviewSend')?.addEventListener('click', () => confirmDemoReviewSend());

  $('btnStartDemo')?.addEventListener('click', () => {
    $('btnStartDemo')?.classList.remove('wiggle-attention');
    goToHome();
    setTourStep('step2');
    applyFocalPoint();
    updateTourFloating();
  });

  (document.getElementById('profile-btn') || document.querySelector('[data-action="profile"]'))?.addEventListener('click', () => {
    if (document.querySelector('[data-action="profile"].is-demo-disabled')) return;
    goToProfile();
  });

  document.querySelectorAll('.action-tiles [data-tab]').forEach((el) => {
    el.addEventListener('click', () => setHomeTab(el.getAttribute('data-tab')));
  });

  $('btnArchiveCheckin')?.addEventListener('click', () => archiveCheckin());

  document.querySelectorAll('.fab').forEach((fab) => {
    fab.addEventListener('click', (e) => {
      e.preventDefault();
      $('fabNewCheckin')?.classList.remove('fab-attention', 'fab-glow');
      openCreateActionSheet();
    });
  });

  $('tour-close')?.addEventListener('click', closeTour);
  $('tour-minimize')?.addEventListener('click', minimizeTour);
  $('tour-bubble')?.addEventListener('click', () => {
    if (state.isFinalStep) return; // HARD STOP on final step
    tour.isHidden = false;
    tour.isMinimized = false;
    showTour();
  });
  $('tour-next')?.addEventListener('click', () => advanceDemo());

  window.addEventListener('resize', updateTourFloating, { passive: true });
  window.addEventListener('scroll', updateTourFloating, { passive: true });

  document.querySelectorAll('.content-area').forEach(el => {
    el.addEventListener('scroll', updateTourFloating, { passive: true });
  });

document
  .getElementById('btnLoadSampleData')
  ?.addEventListener('click', loadSampleCheckins);

  initPasswordToggles();
  initCreateActionSheet();
}

function initPasswordToggles() {
  const eyePath = `${assetBase}/shared/assets/icons/lucide/eye.svg`;
  const eyeOffPath = `${assetBase}/shared/assets/icons/lucide/eye-off.svg`;
  function wireToggle(toggleId, inputId) {
    const btn = $(toggleId);
    const input = $(inputId);
    if (!btn || !input) return;
    btn.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      const img = btn.querySelector('img');
      if (img) img.src = isPassword ? eyePath : eyeOffPath;
      btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
  }
  wireToggle('toggle-new-password', 'change-password-new');
  wireToggle('toggle-confirm-password', 'change-password-confirm');
}

/* =========================================================
   INIT
========================================================= */

function init() {
  loadCheckins();
  initializeWebsite();
  applyPersonalization();

  // Mobile mode
  applyMobileMode();
  window.addEventListener('resize', () => {
    applyMobileMode();
    updateMobileLayoutMetrics();
    positionMobileSpotlight();
  });

  // Greeting
  const greeting = document.querySelector('.greeting');
  if (greeting) {
    greeting.innerHTML = `
      Hi, <span class="greeting-accent">${demoUser.firstName}</span> | ${demoUser.businessName}
    `;
  }

  updateProfilePersonalization();

  // Wire UI
  wireControls();
  wirePostDemoPanel();
  wireOutcomesSlideshow();

  // Apply demo mode restrictions if in demo mode (not on prototype)
  applyDemoRestrictions();

  applyFocalPoint();
  syncAttentionAnimations();

  // Location switcher: shared UI for both prototype and demo (demo = no-op on switch)
  initLocationSwitcher();

  initTagSelector();
  initRegenerateModal();

  // Prototype page: start on app home screen, no tour, no Start Demo; all controls active
  if (isPrototype) {
    document.body.classList.add('jcp-phone-shell');
    document.querySelectorAll('.app-screen').forEach((s) => s.classList.remove('active'));
    const homeScreen = document.getElementById('home-screen');
    if (homeScreen) homeScreen.classList.add('active');
    state.currentScreen = 'home-screen';
    renderHomeCheckins();
    document.getElementById('tour-float')?.classList.add('is-hidden');
    document.getElementById('tour-bubble')?.classList.add('is-hidden');
    ensurePrototypeControlsEnabled();
    syncAttentionAnimations();
    initLocationSmartPrompt();
    return;
  }

  const returnState = readReturnState();
  if (returnState) {
    if (returnState.hasPublished) {
      state.hasPublished = true;
    }
    if (returnState.guideDisabled) {
      state.guideDisabled = true;
    }
    if (returnState.screenId) {
      setScreen(returnState.screenId);
    }
    if (returnState.stepKey) {
      setTourStep(returnState.stepKey);
    }
    
    // Only show tour if guide is not disabled
    // (when returning from directory, guide is disabled and tooltip should be hidden)
    if (!state.guideDisabled) {
      showTour();
      updateTourFloating();
    } else {
      // Ensure tour elements are hidden when guide is disabled
      document.getElementById('tour-float')?.classList.add('is-hidden');
      document.getElementById('tour-bubble')?.classList.add('is-hidden');
    }
    
    if (returnState.showPostDemoPanel) {
      showPostDemoPanel();
    }
    clearReturnState();
    return;
  }

  // Tour start (after DOM paints)
  setTimeout(() => {
    jcpDemoTrack('demo_run_started');
    // Matomo + dataLayer (once per session)
    try {
      if (typeof _paq !== 'undefined') {
        if (!sessionStorage.getItem('jcp_matomo_demo_started')) {
          _paq.push(['trackEvent', 'Demo', 'Started']);
          sessionStorage.setItem('jcp_matomo_demo_started', '1');
        }
        if (!sessionStorage.getItem('jcp_matomo_demo_run_started')) {
          _paq.push(['trackEvent', 'Demo Run Started', 'Run Started']);
          sessionStorage.setItem('jcp_matomo_demo_run_started', '1');
        }
      }
      if (typeof window.dataLayer !== 'undefined' && !sessionStorage.getItem('jcp_datalayer_demo_run_started')) {
        window.dataLayer.push({ event: 'demo_run_started' });
        sessionStorage.setItem('jcp_datalayer_demo_run_started', '1');
      }
    } catch (e) {}
    setTourStep('step1');
    showTour();
    updateTourFloating();
  }, 50);
}

function updateTourNextLabel(label = 'Next →') {
  const btn = $('tour-next');
  if (btn) btn.textContent = label;
}

function readReturnState() {
  try {
    const raw = localStorage.getItem('demoReturnState');
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
}

function clearReturnState() {
  try {
    localStorage.removeItem('demoReturnState');
  } catch (e) {
    // no-op
  }
}

function openDirectoryProfileFromDemo() {
  // Final hard stop of tour
  tour.isHidden = true;
  tour.isMinimized = true;
  state.guideDisabled = true;
  tour.stepKey = null;

  document.getElementById('tour-float')?.classList.add('is-hidden');
  document.getElementById('tour-bubble')?.classList.add('is-hidden');

  localStorage.setItem(
    'directoryDemoListing',
    JSON.stringify({
      businessName: demoUser.businessName,
      status: 'Active',
      lastJob: 'Just now'
    })
  );

  localStorage.setItem(
    'demoReturnState',
    JSON.stringify({
      screenId: state.currentScreen,
      stepKey: 'step6',
      showPostDemoPanel: true,
      guideDisabled: true,
      hasPublished: state.hasPublished
    })
  );

  // Go to the main Directory page (not a single listing)
  window.location.href = `${baseUrl}/directory/`;
}

/* =========================================================
   FINAL POST-DEMO SALES STEP
========================================================= */

function showPostDemoPanel() {
  const panel = document.getElementById('post-demo-panel');
  if (!panel) return;

  document.getElementById('post-demo-bubble')?.classList.add('is-hidden');
  setMobileGuideCollapsed(true);

  panel.classList.add('active');
  jcpDemoTrack('post_demo_modal_shown');
  // Matomo: Demo / Completed (once per session)
  try {
    if (typeof _paq !== 'undefined' && !sessionStorage.getItem('jcp_matomo_demo_completed')) {
      _paq.push(['trackEvent', 'Demo Completed', 'Completed']);
      sessionStorage.setItem('jcp_matomo_demo_completed', '1');
    }
  } catch (e) {}

  // dataLayer for GTM: Demo Completed (same moment, once per session)
  try {
    if (typeof window.dataLayer !== 'undefined' && !sessionStorage.getItem('jcp_datalayer_demo_completed')) {
      window.dataLayer.push({ event: 'demo_completed', registration_type: 'demo_completed' });
      sessionStorage.setItem('jcp_datalayer_demo_completed', '1');
    }
  } catch (e) {}

  hideMobileSpotlight();
  document.getElementById('tour-float')?.classList.add('is-hidden');
  document.getElementById('tour-bubble')?.classList.add('is-hidden');
  updateGuidedCoachBackdrop();

  panel.addEventListener('click', postDemoOverlayClose);
  document.addEventListener('keydown', postDemoEscClose);
}

function hidePostDemoPanel() {
  const panel = document.getElementById('post-demo-panel');
  const bubble = document.getElementById('post-demo-bubble');
  if (!panel || !bubble) return;

  panel.classList.remove('active');
  if (state.isFinalStep && tour.stepKey === 'step6') {
    bubble.classList.remove('is-hidden');
  } else {
    bubble.classList.add('is-hidden');
  }

  panel.removeEventListener('click', postDemoOverlayClose);
  document.removeEventListener('keydown', postDemoEscClose);
  const dir = document.getElementById('directory-collapsible');
  if (dir) dir.style.display = 'none';
  document.getElementById('directory-hint')?.classList.add('is-hidden');

  if (tour.stepKey === 'step6' && isGuidedDemoRun() && !outcomesSlideshow.isOpen) {
    showOutcomesCtaDock();
  }
}

function postDemoOverlayClose(e) {
  if (e.target.id === 'post-demo-panel') {
    hidePostDemoPanel();
  }
}

function postDemoEscClose(e) {
  if (e.key === 'Escape') {
    hidePostDemoPanel();
  }
}


/* ----------------------------------
   Email demo link (basic stub)
---------------------------------- */
function emailDemoLink() {
  if (!demoUser.email) {
    alert('We don’t have an email on file for this demo.');
    return;
  }

  fetch('/api/email-demo', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: demoUser.email,
      firstName: demoUser.firstName,
      businessName: demoUser.businessName,
      demoUrl: window.location.href
    })
  });

  const btn = document.getElementById('postDemoEmailBtn');
  if (btn) {
    btn.textContent = 'Demo sent ✓';
    btn.disabled = true;
    btn.classList.add('is-disabled');
  }
}



/* ----------------------------------
   Bind panel buttons
---------------------------------- */
function wirePostDemoPanel() {
  document
    .getElementById('postDemoX')
    ?.addEventListener('click', hidePostDemoPanel);

  document
    .getElementById('post-demo-bubble')
    ?.addEventListener('click', showPostDemoPanel);

  const primaryCta = document.querySelector('.post-demo-primary-cta');
  if (primaryCta) {
    primaryCta.href = jcpBuildOnboardingUrl(jcpDemoOnboardingHandoffQuery('demo_post_panel'));
    primaryCta.addEventListener('click', function() {
      jcpDemoTrack('cta_clicked', null, { cta: 'get_started_free' });
      jcpDemoTrack('demo_converted');
      // Matomo: Post Demo CTA Click (Early Access), once per session
      try {
        if (typeof _paq !== 'undefined' && !sessionStorage.getItem('jcp_matomo_demo_cta_early_access')) {
          _paq.push(['trackEvent', 'Demo', 'Post Demo CTA Click (Early Access)']);
          sessionStorage.setItem('jcp_matomo_demo_cta_early_access', '1');
        }
      } catch (e) {}
    });
  }

  document
    .getElementById('btnReplayDemo')
    ?.addEventListener('click', () => {
      restartGuidedDemo();
    });
}


// Don't call init() immediately - wait for jcp-render.js to inject the HTML
// jcp-render.js will call window.initDemo() after the template is loaded
window.__JCP_DEMO_INITED = window.__JCP_DEMO_INITED || false;
window.initDemo = () => {
  if (window.__JCP_DEMO_INITED) return;
  window.__JCP_DEMO_INITED = true;
  init();
};

// Defensive: if template loads before this script (race), auto-init once DOM has demo UI
function jcpMaybeAutoInitDemo() {
  if (window.__JCP_DEMO_INITED) return;
  if (document.getElementById('btnStartDemo') || document.getElementById('home-screen')) {
    window.initDemo();
  }
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(jcpMaybeAutoInitDemo, 0);
    setTimeout(jcpMaybeAutoInitDemo, 250);
  });
} else {
  setTimeout(jcpMaybeAutoInitDemo, 0);
  setTimeout(jcpMaybeAutoInitDemo, 250);
}

/* =========================================================
   GLOBALS (required because HTML uses inline onclick)
   Keep these on window so nothing breaks.
========================================================= */
window.goToHome = goToHome;
window.goToNew = goToNew;
window.openCreateActionSheet = openCreateActionSheet;
window.closeCreateActionSheet = closeCreateActionSheet;
window.goToProfile = goToProfile;
window.goToEditProfile = goToEditProfile;
window.saveEditProfile = saveEditProfile;
window.goToChangePassword = goToChangePassword;
window.confirmChangePassword = confirmChangePassword;
window.goToRequestReview = goToRequestReview;
window.goToReviewRequestOptions = goToReviewRequestOptions;
window.goBackFromReviewMethod = goBackFromReviewMethod;
window.goBackToEdit = goBackToEdit;
window.submitReviewRequestFromScreen = submitReviewRequestFromScreen;
window.addPhotos = addPhotos;
window.processPhotos = processPhotos;
window.regenerateDescription = regenerateDescription;
window.saveCheckin = saveCheckin;
window.openReviewDialog = openReviewDialog;
window.closeReviewDialog = closeReviewDialog;
window.confirmDemoReviewSend = confirmDemoReviewSend;
window.sendReviewRequest = sendReviewRequest;
window.openCheckinForEdit = openCheckinForEdit;
window.advanceDemo = advanceDemo;

// Demo / prototype mode utilities
window.JCP_DEMO = {
  isPrototype: isPrototype,
  isDemoMode: isDemoMode,
  showRestrictionTooltip: showDemoRestrictionTooltip,
  restrictions: demoRestrictions
};
