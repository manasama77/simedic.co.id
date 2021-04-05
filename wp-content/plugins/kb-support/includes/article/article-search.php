<?php
/**
 * KB Article Search Functions
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
 * Exclude restricted articles from main WP search if user is not logged in.
 *
 * @since	1.0
 * @param	obj		$query	The current query
 * @return	void
 */
function kbs_filter_articles_search( $query )	{

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )	{
		return;
	}

	if ( is_admin() )	{
		return;
	}

	if ( ! kbs_hide_restricted_articles() || is_user_logged_in() )	{
		return;
	}

	if ( $query->is_main_query() )	{

		if ( $query->is_search )	{

			$hidden_ids = kbs_get_restricted_articles();

			$query->set( 'post__not_in', $hidden_ids );

		}

	}

} // kbs_filter_articles_search
add_action( 'pre_get_posts', 'kbs_filter_articles_search' );

/**
 * Renders the KB Article search form.
 *
 * @since	1.0
 * @return	str
 */
function kbs_article_search_form()	{
	ob_start();

	kbs_get_template_part( 'shortcode', 'search' );

	return apply_filters( 'kbs_article_search_form', ob_get_clean() );
} // kbs_article_search_form
