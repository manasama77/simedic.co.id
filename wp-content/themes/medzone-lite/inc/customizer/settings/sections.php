<?php
/**
 * MedZone_Lite Theme Customizer Panels & Sections
 *
 * @package MedZone_Lite
 * @since   1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register customizer panels
 */
$panels = array(
	/**
	 * General panel
	 */
	array(
		'id'   => 'medzone_lite_panel_general',
		'args' => array(
			'priority'       => 24,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => esc_html__( 'General options', 'medzone-lite' ),
		),
	),
	/**
	 * Content Panel
	 */
	array(
		'id'   => 'medzone_lite_panel_content',
		'args' => array(
			'priority'       => 27,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'type'           => 'epsilon-panel-regular',
			'title'          => esc_html__( 'Page sections', 'medzone-lite' ),
		),
	),
	/**
	 * Color panel
	 */
	array(
		'id'   => 'medzone_lite_panel_colors',
		'args' => array(
			'priority'       => 29,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => esc_html__( 'Colors', 'medzone-lite' ),
		),
	),
	array(
		'id'   => 'medzone_lite_panel_section_content',
		'args' => array(
			'priority'       => 9999,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'type'           => 'epsilon-panel-regular',
			'title'          => esc_html__( 'Front Page Content', 'medzone-lite' ),
			'panel'          => 'medzone_lite_panel_content',
			'hidden'         => true,
		),
	),
);

/**
 * Register sections
 */
$sections            = array(
	array(
		'id'   => 'medzone_lite_recomended_section',
		'args' => array(
			'type'                         => 'epsilon-section-recommended-actions',
			'title'                        => esc_html__( 'Recomended Actions', 'medzone-lite' ),
			'social_text'                  => esc_html__( 'MachoThemes is also social', 'medzone-lite' ),
			'plugin_text'                  => esc_html__( 'Recomended Plugins', 'medzone-lite' ),
			'actions'                      => MedZone_Lite_Dashboard_Setup::get_instance()->get_actions(),
			'plugins'                      => MedZone_Lite_Dashboard_Setup::get_instance()->get_plugins(),
			'theme_specific_option'        => MedZone_Lite_Dashboard_Setup::get_instance()->theme['theme-slug'] . '_actions_left',
			'theme_specific_plugin_option' => MedZone_Lite_Dashboard_Setup::get_instance()->theme['theme-slug'] . '_plugins_left',
			'facebook'                     => 'https://www.facebook.com/machothemes',
			'twitter'                      => 'https://twitter.com/MachoThemez',
			'wp_review'                    => 'https://wordpress.org/support/theme/medzone-lite/reviews/?rate=5#new-post',
			'priority'                     => 0,
		),
	),
	/**
	 * General section
	 */
	array(
		'id'   => 'medzone_lite_header_section',
		'args' => array(
			'title'    => esc_html__( 'Header', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_general',
			'priority' => 1,
		),
	),
	array(
		'id'   => 'medzone_lite_layout_section',
		'args' => array(
			'title'    => esc_html__( 'Layout & Typography', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_general',
			'priority' => 3,
		),
	),
	array(
		'id'   => 'medzone_lite_footer_section',
		'args' => array(
			'title'    => esc_html__( 'Footer', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_general',
			'priority' => 50,
		),
	),

	/**
	 * Repeatable sections container
	 */
	array(
		'id'   => 'medzone_lite_repeatable_section',
		'args' => array(
			'title'       => esc_html__( 'Page Sections', 'medzone-lite' ),
			'description' => esc_html__( 'The `Page sections` area will allow you to build your content with the help of our predefined set of sections. You don\'t need to write a single line of code, just select the sections you want to display in the current page and fill in the required fields .', 'medzone-lite' ),
			'priority'    => 0,
			'panel'       => 'medzone_lite_panel_content',
		),
	),

	/**
	 * Theme Content Sections
	 */
	array(
		'id'   => 'medzone_lite_doctors_section',
		'args' => array(
			'title'    => esc_html__( 'Doctors', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 1,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_cta_services',
		'args' => array(
			'title'    => esc_html__( 'Hero call to action services', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 2,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_about_info',
		'args' => array(
			'title'    => esc_html__( 'About', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 3,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_specialties',
		'args' => array(
			'title'    => esc_html__( 'Specialties', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 4,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_hospital_information_section',
		'args' => array(
			'title'    => esc_html__( 'Hospital Information', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 5,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_testimonials_section',
		'args' => array(
			'title'    => esc_html__( 'Testimonials', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 6,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_hospital_schedule_section',
		'args' => array(
			'title'    => esc_html__( 'Schedule', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 6,
			'type'     => 'epsilon-section-doubled',
		),
	),
	array(
		'id'   => 'medzone_lite_slides_section',
		'args' => array(
			'title'    => esc_html__( 'Slides', 'medzone-lite' ),
			'panel'    => 'medzone_lite_panel_section_content',
			'priority' => 7,
			'type'     => 'epsilon-section-doubled',
		),
	),
);
$stylesheet          = get_stylesheet();
$visible_recommended = get_option( $stylesheet . '_recommended_actions', false );
if ( $visible_recommended ) {
	unset( $sections[0] );
}

$collection = array(
	'panel'   => $panels,
	'section' => $sections,
);

Epsilon_Customizer::add_multiple( $collection );

