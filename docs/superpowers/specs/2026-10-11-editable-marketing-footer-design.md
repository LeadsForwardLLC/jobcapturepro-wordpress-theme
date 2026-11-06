# Editable Marketing Footer — Design

**Date:** 2026-10-11  
**Status:** Approved  
**Scope:** Niche-style front-end editing for the global marketing footer only

## Decisions

| Topic | Choice |
|-------|--------|
| Depth | Full marketing footer content (blurb, columns, CTAs, socials) |
| UX | Same as niche pages — inline on the live site |
| Who | `jcp_niche_user_can_inline_edit()` (`edit_posts` + logged in) |
| Storage | Site option JSON (global, not per-page) |
| Directory footer | Out of scope (stays hardcoded) |
| Landing-minimal footer | Out of scope (Privacy/Terms/© only) |
| Privacy / Terms / Powered by | Locked |
| Logo | Locked |
| Layout / grid | Locked |

## Problem

The marketing footer in `templates/global/footer.php` is hardcoded. Editors can change niche/landing copy on the front end but cannot change footer CTAs or links without a deploy. Analytics show footer trial CTAs (`footer_signup`, `footer_get_listed`, `footer_get_started`) drive large onboarding volume with near-zero signups — editors need to retarget those URLs without engineering.

## Data model

Site option key: `jcp_marketing_footer` (array).

Defaults mirror today’s hardcoded marketing footer so the public site is unchanged until someone saves.

```
{
  blurb: string,
  columns: [
    {
      id: "product" | "resources" | "company",  // stable ids for tracking
      heading: string,
      links: [ { label: string, url: string } ]
    }
  ],
  trial_cta: { label: string, url: string },
  socials: {
    facebook: string,
    x: string,
    instagram: string,
    tiktok: string,
    youtube: string
  }
}
```

Notes:

- Column `id` values stay fixed; editors change heading/links, not column identity.
- Within a column, links are add / remove / reorder.
- Empty social URL → hide that icon for visitors; editors still see an edit affordance.
- Empty link label → omit that link for visitors.
- `trial_cta.url` may be any absolute or site-relative URL. When rendered, continue stamping CTA tracking with surface `footer_signup` (existing `jcp_niche_cta_tracking_attr` / onboarding UTM helpers). If the URL already points at the app onboarding helper, keep using `jcp_core_onboarding_app_url` + `footer_signup` defaults; if the editor points at a marketing path (e.g. `/proof-gap/`), render that URL and still attach footer CTA tracking attributes where applicable.

## Rendering

- `templates/global/footer.php` marketing branch reads via `jcp_get_marketing_footer()` (defaults merged over saved option).
- Directory mode and `$hide_site_chrome` minimal footer unchanged.
- Editable nodes emit `data-jcp-path` under a `footer.*` namespace, e.g. `footer.blurb`, `footer.columns.product.heading`, `footer.columns.product.links.0.label`, `footer.trial_cta.label`, `footer.socials.facebook`.
- Reuse niche editable attr helpers where practical (`jcp_niche_editable_attr`, `jcp_niche_editable_link_attr`) so the existing front-end editor toolbar recognizes paths.

## Editor UX

- Same capability gate as niche pages.
- When the niche/page front-end editor is active on any page that shows the marketing footer, footer fields are clickable.
- Saving footer paths writes the site option (not `_jcp_page_content` / niche post meta).
- REST: `POST /jcp/v1/marketing-footer` (or PATCH) with permission `jcp_niche_user_can_inline_edit` equivalent; sanitize URLs with `esc_url_raw`, text with `sanitize_text_field` / `wp_kses_post` as appropriate; reject unknown column ids.
- Optional: small “Footer” chip in the editor chrome when focus is inside `.jcp-footer` so editors know they are editing a global surface.

## Tracking

- Preserve surfaces for default / onboarding-bound CTAs: `footer_signup` (marketing Company trial link). Directory surfaces stay on the hardcoded directory footer.
- Do not invent new surfaces per edited URL unless product later requests it; one stable footer trial surface is enough for analytics.

## Out of scope (v1)

- Directory-mode footer editor
- Landing-minimal footer editor
- Logo upload / brand image
- Privacy, Terms, Powered by LeadsForward
- App Store / Play badge URL editor (leave `footer-apps` as today)
- WP admin settings screen (front-end only, matching niche)
- Per-page footer overrides

## Acceptance

1. Logged-out visitors see current footer until an editor saves changes.
2. User with `edit_posts` can change blurb, column headings/links, trial CTA, and social URLs on the live marketing footer and see updates after save/refresh.
3. Directory and campaign landing-minimal footers are unaffected.
4. Trial CTA still emits footer tracking when clicked.
5. Invalid or empty social URLs do not render broken icons to visitors.
