<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
		'email_address'	=> 'Email Address'
	);

	displayForm( $settingsOptions );

	$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();

	if( isset( $_GET['list_id'] ) && isset( $_GET['email_address'] ) ){

		$list_id = $_GET['list_id'];
		$email_address = $_GET['email_address'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$subscriber_hash = $mailchimpAPI->getSubscriberHash( $email_address );

		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/members/$subscriber_hash/activity-feed" );

		echo "<pre>";
		print_r( $response );
		echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
