=== Hippies MultiFeed ===
Contributors: christianbolstad
Tags: news, feeds, rss
Requires at least: 3.3.0
Tested up to: 3.3.2
Stable tag: 1.1

A system that enables users to display items from multiple RSS-feeds within the same widget. It is based on KNR MultiFeed by k_nitin_r but is updated 
with support for wordpress core functions as fetching objects via the HTTP API (to enable proxy support) and internationalization support. 


== Description ==

The Hippies MultiFeed plugin enables users to display multiple news feeds within the same widget. The widget randomizes the items displayed in the widget or displaying them in chronological order (experimental).

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload hip-multifeed files to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Hippies MultiFeed used with a shortcode and as a widget

== Frequently Asked Questions ==

= How can I use a shortcode? =

In a page or a post, simply add your shortcode in as follows

Example 1

[hipmultifeed]
http://wordpress.org/news/feed/
[/hipmultifeed]

Example 2

[hipmultifeed itemlimit="20" selecttype="Chronological"]
http://wordpress.org/news/feed/
http://ma.tt/feed/
[/hipmultifeed]

= Where can I ask questions? =

Shoot an email to christian@carnaby.se

== Changelog ==


= 1.1 =
	- Feature: Now possible to make string templates 
	- Bugfix: Saving form data in widgets 
	- Bugfix: Rename classes to avoid conflict with KNR Multifeeds 		

= 1.0 =
* The first version, adding additional features to KNR MultiFeed and changes internal behavuiour.
	- Doesn't store the cache files in the plugins own directory, uses the path given by sys_get_temp_dir() instead. 
	- Uses Wordpress native HTTP API to fetch feeds (wp_remote_get() instead of file_get_contents())
	- Internationalization support (including swedish translation)
		
