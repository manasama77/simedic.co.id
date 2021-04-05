<?php
/**
 * Migrate Ticket Departments
 *
 * Determines if a ticket is assigned to a department and adds new
 * meta keys if required.
 *
 * @subpackage  Admin/Classes/KBS_Ticket_Department_Migration
 * @copyright   Copyright (c) 2019, KB Support
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Ticket_Department_Migration Class
 *
 * @since	1.3
 */
class KBS_Ticket_Department_Migration extends KBS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var		string
	 * @since	1.3
	 */
	public $export_type = '';

	/**
	 * Allows for non-ticket batch processing to be run.
	 * @since	1.3
	 * @var		boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since	1.3
	 * @var		integer
	 */
	public $per_step = 50;

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.3
	 * @return	array	$data The data for the update
	 */
	public function get_data() {

		$step_items = $this->get_tickets_for_current_step();

		if ( ! is_array( $step_items ) || empty( $step_items ) ) {
			return false;
		}

        foreach( $step_items as $ticket )	{

            // Get the department term ID
			$department_id = false;
			$department    = kbs_get_department_for_ticket( $ticket->ID );

			if ( ! empty( $department ) && ! empty( $department->term_id ) )	{
				$department_id = (int) $department->term_id;
			}

			add_post_meta( $ticket->ID, '_kbs_ticket_department', $department_id, true );
		}

		return true;
	} // get_data

	/**
	 * Return the calculated completion percentage
	 *
	 * @since	1.2.9
	 * @return	int
	 */
	public function get_percentage_complete() {

		$total = (int) get_option( 'kbs_update_ticket_department_total', 0 );

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->step * $this->per_step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	} // get_percentage_complete

	/**
	 * Set the properties specific to the tickets export
	 *
	 * @since	1.2.9
	 * @param  array   $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {}

	/**
	 * Process a step
	 *
	 * @since	1.3
	 * @return	bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die(
				__( 'You do not have permission to run this upgrade.', 'kb-support' ),
				__( 'Error', 'kb-support' ),
				array( 'response' => 403 )
            );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->done = true;
			delete_option( 'kbs_update_ticket_department_total' );
			$this->message = sprintf( __( '%s departments updated successfully.', 'kb-support' ), kbs_get_ticket_label_singular() );
			kbs_set_upgrade_complete( 'upgrade_ticket_departments' );
			return false;
		}
	} // process_step

	public function headers() {
		ignore_user_abort( true );

		if ( ! kbs_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
	} // headers

	/**
	 * Perform the export
	 *
	 * @access	public
	 * @since	1.3
	 * @return	void
	 */
	public function export() {

		// Set headers
		$this->headers();

		die();
	}

	/**
	 * Fetch total number of tickets needing migration
	 *
	 * @since	1.3
	 * @global	object	$wpdb
	 */
	public function pre_fetch() {
		global $wpdb;

		// Default count (assume no entries)
		$ticket_count = 0;

        // Count the number of entries!
		$args = array(
			'post_type'      => 'kbs_ticket',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'department',
					'operator' => 'EXISTS'
				),
			),
			'order'          => 'ASC'
		);

		$query = new WP_Query( $args );
		$ticket_count = count( $query->posts );

		// Temporarily save the number of rows
		update_option( 'kbs_update_ticket_department_total', (int) $ticket_count );
	}

	/**
	 * Get the tickets (50 based on this->per_step) for the current step
	 *
	 * @since	1.3
	 * @return	array
	 */
	private function get_tickets_for_current_step() {
		$args = array(
			'post_type'      => 'kbs_ticket',
			'post_status'    => 'any',
			'paged'          => $this->step,
			'posts_per_page' => $this->per_step,
			'tax_query'      => array(
				array(
					'taxonomy' => 'department',
					'operator' => 'EXISTS'
				),
			),
			'order'          => 'ASC'
		);

		$query   = new WP_Query( $args );
        $tickets = $query->posts;

		return ! empty( $tickets ) ? $tickets : array();
	} // get_tickets_for_current_step

} // KBS_Ticket_Department_Migration
