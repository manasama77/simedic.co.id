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
            <div class="col-sm-7">
                <?php if (!empty($fields['hero_cta'])) { ?>
                    <h2><?php echo wp_kses_post($fields['hero_cta']); ?></h2>
                <?php } ?>

                <?php if (!empty($fields['hero_small'])) { ?>
                    <p><span class="text-accent-color"><?php echo wp_kses_post($fields['hero_small']); ?></span></p>
                <?php } ?>

                <?php if (!empty($fields['service'])) { ?>
                    <ul class="medical-specialties">
                        <?php $i = 0; ?>

                        <?php foreach ($fields['service'] as $k => $v) { ?>
                            <li <?php echo (0 === $i) ? 'class="active"' : ''; ?>>
                                <a href="#"><i class="<?php echo esc_attr($v['service_icon']); ?>"></i></a>
                                <p><span class="text-accent-color"><?php echo esc_html($v['service_title']); ?></span>
                                    <br /> <?php echo esc_html($v['service_description']); ?>
                                </p>
                            </li>
                            <?php $i++; ?>

                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </div>
    </div>
</div>