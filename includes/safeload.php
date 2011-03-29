<?php
 /**
 * WhispyForum function file - safeload.php
 * 
 * Loads all required libraries for usage
 * without generating any environment.
 * 
 * This script is a modified copy of load.php
 * 
 * WhispyForum
 */
echo '<head>
	<title>{GLOBAL_TITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
<link rel="stylesheet" type="text/css" href="themes/winky/style.css">'."\n"; // We load the default stylesheet

/* Libraries */
// Template conductor (we load it before everything because templates are needed to get error messages)
require("templates.class.php");
global $Ctemplate; // Class is global
$Ctemplate = new class_template;
/* Libraries */

/* Preload checks */
// Check whether configuration file exists
if ( file_exists("config.php") == 1 )
{
	require("config.php"); // Require and embed it
	
	// Check whether the installation was successful
	if ( !defined('WHISPYFORUM') ) // There is a "DEFINE()" constant in config.php which is a random UUID generated on install
	{
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_package_settings.png", // Text file icon
			'TITLE'	=>	"{LANG_LOAD_CORRUPTION}", // Error title
			'BODY'	=>	"{LANG_LOAD_CORRUPTION_BODY}", // Error text
			'ALT'	=>	"{LANG_LOAD_CORRUPTION_ALT}" // Alternate picture text
	), FALSE ); // We output an error message
	exit; // Terminate the script
	} // Else: do nothing
} elseif ( file_exists("config.php") == 0 ) // If not
{
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Unaviable file icon
		'TITLE'	=>	"{LANG_LOAD_NOCFG}", // Error title
		'BODY'	=>	"{LANG_LOAD_NOCFG_BODY}", // Error text
		'ALT'	=>	"{LANG_FILE_UNAVIABLE}" // Alternate picture text
	), FALSE ); // We output an error message
	exit; // Terminate the script
}

/* Preload checks */

/* Libraries */
// mySQL database layer
require("mysql.class.php");
global $Cmysql; // Class is global
$Cmysql = new class_mysql;

// users & session manager 
require("users.class.php");
global $Cusers; // Class is global
$Cusers = new class_users;

// general functions
require("includes/functions.php");
/* Libraries */

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_GET,true))."<br>";
print "<h4>POST</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_POST,true))."<br>";
print "<h4>FILES</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_FILES,true))."<br>";

/* START GENERATION */
$Cmysql->Connect(); // Connect to database
$Cusers->Initialize(); // We initialize the userdata
// User initialization also loads the language file

/* DEVELOPEMENT */
print "<h4>SESSION</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_SESSION,true))."<br>";
//print "<h4>SERVER</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_SERVER,true))."<br>";
//print "<h4>REQUEST</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_REQUEST,true))."<br>";
//print "<h4>ENVIRONMENT</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_ENV,true))."<br>";
//print "<h4>COOKIES</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_COOKIE,true))."<br>";
//print "<h4>Configuration</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($cfg,true))."<br>";
//print "<h4>Localization</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($wf_lang,true))."<br>";

/* FRAMEWORK */

function DoFooter()
{
	global $Cmysql; // Load classes
	
	$Cmysql->Disconnect(); // Disconnect from database
}

/* FRAMEWORK */

/* END GENERATION */
?>