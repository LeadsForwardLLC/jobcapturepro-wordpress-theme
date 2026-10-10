# Task 2 Report: Testimonials block render markup

## Status

**DONE**

## Commit hash(es)

- `66cb4f1` — Render testimonials block with featured quote and secondary strip.

## Files changed

| File | Change |
|------|--------|
| `inc/niche-landing/render.php` | Added `jcp_niche_render_testimonials()` plus helpers for review normalization, featured resolution, star row, and JSON review store |
| `inc/page-blocks/render.php` | Wired `case 'testimonials'` dispatch in `jcp_page_render_block()` |

## Markup delivered

- Section: `.jcp-section.rankings-section.jcp-block-testimonials` with `data-jcp-testimonials`, `data-autoplay`, `data-autoplay-ms`, `data-featured-key`
- Optional eyebrow via `show_eyebrow` / `eyebrow` props (uses `demo-badge` class until Task 3 CSS)
- Section header via `jcp_niche_render_section_header( $props, 'testimonials' )`
- Featured panel: `figure.jcp-testimonials-featured[data-jcp-testimonials-featured]` with stars, blockquote, cite name/role
- Secondary strip: prev/next nav, track with `button.jcp-testimonials-card[data-review-key]`, dots container
- Review store: `<script type="application/json" data-jcp-testimonials-store>` with normalized review array for Task 4 JS

## Test / verification commands + results

### PHP syntax check

```bash
php -l inc/niche-landing/render.php
php -l inc/page-blocks/render.php
```

**Result:** No syntax errors detected in either file.

### WP-CLI smoke check

```bash
wp eval '$props = jcp_page_default_block_props("testimonials"); ob_start(); jcp_niche_render_testimonials($props); ...'
```

**Result:** WP-CLI not available in this environment. Browser/visual check deferred until Task 5 places block on homepage preset.

### Expected render (default props)

- Featured: Peter Bonk (`data-featured-key="peter-bonk"`)
- Secondary cards: 3 (Trent Ellison, Brian Hardy, Heriberto Eddie Roman)
- JSON store: 4 normalized reviews with `id`, `name`, `role`, `quote`, `rating`

## Concerns / follow-ups

- **No CSS/JS yet** — block is unstyled and non-interactive until Tasks 3–4.
- **Homepage not seeded** — block won't appear on live home until Task 5 preset/upgrade.
- **Eyebrow styling** — reuses `demo-badge`; Task 3 may want a dedicated `.jcp-testimonials-eyebrow` rule under home.css.
- **Visual check pending** — confirm featured + 3 cards in browser once block is on a page.
