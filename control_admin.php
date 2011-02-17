<?php
 /**
 * WhispyForum script file - control_admin.php
 * 
 * Admin control panel. Usage: help administrator set the engine's properties.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("admin/admin_head", FALSE); // Header

// We define the $site variable
$site = "";

// Get user's level
$uDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'")); // We query the user's data

if ( $uDBArray['userLevel'] < 3 )
{
	// If the user does not have rights to see the admin panel
	$Ctemplate->useStaticTemplate("admin/insufficient_rights", FALSE); // We give an unaviable error
} elseif ( $uDBArray['userLevel'] >= 3 )
{
// If user has the rights, the panel is accessible

if ( isset($_POST['site']) )
{
	// If site is passed by POST
	// the site variable is the POSTed value
	
	$site = $_POST['site'];
} elseif ( !isset($_POST['site']) )
{
	// If the POSTed variable is NULL
	// we see if there's site variable GET
	
	if ( isset($_GET['site']) )
	{
		// If there is, site is the GET value
		$site = $_GET['site'];
	} elseif ( !isset($_GET['site']) )
	{
		// If not, site is NULL
		$site = NULL;
	}
}

// Now, the site variable is either NULL or set from HTTP POST/GET

switch ($site)
{
	case "menus":
		// Menu and menu item editor
		
		break;
}
}
$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
DoFooter();
?>