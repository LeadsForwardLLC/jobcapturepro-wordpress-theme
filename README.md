# JobCapturePro Core Theme

WordPress theme for JobCapturePro public website, directory, estimator, and block-based marketing pages.

## Documentation

**Start with [DOCUMENTATION.md](./DOCUMENTATION.md)** — especially the [Developer Handoff](./DOCUMENTATION.md#developer-handoff) section.

| Topic | Where |
|-------|--------|
| Block pages & editor | DOCUMENTATION.md → JCP Page Block System, Live Page Editor |
| Editor SOP (for content team) | WP Admin → **JCP** → **Page System** |
| Block catalog | WP Admin → **JCP** → **Block Library** |
| Architecture migration | `docs/superpowers/specs/2026-06-15-jcp-block-page-system-design.md` |
| Companies / directory API | DOCUMENTATION.md → JCP Companies CPT & API Sync |

## Quick Start

### Theme structure

- **CSS:** `/css/` — `base.css` (tokens) → layout → components → sections → pages
- **JavaScript:** `/assets/js/` — core → features → pages (enqueue paths use `js/…`; resolved via `jcp_core_asset_path()`)
- **Block system:** `/inc/page-blocks/` (registry, schema, REST) + `/inc/niche-landing/` (section renderers)
- **Templates:** Root `page-*.php` + `/templates/global/`, `/templates/partials/`, `/templates/survey/`

### Deploy

Push to `main` → GitHub Actions (`.github/workflows/deploy-main.yml`) → SiteGround production.

### Key files

| File | Purpose |
|------|---------|
| `functions.php` | Theme bootstrap |
| `inc/helpers.php` | Assets, page detection, block editor enqueue |
| `inc/enqueue.php` | Styles/scripts per page type |
| `inc/page-blocks/` | Block page system |
| `inc/niche-landing/` | Section HTML + doc import + industry CPT |
| `inc/jcp-api-cpt.php` | Companies CPT + API sync (needs `JCP_API_TOKEN` in wp-config) |
| `inc/form-fields.php` | Canonical form field names for GHL webhooks |

### Forms & GoHighLevel

| Form | REST endpoint |
|------|---------------|
| Early Access | `POST /wp-json/jcp/v1/early-access-submit` |
| Demo Survey | `POST /wp-json/jcp/v1/demo-survey-submit`, `demo-viewed-submit` |
| Contact | `POST /wp-json/jcp/v1/contact-submit` |

See DOCUMENTATION.md → Forms & GoHighLevel for payload keys and GHL workflow notes.

### ACF scope

- **Removed:** Theme-level Homepage Settings and JCP Theme Settings (nav CTAs, form copy, footer are hardcoded in templates).
- **Active:** Per-page **Bottom CTA** on standard Pages only (`inc/acf-config.php`).

### Block page editor (logged-in editors)

Scripts: `page-media-editor.js` → `niche-page-editor.js` → `page-collection-editor.js`  
REST: `GET/POST /wp-json/jcp/v1/page/{id}`  
Bootstrap global: `JCP_NICHE_EDITOR`

### Setup

- **Local:** WordPress via Local by Flywheel (or similar)
- **API token:** `define( 'JCP_API_TOKEN', '…' );` in wp-config.php (not in theme repo)
- **GHL:** Two webhooks — Early Access and Demo Survey (do not swap URLs)

For full details, see [DOCUMENTATION.md](./DOCUMENTATION.md).
