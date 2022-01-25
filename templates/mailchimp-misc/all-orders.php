<?php

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$store_id = $mailchimpAPI->getStoreID();

	$columns = array(
		array(
			'label'	=> 'ID',
			'key'		=> 'id'
		),
		array(
			'label'	=> 'Customer ID',
			'key'		=> 'customer->id'
		),
		array(
			'label'	=> 'First Name',
			'key'		=> 'customer->first_name'
		),
		array(
			'label'	=> 'Last Name',
			'key'		=> 'customer->last_name'
		),
		array(
			'label'	=> 'Order Total',
			'key'		=> 'order_total'
		),
		array(
			'label'	=> 'Currency Code',
			'key'		=> 'currency_code'
		),
		array(
			'label'	=> 'Store ID',
			'key'		=> 'store_id'
		),
		array(
			'label'	=> 'Processed At Foreign',
			'key'		=> 'processed_at_foreign'
		),
	);

	$per_page = 50;
	$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$offset = ( $activepage - 1 ) * $per_page;
	$response = $mailchimpAPI->processRequest( "ecommerce/orders?offset=$offset" );

	$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
	$table_ui->display( $columns, $response->orders, 'orders' );

	$table_ui->pagination( $per_page, $response->total_items, array( 'action' ) );

	/*
	echo "<pre>";
	print_r( $response );
	echo "</pre>";
	*/
