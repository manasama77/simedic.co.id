<?php
/**
 * Epsilon Onboarding Ajax Handler
 *
 * @package Epsilon Framework
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Epsilon_Dashboard_Ajax {
	/**
	 * Epsilon_Onboarding_Ajax_Handler constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_epsilon_dashboard_ajax_callback', array(
			$this,
			'epsilon_dashboard_ajax_callback',
		) );
	}

	/**
	 * AJAX Handler
	 */
	public function epsilon_dashboard_ajax_callback() {
		if ( is_string( $_POST['args'] ) ) {
			$_POST['args'] = json_decode( wp_unslash( $_POST['args'] ), true );
		}

		if ( !isset( $_POST['args'], $_POST['args']['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['args']['nonce'] ), 'epsilon_dashboard_nonce' ) ) {
			wp_die(
				wp_json_encode(
					array(
						'status' => false,
						'error'  => esc_html__( 'Not allowed', 'epsilon-framework' ),
					)
				)
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
		    wp_die(
				json_encode(
					array(
						'status' => false,
						'error'  => 'Not allowed',
					)
				)
			);
		}

		$args_action = array_map( 'sanitize_text_field', wp_unslash( $_POST['args']['action'] ) );

		if ( count( $args_action ) !== 2 ) {
			wp_die(
				wp_json_encode(
					array(
						'status' => false,
						'error'  => esc_html__( 'Not allowed', 'epsilon-framework' ),
					)
				)
			);
		}

		if ( ! class_exists( $args_action[0] ) ) {
			wp_die(
				wp_json_encode(
					array(
						'status' => false,
						'error'  => esc_html__( 'Class does not exist', 'epsilon-framework' ),
					)
				)
			);
		}

		$class = Epsilon_Dashboard_Ajax::sanitize_class_name( $args_action[0] );
		$method = $args_action[1];
		$args   = array();

		if ( is_array( $_POST['args']['args'] ) ) {
			$args = Epsilon_Sanitizers::array_map_recursive( 'sanitize_text_field', wp_unslash( $_POST['args']['args'] ) );
		}

		$response = $class::$method( $args );

		if ( is_array( $response ) ) {
			wp_die( wp_json_encode( $response ) );
		}

		if ( 'ok' === $response ) {
			wp_die(
				wp_json_encode(
					array(
						'status'  => true,
						'message' => 'ok',
					)
				)
			);
		}

		wp_die(
			wp_json_encode(
				array(
					'status'  => false,
					'message' => 'nok',
				)
			)
		);
	}


	/**
     * Sanitize class name
     *
     * @param $args
     */
    public static function sanitize_class_name( $class ) {
        $allowed_classes = array( 'Epsilon_Dashboard_Helper', 'EDD_Theme_Helper', 'Epsilon_Uninstall_Feedback', 'Epsilon_Import_Data' );
        if ( in_array( $class, $allowed_classes ) ) {
            return $class;
        }else{
            return false;
        }
    }

}