<?php
/**
 * MedZone_Lite Theme Customizer settings
 *
 * @package MedZone_Lite
 * @since   1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MedZone_Lite_Customizer
 */
class MedZone_Lite_Customizer {

	/**
	 * The basic constructor of the helper
	 * It changes the default panels of the customizer
	 *
	 * MedZone_Lite_Customizer_Helper constructor.
	 */
	public function __construct() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customizer_enqueue_scripts' ) );
		/**
		 * Customizer enqueues & controls
		 */
		add_action( 'customize_register', array( $this, 'change_default_panels' ), 98 );
		add_action( 'customize_register', array( $this, 'add_theme_options' ), 99 );
	}

	/**
	 * Loads the settings for the panels
	 */
	public function add_theme_options() {
		$path = get_template_directory() . '/inc/customizer/settings';

		require_once $path . '/upsells.php';
		require_once $path . '/sections.php';
		require_once $path . '/fields.php';
	}

	/**
	 * Runs on initialization, changes the default panels to the Theme options
	 */
	public function change_default_panels( $manager ) {
		/**
		 * Change transports
		 */
		$manager->get_setting( 'blogname' )->transport        = 'postMessage';
		$manager->get_setting( 'blogdescription' )->transport = 'postMessage';
		$manager->get_setting( 'custom_logo' )->transport     = 'refresh';

		/**
		 * Change panels
		 */
		$manager->get_section( 'header_image' )->panel      = 'medzone_lite_panel_general';
		$manager->get_section( 'background_image' )->panel  = 'medzone_lite_panel_general';
		$manager->get_section( 'colors' )->panel            = 'medzone_lite_panel_general';
		$manager->get_section( 'title_tagline' )->panel     = 'medzone_lite_panel_general';
		$manager->get_section( 'static_front_page' )->panel = 'medzone_lite_panel_content';

		/**
		 * Change priorities
		 */
		$manager->get_section( 'title_tagline' )->priority     = 0;
		$manager->get_control( 'custom_logo' )->priority       = 0;
		$manager->get_control( 'blogname' )->priority          = 2;
		$manager->get_section( 'header_image' )->priority      = 4;
		$manager->get_control( 'blogdescription' )->priority   = 17;
		$manager->get_section( 'static_front_page' )->priority = 0;

		/**
		 * Change labels
		 */
		$manager->get_control( 'custom_logo' )->description   = esc_html__( 'The image logo, if set, will override the text logo. You can not have both at the same time. A tagline can be displayed under the text logo.', 'medzone-lite' );
		$manager->get_section( 'header_image' )->title        = esc_html__( 'Blog options', 'medzone-lite' );
		$manager->get_control( 'page_on_front' )->description = esc_html__( 'If you have front-end sections, those will be displayed instead. Consider adding a "Content Section" if you need to display the page content as well.', 'medzone-lite' );

		if ( ! isset( $manager->selective_refresh ) ) {
			return;
		}

		$manager->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title',
			'render_callback' => function () {
				bloginfo( 'name' );
			},
		) );

		$manager->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => function () {
				bloginfo( 'description' );
			},
		) );
	}

	/**
	 * Our Customizer script
	 *
	 * Dependencies: Customizer Controls script (core)
	 */
	public function customizer_enqueue_scripts() {
		wp_enqueue_script( 'customizer-scripts', get_template_directory_uri() . '/inc/customizer/assets/js/customizer.js', array( 'customize-controls' ) );
	}

	/**
	 * Active Callback for copyright
	 */
	public static function copyright_enabled_callback( $control ) {
		if ( $control->manager->get_setting( 'medzone_lite_enable_copyright' )->value() == true ) {
			return true;
		}

		return false;
	}
}
