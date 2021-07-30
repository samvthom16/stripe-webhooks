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

		add_filter( 'stripe_webhooks_find_child', function( $value, $slug ){
			if( $slug == 'email-address' ){
				$value = $_GET['email_address'];
			}
			return $value;
		}, 10, 2 );

		$columns = array(
			array(
				'label'	=> 'Email Address',
				'key'		=> ''
			),
			array(
				'label'	=> 'Activity Type',
				'key'		=> 'activity_type'
			),
			array(
				'label'	=> 'Created At',
				'key'		=> 'created_at_timestamp'
			),
			array(
				'label'	=> 'Campaign ID',
				'key'		=> 'campaign_id'
			),
			array(
				'label'	=> 'Campaign Title',
				'key'		=> 'campaign_title'
			),
			array(
				'label'	=> 'Link Clicked',
				'key'		=> 'link_clicked'
			),
		);

		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/members/$subscriber_hash/activity-feed" );

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->activity, 'member-feed' );


		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
