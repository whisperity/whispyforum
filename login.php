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
	echo "no post";
	exit; // We terminate the script
}

if ( $_POST['user_loginname'] == NULL )
{
	// If user wants to login without username
	// give error message
	echo "no usrname";
	exit; // We terminate the script
}

if ( $_POST['user_password'] == NULL )
{
	// If user wants to login without entering password
	// give error message
	echo "no usrpwd";
	exit; // We terminate the script
}

$logsuccess = $Cusers->Login($_POST['user_loginname'], $_POST['user_password']); // We call the login function.

// $logsuccess is TRUE if the user successfully logged in
// $logsuccess is FALSE if there were errors during login

if ( $logsuccess == FALSE )
{
	// We output an error message
	echo "ERROR";
} elseif ( $logsuccess == TRUE )
{
	// We give success
	echo "SUCCESS!";
}

DoFooter();
?>
