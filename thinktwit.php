<?php
/*
    Plugin Name: ThinkTwit
    Plugin URI: http://www.thepicketts.org/thinktwit/
    Description: Outputs tweets from any Twitter users (hashtag filterable) through the Widget interface. Can be called via shortcode or PHP function call
    Version: 1.3.8
    Author: Stephen Pickett
    Author URI: http://www.thepicketts.org/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	define("VERSION",				"1.3.8");
	define("USERNAMES", 			"stephenpickett");
	define("HASHTAGS", 				"");
	define("USERNAME_SUFFIX", 		" said: ");
	define("LIMIT", 				5);
	define("MAX_DAYS", 				7);
	define("UPDATE_FREQUENCY", 		0);
	define("SHOW_USERNAME", 		"name");
	define("SHOW_AVATAR", 			1);
	define("SHOW_PUBLISHED", 		1);
	define("SHOW_FOLLOW",    		1);
	define("LINKS_NEW_WINDOW", 		1);
	define("NO_CACHE", 				0);
	define("USE_CURL", 				0);
	define("DEBUG", 				0);
	define("TIME_THIS_HAPPENED",	"This happened ");
	define("TIME_LESS_MIN",      	"less than a minute ago");
	define("TIME_MIN",           	"about a minute ago");
	define("TIME_MORE_MINS",     	" minutes ago");
	define("TIME_1_HOUR",        	"about an hour ago");
	define("TIME_2_HOURS",       	"a couple of hours ago");
	define("TIME_PRECISE_HOURS", 	"about =x= hours ago");
	define("TIME_1_DAY",         	"yesterday");
	define("TIME_2_DAYS",        	"almost 2 days ago");
	define("TIME_MANY_DAYS",     	" days ago");
	define("TIME_NO_RECENT",     	"There have been no recent tweets");

	class ThinkTwit extends WP_Widget {	
		// Returns the current ThinkTwit version
		public static function get_version() {
			return VERSION;
		}
		
		// Constructor
		public function ThinkTwit() {
			// Set the description of the widget
			$widget_ops = array("description" => "Outputs tweets from one or more Twitter users through the Widget interface, filtered on a particular #hashtag(s)");

			// Load jQuery
			wp_enqueue_script("jquery");
			
			// Load stylesheet
			$thinktwit_style_url = plugins_url("thinktwit.css", __FILE__); // Respects SSL, stylesheet is relative to the current file
			$thinktwit_style_file = WP_PLUGIN_DIR . "/thinktwit/thinktwit.css";
			
			if (file_exists($thinktwit_style_file)) {
				wp_register_style("thinktwit", $thinktwit_style_url);
				wp_enqueue_style("thinktwit");
			}
			
			// Override the default constructor, passing the name and description
			parent::WP_Widget("thinkTwit", $name = "ThinkTwit", $widget_ops);
		}

		// Display the widget
		public function widget($args, $instance) {
			extract($args);

			// Get the div id of the widget
			$widget_id        = $args["widget_id"];

			// Store the widget values in variables
			$title            = apply_filters("widget_title", $instance["title"]);
			$usernames        = !isset($instance["usernames"])			? USERNAMES : $instance["usernames"];
			$hashtags  	      = !isset($instance["hashtags"])			? HASHTAGS : $instance["hashtags"];
			$username_suffix  = !isset($instance["username_suffix"])	? USERNAME_SUFFIX : $instance["username_suffix"];
			$limit            = !isset($instance["limit"])				? LIMIT : $instance["limit"];
			$max_days         = !isset($instance["max_days"])			? MAX_DAYS : $instance["max_days"];
			$update_frequency = !isset($instance["update_frequency"])	? UPDATE_FREQUENCY : $instance["update_frequency"];
			$show_username    = !isset($instance["show_username"])		? SHOW_USERNAME : $instance["show_username"];
			$show_avatar      = !isset($instance["show_avatar"])		? SHOW_AVATAR : $instance["show_avatar"];
			$show_published   = !isset($instance["show_published"])		? SHOW_PUBLISHED : $instance["show_published"];
			$show_follow      = !isset($instance["show_follow"])		? SHOW_FOLLOW : $instance["show_follow"];
			$links_new_window = !isset($instance["links_new_window"])	? LINKS_NEW_WINDOW : $instance["links_new_window"];
			$no_cache         = !isset($instance["no_cache"])			? NO_CACHE : $instance["no_cache"];
			$use_curl         = !isset($instance["use_curl"])			? USE_CURL : $instance["use_curl"];
			$debug            = !isset($instance["debug"])				? DEBUG : $instance["debug"];
			
			// Times
			$time_settings = array(11);
			$time_settings[0] = !isset($instance["time_this_happened"])	? TIME_THIS_HAPPENED : $instance["time_this_happened"];
			$time_settings[1] = !isset($instance["time_less_min"])		? TIME_LESS_MIN : $instance["time_less_min"];
			$time_settings[2] = !isset($instance["time_min"])			? TIME_MIN : $instance["time_min"];
			$time_settings[3] = !isset($instance["time_more_mins"])		? TIME_MORE_MINS : $instance["time_more_mins"];
			$time_settings[4] = !isset($instance["time_1_hour"])		? TIME_1_HOUR : $instance["time_1_hour"];
			$time_settings[5] = !isset($instance["time_2_hours"])		? TIME_2_HOURS : $instance["time_2_hours"];
			$time_settings[6] = !isset($instance["time_precise_hours"])	? TIME_PRECISE_HOURS : $instance["time_precise_hours"];
			$time_settings[7] = !isset($instance["time_1_day"])			? TIME_1_DAY : $instance["time_1_day"];
			$time_settings[8] = !isset($instance["time_2_days"])		? TIME_2_DAYS : $instance["time_2_days"];
			$time_settings[9] = !isset($instance["time_many_days"])		? TIME_MANY_DAYS : $instance["time_many_days"];
			$time_settings[10]= !isset($instance["time_no_recent"])		? TIME_NO_RECENT : $instance["time_no_recent"];
			
			// Output code that should appear before the widget
			echo $before_widget;

			// If there is a title output it with before and after code
			if ($title)
				echo $before_title . $title . $after_title;

			// If the user selected to not cache the widget then output AJAX method
			if ($no_cache) { 
				echo ThinkTwit::output_ajax($widget_id, $usernames, $hashtags, $username_suffix, $limit, $max_days, $update_frequency, $show_username, $show_avatar, $show_published, $show_follow, $links_new_window, $use_curl, $debug, $time_settings);
			// Otherwise output HTML method
			} else {
				echo ThinkTwit::parse_feed($widget_id, $usernames, $hashtags, $username_suffix, $limit, $max_days, $update_frequency, $show_username, $show_avatar, $show_published, $show_follow, $links_new_window, $use_curl, $debug, $time_settings);
			}
			
			// Output code that should appear after the widget
			echo $after_widget;
		}

		// Update the widget when editing through admin user interface
		public function update($new_instance, $old_instance) {
			$instance = $old_instance;

			// Strip tags and update the widget settings
			$instance["title"]              = strip_tags($new_instance["title"]);
			$instance["usernames"]          = strip_tags($new_instance["usernames"]);
			$instance["hashtags"]           = strip_tags($new_instance["hashtags"]);
			$instance["username_suffix"]    = strip_tags($new_instance["username_suffix"]);
			$instance["limit"]              = strip_tags($new_instance["limit"]);
			$instance["max_days"]           = strip_tags($new_instance["max_days"]);
			$instance["update_frequency"]   = strip_tags($new_instance["update_frequency"]);
			$instance["show_username"]      = strip_tags($new_instance["show_username"]);
			$instance["show_avatar"]        = (strip_tags($new_instance["show_avatar"]) == "Yes" ? 1 : 0);
			$instance["show_published"]     = (strip_tags($new_instance["show_published"]) == "Yes" ? 1 : 0);
			$instance["show_follow"]        = (strip_tags($new_instance["show_follow"]) == "Yes" ? 1 : 0);
			$instance["links_new_window"]   = (strip_tags($new_instance["links_new_window"]) == "Yes" ? 1 : 0);
			$instance["no_cache"]           = (strip_tags($new_instance["no_cache"]) == "Yes" ? 1 : 0);
			$instance["use_curl"]           = (strip_tags($new_instance["use_curl"]) == "Yes" ? 1 : 0);
			$instance["debug"]              = (strip_tags($new_instance["debug"]) == "Yes" ? 1 : 0);
			$instance["time_this_happened"] = strip_tags($new_instance["time_this_happened"]);
			$instance["time_less_min"]      = strip_tags($new_instance["time_less_min"]);
			$instance["time_min"]           = strip_tags($new_instance["time_min"]);
			$instance["time_more_mins"]     = strip_tags($new_instance["time_more_mins"]);
			$instance["time_1_hour"]        = strip_tags($new_instance["time_1_hour"]);
			$instance["time_2_hours"]       = strip_tags($new_instance["time_2_hours"]);
			$instance["time_precise_hours"] = strip_tags($new_instance["time_precise_hours"]);
			$instance["time_1_day"]         = strip_tags($new_instance["time_1_day"]);
			$instance["time_2_days"]        = strip_tags($new_instance["time_2_days"]);
			$instance["time_many_days"]     = strip_tags($new_instance["time_many_days"]);
			$instance["time_no_recent"]     = strip_tags($new_instance["time_no_recent"]);

			return $instance;
		}

		// Output admin form for updating the widget
		public function form($instance) {
			// Set up some default widget settings
			$defaults = array("title"              => "My Tweets",
							  "usernames"          => USERNAMES,
							  "hashtags"           => HASHTAGS,
							  "username_suffix"    => USERNAME_SUFFIX,
							  "limit"              => LIMIT,
							  "max_days"           => MAX_DAYS,
							  "update_frequency"   => UPDATE_FREQUENCY,
							  "show_username"      => SHOW_USERNAME,
							  "show_avatar"        => SHOW_AVATAR,
							  "show_published"     => SHOW_PUBLISHED,
							  "show_follow"        => SHOW_FOLLOW,
							  "links_new_window"   => LINKS_NEW_WINDOW,
							  "no_cache"           => NO_CACHE,
							  "use_curl"           => USE_CURL,
							  "debug"              => DEBUG,
							  "time_this_happened" => TIME_THIS_HAPPENED,
							  "time_less_min"      => TIME_LESS_MIN,
							  "time_min"           => TIME_MIN,
							  "time_more_mins"     => TIME_MORE_MINS,
							  "time_1_hour"        => TIME_1_HOUR,
							  "time_2_hours"       => TIME_2_HOURS,
							  "time_precise_hours" => TIME_PRECISE_HOURS,
							  "time_1_day"         => TIME_1_DAY,
							  "time_2_days"        => TIME_2_DAYS,
							  "time_many_days"     => TIME_MANY_DAYS,
							  "time_no_recent"     => TIME_NO_RECENT
							 );
							 
			$instance = wp_parse_args((array) $instance, $defaults);

		?>
			<div class="accordion">
				<h3 class="head" style="background: #F1F1F1 url(images/arrows.png) no-repeat right 4px; padding: 4px; border: 1px solid #DFDFDF;">General Settings</h3>
				<div>
					<p><label for="<?php echo $this->get_field_id("title"); ?>"><?php _e("Title:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo $instance["title"]; ?>" /></label></p>

					<p><label for="<?php echo $this->get_field_id("usernames"); ?>"><?php _e("Twitter usernames (optional) separated by spaces:"); ?> <textarea rows="4" cols="40" class="widefat" id="<?php echo $this->get_field_id("usernames"); ?>" name="<?php echo $this->get_field_name("usernames"); ?>"><?php echo $instance["usernames"]; ?></textarea></label></p>

					<p><label for="<?php echo $this->get_field_id("hashtags"); ?>"><?php _e("Twitter hashtags/keywords (optional):"); ?> <input class="widefat" id="<?php echo $this->get_field_id("hashtags"); ?>" name="<?php echo $this->get_field_name("hashtags"); ?>"  type="text" value="<?php echo $instance["hashtags"]; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("username_suffix"); ?>"><?php _e("Username suffix (e.g. \" said \"):"); ?> <input class="widefat" id="<?php echo $this->get_field_id("username_suffix"); ?>" name="<?php echo $this->get_field_name("username_suffix"); ?>" type="text" value="<?php echo $instance["username_suffix"]; ?>" /></label></p>

					<p><label for="<?php echo $this->get_field_id("limit"); ?>"><?php _e("Max tweets to display:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("limit"); ?>" name="<?php echo $this->get_field_name("limit"); ?>" type="text" value="<?php echo $instance["limit"]; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("max_days"); ?>"><?php _e("Max days to display:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("max_days"); ?>" name="<?php echo $this->get_field_name("max_days"); ?>" type="text" value="<?php echo $instance["max_days"]; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("update_frequency"); ?>"><?php _e("Update frequency:"); ?> <select id="<?php echo $this->get_field_id("update_frequency"); ?>" name="<?php echo $this->get_field_name("update_frequency"); ?>" class="widefat">
						<option value="-1" <?php if (strcmp($instance["update_frequency"], -1) == 0) echo " selected=\"selected\""; ?>>Live (uncached)</option>
						<option value="0" <?php if (strcmp($instance["update_frequency"], 0) == 0) echo " selected=\"selected\""; ?>>Live (cached)</option>
						<option value="1" <?php if (strcmp($instance["update_frequency"], 1) == 0) echo " selected=\"selected\""; ?>>Hourly</option>
						<option value="2" <?php if (strcmp($instance["update_frequency"], 2) == 0) echo " selected=\"selected\""; ?>>Every 2 hours</option>
						<option value="4" <?php if (strcmp($instance["update_frequency"], 4) == 0) echo " selected=\"selected\""; ?>>Every 4 hours</option>
						<option value="12" <?php if (strcmp($instance["update_frequency"], 12) == 0) echo " selected=\"selected\""; ?>>Every 12 hours</option>
						<option value="24" <?php if (strcmp($instance["update_frequency"], 24) == 0) echo " selected=\"selected\""; ?>>Every day</option>
						<option value="48" <?php if (strcmp($instance["update_frequency"], 48) == 0) echo " selected=\"selected\""; ?>>Every 2 days</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("show_username"); ?>"><?php _e("Show username:"); ?> <select id="<?php echo $this->get_field_id("show_username"); ?>" name="<?php echo $this->get_field_name("show_username"); ?>" class="widefat">
						<option value="none" <?php if (strcmp($instance["show_username"], "none") == 0) echo " selected=\"selected\""; ?>>None</option>
						<option value="name" <?php if (strcmp($instance["show_username"], "name") == 0) echo " selected=\"selected\""; ?>>Name</option>
						<option value="username" <?php if (strcmp($instance["show_username"], "username") == 0) echo " selected=\"selected\""; ?>>Username</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("show_avatar"); ?>"><?php _e("Show username's avatar:"); ?> <select id="<?php echo $this->get_field_id("show_avatar"); ?>" name="<?php echo $this->get_field_name("show_avatar"); ?>" class="widefat">
						<option <?php if ($instance["show_avatar"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["show_avatar"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("show_published"); ?>"><?php _e("Show when published:"); ?> <select id="<?php echo $this->get_field_id("show_published"); ?>" name="<?php echo $this->get_field_name("show_published"); ?>" class="widefat">
						<option <?php if ($instance["show_published"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["show_published"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("show_follow"); ?>"><?php _e("Show \"Follow @username\" links:"); ?> <select id="<?php echo $this->get_field_id("show_follow"); ?>" name="<?php echo $this->get_field_name("show_follow"); ?>" class="widefat">
						<option <?php if ($instance["show_follow"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["show_follow"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("links_new_window"); ?>"><?php _e("Open links in new window:"); ?> <select id="<?php echo $this->get_field_id("links_new_window"); ?>" name="<?php echo $this->get_field_name("links_new_window"); ?>" class="widefat">
						<option <?php if ($instance["links_new_window"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["links_new_window"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("no_cache"); ?>"><?php _e("Prevent caching e.g. by WP Super Cache:"); ?> <select id="<?php echo $this->get_field_id("no_cache"); ?>" name="<?php echo $this->get_field_name("no_cache"); ?>" class="widefat">
						<option <?php if ($instance["no_cache"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["no_cache"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("use_curl"); ?>"><?php _e("Use CURL for accessing Twitter API (set yes if getting `URL file-access` errors):"); ?> <select id="<?php echo $this->get_field_id("use_curl"); ?>" name="<?php echo $this->get_field_name("use_curl"); ?>" class="widefat">
						<option <?php if ($instance["use_curl"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["use_curl"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id("debug"); ?>"><?php _e("Output debug messages:"); ?> <select id="<?php echo $this->get_field_id("debug"); ?>" name="<?php echo $this->get_field_name("debug"); ?>" class="widefat">
						<option <?php if ($instance["debug"] == 1) echo "selected=\"selected\""; ?>>Yes</option>
						<option <?php if ($instance["debug"] == 0) echo "selected=\"selected\""; ?>>No</option>
					</select></label></p>
				</div>
			</div>
			
			<div class="accordion">
				<h3 class="head" style="background: #F1F1F1 url(images/arrows.png) no-repeat right 4px; padding: 4px; border: 1px solid #DFDFDF;">Time Messages</h3>
				<div>
					<p>NOTE: The editing of these messages is optional.</p>
					
					<p><label for="<?php echo $this->get_field_id("time_this_happened"); ?>"><?php _e("Time prefix:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_this_happened"); ?>" name="<?php echo $this->get_field_name("time_this_happened"); ?>" type="text" value="<?php echo $instance['time_this_happened']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_less_min"); ?>"><?php _e("Less than 59 seconds ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_less_min"); ?>" name="<?php echo $this->get_field_name("time_less_min"); ?>" type="text" value="<?php echo $instance['time_less_min']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_min"); ?>"><?php _e("Less than 1 minute 59 seconds ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_min"); ?>" name="<?php echo $this->get_field_name("time_min"); ?>" type="text" value="<?php echo $instance['time_min']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_more_mins"); ?>"><?php _e("Less than 50 minutes ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_more_mins"); ?>" name="<?php echo $this->get_field_name("time_more_mins"); ?>" type="text" value="<?php echo $instance['time_more_mins']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_1_hour"); ?>"><?php _e("Less than 89 minutes ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_1_hour"); ?>" name="<?php echo $this->get_field_name("time_1_hour"); ?>" type="text" value="<?php echo $instance['time_1_hour']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_2_hours"); ?>"><?php _e("Less than 150 minutes ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_2_hours"); ?>" name="<?php echo $this->get_field_name("time_2_hours"); ?>" type="text" value="<?php echo $instance['time_2_hours']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_precise_hours"); ?>"><?php _e("Less than 23 hours ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_precise_hours"); ?>" name="<?php echo $this->get_field_name("time_precise_hours"); ?>" type="text" value="<?php echo $instance['time_precise_hours']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_1_day"); ?>"><?php _e("Less than 36 hours:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_1_day"); ?>" name="<?php echo $this->get_field_name("time_1_day"); ?>" type="text" value="<?php echo $instance['time_1_day']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_2_days"); ?>"><?php _e("Less than 48 hours ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_2_days"); ?>" name="<?php echo $this->get_field_name("time_2_days"); ?>" type="text" value="<?php echo $instance['time_2_days']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_many_days"); ?>"><?php _e("More than 48 hours ago:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_many_days"); ?>" name="<?php echo $this->get_field_name("time_many_days"); ?>" type="text" value="<?php echo $instance['time_many_days']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id("time_no_recent"); ?>"><?php _e("No recent tweets:"); ?> <input class="widefat" id="<?php echo $this->get_field_id("time_no_recent"); ?>" name="<?php echo $this->get_field_name("time_no_recent"); ?>" type="text" value="<?php echo $instance['time_no_recent']; ?>" /></label></p>
				</div>
			</div>
			
			<h3>Support Development</h3>
			
			<p>If you would like to support development of ThinkTwit donations are gratefully accepted:</p>
			<p style="text-align:center"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B693F67QHAT8E" target="_blank"><img src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" alt="PayPal — The safer, easier way to pay online." /></a><img src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></p>
			<p><a id="widget-thinktwit-<?php $id = explode("-", $this->get_field_id("widget_id")); echo $id[2]; ?>-reset_settings" href="#">Reset Settings</a></p>
				
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					// Add accordion functionality
					$('div[id$="thinktwit-<?php echo $id[2]; ?>"] .accordion .head').click(function() {
						$(this).next().toggle('slow');
						return false;
					}).next().hide();
					
					// When reset_settings loads add the onclick function
					$("#widget-thinktwit-<?php echo $id[2]; ?>-reset_settings").live("click", function() {					  
						// Reset all of the values to their default
						$("#widget-thinktwit-<?php echo $id[2]; ?>-usernames").val("<?php echo USERNAMES; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-hashtags").val("<?php echo HASHTAGS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-username_suffix").val("<?php echo USERNAME_SUFFIX; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-limit").val("<?php echo LIMIT; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-max_days").val("<?php echo MAX_DAYS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-update_frequency").val("<?php echo UPDATE_FREQUENCY; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-show_username").val("<?php echo SHOW_USERNAME; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-show_avatar").val("<?php echo (SHOW_AVATAR ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-show_published").val("<?php echo (SHOW_PUBLISHED ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-show_follow").val("<?php echo (SHOW_FOLLOW ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-links_new_window").val("<?php echo (LINKS_NEW_WINDOW ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-no_cache").val("<?php echo (NO_CACHE ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-use_curl").val("<?php echo (USE_CURL ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-debug").val("<?php echo (DEBUG ? "Yes" : "No"); ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_this_happened").val("<?php echo TIME_THIS_HAPPENED; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_less_min").val("<?php echo TIME_LESS_MIN; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_min").val("<?php echo TIME_MIN; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_more_mins").val("<?php echo TIME_MORE_MINS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_1_hour").val("<?php echo TIME_1_HOUR; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_2_hours").val("<?php echo TIME_2_HOURS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_precise_hours").val("<?php echo TIME_PRECISE_HOURS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_1_day").val("<?php echo TIME_1_DAY; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_2_days").val("<?php echo TIME_2_DAYS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_many_days").val("<?php echo TIME_MANY_DAYS; ?>");
						$("#widget-thinktwit-<?php echo $id[2]; ?>-time_no_recent").val("<?php echo TIME_NO_RECENT; ?>");
						
						// Focus on the usernames
						$("#widget-thinktwit-<?php echo $id[2]; ?>-usernames").focus();
					
						// Return false so that the standard click function doesn't occur (i.e. navigating to #)
						return false;
					});
				});
			</script>
		<?php
		}
					
		// Function for handling AJAX requests
		public static function ajax_request_handler() {
			// Check that all parameters have been passed
			if ((isset($_GET["thinktwit_request"]) && ($_GET["thinktwit_request"] == "parse_feed")) && isset($_GET["thinktwit_widget_id"]) && 
			  isset($_GET["thinktwit_usernames"]) && isset($_GET["thinktwit_hashtags"]) && isset($_GET["thinktwit_username_suffix"]) && 
			  isset($_GET["thinktwit_limit"]) && isset($_GET["thinktwit_max_days"]) && isset($_GET["thinktwit_update_frequency"]) && 
			  isset($_GET["thinktwit_show_username"]) && isset($_GET["thinktwit_show_published"]) && isset($_GET["thinktwit_show_follow"]) && 
			  isset($_GET["thinktwit_links_new_window"]) && isset($_GET["thinktwit_use_curl"]) && isset($_GET["thinktwit_debug"]) && 
			  isset($_GET["thinktwit_time_this_happened"]) && isset($_GET["thinktwit_time_less_min"]) && isset($_GET["thinktwit_time_min"]) && 
			  isset($_GET["thinktwit_time_more_mins"]) && isset($_GET["thinktwit_time_1_hour"]) && isset($_GET["thinktwit_time_2_hours"]) && 
			  isset($_GET["thinktwit_time_precise_hours"]) && isset($_GET["thinktwit_time_1_day"]) && isset($_GET["thinktwit_time_2_days"]) && 
			  isset($_GET["thinktwit_time_many_days"]) && isset($_GET["thinktwit_time_no_recent"])) {
			  
				// Create an array to contain the time settings
				$time_settings = array(11);

				$time_settings[0] = strip_tags($_GET["thinktwit_time_this_happened"]);
				$time_settings[1] = strip_tags($_GET["thinktwit_time_less_min"]);
				$time_settings[2] = strip_tags($_GET["thinktwit_time_min"]);
				$time_settings[3] = strip_tags($_GET["thinktwit_time_more_mins"]);
				$time_settings[4] = strip_tags($_GET["thinktwit_time_1_hour"]);
				$time_settings[5] = strip_tags($_GET["thinktwit_time_2_hours"]);
				$time_settings[6] = strip_tags($_GET["thinktwit_time_precise_hours"]);
				$time_settings[7] = strip_tags($_GET["thinktwit_time_1_day"]);
				$time_settings[8] = strip_tags($_GET["thinktwit_time_2_days"]);
				$time_settings[9] = strip_tags($_GET["thinktwit_time_many_days"]);
				$time_settings[10] = strip_tags($_GET["thinktwit_time_no_recent"]);
	
			  
				// Output the feed and exit the call
				echo ThinkTwit::parse_feed(strip_tags($_GET["thinktwit_widget_id"]), strip_tags($_GET["thinktwit_usernames"]), strip_tags($_GET["thinktwit_hashtags"]), 
				  strip_tags($_GET["thinktwit_username_suffix"]), strip_tags($_GET["thinktwit_limit"]), strip_tags($_GET["thinktwit_max_days"]), 
				  strip_tags($_GET["thinktwit_update_frequency"]), strip_tags($_GET["thinktwit_show_username"]), strip_tags($_GET["thinktwit_show_avatar"]), 
				  strip_tags($_GET["thinktwit_show_published"]), strip_tags($_GET["thinktwit_show_follow"]), strip_tags($_GET["thinktwit_links_new_window"]), 
				  strip_tags($_GET["thinktwit_use_curl"]), strip_tags($_GET["thinktwit_debug"]), $time_settings);

				exit();
			} elseif (isset($_GET["thinktwit_request"]) && ($_GET["thinktwit_request"] == "parse_feed")) {
				// Otherwise display an error and exit the call
				echo "<p class=\"thinkTwitError\">Error: Unable to display tweets.</p>";
				
				exit();
			}
		}
		
		// Looks in the downloaded file for a Twitter message that says the request was redirected, if found returns the URL to use instead
		private static function check_avatar_for_redirect($location) {
			// Get the file
			$file = file_get_contents($location);
			
			// First of all look for the redirect
			if (strpos($file, "redirected")) {
				// We have found a redirect, so next look for the URL between double quotes
				if (preg_match('/"([^"]+)"/', $str, $m)) {
					// If we find a match (we should) then return the URL
					return $m[1]; 
				}
			}
			
			return false;
		}
		
		// Downloads the avatar for the given username, using CURL if specified
		private static function download_avatar($use_curl, $username) {
			// Get the URL of the poster's avatar
			$url = "http://twitter.com/api/users/profile_image/" . $username;
			
			// Get image MIME type
			$mime = ThinkTwit::get_image_mime_type($url);
			
			// Store the filename
			$filename = $username . $mime;
			$dir = plugin_dir_path( __FILE__ ) . 'images/';
			
			// First of all check if the folder exists
			if (!file_exists($dir)) {
				// If it doesn't then create it with write permissions
				wp_mkdir_p($dir);
			} else {
				// And if it exists then check it is writeable
				if (!is_writable($dir)) {
					// If it isn't writeable then make it writeable
					chmod($dir, 0777);
				}
			}
			
			while ($url) {
				// If file doesn't exist or file is older than 24 hours
				if (!file_exists($dir . $filename) || time() - filemtime(realpath($dir . $filename)) >= (60 * 60 * 24)) {					
					// Download and save the image using CURL or file_put_contents
					if ($use_curl) {
						// Initiate a CURL object and open the image URL
						$ch = curl_init($url);
						
						// Open file location to save in using write binary mode
						$fp = fopen($dir . $filename, 'wb');
						
						// Set to return a file, to write in to fp
						curl_setopt($ch, CURLOPT_FILE, $fp);
						
						// Set to not include the header in the output
						curl_setopt($ch, CURLOPT_HEADER, 0);
						
						// Execute the call
						curl_exec($ch);
						
						// Close the CURL object
						curl_close($ch);
						
						// Close the file object
						fclose($fp);
					} else {
						// Download the file without CURL
						file_put_contents($dir . $filename, file_get_contents(htmlspecialchars($url)));
					}
				}
				
				// Check the contents for a redirect (this should return false and break the loop once it has a working file)
				$url = ThinkTwit::check_avatar_for_redirect($dir . $filename);
			}
			
			return $filename;
		}
		
		// Returns the MIME type (jpeg, png or gif - only allowed by Twitter) of the image at the given URL
		private static function get_image_mime_type($url) {
			// Use getimagesize to get the MIME type
			$size = @getimagesize($url);
			
			// Return the corresponding file extension
			switch ($size['mime']) {
				case 'image/gif':
					return ".gif";
					
					break;
				case 'image/jpeg':
					return ".jpg";
					
					break;
				case 'image/png':
					return ".png";
					
					break;
				default:
					return ".jpg";
					
					break;
			}
		}

		// Returns an array of Tweets from the cache or from Twitter depending on state of cache
		private static function get_tweets($update_frequency, $url, $use_curl, $widget_id, $limit, $max_days, $usernames) {
			$tweets;

			// First check that if the user wants live updates
			if ($update_frequency == -1) {
				// If so then just get the tweets live from Twitter
				$tweets = ThinkTwit::get_tweets_from_twitter($url, $use_curl);
			} else {
				// Otherwise, get values from cache
				$last_update = ThinkTwit::get_tweets_from_cache($widget_id);
				
				// Ensure the database contained tweets
				if ($last_update != FALSE) {
					// Get the tweets from the last update
					$tweets = $last_update[0];
					
					// Get the time when the last update was cached
					$cachedTime = $last_update[1];
				} else {
					// If it didn't then create an empty array
					$tweets = array();
					
					// And store the time as zero (so it always updates)
					$cachedTime = 0;
				}
				
				// Get the difference between now and when the cache was last updated
				$diff = time() - $cachedTime;
		
				// If update is required (the number of hours since last update,
				// calculated by dividing by 60 to get mins and 60 again to get hours)
				if (($diff / 3600) > $update_frequency) {
					// Get tweets fresh from Twitter
					$fresh_tweets = ThinkTwit::get_tweets_from_twitter($url, $use_curl);
					
					// Merge all the tweets together
					$tweets = ThinkTwit::merge_tweets($tweets, $fresh_tweets);
					
					// Remove empty tweets
					$tweets = ThinkTwit::remove_empty_tweets($tweets);
					
					// Sort array by date
					ThinkTwit::sort_tweets($tweets);
					
					// Remove any tweets that are duplicates
					$tweets = ThinkTwit::remove_duplicates($tweets);
					
					// Remove any tweets that aren't in usernames
					$tweets = ThinkTwit::remove_incorrect_usernames($tweets, $usernames);
					
					// If necessary, shrink the array (limit minus 1 as we start array from zero)
					if (count($tweets) > $limit) {
						$tweets = ThinkTwit::trim_array($tweets, $limit);
					}
					
					// Store updated array in cache
					ThinkTwit::update_cache($tweets, $widget_id);
				}
			}
			
			// Remove any tweets that are older than max days
			$tweets = ThinkTwit::remove_old_tweets($tweets, $max_days);

			return $tweets;
		}
		
		// Returns an array of Tweets from the cache, along with the time of the last update
		private static function get_tweets_from_cache($widget_id) {
			// Get the option from the cache
			$tweets = get_option("widget_" . $widget_id . "_cache");
			
			return $tweets;
		}

		// Returns an array of Tweets when given the URL to access and a boolean indicating whether to use CURL
		private static function get_tweets_from_twitter($url, $use_curl) {
			// If user wishes to use CURL
			if ($use_curl) {
				// Initiate a CURL object
				$ch = curl_init();

				// Set the URL
				curl_setopt($ch, CURLOPT_URL, $url);

				// Set to return a string
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Set the timeout
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

				// Execute the API call
				$feed = curl_exec($ch);

				// Close the CURL object
				curl_close($ch);
			} else {
				// Execute the API call
				$feed = @file_get_contents($url);
			}

			// Put all entries into an array
			$clean = explode("<entry>", $feed);

			// Get the amount of entries
			$amount = count($clean) - 1;

			// Create an array to store the tweets
			$tweets = array();

			// Find out if there are any entires, if so output them
			if ($amount > 0) {
				// Loops through all the entries found in the XML feed
				for ($i = 1; $i <= $amount; $i++) {
					// Get the current entry
					$entry_close = explode("</entry>", $clean[$i]);

					// Get the content of the tweet
					$clean_content_1 = explode("<content type=\"html\">", $entry_close[0]);
					$clean_content = explode("</content>", $clean_content_1[1]);

					// Get the name of who created the tweet
					$clean_name_2 = explode("<name>", $entry_close[0]);
					$clean_name_1 = explode("(", $clean_name_2[1]);
					$clean_name = explode(")</name>", $clean_name_1[1]);

					// Get the URI of the tweet source
					$clean_uri_1 = explode("<uri>", $entry_close[0]);
					$clean_uri = explode("</uri>", $clean_uri_1[1]);

					// Get the date that the tweet was created
					$clean_published_1 = explode("<published>", $entry_close[0]);
					$clean_published = explode("</published>", $clean_published_1[1]);

					// Make the links clickable
					$clean_content[0] = str_replace("&lt;", "<", $clean_content[0]);
					$clean_content[0] = str_replace("&gt;", ">", $clean_content[0]);
					$clean_content[0] = str_replace("&amp;", "&", $clean_content[0]);
					$clean_content[0] = str_replace("&apos;", "'", $clean_content[0]);
					$clean_content[0] = str_replace("&amp;quot;", "&quot;", $clean_content[0]);
					$clean_content[0] = str_replace("&amp;lt", "<", $clean_content[0]);
					$clean_content[0] = str_replace("&amp;gt", ">", $clean_content[0]);
					$clean_content[0] = str_replace("&quot;", "\"", $clean_content[0]);
					
					$filename = ThinkTwit::download_avatar($use_curl, trim($clean_name_1[0]));

					// Create a tweet and add it to the array
					$tweets[] = new Tweet($clean_uri[0], $filename, $clean_name[0], trim($clean_name_1[0]), $clean_content[0], $clean_published[0]);;
				}
			}

			return $tweets;
		}
		
		// Inserts the tweets in array1 and array2 to a new array
		private static function merge_tweets($array1, $array2) {
			$new_array = array();
			
			// Loop through array1
			for ($i = 0; $i < count($array1); $i++) {
				// Add each item in the array in to the new array
				$new_array[] = $array1[$i];
			}
			
			// Loop through array2
			for ($i = 0; $i < count($array2); $i++) {
				// Add each item in the array in to the new array
				$new_array[] = $array2[$i];
			}
			
			return $new_array;
		}
		
		// Outputs the AJAX code to handle no-caching
		public static function output_ajax($widget_id, $usernames, $hashtags, $username_suffix, $limit, $max_days, $update_frequency, $show_username, $show_avatar, $show_published, $show_follow, $links_new_window, $use_curl, $debug, $time_settings) {
			return 
				"<script type=\"text/javascript\">
					jQuery(document).ready(function($){
						$.ajax({
							type : \"GET\",
							url : \"index.php\",
							data : { 
								thinktwit_request             : \"parse_feed\",
								thinktwit_widget_id           : \"" . $widget_id . "\",
								thinktwit_usernames           : \"" . $usernames . "\",
								thinktwit_hashtags            : \"" . $hashtags . "\",
								thinktwit_username_suffix     : \"" . $username_suffix . "\",
								thinktwit_limit               : \"" . $limit . "\",
								thinktwit_max_days            : \"" . $max_days . "\",
								thinktwit_update_frequency    : \"" . $update_frequency . "\",
								thinktwit_show_username       : \"" . $show_username . "\",
								thinktwit_show_avatar         : \"" . $show_avatar . "\",
								thinktwit_show_published      : \"" . $show_published . "\",
								thinktwit_show_follow         : \"" . $show_follow . "\",
								thinktwit_links_new_window    : \"" . $links_new_window . "\",
								thinktwit_use_curl            : \"" . $use_curl . "\",
								thinktwit_debug               : \"" . $debug . "\",
								thinktwit_time_this_happened  : \"" . $time_settings[0] . "\",
								thinktwit_time_less_min       : \"" . $time_settings[1] . "\",
								thinktwit_time_min            : \"" . $time_settings[2] . "\",
								thinktwit_time_more_mins      : \"" . $time_settings[3] . "\",
								thinktwit_time_1_hour         : \"" . $time_settings[4] . "\",
								thinktwit_time_2_hours        : \"" . $time_settings[5] . "\",
								thinktwit_time_precise_hours  : \"" . $time_settings[6] . "\",
								thinktwit_time_1_day          : \"" . $time_settings[7] . "\",
								thinktwit_time_2_days         : \"" . $time_settings[8] . "\",
								thinktwit_time_many_days      : \"" . $time_settings[9] . "\",
								thinktwit_time_no_recent      : \"" . $time_settings[10] . "\"
							},
							success : function(response) {
								// The server has finished executing PHP and has returned something, so display it!
								$(\"#" . $widget_id . "\").append(response);
							}
						});
					});
				</script>";
		}
		
		// Public accessor to output parse_feed
		public static function output_anywhere($args) {
			// Ensure each argument has a value
			if (isset($args["widget_id"])) {
				$args["widget_id"] = "thinktwit-oa-" . $args["widget_id"];
			} else {
				$args["widget_id"] = "thinktwit-oa-0";
			}
				
			if (!isset($args["usernames"]))
				$args["usernames"] = USERNAMES;
			
			if (!isset($args["hashtags"]))
				$args["hashtags"] = HASHTAGS;
				
			if (!isset($args["username_suffix"]))
				$args["username_suffix"] = USERNAME_SUFFIX;
				
			if (!isset($args["limit"]))
				$args["limit"] = LIMIT;
				
			if (!isset($args["max_days"]))
				$args["max_days"] = MAX_DAYS;
			
			if (!isset($args["update_frequency"]))
				$args["update_frequency"] = UPDATE_FREQUENCY;
			
			if (!isset($args["show_username"]))
				$args["show_username"] = SHOW_USERNAME;
			
			if (!isset($args["show_avatar"]))
				$args["show_avatar"] = SHOW_AVATAR;
			
			if (!isset($args["show_published"]))
				$args["show_published"] = SHOW_PUBLISHED;
			
			if (!isset($args["show_follow"]))
				$args["show_follow"] = SHOW_FOLLOW;
			
			if (!isset($args["links_new_window"]))
				$args["links_new_window"] = LINKS_NEW_WINDOW;
			
			if (!isset($args["no_cache"]))
				$args["no_cache"] = NO_CACHE;
			
			if (!isset($args["use_curl"]))
				$args["use_curl"] = USE_CURL;
			
			if (!isset($args["debug"]))
				$args["debug"] = DEBUG;
			
			if (!isset($args["time_this_happened"]))
				$args["time_this_happened"] = TIME_THIS_HAPPENED;
			
			if (!isset($args["time_less_min"]))
				$args["time_less_min"] = TIME_LESS_MIN;
			
			if (!isset($args["time_min"]))
				$args["time_min"] = TIME_MIN;
			
			if (!isset($args["time_more_mins"]))
				$args["time_more_mins"] = TIME_MORE_MINS;
			
			if (!isset($args["time_1_hour"]))
				$args["time_1_hour"] = TIME_1_HOUR;
			
			if (!isset($args["time_2_hours"]))
				$args["time_2_hours"] = TIME_2_HOURS;
			
			if (!isset($args["time_precise_hours"]))
				$args["time_precise_hours"] = TIME_PRECISE_HOURS;
			
			if (!isset($args["time_1_day"]))
				$args["time_1_day"] = TIME_1_DAY;
			
			if (!isset($args["time_2_days"]))
				$args["time_2_days"] = TIME_2_DAYS;
			
			if (!isset($args["time_many_days"]))
				$args["time_many_days"] = TIME_MANY_DAYS;
			
			if (!isset($args["time_no_recent"]))
				$args["time_no_recent"] = TIME_NO_RECENT;
					  		  										 
			// Create an array to contain the time settings
			$time_settings = array(11);
			
			$time_settings[0] = $args["time_this_happened"];
			$time_settings[1] = $args["time_less_min"];
			$time_settings[2] = $args["time_min"];
			$time_settings[3] = $args["time_more_mins"];
			$time_settings[4] = $args["time_1_hour"];
			$time_settings[5] = $args["time_2_hours"];
			$time_settings[6] = $args["time_precise_hours"];
			$time_settings[7] = $args["time_1_day"];
			$time_settings[8] = $args["time_2_days"];
			$time_settings[9] = $args["time_many_days"];
			$time_settings[10] = $args["time_no_recent"];
			
			// If the user selected to use no-caching output AJAX code
			if ($args["no_cache"]) { 
				return "<div id=\"" . $args["widget_id"] . "\">" . ThinkTwit::output_ajax($args["widget_id"], $args["usernames"], $args["hashtags"], $args["username_suffix"], $args["limit"], $args["max_days"], $args["update_frequency"], $args["show_username"], $args["show_avatar"], $args["show_published"], $args["show_follow"], $args["links_new_window"], $args["use_curl"], $args["debug"], $time_settings) . "</div>";
			// Otherwise output HTML method
			} else {
				return ThinkTwit::parse_feed($args["widget_id"], $args["usernames"], $args["hashtags"], $args["username_suffix"], $args["limit"], $args["max_days"], $args["update_frequency"], $args["show_username"], $args["show_avatar"], $args["show_published"], $args["show_follow"], $args["links_new_window"], $args["use_curl"], $args["debug"], $time_settings);
			}
		}
		
		// Returns the tweets, subject to the given parameters
		private static function parse_feed($widget_id, $usernames, $hashtags, $username_suffix, $limit, $max_days, $update_frequency, $show_username, $show_avatar, $show_published, 
		  $show_follow, $links_new_window, $use_curl, $debug, $time_settings) {
			
			$output = "";

			// Contstruct a string of usernames to search for
			$username_string = str_replace(" ", "+OR+from%3A", $usernames);
			
			// Replace hashes in hashtags with code for URL
			$hashtags = str_replace("#", "%23", $hashtags);
			
			// Replace spaces in hashtags with plus signs
			$hashtags = str_replace(" ", "+", $hashtags);

			// Construct the URL to obtain the Twitter ATOM feed (XML)
			$url = "http://search.twitter.com/search.atom?q=from%3A" . $username_string . "+" . $hashtags . "&rpp=" . $limit;

			// If user wishes to output debug info then do so
			if ($debug) {
				$output .= "<p>now: " . date('H:i:s', time()) . "</p>";
				$output .= "<p>widget_id: " . $widget_id . "</p>";
				$output .= "<p>use_curl: " . $use_curl . "</p>";
				$output .= "<p>usernames: " . $usernames . "</p>";
				$output .= "<p>hashtags: " . $hashtags . "</p>";
				$output .= "<p>username_suffix: " . $username_suffix . "</p>";
				$output .= "<p>limit: " . $limit . "</p>";
				$output .= "<p>max_days: " . $max_days . "</p>";
				$output .= "<p>show_username: " . $show_username . "</p>";
				$output .= "<p>show_avatar: " . $show_avatar . "</p>";
				$output .= "<p>show_published: " . $show_published . "</p>";
				$output .= "<p>show_follow: " . $show_follow . "</p>";
				$output .= "<p>links_new_window: " . $links_new_window . "</p>";
				$output .= "<p>url: " . $url . "</p>";
				$output .= "<p>debug: " . $debug . "</p>";
				$output .= "<p>time_this_happened: " . $time_settings[0] . "</p>";
				$output .= "<p>time_less_min: " . $time_settings[1] . "</p>";
				$output .= "<p>time_min: " . $time_settings[2] . "</p>";
				$output .= "<p>time_more_mins: " . $time_settings[3] . "</p>";
				$output .= "<p>time_1_hour " . $time_settings[4] . "</p>";
				$output .= "<p>time_2_hours: " . $time_settings[5] . "</p>";
				$output .= "<p>time_precise_hours: " . $time_settings[6] . "</p>";
				$output .= "<p>time_1_day: " . $time_settings[7] . "</p>";
				$output .= "<p>time_2_days: " . $time_settings[8] . "</p>";
				$output .= "<p>time_many_days: " . $time_settings[9] . "</p>";
				$output .= "<p>time_no_recent: " . $time_settings[10] . "</p>";
			}

			// Get the tweets
			$tweets = ThinkTwit::get_tweets($update_frequency, $url, $use_curl, $widget_id, $limit, $max_days, $usernames);

			// Create an ordered list
			$output .= "<ol class=\"thinkTwitTweets\">";

			// Find out if there are any tweets, if so output them
			if (count($tweets) > 0) {
				// Loop through each tweet
				for ($i = 0; $i < count($tweets); $i++) {
					// Get the current tweet
					$tweet = $tweets[$i];

					// Output the list item
					$output .= "<li id=\"tweet-" . ($i + 1) . "\" class=\"thinkTwitTweet " . (($i + 1) % 2 ? "thinkTwitOdd" : "thinkTwitEven") . "\">";

					$name = "";
					// If the user wants to output the name or username then store it
					if (strcmp($show_username, "name") == 0) {
						$name = $tweet->getName();
					} elseif (strcmp($show_username, "username") == 0) {
						$name = $tweet->getUsername();
					}

					// Output the link to the poster's profile
					$output .= "<a href=\"" . $tweet->getUrl() . "\"" . ($links_new_window ? " target=\"blank\"" : "") . " title=\"" . $name . "\" class=\"thinkTwitUsername\" rel=\"nofollow\">";
					
					// If the avatar is empty (this should only happen after an upgrade)
					if (!$tweet->getAvatar()) {
						// Download the avatar (we need the filename but we should make sure that the file is there anyway)
						$filename = ThinkTwit::download_avatar($use_curl, $tweet->getUsername());
						
						// Store the filename in the tweet
						$tweet->setAvatar($filename);
						
						// Store the tweet in the array of tweets
						$tweets[$i] = $tweet;
						
						// Update the cache with the updated tweets array
						ThinkTwit::update_cache($tweets, $widget_id);
					} else {
						// But if it does exist then get the full file path
						$file = plugin_dir_path( __FILE__ ) . 'images/' . $tweet->getAvatar();
						
						// And if the file doesn't exist
						if (!file_exists($file)) {
							// Then download it
							$filename = ThinkTwit::download_avatar($use_curl, $tweet->getUsername());
						}
					}
					
					// Get the URL of the poster's avatar
					$url = plugins_url( 'images/' . $tweet->getAvatar() , __FILE__ );

					// Check if the user wants to display the poster's avatar and that we can actually find one
					if ($show_avatar && $url != false) {
						$output .= "<img src=\"" . $url . "\" alt=\"" . $name . "\" />";
					}
					
					// Check if the user wants to output the name, username or nothing at all
					if (strcmp($show_username, "none") != 0) {
						$output .= $name;
					}
					
					// Close the link and output the suffix
					$output .= "</a><span class=\"thinkTwitSuffix\">" . $username_suffix . "</span>";

					// Surround the tweet in a span to allow targeting of the tweet
					$output .= "<span class=\"thinkTwitContent\">";
					
					// Check if the user wants URL's to open in a new window
					if ($links_new_window) {
						// Find the URL's in the content
						$url_strings = explode("href=\"", $tweet->getContent());

						// Append the first part of the content to output
						$output .= $url_strings[0];

						// Loop through each URL
						for ($j = 1; $j <= (count($url_strings) - 1); $j++) {
							// Find the position of the closing quotation mark within the current string
							$pos = strpos($url_strings[$j], "\"");

							// Append everything up to the quotation marks
							$output .=  "href=\"" . substr($url_strings[$j], 0, $pos + 1);

							// Then add the code to open a new window
							$output .= " target=\"_blank\" rel=\"nofollow\"";

							// Then add everything after
							$output .= substr($url_strings[$j], $pos + 1);
						}
					} else {
						// Otherwise simply append the content unedited
						$output .= $tweet->getContent();
					}

					// Close the span
					$output .= "</span>";

					// Check if the user wants to show the published date
					if ($show_published) {
						$output .= "<span class=\"thinkTwitPublished\">" . $time_settings[0] . ThinkTwit::relative_created_at(strtotime($tweet->getTimestamp()), $time_settings) . "</span>";
					}

					// Close the list item
					$output .= "</li>";
				}
			} else {
				// If no tweets were found output the message to say so
				$output .= "<li class=\"thinkTwitNoTweets\">" . $time_settings[10] . ".</li>";
			}

			$output .= "</ol>";
						
			// Check if the user wants to show the "Follow @username" links
			if ($show_follow) {
				// If so then output one for each username
				foreach(split(" ", $usernames) as $username) {
					$output .= "<p class=\"thinkTwitFollow\"><a href=\"https://twitter.com/" . $username . "\" class=\"twitter-follow-button\" data-show-count=\"false\" data-dnt=\"true\">Follow @" . $username . "</a></p>";
				}
			}
			
			$output .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=\"//platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>";

			return apply_filters("think_twit", $output);
		}

		// Given a PHP time this returns how long ago that time was, in easy to understand English
		private static function relative_created_at($time_to_compare, $time_settings) {
			// Get the difference between the current time and the time we wish to compare against
			$time_difference = time() - $time_to_compare;

			if ($time_difference < 59) {            // Less than 59 seconds ago
				return $time_settings[1];
			} else if ($time_difference < 119) {    // Less than 1 minute 59 seconds ago
				return $time_settings[2];
			} else if ($time_difference < 3000) {   // Less than 50 minutes ago
				return round($time_difference / 60) . $time_settings[3];
			} else if ($time_difference < 5340) {   // Less than 89 minutes ago
				return $time_settings[4];
			} else if ($time_difference < 9000) {   // Less than 150 minutes ago
				return $time_settings[5];
			} else if ($time_difference < 82800) {  // Less than 23 hours ago
				return str_replace("=x=", round($time_difference / 3600), $time_settings[6]);
			} else if ($time_difference < 129600) { // Less than 36 hours
				return $time_settings[7];
			} else if ($time_difference < 172800) { // Less than 48 hours ago
				return $time_settings[8];
			} else {                                // More than 48 hours ago
				return round($time_difference / 86400) . $time_settings[9];
			}
		}
		
		// Returns an array with duplicate tweets removed (based on timestamp)
		private static function remove_duplicates($array) {
			$new_array = array();
			
			// Iterate through item
			for($i = 0; $i < count($array); $i++) {
				// If it's the first item, or if the current item's timestamp is not equal to the previous
				if (($i == 0) || ($i > 0 && $array[$i]->getTimestamp() != $array[$i - 1]->getTimestamp())) {
					// Add it to the new array
					$new_array[] = $array[$i];
				}
			}
			
			return $new_array;
		}
		
		// Removes empty tweets (based on content)
		private static function remove_empty_tweets($array) {
			$new_array = array();
			
			// Iterate through item
			for($i = 0; $i < count($array); $i++) {
				// If the current item does have content
				if ($array[$i]->getContent() != NULL && $array[$i]->getContent() != "") {
					// Add it to the new array
					$new_array[] = $array[$i];
				}
			}
			
			return $new_array;
		}
		
		// Returns an array with only the requested usernames
		private static function remove_incorrect_usernames($array, $usernames) {
			$new_array = array();
			
			// Iterate through item
			for($i = 0; $i < count($array); $i++) {
				// If the current item has a valid username
				if (strlen(stristr($usernames,$array[$i]->getUsername())) > 0) {
					// Add it to the new array
					$new_array[] = $array[$i];
				}
			}
			
			// NOTE: This code doesn't work if the owner of the file is different to the user of the running process
			// Get a listing of the images directory
			/*if ($handle = opendir(plugin_dir_path( __FILE__ ) . 'images/')) {
				// Iterate through the listing
				while (false !== ($entry = readdir($handle))) {
					// Ignore . and .., and make sure that we are dealing with a png, jpg or gif
					if ($entry != "." && $entry != ".." && (strpos($entry, ".png") || strpos($entry, ".jpg") || strpos($entry, ".gif"))) {
						// Look for the last fullstop in the filename so that we can get the username
						$fullstop = strrpos($entry, ".");
						
						// If there is no fullstop then we don't want to process any further (this shouldn't ever happen)
						if ($fullstop !== FALSE) {
							// Get filename but ignore the extension
							$username = substr($entry, 0, $fullstop);
							
							// If the filename is not in $usernames
							if (strlen(stristr($usernames,$username)) == 0) {
								// If the file exists
								if (file_exists($entry)) {
									// First of all make it fully writeable to ensure we can delete it
									@chmod($entry, 0777);
									
									// Then delete it
									@unlink($entry);
								}
							}
						}
					}
				}
				
				// Close the directory stream
				closedir($handle);
			}*/
			
			return $new_array;
		}
		
		// Returns an array with tweets older than max days removed
		private static function remove_old_tweets($array, $max_days) {
			$new_array = array();
			
			// Iterate through item
			for($i = 0; $i < count($array); $i++) {
				// Get the oldest date the tweet can be
				$oldest_date = date("c", strtotime("-" . $max_days . " day" , strtotime(date("c"))));
				
				// If the current item is younger than the oldest date				
				if ($array[$i]->getTimestamp() > $oldest_date) {
					// Add it to the new array
					$new_array[] = $array[$i];
				}
			}
			
			return $new_array;
		}
		
		// Function to handle shortcode
		public static function shortcode_handler($atts) {
			extract(shortcode_atts(array(
				"unique_id"          => 0,
				"usernames"          => USERNAMES,
				"hashtags"           => HASHTAGS,
				"username_suffix"    => USERNAME_SUFFIX,
				"limit"              => LIMIT,
				"max_days"           => MAX_DAYS,
				"update_frequency"   => UPDATE_FREQUENCY,
				"show_username"      => SHOW_USERNAME,
				"show_avatar"        => SHOW_AVATAR,
				"show_published"     => SHOW_PUBLISHED,
				"show_follow"        => SHOW_FOLLOW,
				"links_new_window"   => LINKS_NEW_WINDOW,
				"no_cache"           => NO_CACHE,
				"use_curl"           => USE_CURL,
				"debug"              => DEBUG,
				"time_this_happened" => TIME_THIS_HAPPENED,
				"time_less_min"      => TIME_LESS_MIN,
				"time_min"           => TIME_MIN,
				"time_more_mins"     => TIME_MORE_MINS,
				"time_1_hour"        => TIME_1_HOUR,
				"time_2_hours"       => TIME_2_HOURS,
				"time_precise_hours" => TIME_PRECISE_HOURS,
				"time_1_day"         => TIME_1_DAY,
				"time_2_days"        => TIME_2_DAYS,
				"time_many_days"     => TIME_MANY_DAYS,
				"time_no_recent"     => TIME_NO_RECENT
			), $atts));
			
			// Modify unique id to lock it to shortcodes
			$unique_id = "thinktwit-sc-" . $unique_id;
						 
			// Create an array to contain the time settings
			$time_settings = array(11);

			$time_settings[0] = $time_this_happened;
			$time_settings[1] = $time_less_min;
			$time_settings[2] = $time_min;
			$time_settings[3] = $time_more_mins;
			$time_settings[4] = $time_1_hour;
			$time_settings[5] = $time_2_hours;
			$time_settings[6] = $time_precise_hours;
			$time_settings[7] = $time_1_day;
			$time_settings[8] = $time_2_days;
			$time_settings[9] = $time_many_days;
			$time_settings[10] = $time_no_recent;

			// If user selected to use no-caching output AJAX code
			if ($no_cache) {
				return "<div id=\"" . $unique_id . "\">" . ThinkTwit::output_ajax($unique_id, $usernames, $hashtags, $username_suffix, $limit, $max_days, $update_frequency, $show_username, $show_avatar, $show_published, $show_follow, $links_new_window, $use_curl, $debug, $time_settings) . "</div>";
			// Otherwise output HTML method
			} else {
				return ThinkTwit::parse_feed($unique_id, $usernames, $hashtags, $username_suffix, $limit, $max_days, $update_frequency, $show_username, $show_avatar, $show_published, $show_follow, $links_new_window, $use_curl, $debug, $time_settings);
			}
		}
		
		// Bubble sorts the tweets in array upon the timestamp
		private static function sort_tweets(&$array) {
			// Loop down through the array
			for ($i = count($array) - 1; $i >= 0; $i--) {
				// Record whether there was a swap
				$swapped = false;
				
				// Loop through un-checked array items
				for ($j = 0; $j < $i; $j++) {
					// Compare the values
					if ($array[$j]->getTimestamp() < $array[$j + 1]->getTimestamp()) {
						// Swap the values
						$tmp = $array[$j];
						$array[$j] = $array[$j + 1];        
						$array[$j + 1] = $tmp;
						$swapped = true;
					}
				}
			  
			  if (!$swapped) return;
			}
			
			return $array;
		}
		
		// Returns the given array but trimmed to the size of n
		private static function trim_array($array, $n){
			$new_array = array();
			
			// Loop through the array until n
			for($i = 0; $i < $n; $i++) {
				array_push($new_array, $array[$i]);
			}
			
			return $new_array;
		}
				
		// Updates the cache with the given Tweets and stores the time of the update
		private static function update_cache($tweets, $widget_id, $timestamp = -1) {
			// If timestamp is -1 (default) then get the current time
			if ($timestamp == -1) $timestamp = time();
			
			// Store the tweets in the database with the given timestamp
			update_option("widget_" . $widget_id . "_cache", array($tweets, $timestamp));
			
			do {
				// Get our widget settings
				$settings = get_option("widget_thinktwit_settings");
							
				// If settings isn't an array
				if (!is_array($settings)) {
					// Store updated timestamp
					$current_updated = microtime(); // For some reason some values are coming up identical between shortcode and widget when you have multiple widgets - how??
					
					// Create the array with the minimum required values
					$settings = array("version" => ThinkTwit::get_version(), "cache_names" => array("widget_" . $widget_id . "_cache"), "updated" => $current_updated);
				} else {
					// Otherwise, add the widget cache name to the array
					array_push($settings["cache_names"], "widget_" . $widget_id . "_cache");
					
					// Return a unique copy of the array to ensure we don't have duplicates
					$settings["cache_names"] = array_unique($settings["cache_names"]);
					
					// Store the current updated timestamp
					$current_updated = $settings["updated"];
					
					// Update the updated timestamp
					$settings["updated"] = microtime();
				}
				
				// Get a fresh copy of the settings so we can compare the timestamp with our settings timestamp
				// (if there is a difference then settings have been updated since we started, so repeat process)
				$fresh_settings = get_option("widget_thinktwit_settings");
				
				// Check that the fresh settings exist or else we will be stuck in a loop
				if (!is_array($fresh_settings)) {
					// If they don't lets just take a copy of our settings
					$fresh_settings = $settings;
				}
			} while($current_updated != $fresh_settings["updated"]);
			
			// Store the name of the cache in our settings
			update_option("widget_thinktwit_settings", $settings);
		}
	}
	
	// Class for storing a tweet
	class Tweet {
		protected $url;
		protected $avatar;
		protected $name;
		protected $username;
		protected $content;
		protected $timestamp;

		// Constructor
		public function __construct($url, $avatar, $name, $username, $content, $timestamp) {
			$this->url = trim($url);
			$this->avatar = trim($avatar);
			$this->name = trim($name);
			$this->username = trim($username);
			$this->content = trim($content);
			$this->timestamp = trim($timestamp);
		}

		// toString method outputs the contents of the Tweet
		public function __toString() {
			return "[url=$this->url, avatar=$this->avatar, name=$this->name, username=$this->username, content='$this->content', timestamp=$this->timestamp]";
		}

		// Returns the tweet's URL
		public function getUrl() {
			return $this->url;
		}

		// Sets the tweet's URL
		public function setUrl($url) {
			$this->url = trim($url);
		}

		// Returns the tweet's avatar filename
		public function getAvatar() {
			return $this->avatar;
		}

		// Sets the tweet's avatar filename
		public function setAvatar($avatar) {
			$this->avatar = trim($avatar);
		}

		// Returns the tweet's Twitter name
		public function getName() {
			return $this->name;
		}

		// Sets the tweet's Twitter name
		public function setName($name) {
			$this->name = trim($name);
		}

		// Returns the tweet's username
		public function getUsername() {
			return $this->username;
		}

		// Sets the tweet's username
		public function setUsername($username) {
			$this->username = trim($username);
		}

		// Returns the tweet's content
		public function getContent() {
			return $this->content;
		}

		// Sets the tweet's content
		public function setContent($content) {
			$this->content = trim($content);
		}

		// Returns the tweet's timestamp
		public function getTimestamp() {
			return $this->timestamp;
		}

		// Sets the tweet's content
		public function setTimestamp($timestamp) {
			$this->timestamp = trim($timestamp);
		}
	}
	
	// Add shortcode
	add_shortcode("thinktwit", "ThinkTwit::shortcode_handler");

	// Add the handler to init()
	add_action("init", "ThinkTwit::ajax_request_handler");

	// Register the widget to be initiated
	add_action("widgets_init", create_function("", "return register_widget(\"ThinkTwit\");"));
?>