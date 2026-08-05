# Form Landing Template — Design

**Date:** 2026-07-29  
**Status:** Approved (implement)

## Goal

Reusable, distraction-free full-screen WordPress page template for embedding Fluent Forms, calendars, surveys, or custom HTML — no site chrome.

## Approach

Classic `Template Name: Form Landing` page template (same convention as Contact / Demo / Pricing). Template-gated meta box for editable fields. Bare document shell (`wp_head` / `wp_footer` only), matching Demo survey’s chrome-free pattern.

## Layout

- Full viewport, white background
- Top bar: logo (left) · close X (right)
- Centered column ~780px max-width
- `h1` title → supporting paragraph → optional reassurance line → embed zone
- No cards, gradients, testimonials, or secondary CTAs

## Close behavior

- Accessible control (`aria-label="Close"`)
- Prefer `history.back()` when same-origin referrer exists
- Else navigate to configurable fallback (default `/personalized-demo/`)

## Admin fields

| Field | Storage |
|-------|---------|
| Logo | Attachment ID (+ URL fallback) |
| Page title | Text (falls back to WP title) |
| Supporting text | Textarea |
| Reassurance text | Text |
| Embed | Shortcode or HTML |
| Close fallback URL | URL |

## Assets

- `css/pages/form-landing.css` — lightweight, base tokens only
- `assets/js/pages/form-landing.js` — close button only
- Skip global nav / banner / marketing CSS stack on this template
- Hide floating chat widgets via body class CSS
- Fluent bridge enqueues when embed contains Fluent shortcode

## Out of scope

- JCP block editor / campaign presets
- Live Structure editing
- Multi-step page builder features
