<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

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
        // This connects to WordPress's Settings API
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
</div>