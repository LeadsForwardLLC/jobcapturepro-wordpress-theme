# Task 1 Report: Stable review IDs + testimonials block registry defaults

## Status

**DONE**

## Commit hash(es)

- `51eb13a16c0d42cd4e5638fdd7a2d175c460902e` — Add testimonials block registry defaults and stable review ids.

## Files changed

| File | Change |
|------|--------|
| `inc/sales-tool/config.php` | Added stable `id` key to each entry in `jcp_sales_tool_default_reviews()`; updated PHPDoc return type |
| `inc/page-blocks/registry.php` | Registered `testimonials` block in `jcp_block_registry()` (after `proof_flow`); added full default props in `jcp_page_default_block_props()` |

## Test / verification commands + results

### PHP syntax check

```bash
php -l inc/sales-tool/config.php
php -l inc/page-blocks/registry.php
```

**Result:** No syntax errors detected in either file.

### WP-CLI smoke check

```bash
wp eval 'print_r( array_column( jcp_sales_tool_default_reviews(), "id" ) ); print_r( jcp_page_default_block_props("testimonials")["featured_key"] );'
```

**Result:** WP-CLI not available in this environment (`wp not found`).

### PHP stub smoke check (fallback)

```bash
php -r "
define('ABSPATH', true);
function __(\$s, \$d = null) { return \$s; }
require 'inc/sales-tool/config.php';
require 'inc/page-blocks/registry.php';
\$ids = array_column(jcp_sales_tool_default_reviews(), 'id');
echo 'Review IDs: ' . implode(', ', \$ids) . PHP_EOL;
echo 'Count: ' . count(\$ids) . PHP_EOL;
\$props = jcp_page_default_block_props('testimonials');
echo 'featured_key: ' . (\$props['featured_key'] ?? 'MISSING') . PHP_EOL;
echo 'reviews count: ' . count(\$props['reviews'] ?? []) . PHP_EOL;
echo 'section_id: ' . (\$props['section_id'] ?? 'MISSING') . PHP_EOL;
\$registry = jcp_block_registry();
echo 'registry has testimonials: ' . (isset(\$registry['testimonials']) ? 'yes' : 'no') . PHP_EOL;
"
```

**Result:**

```
Review IDs: trent-ellison, brian-hardy, heriberto-eddie-roman, peter-bonk
Count: 4
featured_key: peter-bonk
reviews count: 4
section_id: testimonials
registry has testimonials: yes
```

### Grep confirmation

All four expected review IDs present in `inc/sales-tool/config.php`:

- `trent-ellison`
- `brian-hardy`
- `heriberto-eddie-roman`
- `peter-bonk`

## Concerns / questions

- **WP-CLI unavailable locally:** Smoke test used a stubbed `__()` include instead of full WordPress bootstrap. Recommend re-running the brief's `wp eval` command in a Local WP shell before Task 2 render work.
- **Not pushed:** Commit is local only per subagent instructions; branch is 1 commit ahead of `origin/main`.
- **Downstream tasks not started:** No render template, preset insertion, editor fields, CSS, or migration — Tasks 2–7 remain unimplemented as scoped.
