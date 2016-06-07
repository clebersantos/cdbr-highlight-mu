jQuery(function() {
	 jQuery('#highlights-loading').hide();


	jQuery(".highlights-delete").click( function(){
		
		var post_id, blog_id;

		var c = confirm("Tem certeza que deseja limpar o destaque?");
		
		if( c == true ) {

			jQuery( this ).parent().parent().remove();

			refreshHighlights();			
		}

		return false;

	});

	jQuery( "#highlights-sortable" ).sortable({
		placeholder: "ui-state-highlight",
		revert: "invalid",
		stop: function( event, ui ) {
			refreshHighlights();
		}
	});
	jQuery( "#highlights-sortable" ).disableSelection();

	jQuery( "#highlights-activities-sortable li" ).draggable({
		connectToSortable: '#highlights-sortable',
		zIndex: 1,
		cursor: "move",
	    helper: 'clone'
     });

	function refreshHighlights() {

		jQuery( "#highlights-loading" ).show();

		var highlight = [];

		jQuery( "#highlights-sortable li" ).each( function( i ) {
			
			jQuery( this ).find( "#order" ).val( i + 1 );

			highlight[i] = {
				order: i,
				post_id: jQuery( this ).find( '#post_id' ).val(),
				blog_id: jQuery( this ).find( '#blog_id' ).val(),
				highlight_title: jQuery( this ).find( '#highlight_title' ).val(),
				highlight_excerpt: jQuery( this ).find( '#highlight_excerpt' ).val()
			};
			
		});

		var new_highlight = jQuery.map(highlight, function(value, index) {
		    return [value];
		});

		var data = {
			"action": "order_highlights",
			nonce: highlightsAjax.nonce,
			order: new_highlight
		};

		jQuery.post(
			highlightsAjax.ajaxurl,
			data,
			function(response) {
	            // jQuery('.updated').html( response.data );
	            jQuery('#highlights-loading').hide();
	            return false;
	        }
		);
	}

});
