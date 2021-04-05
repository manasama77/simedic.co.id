<?php
/**
 * Migrate Ticket Numbers
 *
 * Switches tickets to sequential numbering.
 *
 * @subpackage  Admin/Classes/KBS_Ticket_Sources_Migration
 * @copyright   Copyright (c) 2019, KB Support
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Ticket_Sequential_Numbering_Migration Class
 *
 * @since	1.2.9
 */
class KBS_Ticket_Sequential_Numbering_Migration extends KBS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var		string
	 * @since	1.2.9
	 */
	public $export_type = '';

	/**
	 * Allows for non-ticket batch processing to be run.
	 * @since	1.2.9
	 * @var		boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since	1.2.9
	 * @var		integer
	 */
	public $per_step = 50;

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.2.9
	 * @return	array	$data     The data for the update
	 */
	public function get_data() {

		$step_items = $this->get_tickets_for_current_step();

		if ( ! is_array( $step_items ) || empty( $step_items ) ) {
			return false;
		}

        $number = intval( get_option( 'kbs_next_ticket_number' ) );
        $prefix = kbs_get_option( 'ticket_prefix' );
		$suffix = kbs_get_option( 'ticket_suffix' );

        foreach( $step_items as $ticket )	{

            // Re-add the prefix and suffix
			$ticket_number = $prefix . $number . $suffix;

			kbs_update_ticket_meta( $ticket->ID, '_kbs_ticket_number', $ticket_number );

			// Increment the ticket number
            update_option( 'kbs_last_ticket_number', $number );
			$number++;
            update_option( 'kbs_next_ticket_number', $number );
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

		$total = (int) get_option( 'kbs_update_ticket_sequential_numbering_total', 0 );

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
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {}

	/**
	 * Process a step
	 *
	 * @since	1.2.9
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
			delete_option( 'kbs_update_ticket_sequential_numbering_total' );
            delete_option( 'kbs_next_ticket_number' );
			$this->message = sprintf( __( '%s numbers updated successfully.', 'kb-support' ), kbs_get_ticket_label_singular() );
			delete_option( 'kbs_upgrade_sequential' );
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
	 * @since	1.2.9
	 * @return	void
	 */
	public function export() {

		// Set headers
		$this->headers();

		die();
	}

	/**
	 * Fetch total number of tickets that need updating
	 *
	 * @since	1.2.9
	 * @global	object	$wpdb
	 */
	public function pre_fetch() {
		global $wpdb;

		// Default count (assume no entries)
		$ticket_count = 0;

        // Count the number of entries!
		$ticket_count = $wpdb->get_var(
            "
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = 'kbs_ticket'
            "
        );

		// Temporarily save the number of rows
		update_option( 'kbs_update_ticket_sequential_numbering_total', (int) $ticket_count );

        $start = intval( kbs_get_option( 'sequential_start', 1 ) );
        $next  = get_option( 'kbs_next_ticket_number' );
        if ( ! $next )  {
            update_option( 'kbs_next_ticket_number', $start );
        }
	}

	/**
	 * Get the tickets and replies (50 based on this->per_step) for the current step
	 *
	 * @since	1.2.9
	 * @return	array
	 */
	private function get_tickets_for_current_step() {
        $args = array(
            'number' => $this->per_step,
            'page'   => $this->step,
            'status' => 'any',
            'order'  => 'ASC'
        );

        $query = new KBS_Tickets_Query( $args );
        $tickets = $query->get_tickets();

		return ! empty( $tickets ) ? $tickets : array();
	} // get_tickets_for_current_step

} // KBS_Ticket_Sequential_Numbering_Migration
