<?php

	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['list_id'] ) ){

		$list_id = $_GET['list_id'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/locations" );

		$columns = array(
			array(
				'label'	=> 'Country',
				'key'		=> 'country'
			),
			array(
				'label'	=> 'CC',
				'key'		=> 'cc'
			),
			array(
				'label'	=> 'Percent',
				'key'		=> 'percent'
			),
			array(
				'label'	=> 'Total',
				'key'		=> 'total'
			),
		);

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->locations, 'list-locations' );

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";


		}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
