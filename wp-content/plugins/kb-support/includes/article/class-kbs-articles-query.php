<?php
/**
 * KB Articles Query
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
 * KBS_Articles_Query Class
 *
 * This class is for retrieving KB Article data
 *
 * Articles can be retrieved for date ranges and pre-defined periods
 *
 * @since	1.0
 */
class KBS_Articles_Query extends KBS_Stats {

	/**
	 * The args to pass to the kbs_get_articles() query
	 *
	 * @var		array
	 * @access	public
	 * @since	1.0
	 */
	public $args = array();

	/**
	 * The articles found based on the criteria set
	 *
	 * @var		arr|false
	 * @access	public
	 * @since	1.0
	 */
	public $articles = 0;

	/**
	 * The count of articles found based on the criteria set
	 *
	 * @var		int
	 * @access	public
	 * @since	1.0
	 */
	public $total_articles = 0;

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @access	public
	 * @since	1.0
	 * @param	arr		$args	The array of arguments that can be passed in and used for setting up this article query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'post_type'  => KBS()->KB->post_type,
			'articles'   => null,
			'start_date' => false,
			'end_date'   => false,
			'author'     => null,
			'restricted' => null,
			'number'     => 20,
			'page'       => null,
			'orderby'    => 'views',
			'order'      => 'DESC',
			'status'     => 'publish',
			'meta_key'   => null,
			'year'       => null,
			'month'      => null,
			'day'        => null,
			's'          => null,
			'fields'     => null
		);

		$this->args = wp_parse_args( $args, $defaults );
		$this->args = apply_filters( 'kbs_get_articles_args', $this->args );

		$this->init();
	} // __construct

	/**
	 * Set a query variable.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ) ) )	{
			$this->args[ $query_var ][] = $value;
		} else	{
			$this->args[ $query_var ] = $value;
		}
	} // __set

	/**
	 * Unset a query variable.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function __unset( $query_var ) {
		unset( $this->args[ $query_var ] );
	} // __unset

	/**
	 * Modify the query/query arguments before we retrieve articles.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function init() {
		add_action( 'kbs_pre_get_articles',  array( $this, 'date_filter_pre'  ) );
		add_action( 'kbs_post_get_articles', array( $this, 'date_filter_post' ) );

		add_action( 'kbs_pre_get_articles',  array( $this, 'orderby'    ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'status'     ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'month'      ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'per_page'   ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'page'       ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'articles'   ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'restricted' ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'author'     ) );
		add_action( 'kbs_pre_get_articles',  array( $this, 'search'     ) );
	} // init

	/**
	 * Retrieve articles.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access	public
	 * @since	1.0
	 * @return	obj
	 */
	public function get_articles() {
		do_action( 'kbs_pre_get_articles', $this );

		$query = new WP_Query( $this->args );

		$this->total_articles = (int) $query->found_posts;
		$this->articles       = $query->posts;

		do_action( 'kbs_post_get_articles', $this );

		return $this->articles;
	} // get_articles

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function date_filter_pre() {
		if( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		$this->setup_dates( $this->args['start_date'], $this->args['end_date'] );

		add_filter( 'posts_where', array( $this, 'posts_where' ) );
	} // date_filter_pre

	/**
	 * If querying a specific date, remove filters after the query has been run
	 * to avoid affecting future queries.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function date_filter_post() {
		if ( ! $this->args['start_date'] || ! $this->args['end_date'] ) {
			return;
		}

		remove_filter( 'posts_where', array( $this, 'posts_where' ) );
	} // date_filter_post

	/**
	 * Post Status
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function status() {
		if ( ! isset ( $this->args['status'] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args['status'] );
		$this->__unset( 'status' );
	} // status

	/**
	 * Current Page
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function page() {
		if ( ! isset ( $this->args['page'] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args['page'] );
		$this->__unset( 'page' );
	} // page

	/**
	 * Posts Per Page
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function per_page() {

		if ( ! isset( $this->args['number'] ) ){
			return;
		}

		if ( $this->args['number'] == -1 ) {
			$this->__set( 'nopaging', true );
		} else {
			$this->__set( 'posts_per_page', $this->args['number'] );
		}

		$this->__unset( 'number' );
	} // per_page

	/**
	 * Current Month
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function month() {
		if ( ! isset ( $this->args['month'] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args['month'] );
		$this->__unset( 'month' );
	} // month

	/**
	 * Order by
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function orderby() {
		$total_views_key = kbs_get_article_view_count_meta_key_name();
		$month_views_key = kbs_get_article_view_count_meta_key_name( false );

		switch ( $this->args['orderby'] ) {
			case 'views':
			default:
				$this->__set( 'meta_key', $total_views_key );
				$this->__set( 'orderby', 'meta_value_num' );
				break;

			case 'views_month':
				$this->__set( 'meta_key', $month_views_key );
				$this->__set( 'orderby', 'meta_value_num' );
				break;

			case 'author':
			case 'date':
			case 'modified':
			case 'name':
			case 'rand':
			case 'relevance':
			case 'title':
				$this->__set( 'orderby', $this->args['orderby'] );
				break;
		}
	} // orderby

	/**
	 * Articles
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function articles() {
		if ( ! isset ( $this->args['articles'] ) ) {
			return;
		}

		if ( ! is_array( $this->args['articles'] ) )	{
			$this->args['articles'] = explode( ',', $this->args['articles'] );
		}

		$this->__set( 'post__in', $this->args['articles'] );
		$this->__unset( 'articles' );
	} // articles

	/**
	 * Restricted
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function restricted() {
		if ( ! isset ( $this->args['restricted'] ) ) {
			return;
		}

		$query = array(
			'key'     => '_kbs_article_restricted',
			'value'   => '1'
		);

		$this->__set( 'meta_query', $query );
		$this->__unset( 'restricted' );
	} // restricted

	/**
	 * Author
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function author() {
		if ( ! isset ( $this->args['author'] ) ) {
			return;
		}

		$this->__unset( 'author' );
	} // author

	/**
	 * Search
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function search() {

		if ( ! isset( $this->args['s'] ) ) {
			return;
		}

		$search = trim( $this->args['s'] );

		if ( empty( $search ) ) {
			return;
		}

		if ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if ( is_object( $post ) && $post->post_type == KBS()->KB->post_type ) {

				$arr   = array();
				$arr[] = $search;
				$this->__set( 'post__in', $arr );
				$this->__unset( 's' );
			}

		} else {
			$this->__set( 's', $search );
		}

	} // search

	/**
	 * Order posts by relevance.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function orderby_relevance()	{
		if ( empty( $this->posts ) )	{
			
		}
	} // orderby_relevance

} // KBS_Articles_Query
