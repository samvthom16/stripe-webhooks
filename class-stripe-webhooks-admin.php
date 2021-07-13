<?php

class STRIPE_WEBHOOKS_ADMIN extends STRIPE_WEBHOOKS_BASE{

	var $menu;
	var $settings;
	var $settingsMetaOptionKey;

	function __construct(){

		$this->setSettingsMetaOptionKey( 'stripe_webhooks_settings' );

		$this->setSettings( get_option( $this->getSettingsMetaOptionKey() ) );

		$this->setMenu( array(
			'stripe-mailchimp-webhooks'	=> array(
				'title'	=> 'Stripe Mailchimp Webhooks',
				'icon'	=> 'dashicons-editor-kitchensink'
			)
		) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	/*
	* GETTER AND SETTER FUNCTIONS
	*/
	function getMenu(){ return $this->menu; }
	function setMenu( $menu ){ $this->menu = $menu; }

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
	/*
	* END OF GETTER AND SETTER FUNCTIONS
	*/

	function admin_menu(){

		foreach( $this->getMenu() as $slug => $menu_item ){

			$menu_item['slug'] = $slug;

			// CHECK FOR MAIN MENU OR SUB MENU
			if( !isset( $menu_item['menu'] ) ){
				add_menu_page( $menu_item['title'], $menu_item['title'], 'manage_options', $menu_item['slug'], array( $this, 'menu_page' ), $menu_item['icon'] );
			}
			else{
				add_submenu_page( $menu_item['menu'], $menu_item['title'], $menu_item['title'], 'manage_options', $menu_item['slug'], array( $this, 'menu_page' ) );
			}

		}

	}

	/* MENU PAGE */
	function menu_page(){
		$page = $_GET[ 'page' ];
		include( 'templates/'.$page.'.php' );
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
