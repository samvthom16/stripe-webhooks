<?php

	class STRIPE_WEBHOOKS_STRIPE_API extends STRIPE_WEBHOOKS_BASE{

		var $settings;

		function __construct(){
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
				'limit'	=> 10
			) );
			if( isset( $payments->data ) ) return $payments->data;
			return array();
		}

		function filterPaymentIntentData( $payment ){
			$amount = $payment->amount > 0 ? (float) $payment->amount/100 : 0;
			$currency = strtoupper( $payment->currency );
			$created = date('d M Y', $payment->created );

			$data = array(
				'stripePaymentID'		=> $payment->id,
				'stripeCustomerID'	=> $payment->customer,
				'amount'						=> $amount,
				'currency'					=> $currency,
				'created'						=> $payment->created
			);

			if( isset( $payment->metadata ) && isset( $payment->metadata->mc_cid ) && !empty( $payment->metadata->mc_cid ) ){
				$data[ 'campaign_id' ] = $payment->metadata->mc_cid;
			}

			if( isset( $payment->metadata ) && isset( $payment->metadata->mc_eid ) && !empty( $payment->metadata->mc_eid ) ){
				$data[ 'mailchimp_user_id' ] = $payment->metadata->mc_eid;
			}

			return $data;
		}

	}
