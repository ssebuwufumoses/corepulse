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

            // Escape output compliance. Using strict string literals instead of dynamic variables.
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

        // Do not load the HUD inside page builders, editors, or backend
        if ( is_admin() ) return; 
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL params for display logic only, not processing forms.
        if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && $_GET['action'] == 'elementor' ) ) return;
        
        if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) return;
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL params for display logic only, not processing forms.
        if ( isset( $_GET['fl_builder'] ) || isset( $_GET['et_fb'] ) || isset( $_GET['bricks'] ) || isset( $_GET['oxygen_iframe'] ) ) return;

        global $wp_scripts, $wp_styles;
        $total_js_size = 0;
        $total_css_size = 0;
        $culprits = array();

        if ( isset( $wp_scripts ) ) {
            $all_scripts = array_unique( array_merge( (array) $wp_scripts->queue, (array) $wp_scripts->done ) );
            foreach ( $all_scripts as $handle ) {
                if ( strpos( $handle, 'corepulse' ) !== false ) continue;

                $src = isset( $wp_scripts->registered[$handle] ) ? $wp_scripts->registered[$handle]->src : '';
                $size_info = $this->get_file_size_info( $src );
                $suggestion = 'Custom or 3rd-party script.';
                
                $culprits[] = array( 
                    'handle'     => $handle, 
                    'url'        => $src,
                    'suggestion' => $suggestion,
                    'type'       => 'js',
                    'size'       => $size_info['display'],
                    'bytes'      => $size_info['bytes'],
                    'domain'     => $size_info['domain'],
                    'provider'   => $this->get_provider( $src ) 
                );
                $total_js_size += ( $size_info['bytes'] / 1024 ); 
            }
        }

        if ( isset( $wp_styles ) ) {
            $all_styles = array_unique( array_merge( (array) $wp_styles->queue, (array) $wp_styles->done ) );
            foreach ( $all_styles as $handle ) {
                if ( strpos( $handle, 'corepulse' ) !== false ) continue;

                $src = isset( $wp_styles->registered[$handle] ) ? $wp_styles->registered[$handle]->src : '';
                $size_info = $this->get_file_size_info( $src );
                
                $suggestion = 'Heavy stylesheet. Dequeue on pages where this design is not needed.';
                if ( strpos( $handle, 'elementor' ) !== false ) $suggestion = 'Elementor builder CSS. Often loads globally even when unused.';
                
                $culprits[] = array( 
                    'handle'     => $handle, 
                    'url'        => $src,
                    'suggestion' => $suggestion,
                    'type'       => 'css',
                    'size'       => $size_info['display'],
                    'bytes'      => $size_info['bytes'],
                    'domain'     => $size_info['domain'],
                    'provider'   => $this->get_provider( $src ) 
                );
                $total_css_size += ( $size_info['bytes'] / 1024 ); 
            }
        }

        usort($culprits, function($a, $b) { return $b['bytes'] <=> $a['bytes']; });

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

        printf(
            '<script>window.corePulseData = { weight: %d, css_weight: %d, culprits: %s, rules: %s, settings: %s, preloads: %s };</script>',
            intval( $total_js_size ),
            intval( $total_css_size ),
            wp_json_encode( $culprits ),
            wp_json_encode( $killed_scripts ),
            wp_json_encode( $user_settings ),
            wp_json_encode( $preloaded_assets )
        );
        
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