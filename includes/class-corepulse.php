<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse {
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    private function load_dependencies() {
        require_once COREPULSE_PATH . 'includes/class-admin-bar.php';
        require_once COREPULSE_PATH . 'includes/class-asset-autopsy.php';
        require_once COREPULSE_PATH . 'includes/class-hydration-guard.php';
        require_once COREPULSE_PATH . 'includes/class-suggestions.php';
        
        // Kill Switch dependency
        require_once COREPULSE_PATH . 'includes/class-kill-switch.php';
    }

    private function define_admin_hooks() {
        new CorePulse_Admin_Bar();
        new CorePulse_Asset_Autopsy();
        new CorePulse_Hydration_Guard();
        
        // Initialized the Kill Switch engine
        new CorePulse_Kill_Switch();

        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
    }

    /**
     * Registers the CorePulse settings page under the main Settings menu.
     */
    public function add_plugin_admin_menu() {
        add_options_page(
            'CorePulse Settings',
            'CorePulse',
            'manage_options',
            'corepulse',
            array( $this, 'display_plugin_admin_page' )
        );
    }

    /**
     * Registers warning and danger thresholds for the JS payload.
     */
    public function register_plugin_settings() {
        register_setting( 
            'corepulse_options_group', 
            'corepulse_warning_limit',
            array( 'sanitize_callback' => 'absint' ) 
        );
        
        register_setting( 
            'corepulse_options_group', 
            'corepulse_danger_limit',
            array( 'sanitize_callback' => 'absint' )
        );
    }

    /**
     * Outputs the settings page HTML.
     */
    public function display_plugin_admin_page() {
        require_once COREPULSE_PATH . 'templates/settings-page.php';
    }
}