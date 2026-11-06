# App Prototype UI Parity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bring the shared phone prototype (`/onboarding` + `/demo?mode=run`) into visual and interaction parity with the Nov 2026 native app screenshots, with full actions on onboarding and soft limits + tour on demo, using only fake or personalized contractor data.

**Architecture:** Evolve the existing shared shell (`assets/demo/index.html`, `assets/js/features/demo/jcp-demo.js`, `assets/shared/assets/demo.css`). Mode flags already gate demo vs prototype; extend soft-limit helpers and add missing screens/sheets. Do not fork markup for demo vs onboarding.

**Tech Stack:** Theme static HTML/CSS/vanilla JS phone shell; WordPress templates already enqueue the shell; Lucide SVG icons under `assets/shared/assets/icons/lucide/`.

**Spec:** `docs/superpowers/specs/2026-11-06-app-prototype-ui-parity-design.md`

## Global Constraints

- One shared phone source updates `/onboarding` and `/demo?mode=run`.
- `/onboarding`: all buttons/actions enabled (in-prototype only; no real backends).
- `/demo`: soft limits + guided tour preserved; survey personalization preserved.
- Never ship `LeadsForward`, `Trevor Eddy`, `trevor@…`, Fruitville/Sarasota screenshot address, or `leadsforward.com` inside the shared prototype defaults/fixtures (`assets/demo/**`, `jcp-demo.js` identity helpers). Marketing-site LeadsForward mentions outside the phone shell are out of scope.
- Default non-personalized identity: business `Business Name`, user `Alex Rivera`, email `alex@example.com`.
- Check-in content: personalized niche **or** generic contractor fixtures only.
- Primary accent ~`#FF5A4D` / existing demo coral-red; white cards; ~12–16px radii.
- Commit after each task; push when the feature is complete (theme always-push rule).

## File map

| File | Responsibility |
|------|----------------|
| `assets/demo/index.html` | Screen/sheet markup for home, new, edit, review, profile, color mode, notifications, publishing, pending uploads, QR |
| `assets/js/features/demo/jcp-demo.js` | Navigation, state, soft limits, tour selectors, personalization, publishing/offline/pending helpers |
| `assets/shared/assets/demo.css` | Visual parity styles for new and restyled screens |
| `page-onboarding.php` / `page-demo.php` | Touch only if mode flags or enqueue need a tweak (prefer no change) |
| Spec (reference) | `docs/superpowers/specs/2026-11-06-app-prototype-ui-parity-design.md` |

**Canonical helpers to extend (existing):** `isDemoMode`, `isPrototype`, `isOnboardingHub()`, `showDemoRestrictionTooltip()`, `applyPrototypeAppLabels()`, `getOrgSwitcherLabel()`, `updateProfilePersonalization()`, `goToHome` / `goToNew` / `goToProfile` / `goToRequestReview`, `openLocationSheet`, `saveCheckin`.

**New helpers this plan introduces:**

```js
const DEFAULT_DEMO_IDENTITY = {
  firstName: 'Alex',
  lastName: 'Rivera',
  businessName: 'Business Name',
  email: 'alex@example.com',
  niche: 'plumbing',
  roleLabel: 'Field Tech',
};

function softBlockDemoAction(el, message);
function getPublishingState(); // 'published' | 'not_published'
function setPublishingState(state);
function openPublishingSheet();
function closePublishingSheet();
function openPendingUploadsSheet();
function closePendingUploadsSheet();
function openScanReviewSheet();
function closeScanReviewSheet();
function goToColorMode();
function goToNotifications();
function applyColorMode(mode); // 'system' | 'light' | 'dark'
function sendReviewLink(); // mode-aware share/copy/toast
function prototypeLogOut();
```

---

### Task 1: Strip banned identities + lock generic defaults

**Files:**
- Modify: `assets/demo/index.html` (org label, profile card placeholders)
- Modify: `assets/js/features/demo/jcp-demo.js` (`demoUser` defaults, `getOrgSwitcherLabel`, `applyPrototypeAppLabels`, onboarding hub init block ~L647–656)

**Interfaces:**
- Consumes: existing `demoUser`, `isOnboardingHub()`, `isPrototype`
- Produces: `DEFAULT_DEMO_IDENTITY` constant; all non-personalized paths resolve to Alex Rivera / Business Name / alex@example.com

- [ ] **Step 1: Add identity constant near top of `jcp-demo.js` and wire defaults**

Replace the initial `demoUser` and onboarding hub assignment so defaults never use banned strings:

```js
const DEFAULT_DEMO_IDENTITY = {
  firstName: 'Alex',
  lastName: 'Rivera',
  businessName: 'Business Name',
  email: 'alex@example.com',
  niche: 'plumbing',
  roleLabel: 'Field Tech',
};

let demoUser = {
  firstName: DEFAULT_DEMO_IDENTITY.firstName,
  lastName: DEFAULT_DEMO_IDENTITY.lastName,
  businessName: DEFAULT_DEMO_IDENTITY.businessName,
  niche: DEFAULT_DEMO_IDENTITY.niche,
  email: DEFAULT_DEMO_IDENTITY.email,
};
```

In the `JCP_IS_ONBOARDING_HUB` init branch and in `applyPrototypeAppLabels()`, set the same identity (replace `Company Name` / `User` / `company@gmail.com` and delete the `LeadsForward` fallback in `getOrgSwitcherLabel` / `applyPrototypeAppLabels`).

- [ ] **Step 2: Update HTML placeholders**

In `assets/demo/index.html`:
- Org switcher label: `Business Name` (not LeadsForward)
- `#profile-name`: `Alex Rivera`
- `#profile-email`: `alex@example.com`

- [ ] **Step 3: Verify banned strings are gone from the phone shell**

Run:

```bash
rg -n "LeadsForward|Trevor Eddy|trevor@|Fruitville|leadsforward\\.com|Sarasota" \
  assets/demo/index.html assets/js/features/demo/jcp-demo.js
```

Expected: **no matches** in those two files (spec bans apply to the phone shell only).

- [ ] **Step 4: Commit**

```bash
git add assets/demo/index.html assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Use generic fake identity defaults in the phone prototype shell.

EOF
)"
```

---

### Task 2: Home Check-Ins visual parity + pending-upload banner shell

**Files:**
- Modify: `assets/demo/index.html` (`#home-screen`)
- Modify: `assets/shared/assets/demo.css` (home header, tabs, banner, list cards)
- Modify: `assets/js/features/demo/jcp-demo.js` (`renderCheckinCard`, `renderHomeCheckins`; stub banner click → `openPendingUploadsSheet` no-op until Task 7)

**Interfaces:**
- Consumes: existing check-in list state
- Produces: home markup matching screenshot hierarchy: title “Check-Ins” + help; org pill with name + address line; segmented My Jobs/Team/Archive; optional `#pending-uploads-banner`; search + map FAB; cards with thumb + author + relative time

- [ ] **Step 1: Restructure `#home-screen` header in `index.html`**

Match screenshot structure (keep existing IDs for tabs/search/map/list/FAB where possible):

```html
<div class="app-header app-header--home">
  <h1 class="home-title">Check-Ins</h1>
  <button type="button" class="home-help-btn" id="btnHomeHelp" aria-label="Help">…</button>
</div>
<button type="button" class="org-switcher location-switcher" id="location-switcher" …>
  <span class="org-switcher__mark">…</span>
  <span class="org-switcher__text">
    <span class="location-switcher__label org-switcher__label">Business Name</span>
    <span class="org-switcher__address" id="org-switcher-address">1242 Mason Rd, Austin, TX 78704</span>
  </span>
  <img class="location-switcher__chevron" …>
</button>
<!-- existing action-tiles / home-toolbar / list -->
<button type="button" class="pending-uploads-banner" id="pending-uploads-banner" hidden>
  <span class="pending-uploads-banner__icon">…</span>
  <span class="pending-uploads-banner__copy">
    <strong id="pending-uploads-banner-title">1 check-in waiting to upload</strong>
    <small>Saved on this device · Tap to review.</small>
  </span>
  <span class="pending-uploads-banner__badge" id="pending-uploads-banner-count">1</span>
</button>
```

Keep `#tab-my-jobs`, `#tab-team`, `#tab-archived`, `#home-search-input`, `#btnHomeMap`, `#home-checkin-list`, `#fabNewCheckin`, `#profile-btn`.

- [ ] **Step 2: Style home + list cards in `demo.css`**

Add/adjust rules for `.home-title`, `.org-switcher` (pill with address), segmented tabs (active coral fill), `.pending-uploads-banner`, `.home-checkin-item` (title/subtitle/desc/thumb/footer author·time in coral). Reuse existing BEM where present; do not introduce purple themes.

- [ ] **Step 3: Update `renderCheckinCard` footer**

Ensure card shows author from `demoUser` (or check-in author) and relative time; truncate description. Wire `#pending-uploads-banner` click to `openPendingUploadsSheet` (define as empty function stub if Task 7 not done yet):

```js
function openPendingUploadsSheet() { /* Task 7 */ }
```

- [ ] **Step 4: Manual check**

Open `/onboarding` (or local phone shell). Confirm home hierarchy matches screenshots without LeadsForward.

- [ ] **Step 5: Commit**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Restyle Check-Ins home to match the updated app shell.

EOF
)"
```

---

### Task 3: New Check-in screen parity

**Files:**
- Modify: `assets/demo/index.html` (`#new-screen`)
- Modify: `assets/shared/assets/demo.css`
- Modify: `assets/js/features/demo/jcp-demo.js` only if location card labels need CURRENT LOCATION copy

**Interfaces:**
- Consumes: existing `#new-location-card`, `#uploadBtnCamera`, `#uploadBtnGallery`, camera overlay
- Produces: screenshot layout — Location card + two large circular coral camera/gallery buttons; remove or hide CRM integrations note on prototype (already hidden via `applyPrototypeAppLabels`)

- [ ] **Step 1: Update `#new-screen` markup**

```html
<div class="section-title new-screen-location-title">Location</div>
<div class="location-card location-card--new" id="new-location-card">
  … CURRENT LOCATION · street · city …
</div>
<div class="new-media-actions">
  <button type="button" class="new-media-btn" id="uploadBtnCamera" data-action="add-photos" aria-label="Take a photo">…camera…</button>
  <button type="button" class="new-media-btn" id="uploadBtnGallery" data-action="add-photos" aria-label="Choose from gallery">…image…</button>
</div>
```

Keep `#submit-btn` flow working (show after photos as today, or auto-advance to edit — preserve existing create pipeline).

- [ ] **Step 2: CSS for `.new-media-actions` / `.new-media-btn`**

Large circular coral buttons (~72–88px), white icons, centered in content area.

- [ ] **Step 3: Smoke-test create path on `/onboarding`**

Camera or gallery → photos → create/edit still works.

- [ ] **Step 4: Commit**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Align New Check-in media actions with the updated app UI.

EOF
)"
```

---

### Task 4: Edit Check-In + Publishing sheet

**Files:**
- Modify: `assets/demo/index.html` (`#edit-screen` + new publishing sheet)
- Modify: `assets/shared/assets/demo.css`
- Modify: `assets/js/features/demo/jcp-demo.js` (`showEditScreen`, `saveCheckin`, new publishing helpers; retarget SEO spotlight from “Publish Everywhere” to publishing control + Request Review)

**Interfaces:**
- Produces: `getPublishingState()`, `setPublishingState(state)`, `openPublishingSheet()`, `closePublishingSheet()`
- Edit chrome: back, title, `#btnEditSave` “Save”; `#publish-status-badge`; author · relative time; location + date rows; photos; AI description + Regenerate; tags; primary `#btnRequestReview` “Request Review”; `#btnEditMore` overflow; Archive moved to overflow or secondary as in screenshots

- [ ] **Step 1: Replace edit action bar markup**

Remove primary “Publish Everywhere” as the main CTA. Add:

```html
<div class="edit-meta-row">
  <button type="button" class="publish-status-badge" id="publish-status-badge" data-state="not_published">Not Published</button>
  <span class="edit-meta-author" id="edit-meta-author">Alex Rivera · a moment ago</span>
</div>
…
<div class="edit-footer-actions">
  <button type="button" class="btn btn-primary" id="btnRequestReview" onclick="goToRequestReview()">… Request Review</button>
  <button type="button" class="btn-icon" id="btnEditMore" aria-label="More">…</button>
</div>
<button type="button" class="btn btn-secondary btn-archive-float" id="btnArchiveCheckin">… Archive Check-In</button>
```

Keep `#btnSavePublish` only if tour still needs a publish trigger — prefer renaming to open publishing sheet or keep a Save in header that persists local edits without the old “Publish Everywhere” label.

- [ ] **Step 2: Add Publishing bottom sheet markup**

```html
<div class="location-sheet-overlay" id="publishing-sheet-overlay" aria-hidden="true">
  <div class="location-sheet" id="publishing-sheet" role="dialog" aria-labelledby="publishing-sheet-title">
    <h2 id="publishing-sheet-title">Publishing</h2>
    <button type="button" class="publish-option" data-publish-state="published">Published — Shows on your website…</button>
    <button type="button" class="publish-option is-selected" data-publish-state="not_published">Not published — Stays internal…</button>
    <div class="publish-ai-card">AI auto-publishing can set this for you… Turn it on or off on the admin dashboard.</div>
    <button type="button" class="btn btn-secondary btn-full" id="publishing-sheet-close">Close</button>
  </div>
</div>
```

- [ ] **Step 3: Implement publishing state in JS**

```js
let publishingState = 'not_published';

function getPublishingState() { return publishingState; }

function setPublishingState(state) {
  publishingState = state === 'published' ? 'published' : 'not_published';
  const badge = document.getElementById('publish-status-badge');
  if (badge) {
    badge.dataset.state = publishingState;
    badge.textContent = publishingState === 'published' ? 'Published' : 'Not Published';
  }
  document.querySelectorAll('.publish-option').forEach((btn) => {
    btn.classList.toggle('is-selected', btn.dataset.publishState === publishingState);
  });
}

function openPublishingSheet() {
  const overlay = document.getElementById('publishing-sheet-overlay');
  if (!overlay) return;
  overlay.classList.add('is-open');
  overlay.setAttribute('aria-hidden', 'false');
}

function closePublishingSheet() {
  const overlay = document.getElementById('publishing-sheet-overlay');
  if (!overlay) return;
  overlay.classList.remove('is-open');
  overlay.setAttribute('aria-hidden', 'true');
}
```

Wire badge click → `openPublishingSheet`. Option click → `setPublishingState` + close. AI card: informational only (no external nav). Demo `saveCheckin` / outcomes flow must still run when the tour expects “publish” — map the guided demo’s publish beat to selecting Published **or** keep an internal `saveCheckin()` call from the tour spotlight target (`#btnSavePublish` can remain hidden in DOM for tour hooks if needed; prefer updating tour selectors in Task 8).

- [ ] **Step 4: Style edit + publishing sheet**

Match screenshot spacing, AI pill, dashed Add photo, coral Request Review footer.

- [ ] **Step 5: Commit**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Add Publishing sheet and restyle Edit Check-In actions.

EOF
)"
```

---

### Task 5: Request Review — message, Scan QR sheet, Send Link

**Files:**
- Modify: `assets/demo/index.html` (`#review-request-options-screen`, optional simplify `#request-review-screen`, add Scan & Review sheet)
- Modify: `assets/shared/assets/demo.css`
- Modify: `assets/js/features/demo/jcp-demo.js` (`goToRequestReview`, `goToReviewRequestOptions`, new `openScanReviewSheet`, `sendReviewLink`)

**Interfaces:**
- Produces: `openScanReviewSheet()`, `closeScanReviewSheet()`, `sendReviewLink()`
- QR payload: fake URL only, e.g. `https://jobcapturepro.com/demo-review` (appearance QR; not a real customer page)

- [ ] **Step 1: Align Request Review options screen with screenshots**

Single primary screen: subtitle, `INCLUDE A MESSAGE` label, textarea, **Scan QR** (secondary) + **Send Link** (primary). Give buttons stable IDs:

```html
<button type="button" class="btn btn-secondary btn-full" id="btnScanReviewQr">Scan QR</button>
<button type="button" class="btn btn-primary btn-full" id="btnSendReviewLink">Send Link</button>
```

- [ ] **Step 2: Add Scan & Review sheet**

Bottom sheet: title “Scan & Review”, sub “Customer can scan to access review page.”, large QR image (static SVG or canvas-generated from fake URL).

- [ ] **Step 3: Implement `sendReviewLink`**

```js
async function sendReviewLink() {
  const messageEl = document.querySelector('#review-request-options-screen .request-review-message, #request-review-message');
  const text = (messageEl && messageEl.value) || 'We loved working with you! If you have a moment to leave us a review, it would be greatly appreciated.';
  const url = 'https://jobcapturepro.com/demo-review';
  const payload = `${text}\n\n${url}`;

  if (isDemoMode) {
    showDemoRestrictionTooltip(document.getElementById('btnSendReviewLink'), 'Link ready — sharing is simulated in the demo');
    return;
  }
  if (navigator.share) {
    try {
      await navigator.share({ text: payload });
      return;
    } catch (e) { /* user cancelled or failed */ }
  }
  try {
    await navigator.clipboard.writeText(payload);
    showDemoRestrictionTooltip(document.getElementById('btnSendReviewLink'), 'Review link copied');
  } catch (e) {
    showDemoRestrictionTooltip(document.getElementById('btnSendReviewLink'), 'Copy this message to send your review link');
  }
}
```

Wire Scan QR → `openScanReviewSheet`. Preserve demo review-success / outcomes hooks that currently fire after request-review where the tour depends on them (call existing success path after simulated send if required).

- [ ] **Step 4: Commit**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Update Request Review with QR sheet and mode-aware Send Link.

EOF
)"
```

---

### Task 6: Profile, Color Mode, Notifications

**Files:**
- Modify: `assets/demo/index.html` (rebuild `#profile-screen`; add `#color-mode-screen`, `#notifications-screen`)
- Modify: `assets/shared/assets/demo.css`
- Modify: `assets/js/features/demo/jcp-demo.js` (`goToProfile`, `goToColorMode`, `goToNotifications`, `applyColorMode`, `prototypeLogOut`, soft blocks)

**Interfaces:**
- Produces: `goToColorMode()`, `goToNotifications()`, `applyColorMode(mode)`, `prototypeLogOut()`
- Color mode storage key: `jcpPrototypeColorMode` (`system`|`light`|`dark`)

- [ ] **Step 1: Rebuild profile markup**

Match screenshots:
- Title left + bell button (`#btnProfileNotifications` → `goToNotifications`)
- Gradient identity card: name, email, role badge (`DEFAULT_DEMO_IDENTITY.roleLabel` or `SUPER ADMIN`-style fake badge text **Admin** — do not use real staff names)
- ACCOUNT card: Color Mode (value + navigate), App Info (chevron; simple alert/sheet with version “Prototype”), Location row (Business Name + fake address)
- PREFERENCES: Notifications toggle (can mirror push pref), Offline Mode toggle `#toggleOfflineMode`
- Log Out `#btnLogOut` coral full width
- Remove Delete Account from primary profile (or move behind overflow); Change Password / Edit Profile optional secondary — prefer App Info over old Edit Profile for parity, keep edit-profile screen reachable from identity card chevron on onboarding

- [ ] **Step 2: Add Color Mode + Notifications screens**

Color Mode: System / Light / Dark rows + checkmark on selected.  
Notifications: PREFERENCES Push + Email toggles; RECENT list with three fake contractor items (no LeadsForward/Maria Lopez from screenshots — use generic names like “Jordan Lee joined Business Name” only when personalized business exists).

- [ ] **Step 3: Wire JS**

```js
function applyColorMode(mode) {
  const root = document.querySelector('.phone-wrapper') || document.getElementById('jcp-app');
  const resolved = mode === 'system'
    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : mode;
  if (root) root.dataset.colorMode = resolved;
  try { localStorage.setItem('jcpPrototypeColorMode', mode); } catch (e) {}
  document.querySelectorAll('.color-mode-option').forEach((row) => {
    row.classList.toggle('is-selected', row.dataset.colorMode === mode);
  });
  const valueEl = document.getElementById('profile-color-mode-value');
  if (valueEl) valueEl.textContent = mode.charAt(0).toUpperCase() + mode.slice(1);
}

function prototypeLogOut() {
  if (isDemoMode) {
    softBlockDemoAction(document.getElementById('btnLogOut'), 'Not available in the demo');
    return;
  }
  goToHome();
  showDemoRestrictionTooltip(document.getElementById('fabNewCheckin'), 'Signed out of prototype session');
}

function softBlockDemoAction(el, message) {
  showDemoRestrictionTooltip(el, message || 'This action is disabled in the demo');
}
```

Offline toggle: onboarding toggles local `offlineMode` flag and can show pending banner (Task 7); demo soft-blocks with toast.

- [ ] **Step 4: Commit**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Rebuild Profile with Color Mode and Notifications screens.

EOF
)"
```

---

### Task 7: Pending uploads sheet + offline simulation

**Files:**
- Modify: `assets/demo/index.html` (pending uploads sheet)
- Modify: `assets/shared/assets/demo.css`
- Modify: `assets/js/features/demo/jcp-demo.js` (`openPendingUploadsSheet`, offline flag, banner visibility)

**Interfaces:**
- Consumes: `#pending-uploads-banner`, offline toggle
- Produces: working sheet with at least one fake pending item when offline (onboarding) or when demo intentionally shows the banner for UX without breaking tour

- [ ] **Step 1: Add pending uploads sheet markup**

Title “Pending uploads.” Subtitle “Stored on this device until you go back online.” List row: thumb, address, “1 photo · Just now”, Pending pill, upload + trash icons.

- [ ] **Step 2: Implement open/close + banner sync**

```js
let offlineMode = false;
let pendingUploads = [];

function syncPendingUploadsBanner() {
  const banner = document.getElementById('pending-uploads-banner');
  if (!banner) return;
  const count = pendingUploads.length;
  banner.hidden = count === 0;
  const title = document.getElementById('pending-uploads-banner-title');
  const badge = document.getElementById('pending-uploads-banner-count');
  if (title) title.textContent = count === 1 ? '1 check-in waiting to upload' : `${count} check-ins waiting to upload`;
  if (badge) badge.textContent = String(count);
}

function openPendingUploadsSheet() {
  /* render list; open overlay */
}
```

Onboarding: enabling Offline Mode pushes a generic pending fixture if empty. Demo: do not auto-enter offline mid-tour; allow viewing sheet only if banner was shown by intentional fixture without deleting tour-critical check-ins (soft-block trash).

- [ ] **Step 3: Commit**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Add pending uploads sheet and offline banner simulation.

EOF
)"
```

---

### Task 8: Soft demo limits + tour retarget + verification

**Files:**
- Modify: `assets/js/features/demo/jcp-demo.js` (demo disable pass, tour selectors/copy, SEO spotlight targets)
- Modify: `assets/shared/assets/demo.css` (`.is-demo-disabled` already exists — ensure new controls covered)
- Touch tour DOM in `assets/demo/index.html` only if step labels live there (`#mobileStepBody`, etc.)

**Interfaces:**
- Consumes: all new controls from Tasks 2–7
- Produces: demo soft-limit matrix from the spec; tour completes end-to-end

- [ ] **Step 1: Centralize demo soft blocks on init**

Ensure these call `softBlockDemoAction` / `is-demo-disabled` in demo mode:
- `#btnLogOut`
- `#toggleOfflineMode`
- location sheet items (already partially disabled)
- `#tab-team`, `#tab-archived` (visible + soft-disabled unless tour opens them)
- publishing AI card external implication (no navigation)
- pending upload trash if it would break tour state

Do **not** soft-block: create check-in, edit fields, Request Review, Scan QR sheet open, Send Link simulated toast, Color Mode (allow), Notifications toggles (local only).

- [ ] **Step 2: Retarget guided tour**

Search `jcp-demo.js` for selectors/labels referencing `Publish Everywhere`, `Preview Review Request`, old profile controls. Update to `#publish-status-badge` / publishing sheet / `#btnRequestReview` “Request Review”. Re-run mobile stepper copy if it says outdated button names.

- [ ] **Step 3: Full verification**

```bash
rg -n "LeadsForward|Trevor Eddy|trevor@|Fruitville|leadsforward\\.com" \
  assets/demo/index.html assets/js/features/demo/jcp-demo.js
```

Expected: no matches.

Manual:
1. `/onboarding` — walk home → new → edit → publishing → request review (QR + share/copy) → profile → color mode → notifications → offline → pending sheet.
2. `/demo?mode=run` — complete guided tour; confirm soft toasts on Log Out / location switch / offline; personalization still applies when URL params present.
3. Spot-check visuals against screenshots (hierarchy, not OS chrome).

- [ ] **Step 4: Commit + push**

```bash
git add assets/demo/index.html assets/shared/assets/demo.css assets/js/features/demo/jcp-demo.js
git commit -m "$(cat <<'EOF'
Retarget demo tour and enforce soft limits for new app UI.

EOF
)"
git push origin HEAD
```

---

## Spec coverage checklist

| Spec requirement | Task |
|------------------|------|
| Shared shell architecture | All (file map) |
| Home Check-Ins parity | 2 |
| Switch location soft limits | 1/8 (existing sheet + soft block) |
| New Check-in | 3 |
| Edit Check-In | 4 |
| Publishing sheet | 4 |
| Request Review + QR + Send Link | 5 |
| Profile | 6 |
| Color Mode | 6 |
| Notifications | 6 |
| Pending uploads | 7 |
| Soft demo limits | 8 |
| Identity bans + defaults | 1 |
| Tour preserved/retargeted | 8 |
| Verification grep + walks | 8 |

## Self-review notes

- No TBD placeholders in task steps.
- Helper names are consistent across tasks (`openPendingUploadsSheet`, `softBlockDemoAction`, `DEFAULT_DEMO_IDENTITY`).
- Tour publish beat explicitly handled in Tasks 4 and 8 to avoid breaking `/demo`.
- Marketing-site LeadsForward copy outside the phone shell intentionally untouched.
