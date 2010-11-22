=== Plugin Name ===
Contributors: aut0poietic
Donate link: http://aut0poietic.us/applications/network-event-calendar/
Tags: event, calendar, mult-site, mu
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: 0.8.7

A simple event calendar for WordPress 3.0 MultiSite. 

== Description ==

The Network Event Calendar is a very simple Event Listing and Management plugin. What makes this plugin different from the multitude of other plugins that do the same job is that this plugin is WordPress 3.0.1 Multi-Site Network Aware.

*	All sub-sites in the network can create events for their own personal events. These work similar to posts, except you should set the "Publish" date for the date of your event.
*	The primary site (located in the root / of the installation) can also create events; However, when the root site creates an event, that event is considered "Global" and appears in the listing of all sub-sites.
*	When a sub-site creates an event, they can elect to make that event "global" and appear in all sites event listings.
*	The events are listed on special pages that you create using WordPress Pages, adding a special code to the page to manage the appearance of the event listing/calendar. The listing is VERY configurable: You can show events from just one site, all sites, or mix them in any way.
*	The special event listing code (known as a "shortcode") is completely built into the WordPress UI as a wizard: If you've added an image to a page, you can add an event listing.
*	The Plugin contains a side-bar Widget with the same flexibility as the shortcode: You can tweak the view to show what you want.
*	For the theme developer, the plugin exposes API methods that mimic the behavior of standard WordPress methods; the_events, get_the_events and get_total_events. <a href="http://aut0poietic.us/network-event-calendar-api-documentation/">Full API documentation exists</a>.

<a href="http://aut0poietic.us/applications/network-event-calendar/">Full documentation, examples and usage and themeing guides are available</a>

== Installation ==

1. Make sure you're using at least WordPress 3.0.1 (you can find the version number in the lower right hand corner of your WordPress Dashboard).
2. For this plugin to be of any use, you must be using the network (aka MultiSite or MU) configuration. Not sure? <a href="http://codex.wordpress.org/Create_A_Network">Check the documentation.</a>
3. Download the plugin and unzip the files to a familiar location, such as your Desktop or Documents folder.
4. Upload the "network-event-calendar" you created when unzipping the plugin to your 'wp-content/plugins' directory to your plugins directory. It will create a 'wp-content/plugins/network-event-calendar/' directory.
5. Log-in to your WordPress site as a SuperAdmin and then go to your main / root site Dashboard. Select "Plugins" and locate the entry for "Network Event Calendar." You may need to click "Inactive" to show the inactive plugins.
6. Click "Network Activate" to activate the plugin. If you do not see "Network Activate" as an option, you are either not running a Network/MultiSite installation, not logged in as SuperAdmin, or not viewing the root ( / ) site.

== Future ==

Consider this plugin "Beta" (in the Google sense) at version 0.8.0 - I will be maintaining the plugin and adding features as I  get requests and time. <a href="http://aut0poietic.us/network-event-calendar-feature-requests/">Just leave a comment on the Feature Request page</a>; If it makes sense to me and does not conflict with my needs for the plugin, I'll be happy add the feature.

* The event listing is un-styled;  this version does not include a style sheet and requires that you style the listing by editing your theme stylesheet. <a href="http://aut0poietic.us/how-to-style-the-network-event-calendar/">However, there is a view styling guide available to help get you going.</a>

* Currently, events are pretty basic: title, text, excerpt, comments and global options only. This will get beefed up as things move along (and users make comments/suggestions).

* This plugin will not function as a MUST-USE plugin, nor will it work correctly if you have moved your wp-admin, wp-content, or wp-content/plugins folders.

* This plugin requires the latest and greatest of both PHP and WordPress: PHP 5 and WordPress 3.0.1.

== Updating 0.8.0 ==

When upgrading from 0.8.0, do not deactivate the plugin -- Just replace the files on the server. See <a href="http://aut0poietic.us/2010/10/06/network-event-calendar-0-8-0-upgrade/">this note on aut0poietic.us for full details.</a>

== Changelog ==
= 0.8.7 =
* Added POT File to translations folder
* Fixed php short tag inclusion on shortcode_handler.php

= 0.8.4 =
* Removed register_deactivation_hook
* Fixed delete/deactivate sub-site entry from root blog
* Fixed CSS Extended Class Names (quotation issue)
* Check for disassociated Event Document Type on PUBLISH/SAVE/UPDATE and restore Global Look-up entry.

= 0.8.0 =
* Initial Release
