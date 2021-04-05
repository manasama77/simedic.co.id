<?php
/**
 * Agent Actions
 *
 * @package     KBS
 * @subpackage  Agent/Functions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Log an agents online status.
 *
 * Sets a transient that tells us the agent is actively logged on.
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_agent_status()	{
	if ( is_admin() && is_user_logged_in() )	{
		$agent_id = get_current_user_id();
		$expire   = MINUTE_IN_SECONDS * 30;
		$expire   = apply_filters( 'kbs_agent_status_expire_time', $expire );
		$screen   = get_current_screen();

		if ( ! empty( $agent_id ) && kbs_is_agent( $agent_id ) )	{
			$transient_key = 'kbs_active_agent_' . $agent_id;
			set_transient( $transient_key, $screen->id, $expire );
		}
	}
} // kbs_set_agent_status
add_action( 'current_screen', 'kbs_set_agent_status' );

/**
 * Sets an agents status to offline during logoff.
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_agent_offline( $agent_id = 0 )	{
	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	if ( kbs_is_agent( $agent_id ) )	{
		delete_transient( 'kbs_active_agent_' . $agent_id );
	}
} // kbs_set_agent_offline
add_action( 'login_form_logout', 'kbs_set_agent_offline' );

/**
 * Adds default values to the user meta when a new agent is added.
 *
 * @since   1.2.5
 * @param   int     $user_id    WP User ID
 * @return  void
 */
function kbs_add_default_agent_data_to_user( $user_id ) {
    /**
     * Fixes a bug whereby when get_password_reset_key() is called
     * it results in the `profile_updated` hook being used.
     * If we leave these actions in place during bulk calls to get_password_reset_key()
     * timeouts are experienced.
     *
     * @since   1.5.3
     */
    if ( did_action( 'retrieve_password' ) )    {
        return;
    }

    if ( is_admin() && kbs_is_agent( $user_id ) ) {
        $agent = new KBS_Agent( $user_id );
    }
} // kbs_add_default_agent_data_to_user
add_action( 'user_register', 'kbs_add_default_agent_data_to_user' );
add_action( 'profile_update', 'kbs_add_default_agent_data_to_user' );

/**
 * Adjust agent ticket count on assignment.
 *
 * @since   1.2.5
 * @param   int     $ticket_id  Ticket ID
 * @param   int     $new_agent  Agent being assigned
 * @param   int     $old_agent  Previous agent
 * @return  void
 */
function kbs_update_agent_open_ticket_count_assignment( $ticket_id, $new_agent = 0, $old_agent = 0 ) {

    if ( $new_agent == $old_agent ) {
        return;
    }

    $agent  = new KBS_Agent( $new_agent );
    $_agent = new KBS_Agent( $old_agent );

    if ( ! $agent ) {
        return;
    }

    $statuses = $agent->get_open_statuses();

    if ( in_array( get_post_status( $ticket_id ), $statuses ) )   {
        $agent->increase_open_tickets();

        if ( $_agent )  {
            $_agent->decrease_open_tickets();
        }
    }
} // kbs_update_agent_open_ticket_count_assignment
add_action( 'kbs_assigned_agent', 'kbs_update_agent_open_ticket_count_assignment', 10, 3 );

/**
 * Adjust agent open ticket count when a ticket's status changes.
 *
 * @since   1.2.5
 * @param   int     $ticket_id  The ticket ID
 * @param   string  $new_status The new status of the ticket
 * @param   string  $old_status The old status of the ticket
 * @param   object  $ticket     KBS_Ticket class object
 * @return  void
 */
function kbs_update_agent_open_ticket_count_status_change( $ticket_id, $new_status, $old_status, $ticket ) {
    if ( empty( $ticket->agent_id ) || $old_status == $new_status )   {
        return;
    }

    $agent = new KBS_Agent( $ticket->agent_id );

    if ( ! $agent ) {
        return;
    }

    $statuses = $agent->get_open_statuses();

    if ( in_array( $new_status, $statuses ) && in_array( $old_status, $statuses ) ) {
        return;
    }

    if ( in_array( $new_status, $agent->get_open_statuses() ) )   {
        $agent->increase_open_tickets();
    } else  {
        $agent->decrease_open_tickets();
    }
} // kbs_update_agent_open_ticket_count_status_change
add_action( 'kbs_update_ticket_status', 'kbs_update_agent_open_ticket_count_status_change', 10, 4 );
