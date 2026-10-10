# Proof Gap v11 — Mobile Funnel Screenshots

**Easy find:** `proof-gap-screenshots/` at the theme root → open `index.html`.

## Captured path (v11 — 8 states)

1. Welcome
2. Trade (HVAC)
3. Workflow (Housecall Pro) — auto-advance with micro-confirm
4. Jobs/week (11–20)
5. Visibility question (About half)
6. Result + email (combined screen)
7. App sim (compressed ~4s)
8–10. Trial bridge with dest previews (Website, Social, Google)

## Viewports

- `mobile-*.png` — 390×844

## Re-run

```bash
cd .superpowers/sdd/screenshots/proof-gap
MOBILE_ONLY=1 node qa-copy-pass.js
```
