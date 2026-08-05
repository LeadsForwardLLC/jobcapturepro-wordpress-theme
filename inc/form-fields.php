<?php
/**
 * Canonical form field names for GHL webhook payloads
 *
 * Demo Survey is the source of truth. Both Early Access and Demo Survey use these
 * REST param names and GHL payload keys for shared concepts so GHL workflows
 * receive consistent data.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GHL payload keys (human-readable keys sent to GoHighLevel webhooks).
 * Use these when building application/x-www-form-urlencoded bodies.
 */
define( 'JCP_GHL_KEY_FIRST_NAME', 'First Name' );
define( 'JCP_GHL_KEY_LAST_NAME', 'Last Name' );
define( 'JCP_GHL_KEY_EMAIL', 'Email' );
define( 'JCP_GHL_KEY_PHONE', 'Phone' );
define( 'JCP_GHL_KEY_COMPANY', 'Company' );
define( 'JCP_GHL_KEY_BUSINESS_TYPE', 'Business Type' );
define( 'JCP_GHL_KEY_USE_CASE', 'Use Case' );
define( 'JCP_GHL_KEY_SERVICE_AREA', 'Service Area' );
define( 'JCP_GHL_KEY_REFERRAL_SOURCE', 'Referral Source' );
define( 'JCP_GHL_KEY_UTM_SOURCE', 'UTM Source' );
define( 'JCP_GHL_KEY_UTM_MEDIUM', 'UTM Medium' );
define( 'JCP_GHL_KEY_UTM_CAMPAIGN', 'UTM Campaign' );
define( 'JCP_GHL_KEY_UTM_CONTENT', 'UTM Content' );
define( 'JCP_GHL_KEY_LANDING_PAGE', 'Landing Page' );
define( 'JCP_GHL_KEY_REFERRER', 'Referrer' );
define( 'JCP_GHL_KEY_EVENT', 'Event' );
define( 'JCP_GHL_KEY_TOPIC', 'Topic' );
define( 'JCP_GHL_KEY_MESSAGE', 'Message' );
define( 'JCP_GHL_KEY_ATTACHMENT', 'Attachment' );

/**
 * REST request param names (snake_case, used in JSON body from frontend).
 * Use these when registering REST args and reading request params.
 * Both forms collect first_name and last_name separately; GHL keys "First Name" and "Last Name" receive those values.
 */
define( 'JCP_REST_PARAM_FIRST_NAME', 'first_name' );
define( 'JCP_REST_PARAM_LAST_NAME', 'last_name' );
define( 'JCP_REST_PARAM_EMAIL', 'email' );
define( 'JCP_REST_PARAM_PHONE', 'phone' );
define( 'JCP_REST_PARAM_COMPANY', 'company' );
define( 'JCP_REST_PARAM_BUSINESS_TYPE', 'business_type' );
define( 'JCP_REST_PARAM_DEMO_GOALS', 'demo_goals' );
define( 'JCP_REST_PARAM_SERVICE_AREA', 'service_area' );
define( 'JCP_REST_PARAM_REFERRAL_SOURCE', 'referral_source' );
define( 'JCP_REST_PARAM_UTM_SOURCE', 'utm_source' );
define( 'JCP_REST_PARAM_UTM_MEDIUM', 'utm_medium' );
define( 'JCP_REST_PARAM_UTM_CAMPAIGN', 'utm_campaign' );
define( 'JCP_REST_PARAM_UTM_CONTENT', 'utm_content' );
define( 'JCP_REST_PARAM_LANDING_PAGE', 'landing_page' );
define( 'JCP_REST_PARAM_REFERRER', 'referrer' );
