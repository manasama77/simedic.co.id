<?php
/**
 * Template part for displaying a page section
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

$frontpage = Epsilon_Page_Generator::get_instance( 'medzone_lite_frontpage_sections_' . get_the_ID(), get_the_ID() );
$fields    = $frontpage->sections[ $section_id ];
$grouping  = array(
	'values'   => $fields['slider_grouping'],
	'group_by' => 'slide_cta',
);

$arr = array(
	'slider_autostart',
	'slider_infinite',
	'slider_pager',
	'slider_controls',
);
if ( is_customize_preview() ) {
	foreach ( $arr as $k ) {
		if ( is_bool( $fields[ $k ] ) ) {
			continue;
		}
		$fields[ $k ] = is_string( $fields[ $k ] ) && 'true' === $fields[ $k ] ? true : false;
	}
}
$fields['slides'] = $frontpage->get_repeater_field( $fields['slider_repeater_field'], array(), $grouping );

?>
<div data-customizer-section-id="medzone_lite_repeatable_section" data-section="<?php echo esc_attr( $section_id ); ?>">
	<div class="medzone-slider" data-medzone-slider-mode-fade="<?php echo 'fade' === $fields['slider_transition'] ? 'true' : 'false'; ?>" data-medzone-slider-speed="<?php echo ! empty( $fields['slider_speed'] ) ? absint( $fields['slider_speed'] ) : '500'; ?>" data-medzone-slider-autoplay="<?php echo $fields['slider_autostart'] ? 'true' : 'false'; ?>" data-medzone-slider-loop="<?php echo $fields['slider_infinite'] ? 'true' : 'false'; ?>" data-medzone-slider-enable-pager="<?php echo $fields['slider_pager'] ? 'true' : 'false'; ?>" data-medzone-slider-enable-controls="<?php echo $fields['slider_controls'] ? 'true' : 'false'; ?>">

		<ul class="slides">
			<?php foreach ( $fields['slides'] as $slide ) { ?>

				<?php
				$style = array(
					'background-image' => ! empty( $slide['slide_background'] ) ? $slide['slide_background'] : '',
					'background-color' => ! empty( $slide['slide_background_color'] ) ? $slide['slide_background_color'] : '',
				);
				$css   = 'style="';
				$style = array_filter( $style );

				foreach ( $style as $k => $v ) {
					if ( 'background-image' === $k ) {
						$css .= esc_attr( $k ) . ':url(' . esc_url( $v ) . ');';
					} else {
						$css .= esc_attr( $k ) . ':' . esc_attr( $v ) . ';';
					}
				}
				$css .= '"';

				$captions = array(
					'medzone-slider-slide-content'               => true,
					'medzone-slider-slide-content-valign-top'    => 'aligntop' === $slide['slide_vertical_alignment'] ? true : false,
					'medzone-slider-slide-content-valign-middle' => 'alignmiddle' === $slide['slide_vertical_alignment'] ? true : false,
					'medzone-slider-slide-content-valign-bottom' => 'alignbottom' === $slide['slide_vertical_alignment'] ? true : false,
					'medzone-slider-slide-content-align-left'    => 'left' === $slide['slide_alignment'] ? true : false,
					'medzone-slider-slide-content-align-center'  => 'center' === $slide['slide_alignment'] ? true : false,
					'medzone-slider-slide-content-align-right'   => 'right' === $slide['slide_alignment'] ? true : false,
				);
				$captions = array_filter( $captions );
				?>
				<li <?php echo $css; ?>>
					<div class="<?php echo esc_attr( implode( ' ', array_keys( $captions ) ) ); ?>">
						<div class="medzone-slider-slide-content-wrap">
							<?php
							if ( ! empty( $slide['slide_cta'] ) ) {
								echo '<h1>' . wp_kses_post( $slide['slide_cta'] ) . '</h1>';
							}

							if ( ! empty( $slide['slide_small'] ) ) {
								echo wpautop( wp_kses_post( $slide['slide_small'] ) );
							}
							?>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul><!-- end .slides -->

		<div class="medzone-slider-dots medzone-slider-dots-align-center"></div>
		<div class="medzone-slider-arrows"></div>

	</div><!-- end .medzone-slider -->
</div>

