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
