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

jQuery( document ).ready( function(){
	jQuery('[data-behaviour~=form-redirect]').form_redirect();
} );
