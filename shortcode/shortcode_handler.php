<?php
/** 
 *	File: shortcode_handler.php
 *
 *	WordPress Usage: Shortcode Handler
 * 
 *	Last Edited: 
 *	
 *		Jer Brand on 2010/11/22
 */

/*
 *	Function: mu_events_shortcode_handler
 *
 *	Handles insertion of the events listing utilizing the standard Multi-Site Event API.
 *
 *	See Also:
 *
 *		<get_the_events>
 *
 *	Uses Hook:
 *
 *		add_shortcode ( method )
 *
 */
function mu_events_shortcode_handler( $atts )
{
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
		'fields' 				=> "time, title, text, url, site_id, is_active, is_global" 
	);
	
	$args = shortcode_atts(	$defaults ,	$atts ) ;
	$return_value = the_events( $args , true , false ) ;
	return $return_value  ;
}
?>