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
