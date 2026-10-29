# JobCapturePro Website & Directory — Ownership Handoff

**Primary owner:** Abdul  
**Scope:** Everything on **jobcapturepro.com** — WordPress theme, marketing site, contractor directory, industry/SEO pages, paid funnels hosted on WP, and related theme ops.  
**Repo:** [LeadsForwardLLC/jobcapturepro-wordpress-theme](https://github.com/LeadsForwardLLC/jobcapturepro-wordpress-theme) (theme folder = this repo root; older `jobcapturepro-web` remote still redirects)  
**Not in this repo:** `app.jobcapturepro.com` (Next.js / JCP-API), Stripe app billing, Firebase app, n8n (separate). Marketing may *link into* the app; app bugs (e.g. onboarding email lock) live in **JCP-API**, not this theme.

**Last updated:** 2026-10-16

---

## 1. Ownership model (read this first)

| Role | Responsibility |
|------|----------------|
| **Abdul (primary)** | Day-to-day development for the entire public website + directory. Feature work, bugs, SEO/page builds with the content team, PR review coordination with Chris, production deploys via process below. |
| **Chris Allen** | Coordinate before merges that affect shared systems, releases, or risky production changes. Align on branch strategy and go-live. |
| **Shannon, Sarah, Jess + SEO** | SEO strategy, keyword/page plan, content. Abdul enables them with Rank Math + block/industry page tooling and implements frontend/WP backend changes they need. |
| **Trevor** | May still ship occasional updates (ads funnels, Proof Gap, etc.) but is **not** the primary website developer. See §3 collaboration process. |

If work touches **both** the marketing site and the app, clarify ownership up front (theme vs JCP-API) and share links in Jira.

---

## 2. Critical deploy rule (do not skip)

**Pushing to `main` auto-deploys production.**

- Workflow: `.github/workflows/deploy-main.yml`
- Trigger: **push** to `main` (also manual `workflow_dispatch`)
- Target: SiteGround WordPress theme directory via SSH/rsync
- Effect: Live **jobcapturepro.com** updates within minutes

### What that means in practice

1. **Never push unfinished work to `main`.**
2. Create a **feature or fix branch**, open a PR, get review (coordinate with Chris).
3. Merge to `main` only when ready for production.
4. Watch the GitHub Actions run after merge; confirm the site after deploy.
5. Prefer small, reversible PRs over large dumps onto `main`.

**Vocabulary:** *Committing* saves history locally/on a branch. *Pushing* sends commits to GitHub. **Only pushing (or merging) to `main` triggers auto-deploy.**

---

## 3. Collaboration process (Trevor still occasional)

When Trevor needs a website change:

1. Open a short Jira ticket (or comment on an existing one) with goal + acceptance criteria.
2. Prefer a **named branch** (`feature/…` or `fix/…`) and PR into `main` — not silent direct pushes when avoidable.
3. Tag **Abdul** as reviewer/owner so nothing lands without visibility.
4. Ads/funnel experiments (e.g. `/proof-gap/`) should note “do not change Meta / tracking definitions” if analytics-sensitive.
5. After merge, Abdul confirms deploy health.

Abdul remains accountable for overall site health even when Trevor contributes a PR.

---

## 4. Repo map (how the theme is organized)

```
jobcapturepro-core/          ← this Git repo root (installed as WP theme)
├── functions.php            ← bootstrap; loads inc/*
├── style.css                ← theme header only (real CSS is in /css)
├── page-*.php / single-*.php← WordPress templates (must stay in theme root)
├── header.php / footer.php  ← thin wrappers → templates/global/
├── inc/                     ← PHP modules (business logic, REST, admin)
│   ├── page-blocks/         ← block registry, REST, render orchestration
│   ├── niche-landing/       ← section HTML renderers + industry CPT (keep both)
│   ├── enqueue.php          ← which CSS/JS loads where
│   ├── helpers.php          ← asset paths, page detection
│   ├── template-routes.php  ← /directory and /company routing
│   ├── jcp-api-cpt.php      ← companies CPT + API sync
│   ├── marketing-footer.php ← editable marketing footer
│   ├── proof-gap*.php       ← paid Proof Gap funnel
│   ├── rest-*.php           ← form / survey REST endpoints
│   └── admin-*.php          ← WP Admin UI (Page System, Block Library, …)
├── templates/               ← PHP partials (global header/footer, funnels)
├── css/                     ← design system + page CSS
├── assets/
│   ├── js/core|features|pages ← front-end JS
│   ├── directory/           ← directory listing + profile HTML shells
│   └── campaign/ …          ← marketing assets
├── docs/                    ← developer docs (you are here)
├── .github/workflows/       ← deploy-main.yml
└── DOCUMENTATION.md         ← long-form technical reference
```

### Mental model

| Surface | How it renders |
|---------|----------------|
| Marketing / industry / block pages | Server-side PHP blocks (`page-blocks` + `niche-landing` renderers) |
| Directory + company profiles | WP route → `#jcp-app` → JS loads HTML from `assets/directory/` |
| Demo / surveys / Proof Gap | Dedicated `page-*.php` + page JS under `assets/js/pages/` |
| Blog | Standard WP posts + conversion helpers in `inc/blog-conversion.php` |

**Do not delete `inc/niche-landing/`** — still required at runtime for section HTML. Migration to a single block system is incomplete (documented in `DOCUMENTATION.md`).

---

## 5. Day-to-day: how to update the theme

### Local

1. Local by Flywheel (or equivalent) WordPress site pointing at this theme.
2. Secrets stay in `wp-config.php` (never commit): e.g. `JCP_API_TOKEN`, GHL webhook URLs if defined as constants.
3. Clear caches (WP Rocket / SiteGround) after verifying production changes that look “stuck.”

### Git workflow (required)

```bash
git checkout main
git pull origin main
git checkout -b feature/short-description
# … work …
git push -u origin HEAD
# Open PR → review with Chris as needed → merge to main → auto-deploy
```

### After merge to `main`

1. GitHub → Actions → “Deploy main to SiteGround”
2. Spot-check affected URLs on production
3. If deploy fails, do **not** keep pushing blind fixes without reading the Action log

### Content / SEO (no deploy needed for copy-only)

Editors can often update pages via:

- WP Admin → **JCP → Page System** (SOP)
- WP Admin → **JCP → Block Library**
- Live front-end editor (logged-in) on block/industry pages
- Rank Math fields per page

Theme code changes still need a deploy.

---

## 6. Directory (full ownership)

| Item | Detail |
|------|--------|
| Listing URL | `/directory` |
| Profile URL | `/directory/{slug}` (legacy `/company` still routed) |
| Templates | `page-directory.php`, `single-jcp_company.php` |
| Routing | `inc/template-routes.php` + `jcp_is_directory_mode()` in `inc/helpers.php` |
| Front-end | `assets/js/features/directory/*`, `assets/directory/*` |
| Data | Companies CPT + sync — `inc/jcp-api-cpt.php` (needs `JCP_API_TOKEN`) |
| SEO helpers | `inc/seo-directory.php` |

Directory Mode swaps header/footer nav for marketplace context (same global templates).

---

## 7. Marketing pages & SEO partnership

**Check in with Shannon, Sarah, Jess and the SEO team** on strategy and page pipeline. Your job is to make sure they can:

1. Create **industry pages** at scale (`jcp_niche_landing` → `/industries/{slug}/`)
2. Create **marketing pages** with template **JCP Block Page**
3. Set Rank Math title/description/focus keyword
4. Use live edit + WP Admin import tools without breaking layout

Theme SEO health helpers: `inc/page-blocks/seo-audit.php`.  
Architecture notes: `docs/superpowers/specs/2026-06-15-jcp-block-page-system-design.md`

---

## 8. Paid / acquisition surfaces on WP (know these)

| Path | Purpose | Primary code |
|------|---------|--------------|
| `/proof-gap/` | Paid Facebook survey funnel | `page-proof-gap.php`, `inc/proof-gap*.php`, `assets/js/pages/proof-gap*.js` |
| `/demo/` and related | Product demo / survey | `page-demo.php`, survey templates + JS |
| Job proof demo | Paid demo funnel | `page-job-proof-demo*.php`, `inc/job-proof-demo.php` |
| Forms → GHL | Lead capture | `inc/rest-*.php`, `inc/form-fields.php` |

Treat tracking (`docs/JCP_TRACKING_CONTRACT.md`, PostHog) carefully — do not casually rename milestone events.

---

## 9. Documentation index

| Doc | Use |
|-----|-----|
| **`docs/WEBSITE_OWNERSHIP_HANDOFF.md`** (this file) | Ownership, deploy, process, map |
| **`README.md`** | Quick start |
| **`DOCUMENTATION.md`** | Full technical reference (blocks, assets, forms, GHL) |
| **`docs/INDEX.md`** | Map of all docs |
| **`docs/CONTRIBUTING.md`** | Branch / PR / deploy checklist |
| WP Admin → JCP → Page System | Editor SOP |
| `docs/JCP_TRACKING_CONTRACT.md` | Analytics contract |
| `docs/STRIPE_TRIAL_WEBHOOK_SETUP.md` | Trial tracking (WP side) |
| `docs/superpowers/` | Historical design plans (context, not runtime) |

---

## 10. Access checklist (request if missing)

- [ ] GitHub: write access to `LeadsForwardLLC/jobcapturepro-wordpress-theme`
- [ ] SiteGround / WP Admin (editor or admin as needed)
- [ ] Ability to view GitHub Actions deploy logs
- [ ] Rank Math / SEO tools as required by Shannon–Jess
- [ ] PostHog project access (read) for funnel verification
- [ ] Jira project access (JCPDASH / related)

---

## 11. Hygiene & cleanup status

Full write-up: [`AUDIT_AND_CLEANUP_2026-10-16.md`](./AUDIT_AND_CLEANUP_2026-10-16.md)

**Done (Oct 2026):** root icon orphans already gone; `.superpowers/` untracked; QA screenshots under `docs/qa/`; historical audits under `docs/archive/`; deploy excludes non-runtime paths.

**Still open (do not crash-refactor):**

- Many remote feature branches — archive with Chris; don’t mass-delete without agreement
- Dual `page-blocks` + `niche-landing` — intentional mid-migration
- `home.js` legacy homepage fallback — keep until all envs have block meta
- App onboarding “email can’t edit” (`JCPDASH-159`) is **app** (`email` query param → `readOnly`) — not fixed in this theme

---

## 12. First 48 hours for Abdul

1. Clone repo, run locally, open homepage + `/directory` + one industry page.
2. Read this handoff + `DOCUMENTATION.md` Developer Handoff section.
3. Confirm GitHub Actions deploy workflow visibility.
4. Meet Chris on branch/PR expectations.
5. Sync with Shannon, Sarah, Jess on SEO page backlog and how you’ll support builds.
6. Skim open Jira website/directory tickets; take ownership of website-labeled work.
7. Comment on `JCPDASH-159` only if helping triage — fix belongs in JCP-API onboarding.

---

**Questions:** Tag Chris for process/release; tag Trevor for historical funnel/ads context; own the website going forward.
