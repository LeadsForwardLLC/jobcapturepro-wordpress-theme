# Proof Gap Survey — Phase 1 Foundation Design

**Route:** `/proof-gap/`  
**Experiment id / lp_variant:** `proof_gap_survey_v1`  
**Status:** Shell / foundation only (no final copy, art, or nurture)

## Intent

Paid Meta/TikTok traffic lands directly in a mobile-first survey app. First question = landing page. No long marketing page. Isolated from `/demo/` and `/proof-sprint/`.

## Architecture

- Mirror proof-sprint route pattern: `inc/proof-gap.php` + `page-proof-gap.php` + isolated CSS/JS
- Explicit JS state machine (8 states) with local persistence (7-day TTL) + back/resume
- CRM: new REST route reuses durable demo lead queue helpers with **survey-specific** event/tags (`proof-gap-survey`), not `demo-opt-in`
- Analytics: dataLayer events without PII; Lead only after durable email capture + event_id
- Trial: existing onboarding URL helper + attribution decorator; opaque `pg_handoff` token prepared (app may not consume yet)

## Out of scope (Phase 1)

Final copy, illustrations, product mocks, testimonials, GHL nurture, ads, Meta campaign config.
