<?php
/**
 * Article Stats
 *
 * @package     KBS
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Article_Stats Class
 *
 * This class is for retrieving stats for articles
 *
 * Stats can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.0
 */
class KBS_Article_Stats extends KBS_Stats {

	/**
	 * Retrieve article stats.
	 *
	 * @access	public
	 * @since	1.0
	 * @param	str|bool	$start_date The starting date for which we'd like to filter our article stats. If false, we'll use the default start date of `this_month`
	 * @param	str|bool	$end_date 	The end date for which we'd like to filter our article stats. If false, we'll use the default end date of `this_month`
	 * @return 	float|int 	Total amount of articles based on the passed arguments.
	 */
	public function get_articles( $start_date = false, $end_date = false ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )	{
			return $this->start_date;
		}

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )	{
			return $this->end_date;
		}

		add_filter( 'kbs_count_articles_where', array( $this, 'count_articles_where' ) );

		$count = wp_count_posts( KBS()->KB->post_type );

		remove_filter( 'kbs_count_articles_where', array( $this, 'count_articles_where' ) );

		return $count;

	} // get_articles

} // KBS_Article_Stats
