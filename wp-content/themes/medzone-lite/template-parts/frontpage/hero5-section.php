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
                    <h2 style="color: #fff; text-align: center; position: inherit; top: 80px; overflow-wrap: break-word;"><?php echo wp_kses_post($fields['hero_cta']); ?></h2>
                <?php } ?>

            </div>

        </div>
    </div>
</div>