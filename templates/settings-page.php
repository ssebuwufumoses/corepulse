<?php if ( ! defined( 'ABSPATH' ) ) exit; 

// Handle Log Clearing (Must be at the very top before headers)
if ( isset( $_POST['corepulse_clear_logs'], $_POST['corepulse_clear_logs_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['corepulse_clear_logs_nonce'] ) ), 'corepulse_clear_logs_action' ) ) {
    delete_option( 'corepulse_historical_logs' );
    echo '<div class="notice notice-success is-dismissible"><p><strong>Historical Pulse Logs have been completely wiped clean!</strong></p></div>';
}
?>

<div class="wrap">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#00ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
        </svg>
        CorePulse Settings
    </h1>
    <p>Configure your server-centric performance thresholds. Shift the load. Feel the Pulse.</p>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'corepulse_options_group' );
        do_settings_sections( 'corepulse_options_group' );
        
        // Fetch current values (with defaults) - PREFIXED for strict WP standards
        $corepulse_js_warn     = get_option( 'corepulse_js_warning', 200 );
        $corepulse_js_danger   = get_option( 'corepulse_js_danger', 500 );
        $corepulse_css_warn    = get_option( 'corepulse_css_warning', 150 );
        $corepulse_css_danger  = get_option( 'corepulse_css_danger', 300 );
        $corepulse_dom_warn    = get_option( 'corepulse_dom_warning', 800 );
        $corepulse_dom_danger  = get_option( 'corepulse_dom_danger', 1500 );
        $corepulse_media_limit = get_option( 'corepulse_media_limit', 250 );
        $corepulse_hide_node   = get_option( 'corepulse_hide_floating_node', 0 );
        ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">JS Warning Threshold (KB)</th>
                <td>
                    <input type="number" name="corepulse_js_warning" value="<?php echo esc_attr( $corepulse_js_warn ); ?>" />
                    <p class="description">Turn the pulse yellow when JS exceeds this weight.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">JS Danger Threshold (KB)</th>
                <td>
                    <input type="number" name="corepulse_js_danger" value="<?php echo esc_attr( $corepulse_js_danger ); ?>" />
                    <p class="description">Turn the pulse red when JS exceeds this weight.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">CSS Warning Threshold (KB)</th>
                <td>
                    <input type="number" name="corepulse_css_warning" value="<?php echo esc_attr( $corepulse_css_warn ); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">CSS Danger Threshold (KB)</th>
                <td>
                    <input type="number" name="corepulse_css_danger" value="<?php echo esc_attr( $corepulse_css_danger ); ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">DOM Warning Threshold (Nodes)</th>
                <td>
                    <input type="number" name="corepulse_dom_warning" value="<?php echo esc_attr( $corepulse_dom_warn ); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">DOM Danger Threshold (Nodes)</th>
                <td>
                    <input type="number" name="corepulse_dom_danger" value="<?php echo esc_attr( $corepulse_dom_danger ); ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Heavy Media Limit (KB)</th>
                <td>
                    <input type="number" name="corepulse_media_limit" value="<?php echo esc_attr( $corepulse_media_limit ); ?>" />
                    <p class="description">Flag images, fonts, and videos larger than this size.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">UI Preferences</th>
                <td>
                    <label>
                        <input type="checkbox" name="corepulse_hide_floating_node" value="1" <?php checked( 1, $corepulse_hide_node ); ?> />
                        Hide the bottom-left floating trigger node (Rely only on Admin Bar or Ctrl+Shift+X)
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ccc;">

    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="margin-bottom: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2271b1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                Historical Pulse Logs
            </h2>
            <p style="margin-top: 5px; margin-bottom: 0;">Your site's most recent Web Vitals snapshots. Automatically captured 5 seconds after page load.</p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'corepulse_clear_logs_action', 'corepulse_clear_logs_nonce' ); ?>
            <input type="hidden" name="corepulse_clear_logs" value="1">
            <button type="submit" class="button" style="color: #d63638; border-color: #d63638;" onclick="return confirm('Are you sure you want to permanently delete all performance logs? This cannot be undone.');">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-top: -2px; margin-right: 4px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                Clear All Logs
            </button>
        </form>
    </div>

    <?php 
    $corepulse_history = get_option( 'corepulse_historical_logs', array() );
    if ( empty( $corepulse_history ) ) : ?>
        <div style="background: #fff; border-left: 4px solid #ffcc00; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 15px;">
            <p style="margin: 0;"><strong>No logs yet.</strong> Browse your frontend pages to start collecting performance snapshots.</p>
        </div>
    <?php else : 
        // Reverse array to show newest logs first
        $corepulse_history = array_reverse( $corepulse_history ); 
    ?>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Path</th>
                    <th>JS Payload</th>
                    <th>CSS Payload</th>
                    <th>TTFB (ms)</th>
                    <th>INP (ms)</th>
                    <th>CLS</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ( $corepulse_history as $corepulse_log ) : 
                    $corepulse_js_color   = $corepulse_log['js_kb'] > $corepulse_js_danger ? '#d63638' : ( $corepulse_log['js_kb'] > $corepulse_js_warn ? '#b32d2e' : '#00a32a' );
                    $corepulse_ttfb_color = $corepulse_log['ttfb'] > 600 ? '#d63638' : ( $corepulse_log['ttfb'] > 300 ? '#b32d2e' : '#00a32a' );
                    $corepulse_inp_color  = $corepulse_log['inp'] > 500 ? '#d63638' : ( $corepulse_log['inp'] > 200 ? '#b32d2e' : '#00a32a' );
                    $corepulse_cls_color  = $corepulse_log['cls'] > 0.25 ? '#d63638' : ( $corepulse_log['cls'] > 0.1 ? '#b32d2e' : '#00a32a' );
                ?>
                    <tr>
                        <td><strong><?php echo esc_html( $corepulse_log['date'] ); ?></strong></td>
                        <td><code><?php echo esc_html( $corepulse_log['url'] ); ?></code></td>
                        <td style="color: <?php echo esc_attr( $corepulse_js_color ); ?>; font-weight: bold;"><?php echo esc_html( $corepulse_log['js_kb'] ); ?> KB</td>
                        <td><?php echo esc_html( $corepulse_log['css_kb'] ); ?> KB</td>
                        <td style="color: <?php echo esc_attr( $corepulse_ttfb_color ); ?>; font-weight: bold;"><?php echo esc_html( $corepulse_log['ttfb'] ); ?></td>
                        <td style="color: <?php echo esc_attr( $corepulse_inp_color ); ?>; font-weight: bold;"><?php echo esc_html( $corepulse_log['inp'] ); ?></td>
                        <td style="color: <?php echo esc_attr( $corepulse_cls_color ); ?>; font-weight: bold;"><?php echo esc_html( $corepulse_log['cls'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>