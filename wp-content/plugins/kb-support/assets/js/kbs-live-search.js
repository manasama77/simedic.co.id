jQuery(document).ready(function ($) {

	var search_timeout_id = null;

	// Hide the articles when the close link is clicked
	$('#close-search').click(function() {
        $('.kbs-article-search-results').hide('slow');
    });

	// Execute the article search
	function find_article( search_text )	{

		$('#kbs-article-results').html('');

		if( search_text.length < kbs_search_vars.min_search_trigger )	{
			$('.kbs-article-search-results').hide('slow');
			return;
		}

		$('.kbs-article-search-results').hide('fast');
		$('#kbs-loading').html('<img src="' + kbs_scripts.ajax_loader + '" />');
		$('#kbs-loading').show('fast');

		var postData = {
			term   : search_text,
			action : 'kbs_ajax_article_search'
		};

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : postData,
			url        : kbs_scripts.ajaxurl,
			success    : function (response) {
				if ( response.articles && '' !== response.articles )	{
					$('#kbs-article-results').html(response.articles);
					$('.kbs-article-search-results').show('slow');
				} else	{
					$('#kbs-article-results').html();
					$('.kbs-article-search-results').hide('slow');
				}
			},
			complete: function()	{
				$('#kbs-loading').hide('fast');
				$('#kbs-loading').html('');
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

	}

	// Calls the article search function
	$( '.kbs-article-search' ).keyup( function(e)	{
		clearTimeout( search_timeout_id );
		search_timeout_id = setTimeout( find_article.bind( undefined, e.target.value ), 500 );
	});

});