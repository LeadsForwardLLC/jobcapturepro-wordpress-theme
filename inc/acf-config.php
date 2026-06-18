<?php
/**
 * ACF Configuration
 * Per-page bottom CTA.
 *
 * @package JCP_Core
 */

// Only run if ACF is active
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
}

/**
 * Register ACF field groups (per-page bottom CTA).
 */
function jcp_core_register_acf_field_groups() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    // Per-page bottom CTA (Pages only)
    acf_add_local_field_group(
        [
            'key'      => 'jcp_page_cta',
            'title'    => 'Bottom CTA',
            'fields'   => [
                [
                    'key'         => 'enable_page_cta',
                    'label'       => 'Enable bottom CTA',
                    'name'        => 'enable_page_cta',
                    'type'        => 'true_false',
                    'default'     => 0,
                    'ui'          => 1,
                ],
                [
                    'key'               => 'page_cta_headline',
                    'label'             => 'CTA Headline',
                    'name'              => 'page_cta_headline',
                    'type'              => 'text',
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'enable_page_cta',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'key'               => 'page_cta_supporting_text',
                    'label'             => 'Supporting Text',
                    'name'              => 'page_cta_supporting_text',
                    'type'              => 'textarea',
                    'rows'              => 2,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'enable_page_cta',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'key'               => 'page_cta_button_label',
                    'label'             => 'Button Label',
                    'name'              => 'page_cta_button_label',
                    'type'              => 'text',
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'enable_page_cta',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'key'               => 'page_cta_button_url',
                    'label'             => 'Button URL',
                    'name'              => 'page_cta_button_url',
                    'type'              => 'url',
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'enable_page_cta',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'page',
                    ],
                    [
                        'param'    => 'page_template',
                        'operator' => '==',
                        'value'    => 'default',
                    ],
                ],
            ],
            'menu_order' => 5,
        ]
    );

}

add_action( 'acf/init', 'jcp_core_register_acf_field_groups' );

/**
 * Default "Why are you interested?" options for Early Access form (checkboxes).
 * Matches "What should this demo prove?" options on the Demo Survey.
 *
 * @return array List of [ 'label' => string, 'value' => string ]
 */
function jcp_core_early_access_default_why_interested_options(): array {
    return [
        [ 'label' => 'More inbound calls', 'value' => 'More inbound calls' ],
        [ 'label' => 'Better Google visibility', 'value' => 'Better Google visibility' ],
        [ 'label' => 'More customer reviews', 'value' => 'More customer reviews' ],
        [ 'label' => 'Stronger website trust', 'value' => 'Stronger website trust' ],
        [ 'label' => 'Less marketing busywork', 'value' => 'Less marketing busywork' ],
        [ 'label' => 'Showcase my work', 'value' => 'Showcase my work' ],
    ];
}

/**
 * Default "How did you hear about us?" options for Early Access form (exact labels for GHL).
 *
 * @return array List of [ 'label' => string, 'value' => string ]
 */
function jcp_core_early_access_default_referral_options(): array {
    $labels = [
        'Google Search',
        'Google Maps',
        'Facebook / Instagram',
        'Referral (another contractor)',
        'YouTube / Video',
        'Podcast',
        'Industry Event',
        'Other',
    ];
    $out = [];
    foreach ( $labels as $label ) {
        $out[] = [ 'label' => $label, 'value' => $label ];
    }
    return $out;
}

/**
 * Default "Business type" options for Early Access form (same as Demo Survey Step 1).
 * Grouped for optgroup rendering.
 *
 * @return array List of [ 'label' => string (optgroup), 'options' => [ [ 'value' => string, 'label' => string ], ... ] ]
 */
function jcp_core_early_access_default_business_type_options(): array {
    return [
        [
            'label'   => 'Building & mechanical',
            'options' => [
                [ 'value' => 'plumbing', 'label' => 'Plumbing' ],
                [ 'value' => 'hvac', 'label' => 'HVAC' ],
                [ 'value' => 'electrical', 'label' => 'Electrical' ],
                [ 'value' => 'roofing', 'label' => 'Roofing' ],
            ],
        ],
        [
            'label'   => 'General contracting & remodeling',
            'options' => [
                [ 'value' => 'general-contractor', 'label' => 'General Contractor' ],
                [ 'value' => 'handyman', 'label' => 'Handyman' ],
                [ 'value' => 'remodeling', 'label' => 'Remodeling / Renovation' ],
            ],
        ],
        [
            'label'   => 'Outdoor & property',
            'options' => [
                [ 'value' => 'landscaping', 'label' => 'Landscaping' ],
                [ 'value' => 'lawn-care', 'label' => 'Lawn care' ],
                [ 'value' => 'tree-service', 'label' => 'Tree service' ],
                [ 'value' => 'pest-control', 'label' => 'Pest control' ],
                [ 'value' => 'fencing', 'label' => 'Fencing' ],
            ],
        ],
        [
            'label'   => 'Cleaning & restoration',
            'options' => [
                [ 'value' => 'carpet-cleaning', 'label' => 'Carpet cleaning' ],
                [ 'value' => 'house-cleaning', 'label' => 'House cleaning' ],
                [ 'value' => 'pressure-washing', 'label' => 'Pressure washing' ],
                [ 'value' => 'painting', 'label' => 'Painting (interior / exterior)' ],
            ],
        ],
        [
            'label'   => 'Other trades',
            'options' => [
                [ 'value' => 'flooring', 'label' => 'Flooring' ],
                [ 'value' => 'windows-doors', 'label' => 'Windows & doors' ],
                [ 'value' => 'insulation', 'label' => 'Insulation' ],
                [ 'value' => 'garage-doors', 'label' => 'Garage doors' ],
                [ 'value' => 'pool-service', 'label' => 'Pool service' ],
                [ 'value' => 'moving-junk', 'label' => 'Moving / Junk removal' ],
            ],
        ],
        [
            'label'   => 'Other',
            'options' => [
                [ 'value' => 'other', 'label' => 'Other home service' ],
            ],
        ],
    ];
}

/**
 * Resolve business type value to display label for GHL Trade field.
 *
 * @param string $value Business type value (e.g. plumbing).
 * @return string Label (e.g. Plumbing) or value if not found.
 */
function jcp_core_early_access_business_type_label( string $value ): string {
    $value = trim( $value );
    if ( $value === '' ) {
        return '';
    }
    foreach ( jcp_core_early_access_default_business_type_options() as $group ) {
        foreach ( $group['options'] as $opt ) {
            if ( isset( $opt['value'] ) && (string) $opt['value'] === $value && isset( $opt['label'] ) ) {
                return (string) $opt['label'];
            }
        }
    }
    return $value;
}

/**
 * Get Early Access form config for frontend (hardcoded defaults).
 *
 * @return array why_interested_options, referral_options, business_type_options, require_phone, require_company, success_redirect, success_message, headline, subhead, button_label (+ rest_url set by enqueue)
 */
function jcp_core_get_early_access_form_config(): array {
    return [
        'why_interested_options'  => jcp_core_early_access_default_why_interested_options(),
        'referral_options'        => jcp_core_early_access_default_referral_options(),
        'business_type_options'   => jcp_core_early_access_default_business_type_options(),
        'require_phone'           => true,
        'require_company'         => true,
        'success_redirect'        => home_url( '/early-access-success/' ),
        'success_message'         => "Thanks for signing up. We'll be in touch soon with early-bird pricing and next steps.",
        'headline'                => 'Early Access',
        'subhead'                 => "You're early. That's a good thing. Get access before public launch with early-bird pricing and help shape the platform as it grows.",
        'button_label'            => 'Join Early Access',
    ];
}
