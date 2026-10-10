# Task 3 Report: Testimonials CSS

**Status:** Complete  
**Date:** 2026-08-21  
**Commit:** (see git log after commit)

## What was done

Added a `/* Testimonials */` section to `css/pages/home.css` (~375 lines) styling all classes from `jcp_niche_render_testimonials()`:

| Element | Classes styled |
|---------|----------------|
| Layout | `.jcp-testimonials`, `.jcp-testimonials-eyebrow` |
| Featured | `.jcp-testimonials-featured`, `-quote`, `-cite`, `-name`, `-role`, `-stars` |
| Slider | `.jcp-testimonials-slider`, `-track`, `-card`, `-card-quote`, `-card-name`, `-card-role` |
| Controls | `.jcp-testimonials-nav`, `-nav--prev`, `-nav--next`, `-dots`, `.jcp-testimonials-dot` |

Scoped under `.jcp-home` and `.jcp-marketing` to match neighboring section patterns.

## Requirements coverage

- **Section spacing:** Uses existing `--jcp-space-*` tokens; block max-width 1100px centered like other content bands.
- **Featured panel:** `--jcp-color-bg-secondary` surface, subtle border + `--jcp-shadow-xs`, coral 4px left accent (`--jcp-color-primary`), generous padding, clamp quote typography.
- **Stars:** Amber via `--jcp-color-warning` (#f59e0b).
- **Slider cards:** Equal min-height (11.5rem desktop / 10.5rem mobile), flex column with quote flex-grow, `-webkit-line-clamp: 4`, ~50% width on desktop for peek, scroll-snap track.
- **Nav arrows:** 44px circular buttons, hover/focus-visible states, disabled opacity.
- **Dots:** Styled for `button`, `.jcp-testimonials-dot`, `.is-active`, and `[aria-selected="true"]` (Task 4 JS placeholders).
- **Desktop:** Featured above strip in column flex; slider grid with flanking arrows.
- **Mobile (≤768px):** Full-width featured; horizontal swipe track at 82–88% card width; arrows hidden; edge-to-edge track bleed with safe-area padding.
- **Reduced motion:** Disables scroll-behavior smooth, transitions, and dot scale transform.

## Visual check

Browser spot-check against `http://jobcapturepro.local/` was attempted but the MCP browser tab could not attach. CSS follows existing homepage tokens and markup; manual verification recommended after Task 5 seeds the block on the homepage.

## Concerns / follow-ups for later tasks

1. **Task 4 JS** must populate `.jcp-testimonials-dots` with `button` elements (or `.jcp-testimonials-dot`) using `.is-active` / `aria-selected` — CSS is ready but dots container is empty until JS runs.
2. **Active card state** styles target `.is-active` and `[aria-current="true"]` — JS should set one of these when syncing featured review.
3. **Mobile track bleed** uses container padding calc; verify no horizontal page scroll on narrow devices once block is live.
4. **Featured swap animation** — only opacity transition on featured panel; Task 4 may want a `.is-swapping` class hook if richer motion is desired (respecting reduced-motion).

## Files changed

- `css/pages/home.css` — testimonials styles added

## Not in scope (per instructions)

- Tasks 4–7 (JS, seed/upgrade, editor)
- Push to remote (explicitly skipped)
