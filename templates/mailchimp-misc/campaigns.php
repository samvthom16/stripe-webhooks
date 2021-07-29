<?php

	$settingsOptions = array(

	);

	//displayForm( $settingsOptions );

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$store_id = $mailchimpAPI->getStoreID();

	$per_page = 100;
	$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$offset = ( $activepage - 1 ) * $per_page;
	$response = $mailchimpAPI->cachedProcessRequest( "/campaigns?count=$per_page&offset=$offset" );

	$columns = array(
		array(
			'label'	=> 'ID',
			'key'		=> 'id'
		),
		array(
			'label'	=> 'Type',
			'key'		=> 'type'
		),
		array(
			'label'	=> 'Create Time',
			'key'		=> 'create_time'
		),
		array(
			'label'	=> 'Archive Url',
			'key'		=> 'archive_url'
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
			'label'	=> 'Send Time',
			'key'		=> 'send_time'
		),
		array(
			'label'	=> 'Content Type',
			'key'		=> 'content_type'
		),
		array(
			'label'	=> 'Recipients List',
			'key'		=> 'recipients->list_name'
		),
		array(
			'label'	=> 'Opens',
			'key'		=> 'report_summary->opens'
		),
		array(
			'label'	=> 'Unique Opens',
			'key'		=> 'report_summary->unique_opens'
		),
		array(
			'label'	=> 'Open Rate',
			'key'		=> 'report_summary->open_rate'
		),
		array(
			'label'	=> 'Clicks',
			'key'		=> 'report_summary->clicks'
		),
		array(
			'label'	=> 'Subscriber Clicks',
			'key'		=> 'report_summary->subscriber_clicks'
		),
		array(
			'label'	=> 'Click Rate',
			'key'		=> 'report_summary->click_rate'
		),
		array(
			'label'	=> 'Total Orders',
			'key'		=> 'report_summary->ecommerce->total_orders'
		),
		array(
			'label'	=> 'Total Spent',
			'key'		=> 'report_summary->ecommerce->total_spent'
		),
		array(
			'label'	=> 'Total Revenue',
			'key'		=> 'report_summary->ecommerce->total_revenue'
		),
	);

	$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
	$table_ui->display( $columns, $response->campaigns, 'campaigns' );
	$table_ui->pagination( $per_page, $response->total_items, array( 'action' ) );

	//echo "<pre>";
	//print_r( $response );
	//echo "</pre>";
