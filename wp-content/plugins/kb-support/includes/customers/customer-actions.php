<?php
/**
 * Customer Actions
 *
 * @package     KBS
 * @subpackage  Customers/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Filter the KBS_Tickets_Query status argument if a customer should hide closed tickets.
 *
 * @since	1.2.6
 * @param	array	$statuses	The ticket statuses to include in the query
 * @param	bool	$can_select	True to only return selectable status. False for all
 */
function kbs_customer_action_maybe_hide_closed_tickets( $statuses, $can_select )	{
	if ( ! kbs_is_agent() && ( ! isset( $_REQUEST['show_closed'] ) || '1' != $_REQUEST['show_closed'] ) )	{
		$user_id = get_current_user_id();
		if ( ! empty( $user_id ) && kbs_customer_maybe_hide_closed_tickets( $user_id ) )	{
			if ( ( $key = array_search( 'closed', $statuses ) ) !== false )	{
				unset( $statuses[ $key ] );
			}
		}
	}

	return $statuses;
} // kbs_customer_action_maybe_hide_closed_tickets
add_filter( 'kbs_get_customer_tickets_statuses', 'kbs_customer_action_maybe_hide_closed_tickets', 10, 2 );
