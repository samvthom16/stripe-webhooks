<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['list_id'] ) ){

		$list_id = $_GET['list_id'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/growth-history" );

		$columns = array(
			array(
				'label'	=> 'List ID',
				'key'		=> 'list_id'
			),
			array(
				'label'	=> 'Month',
				'key'		=> 'month'
			),
			array(
				'label'	=> 'Existing',
				'key'		=> 'existing'
			),
			array(
				'label'	=> 'Imports',
				'key'		=> 'imports'
			),
			array(
				'label'	=> 'Optins',
				'key'		=> 'optins'
			),
			array(
				'label'	=> 'Subscribed',
				'key'		=> 'subscribed'
			),
			array(
				'label'	=> 'Unsubscribed',
				'key'		=> 'unsubscribed'
			),
			array(
				'label'	=> 'Reconfirm',
				'key'		=> 'reconfirm'
			),
			array(
				'label'	=> 'Cleaned',
				'key'		=> 'cleaned'
			),
			array(
				'label'	=> 'Pending',
				'key'		=> 'pending'
			),
			array(
				'label'	=> 'Deleted',
				'key'		=> 'deleted'
			),
			array(
				'label'	=> 'Transactional',
				'key'		=> 'transactional'
			),
		);

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->history, 'list-growth' );

		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}
