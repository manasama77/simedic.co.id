<?php	
/**
 * Manage ticket_sources taxonomy.
 * 
 * @since		1.2.9
 * @package		KBS
 * @subpackage	Taxonomies
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Ensure that the default ticket sources cannot be deleted by removing the 
 * delete option from the hover menu on the edit screen.
 * 
 * @since	1.2.9
 * @param	array     $actions		The array of actions in the hover menu
 * @param	object    $tag			The object array for the term
 * @return	array     The filtered array of actions in the hover menu
 */
function kbs_ticket_source_remove_delete_row_action( $actions, $tag )	{

    $protected = kbs_get_protected_ticket_source_term_ids();

	if ( in_array( $tag->term_id, $protected ) )	{
        if ( isset( $actions['delete'] ) )  {
		  unset( $actions['delete'] );
        }
        if ( isset( $actions['inline hide-if-no-js'] ) )    {
            unset( $actions['inline hide-if-no-js'] );
        }
	}

    // Remove the 'view' option for all terms
    if ( isset( $actions['view'] ) )    {
        unset( $actions['view'] );
    }

	return $actions;

} // kbs_ticket_source_remove_delete_row_action
add_filter( 'ticket_source_row_actions', 'kbs_ticket_source_remove_delete_row_action', 10, 2 );

/**
 * Ensure that the default website term cannot be deleted by removing the 
 * bulk action checkbox.
 *
 * @since	1.2.9
 * @return	void
 */
function kbs_ticket_source_website_term_remove_checkbox()	{

	if ( ! isset( $_GET['taxonomy'] ) || 'ticket_source' != $_GET['taxonomy'] )	{
		return;
	}

    $protected = kbs_get_protected_ticket_source_term_ids();
	$terms     = kbs_get_ticket_source_terms();

	if ( empty( $terms ) )	{
		return;
	}

	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php
		foreach( $terms as $term )	{

			if ( ! empty( $term->term_id ) && in_array( $term->term_id, $protected ) )	{
				?>$('input#cb-select-<?php echo $term->term_id; ?>').prop('disabled', true).hide();<?php
			}

		}
		?>
	});
	</script>
	<?php
} // kbs_edd_download_terms_remove_checkbox
add_action( 'admin_footer-edit-tags.php', 'kbs_ticket_source_website_term_remove_checkbox' );

/**
 * Make the default ticket term slug read-only when editing.
 *
 * @since	1.2.9
 * @param	object    $tag    The tag object
 * @return	string
 */
function kbs_set_default_ticket_source_terms_readonly( $tag )	{

    $protected = kbs_get_protected_ticket_source_term_ids();

	if ( in_array( $tag->term_id, $protected ) )	{
		?>
        <script type="text/javascript">
		jQuery().ready(function($)	{
			$("#slug").attr('readonly','true');
            $('#delete-link').addClass('kbs-hidden');
		});
		</script>
        <?php
	}
} // kbs_set_default_ticket_source_terms_readonly
add_action( 'ticket_source_edit_form_fields', 'kbs_set_default_ticket_source_terms_readonly' );
