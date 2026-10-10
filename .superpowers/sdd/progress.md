# Job Proof Demo — Ultimate Rebuild Progress

**Date:** 2026-09-15  
**Routes:** `/job-proof-demo/` (LP) · `/job-proof-demo/demo/` (personalized run)  
**`/demo/`:** unchanged

## Capability truth (audit)

| Area | Verdict |
|------|---------|
| Photo upload → AI demo endpoint | **None** — sample job only; no fake analysis |
| Housecall Pro / Jobber / ServiceTitan / CompanyCam | **Marketing copy only** in WP layer — no logos/list as live |
| AI | Check-in **descriptions/content** — not image enhancement |
| GBP / social / website / directory / reviews | Supported product concepts; publishing “where connected” |

## Analytics mapping (one event per stage)

| Funnel stage | Event name (dataLayer) | PostHog | Matomo | Meta | GHL | Trigger | Key props |
|---|---|---|---|---|---|---|---|
| 1 PaidLandingViewed | `PaidLandingView` (reuse) | via GTM | via GTM | — | — | LP load | utm_*, lp_variant, device, referrer |
| 2 HeroDemoStarted | `HeroDemoStarted` | via GTM | via GTM | — | — | Sample job click | cta_source |
| 3 HeroTransformationCompleted | `HeroTransformationCompleted` | via GTM | via GTM | — | — | Canvas done (~5–7s) | cta_source |
| 4 DemoFormViewed | `DemoFormViewed` | via GTM | via GTM | — | — | Opt-in in view / scroll | — |
| 5 DemoFormSubmitted | `DemoFormSubmitted` + `demo_opt_in` | via GTM | via GTM | **Lead** only on `demo_opt_in` after CRM OK | upsert via `demo-survey-submit` | Form submit | trade, crm_saved |
| 6 PersonalizedDemoViewed | `PersonalizedDemoViewed` | via GTM | via GTM | — | demo-viewed-submit | Run page load (opted-in) | trade |
| 7 PersonalizedDemoStarted | `PersonalizedDemoStarted` | via GTM | via GTM | — | demo_run_started | Sequence start | — |
| 8 PersonalizedDemoResultsViewed | `PersonalizedDemoResultsViewed` | via GTM | via GTM | — | demo_publish_completed | Results reveal | — |
| 9 TrialCTAClicked | `TrialCTAClicked` | via GTM | via GTM | — | demo_converted | Trial click | cta_source |
| 10 TrialStarted | App onboarding (not WP) | app | — | TrialStarted only if legitimate | — | Onboarding start | — |
| 11 ActivatedTrial | App | app | — | — | — | Activation | — |
| 12 PaidCustomer | App / billing | app | — | — | — | Paid | — |

No PII in anonymous analytics. No double-fire of old `proof_*` + new names.

## Launch blockers (still open)

1. Hardcoded onboarding `sessionId`
2. Meta browser/CAPI Lead dedup (`event_id`) unverified
3. GHL nurture email link destinations (confirm vs `/demo/` — update manually if needed)
4. Paid attribution into GHL (preserve; verify in live)
5. Trial attribution handoff (UTM + email/niche params)
6. PostHog funnel dashboard build (events now staged)
7. Responsive QA across 375–1600 (pending screenshot pass)
