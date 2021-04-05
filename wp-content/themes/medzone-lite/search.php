<?php
/**
 * The template for displaying search results pages.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package MedZone_Lite
 */

get_header();
$img = get_custom_header();
$img = $img->url;

$layout = MedZone_Lite_Helper::get_layout();
?>
<div id="content">
	<!-- /// CONTENT  /////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
	<div id="page-header" <?php echo ( ! empty( $img ) ) ? 'style="background-image:url(' . esc_url( $img ) . '"' : ''; ?>>
		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<h2>
						<?php
						/* Translators: Search result title. */
						printf( esc_html__( 'Search Results for: %s', 'medzone-lite' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
						?>
					</h2>
					<?php
					$display = get_bloginfo( 'description', 'display' );
					if ( ! empty( $display ) ) :
						?>
						<p>
							<span class="text-accent-color"><?php echo wp_kses_post( $display ); /* WPCS: xss ok. */ ?></span>
						</p>
						<?php
					endif;
					?>
				</div><!-- end .col -->
			</div><!-- end .row -->
		</div><!-- end .container -->
	</div><!-- end #page-header -->

	<div class="container">
		<div class="row">
			<?php
			if ( 'left-sidebar' === $layout['type'] && is_active_sidebar( 'sidebar' ) ) {
				?>
				<div class="col-sm-<?php echo esc_attr( $layout['columns']['sidebar']['span'] ); ?>">
					<!-- /// SIDEBAR CONTENT  /////////////////////////////////////////////////////////////////////////////////// -->
					<?php dynamic_sidebar( 'sidebar' ); ?>
					<!-- //////////////////////////////////////////////////////////////////////////////////////////////////////// -->
				</div>
				<?php
			}
			?>

			<div class="<?php echo ( 1 === $layout['columnsCount'] && ! is_active_sidebar( 'sidebar' ) ) ? 'col-sm-12' : 'col-sm-' . esc_attr( $layout['columns']['content']['span'] ); ?>">
				<!-- /// MAIN CONTENT  ////////////////////////////////////////////////////////////////////////////////////// -->
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/content', get_post_format() );
					endwhile;
				else :
					get_template_part( 'template-parts/content/content', 'none' );
				endif;

				the_posts_pagination(
					array(
						'prev_text' => '<span class="fa fa-angle-left"></span><span class="screen-reader-text">' . esc_html__( 'Previous', 'medzone-lite' ) . '</span>',
						'next_text' => '<span class="fa fa-angle-right"></span><span class="screen-reader-text">' . esc_html__( 'Next', 'medzone-lite' ) . '</span>',
					)
				);
				?>
				<!-- //////////////////////////////////////////////////////////////////////////////////////////////////////// -->
			</div>

			<?php
			if ( 'right-sidebar' === $layout['type'] && is_active_sidebar( 'sidebar' ) ) {
				?>
				<div class="col-sm-<?php echo esc_attr( $layout['columns']['sidebar']['span'] ); ?>">
					<!-- /// SIDEBAR CONTENT  /////////////////////////////////////////////////////////////////////////////////// -->
					<?php dynamic_sidebar( 'sidebar' ); ?>
					<!-- //////////////////////////////////////////////////////////////////////////////////////////////////////// -->
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
