# Thank You + Code/Embed Implementation Plan

> **For agentic workers:** Implement task-by-task. Commit after the feature lands.

**Goal:** Ship `code_embed` block + `thank_you` preset per `docs/superpowers/specs/2026-07-29-thank-you-code-embed-design.md`.

**Architecture:** `inc/code-embed.php` (sanitize/render) · registry/presets/finalize · render dispatch · Structure editor field · CSS.

## Tasks

1. Create `inc/code-embed.php` + require from `functions.php`
2. Register `code_embed` + `thank_you` preset + finalize defaults
3. Wire render, doc section, writer dropdown, editor JS, CSS
4. Update spec status; commit + push

## Test

- Layout dropdown shows Thank You page
- Skeleton: hero message + empty embed + final CTA
- Paste Calendly iframe / shortcode → Save → renders
- Disallowed script stripped for visitors
