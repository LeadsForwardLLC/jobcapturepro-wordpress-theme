<?php
/**
 * Template Name: JCP Block Page
 *
 * Marketing / internal pages built from the global block library.
 * Keeps the existing WordPress page URL and Rank Math SEO — same post ID, same slug.
 *
 * @package JCP_Core
 */

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		jcp_niche_render_page( (int) get_the_ID() );
	}
}

get_footer();
