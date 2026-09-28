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
// Required for Proof Sprint matched attribution (no marketing email gate):
define( 'JCP_POSTHOG_PERSONAL_API_KEY', 'phx_...' );   // PostHog → Settings → Personal API keys (query scope)
define( 'JCP_POSTHOG_PROJECT_ID', '593169' );          // JobCapturePro Default project
```

Environment-variable equivalents with the same names are also accepted.

## Proof Sprint attribution bridge

Proof Sprint does not collect email on the marketing site. After Stripe creates the trial:

1. Webhook records the conversion immediately (authoritative).
2. Email lead join runs first (Proof Gap path).
3. If unmatched, HogQL looks up `signup_completed` for that email and parses `$current_url` for `ph_distinct_id`, UTMs, `lp_variant`, `qa_trace_id`.
4. Side effects (`trial_started` + Meta `StartTrial`) are deferred up to ~5 minutes while that bridge resolves — **one** emission per subscription (`event_id` / `$insert_id` = `jcp_trial_<subscription_id>`).

Requires `JCP_POSTHOG_PERSONAL_API_KEY` with Query access on project `593169`.

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
