<?php
/**
 * MedZone_Lite Theme Sidebars
 *
 * @package MedZone_Lite
 * @since   1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MedZone_Lite_Sidebars
 */
class MedZone_Lite_Sidebars {
	/**
	 * Holds the
	 *
	 * @var array
	 */
	public $sidebars = array();

	/**
	 * MedZone_Lite_Sidebars constructor.
	 */
	public function __construct() {
		$this->collect_sidebars();
		add_action( 'widgets_init', array( $this, 'set_sidebars' ) );
		add_action( 'widgets_init', array( $this, 'initiate_widgets' ) );
	}

	/**
	 * Registers sidebars
	 */
	public function set_sidebars() {
		foreach ( $this->sidebars as $sidebar ) {
			register_sidebar( $sidebar );
		}
	}

	/**
	 * Add sidebars here
	 */
	private function collect_sidebars() {
		$this->sidebars = array(
			array(
				'id'            => 'sidebar',
				'name'          => __( '[Blog] Sidebar #1', 'medzone-lite' ),
				'before_title'  => '<h5 class="widget-title"><span>',
				'after_title'   => '</span></h5>',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
			),

			array(
				'id'            => 'footer-sidebar-1',
				'name'          => __( '[Footer] Sidebar #1', 'medzone-lite' ),
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
			),

			array(
				'id'            => 'footer-sidebar-2',
				'name'          => __( '[Footer] Sidebar #2', 'medzone-lite' ),
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
			),

			array(
				'id'            => 'footer-sidebar-3',
				'name'          => __( '[Footer] Sidebar #3', 'medzone-lite' ),
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
			),
			array(
				'id'            => 'footer-sidebar-4',
				'name'          => __( '[Footer] Sidebar #4', 'medzone-lite' ),
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
			),
		);
	}

	/**
	 * Initiate widgets
	 */
	public function initiate_widgets() {
		$widgets = array(
			'MedZone_Lite_Featured_Doctor',
			'MedZone_Lite_Recent_Posts',
		);

		foreach ( $widgets as $widget ) {
			new $widget();
			register_widget( $widget );
		}
	}
}
