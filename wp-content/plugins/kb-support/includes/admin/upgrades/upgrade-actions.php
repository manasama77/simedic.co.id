<?php
/**
 * Upgrade Actions
 *
 * @package     KBS
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2019, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.9
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/***************************************************************************************************
 *
 * Sequential Ticket Numbers Update
 *
 **************************************************************************************************/

/**
 * Register the sequential ticket numbers update batch exporter
 *
 * @since	1.2.9
 */
function kbs_register_batch_sequential_ticket_numbers_migration() {
	add_action( 'kbs_batch_export_class_include', 'kbs_include_ticket_sequential_numbering_batch_processor', 10, 1 );
} // kbs_register_batch_ticket_source_migration
add_action( 'kbs_register_batch_exporter', 'kbs_register_batch_sequential_ticket_numbers_migration', 10 );

/**
 * Loads the sequential ticket numbers update batch process if needed
 *
 * @since 	1.2.9
 * @param	string    $class	The class being requested to run for the batch export
 * @return	void
 */
function kbs_include_ticket_sequential_numbering_batch_processor( $class ) {
	if ( 'KBS_Ticket_Sequential_Numbering_Migration' === $class ) {
		require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-ticket-sequential-numbering-migration.php';
	}
} // kbs_include_ticket_sequential_numbering_batch_processor

/***************************************************************************************************
 *
 * Version 1.2.9
 * Ticket Source Upgrade
 *
 **************************************************************************************************/

/**
 * Register the ticket source upgrade batch exporter
 *
 * @since	1.2.9
 */
function kbs_register_batch_ticket_source_migration() {
	add_action( 'kbs_batch_export_class_include', 'kbs_include_ticket_source_migration_batch_processor', 10, 1 );
} // kbs_register_batch_ticket_source_migration
add_action( 'kbs_register_batch_exporter', 'kbs_register_batch_ticket_source_migration', 10 );

/**
 * Loads the ticket source upgrade batch process if needed
 *
 * @since 	1.2.9
 * @param	string    $class	The class being requested to run for the batch export
 * @return	void
 */
function kbs_include_ticket_source_migration_batch_processor( $class ) {
	if ( 'KBS_Ticket_Sources_Migration' === $class ) {
		require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-ticket-source-migration.php';
	}
} // kbs_include_ticket_source_migration_batch_processor

/***************************************************************************************************
 *
 * Version 1.3
 * Ticket Department Upgrade
 *
 **************************************************************************************************/

/**
 * Register the ticket departments upgrade batch exporter
 *
 * @since	1.3
 */
function kbs_register_batch_ticket_department_migration() {
	add_action( 'kbs_batch_export_class_include', 'kbs_include_ticket_department_migration_batch_processor', 10, 1 );
} // kbs_register_batch_ticket_source_migration
add_action( 'kbs_register_batch_exporter', 'kbs_register_batch_ticket_department_migration', 10 );

/**
 * Loads the ticket department upgrade batch process if needed
 *
 * @since 	1.3
 * @param	string    $class	The class being requested to run for the batch export
 * @return	void
 */
function kbs_include_ticket_department_migration_batch_processor( $class ) {
	if ( 'KBS_Ticket_Department_Migration' === $class ) {
		require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-ticket-department-migration.php';
	}
} // kbs_include_ticket_department_migration_batch_processor

/***************************************************************************************************
 *
 * Version 1.3.3
 * Article Monthly View Count Upgrade
 *
 **************************************************************************************************/

/**
 * Register the article view count upgrade batch exporter
 *
 * @since	1.3.3
 */
function kbs_register_batch_article_monthly_count_migration() {
	add_action( 'kbs_batch_export_class_include', 'kbs_include_article_monthly_count_migration_batch_processor', 10, 1 );
} // kbs_register_batch_article_monthly_count_migration
add_action( 'kbs_register_batch_exporter', 'kbs_register_batch_article_monthly_count_migration', 10 );

/**
 * Loads the article monthly view count upgrade batch process if needed
 *
 * @since 	1.3.3
 * @param	string    $class	The class being requested to run for the batch export
 * @return	void
 */
function kbs_include_article_monthly_count_migration_batch_processor( $class ) {
	if ( 'KBS_Article_Monthly_Count_Migration' === $class ) {
		require_once KBS_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-article-monthly-count-migration.php';
	}
} // kbs_include_article_monthly_count_migration_batch_processor
