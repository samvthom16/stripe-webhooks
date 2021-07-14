<?php

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$response = $mailchimpAPI->cachedProcessRequest( '/lists' );

	echo "<pre>";
	print_r( $response );
	echo "</pre>";
