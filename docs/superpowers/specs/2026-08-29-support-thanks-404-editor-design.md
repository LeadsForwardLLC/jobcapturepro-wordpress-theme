# Support Thank-You, Simple Page Editor & 404 — Design

**Date:** 2026-08-29  
**Status:** Approved (user chose recommended options; implement immediately)  
**Scope:** Support confirmation, front-end editability for Pricing/Support/thank-you, branded 404

## Decisions

| Topic | Choice |
|-------|--------|
| Topic messaging | Shared page + short topic-specific headline; shared body/SLA/contact |
| URL | Upgrade `/contact-success/` |
| Editor depth | Keep PHP/JS templates; wire main copy into existing front-end editor |
| 404 vibe | Warm contractor “job site isn’t on the map” + helpful exits |

## Support thank-you (`/contact-success/`)

- Template: `page-contact-success.php` (ensure WP page exists for editor + permalink)
- Query: `?topic=` from Fluent Topic field (slug or label)
- Topic headline variants for: Getting Started, Technical Issue, Feature Request, Billing, General Question (+ default)
- Shared copy: thanks, response in 24–48 hours, immediate help via `support@jobcapturepro.com` and `(941) 941-9506`
- CTAs: Back to Support, Help Center (editable labels/URLs where practical)
- SEO: always `noindex, follow`
- Theme JS on Support form success redirects to `/contact-success/?topic=…` (Fluent confirmation can also redirect there)

## Simple editable pages

Extend content-page detection for:

- `page-support.php`
- `page-pricing.php`
- `page-contact-success.php`

Seed `_jcp_page_content` defaults (hero headline/subheadline + key CTAs). Templates emit `data-jcp-path` so the existing niche editor can inline-edit. Pricing JS stamps the same paths on the rendered hero.

Out of scope: blog, archives, directory, demo shells, form-landing field chrome.

## 404

- New `404.php` + `css/pages/404.css`
- Map-pin / “this job site isn’t on the map” composition
- Helpful links: Home, Support, Help Center, Pricing, Demo
- Site chrome via header/footer; noindex optional (404s typically not indexed)

## Fluent Forms admin note

Prefer Confirmation → Redirect to `/contact-success/` (theme also redirects on success as backup).
