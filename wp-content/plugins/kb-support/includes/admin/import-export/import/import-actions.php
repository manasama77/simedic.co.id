<?php
/**
 * Import Actions
 *
 * These are actions related to exporting data from KBS.
 *
 * @package     KBS
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2016, Mike Howard
 * @since       1.1
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process a settings import from a json file
 *
 * @since   1.1
 * @return  void
 */
function kbs_tools_settings_process_import() {

    if ( ! isset( $_POST['kbs-action'] ) || 'import_settings' != $_POST['kbs-action'] )	{
		return;
	}

	if ( empty( $_POST['kbs_import_nonce'] ) ) {
		return;
    }

	if ( ! wp_verify_nonce( $_POST['kbs_import_nonce'], 'kbs_import_nonce' ) )   {
		return;
    }

	if ( ! current_user_can( 'export_ticket_reports' ) ) {
		return;
    }

	if ( kbs_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
		wp_safe_redirect( add_query_arg( array(
            'post_type'    => 'kbs_ticket',
            'page'         => 'kbs-tools',
            'tab'          => 'import',
            'kbs-message'  => 'settings-import-missing-file'
        ), admin_url( 'edit.php' ) ) );
        exit;
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if ( empty( $import_file ) ) {
		wp_safe_redirect( add_query_arg( array(
            'post_type'    => 'kbs_ticket',
            'page'         => 'kbs-tools',
            'tab'          => 'import',
            'kbs-message'  => 'settings-import-missing-file'
        ), admin_url( 'edit.php' ) ) );
        exit;
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = kbs_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'kbs_settings', $settings );

	wp_safe_redirect( add_query_arg( array(
		'post_type'    => 'kbs_ticket',
		'page'         => 'kbs-tools',
		'tab'          => 'import',
		'kbs-message'  => 'settings-imported'
	), admin_url( 'edit.php' ) ) );
	exit;

} // kbs_tools_settings_process_import
add_action( 'init', 'kbs_tools_settings_process_import' );
