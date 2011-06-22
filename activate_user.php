<?php
 /**
 * WhispyForum script file - user activation page
 * 
 * This page helps new users activating their accounts
 * 
 * WhispyForum
 */

include("includes/safeload.php"); // We load the environment without framework

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
	$check = mysql_fetch_assoc($Cmysql->Query("SELECT id FROM users WHERE username='" .$Cmysql->EscapeString($_GET['username']). "' AND token='" .$Cmysql->EscapeString($_GET['token']). "'")); // This will be FALSE if the username is unknown or the token is invalid, and TRUE if we're successful to find the user
	
	if ( $check == FALSE )
	{
		// If the user does not exists
		// Output an error message
		$Ctemplate->useStaticTemplate("user/activate_error", FALSE);
	} elseif ( $check == TRUE )
	{
		// If the user exists
		// Do activation
		
		$activate = $Cmysql->Query("UPDATE users SET activated='1', token=NULL WHERE id='" .$Cmysql->EscapeString($check['id']). "'");		
		
		if ( $activate == FALSE )
		{
			// If we failed activating, output an error message
			$Ctemplate->useTemplate("user/activate_error", array(
				'USERNAME'	=>	$_GET['username'],
				'TOKEN'	=>	$_GET['token']
			), FALSE);
		} elseif ( $activate == TRUE )
		{
			// If we succeeded, output success message (forwarding the user to the homepage)
			$Ctemplate->useStaticTemplate("user/activate_success", FALSE);
		}
	}
}

DoFooter();
?>
