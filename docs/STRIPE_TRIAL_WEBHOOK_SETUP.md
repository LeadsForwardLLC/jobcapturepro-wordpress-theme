# Stripe trial webhook — ops setup

Marketing endpoint (after theme deploy):

```text
https://jobcapturepro.com/wp-json/jcp/v1/stripe-trial-webhook
```

## wp-config.php / hosting secrets (do not commit)

```php
define( 'JCP_STRIPE_WEBHOOK_SECRET', 'whsec_...' );      // from Stripe Dashboard endpoint
define( 'JCP_STRIPE_SECRET_KEY', 'sk_live_...' );        // same Stripe account as app; customer retrieve only
define( 'JCP_META_CAPI_ACCESS_TOKEN', 'EAA...' );        // Meta system user token for Pixel CAPI
// optional:
// define( 'JCP_META_PIXEL_ID', '1440845294314184' );
// define( 'JCP_META_CAPI_TEST_EVENT_CODE', 'TEST12345' ); // QA only — remove for production
// define( 'JCP_POSTHOG_PROJECT_API_KEY', 'phc_...' );     // defaults to existing project key
define( 'JCP_POSTHOG_SIGNUP_BRIDGE_SECRET', '…' );     // shared secret for PostHog CDP → WP
define( 'JCP_POSTHOG_PROJECT_ID', '593169' );          // JobCapturePro Default project
// Optional last-resort HogQL only (NOT the primary race path):
// define( 'JCP_POSTHOG_HOGQL_FALLBACK', true );
// define( 'JCP_POSTHOG_PERSONAL_API_KEY', 'phx_...' );
```

Environment-variable equivalents with the same names are also accepted.

## Architecture (event-driven)

1. Stripe `customer.subscription.created` → durable conversion row immediately.
2. **Meta `StartTrial`** fires immediately (Stripe-authoritative, `event_id = jcp_trial_<subscription_id>`). PostHog enrichment failure must never suppress Meta.
3. **PostHog `trial_started`** waits for `signup_completed` CDP push → `/posthog-signup-bridge` → email match wake.
4. If CDP wake fails within **120s**, finalize PostHog as unmatched (exactly once). Bounded WP-Cron safety net only — no HogQL polling loop / shutdown sleeps.

Matching hierarchy on bridge wake:

1. Normalized email (unique) → pending conversion row
2. Lead attribution when email already known on marketing site (Proof Gap)
3. Local signup bridge (`ph_distinct_id`, UTMs, `qa_trace_id` from onboarding URL / CDP fields)

## Proof Sprint attribution bridge

Proof Sprint does not collect email on the marketing site. After Stripe creates the trial:

1. Webhook records the conversion + sends Meta immediately.
2. Email lead join runs (Proof Gap path when a lead exists).
3. PostHog CDP destination posts `signup_completed` to `/wp-json/jcp/v1/posthog-signup-bridge`.
4. Bridge stores attribution and **wakes** any pending conversion for that email → one matched `trial_started`.

**PostHog CDP destination** (Data pipelines → Destinations → HTTP Webhook):

- Filter: event `signup_completed`
- URL: `https://jobcapturepro.com/index.php?rest_route=/jcp/v1/posthog-signup-bridge` (PostHog CDN IPs reach this path reliably; `/wp-json/...` was intermittently SiteGround-captcha'd; bare `.php` returned 403)
- Header: `X-JCP-Bridge-Secret: <same as JCP_POSTHOG_SIGNUP_BRIDGE_SECRET>`
- Body JSON includes email, `current_url`, `distinct_id`, `ph_distinct_id`, UTMs, `qa_trace_id`, `lp_variant`, `funnel_surface`, `source`
- Hog must treat `position()` as **1-based** (0 = not found) and accept JSON object `body.received == true` (not only a string search for `"received":true`)

**SiteGround Anti-Bot:** Prefer the `index.php?rest_route=/jcp/v1/posthog-signup-bridge` URL for CDP. Optionally exclude `/wp-json/jcp/v1/*` from Anti-Bot AI.

## Stripe Dashboard steps

1. Open the **same live Stripe account** used by JobCapturePro billing.
2. Developers → Webhooks → **Add endpoint**.
3. Endpoint URL: `https://jobcapturepro.com/wp-json/jcp/v1/stripe-trial-webhook`
4. Listen to events: **`customer.subscription.created` only** (for this marketing endpoint).
5. Create endpoint → reveal **Signing secret** (`whsec_…`) → set `JCP_STRIPE_WEBHOOK_SECRET`.
6. Ensure `JCP_STRIPE_SECRET_KEY` can read Customers in that account.
7. Send a test event or complete one QA signup; confirm 2xx in Stripe delivery log.

## Privacy note (client IP / UA)

Marketing leads do **not** persist raw client IP or User-Agent for Meta CAPI. Funnel analytics only stores hashed IPs. No sitewide consent gate was found that would authorize retaining raw IP/UA on the lead for Match Quality. Meta receives hashed email + `_fbp`/`_fbc` when present on the lead.

## GHL

No GHL automation changes in this release. Future optional: on conversion row `attribution_status=matched`, tag contact “trial-started”.
