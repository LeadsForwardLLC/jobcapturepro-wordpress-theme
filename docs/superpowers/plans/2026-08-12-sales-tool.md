# Sales Tool Implementation Plan

> **For agentic workers:** Implement task-by-task. Steps use checkbox syntax.

**Goal:** Ship a WordPress-backed, theme-branded sales presentation (CPT + page template) with shared pricing, reviews, Present mode, and assessment-aligned fields.

**Architecture:** Shared `inc/pricing-plans.php`; `inc/sales-tool/*` for CPT/meta/config; full-screen `page-sales-tool.php` + CPT single; refactor `assets/jcp-sales-tool` to consume `JCP_SALES_TOOL`.

**Tech Stack:** WordPress theme PHP, vanilla JS, theme CSS variables.

## Global Constraints

- Review copy: QR / link on-site handoff, not automatic.
- Pricing from shared PHP only; link out to `/pricing/`.
- Integrations: Housecall Pro, Jobber, ServiceTitan, CompanyCam.
- Affiliate = presenter branding, not a third story mode.
- Commit and push when each major slice works.

---

## File map

| File | Responsibility |
|------|----------------|
| `inc/pricing-plans.php` | Canonical plans + trial CTA helpers |
| `inc/sales-tool/bootstrap.php` | Requires + hooks |
| `inc/sales-tool/cpt.php` | CPT + rewrite `/sales/` |
| `inc/sales-tool/meta.php` | Meta box save/load |
| `inc/sales-tool/config.php` | Build localize payload + reviews |
| `inc/sales-tool/enqueue.php` | Detect + enqueue assets |
| `page-sales-tool.php` | Blank live-call shell |
| `single-jcp_sales_deck.php` | CPT shell |
| `assets/jcp-sales-tool/app.js` | Deck runtime |
| `assets/jcp-sales-tool/styles.css` | Theme-aligned styles + Present |
| `assets/js/pages/pricing.js` | Consume shared plans when localized |
| `functions.php`, `inc/enqueue.php`, `inc/helpers.php`, `inc/admin-page-templates.php` | Wire-up |

---

### Task 1: Shared pricing + sales-tool PHP scaffolding

- [ ] Add `inc/pricing-plans.php` with Starter/Scale/Enterprise monthly prices and helpers
- [ ] Localize plans on pricing page enqueue
- [ ] Add `inc/sales-tool/*` CPT, meta, config, enqueue, bootstrap
- [ ] Add page + single templates; wire `functions.php`
- [ ] Flush rewrites on theme switch / first load

### Task 2: Front-end deck refactor

- [ ] Rebuild shell HTML in PHP templates (no standalone index dependency)
- [ ] Refactor `app.js` for `JCP_SALES_TOOL`, reviews chapter, Acculevel optional, Present mode, QR language, shared plans
- [ ] Restyle with `--jcp-*` tokens; Present mode CSS

### Task 3: Pricing.js consume shared config + polish

- [ ] Use `window.JCP_PRICING.plans` when present
- [ ] Smoke-check admin meta + front templates
- [ ] Commit and push
