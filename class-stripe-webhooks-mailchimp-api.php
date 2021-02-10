<?php

	class STRIPE_WEBHOOKS_MAILCHIMP_API extends STRIPE_WEBHOOKS_BASE{

		var $settings;

		function __construct(){
			$admin = STRIPE_WEBHOOKS_ADMIN::getInstance();
			$this->setSettings( $admin->getSettings() );
		}

		function setSettings( $settings ){ $this->settings = $settings; }
		function getSettings(){ return $this->settings; }

		function getEachSetting( $key ){
			$settings = $this->getSettings();
			if( isset( $settings[ $key ] ) ) return $settings[ $key ];
			return '';
		}

		function getAPIKey(){ return $this->getEachSetting( 'mailchimpAPIKey' ); }
		function getServer(){ return $this->getEachSetting( 'mailchimpServer' ); }
		function getStoreID(){ return $this->getEachSetting( 'mailchimpStoreID' ); }

		function getBaseURL(){ return 'https://'.$this->getServer().'.api.mailchimp.com/3.0/';}

		function getUserDefinedProducts(){
			return array(
				'ranges' => array(
					'Small Donation' 				=> 10,
					'Medium Donation' 			=> 50,
				),
				'upper'	=> 'High Donation'
			);
		}

		function slugify( $text ){
			$text = preg_replace('~[^\pL\d]+~u', '-', $text);
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
			$text = preg_replace('~[^-\w]+~', '', $text);
			$text = trim($text, '-');
			$text = preg_replace('~-+~', '-', $text);
			$text = strtolower($text);
			if ( empty( $text ) ) {
		    return 'n-a';
		  }
			return $text;
		}

		/*
		* SYNCH USER DEFINED PRODUCTS TO MAILCHIMP
		*/
		function syncProducts(){

			$store_id = $this->getStoreID();

			$responses = array();
			$products = $this->getUserDefinedProducts();
			if( isset( $products['ranges'] ) && is_array( $products['ranges'] ) ){
				foreach( $products['ranges'] as $title => $limit ){
					$response = $this->createProduct( array( 'title' => $title ) );
					array_push( $responses, $response );
				}
			}
			if( isset( $products['upper'] ) ){
				$response = $this->createProduct( array( 'title' => $products['upper'] ) );
				array_push( $responses, $response );
			}
			return $responses;
		}

		function getProductTitleByAmount( $amount ){
			$products = $this->getUserDefinedProducts();
			if( isset( $products['ranges'] ) && is_array( $products['ranges'] ) ){
				foreach( $products['ranges'] as $title => $limit ){
					if( $amount <= $limit ){
						return $title;
					}
				}
			}
			return $products['upper'];
		}

		function createStore( $store ){
			if( !isset( $store['id'] ) ) $store['id'] = $this->slugify( $store['name'] );
			return $this->processRequest( '/ecommerce/stores', $store );
		}

		/*
		* TAKES EMAIL ADDRESS AS ARGUMENT AND RETURNS MD5 HASH
		*/
		function getSubscriberHash( $email_address ){ return md5( strtolower( $email_address ) ); }



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
				'opt_in_status' => true
			);
			return $this->processRequest( '/ecommerce/stores/' . $store_id . '/customers', $customer );
		}

		function getCustomer( $email_address ){
			$store_id = $this->getStoreID();
			$customer_id = $this->getSubscriberHash( $email_address );
			return $this->processRequest( '/ecommerce/stores/' . $store_id . '/customers//' . $customer_id );
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

			$unique_id = time();

			//if( !isset( $order['id'] ) ){ $order['id'] = 'order' . $unique_id; }
			//if( !isset( $order['currency_code'] ) ){ $order['currency_code'] = 'GBP'; }

			$order['lines'] = array(
				array(
					'id'									=> 'line' . $unique_id,
					'product_id' 					=> $product_id,
					'product_variant_id'	=> $product_id,
					'quantity'						=> 1,
					'price'								=> $order['order_total']
				)
			);

			return $this->processRequest( 'ecommerce/stores/' . $store_id . '/orders', $order );
		}

		function createOrderForEmailAddress( $email_address, $order ){
			$order['customer'] = $this->createCustomerIfDoesNotExist( $email_address );
			$product_title = $this->getProductTitleByAmount( $amount );
			$product_id = $this->slugify( $product_title );
			return $this->createOrder( $product_id, $order );
		}

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

		function processRequest( $partUrl, $postParams = array(), $deleteFlag = false ){

			$url = $this->getBaseURL() . $partUrl;
			$auth = base64_encode( 'user:' . $this->getAPIKey() );

			//echo $url;

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: Basic '.$auth ) );
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

		function cachedProcessRequest( $partUrl, $postParams = array() ){
			$cache_key = 'stripe-mc' . md5( $partUrl );
			$data = array();

			// Get any existing copy of our transient data
			if ( false === ( $data = get_transient( $cache_key ) ) ) {
			    $data = $this->processRequest( $partUrl, $postParams );
			    set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
			}
			return $data;
		}

	}
