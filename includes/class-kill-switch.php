<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Kill_Switch {
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'execute_kill_list' ), 9999 );
        add_action( 'wp_print_scripts', array( $this, 'execute_kill_list' ), 9999 );
        add_action( 'wp_print_footer_scripts', array( $this, 'execute_kill_list' ), 9 );
        add_action( 'wp_print_styles', array( $this, 'execute_kill_list' ), 9999 );
        
        add_action( 'wp_ajax_corepulse_toggle_script', array( $this, 'toggle_script' ) );
        add_action( 'wp_ajax_corepulse_emergency_restore', array( $this, 'emergency_restore' ) ); 
        add_action( 'wp_ajax_corepulse_import_rules', array( $this, 'import_rules' ) );
        add_action( 'wp_ajax_corepulse_toggle_preload', array( $this, 'toggle_preload' ) );
    }

    public function execute_kill_list() {
        if ( is_admin() ) return;

        // v1.2.0: Headless Simulation Interceptor
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $is_simulating = isset( $_GET['cp_simulate'] ) && $_GET['cp_simulate'] === 'true';
        
        if ( $is_simulating && current_user_can( 'manage_options' ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( ! empty( $_GET['cp_target'] ) ) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $targets = explode( ',', sanitize_text_field( wp_unslash( $_GET['cp_target'] ) ) );
                foreach ( $targets as $handle ) {
                    $handle = trim( $handle );
                    wp_dequeue_script( $handle );
                    wp_deregister_script( $handle );
                    wp_dequeue_style( $handle );
                    wp_deregister_style( $handle );
                }
            }
        }

        $killed_scripts = get_option( 'corepulse_killed_scripts', array() );
        $current_id = get_queried_object_id();

        if ( ! empty( $killed_scripts ) && is_array( $killed_scripts ) ) {
            foreach ( $killed_scripts as $handle => $data ) {
                
                if ( is_numeric( $handle ) ) {
                    wp_dequeue_script( $data );
                    wp_deregister_script( $data );
                    wp_dequeue_style( $data );
                    wp_deregister_style( $data );
                    continue;
                }

                $rule        = isset( $data['rule'] ) ? $data['rule'] : 'everywhere';
                $locations   = isset( $data['locations'] ) ? $data['locations'] : array();
                $should_kill = false;

                if ( 'everywhere' === $rule ) {
                    $should_kill = true;
                } elseif ( 'only' === $rule && in_array( $current_id, $locations, true ) ) {
                    $should_kill = true;
                } elseif ( 'except' === $rule && ! in_array( $current_id, $locations, true ) ) {
                    $should_kill = true;
                }

                if ( $should_kill ) {
                    wp_dequeue_script( $handle );
                    wp_deregister_script( $handle );
                    wp_dequeue_style( $handle );
                    wp_deregister_style( $handle );
                }
            }
        }
    }

    public function toggle_script() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $handle = isset( $_POST['handle'] ) ? sanitize_text_field( wp_unslash( $_POST['handle'] ) ) : '';
        $rule = isset( $_POST['rule'] ) ? sanitize_text_field( wp_unslash( $_POST['rule'] ) ) : 'everywhere';
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

        if ( empty( $handle ) ) wp_send_json_error( 'No script handle provided.' );

        $killed_scripts = get_option( 'corepulse_killed_scripts', array() );
        
        $upgraded_scripts = array();
        foreach ( $killed_scripts as $key => $val ) {
            if ( is_numeric( $key ) ) {
                $upgraded_scripts[ $val ] = array( 'rule' => 'everywhere', 'locations' => array() );
            } else {
                $upgraded_scripts[ $key ] = $val;
            }
        }
        $killed_scripts = $upgraded_scripts;

        if ( isset( $killed_scripts[ $handle ] ) && 'revive' === $rule ) {
            unset( $killed_scripts[$handle] );
            $status = 'revived';
        } else {
            $locations = array();
            if ( $rule !== 'everywhere' && $post_id > 0 ) $locations[] = $post_id;
            $killed_scripts[ $handle ] = array( 'rule' => $rule, 'locations' => $locations );
            $status = 'killed';
        }

        update_option( 'corepulse_killed_scripts', $killed_scripts );
        wp_send_json_success( array( 'status' => $status, 'handle' => $handle ) );
    }

    public function emergency_restore() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        update_option( 'corepulse_killed_scripts', array() ); 
        wp_send_json_success( 'All scripts restored.' );
    }

    public function import_rules() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        
        $raw_rules = isset( $_POST['rules'] ) ? sanitize_textarea_field( wp_unslash( $_POST['rules'] ) ) : '';
        $rules = json_decode( $raw_rules, true );
        if ( is_array( $rules ) ) {
            update_option( 'corepulse_killed_scripts', $rules );
            wp_send_json_success();
        }
        wp_send_json_error( 'Invalid JSON format.' );
    }

    public function toggle_preload() {
        check_ajax_referer( 'corepulse_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'image';

        if ( empty( $url ) ) wp_send_json_error( 'No URL provided.' );

        $preloads = get_option( 'corepulse_preloaded_assets', array() );

        if ( isset( $preloads[$url] ) ) {
            unset( $preloads[$url] );
            $status = 'removed';
        } else {
            $preloads[$url] = $type;
            $status = 'added';
        }

        update_option( 'corepulse_preloaded_assets', $preloads );
        wp_send_json_success( array( 'status' => $status, 'url' => $url ) );
    }
}