# Job Proof Demo Paid Funnel — Implementation Plan

**Date:** 2026-09-13  
**Spec:** `docs/superpowers/specs/2026-09-13-job-proof-demo-paid-funnel-design.md`  
**Amendments:** Approved Option A + Meta audit gate, paid UTM survival, session audit, single analytics path, no street address, 3-moment UX.

## Architecture
- Template: `page-job-proof-demo.php`
- Markup: `templates/job-proof-demo/content.php`
- CSS/JS: `css/pages/job-proof-demo.css`, `assets/js/pages/job-proof-demo.js`
- Bootstrap/seed: `inc/job-proof-demo.php`
- Shared: attribution path map; onboarding handoff overwrite of marketing-default UTMs; dataLayer-only events

## 3 user moments
1. Finished Job
2. JCP Transformation (check-in)
3. Outputs + value bridge + trial CTA (same final state)

## Analytics
`dataLayer` only via one helper. No `posthog.capture`. Session-deduped `proof_*` events.

## Session ID
Document: production uses shared hardcoded `sessionId` (no issuance API in theme). Do not invent random UUIDs.
