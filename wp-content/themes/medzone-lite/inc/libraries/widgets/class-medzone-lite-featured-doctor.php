<?php
/**
 * Widget that retrieves a featured doctor
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class MedZone_Lite_Featured_Doctor extends WP_Widget {
	/**
	 * MedZone_Lite_Featured_Doctor constructor.
	 */
	public function __construct() {
		parent::__construct(
			'medzone_lite_featured_doctor',
			__( 'MedZone_Lite - Featured Doctor', 'medzone-lite' ),
			array(
				'classname'                   => 'mt_featured_doctor',
				'description'                 => __( 'Widget that allows you to display a featured doctor in a nicely designed box.', 'medzone-lite' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Handle widget options
	 *
	 * @param array $instance Widget options.
	 *
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'      => '',
			'doctor_id'  => '',
			'custom_url' => '',
		);

		$instance = wp_parse_args( $instance, $defaults );

		$doctors   = MedZone_Lite_Helper::get_group_values_from_meta( 'medzone_lite_doctors', 'doctor_name' );
		$sanitized = array( __( '- Select a doctor -', 'medzone-lite' ) );

		foreach ( $doctors as $k => $doctor ) {
			if ( 'all' === $k ) {
				continue;
			}

			$sanitized[ preg_replace( '/\s+/', '-', $doctor ) ] = $doctor;
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"> <?php esc_html_e( 'Title', 'medzone-lite' ); ?> </label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'doctor_id' ) ); ?>">
				<?php esc_html_e( 'Doctor', 'medzone-lite' ); ?>
			</label>

			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'doctor_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'doctor_id' ) ); ?>">
				<?php foreach ( $sanitized as $id => $doctor ) { ?>
					<option <?php selected( $id, $instance['doctor_id'] ); ?> value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $doctor ); ?></option>
				<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'custom_url' ) ); ?>"> <?php esc_html_e( 'Custom URL', 'medzone-lite' ); ?> </label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'custom_url' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'custom_url' ) ); ?>" value="<?php echo esc_attr( $instance['custom_url'] ); ?>">
		</p>

		<?php
		return '';
	}

	/**
	 * Sanitize user input
	 *
	 * @param array $new_instance New settings sent through the form.
	 * @param array $old_instance Old settings sent through the form.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array(
			'title'      => empty( $new_instance['title'] ) ? '' : strip_tags( $new_instance['title'] ),
			'custom_url' => empty( $new_instance['custom_url'] ) ? '' : esc_url_raw( $new_instance['custom_url'] ),
			'doctor_id'  => empty( $new_instance['doctor_id'] ) ? '' : strip_tags( $new_instance['doctor_id'] ),
		);

		return $instance;
	}

	/**
	 * Render the widget in the frontend
	 *
	 * @param array $args     Widgets global config.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		$defaults = array(
			'title'      => '',
			'doctor_id'  => '',
			'custom_url' => '',
		);

		$instance = wp_parse_args( $instance, $defaults );

		$html = $this->build_html( $args, $instance );
		echo $html;
	}

	/**
	 * Grab information from the database ( in this case doctors )
	 *
	 * @param string $doctor_id Get the doctor, by name.
	 *
	 * @return array
	 */
	private function get_data( $doctor_id = '' ) {
		$doctors   = $this->get_repeater_field( 'medzone_lite_doctors', array() );
		$sanitized = array();

		foreach ( $doctors as $k => $doctor ) {
			$sanitized[ preg_replace( '/\s+/', '-', $doctor['doctor_name'] ) ] = $doctor;
		}

		$arr = array(
			'image'     => empty( $sanitized[ $doctor_id ]['doctor_image'] ) ? '' : $sanitized[ $doctor_id ]['doctor_image'],
			'name'      => empty( $sanitized[ $doctor_id ]['doctor_name'] ) ? '' : $sanitized[ $doctor_id ]['doctor_name'],
			'specialty' => empty( $sanitized[ $doctor_id ]['doctor_group'] ) ? '' : $sanitized[ $doctor_id ]['doctor_group'],
			'text'      => empty( $sanitized[ $doctor_id ]['doctor_description'] ) ? '' : wp_trim_words( strip_tags( $sanitized[ $doctor_id ]['doctor_description'] ), 15 ),
		);

		return $arr;
	}

	/**
	 * @param string $key
	 * @param array  $default
	 *
	 * @return array|string
	 */
	private function get_repeater_field( $key = '', $default = array() ) {
		$data = get_theme_mod( $key, $default );

		/**
		 * In case we are in the customizer, we need to make sure that when we don`t have any values we stop here
		 */
		if ( is_customize_preview() ) {
			global $wp_customize;
			$post = $wp_customize->post_value( $wp_customize->get_setting( $key ) );
			if ( null !== $post ) {
				return $data;
			}
		}

		if ( empty( $data ) ) {
			$data = get_post_meta( Epsilon_Content_Backup::get_instance()->setting_page, $key, true );
			$data = isset( $data[ $key ] ) ? $data[ $key ] : $default;
		}

		return $data;
	}

	/**
	 * Build the widget HTML
	 *
	 * @param array $args     Widgets global config.
	 * @param array $instance Widget instance.
	 *
	 * @return string
	 */
	private function build_html( $args, $instance ) {
		$doctor = $this->get_data( $instance['doctor_id'] );

		if ( empty( $instance['doctor_id'] ) ) {
			return '';
		}

		$html = $args['before_widget'];

		if ( $instance['title'] ) {
			$html .= $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
		}

		$html .= '<div class="mt-doctor mt-doctor-portrait" style="background-image: url(' . esc_url( $doctor['image'] ) . ')">';
		$html .= '<div class="mt-doctor-info">';
		$html .= '<h6>' . esc_html( $doctor['name'] ) . '<br/> <span class="text-accent-color">' . esc_html( $doctor['specialty'] ) . '</span></h6>';
		$html .= '<p>' . esc_html( $doctor['text'] ) . '</p>';

		if ( $instance['custom_url'] ) {
			$html .= '<a class="btn btn-small" href="#">' . esc_html__( 'Book appointment', 'medzone-lite' ) . '</a>';
		}

		$html .= '</div>';
		$html .= '</div>';

		$html .= $args['after_widget'];

		return $html;
	}
}
