<?php
/*
    Plugin Name: ThinkTwit
    Plugin URI: http://www.thinkcs.org/about/think-digital/digital-services/thinktwit/
    Description: Outputs tweets from one or more Twitter users through the Widget interface
    Version: 1.1.0
    Author: Stephen Pickett
    Author URI: http://www.thinkcs.org/meet-the-team/stephen-pickett/
*/

/*
    Copyright 2010 Stephen Pickett (http://www.thinkcs.org/contact-us/)

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
    }

    // Display the widget
    public function widget($args, $instance) {
        extract($args);

        $title          = apply_filters('widget_title', $instance['title']);
        $usernames      = $instance['usernames'];
        $usernameSuffix = $instance['usernameSuffix'];
        $limit          = $instance['limit'];
        $showUsername   = isset($instance['showUsername']) ? $instance['showUsername'] : false;
        $showPublished  = isset($instance['showPublished']) ? $instance['showPublished'] : false;
        $linksNewWindow = isset($instance['linksNewWindow']) ? $instance['linksNewWindow'] : false;

        ?>
              <?php echo $before_widget; ?>

              <?php if ($title)
                  echo $before_title . $title . $after_title; ?>

              <?php
              echo parse_feed($usernames, $usernameSuffix, $limit, $showUsername, $showPublished, $linksNewWindow);
              //echo "Entered: " . $usernames . ", " . $usernameSuffix . ", " . $limit . ", " . $showUsername . ", " . $showPublished . ", " . $linksNewWindow;
              ?>

              <?php echo $after_widget; ?>
        <?php
    }

    // Update the widget
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;

        // Strip tags and update the widget settings
        $instance['title']          = strip_tags($new_instance['title']);
        $instance['usernames']      = strip_tags($new_instance['usernames']);
        $instance['usernameSuffix'] = strip_tags($new_instance['usernameSuffix']);
        $instance['limit']          = strip_tags($new_instance['limit']);
        $instance['showUsername']   = (strip_tags($new_instance['showUsername']) == "Yes" ? true : false);
        $instance['showPublished']  = (strip_tags($new_instance['showPublished']) == "Yes" ? true : false);
        $instance['linksNewWindow'] = (strip_tags($new_instance['linksNewWindow']) == "Yes" ? true : false);

        return $instance;
    }

    // Output form for updating the widget
    public function form($instance) {
        // Set up some default widget settings
        $defaults = array('title'          => 'My Tweets',
                          'usernames'      => 'stephenpickett',
                          'usernameSuffix' => ' said: ',
                          'limit'          => '5',
                          'showUsername'   => true,
                          'showPublished'  => true,
                          'linksNewWindow' => true);
        $instance = wp_parse_args((array) $instance, $defaults);
        extract($args);

        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></label></p>

            <p><label for="<?php echo $this->get_field_id('usernames'); ?>"><?php _e('Twitter usernames (separated by spaces):'); ?> <textarea rows="4" cols="40" class="widefat" id="<?php echo $this->get_field_id('usernames'); ?>" name="<?php echo $this->get_field_name('usernames'); ?>"><?php echo $instance['usernames']; ?></textarea></label></p>

            <p><label for="<?php echo $this->get_field_id('usernameSuffix'); ?>"><?php _e('Username suffix (e.g. " said "):'); ?> <input class="widefat" id="<?php echo $this->get_field_id('usernameSuffix'); ?>" name="<?php echo $this->get_field_name('usernameSuffix'); ?>" type="text" value="<?php echo $instance['usernameSuffix']; ?>" /></label></p>

            <p><label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Max tweets to display:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $instance['limit']; ?>" /></label></p>

            <p><label for="<?php echo $this->get_field_id('showUsername'); ?>"><?php _e('Show usernames:'); ?> <select id="<?php echo $this->get_field_id('showUsername'); ?>" name="<?php echo $this->get_field_name('showUsername'); ?>" class="widefat">
                <option <?php if ($instance['showUsername'] == true) echo 'selected="selected"'; ?>>Yes</option>
                <option <?php if ($instance['showUsername'] == false) echo 'selected="selected"'; ?>>No</option>
            </select></label></p>

            <p><label for="<?php echo $this->get_field_id('showPublished'); ?>"><?php _e('Show when published:'); ?> <select id="<?php echo $this->get_field_id('showPublished'); ?>" name="<?php echo $this->get_field_name('showPublished'); ?>" class="widefat">
                <option <?php if ($instance['showPublished'] == true) echo 'selected="selected"'; ?>>Yes</option>
                <option <?php if ($instance['showPublished'] == false) echo 'selected="selected"'; ?>>No</option>
            </select></label></p>

            <p><label for="<?php echo $this->get_field_id('linksNewWindow'); ?>"><?php _e('Open links in new window:'); ?> <select id="<?php echo $this->get_field_id('linksNewWindow'); ?>" name="<?php echo $this->get_field_name('linksNewWindow'); ?>" class="widefat">
                <option <?php if ($instance['linksNewWindow'] == true) echo 'selected="selected"'; ?>>Yes</option>
                <option <?php if ($instance['linksNewWindow'] == false) echo 'selected="selected"'; ?>>No</option>
            </select></label></p>
        <?php
    }
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

// Returns the tweets subjects to the given parameters
function parse_feed($usernames, $username_suffix, $limit, $show_username, $show_published, $links_new_window) {
    // Contstruct a string of usernames to search for
    $usernames = str_replace(" ", "+OR+from%3A", $usernames);

    // Construct the URL to obtain the Twitter ATOM feed (XML)
    $feed = "http://search.twitter.com/search.atom?q=from%3A" . $usernames . "&rpp=" . $limit;
    $feed = file_get_contents($feed);
    $feed = str_replace("&", "&", $feed);
    $feed = str_replace("<", "<", $feed);
    $feed = str_replace(">", ">", $feed);

    // Put all entries into an array
    $clean = explode("<entry>", $feed);

    // Get the amount of entries
    $amount = count($clean) - 1;

    // Create a variable to store the entries for output
    $output = "<ul class=\"thinkTwitTweets\">";

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

            $output .= "<li class=\"thinkTwitTweet\">";

            if ($show_username == true) {
                $output .= "<a href=\"" . $clean_uri[0] . "\"" . ($links_new_window == true ? " target=\"blank\"" : "") . " class=\"thinkTwitAuthor\">" . $clean_name[0] . "</a>" . $username_suffix;
            }

            // Make the links clickable
            $clean_content[0] = str_replace("&lt;", "<", $clean_content[0]);
            $clean_content[0] = str_replace("&gt;", ">", $clean_content[0]);
            $clean_content[0] = str_replace("&amp;", "&", $clean_content[0]);
            $clean_content[0] = str_replace("&apos;", "'", $clean_content[0]);
            $clean_content[0] = str_replace("&amp;quot;", "&quot;", $clean_content[0]);
            $clean_content[0] = str_replace("&amp;lt", "<", $clean_content[0]);
            $clean_content[0] = str_replace("&amp;gt", ">", $clean_content[0]);
            $clean_content[0] = str_replace("&quot;", "\"", $clean_content[0]);

            // Check if the user wants URL's to open in a new window
            if ($links_new_window == true) {
                // Find the URL's in the content
                $url_strings = explode("href=\"", $clean_content[0]);

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
                $output .= $clean_content[0];
            }

            if ($show_published == true) {
                $output .= "<span class=\"thinkTwitPublished\">This happened " . relative_created_at(strtotime($clean_published[0])) . "</span>";
            }

            $output .= "</li>";
        }
    } else {
        $output = "<li class=\"thinkTwitNoTweets\">There have been no tweets for the past 7 days.</li>";
    }

    $output .= "</ul>";

    return $output;
}

add_action('widgets_init', create_function('', 'return register_widget("ThinkTwit");'));
?>