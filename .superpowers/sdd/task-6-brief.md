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
