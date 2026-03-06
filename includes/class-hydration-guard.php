<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Hydration_Guard {
    public function __construct() {
        add_action( 'wp_head', array( $this, 'inject_hydration_monitor' ), 1 );
    }

    public function inject_hydration_monitor() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <script id="corepulse-hydration-trap">
            (function() {
                const originalError = console.error;
                window.corePulseHydrationErrors = [];

                console.error = function(...args) {
                    if (args[0] && typeof args[0] === 'string' && args[0].toLowerCase().includes('hydration')) {
                        window.corePulseHydrationErrors.push(args[0]);
                        window.dispatchEvent(new Event('corepulse_hydration_error'));
                    }
                    originalError.apply(console, args);
                };
            })();
        </script>
        <?php
    }
}