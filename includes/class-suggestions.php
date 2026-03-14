<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CorePulse_Suggestions {
    
    /**
     * Smart Pulse Heuristic Engine
     * Analyzes asset data to formulate conversational, AI-like optimization suggestions.
     */
    public static function analyze_asset( $handle, $type, $bytes, $dependents ) {
        $kb = round( $bytes / 1024 );
        $handle_lower = strtolower( $handle );
        $suggestion = '';

        // Known Heavy Offender Dictionary
        $known_offenders = array(
            'moment'       => 'Moment.js is outdated and heavy. Replace with native Intl.DateTimeFormat (0kb cost).',
            'lodash'       => 'Lodash detected. Consider swapping to native ES6 Array methods to save payload.',
            'swiper'       => 'Heavy slider library. If this page has no sliders, kill this script immediately.',
            'fontawesome'  => 'FontAwesome loads thousands of unused icons. Consider swapping to inline SVGs.',
            'dialog'       => 'Popup library detected. Use the native HTML <dialog> element instead.',
            'imagesloaded' => 'Native lazy loading exists in modern browsers. This library is likely redundant.',
            'share'        => 'Social sharing library. Replace with lightweight, native anchor links.',
            'recaptcha'    => 'Google reCAPTCHA blocks the main thread. Dequeue it on pages without forms.'
        );

        foreach ( $known_offenders as $key => $advice ) {
            if ( strpos( $handle_lower, $key ) !== false ) {
                $suggestion = $advice;
                break;
            }
        }

        // Dynamic WooCommerce Detection
        if ( empty( $suggestion ) && strpos( $handle_lower, 'wc-' ) === 0 || strpos( $handle_lower, 'woocommerce' ) !== false ) {
            $suggestion = 'WooCommerce asset detected. If this is not a shop, cart, or checkout page, snipe it.';
        }

        // Dynamic Builder Detection
        if ( empty( $suggestion ) && ( strpos( $handle_lower, 'elementor' ) !== false || strpos( $handle_lower, 'divi' ) !== false ) ) {
            $suggestion = 'Builder asset. Page builders often load global scripts on simple pages. Verify if it is actively used here.';
        }

        // File Size Heuristics (If no specific library matched)
        if ( empty( $suggestion ) ) {
            if ( $type === 'js' ) {
                if ( $kb > 300 ) {
                    $suggestion = 'MASSIVE JavaScript payload. This is severely blocking the main thread. Snipe if possible.';
                } elseif ( $kb > 100 ) {
                    $suggestion = 'Heavy execution time expected. Dequeue if not required on this specific route.';
                }
            } elseif ( $type === 'css' ) {
                if ( $kb > 150 ) {
                    $suggestion = 'Massive stylesheet. Consider extracting Critical CSS and dequeuing this file.';
                }
            }
        }

        // Dependency Warning Overlay
        if ( count( $dependents ) > 3 ) {
            $suggestion = 'Warning: This is a highly requested foundational file. Killing it is extremely risky.';
        }

        // Default Fallback
        if ( empty( $suggestion ) ) {
            $suggestion = $type === 'js' ? 'Standard JS payload. Snipe to save bandwidth if unused.' : 'Standard stylesheet. Safe to dequeue if styles are unused.';
        }

        return $suggestion;
    }
}