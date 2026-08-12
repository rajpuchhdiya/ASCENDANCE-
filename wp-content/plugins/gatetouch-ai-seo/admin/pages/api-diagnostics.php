<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.

$status       = get_option( 'gatetouch_api_status', 'pending' );
$error_count  = (int) get_option( 'gatetouch_api_error_count', 0 );
$usage        = get_option( 'gatetouch_api_usage', [] );
$is_safe_mode = $error_count >= 5;

// Calculate totals
$total_tokens = 0;
$total_cost   = 0;
foreach ( $usage as $day ) {
    $total_tokens += $day['tokens'] ?? 0;
    $total_cost   += $day['cost'] ?? 0;
}
// Rendered as the AI Diagnostics tab inside help-center.php, which supplies the
// page wrapper and header. Kept renderable standalone for the legacy
// ?page=gatetouch-diagnostics URL, which is why the wrapper is conditional.
$gt_standalone = ! isset( $gt_help_tabs );

// Used by both render paths, so it must be resolved outside the wrapper.
$active_provider = get_option( 'gatetouch_ai_provider', 'openai' );
$provider_labels = [ 'openai' => 'OpenAI', 'anthropic' => 'Anthropic', 'gemini' => 'Google Gemini' ];
$provider_label  = $provider_labels[ $active_provider ] ?? ucfirst( $active_provider );
?>

<?php if ( $gt_standalone ) : ?>
<div class="gatetouch-admin-wrap">
    <?php
    GateTouch_Helpers::page_header(
        __( 'AI Health & Diagnostics', 'gatetouch-ai-seo' ),
        sprintf(
            /* translators: %s: AI provider name */
            __( 'Monitor your %s API connectivity, usage, and system logs.', 'gatetouch-ai-seo' ),
            esc_html( $provider_label )
        )
    );
    ?>
<?php endif; ?>

    <div class="gatetouch-diagnostics-grid" style="display:grid; grid-template-columns: 2fr 1fr; gap:30px; margin-top:30px;">
        
        <!-- LEFT: Main Stats & Logs -->
        <div class="gatetouch-diag-main">
            
            <!-- Status Card -->
            <div class="gatetouch-glass-card" style="margin-bottom:30px;">
                <div class="gatetouch-glass-card__header"><?php esc_html_e( 'API Connection Status', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-glass-card__body" style="padding:30px;">
                    <div style="display:flex; align-items:center; gap:30px;">
                        <div style="text-align:center;">
                            <?php
                            $diag_color    = $status === 'valid' ? '#10b981' : ( $is_safe_mode ? '#ef4444' : '#f59e0b' );
                            $diag_icon     = $status === 'valid' ? 'check-circle' : ( $is_safe_mode ? 'alert-octagon' : 'alert-triangle' );
                            ?>
                            <div style="width:80px; height:80px; border-radius:50%; background:<?php echo esc_attr( $diag_color ); ?>20; color:<?php echo esc_attr( $diag_color ); ?>; display:flex; align-items:center; justify-content:center;">
                                <?php echo wp_kses( GateTouch_Helpers::icon( $diag_icon, 36 ), GateTouch_Helpers::svg_kses_allowed() ); ?>
                            </div>
                            <div style="margin-top:10px; font-weight:800; text-transform:uppercase; font-size:11px; color:var(--riq-text-mid);"><?php echo esc_html( strtoupper( $status ) ); ?></div>
                        </div>
                        <div>
                            <h2 style="margin:0; font-size:24px; font-weight:800;">
                                <?php
                                if ( $is_safe_mode ) esc_html_e( 'System in Safe Mode', 'gatetouch-ai-seo' );
                                elseif ( $status === 'valid' ) esc_html_e( 'API Connection Operational', 'gatetouch-ai-seo' );
                                else esc_html_e( 'API Pending Configuration', 'gatetouch-ai-seo' );
                                ?>
                            </h2>
                            <p style="color:var(--riq-text-mid); margin:10px 0 20px;">
                                <?php
                                if ( $is_safe_mode ) {
                                    esc_html_e( 'Your API has failed multiple times. We have paused AI requests to protect your site performance.', 'gatetouch-ai-seo' );
                                } elseif ( $status === 'valid' ) {
                                    printf(
                                        /* translators: %s: AI provider name */
                                        esc_html__( 'All systems are go. GT SEO/GEO/AEO is successfully communicating with %s.', 'gatetouch-ai-seo' ),
                                        esc_html( $provider_label )
                                    );
                                } else {
                                    esc_html_e( 'Please validate your API key to enable AI-powered SEO features.', 'gatetouch-ai-seo' );
                                }
                                ?>
                            </p>
                            <div style="display:flex; gap:12px;">
                                <button id="gatetouch-test-api" class="gatetouch-btn gatetouch-btn--primary"><?php esc_html_e( 'Run Live Test', 'gatetouch-ai-seo' ); ?></button>
                                <?php if ( $is_safe_mode ) : ?>
                                    <button id="gatetouch-reset-api-errors" class="gatetouch-btn gatetouch-btn--secondary"><?php esc_html_e( 'Reset Safe Mode', 'gatetouch-ai-seo' ); ?></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Chart / Stats -->
            <div class="gatetouch-glass-card" style="margin-bottom:30px;">
                <div class="gatetouch-glass-card__header"><?php esc_html_e( 'Resource Consumption (Last 30 Days)', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-glass-card__body" style="padding:0;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; border-bottom:1px solid #f1f5f9;">
                        <div style="padding:25px; border-right:1px solid #f1f5f9; text-align:center;">
                            <span style="font-size:11px; font-weight:800; color:var(--riq-text-light); text-transform:uppercase;"><?php esc_html_e( 'Total Tokens', 'gatetouch-ai-seo' ); ?></span>
                            <strong style="display:block; font-size:24px; color:var(--riq-primary); margin-top:5px;"><?php echo esc_html( number_format_i18n( $total_tokens ) ); ?></strong>
                        </div>
                        <div style="padding:25px; border-right:1px solid #f1f5f9; text-align:center;">
                            <span style="font-size:11px; font-weight:800; color:var(--riq-text-light); text-transform:uppercase;"><?php esc_html_e( 'Estimated Cost', 'gatetouch-ai-seo' ); ?></span>
                            <strong style="display:block; font-size:24px; color:#10b981; margin-top:5px;">$<?php echo esc_html( number_format_i18n( $total_cost, 4 ) ); ?></strong>
                        </div>
                        <div style="padding:25px; text-align:center;">
                            <span style="font-size:11px; font-weight:800; color:var(--riq-text-light); text-transform:uppercase;"><?php esc_html_e( 'AI Jobs Run', 'gatetouch-ai-seo' ); ?></span>
                            <strong style="display:block; font-size:24px; color:#a855f7; margin-top:5px;"><?php echo esc_html( count( array_keys( $usage ) ) ); ?> <?php esc_html_e( 'Days', 'gatetouch-ai-seo' ); ?></strong>
                        </div>
                    </div>
                    
                    <div style="padding:20px;">
                        <table class="wp-list-table widefat fixed striped" style="border:none; box-shadow:none;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th><?php esc_html_e( 'Tokens Used', 'gatetouch-ai-seo' ); ?></th>
                                    <th><?php esc_html_e( 'Est. Cost', 'gatetouch-ai-seo' ); ?></th>
                                    <th><?php esc_html_e( 'Success Rate', 'gatetouch-ai-seo' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $reversed_usage = array_reverse($usage, true);
                                foreach ( array_slice($reversed_usage, 0, 7) as $date => $day ) : ?>
                                <tr>
                                    <td><?php echo esc_html( gmdate( 'M d, Y', strtotime( $date ) ) ); ?></td>
                                    <td><?php echo esc_html( number_format_i18n( $day['tokens'] ) ); ?></td>
                                    <td>$<?php echo esc_html( number_format_i18n( $day['cost'], 5 ) ); ?></td>
                                    <td><span style="color:#10b981; font-weight:700;">100%</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>

        <!-- RIGHT: Sidebar Info -->
        <div class="gatetouch-diag-sidebar">
            <div class="gatetouch-glass-card">
                <div class="gatetouch-glass-card__header"><?php esc_html_e( 'Health Monitoring', 'gatetouch-ai-seo' ); ?></div>
                <div class="gatetouch-glass-card__body">
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px;">Error Rate:</span>
                            <strong style="color:<?php echo esc_attr( $error_count > 0 ? '#ef4444' : '#10b981' ); ?>;"><?php echo esc_html( $error_count ); ?> / 5</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px;">AI Model:</span>
                            <strong style="font-size:12px;"><?php echo esc_html( get_option( 'gatetouch_ai_model', 'gpt-4o' ) ); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px;">Job Queue:</span>
                            <strong style="color:#10b981;">Idle</strong>
                        </div>
                    </div>
                    <hr style="border:none; border-top:1px solid #f1f5f9; margin:20px 0;">
                    <p style="font-size:12px; color:var(--riq-text-mid); line-height:1.5;"><?php esc_html_e( 'Safe Mode automatically triggers if the API returns 5 consecutive errors. This prevents slow page loads and protects your site from potential timeouts.', 'gatetouch-ai-seo' ); ?></p>
                </div>
            </div>

            <div class="gatetouch-glass-card" style="margin-top:20px; background:linear-gradient(135deg, #6366f1, #a855f7); color:#fff; border:none;">
                <div class="gatetouch-glass-card__body" style="padding:25px;">
                    <h3 style="color:#fff; margin:0 0 10px; font-weight:800;"><?php esc_html_e( 'Enterprise Tip', 'gatetouch-ai-seo' ); ?></h3>
                    <p style="font-size:13px; color:rgba(255,255,255,0.8); line-height:1.6;">Using **GPT-4o-mini** can reduce your costs by up to 90% while maintaining high accuracy for basic metadata tasks.</p>
                </div>
            </div>
        </div>
    </div>
<?php if ( $gt_standalone ) : ?>
</div>
<?php endif; ?>
