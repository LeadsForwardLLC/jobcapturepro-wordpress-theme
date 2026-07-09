<?php
/**
 * Automatic blog conversion layer — CTAs, trade matching, related hubs.
 * Zero per-post setup: injects mid-content + end-of-post conversion on singles,
 * and a CTA strip on the blog archive.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether blog conversion UI should run on this request.
 */
function jcp_blog_conversion_enabled(): bool {
	return (bool) apply_filters( 'jcp_blog_conversion_enabled', true );
}

/**
 * Published industry (trade) pages for matching.
 *
 * @return array<int, array{id:int,label:string,slug:string,url:string,tokens:array<int,string>}>
 */
function jcp_blog_conversion_trade_catalog(): array {
	$cached = get_transient( 'jcp_blog_conversion_trades_v1' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$posts = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]
	);

	$catalog = [];
	foreach ( $posts as $post ) {
		$content = function_exists( 'jcp_niche_get_content' ) ? jcp_niche_get_content( (int) $post->ID ) : [];
		$label   = ! empty( $content['niche_label'] ) ? (string) $content['niche_label'] : get_the_title( $post );
		$slug    = (string) $post->post_name;
		$tokens  = [];
		foreach ( [ $label, $slug, str_replace( '-', ' ', $slug ) ] as $piece ) {
			$tokens = array_merge( $tokens, jcp_blog_conversion_tokenize( (string) $piece ) );
		}
		// Common aliases.
		$aliases = [
			'hvac'       => [ 'hvac', 'heating', 'cooling', 'air conditioning', 'furnace' ],
			'plumbing'   => [ 'plumbing', 'plumber', 'drain', 'water heater', 'pipe' ],
			'roofing'    => [ 'roofing', 'roofer', 'roof', 'shingle' ],
			'electrical' => [ 'electrical', 'electrician', 'wiring' ],
			'landscap'   => [ 'landscaping', 'landscape', 'lawn' ],
			'foundation' => [ 'foundation', 'foundation repair', 'crawlspace' ],
		];
		foreach ( $aliases as $needle => $extra ) {
			if ( str_contains( strtolower( $slug . ' ' . $label ), $needle ) ) {
				foreach ( $extra as $alias ) {
					$tokens = array_merge( $tokens, jcp_blog_conversion_tokenize( $alias ) );
				}
			}
		}
		$tokens = array_values( array_unique( $tokens ) );
		if ( $tokens === [] ) {
			continue;
		}
		$catalog[] = [
			'id'     => (int) $post->ID,
			'label'  => $label,
			'slug'   => $slug,
			'url'    => get_permalink( $post ),
			'tokens' => $tokens,
		];
	}

	set_transient( 'jcp_blog_conversion_trades_v1', $catalog, HOUR_IN_SECONDS );
	return $catalog;
}

/**
 * Clear trade catalog cache when industries change.
 */
function jcp_blog_conversion_clear_trade_cache(): void {
	delete_transient( 'jcp_blog_conversion_trades_v1' );
}
add_action( 'save_post_jcp_niche_landing', 'jcp_blog_conversion_clear_trade_cache' );
add_action( 'after_switch_theme', 'jcp_blog_conversion_clear_trade_cache' );

/**
 * Tokenize text for matching.
 *
 * @param string $text Raw text.
 * @return array<int, string>
 */
function jcp_blog_conversion_tokenize( string $text ): array {
	if ( function_exists( 'jcp_internal_link_tokenize' ) ) {
		return jcp_internal_link_tokenize( $text );
	}
	$text  = strtolower( wp_strip_all_tags( $text ) );
	$text  = preg_replace( '/[^a-z0-9\s-]/u', ' ', $text ) ?? '';
	$parts = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
	return is_array( $parts ) ? array_values( array_unique( $parts ) ) : [];
}

/**
 * Build a searchable corpus for a blog post.
 *
 * @param int $post_id Post ID.
 */
function jcp_blog_conversion_post_corpus( int $post_id ): string {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return '';
	}
	$bits = [ $post->post_title, $post->post_excerpt, wp_trim_words( wp_strip_all_tags( $post->post_content ), 120, '' ) ];
	$tags = get_the_tags( $post_id );
	if ( is_array( $tags ) ) {
		foreach ( $tags as $tag ) {
			$bits[] = $tag->name;
		}
	}
	$cats = get_the_category( $post_id );
	if ( is_array( $cats ) ) {
		foreach ( $cats as $cat ) {
			$bits[] = $cat->name;
		}
	}
	$focus = trim( (string) get_post_meta( $post_id, 'rank_math_focus_keyword', true ) );
	if ( $focus === '' ) {
		$focus = trim( (string) get_post_meta( $post_id, '_rank_math_focus_keyword', true ) );
	}
	if ( $focus !== '' ) {
		$bits[] = $focus;
	}
	return implode( ' ', $bits );
}

/**
 * Detect the best-matching trade for a post (or null).
 *
 * @param int $post_id Post ID.
 * @return array{id:int,label:string,slug:string,url:string,score:int}|null
 */
function jcp_blog_conversion_detect_trade( int $post_id ): ?array {
	$corpus = strtolower( jcp_blog_conversion_post_corpus( $post_id ) );
	if ( $corpus === '' ) {
		return null;
	}
	$tokens = jcp_blog_conversion_tokenize( $corpus );
	$best   = null;
	$best_score = 0;

	foreach ( jcp_blog_conversion_trade_catalog() as $trade ) {
		$score = 0;
		$label = strtolower( $trade['label'] );
		$slug  = strtolower( $trade['slug'] );
		if ( $label !== '' && str_contains( $corpus, $label ) ) {
			$score += 12;
		}
		if ( $slug !== '' && str_contains( $corpus, str_replace( '-', ' ', $slug ) ) ) {
			$score += 8;
		}
		foreach ( $trade['tokens'] as $token ) {
			if ( in_array( $token, $tokens, true ) || str_contains( $corpus, $token ) ) {
				$score += strlen( $token ) >= 5 ? 3 : 2;
			}
		}
		if ( $score > $best_score ) {
			$best_score = $score;
			$best       = [
				'id'    => $trade['id'],
				'label' => $trade['label'],
				'slug'  => $trade['slug'],
				'url'   => $trade['url'],
				'score' => $score,
			];
		}
	}

	if ( ! $best || $best_score < 4 ) {
		return null;
	}
	return $best;
}

/**
 * Resolve demo + trial CTAs for blog surfaces.
 *
 * @param string $utm_suffix Surface key (blog_end, blog_mid, blog_archive).
 * @return array{demo: array{label:string,url:string}, trial: array{label:string,url:string}}
 */
function jcp_blog_conversion_ctas( string $utm_suffix = 'blog' ): array {
	$demo_url = home_url( '/demo/' );
	$trial    = [ 'label' => __( 'Start free trial', 'jcp-core' ), 'url' => home_url( '/demo/' ) ];
	if ( function_exists( 'jcp_global_resolve_cta' ) ) {
		$trial = jcp_global_resolve_cta(
			__( 'Start free trial', 'jcp-core' ),
			'',
			'blog_' . $utm_suffix
		);
	}
	return [
		'demo'  => [
			'label' => __( 'Watch the live demo', 'jcp-core' ),
			'url'   => $demo_url,
		],
		'trial' => $trial,
	];
}

/**
 * Related hub cards for a post (trade + demo + features hub).
 *
 * @param int $post_id Post ID.
 * @return array<int, array{label:string,url:string,excerpt:string,type:string}>
 */
function jcp_blog_conversion_related_hubs( int $post_id ): array {
	$trade = jcp_blog_conversion_detect_trade( $post_id );
	$cards = [];

	if ( $trade ) {
		$cards[] = [
			'label'   => sprintf(
				/* translators: %s: trade name */
				__( '%s marketing software', 'jcp-core' ),
				$trade['label']
			),
			'url'     => $trade['url'],
			'excerpt' => sprintf(
				/* translators: %s: trade name */
				__( 'See how JobCapturePro turns completed %s jobs into Google visibility, reviews, and website proof.', 'jcp-core' ),
				strtolower( $trade['label'] )
			),
			'type'    => 'trade',
		];
	}

	$cards[] = [
		'label'   => __( 'Interactive product demo', 'jcp-core' ),
		'url'     => home_url( '/demo/' ),
		'excerpt' => __( 'Walk through capture → check-in → publish in about two minutes. No signup required.', 'jcp-core' ),
		'type'    => 'demo',
	];

	$features_url = home_url( '/features/' );
	// Prefer a published Features page if it exists.
	$features_page = get_page_by_path( 'features' );
	if ( $features_page instanceof WP_Post && $features_page->post_status === 'publish' ) {
		$features_url = get_permalink( $features_page );
	}
	$cards[] = [
		'label'   => __( 'Product features', 'jcp-core' ),
		'url'     => $features_url,
		'excerpt' => __( 'Proof that compounds across Google, your website, social, and reviews — from jobs you already complete.', 'jcp-core' ),
		'type'    => 'feature',
	];

	if ( ! $trade ) {
		$industries = get_post_type_archive_link( 'jcp_niche_landing' );
		if ( ! is_string( $industries ) || $industries === '' ) {
			$industries = home_url( '/industries/' );
		}
		array_unshift(
			$cards,
			[
				'label'   => __( 'Solutions by trade', 'jcp-core' ),
				'url'     => $industries,
				'excerpt' => __( 'Browse industry pages for plumbers, HVAC, roofers, and more home-service trades.', 'jcp-core' ),
				'type'    => 'trade',
			]
		);
	}

	return array_slice( $cards, 0, 3 );
}

/**
 * Personalized headline/subcopy for end CTA.
 *
 * @param int $post_id Post ID.
 * @return array{headline:string,subheadline:string,note:string}
 */
function jcp_blog_conversion_end_copy( int $post_id ): array {
	$trade = jcp_blog_conversion_detect_trade( $post_id );
	if ( $trade ) {
		return [
			'headline'    => sprintf(
				/* translators: %s: trade name */
				__( 'Ready to turn every %s job into more customers?', 'jcp-core' ),
				$trade['label']
			),
			'subheadline' => sprintf(
				/* translators: %s: trade name */
				__( 'See how JobCapturePro captures proof from real %s work and publishes it where homeowners already search.', 'jcp-core' ),
				strtolower( $trade['label'] )
			),
			'note'        => __( 'No signup required for the demo · Setup in minutes', 'jcp-core' ),
		];
	}
	return [
		'headline'    => __( 'Turn completed jobs into more booked work', 'jcp-core' ),
		'subheadline' => __( 'JobCapturePro captures proof from the jobs you already finish and publishes it across Google, your website, and social — without adding busywork for your crew.', 'jcp-core' ),
		'note'        => __( 'No signup required for the demo · Setup in minutes', 'jcp-core' ),
	];
}

/**
 * Mid-content strip copy.
 *
 * @param int $post_id Post ID.
 * @return array{eyebrow:string,title:string,body:string}
 */
function jcp_blog_conversion_mid_copy( int $post_id ): array {
	$trade = jcp_blog_conversion_detect_trade( $post_id );
	if ( $trade ) {
		return [
			'eyebrow' => __( 'See it live', 'jcp-core' ),
			'title'   => sprintf(
				/* translators: %s: trade name */
				__( 'Watch how this works for %s companies', 'jcp-core' ),
				$trade['label']
			),
			'body'    => __( 'A two-minute interactive demo — capture a job, publish proof, request a review.', 'jcp-core' ),
		];
	}
	return [
		'eyebrow' => __( 'See it live', 'jcp-core' ),
		'title'   => __( 'Prefer to see the product instead of reading about it?', 'jcp-core' ),
		'body'    => __( 'Launch the interactive demo — no signup required.', 'jcp-core' ),
	];
}

/**
 * Render mid-content conversion strip HTML.
 *
 * @param int $post_id Post ID.
 */
function jcp_blog_conversion_render_mid_strip( int $post_id ): string {
	$copy = jcp_blog_conversion_mid_copy( $post_id );
	$ctas = jcp_blog_conversion_ctas( 'mid' );
	$demo = $ctas['demo'];

	ob_start();
	?>
	<aside class="jcp-blog-mid-cta" data-jcp-blog-cta="mid" aria-label="<?php esc_attr_e( 'Product demo call to action', 'jcp-core' ); ?>">
		<div class="jcp-blog-mid-cta__inner">
			<p class="jcp-blog-mid-cta__eyebrow"><?php echo esc_html( $copy['eyebrow'] ); ?></p>
			<p class="jcp-blog-mid-cta__title"><?php echo esc_html( $copy['title'] ); ?></p>
			<p class="jcp-blog-mid-cta__body"><?php echo esc_html( $copy['body'] ); ?></p>
			<a
				class="btn btn-primary jcp-blog-mid-cta__btn"
				href="<?php echo esc_url( $demo['url'] ); ?>"
				<?php
				if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
					jcp_niche_cta_tracking_attr( $demo['url'], 'blog_mid', $demo['label'] );
				}
				?>
			><?php echo esc_html( $demo['label'] ); ?> →</a>
		</div>
	</aside>
	<?php
	return (string) ob_get_clean();
}

/**
 * Inject mid-content CTA after ~40–50% of paragraphs (once).
 *
 * @param string $content Post content.
 */
function jcp_blog_conversion_inject_mid_content( string $content ): string {
	if ( ! jcp_blog_conversion_enabled() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( is_admin() || wp_is_json_request() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}
	if ( str_contains( $content, 'jcp-blog-mid-cta' ) ) {
		return $content;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return $content;
	}

	if ( ! preg_match_all( '/<\/p>/i', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		return $content;
	}

	$closes = $matches[0];
	$count  = count( $closes );
	if ( $count < 3 ) {
		return $content;
	}

	$insert_idx = (int) max( 1, min( $count - 1, (int) floor( $count * 0.45 ) ) );
	$offset     = (int) $closes[ $insert_idx ][1] + strlen( (string) $closes[ $insert_idx ][0] );
	$strip      = jcp_blog_conversion_render_mid_strip( (int) $post_id );

	return substr( $content, 0, $offset ) . $strip . substr( $content, $offset );
}
add_filter( 'the_content', 'jcp_blog_conversion_inject_mid_content', 12 );

/**
 * Render end-of-post conversion band + related hubs.
 *
 * @param int $post_id Post ID.
 */
function jcp_blog_conversion_render_end( int $post_id ): void {
	if ( ! jcp_blog_conversion_enabled() ) {
		return;
	}
	$copy  = jcp_blog_conversion_end_copy( $post_id );
	$ctas  = jcp_blog_conversion_ctas( 'end' );
	$hubs  = jcp_blog_conversion_related_hubs( $post_id );
	$trade = jcp_blog_conversion_detect_trade( $post_id );
	?>
	<section class="jcp-section rankings-section jcp-blog-end-conversion" data-jcp-blog-cta="end"<?php echo $trade ? ' data-jcp-trade="' . esc_attr( $trade['slug'] ) . '"' : ''; ?>>
		<div class="jcp-container">
			<div class="rankings-cta jcp-blog-end-cta">
				<div class="cta-content">
					<h3><?php echo esc_html( $copy['headline'] ); ?></h3>
					<p class="cta-paragraph"><?php echo esc_html( $copy['subheadline'] ); ?></p>
				</div>
				<div class="cta-button-wrapper jcp-blog-end-cta__actions">
					<a
						class="btn btn-primary rankings-cta-btn"
						href="<?php echo esc_url( $ctas['demo']['url'] ); ?>"
						<?php
						if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
							jcp_niche_cta_tracking_attr( $ctas['demo']['url'], 'blog_end', $ctas['demo']['label'] );
						}
						?>
					><?php echo esc_html( $ctas['demo']['label'] ); ?></a>
					<a
						class="btn btn-secondary jcp-blog-end-cta__secondary"
						href="<?php echo esc_url( $ctas['trial']['url'] ); ?>"
						<?php
						if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
							jcp_niche_cta_tracking_attr( $ctas['trial']['url'], 'blog_end_trial', $ctas['trial']['label'] );
						}
						?>
					><?php echo esc_html( $ctas['trial']['label'] ); ?></a>
					<p class="cta-note"><?php echo esc_html( $copy['note'] ); ?></p>
				</div>
			</div>

			<?php if ( $hubs ) : ?>
				<div class="jcp-blog-related">
					<h3 class="jcp-blog-related__title"><?php esc_html_e( 'Keep exploring', 'jcp-core' ); ?></h3>
					<div class="jcp-blog-related__grid">
						<?php foreach ( $hubs as $card ) : ?>
							<a class="jcp-blog-related__card" href="<?php echo esc_url( $card['url'] ); ?>" data-jcp-related="<?php echo esc_attr( $card['type'] ); ?>">
								<span class="jcp-blog-related__card-type"><?php echo esc_html( ucfirst( $card['type'] ) ); ?></span>
								<strong class="jcp-blog-related__card-title"><?php echo esc_html( $card['label'] ); ?></strong>
								<span class="jcp-blog-related__card-excerpt"><?php echo esc_html( $card['excerpt'] ); ?></span>
								<span class="jcp-blog-related__card-link"><?php esc_html_e( 'Learn more', 'jcp-core' ); ?> →</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Render archive / blog index CTA strip.
 */
function jcp_blog_conversion_render_archive_strip(): void {
	if ( ! jcp_blog_conversion_enabled() ) {
		return;
	}
	$ctas = jcp_blog_conversion_ctas( 'archive' );
	?>
	<section class="jcp-section rankings-section jcp-blog-archive-cta" data-jcp-blog-cta="archive">
		<div class="jcp-container">
			<div class="rankings-cta jcp-blog-archive-cta__band">
				<div class="cta-content">
					<h3><?php esc_html_e( 'See JobCapturePro turn real jobs into visibility', 'jcp-core' ); ?></h3>
					<p class="cta-paragraph"><?php esc_html_e( 'Skip the theory — launch the interactive demo and watch a completed job become Google, website, and social proof.', 'jcp-core' ); ?></p>
				</div>
				<div class="cta-button-wrapper">
					<a
						class="btn btn-primary rankings-cta-btn"
						href="<?php echo esc_url( $ctas['demo']['url'] ); ?>"
						<?php
						if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
							jcp_niche_cta_tracking_attr( $ctas['demo']['url'], 'blog_archive', $ctas['demo']['label'] );
						}
						?>
					><?php echo esc_html( $ctas['demo']['label'] ); ?></a>
					<p class="cta-note"><?php esc_html_e( 'No signup required · Takes about 2 minutes', 'jcp-core' ); ?></p>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Whether the blog-archive sticky bar should render (posts index only, not singles).
 */
function jcp_blog_conversion_should_show_sticky(): bool {
	if ( ! jcp_blog_conversion_enabled() ) {
		return false;
	}
	// Posts page / blog home only — not single posts (mid + end CTAs already cover those).
	return is_home() && ! is_singular();
}

/**
 * Render slim sticky conversion bar markup for the blog archive (hidden until JS reveals).
 */
function jcp_blog_conversion_render_sticky(): void {
	if ( ! jcp_blog_conversion_should_show_sticky() ) {
		return;
	}
	$ctas = jcp_blog_conversion_ctas( 'sticky' );
	$demo = $ctas['demo'];
	?>
	<div
		id="jcpBlogStickyCta"
		class="jcp-blog-sticky-cta"
		hidden
		data-jcp-blog-cta="sticky"
		role="region"
		aria-label="<?php esc_attr_e( 'Demo call to action', 'jcp-core' ); ?>"
	>
		<div class="jcp-blog-sticky-cta__inner">
			<p class="jcp-blog-sticky-cta__copy">
				<strong><?php esc_html_e( 'See it in 2 minutes', 'jcp-core' ); ?></strong>
				<span class="jcp-blog-sticky-cta__sep" aria-hidden="true">·</span>
				<span><?php esc_html_e( 'Interactive demo — no signup required', 'jcp-core' ); ?></span>
			</p>
			<div class="jcp-blog-sticky-cta__actions">
				<a
					class="btn btn-primary jcp-blog-sticky-cta__btn"
					href="<?php echo esc_url( $demo['url'] ); ?>"
					<?php
					if ( function_exists( 'jcp_niche_cta_tracking_attr' ) ) {
						jcp_niche_cta_tracking_attr( $demo['url'], 'blog_sticky', $demo['label'] );
					}
					?>
				><?php echo esc_html( $demo['label'] ); ?></a>
				<button
					type="button"
					class="jcp-blog-sticky-cta__close"
					id="jcpBlogStickyCtaClose"
					aria-label="<?php esc_attr_e( 'Dismiss', 'jcp-core' ); ?>"
				>×</button>
			</div>
		</div>
	</div>
	<?php
}
