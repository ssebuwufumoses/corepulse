<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Suggestions {
    public static function get_replacement( $lib_handle ) {
        $alternatives = array(
            'moment'       => 'Use native Intl.DateTimeFormat (0kb cost)',
            'lodash'       => 'Use native ES6 Array methods',
            'jquery'       => 'Consider the WordPress Interactivity API',
            'react'        => 'Check hydration cost. Consider Server-Centric rendering.',
            'elementor'    => 'Heavy page builder detected. Monitor DOM depth closely.',
            'swiper'       => 'Heavy slider library. Consider native CSS snap-scrolling.',
            'dialog'       => 'Heavy popup library. Use the native HTML <dialog> element.',
            'imagesloaded' => 'Native lazy loading exists. Consider removing.',
            'share-link'   => 'Heavy social library. Use lightweight anchor links.',
            'frontend'     => 'Massive execution time. Dequeue if not required on this page.'
        );

        foreach ( $alternatives as $key => $suggestion ) {
            if ( strpos( strtolower( $lib_handle ), $key ) !== false ) {
                return $suggestion;
            }
        }
        
        return false;
    }
}