=== ThinkTwit ===
Contributors: stephen.pickett
Author URI: http://www.thinkcs.org/meet-the-team/stephen-pickett/
Tags: twitter, tweet, thinktwit
Requires at least: 2.8.6
Tested up to: 2.9.1
Stable tag: trunk

Outputs the specified number of tweets from the specified Twitter usernames.


== Description ==

ThinkTwit uses the Twitter ATOM API to display recent tweets from one or more Twitter users. It is very simple, yet flexible 
and easily customised. It can be placed on your Wordpress page simply through drag and drop on the Widgets interface.

Plugin URI: http://www.thinkcs.org/about/think-digital/digital-services/thinktwit/

Features:
--------
 * Configure from Admin panel
 * JavaScript is not required
 * Can specify multiple usernames
 * Can specify maximum number of tweets
 * Easy to configure and customise (through CSS)
 
Requirements/Restrictions:
-------------------------
 * Works with Wordpress 2.8.6 to 2.9.1, not tested with other versions
 * Must be installed using the widgets sidebar


== Installation ==

1. Unpack the zip file and upload the `thinktwit` folder to the `/wp-content/plugins/` directory, or download through the `Plugins` menu in WordPress

2. Activate the plugin through the `Plugins` menu in WordPress

3. Expand `Settings` and then click `ThinkTwit`. Fill in the options as required and then save

4. Go to `Appearance` and then `Widgets` and drag `ThinkTwit` to your sidebar

Updates are automatic. Click on `Upgrade Automatically` if prompted from the admin menu. If you ever have to manually 
upgrade, simply replace the files with those from the new version.

NOTE: For those inexperienced with CSS, simply add the following to the bottom of your CSS file for basic formatting:

/* ThinkTwit - Twitter Widget */
div#thinktwit {       /* main widget container */
    background         : #FFFFFF;
    color              : #000000;
    font-size          : 12px;
    margin             : 0 0 10px;
    padding            : 10px;
}

div#thinktwit h2 {    /* title */
    margin             : 0 0 10px;
}

div#thinktwit ul {    /* container of tweets */
    margin             : 0;
}

div#thinktwit ul li { /* individual tweets */
    margin             : 0 0 10px;
    padding            : 0;
    word-wrap          : break-word;
}

Uninstall:
----------
To uninstall simply deactivate and then delete the following options from the `wp_options` table:

 * thinkTwit_title
 * thinkTwit_usernames
 * thinkTwit_limit
 * thinkTwit_showUsername
 * thinkTwit_showPublished
 * thinkTwit_widgetPrefix
 * thinkTwit_tweetPrefix
 * thinkTwit_usernameSuffix
 * thinkTwit_tweetSuffix
 * thinkTwit_publishedPrefix
 * thinkTwit_publishedSuffix
 * thinkTwit_widgetSuffix


== Frequently Asked Questions ==

= How do I insert spaces in the settings? =

Wordpress doesn't seem to like spaces unless they are between words - it removes them when saving in the database. Instead please 
use "&nbsp;" (without the quotes) in place of each individual space at the start or end of a sentence e.g. "There is no space at the 
beginning, but there is one at the end&nbsp;".


== Screenshots ==

1. screenshot-1.png shows the plugin working on the ThinkCS homepage
2. screenshot-2.png shows where the settings are found
1. screenshot-3.png shows the settings that can be configured


== Changelog ==

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