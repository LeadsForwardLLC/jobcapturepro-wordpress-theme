# Task 2 Report: Render marketing footer from helpers with editable attrs

Status: DONE (with concerns noted)

## Changes
`templates/global/footer.php` only; the directory-mode and landing-minimal markup is unchanged.

- Added a data-loading block right after the `hide_site_chrome` `else`, for non-directory mode only:
  - It reads `jcp_get_marketing_footer()`.
  - Empty `trial_cta.url` falls back to `$jcp_onboarding_url` (footer_signup).
  - Relative URLs are resolved through `home_url()`.
- Blurb: `<p data-jcp-path="footer.blurb">`.
- Columns are looped from data:
  - Each `h4` gets `footer.columns.{id}.heading`.
  - Each link is wrapped in `div.jcp-collection-item[data-jcp-array-item=i]`.
  - Each link `<a>` gets `data-jcp-path=...links.{i}.label` and `data-jcp-href-path=...links.{i}.url`, via `jcp_niche_editable_link_paths`.
  - Each column's links container is `div.jcp-footer-col-links[data-jcp-array="footer.columns.{id}.links"]`.
  - `jcp_niche_collection_remove_btn( true )` and `jcp_niche_collection_add_btn( '+ Add link' )` render for editors only.
- Trial CTA sits in the company column with `footer.trial_cta.label` and `footer.trial_cta.url`. Tracking is `jcp_niche_cta_tracking_attr( $mf_trial_href, 'footer', $mf_trial_label )`.
- Socials: the loop uses `$footer_social_items`.
  - Directory mode gets `$social_links` unchanged.
  - Marketing mode gets data URLs plus `data-jcp-href-path="footer.socials.{key}"`.
  - Visitors never see empty-URL socials. Editors get an `href="#"` placeholder so they can fill it in.
- About: visitors don't see About while the page is unpublished, which matches the old behavior. Editors always see it.
- Links with an empty label or URL are hidden from visitors and shown to editors, with `#` as the href when the URL is empty.

## Verification
- `php -l templates/global/footer.php` passed.
- No local site was reachable, so I used a stub-WP harness (`/tmp/harness.php`) to render `footer.php` as visitor and as editor.
  - Defaults reproduce the old copy and links.
  - The trial CTA goes to the onboarding URL with `data-cta="Start Free Trial" data-cta-location="footer"`.
  - Editor mode emits add/remove buttons; visitor mode emits none.
  - All five `footer.socials.*` paths are present.
- A real logged-out homepage check (brief Step 3) is still outstanding.

## Fix: Company column visitor order (Support → Trial → About)
- Trial CTA `<a>` is now a sibling inside `div.jcp-footer-col-links[data-jcp-array="footer.columns.company.links"]`, immediately after the first rendered link item (Support), without `data-jcp-array-item`.
- `niche-page-editor.js` reindexes with `:scope > [data-jcp-array-item]` only, so collection indices stay correct.
- Fallback: if no company link renders (editor-only empty state), trial still outputs before `+ Add link`.
- About unpublished gate for visitors unchanged.

## Concerns
1. ~~DOM order in the Company column~~ **Resolved** — visitor order matches legacy footer (Support, Trial, About).
2. Link rows are wrapped in a `div.jcp-collection-item`. The CSS is block and `.jcp-footer-col a` still applies, so the layout is the same for visitors. There is no footer-specific styling yet for the add/remove buttons in editing mode; the existing `.jcp-collection-*` styles come from niche-landing.css, which may not load on every page. That is for Tasks 3–4.
3. Social anchors have only `data-jcp-href-path` (no `data-jcp-path`). The editor in Tasks 3–4 must handle href-only targets.
4. The trial label is no longer translatable through `esc_html_e`, because it now comes from the option.
