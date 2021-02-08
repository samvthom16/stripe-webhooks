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

			case 'syncProducts':
				$response = $mailchimpAPI->syncProducts();
				break;

			case 'sync':
				/* this is a proper ajax request */

				echo $this->syncMailchimp( $_GET['stripePaymentID'], $_GET['stripeCustomerID'], $_GET['amount'], $_GET['currency'], $_GET['created'] );

				//print_r( $_GET );
				wp_die();

			/*
			case 'order':
				$amount = 20;
				$email_address = 'sam@sputznik.com';
				$response = $mailchimpAPI->createOrderForAmount( $email_address, $amount );
				break;
			*/

			case 'deleteOrder':
				$order_id = 'pi_1IIebGKEe1YgzvEqs2mdmpAB';
				$store_id = $mailchimpAPI->getStoreID();
				$apiURL = "ecommerce/stores/$store_id/orders/$order_id";
				echo $apiURL;
				$response = $mailchimpAPI->processRequest( $apiURL, array(), true );
				break;

			case 'orders':

				$store_id = $mailchimpAPI->getStoreID();
				$response = $mailchimpAPI->processRequest( 'ecommerce/stores/' . $store_id . '/orders' );
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

	function syncMailchimp( $stripePaymentID, $stripeCustomerID, $amount, $currency, $created ){

		$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$mailchimpOrder = $mailchimpAPI->getOrderInfo( $stripePaymentID );

		if( isset( $mailchimpOrder->id ) ){
			return 'Mailchimp Order with the same ID: ' . $stripePaymentID . ' already exists.';
		}

		$email_address = $stripe->getEmailFromCustomerID( $stripeCustomerID );

		$order = array(
			'id'										=> $stripePaymentID,
			'order_total'						=> $amount,
			'currency_code'					=> $currency,
			'processed_at_foreign'	=> date('c', $created )
		);
		$response = $mailchimpAPI->createOrderForEmailAddress( $email_address, $order );

		if( isset( $response->id ) ){
			return "Order has been succesfully created with ID: " . $response->id;
		}

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
					$amount = $paymentIntent->amount;
					$amount = $amount > 0 ? (float) $amount/100 : 0;
					echo $this->syncMailchimp( $paymentIntent->id, $paymentIntent->customer, $amount, strtoupper( $paymentIntent->currency ), $paymentIntent->created );
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
