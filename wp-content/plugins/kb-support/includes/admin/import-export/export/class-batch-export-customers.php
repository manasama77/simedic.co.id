<?php
/**
 * Batch Customers Export Class
 *
 * This class handles customer exports
 *
 * @package     KBS
 * @subpackage  Admin/Exports
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Batch_Export_Customers Class
 *
 * @since	1.1
 */
class KBS_Batch_Export_Customers extends KBS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var		str
	 * @since	1.1
	 */
	public $export_type = 'customers';

	/**
	 * Set the CSV columns
	 *
	 * @access	public
	 * @since	1.1
	 * @return	arr		$cols	All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'      => __( 'ID', 'kb-support' ),
			'name'    => __( 'Name', 'kb-support' ),
			'email'   => __( 'Email', 'kb-support' ),
			'company' => __( 'Company', 'kb-support' ),
			'tickets' => sprintf( __( 'Number of %s', 'kb-support' ), kbs_get_ticket_label_plural() ),
			'notes'   => __( 'Notes', 'kb-support' )
		);

		return $cols;
	} // csv_cols

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.1
	 * @return	arr		$data	The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		// Export all customers
		$offset    = 30 * ( $this->step - 1 );
		$customers = KBS()->customers->get_customers( array( 'number' => 30, 'offset' => $offset ) );

		$i = 0;

		foreach ( $customers as $customer ) {

			$data[$i]['id']      = $customer->id;
			$data[$i]['name']    = $customer->name;
			$data[$i]['email']   = $customer->email;
			$data[$i]['company'] = kbs_get_company_name( $customer->company_id );
			$data[$i]['tickets'] = kbs_get_customer_ticket_count( $customer->id );
			$data[$i]['notes']   = $customer->notes;

			$i++;
		}

		$data = apply_filters( 'mdjm_export_get_data', $data );
		$data = apply_filters( 'mdjm_export_get_data_' . $this->export_type, $data );

		return $data;
	} // get_data

	/**
	 * Return the calculated completion percentage
	 *
	 * @since	1.1
	 * @return	int
	 */
	public function get_percentage_complete() {

		$percentage = 0;

		// We can't count the number when getting them for a specific download
		$total = KBS()->customers->count();

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	} // get_percentage_complete

	/**
	 * Set the properties specific to the Clients export
	 *
	 * @since	1.1
	 * @param	arr		$request	The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start    = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end      = isset( $request['end']   ) ? sanitize_text_field( $request['end']   ) : '';
	} // set_properties

} // KBS_Batch_Export_Customers
