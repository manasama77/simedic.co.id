<?php
/**
 * Migrate Ticket Sources
 *
 * Switches from meta keys to taxonomies for recording the source by which a
 * ticket and reply was logged.
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
 * KBS_Ticket_Sources_Migration Class
 *
 * @since	1.2.9
 */
class KBS_Ticket_Sources_Migration extends KBS_Batch_Export {

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
	 * @return	array	$data The data for the update
	 */
	public function get_data() {

		$step_items = $this->get_tickets_for_current_step();

		if ( ! is_array( $step_items ) || empty( $step_items ) ) {
			return false;
		}

        $sources = array(
            1  => array(
                'slug' => 'kbs-website',
                'name' => __( 'Website', 'kb-support' ),
                'desc' => sprintf( __( '%s received via website', 'kb-support' ), kbs_get_ticket_label_plural() )
            ),
            2  => array(
                'slug' => 'kbs-email',
                'name' => __( 'Email', 'kb-support' ),
                'desc' => sprintf( __( '%s received via email', 'kb-support' ), kbs_get_ticket_label_plural() )
            ),
            3  => array(
                'slug' => 'kbs-telephone',
                'name' => __( 'Telephone', 'kb-support' ),
                'desc' => sprintf( __( '%s received via telephone', 'kb-support' ), kbs_get_ticket_label_plural() )
            ),
            99 => array(
                'slug' => 'kbs-other',
                'name' => __( 'Other', 'kb-support' ),
                'desc' => sprintf( __( '%s received via another means', 'kb-support' ), kbs_get_ticket_label_plural() )
            )
        );

        $sources = apply_filters( 'kbs_ticket_log_sources', $sources );

        foreach( $step_items as $ticket )	{

			// Retrieve current source
			$meta_key   = 'kbs_ticket' == $ticket->post_type ? '_kbs_ticket_source' : '_kbs_reply_source';
			$old_source = get_post_meta( $ticket->ID, $meta_key, true );
            $old_source = ! empty( $old_source ) ? absint( $old_source ) : 1;

			// Map to new source term and use Website as the default
            if ( isset( $sources[ $old_source ] ) && ! empty( $sources[ $old_source ]['slug'] ) )   {
                $new_source = $sources[ $old_source ]['slug'];
            } else  {
                $new_source = 'kbs-website';
            }

			// Add source term to ticket
            $add_term = wp_set_object_terms( $ticket->ID, $new_source, 'ticket_source' );

            if ( ! is_wp_error( $add_term ) )   {
                delete_post_meta( $ticket->ID, $meta_key );
            }

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

		$total = (int) get_option( 'kbs_tickets_and_replies_total_sources', 0 );

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
			delete_option( 'kbs_tickets_and_replies_total_sources' );
			$this->message = sprintf( __( '%s sources updated successfully.', 'kb-support' ), kbs_get_ticket_label_singular() );
			kbs_set_upgrade_complete( 'upgrade_ticket_sources' );
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
	 * Fetch total number of tickets and replies needing migration
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
            WHERE post_type IN ('kbs_ticket', 'kbs_ticket_reply')
            "
        );

		// Temporarily save the number of rows
		update_option( 'kbs_tickets_and_replies_total_sources', (int) $ticket_count );
	}

	/**
	 * Get the tickets and replies (50 based on this->per_step) for the current step
	 *
	 * @since	1.2.9
	 * @return	array
	 */
	private function get_tickets_for_current_step() {
		// Default values
		$offset  = ( $this->step * $this->per_step ) - $this->per_step;

        $args = array(
            'posts_per_page' => $this->per_step,
            'paged'          => $this->step,
            'status'         => 'any',
            'order'          => 'ASC',
            'post_type'      => array( 'kbs_ticket', 'kbs_ticket_reply' )
        );

        $query   = new WP_Query( $args );
        $tickets = $query->posts;

		return ! empty( $tickets ) ? $tickets : array();
	} // get_tickets_for_current_step

} // KBS_Ticket_Sources_Migration
