# Task 6 Report: Niche page editor controls for testimonials

## Status: Complete

**Commit:** (see below) — Expose testimonials featured select and toggles in the page editor.

## Changes

| File | Change |
|------|--------|
| `assets/js/pages/niche-page-editor.js` | Testimonials block editor wiring |

### Selector map

```js
testimonials: '.jcp-block-testimonials, #testimonials',
```

Matches `jcp_niche_render_testimonials()` section class and default `section_id`.

### Visibility toggles (Show tab)

Mirrors `faq` / `proof_flow`:

- `show_eyebrow` → `.jcp-testimonials-eyebrow`
- `show_headline` → section headline selector
- `show_subheadline` → `.rankings-subtitle`

Eyebrow restore helper added for re-showing hidden eyebrow without reload.

### Content tab

- **Featured review** — `<select>` bound to `featured_key`, options from `reviews[].id` (fallback name slug)
- **Star ratings** — On/Off → `show_stars`
- **Reviewer roles** — On/Off → `show_roles`
- **Autoplay slider** — On/Off → `autoplay`

Uses existing `jcp-layout-btns` / `jcp-structure-text-input` patterns (same as form embed display toggle).

### Inline text

No extra wiring needed — PHP already emits `data-jcp-path` for:

- `testimonials.eyebrow`
- `testimonials.headline` / `testimonials.subheadline` (via section header)

### Live DOM sync

`syncTestimonialsToDom()` updates featured panel, secondary track, `data-featured-key`, `data-autoplay`, and star/role visibility when content controls change.

## Verification

- `node --check assets/js/pages/niche-page-editor.js` — pass
- Manual homepage editor test (featured → Trent → Save) — **not run in this session** (requires logged-in browser on local site)

## Concerns

1. **Slider JS after featured change** — Rebuilding `[data-jcp-testimonials-track]` in the editor does not re-bind `testimonials.js` listeners; prev/next/dots/autoplay may be stale until hard refresh. Save + reload is the reliable path (matches Task 6 manual test intent).
2. **Empty reviews** — Featured select is disabled when `reviews[]` is empty; render PHP also bails if no featured review resolves.
3. **Not pushed** — Per task instructions, commit only; push deferred to Task 7.
