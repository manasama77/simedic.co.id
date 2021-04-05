<?php
/**
 * Template Functions
 *
 * @package     KBS
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve page ID's.
 *
 * Used for the submission and tickets pages.
 *
 * @since	1.1
 * @param	str		$page	The page to retrieve the ID for
 * @return	int		The page ID
 */
function kbs_get_page_id( $page )	{
	$page = apply_filters( 'kbs_get_' . $page . '_page_id', kbs_get_option( $page . '_page' ) );

	return $page ? absint( $page ) : -1;
} // kbs_get_page_id

/**
 * Display front end notices.
 *
 * @since	1.0
 * @return	str
 */
function kbs_notices()	{

	if( ! isset( $_GET, $_GET['kbs_notice'] ) )	{
		return;
	}

	echo kbs_display_notice( $_GET['kbs_notice'] );

} // kbs_display_notice
add_action( 'kbs_notices', 'kbs_notices' );

/**
 * The form submit button label.
 *
 * @since	1.0
 * @return	str		The label for the form submit button.
 */
function kbs_get_form_submit_label()	{
    $label = kbs_get_option( 'form_submit_label', sprintf( __( 'Submit %s', 'kb-support' ), kbs_get_ticket_label_singular() ) );
	return apply_filters( 'kbs_form_submit_label', $label );
} // kbs_get_form_submit_label

/**
 * The ticket reply submit button label.
 *
 * @since	1.0
 * @return	str		The label for the ticket reply form submit button.
 */
function kbs_get_ticket_reply_label()	{
	return kbs_get_option( 'ticket_reply_label', __( 'Reply', 'kb-support' ) );
} // kbs_get_ticket_reply_label

/**
 * Output the hidden form fields.
 *
 * @since	1.0
 * @param	$form_id	The ID of the form on display.
 * @return	str
 */
function kbs_render_hidden_form_fields( $form_id )	{
    global $wp;

    $page_url = kbs_get_current_page_url();

	$hidden_fields = array(
		'kbs_form_id'           => $form_id,
		'kbs_honeypot'          => '',
		'redirect'              => $page_url,
		'kbs_nonce'             => wp_create_nonce( 'kbs_submission_nonce' ),
		'action'                => 'kbs_validate_ticket_form',
        'kbs_submission_origin' => $page_url
	);

	$hidden_fields = apply_filters( 'kbs_form_hidden_fields', $hidden_fields, $form_id );

	ob_start(); ?>

	<?php foreach( $hidden_fields as $key => $value ) : ?>
    	<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
    <?php endforeach; ?>

	<?php wp_nonce_field( 'kbs-form-validate', 'kbs_log_ticket' ); ?>

    <?php echo ob_get_clean();
} // kbs_render_hidden_form_fields

/**
 * Output the hidden reply form fields.
 *
 * @since	1.0
 * @global	obj		$current_user	If logged in, the current user object
 * @param	int		$ticket_id		The ID of the form on display.
 * @return	arr		Hidden form fields for reply form
 */
function kbs_render_hidden_reply_fields( $ticket_id )	{
	global $current_user;

	$current_page  = kbs_get_current_page_url();
	remove_query_arg( array( 'kbs_notice', 'ticket' ), $current_page );

	$hidden_fields = array(
		'kbs_ticket_id'  => $ticket_id,
		'kbs_honeypot'   => '',
		'redirect'       => add_query_arg( 'ticket', $_GET['ticket'], $current_page ),
		'action'         => 'kbs_validate_ticket_reply_form'
	);

	// If logged in we don't need to display the email input but we still need to capture it
	// for form validation
	if ( is_user_logged_in() )	{
		$hidden_fields['kbs_confirm_email'] = $current_user->user_email;
	}

	$hidden_fields = apply_filters( 'kbs_reply_hidden_fields', $hidden_fields, $ticket_id );

	ob_start(); ?>

	<?php foreach( $hidden_fields as $key => $value ) : ?>
    	<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
    <?php endforeach; ?>

	<?php wp_nonce_field( 'kbs-reply-validate', 'kbs_ticket_reply' ); ?>

    <?php echo ob_get_clean();
} // kbs_render_hidden_reply_fields

/**
 * Before Article Content
 *
 * Adds an action to the beginning of kb article post content that can be hooked to
 * by other functions.
 *
 * @since	1.0
 * @global	$post
 *
 * @param	str		$content	The the_content field of the kb article object
 * @return	str		The content with any additional data attached
 */
function kbs_before_article_content( $content ) {
	global $post;

	$kb_post = KBS()->KB->post_type;

	if ( $post && $post->post_type == $kb_post && is_singular( $kb_post ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'kbs_before_article_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
} // kbs_before_article_content
add_filter( 'the_content', 'kbs_before_article_content' );

/**
 * After Article Content
 *
 * Adds an action to the end of kb article post content that can be hooked to by
 * other functions.
 *
 * @since	1.0
 * @global	$post
 *
 * @param	str		$content	The the_content field of the kb article object
 * @return	str		The content with any additional data attached
 */
function kbs_after_article_content( $content ) {
	global $post;

	$kb_post = KBS()->KB->post_type;

	if ( $post && $kb_post == $post->post_type && is_singular( $kb_post ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'kbs_after_article_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
} // kbs_after_article_content
add_filter( 'the_content', 'kbs_after_article_content', 15 );

/**
 * Increment the post view count for articles when accessed.
 *
 * @since	1.0
 * @return	void
 */
function kbs_article_maybe_increment_views()	{
	// This hook causes duplicate counting!
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

	if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) )	{
		return;
	}

	if ( KBS()->KB->post_type != get_post_type() || ! is_singular( KBS()->KB->post_type ) )	{
		return;
	}

	if ( ! kbs_count_agent_article_views() )	{
		if ( is_user_logged_in() && kbs_is_agent( get_current_user_id() ) )	{
			return;
		}
	}

	$article_id = get_the_ID();

	if ( ! kbs_article_user_can_access( $article_id ) )	{
		return;
	}

	kbs_increment_article_view_count( $article_id );

} // kbs_article_maybe_increment_views
add_action( 'wp', 'kbs_article_maybe_increment_views' );

/**
 * Returns the path to the KBS templates directory
 *
 * @since	1.0
 * @return 	str
 */
function kbs_get_templates_dir() {
	return KBS_PLUGIN_DIR . 'templates';
} // kbs_get_templates_dir

/**
 * Returns the URL to the KBS templates directory
 *
 * @since	1.0
 * @return	str
 */
function kbs_get_templates_url() {
	return KBS_PLUGIN_URL . 'templates';
} // kbs_get_templates_url

/**
 * Retrieves a template part
 *
 * @since	1.0
 *
 * Taken from bbPress
 *
 * @param	str		$slug
 * @param	str		$name 	Default null
 * @param	bool	$load
 *
 * @return	str
 *
 * @uses kbs_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */
function kbs_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();

	if ( isset( $name ) )	{
		$templates[] = $slug . '-' . $name . '.php';
	}

	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'kbs_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return kbs_locate_template( $templates, $load, false );
} // kbs_get_template_part

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @since	1.0
 *
 * @param	str|arr		$template_names		Template file(s) to search for, in order.
 * @param	bool		$load				If true the template file will be loaded if it is found.
 * @param	bool		$require_once		Whether to require_once or require. Default true.
 *   Has no effect if $load is false.
 * @return atr			The template filename if one is located.
 */
function kbs_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach( kbs_get_theme_template_paths() as $template_path ) {

			if( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if ( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )	{
		load_template( $located, $require_once );
	}

	return $located;
} // kbs_locate_template

/**
 * Returns a list of paths to check for template locations
 *
 * @since	1.0
 * @return mixed|void
 */
function kbs_get_theme_template_paths() {

	$template_dir = kbs_get_theme_template_dir_name();

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => kbs_get_templates_dir()
	);

	$file_paths = apply_filters( 'kbs_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
} // kbs_get_theme_template_paths

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the kbs_templates_dir filter.
 *
 * @since	1.0
 * @return	str
*/
function kbs_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'kbs_templates_dir', 'kbs_templates' ) );
} // kbs_get_theme_template_dir_name

/**
 * Adds KBS Version to the <head> tag
 *
 * @since	1.0
 * @return	void
*/
function kbs_version_in_header(){
	echo '<meta name="generator" content="KB Support v' . KBS_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'kbs_version_in_header' );
