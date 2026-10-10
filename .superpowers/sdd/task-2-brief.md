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
