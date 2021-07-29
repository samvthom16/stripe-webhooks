<?php

	add_action( 'stripe_webhooks_admin_column', function( $col_label, $row, $col_slug ){

		switch( $col_slug ){

			case 'name':
				$list_id = $_GET['list_id'];
				$segment_id = $row['id'];
				$baseurl = admin_url( 'admin.php' ) . "?page=" . $_GET['page'] . "&list_id=" . $list_id . "&segment_id=" . $segment_id;

				$buttons = array(
					'segment-members' => 'Members'
				);

				ob_start();
				echo $col_label;
				echo "<ul class='action-buttons'>";
				foreach ( $buttons as $slug => $value ) {
					$url = $baseurl .= '&action=' . $slug;
					echo "<li><a target='_blank' href='$url'>$value</a></li>";
				}
				echo "</ul>";
				$col_label = ob_get_clean();
				break;

		}
		return $col_label;
	}, 10, 3 );


	$settingsOptions = array(
		'list_id'				=> 'Mailchimp List ID',
	);

	$this->displayForm( $settingsOptions );

	if( isset( $_GET['list_id'] ) ){

		$list_id = $_GET['list_id'];

		$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

		$per_page = 50;
		$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$offset = ( $activepage - 1 ) * $per_page;
		$response = $mailchimpAPI->cachedProcessRequest( "/lists/$list_id/segments?count=$per_page" );

		$columns = array(
			array(
				'label'	=> 'ID',
				'key'		=> 'id'
			),
			array(
				'label'	=> 'Name',
				'key'		=> 'name'
			),
			array(
				'label'	=> 'Member Count',
				'key'		=> 'member_count'
			),
			array(
				'label'	=> 'Type',
				'key'		=> 'type'
			),
			array(
				'label'	=> 'Created At',
				'key'		=> 'created_at'
			),
			array(
				'label'	=> 'Updated At',
				'key'		=> 'updated_at'
			),
			array(
				'label'	=> 'List ID',
				'key'		=> 'list_id'
			),
		);

		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		$table_ui->display( $columns, $response->segments, 'list-segments' );

		$table_ui->pagination( $per_page, $response->total_items, array( 'list_id', 'action' ) );

		//echo "<pre>";
		//print_r( $response );
		//echo "</pre>";

	}
	else{
			//$this->displayErrorNotice( "This list does not exist in Mailchimp." );
	}





?>
