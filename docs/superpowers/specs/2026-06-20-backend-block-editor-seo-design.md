# Backend Block Editor + SEO Layer — Design Notes

**Date:** June 20, 2026  
**Status:** Partially implemented (SEO Health shipped; backend canvas planned)

## Decisions

1. **New marketing pages:** WP Pages + `page-jcp-blocks.php` template (not `jcp_page` CPT).
2. **Industry pages:** `jcp_niche_landing` CPT at `/industries/{slug}/`.
3. **Component model:** Block registry + shared PHP/CSS renderers; JSON = content props only.
4. **SEO:** Rank Math for meta; theme `seo-audit.php` for cross-checks and list-column status.

## Phase 1 — Shipped

- SEO Health meta box on block page edit screens (`inc/page-blocks/seo-audit.php`)
- SEO column on Industries / Pages / Marketing Pages list tables
- Updated SOP: `inc/admin-theme-docs.php`, `DOCUMENTATION.md`

## Phase 2 — Backend block canvas (planned)

Reuse frontend block logic (`niche-page-editor.js`) in WP Admin:

- Meta box “Page structure” with same block list as live editor
- SortableJS reorder, + Add from `jcp_block_registry()` filtered by `page_kind`
- Save via existing `jcp_page_save_content()` on post save
- Read-only previews optional; primary action = “Edit content on live page”

## Phase 3 — Cleanup

- Delete root `assets/shared/assets/icons/*.{json,svg}` after staging icon check (~3,300 files)
- Remove `home.js` after block homepage confirmed everywhere
- Consolidate `inc/niche-landing/render.php` into `inc/page-blocks/` long-term

## Editor workflow (target)

```
Writer doc → Admin import → blocks[] JSON filled
           → Rank Math + SEO Health
           → Publish
           → Live editor: polish copy, media, reorder if needed
```

Document import already maps HERO/FAQ/etc. to registry block types via `jcp_page_legacy_to_blocks()`.
