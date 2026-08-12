(() => {
  const cfg = window.JCP_SALES_TOOL || {};
  const assetBase = (cfg.assetBase || "").replace(/\/$/, "");
  const img = (name) => `${assetBase}/assets/${name}`;
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
    { id: "objections", label: "Objections" },
    { id: "close", label: "Close" },
  ];

  const prospectSeed = cfg.prospect || {};
  const defaults = {
    prospectName: prospectSeed.company || "",
    repName: (cfg.presenter && cfg.presenter.name) || "",
    mode: prospectSeed.mode === "partner" ? "partner" : "contractor",
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
    presenting: false,
  };

  let state = loadState();
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
    <span class="eyebrow">Chapter ${String(number).padStart(2, "0")} · ${eyebrow}</span>
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
        reason: "Custom connectivity, org-wide control, or a larger location footprint makes Enterprise the cleanest fit.",
        includes: (enterprise && enterprise.includes) || [],
      };
    }
    if (state.automation || Number(state.locations) > 1 || state.priorities.some((x) => ["More reviews", "Consistent content", "Multi-location control"].includes(x))) {
      return {
        id: "scale",
        name: (scale && scale.name) || "Scale",
        price: scale ? `$${scale.monthly}` : "$249",
        reason: "They need the proof engine to run consistently across completed jobs, reviews, social, and Google visibility.",
        includes: (scale && scale.includes) || [],
      };
    }
    return {
      id: "starter",
      name: (starter && starter.name) || "Starter",
      price: starter ? `$${starter.monthly}` : "$99",
      reason: "A simple mobile-led proof workflow is the most practical place to start for a single location.",
      includes: (starter && starter.includes) || [],
    };
  }

  function renderCover() {
    const partner = state.mode === "partner";
    const title = partner
      ? `Give every client a proof engine that creates <em>more calls.</em>`
      : `Turn every completed job into <em>more calls.</em>`;
    const company = state.prospectName || (partner ? "your clients" : "your team");
    return `<section class="chapter cover">
    <img class="cover-image" src="${img("jcp-product-visual.png")}" alt="JobCapturePro completed-job publishing workflow" />
    <div class="cover-copy">
      <span class="eyebrow">${partner ? "Partner presentation" : "Live sales presentation"}</span>
      <h1>${title}</h1>
      <p>${esc(company)} already takes job photos. JobCapturePro turns them into Google updates, website content, social posts, and directory listings — then your crew asks for a review on site with a QR code.</p>
      <p class="cover-integrations">Already on ${esc(integrationLine())}? We integrate with the tools crews already use.</p>
      <button class="start-btn" type="button" data-go="1">See the proof engine →</button>
    </div>
    <div class="cover-callout">${state.repName ? `Presented by ${esc(state.repName)} · ` : ""}${esc(state.prospectName || (partner ? "Partner overview" : "Prospect presentation"))}</div>
  </section>`;
  }

  function renderProblem() {
    const partner = state.mode === "partner";
    return `<section class="chapter content-pad">
    ${chapterHeader(2, "The missed opportunity", partner ? "Your clients already have the content." : "You already have the content.", partner ? `Completed jobs rarely become consistent, location-specific marketing for the ${esc(state.trade.toLowerCase())} clients you serve.` : `For ${esc(state.trade.toLowerCase())} teams, completed jobs rarely make it from the camera roll to the places customers decide who to call.`)}
    <div class="problem-layout">
      <div class="problem-statement">One real job can become proof in <em>five places.</em></div>
      <div class="channel-stack">
        <div class="channel-line"><strong>Google Maps</strong><span>Localized updates</span></div>
        <div class="channel-line"><strong>Your website</strong><span>Search-ready job proof</span></div>
        <div class="channel-line"><strong>Social media</strong><span>Consistent project content</span></div>
        <div class="channel-line"><strong>Directory</strong><span>Verified public listing</span></div>
        <div class="channel-line"><strong>Review requests</strong><span>On-site QR / link handoff</span></div>
      </div>
    </div>
    <div class="proof-strip"><div class="proof-stat"><b>1 photo</b><span>proof everywhere</span></div><div class="proof-stat"><b>4 channels</b><span>website, Google, social + directory</span></div><div class="proof-stat"><b>+ reviews</b><span>QR ask before you leave</span></div></div>
  </section>`;
  }

  function renderDiagnose() {
    const crmOptions = ["Housecall Pro", "Jobber", "ServiceTitan", "CompanyCam", "FieldEdge", "Workiz", "QuickBooks", "HighLevel", "Other / none"];
    const channels = ["Website", "Google Business Profile", "Facebook / Instagram", "Directory", "Review requests"];
    const priorities = ["Local visibility", "More reviews", "Faster follow-up", "Consistent content", "Multi-location control"];
    return `<section class="chapter content-pad">
    ${chapterHeader(3, "Diagnose", "Start with the work they already do.", "Mirror the Demo Assessment: where photos go, how proof gets published, and how reviews are asked for today.")}
    <div class="prompt-banner"><strong>Ask this first</strong><p>“Walk me through what happens to the photos after a job is finished — and how you ask for the review before you leave.”</p></div>
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
    ${chapterHeader(4, "Proof gap", "Make the invisible work visible.", "Completed jobs that could reinforce local trust — but currently leave no consistent public trail.")}
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
    { title: "Optimize", short: "Job context becomes search-ready proof", detail: "JCP prepares the job to be useful online.", bullets: ["SEO keyword, tags, and description", "Image filtering and priority selection", "WebP, filenames, alt text, coordinates, and schema"] },
    { title: "Distribute", short: "Publish proof where buyers look", detail: "One real job supports four publish channels.", bullets: ["Website / WordPress check-in display", "Google Business Profile posting", "Facebook, Instagram, and X scheduling", "JobCapturePro directory listing"] },
    { title: "Convert", short: "Ask for the review on site", detail: "Before leaving, crew shows a QR code (or sends a link) — the fifth place proof shows up.", bullets: ["On-site QR handoff while the customer is happiest", "Optional review link if they prefer", "More reviews without awkward office follow-ups"] },
  ];

  function renderEngine() {
    const detail = engineSteps[selectedEngine];
    return `<section class="chapter content-pad">
    ${chapterHeader(5, "How it works", "One job in. Proof everywhere.", "JobCapturePro connects job completion to the marketing work that usually gets delayed or forgotten.")}
    <div class="engine">
      <div class="engine-flow">${engineSteps.map((step, i) => `<button class="engine-step ${selectedEngine === i ? "active" : ""}" data-engine="${i}" type="button"><span class="step-no">0${i + 1}</span><h3>${step.title}</h3><p>${step.short}</p></button>`).join("")}</div>
      <div class="engine-detail"><strong>${detail.detail}</strong><ul>${detail.bullets.map((x) => `<li>${x}</li>`).join("")}</ul></div>
    </div>
  </section>`;
  }

  const acculevelMarkets = {
    triadelphia: {
      label: "Triadelphia, WV",
      image: img("acculevel-triadelphia-timeline.png"),
      report: img("acculevel-triadelphia-report.png"),
      headline: "0% → 100%",
      detail: "Share of Local Voice reached 100% for basement waterproofing; foundation repair reached approximately 89% in the latest supplied scan.",
    },
    monroe: {
      label: "Monroe, MI",
      image: img("acculevel-monroe-timeline.png"),
      report: img("acculevel-monroe-report.png"),
      headline: "0% → ~97%",
      detail: "Share of Local Voice reached approximately 97% for basement waterproofing; foundation repair reached approximately 84% in the latest supplied scan.",
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
        <div class="timeline-frame"><img src="${market.image}" alt="${market.label} LocalFalcon scan timeline" /><i class="timeline-gradient"></i></div>
        <div class="case-metric-row"><div class="case-metric"><span>Starting visibility</span><b>0% SoLV</b></div><div class="case-metric"><span>Latest high</span><b class="good">${market.headline.split("→")[1].trim()}</b></div><div class="case-metric"><span>Rollout</span><b>111 locations</b></div></div>
        <div class="case-source"><span>${market.detail}</span><button class="link-button" type="button" data-report="${market.report}">View source report ↗</button></div>
      </div>
    </div>`
      : "";

    return `<section class="chapter content-pad">
    ${chapterHeader(6, "Customer proof", "Operators and agencies are already winning with it.", "Short, high-trust snippets first. Acculevel Maps proof optional when the call needs local SEO evidence.")}
    <div class="reviews-grid">${reviewCards}</div>
    ${acculevel}
  </section>`;
  }

  const segments = {
    owner: { label: "Owner-operator", range: "$0–$2M", title: "Get the job off the camera roll and in front of the next customer.", story: "Capture proof from the field, publish it, and build a visible record of real work without another marketing chore.", points: [["Lead with", "Simplicity, local visibility, and owned proof"], ["Show", "Mobile check-in and website proof"], ["Listen for", "Feast-or-famine demand and unused photos"]] },
    growth: { label: "Growth-stage", range: "$2M–$10M", title: "Standardize proof and reviews before growth makes the leaks bigger.", story: "Connect completed jobs to four publish channels plus an on-site review ask — with less dependence on someone remembering each task.", points: [["Lead with", "Consistency and conversion support"], ["Show", "CRM integrations, QR reviews, GBP, social, directory"], ["Listen for", "Slow follow-up and inconsistent execution"]] },
    enterprise: { label: "Multi-location", range: "$10M+", title: "Make every location visible without losing control.", story: "Centralize proof generation, publishing, integrations, and reporting while each location stays connected.", points: [["Lead with", "Control and location-level visibility"], ["Show", "Org access, custom integration, reporting"], ["Listen for", "Fragmented execution across markets"]] },
  };

  function renderFit() {
    const fit = segments[state.segment];
    return `<section class="chapter content-pad">
    ${chapterHeader(7, "Right fit", "Tell the story at their altitude.", "Choose the operating stage that sounds most like the prospect.")}
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
    const partnerRollout =
      state.mode === "partner"
        ? `<aside class="recommendation">
        <span class="plan-kicker">Recommended motion</span><h3>Partner rollout</h3><p class="plan-reason">Prove one repeatable client workflow, document the result, then expand.</p>
        <div class="partner-path"><div class="partner-step"><span>Phase 01</span><h4>Pilot</h4><p>Select a focused cohort and confirm integrations.</p></div><div class="partner-step"><span>Phase 02</span><h4>Prove</h4><p>Track check-ins, publishing, reviews, and Maps visibility.</p></div><div class="partner-step"><span>Phase 03</span><h4>Expand</h4><p>Turn the working motion into a repeatable partner offer.</p></div></div>
        <p class="plan-note">Partner economics require a scoped proposal.</p>
      </aside>`
        : `<aside class="recommendation">
        <span class="plan-kicker">Recommended fit</span><h3>${plan.name}</h3>${state.showPricing ? `<p class="price">${plan.price} <span>/ month</span></p>` : ""}<p class="plan-reason">${plan.reason}</p>
        <ul class="included">${(plan.includes || []).map((x) => `<li>${esc(x)}</li>`).join("")}</ul>
        <p class="plan-note">${state.showPricing ? `Additional locations: $${cfg.extraLocationFee || 100} each. ` : ""}Prices stay current on <a href="${esc(pricingLink)}" target="_blank" rel="noopener">our pricing page</a>.</p>
        <a class="plan-cta" href="${esc(cta.primaryUrl || pricingLink)}" target="_blank" rel="noopener">${esc(cta.primaryLabel || "Start free 14-day trial")} →</a>
      </aside>`;
    return `<section class="chapter content-pad">
    ${chapterHeader(8, state.mode === "partner" ? "Partner rollout" : "Plan", state.mode === "partner" ? "Start focused. Prove the workflow. Expand." : "Match the plan to the workflow.", "Qualify for the operating need first. Price lands better after they see what they will actually use.")}
    <div class="plan-layout">
      <div class="plan-controls">
        <div class="field"><span class="field-label">Company stage</span><div class="choice-row">${Object.entries(segments)
          .map(([key, item]) => radioChoice("planSegment", key, item.label, state.segment === key))
          .join("")}</div></div>
        <div class="field"><label for="planLocations">Locations</label><input id="planLocations" type="number" min="1" max="500" value="${state.locations}"></div>
        <div class="field"><span class="field-label">Workflow</span><div class="choice-row">${checkboxChoice("automation", "yes", "Automate from CRM", state.automation)}${checkboxChoice("customIntegration", "yes", "Custom integration / API", state.customIntegration)}</div></div>
        <div class="field"><span class="field-label">Important outcomes</span><div class="choice-row">${["Local visibility", "More reviews", "Faster follow-up", "Consistent content", "Multi-location control"].map((x) => checkboxChoice("planPriorities", x, x, state.priorities.includes(x))).join("")}</div></div>
      </div>
      ${partnerRollout}
    </div>
  </section>`;
  }

  const objections = [
    { title: "We already have a CRM", answer: "Good. JCP is not asking you to replace the system that schedules jobs. It uses completed-job activity to create the proof and visibility most CRMs leave behind — and we integrate with tools like Housecall Pro, Jobber, ServiceTitan, and CompanyCam.", proof: "Confirm their CRM and show the capture path." },
    { title: "My techs won’t use it", answer: "The app is built around a simple photo check-in. Reviews are an on-site QR handoff that takes seconds before they leave — not another office task later.", proof: "Confirm photo habits and who owns the ask today." },
    { title: "We already post on social", answer: "Useful — but social is only one of four publish channels. The bigger opportunity is turning each real job into website proof, Google Business Profile activity, directory presence, social, and an on-site review ask.", proof: "Ask how often the same job reaches all four channels plus a review ask." },
    { title: "Will this guarantee rankings?", answer: "No responsible platform can guarantee rankings. JCP gives you a consistent supply of real, location-based job proof and the technical layer to publish it well.", proof: "Keep this measured; use GeoGrid reporting over time." },
    { title: "It feels expensive", answer: "Compare it to the manual workflow it replaces and the proof inventory currently going unused.", proof: "Use their proof-gap estimate. Do not invent a lead ROI." },
    { title: "We don’t have time", answer: "That is the problem. Marketing from completed work fails when it depends on spare time. JCP connects the work to job completion — including a quick QR review ask on site.", proof: "Clarify who captures photos and what can be automated." },
  ];

  function renderObjections() {
    const item = objections[selectedObjection];
    return `<section class="chapter content-pad">
    ${chapterHeader(9, "Objections", "Stay grounded. Show the mechanism.", "The strongest response is specific to their workflow and honest about what the product can and cannot guarantee.")}
    <div class="objection-layout">
      <div class="objection-list">${objections.map((x, i) => `<button type="button" data-objection="${i}" class="objection-btn ${selectedObjection === i ? "active" : ""}">${x.title}</button>`).join("")}</div>
      <div class="objection-response"><span class="eyebrow">Say this</span><h3>${item.title}</h3><p class="say-this">“${item.answer}”</p><p class="proof-check rep-only"><strong>Proof check:</strong> ${item.proof}</p></div>
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
    const company = state.prospectName || "this prospect";
    const gap = calcGap();
    const plan = recommendPlan();
    const today = new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(new Date());
    const path = state.mode === "partner" ? "Partner pilot" : plan.name;
    const pathDetail = state.mode === "partner" ? "Start with a focused client cohort, prove the workflow, then expand." : plan.reason;
    const logoSrc = (cfg.presenter && cfg.presenter.logoUrl) || img("jcp-logo-dark.png");
    return `<section class="chapter content-pad">
    ${chapterHeader(10, "Close", "Make the next step concrete.", "Confirm the problem, the right rollout, and one action that moves the deal forward.")}
    <div class="close-layout">
      <div class="close-notes rep-only">
        <div class="field"><label for="nextStep">Agreed next step</label><textarea id="nextStep">${esc(state.nextStep)}</textarea></div>
        <div class="field"><label for="followUpDate">Target date</label><input id="followUpDate" type="date" value="${esc(state.followUpDate)}"></div>
        <div class="field"><label for="salesNotes">Call notes</label><textarea id="salesNotes" placeholder="Decision criteria, stakeholders, integration questions…">${esc(state.salesNotes)}</textarea></div>
      </div>
      <article class="recap" id="recap">
        <div class="recap-top"><div class="brand"><img src="${logoSrc}" alt="JobCapturePro" /></div><span class="recap-date">${today}</span></div>
        <h3>${esc(state.prospectName || "Prospect")} · call recap</h3>
        <h4>What we heard</h4><p>${esc(company)} completes about <strong>${gap.monthly} jobs per month</strong>. Roughly <strong>${gap.unused}</strong> may not be consistently published as public proof. Priorities: ${esc(state.priorities.join(", ") || "visibility and proof consistency")}.</p>
        <h4>Recommended path</h4><p><strong>${path}${state.mode === "contractor" && state.showPricing ? ` · ${plan.price}/month` : ""}.</strong> ${pathDetail}</p>
        <h4>Next step</h4><p id="recapNextStep">${esc(state.nextStep || defaults.nextStep)}${state.followUpDate ? ` Target: ${esc(state.followUpDate)}.` : ""}</p>
        <div id="recapNotesWrap" class="rep-only" ${state.salesNotes ? "" : "hidden"}><h4>Notes</h4><p id="recapNotes">${esc(state.salesNotes)}</p></div>
        <div class="recap-ctas">
          <a class="plan-cta" href="${esc(cta.primaryUrl || "#")}" target="_blank" rel="noopener">${esc(cta.primaryLabel || "Start free 14-day trial")}</a>
          <a class="plan-cta plan-cta--secondary" href="${esc(cta.secondaryUrl || cfg.pricingUrl || "/pricing/")}" target="_blank" rel="noopener">${esc(cta.secondaryLabel || "See live pricing")}</a>
        </div>
        <div class="recap-actions rep-only"><button class="copy-btn" id="copyRecap" type="button">Copy recap</button><button class="print-btn" id="printRecap" type="button">Print / PDF</button></div>
      </article>
    </div>
  </section>`;
  }

  function render() {
    renderNav();
    const renderers = [renderCover, renderProblem, renderDiagnose, renderGap, renderEngine, renderProof, renderFit, renderPlan, renderObjections, renderClose];
    stage.innerHTML = renderers[state.chapter]();
    const prospectInput = $("#prospectName");
    if (prospectInput) prospectInput.value = state.prospectName;
    const metaLabel = document.querySelector(".call-meta label");
    if (metaLabel) metaLabel.textContent = state.mode === "partner" ? "Partner" : "Prospect";
    document.querySelectorAll("[data-mode]").forEach((button) => button.classList.toggle("active", button.dataset.mode === state.mode));
    const list = chapterList();
    $("#progressLabel").textContent = `${String(state.chapter + 1).padStart(2, "0")} / ${String(list.length).padStart(2, "0")}`;
    $("#progressBar").style.width = `${((state.chapter + 1) / list.length) * 100}%`;
    $("#backBtn").disabled = state.chapter === 0;
    $("#backBtn").style.opacity = state.chapter === 0 ? ".35" : "1";
    const next = $("#nextBtn");
    next.disabled = state.chapter === list.length - 1;
    next.style.opacity = next.disabled ? ".35" : "1";
    next.querySelector("span").textContent = state.chapter === 0 ? "Start the call" : state.chapter === list.length - 2 ? "Build recap" : "Next chapter";
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
      stage.querySelectorAll("[data-report]").forEach((el) => el.addEventListener("click", () => openReport(el.dataset.report)));
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
      if (printBtn) printBtn.addEventListener("click", () => window.print());
    }
  }

  function showToast(message) {
    const toast = $("#toast");
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 1700);
  }

  function openReport(src) {
    $("#reportImage").src = src;
    $("#reportModal").hidden = false;
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
    $("#customizer").setAttribute("aria-hidden", "false");
    $("#drawerBackdrop").hidden = false;
    document.body.classList.add("customizer-open");
  }

  function closeCustomizer() {
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
    $("#closeReport").addEventListener("click", () => {
      $("#reportModal").hidden = true;
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && state.presenting) setState({ presenting: false });
      if (e.key === "ArrowRight" && !e.target.matches("input, textarea, select")) goTo(state.chapter + 1);
      if (e.key === "ArrowLeft" && !e.target.matches("input, textarea, select")) goTo(state.chapter - 1);
    });
  }

  bindGlobal();
  render();
})();
