<?php
/**
 * MedZone_Lite functions and definitions
 *
 * @link    https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package MedZone_Lite
 * @since   1.0
 */

/**
 * Load Autoloader
 */
require_once 'inc/class-medzone-lite-autoloader.php';
/**
 * Instantiate it
 */
$medzone = new MedZone_Lite();

if ( ! function_exists( 'wp_body_open' ) ) {
    function wp_body_open() {
        do_action( 'wp_body_open' );
    }
}