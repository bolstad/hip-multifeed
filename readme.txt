=== Hippies MultiFeed ===
Contributors: christianbolstad
Tags: news, feeds, rss
Requires at least: 3.3.0
Tested up to: 3.3.1
Stable tag: 1.0

A system that enables users to display items from multiple RSSfeeds within the same widget. Based on KNR MultiFeed by k_nitin_r. 

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

= 1.0 =
* The first version, adding additional features to KNR MultiFeed and changes internal behavuiour.
	- Doesn't store the cache files in the plugins own directory, uses the path given by sys_get_temp_dir() instead. 
	- Uses Wordpress native HTTP API to fetch feeds (wp_remote_get() instead of file_get_contents())
	- Internationalization support (including swedish translation)
		
