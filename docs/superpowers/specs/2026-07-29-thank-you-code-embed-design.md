# Thank You Preset + Code/Embed Block — Design

**Date:** 2026-07-29  
**Status:** Shipped  
**Scope:** A only (Thank You template + global Code/Embed). Singular drag-and-drop elements are deferred.

## Goal

Editors can create a normal marketing thank-you page (site header/footer) with:

1. A thank-you message  
2. A safe calendar / embed zone  
3. A secondary CTA  

The embed capability is a **global block** usable on any JCP layout (not Thank You–only).

## Approach

**New `code_embed` block + `thank_you` page preset** (mirrors Campaign / Minimal / Features).

- Lives in the existing Structure editor (`+ Add block`, reorder, layout settings)  
- Normal site chrome (not Form Landing)  
- Safer rendering: shortcodes + allowlisted iframes only  

## Thank You preset

| Field | Value |
|-------|--------|
| Layout label | Thank You page |
| Preset slug | `thank_you` |
| `page_kind` | `marketing` |
| Site chrome | Normal header/footer (`hide_site_chrome` = false) |

### Default block stack

1. **Hero** (centered / condensed-friendly)  
   - Headline: thank-you message  
   - Subheadline: short supporting line  
   - Primary CTA hidden by default (or empty)  
   - Secondary CTA optional / off by default  
   - Visual off by default (`show_visual` = false)  
2. **Code / Embed**  
   - Optional section headline (“Book a time”)  
   - Empty embed by default (editor pastes Calendly / shortcode)  
3. **Final CTA**  
   - Secondary-style next steps (“Back to home”, “Explore the demo”)  

Writers can remove/reorder/add blocks like any other preset.

## Code / Embed block (`code_embed`)

### Registry

| Key | Value |
|-----|--------|
| Type | `code_embed` |
| Label | Code / Embed |
| Category | content |
| Page kinds | `industry`, `marketing`, `referral`, `home` |
| Doc section | `CODE EMBED` (optional for doc import) |

### Props

| Prop | Type | Default | Notes |
|------|------|---------|--------|
| `headline` | string | `Book a time` | Section title above embed |
| `subheadline` | string | `` | Optional supporting line |
| `embed_code` | string | `` | Shortcode **or** iframe HTML |
| `show_headline` | bool | true | Structure SHOW toggle |
| `show_subheadline` | bool | false | Structure SHOW toggle |

Layout uses standard section layout controls (width, background, align) like other content blocks.

### Editor UX

- Live Structure: editable headline/subheadline (same patterns as `form_embed`)  
- Embed field: textarea (admin Structure settings **and** on-page editor input for users who can edit), placeholder examples for Calendly iframe / `[shortcode]`  
- Empty state for editors: “Paste a shortcode or allowlisted iframe, then Save.”  
- Visitors see nothing in the embed panel if empty (section may still show headline if enabled)

### Rendering (safer mode)

On save / render, `embed_code` is normalized by `jcp_code_embed_sanitize()`:

1. **Trim** and strip wrapping backticks/quotes  
2. If value matches a **single shortcode** (`[tag ...]` only): keep via shortcode allow pattern (same spirit as Fluent sanitizer; allow any registered shortcode tag shape, run through `do_shortcode`)  
3. Else if value contains HTML: parse and keep **only** allowlisted `<iframe>` elements (and harmless wrappers like a single outer `<div>` if needed)  
4. Strip `<script>`, event handlers, `javascript:` URLs, unknown tags  

**Allowlisted iframe hosts** (host suffix match; filterable via `jcp_code_embed_iframe_hosts`):

- `calendly.com`  
- `cal.com`  
- `hubspot.com` / `hsforms.com` / `hs-sites.com`  
- `google.com` / `calendar.google.com`  
- `microsoft.com` / `outlook.office.com` / `outlook.office365.com`  
- `chilipiper.com`  
- `savvycal.com`  
- `youcanbook.me`  

Iframe attributes kept: `src`, `width`, `height`, `title`, `loading`, `allow`, `allowfullscreen`, `frameborder`, `style` (sanitized to sizing only where practical). Force `loading="lazy"` when missing. Add `referrerpolicy="no-referrer-when-downgrade"` if absent.

Invalid / disallowed content → empty render for visitors; editor sees a clear error hint (“Embed not allowed — use a shortcode or an iframe from an approved calendar host”).

### Output markup (sketch)

```html
<section class="jcp-section jcp-code-embed">
  <div class="jcp-container">
    <!-- optional intro -->
    <div class="jcp-code-embed__panel">
      <!-- sanitized shortcode output OR iframe -->
    </div>
  </div>
</section>
```

Light CSS in `css/pages/niche-landing.css` (or a tiny `css/components/code-embed.css` enqueued with niche/marketing): full-width responsive iframe (`aspect-ratio` or min-height ~640px for calendars), no card chrome unless section layout requests a surface.

## Wiring checklist

- `inc/page-blocks/registry.php` — register block + defaults  
- `inc/page-blocks/presets.php` — `thank_you` preset  
- `inc/page-blocks/render.php` — dispatch `code_embed`  
- `inc/page-blocks/doc-sections.php` / writer tools — include in marketing choices if applicable  
- New helper (e.g. `inc/code-embed.php` or under `inc/niche-landing/`) — sanitize + render  
- Live editor: collect/save `embed_code` path; Structure settings field  
- Admin Layout dropdown picks up preset automatically via `jcp_page_presets()`  

## Defaults (Thank You copy)

| Block | Default copy |
|-------|----------------|
| Hero H1 | Thanks — you’re on the list |
| Hero sub | We’ll follow up shortly. Prefer to talk now? Pick a time below. |
| Hero visual | Off |
| Hero primary CTA | Hidden / empty |
| Code embed headline | Book a time |
| Final CTA headline | While you wait |
| Final CTA primary | Back to home → `/` |
| Final CTA secondary | Explore the demo → `/demo` |

## Out of scope

- Singular drag-and-drop elements (headings/images/icons inside sections)  
- Form Landing changes  
- Expanding Form embed into a generic embed  
- Arbitrary raw HTML / unrestricted scripts  
- Auto-wiring Fluent Forms confirmation → Thank You URL (Fluent settings; document in admin help only)

## Success criteria

1. Layout dropdown shows **Thank You page**  
2. New pages from that layout get message + embed + secondary CTA  
3. Code/Embed can be added to any existing Block / Campaign / Industry page  
4. Calendly (or similar allowlisted) iframe and `[shortcode]` both work after Save  
5. Non-allowlisted script/HTML does not render for visitors  
6. Normal site header/footer remain visible  
