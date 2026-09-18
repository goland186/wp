 <div class="wrap google_routeplaner">
   <div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Settings', 'google_routeplaner'); ?></h2>
	<?php
	/*
	 * Save Settings
	 */
	if ('google_routeplaner_save_settings' == $_POST['action'])
	{
		update_option("google_routeplaner_language", $_POST['google_routeplaner_language']);
		update_option("google_routeplaner_viewport", $_POST['google_routeplaner_viewport']);
		update_option("google_routeplaner_api_key", $_POST['google_routeplaner_api_key']);

		echo '<p class="success">' . __('Settings saved!', 'google_routeplaner') . '</p>';
	}
	?>
	<div id="poststuff"> 
	<form method="post" action="">
		<div style="width: 48%; float: right;">
		
			<div class="postbox">
				<h3><?php _e('Viewport settings', 'google_routeplaner'); ?></h3>
				<div class="inside">
					<p><?php _e('If you have set your viewport with another plugin you can disable the viewport output from this plugin.', 'google_routeplaner'); ?></p>
					<p><input type="radio" name="google_routeplaner_viewport" id="google_routeplaner_viewport_yes" value="yes"<?php if('yes' == get_option("google_routeplaner_viewport")) { echo ' checked="checked"'; } ?> />
					<label for="google_routeplaner_viewport_yes"><?php _e('Yes, use viewport.', 'google_routeplaner'); ?></label><br />
					
					<input type="radio" name="google_routeplaner_viewport" id="google_routeplaner_viewport_no" value="no"<?php if('no' == get_option("google_routeplaner_viewport")) { echo ' checked="checked"'; } ?> />
					<label for="google_routeplaner_viewport_no"><?php _e('No, do NOT use viewport.', 'google_routeplaner'); ?></label></p>
				</div>
			</div>
		</div>
	   <div style="width: 48%; float: left;">
		   <div class="postbox">
			   <h3><?php _e('Language', 'google_routeplaner'); ?></h3>
			   <div class="inside">
					<p><?php _e('You can set the language for the Google driving directions for all maps. You can overwrite this setting for every map.', 'google_routeplaner'); ?></p>
					<p><select name="google_routeplaner_language" id="google_routeplaner_language">
						<?php
						$gr_current_language = get_option("google_routeplaner_language");
						if(is_array($google_routeplaner_langs)) {
							foreach($google_routeplaner_langs as $code => $lang) {
								if($gr_current_language == $code) {
									echo '<option value="' . $code . '" selected="">' . $lang . '</option>';
								} else {
									echo '<option value="' . $code . '">' . $lang . '</option>';
								}
							}							
						}							
						?>
					</select><br />
					<i><?php _e('Set language for driving information, this does not effect the interface.', 'google_routeplaner'); ?></i></p>
				</div>
			</div>
			
			<div class="postbox">
			   <h3><?php _e('API Key', 'google_routeplaner'); ?></h3>
			   <div class="inside">
					<p><?php _e('Since Version 3 of the API there is no more need for an API Key. But you have more control and statistics over your API usage.', 'google_routeplaner'); ?><br />
					<?php _e('You can get an API Key from Google <a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key">here</a>.', 'google_routeplaner'); ?></p>
					<p><input type="text" name="google_routeplaner_api_key" id="google_routeplaner_api_key" value="<?php echo get_option("google_routeplaner_api_key"); ?>" />					
					<br />
					<i><?php _e('You need a Google account to get an API Key.', 'google_routeplaner'); ?></i></p>
				</div>
			</div>
		</div>
		<p style="clear: both;"><input type="submit" class="button-primary" value="<?php _e('Save settings', 'google_routeplaner'); ?>" />
		<input name="action" value="google_routeplaner_save_settings" type="hidden" /></p>
	   </form>
   </div>
</div>