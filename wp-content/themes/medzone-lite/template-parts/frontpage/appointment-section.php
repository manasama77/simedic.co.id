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
?>

<div class="appointments-section"
	<?php if ( ! empty( $fields['appointment_background'] ) ) : ?>
		style="background-image: url('<?php echo esc_url( $fields['appointment_background'] ); ?>');"
	<?php endif; ?>
	 data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr( $section_id ); ?>">

	<div class="container">
		<?php echo wp_kses_post( MedZone_Lite_Helper::generate_pencil() ); ?>
		<div class="row">
			<div class="col-sm-8">
				<?php if ( ! empty( $fields['appointment_title'] ) ) { ?>
					<h4><?php echo wp_kses_post( $fields['appointment_title'] ); ?></h4>
				<?php } ?>

				<?php echo wp_kses_post( $fields['appointment_text'] ); ?>

				<?php if ( ! empty( $fields['appointment_form'] ) ) { ?>
					<?php 
					     echo defined('KALIFORMS_VERSION') 
						     ? do_shortcode( '[kaliform id="' . absint( $fields['appointment_form'] ) . '"]' ) 
						     : do_shortcode( '[contact-form-7 id="' . absint( $fields['appointment_form'] ) . '"]' ); 
					?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
