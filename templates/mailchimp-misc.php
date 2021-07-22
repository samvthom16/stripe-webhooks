<?php

	$screens = array(
		'orders'					=> 'Orders',
		'order-info'			=> 'Order Info',
		'campaigns'				=> 'Campaigns',
		'campaign-info'		=> 'Campaign Info',
		'automations'			=> 'Automations',
		'automation-info'	=> 'Automation Info'
	);

	$this->displayTabs( $screens, 'Mailchimp Misc', plugin_dir_path( __FILE__ ) . "mailchimp-misc" );
