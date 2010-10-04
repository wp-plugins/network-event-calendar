<?php
/** 
 	File: widget_class.php 
	
		WordPress Usage: Multi-Site Events Widget Class
  
 	Last Edited: 
	
		Jer Brand on 2010/09/27
 */

/*
 *	Class: MU_Events_Widget
 *
 *	WordPress Widget Class for Multi-Site Events Plugin. 
 *
 */
class MU_Events_Widget extends WP_Widget 
{
	// Making this class level so we only have to load it when we create a class. 
	// I'm sure the query isn't that big of a deal, but I'm paranoid about speed.
	var $allowed_sites ;
	
	
	/*
	 	Constructor: MU_Events_Widget
	 
	 	Registers the widget and creates the $allowed_sites array for later use. 
	 
	 */
	function MU_Events_Widget( ) 
	{
		global $wpdb ;
		
		$widget_ops = array( 
							'classname' => MU_EVENTS_PLUGIN_ID,
							'description' => __( 'Display a list of events from multiple sites in your network.' , MU_EVENTS_PLUGIN_ID ) 
							);

		/* Widget control settings. */
		$control_ops = array(
							 'width' => 250, 
							 'height' => 200, 
							 'id_base' => MU_EVENTS_PLUGIN_ID 
							 );

		/* Create the widget. */
		$this->WP_Widget( 
						 MU_EVENTS_PLUGIN_ID, __( 'Mult-Site Events' , MU_EVENTS_PLUGIN_ID ) , 
						 $widget_ops , 
						 $control_ops 
						 );
		
		/* sites query */
		$__sites = $wpdb->get_results( 
			$wpdb->prepare( 
				"SELECT blog_id, path 
				 FROM $wpdb->blogs 
				 WHERE public = '1' 
				 AND archived = '0' 
				 AND spam = '0' 
				 AND deleted = '0' 
				 ORDER BY blog_id DESC" 
			)
		);
		
		/* create the array to be indexed by the blog_id */
		$this->allowed_sites = array( ) ;
		foreach( $__sites as $s )
		{
			$this->allowed_sites[ $s->blog_id ] = $s ;
		}
	}


	/*
	 *	Method: form
	 *
	 *	Displays the Widget editor form for the user.
	 *
	 */
	function form( $instance ) 
	{
		$defaults = array(
			'title'					=> __( "Events" , MU_EVENTS_PLUGIN_ID ),
			'months' 				=> 1,
			'startmonth' 			=> 'today',
			'limit' 				=> -1,
			'orderby' 				=> 'time',
			'orderas' 				=> 'ASC' ,
			'sites' 				=> "1" ,
			'fields' 				=> "time, title, text, url, site_id, is_active, is_global",
			'events_url'			=> ''
		);
		
		$instance = wp_parse_args( ( array ) $instance, $defaults );
		
		// Title
		echo sprintf(
					 	'<p><label>%s <input class="widefat" id="%s" name="%s" type="text" value="%s"></label></p>' ,
						__( "Title:" , MU_EVENTS_PLUGIN_ID ),
						$this->get_field_id( 'title' ) ,
						$this->get_field_name( 'title' ) ,
						$instance['title'] 
		) ;
		
		echo sprintf( 
					 	'<p><label>%s <input class="widefat" id="%s" name="%s" type="text" value="%s"></label></p>' , 
						__( "Event Page URL:", MU_EVENTS_PLUGIN_ID ) , 
						$this->get_field_id( 'events_url' ) , 
						$this->get_field_name( 'events_url' ) , 
						$instance['events_url'] 
		) ;
		
		// Months
		echo sprintf( 
					 	'<p><label for="%s">%s</label>' , 
						$this->get_field_id( 'months' ) ,
						__( "Number of months to show:" , MU_EVENTS_PLUGIN_ID )
					) ;
		
		echo sprintf( 
					 '<input id="%s" name="%s" type="text" value="%d" size="2"></p>' , 
					 $this->get_field_id( 'months' ) , 
					 $this->get_field_name( 'months' ) , 
					 intval( $instance[ 'months' ] ) 
		) ;
		
		// Limit
		echo sprintf( 
					 	'<p><label for="%s">%s</label>' , 
						$this->get_field_id( 'limit' ) ,
						__( "Max Events to Show:" , MU_EVENTS_PLUGIN_ID )
					) ;
		
		echo sprintf( 
					 	'<input id="%s" name="%s" type="text" value="%d" size="2"><br/><small>%s</small></p>' , 
						$this->get_field_id( 'limit' ) , 
						$this->get_field_name( 'limit' ) , 
						intval( $instance[ 'limit' ] ) ,
						__( "( use -1 to show all )", MU_EVENTS_PLUGIN_ID ) 
		) ;
		
		
		// Start the list with the first / today
		echo sprintf( 
					 	'<p><label for="%s">%s</label>', 
						$this->get_field_id( 'startmonth' ) ,
						__( "Show events from:" , MU_EVENTS_PLUGIN_ID ) 
		) ;
		
		echo sprintf( 
					 	'<select id="%s" name="%s" class="widefat">' , 
						$this->get_field_id( 'startmonth' ), 
						$this->get_field_name( 'startmonth' ) 
		) ;
		
		echo sprintf( 
					 	'<option value="today" %s>%s</option>' , 
						$instance[ 'startmonth' ] == "today" ? 'selected="selected"' : "",
						__( "Beginning with user&apos;s current date", MU_EVENTS_PLUGIN_ID )
		) ;
		
		echo sprintf( 
					 	'<option value="first" %s>%s</option>' , 
						$instance['startmonth'] == "first" ? 'selected="selected"' : "", 
						__( "Beginning the 1st of the month" , MU_EVENTS_PLUGIN_ID ) 
		) ;
		echo '</select>' ;
		
		echo sprintf( 
					 	'<p><label for="%s">%s </label>' , 
						$this->get_field_id( 'orderby' ) ,
						__( "Order by:" ,  MU_EVENTS_PLUGIN_ID )
		) ;
		
		echo sprintf( 
					 	'<select id="%s" name="%s">' , 
						$this->get_field_id( 'orderby' ) , 
						$this->get_field_name( 'orderby' )
		) ;
		
		echo sprintf(
					 	'<option value="time" %s>%s</option>' , 
						$instance['orderby'] == "time" ? 'selected="selected"' : "" ,
						__( "Date" , MU_EVENTS_PLUGIN_ID )
		) ;
		
		echo sprintf( 
					 	'<option value="title" %s>%s</option>' , 
						$instance[ 'orderby' ] == "title" ? 'selected="selected"' : "" ,
						__( "Title" , MU_EVENTS_PLUGIN_ID )
		) ;
		echo sprintf( '</select>&nbsp;' ) ;
		
		echo sprintf( 
					 	'<select id="%s" name="%s">' , 
						$this->get_field_id( 'orderas' ) , 
						$this->get_field_name( 'orderas' )
		) ;
		
		echo sprintf( 
					 	'<option value="ASC" %s>%s</option>' , 
						$instance['orderas'] == "ASC" ? 'selected="selected"' : "" ,
						__( "ASC" , MU_EVENTS_PLUGIN_ID ) 
		) ;
		
		echo sprintf( 
					 	'<option value="DESC" %s>%s</option>', 
						$instance['orderas'] == "DESC" ? 'selected="selected"' : "" ,
						__( "DESC" , MU_EVENTS_PLUGIN_ID ) 
		) ;
		
		echo          '</select></p>' ;
		
		
		echo sprintf( 
					 	'<p><label>%s <input class="widefat" id="%s" name="%s" type="text" value="%s"></label></p>' ,
						__( "Display events for the sites:" , MU_EVENTS_PLUGIN_ID ) ,
					 	$this->get_field_id( 'sites' ) ,
						$this->get_field_name( 'sites' ) ,
						$instance['sites'] 
		) ;
		
		echo sprintf( 
					 	'<div>%s <span class="showHideSiteList" onclick="toggleSiteView( this );">%s</span></div>' , 
						__( "This field must be a comma separated list of site id&apos;s you want displayed." , MU_EVENTS_PLUGIN_ID ) ,
						__( "Display a list of all sites." , MU_EVENTS_PLUGIN_ID )
		);
		echo          '<div class="mueventssitelist hiddenList">' ;
		foreach( $this->allowed_sites as $site ) 
		{
			echo sprintf( '<div style="padding:4px;" onclick="document.getElementById(\'%s\').value += \',%d\'">%d - %s</div>' , $this->get_field_id( 'sites' ) ,  $site->blog_id , $site->blog_id ,  ( $site->path == "/" ? "Main Site" : str_replace("/", "", $site->path ) ) ) ;
		}
		echo          '</div>' ;
	}
	
	/*
	 *	Method: update
	 *
	 *	Validates and saves data from the widget form.
	 *
	 */
	function update( $new_instance , $old_instance ) 
	{
		$instance = $old_instance;
		
		$instance['title'] 		= strip_tags( $new_instance['title'] ) ;
		$instance['events_url'] = strip_tags( $new_instance['events_url'] ) ;
		
		$_m = intval(strip_tags( $new_instance['months'] )) ;
		
		if($_m > 0 && $_m < 13)
		{
			$instance['months']	=  $_m ;
		}
		else
		{
			$instance['months']	=  1 ;
		}
		
		if( trim(strip_tags( $new_instance['startmonth'] )) == "first")
		{
			$instance['startmonth'] = 'first' ;	
		}
		else
		{
			$instance['startmonth'] = 'today' ;	
		}
		
		if( trim(strip_tags( $new_instance['orderby'] )) == "title")
		{
			$instance['orderby'] = 'title' ;	
		}
		else
		{
			$instance['orderby'] = 'time' ;	
		}
		
		if( trim(strip_tags( $new_instance['orderas'] )) == "DESC")
		{
			$instance['orderas'] = 'DESC' ;	
		}
		else
		{
			$instance['orderas'] = 'ASC' ;	
		}
		if( trim(strip_tags( $new_instance['limit'] )) == "-1" || intval( $new_instance['limit'] ) == 0 )
		{
			$instance['limit'] = '-1' ;	
		}
		else
		{
			$instance['limit'] = intval( $new_instance['limit'] ) ;	
		}
		
		
		$s = split( ",", $new_instance[ 'sites' ] ) ;
		$s1 = array( ) ;
		
		foreach($s as $_s)
		{
			array_push( $s1 , intval( $_s ) ) ;	
		}
		
		$instance['sites'] 	= join( "," , $s1 ) ;
		return $instance ;
	}

	/*
	 *	Method: widget
	 *
	 *	Renders the widget.
	 *
	 */
	function widget( $args , $instance ) 
	{
		extract( $args ) ;
		
		$eventArgs  = "" ;
		$eventArgs .= "months=" . $instance[ 'months' ] ;
		$eventArgs .= "&startmonth=" . $instance[ 'startmonth' ] ;
		$eventArgs .= "&orderby=" . $instance[ 'orderby' ] ;
		$eventArgs .= "&orderas=" . $instance[ 'orderas' ] ;
		$eventArgs .= "&sites=" . $instance[ 'sites' ] ;
		$eventArgs .= "&limit=" . $instance[ 'limit' ] ;
		
		
		$events = the_events( $eventArgs , false , false ) ;
		
		echo $before_widget;
		
		if ( !empty( $instance['title'] )  )
		{
			echo $before_title . $instance['title'] . $after_title ;
		}
		
		echo $events ;
		
		if ( !empty( $instance['events_url'] )  )
		{
			echo sprintf( 
						 	'<a href="%s" class="mu-events-widget-show-page">%s</a>' ,
							$instance['events_url'] ,
							__( "View Events &raquo;" , MU_EVENTS_PLUGIN_ID )
			) ;
		}
		
		echo $after_widget;
	}

}
/*
 *	Function: mu_events_load_widget
 *
 *	Registers the widget class with the WordPress System.
 *
 *	Uses Hook:
 *
 *		widgets_init
 *
 */
function mu_events_load_widget( ) 
{
	register_widget( 'MU_Events_Widget' );
}
?>