<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Customers REST API
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
class KBS_Customers_API extends KBS_API {

	/**
	 * Customer ID
	 *
	 * @since	1.5
	 * @var		int
	 */
	protected $customer_id = 0;

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
		$this->rest_base = 'customers';

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	} // __construct

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since	4.5
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
     * Checks if a given request has access to read a customer.
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

		return kbs_can_view_customers( $this->user_id );
    } // get_item_permissions_check

    /**
     * Checks if a given request has access to read customers.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
    } // get_items_permissions_check

	/**
	 * Retrieves a single customer.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$customer = new KBS_Customer( $request['id'] );

		if ( ! $this->check_read_permission( $customer ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				$this->errors( 'no_permission' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$data     = $this->prepare_item_for_response( $customer, $request );
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
		 * values are accepted as-passed, and their internal KBS_Customer_DB parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'exclude'  => 'exclude_id',
			'include'  => 'include_id',
			'company'  => 'company_id',
			'order'    => 'order',
			'per_page' => 'number'
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

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a customer collection request.
		 *
		 * @since	1.5
		 * @param	array			$prepared_args	Key value array of query var to query value
		 * @param	WP_REST_Request	$request		The request used
		 */
		$prepared_args = apply_filters( "rest_kbs_customer_query", $prepared_args, $request );

		$query         = new KBS_DB_Customers( $prepared_args );
		$customers     = array();
		$query_result  = $query->get_customers();

		foreach ( $query_result as $_customer ) {
			if ( ! $this->check_read_permission( $_customer ) ) {
				continue;
			}

			$customer    = new KBS_Customer( $_customer->id );
			$data        = $this->prepare_item_for_response( $customer, $request );
			$customers[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $customers );

		return $response;
	} // get_items

	/**
	 * Prepares a single customer output for response.
	 *
	 * @since	1.5
	 * @param	KBS_Customer		$customer	KBS_Customer object
	 * @param	WP_REST_Request		$request	Request object
	 * @return	WP_REST_Response	Response object
	 */
	public function prepare_item_for_response( $customer, $request )	{
		$data = array();

		$data['id'] = $customer->id;

		if ( ! empty( $customer->name ) )	{
			$data['name'] = $customer->name;
		}

		if ( ! empty( $customer->user_id ) )	{
			$data['user_id'] = $customer->user_id;
		}

		if ( ! empty( $customer->email ) )	{
			$data['email'] = $customer->email;
		}

		if ( ! empty( $customer->emails ) )	{
			$data['additional_emails'] = array();

			foreach ( $customer->emails as $email ) {
				if ( $customer->email === $email ) {
					continue;
				}

				$data['additional_emails'][] = $email;
			}
		}

		if ( ! empty( $customer->primary_phone ) )	{
			$data['phone']   = array();
			$data['phone']['primary'] = $customer->primary_phone;

			if ( ! empty( $customer->additional_phone ) )	{
				$data['phone']['additional'] = $customer->additional_phone;
			}
		}

		if ( ! empty( $customer->website ) )	{
			$data['website'] = $customer->website;
		}

		$address = $customer->get_meta( 'address', true );
		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'country' => '',
			'zip'     => ''
		);

		$address     = wp_parse_args( $address, $defaults );
		$has_address = false;

		foreach ( $address as $address_field )	{
			if ( ! empty( $address_field ) )	{
				$has_address = true;
			}
		}

		if ( $has_address )	{
			$data['address'] = $address;
		}

		if ( ! empty( $customer->company ) )	{
			$data['company'] = $customer->company_id;
		}

		if ( ! empty( $customer->date_created ) )	{
			$data['date_created'] = $customer->date_created;
		}

		if ( ! empty( $customer->notes ) && is_array( $customer->notes ) )	{
			$data['notes'] = array();

			foreach( $customer->notes as $note )	{
				$data['notes'][] = stripslashes( $note );
			}
		}

		$data['ticket_count'] = kbs_get_customer_ticket_count( $customer );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		$links    = $this->prepare_links( $customer );

		$response->add_links( $links );

		/**
		 * Filters the customer data for a response.
		 *
		 * @since	1.5
		 *
		 * @param WP_REST_Response	$response	The response object
		 * @param KBS_Customer		$customer	Customer object
		 * @param WP_REST_Request	$request	Request object
		 */
		return apply_filters( "rest_prepare_kbs_customer", $response, $customer, $request );
	} // prepare_item_for_response

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since	1.5
	 * @return	array	Collection parameters
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['id'] = array(
			'description' => __( 'Only include specific IDs in the result set.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer'
			)
		);

		$query_params['name'] = array(
			'description' => __( 'Only include specific customer names in the result set.', 'kb-support' ),
			'type'        => 'string'
		);

		$query_params['number'] = array(
			'description' => __( 'Number of results to return.', 'kb-support' ),
			'type'        => 'integer',
			'default'     => '20'
		);

		$query_params['exclude_id'] = array(
			'description' => __( 'Ensure result set excludes specific IDs.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);

		$query_params['user_id'] = array(
			'description' => __( 'Only include specific User IDs in the result set.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer'
			)
		);

		$query_params['company'] = array(
			'description' => __( 'Only include users from specific Company IDs in the result set.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer'
			)
		);

		$query_params['include'] = array(
			'description' => __( 'Limit result set to specific IDs.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);

		$query_params['offset'] = array(
			'description' => __( 'Offset the result set by a specific number of items.', 'kb-support' ),
			'type'        => 'integer',
		);

		$query_params['order'] = array(
			'default'     => 'desc',
			'description' => __( 'Order sort attribute ascending or descending.', 'kb-support' ),
			'enum'        => array( 'asc', 'desc' ),
			'type'        => 'string',
		);

		$query_params['orderby'] = array(
			'default'     => 'id',
			'description' => __( 'Sort collection by object attribute.', 'kb-support' ),
			'enum'        => array(
				'id',
				'user_id',
				'name',
				'email',
				'company_id',
				'date'
			),
			'type'        => 'string',
		);

		/**
		 * Filter collection parameters for the customers controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal KBS_DB_Customers parameter.
		 *
		 * @since	1.5
		 * @param	array	$query_params	JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'rest_kbs_customer_collection_params', $query_params );
	} // get_collection_params

	/**
	 * Checks if a customer can be accessed.
	 *
	 * @since	1.5
	 * @param	object	KBS_Customer object
	 * @return	bool	Whether the post can be read.
	 */
	public function check_read_permission( $customer )	{
		return kbs_can_view_customers( $this->user_id );
	} // check_read_permission

	/**
	 * Prepares links for the request.
	 *
	 * @since	1.5
	 * @param	KBS_Customer	$customer	KBS Customer object
	 * @return	array			Links for the given post
	 */
	protected function prepare_links( $customer ) {
		$base = sprintf( '%s/%s', $this->namespace . $this->version, $this->rest_base );

		// Entity meta.
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $customer->id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			)
		);

		if ( ! empty( $customer->user_id ) )	{
			$links['user'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $customer->user_id ),
				'embeddable' => true
			);
		}

		if ( ! empty( $customer->company_id ) )	{
			$links['company'] = array(
				'href'       => rest_url( 'kbs/v1/companies/' . $customer->company_id ),
				'embeddable' => true
			);
		}

		$links[ kbs_get_ticket_label_plural( true ) ] = array(
			'href'       => rest_url( 'kbs/v1/tickets/?customer=' . $customer->id ),
			'embeddable' => true
		);

		return $links;
	} // prepare_links

} // KBS_Customers_API

new KBS_Customers_API();
