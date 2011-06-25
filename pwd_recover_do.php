<?php
 /**
 * WhispyForum script file - password recovery (second step step)
 * 
 * These two pages helps the user recovering their password
 *  * pwd_recover_begin.php - asks for username, generates token, sends out email
 *  * pwd_recover_do.php    - checks token and username, does password modification
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

if ( ( @$_POST['username'] == NULL ) || ( $_POST['token'] == NULL ) || ( @$_POST['step'] == NULL ) || ( $_POST['step'] == "start" ) )
{
	// If there is no username or token present, or the current step is the begin step (getting new password)
	// output form
	
	if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
	{
		// We output the form with data returned (user doesn't have to enter it again)
		$Ctemplate->useTemplate("user/recover_do", array(
			'USERNAME'	=>	@$_POST['username'],
			'TOKEN'	=>	@$_POST['token']
		), FALSE);
	} else {
		// We output general form
		$Ctemplate->useTemplate("user/recover_do", array(
			'USERNAME'	=>	"",
			'TOKEN'	=>	""
		), FALSE);
	}
} elseif ( ( @$_POST['username'] != NULL ) && ( $_POST['token'] != NULL ) && ( $_POST['step'] == "do" ) )
{
	// If there is a username and token present, and the step is the working part
	// Do restore
	
	// Check for the username and token
	$user = mysql_fetch_assoc($Cmysql->Query("SELECT email, activated FROM users WHERE username='" .$Cmysql->EscapeString($_POST['username']). "'"));
	
	if ( $user == FALSE )
	{
		// If the user is not present, output error message
		$Ctemplate->useTemplate("user/recover_do_user_error", array(
			'USERNAME'	=>	$_POST['username'],
			'TOKEN'	=>	$_POST['token']
		), FALSE);
		
		// Terminate execution
		DoFooter();
		exit;
	}
	
	// Then check it is already activated
	if ( $user['activated'] == '0' )
	{
		// If the user is inactivated, output error message
		$Ctemplate->useTemplate("user/recover_do_activate_error", array(
			'USERNAME'	=>	$_POST['username'],
			'TOKEN'	=>	$_POST['token']
		), FALSE);
		
		// Terminate execution
		DoFooter();
		exit;
	}
	
	// First, check whether password is present
	if ( ( @$_POST['pass1'] == NULL ) || ( @$_POST['pass2'] == NULL ) )
	{
		// If any of the password fields is empty, output error and return form
		$Ctemplate->useTemplate("user/recover_do_nopass_error", array(
			'USERNAME'	=>	$_POST['username'],
			'TOKEN'	=>	$_POST['token']
		), FALSE);
		
		// Terminate execution
		DoFooter();
		exit;
	}
	
	// Then check whether the passwords are identical
	if ( $_POST['pass1'] != $_POST['pass2'] )
	{
		// If the two passwords are NOT identical, output error message
		$Ctemplate->useTemplate("user/recover_do_passmatch_error", array(
			'USERNAME'	=>	$_POST['username'],
			'TOKEN'	=>	$_POST['token']
		), FALSE);
		
		// Terminate execution
		DoFooter();
		exit;
	}
	
	// If both is true, and the passwords are okay, do procedure
	$setNewPass = $Cmysql->Query("UPDATE users SET pwd='" .md5($Cmysql->EscapeString($_POST['pass1'])). "', token=NULL WHERE username='" .$Cmysql->EscapeString($_POST['username']). "'"); // Set the new password in database, TRUE if it was successful
	
	if ( $setNewPass == FALSE )
	{
		// If we failed setting the password in database, output an error message
		$Ctemplate->useStaticTemplate("user/recover_do_tokensql_error", FALSE);
	} elseif ( $setNewPass == TRUE )
	{
		// If we set the password
		$Ctemplate->useStaticTemplate("user/recover_do_success", FALSE); // Send success message
	}
}

DoFooter();
?>
