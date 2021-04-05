<?php
/**
 * File Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Checks if file uploads are enabled
 *
 * @since	1.0
 * @return	bool	$ret	True if file uploads are enabled, false otherwise
 */
function kbs_file_uploads_are_enabled() {
	$return = false;

	if ( kbs_get_max_file_uploads() > 0 )	{
		$return = true;
	}
	
	return (bool) apply_filters( 'kbs_file_uploads', $return );
} // kbs_file_uploads_are_enabled

/**
 * Retrieve the maximum number of files which can be uploaded.
 *
 * @since	1.0
 * @return	int		The maximum number of files that can be uploaded
 */
function kbs_get_max_file_uploads() {
	return (int) kbs_get_option( 'file_uploads', 0 );
} // kbs_get_max_file_uploads

/**
 * Set Upload Directory
 *
 * Sets the upload dir to kbs.
 *
 * @since	1.0
 * @return	arr		Upload directory information.
 */
function kbs_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) )	{

		$time             = current_time( 'mysql' );
		$y                = substr( $time, 0, 4 );
		$m                = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";

	}

	$upload['subdir'] = '/kbs' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	
	return apply_filters( 'kbs_set_upload_dir', $upload );

} // kbs_set_upload_dir

/**
 * Retrieve the current upload DIR for tickets.
 *
 * @since	1.0
 * @param	$key	The array key to return or false for the entire array.
 * @return	str
 */
function kbs_get_upload_dir( $key = false )	{
	add_filter( 'upload_dir', 'kbs_set_upload_dir' );

	$upload_dir = wp_upload_dir();

	if ( $key && isset( $upload_dir[ $key ] ) )	{
		$dir = $upload_dir[ $key ];
	} else	{
		$dir = $upload_dir;
	}

	return $dir;
} // kbs_get_upload_dir

/**
 * Change Tickets Upload Directory.
 *
 * This function works by hooking on the WordPress Media Uploader
 * and moving the uploaded files that are used for KBS to a kbs
 * directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/kbs/{year}/{month}.
 *
 * @since	1.0
 * @global	$pagenow
 * @param   array   $upload_path    DIR path data for uploads
 * @return	array   DIR path data for uploads
 */
function kbs_change_tickets_upload_dir( $upload_path ) {
    global $pagenow;

    $ticket_dir = false;
    $referrer   = wp_get_referer();
    $filtered   = array( 'post_type=kbs_ticket', 'post.php?post=' );
    $bail       = true;

    foreach( $filtered as $url_part )   {
        if ( false !== strpos( $referrer, $url_part ) )   {
            $bail = false;
            break;
        }
    }

    if ( $bail )    {
        return $upload_path;
    }

    if ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) {
        if ( false !== strpos( $referrer, 'post_type=kbs_ticket' ) ) {
            $ticket_dir = true;
        } elseif ( false !== strpos( $referrer, 'post.php?post=' ) ) {
            $url_parts = parse_url( $referrer );
            parse_str( $url_parts['query'], $url_query_string );
            $post_id = $url_query_string['post'];

            if ( 'kbs_ticket' == get_post_type( $post_id ) )    {
                $ticket_dir = true;
            }
        }

        if ( $ticket_dir )  {
            $upload_path = kbs_set_upload_dir( $upload_path );
        }
    }

    return $upload_path;
} // kbs_change_tickets_upload_dir
add_filter( 'upload_dir', 'kbs_change_tickets_upload_dir' );

/**
 * Sets the enctype for file upload forms.
 *
 * @since	1.0
 * @return	str
 */
function kbs_maybe_set_enctype() {
	if ( kbs_file_uploads_are_enabled() )	{
		$output = ' enctype="multipart/form-data"';
		
		echo apply_filters( 'kbs_maybe_set_enctype', $output );
	}
} // kbs_maybe_set_enctype

/**
 * Retrieves an array of default file extensions that can be uploaded via the submission and reply forms.
 *
 * @since	1.0
 * @return	str		String of default file extensions
 */
function kbs_get_default_file_types()	{
	$file_types  = '.jpg, .jpeg, .jpe, .gif, .png, .bmp, .tif, .tiff, .txt, .csv, .css, .htm, .html, .rtf, .zip, .tar, .gz, .gzip, .7z';
	$file_types .= '.doc, .ppt, .xls, .docx, .xlsx, .pptx, .odt, .odp, .ods, .odg, .wp, .wpd, .numbers, .pages';

	return $file_types;
} // kbs_get_allowed_file_types

/**
 * Retrieves an array of file extensions that can be uploaded via the submission and reply forms.
 *
 * @since	1.0
 * @return	str		Allowed file extensions
 */
function kbs_get_allowed_file_types()	{
	return kbs_get_option( 'file_extensions' );
} // kbs_get_allowed_file_types

/**
 * Sanitize file extensions.
 *
 * Creates an array of file extensions when setting option is updated.
 *
 * @since	1.0
 * @param	str		$value		Comma seperated list of allowed file extensions
 * @return	arr		Array of allowed file extensions
 */
function kbs_sanitize_file_extensions( $value )	{
	$extensions = explode( ',', $value );

	// Make sure extensions are preceeded with a dot
	foreach( $extensions as $array_key => $extension )	{
		if ( '.' != substr( trim( $extension ), 0, 1 ) )	{
			$extensions[ $array_key ] = ' .' . trim( $extension );
		}
	}

	$value = implode( ',', $extensions );

	return $value;
} // kbs_sanitize_file_extensions
add_filter( 'kbs_settings_sanitize_file_extensions', 'kbs_sanitize_file_extensions' );

/**
 * Whether or not there are files to upload.
 *
 * @since	1.0
 * @param	str		$name	The file name
 * @return	bool	True if there are files, or false.
 */
function kbs_has_files_to_upload( $name )	{
	return ( ! empty( $_FILES ) ) && isset( $_FILES[ $name ] );
} // kbs_has_files_to_upload

/**
 * Whether or not files should be attached to emails.
 *
 * @since   1.1.9
 * @return  bool
 */
function kbs_send_files_as_attachments()    {
    return kbs_get_option( 'attach_files', false );
} // kbs_send_files_as_attachments

/**
 * Attach files to a ticket or reply.
 *
 * @since	1.0
 * @param	arr	$attachment		$_FILES
 * @param	int	$ticket_id		The ticket/reply ID
 * @return	int	The attachment ID.
 */
function kbs_attach_file_to_ticket( $attachment, $ticket_id )	{

	if ( ! kbs_file_uploads_are_enabled() )	{
		return false;
	}

	add_filter( 'upload_dir', 'kbs_set_upload_dir' );

	if ( $_FILES[ $attachment ]['error'] !== UPLOAD_ERR_OK )	{
		return false;
	}
 
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
 
	$attach_id = media_handle_upload( $attachment, $ticket_id );
 
	return $attach_id;

} // kbs_attach_file_to_ticket

/**
 * Attach files to a ticket reply.
 *
 * @since	1.0
 * @global	$_FILES
 * @param	int		$reply_id	The ticket reply ID to which were attaching.
 * @return	void
 */
function kbs_attach_files_to_reply( $reply_id )	{
	$attachments = $_FILES['kbs_files'];
	$attachments = ! is_array( $attachments ) ? array( $attachments ) : $attachments;

	foreach( $attachments['name'] as $key => $value )	{
		if ( $attachments['name'][ $key ] )	{
			$attachment = array(
				'name'     => $attachments['name'][ $key ],
				'type'     => $attachments['type'][ $key ],
				'tmp_name' => $attachments['tmp_name'][ $key ],
				'error'    => $attachments['error'][ $key ],
				'size'     => $attachments['size'][ $key ]
			);

			$_FILES = array( 'kbs_reply_attachments' => $attachment );

			foreach( $_FILES as $attachment => $array )	{
				$file_id = kbs_attach_file_to_ticket( $attachment, $reply_id );
			}
		}
	}
} // kbs_attach_files_to_reply

/**
 * Determine if the ticket (or reply) has files attached.
 *
 * @since	1.0
 * @param	int		$ticket_id	The Ticket/Reply ID
 * @return	mixed	Object of attachment posts if they exist, otherwise false.
 */
function kbs_ticket_has_files( $ticket_id )	{

	$files = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'post_parent'    => $ticket_id,
			'post_status'    => array( 'inherit' )
		)
	);
	
	return apply_filters( 'kbs_ticket_has_files', $files, $ticket_id );
	
} // kbs_ticket_has_files

/**
 * Adds attachments to ticket emails.
 *
 * @since   1.1.9
 * @param   int     id              Ticket or Reply ID
 * @return  arr     Array of files to attach to email.
 */
function kbs_maybe_attach_files_to_email( $id ) {

    $attachments = array();

    if ( ! empty( $id ) && kbs_send_files_as_attachments() )    {

		if ( 'none' === kbs_get_email_template() )	{

			$files = kbs_get_attachments_from_inline_content( $id );

			if ( ! empty( $files ) )	{
				$attachments = $files;
			}

		} else	{

			$files = kbs_ticket_has_files( $id );

			if ( $files )   {
				foreach ( $files as $file ) {
					$attachments[] = get_attached_file( $file->ID );
				}
			}

		}

    }

    return $attachments;

} // kbs_maybe_attach_files_to_email

/**
 * Retrieves inline images and links within email content when plain text emails are defined.
 *
 * Plain text emails have all HTML tags stripped and therefore inline images etc. are lost during email.
 * This function searches the content and checks for images and other links. It then verifies they exist
 * within the WordPress media gallery and attaches them as files to the outgoing email.
 *
 * We rely upon the removal of the content from KBS_Email->build_email.
 *
 * @since	1.1.10
 * @param	int			$id		Ticket or Reply ID
 * @return	arr			Array of files to attach
 */
function kbs_get_attachments_from_inline_content( $id )	{

	$attachments = array();
	$content     = get_post_field( 'post_content', $id, 'raw' );

	if ( ! empty( $content ) )	{

		$pattern = '/<img.*?src="([^"]*)".*?\/?>|<a.*?href="([^"]*)".*?\/?>/';
		$pattern = apply_filters( 'kbs_get_attachments_from_inline_content_pattern', $pattern );

		preg_match_all( $pattern, $content, $urls, PREG_PATTERN_ORDER );
		
		if ( ! empty( $urls ) )	{
			$all_urls = array_merge( $urls[1], $urls[2] );
			$all_urls = array_filter( $all_urls );
		}

		if ( ! empty( $all_urls ) )	{
			foreach( $all_urls as $url )	{
				$file_path = kbs_get_attachment_path_from_url( $url );

				if ( $file_path )	{
					$attachments[] = $file_path;
				}
			}
		}

	}

	return $attachments;
} // kbs_get_attachments_from_inline_content

/**
 * Retrieve an attachment's full path from its URL.
 *
 * @since	1.1.10
 * @param	str		$url	The URL path to the file
 * @return	int		The attachment path
 */
function kbs_get_attachment_path_from_url( $url )	{

	$file_path   = false;
	$upload_dirs = array( wp_upload_dir(), kbs_get_upload_dir() );
    $upload_dirs = apply_filters( 'kbs_get_attachment_upload_dirs', $upload_dirs, $url );

	foreach ( $upload_dirs as $upload_dir )	{

		if ( false !== strpos( $url, $upload_dir['baseurl'] . '/' ) )	{ // Is URL in uploads directory?

			$file = basename( $url );

			$query_args = array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'fields'      => 'ids',
				'meta_query'  => array(
					'relation' => 'OR',
					array(
						'value'   => $file,
						'compare' => 'LIKE',
						'key'     => '_wp_attachment_metadata',
					),
					array(
						'value'   => $file,
						'compare' => 'LIKE',
						'key'     => '_wp_attached_file',
					)
				)
			);

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {

				foreach ( $query->posts as $post_id ) {

					$meta = wp_get_attachment_metadata( $post_id );

					if ( $meta )	{
						$original_file       = basename( $meta['file'] );
						$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
					} else	{
						$original_file       = basename( get_attached_file( $post_id ) );
						$cropped_image_files = array();
					}

					if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
						$file_path = $original_file;
						$file_path = trailingslashit( $upload_dir['path'] ) . $file_path;
						return $file_path;
					}

				}

			}

		}

	}

	return $file_path;
} // kbs_get_attachment_path_from_url

/**
 * Create array of files for display on ticket manager page.
 *
 * @since	1.2.6
 * @param	array	$files	Array of files attached to ticket
 * @return	array	Array of file links
 */
function kbs_get_ticket_files_list( $files = array() )	{
	$output = array();

	foreach( $files as $file )	{
		$file_url  = wp_get_attachment_url( $file->ID );
		$file_name = basename( get_attached_file( $file->ID ) );

		$output[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			$file_url,
			$file_name
		);
    }

	return $output;
} // kbs_get_ticket_files_list
