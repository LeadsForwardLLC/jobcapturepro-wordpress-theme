<?php
/**
 * Sales tool front-end config payload.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default customer review snippets for the proof chapter.
 *
 * @return list<array{name:string,role:string,quote:string,rating?:int}>
 */
function jcp_sales_tool_default_reviews(): array {
	return [
		[
			'name'   => 'Trent Ellison',
			'role'   => 'Home service operator',
			'quote'  => 'Easy to use and really smart. Makes it super simple to turn completed work into useful online content — and the review side is amazing.',
			'rating' => 5,
		],
		[
			'name'   => 'Brian Hardy',
			'role'   => 'Contractor',
			'quote'  => 'Awesome — it takes my work site pictures and turns them into a marketing campaign.',
			'rating' => 5,
		],
		[
			'name'   => 'Heriberto Eddie Roman',
			'role'   => 'Business owner',
			'quote'  => 'JobCapturePro has been a game changer for my business!',
			'rating' => 5,
		],
		[
			'name'   => 'Peter Bonk',
			'role'   => 'Marketing agency',
			'quote'  => 'One of the easiest marketing wins we\'ve had for an HVAC client. Techs already take photos — now those become GBP updates, website content, social posts, and an on-site review ask. The review flow alone has been worth it.',
			'rating' => 5,
		],
	];
}

/**
 * Map software keys to display labels (integration callouts).
 *
 * @param list<string> $keys Keys.
 * @return list<string>
 */
function jcp_sales_tool_software_labels( array $keys ): array {
	$opts = jcp_sales_tool_software_options();
	$out  = [];
	foreach ( $keys as $key ) {
		if ( isset( $opts[ $key ] ) ) {
			$out[] = $opts[ $key ];
		}
	}
	return $out;
}

/**
 * Highlighted field-software integrations for copy.
 *
 * @param list<string> $software Keys from settings.
 * @return list<string>
 */
function jcp_sales_tool_integration_callouts( array $software ): array {
	$priority = [
		'housecallpro' => 'Housecall Pro',
		'jobber'       => 'Jobber',
		'servicetitan' => 'ServiceTitan',
		'companycam'   => 'CompanyCam',
	];
	$hit = [];
	foreach ( $priority as $key => $label ) {
		if ( in_array( $key, $software, true ) ) {
			$hit[] = $label;
		}
	}
	if ( $hit === [] ) {
		return array_values( $priority );
	}
	return $hit;
}

/**
 * Build localize payload for a post (or empty live-call defaults).
 *
 * @param int $post_id Post ID (0 = blank defaults).
 * @return array<string, mixed>
 */
function jcp_sales_tool_build_config( int $post_id = 0 ): array {
	$settings = $post_id > 0 ? jcp_sales_tool_get_settings( $post_id ) : jcp_sales_tool_defaults();
	$trial    = function_exists( 'jcp_pricing_trial_cta' ) ? jcp_pricing_trial_cta( 'sales_tool' ) : [
		'label' => 'Start free 14-day trial',
		'url'   => home_url( '/' ),
		'note'  => 'No credit card required',
	];
	$plans    = function_exists( 'jcp_pricing_plans' ) ? jcp_pricing_plans() : [];
	$pricing_url = function_exists( 'jcp_pricing_page_url' ) ? jcp_pricing_page_url() : home_url( '/pricing/' );

	$logo_url = '';
	$logo_id  = absint( $settings['presenter_logo_id'] ?? 0 );
	if ( $logo_id > 0 ) {
		$logo_url = (string) wp_get_attachment_image_url( $logo_id, 'medium' );
	}

	$cta_label = trim( (string) ( $settings['cta_label'] ?? '' ) );
	$cta_url   = trim( (string) ( $settings['cta_url'] ?? '' ) );
	$sec_label = trim( (string) ( $settings['secondary_cta_label'] ?? '' ) );
	$sec_url   = trim( (string) ( $settings['secondary_cta_url'] ?? '' ) );

	$software = (array) ( $settings['software'] ?? [] );

	$asset_base = trailingslashit( get_template_directory_uri() ) . 'assets/jcp-sales-tool';

	$script_admin_url = '';
	if ( current_user_can( 'edit_pages' ) ) {
		$script_admin_url = admin_url( 'admin.php?page=jcp-sales-call-script' );
	}

	return [
		'assetBase'   => $asset_base,
		'pricingUrl'  => $pricing_url,
		'scriptAdminUrl' => $script_admin_url,
		'extraLocationFee' => function_exists( 'jcp_pricing_extra_location_fee' ) ? jcp_pricing_extra_location_fee() : 199,
		'plans'       => $plans,
		'trial'       => $trial,
		'presenter'   => [
			'type'    => (string) ( $settings['presenter_type'] ?? 'internal' ),
			'name'    => (string) ( $settings['presenter_name'] ?? '' ),
			'logoUrl' => $logo_url,
		],
		'prospect'    => [
			'company'         => (string) ( $settings['company'] ?? '' ),
			'trade'           => (string) ( $settings['trade'] ?? 'Home services' ),
			'mode'            => (string) ( $settings['mode'] ?? 'contractor' ),
			'jobsPerWeek'     => (int) ( $settings['jobs_per_week'] ?? 20 ),
			'locations'       => (int) ( $settings['locations'] ?? 1 ),
			'crewBand'        => (string) ( $settings['crew_band'] ?? '2-4' ),
			'software'        => $software,
			'softwareLabels'  => jcp_sales_tool_software_labels( $software ),
			'integrations'    => jcp_sales_tool_integration_callouts( $software ),
			'photoFrequency'  => (string) ( $settings['photo_frequency'] ?? 'most' ),
			'publishHabit'    => (string) ( $settings['publish_habit'] ?? 'occasionally' ),
			'reviewHabit'     => (string) ( $settings['review_habit'] ?? 'occasionally' ),
			'challenges'      => (array) ( $settings['challenges'] ?? [] ),
			'timeline'        => (string) ( $settings['timeline'] ?? '30_days' ),
			'captureRate'     => (int) ( $settings['capture_rate'] ?? 45 ),
			'publishRate'     => (int) ( $settings['publish_rate'] ?? 15 ),
		],
		'flags'       => [
			'showPricing'   => ! empty( $settings['show_pricing'] ),
			'showAcculevel' => ! empty( $settings['show_acculevel'] ),
		],
		'acculevelLeadLift' => (string) ( $settings['acculevel_lead_lift'] ?? '' ),
		'reviews'     => jcp_sales_tool_default_reviews(),
		'cta'         => [
			'primaryLabel'   => $cta_label !== '' ? $cta_label : (string) $trial['label'],
			'primaryUrl'     => $cta_url !== '' ? $cta_url : (string) $trial['url'],
			'primaryNote'    => (string) ( $trial['note'] ?? '' ),
			'secondaryLabel' => $sec_label !== '' ? $sec_label : __( 'See live pricing', 'jcp-core' ),
			'secondaryUrl'   => $sec_url !== '' ? $sec_url : $pricing_url,
		],
		'storageKey'  => $post_id > 0 ? 'jcp-sales-call-' . $post_id : 'jcp-sales-call-live',
	];
}
