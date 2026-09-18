 <div class="wrap google_routeplaner">
   <div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Overview', 'google_routeplaner'); ?></h2>
	<div id="poststuff">
		<div style="width: 33%; float: right;">
			<div class="postbox">
				<h3><?php _e('Changelog', 'google_routeplaner'); ?></h3>
				<div class="inside" style="height: 300px; overflow: auto; overflow-y: scroll;">
					<h5>Version 4.0</h3>
						<ul>
							<li>Fix for update problems</li>
							<li>Project now abandoned - no more support, no more ads, no more donations!</li>
						</ul>

					<h5>Version 3.6</h3>
						<ul>
							<li>Fix for the foreach-error</li>
							<li>Import should now work or give an error message</li>
						</ul>
						
					<h5>Version 3.5</h3>
						<ul>
							<li>New and way more flexible database structure</li>
							<li>You can now use a Google API Key if you want</li>
							<li>Maps now have a title that can be different from the destination</li>
							<li>You can now define a display destination. This is shown instead of the route destination, for example if you have to use longitude and latitude.</li>
							<li>Added pan control options</li>
						</ul>
				
					<h5>Version 3.2</h3>
						<ul>
							<li>Added checks for editing and creating routes</li>
						</ul>
					
					<h5>Version 3.1</h3>
						<ul>
							<li>Added a lot of new languages which Google supports</li>
							<li>Label and button of the maps are now translated with a PHP-file. This should make it a lot easier
							to translate and to have different languages for maps on the same WordPress installation.<br />
							Take a look at the <em>google-routeplaner-translations.php</em> in the plugins directory!<br />
							Copy this file to the directory of your active theme to apply and keep changes even after updates.</li>
							<li>Added an integrity check to solve database issues.</li>
						</ul>
					<h5>Version 3.0</h5>
						<ul>
							<li>Donation settings have been changed. Link can be removed without donating</li>
							<li>Viewport can now be disabled</li>
							<li>Small graphical tweaks</li>
							<li>Troubleshooting page added</li>
							<li>Autodetection can now give longitude and latitude or country, city and street</li>
						</ul>
					<h5>Version 2.6</h5>
						<ul>
							<li>Hotfix for trouble with version 2.5</li>
						</ul>
					<h5>Version 2.5</h5>
						<ul>
							<li>French translation added, thanks to Corentin Smith</li>
							<li>Slovak Translation by <a href="http://webhostinggeeks.com/blog/">WebHostingGeeks.com</a> added</li>
							<li>You can now define map height and width in px, % or em</li>
							<li>New feature: HTML5 detection for users location to autofill the form</li>
						</ul>
					<h5>Version 2.3</h5>
						<ul>
							<li>WordPress Multisite support</li>
							<li>Added default values for zoom level to prevent errors</li>
							<li>New Icon</li>
							<li>You are now asked if you really want to delete a route</li>
						</ul>
					<h5>Version 2.2</h5>
						<ul>
							<li>Donation settings have been changed. Please take a look at them!</li>
							<li>Fix for install error (which did not effect function)</li>
							<li>Small visual improvments</li>
							<li>Documentation page added</li>
						</ul>
					<h5>Version 2.1</h5>
						<ul>
							<li>Fixed a bug where installation went wrong and routes could not be added</li>
						</ul>
					<h5>Version 2</h5>Fix for install error (which did not effect function)
						<ul>
							<li>Issues with writing to the database some users had fixed</li>
							<li>Removed a lot of potential issues other plugins may cause</li>
							<li>Zoom is now adjustable</li>
							<li>Added a marker to the map when no route is calculated</li>
						</ul>
					<h5>Version 1.5</h5>
						<ul>
							<li>Added some icons</li>
							<li>Made it easier to copy the routes codes</li>
							<li>You can now use more then one map on the same page (but only one language is possible at once)</li>
							<li>Removed CSS additions due it only caused invalid HTML code</li>
							<li>Due to the updates you can now change the look using your themes CSS file.</li>
						</ul>
					<h5>Version 1.21</h5>
						<ul>
							<li>Hotfix for not working setup</li>
						</ul>
					<h5>Version 1.2</h5>
						<ul>
							<li>You can now select a different language for every map</li>
							<li>Fixed a compatiblity issues with the <a href="http://thecartpress.com">TheCartPress</a> plugin</li>
						</ul>
					<h5>Version 1.1</h5>
						<ul>
							<li>Cleaned up Sourcecode</li>
							<li>Removed some validation issues (there are still some remaining)</li>
						</ul>
					<h5>Version 1.0</h5>
						<ul>
							<li>Updated to Google Maps API V3</li>
							<li>No more API Key required</li>
							<li>Overview Map no longer available due to it's not available in Google Maps API V3</li>
							<li>Improved design</li>
						</ul>
					<h5>Version 0.6</h5>
						<ul>
							<li>Displays the code for each route in the admin section</li>
							<li>Added a preview function to the admin section</li>
						</ul>
					<h5>Version 0.5</h5>
						<ul>
							<li>Plugin release</li>
						</ul>
			   </div>
		   </div>
		   
		   	<div class="postbox">
			   <h3><?php _e('Enjoy the plugin?', 'google_routeplaner'); ?></h3>
				<div class="inside">
					<p><?php _e('If you like the plugin you can support my work in different ways:', 'google_routeplaner'); ?></p>
						<ul style="list-style-type: none;">
							<li><img src="<?php echo WP_PLUGIN_URL; ?>/google-routeplaner/images/icon_wordpress.png" alt="" /> <a href="http://wordpress.org/extend/plugins/google_routeplaner/"> <?php _e('Rate it on WordPress.org', 'google_routeplaner'); ?></a></li>
							<li><img src="<?php echo WP_PLUGIN_URL; ?>/google-routeplaner/images/icon_paypal.png" alt="" /> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=VAFAKGCDQ2GHU"> <?php _e('Donate using PayPal', 'google_routeplaner'); ?></a></li>
						</ul>
				</div>
		   </div>
		   
		   <div class="postbox">
				<h3><?php _e('Support &amp; Feedback', 'google_routeplaner'); ?></h3>
				<div class="inside">
					<p><?php _e('This plugin will not be supported anymore.', 'google_routeplaner'); ?></p>
				</div>
			</div>
		</div>
		
		<div style="width: 65%; float: left;">
						
			<div class="postbox">
				<h3><?php _e('The Plugin', 'google_routeplaner'); ?></h3>
				<div class="inside">
					<p><?php _e('Google Routeplanner generates a routeplanner based on the <a href="http://code.google.com/apis/maps/">Google Maps API</a>.', 'google_routeplaner'); ?></p>
					<p><?php _e('We suggest adding a printer friendly stylesheet to your Wordpress design to allow your users to print the route without the need to print design elements. A tutorial how to do this can be found <a href="http://codex.wordpress.org/Styling_for_Print">here</a>.', 'google_routeplaner'); ?></p>	
				</div>
			</div>

			<div class="postbox">
				<h3><?php _e('Translations', 'google_routeplaner'); ?></h3>
				<div class="inside">
					<table class="widefat" cellspacing="0" cellpadding="0">
						<thead>
							<tr class="column-cb">
								<th><?php _e('Language', 'google_routeplaner'); ?></th>
								<th><?php _e('Progress', 'google_routeplaner'); ?></th>
								<th><?php _e('Contributor', 'google_routeplaner'); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr class="column-cb">
								<th><?php _e('Language', 'google_routeplaner'); ?></th>
								<th><?php _e('Progress', 'google_routeplaner'); ?></th>
								<th><?php _e('Contributor', 'google_routeplaner'); ?></th>
							</tr>
						</tfoot>
						<tbody>
							<tr class="odd">
								<td><?php _e('English', 'google_routeplaner'); ?></td>
								<td><?php _e('Core', 'google_routeplaner'); ?></td>
								<td>Unknown</td>
							</tr>
							<tr class="even">
								<td><?php _e('German', 'google_routeplaner'); ?></td>
								<td>98%</td>
								<td>Unknown</td>
							</tr>
							<tr class="odd">
								<td><?php _e('French', 'google_routeplaner'); ?></td>
								<td>31%</td>
								<td>Corentin Smith</td>
							</tr>
							<tr class="even">
								<td><?php _e('Dutch', 'google_routeplaner'); ?></td>
								<td>31%</td>
								<td><?php _e('Unknown', 'google_routeplaner'); ?></td>
							</tr>
							<tr class="odd">
								<td><?php _e('Romanian', 'google_routeplaner'); ?></td>
								<td>31%</td>
								<td><a href="http://webhostinggeeks.com/">WebHostingGeeks.com</a></td>
							</tr>
							<tr class="even">
								<td><?php _e('Slovak', 'google_routeplaner'); ?></td>
								<td>31%</td>
								<td><a href="http://webhostinggeeks.com/">WebHostingGeeks.com</a></td>
							</tr>
						</tbody>				
					</table>
				</div>
			</div>
		</div>
	</div>
</div>