# Sales Tool (WordPress) Design

**Date:** 2026-08-12  
**Status:** Approved for implementation (Approach 1)

## Goal

Convert `assets/jcp-sales-tool/` into a theme-native, screenshare-ready sales presentation that partners, affiliates, and internal reps can customize from WP admin. Personalize from assessment-style fields. Keep pricing, trial CTAs, integrations, and review language aligned with the live site.

## Decisions

| Topic | Decision |
|--------|----------|
| Backend | CPT `jcp_sales_deck` **and** page template `Sales Tool` for blank live calls |
| Story modes | `contractor` \| `partner` (affiliate = presenter role, uses contractor story + own branding/CTA) |
| Present mode | Hide rep chrome; keep progress + next/back; keep collaborative diagnose/gap |
| Social proof | Review snippets **always**; Acculevel chapter **optional** per deck |
| Pricing | Show recommended plan + price by default; prices from **shared PHP config** used by `/pricing/` |
| Assessment | Manual fields now; stub “Load assessment” for future Fluent/GHL import |
| Reviews copy | On-site QR / link handoff — never “automatic send / nothing for crew” |
| Integrations | Call out Housecall Pro, Jobber, ServiceTitan, CompanyCam when relevant |

## Architecture

1. **`inc/pricing-plans.php`** — single source of truth for plan names, monthly prices, feature bullets, trial CTA URL/label. Localized to pricing page and sales tool.
2. **`inc/sales-tool/`** — CPT, meta box, config builder, asset enqueue helpers.
3. **`page-sales-tool.php`** — full-document shell (like Demo), no site chrome.
4. **CPT single** — same shell; config from post meta.
5. **Front-end** — refactor `assets/jcp-sales-tool/` to consume `window.JCP_SALES_TOOL`, theme CSS variables, Present mode.

### URLs

- Live blank: WordPress page with Sales Tool template (e.g. `/sales-tool/`)
- Branded decks: `/sales/{slug}/` rewrite for CPT

### Config shape (localized)

```
{
  assetBase, pricingUrl, trialUrl, trialLabel,
  plans: [{ id, name, monthly, ... }],
  presenter: { type, name, logoUrl },
  prospect: { company, trade, mode, ...assessmentFields },
  flags: { showPricing, showAcculevel, presentByDefault },
  reviews: [{ name, role, quote, rating? }],
  cta: { primaryLabel, primaryUrl, secondaryLabel, secondaryUrl }
}
```

## Chapter flow

1. Opening (personalized company / mode)
2. Problem (four proof surfaces; review = QR on site)
3. Diagnose (assessment-aligned fields)
4. Proof gap (calculated unused jobs)
5. How it works (capture → optimize → distribute → convert via QR)
6. Proof (reviews always; Acculevel if enabled)
7. Right fit (segment)
8. Plan (shared pricing + link to `/pricing/`)
9. Objections (rep prompts; soften in Present or keep as talk track)
10. Close (next step + trial CTA + recap; hide notes in Present)

## Backend fields (CPT + page meta)

- Presenter type: Internal / Affiliate / Agency partner  
- Presenter name, logo  
- Audience mode default: Contractor / Partner  
- Company / prospect name, trade/niche  
- Jobs/week, locations, crew band, software stack (multi)  
- Photo frequency, photo destination, publish habit, review habit, challenges, timeline  
- Show Acculevel, show pricing  
- Primary CTA override (optional; default trial from global settings)  
- Assessment load stub (email lookup UI; Phase 2 wires data)

## Present mode (conversion)

- Hides: sidebar admin list styling excess, Customize, Reset, mode switcher, save chip, close-notes fields, objection “Proof check” if too internal  
- Shows: branded deck, progress, next/back, diagnose/gap interactions  
- Body class `is-presenting`

## Out of scope (Phase 2)

- Live Fluent Forms / GHL assessment auto-import  
- Per-rep auth / private decks  
- Analytics on chapter drop-off  

## Success criteria

- Create branded deck in admin → public URL works screenshare-clean in Present  
- Blank Sales Tool page works for live fill-in  
- Plan prices match pricing page without duplicate hardcoding  
- Reviews + QR language correct; integrations mentioned  
- Acculevel can be toggled off
