<?php

/**
 * Template part for displaying a frontpage section
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

$frontpage             = Epsilon_Page_Generator::get_instance('medzone_lite_frontpage_sections_' . get_the_ID(), get_the_ID());
$fields                = $frontpage->sections[$section_id];
$fields['information'] = $frontpage->get_repeater_field($fields['about_repeater_field'], array());
if ('all' !== $fields['about_grouping'][0]) {
	foreach ($fields['information'] as $k => $v) {
		if (is_array($fields['about_grouping']) && !in_array($v['info_title'], $fields['about_grouping'])) {
			unset($fields['information'][$k]);
		}
	}
}
$i = 0;
?>

<div class="about-section-2" data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr($section_id); ?>">
	<div class="container">
		<?php echo wp_kses_post(MedZone_Lite_Helper::generate_pencil()); ?>
		<div class="row">

			<div class="col-sm-12">
				<?php if (!empty($fields['about_title'])) { ?>
					<h4 class="text-center"><?php echo wp_kses_post($fields['about_title']); ?></h4>
				<?php } ?>

				<span class="text-left">
					<?php echo wp_kses_post($fields['about_description']); ?>
				</span>
			</div>

			<div class="col-sm-12 text-center">
				<?php if (!empty($fields['about_image'])) { ?>
					<img src="<?php echo esc_url($fields['about_image']); ?>" class="img-fluid" />
				<?php } ?>
			</div>

		</div>
	</div>
	<?php if (!empty($fields['information'])) { ?>
		<div class="container">
			<div class="row about-row-margin-bottom">

				<?php $max_row = ceil(count($fields['information']) / 3); ?>

				<?php foreach ($fields['information'] as $k => $v) { ?>

					<?php $i++; ?>
					<div class="col-sm-4">
						<h6><span class="text-accent-color"><?php echo wp_kses_post($v['info_title']); ?></span></h6>
						<?php echo wp_kses_post($v['info_description']); ?>
					</div>
					<?php
					if ((int) fmod($i, 3) === 0 && count($fields['information']) !== (int) $i) {
						echo '</div><div class="row about-row-margin-bottom">';
					} elseif (count($fields['information']) === (int) $i) {
						continue;
					}
					?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
</div>