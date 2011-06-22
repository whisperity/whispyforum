<?php
 /**
 * WhispyForum script file - registration.php
 * 
 * Usage: user registration
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("user/reg_head", FALSE); // Header

// We define the $site variable
$site = "";

if ( $_SESSION['log_bool'] == TRUE )
{
	// If the user is logged in
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_NO_LOGGEDINS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_GUEST}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unavailable error
} elseif ( $_SESSION['log_bool'] == FALSE)
{
// If user is logged out, the registration is accessible


if (!isset($_POST['regPos']))
{
	$regPos = 0;
} else {
	$regPos = $_POST['regPos'];
}

// Now, the regPos variable is either 0 or set from HTTP POST

switch ($regPos)
{
	case NULL:
	case 0:
		// Introduction
		
		$Ctemplate->useStaticTemplate("user/reg_start", FALSE); // Registration splash
		break;
	case 1:
		// User login informations
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("user/reg_userdata", array(
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
				), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("user/reg_userdata", array(
				'USERNAME'	=>	"", // Username
				'PASSWORD'	=>	"", // Password
				'PASSWORD_AGAIN'	=>	"", // Password (entered again)
				'EMAIL'	=>	"" // E-mail address
				), FALSE); // Login information
		}
		break;
	case 2:
		// User registering
		
		// First, we do a check whether every required fields have data
		if ( $_POST['username'] == NULL ) // Username
		{
			$Ctemplate->useTemplate("user/reg_userdata_variable_error", array(
				'VARIABLE'	=>	"{LANG_USERNAME}", // Errornous variable name
				'USERNAME'	=>	$_POST['username'], // Username (should be empty)
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( $_POST['password'] == NULL ) // Password
		{
			$Ctemplate->useTemplate("user/reg_userdata_variable_error", array(
				'VARIABLE'	=>	"{LANG_PASSWORD}", // Errornous variable name
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password (should be empty)
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( $_POST['password_again'] == NULL ) // Password (entered again)
		{
			$Ctemplate->useTemplate("user/reg_userdata_variable_error", array(
				'VARIABLE'	=>	"{LANG_PASSWORD_AGAIN}", // Errornous variable name
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again) (should be empty)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( $_POST['email'] == NULL ) // E-mail address
		{
			$Ctemplate->useTemplate("user/reg_userdata_variable_error", array(
				'VARIABLE'	=>	"{LANG_EMAIL}", // Errornous variable name
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address (should be empty)
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Now, all variables are entered
		
		// We check whether the two entered passwords are identical
		if ( $_POST['password_again'] != $_POST['password'] )
		{
			$Ctemplate->useTemplate("user/reg_userdata_password_match_error", array(
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Check whether the user wants to register a used name
		$nameAllocated = mysql_num_rows($Cmysql->Query("SELECT username FROM users WHERE username='" .$Cmysql->EscapeString($_POST['username']). "'"));
		
		if ( $nameAllocated != 0 )
		{
			// If there's a user with the same name
			$Ctemplate->useTemplate("user/reg_userdata_name_allocated_error", array(
				'USERNAME'	=>	$_POST['username'], // Username (errorneous)
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Check whether the user wants to register a used e-mail address
		$emailAllocated = mysql_num_rows($Cmysql->Query("SELECT email FROM users WHERE email='" .$Cmysql->EscapeString($_POST['email']). "'"));
		
		if ( $emailAllocated != 0 )
		{
			// If there's a user with the same name
			$Ctemplate->useTemplate("user/reg_userdata_email_allocated_error", array(
				'USERNAME'	=>	$_POST['username'], // Username (errorneous)
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE);
			// We terminate the script
			$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Generate an activation token
		$token = generateHexTokenNoDC(); // Use 32 character length generator (without doublecolons (:))
		
		// Everything is fine, we register the user.
		$regQuery = $Cmysql->Query("INSERT INTO users(username, pwd, email, regdate, userLevel, activated, token) VALUES ('" .
			$Cmysql->EscapeString($_POST['username']). "'," .
			"'" .md5($Cmysql->EscapeString($_POST['password'])). "'," .
			"'" .$Cmysql->EscapeString($_POST['email']). "', " .time(). ", 1, 0, '" .$token. "')"); // Will be true if we succeed
		
		if ( $regQuery == FALSE )
		{
			// If there were errors during registration
			$Ctemplate->useTemplate("user/reg_userdata_reg_error", array(
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
			), FALSE); // Give error message and retry form
		} elseif ( $regQuery == TRUE )
		{
			// Mail body (content)
			$email = $Ctemplate->useTemplate("user/reg_activation_email", array(
				'GLOBAL_TITLE'	=>	config("global_title"),
				'SITE_HOST'	=>	"http://" . config("site_host"),
				'ACTIVATION_LINK'	=>	"http://" .config("site_host"). "/activate_user.php?username=" .$_POST['username']. "&token=" .$token,
				'ACTIVATION_SITE'	=>	"http://" .config("site_host"). "/activate_user.php",
				'USERNAME'	=>	$_POST['username'],
				'TOKEN'	=>	$token		
			), TRUE);
			
			// Mail headers
			$headers  = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n";
                        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'Website-domain: ' .config("site_host");
			
			// Send out the mail
			mail($_POST['email'], $wf_lang['{LANG_REG_ACTIVATION_EMAIL_SUBJECT}'], $email, $headers);
			
			// If registration completed successfully
			$Ctemplate->useStaticTemplate("user/reg_userdata_reg_success", FALSE); // Give success
		}
		break;
	case 3:
		// Finish
		$Ctemplate->useStaticTemplate("user/reg_finish", FALSE); // Finish message
		break;
}

}
$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
DoFooter();
?>
