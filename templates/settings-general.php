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
	<p class='submit'><input type="submit" name="submit" class="button button-primary" value="Save Settings"><p>
</form>
