<?php
/**
 * Participant Functions
 *
 * @package     KBS
 * @subpackage  Paricipant/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether participants are enabled.
 *
 * @since	1.2.4
 * @return	bool	True if participants are enabled, otherwise false
 */
function kbs_participants_enabled()	{
	return kbs_get_option( 'enable_participants', false );
} // kbs_participants_enabled

/**
 * Whether or not participants should be copied into ticket communications.
 *
 * @since   1.2.4
 * @return  bool    True if they should be copied, otherwise false
 */
function kbs_copy_participants_in_communications()   {
    $enabled = false;

    if ( kbs_participants_enabled() ) {
        $enabled = kbs_get_option( 'copy_participants', false );
    }

    return $enabled;
} // kbs_copy_participants_in_communications

/**
 * Whether or not the given user is a participant of the current ticket.
 *
 * @since	1.2.4
 * @param	int|object	$ticket             Ticket ID or KBS_Ticket object
 * @param	mixed	$customer_id_or_email   Customer ID, email address or KBS_Customer object
 * @return	bool	True if a participant of the ticket, otherwise false
 */
function kbs_is_ticket_participant( $ticket, $customer_id_or_email )	{
	$ticket         = kbs_get_ticket( $ticket );
	$is_participant = false;

	if ( $ticket )	{
		$is_participant = $ticket->is_participant( $customer_id_or_email );
	}

	return $is_participant;
} // kbs_is_ticket_participant

/**
 * Retrieve the ticket participants.
 *
 * @since	1.2.4
 * @param	int|object	$ticket				Ticket ID or KBS_Ticket object
 * @param	bool		$include_primary	True to include the primary employee in the count
 * @return	array		Array of participants
 */
function kbs_get_ticket_participants( $ticket, $include_primary = true )	{
	$ticket       = kbs_get_ticket( $ticket );
	$participants = array();
	
	if ( $ticket )	{
		$participants = $ticket->participants;

		if ( ! $include_primary && in_array( $ticket->email, $participants ) )	{
			if ( ( $key = array_search( $ticket->email, $participants ) ) !== false )	{
				unset( $participants[ $key ] );
			}
		}
	}

	return $participants;
} // kbs_get_ticket_participants

/**
 * Retrieve the count of ticket participants.
 *
 * @since	1.2.4
 * @param	int|object	$ticket				Ticket ID or KBS_Ticket object
 * @param	bool		$include_primary	True to include the primary employee in the count
 * @return	int			Count of participants
 */
function kbs_get_ticket_participant_count( $ticket, $include_primary = false  )	{
	return count( kbs_get_ticket_participants( $ticket, $include_primary ) );
} // kbs_get_ticket_participant_count

/**
 * List the ticket participants.
 *
 * @since	1.2.4
 * @param	int|object	$ticket				Ticket ID or KBS_Ticket object
 * @param	bool		$include_primary	True to include the primary employee
 * @param	bool		$email_only			True returns a list of email addresses only
 * @param	bool		$with_id			True includes a customers ID if they exist
 * @return	string		Count of participants
 */
function kbs_list_ticket_participants( $ticket, $args = array()  )	{
	$defaults = array(
		'primary'     => false,
		'email_only'  => true,
		'remove_link' => false
	);

	$args          = wp_parse_args( $args, $defaults );
	$participants  = array();
	$_participants = kbs_get_ticket_participants( $ticket, $args['primary'] );

	if ( empty( $_participants ) )	{
		return sprintf(
			__( 'There are no participants of this %s.', 'kb-support' ),
			kbs_get_ticket_label_singular( true )
		);
	}

	foreach( $_participants as $email )	{
		$output   = '<ul class="participant-container space-between">';
		$customer = false;
		$link     = '';

		$output .= '<li class="participant-item">';
		$output .= sprintf( '<a href="mailto:%1$s">%1$s</a>', $email );
		$output .= '</li>';

		if ( ! $args['email_only'] )	{
			$customer = new KBS_Customer( $email );

			$output .= '<li class="participant-item">';

			if ( $customer && ! empty( $customer->id ) )	{
				$link    = add_query_arg( array(
					'post_type' => 'kbs_ticket',
					'page'      => 'kbs-customers',
					'view'      => 'userdata',
					'id'        => $customer->id
				) );
				$output .= sprintf(
					'<a href="%s">%s</a>',
					$link,
					$customer->name
				);
			} else	{
				$output .= '&mdash;';
			}

			$output .= '</li>';
		}

		if ( $args['remove_link'] )	{
			$output .= '<li class="participant-item">';
			$output .= sprintf(
				'<a href="#" class="kbs-delete remove-participant" data-participant="%s">%s</a>',
				$email,
				__( 'Remove', 'kb-support' )
			);
			$output .= '</li>';
		}

		$output .= '</ul>';

		$participants[] = $output;
	}

	return implode( '', $participants );
} // kbs_list_ticket_participants

/**
 * Update the list of ticket participants.
 *
 * @since	1.2.4
 * @param	int				$ticket				Ticket ID or KBS_Ticket object
 * @param	string|array	$email_addresses	Email address, or array of addresses, to add
 * @return	array			Array of participants
 */
function kbs_add_ticket_participants( $ticket, $email_addresses )	{
	$ticket       = kbs_get_ticket( $ticket );
	$participants = array();

	if ( $ticket )	{
		$participants = $ticket->add_participants( $email_addresses );
	}

	return $participants;
} // kbs_add_ticket_participants

/**
 * Removes a ticket participants.
 *
 * @since	1.2.4
 * @param	int				$ticket				Ticket ID or KBS_Ticket object
 * @param	string|array	$email_addresses	Email address, or array of addresses, to remove
 * @return	array			Array of remaining participants
 */
function kbs_remove_ticket_participants( $ticket, $email_addresses )	{
	$ticket       = kbs_get_ticket( $ticket );
	$participants = array();

	if ( $ticket )	{
		$participants = $ticket->remove_participants( $email_addresses );
	}

	return $participants;
} // kbs_remove_ticket_participants

/**
 * Filters email headers if we CC in the ticket participants in agent replies.
 *
 * @since   1.2.4
 * @param   array   $headers        Email headers
 * @param   int     $ticket_id      Ticket ID
 * @param   array   $ticket_data    Array of ticket meta data
 * @return  string
 */
function kbs_maybe_cc_participants( $headers, $ticket_id, $ticket_data ) {
    $cc = kbs_copy_participants_in_communications();
    $cc = apply_filters( 'kbs_maybe_cc_participants', $cc, $ticket_id, $ticket_data );

    if ( $cc )  {
        $participants = kbs_get_ticket_participants( $ticket_id, false );

        if ( ! empty( $participants ) )   {
            foreach( $participants as $participant )    {
                $email = is_email( $participant );

                if ( $email )   {
                    $headers[] = 'Cc: ' . $participant;
                }
            }
        }
    }

    return $headers;
} // kbs_maybe_cc_participants
add_filter( 'kbs_ticket_headers', 'kbs_maybe_cc_participants', 10, 3 );
add_filter( 'kbs_ticket_reply_headers', 'kbs_maybe_cc_participants', 10, 3 );
add_filter( 'kbs_ticket_closed_headers', 'kbs_maybe_cc_participants', 10, 3 );
