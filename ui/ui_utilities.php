<?php
/** 
 *	File: ui_utilities.php
 *
 *	WordPress Usage: Misc UI Methods
 *	
 *	This file contains various methods used by the UI. 
 *	These methods were relocated here for clarity.
 *
 *	Last Edited: 
 *	
 *		Jer Brand on 2010/09/20
 */


/**
 *	Function: mu_events_handle_delete_event
 *
 *	Called when the administration page detects the delete paramater and correct nonce.
 *	Deletes the event from the posts listing and additionally checks to ensure that 
 *	the entry has been removed from the shadow-table.
 */
function mu_events_handle_delete_event( )
{
	global $wpdb , $blog_id; 
	
	$ds = intval( $_GET[ 'ds' ] ) ;
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . ___TABLE . ' WHERE id=%d', $ds ) ) ;
	
	if( !empty( $row ) )
	{
		$title = "Event “{$row->title}”" ;
		if( $blog_id != $row->site_id )
    		switch_to_blog( $row->site_id ) ;
		
		wp_delete_post( $row->event_id , true ) ; 
		
		if( $blog_id != $row->site_id )
    		restore_current_blog( );	

/* ****************************** FALLBACK DELETE *********************************** 
	On the off-chance that an event-custom-post-type is delted, but the shadow-entry
	is still floating around, this piece checks to see if the entry is still there,
	and if so, deletes it. 
   ********************************************************************************** */
   		$query = $wpdb->prepare( 'SELECT * FROM ' . ___TABLE . ' WHERE id=%d', $ds ) ;
		$row = $wpdb->get_row( $query ) ;
		if( !empty( $row ) )
		{
			$title = sprintf( __("The shadow-entry for event “$s”" , MU_EVENTS_PLUGIN_ID ) ,  $row->title );
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . ___TABLE . ' WHERE id=%d', $ds ) ) ;
		}
/* ****************************** END FALLBACK DELETE *********************************** */
		
		add_option( 
				   	MU_EVENTS_OPTION_STATUS_MESSAGE , 
				   	sprintf( __( "“%s” has been deleted." ), $title )
				   );
		
		wp_redirect( admin_url( 'edit.php?post_type=event&page=mu-events' ) ) ;
		exit ;
	}
}
/**
 *	Function: mu_events_handle_event_toggle_active
 *
 *	Called when the administration page detects the trash paramater and correct nonce.
 *	Sends the event to the trash and triggers the corresponding hook. If the item is already
 *	in the trash, this method will re-publish the event.
 */
function mu_events_handle_event_toggle_active( )
{
	global $wpdb ; 
	
	$d = intval( $_GET[ 'd' ] ) ;
	$query = $wpdb->prepare( 'SELECT * FROM ' . ___TABLE . ' WHERE id=%d', $d ) ;
	$row = $wpdb->get_row( $query ) ;
	if( !empty( $row ) )
	{
		if( $blog_id != $row->site_id )
    		switch_to_blog( $row->site_id ) ;

		$post = get_post( $row->event_id ) ;
		
		if( !empty( $post ) )
		{
			if( 'trash' == $post->post_status )
			{
				wp_publish_post( $row->event_id );
				$active = __( "active" , MU_EVENTS_PLUGIN_ID ) ; 
			}
			else
			{
				// not sure about this one -- it works, but isn't listed in the codex.
				// see: http://wordpress.org/support/topic/wp_delete_post-wp_trash_post
				wp_trash_post( $row->event_id ) ;	
				$active = __( "inactive" , MU_EVENTS_PLUGIN_ID ) ;
			}
		}
		
		if( $blog_id != $row->site_id )
    		restore_current_blog( );	

		add_option( 
				   MU_EVENTS_OPTION_STATUS_MESSAGE , 
				   sprintf( 
						   __('Event “%1$s” has been marked as %2$s.' , MU_EVENTS_PLUGIN_ID ),
						   $row->title ,
						   $active 
						   )
					) ;
		wp_redirect( admin_url( 'edit.php?post_type=event&page=mu-events' ) ) ;
		exit ;
	}
}

/**
 *	Function: mu_events_admin_title
 *
 *	Outputs the title of the admin page and an "add New" button.
 */
function mu_events_admin_title( ) 
{
	echo '<div id="icon-edit" class="icon32"><br></div>' ;
	echo sprintf('<h2>%s <a href="%s" class="button add-new-h2">%s</a></h2>', __("Global Events List", MU_EVENTS_PLUGIN_ID ),  mu_events_build_admin_url( 'post-new.php?post_type=event' ), __("Add New", MU_EVENTS_PLUGIN_ID ) ) ;
}

/**
 *	Function: show_status_messages
 *
 *	Display's any status messages stored in wp-option MU_EVENTS_OPTION_STATUS_MESSAGE
 *	then clears that variable.
 */
function show_status_messages( )
{
	$message = get_option( MU_EVENTS_OPTION_STATUS_MESSAGE , NULL ) ;
	if( !is_null($message))
	{
		echo "<div class='updated below-h2'><p>$message</p></div>" ;	
		delete_option( MU_EVENTS_OPTION_STATUS_MESSAGE ) ;
	}	
}

/*
	 *	Function: mu_events_build_admin_url
	 *
	 *	Creates an admin interface URL using the correct blog/site and rolling in nonce and param organization
	 *
	 *	Parameters:
	 *
	 *		$url 		- [String, Required] The base URL without wp-admin.
	 *		$params 	- [Array, Optional] An array of properties => values to be used as _GET parameters. Default is an empty array.
	 *		$site_id	- [Integer, Optional] The ID of the site. Default is 1 ( '/' ).
	 *		$nonce		- [String, NOnce, Optional] NOnce (Number Used Once) ID for this action. Default is empty string.
	 *
	 *	Returns:
	 *
	 *		The final URL using the parameters provided.
	 */
function mu_events_build_admin_url( $url , $params = array( ) , $site_id = 1 , $nonce = '' )
{
	global $blog_id ;
	
	if( $site_id != $blog_id) 
		switch_to_blog( $site_id  ) ;
	

	if( 0 != sizeof( $params ) )
	{
		$params = array_filter( $params , '__mu_events_admin_url_filter' ) ;
		
		$args = http_build_query ( $params ) ;
		
		if( strpos( $url , "?" ) !== FALSE )
			$url .= "&" . $args ;
		else
			$url .= "?" . $args ;
	}
	$url =  admin_url( $url ) ;
	
	if( !empty( $nonce ) )
	{
		$url = wp_nonce_url( $url , $nonce ) ;
	}
	
	if( $site_id != $blog_id) 
		restore_current_blog( );
		
	return $url ;
} 

/*
 *	Function: __mu_events_admin_url_filter
 *
 *	Filters out empty elements from the params array.
 *
 *	See Also
 *		<mu_events_build_admin_url>
 */
function __mu_events_admin_url_filter( $field )
{
	return  ( !empty( $field ) && !( '' == trim( $field ) ) && !( is_numeric( $field ) ? 0 == intval( $field ) : FALSE ) )  ;
}

/*
 *	Function: mu_events_render_month_filter_ui
 *
 *	Renders the month / year dropdown list
 */
function mu_events_render_month_filter_ui( )
{
	global $wpdb , $wp_locale, $render_filter_button , $m ;

	$arc_query = $wpdb->prepare("SELECT DISTINCT YEAR(time) AS yyear, MONTH(time) AS mmonth FROM " . ___TABLE . " ORDER BY time DESC");
	$arc_result = $wpdb->get_results( $arc_query );
	$month_count = count($arc_result);
	
	if ( $month_count && !( 1 == $month_count && 0 == $arc_result[ 0 ]->mmonth ) ) 
	{
		$render_filter_button  = true ;
		echo '<select name="m">' ;
        echo sprintf( '<option %s value="0">%s</option>', selected( $m, 0 ) ,  __("Show all dates", MU_EVENTS_PLUGIN_ID ) ) ;
		foreach ( $arc_result as $arc_row )
		{
			if ( $arc_row->yyear == 0 )
				continue ;

			$arc_row->mmonth = zeroise( $arc_row->mmonth , 2 ) ;

			if ( $arc_row->yyear . $arc_row->mmonth == $m )
				$default = ' selected="selected"';
			else
				$default = '';

			echo "<option$default value='" . esc_attr( "$arc_row->yyear$arc_row->mmonth" ) . "'>";
			echo $wp_locale->get_month( $arc_row->mmonth ) . " $arc_row->yyear";
			echo "</option>\n";
		}
    echo '</select>' ;
	}
}

/*
 *	Function: mu_events_render_site_filter_ui
 *
 *	Renders the site/blog dropdown list
 */
function mu_events_render_site_filter_ui( ) 
{
	global $wpdb , $wp_locale , $render_filter_button, $s ;
	
	$site_query = $wpdb->prepare( "SELECT DISTINCT site_id FROM " . ___TABLE . " ORDER BY site_id ASC ;" );
	$site_result = $wpdb->get_results( $site_query ) ;
	$site_count = count( $site_result ) ;

	if ( $site_count && !( 1 == $site_count ) ) 
	{
		$render_filter_button  = true ;
		
    	echo '<select name="s">' ;
		echo sprintf( '<option %s value="0">%s</option>', selected( $s , 0 ) , __( "Show all sites" , MU_EVENTS_PLUGIN_ID ) ) ;
		foreach ( $site_result as $site_row )
		{
			if ( $site_row->site_id == $s )
				$default = ' selected="selected"';
			else
				$default = '' ;

			echo "<option$default value='" . esc_attr( $site_row->site_id ) . "'>" ;
			echo get_blog_details( $site_row->site_id )->blogname ;
			echo "</option>\n";
		}
		echo '</select>' ;
	} 
}
/*
 *	Function: mu_events_render_filter_button_ui
 *
 *	Renders the apply filter button
 */
function mu_events_render_filter_button_ui( )
{
	global $render_filter_button  ;
	
	if( $render_filter_button ) 
		echo sprintf( '<input type="submit" id="post-query-submit" value="%s" class="button-secondary">', __( "Filter" , MU_EVENTS_PLUGIN_ID ) ) ;
}
/*
 *	Function: mu_events_get_global_list_event_count
 *
 *	Returns:
 *	
 *		The number of events in the current filter query.
 */
function mu_events_get_global_list_event_count( ) 
{
	global $wpdb , $m , $s ;
	
	$count = 'SELECT COUNT(id) as eventCount FROM ' . ___TABLE ;
	$where = "" ;
	
	if( ! empty ( $s ) )
	{
		$where  = sprintf( " site_id in ( %d ) " , intval( $s ) ) ;
	}
	if( ! empty ( $m ) )
	{
		if( strlen( $where ) > 1 )
			$where  .= sprintf( " AND YEAR(time) = '%s' AND MONTH(time) = '%s'", substr( $m , 0 , 4 ) , substr( $m , 4 ) )  ; 
		else
			$where  = sprintf( " YEAR(time) = '%s' AND MONTH(time) = '%s'", substr( $m , 0 , 4 ) , substr( $m , 4 ) ); 
	}
	
	if( strlen( $where ) > 0 )
		$count .= " WHERE " . $where ;
	
	$result = $wpdb->get_results( $count ) ;
	
	return   $result[ 0 ]->eventCount ;
}

/*
 *	Function: mu_events_get_global_event_list
 *
 *	Returns:
 *	
 *		The an array of result objects containing the event details.
 */
function mu_events_get_global_event_list( )
{
	global $wpdb , $m , $s , $paged ;
	
	$query = 'SELECT * FROM ' . ___TABLE ;
	$where = "" ;
	
	if( ! empty ( $s ) && $s != 0 )
	{
		$where  = sprintf( " site_id in ( %d ) " , intval( $s ) ) ;
	}
	
	if( ! empty ( $m ) && $m != 0)
	{
		if( strlen( $where ) > 1 )
			$where  .= sprintf( " AND YEAR(time) = '%s' AND MONTH(time) = '%s'", substr( $m , 0 , 4 ) , substr( $m , 4 ) )  ; 
		else
			$where  = sprintf( " YEAR(time) = '%s' AND MONTH(time) = '%s'", substr( $m , 0 , 4 ) , substr( $m , 4 ) ); 
	}
	
	if( strlen( $where ) > 0 )
	{
		$query .= " WHERE " . $where ;
	}
	$query = sprintf( $query . " ORDER BY time DESC" . " LIMIT %d, %d", (MU_EVENTS_ADMIN_EVENT_LIMIT * ( $paged-1 ) ) , MU_EVENTS_ADMIN_EVENT_LIMIT ) ;

	return $wpdb->get_results( $query ) ;
}

/*
 *	Function: mu_events_render_pagination
 *
 *	Renders the pagination controls for the admin page.
 */
function mu_events_render_pagination( ) 
{ 
	global $event_count , $paged ;
	
	$pages = ceil( $event_count / MU_EVENTS_ADMIN_EVENT_LIMIT ) ;
	
	if ( $pages > 1 ) 
	{ 
		echo '<div class="tablenav"><div class="tablenav-pages">' ;
		echo '<span class="displaying-num">' ;
		echo sprintf( 
						 __('Displaying %1$s - %2$s of %3$s' , MU_EVENTS_PLUGIN_ID ) , 
						 $limit * ($p - 1) + 1 ,
						 $limit * $p , 
						 $event_count 
					 ) ;
		echo "</span>" ;
		
		for ( $i = 1 ; $i <= $pages ; $i++ )  
		{ 
			if( $i == $paged )
				echo sprintf('<span class="page-numbers current">%s</span>', $i )  ;
			else
			{
				$page_url = mu_events_build_admin_url( 	'edit.php' , 
																					array(  
																						'post_type' => 'event' , 
																						'page' =>'mu-events' ,
																						'noheader' => 'true' ,
																						'm' => $m ,
																						's' => $s ,
																						'paged' => $paged 
																					)
																				) ;
				echo sprintf('<a class="page-numbers" href="%s">%d</a>', $page_url , $i ) ;
			}
		}
		$max_url = mu_events_build_admin_url( 	'edit.php' , 
																					array(  
																						'post_type' => 'event' , 
																						'page' =>'mu-events' ,
																						'noheader' => 'true' ,
																						'm' => $m ,
																						's' => $s ,
																						'paged' => $paged 
																					)
																				) ;
		echo sprintf('<a class="page-numbers" href="%s">»</a>', $max_url  ) ;
		echo '</div></div>' ;
	}
}
?>