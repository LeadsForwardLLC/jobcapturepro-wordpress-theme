# JobCapturePro Core Theme - Master Documentation

**Last Updated:** June 17, 2026  
**Version:** 2.0  
**For:** Developers taking over the theme (see [Developer Handoff](#developer-handoff) first)

---

## 📋 TABLE OF CONTENTS

1. [Developer Handoff](#developer-handoff)
2. [Project Overview](#project-overview)
3. [JCP Page Block System](#jcp-page-block-system)
4. [Live Page Editor](#live-page-editor)
5. [Architecture & Structure](#architecture--structure)
6. [File Organization](#file-organization)
7. [Asset Management](#asset-management)
8. [Template System](#template-system)
9. [Development Guidelines](#development-guidelines)
10. [Current Status](#current-status)
11. [Quick Reference](#quick-reference)
12. [Setup & Integrations](#setup--integrations) — includes [JCP Companies CPT & API key (wp-config)](#jcp-companies-cpt--api-sync)
13. [Forms & GoHighLevel](#forms--gohighlevel)

---

## 🚀 DEVELOPER HANDOFF

### Start here

| If you need to… | Read / open |
|-----------------|-------------|
| Understand structured marketing pages | [JCP Page Block System](#jcp-page-block-system) |
| Work on click-to-edit, undo, add/remove cards | [Live Page Editor](#live-page-editor) |
| Change section HTML or new block types | `inc/niche-landing/render.php`, `inc/page-blocks/registry.php` |
| Editor save/load API | `inc/page-blocks/rest-content.php` |
| Operational guide for editors | WP Admin → **JCP** → **Page System** (`inc/admin-theme-docs.php`) |
| Block catalog for editors | WP Admin → **JCP** → **Block Library** (`inc/admin-block-library.php`) |
| Architecture migration notes | `docs/superpowers/specs/2026-06-15-jcp-block-page-system-design.md` |

### Two systems (intentional, mid-migration)

The theme is **mid-migration** from legacy niche landing to a unified block page system. **Both** `inc/page-blocks/` and `inc/niche-landing/` are required at runtime:

- **`inc/page-blocks/`** — Block registry, content schema, `jcp_page_render()`, REST API, `jcp_page` CPT, migrations.
- **`inc/niche-landing/`** — Section PHP renderers (~1,280 lines), doc import parser, `jcp_niche_landing` CPT, admin meta boxes, editable attributes, shared partials.

Do **not** delete `inc/niche-landing/` until section renderers are fully moved into page-blocks.

### CPTs for structured content

| CPT | Archive URL | Template | Use when |
|-----|-------------|----------|----------|
| `jcp_niche_landing` | `/industries/` | `single-jcp_niche_landing.php` | Industry/niche landing pages |
| `jcp_page` | `/pages/` | `single-jcp_page.php`, `page-jcp-blocks.php` | Generic block pages |
| WP `page` + template | — | `page-home.php`, `page-referral-program.php` | Homepage, referral program |

Content is stored in post meta `_jcp_page_content` (canonical). Legacy `_jcp_niche_content` is adapted via `jcp_page_legacy_to_blocks()`.

### Deploy

Pushing to `main` triggers GitHub Actions (`.github/workflows/deploy-main.yml`) → SiteGround production.

### Known intentional shims (do not delete)

- `css/buttons.css` — Empty enqueue shim; preserves CSS load order.
- `assets/js/pages/home.js` — Legacy JS homepage fallback when front page has no block content meta.
- `inc/page-blocks/doc-parser.php` — Thin wrapper; real parser is `inc/niche-landing/doc-parser.php`.

### Repo hygiene

- `.DS_Store` is gitignored; do not commit macOS metadata.
- `docs/superpowers/` — Internal implementation plans; safe to keep for context.
- `assets/shared/assets/icons/lucide/` — **Only** icon set used by the theme (~1,667 SVGs). Root-level icon JSON files under `assets/shared/assets/icons/` are legacy/unused bloat; candidate for removal after staging verification.

---

## 🎯 PROJECT OVERVIEW

### What is This Theme?
WordPress theme for JobCapturePro public website, directory, and estimator. The theme uses **client-side rendering** for most pages - WordPress acts as the host/CMS, while JavaScript handles UI rendering.

### Key Characteristics
- **Hybrid Architecture**: WordPress PHP templates + JavaScript rendering + PHP block-rendered marketing pages
- **Block page system**: Structured JSON content + PHP section renderers + live front-end editor
- **Modular CSS**: Design tokens (in `base.css`) → base → layout → components → sections → utilities → pages
- **Organized JavaScript**: Core → Features → Pages structure under `assets/js/`
- **Documented shims**: Legacy fallbacks are intentional; see [Developer Handoff](#developer-handoff)

---

## 🧱 JCP PAGE BLOCK SYSTEM

### Data model

Each structured page stores a JSON document in post meta `_jcp_page_content`:

```json
{
  "blocks": [
    { "id": "b1", "type": "hero", "props": {} },
    { "id": "b2", "type": "how_it_works", "props": {} }
  ],
  "content": {
    "hero": { "headline": "...", "subheadline": "..." },
    "how_it_works": { "headline": "...", "steps": [ { "title": "...", "lines": ["..."] } ] }
  }
}
```

- **`blocks`** — Ordered section stack (type, id, optional per-page label, layout props).
- **`content`** — Flat keyed section payloads consumed by PHP renderers.

Helpers live in `inc/page-blocks/schema.php` (`jcp_page_get_content`, `jcp_page_get_content_flat`, adapters). Block types are defined in `inc/page-blocks/registry.php`.

### Render pipeline

```
Template (page-home.php, single-jcp_page.php, …)
  → jcp_niche_render_page() or jcp_page_render()
    → inc/page-blocks/render.php (orchestration, block loop)
      → inc/niche-landing/render.php (section HTML: hero, benefits, FAQ, …)
        → partials.php / components.php / editable.php (shared markup + editor attrs)
```

Homepage default path: `page-home.php` calls `jcp_niche_render_page()` which delegates to `jcp_page_render()`. Legacy `home.js` string-template homepage is used **only** when the front page has no `_jcp_page_content` meta (pre-migration).

### Key files

| File | Role |
|------|------|
| `inc/page-blocks/registry.php` | Block type catalog |
| `inc/page-blocks/schema.php` | Content storage, presets, flat content |
| `inc/page-blocks/render.php` | `jcp_page_render()` orchestration |
| `inc/page-blocks/rest-content.php` | `GET/POST /wp-json/jcp/v1/page/{id}` |
| `inc/page-blocks/migrate-pages.php` | One-time homepage / referral migrations |
| `inc/page-blocks/presets.php` | Default block stacks per page kind |
| `inc/niche-landing/render.php` | Section HTML for each block type |
| `inc/niche-landing/editable.php` | `data-jcp-path`, `data-jcp-array`, collection attrs |
| `inc/niche-landing/dummy-*.json` | Preset content samples |

### Document import

Google Doc → paste in admin meta box → `jcp_niche_parse_document()` (`inc/niche-landing/doc-parser.php`) → JSON content. Wrapper alias: `jcp_page_parse_document()` in `inc/page-blocks/doc-parser.php`.

---

## ✏️ LIVE PAGE EDITOR

Logged-in users with `edit_post` capability see a front-end toolbar on structured pages (`jcp_core_enqueue_page_block_editor()` in `inc/helpers.php`).

### Scripts (load order)

1. `assets/js/pages/page-media-editor.js` — Image/video slots via `wp.media`
2. `assets/js/pages/niche-page-editor.js` — Toolbar, undo/redo, structure panel, inline text, save
3. `assets/js/pages/page-collection-editor.js` — Add/remove cards, FAQ items, steps, optional CTAs

Global bootstrap: `window.JCP_NICHE_EDITOR` (REST URL, nonce, blocks, flat content, registry). Editor API: `window.__JCP_EDITOR_API__`.

### Editable regions

- **Text** — `[data-jcp-path]` → keys in `content` JSON
- **Links** — `[data-jcp-href-path]`
- **Media** — `[data-jcp-media-url-path]`, `[data-jcp-media-alt-path]`
- **Collections** — `[data-jcp-array]` container + `[data-jcp-array-item]` items (benefits cards, FAQ, timeline steps, bullets)
- **Optional CTAs** — `[data-jcp-optional]` slots (delete restores placeholder)

### History / undo

Snapshots store `{ pageDocument, flatContent }`. Undo restores JSON then calls `JCP_SYNC_COLLECTIONS_FROM_CONTENT()` to rebuild deleted DOM nodes, then reapplies text. Timeline step numbers renumber via `updateTimelineStepNumbers()` on every collection refresh.

### Caching

Editor sets `DONOTCACHEPAGE` so page caches do not serve stale content to editors.

### Not editable (by design)

Live CPT/directory listings, decorative icons, demo phone mockup chrome.

---

## 🏗️ ARCHITECTURE & STRUCTURE

### Rendering Patterns

#### Pattern 1: Block-rendered Homepage (current default)
```php
// page-home.php
<?php get_header(); ?>
<?php jcp_niche_render_page(); ?>
<?php get_footer(); ?>
```
- PHP block system renders all sections server-side (`inc/page-blocks/render.php` → `inc/niche-landing/render.php`)
- `assets/js/pages/home-interactions.js` handles scroll/anchor behavior
- **Legacy fallback:** If front page has no `_jcp_page_content` meta, `assets/js/pages/home.js` renders via JS string templates

#### Pattern 1b: JavaScript String Templates (Pricing, Early Access)
```php
// page-pricing.php, page-early-access.php
<?php get_header(); ?>
<div id="jcp-app" data-jcp-page="pricing"></div>
<?php get_footer(); ?>
```
- JavaScript in `assets/js/pages/pricing.js` / `early-access.js` renders HTML via string templates

#### Pattern 2: PHP Survey + Demo App Shell
```php
// page-demo.php
<?php get_header(); ?>
<?php /* PHP survey templates or demo app container */ ?>
<?php get_footer(); ?>
```
- **Survey** (`/demo` without `?mode=run`): PHP templates in `templates/survey/` + `assets/js/pages/survey.js`
- **Demo app** (`/demo?mode=run`): `assets/demo/index.html` loaded via `jcp-render.js`

#### Pattern 3: Static HTML Templates (Directory, Estimate, Company profile)
```php
// page-directory.php, page-estimate.php, single-jcp_company.php
<?php get_header(); ?>
<div id="jcp-app" data-jcp-page="directory"></div>
<?php get_footer(); ?>
```
- `assets/js/core/jcp-render.js` loads HTML from `assets/directory/`, `assets/estimate/`, etc.
- WordPress enqueues replace stripped `<script>` / `<link>` tags from templates

#### Pattern 4: WordPress Content Templates (page.php, home.php, single.php)
- **Standard page** (`page.php`): One section, one container; optional ACF bottom CTA
- **Blog archive** (`home.php`): Post grid in one section block
- **Single post** (`single.php`): Title, meta, content, tags, nav, comments in one block

#### Pattern 5: PHP reference pages (UI Library)
- `page-ui-library.php` — Component gallery for designers/developers

#### Blog & single post styling (`css/pages/blog.css`)
- **Single post meta:** Author (round 36px avatar + name link), date, categories; dot separators
- **Comments:** Compact form and list spacing
- **Post navigation:** No extra top border above nav

---

## 📁 FILE ORGANIZATION

### Root Directory Structure

```
jobcapturepro-core/
├── assets/              # JS, HTML templates, icons (see assets/js/ for canonical JS)
├── css/                 # All stylesheets
├── docs/superpowers/    # Internal architecture plans (block system, etc.)
├── inc/
│   ├── page-blocks/     # Block registry, schema, render orchestration, REST
│   └── niche-landing/   # Section renderers, doc parser, industry CPT
├── templates/           # PHP template parts (header, footer, nav, survey)
├── *.php                # WordPress template files (MUST be in root)
├── DOCUMENTATION.md     # This file
└── README.md            # Quick start + pointers
```

### CSS Structure

```
css/
├── base.css             # Design tokens (CSS variables) + resets + typography
├── layout.css           # Containers, grids, section spacing
├── buttons.css          # Empty shim (button styles moved to components.css)
├── components.css       # Buttons, cards, badges, pills, accordions (single source of truth)
├── sections.css          # Homepage section styles (hero, FAQ, CTA, etc.)
├── utilities.css        # Helper classes (text-center, spacing utilities)
└── pages/
    ├── home.css         # Homepage-specific overrides only
    ├── pricing.css      # Pricing-specific overrides only
    ├── early-access.css # Early access-specific overrides only
    ├── directory-consolidated.css  # Directory page styles
    ├── profile-consolidated.css     # Company profile styles
    ├── demo.css         # Demo page styles
    ├── estimate.css    # Estimate page styles
    └── survey.css       # Survey page styles
```

**CSS Loading Order:**
```
base.css → layout.css → buttons.css → components.css → utilities.css → sections.css → page-specific.css
```

### JavaScript Structure

```
assets/js/
├── core/
│   ├── jcp-render.js   # Template loader (loads HTML files via AJAX)
│   └── jcp-nav.js      # Global navigation behavior
├── features/
│   ├── demo/
│   │   └── jcp-demo.js
│   ├── directory/
│   │   ├── directory.js
│   │   ├── profile.js
│   │   └── directory-integration.js
│   ├── estimate/
│   │   ├── estimate-builder.js
│   │   ├── analytics.js
│   │   └── requests.js
│   └── faq.js
└── pages/
    ├── pricing.js       # Pricing page renderer (string templates)
    ├── early-access.js  # Early access renderer (string templates)
    ├── survey.js        # Demo survey (PHP template companion)
    ├── niche-page-editor.js   # Live page editor (toolbar, undo, structure)
    ├── page-collection-editor.js  # Add/remove list items, cards, steps
    ├── page-media-editor.js   # Image/video slot editor
    ├── home-interactions.js   # Homepage anchors/interactions (block homepage)
    ├── home.js                # Legacy homepage renderer (fallback only)
    ├── industries-archive.js  # /industries/ archive
    ├── contact.js             # Contact form page
    ├── early-access-success.js
    └── wp-plugin-prototype.js
```

**Note:** Enqueue paths use `js/pages/foo.js`; `jcp_core_asset_path()` resolves to `assets/js/pages/foo.js`. All theme JS lives under `assets/js/`.

### Assets Structure

```
assets/
├── demo/
│   ├── index.html       # Demo app shell (?mode=run)
│   └── leaflet/         # Leaflet mapping library
├── directory/
│   ├── index.html       # Directory listing
│   └── profile.html     # Company profile
├── estimate/
│   └── index.html       # Estimate builder
├── js/                  # All theme JavaScript (canonical location)
└── shared/
    ├── assets/
    │   ├── demo.css
    │   └── survey.css
    ├── assets/icons/lucide/  # Lucide SVGs used by jcp_core_icon() and editor
    ├── img/
    └── video/
```

**Survey UI** is PHP-rendered (`templates/survey/`), not `assets/survey/index.html`.

### Template Structure

```
Root (WordPress REQUIRES these in root):
├── index.php                    # Fallback template
├── header.php                   # Wrapper → templates/global/header.php
├── footer.php                   # Wrapper → templates/global/footer.php
├── functions.php                # Theme bootstrap
├── page-home.php                # Homepage (block-rendered PHP; legacy JS fallback)
├── page-pricing.php             # Pricing (JS-rendered)
├── page-early-access.php        # Early access (JS-rendered)
├── page-demo.php                # Demo survey (PHP) + demo app (?mode=run)
├── page-directory.php           # Directory (loads HTML template)
├── page-estimate.php            # Estimate (loads HTML template)
├── page-contact.php             # Contact form
├── page-help.php                # Help articles
├── page-jcp-blocks.php          # Generic block page template
├── page-referral-program.php    # Referral program block page
├── page-ui-library.php          # UI component library (PHP-rendered)
├── archive-jcp_niche_landing.php # /industries/ archive
├── single-jcp_niche_landing.php  # Industry landing pages
├── single-jcp_page.php          # Generic block pages
└── single-jcp_company.php       # Company profile (loads HTML template)

templates/
├── global/
│   ├── header.php               # Full HTML header (called by root header.php)
│   └── footer.php               # Full HTML footer (called by root footer.php)
├── partials/
│   └── nav.php                  # Marketing + directory nav
└── survey/                      # Demo survey step templates
```

**Why Root Files Can't Be Moved:**
WordPress template hierarchy **REQUIRES** `page-*.php`, `single-*.php`, `header.php`, `footer.php`, and `index.php` to be in the root directory. WordPress won't find them elsewhere.

**Directory and company routing:** `/directory` and `/company` are served as standard WordPress responses (200, correct document title) via rewrite rules and `template_include` in `inc/template-routes.php`. Rewrite rules map `^directory/?$` and `^company/?$` to query var `jcp_route`; `template_redirect` clears 404 and sets title; `template_include` returns `page-directory.php` or `single-jcp_company.php`. This keeps Rank Math / Yoast compatibility and avoids “Page Not Found” titles. Both templates use `get_header()` / `get_footer()` and a single `#jcp-app` container; JS (jcp-render.js) loads directory listing or profile HTML into that container. Directory Mode header/footer switching applies on these pages via `jcp_is_directory_mode()`.

**page-company.php vs single-jcp_company.php:** All company-profile URLs (`/directory/[slug]`, `/company`, `/company?id=xxx`) are served by **single-jcp_company.php** via `template_include` when `jcp_route=company`. **page-company.php** is a Page template ("Company (App)") that appears in the Page Attributes dropdown; it is **not** used for `/company` (the rewrite rule sends that to single-jcp_company.php). page-company.php is only used when an editor creates a WordPress Page, assigns the "Company (App)" template to it, and the user visits that page's permalink (e.g. a custom URL like /contractor-profile/). If you do not need a Page at a custom URL that shows the company app, page-company.php is redundant and can be removed.

#### Navigation (marketing)

The main marketing nav (desktop) is defined in `templates/partials/nav.php`: **How it works**, **Features**, **Who it's for** (scroll to homepage sections; from other pages they link to `/#how-it-works`, etc.), **Pricing** (`/pricing`), **Resources** (minimal dropdown: Directory, Blog, Contact). Right-side CTAs: **Online Demo** (secondary), **Get Started** (primary). Demo, Directory, and Company profile pages use different nav sets. The Resources dropdown is click/hover on desktop, keyboard navigable (Enter/Space, Arrow keys, Escape closes). Mobile menu order: CTAs first, then How it works, Features, Who it's for, Pricing, Directory, Blog, Contact. Behavior (scroll-to-section, dropdown, mobile open/close) is in `assets/js/core/jcp-nav.js`.

**Directory Mode:** On directory-related pages (`/directory`, `/directory/*`, contractor profile `/company`), the same global header component switches to a contextual nav state so the directory feels like a marketplace destination. Detection: `jcp_is_directory_mode()` in `inc/helpers.php`. In Directory Mode: logo links to `/directory` and a small "Directory" label appears next to the brand; nav shows **Find contractors** → `/directory`, **How rankings work** → `/directory/#how-it-works`, **Trust & verification** → `/directory/#trust`; CTAs are **Find a contractor** (primary → `/directory`) and **Are you a contractor?** (secondary → `/`). Marketing links (Features, Pricing, Resources) are not shown in Directory Mode. Mobile menu mirrors the same mode.

**Footer Directory Mode:** The same global footer (`templates/global/footer.php`) uses `jcp_is_directory_mode()` to render a directory-appropriate variant. In Directory Mode: brand blurb is “Verified job proof from active contractors.” and logo links to `/directory`; link columns are **Directory** (Find contractors, How rankings work, Trust & verification), **For homeowners** (How it works, What verified means, Request a quote — Coming soon), **For contractors** (Get listed, See the live demo, Join early access). Privacy and Terms and social icons stay; “Powered by LeadsForward” is hidden in Directory Mode only.

---

## 📦 ASSET MANAGEMENT

### How Assets Are Loaded

#### CSS Loading (via `inc/enqueue.php`)
- **Base System**: Always loaded on all pages
  - `base.css` (design tokens + resets)
  - `layout.css` (grids, containers)
  - `buttons.css` (shim, empty)
  - `components.css` (reusable components)
  - `utilities.css` (helper classes)

- **Marketing Pages** (Home, Pricing, Early Access):
  - `sections.css` (homepage sections: hero, How it works, Real Job Proof, FAQ, CTA, etc.)
  - Page-specific CSS (`home.css`, `pricing.css`, `early-access.css`)

**Real Job Proof section** (homepage only, in `home.js` + `sections.css`): Renders directly under the 4-step "How JobCapturePro works" flow. Purpose: make the abstract flow concrete by showing four proof outputs (Google Business Profile, Website, Directory, Reviews) with icons and one-line copy, a directory reinforcement callout ("All JobCapturePro customers are added to the verified directory"), and a soft CTA to the live demo. Uses existing components (rankings-header, factor-icon-wrapper, demo-badge, timeline-cta-link). Reinforces proof, not features; introduces the Directory as proof. No images; icons and layout only.

- **Standard Pages & Blog** (generic `page.php`, blog archive, single post):
  - `layout.css` (containers, section spacing — ensures `.jcp-container` works on standard pages)
  - `sections.css` (when loading blog/single/page styles)
  - `blog.css` (single post, archive, comments, post cards)

- **Feature Pages** (Demo, Directory, Estimate):
  - `demo.css` (imports `assets/shared/assets/demo.css`)
  - `directory-consolidated.css` (imports `assets/directory/directory.css` + `assets/shared/assets/demo.css`)
  - `estimate.css` (imports `assets/estimate/estimate-builder.css`)

#### JavaScript Loading (via `inc/enqueue.php`)
- **Always Loaded:**
  - `js/core/jcp-nav.js` (global navigation)
  - `js/core/jcp-render.js` (template loader)

- **Page-Specific:**
  - Homepage: `js/pages/home.js`
  - Pricing: `js/pages/pricing.js` + `js/features/faq.js`
  - Early Access: `js/pages/early-access.js`
  - Demo: `js/features/demo/jcp-demo.js` + Leaflet library
  - Directory: `js/features/directory/directory.js`
  - Estimate: `js/features/estimate/*.js` (3 files)
  - Survey: `js/pages/survey.js`

#### HTML Template Loading (via `jcp-render.js`)
- Demo app (`/demo?mode=run`): `assets/demo/index.html`
- Directory page (`/directory`): `assets/directory/index.html`
- Company profile (`/directory/[slug]` or legacy `/company?id=[slug]`): `assets/directory/profile.html`
- Estimate page (`/estimate`): `assets/estimate/index.html`

**Survey** (`/demo` without `mode=run`) uses PHP templates in `templates/survey/`, not `jcp-render.js`.

**Note:** HTML templates have their `<script>` and `<link>` tags stripped by `jcp-render.js` and replaced by WordPress enqueues.

### Asset Helper Functions

Located in `inc/helpers.php`:

```php
jcp_core_asset_path($relative_path)    // Get file path
jcp_core_asset_url($relative_path)     // Get file URL
jcp_core_enqueue_style($handle, $path, $deps)  // Enqueue CSS
jcp_core_enqueue_script($handle, $path, $deps) // Enqueue JS
```

**Path Resolution:**
1. Check theme root for file
2. If not found, check `assets/` folder
3. Cache-busting via `filemtime()`

---

## 🎨 TEMPLATE SYSTEM

### WordPress Template Hierarchy

WordPress looks for templates in this order:
1. `page-{slug}.php` (e.g., `page-pricing.php`)
2. `page-{id}.php` (e.g., `page-123.php`)
3. `page.php` (generic page template)
4. `index.php` (fallback)

**Our theme uses:** `page-{slug}.php` files for all pages.

### Page Detection

Located in `inc/helpers.php::jcp_core_get_page_detection()`:

```php
$pages = [
    'is_home'         => is_front_page() || $path === '' || $path === 'home',
    'is_pricing'      => is_page_template('page-pricing.php') || $path === 'pricing',
    'is_early_access' => is_page_template('page-early-access.php') || $path === 'early-access',
    'is_demo'         => is_page_template('page-demo.php') || $path === 'demo',
    'is_directory'    => is_page_template('page-directory.php') || $path === 'directory',
    'is_estimate'     => is_page_template('page-estimate.php') || $path === 'estimate',
    'is_company'      => is_singular('jcp_company') || $path === 'company',
    'is_ui_library'   => is_page_template('page-ui-library.php') || $path === 'ui-library',
];
```

### Template Routing

Located in `inc/template-routes.php`:
- Handles 404 fallbacks for routes like `/demo`, `/pricing`, `/directory`
- Maps URLs to template files even if WordPress pages don't exist
- Allows SPA-style routing

---

## 💻 DEVELOPMENT GUIDELINES

### CSS Development Rules

#### ✅ DO:
- Use CSS variables from `base.css` (e.g., `var(--jcp-color-primary)`)
- Use spacing scale (e.g., `var(--jcp-space-lg)` = 24px)
- Put reusable components in `components.css`
- Put homepage sections in `sections.css`
- Keep page-specific CSS minimal (< 200 lines ideally)

#### ❌ DON'T:
- Hardcode colors (use `var(--jcp-color-*)`)
- Hardcode spacing (use `var(--jcp-space-*)`)
- Put reusable styles in page-specific CSS
- Create page-specific component variants
- Use inline styles

### JavaScript Development Rules

#### ✅ DO:
- Put global behavior in `js/core/`
- Put feature-specific code in `js/features/{feature}/`
- Put page-specific renderers in `js/pages/`
- Use `window.JCP_ASSET_BASE` for asset paths
- Follow existing patterns (string templates vs HTML loading)

#### ❌ DON'T:
- Mix concerns (core vs features vs pages)
- Hardcode asset paths
- Create new rendering patterns without documenting

### Template Development Rules

#### ✅ DO:
- Keep WordPress-required files in root (`page-*.php`, `header.php`, `footer.php`)
- Use `get_template_part()` for reusable components
- Use `templates/global/` for header/footer/nav
- Keep templates minimal (delegate to JS or HTML files)

#### ❌ DON'T:
- Move WordPress-required files out of root
- Duplicate header/footer logic
- Mix PHP rendering with JS rendering unnecessarily

### Adding New Pages

1. **Create WordPress template** (`page-{slug}.php`):
   ```php
   <?php get_header(); ?>
   <div id="jcp-app" data-jcp-page="{slug}"></div>
   <?php get_footer(); ?>
   ```

2. **Add page detection** in `inc/helpers.php`:
   ```php
   'is_{slug}' => is_page_template('page-{slug}.php') || $path === '{slug}',
   ```

3. **Add enqueue logic** in `inc/enqueue.php`:
   ```php
   if ( $pages['is_{slug}'] ) {
       jcp_core_enqueue_style('jcp-core-{slug}', 'css/pages/{slug}.css', [...]);
       jcp_core_enqueue_script('jcp-core-{slug}', 'js/pages/{slug}.js', [...]);
   }
   ```

4. **Create renderer** (`js/pages/{slug}.js` or HTML template in `assets/{slug}/index.html`)

5. **Update `jcp-render.js`** if using HTML template pattern

---

## ✅ CURRENT STATUS

### Completed Refactoring Phases

#### ✅ Phase C - Stage 1C: Button Deduplication
- All button styles consolidated into `components.css`
- `buttons.css` converted to empty shim
- Zero visual changes, exact cascade preserved

#### ✅ Phase C - Stage 2: Homepage Section CSS Consolidation
- All homepage section styles moved to `sections.css`
- `home.css` now only contains page-specific overrides
- Sections can be reused across pages (FAQ, Final CTA)

#### ✅ Phase C - Stage 2B: Flattened Sections
- `sections.css` is now a single physical file (no `@import`)
- All section CSS inlined for better performance

#### ✅ Phase C - Stage 2C: Section Decommissioning
- All `css/pages/home/*.css` files converted to stubs
- Styles now live only in `sections.css`

#### ✅ Phase C - Stage 2D: Shared Section Rebinding
- Pricing and Early Access pages now use `sections.css` for FAQ/CTA
- No duplicate CSS, clean dependency chain

#### ✅ Phase C - Stage 3: JavaScript Organization
- All JS moved to `assets/js/` with clear structure:
  - `core/` - Global behavior
  - `features/` - Feature-specific modules
  - `pages/` - Page-specific renderers
- All enqueue paths updated

#### ✅ Phase C - Stage 4: Legacy Asset Decommissioning
- Empty folders removed (`assets/core/`, `assets/css/`)
- Legacy CSS/JS archived and later removed from repo

#### ✅ JCP Page Block System (June 2026)
- Unified block registry, schema, and REST API (`inc/page-blocks/`)
- Live front-end editor with undo/redo, media slots, collection add/remove
- Homepage migrated to PHP block render; `home.js` kept as legacy fallback
- Industry pages (`jcp_niche_landing`) and generic block pages (`jcp_page`)

#### ✅ Template Cleanup
- Deleted `front-page.php` (duplicate)
- Deleted `templates/pages/home.php` (unused)
- Deleted `templates/sections/` (unused)
- Deleted `templates/components/` (unused)
- Deleted unused partials
- Removed empty folders

#### ✅ Assets Cleanup
- Deleted `assets/shared/assets/marketing.css` (unused)
- Fixed Leaflet library location
- Removed broken JS references from HTML templates
- Cleaned up CDN references

#### ✅ Directory & Profile Page Refactoring
- Unified directory and profile pages with global design system
- Removed "box within box" styling issues
- Consolidated badge styles (removed borders, added unlisted variant)
- Improved hero layouts with gallery components
- Standardized CTA components across all pages
- Fixed navigation consistency across directory and profile pages

#### ✅ Badge System Updates
- Removed borders from all badge variants (verified, trusted, listed, unlisted)
- Added "Unlisted" badge variant (grey styling)
- Updated badge filtering logic to exclude unlisted badges when "verified only" is active
- Standardized badge appearance across directory listings and profile pages

#### ✅ Code Organization & Cleanup
- Removed audit documentation files (consolidated into main docs)
- Updated README and DOCUMENTATION with current project state
- Cleaned up unused CSS files and empty stubs
- Organized all JavaScript into clear feature/page structure

#### ✅ WordPress readiness & template improvements (Jan 2026)
- **Theme identity:** `style.css` — Text Domain `jcp-core`, Theme URI, Description; `functions.php` — `load_theme_textdomain()` for translations.
- **Page templates:** All custom page templates have `Template Name:` and docblocks (Home, Pricing, Early Access, Demo, Directory, Estimate, Company, Design System, UI Library) so they appear in Page Attributes.
- **Standard page / blog / single:** `page.php`, `home.php`, `single.php` use a single `<section>` so content is in one container with one block of padding (no double gap under headlines). Standard pages and blog load `layout.css` and (when needed) `sections.css` via `inc/enqueue.php`.
- **Single post:** Author with round avatar in meta; one horizontal rule before comments; compact comment section (smaller form, tighter list). `comments.php` textarea 4 rows; `blog.css` comment and post-nav spacing updated.
- **Escaping & i18n:** Archive dates escaped; “Read more”, “Tags:”, footer logo alt/text; comments “One Comment” fix; `page-ui-library.php` icon helper returns `esc_url()` + `sanitize_file_name()`; single post nav strings translatable.
- **Pricing content:** Plans and feature lists in `assets/js/pages/pricing.js` (Starter $99, Scale $249, Enterprise $399); additional pricing notes in pricing section; Enterprise+ removed.

### Current File Counts

| Category | Count | Status |
|----------|-------|--------|
| **Root PHP Files** | 14 | ✅ All required by WordPress |
| **Template Files** | 4 | ✅ All used |
| **CSS Files** | 15 | ✅ All used |
| **JavaScript Files** | 15 | ✅ All organized |
| **HTML Templates** | 5 | ✅ All used |
| **Unused Files** | 0 | ✅ All cleaned up |

---

## 📚 QUICK REFERENCE

### CSS Variables (Design Tokens)

**Colors:**
- `--jcp-color-primary` (#ff5036)
- `--jcp-color-secondary` (#1f2937)
- `--jcp-color-text-primary` (#111827)
- `--jcp-color-bg-primary` (#ffffff)
- `--jcp-color-border` (#e5e7eb)

**Spacing (8px scale):**
- `--jcp-space-xs` (4px)
- `--jcp-space-sm` (8px)
- `--jcp-space-md` (16px)
- `--jcp-space-lg` (24px)
- `--jcp-space-xl` (32px)
- `--jcp-space-2xl` (40px)
- `--jcp-space-3xl` (48px)
- `--jcp-space-4xl` (56px)
- `--jcp-space-5xl` (64px)
- `--jcp-space-6xl` (80px)

**Typography:**
- `--jcp-font-size-base` (16px)
- `--jcp-font-size-lg` (18px)
- `--jcp-font-size-xl` (20px)
- `--jcp-font-size-2xl` (24px)
- `--jcp-font-size-3xl` (30px)
- `--jcp-font-size-4xl` (36px)
- `--jcp-font-size-5xl` (48px)
- `--jcp-font-size-6xl` (60px)

**Full list:** See `css/tokens.css` or `css/base.css`

### Common CSS Classes

**Layout:**
- `.jcp-container` - Standard container (1240px max, 94% responsive)
- `.jcp-section` - Full-width section wrapper
- `.jcp-grid-2` - Two-column grid
- `.jcp-grid-3` - Three-column grid

**Components:**
- `.btn.btn-primary` - Primary button
- `.btn.btn-secondary` - Secondary button
- `.jcp-card` - Card component
- `.directory-badge` - Directory listing badges (verified, trusted, listed, unlisted)
- `.rankings-cta` - Standard CTA section (orange background, white text)

**Badge Variants:**
- `.directory-badge.verified` - Blue background, blue text (no border)
- `.directory-badge.trusted` - Orange background, brown text (no border)
- `.directory-badge.listed` - Light grey background, grey text (no border)
- `.directory-badge.unlisted` - Medium grey background, grey text (no border)

**Utilities:**
- `.jcp-text-center` - Center-aligned text
- `.jcp-text-muted` - Muted text color

### JavaScript Global Variables

- `window.JCP_ASSET_BASE` - Base URL for assets
- `window.JCP_CONFIG.baseUrl` - Site base URL
- `window.JCP_DIRECTORY_DATA` - Directory listings data
- `window.JCP_PROFILE_DATA` - Company profile data

### Page URLs & Templates

| URL | Template | Rendering Method |
|-----|----------|------------------|
| `/` or `/home` | `page-home.php` | PHP block render (+ `home-interactions.js`); legacy `home.js` if no block meta |
| `/pricing` | `page-pricing.php` | JS string templates |
| `/early-access` | `page-early-access.php` | JS string templates |
| `/early-access-success` | `page-early-access-success.php` | PHP template |
| `/demo?mode=run` | `page-demo.php` | HTML template (`assets/demo/index.html`) |
| `/demo` | `page-demo.php` | PHP survey (`templates/survey/`) + `survey.js` |
| `/directory` | `page-directory.php` | HTML template (`assets/directory/index.html`) |
| `/directory/[slug]` | `single-jcp_company.php` | HTML template (`assets/directory/profile.html`) |
| `/estimate` | `page-estimate.php` | HTML template (`assets/estimate/index.html`) |
| `/contact` | `page-contact.php` | PHP + `contact.js` |
| `/industries/` | `archive-jcp_niche_landing.php` | PHP archive |
| `/industries/[slug]` | `single-jcp_niche_landing.php` | PHP block render |
| `/pages/[slug]` | `single-jcp_page.php` | PHP block render |
| `/ui-library` | `page-ui-library.php` | PHP template |
| Generic page | `page.php` | PHP template |
| Blog archive | `home.php` | PHP template |
| Single post | `single.php` | PHP template |

---

## 🔌 SETUP & INTEGRATIONS

### JCP Companies CPT & API Sync

The theme includes the **JCP Companies** custom post type and sync with the JobCapture Pro API (`https://app.jobcapturepro.com/api/companies`). This replaces the former Code Snippets “Companies Directory” snippet; do not run that snippet and the theme together or the CPT will register twice and break the site.

**What it does (same as the original snippet):**
- Registers the `jcp_company` post type (menu: **JCP Companies**).
- **Import from API:** WP Admin → **JCP Companies** → **Import from API** to fetch companies and create/update posts. Option to “Force update existing companies.”
- **Daily cron:** Runs an import automatically once per day.
- **Shortcode:** `[jcp_companies]` with attributes `limit`, `columns`, `show_description`, `show_address`, `show_phone`, `show_logo`, `show_industries`, `show_tags`.
- **Admin:** Custom list columns (Company ID, Industries, Tags, Phone, Last Synced), Company Information meta box on edit screen.

**API key (required for Import and cron):** The JobCapture Pro API key is **not** stored in the theme. It must be set in **wp-config.php** so it is never committed to the theme repo.

1. Open **wp-config.php** (WordPress root, not inside the theme).
2. In the “Add any custom values” section (above `/* That's all, stop editing! */`), add:
   ```php
   define( 'JCP_API_TOKEN', 'your_jobcapturepro_api_key_here' );
   ```
3. Replace `your_jobcapturepro_api_key_here` with your actual JobCapture Pro API key (from the JobCapture Pro app dashboard or your previous snippet).
4. Save wp-config.php. Do not commit wp-config.php with the real key; it lives outside the theme and is not pushed with the theme to GitHub.

Without this constant, **Import from API** and the daily cron cannot authenticate; the theme shows an admin notice on JCP Companies screens when the token is missing.

**Theme files:** `inc/jcp-api-cpt.php` (CPT registration, API client, import page, cron, shortcode, admin columns/meta box); `inc/company-data.php` (description resolution and generated descriptions for display). Directory and profile pages read company data via `jcp_core_company_data()` which uses the resolved description (API description if ≥120 chars, else generated, else fallback).

### Local Development

- Run WordPress locally (e.g. [Local](https://localwp.com/) by Flywheel, MAMP, or similar).
- Point the site at the theme directory; ensure PHP and MySQL meet WordPress requirements.
- For GHL webhook testing, use a tunnel (e.g. ngrok) so GoHighLevel can reach your local REST endpoints, or test on a staging URL.

### GoHighLevel Webhooks

The theme posts form submissions to **two separate** GoHighLevel inbound webhooks. Do not mix them.

| Form | Purpose | Webhook URL constant | File |
|------|---------|----------------------|------|
| **Early Access** | Founding crew signup → Early Access automation | `JCP_GHL_WEBHOOK_URL_DEFAULT` | `inc/rest-early-access.php` |
| **Demo Survey** | Demo signup (opt-in + viewed-demo) → single workflow with Event branching | `JCP_GHL_DEMO_SURVEY_WEBHOOK_URL` | `inc/rest-demo-survey.php` |

- **Early Access:** REST route `POST /wp-json/jcp/v1/early-access-submit`. Payload: `application/x-www-form-urlencoded`, flat key-value. Keys: First Name, Email, Phone, Company, Trade, Message, Referral Source[].
- **Demo Survey:** Two REST routes post to the **same** webhook. (1) `POST /wp-json/jcp/v1/demo-survey-submit` when user clicks "Continue to preview" — full form, Event= demo-opt-in, tags demo-completed, demo-interest. (2) `POST /wp-json/jcp/v1/demo-viewed-submit` when user clicks "Skip to demo" or "Launch the live demo" — Event= demo-viewed, tags viewed-demo. GHL workflow branches on Event (if/then).

### ACF (Advanced Custom Fields)

- **Theme-level settings removed:** Homepage Settings and JCP Theme Settings ACF options pages have been removed. Nav CTAs (Online Demo, Get Started), homepage hero CTAs, Early Access/Demo Survey copy, and footer contact are hardcoded in templates; can be reintroduced later.
- **Per-page bottom CTA:** Optional CTA block at the bottom of standard Pages. ACF field group **Bottom CTA** on post type `page`. Content-level customization only (headline, supporting text, button label, URL)—no design or layout controls. Uses the existing global CTA component (`.rankings-cta`); no new CSS.

**Per-page CTA field keys (ACF on post type `page`):** `enable_page_cta` (true/false), `page_cta_headline`, `page_cta_supporting_text`, `page_cta_button_label`, `page_cta_button_url`. CTA renders only when `enable_page_cta` is true and headline, button label, and button URL are non-empty. Rendered in `page.php` only; uses existing `.rankings-cta` markup.

### Debug Logging

- GHL webhook requests/responses are logged with `error_log()` only when `WP_DEBUG_LOG` is defined and true (see `inc/rest-early-access.php`). Disable in production or ensure logs are not exposed.

---

## 📋 FORMS & GOHIGHLEVEL

### Purpose of Each Form

| Form | Page / trigger | Purpose |
|------|----------------|---------|
| **Early Access** | `/early-access` | Founding crew signup. Collects contact info, business type, why interested, referral source. One submission per submit; payload goes to Early Access webhook only. |
| **Demo Survey** | `/demo` (no `mode=run`) | Demo opt-in and viewed-demo tracking. Step 3 "Continue to preview" sends full form to Demo webhook (Event= demo-opt-in). "Skip to demo" / "Launch the live demo" sends minimal payload to same webhook (Event= demo-viewed). GHL branches on Event. |

### Data Flow

1. **Frontend** → POST JSON to theme REST endpoint (e.g. `/wp-json/jcp/v1/early-access-submit` or `/wp-json/jcp/v1/demo-survey-submit`).
2. **REST handler** → Validates required fields, builds `application/x-www-form-urlencoded` body (flat key-value; no nested objects).
3. **Theme** → `wp_remote_post()` to the form’s GHL webhook URL.
4. **GHL** → Workflow receives webhook; mapping (payload key → contact field / custom field) is done in GHL, not in the theme.

### Field Naming Conventions (Single Source of Truth)

- **Canonical definitions:** `inc/form-fields.php` defines REST param names and GHL payload keys. Demo Survey is the source of truth. Both Early Access and Demo Survey REST handlers use these constants when building webhook bodies so GHL receives consistent keys.
- **REST request body (JSON):** Snake_case (e.g. `first_name`, `company`, `demo_goals`, `business_type`). Same concept uses the same param on both forms.
- **GHL payload (form-urlencoded):** Keys come from `form-fields.php` (e.g. `JCP_GHL_KEY_FIRST_NAME` → "First Name", `JCP_GHL_KEY_USE_CASE` → "Use Case"). Both forms send the same key for the same concept (e.g. "Use Case" for why interested / demo goals, not "Message").

### Shared vs Form-Specific Fields

**Shared fields (identical REST param and GHL key on both forms; defined in `inc/form-fields.php`):**

| Concept | Form label (Demo = source of truth) | REST param (both forms) | GHL key (both forms) | Value |
|---------|--------------------------------------|-------------------------|----------------------|-------|
| First name | First name | `first_name` | First Name | As entered |
| Last name | Last name | `last_name` | Last Name | As entered |
| Email | Email address | `email` | Email | As entered |
| Phone | Phone | `phone` | Phone | As entered |
| Business name / company | Business name | `company` | Company | As entered |
| Business type | Business type | `business_type` | Business Type | Display label (e.g. Plumbing, General Contractor) |
| Why interested / demo goals | (context-specific label) | `demo_goals` (array) | Use Case | Comma-joined labels |

- **First name / Last name:** Both forms collect first name and last name in separate fields. REST params `first_name` and `last_name` map to GHL keys "First Name" and "Last Name".
- **Labels/placeholders:** Demo Survey is the source of truth: "First name", "John"; "Last name", "Smith"; "Email address", "you@company.com"; "Business name", "Summit Plumbing"; "Business type", "Select your business type". Early Access uses the same labels and placeholders for these shared fields.
- Both forms send **Business Type** as the display label and **Use Case** for the “why interested” / “what should this demo prove” checkboxes. GHL workflows map **Use Case** and **Business Type**.

**Demo-only (exist only on Demo Survey; never sent by Early Access):**

- `service_area` → Service Area  
- `demo_goals` (array) → Use Case (comma-joined)  
- Event, Tags (demo-completed, demo-interest, viewed-demo)

**Early-Access-only (never sent by Demo Survey):**

- `referral_source` → Referral Source[] (array)

**GHL workflow notes:** Early Access sends **Use Case** (same key as Demo Survey) for the “why interested” checkboxes—not "Message". Early Access sends **Business Type** (same key as Demo Survey). If your Early Access workflow previously mapped **Trade** or **Message**, update it to map **Business Type** and **Use Case** instead.

### Webhook Mapping Philosophy

- All mapping from payload keys to GHL contact/custom fields happens in the **GHL workflow** (Create/Update Contact, etc.). The theme only sends consistent, flat key-value pairs.
- Do not rename payload keys arbitrarily; changing a key breaks existing GHL workflows unless they are updated.
- Adding a new optional field: add to REST args (optional), add to build-body function, add to frontend; then map in GHL.

### How to Add a Future Form Without Breaking GHL

1. **New form = new webhook.** Do not reuse Early Access or Demo Survey webhook URLs for a different form.
2. **New REST route** in the appropriate `inc/rest-*.php` (or new file required from `functions.php`). Validate required fields; build `application/x-www-form-urlencoded` body; `wp_remote_post()` to the new webhook URL.
3. **Use canonical keys from `inc/form-fields.php`** for overlapping concepts (e.g. `JCP_GHL_KEY_FIRST_NAME`, `JCP_REST_PARAM_COMPANY`, `JCP_GHL_KEY_USE_CASE`). Add new constants to `form-fields.php` only if the concept is truly new.
4. **Form-specific fields** use new REST params and new GHL payload keys; do not inject them into Early Access or Demo payloads.
5. **Document** in this section: form purpose, REST route, payload keys, and which webhook constant to define.

### Survey Script Location

- Demo Survey frontend logic lives in **`assets/js/pages/survey.js`** (not under `features/`). Enqueued on demo page when not `?mode=run`.

### Demo Analytics

- **Location:** WP Admin → **JCP** → **Demo Analytics** (read-only). Uses the same storage as demo events: custom table `wp_jcp_demo_events` and REST endpoint `POST /wp-json/jcp/v1/demo-event`. A second table `wp_jcp_demo_sessions` stores session-level summaries for drill-down only.
- **Metrics:** Total sessions (started), funnel completion and drop-off by step, CTA click counts, completion rate, average/median time to completion, primary drop-off step. **Demo → Early Access Conversion** is a separate metric. Aggregate counts are unchanged; no existing metric keys or formulas were modified.
- **Demo → Early Access Conversion:** Counts how many demo sessions later reached the `/early-access-success/` page in the **same session**. A conversion is recorded only when (1) the user started or ran the demo (session has `demo_started` or `demo_run_started`), (2) the user reached `/early-access-success/` with the same `session_id` in the URL (passed from the post-demo CTA and preserved through the Early Access form redirect). Conversion is **session-based, not user-based**: one row per session (`demo_converted` event); no PII. Stored in the same `wp_jcp_demo_events` table; no new keys beyond the existing `event_type` value `demo_converted`.
- **Session-level tracking (WordPress is NOT a lead system):** Session records are stored in `wp_jcp_demo_sessions` for read-only inspection only. Stored fields: `session_id` (required), `business_name` (optional), `business_type` (optional), `demo_started_at`, `demo_completed` (boolean), `demo_converted` (boolean), `conversion_at` (nullable). No phone numbers, no full emails, no editing, no export, no CRM-style actions. GoHighLevel remains the system of record for leads. Clicking **Total sessions started** or **Demo conversions** opens a modal with a table of up to 25 sessions (Session short hash, Business name, Demo completed, Converted, Started relative time); **Demo conversions** shows only sessions where `demo_converted = true`. Only users with `manage_options` can view session details; all output is escaped; logic fails silently if session data is unavailable.
- **Reset behavior:** When **Reset Demo Analytics** is used, both `wp_jcp_demo_events` and `wp_jcp_demo_sessions` are truncated, `analytics_start_date` is set to the current time, and the session list becomes empty. No partial reset.

---

## 🔧 MAINTENANCE

### Updating This Documentation

**When to Update:**
- After adding new pages/templates
- After restructuring CSS/JS
- After adding new features
- After cleanup/refactoring
- When file counts change

**How to Update:**
1. Edit `DOCUMENTATION.md` directly
2. Update relevant sections
3. Update "Last Updated" date
4. Commit changes

### File Organization Rules

1. **WordPress-required files MUST stay in root** (`page-*.php`, `header.php`, `footer.php`, `index.php`)
2. **CSS follows cascade order** (tokens → base → layout → components → sections → utilities → pages)
3. **JS follows structure** (core → features → pages)
4. **Templates are minimal** (delegate to JS or HTML files)
5. **No duplicate files** (one source of truth per component)

### Mobile & iOS (viewport / scroll)

The theme uses `min-height: 100dvh` and `-webkit-overflow-scrolling: touch` so content fills the visible area on iOS Safari and scroll is not blocked. Body overflow is reset on nav init and on `pageshow` so the mobile menu never leaves scroll locked (e.g. after back/forward or cached state). If content is missing until scroll or scroll is blocked on iOS, check: (1) no plugin or inline style setting `body { overflow: hidden }` on load; (2) WP Rocket / caching: try disabling "Delay JavaScript execution" or "Optimize CSS Delivery" for the theme; (3) ensure no other script sets `document.body.style.overflow` without restoring it.

### Cleanup Checklist

Before committing:
- [ ] No unused files
- [ ] No duplicate files
- [ ] All files documented
- [ ] Documentation updated
- [ ] Visual parity confirmed

---

## 📞 SUPPORT

### Common Issues

**Q: Why are HTML files in `assets/` folder?**
A: They're loaded client-side via AJAX by `jcp-render.js`. This is an unusual pattern but it's how the theme works.

**Q: Why can't I move `page-*.php` files to `templates/`?**
A: WordPress template hierarchy requires them in root. WordPress won't find them elsewhere.

**Q: Where do I add new CSS?**
A: 
- Reusable components → `components.css`
- Homepage sections → `sections.css`
- Page-specific → `css/pages/{page}.css`

**Q: Where do I add new JavaScript?**
A:
- Global behavior → `js/core/`
- Feature-specific → `js/features/{feature}/`
- Page-specific → `js/pages/`

**Q: How do I add a new page?**
A: See "Adding New Pages" section above.

---

**Last Updated:** January 28, 2026  
**Version:** 1.2  
**Maintained By:** Development Team  
**Questions?** Refer to this documentation first, then consult codebase.
