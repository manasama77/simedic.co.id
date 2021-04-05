<?php
/**
 * Roles and Capabilities
 *
 * @package     KBS
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 * These roles let us have Support Agents, Support Customers, etc, each of whom can do
 * certain things within KBS Support
 *
 * @since	1.0
 */
class KBS_Roles {

	/**
	 * Get things going
	 *
	 * @since	1.0
	 */
	public function __construct() {
		add_filter( 'map_meta_cap', array( $this, 'meta_caps' ), 10, 4 );
	} // __construct

	/**
	 * Add new support roles with default WP caps
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function add_roles() {
		add_role( 'support_manager', __( 'Support Manager', 'kb-support' ), array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'delete_others_pages'    => true,
			'delete_others_posts'    => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true
		) );

		add_role( 'support_agent', __( 'Support Agent', 'kb-support' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'upload_files'           => true,
			'delete_posts'           => false
		) );

		add_role( 'support_customer', __( 'Support Customer', 'kb-support' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'upload_files'           => false,
			'delete_posts'           => false
		) );
	}

	/**
	 * Add new support-specific capabilities
	 *
	 * @access	public
	 * @since	1.0
	 * @global	WP_Roles $wp_roles
	 * @return	void
	 */
	public function add_caps() {

		global $wp_roles;

		if ( class_exists('WP_Roles') ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'support_manager', 'view_ticket_reports' );
			$wp_roles->add_cap( 'support_manager', 'view_ticket_sensitive_data' );
			$wp_roles->add_cap( 'support_manager', 'export_ticket_reports' );
			$wp_roles->add_cap( 'support_manager', 'manage_ticket_settings' );

			$wp_roles->add_cap( 'administrator', 'view_ticket_reports' );
			$wp_roles->add_cap( 'administrator', 'view_ticket_sensitive_data' );
			$wp_roles->add_cap( 'administrator', 'export_ticket_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_ticket_settings' );

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {

					$ignore_for_agents = array(
						'manage_ticket_terms',
						'edit_ticket_terms',
						'delete_ticket_terms',
					);

					if ( ! in_array( $cap, $ignore_for_agents ) )	{
						$wp_roles->add_cap( 'support_agent', $cap );
					}

					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'support_manager', $cap );

				}
			}

			// Submission form capabilities
			$wp_roles->add_cap( 'administrator', 'edit_submission_form' );
			$wp_roles->add_cap( 'administrator', 'read_submission_form' );
			$wp_roles->add_cap( 'administrator', 'delete_submission_form' );
			$wp_roles->add_cap( 'administrator', 'edit_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'edit_others_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'publish_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'read_private_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'delete_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'delete_private_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'delete_published_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'delete_others_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'edit_private_submission_forms' );
			$wp_roles->add_cap( 'administrator', 'edit_published_submission_forms' );

			$wp_roles->add_cap( 'support_manager', 'edit_submission_form' );
			$wp_roles->add_cap( 'support_manager', 'read_submission_form' );
			$wp_roles->add_cap( 'support_manager', 'delete_submission_form' );
			$wp_roles->add_cap( 'support_manager', 'edit_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'edit_others_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'publish_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'read_private_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'delete_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'delete_private_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'delete_published_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'delete_others_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'edit_private_submission_forms' );
			$wp_roles->add_cap( 'support_manager', 'edit_published_submission_forms' );

		}

	} // add_caps

	/**
	 * Gets the core post type capabilities
	 *
	 * @access	public
	 * @since	1.0
	 * @return	arr		$capabilities	Core post type capabilities
	 */
	public function get_core_caps() {

		$capabilities = array();

		$capability_types = array( 'ticket', 'article', 'customer' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// Custom
				"view_{$capability_type}_stats"
			);
		}

		return $capabilities;

	} // get_core_caps

	/**
	 * Map meta caps to primitive caps
	 *
	 * @access	public
	 * @since	1.0
	 * @return	arr		$caps
	 */
	public function meta_caps( $caps, $cap, $user_id, $args ) {

		switch( $cap ) {

			case 'view_ticket_stats' :

				if( empty( $args[0] ) ) {
					break;
				}

				$ticket = get_post( $args[0] );
				if ( empty( $ticket ) ) {
					break;
				}

				if( user_can( $user_id, 'view_ticket_reports' ) || $user_id == $ticket->post_author ) {
					$caps = array();
				}

				break;
		}

		return $caps;

	} // meta_caps

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function remove_caps() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			/** Support Manager Capabilities */
			$wp_roles->remove_cap( 'support_manager', 'view_ticket_reports' );
			$wp_roles->remove_cap( 'support_manager', 'view_ticket_sensitive_data' );
			$wp_roles->remove_cap( 'support_manager', 'export_ticket_reports' );
			$wp_roles->remove_cap( 'support_manager', 'manage_ticket_settings' );

			/** Site Administrator Capabilities */
			$wp_roles->remove_cap( 'administrator', 'view_ticket_reports' );
			$wp_roles->remove_cap( 'administrator', 'view_ticket_sensitive_data' );
			$wp_roles->remove_cap( 'administrator', 'export_ticket_reports' );
			$wp_roles->remove_cap( 'administrator', 'manage_ticket_settings' );

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'support_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
					$wp_roles->remove_cap( 'support_agent', $cap );
				}
			}

			// Submission form capabilities
			$wp_roles->remove_cap( 'administrator', 'edit_submission_form' );
			$wp_roles->remove_cap( 'administrator', 'read_submission_form' );
			$wp_roles->remove_cap( 'administrator', 'delete_submission_form' );
			$wp_roles->remove_cap( 'administrator', 'edit_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'edit_others_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'publish_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'read_private_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'delete_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'delete_private_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'delete_published_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'delete_others_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'edit_private_submission_forms' );
			$wp_roles->remove_cap( 'administrator', 'edit_published_submission_forms' );

			$wp_roles->remove_cap( 'support_manager', 'edit_submission_form' );
			$wp_roles->remove_cap( 'support_manager', 'read_submission_form' );
			$wp_roles->remove_cap( 'support_manager', 'delete_submission_form' );
			$wp_roles->remove_cap( 'support_manager', 'edit_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'edit_others_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'publish_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'read_private_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'delete_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'delete_private_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'delete_published_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'delete_others_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'edit_private_submission_forms' );
			$wp_roles->remove_cap( 'support_manager', 'edit_published_submission_forms' );

		}

	} // remove_caps

} // KBS_Roles
