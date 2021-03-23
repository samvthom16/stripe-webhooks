<?php

	class STRIPE_WEBHOOKS_STRIPE_API extends STRIPE_WEBHOOKS_API{

		var $settings;

		/* API RELATED DATA */
		function getBaseURL(){ return "https://api.stripe.com/v1/";}
		function getHTTPHeader(){ return array( 'Content-Type: application/json', 'Authorization: Bearer '.$this->getSecretKey() ); }
		function getSecretKey(){ return $this->getEachSetting( 'stripeSecret' ); }
		function getPublishableKey(){ return $this->getEachSetting( 'stripePublishable' ); }
		/* API RELATED DATA */

		/*
		* USED TO HANDLE WEBHOOK EVENT
		*/
		function getEventFromPayload(){
			$payload = @file_get_contents('php://input');
			$event = null;
			try {
					$event = json_decode( $payload, true );
			} catch( Exception $e ) {
					// Invalid payload
					http_response_code(400);
					exit();
			}
			return $event;
		}

		function getCustomer( $customer_id ){
			return $this->processRequest( "customers/$customer_id" );
		}

		/*
		function filterDataForMailchimp( $customer ) {

			$data = array(
				'first_name' 	=> '',
				'last_name' 	=> '',
			);

	    $name = trim( $customer->name );
	    $data['last_name'] = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
	    $data['first_name'] = trim( preg_replace('#'.preg_quote($last_name,'#').'#', '', $name ) );

	    return $data;
		}
		*/

		function getEmailFromCustomerID( $customer_id ){
			$customer = $this->getCustomer( $customer_id );
			if( isset( $customer->email ) ) return $customer->email;
			return '';
		}

		function listPayments( $data = array( 'limit' => 10 ) ){
			$params = array();

			$url = "payment_intents?limit=" . $data['limit'];
			if( isset( $data['starting_after'] ) && $data['starting_after'] ){
				$url .= "&starting_after=" . $data['starting_after'];
			}
			elseif( isset( $data['ending_before'] ) && $data['ending_before'] ){
				$url .= "&ending_before=" . $data['ending_before'];
			}

			if( isset( $data['created__gte'] ) && $data['created__gte'] ){
				$url .= "&created[gte]=" . strtotime( $data['created__gte'] );
			}
			if( isset( $data['created__lte'] ) && $data['created__lte'] ){
				$url .= "&created[lte]=" . strtotime( $data['created__lte'] );
			}

			return $this->processRequest( $url );
		}

		function getPaymentIntent( $payment_id ){
			return $this->processRequest( "payment_intents/$payment_id" );
		}

		/*
		* CONVERT PAYMENT INTENT OBJECT INTO A SMALL ARRAY WITH ONLY THE REQUIRED VALUES
		*/
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
