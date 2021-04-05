<?php

/**
 * Template part for displaying a frontpage section
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

$frontpage         = Epsilon_Page_Generator::get_instance('medzone_lite_frontpage_sections_' . get_the_ID(), get_the_ID());
$fields            = $frontpage->sections[$section_id];
$fields['doctors'] = $frontpage->get_repeater_field($fields['doctors_repeater_field'], array());

$i = 0;
?>

<div class="our-doctors-section" data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr($section_id); ?>">
	<div class="container">
		<?php echo wp_kses_post(MedZone_Lite_Helper::generate_pencil()); ?>
		<div class="row">
			<div class="col-sm-12">
				<h4 class="text-center"><?php echo wp_kses_post($fields['doctors_title']); ?></h4>

				<?php echo wp_kses_post($fields['doctors_subtitle']); ?>
				<br>
			</div>
		</div>
	</div>
	<?php if (!empty($fields['doctors'])) { ?>
		<div class="container">
			<div class="row">
				<?php foreach ($fields['doctors'] as $doctor) { ?>

					<?php
					if ('all' !== $fields['doctors_group'][0] && (is_array($fields['doctors_group']) && !in_array($doctor['doctor_name'], $fields['doctors_group']))) {
						continue;
					}
					?>

					<?php $doctor['doctor_image'] = !empty($doctor['doctor_image']) ? $doctor['doctor_image'] : get_template_directory_uri() . '/assets/images/doctor_placeholder.jpg'; ?>

					<?php $i++; ?>
					<div class="col-sm-6 col-md-4">
						<div class="doctor-profile">
							<div class="doctor-profile-summary">
								<img src="<?php echo esc_url($doctor['doctor_image']); ?>" alt="">
								<a class="doctor-profile-summary-details-trigger" href="#"><i class="fa fa-plus-circle"></i></a>
								<div class="doctor-profile-summary-details">
									<?php echo wp_kses_post($doctor['doctor_description']); ?>
								</div>
							</div>
							<?php if (!empty($doctor['doctor_name'])) { ?>
								<h6><?php echo esc_html($doctor['doctor_name']); ?></h6>
							<?php } ?>

							<?php if (!empty($doctor['doctor_group'])) { ?>
								<p class="text-accent-color"><?php echo esc_html($doctor['doctor_group']); ?></p>
							<?php } ?>

							<?php
							$arr = array(
								'facebook'    => $doctor['doctor_social_facebook'],
								'twitter'     => $doctor['doctor_social_twitter'],
								'google-plus' => $doctor['doctor_social_google'],
								'linkedin'    => $doctor['doctor_social_linkedin'],
							);

							$arr = array_filter($arr);
							?>

							<?php if (!empty($arr)) { ?>
								<div class="social-links">
									<?php foreach ($arr as $k => $v) { ?>
										<a class="<?php echo esc_attr($k); ?>-icon social-icon" href="<?php echo esc_url($v); ?>">
											<i class="fa fa-<?php echo esc_attr($k); ?>"></i> </a>
									<?php } ?>
								</div>
							<?php } ?>
						</div>
					</div>

					<?php
					if ((int) fmod($i, 4) === 0 && count($fields['doctors']) !== (int) $i) {
						echo '</div><div class="row">';
					} elseif (count($fields['doctors']) === (int) $i) {
						continue;
					}
					?>

				<?php } // End foreach(). 
				?>
			</div>
		</div>
	<?php } ?>
</div>