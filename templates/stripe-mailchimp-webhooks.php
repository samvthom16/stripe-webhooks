<?php

	$screens = array(
		'general'		=> 'General Settings',
		'stores'		=> 'Mailchimp Stores',
		'payments'	=> 'Recent Payments',
		'invoices'	=> 'Recent Invoices',
	);

	$this->displayTabs( $screens, 'Stripe Mailchimp Webhooks', plugin_dir_path( __FILE__ ) . "settings" );

	
