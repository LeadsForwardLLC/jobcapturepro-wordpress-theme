# Campaign Landing Page — Conversion Layout Redesign

**Date:** 2026-07-27  
**Status:** Awaiting user review  
**Approach:** A — CSS-first + small PHP hooks  
**Scope:** Campaign preset pages only (`.jcp-page-campaign` / `body.jcp-landing-chrome-hidden`)

---

## Non-negotiables

- **Zero copy changes** — no rewrite, shorten, expand, or invent of headlines, body, CTAs, FAQ, trust lines, or form shortcodes.
- **No offer / brand identity changes.**
- **No global style bleed** — new rules scoped to campaign classes; shared renderers only get optional campaign wrappers/classes.
- Preserve anchors (`#apply`), Fluent Forms shortcodes, schema, tracking, analytics hooks.
- Lightweight: no new libraries; tiny JS only for sticky CTA + smooth scroll + form-in-view.

---

## Current problems (implementation)

| Area | Today |
|------|--------|
| Hero | Centered copy-only (`show_visual: false`); tall, narrow H1 wrap |
| Page width | Content feels like a skinny column on 1440–1920 |
| Spacing | Large arbitrary gaps; sections not rhythmically connected |
| Core mechanic | Weak strip; looks detached |
| Cards (problem / benefits / HIW) | Small, flat, under-width on desktop |
| Demo preview | Oversized / unbalanced vs page container |
| Form | Thin panel, no min-height → looks empty/broken |
| Final CTA | Underpowered |
| Header | Logo-only; no header CTA; no mobile sticky apply |

---

## Architecture

### New assets (campaign-scoped)

1. **`css/pages/campaign-landing.css`** — primary redesign surface; tokens + section layouts under `.jcp-page-campaign`.
2. **`assets/js/pages/campaign-landing.js`** — smooth scroll to `#apply`, mobile sticky CTA with IntersectionObserver hide-when-form-visible, `prefers-reduced-motion` respect.
3. **Enqueue** only when `jcp_page_is_campaign_landing()` (or body class / main class present).

### Thin PHP hooks (no fork of every section)

| Hook | Change |
|------|--------|
| `campaign-preset.php` / hero finalize | Enable split hero layout for campaign (`show_visual` / centered→split) **without changing copy**; product-proof frame uses existing media props or a CSS composition around existing image slots |
| `templates/global/header.php` brandbar | Within content width: logo + optional primary CTA link pulled from page flat content (`hero.cta_primary` label/url) when chrome hidden |
| Form embed render | Stronger panel shell classes + min-height / loading placeholder markup (no marketing copy — structural/aria only, or reuse existing empty state string) |
| FAQ render | Ensure button semantics / `aria-expanded` if not already; campaign CSS for larger rows |
| Sticky CTA markup | Render once in campaign footer/main via small PHP partial or JS-injected from existing primary CTA label (prefer PHP so label stays source-of-truth from content) |

**Do not** invent new body copy for eyebrows. Eyebrow may only reuse an existing field (e.g. demo `badge`, or a short existing fragment already on the page). If no suitable field exists, skip eyebrow rather than invent text.

---

## Layout system

Scoped CSS variables on `.jcp-page-campaign`:

- `--jcp-campaign-max`: ~1200px outer; content ~1120px
- Horizontal padding: 24 / 32–48 / 48–64
- Section padding: 40–56 mobile → 56–72 tablet → 72–96 desktop
- Alternating surfaces: white / warm off-white / cool gray / navy conversion bands
- Radii 14–22px; soft borders; restrained shadows; JCP orange + navy + neutrals only

---

## Section specs (visual only)

1. **Header** — ~64–72px brandbar, content-width, subtle border; desktop right CTA = existing primary label → `#apply` (or stored URL). Sticky only if lightweight.
2. **Hero** — Desktop 55/45 two-column, ~720–820px height budget; larger H1 (fewer wraps); CTAs + trust under subhead; right = product-proof frame (layered UI cards via CSS around existing asset — not a new stock photo narrative). Mobile: stack, compact, full-width primary CTA.
3. **Core mechanic** — Unified strip overlapping/attached under hero; 3 equal columns + dividers; stack on mobile.
4. **Problem** — Heading + 3 substantial equal-height cards; closing line in highlighted statement box.
5. **Benefits** — 2×2 larger cards; hierarchy via existing fields only (title/body/keyword/tagline if already in schema — do not invent fields or copy).
6. **How it works** — Full-width 3 step cards + connectors; vertical timeline on mobile; existing CTA below.
7. **Demo preview** — Contained panel; ~45/55 text/media; no edge-to-edge empty canvas.
8. **FAQ** — Max ~780–900px; larger accordion; a11y preserved/improved; sits near apply.
9. **Form / apply** — Contrasting band; white form card 24–36px pad, radius, shadow, min-height; bridge CSS for 48–52px inputs; fix collapsed empty look without changing shortcode.
10. **Final CTA** — Full-width navy (or navy/orange) band; 2-col desktop; dominant button matching existing label.
11. **Mobile sticky CTA** — `<768px`; primary label; safe-area; hide when `#apply` substantially visible.

---

## Out of scope

- Rewriting imported case-study copy or dummy JSON marketing text (structure/flags only if required for layout).
- Changing Fluent Forms form fields/logic.
- Global `niche-landing.css` refactors beyond moving/removing the tiny existing campaign override block into the new file.
- New font families or heavy animation libraries.

---

## Asset gaps (manual follow-up)

- Best hero proof still needs **real product screenshots** (check-in, GBP, site, review) if current page only has a contractor photo — CSS can frame what’s there; replacing media is editorial, not this PR.
- Form must have a valid Fluent shortcode on the page for the card to show a live form (layout shell works either way).

---

## Acceptance (summary)

Paid-traffic campaign page: uses desktop width, less empty vertical space, proof-forward hero, distinct connected sections, apply form as strongest conversion point, all existing words preserved, mobile + desktop clean, a11y/CWV-friendly.
