# Ultimate Job Proof Demo — Technical Report (A–M)

**Shipped:** 2026-09-15  
**Routes:** `/job-proof-demo/` · `/job-proof-demo/demo/`  
**Screenshots:** `.superpowers/sdd/screenshots/ultimate/`

## A. Product capabilities verified
- Completed-job → website / GBP / social / reviews / Directory are real product concepts.
- AI generates **check-in descriptions / publish copy**, not photo enhancement.
- No public safe photo-upload → AI demo endpoint → **sample job only**.

## B. Integrations verified
- Housecall Pro / Jobber / ServiceTitan / CompanyCam: **marketing copy only** in WP layer (no connector code).
- LP does **not** show unverified logos or “live” CRM names; copy says workflows vary by setup.

## C. Global components reused
- Campaign shell, brandbar, niche rankings/CTA patterns, story-moments previews, survey form chrome, testimonial renderer, directory preview helper, case-study exit card CSS.

## D. Custom UI created
- Hero product canvas + in-place transformation.
- Integrations / differentiator / AI flow / founder thumbnail sections.
- `/job-proof-demo/demo/` personalized run environment + large result previews.

## E. Analytics event mapping
See `.superpowers/sdd/progress.md` table. One event per stage; Stage 1 reuses `PaidLandingView` (not a second `PaidLandingViewed`). Meta Lead only via `demo_opt_in` after CRM success.

## F. PostHog funnel verification
Events are staged in dataLayer for GTM→PostHog. Dashboard build still required in PostHog UI (blocker #6).

## G. GHL nurture verification
Opt-in still posts to `jcp/v1/demo-survey-submit` (same as `/demo/`). **Manual check required:** nurture email links must point to `/job-proof-demo/demo/` (not `/demo/`) if currently mis-pointed.

## H. Meta Lead / CAPI dedup
Browser Lead fires only on successful CRM path via `demo_opt_in`. CAPI `event_id` dedup still **unverified** (blocker #2).

## I. Attribution
`jcp-attribution.js` preserves UTMs / fbclid / lp_variant; child path `/job-proof-demo/demo` inherits `job_proof_demo`.

## J. Trial handoff
Trial links decorated with attribution + email/niche when known. Surface keys: `job_proof_demo_*`.

## K. sessionId
Still uses hardcoded onboarding sessionId helper (blocker #1).

## L. Responsive QA
Captured 1440 desktop full + 390 mobile full + section shots. Hero transform works on mobile. Broader 375/430/1280/1600 pass still recommended.

## M. Remaining blockers
1. Hardcoded onboarding sessionId  
2. Meta CAPI event_id dedup  
3. Confirm GHL nurture destination URLs  
4. Live paid→GHL attribution check  
5. Trial attribution in app  
6. PostHog funnel dashboard  
7. Full viewport matrix QA  

## Bug fixed during QA
Child slug `demo` matched `/demo/` asset detection and loaded `survey.js` instead of funnel JS/CSS. Enqueue order + `is_demo` exclusion fixed.
