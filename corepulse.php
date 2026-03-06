<?php
/**
 * Plugin Name: CorePulse
 * Plugin URI: https://ssebuwufu.com
 * Description: Real-time Hydration & Performance Monitoring. Shift the load back to the server.
 * Version: 1.0.0
 * Author: Ssebuwufu Moses
 * Author URI: https://x.com/EduTechCenter
 * License: GPL2
 * Text Domain: corepulse
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Define Plugin Constants
define( 'COREPULSE_PATH', plugin_dir_path( __FILE__ ) );
define( 'COREPULSE_URL', plugin_dir_url( __FILE__ ) );

// Load the CorePulse Orchestrator
require_once COREPULSE_PATH . 'includes/class-corepulse.php';

function corepulse_run() {
    $plugin = new CorePulse();
}
corepulse_run();