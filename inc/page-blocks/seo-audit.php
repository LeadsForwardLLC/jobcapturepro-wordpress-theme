<?php
/**
 * Built-in SEO health checks for JCP block pages (complements Rank Math).
 *
 * @package JCP_Core
 */

/**
 * Whether the post uses the JCP block page system.
 *
 * @param int $post_id Post ID.
 */
function jcp_seo_audit_is_block_page( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}
	return jcp_page_is_content_page( $post_id );
}

/**
 * Read Rank Math post meta (when plugin is active).
 *
 * @param int $post_id Post ID.
 * @return array{focus_keyword: string, title: string, description: string}
 */
function jcp_seo_audit_rank_math_meta( int $post_id ): array {
	$focus = trim( (string) get_post_meta( $post_id, 'rank_math_focus_keyword', true ) );
	if ( $focus === '' ) {
		$focus = trim( (string) get_post_meta( $post_id, '_rank_math_focus_keyword', true ) );
	}
	$title = trim( (string) get_post_meta( $post_id, 'rank_math_title', true ) );
	if ( $title === '' ) {
		$title = trim( (string) get_post_meta( $post_id, '_rank_math_title', true ) );
	}
	$description = trim( (string) get_post_meta( $post_id, 'rank_math_description', true ) );
	if ( $description === '' ) {
		$description = trim( (string) get_post_meta( $post_id, '_rank_math_description', true ) );
	}
	return [
		'focus_keyword' => $focus,
		'title'         => $title,
		'description'   => $description,
	];
}

/**
 * Extract plain text snippets from flat page content for SEO checks.
 *
 * @param array<string, mixed> $flat Flat content.
 * @return array{h1: string, intro: string, slug_hint: string}
 */
function jcp_seo_audit_content_snippets( array $flat ): array {
	$hero = is_array( $flat['hero'] ?? null ) ? $flat['hero'] : [];
	$h1   = trim( (string) ( $hero['h1'] ?? $hero['h1_prefix'] ?? '' ) );
	$intro = trim( (string) ( $hero['subheadline'] ?? '' ) );
	if ( $intro === '' && ! empty( $flat['what_it_is']['intro'] ) ) {
		$intro = trim( (string) $flat['what_it_is']['intro'] );
	}
	$slug_hint = trim( (string) ( $flat['niche_key'] ?? '' ) );
	return [
		'h1'        => $h1,
		'intro'     => $intro,
		'slug_hint' => $slug_hint,
	];
}

/**
 * Count keyword occurrences (case-insensitive, whole-word-ish).
 *
 * @param string $haystack Text.
 * @param string $keyword  Keyword phrase.
 */
function jcp_seo_audit_keyword_count( string $haystack, string $keyword ): int {
	$keyword = trim( $keyword );
	if ( $keyword === '' || $haystack === '' ) {
		return 0;
	}
	$pattern = '/' . preg_quote( $keyword, '/' ) . '/iu';
	return preg_match_all( $pattern, $haystack, $m ) ? count( $m[0] ) : 0;
}

/**
 * Run SEO audit for a block page.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{level: string, message: string}>
 */
function jcp_seo_audit_run( int $post_id ): array {
	$issues = [];
	$post   = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return $issues;
	}

	$rm    = jcp_seo_audit_rank_math_meta( $post_id );
	$flat  = jcp_page_get_content_flat( $post_id );
	$copy  = jcp_seo_audit_content_snippets( $flat );
	$focus = $rm['focus_keyword'];

	// Primary focus keyword is required for programmatic pages.
	if ( $focus === '' ) {
		$doc_kw = '';
		if ( ! empty( $flat['seo']['keywords'] ) && is_array( $flat['seo']['keywords'] ) ) {
			$doc_kw = trim( (string) ( $flat['seo']['keywords'][0] ?? '' ) );
		}
		if ( $doc_kw !== '' ) {
			$issues[] = [
				'level'   => 'warning',
				'message' => sprintf(
					/* translators: %s: suggested keyword from document import */
					__( 'Set a Rank Math focus keyword. Document import suggested: “%s”.', 'jcp-core' ),
					$doc_kw
				),
			];
		} else {
			$issues[] = [
				'level'   => 'error',
				'message' => __( 'Add a primary focus keyword in the Rank Math panel (required for every page).', 'jcp-core' ),
			];
		}
	}

	if ( $rm['title'] === '' ) {
		$issues[] = [
			'level'   => 'error',
			'message' => __( 'Set an SEO title in Rank Math (50–60 characters ideal).', 'jcp-core' ),
		];
	} elseif ( mb_strlen( $rm['title'] ) < 30 ) {
		$issues[] = [
			'level'   => 'warning',
			'message' => __( 'SEO title is short — aim for 50–60 characters with the focus keyword near the start.', 'jcp-core' ),
		];
	} elseif ( mb_strlen( $rm['title'] ) > 65 ) {
		$issues[] = [
			'level'   => 'warning',
			'message' => __( 'SEO title may truncate in search results (over ~60 characters).', 'jcp-core' ),
		];
	}

	if ( $rm['description'] === '' ) {
		$issues[] = [
			'level'   => 'error',
			'message' => __( 'Set a meta description in Rank Math (140–160 characters ideal).', 'jcp-core' ),
		];
	} elseif ( mb_strlen( $rm['description'] ) < 100 ) {
		$issues[] = [
			'level'   => 'warning',
			'message' => __( 'Meta description is short — aim for 140–160 characters with a clear benefit and keyword.', 'jcp-core' ),
		];
	}

	if ( $copy['h1'] === '' ) {
		$issues[] = [
			'level'   => 'error',
			'message' => __( 'Hero H1 is empty — add one via Quick Edit or the live page editor.', 'jcp-core' ),
		];
	}

	if ( $focus !== '' && $copy['h1'] !== '' && ! str_contains( mb_strtolower( $copy['h1'] ), mb_strtolower( $focus ) ) ) {
		$issues[] = [
			'level'   => 'warning',
			'message' => __( 'Focus keyword does not appear in the hero H1 — include it naturally for stronger relevance.', 'jcp-core' ),
		];
	}

	if ( $focus !== '' && $rm['title'] !== '' && ! str_contains( mb_strtolower( $rm['title'] ), mb_strtolower( $focus ) ) ) {
		$issues[] = [
			'level'   => 'warning',
			'message' => __( 'Focus keyword is missing from the SEO title.', 'jcp-core' ),
		];
	}

	$body_text = $copy['h1'] . ' ' . $copy['intro'];
	if ( $focus !== '' && $body_text !== '' ) {
		$count = jcp_seo_audit_keyword_count( $body_text, $focus );
		if ( $count === 0 ) {
			$issues[] = [
				'level'   => 'warning',
				'message' => __( 'Focus keyword not found in hero copy — use it in the H1 or subheadline.', 'jcp-core' ),
			];
		}
	}

	if ( function_exists( 'jcp_internal_link_post_audit' ) ) {
		$links = jcp_internal_link_post_audit( $post_id );
		$in    = (int) ( $links['inbound'] ?? 0 );
		$out   = (int) ( $links['outbound_internal'] ?? 0 );
		$ext   = (int) ( $links['outbound_external'] ?? 0 );

		if ( $post->post_status === 'publish' && $in === 0 ) {
			$issues[] = [
				'level'   => 'warning',
				'message' => __( 'No inbound internal links — other site pages do not link here yet. Add links from related trades, features, or blog posts.', 'jcp-core' ),
			];
		} elseif ( $in >= 20 ) {
			$issues[] = [
				'level'   => 'warning',
				'message' => sprintf(
					/* translators: %d: inbound link count */
					__( 'High inbound internal link count (%d) — verify links are relevant and not over-optimized.', 'jcp-core' ),
					$in
				),
			];
		}

		if ( $out >= 15 ) {
			$issues[] = [
				'level'   => 'warning',
				'message' => sprintf(
					/* translators: %d: outbound internal link count */
					__( 'Many outbound internal links (%d) — consider trimming to the most relevant destinations.', 'jcp-core' ),
					$out
				),
			];
		} elseif ( $post->post_status === 'publish' && $out <= 1 ) {
			$issues[] = [
				'level'   => 'warning',
				'message' => __( 'Few outbound internal links — link to related trades, demo, or features where it helps readers.', 'jcp-core' ),
			];
		}

		if ( $ext > 0 ) {
			$hosts = (array) ( $links['external_hosts'] ?? [] );
			$host_list = implode( ', ', array_slice( $hosts, 0, 4 ) );
			if ( count( $hosts ) > 4 ) {
				$host_list .= '…';
			}
			$issues[] = [
				'level'   => 'warning',
				'message' => sprintf(
					/* translators: 1: count, 2: host list */
					__( 'Outbound external links present (%1$d) — review destinations: %2$s', 'jcp-core' ),
					$ext,
					$host_list !== '' ? $host_list : __( 'external sites', 'jcp-core' )
				),
			];
		}
	}

	if ( $post->post_status === 'publish' && ! empty( $issues ) ) {
		$has_error = false;
		foreach ( $issues as $issue ) {
			if ( ( $issue['level'] ?? '' ) === 'error' ) {
				$has_error = true;
				break;
			}
		}
		if ( $has_error ) {
			array_unshift(
				$issues,
				[
					'level'   => 'error',
					'message' => __( 'This page is published but SEO is incomplete — fix Rank Math settings before expecting strong rankings.', 'jcp-core' ),
				]
			);
		}
	}

	if ( empty( $issues ) ) {
		$issues[] = [
			'level'   => 'ok',
			'message' => __( 'Core SEO checks passed. Review Rank Math’s full score for additional suggestions.', 'jcp-core' ),
		];
	}

	return $issues;
}

/**
 * Register SEO health meta box on block page edit screens.
 */
function jcp_seo_audit_register_meta_box(): void {
	global $post;
	$types = [ 'jcp_niche_landing' ];
	foreach ( $types as $post_type ) {
		add_meta_box(
			'jcp_seo_health',
			__( 'SEO Health (JCP + Rank Math)', 'jcp-core' ),
			'jcp_seo_audit_render_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
	if ( $post instanceof WP_Post && $post->post_type === 'page' && jcp_admin_page_uses_editor( $post ) ) {
		add_meta_box(
			'jcp_seo_health',
			__( 'SEO Health (JCP + Rank Math)', 'jcp-core' ),
			'jcp_seo_audit_render_meta_box',
			'page',
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'jcp_seo_audit_register_meta_box', 5 );

/**
 * @param WP_Post $post Post.
 */
function jcp_seo_audit_render_meta_box( WP_Post $post ): void {
	if ( ! jcp_seo_audit_is_block_page( (int) $post->ID ) ) {
		return;
	}

	$issues = jcp_seo_audit_run( (int) $post->ID );
	$rm     = jcp_seo_audit_rank_math_meta( (int) $post->ID );
	?>
	<div class="jcp-seo-audit">
		<?php if ( $rm['focus_keyword'] !== '' ) : ?>
			<p class="jcp-seo-audit__keyword">
				<strong><?php esc_html_e( 'Focus keyword:', 'jcp-core' ); ?></strong>
				<code><?php echo esc_html( $rm['focus_keyword'] ); ?></code>
			</p>
		<?php endif; ?>
		<ul class="jcp-seo-audit__list">
			<?php foreach ( $issues as $issue ) : ?>
				<?php
				$level = (string) ( $issue['level'] ?? 'warning' );
				$class = 'jcp-seo-audit__item jcp-seo-audit__item--' . sanitize_html_class( $level );
				?>
				<li class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( (string) ( $issue['message'] ?? '' ) ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p class="description">
			<?php esc_html_e( 'Complete the Rank Math panel on this screen (focus keyword, SEO title, meta description). JCP checks on-page copy against that keyword and flags internal/external link balance.', 'jcp-core' ); ?>
		</p>
	</div>
	<style>
		.jcp-seo-audit__list { margin: 0.5em 0 0; padding-left: 1.25em; }
		.jcp-seo-audit__item { margin-bottom: 0.35em; }
		.jcp-seo-audit__item--error { color: #b32d2e; font-weight: 600; }
		.jcp-seo-audit__item--warning { color: #996800; }
		.jcp-seo-audit__item--ok { color: #007017; list-style: none; margin-left: -1.25em; }
		.jcp-seo-audit__item--ok::before { content: "✓ "; }
	</style>
	<?php
}

/**
 * Admin list table column: SEO status.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function jcp_seo_audit_posts_columns( array $columns ): array {
	$new = [];
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( $key === 'title' ) {
			$new['jcp_seo'] = __( 'SEO', 'jcp-core' );
		}
	}
	return $new;
}

/**
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function jcp_seo_audit_posts_column_content( string $column, int $post_id ): void {
	if ( $column !== 'jcp_seo' || ! jcp_seo_audit_is_block_page( $post_id ) ) {
		return;
	}
	$issues = jcp_seo_audit_run( $post_id );
	$worst  = 'ok';
	foreach ( $issues as $issue ) {
		$level = (string) ( $issue['level'] ?? '' );
		if ( $level === 'error' ) {
			$worst = 'error';
			break;
		}
		if ( $level === 'warning' && $worst !== 'error' ) {
			$worst = 'warning';
		}
	}
	$labels = [
		'ok'      => __( 'OK', 'jcp-core' ),
		'warning' => __( 'Needs work', 'jcp-core' ),
		'error'   => __( 'Incomplete', 'jcp-core' ),
	];
	echo '<span class="jcp-seo-col jcp-seo-col--' . esc_attr( $worst ) . '">' . esc_html( $labels[ $worst ] ?? $worst ) . '</span>';
}

foreach ( [ 'jcp_niche_landing', 'page' ] as $jcp_seo_post_type ) {
	add_filter( "manage_{$jcp_seo_post_type}_posts_columns", 'jcp_seo_audit_posts_columns' );
	add_action( "manage_{$jcp_seo_post_type}_posts_custom_column", 'jcp_seo_audit_posts_column_content', 10, 2 );
}
