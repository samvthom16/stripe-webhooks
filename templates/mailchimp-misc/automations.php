<?php

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$store_id = $mailchimpAPI->getStoreID();

	$response = $mailchimpAPI->cachedProcessRequest( "/automations" );

	echo "<pre>";
	print_r( $response );
	echo "</pre>";
