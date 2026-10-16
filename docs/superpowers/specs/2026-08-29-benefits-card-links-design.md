# Benefits card links

**Date:** 2026-08-29  
**Status:** Approved — implementing  
**Scope:** Benefits grid cards (`.ranking-factor-card`), not job-flow steps

## Goal
Each benefit card can have its own optional URL. Clicking anywhere on a linked card navigates. Editors set the URL via the existing **Link** toolbar + internal suggestions picker (same as text hyperlinks).

## Design
1. Data: `benefits.items[n].url` (string; empty = not linked).
2. Markup: card stays a `div`. When `url` is set, add a stretched hit `<a class="ranking-factor-card__link">` so the whole card is clickable without wrapping editable fields in an anchor.
3. Editor: focus/select a benefit card → **Link** opens the internal-suggestion modal; apply/clear writes `url`. Clicks do not navigate while editing.
4. CSS: subtle linked hover; hit link covers the card (`position: absolute; inset: 0`), content stays above via `z-index`.

## Out of scope
- Campaign job-flow benefit steps
- One shared URL for all cards
- New sidebar-only link UI
