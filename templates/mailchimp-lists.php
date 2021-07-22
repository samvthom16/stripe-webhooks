<?php
	
	$screens = array(
		'lists'						=> 'Lists',
		'list-activity'		=> 'Activity',
		'list-growth'			=> 'Growth',
		'list-locations'	=> 'Locations',
		'list-segments'		=> 'Segments',
		'segment-members'	=> 'Segment Members',
		'member-activity'	=> 'Member Activity',
		'member-feed'			=> 'Member Feed',
		'member-tags'			=> 'Member Tags'
	);

	$this->displayTabs( $screens, 'Mailchimp Lists', plugin_dir_path( __FILE__ ) . "mailchimp-lists" );
