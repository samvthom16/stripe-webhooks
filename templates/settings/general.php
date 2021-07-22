<?php
	
	$settingsMetaOptionKey = $this->getSettingsMetaOptionKey();

	$settingsOptions = $this->getSettingsOptions();

	if( isset( $_POST['submit'] ) && isset( $_POST[ $settingsMetaOptionKey ] ) ){
		update_option( $settingsMetaOptionKey, $_POST[ $settingsMetaOptionKey ] );
		$this->setSettings( $_POST[ $settingsMetaOptionKey ] );
		$this->displayUpdateNotice( "General Settings have been saved." );
	}

	$data = $this->getSettings();

?>
<form method="POST">
	<?php
		foreach( $settingsOptions as $option_slug => $option_title ):
			$option_value = isset( $data[$option_slug] ) ? $data[$option_slug] : '';
			$option_name = $settingsMetaOptionKey . "[" . $option_slug ."]";
	?>
	<p>
		<label><?php _e( $option_title );?></label><br>
		<input type="text" name="<?php _e( $option_name );?>" style="width: 100%; max-width: 400px;" value="<?php _e( $option_value );?>" />
	</p>
	<?php endforeach;?>
	<p>Setup webhook for this URL: <span class="badge"><?php _e( admin_url( 'admin-ajax.php' ) . '?action=stripe-webhooks' );?></span></p>
	<p class='submit'><input type="submit" name="submit" class="button button-primary" value="Save Settings"><p>
</form>
<style>
	form p span.badge{
		border: #ccc;
		background: #ccc;
		margin-left: 10px;
		padding: 3px 7px;
		display: inline;
		border-radius: 5px;
	}
</style>
