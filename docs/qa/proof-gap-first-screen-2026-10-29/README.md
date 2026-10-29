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

The new supporting line (“see where … could be working harder”) is directionally right; the immediate on-page result is a **quantitative gap estimate**, with channel-level “where” detail arriving after email / in the demo. Acceptable for this PR; optional follow-up softener documented in the PR body.
