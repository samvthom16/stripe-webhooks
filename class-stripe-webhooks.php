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

			case 'order':
				$amount = 20;
				$email_address = 'sam@sputznik.com';
				$response = $mailchimpAPI->createOrderForAmount( $email_address, $amount );
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

		$payload = @file_get_contents('php://input');
		$event = null;

		try {
				$event = \Stripe\Event::constructFrom(
						json_decode($payload, true)
				);
		} catch(\UnexpectedValueException $e) {
				// Invalid payload
				http_response_code(400);
				exit();
		}

		//echo "<pre>";
		//print_r( $event );
		//echo "</pre>";

		/* Handle the event */
		switch ($event->type) {
				case 'payment_intent.succeeded':
						$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent

						echo $paymentIntent->id;
						echo $paymentIntent->amount;
						echo $paymentIntent->customer;
						echo $paymentIntent->currency;
						
						// Then define and call a method to handle the successful payment intent.
						// handlePaymentIntentSucceeded($paymentIntent);
						break;
				case 'payment_method.attached':
						$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
						// Then define and call a method to handle the successful attachment of a PaymentMethod.
						// handlePaymentMethodAttached($paymentMethod);
						break;
				// ... handle other event types
				default:
						echo 'Received unknown event type ' . $event->type;
		}

		http_response_code(200);
		wp_die();
	}

}

STRIPE_WEBHOOKS::getInstance();
