<?php
/**
 * Admin functions for departments
 *
 * @package     KBS
 * @subpackage  Admin/Functions/Departments
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove the slug column
 * Add the agent count column.
 *
 * @since	1.2
 * @param	array	$columns	Array of table columns
 * @return	Array of table columns
 */
function kbs_department_taxonomy_columns( $columns )	{
	if ( isset( $columns['slug'] ) )	{
		unset( $columns['slug'] );
	}

	$columns['agents'] = __( 'Agents', 'kb-support' );

	return $columns;
} // kbs_department_taxonomy_columns
add_filter( 'manage_edit-department_columns', 'kbs_department_taxonomy_columns' );

/**
 * Render the output for the department agents column.
 *
 * @since	1.2
 * @param	string	$content		Column output
 * @param	string	$column_name	Name of current column
 * @param	int		$term_id		The term ID
 * @return	Column output
 */
function kbs_render_department_taxonomy_agent_column( $content, $column_name, $term_id )	{
    if ( 'agents' == $column_name )	{
        $content = kbs_get_department_agent_count( $term_id );
    }

	return $content;
} // kbs_render_department_taxonomy_agent_column
add_filter( 'manage_department_custom_column', 'kbs_render_department_taxonomy_agent_column', 10, 3 );

/**
 * Adds the agent selection input to the Edit Department page.
 *
 * @since	1.2
 * @param	object	$tag		Current taxonomy term object
 * @param	string	$taxonomy	Current taxonomy slug
 * @return	void
 */
function kbs_edit_department_agent_options( $tag, $taxonomy )	{

	$agents = array();

	if ( is_object( $tag ) )	{
		$agents = kbs_get_department_agents( $tag->term_id );
	}

	?>
    <tr class="form-field term-agents-wrap">
        <th scope="row"><label for="kbs_agents"><?php _e( 'Department Agents', 'kb-support' ); ?></label></th>
        <td><?php echo KBS()->html->agent_dropdown( array(
			'options'          => array(),
			'name'             => 'kbs_agents',
			'show_option_all'  => false,
			'show_option_none' => false,
            'selected'         => $agents,
            'chosen'           => true,
            'multiple'         => true,
            'placeholder'      => __( 'Select agents', 'kb-support' ),
		) ); ?></td>
    </tr>
    <?php
} // kbs_edit_department_agent_options
add_action( 'department_edit_form_fields', 'kbs_edit_department_agent_options', 10, 2 );

/**
 * Adds the agent selection input to the Add Department page.
 *
 * @since	1.2
 * @param	object	$tag		Current taxonomy term object
 * @param	string	$taxonomy	Current taxonomy slug
 * @return	void
 */
function kbs_add_department_agent_options( $taxonomy )	{
	?>
    <div class="form-field term-agents-wrap">
    	<label for="kbs_agents"><?php _e( 'Department Agents', 'kb-support' ); ?></label>
        <?php echo KBS()->html->agent_dropdown( array(
			'options'          => array(),
			'name'             => 'kbs_agents',
			'show_option_all'  => false,
			'show_option_none' => false,
            'selected'         => array(),
            'chosen'           => true,
            'multiple'         => true,
            'placeholder'      => __( 'Select agents', 'kb-support' ),
		) ); ?>
    </div>
    <?php
} // kbs_department_agents_list
add_action( 'department_add_form_fields', 'kbs_add_department_agent_options', 10 );

/**
 * Saves the agents for the department.
 *
 * @since	1.2
 * @param	int		$term_id	Current term ID
 * @return	void
 */
function kbs_save_department_agents( $term_id )	{

	$agents = array();

	if ( isset( $_POST['kbs_agents'] ) )	{

		$agents = array_map( 'absint', $_POST['kbs_agents'] );

	} 

	update_term_meta( $term_id, 'kbs_department_agents', $agents );

} // kbs_save_department_agents
add_action( 'edited_department', 'kbs_save_department_agents', 10 );
add_action( 'create_department', 'kbs_save_department_agents', 10 );

/**
 * When a department term is deleted, remove ticket associations from meta table.
 *
 * @since   1.0
 * @param   int     $term_id      Term ID.
 * @param   int     $tt_id        Term taxonomy ID.
 * @param   mixed   $deleted_term Copy of the already-deleted term, in the form specified
 *                              by the parent function. WP_Error otherwise.
 * @param   array   $object_ids   List of term object IDs.
 */
function kbs_ticket_cleanup_ticket_department_meta_action( $term_id, $tt_id, $deleted_term, $object_ids )   {
    global $wpdb;

    $wpdb->query( 
        $wpdb->prepare( 
            "
            DELETE FROM $wpdb->postmeta
            WHERE meta_key = %s
            AND meta_value = %d
            ",
	        '_kbs_ticket_department', $term_id
        )
    );
} // kbs_ticket_cleanup_ticket_department_meta_action
add_action( 'delete_department', 'kbs_ticket_cleanup_ticket_department_meta_action', 10, 4 );
