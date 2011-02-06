<?php
 /**
 * WhispyForum script file - control_user.php
 * 
 * User control panel. Usage: help individuals set user-specific properties.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("user/cp_head", FALSE); // Header

// We define the $site variable
$site = "";

if ( $_SESSION['log_bool'] == FALSE )
{
	// If the user is a guest
	$Ctemplate->useStaticTemplate("user/unaviable_guest", FALSE); // We give an unaviable error
} elseif ( $_SESSION['log_bool'] == TRUE)
{
// If user is logged in, the control panel is accessible

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

echo $site;

}
$Ctemplate->useStaticTemplate("user/cp_foot", FALSE); // Footer
DoFooter();
?>