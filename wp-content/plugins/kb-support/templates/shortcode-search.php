<?php
/**
 * This template is used to display the KB Article search form with [kbs_search]
 */
$single = kbs_get_article_label_singular();
$plural = kbs_get_article_label_plural();

$format = current_theme_supports( 'html5', 'search-form' ) ? 'html5' : 'xhtml';

$format = apply_filters( 'search_form_format', $format ); ?>

<div id="kbs_search_form" class="kbs_search">
	<?php if ( 'html5' == $format ) : ?>
    
        <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="text" class="search-field" placeholder="<?php echo sprintf( esc_attr_x( 'Search %s &hellip;', 'placeholder', 'kb-support' ), $plural ); ?>" value="<?php echo get_search_query(); ?>" name="s_article" />
            <input type="hidden" name="kbs_action" value="search_articles" />
            <button class="search-submit"><?php echo _x( 'Search', 'submit button', 'kb-support' ); ?></button>
        </form>
    
    <?php else : ?>
    
        <form role="search" method="get" id="searchform" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <div>
                <input type="text" value="<?php echo get_search_query(); ?>" name="s_article" id="s" placeholder="<?php echo sprintf( esc_attr_x( 'Search %s &hellip;', 'placeholder', 'kb-support' ), $plural ); ?>" />
                <input type="hidden" name="kbs_action" value="search_articles" />
                <input type="submit" id="searchsubmit" value="<?php echo _x( 'Search', 'submit button', 'kb-support' ); ?>" />
            </div>
        </form>
    
    <?php endif; ?>
</div>
