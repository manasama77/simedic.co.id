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
			a
			<?php
			if (has_post_thumbnail()) {
				the_post_thumbnail('medzone-blog-image');
			}
			?>
			<?php if (!is_front_page()) { ?>
				<div class="post-meta">
					<span class="posted-on"><?php echo the_date(); ?></span>
					/ <?php MedZone_Lite_Helper::posted_on('author'); ?>

					/ <?php MedZone_Lite_Helper::posted_on('comments'); ?>
					<?php if (current_user_can('manage_options')) { ?>
						/<span class="edit-link">
							<a class="post-edit-link" target="_blank" href="<?php echo esc_url(get_admin_url()) . 'post.php?post=' . get_the_ID() . '&action=edit'; ?>">
								<?php echo esc_html__('Edit', 'medzone-lite'); ?>
							</a>
						</span>
					<?php } ?>
				</div><!-- .post-meta -->
			<?php } ?>
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