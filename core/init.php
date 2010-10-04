<?php
/** 
 	File: init.php
 
 	WordPress Usage : Plugin Installation and Uninstallation
 
 	Last Edited: 
	
		Jer Brand on 2010/09/17
 */

/*
 	Function: mu_events_install
 
 	Installs the database if it does not exist and registers the database version
 	(currently 1.0) for future upgrades. 
  
 	Called any time the plugin is installed.
  
   Utilizes dbDelta method in the upgrade.php file packaged with wordpress.
 	
 	Uses Hook:
  
 		register_activation_hook
  
 */
function mu_events_install( )
{
	global $wpdb , $muevents_db_version ;
	
	if( $wpdb->get_var( "show tables like '" . ___TABLE . "'" ) != ___TABLE ) 
	{
		$sql = "CREATE TABLE " . ___TABLE . " (
					id mediumint(20) NOT NULL AUTO_INCREMENT,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					title tinytext NOT NULL,
					text text NOT NULL,
					url tinytext  NOT NULL,
					site_id bigint(20) NOT NULL,
					event_id bigint(20) NOT NULL,
					is_active boolean DEFAULT '1' NOT NULL,
					is_global boolean DEFAULT '0' NOT NULL,
					UNIQUE KEY id (id)
				) ;" ;

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ) ;
	  
      dbDelta( $sql ) ;
	  
	  add_option( "muevents_db_version" , $muevents_db_version ) ;
	}
}


/*
 	Function: mu_events_uninstall
  
 	Deletes the database when the plugin is uninstalled.
 	
 	Uses Hook:
  
 		register_deactivation_hook
  
 */
function mu_events_uninstall( )
{
	global $wpdb ;
	$wpdb->query( $wpdb->prepare("DROP TABLE IF EXISTS " . ___TABLE ) );
}

?>