<?php
/**
 * Template part for displaying the copyright footer
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

if ( get_theme_mod( 'medzone_lite_enable_copyright', true ) || has_nav_menu( 'copyright' ) ) : ?>
	<div id="footer-bottom" class="contrast">
		<!-- /// FOOTER-BOTTOM  ////////////////////////////////////////////////////////////////////////////////////////////// -->
		<div class="container">
			<div class="row">
				<?php if ( get_theme_mod( 'medzone_lite_enable_copyright', true ) ) : ?>
					<div id="footer-bottom-widget-area-1" class="col-sm-6">
						<?php
						// Translators: %s is a link.
						echo wp_kses_post(
							get_theme_mod(
								'medzone_lite_copyright_contents',
								sprintf(
									esc_html__( 'MedZone Lite by %1$sMacho Themes%2$s &copy; %3$s. All rights reserved.', 'medzone-lite' ),
									'<a href="https://www.machothemes.com" target="_blank">',
									'</a>',
									date( 'Y' )
								)
							)
						);
						?>
					</div><!-- end .col -->
				<?php endif; ?>

				<div id="footer-bottom-widget-area-2" class="col-sm-6">
					<?php
					wp_nav_menu(
						array(
							'menu'           => 'footer',
							'theme_location' => 'footer',
							'depth'          => 1,
							'container'      => 'div',
							'menu_class'     => 'footer-copyright-menu text-right',
						)
					);
					?>
				</div><!-- end .col -->
			</div><!-- end .row -->
		</div><!-- end .container -->
		<!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
	</div><!-- end #footer-bottom -->
<?php endif; ?>
