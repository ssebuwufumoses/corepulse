<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CorePulse WP-CLI Integration
 * Allows server admins to manage CorePulse directly from the terminal.
 */
class CorePulse_CLI {

    /**
     * Get the current status of CorePulse settings and rules.
     * * ## EXAMPLES
     * * wp corepulse status
     */
    public function status( $args, $assoc_args ) {
        WP_CLI::success( 'CorePulse v1.3.0 is active and running.' );
        
        $killed_scripts = get_option( 'corepulse_killed_scripts', array() );
        $preloads       = get_option( 'corepulse_preloaded_assets', array() );
        
        WP_CLI::log( '--- Current CorePulse Stats ---' );
        WP_CLI::log( 'Active Sniper Rules: ' . count( $killed_scripts ) );
        WP_CLI::log( 'Boosted (Preloaded) Assets: ' . count( $preloads ) );
        
        // Settings thresholds
        WP_CLI::log( 'JS Danger Threshold: ' . get_option( 'corepulse_js_danger', 500 ) . ' KB' );
        WP_CLI::log( 'CSS Danger Threshold: ' . get_option( 'corepulse_css_danger', 300 ) . ' KB' );
    }

    /**
     * Purges the Historical Pulse Logs (ROI Dashboard data).
     * * ## EXAMPLES
     * * wp corepulse clear-logs
     */
    public function clear_logs( $args, $assoc_args ) {
        delete_option( 'corepulse_historical_logs' );
        WP_CLI::success( 'All historical performance logs have been successfully wiped.' );
    }

    /**
     * Emergency reset for all Sniper Rules (Unblocks all scripts).
     * * ## EXAMPLES
     * * wp corepulse reset-rules
     */
    public function reset_rules( $args, $assoc_args ) {
        WP_CLI::confirm( 'Are you sure you want to delete all active Sniper Rules? This will revive all blocked scripts.' );
        update_option( 'corepulse_killed_scripts', array() );
        WP_CLI::success( 'Sniper rules reset. All scripts revived.' );
    }
}