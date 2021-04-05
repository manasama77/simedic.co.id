<?php
/**
 * Functions for privacy
 *
 * @package     KBS
 * @subpackage  Functions/Privacy
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the privacy page.
 *
 * @since   1.2.2
 * @return  int     The page ID for the privacy policy
 */
function kbs_get_privacy_page() {
    $privacy_page    = get_option( 'wp_page_for_privacy_policy' );
    $privacy_page    = apply_filters( 'kbs_privacy_page', $privacy_page );

    return $privacy_page;
} // kbs_get_privacy_page

/**
 * Register the KBS template for a privacy policy.
 *
 * Note, this is just a suggestion and should be customized to meet your businesses needs.
 *
 * @since	1.2.2
 * @return	string	The KBS suggested privacy policy
 */
function kbs_register_privacy_policy_template() {

	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = sprintf(
		__( 'We collect and store information about you during the %s submission process on our website. This information may include, but is not limited to, your name, your email address, and any additional details that may be requested from you for the purpose of handling your support %s.', 'kb-support' ),
		kbs_get_ticket_label_singular( true ),
		kbs_get_ticket_label_plural( true )
	);

	$content .= "\n\n";

	$content .= __( 'Handling this information also allows us to:', 'kb-support' );
	$content .= '<ul>';
	$content .= '<li>- Respond to your support requests, questions and comments</li>';
	$content .= '<li>- Send you important account/support information</li>';
	$content .= '<li>- Set up and administer your account, provide technical and/or customer support, and verify your identity</li>';
	$content .= '</ul>';

	$content .= "\n\n";

	$additional_collection   = array();
	$additional_collection[] = sprintf(
		__( 'Your location and traffic data (including IP address) if you create, or reply to a %s', 'kb-support' ),
		kbs_get_ticket_label_singular( true )
	);

	$additional_collection[] = __( 'Your comments and rating reviews if you choose to leave them on our website', 'kb-support' );

	$additional_collection[] = __( 'Your account email and password to allow you to access your account, if you have one', 'kb-support' );

	$additional_collection[] = sprintf(
		__( 'If you choose to create an account with us, your name, and email address, which will be used to populate the submission form future %s', 'kb-support' ),
		kbs_get_ticket_label_plural( true )
	);

	$additional_collection   = apply_filters( 'kbs_privacy_policy_additional_collection', $additional_collection );

	if ( ! empty( $additional_collection ) )	{
		$content .= __( 'Additionally we may also collect the following information:', 'kb-support' );
		$content .= '<ul>';

		foreach( $additional_collection as $item )	{
			$content .= sprintf( '<li>- %s</li>', $item );
		}

		$content .= '</ul>';
	}

	$content = apply_filters( 'kbs_privacy_policy_content', $content );
	$content = wp_kses_post( $content );

	wp_add_privacy_policy_content( 'KB Support', wpautop( $content ) );
} // kbs_register_privacy_policy_template
add_action( 'admin_init', 'kbs_register_privacy_policy_template' );

/**
 * Given a string, mask it with the * character.
 *
 * First and last character will remain with the filling characters being changed to *. One Character will
 * be left in tact as is. Two character strings will have the first character remain and the second be a *.
 *
 * @since	1.2.2
 * @param	string	$string
 * @return	string	Masked string
 */
function kbs_mask_string( $string = '' ) {

	if ( empty( $string ) ) {
		return '';
	}

	$first_char = substr( $string, 0, 1 );
	$last_char  = substr( $string, -1, 1 );

	$masked_string = $string;

	if ( strlen( $string ) > 2 ) {

		$total_stars = strlen( $string ) - 2;
		$masked_string = $first_char . str_repeat( '*', $total_stars ) . $last_char;

	} elseif ( strlen( $string ) === 2 ) {
		$masked_string = $first_char . '*';
	}

	return $masked_string;

} // kbs_mask_string

/**
 * Given a domain, mask it with the * character.
 *
 * TLD parts will remain intact (.com, .co.uk, etc). All subdomains will be masked t**t.e*****e.co.uk.
 *
 * @since	1.2.2
 * @param	string	$domain
 * @return	string	Masked domain
 */
function kbs_mask_domain( $domain = '' ) {

	if ( empty( $domain ) ) {
		return '';
	}

	$domain_parts = explode( '.', $domain );

	if ( count( $domain_parts ) === 2 ) {

		// We have a single entry tld like .org or .com
		$domain_parts[0] = kbs_mask_string( $domain_parts[0] );

	} else {

		$part_count     = count( $domain_parts );
		$possible_cctld = strlen( $domain_parts[ $part_count - 2 ] ) <= 3 ? true : false;

		$mask_parts = $possible_cctld ? array_slice( $domain_parts, 0, $part_count - 2 ) : array_slice( $domain_parts, 0, $part_count - 1 );

		$i = 0;
		while ( $i < count( $mask_parts ) ) {
			$domain_parts[ $i ] = kbs_mask_string( $domain_parts[ $i ]);
			$i++;
		}

	}

	return implode( '.', $domain_parts );
} // kbs_mask_domain

/**
 * Given an email address, mask the name and domain according to domain and string masking functions.
 *
 * Will result in an email address like a***n@e*****e.org for admin@example.org.
 *
 * @since	1.2.2
 * @param	string	$email_address
 * @return	string	Masked email address
 */
function kbs_pseudo_mask_email( $email_address ) {
	if ( ! is_email( $email_address ) ) {
		return $email_address;
	}

	$email_parts = explode( '@', $email_address );
	$name        = kbs_mask_string( $email_parts[0] );
	$domain      = kbs_mask_domain( $email_parts[1] );

	$email_address = $name . '@' . $domain;


	return $email_address;
} // kbs_pseudo_mask_email

/**
 * Log the privacy and terms timestamp for the last submitted ticket for a customer.
 *
 * Stores the timestamp of the last time the user clicked the 'submit ticket' button for the
 * Agree to Terms and/or Privacy Policy checkboxes during the submission process.
 *
 * @since	1.2.2
 * @param	$ticket_id		The ticket post ID
 * @param	$ticket_data	Array of ticket data
 * @return	void
 */
function kbs_log_terms_and_privacy_times( $ticket_id, $ticket_data ) {
	$ticket   = kbs_get_ticket( $ticket_id );
	$customer = new KBS_Customer( $ticket->customer_id );

	if ( empty( $customer->id ) ) {
		return;
	}

	if ( ! empty( $ticket_data['privacy_accepted'] ) ) {
		$customer->update_meta( 'agree_to_privacy_time', $ticket_data['privacy_accepted'] );
	}

	if ( ! empty( $ticket_data['terms_agreed'] ) ) {
		$customer->update_meta( 'agree_to_terms_time', $ticket_data['terms_agreed'] );
	}
} // kbs_log_terms_and_privacy_times
add_action( 'kbs_add_ticket', 'kbs_log_terms_and_privacy_times', 10, 2 );

/*
 * Returns an anonymized email address.
 *
 * While WP Core supports anonymizing email addresses with the wp_privacy_anonymize_data function,
 * it turns every email address into deleted@site.invalid, which does not work when some ticket/customer
 * records are still needed for legal and regulatory reasons.
 *
 * This function will anonymize the email with an MD5 that is salted and given a randomized uniqid prefixed
 * with the site URL in order to prevent connecting a single customer across multiple sites,
 * as well as the timestamp at the time of anonymization (so it trying the same email again will not be
 * repeatable and therefore connected), and return the email address as <hash>@site.invalid.
 *
 * @since	1.2.2
 * @param	string	$email_address	Email address to be anonymized
 * @return	string	Anonymized email address
 */
function kbs_anonymize_email( $email_address ) {

	if ( empty( $email_address ) ) {
		return $email_address;
	}

	$email_address    = strtolower( $email_address );
	$email_parts      = explode( '@', $email_address );
	$anonymized_email = wp_hash( uniqid( get_option( 'site_url' ), true ) . $email_parts[0] . current_time( 'timestamp' ), 'nonce' );


	return $anonymized_email . '@site.invalid';
} // kbs_anonymize_email

/**
 * Given a customer ID, anonymize the data related to that customer.
 *
 * Only the customer record is affected in this function. The data that is changed:
 * - The name is changed to 'Anonymized Customer'
 * - The email address is anonymized, but kept in a format that passes is_email checks
 * - The date created is set to the timestamp of 0 (January 1, 1970)
 * - Notes are fully cleared
 * - Any additional email addresses are removed
 *
 * Once completed, a note is left stating when the customer was anonymized.
 *
 * @since	1.2.2
 * @param	int		$customer_id
 * @return	array
 */
function _kbs_anonymize_customer( $customer_id = 0 ) {

	$customer = new KBS_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return array( 'success' => false, 'message' => sprintf( __( 'No customer with ID %d', 'kb-support' ), $customer_id ) );
	}

	/**
	 * Determines if this customer should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not anonymize a customer.
	 *
	 * @since	1.2.2
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type	bool	$should_anonymize	If the customer should be anonymized.
	 *     @type	string	$message			A message to display if the customer could not be anonymized.
	 * }
	 */
	$should_anonymize_customer = apply_filters( 'kbs_should_anonymize_customer', array( 'should_anonymize' => true, 'message' => '' ), $customer );

	if ( empty( $should_anonymize_customer['should_anonymize'] ) ) {
		return array( 'success' => false, 'message' => $should_anonymize_customer['message'] );
	}

	// Now we should look at tickets this customer has associated, and if there are any tickets that should not be modified,
	// do not modify the customer.
	$tickets = kbs_get_tickets( array(
		'customer' => $customer->id,
		'output'   => 'tickets',
		'number'   => -1
	) );

	foreach ( $tickets as $ticket ) {
		$action = _kbs_privacy_get_ticket_action( $ticket );
		if ( 'none' === $action ) {
			return array(
				'success' => false,
				'message' => sprintf( __( 'Customer could not be anonymized due to %s that could not be anonymized or deleted.', 'kb-support' ), kbs_get_ticket_label_plural( true ) )
			);
		}
	}

	// Loop through all their email addresses, and remove any additional email addresses.
	foreach ( $customer->emails as $email ) {
		$customer->remove_email( $email );
	}

	if ( $customer->user_id > 0 ) {
		delete_user_meta( $customer->user_id, '_kbs_user_address' );
	}

	$customer->update( array(
		'name'         => __( 'Anonymized Customer', 'kb-support' ),
		'email'        => kbs_anonymize_email( $customer->email ),
		'date_created' => date( 'Y-m-d H:i:s', 0 ),
		'notes'        => '',
		'user_id'      => 0
	) );

	/**
	 * Run further anonymization on a customer
	 *
	 * Developers and extensions can use the KBS_Customer object passed into the kbs_anonymize_customer action
	 * to complete further anonymization.
	 *
	 * @since	1.2.2
	 * @param	KBS_Customer	$customer	The KBS_Customer object that was found.
	 */
	do_action( 'kbs_anonymize_customer', $customer );

	$customer->add_note( __( 'Customer anonymized successfully', 'kb-support' ) );
	return array( 'success' => true, 'message' => sprintf( __( 'Customer ID %d successfully anonymized.', 'kb-support' ), $customer_id ) );

} // _kbs_anonymize_customer

/**
 * Given a ticket ID, anonymize the data related to that ticket.
 *
 * Only the ticket record is affected in this function. The data that is changed:
 * - First Name is made blank
 * - Last  Name is made blank
 * - All email addresses are converted to the anonymized email address on the customer
 * - The IP address is run to only be the /24 IP Address (ending in .0) so it cannot be traced back to a user
 *
 * @since	1.2.2
 * @param	int		$ticket_id
 * @return array
 */
function _kbs_anonymize_ticket( $ticket_id = 0 ) {

	$ticket = kbs_get_ticket( $ticket_id );
	if ( false === $ticket ) {
		return array(
			'success' => false,
			'message' => sprintf(
				__( 'No %s with ID %d.', 'kb-support' ),
				kbs_get_ticket_label_singular(),
				$ticket_id
			)
		);
	}

	/**
	 * Determines if this ticket should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not anonymize a ticket.
	 *
	 * @since	1.2.2
	 * @param	array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the ticket should be anonymized.
	 *     @type string $message          A message to display if the customer could not be anonymized.
	 * }
	 */
	$should_anonymize_ticket = apply_filters( 'kbs_should_anonymize_ticket', array( 'should_anonymize' => true, 'message' => '' ), $ticket );

	if ( empty( $should_anonymize_ticket['should_anonymize'] ) ) {
		return array( 'success' => false, 'message' => $should_anonymize_ticket['message'] );
	}

	$action = _kbs_privacy_get_ticket_action( $ticket );

	switch( $action ) {

		case 'none':
		default:
			$return = array(
				'success' => false,
				'message' => sprintf(
					__( '%s not modified, due to status: %s.', 'kb-support' ),
					kbs_get_ticket_label_singular(),
					$ticket->status
				)
			);
			break;

		case 'delete':
			wp_delete_post( $ticket_id, true );

			$return = array(
				'success' => true,
				'message' => sprintf(
					__( '%s %d with status %s deleted.', 'kb-support' ),
					kbs_get_ticket_label_singular(),
					$ticket->ID,
					$ticket->status
				)
			);
			break;

		case 'anonymize':
			$customer = new KBS_Customer( $ticket->customer_id );

			$ticket->ip    = wp_privacy_anonymize_ip( $ticket->ip );
			$ticket->email = $customer->email;

			$ticket->first_name = '';
			$ticket->last_name  = '';

			wp_update_post( array(
				'ID' => $ticket->ID,
				'post_title' => __( 'Anonymized Customer', 'kb-support' ),
				'post_name'  => sanitize_title( __( 'Anonymized Customer', 'kb-support' ) ),
			) );

			// Because we changed the post_name, WordPress sets a meta on the item for the `old slug`, we need to kill that.
			delete_post_meta( $ticket->ID, '_wp_old_slug' );

			/**
			 * Run further anonymization on a ticket
			 *
			 * Developers and extensions can use the KBS_Ticket object passed into the kbs_anonymize_ticket action
			 * to complete further anonymization.
			 *
			 * @since	1.2.2
			 *
			 * @param	KBS_Ticket		$ticket		The KBS_Ticket object that was found.
			 */
			do_action( 'kbs_anonymize_ticket', $ticket );

			$ticket->save();
			$return = array(
				'success' => true,
				'message' => sprintf(
					__( '%s ID %d successfully anonymized.', 'kb-support' ),
					kbs_get_ticket_label_singular(),
					$ticket_id
				)
			);
			break;
	}

	return $return;
} // _kbs_anonymize_ticket

/**
 * Given a KBS_Ticket, determine what action should be taken during the eraser processes.
 *
 * @since	1.2.2
 * @param	KBS_Ticket $ticket
 * @return	string
 */
function _kbs_privacy_get_ticket_action( KBS_Ticket $ticket ) {

	$action = kbs_get_option( 'ticket_privacy_action', false );

	// If the admin has not saved any special settings for the actions to be taken, use defaults.
	if ( empty( $action ) ) {

		switch ( $ticket->status ) {

            case 'open':
			case 'hold':
			case 'closed':
				$action = 'anonymize';
				break;

			case 'new':
			default:
				$action = 'none';
				break;

		}

	}

	/**
	 * Allow filtering of what type of action should be taken for a ticket.
	 *
	 * Developers and extensions can use this filter to modify how KB Support will treat an order
	 * that has been requested to be deleted or anonymized.
	 *
	 * @since	1.2.2
	 *
	 * @param	string		$action 	What action will be performed (none, delete, anonymize)
	 * @param	KBS_Ticket	$ticket		The KBS_Ticket object that has been requested to be anonymized or deleted.
	 */
	$action = apply_filters( 'kbs_privacy_ticket_status_action_' . $ticket->status, $action, $ticket );

	return $action;
} // _kbs_privacy_get_ticket_action

/**
 * Since our eraser callbacks need to look up a stored customer ID by hashed email address,
 * developers can use this to retrieve the customer ID associated with an email address that's being
 * requested to be deleted even after the customer has been anonymized.
 *
 * @since	1.2.2
 * @param	$email_address
 * @return	KBS_Ticket
 */
function _kbs_privacy_get_customer_id_for_email( $email_address ) {
	$customer_id = get_option( 'kbs_priv_' . md5( $email_address ), true );
	$customer    = new KBS_Customer( $customer_id );

	return $customer;
} // _kbs_privacy_get_customer_id_for_email

/**
 * Register any of our Privacy Data Exporters
 *
 * @since	1.2.2
 * @param	$exporters
 * @return	array
 */
function kbs_register_privacy_exporters( $exporters ) {

	$exporters[] = array(
		'exporter_friendly_name' => __( 'KBS Customer Record', 'kb-support' ),
		'callback'               => 'kbs_privacy_customer_record_exporter',
	);

	return $exporters;

} // kbs_register_privacy_exporters
add_filter( 'wp_privacy_personal_data_exporters', 'kbs_register_privacy_exporters' );

/**
 * Retrieves the Customer record for the Privacy Data Exporter
 *
 * @since	1.2.2
 * @param	string	$email_address
 * @param	int		$page
 * @return	array
 */
function kbs_privacy_customer_record_exporter( $email_address = '', $page = 1 ) {

	$customer = new KBS_Customer( $email_address );

	if ( empty( $customer->id ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_data = array(
		'group_id'    => 'kbs-customer-record',
		'group_label' => __( 'KBS Customer Record', 'kb-support' ),
		'item_id'     => "kbs-customer-record-{$customer->id}",
		'data'        => array(
			array(
				'name'  => __( 'Customer ID', 'kb-support' ),
				'value' => $customer->id
			),
			array(
				'name'  => __( 'Name', 'kb-support' ),
				'value' => $customer->name
			),
			array(
				'name'  => __( 'Primary Email', 'kb-support' ),
				'value' => $customer->email
			),
			array(
				'name'  => __( 'Primary Phone', 'kb-support' ),
				'value' => $customer->primary_phone
			),
			array(
				'name'  => __( 'Additional Phone', 'kb-support' ),
				'value' => $customer->additional_phone
			),
			array(
				'name'  => __( 'Website', 'kb-support' ),
				'value' => $customer->website
			),
			array(
				'name'  => __( 'Date Created', 'kb-support' ),
				'value' => $customer->date_created
			),
			array(
				'name'  => __( 'All Email Addresses', 'kb-support' ),
				'value' => implode( ', ', $customer->emails )
			)
		)
	);

	$agree_to_privacy_time = $customer->get_meta( 'agree_to_privacy_time', false );
	if ( ! empty( $agree_to_privacy_time ) ) {
		foreach ( $agree_to_privacy_time as $timestamp ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Privacy Policy', 'kb-support' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp )
			);
		}
	}

	$agree_to_terms_time = $customer->get_meta( 'agree_to_terms_time', false );
	if ( ! empty( $agree_to_terms_time ) ) {
		foreach ( $agree_to_terms_time as $timestamp ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Terms', 'kb-support' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp )
			);
		}
	}

	$export_data = apply_filters( 'kbs_privacy_customer_record', $export_data, $customer );

	return array( 'data' => array( $export_data ), 'done' => true );
} // kbs_privacy_customer_record_exporter

/**
 * This registers a single eraser _very_ early to avoid any other hook into the KBS data from running first.
 *
 * We are going to set an option of what customer we're currently deleting for what email address,
 * so that after the customer is anonymized we can still find them. Then we'll delete it.
 *
 * @since	1.2.2
 * @param	array	$erasers
 */
function kbs_register_privacy_eraser_customer_id_lookup( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => 'pre-eraser-customer-id-lookup',
		'callback'             => 'kbs_privacy_prefetch_customer_id',
	);

	return $erasers;
} // kbs_register_privacy_eraser_customer_id_lookup
add_filter( 'wp_privacy_personal_data_erasers', 'kbs_register_privacy_eraser_customer_id_lookup', 5, 1 );

/**
 * Lookup the customer ID for this email address so that we can use it later in the anonymization process.
 *
 * @since	1.2.2
 * @param	string		$email_address
 * @param	int			$page
 * @return array
 */
function kbs_privacy_prefetch_customer_id( $email_address, $page = 1 ) {
	$customer = new KBS_Customer( $email_address );
	update_option( 'kbs_priv_' . md5( $email_address ), $customer->id, false );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
} // kbs_privacy_prefetch_customer_id

/**
 * This registers a single eraser _very_ late to remove a customer ID that was found for the erasers.
 *
 * We are now assumed done with our exporters, so we can go ahead and delete the customer ID we
 * found for this eraser.
 *
 * @since	1.2.2
 * @param	array	$erasers
 */
function kbs_register_privacy_eraser_customer_id_removal( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => __( 'Possibly Delete Customer', 'kb-support' ),
		'callback'             => 'kbs_privacy_maybe_delete_customer_eraser',
	);

	$erasers[] = array(
		'eraser_friendly_name' => 'post-eraser-customer-id-lookup',
		'callback'             => 'kbs_privacy_remove_customer_id',
	);

	return $erasers;
} // kbs_register_privacy_eraser_customer_id_removal
add_filter( 'wp_privacy_personal_data_erasers', 'kbs_register_privacy_eraser_customer_id_removal', 9999, 1 );

/**
 * Delete the customer ID for this email address that was found in kbs_privacy_prefetch_customer_id()
 *
 * @since	1.2.2
 * @param	string		$email_address
 * @param	int			$page
 * @return	array
 */
function kbs_privacy_remove_customer_id( $email_address, $page = 1 ) {
	delete_option( 'kbs_priv_' . md5( $email_address ) );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
} // kbs_privacy_remove_customer_id

/**
 * If after the ticket anonymization/erasure methods have been run, and there are no longer tickets
 * for the requested customer, go ahead and delete the customer
 *
 * @since	1.2.2
 * @param	string	$email_address	The email address requesting anonymization/erasure
 * @param	int		$page			The page (not needed for this query)
 * @return	array
 */
function kbs_privacy_maybe_delete_customer_eraser( $email_address, $page = 1 ) {
	$customer = _kbs_privacy_get_customer_id_for_email( $email_address );

	if ( empty( $customer->id ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$tickets = kbs_get_tickets( array(
		'customer' => $customer->id,
		'output'   => 'tickets',
		'page'     => $page,
	) );

	if ( ! empty( $tickets ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(
				sprintf(
					__( 'Customer for %s not deleted, due to remaining %s.', 'kb-support' ),
					$email_address,
					kbs_get_ticket_label_plural( true )
				),
			),
			'done'           => true,
		);
	}

	if ( empty( $tickets ) ) {
		global $wpdb;

		$deleted_customer = KBS()->customers->delete( $customer->id );
		if ( $deleted_customer ) {
			$customer_meta_table = KBS()->customer_meta->table_name;
			$deleted_meta = $wpdb->query( "DELETE FROM {$customer_meta_table} WHERE customer_id = {$customer->id}" );

			return array(
				'items_removed'  => true,
				'items_retained' => false,
				'messages'       => array(
					sprintf( __( 'Customer for %s successfully deleted.', 'kb-support' ), $email_address ),
				),
				'done'           => true,
			);
		}
	}

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(
			sprintf( __( 'Customer for %s failed to be deleted.', 'kb-support' ), $email_address ),
		),
		'done'           => true,
	);
} // kbs_privacy_maybe_delete_customer_eraser

/**
 * Register eraser for KBS Data
 *
 * @since	1.2.2
 * @param	array	$erasers
 * @return	array
 */
function kbs_register_privacy_erasers( $erasers = array() ) {

	// The order of these matter, customer needs to be anonymized prior to the customer, so that the ticket can adopt
	// properties of the customer like email.
	$erasers[] = array(
		'eraser_friendly_name' => __( 'KBS Customer Record', 'kb-support' ),
		'callback'             => 'kbs_privacy_customer_anonymizer',
	);

	$erasers[] = array(
		'eraser_friendly_name' => sprintf(
			__( 'KBS %s Record', 'kb-support' ),
			kbs_get_ticket_label_singular()
		),
		'callback'             => 'kbs_privacy_ticket_eraser',
	);

	return $erasers;

} // kbs_register_privacy_erasers
add_filter( 'wp_privacy_personal_data_erasers', 'kbs_register_privacy_erasers', 11, 1 );

/**
 * Anonymize a customer record through the WP Core Privacy Data Eraser methods.
 *
 * @since	1.2.2
 * @param	string		$email_address
 * @param	int			$page
 * @return	array
 */
function kbs_privacy_customer_anonymizer( $email_address, $page = 1 ) {
	$customer = _kbs_privacy_get_customer_id_for_email( $email_address );

	$anonymized = _kbs_anonymize_customer( $customer->id );
	if ( empty( $anonymized['success'] ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $anonymized['message'] ),
			'done'           => true
		);
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array( sprintf( __( 'Customer for %s has been anonymized.', 'kb-support' ), $email_address ) ),
		'done'           => true
	);
} // kbs_privacy_customer_anonymizer

/**
 * Anonymize a ticket record through the WP Core Privacy Data Eraser methods.
 *
 * @since	1.2.2
 * @param	string	$email_address
 * @param	int		$page
 * @return array
 */
function kbs_privacy_ticket_eraser( $email_address, $page = 1 ) {
	$customer = _kbs_privacy_get_customer_id_for_email( $email_address );

	$tickets = kbs_get_tickets( array(
		'customer' => $customer->id,
		'output'   => 'tickets',
		'page'     => $page
	) );

	if ( empty( $tickets ) ) {

		if ( 1 === $page )	{
			$message = sprintf(
				__( 'No %s found for %s.', 'kb-support' ),
				kbs_get_ticket_label_singular( true ),
				$email_address
			);
		} else	{
			$message = sprintf(
				__( 'All eligible %s anonymized or deleted for %s.', 'kb-support' ), 
				kbs_get_ticket_label_plural( true ),
				$email_address
			);
		}

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $message ),
			'done'           => true
		);
	}

	$items_removed  = null;
	$items_retained = null;
	$messages       = array();
	foreach ( $tickets as $ticket ) {
		$result = _kbs_anonymize_ticket( $ticket->ID );

		if ( ! is_null( $items_removed ) && $result['success'] ) {
			$items_removed = true;
		}

		if ( ! is_null( $items_removed ) && ! $result['success'] ) {
			$items_retained = true;
		}

		$messages[] = $result['message'];
	}

	return array(
		'items_removed'  => ! is_null( $items_removed )  ? $items_removed  : false,
		'items_retained' => ! is_null( $items_retained ) ? $items_retained : false,
		'messages'       => $messages,
		'done'           => false
	);
} // kbs_privacy_ticket_eraser
