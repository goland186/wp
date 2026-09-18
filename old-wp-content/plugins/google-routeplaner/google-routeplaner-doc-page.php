<div class="wrap google_routeplaner">
	<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Documentation', 'google_routeplaner'); ?></h2>
	<div id="poststuff">
		<div class="postbox" style="width: 48%; float: right;">
			<h3><?php _e('Theme the routeplanner', 'google_routeplaner'); ?></h3>
			<div class="inside">
				<p><?php _e('You can use your themes CSS file to edit how the routeplanners output looks on your page.', 'google_routeplaner'); ?></p>
				<p><strong><?php _e('Classes', 'google_routeplaner'); ?></strong></p>
				<p><?php _e('To effect all maps on your website you can use the following classes:', 'google_routeplaner'); ?></p>
				<ul>
					<li>google_map_controls (<?php _e('The area with the input field', 'google_routeplaner'); ?>)</li>
					<li>google_map_canvas (<?php _e('The map itself', 'google_routeplaner'); ?>)</li>
					<li>google_map_directions (<?php _e('The driving directions displayed below the map', 'google_routeplaner'); ?>)</li>
				</ul>
				<p><?php _e('Example', 'google_routeplaner'); ?></p>
				<p><code>.google_map_directions {<br />
				&nbsp;&nbsp;&nbsp;background-color: #FF0000;<br />
				}</code><br />
				<?php _e('This will set the background color for the directions of all maps to red.', 'google_routeplaner'); ?></p>
				
				<p><strong><?php _e('IDs', 'google_routeplaner'); ?></strong></p>
				<p><?php _e('To effect only a specific map on your website you can use IDs.', 'google_routeplaner'); ?></p>
				<ul>
					<li>map_controls$ (<?php _e('The area with the input field', 'google_routeplaner'); ?>)</li>
					<li>map_canvas$ (<?php _e('The map itself', 'google_routeplaner'); ?>)</li>
					<li>map_directions$ (<?php _e('The driving directions displayed below the map', 'google_routeplaner'); ?>)</li>
				</ul>
				<p><?php _e('You have to replace the $ with the maps ID.', 'google_routeplaner'); ?></p>
				<p><?php _e('Example', 'google_routeplaner'); ?></p>
				<p><code>#map_directions4 {<br />
				&nbsp;&nbsp;&nbsp;color: #FFF;<br />
				}</code><br />
				<?php _e('This will set the text color for the directions of the map with the ID 4 to red.', 'google_routeplaner'); ?></p>
			
			</div>	
	</div>
	
	<div class="postbox" style="width: 48%; float: left;">
			<h3><?php _e('Translate the map', 'google_routeplaner'); ?></h3>
			<div class="inside">
				<p><?php _e('You can set the default language you want to use in the plugins settings.', 'google_routeplaner'); ?></p>
				<p><?php _e('Each map can have a different language. You can define the language when creating or editing a route.', 'google_routeplaner'); ?></p>
				<p><?php _e('Since V3.1 this also effects the form above the map and not only the driving directions.', 'google_routeplaner'); ?></p>
				<p><?php _e('If you want to change the translation or add one that is missing, go to the plugins directory and copy the file <em>google-routeplaner-translations.php</em> into your active themes directory.', 'google_routeplaner'); ?></p>
				<p><?php _e('Open the file with your prefered editor (I recommand <a href="http://notepad-plus-plus.org/">Notepad++</a>) and you will find code like this:', 'google_routeplaner'); ?></p>
				<code>$google_routeplaner_trans['en']['label'] = 'Your location';<br />
				$google_routeplaner_trans['en']['button'] = 'Calculate route';</code>
				<p><?php _e('This is the array for the english translation. Copy this to the bottom of the file and change the language code <em>en</em> to the language code of your language and translate the two phrases.', 'google_routeplaner'); ?></p>
				<p><?php _e('For example, the german translation would look like this:', 'google_routeplaner'); ?></p>
				<code>$google_routeplaner_trans['de']['label'] = 'Ihr Standort';<br />
				$google_routeplaner_trans['de']['button'] = 'Route berechnen';</code>
				<p><?php _e('Save the file and label and button will be translated.', 'google_routeplaner'); ?></p>
			</div>	
	</div>
</div>