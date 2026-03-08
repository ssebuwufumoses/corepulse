<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Pulse_Logs {
    public function __construct() {
        add_action( 'wp_ajax_corepulse_log_vitals', array( $this, 'save_snapshot' ) );
    }

    public function save_snapshot() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        // Sanitize incoming Web Vitals
        $log_data = array(
            'date'    => current_time( 'M j, Y' ), // e.g., "Mar 8, 2026"
            'js_kb'   => isset( $_POST['js_kb'] ) ? floatval( $_POST['js_kb'] ) : 0,
            'css_kb'  => isset( $_POST['css_kb'] ) ? floatval( $_POST['css_kb'] ) : 0,
            'ttfb'    => isset( $_POST['ttfb'] ) ? intval( $_POST['ttfb'] ) : 0,
            'inp'     => isset( $_POST['inp'] ) ? intval( $_POST['inp'] ) : 0,
            'cls'     => isset( $_POST['cls'] ) ? floatval( $_POST['cls'] ) : 0,
            'url'     => isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : 'Unknown'
        );

        $history = get_option( 'corepulse_historical_logs', array() );
        if ( ! is_array( $history ) ) $history = array();

        // Create a unique hash for the Day + URL so we only save 1 snapshot per page per day
        $hash = md5( $log_data['date'] . $log_data['url'] );
        $history[$hash] = $log_data;

        // Keep the database lean: Only store the most recent 50 snapshots globally
        if ( count( $history ) > 50 ) {
            $history = array_slice( $history, -50, null, true );
        }

        update_option( 'corepulse_historical_logs', $history );
        wp_send_json_success( 'Snapshot saved.' );
    }
}