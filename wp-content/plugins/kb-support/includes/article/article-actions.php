<?php
/**
 * KB Article Actions
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
 * Process a search of the KB Articles.
 *
 * @since	1.0
 * @return	void
 */
function kbs_search_articles_action()	{

	if ( ! isset( $_GET['kbs_action'] ) || 'search_articles' != $_GET['kbs_action'] )	{
		return;
	}

	$args = array(
		's'                   => urlencode( $_GET['s_article'] ),
		'post_type'           => KBS()->KB->post_type,
		'ignore_sticky_posts' => true,
		'cache_results'       => false,
		'order_by'            => 'relevance'
	);

	$args = apply_filters( 'kbs_article_search', $args );

	$redirect = add_query_arg( $args, esc_url( home_url( '/' ) ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_search_articles_action
add_action( 'init', 'kbs_search_articles_action' );

/**
 * Creates a new article when a ticket is closed.
 *
 * @since	1.0
 * @return	void
 */
function kbs_create_article_action()	{

	if ( ! isset( $_GET['kbs-action'] ) || 'create_article' != $_GET['kbs-action'] )	{
		return;
	}

	$ticket = new KBS_Ticket( $_GET['ticket_id'] );

	if ( ! empty( $_GET['reply_id'] ) )	{
		$reply = get_post( $_GET['reply_id'] );
	} else	{
		$reply = kbs_get_last_reply( $ticket->ID );
	}

	if ( $reply )	{
		$args = array(
			'post_status'  => 'draft',
			'post_title'   => $ticket->ticket_title,
			'post_content' => $reply->post_content
		);

		$article_id = kbs_add_article( $args, $ticket->ID );
	} else	{
		$article_id = false;
	}

	if ( $article_id )	{

		$redirect_args = array(
			'post'        => $article_id,
			'kbs-message' => 'article_created',
			'action'      => 'edit'
		);

	} else	{

		$redirect_args = array(
			'post'        => $_GET['ticket_id'],
			'kbs-message' => 'create_article_failed',
			'action'      => 'edit'
		);

	}

	$redirect = add_query_arg( $redirect_args, admin_url( 'post.php' ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_create_article_action
add_action( 'init', 'kbs_create_article_action' );

/**
 * Reset an articles view count.
 *
 * @since	1.0.6
 */
function kbs_reset_article_view_count()	{

	if ( ! isset( $_GET['kbs-action'] ) || 'reset_article_views' != $_GET['kbs-action'] )	{
		return;
	}

	if ( ! isset( $_GET['kbs-nonce'] ) || ! wp_verify_nonce( $_GET['kbs-nonce'], 'reset_views' ) )	{
		wp_die( 'Cheatin&#8217; huh?' );
	}

	if ( ! isset( $_GET['article_id'] ) || KBS()->KB->post_type != get_post_type( $_GET['article_id'] ) )	{
		return;
	}

	$article_id = absint( $_GET['article_id'] );
	$total_key  = kbs_get_article_view_count_meta_key_name();
	$month_key  = kbs_get_article_view_count_meta_key_name( false );

	if ( update_post_meta( $article_id, $total_key, 0 ) && update_post_meta( $article_id, $month_key, 0 ) )	{
		$message = 'reset_article_views';
	} else	{
		$message = 'reset_article_views_failed';
	}

	$redirect = add_query_arg( array(
		'post_type'   => KBS()->KB->post_type,
		'kbs-message' => $message
	), admin_url( 'edit.php' ) );

	wp_safe_redirect( $redirect );

	die();

} // kbs_reset_article_view_count
add_action( 'init', 'kbs_reset_article_view_count' );
