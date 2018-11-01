<?php
 /**
 * WhispyForum script file - login page
 * 
 * Page using the user class to do logins
 * 
 * WhispyForum
 */

include("includes/safeload.php"); // We load the environment without framework

if ( $_POST == NULL )
{
	// If POST array is null (direct opening the page)
	// we give error
	$Ctemplate->useStaticTemplate("user/login_err_nopost", FALSE);
	
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

if ( $_POST['user_loginname'] == NULL )
{
	// If user wants to login without username
	// give error message
	
	$Ctemplate->useTemplate("user/login_err_novalue", array(
		'RETURN_TO_URL'	=>	$returnURI, // Return URI
		'VARIABLE_HEADER'	=>	"{LANG_USERNAME}", // Unentered variable (uppercase)
		'VARIABLE_BODY'	=>	"{LANG_USERNAME_LOWERCASE}" // Unentered variable (lowercase)
	), FALSE);
	
	exit; // We terminate the script
}

if ( $_POST['user_password'] == NULL )
{
	// If user wants to login without entering password
	// give error message
	
	$Ctemplate->useTemplate("user/login_err_novalue", array(
		'RETURN_TO_URL'	=>	$returnURI, // Return URI
		'VARIABLE_HEADER'	=>	"{LANG_PASSWORD}", // Unentered variable (uppercase)
		'VARIABLE_BODY'	=>	"{LANG_PASSWORD_LOWERCASE}" // Unentered variable (lowercase)
	), FALSE);
	
	exit; // We terminate the script
}

if ( $_POST['user_class'] == NULL )
{
	// If user wants to login without class
	// give error message
	
	$Ctemplate->useTemplate("user/login_err_novalue", array(
		'RETURN_TO_URL'	=>	$returnURI, // Return URI
		'VARIABLE_HEADER'	=>	"{LANG_CLASS}", // Unentered variable (uppercase)
		'VARIABLE_BODY'	=>	"{LANG_CLASS}" // Unentered variable (lowercase)
	), FALSE);
	
	exit; // We terminate the script
}

$logsuccess = $Cusers->Login($_POST['user_loginname'], $_POST['user_password'], fClassFix($_POST['user_class'])); // We call the login function.

// $logsuccess is TRUE if the user successfully logged in
// $logsuccess is FALSE if there were errors during login

if ( $logsuccess === FALSE )
{
	// We output an error message
	$Ctemplate->useTemplate("user/login_error", array(
		'RETURN_TO_URL'	=>	$returnURI // Return URI
	), FALSE);
} elseif ( $logsuccess === "activate" )
{
	// If the user is not activated
	$Ctemplate->useTemplate("user/login_error_activate", array(
		'RETURN_TO_URL'	=>	$returnURI // Return URI
	), FALSE); // Output specific error message
} elseif ( $logsuccess === TRUE )
{
	// We give success
	$Ctemplate->useTemplate("user/login_success", array(
		'RETURN_TO_URL'	=>	$returnURI // Return URI
	), FALSE);
}

DoFooter();
?>
