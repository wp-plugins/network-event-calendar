<?php
/* 
  	File: edit_event_hooks.php
 
	WordPress Usage: Event Editor Event Hook Handlers
  
  	Last Edited: 
 
 		Jer Brand on 2010/10/06
 */
 
/*
  	Function: mu_events_publish_event_hook
 
  	Fires whenever the users clicks 'publish', 'update', 'Activate' in the Global Events Listing
  	or restores an event from the trash.
  	This method creates an entry for the event in a global shadow 
  	(as in it 'shadows' the wp*_posts table for all sites). 
  	Additionally adds two custom post values based on  user settings: 
 
  		- MU_EVENTS_META_KEY_ID: The id of the entry in the shadow table for this event.
  		- MU_EVENTS_META_KEY_CREATE_AS_GLOBAL: Boolean indicating the event is considered global.
 
  	Global events are added to the shadow table using the site_id of the / site ( assumed to be 1 ).
 
  	Parameters:
 
  		$event_id - the id of the event.
 
  	Uses Hook:
 
  		publish_event
 
 */
function mu_events_publish_event_hook( $event_id )
{
	global $blog_id, $wpdb ;
	
	if( isset( $_POST['mu_event_is_global'] ) )
	{
		$is_global =  $_POST['mu_event_is_global'] ;
	}
	else
	{
		$is_global =  "0" ;
	}
	if(! update_post_meta( $event_id , MU_EVENTS_META_KEY_CREATE_AS_GLOBAL , $is_global ) )
	{
		add_post_meta( $event_id , MU_EVENTS_META_KEY_CREATE_AS_GLOBAL , $is_global,  true ) ;
	}

	$event 				= get_post( $event_id ) ;
	$mu_event_id 		= get_post_meta( $event_id , MU_EVENTS_META_KEY_ID , true ) ;
	$event_is_global 	= get_post_meta( $event_id , MU_EVENTS_META_KEY_CREATE_AS_GLOBAL , true ) ;
	$site_id 			= $blog_id ;
	$is_global 			= ( "1" == $event_is_global || 1 == $event_is_global ||  1 == $site_id ) ? 1 : 0 ;
	
	
	$permalink = get_permalink( $event_id ) ;
	
	if( '' != trim( $mu_event_id ) )
	{
		$rows_affected = $wpdb->update( 
			___TABLE , 
			array( 
					'time' 		=> $event->post_date , 
					'title' 	=> $event->post_title , 
					'text' 		=> $event->post_excerpt , 
					'url' 		=> $permalink , 
					'site_id' 	=> $site_id , 
					'event_id' 	=> $event_id, 
					'is_active'	=> 1,
					'is_global' => $is_global 
				 ) , 
			array( 'ID' => $mu_event_id ) , 
			array( 
					'%s' ,
					'%s' ,
					'%s' ,
					'%s' ,
					'%d' ,
					'%d' ,
					'%d' ,
					'%d' 
				) , 
			array( '%d' ) 
		) ;
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# # # # # # # # # # # # # #        BUGFIX 0.8.4         # # # # # # # # # # # # #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#   If the query returns 0 rows affected, we've triggered the 0.8.0          #
#   upgrade bug.  Re-create the global event entry.                                 #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
		if( 0 == $rows_affected )
		{
			$rows_affected = $wpdb->insert( 
				___TABLE,
				array( 
						'time' 		=> $event->post_date , 
						'title' 	=> $event->post_title , 
						'text' 		=> $event->post_excerpt , 
						'url' 		=> $permalink , 
						'site_id' 	=> $site_id , 
						'event_id' 	=> $event_id, 
						'is_active' => 1,
						'is_global' => $is_global
					   ) 
			);
			
			$mu_event_id = $wpdb->insert_id ;
			add_post_meta( $event_id , MU_EVENTS_META_KEY_ID , $mu_event_id , true ) ;
		}
	}
	else
	{
///TODO: Rednudant code -- functionize this in future versions.
		$rows_affected = $wpdb->insert( 
			___TABLE,
			array( 
					'time' 		=> $event->post_date , 
					'title' 	=> $event->post_title , 
					'text' 		=> $event->post_excerpt , 
					'url' 		=> $permalink , 
					'site_id' 	=> $site_id , 
					'event_id' 	=> $event_id, 
					'is_active' => 1,
					'is_global' => $is_global
				   ) 
		);
		
		$mu_event_id = $wpdb->insert_id ;
		add_post_meta( $event_id , MU_EVENTS_META_KEY_ID , $mu_event_id , true ) ;
	}
}

/*
  	Function: mu_events_delete_event_hook
 
  	Fires when the user clicks "Permanently Delete' on an event in the trash
  	or clicks "Delete" in the Global Event Listing.
 
  	Parameters:
 
  		$event_id - the id of the event.
 
  	Uses Hook:
 
  		delete_post
 
 */
function mu_events_delete_event_hook( $event_id )
{
	global $wpdb ;
	$mu_event_id = get_post_meta( $event_id , MU_EVENTS_META_KEY_ID, true ) ;
	
	if( trim( $mu_event_id ) != '' )
	{
		$query = ' DELETE FROM ' . ___TABLE . ' WHERE id in ( %d ); ' ;
		$wpdb->query( $wpdb->prepare( $query, $mu_event_id ) );	
	}
}


/*
  	Function: mu_events_transition_post_status_hook
 
  	Fires *anytime* the a post/page/event status changes.
  	However, only functions when the object is an event post_type
  	and the new status is 'trash' -- in essence capturing when the user
  	clicks 'trash' on the Events list (non-global), 'Move to trash' on the
  	Edit event page or Global Events List.
 
  	This method simply sets the item as non-active, and unavailable to the list.
 
  	Parameters:
 
  		$new_status		- the new status for this event object.
  		$old_status		- the previous status for this event object.
  		$event_object	- object containing the event ( post ) details.
 
  	Uses Hook:
 
  		transition_post_status
 
 */
function mu_events_transition_post_status_hook( $new_status , $old_status , $post ) 
{
	global $wpdb ;
	
	$pt = get_post_type( $post ) ;
	
	if( 'event' == $pt && 'trash' == $new_status )
	{
		$mu_event_id = get_post_meta( $post->ID , MU_EVENTS_META_KEY_ID, true ) ;
		if( trim( $mu_event_id ) != '' )
		{
			$rows = $wpdb->update(
				___TABLE , 
				array( 'is_active' => '0' ) ,
				array( 'ID' => $mu_event_id ) , 
				array( '%d' ) , 
				array( '%d' )
			);
		}
	}
}



?>