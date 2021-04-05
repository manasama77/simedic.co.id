<?php
/**
 * The sidebar containing the footer widget area.
 *
 * @package MedZone_Lite
 */

/**
 * The defined sidebars
 */
$mysidebars = array(
	'footer-sidebar-1',
	'footer-sidebar-2',
	'footer-sidebar-3',
	'footer-sidebar-4',
);

/**
 * We create an empty array that will keep which one of them has any active sidebars
 */
$sidebars = array();
foreach ( $mysidebars as $column ) {
	if ( is_active_sidebar( $column ) ) {
		$sidebars[] = $column;
	}
};

/**
 * Handle the sizing of the footer columns based on the user selection
 */
$footer_layout = get_theme_mod( 'medzone_lite_footer_columns', false );
if ( ! $footer_layout ) {
	$footer_layout = MedZone_Lite_Helper::get_footer_default();
}
if ( ! is_array( $footer_layout ) ) {
	$footer_layout = json_decode( $footer_layout, true );
}

/**
 * In case all the sidebars have widgets attached, we slice the array.
 */
if ( ! empty( $sidebars ) ) { ?>
	<div id="footer" class="contrast">
		<div class="container">
			<div class="row">
				<?php foreach ( $footer_layout['columns'] as $sidebar ) : ?>

					<?php if ( is_active_sidebar( 'footer-sidebar-' . $sidebar['index'] ) ) { ?>
						<div id="footer-widget-area-<?php echo esc_attr( $sidebar['index'] ); ?>" class="col-sm-<?php echo esc_attr( $sidebar['span'] ); ?>">
							<?php dynamic_sidebar( 'footer-sidebar-' . $sidebar['index'] ); ?>
						</div>
					<?php } ?>

				<?php endforeach; ?>
			</div><!--.row-->
		</div>
	</div>
	<?php
}
