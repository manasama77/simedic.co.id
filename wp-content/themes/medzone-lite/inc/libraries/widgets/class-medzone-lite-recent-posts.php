<?php
/**
 * Widget that retrieves recent posts
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MedZone_Lite_Recent_Posts
 */
class MedZone_Lite_Recent_Posts extends WP_Widget {
	/**
	 * MedZone_Lite_Recent_Posts constructor.
	 */
	public function __construct() {
		parent::__construct(
			'medzone_lite_recent_posts',
			__( 'MedZone_Lite - Recent Posts', 'medzone-lite' ),
			array(
				'classname'                   => 'mt_widget_recent_entries',
				'description'                 => __( 'Widget that allows you to display a list of recent posts with a thumbnail.', 'medzone-lite' ),
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
			'title' => '',
			'date'  => 'on',
			'count' => 3,
		);
		$instance = wp_parse_args( $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"> <?php esc_html_e( 'Title', 'medzone-lite' ); ?> </label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>


		<label class="block" for="input_<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">
			<span class="customize-control-title">
				<?php esc_html_e( 'Posts to Show', 'medzone-lite' ); ?> :
			</span>

		</label>

		<div class="slider-container">
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" class="rl-slider" id="input_<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>"/>

			<div id="slider_<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" data-attr-min="1" data-attr-max="10" data-attr-step="1" class="ss-slider"></div>
		</div>


		<div class="checkbox_switch">
				<span class="customize-control-title onoffswitch_label">
					<?php esc_html_e( 'Show Date', 'medzone-lite' ); ?>
				</span>
			<div class="onoffswitch">
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_name( 'date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'date' ) ); ?>" class="onoffswitch-checkbox" value="on"
					<?php checked( $instance['date'], 'on' ); ?>>
				<label class="onoffswitch-label" for="<?php echo esc_attr( $this->get_field_name( 'date' ) ); ?>"></label>
			</div>
		</div>

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
			'title' => empty( $new_instance['title'] ) ? '' : strip_tags( $new_instance['title'] ),
			'date'  => empty( $new_instance['date'] ) ? '' : strip_tags( $new_instance['date'] ),
			'count' => empty( $new_instance['count'] ) ? '' : absint( $new_instance['count'] ),
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
			'title' => '',
			'date'  => 'on',
			'count' => 3,
		);

		$instance = wp_parse_args( $instance, $defaults );

		$html = $this->build_html( $args, $instance );
		wp_reset_postdata();
		echo $html;
	}

	/**
	 * Grab information from the database ( in this case posts )
	 *
	 * @param array $instance Widget onfig.
	 *
	 * @return WP_Query
	 */
	private function get_data( $instance ) {
		$atts  = array(
			'posts_per_page'      => $instance['count'],
			'ignore_sticky_posts' => 1,
		);
		$posts = new WP_Query( $atts );

		return $posts;
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
		$posts = $this->get_data( $instance );

		$html = $args['before_widget'];

		if ( $instance['title'] ) {
			$html .= $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
		}
		if ( $posts->have_posts() ) :
			$html .= '<ul>';
			while ( $posts->have_posts() ) :
				$posts->the_post();
				$html .= '<li>';

				if ( has_post_thumbnail() ) {
					$html .= '<img src="' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ) . '" alt="">';
				}
				$html .= '<div>';
				$html .= '<a href="' . esc_url( get_the_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
				if ( 'on' === $instance['date'] ) {
					$html .= '<span class="post-date">' . esc_html( get_the_date() ) . '</span>';
				}

				$html .= '</div>';
				$html .= '</li>';
			endwhile;
			$html .= '</ul>';
		endif;
		$html .= $args['after_widget'];

		return $html;
	}
}
