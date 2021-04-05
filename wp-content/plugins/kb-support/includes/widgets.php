<?php
/**
 * Widgets
 *
 * Widgets related funtions and widget registration.
 *
 * @package     KBS
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Article Categories / Tags Widget
| - Popular Articles
*/

/**
 * Article Categories / Tags Widget.
 *
 * @since	1.0.2
 * @return	void
*/
class kbs_article_categories_tags_widget extends WP_Widget {

	/** Constructor */
	function __construct()	{

		parent::__construct(
			'kbs_article_categories_tags_widget',
			sprintf(
				__( 'KBS %s Categories / Tags', 'kb-support' ), kbs_get_article_label_plural()
			),
			array( 'description' => sprintf( __( 'Display the %s categories or tags', 'kb-support' ), kbs_get_article_label_plural() ) )
		);

	} // __construct

	/** @see WP_Widget::widget */
	function widget( $args, $instance )	{

		// Set defaults.
		$args['id']           = ( isset( $args['id'] ) ) ? $args['id'] : 'kbs_article_categories_tags_widget';
		$instance['title']    = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$instance['taxonomy'] = ( isset( $instance['taxonomy'] ) ) ? $instance['taxonomy'] : 'article_category';

		$title      = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$tax        = $instance['taxonomy'];
		$count      = isset( $instance['count'] ) && $instance['count'] == 'on'           ? 1 : 0;
		$hide_empty = isset( $instance['hide_empty'] ) && $instance['hide_empty'] == 'on' ? 1 : 0;
		$children   = isset( $instance['children'] ) && $instance['children']     == 'on' ? 1 : 0;

		echo $args['before_widget'];

		if ( $title )	{
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'kbs_before_article_taxonomy_widget' );

		echo '<ul class="kbs-taxonomy-widget">' . "\n";
			wp_list_categories( 'title_li=&taxonomy=' . $tax . '&show_count=' . $count . '&hide_empty=' . $hide_empty . '&hierarchical=' . $children );
		echo '</ul>' . "\n";

		do_action( 'kbs_after_article_taxonomy_widget' );

		echo $args['after_widget'];

	} // widget

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )	{

		$instance               = $old_instance;
		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['taxonomy']   = strip_tags( $new_instance['taxonomy'] );
		$instance['count']      = isset( $new_instance['count'] )      ? $new_instance['count']      : '';
		$instance['hide_empty'] = isset( $new_instance['hide_empty'] ) ? $new_instance['hide_empty'] : '';
		$instance['children']   = isset( $new_instance['children'] )   ? $new_instance['children']   : '';

		return $instance;

	} // update

	/** @see WP_Widget::form */
	function form( $instance )	{

		// Set up some default widget settings.
		$defaults = array(
			'title'         => '',
			'taxonomy'      => 'article_category',
			'count'         => 'off',
			'hide_empty'    => 'off',
			'children'      => 'off'
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'kb-support' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php _e( 'Taxonomy:', 'kb-support' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>">

				<?php
				$category_labels = kbs_get_taxonomy_labels( 'article_category' );
				$tag_labels      = kbs_get_taxonomy_labels( 'article_tag' );
				?>

				<option value="article_category" <?php selected( 'article_category', $instance['taxonomy'] ); ?>><?php echo $category_labels['name']; ?></option>
				<option value="article_tag" <?php selected( 'article_tag', $instance['taxonomy'] ); ?>><?php echo $tag_labels['name']; ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show Count:', 'kb-support' ); ?></label>
			<input <?php checked( $instance['count'], 'on' ); ?> id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="checkbox" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide Empty Categories:', 'kb-support' ); ?></label>
			<input <?php checked( $instance['hide_empty'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" type="checkbox" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'children' ); ?>"><?php _e( 'Show Child Categories:', 'kb-support' ); ?></label>
			<input <?php checked( $instance['children'], 'on' ); ?> id="<?php echo $this->get_field_id( 'children' ); ?>" name="<?php echo $this->get_field_name( 'children' ); ?>" type="checkbox" />
		</p>

	<?php
	} // form

} // kbs_article_categories_tags_widget

/**
 * Popluar Articles Widget.
 *
 * @since	1.0.2
 * @return	void
*/
class kbs_popular_articles_widget extends WP_Widget {

	/** Constructor */
	function __construct()	{

		parent::__construct(
			'kbs_popular_articles_widget',
			sprintf(
				__( 'KBS Popular %s', 'kb-support' ), kbs_get_article_label_plural()
			),
			array( 'description' => sprintf( __( 'Display the most popular %s', 'kb-support' ), kbs_get_article_label_plural() ) )
		);

	} // __construct

	/** @see WP_Widget::widget */
	function widget( $args, $instance )	{

		// Set defaults.
		$args['id'] = ( isset( $args['id'] ) ) ? $args['id'] : 'kbs_popular_articles_widget';

		$instance['title']      = ( isset( $instance['title'] ) )      ? $instance['title']      : '';

		$title  = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : '0';
		$views  = isset( $instance['views'] ) && $instance['views'] == 'on'           ? 1 : 0;

		echo $args['before_widget'];

		if ( $title )	{
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'kbs_before_popular_articles_widget' );

		$popular_articles = new KBS_Articles_Query( array(
			'number'  => $number
		) );

		$articles = $popular_articles->get_articles();

		echo '<ul class="kbs-popular-articles-widget">' . "\n";
			foreach( $articles as $article )	{
				$url = get_permalink( $article->ID );

				$output_views = '';
				if ( ! empty( $views ) )	{
					$article_views = kbs_get_article_view_count( $article->ID );
					if ( ! empty( $article_views ) )	{
						$output_views .= ' ';
						$output_views .= sprintf( _n( '(%s view)', '(%s views)', $article_views, 'kb-support' ), number_format_i18n( $article_views ) );
					}
				}

				echo '<li>';
				echo '<a href="' . $url . '">' . get_the_title( $article->ID ) . '</a>';
				echo $output_views;
				echo '</li>';
			}
		echo '</ul>' . "\n";

		do_action( 'kbs_after_popular_articles_widget' );

		echo $args['after_widget'];

	} // widget

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )	{

		$instance           = $old_instance;
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['number'] = isset( $new_instance['number'] ) ? $new_instance['number'] : '5';
		$instance['views']  = isset( $new_instance['views'] )  ? $new_instance['views']  : '';

		return $instance;

	} // update

	/** @see WP_Widget::form */
	function form( $instance )	{

		// Set up some default widget settings.
		$defaults = array(
			'title'  => '',
			'number' => '5',
			'views'  => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'kb-support' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php printf( __( 'Number of %s:', 'kb-support' ), kbs_get_article_label_plural() ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" class="small-text" value="<?php echo $instance['number']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'views' ); ?>"><?php _e( 'Display View Count:', 'kb-support' ); ?></label>
			<input<?php checked( $instance['views'], 'on' ); ?> id="<?php echo $this->get_field_id( 'views' ); ?>" name="<?php echo $this->get_field_name( 'views' ); ?>" type="checkbox" />
		</p>

	<?php
	} // form

} // kbs_popular_articles_widget

/**
 * Register Widgets.
 *
 * Registers the KBS Widgets.
 *
 * @since	1.0.2
 * @return	void
 */
function kbs_register_widgets() {
	register_widget( 'kbs_article_categories_tags_widget' );
	register_widget( 'kbs_popular_articles_widget' );
} // kbs_register_widgets
add_action( 'widgets_init', 'kbs_register_widgets' );
