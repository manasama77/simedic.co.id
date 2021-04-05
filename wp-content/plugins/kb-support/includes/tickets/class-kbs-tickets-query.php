<?php
/**
 * Tickets Query
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
 * KBS_Tickets_Query Class
 *
 * This class is for retrieving ticket data
 *
 * Tickets can be retrieved for date ranges and pre-defined periods
 *
 * @since	1.0
 */
class KBS_Tickets_Query extends KBS_Stats {

	/**
	 * The args to pass to the kbs_get_tickets() query
	 *
	 * @var		array
	 * @access	public
	 * @since	1.0
	 */
	public $args = array();

	/**
	 * The tickets found based on the criteria set
	 *
	 * @var		array|false
	 * @access	public
	 * @since	1.0
	 */
	public $tickets = false;

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @access	public
	 * @since	1.0
	 * @param	arr		$args	The array of arguments that can be passed in and used for setting up this ticket query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'           => 'tickets', // Use 'posts' to get standard post objects
			'post_type'        => array( 'kbs_ticket' ),
			'start_date'       => false,
			'end_date'         => false,
			'number'           => 20,
			'page'             => null,
			'ticket_ids'       => null,
			'orderby'          => 'ID',
			'order'            => 'DESC',
			'user'             => null,
			'customer'         => null,
			'company'          => null,
            'agent'            => null,
			'agents'           => null,
			'key'              => null,
			'status'           => kbs_get_ticket_status_keys( false ),
			'meta_key'         => null,
			'year'             => null,
			'month'            => null,
			'day'              => null,
			's'                => null,
			'search_in_notes'  => false,
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
	 * Modify the query/query arguments before we retrieve tickets.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function init() {
		add_action( 'kbs_pre_get_tickets',  array( $this, 'date_filter_pre'  ) );
		add_action( 'kbs_post_get_tickets', array( $this, 'date_filter_post' ) );

		add_action( 'kbs_pre_get_tickets',  array( $this, 'orderby'    ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'status'     ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'month'      ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'per_page'   ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'page'       ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'ticket_ids' ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'agent'      ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'user'       ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'customer'   ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'company'    ) );
        add_action( 'kbs_pre_get_tickets',  array( $this, 'agent'      ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'key'        ) );
		add_action( 'kbs_pre_get_tickets',  array( $this, 'search'     ) );
	} // init

	/**
	 * Retrieve tickets.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access	public
	 * @since	1.0
	 * @return	obj
	 */
	public function get_tickets() {
		do_action( 'kbs_pre_get_tickets', $this );

		$query = new WP_Query( $this->args );

		$custom_output = array(
			'tickets',
			'kbs_ticket',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$ticket_id = get_post()->ID;
				$ticket    = new KBS_Ticket( $ticket_id );

				$this->tickets[] = apply_filters( 'kbs_ticket', $ticket, $ticket_id, $this );
			}

			wp_reset_postdata();
		}

		do_action( 'kbs_post_get_tickets', $this );

		return $this->tickets;
	} // get_tickets

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
			$user_key = '_kbs_ticket_user_id';
		} else {
			$user_key = '_kbs_ticket_user_email';
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
			'key'   => '_kbs_ticket_customer_id',
			'value' => (int) $this->args['customer'],
		) );
	} // customer

	/**
	 * Specific company id
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function company() {
		if ( is_null( $this->args['company'] ) || ! is_numeric( $this->args['company'] ) ) {
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_kbs_ticket_company_id',
			'value' => (int) $this->args['company'],
		) );
	} // company

	/**
	 * Specific ticket key
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function key() {
		if ( is_null( $this->args['key'] ) ) {
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_kbs_ticket_key',
			'value' => $this->args['key'],
		) );
	} // key

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

		if ( $is_email || strlen( $search ) == 32 ) {

			$key = $is_email ? '_kbs_ticket_user_email' : '_kbs_ticket_key';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => 'LIKE'
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( $is_user )	{

			$search_meta = array(
				'key'   => '_kbs_ticket_user_id',
				'value' => trim( str_replace( 'user:', '', strtolower( $search ) ) )
			);

			$this->__set( 'meta_query', $search_meta );

			if ( kbs_use_sequential_ticket_numbers() )	{

				$search_meta = array(
					'key'     => '_kbs_ticket_number',
					'value'   => $search,
					'compare' => 'LIKE'
				);

				$this->__set( 'meta_query', $search_meta );

				$this->args['meta_query']['relation'] = 'OR';

			}

			$this->__unset( 's' );

		} elseif (
			kbs_use_sequential_ticket_numbers() &&
			(
				false !== strpos( $search, kbs_get_option( 'ticket_prefix' ) ) ||
				false !== strpos( $search, kbs_get_option( 'ticket_suffix' ) )
			)
		) {

			$search_meta = array(
				'key'     => '_kbs_ticket_number',
				'value'   => $search,
				'compare' => 'LIKE'
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if ( is_object( $post ) && $post->post_type == 'kbs_ticket' )	{

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
	 * Ticket IDs
	 *
	 * @access	public
	 * @since	1.5
	 * @return	void
	 */
	public function ticket_ids() {
		if ( empty( $this->args['ticket_ids'] ) ) {
			return;
		}

		if ( ! is_array( $this->args['ticket_ids'] ) )	{
			$this->args['ticket_ids'] = array( $this->args['ticket_ids'] );
		}

		$query = array_map( 'absint', $this->args['ticket_ids'] );

		$this->__set( 'post__in', $query );
		unset( $this->args['ticket_ids'] );
	} // agent

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
			'key'     => '_kbs_ticket_agent_id',
			'value'   => $this->args['agent']
		);

		$this->__set( 'meta_query', $query );
	} // agent

} // KBS_Tickets_Query
