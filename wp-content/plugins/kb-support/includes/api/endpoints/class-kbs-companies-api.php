<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Companies REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Companies_API Class
 *
 * @since	1.5
 */
class KBS_Companies_API extends KBS_API {

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct( $post_type )	{
		$this->post_type = 'kbs_company';
		$obj             = get_post_type_object( $this->post_type );
		$this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	} // __construct

    /**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since	1.5
	 * @see		register_rest_route()
	 */
	public function register_routes() {
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
						'description' => __( 'Unique identifier for the object.', 'kb-support' ),
						'type'        => 'integer',
					),
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
     * Checks if a given request has access to read a company.
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
     * Checks if a given request has access to read multiple companies.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
    } // get_items_permissions_check

    /**
	 * Retrieves a single post.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request				$request	Full details about the request.
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		if ( is_post_type_viewable( get_post_type_object( $post->post_type ) ) ) {
			$response->link_header( 'alternate', get_permalink( $post->ID ), array( 'type' => 'text/html' ) );
		}

		return $response;
	} // get_item

    /**
	 * Retrieves a collection of posts.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request				$request	Full details about the request.
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		// Ensure a search string is set in case the orderby is set to 'relevance'.
		if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
			return new WP_Error(
				'rest_no_search_term_defined',
				__( 'You need to define a search term to order by relevance.', 'kb-support' ),
				array( 'status' => 400 )
			);
		}

		// Ensure an include parameter is set in case the orderby is set to 'include'.
		if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
			return new WP_Error(
				'rest_orderby_include_missing_include',
				__( 'You need to define an include parameter to order by include.', 'kb-support' ),
				array( 'status' => 400 )
			);
		}

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = array();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'exclude'        => 'post__not_in',
			'include'        => 'post__in',
			'menu_order'     => 'menu_order',
			'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'search'         => 's',
			'slug'           => 'post_name__in'
		);

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Check for & assign any parameters which require special handling or setting.
		$args['date_query'] = array();

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['before'], $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['after'], $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		if ( isset( $registered['sticky'], $request['sticky'] ) ) {
			$sticky_posts = get_option( 'sticky_posts', array() );
			if ( ! is_array( $sticky_posts ) ) {
				$sticky_posts = array();
			}
			if ( $request['sticky'] ) {
				/*
				 * As post__in will be used to only get sticky posts,
				 * we have to support the case where post__in was already
				 * specified.
				 */
				$args['post__in'] = $args['post__in'] ? array_intersect( $sticky_posts, $args['post__in'] ) : $sticky_posts;

				/*
				 * If we intersected, but there are no post ids in common,
				 * WP_Query won't return "no posts" for post__in = array()
				 * so we have to fake it a bit.
				 */
				if ( ! $args['post__in'] ) {
					$args['post__in'] = array( 0 );
				}
			} elseif ( $sticky_posts ) {
				/*
				 * As post___not_in will be used to only get posts that
				 * are not sticky, we have to support the case where post__not_in
				 * was already specified.
				 */
				$args['post__not_in'] = array_merge( $args['post__not_in'], $sticky_posts );
			}
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post collection request.
		 *
		 * @since 4.7.0
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_query/
		 *
		 * @param array           $args    Key value array of query var to query value.
		 * @param WP_REST_Request $request The request used.
		 */
		$args       = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', '__return_false' );
		}

		$posts = array();

		foreach ( $query_result as $post ) {
			if ( ! $this->check_read_permission( $post ) ) {
				continue;
			}

			$data    = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		// Reset filter.
		if ( 'edit' === $request['context'] ) {
			remove_filter( 'post_password_required', '__return_false' );
		}

		$page        = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $posts_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'kb-support' ),
				array( 'status' => 400 )
			);
		}

		$response = rest_ensure_response( $posts );

		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

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
	 * Prepares a single post output for response.
	 *
	 * @since	1.5
	 * @param	WP_Post				$post		Post object.
	 * @param	WP_REST_Request		$request	Request object.
	 * @return	WP_REST_Response	Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		$data['id'] = $post->ID;

		$data['slug'] = $post->post_name;

        $data['title'] = array();
        $data['title']['raw'] = $post->post_title;

        $data['title']['rendered'] = get_the_title( $post->ID );

		$has_password_filter = false;

        $data['content'] = array();
        $data['content']['raw'] = $post->post_content;
        $data['content']['rendered'] = post_password_required( $post ) ? '' : apply_filters( 'the_content', $post->post_content );

        $data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );


        $data['meta'] = $this->meta->get_value( $post->ID, $request );

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $post );
		$response->add_links( $links );

		if ( ! empty( $links['self']['href'] ) ) {
			$actions = $this->get_available_actions( $post, $request );

			$self = $links['self']['href'];

			foreach ( $actions as $rel ) {
				$response->add_link( $rel, $self );
			}
		}

		/**
		 * Filters the post data for a response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "kbs_rest_prepare_{$this->post_type}", $response, $post, $request );
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

		$query_params['orderby'] = array(
			'description' => __( 'Sort collection by object attribute.', 'kb-support' ),
			'type'        => 'string',
			'default'     => 'title',
			'enum'        => array(
				'customer',
				'id',
				'include',
				'title'
			),
		);

		$post_type = get_post_type_object( $this->post_type );

		/**
		 * Filter collection parameters for the companies controller.
		 *
		 * The dynamic part of the filter `$this->post_type` refers to the post
		 * type slug for the controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_Query parameter. Use the
		 * `rest_{$this->post_type}_query` filter to set WP_Query parameters.
		 *
		 * @since	1.5
		 *
		 * @param	array			$query_params	JSON Schema-formatted collection parameters.
		 * @param	WP_Post_Type	$post_type		Post type object.
		 */
		return apply_filters( "rest_{$this->post_type}_collection_params", $query_params, $post_type );
	} // get_collection_params

    /**
	 * Determines the allowed query_vars for a get_items() response and prepares
	 * them for WP_Query.
	 *
	 * @since  1.5
	 * @param  array           $prepared_args  Optional. Prepared WP_Query arguments. Default empty array.
	 * @param  WP_REST_Request $request        Optional. Full details about the request.
	 * @return array           Items query arguments.
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {
		$query_args = array();

		foreach ( $prepared_args as $key => $value ) {
			/**
			 * Filters the query_vars used in get_items() for the constructed query.
			 *
			 * The dynamic portion of the hook name, `$key`, refers to the query_var key.
			 *
			 * @since	1.5
			 * @param	string	$value	The query_var value.
			 */
			$query_args[ $key ] = apply_filters( "kbs_rest_query_var-{$key}", $value ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		$query_args['ignore_sticky_posts'] = true;

		// Map to proper WP_Query orderby param.
		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'id'            => 'ID',
				'include'       => 'post__in',
				'slug'          => 'post_name',
				'include_slugs' => 'post_name__in'
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		return $query_args;
	} // prepare_items_query

	/**
	 * Checks if a company can be read.
	 *
	 * @since	1.5
	 * @return	bool	Whether the company can be read.
	 */
	public function check_read_permission( $post )	{
		return kbs_can_view_customers( $this->user_id );
	} // check_read_permission

	/**
	 * Prepares links for the request.
	 *
	 * @since  1.5
	 * @param  WP_Post $post   Post object.
	 * @return array	Links for the given company
	 */
	protected function prepare_links( $post ) {
        $customer = get_post_meta( $post->ID, '_kbs_company_customer', true );

		if ( ! empty( $customer ) )	{
			$links['customer'] = array(
				'href'       => rest_url( 'kbs/v1/customers/' . $customer ),
				'embeddable' => true
			);
		}

		$links[ kbs_get_ticket_label_plural( true ) ] = array(
			'href'       => rest_url( 'kbs/v1/tickets/?company=' . $post->ID ),
			'embeddable' => true
		);

		return $links;
	} // prepare_links

} // KBS_Companies_API
