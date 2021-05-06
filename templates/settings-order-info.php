<form data-behaviour='order-form'>
	<?php
		$settingsMetaOptionKey = 'store';
		$settingsOptions = array(
			'order_id'				=> 'Mailchimp Order ID',
			//'name'					=> 'Store Name',
			//'currency_code'	=> 'Currency Code',
		);
		foreach( $settingsOptions as $option_slug => $option_title ):
			$option_name = $settingsMetaOptionKey . "[" . $option_slug ."]";
			$option_value = isset( $_GET[ $option_slug ] ) ? $_GET[ $option_slug ] : "";

	?>
	<p>
		<label><?php _e( $option_title );?></label><br>
		<input type="text" name="<?php _e( $option_name );?>" style="width: 100%; max-width: 400px;" value="<?php _e( $option_value );?>" />
	</p>
	<?php endforeach;?>
	<input type="hidden" name="url" value="<?php _e( admin_url('options-general.php') . '?page=settings&action=order_info' );?>" />
	<p class='submit'><input type="submit" name="submit" class="button button-primary" value="Get"><p>
</form>

<?php

	$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();


	if( isset( $_GET['order_id'] ) ){
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
		$order = $mailchimpAPI->getOrderInfo( $_GET['order_id'] );

		//echo "<pre>";
		//print_r( $order );
		//echo "</pre>";

		if( isset( $order->id ) ){
			$i = 1;
			_e( "<h4>Mailchimp Order Information</h4>" );
			_e( "<ul class='stores-list'>" );
			_e( "<li class='grid-list meta'>" );
			_e( '<span class="number">#</span>' );
			_e( '<span class="amount">Amount</span>' );
			_e( '<span class="created">Created</span>' );
			_e( '<span class="payment-id">Payment #</span>' );
			_e( '<span class="customer">Customer</span>' );
			_e( "</li>" );


			_e( "<li class='grid-list'>" );
			_e( '<span class="number">' . $i . '</span>' );
			_e( '<span class="amount">' . $order->order_total . ' ' . $order->currency_code . '</span>' );
			_e( '<span class="created">' . $order->processed_at_foreign . '</span>' );
			_e( '<span class="payment-id">' . $order->id . '</span>' );
			_e( '<span class="customer">' . $order->customer->email_address . '</span>' );

			_e( "</li>" );

			_e( "</ul>" );

			//echo "<pre>";
			//print_r( $order->lines );
			//echo "</pre>";

			if( isset( $order->lines ) && count( $order->lines ) ){
				echo "<h5>Products</h5>";
				echo "<ul>";
				_e( "<li class='grid-list-products meta'>" );
				_e( '<span class="number">#</span>' );
				_e( '<span>Amount</span>' );
				_e( '<span>Quantity</span>' );
				_e( '<span>Product Title</span>' );
				_e( "</li>" );
				$i = 1;
				foreach( $order->lines as $product ){
					_e( "<li class='grid-list-products'>" );
					_e( '<span class="number">' . $i . '</span>' );
					_e( '<span>' . $product->price . ' ' . $order->currency_code . '</span>' );
					_e( '<span class="text-center">' . $product->quantity . '</span>' );
					_e( '<span>' . $product->product_title . '</span>' );
					_e( "</li>" );
					$i++;
				}
				echo "</ul>";
			}


			_e( '<button data-id="' . $order->id . '" class="order-delete-btn button">Delete This Order From Mailchimp</button>' );
		}
		else{
			$this->displayErrorNotice( "This order does not exist in the Mailchimp E-Commerce Store." );

		}
	}





?>
<style>
	li.grid-list.meta, li.grid-list-products.meta{
		color: #999;
	}
	li.grid-list, li.grid-list-products{
		display: grid;
		grid-template-columns: 40px 80px 200px 220px 250px;
		grid-gap: 10px;
		background: #fff;
		max-width: 750px;
	}
	li.grid-list span.number, li.grid-list-products span.number, span.text-center{ text-align: center; }
	li.grid-list span, li.grid-list-products span{
		padding: 5px;
		border-right: #ccc solid 1px;
	}

	li.grid-list span:last-child, li.grid-list-products span:last-child{ border: none;}



	li.grid-list-products{
		grid-template-columns: 40px 80px 70px 200px;
		max-width: 400px;
	}


</style>
<script>

	jQuery('[data-behaviour~=order-form]').each(function(){
			var $form 	= jQuery( this ),
				url				= $form.find('input[name=url]').first().val();


			$form.submit( function( ev ){
				ev.preventDefault();

				var order_id = $form.find('input[type=text]').first().val();

				location.href = url + "&order_id=" + order_id;

			} );
	} );

	jQuery('.order-delete-btn').each(function(){
			var $button 		= jQuery( this ),
				button_text 	= $button.html(),
				id 						= $button.data( 'id' );

			$button.click( function( ev ){
				ev.preventDefault();

				//console.log( window.payments[id] );

				$button.html( 'Loading...' );

				jQuery.ajax({
					url				: "<?php echo admin_url('admin-ajax.php'); ?>?action=stripe-mailchimp&event=deleteOrder",
					data			: {
						action 		: 'stripe-mailchimp',
						event			: 'deleteOrder',
						order_id	: id
					},
					success		: function( response ){
						$button.html( button_text );
						var url = jQuery('[data-behaviour~=order-form]').find('input[name=url]').first().val();
						location.href = url + "&order_id=" + id;
					}
				} );

			} );
	} );
</script>
