<?php
/*
	Plugin Name: Stripe Webhooks
	Plugin URI: https://sputznik.com/
	Description: Custom built for ADF
	Version: 1.0.1
	Author: Samuel Thomas, Sputznik
	Author URI: https://sputznik.com/
*/

	if( ! defined( 'ABSPATH' ) ){
		exit;
	}

	$inc_files = array(
		//'stripe-php/init.php',
		'class-stripe-webhooks-base.php',
		'class-stripe-webhooks-stripe-api.php',
		'class-stripe-webhooks-mailchimp-api.php',
		'class-stripe-webhooks-admin.php',
		'class-stripe-webhooks.php'
	);

	foreach( $inc_files as $inc_file ){
		require_once( $inc_file );
	}
