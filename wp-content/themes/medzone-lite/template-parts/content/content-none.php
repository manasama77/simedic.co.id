<?php
/**
 * Template part for displaying single posts.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package MedZone_Lite
 */

?>
<section class="no-results not-found">
	<header class="page-header">
		<h3 class="page-title"><span><?php esc_html_e( 'Nothing Found', 'medzone-lite' ); ?></span></h3>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<?php
			$kses = array(
				'a' => array(
					'href' => array(),
				),
			);
			?>

			<p>
				<?php
				// Translators: %s is a link.
				printf( wp_kses( __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'medzone-lite' ), $kses ), esc_url( admin_url( 'post-new.php' ) ) );
				?>
			</p>

		<?php elseif ( is_search() ) : ?>

			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'medzone-lite' ); ?></p>

			<?php get_search_form(); ?>

		<?php else : ?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'medzone-lite' ); ?></p>

			<?php get_search_form(); ?>

		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
