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
			),
			'mailchimp-lists'	=> array(
				'title'	=> 'Mailchimp Lists',
				'menu'	=> 'stripe-mailchimp-webhooks'
			),
			'mailchimp-misc'	=> array(
				'title'	=> 'Mailchimp Misc',
				'menu'	=> 'stripe-mailchimp-webhooks'
			),
			'mailchimp-member-feed'	=> array(
				'title'	=> 'Mailchimp Member Feed',
				'menu'	=> 'stripe-mailchimp-webhooks'
			)
		) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'assets') );

		add_action( 'wp_ajax_exportcsv', array( $this, 'exportCsv' ) );

		/* SAMPLE ACTION HOOK FOR AJAX CALL */
		add_action('orbit_batch_action_stripe_export_member_feeds', function(){

			$members = $_POST[ 'members' ];
			$list_id = $_POST[ 'list_id' ];
			$batch_step = $_POST[ 'orbit_batch_step' ];
			$columns = $_POST[ 'columns' ];
			$file_slug = $_POST[ 'file_slug' ];

			$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();
			$export = STRIPE_WEBHOOKS_EXPORT::getInstance();

			$member_email = $members[ $batch_step - 1 ];
			$member_id = $mailchimpAPI->getSubscriberHash( $member_email );
			$feed_response = $mailchimpAPI->processRequest( "/lists/$list_id/members/$member_id/activity-feed?count=1000" );

			echo $member_id;
			foreach( $feed_response->activity as $activity ){
				$activity->id = $member_id;

				$row = array();

				foreach ( $columns as $column ) {
					if( $column == 'email_address' ){
						array_push( $row, $member_email );	
					}
					else{
						array_push( $row, $activity->$column );
					}
				}

				$export->addRowToCSV( $file_slug, $row );

				//echo "<pre>";
				//print_r( $row );
				//echo "</pre>";
			}


		} );


	}

	function exportCsv(){

		$export = STRIPE_WEBHOOKS_EXPORT::getInstance();

		$file_slug = $_POST['settings']['filename'];

		$filepath = $export->addRowsToCSV( $file_slug, $_POST['rows'] );

		wp_send_json( $filepath );


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

	function assets(){
		wp_enqueue_script( 'stripe-webhooks-admin', plugins_url( 'stripe-webhooks/dist/js/admin.js' ), array(), time() );
		wp_enqueue_style( 'stripe-webhooks-admin', plugins_url( 'stripe-webhooks/dist/css/admin.css' ), array(), time() );

		$orbit_batch_process = new ORBIT_BATCH_PROCESS;
		$orbit_batch_process->enqueue();
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

	function displayForm( $settingsOptions ){

		_e( "<form data-behaviour='form-redirect'>" );

		foreach( $settingsOptions as $option_slug => $option_title ){
			$option_name = $option_slug;
			$option_value = isset( $_GET[ $option_slug ] ) ? $_GET[ $option_slug ] : "";

			_e('<p>');
			_e( "<label>$option_title</label><br>" );
			_e( "<input class='form-field' type='text' name='$option_name' style='width: 100%; max-width: 400px;' value='$option_value' />" );
			_e('</p>');
		}

		// SHOW URL PARAMETERS IN HIDDEN FIELD
		$url = admin_url( 'admin.php' );
		$url_params = array( 'page', 'action' );
		$i = 0;
		foreach( $url_params as $param ){
			if( isset( $_GET[ $param ] ) && $_GET[ $param ] ){

				if( $i ){ $url .= "&"; }
				else{ $url .= "?"; }

				$url .= $param . "=" . $_GET[ $param ];
				$i++;
			}
		}

		_e( "<input type='hidden' name='url' value='$url' />" );
		_e( "<p class='submit'><input type='submit' name='submit' class='button button-primary' value='Get'><p>" );
		_e( '</form>' );
	}

	function displayTabs( $screens, $heading = '', $folder = '' ){
		_e( "<div class='wrap'>" );

		_e( "<h1>$heading</h1>" );

		_e( "<h2 class='nav-tab-wrapper'>" );

		$i = 0;
		$active_tab = '';
		foreach( $screens as $slug => $title ){
			$base_settings_url = "admin.php?page=" . $_GET['page'];

			$url = admin_url( $base_settings_url );

			if( $i ){
				$url =  esc_url( add_query_arg( array( 'action' => $slug ), admin_url( $base_settings_url ) ) );
			}

			$nav_class = "nav-tab";

			if( $i && isset( $_GET['action'] ) && $slug == $_GET['action'] ){
				$nav_class .= " nav-tab-active";
				$active_tab = $slug;
			}

			if( !$i && !isset( $_GET['action'] ) ){
				$nav_class .= " nav-tab-active";
				$active_tab = $slug;
			}

			$i++;

			_e( "<a href='$url' class='$nav_class'>$title</a>" );
		}
		_e( "</h2>" );

		$file_location = 	$folder . "/" . $active_tab . ".php";

		if( file_exists( $file_location ) ){
			include( $file_location );
		}

		_e( "</div>" );
	}



}

STRIPE_WEBHOOKS_ADMIN::getInstance();
