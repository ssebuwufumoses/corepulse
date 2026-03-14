<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse {
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->enable_query_tracking(); // Start the SQL Radar
    }

    private function enable_query_tracking() {
        // Turn on WordPress's hidden query tracker ONLY for Admins
        add_action( 'init', function() {
            if ( current_user_can( 'manage_options' ) && ! defined( 'SAVEQUERIES' ) ) {
                define( 'SAVEQUERIES', true );
            }
        }, 1 );
    }

    private function load_dependencies() {
        require_once COREPULSE_PATH . 'includes/class-admin-bar.php';
        require_once COREPULSE_PATH . 'includes/class-asset-autopsy.php';
        require_once COREPULSE_PATH . 'includes/class-hydration-guard.php';
        require_once COREPULSE_PATH . 'includes/class-suggestions.php';
        
        // Kill Switch dependency
        require_once COREPULSE_PATH . 'includes/class-kill-switch.php';
        
        // Dependencies
        require_once COREPULSE_PATH . 'includes/class-preconnect-engine.php';
        require_once COREPULSE_PATH . 'includes/class-pulse-logs.php';
        require_once COREPULSE_PATH . 'includes/class-database-autopsy.php';

        // WP-CLI Support
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once COREPULSE_PATH . 'includes/class-corepulse-cli.php';
        }
    }

    private function define_admin_hooks() {
        new CorePulse_Admin_Bar();
        new CorePulse_Asset_Autopsy();
        new CorePulse_Hydration_Guard();
        
        // Initialized the Kill Switch engine
        new CorePulse_Kill_Switch();
        
        // Boot up Engines
        new CorePulse_Preconnect_Engine();
        new CorePulse_Pulse_Logs();
        new CorePulse_Database_Autopsy();

        // Register WP-CLI Commands
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'corepulse', 'CorePulse_CLI' );
        }

        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
        
        // Load local assets for the settings page (fixes CDN error)
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
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
     * Enqueue assets strictly for the backend settings page.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load Chart.js on the CorePulse settings page to avoid conflicts
        if ( $hook !== 'settings_page_corepulse' ) {
            return;
        }
        wp_enqueue_script( 'corepulse-chart', COREPULSE_URL . 'assets/js/chart.min.js', array(), '4.4.1', true );
    }

    /**
     * Whitelists and securely registers ALL settings fields to the database.
     */
    public function register_plugin_settings() {
        $settings_fields = array(
            'corepulse_js_warning',
            'corepulse_js_danger',
            'corepulse_css_warning',
            'corepulse_css_danger',
            'corepulse_dom_warning',
            'corepulse_dom_danger',
            'corepulse_media_limit',
            'corepulse_hide_floating_node'
        );

        foreach ( $settings_fields as $field ) {
            register_setting( 
                'corepulse_options_group', 
                $field,
                array( 'sanitize_callback' => 'absint' ) 
            );
        }
    }

    /**
     * Outputs the settings page HTML.
     */
    public function display_plugin_admin_page() {
        require_once COREPULSE_PATH . 'templates/settings-page.php';
    }
}