<?php

	function displayForm( $settingsOptions ){
		?>
		<form data-behaviour='form-redirect'>
			<?php
				foreach( $settingsOptions as $option_slug => $option_title ):
					$option_name = $option_slug;
					$option_value = isset( $_GET[ $option_slug ] ) ? $_GET[ $option_slug ] : "";

			?>
			<p>
				<label><?php _e( $option_title );?></label><br>
				<input class='form-field' type="text" name="<?php _e( $option_name );?>" style="width: 100%; max-width: 400px;" value="<?php _e( $option_value );?>" />
			</p>
			<?php endforeach;?>

			<?php

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

			?>

			<input type="hidden" name="url" value="<?php _e( $url );?>" />
			<p class='submit'><input type="submit" name="submit" class="button button-primary" value="Get"><p>
		</form>
		<?php
	}

	$screens = array(
		'lists'	=> array(
			'label'		=> 'Lists',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-lists.php'
		),
		'list-activity'	=> array(
			'label'		=> 'Activity',
			'action'	=> 'list-activity',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-listactivity.php'
		),
		'list-growth'	=> array(
			'label'		=> 'Growth',
			'action'	=> 'list-growth',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-listgrowth.php'
		),
		'list-locations'	=> array(
			'label'		=> 'Locations',
			'action'	=> 'list-locations',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-listlocations.php'
		),
		'list-segments'	=> array(
			'label'		=> 'Segments',
			'action'	=> 'list-segments',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-listsegments.php'
		),
		'segment-members'	=> array(
			'label'		=> 'Segment Members',
			'action'	=> 'segment-members',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-segmentmembers.php'
		),
		'member-activity'	=> array(
			'label'		=> 'Member Activity',
			'action'	=> 'member-activity',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-memberactivity.php'
		),
		'member-feed'	=> array(
			'label'		=> 'Member Feed',
			'action'	=> 'member-feed',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-memberfeed.php'
		),
		'member-tags'	=> array(
			'label'		=> 'Member Tags',
			'action'	=> 'member-tags',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-membertags.php'
		),
		'orders'	=> array(
			'label'		=> 'Orders',
			'action'	=> 'orders',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-orders.php'
		),
		'order-info'	=> array(
			'label'		=> 'Order Info',
			'action'	=> 'order-info',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-orderinfo.php'
		),
		'campaigns'	=> array(
			'label'		=> 'Campaigns',
			'action'	=> 'campaigns',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-campaigns.php'
		),
		'campaign-info'	=> array(
			'label'		=> 'Campaign Info',
			'action'	=> 'campaign-info',
			'tab'		=> plugin_dir_path(__FILE__).'mailchimp-campaigninfo.php'
		),
	);

	$active_tab = '';
?>
<div class="wrap">
	<h1>Mailchimp Data</h1>
	<h2 class="nav-tab-wrapper">
	<?php
		foreach( $screens as $slug => $screen ){

			$base_settings_url = "admin.php?page=mailchimp-data";

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
<script>

	jQuery('[data-behaviour~=form-redirect]').each(function(){
			var $form 	= jQuery( this );

			function getURL(){
				var url = $form.find('input[name=url]').val();

				$form.find( 'input.form-field' ).each( function(){
					var $input  = jQuery( this ),
						name			= $input.attr( 'name' ),
						value			= $input.val();

					url += "&" + name + "=" + value;

				} );
				return url;
			}

			console.log( getURL() );

			$form.submit( function( ev ){
				ev.preventDefault();

				location.href = getURL();

			} );
	} );


</script>
