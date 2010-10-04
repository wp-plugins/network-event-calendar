<?php
/** 
 *	File: tinymce_button.php
 *	
 *	WordPress Usage: MCE Button Creation
 * 
 *	Last Edited:
 *	
 *		Jer Brand on 2010/09/27
 */

/*
 *	Function: mu_events_add_button
 *
 *	Adds a new button to the TinyMCE editor on all pages that allows the insertion of 
 *	an event list to a page or post.
 *	
 *	Uses Hook:
 *
 *		init
 *
 */
function mu_events_add_button( )
{
   if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
   {
     return ;
   }
   if ( get_user_option( 'rich_editing' ) == 'true' ) 
   {
     add_filter( 'mce_external_plugins' , 'mu_events_add_tinymce_plugin' ) ;
     add_filter( 'mce_buttons' , 'mu_events_register_tinymce_button' ) ;
   }
}

/*
 *	Function: mu_events_register_tinymce_button
 *
 *	Addss the button to the $buttons array for TinyMCE.
 *	
 *	Uses Hook:
 *
 *		mce_buttons
 *
 */
function mu_events_register_tinymce_button( $buttons ) 
{
   array_push( $buttons , "|" , "mueventsshortcode" );
   return $buttons ;
}

/*
 *	Function: mu_events_add_tinymce_plugin
 *
 *	Adds the plugin JavaScript file to the TinyMCE.
 *	
 *	Uses Hook:
 *
 *		mce_external_plugins
 *
 */
function mu_events_add_tinymce_plugin( $plugin_array ) 
{
   $plugin_array[ 'mueventsshortcode' ] = MU_EVENTS_PLUGIN_URL . 'shortcode/tinymce/editor_plugin.js' ;
   return $plugin_array ;
}

/*
 *	Function: mu_events_refresh_mce
 *
 *	While not strictly necessary, this method updates TinyMCE's version
 *	in an effort to force a UI update. 
 *	
 *	Uses Hook:
 *
 *		tiny_mce_version
 *
 */
function mu_events_refresh_mce( $ver ) 
{
  $ver += 3;
  return $ver;
}
?>