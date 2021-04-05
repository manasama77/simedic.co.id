<?php
/**
 * Form actions
 *
 * @package     KBS
 * @subpackage  Functions/Forms/Actions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Delete's a form field.
 *
 * @since	1.0
 * @return	void
 */
function kbs_delete_field_action()	{

	if ( ! isset( $_GET['kbs-action'] ) || 'delete_form_field' != $_GET['kbs-action'] )	{
		return;
	}

	if ( ! isset( $_GET['kbs-action-nonce'] ) || ! wp_verify_nonce( $_GET['kbs-action-nonce'], 'delete_form_field' ) )	{
		return;
	}

	$url     = remove_query_arg( array( 'kbs-message', 'kbs-action', 'kbs_action_nonce', 'field_id' ) );
	$message = 'field_deleted';
	
	if ( ! kbs_delete_field( $_GET['field_id'] ) )	{
		$message = 'field_delete_fail';
	}

	wp_redirect(
		add_query_arg(
			'kbs-message', $message, $url
		)
	);

	die();

} // kbs_delete_field_action

add_action( 'init', 'kbs_delete_field_action' );
