# Proof Gap first-screen copy — visual QA (2026-10-29)

Review-only PR visual pack for the P0 welcome copy / layout pass.

## Contents

- `before/` — production screenshots (`control` + `direct_question`, desktop + mobile)
- `after/` — local preview screenshots of the proposed first screen (same matrix)
- `preview.html` — static after-preview used for screenshots (not a production route)

## Recapture

```bash
node scripts/capture-proof-gap-first-screen.mjs
```

## Copy accuracy note

Assessment delivers: 4 questions → estimated unused finished-job volume → email for a personalized job example → product demo → trial CTA.

Current sub copy matches that deliverable (“estimate how much is going unseen”).

## Logo note

Production chrome uses `assets/brand/jcp-logo-dark-320.webp` (location-pin lockup). An earlier after-preview used a placeholder orange “J” circle — that was preview-only, not a production change. Preview now loads the real pin logo asset.
