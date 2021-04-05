<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Companies
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KB Company contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_company_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'kbs_company' )	{
		return;
	}

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();

	$screen->set_help_sidebar( kbs_get_contextual_help_sidebar_text() );

    do_action( 'kbs_company_before_general_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-company-general',
		'title'   => __( 'Manage Company Profiles', 'kb-support' ),
		'content' => apply_filters( 'kbs_company_general_contextual_help',
			'<p>' . sprintf( 
				__( 'Creating companies enables you to identify all support %s that have been created for a single business customer.', 'kb-support' ),
				strtolower( $ticket_plural )
			) . '</p>' .
			'<p>' . sprintf(
				__( 'Once you have created company profiles, add your <a href="%1$s">customers</a> to the company from the <a href="%1$s">customers page</a>.', 'kb-support' ),
				admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers' )
			) . '</p>' .
			'<ul>' .
                '<li>' .
					__( "<strong>Customer</strong> - Select an existing customer to become the primary contact for the company. Upon selection the other fields may be populated with the customer's information.", 'kb-support' ) . '<br>' .
                    __( 'When creating a new company, all customers are displayed with their company name. For existing companies, only customers who have been associated with the company are available for selection.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Contact Name</strong> - This should be the name of the primay contact person within the company.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Email Address</strong> - The contact email address of the company.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Phone Number</strong> - The phone number of the comany.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Website</strong> - The website for the company', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Company Logo</strong> - Optionally upload the company logo.', 'kb-support' ) .
				'</li>' .
			'</ul>' .
            '<p>' .
                sprintf( __( 'For existing companies, %s that have been recently created are displayed.', 'kb-support' ), strtolower( $ticket_plural ) ) .
            '</p>'
        )
	) );

	do_action( 'kbs_company_contextual_help' );

} // kbs_company_contextual_help
add_action( 'load-post.php', 'kbs_company_contextual_help' );
add_action( 'edit-post.php', 'kbs_company_contextual_help' );
