<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
	);

	$this->displayForm( $settingsOptions );

	$stripe = STRIPE_WEBHOOKS_STRIPE_API::getInstance();


	if( isset( $_GET['list_id'] ) ){

		$list_id = $_GET['list_id'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$per_page = 50;
		$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$offset = ( $activepage - 1 ) * $per_page;
		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/activity?count=$per_page&offset=$offset" );

		$columns = array(
			array(
				'label'	=> 'Day',
				'key'		=> 'day'
			),
			array(
				'label'	=> 'Emails Sent',
				'key'		=> 'emails_sent'
			),
			array(
				'label'	=> 'Unique Opens',
				'key'		=> 'unique_opens'
			),
			array(
				'label'	=> 'Recipient Clicks',
				'key'		=> 'recipient_clicks'
			),
			array(
				'label'	=> 'Hard Bounce',
				'key'		=> 'hard_bounce'
			),
			array(
				'label'	=> 'Soft Bounce',
				'key'		=> 'soft_bounce'
			),
			array(
				'label'	=> 'Subscriptions',
				'key'		=> 'subs'
			),
			array(
				'label'	=> 'Unsubscriptions',
				'key'		=> 'unsubs'
			),
			array(
				'label'	=> 'Other Adds',
				'key'		=> 'other_adds'
			),
			array(
				'label'	=> 'Other Removes',
				'key'		=> 'other_removes'
			),
		);

		add_action( 'stripe_webhooks_admin_column', function( $col_label, $row, $col_slug ){

			switch( $col_slug ){
				case 'day':
					$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
					if( $table_ui->checkIsAValidDate( $col_label ) ){
						$col_label = date( 'd M Y', strtotime( $col_label ) );
					}
					break;
			}
			return $col_label;
		}, 10, 3 );

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();

		$table_ui->display( $columns, $response->activity, 'list-activity' );

		$table_ui->pagination( $per_page, $response->total_items, array( 'list_id', 'action' ) );

	}
