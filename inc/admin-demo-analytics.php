<?php
/**
 * Admin: Demo Analytics section (read-only funnel, CTA counts, completion rate).
 * Under JCP Theme Settings. No styling controls, no export.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add JCP menu and Demo Analytics page.
 */
function jcp_demo_analytics_admin_menu(): void {
    add_menu_page(
        __( 'JCP Theme Settings', 'jcp-core' ),
        __( 'JCP', 'jcp-core' ),
        'edit_posts',
        'jcp-theme-settings',
        'jcp_theme_docs_render_page',
        'dashicons-chart-bar',
        59
    );
    add_submenu_page(
        'jcp-theme-settings',
        __( 'Demo Analytics', 'jcp-core' ),
        __( 'Demo Analytics', 'jcp-core' ),
        'manage_options',
        'jcp-demo-analytics',
        'jcp_demo_analytics_render_page'
    );
}

/**
 * Render Demo Analytics page: funnel table, drop-off %, CTA counts, completion rate.
 */
function jcp_demo_analytics_render_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $stats = jcp_demo_analytics_get_stats();
    $data_since = $stats['data_since'] ?? null;
    $avg_seconds = isset( $stats['avg_time_to_completion_seconds'] ) ? (int) $stats['avg_time_to_completion_seconds'] : null;
    $median_seconds = isset( $stats['median_time_to_completion_seconds'] ) ? (int) $stats['median_time_to_completion_seconds'] : null;
    $primary_dropoff = $stats['primary_dropoff'] ?? null;
    $total_sessions = (int) $stats['total_sessions'];
    $reset_nonce = wp_create_nonce( 'jcp_demo_analytics_reset' );
    $sessions_nonce = wp_create_nonce( 'jcp_demo_analytics_sessions' );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Demo Analytics', 'jcp-core' ); ?></h1>
        <p><?php esc_html_e( 'Read-only funnel and CTA metrics. No styling controls.', 'jcp-core' ); ?></p>

        <p>
            <?php
            if ( $data_since !== null && $data_since !== '' ) {
                echo esc_html( sprintf( __( 'Data since: %s', 'jcp-core' ), $data_since ) );
            } else {
                esc_html_e( 'No demo sessions recorded yet.', 'jcp-core' );
            }
            ?>
        </p>

        <?php if ( $total_sessions > 0 && $total_sessions < 50 ) : ?>
        <p style="color: #646970;"><em><?php esc_html_e( 'Low sample size. Trends may not be statistically reliable.', 'jcp-core' ); ?></em></p>
        <?php endif; ?>

        <?php
        $demo_conversions = (int) ( $stats['demo_conversions'] ?? 0 );
        $conversion_rate  = (float) ( $stats['conversion_rate'] ?? 0 );
        $post_demo_shown  = (int) ( $stats['post_demo_shown'] ?? 0 );
        $post_demo_conversion_rate = (float) ( $stats['post_demo_conversion_rate'] ?? 0 );
        $post_demo_cta_rates = $stats['post_demo_cta_rates'] ?? [];
        $business_type_dist = $stats['business_type_distribution'] ?? [];
        $demo_goals_dist    = $stats['demo_goals_distribution'] ?? [];
        $landing_page_dist  = $stats['landing_page_distribution'] ?? [];
        $utm_source_dist    = $stats['utm_source_distribution'] ?? [];
        ?>
        <div class="jcp-demo-analytics-cols" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
            <div class="jcp-demo-analytics-col" style="min-width: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 18px; box-shadow: 0 1px 1px rgba(0,0,0,.04); align-self: start;">
                <h2 style="margin: 0 0 14px 0; font-size: 1.1em; color: #1d2327; font-weight: 600;"><?php esc_html_e( 'Overall', 'jcp-core' ); ?></h2>
                <div class="jcp-demo-conversion-box" style="margin-bottom: 16px; padding: 14px 16px; border: 1px solid #2271b1; border-radius: 4px; background: #f0f6fc;">
                    <p style="margin: 0 0 6px 0; font-size: 11px; color: #50575e; text-transform: uppercase; letter-spacing: 0.02em;"><?php esc_html_e( 'Demo → Start Free Trial', 'jcp-core' ); ?></p>
                    <?php if ( $total_sessions === 0 ) : ?>
                        <p style="margin: 0; font-size: 15px; font-weight: 600; color: #1d2327;"><?php esc_html_e( 'No demo sessions yet.', 'jcp-core' ); ?></p>
                    <?php elseif ( $demo_conversions === 0 ) : ?>
                        <p style="margin: 0; font-size: 15px; font-weight: 600; color: #1d2327;"><?php esc_html_e( 'No Start Free Trial conversions recorded yet.', 'jcp-core' ); ?></p>
                    <?php else : ?>
                        <p style="margin: 0; font-size: 18px; font-weight: 700; color: #2271b1;"><button type="button" class="button-link" id="jcp-demo-analytics-sessions-converted" data-filter="converted" style="font-size: 18px; font-weight: 700; color: #2271b1;"><?php echo esc_html( sprintf( __( '%1$d of %2$d demos converted (%3$s%%)', 'jcp-core' ), $demo_conversions, $total_sessions, (string) $conversion_rate ) ); ?></button></p>
                        <?php if ( $post_demo_shown > 0 ) : ?>
                        <p style="margin: 6px 0 0 0; font-size: 12px; color: #50575e;"><?php echo esc_html( sprintf( __( '%1$s%% of completed demos clicked Start Free Trial', 'jcp-core' ), (string) $post_demo_conversion_rate ) ); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <table class="widefat striped" style="width: 100%; margin-bottom: 0;">
                    <tbody>
                        <tr>
                            <td style="font-weight: 500;"><?php esc_html_e( 'Total sessions (started)', 'jcp-core' ); ?></td>
                            <td><button type="button" class="button-link" id="jcp-demo-analytics-sessions-all" data-filter="all"><?php echo (int) $stats['total_sessions']; ?></button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500;"><?php esc_html_e( 'Reached end-screen', 'jcp-core' ); ?></td>
                            <td><?php echo (int) $post_demo_shown; ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500;"><?php esc_html_e( 'Demo completion rate', 'jcp-core' ); ?></td>
                            <td style="font-weight: 600;"><?php echo esc_html( (string) $stats['completion_rate'] ); ?>%</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500;"><?php esc_html_e( 'Average time to completion', 'jcp-core' ); ?></td>
                            <td><?php echo $avg_seconds !== null ? esc_html( jcp_demo_analytics_format_seconds( $avg_seconds ) ) : esc_html__( 'Not enough data yet', 'jcp-core' ); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500;"><?php esc_html_e( 'Median time to completion', 'jcp-core' ); ?></td>
                            <td><?php echo $median_seconds !== null ? esc_html( jcp_demo_analytics_format_seconds( $median_seconds ) ) : esc_html__( 'Not enough data yet', 'jcp-core' ); ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php if ( $primary_dropoff !== null && ! empty( $primary_dropoff['label'] ) ) : ?>
                <div style="margin-top: 16px; padding: 12px 14px; background: #fff8e5; border-left: 4px solid #dba617; border-radius: 0 3px 3px 0;">
                    <p style="margin: 0; font-size: 11px; color: #646970; text-transform: uppercase; letter-spacing: 0.03em;"><?php esc_html_e( 'Primary drop-off point', 'jcp-core' ); ?></p>
                    <p style="margin: 4px 0 0 0; font-size: 14px; font-weight: 700; color: #1d2327;"><?php echo esc_html( $primary_dropoff['label'] ); ?> <span style="color: #d63638;">(<?php echo esc_html( (string) $primary_dropoff['dropoff'] ); ?>%)</span></p>
                </div>
                <?php endif; ?>
            </div>
            <div class="jcp-demo-analytics-col" style="min-width: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 18px; box-shadow: 0 1px 1px rgba(0,0,0,.04); align-self: start;">
                <h2 style="margin: 0 0 14px 0; font-size: 1.1em; color: #1d2327; font-weight: 600;"><?php esc_html_e( 'End-screen CTAs', 'jcp-core' ); ?></h2>
                <p class="description" style="margin: 0 0 10px 0; font-size: 12px; color: #646970;"><?php esc_html_e( 'Clicks after the post-demo panel. Rate = % of sessions that reached the end screen.', 'jcp-core' ); ?></p>
                <table class="widefat striped" style="width: 100%; margin-bottom: 16px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Button', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( 'Clicks', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( 'Rate', 'jcp-core' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $post_demo_cta_rates ) ) : ?>
                            <?php foreach ( $post_demo_cta_rates as $row ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $row['label'] ); ?></td>
                                    <td><?php echo (int) $row['count']; ?></td>
                                    <td><?php echo esc_html( (string) $row['pct'] ); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="3"><em><?php esc_html_e( 'No end-screen clicks yet.', 'jcp-core' ); ?></em></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <h3 style="margin: 0 0 10px 0; font-size: 13px; color: #50575e;"><?php esc_html_e( 'Other CTA clicks', 'jcp-core' ); ?></h3>
                <table class="widefat striped" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'View listing in directory', 'jcp-core' ); ?></td>
                            <td><?php echo (int) ( $stats['cta_counts']['view_directory'] ?? 0 ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'View main directory', 'jcp-core' ); ?></td>
                            <td><?php echo (int) ( $stats['cta_counts']['view_main_directory'] ?? 0 ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="jcp-demo-analytics-col" style="min-width: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 18px; box-shadow: 0 1px 1px rgba(0,0,0,.04); align-self: start;">
                <h2 style="margin: 0 0 14px 0; font-size: 1.1em; color: #1d2327; font-weight: 600;"><?php esc_html_e( 'Business type', 'jcp-core' ); ?></h2>
                <?php if ( ! empty( $business_type_dist ) ) : ?>
                <table class="widefat striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Business type', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( '%', 'jcp-core' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $business_type_dist as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( $row['label'] ); ?></td>
                                <td><?php echo (int) $row['count']; ?></td>
                                <td><?php echo esc_html( (string) $row['pct'] ); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p><em><?php esc_html_e( 'No business types recorded yet. Data appears when visitors complete the demo survey (step 1: business name and type).', 'jcp-core' ); ?></em></p>
                <?php endif; ?>
            </div>
            <div class="jcp-demo-analytics-col" style="min-width: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 18px; box-shadow: 0 1px 1px rgba(0,0,0,.04); align-self: start;">
                <h2 style="margin: 0 0 14px 0; font-size: 1.1em; color: #1d2327; font-weight: 600;"><?php esc_html_e( 'What should this demo prove?', 'jcp-core' ); ?></h2>
                <p class="description" style="margin: 0 0 10px 0; font-size: 12px; color: #646970;"><?php esc_html_e( '% of sessions per answer (step 2; up to 2 choices).', 'jcp-core' ); ?></p>
                <?php if ( ! empty( $demo_goals_dist ) ) : ?>
                <table class="widefat striped" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Answer', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( '%', 'jcp-core' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $demo_goals_dist as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( $row['label'] ); ?></td>
                                <td><?php echo (int) $row['count']; ?></td>
                                <td><?php echo esc_html( (string) $row['pct'] ); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p><em><?php esc_html_e( 'No demo goals recorded yet. Data appears when visitors complete survey step 2 ("What should this demo prove?").', 'jcp-core' ); ?></em></p>
                <?php endif; ?>
            </div>
        </div>

        <h2 style="margin-top: 32px;"><?php esc_html_e( 'Where they came from', 'jcp-core' ); ?></h2>
        <p class="description"><?php esc_html_e( 'First-touch landing page and UTM source captured when the lead entered the site (before /demo). Older sessions may show Unknown until new traffic arrives.', 'jcp-core' ); ?></p>
        <div class="jcp-demo-analytics-cols" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 12px;">
            <div class="jcp-demo-analytics-col" style="min-width: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 18px; box-shadow: 0 1px 1px rgba(0,0,0,.04); align-self: start;">
                <h3 style="margin: 0 0 12px 0; font-size: 1em;"><?php esc_html_e( 'Landing page', 'jcp-core' ); ?></h3>
                <?php if ( ! empty( $landing_page_dist ) ) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Page', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( '%', 'jcp-core' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $landing_page_dist as $row ) : ?>
                            <tr>
                                <td>
                                    <?php echo esc_html( $row['label'] ); ?>
                                    <?php if ( ! empty( $row['value'] ) ) : ?>
                                        <br><code style="font-size:11px;color:#646970;"><?php echo esc_html( $row['value'] ); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo (int) $row['count']; ?></td>
                                <td><?php echo esc_html( (string) $row['pct'] ); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p><em><?php esc_html_e( 'No landing-page data yet.', 'jcp-core' ); ?></em></p>
                <?php endif; ?>
            </div>
            <div class="jcp-demo-analytics-col" style="min-width: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 18px; box-shadow: 0 1px 1px rgba(0,0,0,.04); align-self: start;">
                <h3 style="margin: 0 0 12px 0; font-size: 1em;"><?php esc_html_e( 'UTM source', 'jcp-core' ); ?></h3>
                <?php if ( ! empty( $utm_source_dist ) ) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Source', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></th>
                            <th><?php esc_html_e( '%', 'jcp-core' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $utm_source_dist as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( $row['label'] ); ?></td>
                                <td><?php echo (int) $row['count']; ?></td>
                                <td><?php echo esc_html( (string) $row['pct'] ); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p><em><?php esc_html_e( 'No UTM source data yet.', 'jcp-core' ); ?></em></p>
                <?php endif; ?>
            </div>
        </div>

        <h2 style="margin-top: 32px;"><?php esc_html_e( 'Funnel completion & drop-off', 'jcp-core' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Matches the live demo: single-screen gate → guided steps 1–5 → outcomes → end screen. Drop-off % is from the previous linear step (replay is a side action).', 'jcp-core' ); ?></p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Step', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Sessions reached', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( '% of started', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Drop-off from prior step', 'jcp-core' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $stats['funnel'] as $row ) : ?>
                    <?php
                    $drop = (float) ( $row['dropoff'] ?? 0 );
                    $is_side = ! empty( $row['side'] );
                    $drop_style = $is_side ? 'color:#646970;' : ( $drop >= 25 ? 'color:#d63638;font-weight:700;' : ( $drop >= 10 ? 'color:#996800;font-weight:600;' : '' ) );
                    ?>
                    <tr<?php echo $is_side ? ' style="opacity:0.85;"' : ''; ?>>
                        <td><?php echo esc_html( $row['step'] ); ?></td>
                        <td><?php echo (int) $row['count']; ?></td>
                        <td><?php echo esc_html( (string) $row['pct'] ); ?>%</td>
                        <td style="<?php echo esc_attr( $drop_style ); ?>">
                            <?php
                            if ( $is_side ) {
                                esc_html_e( 'n/a', 'jcp-core' );
                            } else {
                                echo esc_html( (string) $row['dropoff'] ) . '%';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $recent_leads = jcp_demo_analytics_get_sessions( 'all', 50 );
        ?>
        <h2 style="margin-top: 32px;"><?php esc_html_e( 'Leads (recent sessions)', 'jcp-core' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Contact email, name, company, niche, and landing page for ops inspection. Click a row to open the event timeline.', 'jcp-core' ); ?></p>
        <?php if ( empty( $recent_leads ) ) : ?>
            <p><em><?php esc_html_e( 'No demo sessions recorded yet.', 'jcp-core' ); ?></em></p>
        <?php else : ?>
        <table class="widefat striped" id="jcp-demo-analytics-leads-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Email', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Name', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Company', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Niche', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Came from', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Last step', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Trial', 'jcp-core' ); ?></th>
                    <th><?php esc_html_e( 'Started', 'jcp-core' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $recent_leads as $lead ) : ?>
                    <tr class="jcp-demo-analytics-lead-row" data-session-id="<?php echo esc_attr( (string) ( $lead['session_id'] ?? '' ) ); ?>" style="cursor:pointer;">
                        <td><?php echo esc_html( $lead['contact_email'] ?: '—' ); ?></td>
                        <td><?php echo esc_html( $lead['contact_name'] ?: '—' ); ?></td>
                        <td><?php echo esc_html( $lead['business_name'] ?: '—' ); ?></td>
                        <td><?php echo esc_html( $lead['business_type_display'] ?: '—' ); ?></td>
                        <td>
                            <?php echo esc_html( $lead['landing_page_display'] ?: __( 'Unknown', 'jcp-core' ) ); ?>
                            <?php if ( ! empty( $lead['utm_source'] ) ) : ?>
                                <br><span style="color:#646970;font-size:12px;"><?php echo esc_html( (string) $lead['utm_source'] ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $lead['last_step'] ?: '—' ); ?></td>
                        <td><?php echo ! empty( $lead['demo_converted'] ) ? esc_html__( 'Yes', 'jcp-core' ) : esc_html__( 'No', 'jcp-core' ); ?></td>
                        <td><?php echo esc_html( (string) ( $lead['demo_started_at'] ?? '' ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <p style="margin-top: 32px;">
            <button type="button" class="button" id="jcp-demo-analytics-reset-btn"><?php esc_html_e( 'Reset demo analytics', 'jcp-core' ); ?></button>
        </p>

        <div id="jcp-demo-analytics-reset-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
            <div style="background: #fff; margin: 120px auto; padding: 24px; max-width: 400px; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <p><strong><?php esc_html_e( 'This will permanently clear all demo analytics data.', 'jcp-core' ); ?></strong></p>
                <p><?php esc_html_e( 'This action cannot be undone.', 'jcp-core' ); ?></p>
                <p>
                    <button type="button" class="button button-primary" id="jcp-demo-analytics-reset-confirm"><?php esc_html_e( 'Confirm reset', 'jcp-core' ); ?></button>
                    <button type="button" class="button" id="jcp-demo-analytics-reset-cancel"><?php esc_html_e( 'Cancel', 'jcp-core' ); ?></button>
                </p>
            </div>
        </div>

        <div id="jcp-demo-analytics-sessions-modal" style="display: none; position: fixed; z-index: 100001; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
            <div style="background: #fff; margin: 40px auto; padding: 24px; max-width: 960px; max-height: 80vh; overflow: auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <p style="margin: 0 0 16px 0;"><strong id="jcp-demo-analytics-sessions-modal-title"><?php esc_html_e( 'Sessions', 'jcp-core' ); ?></strong></p>
                <div id="jcp-demo-analytics-sessions-content">
                    <p><?php esc_html_e( 'Loading…', 'jcp-core' ); ?></p>
                </div>
                <p style="margin-top: 16px;"><button type="button" class="button" id="jcp-demo-analytics-sessions-close"><?php esc_html_e( 'Close', 'jcp-core' ); ?></button></p>
            </div>
        </div>

        <div id="jcp-demo-analytics-detail-modal" style="display: none; position: fixed; z-index: 100002; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
            <div style="background: #fff; margin: 40px auto; padding: 24px; max-width: 720px; max-height: 80vh; overflow: auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <p style="margin: 0 0 16px 0;"><strong id="jcp-demo-analytics-detail-title"><?php esc_html_e( 'Session detail', 'jcp-core' ); ?></strong></p>
                <div id="jcp-demo-analytics-detail-content">
                    <p><?php esc_html_e( 'Loading…', 'jcp-core' ); ?></p>
                </div>
                <p style="margin-top: 16px;"><button type="button" class="button" id="jcp-demo-analytics-detail-close"><?php esc_html_e( 'Close', 'jcp-core' ); ?></button></p>
            </div>
        </div>

        <script>
        (function() {
            var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
            var sessionsNonce = '<?php echo esc_js( $sessions_nonce ); ?>';
            var detailNonce = '<?php echo esc_js( wp_create_nonce( 'jcp_demo_analytics_session_detail' ) ); ?>';

            var resetBtn = document.getElementById('jcp-demo-analytics-reset-btn');
            var resetModal = document.getElementById('jcp-demo-analytics-reset-modal');
            var confirmBtn = document.getElementById('jcp-demo-analytics-reset-confirm');
            var cancelBtn = document.getElementById('jcp-demo-analytics-reset-cancel');
            if (resetBtn && resetModal && confirmBtn && cancelBtn) {
                resetBtn.addEventListener('click', function() { resetModal.style.display = 'block'; });
                cancelBtn.addEventListener('click', function() { resetModal.style.display = 'none'; });
                resetModal.addEventListener('click', function(e) { if (e.target === resetModal) resetModal.style.display = 'none'; });
                confirmBtn.addEventListener('click', function() {
                    confirmBtn.disabled = true;
                    var formData = new FormData();
                    formData.append('action', 'jcp_demo_analytics_reset');
                    formData.append('nonce', '<?php echo esc_js( $reset_nonce ); ?>');
                    fetch(ajaxUrl, { method: 'POST', body: formData })
                        .then(function(r) { return r.json(); })
                        .then(function(data) { if (data && data.success) window.location.reload(); else confirmBtn.disabled = false; })
                        .catch(function() { confirmBtn.disabled = false; });
                });
            }

            function escapeHtml(s) { if (s == null) return ''; var div = document.createElement('div'); div.textContent = s; return div.innerHTML; }
            function shortHash(sid) { return (sid && sid.length >= 8) ? sid.substring(0, 8) : (sid || '—'); }
            function relativeTime(iso) {
                if (!iso) return '—';
                var d = new Date(iso); var n = new Date(); var sec = Math.floor((n - d) / 1000);
                if (sec < 60) return '<?php echo esc_js( __( 'Just now', 'jcp-core' ) ); ?>';
                if (sec < 3600) return Math.floor(sec / 60) + ' <?php echo esc_js( __( 'min ago', 'jcp-core' ) ); ?>';
                if (sec < 86400) return Math.floor(sec / 3600) + ' <?php echo esc_js( __( 'hours ago', 'jcp-core' ) ); ?>';
                return Math.floor(sec / 86400) + ' <?php echo esc_js( __( 'days ago', 'jcp-core' ) ); ?>';
            }

            var sessionsModal = document.getElementById('jcp-demo-analytics-sessions-modal');
            var sessionsContent = document.getElementById('jcp-demo-analytics-sessions-content');
            var sessionsTitle = document.getElementById('jcp-demo-analytics-sessions-modal-title');
            var sessionsClose = document.getElementById('jcp-demo-analytics-sessions-close');
            function openSessionsModal(filter) {
                if (!sessionsModal || !sessionsContent) return;
                sessionsContent.innerHTML = '<p><?php echo esc_js( __( 'Loading…', 'jcp-core' ) ); ?></p>';
                if (sessionsTitle) sessionsTitle.textContent = filter === 'converted' ? '<?php echo esc_js( __( 'Demo conversions', 'jcp-core' ) ); ?>' : '<?php echo esc_js( __( 'Total sessions started', 'jcp-core' ) ); ?>';
                sessionsModal.style.display = 'block';
                var formData = new FormData();
                formData.append('action', 'jcp_demo_analytics_sessions');
                formData.append('nonce', sessionsNonce);
                formData.append('filter', filter === 'converted' ? 'converted' : 'all');
                fetch(ajaxUrl, { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!sessionsContent) return;
                        if (!data || !data.success || !Array.isArray(data.data)) {
                            sessionsContent.innerHTML = '<p><?php echo esc_js( __( 'No demo sessions recorded yet.', 'jcp-core' ) ); ?></p>';
                            return;
                        }
                        var rows = data.data;
                        if (rows.length === 0) {
                            sessionsContent.innerHTML = '<p><?php echo esc_js( __( 'No demo sessions recorded yet.', 'jcp-core' ) ); ?></p>';
                            return;
                        }
                        var html = '<table class="widefat striped"><thead><tr><th><?php echo esc_js( __( 'Email', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Name', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Company', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Niche', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Came from', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Last step', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'End-screen CTAs', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Trial', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Started', 'jcp-core' ) ); ?></th></tr></thead><tbody>';
                        for (var i = 0; i < rows.length; i++) {
                            var r = rows[i];
                            var ctas = (r.post_demo_ctas_display && r.post_demo_ctas_display.length) ? r.post_demo_ctas_display.join(', ') : '—';
                            var cameFrom = escapeHtml(r.landing_page_display || '<?php echo esc_js( __( 'Unknown', 'jcp-core' ) ); ?>');
                            if (r.utm_source) cameFrom += '<br><span style="color:#646970;font-size:12px;">' + escapeHtml(r.utm_source) + '</span>';
                            html += '<tr class="jcp-demo-analytics-lead-row" data-session-id="' + escapeHtml(r.session_id || '') + '" style="cursor:pointer;">' +
                                '<td>' + escapeHtml(r.contact_email || '—') + '</td>' +
                                '<td>' + escapeHtml(r.contact_name || '—') + '</td>' +
                                '<td>' + escapeHtml(r.business_name || '—') + '</td>' +
                                '<td>' + escapeHtml(r.business_type_display || '—') + '</td>' +
                                '<td>' + cameFrom + '</td>' +
                                '<td>' + escapeHtml(r.last_step || '—') + '</td>' +
                                '<td>' + escapeHtml(ctas) + '</td>' +
                                '<td>' + (r.demo_converted ? '<?php echo esc_js( __( 'Yes', 'jcp-core' ) ); ?>' : '<?php echo esc_js( __( 'No', 'jcp-core' ) ); ?>') + '</td>' +
                                '<td>' + escapeHtml(relativeTime(r.demo_started_at)) + '</td></tr>';
                        }
                        html += '</tbody></table>';
                        sessionsContent.innerHTML = html;
                    })
                    .catch(function() {
                        if (sessionsContent) sessionsContent.innerHTML = '<p><?php echo esc_js( __( 'No demo sessions recorded yet.', 'jcp-core' ) ); ?></p>';
                    });
            }
            document.getElementById('jcp-demo-analytics-sessions-all') && document.getElementById('jcp-demo-analytics-sessions-all').addEventListener('click', function() { openSessionsModal('all'); });
            document.getElementById('jcp-demo-analytics-sessions-converted') && document.getElementById('jcp-demo-analytics-sessions-converted').addEventListener('click', function() { openSessionsModal('converted'); });
            if (sessionsClose) sessionsClose.addEventListener('click', function() { if (sessionsModal) sessionsModal.style.display = 'none'; });
            if (sessionsModal) sessionsModal.addEventListener('click', function(e) { if (e.target === sessionsModal) sessionsModal.style.display = 'none'; });

            var detailModal = document.getElementById('jcp-demo-analytics-detail-modal');
            var detailContent = document.getElementById('jcp-demo-analytics-detail-content');
            var detailTitle = document.getElementById('jcp-demo-analytics-detail-title');
            var detailClose = document.getElementById('jcp-demo-analytics-detail-close');
            function openSessionDetail(sessionId) {
                if (!detailModal || !detailContent || !sessionId) return;
                detailContent.innerHTML = '<p><?php echo esc_js( __( 'Loading…', 'jcp-core' ) ); ?></p>';
                if (detailTitle) detailTitle.textContent = '<?php echo esc_js( __( 'Session detail', 'jcp-core' ) ); ?> · ' + shortHash(sessionId);
                detailModal.style.display = 'block';
                var formData = new FormData();
                formData.append('action', 'jcp_demo_analytics_session_detail');
                formData.append('nonce', detailNonce);
                formData.append('session_id', sessionId);
                fetch(ajaxUrl, { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!detailContent) return;
                        if (!data || !data.success || !data.data) {
                            detailContent.innerHTML = '<p><?php echo esc_js( __( 'Session not found.', 'jcp-core' ) ); ?></p>';
                            return;
                        }
                        var d = data.data;
                        var html = '<table class="widefat" style="margin-bottom:16px;"><tbody>' +
                            '<tr><th><?php echo esc_js( __( 'Email', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.contact_email || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Name', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.contact_name || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Company', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.business_name || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Niche', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.business_type_display || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Came from', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.landing_page_display || '<?php echo esc_js( __( 'Unknown', 'jcp-core' ) ); ?>') + (d.landing_page ? '<br><code style="font-size:11px;color:#646970;">' + escapeHtml(d.landing_page) + '</code>' : '') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Referrer', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.referrer_display || d.referrer || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'UTM source', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.utm_source || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'UTM campaign', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.utm_campaign || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Last step', 'jcp-core' ) ); ?></th><td>' + escapeHtml(d.last_step || '—') + '</td></tr>' +
                            '<tr><th><?php echo esc_js( __( 'Converted', 'jcp-core' ) ); ?></th><td>' + (d.demo_converted ? '<?php echo esc_js( __( 'Yes', 'jcp-core' ) ); ?>' : '<?php echo esc_js( __( 'No', 'jcp-core' ) ); ?>') + '</td></tr>' +
                            '</tbody></table>';
                        html += '<h3 style="margin:0 0 8px 0;font-size:14px;"><?php echo esc_js( __( 'Event timeline', 'jcp-core' ) ); ?></h3>';
                        if (!d.events || !d.events.length) {
                            html += '<p><em><?php echo esc_js( __( 'No events recorded for this session.', 'jcp-core' ) ); ?></em></p>';
                        } else {
                            html += '<table class="widefat striped"><thead><tr><th><?php echo esc_js( __( 'Time', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Event', 'jcp-core' ) ); ?></th><th><?php echo esc_js( __( 'Detail', 'jcp-core' ) ); ?></th></tr></thead><tbody>';
                            for (var i = 0; i < d.events.length; i++) {
                                var ev = d.events[i];
                                html += '<tr><td>' + escapeHtml(ev.created_at || '—') + '</td><td>' + escapeHtml(ev.label || ev.event_type || '') + '</td><td>' + escapeHtml(ev.detail || '') + '</td></tr>';
                            }
                            html += '</tbody></table>';
                        }
                        detailContent.innerHTML = html;
                    })
                    .catch(function() {
                        if (detailContent) detailContent.innerHTML = '<p><?php echo esc_js( __( 'Session not found.', 'jcp-core' ) ); ?></p>';
                    });
            }
            document.addEventListener('click', function(e) {
                var row = e.target && e.target.closest ? e.target.closest('.jcp-demo-analytics-lead-row') : null;
                if (row && row.getAttribute('data-session-id')) {
                    openSessionDetail(row.getAttribute('data-session-id'));
                }
            });
            if (detailClose) detailClose.addEventListener('click', function() { if (detailModal) detailModal.style.display = 'none'; });
            if (detailModal) detailModal.addEventListener('click', function(e) { if (e.target === detailModal) detailModal.style.display = 'none'; });
        })();
        </script>
    </div>
    <?php
}

/**
 * AJAX handler for reset demo analytics.
 */
function jcp_demo_analytics_ajax_reset(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
    }
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jcp_demo_analytics_reset' ) ) {
        wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
    }
    $done = jcp_demo_analytics_reset();
    if ( $done ) {
        wp_send_json_success();
    }
    wp_send_json_error( [ 'message' => 'Reset failed' ], 500 );
}

/**
 * AJAX handler for session list (read-only). manage_options required.
 */
function jcp_demo_analytics_ajax_sessions(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
    }
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jcp_demo_analytics_sessions' ) ) {
        wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
    }
    $filter = isset( $_POST['filter'] ) ? sanitize_text_field( wp_unslash( $_POST['filter'] ) ) : 'all';
    if ( $filter !== 'all' && $filter !== 'converted' ) {
        $filter = 'all';
    }
    $sessions = jcp_demo_analytics_get_sessions( $filter, 50 );
    wp_send_json_success( $sessions );
}

/**
 * AJAX handler for single-session detail + timeline. manage_options required.
 */
function jcp_demo_analytics_ajax_session_detail(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
    }
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jcp_demo_analytics_session_detail' ) ) {
        wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
    }
    $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
    $detail = jcp_demo_analytics_get_session_detail( $session_id );
    if ( $detail === null ) {
        wp_send_json_error( [ 'message' => 'Not found' ], 404 );
    }
    wp_send_json_success( $detail );
}

add_action( 'admin_menu', 'jcp_demo_analytics_admin_menu' );
add_action( 'wp_ajax_jcp_demo_analytics_reset', 'jcp_demo_analytics_ajax_reset' );
add_action( 'wp_ajax_jcp_demo_analytics_sessions', 'jcp_demo_analytics_ajax_sessions' );
add_action( 'wp_ajax_jcp_demo_analytics_session_detail', 'jcp_demo_analytics_ajax_session_detail' );
