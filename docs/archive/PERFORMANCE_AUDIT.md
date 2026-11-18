# JobCapturePro Performance Audit & Recommendations

**PageSpeed Insights (mobile):** 57 — Main issues: CLS 0.821, LCP 4.3s, ~500 KB unused JS, images without dimensions, render-blocking CSS/fonts.

---

## 1. JavaScript audit & scoping (DONE + remaining)

### Implemented
- **Render script only on app pages.** `jcp-render.js` is no longer enqueued on blog, single post, or generic pages. It loads only when: home, pricing, early-access, contact, demo, directory, company, or estimate.
- **Async template loading.** Replaced synchronous `XMLHttpRequest` in `jcp-render.js` with `fetch()`. Template fetch no longer blocks the main thread; layout is reserved with `#jcp-app { min-height: 50vh }` during load to reduce CLS.

### Bundle summary
| Script | Loaded on | Purpose |
|--------|-----------|---------|
| `jcp-core-nav.js` | All pages | Global nav (keep) |
| `jcp-render.js` | Home, pricing, early-access, contact, demo, directory, company, estimate | Dispatches to page render or fetches HTML template |
| `js/pages/home.js` | Home only | Homepage hero, sections, CTAs |
| `js/pages/pricing.js` | Pricing only | Pricing table, FAQ |
| `js/features/demo/jcp-demo.js` | Demo only (?mode=run) | Heavy demo UI, Leaflet |
| `js/features/directory/directory.js` | Directory only | Listing grid, filters |
| `js/features/directory/profile.js` | Company profile only | Profile hydration |
| `js/features/directory/directory-integration.js` | Company only | Profile + directory integration |
| `js/features/estimate/*.js` | Estimate only | Estimate builder |

### Recommended next steps
- **Delay non-critical JS:** Use WP Rocket “Delay JavaScript execution” for analytics, chat, or tracking scripts only — **not** for `jcp-core-nav`, `jcp-render`, or page-specific bundles (they are required for above-the-fold content).
- **Audit plugins:** Identify which plugins add the ~500 KB unused JS (e.g. Rank Math, forms, page builders). Exclude their scripts from critical path or load them conditionally.

---

## 2. CLS fixes (DONE + remaining)

### Implemented
- **Image dimensions.** Added explicit `width` and `height` (and `alt` where missing) to:
  - Nav logo, mobile logo, footer logos (`templates/partials/nav.php`, `templates/global/footer.php`)
  - Directory hero images (`assets/directory/index.html`) — main hero also has `fetchpriority="high"`
  - Home hero phone image (`assets/js/pages/home.js`) — `fetchpriority="high"`
  - Profile gallery and work-card images (`assets/directory/profile.html`)
- **Reserved space during template load.** In `jcp-render.js`, `#jcp-app` gets `min-height: 50vh` before `fetch()` and is cleared after inject so layout doesn’t collapse then jump.

### Recommended next steps
- **Sliders / modals:** Ensure any slider or modal container has a fixed height or `aspect-ratio` in CSS so space is reserved before content loads.
- **Fonts:** Use `font-display: swap` (see §4) so text doesn’t cause a layout shift when fonts load.
- **Above-the-fold injection:** Avoid injecting large blocks of content above the fold after first paint; keep hero content in initial HTML or inject with reserved space (as with `#jcp-app`).

---

## 3. Image optimization (DONE + WP Rocket)

### Implemented
- **LCP preload.** Home and directory pages get a `<link rel="preload" as="image">` for the LCP image (hero phone image and directory main hero).
- **Dimensions and priority.** Hero/LCP images have `width`, `height`, and (where appropriate) `fetchpriority="high"` so they are not lazy-loaded by the browser and layout is stable.

### Recommended (WP Rocket / server)
- **WebP/AVIF:** Serve images in modern formats (e.g. ShortPixel, Imagify, or server rules). Ensure LCP image is available as WebP/AVIF and referenced in HTML or via `picture`/`source`.
- **Lazy-load:** Enable for images **below the fold** only. Exclude:
  - Home: hero phone image (`jcp-user-photo.jpg`)
  - Directory: main hero (`confident-foreman.jpg`)
  - Any image with `fetchpriority="high"`
- **Sizing:** Serve appropriately sized sources for mobile (e.g. 1x/2x, or responsive `srcset`).

---

## 4. Fonts

### Current
- **Theme:** `css/base.css` uses system font stack only; no custom `@font-face` or Google Fonts in theme.
- **Estimate:** Font Awesome in `assets/estimate/fontawesome/css/all.min.css` uses `font-display: block`, which can block text and hurt LCP.

### Recommended
- **Any new fonts:** Use `font-display: swap` in `@font-face` or when loading Google Fonts.
- **Preload critical fonts:** If you add a critical font (e.g. heading font), preload it:  
  `<link rel="preload" href="…" as="font" type="font/woff2" crossorigin>`
- **Non-blocking font CSS:** Load non-critical font stylesheets with `media="print" onload="this.media='all'"` or defer them so they don’t block first paint.
- **Font Awesome (estimate):** If that page is critical, consider a small subset of icons or swapping to `font-display: swap` (e.g. local override or plugin).

---

## 5. WP Rocket configuration checklist

### Safe to enable now
| Option | Recommendation |
|--------|----------------|
| **Minify CSS** | ON — test each main template (home, directory, company, blog); roll back if layout breaks. |
| **Minify JavaScript** | ON — theme scripts are already deferred; minification is safe. |
| **Defer JavaScript** | ON — theme already adds `defer` to its scripts; WP Rocket’s “Defer” can reinforce. Do **not** defer in a way that breaks `jcp-render` or page-specific entry points. |
| **LazyLoad images** | ON — with **exclusions** (see below). |
| **Add missing image dimensions** | ON — theme now provides dimensions; WP Rocket can fill any remaining. |

### Use with care
| Option | Recommendation |
|--------|----------------|
| **Remove unused CSS** | Test per template. Dynamic classes and conditional layout (e.g. directory, profile, demo) can break if critical CSS is removed. Enable on a staging copy and test home, directory, company, demo, estimate, blog. |
| **Delay JavaScript execution** | Use only for **non-critical** scripts (analytics, chat, tracking). Do **not** delay: `jcp-core-nav`, `jcp-render`, `jcp-core-home`, `jcp-core-directory`, `jcp-core-profile`, or estimate/demo bundles. Add those to “No delay” / exclude list. |

### LazyLoad exclusions (images)
Exclude from LazyLoad so they can be LCP and not delayed:
- `jcp-user-photo.jpg` (home hero)
- `confident-foreman.jpg` (directory hero)
- Or use a class such as `skip-lazy` on LCP images and add that class to WP Rocket’s exclusion list if supported.

### Do not enable (or only with heavy testing)
- **Aggressive “Delay JS”** on theme or directory/demo/estimate scripts — will break hydration and template injection.
- **Remove unused CSS** site-wide without testing each template.

---

## 6. Accessibility quick wins

- **Buttons:** Ensure every `<button>` and icon-only control has an accessible name (`aria-label` or visible text). Theme already uses e.g. `aria-label="Toggle menu"`, `aria-label="Clear search"`; audit remaining buttons (e.g. gallery prev/next, close, dropdown triggers).
- **Heading hierarchy:** Use a single `<h1>` per page and logical order (h1 → h2 → h3). Check directory, profile, and demo for skipped levels or multiple h1s.
- **Contrast:** Fix any contrast issues flagged by Lighthouse or WAVE (e.g. grey text on light background, badge colors).

---

## 7. Security & best practices

### Fixed
- **Insecure request:** Replaced `http://jobcapturepro.com` with `https://jobcapturepro.com` in:
  - `assets/directory/index.html` (hero images)
  - `assets/js/pages/home.js` (hero phone image)  
  So all theme references are HTTPS (no mixed content).

### Recommended
- **Legacy JS:** Remove or replace any unused or deprecated scripts (e.g. old jQuery, duplicate polyfills) to reduce payload and security surface.
- **Third-party scripts:** Prefer loading analytics/chat with `defer` or “Delay JS” and ensure they don’t block main-thread or LCP.

---

## Scope: applied across the entire theme

The audit and fixes were applied theme-wide:

- **PHP templates:** nav, footer, survey wrapper/deck, single post (featured image).
- **HTML templates:** directory index (hero + icons), directory profile (gallery + work cards), estimate index (snapshots + similar jobs + recap photo).
- **JS-rendered content:** home (conversion image, hero phone), directory cards (company logo), directory profile (checkin images), demo (checkin cards, feed images, photo items), estimate builder (uploaded/existing/intake photos).
- **Enqueue:** render only on app pages; LCP preload for home and directory.
- **Render:** async fetch + reserved layout; no sync XHR.

## Files modified (exact paths)

- `inc/enqueue.php` — Conditional render only on app pages; LCP preload for home/directory.
- `assets/js/core/jcp-render.js` — Async `fetch()` instead of sync XHR; reserve `#jcp-app` min-height during load.
- `assets/directory/index.html` — HTTPS hero URLs; width/height and `fetchpriority="high"` on main hero; width/height on stack images and list/grid icons.
- `assets/js/pages/home.js` — HTTPS hero phone image; width, height, `fetchpriority="high"`; conversion image width/height and `loading="lazy"`.
- `assets/js/features/directory/directory.js` — Company logo img width/height (40×40).
- `assets/js/features/directory/profile.js` — Checkin card img width/height and loading="lazy".
- `assets/js/features/demo/jcp-demo.js` — Checkin card, home-checkin-thumb, photo-item, feed-image, and checkin photo imgs: width/height and loading="lazy" where appropriate.
- `assets/js/features/estimate/estimate-builder.js` — Uploaded photo, existing-photo-img, intake-photo-img: width/height and loading="lazy".
- `templates/partials/nav.php` — Width/height on logo and mobile logo.
- `templates/global/footer.php` — Width/height on both footer logos.
- `templates/survey/wrapper.php` — Width/height on survey logo.
- `templates/survey/deck.php` — Width/height on deck tile icons.
- `single.php` — Width/height and loading/fetchpriority on default featured image; loading/fetchpriority on post thumbnail.
- `assets/directory/profile.html` — Width/height and `alt` on all gallery and work-card images.
- `assets/estimate/index.html` — Width/height on snapshot thumbs, similar-job images, and recap photo; loading="lazy" on similar-job images.

---

## Testing notes

1. **After enabling WP Rocket options:** Clear cache and test on mobile (or Lighthouse mobile): home, /directory, /directory/[slug], /demo, /estimate, /pricing, /blog.
2. **CLS:** Re-run PageSpeed Insights; CLS should improve from ~0.82 due to dimensions and reserved space. If CLS remains high, find the shifting element (e.g. font swap, late-injected ad) and reserve space or defer it.
3. **LCP:** Confirm LCP element is the hero image (or intended element); preload and `fetchpriority="high"` should help. Measure with Chrome DevTools “LCP” in Performance.
4. **JS:** Confirm directory and company pages still load and display listings and profile; confirm demo and estimate still work after any “Delay JS” or “Remove unused CSS” changes.
