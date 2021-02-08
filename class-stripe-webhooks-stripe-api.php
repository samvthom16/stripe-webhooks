<?php

	class STRIPE_WEBHOOKS_STRIPE_API extends STRIPE_WEBHOOKS_BASE{

		var $settings;

		function __construct(){
			require_once('stripe-php/init.php');

			$admin = STRIPE_WEBHOOKS_ADMIN::getInstance();
			$this->setSettings( $admin->getSettings() );

			\Stripe\Stripe::setApiKey( $this->getSecretKey() );
		}

		function setSettings( $settings ){ $this->settings = $settings; }
		function getSettings(){ return $this->settings; }

		function getEachSetting( $key ){
			$settings = $this->getSettings();
			if( isset( $settings[ $key ] ) ) return $settings[ $key ];
			return '';
		}

		function getSecretKey(){ return $this->getEachSetting( 'stripeSecret' ); }
		function getPublishableKey(){ return $this->getEachSetting( 'stripePublishable' ); }

		function getEventFromPayload(){
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
			return $event;
		}

		function getCustomer( $customer_id ){
			return \Stripe\Customer::retrieve( $customer_id );
		}

		function getEmailFromCustomerID( $customer_id ){
			$customer = $this->getCustomer( $customer_id );
			if( isset( $customer->email ) ) return $customer->email;
			return '';
		}

		function listPayments(){
			$payments = \Stripe\PaymentIntent::all( array(
				'limit'	=> 30
			) );
			if( isset( $payments->data ) ) return $payments->data;
			return array();
		}

	}
