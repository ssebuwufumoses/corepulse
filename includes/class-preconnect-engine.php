<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Preconnect_Engine {
    public function __construct() {
        // Hook early into the head to print hints before assets load
        add_action( 'wp_head', array( $this, 'inject_resource_hints' ), 2 );
    }

    public function inject_resource_hints() {
        if ( is_admin() ) return;

        global $wp_scripts, $wp_styles;
        $external_domains = array();
        $site_host = wp_parse_url( site_url(), PHP_URL_HOST );

        // Extract from Scripts
        $this->extract_domains( $wp_scripts, $site_host, $external_domains );
        // Extract from Styles
        $this->extract_domains( $wp_styles, $site_host, $external_domains );

        if ( ! empty( $external_domains ) ) {
            echo "\n\n";
            foreach ( $external_domains as $domain ) {
                echo "<link rel='preconnect' href='" . esc_url( 'https://' . $domain ) . "' crossorigin>\n";
                echo "<link rel='dns-prefetch' href='" . esc_url( 'https://' . $domain ) . "'>\n";
            }
            echo "\n\n";
        }
    }

    private function extract_domains( $wp_dependencies, $site_host, &$external_domains ) {
        if ( ! isset( $wp_dependencies ) || ! is_a( $wp_dependencies, 'WP_Dependencies' ) ) return;

        // Loop through assets queued to load on this specific page
        foreach ( $wp_dependencies->queue as $handle ) {
            if ( isset( $wp_dependencies->registered[$handle] ) ) {
                $src = $wp_dependencies->registered[$handle]->src;
                
                // Skip empty sources or local WordPress files
                if ( empty( $src ) || strpos( $src, '/wp-' ) === 0 ) continue;

                $parsed = wp_parse_url( $src );
                if ( isset( $parsed['host'] ) && $parsed['host'] !== $site_host ) {
                    // Prevent duplicates
                    if ( ! in_array( $parsed['host'], $external_domains ) ) {
                        $external_domains[] = $parsed['host'];
                    }
                }
            }
        }
    }
}