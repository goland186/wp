<?php
	global $wpdb, $google_routeplaner_langs;
	
	if(!google_routeplaner_check_table()) {
		google_routeplaner_install();
	}
	
	/*
	 * Save Route
	 */
	if ('google_routeplaner_add_route' == $_POST['action'])
	{
		if('' !== $_POST['google_routeplaner_destination']) {
			
			if('' !== trim($_POST['google_routeplaner_title'])) {
				$map_name = $_POST['google_routeplaner_title'];
			} else {
				$map_name = $_POST['google_routeplaner_destination'];
			}
		
		
			$wpdb->query("INSERT INTO " . $wpdb->prefix . "g_routeplanner_plans (`plan_title`)
			VALUES (
			'" . $map_name . "')");
			
			$map_id = mysql_insert_id();
			
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
			
		if(0 == $map_id) {
			google_routeplaner_install();
		?>
			<div class="wrap google_routeplaner">
				<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Add Route', 'google_routeplaner'); ?></h2>
				<p class="error">
				<?php _e('There has been a problem with installing the plugin. The plugin tried to fix this, please try again.', 'google_routeplaner'); ?><br />
				<?php _e('If the error still appears, please deactivete the plugin, activate it again and try to add a route then.', 'google_routeplaner'); ?></p>
				<p><a href="admin.php?page=google_routeplaner_routes" class="button"><?php _e('Back to overview', 'google_routeplaner'); ?></a></p>
			</div>

		<?php
			} else {		
		?>	  
			<div class="wrap google_routeplaner">
			<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> &bull; <?php _e('Add Route', 'google_routeplaner'); ?></h2>
			<p class="success"><?php _e('Your route has been saved!', 'google_routeplaner'); ?><br />
			<?php _e('The code for this route is: ', 'google_routeplaner'); ?> [googlerouteplaner=<?php echo $map_id; ?>]</p>
			<p><a href="admin.php?page=google_routeplaner_routes" class="button"><?php _e('Back to overview', 'google_routeplaner'); ?></a></p>
			</div>
		<?php
			}
		} else {
			$gr_error['google_routeplaner_destination'] = true;
		}
	}
	if ('google_routeplaner_add_route' !== $_POST['action'] || isset($gr_error)) {
	/*
	 * Output Form
	 */

?>

	<div class="wrap google_routeplaner">
		<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> &bull; <?php _e('Add Route', 'google_routeplaner'); ?></h2>
	   	<div id="poststuff"> 
			<form method="post" action="">
				<div class="postbox" style="clear: both;">
					<h3><?php _e('Map title', 'google_routeplaner'); ?></h3>
					<div class="inside">
						<p><input type="text" name="google_routeplaner_title" id="google_routeplaner_title" style="width: 450px;" value="<?php _e('New map', 'google_routeplaner'); ?>" /><br />
						<i><?php _e('Empty to use destination as title.', 'google_routeplaner'); ?></i></p>
					</div>
				</div>
				<div class="postbox" style="width: 48%; float: right;">
					<h3><?php _e('Detail settings', 'google_routeplaner'); ?></h3>
					<div class="inside">
						<p><label for="google_routeplaner_map_type" class="formbold"><?php _e('Map type', 'google_routeplaner'); ?></label><br />
						<select name="google_routeplaner_map_type" id="google_routeplaner_map_type">
							<?php $google_routeplaner_map_type = get_option("google_routeplaner_map_type"); ?>
							<option value="ROADMAP" selected="selected"><?php _e('Road map', 'google_routeplaner'); ?></option>
							<option value="SATELLITE"><?php _e('Satellite map', 'google_routeplaner'); ?></option>
							<option value="HYBRID"><?php _e('Hybrid map', 'google_routeplaner'); ?></option>
							<option value="TERRAIN"><?php _e('Physical map', 'google_routeplaner'); ?></option>	
						</select><br />
						<i><?php _e('Select how the map should look like.', 'google_routeplaner'); ?></i></p>
						
						<p><span class="formbold"><?php _e('Map Zoom Control', 'google_routeplaner'); ?></span><br />
						<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_large" value="DEFAULT" checked="checked" />
						<label for="google_routeplaner_zoom_control_large"><?php _e('Default', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_small" value="SMALL" />
						<label for="google_routeplaner_zoom_control_small"><?php _e('Small', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_zoom" value="LARGE" />
						<label for="google_routeplaner_zoom_control_zoom"><?php _e('Large', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_zoom_control" id="google_routeplaner_zoom_control_none" value="NONE" />
						<label for="google_routeplaner_zoom_control_none"><?php _e('None', 'google_routeplaner'); ?></label><br />
						<i><?php _e('Select the type of zoom and direction control you want.', 'google_routeplaner'); ?></i></p>
						
						<p><span class="formbold"><?php _e('Pan Control', 'google_routeplaner'); ?></span><br />
						<input type="radio" name="google_routeplaner_pan_control" id="google_routeplaner_pan_control_true" value="true" checked="checked" />
						<label for="google_routeplaner_pan_control_true"><?php _e('Yes', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_pan_control" id="google_routeplaner_pan_control_false" value="false" />
						<label for="google_routeplaner_pan_control_false"><?php _e('No', 'google_routeplaner'); ?></label><br />
						<i><?php _e('Enable or disable the pan control.', 'google_routeplaner'); ?></i></p>
						
						<p><span class="formbold"><?php _e('Map Type Control', 'google_routeplaner'); ?></span><br />
						
						<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_default" value="DEFAULT" checked="checked" />
						<label for="google_routeplaner_type_control_default"><?php _e('Default', 'google_routeplaner'); ?></label><br />						
						<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_standard" value="HORIZONTAL_BAR" />
						<label for="google_routeplaner_type_control_standard"><?php _e('Normal', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_hierachical" value="DROPDOWN_MENU" />
						<label for="google_routeplaner_type_control_hierachical"><?php _e('Hierarchical', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_type_control" id="google_routeplaner_type_control_none" value="NONE" />
						<label for="google_routeplaner_type_control_none"><?php _e('None', 'google_routeplaner'); ?></label><br />
						<i><?php _e('Select if users can change the map type.', 'google_routeplaner'); ?></i></p>
					</div>
				</div>
				
				<div class="postbox" style="width: 48%; float: left;">
					<h3><?php _e('General settings', 'google_routeplaner'); ?></h3>
					<div class="inside">

						<p><label for="google_routeplaner_destination" class="formbold"><?php _e('Destination', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_destination" id="google_routeplaner_destination" style="width: 300px;" value="" /><br />
						<i><?php _e('Add a search string for the destination. Adress, company name or something like this.', 'google_routeplaner'); ?></i></p>
						<?php
						if($gr_error['google_routeplaner_destination']) {
							echo '<p class="error">' . __('Please insert a destination', 'google_routeplaner') . '</p>';
						}
						?>						
						
						<p><label for="google_routeplaner_alt_destination" class="formbold"><?php _e('Display Destination', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_alt_destination" id="google_routeplaner_alt_destination" style="width: 300px;" value="" /><br />
						<i><?php _e('If you want a different name to show as title for the destination.', 'google_routeplaner'); ?></i></p>
						
						<p><label for="google_routeplaner_map_width" class="formbold"><?php _e('Map width', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_map_width" id="google_routeplaner_map_width" style="width: 70px;" value="500" />
						<select name="google_routeplaner_map_width_unit" id="google_routeplaner_map_width_unit">
							<option value="px">px</option>
							<option value="%">%</option>
							<option value="em">em</option>
						</select>
						<br />
						<i><?php _e('Enter the width for the map.', 'google_routeplaner'); ?></i></p>
						<p><label for="google_routeplaner_map_height" class="formbold"><?php _e('Map heigth', 'google_routeplaner'); ?></label><br />
						<input type="text" name="google_routeplaner_map_height" id="google_routeplaner_map_height" style="width: 70px;" value="400" />
						<select name="google_routeplaner_map_height_unit" id="google_routeplaner_map_height_unit">
							<option value="px">px</option>
							<option value="%">%</option>
							<option value="em">em</option>
						</select>
						<br />
						<i><?php _e('Enter the height for the map.', 'google_routeplaner'); ?></i></p>
						
						<p><label for="google_routeplaner_zoom" class="formbold"><?php _e('Map Zoom', 'google_routeplaner'); ?></label><br />
						<select name="google_routeplaner_zoom" id="google_routeplaner_zoom">
							<?php
							$z_level = 0;
							while($z_level <= 18) {
								if(8 == $z_level) {
									echo '<option value="' . $z_level . '" selected="">' . $z_level . '</option>';
								} else {
									echo '<option value="' . $z_level . '">' . $z_level . '</option>';
								}
								$z_level++;
							}
							?>
							
						</select></p>		
						
						<p><span class="formbold"><?php _e('Auto-Detect users location', 'google_routeplaner'); ?></span><br />
						<input type="radio" name="google_routeplaner_autofill" id="google_routeplaner_autofill_coords" value="1" />
						<label for="google_routeplaner_autofill_coords"><?php _e('Get coordinates', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_autofill" id="google_routeplaner_autofill_city" value="2" />
						<label for="google_routeplaner_autofill_city"><?php _e('Get address', 'google_routeplaner'); ?></label><br />
						<input type="radio" name="google_routeplaner_autofill" id="google_routeplaner_autofill_no" value="0" checked="checked" />
						<label for="google_routeplaner_autofill_no"><?php _e('No detection', 'google_routeplaner'); ?></label><br />
						<i><?php _e('This is based on HTML5 and will work best with mobile devices.', 'google_routeplaner'); ?></i></p>
						
						<p><label for="google_routeplaner_language" class="formbold"><?php _e('Language', 'google_routeplaner'); ?></label><br />
						<select name="google_routeplaner_language" id="google_routeplaner_language">
							<option value=""><?php _e('Default', 'google_routeplaner'); ?></option>
							<?php
							if(is_array($google_routeplaner_langs)) {
								foreach($google_routeplaner_langs as $code => $lang) {
									echo '<option value="' . $code . '">' . $lang . '</option>';
								}							
							}							
							?>	
						</select><br />
						<i><?php _e('This only effects the route output, not the interface!', 'google_routeplaner'); ?></i></p>
					</div>
				</div>

				<p style="clear: both;"><input type="submit" class="button-primary" value="<?php _e('Save route', 'google_routeplaner'); ?>" />
				<input name="action" value="google_routeplaner_add_route" type="hidden" /></p>
		   </form>
		</div>
	</div>
<?php
	}
?>