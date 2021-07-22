<?php

	$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();

	$args = array( 'limit'	=> 10 );

	$get_params = array( 'limit', 'starting_after', 'ending_before', 'created__gte', 'created__lte' );
	foreach( $get_params as $get_param ){
		if( isset( $_GET[ $get_param ] ) ){
			$args[ $get_param ] = $_GET[ $get_param ];
		}
	}


	//$uniqueID = substr( md5( json_encode( $args ) ), 0, 10 );

	//$cache_key = 'stripe-recent-payments' . $uniqueID;
	$payments = array();

	// Get any existing copy of our transient data
	//if ( false === ( $payments = get_transient( $cache_key ) ) ) {
		$payments = $stripe->listPayments( $args );
		//set_transient( $cache_key, $payments, 7 * MINUTE_IN_SECONDS );
	//}

	//echo "<pre>";
	//print_r( $payments );
	//echo "</pre>";

	if( isset( $payments->data ) && count( $payments->data ) ){

		_e( "<h4>List of Recent Payments</h4>" );
		_e( "<ul class='stores-list'>" );
		_e( "<li class='grid-list meta succeeded'>" );
		_e( '<span class="number">#</span>' );
		_e( '<span class="amount">Amount</span>' );
		_e( '<span class="created">Created</span>' );
		_e( '<span class="payment-id">Payment #</span>' );
		_e( '<span class="customer">Customer #</span>' );
		_e( "</li>" );

		$i = 1;
		$last_payment_id = '';
		$first_payment_id = '';
		foreach( $payments->data as $payment ){

			$data = $stripe->filterPaymentIntentData( $payment );

			if( $i == 1 ) $first_payment_id = $data['stripePaymentID'];

			$status = $payment->status;

			$last_payment_id = $data['stripePaymentID'];

			_e( "<li class='grid-list $status'>" );
			_e( '<span class="number">' . $i . '</span>' );
			_e( '<span class="amount">' . $data['amount'] . ' ' . $data['currency'] . '</span>' );
			_e( '<span class="created">' . date('d M Y', $data['created'] ) . '</span>' );
			_e( '<span class="payment-id">' . $data['stripePaymentID'] . '</span>' );
			_e( '<span class="customer">' );

			if( $status != 'succeeded' ){
				echo "<i title='$status' class='dashicons dashicons-bell'></i>&nbsp;";
			}

			echo $data['stripeCustomerID'];

			if( isset( $payment->status ) && $payment->status == 'succeeded' ){
				_e( '&nbsp;<button data-id="' . $data['stripePaymentID'] . '" class="button">Sync</button>' );
			}

			_e( '</span>' );
			_e( "</li>" );

			$i++;
		}
		_e( "</ul>" );

		//_e('<pre>');
		//print_r( $payments->has_more );
		//_e('</pre>');

		$get_params = array( 'action', 'limit', 'created__gte', 'created__lte' );
		$currentUrl = admin_url(	'options-general.php?page=settings' );
		foreach( $get_params as $get_param ){
			if( isset( $_GET[ $get_param ] ) ){
				$currentUrl .= "&" . $get_param . "=" . $_GET[ $get_param ];
			}
		}

		_e('<ul class="paginate-btns">');
		if( ( $payments->has_more && isset( $_GET['ending_before'] ) ) ||
				( isset( $_GET['starting_after'] ) )
			){
			$prevUrl = $currentUrl . "&ending_before=" . $first_payment_id;
			_e("<li><a href='$prevUrl' class='button'>Previous</a></li>");
		}
		if( ( $payments->has_more && isset( $_GET['starting_after'] ) ) ||
				( isset( $_GET['ending_before'] ) ) ||
				( !( isset( $_GET[ 'ending_before' ] ) ) && !( isset( $_GET[ 'starting_after' ] ) ) )
			){
			$nextUrl = $currentUrl . "&starting_after=" . $last_payment_id;
			_e("<li><a href='$nextUrl' class='button'>Next</a></li>");
		}
		_e('</ul>');

	}
?>
<style>
	.paginate-btns li{ display: inline-block; }
	.paginate-btns li:not(:first-child){ margin-left: 15px;}

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

	li.grid-list:not(.succeeded){
		border: #d63638 solid 1px;
		background: #d63638;
		color: #fff;
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

				//console.log( window.payments[id] );

				$button.html( 'Loading...' );

				jQuery.ajax({
					url				: "<?php echo admin_url('admin-ajax.php'); ?>?action=stripe-mailchimp&event=syncPayment",
					data			: {
						stripePaymentID: id
					},
					success		: function( response ){
						alert( response );
						$button.html( button_text );
					}
				});

			} );
	} );
</script>
