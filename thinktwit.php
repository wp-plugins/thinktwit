<?php
/*
    Plugin Name: ThinkTwit
    Plugin URI: http://www.thinkcs.org/about/think-digital/digital-services/thinktwit/
    Description: Outputs tweets from one or more Twitter users through the Widget interface
    Version: 1.0.5
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

    // Register activation hook
    register_activation_hook(__FILE__,'thinkTwit_install');

    // Register deactivation hook
    register_deactivation_hook( __FILE__, 'thinkTwit_uninstall' );

    // Called when initialising widget
    function thinkTwit_init() {
        // Register the plugin with the sidebar
        register_sidebar_widget(__('ThinkTwit'), 'widget_thinkTwit');
    }

    // Add the settings menu item
    add_action('admin_menu', 'thinkTwitMenu');

    // Add action to complete when the plugin is loaded
    add_action("plugins_loaded", "thinkTwit_init");

    // Called when the plugin is activated
    function thinkTwit_install() {
        // Creates database fields
        add_option("thinkTwit_usernames",       'stephenpickett',    '', 'yes');
        add_option("thinkTwit_limit",           '5',                 '', 'yes');
        add_option("thinkTwit_showUsername",    '1',                 '', 'yes');
        add_option("thinkTwit_showPublished",   '1',                 '', 'yes');
        add_option("thinkTwit_linksNewWindow",  '1',                 '', 'yes');
        add_option("thinkTwit_widgetPrefix",    '<ul>',              '', 'yes');
        add_option("thinkTwit_tweetPrefix",     '<li>',              '', 'yes');
        add_option("thinkTwit_usernameSuffix",  '&nbsp;said:&nbsp;', '', 'yes');
        add_option("thinkTwit_tweetSuffix",     '</li>',             '', 'yes');
        add_option("thinkTwit_publishedPrefix", '<br><i>',           '', 'yes');
        add_option("thinkTwit_publishedSuffix", '</i>',              '', 'yes');
        add_option("thinkTwit_widgetSuffix",    '</ul>',             '', 'yes');
        add_option("thinkTwit_title",           'My tweets',         '', 'yes');
    }

    // Called when the plugin is deactivated
    function thinkTwit_uninstall() {
        // Currently not used
    }

    // Called when menu item is added
    function thinkTwitMenu() {
        add_options_page('ThinkTwit', 'ThinkTwit', 'administrator', 'thinktwit', 'thinkTwitSettings');
    }

    // Called when the menu item is loaded
    function thinkTwitSettings() {
?>
        <div>
            <h2>ThinkTwit Settings</h2>

            <form method="post" action="options.php">
                <?php wp_nonce_field('update-options'); ?>

                <table width="100%">
                    <tr valign="top">
                        <th width="30%" align="left">Title:</th>
                        <td width="70%"><input name="thinkTwit_title" size="20" type="text" id="thinkTwit_title" value="<?php echo get_option('thinkTwit_title'); ?>" /></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(shown at the top before your tweets)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Usernames:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_usernames" id="thinkTwit_usernames"><?php echo get_option('thinkTwit_usernames'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(separated by spaces e.g. "bob jim")</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Limit:</th>
                        <td><input name="thinkTwit_limit" size="5" type="text" id="thinkTwit_usernames" value="<?php echo get_option('thinkTwit_limit'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(maximum combined number of tweets to show)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Show username:</th>
                        <td><input type="radio" name="thinkTwit_showUsername" id="thinkTwit_showUsername_yes" value="1" <?php echo (get_option('thinkTwit_showUsername') == 1 ? "checked=\"checked\"" : "") ?>/> Yes <input type="radio" name="thinkTwit_showUsername" id="thinkTwit_showUsername_no" value="0" <?php echo (get_option('thinkTwit_showUsername') == 0 ? "checked=\"checked\"" : "") ?>/> No</td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(indicates whether to display the username of the author before each tweet)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Show when published:</th>
                        <td><input type="radio" name="thinkTwit_showPublished" id="thinkTwit_showPublished_yes" value="1" <?php echo (get_option('thinkTwit_showPublished') == 1 ? "checked=\"checked\"" : "") ?>/> Yes <input type="radio" name="thinkTwit_showPublished" id="thinkTwit_showPublished_no" value="0" <?php echo (get_option('thinkTwit_showPublished') == 0 ? "checked=\"checked\"" : "") ?>/> No</td>
                    </tr>
                    <tr valign="top">
                        <td height="50" colspan="2">(indicates whether to show when the tweet was published after each tweet, in written format e.g. "a minute ago")</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Open links in new window:</th>
                        <td><input type="radio" name="thinkTwit_linksNewWindow" id="thinkTwit_linksNewWindow_yes" value="1" <?php echo (get_option('thinkTwit_linksNewWindow') == 1 ? "checked=\"checked\"" : "") ?>/> Yes <input type="radio" name="thinkTwit_linksNewWindow" id="thinkTwit_linksNewWindow_no" value="0" <?php echo (get_option('thinkTwit_linksNewWindow') == 0 ? "checked=\"checked\"" : "") ?>/> No</td>
                    </tr>
                    <tr valign="top">
                        <td height="50" colspan="2">(indicates whether to open links in a new window)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Widget prefix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_widgetPrefix" id="thinkTwit_widgetPrefix"><?php echo get_option('thinkTwit_widgetPrefix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(output between the title and the first tweet)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Tweet prefix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_tweetPrefix" id="thinkTwit_tweetPrefix"><?php echo get_option('thinkTwit_tweetPrefix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(output before every tweet)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Username suffix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_usernameSuffix" id="thinkTwit_usernameSuffix"><?php echo get_option('thinkTwit_usernameSuffix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(output after the username, only used if username is shown)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Tweet suffix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_tweetSuffix" id="thinkTwit_tweetSuffix"><?php echo get_option('thinkTwit_tweetSuffix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(output after every tweet)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Published prefix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_publishedPrefix" id="thinkTwit_publishedPrefix"><?php echo get_option('thinkTwit_publishedPrefix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(output before the published time, only used if published is shown)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Published suffix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_publishedSuffix" id="thinkTwit_publishedSuffix"><?php echo get_option('thinkTwit_publishedSuffix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td height="30" colspan="2">(output after the published time, only used if published is shown)</td>
                    </tr>

                    <tr valign="top">
                        <th align="left">Widget suffix:</th>
                        <td><textarea rows="4" cols="40" name="thinkTwit_widgetSuffix" id="thinkTwit_widgetSuffix"><?php echo get_option('thinkTwit_widgetSuffix'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">(output after the last tweet)</td>
                    </tr>
                </table>

                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="thinkTwit_title,thinkTwit_usernames,thinkTwit_limit,thinkTwit_showUsername,thinkTwit_showPublished,thinkTwit_linksNewWindow,thinkTwit_widgetPrefix,thinkTwit_tweetPrefix,thinkTwit_usernameSuffix,thinkTwit_tweetSuffix,thinkTwit_publishedPrefix,thinkTwit_publishedSuffix,thinkTwit_widgetSuffix" />

                <p><input type="submit" value="<?php _e('Save Changes') ?>" /></p>

                <p><a href="http://www.thinkcs.org/about/think-digital/digital-services/thinktwit/">ThinkTwit</a> was created by <a href="http://www.thinkcs.org/meet-the-team/stephen-pickett/">Stephen Pickett</a> at <a href="http://www.thinkcs.org/">Think Consulting Solutions</a>.</p>

            </form>
        </div>
<?php
    }

    // Given a PHP time this returns how long ago that time was in easy to understand English
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
    function parse_feed($usernames, $limit, $show_username, $show_published, $links_new_window, $tweet_prefix, $username_suffix, $tweet_suffix, $published_prefix, $published_suffix) {
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
        $output = "";

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

            $output .= $tweet_prefix;

            if ($show_username == 1) {
                $output .= "<a href=\"" . $clean_uri[0] . "\"" . (get_option('thinkTwit_linksNewWindow') == 1 ? " target=\"blank\"" : "") . ">" . $clean_name[0] . "</a>" . $username_suffix;
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
            if (get_option('thinkTwit_linksNewWindow') == 1) {
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

            if ($show_published == 1) {
                $output .= $published_prefix . "This happened " . relative_created_at(strtotime($clean_published[0])) . $published_suffix;
            }

            $output .= $tweet_suffix;
        }

        return $output;
    }

    function outputTweets() {
        // Output the feed prefix
        echo get_option('thinkTwit_widgetPrefix');

        // Output the feed
        echo parse_feed(get_option('thinkTwit_usernames'), get_option('thinkTwit_limit'), get_option('thinkTwit_showUsername'), get_option('thinkTwit_showPublished'), get_option('thinkTwit_linksNewWindow'), get_option('thinkTwit_tweetPrefix'), get_option('thinkTwit_usernameSuffix'), get_option('thinkTwit_tweetSuffix'), get_option('thinkTwit_publishedPrefix'), get_option('thinkTwit_publishedSuffix'));

        // Output the feed suffix
        echo get_option('thinkTwit_widgetSuffix');
    }

    function widget_thinkTwit() {
        // Output opening div container tag
        echo "<div id=\"thinktwit\">";

        // Output widget title
        echo "<h2 class=\"widgettitle\">" . get_option('thinkTwit_title') . "</h2>";

        // Output the tweets
        outputTweets();

        // Output closing div container tag
        echo "</div>";
    }
?>