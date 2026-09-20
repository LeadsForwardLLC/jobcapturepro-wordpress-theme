<?php
/**
 * Admin: Funnel Analytics (multi-funnel unique-session reporting).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Funnel Analytics under JCP menu.
 */
function jcp_funnel_analytics_admin_menu(): void {
	add_submenu_page(
		'jcp-theme-settings',
		__( 'Funnel Analytics', 'jcp-core' ),
		__( 'Funnel Analytics', 'jcp-core' ),
		'manage_options',
		'jcp-funnel-analytics',
		'jcp_funnel_analytics_render_admin'
	);
}
add_action( 'admin_menu', 'jcp_funnel_analytics_admin_menu', 20 );

/**
 * Enqueue admin styles on this page only.
 *
 * @param string $hook Hook.
 */
function jcp_funnel_analytics_admin_assets( string $hook ): void {
	if ( $hook !== 'jcp-theme-settings_page_jcp-funnel-analytics' ) {
		return;
	}
	$path = get_template_directory() . '/css/admin/funnel-analytics.css';
	$uri  = get_template_directory_uri() . '/css/admin/funnel-analytics.css';
	$ver  = is_readable( $path ) ? (string) filemtime( $path ) : '1';
	wp_enqueue_style( 'jcp-funnel-analytics-admin', $uri, [], $ver );
}
add_action( 'admin_enqueue_scripts', 'jcp_funnel_analytics_admin_assets' );

/**
 * CSV export (non-PII aggregates only).
 */
function jcp_funnel_analytics_handle_csv_export(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'jcp-core' ) );
	}
	check_admin_referer( 'jcp_funnel_analytics_csv' );
	if ( ! function_exists( 'jcp_funnel_analytics_report' ) ) {
		wp_die( esc_html__( 'Analytics module not loaded.', 'jcp-core' ) );
	}

	$filters = jcp_funnel_analytics_parse_filters();
	$report  = jcp_funnel_analytics_report( $filters );
	$funnel  = (string) ( $filters['funnel'] ?? 'proof_gap' );
	$days    = (int) ( $filters['days'] ?? 30 );
	$filename = sprintf( 'jcp-funnel-%s-%dd-%s.csv', $funnel, $days, gmdate( 'Ymd' ) );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$out = fopen( 'php://output', 'w' );
	if ( ! $out ) {
		wp_die( esc_html__( 'Could not open output stream.', 'jcp-core' ) );
	}

	fputcsv( $out, [ 'section', 'key', 'label', 'sessions', 'from_previous_pct', 'drop', 'drop_pct', 'from_landing_pct', 'median_ms' ] );
	foreach ( $report['stages'] ?? [] as $stage ) {
		fputcsv(
			$out,
			[
				'stage',
				(string) ( $stage['key'] ?? '' ),
				(string) ( $stage['label'] ?? '' ),
				! empty( $stage['not_connected'] ) ? 'not_connected' : (string) (int) ( $stage['sessions'] ?? 0 ),
				(string) ( $stage['from_previous'] ?? '' ),
				(string) ( $stage['drop'] ?? '' ),
				(string) ( $stage['drop_pct'] ?? '' ),
				(string) ( $stage['from_landing'] ?? '' ),
				(string) ( $stage['median_ms'] ?? '' ),
			]
		);
	}

	fputcsv( $out, [] );
	fputcsv( $out, [ 'section', 'question', 'viewed', 'answered', 'answer_rate', 'drop_after_view', 'median_ms', 'top_answers' ] );
	foreach ( $report['questions'] ?? [] as $q ) {
		$tops = [];
		foreach ( $q['top_answers'] ?? [] as $a ) {
			$tops[] = ( $a['value'] ?? '' ) . ':' . (int) ( $a['count'] ?? 0 );
		}
		fputcsv(
			$out,
			[
				'question',
				(string) ( $q['question'] ?? '' ),
				(int) ( $q['viewed'] ?? 0 ),
				(int) ( $q['answered'] ?? 0 ),
				(string) ( $q['answer_rate'] ?? '' ),
				(int) ( $q['drop_after_view'] ?? 0 ),
				(string) ( $q['median_ms'] ?? '' ),
				implode( '|', $tops ),
			]
		);
	}

	fputcsv( $out, [] );
	fputcsv( $out, [ 'section', 'destination', 'sessions' ] );
	foreach ( $report['destinations'] ?? [] as $d ) {
		fputcsv( $out, [ 'destination', (string) ( $d['destination'] ?? '' ), (int) ( $d['sessions'] ?? 0 ) ] );
	}

	fputcsv( $out, [] );
	fputcsv( $out, [ 'section', 'device', 'sessions' ] );
	foreach ( $report['devices'] ?? [] as $d ) {
		fputcsv( $out, [ 'device', (string) ( $d['device'] ?? '' ), (int) ( $d['sessions'] ?? 0 ) ] );
	}

	fputcsv( $out, [] );
	fputcsv( $out, [ 'section', 'source', 'campaign', 'content', 'landing', 'starts', 'survey_pct', 'email_pct', 'trial_cta_pct' ] );
	foreach ( $report['traffic'] ?? [] as $t ) {
		fputcsv(
			$out,
			[
				'traffic',
				(string) ( $t['source'] ?? '' ),
				(string) ( $t['campaign'] ?? '' ),
				(string) ( $t['content'] ?? '' ),
				(int) ( $t['landing_sessions'] ?? 0 ),
				(int) ( $t['starts'] ?? 0 ),
				(string) ( $t['survey_completion'] ?? '' ),
				(string) ( $t['email_pct'] ?? '' ),
				(string) ( $t['trial_cta_pct'] ?? '' ),
			]
		);
	}

	fclose( $out );
	exit;
}
add_action( 'admin_post_jcp_funnel_analytics_csv', 'jcp_funnel_analytics_handle_csv_export' );

/**
 * Format ms as human duration.
 *
 * @param int|null $ms Milliseconds.
 */
function jcp_funnel_analytics_format_ms( $ms ): string {
	if ( $ms === null || $ms === '' ) {
		return __( 'n/a', 'jcp-core' );
	}
	$ms = (int) $ms;
	if ( $ms < 1000 ) {
		return $ms . ' ms';
	}
	$sec = round( $ms / 1000, 1 );
	if ( $sec < 60 ) {
		return $sec . 's';
	}
	return round( $sec / 60, 1 ) . 'm';
}

/**
 * Render admin dashboard.
 */
function jcp_funnel_analytics_render_admin(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! function_exists( 'jcp_funnel_analytics_report' ) ) {
		echo '<div class="wrap"><h1>Funnel Analytics</h1><p>Analytics module not loaded.</p></div>';
		return;
	}

	$filters = jcp_funnel_analytics_parse_filters();
	$report  = jcp_funnel_analytics_report( $filters );
	$funnels = jcp_funnel_analytics_funnels();
	$summary = $report['summary'] ?? [];
	$stages  = $report['stages'] ?? [];
	$rates   = $report['rates'] ?? [];
	$compare = $report['compare'] ?? [];
	$nc      = static function ( $v ) {
		if ( $v === null ) {
			return esc_html__( 'Not connected', 'jcp-core' );
		}
		return esc_html( (string) $v );
	};
	$delta_html = static function ( string $key ) use ( $compare ): string {
		if ( empty( $compare[ $key ] ) || $compare[ $key ]['delta'] === null ) {
			return '';
		}
		$d   = (float) $compare[ $key ]['delta'];
		$pct = $compare[ $key ]['delta_pct'];
		$cls = $d > 0 ? 'is-up' : ( $d < 0 ? 'is-down' : 'is-flat' );
		$sign = $d > 0 ? '+' : '';
		$label = $sign . (int) $d;
		if ( $pct !== null ) {
			$label .= ' (' . $sign . $pct . '%)';
		}
		return '<div class="jcp-fa__card-delta ' . esc_attr( $cls ) . '">' . esc_html( $label ) . ' vs prior</div>';
	};

	$landing_sessions = 0;
	foreach ( $stages as $s ) {
		if ( ( $s['key'] ?? '' ) === 'landing' && is_int( $s['sessions'] ?? null ) ) {
			$landing_sessions = (int) $s['sessions'];
			break;
		}
	}

	$csv_url = wp_nonce_url(
		add_query_arg(
			array_merge(
				[ 'action' => 'jcp_funnel_analytics_csv' ],
				array_filter(
					[
						'funnel'           => $filters['funnel'],
						'days'             => $filters['days'],
						'utm_source'       => $filters['utm_source'],
						'utm_medium'       => $filters['utm_medium'] ?? '',
						'utm_campaign'     => $filters['utm_campaign'],
						'utm_content'      => $filters['utm_content'],
						'lp_variant'       => $filters['lp_variant'],
						'creative_concept' => $filters['creative_concept'] ?? '',
						'trade'            => $filters['trade'],
						'workflow'         => $filters['workflow'] ?? '',
						'device'           => $filters['device'],
					],
					static function ( $v ) {
						return $v !== '' && $v !== null;
					}
				)
			),
			admin_url( 'admin-post.php' )
		),
		'jcp_funnel_analytics_csv'
	);
	?>
	<div class="wrap jcp-fa jcp-funnel-analytics">
		<h1><?php esc_html_e( 'Funnel Analytics', 'jcp-core' ); ?></h1>
		<p class="jcp-fa__note description"><?php echo esc_html( (string) ( $report['calculation_note'] ?? '' ) ); ?></p>
		<?php if ( ! empty( $report['compare_note'] ) ) : ?>
			<p class="description"><?php echo esc_html( (string) $report['compare_note'] ); ?></p>
		<?php endif; ?>

		<form method="get" class="jcp-fa__filters">
			<input type="hidden" name="page" value="jcp-funnel-analytics" />
			<label><?php esc_html_e( 'Funnel', 'jcp-core' ); ?>
				<select name="funnel">
					<?php foreach ( $funnels as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['funnel'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label><?php esc_html_e( 'Date range', 'jcp-core' ); ?>
				<select name="days">
					<?php foreach ( [ 7, 14, 30, 90 ] as $d ) : ?>
						<option value="<?php echo (int) $d; ?>" <?php selected( (int) $filters['days'], $d ); ?>><?php echo esc_html( sprintf( __( 'Last %d days', 'jcp-core' ), $d ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="jcp-fa__compare">
				<input type="checkbox" name="compare" value="1" <?php checked( ! empty( $filters['compare'] ) ); ?> />
				<?php esc_html_e( 'Compare prior period', 'jcp-core' ); ?>
			</label>
			<label><?php esc_html_e( 'UTM source', 'jcp-core' ); ?> <input type="text" name="utm_source" value="<?php echo esc_attr( (string) $filters['utm_source'] ); ?>" placeholder="facebook" /></label>
			<label><?php esc_html_e( 'UTM medium', 'jcp-core' ); ?> <input type="text" name="utm_medium" value="<?php echo esc_attr( (string) ( $filters['utm_medium'] ?? '' ) ); ?>" /></label>
			<label><?php esc_html_e( 'UTM campaign', 'jcp-core' ); ?> <input type="text" name="utm_campaign" value="<?php echo esc_attr( (string) $filters['utm_campaign'] ); ?>" /></label>
			<label><?php esc_html_e( 'UTM content', 'jcp-core' ); ?> <input type="text" name="utm_content" value="<?php echo esc_attr( (string) $filters['utm_content'] ); ?>" /></label>
			<label><?php esc_html_e( 'lp_variant', 'jcp-core' ); ?> <input type="text" name="lp_variant" value="<?php echo esc_attr( (string) $filters['lp_variant'] ); ?>" /></label>
			<label><?php esc_html_e( 'creative_concept', 'jcp-core' ); ?> <input type="text" name="creative_concept" value="<?php echo esc_attr( (string) ( $filters['creative_concept'] ?? '' ) ); ?>" /></label>
			<label><?php esc_html_e( 'Trade', 'jcp-core' ); ?> <input type="text" name="trade" value="<?php echo esc_attr( (string) $filters['trade'] ); ?>" /></label>
			<label><?php esc_html_e( 'Workflow', 'jcp-core' ); ?> <input type="text" name="workflow" value="<?php echo esc_attr( (string) ( $filters['workflow'] ?? '' ) ); ?>" /></label>
			<label><?php esc_html_e( 'Device', 'jcp-core' ); ?>
				<select name="device">
					<option value=""><?php esc_html_e( 'All', 'jcp-core' ); ?></option>
					<?php foreach ( [ 'mobile', 'tablet', 'desktop' ] as $dev ) : ?>
						<option value="<?php echo esc_attr( $dev ); ?>" <?php selected( (string) $filters['device'], $dev ); ?>><?php echo esc_html( ucfirst( $dev ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button class="button button-primary" type="submit"><?php esc_html_e( 'Apply', 'jcp-core' ); ?></button>
		</form>

		<div class="jcp-fa__toolbar">
			<a class="button" href="<?php echo esc_url( $csv_url ); ?>"><?php esc_html_e( 'Export CSV (no PII)', 'jcp-core' ); ?></a>
			<span class="description"><?php echo esc_html( sprintf( __( 'Cohort: %d sessions', 'jcp-core' ), (int) ( $report['cohort_size'] ?? 0 ) ) ); ?></span>
		</div>

		<?php
		$cards = [
			'visitors'          => __( 'Starts / visitors', 'jcp-core' ),
			'survey_starts'     => __( 'Assessment started', 'jcp-core' ),
			'survey_completion' => __( 'Assessment complete', 'jcp-core' ),
			'emails_captured'   => __( 'Email captures', 'jcp-core' ),
			'product_reveal'    => __( 'Product reveal reach', 'jcp-core' ),
			'trial_plan_views'  => __( 'Trial plan views', 'jcp-core' ),
			'trial_cta_clicks'  => __( 'Trial CTA clicks', 'jcp-core' ),
			'trials_started'    => __( 'Trial starts', 'jcp-core' ),
			'paid_customers'    => __( 'Paid', 'jcp-core' ),
		];
		?>
		<div class="jcp-fa__cards">
			<?php foreach ( $cards as $key => $label ) : ?>
				<?php $val = $summary[ $key ] ?? null; ?>
				<div class="jcp-fa__card">
					<div class="jcp-fa__card-label"><?php echo esc_html( $label ); ?></div>
					<div class="jcp-fa__card-value"><?php echo $nc( $val ); ?></div>
					<?php echo $delta_html( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $rates ) ) : ?>
			<div class="jcp-fa__rates">
				<span><?php esc_html_e( 'Start → email', 'jcp-core' ); ?>: <strong><?php echo $rates['start_to_email'] === null ? '—' : esc_html( (string) $rates['start_to_email'] ) . '%'; ?></strong></span>
				<span><?php esc_html_e( 'Reveal reach', 'jcp-core' ); ?>: <strong><?php echo $rates['reveal_reach'] === null ? '—' : esc_html( (string) $rates['reveal_reach'] ) . '%'; ?></strong></span>
				<span><?php esc_html_e( 'Email → trial CTA', 'jcp-core' ); ?>: <strong><?php echo $rates['email_to_trial_cta'] === null ? '—' : esc_html( (string) $rates['email_to_trial_cta'] ) . '%'; ?></strong></span>
				<span><?php esc_html_e( 'Start → trial CTA', 'jcp-core' ); ?>: <strong><?php echo $rates['start_to_trial_cta'] === null ? '—' : esc_html( (string) $rates['start_to_trial_cta'] ) . '%'; ?></strong></span>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Funnel visualization', 'jcp-core' ); ?></h2>
		<div class="jcp-fa-funnel" role="list">
			<?php if ( empty( $stages ) ) : ?>
				<p><?php esc_html_e( 'No sessions in this window yet.', 'jcp-core' ); ?></p>
			<?php else : ?>
				<?php foreach ( $stages as $stage ) : ?>
					<?php
					if ( ! empty( $stage['not_connected'] ) ) {
						continue;
					}
					$sess = (int) ( $stage['sessions'] ?? 0 );
					$width = $landing_sessions > 0 ? max( 4, round( ( $sess / $landing_sessions ) * 100 ) ) : ( $sess > 0 ? 100 : 4 );
					$drop_hot = isset( $stage['drop_pct'] ) && (float) $stage['drop_pct'] >= 25;
					?>
					<div class="jcp-fa-funnel__node<?php echo $drop_hot ? ' is-drop' : ''; ?>" role="listitem">
						<div class="jcp-fa-funnel__label"><?php echo esc_html( (string) $stage['label'] ); ?></div>
						<div class="jcp-fa-funnel__users"><?php echo esc_html( (string) $sess ); ?></div>
						<div class="jcp-fa-funnel__bar" aria-hidden="true"><span style="width:<?php echo (float) $width; ?>%"></span></div>
						<div class="jcp-fa-funnel__meta">
							<?php echo esc_html( (string) ( $stage['from_previous'] ?? '—' ) ); ?>% step
							· <?php echo esc_html( (string) ( $stage['from_landing'] ?? '—' ) ); ?>% cum
							<?php if ( isset( $stage['drop_pct'] ) && (float) $stage['drop_pct'] > 0 ) : ?>
								· <span class="<?php echo $drop_hot ? 'is-drop' : ''; ?>"><?php echo esc_html( (string) $stage['drop_pct'] ); ?>% drop</span>
							<?php endif; ?>
							<?php if ( ! empty( $stage['median_ms'] ) ) : ?>
								· <?php echo esc_html( jcp_funnel_analytics_format_ms( $stage['median_ms'] ) ); ?> med
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<h2><?php esc_html_e( 'Drop-off table', 'jcp-core' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Stage', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Step conv.', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Drop-off', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Drop %', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'From entry', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Median time', 'jcp-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $stages ) ) : ?>
				<tr><td colspan="7"><?php esc_html_e( 'No sessions in this window yet.', 'jcp-core' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $stages as $stage ) : ?>
					<tr>
						<td><strong><?php echo esc_html( (string) $stage['label'] ); ?></strong></td>
						<td>
							<?php
							if ( ! empty( $stage['not_connected'] ) ) {
								echo esc_html__( 'Not connected', 'jcp-core' );
							} else {
								echo esc_html( (string) (int) $stage['sessions'] );
							}
							?>
						</td>
						<td><?php echo ! empty( $stage['not_connected'] ) ? '—' : esc_html( (string) $stage['from_previous'] ) . '%'; ?></td>
						<td><?php echo ! empty( $stage['not_connected'] ) ? '—' : esc_html( (string) (int) $stage['drop'] ); ?></td>
						<td><?php echo ! empty( $stage['not_connected'] ) ? '—' : esc_html( (string) $stage['drop_pct'] ) . '%'; ?></td>
						<td><?php echo ! empty( $stage['not_connected'] ) ? '—' : esc_html( (string) $stage['from_landing'] ) . '%'; ?></td>
						<td><?php echo ! empty( $stage['not_connected'] ) ? '—' : esc_html( jcp_funnel_analytics_format_ms( $stage['median_ms'] ?? null ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( ! empty( $report['questions'] ) ) : ?>
			<h2><?php esc_html_e( 'Answer distributions', 'jcp-core' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Question', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Viewed', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Answered', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Answer rate', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Drop after view', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Median time', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Top answers', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $report['questions'] as $q ) : ?>
					<tr>
						<td><strong><?php echo esc_html( (string) $q['question'] ); ?></strong></td>
						<td><?php echo (int) $q['viewed']; ?></td>
						<td><?php echo (int) $q['answered']; ?></td>
						<td>
							<span class="jcp-fa-pctbar" aria-hidden="true"><span style="width:<?php echo min( 100, (float) $q['answer_rate'] ); ?>%"></span></span>
							<?php echo esc_html( (string) $q['answer_rate'] ); ?>%
						</td>
						<td><?php echo (int) $q['drop_after_view']; ?></td>
						<td><?php echo esc_html( jcp_funnel_analytics_format_ms( $q['median_ms'] ?? null ) ); ?></td>
						<td>
							<?php
							$bits = [];
							$total_ans = max( 1, (int) $q['answered'] );
							foreach ( $q['top_answers'] as $a ) {
								$pct = round( ( (int) $a['count'] / $total_ans ) * 100, 1 );
								$bits[] = esc_html( $a['value'] ) . ' ' . (int) $a['count'] . ' (' . esc_html( (string) $pct ) . '%)';
							}
							echo $bits ? implode( ' · ', $bits ) : '—';
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( ! empty( $report['destinations'] ) ) : ?>
			<h2><?php esc_html_e( 'Product destination engagement', 'jcp-core' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Unique sessions that tapped each destination (first tap per destination only — no autoplay).', 'jcp-core' ); ?></p>
			<?php
			$di = $report['destination_insight'] ?? [];
			if ( ! empty( $di ) ) :
				?>
				<p class="description">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: avg destinations, 2: session count */
							__( 'Avg destinations viewed: %1$s across %2$d sessions with a tab tap.', 'jcp-core' ),
							(string) ( $di['avg_per_session'] ?? 0 ),
							(int) ( $di['sessions_with_dest'] ?? 0 )
						)
					);
					?>
				</p>
			<?php endif; ?>
			<table class="widefat striped" style="max-width:720px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Destination', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'First destination', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$first_map = [];
				foreach ( $di['first'] ?? [] as $f ) {
					$first_map[ (string) $f['destination'] ] = (int) $f['sessions'];
				}
				foreach ( $report['destinations'] as $d ) :
					$dest = (string) $d['destination'];
					?>
					<tr>
						<td><strong><?php echo esc_html( ucfirst( $dest ) ); ?></strong></td>
						<td><?php echo (int) $d['sessions']; ?></td>
						<td><?php echo (int) ( $first_map[ $dest ] ?? 0 ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( ! empty( $report['devices'] ) ) : ?>
			<h2><?php esc_html_e( 'Device breakdown', 'jcp-core' ); ?></h2>
			<table class="widefat striped" style="max-width:420px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Device', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $report['devices'] as $d ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $d['device'] ); ?></td>
						<td><?php echo (int) $d['sessions']; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( ! empty( $report['traffic'] ) ) : ?>
			<h2><?php esc_html_e( 'Traffic / creative breakdown', 'jcp-core' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Source', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Campaign', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Content / Ad', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Landing', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Starts', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Survey %', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Email %', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Trial CTA %', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Trial start %', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Paid %', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $report['traffic'] as $t ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $t['source'] ); ?></td>
						<td><?php echo esc_html( (string) $t['campaign'] ); ?></td>
						<td><?php echo esc_html( (string) $t['content'] ); ?></td>
						<td><?php echo (int) $t['landing_sessions']; ?></td>
						<td><?php echo (int) $t['starts']; ?></td>
						<td><?php echo esc_html( (string) $t['survey_completion'] ); ?>%</td>
						<td><?php echo esc_html( (string) $t['email_pct'] ); ?>%</td>
						<td><?php echo esc_html( (string) $t['trial_cta_pct'] ); ?>%</td>
						<td><?php echo $nc( $t['trial_start_pct'] ); ?></td>
						<td><?php echo $nc( $t['paid_pct'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php $diag = $report['diagnostics'] ?? []; ?>
		<?php if ( ! empty( $diag ) ) : ?>
			<h2><?php esc_html_e( 'Data quality', 'jcp-core' ); ?></h2>
			<table class="widefat striped" style="max-width:720px;">
				<tbody>
					<tr><th><?php esc_html_e( 'Sessions missing source (30d)', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['missing_source_sessions'] ?? 0 ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Sessions missing campaign (30d)', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['missing_campaign_sessions'] ?? 0 ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Duplicate event IDs rejected', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['duplicate_rejected'] ?? 0 ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Events missing funnel_id', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['missing_funnel_id'] ?? 0 ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Events missing session_id', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['missing_session_id'] ?? 0 ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Last event received', 'jcp-core' ); ?></th><td><?php echo esc_html( (string) ( $diag['last_event_received'] ?? '—' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Event count last 24h', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['events_last_24h'] ?? 0 ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Analytics endpoint', 'jcp-core' ); ?></th><td><code><?php echo esc_html( (string) ( $diag['endpoint_status'] ?? '' ) ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Raw event retention', 'jcp-core' ); ?></th><td><?php echo (int) ( $diag['retention_days'] ?? 120 ); ?> <?php esc_html_e( 'days', 'jcp-core' ); ?></td></tr>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
