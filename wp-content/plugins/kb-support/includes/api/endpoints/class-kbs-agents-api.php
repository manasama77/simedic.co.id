<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Agents REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Customers_API Class
 *
 * @since	1.5
 */
class KBS_Agents_API extends KBS_API {
	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
		$this->rest_base = 'agents';

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	} // __construct

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since	1.5
	 * @see		register_rest_route()
	 */
    public function register_routes()    {
        register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				)
			)
		);

		register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'type'        => 'integer',
						'description' => __( 'Unique identifier for the %s.', 'kb-support' )
					)
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' )
				)
			)
		);
    } // register_routes

	/**
     * Checks if a given request has access to read an agent.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
		if ( ! $this->is_authenticated() )	{
			return new WP_Error(
				'rest_forbidden_context',
				$this->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return kbs_is_ticket_admin( $this->user_id );
    } // get_item_permissions_check

    /**
     * Checks if a given request has access to read agents.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
    } // get_items_permissions_check

	/**
	 * Retrieves a single agent.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$agent = $this->get_agent( $request['id'] );

        if ( is_wp_error( $agent ) ) {
			return $agent;
		}

		$data     = $this->prepare_item_for_response( $agent, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // get_item

	/**
	 * Retrieves a collection of customers.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request		$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error		Response object on success, or WP_Error object on failure
	 */
	function get_items( $request )	{
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_User_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'exclude'  => 'exclude',
			'include'  => 'include',
			'order'    => 'order',
			'per_page' => 'number',
			'search'   => 'search',
			'roles'    => 'role__in',
			'slug'     => 'nicename__in',
		);

		$prepared_args = array();

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $prepared_args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$prepared_args[ $wp_param ] = $request[ $api_param ];
			}
		}

		if ( isset( $registered['offset'] ) && ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
		}

		if ( isset( $registered['orderby'] ) ) {
			$orderby_possibles = array(
				'id'              => 'ID',
				'include'         => 'include',
				'name'            => 'display_name',
				'registered_date' => 'registered',
				'slug'            => 'user_nicename',
				'include_slugs'   => 'nicename__in',
				'email'           => 'user_email',
				'url'             => 'user_url',
			);
			$prepared_args['orderby'] = $orderby_possibles[ $request['orderby'] ];
		}

        if ( ! empty( $prepared_args['search'] ) ) {
			$prepared_args['search'] = '*' . $prepared_args['search'] . '*';
		}

		/**
		 * Filters WP_User_Query arguments when querying users via the REST API.
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_user_query/
		 *
		 * @since 4.7.0
		 *
		 * @param array           $prepared_args Array of arguments for WP_User_Query.
		 * @param WP_REST_Request $request       The current request.
		 */
		$prepared_args = apply_filters( 'kbs_rest_agents_query', $prepared_args, $request );

        $roles = kbs_get_agent_user_roles();

        $prepared_args['role__in'] = $roles;

		$query = new WP_User_Query( $prepared_args );

		$users = array();

		foreach ( $query->results as $user ) {
			$data    = $this->prepare_item_for_response( $user, $request );
			$users[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $users );

		// Store pagination values for headers then unset for count query.
		$per_page = (int) $prepared_args['number'];
		$page     = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

		$prepared_args['fields'] = 'ID';

		$total_users = $query->get_total();

		if ( $total_users < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $prepared_args['number'], $prepared_args['offset'] );
			$count_query = new WP_User_Query( $prepared_args );
			$total_users = $count_query->get_total();
		}

		$response->header( 'X-WP-Total', (int) $total_users );

		$max_pages = ceil( $total_users / $per_page );

		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base = add_query_arg( urlencode_deep( $request->get_query_params() ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );
		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	} // get_items

    /**
	 * Get the agent, if the ID is valid.
	 *
	 * @since  1.5
	 *
	 * @param  int     $id         Supplied ID.
	 * @return WP_User|WP_Error    True if ID is valid, WP_Error otherwise.
	 */
	protected function get_agent( $id ) {
		$error = new WP_Error(
			'rest_agent_invalid_id',
			__( 'Invalid agent ID.', 'kb-support' ),
			array( 'status' => 404 )
		);

		if ( (int) $id <= 0 || ! kbs_is_agent( $id ) ) {
			return $error;
		}

		$agent = get_userdata( (int) $id );
		if ( empty( $agent ) || ! $agent->exists() ) {
			return $error;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $agent->ID ) ) {
			return $error;
		}

		return $agent;
	} // get_agent

    /**
	 * Prepares a single agent output for response.
	 *
	 * @since  1.5
	 *
	 * @param  WP_User         $user       User object.
	 * @param  WP_REST_Request $request    Request object.
	 * @return WP_REST_Response            Response object.
	 */
	public function prepare_item_for_response( $user, $request ) {
		$data   = array(
            'id'         => $user->ID,
            'name'       => $user->display_name,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->user_email
            
        );

		$context = ! empty( $request['context'] ) ? $request['context'] : 'embed';
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $user ) );

		/**
		 * Filters user data returned from the REST API.
		 *
		 * @since 1.5
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_User          $user     User object used to create response.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'rest_kbs_prepare_agent', $response, $user, $request );
	} // prepare_item_for_response

	/**
	 * Prepares links for the agent request.
	 *
	 * @since  4.7.0
	 *
	 * @param  WP_Post $user   User object.
	 * @return array   Links for the given user.
	 */
	protected function prepare_links( $user ) {
        $base = sprintf( '%s/%s', $this->namespace . $this->version, $this->rest_base );
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $user->ID ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		return $links;
	} // prepare_links

} // KBS_Agents_API

new KBS_Agents_API();
