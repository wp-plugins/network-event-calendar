<?php
/**
 * File: Network Event Calendar
 *
 * Plugin Name: Network Event Calendar
 *
 * Plugin URI: http://aut0poietic.us/applications/network-event-calendar/
 * 
 * Description: A simple event calendar that is Multi-Site aware and provides Widgets, shortcodes and WP_API methods for interacting with events.
 * 
 * Author: aut0poietic / Jer Brand
 * 
 * Version: 0.8.7
 * 
 * Author URI: http://aut0poietic.us
 * 
 * Text Domain: mu-events 
 * 
 */

global $muevents_db_version ; 
$muevents_db_version = "1.0" ;

/* 
 *	Yes, I know you want me to use $wpdb->prefix. But this plug-in is Multi-Site aware, 
 *	and I need 1 table, not 100. 
 */
$cdir = str_replace( "\\", "/", dirname( __FILE__ ) ) ;
$cdir =  substr( $cdir , strrpos( $cdir, "/" ) + 1 ) ;
define( "___TABLE" 								, 'global_mu_events' ) ; 
define( 'MU_EVENTS_PLUGIN_ID' 					, 'mu-events' ) ;
define( 'MU_EVENTS_BOX_ID' 						, 'mu-events-make-global' ) ;
define( 'MU_EVENTS_PLUGIN_DIR' 					, WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $cdir . DIRECTORY_SEPARATOR ) ;
define( 'MU_EVENTS_PLUGIN_URL' 					, WP_PLUGIN_URL . "/" . $cdir . '/' ) ;
define( "MU_EVENTS_META_KEY_ID" 				, '_mu_events_id' ) ; 
define( "MU_EVENTS_META_KEY_CREATE_AS_GLOBAL"	, '_mu_events_global' ) ; 
define( "MU_EVENTS_NONCE_DELETE" 				, 'mu-delete-item' ) ;
define( "MU_EVENTS_NONCE_TOGGLE_ACTIVATE" 		, 'mu-trash-item' ) ;
define( "MU_EVENTS_OPTION_STATUS_MESSAGE" 		, 'mu_events_admin_message' ) ;
define( 'MU_EVENTS_ADMIN_EVENT_LIMIT' 			, 20 ) ;



# # # Must be loaded every Page Load

require_once( MU_EVENTS_PLUGIN_DIR . '/core/init.php' ) ;
register_activation_hook( __FILE__ 	, 'mu_events_install' ) ; 

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# # # # # # # # # # # # # #        BUGFIX 0.8.1         # # # # # # # # # # # # #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
// uncomment this line before deactivation to completely remove the database.
//register_deactivation_hook( __FILE__, 'mu_events_uninstall' ) ;


require_once( MU_EVENTS_PLUGIN_DIR . '/core/mu_event_api.php' ) ;
require_once( MU_EVENTS_PLUGIN_DIR . '/core/event_post_type.php' ) ;
add_action( 'init'							, 'mu_events_create_post_type' ) ;

require_once( MU_EVENTS_PLUGIN_DIR . '/shortcode/shortcode_handler.php' ) ;
add_shortcode( MU_EVENTS_PLUGIN_ID 			, 'mu_events_shortcode_handler' ) ;

require_once( MU_EVENTS_PLUGIN_DIR . '/widget/widget_class.php' ) ;
add_action( 'widgets_init'					, 'mu_events_load_widget' ) ;


# # # Load only in Admin Interface
if( is_admin( ) )
{
	require_once( MU_EVENTS_PLUGIN_DIR . '/core/edit_event_hooks.php' ) ;
	require_once( MU_EVENTS_PLUGIN_DIR . '/ui/global_metabox.php' ) ;
	require_once( MU_EVENTS_PLUGIN_DIR . '/shortcode/tinymce/tinymce_button.php' ) ;
	require_once( MU_EVENTS_PLUGIN_DIR . '/widget/widget_admin.php' ) ;
	
	add_action( 'init'							, 'mu_events_add_button' ) ;
	add_filter( 'tiny_mce_version'				, 'mu_events_refresh_mce') ;
	add_action( 'admin_head' 					, 'mu_events_fix_plugin_icons_in_header' ) ;
	add_action( 'admin_menu' 					, 'mu_events_create_event_list_menu') ;
	add_action( 'admin_menu' 					, 'mu_events_global_metabox' ) ;
	
	add_action( 'publish_event'					, 'mu_events_publish_event_hook' ) ;
	add_action( 'delete_post'					, 'mu_events_delete_event_hook' , 0 , 1 ) ;
	add_action( 'transition_post_status'		, 'mu_events_transition_post_status_hook', 0 , 3 ) ;
	
	add_action( 'admin_print_scripts-widgets.php', 'mu_events_enqueue_script_styles');
}

$months_list = array( "", 
	__( "Jan" , MU_EVENTS_PLUGIN_ID ), __( "Feb" , MU_EVENTS_PLUGIN_ID ), __( "Mar" , MU_EVENTS_PLUGIN_ID ), 
	__( "Apr" , MU_EVENTS_PLUGIN_ID ), __( "May" , MU_EVENTS_PLUGIN_ID ), __( "Jun" , MU_EVENTS_PLUGIN_ID ), 
	__( "Jul" , MU_EVENTS_PLUGIN_ID ), __( "Aug" , MU_EVENTS_PLUGIN_ID ), __( "Sep" , MU_EVENTS_PLUGIN_ID ), 
	__( "Oct" , MU_EVENTS_PLUGIN_ID ), __( "Nov" , MU_EVENTS_PLUGIN_ID ), __( "Dec" , MU_EVENTS_PLUGIN_ID )
) ;

# # # GLOBALIZING INTERNAL WP VARIABLES
global $wpdb, $blog_id, $current_blog, $months_list ;
?>