<?php
/**
 * Email Template
 *
 * @package     KBS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the current email template.
 *
 * This is simply a wrapper to KBS_Email_Templates->get_template()
 *
 * @since	1.1.10
 * return	str
 */
function kbs_get_email_template()	{
	$template = new KBS_Emails;
	return $template->get_template();
} // kbs_get_email_template

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * This is simply a wrapper to KBS_Email_Templates->get_templates()
 *
 * @since	1.0
 * @return	arr		All the registered email templates
 */
function kbs_get_email_templates()   {
	$templates = new KBS_Emails;
	return $templates->get_templates();
} // kbs_get_email_templates

/**
 * Email Template Tags
 *
 * @since	1.0
 *
 * @param	str		$message		Message with the template tags
 * @param	int		$ticket_id		Ticket ID
 * @return	str		$message		Fully formatted message
 */
function kbs_email_template_tags( $message, $ticket_id )     {
	return kbs_do_email_tags( $message, $ticket_id );
} // kbs_email_template_tags

/**
 * Email Preview Template Tags
 *
 * @since	1.0
 * @param	str		$message	Email message with template tags
 * @return	str		$message	Fully formatted message
 */
function kbs_email_preview_template_tags( $message )     {

	$user      = wp_get_current_user();
	$ticket_id = kbs_get_ticket_number( rand( 1, 100 ) );

	$search  = array(
		'{name}', '{fullname}', '{username}', '{user_email}', '{sitename}',
		'{date}', '{ticket_id}', '{ticket_url}', '{ticket_content}'
	);
	$replace = array(
		$user->first_name,
		$user->display_name,
		$user->user_login,
		$user->user_email,
		get_bloginfo( 'name' ),
		get_date_from_gmt( current_time( 'timestamp' ), get_option( 'date_format' ) ),
		get_date_from_gmt( current_time( 'timestamp' ), get_option( 'time_format' ) ),
		kbs_get_ticket_number( $ticket_id ),
		kbs_get_ticket_url( $ticket_id ),
		sprintf( __( 'This is where your %s content would be displayed.', 'kb-support' ), kbs_get_ticket_label_plural( true ) )
	);

	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
 	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{user_email}', $user->user_email, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{date}', get_date_from_gmt( current_time( 'timestamp' ), get_option( 'date_format' ) ), $message );
	$message = str_replace( '{ticket_id}', $ticket_id, $message );
	$message = str_replace( '{ticket_details}', $ticket_id, $message );

	return $message;
} // kbs_email_preview_template_tags

/**
 * Email Template Preview
 *
 * @since	1.0
 * @param
 */
function kbs_email_template_preview() {
	if( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}

	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'kbs_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php printf( __( '%s Logged Preview', 'kb-support' ), kbs_get_ticket_label_singular() ); ?> "><?php printf( __( 'Preview %s Logged', 'kb-support' ), kbs_get_ticket_label_singular() ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array( 'kbs_action' => 'send_test_email' ) ), 'kbs-test-email' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'kb-support' ); ?></a>
	<?php
	echo ob_get_clean();
} // kbs_email_template_preview
add_action( 'kbs_email_settings', 'kbs_email_template_preview' );

/**
 * Displays the email preview
 *
 * @since	1.0
 * @return	void
 */
function kbs_display_email_template_preview() {

	if( empty( $_GET['kbs_action'] ) ) {
		return;
	}

	if( 'preview_email' !== $_GET['kbs_action'] ) {
		return;
	}

	if( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}

	KBS()->emails->heading = sprintf( __( '%s Received', 'kb-support' ), kbs_get_ticket_label_singular() );

	echo KBS()->emails->build_email( kbs_email_preview_template_tags( kbs_get_ticket_logged_email_body_content( 0, array() ) ) );

	exit;

} // kbs_display_email_template_preview
add_action( 'template_redirect', 'kbs_display_email_template_preview' );

/**
 * Ticket Logged Email Template Body.
 *
 * This is the default content sent to the customer when a new ticket is logged.
 *
 * @since	1.0
 * @param	int 	$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_ticket_logged_email_body_content( $ticket_id = 0, $ticket_data = array() ) 	{

	$logged_email_body = __( 'Dear', 'kb-support' ) . " {name},\n\n";
	$logged_email_body .= sprintf( __( 'Thank you for logging your support %s.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ) . "\n\n";
	$logged_email_body .= __( "We've received the details and will be in touch as necessary shortly.", 'kb-support' ) . "\n\n";
	$logged_email_body .= __( 'Regards', 'kb-support' ) . "\n\n";
	$logged_email_body .= '{sitename}' . "\n\n";
	$logged_email_body .= '<hr />';
	
	$logged_email_body .= '<h3>' . sprintf( __( 'Your Ticket Details', 'kb-support' ), kbs_get_ticket_label_singular() ) . ' - #{ticket_id}</h3>' . "\n";
	$logged_email_body .= '<strong>{ticket_title}</strong>' . "\n\n";
	$logged_email_body .= '{ticket_content}' . "\n\n";
	$logged_email_body .= '<a href="{ticket_url_path}">' . sprintf( __( 'View %s', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</a>' . "\n\n";

	$email = kbs_get_option( 'ticket_content', false );
	$email = $email ? stripslashes( $email ) : $logged_email_body;

	$email_body = apply_filters( 'kbs_ticket_logged_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'kbs_ticket_logged_email_content_' . KBS()->emails->get_template(), $email_body, $ticket_id, $ticket_data );

	return apply_filters( 'kbs_ticket_logged_email_content', $email_body, $ticket_id, $ticket_data );

} // kbs_get_ticket_logged_email_body_content

/**
 * Ticket Reply Email Template Body
 *
 * This is the default content sent to the customer when a reply is added to a ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_ticket_reply_email_body_content( $ticket_id = 0, $ticket_data = array() )   {

	$logged_email_body = __( 'Dear', 'kb-support' ) . " {name},\n\n";
	$logged_email_body .= sprintf( __( 'Your support %s # {ticket_id} has received a reply.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ) . "\n\n";
	$logged_email_body .= '<a href="{ticket_url_path}">' . sprintf( __( 'View %s', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</a>' . "\n\n";
	$logged_email_body .= "Regards\n\n";
	$logged_email_body .= "{sitename}";

	$email = kbs_get_option( 'ticket_reply_content', false );
	$email = $email ? stripslashes( $email ) : $logged_email_body;

	$email_body = apply_filters( 'kbs_ticket_reply_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'kbs_ticket_reply_email_content_' . KBS()->emails->get_template(), $email_body, $ticket_id, $ticket_data );

	return apply_filters( 'kbs_ticket_reply_email_content', $email_body, $ticket_id, $ticket_data );

} // kbs_get_ticket_reply_email_body_content

/**
 * Ticket Closed Email Template Body
 *
 * This is the default content sent to the customer when a ticket is closed.
 *
 * @since	1.0
 * @param	int		$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_ticket_closed_email_body_content( $ticket_id = 0, $ticket_data = array() )  {

	$logged_email_body = __( 'Dear', 'kb-support' ) . " {name},\n\n";
	$logged_email_body .= sprintf( __( 'Your support %s # {ticket_id} is now closed.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ) . "\n\n";
	$logged_email_body .= '<a href="{ticket_url_path}">' . sprintf( __( 'View %s', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</a>' . "\n\n";
	$logged_email_body .= "Regards\n\n";
	$logged_email_body .= "{sitename}";

	$email = kbs_get_option( 'ticket_closed_content', false );
	$email = $email ? stripslashes( $email ) : $logged_email_body;

	$email_body = apply_filters( 'kbs_ticket_closed_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'kbs_ticket_closed_email_content_' . KBS()->emails->get_template(), $email_body, $ticket_id, $ticket_data );

	return apply_filters( 'kbs_ticket_closed_email_content', $email_body, $ticket_id, $ticket_data );

} // kbs_get_ticket_closed_email_body_content

/**
 * Ticket Notification Template Body
 *
 * This is the default notification content sent to the admin when a new ticket is logged.
 *
 * @since	1.0
 * @param	int		$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_ticket_notification_email_body_content( $ticket_id = 0, $ticket_data = array() )    {

	$single = kbs_get_ticket_label_singular();
	$plural = kbs_get_ticket_label_plural();
	$name   = '';
	$email  = kbs_get_ticket_user_email( $ticket_id );

	$default_email_body = __( 'Hey there!', 'kb-support' ) . "\n\n";
	$default_email_body .= sprintf( __( 'A new %s has been logged at', 'kb-support' ), strtolower( $single ) ) . " {sitename}.\n\n";
	$default_email_body .= "<strong>{ticket_title} - #{ticket_id}</strong>\n\n";
	$default_email_body .= '<a href="{ticket_admin_url_path}">' . sprintf( __( 'View %s', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</a>' . "\n\n";
	$default_email_body .= __( 'Regards', 'kb-support' ) . "\n\n";
	$default_email_body .= '{sitename}';

	$email = kbs_get_option( 'ticket_notification', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = kbs_email_template_tags( $email, $ticket_id );

	$email_body = apply_filters( 'kbs_ticket_notification_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'kbs_ticket_notification_email', $email_body, $ticket_id, $ticket_data );

} // kbs_get_ticket_notification_email_body_content

/**
 * Reply Notification Template Body
 *
 * This is the default notification content sent to the admin when a customer replies to a ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_reply_notification_email_body_content( $ticket_id = 0, $ticket_data = array() )     {

	$single = kbs_get_ticket_label_singular();
	$plural = kbs_get_ticket_label_plural();
	$name   = '';
	$email  = kbs_get_ticket_user_email( $ticket_id );

	$default_email_body = __( 'Hey there!', 'kb-support' ) . "\n\n";
	$default_email_body .= sprintf( __( 'A new reply has been received for a support %s.', 'kb-support' ), strtolower( $single ) ) . "\n\n";
	$default_email_body .= "<strong>{ticket_title} - #{ticket_id}</strong>\n\n";
	$default_email_body .= '<a href="{ticket_admin_url_path}">' . sprintf( __( 'View %s', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</a>' . "\n\n";
	$default_email_body .= __( 'Regards', 'kb-support' ) . "\n\n";
	$default_email_body .= '{sitename}';

	$email = kbs_get_option( 'reply_notification', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = kbs_email_template_tags( $email, $ticket_id );
	$email_body = apply_filters( 'kbs_ticket_reply_notification_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'kbs_ticket_reply_notification_email', $email_body, $ticket_id, $ticket_data );

} // kbs_get_reply_notification_email_body_content

/**
 * Agent Assignment Notification Template Body
 *
 * This is the default notification content sent to agents when a ticket is assigned to them.
 *
 * @since	1.1
 * @param	int		$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_agent_assigned_notification_email_body_content( $ticket_id = 0, $ticket_data = array() )    {

    $single = kbs_get_ticket_label_singular();

	$default_email_body = __( 'Hey there!', 'kb-support' ) . "\n\n";
	$default_email_body .= sprintf( __( 'A %s has been assigned to you at {sitename}.', 'kb-support' ), strtolower( $single ) ) . "\n\n";
	$default_email_body .= "<strong>{ticket_title} - #{ticket_id}</strong>\n\n";
	$default_email_body .= sprintf( __( 'Please login to view and update the %s.', 'kb-support' ), strtolower( $single ) ) . "\n\n";
    $default_email_body .= '<a href="{ticket_admin_url_path}">' . sprintf( __( 'View %s', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</a>' . "\n\n";
	$default_email_body .= __( 'Regards', 'kb-support' ) . "\n\n";
	$default_email_body .= '{sitename}';

	$email = kbs_get_option( 'agent_assign_notification', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = kbs_email_template_tags( $email, $ticket_id );
	$email_body = apply_filters( 'kbs_agent_assigned_notification_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'kbs_agent_assigned_notification_email', $email_body, $ticket_id, $ticket_data );

} // kbs_get_agent_assigned_notification_email_body_content
