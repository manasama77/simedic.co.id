<?php
/**
 * Agent Functions
 *
 * Functions related to agents
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve agent roles.
 *
 * @since	1.2
 * @return	array	Array of user roles for agents
 */
function kbs_get_agent_user_roles()  {
	$roles = array( 'support_agent', 'support_manager' );

	if ( kbs_get_option( 'admin_agents', false ) )	{
		$roles[] = 'administrator';
	}

	$roles = apply_filters( 'kbs_agent_user_roles', $roles );

    return $roles;
} // kbs_get_agent_user_roles

/**
 * Whether or not a multiple agents is enabled.
 *
 * @since	1.1
 * @return	bool	True if enabled, otherwise false
 */
function kbs_multiple_agents()  {
    return kbs_get_option( 'multiple_agents', false );
} // kbs_multiple_agents

/**
 * Whether or not agents can select ticket status during reply.
 *
 * @since	1.4
 * @return	bool	True or false
 */
function kbs_agent_can_set_status_on_reply()	{
	$can_set = kbs_get_option( 'agent_update_status_reply' );
	$can_set = apply_filters( 'kbs_agent_can_set_status_on_reply', $can_set );

	return (bool) $can_set;
} // kbs_agent_can_set_status_on_reply

/**
 * Which status should be set by default when an agent replies?
 *
 * @since	1.4
 * @param	int		$ticket_id	The ticket ID
 * @return	string	Status that should be selected by default
 */
function kbs_agent_get_default_reply_status( $ticket_id = 0 )	{
	$status   = kbs_get_option( 'agent_reply_status' );
	$statuses = kbs_get_ticket_statuses();

	if ( $status && ( ! is_array( $statuses ) || ! array_key_exists( $status, $statuses ) ) )	{
		$status = false;
	}

	if ( ! $status && ! empty( $ticket_id ) )	{
		$status = get_post_status( $ticket_id );
	}

	$status = apply_filters( 'kbs_agent_default_reply_status', $status );

	return $status;
} // kbs_agent_get_default_reply_status

/**
 * Retrieve the agent ID from a ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	int		The agent ID
 */
function kbs_get_agent_id_from_ticket( $ticket_id )	{
	return get_post_meta( $ticket_id, '_kbs_ticket_agent_id', true );
} // kbs_get_agent_id_from_ticket

/**
 * Retrieve the additional agents from a ticket.
 *
 * @since	1.1
 * @param	int		$ticket_id	The ticket ID
 * @return	arr		Array of secondary agents assigned to a ticket
 */
function kbs_get_workers_of_ticket( $ticket_id )	{

	$agents = array();

	if ( kbs_multiple_agents() )	{
		$agents = get_post_meta( $ticket_id, '_kbs_ticket_agents', true );
	}

	return apply_filters( 'kbs_workers_of_ticket', $agents );
} // kbs_get_workers_of_ticket

/**
 * Retrieve all agents.
 *
 * @since	1.0
 * @param	bool	$ids	True to return array of IDs, false for array of user objects
 * @return	mixed
 */
function kbs_get_agents( $ids = false )	{
	$role = kbs_get_agent_user_roles();

	$users = kbs_get_users_by_role( $role, $ids );
	
	return $users;
} // kbs_get_agents

/**
 * Retrieve ticket admin user roles.
 *
 * @since	1.2.5
 * @return	array	Array of KBS admin user roles
 */
function kbs_get_ticket_admin_user_roles()	{
	$roles = array( 'support_manager' );

	if ( kbs_get_option( 'admin_agents' ) )	{
		$roles[] = 'administrator';
	}

	$roles = apply_filters( 'kbs_ticket_admin_user_roles', $roles );

	return $roles;
} // kbs_get_ticket_admin_user_roles

/**
 * Whether or not the user is a KBS ticket admin.
 *
 * @since	1.0
 * @param	int|object	$agent_id	The user ID or a WP_User object to check.
 * @return	bool	True if the user is a KBS ticket admin, or false
 */
function kbs_is_ticket_admin( $agent_id = 0 )	{

	if ( ! empty( $agent_id ) && is_numeric( $agent_id ) ) {
        $agent = get_userdata( $agent_id );
    } else {
        $agent = wp_get_current_user();
    }

	$is_admin = false;

    if ( ! empty( $agent ) ) {
		$roles = kbs_get_ticket_admin_user_roles();

		foreach( $roles as $role )	{
			$is_admin = in_array( $role, (array) $agent->roles );

			if ( $is_admin )	{
				break;
			}
		}
    }

	$is_admin = apply_filters( 'kbs_is_ticket_admin', $is_admin, $agent );

	return $is_admin;

} // kbs_is_ticket_admin

/**
 * Whether or not the user is a KBS admin.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID to check.
 * @return	bool	True if the user is a KBS admin, or false
 */
function kbs_is_kbs_admin( $agent_id = 0 )	{

	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	return ( user_can( $agent_id, 'administrator' ) || user_can( $agent_id, 'manage_ticket_settings' ) );

} // kbs_is_kbs_admin

/**
 * Whether or not the user is an agent.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID to check.
 * @return	bool	True if the user is an agent, or false
 */
function kbs_is_agent( $agent_id = 0 )	{

	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	$agents = kbs_get_agents( true );

	return in_array( $agent_id, $agents );

} // kbs_is_agent

/**
 * Whether or not an agent can submit a ticket from the front end.
 *
 * @since	1.0.4
 * @return	bool	True if an agent can submit a ticket, otherwise false
 */
function kbs_agent_can_submit( $can_submit )	{
	if ( kbs_is_agent() )	{
		$can_submit = apply_filters( 'kbs_agent_can_submit', false );

		add_filter( 'kbs_user_cannot_submit', function( $output ) {
			ob_start();
			echo kbs_display_notice( 'agents_cannot_submit' );
			return ob_get_clean();
		} );
	}

	return $can_submit;
} // kbs_agent_can_submit
add_filter( 'kbs_user_can_submit', 'kbs_agent_can_submit', 999 );

/**
 * Whether or not an agent can view the ticket.
 *
 * @since	1.0
 * @param	int|obj	$ticket		The ticket ID or a KBS_Ticket class object
 * @param	int		$agent_id	The user ID of the agent.
 * @return	bool	True if agent can view, otherwise false
 */
function kbs_agent_can_access_ticket( $ticket = '', $agent_id = '' )	{
	if ( empty( $ticket ) )	{
		return false;
	}

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
		if ( empty( $ticket->ID ) )	{
			return false;
		}
	}

	if ( ! kbs_get_option( 'admin_agents' ) && current_user_can( 'administrator' ) )	{
		return false;
	}

    if ( current_user_can( 'manage_ticket_settings' ) ) {
        return true;
    }

	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

    $return   = false;
	$restrict = kbs_get_option( 'restrict_agent_view' );

	if ( ! empty( $ticket->agent_id ) && $agent_id == $ticket->agent_id )	{
		$return = true;
	} elseif ( kbs_departments_enabled() )	{
		$departments       = kbs_get_agent_departments( $agent_id );
		$ticket_department = kbs_get_department_for_ticket( $ticket->ID );

		if ( ! empty( $departments ) && ! empty( $ticket_department ) && ! empty( $ticket_department->term_id ) )	{
			if ( $agent_id == $ticket->agent_id || in_array( $ticket_department->term_id, $departments ) )	{
				$return = true;
			}
		}
	} elseif ( empty( $ticket->agent_id ) || $agent_id == $ticket->agent_id )	{
		$return = true;
	}

	if ( ! $restrict )	{
		$return = true;
	}

    if ( kbs_multiple_agents() )    {
        if ( in_array( $agent_id, $ticket->agents ) )   {
            $return = true;
        }
    }

	$allowed_statuses = array( 'new', 'auto-draft', 'draft' );
	if ( in_array( get_post_status( $ticket->ID ), $allowed_statuses ) )	{
		$return = true;
	}

	$return = apply_filters( 'kbs_agent_can_access_ticket', $return, $ticket, $agent_id );

	return (bool) $return;
} // kbs_agent_can_access_ticket

/**
 * Retrieve the agent with the least ticket count.
 *
 * @since	1.0
 * @return	int		Agent ID
 */
function kbs_get_agent_least_tickets()	{
	$agents    = kbs_get_agents( true );
	$agent_id  = 0;
	$low_count = 999999;

	foreach( $agents as $agent )	{
		$ticket_count = kbs_agent_ticket_count( $agent );

		if ( $ticket_count < $low_count )	{
			$low_count = $ticket_count;
			$agent_id  = $agent;
		}
	}

	return $agent_id;
} // kbs_get_agent_least_tickets

/**
 * Retrieve a random agent.
 *
 * @since	1.0
 * @return	int		Random agent user ID
 */
function kbs_get_random_agent()	{
	$agents   = kbs_get_agents( true );
	$agent_id = 0;

	if ( ! empty( $agents ) )	{
		$random   = array_rand( $agents, 1 );
		$agent_id = $agents[ $random ];
	}

	return $agent_id;
} // kbs_get_random_agent

/**
 * Retrieve the count of agent tickets.
 *
 * @since	1.0
 * @param	int		$agent_id	The agent ID for which to retrieve the count.
 * @return	int		Count of agent tickets
 */
function kbs_agent_ticket_count( $agent_id )	{
	$tickets = kbs_count_tickets( array( 'agent' => $agent_id ) );
	$total   = 0;

	if ( ! empty( $tickets ) )	{
		$active_statuses = kbs_get_active_ticket_status_keys();
		foreach( $tickets as $status => $count )	{
			if ( ! empty( $tickets->$status ) && in_array( $status, $active_statuses ) )	{
				$total += $count;
			}
		}
	}

	return $total;
} // kbs_agent_ticket_count

/**
 * Auto assigns an agent to a ticket.
 *
 * @since	1.0
 * @param	arr		$ticket_data	Data passed to ticket
 * @return	void
 */
function kbs_auto_assign_agent( $ticket_data )	{
	$auto_assign = kbs_get_option( 'assign_on_submit', false );

	if ( ! empty( $auto_assign ) )	{
		switch( $auto_assign )	{
			case 'least':
				$ticket_data['agent_id'] = kbs_get_agent_least_tickets();
				break;
	
			case 'random':
				$ticket_data['agent_id'] = kbs_get_random_agent();		
				break;
	
			default:
				do_action( 'kbs_auto_assign_agent', $ticket_data );
				break;
		}
	
		// If an agent is assigned update status if 'new'
		if ( ! empty( $ticket_data['agent_id'] ) )	{
			if ( empty( $ticket_data['status'] ) || 'new' == $ticket_data['status'] )	{
				$ticket_data['status'] = 'open';
			}
		}
	}

	return $ticket_data;
} // kbs_auto_assign_agent
add_filter( 'kbs_add_ticket_data', 'kbs_auto_assign_agent' );

/**
 * Retrieve all departments that an agent belongs to.
 *
 * @since	1.3
 * @param	int		$agent_id	The agent WP user ID
 * @return	array	Array of department IDs for which the agent belongs
 */
function kbs_get_agent_departments( $agent_id )	{
	global $wpdb;

	$agent_departments = array();

	$results = $wpdb->get_results( $wpdb->prepare(
		"
		SELECT term_id
		FROM $wpdb->termmeta
		WHERE
			meta_key = '%s'
			AND
			meta_value LIKE %s
		", 'kbs_department_agents', '%' . sprintf( ':%d;', $agent_id ) . '%'
	) );

	foreach( $results as $result )	{
		$agent_departments[] = $result->term_id;
	}

	return $agent_departments;
} // kbs_get_agent_departments

/**
 * Retrieve an agents status.
 *
 * This function is a wrapper for the get_transient function
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	mixed	True if online, or false
 */
function kbs_get_agent_status( $agent_id )	{
	return get_transient( 'kbs_active_agent_' . $agent_id );
} // kbs_get_agent_status

/**
 * Whether to display an agents online status.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_display_agent_status()	{
	return kbs_get_option( 'agent_status', false );
} // kbs_display_agent_status

/**
 * Whether or not an agent is online.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	bool	True if online, or false
 */
function kbs_agent_is_online( $agent_id )	{
	$status = kbs_get_agent_status( $agent_id );

	$online = false === $status ? false : true;

	return apply_filters( 'kbs_agent_is_online', $online, $agent_id );
} // kbs_agent_is_online

/**
 * Retrieve an agents online status.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	str		'online' | 'offline'
 */
function kbs_get_agent_online_status( $agent_id )	{
	$status = kbs_agent_is_online( $agent_id ) ? 'online' : 'offline';
	$status = apply_filters( 'kbs_agent_online_status', $status, $agent_id );

	return $status;
} // kbs_get_agent_online_status

/**
 * Retrieve count of agents currently online and available.
 *
 * @since	1.0
 * @return	int
 */
function kbs_get_online_agent_count()	{
	$agent_ids = kbs_get_agents( true );
	$online    = 0;

	foreach( $agent_ids as $agent_id )	{
		if ( kbs_agent_is_online( $agent_id ) )	{
			$online++;
		}
	}

	return (int)$online;
} // kbs_get_online_agent_count

/**
 * Whether or not an agent should be alerted when a reply is received during ticket editing.
 *
 * @since   1.3.4
 * @param   int     $agent_id   WP User ID of agent. Default to current user
 * @return  bool    True to notify, otherwise false
 */
function kbs_alert_agent_ticket_reply( $agent_id = 0 ) {
    $agent_id = ! empty( $agent_id ) ? absint( $agent_id ) : get_current_user_id();

    $alert = get_user_meta( $agent_id, '_kbs_reply_alerts', true );
    $alert = absint( $alert );

    return apply_filters( 'kbs_alert_agent_ticket_reply', $alert, $agent_id );
} // kbs_alert_agent_ticket_reply

/**
 * Redirect when an agent replies to or closes a ticket within admin.
 *
 * @since   1.2.4
 * @param   int     $ticket_id  The ticket ID
 * @param   int     $agent_id   The agent ID
 * @return  void
 */
function kbs_maybe_redirect_on_ticket_save( $ticket_id, $agent_id = 0 )   {
    if ( empty( $agent_id ) )   {
        $agent_id = get_current_user_id();
    }

    $status        = get_post_status( $ticket_id );
    $redirect_type = 'closed' == $status ? 'close' : 'reply';
    $redirect_key  = '_kbs_redirect_' . $redirect_type;
	$redirect      = get_user_meta( $agent_id, $redirect_key, true );
    $default       = 'stay';
    $notice        = 'closed' == $status ? 'ticket_reply_added_closed' : 'ticket_reply_added';

	if ( empty( $redirect ) )	{
		$redirect = $default;
	}

	switch( $redirect )	{
		case 'stay': // Current ticket
		default:
			$url = add_query_arg( array(
				'kbs-message'   => $notice,
                'kbs_ticket_id' => $ticket_id
			), kbs_get_ticket_url( $ticket_id, true ) );
			break;

		case 'list': // All tickets list
			$url = add_query_arg( array(
				'post_type'     => 'kbs_ticket',
				'kbs-message'   => $notice,
				'kbs_ticket_id' => $ticket_id
			), admin_url( 'edit.php' ) );
			break;
	}

	wp_safe_redirect( $url );
	exit;
} // kbs_maybe_redirect_on_ticket_save
