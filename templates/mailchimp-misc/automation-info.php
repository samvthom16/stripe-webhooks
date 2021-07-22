<?php

	$settingsOptions = array(
		'workflow_id' => 'Workflow ID'
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['workflow_id'] ) ){

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$workflow_id = $_GET['workflow_id'];

		$response = $mailchimpAPI->cachedProcessRequest( "/automations/$workflow_id" );

		echo "<pre>";
		print_r( $response );
		echo "</pre>";


	}
