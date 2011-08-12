=== ThinkTwit ===
Contributors: stephen.pickett
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B693F67QHAT8E
Author URI: http://www.thepicketts.org
Tags: twitter, tweet, thinktwit
Requires at least: 2.8.6
Tested up to: 3.2.1
Stable tag: trunk

A sidebar widget that outputs Twitter tweets. It is highly customisable through CSS and can be easily placed anywhere within your site. It supports
caching and no-caching (if using WP Super Cache, for instance), can use JavaScript/AJAX but is not required, and can use CURL as an alternative
connection mechanism. Unlike most other plugins it allows output from multiple Twitter users. Updated regularly!


== Description ==

ThinkTwit uses the Twitter ATOM API to display recent tweets from one or more Twitter users. It is very simple, yet flexible 
and easily customised. It can be placed on your Wordpress page simply through drag and drop on the Widgets interface or through
the use of Shortcode or PHP function calls.

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
 * Supports optional caching of tweets
 * Can be implemented using shortcode or PHP function call
 * Can display the avatar of the Twitter user
 * Output can be filtered (using apply_filters)
 
Requirements/Restrictions:
-------------------------
 * Works with Wordpress 2.8.6 to 3.1.3, not tested with other versions
 * Can be installed using the widgets sidebar
 * Can also be used via shortcode or PHP call (which can be placed anywhere)


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
    margin             : 0;
}

ol.thinkTwitTweets li.thinkTwitTweet {
    list-style         : none;
    margin             : 0 0 15px;
    word-wrap          : break-word;
}

ol.thinkTwitTweets li.thinkTwitTweet img {
    border             : 0;
    float              : left;
    margin-right       : 5px;
}

ol.thinkTwitTweets li.thinkTwitTweet span.thinkTwitPublished {
    display            : block;
    font-style         : italic;
}`

= Uninstall =
  ------------------------------------
To uninstall simply deactivate, delete the `thinktwit` directory from `wp-content/plugins/` and then delete the following option 
from the `wp_options` table:

 * widget_thinktwit
 * widget_`<widgetid>`_cache (where `<widgetid>` is the system generated id for each widget instance e.g. thinktwit-3)
 * widget_thinktwit-sc-`<unique id>`_cache (where `<unique id>` is your specified unique id for each shortcode instance e.g. thinktwit-sc-3)


== Frequently Asked Questions ==

= How many tweets will be shown? =

This is determined by your settings within the widget. The default is set to 5.

= How long back will tweets be shown for? =

If using the update frequency "Live (uncached)" ThinkTwit will show tweets that have occurred in the last 7 days, due to restrictions in the 
Twitter API. However, any other option will utilise ThinkTwit's own cache and will therefore display anything within the cache.

= What will happen if I haven't tweeted in the last 7 days? =

If you aren't caching tweets then a message will be shown stating: "There have been no tweets for the past 7 days", but if you are caching 
tweets then it will simply show the tweets stored in the cache, even if they are older than 7 days

= How can I style ThinkTwit? =

ThinkTwit uses the widget API so should be styled correctly by your theme. You may, however, wish to make minor CSS changes. If so you
should find the following information handy:

* ThinkTwit essentially outputs tweets as a list
* The list container can be accessed using `ol.thinkTwitTweets`
* All tweets can be accessed using `ol.thinkTwitTweets li.thinkTwitTweet`
* Individual tweets can be accessed using `ol.thinkTwitTweets #tweet-n` where n is the number of the tweet
* Odd and even tweets can be accessed using `ol.thinkTwitTweets li.thinkTwitOdd` and `ol.thinkTwitTweets li.thinkTwitEven` accordingly
* The author within a tweet can be accessed using `ol.thinkTwitTweets li.thinkTwitTweet a.thinkTwitAuthor`
* The author suffix within a tweet can be accessed using `ol.thinkTwitTweets li.thinkTwitTweet a.thinkTwitSuffix`
* The content of a tweet can be accessed using `ol.thinkTwitTweets li.thinkTwitTweet a.thinkTwitContent`
* The published time within a tweet can be accessed using `ol.thinkTwitTweets li.thinkTwitTweet span.thinkTwitPublished`
* The "no tweets" message can be accessed using `ol.thinkTwitTweets li.thinkTwitNoTweets`
* The AJAX error message ("Error: Unable to display tweets") can be accessed using `p.thinkTwitError'

= How do I stop caching in caching engines such as WP Super Cache? =

Turn on no-caching.

= How does no-caching work? =

Instead of outputting HTML it outputs Javascript. The Javascript uses AJAX (via jQuery) to make a call to a method that returns the 
HTML which is then inserted in to the correct location.

= I'm using no-caching but nothing appears below the title =

Your theme is probably not setup properly. AJAX requires a location to insert the returned data from the server-side call. ThinkTwit
puts it in to the div that contains the widget. A Wordpress theme written correctly should output a unique id for each widget that is
output. Inform the developer of your theme to have the following (or something similar) in their register_sidebar function:

`'before_widget' => '<div id="%1$s" class="widget %2$s">'`

= Why do I get this error? Warning: file_get_contents() [function.file-get-contents]: URL file-access is disabled in the server configuration =

You are getting this error because the allow_url_fopen option is disabled on your server. You can resolve this by either enabling it, or
if you are unable to do this (it may be a shared server) you can enable CURL in the widget options.

= Why is there no space between my tweet and the time it was tweeted? =

This is because you haven't specified a space within your CSS. One way to do this is as follows:

`ol.thinkTwitTweets li.thinkTwitTweet span.thinkTwitPublished {
  margin-left: 5px;
}`

= What is the shortcode command? =

If you wish to use shortcode to access ThinkTwit you must use the following format:

`[thinktwit unique_id=x use_curl=0|1 usernames="xxx yyy" username_suffix="xxx" limit=x update_frequency=x show_username=none|name|username 
show_avatar=0|1 show_published=0|1 links_new_window=0|1 debug=0|1 time_this_happened="xxx" time_less_min="xxx" time_min="xxx" time_more_mins="xxx" 
time_1_hour="xxx" time_2_hours="xxx" time_precise_hours="xxx" time_1_day="xxx" time_2_days="xxx" time_many_days="xxx" time_no_recent="xxx"]`

Note: Shortcodes will no longer automatically use live Twitter feeds - alter the update_frequency as required (-1 live (cached), 0 live (cached, 
otherwise enter an integer for the number of hours between updates.

= Can I output a feed anywhere without using shortcode? =

Yes you can output ThinkTwit programmatically, e.g. in a template, using the following command:

`<?php echo Foo::thinktwit_output("thinktwit-oa-" . 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22); ?>`

1. Uniqe ID: integer - You should give this a unique id for caching or styling.
1. Use CURL: boolean - Indicates whether or not to use CURL.
1. Usernames: string - The list of Twitter usernames to output tweets for.
1. Username Suffix: string - The text that should appear after a username e.g. " said: ".
1. Limit: int - The maximum number of tweets to display.
1. Update Frequency - int - Minus 1 indicates live (uncached), 0 indicates live (cached), and anything else indicates the number of 
hours between getting updates from Twitter.
1. Show Username: boolean - None indicates no username should be shown, name indicates the user's full name should be shown and
username indicates the user's username should be shown.
1. Show Avatar: boolean - Indicates whether the Twitter user's avatar should be displayed.
1. Show Published: boolean - Indicates whether the time the tweet was made should be displayed e.g. "This happened a day ago".
1. Links New Window: boolean - Indicates whether links should be opened in a new window.
1. Debug: boolean - Indicates whether to turn on debugging mode.
1. Time this happened: string - Time prefix (default: "This happened").
1. Time less min: string - Time less than 1 minute (default: "less than a minute ago").
1. Time min: string - Time approximately 1 minute ago (default: "about a minute ago").
1. Time more mins: string - Time more than 1 minute ago (default: " minutes ago").
1. Time 1 hour: string - Time approximately 1 hour ago (default: "about an hour ago").
1. Time 2 hours: string - Time approximately 2 hours ago (default: "a couple of hours ago").
1. Time precise hours: string - Time more than 2 hours ago (default: "about =x= hours ago"). NOTE: =x= should be used to insert the number of hours.
1. Time 1 day: string - Time approximately 1 day ago (default: "a day ago").
1. Time 2 days: string - Time approximately 2 days ago (default: "almost 2 days ago").
1. Time many days: string - Time more than 2 days ago (default: " days ago").
1. Time no recent: string - Output when there are no tweets to display (default: "There have been no recent tweets").

NOTE: Parameters after debug (parameter 11) can be left out if you wish to use the default values.

= I'm getting strange errors or no output =

You may need to clear and rebuild your cache. See uninstall instructions.

= How do I prevent use of nofollow tags in my URLs? =

You can apply a filter - see the following URL for an example:

http://digwp.com/2010/02/remove-nofollow-attributes-from-post-content/

= What are the options "Show when published" and "Update frequency"? =

* Show when published - indicates whether the time the tweet was made is shown e.g. "This happen 1 day ago"
* Update frequency - indicates how often Twitter should be contacted to get a list of tweets. Use this
to turn on or off caching, and to decide how often to update the cache


== Screenshots ==

1. screenshot-1.png shows ThinkTwit working as a widget on the the ThinkTwit development homepage
1. screenshot-2.png shows ThinkTwit working via shortcode within a blog post on the the ThinkTwit development homepage
1. screenshot-3.png shows ThinkTwit working via a PHP function call in the header of the ThinkTwit development homepage
1. screenshot-4.png shows the settings that can be configured within the widget


== Changelog ==

= 1.2.1 =
- (07 Aug 2011) Added donation links and expandable menus within the widget settings

= 1.2.0 =
- (05 Aug 2011) MAJOR UPDATES:
* Massive update to the readme, including updated screenshots
* Re-write of code to make better use of object orientation and private/public functions
* More flexibility in shortcodes and output anywhere function (thinktwit_output) - including ability to use caching
* Introduced ability to alter time output text e.g. "This happened 16 minutes ago"
* Added class to style error message when using AJAX
* General readability improvements to code

= 1.1.10 =
- (05 Jun 2011) Added ability to apply your own filters and added nofollow tags to links

= 1.1.9 =
- (29 May 2011) Added ability to target individual tweets, odd and even tweets, content and author suffix and linked the avatar to the 
user's profile

= 1.1.8 =
- (28 May 2011) Added ability to include the Twitter poster's avatar

= 1.1.7 =
- (14 May 2011) Fixed cache not saving with the widgetid (meaning all instances will share the same cache), allows cache size to grow 
and shrink according to the size limit in the widget settings, only outputs cached tweets by users whose name is in the usernames list
within settings and sorted methods in to alphabetical order to aid searching of methods

= 1.1.6 =
- (13 May 2011) Fixed caching to prevent over-writing of cache and ensure it instead adds them to it (removing anything at the end if 
necessary) and added live option that uses the cache (so you can check for updates and update the cache before displaying cached tweets, 
this ensures that if Twitter is not available it will still display tweets)

= 1.1.5 =
- (22 Apr 2011) Added caching of tweets (optional) and added tweet shortcodes

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

= 1.0.0 =
- (21 Jan 2010) Initial Release

== Upgrade Notice ==

= 1.2.0 =
- Most flexible version to date with correct visibility of functions to prevent overlap with other plugins - many other updates included.

= 1.1.0 =
- Correctly uses Widget API to allow multiple instantiations - many other updates included.