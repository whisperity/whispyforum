<?php
/**
 * WhispyForum
 * 
 * /language/english/core.php
*/

/**
 * *********************************************************
 * * IMPORTANT! PLEASE READ BEFORE TRANSLATING! IMPORTANT! *
 * *********************************************************
 * 
 * Please do not translate the words between { and } characters
 * (like {LANG_LEFT}, {TITLE} and so on...) and HTML elements.
 * Line breaks are specified as "\n".
 * 
 * Some localization values are having variables in them, which
 * variables are given value by the frontend. You should familiarize
 * yourself with the values given so they can be output in good order
 * in the newly translated file.
 * (See lang_key() in /includes/language.php)
*/

// The array to be loaded is called $localized.
// It is merged into the global $localization array by load_lang().
$localized = array(
	'NEXT'	=>	"Next",
	
	'MANDATORY VARIABLES'	=>	"Fields marked with a red star (" .'<span class="red-star">*</span>'. ") are mandatory.",
	
	'NONSTANDARD CONFIGURATION'	=>	"Your configuration seems to be non-standard. It might affect your users in a bad way!",
	
	'USER AUTH ERROR TITLE'	=>	"Authentication error",
	'USER AUTH ERROR'	=>	"The credentials stored in your browser cookie are invalid. Due to this failure, you have been logged out.",
	'USER CLIENT ERROR TITLE'	=>	"Validation error",
	'USER CLIENT ERROR'	=>	"The information stored in your browser cookie are invalid. Due to this failure, you have been logged out.",
	'USER READONLY'	=>	"This instance of the user object is running in read-only mode.",
	
	'LANGUAGE NOT FOUND'	=>	"Requested language file {FILE} not found.",
	
	'MYSQL'	=>	"MySQL",
	'QUERY ERROR'	=>	"There were an error executing the SQL query.\nThe error message was: {ERROR}.\nThe query was: {QUERY}",
	
	'MODULE NO ID NO FILE'	=>	"Unable to load unspecified new module. Filename is missing.",
	'MODULE NO FILE'	=>	"Module filename was not specified for ID #{ID}.",
	'MODULE FILE MISSING'	=>	"The requested module file {FILE} is missing.",
	
	'TEMPLATE FILE MISSING'	=>	"The requested file {FILE} not found in basedir {BASEDIR}.",
	'TEMPLATE MISSING'	=>	"Template {TEMPLATE} is not loaded.",
	'TEMPLATE STACK ALREADY'	=>	"The stack named {STACK} already exists.",
	'TEMPLATE STACK MISSING'	=>	"The stack named {STACK} does not exist.",
		
	'DIRECT TITLE'	=>	"Direct opening",
	'DIRECT'	=>	"This page does not support direct opening.",
	
	'REDIRECT'	=>	'You are being redirected. If your browser does not support redirection, click <a href="{LINK}">here</a>.',
	
	'USERNAME'	=>	"Username",
	'PASSWORD'	=>	"Password",
	
	'MENU INVALID'	=>	"Invalid menu!",
	'MENU WRONG ID'	=>	"The module is configured to show menu ID #{ID}, but such menu does not exist.",
	'MENU NO ID'	=>	"The module is not configured properly.",
	
	'USERBOX NOT FOUND'	=>	"Your module list does not contain a userbox.\nA userbox was added to the list of appearing modules to prevent locking yourself out from the system.",
	'USERBOX LOGIN'	=>	"Login",
	'USERBOX REGISTER'	=>	"Register",
	'USERBOX RECOVER'	=>	"Lost password",
	'USERBOX WELCOME'	=>	"Welcome, {USERNAME}!",
	'USERBOX AVATAR ALT'	=>	"Your avatar",
	'USERBOX PROFILE'	=>	"Profile",
	'USERBOX USERCP'	=>	"Control panel",
	'USERBOX ADMINCP'	=>	"Administrator panel",
	'USERBOX LOGOUT'	=>	"Logout",
	
	'LOGIN EMPTY'	=>	"The username or password field was left empty.",
	'LOGIN ALREADY'	=>	"You are already logged in.",
	'LOGIN AUTH FAIL'	=>	"The username or password you supported is invalid, or your user does not exist.",
	'LOGIN NOT ACTIVATED'	=>	"You account is not activated. Please check your e-mail inbox and activate your account before using it.",
	'LOGIN SUCCESS'	=>	"You have successfully logged in. Welcome!",
	
	'LOGOUT NOT LOGGED IN'	=>	"You are not logged in.",
	'LOGOUT SUCCESS'	=>	"You have successfully logged out.",
);
?>
