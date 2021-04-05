<?php
/**
 * Class that renders repeater blocks readable
 *
 * @package MedZone_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MedZone_Lite_Post_Parser {
	/**
	 * MedZone_Lite_Post_Parser constructor.
	 *
	 * @param string $option
	 */
	public function __construct( $option = '' ) {

	}

	/**
	 * @return MedZone_Lite_Post_Parser
	 */
	public static function get_instance() {
		static $inst;
		if ( ! $inst ) {
			$inst = new MedZone_Lite_Post_Parser();
		}

		return $inst;
	}

	/**
	 * @param $control
	 * @param $value
	 * @param $id
	 *
	 * @return string
	 */
	public function parse_medzone_lite_slides( $control, $value, $id ) {
		$content = '';
		$content .= '<!-- epsilon/' . $id . ' -->' . "\n";
		$content .= '<ul>' . "\n";
		foreach ( $value as $slide ) {
			$content .= '<!-- epsilon/' . $control->label . '-->' . "\n";
			$content .= '<li>';

			if ( ! empty( $slide['slide_background'] ) ) {
				$content .= '<img src="' . $slide['slide_background'] . '" />' . "\n";
			}
			if ( ! empty( $slide['slide_cta'] ) ) {
				$content .= '<h2>' . $slide['slide_cta'] . '</h2>' . "\n";
			}
			if ( ! empty( $slide['slide_cta'] ) ) {
				$content .= wpautop( $slide['slide_small'] ) . "\n";
			}

			$content .= '</li>' . "\n";
			$content .= '<!-- /epsilon/' . $control->label . '-->' . "\n";
		}
		$content .= '</ul>' . "\n";
		$content .= '<!-- /epsilon/' . $id . ' -->' . "\n";

		return $content;
	}

	/**
	 * @param $control
	 * @param $value
	 * @param $id
	 *
	 * @return string
	 */
	public function parse_medzone_lite_doctors( $control, $value, $id ) {
		$content = '';
		$content .= '<!-- epsilon/' . $id . ' -->' . "\n";

		foreach ( $value as $doctor ) {
			$content .= '<!-- epsilon/' . $control->label . '-->' . "\n";

			if ( ! empty( $doctor['doctor_image'] ) ) {
				$content .= '<img src="' . $doctor['doctor_image'] . '" />' . "\n";
			}
			if ( ! empty( $doctor['doctor_name'] ) ) {
				$content .= '<h3 class="doctor-name">' . $doctor['doctor_name'] . '</h3>' . "\n";
			}
			if ( ! empty( $doctor['doctor_group'] ) ) {
				$content .= '<p class="doctor-specialty">' . $doctor['doctor_group'] . '</p>' . "\n";
			}
			if ( ! empty( $doctor['doctor_description'] ) ) {
				/**
				 * This accepts editor, so we can wrap it in a div
				 */
				$content .= '<div class="doctor-description">' . $doctor['doctor_description'] . '</div>' . "\n";
			}

			$socials = array(
				'Facebook' => isset( $doctor['doctor_social_facebook'] ) ? $doctor['doctor_social_facebook'] : '',
				'Twitter'  => isset( $doctor['doctor_social_twitter'] ) ? $doctor['doctor_social_twitter'] : '',
				'Google'   => isset( $doctor['doctor_social_google'] ) ? $doctor['doctor_social_google'] : '',
				'LinkedIn' => isset( $doctor['doctor_social_linked'] ) ? $doctor['doctor_social_linked'] : '',
			);

			$socials = array_filter( $socials );
			if ( ! empty( $socials ) ) {
				$content .= '<ul class="doctor-socials">' . "\n";
				foreach ( $socials as $name => $url ) {
					$content .= '<li><a href="' . esc_url( $url ) . '">' . $name . '</a></li>' . "\n";
				}
				$content .= '</ul>' . "\n";
			}

			$content .= '<!-- /epsilon/' . $control->label . '-->' . "\n";
		}

		$content .= '<!-- /epsilon/' . $id . ' -->' . "\n";

		return $content;
	}

	/**
	 * @param $control
	 * @param $value
	 * @param $id
	 *
	 * @return string
	 */
	public function parse_medzone_lite_testimonials( $control, $value, $id ) {
		$content = '';
		$content .= '<!-- epsilon/' . $id . ' -->' . "\n";
		foreach ( $value as $testimonial ) {
			$content .= '<!-- epsilon/' . $control->label . '-->' . "\n";
			if ( ! empty( $testimonial['testimonial_title'] ) ) {
				$content .= '<h3 class="testimonial-title">' . $testimonial['testimonial_title'] . '</h3>' . "\n";
			}
			if ( ! empty( $testimonial['testimonial_subtitle'] ) ) {
				$content .= '<h4 class="testimonial-subtitle">' . $testimonial['testimonial_subtitle'] . '</h4>' . "\n";
			}
			if ( ! empty( $testimonial['testimonial_text'] ) ) {
				$content .= '<blockquote>' . $testimonial['testimonial_text'] . '</blockquote>' . "\n";
			}
			if ( ! empty( $testimonial['testimonial_image'] ) ) {
				$content .= '<img src="' . $testimonial['testimonial_image'] . '" />';
			}
			$content .= '<!-- /epsilon/' . $control->label . '-->' . "\n";
		}
		$content .= '<!-- /epsilon/' . $id . ' -->' . "\n";

		return $content;
	}

	/**
	 * @param $control
	 * @param $value
	 * @param $id
	 *
	 * @return string
	 */
	public function parse_medzone_lite_hospital_information( $control, $value, $id ) {
		$content = '';
		$content .= '<!-- epsilon/' . $id . ' -->' . "\n";

		if ( ! empty( $value ) ) {
			$content .= '<dl>' . "\n";
		}

		foreach ( $value as $accordion ) {
			$content .= '<!-- epsilon/' . $control->label . '-->' . "\n";

			if ( ! empty( $accordion['info_title'] ) ) {
				$content .= '<dt>' . $accordion['info_title'] . '</dt>' . "\n";
			}

			if ( ! empty( $accordion['info_text'] ) ) {
				$content .= '<dd>' . $accordion['info_text'] . '</dd>' . "\n";
			}

			$content .= '<!-- /epsilon/' . $control->label . '-->' . "\n";
		}

		if ( ! empty( $value ) ) {
			$content .= '</dl>' . "\n";
		}

		$content .= '<!-- /epsilon/' . $id . ' -->' . "\n";

		return $content;
	}

	/**
	 * @param $control
	 * @param $value
	 * @param $id
	 *
	 * @return string
	 */
	public function parse_medzone_lite_cta_services( $control, $value, $id ) {
		$content = '';
		$content .= '<!-- epsilon/' . $id . ' -->' . "\n";
		if ( ! empty( $value ) ) {
			$content .= '<dl>' . "\n";
		}

		foreach ( $value as $accordion ) {
			$content .= '<!-- epsilon/' . $control->label . '-->' . "\n";

			if ( ! empty( $accordion['service_title'] ) ) {
				$content .= '<dt>' . $accordion['service_title'] . '</dt>' . "\n";
			}

			if ( ! empty( $accordion['service_description'] ) ) {
				$content .= '<dd>' . $accordion['service_description'] . '</dd>' . "\n";
			}

			$content .= '<!-- /epsilon/' . $control->label . '-->' . "\n";
		}

		if ( ! empty( $value ) ) {
			$content .= '</dl>' . "\n";
		}

		$content .= '<!-- /epsilon/' . $id . ' -->' . "\n";

		return $content;
	}

	/**
	 * @param $control
	 * @param $value
	 * @param $id
	 *
	 * @return string
	 */
	public function parse_medzone_lite_about_info( $control, $value, $id ) {
		$content = '';
		$content .= '<!-- epsilon/' . $id . ' -->' . "\n";
		if ( ! empty( $value ) ) {
			$content .= '<dl>' . "\n";
		}

		foreach ( $value as $info ) {
			$content .= '<!-- epsilon/' . $control->label . '-->' . "\n";

			if ( ! empty( $info['info_title'] ) ) {
				$content .= '<dt>' . $info['info_title'] . '</dt>' . "\n";
			}

			if ( ! empty( $info['info_description'] ) ) {
				$content .= '<dd>' . $info['info_description'] . '</dd>' . "\n";
			}

			$content .= '<!-- /epsilon/' . $control->label . '-->' . "\n";
		}

		if ( ! empty( $value ) ) {
			$content .= '</dl>' . "\n";
		}

		$content .= '<!-- /epsilon/' . $id . ' -->' . "\n";

		return $content;
	}
}
