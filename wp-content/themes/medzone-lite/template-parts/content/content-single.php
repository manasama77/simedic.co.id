<?php

/**
 * Template part for displaying single posts.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

?>
<article id="post-<?php the_ID(); ?>">
	<div class="row">
		<div class="col-sm-12">
			<?php
			if (has_post_thumbnail()) {
				the_post_thumbnail('medzone-blog-image');
			}
			?>

			<div class="post-header">
				<?php
				if (get_theme_mod('medzone_lite_show_single_post_categories', true)) {
					MedZone_Lite_Helper::posted_on('category');
				}
				?>
				<h4 class="post-title">
					<a href="<?php echo esc_url(get_the_permalink()); ?>"><?php the_title(); ?></a>
				</h4><!-- end .post-ttile -->
			</div><!-- .post-header -->
			<div class="post-meta">
				<span class="posted-on"><?php echo esc_html(get_the_date()); ?></span>
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
if (get_theme_mod('medzone_lite_enable_author_box', true)) {
	get_template_part('template-parts/misc/author-bio');
}
?>

<?php the_post_navigation(); ?>