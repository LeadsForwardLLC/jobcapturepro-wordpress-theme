# Contributing — jobcapturepro-web (theme)

**Primary owner:** Abdul  
**Repo:** [LeadsForwardLLC/jobcapturepro-web](https://github.com/LeadsForwardLLC/jobcapturepro-web)

Read [`WEBSITE_OWNERSHIP_HANDOFF.md`](./WEBSITE_OWNERSHIP_HANDOFF.md) first.

---

## Branch & deploy rules

| Action | Result |
|--------|--------|
| Commit on a feature branch | Safe — nothing deploys |
| Push feature branch | Safe — nothing deploys |
| Merge / push to **`main`** | **Auto-deploys** production (SiteGround) |

Workflow: `.github/workflows/deploy-main.yml`  
Trigger: `push` to `main` (and manual `workflow_dispatch`)

### Required workflow

1. `git checkout main && git pull`
2. `git checkout -b feature/short-name` or `fix/short-name`
3. Implement + test locally
4. Push branch → open PR → review (coordinate with **Chris Allen** for risky or shared changes)
5. Merge to `main` only when ready for live
6. Confirm GitHub Action “Deploy main to SiteGround” succeeded
7. Spot-check production URLs

**Do not** push unfinished work to `main`.

---

## PR expectations

- One clear purpose per PR when possible
- Note SEO/content impact if marketing templates or Rank Math-related PHP changes
- Call out tracking/PostHog event changes explicitly (`docs/JCP_TRACKING_CONTRACT.md`)
- Tag **Abdul** on any Trevor-authored website PR so ownership stays visible

---

## What lives where

| Change type | Primary paths |
|-------------|----------------|
| Block / industry page HTML | `inc/niche-landing/`, `inc/page-blocks/` |
| CSS design system | `css/` |
| Page-specific JS | `assets/js/pages/` |
| Directory UI | `assets/directory/`, `assets/js/features/directory/` |
| Forms → GHL | `inc/rest-*.php`, `inc/form-fields.php` |
| Deploy | `.github/workflows/deploy-main.yml` only (secrets in GitHub, not repo) |

Secrets (`JCP_API_TOKEN`, SSH deploy keys, GHL URLs as constants) stay in **wp-config / GitHub Secrets** — never commit them.

---

## Content editors (no code deploy)

- WP Admin → **JCP → Page System** (SOP)
- WP Admin → **JCP → Block Library**
- Live front-end editor (logged-in) on block/industry pages
- Rank Math fields per page

Code changes still need a PR → `main` → deploy.

---

## After you merge

1. Watch Actions
2. Hard-refresh / purge cache (WP Rocket / SiteGround) if the change looks stale
3. Update Jira ticket status

---

## Questions

- Release / process → Chris  
- Historical ads/funnel context → Trevor  
- Day-to-day website ownership → Abdul
