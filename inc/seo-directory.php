<?php
/**
 * SEO for Directory and Contractor Profile Pages
 * Meta descriptions, profile-specific titles, and Schema.org JSON-LD
 * to help directory and profile pages rank (e.g. vs HomeAdvisor-style sites).
 *
 * @package JCP_Core
 */

/**
 * Register SEO filters and schema for directory/company routes.
 * Runs on template_redirect after routing so jcp_route is set.
 *
 * @return void
 */
function jcp_core_seo_directory_profile(): void {
    $route = get_query_var( 'jcp_route', '' );
    if ( $route !== 'directory' && $route !== 'company' ) {
        return;
    }

    $slug = get_query_var( 'jcp_company_slug', '' );
    if ( $slug === '' && isset( $_GET['id'] ) && is_string( $_GET['id'] ) ) {
        $slug = sanitize_text_field( wp_unslash( $_GET['id'] ) );
    }

    $site_name = get_bloginfo( 'name' );
    $directory_url = home_url( '/directory/' );

    if ( $route === 'directory' ) {
        // Meta description: keyword-rich, value proposition vs lead-gen directories.
        add_filter(
            'rank_math/frontend/description',
            function ( $description ) use ( $directory_url ) {
                return __(
                    'Find verified contractors with real job proof. Browse roofing, plumbing, HVAC, and electrical contractors—ranked by recent activity and trust. No pay-to-play; proof from real jobs.',
                    'jcp-core'
                );
            },
            10,
            1
        );
        // Schema: WebPage + ItemList so Google understands this is a directory.
        add_action( 'wp_head', 'jcp_core_seo_directory_schema', 1 );
        return;
    }

    if ( $route === 'company' && $slug !== '' && function_exists( 'jcp_core_resolve_company_for_profile' ) ) {
        $company = jcp_core_resolve_company_for_profile( $slug );
        if ( $company !== null ) {
            $name   = isset( $company['name'] ) ? $company['name'] : '';
            $service = isset( $company['service'] ) ? $company['service'] : '';
            $city   = isset( $company['city'] ) ? $company['city'] : '';
            $permalink = isset( $company['permalink'] ) ? $company['permalink'] : $directory_url . $slug . '/';
            $desc_text = isset( $company['description'] ) && $company['description'] !== ''
                ? wp_trim_words( wp_strip_all_tags( $company['description'] ), 25 )
                : '';

            // Profile-specific title: "Summit Roofing - Roofing in Houston, TX | JobCapturePro"
            $profile_title = $name;
            if ( $service !== '' && $city !== '' ) {
                $profile_title .= ' - ' . $service . ' in ' . $city;
            } elseif ( $service !== '' ) {
                $profile_title .= ' - ' . $service;
            }
            $profile_title .= ' | ' . $site_name;

            add_filter(
                'rank_math/frontend/title',
                function ( $title ) use ( $profile_title ) {
                    return $profile_title;
                },
                15,
                1
            );
            $title_part = $name;
            if ( $service !== '' && $city !== '' ) {
                $title_part .= ' - ' . $service . ' in ' . $city;
            } elseif ( $service !== '' ) {
                $title_part .= ' - ' . $service;
            }
            add_filter(
                'document_title_parts',
                function ( $parts ) use ( $title_part ) {
                    $parts['title'] = $title_part;
                    return $parts;
                },
                999,
                1
            );
            // Meta description: unique per contractor.
            $meta_desc = $name;
            if ( $service !== '' || $city !== '' ) {
                $meta_desc .= ' — ' . trim( $service . ( $service && $city ? ', ' : '' ) . $city );
            }
            $meta_desc .= '. ' . __( 'Verified job proof and recent activity. View profile on JobCapturePro.', 'jcp-core' );
            if ( $desc_text !== '' ) {
                $meta_desc = $name . ' — ' . $desc_text;
            }
            add_filter(
                'rank_math/frontend/description',
                function ( $description ) use ( $meta_desc ) {
                    return $meta_desc;
                },
                10,
                1
            );
            // Schema: LocalBusiness for rich results and service-area understanding.
            add_action( 'wp_head', function () use ( $company, $permalink ) {
                jcp_core_seo_profile_schema( $company, $permalink );
            }, 1 );
        }
    }
}

/**
 * Output WebPage + ItemList schema for the directory listing page.
 *
 * @return void
 */
function jcp_core_seo_directory_schema(): void {
    $directory_url = home_url( '/directory/' );
    $listings = [];
    $companies = get_posts(
        [
            'post_type'      => 'jcp_company',
            'post_status'    => 'publish',
            'numberposts'    => -1,
        ]
    );
    foreach ( $companies as $c ) {
        if ( function_exists( 'jcp_directory_listing_is_temporarily_hidden' ) && jcp_directory_listing_is_temporarily_hidden( $c ) ) {
            continue;
        }
        $listings[] = home_url( '/directory/' . get_post_field( 'post_name', $c->ID ) . '/' );
    }
    if ( function_exists( 'jcp_core_get_demo_companies' ) ) {
        foreach ( jcp_core_get_demo_companies() as $row ) {
            if ( function_exists( 'jcp_directory_listing_is_temporarily_hidden' ) && jcp_directory_listing_is_temporarily_hidden( $row ) ) {
                continue;
            }
            $id = isset( $row['id'] ) ? $row['id'] : '';
            if ( $id !== '' ) {
                $listings[] = home_url( '/directory/' . $id . '/' );
            }
        }
    }
    $listings = array_unique( array_filter( $listings ) );

    $webpage = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => __( 'Contractor Directory', 'jcp-core' ) . ' - ' . get_bloginfo( 'name' ),
        'description' => __( 'Find verified contractors with real job proof. Ranked by activity and trust.', 'jcp-core' ),
        'url'      => $directory_url,
    ];
    if ( ! empty( $listings ) ) {
        $webpage['mainEntity'] = [
            '@type'     => 'ItemList',
            'itemListElement' => array_values( array_map( function ( $url, $i ) {
                return [
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'url'      => $url,
                ];
            }, $listings, array_keys( $listings ) ) ),
        ];
    }
    echo '<!-- Schema: Directory --><script type="application/ld+json">' . wp_json_encode( $webpage ) . "</script>\n";
}

/**
 * Output LocalBusiness schema for a contractor profile page.
 *
 * @param array  $company   Company data from jcp_core_resolve_company_for_profile or jcp_core_company_data.
 * @param string $permalink Profile URL.
 * @return void
 */
function jcp_core_seo_profile_schema( array $company, string $permalink ): void {
    $name = isset( $company['name'] ) ? $company['name'] : '';
    $service = isset( $company['service'] ) ? $company['service'] : 'Contractor';
    $city = isset( $company['city'] ) ? $company['city'] : '';
    $description = isset( $company['description'] ) ? wp_trim_words( wp_strip_all_tags( $company['description'] ), 40 ) : ( $name . ' - ' . $service . ( $city ? ' in ' . $city : '' ) );
    $logo = isset( $company['logo'] ) && $company['logo'] !== '' ? $company['logo'] : '';
    $lat = isset( $company['lat'] ) && is_numeric( $company['lat'] ) ? (float) $company['lat'] : null;
    $lng = isset( $company['lng'] ) && is_numeric( $company['lng'] ) ? (float) $company['lng'] : null;
    $rating = isset( $company['rating'] ) && $company['rating'] !== '' ? $company['rating'] : null;
    $reviews = isset( $company['reviews'] ) ? (int) $company['reviews'] : null;

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'      => 'ProfessionalService',
        'name'       => $name,
        'description' => $description,
        'url'        => $permalink,
    ];
    if ( $city !== '' ) {
        $schema['areaServed'] = [
            '@type' => 'City',
            'name'  => $city,
        ];
    }
    if ( $logo !== '' ) {
        $schema['image'] = $logo;
    }
    if ( $lat !== null && $lng !== null ) {
        $schema['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $lat,
            'longitude' => $lng,
        ];
    }
    if ( $rating !== null && $reviews !== null && $reviews > 0 && is_numeric( $rating ) ) {
        $schema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => (float) $rating,
            'reviewCount' => $reviews,
        ];
    }
    echo '<!-- Schema: Contractor profile --><script type="application/ld+json">' . wp_json_encode( $schema ) . "</script>\n";
}

add_action( 'template_redirect', 'jcp_core_seo_directory_profile', 20 );
