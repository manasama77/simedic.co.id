<?php
/**
 * The template part displaying the search form
 *
 * @package MedZone_Lite
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( get_home_url() ); ?>">
	<label> <span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'medzone-lite' ); ?></span>
		<input class="search-field" placeholder="<?php esc_attr_e( 'type the search term... ', 'medzone-lite' ); ?>" value="" name="s" type="search">
	</label> <input class="search-submit" value="<?php esc_attr_e( 'Search', 'medzone-lite' ); ?>" type="submit">
</form>
