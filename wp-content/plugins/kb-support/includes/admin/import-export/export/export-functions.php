<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from KBS.
 *
 * @package     KBS
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, Mike Howard
 * @since       1.1
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

require_once( KBS_PLUGIN_DIR . '/includes/admin/import-export/export/class-kbs-export.php' );
require_once( KBS_PLUGIN_DIR . '/includes/admin/import-export/export/export-actions.php' );

/**
 * Process batch exports via ajax
 *
 * @since	1.1
 * @return	void
 */
function kbs_do_ajax_export() {

	require_once( KBS_PLUGIN_DIR . '/includes/admin/import-export/export/class-batch-export.php' );

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;

	if ( ! wp_verify_nonce( $_REQUEST['kbs_ajax_export'], 'kbs_ajax_export' ) ) {
		die( '-2' );
	}

	do_action( 'kbs_batch_export_class_include', $form['kbs-export-class'] );

	$step     = absint( $_POST['step'] );
	$class    = sanitize_text_field( $form['kbs-export-class'] );
	$export   = new $class( $step );

	if( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		echo json_encode( array( 'error' => true, 'message' => __( 'Export location or file not writable', 'kb-support' ) ) ); exit;
	}

	$export->set_properties( $_REQUEST );

	// Allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data
	$export->pre_fetch();

	$ret = $export->process_step( $step );

	$percentage = $export->get_percentage_complete();

	if( $ret ) {

		$step += 1;
		echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) ); exit;

	} elseif ( true === $export->is_empty ) {

		echo json_encode( array( 'error' => true, 'message' => __( 'No data found for export parameters', 'kb-support' ) ) ); exit;

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ? $export->message : __( 'Batch Processing Complete', 'kb-support' );
		echo json_encode( array( 'success' => true, 'message' => $message ) ); exit;

	} else {

		$args = array_merge( $_REQUEST, array(
			'step'       => $step,
			'class'      => $class,
			'nonce'      => wp_create_nonce( 'kbs-batch-export' ),
			'kbs-action' => 'download_batch_export',
		) );

		$event_url = add_query_arg( $args, admin_url() );

		echo json_encode( array( 'step' => 'done', 'url' => $event_url ) ); exit;

	}
} // kbs_do_ajax_export
add_action( 'wp_ajax_kbs_do_ajax_export', 'kbs_do_ajax_export' );

/**
 * Display the customer export.
 *
 * @since	1.1
 */
function kbs_export_customers_display()	{
	?>
	<div class="postbox kbs-export-customers">
		<h3><span><?php _e( 'Export Customers','kb-support' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Download a CSV of customers.', 'kb-support' ); ?></p>
			<form id="kbs-export-customers" class="kbs-export-form kbs-import-export-form" method="post">
				<?php wp_nonce_field( 'kbs_ajax_export', 'kbs_ajax_export' ); ?>
				<input type="hidden" name="kbs-export-class" value="KBS_Batch_Export_Customers"/>
				<?php submit_button( __( 'Generate CSV', 'kb-support' ), 'secondary', 'submit', false ); ?>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
    <?php
} // kbs_export_customers_display
add_action( 'kbs_tools_export_after', 'kbs_export_customers_display', 10 );

/**
 * Display the ticket export.
 *
 * @since	1.1
 */
function kbs_export_tickets_display() {
	$label_single = kbs_get_ticket_label_singular();
	$label_plural = kbs_get_ticket_label_plural();

    ?>
    <div class="postbox kbs-export-tickets">
		<h3><span><?php printf( __( 'Export %s', 'kb-support' ), $label_plural ); ?></span></h3>
		<div class="inside">
			<p><?php printf( __( 'Download a CSV formatted file of %s.', 'kb-support' ), strtolower( $label_plural ) ); ?></p>

            <form id="kbs-export-tickets" class="kbs-export-form kbs-import-export-form" method="post">
                <?php wp_nonce_field( 'kbs_ajax_export', 'kbs_ajax_export' ); ?>
                <input type="hidden" name="kbs-export-class" value="KBS_Batch_Export_Tickets"/>
                <?php echo KBS()->html->date_field( array(
					'id'          => 'kbs-ticket-export-start',
					'name'        => 'ticket_start',
					'placeholder' => __( 'Select Start Date', 'kb-support' )
				) ); ?>
                <?php echo KBS()->html->date_field( array(
					'id'          => 'kbs-ticket-export-end',
					'name'        => 'ticket_end',
					'placeholder' => __( 'Select End Date', 'kb-support' )
				) ); ?>
                <select name="ticket_status">
                    <option value="any"><?php _e( 'All Statuses', 'kb-support' ); ?></option>
                    <?php foreach( kbs_get_post_statuses( 'labels', true ) as $ticket_status ) : ?>
                        <option value="<?php echo $ticket_status->name; ?>"><?php echo esc_html( $ticket_status->label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <span>
                	<?php submit_button( __( 'Generate CSV', 'kb-support' ), 'secondary', 'submit', false ); ?>
                    <span class="spinner"></span>
                </span>
            </form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
    <?php
} // kbs_export_tickets_display
add_action( 'kbs_tools_export_after', 'kbs_export_tickets_display', 30 );

