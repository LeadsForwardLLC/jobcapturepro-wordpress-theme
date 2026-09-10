<?php
/**
 * 90-day case study cohort: capacity helpers + page bar + exit-intent enqueue.
 *
 * Public story: we are selecting 10 businesses. Application volume is shown as a
 * fill % (admin-edited) — applications may far exceed 10; only 10 are selected.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How many businesses we will select (fixed public number).
 */
function jcp_case_study_selecting_total(): int {
	return 10;
}

/**
 * Alias kept for older callers.
 */
function jcp_case_study_spots_total(): int {
	return jcp_case_study_selecting_total();
}

/**
 * Application pipeline fill percent (0–100). Default 80.
 * Migrates legacy spots_claimed (0–10) when the new key is missing.
 */
function jcp_case_study_applications_fill_percent(): int {
	$settings = function_exists( 'jcp_global_settings' ) ? jcp_global_settings() : [];
	$cs       = isset( $settings['case_study'] ) && is_array( $settings['case_study'] ) ? $settings['case_study'] : [];

	if ( array_key_exists( 'applications_fill_percent', $cs ) ) {
		return max( 0, min( 100, (int) $cs['applications_fill_percent'] ) );
	}

	// Legacy: spots_claimed / 10 → percent (0 stayed 0; treat unset as default 80).
	if ( array_key_exists( 'spots_claimed', $cs ) && (int) $cs['spots_claimed'] > 0 ) {
		$total = jcp_case_study_selecting_total();
		return max( 0, min( 100, (int) round( ( (int) $cs['spots_claimed'] / max( 1, $total ) ) * 100 ) ) );
	}

	return 80;
}

/**
 * @deprecated Use jcp_case_study_applications_fill_percent().
 */
function jcp_case_study_capacity_percent(): int {
	return jcp_case_study_applications_fill_percent();
}

/**
 * @deprecated Legacy spots model — returns approximate claimed from fill %.
 */
function jcp_case_study_spots_claimed(): int {
	return (int) round( ( jcp_case_study_applications_fill_percent() / 100 ) * jcp_case_study_selecting_total() );
}

/**
 * @deprecated
 */
function jcp_case_study_spots_remaining(): int {
	return max( 0, jcp_case_study_selecting_total() - jcp_case_study_spots_claimed() );
}

/**
 * Sanitize YYYY-MM-DD close date (empty allowed). Kept for settings compat; not shown publicly.
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
	$percent  = jcp_case_study_applications_fill_percent();
	$selecting = jcp_case_study_selecting_total();
	$mod       = sanitize_html_class( $context );
	?>
	<div
		class="jcp-case-capacity jcp-case-capacity--<?php echo esc_attr( $mod ); ?>"
		role="status"
		aria-live="polite"
		data-fill-percent="<?php echo esc_attr( (string) $percent ); ?>"
		data-selecting-total="<?php echo esc_attr( (string) $selecting ); ?>"
	>
		<div class="jcp-case-capacity__head">
			<p class="jcp-case-capacity__label">
				<strong><?php echo esc_html( sprintf( /* translators: %d: fill percent */ __( 'Applications are %d%% full', 'jcp-core' ), $percent ) ); ?></strong>
			</p>
			<p class="jcp-case-capacity__meta">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of businesses selected */
						__( 'We’re only selecting %d businesses', 'jcp-core' ),
						$selecting
					)
				);
				?>
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
	if ( function_exists( 'jcp_core_is_demo_survey_request' ) && jcp_core_is_demo_survey_request() ) {
		return false;
	}
	if ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ) {
		return true;
	}
	if ( ! empty( $pages['is_demo'] ) ) {
		return false;
	}
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

	if ( $exit ) {
		if ( function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ) {
			jcp_core_enqueue_style( 'jcp-core-base', 'css/base.css' );
		}
		jcp_core_enqueue_script( 'jcp-core-case-study-exit', 'js/features/case-study-exit-intent.js', [] );
		wp_localize_script(
			'jcp-core-case-study-exit',
			'JCP_CASE_STUDY',
			[
				'url'            => jcp_case_study_url( 'exit_intent' ),
				'fillPercent'    => jcp_case_study_applications_fill_percent(),
				'selectingTotal' => jcp_case_study_selecting_total(),
				'delayMs'        => function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request() ? 8000 : 18000,
				'minWidth'       => 1024,
				'allowDemo'      => function_exists( 'jcp_core_is_demo_run_request' ) && jcp_core_is_demo_run_request(),
			]
		);
	}
}
add_action( 'wp_enqueue_scripts', 'jcp_case_study_enqueue_assets', 40 );
