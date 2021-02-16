<?php

class STRIPE_WEBHOOKS extends STRIPE_WEBHOOKS_BASE{

	var $metafields;

	function __construct(){
		add_action( 'wp_ajax_nopriv_stripe-webhooks', array( $this, 'process' ) );
		add_action( 'wp_ajax_stripe-webhooks', array( $this, 'process' ) );

		add_action( 'wp_ajax_stripe-mailchimp', array( $this, 'mailchimp' ) );
		add_action( 'wp_ajax_nopriv_stripe-mailchimp', array( $this, 'mailchimp' ) );

		add_action( 'give_after_donation_levels', array( $this, 'give_hidden_fields' ) );
		add_filter( 'give_stripe_prepare_metadata', array( $this, 'give_stripe_prepare_data' ), 10, 3 );

	}

	function getMetafields(){
		return array(
			'utm_source',			// Google Analytics
			'utm_campaign',		// Google Analytics
			'utm_medium',			// Google Analytics
			'utm_term',				// Google Analytics
			'mc_cid',					// Mailchimp Campaign ID
			'mc_eid'					// Mailchimp User ID
		);
	}

	function give_hidden_fields(){
		$metafields = $this->getMetafields();
		foreach( $metafields as $metafield ){
			$metavalue = isset( $_GET[ $metafield ] ) ? $_GET[ $metafield ] : "";
			_e("<input type='hidden' name='$metafield' value='$metavalue' />");
		}
	}

	function give_stripe_prepare_data( $args, $donation_id, $donation_data ){
		if( is_array( $donation_data ) && isset( $donation_data['post_data'] ) ){
			$metafields = $this->getMetafields();
			foreach( $metafields as $metafield ){
				$args[ $metafield ] = isset( $donation_data['post_data'][ $metafield ] ) ? $donation_data['post_data'][ $metafield ] : "";
			}
		}
		return $args;
	}

	function mailchimp(){

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$response = array();

		$event = isset( $_GET['event'] ) ? $_GET['event'] : '';

		switch( $event ){

			case 'syncProducts':
				$response = $mailchimpAPI->syncProducts();
				break;

			case 'sync':
				/* this is a proper ajax request */
				if( isset( $_GET['stripePaymentID'] ) && isset( $_GET['stripeCustomerID'] ) && isset( $_GET['amount'] ) && isset( $_GET['currency'] ) && isset( $_GET['created'] ) ){
					echo $this->syncMailchimp( $_GET );
				}

				//print_r( $_GET );
				wp_die();

			/*
			case 'order':
				$amount = 20;
				$email_address = 'sam@sputznik.com';
				$response = $mailchimpAPI->createOrderForAmount( $email_address, $amount );
				break;
			*/

			case 'uniqueUser':
				$unique_id = 'e14fd6d3ef'; //
				$response = $mailchimpAPI->getUniqueCustomer( $unique_id );

				if( $response == null ){
					echo "Empty response";
				}

				//$response = $mailchimpAPI->getStoreInfo();
				//echo $response->list_id;
				break;

			case 'deleteOrder':
				if( isset( $_GET['order_id'] ) ){
					$order_id = $_GET['order_id'];
					$store_id = $mailchimpAPI->getStoreID();
					$apiURL = "ecommerce/stores/$store_id/orders/$order_id";
					$response = $mailchimpAPI->processRequest( $apiURL, array(), true );
				}
				break;

			case 'orders':
				$store_id = $mailchimpAPI->getStoreID();
				$response = $mailchimpAPI->processRequest( 'ecommerce/stores/' . $store_id . '/orders' );
				break;

			case 'resetProducts':
				$store_id = $mailchimpAPI->getStoreID();
				$products = $mailchimpAPI->processRequest( 'ecommerce/stores/' . $store_id . '/products' );
				if( isset( $products->products ) && is_array( $products->products ) ){
					foreach( $products->products as $product ){
						$product_id = $product->id;
						$apiURL = "ecommerce/stores/$store_id/products/$product_id";
						array_push( $response, $mailchimpAPI->processRequest( $apiURL, array(), true ) );
					}
				}
				break;

			case 'stores':
				$response = $mailchimpAPI->processRequest( 'ecommerce/stores' );
				break;

		}

		echo "<pre>";
		print_r( $response );
		echo "</pre>";

		wp_die();
	}

	// CHECK IF UNIQUE MAILCHIMP USER ID EXISTS, IF YES THE RETRIEVE FROM MAILCHIMP
	// OR GET IT FROM STRIPE ITSELF
	function getEmailAddressFromMailchimpOrStripe( $data ){
		$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
		if( isset( $data['mailchimp_user_id'] ) ){
			$customer = $mailchimpAPI->getUniqueCustomer( $data['mailchimp_user_id'] );
			if( $customer!=null && isset( $customer->email_address ) && !empty( $customer->email_address ) ){
				return $customer->email_address;
			}
		}
		return $stripe->getEmailFromCustomerID( $data['stripeCustomerID'] );
	}

	function syncMailchimp( $data ){

		$stripePaymentID = $data['stripePaymentID'];
		$stripeCustomerID = $data['stripeCustomerID'];
		$amount = $data['amount'];
		$currency = $data['currency'];
		$created = $data['created'];

		$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$mailchimpOrder = $mailchimpAPI->getOrderInfo( $stripePaymentID );
		if( isset( $mailchimpOrder->id ) ){
			return 'Mailchimp Order with the same ID: ' . $stripePaymentID . ' already exists.';
		}

		$email_address = $this->getEmailAddressFromMailchimpOrStripe( $data );

		$order = array(
			'id'										=> $stripePaymentID,
			'order_total'						=> $amount,
			'currency_code'					=> $currency,
			'processed_at_foreign'	=> date('c', $created )
		);

		if( isset( $data['campaign_id'] ) ){
			$order['campaign_id'] = $data['campaign_id'];
		}

		//print_r( $order );

		$response = $mailchimpAPI->createOrderForEmailAddress( $email_address, $order );

		if( isset( $response->id ) ){
			return "Order has been succesfully created with ID: " . $response->id;
		}

		//print_r( $response );

		return "Order could not be created for some reason.";
	}

	function process(){
		//require_once('stripe-php/init.php');

		$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();

		$event = $stripe->getEventFromPayload();

		//$customer = $stripe->getCustomer( 'cus_Gq8h2kaM2xherm' );

		//echo "<pre>";
		//print_r(  );
		//echo "</pre>";

		/* Handle the event */
		switch ( $event->type ) {
			case 'payment_intent.succeeded':
				$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
				if( isset( $paymentIntent->customer ) && !empty( $paymentIntent->customer ) ){
					$data = $stripe->filterPaymentIntentData( $paymentIntent );
					/*
					$data = array(
						'stripePaymentID' 	=> $paymentIntent->id,
						'stripeCustomerID' 	=> $paymentIntent->customer,
						'amount'						=> $paymentIntent->amount > 0 ? (float) $paymentIntent->amount/100 : 0,
						'currency'					=> strtoupper( $paymentIntent->currency ),
						'created'						=> $paymentIntent->created
					);
					*/
					echo $this->syncMailchimp( $data );
				}
				break;
			default:
				echo 'Received unknown event type ' . $event->type;
		}

		http_response_code(200);
		wp_die();
	}

}

STRIPE_WEBHOOKS::getInstance();
