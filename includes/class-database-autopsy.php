<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Database_Autopsy {
    
    public function __construct() {
        // We use AJAX so we don't run heavy DB queries on every page load!
        add_action( 'wp_ajax_corepulse_purge_transients', array( $this, 'purge_transients' ) );
        add_action( 'wp_ajax_corepulse_scan_database', array( $this, 'run_scan' ) );
    }

    public function run_scan() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        global $wpdb;

        // Scan Autoloaded Options Size
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $autoload_query = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE autoload = 'yes' OR autoload = 'on'" );
        $autoload_size = 0;
        $heavy_options = array();

        foreach ( $autoload_query as $option ) {
            $size = strlen( $option->option_value );
            $autoload_size += $size;
            
            // If a single database row is over 10KB, flag it as a heavy offender
            if ( $size > 10000 ) { 
                $heavy_options[] = array(
                    'name' => $option->option_name,
                    'kb'   => round( $size / 1024, 1 )
                );
            }
        }

        // Scan for Expired/Bloated Transients
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $transients_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'" );

        // Sort the heavy options from largest to smallest
        usort($heavy_options, function($a, $b) { return $b['kb'] <=> $a['kb']; });

        wp_send_json_success( array(
            'autoload_kb'   => round( $autoload_size / 1024, 1 ),
            'heavy_options' => array_slice( $heavy_options, 0, 15 ), // Return the top 15 worst offenders
            'transients'    => $transients_count
        ) );
    }

    /**
     * Instantly purges all expired and orphaned transients from the database.
     */
    public function purge_transients() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        global $wpdb;
        
        // Delete standard transients and site transients
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%'" );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_%'" );

        wp_send_json_success();
    }
}