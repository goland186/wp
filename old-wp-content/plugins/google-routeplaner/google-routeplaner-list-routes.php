<?php
	global $wpdb;

	$planers = $wpdb->get_results("SELECT * 
		FROM " . $wpdb->prefix . "g_routeplanner_plans 
		ORDER BY plan_id", ARRAY_A); 
?>
  <div class="wrap google_routeplaner">
	<script type="text/javascript">
	function SelectAll(id)
	{
		document.getElementById(id).focus();
		document.getElementById(id).select();
	}
	function deleteroute(url) {
		if (confirm('<?php _e('Really delete this route?', 'google_routeplaner'); ?>')) {
			window.location = url;
		}
	}
	</script>
   <div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Routes', 'google_routeplaner'); ?></h2>
   <p><a href="admin.php?page=google_routeplaner_routes&amp;routeplaner_action=google_routeplaner_add_route" class="button-primary"><?php _e('Create Route', 'google_routeplaner'); ?></a></p>
   <table class="widefat" cellspacing="0" cellpadding="0">
	<thead>
    <tr class="column-cb">
	 <th><?php _e('ID', 'google_routeplaner'); ?></th>
	 <th><?php _e('Name', 'google_routeplaner'); ?></th>
	 <th><?php _e('Code', 'google_routeplaner'); ?></th>
	 <th><?php _e('Actions', 'google_routeplaner'); ?></th>
	</tr>
   </thead>
   <tfoot>
    <tr class="column-cb">
	 <th><?php _e('ID', 'google_routeplaner'); ?></th>
	 <th><?php _e('Name', 'google_routeplaner'); ?></th>
	 <th><?php _e('Code', 'google_routeplaner'); ?></th>
	 <th><?php _e('Actions', 'google_routeplaner'); ?></th>
	</tr>
   </tfoot>
   
   <?php
	if(is_array($planers) && !empty($planers)) {
		$rownum = 1;
		foreach($planers as $planer) {

			if($rownum&1) { echo '<tr class="even">'; } else { echo '<tr class="odd">'; }
			$rownum++;
			
			echo '<td>' . $planer['plan_id'] . '</td>
			<td>' . $planer['plan_title'] . '</td>
			<td><input type="text" id="planercode_' . $planer['plan_id'] . '" onClick="SelectAll(\'planercode_' . $planer['plan_id'] . '\');" class="routecode" value="[googlerouteplaner=' . $planer['plan_id'] . ']" /></td>
			<td>
			<a href="admin.php?page=google_routeplaner_routes&amp;routeplaner_action=preview_route&amp;route_id=' . $planer['plan_id'] . '" class="button gr_preview">' . __('preview', 'google_routeplaner') . '</a>
			<a href="admin.php?page=google_routeplaner_routes&amp;routeplaner_action=edit_route&amp;route_id=' . $planer['plan_id'] . '" class="button gr_edit">' . __('edit', 'google_routeplaner') . '</a>
			<a onclick="deleteroute(\'admin.php?page=google_routeplaner_routes&amp;routeplaner_action=delete_route&amp;route_id=' . $planer['plan_id'] . '\');" class="button gr_delete">' . __('delete', 'google_routeplaner') . '</a>
			</td>
			</tr>' . "\n";
		}
	} else {
		echo '<tr>
		<td colspan="4">' . __('You have not created any routes yet!', 'google_routeplaner') . '</td>
		</tr>';
	}
   ?>
   </table>
   </div>