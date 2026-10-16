# Theme documentation index

Use this map to find the right doc. Prefer the ownership handoff if you are taking over the website.

| Doc | Audience | Purpose |
|-----|----------|---------|
| **[WEBSITE_OWNERSHIP_HANDOFF.md](./WEBSITE_OWNERSHIP_HANDOFF.md)** | Abdul / new owner | Ownership, deploy rule, repo map, SEO partners, first 48 hours |
| **[CONTRIBUTING.md](./CONTRIBUTING.md)** | All contributors | Branches, PRs, auto-deploy checklist |
| **[../README.md](../README.md)** | Everyone | Quick start |
| **[../DOCUMENTATION.md](../DOCUMENTATION.md)** | Developers | Full technical reference (blocks, assets, forms, GHL, handoff) |
| **[JCP_TRACKING_CONTRACT.md](./JCP_TRACKING_CONTRACT.md)** | Ads / analytics | PostHog / tracking event contract |
| **[STRIPE_TRIAL_WEBHOOK_SETUP.md](./STRIPE_TRIAL_WEBHOOK_SETUP.md)** | Ops | Trial tracking webhook notes (WP side) |
| **[../PERFORMANCE_AUDIT.md](../PERFORMANCE_AUDIT.md)** | Devs | Historical performance audit |
| **[../THEME_REVIEW.md](../THEME_REVIEW.md)** | Devs | Historical theme review notes |
| **superpowers/** | Context only | Design specs & implementation plans — not runtime docs |
| WP Admin → **JCP → Page System** | Editors | Live SOP for building/editing pages |
| WP Admin → **JCP → Block Library** | Editors | Block catalog |

## GitHub

- Repo: https://github.com/LeadsForwardLLC/jobcapturepro-web  
- Auto-deploy: push/merge to `main` → SiteGround (`.github/workflows/deploy-main.yml`)

## Out of scope (different repos)

| Surface | Owner repo |
|---------|------------|
| `app.jobcapturepro.com` | JCP-API / Next.js app |
| Stripe app billing, Firebase | App stack |
| n8n automations | Separate ops |

Marketing site may link into the app; app bugs are not fixed in this theme.
