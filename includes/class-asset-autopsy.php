<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Asset_Autopsy {
    public function __construct() {
        add_action( 'wp_footer', array( $this, 'calculate_payload_weights' ), 999 );
        add_action( 'admin_footer', array( $this, 'calculate_payload_weights' ), 999 );
        add_action( 'wp_head', array( $this, 'output_boost_preloads' ), 1 ); 
    }

    public function output_boost_preloads() {
        $preloads = get_option( 'corepulse_preloaded_assets', array() );
        if ( empty( $preloads ) ) return;

        echo "\n\n";
        foreach ( $preloads as $url => $type ) {
            $as = 'image';
            if ( $type === 'css' ) $as = 'style';
            if ( $type === 'js' ) $as = 'script';
            if ( $type === 'font' ) $as = 'font';

            if ( $as === 'font' ) {
                echo "<link rel='preload' href='" . esc_url( $url ) . "' as='" . esc_attr( $as ) . "' crossorigin='anonymous'>\n";
            } else {
                echo "<link rel='preload' href='" . esc_url( $url ) . "' as='" . esc_attr( $as ) . "'>\n";
            }
        }
        echo "\n\n";
    }

    public function calculate_payload_weights() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( is_admin() ) return; 
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && $_GET['action'] == 'elementor' ) ) return;
        if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) return;
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['fl_builder'] ) || isset( $_GET['et_fb'] ) || isset( $_GET['bricks'] ) || isset( $_GET['oxygen_iframe'] ) ) return;

        global $wp_scripts, $wp_styles;
        $total_js_size = 0;
        $total_css_size = 0;
        $culprits = array();
        
        // Initialize Blame Game Matrix
        $blame_matrix = array();

        // Asset Dependency Reverse Mapping Arrays
        $js_dependents = array();
        $css_dependents = array();

        // Core Guard Protection Matrix
        $unstoppable_core = array( 
            'jquery', 'jquery-core', 'jquery-migrate', 'wp-api', 
            'wp-hooks', 'wp-i18n', 'wp-polyfill', 'wp-data',
            'elementor-frontend', 'elementor-common' 
        );
        $high_risk_core = array( 
            'wp-embed', 'comment-reply', 'hoverIntent', 'admin-bar' 
        );

        if ( isset( $wp_scripts ) ) {
            $all_scripts = array_unique( array_merge( (array) $wp_scripts->queue, (array) $wp_scripts->done ) );
            
            // First Pass: Map out which active scripts rely on which handles
            foreach ( $all_scripts as $handle ) {
                if ( isset( $wp_scripts->registered[$handle] ) ) {
                    $deps = $wp_scripts->registered[$handle]->deps;
                    if ( ! empty( $deps ) && is_array( $deps ) ) {
                        foreach ( $deps as $dep ) {
                            if ( ! isset( $js_dependents[$dep] ) ) $js_dependents[$dep] = array();
                            $js_dependents[$dep][] = $handle;
                        }
                    }
                }
            }

            // Second Pass: Build the Culprit Payload
            foreach ( $all_scripts as $handle ) {
                if ( strpos( $handle, 'corepulse' ) !== false ) continue;

                $src = isset( $wp_scripts->registered[$handle] ) ? $wp_scripts->registered[$handle]->src : '';
                $size_info = $this->get_file_size_info( $src );
                $dependents = isset( $js_dependents[$handle] ) ? array_unique( $js_dependents[$handle] ) : array();
                
                // Track Blame Game Weight
                $provider_name = $this->get_provider( $src );
                if ( ! empty( $provider_name ) ) {
                    if ( ! isset( $blame_matrix[$provider_name] ) ) $blame_matrix[$provider_name] = 0;
                    $blame_matrix[$provider_name] += $size_info['bytes'];
                }
                
                // Trigger Smart Pulse AI
                $suggestion = CorePulse_Suggestions::analyze_asset( $handle, 'js', $size_info['bytes'], $dependents );
                
                // Determine Core Guard Status (Overrides suggestion if critical)
                $protection = 'none';
                if ( in_array( $handle, $unstoppable_core ) ) {
                    $protection = 'unstoppable';
                    $suggestion = 'CRITICAL CORE ASSET. Dequeuing is disabled to prevent fatal crashes.';
                } elseif ( in_array( $handle, $high_risk_core ) ) {
                    $protection = 'warning';
                    $suggestion = 'Native WP Asset. Dequeuing may break expected core functionality.';
                }
                
                $culprits[] = array( 
                    'handle'           => $handle, 
                    'url'              => $src,
                    'suggestion'       => $suggestion,
                    'type'             => 'js',
                    'size'             => $size_info['display'],
                    'bytes'            => $size_info['bytes'],
                    'domain'           => $size_info['domain'],
                    'provider'         => $provider_name,
                    'dependents'       => $dependents,
                    'protection_level' => $protection 
                );
                $total_js_size += ( $size_info['bytes'] / 1024 ); 
            }
        }

        if ( isset( $wp_styles ) ) {
            $all_styles = array_unique( array_merge( (array) $wp_styles->queue, (array) $wp_styles->done ) );
            
            // First Pass: Map CSS dependencies
            foreach ( $all_styles as $handle ) {
                if ( isset( $wp_styles->registered[$handle] ) ) {
                    $deps = $wp_styles->registered[$handle]->deps;
                    if ( ! empty( $deps ) && is_array( $deps ) ) {
                        foreach ( $deps as $dep ) {
                            if ( ! isset( $css_dependents[$dep] ) ) $css_dependents[$dep] = array();
                            $css_dependents[$dep][] = $handle;
                        }
                    }
                }
            }

            foreach ( $all_styles as $handle ) {
                if ( strpos( $handle, 'corepulse' ) !== false ) continue;

                $src = isset( $wp_styles->registered[$handle] ) ? $wp_styles->registered[$handle]->src : '';
                $size_info = $this->get_file_size_info( $src );
                $dependents = isset( $css_dependents[$handle] ) ? array_unique( $css_dependents[$handle] ) : array();
                
                // Track Blame Game Weight
                $provider_name = $this->get_provider( $src );
                if ( ! empty( $provider_name ) ) {
                    if ( ! isset( $blame_matrix[$provider_name] ) ) $blame_matrix[$provider_name] = 0;
                    $blame_matrix[$provider_name] += $size_info['bytes'];
                }
                
                // Trigger Smart Pulse AI
                $suggestion = CorePulse_Suggestions::analyze_asset( $handle, 'css', $size_info['bytes'], $dependents );
                
                $culprits[] = array( 
                    'handle'           => $handle, 
                    'url'              => $src,
                    'suggestion'       => $suggestion,
                    'type'             => 'css',
                    'size'             => $size_info['display'],
                    'bytes'            => $size_info['bytes'],
                    'domain'           => $size_info['domain'],
                    'provider'         => $provider_name,
                    'dependents'       => $dependents,
                    'protection_level' => 'none'
                );
                $total_css_size += ( $size_info['bytes'] / 1024 ); 
            }
        }

        usort( $culprits, function( $a, $b ) { return $b['bytes'] <=> $a['bytes']; } );

        // Format the Blame Game Matrix
        arsort( $blame_matrix );
        $blame_game_formatted = array();
        foreach ( $blame_matrix as $prov => $bytes ) {
            $blame_game_formatted[] = array( 'provider' => $prov, 'kb' => round( $bytes / 1024, 1 ) );
        }

        // ------------------------------------------------------------------
        // Query Autopsy Engine (Slow SQL Radar)
        // ------------------------------------------------------------------
        global $wpdb;
        $slow_queries = array();
        $savequeries_enabled = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

        if ( $savequeries_enabled && ! empty( $wpdb->queries ) ) {
            $queries = $wpdb->queries;
            
            // Sort queries by execution time (Index 1 is the time in seconds)
            usort( $queries, function( $a, $b ) {
                return $b[1] <=> $a[1];
            } );
            
            // Grab the top 5 slowest queries
            $top_queries = array_slice( $queries, 0, 5 );
            foreach ( $top_queries as $q ) {
                $stack = explode( ',', $q[2] );
                $caller = end( $stack ); // Get the specific function that ran the query
                
                $sql = wp_strip_all_tags( $q[0] );
                if ( strlen( $sql ) > 120 ) {
                    $sql = substr( $sql, 0, 120 ) . '...';
                }
                
                $slow_queries[] = array(
                    'time'   => round( $q[1] * 1000, 2 ), // Convert seconds to ms
                    'query'  => $sql,
                    'caller' => trim( $caller )
                );
            }
        }
        // ------------------------------------------------------------------

        $killed_scripts = get_option( 'corepulse_killed_scripts', array() );
        $preloaded_assets = get_option( 'corepulse_preloaded_assets', array() ); 

        $user_settings = array(
            'js_warning'   => (int) get_option( 'corepulse_js_warning', 200 ),
            'js_danger'    => (int) get_option( 'corepulse_js_danger', 500 ),
            'css_warning'  => (int) get_option( 'corepulse_css_warning', 150 ),
            'css_danger'   => (int) get_option( 'corepulse_css_danger', 300 ),
            'dom_warning'  => (int) get_option( 'corepulse_dom_warning', 800 ),
            'dom_danger'   => (int) get_option( 'corepulse_dom_danger', 1500 ),
            'media_limit'  => (int) get_option( 'corepulse_media_limit', 250 ),
            'hide_trigger' => (bool) get_option( 'corepulse_hide_floating_node', 0 )
        );

        // Output corePulseData via WordPress script API so no raw <script> tag is echoed.
        // calculate_payload_weights() runs at wp_footer priority 999, after WordPress has
        // already printed footer scripts at priority 20, so we register a dedicated handle
        // and call wp_print_scripts() immediately to flush it in place.
        $inline_data = sprintf(
            'window.corePulseData = { weight: %d, css_weight: %d, culprits: %s, rules: %s, settings: %s, preloads: %s, blame_game: %s, slow_queries: %s, savequeries: %s };',
            intval( $total_js_size ),
            intval( $total_css_size ),
            wp_json_encode( $culprits ),
            wp_json_encode( $killed_scripts ),
            wp_json_encode( $user_settings ),
            wp_json_encode( $preloaded_assets ),
            wp_json_encode( $blame_game_formatted ),
            wp_json_encode( $slow_queries ),
            wp_json_encode( $savequeries_enabled )
        );
        wp_register_script( 'corepulse-data', '', array( 'corepulse-js' ), '1.2.0', true );
        wp_add_inline_script( 'corepulse-data', $inline_data );
        wp_print_scripts( array( 'corepulse-data' ) );
        
        include COREPULSE_PATH . 'templates/hud-overlay.php';
    }

    private function get_provider( $src ) {
        if ( empty( $src ) ) return '';
        if ( strpos( $src, '/wp-content/plugins/' ) !== false ) {
            $parts = explode( '/wp-content/plugins/', $src );
            $folder = explode( '/', $parts[1] )[0];
            return 'Plugin: ' . ucwords( str_replace( '-', ' ', $folder ) );
        } elseif ( strpos( $src, '/wp-content/themes/' ) !== false ) {
            $parts = explode( '/wp-content/themes/', $src );
            $folder = explode( '/', $parts[1] )[0];
            return 'Theme: ' . ucwords( str_replace( '-', ' ', $folder ) );
        } elseif ( strpos( $src, '/wp-includes/' ) !== false || strpos( $src, '/wp-admin/' ) !== false ) {
            return 'WordPress Core';
        }
        return '';
    }

    private function get_file_size_info( $src ) {
        if ( empty( $src ) ) return array( 'display' => 'Inline', 'bytes' => 0, 'domain' => 'Inline' );
        
        $src = strtok( $src, '?' ); 
        $site_url = site_url();
        $abs_path = '';
        $domain = '';

        if ( strpos( $src, $site_url ) === 0 ) {
            $rel_path = str_replace( $site_url, '', $src );
            $abs_path = rtrim(ABSPATH, '/') . '/' . ltrim( $rel_path, '/' );
            $domain = 'Local';
        } elseif ( strpos( $src, '/wp-' ) === 0 ) {
            $abs_path = rtrim(ABSPATH, '/') . '/' . ltrim( $src, '/' );
            $domain = 'Local';
        } else {
            $parsed = wp_parse_url( $src );
            $domain = isset($parsed['host']) ? $parsed['host'] : 'External';
            return array( 'display' => 'External', 'bytes' => 0, 'domain' => $domain );
        }

        if ( file_exists( $abs_path ) ) {
            $bytes = filesize( $abs_path );
            $kb = round( $bytes / 1024, 1 );
            return array( 'display' => $kb . ' KB', 'bytes' => $bytes, 'domain' => 'Local' );
        }

        return array( 'display' => 'Unknown', 'bytes' => 0, 'domain' => 'Unknown' );
    }
}