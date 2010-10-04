<?php
/** 
 *	File: muEvent_editorDialog.php
 *
 *	WordPress Usage: TinyMCE Event List Editor
 *
 *	This file contains the code necessary to render and use the List Editor Dialog in TinyMCE.
 * 
 *	Last Edited: 
 *
 *		Jer Brand on 2010/09/29
 */
 
// There's gotta be a better way to do this, but it works for now.
require_once( dirname( __FILE__ ) . '../../../../../../wp-admin/admin.php' ) ;

if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
{
	_e( '<p>You do not have sufficient rights to perform this action.</p></body></html>' , MU_EVENTS_PLUGIN_ID )   ;
	exit ;
}

global $months_list ;

$placeholder_url = dirname( $_SERVER['PHP_SELF'] ) . "/../../images/" . __( "mu_events_mce_placeholder_icon.png" , MU_EVENTS_PLUGIN_ID ) ;

//	values used to provide options and validate user entry.
$allowed_sites 			= array( ) ;
$sites_array 			= array( ) ;
$allowed_fields 		= array( "time", "title", "site_id", "is_global" ) ;
$human_readable_fields 	= array( 
									__( "Time/Date of Event" , MU_EVENTS_PLUGIN_ID ) , 
									__( "Event Title" , MU_EVENTS_PLUGIN_ID ) , 
									__( "Group by Site" , MU_EVENTS_PLUGIN_ID ) , 
									__( "Group by Global/Not Global" , MU_EVENTS_PLUGIN_ID ) 
						  ) ;
$allowed_orderas 		= array( 'ASC', 'DESC' ) ;
$human_readable_orderas = array( __( 'Ascending Order' , MU_EVENTS_PLUGIN_ID ) , __( 'Descending Order' , MU_EVENTS_PLUGIN_ID ) ) ;

// Don't freak out, we're just asking for a list of id's and paths for all blogs. 
// Much less dangerous than "get_blog_list" as there's no join here.
$__sites = $wpdb->get_results( 
	$wpdb->prepare( 
		"SELECT blog_id, path 
		 FROM $wpdb->blogs 
		 WHERE public = '1' 
		 AND archived = '0' 
		 AND spam = '0' 
		 AND deleted = '0' 
		 ORDER BY blog_id DESC" ) );
foreach($__sites as $s)
{
	$allowed_sites[$s->blog_id] = $s ;
}

// Here's the default values for a new Event Listing
$sites 					= "" ;
$events_dated_after 	= "";
$events_dated_before 	= "";
$limit 					= -1 ;
$months 				= 1 ;
$startmonth 			= "today" ;
$orderby 				= "time" ;
$orderas 				= "ASC" ;


$edit 					= false ; // True if we're editing an existing event list.

// The GET Params.  
$params = isset($_GET[ 'params' ] ) ? stripslashes($_GET[ 'params' ]) : NULL ;

if( strlen( trim( $params ) ) > 0 )
{
	$edit = true ;
	$temp = NULL ;

	//////////////////////////////////////////////////////////////////////////// sites
	preg_match( '/sites\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		$temp = $temp[1] ;
		if( false == empty( $temp ) && false == is_null( $temp ) )
		{
			$temp = split( ",", $temp ) ;
			foreach( $temp as $key => $value) 
			{
				$temp[ $key ] = "'" . intval( $value ) . "'" ;	
				$sites_array[ $key ] = intval( $value ) ;
			}
			$sites = join( "," , $temp ) ;
			
		}
	}
	
	//////////////////////////////////////////////////////////////////////////// events_dated_before
	$temp = NULL ; 
	preg_match( '/events_dated_before\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		$temp = $temp[ 1 ] ;
		if( ! empty( $temp ) ) 
		{
			$events_dated_before = date( "Y-m-d 00:00", strtotime( $temp ) ) ;	
		}
	}
	
	//////////////////////////////////////////////////////////////////////////// events_dated_after
	$temp = NULL ; 
	preg_match( '/events_dated_after\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		$temp = $temp[ 1 ] ;
		if( ! empty( $temp ) ) 
		{
			$events_dated_after = date( "Y-m-d 00:00", strtotime( $temp ) ) ;	
		}
	}
	
	//////////////////////////////////////////////////////////////////////////// limit
	$temp = NULL ; 
	preg_match( '/limit\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		$limit = intval( $temp[ 1 ] ) ;	
	}
	
	//////////////////////////////////////////////////////////////////////////// months
	$temp = NULL ; 
	preg_match( '/months\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		$months = intval( $temp[ 1 ] ) ;	
	}
	
	//////////////////////////////////////////////////////////////////////////// startmonth
	$temp = NULL; // and when we're done with $temp
	preg_match( '/startmonth\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		if( trim( $temp[ 1 ] ) == "first" )
		{
			$startmonth = "first" ;
		}
		else
		{
			$startmonth = "today" ;	
		}
	}

	//////////////////////////////////////////////////////////////////////////// orderby
	$temp = NULL ; 
	preg_match( '/orderby\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		if( in_array( trim( $temp[ 1 ] ), $allowed_fields ) )
		{
			$orderby = trim( $temp[ 1 ] ) ;	
		}
	}
	
	//////////////////////////////////////////////////////////////////////////// orderas
	$temp = NULL ; 
	preg_match( '/orderas\s*=\s*[\"\'](.+?)[\"\']/is' , $params , $temp ) ;
	if( sizeof( $temp ) > 0 )
	{
		if( in_array( strtoupper( trim( $temp[ 1 ] ) ) , $allowed_orderas ) )
		{
			$orderas = trim( $temp[ 1 ] ) ;	
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php if( $edit ) { _e("Edit Events Listing") ; } else { _e("Insert Events Listing") ; } ?></title>
<link rel="stylesheet" href="<?php echo get_option( 'siteurl' ) ?>/wp-admin/load-styles.php?c=1&amp;dir=ltr&amp;load=global,wp-admin&amp;ver=aba7495e395713976b6073d5d07d3b17" type="text/css" media="all">
<!--[if lte IE 7]>
	<link rel='stylesheet' id='ie-css'  href='<?php echo get_option( 'siteurl' ) ?>/wp-admin/css/ie.css?ver=20100610' type='text/css' media='all' />
<![endif]-->
<link rel="stylesheet" href="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/themes/advanced/skins/wp_theme/ui.css?ver=327-1235">
<link rel="stylesheet" href="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/plugins/inlinepopups/skins/clearlooks2/window.css?ver=327-1235">
<link rel="stylesheet" href="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/themes/advanced/skins/wp_theme/dialog.css?ver=327-1235" />
<link rel="stylesheet" href="<?php echo dirname( $_SERVER['PHP_SELF'] ) . "/muEvent_editor_styles.css" ; ?>" />
<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) ?>/wp-admin/load-scripts.php?c=1&amp;load=jquery"></script>
<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/tiny_mce_popup.js?ver=3211"></script>
<? include( "js_strings.php" ) ; ?>
<script type="text/javascript" src="<?php echo dirname( $_SERVER['PHP_SELF'] ) . "/muEvent_editor_script.js" ; ?>"></script>
</head>
<body scroll="no" style="overflow:hidden">
<form onsubmit="insertShortTag(); return false;" action="#">
	<div class="tabs">
		<ul>
			<li id="general_tab" class="current">
				<span>
					<a href="javascript:;" onmousedown="return false;">
						<?php _e( 'General', MU_EVENTS_PLUGIN_ID ) ; ?>
					</a>
				</span>
			</li>
			<li id="sites_tab">
				<span>
					<a  href="javascript:;"  onmousedown="return false;">
						<?php _e( 'Sites', MU_EVENTS_PLUGIN_ID ) ; ?>
					</a>
				</span>
			</li>
		</ul>
	</div>
	<div class="panel_wrapper">
		<div id="general_panel" class="panel current">
			<fieldset>
				<legend>
					<?php _e( "Event Dates", MU_EVENTS_PLUGIN_ID ) ;  ?>
				</legend>
			<?php
				$mchecked = "" ;
				$rchecked = "" ;
				if( empty( $events_dated_after ) )
				{
					$mchecked = 'checked="checked"' ;
				}
				else
				{
					$rchecked = 'checked="checked"' ;
				}
			?>
				<div class="datecontainer">
					<input <?php echo $mchecked ; ?> type="radio" id="useMonthsM" name="useMonths" value="1" />
					
					<label for="useMonthsM">
					<?php 
					echo sprintf( __("The event listing should show %s months",  MU_EVENTS_PLUGIN_ID ), '<input type="text" name="months" id="months" size="2" min="0" max="12" step="1" value="' . $months . '">' ) ; 
					?>
					</label><br />
					<label for="startmonth" class="startmonthLabel">
						<?php _e("Begin showing events from" ,  MU_EVENTS_PLUGIN_ID ) ; ?> </label>
					<select id="startmonth" name="startmonth">
						<?php
							echo  sprintf( '<option value="today" %s>%s</option>' , ( $startmonth == "today" ? 'selected="selected"' : "" ) , __("the date viewed" , MU_EVENTS_PLUGIN_ID ) ) ;
							echo  sprintf( '<option value="first" %s>%s</option>' , ( $startmonth == "first" ? 'selected="selected"' : "" ) , __("the first of the month" , MU_EVENTS_PLUGIN_ID ) ) ;
						?>
					</select>.
				</div>
				
				<div class="datecontainer">
					<input <?php echo $rchecked ; ?> type="radio" id="useMonthsR" name="useMonths" value="0" />
					<label for="useMonthsR">
						<?php _e( "The event listing should show values for the following dates:", MU_EVENTS_PLUGIN_ID ) ;  ?>
					</label>
					
					<div class="timestamp-wrap">
					<label for="afterMonth" class="month">
						<?php _e( "Events After: ", MU_EVENTS_PLUGIN_ID ) ;  ?>
					</label>
					<?php 
						// In order to allow this to be fully internationalized, 
						// I need to allow translators to set the order and seperation characters
						// for the input elements. I'm going to dump each element into a variable and
						// output that variable into a sprintf string, and allow the translators to edit
						// the sprintf string, rather than the individual entries.
						
						$afterMonthInput = '<select id="afterMonth" name="afterMonth">' ;
						$len = 13; // Because the number of months in a year is unlikely to change....
						$sel = !empty($events_dated_after) ? intval( date( "m", strtotime( $events_dated_after ) ) ) : 0;
						for($i = 0; $i < $len ; $i++)
						{
							$selected = ($sel == $i) ? 'selected="selected"' : "" ;
							$afterMonthInput .= sprintf('<option value="%d" %s>%s</option>', $i , $selected, $months_list[ $i ] ) ;
						}
						$afterMonthInput .= '</select>' ;
					
						$afterDayInput = sprintf( '<input type="text" min="1" max="31" id="afterDay" name="afterDay" value="%s" size="2" maxlength="2" autocomplete="off">', !empty( $events_dated_after ) ? date( "d", strtotime( $events_dated_after ) ) : "" ) ;
						$afterYearInput = sprintf( '<input type="text" id="afterYear" name="afterYear" value="%s" size="4" maxlength="4" autocomplete="off">', !empty($events_dated_after) ? date("Y", strtotime($events_dated_after)): "" ) ;
						
						/* translators: String is currently English "month day, year" and is used 
						   to set the order of the 'after' input text and dropdown elements, where 
						   %1$s == Month, %2$s == Day, and %3$s == Year. */
						echo sprintf( __('%1$s %2$s, %3$s' , MU_EVENTS_PLUGIN_ID), $afterMonthInput, $afterDayInput , $afterYearInput ) ;
	
					?>
				</div>
					
					<div class="timestamp-wrap">
					<label for="beforeMonth" class="month">
						<?php _e( "But before: ", MU_EVENTS_PLUGIN_ID ) ;  ?>
					</label>
					<?php 
						$beforeMonthInput = '<select id="beforeMonth" name="beforeMonth">' ;
						$len = 13; // Yup, still 12 months in a year.
						$sel = !empty($events_dated_before) ? intval( date( "m", strtotime( $events_dated_before ) ) ) : 0;
						for($i = 0; $i < $len ; $i++)
						{
							$selected = ($sel == $i) ? 'selected="selected"' : "" ;
							$beforeMonthInput .= sprintf('<option value="%d" %s>%s</option>', $i , $selected, $months_list[ $i ] ) ;
						}
						$beforeMonthInput .= '</select>' ;
						
						$beforeDayInput = sprintf( '<input type="text" min="1" max="31" id="beforeDay" name="beforeDay" value="%s" size="2" maxlength="2" autocomplete="off">', !empty($events_dated_before) ? date("d", strtotime($events_dated_before)): "" ) ;
						$beforeYearInput = sprintf( '<input type="text" id="beforeYear" name="beforeYear" value="%s" size="4" maxlength="4" autocomplete="off">', !empty($events_dated_before) ? date("Y", strtotime($events_dated_before)): "" );
						
						/* translators: String is currently English "month day, year" and is used 
						   to set the order of the 'before' input text and dropdown elements, where 
						   %1$s == Month, %2$s == Day, and %3$s == Year. */
						echo sprintf( __('%1$s %2$s, %3$s' , MU_EVENTS_PLUGIN_ID), $beforeMonthInput, $beforeDayInput , $beforeYearInput ) ;
						?>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<legend>
					<?php _e( "Event Number", MU_EVENTS_PLUGIN_ID ) ;  ?>
				</legend>
				<div class="datecontainer">
				<input type="checkbox" id="limitresults" name="limitresults" value="1" <?php if( $limit > 0 ) { echo 'checked="checked"' ; } ?>/>
				<label for="limitresults">
					<?php _e( "I want to limit the listing to show only ", MU_EVENTS_PLUGIN_ID ) ;  ?>
				</label>
				<input type="text" name="limit" id="limit" size="3" min="1" max="100" step="1" value="<?php if($limit > 0) { echo $limit ; } ?>">
				<label for="limit" title="<?php _e( "Event Listings do not have pages. If you limit this to 10 events, that is all that will ever show up.", MU_EVENTS_PLUGIN_ID ) ;  ?>"> events <span class="betterlookhere">*</span></label>
				</div>
			</fieldset>
			<fieldset>
				<legend>
					<?php _e( "Event Order", MU_EVENTS_PLUGIN_ID ) ;  ?>
				</legend>
				<div class="datecontainer">
				<div class="order-wrap">
				<label for="orderby">
					<?php _e( "Order the events by ", MU_EVENTS_PLUGIN_ID ) ;  ?>
				</label>
				<select id="orderby" name="orderby">
					<?php
						$len = sizeof( $allowed_fields ) ;
						for($i = 0; $i < $len ; $i++)
						{
							$selected = ($orderby == $allowed_fields[ $i ] ) ? 'selected="selected"' : "" ;
							echo sprintf('<option value="%s" %s>%s</option>', $allowed_fields[ $i ] , $selected, $human_readable_fields[ $i ] ) ;
						}
					?>
				</select>.
				</div>
				<div class="order-wrap">
				<label for="orderas">
					<?php _e( "Put the events in ", MU_EVENTS_PLUGIN_ID ) ;  ?>
				</label>
				<select id="orderas" name="orderas">
					<?php
						$len = sizeof( $allowed_orderas ) ;
						for($i = 0; $i < $len ; $i++)
						{
							$selected = ($orderas == $allowed_orderas[ $i ] ) ? 'selected="selected"' : "" ;
							echo sprintf('<option value="%s" %s>%s</option>', $allowed_orderas[ $i ] , $selected, $human_readable_orderas[ $i ] ) ;
						}
					?>
				</select>
				</div>
				</div>
			</fieldset>
		</div>
		<div id="sites_panel" class="panel">
			<table  border="0" cellpadding="4" cellspacing="0" width="100%" height="100%">
				<tr>
					<th style="width:45%; height:24px;">
						<?php _e( "Available Sites", MU_EVENTS_PLUGIN_ID ) ;  ?>
					</th>
					<td></td>
					<th  style="width:45%; height:24px;">
						<?php _e( "Showing Events From", MU_EVENTS_PLUGIN_ID ) ;  ?>
						</th>
				</tr>
				<tr>
					<td>
						<select style="width:100%;height:100%;overflow:auto;" multiple="multiple" id="availableSites">
						<?php
							foreach($allowed_sites as $site)
							{
								$path = $site->path  ;
								$path = ( "/" == $path ) ? "Main Site ( / )" : $path ;
								echo sprintf( '<option value="%d">%s</option>' , $site->blog_id , $path ) ;	 ;
							}
						?>
						</select>
					</td>
					<td valign="middle" align="center">
						<input type="button" id="addSite" value=">>" title="<?php _e( "Add Site", MU_EVENTS_PLUGIN_ID ) ;  ?>" />
						<br /><br />
						<input type="button" id="removeSite" value="<<" title="<?php _e( "Remove Site", MU_EVENTS_PLUGIN_ID ) ;  ?>"/>
					</td>
					<td>
						<select style="width:100%;height:100%;overflow:auto;" multiple="multiple" id="sites">
						<?php
							
							foreach($sites_array as $s)
							{
								//print( $s . " " . intval( $s ) ) ;
								if( isset( $allowed_sites[ intval( $s ) ] ) ) 
								{
									$path = $allowed_sites[ intval( $s ) ]->path ;
									$path = ( "/" == $path ) ? "Main Site ( / )" : $path ;
									echo sprintf( '<option value="%d">%s</option>' , $allowed_sites[ intval( $s ) ]->blog_id , $path ) ;	 ;
								}
							}
						?>
						</select>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="mceActionPanel">
		<div style="float: left"><input type="button" id="cancel" name="cancel" value="<?php _e( "Cancel", MU_EVENTS_PLUGIN_ID ) ;  ?>" class="mceClose" onclick="tinyMCEPopup.close();"></div>
		<div style="float: right"><input type="submit" id="insert" name="insert" value="<?php _e( "Insert", MU_EVENTS_PLUGIN_ID ) ;  ?>"></div>
	</div>
</form>
</body>
</html>