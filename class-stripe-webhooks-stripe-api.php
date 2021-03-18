<?php

	class STRIPE_WEBHOOKS_STRIPE_API extends STRIPE_WEBHOOKS_BASE{

		var $settings;

		function __construct(){
			$admin = STRIPE_WEBHOOKS_ADMIN::getInstance();
			$this->setSettings( $admin->getSettings() );
			//\Stripe\Stripe::setApiKey( $this->getSecretKey() );
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
					$event = json_decode( $payload, true );

					/*
					if( isset( $data['type'] ) ){
						$event->type = $data['type'];
					}

					if( isset( $data['data'] ) && isset( $data['data']['object'] ) ){
						$event->paymentIntent = $data['data']['object'];
					}

					/*
					$event = \Stripe\Event::constructFrom(
							json_decode($payload, true)
					);
					*/
			} catch(\UnexpectedValueException $e) {
					// Invalid payload
					http_response_code(400);
					exit();
			}
			return $event;
		}

		function getCustomer( $customer_id ){
			return $this->processRequest( "customers/$customer_id" );
		}

		function getEmailFromCustomerID( $customer_id ){
			$customer = $this->getCustomer( $customer_id );
			if( isset( $customer->email ) ) return $customer->email;
			return '';
		}

		function listPayments( $data = array( 'limit' => 10 ) ){

			$url = "payment_intents?limit=" . $data['limit'];
			if( isset( $data['starting_after'] ) && $data['starting_after'] ){
				$url .= "&starting_after=" . $data['starting_after'];
			}

			return $this->processRequest( $url );

			//if( isset( $payments->data ) ) return $payments->data;
			//return array();
		}

		function getPaymentIntent( $payment_id ){
			return $this->processRequest( "payment_intents/$payment_id" );
		}

		function getBaseURL(){ return "https://api.stripe.com/v1/";}

		function processRequest( $partUrl, $postParams = array(), $deleteFlag = false ){

			$url = $this->getBaseURL() . $partUrl;


			//echo $url;

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Bearer '.$this->getSecretKey() ) );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

			if( count( $postParams ) ){
				curl_setopt( $ch, CURLOPT_POST, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $postParams ) );

				/*
				echo "<pre>";
				print_r( json_encode( $postParams ) );
				echo "</pre>";
				*/
			}

			if( $deleteFlag ){
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
			}

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$result = curl_exec($ch);
			return json_decode($result);
		}

		function filterPaymentIntentData( $payment ){

			$amount = $payment->amount > 0 ? (float) $payment->amount/100 : 0;
			$currency = strtoupper( $payment->currency );
			$created = date('d M Y', $payment->created );

			$data = array(
				'stripePaymentID'		=> $payment->id,
				'stripeCustomerID'	=> ( !isset( $payment->customer ) || !$payment->customer ) ? null : $payment->customer,
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

			if( isset( $payment->metadata ) && isset( $payment->metadata->mc_sid ) && !empty( $payment->metadata->mc_sid ) ){
				$data[ 'mailchimp_store_id' ] = $payment->metadata->mc_sid;
			}

			return $data;
		}

	}
