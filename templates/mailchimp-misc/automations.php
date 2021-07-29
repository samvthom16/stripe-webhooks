<?php

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$store_id = $mailchimpAPI->getStoreID();

	$per_page = 100;
	$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$offset = ( $activepage - 1 ) * $per_page;
	$response = $mailchimpAPI->cachedProcessRequest( "/automations?count=$per_page&offset=$offset" );

	$columns = array(
		array(
			'label'	=> 'ID',
			'key'		=> 'id'
		),
		array(
			'label'	=> 'Create Time',
			'key'		=> 'create_time'
		),
		array(
			'label'	=> 'Start Time',
			'key'		=> 'start_time'
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
			'label'	=> 'Recipients List',
			'key'		=> 'recipients->list_name'
		),
		array(
			'label'	=> 'Opens',
			'key'		=> 'tracking->opens'
		),
		array(
			'label'	=> 'HTML Clicks',
			'key'		=> 'tracking->html_clicks'
		),
		array(
			'label'	=> 'text Clicks',
			'key'		=> 'tracking->text_clicks'
		),
		array(
			'label'	=> 'Goal Tracking',
			'key'		=> 'tracking->goal_tracking'
		),
		array(
			'label'	=> 'Ecommerce',
			'key'		=> 'tracking->ecomm360'
		),
		array(
			'label'	=> 'Google Analytics',
			'key'		=> 'tracking->google_analytics'
		),
		array(
			'label'	=> 'Clicktale',
			'key'		=> 'tracking->clicktale'
		),
	);

	$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
	$table_ui->display( $columns, $response->automations, 'automations' );
	$table_ui->pagination( $per_page, $response->total_items, array( 'action' ) );

	//echo "<pre>";
	//print_r( $response );
	//echo "</pre>";
