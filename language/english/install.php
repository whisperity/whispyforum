<?php
/**
 * WhispyForum
 * 
 * /language/english/install.php
*/

/**
 * For translation information, see core.php in the localization folder.
*/

// The array to be loaded is called $localized.
// It is merged into the global $localization array by load_lang().
$localized = array(
	'INSTALL GLOBAL TITLE'	=>	"WhispyForum Installer",
	
	/* Installer menu */
	'INSTALL MENU HEADER'	=>	"Installer",
	'INSTALL STEP 1'	=>	"Introduction",
	'INSTALL STEP 2'	=>	"Configuration file",
	'INSTALL STEP 3'	=>	"Setting up database",
	'INSTALL STEP 4'	=>	"Registering administrator user",
	'INSTALL STEP 5'	=>	"Finish",
	
	/* Introduction */
	'INTRODUCTION TITLE'	=>	"Introduction",
	'INTRODUCTION TRY OUT SETTINGS'	=>	"You now have a brief opportunity to try out the themes and languages currently installed on the server. You can now change the way the installer appears. These language and theme preferences will be carried on as the defaults for the main system.",
	'INTRODUCTION LANGUAGE'	=>	"Language:",
	'INTRODUCTION THEME'	=>	"Theme:",
	'INTRODUCTION MODIFY SETTINGS'	=>	"Modify language and theme",
	'INTRODUCTION BODY'	=>	"Welcome to the installer, which will help and guide you through setting up this copy of WhispyForum, with getting the database and other subsystems configured.",
	'DISCLAIMER HEAD'	=>	"Disclaimer! <small>Please read carefully!</small>",
	'DISCLAIMER TEXT'	=>	"This package is provided as-is, with no obligatory warranty. The current release is marked a development release. Data safety or system stability could not be guaranateed.\n\nAlways use the system with taking necessary precautions in form of backup and stability monitoring.",
	'ALREADY INSTALLED HEAD'	=>	"WhispyForum is already installed",
	'ALREADY INSTALLED TEXT'	=>	"It seems that WhispyForum is already installed.\n
Reinstalling the system can produce a lot of stability and incompatibility issues. Primarily, when the installer creates the database table entries, it can truncate/rewrite/append tables that shouldn't be accessed in such ways. On the other hand, the configuration file can get corrupted.\n
Before we can move on installing the system, you have to make your data safe. Create a database backup, and drop every tables from the database. After that, delete <tt>config.php</tt> from the server root directory.\n
<small>Existing configuration detected. Setup process terminated.</small>",
	'CHECK HEADER'	=>	"Environment checks",
	'CHECK BODY'	=>	"Every software has a set of system recommendations (even web applications), and WhispyForum is not different. Before the installation could continue, some environmental checks will be ran on your server installation.",
	'CHECK CRITICAL'	=>	"Critical error",
	'CHECK ERROR'	=>	"Error",
	'CHECK WARNING'	=>	"Warning",
	'CHECK INFO'	=>	"Notice/Information",
	'CHECK SUCCESS'	=>	"Success",
	'PHPVERSION FAIL'	=>	"PHP version is less than {REQUIRED_VERSION}",
	'PHPVERSION FAIL BODY'	=>	"WhispyForum needs at least PHP {REQUIRED_VERSION} to operate properly. Current version is {CURRENT_VERSION}.\nHaving an older version installed might produce critical errors, and it is well advised to update your installation for general compatibility.",
	'PHPVERSION OK'	=>	"PHP version is {CURRENT_VERSION}",
	'PHPVERSION OK BODY'	=>	"Your server seems to have PHP version {CURRENT_VERSION} installed.",
	'EXTENSION FAIL'	=>	"No database extensions found.",
	'EXTENSION FAIL BODY'	=>	"The checker was unable to find any database extensions which are known by the system and loaded on the server. Please make sure that at least one of the following extensions (specifically the one you will use to access your database host) is loaded: {EXTENSIONS}.",
	'EXTENSION OK'	=>	"Found compatible database extensions.",
	'EXTENSION OK BODY'	=>	"At least one database extension was found loaded on the server and known by the system. You will be able to use the following extensions: {EXTENSIONS}.",
	'CONFIG FAIL'	=>	"Failed to write configuration file.",
	'CONFIG FAIL BODY'	=>	"The webserver's user (usually <tt>www-data</tt>) needs to have access to write the <tt>config.php</tt> file in the document root folder.",
	'CONFIG OK'	=>	"Configuration file is writable.",
	'UPLOAD FAIL'	=>	"Failed to write user upload folder.",
	'UPLOAD FAIL BODY'	=>	"The webserver's user (usually <tt>www-data</tt>) needs to have access to write the <tt>upload/</tt> folder in the document root folder for user uploads to work properly.\nYou can neglect this error, but doing so will wind up further malfunction in production.",
	'UPLOAD OK'	=>	"Upload folder is writable.",
	'SUPERFAIL NOTICE'	=>	"There were one or more errors checking the environment in which the software should operate and thus the installation cannot continue.\nPlease revise the output above and resolve the found errors.",
	
	/* Configuration file */
	'CONFIG TITLE'	=>	"Configuration file",
	'CONFIG INTRO'	=>	"The configuration file stores the basic configuration values of your site, like database connection information. Values which are not obtainable from the database are stored on the server's hard drive in this file.",
	'DATABASE CONFIG DATA'	=>	"Database connection configuration",
	'DATABASE TYPE'	=>	"Type of database",
	'DATABASE HOST'	=>	"Host of database server",
	'DATABASE USER'	=>	"Username",
	'DATABASE PASS'	=>	"Password",
	'DATABASE NAME'	=>	"Name of database",
	
	'MYSQLI'	=>	"MySQL improved",
	
	/* Writing configuration file */
	'WRITECONFIG TITLE'	=>	"Configuration file",
	'WRITECONFIG RETURN BODY'	=>	"To revise the configuration details and to try again, click the button below.",
	'WRITECONFIG CONNECTION ERROR'	=>	"Unable to connect to the database",
	'WRITECONFIG CONNECTION ERROR BODY'	=>	"Using <tt>{LAYER}</tt>, the server responded <tt>{ERROR_MESSAGE}</tt>.",
	'WRITECONFIG CONFIG WRITTEN'	=>	"Successfully created configuration file.",
	
	/* Creating database */
	'DBCREATE TITLE'	=>	"Creating database",
	'DBCREATE SUCCESS'	=>	"The database has been successfully created or it was already there.",
	'DBCREATE FAIL'	=>	"Failed to create the database",
	'DBCREATE FAIL BODY'	=>	"This error usually indicates inability to connect to the database or lack of <tt>CREATE</tt> privilege of the current user.",
	'DBCREATE FAIL MESSAGE'	=>	"To revise database configuration settings, click the button below.",
	
	/* Creating database tables */
	'DBTABLES TITLE'	=>	"Creating tables",
	'DBTABLES FAIL'	=>	"Failed to create database structure",
	'DBTABLES SUCCESS'	=>	"The database tables were created successfully",
	
	/* Administrator user */
	'ADMINUSER TITLE'	=>	"Administrator user",
	'ADMINUSER DATA'	=>	"Administrator user credentials",
	'ADMINUSER INFO'	=>	"The administrator user created now will serve as the first user of the system. With this user, you will be able to access every configuration directive while using the system.",
	
	/* Registering administrator user */
	'ADMINUSER RETURN BODY'	=>	"To revise the credentials and to try again, click the button below.",
	'ADMINUSER REGISTER ERROR'	=>	"Failed to register the administrator user.",
	'ADMINUSER SUCCESS'	=>	"Your administrator user was registered successfully.",
	
	/* Finish */
	'FINISH TITLE'	=>	"Finish",
	'FINISH BODY'	=>	"The installer has finished setting up your system. You will be able to finetune the settings after logging into your previously created administrator user.",
	'FINISH CONTINUE MESSAGE'	=>	"Clicking this button will lead you to the main site."
);
?>
