=== ThinkTwit ===
Contributors: stephen.pickett
Author URI: http://www.thepicketts.org
Tags: twitter, tweet, thinktwit
Requires at least: 2.8.6
Tested up to: 2.9.2
Stable tag: trunk

A sidebar widget that outputs Twitter tweets. It is highly customisable and, unlike other plugins, allows output from multiple Twitter
users.


== Description ==

ThinkTwit uses the Twitter ATOM API to display recent tweets from one or more Twitter users. It is very simple, yet flexible 
and easily customised. It can be placed on your Wordpress page simply through drag and drop on the Widgets interface.

Plugin URI: http://www.thepicketts.org/thinktwit/

Features:
--------
 * Configure from Widgets settings
 * Multiple instances can be deployed (like other widgets)
 * JavaScript is not required (unless no-caching is activated)
 * Can specify multiple usernames
 * Can specify maximum number of tweets
 * Easy to configure and customise (through CSS)
 * Supports no-caching, to prevent caching of tweets by caching engines such as WP Super Cache
 * Supports CURL as an alternative to access the Twitter API if URL file-access is disabled
 
Requirements/Restrictions:
-------------------------
 * Works with Wordpress 2.8.6 to 2.9.2, not tested with other versions
 * Must be installed using the widgets sidebar


== Installation ==

1. Unpack the zip file and upload the `thinktwit` folder to the `/wp-content/plugins/` directory, or download through the `Plugins` menu 
in WordPress

1. Activate the plugin through the `Plugins` menu in WordPress

1. Go to `Appearance` and then `Widgets` and drag `ThinkTwit` to your sidebar

1. Fill in the options as required and then save

Updates are automatic. Click on `Upgrade Automatically` if prompted from the admin menu. If you ever have to manually 
upgrade, simply replace the files with those from the new version.

NOTE: For those inexperienced with CSS, simply add the following to the bottom of your CSS file for basic formatting:

`/* ThinkTwit - Twitter Widget */

ol.thinkTwitTweets {
    font-size          : 12px;
}

ol.thinkTwitTweets li.thinkTwitTweet {
    list-style         : none;
    word-wrap          : break-word;
}

ol.thinkTwitTweets li.thinkTwitTweet span.thinkTwitPublished {
    display            : block;
}`

= To uninstall versions prior to 1.1.0: =
  ------------------------------------
To uninstall simply deactivate, delete the `thinktwit` directory from `wp-content/plugins/` and then delete the following options 
from the `wp_options` table:

 * thinkTwit_title
 * thinkTwit_usernames
 * thinkTwit_limit
 * thinkTwit_showUsername
 * thinkTwit_showPublished
 * thinkTwit_linksNewWindow
 * thinkTwit_widgetPrefix
 * thinkTwit_tweetPrefix
 * thinkTwit_usernameSuffix
 * thinkTwit_tweetSuffix
 * thinkTwit_publishedPrefix
 * thinkTwit_publishedSuffix
 * thinkTwit_widgetSuffix

= To uninstall versions 1.1.0 and above: =
  -------------------------------------
To uninstall simply deactivate, delete the `thinktwit` directory from `wp-content/plugins/` and then delete the following option 
from the `wp_options` table:

 * widget_thinktwit


== Frequently Asked Questions ==

= How many tweets will be shown? =

This is determined by your settings within the widget. The default is set to 5.

= How long back will tweets be shown for? =

ThinkTwit will show tweets that have occurred in the last 7 days, due to restrictions in the Twitter API.

= What will happen if I haven't tweeted in the last 7 days? =

A message will be shown stating: "There have been no tweets for the past 7 days"

= How can I style ThinkTwit? =

ThinkTwit uses the widget API so should be style correctly by your theme. You may, however, wish to make minor CSS changes. If so you
should find the following information handy:

* ThinkTwit essentially outputs tweets as a list
* The list container can be access using ul.thinkTwitTweets
* Each tweet can be accessed using ul.thinkTwitTweets li.thinkTwitTweet
* The author within a tweet can be accessed using ul.thinkTwitTweets li.thinkTwitTweet a.thinkTwitAuthor
* The published time within a tweet can be accessed using ul.thinkTwitTweets li.thinkTwitTweet span.thinkTwitPublished
* The "no tweets" message can be accessed using ul.thinkTwitTweets li.thinkTwitNoTweets

= How do I stop caching in caching engines such as WP Super Cache? =

Turn on no-caching.

= How does no-caching work? =

Instead of outputting HTML it outputs Javascript. The Javascript uses AJAX (via jQuery) to make a call to a method that returns the 
HTML which is then inserted in to the correct location.

= I'm using no-caching but nothing appears below the title =

Your theme is probably not setup properly. AJAX requires a location to insert the returned data from the server-side call. ThinkTwit
puts it in to the div that contains the widget. A Wordpress theme written correctly should output a unique id for each widget that is
output. Inform the maker of your theme to have the following (or something similar) in their register_sidebar function:

`'before_widget' => '<div id="%1$s" class="widget %2$s">'`

= Why do I get this error? Warning: file_get_contents() [function.file-get-contents]: URL file-access is disabled in the server configuration =

You are getting this error because the allow_url_fopen option is disabled on your server. You can resolve this by either enabling it, or
if you are unable to do this (it may be a shared server) you can enable CURL in the widget options.


== Screenshots ==

1. screenshot-1.png shows the plugin working on the ThinkCS homepage
1. screenshot-2.png shows the settings that can be configured within the widget


== Changelog ==

= 1.1.4 =
- (09 Mar 2010) Removed some extranous code, added option to show username or Twitter name and changed list to ordered list for semantics

= 1.1.3 =
- (03 Mar 2010) Minor change where a variable was being over-written but it had no real affect and updated screenshot-2.png

= 1.1.2 =
- (03 Mar 2010) Added no-caching (to prevent ThinkTwit from being cached by caching engines), an option to use CURL to access
the Twitter API, optional debug messages, updated readme and moved development to http://www.thepicketts.org

= 1.1.1 =
- (16 Feb 2010) Removed unnecessary PHP command that was causing annoying error in widget screen (though not causing a problem) and
updated readme with new FAQ and uninstall instructions

= 1.1.0 =
- (11 Feb 2010) MAJOR UPDATES:
* Rewritten 80% of the code to correctly use widget API
* No longer need to spexify suffixes and prefixes - correctly hardcoded to use unordered lists
* Added classes for more flexible CSS changes
* Original settings page removed - all settings now made in widget NOTE: TAKE NOTE OF YOUR SETTINGS BEFORE UPDATING!
* Updated default CSS for basic use - please replace original with this and update as necessary
* No break spaces ("&nbsp;") no longer required to replace spaces in settings
* FAQ removed as there was only one question that is no longer relevant
* Updated screenshots to reflect new settings configuration

= 1.0.6 =
- (09 Feb 2010) Added temporary "no tweets in the last 7 days" notice (if no tweets are visible) until caching is implemented

= 1.0.5 =
- (09 Feb 2010) Fixed title not being saved in Settings, also added option to open links in new window and added FAQ to the readme

= 1.0.4 =
- (04 Feb 2010) Added option to change the widget title and replaced spaces with no-break spaces (&nbsp;) in default username suffix

= 1.0.3 =
- (03 Feb 2010) Removed some spaces at top of file that may be causing issues for some people

= 1.0.2 =
- (03 Feb 2010) Removed automatic deletion of database fields on deactivation and updated readme

= 1.0.1 =
- (27 Jan 2010) Fixed incorrect output of ampersands and apostrophes

= 1.0 =
- (21 Jan 2010) Initial Release