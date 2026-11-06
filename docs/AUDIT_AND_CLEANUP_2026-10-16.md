# Theme audit & cleanup — 2026-10-16

Companion to [`WEBSITE_OWNERSHIP_HANDOFF.md`](./WEBSITE_OWNERSHIP_HANDOFF.md).  
Scope: organize the repo and stop shipping non-runtime weight to production **without** changing theme PHP/JS/CSS behavior.

---

## Findings

| Area | Status | Notes |
|------|--------|-------|
| Duplicate root icons (`assets/shared/assets/icons/*.{svg,json}`) | **Already cleaned** | Only `lucide/` remains (~1,667 SVGs). Docs still claimed ~3,300 orphans — corrected. |
| Lucide icon usage | Active | `jcp_core_icon()`, editors, demo JS — keep full set. |
| `.superpowers/` (~105MB tracked screenshots + SDD notes) | **Removed from git** | Local-only agent workspace. Not runtime. Gitignored. |
| Theme-root `proof-gap-screenshots/` | **Moved** | → `docs/qa/proof-gap-screenshots/` |
| `PERFORMANCE_AUDIT.md`, `THEME_REVIEW.md` | **Moved** | → `docs/archive/` (historical) |
| Deploy package | **Tightened** | Non-runtime paths excluded from SiteGround tarball/rsync |
| Dual `page-blocks` + `niche-landing` | Keep | Mid-migration; both required at runtime |
| `home.js` legacy fallback | Keep | Still enqueued when front page has no block meta |
| Niche → page schema aliases | Keep | Deprecated wrappers still called |
| Stale remote feature branches (~19) | Backlog | Archive with Chris — do not mass-delete |
| `assets/shared/reference-funnel` | Keep | Reference for proof-sprint rebuild (`inc/proof-sprint.php`) |
| `css/buttons.css` | Keep | Intentional empty enqueue shim |

---

## Changes in this pass

1. **Git**
   - Stop tracking `.superpowers/` (add to `.gitignore` + `.cursor/`)
   - Move QA screenshots under `docs/qa/`
   - Move historical audits under `docs/archive/`
2. **Deploy** (`.github/workflows/deploy-main.yml`)
   - Exclude: `.superpowers`, `.cursor`, `.tmp`, `node_modules`, `scripts`, package manifests, `docs/qa`, `docs/superpowers`, `docs/archive`
   - Still ship: runtime theme + ownership docs (`WEBSITE_OWNERSHIP_HANDOFF`, `CONTRIBUTING`, `INDEX`, tracking contracts)
3. **Docs**
   - Correct outdated icon-cleanup claims in `DOCUMENTATION.md` / handoff
   - This audit report + INDEX updates

---

## Intentionally not changed (avoid breakage)

- No PHP/JS/CSS refactors
- No deletion of `home.js`, niche aliases, or `niche-landing/`
- No remote branch deletion
- No lucide icon pruning (would need a used-icon inventory pass)

---

## Suggested next cleanups (Abdul / Chris)

1. With Chris: archive merged remote branches listed in handoff §11  
2. Optional: inventory lucide icons actually referenced → trim unused SVGs  
3. When homepage always has `_jcp_page_content` in all envs → remove `home.js` fallback  
4. Long-term: finish page-blocks / niche-landing renderer merge  

---

## Verify after deploy

- Homepage, `/directory`, one `/industries/{slug}/` page  
- GitHub Action “Deploy main to SiteGround” green  
- Confirm production theme dir no longer grows with agent screenshots (new deploys)
