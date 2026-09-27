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
```

Environment-variable equivalents with the same names are also accepted.

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
