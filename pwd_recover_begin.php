<?php
 /**
 * WhispyForum script file - password recovery (first step)
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

if ( @$_POST['username'] == NULL )
{
	// If there is no username present, output the form
	
	if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
	{
		// We output the form with data returned (user doesn't have to enter it again)
		$Ctemplate->useTemplate("user/recover_begin", array(
			'USERNAME'	=>	@$_POST['username']
		), FALSE);
	} else {
		// We output general form
		$Ctemplate->useTemplate("user/recover_begin", array(
			'USERNAME'	=>	""
		), FALSE);
	}
} elseif ( @$_POST['username'] != NULL )
{
	// If there is a username present, send message
	
	// First, check whether the username is registered
	$user = mysql_fetch_assoc($Cmysql->Query("SELECT email, activated FROM users WHERE username='" .$Cmysql->EscapeString($_POST['username']). "'"));
	
	if ( $user == FALSE )
	{
		// If the user is not present, output error message
		$Ctemplate->useStaticTemplate("user/recover_begin_user_error", FALSE);
		
		// Terminate execution
		DoFooter();
		exit;
	}
	
	// Then check it is already activated
	if ( $user['activated'] == '0' )
	{
		// If the user is inactivated, output error message
		$Ctemplate->useStaticTemplate("user/recover_begin_activate_error", FALSE);
		
		// Terminate execution
		DoFooter();
		exit;
	}
	
	// If both is true, do procedure
	$token = generateHexTokenNoDC(); // Generate a token
	$setToken = $Cmysql->Query("UPDATE users SET token='" .$token. "' WHERE username='" .$Cmysql->EscapeString($_POST['username']). "'"); // Set the token in database, TRUE if it was successful
	
	if ( $setToken == FALSE )
	{
		// If we failed setting the token in database, output an error message
		$Ctemplate->useStaticTemplate("user/recover_begin_tokensql_error", FALSE);
	} elseif ( $setToken == TRUE )
	{
		// If we set the token
		$Ctemplate->useStaticTemplate("user/recover_begin_success", FALSE); // Send success message
		
		// Mail body (content)
		$variables = array(
			'RECOVERY_SITE'	=>	"http://" .config("site_host"). "/pwd_recover_do.php",
			'USERNAME'	=>	$_POST['username'],
			'TOKEN'	=>	$token
		);
		
		// Send out the mail
		sendTemplateMail($user['email'], $wf_lang['{LANG_PWDRECOVER_EMAIL_SUBJECT}'], "user/recover_email", $variables);
	}
}

DoFooter();
?>
