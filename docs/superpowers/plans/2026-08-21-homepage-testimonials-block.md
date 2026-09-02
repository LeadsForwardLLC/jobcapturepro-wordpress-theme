# Homepage Testimonials Block Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a reusable `testimonials` page block (featured review + slider of the rest) and place it on the homepage after `proof_flow`, seeded from `jcp_sales_tool_default_reviews()` with Peter Bonk featured by default.

**Architecture:** Register `testimonials` in the existing page-block system (registry → defaults → render → preset/seed). Markup follows `.jcp-section` / `.jcp-container` patterns. Small dedicated JS handles slider + optional promote-to-featured. CSS lives mainly in `css/pages/home.css`. A one-time content upgrade inserts the block into saved homepage meta when missing.

**Tech Stack:** WordPress theme PHP, page-blocks registry, vanilla JS, existing JCP CSS tokens.

**Spec:** `docs/superpowers/specs/2026-08-21-homepage-testimonials-block-design.md`

## Global Constraints

- Do not invent new reviews; seed from `jcp_sales_tool_default_reviews()`.
- Default featured review: Peter Bonk (`featured_key` = `peter-bonk`).
- Homepage order: insert after `proof_flow`, before `benefits`.
- Reuse homepage visual language (tokens, section headers); no new brand system.
- Respect `prefers-reduced-motion` for autoplay/transitions.
- Commit after each task; push when the feature is complete (repo rule).

## File map

| File | Responsibility |
|------|----------------|
| `inc/sales-tool/config.php` | Add stable `id` to each default review (slug) |
| `inc/page-blocks/registry.php` | Register type + default props |
| `inc/page-blocks/presets.php` | Insert into home `block_types` |
| `inc/page-blocks/doc-sections.php` | Map `testimonials` → doc section if used |
| `inc/page-blocks/render.php` | `case 'testimonials'` |
| `inc/niche-landing/render.php` | `jcp_niche_render_testimonials()` markup |
| `inc/niche-landing/dummy-home.json` | Seed props for fresh presets |
| `inc/page-blocks/testimonials-upgrade.php` (new) | Insert block into saved home content when missing |
| `functions.php` | Require upgrade file |
| `css/pages/home.css` | Featured + slider styles |
| `assets/js/pages/testimonials.js` (new) | Slider + featured swap |
| `inc/enqueue.php` | Enqueue JS on home |
| `assets/js/pages/niche-page-editor.js` | Editor fields: featured select, toggles |

---

### Task 1: Stable review IDs + default block props helper

**Files:**
- Modify: `inc/sales-tool/config.php`
- Modify: `inc/page-blocks/registry.php`

**Interfaces:**
- Produces: each review in `jcp_sales_tool_default_reviews()` includes `id` string (`trent-ellison`, `brian-hardy`, `heriberto-eddie-roman`, `peter-bonk`)
- Produces: `jcp_page_default_block_props( 'testimonials' )` returns full default props array

- [ ] **Step 1: Add `id` to each default review**

In `jcp_sales_tool_default_reviews()`, add `'id' => '…'` as the first key of each review array:

```php
[
	'id'     => 'trent-ellison',
	'name'   => 'Trent Ellison',
	'role'   => 'Home service operator',
	'quote'  => '…',
	'rating' => 5,
],
// brian-hardy, heriberto-eddie-roman, peter-bonk
```

- [ ] **Step 2: Register block in `jcp_block_registry()`**

Insert after `proof_flow` entry:

```php
'testimonials' => [
	'type'         => 'testimonials',
	'label'        => __( 'Testimonials', 'jcp-core' ),
	'description'  => __( 'Featured customer quote + slider of supporting reviews', 'jcp-core' ),
	'category'     => 'content',
	'legacy_key'   => 'testimonials',
	'doc_sections' => [ 'TESTIMONIALS' ],
	'page_kinds'   => [ 'home', 'marketing' ],
],
```

- [ ] **Step 3: Add defaults in `jcp_page_default_block_props()`**

```php
'testimonials' => [
	'eyebrow'         => __( 'Customer stories', 'jcp-core' ),
	'headline'        => __( 'Trusted by contractors who already take the photos', 'jcp-core' ),
	'subheadline'     => __( 'Real operators and agencies using JobCapturePro to turn completed jobs into visibility, content, and reviews.', 'jcp-core' ),
	'reviews'         => function_exists( 'jcp_sales_tool_default_reviews' ) ? jcp_sales_tool_default_reviews() : [],
	'featured_key'    => 'peter-bonk',
	'autoplay'        => true,
	'autoplay_ms'     => 6000,
	'show_stars'      => true,
	'show_roles'      => true,
	'show_eyebrow'    => true,
	'show_headline'   => true,
	'show_subheadline'=> true,
	'section_id'      => 'testimonials',
],
```

- [ ] **Step 4: Smoke-check via WP-CLI or local PHP**

```bash
wp eval 'print_r( array_column( jcp_sales_tool_default_reviews(), "id" ) ); print_r( jcp_page_default_block_props("testimonials")["featured_key"] );'
```

Expected: four ids printed; `featured_key` = `peter-bonk`.

- [ ] **Step 5: Commit**

```bash
git add inc/sales-tool/config.php inc/page-blocks/registry.php
git commit -m "$(cat <<'EOF'
Add testimonials block registry defaults and stable review ids.

Seed homepage social proof from sales-tool reviews with peter-bonk as the default featured key.
EOF
)"
```

---

### Task 2: Render markup

**Files:**
- Modify: `inc/niche-landing/render.php`
- Modify: `inc/page-blocks/render.php`

**Interfaces:**
- Consumes: props from Task 1
- Produces: `jcp_niche_render_testimonials( array $props ): void`

- [ ] **Step 1: Implement `jcp_niche_render_testimonials()`**

Add near other block renderers in `inc/niche-landing/render.php`. Behavior:

1. Normalize `$reviews` from props (fallback to `jcp_sales_tool_default_reviews()`).
2. Resolve featured by `featured_key` matching `id` (fallback name slug / first item).
3. Build `$secondary` = all reviews except featured.
4. Output section structure:

```html
<section class="jcp-section rankings-section jcp-block-testimonials" id="testimonials"
  data-jcp-testimonials
  data-autoplay="1|0"
  data-autoplay-ms="6000"
  data-featured-key="peter-bonk">
  <div class="jcp-container">
    <!-- section header via jcp_niche_render_section_header if compatible, else eyebrow/h2/p -->
    <div class="jcp-testimonials">
      <figure class="jcp-testimonials-featured" data-jcp-testimonials-featured>
        <!-- stars, blockquote, figcaption name/role -->
      </figure>
      <div class="jcp-testimonials-slider" data-jcp-testimonials-slider>
        <button type="button" class="jcp-testimonials-nav jcp-testimonials-nav--prev" aria-label="Previous">…</button>
        <div class="jcp-testimonials-track" data-jcp-testimonials-track>
          <!-- each secondary review: button.jcp-testimonials-card with data-review-key -->
        </div>
        <button type="button" class="jcp-testimonials-nav jcp-testimonials-nav--next" aria-label="Next">…</button>
        <div class="jcp-testimonials-dots" data-jcp-testimonials-dots></div>
      </div>
    </div>
  </div>
</section>
```

Include a `<template data-jcp-testimonials-store>` or `data-*` JSON blob of all reviews so JS can swap featured without a page reload.

Escape all output with `esc_html` / `esc_attr`.

- [ ] **Step 2: Dispatch in `jcp_page_render_block()`**

```php
case 'testimonials':
	jcp_niche_render_testimonials( $props );
	break;
```

- [ ] **Step 3: Visual check locally**

Temporarily force-render on a page or use a PHPUnit-less browser check: homepage (or temporary insert) shows featured Peter Bonk + 3 cards.

- [ ] **Step 4: Commit**

```bash
git add inc/niche-landing/render.php inc/page-blocks/render.php
git commit -m "$(cat <<'EOF'
Render testimonials block with featured quote and secondary strip.

Output accessible markup and a review data store so the slider can promote cards to featured.
EOF
)"
```

---

### Task 3: CSS — SaaS featured + strip

**Files:**
- Modify: `css/pages/home.css`

**Interfaces:**
- Consumes: class names from Task 2

- [ ] **Step 1: Add styles under a clear `/* Testimonials */` section**

Requirements:

- Section spacing consistent with neighboring home sections
- Featured: soft surface (`--jcp-color-bg-secondary` or equivalent), subtle border, generous padding, large quote type, amber/gold stars, coral left accent or top rule using `--jcp-color-primary`
- Slider cards: smaller, equal height, peek next card (`overflow-x: auto` / track with `scroll-snap`)
- Nav buttons circular, keyboard-focus visible
- Dots under track
- Mobile: featured full width; strip horizontal swipe
- `@media (prefers-reduced-motion: reduce)` — disable transforms/autoplay-dependent transitions

Do **not** invent purple gradients or heavy multi-layer shadows.

- [ ] **Step 2: Spot-check desktop + ~390px width in browser**

- [ ] **Step 3: Commit**

```bash
git add css/pages/home.css
git commit -m "$(cat <<'EOF'
Style homepage testimonials featured panel and review slider.

Keep the treatment on existing JCP tokens so the block matches surrounding home sections.
EOF
)"
```

---

### Task 4: Slider + featured-swap JS

**Files:**
- Create: `assets/js/pages/testimonials.js`
- Modify: `inc/enqueue.php`

**Interfaces:**
- Consumes: `[data-jcp-testimonials]` root + review store from markup
- Produces: working prev/next, dots, autoplay, click-to-feature

- [ ] **Step 1: Implement `testimonials.js` IIFE**

```js
(function () {
  'use strict';
  function init(root) {
    // parse reviews JSON from store
    // track scroll / index for secondary cards
    // prev/next + dots
    // autoplay with pause on hover/focus; skip if prefers-reduced-motion or data-autoplay=0
    // card click → set featured HTML from review data, rebuild secondary list, update data-featured-key
  }
  document.querySelectorAll('[data-jcp-testimonials]').forEach(init);
})();
```

Keep under ~150–200 lines; no dependencies.

- [ ] **Step 2: Enqueue on home (and when block present if detectable)**

In `inc/enqueue.php` where `jcp-core-home` / home interactions load:

```php
jcp_core_enqueue_script( 'jcp-core-testimonials', 'js/pages/testimonials.js', [ 'jcp-core-home-interactions' ] );
```

If a clean “block present” check exists, prefer that; otherwise home template enqueue is enough for v1.

- [ ] **Step 3: Manual test**

- Next/prev moves cards  
- Autoplay advances then pauses on hover  
- Click secondary → becomes featured; previous featured appears in strip  
- Reduced-motion: no autoplay

- [ ] **Step 4: Commit**

```bash
git add assets/js/pages/testimonials.js inc/enqueue.php
git commit -m "$(cat <<'EOF'
Add testimonials slider interactions and home enqueue.

Support autoplay, dots, and promoting a strip review into the featured panel.
EOF
)"
```

---

### Task 5: Preset, seed JSON, and saved-home upgrade

**Files:**
- Modify: `inc/page-blocks/presets.php`
- Modify: `inc/niche-landing/dummy-home.json`
- Create: `inc/page-blocks/testimonials-upgrade.php`
- Modify: `functions.php`
- Modify: `inc/page-blocks/doc-sections.php` (add `TESTIMONIALS` mapping if the file lists block→section)

**Interfaces:**
- Produces: home preset order includes `testimonials` after `proof_flow`
- Produces: `jcp_page_upgrade_home_testimonials( array $content, int $post_id ): array` inserts block when missing

- [ ] **Step 1: Update home preset `block_types`**

```php
'proof_flow',
'testimonials',
'benefits',
```

- [ ] **Step 2: Add testimonials content to `dummy-home.json`**

Add a `testimonials` key matching default props (eyebrow, headline, subheadline, featured_key, flags). Reviews can be omitted so render falls back to `jcp_sales_tool_default_reviews()`, or inline the four reviews with ids — prefer inline for offline seed stability.

Ensure `jcp_page_legacy_to_blocks()` maps `testimonials` via registry `legacy_key` (automatic if legacy key matches and preset order includes the type).

- [ ] **Step 3: Upgrade saved homepage documents**

Create `inc/page-blocks/testimonials-upgrade.php`:

```php
/**
 * Ensure home page documents include testimonials after proof_flow.
 */
function jcp_page_upgrade_home_testimonials( array $content, int $post_id ): array {
	if ( jcp_page_detect_kind( $post_id ) !== 'home' && ( $content['page_kind'] ?? '' ) !== 'home' ) {
		return $content;
	}
	$blocks = $content['blocks'] ?? [];
	if ( ! is_array( $blocks ) ) {
		return $content;
	}
	foreach ( $blocks as $block ) {
		if ( ( $block['type'] ?? '' ) === 'testimonials' ) {
			return $content;
		}
	}
	$new = [
		'id'    => 'testimonials-' . wp_generate_password( 8, false ),
		'type'  => 'testimonials',
		'props' => jcp_page_default_block_props( 'testimonials' ),
	];
	$out = [];
	$inserted = false;
	foreach ( $blocks as $block ) {
		$out[] = $block;
		if ( ! $inserted && ( $block['type'] ?? '' ) === 'proof_flow' ) {
			$out[] = $new;
			$inserted = true;
		}
	}
	if ( ! $inserted ) {
		$out[] = $new;
	}
	$content['blocks'] = $out;
	return $content;
}
```

Hook into the same upgrade path used by other page upgrades (e.g. where `jcp_page_upgrade_case_study_form_modal` is applied in `schema.php`). Mirror that pattern exactly.

Require the file from `functions.php` next to other page-block requires.

- [ ] **Step 4: Verify**

```bash
wp eval '
$id = (int) get_option("page_on_front");
$c = jcp_page_get_content($id);
echo implode(",", array_column($c["blocks"] ?? [], "type"));
'
```

Expected: `…,proof_flow,testimonials,benefits,…`

- [ ] **Step 5: Commit**

```bash
git add inc/page-blocks/presets.php inc/niche-landing/dummy-home.json inc/page-blocks/testimonials-upgrade.php functions.php inc/page-blocks/doc-sections.php inc/page-blocks/schema.php
git commit -m "$(cat <<'EOF'
Place testimonials on the home preset and upgrade saved home content.

Insert the block after proof_flow for new and existing homepage documents.
EOF
)"
```

---

### Task 6: Niche page editor controls

**Files:**
- Modify: `assets/js/pages/niche-page-editor.js`

**Interfaces:**
- Consumes: block type `testimonials` props
- Produces: editable eyebrow/headline/subhead, featured select, autoplay/stars/roles toggles

- [ ] **Step 1: Add selector map**

```js
testimonials: '.jcp-block-testimonials, #testimonials',
```

- [ ] **Step 2: Add field config**

Wire text paths for `testimonials.eyebrow|headline|subheadline`. Add a select for `featured_key` populated from `reviews[].id` / `name`. Toggles: `show_stars`, `show_roles`, `autoplay`.

Follow existing patterns for `proof_flow` / `faq` field definitions in the same file (mirror structure; do not invent a new editor framework).

- [ ] **Step 3: Manual editor test on homepage**

Change featured to Trent → Save → front-end shows Trent featured.

- [ ] **Step 4: Commit**

```bash
git add assets/js/pages/niche-page-editor.js
git commit -m "$(cat <<'EOF'
Expose testimonials featured select and toggles in the page editor.

Editors can choose which review is highlighted without a code change.
EOF
)"
```

---

### Task 7: End-to-end QA + push

**Files:** none (verification)

- [ ] **Step 1: Front-end checklist on `/` (hard-refresh)**

- [ ] Block appears after proof flow, before benefits  
- [ ] Peter Bonk featured by default  
- [ ] Three others in slider  
- [ ] Stars visible  
- [ ] Desktop + mobile layouts OK  
- [ ] Autoplay / pause / promote work  
- [ ] No console errors  
- [ ] No theme/Fluent conflicts on this section  

- [ ] **Step 2: Push branch commits to `origin`**

```bash
git push origin HEAD
```

- [ ] **Step 3: Confirm deploy / live homepage**

After SiteGround deploy, verify production homepage shows the section (cache-bust if needed).

---

## Spec coverage check

| Spec requirement | Task |
|------------------|------|
| Placement after `proof_flow` | 5 |
| Featured + slider | 2, 3, 4 |
| Peter Bonk default | 1 |
| Props / featured_key editable | 1, 6 |
| Seed from sales-tool reviews | 1, 5 |
| Homepage visual language | 3 |
| Autoplay + reduced motion | 3, 4 |
| Saved home upgrade | 5 |

## Placeholder scan

No TBD/TODO steps. Commands and code sketches are concrete; adjust only if a mirrored helper name differs slightly in `schema.php` (follow existing upgrade hook pattern).
