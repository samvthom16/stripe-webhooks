<?php
	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	$response = $mailchimpAPI->cachedProcessRequest( '/lists' );

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
			'key'		=> 'stats->member_count'
		),
		array(
			'label'	=> 'Unsubscribe Count',
			'key'		=> 'stats->unsubscribe_count'
		),
		array(
			'label'	=> 'Cleaned Count',
			'key'		=> 'stats->cleaned_count'
		),
		array(
			'label'	=> 'Member Count Since Send',
			'key'		=> 'stats->member_count_since_send'
		),
		array(
			'label'	=> 'Unsubscribe Count Since Send',
			'key'		=> 'stats->unsubscribe_count_since_send'
		),
		array(
			'label'	=> 'Cleaned Count Since Send',
			'key'		=> 'stats->cleaned_count_since_send'
		),
		array(
			'label'	=> 'Campaign Count',
			'key'		=> 'stats->campaign_count'
		),
		array(
			'label'	=> 'Campaign Last Sent',
			'key'		=> 'stats->campaign_last_sent'
		),
		array(
			'label'	=> 'Merge Field Count',
			'key'		=> 'stats->merge_field_count'
		),
		array(
			'label'	=> 'Avg Sub Rate',
			'key'		=> 'stats->avg_sub_rate'
		),
		array(
			'label'	=> 'Avg Unsub Rate',
			'key'		=> 'stats->avg_unsub_rate'
		),
		array(
			'label'	=> 'Target Sub Rate',
			'key'		=> 'stats->target_sub_rate'
		),
		array(
			'label'	=> 'Open Rate',
			'key'		=> 'stats->open_rate'
		),
		array(
			'label'	=> 'Click Rate',
			'key'		=> 'stats->click_rate'
		),
		array(
			'label'	=> 'Last Sub Date',
			'key'		=> 'stats->last_sub_date'
		),
		array(
			'label'	=> 'Last Unsub Date',
			'key'		=> 'stats->last_unsub_date'
		)
	);

	add_action( 'stripe_webhooks_admin_column', function( $col_label, $row, $col_slug ){

		switch( $col_slug ){

			case 'name':
				$list_id = $row['id'];
				$baseurl = admin_url( 'admin.php' ) . "?page=" . $_GET['page'] . "&list_id=" . $list_id;

				$buttons = array(
					'list-activity' 	=> 'Activity',
					'list-growth' 		=> 'Growth',
					'list-locations' 	=> 'Locations',
					'list-segments' 	=> 'Segments'
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

			case 'last-sub-date':
			case 'last-unsub-date':
			case 'campaign-last-sent':
				$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
				if( $table_ui->checkIsAValidDate( $col_label ) ){
					$col_label = date( 'd M Y', strtotime( $col_label ) );
				}
				break;
		}
		return $col_label;
	}, 10, 3 );

	if( isset( $response->lists ) ){
		$table_ui = STRIPE_WEBHOOKS_TABLE_UI::getInstance();
		_e( "<div class='table-wrapper'>" );
		$table_ui->display( $columns, $response->lists, 'lists' );
		_e( "</div>" );
	}

	//echo "<pre>";
	//print_r( $response->total_items );
	//echo "</pre>";
