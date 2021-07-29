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
				'label'	=> 'Action',
				'key'		=> 'action'
			),
			array(
				'label'	=> 'Timestamp',
				'key'		=> 'timestamp'
			),
			array(
				'label'	=> 'URL',
				'key'		=> 'url'
			),
			array(
				'label'	=> 'Campaign ID',
				'key'		=> 'campaign_id'
			),
			array(
				'label'	=> 'Title',
				'key'		=> 'title'
			),
			array(
				'label'	=> 'Type',
				'key'		=> 'type'
			),
		);

		$per_page = 50;
		$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$offset = ( $activepage - 1 ) * $per_page;
		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/members/$subscriber_hash/activity?count=$per_page&offset=$offset" );

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->activity, 'member-activity' );
		$table_ui->pagination( $per_page, $response->total_items, array( 'list_id', 'action', 'email_address' ) );

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
