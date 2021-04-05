<?php
/**
 * Companies Query
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
 * KBS_Companies_Query Class
 *
 * This class is for retrieving company data
 *
 * Companies can be retrieved for date ranges and pre-defined periods
 *
 * @since	1.0
 */
class KBS_Companies_Query extends KBS_Stats {

	/**
	 * The args to pass to the kbs_get_companies() query
	 *
	 * @var		array
	 * @access	public
	 * @since	1.0
	 */
	public $args = array();

	/**
	 * The companies found based on the criteria set
	 *
	 * @var		array|false
	 * @access	public
	 * @since	1.0
	 */
	public $companies = false;

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @access	public
	 * @since	1.0
	 * @param	arr		$args	The array of arguments that can be passed in and used for setting up this company query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'           => 'companies', // Use 'posts' to get standard post objects
			'post_type'        => array( 'kbs_company' ),
			'start_date'       => false,
			'end_date'         => false,
			'number'           => 20,
			'page'             => null,
			'orderby'          => 'ID',
			'order'            => 'DESC',
			'user'             => null,
			'customer'         => null,
			'company_id'       => null,
			'status'           => 'publish',
			'meta_key'         => null,
			'year'             => null,
			'month'            => null,
			'day'              => null,
			's'                => null,
			'fields'           => null
		);

		$this->args = wp_parse_args( $args, $defaults );

		$this->init();
	} // __construct

	/**
	 * Set a query variable.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ) ) )
			$this->args[ $query_var ][] = $value;
		else
			$this->args[ $query_var ] = $value;
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
	 * Modify the query/query arguments before we retrieve companies.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function init() {
		add_action( 'kbs_pre_get_companies',  array( $this, 'date_filter_pre'  ) );
		add_action( 'kbs_post_get_companies', array( $this, 'date_filter_post' ) );

		add_action( 'kbs_pre_get_companies',  array( $this, 'orderby'    ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'status'     ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'month'      ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'per_page'   ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'page'       ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'agent'      ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'user'       ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'customer'   ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'company_id' ) );
		add_action( 'kbs_pre_get_companies',  array( $this, 'search'     ) );
	} // init

	/**
	 * Retrieve companies.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access	public
	 * @since	1.0
	 * @return	obj
	 */
	public function get_companies() {
		do_action( 'kbs_pre_get_companies', $this );

		$query = new WP_Query( $this->args );

		$custom_output = array(
			'companies',
			'kbs_company',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$company_id = get_post()->ID;
				$company    = new KBS_Company( $company_id );

				$this->companies[] = apply_filters( 'kbs_company', $company, $company_id, $this );
			}

			wp_reset_postdata();
		}

		do_action( 'kbs_post_get_companies', $this );

		return $this->companies;
	} // get_companies

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
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
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

		if( ! isset( $this->args['number'] ) ){
			return;
		}

		if ( $this->args['number'] == -1 ) {
			$this->__set( 'nopaging', true );
		}
		else{
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
		switch ( $this->args['orderby'] ) {
			default :
				$this->__set( 'orderby', $this->args['orderby'] );
			break;
		}
	} // orderby

	/**
	 * Specific User
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function user() {
		if ( is_null( $this->args['user'] ) ) {
			return;
		}

		if ( is_numeric( $this->args['user'] ) ) {
			$user_key = '_kbs_company_user_id';
		} else {
			$user_key = '_kbs_company_user_email';
		}

		$this->__set( 'meta_query', array(
			'key'   => $user_key,
			'value' => $this->args['user']
		) );
	}

	/**
	 * Specific customer id
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function customer() {
		if ( is_null( $this->args['customer'] ) || ! is_numeric( $this->args['customer'] ) ) {
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_kbs_company_customer_id',
			'value' => (int) $this->args['customer'],
		) );
	} // customer

	/**
	 * Specific company ids
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function company_id() {
		if ( is_null( $this->args['company_id'] ) ) {
			return;
		}

		$company_ids = $this->args['company_id'];
		if ( ! is_array( $company_ids ) )	{
			$company_ids = array( $company_ids );
		}
		$company_ids = array_map( 'absint', $company_ids );

		$this->__set( 'post__in', $company_ids );
	} // company_id

	/**
	 * Search
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function search() {

		if( ! isset( $this->args['s'] ) ) {
			return;
		}

		$search = trim( $this->args['s'] );

		if( empty( $search ) ) {
			return;
		}

		$is_email = is_email( $search ) || strpos( $search, '@' ) !== false;
		$is_user  = strpos( $search, strtolower( 'user:' ) ) !== false;

		if ( $is_email ) {

			$user_data = get_user_by( 'email', $search );

			if ( $user_data )	{
				$search = $user_data->ID;
			}

			$key = '_kbs_company_client';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if( is_object( $post ) && $post->post_type == 'kbs_company' ) {

				$arr   = array();
				$arr[] = $search;
				$this->__set( 'post__in', $arr );
				$this->__unset( 's' );
			}

		} elseif ( '#' == substr( $search, 0, 1 ) ) {

			$search = str_replace( '#:', '', $search );
			$search = str_replace( '#', '', $search );
			$this->__set( 'event', $search );
			$this->__unset( 's' );

		} else {
			$this->__set( 's', $search );
		}

	} // search

	/**
	 * Agent
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function agent() {
		if ( empty( $this->args['agent'] ) ) {
			return;
		}

		$query = array(
			'key'     => '_kbs_company_agent_id',
			'value'   => $this->args['agent']
		);

		$this->__set( 'meta_query', $query );
	} // agent

} // KBS_Companies_Query
