<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     KBS
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Sanitizes a string key for KBS Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
 *
 * @since 	0.1
 * @param	str		$key	String key
 * @return	str		Sanitized key
 */
function kbs_sanitize_key( $key ) {
	$raw_key = $key;
	$key = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	/**
	 * Filter a sanitized key string.
	 *
	 * @since 2.5.8
	 * @param	str	$key     Sanitized key.
	 * @param	str $raw_key The key prior to sanitization.
	 */
	return apply_filters( 'kbs_sanitize_key', $key, $raw_key );
} // kbs_sanitize_key
