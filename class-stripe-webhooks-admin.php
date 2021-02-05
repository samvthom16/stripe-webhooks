<?php

class STRIPE_WEBHOOKS_ADMIN extends STRIPE_WEBHOOKS_BASE{

	var $settings;
	var $settingsMetaOptionKey;

	function __construct(){

		$this->setSettingsMetaOptionKey( 'stripe_webhooks_settings' );

		$this->setSettings( get_option( $this->getSettingsMetaOptionKey() ) );

		add_action( 'admin_menu', function(){

			add_submenu_page(
				'options-general.php',
				__('Stripe Webhoooks Settings', 'stripe-webhooks'),
				__('Stripe Webhoooks Settings', 'stripe-webhooks'),
				'manage_options',
				'settings',
				array( $this, 'settings_page' )
			);


		});

	}

	function setSettingsMetaOptionKey( $settingsMetaOptionKey ){ $this->settingsMetaOptionKey = $settingsMetaOptionKey; }
	function getSettingsMetaOptionKey(){ return $this->settingsMetaOptionKey; }

	// DEFAULT VALUE SHOULD BE ARRAY
	function getSettings(){
		if( empty( $this->settings ) || !is_array( $this->settings ) ) $this->settings = array();
		return $this->settings;
	}
	function setSettings( $settings ){ $this->settings = $settings; }

	function getSettingsOptions(){
		return array(
			'stripeSecret' 				=> 'Stripe Secret Key',
			'stripePublishable'		=> 'Stripe Publishable Key',
			'mailchimpAPIKey'			=> 'Mailchimp API Key',
			'mailchimpServer'			=> 'Mailchimp Server',
			'mailchimpStoreID'		=> 'Mailchimp Store ID'
		);
	}

	function settings_page(){
		include "templates/settings.php";
	}

	function displayUpdateNotice( $message ){
		?>
		<div class="updated notice">
    	<p><?php _e( $message );?></p>
		</div>
		<?php
	}

	function displayErrorNotice( $message ){
		?>
		<div class="error notice">
    	<p><?php _e( $message );?></p>
		</div>
		<?php
	}

}

STRIPE_WEBHOOKS_ADMIN::getInstance();
