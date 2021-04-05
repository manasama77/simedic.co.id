<?php
/**
 * Template part for displaying a frontpage section
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

$frontpage            = Epsilon_Page_Generator::get_instance( 'medzone_lite_frontpage_sections_' . get_the_ID(), get_the_ID() );
$fields               = $frontpage->sections[ $section_id ];
$fields['open-hours'] = $frontpage->get_repeater_field( $fields['openhours_repeater_field'], array() );
?>

<div class="open-hours-section contrast"
	<?php if ( ! empty( $fields['openhours_background'] ) ) : ?>
		style="background-image: url('<?php echo esc_url( $fields['openhours_background'] ); ?>');"
	<?php endif; ?>
	 data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<?php echo wp_kses_post( MedZone_Lite_Helper::generate_pencil() ); ?>
		<div class="row">
			<div class="col-sm-7">
				<div class="open-hours-section-info">
					<?php if ( ! empty( $fields['openhours_title'] ) ) { ?>
						<h1 class="text-center"><?php echo wp_kses_post( $fields['openhours_title'] ); ?></h1>
					<?php } ?>
					<br>
					<?php echo wp_kses_post( $fields['openhours_text'] ); ?>
				</div>

			</div>
			<?php if ( ! empty( $fields['open-hours'] ) ) { ?>
				<div class="col-sm-5">
					<div class="open-hours">

						<h4><?php echo esc_html__( 'Opening Hours', 'medzone-lite' ); ?></h4>

						<?php foreach ( $fields['open-hours'] as $schedule ) { ?>
							<h6>
								<span class="text-uppercase"><?php echo wp_kses_post( $schedule['schedule_days'] ); ?></span>
							</h6>
							<p><?php echo wp_kses_post( $schedule['schedule_hours'] ); ?></p>
						<?php } ?>

						<br/>

					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
