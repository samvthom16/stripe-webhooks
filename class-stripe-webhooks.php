<?php

class STRIPE_WEBHOOKS extends STRIPE_WEBHOOKS_BASE{

	function __construct(){
		add_action( 'wp_ajax_nopriv_stripe-webhooks', array( $this, 'process' ) );
		add_action( 'wp_ajax_stripe-webhooks', array( $this, 'process' ) );

		add_action( 'wp_ajax_stripe-mailchimp', array( $this, 'mailchimp' ) );
		add_action( 'wp_ajax_nopriv_stripe-mailchimp', array( $this, 'mailchimp' ) );

	}

	function mailchimp(){

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$response = array();

		$event = isset( $_GET['event'] ) ? $_GET['event'] : '';

		switch( $event ){

			case 'sync':
				$response = $mailchimpAPI->syncProducts();
				break;

			/*
			case 'order':
				$amount = 20;
				$email_address = 'sam@sputznik.com';
				$response = $mailchimpAPI->createOrderForAmount( $email_address, $amount );
				break;
			*/

			case 'deleteOrder':
				$order_id = 'order1612454175';
				$store_id = $mailchimpAPI->getStoreID();
				$apiURL = "ecommerce/stores/$store_id/orders/$order_id";
				echo $apiURL;
				$response = $mailchimpAPI->processRequest( $apiURL, array(), true );
				break;

			case 'orders':
				$store_id = $mailchimpAPI->getStoreID();
				$response = $mailchimpAPI->cachedProcessRequest( 'ecommerce/stores/' . $store_id . '/orders' );
				break;

			case 'resetOrders':
				//order1612519921
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

	function process(){
		require_once('stripe-php/init.php');

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
					$email_address = $stripe->getEmailFromCustomerID( $paymentIntent->customer );
					$currency_code = strtoupper( $paymentIntent->currency );
					$amount = $paymentIntent/100;

					$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
					$response = $mailchimpAPI->createOrderForAmount( $email_address, $amount, $currency_code );

					if( isset( $response->id ) ){
						echo "Order has been succesfully created with ID: " . $response->id;
					}
				}





				// Then define and call a method to handle the successful payment intent.
				// handlePaymentIntentSucceeded($paymentIntent);
				break;

			default:
				echo 'Received unknown event type ' . $event->type;
		}

		http_response_code(200);
		wp_die();
	}

}

STRIPE_WEBHOOKS::getInstance();
