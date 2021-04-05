<?php
/**
 * The template for displaying footer part
 *
 * @package MedZone_Lite
 */

get_sidebar( 'footer' );
get_template_part( 'template-parts/footer/copyright' );
?>
</div>

<?php if ( get_theme_mod( 'medzone_lite_enable_go_top', true ) ) : ?>
	<a id="back-to-top" href="#"><i class="fa fa-angle-up"></i></a>
<?php endif; ?>

<?php wp_footer(); ?>
</body></html>
