<?php
/**
 * Server-rendered niche / industry landing sections.
 *
 * @package JCP_Core
 */

/**
 * Escape and echo plain text.
 *
 * @param string $text Text.
 */
function jcp_niche_e( string $text ): void {
	echo esc_html( $text );
}

/**
 * Render full niche landing page.
 *
 * @param int $post_id Post ID.
 */
function jcp_niche_render_page( int $post_id ): void {
	$c         = jcp_niche_get_content( $post_id );
	$niche_key = ! empty( $c['niche_key'] ) ? (string) $c['niche_key'] : get_post_field( 'post_name', $post_id );
	$niche_key = sanitize_title( $niche_key );

	echo '<main class="jcp-marketing jcp-niche" data-niche="' . esc_attr( $niche_key ) . '">';
	jcp_niche_render_hero( $c, $niche_key );
	jcp_niche_render_what_it_is( $c );
	jcp_niche_render_core_mechanic( $c );
	jcp_niche_render_how_it_works( $c, $niche_key );
	jcp_niche_render_check_ins( $c );
	jcp_niche_render_problem( $c );
	jcp_niche_render_benefits( $c );
	jcp_niche_render_differentiation( $c );
	jcp_niche_render_who_its_for( $c );
	jcp_niche_render_faq( $c );
	jcp_niche_render_final_cta( $c, $niche_key );
	echo '</main>';
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_hero( array $c, string $niche_key ): void {
	$h = $c['hero'] ?? [];
	if ( empty( $h['h1'] ) ) {
		return;
	}
	$primary   = jcp_niche_resolve_cta( $h['cta_primary'] ?? [], $niche_key );
	$secondary = jcp_niche_resolve_cta( $h['cta_secondary'] ?? [ 'label' => 'See how it works', 'url' => '#how-it-works' ], $niche_key );
	?>
	<section class="jcp-section jcp-hero jcp-niche-hero">
		<div class="jcp-container">
			<div class="jcp-hero-grid jcp-niche-hero-grid">
				<div class="jcp-hero-copy hero-copy">
					<h1 class="jcp-hero-title"><?php jcp_niche_e( (string) $h['h1'] ); ?></h1>
					<?php if ( ! empty( $h['subheadline'] ) ) : ?>
						<p class="jcp-hero-subtitle"><?php jcp_niche_e( (string) $h['subheadline'] ); ?></p>
					<?php endif; ?>
					<div class="jcp-actions directory-cta-row">
						<?php if ( $primary['label'] !== '' ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( $primary['url'] ); ?>" data-cta="<?php echo esc_attr( $primary['label'] ); ?>" data-cta-location="niche_hero"><?php jcp_niche_e( $primary['label'] ); ?></a>
						<?php endif; ?>
						<?php if ( $secondary['label'] !== '' ) : ?>
							<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] ); ?>"><?php jcp_niche_e( $secondary['label'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $h['trust_line'] ) ) : ?>
						<p class="jcp-niche-trust-line"><?php jcp_niche_e( (string) $h['trust_line'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_what_it_is( array $c ): void {
	$w = $c['what_it_is'] ?? [];
	if ( empty( $w['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-what">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $w['headline'] ); ?></h2>
				<?php if ( ! empty( $w['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"><?php jcp_niche_e( (string) $w['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="jcp-niche-two-col">
				<div class="jcp-niche-card">
					<h3 class="jcp-niche-card-title"><?php esc_html_e( 'Your team is already:', 'jcp-core' ); ?></h3>
					<ul class="jcp-niche-list">
						<?php foreach ( (array) ( $w['team_already'] ?? [] ) as $line ) : ?>
							<li><?php jcp_niche_e( (string) $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="jcp-niche-card">
					<p class="jcp-niche-lead"><?php esc_html_e( 'But once the work is done, most of it disappears. JobCapturePro changes that.', 'jcp-core' ); ?></p>
					<h3 class="jcp-niche-card-title"><?php esc_html_e( 'It automatically turns real jobs into:', 'jcp-core' ); ?></h3>
					<ul class="jcp-niche-list">
						<?php foreach ( (array) ( $w['turns_into'] ?? [] ) as $line ) : ?>
							<li><?php jcp_niche_e( (string) $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php if ( ! empty( $w['closing'] ) ) : ?>
				<p class="jcp-niche-closing"><?php jcp_niche_e( (string) $w['closing'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_core_mechanic( array $c ): void {
	$items = $c['core_mechanic'] ?? [];
	if ( empty( $items ) || ! is_array( $items ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-mechanic-strip" aria-label="<?php esc_attr_e( 'How it scales', 'jcp-core' ); ?>">
		<div class="jcp-container">
			<div class="proof-flow jcp-niche-proof-flow">
				<?php foreach ( $items as $item ) : ?>
					<?php if ( ! is_array( $item ) ) { continue; } ?>
					<div class="proof-flow-item">
						<div class="proof-flow-content">
							<div class="proof-flow-label"><?php jcp_niche_e( (string) ( $item['value'] ?? '' ) . ' ' . ( $item['label'] ?? '' ) ); ?></div>
							<?php if ( ! empty( $item['detail'] ) ) : ?>
								<p class="proof-flow-copy"><?php jcp_niche_e( (string) $item['detail'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_how_it_works( array $c, string $niche_key ): void {
	$h = $c['how_it_works'] ?? [];
	if ( empty( $h['headline'] ) ) {
		return;
	}
	$cta = jcp_niche_resolve_cta(
		[
			'label' => $h['cta_label'] ?? 'See it in action',
			'url'   => $h['cta_url'] ?? '/demo',
		],
		$niche_key
	);
	?>
	<section class="jcp-section rankings-section" id="how-it-works">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $h['headline'] ); ?></h2>
				<?php if ( ! empty( $h['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"><?php jcp_niche_e( (string) $h['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="timeline-steps">
				<?php
				$steps = (array) ( $h['steps'] ?? [] );
				foreach ( $steps as $i => $step ) :
					if ( ! is_array( $step ) ) {
						continue;
					}
					$num = str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
					?>
					<div class="timeline-step">
						<div class="step-number"><?php echo esc_html( $num ); ?></div>
						<div class="step-content">
							<h4 class="step-title"><?php jcp_niche_e( (string) ( $step['title'] ?? '' ) ); ?></h4>
							<?php foreach ( (array) ( $step['lines'] ?? [] ) as $line ) : ?>
								<p class="step-description"><?php jcp_niche_e( (string) $line ); ?></p>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( $cta['label'] !== '' ) : ?>
				<div class="timeline-cta">
					<a href="<?php echo esc_url( $cta['url'] ); ?>" class="btn btn-secondary"><?php jcp_niche_e( $cta['label'] ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_check_ins( array $c ): void {
	$ch = $c['check_ins'] ?? [];
	if ( empty( $ch['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-checkins">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $ch['headline'] ); ?></h2>
				<?php if ( ! empty( $ch['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"><?php jcp_niche_e( (string) $ch['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $ch['job_types'] ) ) : ?>
				<ul class="jcp-niche-tags">
					<?php foreach ( (array) $ch['job_types'] as $tag ) : ?>
						<li><?php jcp_niche_e( (string) $tag ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="jcp-niche-feature-grid">
				<?php foreach ( (array) ( $ch['features'] ?? [] ) as $feat ) : ?>
					<?php if ( ! is_array( $feat ) ) { continue; } ?>
					<div class="jcp-niche-feature-card">
						<h3><?php jcp_niche_e( (string) ( $feat['title'] ?? '' ) ); ?></h3>
						<p><?php jcp_niche_e( (string) ( $feat['body'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $ch['closing'] ) ) : ?>
				<p class="jcp-niche-closing"><?php jcp_niche_e( (string) $ch['closing'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_problem( array $c ): void {
	$p = $c['problem'] ?? [];
	if ( empty( $p['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-problem">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $p['headline'] ); ?></h2>
				<?php if ( ! empty( $p['subheadline'] ) ) : ?>
					<p class="rankings-subtitle"><?php jcp_niche_e( (string) $p['subheadline'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="jcp-niche-pain-grid">
				<?php foreach ( (array) ( $p['pain_points'] ?? [] ) as $pain ) : ?>
					<?php if ( ! is_array( $pain ) ) { continue; } ?>
					<div class="jcp-niche-pain-card">
						<h3><?php jcp_niche_e( (string) ( $pain['title'] ?? '' ) ); ?></h3>
						<p><?php jcp_niche_e( (string) ( $pain['body'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $p['closing'] ) ) : ?>
				<p class="jcp-niche-closing"><?php jcp_niche_e( (string) $p['closing'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_benefits( array $c ): void {
	$b = $c['benefits'] ?? [];
	if ( empty( $b['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-benefits">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $b['headline'] ); ?></h2>
			</div>
			<div class="jcp-niche-benefit-grid">
				<?php foreach ( (array) ( $b['items'] ?? [] ) as $item ) : ?>
					<?php if ( ! is_array( $item ) ) { continue; } ?>
					<div class="jcp-niche-benefit-card">
						<h3><?php jcp_niche_e( (string) ( $item['title'] ?? '' ) ); ?></h3>
						<p><?php jcp_niche_e( (string) ( $item['body'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $b['closing'] ) ) : ?>
				<p class="jcp-niche-closing jcp-niche-closing--center"><?php jcp_niche_e( (string) $b['closing'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_differentiation( array $c ): void {
	$d = $c['differentiation'] ?? [];
	if ( empty( $d['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-diff">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $d['headline'] ); ?></h2>
			</div>
			<?php if ( ! empty( $d['body'] ) ) : ?>
				<p class="jcp-niche-prose"><?php jcp_niche_e( (string) $d['body'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $d['bullets'] ) ) : ?>
				<ul class="jcp-niche-inline-bullets">
					<?php foreach ( (array) $d['bullets'] as $bullet ) : ?>
						<li><?php jcp_niche_e( (string) $bullet ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_who_its_for( array $c ): void {
	$w = $c['who_its_for'] ?? [];
	if ( empty( $w['headline'] ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section jcp-niche-audiences" id="who-its-for">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $w['headline'] ); ?></h2>
			</div>
			<div class="jcp-niche-audience-grid">
				<?php foreach ( (array) ( $w['audiences'] ?? [] ) as $aud ) : ?>
					<?php if ( ! is_array( $aud ) ) { continue; } ?>
					<div class="jcp-niche-audience-card">
						<h3><?php jcp_niche_e( (string) ( $aud['title'] ?? '' ) ); ?></h3>
						<p><?php jcp_niche_e( (string) ( $aud['body'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 */
function jcp_niche_render_faq( array $c ): void {
	$f = $c['faq'] ?? [];
	$items = (array) ( $f['items'] ?? [] );
	if ( empty( $f['headline'] ) || empty( $items ) ) {
		return;
	}
	?>
	<section class="jcp-section rankings-section faq-section" id="faq">
		<div class="jcp-container">
			<div class="rankings-header">
				<h2><?php jcp_niche_e( (string) $f['headline'] ); ?></h2>
			</div>
			<div class="faq-grid">
				<?php foreach ( $items as $i => $item ) : ?>
					<?php if ( ! is_array( $item ) ) { continue; } ?>
					<details class="faq-item" id="faq-<?php echo esc_attr( (string) $i ); ?>">
						<summary><?php jcp_niche_e( (string) ( $item['q'] ?? '' ) ); ?></summary>
						<p><?php jcp_niche_e( (string) ( $item['a'] ?? '' ) ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @param array<string, mixed> $c Content.
 * @param string               $niche_key Niche key.
 */
function jcp_niche_render_final_cta( array $c, string $niche_key ): void {
	$f = $c['final_cta'] ?? [];
	if ( empty( $f['headline'] ) ) {
		return;
	}
	$primary   = jcp_niche_resolve_cta( $f['cta_primary'] ?? [], $niche_key );
	$secondary = jcp_niche_resolve_cta( $f['cta_secondary'] ?? [ 'label' => 'See how it works', 'url' => '/demo' ], $niche_key );
	?>
	<section class="jcp-section rankings-section conversion-section jcp-niche-final">
		<div class="jcp-container">
			<div class="rankings-cta">
				<div class="cta-content">
					<h3><?php jcp_niche_e( (string) $f['headline'] ); ?></h3>
					<?php if ( ! empty( $f['subheadline'] ) ) : ?>
						<p class="cta-paragraph"><?php jcp_niche_e( (string) $f['subheadline'] ); ?></p>
					<?php endif; ?>
				</div>
				<div class="cta-button-wrapper">
					<?php if ( $primary['label'] !== '' ) : ?>
						<a class="btn btn-primary rankings-cta-btn" href="<?php echo esc_url( $primary['url'] ); ?>" data-cta="<?php echo esc_attr( $primary['label'] ); ?>" data-cta-location="niche_footer"><?php jcp_niche_e( $primary['label'] ); ?></a>
					<?php endif; ?>
					<?php if ( $secondary['label'] !== '' ) : ?>
						<a class="btn btn-secondary" href="<?php echo esc_url( $secondary['url'] ); ?>"><?php jcp_niche_e( $secondary['label'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render industries archive hub.
 */
function jcp_niche_render_archive(): void {
	$posts = get_posts(
		[
			'post_type'      => 'jcp_niche_landing',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]
	);
	?>
	<main class="jcp-marketing jcp-niche jcp-niche-archive">
		<section class="jcp-section jcp-hero jcp-niche-hero jcp-niche-archive-hero">
			<div class="jcp-container">
				<div class="jcp-hero-copy hero-copy">
					<h1 class="jcp-hero-title"><?php esc_html_e( 'Marketing software for home service contractors', 'jcp-core' ); ?></h1>
					<p class="jcp-hero-subtitle"><?php esc_html_e( 'JobCapturePro turns completed jobs into Google visibility, website proof, social posts, and reviews — built for the trades you run every day.', 'jcp-core' ); ?></p>
				</div>
			</div>
		</section>
		<section class="jcp-section rankings-section">
			<div class="jcp-container">
				<div class="rankings-header">
					<h2><?php esc_html_e( 'Browse by industry', 'jcp-core' ); ?></h2>
					<p class="rankings-subtitle"><?php esc_html_e( 'See how JobCapturePro works for your trade.', 'jcp-core' ); ?></p>
				</div>
				<div class="jcp-niche-archive-grid">
					<?php foreach ( $posts as $post ) : ?>
						<?php
						$content = jcp_niche_get_content( (int) $post->ID );
						$label   = ! empty( $content['niche_label'] ) ? (string) $content['niche_label'] : get_the_title( $post );
						$excerpt = $content['hero']['subheadline'] ?? get_the_excerpt( $post );
						?>
						<a class="jcp-niche-archive-card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
							<h3><?php echo esc_html( $label ); ?></h3>
							<p><?php echo esc_html( wp_strip_all_tags( (string) $excerpt ) ); ?></p>
							<span class="jcp-niche-archive-link"><?php esc_html_e( 'Learn more', 'jcp-core' ); ?> →</span>
						</a>
					<?php endforeach; ?>
				</div>
				<?php if ( empty( $posts ) ) : ?>
					<p><?php esc_html_e( 'Industry pages coming soon.', 'jcp-core' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	</main>
	<?php
}
