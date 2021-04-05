<?php
/**
 * KB Support API for creating Email template tags
 *
 * Taken from Easy Digital Downloads
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {ticket_content}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: kbs_do_email_tags( $content, $ticket_id );
 *
 * To add tags, use: kbs_add_email_tag( $tag, $description, $func ). Be sure to wrap kbs_add_email_tag()
 * in a function hooked to the 'kbs_add_email_tags' action
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

class KBS_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since	1.0
	 */
	private $tags;

	/**
	 * Ticket ID
	 *
	 * @since	1.0
	 */
	private $ticket_id;

	/**
	 * Add an email tag
	 *
	 * @since	1.0
	 *
	 * @param	str			$tag	Email tag to be replaces in email
	 * @param	callable	$func	Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[ $tag ] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	} // add

	/**
	 * Remove an email tag
	 *
	 * @since	1.0
	 *
	 * @param	str		$tag	Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[ $tag ] );
	} // remove

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since	1.0
	 *
	 * @param	str		$tag	Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	} // email_tag_exists

	/**
	 * Returns a list of all email tags
	 *
	 * @since	1.0
	 *
	 * @return	arr
	 */
	public function get_tags() {
		return $this->tags;
	} // get_tags

	/**
	 * Search content for email tags and filter email tags through their hooks.
	 *
	 * @param	str		$content	Content to search for email tags
	 * @param	int		$ticket_id	The ticket id
	 *
	 * @since	1.0
	 *
	 * @return	str		Content with email tags filtered out.
	 */
	public function do_tags( $content, $ticket_id ) {

		// Check if there is at least one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->ticket_id = $ticket_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->ticket_id = null;

		return $new_content;
	} // do_tags

	/**
	 * Do a specific tag, this function should not be used. Please use kbs_do_email_tags instead.
	 *
	 * @since	1.0
	 *
	 * @param	str		$m	Message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[ $tag ]['func'], $this->ticket_id, $tag );
	} // do_tag

} // KBS_Email_Template_Tags

/**
 * Add an email tag
 *
 * @since	1.0
 *
 * @param	str			$tag	Email tag to be replace in email
 * @param	callable	$func	Hook to run when email tag is found
 */
function kbs_add_email_tag( $tag, $description, $func ) {
	KBS()->email_tags->add( $tag, $description, $func );
} // kbs_add_email_tag

/**
 * Remove an email tag
 *
 * @since	1.0
 *
 * @param	str		$tag	Email tag to remove hook from
 */
function kbs_remove_email_tag( $tag ) {
	KBS()->email_tags->remove( $tag );
} // kbs_remove_email_tag

/**
 * Check if $tag is a registered email tag
 *
 * @since	1.0
 *
 * @param	str		$tag	Email tag that will be searched
 *
 * @return	bool
 */
function kbs_email_tag_exists( $tag ) {
	return KBS()->email_tags->email_tag_exists( $tag );
} // kbs_email_tag_exists

/**
 * Get all email tags
 *
 * @since	1.0
 *
 * @return	arr
 */
function kbs_get_email_tags() {
	return KBS()->email_tags->get_tags();
} // kbs_get_email_tags

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since	1.0
 *
 * @return	str
 */
function kbs_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = kbs_get_email_tags();

    if ( empty( $email_tags ) || ! is_array( $email_tags ) )    {
        $email_tags = array();
    }

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	return $list;
} // kbs_get_emails_tags_list

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param	str		$content	Content to search for email tags
 * @param	int		$ticket_id	The ticket id
 *
 * @since	1.0
 *
 * @return	str		Content with email tags filtered out.
 */
function kbs_do_email_tags( $content, $ticket_id ) {

	// Replace all tags
	$content = KBS()->email_tags->do_tags( $content, $ticket_id );

	// Return content
	return $content;
} // kbs_do_email_tags

/**
 * Load email tags
 *
 * @since	1.0
 */
function kbs_load_email_tags() {
	do_action( 'kbs_add_email_tags' );
} // kbs_load_email_tags
add_action( 'init', 'kbs_load_email_tags', -999 );

/**
 * Add default KBS email template tags
 *
 * @since	1.0
 */
function kbs_setup_email_tags() {

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'name',
			'description' => __( 'The customers first name', 'kb-support' ),
			'function'    => 'kbs_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( 'The customers full name, first and last', 'kb-support' ),
			'function'    => 'kbs_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( 'The customers user name on the site, if they registered an account', 'kb-support' ),
			'function'    => 'kbs_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( 'The customers email address', 'kb-support' ),
			'function'    => 'kbs_email_tag_user_email'
		),
		array(
			'tag'         => 'company',
			'description' => sprintf(
				__( 'The name of the company to which the %s is associated', 'kb-support' ),
				strtolower( $ticket_singular )
			),
			'function'    => 'kbs_email_tag_company'
		),
		array(
			'tag'         => 'company_contact',
			'description' => __( 'The contact name of the company', 'kb-support' ),
			'function'    => 'kbs_email_tag_company_contact'
		),
		array(
			'tag'         => 'company_email',
			'description' => __( 'The email address of the company', 'kb-support' ),
			'function'    => 'kbs_email_tag_company_email'
		),
		array(
			'tag'         => 'company_phone',
			'description' => __( 'The phone number of the company', 'kb-support' ),
			'function'    => 'kbs_email_tag_company_phone'
		),
		array(
			'tag'         => 'company_website',
			'description' => __( 'The website URL of the company', 'kb-support' ),
			'function'    => 'kbs_email_tag_company_website'
		),
		array(
			'tag'         => 'company_logo',
			'description' => __( 'Inserts the logo of the company', 'kb-support' ),
			'function'    => 'kbs_email_tag_company_logo'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'kb-support' ),
			'function'    => 'kbs_email_tag_sitename'
		),
		array(
			'tag'         => 'date',
			'description' => sprintf( __( 'The date of the %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_date'
		),
		array(
			'tag'         => 'time',
			'description' => sprintf( __( 'The time of the %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_time'
		),
		array(
			'tag'         => 'ticket_id',
			'description' => sprintf( __( 'The unique ID number for this %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_id'
		),
        array(
			'tag'         => 'ticket_status',
			'description' => sprintf( __( 'The current status of the %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_status'
		),
		array(
			'tag'         => 'ticket_title',
			'description' => sprintf( __( 'Title of the %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_title'
		),
		array(
			'tag'         => 'ticket_content',
			'description' => sprintf( __( 'Content of the %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_content'
		),
		array(
			'tag'         => 'ticket_files',
			'description' => sprintf( __( 'Attach or list files attached to the %s', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_files'
		),
        array(
			'tag'         => 'department',
			'description' => sprintf( __( 'Inserts the name of the department who are handling the %s or an empty string if no department is assigned', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_department'
		),
		array(
			'tag'         => 'reply_author',
			'description' => sprintf( __( 'The name or email address (if no name is in the database) of the author of the the most recent %s reply', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_reply_author'
		),
		array(
			'tag'         => 'reply_date',
			'description' => sprintf( __( 'The date of the most recent %s reply', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_reply_date'
		),
		array(
			'tag'         => 'reply_time',
			'description' => sprintf( __( 'The time of the most recent %s reply', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_reply_time'
		),
		array(
			'tag'         => 'reply_content',
			'description' => __( 'Content of the most recent reply', 'kb-support' ),
			'function'    => 'kbs_email_tag_reply_content'
		),
        array(
			'tag'         => 'reply_files',
			'description' => sprintf( __( 'Attach or list files attached to the %s reply', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_reply_files'
		),
		array(
			'tag'         => 'ticket_url',
			'description' => sprintf( __( 'Adds a URL so customers can view their %s directly on your website.', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_url'
		),
		array(
			'tag'         => 'ticket_url_path',
			'description' => sprintf( __( 'Adds a non-linked URL so customers can view their %s directly on your website.', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_url_path'
		),
		array(
			'tag'         => 'ticket_admin_url',
			'description' => sprintf( __( 'Adds a URL so admins can access a %s directly.', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_admin_url'
		),
		array(
			'tag'         => 'ticket_admin_url_path',
			'description' => sprintf( __( 'Adds a non-linked URL so admins can access a %s directly.', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_admin_url_path'
		),
		array(
			'tag'         => 'close_ticket_url',
			'description' => sprintf( __( 'Adds a URL link that customers can click to close a ticket.', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_close_ticket_url'
		),
		array(
			'tag'         => 'close_ticket_url_path',
			'description' => sprintf( __( 'Adds a non-linked URL link that customers can click to close a ticket.', 'kb-support' ), strtolower( $ticket_singular ) ),
			'function'    => 'kbs_email_tag_ticket_close_ticket_url_path'
		)
	);

	// Apply kbs_email_tags filter
	$email_tags = apply_filters( 'kbs_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		kbs_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

} // kbs_setup_email_tags
add_action( 'kbs_add_email_tags', 'kbs_setup_email_tags' );

/**
 * Email template tag: name
 * The customers first name
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		name
 */
function kbs_email_tag_first_name( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	$user_info = $ticket->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name   = kbs_get_email_names( $user_info );
	return $email_name['name'];
} // kbs_email_tag_first_name

/**
 * Email template tag: fullname
 * The customers full name, first and last
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		fullname
 */
function kbs_email_tag_fullname( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	$user_info = $ticket->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name   = kbs_get_email_names( $user_info );
	return $email_name['fullname'];
} // kbs_email_tag_fullname

/**
 * Email template tag: username
 * The customers user name on the site, if they registered an account
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		username
 */
function kbs_email_tag_username( $ticket_id  ) {
	$ticket = new KBS_Ticket( $ticket_id );

	$user_info = $ticket->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name   = kbs_get_email_names( $user_info );
	return $email_name['username'];
} // kbs_email_tag_username

/**
 * Email template tag: user_email
 * The customers email address
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		user_email
 */
function kbs_email_tag_user_email( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->email;
} // kbs_email_tag_user_email

/**
 * Email template tag: company
 * The company associated with the ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		company
 */
function kbs_email_tag_company( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return kbs_get_company_name( $ticket->company_id );
} // kbs_email_tag_company

/**
 * Email template tag: company_contact
 * The company contact name
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		company_contact
 */
function kbs_email_tag_company_contact( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return kbs_get_company_contact( $ticket->company_id );
} // kbs_email_tag_company_contact

/**
 * Email template tag: company_email
 * The company email address
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		company_email
 */
function kbs_email_tag_company_email( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return kbs_get_company_email( $ticket->company_id );
} // kbs_email_tag_company_email

/**
 * Email template tag: company_phone
 * The company phone number
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		company_phone
 */
function kbs_email_tag_company_phone( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return kbs_get_company_phone( $ticket->company_id );
} // kbs_email_tag_company_phone

/**
 * Email template tag: company_website
 * The company website address
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		company_website
 */
function kbs_email_tag_company_website( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return kbs_get_company_website( $ticket->company_id );
} // kbs_email_tag_company_website

/**
 * Email template tag: company_logo
 * Inserts the company logo
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		company_logo
 */
function kbs_email_tag_company_logo( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );
	$return = '';
	$logo   = kbs_get_company_logo( $ticket->company_id );

	if ( $logo )	{
		$return = sprintf( '<img src="%s" alt="%s">', $logo, kbs_get_company_name( $ticket->company_id ) );
	}

	return $return;
} // kbs_email_tag_company_logo

/**
 * Email template tag: department
 * Inserts the department name who are handling the ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Department name
 */
function kbs_email_tag_department( $ticket_id ) {
	$department = kbs_get_department_for_ticket( $ticket_id );
    $return     = '';

    if ( $department )  {
        $return = $department->name;
    }

	return $return;
} // kbs_email_tag_department

/**
 * Email template tag: sitename
 * Your site name
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		sitename
 */
function kbs_email_tag_sitename( $ticket_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
} // kbs_email_tag_sitename

/**
 * Email template tag: date
 * Date of ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		date
 */
function kbs_email_tag_date( $ticket_id  ) {
	$post_time = get_post_time( 'U', false, $ticket_id );

	return date_i18n( get_option( 'date_format' ), $post_time );
} // kbs_email_tag_date

/**
 * Email template tag: time
 * Time of ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		date
 */
function kbs_email_tag_time( $ticket_id ) {
	$post_time = get_post_time( 'U', false, $ticket_id );

	return date_i18n( get_option( 'time_format' ), $post_time );
} // kbs_email_tag_time

/**
 * Email template tag: ticket_id
 * The unique ID number for this ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	int		Ticket number
 */
function kbs_email_tag_ticket_id( $ticket_id ) {
	return kbs_get_ticket_number( $ticket_id );
} // kbs_email_tag_ticket_id

/**
 * Email template tag: ticket_status
 * The current status of the ticket
 *
 * @since	1.2.2
 * @param	int		$ticket_status
 * @return	int		Ticket post status
 */
function kbs_email_tag_ticket_status( $ticket_id ) {
	return kbs_get_ticket_status( $ticket_id, true );
} // kbs_email_tag_ticket_status

/**
 * Email template tag: ticket_title
 * The title of the submitted ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket title
 */
function kbs_email_tag_ticket_title( $ticket_id )	{
	return get_the_title( $ticket_id );
} // kbs_email_tag_ticket_title

/**
 * Email template tag: ticket_content
 * The content of the submitted ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket content
 */
function kbs_email_tag_ticket_content( $ticket_id )	{
	return get_post_field( 'post_content', $ticket_id, 'raw' );
} // kbs_email_tag_ticket_content

/**
 * Email template tag: ticket_files
 * List of files attached to the ticket with links to open.
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Links to files
 */
function kbs_email_tag_ticket_files( $ticket_id )	{

    if ( kbs_send_files_as_attachments() )  {

        add_filter( 'kbs_ticket_attachments', 'kbs_maybe_attach_files_to_email_action', 10, 3 );
        add_filter( 'kbs_ticket_reply_attachments', 'kbs_maybe_attach_files_to_email_action', 10, 4 );
        add_filter( 'kbs_admin_ticket_notification_attachments', 'kbs_maybe_attach_files_to_email_action', 10, 3 );
        add_filter( 'kbs_admin_reply_notification_attachments', 'kbs_maybe_attach_files_to_email_action', 10, 4 );
        add_filter( 'kbs_ticket_closed_attachments', 'kbs_maybe_attach_files_to_email_action', 10, 3 );
        add_filter( 'kbs_agent_assigned_attachments', 'kbs_maybe_attach_files_to_email_action', 10, 3 );

    } else  {
        $files = kbs_ticket_has_files( $ticket_id );

        if ( $files )	{
            $output = '';
            foreach( $files as $file )	{
                $output .= '<p><a href="' . wp_get_attachment_url( $file->ID ) . '">' . basename( get_attached_file( $file->ID ) ) . '</a></p>';
            }

            return $output;
        }
    }
} // kbs_email_tag_ticket_files

/**
 * Email template tag: reply_author
 * Name or email address of the author to the most recent ticket reply.
 *
 * @since	1.2.4
 * @param	int		$ticket_id
 * @return	string	date
 */
function kbs_email_tag_reply_author( $ticket_id  ) {
	$reply = kbs_get_last_reply( $ticket_id );

	return kbs_get_reply_author_name( $reply );
} // kbs_email_tag_reply_author

/**
 * Email template tag: reply_date
 * Date of most recent reply
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		date
 */
function kbs_email_tag_reply_date( $ticket_id  ) {
	$reply = kbs_get_last_reply( $ticket_id );

	if ( $reply )	{
		$post_time = get_post_time( 'U', false, $reply->ID );

		return date_i18n( get_option( 'date_format' ), $post_time );
	}
} // kbs_email_tag_reply_date

/**
 * Email template tag: reply_time
 * Time of most recent reply
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		date
 */
function kbs_email_tag_reply_time( $ticket_id ) {
	$reply = kbs_get_last_reply( $ticket_id );

	if ( $reply )	{
		$post_time = get_post_time( 'U', false, $reply->ID );
	
		return date_i18n( get_option( 'time_format' ), $post_time );
	}
} // kbs_email_tag_reply_time

/**
 * Email template tag: reply_content
 * The content of the most recent ticket reply.
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Links to files
 */
function kbs_email_tag_reply_content( $ticket_id )	{
	$reply = kbs_get_last_reply( $ticket_id );

	if ( $reply )	{
		return get_post_field( 'post_content', $reply->ID, 'raw' );
	}
} // kbs_email_tag_reply_content

/**
 * Email template tag: reply_files
 * List of files attached to the reply with links to open.
 *
 * @since	1.1.9
 * @param	int		$ticket_id
 * @return	str		Ticket content
 */
function kbs_email_tag_reply_files( $ticket_id )	{
    $last_reply = kbs_get_last_reply( $ticket_id );

    if ( $last_reply )  {
        return kbs_email_tag_ticket_files( $last_reply->ID );
    }
} // kbs_email_tag_reply_files

/**
 * Email template tag: ticket_url
 * Adds a URL so customers can view their ticket directly on your website
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket URL
 */
function kbs_email_tag_ticket_url( $ticket_id ) {
	$url = kbs_get_ticket_url( $ticket_id, false, true );
	$url = apply_filters( 'kbs_tag_ticket_url', $url, $ticket_id );

	return '<a href="' . $url . '">' . $url . '</a>';
} // kbs_email_tag_ticket_url

/**
 * Email template tag: ticket_url_path
 * Adds a non-linked URL so customers can view their ticket directly on your website
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket URL path
 */
function kbs_email_tag_ticket_url_path( $ticket_id ) {
	$url = kbs_get_ticket_url( $ticket_id, false, true );
	$url = apply_filters( 'kbs_tag_ticket_url_path', $url, $ticket_id );

	return $url;
} // kbs_email_tag_ticket_url_path

/**
 * Email template tag: ticket_admin_url
 * Adds a URL so admins can access a ticket directly
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket admin URL
 */
function kbs_email_tag_ticket_admin_url( $ticket_id ) {
	$url = kbs_get_ticket_url( $ticket_id, true );
	$url = apply_filters( 'kbs_tag_ticket_admin_url', $url, $ticket_id );

	return '<a href="' . $url . '">' . $url . '</a>';
} // kbs_email_tag_ticket_admin_url

/**
 * Email template tag: ticket_admin_url_path
 * Adds a non-linked URL so admins can access a ticket directly
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket admin URL path
 */
function kbs_email_tag_ticket_admin_url_path( $ticket_id ) {
	$url = kbs_get_ticket_url( $ticket_id, true );
	$url = apply_filters( 'kbs_tag_ticket_admin_url_path', $url, $ticket_id );

	return $url;
} // kbs_email_tag_ticket_admin_url_path

/**
 * Email template tag: close_ticket_url
 * Adds a URL link so customers can close a ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Close ticket URL
 */
function kbs_email_tag_ticket_close_ticket_url( $ticket_id ) {
	$url = kbs_get_ticket_url( $ticket_id, false, true );
	$url = remove_query_arg( 'kbs_action', $url );
	$url = add_query_arg( 'kbs_action', 'close_ticket', $url );
	$url = apply_filters( 'kbs_tag_close_ticket_url', $url, $ticket_id );

	return '<a href="' . $url . '">' . $url . '</a>';
} // kbs_email_tag_ticket_close_ticket_url

/**
 * Email template tag: close_ticket_url_path
 * Adds a non-linked URL so customers can close a ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Close ticket URL path
 */
function kbs_email_tag_ticket_close_ticket_url_path( $ticket_id ) {
	$url = kbs_get_ticket_url( $ticket_id, false, true );
	$url = remove_query_arg( 'kbs_action', $url );
	$url = add_query_arg( 'kbs_action', 'close_ticket', $url );
	$url = apply_filters( 'kbs_tag_close_ticket_url_path', $url, $ticket_id );

	return $url;
} // kbs_email_tag_ticket_close_ticket_url_path
