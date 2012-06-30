<?php
/**
 * WhispyForum
 * 
 * /language/english/install.php
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
	'INSTALL GLOBAL TITLE'	=>	"WhispyForum Installer",
	
	/* Installer menu */
	'INSTALL MENU HEADER'	=>	"Installer steps",
	'INSTALL STEP 1'	=>	"1. Introduction",
	'INSTALL STEP 2'	=>	"2. Configuration file",
	'INSTALL STEP 3'	=>	"3. Writing configuration file",
	'INSTALL STEP 4'	=>	"4. Testing database connection",
	'INSTALL STEP 5'	=>	"5. Creating database",
	'INSTALL STEP 6'	=>	"6. Creating tables",
	'INSTALL STEP 7'	=>	"7. Adminisrator user",
	'INSTALL STEP 8'	=>	"8. Registering administrator user",
	'INSTALL STEP 9'	=>	"9. Site configuration",
	'INSTALL STEP 10'	=>	"10. Saving site configuration",
	'INSTALL STEP 11'	=>	"11. Finish",
	
	/* Step 1: Introduction */
	'INTRODUCTION TITLE'	=>	"Introduction",
	'INTRODUCTION TRY OUT SETTINGS'	=>	"You now have a brief opportunity to try out the themes and languages currently installed on the server. You can now change the way the Installer appears. These language and theme preferences will be carried on as the defaults of the main system.",
	'INTRODUCTION LANGUAGE'	=>	"Language:",
	'INTRODUCTION THEME'	=>	"Theme:",
	'INTRODUCTION MODIFY SETTINGS'	=>	"Modify language and theme",
	'INTRODUCTION BODY'	=>	"Thank you for choosing WhispyForum. This version is the successor of the first WhispyForum, a PHP based portal/forum engine. We think you and your users will delight in the use of this system.",
	'DISCLAIMER HEAD'	=>	"Disclaimer! <small>Please read carefully!</small>",
	'DISCLAIMER TEXT'	=>	"This system is in-developement state, hence there are no stable release. There can be unexpected, unforseen and imminent data losses, which can lead to system instability.\n
While using the system, you're well advised to create data backups regurarly, in case if you (unfortunately) have to restore.\n
This package is provided as is. By using this package, you hereby accept our disclaimer. Until we push a stable release, we cannot guarantee that you won't experience any data loss.\n
<small>You've been warned...</small>",
	'ALREADY INSTALLED HEAD'	=>	"WhispyForum is already installed",
	'ALREADY INSTALLED TEXT'	=>	"It seems that WhispyForum is already installed.\n
Reinstalling the system can produce a lot of stability and incompatibility issues. Primarily, when the installer creates the database table entries, it can truncate/rewrite/append tables that shouldn't be accessed in such ways. On the other hand, the configuration file can get corrupted.\n
Before we can move on installing the system, you have to make your data safe. Create a database backup, and drop every tables from the database. After that, delete <tt>config.php</tt> from the server root directory.\n
<small>Existing configuration detected. Setup process terminated.</small>",
	'CHECK HEADER'	=>	"Environment checks",
	'CHECK CRITICAL'	=>	"Critical error",
	'CHECK ERROR'	=>	"Error",
	'CHECK WARNING'	=>	"Warning",
	'CHECK INFO'	=>	"Notice/Information",
	'CHECK SUCCESS'	=>	"Success",
	'PHPVERSION'	=>	"PHP version is at least 4.3.0",
	'PHPVERSION 1'	=>	"WhispyForum needs at least PHP 4.3.0 to operate properly.\nHaving a lower version installed might produce critical errors, and it is well advised to update your installation.",
	'REGISTER GLOBALS'	=>	"<tt>register_globals</tt> is turned off.",
	'REGISTER GLOBALS 1'	=>	"To lift some security concerns, it is advised to turn register_globals off. WhispyForum will operate if it is turned on nontheless.",
	'CHECK MYSQL'	=>	"MySQL extension is loaded.",
	'CHECK MYSQL 1'	=>	"WhispyForum needs the MySQL extension to be loaded, as that is the current layer to access the database. Without it, there is no chance for a proper operation.",
	'WRITABLE CONFIG'	=>	"Configuration file is writable.",
	'WRITABLE CONFIG 1'	=>	"<tt>config.php</tt> needs to be writable by the webserver for the installation to complete.",
	'WRITABLE CACHE'	=>	"Cache is writable.",
	'WRITABLE CACHE 1'	=>	"Make sure that <tt>cache/</tt> is writable by the webserver's user (usually <tt>www-data</tt>).\nWithout it, the caching system will not work.",
	'WRITABLE UPLOAD'	=>	"Upload folder is writable.",
	'WRITABLE UPLOAD 1'	=>	"Make sure that <tt>upload/</tt>, and all subfolders inside are writable by the webserver's user (usually <tt>www-data</tt>).\nNo access to the upload folder will cause failure in the system, as there will be no chance of uploading content like avatars or attachments.",
	'SUPERFAIL NOTICE'	=>	"There were one or more errors checking the environment in which the system will operate. Some errors point out lacks of mandatory settings in the configuration and thus, the installation cannot continue.\nPlease revise the output above and resolve the errors.",
	
	/* Step 2: Configuration file */
	'CONFIG INTRO'	=>	"The configuration file stores the basic configuration values of your site, like database connection information. Values which are not obtainable from the database are stored on the server's hard drive in this file.",
	'DATABASE CONFIG DATA'	=>	"Database connection configuration",
	'DATABASE TYPE'	=>	"Type of database",
	'DATABASE HOST'	=>	"Host of database server",
	'DATABASE USER'	=>	"Username",
	'DATABASE PASS'	=>	"Password",
	'DATABASE NAME'	=>	"Name of database",
);
?>