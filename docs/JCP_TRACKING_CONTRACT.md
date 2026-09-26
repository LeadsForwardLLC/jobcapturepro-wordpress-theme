# JobCapturePro Tracking Contract

Canonical event meanings for paid acquisition (`/proof-gap/`, `/proof-sprint/`) and trial provisioning (`app.jobcapturepro.com`).

**PostHog project:** JobCapturePro / Default (`593169`)  
**Marketing capture path:** `assets/js/core/jcp-posthog.js` → PostHog Capture API (no GTM mirror)  
**App capture path:** `posthog-js` on `app.jobcapturepro.com`

---

## Absolute rules

| Rule | Meaning |
|------|---------|
| `trial_cta_clicked` ≠ `trial_started` | CTA click is intent only. |
| `trial_started` | Fires **only** after successful backend trial provisioning. |
| `signup_completed` | Fires after successful `POST /api/onboarding` + Firebase Auth sign-in (client). Closest live “account created” signal today. |
| Do not fire Meta primary conversion on CTA click | Lead (email) and trial conversion are separate. |

---

## Proof Gap funnel events

### `proof_gap_viewed`
- **Meaning:** Visitor landed on `/proof-gap/` (survey shell ready).
- **When:** Once per page load / session init.
- **PostHog:** Yes (canonical).
- **Meta:** None (diagnostic).
- **GHL:** None.
- **Primary conversion:** No.

### `proof_gap_started`
- **Meaning:** Visitor began the diagnostic (left welcome / first engagement).
- **When:** First progression into the survey.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `proof_gap_answered`
- **Meaning:** One survey question answered.
- **When:** After each of the four questions (trade, workflow, jobs/week, marketing-use %). Expected ×4 per completed survey.
- **Properties:** `question`, `answer` (and related step fields).
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None (answers are later packed into lead payload).
- **Primary conversion:** No.

### `proof_gap_completed`
- **Meaning:** All required survey answers collected; result can be shown.
- **When:** Immediately after the fourth answer.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `proof_gap_email_submitted`
- **Meaning:** Work email captured and durable lead persist succeeded (`/wp-json/jcp/v1/proof-gap-survey-submit`).
- **When:** After successful REST response (`captured: true`).
- **PostHog:** Yes.
- **Meta equivalent:** Browser/GTM **Lead** via `dataLayer` event `demo_opt_in` (`lead_type` / `source` = `proof_gap_survey`) **only after** CRM capture success. Shared `event_id` / `eventID` for browser↔CAPI dedup when GTM/CAPI are mapped.
- **GHL:** Event `proof-gap-survey`; tags `proof-gap-survey`, `proof_gap_survey_v1`; UTMs + Business Type + Use Case (survey summary) + Event Id.
- **Primary conversion:** No (lead / diagnostic). Not the trial primary.

### `proof_gap_transformation_viewed`
- **Meaning:** Visitor saw the job→channels transformation / app sim.
- **When:** Entering the transform / app_sim state after email.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `proof_gap_preview_clicked`
- **Meaning:** Visitor opened a channel preview tab/card (website / Google / social / reviews / directory).
- **When:** Optional interaction on transform / trial bridge.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

---

## Proof Sprint landing events

### `proof_sprint_viewed`
- **Meaning:** Visitor landed on `/proof-sprint/`.
- **When:** Once per page init.
- **PostHog:** Yes (`source=proof_sprint`).
- **Meta:** None (page view only via existing GTM `$pageview` / PaidLandingView if mapped).
- **GHL:** None.
- **Primary conversion:** No.

### `proof_sprint_output_viewed`
- **Meaning:** Visitor viewed a One Job → Five Outputs channel panel.
- **When:** Section intersection (first channel) or tab change.
- **Properties:** `channel`.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `proof_sprint_case_study_viewed`
- **Meaning:** Visitor expanded the full Local Falcon case study.
- **When:** `<details>` open / toggle.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `proof_sprint_faq_opened`
- **Meaning:** Visitor opened an FAQ item.
- **When:** FAQ `<details>` open.
- **Properties:** `question`.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `proof_sprint_cta_clicked`
- **Meaning:** Visitor clicked a Proof Sprint trial CTA (placement-aware companion to `trial_cta_clicked`).
- **When:** Click on `[data-ps-trial]`.
- **Properties:** `placement`, `destination`, `source=proof_sprint`.
- **PostHog:** Yes.
- **Meta:** Diagnostic only — **not** primary trial conversion.
- **GHL:** None.
- **Primary conversion:** No.

---

## Shared trial funnel events

### `trial_cta_viewed`
- **Meaning:** A trial CTA was visible in viewport.
- **When:** IntersectionObserver (~50% visible), once per `placement`.
- **Properties:** `source` (`proof_gap` | `proof_sprint`), `placement`.
- **PostHog:** Yes.
- **Meta:** None.
- **GHL:** None.
- **Primary conversion:** No.

### `trial_cta_clicked`
- **Meaning:** Visitor clicked Start Trial / equivalent and is navigating to onboarding.
- **When:** Click handler on trial CTA (before or as navigation starts).
- **Properties:** `source`, `placement`, destination URL (may include attribution).
- **PostHog:** Yes.
- **Meta:** Must **not** be mapped as the successful-trial primary conversion.
- **GHL:** None.
- **Primary conversion:** No.

### `signup_completed`
- **Meaning:** App onboarding succeeded: Auth user created, org created, client signed in with custom token.
- **When:** Client `posthog.capture('signup_completed')` immediately after successful `POST /api/onboarding` + `signInWithCustomToken` (JCP-API `useOnboardingForm.ts`).
- **PostHog:** App project (same 593169 when configured).
- **Meta:** Not emitted by app code directly; any Meta mapping must be via GTM/CAPI listening to this event or a dedicated server event — **verify in GTM/Adspirer separately**.
- **GHL:** Not from this event by default.
- **Primary conversion:** **Candidate** for “account created,” but not the named `trial_started` contract.

### `trial_started`
- **Meaning:** Successful real trial provisioning (Stripe no-card 14-day Scale trial attached to org).
- **When:** **Intended** immediately after `provisionTrialSubscription` succeeds inside `POST /api/onboarding`.
- **Current production status (2026-09-26):** **Not present in live app taxonomy.** JCP-API PR #133 added it then was **reverted** (`00597cf`). Live app still only emits `signup_completed`.
- **PostHog:** Should be yes when re-shipped.
- **Meta:** Intended primary conversion event for paid acquisition (browser and/or CAPI) — **must share one `event_id` if both fire**.
- **GHL:** Optional downstream tag; not required for Meta primary.
- **Primary conversion:** **Yes** (once live).

---

## Identity & attribution continuity

| Field | Role |
|-------|------|
| PostHog `distinct_id` | Sticky marketing-site id (`jcp_ph_id` cookie / localStorage). |
| `ph_distinct_id` URL param | Passed marketing → app for identity bootstrap (**app bootstrap from #133 not live**). |
| `survey_session_id` | Unique Proof Gap survey UUID (not the shared onboarding `sessionId`). |
| `qa_trace_id` | First-touch QA / campaign trace; persisted in attribution store. |
| UTMs / `fbclid` / `_fbp` / `_fbc` / `landing_page` / `referrer` / `first_touch_timestamp` | First-touch attribution (`jcp-attribution.js`). |

---

## Onboarding URL `sessionId`

`jcp_core_onboarding_hardcoded_session_id()` supplies a **shared bootstrap UUID** on marketing→app links. Server requires `sessionId` **or** `stripeSessionId` to be present; it is **not** a per-user auth/tenant secret. See launch report for NON-BLOCKING classification.

---

## Document history

- Created for paid acquisition launch handoff (Proof Gap + Proof Sprint).
- Update this file when `trial_started` ships on the app or Meta CAPI mappings change.
