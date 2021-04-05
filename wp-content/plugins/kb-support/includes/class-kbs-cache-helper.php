<?php
/**
 * KBS_Cache_Helper class
 *
 * @package     KBS
 * @subpackage  Classes/Caching
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class KBS_Cache_Helper {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'prevent_caching' ), 0 );
		add_action( 'update_option_kbs_settings', array( __CLASS__, 'delete_page_cache' ), 999, 2 );
	} // init

	/**
	 * Get the page name/id for a KBS page.
	 *
	 * @since	1.1
	 * @param 	str		$kbs_page	The page to retrieve the name/id for
	 * @return	arr		Array of page id/name
	 */
	private static function get_page_uris( $kbs_page ) {
		$kbs_page_uris = array();

		if ( ( $page_id = kbs_get_page_id( $kbs_page ) ) && $page_id > 0 && ( $page = get_post( $page_id ) ) )	{
			$kbs_page_uris[] = 'p=' . $page_id;
			$kbs_page_uris[] = '/' . $page->post_name . '/';
		}

		return $kbs_page_uris;
	} // get_page_uris

	/**
	 * Prevent caching on dynamic pages.
	 */
	public static function prevent_caching() {

		if ( ! is_blog_installed() ) {
			return;
		}

        if ( KBS()->KB->post_type == get_post_type() && kbs_article_is_restricted( get_the_ID() ) )    {
            self::nocache();
            return;
        }

		if ( false === ( $kbs_page_uris = get_transient( 'kbs_cache_excluded_uris' ) ) )	{
			$kbs_page_uris = array_filter( array_merge( self::get_page_uris( 'submission' ), self::get_page_uris( 'tickets' ) ) );
	    	set_transient( 'kbs_cache_excluded_uris', $kbs_page_uris, DAY_IN_SECONDS );
		}

		if ( is_array( $kbs_page_uris ) )	{
			foreach ( $kbs_page_uris as $uri )	{
				if ( stristr( trailingslashit( $_SERVER['REQUEST_URI'] ), $uri ) )	{
					self::nocache();
					break;
				}
			}
		} 
	} // prevent_caching

	/**
	 * Set nocache constants and headers.
	 *
	 * @since	1.1
	 * @access	private
	 */
	private static function nocache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( "DONOTCACHEPAGE", true );
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( "DONOTCACHEOBJECT", true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( "DONOTCACHEDB", true );
		}
		nocache_headers();
	} // nocache

	/**
	 * Delete the page cache when settings are updated.
	 *
	 * @since	1.1
	 * @param	mixed	The pre-save value of the setting
	 * @param	mixed	The updated value of the setting
	 * @return	void
	 */
	 public static function delete_page_cache( $old_value, $value )	{
		if ( ! isset( $old_value['tickets_page'] ) )	{
			return;
		}

		$pages = array( 'submission', 'tickets' );

		foreach( $pages as $page )	{
			if ( $value[ $page . '_page' ] != $old_value[ $page . '_page' ] )	{
				delete_transient( 'kbs_cache_excluded_uris' );
				break;
			}
		}

	 } // delete_page_cache

} // class KBS_Cache_Helper

KBS_Cache_Helper::init();
