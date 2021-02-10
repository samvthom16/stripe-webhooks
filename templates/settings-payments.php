<?php

	$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();

	$cache_key = 'stripe-recent-payments1';
	$payments = array();

	// Get any existing copy of our transient data
	if ( false === ( $payments = get_transient( $cache_key ) ) ) {
		$payments = $stripe->listPayments();
		set_transient( $cache_key, $payments, 5 * MINUTE_IN_SECONDS );
	}

	//echo "<pre>";
	//print_r( $payments );
	//echo "</pre>";

	if( count( $payments ) ){

		$script_payments = array();

		_e( "<h4>List of Recent Payments</h4>" );
		_e( "<ul class='stores-list'>" );
		_e( "<li class='grid-list meta'>" );
		_e( '<span class="number">#</span>' );
		_e( '<span class="amount">Amount</span>' );
		_e( '<span class="created">Created</span>' );
		_e( '<span class="payment-id">Payment #</span>' );
		_e( '<span class="customer">Customer #</span>' );
		_e( "</li>" );

		$i = 1;
		foreach( $payments as $payment ){

			if( $payment->status == 'succeeded' ){
				$data = $stripe->filterPaymentIntentData( $payment );
				$script_payments[ $data['stripePaymentID'] ] = $data;

				_e( "<li class='grid-list'>" );
				_e( '<span class="number">' . $i . '</span>' );
				_e( '<span class="amount">' . $data['amount'] . ' ' . $data['currency'] . '</span>' );
				_e( '<span class="created">' . date('d M Y', $data['created'] ) . '</span>' );
				_e( '<span class="payment-id">' . $data['stripePaymentID'] . '</span>' );
				if( $data['stripeCustomerID'] ){
					_e( '<span class="customer">' . $data['stripeCustomerID'] . '&nbsp;<button data-id="' . $data['stripePaymentID'] . '" class="button">Sync</button></span>' );
				}
				_e( "</li>" );
				$i++;
			}
		}
		_e( "</ul>" );
		_e( "<br><hr>" );

		_e( "<script type='text/javascript'>");
		_e( "window.payments = " . json_encode( $script_payments ) . ";" );
		_e( "</script>");
	}
?>
<style>
	li.grid-list.meta{
		color: #999;
	}
	li.grid-list{
		display: grid;
		grid-template-columns: 40px 80px 100px 220px 250px;
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

	jQuery('li.grid-list span.customer button').each(function(){
			var $button 		= jQuery( this ),
				button_text 	= $button.html(),
				id 						= $button.data( 'id' );

			$button.click( function( ev ){
				ev.preventDefault();


				$button.html( 'Loading...' );

				jQuery.ajax({
					url				: "<?php echo admin_url('admin-ajax.php'); ?>?action=stripe-mailchimp&event=sync",
					data			: window.payments[id],
					success		: function( response ){
						alert( response );
						$button.html( button_text );
					}
				});

			} );
	} );
</script>
