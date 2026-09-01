(() => {
  const cfg = window.JCP_SALES_TOOL || {};
  const assetBase = (cfg.assetBase || "").replace(/\/$/, "");
  const assetVer = "20260901a";
  const img = (name) => `${assetBase}/assets/${name}?v=${assetVer}`;
  const plans = cfg.plans || {};
  const reviews = Array.isArray(cfg.reviews) ? cfg.reviews : [];
  const cta = cfg.cta || {};
  // Bump storage key so stale local call state (old Maps copy / hidden case) resets once.
  const storageKey = (cfg.storageKey || "jcp-sales-call-live") + "-maps-v2";

  function planMonthly(id, fallback) {
    const plan = plans[id];
    return plan && plan.monthly != null ? Number(plan.monthly) : fallback;
  }

  function extraFee(id, fallback) {
    const fees = cfg.extraLocationFees || {};
    if (fees[id] != null && fees[id] !== "") return Number(fees[id]);
    if (id === "scale" && cfg.extraLocationFee != null) return Number(cfg.extraLocationFee);
    return fallback;
  }

  const baseChapters = [
    { id: "cover", label: "Opening" },
    { id: "problem", label: "The problem" },
    { id: "diagnose", label: "Diagnose" },
    { id: "gap", label: "Proof gap" },
    { id: "engine", label: "How it works" },
    { id: "demo", label: "Live demo" },
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
    nextStep: "Start Free Trial and connect one real job workflow.",
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
    return baseChapters.map((chapter) => {
      if (chapter.id === "plan" && isChannelPartner()) {
        return { ...chapter, label: isAffiliate() ? "Earn" : "Partner" };
      }
      if (chapter.id === "diagnose" && isChannelPartner()) {
        return { ...chapter, label: "Example" };
      }
      return chapter;
    });
  }

  function chapterNum(id) {
    const idx = chapterList().findIndex((chapter) => chapter.id === id);
    return idx >= 0 ? idx + 1 : 1;
  }

  function currentChapter() {
    return chapterList()[state.chapter] || null;
  }

  /** Map free-text trade to a demo niche key the live demo understands. */
  function nicheFromTrade(trade) {
    const t = String(trade || "").toLowerCase();
    if (/plumb|drain|water.?heat|pipe|sewer/.test(t)) return "plumbing";
    if (/hvac|heat(?:ing)?|air.?cond|furnace|cool(?:ing)?/.test(t)) return "hvac";
    if (/electr/.test(t)) return "electrical";
    if (/roof/.test(t)) return "roofing";
    if (/landscap|lawn|outdoor|tree/.test(t)) return "outdoor";
    if (/clean|janitor|maid/.test(t)) return "cleaning";
    if (/remodel|renovat|carpentr|build/.test(t)) return "remodel";
    if (/restor|water.?dam|mold|fire.?dam/.test(t)) return "restoration";
    return "";
  }

  function seedDemoUserForEmbed() {
    const niche = nicheFromTrade(state.trade);
    const company = String(state.prospectName || "").trim() || (isChannelPartner() ? "Sample Contractor" : "Your Business");
    const rep = String(state.repName || "").trim();
    const firstName = rep ? rep.split(/\s+/)[0] : "Alex";
    const user = {
      firstName,
      lastName: "",
      businessName: company,
      niche: niche || "plumbing",
      email: "",
      goals: Array.isArray(state.priorities) ? state.priorities.slice(0, 3) : [],
    };
    try {
      localStorage.setItem("demoUser", JSON.stringify(user));
      sessionStorage.setItem("jcp_demo_intake_complete", "1");
    } catch (e) {
      // no-op
    }
    return user;
  }

  function buildDemoEmbedUrl() {
    const user = seedDemoUserForEmbed();
    const base = (cfg.demoRunUrl || siteUrl("/demo/")).split("?")[0];
    const url = new URL(base, window.location.origin);
    url.searchParams.set("mode", "run");
    url.searchParams.set("embed", "1");
    url.searchParams.set("source", "sales_deck");
    if (user.firstName) url.searchParams.set("name", user.firstName);
    if (user.businessName) url.searchParams.set("business", user.businessName);
    if (user.niche) url.searchParams.set("niche", user.niche);
    return url.toString();
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
    const scaleFee = extraFee("scale", 150);
    const enterpriseFee = extraFee("enterprise", 100);
    const locs = Math.max(1, Number(state.locations) || 1);
    const enterpriseSignal =
      state.customIntegration || state.segment === "enterprise" || locs >= 8;

    if (enterpriseSignal) {
      return {
        id: "enterprise",
        name: (enterprise && enterprise.name) || "Enterprise",
        price: `$${planMonthly("enterprise", 399)}`,
        reason:
          "Large multi-location or custom connectivity — dedicated support, org-wide publishing, 3 Local Falcon keywords tracked, and locations at $" +
          enterpriseFee +
          "/mo each from live pricing.",
        includes: (enterprise && enterprise.includes) || [],
        locationNote:
          locs > 1
            ? `${locs} locations → 1 included + ${locs - 1} × $${enterpriseFee}/mo`
            : `1 location included · extras $${enterpriseFee}/mo`,
      };
    }

    if (locs > 1) {
      return {
        id: "scale",
        name: (scale && scale.name) || "Scale",
        price: `$${planMonthly("scale", 249)}`,
        reason:
          "Multi-location teams need one proof engine across markets — Maps visibility, geotagged website content, reviews, and social — with extra locations at $" +
          scaleFee +
          "/mo each, plus 1 Local Falcon keyword tracked.",
        includes: (scale && scale.includes) || [],
        locationNote: `${locs} locations → 1 included + ${locs - 1} × $${scaleFee}/mo`,
      };
    }

    return {
      id: "starter",
      name: (starter && starter.name) || "Starter",
      price: `$${planMonthly("starter", 99)}`,
      reason:
        "One location: capture the job, publish local-search-ready proof, and ask for the review on site. Starter is 1 location only — upgrade to Scale when you add markets.",
      includes: (starter && starter.includes) || [],
      locationNote: "1 location only — cannot add more",
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
  function audienceLabel() {
    return isAffiliate() ? "Affiliate" : isPartner() ? "Partner" : "Contractor";
  }
  function displayName() {
    const named = String(state.prospectName || "").trim();
    if (named) return named;
    if (isAffiliate()) return "Your referral network";
    if (isPartner()) return "Your agency";
    return "Your business";
  }
  function defaultNextStep(mode = state.mode) {
    if (mode === "affiliate") return "Join the referral program and share your link with one contractor this week.";
    if (mode === "partner") return "Apply as a strategic partner and pick one client for a pilot.";
    return "Start Free Trial and connect one real job workflow.";
  }
  function siteUrl(path) {
    try {
      return new URL(path, window.location.origin).toString();
    } catch {
      return path;
    }
  }

  /** Affiliate = 20% x 12 months. Partner = 15% residual while customer stays active. */
  function commissionRows() {
    const rows = [
      { plan: "Starter", monthly: planMonthly("starter", 99) },
      { plan: "Scale", monthly: planMonthly("scale", 249) },
      { plan: "Enterprise", monthly: planMonthly("enterprise", 399) },
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
      <button class="start-btn" type="button" data-go="1">See How It Works →</button>
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
      <div class="problem-statement">One photo becomes proof in <em>five places.</em></div>
      <div class="channel-stack">
        <div class="channel-line"><strong>Website</strong><span>Geotagged, local-SEO job content</span></div>
        <div class="channel-line"><strong>Google</strong><span>Maps / Business Profile updates</span></div>
        <div class="channel-line"><strong>Social</strong><span>Consistent project proof</span></div>
        <div class="channel-line"><strong>Directory</strong><span>Verified public listing</span></div>
        <div class="channel-line"><strong>Review opportunity</strong><span>On-site QR / link handoff</span></div>
      </div>
    </div>
    <div class="proof-strip"><div class="proof-stat"><b>Local SEO</b><span>Maps + site proof that wins leads</span></div><div class="proof-stat"><b>AI-ready</b><span>Real work, reviews, credibility</span></div><div class="proof-stat"><b>+ reviews</b><span>QR ask before you leave</span></div></div>
  </section>`;
  }

  function renderDiagnose() {
    const crmOptions = ["Housecall Pro", "Jobber", "ServiceTitan", "CompanyCam", "FieldEdge", "Workiz", "QuickBooks", "HighLevel", "Other / none"];
    const channels = ["Website", "Google Business Profile", "Facebook / Instagram", "Directory", "Review requests"];
    const priorities = ["Local visibility", "More reviews", "Faster follow-up", "Consistent content", "Multi-location control"];
    const title = isAffiliate()
      ? "Use a contractor you’d refer."
      : isPartner()
        ? "Use a client you’d put on this."
        : "Start with the work you already do.";
    const intro = isAffiliate()
      ? "Plug in numbers for a typical shop you send over — so the proof-gap story is concrete when you pitch JobCapturePro."
      : isPartner()
        ? "Walk a real (or typical) client’s volume so the rest of the deck is about their workflow, not a generic demo."
        : "A few quick inputs so we can show what this looks like for your volume — not a generic demo.";
    const banner = isAffiliate()
      ? "<strong>For the referral pitch</strong><p>Estimate jobs, photo capture, and how often that work becomes public proof for a contractor you’d refer.</p>"
      : isPartner()
        ? "<strong>For the client pilot</strong><p>Estimate jobs, photo capture, and publishing habits for the account you’d onboard first.</p>"
        : "<strong>Together on this call</strong><p>Walk through what happens to photos after a job finishes — and how you ask for the review before you leave.</p>";
    return `<section class="chapter content-pad">
    ${chapterHeader(3, isChannelPartner() ? "Example workflow" : "Your workflow", title, intro)}
    <div class="prompt-banner">${banner}</div>
    <div class="form-grid">
      <div class="field"><label for="jobsPerWeek">${isChannelPartner() ? "Completed jobs per week (example shop)" : "Completed jobs per week"}</label><input id="jobsPerWeek" type="number" min="1" max="10000" value="${state.jobsPerWeek}"></div>
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
    const intro = isAffiliate()
      ? "These numbers become your pitch: completed jobs that could strengthen a contractor’s local Maps presence, website SEO, and reviews — but may not leave a public trail today."
      : isPartner()
        ? "These numbers become the client story: completed jobs that could strengthen local Maps presence, website SEO, and reviews — but may not leave a consistent public trail today."
        : "These are completed jobs that could strengthen local Maps presence, website SEO, and review credibility — but may not leave a consistent public trail today.";
    const label = isChannelPartner()
      ? "completed jobs per month a shop like this may leave unused as public proof."
      : "completed jobs per month may be going unused as public proof.";
    return `<section class="chapter content-pad">
    ${chapterHeader(4, "Proof gap", "Make unused jobs visible online.", intro)}
    <div class="gap-layout">
      <div>
        <div class="big-number"><span>${gap.unused}</span></div>
        <p class="big-label">${label}</p>
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
    { title: "Distribute", short: "Publish where local buyers (and AI) look", detail: "One photo supports five outcomes — Website, Google, Social, Directory, and a review opportunity.", bullets: ["Website: geotagged, local-SEO job pages", "Google Maps / Business Profile posts that drive calls", "Social: Facebook, Instagram, and X", "Directory: verified JobCapturePro listing", "Review opportunity: on-site QR / link handoff"] },
    { title: "Convert", short: "Ask for the review on site", detail: "Before leaving, crew shows a QR code (or sends a link) — the credibility signal AI search and neighbors both trust.", bullets: ["On-site QR handoff while the customer is happiest", "Optional review link if they prefer", "More reviews without awkward office follow-ups"] },
  ];

  function renderEngine() {
    const detail = engineSteps[selectedEngine];
    return `<section class="chapter content-pad">
    ${chapterHeader(chapterNum("engine"), "How it works", "One job in. Proof everywhere.", isAffiliate() ? "Show contractors: completed work becomes Map Pack fuel, geotagged website content, reviews, and social — then you earn when they subscribe." : isPartner() ? "Show clients: completed work becomes Maps visibility, geotagged website content, reviews, and social — without a content team." : "JobCapturePro turns completed work into Maps visibility, geotagged website content, reviews, and social — proof that wins local Map Pack attention and AI answers.")}
    <div class="engine">
      <div class="engine-flow">${engineSteps.map((step, i) => `<button class="engine-step ${selectedEngine === i ? "active" : ""}" data-engine="${i}" type="button"><span class="step-no">0${i + 1}</span><h3>${step.title}</h3><p>${step.short}</p></button>`).join("")}</div>
      <div class="engine-detail"><strong>${detail.detail}</strong><ul>${detail.bullets.map((x) => `<li>${x}</li>`).join("")}</ul></div>
    </div>
  </section>`;
  }

  function renderDemo() {
    const company = esc(displayName());
    const trade = esc(state.trade || "Home services");
    const intro = isAffiliate()
      ? `Walk the live product as if you were showing a contractor named ${company}. Same guided demo prospects use — personalized from this deck.`
      : isPartner()
        ? `Walk the live product for a client like ${company}. Same guided demo they would run — seeded with this deck’s company and trade.`
        : `This is the real JobCapturePro demo — seeded for ${company} (${trade}). No duplicate build: same product walk-through as /demo.`;
    return `<section class="chapter demo-embed-chapter">
    <div class="demo-embed-chrome">
      ${chapterHeader(chapterNum("demo"), "Live product", "Try it with their job story.", intro)}
      <div class="demo-embed-meta">
        <span>Using <strong>${company}</strong> · ${trade}</span>
        <button type="button" class="quiet-btn" id="reloadDemoEmbed">Reload with current company</button>
        <a class="quiet-btn" id="openDemoTab" href="#" target="_blank" rel="noopener">Open in new tab</a>
      </div>
    </div>
    <div class="demo-embed-shell">
      <iframe class="demo-embed-frame" title="JobCapturePro live demo" src="about:blank" data-demo-embed="1"></iframe>
    </div>
  </section>`;
  }

  const lfGridPatterns = {
    before_blank: Array(49).fill(20),
    after_fr_wv: [2,1,1,2,1,3,4,1,1,2,1,2,1,3,1,2,1,1,2,1,2,2,1,1,1,1,2,5,1,1,2,1,1,3,2,3,1,1,2,1,1,6,4,3,2,1,2,3,7],
    after_bw_wv: [1,1,2,1,1,2,1,1,2,1,1,2,1,1,2,1,1,1,1,2,1,1,1,2,1,1,1,2,1,2,1,1,2,1,1,2,1,1,2,1,1,1,1,1,2,1,1,2,1],
    after_fr_mi: [2,1,1,3,2,4,8,1,2,1,1,2,3,5,1,1,2,1,1,2,3,3,1,1,1,2,1,6,2,1,2,1,1,3,2,5,2,1,2,1,2,7,4,3,2,1,2,3,9],
    after_bw_mi: [1,1,2,1,1,2,3,1,2,1,1,2,1,1,2,1,1,1,1,2,1,1,1,2,1,1,1,2,1,2,1,1,2,1,1,2,1,1,2,1,1,4,1,1,2,1,1,5,1],
  };

  function lfRankClass(rank) {
    const n = Number(rank) || 20;
    if (n <= 3) return "is-rank-top";
    if (n <= 10) return "is-rank-mid";
    if (n <= 19) return "is-rank-low";
    return "is-rank-out";
  }

  function renderLfGrid(patternKey, ariaLabel, mapUrl) {
    const ranks = lfGridPatterns[patternKey] || lfGridPatterns.before_blank;
    const cells = ranks
      .map((rank) => `<span class="jcp-lf-grid__cell ${lfRankClass(rank)}" title="Rank ${rank}"></span>`)
      .join("");
    return `<div class="jcp-lf-grid jcp-lf-grid--mapped" role="img" aria-label="${esc(ariaLabel)}">
      <img class="jcp-lf-grid__map" src="${esc(mapUrl)}" alt="" width="640" height="640" loading="lazy" decoding="async" />
      <div class="jcp-lf-grid__cells">${cells}</div>
    </div>`;
  }

  const acculevelMarkets = {
    triadelphia: {
      label: "Triadelphia, WV",
      meta: "Real Google Maps area · ~12 weeks (March → June)",
      mapBg: "lf-map-triadelphia.jpg",
      keywords: [
        {
          keyword: "Foundation repair",
          beforeCover: "0%",
          afterCover: "90%",
          beforeSummary: "Not showing up",
          afterSummary: "Showing up across town",
          beforePattern: "before_blank",
          afterPattern: "after_fr_wv",
        },
        {
          keyword: "Waterproofing",
          beforeCover: "0%",
          afterCover: "100%",
          beforeSummary: "Not showing up",
          afterSummary: "Showing up #1–#3",
          beforePattern: "before_blank",
          afterPattern: "after_bw_wv",
        },
      ],
    },
    monroe: {
      label: "Monroe, MI",
      meta: "Real Google Maps area · ~12 weeks (March → June)",
      mapBg: "lf-map-monroe.jpg",
      keywords: [
        {
          keyword: "Foundation repair",
          beforeCover: "0%",
          afterCover: "84%",
          beforeSummary: "Not showing up",
          afterSummary: "Showing up across town",
          beforePattern: "before_blank",
          afterPattern: "after_fr_mi",
        },
        {
          keyword: "Waterproofing",
          beforeCover: "0%",
          afterCover: "96%",
          beforeSummary: "Not showing up",
          afterSummary: "Showing up #1–#3",
          beforePattern: "before_blank",
          afterPattern: "after_bw_mi",
        },
      ],
    },
  };

  function stars(n = 5) {
    return "★★★★★".slice(0, Math.max(0, Math.min(5, n)));
  }

  function renderKeywordMetrics(keywords, mapUrl) {
    return `<div class="lf-keywords">${keywords
      .map(
        (k) => `<div class="lf-keyword">
        <div class="lf-keyword-name">${esc(k.keyword)}</div>
        <div class="lf-keyword-grids">
          <div class="lf-keyword-grid-side">
            <span class="lf-grid-phase lf-grid-phase--before">Before</span>
            ${renderLfGrid(k.beforePattern, `Before ${k.keyword}`, mapUrl)}
            <div class="lf-metric lf-metric--before">
              <strong>${esc(k.beforeSummary)}</strong>
              <em>${esc(k.beforeCover)} of the map</em>
            </div>
          </div>
          <div class="lf-keyword-arrow" aria-hidden="true">→</div>
          <div class="lf-keyword-grid-side">
            <span class="lf-grid-phase lf-grid-phase--after">After</span>
            ${renderLfGrid(k.afterPattern, `After ${k.keyword}`, mapUrl)}
            <div class="lf-metric lf-metric--after">
              <strong>${esc(k.afterSummary)}</strong>
              <em>${esc(k.afterCover)} of the map</em>
            </div>
          </div>
        </div>
      </div>`
      )
      .join("")}</div>`;
  }

  function renderLocalFalconCard(marketKey) {
    const market = acculevelMarkets[marketKey];
    const leadLift = state.acculevelLeadLift === "" ? null : Number(state.acculevelLeadLift);
    const mapUrl = img(market.mapBg);
    return `<div class="lf-card">
      <div class="lf-card-head">
        <div>
          <span class="lf-kicker">Google Maps · real service area</span>
          <h4>${esc(market.label)}</h4>
          <p class="lf-search">${esc(market.meta)}</p>
        </div>
      </div>
      ${renderKeywordMetrics(market.keywords, mapUrl)}
      <div class="lf-value">
        <strong>So what for contractors</strong>
        <p>When more of the map shows you near the top of Google, more homeowners can find you and call — visibility support, not a ranking guarantee.</p>
        ${leadLift === null ? "" : `<p class="lf-verified"><strong>Verified lead impact:</strong> +${leadLift}% leads during the measured period.</p>`}
      </div>
    </div>`;
  }

  function renderProof() {
    const leadLift = state.acculevelLeadLift === "" ? null : Number(state.acculevelLeadLift);
    const reviewCards = reviews
      .map((r) => {
        const avatar = r.avatar || r.avatar_url || "";
        const avatarAlt = r.avatarAlt || r.avatar_alt || r.name || "";
        const avatarHtml = avatar
          ? `<img class="review-avatar" src="${esc(avatar)}" alt="${esc(avatarAlt)}" width="44" height="44" loading="lazy" />`
          : `<span class="review-avatar review-avatar--fallback" aria-hidden="true">${esc(String(r.name || "?").charAt(0))}</span>`;
        return `<article class="review-card">
        <div class="review-card-top">
          ${avatarHtml}
          <div class="review-card-meta"><strong>${esc(r.name)}</strong><span>${esc(r.role || "")}</span></div>
        </div>
        <div class="review-stars" aria-label="${r.rating || 5} stars">${stars(r.rating || 5)}</div>
        <p>“${esc(r.quote)}”</p>
      </article>`;
      })
      .join("");

    const acculevel = state.showAcculevel
      ? `<div class="case-layout case-layout--maps">
      <div class="case-story">
        <p class="case-kicker">Anonymous · multi-location foundation company</p>
        <h3 class="case-headline">Invisible on Google Maps → showing up first across town in ~90 days.</h3>
        <p class="case-copy">They used JobCapturePro to turn finished jobs into proof online. In two markets, people searching these services started seeing them across the map — not a ranking guarantee, just what consistent job proof can do.</p>
        <div class="case-stat-row">
          <div class="case-stat"><strong>0% → up to 100%</strong><span>of the map covered</span></div>
          <div class="case-stat"><strong>Invisible → #1–#3</strong><span>on Google Maps</span></div>
          <div class="case-stat"><strong>2 markets</strong><span>same playbook</span></div>
          <div class="case-stat"><strong>~12 weeks</strong><span>March → June</span></div>
        </div>
        <div class="lf-primer lf-primer--compact">
          <div class="lf-primer-item"><span>Red</span><strong>Not showing up</strong><p>Homeowners searching nearby didn’t see them in Google’s local results.</p></div>
          <div class="lf-primer-item"><span>Green</span><strong>Near the top</strong><p>They showed up in the places people search across town.</p></div>
        </div>
        <div class="case-proof-note">${
          leadLift === null
            ? "Past Google Maps results don’t guarantee future rankings or leads."
            : `<strong>Verified lead impact:</strong> +${leadLift}% leads during the measured period. Past results don’t guarantee future rankings.`
        }</div>
      </div>
      <div class="case-visual">
        <div class="case-tabs">${Object.entries(acculevelMarkets)
          .map(([key, item]) => `<button class="case-tab ${selectedCaseMarket === key ? "active" : ""}" data-case-market="${key}" type="button">${item.label}</button>`)
          .join("")}</div>
        ${renderLocalFalconCard(selectedCaseMarket)}
        <div class="lf-legend"><span>Real Google Maps areas</span><span>Triadelphia, WV · Monroe, MI</span></div>
      </div>
    </div>`
      : "";

    return `<section class="chapter content-pad">
    ${chapterHeader(
      chapterNum("proof"),
      "Customer proof",
      "Real jobs. Real Google Maps lift.",
      isAffiliate()
        ? "Use these quotes when you refer. The before/after maps show how much more of town started seeing them on Google."
        : isPartner()
          ? "Proof for a client pitch. Simple before/after maps: invisible → showing up across town."
          : "Operators who turned job proof into local visibility — so more homeowners could find them on Google Maps."
    )}
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
    const title = isAffiliate()
      ? "Match the story to who you refer."
      : isPartner()
        ? "Match the story to the clients you serve."
        : "Match the story to how you operate.";
    const intro = isAffiliate()
      ? "Pick the stage that sounds most like the contractors you send over. Same product — different pitch emphasis."
      : isPartner()
        ? "Pick the stage that sounds most like your typical client. Same workflow — different rollout story."
        : "Pick the stage that sounds most like your business. The workflow stays the same — the emphasis changes.";
    return `<section class="chapter content-pad">
    ${chapterHeader(chapterNum("fit"), "Right fit", title, intro)}
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
      const scaleFee = extraFee("scale", 150);
      const enterpriseFee = extraFee("enterprise", 100);
      aside = `<aside class="recommendation">
        <span class="plan-kicker">Recommended fit</span><h3>${plan.name}</h3>${state.showPricing ? `<p class="price">${plan.price} <span>/ month</span></p>` : ""}<p class="plan-reason">${plan.reason}</p>
        <ul class="included">${(plan.includes || []).map((x) => `<li>${esc(x)}</li>`).join("")}</ul>
        <p class="plan-note">${state.showPricing ? `${esc(plan.locationNote || `1 location included`)}. ` : ""}Starter is 1 location only. Scale adds locations at $${scaleFee}/mo (1 Local Falcon keyword); Enterprise at $${enterpriseFee}/mo (3 Local Falcon keywords). Prices stay current on <a href="${esc(pricingLink)}" target="_blank" rel="noopener">our pricing page</a>.</p>
        <a class="plan-cta" href="${esc(cta.primaryUrl || pricingLink)}" target="_blank" rel="noopener">${esc(cta.primaryLabel || "Start Free Trial")} →</a>
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
        : "Match the plan to your locations.";
    const lead = isAffiliate()
      ? "Simple referral economics — 20% recurring for 12 months on paid accounts."
      : isPartner()
        ? "For agencies and consultants doing real selling: residual commission while the customer stays active."
        : `We’ll recommend from live pricing: 1 location → Starter ($${planMonthly("starter", 99)}/mo); multi-location → Scale ($${planMonthly("scale", 249)}/mo + $${extraFee("scale", 150)}/mo per extra, 1 Local Falcon keyword); large / custom → Enterprise (3 Local Falcon keywords).`;

    return `<section class="chapter content-pad">
    ${chapterHeader(chapterNum("plan"), isAffiliate() || isPartner() ? "Earn with JCP" : "Plan", headline, sub + " " + lead)}
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

  function objectionsForMode() {
    if (isAffiliate()) {
      return [
        { title: "Will contractors actually buy?", answer: "You’re recommending something they already do — finish jobs and take photos. JobCapturePro turns that into Maps visibility, geotagged website proof, and reviews. Point them to Start Free Trial; you earn when they become a paid customer." },
        { title: "How do I get paid?", answer: "Join the referral program, share your link, and earn 20% recurring commission for 12 months on paid accounts under program terms." },
        { title: "Is this hard to explain?", answer: "Keep it simple: one check-in → local SEO proof on their website and Google → QR review ask on site. That’s the pitch." },
        { title: "Does this guarantee rankings?", answer: "No honest platform can. What you can say honestly: it publishes the real-work proof Google, neighbors, and AI answers reward — geotagged jobs, reviews, and local credibility." },
        { title: "What if they already post on social?", answer: "Social is one channel. The bigger gap for most shops is Maps posts, geotagged website content, directory presence, and an on-site review ask from the same job." },
        { title: "I don’t have time to sell software", answer: "That’s why it’s a referral motion, not a full sales cycle. Share the demo or referral link; JobCapturePro handles onboarding." },
      ];
    }
    if (isPartner()) {
      return [
        { title: "Will this conflict with our other tools?", answer: "Keep the CRM and project tools. JobCapturePro sits after job completion — geotagged website proof, Maps/GBP, social, directory, and an on-site QR review ask." },
        { title: "How do partner economics work?", answer: "Strategic partners who sell and support earn 15% recurring for as long as the customer stays an active paid account — stronger than a 12-month affiliate cut when you’re doing the heavy lifting." },
        { title: "Will techs at our clients use it?", answer: "It’s a simple photo check-in. The review is a QR shown before leaving — seconds on site, not another office chore." },
        { title: "Does this guarantee rankings?", answer: "No. What you deliver is a steady supply of real, location-based proof published well — the signals local search and AI answers reward. Results still depend on market, site, and execution." },
        { title: "Is margin worth the effort?", answer: "Compare residual commission plus delivery leverage: you’re productizing proof and local SEO instead of building content by hand every month." },
        { title: "We don’t have bandwidth for another platform", answer: "Start with one pilot client, confirm integrations, prove Maps/proof consistency, then expand. That’s the partner path on the Plan chapter." },
      ];
    }
    return [
      { title: "We already have a CRM", answer: "Keep it. JobCapturePro doesn’t replace scheduling — it turns completed-job activity into local Maps posts, geotagged website content, and reviews most CRMs never publish. We integrate with Housecall Pro, Jobber, ServiceTitan, CompanyCam, and more." },
      { title: "Will my techs actually use it?", answer: "It’s built around a simple photo check-in. The review is a QR they show before leaving — seconds on site, not another office task next week." },
      { title: "We already post on social", answer: "Helpful — social is one channel. The bigger opportunity is Website (geotagged jobs), Google Maps updates, Directory presence, and a Review opportunity from the same photo." },
      { title: "Does this guarantee rankings?", answer: "No honest platform can. What you get is a steady supply of what today’s search — including AI answers — actually rewards: real jobs, geotagged proof, reviews, and local credibility. Results still depend on your market, site, and execution." },
      { title: "Is this worth the cost?", answer: "Compare it to the manual work it replaces and the unused proof inventory we just looked at. You can Start Free Trial — no credit card — and pricing stays current on our pricing page." },
      { title: "We don’t have time for another tool", answer: "That’s exactly why it ties to job completion. Capture happens with the work; the QR review takes seconds before you leave." },
    ];
  }

  function renderObjections() {
    const objections = objectionsForMode();
    const item = objections[Math.min(selectedObjection, objections.length - 1)];
    const intro = isAffiliate()
      ? "These are the questions affiliates hear when recommending JobCapturePro."
      : isPartner()
        ? "These are the questions agencies and consultants hear when pitching JobCapturePro to clients."
        : "These are the questions we hear most from contractors.";
    return `<section class="chapter content-pad">
    ${chapterHeader(chapterNum("objections"), "Common questions", "Straight answers.", intro)}
    <div class="objection-layout">
      <div class="objection-list">${objections.map((x, i) => `<button type="button" data-objection="${i}" class="objection-btn ${selectedObjection === i ? "active" : ""}">${x.title}</button>`).join("")}</div>
      <div class="objection-response"><span class="eyebrow">Answer</span><h3>${item.title}</h3><p class="say-this">${item.answer}</p></div>
    </div>
  </section>`;
  }

  function recapText() {
    const company = displayName();
    const gap = calcGap();
    const plan = recommendPlan();
    const priorities = state.priorities.length ? state.priorities.join(", ") : "visibility and proof consistency";
    let path;
    if (isAffiliate()) path = "Affiliate referral program — 20% recurring for 12 months on paid referrals.";
    else if (isPartner()) path = "Strategic partner path — 15% residual while customers stay active.";
    else path = `${plan.name}${state.showPricing ? ` at ${plan.price}/month` : ""}. ${plan.reason}`;
    const heard = isChannelPartner()
      ? `Example ${state.trade.toLowerCase()} shop completes about ${gap.monthly} jobs/month; roughly ${gap.unused} may not become consistent public proof. Priorities: ${priorities}.`
      : `${company} completes about ${gap.monthly} jobs per month. Roughly ${gap.unused} may not be consistently published as public proof. Priorities: ${priorities}.`;
    return `JobCapturePro ${audienceLabel().toLowerCase()} recap — ${company}\n\nWhat we covered\n${heard}\n\nRecommended path\n${path}\n\nNext step\n${state.nextStep || defaultNextStep()}${state.followUpDate ? ` Target: ${state.followUpDate}.` : ""}\n\nNotes\n${state.salesNotes || "No additional notes."}\n`;
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
    const company = displayName();
    const gap = calcGap();
    const plan = recommendPlan();
    const today = new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(new Date());
    const logo = (cfg.presenter && cfg.presenter.logoUrl) || img("jcp-logo-dark.png");
    const nextDefault = defaultNextStep();
    let path;
    let pathDetail;
    let primaryHref = cta.primaryUrl || cfg.pricingUrl || "/pricing/";
    let primaryLabel = cta.primaryLabel || "Start Free Trial";
    let secondaryHref = cta.secondaryUrl || "/demo/";
    let secondaryLabel = cta.secondaryLabel || "See It for My Business";
    let lede;
    let why;
    let covered;
    let metricJobsLabel = "Jobs / month";
    let metricUnusedLabel = "Underused proof";

    if (isAffiliate()) {
      path = "Affiliate referral program";
      pathDetail = "Earn 20% recurring commission for 12 months when contractors you refer become paid customers.";
      primaryHref = "/referral-program/";
      primaryLabel = "Join the referral program";
      secondaryHref = "/demo/";
      secondaryLabel = "Share the live demo";
      lede = "Help contractors turn finished jobs into local Maps visibility, geotagged website proof, reviews, and social — and earn when they become customers.";
      why = "<strong>Why affiliates win with this:</strong> You’re recommending real work → real proof. Local Maps and AI search reward the same thing JobCapturePro publishes: geotagged jobs, reviews, and credibility.";
      covered = `Using the example shop from this call: about <strong>${gap.monthly} jobs/month</strong>, with roughly <strong>${gap.unused}</strong> potentially unpublished as public proof. That’s the story you can tell when you refer. Focus areas: ${esc(state.priorities.join(", ") || "visibility and reviews")}.`;
      metricJobsLabel = "Example jobs / mo";
      metricUnusedLabel = "Proof left on table";
    } else if (isPartner()) {
      path = "Strategic partner path";
      pathDetail = "15% recurring for as long as referred customers stay active — pilot one client, prove the workflow, then expand.";
      primaryHref = "/referral-program/";
      primaryLabel = "Apply as a partner";
      secondaryHref = cfg.pricingUrl || "/pricing/";
      secondaryLabel = "See Live Pricing";
      lede = "Productize local SEO proof for clients: Maps visibility, geotagged website content, reviews, and social — without becoming their content team.";
      why = "<strong>Why partners win with this:</strong> Clients already create the assets. You deliver Maps + local-SEO publishing and residual economics while the account stays active.";
      covered = `For a client like this call’s example: about <strong>${gap.monthly} jobs/month</strong>, with roughly <strong>${gap.unused}</strong> potentially unpublished. Focus areas: ${esc(state.priorities.join(", ") || "visibility and reviews")}.`;
      metricJobsLabel = "Client jobs / mo";
      metricUnusedLabel = "Unused client proof";
    } else {
      path = plan.name;
      pathDetail = plan.reason;
      lede = "Real jobs become local Maps visibility, geotagged website proof, reviews, and social — credibility Google, neighbors, and AI search all reward.";
      why = "<strong>Why this matters now:</strong> Local Maps drives calls. Website content is geotagged to the job and optimized for local search. AI-era search values real work, real reviews, and real credibility.";
      covered = `${esc(company)} completes about <strong>${gap.monthly} jobs per month</strong>. Roughly <strong>${gap.unused}</strong> may not be consistently published as public proof today — proof that could strengthen local Maps presence, website SEO, and reviews. Focus areas: ${esc(state.priorities.join(", ") || "visibility and proof consistency")}.`;
    }

    return `<section class="chapter content-pad">
    ${chapterHeader(chapterNum("close"), "Next steps", "A clear path forward.", isAffiliate() ? "Get your referral link and start sharing JobCapturePro with contractors who already take job photos." : isPartner() ? "Apply as a strategic partner, pilot one client workflow, then expand." : "One-page summary of this call — plus Start Free Trial when you’re ready.")}
    <div class="close-layout">
      <div class="close-notes rep-only">
        <div class="field"><label for="nextStep">Agreed next step</label><textarea id="nextStep">${esc(state.nextStep || nextDefault)}</textarea></div>
        <div class="field"><label for="followUpDate">Target date</label><input id="followUpDate" type="date" value="${esc(state.followUpDate)}"></div>
        <div class="field"><label for="salesNotes">Call notes</label><textarea id="salesNotes" placeholder="Decision criteria, stakeholders, integration questions…">${esc(state.salesNotes)}</textarea></div>
      </div>
      <article class="recap" id="recap">
        <div class="recap-top"><div class="brand"><img src="${logo}" alt="JobCapturePro" /></div><span class="recap-date">${audienceLabel()} · ${today}</span></div>
        <h3>${esc(company)} · summary</h3>
        <p class="recap-lede">${lede}</p>
        <div class="recap-metrics">
          <div class="recap-metric"><span>${metricJobsLabel}</span><strong>${gap.monthly}</strong></div>
          <div class="recap-metric"><span>${metricUnusedLabel}</span><strong>${gap.unused}</strong></div>
          <div class="recap-metric recap-metric--accent"><span>Recommended</span><strong>${esc(isChannelPartner() ? (isAffiliate() ? "Affiliate" : "Partner") : path)}</strong></div>
        </div>
        <h4>What we covered</h4><p>${covered}</p>
        <h4>Recommended path</h4><p><strong>${esc(path)}${!isChannelPartner() && state.showPricing ? ` · ${plan.price}/month` : isAffiliate() ? " · 20% × 12 months" : isPartner() ? " · 15% residual" : ""}.</strong> ${esc(pathDetail)}</p>
        <div class="recap-why">${why}</div>
        <h4>Suggested next step</h4><p id="recapNextStep">${esc(state.nextStep || nextDefault)}${state.followUpDate ? ` Target: ${esc(state.followUpDate)}.` : ""}</p>
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
    const company = displayName();
    const logo = (cfg.presenter && cfg.presenter.logoUrl) || img("jcp-logo-dark.png");
    const pricingUrl = siteUrl(cfg.pricingUrl || "/pricing/");
    const referralUrl = siteUrl("/referral-program/");
    const demoUrl = siteUrl("/demo/");
    let path = plan.name;
    let pathDetail = plan.reason;
    let priceLine = state.showPricing ? `${plan.price}/month` : "Talk through fit";
    let primaryLabel = cta.primaryLabel || "Start Free Trial";
    let primaryUrl = siteUrl((cta.primaryUrl && !String(cta.primaryUrl).includes("sessionId") ? cta.primaryUrl : null) || cfg.pricingUrl || "/pricing/");
    let ctaPill = "No credit card · 14 days";
    let lede;
    let why;
    let covered;
    let metricJobs = { label: "Jobs / month", value: String(gap.monthly), note: "From this call’s inputs" };
    let metricUnused = { label: "Underused proof", value: String(gap.unused), note: "Not consistently published" };
    let includes = Array.isArray(plan.includes) ? plan.includes.slice(0, 5) : [];

    if (isAffiliate()) {
      path = "Affiliate program";
      pathDetail = "Earn 20% recurring commission for 12 months when contractors you refer become paid customers.";
      priceLine = "20% × 12 months";
      primaryLabel = "Join the referral program";
      primaryUrl = referralUrl;
      ctaPill = "Share · refer · earn";
      lede = "Refer contractors who already finish jobs and take photos. JobCapturePro turns that work into local Maps visibility, geotagged website proof, reviews, and social — and you earn when they become customers.";
      why = "You’re recommending what local search and AI answers reward: real jobs, geotagged proof, reviews, and credibility. Use the example shop numbers from this call when you pitch.";
      covered = `Example ${esc(state.trade.toLowerCase())} shop from this call: about <strong>${gap.monthly} jobs/month</strong>, with roughly <strong>${gap.unused}</strong> potentially left unpublished as public proof.`;
      metricJobs = { label: "Example jobs / mo", value: String(gap.monthly), note: "Typical shop from this call" };
      metricUnused = { label: "Proof left on table", value: String(gap.unused), note: "Pitch inventory, not a lead promise" };
      includes = [
        "20% recurring commission for 12 months",
        "Easy pitch: jobs they already finish",
        "Maps + geotagged local-SEO website proof",
        "On-site QR review requests",
        "Share demo or referral link",
      ];
    } else if (isPartner()) {
      path = "Strategic partner";
      pathDetail = "15% recurring for as long as referred customers stay active — pilot, prove, expand.";
      priceLine = "15% residual";
      primaryLabel = "Apply as a partner";
      primaryUrl = referralUrl;
      ctaPill = "Pilot → prove → expand";
      lede = "Deliver a proof engine for clients: local Maps visibility, geotagged website content, reviews, and social — without staffing a content team for every account.";
      why = "Clients already create the work. You productize local SEO publishing and earn residual commission while accounts stay active.";
      covered = `Client example from this call: about <strong>${gap.monthly} jobs/month</strong>, with roughly <strong>${gap.unused}</strong> potentially unpublished. Focus areas: ${esc(state.priorities.join(", ") || "visibility and reviews")}.`;
      metricJobs = { label: "Client jobs / mo", value: String(gap.monthly), note: "Pilot account example" };
      metricUnused = { label: "Unused client proof", value: String(gap.unused), note: "Delivery opportunity" };
      includes = [
        "15% residual while customer is active",
        "Pilot → prove → expand motion",
        "Geotagged local-SEO website publish",
        "Google Maps / GBP + review QR",
        "CRM integrations for client stacks",
      ];
    } else {
      lede = "Real jobs become local Maps visibility, geotagged website proof, reviews, and social — the credibility Google, neighbors, and AI search answers all reward.";
      why = "Local Maps coverage drives calls. Website content from JobCapturePro is optimized for local search — images geotagged to the actual job, SEO built in. AI-era search values real work, real proof, real reviews, and real credibility.";
      covered = `${esc(company)} completes about <strong>${gap.monthly} jobs per month</strong>. Roughly <strong>${gap.unused}</strong> may not be consistently published as public proof today.`;
      if (!includes.length) {
        includes = [
          "Geotagged images at the real job location",
          "Website content optimized for local search",
          "Google Maps / Business Profile publishing",
          "On-site QR review requests",
          "Social + directory distribution",
        ];
      }
      // Prefer clean marketing URLs on the leave-behind (not long onboarding session links)
      if (String(primaryUrl).includes("sessionId") || String(primaryUrl).includes("onboarding")) {
        primaryUrl = siteUrl("/pricing/");
      }
    }

    return {
      gap,
      plan,
      today,
      company,
      logo,
      pricingUrl,
      referralUrl,
      demoUrl,
      path,
      pathDetail,
      priceLine,
      primaryLabel,
      primaryUrl,
      ctaPill,
      lede,
      why,
      covered,
      metricJobs,
      metricUnused,
      includes,
      priorities: state.priorities.join(", ") || "Local visibility, reviews, and consistent proof",
      next: (state.nextStep || defaultNextStep()) + (state.followUpDate ? ` Target: ${state.followUpDate}.` : ""),
      notes: state.salesNotes || "",
      modeLabel: audienceLabel(),
      presented: state.repName ? `Presented by ${state.repName}` : "Prepared with JobCapturePro",
    };
  }

  function buildLeavebehindHtml() {
    const d = leavebehindPayload();
    const titleName = d.company.replace(/[^\w\s-]/g, "").trim() || "JobCapturePro";
    const includes = d.includes.map((x) => `<li>${esc(x)}</li>`).join("");
    const notes = d.notes ? `<section class="lb-block"><h2>Notes</h2><p>${esc(d.notes)}</p></section>` : "";

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>JobCapturePro — ${esc(d.modeLabel)} — ${esc(titleName)} summary</title>
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
    font-size: 32px; line-height: 1.12; letter-spacing: -0.03em; color: #111827;
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
  .metric.accent-card { background: #111827; border-color: #111827; }
  .metric.accent-card span { color: #ffb4a8; }
  .metric.accent-card strong, .metric.accent-card em { color: #ffffff; }
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
    font-size: 26px; color: #ffffff; letter-spacing: -0.03em;
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
  .cta-bar strong { display: block; font-size: 14px; color: #ffffff; }
  .cta-bar span { display: block; margin-top: 3px; font-size: 12px; color: #ffe8e3; }
  .cta-bar .pill {
    flex: 0 0 auto; padding: 8px 12px; border-radius: 999px; background: #fff; color: #111827;
    font-size: 11px; font-weight: 800; white-space: nowrap;
  }
  .foot {
    margin-top: 14px; display: flex; justify-content: space-between; gap: 12px;
    color: #6b7280; font-size: 10px;
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
      <div class="top-meta"><strong>${esc(d.modeLabel)} summary</strong>${esc(d.today)}</div>
    </header>
    <div class="hero">
      <span class="eyebrow">Leave-behind · one page</span>
      <h1>${esc(d.company)}</h1>
      <p class="lede">${esc(d.lede)}</p>
    </div>
    <div class="metrics">
      <div class="metric"><span>${esc(d.metricJobs.label)}</span><strong>${esc(d.metricJobs.value)}</strong><em>${esc(d.metricJobs.note)}</em></div>
      <div class="metric"><span>${esc(d.metricUnused.label)}</span><strong>${esc(d.metricUnused.value)}</strong><em>${esc(d.metricUnused.note)}</em></div>
      <div class="metric accent-card"><span>Recommended</span><strong>${esc(d.path)}</strong><em>${esc(d.priceLine)}</em></div>
    </div>
    <div class="grid">
      <div>
        <section class="lb-block">
          <h2>What we covered</h2>
          <p>${d.covered} Focus areas: <strong>${esc(d.priorities)}</strong>.</p>
        </section>
        <section class="why">
          <h2>Why this matters now</h2>
          <p>${esc(d.why)}</p>
        </section>
        <section class="next lb-block" style="margin-top:16px">
          <h2>Suggested next step</h2>
          <p>${esc(d.next)}</p>
        </section>
        ${notes}
      </div>
      <aside class="plan-card">
        <div class="kicker">Recommended path</div>
        <h3>${esc(d.path)}</h3>
        <div class="price">${esc(d.priceLine)}</div>
        <p>${esc(d.pathDetail)}</p>
        <ul>${includes}</ul>
      </aside>
    </div>
    <div class="cta-bar">
      <div><strong>${esc(d.primaryLabel)}</strong><span>${esc(d.primaryUrl.replace(/^https?:\/\//, ""))}</span></div>
      <div class="pill">${esc(d.ctaPill)}</div>
    </div>
    <footer class="foot">
      <span>${esc(d.presented)}</span>
      <span>${isChannelPartner() ? `Referral: ${esc(d.referralUrl.replace(/^https?:\/\//, ""))}` : `Pricing: ${esc(d.pricingUrl.replace(/^https?:\/\//, ""))}`}</span>
    </footer>
  </article>
</body>
</html>`;
  }

  function downloadProspectPdf() {
    const html = buildLeavebehindHtml();
    const titleName = displayName().replace(/[^\w\s-]/g, "").trim().replace(/\s+/g, "-") || "summary";
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
    document.title = `JobCapturePro-${audienceLabel()}-${titleName}-summary`;

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
      showToast("Choose “Save as PDF” · set Color on");
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

  function mountDemoEmbed(forceReload = false) {
    const frame = stage.querySelector("iframe[data-demo-embed]");
    if (!frame) return;
    const url = buildDemoEmbedUrl();
    const openTab = $("#openDemoTab");
    if (openTab) openTab.href = url;
    if (forceReload || frame.getAttribute("src") === "about:blank" || frame.dataset.embedUrl !== url) {
      frame.dataset.embedUrl = url;
      frame.src = url;
    }
  }

  function render() {
    renderNav();
    const list = chapterList();
    const ch = list[state.chapter];
    const chapterId = ch ? ch.id : "";
    const keepDemoFrame =
      chapterId === "demo" &&
      stage.dataset.chapterId === "demo" &&
      stage.querySelector("iframe[data-demo-embed]");

    if (!keepDemoFrame) {
      const renderers = {
        cover: renderCover,
        problem: renderProblem,
        diagnose: renderDiagnose,
        gap: renderGap,
        engine: renderEngine,
        demo: renderDemo,
        proof: renderProof,
        fit: renderFit,
        plan: renderPlan,
        objections: renderObjections,
        close: renderClose,
      };
      const renderer = renderers[chapterId] || renderCover;
      stage.innerHTML = renderer();
      stage.dataset.chapterId = chapterId;
      if (chapterId === "demo") {
        mountDemoEmbed(true);
      }
    } else {
      // Stay on Live demo without remounting the iframe (preserves in-progress walkthrough).
      const openTab = $("#openDemoTab");
      if (openTab) openTab.href = buildDemoEmbedUrl();
      const meta = stage.querySelector(".demo-embed-meta span");
      if (meta) {
        meta.innerHTML = `Using <strong>${esc(displayName())}</strong> · ${esc(state.trade || "Home services")}`;
      }
    }

    const prospectInput = $("#prospectName");
    if (prospectInput) {
      prospectInput.value = state.prospectName;
      prospectInput.placeholder = isAffiliate()
        ? "Affiliate name or network"
        : isPartner()
          ? "Agency or partner name"
          : "Company name";
    }
    const metaLabel = document.querySelector(".call-meta label");
    if (metaLabel) metaLabel.textContent = isPartner() ? "Partner" : isAffiliate() ? "Affiliate" : "Prospect";
    document.querySelectorAll("[data-mode]").forEach((button) => button.classList.toggle("active", button.dataset.mode === state.mode));
    $("#progressLabel").textContent = `${String(state.chapter + 1).padStart(2, "0")} / ${String(list.length).padStart(2, "0")}`;
    $("#progressBar").style.width = `${((state.chapter + 1) / list.length) * 100}%`;
    $("#backBtn").disabled = state.chapter === 0;
    $("#backBtn").style.opacity = state.chapter === 0 ? ".35" : "1";
    const next = $("#nextBtn");
    next.disabled = state.chapter === list.length - 1;
    next.style.opacity = next.disabled ? ".35" : "1";
    next.querySelector("span").textContent = state.chapter === 0 ? "Continue" : state.chapter === list.length - 2 ? "See next steps" : "Next";
    document.body.classList.toggle("is-presenting", !!state.presenting);
    document.body.classList.toggle("is-demo-chapter", chapterId === "demo");
    const presentBtn = $("#presentBtn");
    if (presentBtn) presentBtn.textContent = state.presenting ? "Exit present" : "Present";
    if (!keepDemoFrame) {
      bindChapterEvents();
    }
  }

  function updateArray(name, value, checked) {
    const set = new Set(state[name]);
    checked ? set.add(value) : set.delete(value);
    setState({ [name]: [...set] });
  }

  function bindChapterEvents() {
    stage.querySelectorAll("[data-go]").forEach((el) => el.addEventListener("click", () => goTo(Number(el.dataset.go))));
    const id = (currentChapter() || {}).id || "";

    if (id === "diagnose") {
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
    if (id === "engine") {
      stage.querySelectorAll("[data-engine]").forEach((el) => el.addEventListener("click", () => { selectedEngine = Number(el.dataset.engine); render(); }));
    }
    if (id === "demo") {
      $("#reloadDemoEmbed")?.addEventListener("click", () => mountDemoEmbed(true));
      const openTab = $("#openDemoTab");
      if (openTab) openTab.href = buildDemoEmbedUrl();
    }
    if (id === "proof") {
      stage.querySelectorAll("[data-case-market]").forEach((el) => el.addEventListener("click", () => { selectedCaseMarket = el.dataset.caseMarket; render(); }));
      stage.querySelectorAll("[data-lightbox-src]").forEach((el) =>
        el.addEventListener("click", () => openLightbox(el.dataset.lightboxSrc, el.dataset.lightboxFallback || el.dataset.lightboxSrc))
      );
    }
    if (id === "fit") stage.querySelectorAll("[data-segment]").forEach((el) => el.addEventListener("click", () => setState({ segment: el.dataset.segment })));
    if (id === "plan") {
      stage.querySelectorAll('input[name="planSegment"]').forEach((el) => el.addEventListener("change", (e) => setState({ segment: e.target.value })));
      $("#planLocations").addEventListener("change", (e) => setState({ locations: e.target.value }));
      const auto = stage.querySelector('input[name="automation"]');
      const custom = stage.querySelector('input[name="customIntegration"]');
      if (auto) auto.addEventListener("change", (e) => setState({ automation: e.target.checked }));
      if (custom) custom.addEventListener("change", (e) => setState({ customIntegration: e.target.checked }));
      stage.querySelectorAll('input[name="planPriorities"]').forEach((el) => el.addEventListener("change", (e) => updateArray("priorities", e.target.value, e.target.checked)));
    }
    if (id === "objections") stage.querySelectorAll("[data-objection]").forEach((el) => el.addEventListener("click", () => { selectedObjection = Number(el.dataset.objection); render(); }));
    if (id === "close") {
      ["nextStep", "followUpDate", "salesNotes"].forEach((fieldId) => {
        const el = $("#" + fieldId);
        if (!el) return;
        el.addEventListener("input", (e) => {
          state[fieldId] = e.target.value;
          saveState();
          const nextEl = $("#recapNextStep");
          if (nextEl) nextEl.textContent = `${state.nextStep || defaultNextStep()}${state.followUpDate ? ` Target: ${state.followUpDate}.` : ""}`;
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
    const acculevelToggle = $("#settingAcculevel");
    if (acculevelToggle) acculevelToggle.checked = state.showAcculevel;
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
    const acculevelToggle = $("#settingAcculevel");
    setState({
      prospectName: $("#settingProspect").value.trim(),
      repName: $("#settingRep").value.trim(),
      trade: $("#settingTrade").value.trim() || "Home services",
      jobsPerWeek: Number($("#settingJobs").value) || state.jobsPerWeek,
      locations: Number($("#settingLocations").value) || state.locations,
      acculevelLeadLift: $("#settingLeadLift").value,
      showPricing: $("#settingPricing").checked,
      showAcculevel: acculevelToggle ? acculevelToggle.checked : state.showAcculevel,
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
      btn.addEventListener("click", () => {
        const mode = btn.dataset.mode;
        const knownDefaults = [
          defaults.nextStep,
          defaultNextStep("contractor"),
          defaultNextStep("affiliate"),
          defaultNextStep("partner"),
        ];
        const patch = { mode };
        if (!state.nextStep || knownDefaults.includes(state.nextStep)) {
          patch.nextStep = defaultNextStep(mode);
        }
        selectedObjection = 0;
        setState(patch);
      })
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
      if (e.key === "ArrowRight" && !e.target.matches("input, textarea, select") && (currentChapter() || {}).id !== "demo") goTo(state.chapter + 1);
      if (e.key === "ArrowLeft" && !e.target.matches("input, textarea, select") && (currentChapter() || {}).id !== "demo") goTo(state.chapter - 1);
    });
    window.addEventListener("message", (e) => {
      if (e.origin !== window.location.origin) return;
      const data = e.data;
      if (!data || data.type !== "jcp-demo-embed") return;
      if (data.event === "post_demo_shown" || data.event === "outcomes_completed") {
        showToast("Demo complete — hit Continue when you’re ready");
      }
    });
  }

  bindGlobal();
  render();
})();
