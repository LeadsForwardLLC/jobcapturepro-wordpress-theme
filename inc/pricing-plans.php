<?php
/**
 * Canonical pricing plans — single source for /pricing/ and the sales tool.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plan catalog (monthly list price and display copy).
 *
 * @return array<string, array<string, mixed>>
 */
function jcp_pricing_plans(): array {
	return [
		'starter'    => [
			'id'                       => 'starter',
			'monthly'                  => 99,
			'yearly'                   => 79,
			'name'                     => 'Starter',
			'description'              => 'Everything a single-location business needs to turn check-ins into reviews.',
			'pill'                     => '1 location only',
			'allows_additional_locations' => false,
			'extra_location_fee'       => null,
			'features'                 => [
				[
					'text'    => '1 location only — cannot add more',
					'tooltip' => 'Starter is locked to a single operating location. Upgrade to Scale or Enterprise to add locations.',
				],
				[
					'text'    => 'Unlimited check-in tracking',
					'tooltip' => 'Track unlimited jobs/check-ins for your included location.',
				],
				[
					'text'    => 'On-site review requests',
					'tooltip' => 'After each job, your crew shows a QR code or sends a link so the customer can leave a review while the experience is fresh.',
				],
				[
					'text'    => 'Team activity feed',
					'tooltip' => 'See check-ins and activity across your team in one place.',
				],
				'Email support',
			],
			'includes'                 => [
				'1 location only (no add-ons)',
				'Technician-first mobile check-ins',
				'On-site QR review requests',
				'Geotagged, local-SEO website publishing',
				'Admin dashboard access',
			],
		],
		'scale'      => [
			'id'                       => 'scale',
			'monthly'                  => 249,
			'yearly'                   => 199,
			'name'                     => 'Scale',
			'description'              => 'Built for multi-location brands ready to grow without adding overhead.',
			'pill'                     => 'Most popular',
			'featured'                 => true,
			'allows_additional_locations' => true,
			'extra_location_fee'       => 150,
			'features'                 => [
				'Everything in Starter',
				'1 location included',
				[
					'text'    => 'Add locations anytime (+$150/mo each)',
					'tooltip' => 'Add more operating locations under one account at $150/month each.',
				],
				[
					'text'    => '1 Local Falcon keyword tracked',
					'tooltip' => 'Track one keyword in Local Falcon to see your Google Maps ranking and coverage radius.',
				],
				[
					'text'    => 'CRM integration',
					'tooltip' => 'Connect systems like Housecall Pro, Jobber, ServiceTitan, and CompanyCam.',
				],
				'WordPress plugin',
				'Social Media posting',
				'Google Business Profile posting',
				[
					'text'    => 'Advanced analytics',
					'tooltip' => 'Deeper reporting across check-ins, reviews, and performance by location.',
				],
				[
					'text'    => 'Priority support',
					'tooltip' => 'Faster responses and escalation for time-sensitive issues.',
				],
			],
			'includes'                 => [
				'1 location included · add more at $150/mo',
				'1 Local Falcon keyword (Maps ranking + radius)',
				'CRM integrations (Housecall Pro, Jobber, ServiceTitan, CompanyCam, and more)',
				'Geotagged images + local-SEO website content',
				'Google Maps / Business Profile posting',
				'On-site QR / link review requests',
			],
		],
		'enterprise' => [
			'id'                       => 'enterprise',
			'monthly'                  => 399,
			'yearly'                   => 319,
			'name'                     => 'Enterprise',
			'description'              => 'Dedicated support and custom connectivity for multi-location teams.',
			'pill'                     => 'Enterprise',
			'allows_additional_locations' => true,
			'extra_location_fee'       => 100,
			'features'                 => [
				'Everything in Scale',
				'1 location included',
				[
					'text'    => 'Add locations anytime (+$100/mo each)',
					'tooltip' => 'Add more operating locations under one account at $100/month each.',
				],
				[
					'text'    => 'Custom integrations',
					'tooltip' => 'Custom API integrations and tailored workflows for complex stacks.',
				],
				[
					'text'    => 'Dedicated account manager',
					'tooltip' => 'A single point of contact for rollout, strategy, and ongoing success.',
				],
				[
					'text'    => 'SLA guarantee',
					'tooltip' => 'Priority handling with service-level commitments for support/uptime.',
				],
			],
			'includes'                 => [
				'1 location included · add more at $100/mo',
				'Custom integrations and API access',
				'Geotagged local-SEO publish across markets',
				'Organization-wide reporting',
				'Dedicated onboarding',
			],
		],
	];
}

/**
 * Extra location fee for a plan (monthly), or null when not allowed.
 *
 * @param string $plan_id starter|scale|enterprise.
 */
function jcp_pricing_extra_location_fee( string $plan_id = 'scale' ): ?int {
	$plans = jcp_pricing_plans();
	$key   = sanitize_key( $plan_id );
	if ( empty( $plans[ $key ] ) ) {
		return null;
	}
	$fee = $plans[ $key ]['extra_location_fee'] ?? null;
	return $fee === null || $fee === '' ? null : (int) $fee;
}

/**
 * Map of plan id → extra location fee (null = not available).
 *
 * @return array<string, int|null>
 */
function jcp_pricing_extra_location_fees(): array {
	$out = [];
	foreach ( jcp_pricing_plans() as $id => $plan ) {
		$fee = $plan['extra_location_fee'] ?? null;
		$out[ (string) $id ] = ( $fee === null || $fee === '' ) ? null : (int) $fee;
	}
	return $out;
}

/**
 * Public pricing page URL.
 */
function jcp_pricing_page_url(): string {
	return home_url( '/pricing/' );
}

/**
 * Trial CTA label + URL for sales surfaces.
 *
 * @param string $utm_content UTM content.
 * @return array{label:string,url:string,note:string}
 */
function jcp_pricing_trial_cta( string $utm_content = 'sales_tool' ): array {
	$url = function_exists( 'jcp_core_onboarding_app_url_raw' )
		? jcp_core_onboarding_app_url_raw( jcp_core_onboarding_utm_defaults( $utm_content ) )
		: home_url( '/' );

	return [
		'label' => __( 'Start free 14-day trial', 'jcp-core' ),
		'url'   => $url,
		'note'  => __( 'No credit card required', 'jcp-core' ),
	];
}

/**
 * Payload for wp_localize_script on the pricing page.
 *
 * @return array<string, mixed>
 */
function jcp_pricing_localize_payload(): array {
	return [
		'plans'              => jcp_pricing_plans(),
		'extraLocationFee'   => jcp_pricing_extra_location_fee( 'scale' ), // legacy default (Scale).
		'extraLocationFees'  => jcp_pricing_extra_location_fees(),
		'pricingUrl'         => jcp_pricing_page_url(),
		'trial'              => jcp_pricing_trial_cta( 'pricing' ),
	];
}
