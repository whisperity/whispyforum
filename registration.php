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
	$Ctemplate->useStaticTemplate("user/unaviable_loggedin", FALSE); // We give an unaviable error
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
				'VARIABLE'	=>	"Username", // Errornous variable name
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
				'VARIABLE'	=>	"Password", // Errornous variable name
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
				'VARIABLE'	=>	"Password (again)", // Errornous variable name
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
				'VARIABLE'	=>	"E-mail address", // Errornous variable name
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
		
		$regQuery = $Cmysql->Query("INSERT INTO users(");
		// Everything is fine, we register the user.
		/*$Ctemplate->useStaticTemplate("user/reg_userdata_reg_success", FALSE);
		$Ctemplate->useTemplate("user/reg_userdata_reg_error", array(
				'USERNAME'	=>	$_POST['username'], // Username
				'PASSWORD'	=>	$_POST['password'], // Password
				'PASSWORD_AGAIN'	=>	$_POST['password_again'], // Password (entered again)
				'EMAIL'	=>	$_POST['email'] // E-mail address
				), FALSE);*/
		break;
	case 3:
		// Finish
		
		break;
}

}
$Ctemplate->useStaticTemplate("user/reg_foot", FALSE); // Footer
DoFooter();
?>