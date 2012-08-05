<?php
/**
 * WhispyForum
 * 
 * /install.php
*/

// Define some constants for this environment.
define('WHISPYFORUM', TRUE);
define('REQUIRE_SAFEMODE', TRUE);
define('WORKING_DIRECTORY', getcwd());

// Load and initialize the required libraries
require "includes/functions.php";
require "includes/language.php";
require "includes/template.php";
require "includes/user.php";

global $template, $user;
$template = new template;
load_lang("core");
$template->load_template("framework", TRUE);
$user = new user(0, FALSE);

load_lang("install");
$template->load_template("install", TRUE);

// Fetch the install configuration from session.
if ( !is_array(@$_SESSION['install_config']) )
{
	$_SESSION['install_config'] = array(
		'language'	=>	"english",
		'theme_name'	=>	"tuvia",
		'step'	=>	1
	);
}

if ( isset($_POST['step']) )
{
	// If we receive a new "step" variable in the POST header, we save it as the current step,
	// so the installer later on will load the wanted step properly.
	$_SESSION['install_config']['step'] = intval(@$_POST['step']);
} else {
	$_SESSION['install_config']['step'] = 1;
}

// If we are on step one and got a new language and theme option, we set it
// so the installer will automatically load the new ones.
if ( ( @$_POST['new_language'] != NULL || @$_POST['new_theme'] != NULL ) && $_SESSION['install_config']['step'] == 1 )
{
	$_SESSION['install_config']['language'] = ( @$_POST['new_language'] != NULL ? @$_POST['new_language'] : $_SESSION['install_config']['language'] );
	$_SESSION['install_config']['theme_name'] = ( @$_POST['new_theme'] != NULL ? @$_POST['new_theme'] : $_SESSION['install_config']['theme_name'] );
}

// The list of database layers installed into the system.
if ( in_array($_SESSION['install_config']['step'], array(1, 2, 3), TRUE) )
{
	$layers_includes = array('mysqli');
	$layers_available = array();
	
	// The list of layers known by the system and installed on the server
	foreach ( $layers_includes as $v )
		if ( extension_loaded($v) && file_exists("includes/" .$v. ".php") )
			$layers_available[] = $v;
	
	sort($layers_available);
}

// Load the configuration-specific language.
// This automatically overwrites the previously loaded (english) localization.
load_lang("install", $_SESSION['install_config']['language']);

print $template->parse_template("header", array(
	'HEADER'	=>	NULL,
	'GLOBAL_TITLE'	=>	lang_key("INSTALL GLOBAL TITLE"),
	'THEME_NAME'	=>	$_SESSION['install_config']['theme_name']) );

// Create two stacks to buffer the content.
$template->create_stack("left");
$template->create_stack("right");

switch ( $_SESSION['install_config']['step'] )
{
	default:
	case 0:
	case 1:
		/* Introduction */
		$step_title = lang_key("INTRODUCTION TITLE");
		$step_picture = "install.png";
		$step_number = 1; // This is the first step.
		
		// Fetch language options for the starter form
		$template->create_stack("language options");
		foreach ( scandir("language") as $subfolder )
		{
			if ( !in_array($subfolder, array(".", "..", ".svn")) && is_dir("language/".$subfolder) && is_readable("language/".$subfolder) )
			{
				// After we exclude some nonused folders, we open each subfolder searching for a defintion file.
				if ( is_readable("language/".$subfolder."/definition.php") )
				{
					include("language/".$subfolder."/definition.php");
					
					$template->add_to_stack( $template->parse_template("introduction language option", array(
						'SELECTED'	=>	($_SESSION['install_config']['language'] == $subfolder ? " selected " : " "),
						'DIR_NAME'	=>	$subfolder,
						'LOCALIZED_NAME'	=>	$definition['LOCALIZED_NAME'],
						'SHORT_NAME'	=>	$definition['SHORT_NAME'],
						'LANG_CODE'	=>	$definition['LANG_CODE']
					) ), "language options");
				}
			}
		}
		
		// Fetch theme options for the starter form
		$template->create_stack("theme options");
		foreach ( scandir("themes") as $subfolder )
		{
			if ( !in_array($subfolder, array(".", "..", ".svn")) && is_dir("themes/".$subfolder) && is_readable("themes/".$subfolder) )
			{
				// After we exclude some nonused folders, we open each subfolder searching for a defintion file.
				if ( is_readable("themes/".$subfolder."/definition.php") )
				{
					include("themes/".$subfolder."/definition.php");
					
					$template->add_to_stack( $template->parse_template("introduction theme option", array(
						'SELECTED'	=>	($_SESSION['install_config']['language'] == $subfolder ? " selected " : " "),
						'DIR_NAME'	=>	$subfolder,
						'NAME'	=>	$definition['NAME']
					) ), "theme options");
				}
			}
		}
		
		$template->add_to_stack(
			$template->parse_template("introduction form", array(
				'TITLE'	=>	lang_key("INTRODUCTION SETTING HEADER"),
				'TRY_OUT_SETTINGS'	=>	lang_key("INTRODUCTION TRY OUT SETTINGS"),
				'INTRODUCTION_LANGUAGE'	=>	lang_key("INTRODUCTION LANGUAGE"),
				'LANGUAGES_EMBED'	=>	$template->get_stack("language options"),
				'INTRODUCTION_THEME'	=>	lang_key("INTRODUCTION THEME"),
				'THEMES_EMBED'	=>	$template->get_stack("theme options"),
				'INTRODUCTION_MODIFY_SETTINGS'	=>	lang_key("INTRODUCTION MODIFY SETTINGS"),
			) ), "left");
		
		// There are some mandatory checks to ensure that the system can work in this environment.
		// The $superfail variable might be turned TRUE to prevent further installation.
		$_SESSION['install_config']['superfail'] = FALSE;
		
		$template->create_stack("envchecks");
		
		$template->add_to_stack( $template->parse_template("introduction envcheck header", array(
			'ENVCHECK_HEADER'	=>	lang_key("CHECK HEADER"),
			'ENVCHECK_DESCRIPTION'	=>	lang_key("CHECK BODY")
		) ), "envchecks");
		
		function envcheck( $type, $header, $message, $custom_image = NULL, $set_superfail = FALSE )
		{
			/**
			 * This function adds one parsed template of one environment check event to the 'left' stack here.
			 * 
			 * $type can either be 'CRITICAL', 'ERROR', 'WARNING', 'INFO' or 'SUCCESS' defining the type of check
			 * $header and $message are the text output of the box
			 * $custom_image can override the image printed
			 * $set_superfail will lock the user to 'superfail' state if set to TRUE
			*/
			
			global $template;
			
			switch (strtolower($type))
			{
				case "critical":
					$image = "critical.png";
					break;
				case "error":
					$image = "error.png";
					break;
				case "warning":
					$image = "warning.png";
					break;
				case "info":
					$image = "info.png";
					break;
				case "success":
					$image = "success.png";
					break;
			}
			
			if ( isset($custom_image) && $custom_image != NULL )
				$image = $custom_image;
			
			// If call requested turning superfail on, we load the config cache and set superfail to TRUE.
			if ( $set_superfail )
				$_SESSION['install_config']['superfail'] = TRUE;
			
			$template->add_to_stack( $template->parse_template("introduction envcheck", array(
				'TYPE'	=>	$type,
				'STATUS'	=>	lang_key("CHECK " .strtoupper($type)),
				'THEME_NAME'	=>	$_SESSION['install_config']['theme_name'],
				'IMAGE'	=>	$image,
				'TITLE'	=>	$header,
				'MESSAGE'	=>	$message
			) ), "envchecks");
		}
		
		// Check PHP version. Current release needs at least PHP 5.4.4.
		$current = PHP_VERSION;
		$required= "5.4.4";
		$compare = version_compare($current, $required, ">=");
		
		envcheck(
			( !$compare ? 'CRITICAL' : 'INFO' ),
			( !$compare 
				? lang_key("PHPVERSION FAIL", array(
					'REQUIRED_VERSION'	=>	$required))
				: lang_key("PHPVERSION OK", array(
					'CURRENT_VERSION'	=>	$current)) ),
			( !$compare 
				? lang_key("PHPVERSION FAIL BODY", array(
					'CURRENT_VERSION'	=>	$current,
					'REQUIRED_VERSION'	=>	$required) )
				: lang_key("PHPVERSION OK BODY", array(
					'CURRENT_VERSION'	=>	$current)) ),
			NULL,
			( !$compare ));
		
		// Check database extension layers availability
		$extensions = count($layers_available);
		
		envcheck(
			( $extensions === 0 ? 'CRITICAL' : 'INFO' ),
			( $extensions === 0 ? lang_key("EXTENSION FAIL") : lang_key("EXTENSION OK") ),
			( $extensions === 0 
				? lang_key("EXTENSION FAIL BODY", array(
					'EXTENSIONS'	=>	implode(", ", $layers_includes)) )
				: lang_key("EXTENSION OK BODY", array(
					'EXTENSIONS'	=>	implode(", ", $layers_available)) ) ),
			"driver.png",
			( $extensions === 0 ));
		
		// Check whether config.php is writable.
		// (If the file already exists, we check whether it is writable -- installer will disallow rewrite,
		// if it does not exist, we create an empty dummy file to check writableness.)
		if ( file_exists("config.php") )
			$cfg_check = is_writable("config.php");
		
		if ( !file_exists("config.php") )
		{
			$cfg_check = @file_put_contents("config.php", "<?php\n\t\$dummy = TRUE;\n?>");
			if ( $cfg_check !== FALSE )
				$cfg_check = TRUE;
		}
		
		envcheck(
			( !$cfg_check ? 'CRITICAL' : 'SUCCESS' ),
			( !$cfg_check ? lang_key("CONFIG FAIL") : lang_key("CONFIG OK") ),
			( !$cfg_check ? lang_key("CONFIG FAIL BODY") : NULL ),
			( !$cfg_check ? "locked.png" : "edit.png" ),
			( !$cfg_check ));
		
		// Check whether upload/ is writable.
		if ( !is_writable("upload") )
			@mkdir("upload");
		
		envcheck(
			( !is_writable("upload") ? 'ERROR' : 'SUCCESS' ),
			( !is_writable("upload") ? lang_key("UPLOAD FAIL") : lang_key("UPLOAD OK") ),
			( !is_writable("upload") ? lang_key("UPLOAD FAIL BODY") : NULL ),
			( !is_writable("upload") ? "locked.png" : NULL ),
			FALSE);
		
		// Check whether the system is installed
		include "config.php";
		if ( is_array(@$cfg) )
		{
			// If the system is installed, we output an error message for the user.
			$template->add_to_stack( ambox('CRITICAL', lang_key("ALREADY INSTALLED TEXT"), lang_key("ALREADY INSTALLED HEAD"), "locked.png"), "left");
		} else {
			// If the system is not installed, we add the welcome screen.
			
			$template->add_to_stack(
				$template->parse_template("introduction", array(
					'INTRODUCTION_BODY'	=>	lang_key("INTRODUCTION BODY"),
					'ENVIRONMENT_CHECKS'	=>	$template->get_stack("envchecks"),
					// Until developer state is resolved, the ambox() should stay.
					'DEVELOPER_STATE_BOX'	=>	ambox('CRITICAL', lang_key("DISCLAIMER TEXT"), lang_key("DISCLAIMER HEAD"))
				) ), "left");
			
			// We need to reload the cached configuration from the disk.
			if ( $_SESSION['install_config']['superfail'] === TRUE )
			{
				// If the previous environment checks resulted in a "superfailure",
				// a condition which prevents us from continuing the installation, we block it.
				$template->add_to_stack(
					$template->parse_template("introduction superfail", array(
						'SUPERFAIL_NOTICE'	=>	lang_key("SUPERFAIL NOTICE"),
						'SUBMIT_CAPTION'	=>	lang_key("NEXT")
					) ), "left");
			} elseif ( $_SESSION['install_config']['superfail'] === FALSE )
			{
				$template->add_to_stack(
					$template->parse_template("introduction forward form", array(
						'SUBMIT_CAPTION'	=>	lang_key("NEXT")
					) ), "left");
			}
		}
		
		break;
	case 2:
		/* Configuration file */
		$step_title = lang_key("CONFIG TITLE");
		$step_picture = "configuration.png";
		$step_number = 2; // This is the second step.
		
		// Generate list of available SQL handlers.
		$template->create_stack("sqlhandlers");
		foreach ( $layers_available as $v )
			$template->add_to_stack( $template->parse_template("config dbtype option", array(
				'VALUE'	=>	$v,
				'CAPTION'	=>	lang_key(strtoupper($v)),
				'SELECTED'	=>	( ( isset($_POST['error_return']) && $_POST['dbtype'] == $v ) ? 'selected="selected"' : NULL )
			) ), "sqlhandlers");
		
		$template->add_to_stack( $template->parse_template("config", array(
			'CONFIG_INTRO'	=>	lang_key("CONFIG INTRO"),
			'MANDATORY_VARIABLES'	=>	lang_key("MANDATORY VARIABLES"),
			'DATABASE_CONFIG_DATA'	=>	lang_key("DATABASE CONFIG DATA"),
			
			'DATABASE_TYPE'	=>	lang_key("DATABASE TYPE"),
			'DATABASE_HOST'	=>	lang_key("DATABASE HOST"),
			'DATABASE_USER'	=>	lang_key("DATABASE USER"),
			'DATABASE_PASS'	=>	lang_key("DATABASE PASS"),
			'DATABASE_NAME'	=>	lang_key("DATABASE NAME"),
			
			'NEXT_CAPTION'	=>	lang_key("NEXT"),
			
			/** Database configuration **/
			'DBTYPE_OPTIONS'	=>	$template->get_stack("sqlhandlers"),
			'DBHOST'	=>	( isset($_POST['error_return']) ? @$_POST['dbhost'] : "localhost" ),
			'DBUSER'	=>	( isset($_POST['error_return']) ? @$_POST['dbuser'] : "" ),
			'DBPASS'	=>	( isset($_POST['error_return']) ? @$_POST['dbpass'] : "" ),
			'DBNAME'	=>	( isset($_POST['error_return']) ? @$_POST['dbname'] : "whispyforum" ),
		)), "left");
		
		$template->delete_stack("sqlhandlers");
		
		break;
	case 3:
		/* Writing configuration file */
		$step_title = lang_key("WRITECONFIG TITLE");
		$step_picture = "edit.png";
		$step_number = 2; // While technically this is the third step, we list it as the second again.
		
		$mandatory_variable_fail = FALSE;
		$mandatory_variables = array('dbtype', 'dbhost', 'dbuser', 'dbpass', 'dbname');
		
		foreach ( $mandatory_variables as $v )
			if ( !isset($_POST[$v]) || @$_POST[$v] == NULL )
				$mandatory_variable_fail = TRUE;
		
		// Prepare an 'error return' form.
		$error_return = $template->parse_template("config error return", array(
			'DBTYPE'	=>	@$_POST['dbtype'],
			'DBHOST'	=>	@$_POST['dbhost'],
			'DBUSER'	=>	@$_POST['dbuser'],
			'DBPASS'	=>	@$_POST['dbpass'],
			'DBNAME'	=>	@$_POST['dbname'],
			
			'MESSAGE'	=>	lang_key("WRITECONFIG RETURN BODY"),
			'SUBMIT_CAPTION'	=>	lang_key("BACK")
		) );
		
		if ( $mandatory_variable_fail )
		{
			$template->add_to_stack( ambox('ERROR', lang_key("VARIABLE ERROR MULTI"), NULL), "left");
			
			$template->add_to_stack( $error_return, "left");
		} elseif ( !$mandatory_variable_fail )
		{
			$writefile = FALSE;
			
			// Check database connection
			if ( !array_search($_POST['dbtype'], $layers_available) )
			{
				$layer_name = "db_" .$_POST['dbtype'];
				require "includes/" .$_POST['dbtype']. ".php";
				
				$connection = $layer_name::test_connection($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
				if ( $connection === TRUE )
					// If database connection is successful, write configuration file.
					$writefile = TRUE;
			}
			
			if ( !$writefile )
			{
				$template->add_to_stack( ambox('CRITICAL', lang_key("WRITECONFIG CONNECTION ERROR BODY", array(
					'LAYER'	=>	$_POST['dbtype'],
					'ERROR_MESSAGE'	=>	$connection) ), lang_key("WRITECONFIG CONNECTION ERROR"), "driver.png"), "left");
				
				$template->add_to_stack( $error_return, "left");
			} elseif ( $writefile )
			{
				// Write configuration file.
				
				$configfile = fopen("config.php", "w");
				fwrite($configfile, $template->parse_template("config file", array(
					'DBTYPE'	=>	$_POST['dbtype'],
					'DBHOST'	=>	$_POST['dbhost'],
					'DBUSER'	=>	$_POST['dbuser'],
					'DBPASS'	=>	$_POST['dbpass'],
					'DBNAME'	=>	$_POST['dbname'],
					'TOKEN'	=>	token()
				) ) );
				fclose($configfile);
				
				$template->add_to_stack( ambox('SUCCESS', lang_key("WRITECONFIG CONFIG WRITTEN")), "left");
				
				$template->add_to_stack( $template->parse_template("config forward form", array(
					'SUBMIT_CAPTION'	=>	lang_key("NEXT")
				) ), "left");
			}
		}
		
		break;
	case 4:
		/* Creating database */
		$step_title = lang_key("DBCREATE TITLE");
		$step_picture = "database.png";
		$step_number = 3;
		
		require "config.php";
		require "includes/" .$cfg['dbtype']. ".php";
		$layer_name = "db_" . $cfg['dbtype'];
		$db = new $layer_name($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], NULL);
		
		// Fetch whether the database we need exists or not.
		$check = $db->query('SELECT schema_name FROM information_schema.schemata WHERE schema_name = "' .$db->escape($cfg['dbname']). '"');
		
		if ( $db->num_rows($check) === 0 )
			// Create the database
			$db->query("CREATE DATABASE " .$db->escape($cfg['dbname']). " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
		
		$db->free_result($check);
		
		// Check again to ensure proper creation.
		$check_again = $db->query('SELECT schema_name FROM information_schema.schemata WHERE schema_name = "' .$db->escape($cfg['dbname']). '"');
		
		if ( $db->num_rows($check_again) === 1 )
		{
			$template->add_to_stack( ambox('SUCCESS', lang_key("DBCREATE SUCCESS")), "left");
			
			$template->add_to_stack( $template->parse_template("dbcreate forward form", array(
				'SUBMIT_CAPTION'	=>	lang_key("NEXT")
			) ), "left");
		} elseif ( $db->num_rows($check_again) !== 1 )
		{
			$template->add_to_stack( ambox('CRITICAL', lang_key("DBCREATE FAIL BODY"), lang_key("DBCREATE FAIL"), "locked.png"), "left");
			
			$template->add_to_stack( $template->parse_template("config error return", array(
				'DBTYPE'	=>	$cfg['dbtype'],
				'DBHOST'	=>	$cfg['dbhost'],
				'DBUSER'	=>	$cfg['dbuser'],
				'DBPASS'	=>	$cfg['dbpass'],
				'DBNAME'	=>	$cfg['dbname'],
				'MESSAGE'	=>	lang_key("DBCREATE FAIL MESSAGE"),
				'SUBMIT_CAPTION'	=>	lang_key("BACK")
			) ), "left");
		}
		
		$db->free_result($check_again);
		unset($db);
		
		break;
	case 5:
		/* Creating tables */
		$step_title = lang_key("DBTABLES TITLE");
		$step_picture = "database.png";
		$step_number = 3;
		
		require "config.php";
		require "includes/" .$cfg['dbtype']. ".php";
		$layer_name = "db_" . $cfg['dbtype'];
		$db = new $layer_name($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname']);
		
		$database_setup = " ## Database setup.
		CREATE TABLE IF NOT EXISTS users (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`email` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`extra_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`loggedin` tinyint(1) NOT NULL DEFAULT '0',
			`activated` tinyint(1) NOT NULL DEFAULT '0',
			`token` varchar(49) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`userLevel` tinyint(2) NOT NULL DEFAULT '0',
			`avatar_filename` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`theme` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`post_count` int(6) NOT NULL DEFAULT '0',
			`news_comment_count` int(6) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `username` (`username`),
			UNIQUE KEY `email` (`email`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS config (
			`variable` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`value` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			UNIQUE KEY `variable` (`variable`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS modules (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`module` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`extra_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`align` tinyint(2) NOT NULL DEFAULT '0',
			`side` enum('left', 'right') NOT NULL DEFAULT 'left',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS menus (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`header` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS menu_entries (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`menu_id` int(10) NOT NULL,
			`label` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS forums (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`info` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
			`minLevel` enum('0', '1', '2', '3') NOT NULL DEFAULT '0',
			`createdate` int(16) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS topics (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`forumid` int(10) NOT NULL,
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`createuser` int(10) NOT NULL,
			`createdate` int(16) NOT NULL DEFAULT '0',
			`locked` tinyint(1) NOT NULL DEFAULT '0',
			`highlighted` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

		CREATE TABLE IF NOT EXISTS posts (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`topicid` int(10) NOT NULL,
			`forumid` int(10) NOT NULL,
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`createuser` int(10) NOT NULL,
			`createdate` int(16) NOT NULL DEFAULT '0',
			`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS news (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`createuser` int(10) NOT NULL,
			`createdate` int(16) NOT NULL DEFAULT '0',
			`description` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			`commentable` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		
		CREATE TABLE IF NOT EXISTS news_comments (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`news_id` int(10) NOT NULL,
			`createuser` int(10) NOT NULL,
			`createdate` int(16) NOT NULL DEFAULT '0',
			`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		
		$result = $db->multi_query($database_setup);
		if ( $result === FALSE )
		{
			$template->add_to_stack( ambox('CRITICAL', lang_key("DBCREATE FAIL BODY"), lang_key("DBTABLES FAIL"), "locked.png"), "left");
			
			$template->add_to_stack( $template->parse_template("config error return", array(
				'DBTYPE'	=>	$cfg['dbtype'],
				'DBHOST'	=>	$cfg['dbhost'],
				'DBUSER'	=>	$cfg['dbuser'],
				'DBPASS'	=>	$cfg['dbpass'],
				'DBNAME'	=>	$cfg['dbname'],
				'MESSAGE'	=>	lang_key("DBCREATE FAIL MESSAGE"),
				'SUBMIT_CAPTION'	=>	lang_key("BACK")
			) ), "left");
		} else {
			$template->add_to_stack( ambox('SUCCESS', lang_key("DBTABLES SUCCESS")), "left");
			
			$template->add_to_stack( $template->parse_template("dbtables forward form", array(
				'SUBMIT_CAPTION'	=>	lang_key("NEXT")
			) ), "left");
		}
		
		unset($db);
		
		break;
	case 6:
		/* Administrator user */
		$step_title = lang_key("ADMINUSER TITLE");
		$step_picture = "user.png";
		$step_number = 4;
		
		$template->add_to_stack( $template->parse_template("adminuser", array(
			'ADMINUSER_INTRO'	=>	lang_key("ADMINUSER INFO"),
			'MANDATORY_VARIABLES'	=>	lang_key("MANDATORY VARIABLES"),
			'ADMINUSER_DATA'	=>	lang_key("ADMINUSER DATA"),
			
			'LABEL_USERNAME'	=>	lang_key("USERNAME"),
			'LABEL_PASSWORD'	=>	lang_key("PASSWORD"),
			'LABEL_PASSWORD_RETYPE'	=>	lang_key("PASSWORD RETYPE"),
			'LABEL_EMAIL_ADDRESS'	=>	lang_key("EMAIL ADDRESS"),
			
			'NEXT_CAPTION'	=>	lang_key("NEXT"),
			
			/* Administrator user credentials */
			'USERNAME'	=>	( isset($_POST['error_return']) ? @$_POST['username'] : NULL ),
			'PASSWORD'	=>	( isset($_POST['error_return']) ? @$_POST['password'] : NULL ),
			'PASSWORD_2'	=>	( isset($_POST['error_return']) ? @$_POST['password_2'] : NULL ),
			'EMAIL'	=>	( isset($_POST['error_return']) ? @$_POST['email'] : NULL )
		) ), "left");
		
		break;
	case 7:
		/* Registering administrator user */
		$step_title = lang_key("ADMINUSER TITLE");
		$step_picture = "user.png";
		$step_number = 4;
		
		$mandatory_variable_fail = FALSE;
		$mandatory_variables = array('username', 'password', 'password_2', 'email');
		
		foreach ( $mandatory_variables as $v )
			if ( !isset($_POST[$v]) || @$_POST[$v] == NULL )
				$mandatory_variable_fail = TRUE;
		
		// Prepare an 'error return' form.
		$error_return = $template->parse_template("adminuser error return", array(
			'USERNAME'	=>	@$_POST['username'],
			'PASSWORD'	=>	@$_POST['password'],
			'PASSWORD_2'	=>	@$_POST['password_2'],
			'EMAIL'	=>	@$_POST['email'],
			
			'MESSAGE'	=>	lang_key("ADMINUSER RETURN BODY"),
			'SUBMIT_CAPTION'	=>	lang_key("BACK")
		) );
		
		if ( $mandatory_variable_fail )
		{
			$template->add_to_stack( ambox('ERROR', lang_key("VARIABLE ERROR MULTI"), NULL), "left");
			
			$template->add_to_stack( $error_return, "left");
		} elseif ( !$mandatory_variable_fail )
		{
			if ( $_POST['password'] !== $_POST['password_2'] )
			{
				$template->add_to_stack( ambox('ERROR', lang_key("PASSWORD NO MATCH"), NULL), "left");
				
				$template->add_to_stack( $error_return, "left");
			} else {
				require "config.php";
				require "includes/" .$cfg['dbtype']. ".php";
				$layer_name = "db_" . $cfg['dbtype'];
				$db = new $layer_name($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname']);
				
				$result = $db->query('INSERT INTO users(username, pwd, email, userLevel) VALUES (
					"' .$db->escape($_POST['username']). '",
					"' .$db->escape($_POST['password']). '",
					"' .$db->escape($_POST['email']). '", 4)');
				
				if ( !$result )
				{
					$template->add_to_stack( ambox('CRITICAL', lang_key("ADMINUSER REGISTER ERROR"), NULL, "locked.png"), "left");
					
					$template->add_to_stack( $error_return, "left");
				} elseif ( $result )
				{
					$template->add_to_stack( ambox('SUCCESS', lang_key("ADMINUSER SUCCESS")), "left");
					
					$template->add_to_stack( $template->parse_template("adminuser forward form", array(
						'SUBMIT_CAPTION'	=>	lang_key("NEXT")
					) ), "left");
				}
				
				unset($db);
			}
		}
		
		break;
	case 8:
		/* Finish */
		$step_title = lang_key("FINISH TITLE");
		$step_picture = "success.png";
		$step_number = 5;
		
		$template->add_to_stack( $template->parse_template("finish", array(
			'BODY'	=>	lang_key("FINISH BODY"),
			'MESSAGE'	=>	lang_key("FINISH CONTINUE MESSAGE"),
			'SUBMIT_CAPTION'	=>	lang_key("FINISH")
		) ), "left");
		
		break;
}

// Generate the installer menu
$template->create_stack("install menu entries");
for ($i = 1; $i <= 5; $i++)
{
	if ( $i < $step_number )
		$type = "done";
	if ( $i == $step_number )
		$type = "actual";
	if ( $i > $step_number )
		$type = "remain";
	
	$template->add_to_stack( $template->parse_template("install menu element", array(
		'TYPE'	=>	$type,
		'CAPTION'	=>	lang_key("INSTALL STEP ".$i) ) ), "install menu entries");
}

$template->add_to_stack( $template->parse_template("install menu", array(
	'HEADER'	=>	lang_key("INSTALL MENU HEADER"),
	'CONTENT'	=>	$template->get_stack("install menu entries") ) ), "right" );

$template->delete_stack("install menu entries");

// Output installer content using the buffered stacks
print $template->parse_template("install", array(
	'THEME_NAME'	=>	$_SESSION['install_config']['theme_name'],
	'PICTURE'	=>	$step_picture,
	'ALT'	=>	$step_title,
	'TITLE'	=>	$step_title,
	'LEFT_CONTENT'	=>	$template->get_stack("left"),
	'RIGHT_CONTENT'	=>	$template->get_stack("right"),
	'FOOTER'	=>	NULL
	) );

// Unset classes and finalize execution.
unset($user);
unset($sql);
unset($template);
unset($localization);
?>