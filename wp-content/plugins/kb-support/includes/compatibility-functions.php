<?php
/**
 * Compatibility functions to address conflicts with other plugins and themes
 *
 * @package     KBS
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
        exit;

/**
 * Enables admin access for Support Workers when WooCommerce is installed.
 *
 * @see     https://github.com/KB-Support/kb-support/issues/54
 * @since   1.1.5
 * @param   bool    $prevent_access     Whether or not the user can access admin
 * @return  Whether or not the user can access admin
 */
function kbs_agent_woocommerce_admin_access( $prevent_access )  {
    if ( $prevent_access && kbs_is_agent() )    {
        $prevent_access = false;
    }

    return $prevent_access;
} // kbs_agent_woocommerce_admin_access
add_filter( 'woocommerce_prevent_admin_access', 'kbs_agent_woocommerce_admin_access', 900 );

/**
 * Enables URL generation for All in One WP Security
 * who have the change login page feature enabled.
 *
 * @since   1.1.5
 *
 */
function kbs_filter_aiowps_url()    {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    $plugin = 'all-in-one-wp-security-and-firewall/wp-security.php';

    if ( is_plugin_active( $plugin ) )  {
        add_filter( 'kbs_ticket_admin_url', 'kbs_aiowps_ticket_admin_link', 10, 2 );
    }

} // kbs_filter_aiowps_url
add_action( 'wp_loaded', 'kbs_filter_aiowps_url' );

function kbs_aiowps_ticket_admin_link( $url, $ticket_id ) {
	global $aio_wp_security;

	if ( '1' == $aio_wp_security->configs->get_value( 'aiowps_enable_rename_login_page' ) )    {

		$secret_slug = $aio_wp_security->configs->get_value( 'aiowps_login_page_slug' );

        if ( get_option( 'permalink_structure' ) )  {
            $home_url = trailingslashit (home_url() );
        } else  {
            $home_url = trailingslashit( home_url() ) . '?';
        }

		$secret_login = $home_url . $secret_slug;
		$red_url      = urlencode( $url );
		$final        = add_query_arg( array(
                        'redirect_to' => $red_url,
                        'reauth'      => 1,
                        ), $secret_login );
        $url          = $final;

	}

	return $url;
} // kbs_aiowps_ticket_admin_link
