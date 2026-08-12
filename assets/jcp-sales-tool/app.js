(() => {
  const cfg = window.JCP_SALES_TOOL || {};
  const assetBase = (cfg.assetBase || "").replace(/\/$/, "");
  const assetVer = "20260812d";
  const img = (name) => `${assetBase}/assets/${name}?v=${assetVer}`;
  const plans = cfg.plans || {};
  const reviews = Array.isArray(cfg.reviews) ? cfg.reviews : [];
  const cta = cfg.cta || {};
  const storageKey = cfg.storageKey || "jcp-sales-call-live";

  const baseChapters = [
    { id: "cover", label: "Opening" },
    { id: "problem", label: "The problem" },
    { id: "diagnose", label: "Diagnose" },
    { id: "gap", label: "Proof gap" },
    { id: "engine", label: "How it works" },
    { id: "proof", label: "Proof" },
    { id: "fit", label: "Right fit" },
    { id: "plan", label: "Plan" },
    { id: "objections", label: "Questions" },
    { id: "close", label: "Next steps" },
  ];

  const prospectSeed = cfg.prospect || {};
  const defaults = {
    prospectName: prospectSeed.company || "",
    repName: (cfg.presenter && cfg.presenter.name) || "",
    mode: ["partner", "affiliate"].includes(prospectSeed.mode) ? prospectSeed.mode : "contractor",
    trade: prospectSeed.trade || "Home services",
    showPricing: cfg.flags ? !!cfg.flags.showPricing : true,
    showAcculevel: cfg.flags ? !!cfg.flags.showAcculevel : true,
    acculevelLeadLift: cfg.acculevelLeadLift || "",
    chapter: 0,
    segment: "growth",
    jobsPerWeek: prospectSeed.jobsPerWeek || 20,
    captureRate: prospectSeed.captureRate ?? 45,
    publishRate: prospectSeed.publishRate ?? 15,
    locations: prospectSeed.locations || 1,
    responseTime: "1–4 hours",
    crm: (prospectSeed.softwareLabels && prospectSeed.softwareLabels[0]) || "Housecall Pro",
    software: prospectSeed.software || [],
    integrations: prospectSeed.integrations || ["Housecall Pro", "Jobber", "ServiceTitan", "CompanyCam"],
    photoFrequency: prospectSeed.photoFrequency || "most",
    publishHabit: prospectSeed.publishHabit || "occasionally",
    reviewHabit: prospectSeed.reviewHabit || "occasionally",
    challenges: prospectSeed.challenges || [],
    timeline: prospectSeed.timeline || "30_days",
    channels: ["Website", "Google Business Profile"],
    priorities: ["Local visibility", "More reviews"],
    customIntegration: false,
    automation: true,
    salesNotes: "",
    nextStep: "Start a free 14-day trial and connect one real job workflow.",
    followUpDate: "",
    presenting: true,
  };

  let state = loadState();
  if (new URLSearchParams(window.location.search).get("present") === "0") {
    state.presenting = false;
  } else if (new URLSearchParams(window.location.search).get("present") === "1") {
    state.presenting = true;
  }
  let saveTimer;
  let selectedEngine = 0;
  let selectedObjection = 0;
  let selectedCaseMarket = "triadelphia";

  const $ = (selector) => document.querySelector(selector);
  const stage = $("#chapterStage");
  const nav = $("#chapterNav");

  function loadState() {
    try {
      const saved = JSON.parse(localStorage.getItem(storageKey) || "{}");
      return { ...defaults, ...saved, prospectName: saved.prospectName || defaults.prospectName };
    } catch {
      return { ...defaults };
    }
  }

  function saveState() {
    const { presenting, ...persist } = state;
    localStorage.setItem(storageKey, JSON.stringify(persist));
    const el = $("#saveState");
    if (!el) return;
    el.classList.add("visible");
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => el.classList.remove("visible"), 1100);
  }

  function setState(patch, shouldRender = true) {
    state = { ...state, ...patch };
    saveState();
    if (shouldRender) render();
  }

  function esc(value = "") {
    return String(value).replace(/[&<>'"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" })[char]);
  }

  function chapterList() {
    return baseChapters;
  }

  function goTo(index) {
    const list = chapterList();
    state.chapter = Math.max(0, Math.min(list.length - 1, index));
    saveState();
    render();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function renderNav() {
    const list = chapterList();
    nav.innerHTML = list
      .map(
        (chapter, index) => `
    <button class="chapter-link ${state.chapter === index ? "active" : ""}" data-go="${index}" type="button">
      <span class="chapter-number">${String(index + 1).padStart(2, "0")}</span>
      <span>${chapter.label}</span>
    </button>`
      )
      .join("");
  }

  function chapterHeader(number, eyebrow, title, intro) {
    return `<div class="chapter-head">
    <span class="eyebrow">${eyebrow}</span>
    <h2>${title}</h2>
    <p>${intro}</p>
  </div>`;
  }

  function checkboxChoice(name, value, label, checked) {
    return `<label class="choice"><input type="checkbox" name="${name}" value="${value}" ${checked ? "checked" : ""}><span>${label}</span></label>`;
  }

  function radioChoice(name, value, label, checked) {
    return `<label class="choice"><input type="radio" name="${name}" value="${value}" ${checked ? "checked" : ""}><span>${label}</span></label>`;
  }

  function integrationLine() {
    const list = state.integrations && state.integrations.length ? state.integrations : ["Housecall Pro", "Jobber", "ServiceTitan", "CompanyCam"];
    return list.join(", ");
  }

  function planFromId(id) {
    return plans[id] || null;
  }

  function recommendPlan() {
    const starter = planFromId("starter");
    const scale = planFromId("scale");
    const enterprise = planFromId("enterprise");
    if (state.customIntegration || Number(state.locations) > 3 || state.segment === "enterprise") {
      return {
        id: "enterprise",
        name: (enterprise && enterprise.name) || "Enterprise",
        price: enterprise ? `$${enterprise.monthly}` : "$399",
        reason: "Custom connectivity, org-wide control, or a larger footprint — so every location can publish geotagged, local-search-ready proof at scale.",
        includes: (enterprise && enterprise.includes) || [],
      };
    }
    if (state.automation || Number(state.locations) > 1 || state.priorities.some((x) => ["More reviews", "Consistent content", "Multi-location control", "Local visibility"].includes(x))) {
      return {
        id: "scale",
        name: (scale && scale.name) || "Scale",
        price: scale ? `$${scale.monthly}` : "$249",
        reason: "They need the proof engine running on every job — local Maps visibility, geotagged website content, reviews, and social — the signals AI search and homebuyers both trust.",
        includes: (scale && scale.includes) || [],
      };
    }
    return {
      id: "starter",
      name: (starter && starter.name) || "Starter",
      price: starter ? `$${starter.monthly}` : "$99",
      reason: "A simple mobile-led workflow: capture the job, publish local-search-ready proof, and ask for the review on site.",
      includes: (starter && starter.includes) || [],
    };
  }

  function isPartner() {
    return state.mode === "partner";
  }
  function isAffiliate() {
    return state.mode === "affiliate";
  }
  function isChannelPartner() {
    return isPartner() || isAffiliate();
  }

  /** Affiliate = 20% x 12 months. Partner = 15% residual while customer stays active. */
  function commissionRows() {
    const rows = [
      { plan: "Starter", monthly: 99 },
      { plan: "Scale", monthly: 249 },
      { plan: "Enterprise", monthly: 399 },
    ];
    if (isAffiliate()) {
      return rows.map((r) => ({
        ...r,
        rate: "20%",
        monthlyPay: `$${(r.monthly * 0.2).toFixed(2)}`,
        term: "12 months",
        potential: `$${(r.monthly * 0.2 * 12).toFixed(2)}`,
      }));
    }
    return rows.map((r) => ({
      ...r,
      rate: "15%",
      monthlyPay: `$${(r.monthly * 0.15).toFixed(2)}`,
      term: "While customer is active",
      potential: `$${(r.monthly * 0.15).toFixed(2)}/mo ongoing`,
    }));
  }

  function renderCover() {
    const partner = isPartner();
    const affiliate = isAffiliate();
    const title = partner
      ? `Give every client a proof engine that creates <em>more calls.</em>`
      : affiliate
        ? `Earn recurring commissions by helping contractors turn jobs into <em>marketing.</em>`
        : `Turn every completed job into <em>more calls.</em>`;
    const company = state.prospectName || (partner ? "your clients" : affiliate ? "contractors you know" : "your team");
    const body = partner
      ? `${esc(company)} already take job photos. JobCapturePro turns that work into local Maps visibility, geotagged website content, social, and directory proof — plus an on-site QR review ask. Today’s search (including AI answers) rewards real work, real reviews, and real credibility — exactly what your clients already produce.`
      : affiliate
        ? `Refer contractors and home-service businesses to JobCapturePro and earn <strong>20% recurring commission for 12 months</strong> when they become paid customers. It’s an easy recommend: they already finish jobs and take photos — JCP turns that into local SEO proof buyers and AI search engines both trust.`
        : `${esc(company)} already takes job photos. JobCapturePro turns them into Google Maps updates, local-search-optimized website content, social posts, and directory listings — then your crew asks for a review on site with a QR code. Real jobs. Real proof. More leads.`;
    const eyebrow = partner ? "Partner walkthrough" : affiliate ? "Affiliate program" : "Product walkthrough";
    return `<section class="chapter cover">
    <img class="cover-image" src="${img("jcp-product-visual.png")}" alt="JobCapturePro completed-job publishing workflow" />
    <div class="cover-copy">
      <span class="eyebrow">${eyebrow}</span>
      <h1>${title}</h1>
      <p>${body}</p>
      <p class="cover-integrations">Already on ${esc(integrationLine())}? We integrate with the tools crews already use.</p>
      <button class="start-btn" type="button" data-go="1">See how it works →</button>
    </div>
    <div class="cover-callout">${state.repName ? `Presented by ${esc(state.repName)} · ` : ""}${esc(state.prospectName || (partner ? "Partner overview" : affiliate ? "Affiliate overview" : "Prospect presentation"))}</div>
  </section>`;
  }

  function renderProblem() {
    const partner = isPartner();
    const affiliate = isAffiliate();
    const title = partner
      ? "Your clients already have the content."
      : affiliate
        ? "The people you refer already have the content."
        : "You already have the content.";
    const intro = partner
      ? `Completed jobs rarely become consistent local SEO and Maps proof for the ${esc(state.trade.toLowerCase())} clients you serve — even though AI search and homebuyers both reward real work and real reviews.`
      : affiliate
        ? `Most ${esc(state.trade.toLowerCase())} teams already finish jobs and take photos — but that proof never becomes Maps visibility, geotagged website content, directory presence, or a timely review ask.`
        : `For ${esc(state.trade.toLowerCase())} teams, completed jobs rarely make it from the camera roll to Google Maps, local search, and the trust signals that turn browsers into leads.`;
    return `<section class="chapter content-pad">
    ${chapterHeader(2, "The missed opportunity", title, intro)}
    <div class="problem-layout">
      <div class="problem-statement">One real job can become proof in <em>five places.</em></div>
      <div class="channel-stack">
        <div class="channel-line"><strong>Google Maps</strong><span>Local visibility that drives calls</span></div>
        <div class="channel-line"><strong>Your website</strong><span>Geotagged, local-SEO content</span></div>
        <div class="channel-line"><strong>Social media</strong><span>Consistent project proof</span></div>
        <div class="channel-line"><strong>Directory</strong><span>Verified public listing</span></div>
        <div class="channel-line"><strong>Review requests</strong><span>On-site QR / link handoff</span></div>
      </div>
    </div>
    <div class="proof-strip"><div class="proof-stat"><b>Local SEO</b><span>Maps + site proof that wins leads</span></div><div class="proof-stat"><b>AI-ready</b><span>real work, reviews, credibility</span></div><div class="proof-stat"><b>+ reviews</b><span>QR ask before you leave</span></div></div>
  </section>`;
  }

  function renderDiagnose() {
    const crmOptions = ["Housecall Pro", "Jobber", "ServiceTitan", "CompanyCam", "FieldEdge", "Workiz", "QuickBooks", "HighLevel", "Other / none"];
    const channels = ["Website", "Google Business Profile", "Facebook / Instagram", "Directory", "Review requests"];
    const priorities = ["Local visibility", "More reviews", "Faster follow-up", "Consistent content", "Multi-location control"];
    return `<section class="chapter content-pad">
    ${chapterHeader(3, "Your workflow", "Start with the work you already do.", "A few quick inputs so we can show what this looks like for your volume — not a generic demo.")}
    <div class="prompt-banner"><strong>Together on this call</strong><p>Walk through what happens to photos after a job finishes — and how you ask for the review before you leave.</p></div>
    <div class="form-grid">
      <div class="field"><label for="jobsPerWeek">Completed jobs per week</label><input id="jobsPerWeek" type="number" min="1" max="10000" value="${state.jobsPerWeek}"></div>
      <div class="field"><label for="locations">Locations</label><input id="locations" type="number" min="1" max="500" value="${state.locations}"></div>
      <div class="field"><label for="crm">Current system</label><select id="crm">${crmOptions.map((x) => `<option ${state.crm === x ? "selected" : ""}>${x}</option>`).join("")}</select></div>
      <div class="field"><label for="responseTime">Typical lead response</label><select id="responseTime">${["Under 5 minutes", "5–30 minutes", "30–60 minutes", "1–4 hours", "Same day", "Next day or later", "Not sure"].map((x) => `<option ${state.responseTime === x ? "selected" : ""}>${x}</option>`).join("")}</select></div>
      <div class="field"><span class="field-label">Jobs with usable photos</span><div class="range-row"><input id="captureRate" type="range" min="0" max="100" value="${state.captureRate}"><output class="range-output" id="captureOutput">${state.captureRate}%</output></div></div>
      <div class="field"><span class="field-label">Jobs published as proof</span><div class="range-row"><input id="publishRate" type="range" min="0" max="100" value="${state.publishRate}"><output class="range-output" id="publishOutput">${state.publishRate}%</output></div></div>
      <div class="field wide"><span class="field-label">Where proof appears today</span><div class="choice-row">${channels.map((x) => checkboxChoice("channels", x, x, state.channels.includes(x))).join("")}</div></div>
      <div class="field wide"><span class="field-label">What matters most</span><div class="choice-row">${priorities.map((x) => checkboxChoice("priorities", x, x, state.priorities.includes(x))).join("")}</div></div>
    </div>
  </section>`;
  }

  function calcGap() {
    const monthly = Math.round(Math.max(0, Number(state.jobsPerWeek) || 0) * 4.33);
    const visible = Math.round((monthly * Math.min(100, Math.max(0, Number(state.publishRate) || 0))) / 100);
    const unused = Math.max(0, monthly - visible);
    return { monthly, visible, unused, unusedPct: monthly ? Math.round((unused / monthly) * 100) : 0 };
  }

  function renderGap() {
    const gap = calcGap();
    return `<section class="chapter content-pad">
    ${chapterHeader(4, "Proof gap", "Make the invisible work visible.", "These are completed jobs that could strengthen local Maps presence, website SEO, and review credibility — but may not leave a consistent public trail today.")}
    <div class="gap-layout">
      <div>
        <div class="big-number"><span>${gap.unused}</span></div>
        <p class="big-label">completed jobs per month may be going unused as public proof.</p>
        <div class="gap-meter"><i class="visible" style="width:${100 - gap.unusedPct}%"></i><i class="unused" style="width:${gap.unusedPct}%"></i></div>
        <div class="meter-key"><span>${gap.visible} visible</span><span>${gap.unused} underused</span></div>
      </div>
      <div>
        <div class="signal-list">
          <div class="signal"><span>Monthly completed jobs</span><strong>${gap.monthly}</strong></div>
          <div class="signal"><span>Currently published</span><strong>${state.publishRate}%</strong></div>
          <div class="signal"><span>Photos captured</span><strong>${state.captureRate}%</strong></div>
          <div class="signal"><span>Priority</span><strong>${esc(state.priorities[0] || "Visibility")}</strong></div>
        </div>
        <p class="disclaimer">Illustrative estimate based on call inputs. It measures potential proof inventory — not leads, rankings, or revenue.</p>
      </div>
    </div>
  </section>`;
  }

  const engineSteps = [
    { title: "Capture", short: "App or completed-job automation", detail: "One check-in starts the system.", bullets: ["Technician-first mobile photo check-in", "CRM-triggered check-in from completed jobs", `Works with ${integrationLine()}`] },
    { title: "Optimize", short: "Built for local search — not generic posts", detail: "Unlike most platforms, JobCapturePro builds local SEO into every job before it publishes.", bullets: ["Images geotagged to the actual job location", "SEO-ready titles, tags, descriptions, alt text, and schema", "WebP optimization and clean filenames for search and AI"] },
    { title: "Distribute", short: "Publish where local buyers (and AI) look", detail: "One real job supports four publish channels — with website content optimized for local search.", bullets: ["Website / WordPress: geotagged, local-SEO job pages", "Google Business Profile / Maps posts that drive calls", "Facebook, Instagram, and X scheduling", "JobCapturePro directory listing"] },
    { title: "Convert", short: "Ask for the review on site", detail: "Before leaving, crew shows a QR code (or sends a link) — the credibility signal AI search and neighbors both trust.", bullets: ["On-site QR handoff while the customer is happiest", "Optional review link if they prefer", "More reviews without awkward office follow-ups"] },
  ];

  function renderEngine() {
    const detail = engineSteps[selectedEngine];
    return `<section class="chapter content-pad">
    ${chapterHeader(5, "How it works", "One job in. Proof everywhere.", "JobCapturePro turns completed work into local Maps visibility, geotagged website content, reviews, and social — the proof today’s search engines and AI answers actually value.")}
    <div class="engine">
      <div class="engine-flow">${engineSteps.map((step, i) => `<button class="engine-step ${selectedEngine === i ? "active" : ""}" data-engine="${i}" type="button"><span class="step-no">0${i + 1}</span><h3>${step.title}</h3><p>${step.short}</p></button>`).join("")}</div>
      <div class="engine-detail"><strong>${detail.detail}</strong><ul>${detail.bullets.map((x) => `<li>${x}</li>`).join("")}</ul></div>
    </div>
  </section>`;
  }

  const acculevelMarkets = {
    triadelphia: {
      label: "Triadelphia, WV",
      preview: img("acculevel-localfalcon-triadelphia-scans-preview.webp"),
      previewFallback: img("acculevel-localfalcon-triadelphia-scans-preview.jpg"),
      full: img("acculevel-localfalcon-triadelphia-scans.webp"),
      fullFallback: img("acculevel-localfalcon-triadelphia-scans.jpg"),
      headline: "0% → 100%",
      detail: "LocalFalcon scans for basement waterproofing and foundation repair moved from all-red, 0% Share of Local Voice in March 2026 to predominantly green coverage by June — with tracked searches reaching as high as 100% SoLV.",
    },
    monroe: {
      label: "Monroe, MI",
      preview: img("acculevel-localfalcon-monroe-scans-preview.webp"),
      previewFallback: img("acculevel-localfalcon-monroe-scans-preview.jpg"),
      full: img("acculevel-localfalcon-monroe-scans.webp"),
      fullFallback: img("acculevel-localfalcon-monroe-scans.jpg"),
      headline: "0% → ~96%",
      detail: "Monroe LocalFalcon scans move from all-red 0% SoLV in March 2026 to strong green coverage by June — latest supplied scans reached about 96% SoLV for basement waterproofing and ~84% for foundation repair.",
    },
  };

  function stars(n = 5) {
    return "★★★★★".slice(0, Math.max(0, Math.min(5, n)));
  }

  function renderProof() {
    const market = acculevelMarkets[selectedCaseMarket];
    const leadLift = state.acculevelLeadLift === "" ? null : Number(state.acculevelLeadLift);
    const reviewCards = reviews
      .map(
        (r) => `<article class="review-card">
        <div class="review-card-top"><strong>${esc(r.name)}</strong><span>${esc(r.role || "")}</span></div>
        <div class="review-stars" aria-label="${r.rating || 5} stars">${stars(r.rating || 5)}</div>
        <p>“${esc(r.quote)}”</p>
      </article>`
      )
      .join("");

    const acculevel = state.showAcculevel
      ? `<div class="case-layout case-layout--compact">
      <div class="case-story">
        <div class="case-logo">Accu<span>level</span></div>
        <div class="case-number">111</div><div class="case-number-label">locations installed with JobCapturePro</div>
        <p class="case-copy">Selected LocalFalcon scans moved from all-red, 0% Share of Local Voice grids to predominantly green coverage. The strongest tracked search reached 100% Share of Local Voice.</p>
        <div class="case-proof-note">${leadLift === null ? "Verified result: expanded Google Maps visibility. Direct lead lift is not claimed unless separately verified." : `<strong>Verified lead impact:</strong> +${leadLift}% leads during the measured period.`}</div>
      </div>
      <div class="case-visual">
        <div class="case-tabs">${Object.entries(acculevelMarkets)
          .map(([key, item]) => `<button class="case-tab ${selectedCaseMarket === key ? "active" : ""}" data-case-market="${key}" type="button">${item.label}</button>`)
          .join("")}</div>
        <button class="scan-preview" type="button" data-lightbox-src="${market.full}" data-lightbox-fallback="${market.fullFallback}" aria-label="Open ${market.label} LocalFalcon scans full size">
          <picture>
            <source srcset="${market.preview}" type="image/webp" />
            <img src="${market.previewFallback}" alt="${market.label} LocalFalcon scan history — click to enlarge" loading="lazy" />
          </picture>
          <span class="scan-preview-hint">Click to enlarge</span>
        </button>
        <div class="case-metric-row"><div class="case-metric"><span>Starting visibility</span><b>0% SoLV</b></div><div class="case-metric"><span>Latest high</span><b class="good">${market.headline.split("→")[1].trim()}</b></div><div class="case-metric"><span>Rollout</span><b>111 locations</b></div></div>
        <div class="case-source"><span>${market.detail}</span><button class="link-button" type="button" data-lightbox-src="${market.full}" data-lightbox-fallback="${market.fullFallback}">View full LocalFalcon scans ↗</button></div>
      </div>
    </div>`
      : "";

    return `<section class="chapter content-pad">
    ${chapterHeader(6, "Customer proof", "Real work. Real Maps. Real results.", "Operators and agencies who turned job proof into local visibility. Acculevel LocalFalcon scans show what consistent Maps coverage can look like.")}
    <div class="reviews-grid">${reviewCards}</div>
    ${acculevel}
  </section>`;
  }

  const segments = {
    owner: { label: "Owner-operator", range: "$0–$2M", title: "Get the job off the camera roll and onto Maps and local search.", story: "Capture proof from the field, publish geotagged website content, show up on Google, and build the credibility AI search and neighbors trust — without another marketing chore.", points: [["Best for", "Local Maps visibility and owned proof"], ["You’ll use", "Mobile check-in + local-SEO website publish"], ["Common win", "Photos finally turn into inbound leads"]] },
    growth: { label: "Growth-stage", range: "$2M–$10M", title: "Standardize local SEO, Maps posts, and reviews before growth leaks proof.", story: "Connect every completed job to Maps, geotagged website content, social, directory, and an on-site review ask — so local search keeps compounding as you scale.", points: [["Best for", "Consistent local SEO across every job"], ["You’ll use", "CRM integrations, geotagged site proof, GBP, QR reviews"], ["Common win", "Marketing stops depending on spare time"]] },
    enterprise: { label: "Multi-location", range: "$10M+", title: "Make every market visible on Maps without losing control.", story: "Centralize proof generation, local SEO publishing, integrations, and reporting while each location keeps showing real work in its own market.", points: [["Best for", "Multi-market Maps + local SEO control"], ["You’ll use", "Org access, custom integration, reporting"], ["Common win", "Every market stays active without a content team"]] },
  };

  function renderFit() {
    const fit = segments[state.segment];
    return `<section class="chapter content-pad">
    ${chapterHeader(7, "Right fit", "Match the story to how you operate.", "Pick the stage that sounds most like your business. The workflow stays the same — the emphasis changes.")}
    <div class="fit-layout">
      <div class="segment-tabs">${Object.entries(segments)
        .map(([key, item]) => `<button type="button" data-segment="${key}" class="segment-tab ${state.segment === key ? "active" : ""}"><strong>${item.label}</strong><span>${item.range}</span></button>`)
        .join("")}</div>
      <div class="fit-story"><h3>${fit.title}</h3><p>${fit.story}</p><div class="fit-points">${fit.points.map((point) => `<div class="fit-point"><span>${point[0]}</span><strong>${point[1]}</strong></div>`).join("")}</div></div>
    </div>
  </section>`;
  }

  function renderPlan() {
    const plan = recommendPlan();
    const pricingLink = cfg.pricingUrl || "/pricing/";
    const referralUrl = "/referral-program/";
    const rows = commissionRows();
    const commissionTable = `<div class="commission-table" role="table" aria-label="Commission examples">
        <div class="commission-row commission-row--head" role="row"><span>Plan</span><span>Price</span><span>Your cut</span><span>Term</span></div>
        ${rows.map((r) => `<div class="commission-row" role="row"><span>${r.plan}</span><span>$${r.monthly}/mo</span><span>${r.monthlyPay}/mo</span><span>${r.term}</span></div>`).join("")}
      </div>`;

    let aside;
    if (isAffiliate()) {
      aside = `<aside class="recommendation">
        <span class="plan-kicker">Affiliate program</span><h3>20% for 12 months</h3>
        <p class="plan-reason">Share your referral link. When a contractor becomes a paid customer, you earn 20% recurring commission for 12 months on that account.</p>
        ${commissionTable}
        <p class="plan-note">Commissions apply to active paid customers under program terms. Prices stay current on <a href="${esc(pricingLink)}" target="_blank" rel="noopener">our pricing page</a>.</p>
        <a class="plan-cta" href="${esc(referralUrl)}" target="_blank" rel="noopener">Join the referral program →</a>
      </aside>`;
    } else if (isPartner()) {
      aside = `<aside class="recommendation">
        <span class="plan-kicker">Strategic partner</span><h3>15% for the life of the account</h3>
        <p class="plan-reason">Partners who sell and support JobCapturePro with clients earn <strong>15% recurring commission for as long as the customer remains an active paid account</strong> — healthier than 20% forever, stronger than a 12-month affiliate cut when you’re doing the heavy lifting.</p>
        ${commissionTable}
        <div class="partner-path" style="margin-top:16px"><div class="partner-step"><span>Phase 01</span><h4>Pilot</h4><p>Select a focused client cohort and confirm integrations.</p></div><div class="partner-step"><span>Phase 02</span><h4>Prove</h4><p>Track check-ins, publishing, reviews, and Maps visibility.</p></div><div class="partner-step"><span>Phase 03</span><h4>Expand</h4><p>Turn the working motion into a repeatable partner offer.</p></div></div>
        <p class="plan-note">Enhanced partner terms are scoped by fit and volume. Apply via the referral program and note you’re a strategic partner.</p>
        <a class="plan-cta" href="${esc(referralUrl)}" target="_blank" rel="noopener">Apply as a partner →</a>
      </aside>`;
    } else {
      aside = `<aside class="recommendation">
        <span class="plan-kicker">Recommended fit</span><h3>${plan.name}</h3>${state.showPricing ? `<p class="price">${plan.price} <span>/ month</span></p>` : ""}<p class="plan-reason">${plan.reason}</p>
        <ul class="included">${(plan.includes || []).map((x) => `<li>${esc(x)}</li>`).join("")}</ul>
        <p class="plan-note">${state.showPricing ? `Additional locations: $${cfg.extraLocationFee || 100} each. ` : ""}Prices stay current on <a href="${esc(pricingLink)}" target="_blank" rel="noopener">our pricing page</a>.</p>
        <a class="plan-cta" href="${esc(cta.primaryUrl || pricingLink)}" target="_blank" rel="noopener">${esc(cta.primaryLabel || "Start free 14-day trial")} →</a>
      </aside>`;
    }

    const headline = isAffiliate()
      ? "Affiliate economics"
      : isPartner()
        ? "Partner economics"
        : "Recommended plan";
    const sub = isAffiliate()
      ? "Earn when contractors you refer become customers."
      : isPartner()
        ? "Earn while you sell and support the proof engine."
        : "Match the plan to your workflow.";
    const lead = isAffiliate()
      ? "Simple referral economics — 20% recurring for 12 months on paid accounts."
      : isPartner()
        ? "For agencies and consultants doing real selling: residual commission while the customer stays active."
        : "We’ll recommend a plan based on locations and how automated you want this. Prices stay current on our pricing page.";

    return `<section class="chapter content-pad">
    ${chapterHeader(8, isAffiliate() || isPartner() ? "Earn with JCP" : "Plan", headline, sub + " " + lead)}
    <div class="plan-layout">
      <div class="plan-controls ${isChannelPartner() ? "rep-only" : ""}">
        <div class="field"><span class="field-label">Company stage</span><div class="choice-row">${Object.entries(segments)
          .map(([key, item]) => radioChoice("planSegment", key, item.label, state.segment === key))
          .join("")}</div></div>
        <div class="field"><label for="planLocations">Locations</label><input id="planLocations" type="number" min="1" max="500" value="${state.locations}"></div>
        <div class="field"><span class="field-label">Workflow</span><div class="choice-row">${checkboxChoice("automation", "yes", "Automate from CRM", state.automation)}${checkboxChoice("customIntegration", "yes", "Custom integration / API", state.customIntegration)}</div></div>
        <div class="field"><span class="field-label">Important outcomes</span><div class="choice-row">${["Local visibility", "More reviews", "Faster follow-up", "Consistent content", "Multi-location control"].map((x) => checkboxChoice("planPriorities", x, x, state.priorities.includes(x))).join("")}</div></div>
      </div>
      ${aside}
    </div>
  </section>`;
  }

  const objections = [
    { title: "We already have a CRM", answer: "Keep it. JobCapturePro doesn’t replace scheduling — it turns completed-job activity into local Maps posts, geotagged website content, and reviews most CRMs never publish. We integrate with Housecall Pro, Jobber, ServiceTitan, CompanyCam, and more." },
    { title: "Will my techs actually use it?", answer: "It’s built around a simple photo check-in. The review is a QR they show before leaving — seconds on site, not another office task next week." },
    { title: "We already post on social", answer: "Helpful — social is one of four publish channels. The bigger opportunity is local SEO on your website (geotagged job images), Google Maps updates, directory presence, and an on-site review ask from the same job." },
    { title: "Does this guarantee rankings?", answer: "No honest platform can. What you get is a steady supply of what today’s search — including AI answers — actually rewards: real jobs, geotagged proof, reviews, and local credibility. Results still depend on your market, site, and execution." },
    { title: "Is this worth the cost?", answer: "Compare it to the manual work it replaces and the unused proof inventory we just looked at. You can start with a free 14-day trial — no credit card — and pricing stays current on our pricing page." },
    { title: "We don’t have time for another tool", answer: "That’s exactly why it ties to job completion. Capture happens with the work; the QR review takes seconds before you leave." },
  ];

  function renderObjections() {
    const item = objections[selectedObjection];
    return `<section class="chapter content-pad">
    ${chapterHeader(9, "Common questions", "Straight answers.", "These are the questions we hear most from contractors and partners.")}
    <div class="objection-layout">
      <div class="objection-list">${objections.map((x, i) => `<button type="button" data-objection="${i}" class="objection-btn ${selectedObjection === i ? "active" : ""}">${x.title}</button>`).join("")}</div>
      <div class="objection-response"><span class="eyebrow">Answer</span><h3>${item.title}</h3><p class="say-this">${item.answer}</p></div>
    </div>
  </section>`;
  }

  function recapText() {
    const company = state.prospectName || "Prospect";
    const gap = calcGap();
    const plan = recommendPlan();
    const priorities = state.priorities.length ? state.priorities.join(", ") : "visibility and proof consistency";
    const path = state.mode === "partner" ? "Partner pilot." : `${plan.name}${state.showPricing ? ` at ${plan.price}/month` : ""}. ${plan.reason}`;
    return `JobCapturePro ${state.mode === "partner" ? "partner" : "sales"} recap — ${company}\n\nWhat we heard\n${company} completes about ${gap.monthly} jobs per month. Roughly ${gap.unused} may not be consistently published as public proof. Priorities: ${priorities}.\n\nRecommended path\n${path}\n\nNext step\n${state.nextStep || defaults.nextStep}${state.followUpDate ? ` Target: ${state.followUpDate}.` : ""}\n\nNotes\n${state.salesNotes || "No additional notes."}\n`;
  }

  async function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return;
    }
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.setAttribute("readonly", "");
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    textarea.remove();
  }

  function renderClose() {
    const company = state.prospectName || (isChannelPartner() ? "your network" : "your business");
    const gap = calcGap();
    const plan = recommendPlan();
    const today = new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(new Date());
    const logo = (cfg.presenter && cfg.presenter.logoUrl) || img("jcp-logo-dark.png");
    let path;
    let pathDetail;
    let primaryHref = cta.primaryUrl || "#";
    let primaryLabel = cta.primaryLabel || "Start free 14-day trial";
    let secondaryHref = cta.secondaryUrl || cfg.pricingUrl || "/pricing/";
    let secondaryLabel = cta.secondaryLabel || "See live pricing";

    if (isAffiliate()) {
      path = "Affiliate referral program";
      pathDetail = "Earn 20% recurring commission for 12 months on paid referrals.";
      primaryHref = "/referral-program/";
      primaryLabel = "Join the referral program";
      secondaryHref = "/demo/";
      secondaryLabel = "Share the live demo";
    } else if (isPartner()) {
      path = "Strategic partner path";
      pathDetail = "15% recurring for as long as referred customers stay active — apply as a partner and scope the rollout.";
      primaryHref = "/referral-program/";
      primaryLabel = "Apply as a partner";
      secondaryHref = cfg.pricingUrl || "/pricing/";
      secondaryLabel = "See live pricing";
    } else {
      path = plan.name;
      pathDetail = plan.reason;
    }

    return `<section class="chapter content-pad">
    ${chapterHeader(10, "Next steps", "A clear path forward.", isAffiliate() ? "Get your referral link and start sharing JobCapturePro with contractors who already take job photos." : isPartner() ? "Apply as a strategic partner, pilot one client workflow, then expand." : "Here’s a one-page summary of what we covered — plus a free trial when you’re ready.")}
    <div class="close-layout">
      <div class="close-notes rep-only">
        <div class="field"><label for="nextStep">Agreed next step</label><textarea id="nextStep">${esc(state.nextStep)}</textarea></div>
        <div class="field"><label for="followUpDate">Target date</label><input id="followUpDate" type="date" value="${esc(state.followUpDate)}"></div>
        <div class="field"><label for="salesNotes">Call notes</label><textarea id="salesNotes" placeholder="Decision criteria, stakeholders, integration questions…">${esc(state.salesNotes)}</textarea></div>
      </div>
      <article class="recap" id="recap">
        <div class="recap-top"><div class="brand"><img src="${logo}" alt="JobCapturePro" /></div><span class="recap-date">${today}</span></div>
        <h3>${esc(state.prospectName || (isAffiliate() ? "Affiliate" : isPartner() ? "Partner" : "Your business"))} · summary</h3>
        <p class="recap-lede">Real jobs become local Maps visibility, geotagged website proof, reviews, and social — credibility Google, neighbors, and AI search all reward.</p>
        ${isChannelPartner() ? "" : `<div class="recap-metrics">
          <div class="recap-metric"><span>Jobs / month</span><strong>${gap.monthly}</strong></div>
          <div class="recap-metric"><span>Underused proof</span><strong>${gap.unused}</strong></div>
          <div class="recap-metric recap-metric--accent"><span>Recommended</span><strong>${esc(path)}</strong></div>
        </div>`}
        <h4>What we covered</h4><p>${isChannelPartner() ? `JobCapturePro turns completed ${esc(state.trade.toLowerCase())} jobs into local Maps visibility, geotagged website content, social, and directory proof — plus an on-site QR review ask.` : `${esc(company)} completes about <strong>${gap.monthly} jobs per month</strong>. Roughly <strong>${gap.unused}</strong> may not be consistently published as public proof today — proof that could strengthen local Maps presence, website SEO, and reviews.`} Focus areas: ${esc(state.priorities.join(", ") || "visibility and proof consistency")}.</p>
        <h4>Recommended path</h4><p><strong>${path}${!isChannelPartner() && state.showPricing ? ` · ${plan.price}/month` : ""}.</strong> ${pathDetail}</p>
        <div class="recap-why"><strong>Why this matters now:</strong> Local Maps drives calls. Website content is geotagged to the job and optimized for local search. AI-era search values real work, real reviews, and real credibility.</div>
        <h4>Suggested next step</h4><p id="recapNextStep">${esc(state.nextStep || defaults.nextStep)}${state.followUpDate ? ` Target: ${esc(state.followUpDate)}.` : ""}</p>
        <div id="recapNotesWrap" class="rep-only" ${state.salesNotes ? "" : "hidden"}><h4>Notes</h4><p id="recapNotes">${esc(state.salesNotes)}</p></div>
        <div class="recap-ctas">
          <a class="plan-cta" href="${esc(primaryHref)}" target="_blank" rel="noopener">${esc(primaryLabel)}</a>
          <a class="plan-cta plan-cta--secondary" href="${esc(secondaryHref)}" target="_blank" rel="noopener">${esc(secondaryLabel)}</a>
          <button class="plan-cta plan-cta--secondary" type="button" id="downloadPdfBtn">Download PDF summary</button>
        </div>
        <div class="recap-actions"><button class="copy-btn" id="copyRecap" type="button">Copy summary</button></div>
      </article>
    </div>
  </section>`;
  }

  function leavebehindPayload() {
    const gap = calcGap();
    const plan = recommendPlan();
    const today = new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(new Date());
    const company = state.prospectName || (isAffiliate() ? "Affiliate partner" : isPartner() ? "Strategic partner" : "Your business");
    const logo = (cfg.presenter && cfg.presenter.logoUrl) || img("jcp-logo-dark.png");
    const trialUrl = cta.primaryUrl || cfg.pricingUrl || "https://jobcapturepro.com/pricing/";
    const pricingUrl = cfg.pricingUrl || "https://jobcapturepro.com/pricing/";
    let path = plan.name;
    let pathDetail = plan.reason;
    let priceLine = state.showPricing ? `${plan.price}/month` : "";
    let primaryLabel = cta.primaryLabel || "Start free 14-day trial";
    if (isAffiliate()) {
      path = "Affiliate referral program";
      pathDetail = "Earn 20% recurring commission for 12 months on paid referrals.";
      priceLine = "20% × 12 months";
      primaryLabel = "Join the referral program";
    } else if (isPartner()) {
      path = "Strategic partner path";
      pathDetail = "15% recurring for as long as referred customers stay active.";
      priceLine = "15% residual";
      primaryLabel = "Apply as a partner";
    }
    const includes = !isChannelPartner() && Array.isArray(plan.includes) ? plan.includes.slice(0, 5) : [
      "Geotagged images at the real job location",
      "Website content optimized for local search",
      "Google Maps / Business Profile publishing",
      "On-site QR review requests",
      "Social + directory distribution",
    ];
    return { gap, plan, today, company, logo, trialUrl, pricingUrl, path, pathDetail, priceLine, primaryLabel, includes };
  }

  function buildLeavebehindHtml() {
    const d = leavebehindPayload();
    const titleName = (state.prospectName || "JobCapturePro").replace(/[^\w\s-]/g, "").trim() || "JobCapturePro";
    const covered = isChannelPartner()
      ? `JobCapturePro turns completed ${esc(state.trade.toLowerCase())} jobs into local Maps visibility, geotagged website content, social, and directory proof — plus an on-site QR review ask.`
      : `${esc(d.company)} completes about <strong>${d.gap.monthly} jobs per month</strong>. Roughly <strong>${d.gap.unused}</strong> may not be consistently published as public proof today.`;
    const priorities = esc(state.priorities.join(", ") || "Local visibility, reviews, and consistent proof");
    const next = esc(state.nextStep || defaults.nextStep) + (state.followUpDate ? ` Target: ${esc(state.followUpDate)}.` : "");
    const notes = state.salesNotes ? `<section class="lb-block"><h2>Notes</h2><p>${esc(state.salesNotes)}</p></section>` : "";
    const includes = d.includes.map((x) => `<li>${esc(x)}</li>`).join("");
    const presented = state.repName ? `Presented by ${esc(state.repName)}` : "Prepared with JobCapturePro";
    const modeLabel = isAffiliate() ? "Affiliate" : isPartner() ? "Partner" : "Contractor";

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>JobCapturePro — ${esc(titleName)} summary</title>
<style>
  @page { size: letter; margin: 0.42in; }
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  body {
    font-family: "DM Sans", "Segoe UI", Helvetica, Arial, sans-serif;
    color: #111827;
    background: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .sheet { max-width: 8.5in; margin: 0 auto; }
  .accent { height: 8px; background: linear-gradient(90deg, #ff5036 0%, #ff7a66 55%, #111827 100%); }
  .top {
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    padding: 22px 0 16px; border-bottom: 2px solid #111827;
  }
  .top img { display: block; height: 28px; width: auto; }
  .top-meta { text-align: right; font-size: 11px; color: #6b7280; line-height: 1.45; }
  .top-meta strong { display: block; color: #111827; font-size: 12px; }
  .hero { padding: 22px 0 8px; }
  .eyebrow {
    display: inline-block; margin: 0 0 10px; padding: 4px 9px;
    border-radius: 999px; background: #fff2ef; color: #c2410c;
    font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
  }
  h1 {
    margin: 0 0 10px; font-family: Manrope, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 32px; line-height: 1.12; letter-spacing: -0.03em;
  }
  .lede { margin: 0; max-width: 6.6in; color: #4b5563; font-size: 14px; line-height: 1.55; }
  .metrics {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
    margin: 22px 0 8px;
  }
  .metric {
    padding: 14px 14px 12px; border: 1px solid #e5e7eb; border-radius: 12px;
    background: #f9fafb;
  }
  .metric span {
    display: block; margin-bottom: 6px; color: #6b7280;
    font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
  }
  .metric strong {
    display: block; font-family: Manrope, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 26px; line-height: 1.1; letter-spacing: -0.03em; color: #111827;
  }
  .metric em { display: block; margin-top: 4px; font-style: normal; color: #6b7280; font-size: 11px; }
  .metric.accent-card { background: #111827; border-color: #111827; color: #fff; }
  .metric.accent-card span { color: #ffb4a8; }
  .metric.accent-card strong, .metric.accent-card em { color: #fff; }
  .grid { display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 18px; margin-top: 18px; }
  .lb-block h2 {
    margin: 0 0 8px; color: #2563eb; font-size: 11px; font-weight: 800;
    letter-spacing: .08em; text-transform: uppercase;
  }
  .lb-block p { margin: 0; color: #374151; font-size: 13px; line-height: 1.55; }
  .lb-block p strong { color: #111827; }
  .plan-card {
    padding: 16px; border-radius: 14px; background: #111827; color: #f5f6f1;
  }
  .plan-card .kicker { color: #ff7a66; font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
  .plan-card h3 {
    margin: 8px 0 4px; font-family: Manrope, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 28px; color: #fff; letter-spacing: -0.03em;
  }
  .plan-card .price { margin: 0 0 10px; color: #e5e7eb; font-size: 14px; font-weight: 700; }
  .plan-card p { margin: 0; color: #d1d5db; font-size: 12px; line-height: 1.5; }
  .plan-card ul { margin: 12px 0 0; padding: 0; list-style: none; }
  .plan-card li {
    position: relative; margin: 0 0 7px; padding-left: 16px;
    color: #f3f4f6; font-size: 12px; line-height: 1.4;
  }
  .plan-card li::before { content: "+"; position: absolute; left: 0; color: #ff5036; font-weight: 800; }
  .why {
    margin-top: 16px; padding: 14px 16px; border-left: 4px solid #ff5036;
    background: #fff7f5; border-radius: 0 12px 12px 0;
  }
  .why h2 { margin: 0 0 6px; color: #c2410c; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
  .why p { margin: 0; color: #374151; font-size: 12.5px; line-height: 1.5; }
  .next {
    margin-top: 16px; padding: 14px 16px; border: 1px solid #e5e7eb; border-radius: 12px;
  }
  .cta-bar {
    margin-top: 18px; padding: 14px 16px; display: flex; justify-content: space-between; gap: 16px; align-items: center;
    border-radius: 12px; background: #ff5036; color: #fff;
  }
  .cta-bar strong { display: block; font-size: 14px; }
  .cta-bar span { display: block; margin-top: 3px; font-size: 11px; opacity: .92; word-break: break-all; }
  .cta-bar .pill {
    flex: 0 0 auto; padding: 8px 12px; border-radius: 999px; background: #fff; color: #111827;
    font-size: 11px; font-weight: 800; white-space: nowrap;
  }
  .foot {
    margin-top: 14px; display: flex; justify-content: space-between; gap: 12px;
    color: #9ca3af; font-size: 10px;
  }
  @media print {
    body { background: #fff; }
    .sheet { max-width: none; }
  }
</style>
</head>
<body>
  <article class="sheet">
    <div class="accent"></div>
    <header class="top">
      <img src="${esc(d.logo)}" alt="JobCapturePro" />
      <div class="top-meta"><strong>${modeLabel} summary</strong>${esc(d.today)}</div>
    </header>
    <div class="hero">
      <span class="eyebrow">Leave-behind · one page</span>
      <h1>${esc(d.company)}</h1>
      <p class="lede">Real jobs become local Maps visibility, geotagged website proof, reviews, and social — the credibility Google, neighbors, and AI search answers all reward.</p>
    </div>
    <div class="metrics">
      <div class="metric"><span>Jobs / month</span><strong>${isChannelPartner() ? "—" : d.gap.monthly}</strong><em>${isChannelPartner() ? "Partner / affiliate overview" : "Completed volume discussed"}</em></div>
      <div class="metric"><span>Underused proof</span><strong>${isChannelPartner() ? "—" : d.gap.unused}</strong><em>${isChannelPartner() ? "Proof inventory opportunity" : "Not consistently published"}</em></div>
      <div class="metric accent-card"><span>Recommended</span><strong>${esc(d.path)}</strong><em>${esc(d.priceLine || "Custom fit")}</em></div>
    </div>
    <div class="grid">
      <div>
        <section class="lb-block">
          <h2>What we covered</h2>
          <p>${covered} Focus areas: <strong>${priorities}</strong>.</p>
        </section>
        <section class="why">
          <h2>Why this matters now</h2>
          <p>Local Maps coverage drives calls. Website content from JobCapturePro is optimized for local search — images geotagged to the actual job, SEO built in. AI-era search values exactly what you already create: real work, real proof, real reviews, real credibility.</p>
        </section>
        <section class="next lb-block" style="margin-top:16px">
          <h2>Suggested next step</h2>
          <p>${next}</p>
        </section>
        ${notes}
      </div>
      <aside class="plan-card">
        <div class="kicker">Recommended path</div>
        <h3>${esc(d.path)}</h3>
        <div class="price">${esc(d.priceLine || "Talk through fit")}</div>
        <p>${esc(d.pathDetail)}</p>
        <ul>${includes}</ul>
      </aside>
    </div>
    <div class="cta-bar">
      <div><strong>${esc(d.primaryLabel)}</strong><span>${esc(d.trialUrl)}</span></div>
      <div class="pill">No credit card · 14 days</div>
    </div>
    <footer class="foot">
      <span>${presented}</span>
      <span>Pricing: ${esc(d.pricingUrl)}</span>
    </footer>
  </article>
</body>
</html>`;
  }

  function downloadProspectPdf() {
    const html = buildLeavebehindHtml();
    const titleName = (state.prospectName || "summary").replace(/[^\w\s-]/g, "").trim().replace(/\s+/g, "-") || "summary";
    let frame = document.getElementById("leavebehindPrintFrame");
    if (!frame) {
      frame = document.createElement("iframe");
      frame.id = "leavebehindPrintFrame";
      frame.title = "PDF leave-behind";
      frame.setAttribute("aria-hidden", "true");
      frame.style.cssText = "position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;";
      document.body.appendChild(frame);
    }

    const prevTitle = document.title;
    document.title = `JobCapturePro-${titleName}-summary`;

    const win = frame.contentWindow;
    const doc = frame.contentDocument;
    if (!win || !doc) {
      document.body.classList.add("print-leavebehind");
      window.print();
      setTimeout(() => {
        document.body.classList.remove("print-leavebehind");
        document.title = prevTitle;
      }, 800);
      showToast("Choose “Save as PDF” in the print dialog");
      return;
    }

    doc.open();
    doc.write(html);
    doc.close();

    const runPrint = () => {
      try {
        win.focus();
        win.print();
      } catch (err) {
        document.body.classList.add("print-leavebehind");
        window.print();
        setTimeout(() => document.body.classList.remove("print-leavebehind"), 800);
      }
      showToast("Choose “Save as PDF” in the print dialog");
      setTimeout(() => {
        document.title = prevTitle;
      }, 1200);
    };

    const images = Array.from(doc.images || []);
    if (!images.length) {
      setTimeout(runPrint, 60);
      return;
    }
    Promise.all(
      images.map(
        (image) =>
          image.complete
            ? Promise.resolve()
            : new Promise((resolve) => {
                image.onload = resolve;
                image.onerror = resolve;
              })
      )
    ).then(() => setTimeout(runPrint, 80));
  }

  function render() {
    renderNav();
    const renderers = [renderCover, renderProblem, renderDiagnose, renderGap, renderEngine, renderProof, renderFit, renderPlan, renderObjections, renderClose];
    stage.innerHTML = renderers[state.chapter]();
    const prospectInput = $("#prospectName");
    if (prospectInput) prospectInput.value = state.prospectName;
    const metaLabel = document.querySelector(".call-meta label");
    if (metaLabel) metaLabel.textContent = isPartner() ? "Partner" : isAffiliate() ? "Affiliate" : "Prospect";
    document.querySelectorAll("[data-mode]").forEach((button) => button.classList.toggle("active", button.dataset.mode === state.mode));
    const list = chapterList();
    $("#progressLabel").textContent = `${String(state.chapter + 1).padStart(2, "0")} / ${String(list.length).padStart(2, "0")}`;
    $("#progressBar").style.width = `${((state.chapter + 1) / list.length) * 100}%`;
    $("#backBtn").disabled = state.chapter === 0;
    $("#backBtn").style.opacity = state.chapter === 0 ? ".35" : "1";
    const next = $("#nextBtn");
    next.disabled = state.chapter === list.length - 1;
    next.style.opacity = next.disabled ? ".35" : "1";
    next.querySelector("span").textContent = state.chapter === 0 ? "Continue" : state.chapter === list.length - 2 ? "See next steps" : "Next";
    document.body.classList.toggle("is-presenting", !!state.presenting);
    const presentBtn = $("#presentBtn");
    if (presentBtn) presentBtn.textContent = state.presenting ? "Exit present" : "Present";
    bindChapterEvents();
  }

  function updateArray(name, value, checked) {
    const set = new Set(state[name]);
    checked ? set.add(value) : set.delete(value);
    setState({ [name]: [...set] });
  }

  function bindChapterEvents() {
    stage.querySelectorAll("[data-go]").forEach((el) => el.addEventListener("click", () => goTo(Number(el.dataset.go))));
    if (state.chapter === 2) {
      $("#jobsPerWeek").addEventListener("input", (e) => setState({ jobsPerWeek: e.target.value }, false));
      $("#locations").addEventListener("input", (e) => setState({ locations: e.target.value }, false));
      $("#crm").addEventListener("change", (e) => setState({ crm: e.target.value }, false));
      $("#responseTime").addEventListener("change", (e) => setState({ responseTime: e.target.value }, false));
      $("#captureRate").addEventListener("input", (e) => {
        state.captureRate = e.target.value;
        $("#captureOutput").textContent = `${e.target.value}%`;
        saveState();
      });
      $("#publishRate").addEventListener("input", (e) => {
        state.publishRate = e.target.value;
        $("#publishOutput").textContent = `${e.target.value}%`;
        saveState();
      });
      stage.querySelectorAll('input[name="channels"]').forEach((el) => el.addEventListener("change", (e) => updateArray("channels", e.target.value, e.target.checked)));
      stage.querySelectorAll('input[name="priorities"]').forEach((el) => el.addEventListener("change", (e) => updateArray("priorities", e.target.value, e.target.checked)));
    }
    if (state.chapter === 4) stage.querySelectorAll("[data-engine]").forEach((el) => el.addEventListener("click", () => { selectedEngine = Number(el.dataset.engine); render(); }));
    if (state.chapter === 5) {
      stage.querySelectorAll("[data-case-market]").forEach((el) => el.addEventListener("click", () => { selectedCaseMarket = el.dataset.caseMarket; render(); }));
      stage.querySelectorAll("[data-lightbox-src]").forEach((el) =>
        el.addEventListener("click", () => openLightbox(el.dataset.lightboxSrc, el.dataset.lightboxFallback || el.dataset.lightboxSrc))
      );
    }
    if (state.chapter === 6) stage.querySelectorAll("[data-segment]").forEach((el) => el.addEventListener("click", () => setState({ segment: el.dataset.segment })));
    if (state.chapter === 7) {
      stage.querySelectorAll('input[name="planSegment"]').forEach((el) => el.addEventListener("change", (e) => setState({ segment: e.target.value })));
      $("#planLocations").addEventListener("change", (e) => setState({ locations: e.target.value }));
      const auto = stage.querySelector('input[name="automation"]');
      const custom = stage.querySelector('input[name="customIntegration"]');
      if (auto) auto.addEventListener("change", (e) => setState({ automation: e.target.checked }));
      if (custom) custom.addEventListener("change", (e) => setState({ customIntegration: e.target.checked }));
      stage.querySelectorAll('input[name="planPriorities"]').forEach((el) => el.addEventListener("change", (e) => updateArray("priorities", e.target.value, e.target.checked)));
    }
    if (state.chapter === 8) stage.querySelectorAll("[data-objection]").forEach((el) => el.addEventListener("click", () => { selectedObjection = Number(el.dataset.objection); render(); }));
    if (state.chapter === 9) {
      ["nextStep", "followUpDate", "salesNotes"].forEach((id) => {
        const el = $("#" + id);
        if (!el) return;
        el.addEventListener("input", (e) => {
          state[id] = e.target.value;
          saveState();
          const nextEl = $("#recapNextStep");
          if (nextEl) nextEl.textContent = `${state.nextStep || defaults.nextStep}${state.followUpDate ? ` Target: ${state.followUpDate}.` : ""}`;
          const notes = $("#recapNotes");
          if (notes) notes.textContent = state.salesNotes;
          const wrap = $("#recapNotesWrap");
          if (wrap) wrap.hidden = !state.salesNotes;
        });
      });
      const copyBtn = $("#copyRecap");
      if (copyBtn) copyBtn.addEventListener("click", async () => { await copyText(recapText()); showToast("Recap copied"); });
      const printBtn = $("#printRecap");
      if (printBtn) printBtn.addEventListener("click", downloadProspectPdf);
      const pdfBtn = $("#downloadPdfBtn");
      if (pdfBtn) pdfBtn.addEventListener("click", downloadProspectPdf);
    }
  }

  function showToast(message) {
    const toast = $("#toast");
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 1700);
  }

  function openLightbox(src, fallback) {
    const imgEl = $("#reportImage");
    const modal = $("#reportModal");
    if (!imgEl || !modal) return;
    imgEl.onerror = () => {
      if (fallback && imgEl.getAttribute("src") !== fallback) imgEl.src = fallback;
    };
    imgEl.src = src || fallback;
    modal.hidden = false;
  }

  function closeLightbox() {
    const modal = $("#reportModal");
    if (modal) modal.hidden = true;
  }

  function openCustomizer() {
    $("#settingProspect").value = state.prospectName;
    $("#settingRep").value = state.repName;
    $("#settingTrade").value = state.trade;
    $("#settingJobs").value = state.jobsPerWeek;
    $("#settingLocations").value = state.locations;
    $("#settingLeadLift").value = state.acculevelLeadLift;
    $("#settingPricing").checked = state.showPricing;
    $("#settingAcculevel").checked = state.showAcculevel;
    $("#customizer").classList.add("open");
    $("#customizer").setAttribute("aria-hidden", "false");
    $("#drawerBackdrop").hidden = false;
    document.body.classList.add("customizer-open");
  }

  function closeCustomizer() {
    $("#customizer").classList.remove("open");
    $("#customizer").setAttribute("aria-hidden", "true");
    $("#drawerBackdrop").hidden = true;
    document.body.classList.remove("customizer-open");
  }

  function applyCustomizer() {
    setState({
      prospectName: $("#settingProspect").value.trim(),
      repName: $("#settingRep").value.trim(),
      trade: $("#settingTrade").value.trim() || "Home services",
      jobsPerWeek: Number($("#settingJobs").value) || state.jobsPerWeek,
      locations: Number($("#settingLocations").value) || state.locations,
      acculevelLeadLift: $("#settingLeadLift").value,
      showPricing: $("#settingPricing").checked,
      showAcculevel: $("#settingAcculevel").checked,
    });
    closeCustomizer();
    showToast("Presentation updated");
  }

  function bindGlobal() {
    document.querySelectorAll("[data-go]").forEach((el) => {
      if (el.closest("#chapterStage")) return;
      el.addEventListener("click", (e) => {
        e.preventDefault();
        goTo(Number(el.dataset.go));
      });
    });
    nav.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-go]");
      if (btn) goTo(Number(btn.dataset.go));
    });
    $("#backBtn").addEventListener("click", () => goTo(state.chapter - 1));
    $("#nextBtn").addEventListener("click", () => goTo(state.chapter + 1));
    $("#prospectName").addEventListener("input", (e) => setState({ prospectName: e.target.value }, false));
    document.querySelectorAll("[data-mode]").forEach((btn) =>
      btn.addEventListener("click", () => setState({ mode: btn.dataset.mode }))
    );
    $("#customizeBtn").addEventListener("click", openCustomizer);
    $("#closeCustomizer").addEventListener("click", closeCustomizer);
    $("#drawerBackdrop").addEventListener("click", closeCustomizer);
    $("#applySettings").addEventListener("click", applyCustomizer);
    $("#resetBtn").addEventListener("click", () => {
      localStorage.removeItem(storageKey);
      state = { ...defaults, presenting: state.presenting };
      render();
      showToast("Reset to defaults");
    });
    $("#presentBtn").addEventListener("click", () => setState({ presenting: !state.presenting }));
    $("#closeReport")?.addEventListener("click", closeLightbox);
    $("#closeReportBackdrop")?.addEventListener("click", closeLightbox);
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        const modal = $("#reportModal");
        if (modal && !modal.hidden) {
          closeLightbox();
          return;
        }
        if (state.presenting) setState({ presenting: false });
      }
      if (e.key === "ArrowRight" && !e.target.matches("input, textarea, select")) goTo(state.chapter + 1);
      if (e.key === "ArrowLeft" && !e.target.matches("input, textarea, select")) goTo(state.chapter - 1);
    });
  }

  bindGlobal();
  render();
})();
