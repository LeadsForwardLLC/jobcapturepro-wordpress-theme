# Proof Sprint Performance — Hybrid Design

**Status:** Shipped 2026-09-29 (same Hybrid C as Proof Gap)  
**URL:** `/proof-sprint/`  
**Constraint:** No UX/funnel/attribution changes. Keep `noindex,nofollow`.

## Baseline (mobile PSI)
- Score 81 · LCP 2.0s · TBT 600ms · SI 4.6s · CLS 0
- Main costs: 11+ render-blocking CSS files, full PNG logo, full job-proof.webp, Meta/GTM/PostHog/Matomo on load

## Approach
1. Slim first-party CSS to `base` + `proof-sprint` (self-contain brandbar/FAQ/maps/btn).
2. Force theme WebP logo; keep already-sized campaign assets (don’t “optimize” `-640` back to full).
3. Early Meta PageView after idle/interaction; defer GTM / PostHog SDK / Matomo / FPR.
4. Dequeue unused WP/theme chrome; Rocket exclusions for sprint scripts + Meta.

## Success
- Fewer blocking CSS requests; smaller LCP image bytes; lower TBT without dropping ads PageView.
- Trial CTAs + UTM handoff unchanged.
