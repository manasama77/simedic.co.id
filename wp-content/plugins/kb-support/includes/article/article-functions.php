<?php
/**
 * KB Article Functions
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
 * Get KB Articles
 *
 * Retrieve KB Articles from the database.
 *
 * This is a simple wrapper for KBS_Articles_Query.
 *
 * @since	1.0
 * @param	arr		$args		Arguments passed to get_articles
 * @return	obj		$articles	Articles retrieved from the database
 */
function kbs_get_articles( $args = array() ) {
	$articles = new KBS_Articles_Query( $args );

	return $articles->get_articles();
} // kbs_get_articles

/**
 * Add a new article.
 *
 * @since	1.0
 * @param	arr			array	Post arguments
 * @param	obj|int		$ticket	Linked ticket ID or KBS_Ticket object
 * @return	int|bool	The article ID if successful, or false
 */
function kbs_add_article( $args = array(), $ticket = 0 )	{

	remove_action( 'save_post_article', 'kbs_article_post_save', 10, 3 );

	$ticket_id   = 0;
	$total_views = kbs_get_article_view_count_meta_key_name();
	$month_views = kbs_get_article_view_count_meta_key_name( false );

	$defaults = array(
		'post_type'    => KBS()->KB->post_type,
		'post_author'  => get_current_user_id(),
		'post_status'  => 'publish',
		'post_title'   => '',
		'post_content' => '',
		'meta_input'   => array(
			$total_views => 0,
			$month_views => 0
		)
	);

	$args = wp_parse_args( $args, $defaults );

	if ( isset( $ticket ) )	{
		
		if ( is_numeric( $ticket ) )	{
			$ticket_id = $ticket;
		} else	{
			$ticket    = new KBS_Ticket( $ticket );
			$ticket_id = $ticket->ID;
		}

		$args['meta_input']['_kbs_article_linked_tickets'] = array( $ticket_id );

	}

	$args = apply_filters( 'kbs_add_article_args', $args, $ticket_id );

	do_action( 'kbs_before_add_article', $ticket_id, $args );

	$article_id = wp_insert_post( $args );

	do_action( 'kbs_add_article', $article_id, $ticket_id, $args );

	add_action( 'save_post_article', 'kbs_article_post_save', 10, 3 );

	return $article_id;

} // kbs_add_article

/**
 * Count Articles
 *
 * Returns the total number of articles.
 *
 * @since	1.0
 * @param	arr	$args	List of arguments to base the article count on
 * @return	arr	$count	Number of articles sorted by article date
 */
function kbs_count_articles( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'agent'      => null,
		'author'     => null,
		'restricted' => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null
	);

	$args = wp_parse_args( $args, $defaults );

	$select = "SELECT p.post_status,count( * ) AS num_posts";
	$join = '';
	$where = "WHERE p.post_type = '" . KBS()->KB->post_type . "'";

	// Count articles for a search
	if( ! empty( $args['s'] ) ) {

		$search = $wpdb->esc_like( $args['s'] );
		$search = '%' . $search . '%';

		$where .= $wpdb->prepare( "AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))", $search, $search );

	}

	// Limit article count by author
	if ( ! empty( $args['author'] ) )	{
		$where .= $wpdb->prepare( " AND p.post_author = '%s'", $args['author'] );
	}

	// Limit article count by received date
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

		// Fixes an issue with the articles list table counts when no end date is specified (partly with stats class)
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

	$where = apply_filters( 'kbs_count_articles_where', $where );
	$join  = apply_filters( 'kbs_count_articles_join', $join );

	$query = "
		$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );
	$stats    = array();
	$total    = 0;
	$statuses = get_post_stati();

	foreach ( $statuses as $state ) {
		$stats[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {
		if ( ! in_array( $row['post_status'], $statuses ) )	{
			continue;
		}
		$stats[ $row['post_status'] ] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
} // kbs_count_articles

/**
 * Whether or not to count agent article views.
 *
 * @since	1.3.4
 * @return	bool	True to count, otherwise false
 */
function kbs_count_agent_article_views()	{
	$count = kbs_get_option( 'count_agent_article_views', false );
	$count = apply_filters( 'kbs_increment_count_agent_article_views', $count );

	return (bool) $count;
} // kbs_count_agent_article_views

/**
 * Retrieves the meta key name for the article view count.
 *
 * @since	1.3
 * @param	bool	$total		True returns total views, false monthly
 * @return	string	The meta key name
 */
function kbs_get_article_view_count_meta_key_name( $total = true )	{
	$key = '_kbs_article_views';

	if ( ! $total )	{
		$month = date( 'Y-m' );
		$key   = $key . '_' . $month;
	}

	return apply_filters( 'kbs_article_view_count_meta_key_name', $key );
} // kbs_get_article_view_count_meta_key_name

/**
 * Retrieve the total view count for a KB Article.
 *
 * @since	1.0
 * @param	int		$article_id	Post ID
 * @param	bool	$total		True returns total views, false monthly
 * @return	int
 */
function kbs_get_article_view_count( $article_id, $total = true )	{
	$key        = kbs_get_article_view_count_meta_key_name( $total );
	$view_count = get_post_meta( $article_id, $key, true );
	
	if ( ! $view_count )	{
		$view_count = 0;
	}
	
	return apply_filters( 'kbs_article_view_count', absint( $view_count ), $article_id, $total );
} // kbs_get_article_view_count

/**
 * Increment the total view count for a KB Article.
 *
 * @since	1.0
 * @param	int		$article_id		Post ID
 * @return	bool
 */
function kbs_increment_article_view_count( $article_id )	{
	$total_view_count = kbs_get_article_view_count( $article_id );
	$month_view_count = kbs_get_article_view_count( $article_id, false );

	$total_view_count = $total_view_count ? $total_view_count : 0;
	$month_view_count = $month_view_count ? $month_view_count : 0;

	$total_view_count++;
	$month_view_count++;

	$total_key = kbs_get_article_view_count_meta_key_name();
	$month_key = kbs_get_article_view_count_meta_key_name( false );

	update_post_meta( $article_id, $month_key, $month_view_count );

	return update_post_meta( $article_id, $total_key, $total_view_count );
} // kbs_increment_article_view_count

/**
 * Whether to show article view counts on the dashboard widget.
 *
 * @since	1.3
 * @return	bool
 */
function kbs_show_dashboard_article_view_counts()	{
	return kbs_get_option( 'article_views_dashboard' );
} // kbs_show_dashboard_article_view_counts

/**
 * Retrieve article terms.
 *
 * @since	1.0
 * @param	int			$article_id		The article post ID
 * @return	arr			Array of term ids that are associated with the article
 */
function kbs_get_article_terms( $article_id = 0 )	{
	if ( empty( $article_id ) || ! is_numeric( $article_id ) )	{
		$article_id = get_the_ID();
	}

	return wp_get_post_terms( $article_id, 'article_category', array( 'fields' => 'ids' ) );
} // kbs_get_article_terms

/**
 * Retrieve the KB Article excerpt.
 *
 * @since	1.0
 * @param	int		$article_id		Article ID
 * @return	str		The article excerpt.
 */
function kbs_get_article_excerpt( $article_id ) {

	if ( empty( $article_id ) )	{
		return;
	}

	if ( has_excerpt( $article_id ) )	{
		$excerpt = get_post_field( 'post_excerpt', $article_id );
	} else	{
		$excerpt = get_post_field( 'post_content', $article_id );
	}

	$num_words = kbs_get_article_excerpt_length();
	$excerpt   = wp_trim_words( $excerpt, $num_words, '&hellip;' );

	return apply_filters( 'kbs_article_excerpt', $excerpt );

} // kbs_get_article_excerpt

/**
 * Retrieve linked ticket ID's.
 *
 * @since	1.0
 * @param	int			$article_id		The article ID
 * @return	arr|false	Array of linked ticket ID's or false
 */
function kbs_get_linked_tickets( $article_id )	{
	return get_post_meta( $article_id, '_kbs_article_linked_tickets', true );
} // kbs_get_linked_tickets

/**
 * Retrieve the article URL.
 *
 * @since	1.1.12
 * @param	obj|int		$article	The article ID or post object
 * @return	str			The URL to the article
 */
function kbs_get_article_url( $article )	{
	if ( is_numeric ( $article ) )	{
		$article = get_post( $article );
	}

	$url = get_permalink( $article );
	$url = apply_filters( 'kbs_article_url', $url, $article->ID );

	return $url;
} // kbs_get_article_url
