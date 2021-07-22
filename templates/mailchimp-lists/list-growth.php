<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['list_id'] ) ){

		$list_id = $_GET['list_id'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/growth-history" );

		echo "<pre>";
		print_r( $response );
		echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}
