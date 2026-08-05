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
        [ 'label' => 'Less marketing busywork', 'value' => 'Less marketing busywork' ],
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
            'label'   => 'Core Trades',
            'options' => [
                [ 'value' => 'plumbing', 'label' => 'Plumbing' ],
                [ 'value' => 'hvac', 'label' => 'HVAC / Heating & Cooling' ],
                [ 'value' => 'electrical', 'label' => 'Electrical' ],
                [ 'value' => 'septic-sewer', 'label' => 'Septic & Sewer' ],
                [ 'value' => 'water-heaters', 'label' => 'Water Heaters' ],
                [ 'value' => 'water-treatment', 'label' => 'Water Treatment' ],
                [ 'value' => 'generators', 'label' => 'Generators' ],
            ],
        ],
        [
            'label'   => 'Exterior & Roofing',
            'options' => [
                [ 'value' => 'roofing', 'label' => 'Roofing' ],
                [ 'value' => 'siding-exterior', 'label' => 'Siding & Exterior' ],
                [ 'value' => 'windows-doors', 'label' => 'Windows & Doors' ],
                [ 'value' => 'gutters', 'label' => 'Gutters' ],
                [ 'value' => 'insulation', 'label' => 'Insulation' ],
                [ 'value' => 'masonry', 'label' => 'Masonry' ],
                [ 'value' => 'stucco', 'label' => 'Stucco' ],
                [ 'value' => 'chimney-services', 'label' => 'Chimney Services' ],
            ],
        ],
        [
            'label'   => 'Remodeling & Construction',
            'options' => [
                [ 'value' => 'general-contracting', 'label' => 'General Contracting' ],
                [ 'value' => 'remodeling', 'label' => 'Remodeling' ],
                [ 'value' => 'kitchen-remodeling', 'label' => 'Kitchen Remodeling' ],
                [ 'value' => 'bathroom-remodeling', 'label' => 'Bathroom Remodeling' ],
                [ 'value' => 'basement-finishing', 'label' => 'Basement Finishing' ],
                [ 'value' => 'home-additions', 'label' => 'Home Additions' ],
                [ 'value' => 'handyman', 'label' => 'Handyman Services' ],
                [ 'value' => 'drywall-plaster', 'label' => 'Drywall & Plaster' ],
                [ 'value' => 'carpentry-trim', 'label' => 'Carpentry & Trim' ],
                [ 'value' => 'cabinets-countertops', 'label' => 'Cabinets & Countertops' ],
                [ 'value' => 'flooring', 'label' => 'Flooring' ],
                [ 'value' => 'tile', 'label' => 'Tile' ],
                [ 'value' => 'painting', 'label' => 'Painting' ],
            ],
        ],
        [
            'label'   => 'Outdoor & Property Services',
            'options' => [
                [ 'value' => 'landscaping', 'label' => 'Landscaping' ],
                [ 'value' => 'lawn-care', 'label' => 'Lawn Care' ],
                [ 'value' => 'tree-service', 'label' => 'Tree Service' ],
                [ 'value' => 'irrigation', 'label' => 'Irrigation' ],
                [ 'value' => 'hardscaping', 'label' => 'Hardscaping' ],
                [ 'value' => 'decks-patios', 'label' => 'Decks & Patios' ],
                [ 'value' => 'fencing', 'label' => 'Fencing' ],
                [ 'value' => 'snow-removal', 'label' => 'Snow Removal' ],
                [ 'value' => 'pool-service', 'label' => 'Pool Service' ],
                [ 'value' => 'pool-construction', 'label' => 'Pool Construction' ],
                [ 'value' => 'hot-tub-spa', 'label' => 'Hot Tub & Spa Service' ],
                [ 'value' => 'outdoor-living', 'label' => 'Outdoor Living' ],
            ],
        ],
        [
            'label'   => 'Cleaning & Maintenance',
            'options' => [
                [ 'value' => 'house-cleaning', 'label' => 'House Cleaning' ],
                [ 'value' => 'carpet-cleaning', 'label' => 'Carpet Cleaning' ],
                [ 'value' => 'pressure-washing', 'label' => 'Pressure Washing' ],
                [ 'value' => 'window-cleaning', 'label' => 'Window Cleaning' ],
                [ 'value' => 'junk-removal', 'label' => 'Junk Removal' ],
                [ 'value' => 'dumpster-rental', 'label' => 'Dumpster Rental' ],
                [ 'value' => 'moving-services', 'label' => 'Moving Services' ],
                [ 'value' => 'furniture-assembly', 'label' => 'Furniture Assembly' ],
            ],
        ],
        [
            'label'   => 'Repair & Specialty Services',
            'options' => [
                [ 'value' => 'appliance-repair', 'label' => 'Appliance Repair' ],
                [ 'value' => 'garage-doors', 'label' => 'Garage Doors' ],
                [ 'value' => 'locksmith', 'label' => 'Locksmith' ],
                [ 'value' => 'pest-control', 'label' => 'Pest Control' ],
                [ 'value' => 'termite-control', 'label' => 'Termite Control' ],
                [ 'value' => 'wildlife-removal', 'label' => 'Wildlife Removal' ],
            ],
        ],
        [
            'label'   => 'Restoration & Structural',
            'options' => [
                [ 'value' => 'mold-remediation', 'label' => 'Mold Remediation' ],
                [ 'value' => 'water-damage-restoration', 'label' => 'Water Damage Restoration' ],
                [ 'value' => 'fire-damage-restoration', 'label' => 'Fire Damage Restoration' ],
                [ 'value' => 'foundation-repair', 'label' => 'Foundation Repair' ],
                [ 'value' => 'basement-waterproofing', 'label' => 'Basement Waterproofing' ],
                [ 'value' => 'concrete', 'label' => 'Concrete' ],
                [ 'value' => 'asphalt-paving', 'label' => 'Asphalt & Paving' ],
            ],
        ],
        [
            'label'   => 'Inspection, Energy & Security',
            'options' => [
                [ 'value' => 'home-inspection', 'label' => 'Home Inspection' ],
                [ 'value' => 'solar-installation', 'label' => 'Solar Installation' ],
                [ 'value' => 'ev-charger-installation', 'label' => 'EV Charger Installation' ],
                [ 'value' => 'home-security', 'label' => 'Home Security' ],
                [ 'value' => 'smart-home-installation', 'label' => 'Smart Home Installation' ],
            ],
        ],
        [
            'label'   => 'Other',
            'options' => [
                [ 'value' => 'other', 'label' => 'Other Home Service' ],
            ],
        ],
    ];
}

/**
 * Business type select value when the user enters a custom trade.
 */
function jcp_core_business_type_other_value(): string {
    return 'other';
}

/**
 * Echo <option> and <optgroup> markup for business type selects.
 *
 * @param bool $include_placeholder Whether to include the empty placeholder option.
 */
function jcp_core_render_business_type_select_options( bool $include_placeholder = true ): void {
    if ( $include_placeholder ) {
        echo '<option value="">' . esc_html__( 'Select your business type', 'jcp-core' ) . '</option>';
    }
    foreach ( jcp_core_early_access_default_business_type_options() as $group ) {
        $group_label = isset( $group['label'] ) ? (string) $group['label'] : '';
        $options     = isset( $group['options'] ) && is_array( $group['options'] ) ? $group['options'] : [];
        if ( $group_label === '' || $options === [] ) {
            continue;
        }
        echo '<optgroup label="' . esc_attr( $group_label ) . '">';
        foreach ( $options as $opt ) {
            if ( empty( $opt['value'] ) || ! isset( $opt['label'] ) ) {
                continue;
            }
            echo '<option value="' . esc_attr( (string) $opt['value'] ) . '">' . esc_html( (string) $opt['label'] ) . '</option>';
        }
        echo '</optgroup>';
    }
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
