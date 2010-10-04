<?php
/** 
 *	File: js_strings.php
 *
 *	WordPress Usage: Renders the strings used in the TinyMCE Event Editor Dialog as a self-contained script blog so that
 *	they can be internationalized.
 * 
 *	Last Edited: 
 *
 *		Jer Brand on 2010/09/29
 */

$crlf = "\n" ;
echo '<script type="text/javascript">' . $crlf . $crlf  ;
echo sprintf( 'var __placeholderURL = "%s" ;', $placeholder_url )  . $crlf;
echo sprintf( 'var __monthError = "%s \\n" ; ', __( " - 'Months' must contain a value between 1 and 12", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __afterMonthError = "%s \\n" ;' , __( " - You must select a value for the 'after' month.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __afterDayError = "%s \\n" ;' , __( " - You must enter an 'after' day between 1 and 31.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __afterYearError = "%s \\n" ;' , __( " - You must enter a 4-digit 'after' year.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __beforeMonthError = "%s \\n" ;' , __( " - You must select a value for the 'before' month.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __beforeDayError = "%s \\n" ;' , __( " - You must enter a 'before' day between 1 and 31.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __beforeYearError = "%s \\n" ;' , __( " - You must enter a 4-digit 'before' year.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __limitError = "%s \\n" ;' , __( " - If you desire a limit, you must enter a number between 1 and 100.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __sitesError1 = "%s \\n" ;' , __( " - You must select at least one site to view events from.", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo sprintf( 'var __baseMessage = "%s \\n\\n" ;' , __( "Please correct the following errors:", MU_EVENTS_PLUGIN_ID ) ) . $crlf ;
echo '</script>' . $crlf ;
?>