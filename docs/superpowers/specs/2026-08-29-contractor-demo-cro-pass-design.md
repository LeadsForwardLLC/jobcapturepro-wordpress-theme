# Contractor-demo CRO pass (`/contractor-demo/`)

**Date:** 2026-08-29  
**Status:** Implemented  
**Scope:** Paid Meta landing at `/contractor-demo/` only (not live `/`, not `/home-preview/`)

## Goal

Make the paid landing page visually correct and higher-converting without changing section order (ads already match the current spine).

## Problems

1. **Duplicate benefits UI** — Under “One photo. Here's what actually happens next.”, photo job-flow cards are followed by an identical icon-card row. Root cause: `.jcp-job-flow` steps lack `data-jcp-array-item`, so the front-end collection editor rebuild appends `ranking-factor-card` items instead of replacing job-flow steps.
2. **Demo-preview layout** — “See the whole workflow…” band feels full-bleed / left-pinned; should share the same container width/gutters as the hero.
3. **Headline** — “on a business like yours” is softer than “on our business.”
4. **Funnel polish** — Spacing, meta-stat wrap, and mid/end CTA hierarchy need tightening for cold traffic.

## Approach

Seed + CSS + editor fix (keep current block order).

## Design

### 1. Kill duplicate (editor + render)

- Add `data-jcp-array-item` on each `.jcp-job-flow__step` in PHP render.
- In `page-collection-editor.js`, when the array host is `.jcp-job-flow` (or benefits `variant === job_flow`), rebuild with a job-flow step template — never `buildFactorCard`.
- Result: only the photo flow remains (public + edit mode).

### 2. Demo-preview section

- Headline → **See the whole workflow on our business**.
- Layout width: **default** container (not `full` / `wide`); gutters match hero.
- Keep split: copy left, phone mockup right; soft band/card remains but inset inside the container.

### 3. Funnel CRO polish (same order)

Current order stays: hero → core_mechanic (in hero meta) → benefits (job_flow) → demo_preview → authority → problem → how_it_works → testimonials → faq → final_cta.

| Area | Change |
|------|--------|
| Hero | Balance meta-stat line wrap (“Minimal crew effort”); single primary CTA unchanged |
| One-photo flow | Keep 5 visual steps; optional clearer closing; no second CTA clutter |
| Demo-preview | Strong primary CTA; container + headline as above |
| Authority / problem / how_it_works | Tighter vertical rhythm + contrast so the eye keeps moving to CTAs |
| FAQ + final CTA | Final CTA is the unmistakable last conversion beat |

### 4. Ship path

- Update `inc/niche-landing/dummy-campaign.json` (headline + any layout defaults).
- Campaign CSS in `css/pages/niche-landing.css` (demo_preview width, spacing, meta wrap).
- Bump `jcp_contractor_demo_seed_version` (v9 → v10) and force-refresh seed so production `/contractor-demo/` picks up content/layout on deploy.

## Out of scope

- Live homepage `/` and `/home-preview/`
- Niche landers, ad creative, GHL flows
- Reordering sections or a full redesign

## Success criteria

- [ ] No icon-card duplicate under the one-photo section (edit + public)
- [ ] Demo-preview aligns to hero container width; no left-edge bleed
- [ ] Headline reads “See the whole workflow on our business”
- [ ] Page feels tighter and CTA path clearer without section reorder
- [ ] Changes land on production via seed bump + theme deploy
