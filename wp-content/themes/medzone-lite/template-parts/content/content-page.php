<?php

/**
 * Template part for displaying single pages.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

?>
<article id="page-<?php the_ID(); ?>">
	<div class="row">
		<div class="col-sm-12">
			<div class="post-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before'           => '<nav class="nav-links">',
						'after'            => '</nav>',
						'separator'        => '<span class="sep"></span>',
						'next_or_number'   => 'next',
						'nextpagelink'     => __('Next page', 'medzone-lite'),
						'previouspagelink' => __('Previous page', 'medzone-lite'),
					)
				);
				?>
			</div><!-- .post-content -->
			<?php if (get_theme_mod('medzone_lite_show_single_post_tags', true)) { ?>
				<div class="post-footer">
					<?php MedZone_Lite_Helper::posted_on('tags'); ?>
				</div><!-- .post-footer -->
			<?php } ?>
		</div><!-- end .col -->
	</div><!-- end .row -->
</article>

<?php
if (get_theme_mod('medzone_lite_enable_author_box', true) && !is_front_page()) {
	get_template_part('template-parts/misc/author-bio');
}
?>

<?php the_post_navigation(); ?>