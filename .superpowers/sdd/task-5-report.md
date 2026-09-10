# Task 5 Report: Preset, seed, and saved-home upgrade

## Status: Complete

## Commit

`46ee595` — Place testimonials on the home preset and upgrade saved home content.

## Changes

| File | Change |
|------|--------|
| `inc/page-blocks/presets.php` | Inserted `testimonials` after `proof_flow`, before `benefits` in home preset |
| `inc/niche-landing/dummy-home.json` | Added `testimonials` key with default props and inline four reviews (`featured_key`: `peter-bonk`) |
| `inc/page-blocks/testimonials-upgrade.php` | New `jcp_page_upgrade_home_testimonials()` — inserts block after `proof_flow` when missing on home documents |
| `inc/page-blocks/schema.php` | Wired upgrade in `jcp_page_get_content()` after case-study form modal upgrade |
| `functions.php` | Required `testimonials-upgrade.php` next to other page-block upgrade files |
| `inc/page-blocks/doc-sections.php` | Added `testimonials` → `TESTIMONIALS` legacy map entry and sort order after `PROOF FLOW` |

## Upgrade hook path

1. `functions.php` requires `inc/page-blocks/testimonials-upgrade.php`
2. `jcp_page_get_content()` in `schema.php` runs upgrade chain:
   - `jcp_page_upgrade_industry_media_blocks`
   - `jcp_page_upgrade_embedded_demo_blocks`
   - `jcp_page_upgrade_case_study_form_modal` (if loaded)
   - **`jcp_page_upgrade_home_testimonials`** (if loaded)
3. When upgrade mutates content, `jcp_page_save_content()` persists the updated document automatically

## Verification

- PHP syntax: all modified files pass `php -l`
- JSON: `dummy-home.json` validates
- WP-CLI `wp eval` not available in this shell — manual check recommended:

```bash
wp eval '
$id = (int) get_option("page_on_front");
$c = jcp_page_get_content($id);
echo implode(",", array_column($c["blocks"] ?? [], "type"));
'
```

Expected substring: `proof_flow,testimonials,benefits`

## Deviations from brief

- Used `jcp_page_resolve_kind( $content, $post_id )` instead of non-existent `jcp_page_detect_kind( $post_id )` for home detection (matches existing schema helpers)

## Concerns

- **Auto-save on read:** First front-end/admin load of an existing home page without testimonials will mutate and save stored JSON (same pattern as other upgrades)
- **Missing proof_flow:** Upgrade appends testimonials at end if no `proof_flow` block exists (fallback in brief)
- **Not pushed** per task instructions
