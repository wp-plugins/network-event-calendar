<?php 
/** 
 *	File: admin_css_fix.php
 *	
 *	WordPress Usage: WordPress Event Admin Icon Fix
 * 
 *	Last Edited: 
 *		Jer Brand on 2010/09/16
 */

global $post_type; 
?>
<style>
<?php if ( (isset($_GET['post_type']) && $_GET['post_type'] == 'event') || ($post_type == 'event')) : ?>
#icon-edit { background:transparent url('<?php echo MU_EVENTS_PLUGIN_URL .'/images/mu_events_large_icon.png';?>') no-repeat; }		
<?php endif; ?>
#adminmenu #menu-posts-event div.wp-menu-image{ background:transparent url("<?php echo MU_EVENTS_PLUGIN_URL .'/images/mu_events_small_icon_up.png';?>") no-repeat center center;}
#adminmenu #menu-posts-event:hover div.wp-menu-image,#adminmenu #menu-posts-gallery.wp-has-current-submenu div.wp-menu-image{background:transparent url("<?php echo MU_EVENTS_PLUGIN_URL .'/images/mu_events_small_icon_over.png';?>") no-repeat center center;}	
<?php 
if( stripos($_SERVER['PHP_SELF'], 'widgets.php') !== false) { ?>
.mueventssitelist {border: 1px solid #CCC; height: 200px; overflow: auto; } 
.hiddenList { height:0 !important; overflow: hidden !important; border:none;}
.showHideSiteList { color:#00F;text-decoration:underline;cursor:pointer;cursor:hand; } 
<?php } ?>
</style>
