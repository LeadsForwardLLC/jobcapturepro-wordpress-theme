# Mobile Demo Shell — Design Spec

**Date:** 2026-05-20  
**Status:** Implemented

## Goal

Deliver a polished mobile demo on `/demo` that feels like a native app walkthrough, while guaranteeing that **any change to the phone UI on `/prototype` automatically applies to the mobile demo** — one source, no triple maintenance.

## Core guarantee (how it works today + what we fix)

### Already shared (one source today)

| Asset | Path | Used by |
|-------|------|---------|
| Phone app HTML | `assets/demo/index.html` | `/prototype`, `/demo?mode=run` |
| Phone app logic | `assets/js/features/demo/jcp-demo.js` | both |
| Phone app styles | `assets/shared/assets/demo.css` | both |
| Loader | `assets/js/core/jcp-render.js` | both — explicitly fetches `demo/index.html` for `data-jcp-page="prototype"` and `data-jcp-page="demo"` |

**If you edit screens, buttons, flows, or styles in those three files, both `/prototype` and the phone on `/demo` update together.** There is no separate mobile HTML fork.

### Not shared today (the gap)

`page-prototype.php` contains ~40 lines of **inline CSS** that hide the right panel, tour chrome, stepper, post-demo UI, etc. The mobile demo on `/demo` does **not** use those rules — it relies on `is-mobile-mode` in JS, but almost no matching CSS exists.

That is the only reason prototype and mobile demo can drift visually.

### Fix

Move prototype hide/layout rules into `assets/shared/assets/demo.css` under a single class:

```css
body.jcp-phone-shell { … }
```

Apply it from:
- `/prototype` → `body.jcp-prototype-page.jcp-phone-shell`
- Mobile `/demo?mode=run` → `body.is-mobile-mode.jcp-phone-shell` (set by `applyMobileMode()`)

Remove duplicate hide rules from `page-prototype.php` (keep only prototype-page layout + component reference panel styles).

**After this change: editing `demo.css` or `index.html` updates prototype and mobile demo. Editing `page-prototype.php` only affects the component reference panel wrapper, not the phone.**

## UX decisions (delegated — cleanest option)

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Outcome steps (website, reviews, directory) | Full-screen cards with real mini-previews | Richer, app-like; reuses existing right-panel content moved into overlay cards |
| WP header on mobile demo run | Minimal immersive — hide marketing nav, keep slim bar with logo + step progress + Reset | Feels like an app without trapping the user |
| Separate `/demo/mobile` route | No — auto-detect on same URL | Fewer routes; survey URL params still work |
| Desktop handoff on survey | Keep copy/share as optional; no longer block mobile launch | Mobile demo is now a real experience |
| Breakpoint | **768px** everywhere (survey, nav, `applyMobileMode`) | One canonical width |

## Architecture

```
/prototype                          /demo?mode=run (mobile)
     │                                      │
     └──────────► jcp-render.js ◄────────────┘
                      │
              assets/demo/index.html   ← single phone markup
                      │
              jcp-demo.js              ← single app logic
                      │
              demo.css                 ← single phone styles
                      │
         ┌────────────┴────────────┐
         │   body.jcp-phone-shell   │  ← shared phone-only chrome
         └────────────┬────────────┘
                      │
      ┌───────────────┼───────────────┐
      │               │               │
  prototype      mobile demo      desktop demo
  (no tour)   (guided + cards)  (phone + right panel)
```

## Mobile demo flow

1. User completes survey → `/demo/?mode=run&name=…&business=…&niche=…`
2. `applyMobileMode()` → `is-mobile-mode` + `jcp-phone-shell` on `body`
3. Phone fills viewport (same framing as `/prototype`)
4. Guided tour runs via bottom `#mobile-stepper` (already wired to `advanceDemo()`)
5. Steps 1–3: phone-only (login → home → new check-in)
6. Steps 4–6: full-screen **outcome overlays** (not side panel):
   - Step 4: website publish preview card
   - Step 5: review request / Google preview card
   - Step 6: directory profile card → post-demo CTA
7. Tour bubble stays anchored to phone elements; desktop tour docks hidden

## What does NOT change

- `assets/estimate/` — separate product surface, own mobile stepper
- Survey templates/JS — already responsive; minor copy tweak optional
- Desktop demo layout — unchanged when viewport > 768px
- Prototype behavior — still free exploration, no guided tour (`JCP_IS_PROTOTYPE`)

## Files to touch (implementation)

| File | Change |
|------|--------|
| `assets/shared/assets/demo.css` | Add `jcp-phone-shell`, `is-mobile-mode`, outcome overlay styles |
| `assets/js/features/demo/jcp-demo.js` | Unify breakpoint 768px; add `jcp-phone-shell` class; outcome overlay logic for mobile steps 4–6 |
| `page-prototype.php` | Remove hide rules moved to `demo.css` |
| `page-demo.php` or `css/pages/demo.css` | Mobile run mode: slim header / hide footer |
| `assets/demo/index.html` | Add outcome overlay containers (if not reusing right-panel nodes) |
| `assets/js/pages/survey.js` | Optional: soften desktop-only copy |

## Success criteria

1. Edit a button label in `index.html` → visible on `/prototype` and mobile `/demo` without extra steps
2. Edit phone styles in `demo.css` → same
3. Mobile demo completes full 6-step guided flow without horizontal scroll or desktop right panel
4. Desktop demo unchanged
5. `/prototype` looks identical to before (component panel aside)

## Out of scope

- FlutterFlow / native app build
- GHL email automation for desktop link
- Merging estimate builder mobile patterns
