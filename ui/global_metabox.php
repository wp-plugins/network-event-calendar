<?php
/** 
 *	File: global_metabox.php
 *	
 *	WordPress Usage: WordPress Custom Edit Event Panel
 * 
 *	Last Edited: 
 *		Jer Brand on 2010/09/29
 */
 
/*
 *	Function: mu_events_global_metabox
 *
 *	Adds the "Global Event" panel to the event create/edit page.
 *
 *	Uses Hook:
 *
 *		admin_menu
 */
function mu_events_global_metabox( )
{
	add_meta_box(
				MU_EVENTS_BOX_ID, 
				__( 'Global Event', MU_EVENTS_PLUGIN_ID ), 
				'mu_events_global_panel', 
				'event', 
				'side' 
	);
}

/*
 *	Function: mu_events_global_panel
 *
 *	Creates the content for the global event panel.
 *
 *	Uses Hook:
 *
 *		add_meta_box
 */
function mu_events_global_panel( )
{
	global $post , $blog_id ;
	if( 1 != $blog_id )
	{
		$is_global =  get_post_meta( $post->ID , MU_EVENTS_META_KEY_CREATE_AS_GLOBAL , true )  ;
		$checked = ( $is_global == "1" ) ? 'checked="checked"' : "" ; 
		echo '<input type="checkbox" ' . $checked . ' id="mu_event_is_global" name="mu_event_is_global" value="1" />' ;
		echo '<label for="mu_event_is_global">&nbsp;' . __("This event is <b>Global</b>.", MU_EVENTS_PLUGIN_ID ) . '</label>' ;
	}
	else
	{
		echo __( '<p>Main site events are always global.</p>' , MU_EVENTS_PLUGIN_ID ) ;
	}
}
?>