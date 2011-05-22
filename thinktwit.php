<?php
/*
    Plugin Name: ThinkTwit
    Plugin URI: http://www.thepicketts.org/thinktwit/
    Description: Outputs tweets from one or more Twitter users through the Widget interface
    Version: 1.1.7
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

	class ThinkTwit extends WP_Widget {
		// Constructor
		public function ThinkTwit() {
			parent::WP_Widget('thinkTwit', $name = 'ThinkTwit');

			// Load jQuery
			wp_enqueue_script('jquery');
		}

		// Display the widget
		public function widget($args, $instance) {
			extract($args);

			// Get the div id of the widget
			$widgetid       = $args['widget_id'];

			// Store the widget values in variables
			$title          = apply_filters('widget_title', $instance['title']);
			$usernames      = $instance['usernames'];
			$usernameSuffix = $instance['usernameSuffix'];
			$limit          = $instance['limit'];
			$updateFrequency= $instance['updateFrequency'];
			$showAuthor     = $instance['showAuthor'];
			$showPublished  = isset($instance['showPublished']) ? $instance['showPublished'] : false;
			$linksNewWindow = isset($instance['linksNewWindow']) ? $instance['linksNewWindow'] : false;
			$noCache        = isset($instance['noCache']) ? $instance['noCache'] : false;
			$useCurl        = isset($instance['useCurl']) ? $instance['useCurl'] : false;
			$debug          = isset($instance['debug']) ? $instance['debug'] : false;

			// Output code that should appear before the widget
			echo $before_widget;

			// If there is a title output it with before and after code
			if ($title)
				echo $before_title . $title . $after_title;

			// If the user selected to not cache the widget then output AJAX method
			if ($noCache) { ?>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						$.ajax({
						  type : "GET",
						  url : "index.php",
						  data : { thinktwit_request        : "parse_feed",
                                   thinktwit_widgetid       : "<?php echo $widgetid; ?>",
								   thinktwit_usecurl        : "<?php echo $useCurl; ?>",
								   thinktwit_usernames      : "<?php echo $usernames; ?>",
								   thinktwit_usernameSuffix : "<?php echo $usernameSuffix; ?>",
								   thinktwit_limit          : "<?php echo $limit; ?>",
								   thinktwit_updateFrequency: "<?php echo $updateFrequency; ?>",
								   thinktwit_showAuthor     : "<?php echo $showAuthor; ?>",
								   thinktwit_showPublished  : "<?php echo $showPublished; ?>",
								   thinktwit_linksNewWindow : "<?php echo $linksNewWindow; ?>",
								   thinktwit_debug          : "<?php echo $debug; ?>"},
						  success : function(response) {
							// The server has finished executing PHP and has returned something, so display it!
							$("#<?php echo $widgetid; ?>").append(response);
						  }
						});
					});
				</script>
			<?php
			// Otherwise output HTML method
			} else {
				echo parse_feed($widgetid, $useCurl, $usernames, $usernameSuffix, $limit, $updateFrequency, $showAuthor, $showPublished, $linksNewWindow, $debug);
			}

			// Output code that should appear after the widget
			echo $after_widget;
		}

		// Update the widget when editing through admin user interface
		public function update($new_instance, $old_instance) {
			$instance = $old_instance;

			// Strip tags and update the widget settings
			$instance['title']          = strip_tags($new_instance['title']);
			$instance['usernames']      = strip_tags($new_instance['usernames']);
			$instance['usernameSuffix'] = strip_tags($new_instance['usernameSuffix']);
			$instance['limit']          = strip_tags($new_instance['limit']);
			$instance['updateFrequency']= strip_tags($new_instance['updateFrequency']);
			$instance['showAuthor']     = strip_tags($new_instance['showAuthor']);
			$instance['showPublished']  = (strip_tags($new_instance['showPublished']) == "Yes" ? true : false);
			$instance['linksNewWindow'] = (strip_tags($new_instance['linksNewWindow']) == "Yes" ? true : false);
			$instance['noCache']        = (strip_tags($new_instance['noCache']) == "Yes" ? true : false);
			$instance['useCurl']        = (strip_tags($new_instance['useCurl']) == "Yes" ? true : false);
			$instance['debug']          = (strip_tags($new_instance['debug']) == "Yes" ? true : false);

			return $instance;
		}

		// Output admin form for updating the widget
		public function form($instance) {
			// Set up some default widget settings
			$defaults = array('title'          => 'My Tweets',
							  'usernames'      => 'stephenpickett',
							  'usernameSuffix' => ' said: ',
							  'limit'          => 5,
							  'updateFrequency'=> 0,
							  'showAuthor'     => 'name',
							  'showPublished'  => true,
							  'linksNewWindow' => true,
							  'noCache'        => false,
							  'useCurl'        => false,
							  'debug'          => false);
			$instance = wp_parse_args((array) $instance, $defaults);

		?>
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
		<?php
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

	// Returns an array of Tweets from the cache or from Twitter depending on state of cache
	function get_tweets($updateFrequency, $url, $useCurl, $widgetid, $limit, $usernames) {
		$tweets;

		// First check that if the user wants live updates
		if ($updateFrequency == -1) {
			// If so then just get the tweets live from Twitter
			$tweets = get_tweets_from_twitter($url, $useCurl);
		} else {
			// Otherwise, get values from cache
			$lastUpdate = get_tweets_from_cache($widgetid);
			
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
			if (($diff / 3600) > $updateFrequency) {
				// Get tweets fresh from Twitter
				$fresh_tweets = get_tweets_from_twitter($url, $useCurl);
				
				// Merge all the tweets together
				$tweets = merge_tweets($tweets, $fresh_tweets);
				
				// Remove empty tweets
				$tweets = remove_empty_tweets($tweets);
				
				// Sort array by date
				sort_tweets($tweets, 'timestamp');
				
				// Remove any tweets that are duplicates
				$tweets = remove_duplicates($tweets);
				
				// Remove any tweets that aren't in usernames
				$tweets = remove_incorrect_usernames($tweets, $usernames);
				
				// If necessary, shrink the array (limit minus 1 as we start array from zero)
				if (count($tweets) > $limit) {
					$tweets = trim_array($tweets, $limit);
				}
				
				// Store updated array in cache
				update_cache($tweets, $widgetid);
			}
		}

		return $tweets;
	}
	
	// Returns an array of Tweets from the cache, along with the time of the last update
	function get_tweets_from_cache($widgetid) {
		// Get the option from the cache
		$tweets = get_option("widget_" . $widgetid . "_cache");
		
		return $tweets;
	}

	// Returns an array of Tweets when given the URL to access and a boolean indicating whether to use CURL
	function get_tweets_from_twitter($url, $useCurl) {
		// If user wishes to use CURL
		if ($useCurl) {
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
	function merge_tweets($array1, $array2) {
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
	function parse_feed($widgetid, $useCurl, $usernames, $username_suffix, $limit, $updateFrequency, $show_username, $show_published, $links_new_window, $debug) {
		$output = "";

		// Contstruct a string of usernames to search for
		$username_string = str_replace(" ", "+OR+from%3A", $usernames);

		// Construct the URL to obtain the Twitter ATOM feed (XML)
		$url = "http://search.twitter.com/search.atom?q=from%3A" . $username_string . "&rpp=" . $limit;

		// If user wishes to output debug info then do so
		if ($debug) {
			$output .= "<p>NOW: " . date('H:i:s', time()) . "</p>";
			$output .= "<p>useCurl: " . $useCurl . "</p>";
			$output .= "<p>usernames: " . $usernames . "</p>";
			$output .= "<p>username_suffix: " . $username_suffix . "</p>";
			$output .= "<p>limit: " . $limit . "</p>";
			$output .= "<p>show_username: " . $show_username . "</p>";
			$output .= "<p>show_published: " . $show_published . "</p>";
			$output .= "<p>links_new_window: " . $links_new_window . "</p>";
			$output .= "<p>URL: " . $url . "</p>";
		}

		// Get the tweets
		$tweets = get_tweets($updateFrequency, $url, $useCurl, $widgetid, $limit, $usernames);

		// Create an ordered list
		$output .= "<ol class=\"thinkTwitTweets\">";

		// Find out if there are any tweets, if so output them
		if (count($tweets) > 0) {
			// Loop through each tweet
			for ($i = 0; $i < count($tweets); $i++) {
				// Get the current tweet
				$tweet = $tweets[$i];

				// Output the list item
				$output .= "<li class=\"thinkTwitTweet\">";

				// Check if the user wants to output the name, username or nothing at all
				if (strcmp($show_username, "name") == 0) {
					$output .= "<a href=\"" . $tweet->getUrl() . "\"" . ($links_new_window == true ? " target=\"blank\"" : "") . " class=\"thinkTwitAuthor\">" . $tweet->getName() . "</a>" . $username_suffix;
				} elseif (strcmp($show_username, "username") == 0) {
					$output .= "<a href=\"" . $tweet->getUrl() . "\"" . ($links_new_window == true ? " target=\"blank\"" : "") . " class=\"thinkTwitAuthor\">" . $tweet->getUsername() . "</a>" . $username_suffix;
				}

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
						$output .= " target=\"_blank\"";

						// Then add everything after
						$output .= substr($url_strings[$j], $pos + 1);
					}
				} else {
					// Otherwise simply append the content unedited
					$output .= $tweet->getContent();
				}

				// Check if the user wants to show the published date
				if ($show_published == true) {
					$output .= "<span class=\"thinkTwitPublished\">This happened " . relative_created_at(strtotime($tweet->getTimestamp())) . "</span>";
				}

				// Close the list item
				$output .= "</li>";
			}
		} else {
			$output .= "<li class=\"thinkTwitNoTweets\">There have been no recent tweets.</li>";
		}

		$output .= "</ol>";

		return $output;
	}

	// Given a PHP time this returns how long ago that time was, in easy to understand English
	function relative_created_at($published_time) {
		$time_difference = time() - $published_time;

		if ($time_difference < 59) {
			return "less than a minute ago";
		} else if ($time_difference < 119) {    // changed because otherwise you get 30 seconds of 1 minutes ago
			return "about a minute ago";
		} else if ($time_difference < 3000) {   // less than 50 minutes ago
			return round($time_difference / 60) . " minutes ago";
		} else if ($time_difference < 5340) {   // less than 89 minutes ago
			return "about an hour ago";
		} else if ($time_difference < 9000) {   // less than 150 minutes ago
			return "a couple of hours ago";
		} else if ($time_difference < 82800) {  // less than 23 hours ago
			return "about " . round($time_difference / 3600) . " hours ago";
		} else if ($time_difference < 129600) { // less than 36 hours
			return "a day ago";
		} else if ($time_difference < 172800) { // less than 48 hours ago
			return "almost 2 days ago";
		} else {                           // more th 48 hours ago
			return round($time_difference / 86400) . " days ago";
		}
	}
	
	// Returns an array with duplicate tweets removed (based on timestamp)
	function remove_duplicates($array) {
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
	function remove_empty_tweets($array) {
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
	function remove_incorrect_usernames($array, $usernames) {
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
	function sort_tweets(&$array) {
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
	function trim_array($array, $n){
		$new_array = array();
		
		// Loop through the array until n
		for($i = 0; $i < $n; $i++) {
			array_push($new_array, $array[$i]);
		}
		
		return $new_array;
	}

	// Function for handling AJAX requests
	function thinktwit_request_handler() {
		// Check that all parameters have been passed
		if ((isset($_GET['thinktwit_request']) && ($_GET['thinktwit_request'] == 'parse_feed')) && isset($_GET['thinktwit_widgetid']) && 
		  isset($_GET['thinktwit_usecurl']) && isset($_GET['thinktwit_usernames']) && isset($_GET['thinktwit_usernameSuffix']) && 
		  isset($_GET['thinktwit_limit']) && isset($_GET['thinktwit_updateFrequency']) && isset($_GET['thinktwit_showAuthor']) && 
		  isset($_GET['thinktwit_showPublished']) && isset($_GET['thinktwit_linksNewWindow']) && isset($_GET['thinktwit_debug'])) {

			// Output the feed and exit the call
			echo parse_feed(strip_tags($_GET['thinktwit_widgetid']), strip_tags($_GET['thinktwit_usecurl']), strip_tags($_GET['thinktwit_usernames']),
			  strip_tags($_GET['thinktwit_usernameSuffix']), strip_tags($_GET['thinktwit_limit']), strip_tags($_GET['thinktwit_updateFrequency']), 
			  strip_tags($_GET['thinktwit_showAuthor']), strip_tags($_GET['thinktwit_showPublished']), strip_tags($_GET['thinktwit_linksNewWindow']), 
			  strip_tags($_GET['thinktwit_debug']));
			exit();
		} elseif (isset($_GET['thinktwit_request']) && ($_GET['thinktwit_request'] == 'parse_feed')) {
			// Otherwise display an error and exit the call
			echo "Error: Unable to display tweets.";
			exit();
		}
	}

	// Function to handle shortcode
	// [thinktwit use_curl=0|1 usernames="xxx yyy" username_suffix="xxx" limit=x show_username=none|name|username 
	// show_published=0|1 links_new_window=0|1 debug=0|1]
	function thinktwit_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'use_curl'         => false,
			'usernames'        => 'stephenpickett',
			'username_suffix'  => ' said: ',
			'limit'            => 5,
			'show_username'    => 'name',
			'show_published'   => true,
			'links_new_window' => true,
			'debug'            => false,
		), $atts));
		
		// Pass the variables, but set the update frequency to always be -1 (live and uncached) - don't pass a widgetid as this isn't a widget
		return parse_feed("", $use_curl, $usernames, $username_suffix, $limit, -1, $show_username, $show_published, $links_new_window, $debug);
	}
	
	// Updates the cache with the given Tweets and stores the time of the update
	function update_cache($tweets, $widgetid, $timestamp = -1) {
		// If timestamp is -1 (default) then get the current time
		if ($timestamp == -1) $timestamp = time();
		
		// Store the tweets in the database with the given timestamp
		update_option("widget_" . $widgetid . "_cache", array($tweets, $timestamp));
	}

	// Add shortcode
	add_shortcode('thinktwit', 'thinktwit_shortcode_handler');

	// Add the handler to init()
	add_action('init', 'thinktwit_request_handler');

	// Register the widget to be initiated
	add_action('widgets_init', create_function('', 'return register_widget("ThinkTwit");'));
?>