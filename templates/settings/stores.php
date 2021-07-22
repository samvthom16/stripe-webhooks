<?php

	$mailchimpAPI = STRIPE_WEBHOOKS_MAILCHIMP_API::getInstance();

	if( isset( $_POST['submit'] ) && isset( $_POST[ 'store' ] ) ){

		$response = $mailchimpAPI->createStore( $_POST['store'] );

		echo "<pre>";
		print_r( $response );
		echo "</pre>";

		if( isset( $response->status ) && $response->status != 200 ){
			$message = '';
			if( isset( $response->title ) ){ $message .= $response->title . '. '; }
			if( isset( $response->detail ) ){ $message .= $response->detail; }
			$this->displayErrorNotice( $message );
		}
		elseif( !isset( $response->status ) && isset( $response->id ) ){
			$this->displayUpdateNotice( "New Store has been created with id: " . $response->id . ". Wait for couple of minutes to see the new store in the list below." );
		}


	}

	$response = $mailchimpAPI->cachedProcessRequest( 'ecommerce/stores' );

	if( isset( $response->stores ) && count( $response->stores ) ){
		_e( "<h4>List of Mailchimp Stores</h4>" );
		_e( "<ul class='stores-list'>" );
		$i = 1;
		foreach( $response->stores as $store ){
			_e( "<li>" );
			_e( $i . '. ' . $store->name );
			_e( '<span class="badge">' . $store->id . '</span>' );
			_e( "</li>" );
			$i++;
		}
		_e( "<ul>" );
		_e( "<br><hr>" );
	}
?>
<form method="POST">
	<h4>Create a New Mailchimp Store</h4>
	<?php
		$settingsMetaOptionKey = 'store';
		$settingsOptions = array(
			'list_id'				=> 'Mailchimp List ID',
			'name'					=> 'Store Name',
			'currency_code'	=> 'Currency Code',
		);
		foreach( $settingsOptions as $option_slug => $option_title ):
			$option_name = $settingsMetaOptionKey . "[" . $option_slug ."]";
	?>
	<p>
		<label><?php _e( $option_title );?></label><br>
		<input type="text" name="<?php _e( $option_name );?>" style="width: 100%; max-width: 400px;" value="" />
	</p>
	<?php endforeach;?>
	<p class='submit'><input type="submit" name="submit" class="button button-primary" value="Create"><p>
</form>

<style>
	.stores-list li{
		max-width: 500px;
		border: #eee solid 1px;
		background: #fff;
		padding: 7px;
	}
	.stores-list li .badge{
		font-size: 80%;
		border: #ccc;
		background: #ccc;
		margin-left: 10px;
		padding: 3px;
		display: inline;
		border-radius: 5px;
	}
</style>
