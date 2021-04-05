<?php
/**
 * MedZone_Lite Theme Customizer Fields
 *
 * @package MedZone_Lite
 * @since   1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register customizer fields
 */

/**
 * General section options
 ***************************************************************************************/
Epsilon_Customizer::add_field(
	'medzone_lite_enable_menu_search',
	array(
		'type'        => 'epsilon-toggle',
		'label'       => esc_html__( 'Search icon in the menu', 'medzone-lite' ),
		'description' => esc_html__( 'Toggle the display of the search icon and functionality in the main navigation menu.', 'medzone-lite' ),
		'section'     => 'medzone_lite_header_section',
		'default'     => true,
	)
);

Epsilon_Customizer::add_field(
	'medzone_lite_enable_go_top',
	array(
		'type'        => 'epsilon-toggle',
		'label'       => esc_html__( 'Go to top button', 'medzone-lite' ),
		'description' => esc_html__( 'Toggle the display of the go to top button.', 'medzone-lite' ),
		'section'     => 'medzone_lite_header_section',
		'default'     => true,
	)
);

/**
 * Layout section options
 */
Epsilon_Customizer::add_field(
	'medzone_lite_layout',
	array(
		'type'     => 'epsilon-layouts',
		'section'  => 'medzone_lite_layout_section',
		'layouts'  => array(
			1 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/one-column.png',
			2 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/two-column.png',
		),
		'default'  => array(
			'columnsCount' => 2,
			'columns'      => array(
				1 => array(
					'index' => 1,
					'span'  => 8,
				),
				2 => array(
					'index' => 2,
					'span'  => 4,
				),
			),
		),
		'min_span' => 4,
		'fixed'    => true,
		'label'    => esc_html__( 'Blog Layout', 'medzone-lite' ),
	)
);
Epsilon_Customizer::add_field(
	'medzone_lite_page_layout',
	array(
		'type'     => 'epsilon-layouts',
		'section'  => 'medzone_lite_layout_section',
		'layouts'  => array(
			1 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/one-column.png',
			2 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/two-column.png',
		),
		'default'  => array(
			'columnsCount' => 2,
			'columns'      => array(
				1 => array(
					'index' => 1,
					'span'  => 8,
				),
				2 => array(
					'index' => 2,
					'span'  => 4,
				),
			),
		),
		'min_span' => 4,
		'fixed'    => true,
		'label'    => esc_html__( 'Page Layout', 'medzone-lite' ),
	)
);
/**
 * Typography section options
 */
Epsilon_Customizer::add_field(
	'medzone_lite_typography_headings',
	array(
		'type'          => 'epsilon-typography',
		'transport'     => 'postMessage',
		'label'         => esc_html__( 'Headings', 'medzone-lite' ),
		'section'       => 'medzone_lite_layout_section',
		'description'   => esc_html__( 'Note: Current typography controls will only be affecting the blog.', 'medzone-lite' ),
		'stylesheet'    => 'medzone-lite-main',
		'choices'       => array(
			'font-family',
			'font-weight',
			'font-style',
		),
		'selectors'     => array(
			'.post-title',
			'.post-content h1',
			'.post-content h2',
			'.post-content h3',
			'.post-content h4',
			'.post-content h5',
			'.post-content h6',
		),
		'font_defaults' => array(
			'font-family' => '',
			'font-weight' => '',
			'font-style'  => '',
		),
	)
);
Epsilon_Customizer::add_field(
	'medzone_lite_paragraphs_typography',
	array(
		'type'          => 'epsilon-typography',
		'transport'     => 'postMessage',
		'section'       => 'medzone_lite_layout_section',
		'label'         => esc_html__( 'Paragraphs', 'medzone-lite' ),
		'description'   => esc_html__( 'Note: Current typography controls will only be affecting the blog.', 'medzone-lite' ),
		'stylesheet'    => 'medzone-lite-main',
		'choices'       => array(
			'font-family',
			'font-weight',
			'font-style',
		),
		'selectors'     => array(
			'.post-content p',
		),
		'font_defaults' => array(
			'font-family' => '',
			'font-weight' => '',
			'font-style'  => '',
		),
	)
);

/**
 * Blog section options
 */
Epsilon_Customizer::add_field(
	'medzone_lite_show_single_post_categories',
	array(
		'type'        => 'epsilon-toggle',
		'label'       => esc_html__( 'Post Meta: Categories', 'medzone-lite' ),
		'description' => esc_html__( 'This will disable the category section at the beggining of the post.', 'medzone-lite' ),
		'section'     => 'header_image',
		'default'     => true,
	)
);


Epsilon_Customizer::add_field(
	'medzone_lite_enable_author_box',
	array(
		'type'        => 'epsilon-toggle',
		'label'       => esc_html__( 'Post meta: Author', 'medzone-lite' ),
		'description' => esc_html__( 'Toggle the display of the author box, at the left side of the post. Will only display if the author has a description defined.', 'medzone-lite' ),
		'section'     => 'header_image',
		'default'     => true,
	)
);

Epsilon_Customizer::add_field(
	'medzone_lite_show_single_post_tags',
	array(
		'type'        => 'epsilon-toggle',
		'label'       => esc_html__( 'Post Meta: Tags', 'medzone-lite' ),
		'description' => esc_html__( 'This will disable the tags zone at the end of the post.', 'medzone-lite' ),
		'section'     => 'header_image',
		'default'     => true,
	)
);

/**
 * Footer section options
 */
Epsilon_Customizer::add_field(
	'medzone_lite_footer_columns',
	array(
		'type'     => 'epsilon-layouts',
		'section'  => 'medzone_lite_footer_section',
		'priority' => 0,
		'layouts'  => array(
			1 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/one-column.png',
			2 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/two-column.png',
			3 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/three-column.png',
			4 => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/four-column.png',
		),
		'default'  => array(
			'columnsCount' => 4,
			'columns'      => array(
				array(
					'index' => 1,
					'span'  => 3,
				),
				array(
					'index' => 2,
					'span'  => 3,
				),
				array(
					'index' => 3,
					'span'  => 3,
				),
				array(
					'index' => 4,
					'span'  => 3,
				),
			),
		),
		'fixed'    => true,
		'min_span' => 2,
		'label'    => esc_html__( 'Footer Columns', 'medzone-lite' ),
	)
);

Epsilon_Customizer::add_field(
	'medzone_lite_copyright_contents',
	array(
		'type'    => 'epsilon-text-editor',
		'label'   => esc_html__( 'Copyright Text', 'medzone-lite' ),
		'section' => 'medzone_lite_footer_section',
	)
);


/**
 * Theme Content
 */
/**
 * Doctors
 */
Epsilon_Customizer::add_field(
	'medzone_lite_doctors',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_doctors_section',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'Doctors', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new entries', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'value' => esc_html__( 'Doctors', 'medzone-lite' ),
			'field' => 'doctor_name',
		),

		'fields' => array(
			'doctor_name'            => array(
				'label'   => esc_html__( 'Name', 'medzone-lite' ),
				'type'    => 'text',
				'default' => esc_html__( 'Dr. Jonathan Doe', 'medzone-lite' ),
			),
			'doctor_group'           => array(
				'label'   => esc_html__( 'Specialty', 'medzone-lite' ),
				'type'    => 'text',
				'default' => esc_html__( 'Surgeon', 'medzone-lite' ),
			),
			'doctor_description'     => array(
				'label'   => esc_html__( 'Description', 'medzone-lite' ),
				'type'    => 'epsilon-text-editor',
				'default' => wp_kses_post( '<p>Pellentesque dapibus tristique ornare. Quisque vitanimate viverra lorem. animatenean luctus lorem mi, et lobortis turpis porttitor ut. Donec dictum dolor varius metus pellentesque, quis elementum massa varius.</p>' ),
			),
			'doctor_image'           => array(
				'label'   => esc_html__( 'Portrait', 'medzone-lite' ),
				'type'    => 'epsilon-image',
				'size'    => 'medzone-doctor-portrait',
				'default' => '',
			),
			'doctor_social_facebook' => array(
				'label'   => esc_html__( 'Facebook', 'medzone-lite' ),
				'type'    => 'url',
				'default' => 'http://facebook.com',
			),
			'doctor_social_twitter'  => array(
				'label'   => esc_html__( 'Twitter', 'medzone-lite' ),
				'type'    => 'url',
				'default' => 'http://twitter.com',
			),
			'doctor_social_google'   => array(
				'label'   => esc_html__( 'Google', 'medzone-lite' ),
				'type'    => 'url',
				'default' => 'http://google.com',
			),
			'doctor_social_linkedin' => array(
				'label'   => esc_html__( 'LinkedIn', 'medzone-lite' ),
				'type'    => 'url',
				'default' => 'http://linkedin.com',
			),
		),
	)
);

/**
 * Hero call to action services
 */
Epsilon_Customizer::add_field(
	'medzone_lite_cta_services',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_cta_services',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'Services', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new entries', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'value' => esc_html__( 'Service', 'medzone-lite' ),
			'field' => 'service_title',
		),
		'fields'       => array(
			'service_title'       => array(
				'label'             => esc_html__( 'Service title', 'medzone-lite' ),
				'type'              => 'text',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'Ambulance', 'medzone-lite' ),
			),
			'service_icon'        => array(
				'label'   => esc_html__( 'Service Icon', 'medzone-lite' ),
				'type'    => 'epsilon-icon-picker',
				'default' => 'fa fa-bold',
			),
			'service_description' => array(
				'label'   => esc_html__( 'Service description #1', 'medzone-lite' ),
				'type'    => 'text',
				'default' => esc_html__( 'Lorem Ipsum Dolor Sit Amet', 'medzone-lite' ),
			),
		),
	)
);

/**
 * About section content creation
 */
Epsilon_Customizer::add_field(
	'medzone_lite_about_info',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_about_info',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'About', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new entries', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'value' => esc_html__( 'Information', 'medzone-lite' ),
			'field' => 'info_title',
		),
		'fields'       => array(
			'info_title'       => array(
				'label'             => esc_html__( 'Title', 'medzone-lite' ),
				'type'              => 'text',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'Ambulance', 'medzone-lite' ),
			),
			'info_description' => array(
				'label'             => esc_html__( 'Description', 'medzone-lite' ),
				'type'              => 'epsilon-text-editor',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'Lorem Ipsum Dolor Sit Amet', 'medzone-lite' ),
			),
		),
	)
);

/**
 * Specialties section
 */
Epsilon_Customizer::add_field(
	'medzone_lite_specialties',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_specialties',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'Specialties', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new entries', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'value' => esc_html__( 'Specialty', 'medzone-lite' ),
			'field' => 'specialties_title',
		),
		'fields'       => array(
			'specialties_title'       => array(
				'label'             => esc_html__( 'Specialty title', 'medzone-lite' ),
				'type'              => 'text',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'Neurology', 'medzone-lite' ),
			),
			'specialties_icon'        => array(
				'label'   => esc_html__( 'Specialty Icon', 'medzone-lite' ),
				'type'    => 'epsilon-icon-picker',
				'default' => 'fa fa-stethoscope',
			),
			'specialties_description' => array(
				'label'             => esc_html__( 'Specialty description', 'medzone-lite' ),
				'type'              => 'epsilon-text-editor',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'Outside of a medical emergency, your primary care physician is the "first responder" to your healthcare needs.', 'medzone-lite' ),
			),
		),
	)
);

/**
 * Hospital Schedule
 */
Epsilon_Customizer::add_field(
	'medzone_lite_hospital_schedule',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_hospital_schedule_section',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'Schedule', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new entries', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'field' => 'schedule_days',
		),
		'fields'       => array(
			'schedule_days'  => array(
				'label'             => esc_html__( 'Days', 'medzone-lite' ),
				'description'       => esc_html__( 'e.g. Monday - Thursday', 'medzone-lite' ),
				'type'              => 'text',
				'sanitize_callback' => 'wp_kses_post',
			),
			'schedule_hours' => array(
				'label'             => esc_html__( 'Hours', 'medzone-lite' ),
				'description'       => esc_html__( 'e.g. 9:30 am – 8:30 pm', 'medzone-lite' ),
				'type'              => 'text',
				'sanitize_callback' => 'wp_kses_post',
			),
		),
	)
);

/**
 * Testimonials
 */
Epsilon_Customizer::add_field(
	'medzone_lite_testimonials',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_testimonials_section',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'Testimonials', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new entries', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'field' => 'testimonial_title',
		),
		'fields'       => array(
			'testimonial_title'    => array(
				'label'   => esc_html__( 'Title', 'medzone-lite' ),
				'type'    => 'text',
				'default' => '',
			),
			'testimonial_subtitle' => array(
				'label'   => esc_html__( 'Subtitle', 'medzone-lite' ),
				'type'    => 'text',
				'default' => '',
			),
			'testimonial_text'     => array(
				'label'   => esc_html__( 'Text', 'medzone-lite' ),
				'type'    => 'epsilon-text-editor',
				'default' => '',
			),
			'testimonial_image'    => array(
				'label'   => esc_html__( 'Portrait', 'medzone-lite' ),
				'type'    => 'epsilon-image',
				'size'    => 'medzone-testimonial-portrait',
				'default' => '',
			),
		),
	)
);


Epsilon_Customizer::add_field(
	'medzone_lite_slides',
	array(
		'type'         => 'epsilon-repeater',
		'section'      => 'medzone_lite_slides_section',
		'save_as_meta' => Epsilon_Content_Backup::get_instance()->setting_page,
		'label'        => esc_html__( 'Slides', 'medzone-lite' ),
		'button_label' => esc_html__( 'Add new slides', 'medzone-lite' ),
		'row_label'    => array(
			'type'  => 'field',
			'field' => 'slide_cta',
		),
		'fields'       => array(
			'slide_cta'                => array(
				'label'             => esc_html__( 'Call to action', 'medzone-lite' ),
				'type'              => 'epsilon-text-editor',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'Best Medical Care you can get for you and your family.', 'medzone-lite' ),
			),
			'slide_small'              => array(
				'label'             => esc_html__( 'Call to action subtext', 'medzone-lite' ),
				'type'              => 'epsilon-text-editor',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => esc_html__( 'More than 3000 specialists are here for you', 'medzone-lite' ),
			),
			'slide_background_color' => array(
				'label'      => esc_html__( 'Background color', 'medzone-lite' ),
				'type'       => 'epsilon-color-picker',
				'defaultVal' => '#f9f9fa',
				'default'    => '#f9f9fa',
			),
			'slide_background'         => array(
				'label' => esc_html__( 'Background image', 'medzone-lite' ),
				'type'  => 'epsilon-image',
			),
			'slide_alignment'          => array(
				'type'      => 'epsilon-button-group',
				'label'     => __( 'Alignment', 'epsilon-framework' ),
				'group'     => 'layout',
				'groupType' => 'three',
				'choices'   => array(
					'left'   => array(
						'icon'  => 'dashicons-editor-alignleft',
						'value' => 'left',
					),
					'center' => array(
						'icon'  => 'dashicons-editor-aligncenter',
						'value' => 'center',
					),
					'right'  => array(
						'icon'  => 'dashicons-editor-alignright',
						'value' => 'right',
					),
				),
				'default'   => 'center',
			),
			'slide_vertical_alignment' => array(
				'type'      => 'epsilon-button-group',
				'label'     => __( 'Vertical Alignment', 'epsilon-framework' ),
				'group'     => 'layout',
				'groupType' => 'three',
				'choices'   => array(
					'top'    => array(
						'value' => 'alignbottom',
						'png'   => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/epsilon-section-alignbottom.png',
					),
					'middle' => array(
						'value' => 'alignmiddle',
						'png'   => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/epsilon-section-alignmiddle.png',
					),
					'bottom' => array(
						'value' => 'aligntop',
						'png'   => get_template_directory_uri() . '/inc/libraries/epsilon-framework/assets/img/epsilon-section-aligntop.png',
					),
				),
				'default'   => 'alignmiddle',
			),
		),
	)
);

/**
 * Repeatable sections
 */
Epsilon_Customizer::add_field(
	'medzone_lite_frontpage_sections',
	array(
		'type'                => 'epsilon-section-repeater',
		'label'               => esc_html__( 'Sections', 'medzone-lite' ),
		'section'             => 'medzone_lite_repeatable_section',
		'selective_refresh'   => true,
		'page_builder'        => true,
		'transport'           => 'postMessage',
		'repeatable_sections' => MedZone_Lite_Repeatable_Sections::get_instance()->sections,
	)
);
