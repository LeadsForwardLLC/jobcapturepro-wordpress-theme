# Campaign Landing Conversion Layout — Implementation Plan

**Date:** 2026-07-27  
**Spec:** `docs/superpowers/specs/2026-07-27-campaign-landing-conversion-layout-design.md`  
**Approach:** CSS-first + small PHP hooks

## Files

| File | Responsibility |
|------|----------------|
| `css/pages/campaign-landing.css` | All campaign visual redesign (scoped) |
| `assets/js/pages/campaign-landing.js` | Smooth `#apply` scroll, mobile sticky hide-on-form |
| `inc/page-blocks/campaign-preset.php` | Split hero + show_visual; CTA helper |
| `templates/global/header.php` | Brandbar CTA from existing primary label |
| `templates/global/footer.php` | Mobile sticky CTA markup |
| `inc/fluent-forms-bridge.php` | Form card shell / min-height |
| `inc/enqueue.php` | Conditionally enqueue CSS/JS |
| `inc/niche-landing/dummy-campaign.json` | `show_visual: true` only |
| `css/pages/niche-landing.css` | Remove thin campaign override block |

## Tasks

1. Hero finalize → split + visual; CSS two-column + proof frame
2. Page system tokens, alternating sections, cards, HIW, demo, FAQ, form, final CTA
3. Brandbar + sticky CTA + JS
4. Enqueue + verify PHP lint; commit/push
