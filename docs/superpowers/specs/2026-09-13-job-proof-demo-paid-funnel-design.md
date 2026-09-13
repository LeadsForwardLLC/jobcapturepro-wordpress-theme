# Design Spec: `/job-proof-demo/` Paid Traffic Funnel

**Status:** Approved (Option A) with amendments — implemented 2026-09-13  
**Date:** 2026-09-13  
**Product:** JobCapturePro (WordPress theme `jobcapturepro-core`)  
**Locked URL:** `https://jobcapturepro.com/job-proof-demo/`  
**LP variant key:** `job_proof_demo`  
**Primary Meta creative:** “Proof Waste | Most Expensive Thing | V1” (`stop-throwing-jobs-away.mov`, Meta video ID `38276235788657489`)

### Approved amendments (locked)
1. Meta: audit before coding; proof engagement must never fire/masquerade as Lead; no global Meta semantic changes without approval.
2. Paid UTMs/`fbclid`/`lp_variant` must survive onboarding handoff; marketing defaults must not overwrite them; surface attribution via `jcp_surface`.
3. Audit hardcoded onboarding `sessionId`; do not invent unique sessions without an issuance API.
4. Single analytics emission path (`dataLayer` only — no parallel `posthog.capture`).
5. Sample job: city/service-area only (no residential street).
6. Three user moments: Finished Job → Transformation → Outputs/Value + Trial (same final state).

---

## 1. Problem statement

Paid Meta campaigns are paused because the current acquisition funnel does not convert cold contractors into trials efficiently enough.

**Current paid pattern (existing LPs):**
Cold ad → message-matched LP → CTA into gated `/demo/` → email/trade/personalization before full product value → trial.

**Observed issue:**
Leads can go deep into the existing demo without completing / starting a trial. Asking for contact data before a clear aha moment creates friction for cold traffic.

**New hypothesis:**
```
COLD AD
→ MESSAGE-MATCHED LANDING PAGE (/job-proof-demo/)
→ NO-GATE PRODUCT PROOF (on this page)
→ CLEAR AHA MOMENT
→ FREE TRIAL
```

The visitor must understand JobCapturePro’s core value **before** providing an email address.

---

## 2. Goals and non-goals

### Goals
1. Standalone paid funnel at the **exact** route `/job-proof-demo/` (no rename, no redirect to `/demo/`).
2. Message-match the “Proof Waste / camera roll” Meta creative.
3. Deliver one fast product-proof “aha” in ~45–90 seconds with **no signup gate**.
4. Convert primarily to **Start Free 14-Day Trial**.
5. Preserve existing attribution and analytics contracts (UTM, fbclid, Meta, Matomo, PostHog, FirstPromoter, GHL rules).
6. Mobile-first clarity; short page; visual product proof over copy walls.

### Non-goals
- Do **not** redesign the rest of the marketing site.
- Do **not** alter `/demo/` or its survey/gate flow.
- Do **not** modify existing paid LPs (`/contractor-demo/`, `/contractor-formula/`, `/contractor-nature/`, `/job-proof/`, `/why-we-built-jcp/`) except shared utilities when technically required (e.g. attribution path map).
- Do **not** invent testimonials, rankings, lead/revenue guarantees, SEO/Google ranking guarantees, or fabricated scarcity.
- Do **not** claim live multi-channel publishing for every account without required connections.
- Do **not** change pricing, trial length, auth, Stripe, or onboarding app logic.
- Do **not** create GHL contacts from anonymous proof engagement.
- Do **not** fire Meta Lead merely because someone views/starts the ungated proof.

---

## 3. Audience and positioning

**Audience:** US contractors / home-service business owners (cold Meta traffic).

**Lead with:**
> The job was valuable. The proof should not disappear after the job is finished.

**Core concept:**
```
ONE FINISHED JOB
→ ONE PHOTO / CHECK-IN
→ MULTIPLE PUBLIC PROOF ASSETS
```

**Do not lead with** “marketing automation software.”

**Supported output framing (precise language):**
- Website content / proof
- Google Business Profile activity/content **where connected and supported**
- Social content
- JCP public proof / directory content **where applicable**
- Review opportunities
- Location / service-area proof **where applicable**

Status language on outputs must **not** claim “live” / “published” unless a real live publish occurs (this experience will not perform live publish).

---

## 4. Message match (ad → page)

Ad thesis: *Another perfectly good job lost to the camera roll.*

Page must feel like the immediate continuation:
- Crew did the work → photos taken → customer paid → marketing died
- JCP turns completed jobs into website proof, Google activity, social content, review opportunities

Hero and problem sections reinforce: finished job photo → JCP → public proof.

---

## 5. Recommended architecture

### Decision: dedicated page template (recommended)

| Option | Description | Verdict |
|--------|-------------|---------|
| **A. Dedicated template + on-page proof stepper** | New `page-job-proof-demo.php`, own CSS/JS, seed WP page slug `job-proof-demo` | **Recommended** |
| B. Campaign block-page variant | Extend `jcp_campaign_variants()` like `/job-proof/` | Rejected for primary path — existing variants CTA into `/demo/`; this funnel must not |
| C. Iframe / embed of interactive `/demo/` | Reuse full demo app | Rejected — pulls gate, survey, wrong UX |

### Why A
- Matches locked requirement: standalone funnel, not `/demo/`.
- Avoids accidental coupling to survey/email gate.
- Allows a shorter, clearer aha than the 5-step guided demo.
- Still reuses theme design tokens, campaign assets, attribution, and trial handoff.

### WordPress / routing
- **No custom rewrite** required (same as other marketing pages).
- Create/seed a WordPress `page` with slug `job-proof-demo`.
- Assign template: **Job Proof Demo** (`page-job-proof-demo.php`).
- Settings intent: `noindex`, paid chrome (minimal brandbar + minimal footer), campaign landing semantics.
- Add `/job-proof-demo/` to WP Rocket purge lists if other paid LPs are listed there.

### Shared utilities that MAY be touched (minimally)
1. `assets/js/core/jcp-attribution.js` — add path map entry `/job-proof-demo` → `job_proof_demo`.
2. `inc/enqueue.php` — enqueue page-specific CSS/JS; extend PaidLandingView path→variant maps if present.
3. `inc/wp-rocket.php` — purge path list.
4. Possibly `inc/admin-page-templates.php` — register template label.

### Explicitly untouched
- `page-demo.php`, `assets/js/features/demo/jcp-demo.js`, survey templates/JS
- Existing campaign variant seed documents for other LPs
- Pricing, onboarding session issuance logic, Stripe, auth

---

## 6. Page information architecture

**Length:** short. Visitor should reach the product experience extremely quickly.

### Section 1 — Hero
- **Eyebrow:** YOUR BEST JOBS SHOULDN'T DIE IN THE CAMERA ROLL
- **H1:** One Finished Job Should Keep Working After the Crew Leaves.
- **Support:** Your crew already creates the proof. See how JobCapturePro turns one completed job into marketing assets your customers can actually find.
- **Primary CTA:** See What One Job Creates →  
  - Behavior: scroll/anchor into `#proof` and start proof step 1 (does **not** go to `/demo/`).
- **Secondary under CTA:** Free · Takes about 60 seconds · No signup required
- **Discreet tertiary:** Already get it? Start Free Trial → (subordinate; real trial URL)
- Visual: finished job photo → JCP → public proof (simple transformation, not cluttered)
- Nav: paid-LP minimal brandbar (logo + optional single CTA), not full site nav

### Section 2 — Problem (compact)
- **Heading:** Most Job Photos Never Become Marketing.
- Process: JOB FINISHED → PHOTO TAKEN → CAMERA ROLL / CRM → NOTHING
- Copy: The work happened. The proof exists. But if customers never see it, it does almost nothing for the next sale.
- Contrast: WITH JOBCAPTUREPRO → JOB FINISHED → CHECK-IN → PUBLIC PROOF
- **CTA:** Show Me → (same as starting proof experience)

### Product-proof experience (embedded on this page)
No email, business name, phone, ZIP, or trade required before start.

Target completion: **45–90 seconds**.

Use real JCP visual language / assets. Do not invent fake software chrome if existing components/assets can be reused.

#### Proof Step 1 — The finished job
- Heading: The Job Is Done.
- Default example: Water Heater Replacement
- Show: completed-job photo, service name, realistic city/location, optional Completed badge
- Copy: Normally, this is where the marketing stops.
- CTA: Put This Job to Work →
- No real photo upload

#### Proof Step 2 — JCP check-in
- Heading: JobCapturePro Turns the Work Into Proof.
- Compact check-in: photo, service, location, concise generated description
- Fast transition; no arbitrary artificial delays
- Optional short processing copy only if needed: Creating job proof… / Adding job context… / Preparing connected channels…

#### Proof Step 3 — Aha (most important)
- Heading: One Job. Now Working in More Places.
- Sub: Instead of disappearing into a camera roll, the finished job becomes reusable proof across your marketing.
- Output cards (recommended):
  1. **WEBSITE** — Ready for your website
  2. **GOOGLE** — Prepared for Google *(not “live/published”)*
  3. **SOCIAL** — Social post created
  4. **REVIEW** — Review opportunity ready
  5. **LOCAL / JCP PROOF** — Job proof added
- Visual: ONE JOB → five destinations

#### Proof Step 4 — Value bridge
- Heading: Now Imagine This Happening After Every Finished Job.
- Copy: Your crews are already doing the expensive part — completing the work. JobCapturePro helps make that work visible after the truck leaves.
- Optional compact WITHOUT vs WITH comparison (no SEO overclaims)

#### Trial conversion screen
- Eyebrow: PUT YOUR NEXT JOB TO WORK
- H2: Ready to Do This With Your Own Jobs?
- Primary: Start My Free 14-Day Trial →
- Support: No credit card required.
- Secondary: Talk to a JCP Expert
- Tertiary (optional, small, below): 90-day case study only if program remains live/approved — **not** leading

---

## 7. Sample job content (canonical, honest demo data)

Use one consistent sample job throughout the proof (align with existing theme canonical demo job where practical):

| Field | Value |
|-------|--------|
| Service | Water Heater Replacement |
| Address / area | 1242 Mason Rd, Austin, TX (or equivalent existing campaign asset context) |
| Photo | Existing optimized campaign / demo job image (webp where available) |
| Description | Short realistic completed-job description (no ranking/lead claims) |

Do not ask the visitor to personalize before the aha. Optional later personalization is out of scope for this LP.

---

## 8. Trial handoff (authoritative)

**Production trial destination (existing):**
```
https://app.jobcapturepro.com/onboarding?sessionId={uuid}&step=1
```
Built via theme helpers:
- `jcp_core_onboarding_app_url_raw()`
- `jcp_core_onboarding_hardcoded_session_id()` (or Global Settings override)
- Decorated by `assets/js/core/jcp-onboarding-handoff.js`

**Defaults currently used for marketing→app UTM shell:**
- `utm_source=jobcapturepro.com`
- `utm_medium=website`
- `utm_campaign=onboarding`
- plus surface `utm_content` (e.g. `job_proof_demo_trial`)

**Critical:** Paid click UTMs / `fbclid` / `lp_variant` must still be persisted from the landing URL via `jcp-attribution.js` and merged into handoff where the existing handoff already supports them. Do not invent a new signup URL. Do not break FirstPromoter cookies.

Expert CTA: existing `/personalized-demo/` (or current authoritative expert path), with attribution appended.

---

## 9. Attribution contract

### Incoming (Meta expected scheme — do not hardcode macros in page links)
```
utm_source={{site_source_name}}
utm_medium=paid_social
utm_campaign=jcp_cold_proof_product_demo_2026q3
utm_content=proof_waste_v1
lp_variant=job_proof_demo
fbclid=…
```

### Persistence
- Read query params on load via existing `jcp-attribution.js`.
- First-touch sessionStorage key: `jcp_lead_attribution`.
- Add path fallback: `/job-proof-demo` → `job_proof_demo`.
- Preserve unknown UTM/click params where existing infrastructure already does.
- Stamp `data-jcp-lp-variant="job_proof_demo"` on document/body for this page.
- Trial / expert links decorated so attribution survives cross-domain signup where current handoff already does.

### QA URL
```
https://jobcapturepro.com/job-proof-demo/?utm_source=fb&utm_medium=paid_social&utm_campaign=jcp_cold_proof_product_demo_2026q3&utm_content=proof_waste_v1&lp_variant=job_proof_demo&fbclid=TEST_FBCLID
```

---

## 10. Analytics & CRM rules

### 10.1 PostHog
Theme does not currently own PostHog initialization (typically GTM / external). Implementation will:
1. Prefer a small shared helper if one exists; otherwise push events in a way GTM/PostHog can consume without duplicating parallel trackers.
2. Fire these **non-PII** events once per intended milestone (session/dedupe guards):

| Event | When |
|-------|------|
| `proof_lp_viewed` | Landing page view |
| `proof_demo_started` | User starts proof (hero/problem CTA) |
| `proof_job_viewed` | Step 1 visible / engaged |
| `proof_checkin_created` | Step 2 complete / check-in shown |
| `proof_outputs_viewed` | Step 3 aha outputs shown |
| `proof_demo_completed` | Value bridge / proof flow finished |
| `proof_trial_cta_clicked` | Primary trial CTA click |
| `proof_expert_cta_clicked` | Expert CTA click |

**Properties (non-PII only):** `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `lp_variant`, `fbclid` (if present), device context, referrer.

**Never send to PostHog event properties:** email, phone, name, customer PII.

Also mirror to `window.dataLayer` with the same event names so GTM can route to PostHog/Meta without theme-level Pixel installs.

### 10.2 Meta Pixel
- Pixel ID in production: `1440845294314184` (already installed outside/alongside theme — **do not** install a second pixel or re-init).
- Preserve PageView via existing setup.
- **Do not** fire Lead because someone views/starts ungated proof.
- Conceptual progression for GTM mapping (document; do not invent new standard event semantics in theme):
  - ViewContent / LP view
  - proof demo start (custom)
  - proof demo complete (custom)
  - trial CTA click (custom / existing Trial CTA patterns)
  - StartTrial / CompleteRegistration **only** at real approved signup milestone (app-side / existing)
- Preserve existing browser+CAPI `event_id` dedup if present in GTM/CAPI — theme must not double-fire Pixel events.
- Deliverable includes a **Meta event audit report** of what exists today vs what this page emits.

### 10.3 Matomo
- Preserve existing Matomo (`_paq`) patterns.
- Ensure campaign attribution can survive Meta click → this LP → trial.
- Do not add GA.

### 10.4 GHL
- **No contact create/update** on proof view/start/complete.
- Contact creation only at existing legitimate lead/signup milestones elsewhere.
- When a contact eventually exists, acquisition fields (`utm_*`, `lp_variant`) should still be available from persisted attribution for later forms/signup — reuse existing field mappings; no duplicate contacts for attribution only.

### 10.5 FirstPromoter
- Do not alter referral cookie/script behavior.
- Do not break existing FirstPromoter load/defer rules.

---

## 11. Design system & UX constraints

- Use existing JCP visual system (colors, type, buttons, spacing).
- Priorities: visual proof → speed → mobile → clarity → product realism.
- Avoid: walls of copy, generic stock, excessive animation, fake testimonials, fake activity toasts, fake scarcity, fake “published live” statuses, bloated comparison sections.
- Whitespace; product transformation is the hero.
- Mobile QA targets: **375 / 390 / 430** — no horizontal overflow, no hover-only interactions, trial CTA reachable soon after aha without excessive scroll.
- Performance: optimized/reused assets, lazy-load below fold, no heavy animation libraries, protect CWV.

---

## 12. Proposed file inventory (implementation)

| File | Role |
|------|------|
| `page-job-proof-demo.php` | Page template shell |
| `templates/job-proof-demo/content.php` (or similar) | Markup sections + proof steps |
| `css/pages/job-proof-demo.css` | Page styles |
| `assets/js/pages/job-proof-demo.js` | Proof stepper + event emits + CTA wiring |
| `inc/job-proof-demo.php` (optional) | Seed page, enqueue helpers, template registration |
| `inc/enqueue.php` | Conditional enqueue |
| `assets/js/core/jcp-attribution.js` | Path → `job_proof_demo` |
| `inc/wp-rocket.php` | Purge path |
| Existing campaign images under `assets/campaign/` | Job photo assets |

No changes to `jcp-demo.js` / survey flow.

---

## 13. State machine (proof UX)

```
idle (hero/problem visible)
  → started (step1 job)
  → checkin (step2)
  → outputs (step3 aha)
  → bridge (step4)
  → convert (trial screen)
```

- Browser back/forward: prefer history `pushState` per major step **or** in-page step index with hash (`#proof`, `#proof-outputs`, etc.) so back does not strand the user; refresh should not re-fire conversion events (dedupe keys in sessionStorage).
- Direct trial CTA from hero skips proof but still fires `proof_trial_cta_clicked` (or a clearly named sibling if product prefers distinguishing skip — default: same trial event with `source=hero_skip`).

---

## 14. Copy lock (authoritative strings)

Use the strings in the product brief (hero, problem, proof steps, trial). Do not substitute “marketing automation” headlines. Do not add unsupported outcome claims.

---

## 15. QA acceptance checklist

Simulated paid URL (see §9).

1. Page loads at `/job-proof-demo/` (not redirected to `/demo/`).
2. Attribution params captured into session storage / payload helpers.
3. Proof starts with **no form**.
4. All proof steps work; completion works.
5. Trial CTA uses real onboarding URL; attribution preserved on handoff.
6. PostHog/`dataLayer` events fire once per milestone (no PII).
7. Matomo campaign context intact (as far as theme can verify).
8. Meta Pixel not re-initialized; no Lead on ungated proof start.
9. GHL not populated by anonymous proof events.
10. FirstPromoter intact.
11. Mobile 375/390/430 pass.
12. Existing `/demo/` unchanged.
13. Existing signup/onboarding unchanged.
14. Back/forward does not break experience.
15. Refresh does not duplicate conversion events.
16. No console errors / 404s for required assets.
17. No PII leaked to analytics properties.

---

## 16. Deliverable report (post-implementation)

Must include:
A. Files changed  
B. New route/components  
C. Exact trial destination used  
D. Every PostHog event implemented  
E. Every Meta event touched/reused  
F. Matomo behavior  
G. GHL behavior  
H. FirstPromoter behavior  
I. Attribution persistence implementation  
J. Mobile QA results  
K. Production build/test results  
L. Anything not verified  
M. Existing tracking bugs discovered  

**Rule:** Do not claim verified unless actually tested.

---

## 17. Risks & open items for approvers

1. **PostHog/Meta live outside the theme.** Theme will emit `dataLayer` (+ `posthog.capture` if present). GTM mapping of `proof_*` → PostHog/Meta custom events may need a separate GTM task.
2. **Onboarding UTM defaults** currently use `utm_source=jobcapturepro.com` / `utm_medium=website` as the app-link shell; paid click UTMs are stored separately in attribution and merged where handoff supports it. Approvers should confirm this matches how the app/GHL currently expects paid attribution.
3. **Case-study tertiary** — include only if 90-day program is still live/approved.
4. **Canonical sample job** — recommend reusing existing Mason Rd / water-heater campaign assets for consistency with other JCP demos.
5. **Existing `/job-proof/`** remains the older “Proof Waste” long LP that still CTAs into `/demo/`. This new route is intentionally separate.

---

## 18. Approval ask

Approve **Architecture Option A** (dedicated `page-job-proof-demo.php` + on-page ungated proof stepper + real trial handoff + attribution/path-map extensions only), with tracking rules in §10, before implementation begins.

**Approver responses needed:**
- [ ] Approve Option A as specified
- [ ] Request changes (list)
- [ ] Reject in favor of Option B/C (explain)

---

## Appendix A — Contrast with existing `/demo/`

| | Existing `/demo/` | New `/job-proof-demo/` |
|--|-------------------|-------------------------|
| Gate | Email + trade (+ optional business name) before run | None before aha |
| Goal | Full guided product tour (5 steps) | One fast aha → trial |
| Primary CTA destination from paid LPs | Often `/demo/?lp_variant=…` | On-page proof, then trial |
| Duration | Longer | ~45–90s |
| GHL on start | Survey/viewed webhooks possible | No contact from proof alone |

## Appendix B — Existing paid LP registry (do not break)

| Key | Slug |
|-----|------|
| `contractor_demo` | `/contractor-demo/` |
| `formula` | `/contractor-formula/` |
| `nature_doc` | `/contractor-nature/` |
| `proof_waste` | `/job-proof/` |
| `founder` | `/why-we-built-jcp/` |
| **`job_proof_demo` (new)** | **`/job-proof-demo/`** |
