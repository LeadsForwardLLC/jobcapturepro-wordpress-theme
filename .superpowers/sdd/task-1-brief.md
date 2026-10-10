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
