<div class="wrap google_routeplaner">
	<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Troubleshooting', 'google_routeplaner'); ?></h2>
	<div id="poststuff">
	
		<div class="postbox" style="width: 48%; float: right;">
			<h3><?php _e('Language errors', 'google_routeplaner'); ?></h3>
			<div class="inside">
				<p><?php _e('If you have two or more maps on the same page (or in multiple posts shown on one page) you are not able to select different languages for each map.', 'google_routeplaner'); ?> 
				<?php _e('All maps will use the language used from the last loaded map.', 'google_routeplaner'); ?></p>
				<p><?php _e('The only way to fix this is to put every map on it\'s own page.', 'google_routeplaner'); ?></p>
			</div>	
		</div>
		<div class="postbox" style="width: 48%; float: left;">
			<h3><?php _e('Display errors', 'google_routeplaner'); ?></h3>
			<div class="inside">
				<p><?php _e('Some people had display error with the map like missing markers or seams between parts of the map.', 'google_routeplaner'); ?></p>
				<p><?php _e('This is most of the time caused by your theme and sadly this is also the case with the <em>Twenty Eleven</em>.', 'google_routeplaner'); ?></p>
				<p><?php _e('Please switch to the <em>Twenty Ten</em> to see if the theme is your problem.', 'google_routeplaner'); ?></p>
				<p><?php _e('If that is the case, you need to look into your themes CSS and try to find the troublemaker there.', 'google_routeplaner'); ?></p>
				<p><?php _e('I can not give free support and fix your theme!', 'google_routeplaner'); ?></p>
				<p><img src="<?php echo WP_PLUGIN_URL; ?>/google-routeplaner/images/twenty_eleven_map.jpg" alt="<?php _e('Trouble with Twenty Eleven', 'google_routeplaner'); ?>" /></p>
				<p><?php _e('As you can see, the zoom slider is broken and there are seams between map parts with <em>Twenty Eleven</em>.', 'google_routeplaner'); ?></p>
				<p><img src="<?php echo WP_PLUGIN_URL; ?>/google-routeplaner/images/twenty_ten_map.jpg" alt="<?php _e('No trouble with Twenty Ten', 'google_routeplaner'); ?>" /></p>
				<p><?php _e('Zoom slider is working and there and working and the map parts are seamless with <em>Twenty Ten</em>.', 'google_routeplaner'); ?></p>
			</div>	
		</div>
		<div style="clear: both;"></div>
	</div>