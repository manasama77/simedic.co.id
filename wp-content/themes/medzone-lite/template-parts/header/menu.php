<?php

/**
 * Template part for displaying the menu
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

?>
<div class="col-xs-4 col-sm-3 col-md-10">
	<nav>
		<?php
		wp_nav_menu(
			array(
				'menu'           => 'primary',
				'theme_location' => 'primary',
				'container'      => '',
				'menu_id'        => 'desktop-menu',
				'menu_class'     => 'sf-menu',
				'fallback_cb'    => 'MedZone_Lite_Navwalker::fallback',
				'walker'         => new MedZone_Lite_Navwalker(),
			)
		);

		if (get_theme_mod('medzone_lite_enable_menu_search', true)) :
		?>
			<div id="custom-search">
				<form action="#" id="custom-search-form" role="search">
					<input type="text" value="" name="s" id="s" placeholder="<?php /* Translators: Search term. */ esc_attr_e('type the search term...', 'medzone-lite'); ?>">

					<input type="submit" id="custom-search-submit" value="">
				</form>

				<a href="#" id="custom-search-button"><i class="fa fa-search"></i></a>
			</div><!-- end #custom-search -->
		<?php endif; ?>
		<!-- /// Mobile Menu Trigger //////// -->
		<a href="#" id="mobile-menu-trigger"> <i class="fa fa-bars"></i> </a><!-- end #mobile-menu-trigger -->
	</nav>
</div>