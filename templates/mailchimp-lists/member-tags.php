<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
		'email_address'	=> 'Email Address'
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['list_id'] ) && isset( $_GET['email_address'] ) ){

		$list_id = $_GET['list_id'];
		$email_address = $_GET['email_address'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$subscriber_hash = $mailchimpAPI->getSubscriberHash( $email_address );

		$columns = array(
			array(
				'label'	=> 'ID',
				'key'		=> 'id'
			),
			array(
				'label'	=> 'Name',
				'key'		=> 'name'
			),
			array(
				'label'	=> 'Date Added',
				'key'		=> 'date_added'
			),
		);

		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/members/$subscriber_hash/tags" );

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->tags, 'member-tags' );

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
