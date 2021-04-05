<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MedZone_Lite_Notify_System
 */
class MedZone_Lite_Notify_System extends Epsilon_Notify_System {
	/**
	 * Check installed data
	 */
	public static function check_installed_data() {
		self::check_old_version();
		$stylesheet = get_stylesheet();
		$imported   = get_theme_mod( $stylesheet . '_content_imported', false );

		if ( in_array( $imported, array( true, 1, '1' ) ) ) {
			return true;
		}

		if ( in_array( $imported, array( false, 0, '0' ) ) ) {
			return false;
		}

		return $imported;
	}

	/**
	 * Checks if we have an older version of the theme, with its content imported.
	 */
	public static function check_old_version() {
		$stylesheet = get_stylesheet();

		if ( false !== strpos( $stylesheet, '-lite' ) ) {
			$imported = get_theme_mod( str_replace( '-', '_', $stylesheet ) . '_content_imported', false );
			if ( $imported ) {
				set_theme_mod( $stylesheet . '_content_imported', true );
			}
		}
	}

	/**
	 * Verify the status of a plugin
	 *
	 * @param string      $get         Return title/description/etc.
	 * @param string      $slug        Plugin slug.
	 * @param string      $plugin_name Plugin name.
	 * @param bool|string $special     Callback to verify a certain plugin
	 *
	 * @return mixed
	 */
	public static function plugin_verifier( $slug = '', $get = '', $plugin_name = '', $special = false ) {
		if ( false !== $special ) {
			$arr = self::$special();
		} else {
			$arr = array(
				'installed' => Epsilon_Notify_System::check_plugin_is_installed( $slug ),
				'active'    => Epsilon_Notify_System::check_plugin_is_active( $slug ),
			);

			if ( empty( $get ) ) {
				$arr = array_filter( $arr );

				return 2 === count( $arr );
			}
		}

		// Translators: %s is the plugin name.
		$arr['title'] = sprintf( __( 'Install: %s', 'medzone-lite' ), $plugin_name );
		// Translators: %s is the plugin name.
		$arr['description'] = sprintf( __( 'Please install %s in order to create the demo content.', 'medzone-lite' ), $plugin_name );

		if ( $arr['installed'] ) {
			// Translators: %s is the plugin name
			$arr['title'] = sprintf( __( 'Activate: %s', 'medzone-lite' ), $plugin_name );
			// Translators: %s is the plugin name
			$arr['description'] = sprintf( __( 'Please activate %s in order to create the demo content.', 'medzone-lite' ), $plugin_name );
		}

		return $arr[ $get ];
	}

	/**
	 * Verify that contact form 7 is installed
	 *
	 * @return mixed
	 */
	public static function verify_cf7() {
		$arr = array(
			'installed' => false,
			'active'    => false,
		);

		if ( file_exists( ABSPATH . 'wp-content/plugins/contact-form-7' ) ) {
			$arr['installed'] = true;
			$arr['active']    = defined( 'WPCF7_VERSION' );
		}

		return $arr;
	}
}
