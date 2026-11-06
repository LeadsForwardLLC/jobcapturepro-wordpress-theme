# App Prototype UI Parity — Design Spec

**Date:** 2026-11-06  
**Status:** Design approved in chat; written spec awaiting confirmation  
**Approach:** Evolve the shared phone shell (Approach 1)  
**Surfaces:** `/onboarding` (full interactive prototype) and `/demo?mode=run` (same UI, soft limits + guided tour)

## Goal

Bring the marketing-site phone prototype into visual and interaction parity with the current native JobCapturePro app (as captured in the Nov 2026 screenshots), while:

1. Keeping **one shared phone source** so `/onboarding` and `/demo` cannot drift.
2. Enabling **all buttons and actions** on `/onboarding`.
3. Keeping `/demo` **demonstration-safe** with soft limits and a maintained, controlled tour.
4. Using **only personalized or fake/generic contractor data** — never real people or real customer brands from screenshots.

## Non-goals

- Do not change `app.jobcapturepro.com` signup/onboarding.
- Do not implement real push, email delivery, offline sync, Google Business Profile posting, or social publishing backends.
- Do not rebuild the phone simulator chrome or abandon the shared `assets/demo/` shell.
- Do not add LeadsForward, Trevor Eddy, Sarasota office addresses, or any other screenshot-personal identity into defaults or fixtures.
- Do not remove the `/demo` guided tour; re-target steps to the new UI as needed.

## Architecture

### Single source of truth

| Asset | Path | Used by |
|-------|------|---------|
| Phone markup | `assets/demo/index.html` | `/onboarding`, `/demo?mode=run`, `/prototype` redirect target |
| Phone logic | `assets/js/features/demo/jcp-demo.js` | same |
| Phone styles | `assets/shared/assets/demo.css` (and related demo CSS) | same |
| Loader | `assets/js/core/jcp-render.js` | fetches demo HTML for `data-jcp-page="prototype"` and `data-jcp-page="demo"` |

`/onboarding` continues to wrap that shell in hub chrome (`page-onboarding.php` + `css/pages/onboarding.css` + analytics in `assets/js/pages/onboarding.js`). Hub chrome (logo, downloads, checklist) is out of scope unless a layout tweak is required for the taller/wider new screens.

### Mode flags

| Flag / condition | Behavior |
|------------------|----------|
| `JCP_IS_ONBOARDING_HUB` / prototype (`JCP_IS_DEMO_MODE === false`) | Full actions; no tour chrome |
| `JCP_IS_DEMO_MODE === true` | Soft limits + guided tour; survey personalization preserved |

Soft limits mean: screens remain visible and mostly tappable; actions that would break the demo narrative, leave the controlled flow, or imply real system effects are stubbed, toasted, or no-op’d with clear in-app feedback.

## Visual system

Match the updated native app language from screenshots:

- **Primary accent:** coral/red (~`#FF5A4D` / existing demo brand red — converge on one token, do not introduce purple or unrelated palettes).
- **Surfaces:** white backgrounds, light gray fills (`~#F2F2F2`), thin light borders, card radius ~12–16px.
- **Typography:** clean sans (existing prototype stack); bold titles; uppercase micro-labels for section headers (e.g. `INCLUDE A MESSAGE`, `PREFERENCES`, `RECENT`).
- **Chrome patterns:** circular back buttons; bottom nav **Check-ins / + FAB / Profile**; segmented **My Jobs / Team / Archive**; orange checkmarks for selected settings rows.
- **Sheets/modals:** bottom sheets with rounded tops for Switch location, Pending uploads, Publishing, Scan & Review QR.

Do not invent a new marketing look; this is a fidelity pass to the product UI.

## Screens (parity checklist)

Implement or restyle each of the following in the shared shell:

1. **Check-Ins (home)** — title + help; org/location switcher pill; My Jobs / Team / Archive; optional pending-upload banner; search + map FAB; check-in list cards (title, subtitle, truncated description, thumbnail, author, relative time); bottom nav.
2. **Switch location** — bottom sheet; search; location cards; active = “Current” + highlight. Demo: switching away from current is soft-blocked (toast); onboarding: switch works among fake locations.
3. **New Check-in** — back + title; Location card (CURRENT LOCATION + address + chevron); two large circular actions (camera / gallery); bottom nav.
4. **Edit Check-In** — back, title, Save; status badge (e.g. Not Published) + author · relative time; location + date rows; Photos (count badge, thumbnails, dashed Add); Description with AI badge + Regenerate; Tags “Add tags…”; primary **Request Review**; overflow (⋯); Archive as secondary/overflow path as in screenshots.
5. **Publishing** — sheet with Published / Not published (radio selection) + informational AI auto-publishing card; Close. Demo: AI card does not deep-link out; onboarding: same in-prototype explanation (no real admin dashboard).
6. **Request Review** — subtitle “Choose how you want…”; INCLUDE A MESSAGE textarea (editable default copy); **Scan QR** (secondary) + **Send Link** (primary).
7. **Scan & Review** — sheet with QR (generic/fake review URL payload, not a real customer brand) + short helper copy.
8. **Send Link** — onboarding: Web Share API when available, else copy-link toast; demo: in-app success toast only (no OS share sheet dependency).
9. **Profile** — title + notifications bell; optional identity card (fake or personalized name/email/role badge); ACCOUNT (Color Mode value, App Info, Location row); PREFERENCES (Notifications toggle, Offline Mode toggle); Log Out button.
10. **Color Mode** — System / Light / Dark list with checkmark; applies prototype theme class immediately on onboarding; demo may apply visually but must not break tour contrast assumptions (prefer allowing Light/System; if Dark breaks tour readability, soft-limit Dark with toast — prefer allowing Dark if styles support it).
11. **Notifications** — PREFERENCES toggles (Push, Email); RECENT list with fake/generic items (or personalized business name only when demo personalization is present).
12. **Pending uploads** — sheet listing device-stored items with Pending badge, retry/upload icon, delete; home banner opens this sheet.

Login / start-demo screen remains only as needed for demo bootstrap; onboarding hub should land on Check-Ins ready to use.

## Soft demo limits (explicit)

| Action | Demo | Onboarding |
|--------|------|------------|
| Guided tour steps / spotlight | Required; re-targeted to new controls | Off |
| Log Out | Soft-block (toast: “Not available in the demo”) | Allowed (returns to a safe start/home state in-prototype) |
| Offline Mode toggle | Soft-block or visual-only with toast | Fully toggleable (simulates offline banner / pending uploads UX in-prototype) |
| Switch to another location | Soft-block | Allowed among fake locations |
| Team / Archive tabs | Visible; soft-disabled with toast unless a tour step explicitly opens them (match today’s demo restriction pattern) | Fully usable with fake data |
| AI auto-publishing “admin dashboard” | Informational only; no external navigation | Informational only; no external navigation |
| Send Link / share | In-app toast / fake success | Web Share or copy |
| Delete pending upload that tour depends on | Soft-block or regenerate fixture | Allowed |
| Real store / CRM / network calls | None | None |

Tour structure (step order, outcome cards, mobile stepper) is preserved; copy and selectors update when controls move or rename (e.g. Publish Everywhere → Publishing / Request Review flow).

## Identity and content rules

**Hard bans in code, fixtures, and default copy:**

- No `LeadsForward`, `Trevor Eddy`, `trevor@leadsforward.com`, Fruitville Rd / Sarasota sample address from screenshots, or other real customer/staff identities from attached screenshots.

**Defaults (non-personalized):**

- Business: `Business Name`
- User: fake first/last (e.g. `Alex Rivera` or similar — not a real JCP staff name)
- Email: fake domain pattern derived from fake business slug, or a generic `alex@example.com`-style placeholder
- Locations / check-ins: generic contractor niche (plumbing, HVAC, handyman, etc.) with fake street/city data
- Review message: generic appreciation copy (fix capitalization; no brand names)

**Personalized (`/demo` survey params):**

- Keep current behavior: name, business, niche drive labels, sample jobs, and outcome previews.
- Personalized strings replace defaults only; never mix in banned screenshot identities.

**Check-in content:**

- Always personalized **or** generic contractor niche info.
- AI description text in fixtures should read like product AI output about the sample job photo — niche-generic, not tied to a real company.

## Interaction / data flow (in-prototype)

1. Home list is driven by in-memory check-in state (existing pattern).
2. New Check-in → camera/gallery mock → creates draft → Edit Check-In.
3. Publishing state is local enum: `published` | `not_published`; badge and sheet stay in sync; Save persists local edits.
4. Request Review reads editable message; Scan QR opens sheet; Send Link triggers mode-appropriate share stub.
5. Pending uploads: when Offline Mode is on (onboarding) or demo simulates a pending item for UX, banner + sheet reflect local queue.
6. Color Mode writes a theme class on the phone root (and optionally `localStorage` key scoped to prototype) so refresh in onboarding can keep preference; demo may reset on full reload if that keeps tour stable.

No server writes. No auth. No real QR that points at production customer review pages — use a stable fake URL or SVG QR for appearance only.

## Error handling and empty states

- Empty My Jobs / Team / Archive: keep friendly empty states (existing empty-state patterns, copy refreshed if needed).
- Soft-blocked demo actions: short toast, no console errors, no navigation away from `#jcp-app`.
- Missing personalization params on demo: fall back to generic defaults above (never banned identities).
- Web Share unavailable on onboarding: fall back to clipboard copy + toast.

## Testing / verification

1. `/onboarding`: walk every screen in the parity checklist; every primary control works or shows an intentional in-prototype result.
2. `/demo?mode=run` (desktop + mobile): complete guided tour end-to-end; soft limits fire correctly; personalization still applies.
3. Grep fixtures/markup/JS for banned strings (`LeadsForward`, `Trevor Eddy`, Fruitville, `leadsforward.com`).
4. Visual spot-check against screenshots for layout hierarchy (not pixel-perfect OS chrome).
5. Confirm `/prototype` still 301s to `/onboarding` and loads the same shell.

## Implementation notes

- Prefer updating existing screen IDs and functions where possible so tour hooks remain stable; when renaming is required, update tour selectors in the same change.
- `jcp-demo.js` is large — add new screen helpers near related sections; avoid unrelated refactors.
- Theme commit + push after implementation per theme always-push rule.
- Hub analytics events on `/onboarding` (`onboarding_page_viewed`, store clicks, `onboarding_preview_interacted`) stay unchanged unless new events are explicitly requested later.

## Success criteria

- Setup-session hosts can screen-share `/onboarding` and demonstrate the current app UI with full clicks.
- `/demo` still feels guided and cannot be broken by Log Out / location hopping / real share / admin deep links.
- No banned personal/brand strings appear in the shared prototype defaults.
- One edit to the shared shell updates both surfaces.
