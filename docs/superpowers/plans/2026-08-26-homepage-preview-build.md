# Homepage Preview (home_v2) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans or implement task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Ship a noindex `/home-preview/` page that implements the sales-deck homepage redesign without changing live `/`.

**Architecture:** New `home_v2` preset + `dummy-home-v2.json`, versioned seed (mirror contractor-demo), reuse problem/benefits/demo/how_it_works/faq/final_cta, add `local_falcon_proof` block + home-v2 CSS/JS.

**Tech Stack:** WordPress theme PHP blocks, niche-landing render, CSS/JS enqueue.

**Spec:** `docs/superpowers/specs/2026-08-26-homepage-sales-deck-redesign.md`

## Global Constraints

- Preview only — do not change front page
- Acculevel anonymous (111-location basement waterproofing)
- Primary CTA: Launch interactive demo; Secondary: Start free
- No ranking guarantees; SoLV before/after only
- Shared tokens/modules where practical

---

## File map

| File | Responsibility |
|------|----------------|
| `inc/page-blocks/presets.php` | Add `home_v2` block order |
| `inc/page-blocks/registry.php` | Register `local_falcon_proof` |
| `inc/niche-landing/dummy-home-v2.json` | Preview copy + props |
| `inc/niche-landing/seed.php` | Seed `/home-preview/` + version |
| `inc/niche-landing/render.php` | Render Local Falcon + home_v2 hooks |
| `inc/page-blocks/render.php` | Dispatch new block |
| `inc/enqueue.php` | home-v2 CSS/JS + noindex |
| `css/pages/home-v2.css` | Preview styles |
| `assets/js/pages/home-v2.js` | Motion / compare |
| Copy or reference LF assets | From `assets/jcp-sales-tool/assets/` |

---

### Task 1: Preset + registry + dummy JSON

- [ ] Add `home_v2` preset order per spec §4
- [ ] Register `local_falcon_proof` block
- [ ] Write `dummy-home-v2.json` with edged copy

### Task 2: Seed `/home-preview/`

- [ ] `jcp_niche_home_preview_document()` + `jcp_niche_seed_home_preview()`
- [ ] Option `jcp_home_preview_seed_version` = `1`
- [ ] Template `page-jcp-blocks.php`, noindex, chrome visible

### Task 3: Local Falcon render + five-surface + styles/JS

- [ ] `jcp_niche_render_local_falcon_proof()`
- [ ] Benefits `job_flow` / problem contrast props for theater
- [ ] `home-v2.css` + `home-v2.js` enqueue on preview

### Task 4: Verify + ship

- [ ] Local URL loads all sections
- [ ] Commit + push
