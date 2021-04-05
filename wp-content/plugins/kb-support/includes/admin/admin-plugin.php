<?php
/**
 * Admin Plugin
 *
 * @package     KBS
 * @subpackage  Admin/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Plugins row action links
 *
 * @since	1.0
 * @param	arr		$links	Defined action links
 * @param	str		$file	Plugin file path and name being processed
 * @return	srr		Filtered action links
 */
function kbs_plugin_action_links( $links, $file )	{

	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-settings' ) . '">' . esc_html__( 'Settings', 'kb-support' ) . '</a>';

	if ( $file == 'kb-support/kb-support.php' )	{
		array_unshift( $links, $settings_link );
	}

	return $links;

} // kbs_plugin_action_links
add_filter( 'plugin_action_links', 'kbs_plugin_action_links', 10, 2 );

/**
 * Plugin row meta links
 *
 * @since	1.0
 * @param	arr		$input	Defined meta links
 * @param	str		$file	Plugin file path and name being processed
 * @return	arr		Filtered meta links
 */
function kbs_plugin_row_meta( $input, $file )	{

	if ( $file != 'kb-support/kb-support.php' )	{
		return $input;
	}

	$links = array(
		'<a href="' . esc_url( 'https://kb-support.com/support/' ) . '" target="_blank">' . esc_html__( 'Documentation', 'kb-support' ) . '</a>',
		'<a href="' . esc_url( 'https://kb-support.com/extensions/' ) . '" target="_blank">' . esc_html__( 'Extensions', 'kb-support' ) . '</a>'
	);

	$input = array_merge( $input, $links );

	return $input;

} // kbs_plugin_row_meta
add_filter( 'plugin_row_meta', 'kbs_plugin_row_meta', 10, 2 );

/**
 * Adds rate us text to admin footer when KB Support admin pages are viewed.
 *
 * @since	1.0
 * @param	str		$footer_text	The footer text to output
 * @return	str		Filtered footer text for output
 */
function kbs_admin_footer_rate_us( $footer_text )	{
	global $typenow;

    $disable = kbs_get_option( 'remove_rating' );

	if ( ! $disable && ( 'kbs_ticket' == $typenow || KBS()->KB->post_type == $typenow || 'kbs_form' == $typenow ) )	{

		$footer_text = sprintf(
			__( 'If <strong>KB Support</strong> is helping you support your customers, please <a href="%s" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!', 'kb-support'
			),
			'https://wordpress.org/support/view/plugin-reviews/kb-support?rate=5#postform'
		);

	}

	return $footer_text;
} // kbs_admin_footer_rate_us
add_filter( 'admin_footer_text', 'kbs_admin_footer_rate_us' );
