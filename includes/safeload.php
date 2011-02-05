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
 echo date('l jS \of F Y H:i:s')." loader started\n<br>"; // DEV
 echo '<link rel="stylesheet" type="text/css" href="themes/winky/style.css">'."\n"; // We load the default stylesheet

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
			'THEME_NAME'	=>	"winky", // Theme name
			'PICTURE_NAME'	=>	"Nuvola_apps_package_settings.png", // Text file icon
			'TITLE'	=>	"Corruption!", // Error title
			'BODY'	=>	"WhispyForum appears to be installed, however, the configuration file lacks some important variables. It's advised to reinstall the system. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.', // Error text
			'ALT'	=>	"Corrupt configuration" // Alternate picture text
	), FALSE ); // We output an error message
	} // Else: do nothing
 } elseif ( file_exists("config.php") == 0 ) // If not
 {
 	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	"winky", // Theme name
		'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Unaviable file icon
		'TITLE'	=>	"Configuration file not found!", // Error title
		'BODY'	=>	"The site's configuration file is missing. It usally means that the engine isn't installed properly. Without configuration, the engine cannot be used, because it can't connect to the database. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.', // Error text
		'ALT'	=>	"File unaviable" // Alternate picture text
	), FALSE ); // We output an error message
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
?>

<?php
echo date('l jS \of F Y H:i:s')." loading finished\n<br>"; // DEV
echo "\n<br>".date('l jS \of F Y H:i:s')." content generation started\n<br>"; // DEV
?>

<?php
 /* START GENERATION */
 $Cmysql->Connect(); // Connect to database
 $Cusers->Initialize(); // We initialize the userdata
 
 /* DEVELOPEMENT */
 print "<h4>SESSION</h4>";
 print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_SESSION,true))."<br>"; 
 
 /* FRAMEWORK */
 
 if ( $_GET['ds'] == 1 )
 {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]);
 }
 
 function DoFooter()
 {
	global $Ctemplate, $Cmysql; // Load classes
	
	echo "\n<br>".date('l jS \of F Y H:i:s')." content generation ended\n<br>"; // DEV
	
	$Cmysql->Disconnect(); // Disconnect from database
 }
 
 /* FRAMEWORK */
 
 /* END GENERATION */
?>