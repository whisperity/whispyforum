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
	'DIRECT TITLE'	=>	"Direct opening",
	'DIRECT'	=>	"This page does not support direct opening.",
	'REDIRECT'	=>	'You are being redirected. If your browser does not support redirection, click <a href="{LINK}">here</a>.',
	'USERNAME'	=>	"Username",
	'PASSWORD'	=>	"Password",
	
	'USERBOX LOGIN'	=>	"Login",
	'USERBOX REGISTER'	=>	"Register",
	'USERBOX RECOVER'	=>	"Lost password",
	'LOGIN EMPTY'	=>	"The username or password field was left empty.",
	'LOGIN ALREADY'	=>	"You are already logged in.",
	'LOGIN AUTH FAIL'	=>	"The username or password you supported is invalid, or your user does not exist.",
	'LOGIN NOT ACTIVATED'	=>	"You account is not activated. Please check your e-mail inbox and activate your account before using it.",
	'LOGIN SUCCESS'	=>	"You have successfully logged in. Welcome!",
);
?>
