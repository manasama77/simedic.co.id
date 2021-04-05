<?php
/**
 * Ticket Functions
 *
 * @package     KBS
 * @subpackage  Replies/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve all ticket replies for the ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id		The Ticket ID.
 * @param	arr		$args			See @get_posts
 * @return	obj|false
 */
function kbs_get_replies( $ticket_id = 0, $args = array() )	{
	if ( empty( $ticket_id ) )	{
		return false;
	}

	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->get_replies( $args );
} // kbs_get_replies

/**
 * Retrieve ticket reply count.
 *
 * @since	1.0
 * @param	int		$ticket_id		The Ticket ID.
 * @return	int
 */
function kbs_get_reply_count( $ticket_id )	{
	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->get_reply_count();
} // kbs_get_reply_count

/**
 * Count tickets by reply status.
 *
 * @since   1.4.1
 * @param   string  $waiting_from   Count tickets awaiting replys from
 * @return  int     Count of tickets
 */
function kbs_count_tickets_by_reply_status( $waiting_from = 'all' )   {
    global $wpdb;

    $where = "WHERE meta_key = '_kbs_ticket_last_reply_by'";

    if ( 'agent' == $waiting_from ) {
        $where .= " AND meta_value = 3";
    } elseif ( 'customer' == $waiting_from )    {
        $where .= " AND ( meta_value = 1 OR meta_value = 2 )";
    }

    $where = apply_filters( 'kbs_count_tickets_by_reply_status_where', $where, $waiting_from );

    $count = $wpdb->get_var(
        "
        SELECT COUNT(*)
        FROM $wpdb->postmeta
        $where
        "
    );

    return $count;
} // kbs_count_tickets_by_reply_status

/**
 * Get the ticket reply status colours.
 *
 * @since	1.4
 * @param	string	$replier	Who replied last
 * @param	bool	$default	Whether or not to return the default colour
 * @return	string	Ticket reply status colour
 */
function kbs_get_ticket_reply_status_colour( $replier, $default = false )	{
	$replier  = strtolower( $replier );
	$defaults = apply_filters( 'kbs_default_ticket_reply_status_colours', array(
		'admin'    => '#6b5b95',
		'agent'    => '#6b5b95',
		'customer' => '#c94c4c'
	) );

	if ( $default )	{
		if ( ! array_key_exists( $replier, $defaults ) )	{
			$replier = 'agent';
		}

		return $defaults[ $status ];
	}

	$default_colour = '';

	if ( array_key_exists( $replier, $defaults ) )	{
		$default_colour = $defaults[ $replier ];
	}

	$colour = kbs_get_option( 'colour_reply_' . $replier, $default_colour );
	$colour = apply_filters( 'kbs_ticket_reply_status_colour' . $replier, $colour );

	return $colour;
} // kbs_get_ticket_reply_status_colour

/**
 * Whether or not an agent has replied to a ticket.
 *
 * @since	1.0
 * @param	int			$ticket_id		The Ticket ID.
 * @return	obj|false	
 */
function kbs_ticket_has_agent_reply( $ticket_id )	{
	$reply_args = array(
		'posts_per_page' => 1,
		'meta_query'     => array(
			'relation'    => 'AND',
			array(
				'key'     => '_kbs_reply_agent_id',
				'compare' => 'EXISTS'
			),
			array(
				'key'     => '_kbs_reply_agent_id',
				'value'   => '0',
				'compare' => '!='
			)
		)
	);

	return kbs_get_replies( $ticket_id, $reply_args );
} // kbs_ticket_has_agent_reply

/**
 * Retrieve the last reply for the ticket.
 *
 * @since	1.0
 * @uses	kbs_get_replies()
 * @param	int		$ticket_id		The Ticket ID.
 * @param	arr		$args			See @get_posts
 * @return	obj|false
 */
function kbs_get_last_reply( $ticket_id, $args = array() )	{
	$args['posts_per_page'] = 1;

	$reply = kbs_get_replies( $ticket_id, $args );

	if ( $reply )	{
		return $reply[0];
	}

	return $reply;
} // kbs_get_last_reply

/**
 * Gets the ticket reply HTML.
 *
 * @since	1.0
 * @param	obj|int	$reply		The reply object or ID
 * @param	int		$ticket_id	The ticket ID the reply is connected to
 * @param   bool    $expand     Whether or not to auto expand the reply
 * @return	str
 */
function kbs_get_reply_html( $reply, $ticket_id = 0, $expand = false ) {

	if ( is_numeric( $reply ) ) {
		$reply = get_post( $reply );
	}

	$author      = kbs_get_reply_author_name( $reply, true );
	$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
	$files       = kbs_ticket_has_files( $reply->ID );
	$file_count  = ( $files ? count( $files ) : false );
    $show        = $expand ? ' style="display: block;"' : '';
	$show_hide   = $expand ? __( 'Hide', 'kb-support' ) : __( 'View', 'kb-support' );

	$create_article_link = add_query_arg( array(
		'kbs-action' => 'create_article',
		'ticket_id'  => $ticket_id,
		'reply_id'   => $reply->ID
	), admin_url() );

	$create_article_link = apply_filters( 'kbs_create_article_link', $create_article_link, $ticket_id, $reply );

    $actions = array(
        'read_reply'     => '<a href="#" class="toggle-view-reply-option-section">' . sprintf( __( '%s Reply', 'kb-support' ), $show_hide ) . '</a>',
        'create_article' => '<a href="' . $create_article_link . '" class="toggle-reply-option-create-article">' . sprintf( __( 'Create %s', 'kb-support' ), kbs_get_article_label_singular() ) . '</a>'
    );

    $actions = apply_filters( 'kbs_ticket_replies_actions', $actions, $reply );

    $icons   = array();

    if ( false === strpos( $author, __( 'Customer', 'kb-support' ) ) && false === strpos( $author, __( 'Participant', 'kb-support' ) ) )  {
        $is_read = kbs_reply_is_read( $reply->ID );
        if ( $is_read )  {
            $icons['is_read'] = sprintf(
                '<span class="dashicons dashicons-visibility" title="%s %s"></span>',
                __( 'Read by customer on', 'kb-support' ),
                date_i18n( $date_format, strtotime( $is_read ) )
            );
        } else  {
            $icons['not_read'] = sprintf(
                '<span class="dashicons dashicons-hidden" title="%s"></span>',
                __( 'Customer has not read', 'kb-support' )
            );
        }

        if ( 'closed' != get_post_status( $ticket_id ) && ( current_user_can( 'manage_ticket_settings' ) || get_current_user_id() == $reply->post_author ) ) {

            $delete_url  = wp_nonce_url( add_query_arg( array(
                'kbs-action' => 'delete_ticket_reply',
                'reply_id'   => $reply->ID,
                'ticket_id'  => $ticket_id
            ), admin_url() ), 'delete_ticket_reply', 'kbs_nonce' );

            $actions['trash'] = sprintf(
                '<a href="%s" class="kbs-delete delete-reply">%s</a>',
                $delete_url,
                __( 'Delete Reply', 'kb-support' )
            );

        }

    }

    if ( $file_count )  {
        $icons['files'] = sprintf(
            '<span class="dashicons dashicons-media-document" title="%s"></span>',
            $file_count . ' ' . _n( 'attached file', 'attached files', $file_count, 'kb-support' )
        );
    }

    $icons   = apply_filters( 'kbs_ticket_replies_icons', $icons, $reply );

    ob_start(); ?>

    <div class="kbs-replies-row-header">
        <span class="kbs-replies-row-title">
            <?php echo apply_filters( 'kbs_replies_title', sprintf( __( '%s by %s', 'kb-support' ), date_i18n( $date_format, strtotime( $reply->post_date ) ), $author ), $reply ); ?>
        </span>

        <span class="kbs-replies-row-actions">
            <?php echo implode( ' ', $icons ); ?>
			<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
        </span>
    </div>

    <div class="kbs-replies-content-wrap"<?php echo $show; ?></div>
        <div class="kbs-replies-content-sections">
        	<?php do_action( 'kbs_before_reply_content_section', $reply ); ?>
            <div id="kbs-reply-option-section-<?php echo $reply->ID; ?>" class="kbs-replies-content-section">
                <?php do_action( 'kbs_replies_before_content', $reply ); ?>
                <?php echo wpautop( $reply->post_content ); ?>
                <?php do_action( 'kbs_replies_content', $reply ); ?>
            </div>
            <?php do_action( 'kbs_after_reply_content_section', $reply ); ?>
            <?php if ( $files ) : ?>
                <div class="kbs-replies-files-section">
                	<?php do_action( 'kbs_replies_before_files', $reply ); ?>
                    <ol>
                        <?php foreach( $files as $file ) : ?>
                            <li>
                            	<a href="<?php echo wp_get_attachment_url( $file->ID ); ?>" target="_blank">
									<?php echo basename( get_attached_file( $file->ID ) ); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <?php do_action( 'kbs_replies_after_files', $reply ); ?>
                </div>
            <?php endif; ?>
            <?php do_action( 'kbs_after_reply_content_section', $reply ); ?>
        </div>
    </div>

    <?php

    return ob_get_clean();

} // kbs_get_reply_html

/**
 * Retrieve the name of the person who replied to the ticket.
 *
 * @since	1.0
 * @param	obj|int	$reply		The reply object or ID
 * @param	bool	$role		Whether or not to include the role in the response
 * @return	str		The name of the person who authored the reply. If $role is true, their role in brackets
 */
function kbs_get_reply_author_name( $reply, $role = false )	{
	if ( is_numeric( $reply ) ) {
		$reply = get_post( $reply );
	}

	$author       = __( 'Unknown', 'kb-support' );
	$author_role  = __( 'Customer', 'kb-support' );
    $author_email = kbs_participants_enabled() ? get_post_meta( $reply->ID, '_kbs_reply_participant', true ) : false;
    $author_email = is_email( $author_email );
    $ticket_email = kbs_get_ticket_user_email( $reply->post_parent );
    $customer_id  = get_post_meta( $reply->post_parent, '_kbs_ticket_customer_id', true );

	if ( ! empty( $reply->post_author ) ) {
		$author = get_userdata( $reply->post_author );
		$author = $author->display_name;

        if ( kbs_is_agent( $reply->post_author ) )   {
            $author_role = __( 'Agent', 'kb-support' );
        } elseif ( $author_email )   {
            $author_customer = new KBS_Customer( $author_email );

            if ( $author_customer && $author_customer->id > 0 )   {
                if ( in_array( $ticket_email, $author_customer->emails ) )   {
                    $author_role = __( 'Participant', 'kb-support' );
                }
            }
        }
	} else {
        if ( $author_email )    {
            $author_customer = new KBS_Customer( $author_email );
            if ( $author_customer && $author_customer->id > 0 && $author_customer->id == $customer_id )   {
				$author      = $author_customer->name;
				$author_role = __( 'Customer', 'kb-support' );
            } elseif ( $author_customer && $author_customer->id > 0 && $author_customer->id != $customer_id )   {
				$author      = $author_customer->name;
				$author_role = __( 'Participant', 'kb-support' );
            } else  {
                $author      = $author_email;
                $author_role = __( 'Participant', 'kb-support' );
            }
        } elseif ( $customer_id )	{
			$customer = new KBS_Customer( $customer_id );
			if ( $customer )	{
				$author      = $customer->name;
				$author_role = __( 'Customer', 'kb-support' );
			}
		}
	}

	if ( $role && ! empty( $author_role ) )	{
		$author .= ' (' . $author_role . ')';
	}

	return apply_filters( 'kbs_reply_author_name', $author, $reply, $role, $author_role );

} // kbs_get_reply_author_name

/**
 * Retrieve ticket ID from reply.
 *
 * @since   1.2
 * @param   int     $reply_id
 * @return  int|false
 */
function kbs_get_ticket_id_from_reply( $reply_id )  {
    $ticket_id = get_post_field( 'post_parent', $reply_id );
    return apply_filters( 'kbs_ticket_id_from_reply', $ticket_id );
} // kbs_get_ticket_id_from_reply

/**
 * Mark a reply as read.
 *
 * @since   1.2
 * @param   int     $reply_id
 * @return  int|false
 */
function kbs_mark_reply_as_read( $reply_id )  {

    $ticket_id = kbs_get_ticket_id_from_reply( $reply_id );

    if ( empty( $ticket_id) )   {
        return false;
    }

    $ticket      = new KBS_Ticket( $ticket_id );
    $customer_id = $ticket->customer_id;

    if ( ! empty( $customer_id ) )  {
        $user_id = get_current_user_id();
        if ( ! empty( $user_id ) )  {
            if ( $user_id !== $customer_id )    {
                $mark_read = false;
            }
        }
    }

    $mark_read = apply_filters( 'kbs_mark_reply_as_read', true, $reply_id, $ticket );

    if ( ! $mark_read ) {
        return false;
    }

    do_action( 'kbs_customer_read_reply', $reply_id, $ticket );

    return add_post_meta( $reply_id, '_kbs_reply_customer_read', current_time( 'mysql'), true );

} // kbs_mark_reply_as_read

/**
 * Whether or not a reply has been read.
 *
 * @since   1.2
 * @param   int         $reply_id
 * @return  str|false   false if unread, otherwise the datetime the reply was read.
 */
function kbs_reply_is_read( $reply_id ) {
    $read = get_post_meta( $reply_id, '_kbs_reply_customer_read', true );

    return apply_filters( 'kbs_reply_is_read', $read, $reply_id );
} // kbs_reply_is_read

/**
 * Count Replies
 *
 * Returns the total number of replies.
 *
 * @since	1.2
 * @param	array	$args	List of arguments to base the reply count on
 * @return	array	$count	Number of replies sorted by reply date
 */
function kbs_count_replies( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'agent'      => null,
		'user'       => null,
		'customer'   => null, // Unused
		'ticket'     => null,
		'start-date' => null,
		'end-date'   => null
	);

	$args = wp_parse_args( $args, $defaults );

	$select = "SELECT count(*)";
	$join = '';
	$where = "WHERE p.post_type = 'kbs_ticket_reply' AND p.post_status = 'publish'";

	// Limit reply count by received date
	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '-' ) ) {

		$date_parts = explode( '-', $args['start-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['start-date'] );
			$where .= $wpdb->prepare( " AND p.post_date >= '%s'", $date->format( 'Y-m-d' ) );

		}

		// Fixes an issue with the replies list table counts when no end date is specified (partly with stats class)
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}

	}

	if ( ! empty ( $args['end-date'] ) && false !== strpos( $args['end-date'], '-' ) ) {

		$date_parts = explode( '-', $args['end-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['end-date'] );
			$where .= $wpdb->prepare( " AND p.post_date <= '%s'", $date->format( 'Y-m-d' ) );

		}

	}

	// Replies by ticket ID
	if ( ! empty( $args['ticket'] ) )	{
		$ticket_id = absint( $args['ticket'] );
		if ( ! empty( $ticket_id ) )	{
			$where .= $wpdb->prepare( " AND p.post_parent = '%d'", $ticket_id );
		}
	}

	// Replies by agent ID
	if ( ! empty( $args['agent'] ) )	{
		$agent_id = absint( $args['agent'] );
		if ( ! empty( $agent_id ) )	{
			$where .= $wpdb->prepare( " AND p.post_author = '%d'", $agent_id );
		}
	}

	// Replies by user ID
	if ( ! empty( $args['user'] ) )	{
		$user_id = absint( $args['user'] );
		if ( ! empty( $user_id ) )	{
			$where .= $wpdb->prepare( " AND p.post_author = '%d'", $user_id );
		}
	}

	$where = apply_filters( 'kbs_count_replies_where', $where );
	$join  = apply_filters( 'kbs_count_replies_join', $join );

	$query = "$select
		FROM $wpdb->posts p
		$join
		$where
	";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_var( $query );
	wp_cache_set( $cache_key, $count, 'counts', DAY_IN_SECONDS );

	return $count;
} // kbs_count_replies
