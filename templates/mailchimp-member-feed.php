<?php

function test( $data ){
	echo "<pre>";
	print_r( $data );
	echo "</pre>";
}


_e( '<div class="wrap">' );

$list_id = '7732cae558';
$per_page = 200;

$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
$response = $mailchimpAPI->processRequest( "/lists/$list_id/members?count=$per_page" );
$members = array();
foreach( $response->members as $member ){
	//print_r( $member );
	array_push( $members, $member->email_address );
}

$file_slug = 'member-feeds';
$export = STRIPE_WEBHOOKS_EXPORT::getInstance();
$header_row = array(
	'id',
	'email_address',
	'activity_type',
	'created_at_timestamp',
	'campaign_id',
	'campaign_title',
	'link_clicked'
);
$export->addRowToCSV( $file_slug, $header_row, 'w' );

$orbit_batch_process = new ORBIT_BATCH_PROCESS;

echo $orbit_batch_process->shortcodeFn( array(
	'title' 				=> 'Exporting Member Feeds',
	'desc'					=> '',
	'batch_action'	=> 'stripe_export_member_feeds',
	'batches'				=> count( $members ),
	'params'				=> array(
		'file_slug'		=> $file_slug,
		'members'			=> $members,
		'list_id'			=> $list_id,
		'columns'			=> $header_row
	)
) );

//print_r( $orbit_batch_process );

/*


//test( $response->members );

/*
foreach( $response->members as $member ){
	$member_id = $member->id;
	echo $member_id;

}
*/

_e( '</div>' );
