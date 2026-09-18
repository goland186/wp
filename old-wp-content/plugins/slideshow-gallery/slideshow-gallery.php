<?php

/*
Plugin Name: Slideshow Gallery
Plugin URI: https://tribulant.com/plugins/view/13/wordpress-slideshow-gallery
Author: Tribulant Software
Author URI: https://tribulant.com
Description: Feature content in a JavaScript powered slideshow gallery showcase on your WordPress website. The slideshow is flexible and all aspects can easily be configured. Embedding or hardcoding the slideshow gallery is a breeze. To embed into a post/page, simply insert <code>[tribulant_slideshow]</code> into its content with an optional <code>post_id</code> parameter. To hardcode into any PHP file of your WordPress theme, simply use <code>&lt;?php if (function_exists('slideshow')) { slideshow($output = true, $post_id = false, $gallery_id = false, $params = array()); } ?&gt;</code>.
Version: 1.6.8
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: slideshow gallery, slideshow, gallery, slider, jquery, bfithumb, galleries, photos, images
Text Domain: slideshow-gallery
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

$path = dirname(__FILE__) . DS . 'slideshow-gallery-plugin.php';
if (file_exists($path)) {
	require_once(dirname(__FILE__) . DS . 'includes' . DS . 'checkinit.php');
	require_once(dirname(__FILE__) . DS . 'includes' . DS . 'constants.php');
	require_once($path);
	require_once(dirname(__FILE__) . DS . 'vendors' . DS . 'otf_regen_thumbs.php');
}

if (!class_exists('SlideshowGallery')) {
	class SlideshowGallery extends GalleryPlugin {
		
		function __construct() {		
			$url = explode("&", $_SERVER['REQUEST_URI']);
			$this -> url = $url[0];
			$this -> referer = (empty($_SERVER['HTTP_REFERER'])) ? $this -> url : $_SERVER['HTTP_REFERER'];
			$this -> plugin_name = basename(dirname(__FILE__));
			$this -> plugin_file = plugin_basename(__FILE__);	
			$this -> register_plugin($this -> plugin_name, __FILE__);
			
			//WordPress action hooks
			$this -> add_action('plugins_loaded');
			$this -> add_action('wp_head');
			$this -> add_action('wp_footer');
			$this -> add_action('admin_menu');
			$this -> add_action('admin_head');
			$this -> add_action('admin_notices');
			$this -> add_action('wp_print_styles', 'print_styles');
			$this -> add_action('admin_print_styles', 'print_styles');
			$this -> add_action('wp_print_scripts', 'print_scripts');
			$this -> add_action('admin_print_scripts', 'print_scripts');
			$this -> add_action('init', 'init_textdomain', 10, 1);
			$this -> add_action('admin_init', 'custom_redirect', 1, 1);
			
			//WordPress Ajax hooks
			$this -> add_action('wp_ajax_slideshow_slides_order', 'ajax_slides_order', 10, 1);
			$this -> add_action('wp_ajax_slideshow_tinymce', 'ajax_tinymce', 10, 1);
			
			//WordPress filter hooks
			$this -> add_filter('mce_buttons');
			$this -> add_filter('mce_external_plugins');
			$this -> add_filter("plugin_action_links_" . $this -> plugin_file, 'plugin_action_links', 10, 4);
			
			$this -> add_action('slideshow_ratereviewhook', 'ratereview_hook');
			
			if (!is_admin() || wp_doing_ajax()) { 
				add_shortcode('slideshow', array($this, 'embed')); 
				add_shortcode('tribulant_slideshow', array($this, 'embed'));
			}
			
			$this -> updating_plugin();
		}
		
		function excerpt_more($more = null) {			
			global $slideshow_post;
			$excerptsettings = $this -> get_option('excerptsettings');
			if (!empty($excerptsettings)) {
				$excerpt_readmore = $this -> get_option('excerpt_readmore');
				if (!empty($excerpt_readmore)) {
					$more = ' <a href="' . get_permalink($slideshow_post -> ID) . '">' . __($excerpt_readmore) . '</a>';	
				}
			}
			
			return $more;
		}
		
		function excerpt_length($length = null) {
			$excerptsettings = $this -> get_option('excerptsettings');
			if (!empty($excerptsettings)) {
				$excerpt_length = $this -> get_option('excerpt_length');
				if (!empty($excerpt_length)) {
					$length = $excerpt_length;
				}
			}
			
			return $length;
		}
		
		function plugin_action_links($actions = null, $plugin_file = null, $plugin_data = null, $context = null) {
			$this_plugin = plugin_basename(__FILE__);
			
			if (!empty($plugin_file) && $plugin_file == $this_plugin) {
				$actions[] = '<a href="" onclick="jQuery.colorbox({href:ajaxurl + \'?action=slideshow_serialkey\'}); return false;" id="slideshow_submitseriallink">' . __('Serial Key', 'slideshow-gallery') . '</a>';	
				$actions[] = '<a href="' . admin_url('admin.php?page=' . $this -> sections -> settings) . '">' . __('Settings', 'slideshow-gallery') . '</a>';
			}
			
			return $actions;
		}
		
		function init() {
		}
		
		function init_textdomain() {		
			$locale = get_locale();
			
			if (!empty($locale)) { 
				if ($locale == "ja" || $locale == "ja_JP") { setlocale(LC_ALL, "ja_JP.UTF8"); }
			} else { 
				setlocale(LC_ALL, apply_filters('slideshow_setlocale', $locale)); 
			}
			
			$mo_file = $this -> plugin_name . '-' . $locale . '.mo';
			$language_external = $this -> get_option('language_external');
		
			if (!empty($language_external)) {
				if (function_exists('load_textdomain')) {
					load_textdomain($this -> plugin_name, WP_LANG_DIR . DS . $this -> plugin_name . DS . $mo_file);
				}
			} else {
				if (function_exists('load_plugin_textdomain')) {
					load_plugin_textdomain($this -> plugin_name, false, dirname(plugin_basename(__FILE__)) . DS . 'languages' . DS);
				}
			}			
		}
		
		function plugins_loaded() {		
			$this -> ci_initialize();
				
			if ($this -> language_do()) {
	        	add_filter('gettext', array($this, 'language_useordefault'), 0);
	        }
		}
		
		function wp_head() {
			global $slideshow_javascript, $slideshow_css;
			$slideshow_javascript = array();
			$slideshow_css = array();
		}
		
		function wp_footer() {
			global $slideshow_javascript, $slideshow_css;
			$jsoutput = $this -> get_option('jsoutput');
		
			if (!empty($slideshow_javascript)) {
				if (!empty($jsoutput) && $jsoutput == "footerglobal") {
					?><!-- Slideshow Gallery Javascript BEG --><?php
				
					foreach ($slideshow_javascript as $javascript) {
						echo stripslashes($javascript);
					}
					
					?><!-- Slideshow Gallery Javascript END --><?php
				}
			}
			
			if (!empty($slideshow_css)) {
				if (!empty($jsoutput) && $jsoutput == "footerglobal") {
					?><!-- Slideshow Gallery CSS BEG --><?php
						
					foreach ($slideshow_css as $css) {
						echo stripslashes($css);
					}
					
					?><!-- Slideshow Gallery CSS END --><?php
				}
			}
		}
		
		function admin_menu() {
			$update_icon = ($this -> has_update()) ? ' <span class="update-plugins count-1"><span class="update-count">1</span></span>' : '';
			$this -> check_roles();
			add_menu_page(__('Slideshow', 'slideshow-gallery'), __('Slideshow', 'slideshow-gallery') . $update_icon, 'slideshow_slides', $this -> sections -> slides, array($this, 'admin_slides'), false, "26.113");
			$this -> menus['slideshow-slides'] = add_submenu_page($this -> sections -> slides, __('Manage Slides', 'slideshow-gallery'), __('Manage Slides', 'slideshow-gallery'), 'slideshow_slides', $this -> sections -> slides, array($this, 'admin_slides'));
			$this -> menus['slideshow-galleries'] = add_submenu_page($this -> sections -> slides, __('Manage Galleries', 'slideshow-gallery'), __('Manage Galleries', 'slideshow-gallery'), 'slideshow_galleries', $this -> sections -> galleries, array($this, 'admin_galleries'));
			$this -> menus['slideshow-settings'] = add_submenu_page($this -> sections -> slides, __('Configuration', 'slideshow-gallery'), __('Configuration', 'slideshow-gallery'), 'slideshow_settings', $this -> sections -> settings, array($this, 'admin_settings'));
			
			if (!$this -> ci_serial_valid()) {
				$this -> menus['slideshow-submitserial'] = add_submenu_page($this -> sections -> slides, __('Submit Serial Key', 'slideshow-gallery'), __('Submit Serial Key', 'slideshow-gallery'), 'slideshow_submitserial', $this -> sections -> submitserial, array($this, 'admin_submitserial'));
			}
			
			add_action("load-" . $this -> menus['slideshow-slides'], array($this, 'screen_options_slides'));
			add_action("load-" . $this -> menus['slideshow-galleries'], array($this, 'screen_options_galleries'));
			
			do_action('slideshow_admin_menu', $this -> menus);
			
			add_action('admin_head-' . $this -> menus['slideshow-settings'], array($this, 'admin_head_gallery_settings'));
			
			add_dashboard_page(sprintf('Slideshow Gallery %s', $this -> version), sprintf('Slideshow Gallery %s', $this -> version), 'read', $this -> sections -> about, array($this, 'slideshow_gallery_about'));
			remove_submenu_page('index.php', $this -> sections -> about);
		}
		
		function set_screen_option($status = null, $option = null, $value = null) {			
			return $value;
		}
		
		function removable_query_args($removable_query_args = array()) {
			
			$removable_query_args[] = 'Galleryupdated';
			$removable_query_args[] = 'Gallerymessage';
			
			return $removable_query_args;
		}
		
		function screen_options_slides() {		
			$screen = get_current_screen();
		 
			// get out of here if we are not on our settings page
			if (!is_object($screen) || $screen -> id != $this -> menus['slideshow-slides']) {
				return;
			}
		 
			$args = array(
				'label' 	=> 	__('Slides per page', 'slideshow-gallery'),
				'default' 	=> 	15,
				'option' 	=> 	'slideshow_slides_perpage'
			);
			
			add_screen_option('per_page', $args);
			
			require_once $this -> plugin_base() . DS . 'includes' . DS . 'class.slide-list-table.php';
			$Slide_List_Table = new Slide_List_Table;
		}
		
		function screen_options_galleries() { 						
			$screen = get_current_screen();
		 
			// get out of here if we are not on our settings page
			if (!is_object($screen) || $screen -> id != $this -> menus['slideshow-galleries']) {
				return;
			}
		 
			$args = array(
				'label' 	=> 	__('Galleries per page', 'slideshow-gallery'),
				'default' 	=> 	15,
				'option' 	=> 	'slideshow_galleries_perpage'
			);
			
			add_screen_option('per_page', $args);
			
			require_once $this -> plugin_base() . DS . 'includes' . DS . 'class.gallery-list-table.php';
			$Gallery_List_Table = new Gallery_List_Table;
		}
		
		function default_hidden_columns($hidden = null, $screen = null) {			
			if ($current_screen = get_current_screen()) {												
				if ($current_screen -> id == $screen -> id) {					
					switch ($screen -> id) {
						case $this -> menus['slideshow-slides']			:
							$hidden = array(
								'id',
						        'link',
						        'expiry',
						        'order',
							);
							break;
						case $this -> menus['slideshow-galleries']		:
							$hidden = array(
								'id',
							);
							break;
					}
				}	
			}		
			
			return $hidden;
		}
		
		function slideshow_gallery_about() {
			$this -> render('about', false, true, 'admin');
		}
		
		function admin_head() {
			$this -> render('head', false, true, 'admin');
		}
		
		function admin_head_gallery_settings() {		
			add_meta_box('submitdiv', __('Save Settings', 'slideshow-gallery'), array($this -> Metabox, "settings_submit"), $this -> menus['slideshow-settings'], 'side', 'core');
			add_meta_box('pluginsdiv', __('Recommended Plugin', 'slideshow-gallery'), array($this -> Metabox, "settings_plugins"), $this -> menus['slideshow-settings'], 'side', 'core');
			add_meta_box('aboutdiv', __('About This Plugin', 'slideshow-gallery') . $this -> Html -> help(__('More about this plugin and the creators of it', 'slideshow-gallery')), array($this -> Metabox, "settings_about"), $this -> menus['slideshow-settings'], 'side', 'core');
			add_meta_box('generaldiv', __('General Settings', 'slideshow-gallery') . $this -> Html -> help(__('General configuration settings for the inner workings and some default behaviours', 'slideshow-gallery')), array($this -> Metabox, "settings_general"), $this -> menus['slideshow-settings'], 'normal', 'core');
			add_meta_box('postsdiv', __('Posts/Pages Settings', 'slideshow-gallery'), array($this -> Metabox, "settings_postspages"), $this -> menus['slideshow-settings'], 'normal', 'core');
			add_meta_box('linksimagesdiv', __('Links &amp; Images Overlay', 'slideshow-gallery') . $this -> Html -> help(__('Configure the way that slides with links are opened', 'slideshow-gallery')), array($this -> Metabox, "settings_linksimages"), $this -> menus['slideshow-settings'], 'normal', 'core');
			add_meta_box('stylesdiv', __('Appearance &amp; Styles', 'slideshow-gallery') . $this -> Html -> help(__('Change the way the slideshows look so that it suits your needs', 'slideshow-gallery')), array($this -> Metabox, "settings_styles"), $this -> menus['slideshow-settings'], 'normal', 'core');
			add_meta_box('techdiv', __('Technical Settings', 'slideshow-gallery'), array($this -> Metabox, "settings_tech"), $this -> menus['slideshow-settings'], 'normal', 'core');
			add_meta_box('wprelateddiv', __('WordPress Related', 'slideshow-gallery') . $this -> Html -> help(__('Settings specifically related to WordPress', 'slideshow-gallery')), array($this -> Metabox, "settings_wprelated"), $this -> menus['slideshow-settings'], 'normal', 'core');
			
			do_action('do_meta_boxes', $this -> menus['slideshow-settings'], 'normal');
			do_action('do_meta_boxes', $this -> menus['slideshow-settings'], 'side');
		}
		
		function admin_submitserial() {
			$success = false;
		
			if (!empty($_POST)) {
				check_admin_referer($this -> sections -> submitserial);
				
				if (empty($_REQUEST['serial'])) { $errors[] = __('Please fill in a serial key.', 'slideshow-gallery'); }
				else { 
					$this -> update_option('serialkey', $_REQUEST['serial']);	//update the DB option
					$this -> delete_all_cache('all');
					
					if (!$this -> ci_serial_valid()) { $errors[] = __('Serial key is invalid, please try again.', 'slideshow-gallery'); }
					else {
						delete_transient($this -> pre . 'update_info');
						$success = true;
						$this -> redirect('?page=' . $this -> sections -> welcome); 
					}
				}
			}
			
			$this -> render('settings-submitserial', array('success' => $success, 'errors' => $errors), true, 'admin');
		}
		
		function admin_notices() {
			
			if (is_admin()) {			
				$this -> check_uploaddir();
			
				$message = (!empty($_GET[$this -> pre . 'message'])) ? esc_html($_GET[$this -> pre . 'message']) : false;
				if (!empty($message)) {						
					$msg_type = (!empty($_GET[$this -> pre . 'updated'])) ? 'msg' : 'err';
					call_user_func(array($this, 'render_' . $msg_type), $message);
				}
				
				$showmessage_ratereview = $this -> get_option('showmessage_ratereview');
				if (!empty($showmessage_ratereview)) {
					$message = sprintf(__('You have been using the %s for %s days or more. Please consider to %s it or say it %s on %s.', 'slideshow-gallery'), 
					'<a href="https://wordpress.org/plugins/slideshow-gallery/" target="_blank">Tribulant Slideshow Gallery plugin</a>',
					$showmessage_ratereview,
					'<a class="button" href="https://wordpress.org/support/view/plugin-reviews/slideshow-gallery?rate=5#postform" target="_blank"><i class="fa fa-star"></i> Rate</a>',
					'<a class="button" href="https://wordpress.org/plugins/slideshow-gallery/?compatibility[version]=' . get_bloginfo('version') . '&compatibility[topic_version]=' . $this -> version . '&compatibility[compatible]=1" target="_blank"><i class="fa fa-check"></i> Works</a>',
					'<a href="https://wordpress.org/plugins/slideshow-gallery/" target="_blank">WordPress.org</a>');
					
					$dismissable = admin_url('admin.php?page=' . $this -> sections -> settings . '&slideshow_method=hidemessage&message=ratereview');
					$this -> render_msg($message, $dismissable, false);
				}
				
				/* Serial key submission message */
				$page = esc_html($_GET['page']);
				if (!$this -> ci_serial_valid() && (empty($page) || $page != $this -> sections -> submitserial)) {				
					$hidemessage_upgradetopro = $this -> get_option('hidemessage_upgradetopro');
				
					if (empty($hidemessage_upgradetopro)) {
						$message = sprintf(__('You are using Slideshow Gallery LITE. Take your slideshows to the next level with %s. Already purchased? %s.', 'slideshow-gallery'), '<a href="' . admin_url('admin.php?page=' . $this -> sections -> lite_upgrade) . '">Slideshow Gallery PRO</a>', '<a href="https://tribulant.com/docs/wordpress-slideshow-gallery/1758" target="_blank">See instructions to install PRO</a>');
						$message .= ' <a class="button button-primary" href="' . admin_url('admin.php?page=' . $this -> sections -> lite_upgrade) . '"><i class="fa fa-check"></i> ' . __('Upgrade to PRO', 'slideshow-gallery') . '</a>';
						$message .= ' <a class="button button-secondary" href="' . admin_url('admin.php?page=' . $this -> sections -> welcome . '&slideshow_method=hidemessage&message=upgradetopro') . '"><i class="fa fa-times"></i> ' . __('Hide this message', 'slideshow-gallery') . '</a>';
						$dismissable = admin_url('admin.php?page=' . $this -> sections -> welcome . '&slideshow_method=hidemessage&message=upgradetopro');
						$this -> render_msg($message, $dismissable, false);
						
						?>
			            
			            <script type="text/javascript">
						jQuery(document).ready(function(e) {
			                jQuery('#<?php echo $this -> pre; ?>submitseriallink').click(function() {					
								jQuery.colorbox({href:ajaxurl + "?action=slideshow_serialkey"});
								return false;
							});
			            });
						</script>
			            
			            <?php
			        }
				}
			}
			
		}
		
		function mce_buttons($buttons) {
			array_push($buttons, "separator", "gallery");
			return $buttons;
		}
		
		function mce_external_plugins($plugins) {
			$plugins['gallery'] = $this -> url() . '/js/tinymce/editor_plugin.js';
			return $plugins;
		}
		
		function slideshow($output = true, $post_id = null, $exclude = null) {		
			$params['post_id'] = $post_id;
			$params['exclude'] = $exclude;
		
			$content = $this -> embed($params, false);
			
			if ($output == true) {
				echo $content;
			} else {
				return $content;
			}
		}
		
		function embed($atts = array(), $content = null) {
			//global variables
			global $wpdb;
			$styles = $this -> get_option('styles');
			
			$effect = $this -> get_option('effect');
			$slide_direction = $this -> get_option('slide_direction');
			$easing = $this -> get_option('easing');
			$autoheight = $this -> get_option('autoheight');
			
			$this -> add_filter('excerpt_more', 'excerpt_more', 999, 1);
			$this -> add_filter('excerpt_length', 'excerpt_length', 999, 1);
		
			// default shortcode parameters
			$defaults = array(
				'source'				=>	"slides",
				'products'				=>	false,
				'productsnumber'		=>	10,
				'featured'				=>	false,
				'featurednumber'		=>	10,
				'featuredtype'			=>	"post",
				'gallery_id'			=>	false,
				'orderby'				=>	array('order', "ASC"),
				'orderf'				=>	false,	// order field
				'orderd'				=>	false,	// order direction (ASC/DESC)
				'resizeimages'			=>	(($styles['resizeimages'] == "Y") ? "true" : "false"),
				'imagesoverlay'			=>	(($this -> get_option('imagesthickbox') == "Y") ? "true" : "false"),
				'layout'				=>	($styles['layout']),
				'width'					=>	($styles['width']),
				'height'				=>	((empty($autoheight)) ? $styles['height'] : false),
				'autoheight'			=>	((!empty($autoheight)) ? "true" : "false"),
				'autoheight_max'		=>	($this -> get_option('autoheight_max')),
				'resheight'				=>	($styles['resheight']),
				'resheighttype'			=>	($styles['resheighttype']),
				'auto'					=>	(($this -> get_option('autoslide') == "Y") ? "true" : "false"),
				'effect'				=>	((empty($effect) || (!empty($effect) && $effect == "fade")) ? 'fade' : $effect),
				'slide_direction'		=>	((empty($slide_direction) || (!empty($slide_direction) && $slide_direction == "lr")) ? 'lr' : 'tb'),
				'easing'				=>	((empty($easing)) ? 'swing' : $easing),
				'autospeed'				=>	($this -> get_option('autospeed')),
				'alwaysauto'			=>	($this -> get_option('alwaysauto')),
				'fadespeed'				=>	($this -> get_option('fadespeed')),
				'shownav'				=>	(($this -> get_option('shownav') == "Y") ? "true" : "false"),
				'navopacity'			=>	($this -> get_option('navopacity')),
				'navhoveropacity'		=>	($this -> get_option('navhover')),
				'showinfo'				=>	(($this -> get_option('information') == "Y") ? "true" : "false"),
				'infoposition'			=> 	($this -> get_option('infoposition')),
				'infoonhover'			=>	($this -> get_option('infoonhover')),
				'infospeed'				=>	($this -> get_option('infospeed')),
				'infodelay'				=>	($this -> get_option('infodelay')),
				'infofade'				=>  ($this -> get_option ('infofade')),
				'infofadedelay'			=> 	($this -> get_option ('infofadedelay')),
				'showthumbs'			=>	(($this -> get_option('thumbnails') == "Y") ? "true" : "false"),
				'thumbsposition'		=>	($this -> get_option('thumbposition')),
				'thumbsborder'			=>	($styles['thumbactive']),
				'thumbsspeed'			=>	($this -> get_option('thumbscrollspeed')),
				'thumbsspacing'			=>	($this -> get_option('thumbspacing')),
				'post_id' 				=> 	null,
				'numberposts'			=>	"-1",
				'exclude' 				=> 	null, 
				'custom' 				=> 	null,
			);
					
			$s = shortcode_atts($defaults, $atts);
			extract($s);
			
			$slideshowtype = false;

			if (!empty($products)) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');			
				if (is_plugin_active('wp-checkout' . DS . 'wp-checkout.php')) {
					$slides = array();
					
					if (empty($orderf) && empty($orderd)) {
						$orderf = "created";
						$orderd = "DESC";
					}
					
					if (class_exists('wpCheckout')) {
						if ($wpCheckout = new wpCheckout()) {
							global $wpcoDb, $Product;
							$wpcoDb -> model = $Product -> model;
							$productstype = $products;
						
							switch ($productstype) {
								case 'latest'		:
									$products = $wpcoDb -> find_all(false, false, array($orderf, $orderd), $productsnumber);
									break;
								case 'featured'		:
									$products = $wpcoDb -> find_all(array('featured' => "1"), false, array($orderf, $orderd), $productsnumber);
									break;
							}
						}
					}
					
					if (!empty($products)) {					
						$slides = $products;
						$slideshowtype = "products";
						$content = $this -> render('gallery', array('slides' => $slides, 'unique' => 'products' . $productstype . $productsnumber, 'products' => true, 'options' => $s, 'frompost' => false), false, 'default');
					} else {
						$error = __('No products are available', 'slideshow-gallery');
					}
				} else {
					$error = sprintf(__('You need the %sShopping Cart plugin%s to display products slides.', 'slideshow-gallery'), '<a href="https://tribulant.com/plugins/view/10/wordpress-shopping-cart-plugin" target="_blank">', '</a>');
				}
			// Featured images
			} elseif (!empty($featured)) {
				global $post;
				
				if (empty($orderf) && empty($orderd)) {
					$orderf = "date";
					$orderd = "DESC";
				}
			
				$args = array(
					'numberposts'				=>	$featurednumber,            	// should show 5 but only shows 3
					'post_type'					=>	$featuredtype,    				// posts only
					'meta_key'					=>	'_thumbnail_id', 				// with thumbnail
					'exclude'					=>	$post -> ID,         			// exclude current post
					'orderby'					=>	$orderf,
					'order'						=>	$orderd,
				);
				
				if ($posts = get_posts($args)) {										
					$slides = $posts;
					$slideshowtype = "featured";
					$content = $this -> render('gallery', array('slides' => $slides, 'unique' => 'featured' . $featuredtype . $featurednumber, 'featured' => true, 'options' => $s, 'frompost' => false), false, 'default');
				} else {
					$error = sprintf(__('No posts with featured images are available. Ensure your theme includes %s support.', 'slideshow-gallery'), '<code>add_theme_support("post-thumbnails");</code>');
				}
			// Slides of a gallery
			} elseif (!empty($gallery_id)) {
				if (!is_array($orderby) || $orderby == "random") {
					$orderbystring = "ORDER BY RAND()";
				} else {
					if (empty($orderf) && empty($orderd)) {
						list($orderf, $orderd) = $orderby;
					}
					
					if ($orderf == "order") {
						$orderbystring = "ORDER BY " . $this -> GallerySlides() -> table . ".order " . $orderd . "";
					} else {
						$orderbystring = "ORDER BY " . $this -> Slide() -> table . "." . $orderf . " " . $orderd . "";
					}
				}
			
				$slidesquery = "SELECT * FROM " . $this -> Slide() -> table . " LEFT JOIN " . $this -> GallerySlides() -> table . 
				" ON " . $this -> Slide() -> table . ".id = " . $this -> GallerySlides() -> table . ".slide_id WHERE " . 
				$this -> GallerySlides() -> table . ".gallery_id = '" . $gallery_id . "' AND (" . $this -> Slide() -> table . ".expiry = NULL OR " . $this -> Slide() -> table . ".expiry > CURDATE() OR " . $this -> Slide() -> table . ".expiry = '0000-00-00') " . $orderbystring;
				
				$query_hash = md5($slidesquery);
				if ($oc_slides = wp_cache_get($query_hash, 'slideshowgallery')) {
					$slides = $oc_slides;
				} else {
					$slides = $wpdb -> get_results($slidesquery);
					wp_cache_set($query_hash, $slides, 'slideshowgallery', 0);
				}
				
				if (!empty($slides)) {				
					$imagespath = $this -> get_option('imagespath');
				
					foreach ($slides as $skey => $slide) {											
						$slides[$skey] -> image_path = $this -> Html -> image_path($slide);
					}
				
					if ($orderby == "random") { shuffle($slides); }
					$slideshowtype = "gallery";
					$content = $this -> render('gallery', array('slides' => $slides, 'unique' => 'gallery' . $gallery_id . rand(1, 999), 'options' => $s, 'frompost' => false), false, 'default');	
				} else {
					$error = __('No slides are available in this gallery', 'slideshow-gallery');
				}
			// All slides
			} elseif (!empty($custom) || empty($post_id)) {
				if (!empty($orderf) && !empty($orderd)) {
					$orderby = array($orderf, $orderd);
				}
				
				$slides = $this -> Slide() -> find_all(null, null, $orderby);
				
				if (!empty($slides)) {
					foreach ($slides as $slide_key => $slide) {
						
						// Check exclude
						if (!empty($exclude)) {
							$exclude = array_map('trim', explode(',', $exclude));
							if (in_array($slide -> id, $exclude)) {
								unset($slides[$slide_key]);
							}
						}
						
						// Check slide expiration
						if (!empty($slide -> expiry)) {							
							if (strtotime($slide -> expiry) <= time()) {
								unset($slides[$slide_key]);
							}
						}
					}
				}
				
				if ($orderby == "random") { shuffle($slides); }
				$slideshowtype = "slides";
				
				if (!empty($slides)) {
					$content = $this -> render('gallery', array('slides' => $slides, 'unique' => "custom" . rand(1, 999), 'options' => $s, 'frompost' => false), false, 'default');
				} else {
					$error = __('No slides are available', 'slideshow-gallery');
				}
			// Images of a post/page
			} else {
				global $post;
				$pid = (empty($post_id)) ? $post -> ID : $post_id;
				
				if (!is_numeric($post_id)) {
					$pid = $post -> ID;
				}
			
				if (!empty($pid) && $post = get_post($pid)) {
					$children_attributes = array(
						'numberposts'					=>	$numberposts,
						'post_parent'					=>	$post -> ID,
						'post_type'						=>	"attachment",
						'post_status'					=>	"any",
						'post_mime_type'				=>	"image",
						'orderby'						=>	((!empty($orderf)) ? $orderf : "menu_order"),
						'order'							=>	((!empty($orderd)) ? $orderd : "ASC"),
					);
				
					if ($attachments = get_children($children_attributes)) {
						if (!empty($exclude)) {
							$exclude = array_map('trim', explode(',', $exclude));
							
							$a = 0;
							foreach ($attachments as $id => $attachment) {
								//$attachments[$id] = (object) array_map('esc_attr', (array) $attachment);
								
								$a++;
								if (in_array($a, $exclude)) {
									unset($attachments[$id]);
								}
							}
						}
					
						if ($orderby == "random") { shuffle($attachments); }
						$slides = $attachments;
						$slideshowtype = "post";
						$content = $this -> render('gallery', array('slides' => $slides, 'unique' => $pid, 'options' => $s, 'frompost' => true), false, 'default');
					} else {
						$error = __('No attachments on this post/page', 'slideshow-gallery');
					}
				} else {
					$error = __('No post/page ID was specified', 'slideshow-gallery');
				}
			}
			
			// Check if this is an RSS Feed?
			if (is_feed()) {
				$content = '';
				ob_start();
				
				$width = $this -> get_option('thumbwidth');
				$height = $this -> get_option('thumbheight');
				
				if (!empty($slides)) {
					switch ($slideshowtype) {
						case 'products'					:
							foreach ($slides as $slide) {
								?>
								
								<a href="<?php echo get_permalink($slide -> post_id); ?>" title="<?php echo esc_attr(__($slide -> title)); ?>">
									<img align="left" hspace="15" src="<?php echo $this -> Html() -> otf_image_src($slide, $this -> get_option('thumbwidth'), $this -> get_option('thumbheight'), 100); ?>" />
								</a>
								
								<?php
							}
							break;
						case 'gallery'					:
							foreach ($slides as $slide) {
								?>
								
								<a href="<?php echo $slide -> image_path; ?>" title="<?php echo __($slide -> title); ?>">
									<img align="left" hspace="15" src="<?php echo $this -> Html() -> otf_image_src($slide, $this -> get_option('thumbwidth'), $this -> get_option('thumbheight'), 100); ?>" alt="<?php echo $this -> Html -> sanitize(__($slide -> title)); ?>" />
								</a>
								
								<?php
							}
							break;
						case 'featured'					:
							foreach ($slides as $slide) {
								?>
								
								<a href="<?php echo $slide -> guid; ?>" title="<?php echo esc_attr(__($slide -> post_title)); ?>">
									<img align="left" hspace="15" src="<?php echo $this -> Html() -> otf_image_src($slide, $this -> get_option('thumbwidth'), $this -> get_option('thumbheight'), 100); ?>" alt="<?php echo $this -> Html -> sanitize(__($slide -> post_title)); ?>" />
								</a>
								
								<?php
							}
							break;
						case 'post'						:
						default 						:
							foreach ($slides as $slide) {
								$full_image_href = wp_get_attachment_image_src($slide -> ID, 'full', false);
								$full_image_url = wp_get_attachment_url($slide -> ID); 
								
								?>
								
								<a href="<?php echo $full_image_url; ?>" title="<?php echo __($slide -> post_title); ?>">
									<img align="left" hspace="15" src="<?php echo $this -> Html() -> otf_image_src($slide, $this -> get_option('thumbwidth'), $this -> get_option('thumbheight'), 100); ?>" alt="<?php echo $this -> Html -> sanitize(__($slide -> post_title)); ?>" />
								</a>
								
								<?php
							}
							break;
					}
					
					?><hr style="visibility:hidden; height:1px; width:100%; display:block;" /><?php
				}
				
				$content = ob_get_clean();
			}
			
			// Check for error messages
			if (!empty($error)) {
				$content = '';
				$content .= '<p class="slideshow_error slideshow-gallery-error">';
				$content .= stripslashes($error);
				$content .= '</p>';
			}
			
			remove_filter('excerpt_more', array($this, 'excerpt_more'));
			remove_filter('excerpt_length', array($this, 'excerpt_length'));
			
			return $content;
		}
		
		function admin_slides() {
			global $wpdb;
			$method = (!empty($_GET['method'])) ? esc_html($_GET['method']) : false;
			switch ($method) {
				case 'delete'			:
					$id = esc_html($_GET['id']);
					if (!empty($id)) {
						if ($this -> Slide() -> delete($id)) {
							$msg_type = 'message';
							$message = __('Slide has been removed', 'slideshow-gallery');
						} else {
							$msg_type = 'error';
							$message = __('Slide cannot be removed', 'slideshow-gallery');	
						}
					} else {
						$msg_type = 'error';
						$message = __('No slide was specified', 'slideshow-gallery');
					}
					
					$this -> redirect($this -> referer, $msg_type, $message);
					break;
				case 'save'				:				
					if (!empty($_POST)) {	
						check_admin_referer($this -> sections -> slides . '_save');
											
						if ($this -> Slide() -> save($_POST, true)) {
							$message = __('Slide has been saved', 'slideshow-gallery');
							
							if (!empty($_POST['continueediting'])) {
								$this -> redirect(admin_url('admin.php?page=' . $this -> sections -> slides . '&method=save&id=' . $this -> Slide() -> insertid . '&continueediting=1'), 'message', $message);	
							} else {
								$this -> redirect($this -> url, "message", $message);
							}
						} else {
							$this -> render_err(__('Slide could not be saved', 'slideshow-gallery'));
							$this -> render('slides' . DS . 'save', false, true, 'admin');
						}
					} else {
						$this -> Db -> model = $this -> Slide() -> model;
						$this -> Slide() -> find(array('id' => esc_html($_GET['id'])));
						$this -> render('slides' . DS . 'save', false, true, 'admin');
					}
					break;
				case 'save-multiple'	:
					if (!empty($_POST)) {	
						check_admin_referer($this -> sections -> slides . '_save-multiple');
											
						$errors = array();
						
						if (!empty($_POST['Slide']['slides'])) {
							$slides = $_POST['Slide']['slides'];
							$galleries = $_POST['Slide']['galleries'];
							
							foreach ($slides as $attachment_id => $slide) {
								$slide_data = array(
									'Slide'				=>	array(
										'title'				=>	$slide['title'],
										'description'		=>	$slide['description'],
										'image'				=>	basename($slide['url']),
										'attachment_id'		=>	$attachment_id,
										'type'				=>	'media',
										'image_url'			=>	$slide['url'],
										'media_file'		=>	$slide['url'],
										'galleries'			=>	$galleries,
									)
								);
								
								if (!$this -> Slide() -> save($slide_data)) {									
									$errors = array_merge($errors, $this -> Slide() -> errors);
								}
							}
							
							if (empty($errors)) {
								$message = __('Slides have been saved', 'slideshow-gallery');
								$this -> redirect(admin_url('admin.php?page=' . $this -> sections -> slides), 'message', $message);
							}
						} else {
							$errors[] = __('No slides were selected', 'slideshow-gallery');
						}
					}
					
					$this -> render('slides' . DS . 'save-multiple', array('errors' => $errors), true, 'admin');
					break;
				case 'order'			:
					$gallery_id = esc_html($_GET['gallery_id']);
					if (!empty($gallery_id)) {
						$gallery = $this -> Gallery() -> find(array('id' => esc_html($gallery_id)));
						
						$slides = array();
						$gsquery = "SELECT gs.slide_id FROM `" . $this -> GallerySlides() -> table . "` gs WHERE `gallery_id` = '" . $gallery -> id . "' ORDER BY gs.order ASC";
						
						$query_hash = md5($gsquery);
						if ($oc_gs = wp_cache_get($query_hash, 'slideshowgallery')) {
							$gs = $oc_gs;
						} else {
							$gs = $wpdb -> get_results($gsquery);
							wp_cache_set($query_hash, $gs, 'slideshowgallery', 0);
						}
						
						if (!empty($gs)) {
							foreach ($gs as $galleryslide) {
								$slides[] = $this -> Slide() -> find(array('id' => $galleryslide -> slide_id));
							}
						}
						
						$this -> render('slides' . DS . 'order-gallery', array('gallery' => $gallery, 'slides' => $slides), true, 'admin');	
					} else {
						$slides = $this -> Slide() -> find_all(null, null, array('order', "ASC"));
						$this -> render('slides' . DS . 'order', array('slides' => $slides), true, 'admin');
					}
					break;
				default					:
					$this -> render('slides' . DS . 'index', false, true, 'admin');
					break;
			}
		}
		
		function admin_galleries() {
			
			$method = esc_html($_GET['method']);
			switch ($method) {
				case 'save'						:
					if (!empty($_POST)) {	
						check_admin_referer($this -> sections -> galleries . '_save');
											
						if ($this -> Gallery() -> save($_POST, true)) {
							$message = __('Gallery has been saved', 'slideshow-gallery');
							
							if (!empty($_POST['continueediting'])) {
								$this -> redirect(admin_url('admin.php?page=' . $this -> sections -> galleries . '&method=save&id=' . $this -> Gallery() -> insertid . '&continueediting=1'), 'message', $message);
							} else {
								$this -> redirect($this -> url, "message", $message);
							}
						} else {
							$this -> render('galleries' . DS . 'save', false, true, 'admin');
						}
					} else {
						$this -> Db -> model = $this -> Gallery() -> model;
						$this -> Gallery() -> find(array('id' => esc_html($_GET['id'])));
						$this -> render('galleries' . DS . 'save', false, true, 'admin');
					}
					break;
				case 'view'						:
					$this -> Db -> model = $this -> Gallery() -> model;
					$gallery = $this -> Gallery() -> find(array('id' => esc_html($_GET['id'])));
					$perpage = (isset($_COOKIE[$this -> pre . 'slidesperpage'])) ? $_COOKIE[$this -> pre . 'slidesperpage'] : 25;
					$orderfield = (empty($_GET['orderby'])) ? 'modified' : esc_html($_GET['orderby']);
					$orderdirection = (empty($_GET['order'])) ? 'DESC' : strtoupper(esc_html($_GET['order']));
					$order = array($orderfield, $orderdirection);
					$data = $this -> paginate('GallerySlides', "*", $this -> sections -> galleries . '&method=view&id=' . $gallery -> id, array('gallery_id' => $gallery -> id), false, $perpage, $order);
					
					$data['Slide'] = array();
					if (!empty($data[$this -> GallerySlides() -> model])) {
						foreach ($data[$this -> GallerySlides() -> model] as $galleryslide) {
							$this -> Db -> model = $this -> Slide() -> model;
							$data['Slide'][] = $this -> Slide() -> find(array('id' => $galleryslide -> slide_id));
						}
					}
					
					$this -> render('galleries' . DS . 'view', array('gallery' => $gallery, 'slides' => $data[$this -> Slide() -> model], 'paginate' => $data['Paginate']), true, 'admin');
					break;
				case 'hardcode'			:
					$this -> Db -> model = $this -> Gallery() -> model;
					$gallery = $this -> Gallery() -> find(array('id' => esc_html($_GET['id'])));					
					$this -> render('galleries' . DS . 'hardcode', array('gallery' => $gallery), true, 'admin');
					break;
				case 'delete'			:
					$id = esc_html($_GET['id']);
					if (!empty($id)) {
						if ($this -> Gallery() -> delete(esc_html($_GET['id']))) {
							$msg_type = 'message';
							$message = __('Gallery has been removed', 'slideshow-gallery');
						} else {
							$msg_type = 'error';
							$message = __('Gallery cannot be removed', 'slideshow-gallery');	
						}
					} else {
						$msg_type = 'error';
						$message = __('No gallery was specified', 'slideshow-gallery');
					}
					
					$this -> redirect($this -> referer, $msg_type, $message);
					break;
				default							:
					$this -> render('galleries' . DS . 'index', false, true, 'admin');
					break;
			}
		}
		
		function admin_settings() {
			global $wpdb;
			$method = esc_html($_GET['method']);
		
			switch ($method) {
				case 'dismiss'			:
					if (!empty($_GET['dismiss'])) {
						$this -> update_option('dismiss_' . esc_html($_GET['dismiss']), 1);
					}
					
					$this -> redirect($this -> referer);
					break;
				case 'checkdb'			:
					$this -> check_tables();
					
					if (!empty($this -> models)) {
						foreach ($this -> models as $model) {
							$query = "OPTIMIZE TABLE `" . $this -> {$model}() -> table . "`";
							$wpdb -> query($query);
						}
					}
				
					$this -> redirect($this -> referer, 'message', __('Database tables have been checked and optimized', 'slideshow-gallery'));
					break;
				case 'reset'			:
					global $wpdb;
					$query = "DELETE FROM `" . $wpdb -> prefix . "options` WHERE `option_name` LIKE '" . $this -> pre . "%';";
					
					if ($wpdb -> query($query)) {
						$this -> initialize_options();
					
						$message = __('All configuration settings have been reset to their defaults', 'slideshow-gallery');
						$msg_type = 'message';
						$this -> render_msg($message);	
					} else {
						$message = __('Configuration settings could not be reset', 'slideshow-gallery');
						$msg_type = 'error';
						$this -> render_err($message);
					}
					
					$this -> redirect($this -> url, $msg_type, $message);
					break;
				default					:
					if (!empty($_POST)) {	
						check_admin_referer($this -> sections -> settings);
											
						delete_option('tridebugging');
						$this -> delete_option('infohideonmobile');
						$this -> delete_option('autoheight');
						$this -> delete_option('language_external');
						$this -> delete_option('excerptsettings');
						$this -> delete_option('infofade');
						$this -> delete_option('fadedelay');
						$this -> delete_option('infoonhover');
						$this -> delete_option('thumbhideonmobile');
					
						foreach ($_POST as $pkey => $pval) {					
							switch ($pkey) {
								case 'styles'				:
									$styles = array();
									foreach ($pval as $pvalkey => $pvalval) {
										switch ($pvalkey) {
											case 'layout'			:
												if (!$this -> ci_serial_valid()) {
													$styles[$pvalkey] = "specific";
												} else {
													$styles[$pvalkey] = $pvalval;
												}
												break;
											default 				:
												$styles[$pvalkey] = $pvalval;
												break;
										}
									}
									
									$this -> update_option('styles', $styles);
									break;
								case 'debugging'			:
									if (!empty($pval)) {
										update_option('tridebugging', 1);
									}
									break;
								case 'excerpt_readmore'		:
									if ($this -> language_do()) {
										$this -> update_option($pkey, $this -> language_join($pval));
									} else {
										$this -> update_option($pkey, $pval);
									}
									break;
								case 'permissions'			:
									global $wp_roles;
									$role_names = $wp_roles -> get_names();
								
									if (!empty($_POST['permissions'])) {
										$permissions = $_POST['permissions'];
										
										foreach ($role_names as $role_key => $role_name) {
											foreach ($this -> sections as $section_key => $section_name) {
												$wp_roles -> remove_cap($role_key, 'slideshow_' . $section_key);
												
												if (!empty($permissions[$role_key]) && in_array($section_key, $permissions[$role_key])) {
													$wp_roles -> add_cap($role_key, 'slideshow_' . $section_key);
												}
												
												if ($role_key == "administrator") {
													$wp_roles -> add_cap("administrator", 'slideshow_' . $section_key);
													$permissions[$role_key][] = $section_key;
												}
											}
										}
									}
									
									$this -> update_option('permissions', $permissions);
									break;
								default						:								
									$this -> update_option($pkey, $pval);
									break;
							}
						}
						
						if (!$this -> ci_serial_valid()) {
							$this -> update_option('effect', "slide");
							$this -> update_option('easing', "swing");
							$this -> update_option('infodelay', "0");
							$this -> delete_option('infohideonmobile');
							$this -> delete_option('excerptsettings');
							$this -> update_option('imagesthickbox', "N");
							$this -> delete_option('thumbhideonmobile');
						}
						
						$message = __('Configuration has been saved', 'slideshow-gallery');
						$this -> render_msg($message);
					}	
					
					$this -> render('settings', false, true, 'admin');
					break;
			}
		}
		
		function activation_hook() {
			$this -> add_option('activation_redirect', true);
		}
		
		function custom_redirect() {
		
			$method = esc_html($_GET['slideshow_method']);
			if (!empty($method)) {
				switch ($method) {
					case 'hidemessage'					:
						if (!empty($_GET['message'])) {
							switch ($_GET['message']) {
								case 'upgradetopro'				:
									$this -> update_option('hidemessage_upgradetopro', true);
									break;
								case 'ratereview'				:
									$this -> delete_option('showmessage_ratereview');
									$this -> redirect($this -> referer);
									break;
							}
						}
						break;
				}
			}
			
			$activation_redirect = $this -> get_option('activation_redirect');
			if (is_admin() && !empty($activation_redirect)) {
				$this -> delete_option('activation_redirect');
				wp_cache_flush();
				wp_redirect(admin_url('index.php?page=' . $this -> sections -> about));
			}
		}
	}
}

function SG($param = null) {
	return new SlideshowGallery($param);
}

//initialize a Gallery object
global $Gallery;
$Gallery = new SlideshowGallery();
register_activation_hook(plugin_basename(__FILE__), array($Gallery, 'initialize_options'));
register_activation_hook(plugin_basename(__FILE__), array($Gallery, 'activation_hook'));

if (!function_exists('slideshow')) {
	function slideshow($output = true, $gallery_id = null, $post_id = null, $params = array()) {
		$params['gallery_id'] = $gallery_id;
		$params['post_id'] = $post_id;
	
		$Gallery = new SlideshowGallery();
		$content = $Gallery -> embed($params, false);
		
		if ($output == true) {
			echo $content;
		} else {
			return $content;
		}
	}
}

?>