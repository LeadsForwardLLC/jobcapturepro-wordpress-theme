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
	$nc      = static function ( $v ) {
		if ( $v === null ) {
			return esc_html__( 'Not connected', 'jcp-core' );
		}
		return esc_html( (string) $v );
	};
	?>
	<div class="wrap jcp-funnel-analytics">
		<h1><?php esc_html_e( 'Funnel Analytics', 'jcp-core' ); ?></h1>
		<p class="description"><?php echo esc_html( (string) ( $report['calculation_note'] ?? '' ) ); ?></p>

		<form method="get" style="margin:16px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:end;">
			<input type="hidden" name="page" value="jcp-funnel-analytics" />
			<label>Funnel
				<select name="funnel">
					<?php foreach ( $funnels as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['funnel'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>Date range
				<select name="days">
					<?php foreach ( [ 7, 14, 30, 90 ] as $d ) : ?>
						<option value="<?php echo (int) $d; ?>" <?php selected( (int) $filters['days'], $d ); ?>><?php echo esc_html( sprintf( __( 'Last %d days', 'jcp-core' ), $d ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>UTM source <input type="text" name="utm_source" value="<?php echo esc_attr( (string) $filters['utm_source'] ); ?>" placeholder="facebook" /></label>
			<label>UTM campaign <input type="text" name="utm_campaign" value="<?php echo esc_attr( (string) $filters['utm_campaign'] ); ?>" /></label>
			<label>UTM content / ad <input type="text" name="utm_content" value="<?php echo esc_attr( (string) $filters['utm_content'] ); ?>" /></label>
			<label>lp_variant <input type="text" name="lp_variant" value="<?php echo esc_attr( (string) $filters['lp_variant'] ); ?>" /></label>
			<label>Trade <input type="text" name="trade" value="<?php echo esc_attr( (string) $filters['trade'] ); ?>" /></label>
			<label>Device
				<select name="device">
					<option value=""><?php esc_html_e( 'All', 'jcp-core' ); ?></option>
					<?php foreach ( [ 'mobile', 'tablet', 'desktop' ] as $dev ) : ?>
						<option value="<?php echo esc_attr( $dev ); ?>" <?php selected( (string) $filters['device'], $dev ); ?>><?php echo esc_html( ucfirst( $dev ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button class="button button-primary" type="submit"><?php esc_html_e( 'Apply', 'jcp-core' ); ?></button>
		</form>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin:16px 0;">
			<?php
			$cards = [
				'Visitors'           => $summary['visitors'] ?? 0,
				'Survey starts'      => $summary['survey_starts'] ?? 0,
				'Survey completion'  => $summary['survey_completion'] ?? 0,
				'Emails captured'    => $summary['emails_captured'] ?? null,
				'Product reveal'     => $summary['product_reveal'] ?? null,
				'Trial CTA clicks'   => $summary['trial_cta_clicks'] ?? null,
				'Trials started'     => $summary['trials_started'] ?? null,
				'Activated trials'   => $summary['activated_trials'] ?? null,
				'Paid customers'     => $summary['paid_customers'] ?? null,
			];
			foreach ( $cards as $label => $val ) :
				?>
				<div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:14px;">
					<div style="font-size:12px;color:#646970;"><?php echo esc_html( $label ); ?></div>
					<div style="font-size:22px;font-weight:700;margin-top:4px;"><?php echo $nc( $val ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<h2><?php esc_html_e( 'Funnel stages (unique sessions)', 'jcp-core' ); ?></h2>
		<table class="widefat striped" style="max-width:1100px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Stage', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'From previous', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Drop-off', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'Drop %', 'jcp-core' ); ?></th>
					<th><?php esc_html_e( 'From landing', 'jcp-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $stages ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No sessions in this window yet.', 'jcp-core' ); ?></td></tr>
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
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( ! empty( $report['questions'] ) ) : ?>
			<h2 style="margin-top:28px;"><?php esc_html_e( 'Question analytics', 'jcp-core' ); ?></h2>
			<table class="widefat striped" style="max-width:1100px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Question', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Viewed', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Answered', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Answer rate', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Drop after view', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Median time', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Most common answers', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $report['questions'] as $q ) : ?>
					<tr>
						<td><strong><?php echo esc_html( (string) $q['question'] ); ?></strong></td>
						<td><?php echo (int) $q['viewed']; ?></td>
						<td><?php echo (int) $q['answered']; ?></td>
						<td><?php echo esc_html( (string) $q['answer_rate'] ); ?>%</td>
						<td><?php echo (int) $q['drop_after_view']; ?></td>
						<td><?php echo $q['median_ms'] === null ? esc_html__( 'n/a yet', 'jcp-core' ) : esc_html( (string) $q['median_ms'] ) . ' ms'; ?></td>
						<td>
							<?php
							$bits = [];
							foreach ( $q['top_answers'] as $a ) {
								$bits[] = esc_html( $a['value'] ) . ' (' . (int) $a['count'] . ')';
							}
							echo $bits ? implode( ', ', $bits ) : '—';
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( ! empty( $report['destinations'] ) ) : ?>
			<h2 style="margin-top:28px;"><?php esc_html_e( 'Product destination engagement', 'jcp-core' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Unique sessions that tapped each destination tab (user action only — no autoplay views).', 'jcp-core' ); ?></p>
			<table class="widefat striped" style="max-width:640px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Destination', 'jcp-core' ); ?></th>
						<th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $report['destinations'] as $d ) : ?>
					<tr>
						<td><strong><?php echo esc_html( ucfirst( (string) $d['destination'] ) ); ?></strong></td>
						<td><?php echo (int) $d['sessions']; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( ! empty( $report['traffic'] ) ) : ?>
			<h2 style="margin-top:28px;"><?php esc_html_e( 'Traffic quality', 'jcp-core' ); ?></h2>
			<table class="widefat striped" style="max-width:1200px;">
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
						<th><?php esc_html_e( 'Activation %', 'jcp-core' ); ?></th>
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
						<td><?php echo $nc( $t['activation_pct'] ); ?></td>
						<td><?php echo $nc( $t['paid_pct'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php $diag = $report['diagnostics'] ?? []; ?>
		<?php if ( ! empty( $diag ) ) : ?>
			<h2 style="margin-top:28px;"><?php esc_html_e( 'Data quality', 'jcp-core' ); ?></h2>
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
