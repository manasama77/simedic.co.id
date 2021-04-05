<?php

/**
 * Template part for displaying the logo
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

?>
<div class="col-xs-8 col-sm-9 col-md-2">
	<!-- /// Logo ////////  -->
	<div id="logo" class="logo-text">
		<?php
		if (function_exists('the_custom_logo')) {
			if (has_custom_logo()) {
				the_custom_logo();
			} else {
		?>
				<a class="site-title" href="<?php echo esc_url(home_url()); ?>"> <?php echo esc_html(get_option('blogname', 'medzone-lite')); ?></a>
				<?php
				$description = get_bloginfo('description', 'display');
				if (!empty($description)) :
				?>
					<p class="site-description"><?php echo wp_kses_post($description); /* WPCS: xss ok. */ ?></p>
		<?php
				endif;
			}
		}
		?>
	</div><!-- end #logo -->
</div><!-- end .col -->