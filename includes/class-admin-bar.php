<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Admin_Bar {
    public function __construct() {
        add_action( 'admin_bar_menu', array( $this, 'add_pulse_node' ), 999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pulse_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pulse_assets' ) );
    }

    public function add_pulse_node( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) return;

        // ID so JS can find the dot, and forced it to start green.
        $icon = '<span id="corepulse-icon" class="pulse-icon" style="margin-top: 6px; background: #00ff00; box-shadow: 0 0 5px #00ff00;"></span>';

        if ( is_admin() ) {
            // BackEnd: Plugin is alive, links directly to the Settings page
            $wp_admin_bar->add_node( array(
                'id'    => 'corepulse-status',
                // Change to "Active" and updated the hex color to pure green
                'title' => $icon . ' CorePulse: <span style="color:#00ff00;">Active</span>',
                'href'  => admin_url( 'options-general.php?page=corepulse' ), 
                'meta'  => array( 
                    'title' => 'Go to CorePulse Settings' 
                )
            ) );
        } else {
            // FrontEnd: Plugin is hunting, links to the HUD
            $wp_admin_bar->add_node( array(
                'id'    => 'corepulse-status',
                // Force text to start green so it doesn't flash white!
                'title' => $icon . ' CorePulse: <span id="pulse-score" style="color:#00ff00;">Scanning...</span>',
                'href'  => '#',
                'meta'  => array( 
                    'title' => 'Open Asset Autopsy HUD',
                    // Bulletproof fallback to force open the HUD on click
                    'onclick' => 'const hud = document.getElementById("corepulse-hud"); if(hud) hud.classList.add("corepulse-hud-active"); return false;'
                )
            ) );
        }
    }

    public function enqueue_pulse_assets() {
        if ( is_admin_bar_showing() && current_user_can( 'manage_options' ) ) {
            wp_enqueue_style( 'corepulse-css', COREPULSE_URL . 'assets/css/admin-bar-pulse.css', array(), time() );
            wp_enqueue_script( 'corepulse-js', COREPULSE_URL . 'assets/js/interactivity-ui.js', array(), time(), true );

            $killed_scripts = get_option( 'corepulse_killed_scripts', array() );
            
            wp_localize_script( 'corepulse-js', 'corepulse_ajax', array(
                'url'     => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'corepulse_nonce' ),
                'killed'  => is_array($killed_scripts) ? $killed_scripts : array(),
                'post_id' => get_queried_object_id() 
            ) );
        }
    }
}