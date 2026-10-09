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
define( 'JCP_GHL_KEY_UTM_TERM', 'UTM Term' );
define( 'JCP_GHL_KEY_FBCLID', 'Facebook Click ID' );
define( 'JCP_GHL_KEY_LANDING_PAGE', 'Landing Page' );
define( 'JCP_GHL_KEY_LP_VARIANT', 'LP Variant' );
define( 'JCP_GHL_KEY_FUNNEL_SURFACE', 'Funnel Surface' );
define( 'JCP_GHL_KEY_REFERRER', 'Referrer' );
define( 'JCP_GHL_KEY_CONTACT_ID', 'contactId' );
define( 'JCP_GHL_KEY_EVENT_ID', 'Event Id' );
define( 'JCP_GHL_KEY_EVENT', 'Event' );
define( 'JCP_GHL_KEY_TOPIC', 'Topic' );
define( 'JCP_GHL_KEY_MESSAGE', 'Message' );
define( 'JCP_GHL_KEY_ATTACHMENT', 'Attachment' );
/**
 * Proof Gap → GHL field names (dedicated intake webhook only).
 *
 * Canonical machine keys (preferred for new GHL mappings) + legacy Title Case
 * aliases that the currently published inbound webhook expects.
 * Do not change demo Business Type / Use Case keys used by Demo Survey.
 */
define( 'JCP_GHL_KEY_BUSINESS_NICHE', 'Business Niche' );
define( 'JCP_GHL_KEY_WEEKLY_JOB_VOLUME', 'Weekly Job Volume' );
define( 'JCP_GHL_KEY_ASSESSMENT_NOTES', 'Assessment Notes' );
define( 'JCP_GHL_KEY_PHOTO_WORKFLOW', 'Photo Workflow' );
define( 'JCP_GHL_KEY_MARKETING_USAGE', 'Marketing Usage' );
define( 'JCP_GHL_KEY_SURVEY_SESSION_ID', 'Survey Session Id' );
define( 'JCP_GHL_KEY_QA_TRACE_ID', 'qa_trace_id' );
/** Legacy inbound-webhook key the published GHL workflow maps for Q3 (was wrongly aliased to Weekly Job Volume). */
define( 'JCP_GHL_KEY_JOBS_PER_WEEK', 'Jobs Per Week' );
/** Canonical snake_case Proof Gap survey keys. */
define( 'JCP_GHL_KEY_CANONICAL_BUSINESS_NICHE', 'business_niche' );
define( 'JCP_GHL_KEY_CANONICAL_WEEKLY_JOB_VOLUME', 'weekly_job_volume' );
define( 'JCP_GHL_KEY_CANONICAL_PHOTO_WORKFLOW', 'photo_workflow' );
define( 'JCP_GHL_KEY_CANONICAL_MARKETING_USAGE', 'marketing_usage' );
define( 'JCP_GHL_KEY_CANONICAL_SURVEY_SESSION_ID', 'survey_session_id' );
define( 'JCP_GHL_KEY_ESTIMATED_JOBS_PER_YEAR', 'estimated_jobs_per_year' );
define( 'JCP_GHL_KEY_POTENTIALLY_UNUSED_PERCENT', 'potentially_unused_percent' );
define( 'JCP_GHL_KEY_PH_DISTINCT_ID', 'ph_distinct_id' );
define( 'JCP_GHL_KEY_FUNNEL_VERSION', 'funnel_version' );
define( 'JCP_GHL_KEY_JCP_PG_VARIANT', 'jcp_pg_variant' );
define( 'JCP_GHL_KEY_SURVEY_VERSION', 'survey_version' );
define( 'JCP_GHL_KEY_IS_QA', 'is_qa' );
define( 'JCP_GHL_KEY_UTM_ID', 'utm_id' );

/** Canonical snake_case contact / attribution aliases (sent alongside Title Case legacy keys). */
define( 'JCP_GHL_KEY_CANONICAL_FIRST_NAME', 'first_name' );
define( 'JCP_GHL_KEY_CANONICAL_LAST_NAME', 'last_name' );
define( 'JCP_GHL_KEY_CANONICAL_EMAIL', 'email' );
define( 'JCP_GHL_KEY_CANONICAL_PHONE', 'phone' );
define( 'JCP_GHL_KEY_CANONICAL_COMPANY', 'company' );
define( 'JCP_GHL_KEY_CANONICAL_UTM_SOURCE', 'utm_source' );
define( 'JCP_GHL_KEY_CANONICAL_UTM_MEDIUM', 'utm_medium' );
define( 'JCP_GHL_KEY_CANONICAL_UTM_CAMPAIGN', 'utm_campaign' );
define( 'JCP_GHL_KEY_CANONICAL_UTM_CONTENT', 'utm_content' );
define( 'JCP_GHL_KEY_CANONICAL_UTM_TERM', 'utm_term' );
define( 'JCP_GHL_KEY_CANONICAL_LANDING_PAGE', 'landing_page' );
define( 'JCP_GHL_KEY_CANONICAL_REFERRER', 'referrer' );
define( 'JCP_GHL_KEY_CANONICAL_LP_VARIANT', 'lp_variant' );
define( 'JCP_GHL_KEY_CANONICAL_ASSESSMENT_NOTES', 'assessment_notes' );

/** Demo gate funnel / survey version identifiers (stable machine values). */
if ( ! defined( 'JCP_DEMO_FUNNEL_VERSION' ) ) {
	define( 'JCP_DEMO_FUNNEL_VERSION', 'demo_survey_v1' );
}
if ( ! defined( 'JCP_DEMO_SURVEY_VERSION' ) ) {
	define( 'JCP_DEMO_SURVEY_VERSION', '1' );
}

/**
 * Whether a lead request is QA/test traffic (must not fire production GHL webhooks).
 *
 * True when is_qa is truthy, jcp_qa=1, qa_trace_id is present, or utm_source=qa.
 *
 * @param array<string, mixed> $params Merged contact + attribution params.
 */
function jcp_ghl_request_is_qa( array $params ): bool {
	if ( ! empty( $params['is_qa'] ) ) {
		$raw = $params['is_qa'];
		if ( $raw === true || $raw === 1 || $raw === '1' || $raw === 'true' ) {
			return true;
		}
	}
	if ( ! empty( $params['jcp_qa'] ) && (string) $params['jcp_qa'] === '1' ) {
		return true;
	}
	$qa = isset( $params['qa_trace_id'] ) ? trim( (string) $params['qa_trace_id'] ) : '';
	if ( $qa !== '' ) {
		return true;
	}
	$utm = isset( $params['utm_source'] ) ? strtolower( trim( (string) $params['utm_source'] ) ) : '';
	return $utm === 'qa';
}

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
define( 'JCP_REST_PARAM_UTM_TERM', 'utm_term' );
define( 'JCP_REST_PARAM_FBCLID', 'fbclid' );
define( 'JCP_REST_PARAM_LANDING_PAGE', 'landing_page' );
define( 'JCP_REST_PARAM_LP_VARIANT', 'lp_variant' );
define( 'JCP_REST_PARAM_FUNNEL_SURFACE', 'funnel_surface' );
define( 'JCP_REST_PARAM_REFERRER', 'referrer' );
define( 'JCP_REST_PARAM_CONTACT_ID', 'contact_id' );
define( 'JCP_REST_PARAM_EVENT_ID', 'event_id' );
