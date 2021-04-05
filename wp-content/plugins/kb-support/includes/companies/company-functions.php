<?php
/**
 * Company Functions
 *
 * Functions related to companies
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
 * Get Companies
 *
 * Retrieve companies from the database.
 *
 * This is a simple wrapper for KBS_Companies_Query.
 *
 * @since	1.0
 * @param	arr		$args		Arguments passed to get companies
 * @return	obj		$companies	Companies retrieved from the database
 */
function kbs_get_companies( $args = array() ) {
	// Fallback to post objects to ensure backwards compatibility
	if ( ! isset( $args['output'] ) ) {
		$args['output'] = 'posts';
	}

	$args      = apply_filters( 'kbs_get_companies_args', $args );
	$companies = new KBS_Companies_Query( $args );

	return $companies->get_companies();
} // kbs_get_companies

/**
 * Sanitize the company id.
 *
 * @since	1.0
 * @param	int		$value	The posted value
 * @return	int		The santized value
 */
function kbs_sanitize_company_id( $value = 0 )	{
	$value = absint( $value );

	if ( 'kbs_company' != get_post_type( $value ) )	{
		$value = 0;
	}

	return $value;
} // kbs_sanitize_company_id

/**
 * Whether or not any companies exist.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_has_companies()	{
	$companies = get_posts( array(
		'post_type'      => 'kbs_company',
		'post_status'    => 'publish',
		'posts_per_page' => 1
	) );

	if ( ! empty( $companies ) )	{
		return true;
	}

	return false;
} // kbs_has_companies

/**
 * Counts the total number of companies.
 *
 * @since	1.0
 * @return	int		The total number of companies.
 */
function kbs_count_total_companies() {
	return wp_count_posts( 'kbs_company' )->publish;
} // kbs_count_total_companies

/**
 * Retrieve the company ticket count.
 *
 * @since	1.0
 * @param	obj|int		$company	Company WP_Post object or ID
 * @return	int			Count of tickets
 */
function kbs_count_company_tickets( $company )	{
	if ( ! is_numeric( $company ) )	{
		$company = $company->ID;
	}

	$tickets = kbs_count_tickets( array( 'company' => $company ) );
	$count   = 0;

	if ( ! empty( $tickets ) )	{
		foreach( $tickets as $status )	{
			if ( ! empty( $status ) )	{
				$count += $status;
			}
		}
	}

	return $count;
} // kbs_count_company_tickets

/**
 * Counts the total number of customers within a company.
 *
 * @since	1.0
 * @param	int		$company_id		The company ID
 * @return	int		The total number of customers in the company.
 */
function kbs_count_customers_in_company( $company_id = 0 ) {
	global $wpdb;

	if ( empty( $company_id ) )	{
		return false;
	}

	return KBS()->customers->count( array( 'company_id' => $company_id ) );
} // kbs_count_customers_in_company

/**
 * Whether or not to copy the primary company contact into customer emails.
 *
 * @since   1.2
 * @return  bool
 */
function kbs_email_cc_company_contact() {
    $cc = kbs_get_option( 'copy_company_contact', false );
    $cc = apply_filters( 'kbs_email_cc_company_contact', $cc );

    return $cc;
} // kbs_email_cc_company_contact

/**
 * Filters email headers if we CC in the primary company contact.
 *
 * @since   1.2
 * @param   string  $headers        Email headers
 * @param   int     $ticket_id      Ticket ID
 * @param   array   $ticket_data    Array of ticket meta data
 * @return  string
 */
function kbs_maybe_cc_company_contact( $headers, $ticket_id, $ticket_data ) {
    $cc = kbs_email_cc_company_contact();
    $cc = apply_filters( 'kbs_maybe_cc_company_contact', $cc, $ticket_id, $ticket_data );

    if ( $cc )  {
        $ticket = new KBS_Ticket( $ticket_id );

        if ( ! empty( $ticket->company_id ) )   {
            $email = is_email( kbs_get_company_email( $ticket->company_id ) );

            if ( $email && $email != $ticket->email )   {
                $headers[] = 'Cc: ' . $email;
            }
        }
    }

    return $headers;
} // kbs_maybe_cc_company_contact
add_filter( 'kbs_ticket_headers', 'kbs_maybe_cc_company_contact', 10, 3 );
add_filter( 'kbs_ticket_reply_headers', 'kbs_maybe_cc_company_contact', 10, 3 );
add_filter( 'kbs_ticket_closed_headers', 'kbs_maybe_cc_company_contact', 10, 3 );

/**
 * Retrieve company name.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Company name or false if not found
 */
function kbs_get_company_name( $company = 0 )	{
	if ( empty( $company ) || 'kbs_company' != get_post_type( $company ) )	{
		return false;
	}
	return get_the_title( $company );
} // kbs_get_company_name

/**
 * Retrieve company contact name.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Contact name or false if not found
 */
function kbs_get_company_contact( $company )	{
	if ( is_numeric( $company ) )	{
		$company = new KBS_Company( $company );
	}
	
	return $company->contact;
} // kbs_get_company_contact

/**
 * Retrieve company email address.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Company email address or false if not found
 */
function kbs_get_company_email( $company )	{
	if ( is_numeric( $company ) )	{
		$company = new KBS_Company( $company );
	}
	
	return $company->email;
} // kbs_get_company_email

/**
 * Retrieve company phone number.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Company phone number or false if not found
 */
function kbs_get_company_phone( $company )	{
	if ( is_numeric( $company ) )	{
		$company = new KBS_Company( $company );
	}
	
	return $company->phone;
} // kbs_get_company_phone

/**
 * Retrieve company website address.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Company website address or false if not found
 */
function kbs_get_company_website( $company )	{
	if ( is_numeric( $company ) )	{
		$company = new KBS_Company( $company );
	}
	
	return $company->website;
} // kbs_get_company_website

/**
 * Retrieve company logo URL.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Company logo URL or false if not found
 */
function kbs_get_company_logo( $company )	{
	if ( is_numeric( $company ) )	{
		$company = new KBS_Company( $company );
	}

	if ( ! empty( $company ) )	{
		return $company->logo;
	}
} // kbs_get_company_logo
