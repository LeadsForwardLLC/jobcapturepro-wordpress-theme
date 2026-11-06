# Proof Gap Performance — Hybrid Design

**Status:** Approved (Hybrid C) — implementing 2026-09-29  
**URL:** `/proof-gap/`  
**Constraint:** No UX/funnel/attribution contract changes. Keep `noindex,nofollow`.

## Approach

1. **First-party critical path** — fewer blocking CSS files; async mocks; sized WebP logo.
2. **Meta ASAP** — early lightweight Pixel PageView + first-party `fbclid` capture.
3. **Defer heavy analytics** — GTM / PostHog SDK / Matomo / FPR after first interaction (same pattern as demo shell).
4. **JS** — exclude Proof Gap survey scripts from Rocket Delay JS; remove forced reflow.

## Success

- Faster mobile LCP/TBT vs PSI baseline (57 / LCP ~7s).
- Survey + email + trial handoff unchanged; paid UTMs/`fbclid` preserved.
- Robots remain `noindex,nofollow`.
