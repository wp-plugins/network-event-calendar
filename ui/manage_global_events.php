<?php 
/** 
 *	File: manage_global_events.php
 *
 *	WordPress Use: Global Events Administration UI; This file contains the HTML UI of the admin panel for the plugin.
 *
 *	Last Edited: 
 *
 *		Jer Brand on 2010/10/06
 */

global $wpdb, $blog_id, $wp_locale, $switched , $s , $m , $paged , $event_count ; 

$s 		 = isset( $_GET[ 's' ] ) ? ( int )$_GET[ 's' ] : 0 ; 
$m 		 = isset( $_GET[ 'm' ] ) ? $_GET[ 'm' ] : "" ; 
$paged = isset( $_GET[ 'paged' ] ) ? ( int )$_GET[ 'paged' ] : 1 ; 

if( isset( $_GET[ 'ds' ] )  && check_admin_referer( MU_EVENTS_NONCE_DELETE ) )
{
	mu_events_handle_delete_event( ) ;
}

if( isset( $_GET[ 'd' ] ) && check_admin_referer( MU_EVENTS_NONCE_TOGGLE_ACTIVATE ) )
{
	mu_events_handle_event_toggle_active( ) ;
}

if ( isset( $_GET['noheader'] ) )
{
	require_once( ABSPATH . 'wp-admin/admin-header.php' ) ;
}
?>
<div class="wrap">
<?php echo mu_events_admin_title( )  ; ?>
<?php show_status_messages( ) ; ?>
<?php
	$event_count = mu_events_get_global_list_event_count( ) ; 
	
	if( $event_count > 0 )
	{
		$results = mu_events_get_global_event_list( ) ; 
?>
<form id="posts-filter" action="<?php echo admin_url('edit.php'); ?>" method="get">
	<input type="hidden" name="post_type" value="event" />
    <input type="hidden" name="page" value="mu-events" />
	<div class="tablenav">
		<div class="alignleft actions">
<?php mu_events_render_month_filter_ui( ) ; ?>
<?php mu_events_render_site_filter_ui( )  ; ?>
<?php mu_events_render_filter_button_ui( ) ; ?>
		</div>
		<div class="clear"></div>
	</div>
</form>
	<table class="widefat post fixed" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" id="title" class="manage-column column-title" style="">
					<?php _e("Title", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
				<th scope="col" id="site" class="manage-column column-author" style="">
					<?php _e("Site", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
				<th scope="col" id="active" class="manage-column column-author" style="">
					<?php _e("Active", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
                <th scope="col" id="global" class="manage-column column-author" style="">
					<?php _e("Global", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
				<th scope="col" id="date" class="manage-column column-date" style="">
					<?php _e("Date", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
			</tr>
		</thead>
		<tfoot>
		<tr>
			<th scope="col" id="title" class="manage-column column-title" style="">
					<?php _e("Title", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
				<th scope="col" id="site" class="manage-column column-author" style="">
					<?php _e("Site", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
				<th scope="col" id="active" class="manage-column column-author" style="">
					<?php _e("Active", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
                <th scope="col" id="global" class="manage-column column-author" style="">
					<?php _e("Global", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
				<th scope="col" id="date" class="manage-column column-date" style="">
					<?php _e("Date", MU_EVENTS_PLUGIN_ID ) ;?>
				</th>
		</tr>
		</tfoot>
	
		<tbody>
<?php 
	foreach ( $results as $result ) 
	{ 
		$current_site  	= get_blog_details( $result->site_id, true ) ;
		$edit_url			= mu_events_build_admin_url( 'post.php' , array ('post' => $result->event_id , 'action' => 'edit' ) , $result->site_id )  ;
		
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# # # # # # # # # # # # # #        BUGFIX 0.8.2         # # # # # # # # # # # # #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
// Changed the blog parameter for these links to the root blog.
		
		$delete_url		= mu_events_build_admin_url( 	'edit.php' , 
																				array( 
																					'post_type' => 'event' , 
																					'page' =>'mu-events' ,
																					'noheader' => 'true' ,
																					'm' => $m ,
																					's' => $s ,
																					'paged' => $paged ,
																					'ds' => $result->id
																				), 
																				1,
																				MU_EVENTS_NONCE_DELETE ) ;
		$trash_url		= mu_events_build_admin_url( 	'edit.php' , 
																					array( 
																						'post_type' => 'event' , 
																						'page' =>'mu-events' ,
																						'noheader' => 'true' ,
																						'm' => $m ,
																						's' => $s ,
																						'paged' => $paged ,
																						'd' => $result->id
																					), 
																					1,
																					MU_EVENTS_NONCE_TOGGLE_ACTIVATE ) ;
?>
		
	<tr id="post-142" class="alternate author-self status-publish iedit" valign="top">
		<td class="post-title column-title">
			<strong><a class="row-title" href="<?php echo $edit_url ; ?>" title="Edit “<?php echo $result->title; ?>”"><?php echo $result->title; ?></a></strong>
			<div class="row-actions">
				<span class="edit"><a href="<?php echo $edit_url ; ?>" title="Edit this item">Edit</a> | </span>
				<span class="delete"><a href="<?php echo $delete_url ; ?>" class="submitdelete" title="<?php _e("Deletes every record of this item (bypasses trash)", MU_EVENTS_PLUGIN_ID ) ;?>">Delete</a> | </span>
				<span class="active"><a href="<?php echo $trash_url ; ?>" title="<?php echo intval( $result->is_active ) > 0 ? __("Deactivate", MU_EVENTS_PLUGIN_ID ) : __( "Activate", MU_EVENTS_PLUGIN_ID ) ; ?> “<?php echo $result->title; ?>”"><?php echo intval( $result->is_active ) > 0 ? __("Deactivate", MU_EVENTS_PLUGIN_ID ) : __( "Activate", MU_EVENTS_PLUGIN_ID ) ; ?></a> | </span>
                <span class="view"><a href="<?php echo $result->url ; ?>" title="View “<?php echo $result->title; ?>”" rel="permalink"><?php _e("View Event" , MU_EVENTS_PLUGIN_ID ) ; ?></a></span>
			</div>
		</td>
		<td class="site column-site"><?php echo $current_site->path; ?></td>
		<td class="active column-active"><?php echo intval( $result->is_active ) > 0 ? __( "active" , MU_EVENTS_PLUGIN_ID ) : __( "inactive" , MU_EVENTS_PLUGIN_ID ) ; ?></td>
        <td class="active column-active"><?php echo intval( $result->is_global ) > 0 ? __( "global" , MU_EVENTS_PLUGIN_ID ) : __( "local" , MU_EVENTS_PLUGIN_ID ) ; ?></td>
		<td class="date column-date"><?php echo date( __( "Y/m/d H:i:s a" , MU_EVENTS_PLUGIN_ID ) , strtotime( $result->time ) ) ; ?></td>
	</tr>
<?php } ?>
	</tbody>
</table>
<?php mu_events_render_pagination( )  ; ?>
<?php  }  else { ?>
	<p>No events found.</p>
<?php 
if( $s != 0 || $m != 0 ) 
{ 
	echo sprintf( '<a href="%s">%s</a>' , mu_events_build_admin_url( 	'edit.php' , array( 'post_type' => 'event' , 'page' => 'mu-events') ),  __("Clear Filters", MU_EVENTS_PLUGIN_ID ) ); 
} 
?>
<?php } ?>