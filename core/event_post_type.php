<?php
/* 
  	File: event_post_type.php 
  	
	WordPress Usage: Event Post Type Hooks
  	
  	Last Edited: 
	
		Jer Brand on 2010/09/27 
 */

/**
  	Function: mu_events_create_post_type
 
  	Registers 'event' as a custom post type with the WordPress CMS.
  	Additionally removes the default handler for scheduling future events
  	to ensure that events dated in the future have the 'published'
  	post status.
 
  	Hook Used:
 
  		init
 
  	See Also:
 
  		<mu_events_publish_future_post_now>, <mu_events_publish_future_post_now>
 */
function mu_events_create_post_type( ) 
{
	
	load_plugin_textdomain( MU_EVENTS_PLUGIN_ID ) ;
	
	$labels = array(
		'name'					=> _x( 'Events' , 'post type general name', MU_EVENTS_PLUGIN_ID ) ,
		'singular_name'			=> _x( 'Event' , 'post type singular name', MU_EVENTS_PLUGIN_ID ) ,
		'add_new'				=> _x( 'Add New' , 'event', MU_EVENTS_PLUGIN_ID ) ,
		'add_new_item' 			=> __( 'Add New Event', MU_EVENTS_PLUGIN_ID ) ,
		'edit_item'				=> __( 'Edit Event', MU_EVENTS_PLUGIN_ID ) ,
		'new_item'				=> __( 'New Event', MU_EVENTS_PLUGIN_ID ) ,
		'view_item'				=> __( 'View Event', MU_EVENTS_PLUGIN_ID ) ,
		'search_items'			=> __( 'Search Events', MU_EVENTS_PLUGIN_ID ) ,
		'not_found'				=> __( 'No events found', MU_EVENTS_PLUGIN_ID ) ,
		'not_found_in_trash' 	=> __( 'No events found in Trash', MU_EVENTS_PLUGIN_ID ) , 
		'parent_item_colon' 	=> ''
  );
  
  $args = array(
		'labels'			=> $labels,
		'public'			=> true,
		'publicly_queryable'=> true,
		'show_ui'			=> true, 
		'query_var'			=> true,
		'rewrite'			=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'menu_position' 	=> 5,
		'supports' 			=> array( 'title' , 'editor' , 'author' , 'comments', 'excerpt' )
  ); 
  
  register_post_type( 'event' , $args ) ;
  
  // events are dated in the future, so remove the default
  // hook handler for publishing events with a future date
  // and add our own that saves the thing with a published status.
  remove_action( 'future_event' , '_future_post_hook' ) ;
  add_action( 'future_event' , 'mu_events_publish_future_post_now' ) ;
}

/**
  	Function: mu_events_publish_future_post_now
 
  	Gives custom post-type 'event' article a status of 'publish'
  	rather than future when their post date is in the future.
 
  	Hook Used:
 
  		future_event
 
  	Parameters:
 
  		$id - The id of the post to be published.
 
 */
function mu_events_publish_future_post_now( $id ) 
{
	wp_publish_post( $id ) ;
}

/**
  	Function: mu_events_create_event_list_menu
 
  	Creates a menu option for managing global events listing.
  	This list is only viewable by the superadmin.
 
  	Hook Used:
 
  		admin_menu
 
  	See Also:
 
  		<mu_events_manage_global_events_ui>
 */
function mu_events_create_event_list_menu( )
{
	global $blog_id , $current_user ;
	get_currentuserinfo( ) ;
	if( is_super_admin( $current_user->ID ) && 1 == $blog_id )
	{
		$page = add_submenu_page(
			 'edit.php?post_type=event' ,
			 'Manage Globla Events' , 
			 'Global Events' , 
			 'mu_events_manage_global' , 
			 MU_EVENTS_PLUGIN_ID , 
			 'mu_events_manage_global_events_ui' 
		) ;
	}
}

/**
  	Function: mu_events_manage_global_events_ui
 
  	Renders the UI and handles all global events listing
  	editing tasks.
 
 */
function mu_events_manage_global_events_ui( )
{
	include( MU_EVENTS_PLUGIN_DIR . "/ui/ui_utilities.php" ) ;
	include( MU_EVENTS_PLUGIN_DIR . "/ui/manage_global_events.php" ) ;
}

/**
  	Function: mu_events_fix_plugin_icons_in_header
 
  	Just shows the correct icons for the menus and event list page.
 
  	Hook Used:
 
  		admin_head
 
 */
function mu_events_fix_plugin_icons_in_header( ) 
{
	include( MU_EVENTS_PLUGIN_DIR . "/ui/admin_css_fix.php" ) ;
}
?>