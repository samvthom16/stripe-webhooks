<?php

	$screens = array(
		'tab_one'	=> array(
			'label'		=> 'Tab One',
			'tab'		=> plugin_dir_path(__FILE__).'tab-one.php'
		),
		'tab_two'	=> array(
			'label'		=> 'Tab Two',
			'action'	=> 'sample-action',
			'tab'		=> plugin_dir_path(__FILE__).'tab-two.php'
		)
	);
	
	$active_tab = '';
?>
<div class="wrap">
	<h1>Stripe Webhooks Sample</h1>
	<h2 class="nav-tab-wrapper">
	<?php
		foreach( $screens as $slug => $screen ){

			$base_settings_url = "admin.php?page=stripe-mailchimp-sample";

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
