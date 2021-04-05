<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Tickets
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Ticket contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_ticket_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'kbs_ticket' )	{
		return;
	}

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();

	$screen->set_help_sidebar( kbs_get_contextual_help_sidebar_text() );

    do_action( 'kbs_ticket_before_general_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-ticket-general',
		'title'   => __( 'General', 'kb-support' ),
		'content' => apply_filters( 'kbs_ticket_general_contextual_help',
			'<p>' . sprintf( 
				__( '<strong>Title</strong> - The %1$s title will be seen on the admin screen as well as by customers on your website and via email when the <code>{ticket_title}</code> content tag is used.', 'kb-support' ),
				strtolower( $ticket_singular )
			) . '</p>' .
			'<p>' . sprintf( 
				__( '<strong>Content</strong> - This should be a full description of the issue that is being reported. If you are viewing an existing %1$s the data cannot be edited.', 'kb-support' ),
				strtolower( $ticket_singular )
			) . '</p>' .
			'<p>' . sprintf( 
				__( '<strong>Catgories & Tags</strong> - Help you to group %1$s for reference and reporting.', 'kb-support' ), strtolower( $ticket_plural ) ) .
			'</p>'
        )
	) );

    do_action( 'kbs_ticket_before_create_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-ticket-create',
		'title'   => sprintf( __( 'Create %s', 'kb-support' ), $ticket_singular ),
		'content' => apply_filters( 'kbs_ticket_create_contextual_help',
			'<p>' . sprintf( 
				__( '<strong>Status</strong> - Set or change the status of the %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
			) . '</p>' .
			'<p>' . sprintf( 
				__( '<strong>Customer</strong> - Select the customer to whom the %1$s belongs.', 'kb-support' ),
				strtolower( $ticket_singular )
			) . '</p>' .
			'<p>' . sprintf( 
				__( '<strong>Agent</strong> - Select the agent who is assigned to work on the %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
			) . '</p>'
        )
	) );

    do_action( 'kbs_ticket_before_reply_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-ticket-reply',
		'title'   => sprintf( __( 'Reply to %s', 'kb-support' ), $ticket_singular ),
		'content' => apply_filters( 'kbs_ticket_reply_contextual_help',
			'<p>' . sprintf( 
				__( 'Agents can reply to a %1$s by using the texarea provided. Previous replies are shown above the Add a New Reply textarea and can be expanded by clicking on them. Each reply contains a link which enables you to create a %2$s.', 'kb-support' ),
				strtolower( $ticket_singular ),
				$article_singular
			) . '</p>' .
			'<p>' . sprintf( 
				__( 'When the reply is ready, click <strong>Reply</strong> to submit and send to the customer, or <strong>Reply and Close</strong> to submit and close the %1$s.', 'kb-support' ),
				$ticket_singular
			) . '</p>'
        )
	) );

    do_action( 'kbs_ticket_before_notes_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-ticket-notes',
		'title'   => __( 'Private Notes', 'kb-support' ),
		'content' => apply_filters( 'kbs_ticket_notes_contextual_help',
			'<p>' . __( 'Agents can add private notes to exchange information with other Support Workers. Customers will never see this information.', 'kb-support' ) . '</p>'
        )
	) );

	do_action( 'kbs_ticket_contextual_help' );

} // kbs_ticket_contextual_help
add_action( 'load-post.php', 'kbs_ticket_contextual_help' );
add_action( 'load-post-new.php', 'kbs_ticket_contextual_help' );
