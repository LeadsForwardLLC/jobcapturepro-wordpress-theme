# Case study last-resort CTA + capacity bar

**Date:** 2026-09-08  
**Status:** Approved

## Summary

Keep the existing 10-company / 90-day case study offer. Add a truthful WP-admin capacity bar on `/case-study/` based on **selected/claimed spots only** (max 10). Surface a tertiary last-resort CTA after trial + 1-on-1 demo options, and a **desktop-only** exit-intent popup on marketing pages. Never interrupt the main demo/trial flow.

## Capacity metric

- Option: `case_study.spots_claimed` in Global Settings (integer 0–10)
- Total fixed at 10
- Primary label: “{n} of 10 companies selected”
- Clarifier: “Application required — selection is not automatic”
- Do **not** use applications-under-review as the main metric

## Placement

1. `/case-study/` — capacity bar near hero / apply area  
2. Demo results + post-demo panel — tertiary link under 1-on-1  
3. Desktop exit-intent on home + paid campaign LPs only (not `/demo/`, not mid-demo, not mobile)

## Analytics

- `CaseStudyCTAClicked` / `CaseStudyExitIntentShown` once per session  
- No duplicate Meta Lead events
