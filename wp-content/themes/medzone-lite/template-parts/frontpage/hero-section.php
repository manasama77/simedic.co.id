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
$fields['service'] = $frontpage->get_repeater_field($fields['hero_repeater_field'], array());

?>

<div class="intro-section" <?php if (!empty($fields['hero_background_color']) || (!empty($fields['hero_background']))) : ?> style="
		<?php if (!empty($fields['hero_background_color'])) : ?>
			background-color:<?php echo esc_attr($fields['hero_background_color']); ?>;
		<?php endif; ?>
		<?php if (!empty($fields['hero_background'])) : ?>
			background-image: url(' <?php echo esc_url($fields['hero_background']); ?> ');
		<?php endif; ?>
			" <?php endif; ?> data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr($section_id); ?>">
	<div class="container">
		<?php echo wp_kses_post(MedZone_Lite_Helper::generate_pencil()); ?>
		<div class="row">
			<div class="col-sm-12">
				<?php if (!empty($fields['hero_cta'])) { ?>
					<h2><?php echo wp_kses_post($fields['hero_cta']); ?></h2>
				<?php } ?>

				<?php if (!empty($fields['hero_small'])) { ?>
					<p><span class="text-accent-color"><?php echo wp_kses_post($fields['hero_small']); ?></span></p>
				<?php } ?>

				<ul class="medical-specialties">
					<li class="active">
						<a href="#">
							<img src="http://localhost/simedic.co.id/wp-content/uploads/2021/03/Picture1.png" alt="klinik" style="max-height: 24px;" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://localhost/simedic.co.id/wp-content/uploads/2021/03/Picture2.png" alt="hospital" style="max-height: 24px;" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://localhost/simedic.co.id/wp-content/uploads/2021/03/Picture3.png" alt="lab" style="max-height: 24px;" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://localhost/simedic.co.id/wp-content/uploads/2021/03/Picture4.png" alt="covid" style="max-height: 24px;" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://localhost/simedic.co.id/wp-content/uploads/2021/03/Picture5.png" alt="prolife" style="max-height: 24px;" />
						</a>
					</li>
				</ul>

			</div>

			<div class="col-sm-12 col-md-5">
				<p style="margin-top: 0px; background-color: rgba(43, 43, 43, 40%); padding: 3px;">
					<span style="color: #fff;">
						Hi, We help medical corporate such as clinic, hospital, and many more to scale up by providing them with our signature software namely Simedic
					</span>
				</p>
			</div>
		</div>
	</div>
</div>