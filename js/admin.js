jQuery(function($){

	var $tagnames = $('<select style="width: 95%;"><option value=""></option></sclect>');
	$.each( KDContinentFilter.continent_codes, function( i, v ) {
		$tagnames.append( '<option value="' + i + '">' + v + '</option>' );
	} );

	$('input[name=tag-name]').hide().after( $tagnames );

	$tagnames.on('change',function(){

		$(this).prev().val( $(this).find('option:selected').text() );
		$('input[name=slug]').val( $(this).val() );

	});

	$(document).on('DOMNodeInserted', function(e) {
	    if ( $(e.target).get(0).tagName.toLowerCase() == 'tr' ) {
	       $tagnames.val('');
	    }
	});

});