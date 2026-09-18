 <div class="wrap google_routeplaner">
   <div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Import old database', 'google_routeplaner'); ?></h2>
	<?php
	
	if(!google_routeplaner_check_table()) {
		google_routeplaner_install();
	}
	
	$old_maps = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "google_routeplaner ORDER BY planer_id", ARRAY_A); 
	

	if ('google_routeplaner_delete_old_db' == $_POST['action']) {
		if('y' == $_POST['confirm_delete']) {
		
			$wpdb->query(sprintf('DROP TABLE `' . $wpdb->prefix . 'google_routeplaner`'));
			
			echo '<p class="success">' . __('Old database deleted.', 'google_routeplaner') . '<br />
			<a href="admin.php?page=google-routeplaner.php">' . __('Back to main page', 'google_routeplaner') . '</a></p>';
		
		} else {
			echo '<p class="error">' . __('Please use the checkbox to confirm you want to delete the old database.', 'google_routeplaner') . '</p>';
		}
		
	}
	if ('google_routeplaner_import_database' == $_POST['action']) {
		if('y' == $_POST['import_confirm']) {
			if(is_array($old_maps)) {
				foreach($old_maps as $old_map) {
				
					/*
					 * Delete database entries with the same ID
					 */
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "g_routeplanner_plans
						WHERE plan_id = '" . $old_map['planer_id'] . "'
						LIMIT 1");
						
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "g_routeplanner_set
						WHERE set_plan = '" . $old_map['planer_id'] . "'");
						
					$wpdb->query("INSERT INTO " . $wpdb->prefix . "g_routeplanner_plans (`plan_id`, `plan_title`)
						VALUES (
						'" . $old_map['planer_id'] . "',
						'" . $old_map['start_location'] . "'
						)");
					
					$insert = "(
						'" . $old_map['planer_id'] . "',
						'destination',
						'" . $old_map['start_location'] . "'
						)";
					
					if('' == trim($old_map['planer_width']) || !isset($old_map['planer_width'])) {
						$old_map['planer_width'] = 500;
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'map_width',
						'" . $old_map['planer_width'] . "'
						)";
						
					if('' == trim($old_map['planer_width_unit']) || !isset($old_map['planer_width_unit'])) {
						$old_map['planer_width_unit'] = 'px';
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'map_width_unit',
						'" . $old_map['planer_width_unit'] . "'
						)";
						
					if('' == trim($old_map['planer_height']) || !isset($old_map['planer_height'])) {
						$old_map['planer_height'] = '300';
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'map_height',
						'" . $old_map['planer_height'] . "'
						)";
						
					if('' == trim($old_map['planer_height_unit']) || !isset($old_map['planer_height_unit'])) {
						$old_map['planer_height_unit'] = 'px';
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'map_height_unit',
						'" . $old_map['planer_height_unit'] . "'
						)";
						
					if('' == trim($old_map['planer_zoom']) || !isset($old_map['planer_zoom'])) {
						$old_map['planer_zoom'] = 8;
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'zoom',
						'" . $old_map['planer_zoom'] . "'
						)";
						
					if('' == trim($old_map['planer_type']) || !isset($old_map['planer_type'])) {
						$old_map['planer_type'] = 'ROADMAP';
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'map_type',
						'" . $old_map['planer_type'] . "'
						)";
						
					if('' == trim($old_map['planer_zoom_control']) || !isset($old_map['planer_zoom_control'])) {
						$old_map['planer_zoom_control'] = 'DEFAULT';
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'zoom_control',
						'" . $old_map['planer_zoom_control'] . "'
						)";
						
					if('' == trim($old_map['planer_type_control']) || !isset($old_map['planer_type_control'])) {
						$old_map['planer_type_control'] = 'DEFAULT';
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'type_control',
						'" . $old_map['planer_type_control'] . "'
						)";
						
					if('' == trim($old_map['planer_autofill']) || !isset($old_map['planer_autofill'])) {
						$old_map['planer_autofill'] = 0;
					}
					
					$insert .= ", (
						'" . $old_map['planer_id'] . "',
						'autofill',
						'" . $old_map['planer_autofill'] . "'
						)";
						
					if('' == trim($old_map['planer_language']) || !isset($old_map['planer_language'])) {
						$insert .= ", (
							'" . $old_map['planer_id'] . "',
							'language',
							'" . $old_map['planer_language'] . "'
							)";
					}
					
					if($wpdb->query("INSERT INTO " . $wpdb->prefix . "g_routeplanner_set (`set_plan`, `set_name`, `set_value`)
						VALUES " . $insert)) {
				
						echo '<p class="success">' . sprintf(__('Map ID %s to %s has been successfully imported!', 'google_routeplaner'), $old_map['planer_id'], $old_map['start_location']) . '</p>';
					
					} else {
						echo '<p class="error">' . sprintf(__('Map ID %s to %s could not be imported', 'google_routeplaner'), $old_map['planer_id'], $old_map['start_location']) . '</p>';
					}
				}
				echo '<p class="success">' . __('You can now delete the old database.', 'google_routeplaner') . '</p>';
			}
			
		} else {
			echo '<p class="error">' . __('You need to confirm that you use the import on your own risk.', 'google_routeplaner') . '</p>';
		}
	}
	?>
	<div id="poststuff">
		<div class="postbox" style="width: 48%; float: right;">
			<h3><?php _e('Delete old database', 'google_routeplaner'); ?></h3>
			<div class="inside">
				<?php
				echo '<form method="post" action="">
				<p>' . __('After you imported the old database or if you do not want to import your old maps at all you can delete the old database.', 'google_routeplaner') . '</p>
				<p><input type="checkbox" name="confirm_delete" id="confirm_delete" value="y" /> ' . __('Yes, I want to delete the old database!', 'google_routeplaner') . '</p>
				<p><input type="submit" name="delete_old_db" class="button" value="' . __('Delete old database', 'google_routeplaner') . '" />
				<input name="action" value="google_routeplaner_delete_old_db" type="hidden" /></p>
				</form>';
			
				?>
			</div>	
		</div>
	
		<div class="postbox" style="width: 48%; float: left;">
			<h3><?php _e('Import old database', 'google_routeplaner'); ?></h3>
			<div class="inside">
				<?php
				
				if(is_array($old_maps) && !empty($old_maps)) {
					echo '<h4>' . __('Old maps found', 'google_routeplaner') . '</h4>
					<form action="" method="post">
					<ul>';
					foreach($old_maps as $old_map) {
						echo '<li>' . sprintf(__('Map ID %s to %s', 'google_routeplaner'), $old_map['planer_id'], $old_map['start_location']) . '</li>';
					
					}
					echo '</ul>
					
					<p><strong>' . __('Warning!', 'google_routeplaner') . '</strong><br />
					' . __('To keep links to maps alive the importer will use the old map IDs.', 'google_routeplaner') . ' ' .
					__('If you created new maps before importing these map may will be overwritten by the importer!', 'google_routeplaner') . '</p>
					<p><input type="checkbox" name="import_confirm" id="import_confirm" value="y" /> ' . __('Yes, I want to continue on my own risk!', 'google_routeplaner') . '</p>
					<p><input type="submit" name="import_db" class="button-primary" value="' . __('Import old database', 'google_routeplaner') . '" />
					<input name="action" value="google_routeplaner_import_database" type="hidden" /></p>
										
					</form>';
					
				} else {
					echo '<p class="error">The old database seems to be empty. You can simply delete it!</p>';
				}
				
				?>
			</div>	
		</div>
	</div>
</div>