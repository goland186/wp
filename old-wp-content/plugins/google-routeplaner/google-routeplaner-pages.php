<?php

/* 
 * GENERATE PAGES
 */

/*
 * About Page
 */
function google_routeplaner_about_page() {
	global $wpdb, $import_menu;
	
	include 'google-routeplaner-main-page.php';
}

/* 
 * List of routes
 */
function google_routeplaner_routes_page() {
	switch($_GET['routeplaner_action']) {
		case 'google_routeplaner_add_route':
			google_routeplaner_add_route();
			break;
		case 'delete_route':
			google_routeplaner_delete_route($_GET['route_id']);
			break;
		case 'edit_route':
			google_routeplaner_edit_route($_GET['route_id']);
			break;
		case 'preview_route':
			google_routeplaner_preview_route($_GET['route_id']);
			break;
		default:
			google_routeplaner_list_routes();
			break;
	}
}

/*
 * Settings Page
 */
function google_routeplaner_option_page() {
	global $google_routeplaner_langs;
	include 'google-routeplaner-settings-page.php';
}


/*
 * List Routes
 */
function google_routeplaner_list_routes() {
	include 'google-routeplaner-list-routes.php';
}

/*
 * Import old database
 */
function google_routeplaner_import_page() {
	global $wpdb;
	include 'google-routeplaner-import-page.php';
}

/*
 * Delete Route
 */
function google_routeplaner_delete_route($route_id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "g_routeplanner_plans 
	WHERE plan_id='" . $route_id . "' LIMIT 1");
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "g_routeplanner_set 
	WHERE set_plan='" . $route_id . "'");
	
	?>
	<div class="wrap google_routeplaner">
    <div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> V<?php echo get_option("google_routeplaner_version"); ?> &bull; <?php _e('Delete Route', 'google_routeplaner'); ?></h2>
	<p class="success"><?php _e('The route has been deleted!', 'google_routeplaner'); ?></p>
	<p><a href="admin.php?page=google_routeplaner_routes" class="button"><?php _e('Back to overview', 'google_routeplaner'); ?></a></p>
	</div>
	<?php
}

/*
 * Previw Route
 */
function google_routeplaner_preview_route($route_id) {
	?>
	<div class="wrap google_routeplaner">
	<div id="icon-google_routeplaner" class="icon32"><br /></div><h2><?php _e('Google Routeplanner', 'google_routeplaner'); ?> &bull; <?php _e('Preview Route', 'google_routeplaner'); ?></h2>
	<p><a href="admin.php?page=google_routeplaner_routes" class="button"><?php _e('Back to overview', 'google_routeplaner'); ?></a></p>
    <?php echo google_routeplaner_build_map($route_id); ?>
	</div>
	<?php
}


/*
 * Add Route Page
 */
function google_routeplaner_add_route() {
	include 'google-routeplaner-add-route.php';
}

/*
 * Edit Route Page
 */
function google_routeplaner_edit_route($route_id) {
	include 'google-routeplaner-edit-route.php';
}

/*
 * Documentation Page
 */
function google_routeplaner_doc_page() {
	include 'google-routeplaner-doc-page.php';
}

/*
 * Troubleshooting Page
 */
function google_routeplaner_trouble_page() {
	include 'google-routeplaner-trouble-page.php';
}

function my_admin_notice() {
	global $current_user ;
	$user_id = $current_user->ID;
	 /* Check that the user hasn't already clicked to ignore the message */

	 if ( ! get_user_meta($user_id, 'g_routeplanner_ignore_import') ) {
	 
    ?>
    <div class="updated gr_import">
		<div style="float: right; padding: 15px;"><a href="admin.php?page=google-routeplaner.php&g_routeplanner_ignore_import=0"><?php _e('Hide notice', 'google_routeplaner'); ?></a></div>
		<h3><?php _e('Import old database', 'google_routeplaner'); ?></h3>
		<p><?php _e('Since version 3.5 the plugin has a new database structure. An old database has been detected.', 'google_routeplaner'); ?> 
		<?php _e('We can import most data from older databases, depending on how old the database is.', 'google_routeplaner'); ?></p>
		<p><a href="admin.php?page=google_routeplaner_import" class="button-primary"><?php _e('Try import now', 'google_routeplaner'); ?></a></p>
    </div>
	
    <?php
	}
}

add_action('admin_init', 'g_routeplanner_ignore_import');

function g_routeplanner_ignore_import() {
    global $current_user;
    $user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset($_GET['g_routeplanner_ignore_import']) && '0' == $_GET['g_routeplanner_ignore_import'] ) {
		add_user_meta($user_id, 'g_routeplanner_ignore_import', 'true', true);
	}
}




/*
 * Adds the option page for admin menu
 */
function google_routeplaner_add_menu() {
	global $submenu, $wpdb, $import_menu;
	add_option("google_routeplaner_donate","show_link");	
	add_option("google_routeplaner_language","en");	
	add_option("google_routeplaner_version","1.0");	
	add_option("google_routeplaner_viewport","yes");	
	add_option("google_routeplaner_api_key","");	
	
	add_action( 'admin_menu' , 'admin_menu_new_items' );

	add_menu_page(__('Overview', 'google_routeplaner'), __('Routeplaner', 'google_routeplaner'), 8, 'google-routeplaner.php', 'google_routeplaner_about_page', WP_PLUGIN_URL . '/google-routeplaner/images/routeplanner_icon16.png');
	add_submenu_page('google-routeplaner.php', __('Routes', 'google_routeplaner'), __('Routes', 'google_routeplaner'), 8, 'google_routeplaner_routes', 'google_routeplaner_routes_page');
	add_submenu_page('google-routeplaner.php', __('Settings', 'google_routeplaner'), __('Settings', 'google_routeplaner'), 8, 'google_routeplaner_settings', 'google_routeplaner_option_page');
	add_submenu_page('google-routeplaner.php', __('Documentation', 'google_routeplaner'), __('Documentation', 'google_routeplaner'), 8, 'google_routeplaner_doc', 'google_routeplaner_doc_page');
	add_submenu_page('google-routeplaner.php', __('Troubleshooting', 'google_routeplaner'), __('Troubleshooting', 'google_routeplaner'), 8, 'google_routeplaner_trouble', 'google_routeplaner_trouble_page');
	
	$tables = $wpdb->get_col('SHOW TABLES');
	if (in_array($wpdb->prefix . 'google_routeplaner', $tables)) {
	
		add_action( 'admin_notices', 'my_admin_notice' );
	
		$import_menu = true;
		add_submenu_page('google-routeplaner.php', __('Import', 'google_routeplaner'), __('Import', 'google_routeplaner'), 8, 'google_routeplaner_import', 'google_routeplaner_import_page');
	}

}

add_action('admin_menu', 'google_routeplaner_add_menu');
?>