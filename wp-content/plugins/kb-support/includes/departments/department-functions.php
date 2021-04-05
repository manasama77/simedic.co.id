<?php
/**
 * Functions for departments
 *
 * @package     KBS
 * @subpackage  Functions/Departments
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not departments are enabled.
 * @since	1.2
 * @return	bool
 */
function kbs_departments_enabled()	{
	return kbs_get_option( 'enable_departments', false );
} // kbs_departments_enabled

/**
 * Retrieve all departments.
 *
 * Simple wrapper for get_terms()
 *
 * @since	1.2
 * @param	array	$args		See WP_Term_Query::__construct
 * @return	array	Array of department WP_Term objects or false if no departments exist
 */
function kbs_get_departments( $args = array() )	{
	$defaults = array(
		'hide_empty' => false
	);

	$args = wp_parse_args( $args, $defaults );
	$args['taxonomy'] = 'department';

	$departments = get_terms( $args );

	if ( empty( $departments ) || is_wp_error( $departments ) )	{
		return false;
	}

	return $departments;
} // kbs_get_departments

/**
 * Retrieve a department.
 *
 * Simple wrapper for get_term()
 *
 * @since	1.2
 * @param	int		$department_id	Department term ID
 * @return	object	WP_Term object
 */
function kbs_get_department( $department_id )	{

	$department = get_term( $department_id, 'department' );

	return $department;
} // kbs_get_department

/**
 * Department options.
 *
 * @since	1.2
 * @return	arr		Array of department options.
 */
function kbs_get_department_options()	{
	$options     = array();
	$departments = kbs_get_departments();

	if ( $departments )	{
		foreach( $departments as $department )	{
			$options[ absint( $department->term_id ) ] = $department->name;
		}
	}

	$options = apply_filters( 'kbs_department_options', $options );

	return $options;
} // kbs_get_department_options

/**
 * Retrieve all department agents.
 *
 * @since	1.2
 * @param	int		$department_id	The department (term) ID
 * @return	array	Array of department member ID's
 */
function kbs_get_department_agents( $department_id = '' )	{

	$agents = array();

	if ( ! empty( $department_id ) )	{
		$agents = get_term_meta( $department_id, 'kbs_department_agents', true );
		if ( ! $agents )	{
			$agents = array();
		}
	}

	return $agents;

} // kbs_get_department_agents

/**
 * Retrieve the count of a departments agents.
 *
 * @since	1.2
 * @param	int		$department_id	The department (term) ID
 * @return	int		Count of agents
 */
function kbs_get_department_agent_count( $department_id )	{
	return count( kbs_get_department_agents( $department_id ) );
} // kbs_get_department_agent_count

/**
 * Whether or not an agent is in a department.
 *
 * @since	1.2
 * @param	int		$department_id	The department (term) ID
 * @param	int		$agent_id		The agent (user) ID
 * @return	bool
 */
function kbs_agent_is_in_department( $department_id, $agent_id = '' )	{
	if ( empty( $agent_id ) )	{
		if ( ! is_user_logged_in() )	{
			return false;
		}
		$agent_id = get_current_user_id();
	}

	return in_array( $agent_id, kbs_get_department_agents( $department_id ) );
} // kbs_agent_is_in_department

/**
 * Add an agent to a department.
 *
 * @since	1.2
 * @param	int			$department_id	The department (term) ID
 * @param	int			$agent_id		The agent (user) ID
 * @return	array|bool	Array of department agents on success, or false
 */
function kbs_add_agent_to_department( $department_id, $agent_id = '' )	{
	if ( empty( $agent_id ) )	{
		if ( ! is_user_logged_in() )	{
			return false;
		}
		$agent_id = get_current_user_id();
	}

	$agents = kbs_get_department_agents( $department_id );

	if ( ! in_array( $agent_id, $agents ) )	{
		$agents[] = absint( $agent_id );
		update_term_meta( $department_id, 'kbs_department_agents', $agents );
	}

	return $agents;
} // kbs_add_agent_to_department

/**
 * Add an agent to a department.
 *
 * @since	1.2
 * @param	int			$department_id	The department (term) ID
 * @param	int			$agent_id		The agent (user) ID
 * @return	array|bool	Array of department agents on success, or false
 */
function kbs_remove_agent_from_department( $department_id, $agent_id = '' )	{
	if ( empty( $agent_id ) )	{
		if ( ! is_user_logged_in() )	{
			return false;
		}
		$agent_id = get_current_user_id();
	}

	$agents = kbs_get_department_agents( $department_id );

	if ( ( $key = array_search( $agent_id, $agents ) ) !== false )	{
		unset( $agents[ $key ] );
		update_term_meta( $department_id, 'kbs_department_agents', $agents );
	}

	return $agents;
} // kbs_remove_agent_from_department

/**
 * Retrieves the department from the ticket
 *
 * @since   1.2
 * @param   int     $ticket_id  The ticket ID
 * @return  array   Array containing department term object or false
 */
function kbs_get_department_for_ticket( $ticket_id )    {
    $terms = wp_get_post_terms( $ticket_id, 'department' );
    if ( ! empty( $terms ) && !is_wp_error($terms) )    {
        return $terms[0];
    }

    return false;
} // kbs_get_department_for_ticket

