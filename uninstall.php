<?php
	// Check for ABSPATH and WP_UNINSTALL_PLUGIN to ensure we are authorised to uninstall the plugin
	if(!defined('WP_UNINSTALL_PLUGIN')) {
		exit();
	}
	
	// Clear the cache
	ThinkTwit::clear_cache();
	
	// Finally delete the settings and widget
	delete_option("widget_thinktwit_settings");
	delete_option("widget_thinktwit");
?>