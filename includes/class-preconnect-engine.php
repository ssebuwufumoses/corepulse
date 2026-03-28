<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Preconnect_Engine {
    public function __construct() {
        add_filter( 'wp_resource_hints', array( $this, 'inject_resource_hints' ), 10, 2 );
    }

    public function inject_resource_hints( $urls, $relation_type ) {
        if ( is_admin() ) {
            return $urls;
        }

        if ( ! in_array( $relation_type, array( 'preconnect', 'dns-prefetch' ), true ) ) {
            return $urls;
        }

        static $external_domains = null;

        if ( null === $external_domains ) {
            global $wp_scripts, $wp_styles;
            $external_domains = array();
            $site_host        = wp_parse_url( site_url(), PHP_URL_HOST );

            $this->extract_domains( $wp_scripts, $site_host, $external_domains );
            $this->extract_domains( $wp_styles, $site_host, $external_domains );
        }

        foreach ( $external_domains as $domain ) {
            if ( 'preconnect' === $relation_type ) {
                $urls[] = array(
                    'href'        => 'https://' . $domain,
                    'crossorigin' => 'anonymous',
                );
            } else {
                $urls[] = 'https://' . $domain;
            }
        }

        return $urls;
    }

    private function extract_domains( $wp_dependencies, $site_host, &$external_domains ) {
        if ( ! isset( $wp_dependencies ) || ! is_a( $wp_dependencies, 'WP_Dependencies' ) ) return;

        foreach ( $wp_dependencies->queue as $handle ) {
            if ( isset( $wp_dependencies->registered[ $handle ] ) ) {
                $src = $wp_dependencies->registered[ $handle ]->src;

                if ( empty( $src ) || strpos( $src, '/wp-' ) === 0 ) continue;

                $parsed = wp_parse_url( $src );
                if ( isset( $parsed['host'] ) && $parsed['host'] !== $site_host ) {
                    if ( ! in_array( $parsed['host'], $external_domains, true ) ) {
                        $external_domains[] = $parsed['host'];
                    }
                }
            }
        }
    }
}
