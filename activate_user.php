<?php
 /**
 * WhispyForum script file - user activation page
 * 
 * This page helps new users activating their accounts
 * 
 * WhispyForum
 */

include("includes/safeload.php"); // We load the environment without framework

// Logged in users cannot access this page
if ( $_SESSION['log_bool'] == TRUE )
{
	// If the user is logged in
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_NO_LOGGEDINS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_GUEST}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unavailable error
	
	// Terminate execution
	DoFooter();
	exit;
}

// This script does the activation of fresh users
// It needs to have two input from HTTP GET

if ( ( @$_GET['username'] == NULL ) || ( @$_GET['token'] == NULL ) )
{
	// If either the username or the token is missing (it includes the BOTH sceniaro)
	// Output form, and fill it in with the known data
	$Ctemplate->useTemplate("user/activate_form", array(
		'USERNAME'	=>	@$_GET['username'],
		'TOKEN'	=>	@$_GET['token']
	), FALSE);
} elseif ( ( @$_GET['username'] != NULL ) && ( @$_GET['token'] != NULL ) )
{
	// If we have all the variables
	// Do the activation
	
	// First, select the user from database
	$check = mysql_fetch_assoc($Cmysql->Query("SELECT id, token, activated FROM users WHERE username='" .$Cmysql->EscapeString($_GET['username']). "'")); // This will be FALSE if the username is unknown or the token is invalid, and TRUE if we're successful to find the user
	
	if ( $check === FALSE )
	{
		// If the user does not exists
		// Output an error message
		$Ctemplate->useStaticTemplate("user/activate_error", FALSE);
	} elseif ( $check == TRUE )
	{
		// If the user exists
		
		// Check for activation
		if ( $check['activated'] === "1" )
		{
			// If the user is already activated, output an error message
			$Ctemplate->useStaticTemplate("user/activate_already_error", FALSE);
		} elseif ( $check['activated'] === "0" )
		{
			// If the user has not been activated yet and the token is okay
			// Do activation
			if ( $_GET['token'] === $check['token'] )
			{
				$activate = $Cmysql->Query("UPDATE users SET activated='1', token=NULL WHERE id='" .$Cmysql->EscapeString($check['id']). "'");		
				
				if ( $activate == FALSE )
				{
					// If we failed activating, output an error message
					$Ctemplate->useStaticTemplate("user/activate_error", FALSE);
				} elseif ( $activate == TRUE )
				{
					// If we succeeded, output success message (forwarding the user to the homepage)
					$Ctemplate->useStaticTemplate("user/activate_success", FALSE);
				}
			} else {
				// If the token is not valid, output error
				$Ctemplate->useStaticTemplate("user/activate_error", FALSE);
			}
		}
	}
}

DoFooter();
?>
