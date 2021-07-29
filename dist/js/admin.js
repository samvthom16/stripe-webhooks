jQuery.fn.form_redirect  = function(){
	return this.each( function(){
		var $form 	= jQuery( this );

		function getURL(){
			var url = $form.find('input[name=url]').val();
			$form.find( 'input.form-field' ).each( function(){
				var $input  = jQuery( this ),
					name			= $input.attr( 'name' ),
					value			= $input.val();

				url += "&" + name + "=" + value;
			} );
			return url;
		}

		$form.submit( function( ev ){
			ev.preventDefault();
			location.href = getURL();
		} );
	} );
};

jQuery.fn.export_csv = function(){
	return this.each( function(){
		var $btn = jQuery( this ),
			data 	= window.browserData.csvdata;

		console.log( data );

		$btn.click( function(){

			jQuery.ajax( {
				url				: data['settings']['ajax_url'],
				type			: 'POST',
				dataType	: 'json',
				data			: data,
				success: function( response ){
					//console.log( response );
					window.open( response.url );
				}
			} );


		} );



	} );
};

jQuery( document ).ready( function(){
	jQuery('[data-behaviour~=form-redirect]').form_redirect();
	jQuery('[data-behaviour~=btn-export-csv]').export_csv();
} );
