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
