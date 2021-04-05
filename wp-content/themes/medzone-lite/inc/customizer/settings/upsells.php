<?php
/**
 * MedZone_Lite Theme Upsells
 *
 * @package MedZone_Lite
 * @since   1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$upsells    = array(
	'section' => array(
		array(
			'id'   => 'medzone_lite_lite_upsell_section',
			'args' => array(
				'title'       => esc_html__( 'LITE vs PRO comparison', 'medzone-lite' ),
				'button_text' => esc_html__( 'Learn more', 'medzone-lite' ),
				'button_url'  => esc_url_raw( admin_url() . 'themes.php?page=' . get_stylesheet() . '-dashboard' ),
				'priority'    => 0,
				'type'        => 'epsilon-section-pro',
			),
		),
	),
	'field'   => array(
		array(
			'id'   => 'medzone_lite_typography_layout_upsell',
			'args' => array(
				'section'      => 'medzone_lite_layout_section',
				'type'         => 'epsilon-upsell',
				'priority'     => 0,
				'options'      => array(
					esc_html__( 'Advanced Typography Options', 'medzone-lite' ),
					esc_html__( 'Advanced Blog Layout Control', 'medzone-lite' ),
					esc_html__( 'Color schemes', 'medzone-lite' ),
				),
				'requirements' => array(
					esc_html__( 'The PRO version of the theme offers more typography controls. You will be able to change the font-sizes, line-heights, letter-spacing of the selected fonts.', 'medzone-lite' ),
					esc_html__( 'Complete layout control is available in the Pro Version. You can select the width of elements and the position of sidebar.', 'medzone-lite' ),
					esc_html__( 'The PRO version of the theme allows for a greater degree of customisability. Get multiple professionally designed color schemes with the purchase of the PRO version. ', 'medzone-lite' ),
				),
				'button_url'   => esc_url_raw( get_admin_url() . 'themes.php?page=page=medzone-lite-dashboard' ),
				'button_text'  => esc_html__( 'See PRO vs Lite', 'medzone-lite' ),

				'second_button_url'  => esc_url_raw( 'https://www.machothemes.com/theme/medzone-pro/?utm_source=worg&utm_medium=customizer&utm_campaign=upsell' ),
				'second_button_text' => esc_html__( 'Get PRO now!', 'medzone-lite' ),

				'separator' => 'or',
			),
		),
		array(
			'id'   => 'medzone_lite_footer_upsell',
			'args' => array(
				'section'      => 'medzone_lite_footer_section',
				'priority'     => 0,
				'type'         => 'epsilon-upsell',
				'options'      => array(
					esc_html__( 'Advanced Footer Layout Control', 'medzone-lite' ),
				),
				'requirements' => array(
					esc_html__( 'The PRO version of the theme allows you to control the footer\'s column layout. You will be able to create the perfect layout for your website!', 'medzone-lite' ),
				),
				'button_url'   => esc_url_raw( get_admin_url() . 'themes.php?page=medzone-lite-welcome&tab=features' ),
				'button_text'  => esc_html__( 'See PRO vs Lite', 'medzone-lite' ),

				'second_button_url'  => esc_url_raw( 'https://www.machothemes.com/theme/medzone-pro/?utm_source=worg&utm_medium=customizer&utm_campaign=upsell' ),
				'second_button_text' => esc_html__( 'Get PRO now!', 'medzone-lite' ),

				'separator' => 'or',
			),
		),
		array(
			'id'   => 'medzone_lite_fp_sections_upsell',
			'args' => array(
				'section'      => 'medzone_lite_repeatable_section',
				'priority'     => 0,
				'type'         => 'epsilon-upsell',
				'options'      => array(
					esc_html__( 'All of our frontpage section types', 'medzone-lite' ),
				),
				'requirements' => array(
					esc_html__( 'Get the PRO version of the theme to take advantage of all our pre-defined content blocks!', 'medzone-lite' ),
				),
				'button_url'   => esc_url_raw( get_admin_url() . 'themes.php?page=medzone-lite-welcome&tab=features' ),
				'button_text'  => esc_html__( 'See PRO vs Lite', 'medzone-lite' ),

				'second_button_url'  => esc_url_raw( 'https://www.machothemes.com/theme/medzone-pro/?utm_source=worg&utm_medium=customizer&utm_campaign=upsell' ),
				'second_button_text' => esc_html__( 'Get PRO now!', 'medzone-lite' ),

				'separator' => 'or',
			),
		),
	),
);
$stylesheet = get_stylesheet();
$lvp        = get_option( $stylesheet . '_lite_vs_pro', false );
if ( $lvp ) {
	unset( $upsells['section'][0] );
}

Epsilon_Customizer::add_multiple( $upsells );
