<?php
/*
Plugin Name: Google Routeplanner
Plugin URI: http://wordpress.org/plugins/google-routeplaner/
Description: Allows you to add one or more route planners based on Google Maps to help your users to find a specific place. 
Version: 4.0
Author: Abandoned
Author URI: http://wordpress.org/plugins/google-routeplaner/
Min WP Version: 3.2
*/



/*
 * Load Language
 */
load_plugin_textdomain('google_routeplaner', WP_PLUGIN_DIR . '/google-routeplaner/', dirname(plugin_basename(__FILE__)) . '/languages/');

require_once(WP_PLUGIN_DIR . '/google-routeplaner/google-routeplaner-lang-config.php');

$template_dir = get_template_directory();
if(file_exists($template_dir . '/google-routeplaner-translations.php')) {
	require_once($template_dir . '/google-routeplaner-translations.php');
} else {
	require_once(WP_PLUGIN_DIR . '/google-routeplaner/google-routeplaner-translations.php');
}

/*
 * Install the needed database table
 */
 
function google_routeplaner_check_table() {
	global $wpdb;
	
	$tables = $wpdb->get_col('SHOW TABLES');

	if (in_array($wpdb->prefix . 'g_routeplanner_plans', $tables) && in_array($wpdb->prefix . 'g_routeplanner_set', $tables)) {
		return true;
	} else {
		return false;
	}
}

function google_routeplaner_install() {
	global $wpdb;

	$isNetwork = ($_SERVER['SCRIPT_NAME'] == '/wp-admin/network/plugins.php')?true:false;
	
	if (function_exists('is_multisite') && is_multisite()) {
		if ($isNetwork) {
			
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM " . $wpdb->blogs));
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				google_routeplaner_install_create();
			}
			switch_to_blog($old_blog);
			return;
		}	
	}
	google_routeplaner_install_create();
}
 
function google_routeplaner_install_create() {
	global $wpdb;
	
	if(!google_routeplaner_check_table()) {
		$charset_collate = '';
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if (!empty($wpdb->charset)) {
				$charset_collate .= sprintf(' DEFAULT CHARACTER SET %s', $wpdb->charset);
			}
			if (!empty($wpdb->collate)) {
				$charset_collate .= ' COLLATE ' . $wpdb->collate;
			}
		}

		$sql_routes = 'CREATE TABLE `' . $wpdb->prefix . 'g_routeplanner_plans` (
					 `plan_id` INT NOT NULL AUTO_INCREMENT,
					 `plan_title` VARCHAR(120) NOT NULL,
					 PRIMARY KEY (`plan_id`)
					 )%s';
		$wpdb->query(sprintf($sql_routes, $charset_collate));
		
		$sql_settings = 'CREATE TABLE `' . $wpdb->prefix . 'g_routeplanner_set` (
					 `set_id` INT NOT NULL AUTO_INCREMENT,
					 `set_plan` INT NOT NULL,
					 `set_name` VARCHAR(120) NOT NULL,
					 `set_value` TEXT NOT NULL,
					 PRIMARY KEY (`set_id`)
					 )%s';
		$wpdb->query(sprintf($sql_settings, $charset_collate));
		
		update_option("google_routeplaner_version", '3.6');
		
	} else {
		google_routeplaner_update();	
	}
}

function google_routeplaner_update($force_update = false) {
	global $wpdb;

	$gr_version = get_option("google_routeplaner_version");
	if('' == $gr_version) {
		$gr_version = 0;
	}
	
	if(floatval($gr_version) < 2.5 || $force_update) {
		/* 
		 * Update old information in the database for version 2
		 */
		$wpdb->query('ALTER TABLE  `' . $wpdb->prefix . 'google_routeplaner` ADD  `planer_zoom` INT NOT NULL DEFAULT  \'8\' AFTER  `planer_height`');
			
		/* 
		 * Update old information in the database for version 2.2
		 */
		$cur_donate = get_option('google_routeplaner_donate');
		if('link' == $cur_donate) {
			update_option("google_routeplaner_donate", 'personal_link');
		} elseif('articel' == $cur_donate) {
			update_option("google_routeplaner_donate", 'personal_no_link');
		} elseif('paypal' == $cur_donate) {
			update_option("google_routeplaner_donate", 'commercial_paypal');
		} elseif('none' == $cur_donate) {
			update_option("google_routeplaner_donate", 'personal_no_link');
		}
		
		/* 
		 * Update old information in the database for version 2.5
		 */
		$wpdb->query('ALTER TABLE  `' . $wpdb->prefix . 'google_routeplaner` ADD  `planer_width_unit` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  \'px\' AFTER  `planer_width`');
		$wpdb->query('ALTER TABLE  `' . $wpdb->prefix . 'google_routeplaner` ADD  `planer_height_unit` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  \'px\' AFTER  `planer_height`');
		$wpdb->query('ALTER TABLE  `' . $wpdb->prefix . 'google_routeplaner` ADD  `planer_autofill` INT NOT NULL DEFAULT  \'0\' AFTER  `planer_type_control`');
	}
	
	if(floatval($gr_version) < 3.0 || $force_update) {
		/* 
		 * Update old information in the database for version 3.0
		 */
		$old_donate_option = get_option("google_routeplaner_donate");
		if('personal_link' == $old_donate_option || 'commercial_link' == $old_donate_option) {
			update_option("google_routeplaner_donate", 'show_link');
		} elseif('personal_no_link' == $old_donate_option || 'personal_paypal' == $old_donate_option || 'commercial_paypal' == $old_donate_option) {
			update_option("google_routeplaner_donate", 'no_link');
		}
		
		update_option("google_routeplaner_version", '3.0');
	}
	
	if(floatval($gr_version) < 3.1 || $force_update) {
		/* 
		 * Update old information in the database for version 3.1
		 */
		$wpdb->query('ALTER TABLE  `' . $wpdb->prefix . 'google_routeplaner` CHANGE `planer_language`  `planer_language` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
	
		update_option("google_routeplaner_version", '3.1');
	}
	delete_option('google_routeplaner_donate');
	
	update_option("google_routeplaner_version", '4.0');
}

if ( function_exists('register_update_hook') )
	register_update_hook(WP_PLUGIN_DIR . '/google-routeplaner/google-routeplaner.php', 'google_routeplaner_update');

/*
 * Install plugin
 */
if ( function_exists('register_activation_hook') )
    register_activation_hook(WP_PLUGIN_DIR . '/google-routeplaner/google-routeplaner.php', 'google_routeplaner_install');

/*
 * Uninstall plugin
 */	
function google_routeplaner_uninstall() {
	global $wpdb;
	
	if (function_exists('is_multisite') && is_multisite()) {
		$old_blog = $wpdb->blogid;
		// Get all blog ids
		$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
		foreach ($blogids as $blog_id) {
			switch_to_blog($blog_id);
			google_routeplaner_uninstall_delete();
		}
		switch_to_blog($old_blog);
		return;	
	} 
	google_routeplaner_uninstall_delete();	
}

function google_routeplaner_uninstall_delete() {
	global $wpdb;
	
	delete_option('google_routeplaner_api_key');
	delete_option('google_routeplaner_donate');
	delete_option('google_routeplaner_language');
	delete_option('google_routeplaner_viewport');	
	
	$wpdb->query(sprintf('DROP TABLE `' . $wpdb->prefix . 'g_routeplanner_plans`'));
	$wpdb->query(sprintf('DROP TABLE `' . $wpdb->prefix . 'g_routeplanner_set`'));
	
	/*
	 * Delete old database too!
	 */ 
	$wpdb->query(sprintf('DROP TABLE `' . $wpdb->prefix . 'google_routeplaner`'));

}

if ( function_exists('register_uninstall_hook') )
    register_uninstall_hook(__FILE__, 'google_routeplaner_uninstall');


/*
 * Add new Multisite Blog
 */	
add_action( 'wpmu_new_blog', 'google_routeplaner_new_blog', 10, 6); 		
 
function google_routeplaner_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;
 
	if (is_plugin_active_for_network('google-routeplaner/google-routeplaner.php')) {
		$old_blog = $wpdb->blogid;
		switch_to_blog($blog_id);
		google_routeplaner_install_create();
		switch_to_blog($old_blog);
	}
}

/*
 * Search for plugin code and replace
 */	
function google_routeplaner_output($data) {
	if(!preg_match_all("/\[googlerouteplaner=([0-9]*)\]/", $data, $matches)) {
		return $data;
	} else {
		foreach($matches[1] as $match) {
			$map = google_routeplaner_build_map($match);
			$data = str_replace("[googlerouteplaner=" . $match . "]", $map, $data);
		}
		return $data;
	}
}

/*
 * Output map
 */	
function google_routeplaner_build_map($route_id) {
	global $wpdb, $google_routeplaner_trans;

	$planer = $wpdb->get_row("SELECT * 
		FROM " . $wpdb->prefix . "g_routeplanner_plans 
		WHERE plan_id='" . $route_id . "' 
		LIMIT 1", ARRAY_A);
	
	$planer_settings = $wpdb->get_results("SELECT * 
		FROM " . $wpdb->prefix . "g_routeplanner_set 
		WHERE set_plan='" . $route_id . "'", ARRAY_A);
		
	if(is_array($planer_settings)) {
		foreach($planer_settings as $setting) {
			if('' !== $setting['set_value']) {
				$sett[$setting['set_name']] = $setting['set_value'];
			}
		}		
	}
		
	$map = '	
	<!-- Start Google Routeplanner Plugin Output -->' . "\n";
	
	$map .= '<script type="text/javascript">';
	if(is_array($sett)) {
		foreach($sett as $key => $value) {
			if(is_numeric ($value)) {
				$map .= 'var ' . $key . '_' . $planer['plan_id'] . ' = ' . $value . ';' . "\n";
			} else {
				$map .= 'var ' . $key . '_' . $planer['plan_id'] . ' = \'' . $value . '\';' . "\n";
			}
		}
	}
	$map .= '</script>';

	if(1 < strlen($sett['language'])) {	
		$used_lang = $sett['language'];
		
		if(isset($google_routeplaner_trans[$sett['language']])) {
			$label = $google_routeplaner_trans[$sett['language']]['label'];
			$button = $google_routeplaner_trans[$sett['language']]['button'];
		} else {
			$label = $google_routeplaner_trans['en']['label'];
			$button = $google_routeplaner_trans['en']['button'];
		}
		
	} else {
		$used_lang = get_option("google_routeplaner_language");

		$gr_config_lang = get_option("google_routeplaner_language");
		if(isset($google_routeplaner_trans[$gr_config_lang])) {
			$label = $google_routeplaner_trans[$gr_config_lang]['label'];
			$button = $google_routeplaner_trans[$gr_config_lang]['button'];
		} else {
			$label = $google_routeplaner_trans['en']['label'];
			$button = $google_routeplaner_trans['en']['button'];
		}
	
	}
	
	/*
	 * Use API Key or not
	 */
	$api_key = trim(get_option("google_routeplaner_api_key"));
	if('' !== $api_key) {
		$map .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?key=' . $api_key . '&amp;sensor=false&amp;language=' . $used_lang . '"></script>' . "\n";
	} else {
		$map .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&amp;language=' . $used_lang . '"></script>' . "\n";
	}

	$map .= '<script type="text/javascript" src="' . WP_PLUGIN_URL . '/google-routeplaner/google-routeplaner-main-js.php?plan_id=' . $planer['plan_id'] . '"></script>' . "\n";
	
	$map .= 
	'<form action="#" onsubmit="calcRoute' . $planer['plan_id'] . '(); return false">
	 <div id="map_controls' . $planer['plan_id'] . '" class="google_map_controls"><label for="fromAddress' . $planer['plan_id'] . '">' . $label . '</label> <input type="text" size="25" id="fromAddress' . $planer['plan_id'] . '" name="from" value=""/><input name="calc" type="submit" value="' . $button . '" /></div>
	 <div id="map_canvas' . $planer['plan_id'] . '" class="google_map_canvas" style="overflow: hidden; width: ' . $sett['map_width'] . $sett['map_width_unit'] . '; height: ' . $sett['map_height'] . $sett['map_height_unit'] . ';"></div>
	 <div id="map_directions' . $planer['plan_id'] . '" class="google_map_directions"></div>
	</form>' . "\n";
	
	$map .= '<script type="text/javascript" src="' . WP_PLUGIN_URL . '/google-routeplaner/google-routeplaner-js.php?plan_id=' . $planer['plan_id'] . '&autofill=' . $sett['autofill'] . '"></script>' . "\n";
	
	$map .= '<!-- End Google Routeplanner Plugin Output --><p>' . "\n";

	return $map;
}


/*
 * Content Filter
 */	
if( function_exists('add_filter') ) {
	add_filter('the_content', 'google_routeplaner_output'); 
}


function google_routeplaner_admin_head() {
	if('yes' == get_option("google_routeplaner_viewport")) {
		echo '<meta name="viewport" content="initial-scale=1.0, user-scalable=yes" />';
	}	
	echo '
	<style type="text/css" media="screen">
	@import url("' . WP_PLUGIN_URL . '/google-routeplaner/google-routeplaner.css");
	</style>';
}
add_action('admin_head', 'google_routeplaner_admin_head');

function google_routeplaner_head() {
	if('yes' == get_option("google_routeplaner_viewport")) {
		echo '<meta name="viewport" content="initial-scale=1.0, user-scalable=yes" />';
	}
}
add_action('wp_head', 'google_routeplaner_head');

/*
 * Include pages
 */	
require_once(WP_PLUGIN_DIR . '/google-routeplaner/google-routeplaner-pages.php');


?>