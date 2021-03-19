<?php

	class STRIPE_WEBHOOKS_MAILCHIMP_API extends STRIPE_WEBHOOKS_API{

		/* API RELATED DATA */
		function getAPIKey(){ return $this->getEachSetting( 'mailchimpAPIKey' ); }
		function getBaseURL(){ return 'https://' . $this->getEachSetting( 'mailchimpServer' ) . '.api.mailchimp.com/3.0/'; }
		function getHTTPHeader(){
			$auth = base64_encode( 'user:' . $this->getAPIKey() );
			return array( 'Content-Type: application/json', 'Authorization: Basic '.$auth );
		}
		/* API RELATED DATA */

		function getStoreID(){ return $this->getEachSetting( 'mailchimpStoreID' ); }
		function getProductTitle(){ return "Donation"; }
		function getUserDefinedProducts(){
			return array('Donation', 'Recurring Donation');
		}

		/*
		* SYNCH USER DEFINED PRODUCTS TO MAILCHIMP
		*/
		function syncProducts(){
			$responses = array();
			$products = $this->getUserDefinedProducts();
			foreach( $products as $product ){
				$response = $this->createProduct( array( 'title' => $product ) );
				array_push( $responses, $response );
			}
			return $responses;
		}

		function getStoreInfo(){
			$store_id = $this->getStoreID();
			return $this->processRequest( '/ecommerce/stores/' . $store_id );
		}

		function createStore( $store ){
			if( !isset( $store['id'] ) ) $store['id'] = $this->slugify( $store['name'] );
			return $this->processRequest( '/ecommerce/stores', $store );
		}


		// CREATE CUSTOMER IF DOES NOT EXIST
		function createCustomerIfDoesNotExist( $email_address ){
			$store_id = $this->getStoreID();
			$customer = $this->getCustomer( $email_address );
			if( !isset( $customer->id ) ){
				$customer = $this->createCustomer( $email_address );
			}
			return $customer;
		}

		function createCustomer( $email_address ){
			$store_id = $this->getStoreID();
			$customer = array(
				'id' => $this->getSubscriberHash( $email_address ),
				'email_address' => $email_address,
				'opt_in_status' => false
			);
			return $this->processRequest( '/ecommerce/stores/' . $store_id . '/customers', $customer );
		}

		function _isEmail( $email ) {
		   $find1 = strpos( $email, '@' );
		   $find2 = strpos( $email, '.' );
		   return ( $find1 !== false && $find2 !== false && $find2 > $find1 );
		}

		// GET UNIQUE MEMBER BY ID OR EMAIL ADDRESS FROM THE STORE LIST
		function getUniqueMember( $id_or_email ){

			// GET E-COMMERCE STORE INFORMATION
			$store = $this->getStoreInfo();

			if( isset( $store->list_id ) ){

				// GET CONNECTED LIST OF THE E-COMMERCE STORE
				$list_id = $store->list_id;

				// CHECK IF THIS IS ID OR EMAIL
				if( $this->_isEmail( $id_or_email ) ){

					// GET USER BY EMAIL ADDRESS
					$subscriber_hash = $this->getSubscriberHash( $id_or_email );
					$response = $this->processRequest( "/lists/$list_id/members/$subscriber_hash" );
					return $response;

				}
				else{

					// GET USER BY UNIQUE ID
					$response = $this->processRequest( '/lists//' . $list_id . '/members/?unique_email_id=' . $id_or_email );
					if( isset( $response->members ) && is_array( $response->members ) && count( $response->members ) ){
						return $response->members[0];
					}
				}
			}

			return null;
		}

		function getCustomer( $email_address ){
			$store_id = $this->getStoreID();
			$customer_id = $this->getSubscriberHash( $email_address );
			return $this->processRequest( "/ecommerce/stores/$store_id/customers/$customer_id" );
		}

		function getOrderInfo( $order_id ){
			$store_id = $this->getStoreID();
			return $this->processRequest( '/ecommerce/stores/' . $store_id . '/orders//' . $order_id );
		}

		/*
		* MVP OF ORDER:
		* [order_total] => 50
		*/
		function createOrder( $product_id, $order ){
			$store_id = $this->getStoreID();

			// PRODUCT LINES OF ORDER
			$order['lines'] = array( array(
				'id'									=> 'line' . time(),
				'product_id' 					=> $product_id,
				'product_variant_id'	=> $product_id,
				'quantity'						=> 1,
				'price'								=> $order['order_total']
			) );

			//echo "<pre>";
			//print_r( $order );
			//echo "</pre>";

			return $this->processRequest( "ecommerce/stores/$store_id/orders", $order );
		}

		/*
		function createOrderForEmailAddress( $email_address, $order ){
			$order['customer'] = $this->createCustomerIfDoesNotExist( $email_address );
			$product_title = $this->getProductTitle();
			$product_id = $this->slugify( $product_title );
			return $this->createOrder( $product_id, $order );
		}
		*/

		/*
		* MVP OF PRODUCT: [title] => Sample Product
		*/
		function createProduct($product ){

			$store_id = $this->getStoreID();

			// ADD SLUGIFY ID IF ID NOT PASSED
			if( !isset( $product['id'] ) ) {
				$product['id'] = $this->slugify( $product['title'] );
			}

			// ADD PRODUCT VARIANTS IF DOES NOT EXIST
			if( !isset( $product['variants'] ) ){
				$product['variants'] = array(
					array(
						'id' => $product['id'],
						'title' => $product['title']
					)
				);
			}
			return $this->processRequest( 'ecommerce/stores/' . $store_id . '/products', $product );
		}


	}
