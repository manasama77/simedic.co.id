<?php

/**
 * Template part for displaying posts.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="row">
		<div class="col-sm-12 col-md-6">
			<?php
			$image = '<img src="' . get_template_directory_uri() . '/assets/images/picture_placeholder.jpg" />';
			if (has_post_thumbnail()) {
				$image = get_the_post_thumbnail(get_the_ID(), 'medzone-blog-image');
			}
			?>
			<a href="<?php echo esc_url(get_the_permalink()); ?>" class="post-thumbnail">
				<?php echo wp_kses_post($image); ?>
			</a><!-- end .post-thumbnail -->
		</div><!-- end .col -->
		<div class="col-sm-12 col-md-6">
			<div class="post-header">
				<h4 class="post-title">
					<a href="<?php echo esc_url(get_the_permalink()); ?>"><?php the_title(); ?></a>
				</h4><!-- end .post-ttile -->
			</div><!-- .post-header -->
			<div class="post-content">
				<?php
				the_content();
				?>
			</div><!-- .post-content -->
			<div class="post-meta">
				<span class="posted-on"><?php echo esc_html(get_the_date()); ?></span> /
				<span class="byline"><?php esc_html_e('by', 'medzone-lite'); ?>
					<a class="post-author" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a>
				</span>
			</div><!-- .post-meta -->
		</div><!-- end .col -->
	</div><!-- end .row -->
</article>