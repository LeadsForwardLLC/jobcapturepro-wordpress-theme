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
| `signup_completed` ≠ `trial_started` | Account created ≠ Stripe trial provisioned. |
| `trial_started` | Fires **only** after Stripe confirms a qualifying free trial (`customer.subscription.created` → marketing webhook). |
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
- **Meaning:** Successful real trial provisioning — a Stripe subscription exists with `status=trialing` for the JCP Scale free trial.
- **Source of truth (marketing-owned, 2026-09-27+):** Stripe webhook `customer.subscription.created` handled by `jobcapturepro.com` (`/wp-json/jcp/v1/stripe-trial-webhook`).
- **Qualification (all required):**
  - `event.type = customer.subscription.created`
  - `subscription.status = trialing`
  - `metadata.lookupKey = scale_monthly` **or** `items.data[0].price.lookup_key = scale_monthly`
  - `metadata.organizationId` present
  - not `additional_location_monthly`
- **Do not treat as trial start:** `customer.subscription.updated`, paid `active` without trial, CTA clicks, or account creation alone.
- **PostHog:** Server-side `trial_started` to project `593169`. Prefer matched lead `ph_distinct_id` as `distinct_id`.
- **Meta:** Server CAPI **`StartTrial`** (Pixel `1440845294314184`).
- **Shared `event_id` / `$insert_id`:** `jcp_trial_<stripe_subscription_id>` (one subscription = one conversion).
- **Attribution join:** normalized Stripe customer email → durable marketing lead email (newest lead with `created_at <= subscription.created`). Unmatched trials still emit with `attribution_status=unmatched`.
- **App client event:** Not required. App `signup_completed` remains a separate account-created signal.
- **GHL:** Optional future tag; not required for Meta/PostHog primary.
- **Primary conversion:** **YES**.

### Explicit non-equivalences
| Signal | Equals `trial_started`? |
|--------|-------------------------|
| `signup_completed` | **No** — account created; trial not guaranteed |
| `trial_cta_clicked` / `proof_sprint_cta_clicked` | **No** — intent only |
| Meta Lead / `proof_gap_email_submitted` | **No** — lead / diagnostic |

---

## Identity & attribution continuity

| Field | Role |
|-------|------|
| PostHog `distinct_id` | Sticky marketing-site id (`jcp_ph_id` cookie / localStorage). |
| `ph_distinct_id` | Persisted on durable marketing lead (`attribution_json`) and used as PostHog `distinct_id` for server `trial_started`. Also passed marketing → app via URL for optional bootstrap. |
| `survey_session_id` | Unique Proof Gap survey UUID (not the shared onboarding `sessionId`). |
| `qa_trace_id` | First-touch QA / campaign trace; persisted in client attribution + durable lead `attribution_json`. |
| UTMs / `fbclid` / `_fbp` / `_fbc` / `landing_page` / `referrer` / `first_touch_timestamp` | First-touch attribution (`jcp-attribution.js`). Cookies and `ph_distinct_id` are POST-only to the lead REST endpoint (not GHL, not public URLs). |

---

## SUCCESSFUL STRIPE TRIAL (primary conversion)

| Item | Value |
|------|-------|
| Source of truth | Stripe `customer.subscription.created` |
| Conditions | `status=trialing` + `scale_monthly` + `organizationId` present + not `additional_location_monthly` |
| Marketing endpoint | `POST https://jobcapturepro.com/wp-json/jcp/v1/stripe-trial-webhook` |
| PostHog | `trial_started` |
| Meta | `StartTrial` (CAPI) |
| `event_id` | `jcp_trial_<stripe_subscription_id>` |
| Primary conversion | **YES** |

---

## Onboarding URL `sessionId`

`jcp_core_onboarding_hardcoded_session_id()` supplies a **shared bootstrap UUID** on marketing→app links. Server requires `sessionId` **or** `stripeSessionId` to be present; it is **not** a per-user auth/tenant secret. See launch report for NON-BLOCKING classification.

---

## Document history

- Created for paid acquisition launch handoff (Proof Gap + Proof Sprint).
- 2026-09-27: Marketing-owned Stripe webhook is the authoritative `trial_started` / Meta `StartTrial` path (no app code required).
- Update this file when Meta CAPI mappings or Stripe qualification rules change.
