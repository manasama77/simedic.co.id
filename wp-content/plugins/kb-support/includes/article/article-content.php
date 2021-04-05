<?php
/**
 * Content for Restricted KB Articles
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
 * Filter Article Content if it is restricted.
 *
 * @since	1.0
 * @global	$post
 *
 * @param	str		$content	The the_content field of the kb article object
 * @return	str		The content with any additional data attached
 */
function kbs_filter_article_content( $content ) {
	global $post;

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )	{
		return $content;
	}

	if ( empty( $post ) || ! is_object( $post ) )	{
		return $content;
	}

	if ( KBS()->KB->post_type == $post->post_type && ! kbs_article_user_can_access( $post ) )	{

		if ( kbs_article_is_restricted() )	{

			// Remove comments
			kbs_article_remove_comments();

			$type = is_single() ? 'single' : 'archive';

			$content = kbs_article_format_content( $type );

		}

	}

	return $content;
} // kbs_filter_article_content
add_filter( 'the_content', 'kbs_filter_article_content', 100 );

/**
 * Display an overview only for restricted content. 
 *
 * @since	1.0
 * @param	str		$type		The type of content being displayed. 'single' | 'archive'
 * @return	str		The content to display for restricted articles.
 */
function kbs_article_format_content( $type = 'single' )	{
	global $post;

	$excerpt_length = kbs_get_article_excerpt_length();
	$raw_message    = '';
	$excerpt        = kbs_article_excerpt_by_id( $post, $excerpt_length );
	$message        = '';

	if ( 'single' == $type )	{
		$raw_message = kbs_get_option( 'restricted_notice' );
		$message     = '<div class="kbs_alert kbs_alert_info">' . wpautop( stripslashes( $raw_message ) ) . '</div>';

		$register_login = kbs_get_option( 'restricted_login', 'none' );
	
		if ( 'both' == $register_login || 'login' == $register_login )	{
			$message .= kbs_login_form( kbs_get_current_page_url() );
		}

		if ( 'both' == $register_login || 'registration' == $register_login )	{
			$message .= kbs_register_form( kbs_get_current_page_url() );
		}

	}

	$message = apply_filters( 'kbs_article_restricted_message', $message, $raw_message, $type );
	$content = $excerpt . $message;

	return $content;
} // kbs_article_format_content

/**
 * Retrieve the excerpt length from settings.
 *
 * @since	1.0
 * @return	int		The required excerpt length 
 */

function kbs_get_article_excerpt_length()	{
	$length = kbs_get_option( 'article_excerpt_length', 0 );

	return (int) apply_filters( 'kbs_article_excerpt_length', $length );
} // kbs_get_article_excerpt_length

/**
 * Gets the excerpt of a specific article by its ID or object.
 *
 * @param	obj|int	$post		The ID or object of the post article to get the excerpt of
 * @param	int		$length		The length of the excerpt in words
 * @param	str		$tags		The allowed HTML tags that will not be stripped out.
 * @param	str		$extra		Text to append to the end of the excerpt.
 */

function kbs_article_excerpt_by_id( $post, $length = '', $tags = '<a><em><strong><blockquote><ul><ol><li><p>', $extra = ' . . .' ) {

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	} elseif ( ! is_object( $post ) ) {
		return false;
	}

	if ( empty( $length ) )	{
		$length = kbs_get_article_excerpt_length();
	}

	$more = false;

	if ( has_excerpt( $post->ID ) ) {
		$the_excerpt = $post->post_excerpt;
	} elseif ( strstr( $post->post_content, '<!--more-->' ) ) {
		$more = true;
		$length = strpos( $post->post_content, '<!--more-->' );
		$the_excerpt = $post->post_content;
	} else {
		$the_excerpt = $post->post_content;
	}

	$tags = apply_filters( 'kbs_article_excerpt_tags', $tags );

	if ( $more ) {
		$the_excerpt = strip_shortcodes( strip_tags( stripslashes( substr( $the_excerpt, 0, $length ) ), $tags ) );
	} else {
		$the_excerpt   = strip_shortcodes( strip_tags( stripslashes( $the_excerpt ), $tags ) );
		$the_excerpt   = preg_split( '/\b/', $the_excerpt, $length * 2+1 );
		$excerpt_waste = array_pop( $the_excerpt );
		$the_excerpt   = implode( $the_excerpt );
		$the_excerpt  .= $extra;
	}

	return wpautop( force_balance_tags( $the_excerpt ) );
} // kbs_article_excerpt_by_id

/**
 * Remove comments for restricted articles.
 *
 * @since	1.0
 * @return	void
 */
function kbs_article_remove_comments()	{
	add_filter( 'comments_open', '__return_false');
	add_filter( 'get_comments_number', '__return_false');
} // kbs_article_remove_comments
