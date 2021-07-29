<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
		'segment_id'		=> 'Segment ID'
	);

	$this->displayForm( $settingsOptions );

	$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();

	if( isset( $_GET['list_id'] ) ){

		$list_id = $_GET['list_id'];
		$segment_id = $_GET['segment_id'];
		
		$columns = array(
			array(
				'label'	=> 'ID',
				'key'		=> 'id'
			),
			array(
				'label'	=> 'Email Address',
				'key'		=> 'email_address'
			),
			array(
				'label'	=> 'Unique Email ID',
				'key'		=> 'unique_email_id'
			),
			array(
				'label'	=> 'Email type',
				'key'		=> 'email_type'
			),
			array(
				'label'	=> 'Status',
				'key'		=> 'status'
			),
			array(
				'label'	=> 'Emails Sent',
				'key'		=> 'emails_sent'
			),
			array(
				'label'	=> 'First Name',
				'key'		=> 'merge_fields->FNAME'
			),
			array(
				'label'	=> 'Last Name',
				'key'		=> 'merge_fields->LNAME'
			),
			array(
				'label'	=> 'Country',
				'key'		=> 'merge_fields->COUNTRY'
			),
			array(
				'label'	=> 'Source',
				'key'		=> 'merge_fields->SOURCE'
			),
			array(
				'label'	=> 'Opt In Date',
				'key'		=> 'merge_fields->OPTINDATE'
			),
			array(
				'label'	=> 'Keep Me Info',
				'key'		=> 'merge_fields->KEEPMEINFO'
			),
			array(
				'label'	=> 'avg_open_rate',
				'key'		=> 'stats->avg_open_rate'
			),
			array(
				'label'	=> 'Avg Click Rate',
				'key'		=> 'stats->avg_click_rate'
			),
			array(
				'label'	=> 'Member Rating',
				'key'		=> 'member_rating'
			),
		);

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$per_page = 100;
		$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$offset = ( $activepage - 1 ) * $per_page;
		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/segments/$segment_id/members?count=$per_page&offset=$offset" );

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->members, 'segment-members' );
		$table_ui->pagination( $per_page, $response->total_items, array( 'action', 'list_id', 'segment_id' ) );

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";


	}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
