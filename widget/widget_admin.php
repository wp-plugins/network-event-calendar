<?php
/* 
 	File: widget_admin.php 
 	
	WordPress Usage: Multi-Site Events Widget Admin
  
 	Last Edited: 
	
		Jer Brand on 2010/09/27
 */

/*
 	Function: mu_events_enqueue_script_styles
 
 	Adds a small script to the widget admin page. 
 
 	Hook Used:
	
		admin_print_scripts-widgets.php
	
 */
function mu_events_enqueue_script_styles( )
{
	wp_enqueue_script('mu-events-admin-functions', MU_EVENTS_PLUGIN_URL . '/widget/admin_script.js', array('jquery'), '1.0', false);
}
?>