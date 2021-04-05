<?php
/**
 * Manage company posts.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Posts
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove block editor for kbs_company.
 *
 * @since	1.5
 * @param	bool	$block_editor	Whether or not to use block editor
 * @param	string	$post_type		Post type
 * @return	bool	True to use block editor, or false
 */
function kbs_company_remove_block_editor( $block_editor, $post_type )	{
	if ( 'kbs_company' == $post_type )	{
		$block_editor = false;
	}

	return $block_editor;
} // kbs_company_remove_block_editor
add_filter( 'use_block_editor_for_post_type', 'kbs_company_remove_block_editor', 10, 2 );

/**
 * Define the columns that should be displayed for the Company post lists screen
 *
 * @since	1.0
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function kbs_set_company_post_columns( $columns ) {

	$columns = array(
        'cb'        => '<input type="checkbox" />',
		'logo'      => __( 'Logo', 'kb-support' ),     
		'title'     => __( 'Company', 'kb-support' ),
		'contact'   => __( 'Contact', 'kb-support' ),
		'email'     => __( 'Email', 'kb-support' ),
		'phone'     => __( 'Phone', 'kb-support' ),
		'website'   => __( 'Web URL', 'kb-support' ),
		'tickets'   => kbs_get_ticket_label_plural(),
		'customers' => __( 'Customers', 'kb-support' )
    );
	
	return apply_filters( 'kbs_company_post_columns', $columns );
	
} // kbs_set_company_post_columns
add_filter( 'manage_kbs_company_posts_columns' , 'kbs_set_company_post_columns' );

/**
 * Define the data to be displayed within the Company post custom columns.
 *
 * @since	1.0
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	str
 */
function kbs_set_company_column_data( $column_name, $post_id ) {

	$company = new KBS_Company( $post_id );

	switch ( $column_name ) {
		case 'logo':
			echo get_avatar( 9999999999, '30', $company->logo );
			break;

		case 'contact':
			echo $company->contact;
			break;

		case 'email':
			echo $company->email;
			break;

		case 'phone':
			echo $company->phone;
			break;

		case 'website':
			printf(
                '<a href="%1$s" title="%2$s" target="_blank">%1$s</a>',
                $company->website,
                sprintf( __( 'Open %s in a new tab', 'kb-support' ), $company->website )
            );
			break;

		case 'tickets':
			$company_tickets = kbs_count_company_tickets( $post_id );
			if ( $company_tickets > 0 )	{
				$tickets_page = add_query_arg( array(
					'post_type'  => 'kbs_ticket',
					'company_id' => $post_id
				), admin_url( 'edit.php' ) );
				echo '<a href="' . $tickets_page . '">';
			}

			echo kbs_count_company_tickets( $post_id );

			if ( $company_tickets > 0 )	{
				echo '</a>';
			}
			break;

		case 'customers':
			$customer_count = kbs_count_customers_in_company( $post_id );
			$customer_page  = add_query_arg( array(
				'post_type'  => 'kbs_ticket',
				'page'       => 'kbs-customers',
				'company_id' => $post_id
			), admin_url( 'edit.php' ) );

			if ( $customer_count > 0 )	{
				echo '<a href="' . $customer_page . '">';
			}

			echo kbs_count_customers_in_company( $post_id );

			if ( $customer_count > 0 )	{
				'</a>';
			}
			break;

		default:
			echo __( 'No callback found for post column', 'kb-support' );
			break;
	}

} // kbs_set_company_column_data
add_action( 'manage_kbs_company_posts_custom_column' , 'kbs_set_company_column_data', 10, 2 );

/**
 * Save the Company custom posts
 *
 * @since	1.0
 * @param	int		$post_id		The ID of the post being saved.
 * @param	obj		$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 *
 * @return	void
 */
function kbs_company_post_save( $post_id, $post, $update )	{	

	// Remove the save post action to avoid loops
	remove_action( 'save_post_kbs_company', 'kbs_company_post_save', 10, 3 );

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	$url = remove_query_arg( 'kbs-message' );

	if (
		! isset( $_POST['kbs_company_meta_box_nonce'] )
		|| ! wp_verify_nonce( $_POST['kbs_company_meta_box_nonce'], 'kbs_company_meta_save' )
	)	{
		return;
	}

	// Fire the before save action but only if this is not a new company creation (i.e $post->post_status == 'draft')
	if ( $update === true )	{
		do_action( 'kbs_company_before_save', $post_id, $post, $update );
	}

	$fields = kbs_company_metabox_fields();

	foreach( $fields as $field )	{
		$posted_value = '';

		if ( ! empty( $_POST[ $field ] ) ) {

			if ( '_kbs_company_email' == $field )	{
				$posted_value = sanitize_email( trim( $_POST[ $field ] ) );
			} elseif ( 'kbs_company_website' == $field )	{
				$posted_value = esc_url( $_POST[ $field ] );
			} elseif ( is_string( $_POST[ $field ] ) )	{
				$posted_value = sanitize_text_field( $_POST[ $field ] );
			} elseif ( is_int( $_POST[ $field ] ) )	{
				$posted_value = $_POST[ $field ];
			} elseif( is_array( $_POST[ $field ] ) )	{
				$posted_value = array_map( 'absint', $_POST[ $field ] );
			}
		}

		$new_value = apply_filters( 'kbs_company_metabox_save_' . $field, $posted_value );

		if ( ! empty( $new_value ) ) {
			update_post_meta( $post_id, $field, $new_value );

            if ( '_kbs_company_customer' == $field )    {
                kbs_add_customer_to_company( $new_value, $post_id );
            }

		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	// Fire the after save action
	do_action( 'kbs_company_after_save', $post_id, $post, $update );

	// Re-add the save post action
	add_action( 'save_post_kbs_company', 'kbs_company_post_save', 10, 3 );
} // kbs_company_post_save
add_action( 'save_post_kbs_company', 'kbs_company_post_save', 10, 3 );
