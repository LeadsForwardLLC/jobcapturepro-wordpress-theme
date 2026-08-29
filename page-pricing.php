<?php
/**
 * Template Name: Pricing
 *
 * Renders the pricing page via JavaScript (data-jcp-page="pricing").
 * Hero title/supporting come from editable page content when present.
 *
 * @package JCP_Core
 */

get_header();

$post_id = get_queried_object_id();
if ( $post_id <= 0 ) {
	$page    = get_page_by_path( 'pricing', OBJECT, 'page' );
	$post_id = $page instanceof WP_Post ? (int) $page->ID : 0;
}

$flat = ( $post_id > 0 && function_exists( 'jcp_page_get_content_flat' ) )
	? jcp_page_get_content_flat( $post_id )
	: [];
$hero = is_array( $flat['hero'] ?? null ) ? $flat['hero'] : [];

$default_title      = __( 'Choose the plan that matches your growth', 'jcp-core' );
$default_supporting = __( 'Each tier aligns to business maturity and visibility goals. Start for free and turn real work into reviews, visibility, and trust that drives inbound demand.', 'jcp-core' );

$title = trim( (string) ( $hero['h1'] ?? '' ) );
if ( $title === '' ) {
	$wp_title = trim( (string) get_the_title( $post_id ) );
	$title    = ( $wp_title !== '' && strcasecmp( $wp_title, 'Pricing' ) !== 0 ) ? $wp_title : $default_title;
}

$supporting_plain = trim( (string) ( $hero['subheadline'] ?? '' ) );
if ( $supporting_plain === '' && $post_id > 0 ) {
	$page_content = get_post_field( 'post_content', $post_id );
	$supporting_plain = trim( wp_strip_all_tags( (string) $page_content ) );
}
if ( $supporting_plain === '' ) {
	$supporting_plain = $default_supporting;
}

if ( function_exists( 'jcp_core_replace_retired_promo_copy' ) ) {
	$title            = jcp_core_replace_retired_promo_copy( $title );
	$supporting_plain = jcp_core_replace_retired_promo_copy( $supporting_plain );
}
?>
<div
	id="jcp-app"
	data-jcp-page="pricing"
	data-page-title="<?php echo esc_attr( $title ); ?>"
	data-page-supporting="<?php echo esc_attr( $supporting_plain ); ?>"
></div>
<?php
get_footer();
