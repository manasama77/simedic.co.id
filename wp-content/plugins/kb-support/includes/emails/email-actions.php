<?php
/**
 * Email Actions
 *
 * @package     KBS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Disable notification emails if the To address is included in the no notification list.
 *
 * @since	1.2.8
 * @param   string	$email      Email address
 * @param   object  $ticket     KBS_Ticket Ticket Object
 * @return  string|false        The email address to send to, or false
 */
function kbs_disable_emails_to_no_notification_addresses( $email, $ticket )	{
	if ( kbs_maybe_remove_email_from_notification( $ticket->email ) )  {
        $email = false;
	}

	return $email;
} // kbs_disable_emails_to_no_notification_addresses
add_filter( 'kbs_ticket_received_to_email', 'kbs_disable_emails_to_no_notification_addresses', 999, 2 );

/**
 * Triggers Ticket Received email to be sent after the ticket status is updated
 *
 * @since	1.0
 * @param	int		$ticket_id	Ticket ID
 * @return	void
 */
function kbs_trigger_ticket_received( $ticket_id ) {
	// Make sure we don't send while editing a ticket
	if ( isset( $_POST['kbs-action'] ) && 'edit_ticket' == $_POST['kbs-action'] ) {
		return;
	}

	kbs_email_ticket_received( $ticket_id );
} // kbs_trigger_ticket_received
add_action( 'kbs_add_ticket',  'kbs_trigger_ticket_received', 999, 1 );
add_action( 'kbs_save_ticket', 'kbs_trigger_ticket_received', 999, 1 );

/**
 * Trigger the agent assigned email for new tickets.
 *
 * @since   1.5.3
 * @param   int     $ticket_id  The ticket ID
 * @param   object  $ticket     KBS_Ticket object
 * @return  void
 */
function kbs_trigger_new_ticket_agent_assigned_email( $ticket_id )    {
    $ticket = new KBS_Ticket( $ticket_id );

    if ( ! empty( $ticket->agent_id ) ) {
        kbs_email_agent_assigned_to_ticket( $ticket_id, $ticket->agent_id, 0 );
    }

    if ( ! empty( $ticket->agents ) )   {
        foreach( $ticket->agents as $agent_id ) {
            kbs_email_agent_assigned_to_ticket( $ticket_id, $agent_id, 0 );
        }
    }
} // kbs_trigger_new_ticket_agent_assigned_email
add_action( 'kbs_add_ticket', 'kbs_trigger_new_ticket_agent_assigned_email', PHP_INT_MAX );

/**
 * Trigger the agent assigned email for existing tickets.
 *
 * @since   1.1
 * @param   str     $meta_key   The meta key being updated
 * @param   int     $meta_value The new value of the meta key
 * @param   int     $prev_value The previous value of the meta key
 * @param   int     $ticket_id  The ticket ID
 * @return  void
 */
function kbs_trigger_agent_assigned_email( $meta_key, $meta_value, $prev_value, $ticket_id ) {
    if ( '_kbs_ticket_agent_id' != $meta_key && '_kbs_ticket_agents' != $meta_key )  {
        return;
    }

	if ( '_kbs_ticket_agent_id' == $meta_key && $prev_value != $meta_value )	{
		kbs_email_agent_assigned_to_ticket( $ticket_id, $meta_value, $prev_value );
	}

	if ( '_kbs_ticket_agents' == $meta_key )	{
		foreach( $meta_value as $agent_id )	{
			if ( empty( $prev_value ) || ! in_array( $agent_id, $prev_value ) )	{
				kbs_email_agent_assigned_to_ticket( $ticket_id, $agent_id, $prev_value );
			}
		}
	}

} // kbs_trigger_agent_assigned_email
add_action( 'kbs_update_ticket_meta_key', 'kbs_trigger_agent_assigned_email', 999, 4 );

/**
 * Add additional agents to reply notifications.
 *
 * @since   1.1
 * @param   str     $headers    Email headers
 * @param   int     $ticket_id  Ticket ID
 * @param   arr     $data       Reply data
 *
 */
function kbs_add_additional_agents_to_reply_notifications( $headers, $ticket_id, $data ) {

    if ( kbs_multiple_agents() )  {
        $agents = kbs_get_workers_of_ticket( $ticket_id );

        if ( ! empty( $agents ) )   {
            $emails = kbs_get_option( 'admin_notice_emails', false );
            $emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
            $emails = array_map( 'trim', explode( "\n", $emails ) );

            if ( in_array( '{agent}', $emails ) )   {
                $agent_emails = array();
                foreach( $agents as $agent_id ) {
                    $agent_data = get_userdata( $agent_id );

                    if ( $agent_data )  {
                        $agent_emails[] = $agent_data->user_email;
                    }
                }

                if ( ! empty( $agent_emails ) ) {
                    foreach( $agent_emails as $email )  {
                        $headers[] = 'Cc: ' . $email;
                    }
                }

            }
        }
    }

    return $headers;
} // kbs_add_additional_agents_to_reply_notifications
add_filter( 'kbs_admin_reply_notification_headers', 'kbs_add_additional_agents_to_reply_notifications', 10, 3 );

/**
 * Adds attachments to ticket emails.
 *
 * This action is usually hooked via the {ticket_files} email tag.
 *
 * @since   1.1.9
 * @param   arr     $attachments    Array of files to attach to email
 * @param   int     $ticket_id      The ticket or reply ID
 * @return  arr     Array of files to attach to email.
 */
function kbs_maybe_attach_files_to_email_action( $attachments, $ticket_id, $data, $reply_id = false ) {

    // These actions to not parse a reply ID so we need to determine if replies exist
    if ( doing_action( 'kbs_ticket_closed_attachments' ) || doing_action( 'kbs_agent_assigned_attachments' ) )    {
        $last_reply = kbs_get_last_reply( $ticket_id );

        if ( $last_reply )  {
            $reply_id = $last_reply->ID;
        }
    }

    if ( $reply_id )    {
        $ticket_id = $reply_id;
    }

    return $attachments + kbs_maybe_attach_files_to_email( $ticket_id );

} // kbs_maybe_attach_files_to_email_action

/**
 * Trigger the sending of a Test Email
 *
 * @since	1.0
 * @return	void
 */
function kbs_send_test_email()	{

	if ( ! isset( $_GET['kbs_action'] ) || 'send_test_email' != $_GET['kbs_action'] )	{
		return;
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'kbs-test-email' ) ) {
		return;
	}

	// Send a test email
	kbs_email_test_ticket_received();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'kbs_action' ) ); exit;
} // kbs_send_test_email
add_action( 'init', 'kbs_send_test_email' );
