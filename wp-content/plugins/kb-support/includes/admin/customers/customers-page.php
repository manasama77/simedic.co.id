<?php
/**
 * Customer Page
 *
 * @package     KBS
 * @subpackage  Customers/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Customers Page
 *
 * Renders the customers page contents.
 *
 * @since	1.0
 * @return	void
*/
function kbs_customers_page() {
	$default_views  = kbs_customer_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'customers';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		if ( 'add' == $requested_view )	{
			kbs_render_add_customer_view();
		} else	{
			kbs_render_customer_view( $requested_view, $default_views );
		}
	} else {
		kbs_customers_list();
	}
} // kbs_customers_page

/**
 * Register the views for customer management
 *
 * @since	1.0
 * @return	arr		Array of views and their callbacks
 */
function kbs_customer_views() {
	$views = array();

	return apply_filters( 'kbs_customer_views', $views );
} // kbs_customer_views

/**
 * Register the tabs for customer management.
 *
 * @since	1.0
 * @return	arr		Array of tabs for the customer
 */
function kbs_customer_tabs() {
	$tabs = array();

	return apply_filters( 'kbs_customer_tabs', $tabs );
} // kbs_customer_tabs

/**
 * List table of customers
 *
 * @since	1.0
 * @return	void
 */
function kbs_customers_list() {
	include( dirname( __FILE__ ) . '/class-kbs-customer-table.php' );

	$add_customer_url = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=add' );

	$customers_table = new KBS_Customer_Table();
	$customers_table->prepare_items();
	?>
	<div class="wrap">
		<h1>
			<?php _e( 'Customers', 'kb-support' ); ?>
            <a href="<?php echo esc_url( $add_customer_url ); ?>" class="page-title-action"><?php _e( 'Add Customer', 'kb-support' ); ?></a>
        </h1>
		<?php do_action( 'kbs_customers_table_top' ); ?>
		<form id="kbs-customers-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers' ); ?>">
			<?php
			$customers_table->search_box( __( 'Search Customers', 'kb-support' ), 'kbs-customers' );
			$customers_table->display();
			?>
			<input type="hidden" name="post_type" value="kbs_ticket" />
			<input type="hidden" name="page" value="kbs-customers" />
			<input type="hidden" name="view" value="customers" />
		</form>
		<?php do_action( 'kbs_customers_table_bottom' ); ?>
	</div>
	<?php
} // kbs_customers_list

/**
 * Renders the customer view wrapper
 *
 * @since	1.0
 * @param	str		$view		The View being requested
 * @param	arr		$callbacks	The Registered views and their callback functions
 * @return	void
 */
function kbs_render_customer_view( $view, $callbacks ) {

	$render             = isset( $_GET['render'] ) ? $_GET['render'] : true;
	$url                = remove_query_arg( array( 'kbs-message', 'render' ) );
	$customer_tabs      = kbs_customer_tabs();
	$active_tab         = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $customer_tabs ) ? $_GET['tab'] : 'general';

	if ( ! kbs_can_view_customers() ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'customer_list_permission',
			'render'      => 0
		), $url ) );
		die();
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'invalid_customer_id',
			'render'      => 0
		), $url ) );
		die();
	}

	$customer_id = (int)$_GET['id'];
	$customer    = new KBS_Customer( $customer_id );

	if ( empty( $customer->id ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'invalid_customer_id',
			'render'      => 0
		), $url ) );
		die();
	}

	?>

	<div class='wrap'>
        <h1 class="nav-tab-wrapper">
            <?php
            foreach( $customer_tabs as $key => $tab ) {
    
                $tab_url = add_query_arg( array(
                    'view' => $key,
                ) , $url );
    
                $active = $view == $key ? ' nav-tab-active' : '';
    
                echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab['title'] ) . '" class="nav-tab' . $active . '">';
                    echo '<span class="dashicons ' . sanitize_html_class( $tab['dashicon'] ) . '" aria-hidden="true"></span>' . esc_html( $tab['title'] );
                echo '</a>';
            }
            ?>
        </h1>

		<?php if ( $customer && $render ) : ?>
            <div id="kbs-item-wrapper" class="kbs-customer-wrapper" style="float: left">
                <?php $callbacks[ $view ]( $customer ) ?>
            </div>
        <?php endif; ?>

	</div><!-- .wrap -->
	
	<?php

} // kbs_render_customer_view

/**
 * View a customer
 *
 * @since	1.0
 * @param	$customer	The Customer object being displayed
 * @return	void
 */
function kbs_customers_view( $customer ) {

	$customer_edit_role = apply_filters( 'kbs_edit_customers_role', 'manage_ticket_settings' );

	$tickets      = kbs_get_customer_tickets( $customer->id, array(), false );
	$ticket_count = ! empty( $tickets ) ? count( $tickets ) : 0;

	?>

	<?php do_action( 'kbs_customer_card_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<form id="edit-customer-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id ); ?>">

			<div class="kbs-item-info customer-info">

				<div class="avatar-wrap left" id="customer-avatar">
					<?php echo get_avatar( $customer->email, '', kbs_get_company_logo( $customer->company_id ) ); ?><br />
					<?php if ( current_user_can( $customer_edit_role ) ): ?>
						<span class="info-item editable customer-edit-link"><a href="#" id="edit-customer"><?php _e( 'Edit Customer', 'kb-support' ); ?></a></span>
					<?php endif; ?>
				</div>

				<div class="customer-id right">
					#<?php echo $customer->id; ?>
				</div>

				<div class="customer-address-wrapper right">
					<?php
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

					?>

					<?php if ( ! empty( $has_address ) ) : ?>
                        <strong><?php _e( 'Customer Address', 'kb-support' ); ?></strong>
                        <span class="customer-address info-item editable">
                            <span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
                            <span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
                            <span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
                            <span class="info-item" data-key="state"><?php echo $address['state']; ?></span>
                            <span class="info-item" data-key="country"><?php echo $address['country']; ?></span>
                            <span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
                        </span>
                    <?php else : ?>
                    	<span class="customer-address info-item editable">
							<?php _e( 'No Customer Address Recorded', 'kb-support' ); ?>
                        </span>
					<?php endif; ?>

					<span class="customer-address info-item edit-item">
						<input class="info-item" type="text" data-key="line1" name="customerinfo[line1]" placeholder="<?php _e( 'Address 1', 'kb-support' ); ?>" value="<?php echo $address['line1']; ?>" />
						<input class="info-item" type="text" data-key="line2" name="customerinfo[line2]" placeholder="<?php _e( 'Address 2', 'kb-support' ); ?>" value="<?php echo $address['line2']; ?>" />
						<input class="info-item" type="text" data-key="city" name="customerinfo[city]" placeholder="<?php _e( 'City', 'kb-support' ); ?>" value="<?php echo $address['city']; ?>" />
                        <input type="text" data-key="state" name="customerinfo[state]" id="card_state" class="card_state kbs-input info-item" placeholder="<?php _e( 'County / State / Province', 'kb-support' ); ?>"/>
						<select data-key="country" name="customerinfo[country]" id="billing_country" class="billing_country kbs-select edit-item">
							<?php

							$selected_country = $address['country'];

							$countries = kbs_get_country_list();
							foreach( $countries as $country_code => $country ) {
								echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
							}
							?>
						</select>

						<input class="info-item" type="text" data-key="zip" name="customerinfo[zip]" placeholder="<?php _e( 'Postal / Zip', 'kb-support' ); ?>" value="<?php echo $address['zip']; ?>" />
					</span>

				</div>

				<div class="customer-main-wrapper left">

					<span class="customer-name info-item edit-item"><input size="15" data-key="name" name="customerinfo[name]" type="text" value="<?php echo esc_attr( $customer->name ); ?>" placeholder="<?php _e( 'Customer Name', 'kb-support' ); ?>" /></span>

					<span class="customer-name info-item editable"><span data-key="name"><?php echo $customer->name; ?></span></span>

					<?php if ( kbs_has_companies() ) : ?>

                        <span class="customer-company info-item edit-item">
                            <?php echo KBS()->html->company_dropdown( array(
                                'name'        => 'customerinfo[company_id]',
                                'selected'    => $customer->company_id,
                                'number'      => -1,
                                'placeholder' => __( 'Customer Company', 'kb-support' ),
                                'data'        => array(
									'key'         => 'company_id'
								)
                            ) ); ?>
                        </span>

                        <span class="customer-company info-item editable"><span data-key="company_id"><?php echo $customer->company; ?></span></span>

					<?php endif; ?>

					<span class="customer-name info-item edit-item"><input size="20" data-key="email" name="customerinfo[email]" type="text" value="<?php echo $customer->email; ?>" placeholder="<?php _e( 'Customer Email', 'kb-support' ); ?>" /></span>

					<span class="customer-email info-item editable" data-key="email"><?php echo $customer->email; ?></span>

					<span class="customer-primary-phone info-item edit-item"><input size="20" data-key="primary-phone" name="customerinfo[primary_phone]" type="text" value="<?php echo $customer->primary_phone; ?>" placeholder="<?php _e( 'Customer Primary Phone', 'kb-support' ); ?>" /></span>

					<span class="customer-primary-phone info-item editable" data-key="primary_phone"><?php echo $customer->primary_phone; ?></span>

					<span class="customer-additional-phone info-item edit-item"><input size="20" data-key="additional-phone" name="customerinfo[additional_phone]" type="text" value="<?php echo $customer->additional_phone; ?>" placeholder="<?php _e( 'Customer Additional Phone', 'kb-support' ); ?>" /></span>

					<span class="customer-additional-phone info-item editable" data-key="additional_phone"><?php echo  $customer->additional_phone; ?></span>

					<span class="customer-website info-item edit-item"><input size="20" data-key="web-address" name="customerinfo[website]" type="text" value="<?php echo $customer->website; ?>" placeholder="<?php _e( 'http://', 'kb-support' ); ?>" /></span>

					<span class="customer-website info-item editable" data-key="website"><?php echo  $customer->website; ?></span>

					<span class="customer-since info-item">
						<?php _e( 'Customer since', 'kb-support' ); ?>
						<?php echo date_i18n( get_option( 'date_format' ), strtotime( $customer->date_created ) ) ?>
					</span>

					<span class="customer-user-id info-item edit-item">
						<?php

						$user_id    = $customer->user_id > 0 ? $customer->user_id : '';
						$data_atts  = array( 'key' => 'user_login', 'exclude' => $user_id );
						$user_args  = array(
							'name'  => 'customerinfo[user_login]',
							'class' => 'kbs-user-dropdown',
							'data'  => $data_atts,
						);

						if ( ! empty( $user_id ) ) {
							$userdata = get_userdata( $user_id );
							$user_args['value'] = $userdata->user_login;
						}

						echo KBS()->html->ajax_user_search( $user_args );
						?>
						<input type="hidden" name="customerinfo[user_id]" data-key="user_id" value="<?php echo $customer->user_id; ?>" />
					</span>

					<span class="customer-user-id info-item editable">
						<?php _e( 'User ID', 'kb-support' ); ?>:&nbsp;

						<?php if ( intval( $customer->user_id ) > 0 ) : ?>

							<span data-key="user_id"><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $customer->user_id ); ?>"><?php echo $customer->user_id; ?></a></span>

						<?php else : ?>
							<span data-key="user_id"><?php _e( 'none', 'kb-support' ); ?></span>
						<?php endif; ?>

						<?php if ( current_user_can( $customer_edit_role ) && intval( $customer->user_id ) > 0 ) : ?>

							<span class="disconnect-user"> - <a id="disconnect-customer" href="#disconnect"><?php _e( 'Disconnect User', 'kb-support' ); ?></a></span>

						<?php endif; ?>
					</span>

					<?php do_action( 'kbs_customer_before_ticket_count', $customer ); ?>

                    <a href="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&customer=' . $customer->id ); ?>">
                        <?php printf( _n( '%d ' . kbs_get_ticket_label_singular(), '%d ' . kbs_get_ticket_label_plural(), $ticket_count, 'kb-support' ), $ticket_count ); ?>
                    </a>

				</div>

			</div>

			<span id="customer-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="customerinfo[id]" value="<?php echo $customer->id; ?>" />
				<?php wp_nonce_field( 'edit-customer', '_wpnonce', false, true ); ?>
				<input type="hidden" name="kbs-action" value="edit-customer" />
				<input type="submit" id="kbs-edit-customer-save" class="button-secondary" value="<?php _e( 'Update Customer', 'kb-support' ); ?>" />
				<a id="kbs-edit-customer-cancel" href="" class="delete"><?php _e( 'Cancel', 'kb-support' ); ?></a>
			</span>

		</form>
	</div>

	<hr />

	<?php do_action( 'kbs_customer_before_tables_wrapper', $customer ); ?>

	<div id="kbs-item-tables-wrapper" class="customer-tables-wrapper customer-section">

		<?php do_action( 'kbs_customer_before_tables', $customer ); ?>

		<h3>
			<?php _e( 'Customer Emails', 'kb-support' ); ?>
		</h3>
		<?php
			$primary_email     = $customer->email;
			$additional_emails = $customer->emails;

			$all_emails = array( 'primary' => $primary_email );
			foreach ( $additional_emails as $key => $email ) {
				if ( $primary_email === $email ) {
					continue;
				}

				$all_emails[ $key ] = $email;
			}
		?>
		<table class="wp-list-table widefat striped emails">
			<thead>
				<tr>
					<th><?php _e( 'Email', 'kb-support' ); ?></th>
					<th><?php _e( 'Actions', 'kb-support' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $all_emails ) ) : ?>
					<?php foreach ( $all_emails as $key => $email ) : ?>
						<tr data-key="<?php echo $key; ?>">
							<td>
								<?php echo $email; ?>
								<?php if ( 'primary' === $key ) : ?>
									<span class="dashicons dashicons-star-filled primary-email-icon" title="<?php _e( 'This is the customers primary email address', 'kb-support' ); ?>"></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( 'primary' !== $key ) : ?>
									<?php
										$base_url    = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id );
										$promote_url = wp_nonce_url( add_query_arg( array( 'email' => rawurlencode( $email ), 'kbs-action' => 'customer-primary-email'), $base_url ), 'kbs-set-customer-primary-email' );
										$remove_url  = wp_nonce_url( add_query_arg( array( 'email' => rawurlencode( $email ), 'kbs-action' => 'customer-remove-email'), $base_url ), 'kbs-remove-customer-email' );
									?>
									<a href="<?php echo $promote_url; ?>"><?php _e( 'Make Primary', 'kb-support' ); ?></a>
									&nbsp;|&nbsp;
									<a href="<?php echo $remove_url; ?>" class="delete"><?php _e( 'Remove', 'kb-support' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr class="add-customer-email-row">
						<td colspan="2" class="add-customer-email-td">
							<div class="add-customer-email-wrapper">
								<input type="hidden" name="customer-id" value="<?php echo $customer->id; ?>" />
								<?php wp_nonce_field( 'kbs-add-customer-email', 'add_email_nonce', false, true ); ?>
								<input type="email" name="additional-email" value="" placeholder="<?php _e( 'Email Address', 'kb-support' ); ?>" />&nbsp;
								<input type="checkbox" name="make-additional-primary" value="1" id="make-additional-primary" />&nbsp;<label for="make-additional-primary"><?php _e( 'Make Primary', 'kb-support' ); ?></label>
								<button class="button-secondary kbs-add-customer-email" id="add-customer-email" style="margin: 6px 0;"><?php _e( 'Add Email', 'kb-support' ); ?></button>
								<span class="spinner"></span>
							</div>
							<div class="notice-wrap"></div>
						</td>
					</tr>
				<?php else: ?>
					<tr><td colspan="2"><?php _e( 'No Emails Found', 'kb-support' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php do_action( 'kbs_customer_before_tickets_table', $customer ); ?>

        <h3><?php printf( __( 'Recent %s', 'kb-support' ), kbs_get_ticket_label_plural() ); ?></h3>
		
		<table class="wp-list-table widefat striped tickets">
			<thead>
				<tr>
					<th><?php _e( '#', 'kb-support' ); ?></th>
					<th><?php _e( 'Opened', 'kb-support' ); ?></th>
                    <th><?php _e( 'Title', 'kb-support' ); ?></th>
					<th><?php _e( 'Status', 'kb-support' ); ?></th>
                    <?php do_action( 'kbs_customer_tickets_table_headers', $customer ); ?>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $tickets ) ) : ?>
					<?php foreach ( $tickets as $ticket ) : ?>
						<tr>
							<td><a href="<?php echo admin_url( 'post.php?post=' . $ticket->ID . '&action=edit' ); ?>">
									<?php echo kbs_format_ticket_number( kbs_get_ticket_number( $ticket->ID ) ); ?>
								</a>
                            </td>
							<td class="date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->post_date ) ); ?></td>
                            <td class="title"><?php echo get_the_title( $ticket->ID ); ?></td>
							<td><?php echo kbs_get_ticket_status( $ticket, true ); ?></td>
                            <?php do_action( 'kbs_after_customer_tickets_table_status', $customer ); ?>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5"><?php printf( __( 'No %s Found', 'kb-support' ), kbs_get_ticket_label_plural() ); ?></td></tr>
				<?php endif; ?>
			</tbody>
            <tfoot>
				<tr>
					<th><?php _e( '#', 'kb-support' ); ?></th>
					<th><?php _e( 'Opened', 'kb-support' ); ?></th>
                    <th><?php _e( 'Title', 'kb-support' ); ?></th>
					<th><?php _e( 'Status', 'kb-support' ); ?></th>
                    <?php do_action( 'kbs_customer_tickets_table_headers', $customer ); ?>
				</tr>
			</tfoot>
		</table>

		<?php do_action( 'kbs_customer_after_tickets_table', $customer ); ?>

    </div>

	<?php
}

/**
 * View the notes of a customer.
 *
 * @since	1.0
 * @param	$customer	The Customer being displayed
 * @return	void
 */
function kbs_customer_notes_view( $customer ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $customer->get_notes_count();
	$per_page    = apply_filters( 'kbs_customer_notes_per_page', 20 );
	$total_pages = ceil( $note_count / $per_page );

	$customer_notes    = $customer->get_notes( $per_page, $paged );

    $show_agree_to_terms   = kbs_get_option( 'show_agree_to_terms', false );
    $show_agree_to_privacy = kbs_get_option( 'show_agree_to_privacy_policy', false );
    $privacy_accepted      = __( 'Not yet accepted', 'kb-support' );
    $terms_accepted        = __( 'Not yet agreed', 'kb-support' );
    $privacy_timestamp     = $customer->get_meta( 'agree_to_privacy_time', true );
    $terms_timestamp       = $customer->get_meta( 'agree_to_terms_time', true );
    $date_format           = get_option( 'date_format' );
    $time_format           = get_option( 'time_format' );

    if ( ! empty( $privacy_timestamp ) ) {
        $privacy_accepted = date_i18n( $date_format . ' ' . $time_format, $privacy_timestamp );
    }

    if ( ! empty( $terms_timestamp ) ) {
        $terms_accepted = date_i18n( $date_format . ' ' . $time_format, $terms_timestamp );
    }
	?>

	<div id="kbs-item-notes-wrapper">
		<div class="kbs-item-notes-header">
			<?php echo get_avatar( $customer->email, 30, kbs_get_company_logo( $customer->company_id ) ); ?> <span><?php echo $customer->name; ?></span>
		</div>

        <h3><?php _e( 'Agreements','kb-support' ); ?></h3>

        <?php if ( $show_agree_to_terms ) : ?>
            <span class="customer-terms-agreement-date info-item">
                <?php printf( __( 'Last Agreed to Terms%s', 'kb-support' ), ': ' . $terms_accepted ); ?>
            </span>
        <?php endif; ?>

        <?php if ( $show_agree_to_privacy ) : ?>
            <span class="customer-privacy-policy-date info-item">
                <?php printf( __( 'Last Agreed to Privacy Policy%s', 'kb-support' ),': ' . $privacy_accepted ); ?>
            </span>
        <?php endif; ?>

		<h3><?php _e( 'Notes', 'kb-support' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
            <div style="display: block; margin-bottom: 35px;">
                <form id="kbs-add-customer-note" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=notes&id=' . $customer->id ); ?>">
                    <textarea id="customer-note" name="customer_note" class="customer-note-input" rows="10"></textarea>
                    <br />
                    <input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer->id; ?>" />
                    <input type="hidden" name="kbs-action" value="add-customer-note" />
                    <?php wp_nonce_field( 'add-customer-note', 'add_customer_note_nonce', true, true ); ?>
                    <input id="add-customer-note" class="right button-primary" type="submit" value="Add Note" />
                </form>
            </div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="kbs-customer-notes">
		<?php if ( is_array( $customer_notes ) && count( $customer_notes ) > 0 ) : ?>
			<?php foreach( $customer_notes as $key => $note ) : ?>
				<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="kbs-no-customer-notes">
				<?php _e( 'No Customer Notes', 'kb-support' ); ?>
			</div>
		<?php endif; ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
} // kbs_customer_notes_view

/**
 * View the delete customer page.
 *
 * @since	1.0
 * @param	$customer	The Customer being displayed
 * @return	void
 */
function kbs_customers_delete_view( $customer ) {
	$customer_edit_role = apply_filters( 'kbs_edit_customers_role', 'manage_ticket_settings' );

	?>

	<?php do_action( 'kbs_customer_delete_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<form id="delete-customer" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=delete&id=' . $customer->id ); ?>">

				<div class="kbs-item-notes-header">
				<?php echo get_avatar( $customer->email, 30, kbs_get_company_logo( $customer->company_id ) ); ?> <span><?php echo $customer->name; ?></span>
			</div>


			<div class="customer-info delete-customer">

				<span class="delete-customer-options">
					<p>
						<?php echo KBS()->html->checkbox( array( 'name' => 'kbs-customer-delete-confirm' ) ); ?>
						<label for="kbs-customer-delete-confirm"><?php _e( 'Are you sure you want to delete this customer?', 'kb-support' ); ?></label>
					</p>

					<?php do_action( 'kbs_customer_delete_inputs', $customer ); ?>
				</span>

				<span id="customer-edit-actions">
					<input type="hidden" name="customer_id" value="<?php echo $customer->id; ?>" />
					<?php wp_nonce_field( 'delete-customer', '_wpnonce', false, true ); ?>
					<input type="hidden" name="kbs-action" value="delete-customer" />
					<input type="submit" disabled="disabled" id="kbs-delete-customer" class="button-primary" value="<?php _e( 'Delete Customer', 'kb-support' ); ?>" />
					<a id="kbs-delete-customer-cancel" href="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id ); ?>" class="delete"><?php _e( 'Cancel', 'kb-support' ); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php

	do_action( 'kbs_customer_delete_bottom', $customer );
} // kbs_customers_delete_view

/**
 * Renders the form for adding a new customer.
 *
 * @since	1.0
 * @return	voide
 */
function kbs_render_add_customer_view()	{
	?>
    <div class="wrap">
		<h1>
			<?php _e( 'Add Customer', 'kb-support' ); ?>
        </h1>

		<div id="kbs-item-wrapper" class="kbs-customer-wrapper" style="float: left">
        	<div class="notice-wrap"></div>
			<div class="info-wrapper customer-section">

                <form id="add-customer-info" method="post" action="">

                    <div class="kbs-item-info customer-info">

                        <div class="customer-main-wrapper left">

                            <span class="customer-name info-item"><input size="15" id="customer-name" name="customer_name" type="text" value="" placeholder="<?php _e( 'Customer Name', 'kb-support' ); ?>" /></span>

							<?php if ( kbs_has_companies() ) : ?>
                                <span class="customer-company info-item">
                                	<?php echo KBS()->html->company_dropdown( array(
										'name'        => 'customer_company',
										'number'      => -1,
										'placeholder' => __( 'Customer Company', 'kb-support' )
									) ); ?>
                                </span>
                            <?php endif; ?>

                            <span class="customer-email info-item"><input size="20" id="customer-email" name="customer_email" type="text" value="" placeholder="<?php _e( 'Customer Email', 'kb-support' ); ?>" /></span>

                        </div>
        
                    </div>
        
                    <span id="customer-add-actions">
                        <?php wp_nonce_field( 'add-customer', 'add_customer_nonce', false, true ); ?>
                        <input type="hidden" name="kbs-action" value="add-customer" />
                        <button class="button-primary kbs-add-customer-save" id="kbs-add-customer-save"><?php _e( 'Add Customer', 'kb-support' ); ?></button>
                        <span class="spinner"></span>
                    </span>
        
                </form>
            </div>
        </div>

		<?php do_action( 'kbs_add_customer_form_bottom' ); ?>
	</div>
    <?php
} // kbs_render_add_customer_view
