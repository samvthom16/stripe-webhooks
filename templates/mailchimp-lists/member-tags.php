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
				'label'	=> 'ID',
				'key'		=> 'id'
			),
			array(
				'label'	=> 'Email Address',
				'key'		=> ''
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

		$per_page = 20;
		$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$offset = ( $activepage - 1 ) * $per_page;
		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/members/$subscriber_hash/tags?count=$per_page&offset=$offset" );

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->tags, 'member-tags' );
		$table_ui->pagination( $per_page, $response->total_items, array( 'list_id', 'action', 'email_address' ) );

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
