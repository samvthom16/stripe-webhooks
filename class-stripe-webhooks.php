<?php

class STRIPE_WEBHOOKS extends STRIPE_WEBHOOKS_BASE{

	var $metafields;

	function __construct(){
		add_action( 'wp_ajax_nopriv_stripe-webhooks', array( $this, 'process' ) );
		add_action( 'wp_ajax_stripe-webhooks', array( $this, 'process' ) );

		add_action( 'wp_ajax_stripe-mailchimp', array( $this, 'mailchimp' ) );
		add_action( 'wp_ajax_nopriv_stripe-mailchimp', array( $this, 'mailchimp' ) );

		//add_action( 'give_after_donation_levels', array( $this, 'give_hidden_fields' ) );
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

	/*
	function give_hidden_fields(){
		$metafields = $this->getMetafields();
		foreach( $metafields as $metafield ){
			$metavalue = isset( $_GET[ $metafield ] ) ? $_GET[ $metafield ] : "";
			_e("<input type='hidden' name='$metafield' value='$metavalue' />");
		}
	}
	*/

	function give_stripe_prepare_data( $args, $donation_id, $donation_data ){
		$params = array();
		if( is_array( $donation_data ) && isset( $donation_data['post_data'] ) && isset( $donation_data['post_data']['give-current-url'] ) ){
			$url = $donation_data['post_data']['give-current-url'];
			$url_components = parse_url( $url );
			if( isset( $url_components['query'] ) ){
				parse_str( $url_components['query'], $params );
				$metafields = $this->getMetafields();
				foreach( $metafields as $metafield ){
					$args[ $metafield ] = isset( $params[ $metafield ] ) ? $params[ $metafield ] : "";
				}
			}
		}

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
		$args['mc_sid'] = $mailchimpAPI->getStoreID();

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

			case 'syncInvoice':
				if( isset( $_GET['stripePaymentID'] ) ){
					echo $this->syncMailchimpWithStripe( $_GET['stripePaymentID'], 'invoice' );
					wp_die();
				}
				break;

			case 'syncPayment':
				if( isset( $_GET['stripePaymentID'] ) ){
					echo $this->syncMailchimpWithStripe( $_GET['stripePaymentID'] );
					wp_die();
				}
				break;

			case 'list':
				$list_id = '12d166e948';
				$response = $mailchimpAPI->processRequest( 'lists/' . $list_id . '/members//' );
				break;

			case 'getMember':
				if( isset( $_GET['id'] ) && $_GET['id'] ){
					$id_or_email = $_GET['id'];
					$response = $mailchimpAPI->getUniqueMember( $id_or_email );
				}
				break;

			case 'query':
				$store = $mailchimpAPI->getStoreInfo();
				if( isset( $store->list_id ) ){
					$list_id = $store->list_id;
					$response = $mailchimpAPI->processRequest( "search-members/?list_id=$list_id&query=sam@sputznik.com" );
				}
				break;

			case 'getCustomer':
				if( isset( $_GET['email_address'] ) ){
					$response = $mailchimpAPI->getCustomer( $_GET['email_address'] );
				}
				break;

			case 'uniqueUser':
				$unique_id = 'd7609f5aec'; //
				$response = $mailchimpAPI->getUniqueCustomer( $unique_id );

				if( $response == null ){
					echo "Empty response";
				}

				//$response = $mailchimpAPI->getStoreInfo();
				//echo $response->list_id;
				break;

			case 'deleteOrder':
				//echo "delete order";
				if( isset( $_GET['order_id'] ) ){
					$order_id = $_GET['order_id'];
					$response = $mailchimpAPI->deleteOrder( $order_id );
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

	
	function getCustomer( $data ){

		$customer = array(
			'opt_in_status'	=> false
		);

		$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		// CHECK IF MAILCHIMP UNIQUE ID EXISTS
		if( isset( $data['mailchimp_user_id'] ) ){

			$mc_customer = $mailchimpAPI->getUniqueMember( $data['mailchimp_user_id'] );
			if( $mc_customer!=null && isset( $mc_customer->email_address ) && !empty( $mc_customer->email_address ) ){

				if( isset( $mc_customer->merge_fields ) ){
					if( isset( $mc_customer->merge_fields->FNAME ) ){
						$customer[ 'first_name' ] = $mc_customer->merge_fields->FNAME;
					}
					if( isset( $mc_customer->merge_fields->LNAME ) ){
						$customer[ 'last_name' ] = $mc_customer->merge_fields->LNAME;
					}
				}

				// IF MEMBER EXISTS THEN SET THE OPT_IN_STATUS TO TRUE
				$customer[ 'email_address' ] = $mc_customer->email_address;
				$customer[ 'opt_in_status' ] = true;
			}

		}

		// IF THE EMAIL ADDRESS DOES NOT EXIST, THEN CHECK IN STRIPE
		if( !isset( $customer[ 'email_address' ] ) ){
			// GET EMAIL ADDRESS FROM STRIPE BECAUSE THE MAILCHIMP UNIQUE ID DOES NOT EXIST
			$customer[ 'email_address' ] = $stripe->getEmailFromCustomerID( $data['stripeCustomerID'] );

			// CHECK IF THERE EXISTS A MEMBER FOR THE SAME EMAIL ADDRESS IN THE STORE LIST
			$mc_customer = $mailchimpAPI->getUniqueMember( $customer[ 'email_address' ] );
			if( $mc_customer!=null && isset( $mc_customer->email_address ) && !empty( $mc_customer->email_address ) ){

				if( isset( $mc_customer->merge_fields ) ){
					if( isset( $mc_customer->merge_fields->FNAME ) ){
						$customer[ 'first_name' ] = $mc_customer->merge_fields->FNAME;
					}
					if( isset( $mc_customer->merge_fields->LNAME ) ){
						$customer[ 'last_name' ] = $mc_customer->merge_fields->LNAME;
					}
				}

				// IF MEMBER EXISTS THEN SET THE OPT_IN_STATUS TO TRUE
				$customer[ 'opt_in_status' ] = true;
			}
		}



		// CHECK AGAIN IF THE EMAIL ADDRESS EXISTS, IF YES THEN ADD ID
		if( isset( $customer[ 'email_address' ] ) && !empty( $customer[ 'email_address' ] ) ){

			// ADD SUBSCRIBER HASH AS ID FOR THE CUSTOMER ONLY IF EMAIL ADDRESS EXISTS
			$customer[ 'id' ]  = $mailchimpAPI->getSubscriberHash( $customer[ 'email_address' ] );

			return $customer;
		}

		// DEFAULT OPTION SHOULD BE TO RETURN NULL INCASE AN ERROR HAPPENS
		return null;
	}

	function checkIfRightStore( $mc_store_id ){
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
		if( $mc_store_id ==  $mailchimpAPI->getStoreID() ){
			return true;
		}
		return false;
	}

	function test( $data ){
		echo "<pre>";
		print_r( $data );
		echo "</pre>";
	}

	function syncMailchimpWithStripe( $payment_id_or_invoice_id, $type = 'payment' ){
		$stripeAPI = STRIPE_WEBHOOKS_STRIPE_API::getInstance();
		$product_id = '';
		$data = array();

		switch( $type ){
			case 'invoice':
				$invoice = $stripeAPI->getInvoice( $payment_id_or_invoice_id );
				if( $invoice == null ) return "Invoice has returned null";
				$data = $stripeAPI->filterInvoiceData( $invoice );
				$product_id = 'recurring-donation';
				break;

			default:
				$paymentIntent = $stripeAPI->getPaymentIntent( $payment_id_or_invoice_id );
				if( $paymentIntent == null ) return "Payment Intent has returned null";
				$data = $stripeAPI->filterPaymentIntentData( $paymentIntent );
				$product_id = 'donation';
				break;
		}
		return $this->syncMailchimp( $data, $product_id );
	}



	/*
	* [stripePaymentID]
	* [stripeCustomerID]
	* [amount]
	* [currency]
	* [created]
	* [campaign_id]
	* [mailchimp_store_id]
	* [mailchimp_user_id]
	*/
	function syncMailchimp( $data, $product_id ){

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		//$this->test( $data );

		if( isset( $data['stripeCustomerID'] ) && $data['stripeCustomerID'] != null &&
				isset( $data['mailchimp_store_id'] ) && $this->checkIfRightStore( $data[ 'mailchimp_store_id' ] ) ){

			// CHECK IF ORDER ALREADY EXISTS IN MAILCHIMP
			$mailchimpOrder = $mailchimpAPI->getOrderInfo( $data['stripePaymentID'] );

			if( isset( $mailchimpOrder->id ) ) return 'Mailchimp Order with the same ID: ' . $data['stripePaymentID'] . ' already exists.';

			$customer = $this->getCustomer( $data );

			if( $customer != null ){

				$order = array(
					'id'										=> $data['stripePaymentID'],
					'order_total'						=> $data['amount'],
					'currency_code'					=> $data['currency'],
					'processed_at_foreign'	=> date( 'c', $data['created'] ),
					'customer'							=> $customer
				);

				if( isset( $data['campaign_id'] ) ) $order['campaign_id'] = $data['campaign_id'];

				//$this->test( $order );

				$response = $mailchimpAPI->createOrder( $product_id, $order );
				if( isset( $response->id ) ) return "Order has been succesfully created with ID: " . $response->id;
				else return "Order creation threw errors";

			}
		}
		else{
			return "Order could not be created because of invalid customer or store";
		}

	}



	/*
	function syncMailchimp( $id_or_data ){

		$stripeAPI = STRIPE_WEBHOOKS_STRIPE_API::getInstance();
		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$paymentIntent = null;
		if( !is_object( $id_or_data ) ){
			$paymentIntent = $stripeAPI->getPaymentIntent( $id_or_data );
		}
		else{
			$paymentIntent = $id_or_data;
		}

		//$this->test( $paymentIntent );

		if( $paymentIntent!= null ){

			//echo "Payment intent not null";

			$data = $stripeAPI->filterPaymentIntentData( $paymentIntent );

			//$this->test( $data );

			if( isset( $data['stripeCustomerID'] ) && $data['stripeCustomerID'] != null ){

				/*
				*	IF STORE ID IS NOT SET IN METADATA, THAT MEANS THE PAYMENT INTENT IS OLD
				* IN THAT CASE DONT CHECK FOR THE STORE ID
				*
				if( isset( $data['mailchimp_store_id'] ) && $this->checkIfRightStore( $data[ 'mailchimp_store_id' ] ) ){

					//echo "Payment data array is valid";

					// CHECK IF ORDER ALREADY EXISTS IN MAILCHIMP
					$mailchimpOrder = $mailchimpAPI->getOrderInfo( $data['stripePaymentID'] );

					if( isset( $mailchimpOrder->id ) ){
						return 'Mailchimp Order with the same ID: ' . $data['stripePaymentID'] . ' already exists.';
					}

					//$email_address = $this->getEmailAddressFromMailchimpOrStripe( $data );
					$customer = $this->getCustomer( $data );

					//$this->test( $customer );

					if( $customer != null ){

						$order = array(
							'id'										=> $data['stripePaymentID'],
							'order_total'						=> $data['amount'],
							'currency_code'					=> $data['currency'],
							'processed_at_foreign'	=> date( 'c', $data['created'] ),
							'customer'							=> $customer
						);

						if( isset( $data['campaign_id'] ) ){
							$order['campaign_id'] = $data['campaign_id'];
						}

						//$this->test( $order );

						$response = $mailchimpAPI->createOrder( 'donation', $order );

						if( isset( $response->id ) ){
							return "Order has been succesfully created with ID: " . $response->id;
						}
						else{
							return "Order creation threw errors";
						}

						//echo "<pre>";
						//print_r( $response );
						//echo "</pre>";

					}
					else{
						return "Order could not be created because customer was NULL";
					}
				}
				else{
					return "Order could not be created because the Store was not right";
				}


			}
			else{
				return "Payment Intent does not have customer information";
			}



		}
		else{
			return "Payment Intent has returned null";
		}

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";

		return "Order could not be created for some reason.";
	}
	*/

	function process(){
		//require_once('stripe-php/init.php');

		$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();

		$event = $stripe->getEventFromPayload();

		/* Handle the event */
		switch ( $event['type'] ) {
			case 'payment_intent.succeeded':

				if( isset( $event['data'] ) && isset( $event['data']['object'] ) ){
					$paymentIntent = $event['data']['object']; 	// contains a \Stripe\PaymentIntent

					if( isset( $paymentIntent['id'] ) ){
						//echo $paymentIntent['id'];
						echo $this->syncMailchimp( $paymentIntent['id'] );
					}
					else{
						echo "Payment ID or Customer is NULL";
					}
				}
				break;
			default:
				echo 'Received unknown event type ' . $event['type'];
		}

		http_response_code(200);
		wp_die();
	}

}

STRIPE_WEBHOOKS::getInstance();
