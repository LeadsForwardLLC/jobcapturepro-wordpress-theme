# Task 4 Report: Niche editor dual-save for footer.* paths

Status: DONE (manual browser test not run; `node --check` passes on both JS files)

## Changes
`assets/js/pages/niche-page-editor.js`
- `footerContent` (clone of `bootstrap.footer`), `footerPristine`, `footerHrefBaseline`, `footerDirty`.
- `isFooterPath`, `getFooterPath`, `setFooterPath`, plus `readContentPath`/`writeContentPath` routers. DOM paths address columns by id (`footer.columns.company.links.0.label`) but storage is a list, so the id segment is mapped to its index (unknown id = no-op; avoids clobbering the array).
- `collectFromDom`, string-array collector and href collector route `footer.*` into `footerContent`; `applyFlatContentToDom` reads footer values from `footerContent` (undo/redo).
- Href collection keeps the stored value while the DOM href is still what the server rendered (relative URLs expanded, empty trial CTA URL falling back to onboarding URL, `#` = empty), so no false dirty / no URL rewriting.
- `snapshot()` includes `footerContent`; `updateDirtyState` includes `footerDirty` (derived vs saved snapshot, so undo clears it); undo/redo restores footer.
- Save: POST page (if page part changed), then POST `{footer}` to `cfg.footerRestUrl` if footer changed. Footer failure: "Page saved, but footer save failed — try again", Save stays enabled, no reload; saved snapshot updated so retry only re-sends the footer. Network errors are caught.
- `editorApi.getPath/setPath` are footer-aware (so sub-editors work unchanged); added `footerContent` getter.

`assets/js/pages/page-collection-editor.js`
- Footer column links (`footer.columns.<id>.links`): default item `{label:'New link', url:''}`, row builder matching footer markup (with remove button), and trial CTA anchor (no `data-jcp-array-item`) is preserved/re-placed after the first link on rebuild.

## Concerns
- New link has empty URL until set via the Edit-link control (hidden from visitors until then, per footer.php).
- Manual editor test (brief Step 5) not performed.
