<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Hydration_Guard {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'inject_hydration_monitor' ) );
    }

    public function inject_hydration_monitor() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $script = '(function() {
            var originalError = console.error;
            window.corePulseHydrationErrors = [];
            console.error = function() {
                var args = Array.prototype.slice.call( arguments );
                if ( args[0] && typeof args[0] === "string" && args[0].toLowerCase().indexOf( "hydration" ) !== -1 ) {
                    window.corePulseHydrationErrors.push( args[0] );
                    window.dispatchEvent( new Event( "corepulse_hydration_error" ) );
                }
                originalError.apply( console, args );
            };
        })();';

        wp_register_script( 'corepulse-hydration-trap', '', array(), '1.2.0', false );
        wp_enqueue_script( 'corepulse-hydration-trap' );
        wp_add_inline_script( 'corepulse-hydration-trap', $script );
    }
}
