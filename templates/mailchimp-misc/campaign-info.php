<?php

	$settingsOptions = array(
		'campaign_id' => 'Campaign ID'
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['campaign_id'] ) ){

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$campaign_id = $_GET['campaign_id'];

		$response = $mailchimpAPI->cachedProcessRequest( "/campaigns/$campaign_id" );

		echo "<pre>";
		print_r( $response );
		echo "</pre>";


	}
