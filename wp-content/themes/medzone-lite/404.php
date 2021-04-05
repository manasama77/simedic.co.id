<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package MedZone_Lite
 */

get_header();
?>
	<div id="content">
		<div class="container">
			<div class="row">
				<section class="no-results col-sm-12 not-found">
					<header class="page-header">
						<h3 class="page-title"><span><?php esc_html_e( 'Nothing Found', 'medzone-lite' ); ?></span></h3>
					</header><!-- .page-header -->

					<div class="page-content">
						<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'medzone-lite' ); ?></p>

						<?php get_search_form(); ?>
					</div><!-- .page-content -->
				</section><!-- .no-results -->
			</div>
		</div>
	</div>
<?php
get_footer();
