<?php

	$screens = array(
		'general'	=> array(
			'label'		=> 'General Settings',
			'tab'		=> plugin_dir_path(__FILE__).'settings-general.php'
		),
		'stores'	=> array(
			'label'		=> 'Mailchimp Stores',
			'action'	=> 'stores',
			'tab'		=> plugin_dir_path(__FILE__).'settings-stores.php'
		),
		'payments'	=> array(
			'label'		=> 'Recent Payments',
			'action'	=> 'payments',
			'tab'			=> plugin_dir_path(__FILE__).'settings-payments.php'
		),
		'invoices'	=> array(
			'label'		=> 'Recent Invoices',
			'action'	=> 'invoices',
			'tab'			=> plugin_dir_path(__FILE__).'settings-invoices.php'
		),
		'order_info'	=> array(
			'label'		=> 'Get Order Info',
			'action'	=> 'order_info',
			'tab'			=> plugin_dir_path(__FILE__).'settings-order-info.php'
		),
	);

	$screens = apply_filters( 'meteor_admin_settings_screens', $screens );

	$active_tab = '';
?>
<div class="wrap">
	<h1>Stripe Webhooks Settings</h1>
	<h2 class="nav-tab-wrapper">
	<?php
		foreach( $screens as $slug => $screen ){

			$base_settings_url = "admin.php?page=stripe-mailchimp-webhooks";

			$url = admin_url( $base_settings_url );

			if( isset( $screen['action'] ) ){
				$url =  esc_url( add_query_arg( array( 'action' => $screen['action'] ), admin_url( $base_settings_url ) ) );
			}

			$nav_class = "nav-tab";

			if( isset( $screen['action'] ) && isset( $_GET['action'] ) && $screen['action'] == $_GET['action'] ){
				$nav_class .= " nav-tab-active";
				$active_tab = $slug;
			}

			if( ! isset( $screen['action'] ) && ! isset( $_GET['action'] ) ){
				$nav_class .= " nav-tab-active";
				$active_tab = $slug;
			}

			echo '<a href="'.$url.'" class="'.$nav_class.'">'.$screen['label'].'</a>';
		}
	?>
	</h2>
	<?php

		if( file_exists( $screens[ $active_tab ][ 'tab' ] ) ){
			include( $screens[ $active_tab ][ 'tab' ] );
		}

	?>
</div>
