<?php
/**
 * Template part for displaying a frontpage section
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

$frontpage             = Epsilon_Page_Generator::get_instance( 'medzone_lite_frontpage_sections_' . get_the_ID(), get_the_ID() );
$fields                = $frontpage->sections[ $section_id ];
$fields['specialties'] = $frontpage->get_repeater_field( $fields['specialties_repeater_field'], array() );

if ( 'all' !== $fields['specialties_grouping'][0] ) {
	foreach ( $fields['specialties'] as $k => $v ) {
		if ( is_array( $fields['specialties_grouping'] ) && ! in_array( $v['specialties_title'], $fields['specialties_grouping'] ) ) {
			unset( $fields['specialties'][ $k ] );
		}
	}
}

$i = 0;
?>

<div class="specialities-section" data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<?php echo wp_kses_post( MedZone_Lite_Helper::generate_pencil() ); ?>
		<div class="row">
			<?php if ( ! empty( $fields['specialties'] ) ) { ?>

				<?php $max_row = ceil( count( $fields['specialties'] ) / 2 ); ?>
				<div class="col-sm-4">
					<?php foreach ( $fields['specialties'] as $k => $v ) { ?>

						<?php $i ++; ?>
						<div class="icon-box">
							<i class="<?php echo esc_attr( $v['specialties_icon'] ); ?>"></i>
							<div class="icon-box-content">
								<h6><?php echo wp_kses_post( $v['specialties_title'] ); ?></h6>
								<?php echo wp_kses_post( $v['specialties_description'] ); ?>
							</div>
						</div>
						<?php
						if ( (int) fmod( $i, $max_row ) === 0 && count( $fields['specialties'] ) !== (int) $i ) {
							echo '</div><div class="col-sm-4">';
						} elseif ( count( $fields['specialties'] ) === (int) $i ) {
							continue;
						}
						?>

					<?php } ?>
				</div>
			<?php } ?>

			<?php
			$class = 'col-sm-4';
			if ( empty( $fields['specialties'] ) ) {
				$class = 'col-sm-12';
			}
			?>

			<div class="<?php echo esc_attr( $class ); ?>">
				<?php if ( ! empty( $fields['main_specialty_title'] ) ) { ?>
					<h4><?php echo wp_kses_post( $fields['main_specialty_title'] ); ?></h4>
				<?php } ?>

				<?php if ( ! empty( $fields['main_specialty_subtitle'] ) ) { ?>
					<p>
						<span class="text-accent-color"><?php echo wp_kses_post( $fields['main_specialty_subtitle'] ); ?></span>
					</p>
				<?php } ?>

				<?php echo wp_kses_post( $fields['main_specialty_description'] ); ?>
			</div>
		</div>
	</div>
</div>
