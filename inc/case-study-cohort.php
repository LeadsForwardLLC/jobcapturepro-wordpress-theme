<?php
/**
 * 90-day case study cohort: capacity helpers + page bar + exit-intent enqueue.
 *
 * Capacity metric = admin-edited applicant spots filled toward a cohort of 10.
 * Urgency also uses an application-window close date (countdown).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize YYYY-MM-DD close date (empty allowed).
 */
function jcp_case_study_sanitize_closes_at( string $raw ): string {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return '';
	}
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
		return '';
	}
	$ts = strtotime( $raw . ' 23:59:59' );
	return $ts ? gmdate( 'Y-m-d', $ts ) : '';
}

/**
 * Application window close date (Y-m-d), end-of-day site timezone.
 */
function jcp_case_study_closes_at(): string {
	$settings = function_exists( 'jcp_global_settings' ) ? jcp_global_settings() : [];
	$raw      = isset( $settings['case_study']['closes_at'] )
		? (string) $settings['case_study']['closes_at']
		: '2026-09-30';
	$clean = jcp_case_study_sanitize_closes_at( $raw );
	return $clean !== '' ? $clean : '2026-09-30';
}

/**
 * Unix timestamp for application window close (site timezone end of day).
 */
function jcp_case_study_closes_at_ts(): int {
	$date = jcp_case_study_closes_at();
	$tz   = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
	try {
		$dt = new DateTimeImmutable( $date . ' 23:59:59', $tz );
		return (int) $dt->getTimestamp();
	} catch ( Exception $e ) {
		return (int) strtotime( $date . ' 23:59:59 UTC' );
	}
}

/**
 * Fixed cohort size for the public case study.
 */
function jcp_case_study_spots_total(): int {
	return 10;
}

/**
 * Number of applicant spots already filled (0–10).
 */
function jcp_case_study_spots_claimed(): int {
	$settings = function_exists( 'jcp_global_settings' ) ? jcp_global_settings() : [];
	$claimed  = isset( $settings['case_study']['spots_claimed'] )
		? (int) $settings['case_study']['spots_claimed']
		: 0;
	$total = jcp_case_study_spots_total();
	return max( 0, min( $total, $claimed ) );
}

/**
 * Applicant spots still open.
 */
function jcp_case_study_spots_remaining(): int {
	return max( 0, jcp_case_study_spots_total() - jcp_case_study_spots_claimed() );
}

/**
 * Fill percent for the capacity bar (0–100).
 */
function jcp_case_study_capacity_percent(): int {
	$total = jcp_case_study_spots_total();
	if ( $total <= 0 ) {
		return 0;
	}
	return (int) round( ( jcp_case_study_spots_claimed() / $total ) * 100 );
}

/**
 * Case study page URL with optional utm_content.
 */
function jcp_case_study_url( string $utm_content = '' ): string {
	$url = home_url( '/case-study/' );
	if ( $utm_content !== '' ) {
		$url = add_query_arg( 'utm_content', sanitize_key( $utm_content ), $url );
	}
	return $url;
}

/**
 * Whether the current request is the case-study page.
 */
function jcp_case_study_is_current_page(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	return get_post_field( 'post_name', get_queried_object_id() ) === 'case-study';
}

/**
 * Render the public capacity indicator markup.
 *
 * @param string $context CSS modifier (page|sticky|modal).
 */
function jcp_case_study_render_capacity_bar( string $context = 'page' ): void {
	$claimed   = jcp_case_study_spots_claimed();
	$total     = jcp_case_study_spots_total();
	$remaining = jcp_case_study_spots_remaining();
	$percent   = jcp_case_study_capacity_percent();
	$closes_ts = jcp_case_study_closes_at_ts();
	$mod       = sanitize_html_class( $context );
	?>
	<div
		class="jcp-case-capacity jcp-case-capacity--<?php echo esc_attr( $mod ); ?>"
		role="status"
		aria-live="polite"
		data-spots-claimed="<?php echo esc_attr( (string) $claimed ); ?>"
		data-spots-total="<?php echo esc_attr( (string) $total ); ?>"
		data-closes-at="<?php echo esc_attr( (string) $closes_ts ); ?>"
	>
		<div class="jcp-case-capacity__head">
			<p class="jcp-case-capacity__label">
				<strong><?php echo esc_html( sprintf( /* translators: 1: filled count, 2: total */ __( '%1$d of %2$d applicant spots filled', 'jcp-core' ), $claimed, $total ) ); ?></strong>
			</p>
			<p class="jcp-case-capacity__meta">
				<?php
				if ( $remaining <= 0 ) {
					esc_html_e( 'Cohort goal reached · Late applications go to the waitlist', 'jcp-core' );
				} else {
					echo esc_html(
						sprintf(
							/* translators: %d: remaining applicant spots */
							_n( '%d more application needed', '%d more applications needed', $remaining, 'jcp-core' ),
							$remaining
						)
					);
					echo ' · ';
					esc_html_e( 'Window closes when spots fill or the deadline hits', 'jcp-core' );
				}
				?>
			</p>
			<p class="jcp-case-capacity__countdown" data-jcp-case-countdown="<?php echo esc_attr( (string) $closes_ts ); ?>">
				<?php esc_html_e( 'Application window closing…', 'jcp-core' ); ?>
			</p>
		</div>
		<div class="jcp-case-capacity__track" aria-hidden="true">
			<span class="jcp-case-capacity__fill" style="width: <?php echo esc_attr( (string) $percent ); ?>%;"></span>
		</div>
	</div>
	<?php
}

/**
 * Inject capacity bar early on the case-study page (called from header after nav).
 */
function jcp_case_study_print_capacity_on_page(): void {
	if ( ! jcp_case_study_is_current_page() ) {
		return;
	}
	echo '<div class="jcp-case-capacity-wrap">';
	jcp_case_study_render_capacity_bar( 'page' );
	echo '</div>';
}

/**
 * Whether desktop exit-intent should load on this request.
 */
function jcp_case_study_should_load_exit_intent(): bool {
	if ( is_admin() || wp_is_json_request() ) {
		return false;
	}
	if ( jcp_case_study_is_current_page() ) {
		return false;
	}
	$pages = function_exists( 'jcp_core_get_page_detection' ) ? jcp_core_get_page_detection() : [];
	// Gate-only /demo/ (no mode=run): skip — don't interrupt opt-in.
	if ( function_exists( 'jcp_core_is_demo_survey_request' ) && jcp_core_is_demo_survey_request() ) {
		return false;
	}
	// Interactive demo run: show last-resort exit intent (after outcomes / exit attempts).
	if ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ) {
		return true;
	}
	if ( ! empty( $pages['is_demo'] ) ) {
		return false;
	}
	// Paid campaign LPs hide site chrome but still need the last-resort exit intent.
	if ( function_exists( 'jcp_page_current_is_campaign_landing' ) && jcp_page_current_is_campaign_landing() ) {
		return true;
	}
	if ( ! empty( $pages['is_home'] ) ) {
		return true;
	}
	return false;
}

/**
 * Enqueue capacity CSS on case-study + exit-intent assets on marketing pages.
 */
function jcp_case_study_enqueue_assets(): void {
	$on_case = jcp_case_study_is_current_page();
	$exit    = jcp_case_study_should_load_exit_intent();
	if ( ! $on_case && ! $exit ) {
		return;
	}

	jcp_core_enqueue_style( 'jcp-core-case-study-cohort', 'css/components/case-study-cohort.css', [ 'jcp-core-base' ] );
	jcp_core_enqueue_script( 'jcp-core-case-study-countdown', 'js/features/case-study-countdown.js', [] );

	if ( $exit ) {
		// Demo run skips marketing enqueue; register base so cohort CSS still prints.
		if ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ) {
			jcp_core_enqueue_style( 'jcp-core-base', 'css/base.css' );
		}
		jcp_core_enqueue_script( 'jcp-core-case-study-exit', 'js/features/case-study-exit-intent.js', [ 'jcp-core-case-study-countdown' ] );
		wp_localize_script(
			'jcp-core-case-study-exit',
			'JCP_CASE_STUDY',
			[
				'url'            => jcp_case_study_url( 'exit_intent' ),
				'spotsClaimed'   => jcp_case_study_spots_claimed(),
				'spotsTotal'     => jcp_case_study_spots_total(),
				'spotsRemaining' => jcp_case_study_spots_remaining(),
				'closesAtTs'     => jcp_case_study_closes_at_ts(),
				'delayMs'        => function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ? 8000 : 18000,
				'minWidth'       => 1024,
				'allowDemo'      => function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request(),
			]
		);
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_case_study_enqueue_assets', 40 );
