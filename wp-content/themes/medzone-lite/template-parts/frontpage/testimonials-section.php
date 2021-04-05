<?php
/**
 * Template part for displaying a frontpage section
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

$frontpage = Epsilon_Page_Generator::get_instance( 'medzone_lite_frontpage_sections_' . get_the_ID(), get_the_ID() );
$fields    = $frontpage->sections[ $section_id ];
$grouping  = array(
	'values'   => $fields['testimonials_grouping'],
	'group_by' => 'testimonial_title',
);

$fields['testimonials'] = $frontpage->get_repeater_field( $fields['testimonials_repeater_field'], array(), $grouping );
?>

<div class="testimonials-section" data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<?php echo wp_kses_post( MedZone_Lite_Helper::generate_pencil() ); ?>
		<div class="row">
			<div class="col-sm-12">
				<h4 class="text-center"><?php echo wp_kses_post( $fields['testimonials_title'] ); ?></h4>

				<br>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $fields['testimonials'] ) ) { ?>
		<div class="container">
			<div class="row">
				<div class="col-sm-12 col-md-10 col-md-offset-1">
					<div class="testimonial-slider">
						<ul class="slides">
							<?php foreach ( $fields['testimonials'] as $testimonial ) { ?>
								<li>
									<div class="testimonial">
										<?php if ( ! empty( $testimonial['testimonial_image'] ) ) { ?>
											<img src="<?php echo esc_url( $testimonial['testimonial_image'] ); ?>" alt="<?php echo esc_attr( $testimonial['testimonial_title'] ); ?>">
										<?php } ?>

										<h6><?php echo esc_html( $testimonial['testimonial_title'] ); ?></h6>

										<p>
											<span class="text-accent-color"><?php echo esc_html( $testimonial['testimonial_subtitle'] ); ?></span>
										</p>

										<?php echo wp_kses_post( $testimonial['testimonial_text'] ); ?>
									</div>
								</li>
							<?php } ?>
						</ul>
						<div class="testimonial-slider-pagination"></div>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
