<?php

	$settingsOptions = array(

	);

	//displayForm( $settingsOptions );

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$store_id = $mailchimpAPI->getStoreID();

	$response = $mailchimpAPI->cachedProcessRequest( "/campaigns" );

	echo "<pre>";
	print_r( $response );
	echo "</pre>";
