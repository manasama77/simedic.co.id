<?php
/**
 * SLA
 *
 * @package     KBS
 * @subpackage  Functions/SLA
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not to track SLA.
 *
 * @since	1.0
 * @return	bool	True to track, otherwise false.
 */
function kbs_track_sla()	{
	$track_sla = kbs_get_option( 'sla_tracking', false );

	return apply_filters( 'kbs_track_sla', $track_sla );
} // kbs_track_sla

/**
 * Determines the target response time for the ticket.
 *
 * @since	1.0
 * @param
 * @return	str		Date/Time for targetted response time.
 */
function kbs_calculate_sla_target_response()	{
	$now    = current_time( 'timestamp' );
	$target = kbs_get_option( 'sla_response_time' );

	if ( $target )	{
		$target_response = $target + $now;

		if ( kbs_use_support_hours() )	{
			$time_to_add          = $target;
			$work_hours_remaining = kbs_get_remaining_work_time();

			if ( ! $work_hours_remaining )	{
				$work_hours_remaining = 0;
			}

			$time_to_add = $time_to_add - $work_hours_remaining;

			if ( $time_to_add > 0 )	{ // We need to roll over to other work days

				for( $i = 0; $i <= 6; $i++ )	{
					$next             = strtotime( '+' . $i . ' day' );
					$next_working_day = kbs_get_next_working_day( $next );
					$working_hours    = kbs_get_working_time_in_day( $next_working_day );

					if ( $time_to_add - $working_hours <= 0 )	{
						$target_response = $next_working_day + $time_to_add;
						break;
					}

					$time_to_add = $time_to_add - $working_hours;

				}

			}

		}

		$target = date( 'Y-m-d H:i:s', $target_response );
	}
	
	return apply_filters( 'kbs_calculate_sla_target_response', $target );
} // kbs_calculate_sla_target_response

/**
 * Determines the target resolution time for the ticket.
 *
 * @since	1.0
 * @param
 * @return	str		Date/Time for targetted resolution time.
 */
function kbs_calculate_sla_target_resolution()	{
	$now    = current_time( 'timestamp' );
	$target = kbs_get_option( 'sla_resolve_time' );

	if ( $target )	{
		$target_resolve = $target + $now;

		if ( kbs_use_support_hours() )	{
			$time_to_add          = $target;
			$work_hours_remaining = kbs_get_remaining_work_time();

			if ( ! $work_hours_remaining )	{
				$work_hours_remaining = 0;
			}

			$time_to_add = $time_to_add - $work_hours_remaining;

			if ( $time_to_add > 0 )	{ // We need to roll over to other work days

				for( $i = 0; $i <= 30; $i++ )	{
					$next             = strtotime( '+' . $i . ' day' );
					$next_working_day = kbs_get_next_working_day( $next );
					$working_hours    = kbs_get_working_time_in_day( $next_working_day );

					if ( $time_to_add - $working_hours <= 0 )	{
						$target_resolve = $next_working_day + $time_to_add;
						break;
					}

					$time_to_add = $time_to_add - $working_hours;

				}

			}

		}

		$target = date( 'Y-m-d H:i:s', $target_resolve );
	}
	
	return apply_filters( 'kbs_calculate_sla_target_resolution', $target );
} // kbs_calculate_sla_target_resolution

/**
 * Log first respond value when an agent sends the first reply.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	void
 */
function kbs_set_sla_first_respond( $ticket_id )	{
	add_post_meta( $ticket_id, '_kbs_ticket_sla_first_respond', date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ), true );
	add_post_meta( $ticket_id, '_kbs_ticket_sla_first_respond_agent', get_current_user_id(), true );
} // kbs_calculate_sla_target_resolution
add_action( 'kbs_ticket_admin_reply', 'kbs_set_sla_first_respond', 10, 2 );

/**
 * Retrieve the tickets first response time value.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	void
 */
function kbs_get_sla_first_response( $ticket_id )	{
	return get_post_meta( $ticket_id, '_kbs_ticket_sla_first_respond', current_time( 'timestamp' ) );
} // kbs_calculate_sla_target_resolution

/**
 * Retrieve the target SLA response time.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	str	The target response date/time.
 */
function kbs_get_target_respond( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_target_respond();
} // kbs_get_target_respond

/**
 * Retrieve the target SLA resolve time.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	str	The target resolve date/time.
 */
function kbs_get_target_resolve( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_target_resolve();
} // kbs_get_target_resolve

/**
 * Whether or not the target SLA time has passed.
 *
 * @since	1.0
 * @param	obj|int	$ticket		The KBS Ticket object or a ticket ID
 * @param	str		$sla_target	The SLA target to check. 'response' or 'resolve'
 * @return	bool			False if still within SLA, otherwise true.
 */
function kbs_sla_has_passed( $ticket, $sla_target = 'response' ) {

	if ( ! kbs_track_sla() )	{
		return false;
	}

	if ( empty( $sla_target ) )	{
		return false;
	}

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
	}

	$sla = 'sla_' . $sla_target;

	if ( empty( $ticket->$sla ) )	{
		return false;
	}

	$return = false;
	$now    = current_time( 'timestamp' );
	$target = strtotime( $ticket->$sla );

	switch( $sla_target )	{
		case 'response':
			if ( ! empty( $ticket->first_response ) )	{
				if ( strtotime( $ticket->first_response ) > $target )	{
					$return = true;
				}
			} elseif ( $now > $target )	{
				$return = true;
			}
			break;

		case 'resolve':
				if ( $now > $target )	{
					$return = true;
				}
			break;
	}

	return (bool) apply_filters( 'kbs_sla_has_passed', $return, $ticket, $sla_target );
} // kbs_sla_has_passed

/**
 * Displays the SLA status icons.
 *
 * @since	1.0
 * @param	obj|int		$ticket		The KBS Ticket class object
 * @param	str			$sep		The seperator for the icons
 ( @param	bool		$echo		True to echo, false to return
 * @return	str|arr		If $echo is false, an array is returned
 */
function kbs_display_sla_status_icons( $ticket, $sep = '<br />', $echo = true )	{

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
	}

	$output = array(
		'response' => kbs_display_sla_response_status_icon( $ticket ),
		'resolve'  => kbs_display_sla_resolve_status_icon( $ticket )
	);

	if ( $echo )	{
		echo implode( $sep, $output );
	} else	{
		return $output;
	}

} // kbs_display_sla_status_icons

/**
 * Displays the SLA response status.
 *
 * @since	1.0
 * @param	obj|int		$ticket		The KBS Ticket class object
 * @return	str
 */
function kbs_display_sla_response_status_icon( $ticket )	{

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
	}

	$respond_class = '';
	$output        = '';

	if ( ! empty( $ticket->sla_respond ) )	{

		$target   = strtotime( $ticket->sla_respond );

		if ( ! empty( $ticket->first_response ) )	{

			$response = strtotime( $ticket->first_response );
			$text     = __( 'Responded', 'kb-support' );
			$diff     = human_time_diff( $response, $target );

			if ( $target < $response )	{
				$respond_class = '_over';
				$icon          = 'no';
				$title         = sprintf( __( 'Missed response by %s', 'kb-support' ), $diff );
			} else	{
				$icon  = 'yes';
				$title = sprintf( __( 'Responded %s before SLA expired', 'kb-support' ), $diff );
			}

		} elseif ( 'closed' == $ticket->status )	{

			if ( ! empty( $ticket->closed_date ) )	{
				$closed = $ticket->closed_date;
			} else	{
				$closed = $ticket->modified_date;
			}

			$response = strtotime( $closed );
			$text     = __( 'Closed', 'kb-support' );
			$diff     = human_time_diff( $response, $target );

			if ( $target < $response )	{
				$respond_class = '_over';
				$icon          = 'no';
				$title         = sprintf( __( 'Missed response by %s', 'kb-support' ), $diff );
			} else	{
				$icon  = 'yes';
				$title = sprintf( __( 'Responded %s before SLA expired', 'kb-support' ), $diff );
			}

		} else	{

			$now  = current_time( 'timestamp' );
			$text = __( 'No response', 'kb-support' );
			$diff = human_time_diff( $target, $now );
			$icon = 'clock';

			if ( $now > $target )	{
				$respond_class = '_over';
				$title         = sprintf( __( 'Missed response by %s', 'kb-support' ), $diff );
			} else	{
				$warn = kbs_get_option( 'sla_response_time_warn' );

				if ( ! empty( $warn ) )	{
					$remaining = $target - $now;
					$warn      = absint( $warn ) * ( 60 * 60 );
					if ( $remaining < $warn )	{
						$respond_class = '_warn';
					}
				}

				$title = sprintf( __( '%s left to respond', 'kb-support' ), $diff );
			}

		}

		$output .= '<span class="dashicons dashicons-' . $icon . ' kbs_sla_status' . $respond_class . '" title="' . $title . '">';
		$output .= '</span> ';
		$output .= $text;

	}

	return $output;

} // kbs_display_sla_response_status_icon

/**
 * Displays the SLA resolve status.
 *
 * @since	1.0
 * @param	obj|int		$ticket		The KBS Ticket class object
 * @return	str
 */
function kbs_display_sla_resolve_status_icon( $ticket )	{

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
	}

	$resolve_class = '';
	$output        = '';

	if ( ! empty( $ticket->sla_resolve ) )	{

		$target   = strtotime( $ticket->sla_resolve );

		if ( 'closed' == $ticket->status && ! empty( $ticket->closed_date ) )	{

			$resolved = strtotime( $ticket->closed_date );
			$text     = __( 'Resolved', 'kb-support' );
			$diff     = human_time_diff( $resolved, $target );

			if ( $target < $resolved )	{
				$resolve_class = '_over';
				$icon          = 'no';
				$title         = sprintf( __( 'Missed resolution by %s', 'kb-support' ), $diff );
			} else	{
				$icon  = 'yes';
				$title = sprintf( __( 'Resolved %s before SLA expired', 'kb-support' ), $diff );
			}

		} else	{

			$now  = current_time( 'timestamp' );
			$text = __( 'Unresolved', 'kb-support' );
			$diff = human_time_diff( $target, $now );
			$icon = 'clock';

			if ( $now > $target )	{
				$resolve_class = '_over';
				$title         = sprintf( __( 'Missed resolution by %s', 'kb-support' ), $diff );
			} else	{
				$warn = kbs_get_option( 'sla_resolve_time_warn' );

				if ( ! empty( $warn ) )	{
					$remaining = $target - $now;
					$warn      = absint( $warn ) * ( 60 * 60 );
					if ( $remaining < $warn )	{
						$resolve_class = '_warn';
					}
				}
				$title = sprintf( __( '%s left to resolve', 'kb-support' ), $diff );
			}

		}

		$output .= '<span class="dashicons dashicons-' . $icon . ' kbs_sla_status' . $resolve_class . '" title="' . $title . '">';
		$output .= '</span> ';
		$output .= $text;

	}

	return $output;

} // kbs_display_sla_resolve_status_icon

/**
 * Returns the time until, or since, SLA target response.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	str	$target		The SLA target to check. 'response' or 'resolve'
 * @return	str				Time until (or since) targetted response time.
 */
function kbs_time_to_target( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_sla_remain();
} // kbs_time_to_target_response

/* ------------------------------
	SUPPORT HOURS
-------------------------------*/
/**
 * Whether or not to use support hours.
 *
 * @since	1.0
 * @return	bool	True if we should use support hours, otherwise false
 */
function kbs_use_support_hours()	{
	$use_hours = kbs_get_option( 'define_support_hours', false );
	return apply_filters( 'kbs_use_support_hours', $use_hours );
} // kbs_use_support_hours

/**
 * Retrieve support hours.
 *
 * @since	1.0
 * @return	arr		Array of all working times
 */
function kbs_get_support_hours()	{
	if ( ! kbs_use_support_hours() )	{
		return false;
	}

	$hours = kbs_get_option( 'support_hours', false );

	return apply_filters( 'kbs_support_hours', $hours );
} // kbs_get_support_hours

/**
 * Retrieve the amount of working time in a given day.
 *
 * @since	1.0
 * @param	int|str	$date	The date to check. Can be a unix timestamp or string
 *							See PHP `date()` function for formatting strings
 * @return	int		Total number of seconds in working day
 */
function kbs_get_working_time_in_day( $date = false )	{

	if ( empty( $date ) )	{
		$date = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) );
	} elseif ( ! is_numeric( $date ) )	{
		$date = strtotime( date( 'Y-m-d', $date ) );
	}

	$day           = date( 'w', $date );
	$working_times = kbs_get_support_hours();
	$open_hour     = ! empty( $working_times[ $day ]['open']['hour'] ) ? $working_times[ $day ]['open']['hour'] : '-1';
	$open_minute   = ! empty( $working_times[ $day ]['open']['min'] )  ? $working_times[ $day ]['open']['min']  : '-1';
	$close_hour    = ! empty( $working_times[ $day ]['close']['hour'] ) ? $working_times[ $day ]['close']['hour'] : '-1';
	$close_minute  = ! empty( $working_times[ $day ]['close']['min'] )  ? $working_times[ $day ]['close']['min']  : '-1';

	if ( ! empty( $working_times[ $day ]['closed'] ) )	{
		return 0;
	}

	if ( '-1' == $open_hour || '-1' == $open_minute || '-1' == $close_hour || '-1' == $close_minute )	{
		return 0;
	}
	$open  = strtotime( date( 'Y-m-d ' . $open_hour  . ':' . $open_minute  . ':00', $date ) );
	$close = strtotime( date( 'Y-m-d ' . $close_hour . ':' . $close_minute . ':00', $date ) );

	$seconds_in_work_day = $close - $open;

	return apply_filters( 'kbs_remaining_work_time_in_day', $seconds_in_work_day );

} // kbs_get_working_time_in_day

/**
 * Retrieve remaining working time for today.
 *
 * @since	1.0
 * @return	int|bool	Number of seconds of work time remaining, or false if already closed
 */
function kbs_get_remaining_work_time( $day = 'w' )	{

	$day = date( $day );
	$working_times = kbs_get_support_hours();
	$now           = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
	$close_hour    = ! empty( $working_times[ $day ]['close']['hour'] ) ? $working_times[ $day ]['close']['hour'] : '-1';
	$close_minute  = ! empty( $working_times[ $day ]['close']['min'] )  ? $working_times[ $day ]['close']['min']  : '-1';

	if ( ! empty( $working_times[ $day ]['closed'] ) )	{
		return false;
	}

	if ( '-1' == $close_hour || '-1' == $close_minute )	{
		return false;
	}

	$close         = new DateTime();

	$close->setTime( $close_hour, $close_minute );
	$close_time = $close->format( 'U' );
	$return     = false;

	if ( strtotime( $now ) < $close_time )	{
		$return = $close_time - strtotime( $now );
	}

	return apply_filters( 'kbs_remaining_work_time', $return );

} // kbs_get_remaining_work_time

/**
 * Retrieve the next working day.
 *
 * @since	1.0
 * @param	int		$from	The timestamp from which to find the next working day
 * @return	int		Unix timestamp representation of the next working day start time
 */
function kbs_get_next_working_day( $from = false )	{

	if ( ! $from || ! is_numeric( $from ) )	{
		$from = current_time( 'timestamp' );
	}

	$current       = date( 'Y-m-d', $from );
	$working_times = kbs_get_support_hours();
	$next_open     = false;

	for( $i = 1; $i <= 7; $i++ )	{
		$current = date( 'Y-m-d', strtotime( $current . ' +1 day' ) );
		$hour    = false;
		$min     = false;

		if ( ! empty( $working_times[ date( 'w', strtotime( $current ) ) ]['closed'] ) )	{
			continue;
		}

		if ( '-1' != $working_times[ date( 'w', strtotime( $current ) ) ]['open']['hour'] )	{
			$hour = $working_times[ date( 'w', strtotime( $current ) ) ]['open']['hour'];
		}
		if ( '-1' !=  $working_times[ date( 'w', strtotime( $current ) ) ]['open']['min'] )	{
			$min = $working_times[ date( 'w', strtotime( $current ) ) ]['open']['min'];
		}

		if ( ! empty( $hour ) && ! empty( $min ) )	{
			$next_open = strtotime( $current . ' ' . $hour . ':' . $min );
			break;
		}

	}
	
	return $next_open;

} // kbs_get_next_working_day
