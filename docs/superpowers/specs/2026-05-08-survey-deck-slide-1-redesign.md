## Goal
Redesign **Survey pre-demo deck – Slide 1** to feel modern and on-brand: more color, visual hierarchy, iconography, and subtle motion while staying fast and readable.

Slide 1 currently lives in `templates/survey/deck.php` and is styled by `assets/shared/assets/survey.css` (“PRE-DEMO SLIDE DECK” section).

## Non-goals (for this iteration)
- No redesign of slides 2–8 yet.
- No changes to backend tracking / analytics behavior.
- No heavy animation or continuously running effects.

## Target UX
- **Immediate clarity**: headline + 1-sentence framing.
- **A visual “compounding” flow** that makes the three steps feel connected (not three generic boxes).
- **On-brand accent**: warm gradient / coral accent consistent with existing JCP palette.
- **Subtle movement**: one-time entrance + a gentle “progress sweep” along the connector when the slide becomes active.

## Layout (Option A – “Hero + Value Flow”)
Use a responsive two-column layout inside Slide 1:

- **Left (copy)**
  - Headline (existing, dynamic niche substitution preserved)
  - Lead paragraph (existing)
  - Optional small “signal pill” below lead: e.g. “From 1 job → more calls”

- **Right (visual flow)**
  - A vertical stack of **3 step cards** with:
    - Lucide icon in a small tinted badge
    - Step title (existing bullet text)
    - Small supporting line (new, 1 short sentence each)
  - A connecting line behind/along the cards (gradient) with a one-time animated highlight sweep when slide becomes active.

## Visual design tokens (guidance)
- Accent: keep existing coral (`#FF503E`) as primary accent; allow warm secondary (`#FF8A3D`) as gradient endpoint.
- Background: add a subtle radial/linear wash behind the right-side flow (very low opacity).
- Depth: soft shadow and slightly stronger border treatment on active slide elements.
- Typography: headline stays bold; step card titles should read at-a-glance; supporting lines lighter.

## Motion rules
- Entrance: retain existing `deckIn` fade/translate.
- New motion: “connector sweep” runs once on activation (`.deck-slide.is-active`), duration ~900–1200ms, no infinite loops.
- Respect reduced motion: disable sweep and any transforms under `@media (prefers-reduced-motion: reduce)`.

## Accessibility
- Decorative icons are `aria-hidden="true"`.
- Maintain sufficient contrast for text; avoid relying on color-only meaning.
- Ensure focus styles remain visible for Next/Back/Skip controls (no regressions).

## Implementation scope (files)
- `templates/survey/deck.php`: restructure Slide 1 markup only (wrap into columns; add icon+supporting text; add optional pill).
- `assets/shared/assets/survey.css`: add Slide 1-specific styles (`.deck-slide--intro` etc.) and connector sweep animation.
- `assets/js/pages/survey.js`: no changes expected for Slide 1 logic; only ensure existing niche title swap still targets the right element id.

## Acceptance criteria
- Slide 1 clearly looks “designed” (not a plain list): icons + connected flow + accent wash.
- Slide 1 still looks good at common widths (mobile → desktop).
- No CLS spikes (avoid late-loading images; use pure CSS + SVG icons already in theme).
- Reduced-motion users do not see sweeping animations.

