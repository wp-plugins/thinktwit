<?php
/*
    Plugin Name: ThinkTwit
    Plugin URI: http://www.thepicketts.org/thinktwit/
    Description: Outputs tweets from one or more Twitter users through the Widget interface
    Version: 1.2.2
    Author: Stephen Pickett
    Author URI: http://www.thepicketts.org/
*/

/*
    Copyright 2011 Stephen Pickett (meethoss at gmail dot com)

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

	define("TIME_THIS_HAPPENED", "This happened ");
	define("TIME_LESS_MIN",      "less than a minute ago");
	define("TIME_MIN",           "about a minute ago");
	define("TIME_MORE_MINS",     " minutes ago");
	define("TIME_1_HOUR",        "about an hour ago");
	define("TIME_2_HOURS",       "a couple of hours ago");
	define("TIME_PRECISE_HOURS", "about =x= hours ago");
	define("TIME_1_DAY",         "a day ago");
	define("TIME_2_DAYS",        "almost 2 days ago");
	define("TIME_MANY_DAYS",     " days ago");
	define("TIME_NO_RECENT",     "There have been no recent tweets");

	class ThinkTwit extends WP_Widget {	
		// Constructor
		public function ThinkTwit() {
			// Set the description of the widget
			$widget_ops = array('description' => 'Outputs tweets from one or more Twitter users through the Widget interface');
			
			// Override the default constructor, passing the name and description
			parent::WP_Widget('thinkTwit', $name = 'ThinkTwit', $widget_ops);

			// Load jQuery
			wp_enqueue_script('jquery');
		}

		// Display the widget
		public function widget($args, $instance) {
			extract($args);

			// Get the div id of the widget
			$widget_id        = $args['widget_id'];

			// Store the widget values in variables
			$title            = apply_filters('widget_title', $instance['title']);
			$usernames        = $instance['usernames'];
			$username_suffix  = $instance['usernameSuffix'];
			$limit            = $instance['limit'];
			$update_frequency = $instance['updateFrequency'];
			$show_author      = $instance['showAuthor'];
			$show_avatar      = $instance['showAvatar'];
			$show_published   = isset($instance['showPublished']) ? $instance['showPublished'] : false;
			$links_new_window = isset($instance['linksNewWindow']) ? $instance['linksNewWindow'] : false;
			$no_cache         = isset($instance['noCache']) ? $instance['noCache'] : false;
			$use_curl         = isset($instance['useCurl']) ? $instance['useCurl'] : false;
			$debug            = isset($instance['debug']) ? $instance['debug'] : false;
			
			// Times
			$time_settings = array(11);
			$time_settings[0] = $instance['timeThisHappened'];
			$time_settings[1] = $instance['timeLessMin'];
			$time_settings[2] = $instance['timeMin'];
			$time_settings[3] = $instance['timeMoreMins'];
			$time_settings[4] = $instance['time1Hour'];
			$time_settings[5] = $instance['time2Hours'];
			$time_settings[6] = $instance['timePreciseHours'];
			$time_settings[7] = $instance['time1Day'];
			$time_settings[8] = $instance['time2Days'];
			$time_settings[9] = $instance['timeManyDays'];
			$time_settings[10]= $instance['timeNoRecent'];
			
			// Output code that should appear before the widget
			echo $before_widget;

			// If there is a title output it with before and after code
			if ($title)
				echo $before_title . $title . $after_title;

			// If the user selected to not cache the widget then output AJAX method
			if ($no_cache) { ?>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						$.ajax({
						  type : "GET",
						  url : "index.php",
						  data : { thinktwit_request             : "parse_feed",
                                   thinktwit_widget_id           : "<?php echo $widget_id; ?>",
								   thinktwit_use_curl            : "<?php echo $use_curl; ?>",
								   thinktwit_usernames           : "<?php echo $usernames; ?>",
								   thinktwit_username_suffix     : "<?php echo $username_suffix; ?>",
								   thinktwit_limit               : "<?php echo $limit; ?>",
								   thinktwit_update_frequency    : "<?php echo $update_frequency; ?>",
								   thinktwit_show_author         : "<?php echo $show_author; ?>",
								   thinktwit_show_avatar         : "<?php echo $show_avatar; ?>",
								   thinktwit_show_published      : "<?php echo $show_published; ?>",
								   thinktwit_links_new_window    : "<?php echo $links_new_window; ?>",
								   thinktwit_debug               : "<?php echo $debug; ?>",
								   thinktwit_time_this_happened  : "<?php echo $time_settings[0]; ?>",
								   thinktwit_time_less_min       : "<?php echo $time_settings[1]; ?>",
								   thinktwit_time_min            : "<?php echo $time_settings[2]; ?>",
								   thinktwit_time_more_mins      : "<?php echo $time_settings[3]; ?>",
								   thinktwit_time_1_hour         : "<?php echo $time_settings[4]; ?>",
								   thinktwit_time_2_hours        : "<?php echo $time_settings[5]; ?>",
								   thinktwit_time_precise_hours  : "<?php echo $time_settings[6]; ?>",
								   thinktwit_time_1_day          : "<?php echo $time_settings[7]; ?>",
								   thinktwit_time_2_days         : "<?php echo $time_settings[8]; ?>",
								   thinktwit_time_many_days      : "<?php echo $time_settings[9]; ?>",
								   thinktwit_time_no_recent      : "<?php echo $time_settings[10]; ?>"
								 },
						  success : function(response) {
							// The server has finished executing PHP and has returned something, so display it!
							$("#<?php echo $widget_id; ?>").append(response);
						  }
						});
					});
				</script>
			<?php
			// Otherwise output HTML method
			} else {
				echo ThinkTwit::parse_feed($widget_id, $use_curl, $usernames, $username_suffix, $limit, $update_frequency, $show_author, $show_avatar, $show_published, $links_new_window, $debug, $time_settings);
			}
			
			// Output code that should appear after the widget
			echo $after_widget;
		}

		// Update the widget when editing through admin user interface
		public function update($new_instance, $old_instance) {
			$instance = $old_instance;

			// Strip tags and update the widget settings
			$instance['title']            = strip_tags($new_instance['title']);
			$instance['usernames']        = strip_tags($new_instance['usernames']);
			$instance['usernameSuffix']   = strip_tags($new_instance['usernameSuffix']);
			$instance['limit']            = strip_tags($new_instance['limit']);
			$instance['updateFrequency']  = strip_tags($new_instance['updateFrequency']);
			$instance['showAuthor']       = strip_tags($new_instance['showAuthor']);
			$instance['showAvatar']       = (strip_tags($new_instance['showAvatar']) == "Yes" ? true : false);
			$instance['showPublished']    = (strip_tags($new_instance['showPublished']) == "Yes" ? true : false);
			$instance['linksNewWindow']   = (strip_tags($new_instance['linksNewWindow']) == "Yes" ? true : false);
			$instance['noCache']          = (strip_tags($new_instance['noCache']) == "Yes" ? true : false);
			$instance['useCurl']          = (strip_tags($new_instance['useCurl']) == "Yes" ? true : false);
			$instance['debug']            = (strip_tags($new_instance['debug']) == "Yes" ? true : false);
			$instance['timeThisHappened'] = strip_tags($new_instance['timeThisHappened']);
			$instance['timeLessMin']      = strip_tags($new_instance['timeLessMin']);
			$instance['timeMin']          = strip_tags($new_instance['timeMin']);
			$instance['timeMoreMins']     = strip_tags($new_instance['timeMoreMins']);
			$instance['time1Hour']        = strip_tags($new_instance['time1Hour']);
			$instance['time2Hours']       = strip_tags($new_instance['time2Hours']);
			$instance['timePreciseHours'] = strip_tags($new_instance['timePreciseHours']);
			$instance['time1Day']         = strip_tags($new_instance['time1Day']);
			$instance['time2Days']        = strip_tags($new_instance['time2Days']);
			$instance['timeManyDays']     = strip_tags($new_instance['timeManyDays']);
			$instance['timeNoRecent']     = strip_tags($new_instance['timeNoRecent']);

			return $instance;
		}

		// Output admin form for updating the widget
		public function form($instance) {
			// Set up some default widget settings
			$defaults = array('title'            => 'My Tweets',
							  'usernames'        => 'stephenpickett',
							  'usernameSuffix'   => ' said: ',
							  'limit'            => 5,
							  'updateFrequency'  => 0,
							  'showAuthor'       => 'name',
							  'showAvatar'       => true,
							  'showPublished'    => true,
							  'linksNewWindow'   => true,
							  'noCache'          => false,
							  'useCurl'          => false,
							  'debug'            => false,
							  'timeThisHappened' => TIME_THIS_HAPPENED,
							  'timeLessMin'      => TIME_LESS_MIN,
							  'timeMin'          => TIME_MIN,
							  'timeMoreMins'     => TIME_MORE_MINS,
							  'time1Hour'        => TIME_1_HOUR,
							  'time2Hours'       => TIME_2_HOURS,
							  'timePreciseHours' => TIME_PRECISE_HOURS,
							  'time1Day'         => TIME_1_DAY,
							  'time2Days'        => TIME_2_DAYS,
							  'timeManyDays'     => TIME_MANY_DAYS,
							  'timeNoRecent'     => TIME_NO_RECENT
							 );
							 
			$instance = wp_parse_args((array) $instance, $defaults);

		?>
			<div class="widget" style="border: 0">
				<div class="widget-top" style="border: 0; background: inherit; cursor: default; width: 94%">
					<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#available-widgets"></a></div>
					<div class="widget-title" style="padding: 6px 0 0"><h3 style="margin: 0; cursor: default">General Settings</h3></div>
				</div>
				<div class="widget-inside" style="display: block; border: 1px solid #DFDFDF; padding: 5px; width: 86%;">
					<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></label></p>

					<p><label for="<?php echo $this->get_field_id('usernames'); ?>"><?php _e('Twitter usernames (separated by spaces):'); ?> <textarea rows="4" cols="40" class="widefat" id="<?php echo $this->get_field_id('usernames'); ?>" name="<?php echo $this->get_field_name('usernames'); ?>"><?php echo $instance['usernames']; ?></textarea></label></p>

					<p><label for="<?php echo $this->get_field_id('usernameSuffix'); ?>"><?php _e('Username suffix (e.g. " said "):'); ?> <input class="widefat" id="<?php echo $this->get_field_id('usernameSuffix'); ?>" name="<?php echo $this->get_field_name('usernameSuffix'); ?>" type="text" value="<?php echo $instance['usernameSuffix']; ?>" /></label></p>

					<p><label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Max tweets to display:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $instance['limit']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('updateFrequency'); ?>"><?php _e('Update frequency:'); ?> <select id="<?php echo $this->get_field_id('updateFrequency'); ?>" name="<?php echo $this->get_field_name('updateFrequency'); ?>" class="widefat">
						<option value="-1" <?php if (strcmp($instance['updateFrequency'], -1) == 0) echo ' selected="selected"'; ?>>Live (uncached)</option>
						<option value="0" <?php if (strcmp($instance['updateFrequency'], 0) == 0) echo ' selected="selected"'; ?>>Live (cached)</option>
						<option value="1" <?php if (strcmp($instance['updateFrequency'], 1) == 0) echo ' selected="selected"'; ?>>Hourly</option>
						<option value="2" <?php if (strcmp($instance['updateFrequency'], 2) == 0) echo ' selected="selected"'; ?>>Every 2 hours</option>
						<option value="4" <?php if (strcmp($instance['updateFrequency'], 4) == 0) echo ' selected="selected"'; ?>>Every 4 hours</option>
						<option value="12" <?php if (strcmp($instance['updateFrequency'], 12) == 0) echo ' selected="selected"'; ?>>Every 12 hours</option>
						<option value="24" <?php if (strcmp($instance['updateFrequency'], 24) == 0) echo ' selected="selected"'; ?>>Every day</option>
						<option value="48" <?php if (strcmp($instance['updateFrequency'], 48) == 0) echo ' selected="selected"'; ?>>Every 2 days</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('showAuthor'); ?>"><?php _e('Show author:'); ?> <select id="<?php echo $this->get_field_id('showAuthor'); ?>" name="<?php echo $this->get_field_name('showAuthor'); ?>" class="widefat">
						<option value="none" <?php if (strcmp($instance['showAuthor'], "none") == 0) echo ' selected="selected"'; ?>>None</option>
						<option value="name" <?php if (strcmp($instance['showAuthor'], "name") == 0) echo ' selected="selected"'; ?>>Name</option>
						<option value="username" <?php if (strcmp($instance['showAuthor'], "username") == 0) echo ' selected="selected"'; ?>>Username</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('showAvatar'); ?>"><?php _e('Show author\'s avatar:'); ?> <select id="<?php echo $this->get_field_id('showAvatar'); ?>" name="<?php echo $this->get_field_name('showAvatar'); ?>" class="widefat">
						<option <?php if ($instance['showAvatar'] == true) echo 'selected="selected"'; ?>>Yes</option>
						<option <?php if ($instance['showAvatar'] == false) echo 'selected="selected"'; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('showPublished'); ?>"><?php _e('Show when published:'); ?> <select id="<?php echo $this->get_field_id('showPublished'); ?>" name="<?php echo $this->get_field_name('showPublished'); ?>" class="widefat">
						<option <?php if ($instance['showPublished'] == true) echo 'selected="selected"'; ?>>Yes</option>
						<option <?php if ($instance['showPublished'] == false) echo 'selected="selected"'; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('linksNewWindow'); ?>"><?php _e('Open links in new window:'); ?> <select id="<?php echo $this->get_field_id('linksNewWindow'); ?>" name="<?php echo $this->get_field_name('linksNewWindow'); ?>" class="widefat">
						<option <?php if ($instance['linksNewWindow'] == true) echo 'selected="selected"'; ?>>Yes</option>
						<option <?php if ($instance['linksNewWindow'] == false) echo 'selected="selected"'; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('noCache'); ?>"><?php _e('Prevent caching e.g. by WP Super Cache:'); ?> <select id="<?php echo $this->get_field_id('noCache'); ?>" name="<?php echo $this->get_field_name('noCache'); ?>" class="widefat">
						<option <?php if ($instance['noCache'] == true) echo 'selected="selected"'; ?>>Yes</option>
						<option <?php if ($instance['noCache'] == false) echo 'selected="selected"'; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('useCurl'); ?>"><?php _e('Use CURL for accessing Twitter API (set yes if getting `URL file-access` errors):'); ?> <select id="<?php echo $this->get_field_id('useCurl'); ?>" name="<?php echo $this->get_field_name('useCurl'); ?>" class="widefat">
						<option <?php if ($instance['useCurl'] == true) echo 'selected="selected"'; ?>>Yes</option>
						<option <?php if ($instance['useCurl'] == false) echo 'selected="selected"'; ?>>No</option>
					</select></label></p>

					<p><label for="<?php echo $this->get_field_id('debug'); ?>"><?php _e('Output debug messages:'); ?> <select id="<?php echo $this->get_field_id('debug'); ?>" name="<?php echo $this->get_field_name('debug'); ?>" class="widefat">
						<option <?php if ($instance['debug'] == true) echo 'selected="selected"'; ?>>Yes</option>
						<option <?php if ($instance['debug'] == false) echo 'selected="selected"'; ?>>No</option>
					</select></label></p>
				</div>
			</div>
			
			<div class="widget" style="border: 0">
				<div class="widget-top" style="border: 0; background: inherit; cursor: default; width: 94%">
					<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#available-widgets"></a></div>
					<div class="widget-title" style="padding: 6px 0 0"><h3 style="margin: 0; cursor: default">Time Messages</h3></div>
				</div>
				<div class="widget-inside" style="border: 1px solid #DFDFDF; padding: 5px; width: 86%;">
					<p>NOTE: The editing of these messages is optional.</p>
					
					<p><label for="<?php echo $this->get_field_id('timeThisHappened'); ?>"><?php _e('Time prefix:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timeThisHappened'); ?>" name="<?php echo $this->get_field_name('timeThisHappened'); ?>" type="text" value="<?php echo $instance['timeThisHappened']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('timeLessMin'); ?>"><?php _e('Less than 59 seconds ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timeLessMin'); ?>" name="<?php echo $this->get_field_name('timeLessMin'); ?>" type="text" value="<?php echo $instance['timeLessMin']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('timeMin'); ?>"><?php _e('Less than 1 minute 59 seconds ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timeMin'); ?>" name="<?php echo $this->get_field_name('timeMin'); ?>" type="text" value="<?php echo $instance['timeMin']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('timeMoreMins'); ?>"><?php _e('Less than 50 minutes ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timeMoreMins'); ?>" name="<?php echo $this->get_field_name('timeMoreMins'); ?>" type="text" value="<?php echo $instance['timeMoreMins']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('time1Hour'); ?>"><?php _e('Less than 89 minutes ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('time1Hour'); ?>" name="<?php echo $this->get_field_name('time1Hour'); ?>" type="text" value="<?php echo $instance['time1Hour']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('time2Hours'); ?>"><?php _e('Less than 150 minutes ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('time2Hours'); ?>" name="<?php echo $this->get_field_name('time2Hours'); ?>" type="text" value="<?php echo $instance['time2Hours']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('timePreciseHours'); ?>"><?php _e('Less than 23 hours ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timePreciseHours'); ?>" name="<?php echo $this->get_field_name('timePreciseHours'); ?>" type="text" value="<?php echo $instance['timePreciseHours']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('time1Day'); ?>"><?php _e('Less than 36 hours:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('time1Day'); ?>" name="<?php echo $this->get_field_name('time1Day'); ?>" type="text" value="<?php echo $instance['time1Day']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('time2Days'); ?>"><?php _e('Less than 48 hours ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('time2Days'); ?>" name="<?php echo $this->get_field_name('time2Days'); ?>" type="text" value="<?php echo $instance['time2Days']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('timeManyDays'); ?>"><?php _e('More than 48 hours ago:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timeManyDays'); ?>" name="<?php echo $this->get_field_name('timeManyDays'); ?>" type="text" value="<?php echo $instance['timeManyDays']; ?>" /></label></p>
					
					<p><label for="<?php echo $this->get_field_id('timeNoRecent'); ?>"><?php _e('No recent tweets:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('timeNoRecent'); ?>" name="<?php echo $this->get_field_name('timeNoRecent'); ?>" type="text" value="<?php echo $instance['timeNoRecent']; ?>" /></label></p>
				</div>
			</div>
			
			<h3>Support Development</h3>
			
			<p>If you would like to support development of ThinkTwit donations are gratefully accepted:</p>
			<p style="text-align:center"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B693F67QHAT8E" target="_blank"><img src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" alt="PayPal — The safer, easier way to pay online." /></a><img src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></p>
		<?php
		}
		
		// Returns the avatar for a given Twitter username
		private static function get_twitter_avatar($username, $use_curl) {
			$url = "http://twitter.com/users/" . $username . ".xml";
			
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
				$feed = file_get_contents($url);
			}

			// Put all entries into an array
			$xml = explode("<user>", $feed);

			// Check that there was a valid user found (if so it returns <user> if not it returns <hash>)
			if (count($xml) > 1) {
				// Get the image URL XML (the first instance is <user> and the rest is the remainder)
				$image_url = explode("<profile_image_url>", $xml[1]);

				// Clean up the image URL (get everything before the closing tag)
				$clean_image_url = explode("</profile_image_url>", $image_url[1]);

				// Return the image URL
				return $clean_image_url[0];
			}

			// If nothing was found return false
			return false;
		}

		// Returns an array of Tweets from the cache or from Twitter depending on state of cache
		private static function get_tweets($update_frequency, $url, $use_curl, $widget_id, $limit, $usernames) {
			$tweets;

			// First check that if the user wants live updates
			if ($update_frequency == -1) {
				// If so then just get the tweets live from Twitter
				$tweets = ThinkTwit::get_tweets_from_twitter($url, $use_curl);
			} else {
				// Otherwise, get values from cache
				$lastUpdate = ThinkTwit::get_tweets_from_cache($widget_id);
				
				// Ensure the database contained tweets
				if ($lastUpdate != FALSE) {
					// Get the tweets from the last update
					$tweets = $lastUpdate[0];
					
					// Get the time when the last update was cached
					$cachedTime = $lastUpdate[1];
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
					ThinkTwit::sort_tweets($tweets, 'timestamp');
					
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
				$feed = file_get_contents($url);
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

					// Create a tweet and add it to the array
					$tweets[] = new Tweet($clean_uri[0], $clean_name[0], trim($clean_name_1[0]), $clean_content[0], $clean_published[0]);
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

		// Returns the tweets subjects to the given parameters
		private static function parse_feed($widget_id, $use_curl, $usernames, $username_suffix, $limit, $update_frequency, $show_username, $show_avatar, $show_published, 
		  $links_new_window, $debug, $time_settings) {
			
			$output = "";

			// Contstruct a string of usernames to search for
			$username_string = str_replace(" ", "+OR+from%3A", $usernames);

			// Construct the URL to obtain the Twitter ATOM feed (XML)
			$url = "http://search.twitter.com/search.atom?q=from%3A" . $username_string . "&rpp=" . $limit;

			// If user wishes to output debug info then do so
			if ($debug) {
				$output .= "<p>now: " . date('H:i:s', time()) . "</p>";
				$output .= "<p>widget_id: " . $widget_id . "</p>";
				$output .= "<p>use_curl: " . $use_curl . "</p>";
				$output .= "<p>usernames: " . $usernames . "</p>";
				$output .= "<p>username_suffix: " . $username_suffix . "</p>";
				$output .= "<p>limit: " . $limit . "</p>";
				$output .= "<p>show_username: " . $show_username . "</p>";
				$output .= "<p>show_avatar: " . $show_avatar . "</p>";
				$output .= "<p>show_published: " . $show_published . "</p>";
				$output .= "<p>links_new_window: " . $links_new_window . "</p>";
				$output .= "<p>url: " . $url . "</p>";
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
			$tweets = ThinkTwit::get_tweets($update_frequency, $url, $use_curl, $widget_id, $limit, $usernames);

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
					$output .= "<a href=\"" . $tweet->getUrl() . "\"" . ($links_new_window == true ? " target=\"blank\"" : "") . " class=\"thinkTwitAuthor\" rel=\"nofollow\">";
					
					// Get the URL of the poster's avatar
					$url = ThinkTwit::get_twitter_avatar($tweet->getUsername(), $use_curl);

					// Check if the user wants to display the poster's avatar and that we can actually find one
					if ($show_avatar == true && $url != false) {
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
					if ($links_new_window == true) {
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
					if ($show_published == true) {
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

			return apply_filters('think_twit',$output);
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
			
			return $new_array;
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
		
		// Public function for call anywhere
		public static function thinktwit_output($widget_id, $use_curl, $usernames, $username_suffix, $limit, $update_frequency, $show_username, $show_avatar, $show_published, $links_new_window, $debug, 
		  $time_this_happened = TIME_THIS_HAPPENED, $time_less_min = TIME_LESS_MIN, $time_min = TIME_MIN, $time_more_mins = TIME_MORE_MINS, $time_1_hour = TIME_1_HOUR, $time_2_hours = TIME_2_HOURS, 
		  $time_precise_hours = TIME_PRECISE_HOURS, $time_1_day = TIME_1_DAY, $time_2_days = TIME_2_DAYS, $time_many_days = TIME_MANY_DAYS, $time_no_recent = TIME_NO_RECENT) {
		  										 
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
			
			return ThinkTwit::parse_feed($widget_id, $use_curl, $usernames, $username_suffix, $limit, $update_frequency, $show_username, $show_avatar, $show_published, $links_new_window, $debug, $time_settings);
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
		}
	}
	
	// Class for storing a tweet
	class Tweet {
		protected $url;
		protected $name;
		protected $username;
		protected $content;
		protected $timestamp;

		// Constructor
		public function __construct($url, $name, $username, $content, $timestamp) {
			$this->url = $url;
			$this->name = $name;
			$this->username = $username;
			$this->content = $content;
			$this->timestamp = $timestamp;
		}

		// toString method outputs the contents of the Tweet
		public function __toString() {
			return "[url=$this->url, name=$this->name, username=$this->username, content='$this->content', timestamp=$this->timestamp]";
		}

		// Returns the tweet's URL
		public function getUrl() {
			return $this->url;
		}

		// Sets the tweet's URL
		public function setUrl($url) {
			$this->url = trim($url);
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
	
	// Function for handling AJAX requests
	function thinktwit_request_handler() {
		// Check that all parameters have been passed
		if ((isset($_GET['thinktwit_request']) && ($_GET['thinktwit_request'] == 'parse_feed')) && isset($_GET['thinktwit_widget_id']) && 
		  isset($_GET['thinktwit_use_curl']) && isset($_GET['thinktwit_usernames']) && isset($_GET['thinktwit_username_suffix']) && 
		  isset($_GET['thinktwit_limit']) && isset($_GET['thinktwit_update_frequency']) && isset($_GET['thinktwit_show_author']) && 
		  isset($_GET['thinktwit_show_published']) && isset($_GET['thinktwit_links_new_window']) && isset($_GET['thinktwit_debug']) && 
		  isset($_GET['thinktwit_time_this_happened']) && isset($_GET['thinktwit_time_less_min']) && isset($_GET['thinktwit_time_min']) && 
		  isset($_GET['thinktwit_time_more_mins']) && isset($_GET['thinktwit_time_1_hour']) && isset($_GET['thinktwit_time_2_hours']) && 
		  isset($_GET['thinktwit_time_precise_hours']) && isset($_GET['thinktwit_time_1_day']) && isset($_GET['thinktwit_time_2_days']) && 
		  isset($_GET['thinktwit_time_many_days']) && isset($_GET['thinktwit_time_no_recent'])) {
		  
			// Output the feed and exit the call
			echo ThinkTwit::thinktwit_output(strip_tags($_GET['thinktwit_widget_id']), strip_tags($_GET['thinktwit_use_curl']), strip_tags($_GET['thinktwit_usernames']),
			  strip_tags($_GET['thinktwit_username_suffix']), strip_tags($_GET['thinktwit_limit']), strip_tags($_GET['thinktwit_update_frequency']), 
			  strip_tags($_GET['thinktwit_show_author']), strip_tags($_GET['thinktwit_show_avatar']), strip_tags($_GET['thinktwit_show_published']), 
			  strip_tags($_GET['thinktwit_links_new_window']), strip_tags($_GET['thinktwit_debug']), strip_tags($_GET['thinktwit_time_this_happened']), 
			  strip_tags($_GET['thinktwit_time_less_min']), strip_tags($_GET['thinktwit_time_min']), strip_tags($_GET['thinktwit_time_more_mins']), 
			  strip_tags($_GET['thinktwit_time_1_hour']), strip_tags($_GET['thinktwit_time_2_hours']), strip_tags($_GET['thinktwit_time_precise_hours']), 
			  strip_tags($_GET['thinktwit_time_1_day']), strip_tags($_GET['thinktwit_time_2_days']), strip_tags($_GET['thinktwit_time_many_days']), 
			  strip_tags($_GET['thinktwit_time_no_recent']));

			exit();
		} elseif (isset($_GET['thinktwit_request']) && ($_GET['thinktwit_request'] == 'parse_feed')) {
			// Otherwise display an error and exit the call
			echo "<p class=\"thinkTwitError\">Error: Unable to display tweets.</p>";
			
			exit();
		}
	}

	// Function to handle shortcode
	// [thinktwit unique_id=x use_curl=0|1 usernames="xxx yyy" username_suffix="xxx" limit=x update_frequency=x show_username=none|name|username 
	// show_avatar=0|1 show_published=0|1 links_new_window=0|1 debug=0|1 time_this_happened="xxx" time_less_min="xxx" time_min="xxx" time_more_mins="xxx" 
	// time_1_hour="xxx" time_2_hours="xxx" time_precise_hours="xxx" time_1_day="xxx" time_2_days="xxx" time_many_days="xxx" time_no_recent="xxx"]
	function thinktwit_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'unique_id'          => 0,
			'use_curl'           => false,
			'usernames'          => 'stephenpickett',
			'username_suffix'    => ' said: ',
			'limit'              => 5,
			'update_frequency'   => 0,
			'show_username'      => 'name',
			'show_avatar'        => true,
			'show_published'     => true,
			'links_new_window'   => true,
			'debug'              => false,
			'time_this_happened' => TIME_THIS_HAPPENED,
			'time_less_min'      => TIME_LESS_MIN,
			'time_min'           => TIME_MIN,
			'time_more_mins'     => TIME_MORE_MINS,
			'time_1_hour'        => TIME_1_HOUR,
			'time_2_hours'       => TIME_2_HOURS,
			'time_precise_hours' => TIME_PRECISE_HOURS,
			'time_1_day'         => TIME_1_DAY,
			'time_2_days'        => TIME_2_DAYS,
			'time_many_days'     => TIME_MANY_DAYS,
			'time_no_recent'     => TIME_NO_RECENT
		), $atts));
					
		// Pass the variables, but use the unique id rather than widget id
		return ThinkTwit::thinktwit_output("thinktwit-sc-" . $unique_id, $use_curl, $usernames, $username_suffix, $limit, $update_frequency, $show_username, $show_avatar, $show_published, $links_new_window, 
		  $debug, $time_this_happened, $time_less_min, $time_min, $time_more_mins, $time_1_hour, $time_2_hours, $time_precise_hours, $time_1_day, $time_2_days, $time_many_days, $time_no_recent);
	}

	// Add shortcode
	add_shortcode('thinktwit', 'thinktwit_shortcode_handler');

	// Add the handler to init()
	add_action('init', 'thinktwit_request_handler');

	// Register the widget to be initiated
	add_action('widgets_init', create_function('', 'return register_widget("ThinkTwit");'));
?>