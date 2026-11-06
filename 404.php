<?php
/**
 * 404 — “This job site isn’t on the map”
 *
 * @package JCP_Core
 */

get_header();

$status = function_exists( 'http_response_code' ) ? (int) http_response_code() : 404;
if ( $status !== 404 ) {
	status_header( 404 );
	nocache_headers();
}

$icon_pin = function_exists( 'jcp_core_icon' ) ? jcp_core_icon( 'map-pin' ) : '';
$links    = [
	[
		'href'  => home_url( '/' ),
		'label' => __( 'Home', 'jcp-core' ),
		'desc'  => __( 'Back to the main site', 'jcp-core' ),
	],
	[
		'href'  => home_url( '/support/' ),
		'label' => __( 'Support', 'jcp-core' ),
		'desc'  => __( 'Message the team', 'jcp-core' ),
	],
	[
		'href'  => home_url( '/help/' ),
		'label' => __( 'Help Center', 'jcp-core' ),
		'desc'  => __( 'Guides & how-tos', 'jcp-core' ),
	],
	[
		'href'  => home_url( '/pricing/' ),
		'label' => __( 'Pricing', 'jcp-core' ),
		'desc'  => __( 'Plans that fit your crew', 'jcp-core' ),
	],
	[
		'href'  => home_url( '/demo/' ),
		'label' => __( 'Live demo', 'jcp-core' ),
		'desc'  => __( 'See JobCapturePro in action', 'jcp-core' ),
	],
];
?>
<main class="jcp-marketing jcp-404-page" id="jcp-404">
	<section class="jcp-404-hero" aria-labelledby="jcp-404-title">
		<div class="jcp-404-atmosphere" aria-hidden="true">
			<span class="jcp-404-grid"></span>
			<span class="jcp-404-glow jcp-404-glow--a"></span>
			<span class="jcp-404-glow jcp-404-glow--b"></span>
			<span class="jcp-404-route"></span>
		</div>

		<div class="jcp-container jcp-404-hero__inner">
			<div class="jcp-404-pin" aria-hidden="true">
				<span class="jcp-404-pin__pulse"></span>
				<span class="jcp-404-pin__pulse jcp-404-pin__pulse--delay"></span>
				<?php if ( $icon_pin ) : ?>
					<img class="jcp-404-pin__icon" src="<?php echo esc_url( $icon_pin ); ?>" alt="" width="56" height="56" />
				<?php else : ?>
					<svg class="jcp-404-pin__icon" viewBox="0 0 24 24" width="56" height="56" aria-hidden="true" fill="none" stroke="#ff5036" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/>
						<circle cx="12" cy="10" r="2.5"/>
					</svg>
				<?php endif; ?>
			</div>

			<p class="jcp-404-code">404</p>
			<h1 id="jcp-404-title" class="jcp-404-title"><?php esc_html_e( 'This job site isn’t on the map', 'jcp-core' ); ?></h1>
			<p class="jcp-404-lead">
				<?php esc_html_e( 'The page you’re looking for moved, got renamed, or never got captured. Let’s get you back to real work.', 'jcp-core' ); ?>
			</p>

			<form class="jcp-404-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="jcp-404-search-field"><?php esc_html_e( 'Search the site', 'jcp-core' ); ?></label>
				<input
					id="jcp-404-search-field"
					class="jcp-404-search__input"
					type="search"
					name="s"
					placeholder="<?php esc_attr_e( 'Search JobCapturePro…', 'jcp-core' ); ?>"
				/>
				<button class="btn btn-primary jcp-404-search__btn" type="submit"><?php esc_html_e( 'Search', 'jcp-core' ); ?></button>
			</form>
		</div>
	</section>

	<section class="jcp-404-exits" aria-labelledby="jcp-404-exits-title">
		<div class="jcp-container">
			<h2 id="jcp-404-exits-title" class="jcp-404-exits__title"><?php esc_html_e( 'Useful next stops', 'jcp-core' ); ?></h2>
			<ul class="jcp-404-exits__grid">
				<?php foreach ( $links as $i => $link ) : ?>
					<li class="jcp-404-exit" style="--jcp-404-i: <?php echo (int) $i; ?>">
						<a class="jcp-404-exit__card" href="<?php echo esc_url( $link['href'] ); ?>">
							<span class="jcp-404-exit__label"><?php echo esc_html( $link['label'] ); ?></span>
							<span class="jcp-404-exit__desc"><?php echo esc_html( $link['desc'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
</main>
<?php
get_footer();
