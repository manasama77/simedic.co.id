<?php
/**
 * Tools
 *
 * Functions used for displaying KBS tools menu page.
 *
 * @package     KBS
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Creates the admin submenu page for tools
 *
 * @since	1.1.8
 * @return	void
 */
function kbs_add_tools_menu_link() {

	global $kbs_tools_page;

	$kbs_tools_page = add_submenu_page(
        'edit.php?post_type=kbs_ticket',
        __( 'Tools', 'kb-support' ),
        __( 'Tools', 'kb-support' ),
        'manage_ticket_settings',
        'kbs-tools',
        'kbs_tools_page'
    );

} // kbs_add_tools_menu_link
add_action( 'admin_menu', 'kbs_add_tools_menu_link', 99 );

/**
 * Tools
 *
 * Display the tools page.
 *
 * @since       1.0
 * @return      void
 */
function kbs_tools_page()	{

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

	?>
    <div class="wrap">
		<h1 class="nav-tab-wrapper">
			<?php
			foreach( kbs_get_tools_page_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$tab_url = remove_query_arg( array(
					'kbs-message'
				), $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
			}
			?>
		</h1>
        <div class="metabox-holder">
        	<?php do_action( 'kbs_tools_tab_' . $active_tab ); ?>
        </div>
    </div>
    <?php

} // kbs_tools_page

/**
 * Define the tabs for the tools page.
 *
 * @since	1.0
 * @return	array
 */
function kbs_get_tools_page_tabs()	{

	$tabs = array(
		'general'     => __( 'General', 'kb-support' ),
		'system_info' => __( 'System Info', 'kb-support' ),
        'import'      => __( 'Import', 'kb-support' ),
        'export'      => __( 'Export', 'kb-support' )
	);

	return apply_filters( 'kbs_tools_page_tabs', $tabs );

} // kbs_get_tools_page_tabs

/**
 * Display the ban emails tab
 *
 * @since       1.0
 * @return      void
 */
function kbs_tools_banned_emails_display() {

	if ( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}

	do_action( 'kbs_tools_banned_emails_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Banned Emails', 'kb-support' ); ?></span></h3>
		<div class="inside">
			<p><?php printf( __( 'Emails addresses and domains entered into the box below will not be able log %s. To ban an entire domain, enter the domain starting with "@".', 'kb-support' ), kbs_get_ticket_label_plural( true ) ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-tools&tab=general' ); ?>">
				<p>
					<textarea name="banned_emails" rows="10" class="large-text"><?php echo implode( "\n", kbs_get_banned_emails() ); ?></textarea>
					<span class="description"><?php _e( 'Enter email addresses and/or domains to disallow, one per line.', 'kb-support' ); ?></span>
				</p>
				<p>
					<input type="hidden" name="kbs-action" value="save_banned_emails" />
					<?php wp_nonce_field( 'kbs_banned_emails_nonce', 'kbs_banned_emails_nonce' ); ?>
					<?php submit_button( __( 'Save', 'kb-support' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'kbs_tools_banned_emails_after' );
	do_action( 'kbs_tools_after' );
}
add_action( 'kbs_tools_tab_general', 'kbs_tools_banned_emails_display' );

/**
 * Display the System Info
 *
 * @since	1.0
 * @return	void
 */
function kbs_tools_system_info_display()	{

	if ( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}

	?>

	<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
    		<?php submit_button( __( 'Download System Info File', 'kb-support' ), 'primary', 'kbs-download-sysinfo', true ); ?>
		<textarea readonly onclick="this.focus(); this.select()" id="system-info-textarea" name="kbs-sysinfo" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac)."><?php echo kbs_tools_sysinfo_get(); ?></textarea>
		<p class="submit">
			<input type="hidden" name="kbs-action" value="download_sysinfo" />
			<?php submit_button( __( 'Download System Info File', 'kb-support' ), 'primary', 'kbs-download-sysinfo-2', false ); ?>
		</p>
	</form>

	<?php

} // kbs_tools_system_info_display
add_action( 'kbs_tools_tab_system_info', 'kbs_tools_system_info_display' );

/**
 * Save banned emails
 *
 * @since       1.0
 * @return      void
 */
function kbs_tools_banned_emails_save() {

	global $kbs_options;

	if ( ! isset( $_POST['kbs-action'] ) || 'save_banned_emails' != $_POST['kbs-action'] )	{
		return;
	}

	if ( ! wp_verify_nonce( $_POST['kbs_banned_emails_nonce'], 'kbs_banned_emails_nonce' ) )	{
		return;
	}

	if ( ! current_user_can( 'manage_ticket_settings' ) )	{
		return;
	}

	if ( ! empty( $_POST['banned_emails'] ) )	{

		// Sanitize the input
		$emails = array_map( 'trim', explode( "\n", $_POST['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach( $emails as $id => $email )	{
			if ( ! is_email( $email ) )	{
				if ( $email[0] != '@' )	{
					unset( $emails[$id] );
				}
			}
		}
	} else	{
		$emails = '';
	}

	$kbs_options['banned_emails'] = $emails;
	update_option( 'kbs_settings', $kbs_options );

}
add_action( 'init', 'kbs_tools_banned_emails_save' );

/**
 * Get system info
 *
 * @since	1.0
 * @global	obj	$wpdb	Used to query the database using the WordPress Database API
 * @return	str	$return	A string containing the info to output
 */
function kbs_tools_sysinfo_get()	{

	global $wpdb;

	// Get theme info
	$theme_data = wp_get_theme();
	$theme      = $theme_data->Name . ' ' . $theme_data->Version;

	$return  = '### Begin System Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";
    $return .= 'Locale:                   ' . get_locale() . "\n";

	$return  = apply_filters( 'kbs_sysinfo_after_site_info', $return );

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if ( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	$return .= 'ABSPATH:                  ' . ABSPATH . "\n";

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'KBS/' . KBS_VERSION,
		'body'          => $request
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if ( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'kbs_sysinfo_after_wordpress_config', $return );

	// KBS Configuration
	$installed       = get_option( 'kbs_installed' );
	$install_version = get_option( 'kbs_install_version', false );

	$return .= "\n" . '-- KBS Configuration' . "\n\n";
	$return .= 'Installed:                ' . date_i18n( get_option( 'date_format' ), strtotime( $installed ) ) . "\n";
	$return .= 'First Version:            ' . $install_version . "\n";
	$return .= 'Current Version:          ' . KBS_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'kbs_version_upgraded_from', 'None' ) . "\n";

	$return  = apply_filters( 'kbs_sysinfo_after_kbs_config', $return );

	// KBS pages
	$submission_page = kbs_get_option( 'submission_page', '' );
	$ticket_page     = kbs_get_option( 'tickets_page', '' );

	$return .= "\n" . '-- KBS Page Configuration' . "\n\n";
	$return .= 'Submission Page:         ' . ( ! empty( $submission_page ) ? get_permalink( $submission_page ) . "\n" : "Unset\n" );
	$return .= 'Tickets Page:            ' . ( ! empty( $ticket_page )     ? get_permalink( $ticket_page ) . "\n"    : "Unset\n" );

	$return  = apply_filters( 'kbs_sysinfo_after_kbs_pages', $return );

	// KBS Templates
	$dir = get_stylesheet_directory() . '/kbs_templates/';
	if ( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {
		$return .= "\n" . '-- KBS Template Overrides' . "\n\n";

		foreach( glob( "$dir/*.*" ) as $file ) {
			$return .= 'Filename:                 ' . basename( $file ) . "\n";
		}

		$return  = apply_filters( 'kbs_sysinfo_after_kbs_templates', $return );
	}

    $return = apply_filters( 'kbs_sysinfo_before_plugins', $return );

	// Get plugins that have an update
	$updates = get_plugin_updates();

	// Must-use plugins
	// NOTE: MU plugins can't show updates!
	$muplugins = get_mu_plugins();
	if ( ! empty( $muplugins ) ) {
		$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

		foreach( $muplugins as $plugin => $plugin_data ) {
			$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
		}

		$return = apply_filters( 'kbs_sysinfo_after_wordpress_mu_plugins', $return );
	}

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if ( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'kbs_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if ( in_array( $plugin_path, $active_plugins ) )
			continue;

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	$return  = apply_filters( 'kbs_sysinfo_after_wordpress_plugins_inactive', $return );

	if ( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if ( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		$return  = apply_filters( 'kbs_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'kbs_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

	$return  = apply_filters( 'kbs_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'kbs_sysinfo_after_php_ext', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;

} // kbs_tools_sysinfo_get

/**
 * Generates a System Info download file
 *
 * @since	1.0
 * @return	void
 */
function kbs_tools_sysinfo_download() {

	if ( ! isset( $_POST['kbs-action'] ) || 'download_sysinfo' != $_POST['kbs-action'] )	{
		return;
	}

	if ( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}

	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="kbs-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['kbs-sysinfo'] );
	die();
} // kbs_tools_sysinfo_download
add_action( 'init', 'kbs_tools_sysinfo_download' );

/**
 * Display the tools import/export tab
 *
 * @since       1.1
 * @return      void
 */
function kbs_tools_import_display() {

	if ( ! current_user_can( 'export_ticket_reports' ) ) {
		return;
	}

	do_action( 'kbs_tools_import_before' );
?>

	<div class="postbox">
		<h3><span><?php _e( 'Import Settings', 'kb-support' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the KB Support settings from a .json file. This file can be obtained by exporting the settings on another site using the form within the Export tab.', 'kb-support' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-tools&tab=import' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="kbs-action" value="import_settings" />
					<?php wp_nonce_field( 'kbs_import_nonce', 'kbs_import_nonce' ); ?>
					<?php submit_button( __( 'Import', 'kb-support' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'kbs_tools_import_after' );
} // kbs_tools_import_display
add_action( 'kbs_tools_tab_import', 'kbs_tools_import_display' );

/**
 * Display the tools import/export tab
 *
 * @since       1.1
 * @return      void
 */
function kbs_tools_export_display() {

	if ( ! current_user_can( 'export_ticket_reports' ) ) {
		return;
	}

	do_action( 'kbs_tools_export_before' );
?>

	<div class="postbox kbs-export-settings">
		<h3><span><?php _e( 'Export Settings', 'kb-support' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the KB Support settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'kb-support' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-tools&tab=export' ); ?>">
				<input type="hidden" name="kbs-action" value="export_settings" />
				<?php wp_nonce_field( 'kbs_export_nonce', 'kbs_export_nonce' ); ?>
                <span>
					<?php submit_button( __( 'Export', 'kb-support' ), 'secondary', 'submit', false ); ?>
                </span>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

<?php
	do_action( 'kbs_tools_export_after' );
} // kbs_tools_export_display
add_action( 'kbs_tools_tab_export', 'kbs_tools_export_display' );
