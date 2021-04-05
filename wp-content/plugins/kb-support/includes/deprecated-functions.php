<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     KBS
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the ticket ID.
 *
 * @deprecated	1.1
 *
 * @since	1.0
 * @param	int|obj		$ticket		Post object, or ID.
 * @return	str			The ticket ID with prefix and suffix
 */
function kbs_get_ticket_id( $ticket )	{
	$backtrace = debug_backtrace();

	_kbs_deprecated_function( __FUNCTION__, '1.1', null, $backtrace );

	if ( is_numeric( $ticket ) )	{
		$ticket_id = $ticket;
	} else	{
		$ticket_id = $ticket->ID;
	}

	$prefix = kbs_get_option( 'ticket_prefix', '' );
	$suffix = kbs_get_option( 'ticket_suffix', '' );

	$ticket_id = $prefix . $ticket_id . $suffix;

	return apply_filters( 'kbs_ticket_id', $ticket_id );
} // kbs_get_ticket_id
