const CONFIG = {
  googleMapsApiKey: "AIzaSyB5E2wSlEjFNB0CYioD8tuhB0en7LOm46U", // ✅ Your Google Maps API key
  enableGooglePlaces: true,  // ✅ ENABLED - Smart address autocomplete
  enableGoogleMaps: true,    // ✅ ENABLED - Interactive satellite map view
  enableSolarOutline: false, // Not needed for basic estimates
  enablePropertyFacts: false, // Optional - can enable for property details
  enableVisualIntake: true,
  useRealAI: true,
  visualIntakeEndpoint: '/.netlify/functions/visual-intake',
  propertySnapshotEndpoint: '/.netlify/functions/property-snapshot',
  estimateFusionEndpoint: '/.netlify/functions/estimate-fusion'
};

const baseUrl = window.JCP_CONFIG && window.JCP_CONFIG.baseUrl
  ? window.JCP_CONFIG.baseUrl
  : window.location.origin;

const STORAGE_KEY_PHOTOS = "jcpQuotePhotos";
const STORAGE_KEY_PENDING_JOBS = "jcpPendingJobs";
const STORAGE_KEYS = {
  session: "estimateSession",
  address: "address",
  propertyFacts: "propertyFacts",
  measurementPolygon: "measurementPolygon",
  measurements: "measurements",
  measurementMode: "measurementMode",
  visualIntakePhotos: "estimateVisualIntakePhotos",
  visualIntakeAnalysis: "estimateVisualIntakeAnalysis",
  visualIntakeSkipped: "estimateVisualIntakeSkipped",
  propertySnapshot: "estimatePropertySnapshot",
  estimateFusion: "estimateFusion"
};

// Dynamic Scope Options by Service Type
const SCOPE_OPTIONS = {
  'roofing': [
    { value: 'repair', label: 'Repair' },
    { value: 'partial', label: 'Partial Replacement' },
    { value: 'full', label: 'Full Replacement' },
    { value: 'inspection', label: 'Inspection' }
  ],
  'hvac': [
    { value: 'repair', label: 'Repair' },
    { value: 'service', label: 'Service / Maintenance' },
    { value: 'installation', label: 'Installation' },
    { value: 'replacement', label: 'Replacement' }
  ],
  'plumbing': [
    { value: 'repair', label: 'Repair' },
    { value: 'installation', label: 'Installation' },
    { value: 'emergency', label: 'Emergency Service' },
    { value: 'replacement', label: 'Replacement' }
  ],
  'electrical': [
    { value: 'repair', label: 'Repair' },
    { value: 'installation', label: 'Installation' },
    { value: 'upgrade', label: 'Upgrade' },
    { value: 'inspection', label: 'Inspection' }
  ],
  'painting': [
    { value: 'interior', label: 'Interior' },
    { value: 'exterior', label: 'Exterior' },
    { value: 'both', label: 'Interior & Exterior' },
    { value: 'touch-up', label: 'Touch-up / Repair' }
  ],
  'flooring': [
    { value: 'installation', label: 'Installation' },
    { value: 'repair', label: 'Repair' },
    { value: 'refinish', label: 'Refinish' },
    { value: 'removal', label: 'Removal & Replace' }
  ],
  'landscaping': [
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'installation', label: 'Installation' },
    { value: 'design', label: 'Design & Build' },
    { value: 'cleanup', label: 'Cleanup / Removal' }
  ],
  'default': [
    { value: 'repair', label: 'Repair' },
    { value: 'installation', label: 'Installation' },
    { value: 'replacement', label: 'Replacement' },
    { value: 'service', label: 'Service / Maintenance' }
  ]
};

// Dynamic Estimate Verbiage by Service Type & Scope
const ESTIMATE_VERBIAGE = {
  'roofing': {
    'repair': {
      good: ['Standard materials', 'Basic warranty', 'Timeline 1-2 days'],
      better: ['Quality materials', 'Extended warranty', 'Timeline 1-2 days'],
      best: ['Premium materials', 'Lifetime warranty', 'Priority scheduling']
    },
    'full': {
      good: ['Architectural shingles', '10-year warranty', 'Timeline 2-3 days'],
      better: ['Premium shingles', '25-year warranty', 'Timeline 2-4 days'],
      best: ['Designer shingles', 'Lifetime warranty', 'Timeline 3-5 days']
    }
  },
  'hvac': {
    'repair': {
      good: ['Standard parts', '90-day warranty', 'Same-day service'],
      better: ['OEM parts', '1-year warranty', 'Priority scheduling'],
      best: ['Premium parts', '2-year warranty', 'Emergency service']
    },
    'installation': {
      good: ['Standard efficiency', '5-year warranty', 'Timeline 1-2 days'],
      better: ['High efficiency', '10-year warranty', 'Timeline 1-2 days'],
      best: ['Premium efficiency', '15-year warranty', 'White glove install']
    }
  },
  'plumbing': {
    'repair': {
      good: ['Standard fix', '30-day warranty', 'Timeline 1 day'],
      better: ['Quality parts', '1-year warranty', 'Same-day service'],
      best: ['Premium parts', '2-year warranty', 'Emergency service']
    },
    'installation': {
      good: ['Standard fixtures', '1-year warranty', 'Timeline 1-2 days'],
      better: ['Quality fixtures', '3-year warranty', 'Timeline 1-2 days'],
      best: ['Premium fixtures', '5-year warranty', 'Custom install']
    }
  },
  'electrical': {
    'repair': {
      good: ['Code-compliant fix', '90-day warranty', 'Timeline 1 day'],
      better: ['Quality materials', '1-year warranty', 'Same-day service'],
      best: ['Premium materials', '2-year warranty', 'Priority scheduling']
    },
    'upgrade': {
      good: ['Standard panel', '1-year warranty', 'Timeline 1 day'],
      better: ['Quality panel', '3-year warranty', 'Timeline 1 day'],
      best: ['Smart panel', '5-year warranty', 'Premium install']
    }
  },
  'default': {
    'repair': {
      good: ['Core materials', 'Standard warranty', 'Timeline 2-3 days'],
      better: ['Upgraded materials', 'Extended warranty', 'Timeline 2-4 days'],
      best: ['Premium materials', 'Priority scheduling', 'Timeline 3-5 days']
    },
    'installation': {
      good: ['Standard install', 'Basic warranty', 'Timeline 2-3 days'],
      better: ['Quality install', 'Extended warranty', 'Timeline 2-4 days'],
      best: ['Premium install', 'Comprehensive warranty', 'Timeline 3-5 days']
    }
  }
};

// Measurement Type Mapping - Determines which measurement UI to show
const MEASUREMENT_TYPE = {
  // EXTERIOR services need property footprint from satellite
  'exterior': [
    'roofing', 'roof-repair', 'roof-replacement', 'gutter-cleaning', 'gutter-repair',
    'siding-repair', 'siding-installation', 'exterior-painting',
    'hvac-installation', 'hvac-replacement', 'hvac-repair', 'hvac-maintenance', // All HVAC work
    'landscaping', 'tree-removal', 'fence-repair', 'deck-repair',
    'concrete-work', 'foundation-repair'
  ],
  
  // INTERIOR services need room count + area input
  'interior': [
    'carpet-cleaning', 'carpet-replacement', 'flooring-installation', 'flooring-repair',
    'interior-painting', 'drywall-repair', 'texture-repair',
    'tile-work', 'insulation', 'mold-remediation', 'water-damage'
  ],
  
  // UNIT-BASED services need quantity count
  'unit': [
    'plumbing-repair', 'plumbing-installation', 'water-heater', 'leak-repair',
    'electrical-repair', 'electrical-installation', 'lighting-installation',
    'appliance-installation', 'window-replacement', 'door-installation'
  ],
  
  // HYBRID services need both property + interior details
  'hybrid': [
    'duct-cleaning', 'air-quality', // Whole house systems
    'drain-cleaning', 'panel-upgrade' // Whole house electrical/plumbing
  ]
};

// Helper function to determine measurement type for a service
function getMeasurementType(serviceType) {
  if (!serviceType) return 'exterior'; // Default
  
  const service = serviceType.toLowerCase();
  
  if (MEASUREMENT_TYPE.exterior.some(s => service.includes(s))) return 'exterior';
  if (MEASUREMENT_TYPE.interior.some(s => service.includes(s))) return 'interior';
  if (MEASUREMENT_TYPE.unit.some(s => service.includes(s))) return 'unit';
  if (MEASUREMENT_TYPE.hybrid.some(s => service.includes(s))) return 'hybrid';
  
  return 'exterior'; // Default fallback
}

const $ = (id) => document.getElementById(id);

const steps = [
  {
    title: "Visual Intake",
    summary: "Upload photos to help us prepare a starting description.",
    optional: true
  },
  {
    title: "Address",
    summary: "Find your address to load a quick property view."
  },
  {
    title: "Property view",
    summary: "Confirm measurements in a simple view."
  },
  {
    title: "Project details",
    summary: "Share the details that affect price."
  },
  {
    title: "Estimate",
    summary: "Review options with scope and timing."
  },
  {
    title: "Schedule",
    summary: "Pick a time so we can confirm the details."
  }
];

const addressData = [
  {
    address: "1242 Mason Rd",
    city: "Austin, TX",
    lat: 30.2593,
    lng: -97.7475,
    beds: 3,
    baths: 2,
    year: 1998,
    lot: "0.22 acre",
    area: 2240,
    perimeter: 196,
    confidence: "High"
  },
  {
    address: "542 Oak Meadow Dr",
    city: "Round Rock, TX",
    lat: 30.5157,
    lng: -97.6781,
    beds: 4,
    baths: 3,
    year: 2006,
    lot: "0.28 acre",
    area: 2680,
    perimeter: 212,
    confidence: "High"
  },
  {
    address: "88 West Elm St",
    city: "Georgetown, TX",
    lat: 30.6332,
    lng: -97.6779,
    beds: 3,
    baths: 2,
    year: 1992,
    lot: "0.19 acre",
    area: 1980,
    perimeter: 184,
    confidence: "Medium"
  },
  {
    address: "915 Cypress Ridge",
    city: "Cedar Park, TX",
    lat: 30.5237,
    lng: -97.8203,
    beds: 5,
    baths: 4,
    year: 2014,
    lot: "0.35 acre",
    area: 3240,
    perimeter: 238,
    confidence: "High"
  }
];

const serviceByNiche = {
  plumbing: "plumbing",
  hvac: "hvac",
  electrical: "electrical",
  roofing: "roofing",
  "general-contractor": "general",
  landscaping: "general",
  other: "general",
  service: "general"
};

const state = {
  step: 0,
  selectedAddress: null,
  measurementMode: "auto",
  drawPoints: [],
  drawComplete: false,
  drawSource: "fallback",
  map: null,
  mapMarker: null,
  mapPolygon: null,
  mapLine: null,
  placesService: null,
  autocompleteService: null,
  mapDoubleClickListener: null,
  mapClickListener: null,
  mapsApi: null,
  propertyFacts: null,
  measurements: null,
  photos: [],
  urgency: "soon",
  estimateReady: true,
  selectedTime: "",
  visualIntakePhotos: [],
  visualIntakeAnalysis: null,
  visualIntakeSkipped: false,
  analyzingPhotos: false,
  propertySnapshot: null,
  estimateFusion: null
};

const dom = {
  stageEyebrow: $("stageEyebrow"),
  stageTitle: $("stageTitle"),
  stageSummary: $("stageSummary"),
  stepPanels: Array.from(document.querySelectorAll(".step-panel")),
  stepList: Array.from(document.querySelectorAll(".step-item")),
  backBtn: $("backBtn"),
  nextBtn: $("nextBtn"),
  // Visual Intake (Step 0)
  uploadDropzone: $("uploadDropzone"),
  visualIntakeInput: $("visualIntakeInput"),
  uploadTriggerBtn: $("uploadTriggerBtn"),
  intakePhotoGrid: $("intakePhotoGrid"),
  intakeAnalyzing: $("intakeAnalyzing"),
  intakeReview: $("intakeReview"),
  reviewServiceType: $("reviewServiceType"),
  reviewDescription: $("reviewDescription"),
  reviewConfidence: $("reviewConfidence"),
  skipVisualIntakeBtn: $("skipVisualIntakeBtn"),
  reAnalyzeBtn: $("reAnalyzeBtn"),
  // Step 1: Address
  addressInput: $("addressInput"),
  addressSuggestions: $("addressSuggestions"),
  propertyPreview: $("propertyPreview"),
  propertySkeleton: $("propertySkeleton"),
  propertyAddress: $("propertyAddress"),
  propertyCity: $("propertyCity"),
  propertyFacts: $("propertyFacts"),
  mapCanvas: $("mapCanvas"),
  mapFallback: $("mapFallback"),
  summaryArea: $("summaryArea"),
  summaryPerimeter: $("summaryPerimeter"),
  summaryConfidence: $("summaryConfidence"),
  toggleManualBtn: $("toggleManualBtn"),
  manualInputs: $("manualInputs"),
  manualArea: $("manualArea"),
  manualPerimeter: $("manualPerimeter"),
  applyManualBtn: $("applyManualBtn"),
  serviceType: $("serviceType"),
  scopeType: $("scopeType"),
  scopeLevel: $("scopeLevel"),
  notes: $("notes"),
  photoDrop: $("photoDrop"),
  photoInput: $("photoInput"),
  photoThumbs: $("photoThumbs"),
  trustLine: $("trustLine"),
  estimateGrid: $("estimateGrid"),
  estimateSkeleton: $("estimateSkeleton"),
  priceGood: $("priceGood"),
  priceBetter: $("priceBetter"),
  priceBest: $("priceBest"),
  estimateBasedOn: $("estimateBasedOn"),
  estimateConfidence: $("estimateConfidence"),
  estimateChanges: $("estimateChanges"),
  timeChips: $("timeChips"),
  contactName: $("contactName"),
  contactPhone: $("contactPhone"),
  contactEmail: $("contactEmail"),
  startOverBtn: $("startOverBtn"),
  softwareContext: $("softwareContext"),
  softwareStep: $("softwareStep"),
  softwareFill: $("softwareFill"),
  softwareHelper: $("softwareHelper"),
  stageStatus: $("stageStatus"),
  mobileSteps: Array.from(document.querySelectorAll(".mobile-step")),
  // Property Snapshot
  propertySnapshotPanel: $("propertySnapshotPanel"),
  snapshotStreetView: $("snapshotStreetView"),
  snapshotSatellite: $("snapshotSatellite"),
  snapshotHomeowner: $("snapshotHomeowner"),
  snapshotHomeownerItem: $("snapshotHomeownerItem"),
  snapshotContent: $("snapshotContent"),
  snapshotSummary: $("snapshotSummary"),
  snapshotConfirm: $("snapshotConfirm"),
  snapshotFallback: $("snapshotFallback"),
  // Estimate Delivery
  deliveryEmail: $("deliveryEmail"),
  quoteNumber: $("quoteNumber"),
  downloadPdfBtn: $("downloadPdfBtn"),
  shareLinkBtn: $("shareLinkBtn")
};

function loadFromStorage(key) {
  try {
    const raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
}

function saveToStorage(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch (e) {
    console.warn("Storage unavailable");
  }
}

function saveSession() {
  saveToStorage(STORAGE_KEYS.session, {
    step: state.step,
    measurementMode: state.measurementMode,
    selectedTime: state.selectedTime
  });
}

function restoreSession() {
  const session = loadFromStorage(STORAGE_KEYS.session);
  if (session?.step) {
    // Don't skip Address step unless we have a saved address
    if (session.step > 1 && !loadFromStorage(STORAGE_KEYS.address)) {
      state.step = 1; // Force to Address step if no saved address
    } else {
      state.step = session.step;
    }
  }
  if (session?.measurementMode) {
    state.measurementMode = session.measurementMode;
  }
  if (session?.selectedTime) {
    state.selectedTime = session.selectedTime;
  }
  if (!session?.measurementMode) {
    const storedMode = loadFromStorage(STORAGE_KEYS.measurementMode);
    if (storedMode) {
      state.measurementMode = storedMode;
    }
  }
}

function storeAddress(address) {
  saveToStorage(STORAGE_KEYS.address, address);
}

function storePropertyFacts(facts) {
  saveToStorage(STORAGE_KEYS.propertyFacts, facts);
}

function storeMeasurements(measurements) {
  saveToStorage(STORAGE_KEYS.measurements, measurements);
}

function storePolygon(polygon) {
  saveToStorage(STORAGE_KEYS.measurementPolygon, polygon);
}

function storeMeasurementMode(mode) {
  saveToStorage(STORAGE_KEYS.measurementMode, mode);
}

function readDemoUser() {
  try {
    const raw = localStorage.getItem("demoUser");
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
}

function applyPersonalization() {
  const demoUser = readDemoUser();
  const contractorContext = readContractorContext();
  const businessName = demoUser?.businessName || "JobCapturePro";
  const firstName = demoUser?.firstName || "";
  const serviceDefault = serviceByNiche[demoUser?.niche] || "general";

  dom.softwareContext.textContent = "Home estimate";

  // Use contractor name if coming from directory, otherwise use demo user's first name
  if (contractorContext.contractor) {
    dom.trustLine.textContent = `Your estimate will be reviewed by ${contractorContext.contractor}.`;
  } else if (firstName) {
    dom.trustLine.textContent = `Your estimate will be reviewed by ${firstName}'s team.`;
  }

  dom.serviceType.value = serviceDefault;
}

function loadGoogleMaps() {
  if (!(CONFIG.enableGoogleMaps || CONFIG.enableGooglePlaces) || !CONFIG.googleMapsApiKey) {
    return Promise.resolve(null);
  }
  if (window.google?.maps) {
    return Promise.resolve(window.google.maps);
  }
  return new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src = `https://maps.googleapis.com/maps/api/js?key=${CONFIG.googleMapsApiKey}&libraries=places,geometry&v=weekly`;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve(window.google.maps);
    script.onerror = () => reject(new Error("Google Maps failed to load"));
    document.head.appendChild(script);
  });
}

function hasGoogleMaps() {
  return Boolean(state.mapsApi && CONFIG.enableGoogleMaps && CONFIG.googleMapsApiKey);
}

function hasGooglePlaces() {
  return Boolean(state.mapsApi && CONFIG.enableGooglePlaces && CONFIG.googleMapsApiKey);
}

function mapLatLng(lat, lng) {
  return new window.google.maps.LatLng(lat, lng);
}

async function fetchPropertyFacts(address) {
  if (!CONFIG.enablePropertyFacts) return null;
  try {
    const params = new URLSearchParams();
    if (address.placeId) params.set("placeId", address.placeId);
    if (address.lat && address.lng) {
      params.set("lat", address.lat);
      params.set("lng", address.lng);
    }
    const res = await fetch(`/api/property?${params.toString()}`);
    if (res.status === 404) {
      const fallback = addressData.find((item) => item.address === address.address);
      if (fallback) {
        return {
          beds: fallback.beds,
          baths: fallback.baths,
          yearBuilt: fallback.year,
          lotSize: fallback.lot,
          sqft: fallback.area
        };
      }
    }
    if (!res.ok) throw new Error("Property facts unavailable");
    return await res.json();
  } catch (e) {
    return null;
  }
}

async function fetchOutline(address) {
  if (!CONFIG.enableSolarOutline) return null;
  try {
    const params = new URLSearchParams();
    if (address.placeId) params.set("placeId", address.placeId);
    if (address.lat && address.lng) {
      params.set("lat", address.lat);
      params.set("lng", address.lng);
    }
    const res = await fetch(`/api/outline?${params.toString()}`);
    if (res.status === 404) {
      return { points: createFallbackOutline(address), source: "fallback" };
    }
    if (!res.ok) throw new Error("Outline unavailable");
    return await res.json();
  } catch (e) {
    return null;
  }
}

function initMap(center) {
  if (!hasGoogleMaps()) {
    dom.mapFallback.classList.remove("is-hidden");
    return;
  }
  dom.mapFallback.classList.add("is-hidden");
  if (!state.map) {
    state.map = new window.google.maps.Map(dom.mapCanvas, {
      center,
      zoom: 20,
      mapTypeId: "satellite",
      disableDefaultUI: true,
      gestureHandling: "greedy",
      disableDoubleClickZoom: true
    });
  } else {
    state.map.setCenter(center);
  }
  if (!state.mapMarker) {
    state.mapMarker = new window.google.maps.Marker({
      position: center,
      map: state.map
    });
  } else {
    state.mapMarker.setPosition(center);
  }
}

function clearMapShapes() {
  if (state.mapPolygon) {
    state.mapPolygon.setMap(null);
    state.mapPolygon = null;
  }
  if (state.mapLine) {
    state.mapLine.setMap(null);
    state.mapLine = null;
  }
}

function renderMapDrawPath(points, complete) {
  if (!hasGoogleMaps() || !state.map) return;
  clearMapShapes();
  if (points.length === 0) return;

  if (complete) {
    state.mapPolygon = new window.google.maps.Polygon({
      paths: points,
      map: state.map,
      strokeColor: "#ff503e",
      strokeOpacity: 0.9,
      strokeWeight: 2,
      fillColor: "#ff503e",
      fillOpacity: 0.2
    });
  } else {
    state.mapLine = new window.google.maps.Polyline({
      path: points,
      map: state.map,
      strokeColor: "#ff503e",
      strokeOpacity: 0.9,
      strokeWeight: 2
    });
  }
}

function computeMeasurementsFromLatLng(points) {
  const areaMeters = window.google.maps.geometry.spherical.computeArea(points);
  const lengthMeters = window.google.maps.geometry.spherical.computeLength(points);
  return {
    area: Math.round(areaMeters * 10.7639),
    perimeter: Math.round(lengthMeters * 3.28084)
  };
}

function computeMeasurementsFromPixels(points, widthFt, heightFt) {
  const widthPx = dom.drawCanvas.width || 1;
  const heightPx = dom.drawCanvas.height || 1;
  const scaleX = widthFt / widthPx;
  const scaleY = heightFt / heightPx;
  let area = 0;
  let perimeter = 0;
  for (let i = 0; i < points.length; i += 1) {
    const curr = points[i];
    const next = points[(i + 1) % points.length];
    area += (curr.x * scaleX) * (next.y * scaleY) - (next.x * scaleX) * (curr.y * scaleY);
    const dx = (next.x - curr.x) * scaleX;
    const dy = (next.y - curr.y) * scaleY;
    perimeter += Math.hypot(dx, dy);
  }
  return {
    area: Math.round(Math.abs(area) / 2),
    perimeter: Math.round(perimeter)
  };
}

function updateMeasurementUI() {
  // Get current service type from multiple sources (priority order)
  let serviceType = '';
  
  // 1. Try contractor context first
  const contractorContext = readContractorContext();
  if (contractorContext.service) {
    serviceType = contractorContext.service;
  }
  
  // 2. Try AI analysis
  if (!serviceType && state.visualIntakeAnalysis?.suggestedServiceType) {
    serviceType = state.visualIntakeAnalysis.suggestedServiceType;
  }
  
  // 3. Try Step 3 dropdown (if already filled)
  if (!serviceType && dom.serviceType?.value) {
    serviceType = dom.serviceType.value;
  }
  
  console.log('updateMeasurementUI - serviceType:', serviceType);
  
  const measurementType = getMeasurementType(serviceType);
  console.log('updateMeasurementUI - measurementType:', measurementType);
  
  // Get measurement sections
  const exteriorSection = document.getElementById('exteriorMeasurement');
  const interiorSection = document.getElementById('interiorMeasurement');
  const unitSection = document.getElementById('unitMeasurement');
  const measureSummary = document.querySelector('.measure-summary');
  
  if (!exteriorSection || !interiorSection || !unitSection) {
    console.error('Measurement sections not found!');
    return;
  }
  
  // Hide all sections first
  [exteriorSection, interiorSection, unitSection].forEach(section => {
    section.classList.add('is-hidden');
  });
  
  // Remove two-column class first
  [exteriorSection, interiorSection, unitSection].forEach(section => {
    section.classList.remove('two-column');
  });
  
  // Show appropriate section and update summary
  if (measurementType === 'exterior') {
    exteriorSection.classList.remove('is-hidden');
    // Single column layout for exterior (map only)
    if (measureSummary) {
      const summaryTitle = measureSummary.querySelector('.summary-title');
      if (summaryTitle) {
        summaryTitle.textContent = 'Measurement summary';
      }
      const includesList = measureSummary.querySelector('.summary-list');
      if (includesList) {
        includesList.innerHTML = `
          <li>Roof or system footprint</li>
          <li>Typical access points</li>
          <li>Local labor ranges</li>
        `;
      }
    }
  } else if (measurementType === 'interior') {
    interiorSection.classList.remove('is-hidden');
    interiorSection.classList.add('two-column'); // Two-column layout
    prefillInteriorMeasurements();
    if (measureSummary) {
      const summaryTitle = measureSummary.querySelector('.summary-title');
      if (summaryTitle) {
        summaryTitle.textContent = 'Project scope';
      }
      const includesList = measureSummary.querySelector('.summary-list');
      if (includesList) {
        includesList.innerHTML = `
          <li>Room-by-room pricing</li>
          <li>Material estimates</li>
          <li>Local labor ranges</li>
        `;
      }
    }
  } else if (measurementType === 'unit') {
    unitSection.classList.remove('is-hidden');
    unitSection.classList.add('two-column'); // Two-column layout
    prefillUnitMeasurements();
    if (measureSummary) {
      const summaryTitle = measureSummary.querySelector('.summary-title');
      if (summaryTitle) {
        summaryTitle.textContent = 'Project scope';
      }
      const includesList = measureSummary.querySelector('.summary-list');
      if (includesList) {
        includesList.innerHTML = `
          <li>Per-unit pricing</li>
          <li>Parts & materials</li>
          <li>Local labor ranges</li>
        `;
      }
    }
  } else if (measurementType === 'hybrid') {
    // Show both exterior and interior
    exteriorSection.classList.remove('is-hidden');
    interiorSection.classList.remove('is-hidden');
    exteriorSection.classList.add('two-column');
    interiorSection.classList.add('two-column');
  }
  
  console.log(`Measurement UI updated: ${measurementType} for ${serviceType}`);
}

function prefillInteriorMeasurements() {
  const analysis = state.visualIntakeAnalysis;
  const aiSuggestionBox = document.getElementById('interiorAiSuggestion');
  const aiDetailEl = document.getElementById('aiInteriorDetail');
  
  // If no analysis, hide the AI suggestion box
  if (!analysis) {
    if (aiSuggestionBox) aiSuggestionBox.classList.add('is-hidden');
    return;
  }
  
  const roomCountInput = document.getElementById('roomCount');
  const totalAreaInput = document.getElementById('totalArea');
  
  // Show AI suggestion box
  if (aiSuggestionBox) aiSuggestionBox.classList.remove('is-hidden');
  
  // Build dynamic AI suggestion text
  if (aiDetailEl) {
    const serviceName = analysis.serviceCategory || analysis.keyDetails?.serviceType || 'interior work';
    const rooms = analysis.keyDetails?.estimatedRoomCount || '';
    const area = analysis.keyDetails?.estimatedArea || '';
    
    let detailText = `${serviceName} detected`;
    const details = [];
    if (rooms) details.push(`${rooms} rooms`);
    if (area) details.push(`${area} area`);
    if (details.length > 0) {
      detailText += ` • ${details.join(', ')}`;
    }
    
    aiDetailEl.textContent = detailText;
  }
  
  // Pre-fill inputs if AI has data
  if (roomCountInput && analysis.keyDetails?.estimatedRoomCount) {
    const roomMatch = String(analysis.keyDetails.estimatedRoomCount).match(/(\d+)/);
    if (roomMatch) {
      roomCountInput.value = roomMatch[1];
    }
  }
  
  if (totalAreaInput && analysis.keyDetails?.estimatedArea) {
    const areaMatch = String(analysis.keyDetails.estimatedArea).match(/(\d+)/);
    if (areaMatch) {
      totalAreaInput.value = areaMatch[1];
    }
  }
}

function prefillUnitMeasurements() {
  const analysis = state.visualIntakeAnalysis;
  const aiSuggestionBox = document.getElementById('unitAiSuggestion');
  const aiDetailEl = document.getElementById('aiUnitDetail');
  
  // If no analysis, hide the AI suggestion box
  if (!analysis) {
    if (aiSuggestionBox) aiSuggestionBox.classList.add('is-hidden');
    return;
  }
  
  const unitCountInput = document.getElementById('unitCount');
  const unitHelper = document.getElementById('unitHelper');
  const serviceType = dom.serviceType?.value || '';
  
  // Show AI suggestion box
  if (aiSuggestionBox) aiSuggestionBox.classList.remove('is-hidden');
  
  // Update helper text based on service
  if (unitHelper) {
    if (serviceType.includes('plumbing')) {
      unitHelper.textContent = 'How many fixtures need service?';
    } else if (serviceType.includes('electrical') || serviceType.includes('lighting')) {
      unitHelper.textContent = 'How many fixtures/outlets need work?';
    } else if (serviceType.includes('appliance')) {
      unitHelper.textContent = 'How many appliances to install?';
    } else if (serviceType.includes('window') || serviceType.includes('door')) {
      unitHelper.textContent = 'How many units to replace?';
    }
  }
  
  // Build dynamic AI suggestion text
  if (aiDetailEl) {
    const serviceName = analysis.serviceCategory || analysis.keyDetails?.serviceType || 'units';
    const units = analysis.keyDetails?.estimatedUnitCount || '';
    
    let detailText = `${serviceName} detected`;
    if (units) {
      detailText += ` • approximately ${units} units`;
    }
    
    aiDetailEl.textContent = detailText;
  }
  
  // Pre-fill if AI has data
  if (unitCountInput && analysis.keyDetails?.estimatedUnitCount) {
    const unitMatch = String(analysis.keyDetails.estimatedUnitCount).match(/(\d+)/);
    if (unitMatch) {
      unitCountInput.value = unitMatch[1];
    }
  }
}

function setStep(step) {
  state.step = step;
  dom.stepPanels.forEach((panel) => {
    const panelStep = Number(panel.dataset.step);
    panel.classList.toggle("is-active", panelStep === step);
  });

  dom.stepList.forEach((item) => {
    const itemStep = Number(item.dataset.step);
    item.classList.toggle("is-active", itemStep === step);
    item.classList.toggle("is-complete", itemStep < step);
  });

  dom.mobileSteps.forEach((item) => {
    const itemStep = Number(item.dataset.step);
    item.classList.toggle("is-active", itemStep === step);
  });
  
  // Track step view in analytics
  const stepNames = ['visual_intake', 'address', 'property_view', 'project_details', 'estimate', 'schedule'];
  if (typeof EstimatorAnalytics !== 'undefined' && step >= 0 && step < stepNames.length) {
    EstimatorAnalytics.track(`${stepNames[step]}_viewed`, {
      step: stepNames[step],
      stepIndex: step
    });
  }

  if (step <= 5) {
    const stepData = steps[step];
    const displayStep = stepData.optional ? "Optional" : `Step ${step + 1} of 6`;
    const totalSteps = 6;
    
    dom.stageEyebrow.textContent = displayStep;
    dom.stageTitle.textContent = stepData.title;
    dom.stageSummary.textContent = stepData.summary;
    
    // Apply contractor context personalization after setting default text
    applyContractorContext();
    dom.softwareStep.textContent = displayStep;
    dom.softwareFill.style.width = `${((step + 1) / totalSteps) * 100}%`;
    dom.softwareHelper.textContent =
      step === 0 ? "Optional step" :
      step <= 2 ? "About 1 minute left" : step === 3 ? "About 45 seconds left" : "About 30 seconds left";
    
    // Fetch property snapshot on Property View step
    if (step === 2 && state.selectedAddress) {
      fetchPropertySnapshot();
      updateMeasurementUI(); // Show appropriate measurement form
    }
    
    // Populate photos and ensure service type/complexity on Project Details step
    if (step === 3) {
      populateExistingPhotos();
      // Ensure service type from contractor context or AI analysis is set
      if (state.visualIntakeAnalysis) {
        prefillSurveyFromAnalysis();
      }
      // Update scope options based on service type
      updateScopeOptions();
    }
    
    // Populate project recap and estimate delivery on Schedule step
    if (step === 5) {
      populateProjectRecap();
      setupEstimateDelivery();
    }
    
    // Show status cues
    if (step === 2 && state.selectedAddress) {
      dom.stageStatus.textContent = "Property details loaded";
      dom.stageStatus.classList.remove("is-hidden");
    } else if (step === 3 && state.selectedAddress) {
      dom.stageStatus.textContent = "Measurements reviewed";
      dom.stageStatus.classList.remove("is-hidden");
    } else if (step === 4) {
      dom.stageStatus.textContent = "Scope considered";
      dom.stageStatus.classList.remove("is-hidden");
    } else {
      dom.stageStatus.classList.add("is-hidden");
    }
  } else {
    dom.stageEyebrow.textContent = "Complete";
    dom.stageTitle.textContent = "Estimate submitted";
    dom.stageSummary.textContent = "We will review the details and confirm scope and pricing.";
    dom.softwareStep.textContent = "Complete";
    dom.softwareFill.style.width = "100%";
    dom.softwareHelper.textContent = "Submitted";
    dom.stageStatus.classList.add("is-hidden");
  }

  dom.backBtn.classList.toggle("is-hidden", step === 0 || step === 6);
  dom.nextBtn.textContent = step === 5 ? "Prepare this job" : "Next";
  dom.nextBtn.classList.toggle("is-hidden", step === 6);

  updateNextButton();
  saveSession();
}

function canAdvance() {
  if (state.step === 0) return true; // Visual Intake is optional
  if (state.step === 1) return Boolean(state.selectedAddress);
  if (state.step === 2) {
    // Property View: Can proceed if we have an address OR if manual measurements exist
    return Boolean(state.selectedAddress || state.measurements || state.measurementPolygon?.length > 0);
  }
  if (state.step === 3) return true;
  if (state.step === 4) return true;
  if (state.step === 5) return Boolean(state.selectedTime && dom.contactName.value.trim());
  return false;
}

function updateNextButton() {
  if (state.step === 0) {
    // Visual Intake: Can always continue, but better with photos
    dom.nextBtn.disabled = false;
    if (state.visualIntakePhotos.length > 0 && state.visualIntakeAnalysis) {
      dom.nextBtn.textContent = "Continue to address";
    } else {
      dom.nextBtn.textContent = "Continue to address";
    }
  } else if (state.step === 1) {
    dom.nextBtn.disabled = !state.selectedAddress;
    dom.nextBtn.textContent = "Continue to property";
  } else if (state.step === 2) {
    dom.nextBtn.disabled = !state.selectedAddress;
    dom.nextBtn.textContent = "Continue to details";
  } else if (state.step === 3) {
    dom.nextBtn.disabled = false;
    // Use contractor name if available for more personalized CTA
    const contractorContext = readContractorContext();
    if (contractorContext.contractor) {
      dom.nextBtn.textContent = "Get quote";
    } else {
      dom.nextBtn.textContent = "View estimate";
    }
  } else if (state.step === 4) {
    dom.nextBtn.disabled = false;
    dom.nextBtn.textContent = "Continue to schedule";
  } else if (state.step === 5) {
    dom.nextBtn.disabled = !(state.selectedTime && dom.contactName.value.trim());
    dom.nextBtn.textContent = "Prepare this job";
  }
}

async function showSuggestions(query) {
  dom.addressSuggestions.innerHTML = "";
  if (!query) {
    dom.addressSuggestions.classList.add("is-hidden");
    return;
  }

  if (CONFIG.enableGooglePlaces && hasGooglePlaces()) {
    try {
      // Use new AutocompleteSuggestion API (replaces legacy AutocompleteService)
      const request = {
        input: query,
        includedRegionCodes: ["US"]
        // Note: Not restricting by type to get all address suggestions
      };

      const { suggestions } = await window.google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request);

      if (!suggestions || suggestions.length === 0) {
        dom.addressSuggestions.classList.add("is-hidden");
        return;
      }

      suggestions.slice(0, 5).forEach((suggestion) => {
        const div = document.createElement("div");
        div.className = "suggestion-item";
        
        // New API structure - use text.text as primary, fallback to structured format
        const prediction = suggestion.placePrediction;
        const fullText = prediction?.text?.text || "";
        
        // Try to split into main and secondary parts
        let mainText = fullText;
        let secondaryText = "";
        
        if (prediction?.structuredFormat) {
          mainText = prediction.structuredFormat.mainText?.text || fullText;
          secondaryText = prediction.structuredFormat.secondaryText?.text || "";
        } else if (fullText.includes(",")) {
          // Fallback: split by first comma
          const parts = fullText.split(",");
          mainText = parts[0].trim();
          secondaryText = parts.slice(1).join(",").trim();
        }
        
        div.innerHTML = `<strong>${mainText}</strong><span>${secondaryText}</span>`;
        div.addEventListener("click", () => selectGoogleAddress(prediction));
        dom.addressSuggestions.appendChild(div);
      });
      dom.addressSuggestions.classList.remove("is-hidden");
      return;
    } catch (error) {
      console.error("Google Places autocomplete failed:", error);
      // Hide suggestions on error - don't show placeholder data
      dom.addressSuggestions.classList.add("is-hidden");
      return;
    }
  }

  // Fallback only used if Google Places is disabled
  dom.addressSuggestions.classList.add("is-hidden");
}

function formatCityState(addressComponents) {
  if (!addressComponents) return "";
  const city = addressComponents.find((item) => item.types.includes("locality"))?.long_name;
  const state = addressComponents.find((item) =>
    item.types.includes("administrative_area_level_1")
  )?.short_name;
  return [city, state].filter(Boolean).join(", ");
}

function renderPropertyFacts(facts) {
  if (!facts) {
    dom.propertyFacts.innerHTML = "";
    return;
  }
  const chips = [];
  if (facts.beds) chips.push(`${facts.beds} bed`);
  if (facts.baths) chips.push(`${facts.baths} bath`);
  if (facts.yearBuilt) chips.push(`Built ${facts.yearBuilt}`);
  if (facts.lotSize) chips.push(facts.lotSize);
  if (facts.sqft) chips.push(`${facts.sqft.toLocaleString()} sq ft`);
  dom.propertyFacts.innerHTML = chips.map((chip) => `<span class="property-fact">${chip}</span>`).join("");
}

async function selectGoogleAddress(placePrediction) {
  if (!placePrediction?.placeId && !placePrediction?.place) {
    console.error("No place ID available");
    return;
  }
  
  try {
    // Use new Place API (replaces legacy PlacesService)
    const placeId = placePrediction.placeId || placePrediction.place;
    const place = new window.google.maps.places.Place({
      id: placeId
    });

    // Fetch place details
    await place.fetchFields({
      fields: ["displayName", "formattedAddress", "location", "addressComponents"]
    });

    const address = {
      address: place.formattedAddress.split(",")[0],
      city: formatCityStateFromComponents(place.addressComponents),
      formattedAddress: place.formattedAddress,
      lat: place.location.lat(),
      lng: place.location.lng(),
      placeId: placeId
    };
    setAddress(address, { autoAdvance: true });
  } catch (error) {
    console.error("Failed to fetch place details:", error);
  }
}

function formatCityStateFromComponents(addressComponents) {
  if (!addressComponents) return "";
  let city = "";
  let state = "";
  
  addressComponents.forEach((component) => {
    if (component.types.includes("locality")) {
      city = component.longText;
    }
    if (component.types.includes("administrative_area_level_1")) {
      state = component.shortText;
    }
  });
  
  return [city, state].filter(Boolean).join(", ");
}

async function setAddress(address, { autoAdvance, skipAuto } = {}) {
  state.selectedAddress = address;
  dom.addressInput.value = address.formattedAddress || `${address.address}, ${address.city}`;
  dom.addressSuggestions.classList.add("is-hidden");
  dom.propertyPreview.classList.remove("is-hidden");
  dom.propertySkeleton.classList.remove("is-hidden");
  dom.softwareContext.textContent = `Estimate for ${address.address}`;
  document.body.classList.add("address-confirmed");

  storeAddress(address);
  saveSession();

  if (hasGoogleMaps()) {
    initMap(mapLatLng(address.lat, address.lng));
  }

  let facts = state.propertyFacts || (await fetchPropertyFacts(address));
  if (!facts) {
    const fallback = addressData.find((item) => item.address === address.address);
    if (fallback) {
      facts = {
        beds: fallback.beds,
        baths: fallback.baths,
        yearBuilt: fallback.year,
        lotSize: fallback.lot,
        sqft: fallback.area
      };
    }
  }
  state.propertyFacts = facts;
  if (facts) {
    storePropertyFacts(facts);
  }

  window.setTimeout(() => {
    dom.propertySkeleton.classList.add("is-hidden");
    dom.propertyAddress.textContent = address.address;
    dom.propertyCity.textContent = address.city;
    renderPropertyFacts(facts);
    dom.propertyPreview.classList.add("is-active");
    if (autoAdvance) {
      setStep(2);
    }
  }, 200);

  updateNextButton();
  if (!skipAuto && state.measurementMode === "auto") {
    applyAutoMeasurements();
  }
}

function loadSavedAddress() {
  const saved = loadFromStorage(STORAGE_KEYS.address);
  if (!saved) return;
  const storedFacts = loadFromStorage(STORAGE_KEYS.propertyFacts);
  if (storedFacts) {
    state.propertyFacts = storedFacts;
  }
  return setAddress(saved, { autoAdvance: false, skipAuto: true });
}

function restoreMeasurements() {
  const stored = loadFromStorage(STORAGE_KEYS.measurements);
  if (!stored) return;
  updateMeasurements(stored.area, stored.perimeter, stored.confidence, stored.source);
}

function restorePolygon() {
  const polygon = loadFromStorage(STORAGE_KEYS.measurementPolygon);
  if (!polygon?.points?.length) return;
  state.drawPoints = polygon.points;
  state.drawSource = polygon.source || "draw";
  state.drawComplete = true;
  if (polygon.type === "latlng" && hasGoogleMaps()) {
    renderMapDrawPath(state.drawPoints, true);
  } else if (polygon.type === "pixel") {
    drawOutline();
  } else {
    const rectWidth = dom.drawCanvas.width * 0.55;
    const rectHeight = dom.drawCanvas.height * 0.35;
    const cx = dom.drawCanvas.width / 2;
    const cy = dom.drawCanvas.height / 2;
    state.drawPoints = [
      { x: cx - rectWidth / 2, y: cy - rectHeight / 2 },
      { x: cx + rectWidth / 2, y: cy - rectHeight / 2 },
      { x: cx + rectWidth / 2, y: cy + rectHeight / 2 },
      { x: cx - rectWidth / 2, y: cy + rectHeight / 2 }
    ];
    drawOutline();
  }
}

function updateMeasurements(area, perimeter, confidence, source) {
  console.log("📏 Updating measurements:", { area, perimeter, confidence, source });
  
  const measurements = {
    area,
    perimeter,
    confidence,
    source: source || state.drawSource
  };
  state.measurements = measurements;
  storeMeasurements(measurements);
  
  if (dom.summaryArea) {
    dom.summaryArea.textContent = `${area.toLocaleString()} sq ft`;
    console.log("✅ Updated area display");
  } else {
    console.error("❌ summaryArea element not found!");
  }
  
  if (dom.summaryPerimeter) {
    dom.summaryPerimeter.textContent = `${perimeter.toLocaleString()} ft`;
    console.log("✅ Updated perimeter display");
  } else {
    console.error("❌ summaryPerimeter element not found!");
  }
  
  if (dom.summaryConfidence) {
    dom.summaryConfidence.textContent = confidence;
    console.log("✅ Updated confidence display");
  } else {
    console.error("❌ summaryConfidence element not found!");
  }
}

function setMeasurementMode(mode) {
  state.measurementMode = mode;
  storeMeasurementMode(mode);
  saveSession();
  
  // Drawing UI removed - always use auto measurements
  // Apply auto measurements if we have an address
  if (state.selectedAddress && mode === "auto") {
    applyAutoMeasurements();
  }
}

function enableDrawMode() {
  state.drawComplete = false;
  state.drawPoints = [];
  if (hasGoogleMaps() && state.map) {
    clearMapShapes();
    if (state.mapClickListener) {
      window.google.maps.event.removeListener(state.mapClickListener);
    }
    if (state.mapDoubleClickListener) {
      window.google.maps.event.removeListener(state.mapDoubleClickListener);
    }
    state.mapClickListener = state.map.addListener("click", (event) => {
      state.drawPoints.push({ lat: event.latLng.lat(), lng: event.latLng.lng() });
      renderMapDrawPath(state.drawPoints, false);
      storePolygon({ type: "latlng", points: state.drawPoints, source: "draw" });
    });
    state.mapDoubleClickListener = state.map.addListener("dblclick", () => finalizeDraw());
  }
}

function disableDrawMode() {
  if (state.mapClickListener) {
    window.google.maps.event.removeListener(state.mapClickListener);
    state.mapClickListener = null;
  }
  if (state.mapDoubleClickListener) {
    window.google.maps.event.removeListener(state.mapDoubleClickListener);
    state.mapDoubleClickListener = null;
  }
}

async function applyAutoMeasurements() {
  if (!state.selectedAddress) return;
  if (!state.selectedAddress.lat || !state.selectedAddress.lng) {
    const fallback = addressData.find((item) => item.address === state.selectedAddress.address);
    if (fallback) {
      updateMeasurements(fallback.area, fallback.perimeter, "Low", "default");
    }
    return;
  }
  if (!hasGoogleMaps()) {
    const rectWidth = dom.drawCanvas.width * 0.55;
    const rectHeight = dom.drawCanvas.height * 0.35;
    const cx = dom.drawCanvas.width / 2;
    const cy = dom.drawCanvas.height / 2;
    state.drawPoints = [
      { x: cx - rectWidth / 2, y: cy - rectHeight / 2 },
      { x: cx + rectWidth / 2, y: cy - rectHeight / 2 },
      { x: cx + rectWidth / 2, y: cy + rectHeight / 2 },
      { x: cx - rectWidth / 2, y: cy + rectHeight / 2 }
    ];
    state.drawComplete = true;
    drawOutline();
    const baseArea = state.selectedAddress.area || 2200;
    const widthFt = Math.sqrt(baseArea) * 1.4;
    const heightFt = widthFt * 0.75;
    const measurements = computeMeasurementsFromPixels(state.drawPoints, widthFt, heightFt);
    updateMeasurements(measurements.area, measurements.perimeter, "Medium", "fallback");
    storePolygon({ type: "pixel", points: state.drawPoints, source: "fallback" });
    return;
  }
  const outlineResponse = await fetchOutline(state.selectedAddress);
  const outlinePoints =
    outlineResponse?.points || outlineResponse?.outline?.points || outlineResponse?.polygon?.points;
  if (outlinePoints?.length) {
    state.drawPoints = outlinePoints;
    state.drawComplete = true;
    state.drawSource = "solar";
    renderMapDrawPath(state.drawPoints, true);
    const measurements = computeMeasurementsWithFallback(state.drawPoints);
    updateMeasurements(measurements.area, measurements.perimeter, "High", "solar");
    storePolygon({ type: "latlng", points: state.drawPoints, source: "solar" });
    return;
  }
  const fallbackPoints = createFallbackOutline(state.selectedAddress);
  state.drawPoints = fallbackPoints;
  state.drawComplete = true;
  state.drawSource = "fallback";
  renderMapDrawPath(state.drawPoints, true);
  const measurements = computeMeasurementsWithFallback(state.drawPoints);
  updateMeasurements(measurements.area, measurements.perimeter, "Medium", "fallback");
  storePolygon({ type: "latlng", points: state.drawPoints, source: "fallback" });
}

function createFallbackOutline(address) {
  const lat = address.lat || 30.2672;
  const lng = address.lng || -97.7431;
  const meters = 22;
  const latOffset = meters / 111111;
  const lngOffset = meters / (111111 * Math.cos((lat * Math.PI) / 180));
  return [
    { lat: lat + latOffset, lng: lng - lngOffset },
    { lat: lat + latOffset, lng: lng + lngOffset },
    { lat: lat - latOffset, lng: lng + lngOffset },
    { lat: lat - latOffset, lng: lng - lngOffset }
  ];
}

function computeMeasurementsWithFallback(points) {
  if (hasGoogleMaps() && window.google?.maps?.geometry) {
    const mapped = points.map((point) => {
      if (typeof point.lat === "function") return point;
      return mapLatLng(point.lat, point.lng);
    });
    return computeMeasurementsFromLatLng(mapped);
  }
  return computeMeasurementsFromLatLngFallback(points);
}

function computeMeasurementsFromLatLngFallback(points) {
  if (points.length < 3) {
    return { area: 0, perimeter: 0 };
  }
  const origin = points[0];
  const converted = points.map((point) => {
    const latMeters = (point.lat - origin.lat) * 111111;
    const lngMeters = (point.lng - origin.lng) * 111111 * Math.cos((origin.lat * Math.PI) / 180);
    return { x: lngMeters * 3.28084, y: latMeters * 3.28084 };
  });
  let area = 0;
  let perimeter = 0;
  for (let i = 0; i < converted.length; i += 1) {
    const curr = converted[i];
    const next = converted[(i + 1) % converted.length];
    area += curr.x * next.y - next.x * curr.y;
    perimeter += Math.hypot(next.x - curr.x, next.y - curr.y);
  }
  return { area: Math.round(Math.abs(area) / 2), perimeter: Math.round(perimeter) };
}

function finalizeDraw() {
  console.log("🎨 Finalizing draw with", state.drawPoints.length, "points");
  
  if (state.drawPoints.length < 3) {
    console.warn("⚠️ Need at least 3 points to finalize. Current:", state.drawPoints.length);
    return;
  }
  
  state.drawComplete = true;
  
  if (hasGoogleMaps() && state.map) {
    console.log("✅ Using Google Maps for measurements");
    renderMapDrawPath(state.drawPoints, true);
    const measurements = computeMeasurementsWithFallback(state.drawPoints);
    console.log("📊 Calculated measurements:", measurements);
    updateMeasurements(measurements.area, measurements.perimeter, "High", "draw");
    storePolygon({ type: "latlng", points: state.drawPoints, source: "draw" });
  } else {
    console.log("⚠️ Using fallback measurements (no Google Maps)");
    const baseArea = state.selectedAddress?.area || 2200;
    const widthFt = Math.sqrt(baseArea) * 1.4;
    const heightFt = widthFt * 0.75;
    const measurements = computeMeasurementsFromPixels(state.drawPoints, widthFt, heightFt);
    console.log("📊 Calculated measurements (fallback):", measurements);
    updateMeasurements(measurements.area, measurements.perimeter, "High", "draw");
    storePolygon({ type: "pixel", points: state.drawPoints, source: "draw" });
  }
}

function clearCanvas() {
  const ctx = dom.drawCanvas.getContext("2d");
  ctx.clearRect(0, 0, dom.drawCanvas.width, dom.drawCanvas.height);
}

function drawOutline() {
  clearCanvas();
  const ctx = dom.drawCanvas.getContext("2d");
  if (state.drawPoints.length === 0) return;

  ctx.strokeStyle = "#ff503e";
  ctx.lineWidth = 2;
  ctx.fillStyle = "rgba(255, 80, 62, 0.2)";
  ctx.beginPath();
  state.drawPoints.forEach((point, index) => {
    if (index === 0) {
      ctx.moveTo(point.x, point.y);
    } else {
      ctx.lineTo(point.x, point.y);
    }
  });
  if (state.drawComplete) {
    ctx.closePath();
    ctx.fill();
  }
  ctx.stroke();
}

function handleCanvasClick(event) {
  if (state.measurementMode !== "draw") return;
  const rect = dom.drawCanvas.getBoundingClientRect();
  const point = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  };
  state.drawPoints.push(point);
  drawOutline();
  storePolygon({ type: "pixel", points: state.drawPoints, source: "draw" });
}

function handleCanvasDoubleClick() {
  if (state.measurementMode !== "draw" || state.drawPoints.length < 3) return;
  finalizeDraw();
}

function resetOutline() {
  state.drawPoints = [];
  state.drawComplete = false;
  drawOutline();
  clearMapShapes();
  if (state.selectedAddress && state.measurementMode === "auto") {
    applyAutoMeasurements();
  }
}

function undoOutline() {
  if (state.drawPoints.length === 0) return;
  state.drawPoints.pop();
  state.drawComplete = false;
  if (hasGoogleMaps()) {
    renderMapDrawPath(state.drawPoints, false);
  } else {
    drawOutline();
  }
  storePolygon({ type: hasGoogleMaps() ? "latlng" : "pixel", points: state.drawPoints, source: "draw" });
}

function resizeCanvas() {
  // Canvas removed - no longer needed
  return;
}

function setupUrgencyChips() {
  const buttons = document.querySelectorAll(".choice-chip");
  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      buttons.forEach((chip) => chip.classList.remove("is-active"));
      btn.classList.add("is-active");
      state.urgency = btn.dataset.value || "soon";
    });
  });
}

function addPhotos(files) {
  const newPhotos = Array.from(files).slice(0, 6 - state.photos.length);
  newPhotos.forEach((file) => {
    state.photos.push(file);
  });
  renderPhotos();
}

function renderPhotos() {
  dom.photoThumbs.innerHTML = "";
  state.photos.forEach((file, index) => {
    // Handle both File objects and base64 strings (from localStorage/Visual Intake)
    const url = typeof file === 'string' ? file : URL.createObjectURL(file);
    const wrapper = document.createElement("div");
    wrapper.className = "photo-thumb";
    wrapper.innerHTML = `
      <img src="${url}" alt="Uploaded photo ${index + 1}" width="200" height="150" loading="lazy" />
      <button class="thumb-remove" data-index="${index}" type="button">
        <i class="fa-solid fa-xmark"></i>
      </button>
    `;
    dom.photoThumbs.appendChild(wrapper);
  });

  dom.photoThumbs.querySelectorAll(".thumb-remove").forEach((btn) => {
    btn.addEventListener("click", () => {
      const idx = Number(btn.dataset.index);
      state.photos.splice(idx, 1);
      renderPhotos();
    });
  });
}

function setupPhotoUpload() {
  // Populate existing photos grid
  populateExistingPhotos();
  
  // Toggle upload section
  const toggleBtn = document.getElementById('togglePhotoUploadBtn');
  const uploadSection = document.getElementById('uploadSection');
  
  if (toggleBtn && uploadSection) {
    toggleBtn.addEventListener('click', () => {
      const isHidden = uploadSection.classList.contains('is-hidden');
      uploadSection.classList.toggle('is-hidden');
      toggleBtn.textContent = isHidden 
        ? '− Hide upload section' 
        : '+ Add more photos (optional)';
    });
  }
  
  // Keep existing drag and drop functionality
  dom.photoDrop.addEventListener("dragover", (event) => {
    event.preventDefault();
    dom.photoDrop.classList.add("is-dragging");
  });

  dom.photoDrop.addEventListener("dragleave", () => {
    dom.photoDrop.classList.remove("is-dragging");
  });

  dom.photoDrop.addEventListener("drop", (event) => {
    event.preventDefault();
    dom.photoDrop.classList.remove("is-dragging");
    if (event.dataTransfer?.files) {
      addPhotos(event.dataTransfer.files);
      // Re-populate existing photos after adding new ones
      populateExistingPhotos();
    }
  });

  dom.photoInput.addEventListener("change", (event) => {
    addPhotos(event.target.files);
    // Re-populate existing photos after adding new ones
    populateExistingPhotos();
  });
}

function populateExistingPhotos() {
  const container = document.getElementById('existingPhotos');
  if (!container) return;
  
  container.innerHTML = '';
  const photos = [];
  
  // Add Visual Intake photos
  if (state.visualIntakePhotos && state.visualIntakePhotos.length > 0) {
    state.visualIntakePhotos.forEach((photo, index) => {
      photos.push({
        src: photo,
        label: 'Interior'
      });
    });
  }
  
  // Add Google Property Snapshot images
  if (state.propertySnapshot) {
    if (state.propertySnapshot.streetViewImageUrl) {
      photos.push({
        src: state.propertySnapshot.streetViewImageUrl,
        label: 'Street View'
      });
    }
    if (state.propertySnapshot.satelliteImageUrl) {
      photos.push({
        src: state.propertySnapshot.satelliteImageUrl,
        label: 'Satellite'
      });
    }
  }
  
  // Add newly uploaded photos from this step
  if (state.photos && state.photos.length > 0) {
    state.photos.forEach((photo, index) => {
      // Only add if not already from Visual Intake
      const photoSrc = typeof photo === 'string' ? photo : URL.createObjectURL(photo);
      if (!state.visualIntakePhotos || !state.visualIntakePhotos.includes(photoSrc)) {
        photos.push({
          src: photoSrc,
          label: 'Additional'
        });
      }
    });
  }
  
  // Render photos
  if (photos.length > 0) {
    photos.forEach(photo => {
      const item = document.createElement('div');
      item.className = 'existing-photo-item';
      item.innerHTML = `
        <img src="${photo.src}" alt="${photo.label}" class="existing-photo-img" width="200" height="150" loading="lazy" />
        <div class="existing-photo-label">${photo.label}</div>
      `;
      container.appendChild(item);
    });
  } else {
    // Show placeholder if no photos
    container.innerHTML = `
      <div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #9ca3af; font-size: 13px;">
        No photos added yet. Add photos to help confirm pricing.
      </div>
    `;
  }
}

function buildEstimate() {
  if (!state.selectedAddress) return;
  dom.estimateSkeleton.classList.remove("is-hidden");
  dom.estimateGrid.classList.add("is-hidden");
  dom.estimateBasedOn.textContent =
    "Property size, local labor ranges, and scope details you provided.";

  window.setTimeout(() => {
    const base = estimateBasePrice();
    dom.priceGood.textContent = formatRange(base * 0.9, base * 1.05);
    dom.priceBetter.textContent = formatRange(base * 1.05, base * 1.25);
    dom.priceBest.textContent = formatRange(base * 1.25, base * 1.5);
    
    const confidence = state.measurements?.confidence || "Medium";
    dom.estimateConfidence.textContent = confidence === "High" ? "High" : confidence === "Medium" ? "Medium" : "Based on available details";
    
    dom.estimateChanges.textContent =
      "Access conditions, material choices, or hidden issues.";
    
    // Update dynamic estimate verbiage based on service type and scope
    updateEstimateVerbiage();
    
    dom.estimateSkeleton.classList.add("is-hidden");
    dom.estimateGrid.classList.remove("is-hidden");
  }, 500);
}

function estimateBasePrice() {
  const service = dom.serviceType?.value || 'general-repair';
  const scope = dom.scopeType?.value || 'repair';
  const complexity = dom.scopeLevel?.value || 'standard';
  const measurementType = getMeasurementType(service);
  
  // Get area based on measurement type
  let area = 2000; // Default fallback
  let quantity = 1; // For unit-based services
  
  if (measurementType === 'exterior') {
    // Use property measurements
    area = state.measurements?.area || state.selectedAddress?.area || 2000;
  } else if (measurementType === 'interior') {
    // Use interior measurements
    const roomCount = parseInt(document.getElementById('roomCount')?.value || '0');
    const totalArea = parseInt(document.getElementById('totalArea')?.value || '0');
    
    if (totalArea > 0) {
      area = totalArea;
    } else if (roomCount > 0) {
      // Estimate area based on room count (avg 150 sq ft per room)
      area = roomCount * 150;
    } else {
      area = 400; // Default for interior services
    }
  } else if (measurementType === 'unit') {
    // Use unit count
    quantity = parseInt(document.getElementById('unitCount')?.value || '1');
    area = quantity * 100; // Convert units to equivalent area for pricing
  } else if (measurementType === 'hybrid') {
    // Use both property + interior
    const roomCount = parseInt(document.getElementById('roomCount')?.value || '0');
    area = state.measurements?.area || state.selectedAddress?.area || 2000;
    if (roomCount > 0) {
      area = Math.min(area, roomCount * 200); // Cap based on interior rooms
    }
  }
  
  // Base rates per sq ft for each specific service type
  const baseRates = {
    // Roofing
    'roof-repair': 3.2,
    'roof-replacement': 4.5,
    'gutter-cleaning': 0.8,
    'gutter-repair': 1.5,
    'chimney-repair': 2.0,
    
    // HVAC
    'hvac-repair': 2.5,
    'hvac-installation': 3.8,
    'hvac-maintenance': 1.2,
    'duct-cleaning': 0.9,
    
    // Plumbing
    'plumbing-repair': 2.2,
    'plumbing-installation': 2.8,
    'water-heater': 3.0,
    'drain-cleaning': 1.5,
    'leak-repair': 2.0,
    
    // Electrical
    'electrical-repair': 2.0,
    'electrical-installation': 2.5,
    'panel-upgrade': 3.5,
    'lighting-installation': 1.8,
    
    // Flooring & Carpet
    'carpet-cleaning': 0.5,
    'carpet-replacement': 3.5,
    'flooring-installation': 4.0,
    'flooring-repair': 2.2,
    'tile-work': 3.8,
    
    // Painting & Drywall
    'interior-painting': 2.0,
    'exterior-painting': 2.8,
    'drywall-repair': 1.8,
    'texture-repair': 1.5,
    
    // Exterior
    'siding-repair': 2.5,
    'siding-installation': 4.2,
    'window-replacement': 3.5,
    'door-installation': 2.8,
    'fence-repair': 2.0,
    'deck-repair': 2.5,
    
    // Remediation
    'mold-remediation': 3.5,
    'water-damage': 4.0,
    'air-quality': 1.5,
    'insulation': 2.2,
    
    // Outdoor
    'landscaping': 1.8,
    'tree-removal': 2.5,
    'concrete-work': 3.0,
    'foundation-repair': 5.0,
    
    // Other
    'appliance-installation': 1.5,
    'general-repair': 1.8,
    'handyman': 1.5
  };
  
  const scopeMultiplier = {
    'service': 0.6,        // Service/maintenance is cheaper
    'repair': 0.85,
    'replace': 1.0,
    'install': 1.15
  };
  
  const levelMultiplier = {
    'standard': 1.0,
    'elevated': 1.12,
    'premium': 1.24
  };
  
  const urgencyMultiplier = {
    'soon': 1.0,
    'this-week': 1.05,
    'flexible': 0.95
  };

  // Get rate for specific service type (with fallback)
  const rate = baseRates[service] || 1.8;
  
  // Get multipliers (with fallbacks)
  const scopeMult = scopeMultiplier[scope] || 1.0;
  const levelMult = levelMultiplier[complexity] || 1.0;
  const urgencyMult = urgencyMultiplier[state.urgency] || 1.0;
  
  // Calculate base price
  const base = area * rate * scopeMult * levelMult * urgencyMult;
  
  // Add minimum price based on service type
  const minimumPrice = service.includes('cleaning') ? 150 : 
                       service.includes('maintenance') ? 200 :
                       service.includes('installation') || service.includes('replacement') ? 800 :
                       500;
  
  return Math.max(Math.round(base), minimumPrice);
}

function formatRange(min, max) {
  const minRounded = Math.round(min / 50) * 50;
  const maxRounded = Math.round(max / 50) * 50;
  return `$${minRounded.toLocaleString()} to $${maxRounded.toLocaleString()}`;
}

function setupTimeChips() {
  // Mock CRM availability - in production, this would fetch from contractor's calendar API
  const mockAvailability = [
    { time: "Today 2:00 PM", available: true },
    { time: "Today 4:30 PM", available: true },
    { time: "Tomorrow 9:00 AM", available: true },
    { time: "Tomorrow 1:30 PM", available: false }, // Not available - won't show
    { time: "Thursday 10:00 AM", available: true },
    { time: "Friday 3:00 PM", available: true }
  ];
  
  // Filter only available times
  const availableTimes = mockAvailability.filter(t => t.available);
  
  dom.timeChips.innerHTML = "";
  availableTimes.forEach(({ time }) => {
    const chip = document.createElement("button");
    chip.className = "chip";
    chip.type = "button";
    chip.textContent = time;
    chip.addEventListener("click", () => {
      state.selectedTime = time;
      state.customTime = null; // Clear custom time when preset is selected
      dom.timeChips.querySelectorAll(".chip").forEach((c) => c.classList.remove("is-active"));
      chip.classList.add("is-active");
      updateNextButton();
      saveSession();
    });
    dom.timeChips.appendChild(chip);
  });
  
  // Show CRM indicator
  const crmIndicator = document.getElementById('crmIndicator');
  if (crmIndicator) {
    crmIndicator.classList.remove('is-hidden');
  }
}

function setupCustomTimePicker() {
  const toggleBtn = document.getElementById('toggleCustomTimeBtn');
  const customSection = document.getElementById('customTimeSection');
  const customDate = document.getElementById('customDate');
  const customTime = document.getElementById('customTime');
  
  if (!toggleBtn || !customSection) return;
  
  // Set minimum date to today
  if (customDate) {
    const today = new Date().toISOString().split('T')[0];
    customDate.setAttribute('min', today);
  }
  
  // Toggle custom time section
  toggleBtn.addEventListener('click', () => {
    const isHidden = customSection.classList.contains('is-hidden');
    customSection.classList.toggle('is-hidden');
    toggleBtn.textContent = isHidden ? 
      '← back to recommended times' : 
      'or choose a different date/time →';
  });
  
  // Handle custom date/time selection
  const handleCustomTime = () => {
    if (customDate?.value && customTime?.value) {
      const date = new Date(customDate.value);
      const timeStr = customTime.options[customTime.selectedIndex].text;
      const dateStr = date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
      
      state.selectedTime = `${dateStr} at ${timeStr}`;
      state.customTime = {
        date: customDate.value,
        time: customTime.value
      };
      
      // Clear preset chip selections
      dom.timeChips.querySelectorAll(".chip").forEach((c) => c.classList.remove("is-active"));
      
      // Visual feedback
      toggleBtn.textContent = `✓ Selected: ${dateStr} at ${timeStr}`;
      toggleBtn.style.color = '#10b981';
      
      updateNextButton();
      saveSession();
    }
  };
  
  if (customDate) customDate.addEventListener('change', handleCustomTime);
  if (customTime) customTime.addEventListener('change', handleCustomTime);
}

function createPendingJob() {
  if (!state.selectedAddress) return;
  
  const jobId = `job-${Date.now()}`;
  const pendingJob = {
    jobId,
    address: state.selectedAddress.address,
    city: state.selectedAddress.city,
    propertyContext: {
      beds: state.selectedAddress.beds,
      baths: state.selectedAddress.baths,
      year: state.selectedAddress.year,
      lot: state.selectedAddress.lot,
      area: state.measurements?.area || state.selectedAddress.area
    },
    estimateRange: {
      good: dom.priceGood.textContent,
      better: dom.priceBetter.textContent,
      best: dom.priceBest.textContent
    },
    scopeSummary: {
      service: dom.serviceType.value,
      scope: dom.scopeType.value,
      level: dom.scopeLevel.value,
      urgency: state.urgency,
      notes: dom.notes.value
    },
    status: "Pending confirmation",
    source: "Online estimate",
    timestamp: Date.now(),
    contactInfo: {
      name: dom.contactName.value,
      phone: dom.contactPhone.value,
      email: dom.contactEmail.value
    },
    // Visual Intake data
    photos: state.visualIntakePhotos || [],
    visualIntakeContext: state.visualIntakeAnalysis ? {
      aiAnalysis: state.visualIntakeAnalysis,
      userEdited: dom.reviewDescription?.value !== state.visualIntakeAnalysis.draftProjectDescription,
      photosCount: state.visualIntakePhotos.length
    } : null
  };
  
  const existing = loadFromStorage(STORAGE_KEY_PENDING_JOBS) || [];
  existing.push(pendingJob);
  saveToStorage(STORAGE_KEY_PENDING_JOBS, existing);
  
  // Create request in dashboard system
  if (typeof RequestsManager !== 'undefined') {
    const contractorContext = readContractorContext();
    const requestsManager = RequestsManager.getInstance();
    
    const estimatorData = {
      address: {
        full: state.selectedAddress.address,
        street: state.selectedAddress.address,
        city: state.selectedAddress.city,
        state: state.selectedAddress.state || '',
        zip: state.selectedAddress.zip || '',
        coords: {
          lat: state.selectedAddress.lat,
          lng: state.selectedAddress.lng
        }
      },
      serviceType: dom.serviceType.value,
      scope: dom.scopeType.value,
      complexity: dom.scopeLevel.value,
      aiDescription: state.visualIntakeAnalysis?.draftProjectDescription || '',
      estimate: {
        tier: state.selectedEstimateTier || 'better',
        amount: parseInt(dom.priceBetter.textContent.replace(/[^0-9]/g, '')) || 0,
        range: `${dom.priceGood.textContent}-${dom.priceBest.textContent}`,
        confidence: state.measurements?.confidence || 'Medium'
      },
      schedule: {
        date: state.selectedTime?.date || '',
        time: state.selectedTime?.time || '',
        type: state.selectedTime?.type || 'recommended',
        formatted: state.selectedTime?.formatted || ''
      },
      property: {
        measurements: state.measurements || {},
        photos: state.visualIntakePhotos || [],
        aiAnalysis: state.visualIntakeAnalysis || null
      },
      contractor: {
        id: contractorContext.contractor || null,
        name: contractorContext.companyName || null
      },
      completionTime: 0 // Will be calculated by analytics
    };
    
    const request = requestsManager.createRequest(estimatorData);
    
    // Complete analytics session with request ID
    if (typeof EstimatorAnalytics !== 'undefined' && request) {
      EstimatorAnalytics.complete(request.requestId);
    }
  }
  
  // Notify parent window (if in iframe) that estimate is completed
  if (window.parent !== window) {
    window.parent.postMessage({
      type: 'estimateCompleted',
      jobId: jobId
    }, '*');
  }
  
  return pendingJob;
}

function handleNavigation() {
  dom.backBtn.addEventListener("click", () => {
    if (state.step > 0) {
      setStep(state.step - 1);
    }
  });

  dom.nextBtn.addEventListener("click", () => {
    // Track step completion
    const stepNames = ['visual_intake', 'address', 'property_view', 'project_details', 'estimate', 'schedule'];
    const currentStepName = stepNames[state.step];
    
    // Step 0: Visual Intake -> Address
    if (state.step === 0) {
      // Track visual intake completion
      if (typeof EstimatorAnalytics !== 'undefined') {
        const hasPhotos = state.visualIntakePhotos && state.visualIntakePhotos.length > 0;
        const eventType = hasPhotos ? 'visual_intake_completed' : 'visual_intake_skipped';
        EstimatorAnalytics.track(eventType, {
          step: currentStepName,
          stepIndex: state.step
        });
      }
      
      prefillSurveyFromAnalysis();
      setStep(1);
      return;
    }
    
    // Track completion for other steps
    if (typeof EstimatorAnalytics !== 'undefined' && currentStepName) {
      EstimatorAnalytics.track(`${currentStepName}_completed`, {
        step: currentStepName,
        stepIndex: state.step
      });
    }
    
    // Step 3: Build estimate
    if (state.step === 3) {
      buildEstimate();
    }
    
    // Step 5: Create pending job and show confirmation
    if (state.step === 5) {
      createPendingJob();
      
      // Track request submission
      if (typeof EstimatorAnalytics !== 'undefined') {
        EstimatorAnalytics.track('request_submitted', {
          step: 'schedule',
          stepIndex: 5
        });
      }
      
      setStep(6);
      return;
    }
    
    // Regular navigation
    if (state.step < 5) {
      setStep(state.step + 1);
    }
  });
}

function setupMobileStepper() {
  dom.mobileSteps.forEach((stepBtn) => {
    stepBtn.addEventListener("click", () => {
      const target = Number(stepBtn.dataset.step);
      if (target === state.step) return;
      if (target > state.step && !canAdvance()) return;
      setStep(target);
    });
  });
}

function setupSwipeNavigation() {
  const swipeTarget = document.querySelector(".stage-card");
  if (!swipeTarget) return;

  let startX = 0;
  let startY = 0;
  let tracking = false;

  swipeTarget.addEventListener("touchstart", (event) => {
    if (event.touches.length !== 1) return;
    tracking = true;
    startX = event.touches[0].clientX;
    startY = event.touches[0].clientY;
  });

  swipeTarget.addEventListener("touchend", (event) => {
    if (!tracking) return;
    tracking = false;
    const endX = event.changedTouches[0].clientX;
    const endY = event.changedTouches[0].clientY;
    const diffX = endX - startX;
    const diffY = Math.abs(endY - startY);

    if (Math.abs(diffX) < 50 || diffY > 80) return;
    if (diffX < 0 && state.step < 5 && canAdvance()) {
      setStep(state.step + 1);
    }
    if (diffX > 0 && state.step > 1) {
      setStep(state.step - 1);
    }
  });
}

function setupAddressHandlers() {
  dom.addressInput.addEventListener("input", (event) => {
    showSuggestions(event.target.value);
  });

  dom.addressInput.addEventListener("focus", (event) => {
    showSuggestions(event.target.value);
  });

  document.addEventListener("click", (event) => {
    if (!dom.addressSuggestions.contains(event.target) && event.target !== dom.addressInput) {
      dom.addressSuggestions.classList.add("is-hidden");
    }
  });
}

function setupMeasurementHandlers() {
  // Drawing functionality removed for simpler UX
  // Auto-measurements are default, manual override available in UI
  // No event listeners needed
}

function setupContactInputs() {
  [dom.contactName, dom.contactPhone, dom.contactEmail].forEach((input) => {
    input.addEventListener("input", updateNextButton);
  });
}

function setupStartOver() {
  dom.startOverBtn.addEventListener("click", () => {
    state.step = 1;
    document.body.classList.remove("address-confirmed");
    setStep(1);
  });
}

function setupGlobalClose() {
  const closeBtn = document.getElementById('globalCloseBtn');
  if (!closeBtn) return;
  
  closeBtn.addEventListener('click', () => {
    // Directory click-through is disabled sitewide — return to demo, not /directory.
    if (window.history.length > 1) {
      window.history.back();
    } else {
      window.location.href = `${baseUrl}/demo/`;
    }
  });
}

function setupClearData() {
  const clearBtn = document.getElementById('clearDataBtn');
  if (!clearBtn) return;
  
  clearBtn.addEventListener('click', () => {
    if (confirm('Clear all saved estimate data? This will refresh the page.')) {
      // Show immediate feedback
      clearBtn.textContent = 'Clearing...';
      clearBtn.style.opacity = '0.5';
      clearBtn.style.pointerEvents = 'none';
      
      // Clear all estimate-related localStorage
      Object.values(STORAGE_KEYS).forEach(key => {
        localStorage.removeItem(key);
      });
      localStorage.removeItem(STORAGE_KEY_PHOTOS);
      localStorage.removeItem(STORAGE_KEY_PENDING_JOBS);
      
      // Force immediate reload with cache bypass
      setTimeout(() => {
        window.location.reload(true);
      }, 100);
    }
  });
}

function setupManualOverride() {
  if (!dom.toggleManualBtn || !dom.manualInputs) return;
  
  // Toggle manual inputs visibility
  dom.toggleManualBtn.addEventListener('click', () => {
    const isHidden = dom.manualInputs.classList.contains('is-hidden');
    dom.manualInputs.classList.toggle('is-hidden');
    dom.toggleManualBtn.textContent = isHidden 
      ? 'Hide manual inputs ↑' 
      : 'Not accurate? Adjust manually ↓';
    
    // Pre-fill with current values
    if (isHidden && state.measurements) {
      dom.manualArea.value = state.measurements.area || '';
      dom.manualPerimeter.value = state.measurements.perimeter || '';
    }
  });
  
  // Apply manual measurements
  if (dom.applyManualBtn) {
    dom.applyManualBtn.addEventListener('click', () => {
      const area = parseInt(dom.manualArea.value) || 0;
      const perimeter = parseInt(dom.manualPerimeter.value) || 0;
      
      if (area > 0 && perimeter > 0) {
        updateMeasurements(area, perimeter, "Manual", "manual");
        dom.manualInputs.classList.add('is-hidden');
        dom.toggleManualBtn.textContent = 'Not accurate? Adjust manually ↓';
        console.log("✅ Applied manual measurements:", { area, perimeter });
      } else {
        alert("Please enter valid area and perimeter values.");
      }
    });
  }
}

function setupEstimateDelivery() {
  // Populate delivery email
  if (dom.deliveryEmail && dom.contactEmail) {
    const email = dom.contactEmail.value || "your email";
    dom.deliveryEmail.textContent = email;
  }
  
  // Generate quote number
  if (dom.quoteNumber) {
    const date = new Date();
    const year = date.getFullYear();
    const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
    dom.quoteNumber.textContent = `EST-${year}-${random}`;
  }
  
  // Download PDF button
  if (dom.downloadPdfBtn) {
    dom.downloadPdfBtn.addEventListener('click', () => {
      // Mock PDF download for demo
      alert('📄 PDF download would start here.\n\nIn production:\n• Generate PDF with estimate details\n• Include property photos\n• Add terms & conditions\n• Email copy to homeowner');
      console.log("📄 PDF download triggered");
    });
  }
  
  // Share link button
  if (dom.shareLinkBtn) {
    dom.shareLinkBtn.addEventListener('click', () => {
      // Mock share functionality
      const mockLink = `https://demo.jobcapturepro.com/estimate/${dom.quoteNumber?.textContent || 'EST-2026-0001'}`;
      
      if (navigator.clipboard) {
        navigator.clipboard.writeText(mockLink).then(() => {
          const originalText = dom.shareLinkBtn.innerHTML;
          dom.shareLinkBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Link copied!
          `;
          setTimeout(() => {
            dom.shareLinkBtn.innerHTML = originalText;
          }, 2000);
        });
      } else {
        alert(`📋 Share this estimate:\n\n${mockLink}\n\nIn production:\n• Unique shareable link\n• Email/SMS options\n• Social sharing`);
      }
      console.log("🔗 Share link triggered:", mockLink);
    });
  }
}

function populateProjectRecap() {
  const recapCard = document.getElementById('projectRecapCard');
  const recapPhoto = document.getElementById('recapPhoto');
  const recapService = document.getElementById('recapService');
  const recapScope = document.getElementById('recapScope');
  const recapComplexity = document.getElementById('recapComplexity');
  
  if (!recapCard) return;
  
  // Get service type
  const serviceType = dom.serviceType?.value || '';
  if (serviceType && recapService) {
    const serviceText = serviceType
      .split('-')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
    recapService.textContent = serviceText;
  }
  
  // Get scope and complexity
  if (recapScope) {
    const scopeValue = dom.scopeType?.value || '';
    const scopeText = scopeValue.charAt(0).toUpperCase() + scopeValue.slice(1);
    recapScope.textContent = scopeText;
  }
  
  if (recapComplexity) {
    const complexityValue = dom.scopeLevel?.value || '';
    const complexityText = complexityValue.charAt(0).toUpperCase() + complexityValue.slice(1);
    recapComplexity.textContent = complexityText;
  }
  
  // Get photo thumbnail from Visual Intake or project photos
  let photoSrc = '';
  if (state.visualIntakePhotos && state.visualIntakePhotos.length > 0) {
    photoSrc = state.visualIntakePhotos[0];
  } else if (state.photos && state.photos.length > 0) {
    photoSrc = state.photos[0];
  }
  
  if (photoSrc && recapPhoto) {
    recapPhoto.src = photoSrc;
    recapPhoto.classList.remove('is-hidden');
  } else if (recapPhoto) {
    recapPhoto.classList.add('is-hidden');
  }
  
  // Setup edit button
  const editBtn = document.getElementById('editProjectBtn');
  if (editBtn) {
    editBtn.onclick = () => {
      setStep(3); // Go back to Project Details step
    };
  }
}

function initGoogleServices() {
  if (!state.mapsApi) return;
  // New Places API doesn't require service initialization
  // AutocompleteSuggestion and Place are used directly as static methods
}

function readContractorContext() {
  const params = new URLSearchParams(window.location.search);
  return {
    contractor: params.get("contractor"),
    service: params.get("service"),
    city: params.get("city")
  };
}

function applyContractorContext() {
  const context = readContractorContext();
  if (!context.contractor) return;
  
  const headerTitle = document.querySelector("#stageTitle");
  const headerSummary = document.querySelector("#stageSummary");
  
  if (state.step === 1 && headerTitle && headerSummary) {
    headerTitle.textContent = "Get an estimate";
    headerSummary.textContent = `From ${context.contractor}, a verified contractor.`;
  }
  
  if (context.service) {
    const serviceMap = {
      plumbing: "plumbing",
      hvac: "hvac",
      electrical: "electrical",
      roofing: "roofing"
    };
    const matchedService = serviceMap[context.service.toLowerCase()];
    if (matchedService && dom.serviceType) {
      dom.serviceType.value = matchedService;
    }
  }
}

/* =========================================================
   VISUAL INTAKE (STEP 0) - AI ANALYSIS & PHOTO UPLOAD
========================================================= */

/**
 * Compress image to reduce API costs and upload time
 * Target: 512x512 max for OpenAI 'low' detail mode
 */
async function compressImage(base64Image, maxSize = 512) {
  return new Promise((resolve) => {
    const img = new Image();
    
    img.onload = () => {
      const canvas = document.createElement('canvas');
      let { width, height } = img;
      
      // Calculate new dimensions (maintain aspect ratio)
      if (width > height) {
        if (width > maxSize) {
          height = (height / width) * maxSize;
          width = maxSize;
        }
      } else {
        if (height > maxSize) {
          width = (width / height) * maxSize;
          height = maxSize;
        }
      }
      
      canvas.width = width;
      canvas.height = height;
      
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, width, height);
      
      // Convert to JPEG at 80% quality (good balance)
      const compressed = canvas.toDataURL('image/jpeg', 0.8);
      resolve(compressed);
    };
    
    img.onerror = () => {
      // If compression fails, return original
      resolve(base64Image);
    };
    
    img.src = base64Image;
  });
}

/**
 * Analyze photos using AI (real endpoint or mock)
 */
async function analyzePhotos(photos) {
  // Use real AI if enabled
  if (CONFIG.useRealAI) {
    try {
      // Compress images first to reduce API costs
      const compressedPhotos = await Promise.all(
        photos.map(photo => compressImage(photo, 512))
      );
      
      // Get user context for better analysis
      const demoUser = loadFromStorage('demoUser');
      const context = demoUser ? { niche: demoUser.niche } : {};
      
      // Call serverless endpoint
      const response = await fetch(CONFIG.visualIntakeEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          photos: compressedPhotos,
          context
        }),
        signal: AbortSignal.timeout(12000) // 12s timeout (endpoint has 10s)
      });
      
      const data = await response.json();
      
      if (data.success && data.analysis) {
        return data.analysis;
      }
      
      // If AI failed but returned fallback flag, use mock
      if (data.fallback) {
        console.log('AI unavailable, using mock analysis');
        return mockPhotoAnalysis(photos);
      }
      
      throw new Error('AI analysis failed');
      
    } catch (error) {
      console.warn('AI analysis error, using mock:', error.message);
      return mockPhotoAnalysis(photos);
    }
  }
  
  // Mock mode (for demos without API key)
  return mockPhotoAnalysis(photos);
}

function mockPhotoAnalysis(photos) {
  const services = ['HVAC', 'Plumbing', 'Electrical', 'Roofing', 'General'];
  const descriptions = [
    'Air conditioning unit not cooling efficiently. Outdoor condenser appears to be running but system not reaching set temperature.',
    'Water heater showing signs of age and potential sediment buildup. Tank appears to be original to the home.',
    'Electrical panel outdated and showing signs of wear. Several breakers may need replacement.',
    'Roof shingles showing weather damage and missing granules in several areas. Flashing around chimney needs attention.',
    'General maintenance needed. Multiple small repairs identified throughout the property.'
  ];
  
  const randomIndex = Math.floor(Math.random() * services.length);
  
  return {
    suggestedServiceType: services[randomIndex].toLowerCase(),
    draftProjectDescription: descriptions[randomIndex],
    detectedSurfaceTypes: ['outdoor unit', 'mechanical equipment'],
    visibleIssues: ['age', 'efficiency'],
    confidenceNotes: `Based on ${photos.length} photo${photos.length > 1 ? 's' : ''} of your project`
  };
}

function setupVisualIntake() {
  if (!dom.visualIntakeInput) return;
  
  // Ensure upload dropzone is visible initially
  if (dom.uploadDropzone) {
    dom.uploadDropzone.classList.remove('is-hidden');
  }
  
  // Restore previous photos and analysis
  const savedPhotos = loadFromStorage(STORAGE_KEYS.visualIntakePhotos);
  const savedAnalysis = loadFromStorage(STORAGE_KEYS.visualIntakeAnalysis);
  const wasSkipped = loadFromStorage(STORAGE_KEYS.visualIntakeSkipped);
  
  if (savedPhotos && savedPhotos.length > 0) {
    state.visualIntakePhotos = savedPhotos;
    renderPhotoGrid();
    
    if (savedAnalysis) {
      state.visualIntakeAnalysis = savedAnalysis;
      showReview(savedAnalysis);
    }
  }
  
  if (wasSkipped) {
    state.visualIntakeSkipped = true;
  }
  
  // Upload trigger button
  dom.uploadTriggerBtn?.addEventListener('click', () => {
    dom.visualIntakeInput.click();
  });
  
  // File input handler
  dom.visualIntakeInput.addEventListener('change', handlePhotoSelect);
  
  // Drag and drop
  dom.uploadDropzone?.addEventListener('dragover', (e) => {
    e.preventDefault();
    dom.uploadDropzone.classList.add('drag-over');
  });
  
  dom.uploadDropzone?.addEventListener('dragleave', () => {
    dom.uploadDropzone.classList.remove('drag-over');
  });
  
  dom.uploadDropzone?.addEventListener('drop', async (e) => {
    e.preventDefault();
    dom.uploadDropzone.classList.remove('drag-over');
    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    await processPhotoFiles(files);
  });
  
  // Skip button
  dom.skipVisualIntakeBtn?.addEventListener('click', () => {
    state.visualIntakeSkipped = true;
    saveToStorage(STORAGE_KEYS.visualIntakeSkipped, true);
    setStep(1); // Jump to address step
  });
  
  // Re-analyze button
  dom.reAnalyzeBtn?.addEventListener('click', async () => {
    if (state.visualIntakePhotos.length > 0) {
      await runAnalysis();
    }
  });
  
  // Review description editing
  dom.reviewDescription?.addEventListener('input', () => {
    if (state.visualIntakeAnalysis) {
      state.visualIntakeAnalysis.draftProjectDescription = dom.reviewDescription.value;
      saveToStorage(STORAGE_KEYS.visualIntakeAnalysis, state.visualIntakeAnalysis);
    }
  });
}

async function handlePhotoSelect(e) {
  const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
  await processPhotoFiles(files);
  e.target.value = ''; // Reset input
}

async function processPhotoFiles(files) {
  const maxPhotos = 5;
  const remainingSlots = maxPhotos - state.visualIntakePhotos.length;
  const filesToProcess = files.slice(0, remainingSlots);
  
  for (const file of filesToProcess) {
    const base64 = await fileToBase64(file);
    state.visualIntakePhotos.push(base64);
  }
  
  saveToStorage(STORAGE_KEYS.visualIntakePhotos, state.visualIntakePhotos);
  renderPhotoGrid();
  
  if (state.visualIntakePhotos.length > 0) {
    await runAnalysis();
  }
}

function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

function renderPhotoGrid() {
  if (!dom.intakePhotoGrid) return;
  
  if (state.visualIntakePhotos.length === 0) {
    dom.intakePhotoGrid.classList.add('is-hidden');
    dom.uploadDropzone?.classList.remove('is-hidden');
    return;
  }
  
  dom.uploadDropzone?.classList.add('is-hidden');
  dom.intakePhotoGrid.classList.remove('is-hidden');
  
  dom.intakePhotoGrid.innerHTML = state.visualIntakePhotos.map((photo, index) => `
    <div class="intake-photo-item">
      <img src="${photo}" alt="Project photo ${index + 1}" class="intake-photo-img" width="200" height="150" loading="lazy" />
      <button class="intake-photo-remove" data-index="${index}" type="button">×</button>
    </div>
  `).join('');
  
  // Add remove handlers
  dom.intakePhotoGrid.querySelectorAll('.intake-photo-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const index = parseInt(btn.dataset.index);
      removePhoto(index);
    });
  });
}

function removePhoto(index) {
  state.visualIntakePhotos.splice(index, 1);
  saveToStorage(STORAGE_KEYS.visualIntakePhotos, state.visualIntakePhotos);
  renderPhotoGrid();
  
  if (state.visualIntakePhotos.length === 0) {
    hideReview();
    hideAnalyzing();
  }
}

async function runAnalysis() {
  if (!dom.intakeAnalyzing || !dom.intakeReview) return;
  
  // Show analyzing state
  dom.intakeReview.classList.add('is-hidden');
  dom.intakeAnalyzing.classList.remove('is-hidden');
  state.analyzingPhotos = true;
  
  // Simulate delay for realistic feel
  await new Promise(resolve => setTimeout(resolve, 1500));
  
  // Run analysis
  const analysis = await analyzePhotos(state.visualIntakePhotos);
  state.visualIntakeAnalysis = analysis;
  saveToStorage(STORAGE_KEYS.visualIntakeAnalysis, analysis);
  
  // Hide analyzing, show review
  state.analyzingPhotos = false;
  hideAnalyzing();
  showReview(analysis);
}

function hideAnalyzing() {
  dom.intakeAnalyzing?.classList.add('is-hidden');
}

function showReview(analysis) {
  if (!dom.intakeReview || !analysis) return;
  
  dom.intakeReview.classList.remove('is-hidden');
  
  if (dom.reviewServiceType && analysis.suggestedServiceType) {
    // Convert "carpet-cleaning" to "Carpet Cleaning"
    const serviceType = analysis.suggestedServiceType
      .split('-')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
    dom.reviewServiceType.textContent = serviceType;
  }
  
  if (dom.reviewDescription) {
    dom.reviewDescription.value = analysis.draftProjectDescription;
  }
  
  if (dom.reviewConfidence) {
    const photoCount = state.visualIntakePhotos?.length || 0;
    const areaText = analysis.keyDetails?.estimatedArea ? ` • ${analysis.keyDetails.estimatedArea}` : '';
    dom.reviewConfidence.textContent = `Based on ${photoCount} photo${photoCount !== 1 ? 's' : ''}${areaText}`;
  }
  
  // Enable navigation to next step
  updateNextButton();
}

function hideReview() {
  dom.intakeReview?.classList.add('is-hidden');
}

function prefillSurveyFromAnalysis() {
  const analysis = state.visualIntakeAnalysis;
  if (!analysis) return;
  
  // Pre-fill service type
  if (dom.serviceType && analysis.suggestedServiceType) {
    const serviceValue = analysis.suggestedServiceType.toLowerCase().trim();
    
    // Direct match first
    const options = Array.from(dom.serviceType.options);
    let matchingOption = options.find(opt => opt.value === serviceValue);
    
    // If no direct match, try partial matching
    if (!matchingOption) {
      matchingOption = options.find(opt => 
        opt.value.includes(serviceValue) || 
        serviceValue.includes(opt.value)
      );
    }
    
    if (matchingOption) {
      dom.serviceType.value = matchingOption.value;
      console.log('AI selected service type:', matchingOption.value);
    } else {
      console.warn('Could not match service type:', serviceValue);
    }
  }
  
  // Pre-fill notes/description
  if (dom.notes && analysis.draftProjectDescription) {
    dom.notes.value = analysis.draftProjectDescription;
    
    // Add helper text to indicate it was pre-filled
    const existingHelper = dom.notes.parentElement?.querySelector('.helper-text');
    if (existingHelper) {
      existingHelper.textContent = 'Prepared from your photos • Edit as needed';
      existingHelper.style.color = '#10b981';
    }
  }
  
  // Pre-fill from keyDetails if available
  if (analysis.keyDetails) {
    // Map visible issues to scope (service, repair, replace, install)
    if (dom.scopeType) {
      const issues = analysis.keyDetails?.visibleIssues || [];
      const serviceType = analysis.suggestedServiceType || '';
      
      // Cleaning/maintenance services → "service"
      if (serviceType.includes('cleaning') || 
          serviceType.includes('maintenance') || 
          serviceType.includes('testing') ||
          serviceType.includes('landscaping')) {
        dom.scopeType.value = 'service';
      }
      // Replacement or installation services
      else if (serviceType.includes('replacement') || serviceType.includes('installation')) {
        dom.scopeType.value = 'replace';
      }
      // Check issues for replacement indicators
      else if (issues.some(i => i.toLowerCase().includes('replace') || i.toLowerCase().includes('missing'))) {
        dom.scopeType.value = 'replace';
      }
      // New install
      else if (serviceType.includes('install')) {
        dom.scopeType.value = 'install';
      }
      // Default to repair for damage/issues
      else {
        dom.scopeType.value = 'repair';
      }
    }
    
    // Map urgency
    if (analysis.keyDetails.urgency) {
      const urgencyMap = {
        'immediate': 'soon',
        'soon': 'soon',
        'routine': 'flexible'
      };
      state.urgency = urgencyMap[analysis.keyDetails.urgency] || 'soon';
      
      // Update urgency chips UI
      document.querySelectorAll('.urgency-chip').forEach(chip => {
        chip.classList.toggle('is-active', chip.dataset.urgency === state.urgency);
      });
    }
    
    // Map complexity based on condition, visible issues, and area
    if (dom.scopeLevel) {
      const issuesCount = analysis.keyDetails.visibleIssues?.length || 0;
      const condition = analysis.keyDetails.condition?.toLowerCase() || '';
      const hasLargeArea = analysis.keyDetails.estimatedArea?.includes('entire') || 
                           analysis.keyDetails.estimatedArea?.includes('whole');
      
      if (condition === 'poor' || issuesCount > 2 || hasLargeArea) {
        dom.scopeLevel.value = 'premium';
      } else if (condition === 'fair' || issuesCount > 1) {
        dom.scopeLevel.value = 'elevated';
      } else {
        dom.scopeLevel.value = 'standard';
      }
    }
    
    // Add estimated area to notes if available and not already there
    if (analysis.keyDetails.estimatedArea && dom.notes) {
      const currentNotes = dom.notes.value.trim();
      const areaNote = `Estimated area: ${analysis.keyDetails.estimatedArea}`;
      if (!currentNotes.includes('Estimated area')) {
        dom.notes.value = currentNotes ? `${currentNotes}\n\n${areaNote}` : areaNote;
      }
    }
  }
}

// Property Snapshot Functions
async function fetchPropertySnapshot() {
  if (!state.selectedAddress) return;
  
  const address = state.selectedAddress.address;
  const cachedSnapshot = loadFromStorage(STORAGE_KEYS.propertySnapshot);
  
  // Check if cached and matches current address
  if (cachedSnapshot && cachedSnapshot.address === address) {
    state.propertySnapshot = cachedSnapshot;
    populatePropertySnapshot();
    
    // Try fusion if we have homeowner photos
    if (state.visualIntakePhotos && state.visualIntakePhotos.length > 0) {
      fetchEstimateFusion();
    }
    return;
  }
  
  try {
    const response = await fetch('/.netlify/functions/property-snapshot', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        address: address,
        lat: state.selectedAddress.lat,
        lng: state.selectedAddress.lng
      }),
      signal: AbortSignal.timeout(15000)
    });
    
    const data = await response.json();
    
    if (data.success) {
      state.propertySnapshot = {
        address: address,
        lat: data.lat,
        lng: data.lng,
        streetViewUrl: data.streetViewImageUrl,
        satelliteUrl: data.satelliteImageUrl,
        confidence: data.snapshotConfidence,
        notes: data.notes
      };
      
      saveToStorage(STORAGE_KEYS.propertySnapshot, state.propertySnapshot);
      populatePropertySnapshot();
      
      // Try fusion if we have homeowner photos
      if (state.visualIntakePhotos && state.visualIntakePhotos.length > 0) {
        fetchEstimateFusion();
      }
    } else {
      // Show fallback
      showPropertySnapshotFallback();
    }
  } catch (error) {
    console.warn('Property snapshot fetch failed:', error);
    showPropertySnapshotFallback();
  }
}

async function fetchEstimateFusion() {
  if (!state.propertySnapshot || !state.visualIntakePhotos || state.visualIntakePhotos.length === 0) {
    return;
  }
  
  // Check cache
  const cachedFusion = loadFromStorage(STORAGE_KEYS.estimateFusion);
  if (cachedFusion && cachedFusion.address === state.selectedAddress.address) {
    state.estimateFusion = cachedFusion;
    populateFusionSummary();
    return;
  }
  
  try {
    const response = await fetch('/.netlify/functions/estimate-fusion', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        homeownerPhotos: state.visualIntakePhotos.slice(0, 3), // Max 3 photos
        streetViewImageUrl: state.propertySnapshot.streetViewUrl,
        satelliteImageUrl: state.propertySnapshot.satelliteUrl,
        surveyContext: {
          serviceType: dom.serviceType?.value || '',
          scope: dom.scopeType?.value || '',
          complexity: dom.scopeLevel?.value || '',
          notes: dom.notes?.value || '',
          address: state.selectedAddress.address
        }
      }),
      signal: AbortSignal.timeout(20000)
    });
    
    const data = await response.json();
    
    if (data.success) {
      state.estimateFusion = {
        address: state.selectedAddress.address,
        contextSummary: data.contextSummary,
        needsConfirmation: data.needsConfirmation,
        propertySignals: data.propertySignals,
        confidence: data.confidence,
        suggestedAdjustments: data.suggestedAdjustments
      };
      
      saveToStorage(STORAGE_KEYS.estimateFusion, state.estimateFusion);
      populateFusionSummary();
    }
  } catch (error) {
    console.warn('Estimate fusion failed:', error);
    // Don't show error - just show images without AI text
  }
}

function populatePropertySnapshot() {
  if (!state.propertySnapshot || !dom.propertySnapshotPanel) return;
  
  dom.propertySnapshotPanel.classList.remove('is-hidden');
  
  // Populate street view
  if (state.propertySnapshot.streetViewUrl && dom.snapshotStreetView) {
    dom.snapshotStreetView.src = state.propertySnapshot.streetViewUrl;
  } else if (dom.snapshotStreetView) {
    dom.snapshotStreetView.parentElement.classList.add('is-hidden');
  }
  
  // Populate satellite view
  if (state.propertySnapshot.satelliteUrl && dom.snapshotSatellite) {
    dom.snapshotSatellite.src = state.propertySnapshot.satelliteUrl;
  }
  
  // Populate homeowner photo if exists
  if (state.visualIntakePhotos && state.visualIntakePhotos.length > 0) {
    if (dom.snapshotHomeowner && dom.snapshotHomeownerItem) {
      dom.snapshotHomeowner.src = state.visualIntakePhotos[0];
      dom.snapshotHomeownerItem.classList.remove('is-hidden');
    }
  }
  
  // Show notes if street view unavailable
  if (state.propertySnapshot.notes && dom.snapshotFallback) {
    dom.snapshotFallback.textContent = state.propertySnapshot.notes;
    dom.snapshotFallback.classList.remove('is-hidden');
  }
}

function populateFusionSummary() {
  if (!state.estimateFusion || !dom.snapshotContent) return;
  
  if (dom.snapshotSummary) {
    dom.snapshotSummary.textContent = state.estimateFusion.contextSummary;
  }
  
  if (dom.snapshotConfirm && state.estimateFusion.needsConfirmation) {
    dom.snapshotConfirm.textContent = `Need to confirm: ${state.estimateFusion.needsConfirmation}`;
  }
  
  dom.snapshotContent.classList.remove('is-hidden');
}

function showPropertySnapshotFallback() {
  if (!dom.propertySnapshotPanel) return;
  
  dom.propertySnapshotPanel.classList.remove('is-hidden');
  
  // Show homeowner photo if exists
  if (state.visualIntakePhotos && state.visualIntakePhotos.length > 0) {
    if (dom.snapshotHomeowner && dom.snapshotHomeownerItem) {
      dom.snapshotHomeowner.src = state.visualIntakePhotos[0];
      dom.snapshotHomeownerItem.classList.remove('is-hidden');
    }
  }
  
  if (dom.snapshotFallback) {
    dom.snapshotFallback.textContent = 'Property images unavailable. Measurements still work.';
    dom.snapshotFallback.classList.remove('is-hidden');
  }
}

function updateScopeOptions() {
  if (!dom.serviceType || !dom.scopeType) return;
  
  const serviceType = dom.serviceType.value.toLowerCase();
  const currentScope = dom.scopeType.value; // Preserve current selection if possible
  
  // Extract service category from service type (e.g., "hvac-repair" -> "hvac")
  const serviceCategory = serviceType.split('-')[0];
  
  // Get scope options for this service type
  const scopeOptions = SCOPE_OPTIONS[serviceCategory] || SCOPE_OPTIONS['default'];
  
  // Clear and rebuild scope dropdown
  dom.scopeType.innerHTML = '';
  scopeOptions.forEach(option => {
    const opt = document.createElement('option');
    opt.value = option.value;
    opt.textContent = option.label;
    dom.scopeType.appendChild(opt);
  });
  
  // Try to preserve current selection if it still exists
  const optionValues = scopeOptions.map(o => o.value);
  if (optionValues.includes(currentScope)) {
    dom.scopeType.value = currentScope;
  } else {
    // Default to first option
    dom.scopeType.value = scopeOptions[0].value;
  }
  
  // Save session (estimate will be built when user reaches Step 4)
  saveSession();
}

function setupDynamicFields() {
  // Update scope options when service type changes
  if (dom.serviceType) {
    dom.serviceType.addEventListener('change', () => {
      updateScopeOptions();
      updateMeasurementUI(); // Update measurement form when service changes
      // Rebuild estimate if we're on the estimate step
      if (state.step === 4) {
        buildEstimate();
      }
    });
    
    // Initialize scope options on load
    updateScopeOptions();
  }
  
  // Update estimate when any field changes
  [dom.scopeType, dom.scopeLevel].forEach(element => {
    if (element) {
      element.addEventListener('change', () => {
        // Rebuild estimate if we're on the estimate step
        if (state.step === 4) {
          buildEstimate();
        }
        saveSession();
      });
    }
  });
}

function updateEstimateVerbiage() {
  if (!dom.serviceType || !dom.scopeType) return;
  
  const serviceType = dom.serviceType.value.toLowerCase();
  const scope = dom.scopeType.value.toLowerCase();
  
  // Extract service category
  const serviceCategory = serviceType.split('-')[0];
  
  // Get verbiage for this service + scope combo
  const serviceVerbiage = ESTIMATE_VERBIAGE[serviceCategory] || ESTIMATE_VERBIAGE['default'];
  const scopeVerbiage = serviceVerbiage[scope] || serviceVerbiage['repair'] || serviceVerbiage[Object.keys(serviceVerbiage)[0]];
  
  // Update estimate cards with dynamic verbiage
  const estimateCards = document.querySelectorAll('.estimate-card');
  if (estimateCards.length >= 3) {
    // Good tier
    const goodList = estimateCards[0].querySelector('.estimate-list');
    if (goodList && scopeVerbiage.good) {
      goodList.innerHTML = scopeVerbiage.good.map(item => `<li>${item}</li>`).join('');
    }
    
    // Better tier
    const betterList = estimateCards[1].querySelector('.estimate-list');
    if (betterList && scopeVerbiage.better) {
      betterList.innerHTML = scopeVerbiage.better.map(item => `<li>${item}</li>`).join('');
    }
    
    // Best tier
    const bestList = estimateCards[2].querySelector('.estimate-list');
    if (bestList && scopeVerbiage.best) {
      bestList.innerHTML = scopeVerbiage.best.map(item => `<li>${item}</li>`).join('');
    }
  }
}

function init() {
  // Initialize analytics tracking
  const params = new URLSearchParams(window.location.search);
  const contractorContext = readContractorContext();
  
  if (typeof EstimatorAnalytics !== 'undefined') {
    EstimatorAnalytics.init({
      contractorId: contractorContext.contractor || null,
      source: params.get('source') || 'direct'
    });
  }
  
  applyPersonalization();
  applyContractorContext();
  
  // Clear session if coming from directory (fresh contractor request)
  if (params.get('source') === 'directory') {
    // Clear ALL previous session data for fresh start
    localStorage.removeItem(STORAGE_KEYS.session);
    localStorage.removeItem(STORAGE_KEYS.address);
    localStorage.removeItem(STORAGE_KEYS.propertyFacts);
    localStorage.removeItem(STORAGE_KEYS.measurements);
    localStorage.removeItem(STORAGE_KEYS.measurementPolygon);
    localStorage.removeItem(STORAGE_KEYS.measurementMode);
    localStorage.removeItem(STORAGE_KEYS.propertySnapshot);
    localStorage.removeItem(STORAGE_KEYS.estimateFusion);
    localStorage.removeItem(STORAGE_KEYS.visualIntakeSkipped);
    // Keep photos and analysis if user wants to reuse them
    // localStorage.removeItem(STORAGE_KEYS.visualIntakePhotos);
    // localStorage.removeItem(STORAGE_KEYS.visualIntakeAnalysis);
    
    // Reset state object to initial values
    state.step = 0;
    state.selectedAddress = null;
    state.propertyFacts = null;
    state.measurements = null;
    state.measurementPolygon = [];
    state.measurementMode = "auto";
    state.selectedTime = null;
  }
  
  restoreSession();
  setupVisualIntake();
  setStep(state.step || 0);
  setupAddressHandlers();
  setupMeasurementHandlers();
  setMeasurementMode(state.measurementMode || "auto");
  setupUrgencyChips();
  setupPhotoUpload();
  setupTimeChips();
  setupCustomTimePicker(); // NEW: Custom date/time picker
  setupMobileStepper();
  setupSwipeNavigation();
  handleNavigation();
  setupContactInputs();
  setupStartOver();
  setupGlobalClose();
  setupClearData();
  setupManualOverride();
  setupDynamicFields(); // NEW: Setup dynamic scope and complexity
  loadGoogleMaps()
    .then((mapsApi) => {
      state.mapsApi = mapsApi;
      initGoogleServices();
      if (!hasGoogleMaps()) {
        dom.mapFallback.classList.remove("is-hidden");
      }
      Promise.resolve(loadSavedAddress()).finally(() => {
        restorePolygon();
        restoreMeasurements();
        if (state.selectedAddress?.lat && state.selectedAddress?.lng) {
          initMap(mapLatLng(state.selectedAddress.lat, state.selectedAddress.lng));
        }
      });
    })
    .catch(() => {
      dom.mapFallback.classList.remove("is-hidden");
      Promise.resolve(loadSavedAddress()).finally(() => {
        restorePolygon();
        restoreMeasurements();
      });
    });
}

// Global function for directory integration (called from directory page)
window.launchEstimateBuilder = function(contractorContext) {
  const takeover = document.getElementById("estimateTakeover");
  const iframe = document.getElementById("takeoverIframe");
  const closeBtn = document.getElementById("takeoverClose");
  
  if (!takeover || !iframe || !closeBtn) return;
  
  const params = new URLSearchParams({
    contractor: contractorContext.name || "",
    service: contractorContext.service || "",
    city: contractorContext.city || ""
  });
  
  const baseUrl = window.location.pathname.includes("jcp-quote-builder") 
    ? window.location.pathname 
    : "../jcp-quote-builder/";
  
  iframe.src = `${baseUrl}?${params.toString()}`;
  takeover.classList.remove("is-hidden");
  document.body.style.overflow = "hidden";
  
  const closeTakeover = () => {
    takeover.classList.add("is-hidden");
    document.body.style.overflow = "";
    iframe.src = "";
  };
  
  closeBtn.onclick = closeTakeover;
  takeover.querySelector(".takeover-backdrop").onclick = closeTakeover;
};

/*
 * DIRECTORY INTEGRATION EXAMPLE
 * 
 * To launch the estimate builder from the directory page, call:
 * 
 * window.launchEstimateBuilder({
 *   name: "Summit Plumbing",
 *   service: "plumbing",
 *   city: "Austin, TX"
 * });
 * 
 * This will open the estimate builder as a full-screen takeover
 * with contractor context pre-filled.
 */

// Initialize the application
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
