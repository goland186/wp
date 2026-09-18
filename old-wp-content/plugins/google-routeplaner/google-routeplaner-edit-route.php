<?php
	global $wpdb, $google_routeplaner_langs;
	
	/*
	 * Save Route
	 */
	if ('google_routeplaner_edit_route' == $_POST['action'])
	{
		if('' !== $_POST['google_routeplaner_destination']) {
		
			if('' !== trim($_POST['google_routeplaner_title'])) {
				$map_name = $_POST['google_routeplaner_title'];
			} else {
				$map_name = $_POST['google_routeplaner_destination'];
			}
			
			$map_id = $_POST['route_id'];

			$wpdb->query("DELETE FROM " . $wpdb->prefix . "g_routeplanner_set
				WHERE set_plan = '" . $map_id . "'");
			
			$wpdb->query("UPDATE " . $wpdb->prefix . "g_routeplanner_plans 
			SET
			plan_title = '" . $map_name . "'
			WHERE plan_id = '" . $map_id . "' LIMIT 1");
			
			$wpdb->query("INSERT INTO " . $wpdb->prefix . "g_routeplanner_set (`set_plan`, `set_name`, `set_value`)
			VALUES 
				(
				'" . $map_id . "',
				'destination',
				'" . $_POST['google_routeplaner_destination'] . "'
				),
				(
				'" . $map_id . "',
				'map_width',
				'" . $_POST['google_routeplaner_map_width'] . "'
				),
				(
				'" . $map_id . "',
				'map_width_unit',
				'" . $_POST['google_routeplaner_map_width_unit'] . "'
				),
				(
				'" . $map_id . "',
				'map_height',
				'" . $_POST['google_routeplaner_map_height'] . "'
				),
				(
				'" . $map_id . "',
				'map_height_unit',
				'" . $_POST['google_routeplaner_map_height_unit'] . "'
				),
				(
				'" . $map_id . "',
				'zoom',
				'" . $_POST['google_routeplaner_zoom'] . "'
				),			
				(
				'" . $map_id . "',
				'map_type',
				'" . $_POST['google_routeplaner_map_type'] . "'
				),			
				(
				'" . $map_id . "',
				'zoom_control',
				'" . $_POST['google_routeplaner_zoom_control'] . "'
				),
				(
				'" . $map_id . "',
				'pan_control',
				'" . $_POST['google_routeplaner_pan_control'] . "'
				),
				(
				'" . $map_id . "',
				'type_control',
				'" . $_POST['google_routeplaner_type_control'] . "'
				),
				(
				'" . $map_id . "',
				'autofill',
				'" . $_POST['google_routeplaner_autofill'] . "'
				),			
				(
				'" . $map_id . "',
				'language',
				'" . $_POST['google_routeplaner_language'] . "'
				),			
				(
				'" . $map_id . "',
				'alt_destination',
				'" . $_POST['google_routeplaner_alt_destination'] . "'
				)			
			");
		
			?>
			<div class="wrap google_routeplaner">
			<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Edit Route', 'google_routeplaner'); ?></h2>
			<p class="success"><?php _e('Your changes has been saved!', 'google_routeplaner'); ?></p>
			<p><a href="admin.php?page=google_routeplaner_routes"><?php _e('Back to overview', 'google_routeplaner'); ?></a></p>
			</div>		
			<?php
		} else {
			$gr_error['google_routeplaner_destination'] = true;
		}
	
	}
	if ('google_routeplaner_edit_route' !== $_POST['action'] || isset($gr_error)) {
		/*
		 * Output Form
		 */
		$planer = $wpdb->get_row("SELECT * 
		FROM " . $wpdb->prefix . "g_routeplanner_plans 
		WHERE plan_id='" . $route_id . "' 
		LIMIT 1", ARRAY_A);
		
		$planer_settings = $wpdb->get_results("SELECT * 
		FROM " . $wpdb->prefix . "g_routeplanner_set 
		WHERE set_plan='" . $route_id . "'", ARRAY_A);
		
		if(is_array($planer_settings)) {
			foreach($planer_settings as $setting) {
				$sett[$setting['set_name']] = $setting['set_value'];
			}		
		}

?>
	<div class="wrap google_routeplaner">
		<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> &bull; <?php _e('Edit Route', 'google_routeplaner'); ?></h2>
		<div id="poststuff"> 
			<form method="post" action="">
				<div class="postbox" style="clear: both;">
					<h3><?php _e('Map title', 'google_routeplaner'); ?></h3>
					<div class="inside">
						<p><input type="text" name="google_routeplaner_title" id="google_routeplaner_title" style="width: 450px;" value="<?php echo $planer['plan_title']; ?>" /><br />
						<i><?php _e('Empty to use destination as title.', 'google_routeplaner'); ?></i></p>
					</div>
				</div>
				<div style="width: 48%; float: right;">
					<div class="postbox">
						<h3><?php _e('Detail settings', 'google_routeplaner'); ?></h3>
						<div class="inside">
							<p><label for="google_routeplaner_map_type" class="formbold"><?php _e('Map type', 'google_routeplaner'); ?></label><br />
							<select name="google_routeplaner_map_type" id="google_routeplaner_map_type">
								<option value="ROADMAP"<?php if('ROADMAP' == $sett['map_type']) { echo ' selected=""'; } ?>><?php _e('Road map', 'google_routeplaner'); ?></option>
								<option value="SATELLITE"<?php if('SATELLITE' == $sett['map_type']) { echo ' selected=""'; } ?>><?php _e('Satellite map', 'google_routeplaner'); ?></option>
								<option value="HYBRID"<?php if('HYBRID' == $sett['map_type']) { echo ' selected=""'; } ?>><?php _e('Hybrid map', 'google_routeplaner'); ?></option>
								<option value="TERRAIN"<?php if('TERRAIN' == $sett['map_type']) { echo ' selected=""'; } ?>><?php _e('Physical map', 'google_routeplaner'); ?></option>	
							</select><br />
							<i><?php _e('Select how the map should look like.', 'google_routeplaner'); ?></i></p>
							
							<p><span class="formbold"><?php _e('Map Zoom Control', 'google_routeplaner'); ?></span><br />
							<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_large" value="DEFAULT"<?php if('DEFAULT' == $sett['zoom_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_zoom_control_large"><?php _e('Default', 'google_routeplaner'); ?></label><br />
							<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_small" value="SMALL"<?php if('SMALL' == $sett['zoom_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_zoom_control_small"><?php _e('Small', 'google_routeplaner'); ?></label><br />
							<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_zoom" value="LARGE"<?php if('LARGE' == $sett['zoom_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_zoom_control_zoom"><?php _e('Large', 'google_routeplaner'); ?></label><br />
							<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_none" value="NONE"<?php if('NONE' == $sett['zoom_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_zoom_control_none"><?php _e('None', 'google_routeplaner'); ?></label><br />
							<i><?php _e('Select the type of zoom and direction control you want.', 'google_routeplaner'); ?></i></p>
							
							
							<p><span class="formbold"><?php _e('Pan Control', 'google_routeplaner'); ?></span><br />
							<input type="radio" name="google_routeplaner_pan_control" id="google_routeplaner_pan_control_true" value="true"<?php if('true' == $sett['pan_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_pan_control_true"><?php _e('Yes', 'google_routeplaner'); ?></label><br />
							<input type="radio" name="google_routeplaner_pan_control" id="google_routeplaner_pan_control_false" value="false"<?php if('false' == $sett['pan_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_pan_control_false"><?php _e('No', 'google_routeplaner'); ?></label><br />
							<i><?php _e('Enable or disable the pan control.', 'google_routeplaner'); ?></i></p>
							
							<p><span class="formbold"><?php _e('Map Type Control', 'google_routeplaner'); ?></span><br />
							
							<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_default" value="DEFAULT"<?php if('DEFAULT' == $sett['type_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_type_control_default"><?php _e('Default', 'google_routeplaner'); ?></label><br />	
							<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_standard" value="HORIZONTAL_BAR"<?php if('HORIZONTAL_BAR' == $sett['type_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_type_control_standard"><?php _e('Normal', 'google_routeplaner'); ?></label><br />
							<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_hierachical" value="DROPDOWN_MENU"<?php if('DROPDOWN_MENU' == $sett['type_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_type_control_hierachical"><?php _e('Hierarchical', 'google_routeplaner'); ?></label><br />
							<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_none" value="NONE"<?php if('NONE' == $sett['type_control']) { echo ' checked="checked"'; } ?> />
							<label for="google_routeplaner_type_control_none"><?php _e('None', 'google_routeplaner'); ?></label><br />
							<i><?php _e('Select if users can change the map type.', 'google_routeplaner'); ?></i></p>
						</div>
					</div>
					
					<div class="postbox">
						<h3><?php _e('CSS Hooks', 'google_routeplaner'); ?></h3>
						<div class="inside">
							<code>
								/* The area with the input field for map <?php echo $planer['plan_id']; ?> */<br />
								#map_controls<?php echo $planer['plan_id']; ?> {<br />
								}<br />
								<br />
								/* Map <?php echo $planer['plan_id']; ?> itself */<br />
								#map_canvas<?php echo $planer['plan_id']; ?> {<br />
								}<br />
								<br />
								/* The driving directions displayed below map <?php echo $planer['plan_id']; ?> */<br />
								#map_directions<?php echo $planer['plan_id']; ?> {<br />
								}
							</code>
						</div>
					</div>
				</div>
				
				<div class="postbox" style="width: 48%; float: left;">
				<h3><?php _e('General settings', 'google_routeplaner'); ?></h3>
					<div class="inside">
						<p><label for="google_routeplaner_destination" class="formbold"><?php _e('Destination', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_destination" id="google_routeplaner_destination" style="width: 300px;" value="<?php echo $sett['destination']; ?>" /><br />
						<i><?php _e('Add a search string for the destination. Adress, company name or something like this.', 'google_routeplaner'); ?></i></p>
						<?php
						if($gr_error['google_routeplaner_destination']) {
							echo '<p class="error">' . __('Please insert a destination', 'google_routeplaner') . '</p>';
						}
						?>	
						
						<p><label for="google_routeplaner_alt_destination" class="formbold"><?php _e('Display Destination', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_alt_destination" id="google_routeplaner_alt_destination" style="width: 300px;" value="<?php echo $sett['alt_destination']; ?>" /><br />
						<i><?php _e('If you want a different name to show as title for the destination.', 'google_routeplaner'); ?></i></p>
						
						<p><label for="google_routeplaner_map_width" class="formbold"><?php _e('Map width', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_map_width" id="google_routeplaner_map_width" style="width: 70px;" value="<?php echo $sett['map_width']; ?>" />
						<select name="google_routeplaner_map_width_unit" id="google_routeplaner_map_width_unit">
							<option value="px"<?php if('px' == $sett['map_width_unit']) { echo ' selected=""'; } ?>>px</option>
							<option value="%"<?php if('%' == $sett['map_width_unit']) { echo ' selected=""'; } ?>>%</option>
							<option value="em"<?php if('em' == $sett['map_width_unit']) { echo ' selected=""'; } ?>>em</option>
						</select>
						<br />
						<i><?php _e('Enter the width for the map.', 'google_routeplaner'); ?></i></p>
						<p><label for="google_routeplaner_map_height" class="formbold"><?php _e('Map heigth', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_map_height" id="google_routeplaner_map_height" style="width: 70px;" value="<?php echo $sett['map_height']; ?>" />
						<select name="google_routeplaner_map_height_unit" id="google_routeplaner_map_height_unit">
							<option value="px"<?php if('px' == $sett['map_height_unit']) { echo ' selected=""'; } ?>>px</option>
							<option value="%"<?php if('%' == $sett['map_height_unit']) { echo ' selected=""'; } ?>>%</option>
							<option value="em"<?php if('em' == $sett['map_height_unit']) { echo ' selected=""'; } ?>>em</option>
						</select>
						<br />
						<i><?php _e('Enter the height for the map.', 'google_routeplaner'); ?></i></p>
						
						<p><label for="google_routeplaner_zoom" class="formbold"><?php _e('Map Zoom', 'google_routeplaner'); ?></label><br />
						<select name="google_routeplaner_zoom" id="google_routeplaner_zoom">
							<?php
							$z_level = 0;
							while($z_level <= 18) {
								if($sett['zoom'] == $z_level) {
									echo '<option value="' . $z_level . '" selected="">' . $z_level . '</option>';
								} else {
									echo '<option value="' . $z_level . '">' . $z_level . '</option>';
								}
								$z_level++;
							}
							?>
							
						</select></p>
						
						
						<p><span class="formbold"><?php _e('Auto-Detect users location', 'google_routeplaner'); ?></span><br />
						<input type="radio" name="google_routeplaner_autofill" id="google_routeplaner_autofill_coords" value="1"<?php if(1 == intval($sett['autofill'])) { echo ' checked=""'; } ?> />
						<label for="google_routeplaner_autofill_coords"><?php _e('Get coordinates', 'google_routeplaner'); ?></label><br />
						
						<input type="radio" name="google_routeplaner_autofill" id="google_routeplaner_autofill_city" value="2"<?php if(2 == intval($sett['autofill'])) { echo ' checked=""'; } ?> />
						<label for="google_routeplaner_autofill_city"><?php _e('Get address', 'google_routeplaner'); ?></label><br />
						
						<input type="radio" name="google_routeplaner_autofill" id="google_routeplaner_autofill_no" value="0"<?php if(0 == intval($sett['autofill'])) { echo ' checked=""'; } ?> />
						<label for="google_routeplaner_autofill_no"><?php _e('No detection', 'google_routeplaner'); ?></label><br />
						<i><?php _e('This is based on HTML5 and will work best with mobile devices.', 'google_routeplaner'); ?></i></p>
						
						<p><label for="google_routeplaner_language" class="formbold"><?php _e('Language', 'google_routeplaner'); ?></label><br />
						<select name="google_routeplaner_language" id="google_routeplaner_language">
							<option value=""><?php _e('Default', 'google_routeplaner'); ?></option>
							<?php
							if(is_array($google_routeplaner_langs)) {
								foreach($google_routeplaner_langs as $code => $lang) {
									if($sett['language'] == $code) {
										echo '<option value="' . $code . '" selected="">' . $lang . '</option>';
									} else {
										echo '<option value="' . $code . '">' . $lang . '</option>';
									}
								
								}							
							}							
							?>						
							
						</select><br />
						<i><?php _e('This only effects the route output, not the interface!', 'google_routeplaner'); ?></i></p>
					</div>
				</div>
			
				<p style="clear: both;"><input type="submit" class="button-primary" value="<?php _e('Save changes', 'google_routeplaner'); ?>" />
				<input name="action" value="google_routeplaner_edit_route" type="hidden" />
				<input name="route_id" value="<?php echo $planer['plan_id']; ?>" type="hidden" /></p>
			</form>
		</div>
	</div>
<?php
	}
?>