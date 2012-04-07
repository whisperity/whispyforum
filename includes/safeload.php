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

/* Libraries */
// Template conductor (we load it before everything because templates are needed to get error messages)
require("template.php");
global $Ctemplate, $template; // Class is global
$Ctemplate = new class_template;
$template = new template;
/* Libraries */

// Load boot-time localizations (it's a lite edition of the general English localization, only containing strings which are required before initializing the user array)
include("language/bootlocal.php");

/* Preload checks */
// Check whether configuration file exists
if ( file_exists("config.php") == 1 )
{
	require("config.php"); // Load the configuration file
} elseif ( file_exists("config.php") == 0 ) // If not
{
	// We embed the default (tuvia) stylesheet so the error message will appear properly
	echo '<link rel="stylesheet" type="text/css" href="themes/tuvia/style.css">';
	
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Unavailable file icon
		'TITLE'	=>	"{LANG_LOAD_NOCFG}", // Error title
		'BODY'	=>	"{LANG_LOAD_NOCFG_BODY}", // Error text
		'ALT'	=>	"{LANG_FILE_UNAVAILABLE}" // Alternate picture text
	), FALSE ); // We output an error message
	exit; // Terminate execution
}

/* Preload checks */

/* Libraries */
// mySQL database layer
// general functions
require("includes/functions.php");

require("mysql.php");
global $Cmysql, $sql; // Class is global
$Cmysql = new class_mysql;

$sql = new mysql( $cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname'] );

// users & session manager 
require("user.php");
global $Cusers, $user; // Class is global
$Cusers = new class_users;

/* Libraries */

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
prettyVar($_GET, true);
print "<h4>POST</h4>";
prettyVar($_POST, true);
print "<h4>FILES</h4>";
prettyVar($_FILES, true);

/* START GENERATION */
$Cmysql->Connect(); // Connect to database
$Cusers->Initialize(); // We initialize the userdata
// User initialization also loads the language file

$user = new user(0, FALSE);

/* DEVELOPEMENT */
print "<h4>SESSION</h4>";
prettyVar($_SESSION, true);
// print "<h4>SERVER</h4>";
// prettyVar($_SERVER, true);
// print "<h4>REQUEST</h4>";
// prettyVar($_REQUEST, true);
// print "<h4>ENVIRONMENT</h4>";
// prettyVar($_ENV, true);
// print "<h4>COOKIES</h4>";
// prettyVar($_COOKIE, true);
// print "<h4>Configuration</h4>";
// prettyVar($cfg, true);
// print "<h4>Localization</h4>";
// prettyVar($wf_lang, true);

/* FRAMEWORK */
// Load a lite version of head (will load the title and the stylesheet)
echo '<head>
	<title>' .config("global_title"). '</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<link rel="stylesheet" type="text/css" href="themes/' .$_SESSION['theme_name']. '/style.css">'."\n";
function DoFooter()
{
	global $Cmysql, $sql, $user; // Load classes
	
	unset($user);
	$Cmysql->Disconnect(); // Disconnect from database
	unset($sql);
	
	exit;
}

/* FRAMEWORK */

/* END GENERATION */
?>
