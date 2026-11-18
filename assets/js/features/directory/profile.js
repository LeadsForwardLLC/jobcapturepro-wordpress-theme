/* =========================================================
   DYNAMIC CONTRACTOR PROFILE
   ---------------------------------------------------------
   Loads contractor data from URL params and displays
   profile information dynamically
========================================================= */

const assetBase = window.JCP_ASSET_BASE || '';

const CONTRACTOR_DATA = {
  badges: {
    verified: {
      label: "Verified",
      color: "#3b82f6",
      tooltip: "This contractor has completed verified jobs with real photos, locations, and activity."
    },
    trusted: {
      label: "Trusted Pro",
      color: "#f59e0b",
      tooltip: "Trusted Pro contractors show consistent verified work and high ongoing activity."
    },
    listed: {
      label: "Listed",
      color: "#6b7280",
      tooltip: "This contractor is listed in the directory but has limited verified job activity."
    },
    unlisted: {
      label: "Unlisted",
      color: "#6b7280",
      tooltip: "This contractor has limited verified activity and reduced directory visibility."
    }
  }
};

/* =========================================================
   HELPER FUNCTIONS
========================================================= */


/* =========================================================
   LOAD CONTRACTOR DATA
========================================================= */

function toTitle(text) {
  return String(text || '')
    .replace(/[-_]+/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
    .trim();
}

/** Replace HTML entities and Unicode dashes with a clean hyphen so they don't display as strange characters. */
function cleanDashes(text) {
  if (text == null || typeof text !== 'string') return '';
  return text
    .replace(/&#8211;/g, '-')
    .replace(/&#8212;/g, '-')
    .replace(/\u2013/g, '-')
    .replace(/\u2014/g, '-')
    .trim();
}

function getCompanyInitial(name) {
  if (!name || typeof name !== 'string') return '?';
  const cleaned = cleanDashes(name);
  return (cleaned.charAt(0) || '?').toUpperCase();
}

function getAvatarColor(initial) {
  const colors = [
    'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
    'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
    'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
    'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)',
    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)'
  ];
  const charCode = (initial || '?').charCodeAt(0);
  return colors[charCode % colors.length];
}

function isValidLogo(url) {
  if (!url || typeof url !== 'string' || url.trim() === '') return false;
  return url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//');
}

function loadContractorData() {
  if (window.JCP_PROFILE_DATA) {
    return window.JCP_PROFILE_DATA;
  }

  try {
    const listing = JSON.parse(localStorage.getItem('directoryDemoListing') || 'null');
    const demoUser = JSON.parse(localStorage.getItem('demoUser') || 'null');

    if (listing || demoUser) {
      const service = toTitle(demoUser?.niche || 'Service');
      const city = demoUser?.serviceArea || 'Service Area';

      return {
        name: listing?.businessName || demoUser?.businessName || 'Your Business',
        service,
        city,
        badge: 'verified',
        rating: '5.0',
        reviews: 'New',
        activity: listing?.status || 'Active',
        jobs: 1,
        lastJobDaysAgo: 0,
        verifiedViaLiveJobs: true,
        checkins: []
      };
    }
  } catch (e) {
    // no-op
  }

  return null;
}

/* =========================================================
   RENDER PROFILE
========================================================= */

function renderProfile(data) {
  if (!data) return;
  
  const cleanName = cleanDashes(data.name || '');
  document.title = `${cleanName} — Contractor Profile | JobCapturePro`;

  // Update badge and tooltip
  const badgeEl = document.getElementById('profileBadge');
  const tooltipEl = document.getElementById('profileBadgeTooltip');
  if (badgeEl) {
    const badgeKey = data.badge && CONTRACTOR_DATA.badges[data.badge] ? data.badge : 'listed';
    const badgeInfo = CONTRACTOR_DATA.badges[badgeKey];
    badgeEl.textContent = badgeInfo.label;
    badgeEl.className = `directory-badge ${badgeKey}`;
    if (tooltipEl && badgeInfo.tooltip) {
      tooltipEl.textContent = badgeInfo.tooltip;
    }
  }
  
  // Update name (clean dashes so &#8211; etc. don't show as strange characters)
  const nameEl = document.querySelector('.profile-hero-copy .jcp-hero-title');
  if (nameEl) nameEl.textContent = cleanName;

  // Primary service (capitalize in case API sends lowercase)
  const primaryServiceVal = document.getElementById('profilePrimaryServiceValue');
  if (primaryServiceVal) primaryServiceVal.textContent = toTitle(data.service || 'Service');

  // Full address (clean dashes; from post type only)
  const addressVal = document.getElementById('profileAddressValue');
  if (addressVal) addressVal.textContent = cleanDashes(data.addressFormatted || '') || '—';

  // Phone · Website: inject Lucide icons once so they show to the left of number/website
  const phoneIconWrap = document.getElementById('profilePhoneIconWrap');
  const websiteIconWrap = document.getElementById('profileWebsiteIconWrap');
  if (phoneIconWrap && !phoneIconWrap.innerHTML.trim()) {
    phoneIconWrap.innerHTML = '<img src="' + assetBase + '/shared/assets/icons/lucide/phone.svg" class="lucide-icon lucide-icon-sm" alt="" role="presentation">';
  }
  if (websiteIconWrap && !websiteIconWrap.innerHTML.trim()) {
    websiteIconWrap.innerHTML = '<img src="' + assetBase + '/shared/assets/icons/lucide/earth.svg" class="lucide-icon lucide-icon-sm" alt="" role="presentation">';
  }

  const phoneWrap = document.getElementById('profilePhoneWrap');
  const phoneLink = document.getElementById('profilePhoneLink');
  const websiteWrap = document.getElementById('profileWebsiteWrap');
  const websiteLink = document.getElementById('profileWebsiteLink');
  const contactSep = document.getElementById('profileContactSep');
  const contactLine = document.getElementById('profileContactLine');
  if (phoneLink && data.phone) {
    phoneLink.href = 'tel:' + data.phone.replace(/[^\d+]/g, '');
    phoneLink.textContent = data.phone;
    if (phoneWrap) phoneWrap.style.display = '';
  } else if (phoneWrap) phoneWrap.style.display = 'none';
  if (websiteLink && data.website) {
    websiteLink.href = data.website;
    websiteLink.textContent = 'Website';
    if (websiteWrap) websiteWrap.style.display = '';
  } else if (websiteWrap) websiteWrap.style.display = 'none';
  if (contactSep) contactSep.style.display = (data.phone && data.website) ? '' : 'none';
  if (contactLine) contactLine.style.display = (data.phone || data.website) ? '' : 'none';

  // Show owner name if available
  if (data.owner) {
    const ownerEl = document.getElementById('profile-owner');
    if (ownerEl) {
      ownerEl.textContent = `Owner: ${data.owner}`;
      ownerEl.classList.remove('is-hidden');
    }
  }
  
  // Show verified badge if applicable
  if (data.verifiedViaLiveJobs) {
    const verifiedEl = document.getElementById('profile-verified');
    if (verifiedEl) verifiedEl.classList.remove('is-hidden');
  }
  
  // Update rating
  const reviewCountEl = document.querySelector('.review-count');
  if (reviewCountEl) {
    const rating = data.rating ? data.rating : '—';
    const reviews = data.reviews ? `${data.reviews} reviews` : 'No reviews yet';
    reviewCountEl.textContent = `${rating} (${reviews})`;
  }
  
  // Update description if there's a description section
  const descEl = document.querySelector('.profile-description');
  if (descEl && data.description) {
    descEl.textContent = data.description;
  }

  // TODO: Replace static placeholder with dynamic bio when contractor bio field exists
  const bioEl = document.querySelector('.profile-bio');
  if (bioEl && data.description && data.description.trim()) {
    bioEl.textContent = cleanDashes(data.description.trim());
  }

  // Hydrate proof block with real activity signals (jobs, city)
  const proofItems = document.querySelectorAll('.profile-proof-item');
  if (proofItems.length >= 2 && data.jobs != null) {
    const firstText = proofItems[0].querySelector('span:last-child');
    if (firstText) firstText.innerHTML = `Verified on <strong>${data.jobs}</strong> real jobs`;
  }
  if (proofItems.length >= 2 && data.city) {
    const secondText = proofItems[1].querySelector('span:last-child');
    if (secondText) secondText.innerHTML = `Active weekly in <strong>${data.city}</strong>`;
  }
  
  // Render check-ins
  renderCheckins(data.checkins || []);
}

function renderCheckins(checkins) {
  const checkinsGrid = document.getElementById('checkinsGrid');
  if (!checkinsGrid) return;

  if (!checkins.length) {
    checkinsGrid.innerHTML = `
      <div class="work-card">
        <div class="work-body">
          <h3>New check-ins coming soon</h3>
          <p>This contractor is active, and real job proof will appear here as check-ins sync.</p>
          <span class="meta">Updated automatically</span>
        </div>
      </div>
    `;
    return;
  }

  checkinsGrid.innerHTML = '';

  checkins.forEach(checkin => {
    const card = document.createElement('div');
    card.className = 'checkin-card';
    card.innerHTML = `
      <div class="checkin-image">
        <img src="${checkin.image}" alt="${checkin.title}" width="400" height="210" loading="lazy" />
      </div>
      <div class="checkin-content">
        <h3 class="checkin-title">${checkin.title}</h3>
        <p class="checkin-description">${checkin.description}</p>
        <div class="checkin-meta">
          <span class="checkin-time">
            <img src="${assetBase}/shared/assets/icons/lucide/clock.svg" class="lucide-icon lucide-icon-xs" alt="">
            ${checkin.time}
          </span>
          <span class="checkin-location">
            <img src="${assetBase}/shared/assets/icons/lucide/map-pin.svg" class="lucide-icon lucide-icon-xs" alt="">
            ${checkin.location}
          </span>
        </div>
      </div>
    `;
    checkinsGrid.appendChild(card);
  });
}

function showError(message) {
  const main = document.querySelector('main');
  if (main) {
    const baseUrl = window.JCP_CONFIG && window.JCP_CONFIG.baseUrl
      ? window.JCP_CONFIG.baseUrl
      : window.location.origin;
    main.innerHTML = `
      <div class="error-state">
        <h2>⚠️ ${message}</h2>
        <p>Please select a contractor from the directory.</p>
        <a href="${baseUrl}/" class="btn btn-primary">Back to Home</a>
      </div>
    `;
  }
}

/* =========================================================
   GALLERY FUNCTIONALITY
========================================================= */

function initGallery() {
  const gallery = document.getElementById('profileGallery');
  const track = gallery?.querySelector('.profile-gallery-track');
  const dotsContainer = document.getElementById('galleryDots');
  const prevBtn = document.querySelector('.gallery-prev');
  const nextBtn = document.querySelector('.gallery-next');
  
  if (!gallery || !track || !dotsContainer) return;
  
  const items = track.querySelectorAll('.profile-gallery-item');
  let currentIndex = 0;
  
  // Create dots
  items.forEach((_, index) => {
    const dot = document.createElement('button');
    dot.className = `gallery-dot ${index === 0 ? 'active' : ''}`;
    dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
    dot.addEventListener('click', () => goToSlide(index));
    dotsContainer.appendChild(dot);
  });
  
  function updateGallery() {
    track.style.transform = `translateX(-${currentIndex * 100}%)`;
    
    // Update dots
    dotsContainer.querySelectorAll('.gallery-dot').forEach((dot, index) => {
      dot.classList.toggle('active', index === currentIndex);
    });
    
    // Update button states
    if (prevBtn) prevBtn.disabled = currentIndex === 0;
    if (nextBtn) nextBtn.disabled = currentIndex === items.length - 1;
  }
  
  function goToSlide(index) {
    currentIndex = Math.max(0, Math.min(index, items.length - 1));
    updateGallery();
  }
  
  function nextSlide() {
    if (currentIndex < items.length - 1) {
      currentIndex++;
      updateGallery();
    }
  }
  
  function prevSlide() {
    if (currentIndex > 0) {
      currentIndex--;
      updateGallery();
    }
  }
  
  // Button handlers
  if (prevBtn) prevBtn.addEventListener('click', prevSlide);
  if (nextBtn) nextBtn.addEventListener('click', nextSlide);
  
  // Keyboard navigation
  gallery.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') prevSlide();
    if (e.key === 'ArrowRight') nextSlide();
  });
  
  // Touch/swipe support
  let startX = 0;
  let isDragging = false;
  
  gallery.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    isDragging = true;
  });
  
  gallery.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
  });
  
  gallery.addEventListener('touchend', (e) => {
    if (!isDragging) return;
    isDragging = false;
    const endX = e.changedTouches[0].clientX;
    const diff = startX - endX;
    
    if (Math.abs(diff) > 50) {
      if (diff > 0) nextSlide();
      else prevSlide();
    }
  });
  
  // Auto-play (optional - can be disabled)
  // let autoPlayInterval = setInterval(nextSlide, 5000);
  // gallery.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
  // gallery.addEventListener('mouseleave', () => {
  //   autoPlayInterval = setInterval(nextSlide, 5000);
  // });
  
  updateGallery();
}

/* =========================================================
   PROFILE CTAs (Call / Request quote)
   UI only – no backend yet. TODO: call tracking integration.
========================================================= */

function initProfileCTAs() {
  const callBtn = document.getElementById('profileCtaCall');
  const quoteBtn = document.getElementById('profileCtaQuote');

  if (callBtn) {
    callBtn.addEventListener('click', (e) => {
      e.preventDefault();
      // TODO: Future call tracking integration (e.g. analytics, phone link, or modal)
      window.dispatchEvent(new CustomEvent('jcp_profile_call_click', { detail: { source: 'banner' } }));
    });
  }

  if (quoteBtn) {
    quoteBtn.addEventListener('click', (e) => {
      e.preventDefault();
      // TODO: Future homeowner quote flow – placeholder; can open modal or navigate later
      window.dispatchEvent(new CustomEvent('jcp_profile_quote_click', { detail: { source: 'banner' } }));
    });
  }
}

/* =========================================================
   INIT (called by jcp-render.js after template is injected)
========================================================= */

function initProfile() {
  const contractorData = loadContractorData();
  if (contractorData) {
    renderProfile(contractorData);
  } else {
    showError('Contractor data unavailable');
  }
  initGallery();
  initProfileCTAs();
}

window.initProfile = initProfile;
