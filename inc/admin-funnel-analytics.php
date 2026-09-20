<?php
/**
 * Admin: Funnel Analytics (proof_gap unique-session funnel, no PII).
 * Submenu under JCP (same parent as Demo Analytics).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Funnel Analytics under the JCP menu created by demo analytics.
 */
function jcp_funnel_analytics_admin_menu(): void {
	add_submenu_page(
		'jcp-theme-settings',
		__( 'Funnel Analytics', 'jcp-core' ),
		__( 'Funnel Analytics', 'jcp-core' ),
		'manage_options',
		'jcp-funnel-analytics',
		'jcp_funnel_analytics_render_page'
	);
}

/**
 * Parse admin filter args from GET (defaults: proof_gap, last 30 days).
 *
 * @return array{funnel_id: string, date_from: string, date_to: string, utm_source: string, utm_campaign: string, range: string}
 */
function jcp_funnel_analytics_admin_filter_args(): array {
	$funnel_id = isset( $_GET['funnel'] ) ? sanitize_key( wp_unslash( (string) $_GET['funnel'] ) ) : 'proof_gap';
	if ( ! in_array( $funnel_id, [ 'proof_gap', 'demo' ], true ) ) {
		$funnel_id = 'proof_gap';
	}

	$range = isset( $_GET['range'] ) ? sanitize_key( wp_unslash( (string) $_GET['range'] ) ) : '30d';
	if ( ! in_array( $range, [ '7d', '30d', '90d', 'custom' ], true ) ) {
		$range = '30d';
	}

	$today = wp_date( 'Y-m-d' );
	$days  = 30;
	if ( $range === '7d' ) {
		$days = 7;
	} elseif ( $range === '90d' ) {
		$days = 90;
	}

	$date_to   = $today;
	$date_from = wp_date( 'Y-m-d', strtotime( '-' . ( $days - 1 ) . ' days' ) );

	if ( $range === 'custom' ) {
		if ( ! empty( $_GET['date_from'] ) ) {
			$df = sanitize_text_field( wp_unslash( (string) $_GET['date_from'] ) );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $df ) ) {
				$date_from = $df;
			}
		}
		if ( ! empty( $_GET['date_to'] ) ) {
			$dt = sanitize_text_field( wp_unslash( (string) $_GET['date_to'] ) );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dt ) ) {
				$date_to = $dt;
			}
		}
	}

	$utm_source   = isset( $_GET['utm_source'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['utm_source'] ) ) : '';
	$utm_campaign = isset( $_GET['utm_campaign'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['utm_campaign'] ) ) : '';

	return [
		'funnel_id'    => $funnel_id,
		'date_from'    => $date_from,
		'date_to'      => $date_to,
		'utm_source'   => $utm_source,
		'utm_campaign' => $utm_campaign,
		'range'        => $range,
	];
}

/**
 * Render Funnel Analytics admin page.
 */
function jcp_funnel_analytics_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$filters = jcp_funnel_analytics_admin_filter_args();
	$report  = jcp_funnel_analytics_get_report( $filters );
	$summary = $report['summary'] ?? [];
	$stages  = $report['stages'] ?? [];
	$qstats  = $report['question_stats'] ?? [];
	$traffic = $report['source_breakdown'] ?? [];
	$quality = $report['data_quality'] ?? [];
	$is_stub = ( $report['status'] ?? '' ) === 'adapter_stub';

	$card = 'min-width:0;background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:18px;box-shadow:0 1px 1px rgba(0,0,0,.04);align-self:start;';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Funnel Analytics', 'jcp-core' ); ?></h1>
		<p><?php esc_html_e( 'First-party unique-session funnel for proof_gap. No PII is stored in funnel events.', 'jcp-core' ); ?></p>

		<form method="get" action="" style="margin: 16px 0 24px; padding: 16px 18px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
			<input type="hidden" name="page" value="jcp-funnel-analytics" />
			<div style="display: flex; flex-wrap: wrap; gap: 12px 16px; align-items: flex-end;">
				<label>
					<span style="display:block;font-size:11px;color:#646970;text-transform:uppercase;margin-bottom:4px;"><?php esc_html_e( 'Funnel', 'jcp-core' ); ?></span>
					<select name="funnel">
						<option value="proof_gap" <?php selected( $filters['funnel_id'], 'proof_gap' ); ?>><?php esc_html_e( 'proof_gap', 'jcp-core' ); ?></option>
						<option value="demo" <?php selected( $filters['funnel_id'], 'demo' ); ?>><?php esc_html_e( 'demo (adapter stub)', 'jcp-core' ); ?></option>
					</select>
				</label>
				<label>
					<span style="display:block;font-size:11px;color:#646970;text-transform:uppercase;margin-bottom:4px;"><?php esc_html_e( 'Date range', 'jcp-core' ); ?></span>
					<select name="range" id="jcp-funnel-range">
						<option value="7d" <?php selected( $filters['range'], '7d' ); ?>><?php esc_html_e( 'Last 7 days', 'jcp-core' ); ?></option>
						<option value="30d" <?php selected( $filters['range'], '30d' ); ?>><?php esc_html_e( 'Last 30 days', 'jcp-core' ); ?></option>
						<option value="90d" <?php selected( $filters['range'], '90d' ); ?>><?php esc_html_e( 'Last 90 days', 'jcp-core' ); ?></option>
						<option value="custom" <?php selected( $filters['range'], 'custom' ); ?>><?php esc_html_e( 'Custom', 'jcp-core' ); ?></option>
					</select>
				</label>
				<label>
					<span style="display:block;font-size:11px;color:#646970;text-transform:uppercase;margin-bottom:4px;"><?php esc_html_e( 'From', 'jcp-core' ); ?></span>
					<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
				</label>
				<label>
					<span style="display:block;font-size:11px;color:#646970;text-transform:uppercase;margin-bottom:4px;"><?php esc_html_e( 'To', 'jcp-core' ); ?></span>
					<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
				</label>
				<label>
					<span style="display:block;font-size:11px;color:#646970;text-transform:uppercase;margin-bottom:4px;"><?php esc_html_e( 'utm_source', 'jcp-core' ); ?></span>
					<input type="text" name="utm_source" value="<?php echo esc_attr( $filters['utm_source'] ); ?>" placeholder="<?php esc_attr_e( 'Any', 'jcp-core' ); ?>" style="min-width:140px;" />
				</label>
				<label>
					<span style="display:block;font-size:11px;color:#646970;text-transform:uppercase;margin-bottom:4px;"><?php esc_html_e( 'utm_campaign', 'jcp-core' ); ?></span>
					<input type="text" name="utm_campaign" value="<?php echo esc_attr( $filters['utm_campaign'] ); ?>" placeholder="<?php esc_attr_e( 'Any', 'jcp-core' ); ?>" style="min-width:140px;" />
				</label>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'jcp-core' ); ?></button>
			</div>
		</form>

		<?php if ( $is_stub ) : ?>
			<div style="padding:14px 16px;background:#f0f6fc;border:1px solid #2271b1;border-radius:4px;margin-bottom:20px;">
				<p style="margin:0;"><?php echo esc_html( (string) ( $report['message'] ?? '' ) ); ?></p>
			</div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
			<div style="<?php echo esc_attr( $card ); ?>">
				<p style="margin:0 0 6px;font-size:11px;color:#646970;text-transform:uppercase;"><?php esc_html_e( 'Landing sessions', 'jcp-core' ); ?></p>
				<p style="margin:0;font-size:28px;font-weight:700;color:#1d2327;"><?php echo (int) ( $summary['total_sessions'] ?? 0 ); ?></p>
			</div>
			<div style="<?php echo esc_attr( $card ); ?>">
				<p style="margin:0 0 6px;font-size:11px;color:#646970;text-transform:uppercase;"><?php esc_html_e( 'Started', 'jcp-core' ); ?></p>
				<p style="margin:0;font-size:28px;font-weight:700;color:#1d2327;"><?php echo (int) ( $summary['started'] ?? 0 ); ?></p>
				<p style="margin:6px 0 0;font-size:12px;color:#50575e;"><?php echo esc_html( (string) ( $summary['start_rate'] ?? 0 ) ); ?>% <?php esc_html_e( 'of landing', 'jcp-core' ); ?></p>
			</div>
			<div style="<?php echo esc_attr( $card ); ?>">
				<p style="margin:0 0 6px;font-size:11px;color:#646970;text-transform:uppercase;"><?php esc_html_e( 'Email submitted', 'jcp-core' ); ?></p>
				<p style="margin:0;font-size:28px;font-weight:700;color:#1d2327;"><?php echo (int) ( $summary['email_submitted'] ?? 0 ); ?></p>
				<p style="margin:6px 0 0;font-size:12px;color:#50575e;"><?php echo esc_html( (string) ( $summary['email_rate'] ?? 0 ) ); ?>% <?php esc_html_e( 'of started', 'jcp-core' ); ?></p>
			</div>
			<div style="<?php echo esc_attr( $card ); ?>">
				<p style="margin:0 0 6px;font-size:11px;color:#646970;text-transform:uppercase;"><?php esc_html_e( 'Trial CTA clicked', 'jcp-core' ); ?></p>
				<p style="margin:0;font-size:28px;font-weight:700;color:#1d2327;"><?php echo (int) ( $summary['trial_clicked'] ?? 0 ); ?></p>
				<p style="margin:6px 0 0;font-size:12px;color:#50575e;"><?php echo esc_html( (string) ( $summary['trial_rate'] ?? 0 ) ); ?>% <?php esc_html_e( 'of email', 'jcp-core' ); ?></p>
			</div>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px;">
			<div style="<?php echo esc_attr( $card ); ?>">
				<h2 style="margin:0 0 8px;font-size:1.1em;"><?php esc_html_e( 'Activation', 'jcp-core' ); ?></h2>
				<p style="margin:0;color:#646970;"><?php esc_html_e( 'Status: not_connected', 'jcp-core' ); ?></p>
				<p class="description" style="margin:8px 0 0;"><?php esc_html_e( 'Product activation is not wired to this funnel yet.', 'jcp-core' ); ?></p>
			</div>
			<div style="<?php echo esc_attr( $card ); ?>">
				<h2 style="margin:0 0 8px;font-size:1.1em;"><?php esc_html_e( 'Paid', 'jcp-core' ); ?></h2>
				<p style="margin:0;color:#646970;"><?php esc_html_e( 'Status: not_connected', 'jcp-core' ); ?></p>
				<p class="description" style="margin:8px 0 0;"><?php esc_html_e( 'Paid conversion is not wired to this funnel yet.', 'jcp-core' ); ?></p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Stage funnel', 'jcp-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Unique sessions per stage. Drop-off % is from the previous stage.', 'jcp-core' ); ?></p>
		<?php if ( empty( $stages ) ) : ?>
			<p><em><?php esc_html_e( 'No funnel stages for this selection.', 'jcp-core' ); ?></em></p>
		<?php else : ?>
		<table class="widefat striped" style="margin-top:8px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Stage', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Unique sessions', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( '% of landing', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Drop-off from prior', 'jcp-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $stages as $i => $row ) : ?>
					<?php
					$drop       = (float) ( $row['dropoff'] ?? 0 );
					$drop_style = $i === 0 ? 'color:#646970;' : ( $drop >= 25 ? 'color:#d63638;font-weight:700;' : ( $drop >= 10 ? 'color:#996800;font-weight:600;' : '' ) );
					?>
					<tr>
						<td><?php echo esc_html( (string) ( $row['label'] ?? '' ) ); ?></td>
						<td><?php echo (int) ( $row['sessions'] ?? 0 ); ?></td>
						<td><?php echo esc_html( (string) ( $row['pct'] ?? 0 ) ); ?>%</td>
						<td style="<?php echo esc_attr( $drop_style ); ?>">
							<?php
							if ( $i === 0 ) {
								echo '—';
							} else {
								echo esc_html( (string) $drop ) . '%';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'Question answers', 'jcp-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Unique sessions per answer_key within each question_id (SurveyQuestionAnswered).', 'jcp-core' ); ?></p>
		<?php if ( empty( $qstats ) ) : ?>
			<p><em><?php esc_html_e( 'No question answers recorded for this filter window.', 'jcp-core' ); ?></em></p>
		<?php else : ?>
		<table class="widefat striped" style="margin-top:8px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Question', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Answer', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( '% of question', 'jcp-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $qstats as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( (string) ( $row['question_id'] ?? '' ) ); ?></code></td>
						<td><?php echo esc_html( (string) ( $row['answer_key'] ?? '' ) ); ?></td>
						<td><?php echo (int) ( $row['sessions'] ?? 0 ); ?></td>
						<td><?php echo esc_html( (string) ( $row['pct'] ?? 0 ) ); ?>%</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'Traffic sources', 'jcp-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Unique sessions by utm_source × utm_campaign.', 'jcp-core' ); ?></p>
		<?php if ( empty( $traffic ) ) : ?>
			<p><em><?php esc_html_e( 'No traffic rows for this filter window.', 'jcp-core' ); ?></em></p>
		<?php else : ?>
		<table class="widefat striped" style="margin-top:8px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'utm_source', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'utm_campaign', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( '%', 'jcp-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $traffic as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $row['utm_source'] !== '' ? $row['utm_source'] : __( 'Direct / none', 'jcp-core' ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $row['utm_campaign'] !== '' ? $row['utm_campaign'] : '—' ) ); ?></td>
						<td><?php echo (int) ( $row['sessions'] ?? 0 ); ?></td>
						<td><?php echo esc_html( (string) ( $row['pct'] ?? 0 ) ); ?>%</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'Data quality', 'jcp-core' ); ?></h2>
		<div style="<?php echo esc_attr( $card ); ?> max-width:720px;">
			<?php if ( empty( $quality ) ) : ?>
				<p style="margin:0;"><em><?php esc_html_e( 'No quality metrics for this selection.', 'jcp-core' ); ?></em></p>
			<?php else : ?>
			<table class="widefat striped" style="margin:0;">
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Total events', 'jcp-core' ); ?></td>
						<td><?php echo (int) ( $quality['total_events'] ?? 0 ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Unique sessions', 'jcp-core' ); ?></td>
						<td><?php echo (int) ( $quality['total_sessions'] ?? 0 ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Sessions with utm_source', 'jcp-core' ); ?></td>
						<td><?php echo (int) ( $quality['sessions_with_utm'] ?? 0 ); ?> (<?php echo esc_html( (string) ( $quality['utm_coverage_pct'] ?? 0 ) ); ?>%)</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Sessions with device_class', 'jcp-core' ); ?></td>
						<td><?php echo (int) ( $quality['sessions_with_device'] ?? 0 ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Sessions with fbclid/ttclid flag', 'jcp-core' ); ?></td>
						<td><?php echo (int) ( $quality['sessions_with_click_id'] ?? 0 ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Oldest event in range', 'jcp-core' ); ?></td>
						<td><?php echo esc_html( (string) ( $quality['oldest_event'] ?? '—' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Newest event in range', 'jcp-core' ); ?></td>
						<td><?php echo esc_html( (string) ( $quality['newest_event'] ?? '—' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Retention', 'jcp-core' ); ?></td>
						<td><?php echo esc_html( sprintf( __( '%d days', 'jcp-core' ), (int) ( $quality['retention_days'] ?? 120 ) ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PII columns in funnel table', 'jcp-core' ); ?></td>
						<td><?php esc_html_e( 'None', 'jcp-core' ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

add_action( 'admin_menu', 'jcp_funnel_analytics_admin_menu' );
