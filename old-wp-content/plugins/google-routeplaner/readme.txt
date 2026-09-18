=== Google Routeplanner ===
Contributors: Abandoned
Tags: route, google, map, route planner, planer, planner, route
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 4.0
License: Feel free to edit for personal use

Allows you to add one or more route planners based on Google Maps to help your users to find a specific place.

== Description ==

To help your users locate a specific place, such as your company site, the plugin integrates a Route Planner based on Google Maps in WordPress. 

The view of the plan can be quickly and easily adapt to your needs and of course it is also possible to create more then one route planner, for example to find venues. The well-known interface of Google Maps your users find quickly a way around. With a simple input field the user can specify their own location and not only gets directions but also a map with the marked path.

= Translations =
* German: Unknown
* French: Corentin Smith 
* Dutch: Unknown
* Romanian: [WebHostingGeeks.com](http://webhostinggeeks.com/)
* Slovak [WebHostingGeeks.com](http://webhostinggeeks.com/)

== Installation ==

1. Upload `google_routeplaner`-folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Usage =

After activating the plugin there is a new menu in your admin interface of WordPress called `Routeplaner`. Open the menu and choose `routes` from the submenu. 
Now click on `Create Route` and insert a destination. Change the rest of the settings like you want the map to look like. 

Save the route and you will recieve a code to insert into your page. The code looks like [googlerouteplaner=1] (the number is your routes id and changes for every route). Just insert this code to your page or post - done.

== Upgrade Notice ==

If you encounter any problems after updating please first try to deactivate the plugin and activate it again.
Maybe you need to uninstall and reinstall the plugin if you update from a very old version.


== Frequently Asked Questions ==

= Can I add multiple maps to a single page? =

Yes, this works since version 1.5 but it's not possible to use different languages for routes on one page.


== Changelog ==

= 4.0 = 
* Fix for update problems
* Project now abandoned - no more support, no more ads, no more donations!

= 3.6 = 
* Fix for the foreach-error
* Import should now work or give an error message

= 3.5 = 
* New and way more flexible database structure
* You can now use a Google API Key if you want
* Maps now have a title that can be different from the destination
* You can now define a display destination. This is shown instead of the route destination, for example if you have to use longitude and latitude.
* Added pan control options

= 3.1 =
* Added a lot of new languages which Google supports
* Label and button of the maps are now translated with a PHP-file. This should make it a lot easier	to translate and to have different languages for maps on the same WordPress installation.
* Added an integrity check to solve database issues.
						
= 3.0 =
* Donation settings have been changed. Link can be removed without donating
* Viewport can now be disabled
* Small graphical tweaks
* Troubleshooting page added
* Autodetection can now give longitude and latitude or country, city and street

= 2.6 =
* Hotfix for trouble with version 2.5

= 2.5 =
* French translation added, thanks to Corentin Smith 
* Slovak Translation by [WebHostingGeeks.com](http://webhostinggeeks.com/blog/) added
* You can now define map height and width in px, % or em
* New feature: HTML5 detection for users location to autofill the form

= 2.3 =
* WordPress Multisite support
* Added default values for zoom level to prevent errors
* New Icon
* You are now asked if you really want to delete a route

= 2.2 =
* Donation settings have been changed. Please take a look at them!
* Fix for install error (which did not effect function)</li>
* Small visual improvments
* Documentation page added

= 2.1 =
* Fixed a bug where installation went wrong and routes could not be added

= 2.0 =
* Issues with writing to the database some users had fixed
* Removed a lot of potential issues other plugins may cause
* Zoom is now adjustable
* Added a marker to the map when no route is calculated

= 1.5 =
* Added some icons
* Made it easier to copy the routes codes
* You can now use more then one map on the same page (but only one language is possible at once)
* Removed CSS additions due it only caused invalid HTML code
* Due to the updates you can now change the look using your themes CSS file.

= 1.21 =
* Hotfix for not working setup

= 1.2 =
* You can now select a different language for every map
* Fixed a compatiblity issues with the TheCartPress

= 1.1 =
* Cleaned up Sourcecode
* Removed some validation issues (there are still some remaining)

= 1.0 =
* Updated to Google Maps API V3
* No more API Key required
* Overview Map no longer available due to it's not available in Google Maps API V3
* Improved design

= 0.6 =
* Displays the code for each route in the admin section
* Added a preview function to the admin section

= 0.5 =
* Plugin release

== Screenshots ==

1. screenshot-1.jpg
2. screenshot-2.jpg
3. screenshot-3.jpg
4. screenshot-4.jpg