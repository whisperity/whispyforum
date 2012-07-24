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
define('UNIQUETOKEN', "installer environment");

// Load and initialize the required libraries
require("includes/functions.php");
require("includes/language.php");
//require("includes/module.php");
//require("includes/tinycache.php");
require("includes/template.php");
require("includes/user.php");

global $template, $user;
$template = new template;
load_lang("core");
$template->load_template("framework", TRUE);
$user = new user(0, FALSE);

load_lang("install");
$template->load_template("install/install", TRUE);

// Fetch the install configuration from session.
if ( !is_array(@$_SESSION['install_config']) )
{
	$_SESSION['install_config'] = array(
		'language'	=>	"english",
		'theme_name'	=>	"tuvia",
		'step'	=>	1);
}

if ( @$_POST['step'] != NULL )
{
	// If we receive a new "step" variable in the POST header, we save it as the current step,
	// so the installer later on will load the wanted step properly.
	$_SESSION['install_config']['step'] = intval(@$_POST['step']);
}

// Developer dumps.
prettyVar($_GET);
prettyVar($_POST);
prettyVar($_SESSION);

// If we are on step one and got a new language and theme option, we set it
// so the installer will automatically load the new ones.
if ( ( @$_POST['new_language'] != NULL || @$_POST['new_theme'] != NULL ) && $_SESSION['install_config']['step'] == 1 )
{
	$_SESSION['install_config']['language'] = ( @$_POST['new_language'] != NULL ? @$_POST['new_language'] : $_SESSION['install_config']['language'] );
	$_SESSION['install_config']['theme_name'] = ( @$_POST['new_theme'] != NULL ? @$_POST['new_theme'] : $_SESSION['install_config']['theme_name'] );
}

// The list of database layers installed into the system.
if ( $_SESSION['install_config']['step'] == 1 || $_SESSION['install_config']['step'] == 2 || $_SESSION['install_config']['step'] == 3 )
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
		$step_alt = lang_key("INTRODUCTION TITLE");
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
		$step_alt = lang_key("CONFIG TITLE");
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
		$step_alt = lang_key("WRITECONFIG TITLE");
		$step_number = 2; // While technically this is the third step, we list it as the second again.
		
		$mandatory_variable_fail = FALSE;
		$mandatory_variables = array('dbtype', 'dbhost', 'dbuser', 'dbpass', 'dbname');
		
		foreach ( $mandatory_variables as $v )
			if ( !isset($_POST[$v]) )
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
				include "includes/" .$_POST['dbtype']. ".php";
				
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
				$template->load_template("install/config_php");
				
				$configfile = fopen("config.php", "w");
				fwrite($configfile, $template->parse_template("install/config_php", array(
					'DBTYPE'	=>	$_POST['dbtype'],
					'DBHOST'	=>	$_POST['dbhost'],
					'DBUSER'	=>	$_POST['dbuser'],
					'DBPASS'	=>	$_POST['dbpass'],
					'DBNAME'	=>	$_POST['dbname'],
					'DISABLE_UNIQUETOKEN'	=>	"//", // Disable the UNIQUETOKEN define() in the file for now.
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
		$step_alt = lang_key("DBCREATE TITLE");
		$step_number = 3;
		
		// Connect to the database server.
		include "config.php";
		include "includes/" .$cfg['dbtype']. ".php";
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
}

// Generate the installer menu
$template->create_stack("install menu entries");
for ($i = 1; $i <= 6; $i++)
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
	'PICTURE'	=>	@$step_picture,
	'ALT'	=>	@$step_alt,
	'TITLE'	=>	@$step_title,
	'LEFT_CONTENT'	=>	$template->get_stack("left"),
	'RIGHT_CONTENT'	=>	$template->get_stack("right")
	) );

/*prettyVar($user);
prettyVar($sql);
prettyVar($template);
prettyVar($cache);
prettyVar($localization);*/
// Unset the global classes and finalize execution.
unset($user);
unset($sql);
unset($template);
unset($cache);
unset($localization);
exit;
?>

<?php
/*** OLD SCRIPT, DEPRECATED ***/
die("Old script is deprecated");
 /**
 * WhispyForum script file - install.php
 * 
 * Install file in / directory.
 * 
 * Helps the webadmin installing the engine.
 * 
 * WhispyForum
 */
 
// We do not use the /includes/load.php load script, because 
// it will result in errors due ot preinstallation state.

// Rather, we use copied bits from there.

/* Libraries */
// Template conductor (we load it before everything because templates are needed to get error messages)
require("includes/templates.class.php");
$Ctemplate = new class_template;

// mySQL database layer
require("includes/mysql.class.php");
$Cmysql = new class_mysql;

// general functions
require("includes/functions.php");
/* Libraries */

// Load boot-time localizations (it's a lite edition of the general English localization, only containing strings which are required before initializing the user array)
require("language/bootlocal.php");

// Load the language array
if ( !isset($_POST['ins_lang']) )
{
	// If we did not select the installer language, load the English one
	include("language/english/language.php");
	$_POST['ins_lang'] = "english"; // Make the English language automatically selected in the language switcher
} elseif ( isset($_POST['ins_lang']) )
{
	// If we set, load the one we set
	include("language/" .$_POST['ins_lang']. "/language.php");
}

// Load the theme stylesheet
if ( !isset($_POST['ins_thm']) )
{
	// If we did not select the installer theme, load the default one
	echo '<link rel="stylesheet" type="text/css" href="themes/tuvia/style.css">';
	$_POST['ins_thm'] = "tuvia"; // Make the default theme automatically selected in the theme switcher
} elseif ( isset($_POST['ins_thm']) )
{
	// If we set, load the one we set
	echo '<link rel="stylesheet" type="text/css" href="themes/' .$_POST['ins_thm']. '/style.css">';
}

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_GET,true))."<br>"; 
print "<h4>POST</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_POST,true))."<br>"; 
echo "\n\n\n";
// Set install poistion
if (!isset($_POST['instPos']))
{
	$instPos = 0;
} else {
	$instPos = $_POST['instPos'];
}

// Including templates based on install position
switch ($instPos)
{
	case NULL:
	case 0:
		// Introduction
		
		/* Language settings */
		// The START pages use a language switcher
		// we need to generate a list about available languages
		$langembed = NULL; // Define the container variable
		$Ldir = "./language/"; // Language home dir
		$Lexempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
		
		if (is_dir($Ldir)) 
		{
			if ($Ldh = opendir($Ldir))
			{
				while (($Lfile = readdir($Ldh)) !== false)
				{
					if(!in_array(strtolower($Lfile),$Lexempt))
					{
						if ( filetype($Ldir . $Lfile) == "dir" )
						{
							// We're now querying all language directories
							if ( ( file_exists($Ldir . $Lfile . "/language.php") ) && ( file_exists($Ldir . $Lfile . "/definition.php") ) )
							{
								// We only list directories containing the language AND the definition file
								include($Ldir.$Lfile."/definition.php"); // This will load in $wf_lang_def (containing the definition)
								
								$langembed .= $Ctemplate->useTemplate("install/ins_start_lang_option", array(
									'SELECTED'	=>	($Lfile == $_POST['ins_lang'] ? " selected " : " "), // Selected is ' ' if it's another language, ' selected ' if it's the current. It makes the current language automatically re-selected
									'DIR_NAME'	=>	$Lfile, // Name of the language's directory
									'LOCALIZED_NAME'	=>	$wf_lang_def['LOCALIZED_NAME'], // The language's own, localized name (so it's Deutch for German)
									'SHORT_NAME'	=>	$wf_lang_def['SHORT_NAME'], // The language's English name (so it's German for German)
									'L_CODE'	=>	$wf_lang_def['LANG_CODE'] // Language code (it's de for German)
								), TRUE); // $langembed will contain the HTML code for the <select>
							}
						}
					}
				unset($wf_lang_def); // Free array memory
				}
			closedir($Ldh);
			}
		}
		/* Language settings */
		
		/* Theme settings */
		// The START pages use a theme switcher
		// we need to generate a list about available themes
		$Tdir = "./themes/"; // Language home dir
		$Texempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
		
		$i = 0; // Define a counter on zero
		$embedder = ""; // Define a container
		
		if (is_dir($Tdir)) 
		{
			if ($Tdh = opendir($Tdir))
			{
				while (($Tfile = readdir($Tdh)) !== false)
				{
					if(!in_array(strtolower($Tfile),$Texempt))
					{
						if ( filetype($Tdir . $Tfile) == "dir" )
						{
							// We're now querying all language directories
							if ( ( file_exists($Tdir . $Tfile . "/style.css") ) &&  ( file_exists($Tdir . $Tfile . "/theme.php") ) )
							{
								// We only list directories containing the stylesheet file
								include($Tdir.$Tfile."/theme.php"); // Load the theme definition array ($theme_def)
								
								// Output one table cell for the theme
								$embedder .= $Ctemplate->useTemplate("install/ins_start_theme_option", array(
									'SELECTED'	=>	($Lfile == $_POST['ins_thm'] ? " selected " : " "), // Selected is ' ' if it's another theme, ' selected ' if it's the current. It makes the current theme automatically re-selected
									'THEME'	=>	$theme_def['SHORT_NAME'], // Short name of theme
									'DESCRIPTION'	=>	$theme_def['DESCRIPTION'], // Extended description
									'DIR_NAME'	=>	$Tfile // Name of the theme's directory (containing CSS)
								), TRUE); // Add it to the embedder
							}
						}
					}
				unset($theme_def); // Free array memory
				}
				closedir($Tdh);
			}
		}
		/* Theme settings */
		
		// We check this file existence now, because if we check it in general (before swich() clause)
		// after the third step (generating config.php) the installation hangs
		if ( file_exists("config.php") )
		{
			// If config.php already exists, give error message
			$Ctemplate->useTemplate("install/ins_start_already", array(
				'INSTALL_LANGUAGES'	=>	$langembed, // Insert the embedding <option> content for the language selector
				'INSTALL_THEMES'	=>	$embedder, // Embed the themes into the output wrapper
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang']
			), FALSE);
		} else {
			// If not, give standard starting screen
			$Ctemplate->useTemplate("install/ins_start", array(
				'INSTALL_LANGUAGES'	=>	$langembed, // Insert the embedding <option> content for the language selector
				'INSTALL_THEMES'	=>	$embedder, // Embed the themes into the output wrapper
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang']
			), FALSE); // Use install introduction
		}
		break;
	case 1:
		// Configuration file generator - getting data
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected from step 2 because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("install/ins_config", array(
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("install/ins_config", array(
				'DBHOST'	=>	"localhost", // Database host (default)
				'DBUSER'	=>	"", // Database user
				'DBPASS'	=>	"", // Database password
				'DBNAME'	=>	"tuvia_db", // Database name (default)
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE); // Config file generator
		}
		break;
	case 2:
		// Configuration file generator
		
		// First, we do a check whether any of the mandatory variables are NULL
		if ( $_POST['dbhost'] == NULL ) // Database host
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"{LANG_SQL_DB_HOST}", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host (should be empty)
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['dbuser'] == NULL ) // Database user
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"{LANG_SQL_DB_USER}", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user (should be empty)
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
				), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['dbpass'] == NULL ) // Database password
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"{LANG_SQL_DB_PASS}", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password (should be empty)
				'DBNAME'	=>	$_POST['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['dbname'] == NULL ) // Database name
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"{LANG_SQL_DB_NAME}", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'], // Database name (should be empty)
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
			exit; // We terminate the script
		}
		
		// At this point, every mandatory fields are set.
		// Now begin writing config file.
		
		$configfile = $Ctemplate->useTemplate("install/config.php", array(
			'DBHOST'	=>	$_POST['dbhost'], // Database host
			'DBUSER'	=>	$_POST['dbuser'], // Database user
			'DBPASS'	=>	$_POST['dbpass'], // Database password
			'DBNAME'	=>	$_POST['dbname'], // Database name
			'UUID'	=>	generateHexToken(), // Random token
			'GDATE'	=>	date('l jS \of F Y H:i:s') // Generation date
		), TRUE); // Generating the file from template
		
		// Writing file. If write error occurs, give output.
		$wrSuccess = @file_put_contents("config.php", $configfile); // wrSuccess is undefined if there's error
		
		if (!$wrSuccess) // Checking whether a writing error occured.
		{
			$Ctemplate->useTemplate("install/ins_config_write_error", array(
				// We need to pass these variables for a working return form.
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE); // We give error output
		} else { // If there isn't any writing errors, give success
			file_put_contents("config.md5", md5($configfile) . " *config.php"); // Put the MD5 hash of written content into a seperate file (for later checks)
			
			$Ctemplate->useTemplate("install/ins_config_write_success", array(
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		}
		break;
	case 3:
		// Testing database connection
		require('config.php'); // We initialize the config array (need to do this for database connection)
		
		$dbconnection = FALSE; // We cannot connect to the DB host first
		
		$dbconnection = $Cmysql->TestConnection(); // We make a test database connection. (Will be true if we succeed)
		
		// $dbconnection is TRUE if test connection is successful
		// $dbconnection is FALSE if test connection is unsuccessful
		
		if ( $dbconnection == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtest_error", array(
				'DBHOST'	=>	$cfg['dbhost'], // Database host
				'DBUSER'	=>	$cfg['dbuser'], // Database user
				'USE_PASS'	=>	( ($cfg['dbpass'] != NULL) ? 'yes' : 'no' ), // Whether there's a password set.
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} elseif ( $dbconnection == TRUE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtest_success", array(
				'DBHOST'	=>	$cfg['dbhost'], // Database host
				'DBUSER'	=>	$cfg['dbuser'], // Database user
				'USE_PASS'	=>	( ($cfg['dbpass'] != NULL) ? 'yes' : 'no' ), // Whether there's a password set.
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		}
		
		$Cmysql->Disconnect(); // Close the active connection
		break;
	case 4:
		// Creating database
		require('config.php'); // We initialize the config array (need to do this for database connection)
		$Cmysql->TestConnection(); // We do a reconnect (without DB selecting, so we use TestConnection)
		
		$dbcreate = FALSE; // We failed creating the database first
		
		// $dbcreate isn't FALSE if the database was created
		// $dbcreate is FALSE if the database creation failed
		
		$dbcreate = $Cmysql->Query("CREATE DATABASE IF NOT EXISTS " .$cfg['dbname']. " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"); // Will be true if we succeed
		
		if ( $dbcreate == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbcreate_error", array(
				'DBNAME'	=>	$cfg['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} elseif ( $dbcreate != FALSE )
		{
			// Give success and proceed
			$Ctemplate->useTemplate("install/ins_dbcreate_success", array(
				'DBNAME'	=>	$cfg['dbname'], // Database name
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		}
		
		$Cmysql->Disconnect(); // Close connection
		break;
	case 5:
		// Creating database tables
		require('config.php'); // Recall config array (it is unloaded)
		$Cmysql->Connect(); // Now we can use the generic connect
		
		$Ctemplate->useStaticTemplate("install/ins_fw_dbtables_head", FALSE); // We use a more-complex templating here
		
		/**
		 * Here are two variables:
		  ** $tablecreation: true by default, becomes false if there were any errors
		  ** $dbtables: one variable for each creation script. FALSE by default, becomes NOT FALSE after query
		 */
		
		$tablecreation = TRUE; // By default, we can create the tables
		$tablelist = array(); // Uncreated tables' name list
		
		/* Users table */
		// Stores the user's data
		$dbtables_user = FALSE; // We failed creating the table first
		$dbtables_user = $Cmysql->Query("CREATE TABLE IF NOT EXISTS users (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user loginname',
			`pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user password (md5 hashed)',
			`email` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user e-mail address',
			`extra_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'extra properties of the user',
			`regdate` int(16) NOT NULL COMMENT 'registration date',
			`loggedin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if user is currently logged in, 0 if not',
			`activated` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if user is activated, 0 if not',
			`token` varchar(49) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'token for activation or password restore',
			`userLevel` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'clearance level',
			`avatar_filename` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'avatar picture filename',
			`language` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'english' COMMENT 'user preferred language',
			`theme` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'tuvia' COMMENT 'user preferred theme',
			`post_count` int(6) NOT NULL DEFAULT '0' COMMENT 'number of posts from the user',
			`news_comment_count` int(6) NOT NULL DEFAULT '0' COMMENT 'number of news comments from the user',
			PRIMARY KEY (`id`),
			UNIQUE KEY `username` (`username`),
			UNIQUE KEY `email` (`email`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'userdata'"); // $dbtables_user sets to true if we succeeded creating a table
		
		// We check users table creation
		if ( $dbtables_user == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"users" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "users"; // Append users table name to fail-list
		} elseif ( $dbtables_user != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"users" // Table name
			), FALSE);
		}
		/* Users table */
		
		/* Modules table */
		// Stores the module data
		$dbtables_modules = FALSE; // We failed creating the table first
		$dbtables_modules = $Cmysql->Query("CREATE TABLE IF NOT EXISTS modules (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`module` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'name of the module',
			`extra_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'configuration of the module',
			`align` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'sidebar vertical align',
			`side` enum('left', 'right') NOT NULL DEFAULT 'left' COMMENT 'sidebar choice',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'module data'"); // $dbtables_modules sets to true if we succeeded creating a table
		
		$dbtables_modules_data = FALSE; // We failed adding the default data first
		$dbtables_modules_data = $Cmysql->Query(""); // $dbtables_modules_data sets to true if we succeeded adding default data
		
		// We check menus table creation
		if ( ( $dbtables_modules == FALSE) || ( $dbtables_modules_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"modules" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "modules"; // Append menu table name to fail-list
		} elseif ( ( $dbtables_modules != FALSE )  && ( $dbtables_modules_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"modules" // Table name
			), FALSE);
		}
		/* Modules table */
		
		/* Menus table */
		// Stores the menu's data
		$dbtables_menu = FALSE; // We failed creating the table first
		$dbtables_menu = $Cmysql->Query("CREATE TABLE IF NOT EXISTS menus (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`header` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'menu header',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'menu information'"); // $dbtables_menu sets to true if we succeeded creating a table
		
		$dbtables_menu_data = FALSE; // We failed adding the default data first
		$dbtables_menu_data = $Cmysql->Query("INSERT INTO menus(header, align, side) VALUES ('Main menu')"); // $dbtables_menu_data sets to true if we succeeded adding default data
		
		// We check menus table creation
		if ( ( $dbtables_menu == FALSE) || ( $dbtables_menu_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"menus" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "menus"; // Append menu table name to fail-list
		} elseif ( ( $dbtables_menu != FALSE )  && ( $dbtables_menu_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"menus" // Table name
			), FALSE);
		}
		/* Menus table */
		
		/* Menu entries table */
		// Stores the menu entries' data
		$dbtables_menuEntries = FALSE; // We failed creating the table first
		$dbtables_menuEntries = $Cmysql->Query("CREATE TABLE IF NOT EXISTS menu_entries (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`menu_id` int(10) NOT NULL COMMENT 'menu id (menus.id)',
			`label` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'text to show',
			`href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'link data',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'menu entry information'"); // $dbtables_menuEntries sets to true if we succeeded creating a table
		
		$dbtables_menuEntries_data = FALSE; // We failed adding the default data first
		$dbtables_menuEntries_data = $Cmysql->Query("INSERT INTO menu_entries(menu_id, label, href) VALUES
		(1, 'Homepage', 'index.php'),
		(1, 'Forum', 'forum.php'),
		(1, 'News', 'news.php')"); // $dbtables_menuEntries_data sets to true if we succeeded adding default data
		
		// We check menu entries table creation
		if ( ( $dbtables_menuEntries == FALSE) || ( $dbtables_menuEntries_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"menu_entries" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "menu_entries"; // Append menu entries table name to fail-list
		} elseif ( ( $dbtables_menuEntries != FALSE ) && ( $dbtables_menuEntries_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"menu_entries" // Table name
			), FALSE);
		}
		/* Menu entries table */
		
		/* Forums table */
		// Stores the data of forums
		$dbtables_forums = FALSE; // We failed creating the table first
		$dbtables_forums = $Cmysql->Query("CREATE TABLE IF NOT EXISTS forums (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title for the forum',
			`info` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'little description appearing under forum title',
			`minLevel` enum('0', '1', '2', '3') NOT NULL DEFAULT '0' COMMENT 'minimal user level to list the forum (users.userLevel)',
			`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for forums'"); // $dbtables_forums sets to true if we succeeded creating a table
		
		$dbtables_forums_data = FALSE; // We failed adding the default data first
		$dbtables_forums_data = $Cmysql->Query("INSERT INTO forums(title, info, minLevel, createdate) VALUES
		('Forum', 'This is an automatically created forum for You', '0', '" .time(). "')"); // $dbtables_forums_data sets to true if we succeeded adding default data
		
		// We check forums table creation
		if ( ( $dbtables_forums == FALSE ) || ( $dbtables_forums_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"forums" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "forums"; // Append forums table name to fail-list
		} elseif ( ( $dbtables_forums != FALSE ) && ( $dbtables_forums_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"forums" // Table name
			), FALSE);
		}
		/* Forums table */
		
		/* Topics table */
		// Stores the data of topics
		$dbtables_topics = FALSE; // We failed creating the table first
		$dbtables_topics = $Cmysql->Query("CREATE TABLE IF NOT EXISTS topics (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`forumid` int(10) NOT NULL COMMENT 'id of the forum the topic is in (forums.id)',
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title for the topic',
			`createuser` int(10) NOT NULL COMMENT 'the ID of the user who created the topic (users.id)',
			`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
			`locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'whether the topic is locked (no new posts allowed): 1 - locked, 0 - not locked',
			`highlighted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'topic is highlighted at the top of the list if value is 1',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for topics'"); // $dbtables_topics sets to true if we succeeded creating a table
		
		$dbtables_topics_data = FALSE; // We failed adding the default data first
		$dbtables_topics_data = $Cmysql->Query("INSERT INTO topics(forumid, title, createuser, createdate, locked, highlighted) VALUES
		(1, 'Topic', '1', '" .time(). "', '0', '0')"); // $dbtables_topics_data sets to true if we succeeded adding default data
		
		// We check topics table creation
		if ( ( $dbtables_topics == FALSE ) || ( $dbtables_topics_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"topics" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "topics"; // Append topics table name to fail-list
		} elseif ( ( $dbtables_topics != FALSE ) && ( $dbtables_topics_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"topics" // Table name
			), FALSE);
		}
		/* Topics table */
		
		/* Posts table */
		// Stores the data of posts
		$dbtables_posts = FALSE; // We failed creating the table first
		$dbtables_posts = $Cmysql->Query("CREATE TABLE IF NOT EXISTS posts (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`topicid` int(10) NOT NULL COMMENT 'id of the topic the post is in (topics.id)',
			`forumid` int(10) NOT NULL COMMENT 'id of the forum the topic containing the post is in (forums.id)',
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title for the post',
			`createuser` int(10) NOT NULL COMMENT 'the ID of the user who posted the post (users.id)',
			`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
			`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'text of the post',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for posts'"); // $dbtables_posts sets to true if we succeeded creating a table
		
		$dbtables_posts_data = FALSE; // We failed adding the default data first
		$dbtables_posts_data = $Cmysql->Query("INSERT INTO posts(topicid, forumid, title, createuser, createdate, content) VALUES
		(1, 1, 'First post', '1', '" .time(). "', 'This is your first post to show you the forum. You can delete this if you want to!')"); // $dbtables_posts_data sets to true if we succeeded adding default data
		
		// We check posts table creation
		if ( ( $dbtables_posts == FALSE ) || ( $dbtables_posts_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"posts" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "posts"; // Append posts table name to fail-list
		} elseif ( ( $dbtables_posts != FALSE ) && ( $dbtables_posts_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"posts" // Table name
			), FALSE);
		}
		/* Posts table */
		
		/* Badges table */
		// Stores the data of earned badges
		$dbtables_badges = FALSE; // We failed creating the table first
		$dbtables_badges = $Cmysql->Query("CREATE TABLE IF NOT EXISTS badges (
			`userid` int(10) NOT NULL COMMENT 'id of the user who earned the badge',
			`badgename` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'name of the badge the user earned (refers badge class badge_array)',
			`earndate` int(16) NOT NULL DEFAULT '0' COMMENT 'timestamp when the user earned the badge',
			UNIQUE KEY `userid AND badgename` (`userid`,`badgename`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'badge information';"); // $dbtables_badges sets to true if we succeeded creating a table
		
		$dbtables_badges_data = FALSE; // We failed adding the default data first
		$dbtables_badges_data = $Cmysql->Query("INSERT INTO badges(userid, badgename, earndate) VALUES ('1', 'FIRSTPOST', '" .time(). "')"); // $dbtables_badges_data sets to true if we succeeded adding default data
		
		// We check badges table creation
		if ( ( $dbtables_badges == FALSE) || ( $dbtables_badges_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"badges" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "badges"; // Append badges table name to fail-list
		} elseif ( ( $dbtables_badges != FALSE ) && ( $dbtables_badges_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"badges" // Table name
			), FALSE);
		}
		/* Badges table */
		
		/* Configuration table */
		// Stores the engine configuration
		$dbtables_config = FALSE; // We failed creating the table first
		$dbtables_config = $Cmysql->Query("CREATE TABLE IF NOT EXISTS config (
			`variable` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'variable name',
			`value` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'value for variable',
			UNIQUE KEY `variable` (`variable`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'configuration'"); // $dbtables_config sets to true if we succeeded creating a table
		
		// We check config table creation
		if ( $dbtables_config == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"config" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "config"; // Append config table name to fail-list
		} elseif ( $dbtables_config != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"config" // Table name
			), FALSE);
		}
		/* Configuration table */
		
		/* News table */
		// Stores the news' data
		$dbtables_news = FALSE; // We failed creating the table first
		$dbtables_news = $Cmysql->Query("CREATE TABLE IF NOT EXISTS news (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title of the entry',
			`createuser` int(10) NOT NULL COMMENT 'the ID of the user who posted the entry (users.id)',
			`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
			`description` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'short description of entry',
			`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'text of the entry',
			`commentable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if entry is commentable, 0 if not',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'news entries'"); // $dbtables_news sets to true if we succeeded creating a table
		
		$dbtables_news_data = FALSE; // We failed adding the default data first
		$dbtables_news_data = $Cmysql->Query("INSERT INTO news(title, createuser, createdate, description, content, commentable) VALUES ('The new site is installed.', 1, '" .time(). "', 'Your first news entry.', 'This is a test news entry, you can delete it if you want.', '1')"); // $dbtables_news_data sets to true if we succeeded adding default data
		
		// We check config table creation
		if ( ( $dbtables_news == FALSE ) || ( $dbtables_news_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"news" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "news"; // Append news table name to fail-list
		} elseif ( ( $dbtables_news != FALSE ) && ( $dbtables_news_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"news" // Table name
			), FALSE);
		}
		/* News table */
		
		/* News comments table */
		// Stores the comments for news entries
		$dbtables_news_comments = FALSE; // We failed creating the table first
		$dbtables_news_comments = $Cmysql->Query("CREATE TABLE IF NOT EXISTS news_comments (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`news_id` int(10) NOT NULL COMMENT 'ID of the news entry the comment is posted to (news.id)',
			`createuser` int(10) NOT NULL COMMENT 'the ID of the user who posted the entry (users.id)',
			`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
			`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'text of the entry',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'comments to news'"); // $dbtables_news_comments sets to true if we succeeded creating a table
		
		
		// We check config table creation
		if ( $dbtables_news_comments == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"news_comments" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist[] = "news_comments"; // Append news_comments table name to fail-list
		} elseif ( $dbtables_news_comments != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"news_comments" // Table name
			), FALSE);
		}
		/* News comments table */
		
		// Check global variable status
		if ( $tablecreation == FALSE )
		{
			// Give error
			
			// Generate a human readable list of tables
			foreach ($tablelist as &$table) // Going through all entries
			{
				if ( !isset($tbls) )
				{
					// If the variable is undefined (meaning this is the first entry)
					$tbls = $table; // Add the first errorneous table
				} elseif ( isset($tbls) )
				{
					// If it is defined
					$tbls .= ", " . $table; // Append the table name with a colon (,)
				}
			}
			
			$Ctemplate->useTemplate("install/ins_dbtables_global_error", array(
				'TABLE_LIST'	=>	$tbls, // Tables list (human readable form)
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} elseif ( $tablecreation == TRUE )
		{
			// Give success and proceed form
			$Ctemplate->useTemplate("install/ins_dbtables_global_success", array(
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		}
		
		$Ctemplate->useStaticTemplate("install/ins_fw_dbtables_foot"); // Frame footer
		break;
	case 6:
		// Administrator user generator - getting data
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected from step 7 because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("install/ins_adminusr", array(
				'ROOT_NAME'	=>	$_POST['root_name'], // Root username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'],  // E-mail address
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("install/ins_adminusr", array(
				'ROOT_NAME'	=>	"root", // Root username (default)
				'ROOT_PASS'	=>	"", // Root password
				'ROOT_EMAIL'	=>	$_SERVER['SERVER_ADMIN'], // Root e-mail address (default)
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE); // Config file generator
		}
		break;
	case 7:
		// Registering admin user
		
		// First, we do a check whether any of the mandatory variables are NULL
		if ( $_POST['root_name'] == NULL ) // Username
		{
			$Ctemplate->useTemplate("install/ins_adminusr_variable_error", array(
				'VARIABLE'	=>	"{LANG_USERNAME}", // Errornous variable name
				'ROOT_NAME'	=>	$_POST['root_name'], // Username (should be empty)
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['root_pass'] == NULL ) // Root password
		{
			$Ctemplate->useTemplate("install/ins_adminusr_variable_error", array(
				'VARIABLE'	=>	"{LANG_PASSWORD}", // Errornous variable name
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password (should be empty)
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['root_email'] == NULL ) // E-mail address
		{
			$Ctemplate->useTemplate("install/ins_adminusr_variable_error", array(
				'VARIABLE'	=>	"{LANG_EMAIL}", // Errornous variable name
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address (should be empty)
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
			exit; // We terminate the script
		}
		
		// At this point, every mandatory fields are set.
		// Registering admin user
		require('config.php'); // We initialize the config array (need to do this for database connection)
		$Cmysql->Connect(); // Now we can use the generic connect
		
		$adminreg = FALSE; // We failed registering the root first
		
		// $adminreg isn't FALSE if the admin user was registered
		// $adminreg is FALSE if the admin user registration failed
		
		$adminreg = $Cmysql->Query("INSERT INTO users(username, pwd, email, regdate, activated, userLevel, post_count) VALUES ('" .
			$Cmysql->EscapeString($_POST['root_name']). "'," .
			"'" .$Cmysql->EscapeString($_POST['root_pass']). "'," .
			"'" .$Cmysql->EscapeString($_POST['root_email']). "', " .time(). ", 1, 4, 1)"); // Will be true if we succeed
		
		if ( $adminreg == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_adminusr_reg_error", array(
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} elseif ( $adminreg != FALSE )
		{
			// Give success and proceed
			$Ctemplate->useTemplate("install/ins_adminusr_reg_success", array(
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		}
		
		$Cmysql->Disconnect(); // Close connection
		break;
	case 8:
		// Site configuration
		
		// Load the definition files of the current theme and language
		include("themes/" .$_POST['ins_thm']. "/theme.php");
		include("language/" .$_POST['ins_lang']. "/definition.php");
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected from step 10 because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("install/ins_siteconfig", array(
				/* General */
				'GLOBAL_TITLE'	=>	$_POST['global_title'],
				'SITE_HOST'	=>	$_POST['site_host'],
				'REGISTRATION_CHECK'	=>	(@$_POST['registration'] == "on" ? " checked" : ""), // Automatically check registration if it was selected
				
				/* Appearance */
				'LANGS_LOCALIZED_NAME'	=>	$wf_lang_def['LOCALIZED_NAME'], // The language's own, localized name (so it's Deutch for German)
				'LANGS_SHORT_NAME'	=>	$wf_lang_def['SHORT_NAME'], // The language's English name (so it's German for German)
				'LANGS_CODE'	=>	$wf_lang_def['LANG_CODE'], // Language code (it's de for German)
				'THEMES_THEME'	=>	$theme_def['SHORT_NAME'], // Short name of theme
				'THEMES_DESCRIPTION'	=>	$theme_def['DESCRIPTION'], // Extended description
				
				/* Modules */
				'MODULE_FORUM_CHECK'	=>	(@$_POST['module_forum'] == "on" ? " checked" : ""), // Automatically check forum module if it was selected
				'MODULE_NEWS_CHECK'	=>	(@$_POST['module_news'] == "on" ? " checked" : ""), // Automatically check news module if it was selected
				
				/* Forum */
				// Topic switch
				'T_5_SELECT'	=>	($_POST['forum_topic_count_per_page'] == 5 ? " selected" : ""),
				'T_15_SELECT'	=>	($_POST['forum_topic_count_per_page'] == 15 ? " selected" : ""),
				'T_30_SELECT'	=>	($_POST['forum_topic_count_per_page'] == 30 ? " selected" : ""),
				'T_50_SELECT'	=>	($_POST['forum_topic_count_per_page'] == 50 ? " selected" : ""),
				'T_100_SELECT'	=>	($_POST['forum_topic_count_per_page'] == 100 ? " selected" : ""),
				
				// Post switch
				'P_5_SELECT'	=>	($_POST['forum_post_count_per_page'] == 5 ? " selected" : ""),
				'P_15_SELECT'	=>	($_POST['forum_post_count_per_page'] == 15 ? " selected" : ""),
				'P_30_SELECT'	=>	($_POST['forum_post_count_per_page'] == 30 ? " selected" : ""),
				'P_50_SELECT'	=>	($_POST['forum_post_count_per_page'] == 50 ? " selected" : ""),
				'P_100_SELECT'	=>	($_POST['forum_post_count_per_page'] == 100 ? " selected" : ""),
				
				/* News */
				// Entry switch
				'N_5_SELECT'	=>	($_POST['news_split_value'] == 5 ? " selected" : ""),
				'N_15_SELECT'	=>	($_POST['news_split_value'] == 15 ? " selected" : ""),
				'N_30_SELECT'	=>	($_POST['news_split_value'] == 30 ? " selected" : ""),
				'N_50_SELECT'	=>	($_POST['news_split_value'] == 50 ? " selected" : ""),
				'N_100_SELECT'	=>	($_POST['news_split_value'] == 100 ? " selected" : ""),
				
				// Passing install theme and language directory values
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
		), FALSE); // Output template
		} else {
			// We output general form
			$Ctemplate->useTemplate("install/ins_siteconfig", array(
				/* General */
				'GLOBAL_TITLE'	=>	$wf_lang['{LANG_INSTALL_SITECONFIG_DEFAULT_TITLE}'],
				'SITE_HOST'	=>	$_SERVER['HTTP_HOST'],
				'REGISTRATION_CHECK'	=>	" checked",
				
				/* Appearance */
				'LANGS_LOCALIZED_NAME'	=>	$wf_lang_def['LOCALIZED_NAME'], // The language's own, localized name (so it's Deutch for German)
				'LANGS_SHORT_NAME'	=>	$wf_lang_def['SHORT_NAME'], // The language's English name (so it's German for German)
				'LANGS_CODE'	=>	$wf_lang_def['LANG_CODE'], // Language code (it's de for German)
				'THEMES_THEME'	=>	$theme_def['SHORT_NAME'], // Short name of theme
				'THEMES_DESCRIPTION'	=>	$theme_def['DESCRIPTION'], // Extended description
				
				/* Modules */
				'MODULE_FORUM_CHECK'	=>	" checked", // Automatically check forum module
				'MODULE_NEWS_CHECK'	=>	" checked", // Automatically check news module
				
				/* Forum */
				// Topic switch
				'T_5_SELECT'	=>	"",
				'T_15_SELECT'	=>	" selected",
				'T_30_SELECT'	=>	"",
				'T_50_SELECT'	=>	"",
				'T_100_SELECT'	=>	"",
				
				// Post switch
				'P_5_SELECT'	=>	"",
				'P_15_SELECT'	=>	" selected",
				'P_30_SELECT'	=>	"",
				'P_50_SELECT'	=>	"",
				'P_100_SELECT'	=>	"",
				
				/* News */
				// Entry switch
				'N_5_SELECT'	=>	"",
				'N_15_SELECT'	=>	" selected",
				'N_30_SELECT'	=>	"",
				'N_50_SELECT'	=>	"",
				'N_100_SELECT'	=>	"",
				
				// Passing install theme and language directory values
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE); // Output template
		}
		break;
	case 9:
		// Write site configuration
		require('config.php'); // Recall config array (it is unloaded, but it's needed to connect to database)
		$Cmysql->Connect(); // We can use the generic connect
		
		// First, we do a check whether any of the mandatory variables are NULL
		if ( $_POST['global_title'] == NULL ) // Global title
		{
			$Ctemplate->useTemplate("install/ins_siteconfig_variable_error", array(
				'VARIABLE'	=>	"{LANG_INSTALL_SITECONFIG_TITLE}", // Errornous variable name
				
				/* General */
				'GLOBAL_TITLE'	=>	$_POST['global_title'],
				'SITE_HOST'	=>	$_POST['site_host'],
				'REGISTRATION'	=>	$_POST['registration'],
				
				/* Appearance */
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm'],
				
				/* Modules */
				'MODULE_FORUM'	=>	(@$_POST['module_forum'] == "on" ? "on" : "off"),
				'MODULE_NEWS'	=>	(@$_POST['module_news'] == "on" ? "on" : "off"),
				
				/* Forum */
				'FORUM_TOPIC_COUNT_PER_PAGE'	=>	$_POST['forum_topic_count_per_page'],
				'FORUM_POST_COUNT_PER_PAGE'	=>	$_POST['forum_post_count_per_page'],
				
				/* News */
				'NEWS_SPLIT_VALUE'	=>	$_POST['news_split_value']
			), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['site_host'] == NULL ) // HTTP HOST
		{
			$Ctemplate->useTemplate("install/ins_siteconfig_variable_error", array(
				'VARIABLE'	=>	"{LANG_INSTALL_SITECONFIG_HOST}", // Errornous variable name
				
				/* General */
				'GLOBAL_TITLE'	=>	$_POST['global_title'],
				'SITE_HOST'	=>	$_POST['site_host'],
				'REGISTRATION'	=>	$_POST['registration'],
				
				/* Appearance */
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm'],
				
				/* Modules */
				'MODULE_FORUM'	=>	(@$_POST['module_forum'] == "on" ? "on" : "off"),
				'MODULE_NEWS'	=>	(@$_POST['module_news'] == "on" ? "on" : "off"),
				
				/* Forum */
				'FORUM_TOPIC_COUNT_PER_PAGE'	=>	$_POST['forum_topic_count_per_page'],
				'FORUM_POST_COUNT_PER_PAGE'	=>	$_POST['forum_post_count_per_page'],
				
				/* News */
				'NEWS_SPLIT_VALUE'	=>	$_POST['news_split_value']
			), FALSE);
			exit; // We terminate the script
		}
		
		// Store the site configuration
		$sConfig = $Cmysql->Query("INSERT INTO config(variable, value) VALUES
			('language', '" .$Cmysql->EscapeString($_POST['ins_lang']). "'),
			('theme', '" .$Cmysql->EscapeString($_POST['ins_thm']). "'),
			('global_title', '" .$Cmysql->EscapeString($_POST['global_title']). "'),
			('site_host', '" .$Cmysql->EscapeString($_POST['site_host']). "'),
			('registration', '" .$Cmysql->EscapeString($_POST['registration']). "'),
			('module_forum', '" .(@$_POST['module_forum'] == "on" ? "on" : "off"). "'),
			('forum_topic_count_per_page', '" .$Cmysql->EscapeString($_POST['forum_topic_count_per_page']). "'),
			('forum_post_count_per_page', '" .$Cmysql->EscapeString($_POST['forum_post_count_per_page']). "'),
			('module_news', '" .(@$_POST['module_news'] == "on" ? "on" : "off"). "'),
			('news_split_value', '" .$Cmysql->EscapeString($_POST['news_split_value']). "')"); // $sConfig is true if we are successful
		
		// Give return or proceed forms based on success
		if ( $sConfig == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_siteconfig_error", array(
				/* General */
				'GLOBAL_TITLE'	=>	$_POST['global_title'],
				'SITE_HOST'	=>	$_POST['site_host'],
				'REGISTRATION'	=>	$_POST['registration'],
				
				/* Appearance */
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm'],
				
				/* Modules */
				'MODULE_FORUM'	=>	(@$_POST['module_forum'] == "on" ? "on" : "off"),
				'MODULE_NEWS'	=>	(@$_POST['module_news'] == "on" ? "on" : "off"),
				
				/* Forum */
				'FORUM_TOPIC_COUNT_PER_PAGE'	=>	$_POST['forum_topic_count_per_page'],
				'FORUM_POST_COUNT_PER_PAGE'	=>	$_POST['forum_post_count_per_page'],
				
				/* News */
				'NEWS_SPLIT_VALUE'	=>	$_POST['news_split_value'],
				
				// Passing install theme and language directory values
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		} elseif ( $sConfig != FALSE )
		{
			// Give success and proceed
			$Ctemplate->useTemplate("install/ins_siteconfig_success", array(
				'INSTALL_LANGUAGE'	=>	$_POST['ins_lang'],
				'INSTALL_THEME'	=>	$_POST['ins_thm']
			), FALSE);
		}
		break;
	case 10:
		// Finish
		$Ctemplate->useStaticTemplate("install/ins_finish", FALSE); // Use install finish template
		break;
 }
?>
