<?php
/**
 * WhispyForum script file - logout page
 * 
 * Page using the user class to do logouts
 * 
 * WhispyForum
 */

include("includes/safeload.php"); // We load the environment without framework

if ( $_POST == NULL )
{
	// If POST array is null (direct opening the page)
	// we give error
	$Ctemplate->useStaticTemplate("user/logout_err_nopost", FALSE);
	
	exit; // We terminate the script
}

if ( $_POST['returnto'] == NULL )
{
	// We can have errors with unentered variables
	// if the script redirects the user to the login page
	// without giving any POST causing annoying and unnecessary redirections
	$returnURI = "index.php"; // Return URI is the homepage
} else {
	$returnURI = $_POST['returnto']; // Return URI is the original return page
}

if ( $_POST['logout'] == "do_user_logout" )
{
	// If the proper POST value was given, we logout the user
	$logsuccess = $Cusers->Logout($_SESSION['username']); // We call the logout function.
	
	// $logsuccess is TRUE if the user successfully logged out
	// $logsuccess is FALSE if there were errors during logout
	
	if ( $logsuccess == FALSE )
	{
		// We output an error message
		$Ctemplate->useTemplate("user/logout_error", array(
			"RETURN_TO_URL"	=>	$returnURI, // Return URI
		), FALSE);
	} elseif ( $logsuccess == TRUE )
	{
		// We give success
		$Ctemplate->useStaticTemplate("user/logout_success", FALSE);
	}
}

DoFooter();
?>
