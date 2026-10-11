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

## Fix: preserve empty trial CTA URL despite handoff rewrite (follow-up)
Status: DONE (`node --check` passes on both JS files; manual browser test not run)

Problem: comparing the current href to a baseline captured at init is unreliable because the handoff script rewrites/decorates onboarding URLs, so an empty `trial_cta.url` could be overwritten by the rendered onboarding href (e.g. when only the blurb was edited).

Fix (explicit intent via `data-jcp-href-edited`):
- `niche-page-editor.js` `collectHrefValue`: for footer paths without `data-jcp-href-edited`, return the stored `getFooterPath(path)` value (never the rendered href). With the flag, use the href (`#` -> empty). Baseline comparison and `footerPristine` removed.
- Link modal Apply sets `data-jcp-href-edited="1"` on footer path elements.
- Link modal prefill uses stored footerContent value for unedited footer paths (empty shows empty).
- `applyFlatContentToDom` clears the edited flag on footer hrefs after re-deriving DOM from stored content (undo/redo), so an empty value can't be re-collected as the fallback href. `footerHrefBaseline` is retained only for display of empty URLs.
- `page-collection-editor.js` `buildFooterLink`: new rows with a non-empty url get `data-jcp-href-edited="1"`; empty url renders `#` with no flag.
