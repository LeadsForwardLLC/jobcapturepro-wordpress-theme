# Homepage sales-deck redesign

**Date:** 2026-08-26  
**Status:** Draft for review  
**Approach:** B — sales-deck homepage on a preview route first, then cutover  
**Primary audience:** Cold contractor owners + agencies (one page, contractor-led)  
**Primary CTA:** Launch interactive demo · Secondary: Start free  

---

## 1. Problem

The live homepage (`jobcapturepro.com`) is competent SaaS: clean, clear, low-risk — and low persuasion.

- Voice is polite feature marketing, not the sales deck’s story.
- Demo and Maps proof arrive too late (or not at all).
- Local Falcon / SoLV (Acculevel) proof exists only in the sales tool.
- Benefit icon grids and early testimonials spend scroll without belief.
- Social proof is thin (new company) — the page currently leans on quotes instead of product theater.
- Nav still framed around “trial” language in places; CTAs should say **Start free** / **Start for free**.

The sales tool already sells the winning narrative. The homepage should **act like that deck** — edged, a bit humorous, visual, interactive — without becoming a gimmick microsite.

---

## 2. Goals

1. **Belief before ask** when social proof is thin: demo + one-job theater + anonymized Local Falcon before/after.
2. **Sales-deck spine** on the public homepage (preview first).
3. **Edged, dry contractor humor** — sharp, never meme-y.
4. **Demo-first conversion** with Start free as secondary.
5. **Global design system lift** — tokens and reusable modules so pricing, trades, and landers inherit later.
6. **Zero risk to live home** until explicit cutover from `/home-preview/`.

### Non-goals (this phase)

- Full rewrite of every marketing page in one PR.
- Ranking guarantees or unverified lead-lift %.
- Naming Acculevel on the public site (anonymous case only).
- WebGL / immersive microsite (Approach C).

---

## 3. Delivery model

| Phase | What | Live `/` |
|-------|------|----------|
| 1 | Build `/home-preview/` (noindex) using new modules + copy | Unchanged |
| 2 | Iterate with stakeholder demos on preview URL | Unchanged |
| 3 | Cutover: promote preview content/preset to front page | Swapped |
| 4 | Cascade shared modules to pricing / trades / campaign landers | Incremental |

**Preview route:** WordPress page at `home-preview` (or equivalent), `page-jcp-blocks.php`, preset `home_v2` / campaign-like flags: `noindex`, optional hide chrome parity with marketing pages as needed.

**Seed:** Versioned seed (same pattern as contractor-demo) so preview content refreshes from theme JSON without fighting editor drift during build.

---

## 4. Funnel architecture (approved)

1. **Hero** — edged hook + motion + Demo / Start free  
2. **Camera-roll funeral** — agitation + Without/With contrast  
3. **One job → five surfaces** — interactive proof theater  
4. **Interactive demo band** — early, major CTA  
5. **Local Falcon before/after** — anonymous 111-location case  
6. **How it works** — Capture → Optimize → Distribute → Convert (QR)  
7. **Who it’s for** — contractors + compact agency strip  
8. **Light social proof** — equal small quotes  
9. **Objections / FAQ** — sales-script answers  
10. **Final close** — Demo + Start free  

### Drop / demote vs current home

- Mid-page benefit icon grids as primary persuasion  
- Testimonials before product proof  
- Soft “benefits that show up in the market” filler  
- Giant featured agency quote  

---

## 5. Section specs

### 5.1 Hero

- **Composition:** One first-viewport composition (brand-forward). Not a dashboard.
- **Brand:** JobCapturePro as hero-level signal in visual + lockup, not nav-only.
- **Headline direction:** *Your crew already took the photo. It’s just dying on their phone.* → payoff *One job in. Proof everywhere.*
- **Sub:** One sentence — Maps, website, social, directory, on-site review QR.
- **CTAs:** Primary **Launch interactive demo** → `/demo/` · Secondary **Start free** · Microcopy: *No signup for the demo · About 2 minutes*
- **Visual:** Edge-dominant job atmosphere + phone check-in → channels lighting up (2–3 deliberate motions).
- **Exclude from hero:** stats strips, Local Falcon, FAQ chips, agency pitch.

### 5.2 Camera-roll funeral

- **Headline:** *The job gets finished. The marketing usually dies in the camera roll.*
- **Contrast:** Without JCP (job → photo sits → nothing) vs With JCP (job → proof → published → review ask).
- **Punchline:** *Your techs aren’t lazy. Your photos just never got a job description.*

### 5.3 One job → five surfaces

- **Headline:** *One completed job. Five places it should already be working.*
- **Surfaces:** Maps/GBP · Website · Social · Directory · Review QR  
- **Interaction:** Real job still → five output frames stagger on scroll/tap (chrome overlays OK if true UI screenshots unavailable).
- **Punchline:** *Your CRM keeps the job file. We put the proof where customers look.*
- Mobile: stacked or horizontal snap with same five beats.

### 5.4 Interactive demo band

- Early (immediately after five-surface theater).
- Copy aligned with contractor-demo: trade personalization, ~2 minutes, honest friction for Start free (work email) without scaring the demo.
- Primary CTA: Launch interactive demo.
- Prefer live phone mockup / app shell visual already used on site.

### 5.5 Local Falcon module (anonymous)

- **Customer framing:** Anonymous basement waterproofing company · **111 locations** · no Acculevel name.
- **Headline direction:** *Same crews. Same jobs. Wildly different Maps footprint.*
- **Primer:** Local Falcon = grid across service area; SoLV = how often in Google 3-Pack across pins.
- **Cards:**
  1. Triadelphia, WV — “basement waterproofing” — **0% → 100% SoLV**
  2. Monroe, MI — same keyword — **0% → ~96% SoLV** (optional honest secondary keyword note if used)
- **Assets:** Reuse sales-tool scan imagery under `assets/jcp-sales-tool/assets/acculevel-localfalcon-*` (public-safe; identity anonymized in copy only).
- **Interaction:** Before/after slider or tap-reveal; viewport enter motion.
- **Guardrail:** *This isn’t a ranking guarantee. It’s what consistent, real job proof can do to local Maps presence.*
- **CTA:** Second major Demo / Start free moment.
- **Never:** Unverified lead-lift % on homepage.

### 5.6 How it works

Four steps matching the deck engine:

1. **Capture** — photo check-in / job completion signal  
2. **Optimize** — geotag + local SEO into the check-in  
3. **Distribute** — website, GBP/Maps, social, directory  
4. **Convert** — on-site QR review ask (never “automatic reviews”)

Tight; numeric; one supporting line each. No secondary essay.

### 5.7 Who it’s for + agency strip

- Contractor cards compressed (owner / office / crew) — or chips if cards feel heavy.
- **Agency band:** *Agencies: stop selling retainers for posts your clients never send you.* One sentence + soft CTA (demo or partner link). Must not compete with hero CTAs.

### 5.8 Light social proof

- Equal-weight quotes from existing review set (Trent, Brian, Eddie, Peter).
- No oversized agency feature.
- Optional faces row if assets remain strong.

### 5.9 FAQ / objections

5–7 items, deck-sharp. Required themes:

| Question | Answer gist |
|----------|-------------|
| What does my crew actually have to do? | Photo / show QR |
| We already have CRM / CompanyCam | Keep it; we publish what they don’t |
| Guarantee rankings? | No; feed real job proof |
| Will techs use it? | One check-in |
| What do I get in the demo? | Trade → personalized workflow ~2 min |
| Agency angle (optional) | Run proof at scale for clients |

### 5.10 Final close

- **Headline direction:** *Your next completed job could start working harder than your last ad.*
- CTAs: Demo · Start free  
- Microcopy: Demo no card · Start free = work email · ~2 minutes  
- Optional sticky mobile CTA on preview only (evaluate before globalizing).

---

## 6. Voice & copy principles

- Dry contractor humor; confident; slightly irreverent.
- Prefer concrete nouns (camera roll, Maps, QR, check-in) over abstract “visibility/synergy.”
- Soften absolute zero-friction claims (align with contractor-demo CRO: no “0 busywork” absolutism).
- CTAs: **Start free** (buttons) / **Start for free** (prose). Never “free trial” on new surfaces.
- No ranking guarantees; Maps proof framed as measured SoLV movement for one anonymized operator.

---

## 7. Motion system (global)

Three reusable patterns:

1. **Reveal** — section enter  
2. **Stagger** — multi-item proof  
3. **State change** — before/after, check-in → channels  

Honor `prefers-reduced-motion`. Hero: at most one subtle continuous loop.

---

## 8. Design system / global impact

New shared marketing layer (not homepage-only one-offs):

| Module | Reuse later on |
|--------|----------------|
| CTA pair (Demo / Start free + microcopy) | Pricing, trades, landers |
| Five-surface proof flow | Campaign landers, features |
| Local Falcon compare | Pricing (Scale proof), sales-adjacent pages |
| Without/With contrast | Problem sections sitewide |
| Objection FAQ pattern | Pricing, landers |
| Final close band | Most marketing templates |
| Tokens (type, color, space, motion) | All `jcp-page-*` marketing |

Homepage preview is the **flagship consumer** of the system; other pages adopt in Phase 4.

Preserve established JCP brand color (coral/orange primary) — elevate atmosphere with gradients/imagery, not a purple/cream AI-default look.

---

## 9. Technical approach (high level)

- **Content:** New dummy JSON (e.g. `dummy-home-v2.json`) + preset `home_v2` block order matching §4.
- **Render:** Extend niche-landing / page-blocks with variants:
  - `problem` contrast (reuse contractor-demo pattern if present)
  - `benefits` or new `proof_surfaces` job-flow variant
  - new `local_falcon_proof` block (or authority-like props block)
  - agency strip props on `who_its_for` or dedicated slim block
- **Assets:** Register/copy Local Falcon webp/jpg into a public marketing path if sales-tool path shouldn’t be hotlinked long-term; either is fine for preview if enqueue-safe.
- **CSS:** `css/pages/home-v2.css` (or extend `home.css` behind `.jcp-home-v2`) + shared tokens in base/sections.
- **JS:** `home-interactions` extended or `home-v2-interactions.js` for rotator, falcon compare, five-surface stagger.
- **Analytics:** Keep existing demo / CTA event names where possible; new sections should fire view/click hooks consistent with site GTM.
- **SEO:** Preview `noindex`; on cutover preserve/upgrade title & meta for conversion + clarity (no keyword stuffing).

---

## 10. Success criteria

Preview feels like the sales deck: visitor understands the machine in &lt;30s, sees Maps proof, hits demo without wading through feature cards.

Qualitative bar:

- [ ] First viewport passes “brand test” (still JCP without nav)  
- [ ] Demo CTA appears before mid-page filler  
- [ ] Local Falcon before/after is unmistakable and anonymized  
- [ ] Humor lands without undermining trust  
- [ ] Agency strip present but non-dominant  
- [ ] Live `/` unchanged until cutover approval  
- [ ] Shared modules/tokens exist for Phase 4 cascade  

---

## 11. Open points (resolved in brainstorm)

| Topic | Decision |
|-------|----------|
| Audience | Contractors + agencies, contractor-led |
| Primary CTA | Interactive demo |
| Acculevel naming | Anonymous (111-location basement waterproofing) |
| Build path | `/home-preview/` then cutover |
| Global system | Tokens/modules shared; cascade after homepage |

---

## 12. Out of scope reminders

- Replacing the sales tool itself  
- Inventing new case-study metrics  
- Mandatory phone number in hero microcopy  
- Dark-mode / purple glow / pill-stat clutter aesthetics  
