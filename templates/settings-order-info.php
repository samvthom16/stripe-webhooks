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
		}
		else{
			$this->displayErrorNotice( "This order does not exist in the Mailchimp E-Commerce Store." );

		}



		//echo "<pre>";
		//print_r( $order );
		//echo "</pre>";

	}





?>
<style>
	li.grid-list.meta{
		color: #999;
	}
	li.grid-list{
		display: grid;
		grid-template-columns: 40px 80px 200px 220px 250px;
		grid-gap: 10px;
		background: #fff;
		max-width: 750px;
	}
	li.grid-list span.number{ text-align: center; }
	li.grid-list span{
		padding: 5px;
		border-right: #ccc solid 1px;
	}
	li.grid-list span.customer{
		border: none;
	}
	li.grid-list span.customer button{
		padding: 3px 10px;
		vertical-align: middle;
		margin-left: 10px;
		min-height: 24px;
		line-height: 1;
		float: right;
		border-color: #999;
		color: #555;
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
</script>
