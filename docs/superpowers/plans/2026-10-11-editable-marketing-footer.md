# Editable Marketing Footer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let niche-style front-end editors change the global marketing footer (blurb, columns, trial CTA, socials) without a deploy, while directory and landing-minimal footers stay hardcoded.

**Architecture:** New site option `jcp_marketing_footer` with defaults matching today’s hardcoded marketing footer. PHP helpers load/merge/sanitize/save. Marketing branch of `templates/global/footer.php` renders from helpers and emits `footer.*` `data-jcp-path` / href paths. REST `GET|POST /jcp/v1/marketing-footer` for editors. Niche page editor localizes footer bootstrap, keeps `footerContent` separate from page `flatContent`, and dual-saves footer on Save.

**Tech Stack:** WordPress theme PHP, existing niche front-end editor (`niche-page-editor.js`), REST API `jcp/v1`.

**Spec:** `docs/superpowers/specs/2026-10-11-editable-marketing-footer-design.md`

## Global Constraints

- Marketing footer only — never change directory-mode or landing-minimal footers.
- Capability gate: same as niche — `jcp_niche_user_can_inline_edit()` (`edit_posts` + logged in).
- Defaults must pixel-match current hardcoded marketing copy/URLs until an editor saves.
- Privacy, Terms, Powered by LeadsForward, logo, layout, app badges: locked.
- Trial CTA keeps surface tracking `footer_signup` via existing `jcp_niche_cta_tracking_attr`.
- Commit after each task; push when the feature is complete (theme always-push rule).

## File map

| File | Responsibility |
|------|----------------|
| `inc/marketing-footer.php` (new) | Defaults, get/merge, sanitize, save, REST routes |
| `functions.php` | `require_once` marketing-footer.php |
| `templates/global/footer.php` | Marketing branch reads helpers + editable attrs |
| `inc/helpers.php` | Localize footer bootstrap + footerRestUrl on niche editor |
| `assets/js/pages/niche-page-editor.js` | Separate `footerContent`; route `footer.*` paths; dual-save |
| `docs/superpowers/specs/2026-10-11-editable-marketing-footer-design.md` | Already approved (reference only) |

---

### Task 1: Marketing footer PHP model + REST

**Files:**
- Create: `inc/marketing-footer.php`
- Modify: `functions.php` (require after `global-settings.php`)

**Interfaces:**
- Produces: `jcp_marketing_footer_option_key(): string` → `'jcp_marketing_footer'`
- Produces: `jcp_marketing_footer_defaults(): array`
- Produces: `jcp_get_marketing_footer(): array` (defaults deep-merged over saved option)
- Produces: `jcp_sanitize_marketing_footer( array $input ): array`
- Produces: `jcp_save_marketing_footer( array $input ): array` (sanitize + `update_option`)
- Produces: REST `GET|POST /wp-json/jcp/v1/marketing-footer` permission = `jcp_niche_user_can_inline_edit` when that function exists, else `current_user_can( 'edit_posts' )`

- [ ] **Step 1: Create `inc/marketing-footer.php` with defaults matching current footer**

```php
<?php
/**
 * Editable marketing footer (site option + REST).
 *
 * @package JCP_Core
 */

function jcp_marketing_footer_option_key(): string {
	return 'jcp_marketing_footer';
}

/**
 * @return array<string, mixed>
 */
function jcp_marketing_footer_defaults(): array {
	return [
		'blurb'     => 'Turn real job photos into proof, visibility, reviews, and more jobs.',
		'columns'   => [
			[
				'id'      => 'product',
				'heading' => 'Product',
				'links'   => [
					[ 'label' => 'How it Works', 'url' => home_url( '/#how-it-works' ) ],
					[ 'label' => 'Features', 'url' => home_url( '/#features' ) ],
					[ 'label' => 'By Trade', 'url' => home_url( '/industries/' ) ],
					[ 'label' => 'Pricing', 'url' => home_url( '/pricing' ) ],
				],
			],
			[
				'id'      => 'resources',
				'heading' => 'Resources',
				'links'   => [
					[ 'label' => 'Blog', 'url' => home_url( '/blog' ) ],
					[ 'label' => 'Help Center', 'url' => home_url( '/help' ) ],
					[ 'label' => 'Online Demo', 'url' => home_url( '/demo' ) ],
					[ 'label' => 'Referral Program', 'url' => home_url( '/referral-program' ) ],
				],
			],
			[
				'id'      => 'company',
				'heading' => 'Company',
				'links'   => [
					[ 'label' => 'Support', 'url' => home_url( '/support/' ) ],
					// Trial CTA is separate (trial_cta); About remains a normal link when page exists — render layer may still gate About.
					[ 'label' => 'About JobCapturePro', 'url' => home_url( '/about' ) ],
				],
			],
		],
		'trial_cta' => [
			'label' => 'Start Free Trial',
			// Empty url = use onboarding helper at render time (preserves footer_signup UTMs).
			'url'   => '',
		],
		'socials'   => [
			'facebook'  => 'https://www.facebook.com/profile.php?id=61574958638999',
			'x'         => 'https://x.com/jobcapturepro',
			'instagram' => 'https://www.instagram.com/jobcapturepro/',
			'tiktok'    => 'https://www.tiktok.com/@jobcapturepro',
			'youtube'   => 'https://www.youtube.com/channel/UCckc38UwNU5P8A7eI1txZAw',
		],
	];
}
```

Also implement deep merge get, sanitize (allowed column ids `product|resources|company`; max 12 links/column; `sanitize_text_field` labels; `esc_url_raw` URLs; social keys whitelist), save, and REST register on `rest_api_init`.

GET response shape: `{ "footer": { ...merged... } }`  
POST body: `{ "footer": { ... } }` → sanitize → save → return `{ "success": true, "footer": { ...saved... } }`

- [ ] **Step 2: Require from `functions.php`**

After `require_once ... '/inc/global-settings.php';` add:

```php
require_once get_template_directory() . '/inc/marketing-footer.php';
```

- [ ] **Step 3: Smoke-check defaults**

```bash
wp eval 'echo jcp_get_marketing_footer()["blurb"]; echo "\n"; echo jcp_get_marketing_footer()["columns"][0]["id"];'
```

Expected: blurb string + `product`.

- [ ] **Step 4: Commit**

```bash
git add inc/marketing-footer.php functions.php
git commit -m "$(cat <<'EOF'
Add marketing footer site option model and REST endpoints.

EOF
)"
```

---

### Task 2: Render marketing footer from helpers with editable attrs

**Files:**
- Modify: `templates/global/footer.php` (marketing `else` branch only — leave directory + minimal alone)

**Interfaces:**
- Consumes: `jcp_get_marketing_footer()`
- Produces: DOM with `data-jcp-path` under `footer.*` and link `data-jcp-href-path` for URLs

- [ ] **Step 1: At top of marketing branch, load footer data**

Inside the non-directory (`else`) marketing block, before markup:

```php
$mf = function_exists( 'jcp_get_marketing_footer' ) ? jcp_get_marketing_footer() : [];
$mf_blurb = (string) ( $mf['blurb'] ?? 'Turn real job photos into proof, visibility, reviews, and more jobs.' );
$mf_columns = is_array( $mf['columns'] ?? null ) ? $mf['columns'] : [];
$mf_trial = is_array( $mf['trial_cta'] ?? null ) ? $mf['trial_cta'] : [ 'label' => 'Start Free Trial', 'url' => '' ];
$mf_socials = is_array( $mf['socials'] ?? null ) ? $mf['socials'] : $social_links; // fallback map by key=>url
```

Resolve trial href:

```php
$mf_trial_label = (string) ( $mf_trial['label'] ?? 'Start Free Trial' );
$mf_trial_url_raw = trim( (string) ( $mf_trial['url'] ?? '' ) );
$mf_trial_href = $mf_trial_url_raw !== ''
	? $mf_trial_url_raw
	: $jcp_onboarding_url; // existing footer_signup URL already built above
```

- [ ] **Step 2: Replace hardcoded blurb / columns / trial / socials**

- Brand `<p>`: output `$mf_blurb` with `jcp_niche_editable_attr( 'footer.blurb' )` when helper exists.
- Loop `$mf_columns` for Product/Resources/Company. Paths: `footer.columns.{id}.heading`, `footer.columns.{id}.links.{i}.label`, href path `footer.columns.{id}.links.{i}.url` via `jcp_niche_editable_link_attr` pattern (label on text node + `data-jcp-href-path` on `<a>` — match niche CTA helpers).
- Company column: render Support + other non-trial links from data; render trial CTA from `$mf_trial_*` with tracking:

```php
jcp_niche_cta_tracking_attr( $mf_trial_href, 'footer', $mf_trial_label );
```

Paths: `footer.trial_cta.label`, `footer.trial_cta.url` (href path).

- About link: if column data includes About, render it; if page unpublished, still show for editors when `jcp_niche_user_can_inline_edit()`, hide for visitors (optional; simplest is always render URL from data).
- Socials: foreach known keys; skip empty URL for visitors; for editors with empty URL still render a placeholder `<a>` with href `#` and editable href path so they can fill it. Paths: `footer.socials.{key}` as href-only (keep SVG icons).

Add/remove for column links (editors only): wrap each column’s links list with `data-jcp-array="footer.columns.{id}.links"` using the same collection add/remove pattern niche lists use (`jcp_niche_collection_add_btn`, `jcp_niche_collection_remove_btn`, `data-jcp-array-item`). Link object shape `{ label, url }`.

- [ ] **Step 3: Manual check logged-out homepage**

Load homepage logged out. Footer copy/links must match pre-change marketing footer (trial still goes to onboarding with footer_signup surface).

- [ ] **Step 4: Commit**

```bash
git add templates/global/footer.php
git commit -m "$(cat <<'EOF'
Render marketing footer from editable site option with niche data paths.

EOF
)"
```

---

### Task 3: Localize footer into niche editor bootstrap

**Files:**
- Modify: `inc/helpers.php` (`jcp_core_maybe_enqueue_niche_page_editor` / localize block ~398–422)

**Interfaces:**
- Produces: `JCP_NICHE_EDITOR.footerRestUrl` and `JCP_NICHE_EDITOR.bootstrap.footer`

- [ ] **Step 1: Extend `wp_localize_script` payload**

```php
'footerRestUrl' => rest_url( 'jcp/v1/marketing-footer' ),
'bootstrap' => [
	// ...existing keys...
	'footer' => function_exists( 'jcp_get_marketing_footer' ) ? jcp_get_marketing_footer() : null,
],
```

- [ ] **Step 2: Commit**

```bash
git add inc/helpers.php
git commit -m "$(cat <<'EOF'
Pass marketing footer bootstrap and REST URL into niche editor.

EOF
)"
```

---

### Task 4: Niche editor dual-save for `footer.*` paths

**Files:**
- Modify: `assets/js/pages/niche-page-editor.js`

**Interfaces:**
- Consumes: `cfg.footerRestUrl`, `cfg.bootstrap.footer`
- Produces: in-memory `footerContent`; Save POSTs page content then footer when footer paths changed (or always POST footer if `footerContent` present)

- [ ] **Step 1: Initialize `footerContent` beside `flatContent`**

Near other state:

```javascript
let footerContent = (cfg.bootstrap && cfg.bootstrap.footer && typeof cfg.bootstrap.footer === 'object')
  ? JSON.parse(JSON.stringify(cfg.bootstrap.footer))
  : null;
let footerDirty = false;
```

Helper:

```javascript
const isFooterPath = (path) => typeof path === 'string' && (path === 'footer' || path.startsWith('footer.'));
```

- [ ] **Step 2: Route collect/setPath for footer**

In `collectFromDom`, when `path` / href path starts with `footer.`, write into `footerContent` (not `flatContent`) and set `footerDirty = true`.

Extract a small `setFooterPath(path, value)` that strips the `footer.` prefix and uses existing `setPath(footerContent, rest, value)`.

When reading for display sync, if editor applies flat content to DOM, skip `footer.*` elements (they are server-rendered).

- [ ] **Step 3: Dual-save on Save button**

After successful page POST (or in parallel before reload):

```javascript
if (footerContent && cfg.footerRestUrl) {
  const footerRes = await fetch(cfg.footerRestUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
    body: JSON.stringify({ footer: footerContent }),
  });
  if (!footerRes.ok) {
    statusEl.textContent = 'Page saved, but footer save failed — try again';
    saveBtn.disabled = false;
    updateDirtyState();
    return;
  }
  footerDirty = false;
}
```

Include `footerDirty` in `updateDirtyState()` / unsaved class.

- [ ] **Step 4: Collection add/remove for footer column links**

Ensure existing collection handlers honor array paths under `footer.columns.*.links` writing into `footerContent`. If current handlers only touch `flatContent`, add a branch: when base path starts with `footer.`, mutate `footerContent` and rebuild minimal link row HTML (label + href) matching footer markup.

- [ ] **Step 5: Manual editor test**

1. Log in as editor, open homepage, enter edit mode.  
2. Change footer blurb; change trial CTA URL to `/proof-gap/`; Save.  
3. Reload logged out — blurb + CTA URL persist; CTA click still has footer tracking attr.  
4. Confirm directory page footer unchanged.

- [ ] **Step 6: Commit**

```bash
git add assets/js/pages/niche-page-editor.js
git commit -m "$(cat <<'EOF'
Save marketing footer edits from the niche front-end editor.

EOF
)"
```

---

### Task 5: Acceptance pass + push

**Files:** none new (verification only)

- [ ] **Step 1: Acceptance checklist**

1. Logged-out marketing pages: footer matches defaults if option empty.  
2. Editor can edit blurb, column heading, link label/URL, trial CTA, social URL; Save persists.  
3. Directory footer + landing-minimal footer unchanged.  
4. Empty social URL hidden for visitors.  
5. Trial CTA with empty URL still uses onboarding helper + `footer_signup`.  
6. Trial CTA with custom URL uses that URL + footer tracking.

- [ ] **Step 2: Push**

```bash
git push origin HEAD
```

---

## Spec coverage (self-review)

| Spec requirement | Task |
|------------------|------|
| Site option JSON + defaults | Task 1 |
| Marketing-only render | Task 2 |
| Editable blurb/columns/CTA/socials | Task 2–4 |
| Locked legal/logo/layout/directory/minimal | Task 2 (untouched branches) |
| Same capability as niche | Task 1 REST + existing editor enqueue |
| REST save | Task 1 + 4 |
| `footer_signup` tracking | Task 2 |
| Empty socials hidden for visitors | Task 2 |
| Niche-style UX | Task 3–4 |

No placeholders left; function names aligned across tasks (`jcp_get_marketing_footer`, `footerContent`, `footerRestUrl`).
