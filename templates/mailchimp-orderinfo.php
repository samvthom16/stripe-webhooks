<?php

	$settingsOptions = array(
		'order_id' => 'Order ID'
	);

	displayForm( $settingsOptions );

	if( isset( $_GET['order_id'] ) ){

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$store_id = $mailchimpAPI->getStoreID();
		$order_id = $_GET['order_id'];

		$response = $mailchimpAPI->cachedProcessRequest( "/ecommerce/stores/$store_id/orders/$order_id" );

		echo "<pre>";
		print_r( $response );
		echo "</pre>";


	}
