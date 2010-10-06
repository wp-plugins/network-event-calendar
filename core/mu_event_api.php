<?php
/* File: 

		Plugin API (mu-events/core/mu_event_api.php) 
   
   Last Edited: 
   
   		Jer Brand on 2010/10/06
 */


/* Function: the_events

   Event Listing method patterned after WordPress internal the_* functions.
 
   This event is not used internally -- it is provided for developers wanting a simple
   method of outputing events in their themes.

   Internally, this method utilizes get_the_events() method with a different set of
   default parameters and a fixed set of fields.
 
   Parameters:
 
   	$args 		- 	[string/array] The arguments used to refine the event query simimlar to 
   					WordPress' internal query_posts(). See "$args Values" below
	$showText	-	[boolean] true if you want this method to output the exerpt text; false
					otherwise. The default is true.
	$echoHTML	-	[boolean] true if you want this method to output the html markup to stdout;
					false if you would like the method to return the html formatted string.
					the default is true.
			
   $args Values:
   
   	months				-	[int] The number of months worth of events that 
   							should be displayed. The default is 1.
   	startmonth			-	[string] Whether to start the month query on the first 
   							day of the month or on the day the user views the event list.
   							Valid values are "today" and "first".
   	events_dated_after	-	[string] "YYYY-mm-dd" formatted date. If both events_dated_after
   							and events_dated_before are supplied, months and startmonth
   							are ignored.
   	events_dated_after	-	[string] "YYYY-mm-dd" formatted date. If both events_dated_after
   							and events_dated_before are supplied, months and startmonth
   							are ignored.
   	limit				-	[int]The maximum number of events to display. Use -1 to show all events
   							The default is -1.
   	paged				-	[int] Currently not used -- will allow pagination in future versions.
   	orderby				-	[string] The name of a field to order the events. The default is 'time'.
   	orderas				-	[string] Either 'ASC' or 'DESC'. The default is 'ASC'.
   	sites				-	[array|string] An array of blog/site ids. Integer values are required.
   							This can optionally be a comma seperated list of integers.
   							By Default, sites is array( 1 ), which will show only global events.
 
   Returns:
   
   		If $echoHTML is set to true, outputs the html to stdout; If false, returns an HTML formatted string.
 
   Example:
 
 >		$event_args = array(
 >			'months' 		=>  6,
 >			'startmonth'	=>  'first', 
 >			'sites'			=>  array( 1, 4, )	
 >		);
 >		the_events( $event_args ) ;
   	This example can also be written:
   	
 >		the_events( "months=6&startmonth=first&sites=1,4" ) ;
 
   See Also:
   	<get_the_events>
 
 */
function the_events( $args = array( ), $showText = true, $echoHTML = true )
{
	global $long_months_list, $long_week_days ;
	$defaults = array(
		'events_dated_after' 	=> "",
		'events_dated_before' 	=> "",
		'months' 				=> 1,
		'startmonth' 			=> 'today',
		'limit' 				=> -1,
		'paged' 				=> 1,
		'orderby' 				=> 'time',
		'orderas' 				=> 'ASC',
		'sites' 				=> array( 1 ),
		'fields' 				=> "time, title, text, url, site_id, is_global"
	) ;
	
	$args = wp_parse_args( $args, $defaults );
	
	// override the parameters -- we need specific ones.
	if( true == $showText )
		$args['fields'] = "time, title, text, url, site_id, is_global" ;
	else
		$args['fields'] = "time, title, url, site_id, is_global" ;
		
	$events = get_the_events( $args ) ;
	
	$html = "<ol class=\"eventlist\">\n" ;
	foreach( $events as $event )
	{
		$current_site  	= get_blog_details( $event->site_id , true ) ;
		
		// Provide classes for the user to style against
		$class = $current_site->path ;
		if( $class == "/")
			$class = "parentsite"	;
		else
			$class = str_replace( "/", "", $class ) ;
		
		if( $event->is_global == 1 )
			$class .= " global" ;
		
		$the_date = strtotime( $event->time ) ;
		
		$html .= sprintf( "\t<li class=\"event %s\">\n", $class) ; 
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
# # # # # # # # # # # # # #        BUGFIX 0.8.3         # # # # # # # # # # # # #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
// fixed the display of these items -- forgot that single-quotes will display
// slashes....
		$dayofweek = sprintf('<span class="dayoweek %1$s">%1$s</span>',  date_i18n( "l", $the_date ) ) ;
/* 	translators: custom delimiter 1, see below.  */
		$delimiter1 = sprintf('<span class="date-delimiter">%1$s</span>'	, _x(", " , 'Date Delimiter 1 (%2$s)' , MU_EVENTS_PLUGIN_ID ) ) ;
/* 	translators: custom delimiter 2, see below.  */
		$delimiter2 = sprintf('<span class="date-delimiter">%1$s</span>'	, _x(" "  , 'Date Delimiter 2 (%4$s)' , MU_EVENTS_PLUGIN_ID ) ) ;
/* 	translators: custom delimiter 3, see below.  */
		$delimiter3 = sprintf('<span class="date-delimiter">%1$s</span>'	, _x(", " , 'Date Delimiter 3 (%6$s)' , MU_EVENTS_PLUGIN_ID ) ) ;
		$month		= sprintf('<span class="month %1$s">%1$s</span>'		, date_i18n( "F", $the_date ) ) ;
		$day 		= sprintf('<span class="day day%1$s">%1$s</span>'		, date_i18n( "j", $the_date ) ) ;
		$year 		= sprintf('<span class="year year%1$s">%1$s</span>'	, date_i18n( "Y", $the_date ) )  ;

		$html .= "\t\t<div class=\"date\">" ;
/* 
	translators: Sorry for the ugly string: This string sets the order of the date and delimiter spans
				 for output to the browser in user-facing pages. The items in the list are:
				 %1$s = Day of week
				 %2$s = delimiter (custom delimiter 1 from above) ;
				 %3$s = Month name (full)
				 %4$s = delimiter (custom delimiter 2 from above) ;
				 %5$s = Numeric day
				 %6$s = delimiter (custom delimiter 3 from above) ;
				 %7$s = Full Year
				 
				 These can be reordered any way that works for your language, however
				 be very careful of spaces as you could create additional space in the string and upset the
				 delimiters listed above.
				 
				 If you're curious, this is done to wrap each of these items in spans to allow for image replacement
				 and grapical layouts -- I use them, constantly.
*/
		$html .= sprintf( 
						 __( '%1$s%2$s%3$s%4$s%5$s%6$s%7$s', MU_EVENTS_PLUGIN_ID ) ,
						 $dayofweek ,
						 $delimiter1 ,
						 $month ,
						 $delimiter2 ,
						 $day ,
						 $delimiter3, 
						 $year
		) ;
		$html .= "</div>\n";
		
		$html .= sprintf( "\t\t<a class=\"eventName\" href=\"%s\">%s</a>\n", $event->url , $event->title );
		if( true == $showText )
			$html .= sprintf( "\t\t<p>%s</p>\n", $event->text ) ;
		$html .= "\t</li>\n" ;
	}
	$html .= "</ol>\n" ;
	if( true == $echoHTML )
		echo $html ;
	else
		return $html ;
}

/* Function: get_total_events
 
   Returns the total number of unpaged events for the args provided.
 
   This method is provided for those wanting to create their own pagination 
   controls for the event listings.
 
   Parameters:
 
   	$args - [string/array] The arguments used to refine the event query simimlar to 
   			WordPress' internal query_posts().

   $args Values:

   	months				-	[int] The number of months worth of events that 
   							should be displayed. The default is 1.
   	startmonth			-	[string] Whether to start the month query on the first 
   							day of the month or on the day the user views the event list.
   							Valid values are "today" and "first".
   	events_dated_after	-	[string] "YYYY-mm-dd" formatted date. If both events_dated_after
   							and events_dated_before are supplied, months and startmonth
   							are ignored. The default is null.
   	events_dated_after	-	[string] "YYYY-mm-dd" formatted date. If both events_dated_after
   							and events_dated_before are supplied, months and startmonth
   							are ignored. The default is null.
   	sites				-	[array|string] An array of blog/site ids. Integer values are required.
   							This can optionally be a comma seperated list of integers.
   							By Default, sites is array( 1 ), which will show only global events.
   	fields				-	[array|string] An array of valid field names available from the events.
   							This value can also be represented by a comma seperated list of names, unquoted.
   							Valid values are "time", "title", "text", "url", "site_id", 
   							"event_id", "is_active", and "is_global".
 
   Returns:
 
   	An integer indicating the number of events for the args provided.
 
   Example:
 
 >		$event_args = array(
 >			'months' 		=>  6,
 >			'startmonth'	=>  'first', 
 >			'sites'			=>  array( 1, 4 ),
 >			'fields'		=>	array( "title", "url", "time")
 >		);
 >		
 >		$numEvents = get_total_events( $event_args ) ;
 >		$events = get_the_events( $event_args ) ;
 
   	This example can also be written:
 
 >		$numEvents = get_total_events( "months=6&startmonth=first&sites=1,4&fields=title,url,time" ) ;
 >		$events = get_the_events( "months=6&startmonth=first&sites=1,4&fields=title,url,time" ) ;
 *
 */
function get_total_events( $args = array( ) )
{
	global $wpdb ;
	
	$datewhere = "" ;
	$siteswhere = "" ;
	
	$defaults = array(
		'events_dated_after' 	=> "",
		'events_dated_before' 	=> "",
		'months' 				=> 1,
		'startmonth' 			=> 'today',
		'sites' 				=> array( 1 )
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	extract( $args, EXTR_SKIP );
	
	$query = "SELECT COUNT(id) as total_events FROM " . ___TABLE . " WHERE is_active=1 "  ;
	
	// if the $events_dated_* are set, use a date range query.
	if( !empty( $events_dated_before ) && !empty( $events_dated_before ) )
	{
		$datewhere = sprintf( 
			" AND time BETWEEN '%s' AND '%s'  " ,
			date( 'Y-m-d 00:00', strtotime( $events_dated_after ) ) ,
			date('Y-m-d 00:00' , strtotime( $events_dated_before ) ) 
		)  ;
	}
	//	otherwise use a "months" query to generate the date range. 
	else
	{
		if( trim( $startmonth ) == 'first' )
		{
			$datestart = date("Y-m-1 00:00") ;
		}
		else
		{
			$datestart = date("Y-m-d 00:00") ;
		}
		
		$months = intval($months) ;
		
		$dateend = date( "Y-m-d" , strtotime( date( "Y-m-d", strtotime($datestart ) ) . " +$months month") );
		$datewhere =  sprintf( " AND time BETWEEN '%s' AND '%s'  ",   $datestart , $dateend )  ;
	}
	
	// cast all the sites to ints
	$len = sizeof( $sites ) ;
	for( $i = 0 ; $i < $len ; $i++ )
	{
		$sites[ $i ] = intval( $sites[ $i ] ) ;
	}
	
	if( 1 == $len && $sites[ 0 ] == 1)
	{
		$siteswhere = " AND is_global=1" ;
		
	}
	else
	{
		$siteswhere = sprintf( " AND ( is_global=1 OR site_id in ( %s ) )" , join( ", ", $sites ) ) ;
	}
	
	$query .= $datewhere . $siteswhere ;
	$result = $wpdb->get_results( $query ) ;

	return $result[0]->total_events ;
}


/* Function: get_the_events
 
   Event Listing method patterned after WordPress internal get_the_* functions.
 
   This event is used internally to display all event listings. In addition, it has been
   provided for developers wanting a means of creating custom output for their event pages.
 
   Parameters:
 
   	$args - [string/array] The arguments used to refine the event query simimlar to 
   			WordPress' internal query_posts().
   
   $args Values:
   
   	months				-	[int] The number of months worth of events that 
   							should be displayed. The default is 1.
   	startmonth			-	[string] Whether to start the month query on the first 
   							day of the month or on the day the user views the event list.
   							Valid values are "today" and "first".
   	events_dated_after	-	[string] "YYYY-mm-dd" formatted date. If both events_dated_after
   							and events_dated_before are supplied, months and startmonth
   							are ignored. The default is null.
   	events_dated_after	-	[string] "YYYY-mm-dd" formatted date. If both events_dated_after
   							and events_dated_before are supplied, months and startmonth
   							are ignored. The default is null.
   	limit				-	[int]The maximum number of events to display. Use -1 to show all events
   							The default is -1.
   	paged				-	[int][future] The "page" of results; This value allows theme developers
   							to create pagination functionality for larger sites. This works, but 
   							is currently untested/unsupported. You can retrieve the total number of
   							events using the <get_total_events> method.
   	orderby				-	[string] The name of a field to order the events. The default is 'time'.
   	orderas				-	[string] Either 'ASC' or 'DESC'. The default is 'ASC'.
   	sites				-	[array|string] An array of blog/site ids. Integer values are required.
   							This can optionally be a comma seperated list of integers.
   							By Default, sites is array( 1 ), which will show only global events.
   	fields				-	[array|string] An array of valid field names available from the events.
   							This value can also be represented by a comma seperated list of names, unquoted.
   							Valid values are "time", "title", "text", "url", "site_id", 
   							"event_id", "is_active", and "is_global".
 
   Returns:
 
   	An array of objects containing the specified fields. Can return an empty array if there are no events
   	for the specified query.
 
   Example:
 
 >		$event_args = array(
 >			'months' 		=>  6,
 >			'startmonth'	=>  'first', 
 >			'sites'			=>  array( 1, 4 ),
 >			'fields'		=>	array( "title", "url", "time")
 >		);
 >		$events = get_the_events( $event_args ) ;
 
   	This example can also be written:
   	
 >		$events = get_the_events( "months=6&startmonth=first&sites=1,4&fields=title,url,time" ) ;
 
   Assuming there was existing event data, the output from the above calls
   would look similar to the output below:
 
 >
 >	Array
 >	(
 >	    [0] => stdClass Object
 >        (
 >            [time] => 2010-09-24 18:00:00
 >            [title] => Event 1 Title
 >            [url] => http://wwww.yoursite.com/event/event-1-title/ 
 >        )
 >	    [1] => stdClass Object
 >        (
 >            [time] => 2010-09-30 08:00:00
 >            [title] => Event 2 Title
 >            [url] => http://wwww.yoursite.com/subsite/event/event-2-title/ 
 >        )
 >	)
 >
 
 */
function get_the_events( $args = array( ) )
{
	global $wpdb ;
	
	$allowed_fields = array( "id", "time", "title", "text", "url", "site_id", "event_id", "is_active", "is_global" ) ;
	$allowed_orderas = array( 'ASC', 'DESC' ) ;
	$datewhere = "" ;
	$siteswhere = "" ;
	$limitclause = "" ;
	$orderbyclause = "" ;
	
	$defaults = array(
		'events_dated_after' 	=> "",
		'events_dated_before' 	=> "",
		'months' 				=> 1,
		'startmonth' 			=> 'today',
		'limit' 				=> -1,
		'paged' 				=> 1,
		'orderby' 				=> 'time',
		'orderas' 				=> 'ASC',
		'sites' 				=> array( 1 ),
		'fields' 				=> "id, time, title, text, url, site_id, event_id, is_active, is_global" 
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	if( ! is_array( $fields ) )
	{
		$fields = split( "," , $fields ) ;
	}
	
	$len = sizeof( $fields ) ;
	for( $i = 0 ; $i < $len ; $i++ )
	{
		$fields[ $i ] = trim( $fields[ $i ] ) ;
		if( !in_array( $fields[ $i ] , $allowed_fields ) )
		{
			$fields[ $i ] = "" ;	
		}
	}
	$fields = array_filter( $fields ) ;
	
	$query = sprintf("SELECT %s FROM " . ___TABLE . " WHERE is_active=1 " , join( ", " , $fields ) )  ;
	
	if( !empty( $events_dated_before ) && !empty( $events_dated_before ) )
	{
		$datewhere = sprintf( 
			" AND time BETWEEN '%s' AND '%s'  " ,
			date( 'Y-m-d 00:00' , strtotime( $events_dated_after ) ) ,
			date( 'Y-m-d 00:00' , strtotime( $events_dated_before ) ) 
		)  ;
	}
	else
	{
		if( trim( $startmonth ) == 'first' )
		{
			$datestart = date( "Y-m-1 00:00" ) ;
		}
		else
		{
			$datestart = date( "Y-m-d 00:00" ) ;
		}
		
		$months = intval( $months ) ;
		
		$dateend = date( "Y-m-d" , strtotime( date( "Y-m-d", strtotime( $datestart ) ) . " +$months month" ) ) ;
		$datewhere =  sprintf( " AND time BETWEEN '%s' AND '%s' " ,   $datestart , $dateend ) ;
	}
	
	if( ! is_array( $sites ) )
	{
		$sites = split( ",", $sites ) ;
	}
	
	$len = sizeof( $sites ) ;
	for( $i = 0 ; $i < $len ; $i++ )
	{
		$sites[ $i ] = intval( $sites[ $i ] ) ;
	}

	if( 1 == $len && $sites[ 0 ] == 1)
	{
		$siteswhere = " AND is_global=1" ;
	}
	else
	{
		$siteswhere = sprintf( " AND ( is_global=1 OR site_id in ( %s ) )" , join( ", ", $sites ) ) ;
	}
	
	if( in_array( $orderby , $allowed_fields ) )
	{
		$orderbyclause .= " ORDER BY " . $orderby . " " ;	
	}
	else
	{
		$orderbyclause .= "ORDER BY time " ;		
	}
	
	if( in_array( $orderas, $allowed_orderas ) )
	{
		$orderbyclause .= $orderas ;	
	}
	else
	{
		$orderbyclause .= "ASC" ;		
	}
	if( $limit != -1 )
		$limitclause = sprintf( " LIMIT %d, %d" , $limit * ( $paged - 1 ) , $limit ) ;
	
	$query .= $datewhere . $siteswhere . $orderbyclause . $limitclause ;
	$result = $wpdb->get_results($query) ;
	
	return $result ;
}

?>