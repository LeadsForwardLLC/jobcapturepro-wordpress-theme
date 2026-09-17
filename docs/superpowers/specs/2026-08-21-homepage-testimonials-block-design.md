# Homepage Testimonials Block Design

**Date:** 2026-08-21  
**Status:** Approved for implementation planning  
**Scope:** New reusable page block `testimonials` + homepage placement after `proof_flow`

---

## Goal

Add human social proof to the marketing homepage without inventing a new visual system. Reuse the existing four customer reviews, present them in a modern SaaS layout (one featured + slider of the rest), and make the featured review editable via block props.

---

## Placement

Insert the block into the **Home** preset order and seed content:

1. `hero`
2. `how_it_works`
3. `demo_preview`
4. `proof_flow`
5. **`testimonials`** ← new
6. `benefits`
7. `who_its_for`
8. `directory_preview`
9. `faq`
10. `conversion`
11. `final_cta`

**Rationale:** After “Real job proof, not marketing claims,” visitors have seen *how* proof works. Quotes convert that into *who trusts it* before benefit cards.

Live homepage content stored in `_jcp_page_content` may need a one-time seed/migration or manual block insert if the published page already diverged from the preset. Preset + `dummy-home.json` / legacy conversion must include the new block so fresh installs and defaults stay correct.

---

## Content source

**Single source of truth for default reviews:** `jcp_sales_tool_default_reviews()` in `inc/sales-tool/config.php`.

Default set (order):

1. Trent Ellison — Home service operator  
2. Brian Hardy — Contractor  
3. Heriberto Eddie Roman — Business owner  
4. **Peter Bonk — Marketing agency** ← default featured

Block props may store an editable `reviews` array seeded from that helper so editors can tweak copy without editing PHP. Changing the PHP defaults should still be the canonical product copy when reseeding.

Do **not** invent additional fake testimonials.

---

## Layout

### Section chrome

- Wrapper: standard `.jcp-section` + `.jcp-container` (same pattern as `proof_flow` / `benefits`).
- Optional eyebrow: “Customer stories”
- Headline: “Trusted by contractors who already take the photos”
- Short subhead explaining real operators/agencies using JobCapturePro

### Featured panel

- Large featured quote for `featured_key` (default: Peter Bonk)
- Five-star row (when `show_stars`)
- Quote, name, role (when `show_roles`)
- Soft surface using existing tokens (light fill, subtle border, coral/primary accent — no heavy multi-shadow “AI card” look)
- Visually dominant vs slider cards

### Secondary slider

- Remaining reviews (exclude featured) as compact peek-cards
- Horizontal slider: prev/next controls + dots
- Optional autoplay (~6000ms), pause on hover/focus
- Touch/swipe friendly on mobile
- Stacked/peek layout on small screens; equal-height compact cards

### Optional interaction

- Activating a slider card promotes that review to featured and moves the previous featured into the strip (nice-to-have; include if low-cost during implementation)

---

## Block props

| Prop | Type | Default | Notes |
|------|------|---------|--------|
| `eyebrow` | string | `Customer stories` | |
| `headline` | string | `Trusted by contractors who already take the photos` | |
| `subheadline` | string | short trust line | |
| `reviews` | array | seeded from `jcp_sales_tool_default_reviews()` | `{ name, role, quote, rating?, id? }` |
| `featured_key` | string | `Peter Bonk` (or stable `id`) | Which review is featured |
| `autoplay` | bool | `true` | |
| `autoplay_ms` | int | `6000` | |
| `show_stars` | bool | `true` | |
| `show_roles` | bool | `true` | |

Stable `id` per review (slug of name) preferred for `featured_key` so rename-safe editing is easier.

Editor UX (niche/page block editor):

- Text fields for eyebrow/headline/subhead
- Control to choose featured review (select of review names/ids)
- Toggles for stars, roles, autoplay
- Reviews list editable if the editor already supports repeatable items for similar blocks; otherwise seed-only + featured select is enough for v1

---

## Visual system

- Reuse homepage / marketing tokens from `css/base.css` and section patterns in `css/sections.css`
- Home-specific refinements in `css/pages/home.css` under `.jcp-home …`
- Match existing section header component (`jcp_niche_render_section_header` if applicable)
- Motion: subtle fade/slide for featured swap and slider; respect `prefers-reduced-motion`

---

## Implementation surfaces

| Area | Work |
|------|------|
| `inc/page-blocks/registry.php` | Register `testimonials` type + labels + `page_kinds` including `home` |
| `jcp_page_default_block_props()` | Default props |
| `inc/page-blocks/presets.php` | Insert into `home` `block_types` after `proof_flow` |
| Seed / legacy (`dummy-home.json` and/or converters) | Add block content for defaults |
| `inc/page-blocks/render.php` | `case 'testimonials'` |
| `inc/niche-landing/render.php` (or dedicated partial) | Markup |
| `css/pages/home.css` (+ shared if needed) | Styles |
| `assets/js/pages/home.js` or small `testimonials.js` | Slider + featured swap |
| Enqueue | Only when block present / on home |
| Page editor JS | Featured select + field wiring if required |

---

## Out of scope

- Logo wall / customer logo strip
- Pulling live reviews from Google/Trustpilot APIs
- Redesigning `proof_flow` or benefits
- Case-study campaign page changes
- Changing sales-tool proof chapter UI (only reuse its default review data)

---

## Success criteria

- Homepage shows testimonials after proof flow with Peter Bonk featured by default
- Other three reviews appear in an elegant slider
- Featured review is changeable via block props without code deploy
- Design matches existing homepage language (not a disconnected “new brand”)
- Mobile: readable, swipeable, no overflow/clipped controls
- No duplicate hard-coded review lists diverging from sales-tool defaults at seed time

---

## Decisions locked

- Placement: **A** (after `proof_flow`)
- Featured default: **Peter Bonk**
- Approach: **Featured + carousel strip**
